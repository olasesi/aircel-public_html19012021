<?php
class ModelExtensionModuleiSenseLabsSeo extends Model {
    public function getSetting($key = '', $store_id = null) {
        if (is_null($store_id)) {
            $store_id = $this->config->get('config_store_id');
        }

        $result = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = '" . (int)$store_id . "' LIMIT 1");
        
        if ($result->num_rows > 0) { 
            return $result->row['value'];
        } else {
            return false;
        }
    }

    public function getSEO($route, $key, $value) {
        $hasKeyword = false;
        if (($route == 'product/product' && $key == 'product_id') || (($route == 'product/manufacturer/info' || $route == 'product/product') && $key == 'manufacturer_id') || ($route == 'information/information' && $key == 'information_id')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE language_id = '" . $this->config->get('config_language_id') . "' AND `query` = '" . $this->db->escape($key . '=' . (int)$value) . "'");

            if ($query->num_rows && $query->row['keyword']) {
                $hasKeyword = true;
            }
        } elseif ($key == 'path') {
            $categories = explode('_', $value);

            foreach ($categories as $category) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE language_id = '" . $this->config->get('config_language_id') . "' AND `query` = 'category_id=" . (int)$category . "'");

                if ($query->num_rows && $query->row['keyword']) {
    
                    $hasKeyword = true;
    
                } else {
                    $hasKeyword = false;
                }
            }
        } else {
            $custom_seo_urls = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `query` = '" . $this->db->escape($route) . "' AND `language_id` = '" . $this->config->get('config_language_id') . "' LIMIT 1");
                
            if ($custom_seo_urls->num_rows > 0) {
                $hasKeyword = true;
            }
        }

