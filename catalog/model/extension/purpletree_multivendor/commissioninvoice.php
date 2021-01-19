<?php 
class ModelExtensionPurpletreeMultivendorCommissioninvoice extends Model{
	
	
     public function getCommissionHistory($invoice_id) {
			$query = $this->db->query("SELECT * FROM ". DB_PREFIX ."purpletree_vendor_payment_settlement_history WHERE invoice_id='".(int)$invoice_id."' ORDER BY id DESC");
			if ($query->num_rows) {
			return $query->rows;
			}
			
			return false;
			}
	 public function getCommissionStatus($status_id) {
				$query = $this->db->query("SELECT status FROM ". DB_PREFIX ."purpletree_vendor_plan_invoice_status_languge WHERE status_id='".(int)$status_id."' AND language_id='".(int)$this->config->get('config_language_id')."'"); 
			if ($query->num_rows) {
			return $query->row['status'];
			}
			return false;
			}

	
	public function getCommissionsInvoice($id){
		
		$sql = "SELECT * FROM " . DB_PREFIX . "purpletree_vendor_commission_invoice pvc WHERE id = '".(int)$id."'";		
		$query  = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getDefaultstatus(){
		
		
		$sql = "SELECT status FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_status_languge WHERE status_id = '1'";
       $sql .= " AND language_id = '" . (int)$this->config->get('config_language_id') . "'";	
		
		$query  = $this->db->query($sql);
		if ($query->num_rows) {
			return $query->row['status'];
			}
			return false;		
	}
		public function getInvoiceStatus($id){
		
		
		$sql = "SELECT status FROM " . DB_PREFIX . "purpletree_vendor_payments WHERE invoice_id = '".(int)$id."'";
		$query  = $this->db->query($sql);
		if ($query->num_rows) {
			return $query->row['status'];
			}
			return false;		
	  }
	
	public function getStoreDetail($customer_id){
		$query = $this->db->query("SELECT pvs.* FROM " . DB_PREFIX . "purpletree_vendor_stores pvs where pvs.seller_id='".(int)$customer_id."'");
		return $query->row;
	}
	public function savelinkid(){
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_commission_invoice SET created_at ='".date('Y-m-d')."'");
		return $this->db->getLastId();
	}
	public function getiinvoiceitems($lnk_id = NULL){
		$sql = "SELECT * FROM " . DB_PREFIX . "purpletree_vendor_commission_invoice_items pvc WHERE link_id = ".$lnk_id;
		$query  = $this->db->query($sql);
		return $query->rows;
	}
	 public function getinvoicedate($lnk_id = NULL){
		$sql = "SELECT `created_at` FROM " . DB_PREFIX . "purpletree_vendor_commission_invoice pvc WHERE id = ".$lnk_id;
		$query  = $this->db->query($sql);
		return $query->row;
	}
	
	public function saveCommisionInvoice($data=array(),$link_id = NULL){
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_commission_invoice_items SET order_id ='".(int)$data['order_id']."', product_id='".(int)$data['product_id']."', seller_id='".(int)$data['seller_id']."', commission_fixed='".(int)$data['commission_fixed']."', commission_percent='".(float)$data['commission_percent']."', commission_shipping='".(float)$data['commission_shipping']."', total_commission ='".(int)$data['commission']."', link_id ='".(int)$link_id."'");
	}
		public function getCommissions($data=array()){
		$sql = "SELECT pvc.*,pvc.created_at FROM " . DB_PREFIX . "purpletree_vendor_commission_invoice pvc JOIN " . DB_PREFIX . "purpletree_vendor_commission_invoice_items pvci ON (pvci.link_id = pvc.id)  WHERE  pvci.seller_id ='".(int)$data['seller_id']."'";
		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(pvc.created_at) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(pvc.created_at) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
		}
		if(!isset($data['filter_date_from']) && !isset($data['filter_date_to'])){
			$end_date = date('Y-m-d', strtotime("-30 days"));
			$sql .= " AND DATE(pvc.created_at) >= '".$end_date."'";
			$sql .= " AND DATE(pvc.created_at) <= '".date('Y-m-d')."'";
		}
		
		$sql .= " GROUP BY pvc.id ORDER BY id DESC";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query  = $this->db->query($sql);
		return $query->rows;
	}
	public function getTotalCommissionsinvoices($data=array()){		
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "purpletree_vendor_commission_invoice pvc LEFT JOIN " . DB_PREFIX . "purpletree_vendor_commission_invoice_items pvci ON (pvci.link_id = pvc.id)  WHERE  pvci.seller_id ='".(int)$data['seller_id']."'";	

		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(pvc.created_at) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(pvc.created_at) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
		}
		if(!isset($data['filter_date_from']) && !isset($data['filter_date_to'])){
			$end_date = date('Y-m-d', strtotime("-30 days"));
			$sql .= " AND DATE(pvc.created_at) >= '".$end_date."'";
			$sql .= " AND DATE(pvc.created_at) <= '".date('Y-m-d')."'";
		}	
		$sql .= "ORDER BY pvc.id ASC";
		$query  = $this->db->query($sql);
		if($query->num_rows >0){
			return $query->row['total'];
		} else {
			return 0;
		}
	}
	public function getanyOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND order_status_id > '0'");

		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip']
			);
		} else {
			return false;
		}
	}
	public function getCustomFieldIdFromName($optionName) {
		$query = $this->db->query("SELECT cfd.custom_field_id FROM " . DB_PREFIX . "custom_field_description cfd WHERE cfd.name = '". $this->db->escape($optionName) . "' AND cfd.language_id = '" . (int)$this->config->get('config_language_id')."'");
		if ($query->num_rows) {
			return $query->row['custom_field_id'];
		}
		return false;
	}
	public function getvatfromid($customeridd) {
		$query = $this->db->query("SELECT c.custom_field FROM " . DB_PREFIX . "customer c WHERE c.customer_id = '" . (int)$customeridd."'");
		if ($query->num_rows) {
			return $query->row['custom_field'];
		}
		return false;
	}
		
}
?>