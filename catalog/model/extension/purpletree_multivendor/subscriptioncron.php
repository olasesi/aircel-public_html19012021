<?php 
class ModelExtensionPurpletreeMultivendorSubscriptioncron extends Model{	
			public function addSellerPlan($data=array()) {

				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_plan_invoice SET plan_id='".(int)$data['plan_id']."',seller_id='".(int)$data['seller_id']."',status_id='".(int)$data['vendor_invoice_status']."',created_date=NOW()");	
				$invoice_id = $this->db->getLastId();
				
				foreach($data['totals']['plan'] as $key=>$result){
				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_plan_invoice_item SET invoice_id='". (int)$invoice_id."',code='".$this->db->escape($result['code'])."',title='".$this->db->escape($result['title'])."',price='".$this->db->escape($result['value'])."',sort_order='".$this->db->escape($result['sort_order'])."'");	
				}
			
				//
				$lastsellerplanid = $this->db->query("SELECT id FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE seller_id ='".(int)$data['seller_id']."' order by id DESC limit 0,1");

				if($lastsellerplanid->num_rows) {
					if($data['startt_when'] == 1) {
						$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET end_date='".$this->db->escape($data['start_date'])."' WHERE id ='".$lastsellerplanid->row['id']."'");
					}
				}
				//
				
				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_seller_plan SET invoice_id='".(int)$invoice_id."',plan_id='".(int)$data['plan_id']."',seller_id='".(int)$data['seller_id']."',start_date='".$this->db->escape($data['start_date'])."',end_date='".$this->db->escape($data['end_date'])."',created_date='".$this->db->escape($data['current_date'])."'");
				$id = $this->db->getLastId();
				
					if($data['startt_when'] == 1) {
						$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=0 WHERE id ='".$id."' AND seller_id='".(int)$data['seller_id']."'");
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET  end_date='".$this->db->escape($data['start_date'])."' WHERE id !='".$id."' AND end_date ='0000-00-00 00:00:00' AND seller_id='".(int)$data['seller_id']."'");
					}
					else {
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=1 WHERE id ='".$id."' AND seller_id='".(int)$data['seller_id']."'");
					
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=0,end_date='".$this->db->escape($data['current_date'])."' WHERE id !='".$id."' AND status=1 AND seller_id='".(int)$data['seller_id']."'");
					
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET  end_date='".$this->db->escape($data['current_date'])."' WHERE id !='".$id."' AND end_date ='0000-00-00 00:00:00' AND seller_id='".(int)$data['seller_id']."'");
					}
					
				return $invoice_id;
			}
			
		public function addSellerMultiplePlan($data=array()) {

				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_plan_invoice SET plan_id='".(int)$data['plan_id']."',seller_id='".(int)$data['seller_id']."',status_id='".(int)$data['vendor_invoice_status']."',created_date=NOW()");	
				$invoice_id = $this->db->getLastId();

				foreach($data['totals']['plan'] as $key=>$result){
				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_plan_invoice_item SET invoice_id='". (int)$invoice_id."',code='".$this->db->escape($result['code'])."',title='".$this->db->escape($result['title'])."',price='".$this->db->escape($result['value'])."',sort_order='".$this->db->escape($result['sort_order'])."'");	
				}
			
				//
				$lastsellerplanid = $this->db->query("SELECT id FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE seller_id ='".(int)$data['seller_id']."' order by id DESC limit 0,1");

				if($lastsellerplanid->num_rows) {
					if($data['startt_when'] == 1) {
						$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET end_date='".$this->db->escape($data['start_date'])."' WHERE id ='".$lastsellerplanid->row['id']."'");
					}
				}
				//
				
				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_seller_plan SET invoice_id='".(int)$invoice_id."',plan_id='".(int)$data['plan_id']."',seller_id='".(int)$data['seller_id']."',start_date='".$this->db->escape($data['start_date'])."',end_date='".$this->db->escape($data['end_date'])."',created_date='".$this->db->escape($data['current_date'])."'");
				$id = $this->db->getLastId();
			
					if($data['startt_when'] == 1) {
						$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=0,new_status=0 WHERE id ='".$id."' AND seller_id='".(int)$data['seller_id']."'");

					}
					else {
						
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=1,new_status=1 WHERE id ='".$id."' AND seller_id='".(int)$data['seller_id']."'");
					
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=0,end_date='".$this->db->escape($data['current_date'])."' WHERE id !='".$id."' AND status=1 AND seller_id='".(int)$data['seller_id']."'");
					
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET new_status=0,end_date='".$this->db->escape($data['current_date'])."' WHERE id !='".$id."' AND plan_id='".(int)$data['plan_id']."'AND seller_id='".(int)$data['seller_id']."'");
					
					}
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET  end_date='".$this->db->escape($data['current_date'])."' WHERE id !='".$id."' AND end_date ='0000-00-00 00:00:00' AND seller_id='".(int)$data['seller_id']."'");
					
				return $invoice_id;
			}
	
