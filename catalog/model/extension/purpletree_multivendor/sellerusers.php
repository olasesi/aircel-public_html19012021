<?php 
class ModelExtensionPurpletreeMultivendorSellerusers extends Model{

    public function getStoreUsers($seller_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id = '" . (int)$seller_id . "'");
		return $query->rows;
	}  

    public function getStoreUserData($seller_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE customer_id = '" . (int)$seller_id . "'");
		return $query->row;
	} 

    public function getCustomerByEmail($email){
		$query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE email LIKE '" . $this->db->escape($email) . "'");
		return $query->row;
	} 	
	public function getTotalStoreUsers($seller_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_stores WHERE seller_id = '" . (int)$seller_id . "'");
		return $query->num_rows;
	}	
	
	public function getStoreUsersData($data=array()){
	    $sql = "select pvs.*, c.lastname, c.firstname, c.email, c.telephone from " . DB_PREFIX . "purpletree_vendor_stores pvs INNER JOIN " . DB_PREFIX . "customer c ON (pvs.customer_id = c.customer_id)";

		if (!empty($data['customer_id'])) {
			$sql .= " AND pvs.seller_id = '" . (int)$data['customer_id'] . "%'";
		}	    
		if (!empty($data['lastname'])) {
			$sql .= " AND c.lastname LIKE '" . $this->db->escape($data['lastname']) . "%'";
		}
		if (!empty($data['firstname'])) {
			$sql .= " AND c.firstname LIKE '" . $this->db->escape($data['firstname']) . "%'";
		}		
		if (!empty($data['status'])) {
			$sql .= " AND pvs.status = '" . (int)$data['status'] . "'";
		}
		if (!empty($data['roll'])) {
			$sql .= " AND pvs.role LIKE '" . $this->db->escape($data['role']) . "%'";
		}		
		$sql .= " GROUP BY pvs.customer_id";
		$sql .= " ORDER BY c.firstname";
		
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
				$data['limit'] = 5;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		$query = $this->db->query($sql);

		return $query->rows;		
	}
	
	public function getSeller($customer_id){
	  if ($this->config->get('module_purpletree_multivendor_status')) {
		$query = $this->db->query("SELECT id, seller_id, multi_store_id,store_status, is_removed, customer_id, role, status FROM " . DB_PREFIX . "purpletree_vendor_stores where customer_id ='" . (int)$customer_id . "'");
		if($query->num_rows) {
			return $query->row;
		}
	  }
	}	
	
	public function sellerTotalPlanStatus($seller_id){
		 $sql="SELECT pvps.status_id FROM ". DB_PREFIX ."purpletree_vendor_plan pvp  LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_subscription pvps ON ((pvps.seller_id = pvsp.seller_id) AND (pvps.status_id = pvp.status)) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
		 $query = $this->db->query($sql);
	    if($query->num_rows){	
	    	return $query->row;
    	} else {
	    	return NULL;	
	    }
	}

	public function addSeller($data=array(), $role='STAFF', $cid = '' ,$status=1){
		$this->db->query("INSERT into " . DB_PREFIX . "purpletree_vendor_stores SET seller_id ='".(int)$customer_id."', store_name='".$this->db->escape(trim($store_name))."', multi_store_id='".(int)($this->config->get('config_store_id'))."',store_status='".(int)(!$this->config->get('module_purpletree_multivendor_seller_approval'))."', store_created_at= NOW(), store_updated_at= NOW(), role='" . $role . "', status='" . $status . "',customer_id ='" . $cid . "'");
		$store_id = $this->db->getLastId();
	}	
	public function getSsellerplanStatus($seller_id) {
	    $sql="SELECT pvps.status_id FROM ". DB_PREFIX ."purpletree_vendor_plan pvp  LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_subscription pvps ON ((pvps.seller_id = pvsp.seller_id) AND (pvps.status_id = pvp.status)) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
		$query = $this->db->query($sql);
		if($query->num_rows){
		   return $query->row['status_id'];
		} else { 
		   return false;
	    }
    }
    
	public function addUser($data=array(), $role='STAFF', $cid = '' ,$status=0){
		$this->db->query("INSERT into " . DB_PREFIX . "purpletree_vendor_stores SET seller_id ='".(int)$data['seller_id']."', 
		store_name='".$data['store_name']."', store_logo ='". $data['store_logo'] ."',store_email='". $data['store_email'] ."', store_phone= '" . $data['store_phone'] . "',
		store_banner = '" . $data['store_banner'] . "', document='" . $data['document'] . "', store_description = '" . $data['store_description'] . "',
		store_address = '" . $data['store_address'] . "', store_city = '" . $data['store_city'] . "', store_country = '" . (int)$data['store_country'] . "',
		store_state = '" . (int)$data['store_state'] . "', store_zipcode='" . $data['store_zipcode'] . "', store_shipping_policy = '" . $data['store_shipping_policy'] . "',
		store_return_policy='" . $data['store_return_policy'] . "', store_meta_keywords = '" . $data['store_meta_keywords'] . "', store_meta_descriptions ='" . $data['store_meta_descriptions'] . "',
		store_bank_details = '" . $data['store_bank_details'] . "', store_tin = '" . $data['store_tin'] . "', store_shipping_type = '" . $data['store_shipping_type'] . "',
		store_shipping_order_type='" . $data['store_shipping_order_type'] . "', store_shipping_charge = '" . $data['store_shipping_charge'] . "',
		store_live_chat_enable = '" . (int)$data['store_live_chat_enable'] . "', store_live_chat_code = '" . $data['store_live_chat_code'] . "', store_status= '" . $data['store_status'] . "',
		store_commission = '" . $data['store_commission'] . "', is_removed = '" . $data['is_removed'] . "',store_created_at= NOW(), store_updated_at= NOW(),
		seller_paypal_id = '" . $data['seller_paypal_id'] . "', multi_store_id = '" . $data['multi_store_id'] . "' , role='" . $role . "',customer_id ='" . $cid . "', status='" . $status . "'");
		$store_id = $this->db->getLastId();
	}    
}
?>