<?php 
class ModelExtensionPurpletreeMultivendorCommissioninvoicenotify extends Model{

		public function addPaypalPaymentHistory($data) {

			$query1 = $this->db->query("SELECT invoice_id,seller_id FROM ". DB_PREFIX ."purpletree_vendor_payments WHERE invoice_id='".(int)$data['invoice_id']."'");
			
			if ($query1->num_rows>0) {
			$query2 = $this->db->query("UPDATE ". DB_PREFIX ."purpletree_vendor_payments SET status='".$this->db->escape($data['status'])."',payment_mode='".$this->db->escape($data['payment_mode'])."',transaction_id='".$this->db->escape($data['txn_id'])."',amount='".$this->db->escape($data['amount'])."',created_at=NOW(),updated_at=NOW() WHERE invoice_id='".(int)$data['invoice_id']."'");
			} else {
			$query3 = $this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_payments SET invoice_id='".(int)$data['invoice_id']."',status='".$this->db->escape($data['status'])."',payment_mode='".$this->db->escape($data['payment_mode'])."',transaction_id='".$this->db->escape($data['txn_id'])."',amount='".$this->db->escape($data['amount'])."',created_at=NOW(),updated_at=NOW()");	
			}
			$query4 = $this->db->query("SELECT status_id FROM ". DB_PREFIX ."purpletree_vendor_plan_invoice_status_languge WHERE status='".$this->db->escape($data['status'])."' AND language_id='".(int)$this->config->get('config_language_id')."'"); 
			if ($query4->num_rows) {
			$status= $query4->row['status_id'];
			}
			
			 $query5 = $this->db->query("INSERT INTO ". DB_PREFIX ."purpletree_vendor_payment_settlement_history SET invoice_id='".(int)$data['invoice_id']."',status_id='".$status."',payment_mode='".$this->db->escape($data['payment_mode'])."',transaction_id='".$this->db->escape($data['txn_id'])."',comment='".$this->db->escape($data['comment'])."',created_date=NOW(),modified_date=NOW()");   
			}

	}
?>