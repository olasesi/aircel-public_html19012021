<?php
/* @property ModelCatalogManufacturer model_catalog_manufacturer */
/* @property ModelCatalogCategory model_catalog_category */
class ControllerModuleJournal2SideBlocks extends Controller {

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
        $this->load->model('journal2/menu');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.side_blocks_cache');
        }
    }

    public function index($setting) {
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }

        if (Journal2Cache::$mobile_detect->isMobile()) return;

        Journal2::startTimer(get_class($this));

        $this->journal2->html_classes->addClass('side-blocks-active');

        /* get module data from db */
        $module_data = $this->model_journal2_module->getModule($setting['module_id']);
        if (!$module_data || !isset($module_data['module_data']) || !$module_data['module_data']) return;

		$store_id = Journal2Utils::getProperty($module_data, 'module_data.store_id', -1);

		if ($store_id != -1 && $store_id != $this->config->get('config_store_id')) {
			return;
		}

        $cache_property = "module_journal_side_blocks_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            $css = array();

            if (Journal2Utils::getProperty($module_data, 'module_data.position')) {
                $css[] = 'position: ' . Journal2Utils::getProperty($module_data, 'module_data.position');
            }
            if (Journal2Utils::getProperty($module_data, 'module_data.offset_top')) {
                $css[] = 'top: ' . Journal2Utils::getProperty($module_data, 'module_data.offset_top', 0) . 'px';
            }

            $this->data['icon'] = Journal2Utils::getIconOptions2(Journal2Utils::getProperty($module_data, 'module_data.icon'));
            $this->data['icon_bgcolor'] = 'transparent';

            if (Journal2Utils::getProperty($module_data, 'module_data.icon_bg_color')) {
                $this->data['icon_bgcolor'] = Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'module_data.icon_bg_color'));
            }
            $this->data['icon_border'] = implode('; ', Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'module_data.icon_border')));
            $this->data['content_bgcolor'] = 'transparent';

            $position = Journal2Utils::getProperty($module_data, 'module_data.alignment', 'left');

            switch (Journal2Utils::getProperty($module_data, 'module_data.module_type', 'block')) {
                case 'button':
                    $this->data['type'] = 'button';
                    $this->data['url'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($module_data, 'module_data.link'));
                    $this->data['target'] = Journal2Utils::getProperty($module_data, 'module_data.new_window') ? 'target="_blank"' : '';
                    if (Journal2Utils::getProperty($module_data, 'module_data.icon_bg_hover_color')) {
                        $this->data['icon_bg_hover_color'] = Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'module_data.icon_bg_hover_color'));
                    } else {
                        $this->data['icon_bg_hover_color'] = false;
                    }
                    $offset = Journal2Utils::getProperty($module_data, 'module_data.offset_side', 0) . 'px';
                    $css[] = "{$position}: {$offset}";
                    break;
                case 'block':
                    $this->data['type'] = 'block';
                    $this->data['url'] = 'index.php?route=module/journal2_side_blocks/load&amp;module_id=' . $setting['module_id'];
                    $this->data['content_padding'] = Journal2Utils::getProperty($module_data, 'module_data.content_padding', 0) . 'px';
                    if (Journal2Utils::getProperty($module_data, 'module_data.content_width', 300)) {
                        $css[] = 'width: ' . Journal2Utils::getProperty($module_data, 'module_data.content_width', 300) . 'px';
                        $css[] = (Journal2Utils::getProperty($module_data, 'module_data.alignment') === 'left' ? 'left' : 'right') . ': -' . Journal2Utils::getProperty($module_data, 'module_data.content_width', 300) . 'px';
                    }
                    if (Journal2Utils::getProperty($module_data, 'module_data.content_bg_color')) {
                        $this->data['content_bgcolor'] = Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'module_data.content_bg_color'));
                    }
                    break;
            }

            $this->data['icon_width'] = Journal2Utils::getProperty($module_data, 'module_data.icon_width', 50) . 'px';
            $this->data['icon_height'] = Journal2Utils::getProperty($module_data, 'module_data.icon_height', 50) . 'px';
            if ($position === 'left') {
                $this->data['pos_offset'] = 'right: -' . $this->data['icon_width'];
            } else {
                $this->data['pos_offset'] = 'left: -' . $this->data['icon_width'];
            }

            $this->data['module'] = $module;
            $this->data['alignment'] = Journal2Utils::getProperty($module_data, 'module_data.alignment');
            $this->data['css'] = implode('; ', $css);

            $this->template = 'journal2/module/side_blocks.tpl';

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

    public function load() {
        $module_id = isset($this->request->get['module_id']) ? $this->request->get['module_id'] : -1;

        $module_data = $this->model_journal2_module->getModule($module_id);
        if (!$module_data || !isset($module_data['module_data']) || !$module_data['module_data']) return;

        $this->response->setOutput(Journal2Utils::getProperty($module_data, 'module_data.content.' . $this->config->get('config_language_id')));
    }

}
