<?php
// catalog/controller/api/category.php
class ControllerApiProduct extends Controller {
    
    // Gets all products
    public function index() {
    $this->load->language('api/product');
    $json = array();
 
    if (!isset($this->session->data['api_id'])) {
      $json['status'] = FALSE;
      $json['error']['warning'] = $this->language->get('error_permission');
    } else {
        // load model
        $this->load->model('catalog/product');
        
        $products = $this->model_catalog_product->getProducts();
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
        // echo "index";

  }
  
    public function product_id() {
        
    $this->load->language('api/product');
    $json = array();
    
    if (!isset($this->session->data['api_id'])) {
      $json['error']['warning'] = $this->language->get('error_permission');
    } else {
        if (isset($this->request->post['productid'])) {
			$productid = $this->request->post['productid'];
		} else { 
		    $productid = 0;
			$json['status'] = FALSE;
			$json['message'] = 'NO productid provided';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		// load model
        $this->load->model('catalog/product');
        $this->load->model('custom/product');
        $this->load->model('extension/purpletree_multivendor/sellerproduct');
       
        $product = $this->model_catalog_product->getProduct($productid);
        // var_dump($product,$productid);
        if (true) {
            // $product['description'] = strip_tags(htmlspecialchars_decode($product['description']));
            $product['description'] = htmlspecialchars_decode($product['description']);
        }
        $productCategories = $this->model_catalog_product->getCategories($productid);
        
        $productOptions = $this->model_catalog_product->getProductOptions($productid);
        $productUrl = $this->model_custom_product->getProductSeoUrls($productid);
        $productImages = $this->model_catalog_product->getProductImages($productid);
        
        $productRelated = $this->model_catalog_product->getProductRelated($productid);
        $productStore = $this->model_extension_purpletree_multivendor_sellerproduct->getSellername($productid);
        // var_dump($productStore);
        $json['status'] = TRUE;
        $json['data'] = $product;
        $url = array();
       	foreach ($productUrl as $result) {
       	    $url = $result["1"];
       	}
        $base = "https://www.obejor.com.ng/";
        $json['data']['producturl']  =$base.$url;
        
        $image = array();
        foreach ($productImages as $result) {
            array_push($image, $result['image']);
       	}
        $json['data']['productimages']  = $image;
        
        $test = array();
        foreach ($productRelated as $result) {
       	    array_push($test,$result);
       	}
     
        $json['data']['productoptions'] = $productOptions;
        $json['data']['categories']  = $productCategories;
        $json['data']['relatedproducts']  = $test;
        $json['data']['productstore']   = $productStore;
       
        
        
        
        
        
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
 
}