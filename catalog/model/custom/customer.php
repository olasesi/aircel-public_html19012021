<?php
class ModelCustomCustomer extends Model {
    
	public function editPassword($customer_id, $password) {
	    $this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "', code = '' WHERE customer_id = '" . (int)$customer_id . "'");
	}
	
	public function getPassword($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
        if ($query->num_rows) {
            	$password = $query->row['password'];
            	$salt = $query->row['salt'];
        }

		return $password;
	}
	
	
}
