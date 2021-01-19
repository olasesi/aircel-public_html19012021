<?php
header("Access-Control-Allow-Origin: *");
class ControllerApiLoginuser extends Controller {
   private $error = array();
  
   public function index() {
        header("Access-Control-Allow-Origin: *");
        $json = array();
        $this->load->language('account/login');
        $this->load->language('api/cart');
        $this->load->model('account/address');
        $this->load->model('account/order');
		$this->load->model('account/wishlist');
		$this->load->model('account/customer');
		$this->load->model('custom/cart');
		$this->load->model('custom/address');
		$this->load->model('catalog/product');
        $this->load->language('api/order');
		$this->load->model('custom/orders');
        
        $email =  $_POST['email'];
        $password = $_POST['password'];

    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
    	    
    	    $customerInfo = $this->model_account_customer->getCustomerByEmail($email);

    	    $json['status'] = true;
    	    $json['data'] = array(
	            'customer_id' => $customerInfo['customer_id'],
	            'firstname' => $customerInfo['firstname'],
	            'lastname' => $customerInfo['lastname'],
	            'email' => $customerInfo['email'],
	            'telephone' => $customerInfo['telephone'],
	            'newsletter' => $customerInfo['newsletter'],
	            'address_id' => $customerInfo['address_id'],
	            'status' => $customerInfo['status'],
    	        );
    	    
    	    if ($this->config->get('config_tax_customer') == 'shipping') {
				 $paymentaddressWithIds = $this->model_custom_address->getAddresses($customerInfo['customer_id']);
				 $paymentaddress = array();
				 $addressIds = array_keys($paymentaddressWithIds);
				 for($i = 0; $i < count($addressIds); $i++){
				     array_push($paymentaddress,$paymentaddressWithIds[$addressIds[$i]]);
				 }
                $json['data']['shipping_address'] = $paymentaddress;
			}

            // Wishlist
            $wishlist = $this->model_account_wishlist->getWishlist();
            $wishlistProductsIds = array();
            for( $i = 0; $i < count($wishlist); $i++){
                array_push($wishlistProductsIds,$wishlist[$i]['product_id']);
            }
            $wishlistProducts = array();
            for($i = 0; $i < count($wishlistProductsIds); $i++) {
                $item = $this->model_catalog_product->getProduct($wishlistProductsIds[$i]);
                array_push($wishlistProducts,$item);
            }
            $json['data']['wishlist'] = $wishlistProducts;

            // Products
			$products = $this->model_custom_cart->getProducts($customerInfo['customer_id']);
			$json['data']['cart'] = $products;
// 			$json['data']['orderhistory'] = null;
			
    //         $page = 1;
    
    		$results = $this->model_custom_orders->getOrders($customerInfo['customer_id']);
            $json['data']['orderhistory'] = $results;
    
    // 		foreach ($results as $result) {
    // 			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
    
    // 			$json['data']['orderhistory'] = array(
    // 				'order_id'   => $result['order_id'],
    // 				'name'       => $result['firstname'] . ' ' . $result['lastname'],
    // 				'status'     => $result['status'],
    // 				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
    // 				'products'   => ($product_total),
    // 				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
    // 				'view'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
    // 			);
    // 		}
    	} else {
    	     $json['status'] = false;
    	     $json['mesage'] = $this->error;
    	}
    	$this->response->addHeader('Content-Type: application/json');
    	$this->response->setOutput(json_encode($json));
      
   }
   
  
   
   protected function validate() {
		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

		if ($customer_info && !$customer_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		if (!$this->error) {
			if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($this->request->post['email']);
			} else {
				$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
			}
		}
        
		return !$this->error;
	}
   
}