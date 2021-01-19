<?php
class ModelExtensionModuleLatestsellerProducts extends Model {
	public function getLatest() {
		$products = array();
		//$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_products ORDER BY `id` DESC LIMIT 0,8" );
		$query = $this->db->query("SELECT pvp.product_id FROM " . DB_PREFIX . "purpletree_vendor_products pvp LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = pvp.product_id) WHERE p.status=1 AND pvp.is_approved = 1 AND p.date_available <= NOW() AND p.quantity >= 1 ORDER BY `id` DESC LIMIT 0,8" );
		if($query->num_rows) {
			$products = $query->rows;
		}
		return $products;
		
	}
}