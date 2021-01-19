<?php
class ModelExtensionPurpletreeMultivendorSellerprice extends Model{
	
	public function checkid($product_id,$seller_id) {
		$sellerprice = $this->db->query("SELECT pvt.product_id FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.id = pvtp.template_id) LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtp.seller_id) WHERE pvt.product_id = '" . (int)$product_id . "' AND pvtp.seller_id='".$seller_id."'");	
		if ($sellerprice->num_rows > 0){
			return $sellerprice->num_rows;
		}
	}
	
	public function getvendorcart($cart_id) {
		$query = $this->db->query("SELECT seller_id FROM " . DB_PREFIX . "purpletree_vendor_cart WHERE cart_id='".$cart_id."'");
		if($query->num_rows){
			return $query->row['seller_id'];
		}
	}
	public function getSellerPrice($product_id,$seller_id) {
		$sellerprice = $this->db->query("SELECT pvtp.price AS seller_price FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.id = pvtp.template_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtp.seller_id) WHERE pvt.product_id = '" . (int)$product_id . "' AND pvtp.seller_id='".$seller_id."'");		
		if($sellerprice->num_rows){
			return $sellerprice->row['seller_price'];
		}
	}
}