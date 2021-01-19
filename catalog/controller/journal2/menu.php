<?php
class ControllerJournal2Menu extends Controller {

    private static $CACHEABLE = null;
    private $mega_has_random_products = false;

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
            self::$CACHEABLE = !((bool)$this->journal2->settings->get('config_system_settings.developer_mode'));
        }
    }

    public function header($menu) {
        $cache_property = 'config_' . $menu;
        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $items = $this->journal2->settings->get('config_' . $menu . '.items', array());
            $this->data['items'] = $this->generateMenu($items);
            $this->template = 'journal2/menu/header.tpl';

            $cache = $this->render();
            if (self::$CACHEABLE === true) {
                $this->journal2->cache->set($cache_property, $cache);
            }
        }

        $cache = $this->model_journal2_menu->replaceCacheVars($cache);
        $this->journal2->settings->set('config_' . $menu, $cache);
        if ($menu === 'secondary_menu' && !trim($cache)) {
            $this->journal2->html_classes->addClass('no-secondary');
        }
    }

    public function footer($menu) {
        $cache_property = 'config_' . $menu;
        $cache = $this->journal2->cache->get($cache_property);
        $has_random_products = false;

        if ($cache === null || self::$CACHEABLE !== true) {
            $rows = $this->journal2->settings->get('config_' . $menu . '.rows', array());
            $rows = Journal2Utils::sortArray($rows);

            $this->data['rows'] = array();

            foreach ($rows as $row) {
                if (isset($row['status']) && !$row['status']) continue;
                /* device detection */
                $disable_on_classes = array();

                if ($this->journal2->settings->get('responsive_design')) {
                    $device = Journal2Utils::getDevice();

                    if (Journal2Utils::getProperty($row, 'enable_on_phone', '1') == '0') {
                        if ($device === 'phone') {
                            continue;
                        } else {
                            $disable_on_classes[] = 'hide-on-phone';
                        }
                    }

                    if (Journal2Utils::getProperty($row, 'enable_on_tablet', '1') == '0') {
                        if ($device === 'tablet') {
                            continue;
                        } else {
                            $disable_on_classes[] = 'hide-on-tablet';
                        }
                    }

                    if (Journal2Utils::getProperty($row, 'enable_on_desktop', '1') == '0') {
                        if ($device === 'desktop') {
                            continue;
                        } else {
                            $disable_on_classes[] = 'hide-on-desktop';
                        }
                    }
                }
                $row_css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($row, 'background'));
                if (Journal2Utils::getProperty($row, 'bottom_spacing') !== null) {
                    $row_css[] = 'margin-bottom: ' . Journal2Utils::getProperty($row, 'bottom_spacing') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_top') !== null) {
                    $row_css[] = 'padding-top: ' . Journal2Utils::getProperty($row, 'padding_top') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_right') !== null) {
                    $row_css[] = 'padding-right: ' . Journal2Utils::getProperty($row, 'padding_right') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_bottom') !== null) {
                    $row_css[] = 'padding-bottom: ' . Journal2Utils::getProperty($row, 'padding_bottom') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_left') !== null) {
                    $row_css[] = 'padding-left: ' . Journal2Utils::getProperty($row, 'padding_left') . 'px';
                }
                if ($color = Journal2Utils::getProperty($row, 'color.value.color')) {
                    $row_css[] = 'color: ' . Journal2Utils::getColor($color);
                }
                $temp = array(
                    'type' => '',
                    'css' => implode('; ', $row_css),
                    'class' => implode(' ', $disable_on_classes),
                    'columns' => array(),
                    'contacts' => array(
                        'left' => array(),
                        'right' => array()
                    )
                );
                switch (Journal2Utils::getProperty($row, 'type')) {
                    case 'columns':
                        $temp['type'] = 'columns';
                        $columns = Journal2Utils::getProperty($row, 'columns');
                        $columns = Journal2Utils::sortArray($columns);
                        $temp['classes'] = Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($row, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024), 0);
                        foreach ($columns as $column) {
                            if (isset($column['status']) && !$column['status']) continue;
                            /* device detection */
                            $disable_on_classes = array();

                            $column_style = array();
                            if (Journal2Utils::getProperty($column, 'padding_top') !== null) {
                                $column_style[] = 'padding-top: ' . Journal2Utils::getProperty($column, 'padding_top') . 'px';
                            }
                            if (Journal2Utils::getProperty($column, 'padding_right') !== null) {
                                $column_style[] = 'padding-right: ' . Journal2Utils::getProperty($column, 'padding_right') . 'px';
                            }
                            if (Journal2Utils::getProperty($column, 'padding_bottom') !== null) {
                                $column_style[] = 'padding-bottom: ' . Journal2Utils::getProperty($column, 'padding_bottom') . 'px';
                            }
                            if (Journal2Utils::getProperty($column, 'padding_left') !== null) {
                                $column_style[] = 'padding-left: ' . Journal2Utils::getProperty($column, 'padding_left') . 'px';
                            }

                            if ($this->journal2->settings->get('responsive_design')) {
                                $device = Journal2Utils::getDevice();

                                if (Journal2Utils::getProperty($column, 'enable_on_phone', '1') == '0') {
                                    if ($device === 'phone') {
                                        continue;
                                    } else {
                                        $disable_on_classes[] = 'hide-on-phone';
                                    }
                                }

                                if (Journal2Utils::getProperty($column, 'enable_on_tablet', '1') == '0') {
                                    if ($device === 'tablet') {
                                        continue;
                                    } else {
                                        $disable_on_classes[] = 'hide-on-tablet';
                                    }
                                }

                                if (Journal2Utils::getProperty($column, 'enable_on_desktop', '1') == '0') {
                                    if ($device === 'desktop') {
                                        continue;
                                    } else {
                                        $disable_on_classes[] = 'hide-on-desktop';
                                    }
                                }
                            }
                            switch (Journal2Utils::getProperty($column, 'type')) {
                                case 'text':
                                    /* icon css */
                                    $css = array();

                                    if (Journal2Utils::getColor(Journal2Utils::getProperty($column, 'icon_bg_color.value.color'))) {
                                        $css[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($column, 'icon_bg_color.value.color'));
                                    }
                                    if (Journal2Utils::getProperty($column, 'icon_width')) {
                                        $css[] = 'width: ' . Journal2Utils::getProperty($column, 'icon_width') . 'px';
                                    }
                                    if (Journal2Utils::getProperty($column, 'icon_height')) {
                                        $css[] = 'height: ' . Journal2Utils::getProperty($column, 'icon_height') . 'px';
                                        $css[] = 'line-height: ' . Journal2Utils::getProperty($column, 'icon_height') . 'px';
                                    }
                                    if (Journal2Utils::getProperty($column, 'icon_border')) {
                                        $css = array_merge($css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($column, 'icon_border')));
                                    }

                                    $temp['columns'][] = array(
                                        'class' => implode(' ', $disable_on_classes),
                                        'style' => implode('; ', $column_style),
                                        'type' => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title' => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'text' => Journal2Utils::getProperty($column, 'text.' . $this->config->get('config_language_id')),
                                        'has_icon' => Journal2Utils::getProperty($column, 'icon_status'),
                                        'icon_position' => Journal2Utils::getProperty($column, 'icon_position', 'top'),
                                        'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($column, 'icon')),
                                        'icon_css' => implode('; ', $css),
                                    );
                                    break;
                                case 'menu':
                                    $temp['columns'][] = array(
                                        'class' => implode(' ', $disable_on_classes),
                                        'style' => implode('; ', $column_style),
                                        'type' => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title' => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'items' => $this->generateMenu(Journal2Utils::getProperty($column, 'items', array()))
                                    );
                                    break;
                                case 'newsletter':
                                    $temp['columns'][] = array(
                                        'class' => implode(' ', $disable_on_classes),
                                        'style' => implode('; ', $column_style),
                                        'type'      => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title'     => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'content'   => $this->getChild('module/journal2_newsletter', array (
                                            'module_id' => Journal2Utils::getProperty($column, 'newsletter_id'),
                                            'layout_id' => -1,
                                            'position'  => 'footer'
                                        ))
                                    );
                                    break;
                                case 'products':
                                    $products = array();
                                    $limit = Journal2Utils::getProperty($column, 'items_limit', 5);

                                    $this->data['image_width'] = $this->journal2->settings->get('footer_product_image_width', 50);
                                    $this->data['image_height'] = $this->journal2->settings->get('footer_product_image_height', 50);
                                    $this->data['image_resize_type'] = $this->journal2->settings->get('footer_product_image_type', 'fit');
                                    $this->data['text_tax'] = $this->language->get('text_tax');
                                    $this->data['button_cart'] = $this->language->get('button_cart');
                                    $this->data['button_wishlist'] = $this->language->get('button_wishlist');
                                    $this->data['button_compare'] = $this->language->get('button_compare');

                                    switch (Journal2Utils::getProperty($column, 'section_type')) {
                                        case 'module':
                                            switch (Journal2Utils::getProperty($column, 'module_type')) {
                                                case 'featured':
                                                    $products = $this->model_journal2_product->getFeatured($limit, Journal2Utils::getProperty($column, 'featured_module_id'));
                                                    break;
                                                case 'bestsellers':
                                                    $products = $this->model_journal2_product->getBestsellers($limit);
                                                    break;
                                                case 'specials':
                                                    $products = $this->model_journal2_product->getSpecials($limit);
                                                    break;
                                                case 'latest':
                                                    $products = $this->model_journal2_product->getLatest($limit);
                                                    break;
                                            }
                                            break;
                                        case 'category':
                                            $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($column, 'category.data.id'));
                                            if (!$category_info) continue;
                                            $products = $this->model_journal2_product->getProductsByCategory($category_info['category_id'], $limit);
                                            break;
                                        case 'manufacturer':
                                            $manufacturer = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($column, 'manufacturer.data.id'));
                                            if (!$manufacturer) continue;
                                            $products = $this->model_journal2_product->getProductsByManufacturer($manufacturer['manufacturer_id'], $limit);
                                            break;
                                        case 'custom':
                                            foreach (Journal2Utils::sortArray(Journal2Utils::getProperty($column, 'products', array())) as $product) {
                                                $result = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($product, 'data.id'));
                                                if (!$result) continue;
                                                $products[] = $result;
                                            }
                                            break;
                                        case 'random':
                                            $has_random_products = true;
                                            $random_type = Journal2Utils::getProperty($column, 'random_from', 'all');
                                            $category_id = $random_type === 'category' ? Journal2Utils::getProperty($column, 'random_from_category.id', -1) : -1;
                                            $random_products = $this->model_journal2_product->getRandomProducts($limit, $category_id);
                                            foreach ($random_products as $product) {
                                                $result = $this->model_catalog_product->getProduct($product['product_id']);
                                                if (!$result) continue;
                                                $products[] = $result;
                                            }
                                            break;
                                    }

                                    $products_data = array();

                                    foreach ($products as $product) {
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

                                        $results = $this->model_catalog_product->getProductImages($product['product_id']);

                                        $image2 = false;

                                        if (!$this->journal2->html_classes->hasClass('mobile') && count($results) > 0) {
                                            $image2 = Journal2Utils::resizeImage($this->model_tool_image, $results[0]['image'] ? $results[0]['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']);
                                        }

                                        $products_data[] = array(
                                            'product_id'    => $product['product_id'],
                                            'section_class' => $product_sections,
                                            'thumb'         => $image,
                                            'thumb2'        => $image2,
                                            'dummy'         => Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']),
                                            'name'          => $product['name'],
                                            'price'         => $price,
                                            'special'       => $special,
                                            'rating'        => $rating,
                                            'description'   => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
                                            'tax'           => $tax,
                                            'reviews'       => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                            'href'          => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                                            'labels'        => $this->model_journal2_product->getLabels($product['product_id'])
                                        );
                                    }

                                    $temp['columns'][] = array(
                                        'class' => implode(' ', $disable_on_classes),
                                        'style' => implode('; ', $column_style),
                                        'type'  => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title' => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'products' => $products_data
                                    );
                                break;

                                case 'posts':
                                    $limit = Journal2Utils::getProperty($column, 'items_limit', 5);
                                    $module_type = Journal2Utils::getProperty($column, 'posts_type', 5);

                                    switch ($module_type) {
                                        case 'newest':
                                        case 'comments':
                                        case 'views':
                                            $posts = $this->model_journal2_blog->getPosts(array(
                                                'sort'          => $module_type,
                                                'start'         => 0,
                                                'limit'         => $limit
                                            ));
                                            break;
                                        case 'related':
                                            if (isset($this->request->get['route']) && $this->request->get['route'] === 'product/product' && isset($this->request->get['product_id'])) {
                                                $posts = $this->model_journal2_blog->getRelatedPosts($this->request->get['product_id'], $limit);
                                            }
                                            break;
                                        case 'custom':
                                            $custom_posts = Journal2Utils::getProperty($column, 'posts', array());
                                            $custom_posts_ids = array();
                                            foreach ($custom_posts as $custom_post) {
                                                $post_id = (int)Journal2Utils::getProperty($custom_post, 'data.id', 0);
                                                if ($post_id) {
                                                    $custom_posts_ids[$post_id] = $post_id;
                                                }
                                            }
                                            if ($custom_posts_ids) {
                                                $posts = $this->model_journal2_blog->getPosts(array(
                                                    'post_ids' => implode(',', $custom_posts_ids)
                                                ));
                                            }
                                            break;
                                    }

                                    $this->data['image_width'] = $this->journal2->settings->get('footer_post_image_width', 35);
                                    $this->data['image_height'] = $this->journal2->settings->get('footer_post_image_height', 35);
                                    $this->data['image_resize_type'] = 'crop';

                                    $posts_data = array();
                                    foreach ($posts as $post) {
                                        $posts_data[] = array(
                                            'name'          => $post['name'],
                                            'comments'      => $post['comments'],
                                            'date'          => date($this->language->get('date_format_short'), strtotime($post['date'])),
                                            'image'         => Journal2Utils::resizeImage($this->model_tool_image, $post, $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']),
                                            'href'          => $this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $post['post_id'])
                                        );
                                    }

                                    $temp['columns'][] = array(
                                        'class' => implode(' ', $disable_on_classes),
                                        'style' => implode('; ', $column_style),
                                        'type'  => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title' => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'posts' => $posts_data
                                    );
                                    break;
                            }
                        }
                        break;
                    case 'contacts':
                        $temp['type'] = 'contacts';
                        $contacts = Journal2Utils::getProperty($row, 'contacts');
                        $contacts = Journal2Utils::sortArray($contacts);
                        foreach ($contacts as $contact) {
                            $disable_on_classes = array();

                            if ($this->journal2->settings->get('responsive_design')) {
                                $device = Journal2Utils::getDevice();

                                if (Journal2Utils::getProperty($contact, 'enable_on_phone', '1') == '0') {
                                    if ($device === 'phone') {
                                        continue;
                                    } else {
                                        $disable_on_classes[] = 'hide-on-phone';
                                    }
                                }

                                if (Journal2Utils::getProperty($contact, 'enable_on_tablet', '1') == '0') {
                                    if ($device === 'tablet') {
                                        continue;
                                    } else {
                                        $disable_on_classes[] = 'hide-on-tablet';
                                    }
                                }

                                if (Journal2Utils::getProperty($contact, 'enable_on_desktop', '1') == '0') {
                                    if ($device === 'desktop') {
                                        continue;
                                    } else {
                                        $disable_on_classes[] = 'hide-on-desktop';
                                    }
                                }
                            }

                            $position = Journal2Utils::getProperty($contact, 'position');
                            $icon = Journal2Utils::getIconOptions($contact, Journal2Utils::getProperty($contact, 'name.value.' . $this->config->get('config_language_id')));
                            if ($icon['left'] === null && $icon['right'] === null) {
                                $disable_on_classes[] = 'no-icon';
                            }
                            $name = Journal2Utils::getProperty($contact, 'name.value.' . $this->config->get('config_language_id'));
                            if (!$name) {
                                $disable_on_classes[] = 'no-name';
                            }
                            $disable_on_classes[] = 'hint--top';
                            $tooltip = Journal2Utils::getProperty($contact, 'tooltip');
                            if ($tooltip) {
                                $disable_on_classes[] = 'has-tooltip';
                            }
                            $temp['contacts'][$position][] = array(
                                'classes' => implode(' ', $disable_on_classes),
                                'link' => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($contact, 'link')),
                                'target' => Journal2Utils::getProperty($contact, 'target') ? 'target="_blank"' : '',
                                'name' => $name,
                                'tooltip' => $tooltip,
                                'icon_left' => $icon['left'],
                                'icon_right' => $icon['right']
                            );
                        }
                        break;
                }
                $this->data['rows'][] = $temp;
            }

            $this->template = 'journal2/menu/footer.tpl';

            $cache = $this->render();
            if (self::$CACHEABLE === true && !$has_random_products) {
                $this->journal2->cache->set($cache_property, $cache);
            }
        }

        $cache = $this->model_journal2_menu->replaceCacheVars($cache);
        $this->journal2->settings->set('config_' . $menu, $cache);
    }

    public function mega($menu_name) {
        $cache_property = 'config_' . $menu_name;
        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $menu_items = $this->journal2->settings->get('config_' . $menu_name . '.items', array());
            $menu_items = Journal2Utils::sortArray($menu_items);

            $display = $this->journal2->settings->get('config_' . $menu_name . '.options.display', 'table');
            $this->data['display'] = $display;
            $this->data['table_css_style'] = '';

            $this->data['menu_items'] = array(
                'table' => array(),
                'left'  => array(),
                'right' => array()
            );

            $this->data['color_styles'] = array();
            $index = 0;

            foreach ($menu_items as $key => $menu_item) {
                if (!Journal2Utils::getProperty($menu_item, 'status', 1)) continue;

                $float = Journal2Utils::getProperty($menu_item, 'float', 'left');

                /* device detection */
                $disable_on_classes = array();

                if ($this->journal2->settings->get('responsive_design')) {
                    $device = Journal2Utils::getDevice();

                    if (Journal2Utils::getProperty($menu_item, 'enable_on_phone', '1') == '0') {
                        if ($device === 'phone') {
                            unset($menu_items[$key]);
                            continue;
                        } else {
                            $disable_on_classes[] = 'hide-on-phone';
                        }
                    }

                    if (Journal2Utils::getProperty($menu_item, 'enable_on_tablet', '1') == '0') {
                        if ($device === 'tablet') {
                            unset($menu_items[$key]);
                            continue;
                        } else {
                            $disable_on_classes[] = 'hide-on-tablet';
                        }
                    }

                    if (Journal2Utils::getProperty($menu_item, 'enable_on_desktop', '1') == '0') {
                        if ($device === 'desktop') {
                            unset($menu_items[$key]);
                            continue;
                        } else {
                            $disable_on_classes[] = 'hide-on-desktop';
                        }
                    }
                }

                if ($display === 'floated') {
                    $disable_on_classes[] = 'float-' . $float;
                }

                $index++;

                if ($color = Journal2Utils::getProperty($menu_item, 'color.value.color')) {
                    $this->data['color_styles'][] = "#main-menu-item-$index a { color: $color !important; }";
                }

                if ($color = Journal2Utils::getProperty($menu_item, 'bg_color.value.color')) {
                    $this->data['color_styles'][] = "#main-menu-item-$index { background-color: $color !important; }";
                }

                if ($color = Journal2Utils::getProperty($menu_item, 'hover_color.value.color')) {
                    $this->data['color_styles'][] = "#main-menu-item-$index li:hover a { color: $color !important; }";
                }

                if ($color = Journal2Utils::getProperty($menu_item, 'bg_hover_color.value.color')) {
                    $this->data['color_styles'][] = "#main-menu-item-$index:hover { background-color: $color !important; }";
                }

                $classes = Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($menu_item, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024));
                $menu = array(
                    'name' => '',
                    'href' => '',
                    'items' => array(),
                    'mixed_columns' => array(),
                    'type' => '',
                    'class' => implode(' ', $disable_on_classes),
                    'classes' => $classes,
                    'limit' => Journal2Utils::getProperty($menu_item, 'items_limit', 0),
                    'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($menu_item, 'icon')),
                    'hide_text' => Journal2Utils::getProperty($menu_item, 'hide_text'),
                    'id' => "main-menu-item-$index",
                );
                $image_width = Journal2Utils::getProperty($menu_item, 'image_width', 250);
                $image_height = Journal2Utils::getProperty($menu_item, 'image_height', 250);
                $image_resize_type = Journal2Utils::getProperty($menu_item, 'image_type', 'fit');

                $this->generateMenuItem($menu, $menu_item, $image_width, $image_height, $image_resize_type);

                $name_overwrite = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'));
                if ($name_overwrite) {
                    $menu['name'] = $name_overwrite;
                }

                if ($menu['hide_text']) {
                    $menu['class'] .= ' icon-only';
                }

                if ($display === 'table') {
                    $this->data['menu_items']['table'][] = $menu;
                } else {
                    $this->data['menu_items'][$float][] = $menu;
                }

            }

            if ($display === 'table') {
                $this->data['menu_items'] = $this->data['menu_items']['table'];
                $this->data['table_css_style'] = $this->journal2->settings->get('config_' . $menu_name . '.options.table_layout', 'fixed');
            } else {
                if (Journal2Cache::$mobile_detect->isMobile()) {
                    $this->data['menu_items'] = array_merge($this->data['menu_items']['left'], $this->data['menu_items']['right']);
                } else {
                    $this->data['menu_items'] = array_merge($this->data['menu_items']['left'], array_reverse($this->data['menu_items']['right']));
                }
            }

            $this->data['button_cart'] = $this->language->get('button_cart');
            $this->data['button_wishlist'] = $this->language->get('button_wishlist');
            $this->data['button_compare'] = $this->language->get('button_compare');
            $this->template = 'journal2/menu/main.tpl';

            $cache = $this->render();
            if (self::$CACHEABLE === true && !$this->mega_has_random_products) {
                $this->journal2->cache->set($cache_property, $cache);
            }
        }

        $cache = $this->model_journal2_menu->replaceCacheVars($cache);
        $this->journal2->settings->set('config_' . $menu_name, $cache);
    }

    private function generateMenu($items) {
        $items = Journal2Utils::sortArray($items);
        foreach ($items as $key => &$item) {
            $icon = Journal2Utils::getIconOptions($item);
            /* menu href */
            $href = null;
            $name = null;
            $target = $item['target'] ? ' target="_blank"' : '';
            /* device detection */
            $disable_on_classes = array();

            if ($this->journal2->settings->get('responsive_design')) {
                $device = Journal2Utils::getDevice();

                if (Journal2Utils::getProperty($item, 'enable_on_phone', '1') == '0') {
                    if ($device === 'phone') {
                        unset($items[$key]);
                        continue;
                    } else {
                        $disable_on_classes[] = 'hide-on-phone';
                    }
                }

                if (Journal2Utils::getProperty($item, 'enable_on_tablet', '1') == '0') {
                    if ($device === 'tablet') {
                        unset($items[$key]);
                        continue;
                    } else {
                        $disable_on_classes[] = 'hide-on-tablet';
                    }
                }

                if (Journal2Utils::getProperty($item, 'enable_on_desktop', '1') == '0') {
                    if ($device === 'desktop') {
                        unset($items[$key]);
                        continue;
                    } else {
                        $disable_on_classes[] = 'hide-on-desktop';
                    }
                }
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
                    $name = Journal2Utils::getProperty($item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                    if ($item['menu']['menu_item']) {
                    	$href = "javascript:Journal.openPopup('{$item['menu']['menu_item']}')";
                	}
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
                            $disable_on_classes[] = 'wishlist-total';
                            break;
                        case 'product/compare':
                            $disable_on_classes[] = 'compare-total';
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
            $name_overwrite = Journal2Utils::getProperty($item, 'name_overwrite');

            if (($name_overwrite === null && $overwrite_name) || $name_overwrite == '1') {
                $name = $overwrite_name;
            }

            if (Journal2Utils::getProperty($item, 'mobile_view') === 'icon') {
                $disable_on_classes[] = 'icon-only';
            }
            if (Journal2Utils::getProperty($item, 'mobile_view') === 'text') {
                $disable_on_classes[] = 'text-only';
            }
            if (!$href) {
                $disable_on_classes[] = 'no-link';
            }

            $subitems = Journal2Utils::getProperty($item, 'items', array());

            $item = array(
                'icon_left' => $icon['left'],
                'icon_right'=> $icon['right'],
                'class'     => 'class="m-item ' . ($disable_on_classes ?  implode(' ', $disable_on_classes) : '') . (count($subitems) ? ' has-dropdown': '') . '"',
                'href'      => $href,
                'name'      => $name,
                'target'    => $target,
                'items'     => count($subitems) ? $this->generateMenu($subitems) : array()
            );
        }
        return $items;
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
                                if (version_compare(VERSION, '2.3', '<')) {
                                    $this->load->language('module/featured');
                                } else {
                                    $this->load->language('extension/module/featured');
                                }
                                break;
                            case 'special':
                                $products = $this->model_journal2_product->getSpecials($items_limit ? $items_limit : 5);
                                if (version_compare(VERSION, '2.3', '<')) {
                                    $this->load->language('module/special');
                                } else {
                                    $this->load->language('extension/module/special');
                                }
                                break;
                            case 'bestseller':
                                $products = $this->model_journal2_product->getBestsellers($items_limit ? $items_limit : 5);
                                if (version_compare(VERSION, '2.3', '<')) {
                                    $this->load->language('module/bestseller');
                                } else {
                                    $this->load->language('extension/module/bestseller');
                                }
                                break;
                            case 'latest':
                                $products = $this->model_journal2_product->getLatest($items_limit ? $items_limit : 5);
                                if (version_compare(VERSION, '2.3', '<')) {
                                    $this->load->language('module/latest');
                                } else {
                                    $this->load->language('extension/module/latest');
                                }
                                break;
                        }
                        $menu['name'] = $this->language->get('heading_title');
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
                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'custom.menu_item.name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                        $menu['href'] = Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.url');
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
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
                    /* device detection */
                    $disable_on_classes = array();

                    if ($this->journal2->settings->get('responsive_design')) {
                        $device = Journal2Utils::getDevice();

                        if (Journal2Utils::getProperty($column, 'enable_on_phone', '1') == '0') {
                            if ($device === 'phone') {
                                continue;
                            } else {
                                $disable_on_classes[] = 'hide-on-phone';
                            }
                        }

                        if (Journal2Utils::getProperty($column, 'enable_on_tablet', '1') == '0') {
                            if ($device === 'tablet') {
                                continue;
                            } else {
                                $disable_on_classes[] = 'hide-on-tablet';
                            }
                        }

                        if (Journal2Utils::getProperty($column, 'enable_on_desktop', '1') == '0') {
                            if ($device === 'desktop') {
                                continue;
                            } else {
                                $disable_on_classes[] = 'hide-on-desktop';
                            }
                        }
                    }
                    $cms_blocks = array(
                        'top'       => array(),
                        'bottom'    => array()
                    );
                    foreach (Journal2Utils::getProperty($column, 'cms_blocks', array()) as $cms_block) {
                        if (!$cms_block['status']) continue;
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
                        'class' => implode(' ', $disable_on_classes),
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
