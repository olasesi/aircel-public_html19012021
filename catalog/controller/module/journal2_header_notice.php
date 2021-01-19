<?php
class ControllerModuleJournal2HeaderNotice extends Controller {

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
        $this->load->model('journal2/menu');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.header_notice_cache');
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

        $this->data['cookie_name'] = 'header_notice-' . Journal2Utils::getProperty($module_data, 'do_not_show_again_cookie');
        $this->data['do_not_show_again'] = Journal2Utils::getProperty($module_data, 'do_not_show_again', '1');
        $this->data['show_only_once'] = Journal2Utils::getProperty($module_data, 'show_only_once', '0');

        if (isset($this->request->cookie[$this->data['cookie_name']])) {
            return;
        }

        if ($this->data['show_only_once']) {
            setcookie($this->data['cookie_name'], 1, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
        }

        $cache_property = "module_journal_header_notice_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            /* set global module properties */
            $this->data['module'] = $module;
            $this->data['text'] = Journal2Utils::getProperty($module_data, 'text.value.' . $this->config->get('config_language_id'));
            $this->data['text_align'] = Journal2Utils::getProperty($module_data, 'text_align', 'center');
            $this->data['icon'] = Journal2Utils::getIconOptions2(Journal2Utils::getProperty($module_data, 'icon'));
            $this->data['icon_position'] = Journal2Utils::getProperty($module_data, 'icon_position', 'left');
            $this->data['float_icon'] = Journal2Utils::getProperty($module_data, 'float_icon', '0') == '1' ? 'floated-icon' : '';
            $this->data['fullwidth'] = Journal2Utils::getProperty($module_data, 'fullwidth', '0') == '1' ? 'fullwidth-notice' : '';
            $this->data['close_button_type'] = Journal2Utils::getProperty($module_data, 'close_button_type', 'icon');
            $this->data['close_button_text'] = Journal2Utils::getProperty($module_data, 'close_button_text.value.' . $this->config->get('config_language_id'), 'Close');

            $css = array();

            if (($value = Journal2Utils::getProperty($module_data, 'padding_t.value.text')) !== null) {
                $css[] = 'padding-top: ' . $value . 'px';
            }

            if (($value = Journal2Utils::getProperty($module_data, 'padding_r.value.text')) !== null) {
                $css[] = 'padding-right: ' . $value . 'px';
            }

            if (($value = Journal2Utils::getProperty($module_data, 'padding_b.value.text')) !== null) {
                $css[] = 'padding-bottom: ' . $value . 'px';
            }

            if (($value = Journal2Utils::getProperty($module_data, 'padding_l.value.text')) !== null) {
                $css[] = 'padding-left: ' . $value . 'px';
            }

            $fv = Journal2Utils::getProperty($module_data, 'text_font.value.v');
            $fg = false;

            if (Journal2Utils::getProperty($module_data, 'text_font.value.font_type') === 'google') {
                $fg = true;
                $font_name = Journal2Utils::getProperty($module_data, 'text_font.value.font_name');
                $font_subset = Journal2Utils::getProperty($module_data, 'text_font.value.font_subset');
                $font_weight = Journal2Utils::getProperty($module_data, 'text_font.value.font_weight');
                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                $this->google_fonts[] = array(
                    'name'  => $font_name,
                    'subset'=> $font_subset,
                    'weight'=> $font_weight
                );
                $weight = filter_var(Journal2Utils::getProperty($module_data, 'text_font.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                $css[] = 'font-weight: ' . ($weight ? $weight : 400);
                $css[] = "font-family: '" . Journal2Utils::getProperty($module_data, 'text_font.value.font_name') . "'";
            }

            if (Journal2Utils::getProperty($module_data, 'text_font.value.font_type') === 'system') {
                if ($fv !== '2') {
                    $css[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, 'text_font.value.font_weight');
                }
                $css[] = 'font-family: ' . Journal2Utils::getProperty($module_data, 'text_font.value.font_family');
            }

            if ($fv === '2') {
                if (!$fg && ($value = Journal2Utils::getProperty($module_data, 'text_font.value.font_weight'))) {
                    $css[] = 'font-weight: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'text_font.value.font_style')) {
                    $css[] = 'font-style: ' . $value;
                }
                $value = Journal2Utils::getProperty($module_data, 'text_font.value.font_size');
                if (Journal2Utils::getDevice() === 'phone') {
                    $value2 = Journal2Utils::getProperty($module_data, 'text_font.value.font_size_mobile');
                    if ($value2 && $value2 !== '---') {
                        $value = $value2;
                    }
                }
                if ($value && $value !== '---') {
                    $css[] = 'font-size: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'text_font.value.text_transform')) {
                    $css[] = 'text-transform: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'text_font.value.letter_spacing')) {
                    $css[] = 'letter-spacing: ' . $value . 'px';
                }
            } else {
                if (Journal2Utils::getProperty($module_data, 'text_font.value.font_type') !== 'none') {
                    $css[] = 'font-size: ' . Journal2Utils::getProperty($module_data, 'text_font.value.font_size');
                    $css[] = 'font-style: ' . Journal2Utils::getProperty($module_data, 'text_font.value.font_style');
                    $css[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, 'text_font.value.text_transform');
                    if ($letter_spacing = Journal2Utils::getProperty($module_data, 'text_font.value.letter_spacing')) {
                        $css[] = 'letter-spacing: ' . $letter_spacing . 'px';
                    }
                }
            }

            if (Journal2Utils::getProperty($module_data, 'text_font.value.color.value.color')) {
                $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'text_font.value.color.value.color'));
            }

            if ($color = Journal2Utils::getProperty($module_data, 'text_background_color.value.color')) {
                $css[] = "background-color: " . Journal2Utils::getColor($color);
            }

            foreach (Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'text_background_image')) as $sett) {
                $css[] = $sett;
            }

            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'text_shadow')) as $sett) {
                $css[] = $sett;
            }

            $this->data['css'] = implode('; ', $css);

            $global_style = array();

            /* link colors */
            if ($color = Journal2Utils::getProperty($module_data, 'text_link_color.value.color')) {
                $global_style[] = "#journal-header-notice-{$module} a { color: " . Journal2Utils::getColor($color) . "}";
            }
            if ($color = Journal2Utils::getProperty($module_data, 'text_link_hover_color.value.color')) {
                $global_style[] = "#journal-header-notice-{$module} a:hover { color: " . Journal2Utils::getColor($color) . "}";
            }

            /* button colors */
            if ($color = Journal2Utils::getProperty($module_data, 'button_color.value.color')) {
                $global_style[] = "#journal-header-notice-{$module} .close-notice { color: " . Journal2Utils::getColor($color) . "}";
            }
            if ($color = Journal2Utils::getProperty($module_data, 'button_hover_color.value.color')) {
                $global_style[] = "#journal-header-notice-{$module} .close-notice:hover { color: " . Journal2Utils::getColor($color) . "}";
            }
            if ($color = Journal2Utils::getProperty($module_data, 'button_bg_color.value.color')) {
                $global_style[] = "#journal-header-notice-{$module} .close-notice { background-color: " . Journal2Utils::getColor($color) . "}";
            }
            if ($color = Journal2Utils::getProperty($module_data, 'button_hover_bg_color.value.color')) {
                $global_style[] = "#journal-header-notice-{$module} .close-notice:hover { background-color: " . Journal2Utils::getColor($color) . "}";
            }

            $this->data['global_style'] = $global_style;

            $this->template = 'journal2/module/header_notice.tpl';

            if (self::$CACHEABLE === true) {
                $html = Minify_HTML::minify($this->render(), array(
                    'xhtml' => false,
                    'jsMinifier' => 'j2_js_minify'
                ));
                $this->journal2->cache->set($cache_property, $html);
                $this->journal2->cache->set($cache_property . '_fonts', json_encode($this->google_fonts));
            }
        } else {
            if ($fonts = $this->journal2->cache->get($cache_property . '_fonts')) {
                $fonts = json_decode($fonts, true);
                if (is_array($fonts)) {
                    foreach ($fonts as $font) {
                        $this->journal2->google_fonts->add($font['name'], $font['subset'], $font['weight']);
                    }
                }
            }
            $this->template = 'journal2/cache/cache.tpl';
            $this->data['cache'] = $cache;
        }

        $this->document->addScript('catalog/view/theme/journal2/lib/jqueryc/jqueryc.js');

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

}
