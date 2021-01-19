<?php
class ModelCustomBanners extends Model {
    public function getMainBanners() {
        $module_id = 235;
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_id = ' . (int)$module_id);
        if (isset($query->row['module_data'])) {
            $query->row['module_data'] = json_decode($query->row['module_data'], true);
        }
        return $query->row;
    }
    
}