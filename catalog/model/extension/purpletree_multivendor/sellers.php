<?php
class ModelExtensionPurpletreeMultivendorSellers extends Model{
	public function getSellerstotal($data= array()){
		
		$sql = "SELECT pvs.*,(SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = pvs.seller_id) AS seller,(SELECT co.name FROM " . DB_PREFIX . "country co WHERE co.country_id = pvs.store_country) AS seller_country FROM " . DB_PREFIX . "purpletree_vendor_stores pvs WHERE pvs.multi_store_id='".(int)$this->config->get('config_store_id') ."' AND pvs.store_status='1'";
		if(!empty($data['filter'])){
			$sql .=" HAVING pvs.store_name LIKE '%" . $this->db->escape($data['filter']) . "%'";
		}
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	
	public function getSellers($data= array()){
		$sort_data = array(
			'seller'
		); 
		
		$sql = "SELECT pvs.*,(SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = pvs.seller_id) AS seller,(SELECT co.name FROM " . DB_PREFIX . "country co WHERE co.country_id = pvs.store_country) AS seller_country FROM " . DB_PREFIX . "purpletree_vendor_stores pvs WHERE pvs.multi_store_id='".(int)$this->config->get('config_store_id') ."' AND pvs.store_status='1'";
		
		if(!empty($data['filter'])){
			$sql .=" HAVING pvs.store_name LIKE '%" . $this->db->escape($data['filter']) . "%'";
		}
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= "ORDER BY LCASE(pvs.store_name)";
		} else {
			$sql .= "ORDER BY pvs.store_created_at";
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
	
	public function getSsellerplanStatus($seller_id) {
			//$query=$this->db->query("SELECT status_id FROM ". DB_PREFIX ."purpletree_vendor_plan_subscription WHERE seller_id='".(int)$seller_id."'");
			 $sql="SELECT pvps.status_id FROM ". DB_PREFIX ."purpletree_vendor_plan pvp  LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_subscription pvps ON ((pvps.seller_id = pvsp.seller_id) AND (pvps.status_id = pvp.status)) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
			$query = $this->db->query($sql);
			if($query->num_rows){
			return $query->row['status_id'];
			} else { 
			return false;
			}
		}
		
	public function getInvoiceStatus($seller_id){
		$query=$this->db->query("SELECT pvpi.status_id AS invoice_status FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice pvpi LEFT JOIN " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp ON (pvpi.invoice_id = pvsp.invoice_id) WHERE pvsp.seller_id='".(int) $seller_id."' AND pvsp.status=1");
	if($query->num_rows){	
		return $query->row['invoice_status'];
	} else {
		return NULL;	
	}
	}
		
	public function getTotalSellers($data= array()){
		
		$sql = "SELECT pvs.store_name, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = pvs.seller_id) AS seller FROM " . DB_PREFIX . "purpletree_vendor_stores pvs WHERE pvs.store_status='1'";
		if(!empty($data['filter'])){
			$sql .=" HAVING pvs.store_name LIKE '%" . $this->db->escape($data['filter']) . "%'";
		}
		$query = $this->db->query($sql);
		
		$query->row['total'] = $query->num_rows;
		
		return $query->row['total'];
	}
	
	public function getTotalProducts($data= array()){
		
		$sql = "SELECT COUNT(pvp.id) AS total FROM " . DB_PREFIX . "purpletree_vendor_products pvp JOIN " . DB_PREFIX . "product p ON(p.product_id=pvp.product_id) WHERE pvp.is_approved='1' AND p.status ='1'";
		
		if(!empty($data['seller_id'])){
			$sql .= " AND pvp.seller_id ='".(int)$data['seller_id']."'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function getProducts($data= array()){
		
		$sql = "SELECT p.image, p.product_id FROM " . DB_PREFIX . "purpletree_vendor_products pvp JOIN " . DB_PREFIX . "product p ON(p.product_id=pvp.product_id) WHERE pvp.is_approved='1' AND p.status ='1'";
		
		if(!empty($data['seller_id'])){
			$sql .= " AND pvp.seller_id ='".(int)$data['seller_id']."'";
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
}