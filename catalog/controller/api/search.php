<?php
class ControllerApiSearch extends Controller {
	private $error = array();
    public function index() {
        $this->load->model('journal2/search');
        $this->load->model('journal2/super_filter');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        
        $text = $this->request->post['search'];
        $json = array();

        if(isset($this->request->post['search'])) {
            $results = array();
            $products_total = 0;
            $matches = $this->model_journal2_search->search_data($text);
            $pageno  = isset($this->request->post['pageno']) ? $this->request->post['pageno'] : 1;
            $results = $this->paginateResults($matches, $pageno);
            $json['data']['pageno'] = $pageno;
            $json['status'] = true;
            if(count($results) < 1){$json['status'] = false;}
            $json['data']['product_total'] = count($matches);
            foreach($results as $result) {
                $result = $this->model_catalog_product->getProduct($result['product_id']);
                $json['data']['products'][] = array(
                    'product_id' => $result['product_id'],
                    'name'  => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
                    'url'   => htmlspecialchars_decode($this->url->link('product/product', '&product_id=' . $result['product_id'])),
                    'image' => $result['image'],
                    'price' => $result['price'], 
                    'status' => $result['status'],
                    'special' => $result['special'],
                    'quantity'=>$result['quantity']
                );
            }
            
            $categoryfilterparams = array();
            $categoryfilterparams['search'] = $text;
            $filter = $this->model_journal2_super_filter->getCategories($categoryfilterparams);
            $json['data']['categories'] = $this->sortCategoriesByTotal($filter);
        } else { 
            $json['status'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function getproductsearch(){
        $text = $this->request->post['search'];
        $this->load->model('journal2/super_filter');
        $this->load->model('journal2/search');
        $data = array();
        $json = array();
        $data['search'] = $this->request->post['search'];
        $data['categories'][] = $this->request->post['categoryid'];
        $data['page']= $this->request->post['pageno'];
        $data['limit']= 35;
        $data['start'] = ($data['page'] - 1) * $data['limit'];
        
        
        $json['status'] = true;
        
        // $matches = $this->model_journal2_search->search_data($text,$data['limit'],$start);
        $matches = $this->model_journal2_super_filter->getProductsWithData($data);
        $pageno  = isset($this->request->post['pageno']) ? $this->request->post['pageno'] : 1;
        $results = $this->paginateResults($matches, $pageno);
        $json['data']['pageno'] = $pageno;
        if(count($matches) < 1){$json['status'] = false;}
        foreach($matches as $result) {
            $result = $this->model_catalog_product->getProduct($result['product_id']);
            $json['data']['products'][] = array(
                'product_id' => $result['product_id'],
                'name'  => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
                'url'   => htmlspecialchars_decode($this->url->link('product/product', '&product_id=' . $result['product_id'])),
                'image' => $result['image'],
                'price' => $result['price'], 
                'status' => $result['status'],
                'special' => $result['special'],
                'quantity'=>$result['quantity']
            );
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function sortCategoriesByTotal($categories){
        $total = array_column($categories, 'total');
        array_multisort($total, SORT_DESC, $categories);
        return  $categories;
    }
    
    private function paginateResults($products,$pageno = 1,$limit = 30){
        $results = array_slice($products, ($limit * ($pageno - 1)), $limit);
        return $results;
    }
}