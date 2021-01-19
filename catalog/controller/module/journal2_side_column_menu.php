<?php
class ControllerModuleJournal2SideColumnMenu extends Controller {

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
		$this->language->load('common/header');
		$this->language->load('account/login');
		$this->language->load('account/logout');
		$this->language->load('account/register');
		$this->language->load('common/footer');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/information');
		$this->load->model('journal2/blog');
		$this->load->model('journal2/menu');
		$this->load->model('journal2/product');
		$this->load->model('tool/image');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.side_column_menu_cache');
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

        $cache_property = "module_journal_side_column_menu_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}_{$hash}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true || $hash === null) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            $this->data['module'] = $module;
            $this->data['class'] = $setting['position'] === 'column_right' ? 'side-category-right' : 'side-category-left';
            $this->data['heading_title'] = Journal2Utils::getProperty($module_data, 'module_data.title.value.' . $this->config->get('config_language_id'));
            $this->data['side_category_type'] = Journal2Utils::getProperty($module_data, 'module_data.side_category_type', 'normal');

            $this->data['button_cart'] = $this->language->get('button_cart');
            $this->data['button_wishlist'] = $this->language->get('button_wishlist');
            $this->data['button_compare'] = $this->language->get('button_compare');

            $this->data['menu_items'] = array();

            $menu_items = Journal2Utils::getProperty($module_data, 'module_data.items', array());
            $menu_items = Journal2Utils::sortArray($menu_items);

            foreach ($menu_items as $key => $menu_item) {
                if (!Journal2Utils::getProperty($menu_item, 'status', 1)) continue;

                $width = Journal2Utils::getProperty($menu_item, 'container_width', '764') . 'px';


                $menu_css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($menu_item, 'background'));

                $menu_css[] = "width: {$width}";

                if ($setting['position'] === 'column_right') {
                    $menu_css[] = "left: -{$width}";
                }

                $menu = array(
                    'name' => '',
                    'href' => '',
                    'class' => '',
                    'items' => array(),
                    'mixed_columns' => array(),
                    'type' => '',
                    'limit' => Journal2Utils::getProperty($menu_item, 'items_limit', 0),
                    'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($menu_item, 'icon')),
                    'hide_text' => Journal2Utils::getProperty($menu_item, 'hide_text'),
					'css'	=> implode('; ', $menu_css)
                );
                $image_width = Journal2Utils::getProperty($menu_item, 'image_width', 250);
                $image_height = Journal2Utils::getProperty($menu_item, 'image_height', 250);
                $image_resize_type = Journal2Utils::getProperty($menu_item, 'image_type', 'fit');

                $this->generateMenuItem($menu, $menu_item, $image_width, $image_height, $image_resize_type);

