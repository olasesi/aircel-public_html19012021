<?php 
class ModelExtensionPurpletreeMultivendorSellerproduct extends Model{
	////// For Sub Category ////////
	public function getParentCategories($child_id) {		
		$query = $this->db->query("SELECT DISTINCT c.parent_id,cd.name  FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.parent_id = cd.category_id AND cd.language_id = '" . (int)$this->config->get('config_language_id')."')WHERE c.category_id = '" . (int)$child_id . "'");
		return $query->row;
	}
	public function getSubcategory($parent_id) {		
		$query = $this->db->query("SELECT DISTINCT c.category_id,cd.name  FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id AND cd.language_id = '" . (int)$this->config->get('config_language_id')."')WHERE c.parent_id = '" . (int)$parent_id . "'"); 
		return $query->rows;
	}
	public function getCategories11($data = array()) {
		
		$sql = "SELECT cp.category_id AS category_id, cd1.name AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c1.parent_id = 0";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		if(empty($data['category_type'])){
			$sql .= " AND c1.category_id IN (" . $this->db->escape($data['category_allow']).")";
		}
		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $this->db->escape($data['sort']);
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	////// End Sub Category ////////
	 public function getSellerProducts($data=array()){

		$sql = "SELECT pd.*, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating,p.*,pvp.* ,CONCAT(c.firstname, ' ', c.lastname) AS seller_name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvp.product_id=p.product_id) JOIN " .DB_PREFIX. "customer c ON(c.customer_id=pvp.seller_id) LEFT JOIN ".DB_PREFIX. "product_to_category ptc ON(ptc.product_id=p.product_id)";

		if(!empty($data['seller_id'])){
			
			$sql .= "WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvp.seller_id ='".(int)$data['seller_id']."'";
		} else {
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		}	
		//
		if(isset($data['product'])){
		if(!empty($data['product'])){
				$sql.="AND pvp.product_id IN (".$data['product'].")";		
		}	
		}

		if(!empty($data['category_id']))
		{
			$sql .= " AND ptc.category_id = '" . (int)$data['category_id'] . "'";
		}
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['status']) && !is_null($data['status'])) {
			$sql .= " AND p.status = '" . (int)$data['status'] . "'";
		}
		
		if (isset($data['is_approved']) && !is_null($data['is_approved'])) {
			$sql .= " AND pvp.is_approved = '" . (int)$data['is_approved'] . "'";
		}
		
		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 5;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		//echo $sql;
	//	die;
		$query = $this->db->query($sql);

