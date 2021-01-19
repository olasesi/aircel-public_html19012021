<?php 
class ModelExtensionPurpletreeMultivendorVendor extends Model{
	public function isSeller($customer_id){
			if ($this->config->get('module_purpletree_multivendor_status')) {
		$query = $this->db->query("SELECT id, multi_store_id,store_status, is_removed, customer_id, role, status FROM " . DB_PREFIX . "purpletree_vendor_stores where seller_id='".$customer_id."'");
		if($query->num_rows) {
			return $query->row;
		}
	}
	}
	
	public function addSeller($customer_id,$store_name,$filename = '',$role='STAFF', $cid = '' ,$status=1){
		$this->db->query("INSERT into " . DB_PREFIX . "purpletree_vendor_stores SET seller_id ='".(int)$customer_id."', store_name='".$this->db->escape(trim($store_name))."', multi_store_id='".(int)($this->config->get('config_store_id'))."',store_status='".(int)(!$this->config->get('module_purpletree_multivendor_seller_approval'))."', store_created_at= NOW(), store_updated_at= NOW(), role='" . $role . "', status='" . $status . "',customer_id ='" . $cid . "'");
		$store_id = $this->db->getLastId();
	}
	public function getStoreId($sellerid){
		$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id='". (int)$sellerid."'");
			if ($query->num_rows > 0) {
			return $query->row['id'];
		}	
		return '';
	}
	public function becomeSeller($customer_id,$store_name,$filename = ''){
		if($store_name['become_seller']){
			$this->db->query("INSERT into " . DB_PREFIX . "purpletree_vendor_stores SET seller_id ='".(int)$customer_id."', store_name='".$this->db->escape(trim($store_name['seller_storename']))."', store_status='".(int)(!$this->config->get('module_purpletree_multivendor_seller_approval'))."', store_created_at= NOW(), store_updated_at= NOW()");
			$store_id = $this->db->getLastId();
		}
		else {
			$store_id = 0;
		}
		return $store_id;
		
	}
	
	public function reseller($customer_id,$store_name){
		if($store_name['become_seller']){	
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_stores SET store_status='".(int)(!$this->config->get('module_purpletree_multivendor_seller_approval'))."', is_removed=0 WHERE seller_id='".(int)$customer_id."'");
			$store_id = 1;
		}
		else {
			$store_id = 0;
		}
		return $store_id;
		
	}
	
	public function getSellerStorename($store_name){
		$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_stores where store_name='".$this->db->escape($store_name)."'");
		return $query->num_rows;
	}
	
