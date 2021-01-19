<?php
require_once DIR_SYSTEM . 'journal2/classes/journal2_skin.php';

class ModelJournal2Db extends Model {

    public function getConfigSettings() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "journal2_config WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' OR store_id = -1");

        $res = array();

        foreach ($query->rows as $row) {
            $key = $row['key'];
            $value = $row['serialized'] ? json_decode($row['value'], true) : $row['value'];
            $res[$key] = $value;
        }

        return $res;
    }

    public function getSkinSettings($skin_id) {
        /* get parent */
        $parent_id = $skin_id;
        if ($skin_id >= 100) {
            $query = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "journal2_skins WHERE `skin_id` = '" . (int)$skin_id . "'");
            if ($query->num_rows) {
                $parent_id = $query->row['parent_id'];
            } else {
                $parent_id = 1;
            }
        }

        $settings = array();

        if ($parent_id !== $skin_id) {
            $journal_skin = new Journal2Skin($this->db, $parent_id);
            $settings = array_merge($settings, $journal_skin->load());
        }
        $journal_skin = new Journal2Skin($this->db, $skin_id);
        $settings = array_merge($settings, $journal_skin->load());

        return $settings;
    }

    public function skinExists($skin_id) {
        if (file_exists(DIR_SYSTEM . "journal2/data/themes/{$skin_id}.json")) {
            return true;
        }
        $query = $this->db->query("SELECT count(*) as num FROM " . DB_PREFIX . "journal2_settings WHERE theme_id = '" . (int)$skin_id . "'");
        return $query->row['num'] > 0;
    }

}
?>