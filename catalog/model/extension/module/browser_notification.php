<?php
class ModelExtensionModuleBrowserNotification extends Model {

	public function getModulesByCode($code) {

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `name`");
		return $query->rows;
    }

    public function setToken($token){
        
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "browser_notification` WHERE `token` = '" . $this->db->escape($token) . "'");

		if(empty($query->rows)){
             
            $this->db->query("INSERT INTO `" . DB_PREFIX . "browser_notification` SET `token` = '" . $this->db->escape($token) . "', `date` = NOW()");
        }
    }
}