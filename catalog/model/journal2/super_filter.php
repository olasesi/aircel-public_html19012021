<?php
class ModelJournal2SuperFilter extends Model {
    public static function test() {
        return "hello world";
    }

    private static function sort($a, $b) {
        return $a['name'] > $b['name'];
    }

    private static function cmpAttrs($a, $b) {
        if(is_numeric($a['text']) && is_numeric($b['text'])){
            return ((float)$a['text'] < (float)$b['text']) ? -1 : 1;
        }

        if ($a['text'] == $b['text']) {
            return 0;
        }
        return ($a['text'] < $b['text']) ? -1 : 1;
    }

    private function addFilters($data, $query = ""){

        $sql = "";
        
        if ((isset($data['categories']) && !empty($data['categories']) || (isset($data['path']) && strlen($data['path']) > 0)) && $query != 'category') {
            if (isset($data['filter_sub_category'])) {
                $sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p2c.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "category_path` cp ON (cp.category_id = p2c.category_id)";
            } else {
                $sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (p2c.product_id = p.product_id)";
            }
        }

        $sql .= " LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)";

        $sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON p.product_id = p2s.product_id";

        $sql .= " WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        $sql .= " AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if ((isset($data['categories']) && !empty($data['categories']) ||  (isset($data['path']) && strlen($data['path']) > 0)) && $query != 'category') {
            if (isset($data['filter_sub_category'])) {
                if (isset($data['categories']) && !empty($data['categories'])) {
                    $sql .= " AND cp.path_id IN (" . implode(",", $data['categories']) . ")";
                }else{
                    $sql .= " AND cp.path_id = '" . (int)$data['path'] . "'";
                }
            } else {
                if (isset($data['categories']) && !empty($data['categories'])) {
                    $sql .= " AND p2c.category_id IN (" . implode(",", $data['categories']) . ")";
                }else{
                    $sql .= " AND p2c.category_id = '" . (int)$data['path'] . "'";
                }
            }
        }

        if(isset($data['manufacturers']) && !empty($data['manufacturers']) && $query != 'manufacturer') {
            $sql .= " AND p.manufacturer_id IN (" . implode(",", $data['manufacturers']) . ")";
        }

        if(isset($data['attributes']) && !empty($data['attributes'])  && $query != 'attribute') {
            foreach ($data['attributes'] as $attribute_id => $attribute_values) {
                $temp = "";
                foreach ($attribute_values as $key => $value) {
                    if ($key != 0) {
                        $temp .= " OR ";
                    }
                    $temp .= " TRIM(pai.text) = '" . $this->db->escape($value) . "'";
                }
                $sql .= " AND EXISTS (SELECT * FROM `" . DB_PREFIX . "product_attribute` pai WHERE p.product_id = pai.product_id AND pai.attribute_id = " . $attribute_id . " AND (" . $temp . ") )";
            }
        }

        if(isset($data['options']) && !empty($data['options']) && $query != 'option') {
            $temp = "";

            foreach ($data['options'] as $options) {

                foreach ($options as $key => $option) {
                    $temp = "  povi.option_value_id IN (" .  $option['option_value_id'] . ") ";
                }
                $sql .= " AND EXISTS (SELECT * FROM `" . DB_PREFIX . "product_option_value` povi WHERE p.product_id = povi.product_id AND " . $temp . ")";
            }
        }

        if(isset($data['oc_filters']) && !empty($data['oc_filters']) && $query != 'filter') {
            $temp = "";

            foreach ($data['oc_filters'] as $filters) {

                foreach ($filters as $key => $filter) {
                    $temp = "  pfi.filter_id IN (" . $filter['filter_id'] . ") ";
                }

                $sql .= " AND EXISTS (SELECT * FROM `" . DB_PREFIX . "product_filter` pfi WHERE p.product_id = pfi.product_id AND " . $temp . ")";
            }
        }

        if(isset($data['special'])) {
            if ($this->customer->isLogged()) {
                $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
            } else {
                $customer_group_id = $this->config->get('config_customer_group_id');
            }

            $sql .= " AND EXISTS (SELECT product_id FROM `" . DB_PREFIX . "product_special` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())))";
        }


        if((isset($data['search']) && strlen($data['search']) > 0) || (isset($data['tags']) && !empty($data['tags']))) {
            $sql .= " AND (";

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['search'])));

                foreach ($words as $word) {
                    $implode[] = " LOWER(pd.name) LIKE '%" . utf8_strtolower($this->db->escape($word)) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (isset($data['description']) && $data['description'] == 1) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['search']) . "%'";
                }
            }

            if ((isset($data['search']) && strlen($data['search']) > 0) && (isset($data['tags']) && !empty($data['tags']))) {
                $sql .= " OR ";
            }

            if (isset($data['tags']) && !empty($data['tags'])) {

                $sql .= "";
                foreach ($data['tags'] as $key => $tag) {
                    if ($key == 0) {
                        $sql .= " pd.tag LIKE '%" . $this->db->escape($tag) . "%'";
                    }else{
                        $sql .= " OR pd.tag LIKE '%" . $this->db->escape($tag) . "%'";
                    }
                }
                $sql .= "";
            }

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['search'])) . "'";
            }

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['search'])) . "'";
            }

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['search'])) . "'";
            }

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['search'])) . "'";
            }

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['search'])) . "'";
            }

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['search'])) . "'";
            }

            if (isset($data['search']) && strlen($data['search']) > 0) {
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['search'])) . "'";
            }

            $sql .= ")";
        }

        if($query != 'price' && isset($data['minPrice']) && isset($data['maxPrice']) && $data['maxPrice'] != -1 && $data['minPrice'] != -1) {

            if ($this->customer->isLogged()) {
                $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
            } else {
                $customer_group_id = $this->config->get('config_customer_group_id');
            }

            $special  = "(SELECT price FROM `" . DB_PREFIX . "product_special` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1)";
            $sql .= " AND COALESCE(" . $special . ", p.price) BETWEEN " . (float)$data['minPrice'] . " AND " . (float)$data['maxPrice'] . "";
        }

        if (isset($data['availability']) && is_array($data['availability'])) {
            if (count($data['availability']) === 1) {
                if ($data['availability'][0] > 0) {
                    $sql .= ' AND p.quantity > 0';
                } else {
                    $sql .= ' AND p.quantity <= 0';
                }
            }
        }

        return $sql;
    }

    public function getManufacturers($data = array()){

        $sql = "SELECT max(m.manufacturer_id) manufacturer_id, MAX(m.name) name, MAX(m.image) image, COUNT(*) total FROM `" . DB_PREFIX . "manufacturer` m LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m.manufacturer_id = m2s.manufacturer_id) LEFT JOIN `" . DB_PREFIX . "product` p ON p.manufacturer_id = m.manufacturer_id";

        $sql .= $this->addFilters($data, 'manufacturer');

        $sql .= " AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        $sql .= " GROUP BY m.manufacturer_id HAVING COUNT(*) > 0 ORDER BY m.name, m.sort_order ASC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCategories($data = array()){

        $sql = "SELECT MAX(c.category_id) category_id, MAX(cd.name) name, MAX(c.image) image, COUNT(*) total FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id) LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id) LEFT JOIN `" . DB_PREFIX . "product_to_category`p2c ON c.category_id = p2c.category_id  LEFT JOIN `" . DB_PREFIX . "product` p ON p.product_id = p2c.product_id";

        $sql .= $this->addFilters($data, 'category');

        $sql .= " AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1'";

        if(isset($data['path']) && strlen($data['path']) > 0) {
            $sql .= " AND c.parent_id = '" . $this->db->escape($data['path']) . "'";
        }

		$sql .= " GROUP BY c.category_id HAVING COUNT(*) > 0 ORDER BY c.sort_order ASC, LCASE(cd.name) ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAttributes($data = array()){
        if(isset($data['attributes']) && !empty($data['attributes'])) {
            $product_attributes = $this->getProductAttributes($data);
            foreach ($data['attributes'] as $attribute_id => $value) {
                foreach($product_attributes as $key => $attribute) {
                    if($key == $attribute_id) {
                        unset($product_attributes[$key]);
                    }
                }

                $temp_data = $data;
                unset($temp_data['attributes'][$attribute_id]);
                foreach($this->getProductAttributes($temp_data) as $key => $attribute){
                    if($key == $attribute_id) {
                        $product_attributes[$attribute_id] = $attribute;
                    }
                }
            }

            $results = $product_attributes;
        }else{
            $results = $this->getProductAttributes($data);
        }

        ksort($results);
        return $results;
    }

    private function getProductAttributes($data = array()){
        $data['start'] = 0;
        $data['limit'] = PHP_INT_MAX;

        $products = $this->getProducts($data);

        if (count($products) == 0) {
            return array();
        }

        $sql = "SELECT pa.product_id, MAX(agd.attribute_group_id) as attribute_group_id, MAX(agd.name) as attribute_group_name, MAX(a.attribute_id) as attribute_id, MAX(ad.name) as attribute_name, MAX(pa.text) text, COUNT(*) total FROM `". DB_PREFIX . "product_attribute` pa LEFT JOIN `". DB_PREFIX . "attribute` a ON a.attribute_id = pa.attribute_id LEFT JOIN `". DB_PREFIX . "attribute_description` ad ON ad.attribute_id = a.attribute_id LEFT JOIN `". DB_PREFIX . "attribute_group_description` agd ON agd.attribute_group_id = a.attribute_group_id";

        $sql .= " WHERE pa.product_id IN (" . implode(",", $products) . ")";

        $sql .= " AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sql .= " GROUP BY lower(pa.text), a.attribute_id HAVING COUNT(*) > 0";

        $query = $this->db->query($sql);

        $results = array();

        foreach ($query->rows as $row) {
            if (!isset($results[$row['attribute_id']])) {
                $results[$row['attribute_id']] = array(
                    'attribute_id'          => $row['attribute_id'],
                    'attribute_name'        => $row['attribute_name'],
                    'values'                => array()
                );
            }
            $results[$row['attribute_id']]['values'][] = array(
                'text'                  => $row['text'],
                'total'                 => $row['total'],
            );
        }

        foreach ($results as $attribute_id => &$value) {
            usort($value['values'], array('ModelJournal2SuperFilter', 'cmpAttrs'));
        }

        return $results;
    }

    public function getOptions($data = array()){

      if(isset($data['options']) && !empty($data['options'])) {
            $product_options = $this->getProductOptions($data);

            foreach ($data['options'] as $option_id => $value) {
                foreach($product_options as $key => $option) {
                    if($key == $option_id) {
                        unset($product_options[$key]);
                    }
                }

                $temp_data = $data;
                unset($temp_data['options'][$option_id]);
                foreach($this->getProductOptions($temp_data) as $key => $option){
                    if($key == $option_id) {
                        $product_options[$option_id] = $option;
                    }
                }
            }

            $results = $product_options;
        }else{
            $results = $this->getProductOptions($data);
        }

        ksort($results);
        return $results;
    }

    public function getProductOptions($data = array()){

      $sql = "SELECT MAX(pov.option_id) as option_id, MAX(od.name) as option_name, MAX(ovd.option_value_id) as option_value_id, MAX(ovd.name) as option_value_name, COUNT(*) total, ov.image FROM  `" . DB_PREFIX . "product_option_value` pov LEFT JOIN `". DB_PREFIX . "option_value` ov ON pov.option_value_id = ov.option_value_id LEFT JOIN `". DB_PREFIX . "option_value_description` ovd ON pov.option_value_id = ovd.option_value_id LEFT JOIN `". DB_PREFIX . "option_description` od ON pov.option_id = od.option_id ";
      
        if(isset($data['options']) && !empty($data['options'])) {
            $data['start'] = 0;
            $data['limit'] = PHP_INT_MAX;

            $products = $this->getProducts($data);

            if (count($products) == 0) {
                return array();
            }

            $sql .= " WHERE pov.product_id IN (" . implode(",", $products) . ")";
        }
        else{

            $sql .= " LEFT JOIN `" . DB_PREFIX . "product` p ON p.product_id = pov.product_id";

            $sql .= $this->addFilters($data, 'option');
        }
        
        $sql .= " AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sql .= " GROUP BY pov.option_value_id HAVING COUNT(*) > 0 ORDER BY ov.sort_order, ovd.name";

        $query = $this->db->query($sql);

        $results = array();

        foreach ($query->rows as $row) {
            if (!isset($results[$row['option_id']])) {
                $results[$row['option_id']] = array(
                    'option_id'          => $row['option_id'],
                    'option_name'        => $row['option_name'],
                    'values'             => array()
                );
            }
            $results[$row['option_id']]['values'][] = array(
                'option_value_id'    => $row['option_value_id'],
                'option_value_name'  => $row['option_value_name'],
                'image'              => isset($row['image']) ? $row['image'] : '',
                'total'              => $row['total'],
            );
        }

        return $results;
    }

    public function getFilters($data = array()) {
        $sql = "
			SELECT
				f.filter_id filter_id,
				fd.name filter_name,
				fg.filter_group_id filter_group_id,
				fgd.name filter_group_name,
				COUNT(*) total
			FROM `" . DB_PREFIX . "product` p
			INNER JOIN `" . DB_PREFIX . "product_filter` pf ON (p.product_id = pf.product_id)
			INNER JOIN `" . DB_PREFIX . "filter` f ON (f.filter_id = pf.filter_id)
			INNER JOIN `" . DB_PREFIX . "filter_description` fd ON (fd.filter_id = pf.filter_id)
			INNER JOIN `" . DB_PREFIX . "filter_group` fg ON (fg.filter_group_id = fd.filter_group_id)
			INNER JOIN `" . DB_PREFIX . "filter_group_description` fgd ON (fd.filter_group_id = fgd.filter_group_id)
		";

        $sql .= $this->addFilters($data, 'filter');

        $sql .= "
            AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			GROUP BY pf.filter_id
			HAVING COUNT(*) > 0
            ORDER BY f.sort_order, LCASE(fd.name)
		";

        $query = $this->db->query($sql);

        $results = array();

        foreach ($query->rows as $row) {
            if (!isset($results[$row['filter_group_id']])) {
                $results[$row['filter_group_id']] = array(
                    'filter_group_id'   => $row['filter_group_id'],
                    'filter_group_name' => $row['filter_group_name'],
                    'values'            => array(),
                );
            }
            $results[$row['filter_group_id']]['values'][] = array(
                'filter_id'   => $row['filter_id'],
                'filter_name' => $row['filter_name'],
                'total'       => $row['total'],
            );
        }

        return $results;
    }

    public function getTags($data = array()){

        $sql = "SELECT tag FROM `" . DB_PREFIX . "product` p";
        
        $sql .= $this->addFilters($data, 'tags');

        $sql .= " AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $query = $this->db->query($sql);

        $results = array();

        foreach ($query->rows as $row) {
            foreach (explode(",", $row['tag']) as $value) {
                $value = trim($value);
                if (strlen($value) <= 1) {
                    continue;
                }
                if (!isset($results[$value])) {
                    $results[$value] = array(
                        'name'  => $value,
                        'total' => 1
                    );
                }else{
                    $results[$value]['total']++;
                }
            }
        }

        usort($results, array('ModelJournal2SuperFilter', 'sort'));

        return $results;
    }

    public function getPrice($data = array()){
        if ($this->customer->isLogged()) {
            $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $discount = "(SELECT price FROM `" . DB_PREFIX . "product_discount` pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1)";
        $special  = "(SELECT price FROM `" . DB_PREFIX . "product_special` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1)";

        $sql = "SELECT MAX(COALESCE(" . $special . ", p.price)) as max, MIN(COALESCE(" . $special . ", p.price)) as min FROM `" . DB_PREFIX . "product` p";

        $sql .= $this->addFilters($data, 'price');

        $query = $this->db->query($sql);

        return $query->row;
    }

    private function getProducts($data = array()) {
        if ($this->customer->isLogged()) {
            $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM `" . DB_PREFIX . "review` r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM `" . DB_PREFIX . "product_discount` pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM `" . DB_PREFIX . "product_special` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM `" . DB_PREFIX . "product` p";

        $sql .= $this->addFilters($data, 'product');

        $sql .= " GROUP BY p.product_id";

        if (isset($data['sort']) && $data['sort'] === 'ps.price') {
        	$data['sort'] = 'p.price';
		}

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } elseif ($data['sort'] == 'p.price') {
                $sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(pd.name) DESC";
        } else {
            $sql .= " ASC, LCASE(pd.name) ASC";
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
        $products = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $products[$result['product_id']] = $result['product_id'];
        }

        return $products;
    }

    public function getProductsWithData($data = array()) {
        $products = $this->getProducts($data);

        $this->load->model('catalog/product');

        $product_data = array();
        foreach ($products as $product) {
            $product_data[$product] = $this->model_catalog_product->getProduct($product);
        }

        return $product_data;
    }

    public function getTotalProducts($data = array()) {
        if ($this->customer->isLogged()) {
            $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM `" . DB_PREFIX . "product` p";

        $sql .= $this->addFilters($data);

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getRates($value, $tax_class_id) {
        $tax_rates = array();

        if ($this->customer->isLogged()) {
            $customer_group_id = version_compare(VERSION, '2', '>=') ? $this->customer->getGroupId() : $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        if (isset($this->session->data['shipping_country_id']) || isset($this->session->data['shipping_zone_id'])) {
            $shipping_address = array(
                'country_id' => $this->session->data['shipping_country_id'], 
                'zone_id'    => $this->session->data['shipping_zone_id']);
        } elseif ($this->config->get('config_tax_default') == 'shipping') {
            $shipping_address = array(
                'country_id' => $this->config->get('config_country_id'), 
                'zone_id'    => $this->config->get('config_zone_id'));
        }

        if (isset($this->session->data['payment_country_id']) || isset($this->session->data['payment_zone_id'])) {
            $payment_address = array(
                'country_id' => $this->session->data['payment_country_id'], 
                'zone_id'    =>$this->session->data['payment_zone_id']);
        } elseif ($this->config->get('config_tax_default') == 'payment') {
            $payment_address = array(
                'country_id' => $this->config->get('config_country_id'), 
                'zone_id'    =>$this->config->get('config_zone_id'));
        }

        $store_address = array(
            'country_id' => $this->config->get('config_country_id'), 
            'zone_id'    => $this->config->get('config_zone_id'));

        if (isset($shipping_address)) {
            $tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM `" . DB_PREFIX . "tax_rule` tr1 LEFT JOIN `" . DB_PREFIX . "tax_rate` tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN `" . DB_PREFIX . "tax_rate_to_customer_group` tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN `" . DB_PREFIX . "zone_to_geo_zone` z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN `" . DB_PREFIX . "geo_zone` gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'shipping' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$shipping_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$shipping_address['zone_id'] . "') ORDER BY tr1.priority ASC");

            foreach ($tax_query->rows as $result) {
                $tax_rates[$result['tax_rate_id']] = array(
                    'tax_rate_id' => $result['tax_rate_id'],
                    'name'        => $result['name'],
                    'rate'        => $result['rate'],
                    'type'        => $result['type'],
                    'priority'    => $result['priority']
                );
            }
        }

        if (isset($payment_address)) {
            $tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM `" . DB_PREFIX . "tax_rule` tr1 LEFT JOIN `" . DB_PREFIX . "tax_rate` tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN `" . DB_PREFIX . "tax_rate_to_customer_group` tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN `" . DB_PREFIX . "zone_to_geo_zone` z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN `" . DB_PREFIX . "geo_zone` gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'payment' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$payment_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$payment_address['zone_id'] . "') ORDER BY tr1.priority ASC");

            foreach ($tax_query->rows as $result) {
                $tax_rates[$result['tax_rate_id']] = array(
                    'tax_rate_id' => $result['tax_rate_id'],
                    'name'        => $result['name'],
                    'rate'        => $result['rate'],
                    'type'        => $result['type'],
                    'priority'    => $result['priority']
                );
            }
        }

        if (isset($store_address)) {
            $tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM `" . DB_PREFIX . "tax_rule` tr1 LEFT JOIN `" . DB_PREFIX . "tax_rate` tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN `" . DB_PREFIX . "tax_rate_to_customer_group` tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN `" . DB_PREFIX . "zone_to_geo_zone` z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN `" . DB_PREFIX . "geo_zone` gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'store' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$store_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$store_address['zone_id'] . "') ORDER BY tr1.priority ASC");

            foreach ($tax_query->rows as $result) {
                $tax_rates[$result['tax_rate_id']] = array(
                    'tax_rate_id' => $result['tax_rate_id'],
                    'name'        => $result['name'],
                    'rate'        => $result['rate'],
                    'type'        => $result['type'],
                    'priority'    => $result['priority']
                );
            }
        }           

        $amount = $value;
        $procent = 0;
        foreach ($tax_rates as $tax_rate) {
            if ($tax_rate['type'] == 'F') {
                $amount -= $tax_rate['rate'];
            } elseif ($tax_rate['type'] == 'P') {
                $procent += $tax_rate['rate'];
            }
        }
        if ($procent != 0) {
            $amount /= (1 + ($procent / 100));
        }
        return $amount;
    }
}
?>
