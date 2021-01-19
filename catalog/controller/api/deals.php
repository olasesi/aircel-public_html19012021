<?php
class ControllerApiDeals extends Controller {
    
    public function index() {
        $this->load->language('api/category');
        $json = array();
        if (!isset($this->session->data['api_id'])) {
            $json['status'] = FALSE;
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            
             // load model
            $this->load->model('journal2/product');
            $this->load->model('catalog/product');
            
            $randomIds = $this->model_journal2_product->getRandomProducts(10);
            $products = array();
           
            for($i = 0; $i < sizeof($randomIds); $i++){
                // echo json_encode($products[$i]);
                $product = $this->model_catalog_product->getProduct($randomIds[$i]['product_id']);
                array_push($products,$product);
            }
            $json['status'] = TRUE;
            $json['data'] = $products;
            $this->response->addHeader('Content-Type: application/json');
    		return $this->response->setOutput(json_encode($json));
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
          $this->response->addHeader('Access-Control-Allow-Origin: ' . '*');
          $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          $this->response->addHeader('Access-Control-Max-Age: 1000');
          $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
 
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function getweekdeals() {
        $this->load->language('api/category');
        $json = array();
        
        if (!isset($this->session->data['api_id'])) {
            $json['status'] = FALSE;
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
          $this->response->addHeader('Access-Control-Allow-Origin: ' . '*');
          $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          $this->response->addHeader('Access-Control-Max-Age: 1000');
          $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
 
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function getspecialdeals() {
        echo "Hello World";
    }
    
}