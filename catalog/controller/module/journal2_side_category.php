<?php
class ControllerModuleJournal2SideCategory extends Controller {

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
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('catalog/information');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.side_category_cache');
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

        if ($this->journal2->settings->get('responsive_design')) {
            $device = Journal2Utils::getDevice();

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

        $hash = isset($this->request->server['REQUEST_URI']) ? md5($this->request->server['REQUEST_URI']) : null;

        $cache_property = "module_journal_side_category_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}_{$hash}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true || $hash === null) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            $this->data['module'] = $module;
            $this->data['class'] = $setting['position'] === 'column_right' ? 'side-category-right' : 'side-category-left';
            $this->data['type'] = Journal2Utils::getProperty($module_data, 'module_data.type');

            switch ($this->data['type']) {
                case 'accordion':
                    $this->data['class'] .= ' side-category-accordion';
                    break;
                case 'dropdown':
                    $this->data['class'] .= ' side-category-dropdown';
                    break;
            }

            $this->data['heading_title'] = Journal2Utils::getProperty($module_data, 'module_data.title.value.' . $this->config->get('config_language_id'), 'Categories');

            $tree = array();

            $sql  = "SELECT c.category_id, c.parent_id, cd.name ";

            if ($this->config->get('config_product_count')) {
                $sql  .= ", (SELECT COUNT(p.product_id) FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)  WHERE p.status = '1' AND p.date_available <= NOW()  AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2c.category_id = c.category_id) as total ";
            }

            $sql .= "FROM " . DB_PREFIX . "category c  LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)  LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)  WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'   AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)";


            $query = $this->db->query($sql);

            $results = array();

            foreach ($query->rows as $row) {
                $results[$row['parent_id']][] = $row;
            }

            if (isset($this->request->get['path'])) {
                $parts = explode('_', (string)$this->request->get['path']);
            } else {
                $parts = array();
            }

            if (Journal2Utils::getProperty($module_data, 'module_data.show_categories')) {
                if (is_array($results) && isset($results['0'])) {
                    $tree = $this->generateMultiLevelCategoryMenu($results, $results[0], '', $parts);

                    if ($this->config->get('config_product_count')) {
                        for ($i = 0; $i < count($tree); $i++) {
                            $this->sum($tree[$i]);
                        }
                    }
                }
            }

            $top_items = array();
            $bottom_items = array();

            foreach (Journal2Utils::getProperty($module_data, 'module_data.sections', array()) as $item_data) {
                if (Journal2Utils::getProperty($item_data, 'type', 'custom') === 'custom') {
                    $m = $this->getMenuName($item_data);
                    $item = array(
                        'type'       => 'custom',
                        'name'       => $m['name'],
                        'href'       => $m['href'],
                        'target'     => Journal2Utils::getProperty($item_data, 'new_window') ? 'target="_blank"' : '',
                        'sort_order' => Journal2Utils::getProperty($item_data, 'sort_order')
                    );
                } else {
                    $category_id = Journal2Utils::getProperty($item_data, 'category.data.id');
                    if (is_array($results) && isset($results[$category_id])) {
                        $tree2 = $this->generateMultiLevelCategoryMenu($results, $results[$category_id], $category_id, $parts);

                        $total = 0;
                        if ($this->config->get('config_product_count')) {
                            for ($i = 0; $i < count($tree2); $i++) {
                                $total += $this->sum($tree2[$i]);
                            }
                        }

                        $name = Journal2Utils::getProperty($item_data, 'name.value.' . $this->config->get('config_language_id'));

                        if (!$name) {
                            $category_info = $this->model_catalog_category->getCategory($category_id);
                            $name = $category_info['name'];
                        }

                        $item = array(
                            'type'          => 'category',
                            'href'          => $this->url->link('product/category', 'path=' . $category_id),
                            'name'          => $name,
                            'total'         => $total,
                            'subcategories' => $tree2
                        );
                    } else {
                        $name = Journal2Utils::getProperty($item_data, 'name.value.' . $this->config->get('config_language_id'));

                        if (!$name) {
                            $category_info = $this->model_catalog_category->getCategory($category_id);
                            $name = $category_info['name'];
                        }

                        if ($this->config->get('config_product_count')) {
                            $total = $this->model_catalog_product->getTotalProducts(array('filter_category_id' => $category_id));
                        } else {
                            $total = 0;
                        }

                        $item = array(
                            'type'          => 'category',
                            'name'          => $name,
                            'href'          => $this->url->link('product/category', 'path=' . $category_id),
                            'total'         => $total,
                            'sort_order'    => Journal2Utils::getProperty($item_data, 'sort_order'),
                            'subcategories' => array(),
                        );
                    }
                }

                $item['class'] = $this->isActive($item['href']);

                if (Journal2Utils::getProperty($item_data, 'position') === 'top') {
                    $top_items[] = $item;
                } else {
                    $bottom_items[] = $item;
                }
            }

            $top_items = Journal2Utils::sortArray($top_items);
            $bottom_items = Journal2Utils::sortArray($bottom_items);

            $this->data['categories'] = $tree;
            $this->data['top_items'] = $top_items;
            $this->data['bottom_items'] = $bottom_items;
            $this->data['show_total'] = $this->config->get('config_product_count');

            $this->template = 'journal2/module/side_category.tpl';

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

        $output = $this->model_journal2_menu->replaceCacheVars($output);

        Journal2::stopTimer(get_class($this));

        return $output;
    }

