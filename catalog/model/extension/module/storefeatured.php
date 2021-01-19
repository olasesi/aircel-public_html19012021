<?php
class ModelExtensionModuleStorefeatured extends Model {
	public function getLatest() {
		$stores = array();
		$query = $this->db->query("SELECT pvs.* FROM " . DB_PREFIX . "purpletree_vendor_stores pvs LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id =pvs.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp ON (pvsp.seller_id =pvs.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id =pvsp.plan_id) WHERE pvps.status_id = 1 AND pvsp.status=1 AND pvp.featured_store =1 AND pvs.multi_store_id='".(int)$this->config->get('config_store_id') ."'  GROUP BY pvs.seller_id ORDER BY RAND() LIMIT 0,15"  );
		if($query->num_rows) {
			$stores = $query->rows;
		}
		return $stores;
		
	}
}