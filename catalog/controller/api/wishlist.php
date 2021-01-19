<?php
class ControllerApiWishlist extends Controller { 

    public function addwishlist() {
        $this->load->language('api/cart');
		$this->load->model('custom/wishlist');

		$json = array();
	    $json['status'] = true;
        if (isset($this->request->post['customer_id'])) {
            
	        $customer_id = $this->request->post['customer_id'];
	        $product_id = $this->request->post['product_id'];

			$wishlist = $this->model_custom_wishlist->addWishlist($customer_id, $product_id);
			$json['status'] = true;
        } else {
            $json['status'] = false;
        }
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    public function removewishlist() {
        $this->load->language('api/cart');
		$this->load->model('custom/wishlist');

		$json = array();
	    $json['status'] = true;
        if (isset($this->request->post['customer_id'])) {
            
	        $customer_id = $this->request->post['customer_id'];
	        $product_id = $this->request->post['product_id'];

			$wishlist = $this->model_custom_wishlist->deleteWishlist($customer_id, $product_id);
			$json['status'] = true;
        } else {
            $json['status'] = false;
        }
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    public function getwishlist() {
        $this->load->language('api/cart');
		$this->load->model('custom/wishlist');
		$this->load->model('catalog/product');

		$json = array();
	    $json['status'] = true;
        if (isset($this->request->post['customer_id'])) {
            
	        $customer_id = $this->request->post['customer_id'];
	        
	        // Wishlist
            $wishlist = $this->model_custom_wishlist->getWishlist($customer_id);
            $wishlistProductsIds = array();
            for( $i = 0; $i < count($wishlist); $i++){
                array_push($wishlistProductsIds,$wishlist[$i]['product_id']);
            }
            $wishlistProducts = array();
            for($i = 0; $i < count($wishlistProductsIds); $i++) {
                $item = $this->model_catalog_product->getProduct($wishlistProductsIds[$i]);
                array_push($wishlistProducts,$item);
            }
            $json['data'] = $wishlistProducts;
	        

        } else {
            $json['status'] = false;
        }
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
}