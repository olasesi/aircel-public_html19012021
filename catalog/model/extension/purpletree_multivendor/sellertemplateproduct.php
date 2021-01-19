<?php 
class ModelExtensionPurpletreeMultivendorSellertemplateproduct extends Model{

	 public function getSellerProducts($data=array()){
		$sql = "SELECT pvt.id,pvt.product_id,pvtps.id As seller_template_id,pvtps.seller_id,p.product_id,p.model,pvs.store_name AS store_name,pd.name AS product_name,p.image,pvtps.status,pvtps.quantity,pvtps.price,pvtps.stock_status_id,pvtps.template_id,ss.name as stock_status FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtps ON (pvt.id = pvtps.template_id AND pvtps.seller_id= '".$this->customer->getId()."') LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id)";	
		
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql.="AND pvt.product_id = p.product_id AND pvt.status !=0";		

		
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND pvtps.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND pvtps.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

	 	if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND pvtps.status = '" . (int)$data['filter_status'] . "'";
		}
		
		
		$sql .= " GROUP BY pvt.id";

	/* 	$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		); */

		/* if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		} */

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
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
public function getTotalSellerProducts($data = array()) {
		
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total  FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtps ON (pvt.id = pvtps.template_id AND pvtps.seller_id= '".$this->customer->getId()."') LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id)";
		
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";			
			$sql.="AND pvt.status = 1";	
		
		
		
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
		
		$query = $this->db->query($sql);

		return $query->row['total'];
	}	
	public function getProducts($data = array()) {
        $sql = "SELECT pvt.id,pvt.product_id,pvtps.id As seller_template_id,pvtps.seller_id,p.product_id,p.model,pvs.store_name AS store_name,pd.name AS product_name,p.image,pvtps.status,pvtps.quantity,pvtps.price,pvtps.stock_status_id,pvtps.template_id,ss.name as stock_status FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtps ON (pvt.id = pvtps.template_id AND pvtps.seller_id= '".$this->customer->getId()."') LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id)";	
		
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql.="AND pvt.product_id = p.product_id AND pvt.status !=0";		

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
	public function addProductTemplate($template_id, $data) {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_template_products SET  template_id = '" . (int)$template_id . "',seller_id = '" . (int)$data['seller_id'] . "',  quantity = '" . (int)$data['quantity'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', price = '" . (float)$data['price'] . "',status = '" . (int)$data['status'] . "'");		
		}
	public function editProductTemplate($id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_template_products SET    quantity = '" . (int)$data['quantity'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', price = '" . (float)$data['price'] . "',status = '" . (int)$data['status'] . "' WHERE id = '" . (int)$id . "'");			
	}	
	public function getMinPrice($product_id) {
		$sql = $this->db->query("SELECT MIN(price) AS min_price,pvtp.quantity,pvtp.stock_status_id,pvtp.subtract,pvtp.status AS subtract_status,pvt.product_id FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvtp.template_id = pvt.id) WHERE pvt.product_id = '" . (int)$product_id . "'AND pvtp.status = 1 AND quantity >=1 ");

		return $sql->row;
	}
     public function getProduct($id, $seller_id = NULL) {
		$sql = "SELECT pvtps.*,p.image,pd.name,pd.description FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtps  LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.id = pvtps.template_id) LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)WHERE pvtps.id = '" . (int)$id . "'AND pvtps.seller_id = '" . (int)$seller_id . "'";
	
		$query = $this->db->query($sql);
		return $query->row;
	}
	public function deleteProduct($template_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE id = '" . (int)$template_id . "'");		
		}
   public function getTemplatePrice($template_id) {
		
		$template_price = array();
		$query = $this->db->query("SELECT pvtp.*,pvs.store_name,pvs.store_logo,p.minimum FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvtp.template_id = pvt.id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id  = pvtp.seller_id) LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id  = pvt.product_id ) WHERE pvtp.template_id ='". (int)$template_id ."' AND pvs.store_status = 1");
		if($query->num_rows) {
			$template_price = $query->rows;
		}		
		return $template_price;
		
	}
    public function getStoreRating($seller_id){
		$query = $this->db->query("SELECT AVG(rating) as rating FROM " . DB_PREFIX . "purpletree_vendor_reviews where seller_id='".(int)$seller_id."' AND status=1");	
		return $query->row['rating'];
	}	
	public function updatePrice($minprice) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . (int)$minprice['min_price'] . "',stock_status_id = '" . (int)$minprice['stock_status_id'] . "', subtract = '" . (int)$minprice['subtract'] . "', quantity = '" . (int)$minprice['quantity'] . "' WHERE product_id = '" . (int)$minprice['product_id'] . "'");

	}
		
		public function getTemplateInfo($tem_id) {		
		$template_info= array();
		$query = $this->db->query("SELECT p.image,pd.name,pd.description FROM ". DB_PREFIX . "product p  LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id  = p.product_id ) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.product_id  = p.product_id ) WHERE pvt.id ='". (int)$tem_id ."'");
		if($query->num_rows) {
			$template_info = $query->row;
		}
		return $template_info;
		
	}
	public function updateZero($temp_product_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET price = 0, quantity = 0 WHERE product_id = '" . (int)$temp_product_id . "'");
	}
	public function getTemplateProductId($temp_id) {
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_template WHERE id =". (int)$temp_id);
		if($query->num_rows>0){
			 $template_id = $query->row['product_id'];
			return $template_id;
		}
	}

}
?>