		public function cronReminder() {
				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name, pvpd.plan_name,pvps.seller_id,pvp.no_of_product,pvsp.start_date,pvsp.end_date,pvsp.reminder1,pvsp.reminder2,pvsp.reminder,pvsp.status,pvp.validity,pvsp.plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."' AND pvsp.status = 1"; 
				$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->rows;
					}
			}
			
		public function cronReminderForMultiplePlan() {
				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name, pvpd.plan_name,pvsp.id,pvps.seller_id,pvp.no_of_product,pvsp.start_date,pvsp.new_end_date,pvsp.reminder,pvsp.reminder1,pvsp.reminder2,pvsp.new_status,pvp.validity,pvsp.plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."' AND pvsp.new_status = 1"; 
				$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->rows;
					}
			}

		public function multiplePlanActive() {
				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name, pvpd.plan_name,pvsp.id,pvp.no_of_product,pvsp.start_date,pvsp.new_end_date,pvsp.reminder,pvsp.reminder1,pvsp.reminder2,pvps.seller_id,pvsp.new_status,pvp.validity,pvsp.plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."'"; 
				$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->rows;
					}
			}
			
		public function planActive() {
				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name, pvpd.plan_name,pvsp.id,pvp.no_of_product,pvsp.start_date,pvsp.end_date,pvsp.reminder,pvsp.reminder1,pvsp.reminder2,pvps.seller_id,pvsp.status,pvp.validity,pvsp.plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."'"; 
				$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->rows;
					}
			}
			
		public function getCustomer($customer_id) {
				$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
					if ($query->num_rows) {
						return $query->row;
						} else {
						return NULL;
						}				
			}

			public function updateReminder1($seller_id,$reminder) {
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET reminder= '".(int)$reminder."' WHERE seller_id = '" . (int)$seller_id."' AND status=1");
			}
			public function updateReminder2($seller_id,$reminder) {
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET reminder1= '".(int)$reminder."' WHERE seller_id = '" . (int)$seller_id."' AND status=1");
			}
			public function updateReminder3($seller_id,$reminder) {
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET reminder2= '".(int)$reminder."' WHERE seller_id = '" . (int)$seller_id."' AND status=1");
			}
			
				public function updateMultipleReminder1($id,$reminder) {
					$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET reminder= '".(int)$reminder."' WHERE id = '" .(int)$id."' AND new_status=1");
					}
				public function updateMultipleReminder2($id,$reminder) {
					$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET reminder1= '".(int)$reminder."' WHERE id = '" .(int)$id."' AND new_status=1");
					}	
				public function updateMultipleReminder3($id,$reminder) {
					$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET reminder2= '".(int)$reminder."' WHERE id = '" .(int)$id."' AND new_status=1");
					}						
				public function planExpired($seller_id,$id) {
				$grace=0;
				$validity=0;
				$validity = $this->db->query("SELECT pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON(pvsp.plan_id=pvp.plan_id) WHERE pvsp.id='".$id."'")->row['validity'];
				$grace=(int)$this->config->get('purpletree_multivendor_grace_period');
				$validity_g=$validity+$grace;

				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET status=0,end_date=DATE_ADD(start_date, INTERVAL '".$validity_g."' DAY) WHERE seller_id = '".(int)$seller_id."' AND id='".$id."'");
			}		
			
			public function planExpiredForMultiplePlan($seller_id,$id) {

				$validity = $this->db->query("SELECT pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON(pvsp.plan_id=pvp.plan_id) WHERE pvsp.id='".$id."'")->row['validity'];

				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET new_status=0,new_end_date=DATE_ADD(start_date, INTERVAL '".$validity."' DAY) WHERE seller_id = '".(int)$seller_id."' AND id='".$id."'");

			}
			
		public function getSellerPlanData($seller_id) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id = '" . (int)$seller_id."'");
			}
			
		public function planEnableForMultiplePlan($seller_id,$id) {
				$validity = $this->db->query("SELECT pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON(pvsp.plan_id=pvp.plan_id) WHERE pvsp.id='".$id."'")->row['validity'];
			
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET new_status=1 WHERE seller_id = '" . (int)$seller_id."' AND id ='".$id."' AND (start_date <= NOW() AND NOW() <= (DATE_ADD(start_date, INTERVAL '".$validity."' DAY)))");

			}	
			
		public function planEnable($seller_id,$id) {
				$validity = $this->db->query("SELECT pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON(pvsp.plan_id=pvp.plan_id) WHERE pvsp.id='".$id."'")->row['validity'];
			
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET status=1 WHERE seller_id = '" . (int)$seller_id."' AND id ='".$id."' AND (start_date <= NOW() AND NOW() < (DATE_ADD(start_date, INTERVAL '".$validity."' DAY)))");

			}
			
		public function plansetexpirydateForMultiplePlan($seller_id,$id) {
				$validity=$this->db->query("SELECT validity FROM " . DB_PREFIX . "purpletree_vendor_plan WHERE plan_id IN (SELECT plan_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id = '" . (int)$seller_id."' AND id ='".$id."' and new_status = 1) ")->row['validity'];

				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET new_end_date=  DATE_ADD(now(), INTERVAL '".$validity."' DAY) WHERE seller_id = '" . (int)$seller_id."' AND id ='".$id."' and new_status = 1");
			}
			
		public function plansetexpirydate($seller_id,$id) {
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET end_date=NOW() WHERE seller_id = '" . (int)$seller_id."' AND id !='".$id."' and status = 1");
			}
			public function planDisableForMultiplePlan($seller_id,$id) {
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET new_status=0 WHERE seller_id = '" . (int)$seller_id."' AND id ='".$id."'");
			}
			
		public function planDisable($seller_id,$id) {
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan SET status=0 WHERE seller_id = '" . (int)$seller_id."' AND id !='".$id."'");
			}
			
		public function productDisable($seller_id,$id){

		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvp.seller_id=pvsp.seller_id) RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvsproducts ON(pvp.product_id=pvsproducts.product_id) LEFT JOIN " . DB_PREFIX . "product p ON(pvsproducts.product_id=p.product_id) SET p.status=0,pvsproducts.product_plan_id=0 WHERE pvsp.seller_id='".$seller_id."' AND pvsp.id='".$id."' AND pvsproducts.product_plan_id=pvsp.plan_id");
		
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvp.seller_id=pvsp.seller_id) RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvsproducts ON(pvp.product_id=pvsproducts.product_id) SET pvsproducts.featured_product_plan_id=0 WHERE pvsp.seller_id='".$seller_id."' AND pvsp.id='".$id."' AND pvsproducts.featured_product_plan_id=pvsp.plan_id");
		
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_products pvp ON(pvp.seller_id=pvsp.seller_id) RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvsproducts ON(pvp.product_id=pvsproducts.product_id) SET pvsproducts.category_featured_product_plan_id=0 WHERE pvsp.seller_id='".$seller_id."' AND pvsp.id='".$id."' AND pvsproducts.category_featured_product_plan_id=pvsp.plan_id");
		
		//$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET status=0 WHERE product_id IN (SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int)$seller_id."')");
		
		$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_plan_subscription SET status_id=0,modified_date =NOW() WHERE seller_id = '" . (int)$seller_id."'");
			}
			
		public function getPlan($seller_id) {
				$sql="SELECT pvp.plan_id,pvp.no_of_featured_product,pvp.no_of_category_featured_product,pvp.featured_store,pvp.no_of_product,pvp.joining_fee,pvp.subscription_price,pvp.validity,pvsp.start_date ,pvsp.end_date,pvsp.created_date,pvsp.modified_date,pvpd.plan_name,pvpd.plan_description,pvpd.plan_short_description  FROM ". DB_PREFIX ."purpletree_vendor_plan pvp LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
			
					$query = $this->db->query($sql);
					if( $query->num_rows){
						return $query->row;
						
					} else {
						
					return NULL;	
					}
					
			}
			
	public function getMultiplePlan($seller_id) {
				$sql="SELECT pvp.plan_id,pvp.no_of_featured_product,pvp.no_of_category_featured_product,pvp.featured_store,pvp.no_of_product,pvp.joining_fee,pvp.subscription_price,pvp.validity,pvsp.start_date ,pvsp.new_end_date,pvsp.created_date,pvsp.modified_date,pvpd.plan_name,pvpd.plan_description,pvpd.plan_short_description  FROM ". DB_PREFIX ."purpletree_vendor_plan pvp LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.new_status=1";
			
					$query = $this->db->query($sql);
					if( $query->num_rows){
						return $query->row;
						
					} else {
						
					return NULL;	
					}
					
			}
			
		public function getInvoiceId($seller_id){
				$query = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND status=1");
	
				if($query->num_rows>0){
							return $query->row['invoice_id'];	
						} else {
							return NULL;		
						}
			}
			
		public function getInvoiceIdForMultiplePlan($seller_id){
				$query = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND new_status=1");
	
				if($query->num_rows>0){
							return $query->row['invoice_id'];	
						} else {
							return NULL;		
						}
			}
			
			public function getSubscribePlan($plan_id) {
			
				$sql="SELECT pvsp.start_date ,pvsp.end_date,pvp.plan_id,pvp.no_of_product,pvp.joining_fee,pvp.subscription_price,pvp.validity,pvp.created_date,pvp.modified_date,pvpd.plan_name,pvpd.plan_description,pvpd.plan_short_description  FROM ". DB_PREFIX ."purpletree_vendor_plan pvp LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvp.plan_id='".(int)$plan_id."' AND pvsp.status=1";
						

					$query = $this->db->query($sql);
					if($query->num_rows){
					return $query->row;
						
					} else {
						return NULL;
						
					}
					
				}
				
		public function getSubscribeMultiplePlan($plan_id) {
			
				$sql="SELECT pvsp.start_date ,pvsp.new_end_date,pvp.plan_id,pvp.no_of_product,pvp.joining_fee,pvp.subscription_price,pvp.validity,pvp.created_date,pvp.modified_date,pvpd.plan_name,pvpd.plan_description,pvpd.plan_short_description  FROM ". DB_PREFIX ."purpletree_vendor_plan pvp LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvp.plan_id='".(int)$plan_id."' AND pvsp.new_status=1";
						

					$query = $this->db->query($sql);
					if($query->num_rows){
					return $query->row;
						
					} else {
						return NULL;
						
					}
					
				}
				public function getCurrentPlan($seller_id) {

				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name,pvp.subscription_price, pvpd.plan_name,pvpd.plan_description,pvp.no_of_product,pvsp.start_date,pvsp.end_date,pvsp.reminder,pvsp.status,pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."' AND pvsp.seller_id = '" . (int)$seller_id . "' AND pvsp.status = 1"; 
					$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->row;
					}
			}
			
				public function getCurrentMultiplePlan($seller_id) {

				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name,pvp.subscription_price, pvpd.plan_name,pvpd.plan_description,pvp.no_of_product,pvsp.start_date,pvsp.new_end_date,pvsp.reminder,pvsp.new_status,pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."' AND pvsp.seller_id = '" . (int)$seller_id . "' AND pvsp.new_status = 1"; 
					$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->row;
					}
			}
			
			public function getSellerCurrentPlan($seller_id){
			$query = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan where seller_id='".(int)$seller_id."' AND status=1");
				if($query->num_rows>0){
					
				$query1 = $this->db->query("SELECT code,price FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_item where invoice_id='".(int)$query->row['invoice_id']."'");	
						if($query1->num_rows){
						return $query1->rows;		
						}else {
						return NUll;		
							
						}
					} else {	
					return NUll;	
					}
				}
				
			public function getSellerCurrentMultiplePlan($seller_id){
			$query = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan where seller_id='".(int)$seller_id."' AND new_status=1");
				if($query->num_rows>0){
					
				$query1 = $this->db->query("SELECT code,price FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_item where invoice_id='".(int)$query->row['invoice_id']."'");	
						if($query1->num_rows){
						return $query1->rows;		
						}else {
						return NUll;		
							
						}
					} else {	
					return NUll;	
					}
				}
	public function enableSellerSubscription($seller_id) {
					$query=$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_plan_subscription SET status_id=1 WHERE seller_id='".(int)$seller_id."'");
					
						return true;	
				}
				public function SellerExist($seller_id) {
				$query=$this->db->query("SELECT id FROM ". DB_PREFIX ."purpletree_vendor_plan_subscription WHERE seller_id='".(int)$seller_id."'");
					if($query->num_rows){
					return true;	
					} else { 
					return false;
					}
			}		
			
		public function productDisableForMultiplePlan(){
								
			$used_product = $this->db->query("SELECT COUNT(*) as total,seller_id FROM " . DB_PREFIX . "purpletree_vendor_products GROUP BY seller_id")->rows;

			$total_assign_product = $this->db->query("SELECT SUM(pvp.no_of_product) AS no_of_product,pvsp.seller_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."' AND pvsp.new_status = 1 GROUP BY pvsp.seller_id")->rows; 
			$used_pro=array();
			$data=array();
			if(!empty($used_product)){
			foreach($used_product as $kk=>$vv){
			$used_pro[$vv['seller_id']]=$vv['total'];	
			}
			}
			if(!empty($total_assign_product)){
			foreach($total_assign_product as $id=>$value){
			if(array_key_exists($value['seller_id'],$used_pro)){	
					$data[$value['seller_id']]=array(
					'no_of_product'=>$value['no_of_product'],
					'used_product'=>$used_pro[$value['seller_id']]
					);	
				}				
			}
		}
		if(!empty($data)){
			foreach($data as $seller_id=>$sellerData){
				if($sellerData['no_of_product'] < $sellerData['used_product']) {
				$limitprod = $sellerData['used_product'] - $sellerData['no_of_product'];
				$n_product=$this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int)$seller_id."' ORDER BY id DESC LIMIT ".(int)$limitprod."")->rows;
				$product_ids=array();
				foreach($n_product as $key=> $product){
				$product_ids[]=$product['product_id'];
				}
				$productId=implode(',',$product_ids);
				$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET status=0 WHERE product_id IN (".$productId.")");
				
				$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_products  SET is_approved=0 WHERE product_id IN (".$productId.")");
				}
			
			$no_of_plan = $this->db->query("SELECT COUNT(*) AS no_of_plan FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND new_status=1")->row['no_of_plan'];
			if($no_of_plan===0){
			$query = $this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_plan_subscription SET status_id=0,modified_date =NOW() WHERE seller_id = '" . (int)$seller_id."'");	
				}
			}			
		}
	}
	
		public function SellerDisableProduct($id) {
			$grace = (int)$this->config->get('purpletree_multivendor_grace_period');
			//disable products
			$query=$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_products pvproducts ON(pvsp.seller_id=pvproducts.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvsproducts  ON(pvproducts.product_id=pvsproducts.product_id) LEFT JOIN " . DB_PREFIX . "product p ON(p.product_id=pvsproducts.product_id) SET p.status=0 WHERE pvsproducts.product_plan_id=pvsp.plan_id AND pvsp.id='".(int)$id."'");
		}	
		
		public function SellerDisableFeaturedProduct($id) {
			$grace = (int)$this->config->get('purpletree_multivendor_grace_period');
			//Disable Featured Product
			$query=$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_products pvproducts ON(pvsp.seller_id=pvproducts.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvsproducts  ON(pvproducts.product_id=pvsproducts.product_id) LEFT JOIN " . DB_PREFIX . "product p ON(p.product_id=pvsproducts.product_id) SET pvproducts.is_featured=0,pvsproducts.featured_product_plan_id=0 WHERE pvsproducts.featured_product_plan_id=pvsp.plan_id AND pvsp.id='".(int)$id."'");
		}
		
		public function SellerDisableCategoryFeaturedProduct($id) {
			$grace = (int)$this->config->get('purpletree_multivendor_grace_period');
			// Category Featured Product
			$query=$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp RIGHT JOIN " . DB_PREFIX . "purpletree_vendor_products pvproducts ON(pvsp.seller_id=pvproducts.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_subscription_products pvsproducts  ON(pvproducts.product_id=pvsproducts.product_id) LEFT JOIN " . DB_PREFIX . "product p ON(p.product_id=pvsproducts.product_id) SET pvproducts.is_category_featured =0, pvsproducts.category_featured_product_plan_id=0 WHERE pvsproducts.category_featured_product_plan_id=pvsp.plan_id AND pvsp.id='".(int)$id."'");
		}
		
		
		public function sellerCheckData($seller_id,$id,$plan_id) {
			$grace = (int)$this->config->get('purpletree_multivendor_grace_period');
			
			$query=$this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp WHERE (start_date <= NOW() AND NOW() < DATE_ADD(pvsp.start_date, INTERVAL (SELECT pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_plan pvp WHERE pvp.plan_id=pvsp.plan_id) DAY)) AND seller_id='".(int)$seller_id."' AND plan_id='".(int)$plan_id."' AND id!='".(int)$id."'");
				if($query->num_rows>0){
					return false;
				} else {
					return true;
				}
		}
					public function sellerPlanData() {
					$query=$this->db->query("SELECT * FROM ". DB_PREFIX ."purpletree_vendor_seller_plan GROUP BY seller_id");			
					if($query->num_rows>0){
						return $query->rows;
					}else{
						return NULL;
					}					
				}	
		public function disableSellerSubscription($seller_id) {
					$query=$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_plan_subscription SET status_id=0 WHERE seller_id='".(int)$seller_id."'");
					
				}		
		public function activeSellerPlan($seller_id) {
					if($this->config->get('purpletree_multivendor_multiple_subscription_plan_active')){
									$status='new_status';	
								}else{
									$status='status';	
								}
					$query=$this->db->query("SELECT ".$status." FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE ".$status."=1 AND seller_id='".(int)$seller_id."'");	
				if($query->num_rows>0){
						return false;
					}else{
						return true;
					}					
						
				}
	}

	

?>



