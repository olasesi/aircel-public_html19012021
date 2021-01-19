<?php
class ModelExtensionModulePurpletreeSellerprice extends Model {
	public function getTemplatePrice($product_id) {
		
		$template_price = array();
		$query = $this->db->query("SELECT pvtp.*,pvs.store_name,pvs.store_logo,p.minimum FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtp ON (pvtp.template_id = pvt.id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id  = pvtp.seller_id)  LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id  = pvt.product_id ) WHERE pvt.product_id ='". (int)$product_id ."' AND pvtp.quantity != 0 AND pvs.store_status = 1 AND pvtp.status = 1 AND pvtp.quantity > 0 ORDER BY price");
		if($query->num_rows) {
			$template_price = $query->rows;
		}
		return $template_price;
		
	}
	public function getStoreRating($seller_id){
		$query = $this->db->query("SELECT AVG(rating) as rating FROM " . DB_PREFIX . "purpletree_vendor_reviews where seller_id='".(int)$seller_id."' AND status=1");
		return $query->row['rating'];
	}	
}