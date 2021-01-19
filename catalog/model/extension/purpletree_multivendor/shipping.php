<?php
class ModelExtensionPurpletreeMultivendorShipping extends Model {
	
	public function addShipping($data,$seller_id) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_shipping SET seller_id = '" . (int)$seller_id . "', shipping_country = '" . $data['shipping_country'] . "', zipcode_from = '" .  $this->db->escape($data['zip_from']) . "', zipcode_to = '" . $this->db->escape($data['zip_to']) . "', shipping_price = '" . (float)$data['price'] . "', weight_from = '" . (float)$data['weight_from'] . "', weight_to = '" . (float)$data['weight_to'] . "', max_days = '0'");
	}
 
	 public function deleteShipping($shipping_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_shipping WHERE id = '" . (int)$shipping_id . "'");
			
		}
	
		

	public function getSellers() {
		
		$sql = "SELECT pvs.seller_id,CONCAT(c.firstname, ' ', c.lastname) AS name FROM " . DB_PREFIX . "purpletree_vendor_stores pvs JOIN " .DB_PREFIX. "customer c ON(pvs.seller_id=c.customer_id)";
        $query = $this->db->query($sql);
		return $query->rows;
	}

	public function getShipping($data = array()) {
		$sql = "SELECT pvs.*,CONCAT(c.firstname, ' ', c.lastname) AS name,cu.name AS shipping_country FROM " . DB_PREFIX . "purpletree_vendor_shipping pvs JOIN " .DB_PREFIX. "customer c ON(c.customer_id=pvs.seller_id) JOIN " .DB_PREFIX."country cu ON(pvs.shipping_country=cu.country_id)";
		
		 $implode = array();
		if(isset($data['seller_id']) && $data['seller_id'] != ''){
			$implode[] = "pvs.seller_id = '" . (int)$data['seller_id'] . "'";
		}
		if (isset($data['filter_shipping_country']) && ($data['filter_shipping_country'] != '')) {
			$implode[] = "cu.country_id LIKE '" . (int)$data['filter_shipping_country'] . "'";
		}

		
		if (isset($data['filter_zip_from']) && ($data['filter_zip_from'] != '')) {
			$implode[] = "zipcode_from >='" . (int)$data['filter_zip_from'] . "'";
		}

		if (isset($data['filter_zip_to']) && ($data['filter_zip_to'] != '')) {
			$implode[] = "zipcode_to <= '" . (int)$data['filter_zip_to'] . "'";
		}
		if (isset($data['filter_price']) && ($data['filter_price'] != '')) {
			$implode[] = "shipping_price = '" . (int)$data['filter_price'] . "'";
		}

		if (isset($data['filter_weight_from']) && $data['filter_weight_from'] !== '') {
			$implode[] = "weight_from >= '" . (int)$data['filter_weight_from'] . "'";
		}
		if (isset($data['filter_weight_to']) && $data['filter_weight_to'] !== '') {
			$implode[] = "weight_to <= '" . (int)$data['filter_weight_to'] . "'";
		}


		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		} 

		$sort_data = array(
			'name',
			'cu.name',
			'pvs.zipcode_from',
			'pvs.zipcode_to',
			'pvs.shipping_price',
			'pvs.weight_from',
			'pvs.weight_to',
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

	public function getTotalShipping($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "purpletree_vendor_shipping pvs JOIN " .DB_PREFIX. "customer c ON(c.customer_id=pvs.seller_id) JOIN " .DB_PREFIX."country cu ON(pvs.shipping_country=cu.country_id)";

		 $implode = array();
		
		if (isset($data['filter_shipping_country']) && ($data['filter_shipping_country'] != '')) {
			$implode[] = "cu.country_id LIKE '" . (int)$data['filter_shipping_country'] . "'";
		}

		
		if (isset($data['filter_zip_from']) && ($data['filter_zip_from'] != '')) {
			$implode[] = "zipcode_from >= '" . (int)$data['filter_zip_from'] . "'";
		}

		if (isset($data['filter_zip_to']) && ($data['filter_zip_to'] != '')) {
			$implode[] = "zipcode_to <= '" . (int)$data['filter_zip_to'] . "'";
		}
		if (isset($data['filter_price']) && ($data['filter_price'] != '')) {
			$implode[] = "shipping_price = '" . (int)$data['filter_price'] . "'";
		}

		if (isset($data['filter_weight_from']) && $data['filter_weight_from'] !== '') {
			$implode[] = "weight_from >= '" . (int)$data['filter_weight_from'] . "'";
		}
		if (isset($data['filter_weight_to']) && $data['filter_weight_to'] !== '') {
			$implode[] = "weight_to <= '" . (int)$data['filter_weight_to'] . "'";
		}


		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		} 
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
        
}
?>