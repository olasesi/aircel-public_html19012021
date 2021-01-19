<?php
class ModelCustomWishlist extends Model {
	public function addWishlist($customer_id, $product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$customer_id . "' AND product_id = '" . (int)$product_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_wishlist SET customer_id = '" . (int)$customer_id . "', product_id = '" . (int)$product_id . "', date_added = NOW()");
	
	}

	public function deleteWishlist($customer_id, $product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$customer_id . "' AND product_id = '" . (int)$product_id . "'");
	}

	public function getWishlist($customer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->rows;
	}

	public function getTotalWishlist($customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}
}
