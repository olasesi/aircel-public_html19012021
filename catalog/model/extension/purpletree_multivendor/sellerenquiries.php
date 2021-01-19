<?php
class ModelExtensionPurpletreeMultivendorSellerenquiries extends Model {	

	public function sendSellerMessage($data=array()){
		$sql = "INSERT INTO " . DB_PREFIX . "purpletree_vendor_enquiries SET seller_id = '".(int)$data['seller_id']."', message='". $this->db->escape($data['message']) ."', contact_from=0,created_at=NOW(),updated_at=NOW()";
		$query = $this->db->query($sql);
	}
	
	public function getSellerMessage($data=array()){
		$sql = "SELECT * FROM " . DB_PREFIX . "purpletree_vendor_enquiries WHERE seller_id='".(int)$data['seller_id']."'";
		$sql.="ORDER BY created_at DESC";
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
		if($query->num_rows>0){
			return $query->rows;
		} else {
			return NULL;
		}
		
	}

	public function getTotalSellerMessage($data=array()){
	
		$sql = "SELECT count(*) AS total FROM " . DB_PREFIX . "purpletree_vendor_enquiries WHERE seller_id = '".(int)$data['seller_id']."'";
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
}
?>