        return $hasKeyword ? $this->url->link($route, '&'. $key . '=' . $value, 'SSL') : false;
    }
    
    public function getAutoLinks($store_id = 0) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_autolinks` WHERE `store_id` = '" . (int)$store_id . "'");
        
        if ($query->num_rows > 0) {
            return $query->rows;
        } else {
            return false;
        }
    }
    
    public function getDescriptionWithAutolinks($text = "", $store_id = 0) {
        $autolinks = $this->getAutoLinks($store_id);
        
        if ($autolinks) {
            foreach ($autolinks as $autolink) {
                $actual_replacement = '<a href="'.$autolink['url'].'">'.$autolink['keyword'].'</a>';
                $text = preg_replace('~('.$autolink['keyword'].')(?!\.[a-z]{2,6})\b~u', $actual_replacement, $text);
            }
        }
        
        return $text;
    }
    
    public function getCategoryPathByCategoryId($category_id) {
        $path = NULL;
        if (!empty($category_id)) {
            $paths = $this->db->query("SELECT path_id FROM " . DB_PREFIX . "category_path WHERE category_id='" . (int)$this->db->escape($category_id) . "' ORDER BY level ASC");
            $result_paths = array();
            foreach ($paths->rows as $pathRow) {
                $result_paths[] = $pathRow['path_id'];
            }
            $path = implode('_', $result_paths);
        }
        return $path;
    }
    
    public function getManufacturerData($manufacturer_id = 0) {
        $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "seo_manufacturer_description'")->num_rows;
        
        $manufacturer_data = array();
        $language_id = $this->config->get('config_language_id');
        
        if ($table_check && $manufacturer_id != 0) {
            $meta_title = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_manufacturer_description` WHERE `manufacturer_id` = '" . $this->db->escape($manufacturer_id) . "' AND `language_id` = '" . $this->db->escape($language_id) . "' LIMIT 1");
            if (!empty($meta_title->row['meta_title'])) {
                $manufacturer_data['meta_title'] = $meta_title->row['meta_title'];
            }
            if (!empty($meta_title->row['meta_description'])) {
                $manufacturer_data['meta_description'] = $meta_title->row['meta_description'];
            }
            if (!empty($meta_title->row['meta_keyword'])) {
                $manufacturer_data['meta_keyword'] = $meta_title->row['meta_keyword'];
            }
            
            if (!empty($manufacturer_data)) 
                return $manufacturer_data;
            else
                return false;
        }
        
        return false;
    }
    
    public function MissingPageWorker($route, $store_id = 0) {
        $redirect_table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "seo_404_pages'")->num_rows;
        $status = false;
        
        if ($redirect_table_check) {
            $route_check = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_404_redirects` WHERE `route_from` = '" . $this->db->escape($route) . "' AND `store_id` = '" . (int)$store_id . "' LIMIT 1");

            if ($route_check->num_rows) {
                $status = (!empty($route_check->row['route_to'])) ? $route_check->row['route_to'] : false;
            }
        }
        
        $function_check = $this->getSetting('404_pages_gathering');

        if ($function_check && $status == false) {
            $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "seo_404_pages'")->num_rows;
            
            if ($table_check) {
                $route_check = $this->db->query("SELECT page_id FROM `" . DB_PREFIX . "seo_404_pages` WHERE `route` = '" . $this->db->escape($route) . "' AND `store_id` = '" . (int)$store_id . "' LIMIT 1");
                $route_data = array();
                
                if ($route_check->num_rows) {
                    $route_data = $route_check->row;
                }
                
                if ($route_data) {
                    $this->db->query("UPDATE `" . DB_PREFIX . "seo_404_pages` SET visits = visits+1, last_visited=NOW() WHERE page_id = ".$route_data['page_id']." AND `store_id` = '" . (int)$store_id . "'");
                } else {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_404_pages` SET route='" . $this->db->escape($route) . "', visits = 1, first_visited=NOW(), last_visited=NOW(), `store_id` = '" . (int)$store_id . "'");
                }
                
            }
        }
        
        return $status;
    }    
    
    public function getProductH1Tag($product_id = 0) {
        $result = $this->db->query("SELECT h1 FROM `" . DB_PREFIX . "seo_product_description` WHERE product_id=" . (int)$this->db->escape($product_id) . " AND language_id='".$this->config->get('config_language_id')."' LIMIT 1");
        
        if ($result->num_rows) {
            return $result->row['h1'];
        } else {
            return '';
        }
    }
    
    public function getProductH2Tag($product_id = 0) {
        $result = $this->db->query("SELECT h2 FROM `" . DB_PREFIX . "seo_product_description` WHERE product_id=" . (int)$this->db->escape($product_id) . " AND language_id='".$this->config->get('config_language_id')."' LIMIT 1");
        
        if ($result->num_rows) {
            return $result->row['h2'];
        } else {
            return '';
        }
    }

    public function getProducts($data = array()) {
        $sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

        if (!empty($data['filter_category_id'])) {
            $sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
            $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
        } else {
            $sql .= " FROM " . DB_PREFIX . "product p";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (!empty($data['filter_category_id'])) {
            $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $sql .= " GROUP BY p.product_id";
        $sql .= " ORDER BY p.date_modified DESC, p.sort_order ASC, LCASE(pd.name) ASC";

        if (isset($data['limit']) || $this->getSetting('feed_product_limit') > 1) {
            $data['start'] = isset($data['start']) ? max(0, $data['start']) : 0;
            $data['limit'] = isset($data['limit']) ? max(1, $data['limit']) : ($this->getSetting('feed_product_limit') ? $this->getSetting('feed_product_limit') : 100);

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        $this->load->model('catalog/product');
        
        $product_data = array();
        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
        }

        return $product_data;
    }
	
	public function getPathCategoryPage($category_id) {
        $query = $this->db->query("SELECT parent_id, cd.category_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name ASC");

        if ($query->row['parent_id']) {
            return $this->getPathCategoryPage($query->row['parent_id'], $this->config->get('config_language_id')) . '_' . $query->row['category_id'];
        } else {
            return $query->row['category_id'];
        }
    }
    
    public function getCategoriesByProductId($product_id) {
      $query = $this->db->query("SELECT pc.*, !ISNULL(t1.parent_id) + !ISNULL(t2.parent_id) + !ISNULL(t3.parent_id) + !ISNULL(t4.parent_id) + !ISNULL(t5.parent_id) AS d FROM " . DB_PREFIX . "product_to_category pc LEFT JOIN " . DB_PREFIX . "category t1 ON t1.category_id = pc.category_id LEFT JOIN " . DB_PREFIX . "category t2 ON t1.parent_id = t2.category_id LEFT JOIN " . DB_PREFIX . "category t3 ON t2.parent_id = t3.category_id LEFT JOIN " . DB_PREFIX . "category t4 ON t3.parent_id = t4.category_id LEFT JOIN " . DB_PREFIX . "category t5 ON t4.parent_id = t5.category_id WHERE product_id = '" . (int)$product_id . "' ORDER BY d DESC");

      return $query->rows;

    }
    
    public function getCustomUrls() {
        $store_id    = $this->config->get('config_store_id');
        $language_id = $this->config->get('config_language_id');
        $urls        = array(
            'information/contact',
            'information/sitemap',
            'product/manufacturer',
            'product/search',
            'product/special',
            'product/compare',
            'account/login',
            'account/register',
            'account/account',
            'account/edit',
            'account/password',
            'account/forgotten',
            'account/address',
            'account/wishlist',
            'account/order',
            'account/download',
            'account/reward',
            'account/voucher',
            'account/return',
            'account/return/add',
            'account/transaction',
            'account/recurring',
            'account/newsletter',
            'account/affiliate',
            'affiliate/login',
            'affiliate/register',
            'affiliate/success',
            'checkout/cart',
            'checkout/checkout',
            'checkout/success',
        );

        $query = $this->db->query("SELECT `query` FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "' ORDER BY `query` ASC");

        foreach ($query->rows as $item) {
            $urls[] = $item['query'];
        }

        return array_keys(array_flip($urls));
    }
}
