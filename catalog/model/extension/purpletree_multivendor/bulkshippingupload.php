<?php 
class ModelExtensionPurpletreeMultivendorBulkshippingupload extends Model{

		public function getTableName($tableName)
		{
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "".$this->db->escape($tableName)."`");
			if ($query->num_rows) {
				$table=array();
				$tableName=array();
				foreach($query->rows as $key =>$value)
				{
				$table[]=$value['Field'];	
				}
				$Cell=array_slice(range('A','Z'),0,count($table),true);	
				return array_combine($Cell,$table);
		} else {
			return null;	
		}
		}
		public function getExportData($tableName,$seller_id){
		if($seller_id==''){	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "".$this->db->escape($tableName)."");
		} else {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "".$this->db->escape($tableName)." WHERE seller_id='". (int)$seller_id."'");
		}
		if ($query->num_rows > 0) {
			return $query->rows;
		} else {
			return null;	
		}		
		}
		public function getAllSellerId() {
		$query = $this->db->query("SELECT `seller_id`,`store_name` FROM " . DB_PREFIX . "purpletree_vendor_stores ORDER BY seller_id ASC");
		if ($query->num_rows > 0) {
			return $query->rows;
		} else {
			return null;	
		}
	}
		public function getCountry($country_id) {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$country_id . "'");
		if ($query->num_rows > 0) {
			return $query->row['name'];
		} else {
			return null;	
		}
		
	}		
	
	public function getCountryId($country_name) {
			$query = $this->db->query("SELECT country_id FROM " . DB_PREFIX . "country WHERE name = '" . trim($country_name) . "'");
		if ($query->num_rows > 0) {
			return $query->row['country_id'];
		} else {
			return null;	
		}
	}
	
			public function getStoreName($seller_id) {
		$query = $this->db->query("SELECT store_name FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id = " . (int)$seller_id );

		if ($query->num_rows > 0) {
			return $query->row['store_name'];
		} else {
			return null;	
		}
		
	}	
	public function getSellerId($store_name) {
	
		$query = $this->db->query("SELECT seller_id FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE store_name = '".trim($this->db->escape($store_name))."'");
			if ($query->num_rows > 0) {
			return $query->row['seller_id'];
		} else {
			return null;	
		}
	}
	
		public function addShipping($shippingData) {

		foreach($shippingData['shipping'] as $key=> $data){	
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_shipping SET seller_id = '" . (int)$data['seller_id'] . "', shipping_country = '" . $this->db->escape($data['shipping_country_Id']) . "', zipcode_from = '" .  $this->db->escape($data['zipcode_from']) . "', zipcode_to = '" . $this->db->escape($data['zipcode_to']) . "', shipping_price = '" . (float)$data['shipping_price'] . "', weight_from = '" . (float)$data['weight_from'] . "', weight_to = '" . (float)$data['weight_to'] . "', max_days = '0'");
		}
		
	}
	
		public function updateShipping($shippingData) {
		foreach($shippingData['shipping'] as $key=> $data){	
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_shipping SET seller_id = '" . (int)$data['seller_id'] . "', shipping_country = '" . $this->db->escape($data['shipping_country_Id']) . "', zipcode_from = '" .  $this->db->escape($data['zipcode_from']) . "', zipcode_to = '" . $this->db->escape($data['zipcode_to']) . "', shipping_price = '" . (float)$data['shipping_price'] . "', weight_from = '" . (float)$data['weight_from'] . "', weight_to = '" . (float)$data['weight_to'] . "' WHERE id = '" . (int)$data['id'] . "'");
		}
	}
	 public function getSellerIds() {
			$query=$this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_shipping");
			if ($query->num_rows > 0) {
			return $query->rows;
		} else {
			return null;	
		}
		}
		
		
		
		
}
?>
