<?php
class ControllerJournal2ProductTabs extends Controller {

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
        $this->load->model('journal2/module');
        $this->load->model('catalog/product');
    }

    public function index() {
        if ($this->journal2->page->getType() !== 'product' && $this->journal2->page->getType() !== 'quickview') return;

        Journal2::startTimer('ProductTabs');

        $product_id = (int)$this->journal2->page->getId();

        /* recently viewed */
        $recently_viewed = isset($this->request->cookie['jrv']) && $this->request->cookie['jrv'] ? explode(',', $this->request->cookie['jrv']) : array();
        $recently_viewed = array_diff($recently_viewed, array($product_id));
        array_unshift($recently_viewed, $product_id);
        $limit = (int)$this->config->get(version_compare(VERSION, '2', '>=') ? 'config_product_limit' : 'config_catalog_limit');
        if (!$limit) {
            $limit = 50;
        }
        $recently_viewed = array_splice($recently_viewed, 0, $limit);
        setcookie('jrv', implode(',', $recently_viewed), time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);

        $product_info = $this->model_catalog_product->getProduct($product_id);

        $tabs = $this->model_journal2_module->getProductTabs($product_id, $product_info);
        $tabs = Journal2Utils::sortArray($tabs);

        $tab_tab = array();
        $tab_desc_top = array();
        $tab_desc_bottom = array();
        $tab_image = array();
        $tab_enquiry = array();

        foreach ($tabs as $tab) {
            if (!$tab['status']) continue;

            if ($this->journal2->settings->get('responsive_design')) {
                $device = Journal2Utils::getDevice();

                if (Journal2Utils::getProperty($tab, 'enable_on_phone', '1') == '0') {
                    if ($device === 'phone') {
                        continue;
                    }
                }

                if (Journal2Utils::getProperty($tab, 'enable_on_tablet', '1') == '0') {
                    if ($device === 'tablet') {
                        continue;
                    }
                }

                if (Journal2Utils::getProperty($tab, 'enable_on_desktop', '1') == '0') {
                    if ($device === 'desktop') {
                        continue;
                    }
                }
            }

            $css = array();

            if (Journal2Utils::getColor(Journal2Utils::getProperty($tab, 'icon_bg_color.value.color'))) {
                $css[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($tab, 'icon_bg_color.value.color'));
            }
            if (Journal2Utils::getProperty($tab, 'icon_width')) {
                $css[] = 'width: ' . Journal2Utils::getProperty($tab, 'icon_width') . 'px';
            }
            if (Journal2Utils::getProperty($tab, 'icon_height')) {
                $css[] = 'height: ' . Journal2Utils::getProperty($tab, 'icon_height') . 'px';
                $css[] = 'line-height: ' . Journal2Utils::getProperty($tab, 'icon_height') . 'px';
            }
            if (Journal2Utils::getProperty($tab, 'icon_border')) {
                $css = array_merge($css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($tab, 'icon_border')));
            }

            $position       = Journal2Utils::getProperty($tab, 'position');
            $name           = Journal2Utils::getProperty($tab, 'name.value.' . $this->config->get('config_language_id'));
            $has_icon       = Journal2Utils::getProperty($tab, 'icon_status');
            $icon           = Journal2Utils::getIconOptions2(Journal2Utils::getProperty($tab, 'icon'));
            $icon_css       = implode('; ', $css);

            switch (Journal2Utils::getProperty($tab, 'content_type', 'custom')) {
                case 'custom':
                    $content = Journal2Utils::getProperty($tab, 'content.' . $this->config->get('config_language_id'));
                    break;
                case 'description':
                    $content = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
                    $this->journal2->settings->set('hide_product_description', true);
                    break;
                case 'enquiry':
                    $position = 'enquiry';
                    $this->journal2->settings->set('hide_add_to_cart_button', true);
                    $href = "javascript:Journal.openPopup('" . (int)Journal2Utils::getProperty($tab, 'popup') . "', '" . $product_id . "')";
                    $content = "<a class=\"button enquiry-button\" href=\"{$href}\">{$icon}{$name}</a>";
                    break;
            }

            $position_desc = $position === 'desc' ? '_' . Journal2Utils::getProperty($tab, 'option_position') : '';

            $data = array(
                'name'          => $name,
                'has_icon'      => $has_icon,
                'icon'          => $icon,
                'icon_css'      => $icon_css,
                'content'       => $content
            );

            $var = 'tab_' . $position . $position_desc;
            array_push($$var, $data);
        }

        $this->journal2->settings->set('additional_product_tabs', $tab_tab);
        $this->journal2->settings->set('additional_product_description_top', $tab_desc_top);
        $this->journal2->settings->set('additional_product_description_bottom', $tab_desc_bottom);
        $this->journal2->settings->set('additional_product_description_image', $tab_image);
        $this->journal2->settings->set('additional_product_enquiry', $tab_enquiry);

        Journal2::stopTimer('ProductTabs');
    }

    public function enquiry() {
        Journal2::startTimer('ProductTabs');
        $this->journal2->settings->set('enquiry_products', $this->model_journal2_module->getEnquiryProducts());
        Journal2::stopTimer('ProductTabs');
    }

}
?>