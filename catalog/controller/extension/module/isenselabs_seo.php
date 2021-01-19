<?php
class ControllerExtensionModuleiSenseLabsSeo extends Controller {
    
    private $modulePath;
    private $callModel;
    private $storeUrl;
    private $storeId;
    private $languageId;
    private $storeLogo;
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        $this->config->load('isenselabs/isenselabs_seo');
        $this->modulePath = $this->config->get('isenselabs_seo_path');
        $this->callModel  = $this->config->get('isenselabs_seo_model');
        $this->load->model($this->modulePath);
        
        if ($this->request->server['HTTPS']) {
            $this->storeUrl = $this->config->get('config_ssl');
        } else {
            $this->storeUrl = $this->config->get('config_url');
        }
        
        $this->languageId = $this->config->get('config_language_id');
        $this->storeId = $this->config->get('config_store_id');
        
        $this->registry = $registry;
        
        if (is_file(DIR_IMAGE . $this->config->get('config_image')) && !empty($this->storeUrl)) {
            $this->storeLogo = $this->storeUrl . 'image/' . $this->config->get('config_image');
        } else {
            $this->storeLogo = '';
        }
    }

    /**
     * Sitemap for search engine
     * 
     * URL: index.php?route=extension/module/isenselabs_seo/sitemap_feed
     */
    public function sitemap_feed() {
        if ($this->config->get('module_isenselabs_seo_status')) {
            $output  = '<?xml version="1.0" encoding="UTF-8"?>';
            $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

            // Products
            $this->load->model('tool/image');

            $products = $this->{$this->callModel}->getProducts();

            foreach ($products as $product) {
                if ($product['image'] && file_exists(DIR_IMAGE . $product['image'])) {
                    $product_name = $this->escape($product['name']);
                    $date = (strtotime($product['date_modified']) == -62169984000) || (strtotime($product['date_modified']) == -62169962400) ? date('Y-m-d\TH:i:sP', strtotime(0)) : date('Y-m-d\TH:i:sP', strtotime($product['date_modified']));
                    
                    $output .= '<url>';
                    $output .= '  <loc>' . $this->escape($this->url->link('product/product', 'product_id=' . $product['product_id'])) . '</loc>';
                    $output .= '  <changefreq>weekly</changefreq>';
                    $output .= '  <lastmod>' . $date . '</lastmod>';
                    $output .= '  <priority>1.0</priority>';
                    $output .= '  <image:image>';
                    $output .= '  <image:loc>' . $this->model_tool_image->resize($product['image'], 300, 200) . '</image:loc>';
                    $output .= '  <image:caption>' . $product_name . '</image:caption>';
                    $output .= '  <image:title>' . $product_name . '</image:title>';
                    $output .= '  </image:image>';
                    $output .= '</url>';
                }
            }

            // Categories
            $this->load->model('catalog/category');

            $output .= $this->getCategories(0);

            // Manufacturer
            $this->load->model('catalog/manufacturer');

            $manufacturers = $this->model_catalog_manufacturer->getManufacturers();

            foreach ($manufacturers as $manufacturer) {
                $output .= '<url>';
                $output .= '  <loc>' . $this->escape($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'])) . '</loc>';
                $output .= '  <changefreq>weekly</changefreq>';
                $output .= '  <priority>0.7</priority>';
                $output .= '</url>';

                if ($this->{$this->callModel}->getSetting('feed_manufacturer_product')) {
                    $products = $this->{$this->callModel}->getProducts(array('filter_manufacturer_id' => $manufacturer['manufacturer_id']));

                    foreach ($products as $product) {
                        $output .= '<url>';
                        $output .= '  <loc>' . $this->escape($this->url->link('product/product', 'manufacturer_id=' . $manufacturer['manufacturer_id'] . '&product_id=' . $product['product_id'])) . '</loc>';
                        $output .= '  <changefreq>weekly</changefreq>';
                        $output .= '  <priority>1.0</priority>';
                        $output .= '</url>';
                    }
                }
            }

            // Information
            $this->load->model('catalog/information');

            $informations = $this->model_catalog_information->getInformations();

            foreach ($informations as $information) {
                $output .= '<url>';
                $output .= '  <loc>' . $this->escape($this->url->link('information/information', 'information_id=' . $information['information_id'])) . '</loc>';
                $output .= '  <changefreq>weekly</changefreq>';
                $output .= '  <priority>0.5</priority>';
                $output .= '</url>';
            }

            // SEO Backpack - custom URL
            $urls = $this->{$this->callModel}->getCustomUrls();

            foreach ($urls as $url) {
                $output .= '<url>';
                $output .= '  <loc>' . $this->escape($this->url->link($url)) . '</loc>';
                $output .= '  <changefreq>weekly</changefreq>';
                $output .= '  <priority>0.5</priority>';
                $output .= '</url>';
            }

            // ===

            $output .= '</urlset>';

            $this->response->addHeader('Content-Type: application/xml');
            $this->response->setOutput($output);
        }
    }

    /**
     * Part of sitemap_feed()
     */
    protected function getCategories($parent_id, $current_path = '') {
        $output = '';

        $results = $this->model_catalog_category->getCategories($parent_id);

        foreach ($results as $result) {
            if (!$current_path) {
                $new_path = $result['category_id'];
            } else {
                $new_path = $current_path . '_' . $result['category_id'];
            }

            $output .= '<url>';
            $output .= '  <loc>' . $this->escape($this->url->link('product/category', 'path=' . $new_path)) . '</loc>';
            $output .= '  <changefreq>weekly</changefreq>';
            $output .= '  <priority>0.7</priority>';
            $output .= '</url>';

            if ($this->{$this->callModel}->getSetting('feed_category_product')) {
                $products = $this->{$this->callModel}->getProducts(array('filter_category_id' => $result['category_id']));

                foreach ($products as $product) {
                    $output .= '<url>';
                    $output .= '  <loc>' . $this->escape($this->url->link('product/product', 'path=' . $new_path . '&product_id=' . $product['product_id'])) . '</loc>';
                    $output .= '  <changefreq>weekly</changefreq>';
                    $output .= '  <priority>1.0</priority>';
                    $output .= '</url>';
                }
            }

            $output .= $this->getCategories($result['category_id'], $new_path);
        }

        return $output;
    }

    protected function escape($string)
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /*
    * Event for catalog/view/common/header/before
    * Used for canonical URL manipulation
    */
    public function canonicalManager($eventRoute, &$data) {
        $route = !empty($this->request->get['route']) ? $this->request->get['route'] : '';
        $variable = '';
        $canonical = array(
            'key'   => '',
            'links' => array()
        );

        switch($route) {
            case 'product/product':
                $variable = 'canonical_products';
                break;
            case 'product/category':
                $variable = 'canonical_categories';

                $url_category = $this->url->link('product/category', 'path=' .  $this->request->get['path'], true);
                $canonical = array(
                    'key'   => $url_category,
                    'links' => array(
                        'href' => $url_category,
                        'rel'  => 'canonical'
                    )
                );
                break;
            case 'product/manufacturer/info':
                $variable = 'canonical_manufacturers';

                $url_manufacturer = $this->url->link('product/manufacturer/info', 'manufacturer_id=' .  $this->request->get['manufacturer_id'], true);
                $canonical = array(
                    'key'   => $url_manufacturer,
                    'links' => array(
                        'href' => $url_manufacturer,
                        'rel'  => 'canonical'
                    )
                );
                break;
            case 'information/information':
                $variable = 'canonical_information_pages';

                $url_information = $this->url->link('information/information', 'information_id=' .  $this->request->get['information_id'], true);
                $data['links'][$url_information] = [
                    'href' => $url_information,
                    'rel'  => 'canonical'
                ];
                break;
            case 'product/special':
                $variable = 'canonical_special_page';
                break;
            case 'common/home':
            default:
                $variable = 'canonical_home_page';
                
                $url_home = $this->url->link('common/home', '', true);
                $data['links'][$url_home] = [
                    'href' => $url_home,
                    'rel'  => 'canonical'
                ];
                break;
        }

        $canonical_check = $this->{$this->callModel}->getSetting($variable);

        foreach ($data['links'] as $key => $link) {
            if ($link['rel'] == 'canonical' && !$canonical_check) {
                unset($data['links'][$key]);
            }

            // Fix canonical contain: limit, page etc
            if ($link['rel'] == 'canonical' && $canonical_check && $canonical['key']) {
                unset($data['links'][$key]);
                $data['links'][$canonical['key']] = $canonical['links'];
            }
        }
    }
    
    /*
    * Event for catalog/controller/common/header/after
    * Used for gathering data from search engine bots
    */
    public function searchEngineAnalytics($eventRoute, $data) {
        $search_engine_analytics_enable = $this->{$this->callModel}->getSetting('search_engine_analytics_enable');
            
        if (!empty($search_engine_analytics_enable) && $search_engine_analytics_enable == '1') {
            $crawlers = array(
                'Googlebot',
                'Googlebot-Image',
                'Bingbot',
                'YandexBot',
                'YandexImages'
            );
            $crawlers_agents = implode('|', $crawlers);
            $user_agent = (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $current_protocol = 'http://';
            if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
                $current_protocol = 'https://';
            }
            $current_url = $current_protocol . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

            if (preg_match('/('.$crawlers_agents.')/i', $user_agent, $matches)) {
                $bot_name = current($matches);
                $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_analysis` SET crawler='" . $this->db->escape($bot_name) . "', url='" . $this->db->escape($current_url) . "', store_id = '" . (int)$this->storeId . "', date_added=NOW()");
            }
        }
    }
    
    
    /*
    * Event for catalog/controller/product/manufacturer/info/before
    * Used for adding meta data for the manufacturers
    */    
     public function manufacturerMetaData($eventRoute, &$data) {
        $manufacturer_id = !empty($this->request->get['manufacturer_id']) ? $this->request->get['manufacturer_id'] : '0'; 

        $manufacturer_seo_data = $this->{$this->callModel}->getManufacturerData($manufacturer_id);

         if ($manufacturer_seo_data) {
            if (!empty($manufacturer_seo_data['meta_title']))
                $this->document->setTitle($manufacturer_seo_data['meta_title']);
            if (!empty($manufacturer_seo_data['meta_description']))
                $this->document->setDescription($manufacturer_seo_data['meta_description']);
            if (!empty($manufacturer_seo_data['meta_keyword']))
                $this->document->setKeywords($manufacturer_seo_data['meta_keyword']);
        }
     }
    
    /*
    * Event for catalog/controller/error/not_found/before
    * Used for tracking 404 pages & redirecting
    */    
    public function notFoundPageHandler($eventRoute = '', $data = []) {
        $valid = true;
        $url_route = !empty($this->request->get['_route_']) ? $this->request->get['_route_'] : '';

        // Check non alias
        $url_request = ($this->request->server['HTTPS'] ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url_base    = $this->request->server['HTTPS'] ? $this->config->get('config_ssl') : $this->config->get('config_url');
        if (!$url_route) {
            $url_route   = urldecode(str_replace($url_base, '', $url_request));
        }


        // Blacklist 404 that start with this word
        $blacklist = array('admin/', 'asset/', 'assets/', 'image/', 'cache/', 'view/');
        foreach ($blacklist as $block) {
            if (strpos($url_route, $block) !== false) {
                $valid = false;
                break;
            }
        }

        if ($valid) {
            $route = $this->{$this->callModel}->MissingPageWorker($url_route, $this->storeId);

            if ($route) {
                // Info: Chrome and Firefox cache a 301 redirect with no expiry date
                $this->response->redirect($url_base . ltrim($route, '/'), 301);
            }
        }
    }
    
    /*
    * Event for catalog/view/common/header/before
    * Used for hreflang URL manipulation (1/2)
    */
    public function hreflangControllerManager($eventRoute, &$data) {
        $route = !empty($this->request->get['route']) ? $this->request->get['route'] : '';
        $variable = '';
        $type = '';
        $id = '';
       
        switch($route) {
            case 'product/product':
                $variable = 'hreflang_products';
                $type = 'product_id';
                break;
            case 'product/category':
                $variable = 'hreflang_categories';
                $type = 'category_id';
                break;
            case 'product/manufacturer/info':
                $variable = 'hreflang_manufacturers';
                $type = 'manufacturer_id';
                break;
            case 'information/information':
                $variable = 'hreflang_informations';
                $type = 'information_id';
                break;
            default:
                break;
        }

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $hreflang_check = $this->{$this->callModel}->getSetting($variable);
        $subfolder_prefix_check = $this->{$this->callModel}->getSetting('subfolder_prefixes');
        $default_lang_prefix = $this->{$this->callModel}->getSetting('default_lang_prefix');
        $id = !empty($this->request->get[$type]) ? $this->request->get[$type] : 0;

        $data['hreflangs'] = array();
        if ($hreflang_check) {
            $language_string = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'")->row['value'];

            foreach ($languages as $language) {
                $query = $this->db->query("SELECT `keyword` FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $type . "=" . (int)$id . "' AND `language_id` = '".  $language['language_id']."' AND `store_id` = '" . $this->storeId . "' LIMIT 1");

                $lang_prefix  = '';
                if ($subfolder_prefix_check) {
                    if ($language['code'] != $language_string || $default_lang_prefix) {
                        $lang_prefix =  $language['code'] . '/';

                        $subfolder_prefixes_alias = json_decode($this->{$this->callModel}->getSetting('subfolder_prefixes_alias'), true);
                        if (isset($subfolder_prefixes_alias[$language['code']])) {
                            $lang_prefix = $subfolder_prefixes_alias[$language['code']] . '/';
                        }
                    }
                }

                if ($query->num_rows) {
                    $data['hreflangs'][] = array(
                        'href' => $this->storeUrl . $lang_prefix . $query->row['keyword'],
                        'lang' => $language['code']
                    );
                }
            }
        }
        
        $data['hreflang_links'] = $this->load->view('extension/module/isenselabs_seo/hreflangs', array('data' => ($data['hreflangs'])));
    }
    
    /*
    * Event for catalog/view/common/header/after
    * Used for hreflang URL manipulation (2/2)
    */
    public function hreflangViewManager($eventRoute, &$data, &$output) {
        if (!empty($data['hreflang_links'])) {
            $output = str_replace('</head>' , $data['hreflang_links'] . PHP_EOL . '</head>', $output); 
        }
    }
    
    /*
    * Event for catalog/controller/common/language/language/before
    * Used for SEO URLs language switch
    */
    public function seoUrlLanguageSwitch($eventRoute, &$data) {
        $home_url = $this->url->link('common/home');
        if (isset($this->request->post['code'])) {
			$this->session->data['language'] = $this->request->post['code'];
		}
        
        if (isset($this->request->post['redirect'])) {
            $search_string = "";
            if (($position = strpos($this->request->post['redirect'], "_route_=")) !== FALSE) { 
                $search_string = substr($this->request->post['redirect'], $position+8); 
            } else {
                $search_string = $this->request->post['redirect']; 
            }

            if (!empty($search_string)) {
                $search_string = explode('/', urldecode($search_string));

                // remove any empty arrays from trailing
                if (utf8_strlen(end($search_string)) == 0) {
                    array_pop($search_string);
                }
                foreach ($search_string as $string) {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_custom_urls WHERE keyword = '" . $this->db->escape($string) . "' LIMIT 1");

                    if ($query->num_rows > 0) {

                        if (isset($this->request->post['code'])) {
                            $lang_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($this->request->post['code']) . "' LIMIT 1");
                            if ($lang_query->num_rows > 0) {
                                $this->config->set('config_language', $this->request->post['code']);
                                $this->config->set('config_language_id', $lang_query->row['language_id']);
                            }
                        }
                        $custom_query = $query->row['query'];
                        $custom_lang = $this->config->get('config_language_id');
                        $custom_url_query = $this->db->query("SELECT `keyword` FROM " . DB_PREFIX . "seo_custom_urls WHERE query = '" . $this->db->escape($custom_query) . "' AND language_id = '". (int)$custom_lang ."' AND store_id = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");
                        $subfolder_prefix_check = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key` = '" . $this->db->escape('subfolder_prefixes'). "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");
                        $default_lang_prefix_check = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key` = '" . $this->db->escape('default_lang_prefix'). "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");
                        $language_string = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'")->row['value'];

                        $lang_prefix  = '';
                        if ($subfolder_prefix_check->row['value']) {
                            if ($this->config->get('config_language') != $language_string || $default_lang_prefix_check->row['value']) {
                                $lang_prefix = $this->config->get('config_language') . '/';

                                $subfolder_prefixes_alias = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key` = '" . $this->db->escape('subfolder_prefixes_alias'). "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");
                                $subfolder_prefixes_alias = json_decode($subfolder_prefixes_alias->row['value'], true);
                                if (isset($subfolder_prefixes_alias[$this->session->data['language']])) {
                                    $lang_prefix = $subfolder_prefixes_alias[$this->session->data['language']] . '/';
                                }
                            }
                        }

                        $custom_url = $lang_prefix . $custom_url_query->row['keyword'];
                        if (!empty($custom_url)){
                            $this->response->redirect($custom_url);
                        }

                    } else {

                        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($string) . "' AND `store_id` = '" . $this->storeId . "' LIMIT 1");
                        $redirect_url = "";
                        $redirect_path = "";
                        $redirect_query = "";

                        if ($query->num_rows > 0) {

                            $url = explode('=', $query->row['query']);

                            if (isset($this->request->post['code'])) {
                                $lang_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($this->request->post['code']) . "' LIMIT 1");
                                if ($lang_query->num_rows > 0) {
                                    $this->config->set('config_language', $this->request->post['code']);
                                    $this->config->set('config_language_id', $lang_query->row['language_id']);
                                }
                            }

                            if ($url[0] == 'category_id') {
                                if (!isset($this->request->get['path'])) {
                                    $this->request->get['path'] = $url[1];
                                } else {
                                    $this->request->get['path'] .= '_' . $url[1];
                                }
                            }

                            if ($url[0] == 'product_id') {
                                $redirect_path = "product/product";
                                $redirect_query = "product_id=" . $url[1];
                            }
                            if ($url[0] == 'manufacturer_id') {
                                $redirect_path = "product/manufacturer/info";
                                $redirect_query = "manufacturer_id=" . $url[1];
                            }
                            if ($url[0] == 'information_id') {
                                $redirect_path = "information/information";
                                $redirect_query = "information_id=" . $url[1];
                            }
                        }

                    }
                }

                if (isset($this->request->get['path']) && !empty($redirect_query)) {
                    $redirect_query = "path=" . $this->request->get['path'] . "&" . $redirect_query;
                } else if (isset($this->request->get['path'])) {
                    $redirect_path = "product/category";
                    $redirect_query = "path=" . $this->request->get['path'];
                }

                if (!empty($redirect_query) && !empty($redirect_path)) {
                    $redirect_url = $this->url->link($redirect_path, $redirect_query, 'SSL');
                }

                if (!empty($redirect_url)) {
                    $this->request->post['redirect'] = $redirect_url;
                } elseif (strpos($this->request->post['redirect'], 'route=') === FALSE) {
                    $strip_path   = ltrim(str_replace($home_url, '', $this->request->post['redirect']), '/');
                    $redirect_url = $strip_path ? rtrim($this->url->link('common/home'), '/') . '/' . $strip_path : $this->url->link('common/home');

                    $this->request->post['redirect'] = $redirect_url;
                }
            }
        }

        if (isset($this->request->post['redirect'])) {
			return $this->response->redirect($this->request->post['redirect']);
		} else {
			return $this->response->redirect($this->url->link('common/home'));
		}
    }
    
    /*
    * Event for catalog/view/product/product/before
    * Used for auto-links, h1 and h2 tags in product pages
    */
    public function productAutoLinksH1H2Tags($eventRoute, &$data) {
        $autolinks = $this->{$this->callModel}->getAutoLinks($this->storeId);
            
        if ($autolinks) {
            $data['description'] = $this->{$this->callModel}->getDescriptionWithAutolinks($data['description'], $this->storeId);
        }

        $h1 = $this->{$this->callModel}->getProductH1Tag($data['product_id']);
        $h2 = $this->{$this->callModel}->getProductH2Tag($data['product_id']);

        $data['description'] = (!empty($h2)) ? '<h2>' . $h2 . '</h2>' . $data['description'] : $data['description'];

        $data['heading_title'] = (!empty($h1)) ? $h1 : $data['heading_title'];
    }
    
    /*
    * Event for catalog/view/product/category/before
    * Used for auto-links in category pages
    */
    public function categoryAutoLinks($eventRoute, &$data) {
        $autolinks = $this->{$this->callModel}->getAutoLinks($this->storeId);
            
        if ($autolinks) {
            $data['description'] = $this->{$this->callModel}->getDescriptionWithAutolinks($data['description'], $this->storeId);
        }   
    }
    
    /*
    * Event for catalog/asterisk/before
    * Adds this controller as a rewrite controller to url
    * Used for Custom SEO URLs - 1/3
    */

    public function customUrlFunctionalityRewrite() {
        $this->event->unregister("*/before", $this->modulePath . "/customUrlFunctionalityRewrite");

        if ($this->config->get('config_seo_url')) {
            $this->url->addRewrite($this);
        }
    }
    
    /*
    * Event for catalog/controller/error/not_found/before
    * Used for Custom SEO URLs (2/3)
    */
    
    public function customUrlFunctionality($eventRoute, &$data) {
        if (isset($this->request->get['_route_'])) {
            $parts = explode('/', $this->request->get['_route_']);

            if (utf8_strlen(end($parts)) == 0) {
                array_pop($parts);
            }

            $subfolder_prefix_check = $this->{$this->callModel}->getSetting('subfolder_prefixes');
            $default_lang_prefix_check = $this->{$this->callModel}->getSetting('default_lang_prefix');
            $language_string = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'")->row['value'];

            foreach ($parts as $part) {
                $custom_seo_urls = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `keyword` = '" . $this->db->escape($part) . "' AND `store_id` = '" . $this->storeId . "' AND `language_id` = '" . $this->languageId . "' LIMIT 1");

                if ($custom_seo_urls->num_rows > 0) {
                    $this->request->get['route'] = $custom_seo_urls->row['query'];
                    return new Action($this->request->get['route']);
                } else {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_custom_urls WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");

                    if ($query->num_rows > 0) {

                        $base = '';
                
                        $lang_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . $this->db->escape($query->row['language_id']) . "' LIMIT 1");
                        if ($lang_query->num_rows > 0) {
                            $this->session->data['language'] = $lang_query->row['code'];
                            $this->config->set('config_language', $lang_query->row['code']);
                            $this->config->set('config_language_id', $lang_query->row['language_id']);

                            if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
                                $base = HTTPS_SERVER;
                            } else {
                                $base = HTTP_SERVER;
                            } 
                        }
                        
                       
                        
                        $custom_query = $query->row['query'];
                        $custom_lang = $this->config->get('config_language_id');
                        $custom_url_query = $this->db->query("SELECT `keyword` FROM " . DB_PREFIX . "seo_custom_urls WHERE query = '" . $this->db->escape($custom_query) . "' AND language_id = '". (int)$custom_lang ."' AND store_id = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");
                        
                        $lang_prefix  = '';
                        if ($subfolder_prefix_check) {
                            if ($this->config->get('config_language') != $language_string || $default_lang_prefix_check) {
                                $lang_prefix = $this->config->get('config_language') . '/';

                                $subfolder_prefixes_alias = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key` = '" . $this->db->escape('subfolder_prefixes_alias'). "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");
                                $subfolder_prefixes_alias = json_decode($subfolder_prefixes_alias->row['value'], true);
                                if (isset($subfolder_prefixes_alias[$language['code']])) {
                                    $lang_prefix = $subfolder_prefixes_alias[$language['code']] . '/';
                                }
                            }
                        }
                        
                        $custom_url = $base . $lang_prefix . $custom_url_query->row['keyword'];
                        if (!empty($custom_url)){
                            $this->response->redirect($custom_url);
                        }

                    }
                }

            }
        }
    }
    
    /*
    * Event for catalog/asterisk/before
    * Used for Custom SEO URLs (3/3)
    */
    public function rewrite($link) {
        $default_lang_prefix = $this->{$this->callModel}->getSetting('default_lang_prefix');
        $subfolder_check = $this->{$this->callModel}->getSetting('subfolder_prefixes');

        if ($subfolder_check) {
            $active_lang_code = isset($this->session->data['language']) ? $this->session->data['language'] : $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'")->row['value'];
            $subfolder_prefixes_alias = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key` = '" . $this->db->escape('subfolder_prefixes_alias'). "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");
            $subfolder_prefixes_alias = json_decode($subfolder_prefixes_alias->row['value'], true);
        }

        $url_info = parse_url(str_replace('&amp;', '&', $link));

        $url = '';

        $data = array();

        if (!empty($url_info['query'])) {
            parse_str($url_info['query'], $data);

            foreach ($data as $key => $value) {
                if (isset($data['route'])) {
                    $custom_seo_urls = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `query` = '" . $this->db->escape($data['route']) . "' AND `store_id` = '" . $this->storeId . "' AND `language_id` = '" . $this->languageId . "' LIMIT 1");

                    if ($custom_seo_urls->num_rows > 0) {
                        $url .= '/' . $custom_seo_urls->row['keyword'];    
                        unset($data['key']);
                    }
                }
            }

            if (($url) && ($url <> '/'.$this->session->data['language'])) {
                unset($data['route']);

                $query = '';

                if ($data) {
                    foreach ($data as $key => $value) {
                        $query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
                    }

                    if ($query) {
                        $query = '?' . str_replace('&', '&amp;', trim($query, '&'));
                    }
                }

                if ($subfolder_check) {
                    $language_string = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'")->row['value'];

                    if (isset($this->session->data['language'])) {
                        if($this->session->data['language'] <> $language_string || $default_lang_prefix){
                            $url_prefix = '/' . $this->session->data['language'];

                            if (!empty($subfolder_prefixes_alias) && isset($subfolder_prefixes_alias[$active_lang_code])) {
                                $url_prefix = '/' . $subfolder_prefixes_alias[$active_lang_code];
                            }
                        } else {
                            $url_prefix = '';
                        }
                    } else {
                        $url_prefix = '';
                    }

                    return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url_prefix . $url . $query;
                } else {
                    return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
               }
            } else {
                return $link;
            }
        } else {
            return $link;
        }
    }
    
    /*
    * Event for catalog/view/common/header/before
    * Used for Structured Data (1/2)
    */
    public function structuredData($eventRoute, &$data) {
        $route = !empty($this->request->get['route']) ? $this->request->get['route'] : '';
        
        // Company info
        $seo_data = array();
        $seo_data['richsnippets_company_info']        = $this->{$this->callModel}->getSetting('richsnippets_company_info');

        if ($seo_data['richsnippets_company_info']) {
            $seo_data['richsnippet_phone'] = $this->config->get('config_telephone');

            $seo_data['seo_url_site'] = $this->storeUrl;
            $seo_data['seo_logo'] = $this->storeLogo;
            
            $seo_data['seo_search'] = html_entity_decode($this->url->link('product/search', 'search={search_term_string}', 'SSL'), ENT_QUOTES, 'UTF-8');
            
            $data['company_info'] = $this->load->view('extension/module/isenselabs_seo/sd_company_info', $seo_data);
        }
        
        // Category Breadcrumbs
        $seo_data = array();
        if ($route == 'product/category') {
            $category_path = !empty($this->request->get['path']) ? $this->request->get['path'] : 0;
            $category_id = 0;
            
            if ($category_path) {
               $parts = explode('_', (string)$this->request->get['path']);
               $category_id = end($parts); 
            }
            $richsnippets_category_breadcrumbs = $this->{$this->callModel}->getSetting('richsnippets_category_breadcrumbs');
            
            if ($category_path && $category_id && $richsnippets_category_breadcrumbs) {
                $seo_data['category_breadcrumbs'] = true;

                if ($richsnippets_category_breadcrumbs) {                  
                    $seo_categories = $this->{$this->callModel}->getCategoryPathByCategoryId($category_id);

                    if (!empty($seo_categories)) {
                        $seo_data['breadcrumbs'] = array();
                        
                        $seo_data['breadcrumbs'][] = array(
                            'name' => $this->config->get('config_name'),
                            'href' => $this->url->link('common/home', '', true)
                        );

                        $seo_categories_parts = explode('_', (string) $seo_categories);
                        foreach ($seo_categories_parts as $seo_cat) {
                            $cat_info = $this->model_catalog_category->getCategory($seo_cat);
                            if ($cat_info) {
                                $cat_info['href'] = html_entity_decode($this->url->link('product/category', 'path='.$cat_info['category_id'], true), ENT_QUOTES, 'UTF-8');
                                
                                $seo_data['breadcrumbs'][] = $cat_info;
                            }
                        }
                    }
                    
                }

                $data['category_data_breadcrumbs'] = $this->load->view('extension/module/isenselabs_seo/sd_category_data_breadcrumbs', $seo_data);
            }
        }
        
        // Product Data & Breadcrumbs
        $seo_data = array();
        if ($route == 'product/product') {
            $product_id = !empty($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;
            
            $product_info = array();
            if ($product_id) {
                $this->load->model('tool/image');
                $this->load->model('catalog/product');
                $product_info = $this->model_catalog_product->getProduct($product_id);
                
                if ($product_info) {
                    if ((float)$product_info['special']) {
                        $product_price = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
                        $price_valid_until_query = $this->db->query("SELECT date_end FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = '" . (int)$product_id . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1");
                        if ($price_valid_until_query->num_rows > 0){
                            $price_valid_until = $price_valid_until_query->row['date_end'];
                        }
                    } else {
                       // LoginToSeePrice Compatibility Fix
                       if (!empty($product_info['actual_special'])) {
                           $product_price = $this->tax->calculate($product_info['actual_special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
                       } else if (!empty($product_info['actual_price'])) {
                           $product_price = $this->tax->calculate($product_info['actual_price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
                       } else {                   
                           $product_price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
                       }
                    }

					$product_price = number_format($product_price, 2, '.', '');
                }
            }
            
            $richsnippets_product_data = $this->{$this->callModel}->getSetting('richsnippets_product_data');
            $richsnippets_product_breadcrumbs = $this->{$this->callModel}->getSetting('richsnippets_product_breadcrumbs');
            
            if ($product_info && ($richsnippets_product_data || $richsnippets_product_breadcrumbs)) {
                
                if ($richsnippets_product_data) {
                    $seo_data['product_data']      = true;   
                    $seo_data['name']              = $product_info['name']; 
                    $seo_data['quantity']          = $product_info['quantity'];
                    $seo_data['sku']               = $product_info['sku'];
                    $seo_data['ean']               = $product_info['ean'];
                    $seo_data['mpn']               = $product_info['mpn'];
                    $seo_data['currency_code']     = $this->session->data['currency'];
                    $seo_data['manufacturer']      = $product_info['manufacturer'];
                    $seo_data['review_count']      = $product_info['reviews'];
                    $seo_data['rating']            = (int)$product_info['rating'];
                    $seo_data['model']             = $product_info['model'];
                    $seo_data['url']               = $this->url->link('product/product', 'product_id=' . $product_info['product_id'], 'SSL');
                    $seo_data['description']       = substr(trim(strip_tags(str_replace('"', '',html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')))), 0, 150);

                    if ($product_info['image']) {
                        $seo_data['image']         = $this->model_tool_image->resize($product_info['image'], 500, 500);
                    } else {
                        $seo_data['image']         = '';
                    }
                    
                    $seo_data['price'] = $product_price;
                    $seo_data['price_valid_until'] = isset($price_valid_until) && $price_valid_until != "0000-00-00" ? $price_valid_until : '';
                    
                    if ($product_info['reviews'] > 0) {
                       $review_data = $this->db->query("SELECT * FROM " . DB_PREFIX . "review r WHERE r.product_id = '" . (int)$product_id . "' LIMIT 1")->row;

                       if ($review_data){
                            $seo_data['review_rating'] = $review_data['rating'];
                            $seo_data['review_author'] = $review_data['author'];    
                       }
                    }   
                }
                
                if ($richsnippets_product_breadcrumbs) {  
                    $seo_data['product_breadcrumbs'] = true;
                    
                    $categories         = $this->model_catalog_product->getCategories($product_info['product_id']);
                    $first_category     = end($categories);

                    $seo_categories = $this->{$this->callModel}->getCategoryPathByCategoryId($first_category['category_id']);

                    if (!empty($seo_categories)) {
                        $seo_data['breadcrumbs'] = array();
                        
                        $seo_data['breadcrumbs'][] = array(
                            'name' => $this->config->get('config_name'),
                            'href' => $this->url->link('common/home', '', 'SSL')
                        );

                        $seo_categories_parts = explode('_', (string) $seo_categories);
                        foreach ($seo_categories_parts as $seo_cat) {
                            $cat_info = $this->model_catalog_category->getCategory($seo_cat);
                            if ($cat_info) {
                                $cat_info['href'] = html_entity_decode($this->url->link('product/category', 'path='.$cat_info['category_id'], 'SSL'), ENT_QUOTES, 'UTF-8');
                                
                                $seo_data['breadcrumbs'][] = $cat_info;
                            }
                        }
                    }
                    
                }
                
                $data['product_data_breadcrumbs'] = $this->load->view('extension/module/isenselabs_seo/sd_product_data_breadcrumbs', $seo_data);
            }
            
            // Social SEO
            $seo_data = array();
            $twitter_card = $this->{$this->callModel}->getSetting('twitter_card');
            $twitter_card_username = $this->{$this->callModel}->getSetting('twitter_card_username');
            $twitter_card_product_data = $this->{$this->callModel}->getSetting('twitter_card_product_data');
            $facebook_open_graph = $this->{$this->callModel}->getSetting('facebook_open_graph');
            $facebook_open_graph_product_data = $this->{$this->callModel}->getSetting('facebook_open_graph_product_data');
            
            if ($product_info && $twitter_card && $twitter_card_product_data) {
                $seo_data[] = array('product', 'twitter:card', 'name');
                $seo_data[] = array($twitter_card_username, 'twitter:creator', 'name');
                $seo_data[] = array($twitter_card_username, 'twitter:site', 'name');

                if (!empty($product_info['meta_description'])) {
                    $seo_data[] = array($product_info['meta_description'].'...', 'twitter:description', 'name');
                }

                $seo_data[] = array($product_info['name'], 'twitter:title', 'name');
                $seo_data[] = array($this->url->link('product/product', 'product_id=' . $product_info['product_id'], 'SSL'), 'twitter:domain', 'name');
                
                if ($product_info['image']) {
                    $seo_data[] = array($this->model_tool_image->resize($product_info['image'], 500, 500), 'twitter:image', 'name');
                }
            }

            if ($product_info && $facebook_open_graph && $facebook_open_graph_product_data) {
                $seo_data[] = array('product', 'og:type', 'property');
                $seo_data[] = array($product_info['name'], 'og:title', 'property');

                if (!empty($product_info['meta_description'])) {
                    $seo_data[] = array($product_info['meta_description'].'...', 'og:description', 'property');
                }

                $seo_data[] = array($this->url->link('product/product', 'product_id=' . $product_info['product_id'], 'SSL'), 'og:url', 'property');

                if (!empty($product_info['image'])) {
                    $seo_data[] = array($this->model_tool_image->resize($product_info['image'], 500, 500), 'og:image', 'property');
                }

                $seo_data[] = array($product_price, 'product:price:amount', 'property');
                $seo_data[] = array($this->session->data['currency'], 'product:price:currency', 'property');
            
                $fb_app_id  = $this->{$this->callModel}->getSetting('facebook_open_graph_app_id');
                if ($fb_app_id) {
                    $seo_data[] = array($fb_app_id, 'fb:app_id', 'property');
                }
                
            }
            
            $data['product_data_social_seo'] = $this->load->view('extension/module/isenselabs_seo/social_seo', array('links' => $seo_data));
        }
        
        // Home Page Tags
        if ($route == 'common/home' || $route === '') {
            $seo_data = array();
            
            $twitter_card = $this->{$this->callModel}->getSetting('twitter_card');
            $twitter_card_username = $this->{$this->callModel}->getSetting('twitter_card_username');
            $facebook_open_graph = $this->{$this->callModel}->getSetting('facebook_open_graph');
            
            $meta_title = $this->config->get('config_meta_title');
            $meta_description = $this->config->get('config_meta_description');

            $meta_title = is_array($meta_title) && isset($meta_title[$this->languageId]) ? $meta_title[$this->languageId] : $meta_title;
            $meta_description = is_array($meta_description) && isset($meta_description[$this->languageId]) ? $meta_description[$this->languageId] : $meta_description;
            
            if ($twitter_card) {
                $seo_data[] = array('summary', 'twitter:card', 'name');
                if ($twitter_card_username) {
                    $seo_data[] = array($twitter_card_username, 'twitter:creator', 'name');
                    $seo_data[] = array($twitter_card_username, 'twitter:site', 'name');
                }

                if (!empty($meta_description)) {
                    $seo_data[] = array($meta_description, 'twitter:description', 'name');
                }

                if (!empty($meta_title)) {
                    $seo_data[] = array($meta_title, 'twitter:title', 'name');
                }  

                if (!empty($this->storeUrl)) {
                    $seo_data[] = array($this->storeUrl, 'twitter:domain', 'name');
                }  

                if (!empty($this->storeLogo)) {
                    $seo_data[] = array($this->storeLogo, 'twitter:image', 'name');
                }                
            }  

            if ($facebook_open_graph) {
                $seo_data[] = array('website', 'og:type', 'property');
                if (!empty($meta_title)) {
                    $seo_data[] = array($meta_title, 'og:site_name', 'property');
                    $seo_data[] = array($meta_title, 'og:title', 'property');
                }

                if (!empty($meta_description)) {
                    $seo_data[] = array($meta_description, 'og:description', 'property');
                }

                if (!empty($this->storeUrl)) {
                    $seo_data[] = array($this->storeUrl, 'og:url', 'property');
                }

                if (!empty($this->storeLogo)) {
                    $seo_data[] = array($this->storeLogo, 'og:image', 'property');
                }
            }
            
            $fb_app_id  = $this->{$this->callModel}->getSetting('facebook_open_graph_app_id');
            if ($fb_app_id) {
                $seo_data[] = array($fb_app_id, 'fb:app_id', 'property');
            }
            
            $seo_google_publisher = $this->{$this->callModel}->getSetting('google_publisher');
            $seo_google_publisher_id = $this->{$this->callModel}->getSetting('google_publisher_id');

            if ($seo_google_publisher && !empty($seo_google_publisher_id)) {
                $data['google_publisher_link'] = '<link href="https://plus.google.com/' . trim($seo_google_publisher_id) . '" rel="publisher" />';
            } 

            $data['home_page_social_seo'] = $this->load->view('extension/module/isenselabs_seo/social_seo', array('links' => $seo_data));
        }

        // Meta robots noindex
        $data['meta_robots'] = '';
        $pages = array(
            'product/category',
            'product/manufacturer',
            'product/special',
            'product/product'
        );
        if (in_array($route, $pages)) {
            if (isset($this->request->get['page']) || isset($this->request->get['limit']) || isset($this->request->get['order']) || isset($this->request->get['sort']) || isset($this->request->get['filter_name']) || isset($this->request->get['filter_description'])) {
                $data['meta_robots'] = 'noindex,follow';
            }
        }

        $pages = array(
            'product/compare',
            'product/search',
            'account/forgotten',
            'account/login',
            'account/register',
            'checkout/cart',
            'checkout/checkout',
        );
        if (in_array($route, $pages)) {
            $data['meta_robots'] = 'noindex,follow';
        }
    }
    
    /*
    * Event for catalog/view/common/header/after
    * Used for Structured Data (2/2)
    */
    public function structuredDataView($eventRoute, &$data, &$output) {
        
        if (!empty($data['company_info'])) {
            $output = str_replace('</head>' , $data['company_info'] . PHP_EOL . '</head>', $output); 
        }
        
        if (!empty($data['product_data_breadcrumbs'])) {
            $output = str_replace('</head>' , $data['product_data_breadcrumbs'] . PHP_EOL . '</head>', $output); 
        }
        
        if (!empty($data['category_data_breadcrumbs'])) {
            $output = str_replace('</head>' , $data['category_data_breadcrumbs'] . PHP_EOL . '</head>', $output); 
        }
        
        if (!empty($data['product_data_social_seo'])) {
            $output = str_replace('</head>' , $data['product_data_social_seo'] . PHP_EOL . '</head>', $output); 
        }
        
        if (!empty($data['home_page_social_seo'])) {
            $output = str_replace('</head>' , $data['home_page_social_seo'] . PHP_EOL . '</head>', $output); 
        }
        
        if (!empty($data['google_publisher_link'])) {
            $output = str_replace('</head>' , $data['google_publisher_link'] . PHP_EOL . '</head>', $output); 
        }

        if (!empty($data['meta_robots'])) {
            $output = str_replace('</head>' , '<meta name="robots" content="' . $data['meta_robots'] . '" />' . PHP_EOL . '</head>', $output); 
        }
    }
}
