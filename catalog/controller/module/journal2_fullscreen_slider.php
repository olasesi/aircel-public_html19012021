<?php
class ControllerModuleJournal2FullscreenSlider extends Controller {

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

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.fullscreen_slider_cache');
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

            if (Journal2Utils::getProperty($module_data, 'module_data.enable_on_phone', '1') == '0') {
                if ($device === 'phone') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-phone';
                }
            }

            if (Journal2Utils::getProperty($module_data, 'module_data.enable_on_tablet', '1') == '0') {
                if ($device === 'tablet') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-tablet';
                }
            }

            if (Journal2Utils::getProperty($module_data, 'module_data.enable_on_desktop', '1') == '0') {
                if ($device === 'desktop') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-desktop';
                }
            }
        }

        $cache_property = "module_journal_fullscreen_slider_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            $this->data['module'] = $module;
            $this->data['transition'] = Journal2Utils::getProperty($module_data, 'module_data.transition', 'fade');
            $this->data['transition_speed'] = Journal2Utils::getProperty($module_data, 'module_data.transition_speed', '700');
            $this->data['transition_delay'] = Journal2Utils::getProperty($module_data, 'module_data.transition_delay', '3000');

            if (Journal2Utils::getProperty($module_data, 'module_data.transparent_overlay', '')) {
                $this->data['transparent_overlay'] = Journal2Utils::resizeImage($this->model_tool_image, Journal2Utils::getProperty($module_data, 'module_data.transparent_overlay', ''));
            } else {
                $this->data['transparent_overlay'] = '';
            }

            $this->data['images'] = array();

            $images = Journal2Utils::getProperty($module_data, 'module_data.images', array());
            $images = Journal2Utils::sortArray($images);

            foreach ($images as $image) {
                if (!$image['status']) continue;
                $image = Journal2Utils::getProperty($image, 'image');
                if (is_array($image)) {
                    $image = Journal2Utils::getProperty($image, $this->config->get('config_language_id'));
                }
                $this->data['images'][] = array(
                    'image' => Journal2Utils::resizeImage($this->model_tool_image, $image),
                    'title' => ''
                );
            }

            $this->template = 'journal2/module/fullscreen_slider.tpl';

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

        $this->document->addStyle('catalog/view/theme/journal2/lib/supersized/css/supersized.css');
        $this->document->addScript('catalog/view/theme/journal2/lib/supersized/js/jquery.easing.min.js');
        $this->document->addScript('catalog/view/theme/journal2/lib/supersized/js/supersized.3.2.7.min.js');

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

}