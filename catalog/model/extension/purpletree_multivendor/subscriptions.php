<?php
class ModelExtensionPurpletreeMultivendorSubscriptions extends Model {	

	public function getSubscription($data = array()) {
		$sql = "SELECT pvsp.*,pvpi.status_id,pvpd.plan_name,pvpd.plan_id,pvpisl.status AS status_name FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp JOIN " .DB_PREFIX. "purpletree_vendor_plan_invoice pvpi ON(pvpi.invoice_id=pvsp.invoice_id) JOIN " .DB_PREFIX. "purpletree_vendor_plan_description pvpd ON(pvpd.plan_id=pvsp.plan_id) JOIN ".DB_PREFIX. "purpletree_vendor_plan_invoice_status_languge pvpisl ON(pvpisl. 	status_id=pvpi.status_id) ";	
		
		$implode = array();		
		$implode[] = "pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$implode[] = "pvpisl.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if(isset($data['seller_id']) && $data['seller_id'] != ''){
			$implode[] = "pvsp.seller_id = '" . (int)$data['seller_id'] . "'";
		}
		
		 if (isset($data['filter_plan_id']) && ($data['filter_plan_id'] != '')) {
			$implode[] = "pvsp.plan_id = '" . (int)$data['filter_plan_id'] . "'";
		}
		 
		 if (isset($data['filter_status_id']) && ($data['filter_status_id'] != '')) {
			$implode[] = "pvpi.status_id = '" . (int)$data['filter_status_id'] . "'";
		}
		if (!empty($data['filter_start_date_from'])) {
			$implode[] = " DATE(pvsp.start_date) >= DATE('" . $this->db->escape($data['filter_start_date_from']) . "')";
		}

		if (!empty($data['filter_start_date_to'])) {
			$implode[] = " DATE(pvsp.start_date) <= DATE('" . $this->db->escape($data['filter_start_date_to']) . "')";
		}

		if (!empty($data['filter_end_date_from'])) {
			$implode[] = " DATE(pvsp.end_date) >= DATE('" . $this->db->escape($data['filter_end_date_from']) . "')";
		}

		if (!empty($data['filter_end_date_to'])) {
			$implode[] = " DATE(pvsp.end_date) <= DATE('" . $this->db->escape($data['filter_end_date_to']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}	
		
        $sql .= " ORDER BY pvsp.id";
		
        $sql .= " DESC";
		
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

	public function getTotalSubscription($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp JOIN " .DB_PREFIX. "purpletree_vendor_plan_invoice pvpi ON(pvpi.invoice_id=pvsp.invoice_id) JOIN " .DB_PREFIX. "purpletree_vendor_plan_description pvpd ON(pvpd.plan_id=pvsp.plan_id) JOIN ".DB_PREFIX. "purpletree_vendor_plan_invoice_status_languge pvpisl ON(pvpisl. 	status_id=pvpi.status_id) ";

		 $implode = array();
		 
		 $implode[] = "pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		 $implode[] = "pvpisl.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		 
		 if(isset($data['seller_id']) && $data['seller_id'] != ''){
			$implode[] = "pvsp.seller_id = '" . (int)$data['seller_id'] . "'";
		}
		
		 if (isset($data['filter_plan_id']) && ($data['filter_plan_id'] != '')) {
			$implode[] = "pvsp.plan_id = '" . (int)$data['filter_plan_id'] . "'";
		}
		
		if (isset($data['filter_status_id']) && ($data['filter_status_id'] != '')) {
			$implode[] = "pvpi.status_id = '" . (int)$data['filter_status_id'] . "'";
		}

		if (!empty($data['filter_start_date_from'])) {
			$implode[] = " DATE(pvsp.start_date) >= DATE('" . $this->db->escape($data['filter_start_date_from']) . "')";
		}

		if (!empty($data['filter_start_date_to'])) {
			$implode[] = " DATE(pvsp.start_date) <= DATE('" . $this->db->escape($data['filter_start_date_to']) . "')";
		}
		
		if (!empty($data['filter_end_date_from'])) {
			$implode[] = " DATE(pvsp.end_date) >= DATE('" . $this->db->escape($data['filter_end_date_from']) . "')";
		}

		if (!empty($data['filter_end_date_to'])) {
			$implode[] = " DATE(pvsp.end_date) <= DATE('" . $this->db->escape($data['filter_end_date_to']) . "')";
		}	

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		} 
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getSubscriptionPlanName($plan_name) {
		$sql = "SELECT pvpd.plan_id,pvpd.plan_name FROM " . DB_PREFIX . "purpletree_vendor_plan_description pvpd WHERE pvpd.plan_name  LIKE '%" . $this->db->escape($plan_name) . "%' AND pvpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$query = $this->db->query($sql);
		return $query->rows;
	
	}
	public function getSubscriptionPlanStatus($status) {
		$sql = "SELECT pvpisl.status_id,pvpisl.status FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_status_languge pvpisl WHERE pvpisl.status  LIKE '%" . $this->db->escape($status) . "%'AND pvpisl.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
		return $query->rows;	
	}
        
		public function validity($plan_id) {
		$sql = "SELECT validity FROM " . DB_PREFIX . "purpletree_vendor_plan WHERE plan_id='".(int) $plan_id."'";
		$query = $this->db->query($sql);
		return $query->row['validity'];	
	}
}
?>