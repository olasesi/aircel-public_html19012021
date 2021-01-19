<?php
class ControllerModuleJournal2SideProducts extends Controller {

    private static $CACHEABLE = null;
    private $has_random_products = false;

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

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.side_products_cache');
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

        $cache_property = "module_journal_side_products_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}" . $this->journal2->cache->getRouteCacheKey();

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $products = array();
            $this->data['module_id'] = $setting['module_id'];

            $limit = Journal2Utils::getProperty($module_data, 'items_limit', 5);

            $this->data['image_width'] = $this->journal2->settings->get('side_product_image_width', 50);
            $this->data['image_height'] = $this->journal2->settings->get('side_product_image_height', 50);
            $this->data['image_resize_type'] = $this->journal2->settings->get('side_product_image_type', 'crop');
            $this->data['text_tax'] = $this->language->get('text_tax');
            $this->data['button_cart'] = $this->language->get('button_cart');
            $this->data['button_wishlist'] = $this->language->get('button_wishlist');
            $this->data['button_compare'] = $this->language->get('button_compare');

            $product_id = Journal2Utils::getProperty($this->request->get, 'product_id');
			$post_id 	= Journal2Utils::getProperty($this->request->get, 'journal_blog_post_id');

            switch (Journal2Utils::getProperty($module_data, 'section_type')) {
                case 'module':
                    switch (Journal2Utils::getProperty($module_data, 'module_type')) {
                        case 'featured':
                            $products = $this->model_journal2_product->getFeatured($limit, Journal2Utils::getProperty($module_data, 'featured_module_id'), Journal2Utils::getProperty($module_data, 'filter_category', 0) !== null);
                            break;
                        case 'bestsellers':
                            $products = $this->model_journal2_product->getBestsellers($limit, Journal2Utils::getProperty($module_data, 'filter_category', 0) !== null);
                            break;
                        case 'specials':
                            $products = $this->model_journal2_product->getSpecials($limit, Journal2Utils::getProperty($module_data, 'filter_category', 0) !== null);
                            break;
                        case 'latest':
                            $products = $this->model_journal2_product->getLatest($limit, Journal2Utils::getProperty($module_data, 'filter_category', 0) !== null);
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
                            $products = $this->model_journal2_product->getPeopleAlsoBought($product_id);
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
                    $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($module_data, 'category.data.id'));
                    if (!$category_info) continue;
                    $products = $this->model_journal2_product->getProductsByCategory($category_info['category_id'], $limit);
                    break;
                case 'manufacturer':
                    $manufacturer = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($module_data, 'manufacturer.data.id'));
                    if (!$manufacturer) continue;
                    $products = $this->model_journal2_product->getProductsByManufacturer($manufacturer['manufacturer_id'], $limit);
                    break;
                case 'custom':
                    foreach (Journal2Utils::sortArray(Journal2Utils::getProperty($module_data, 'products', array())) as $product) {
                        $result = $this->model_catalog_product->getProduct((int)Journal2Utils::getProperty($product, 'data.id'));
                        if (!$result) continue;
                        $products[] = $result;
                    }
                    break;
                case 'random':
                    $this->has_random_products = true;
                    $random_type = Journal2Utils::getProperty($module_data, 'random_from', 'all');
                    $category_id = $random_type === 'category' ? Journal2Utils::getProperty($module_data, 'random_from_category.id', -1) : -1;
                    $random_products = $this->model_journal2_product->getRandomProducts($limit, $category_id);
                    foreach ($random_products as $product) {
                        $result = $this->model_catalog_product->getProduct($product['product_id']);
                        if (!$result) continue;
                        $products[] = $result;
                    }
                    break;
            }

            if (!count($products)) {
                return;
            }

            $products_data = array();

            foreach ($products as $product) {
                $image = Journal2Utils::resizeImage($this->model_tool_image, $product['image'] ? $product['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']);

                if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                    if (version_compare(VERSION, '2.2', '>=')) {
                        $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                    }
                } else {
                    $price = false;
                }

                if ((float)$product['special']) {
                    if (version_compare(VERSION, '2.2', '>=')) {
                        $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
                    }
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    if (version_compare(VERSION, '2.2', '>=')) {
                        $tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
                    } else {
                        $tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price']);
                    }
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product['rating'];
                } else {
                    $rating = false;
                }

                $product_sections = isset($this->data['items'][$product['product_id']]) ? $this->data['items'][$product['product_id']]['section_class'] : array();

                $results = $this->model_catalog_product->getProductImages($product['product_id']);

                $products_data[] = array(
                    'product_id'    => $product['product_id'],
                    'section_class' => $product_sections,
                    'thumb'         => $image,
                    'name'          => $product['name'],
                    'price'         => $price,
                    'special'       => $special,
                    'rating'        => $rating,
                    'description'   => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
                    'tax'           => $tax,
                    'reviews'       => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                    'href'          => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                    'labels'        => $this->model_journal2_product->getLabels($product['product_id']),
                    'from_abroad'   => $product['from_abroad']
                );
            }

            $this->data['heading_title'] = Journal2Utils::getProperty($module_data, 'section_title.value.' . $this->config->get('config_language_id'));
            $this->data['products'] = $products_data;

            $this->template = "journal2/module/side_products.tpl";

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

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

}