    private function generateMultiLevelCategoryMenu(&$list, $parent, $path, $parts){
        $tree = array();

        foreach ($parent as $key => $value) {
            $temp = $path . ($path == '' ? $value['category_id'] : '_' . $value['category_id']);
            $value['href'] = $this->url->link('product/category', 'path=' . $temp);
            $value['class'] = in_array($value['category_id'], $parts) ? 'active' : '';

            if (isset($list[$value['category_id']])) {
                $value['subcategories'] = $this->generateMultiLevelCategoryMenu($list, $list[$value['category_id']], $temp, $parts);
            } else {
                $value['subcategories'] = array();
            }
            $tree[] = $value;
        }

        return $tree;
    }

    private function sum(&$tree){
        if (isset($tree['subcategories'])) {
            foreach ($tree['subcategories'] as $level => $child){
                $tree['total'] += $this->sum($tree['subcategories'][$level]);
            }
        }
        return $tree['total'];
    }

    private function isActive($url) {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        return trim(str_replace('index.php?route=common/home', '', $current_url), '/') === trim($url, '/') ? 'active' : '';
    }

    private function getMenuName($menu_item) {
        $href = '';
        $name = '';

        switch ($menu_item['link']['menu_type']) {
            case 'category':
                $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'link.menu_item.id', -1));
                if (!$category_info) continue;
                $name = $category_info['name'];
                $href = $this->url->link('product/category', 'path=' . $category_info['category_id']);
                break;
            case 'product':
                $product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($menu_item, 'link.menu_item.id', -1));
                if (!$product_info) continue;
                $name = $product_info['name'];
                $href = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
                break;
            case 'manufacturer':
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($menu_item, 'link.menu_item.id', -1));
                if (!$manufacturer_info) continue;
                $name = $manufacturer_info['name'];
                $href = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
                break;
            case 'information':
                $information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($menu_item, 'link.menu_item.id', -1));
                if (!$information_info) continue;
                $name = $information_info['title'];
                $href = $this->url->link('information/information', 'information_id=' .  $information_info['information_id']);
                break;
            case 'popup':
                $name = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                $href = "javascript:Journal.openPopup('{$menu_item['link']['menu_item']}')";
                break;
            case 'opencart':
                $customer_name = null;
                switch ($menu_item['link']['menu_item']['page']) {
                    case 'login':
                        $menu_item['link']['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
                        $customer_name = $this->customer->isLogged() ? '{{_customer_}}' : null;
                        break;
                    case 'register':
                        $menu_item['link']['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
                        break;
                    case 'account/wishlist':
                        $disable_on_classes[] = 'wishlist-total';
                        break;
                    case 'product/compare':
                        $disable_on_classes[] = 'compare-total';
                    default:
                }
                $name = $customer_name ? $customer_name : $this->model_journal2_menu->getMenuName($menu_item['link']['menu_item']['page']);
                $href = $this->model_journal2_menu->link($menu_item['link']['menu_item']['page']);
                break;
            case 'blog_home':
                $name = $this->journal2->settings->get('config_blog_settings.title.value.' . $this->config->get('config_language_id'), 'Journal Blog');
                $href = $this->url->link('journal2/blog');
                break;
            case 'blog_category':
                $category_info = $this->model_journal2_blog->getCategory(Journal2Utils::getProperty($menu_item, 'link.menu_item.id', -1));
                if (!$category_info) continue;
                $name = $category_info['name'];
                $href = $this->url->link('journal2/blog', 'journal_blog_category_id=' . $category_info['category_id']);
                break;
            case 'blog_post':
                $post_info = $this->model_journal2_blog->getPost(Journal2Utils::getProperty($menu_item, 'link.menu_item.id', -1));
                if (!$post_info) continue;
                $name = $post_info['name'];
                $href = $this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $post_info['post_id']);
                break;
            case 'custom':
                $name = '';
                $href = Journal2Utils::getProperty($menu_item, 'link.menu_item.url');
                break;
        }

        if ($n = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), '')) {
            $name = $n;
        }

        return array(
            'name' => $name,
            'href' => $href
        );
    }

}
