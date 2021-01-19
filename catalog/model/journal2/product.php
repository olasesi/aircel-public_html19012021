<?php
if (!property_exists('Front', 'IS_OC2') && !property_exists('Router', 'IS_OC2')) {
    echo '
        <h3>Journal Installation Error</h3>
        <p>Make sure you have uploaded all Journal files to your server and successfully replaced <b>system/engine/front.php</b> file.</p>
        <p>You can find more information <a href="http://docs.digital-atelier.com/opencart/journal/#/settings/install" target="_blank">here</a>.</p>
    ';
    exit();
}
class ModelJournal2Product extends Model {

    private static $cache = array();
    private static $latest = null;
    private static $category_products = null;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('catalog/product');
        if (version_compare(VERSION, '3', '>=')) {
            $this->load->model('setting/module');
        } else if (version_compare(VERSION, '2', '>=')) {
            $this->load->model('extension/module');
        }
//        if (isset($this->request->get['path'])) {
//            $parts = explode('_', $this->request->get['path']);
//            $category_id = end($parts);
//            if ($category_id) {
//                self::$category_products = array();
//                foreach ($this->getProductsByCategory($category_id, PHP_INT_MAX) as $value) {
//                    self::$category_products[] = $value['product_id'];
//                }
//            }
//        }
    }

    private function addLabel($product_id, $label, $name) {
        if (!isset(self::$cache[$product_id])) {
            self::$cache[$product_id] = array();
        }
        self::$cache[$product_id][$label] = $name;
    }

    private function hasLabel($product_id, $label) {
        if (!isset(self::$cache[$product_id])) {
            return false;
        }
        return in_array($label, self::$cache[$product_id]);
    }

    public function getLabels($product_id) {
        if (!defined('JOURNAL_INSTALLED')) {
            return array();
        }
        /* get latest label */
        if ($this->journal2->settings->get('label_latest_status', 'always') !== 'never') {
            if (self::$latest === null) {
                self::$latest = $this->model_catalog_product->getLatestProducts($this->journal2->settings->get('label_latest_limit', 10));
            }
            if (!$this->hasLabel($product_id, 'latest') && is_array(self::$latest)) {
                foreach (self::$latest as $product) {
                    if ($product_id == $product['product_id']) {
                        $this->addLabel($product_id, 'latest', $this->journal2->settings->get('label_latest_text', 'New'));
                        break;
                    }
                }
            }
        }

        $product = $this->model_catalog_product->getProduct($product_id);

		if ($product) {
			/* get special label */
			if ($this->journal2->settings->get('label_special_status', 'always') !== 'never') {
				if ((float)$product['special']) {
					if ($this->journal2->settings->get('label_special_type', 'percent') === 'percent') {
						if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
							$price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
						} else {
							$price = false;
						}
						$special = $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
						if ($price > 0.0) {
							if ($this->language->get('direction') === 'ltr') {
								$this->addLabel($product_id, 'sale', '-' . round(($price - $special) / $price * 100) . '%');
							} else {
								$this->addLabel($product_id, 'sale', '-' . round(($price - $special) / $price * 100) . ' %');
							}
						}
					} else {
						$this->addLabel($product_id, 'sale', $this->journal2->settings->get('label_special_text', 'Sale'));
					}
				}
			}

			/* get stock label */
			if (($this->journal2->settings->get('out_of_stock_status', 'always') !== 'never') && ($product['quantity'] <= 0) && Journal2Utils::canGenerateImages()) {
				$this->addLabel($product_id, 'outofstock', $product['stock_status']);
			}
		}

        if (!isset(self::$cache[$product_id])) {
            return array();
        }

        return self::$cache[$product_id];
    }

    public function getSpecialCountdown($product_id) {
        if ($this->customer->isLogged()) {
            $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }
        $query = $this->db->query("SELECT date_end FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = '" . (int)$product_id . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1");
        if (!isset($query->row['date_end']) || $query->row['date_end'] === '0000-00-00') {
            return false;
        }
        return date('D M d Y H:i:s O', strtotime($query->row['date_end']));
    }
    
    public function getSpecialCountdownProducts($data = array()) {
        // if ($this->customer->isLogged()) {
        //     $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
        // } else {
        //     $customer_group_id = $this->config->get('config_customer_group_id');
        // }
        // $query = $this->db->query("SELECT date_end FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = '" . (int)$product_id . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC");
        // if (!isset($query->row['date_end']) || $query->row['date_end'] === '0000-00-00') {
        //     return false;
        // }
        $sql = "SELECT * from " . DB_PREFIX . "product_special ps WHERE (ps.date_end > NOW())";
        $product_data = array();
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		}
        if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
        $query = $this->db->query($sql);
        foreach($query->rows as $result) {
            $product_data[]['product_id'] = $result['product_id'];
        }
        return $product_data;
        // return $query;
    }


    public function getProductViews($product_id) {
        $query = $this->db->query("SELECT viewed FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        return $query->num_rows ? $query->row['viewed'] : null;
    }

    public function getProductSoldCount($product_id) {
        $query = $this->db->query("SELECT sum(op.quantity) as quantity FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON op.order_id = o.order_id WHERE o.order_status_id <> 0 AND op.product_id = '" . (int)$product_id . "'");
        return (int)$query->row['quantity'];
    }

    public function getRandomProducts ($limit = 4, $category_id = -1) {
        if (!$limit) {
            $limit = 4;
        }
        $sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";

        if ($category_id !== -1) {
            $sql .= " LEFT JOIN " . DB_PREFIX ."product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        $sql .= " WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if ($category_id !== -1) {
            $sql .= " AND p2c.category_id = '" . (int)$category_id . "'";
        }

        $sql .= " ORDER BY rand() LIMIT " . (int)$limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getFeatured($limit = 4, $featured_module_id, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $results = array();
                $i = 0;
                foreach ($this->getFeaturedProducts($featured_module_id) as $product_id) {
                    if ($filter_category && self::$category_products !== null && !in_array($product_id, self::$category_products)) continue;
                    $result = $this->model_catalog_product->getProduct($product_id);
                    if (!$result) continue;
                    $results[] = $result;
                    $i++;
                    if ($limit && $i == $limit) {
                        break;
                    }
                }
                return $results;
            }
        }
        $results = array();
        $i = 0;
        foreach ($this->getFeaturedProducts($featured_module_id) as $product_id) {
            $result = $this->model_catalog_product->getProduct($product_id);
            if (!$result) continue;
            $results[] = $result;
            $i++;
            if ($limit && $i == $limit) {
                break;
            }
        }
        return $results;
    }

    private function getFeaturedProducts($featured_module_id) {
        if (!version_compare(VERSION, '2', '>=')) {
            return explode(',', $this->config->get('featured_product'));
        }
        if (version_compare(VERSION, '3', '>=')) {
            return Journal2Utils::getProperty($this->model_setting_module->getModule($featured_module_id), 'product', array());
        }

        return Journal2Utils::getProperty($this->model_extension_module->getModule($featured_module_id), 'product', array());
    }

    public function getBestsellers($limit = 4, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $results = array();
                $i = 0;
                foreach ($this->model_catalog_product->getBestSellerProducts(PHP_INT_MAX) as $product) {
                    $i++;
                    if ($filter_category && self::$category_products !== null && !in_array($product['product_id'], self::$category_products)) continue;
                    $results[] = $product;
                    if ($limit && $i == $limit) {
                        break;
                    }
                }
                return $results;
            }
        }
        return $this->model_catalog_product->getBestSellerProducts($limit);
    }

    public function getSpecials($limit = 4, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $data = array(
                    'sort'  => 'rand()',
                    'order' => 'ASC',
                    'start' => 0
                );
                $results = array();
                $i = 0;
                foreach ($this->model_catalog_product->getProductSpecials($data) as $product) {
                    $i++;
                    if ($filter_category && self::$category_products !== null && !in_array($product['product_id'], self::$category_products)) continue;
                    $results[] = $product;
                    if ($limit && $i == $limit) {
                        break;
                    }
                }
                return $results;
            }
        }
        $data = array(
            'sort'  => 'rand()',
            'order' => 'ASC',
            'start' => 0,
            'limit' => $limit
        );
        return $this->model_catalog_product->getProductSpecials($data);
    }

    public function getLatest($limit = 4, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $data = array(
                    'sort'  => 'p.date_added',
                    'order' => 'DESC',
                    'start' => 0,
                    'limit' => PHP_INT_MAX
                );
                $results = array();
                $i = 0;
                foreach ($this->model_catalog_product->getProducts($data) as $product) {
                    $i++;
                    if ($filter_category && self::$category_products !== null && !in_array($product['product_id'], self::$category_products)) continue;
                    $results[] = $product;
                    if ($limit && $i == $limit) {
                        break;
                    }
                }
                return $results;
            }
        }
        $data = array(
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => 0,
            'limit' => $limit
        );
        return $this->model_catalog_product->getProducts($data);
    }

    public function getProductsByCategory($category_id, $limit = 4) {
        return $this->model_catalog_product->getProducts(array(
            'filter_category_id' => $category_id,
            'start' => 0,
            'sort'  => 'rand()',
            'limit' => $limit
        ));
    }

    public function getProductsByManufacturer($manufacturer_id, $limit = 4) {
        return $this->model_catalog_product->getProducts(array(
            'filter_manufacturer_id' => $manufacturer_id,
            'start' => 0,
            'sort'  => 'rand()',
            'limit' => $limit
        ));
    }

    public function getPeopleAlsoBought($product_id, $limit = 4) {
        $sql = '
            SELECT distinct product_id FROM ' . DB_PREFIX . 'order_product WHERE product_id <> ' . (int)$product_id . ' AND order_id IN (
                SELECT order_id FROM ' . DB_PREFIX . 'order_product WHERE product_id = ' . (int)$product_id . '
            ) LIMIT ' . (int) $limit . '
        ';
        $query = $this->db->query($sql);
        $results = array();
        foreach ($query->rows as $row) {
            $result = $this->model_catalog_product->getProduct($row['product_id']);
            if ($result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    public function getProductRelated($product_id, $limit = 4) {
        return array_slice($this->model_catalog_product->getProductRelated($product_id), 0, $limit);
    }

    public function getMostViewed($limit = 4) {
        $sql = '
            SELECT p.product_id
            FROM ' . DB_PREFIX . 'product p
            LEFT JOIN ' . DB_PREFIX . 'product_to_store p2s ON (p.product_id = p2s.product_id)
            WHERE p.status = "1"
                AND p.date_available <= NOW()
                AND p2s.store_id = "' . (int)$this->config->get('config_store_id') . '"
            ORDER BY rand()
            LIMIT ' . (int)$limit;
        $query = $this->db->query($sql);
        $results = array();
        foreach ($query->rows as $row) {
            $result = $this->model_catalog_product->getProduct($row['product_id']);
            if ($result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    public function getRecentlyViewed($limit = 4) {
        $products = isset($this->request->cookie['jrv']) && $this->request->cookie['jrv'] ? explode(',', $this->request->cookie['jrv']) : array();
        $products = array_slice($products, 0, $limit);
        $results = array();
        foreach ($products as $pid) {
            $result = $this->model_catalog_product->getProduct($pid);
            if ($result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    public function getProductOptionsOC1($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();

				$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

				foreach ($product_option_value_query->rows as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'name'                    => $product_option_value['name'],
						'image'                   => $product_option_value['image'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
                        'points'                  => $product_option_value['points'],
                        'points_prefix'           => $product_option_value['points_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']
					);
				}

				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option_value_data,
					'required'          => $product_option['required']
				);
			} else {
				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
					'required'          => $product_option['required']
				);
			}
		}

		return $product_option_data;
	}

    public function getProductOptionsOC2($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'name'                    => $product_option_value['name'],
					'image'                   => $product_option_value['image'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
                    'points'                  => $product_option_value['points'],
                    'points_prefix'           => $product_option_value['points_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}

}
?>
