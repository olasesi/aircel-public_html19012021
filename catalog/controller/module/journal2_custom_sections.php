<?php
class ControllerModuleJournal2CustomSections extends Controller {

    private static $CACHEABLE = null;
    private $has_random_products = false;
    private $has_items = false;

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
        $this->load->model('journal2/product');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer');
        $this->load->model('tool/image');
        $this->load->language('product/category');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.custom_sections_cache');
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
            $this->journal2->settings->set('module_journal2_custom_sections_' . $setting['module_id'], implode('; ', $css));
            $this->journal2->settings->set('module_journal2_custom_sections_' . $setting['module_id'] . '_classes', implode(' ', $this->data['disable_on_classes']));
            $this->journal2->settings->set('module_journal2_custom_sections_' . $setting['module_id'] . '_video', Journal2Utils::getVideoBackgroundSettings(Journal2Utils::getProperty($module_data, 'video_background.value.text')));

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

        $this->data['spacing'] = Journal2Utils::getProperty($module_data, 'spacing', 0);

        $cache_property = "module_journal_custom_sections_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}" . $this->journal2->cache->getRouteCacheKey();

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            /* set global module properties */
            $this->data['module'] = $module;
            $this->data['show_title'] = Journal2Utils::getProperty($module_data, 'show_title');
            $this->data['show_title_class'] = $this->data['show_title'] ? '' : 'no-heading';
            $this->data['brand_name'] = Journal2Utils::getProperty($module_data, 'brand_name');
            $this->data['module_type'] = Journal2Utils::getProperty($module_data, 'module_type');
            $this->data['render_as'] = Journal2Utils::getProperty($module_data, 'display_as');
            $this->data['default_section'] = '';

            /* generate sections */
            $this->data['sections'] = array();
            $this->data['items'] = array();

            /* image dimensions */
            $this->data['image_width'] = Journal2Utils::getProperty($module_data, 'image_width', $this->config->get('config_image_product_width'));
            $this->data['image_height'] = Journal2Utils::getProperty($module_data, 'image_height', $height = $this->config->get('config_image_product_height'));
            $this->data['image_resize_type'] = Journal2Utils::getProperty($module_data, 'image_type', 'fit');

