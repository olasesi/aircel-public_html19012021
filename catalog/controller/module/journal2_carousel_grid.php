<?php
/* @property ModelCatalogManufacturer model_catalog_manufacturer */
/* @property ModelCatalogCategory model_catalog_category */
class ControllerModuleJournal2CarouselGrid extends Controller {

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
            //self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.carousel_grid_cache');
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

        /* hide on mobile */
        $disable_mobile = Journal2Utils::getProperty($module_data, 'disable_mobile') && $this->journal2->settings->get('responsive_design');

        if ($disable_mobile && (Journal2Cache::$mobile_detect->isMobile() && !Journal2Cache::$mobile_detect->isTablet())) {
            return;
        }

        /* hide on desktop */
        if (Journal2Utils::getProperty($module_data, 'disable_desktop') && !Journal2Cache::$mobile_detect->isMobile()) {
            return;
        }

        $this->data['disable_mobile'] = $disable_mobile ? 'hide-on-mobile' : '';
        $this->data['height'] = Journal2Utils::getProperty($module_data, 'height');

        $this->data['css'] = array();

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_carousel_grid_' . $setting['module_id'], implode('; ', $css));
            $this->journal2->settings->set('module_journal2_carousel_grid_' . $setting['module_id'] . '_video', Journal2Utils::getVideoBackgroundSettings(Journal2Utils::getProperty($module_data, 'video_background.value.text')));

            /* inner css */
            $css = array();
            if (Journal2Utils::getProperty($module_data, 'fullwidth')) {
                $css[] = 'max-width: 100%';
                $css[] = 'padding-left: ' . $padding;
                $css[] = 'padding-right: ' . $padding;
            } else {
                $css[] = 'max-width: ' . $this->journal2->settings->get('site_width', 1024) . 'px';
            }

            $this->data['css'] = $css;
        }

        $module_spacing = Journal2Utils::getProperty($module_data, 'module_spacing');
        $this->data['module_spacing'] = $module_spacing !== null ? $module_spacing . 'px' : null;

        $this->data['css'] = implode('; ', $this->data['css']);

        $cache_property = "module_journal_carousel_grid_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $this->data['module'] = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            $columns = Journal2Utils::getProperty($module_data, 'columns', array());
            $columns = Journal2Utils::sortArray($columns);

            $this->data['columns'] = array();

            foreach ($columns as $column) {
                if (!$column['status']) continue;

                $module_disable_mobile = Journal2Utils::getProperty($column, 'disable_mobile') && $this->journal2->settings->get('responsive_design');

                if ($module_disable_mobile && (Journal2Cache::$mobile_detect->isMobile() && !Journal2Cache::$mobile_detect->isTablet())) {
                    continue;
                }

                /* hide on desktop */
                if (Journal2Utils::getProperty($column, 'disable_desktop') && !Journal2Cache::$mobile_detect->isMobile()) {
                    return;
                }

                $size = floor($column['width']);

                $this->data['columns'][] = array(
                    'classes'   => "xs-{$size} sm-{$size} md-{$size} lg-{$size} xl-{$size}",
                    'content'   => $this->getChild('module/journal2_carousel', array (
                        'module_id' => Journal2Utils::getProperty($column, 'module_id'),
                        'layout_id' => -1,
                        'position'  => 'multi_module'
                    ))
                );
            }

            $this->template = 'journal2/module/carousel_grid.tpl';

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
