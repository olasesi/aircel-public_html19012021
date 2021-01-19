<?php
class ModelExtensionModuleBrowserNotification extends Model {
		

	public function createTable() {

        $query = $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "browser_notification (browser_notification_id INT(11) AUTO_INCREMENT, token VARCHAR(255), date DATE, PRIMARY KEY (browser_notification_id))");        
	}


	public function getDeviceIds(){

		$query = $this->db->query("SELECT token FROM `" . DB_PREFIX . "browser_notification`");
		return $query->rows;
	}
}