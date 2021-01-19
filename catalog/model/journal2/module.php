<?php
class ModelJournal2Module extends Model {

    public function getModule($module_id,$data = array()) {
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_id = ' . (int)$module_id;
        $product_data = array();
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
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
        if (isset($query->row['module_data'])) {
            $query->row['module_data'] = json_decode($query->row['module_data'], true);
        }
        return $query->row;
    }

    public function getProductTabs($product_id, $product_info) {
        $product_id = (int)$product_id;

        /* get product brand */
        $query = $this->db->query('SELECT manufacturer_id FROM ' . DB_PREFIX . 'product WHERE product_id = ' . $product_id);
        if ($query->num_rows > 0) {
            $manufacturer_id = $query->row['manufacturer_id'];
        } else {
            $manufacturer_id = 0;
        }

        /* get product categories */
        $query = $this->db->query('SELECT category_id FROM ' . DB_PREFIX . 'product_to_category WHERE product_id = ' . $product_id);
        $category_ids = array();
        foreach ($query->rows as $row) {
            $category_ids[] = (int)$row['category_id'];
        }

        /* get modules */
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_type = "journal2_product_tabs"');
        $tabs = array();
        foreach ($query->rows as $row) {
            if (isset($row['module_data'])) {
                $tab = json_decode($row['module_data'], true);
                if (!$tab['status']) continue;
                $store_id = Journal2Utils::getProperty($tab, 'store_id', -1);
                if ($store_id != -1 && $store_id != $this->config->get('config_store_id')) {
                    continue;
                }
                switch (Journal2Utils::getProperty($tab, 'global')) {
                    case 0:
                        foreach (Journal2Utils::getProperty($tab, 'products', array()) as $product) {
                            if (Journal2Utils::getProperty($product, 'data.id') == $product_id) {
                                $tabs[$row['module_id']] = $tab;
                                break;
                            }
                        }
                        break;
                    case 1:
                        $tabs[$row['module_id']] = $tab;
                        break;
                    case 2:
                        if ($category_ids) {
                            foreach (Journal2Utils::getProperty($tab, 'categories', array()) as $category) {
                                foreach ($category_ids as $category_id) {
                                    if (Journal2Utils::getProperty($category, 'data.id') == $category_id) {
                                        $tabs[$row['module_id']] = $tab;
                                        break; break;
                                    }
                                }
                            }
                        }
                        break;
                    case 3:
                        if ($manufacturer_id) {
                            foreach (Journal2Utils::getProperty($tab, 'manufacturers', array()) as $manufacturer) {
                                if (Journal2Utils::getProperty($manufacturer, 'data.id') == $manufacturer_id) {
                                    $tabs[$row['module_id']] = $tab;
                                    break;
                                }
                            }
                        }
                        break;
                    case 4:
                        if ($product_info['quantity'] <= 0) {
                            $tabs[$row['module_id']] = $tab;
                        }
                        break;
                }
            }
        }
        return $tabs;
    }

    public function getEnquiryProducts() {
        $product_ids = array();
        $category_ids = array();
        $manufacturer_ids = array();
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_type = "journal2_product_tabs"');
        foreach ($query->rows as $row) {
            if (isset($row['module_data'])) {
                $tab = json_decode($row['module_data'], true);
                if (!$tab['status']) continue;
                $store_id = Journal2Utils::getProperty($tab, 'store_id', -1);
                if ($store_id != -1 && $store_id != $this->config->get('config_store_id')) {
                    continue;
                }
                if (Journal2Utils::getProperty($tab, 'content_type') !== 'enquiry') {
                    continue;
                }
                $this->journal2->settings->set('enquiry_button_text', Journal2Utils::getProperty($tab, 'name.value.' . $this->config->get('config_language_id')));
                $this->journal2->settings->set('enquiry_button_icon', Journal2Utils::getIconOptions2(Journal2Utils::getProperty($tab, 'icon')));
                $this->journal2->settings->set('enquiry_popup_code' , (int)Journal2Utils::getProperty($tab, 'popup'));
                switch (Journal2Utils::getProperty($tab, 'global')) {
                    case 0:
                        foreach (Journal2Utils::getProperty($tab, 'products', array()) as $product) {
                            $id = (int)Journal2Utils::getProperty($product, 'data.id');
                            $product_ids[$id] = $id;
                        }
                        break;
                    case 1:
                        return 'all';
                        break;
                    case 2:
                        foreach (Journal2Utils::getProperty($tab, 'categories', array()) as $category) {
                            $id = (int)Journal2Utils::getProperty($category, 'data.id');
                            $category_ids[$id] = $id;
                        }
                        break;
                    case 3:
                        foreach (Journal2Utils::getProperty($tab, 'manufacturers', array()) as $manufacturer) {
                            $id = (int)Journal2Utils::getProperty($manufacturer, 'data.id');
                            $manufacturer_ids[$id] = $id;
                        }
                        break;
                    case 4:
                        return 'outofstock';
                }
            }
        }

        /* add products from categories */
        if (count($category_ids)) {
            $query = $this->db->query('SELECT product_id FROM ' . DB_PREFIX . 'product_to_category WHERE category_id IN (' . implode(',', $category_ids) . ')');
            foreach ($query->rows as $row) {
                $id = (int)$row['product_id'];
                $product_ids[$id] = $id;
            }
        }

        /* add products from brands */
        if (count($manufacturer_ids)) {
            $query = $this->db->query('SELECT product_id FROM ' . DB_PREFIX . 'product WHERE manufacturer_id IN (' . implode(',', $manufacturer_ids) . ')');
            foreach ($query->rows as $row) {
                $id = (int)$row['product_id'];
                $product_ids[$id] = $id;
            }
        }

        return $product_ids;
    }

}
?>