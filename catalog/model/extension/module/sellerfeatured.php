<?php
class ModelExtensionModuleSellerfeatured extends Model {
	public function getFeatured() {
		$products = array();
		$query = $this->db->query("SELECT pvp.product_id FROM " . DB_PREFIX . "purpletree_vendor_products pvp LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = pvp.product_id) WHERE pvp.is_featured ='1' AND p.status=1 AND pvp.is_approved = 1 AND p.date_available <= NOW() AND p.quantity >= 1 ORDER BY RAND() DESC LIMIT 0,15" );
		if($query->num_rows) {
			$products = $query->rows;
		}
		return $products;
	}
}