<?php
class ControllerApiModules extends Controller {
    public function index() {
        $this->load->language('api/category');
        $json = array();
        
        if (!isset($this->session->data['api_id'])) {
            $json['status'] = FALSE;
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            // load model
            $this->load->model('journal2/product');
            $this->load->model('journal2/module');
            $this->load->model('catalog/product');
            $activeModules = array(001,002,003,004,005,006,007);
            $module = $this->request->post['moduleid'];
            $pageno = isset($this->request->post['pageno']) ? $this->request->post['pageno'] : 1;
            $products = array();
            if(in_array($module,$activeModules)){
                switch ($module){
                    case 001 : 
                      $products = $this->getFeaturedProducts();
                    break;
                    case 002 : 
                      $products = $this->getBestSellers();
                    break;
                    case 003 : 
                      $products = $this->getLatestProducts(); 
                    break;
                    case 004 : 
                      $products = $this->getMostViewedProducts(); 
                    break;
                    case 005 : 
                      $products = $this->getFeaturedProducts();
                    break;
                    case 006 : 
                      $products = $this->getDeals(); 
                    break;
                    case 007 : 
                      $products = $this->getSpecialDeals(); 
                    break;
                }
                $products =   $this->paginateResults($products,$pageno);
                $json['status'] = TRUE;
    	    	$json['data'] = $products;
                $json['pageno'] = $pageno;
            }else {
                $json['status'] = FALSE;
                $json['data'] = "Incorrect module id sent";
            }
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
    
    private function getMostViewedProducts(){
        $mostViewedProducts = $this->model_journal2_product->getMostViewed($limit = 200);
        return $mostViewedProducts;
    }
    
    private function getLatestProducts(){
        $latestProducts = array();
	    $latestProductsInfo= $this->model_journal2_product->getLatest($limit = 200, $filter_category = false);
	    $latestProductsIds = array_keys($latestProductsInfo);
        for( $i = 0;$i < count($latestProductsIds); $i++){
            array_push($latestProducts,$latestProductsInfo[$latestProductsIds[$i]]);
        }
        return $latestProducts;
    }
    
    private function getDeals(){
        $specialProducts = array();
	    $specialProductsInfo= $this->model_journal2_product->getSpecials($limit = 200, $filter_category = false);
        $specialProductsIds = array_keys($specialProductsInfo);
        for( $i = 0;$i < count($specialProductsIds); $i++){
            array_push($specialProducts,$specialProductsInfo[$specialProductsIds[$i]]);
        }
        return $specialProducts;
    }
    
    private function getFeaturedProducts(){
        $products =  array();
        $randomIds = $this->model_journal2_product->getRandomProducts(200);
        for($i = 0; $i < sizeof($randomIds); $i++){
            // $productfeat = $this->model_catalog_product->getProduct($randomIds[$i]['product_id']);
            $product = $this->model_catalog_product->getProduct($randomIds[$i]['product_id']);
            // array_push($products,$productfeat);
            array_push($products,$product);
        }
	    return $products;
    }
    
    private function getBestSellers(){
        $bestSellersProducts = array();
	    $bestSellersInfo= $this->model_journal2_product->getBestsellers($limit = 200, $filter_category = false);
	    $bestSellersIds = array_keys($bestSellersInfo);
        for( $i = 0;$i < count($bestSellersIds); $i++){
            array_push($bestSellersProducts,$bestSellersInfo[$bestSellersIds[$i]]);
        }
        return $bestSellersProducts;
    }
    
    private function getSpecialDeals(){
        $specialProductsCustomModule =  $this->model_journal2_module->getModule(250);
        $specialProductsCustomModuleIds = array();
        $specialCustomModuleProducts    = array();
        foreach ($specialProductsCustomModule['module_data']['product_sections'][0]['products'] as $result) {
            array_push($specialProductsCustomModuleIds,$result['data']['id']);
        }
        foreach($specialProductsCustomModuleIds as $result) {
            $tmpProduct = $this->model_catalog_product->getProduct($result);
            if ($tmpProduct !== false) {
                array_push($specialCustomModuleProducts,$tmpProduct);
                
            }
        }
        return $specialCustomModuleProducts;
    }
    
    private function paginateResults($products,$pageno = 1,$limit = 30){
        $results = array_slice($products, ($limit * ($pageno - 1)), $limit);
        return $results;
    }
}