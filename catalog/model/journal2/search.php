<?php
class ModelJournal2Search extends Model {

    public function search($data, $limit = 50, $include_description = false) {
        $params = array(
            'filter_name'         => $data,
            'filter_tag'          => $data,
            'sort'                => 'p.sort_order',
            'order'               => 'ASC',
            'start'               => 0,
            'limit'               => $limit
        );

        if ($include_description) {
            $params['filter_description'] = $data;
        }

        $this->load->model('catalog/product');

        return $this->model_catalog_product->getProducts($params);
        
    }

    public function search_data($data, $limit = 35, $start = 0, $include_description = false) {
        $params = array(
            'filter_name'         => $data,
            'filter_tag'          => $data,
            'sort'                => 'p.sort_order',
            'order'               => 'ASC',
            'start'               => $start,
            'limit'               => $limit
           
        );

        if ($include_description) {
            $params['filter_description'] = $data;
        }

        $this->load->model('catalog/product');
        return $this->model_catalog_product->getProducts($params);
    }
    
    
}
?>
