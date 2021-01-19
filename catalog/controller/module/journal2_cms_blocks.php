<?php
/* @property ModelCatalogManufacturer model_catalog_manufacturer */
/* @property ModelCatalogCategory model_catalog_category */
class ControllerModuleJournal2CmsBlocks extends Controller {

    private static $CACHEABLE = null;
    private $google_fonts = array();

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

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.cms_blocks_cache');
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

        $this->data['css'] = '';

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_cms_blocks_' . $setting['module_id'], implode('; ', $css));
            $this->journal2->settings->set('module_journal2_cms_blocks_' . $setting['module_id'] . '_classes', implode(' ', $this->data['disable_on_classes']));
            $this->journal2->settings->set('module_journal2_cms_blocks_' . $setting['module_id'] . '_video', Journal2Utils::getVideoBackgroundSettings(Journal2Utils::getProperty($module_data, 'video_background.value.text')));

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
            $this->data['css'] = implode('; ', $css);
        }

        $cache_property = "module_journal_cms_blocks_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            /* set global module properties */
            $this->data['module'] = $module;
            $this->data['title'] = Journal2Utils::getProperty($module_data, 'module_title.value.' . $this->config->get('config_language_id'), '');

            /* item css */
            $css = array();
            $css = array_merge($css, Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'bg')));
            $css = array_merge($css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'border')));
            if ($padding = Journal2Utils::getProperty($module_data, 'padding.value.text', 0)) {
                $css[] = 'padding: ' . $padding . 'px';
            }
            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'shadow')) as $sett) {
                $css[] = $sett;
            }
            $this->data['item_css'] = $css;

            /* headings */
            $css = array();
            $fv = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.v');
            $fg = false;

            if (Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_type') === 'google') {
                $fg = true;
                $font_name = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_name');
                $font_subset = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_subset');
                $font_weight = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_weight');
                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                $this->google_fonts[] = array(
                    'name'  => $font_name,
                    'subset'=> $font_subset,
                    'weight'=> $font_weight
                );
                $weight = filter_var(Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                $css[] = 'font-weight: ' . ($weight ? $weight : 400);
                $css[] = "font-family: '" . Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_name') . "'";
            }
            if (Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_type') === 'system') {
                if ($fv !== '2') {
                    $css[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_weight');
                }
                $css[] = 'font-family: ' . Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_family');
            }
            if ($fv === '2') {
                if (!$fg && ($value = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_weight'))) {
                    $css[] = 'font-weight: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_style')) {
                    $css[] = 'font-style: ' . $value;
                }
                $value = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_size');
                if (Journal2Utils::getDevice() === 'phone') {
                    $value2 = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_size_mobile');
                    if ($value2 && $value2 !== '---') {
                        $value = $value2;
                    }
                }
                if ($value && $value !== '---') {
                    $css[] = 'font-size: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.text_transform')) {
                    $css[] = 'text-transform: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'cms_heading_font.value.letter_spacing')) {
                    $css[] = 'letter-spacing: ' . $value . 'px';
                }
            } else {
                if (Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_type') !== 'none') {
                    $css[] = 'font-size: ' . Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_size');
                    $css[] = 'font-style: ' . Journal2Utils::getProperty($module_data, 'cms_heading_font.value.font_style');
                    $css[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, 'cms_heading_font.value.text_transform');
                }
            }
            if (Journal2Utils::getProperty($module_data, 'cms_heading_font.value.color.value.color')) {
                $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'cms_heading_font.value.color.value.color'));
            }

            if ($value = Journal2Utils::getProperty($module_data, 'cms_heading_padding.value.text')) {
                $css[] = 'padding-bottom: ' . $value . 'px';
            }

            $this->data['headings_style'] = implode('; ', $css);

            /* paragraphs */
            $css = array();
            $fv = Journal2Utils::getProperty($module_data, 'cms_font_color.value.v');
            $fg = false;

            if (Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_type') === 'google') {
                $fg = true;
                $font_name = Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_name');
                $font_subset = Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_subset');
                $font_weight = Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_weight');
                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                $this->google_fonts[] = array(
                    'name'  => $font_name,
                    'subset'=> $font_subset,
                    'weight'=> $font_weight
                );
                $weight = filter_var(Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                $css[] = 'font-weight: ' . ($weight ? $weight : 400);
                $css[] = "font-family: '" . Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_name') . "'";
            }
            if (Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_type') === 'system') {
                if ($fv !== '2') {
                    $css[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_weight');
                }
                $css[] = 'font-family: ' . Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_family');
            }
            if ($fv === '2') {
                if (!$fg && ($value = Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_weight'))) {
                    $css[] = 'font-weight: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_style')) {
                    $css[] = 'font-style: ' . $value;
                }
                $value = Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_size');
                if (Journal2Utils::getDevice() === 'phone') {
                    $value2 = Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_size_mobile');
                    if ($value2 && $value2 !== '---') {
                        $value = $value2;
                    }
                }
                if ($value && $value !== '---') {
                    $css[] = 'font-size: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'cms_font_color.value.text_transform')) {
                    $css[] = 'text-transform: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'cms_font_color.value.letter_spacing')) {
                    $css[] = 'letter-spacing: ' . $value . 'px';
                }
            } else {
                if (Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_type') !== 'none') {
                    $css[] = 'font-size: ' . Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_size');
                    $css[] = 'font-style: ' . Journal2Utils::getProperty($module_data, 'cms_font_color.value.font_style');
                    $css[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, 'cms_font_color.value.text_transform');
                }
            }
            if (Journal2Utils::getProperty($module_data, 'cms_font_color.value.color.value.color')) {
                $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'cms_font_color.value.color.value.color'));
            }

            if ($value = Journal2Utils::getProperty($module_data, 'cms_block_p_padding.value.text')) {
                $css[] = 'padding-bottom: ' . $value . 'px';
            }

            if ($value = Journal2Utils::getProperty($module_data, 'cms_block_line_height.value.text')) {
                $css[] = 'line-height: ' . $value . 'px';
            }

            $this->data['paragraphs_style'] = implode('; ', $css);

            /* sort sections */
            $sections = Journal2Utils::getProperty($module_data, 'sections', array());
            $sections = Journal2Utils::sortArray($sections);

            /* generate sections */
            $this->data['sections'] = array();
            foreach ($sections as $section) {
                if (!$section['status']) continue;
                $icon_css = array();
                $block_css = array();

                if (Journal2Utils::getProperty($section, 'block_icon_offset')) {
                    $margin = Journal2Utils::getProperty($section, 'block_icon_offset');
                    $icon_css[] = 'margin-top: -' . Journal2Utils::getProperty($section, 'block_icon_offset') . 'px';
                } else {
                    $margin = 0;
                }
                if (Journal2Utils::getColor(Journal2Utils::getProperty($section, 'icon_bg_color.value.color'))) {
                    $icon_css[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($section, 'icon_bg_color.value.color'));
                }
                if (Journal2Utils::getProperty($section, 'icon_width')) {
                    $icon_css[] = 'width: ' . Journal2Utils::getProperty($section, 'icon_width') . 'px';
                }
                if (Journal2Utils::getProperty($section, 'icon_height')) {
                    $icon_css[] = 'height: ' . Journal2Utils::getProperty($section, 'icon_height') . 'px';
                    $icon_css[] = 'line-height: ' . Journal2Utils::getProperty($section, 'icon_height') . 'px';
                }
                if (Journal2Utils::getProperty($section, 'icon_border')) {
                    $icon_css = array_merge($icon_css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($section, 'icon_border')));
                }

                $css = array();
                if ($color = Journal2Utils::getProperty($section, 'bg_color.value.color')) {
                    $css[] = 'background-color: ' . Journal2Utils::getColor($color);
                }

                if ($margin) {
                    $block_css[] = 'margin-top: ' . ($margin / 2) . 'px';
                    $block_css[] = 'padding-top: ' . ($margin / 4) . 'px';
                }

                $css = array_merge($css, Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($section, 'background')));

                if (is_numeric($value = Journal2Utils::getProperty($module_data, 'bottom_margin.value.text'))) {
                    $block_css[] = 'margin-bottom: ' . $value . 'px';
                }

                $this->data['sections'][] = array(
                    'css'   => implode('; ', array_merge($this->data['item_css'], $css)),
                    'block_css' => implode('; ', $block_css),
                    'has_icon' => Journal2Utils::getProperty($section, 'icon_status'),
                    'icon_position' => Journal2Utils::getProperty($section, 'icon_position', 'top'),
                    'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($section, 'icon')),
                    'icon_css' => implode('; ', $icon_css),
                    'type' => 'html',
                    'title' => Journal2Utils::getProperty($section, 'section_title.value.' . $this->config->get('config_language_id'), ''),
                    'content_align' => Journal2Utils::getProperty($section, 'text_align', 'left'),
                    'content' => Journal2Utils::getProperty($section, 'text.' . $this->config->get('config_language_id'), 'Not Translated')
                );
            }

            /* grid classes */
            if (in_array($setting['position'], array('column_left', 'column_right'))) {
                $this->data['grid_classes'] = 'xs-100 sm-100 md-100 lg-100 xl-100';
            } else {
                $columns = in_array($setting['position'], array('top', 'bottom')) ? 0 : $this->journal2->settings->get('config_columns_count', 0);
                $this->data['grid_classes'] = Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($module_data, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024), $columns);
            }

            $this->template = 'journal2/module/cms_blocks.tpl';

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