		return $query->rows;
	}
	public function getSsellerplanStatus($seller_id) {
	    $sql="SELECT pvps.status_id FROM ". DB_PREFIX ."purpletree_vendor_plan pvp  LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_subscription pvps ON ((pvps.seller_id = pvsp.seller_id) AND (pvps.status_id = pvp.status)) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
		$query = $this->db->query($sql);
		if($query->num_rows){
		   return $query->row['status_id'];
		} else { 
		   return false;
	}
}
	public function sellerTotalPlanStatus($seller_id){
		 $sql="SELECT pvps.status_id FROM ". DB_PREFIX ."purpletree_vendor_plan pvp  LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_subscription pvps ON ((pvps.seller_id = pvsp.seller_id) AND (pvps.status_id = pvp.status)) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
			$query = $this->db->query($sql);
	if($query->num_rows){	
		return $query->row;
	} else {
		return NULL;	
	}
	}
	
	public function sellerTotalProduct($seller_id){
		$query=$this->db->query("SELECT COUNT(*) AS total_product FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int) $seller_id."'");
	if($query->num_rows){	
		return $query->row;
	} else {
		return NULL;	
	}
	}
	
	public function getInvoiceStatus($seller_id){
		$query=$this->db->query("SELECT pvpi.status_id AS invoice_status FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp ON (pvpi.invoice_id = pvsp.invoice_id) WHERE pvsp.seller_id='".(int) $seller_id."' AND pvsp.status=1");
	if($query->num_rows){	
		return $query->row['invoice_status'];
	} else {
		return NULL;	
	}
	}
	
	
	public function getNoOfProduct($seller_id){
		$query=$this->db->query("SELECT pvp.no_of_product FROM " . DB_PREFIX . "purpletree_vendor_plan pvp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvsp.seller_id='".(int) $seller_id."' AND pvsp.status=1");
		if($query->num_rows){	
			return $query->row;
		} else {
			return NULL;	
		}
	}
	
	public function getTotalSellerProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvp.product_id=p.product_id) JOIN " .DB_PREFIX. "customer c ON(c.customer_id=pvp.seller_id) LEFT JOIN ".DB_PREFIX. "product_to_category ptc ON(ptc.product_id=p.product_id)";
		
		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		if(!empty($data['seller_id'])){
			$sql .= " AND pvp.seller_id ='".(int)$data['seller_id']."'";
		}
		if(isset($data['product'])){
		if(!empty($data['product'])){
				$sql.="AND pvp.product_id IN (".$data['product'].")";		
		}	
		}
		if(!empty($data['category_id']))
		{
			$sql .= " AND ptc.category_id = '" . (int)$data['category_id'] . "'";
		}
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['status']) && !is_null($data['status'])) {
			$sql .= " AND p.status = '" . (int)$data['status'] . "'";
		}
		
		if (isset($data['is_approved']) && !is_null($data['is_approved'])) {
			$sql .= " AND pvp.is_approved = '" . (int)$data['is_approved'] . "'";
		}
		
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function deleteProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_products WHERE product_id = '" . (int)$product_id . "'");

		$this->cache->delete('product');
	}
	
	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}
	
	public function getProduct($product_id, $seller_id=NULL) {
		
		$sql = "SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' limit 0,1) AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvp.product_id=p.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
	
		if($seller_id){
			$sql .= " AND pvp.seller_id='".(int)$seller_id."'";
		}
		
		$query = $this->db->query($sql);

		return $query->row;
	}
	
	public function getProductOptions($product_id) {
		$product_option_data = array();

		$seller_id = $this->customer->getId();
		
		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON po.product_id = pvp.product_id LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvp.seller_id = '".(int)$seller_id."'");
		
		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY ov.sort_order ASC");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
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
	
	public function getOptionValue($option_value_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_value_id = '" . (int)$option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
	
	public function getOptionValues($option_id) {
		$option_value_data = array();

		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '" . (int)$option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order, ovd.name");

		foreach ($option_value_query->rows as $option_value) {
			$option_value_data[] = array(
				'option_value_id' => $option_value['option_value_id'],
				'name'            => $option_value['name'],
				'image'           => $option_value['image'],
				'sort_order'      => $option_value['sort_order']
			);
		}

		return $option_value_data;
	}
	
	public function getProducts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) JOIN  " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvp.product_id=pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['seller_id']) && !is_null($data['seller_id'])) {
			$sql .= " AND pvp.seller_id = '" . (int)$data['seller_id'] . "'";	
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	
	public function addProduct($data) {
		if(!isset($data['sku'])){
	           $data['sku'] = "";
	        }
	        if(!isset($data['upc'])){
	           $data['upc'] = "";
	        }
	         if(!isset($data['ean'])){
	           $data['ean'] = "";
	        }
	         if(!isset($data['jan'])){
	           $data['jan'] = "";
	        }
		 if(!isset($data['isbn'])){
	           $data['isbn'] = "";
	        }
	        if(!isset($data['mpn'])){
	           $data['mpn'] = "";
	        }
	        if(!isset($data['location'])){
	           $data['location'] = "";
	        }
	        if(!isset($data['manufacturer_id'])){
	           $data['manufacturer_id'] = "";
	        }
	        if(!isset($data['points'])){
	           $data['points'] = "";
	        }
	        if(!isset($data['weight_class_id'])){
	           $data['weight_class_id'] = "";
	        }
	        if(!isset($data['length_class_id'])){
	           $data['length_class_id'] = "";
	        }
	        if(!isset($data['sort_order'])){
	           $data['sort_order'] = "";
	        }
		
		if($this->config->get('module_purpletree_multivendor_product_approval')){
			$data['status'] = 0;
			$is_approved = 0;
		} else{
			$data['status'] = $data['status'];
			$is_approved = 1;
		}
		$price_extra_type = "";
		if(isset($data['price_extra_type'])) {
			$price_extra_type = ", price_extra_type = '" . (int)$data['price_extra_type'] . "'";
		}
		$price_extra = "";
		if(isset($data['price_extra'])) {
			$price_extra = ", price_extra = '" . (float)$data['price_extra'] . "'";
		}
		$metal = "";
		if(isset($data['metal'])) {
			$metal = "metal = '" . $this->db->escape($data['metal']) . "',";
		}
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET ".$metal." model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "'".$price_extra_type.$price_extra.", points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		$product_id = $this->db->getLastId();
		
		/////// category featured and featured product /////////
			$is_featured = 0;
		if(isset($data['is_featured'])){
		  $is_featured = $data['is_featured'];
		} elseif(isset($data['featured_product_plan_id'])){
			if($data['featured_product_plan_id'] > 0) {
			$is_featured = 1;
			}
		}
		$is_category_featured = 0;
		if(isset($data['is_category_featured'])){
		  $is_category_featured = $data['is_category_featured'];
		}
		elseif(isset($data['category_featured_product_plan_id'])){
			if($data['category_featured_product_plan_id'] > 0) {
			$is_category_featured = 1;
			}
		}
		if($this->config->get('module_purpletree_multivendor_subscription_plans')){
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_id = '" . (int)$product_id."', product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."'");
		}
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_products SET product_id = '" . (int)$product_id."', seller_id = '".(int)$data['seller_id']."', is_approved ='".(int)$is_approved."',is_featured ='".(int)$is_featured."',is_category_featured ='".(int)$is_category_featured."', created_at =NOW(), updated_at =NOW()");
 		
		/////// End category featured and featured product /////////		
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		foreach ($data['product_description'] as $language_id => $value) {
			
			if($value['meta_title'] == ''){
				$value['meta_title'] = trim($value['name']);
			}
			
			if($value['meta_description'] == ''){
				
				$value['meta_description'] = strip_tags(html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8'));
			}
			
			if($value['meta_keyword'] == ''){
				$value['meta_keyword'] = $value['tag'];
			}
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				if ((int)$product_reward['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
				}
			}
		}

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		// SEO URL
		if (isset($this->request->post['product_seo_url'])) {
		foreach($this->request->post['product_seo_url'] as $store_id => $language){
				foreach($language as $language_id => $keyword) {
					if($keyword == ''){
						$this->request->post['product_seo_url'][$store_id][$language_id] = 
						$this->request->post['product_description'][$language_id]['name'];
					}
				}
			}
		}
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
				/* 		echo "<pre>";
					print_r($keyword);
					die; */
					if($keyword == ''){
						
						$name=array();
						$name=explode(' ',trim($this->request->post['product_description'][$language_id]['name']));
						$keywords=implode('_',$name);
						$keyword = strtolower($keywords)."_".$product_id."_".$language_id;
					}
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
					
				}
			}
		}

		$this->cache->delete('product');

		return $product_id;
	}
	
		public function copyProduct($product_id, $seller_id) {
		
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p WHERE p.product_id = '" . (int)$product_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['sku'] = '';
			$data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';
			
			$data['seller_id'] = (int)$seller_id;

			$data['product_attribute'] = $this->getProductAttributes((int)$product_id);
			$data['product_description'] = $this->getProductDescriptions((int)$product_id);
			$data['product_discount'] = $this->getProductDiscounts((int)$product_id);
			$data['product_filter'] = $this->getProductFilters((int)$product_id);
			$data['product_image'] = $this->getProductImages((int)$product_id);
			$data['product_option'] = $this->getProductOptions((int)$product_id);
			$data['product_related'] = $this->getProductRelated((int)$product_id);
			$data['product_reward'] = $this->getProductRewards((int)$product_id);
			$data['product_special'] = $this->getProductSpecials((int)$product_id);
			$data['product_category'] = $this->getProductCategories((int)$product_id);
			$data['product_download'] = $this->getProductDownloads((int)$product_id);
			$data['product_store'] = $this->getProductStores((int)$product_id);
			$data['category_featured_product_plan_id'] = $this->categoryFeaturedProductPlanName((int)$product_id);
            $data['featured_product_plan_id'] = $this->featuredProductPlanName((int)$product_id);
            $data['product_plan_id'] = $this->productPlanName((int)$product_id);

			$this->addProduct($data);
		}
	}

	public function editProduct($product_id, $data) {
		 if(!isset($data['product_plan_id '])){
	           $data['product_plan_id '] = 0;
	    }
		if(!isset($data['sku'])){
	           $data['sku'] = "";
	        }
	        if(!isset($data['upc'])){
	           $data['upc'] = "";
	        }
	         if(!isset($data['ean'])){
	           $data['ean'] = "";
	        }
	         if(!isset($data['jan'])){
	           $data['jan'] = "";
	        }
		 if(!isset($data['isbn'])){
	           $data['isbn'] = "";
	        }
	        if(!isset($data['mpn'])){
	           $data['mpn'] = "";
	        }
	        if(!isset($data['location'])){
	           $data['location'] = "";
	        }
	        if(!isset($data['manufacturer_id'])){
	           $data['manufacturer_id'] = "";
	        }
	        if(!isset($data['points'])){
	           $data['points'] = "";
	        }
	        if(!isset($data['weight_class_id'])){
	           $data['weight_class_id'] = "";
	        }
	        if(!isset($data['length_class_id'])){
	           $data['length_class_id'] = "";
	        }
	        if(!isset($data['sort_order'])){
	           $data['sort_order'] = "";
	        }
		if(isset($data['is_approved']) && ($data['is_approved'] == 1)){
			$data['status'] = (int)$data['status'];
			$is_approved = 1;
		} else{
			$data['status'] = 0;
			$is_approved = 0;
		}
		
		$seller_id = $this->customer->getId();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_products WHERE product_id='" . (int)$product_id . "' AND seller_id='" . (int)$seller_id."'");
		if($query->num_rows != 1){
			return false;
		}
		$price_extra_type = "";
		if(isset($data['price_extra_type'])) {
			$price_extra_type = ", price_extra_type = '" . (int)$data['price_extra_type'] . "'";
		}
		$price_extra = "";
		if(isset($data['price_extra'])) {
			$price_extra = ", price_extra = '" . (float)$data['price_extra'] . "'";
		}
		$metal = "";
		if(isset($data['metal'])) {
			$metal = "metal = '" . $this->db->escape($data['metal']) . "',";
		}
		
		$this->db->query("UPDATE " . DB_PREFIX . "product SET ".$metal." model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "'".$price_extra_type.$price_extra.", points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
		/* if($this->config->get('module_purpletree_multivendor_subscription_plans')){
			$obj_product=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id = '" . (int)$product_id."'");
			if($obj_product->num_rows>0){
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."' WHERE product_id = '" . (int)$product_id."'");								
			} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_id = '" . (int)$product_id."', product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."'");			
			}
		} */
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}		
		/////// category featured and featured product /////////
			$is_featured = 0;
		if(isset($data['is_featured'])){
		  $is_featured = $data['is_featured'];
		}
		elseif(isset($data['featured_product_plan_id'])){
			if($data['featured_product_plan_id'] > 0) {
			$is_featured = 1;
			}
		}
		$is_category_featured = 0;
		if(isset($data['is_category_featured'])){
		  $is_category_featured = $data['is_category_featured'];
		}
		elseif(isset($data['category_featured_product_plan_id'])){
			if($data['category_featured_product_plan_id'] > 0) {
			 $is_category_featured = 1;
			}
		}
		
			if($this->config->get('module_purpletree_multivendor_subscription_plans')){
			$obj_product=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id = '" . (int)$product_id."'");
			if($obj_product->num_rows>0){
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."' WHERE product_id = '" . (int)$product_id."'");								
			} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_id = '" . (int)$product_id."', product_plan_id = '".(int)$data['product_plan_id']."', featured_product_plan_id = '".(int)$data['featured_product_plan_id']."', category_featured_product_plan_id = '".(int)$data['category_featured_product_plan_id']."'");			
			}
	
		}
		/* $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products SET is_featured ='".(int)$data['is_featured']."',is_category_featured ='".(int)$data['is_category_featured']."' WHERE product_id = '" . (int)$product_id . "'"); */
	    /////// End category featured and featured product /////////
	
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		//$data['seller_id'] = array();
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products SET is_approved ='".(int)$is_approved."',is_featured ='".(int)$is_featured."',is_category_featured ='".(int)$is_category_featured."', updated_at =NOW() WHERE product_id = '" . (int)$product_id."' AND seller_id = '".(int)$seller_id."'");

		foreach ($data['product_description'] as $language_id => $value) {
			
			if($value['meta_title'] == ''){
				$value['meta_title'] = trim($value['name']);
			}
			
			if($value['meta_description'] == ''){
				
				$value['meta_description'] = strip_tags(html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8'));
			}
			
			if($value['meta_keyword'] == ''){
				$value['meta_keyword'] = $value['tag'];
			}
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				if ((int)$value['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		
	/* 	echo $this->request->post['product_description'][$language_id]['name'];
		die;
		 */
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}


		$this->cache->delete('product');
	}
	
	public function getProductDescriptions($product_id) {
		$product_description_data = array();

		$seller_id = $this->customer->getId();
		
		$query = $this->db->query("SELECT pd.* FROM " . DB_PREFIX . "product_description pd JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON pd.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}

		return $product_description_data;
	}
	
	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}
	
	public function getProductCategories($product_id) {
		$product_category_data = array();

		$seller_id = $this->customer->getId();

		$query = $this->db->query("SELECT ptc.* FROM " . DB_PREFIX . "product_to_category ptc JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON ptc.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}
	public function getSubCategories($category_id)
	{
	
		$query = $this->db->query("SELECT cd.category_id,cd.name FROM " . DB_PREFIX . "category c JOIN " .DB_PREFIX . "category_description cd ON(c.parent_id=cd.category_id) WHERE c.category_id =". (int)$category_id);
		return $query->rows;
		
	}
	
	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id) AS path, (SELECT DISTINCT keyword FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "') AS keyword FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
	
	public function getProductFilters($product_id) {
		$product_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_filter_data[] = $result['filter_id'];
		}

		return $product_filter_data;
	}
	
	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$seller_id = $this->customer->getId();

		$product_attribute_query = $this->db->query("SELECT pa.attribute_id FROM " . DB_PREFIX . "product_attribute pa JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON pa.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."' GROUP BY pa.attribute_id");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}
	
	public function getProductDiscounts($product_id) {
		
		$seller_id = $this->customer->getId();
		
		$query = $this->db->query("SELECT pd.* FROM " . DB_PREFIX . "product_discount pd JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON pd.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."' ORDER BY pd.quantity, pd.priority, pd.price");

		return $query->rows;
	}
	
	public function getProductImages($product_id,$sellerid = null) {
		$seller_id = isset($sellerid) ? $sellerid : $this->customer->getId();
// 	/	$seller_id = $this->customer->getId(); added the seller as param because with mobile its impossible to get custoemerid like this
		
		$query = $this->db->query("SELECT pi.* FROM " . DB_PREFIX . "product_image pi JOIN ".DB_PREFIX."purpletree_vendor_products pvp  ON pi.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."' ORDER BY sort_order ASC");

		return $query->rows;
	}

	
	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$seller_id = $this->customer->getId();
		
		$query = $this->db->query("SELECT ptd.* FROM " . DB_PREFIX . "product_to_download ptd JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON ptd.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}
	
	public function getProductRelated($product_id) {
		$product_related_data = array();
		
		$seller_id = $this->customer->getId();
		
		$query = $this->db->query("SELECT pr.* FROM " . DB_PREFIX . "product_related pr JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON pr.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}
	
	public function getProductRewards($product_id) {
		$product_reward_data = array();
		
		$seller_id = $this->customer->getId();
		
		$query = $this->db->query("SELECT pr.* FROM " . DB_PREFIX . "product_reward pr JOIN ".DB_PREFIX."purpletree_vendor_products pvp ON pr.product_id = pvp.product_id WHERE pvp.product_id = '" . (int)$product_id . "' AND pvp.seller_id = '".(int)$seller_id."'");
		
		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $product_reward_data;
	}
	
	public function getManufacturers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	
	public function getCategories($data = array()) {
		
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		if(empty($data['category_type'])){
			$sql .= " AND c1.category_id IN (" . $this->db->escape($data['category_allow']).")";
		}
		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $this->db->escape($data['sort']);
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}

    //added 09/01/2020 ici
	public function getCategoriesALL($data = array()) {
		
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}else{
		    $sql .= " AND cd2.name LIKE '%'";
		}
		
		if(empty($data['category_type'])){
			$sql .= " AND c1.category_id IN (" . $this->db->escape($data['category_allow']).")";
		}
		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $this->db->escape($data['sort']);
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	
	public function getFilters($data) {
		$sql = "SELECT *, (SELECT name FROM " . DB_PREFIX . "filter_group_description fgd WHERE f.filter_group_id = fgd.filter_group_id AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS `group` FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND fd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " ORDER BY f.sort_order ASC";

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

		return $query->rows;
	}
	
	public function getDownloads($data = array()) {
			$sql = "SELECT * FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) JOIN ". DB_PREFIX ."purpletree_vendor_downloads pvd ON (pvd.download_id = d.download_id) WHERE dd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND dd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
			if (isset($data['seller_id']) && !is_null($data['seller_id'])) {
			$sql .= " AND pvd.seller_id = '" . (int)$data['seller_id'] . "'";	
		}
		$sort_data = array(
			'dd.name',
			'd.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY dd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	
	public function getAttributes($data = array()) {
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}

		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY attribute_group, ad.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	
	public function getOptions($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND od.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'od.name',
			'o.type',
			'o.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY od.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	
	public function getSellername($product_id){

		$query  = $this->db->query("SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name,pvp.seller_id,pvs.id, pvs.store_name FROM " . DB_PREFIX . "purpletree_vendor_products pvp JOIN " . DB_PREFIX . "customer c ON(c.customer_id=pvp.seller_id) JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON(pvs.seller_id= pvp.seller_id) WHERE pvp.product_id ='".(int)$product_id."' AND is_approved=1");
		return $query->row;
	}
	
	public function getProductSeoUrls($product_id) {
		$product_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $product_seo_url_data;
	}

	public function getvendorcatagory($vendor_id)
	{
		$query = $this->db->query("SELECT pvc.vendor_category_id,cd.name FROM " . DB_PREFIX ."purpletree_vendor_categories pvc JOIN " . DB_PREFIX . "category_description cd on(cd.category_id=pvc.vendor_category_id) WHERE pvc.vendor_id=".(int)$vendor_id);
		return $query->rows;
	}
	
	
	public function getSellerProductsCategories($data=array()){
		
		$query = $this->db->query("SELECT cd.name,c.status,ptc.category_id,c.parent_id,cp.path_id FROM " . DB_PREFIX . "purpletree_vendor_products pvp JOIN ". DB_PREFIX ."product_to_category ptc on(pvp.product_id=ptc.product_id) JOIN " . DB_PREFIX. "category c on(c.category_id=ptc.category_id) JOIN " . DB_PREFIX . "category_description cd on(cd.category_id=c.category_id) JOIN " . DB_PREFIX. "category_path cp on(cp.category_id=cd.category_id) WHERE c.status=1 AND cd.language_id='".(int)$data['language_id']."' AND pvp.seller_id='".(int)$data['seller_id']."' AND c.parent_id='".(int)$data['category_id']."' GROUP BY ptc.category_id");
		
		return $query->rows;
	}
	
	public function checkParentCategory($category_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE parent_id =". (int)$category_id);
		return $query->rows;
	}
	/////// category featured and featured product /////////
	public function sellerAllowedFeaturedProduct($seller_id){
		
			$query=$this->db->query("SELECT no_of_featured_product FROM " . DB_PREFIX . "purpletree_vendor_plan WHERE plan_id IN (SELECT plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int) $seller_id."' AND status=1 )");
		if($query->num_rows){	
			return $query->row['no_of_featured_product'];
		} else {
			return NULL;	
		}
		}
		
		public function sellerAllowedCategoryFeaturedProduct($seller_id){
			$query=$this->db->query("SELECT no_of_category_featured_product FROM " . DB_PREFIX . "purpletree_vendor_plan WHERE plan_id IN (SELECT plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int) $seller_id."' AND status=1 )");
		if($query->num_rows){	
		
			return $query->row['no_of_category_featured_product'];
			
		} else {
			return NULL;	
		}
		}
		
		public function sellerTotalFeaturedProduct($seller_id,$product_id = NULL){
		$sql = "SELECT COUNT(*) AS total_featured_product FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int) $seller_id."' AND is_featured=1";
		if($product_id) {
			$sql .= " AND product_id !=".$product_id;
		}
		$query = $this->db->query($sql);
	if($query->num_rows){
	
		return $query->row['total_featured_product'];
	} else {
		return NULL;	
	}
	}
	
	
	public function sellerTotalCategpryFeaturedProduct($seller_id,$product_id=null){
		$sql  ="SELECT COUNT(*) AS total_catogry_featured_product FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int) $seller_id."' AND is_category_featured=1";
		if($product_id) {
			$sql .= " AND product_id !=".$product_id;
		}
		$query = $this->db->query($sql);
	if($query->num_rows){
		
		return $query->row['total_catogry_featured_product'];
	} else {
		return NULL;	
	}
	}
	
	
	public function change_is_featured($product_id, $value) {
		
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products SET is_featured ='".(int)$value."' WHERE product_id = '" . (int)$product_id . "'");
	}
	public function change_is_category_featured($product_id, $value) {
		
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products SET is_category_featured ='".(int)$value."' WHERE product_id = '" . (int)$product_id . "'");
	}
	//menu 
		public function sellerProductId($seller_id) {
		$query=$this->db->query("SELECT product_id FROM ". DB_PREFIX . "purpletree_vendor_products  WHERE seller_id = '" . (int)$seller_id . "' AND is_approved=1");
	if($query->num_rows){
		return $query->rows;
	} else {
		return NULL;	
	}
		
	}
	
			public function getSellerId($store_id) {
		$query=$this->db->query("SELECT seller_id FROM ". DB_PREFIX . "purpletree_vendor_stores  WHERE id = '" . (int)$store_id . "'");
	if($query->num_rows){
		return $query->row['seller_id'];
	} else {
		return NULL;	
	}
		
	}
	
	public function categoryId($product_id) {
		
		$query=$this->db->query("SELECT category_id FROM ". DB_PREFIX . "product_to_category  WHERE product_id = '" . (int)$product_id . "'");
		
		if($query->num_rows){
		return $query->rows;
		} else {
			return NULL;	
		}
	}
	
	public function pId($category_id) {		
		$query=$this->db->query("SELECT parent_id FROM ". DB_PREFIX . "category  WHERE category_id = '" . (int)$category_id . "'");
	if($query->num_rows){
		return $query->row['parent_id'];
	} else {
		return NULL;	
	}
	}	
	
	
		public function parentId($product_id) {
			$p_id=array();		
			while($product_id!=0){
			$p_id[]=$product_id;
			$product_id=$this->pId($product_id);
			$p_id[]=$product_id;
			};	
			array_pop($p_id);
			$parent_id=end($p_id);
			return $parent_id;
		}
		
	public function parentDescription($parent_id) {
		$query=$this->db->query("SELECT name FROM ". DB_PREFIX . "category_description  WHERE category_id = '" . (int)$parent_id . "'");
	if($query->num_rows){
		return $query->row['name'];
	} else {
		return NULL;	
	}
		
	}
	
	public function productPlanInfo($seller_id) {
		$query=$this->db->query("SELECT id FROM ". DB_PREFIX . "purpletree_vendor_plan_subscription  WHERE status_id=1 AND seller_id = '" . (int)$seller_id . "'");
		if($query->num_rows){
			if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
			$obj_plan_name=$this->db->query("SELECT pvpd.plan_name,pvp.plan_id FROM ". DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpi.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id=pvpd.plan_id) WHERE pvpi.status_id=2 AND pvpi.invoice_id IN (SELECT invoice_id FROM ". DB_PREFIX . "purpletree_vendor_seller_plan pvsp WHERE pvsp.new_status=1 AND pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvsp.seller_id='".(int)$seller_id."' ) AND pvp.status=1");
			} else {
			$obj_plan_name=$this->db->query("SELECT pvpd.plan_name,pvp.plan_id FROM ". DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpi.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id=pvpd.plan_id) WHERE pvpi.status_id=2 AND pvpi.invoice_id IN (SELECT invoice_id FROM ". DB_PREFIX . "purpletree_vendor_seller_plan pvsp WHERE pvsp.status=1 AND pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pvsp.seller_id='".(int)$seller_id."' AND pvp.status=1 ) ");	
			}
		if($obj_plan_name->num_rows){
		return $obj_plan_name->rows;
		} else {
			return NULL;
		}
		
	} else {
		return NULL;	
	}
		
	}
	
	public function productPlanName($product_id) {
		$query=$this->db->query("SELECT product_plan_id FROM ". DB_PREFIX . "purpletree_vendor_subscription_products  WHERE product_id = '" . (int)$product_id . "'");
	if($query->num_rows){
		return $query->row['product_plan_id'];
	} else {
		return NULL;	
	}
		
	}	

	public function sellerActiveProduct($seller_id,$plan_id,$product_id = NULL){
		$no_of_product = 0;
		$query11 = $this->db->query("SELECT no_of_product FROM ". DB_PREFIX . "purpletree_vendor_plan pvp WHERE plan_id='".$plan_id."'");
		if($query11->num_rows){	
			$no_of_product = $query11->row['no_of_product'];
		}
		$sql = "SELECT COUNT(*) AS product_count FROM ". DB_PREFIX . "purpletree_vendor_products pvp LEFT JOIN ". DB_PREFIX . "purpletree_vendor_subscription_products pvsp ON(pvp.product_id=pvsp.product_id) WHERE pvp.seller_id='".(int)$seller_id."' AND pvsp.product_plan_id='".(int)$plan_id."'";
		if($product_id) {
			$sql .= " AND pvp.product_id !=".$product_id;
		}
		$seller_assign_product= 0;
		$query11= $this->db->query($sql);
			if($query11->num_rows){	
				$seller_assign_product = $query11->row['product_count'];
			}
		if($seller_assign_product<$no_of_product){
		return true;
		} else {
		return false;	
		}
	
	}
	public function featuredProductPlanName($product_id) {
		$query=$this->db->query("SELECT featured_product_plan_id  FROM ". DB_PREFIX . "purpletree_vendor_subscription_products  WHERE product_id = '" . (int)$product_id . "'");
	if($query->num_rows){
		return $query->row['featured_product_plan_id'];
	} else {
		return NULL;	
	}
	}
	public function categoryFeaturedProductPlanName($product_id) {
		$query=$this->db->query("SELECT category_featured_product_plan_id  FROM ". DB_PREFIX . "purpletree_vendor_subscription_products  WHERE product_id = '" . (int)$product_id . "'");
	if($query->num_rows){
		return $query->row['category_featured_product_plan_id'];
	} else {
		return NULL;	
	}
		
	}
	public function getCatgoryFeaturedPlanProduct($plan_id) {
		$query=$this->db->query("SELECT no_of_category_featured_product   FROM ". DB_PREFIX . "purpletree_vendor_plan  WHERE plan_id = '" . (int)$plan_id . "'");
	if($query->num_rows){
		return $query->row['no_of_category_featured_product'];
	} else {
		return NULL;	
	}
		
	}
	public function getCatgoryFeaturedTotalProduct($plan_id,$seller_id) {
		$query=$this->db->query("SELECT COUNT(*) AS catgory_featured_total_product  FROM ". DB_PREFIX . "purpletree_vendor_subscription_products pvsp LEFT JOIN ". DB_PREFIX . "purpletree_vendor_products pvpro ON(pvpro.product_id = pvsp.product_id) WHERE pvsp.category_featured_product_plan_id = '" . (int)$plan_id . "' AND pvpro.seller_id= '" . (int)$seller_id . "'");
	if($query->num_rows){
		return $query->row['catgory_featured_total_product'];
	} else {
		return NULL;	
	}
		
	}
	public function getFeaturedPlanProduct($plan_id) {
		$query=$this->db->query("SELECT no_of_featured_product   FROM ". DB_PREFIX . "purpletree_vendor_plan  WHERE plan_id = '" . (int)$plan_id . "'");
	if($query->num_rows){
		return $query->row['no_of_featured_product'];
	} else {
		return NULL;	
	}
		
	}
	public function getFeaturedTotalProduct($plan_id,$seller_id) {
		$query=$this->db->query("SELECT COUNT(*) AS featured_total_product  FROM ". DB_PREFIX . "purpletree_vendor_subscription_products pvsp LEFT JOIN ". DB_PREFIX . "purpletree_vendor_products pvpro ON(pvpro.product_id = pvsp.product_id) WHERE pvsp.featured_product_plan_id = '" . (int)$plan_id . "' AND pvpro.seller_id= '" . (int)$seller_id . "'");
	if($query->num_rows){
		return $query->row['featured_total_product'];
	} else {
		return NULL;	
	}
		
	}
	public function addFeaturedProductByPopup($product_id,$plan_id) {
		   
		if($this->config->get('module_purpletree_multivendor_subscription_plans')){
			$obj_product=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id = '" . (int)$product_id."'");
			$value = 0;
			if(isset($plan_id)){
				if($plan_id > 0) {
					$value = 1;
				}
			}
			if($obj_product->num_rows>0){
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_subscription_products SET featured_product_plan_id = '".(int)$plan_id."' WHERE product_id = '" . (int)$product_id."'");	
			
			} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_id = '" . (int)$product_id."',featured_product_plan_id = '".(int)$plan_id."'");			
			}
			$this->change_is_featured($product_id, $value);
	
		}
      }
	public function addCategoryFeaturedProductByPopup($product_id,$plan_id) {
		   
		if($this->config->get('module_purpletree_multivendor_subscription_plans')){
			$obj_product=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id = '" . (int)$product_id."'");
			$value = 0;
			if(isset($plan_id)){
				if($plan_id > 0) {
					$value = 1;
				}
			}
			if($obj_product->num_rows>0){
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_subscription_products SET category_featured_product_plan_id = '".(int)$plan_id."' WHERE product_id = '" . (int)$product_id."'");	
			
			} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_subscription_products SET product_id = '" . (int)$product_id."',category_featured_product_plan_id = '".(int)$plan_id."'");			
			}
			$this->change_is_category_featured($product_id, $value);
	
		}
      }
	public function removeCategoryFeaturedProduct($product_id) {
		   
		if($this->config->get('module_purpletree_multivendor_subscription_plans')){
			$obj_product=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id = '" . (int)$product_id."'");
			if($obj_product->num_rows>0){
			$plan_id = 0;
			$seller_id = $this->customer->getId();
			$is_category_featured = 0;
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_subscription_products SET category_featured_product_plan_id = '".(int)$plan_id."' WHERE product_id = '" . (int)$product_id."'");
            $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products SET is_category_featured ='".(int)$is_category_featured."', updated_at =NOW() WHERE product_id = '" . (int)$product_id."' AND seller_id = '".(int)$seller_id."'");			
			} else {
					
			}
	
		}
      }
     public function removeFeaturedProduct($product_id) {
		   
		if($this->config->get('module_purpletree_multivendor_subscription_plans')){
			$obj_product=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_subscription_products WHERE product_id = '" . (int)$product_id."'");
			if($obj_product->num_rows>0){
			$plan_id = 0;
			$is_featured = 0;
			$seller_id = $this->customer->getId();
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_subscription_products SET featured_product_plan_id = '".(int)$plan_id."' WHERE product_id = '" . (int)$product_id."'");
            $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products SET is_featured ='".(int)$is_featured."', updated_at =NOW() WHERE product_id = '" . (int)$product_id."' AND seller_id = '".(int)$seller_id."'");			
			} else {
			
			}
	
		}
      }
	public function getNoOfProductForMultiplePlan($seller_id){
		$query=$this->db->query("SELECT SUM(pvp.no_of_product) AS no_of_product FROM " . DB_PREFIX . "purpletree_vendor_plan pvp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvsp.seller_id='".(int) $seller_id."' AND pvsp.new_status=1");
		if($query->num_rows){	
			return $query->row;
		} else {
			return NULL;	
		}
	}
	public function sellerAllowedFeaturedProductForMultiplePlan($seller_id){
		$query=$this->db->query("SELECT SUM(no_of_featured_product) AS no_of_featured_product FROM " . DB_PREFIX . "purpletree_vendor_plan WHERE plan_id IN (SELECT plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int) $seller_id."' AND new_status=1 )");
			
	if($query->num_rows){	
		return $query->row['no_of_featured_product'];
	} else {
		return NULL;	
	}
	}	
	public function sellerAllowedCategoryFeaturedProductForMultiplePlan($seller_id){
		$query=$this->db->query("SELECT SUM(no_of_category_featured_product) AS no_of_category_featured_product FROM " . DB_PREFIX . "purpletree_vendor_plan WHERE plan_id IN (SELECT plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int) $seller_id."' AND new_status=1 )");
	if($query->num_rows){	
		
		return $query->row['no_of_category_featured_product'];
			
	} else {
		return NULL;	
	}
	}
	public function activeProduct($data=array()){
		$grace = (int)$this->config->get('module_purpletree_multivendor_grace_period');
			
		$query=$this->db->query("SELECT pvpp.product_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_products pvpp ON(pvsp.seller_id=pvpp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvspp ON(pvpp.product_id=pvspp.product_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON(pvps.seller_id=pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_invoice pvpi ON(pvsp.invoice_id=pvsp.invoice_id) WHERE  (NOW() BETWEEN pvsp.start_date AND DATE_ADD(pvsp.start_date, INTERVAL (SELECT SUM(pvp.validity+".$grace.") as validity FROM " . DB_PREFIX . "purpletree_vendor_plan pvp WHERE pvp.plan_id=pvsp.plan_id) DAY)) AND pvsp.seller_id='".$data['seller_id']."' AND pvsp.new_status=1 AND pvps.status_id=1 AND pvpi.invoice_id=2 AND pvpp.is_approved=1 AND pvspp.product_plan_id=pvsp.plan_id");
	if($query->num_rows>0){
		return $query->rows;
	} else {
		return NULL;
	}
		}
	public function categoryMenu($store_id){
		$query = $this->db->query("SELECT pvp.product_id,cd.name,c.category_id,c.parent_id FROM " . DB_PREFIX . "purpletree_vendor_stores pvs LEFT JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvs.seller_id=pvp.seller_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (pvp.product_id=p2c.product_id) LEFT JOIN " . DB_PREFIX . "category c ON(p2c.category_id=c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd ON(c.category_id=cd.category_id) LEFT JOIN " . DB_PREFIX . "product p ON(p.product_id=pvp.product_id) WHERE pvs.id='".$store_id."' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status=1 AND pvp.is_approved=1 GROUP BY c.category_id" );

		if($query->num_rows>0){
			return $query->rows;
		}else {
			return NULL;
		}		
	}
	
	public function categoryMenuProduct($store_id,$category_id){
		$query = $this->db->query("SELECT pvp.product_id,cd.name,c.category_id,c.parent_id FROM " . DB_PREFIX . "purpletree_vendor_stores pvs LEFT JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvs.seller_id=pvp.seller_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (pvp.product_id=p2c.product_id) LEFT JOIN " . DB_PREFIX . "category c ON(p2c.category_id=c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd ON(c.category_id=cd.category_id) LEFT JOIN " . DB_PREFIX . "product p ON(p.product_id=pvp.product_id) WHERE pvs.id='".$store_id."' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status=1 AND pvp.is_approved=1 AND c.category_id='".$category_id."'" );

		if($query->num_rows>0){
			return $query->rows;
		}else {
			return NULL;
		}		
	}
	public function disabledproduct($product_id){
		
		$this->db->query("UPDATE " . DB_PREFIX . "product SET status =0 WHERE product_id='".(int)$product_id."'");
	}
	public function approveProduct($product_id){
	    $is_approved = 1;
		$error=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_products pvp WHERE pvp.product_id='".(int)$product_id."' AND pvp.is_approved =0 ");
		$this->db->query("UPDATE " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON (p.product_id=pvp.product_id) SET p.status =".$is_approved ." WHERE pvp.product_id='".(int)$product_id."' AND pvp.is_approved =1 ");
		if($error->num_rows){
			$error1=1;
		return $error1;
		} else {
			$success=0;
			return $success;
		}
	}
			public function hideEdit($seller_id,$product_id){
		
		$query=$this->db->query("SELECT is_featured FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".$seller_id."' AND product_id='".(int)$product_id."'");
		if($query->num_rows>0){
			return $query->row['is_featured'];
		}else {
			return NULL;	
		}
	}
		public function getEnableProductList($seller_id) {	

		$query = $this->db->query("SELECT DISTINCT pvp.product_id FROM " . DB_PREFIX . "purpletree_vendor_products pvp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvsp ON(pvp.product_id=pvsp.product_id) WHERE pvp.seller_id = '" . (int)$seller_id . "'AND pvsp.product_plan_id IN(SELECT plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON(pvsp.seller_id=pvps.seller_id) WHERE pvsp.seller_id = '" . (int)$seller_id . "' AND pvsp.new_status =1 AND pvps.status_id=1)");  

		if($query->num_rows>0){
			return $query->rows;
		} else {
			return NULL;
		}
	}
	/////// End category featured and featured product /////////
}
?>