				if ($menu_item['type'] === 'mixed') {
					switch (Journal2Utils::getProperty($menu_item, 'custom.top.menu_type')) {
						case 'category':
							$category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
							if (!$category_info) continue;
							$menu['name'] = $category_info['name'];
							$menu['href'] = $this->url->link('product/category', 'path=' . $category_info['category_id']);
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'product':
							$product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
							if (!$product_info) continue;
							$menu['name'] = $product_info['name'];
							$menu['href'] = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'manufacturer':
							$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
							if (!$manufacturer_info) continue;
							$menu['name'] = $manufacturer_info['name'];
							$menu['href'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'information':
							$information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
							if (!$information_info) continue;
							$menu['name'] = $information_info['title'];
							$menu['href'] = $this->url->link('information/information', 'information_id=' . $information_info['information_id']);
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'opencart':
							$customer_name = null;
							switch (Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.page')) {
								case 'login':
									$menu_item['custom']['top']['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
									$customer_name = $this->customer->isLogged() ? '{{_customer_}}' : null;
									break;
								case 'register':
									$menu_item['custom']['top']['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
									break;
								default:
							}
							$menu['name'] = $customer_name ? $customer_name : $this->model_journal2_menu->getMenuName($menu_item['custom']['top']['menu_item']['page']);
							$menu['href'] = $this->model_journal2_menu->link($menu_item['custom']['top']['menu_item']['page']);
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'popup':
							$menu['name'] = Journal2Utils::getProperty($menu_item, 'custom.menu_item.name.value.' . $this->config->get('config_language_id'), 'Not Translated');
							$menu['href'] = "javascript:Journal.openPopup('" . Journal2Utils::getProperty($menu_item, 'custom.top.menu_item') . "')";
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'blog_home':
							$menu['name'] = $this->journal2->settings->get('config_blog_settings.title.value.' . $this->config->get('config_language_id'), 'Journal Blog');
							$menu['href'] = $this->url->link('journal2/blog');
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'blog_category':
							$category_info = $this->model_journal2_blog->getCategory(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
							if (!$category_info) continue;
							$menu['name'] = $category_info['name'];
							$menu['href'] = $this->url->link('journal2/blog', 'journal_blog_category_id=' . $category_info['category_id']);
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'blog_post':
							$post_info = $this->model_journal2_blog->getPost(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
							if (!$post_info) continue;
							$menu['name'] = $post_info['name'];
							$menu['href'] = $this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $post_info['post_id']);
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
						case 'custom':
							$menu['name'] = Journal2Utils::getProperty($menu_item, 'custom.menu_item.name.value.' . $this->config->get('config_language_id'), '');
							$menu['href'] = Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.url');
							$menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
							break;
					}

					if ($name_overwrite = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), '')) {
						$menu['name'] = $name_overwrite;
					}
				}

                $this->data['menu_items'][] = $menu;

            }

            $this->template = 'journal2/module/side_column_menu.tpl';

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

        $this->journal2->html_classes->addClass('flyout-active');
        $this->journal2->settings->set('flyout_' . $setting['position']. '_active', true);

		$output = $this->model_journal2_menu->replaceCacheVars($output);

        Journal2::stopTimer(get_class($this));

        return $output;
    }

