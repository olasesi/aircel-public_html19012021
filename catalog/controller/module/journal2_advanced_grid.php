<?php
/* @property ModelCatalogManufacturer model_catalog_manufacturer */
/* @property ModelCatalogCategory model_catalog_category */
class ControllerModuleJournal2AdvancedGrid extends Controller {

    private static $CACHEABLE = null;

    protected $data = array();

    protected function render() {
        if (version_compare(VERSION, '2.2', '<')) {
            $this->template = $this->config->get('config_template') . '/template/' . $this->template;
        }

        $this->template = str_replace($this->config->get('config_template') . '/template/' . $this->config->get('config_template') . '/template/', $this->config->get('config_template') . '/template/', $this->template);

        if (version_compare(VERSION, '3', '>=')) {
            return $this->load->view(str_replace('.tpl', '', $this->template), $this->data);
        }

        return Front::$IS_OC2 ? $this->load->view($this->template, $this->data) : parent::render();
    }

    protected function getChild($child, $args = array()) {
        return version_compare(VERSION, '2', '>=') ? $this->load->controller($child, $args) : parent::getChild($child, $args);
    }

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');

        if (self::$CACHEABLE === null) {
            //self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.advanced_grid_cache');
            self::$CACHEABLE = false;
        }
    }

    public function index($setting) {
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }

        Journal2::startTimer(get_class($this));

        /* get module data from db */
        $module_data = $this->model_journal2_module->getModule($setting['module_id']);
        if (!$module_data || !isset($module_data['module_data']) || !$module_data['module_data']) return;
        $module_data = $module_data['module_data'];

        /* device detection */
        $this->data['disable_on_classes'] = array();

        if ($this->journal2->settings->get('responsive_design')) {
            $device = Journal2Utils::getDevice();

            if ($setting['position'] === 'column_left' || $setting['position'] === 'column_right') {
                if ($device === 'phone') {
                    return;
                }

                if ($device === 'tablet') {
                    if ($setting['position'] === 'column_left' && $this->journal2->settings->get('left_column_on_tablet', 'on') !== 'on') {
                        return;
                    }

                    if ($setting['position'] === 'column_right' && $this->journal2->settings->get('right_column_on_tablet', 'on') !== 'on') {
                        return;
                    }
                }
            }

            if (Journal2Utils::getProperty($module_data, 'enable_on_phone', '1') == '0') {
                if ($device === 'phone') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-phone';
                }
            }

            if (Journal2Utils::getProperty($module_data, 'enable_on_tablet', '1') == '0') {
                if ($device === 'tablet') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-tablet';
                }
            }

            if (Journal2Utils::getProperty($module_data, 'enable_on_desktop', '1') == '0') {
                if ($device === 'desktop') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-desktop';
                }
            }
        }

        $this->data['height'] = Journal2Utils::getProperty($module_data, 'height');

        $this->data['css'] = array();

        $this->data['is_top_bottom'] = false;

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_advanced_grid_' . $setting['module_id'], implode('; ', $css));
            $this->journal2->settings->set('module_journal2_advanced_grid_' . $setting['module_id'] . '_classes', implode(' ', $this->data['disable_on_classes']));
            $this->journal2->settings->set('module_journal2_advanced_grid_' . $setting['module_id'] . '_video', Journal2Utils::getVideoBackgroundSettings(Journal2Utils::getProperty($module_data, 'video_background.value.text')));

            $this->data['is_top_bottom'] = true;
            /* inner css */
            $css = array();
            if (Journal2Utils::getProperty($module_data, 'fullwidth')) {
                $css[] = 'max-width: 100%';
                $css[] = 'padding-left: ' . $padding;
                $css[] = 'padding-right: ' . $padding;
            } else {
                $css[] = 'max-width: ' . $this->journal2->settings->get('site_width', 1024) . 'px';
                $css = array_merge($css, Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'module_background')));
                if (Journal2Utils::getProperty($module_data, 'module_padding')) {
                    $this->data['gutter_on_class'] = 'gutter-on';
                    $css[] = 'padding: 20px';
                }
            }
            $css = array_merge($css, Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'module_shadow')));
            $this->data['css'] = $css;
        }

        $module_spacing = Journal2Utils::getProperty($module_data, 'module_spacing', 0);
        $this->data['module_spacing'] = $module_spacing . 'px';
        $this->data['grid_dimensions'] = (int)Journal2Utils::getProperty($module_data, 'grid_dimensions', '1') && $this->journal2->html_classes->hasClass('is-admin');

        $this->data['css'] = implode('; ', $this->data['css']);

        $cache_property = "module_journal_advanced_grid_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $this->data['module'] = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            $columns = Journal2Utils::getProperty($module_data, 'columns', array());
            $columns = Journal2Utils::sortArray($columns);

            $this->data['columns'] = array();

            foreach ($columns as $column) {
                if (!$column['status']) continue;

                $modules = Journal2Utils::getProperty($column, 'modules', array());
                $modules = Journal2Utils::sortArray($modules);

                $rendered_modules = array();

                $size = floor($column['width']);

                $has_rs = false;
                $has_banner = false;

                foreach ($modules as $module) {
                    if (!$module['status']) continue;

                    /* device detection */
                    $disable_on_classes = array();

                    if ($this->journal2->settings->get('responsive_design')) {
                        $device = Journal2Utils::getDevice();

                        if (Journal2Utils::getProperty($module, 'enable_on_phone', '1') == '0') {
                            if ($device === 'phone') {
                                continue;
                            } else {
                                $disable_on_classes[] = 'hide-on-phone';
                            }
                        }

                        if (Journal2Utils::getProperty($module, 'enable_on_tablet', '1') == '0') {
                            if ($device === 'tablet') {
                                continue;
                            } else {
                                $disable_on_classes[] = 'hide-on-tablet';
                            }
                        }

                        if (Journal2Utils::getProperty($module, 'enable_on_desktop', '1') == '0') {
                            if ($device === 'desktop') {
                                continue;
                            } else {
                                $disable_on_classes[] = 'hide-on-desktop';
                            }
                        }
                    }

                    $module_id = Journal2Utils::getProperty($module, 'module_id', -1);
                    if ($module_id === -1) continue;

                    $module_data = $this->model_journal2_module->getModule($module_id);

                    if (!isset($module_data['module_type'])) {
                        continue;
                    }

                    $module_type = $module_data['module_type'];

                    if ($module_type === 'journal2_slider') {
                        $has_rs = true;
                    }
                    if ($module_type === 'journal2_static_banners') {
                        $has_banner = true;
                    }

                    $m_width = ($this->journal2->settings->get('site_width', 1024) - ($this->data['is_top_bottom'] ? 0 : 240 * $this->journal2->settings->get("config_columns_count"))) * $size / 100;
                    $m_height = round($this->data['height'] * Journal2Utils::getProperty($module, 'height') / 100);

                    if ($module_type === 'journal2_slider') {
                        $disable_on_classes[] = 'row-rs';
                    }

                    $rendered_modules[] = array(
                        'class'         => $disable_on_classes,
                        'height'        => Journal2Utils::getProperty($module, 'height'),
                        'content'       => $this->getChild('module/' . $module_type, array (
                            'module_id' => $module_id,
                            'layout_id' => -1,
                            'width'     => $m_width,
                            'height'    => $m_height - $module_spacing,
                            'position'  => 'multi_module'
                        ))
                    );
                }

                if ($rendered_modules) {
                    $this->data['columns'][] = array(
                        'classes'   => "xs-{$size} sm-{$size} md-{$size} lg-{$size} xl-{$size}" . ($has_rs ? ' column-rs' : '') . ($has_banner ? ' column-banner' : ''),
                        'modules'   => $rendered_modules,
                        'width'     => $column['width']
                    );
                }
            }

            $this->template = 'journal2/module/advanced_grid.tpl';

            if (self::$CACHEABLE === true) {
                $html = Minify_HTML::minify($this->render(), array(
                    'xhtml' => false,
                    'jsMinifier' => 'j2_js_minify'
                ));
                $this->journal2->cache->set($cache_property, $html);
            }
        } else {
            $this->template = 'journal2/cache/cache.tpl';
            $this->data['cache'] = $cache;
        }

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

}
