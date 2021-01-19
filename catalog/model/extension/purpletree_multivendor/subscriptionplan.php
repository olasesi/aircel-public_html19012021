<?php 
class ModelExtensionPurpletreeMultivendorSubscriptionplan extends Model{
	
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

					} else {
						
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=1,new_status=1 WHERE id ='".$id."' AND seller_id='".(int)$data['seller_id']."'");
					
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET status=0 WHERE id !='".$id."' AND status=1 AND seller_id='".(int)$data['seller_id']."'");
					
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET new_status=0 WHERE id !='".$id."' AND plan_id='".(int)$data['plan_id']."' AND seller_id='".(int)$data['seller_id']."'");
				
					}
					//$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET  end_date='".$this->db->escape($data['current_date'])."' WHERE id !='".$id."' AND end_date ='0000-00-00 00:00:00' AND seller_id='".(int)$data['seller_id']."'");
					
					//$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_seller_plan SET  new_end_date='".$this->db->escape($data['current_date'])."' WHERE id !='".$id."' AND plan_id='".(int)$data['plan_id']."' AND new_end_date ='0000-00-00 00:00:00' AND seller_id='".(int)$data['seller_id']."'");
					
				return $invoice_id;
			}
			
		public function sellerTotalFeaturedProduct($seller_id){
			$query=$this->db->query("SELECT COUNT(*) AS total_featured_product FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int) $seller_id."' AND is_featured=1");
				if($query->num_rows){
				
					return $query->row['total_featured_product'];
				} else {
					return NULL;	
				}
		}
	
		public function sellerTotalCategpryFeaturedProduct($seller_id){
			$query=$this->db->query("SELECT COUNT(*) AS total_catogry_featured_product FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int) $seller_id."' AND is_category_featured=1");
				if($query->num_rows){
					
					return $query->row['total_catogry_featured_product'];
				} else {
					return NULL;	
				}
		}	
			
		public function enableSellerSubscription($seller_id) {
					$query=$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_plan_subscription SET status_id=1 WHERE seller_id='".(int)$seller_id."'");
					
						return true;	
				}
		public function addFirstSellerPlan($seller_id) {
				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_plan_subscription SET seller_id='".(int)$seller_id."',status_id='0', 	created_date=NOW(),modified_date=NOW()");
			}
			
		public function SellerExist($seller_id) {
				$query=$this->db->query("SELECT id FROM ". DB_PREFIX ."purpletree_vendor_plan_subscription WHERE seller_id='".(int)$seller_id."'");
					if($query->num_rows){
					return true;	
					} else { 
					return false;
					}
			}
			
		public function getTotalSellerPorduct($seller_id) {
				$query=$this->db->query("SELECT COUNT(id) AS total_product FROM ". DB_PREFIX ."purpletree_vendor_products WHERE seller_id='".(int)$seller_id."' AND is_approved=1 ");
					if($query->num_rows){
					return $query->row['total_product'];	
					} else { 
					return NULL;
					}
			}
		
		public function SellerPlanStatus($seller_id) {
				$query=$this->db->query("SELECT id FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND status=1");
					if($query->num_rows){
					return true;	
					} else { 
					return false;
					}
			}	
			
		public function sellerPlanId($seller_id) {
				$query=$this->db->query("SELECT plan_id FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND status=1");
					if($query->num_rows){
					return $query->row['plan_id'];	
					} else { 
					return NULL;
					}
			}	
			
		public function sellerMultiplePlanId($seller_id) {
				$query=$this->db->query("SELECT plan_id FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND new_status=1");
					if($query->num_rows){
					return $query->rows;	
					} else { 
					return NULL;
					}
			}

		public function sellerSubscriptionStatus($seller_id) {
				$query=$this->db->query("SELECT status_id FROM ". DB_PREFIX ."purpletree_vendor_plan_subscription WHERE seller_id='".(int)$seller_id."'");
					if($query->num_rows){
					return $query->row['status_id'];	
					} else { 
					return NULL;
					}
			}	
		
		public function addSellerPaymentHistory($data=array()) {			
				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_plan_invoice_history SET invoice_id='".(int)$data['invoice_id']."',status_id=1,payment_mode='Offline',comment='".$this->db->escape($data['comment'])."',created_date=NOW()");
			}
			public function addSellerPaymentHistoryfrompaypal($data=array()) {			
				$this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_plan_invoice_history SET invoice_id='".(int)$data['invoice_id']."',status_id='".(int)$data['status_id']."',payment_mode='Paypal',comment='".$this->db->escape($data['comment'])."',transaction_id='".$this->db->escape($data['transaction_id'])."',created_date=NOW(),modified_date =NOW()");
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_plan_invoice SET status_id='".(int)$data['status_id']."' WHERE invoice_id ='".(int)$data['invoice_id']."'");
					
					if($data['status_id']==2){						
					$query= $this->db->query("SELECT seller_id FROM ". DB_PREFIX ."purpletree_vendor_plan_invoice WHERE invoice_id='".(int)$data['invoice_id']."'");
					if($query->num_rows>0){
					$this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_plan_subscription SET status_id=1 WHERE seller_id='".$query->row['seller_id']."'");
					}
					}
			}
	
		public function getoldinvoiceId($seller_id,$invoice_id){
				$query = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan where seller_id='".(int)$seller_id."' AND invoice_id < ".$invoice_id." ORDER BY id DESC LIMIT 0,1");
					if($query->num_rows){
						return $query->row['invoice_id'];	
					} else {
						return '0';		
					}
			}
			
		public function getInvoiceHistory($invoice_id){
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_history where invoice_id='".(int)$invoice_id."' ORDER BY id DESC");
					if($query->num_rows){
						return $query->rows;	
					} else {
						return NUll;		
					}
			}
		
		public function getstausfromid($id) {
				$query = $this->db->query("SELECT pvpisl.status FROM `".DB_PREFIX."purpletree_vendor_plan_invoice_status` pvpis LEFT JOIN `".DB_PREFIX."purpletree_vendor_plan_invoice_status_languge` pvpisl ON (pvpisl.status_id = pvpis.status_id) WHERE pvpisl.language_id='". (int)$this->config->get('config_language_id') ."' AND pvpisl.status_id ='". $id ."'");
					if($query->num_rows){
						return $query->row['status'];	
					} else {
						return NUll;		
					}
			}
		
		public function getPlanId($invoice_id,$old_invoice_id){
				$invoice=array();
				$old_invoice=array();
				$data=array();
				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice where invoice_id='".(int)$invoice_id."'");
				//old invoice id


				if($query->num_rows){
					$invoice=$query->row;
					$sts 	= $this->getstausfromid($invoice['status_id']);
					$data['invoice']=array();
					$data['invoice']['seller_id']=$invoice['seller_id'];
					$data['invoice']['plan_id']=$invoice['plan_id'];
					$data['invoice']['payment_mode']=$invoice['payment_mode'];
					$data['invoice']['status_id']=$sts;
					$data['invoice']['status_id_id']=$invoice['status_id'];
					$data['invoice']['created_date']=date('d/m/Y',strtotime($invoice['created_date']));
					// print_r($data['invoice']);
					$query1 = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_item where invoice_id='".(int)$invoice_id."'");
					if($query1->num_rows){
						$invoice1=$query1->rows;
						$data['invoice']['item']=array();
						foreach($query1->rows as $item){

							if($item['code']!='previous_balance'){
							$data['invoice']['item'][]=array(
							'title'=>$item['title'],
							'code'=>$item['code'],
							'price'=>$item['price']
							);
							} else {
					$old_invoice_data = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice_item where invoice_id='".(int)$old_invoice_id."'");

							if($old_invoice_data->num_rows){
							foreach($old_invoice_data->rows as $olditem){
									if($olditem['code']=='previous_balance'){
									$data['invoice']['item'][]=array(
									'title'=>$olditem['title'],
									'code'=>$olditem['code'],
									'price'=>$olditem['price']
									);
									}
								  }
								}
							  }
							}
						  }
					   }

					return $data;
				}
			
		public function getInvoiceStatus($seller_id){
				$query = $this->db->query("SELECT MAX(invoice_id) AS id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id='".(int)$seller_id."'");
	
				if($query->num_rows>0){
							$invoice_id=$query->row['id'];	
						} else {
							$invoice_id=NULL;		
						}
				$query1 = $this->db->query("SELECT status_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id='".(int)$seller_id."' AND invoice_id='".$invoice_id."'");
				if($query1->num_rows>0){
							return $query1->row['status_id'];	
						} else {
							return NULL;		
						}
			}
			
		public function getPlanInvoiceStatus($seller_id,$plan_id){
				$query = $this->db->query("SELECT MAX(invoice_id) AS id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id='".(int)$seller_id."' and plan_id='".(int)$plan_id."'");
	
				if($query->num_rows>0){
							$invoice_id=$query->row['id'];	
						} else {
							$invoice_id=NULL;		
						}
				$query1 = $this->db->query("SELECT status_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id='".(int)$seller_id."' AND invoice_id='".$invoice_id."'");
				if($query1->num_rows>0){
							return $query1->row['status_id'];	
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
			
		public function getStoreDetail($customer_id){
				$query = $this->db->query("SELECT pvs.* FROM " . DB_PREFIX . "purpletree_vendor_stores pvs where pvs.seller_id='".(int)$customer_id."'");
				return $query->row;
			}	
		
		public function getSubscriptionStauts($seller_id){

				 $sql="SELECT pvps.status_id FROM ". DB_PREFIX ."purpletree_vendor_plan pvp  LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_subscription pvps ON ((pvps.seller_id = pvsp.seller_id) AND (pvps.status_id = pvp.status)) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
			$query = $this->db->query($sql);
					if($query->num_rows>0){
					return $query->row['status_id'];	
					} else {	
					return NUll;	
					}
			}	
			
			public function invoiceStauts($invoice_id){
				$query = $this->db->query("SELECT status_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice where invoice_id='".(int)$invoice_id."'");
					if($query->num_rows>0){
					$invoice_status 	= $this->getstausfromid($query->row['status_id']);	
					return $invoice_status;	
					} else {	
					return NUll;	
					}
			}
		
		public function getInvoiceStauts($seller_id,$plan_id){
				$query = $this->db->query("SELECT status_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice where seller_id='".(int)$seller_id."' AND plan_id='".(int)$plan_id."'");
					if($query->num_rows>0){
					return $query->row['status_id'];	
					} else {	
					return NUll;	
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

		public function getSubscriptionPlan() {				
				$sql="SELECT pvp.plan_id,pvp.no_of_featured_product,pvp.no_of_category_featured_product,pvp.featured_store,pvp.status,pvp.no_of_product,pvp.joining_fee,pvp.subscription_price,pvp.validity,pvp.created_date,pvp.modified_date,pvpd.plan_name,pvpd.plan_description,pvpd.plan_short_description  FROM ". DB_PREFIX ."purpletree_vendor_plan pvp LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvp.status=1";


				$query = $this->db->query($sql);

				return $query->rows;
			}				
				
			
		public function getNoOfPlanProduct($seller_id) {				
			$total_assign_product = $this->db->query("SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name,SUM(pvp.no_of_product) AS no_of_product ,SUM(pvp.no_of_featured_product) AS no_of_featured_product, 	SUM(pvp.no_of_category_featured_product) AS no_of_category_featured_product FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."' AND pvsp.seller_id = '" . (int)$seller_id . "' AND pvsp.new_status = 1"); 
			if($total_assign_product->num_rows){
				return $total_assign_product->row;
			}
			
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
		
		public function getCustomer($customer_id) {
				$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
					if ($query->num_rows) {
						return $query->row;
						} else {
						return NULL;
						}				
			}
	
	public function getsellernamefromsell($seller_id) {

				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name,c.firstname,c.lastname FROM " . DB_PREFIX . "customer c WHERE c.customer_id='".$seller_id."'";
				$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->row;
					}
	}				
	public function getCurrentPlan($seller_id) {

				$sql = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS seller_name,pvp.subscription_price, pvpd.plan_name,pvpd.plan_description,pvp.no_of_product,pvsp.start_date,pvsp.end_date,pvsp.reminder,pvsp.status,pvp.validity FROM " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan pvp ON (pvp.plan_id = pvsp.plan_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_subscription pvps ON (pvps.seller_id = pvsp.seller_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_plan_description pvpd ON (pvpd.plan_id = pvsp.plan_id) WHERE pvpd.language_id='". (int)$this->config->get('config_language_id') ."' AND pvsp.seller_id = '" . (int)$seller_id . "' AND pvsp.status = 1"; 
					$query = $this->db->query($sql);
					if($query->num_rows){
						return $query->row;
					}
			}
		 public function getstatuslist() {
				$query = $this->db->query("SELECT pvpis.status_id,pvpisl.status FROM `".DB_PREFIX."purpletree_vendor_plan_invoice_status` pvpis LEFT JOIN `".DB_PREFIX."purpletree_vendor_plan_invoice_status_languge` pvpisl ON (pvpisl.status_id = pvpis.status_id) WHERE pvpisl.language_id='". (int)$this->config->get('config_language_id') ."'");
					if($query->num_rows){
						return $query->rows;	
					} else {
						return NUll;		
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
				public function getSubscribePlanInfo($plan_id) {
			
				$sql="SELECT pvsp.start_date ,pvsp.end_date,pvp.plan_id,pvp.no_of_product,pvp.joining_fee,pvp.subscription_price,pvp.validity,pvp.created_date,pvp.modified_date,pvpd.plan_name,pvpd.plan_description,pvpd.plan_short_description  FROM ". DB_PREFIX ."purpletree_vendor_plan pvp LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvp.plan_id='".(int)$plan_id."'";
						

					$query = $this->db->query($sql);
					if($query->num_rows){
					return $query->row;
						
					} else {
						return NULL;
						
					}
					
				}
			public function getCurrentPlanByPlanId($seller_id,$plan_id) {
				$query=$this->db->query("SELECT start_date,end_date,new_end_date FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND plan_id='".$plan_id."' AND new_status=1");
					if($query->num_rows){
					return $query->row;
					} else { 
					return false;
					}
			}
			
		public function validity($plan_id) {
				$query=$this->db->query("SELECT validity FROM ". DB_PREFIX ."purpletree_vendor_plan WHERE plan_id='".$plan_id."'");
					if($query->num_rows){
					return $query->row['validity'];
					} else { 
					return false;
					}
			}
		public function getCurrentPlanByPlanId1($invoice_id) {
				$query=$this->db->query("SELECT start_date,end_date,new_end_date FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE invoice_id='".(int)$invoice_id."'");
					if($query->num_rows){
					return $query->row;
					} else { 
					return false;
					}
			}	
			
		public function getLastPlan($seller_id,$plan_id) {
				$query=$this->db->query("SELECT start_date,end_date,new_end_date FROM ". DB_PREFIX ."purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' and plan_id='".(int)$plan_id."' ORDER BY id DESC LIMIT 1");
					if($query->num_rows){
					return $query->row;
					} else { 
					return false;
					}
			}
			
		public function getMultipleSubscriptionInvoiceStatus($seller_id,$plan_id){
				$query3 = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND plan_id='".(int)$plan_id."' AND new_status=1");
				if($query3->num_rows>0){
							$invoice_id=$query3->row['invoice_id'];	
					} else {	
							return NULL;			
						}						
				$query1 = $this->db->query("SELECT status_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id='".(int)$seller_id."' AND invoice_id='".$invoice_id."'");
				if($query1->num_rows>0){
							return $query1->row['status_id'];	
						} else {
							return NULL;		
						}
			}	

		public function getSubscriptionInvoiceStatus($seller_id,$plan_id){
				$query3 = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "purpletree_vendor_seller_plan WHERE seller_id='".(int)$seller_id."' AND plan_id='".(int)$plan_id."' AND status=1");
				if($query3->num_rows>0){
							$invoice_id=$query3->row['invoice_id'];	
					} else {	
							return NULL;			
						}						
				$query1 = $this->db->query("SELECT status_id FROM " . DB_PREFIX . "purpletree_vendor_plan_invoice WHERE seller_id='".(int)$seller_id."' AND invoice_id='".$invoice_id."'");
				if($query1->num_rows>0){
							return $query1->row['status_id'];	
						} else {
							return NULL;		
						}
			}	
			public function defaultPlan() {
				$query=$this->db->query("SELECT plan_id FROM ". DB_PREFIX ."purpletree_vendor_plan WHERE default_subscription_plan=1 AND status=1");
					if($query->num_rows){
					return $query->row['plan_id'];
					} else { 
					return NULL;
					}
			}				

	}
?>