    private function generateMenuItem (&$menu, $menu_item, $image_width, $image_height, $image_resize_type) {
        $items_limit = Journal2Utils::getProperty($menu_item, 'items_limit', 0);

        switch (Journal2Utils::getProperty($menu_item, 'type')) {
            /* categories menu */
            case 'categories':
                switch (Journal2Utils::getProperty($menu_item, 'categories.render_as', 'megamenu')) {
                    case 'megamenu':
                        $menu['show'] = Journal2Utils::getProperty($menu_item, 'categories.show');
                        switch ($menu['show']) {
                            case 'links':
                                $menu['show_class'] = 'menu-no-image';
                                break;
                            case 'image':
                                $menu['show_class'] = 'menu-no-links';
                                break;
                            default:
                                $menu['show_class'] = '';
                        }
                        $menu['classes'] .= ' menu-image-' . Journal2Utils::getProperty($menu_item, 'categories.image_position', 'right');
                        $menu['type'] = 'mega-menu-categories';
                        $links_type = Journal2Utils::getProperty($menu_item, 'categories.links_type', 'categories');
                        switch (Journal2Utils::getProperty($menu_item, 'categories.type')) {
                            /* existing categories */
                            case 'existing':
                                $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                if (!$parent_category) continue;
                                $menu['name'] = $parent_category['name'];
                                $menu['href'] = $this->url->link('product/category', 'path=' . $parent_category['category_id']);
                                switch ($links_type) {
                                    case 'categories':
                                        $subcategories = $this->model_catalog_category->getCategories(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                        foreach ($subcategories as $subcategory) {
                                            $submenu = array();
                                            $sub_categories = $this->model_catalog_category->getCategories($subcategory['category_id']);
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id'] . '_' . $sub_category['category_id']),
                                                    'image' => Journal2Utils::resizeImage($this->model_tool_image, $sub_category, $image_width, $image_height, $image_resize_type),
                                                    'image_width' => $image_width ? $image_width : 100,
                                                    'image_height' => $image_height ? $image_height : 100,
                                                    'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit')
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $subcategory['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id']),
                                                'items' => $submenu,
                                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $subcategory, $image_width, $image_height, $image_resize_type),
                                                'image_width' => $image_width ? $image_width : 100,
                                                'image_height' => $image_height ? $image_height : 100,
                                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                    case 'products':
                                        $subcategories = $this->model_catalog_category->getCategories(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                        foreach ($subcategories as $subcategory) {
                                            $submenu = array();
                                            $sub_categories = $this->model_journal2_product->getProductsByCategory($subcategory['category_id'], $items_limit ? $items_limit : 5);
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/product', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id'] . '&product_id=' . $sub_category['product_id']),
                                                    'image' => Journal2Utils::resizeImage($this->model_tool_image, $sub_category, $image_width, $image_height, $image_resize_type),
                                                    'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                                    'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                                    'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $subcategory['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id']),
                                                'items' => $submenu,
                                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $subcategory, $image_width, $image_height, $image_resize_type),
                                                'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                                'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                }

                                break;
                            /* custom categories */
                            case 'custom':
                                switch ($links_type) {
                                    case 'categories':
                                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                                        $menu['href'] = 'javascript:;';
                                        foreach (Journal2Utils::getProperty($menu_item, 'categories.items', array()) as $category) {
                                            $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($category, 'data.id'));
                                            if (!$parent_category) continue;
                                            $sub_categories = $this->model_catalog_category->getCategories(Journal2Utils::getProperty($category, 'data.id'));
                                            $submenu = array();
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $sub_category['category_id']),
                                                    'image' => Journal2Utils::resizeImage($this->model_tool_image, $sub_category, $image_width, $image_height, $image_resize_type),
                                                    'image_width' => $image_width ? $image_width : 100,
                                                    'image_height' => $image_height ? $image_height : 100,
                                                    'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit')
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $parent_category['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id']),
                                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $parent_category, $image_width, $image_height, $image_resize_type),
                                                'image_width' => $image_width ? $image_width : 100,
                                                'image_height' => $image_height ? $image_height : 100,
                                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                                'items' => $submenu,
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                    case 'products':
                                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                                        $menu['href'] = 'javascript:;';
                                        foreach (Journal2Utils::getProperty($menu_item, 'categories.items', array()) as $category) {
                                            $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($category, 'data.id'));
                                            if (!$parent_category) continue;
                                            $sub_categories = $this->model_journal2_product->getProductsByCategory(Journal2Utils::getProperty($category, 'data.id'), $items_limit);
                                            $submenu = array();
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/product', 'path=' . $parent_category['category_id'] . '&product_id=' . $sub_category['product_id']),
                                                    'image' => Journal2Utils::resizeImage($this->model_tool_image, $sub_category, $image_width, $image_height, $image_resize_type),
                                                    'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                                    'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                                    'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $parent_category['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id']),
                                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $parent_category, $image_width, $image_height, $image_resize_type),
                                                'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                                'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                                'items' => $submenu,
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                }
                                break;
                        }
                        break;
                    case 'dropdown':
                        $menu['type'] = 'drop-down';
                        switch (Journal2Utils::getProperty($menu_item, 'categories.type')) {
                            /* existing categories */
                            case 'existing':
                                $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                if (!$parent_category) continue;
                                $menu['name'] = $parent_category['name'];
                                $menu['href'] = $this->url->link('product/category', 'path=' . $parent_category['category_id']);
                                $menu['subcategories'] = $this->generateMultiLevelCategoryMenu($parent_category['category_id']);
                                break;
                            /* custom categories */
                            case 'custom':
                                $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                                $menu['href'] = 'javascript:;';
                                $menu['subcategories'] = array();
                                foreach (Journal2Utils::getProperty($menu_item, 'categories.items', array()) as $category) {
                                    $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($category, 'data.id'));
                                    if (!$category_info) continue;
                                    $menu['subcategories'][] = array(
                                        'name' => $category_info['name'],
                                        'href' => $this->url->link('product/category', 'path=' . $category_info['category_id']),
                                        'subcategories' => $this->generateMultiLevelCategoryMenu($category_info['category_id'])
                                    );
                                }
                                break;
                        }
                        if ($name_overwrite = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'))) {
                            $menu['name'] = $name_overwrite;
                        }
                        break;
                }
                break;

            /* products menu */
            case 'products':
                $menu['type'] = 'mega-menu-products';
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                switch (Journal2Utils::getProperty($menu_item, 'products.source')) {
                    /* products from category */
                    case 'category':
                        $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'products.category.id'));
                        if (!$parent_category) continue;
                        $menu['name'] = $parent_category['name'];
                        $menu['href'] = $this->url->link('product/category', 'path=' . $parent_category['category_id']);
                        $products = $this->model_journal2_product->getProductsByCategory($parent_category['category_id'], $items_limit ? $items_limit : 5);
                        foreach ($products as $product) {
                            $menu['items'][] = array(
                                'product_id' => $product['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($product['product_id']),
                                'name' => $product['name'],
                                'href' => $this->url->link('product/product', 'path=' . $parent_category['category_id'] . '&product_id=' . $product['product_id']),
                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $product, $image_width, $image_height, $image_resize_type),
                                'image_width' => $image_width ? $image_width : 100,
                                'image_height' => $image_height ? $image_height : 100,
                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                'price' => $this->getProductPrice($product),
                                'special' => $this->getProductSpecialPrice($product),
                                'rating' => $this->config->get('config_review_status') ? $product['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                'items' => array()
                            );
                        }
                        break;
                    /* products from module */
                    case 'module':
                        $products = array();
                        switch (Journal2Utils::getProperty($menu_item, 'products.module_type')) {
                            case 'featured':
                                $products = $this->model_journal2_product->getFeatured($items_limit ? $items_limit : 5, Journal2Utils::getProperty($menu_item, 'products.featured_module_id'));
                                break;
                            case 'special':
                                $products = $this->model_journal2_product->getSpecials($items_limit ? $items_limit : 5);
                                break;
                            case 'bestseller':
                                $products = $this->model_journal2_product->getBestsellers($items_limit ? $items_limit : 5);
                                break;
                            case 'latest':
                                $products = $this->model_journal2_product->getLatest($items_limit ? $items_limit : 5);
                                break;
                        }
                        $menu['name'] = '';
                        foreach ($products as $product) {
                            $menu['items'][] = array(
                                'product_id' => $product['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($product['product_id']),
                                'name' => $product['name'],
                                'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $product, $image_width, $image_height, $image_resize_type),
                                'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                'price' => $this->getProductPrice($product),
                                'special' => $this->getProductSpecialPrice($product),
                                'rating' => $this->config->get('config_review_status') ? $product['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                'items' => array()
                            );
                        }
                        break;

                    /* products from manufacturer */
                    case 'manufacturer':
                        $manufacturer = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($menu_item, 'products.manufacturer.id'));
                        if (!$manufacturer) continue;
                        $menu['name'] = $manufacturer['name'];
                        $menu['href'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']);
                        $products = $this->model_journal2_product->getProductsByManufacturer($manufacturer['manufacturer_id']);
                        foreach ($products as $product) {
                            $menu['items'][] = array(
                                'product_id' => $product['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($product['product_id']),
                                'name' => $product['name'],
                                'href' => $this->url->link('product/product', '&manufacturer_id=' . $manufacturer['manufacturer_id'] . '&product_id=' . $product['product_id']),
                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $product, $image_width, $image_height, $image_resize_type),
                                'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                'price' => $this->getProductPrice($product),
                                'special' => $this->getProductSpecialPrice($product),
                                'rating' => $this->config->get('config_review_status') ? $product['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                'items' => array()
                            );
                        }
                        break;

                    /* custom products */
                    case 'custom':
                        $products = Journal2Utils::sortArray(Journal2Utils::getProperty($menu_item, 'products.items', array()));
                        foreach ($products as $product) {
                            $result = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($product, 'data.id'));
                            if (!$result) continue;
                            $menu['items'][] = array(
                                'product_id' => $result['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($result['product_id']),
                                'name' => $result['name'],
                                'href' => $this->url->link('product/product', '&product_id=' . $result['product_id']),
                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $result, $image_width, $image_height, $image_resize_type),
                                'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                'price' => $this->getProductPrice($result),
                                'special' => $this->getProductSpecialPrice($result),
                                'rating' => $this->config->get('config_review_status') ? $result['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                                'items' => array()
                            );
                        }
                        $menu['limit'] = PHP_INT_MAX;
                        break;

                    /* random */
                    case 'random':
                        $this->mega_has_random_products = true;
                        $this->load->model('journal2/product');
                        $random_products = $this->model_journal2_product->getRandomProducts($items_limit);
                        foreach ($random_products as $product) {
                            $result = $this->model_catalog_product->getProduct($product['product_id']);
                            if (!$result) continue;
                            $menu['items'][] = array(
                                'product_id' => $result['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($result['product_id']),
                                'name' => $result['name'],
                                'href' => $this->url->link('product/product', '&product_id=' . $result['product_id']),
                                'image' => Journal2Utils::resizeImage($this->model_tool_image, $result, $image_width, $image_height, $image_resize_type),
                                'image_width' => $image_width ? $image_width : $this->config->get('config_image_product_width'),
                                'image_height' => $image_height ? $image_height : $this->config->get('config_image_product_height'),
                                'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                                'price' => $this->getProductPrice($result),
                                'special' => $this->getProductSpecialPrice($result),
                                'rating' => $this->config->get('config_review_status') ? $result['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                                'items' => array()
                            );
                        }
                        break;
                }
                break;

            /* manufacturer menu */
            case 'manufacturers':
                $menu['type'] = 'mega-menu-brands';
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                $manufacturers = array();
                switch (Journal2Utils::getProperty($menu_item, 'manufacturers.type')) {
                    case 'all':
                        $manufacturers = $this->model_catalog_manufacturer->getManufacturers();
                        if ($items_limit > 0) {
                            $manufacturers = array_slice($manufacturers, 0, $items_limit);
                        }
                        break;
                    case 'custom':
                        foreach (Journal2Utils::getProperty($menu_item, 'manufacturers.items', array()) as $manufacturer) {
                            $manufacturers[] = array(
                                'manufacturer_id' => Journal2Utils::getProperty($manufacturer, 'data.id', -1)
                            );
                        }
                }
                $show_name = Journal2Utils::getProperty($menu_item, 'manufacturers.name');
                foreach ($manufacturers as $manufacturer) {
                    $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer['manufacturer_id']);
                    if (!$manufacturer_info) continue;

                    $menu['items'][] = array(
                        'name' => $manufacturer_info['name'],
                        'show'  => Journal2Utils::getProperty($menu_item, 'manufacturers.show', 'both'),
                        'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']),
                        'image' => Journal2Utils::resizeImage($this->model_tool_image, $manufacturer_info, $image_width, $image_height, $image_resize_type),
                        'image_width' => $image_width ? $image_width : 100,
                        'image_height' => $image_height ? $image_height : 100,
                        'dummy' => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $image_width, $image_height, 'fit'),
                        'items' => array()
                    );
                }
                break;

            /* custom menu */
            case 'custom':
                $menu['type'] = 'drop-down';
                $menu['target'] = Journal2Utils::getProperty($menu_item, 'custom.target') ? 'target="_blank"' : '';
                switch (Journal2Utils::getProperty($menu_item, 'custom.top.menu_type')) {
                    case 'category':
                        $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$category_info) continue;
                        $menu['name'] = $category_info['name'];
                        $menu['href'] = $this->url->link('product/category', 'path=' . $category_info['category_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'product':
                        $product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$product_info) continue;
                        $menu['name'] = $product_info['name'];
                        $menu['href'] = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'manufacturer':
                        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$manufacturer_info) continue;
                        $menu['name'] = $manufacturer_info['name'];
                        $menu['href'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'information':
                        $information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$information_info) continue;
                        $menu['name'] = $information_info['title'];
                        $menu['href'] = $this->url->link('information/information', 'information_id=' . $information_info['information_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'opencart':
                        $customer_name = null;
                        switch (Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.page')) {
                            case 'login':
                                $menu_item['custom']['top']['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
                                $customer_name = $this->customer->isLogged() ? '{{_customer_}}' : null;
                                break;
                            case 'register':
                                $menu_item['custom']['top']['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
                                break;
                            default:
                        }
                        $menu['name'] = $customer_name ? $customer_name : $this->model_journal2_menu->getMenuName($menu_item['custom']['top']['menu_item']['page']);
                        $menu['href'] = $this->model_journal2_menu->link($menu_item['custom']['top']['menu_item']['page']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'popup':
                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'custom.menu_item.name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                        $menu['href'] = "javascript:Journal.openPopup('" . Journal2Utils::getProperty($menu_item, 'custom.top.menu_item') ."')";
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'blog_home':
                        $menu['name'] = $this->journal2->settings->get('config_blog_settings.title.value.' . $this->config->get('config_language_id'), 'Journal Blog');
                        $menu['href'] = $this->url->link('journal2/blog');
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'blog_category':
                        $category_info = $this->model_journal2_blog->getCategory(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$category_info) continue;
                        $menu['name'] = $category_info['name'];
                        $menu['href'] = $this->url->link('journal2/blog', 'journal_blog_category_id=' . $category_info['category_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'blog_post':
                        $post_info = $this->model_journal2_blog->getPost(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$post_info) continue;
                        $menu['name'] = $post_info['name'];
                        $menu['href'] = $this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $post_info['post_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'custom':
                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                        $menu['href'] = Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.url');
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                }
                if ($name_overwrite = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'))) {
                    $menu['name'] = $name_overwrite;
                }
                break;

            /* html */
            case 'html':
                $menu['type'] = 'mega-menu-html';
                $menu['name'] = Journal2Utils::getProperty($menu_item, 'html.' . $this->config->get('config_language_id'));
                $menu['html_blocks'] = array();
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                foreach (Journal2Utils::sortArray(Journal2Utils::getProperty($menu_item, 'html_blocks', array())) as $block) {
                    if (!Journal2Utils::getProperty($block, 'status')) continue;
                    $menu['html_blocks'][] = array(
                        'title' => Journal2Utils::getProperty($block, 'title.value.' . $this->config->get('config_language_id'), ''),
                        'text'  => Journal2Utils::getProperty($block, 'text.' . $this->config->get('config_language_id')),
                        'link'  => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($block, 'link'))
                    );
                }
                break;
            /* mixed */
            case 'mixed':
                $menu['type'] = 'mega-menu-mixed';
                $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'));
                $menu['html_blocks'] = array();
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                $columns = Journal2Utils::getProperty($menu_item, 'mixed_columns', array());
                $columns = Journal2Utils::sortArray($columns);

                foreach ($columns as $column) {
                    $image_width = Journal2Utils::getProperty($column, 'image_width', 250);
                    $image_height = Journal2Utils::getProperty($column, 'image_height', 250);
                    $image_resize_type = Journal2Utils::getProperty($column, 'image_type', 'fit');

                    if (!Journal2Utils::getProperty($column, 'status', 1)) continue;
                    $class = Journal2Utils::getProperty($column, 'hide_on_mobile') ? 'hide-on-mobile' : '';
                    if ($class === 'hide-on-mobile' && (Journal2Cache::$mobile_detect->isMobile() || Journal2Cache::$mobile_detect->isTablet()) && $this->journal2->settings->get('responsive_design')) {
                        continue;
                    }
                    if (Journal2Utils::getProperty($column, 'hide_on_desktop', '0') === '1' && !Journal2Cache::$mobile_detect->isMobile()) {
                        continue;
                    }
                    $cms_blocks = array(
                        'top'       => array(),
                        'bottom'    => array()
                    );
                    foreach (Journal2Utils::getProperty($column, 'cms_blocks', array()) as $cms_block) {
                        if (!$cms_block['status']) return;
                        $cms_blocks[Journal2Utils::getProperty($cms_block, 'position', 'top')][] = array(
                            'content'       => Journal2Utils::getProperty($cms_block, 'content.' . $this->config->get('config_language_id')),
                            'sort_order'    => Journal2Utils::getProperty($cms_block, 'sort_order')
                        );
                    }
                    $column_menu = array(
                        'top_cms_blocks' => Journal2Utils::sortArray($cms_blocks['top']),
                        'bottom_cms_blocks' => Journal2Utils::sortArray($cms_blocks['bottom']),
                        'name' => '',
                        'href' => '',
                        'items' => array(),
                        'type' => '',
                        'class' => $class,
                        'width' => Journal2Utils::getProperty($column, 'width', '25') . '%',
                        'classes' => Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($column, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024)),
                        'limit' => Journal2Utils::getProperty($column, 'items_limit', 0),
                        'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($menu_item, 'icon')),
                        'hide_text' => Journal2Utils::getProperty($menu_item, 'hide_text')
                    );
                    $this->generateMenuItem($column_menu, $column, $image_width, $image_height, $image_resize_type);
                    $name_overwrite = Journal2Utils::getProperty($column, 'name.value.' . $this->config->get('config_language_id'));
                    if ($name_overwrite) {
                        $column_menu['name'] = $name_overwrite;
                    }
                    $menu['mixed_columns'][] = $column_menu;
                }
                break;
            /* html block */
            case 'html-block':
                $menu['type'] = 'mega-menu-html-block';
                $menu['name'] = Journal2Utils::getProperty($menu_item, 'html.' . $this->config->get('config_language_id'));
                $menu['html_text'] = Journal2Utils::getProperty($menu_item, 'html_text.' . $this->config->get('config_language_id'));
                break;
        }
    }

	private function generateMenu($items) {
		$items = Journal2Utils::sortArray($items);
		foreach ($items as $key => &$item) {
			$icon = Journal2Utils::getIconOptions($item);
			/* menu href */
			$href = null;
			$name = null;
			$target = $item['target'] ? ' target="_blank"' : '';
			$class = Journal2Utils::getProperty($item, 'hide_on_mobile') ? 'hide-on-mobile' : '';
			if ($class === 'hide-on-mobile' && (Journal2Cache::$mobile_detect->isMobile() || Journal2Cache::$mobile_detect->isTablet()) && $this->journal2->settings->get('responsive_design')) {
				unset($items[$key]);
				continue;
			}
			if (Journal2Utils::getProperty($item, 'hide_on_desktop', '0') === '1' && !Journal2Cache::$mobile_detect->isMobile()) {
				unset($items[$key]);
				continue;
			}
			/* menu type */
			switch ($item['menu']['menu_type']) {
				case 'category':
					$category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
					if (!$category_info) continue;
					$name = $category_info['name'];
					$href = $this->url->link('product/category', 'path=' . $category_info['category_id']);
					break;
				case 'product':
					$product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
					if (!$product_info) continue;
					$name = $product_info['name'];
					$href = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
					break;
				case 'manufacturer':
					$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
					if (!$manufacturer_info) continue;
					$name = $manufacturer_info['name'];
					$href = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
					break;
				case 'information':
					$information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
					if (!$information_info) continue;
					$name = $information_info['title'];
					$href = $this->url->link('information/information', 'information_id=' .  $information_info['information_id']);
					break;
				case 'popup':
					$href = "javascript:Journal.openPopup('{$item['menu']['menu_item']}')";
					break;
				case 'opencart':
					$customer_name = null;
					switch ($item['menu']['menu_item']['page']) {
						case 'login':
							$item['menu']['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
							$customer_name = $this->customer->isLogged() ? '{{_customer_}}' : null;
							break;
						case 'register':
							$item['menu']['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
							break;
						case 'account/wishlist':
							$class .= ' wishlist-total';
							break;
						case 'product/compare':
							$class .= ' compare-total';
						default:
					}
					$name = $customer_name ? $customer_name : $this->model_journal2_menu->getMenuName($item['menu']['menu_item']['page']);
					$href = $this->model_journal2_menu->link($item['menu']['menu_item']['page']);
					break;
				case 'blog_home':
					$name = $this->journal2->settings->get('config_blog_settings.title.value.' . $this->config->get('config_language_id'), 'Journal Blog');
					$href = $this->url->link('journal2/blog');
					break;
				case 'blog_category':
					$category_info = $this->model_journal2_blog->getCategory(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
					if (!$category_info) continue;
					$name = $category_info['name'];
					$href = $this->url->link('journal2/blog', 'journal_blog_category_id=' . $category_info['category_id']);
					break;
				case 'blog_post':
					$post_info = $this->model_journal2_blog->getPost(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
					if (!$post_info) continue;
					$name = $post_info['name'];
					$href = $this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $post_info['post_id']);
					break;
				case 'custom':
					$name = Journal2Utils::getProperty($item, 'name.value.' . $this->config->get('config_language_id'), '');
					$href = Journal2Utils::getProperty($item, 'menu.menu_item.url');
					break;
			}
			$overwrite_name = Journal2Utils::getProperty($item, 'name.value.' . $this->config->get('config_language_id'), '');
			if ($overwrite_name) {
				$name = $overwrite_name;
			}
			if (Journal2Utils::getProperty($item, 'mobile_view') === 'icon') {
				$class .= ' icon-only';
			}
			if (Journal2Utils::getProperty($item, 'mobile_view') === 'text') {
				$class .= ' text-only';
			}
			if (!$href) {
				$class .= ' no-link';
			}
			$class = trim($class);
			$item = array(
				'icon_left' => $icon['left'],
				'icon_right'=> $icon['right'],
				'class'     => $class ? ' class="' . $class .'"' : '',
				'href'      => $href,
				'name'      => $name,
				'target'    => $target,
				'subcategories' => array()
			);
		}
		return $items;
	}

	private function generateMultiLevelCategoryMenu ($category_id, $path = '') {
		$categories = $this->model_catalog_category->getCategories($category_id);
		$path = $path ? $path . '_' . $category_id : $category_id;
		$result = array();
		foreach ($categories as $category) {
			$result[] = array(
				'href'			=> $this->url->link('product/category', 'path=' . $path . '_' . $category['category_id']),
				'new_window'	=> 0,
				'name'			=> $category['name'],
				'sort_order'	=> $category['sort_order'],
				'status'		=> $category['status'],
				'subcategories' => $this->generateMultiLevelCategoryMenu($category['category_id'], $path)
			);
		}
		return $result;
	}

    private function getProductPrice($product) {
        if ((float)$product['price'] && ($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
            return Journal2Utils::currencyFormat($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
        }
        return false;
    }

    private function getProductSpecialPrice($product) {
        if ((float)$product['special']) {
            return Journal2Utils::currencyFormat($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
        }
        return false;
    }

}