            /* image border */
            if (Journal2Utils::getProperty($module_data, 'image_border')) {
                $this->data['image_border_css'] = implode('; ', Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'image_border')));
            } else {
                $this->data['image_border_css'] = null;
            }

            /* image background color */
            if (Journal2Utils::getProperty($module_data, 'image_bgcolor.value.color')) {
                $this->data['image_bgcolor'] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'image_bgcolor.value.color'));
            } else {
                $this->data['image_bgcolor'] = null;
            }

            switch ($this->data['module_type']) {
                case 'product':
                    $sections = Journal2Utils::getProperty($module_data, 'product_sections', array());
                    $sections = Journal2Utils::sortArray($sections);
                    $this->generateProductSections($sections);
                    $this->data['text_tax'] = $this->language->get('text_tax');
                    $this->data['button_cart'] = $this->language->get('button_cart');
                    $this->data['button_wishlist'] = $this->language->get('button_wishlist');
                    $this->data['button_compare'] = $this->language->get('button_compare');
                    break;
                case 'category':
                    $sections = Journal2Utils::getProperty($module_data, 'category_sections', array());
                    $sections = Journal2Utils::sortArray($sections);
                    $this->generateCategorySections($sections);
                    break;
                case 'manufacturer':
                    $sections = Journal2Utils::getProperty($module_data, 'manufacturer_sections', array());
                    $sections = Journal2Utils::sortArray($sections);
                    $this->generateManufacturerSections($sections);
                    break;
            }

            if (!$this->has_items) {
                return;
            }

            $columns = in_array($setting['position'], array('top', 'bottom')) ? 0 : $this->journal2->settings->get('config_columns_count', 0);
            $this->data['single_class'] = count($this->data['sections']) == 1 ? 'single-section' : '';
            $this->data['grid_classes'] = Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($module_data, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024), $columns);

            $this->template = "journal2/module/custom_sections_{$this->data['module_type']}.tpl";

            if (self::$CACHEABLE === true && !$this->has_random_products) {
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

        $this->document->addScript('catalog/view/theme/journal2/lib/isotope/imagesloaded.min.js');
        $this->document->addScript('catalog/view/theme/journal2/lib/isotope/jquery.isotope.min.js');

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

    private function generateProductSections($sections_data) {
        $section_index = 0;

		$product_id = Journal2Utils::getProperty($this->request->get, 'product_id');
		$post_id 	= Journal2Utils::getProperty($this->request->get, 'journal_blog_post_id');

        foreach ($sections_data as $section) {
            if (!$section['status']) continue;

            $products = array();
            $section_products = array();
            $section_name = Journal2Utils::getProperty($section, 'section_title.value.' . $this->config->get('config_language_id'), 'Not Translated');
            $todays_specials =
                Journal2Utils::getProperty($section, 'section_type') === 'module' &&
                Journal2Utils::getProperty($section, 'module_type') === 'specials' &&
                Journal2Utils::getProperty($section, 'todays_specials_only') === '1';
            $limit = $todays_specials ? PHP_INT_MAX : Journal2Utils::getProperty($section, 'items_limit', 5);

            switch (Journal2Utils::getProperty($section, 'section_type')) {
                case 'module':
                    switch (Journal2Utils::getProperty($section, 'module_type')) {
                        case 'featured':
                            $products = $this->model_journal2_product->getFeatured($limit, Journal2Utils::getProperty($section, 'featured_module_id'), Journal2Utils::getProperty($section, 'filter_category', 0) !== null);
                            break;
                        case 'bestsellers':
                            $products = $this->model_journal2_product->getBestsellers($limit, Journal2Utils::getProperty($section, 'filter_category', 0) !== null);
                            break;
                        case 'specials':
                            $products = $this->model_journal2_product->getSpecials($limit, Journal2Utils::getProperty($section, 'filter_category', 0) !== null);
                            break;
                        case 'latest':
                            $products = $this->model_journal2_product->getLatest($limit, Journal2Utils::getProperty($section, 'filter_category', 0) !== null);
                            break;
						case 'related':
							if ($product_id) {
								$products = $this->model_journal2_product->getProductRelated($product_id, $limit);
							} else if ($post_id) {
								$products = $this->model_journal2_blog->getRelatedProducts($post_id, $limit);
							} else {
								$products = array();
							}
                            $this->has_random_products = true;
							break;
                        case 'people-also-bought':
                            $products = $this->model_journal2_product->getPeopleAlsoBought($product_id, $limit);
                            break;
                        case 'most-viewed':
                            $products = $this->model_journal2_product->getMostViewed($limit);
                            $this->has_random_products = true;
                            break;
                        case 'recently-viewed':
                            $products = $this->model_journal2_product->getRecentlyViewed($limit);
                            $this->has_random_products = true;
                            break;
                    }
                    break;
                case 'category':
                    $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($section, 'category.data.id'));
                    if (!$category_info) continue;
                    $products = $this->model_journal2_product->getProductsByCategory($category_info['category_id'], $limit);
                    break;
                case 'manufacturer':
                    $manufacturer = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($section, 'manufacturer.data.id'));
                    if (!$manufacturer) continue;
                    $products = $this->model_journal2_product->getProductsByManufacturer($manufacturer['manufacturer_id'], $limit);
                    break;
                case 'custom':
                    foreach (Journal2Utils::sortArray(Journal2Utils::getProperty($section, 'products', array())) as $product) {
                        $result = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($product, 'data.id'));
                        if (!$result) continue;
                        $products[] = $result;
                    }
                    break;
                case 'random':
                    $this->has_random_products = true;
                    $random_type = Journal2Utils::getProperty($section, 'random_from', 'all');
                    $category_id = $random_type === 'category' ? Journal2Utils::getProperty($section, 'random_from_category.id', -1) : -1;
                    $random_products = $this->model_journal2_product->getRandomProducts($limit, $category_id);
                    foreach ($random_products as $product) {
                        $result = $this->model_catalog_product->getProduct($product['product_id']);
                        if (!$result) continue;
                        $products[] = $result;
                    }
                    break;
            }

            foreach ($products as $product) {
                $this->has_items = true;
                $image = Journal2Utils::resizeImage($this->model_tool_image, $product['image'] ? $product['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']);

                if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                    $price = Journal2Utils::currencyFormat($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                } else {
                    $price = false;
                }

                if ((float)$product['special']) {
                    $special = Journal2Utils::currencyFormat($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = Journal2Utils::currencyFormat((float)$product['special'] ? $product['special'] : $product['price']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product['rating'];
                } else {
                    $rating = false;
                }

                $product_sections = isset($this->data['items'][$product['product_id']]) ? $this->data['items'][$product['product_id']]['section_class'] : array();
                $product_sections[$section_index] = 'section-' . $section_index;

                $results = $this->model_catalog_product->getProductImages($product['product_id']);

                $image2 = false;

                if (!$this->journal2->html_classes->hasClass('mobile') && count($results) > 0) {
                    $image2 = Journal2Utils::resizeImage($this->model_tool_image, $results[0]['image'] ? $results[0]['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']);
                }

                $date_end = false;
                if ($special) {
                    $date_end = $this->model_journal2_product->getSpecialCountdown($product['product_id']);
                    if ($todays_specials && date('Y-m-d', strtotime(date('Y-m-d') . "+1 days")) !== date('Y-m-d', strtotime($date_end))) {
                        continue;
                    }
                    if ($date_end === '0000-00-00') {
                        $date_end = false;
                    }
                    if ($this->journal2->settings->get('show_countdown', 'never') === 'never') {
                        $date_end = false;
                    }
                }

                $cls = explode(' ', Journal2Utils::getProperty($this->data, 'items.' . $product['product_id'] . '.classes'));

                if (Journal2Utils::getProperty($section, 'section_type') === 'module' && Journal2Utils::getProperty($section, 'module_type') === 'specials'
                    && Journal2Utils::getProperty($section, 'countdown_visibility', '0') == '1') {
                    $cls['countdown-on'] = 'countdown-on';
                }

                $product_data = array(
                    'product_id'    => $product['product_id'],
                    'section_class' => $product_sections,
                    'classes'       => implode(' ', $cls),
                    'thumb'         => $image,
                    'thumb2'        => $image2,
                    'name'          => $product['name'],
					'minimum'		=> $product['minimum'] > 0 ? $product['minimum'] : 1,
                    'price'         => $price,
                    'special'       => $special,
                    'date_end'      => $date_end,
                    'rating'        => $rating,
                    'description'   => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
                    'tax'           => $tax,
                    'reviews'       => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                    'href'          => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                    'labels'        => $this->model_journal2_product->getLabels($product['product_id']),
                    'from_abroad'   => $product['from_abroad']
                );

                $this->data['items'][$product['product_id']] = $product_data;
                $section_products[] = $product_data;
            }

            $this->data['sections'][] = array(
                'section_class' => $section_index,
                'section_name'  => $section_name,
                'items'         => $section_products,
                'is_link'       => Journal2Utils::getProperty($section, 'section_type')  === 'link',
                'url'           => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($section, 'link')),
                'target'        => Journal2Utils::getProperty($section, 'new_window') ? 'target="_blank"' : ''
            );

            if ($section_products && Journal2Utils::getProperty($section, 'default_section')) {
                $this->data['default_section'] = $section_index;
            }

            $section_index++;
        }
    }

    private function generateCategorySections($sections_data) {
        $section_index = 0;

        foreach ($sections_data as $section) {
            if (!$section['status']) continue;

            $categories = array();
            $section_categories = array();
            $section_name = Journal2Utils::getProperty($section, 'section_title.value.' . $this->config->get('config_language_id'), 'Not Translated');
            $parent_id = '';

            switch (Journal2Utils::getProperty($section, 'section_type')) {
                case 'top':
                    $categories = $this->model_catalog_category->getCategories();
                    break;
                case 'sub':
                    $cid = Journal2Utils::getProperty($section, 'category_sub.id', -1);
                    if ($cid !== -1) {
                        $parent_id = $cid . '_';
                        $categories = $this->model_catalog_category->getCategories($cid);
                    }
                    break;
                case 'custom':
                    foreach (Journal2Utils::getProperty($section, 'categories', array()) as $category) {
                        $categories[] = array(
                            'category_id' => Journal2Utils::getProperty($category, 'data.id', -1)
                        );
                    }
            }

            $limit = Journal2Utils::getProperty($section, 'items_limit', 5);
            $index = 0;

            foreach ($categories as $category) {
                $this->has_items = true;
                $category_info = $this->model_catalog_category->getCategory($category['category_id']);
                if (!$category_info) continue;

                if ($index++ >= $limit) break;

                $category_sections = isset($this->data['items'][$category_info['category_id']]) ? $this->data['items'][$category_info['category_id']]['section_class'] : array();
                $category_sections[$section_index] = 'section-' . $section_index;

                $category_data = array(
                    'section_class' => $category_sections,
                    'name'          => $category_info['name'],
                    'href'          => $this->url->link('product/category', 'path=' . $parent_id . $category_info['category_id']),
                    'thumb'         => Journal2Utils::resizeImage($this->model_tool_image, $category_info['image'] ? $category_info['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type'])
                );

                $this->data['items'][$category_info['category_id']] = $category_data;
                $section_categories[] = $category_data;
            }

            $this->data['sections'][] = array(
                'section_class' => $section_index,
                'section_name'  => $section_name,
                'items'         => $section_categories,
                'is_link'       => Journal2Utils::getProperty($section, 'section_type')  === 'link',
                'url'           => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($section, 'link')),
                'target'        => Journal2Utils::getProperty($section, 'new_window') ? 'target="_blank"' : ''
            );

            if ($section_categories && Journal2Utils::getProperty($section, 'default_section')) {
                $this->data['default_section'] = $section_index;
            }

            $section_index++;
        }
    }

    private function generateManufacturerSections($sections_data) {
        $section_index = 0;

        foreach ($sections_data as $section) {
            if (!$section['status']) continue;

            $manufacturers = array();
            $section_manufacturers = array();
            $section_name = Journal2Utils::getProperty($section, 'section_title.value.' . $this->config->get('config_language_id'), 'Not Translated');

            switch (Journal2Utils::getProperty($section, 'section_type')) {
                case 'all':
                    $manufacturers = $this->model_catalog_manufacturer->getManufacturers(array(
                        'start' => 0,
                        'limit'	=> Journal2Utils::getProperty($section, 'items_limit', 5)
                    ));
                    break;
                case 'custom':
                    foreach (Journal2Utils::getProperty($section, 'manufacturers', array()) as $manufacturer) {
                        $manufacturers[] = array(
                            'manufacturer_id' => Journal2Utils::getProperty($manufacturer, 'data.id', -1)
                        );
                    }
            }
            foreach ($manufacturers as $manufacturer) {
                $this->has_items = true;
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer['manufacturer_id']);
                if (!$manufacturer_info) continue;

                $manufacturer_sections = isset($this->data['items'][$manufacturer_info['manufacturer_id']]) ? $this->data['items'][$manufacturer_info['manufacturer_id']]['section_class'] : array();
                $manufacturer_sections[$section_index] = 'section-' . $section_index;

                $manufacturer_data = array(
                    'section_class' => $manufacturer_sections,
                    'name'          => $manufacturer_info['name'],
                    'href'          => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']),
                    'thumb'         => Journal2Utils::resizeImage($this->model_tool_image, $manufacturer_info['image'] ? $manufacturer_info['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type'])
                );

                $this->data['items'][$manufacturer_info['manufacturer_id']] = $manufacturer_data;
                $section_manufacturers[] = $manufacturer_data;
            }

            $this->data['sections'][] = array(
                'section_class' => $section_index,
                'section_name'  => $section_name,
                'items'         => $section_manufacturers,
                'is_link'       => Journal2Utils::getProperty($section, 'section_type')  === 'link',
                'url'           => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($section, 'link')),
                'target'        => Journal2Utils::getProperty($section, 'new_window') ? 'target="_blank"' : ''
            );

            if ($section_manufacturers && Journal2Utils::getProperty($section, 'default_section')) {
                $this->data['default_section'] = $section_index;
            }

            $section_index++;
        }
    }

}