	public function getStoreRating($seller_id){
		$query = $this->db->query("SELECT AVG(rating) as rating,count(*) as count FROM " . DB_PREFIX . "purpletree_vendor_reviews where seller_id='".(int)$seller_id."' AND status=1");
		return $query->row;
	}
    public function getStore($store_id){
		$query = $this->db->query("SELECT pvs.*,CONCAT(c.firstname, ' ', c.lastname) AS seller_name, (SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE query = 'seller_store_id=" . (int)$store_id . "') AS store_seo FROM " . DB_PREFIX . "purpletree_vendor_stores pvs JOIN " . DB_PREFIX . "customer c ON(c.customer_id = pvs.seller_id) where pvs.id='".(int)$store_id."'");
		return $query->row;
	} 
	public function getStoreDetail($customer_id){
		$query = $this->db->query("SELECT pvs.* FROM " . DB_PREFIX . "purpletree_vendor_stores pvs where pvs.seller_id='".(int)$customer_id."'");
		return $query->row;
	}
    public function editStoreImage($store_id,$store_logo,$store_banner){	
		$this->db->query("UPDATE " . DB_PREFIX. "purpletree_vendor_stores SET  store_logo='".$this->db->escape($store_logo)."', store_banner='".$this->db->escape($store_banner)."',store_updated_at=NOW() where id='".(int)$store_id."'");
	}
	public function editStore($store_id,$data,$file = ''){
		$dcument = "";
		if($file != '') {
			$dcument = ",document='".$file."'";
		}
		$store_live_chat_enable = "";
		$store_live_chat_code = "";
		if(isset($data['store_live_chat_enable'])) {
			$store_live_chat_enable = ", store_live_chat_enable=". $data['store_live_chat_enable'];
		}
		if(isset($data['store_live_chat_code'])) {
			 $store_live_chat_code = ', store_live_chat_code="'. $data['store_live_chat_code'].'"';
		}
				if(!isset($data['store_name'])) {
			$data['store_name'] = '';
		}if(!isset($data['store_logo'])) {
			$data['store_logo'] = '';
		}if(!isset($data['store_email'])) {
			$data['store_email'] = '';
		}if(!isset($data['store_phone'])) {
			$data['store_phone'] = '';
		}if(!isset($data['store_banner'])) {
			$data['store_banner'] = '';
		}if(!isset($data['store_address'])) {
			$data['store_address'] = '';
		}if(!isset($data['store_city'])) {
			$data['store_city'] = '';
		}if(!isset($data['store_country'])) {
			$data['store_country'] = '';
		}if(!isset($data['store_state'])) {
			$data['store_state'] = '';
		}if(!isset($data['store_meta_keywords'])) {
			$data['store_meta_keywords'] = '';
		}if(!isset($data['store_meta_description'])) {
			$data['store_meta_description'] = '';
		}if(!isset($data['store_bank_details'])) {
			$data['store_bank_details'] = '';
		}if(!isset($data['store_shipping_type'])) {
			$data['store_shipping_type'] = '';
		}if(!isset($data['store_shipping_order_type'])) {
			$data['store_shipping_order_type'] = '';
		}if(!isset($data['store_shipping_charge'])) {
			$data['store_shipping_charge'] = '';
		}if(!isset($data['store_description'])) {
			$data['store_description'] = '';
		}
		if(!isset($data['facebook_link'])) {
			$data['facebook_link'] = '';
		}
		if(!isset($data['google_link'])) {
			$data['google_link'] = '';
		}
		if(!isset($data['instagram_link'])) {
			$data['instagram_link'] = '';
		}
		if(!isset($data['twitter_link'])) {
			$data['twitter_link'] = '';
		}
		if(!isset($data['pinterest_link'])) {
			$data['pinterest_link'] = '';
		}		
		if(!isset($data['wesbsite_link'])) {
			$data['wesbsite_link'] = '';
		} 	
		if(!isset($data['whatsapp_link'])) {
			$data['whatsapp_link'] = '';
		} 
		$this->db->query("UPDATE " . DB_PREFIX. "purpletree_vendor_stores SET store_name='".$this->db->escape(trim($data['store_name']))."', store_logo='".$this->db->escape($data['store_logo'])."', store_email='".$this->db->escape($data['store_email'])."', store_phone='".$this->db->escape($data['store_phone'])."', store_banner='".$this->db->escape($data['store_banner'])."', store_description='".$this->db->escape($data['store_description'])."'".$dcument.$store_live_chat_enable.$store_live_chat_code." , store_address='".$this->db->escape($data['store_address'])."', store_city='".$this->db->escape($data['store_city'])."',store_country='".(int)$data['store_country']."', store_state='".(int)$data['store_state']."', store_zipcode='".$this->db->escape($data['store_zipcode'])."', store_shipping_policy='".$this->db->escape($data['store_shipping_policy'])."', store_return_policy='".$this->db->escape($data['store_return_policy'])."', store_meta_keywords='".$this->db->escape($data['store_meta_keywords'])."', store_meta_descriptions='".$this->db->escape($data['store_meta_description'])."', store_bank_details='".$this->db->escape($data['store_bank_details'])."', store_tin='".$this->db->escape($data['store_tin'])."', store_shipping_type ='".$this->db->escape($data['store_shipping_type'])."',store_shipping_order_type ='".$this->db->escape($data['store_shipping_order_type'])."',store_shipping_charge ='".$this->db->escape($data['store_shipping_charge'])."',seller_paypal_id ='".$this->db->escape($data['seller_paypal_id'])."',store_updated_at=NOW() where id='".(int)$store_id."'");
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_social_links     WHERE store_id = " . (int)$store_id . "");
		if($query->num_rows > 0){
		      $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_social_links SET store_id = '" . (int)$store_id ."', facebook_link ='".$this->db->escape($data['facebook_link']). "', google_link ='".$this->db->escape($data['google_link']). "',instagram_link ='".$this->db->escape($data['instagram_link'])."', twitter_link ='".$this->db->escape($data['twitter_link'])."', pinterest_link ='".$this->db->escape($data['pinterest_link'])."', wesbsite_link ='".$this->db->escape($data['wesbsite_link']). "',  whatsapp_link ='".$this->db->escape($data['whatsapp_link']). "' where store_id ='".(int)$store_id."'");
		}else{ 
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_social_links SET store_id = '" . (int)$store_id ."', facebook_link ='".$this->db->escape($data['facebook_link']). "', google_link ='".$this->db->escape($data['google_link']). "',instagram_link ='".$this->db->escape($data['instagram_link'])."', twitter_link ='".$this->db->escape($data['twitter_link'])."', pinterest_link ='".$this->db->escape($data['pinterest_link'])."', wesbsite_link ='".$this->db->escape($data['wesbsite_link']). "',  whatsapp_link ='".$this->db->escape($data['whatsapp_link']). "'");
			
		}
		
		if ($data['store_seo']) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'seller_store_id=" . (int)$store_id . "'");
			if($query->num_rows > 0){
				$row = $query->row;
				$this->db->query("UPDATE " . DB_PREFIX . "seo_url SET query = 'seller_store_id=" . (int)$store_id . "', language_id = '0', keyword = '".$this->db->escape($data['store_seo']) . "' WHERE seo_url_id=".$row['seo_url_id']);
			} else{
				if(VERSION=='3.1.0.0_b'){
				$push='route=extension/account/purpletree_multivendor/sellerstore/storeview&seller_store_id='.(int)$store_id;	
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET query = 'seller_store_id=" . (int)$store_id . "', language_id = '1', keyword = '" . $this->db->escape($data['store_seo']) . "', push='".$push."'");
				}else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET query = 'seller_store_id=" . (int)$store_id . "', language_id = '0', keyword = '" . $this->db->escape($data['store_seo']) . "'");
				}
			}
		}else {
			$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'seller_store_id=" . (int)$store_id ."'");
		}
	}
	public function getStoreByEmail($email) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE LCASE(store_email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
		
	}
	public function getStoreSeo($seo_url) {
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '".$this->db->escape($seo_url) . "'");
		return $query->row;
	}
	public function removeSeller($seller_id){
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products pvp JOIN " . DB_PREFIX . "product p ON(p.product_id=pvp.product_id) SET p.status=0 WHERE pvp.seller_id='".(int)$seller_id."'");
		
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_stores SET store_status=0, is_removed=1 WHERE seller_id='".(int)$seller_id."'");
	}
	public function getStoreNameByStoreName($store_name2){
		$sql = "SELECT pvs.id ,pvs.seller_id ,pvs.store_name,c.status FROM " . DB_PREFIX . "purpletree_vendor_stores pvs LEFT JOIN ". DB_PREFIX ."customer c ON(pvs.seller_id = c.customer_id) WHERE pvs.store_name = '" . $this->db->escape(trim($store_name2)) . "' AND c.status=1";
		$query = $this->db->query($sql);    
   		return $query->row;	
    }
	public function getStoreSocial($store_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_social_links pvsl where pvsl.store_id='".(int)$store_id."'");
		if($query->num_rows) {
       		 return $query->row;
        } 
	}
	public function getStoreByIdd($sellerid,$email_id){
		$query = $this->db->query("SELECT count(*) AS num_row FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id !='". (int)$sellerid."' AND store_email='".$email_id."'");
			if ($query->num_rows > 0) {
			return $query->row['num_row'];
		} else {	
			return NULL;
		}
		}
	public function getStoreById($sellerid){
    	$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id='". (int)$sellerid."'");
    		if ($query->num_rows > 0) {
    		return $query->row;
    	}	
		return '';
	}
	public function getCustomerEmailId($seller_id) {
	
    	$query = $this->db->query("SELECT email  FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$seller_id . "'");
    	  if($query->num_rows>0){
    			return $query->row['email'];
    		}else {
    			return NULL;
    		}
	}
}
?>
