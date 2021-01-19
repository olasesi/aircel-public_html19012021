<?php 
class ControllerExtensionAccountPurpletreeMultivendorApiLogin extends Controller{
	private $error = array();  
	
// 	public function index(){
// 		$this->load->language('purpletree_multivendor/api');
// 		$json['status'] = 'error';
// 	    $json['message'] = $this->language->get('no_data');
// 		if (!$this->customer->isMobileApiCall()) { 
// 			$json['status'] = 'error';
// 			$json['message'] = $this->language->get('error_permission');
// 			$this->response->addHeader('Content-Type: application/json');
// 			return $this->response->setOutput(json_encode($json));
// 		}
// // 		if (!$this->customer->isLogged()) {
// // 			$json['status'] = 'error';
// // 			$json['seller'] = '3';
// // // 			$json['message'] = $this->language->get('seller_not_logged');
// // 			$this->response->addHeader('Content-Type: application/json');
// // 			return $this->response->setOutput(json_encode($json));
// // 		} 
		
// // 		if ($this->config->get('module_purpletree_multivendor_status')) { } else {
// // 			$json['status'] = 'error';
// // 			$json['message'] = 'Purpletree Multivendor Disabled';
// // 			$this->response->addHeader('Content-Type: application/json');
// // 			return $this->response->setOutput(json_encode($json)); 
		
// // 		}
// 		if(!$this->customer->validateSeller()) {		
// 			$json['status'] = 'error';
// 			$json['message'] = $this->language->get('error_license');
// 			$this->response->addHeader('Content-Type: application/json');
// 			return $this->response->setOutput(json_encode($json));
// 		} 
// 		$this->load->model('extension/purpletree_multivendor/vendor');
// 		$store_id = $this->model_extension_purpletree_multivendor_vendor->getStoreId($this->customer->getId());
// 		$store_detail = $this->customer->isSeller();
// 		if(!empty($store_detail)){
// 			if(($store_id === $store_detail['id']) && $store_detail['store_status'] == '1'){	
// 				$json['message'] = 'Show Seller menu';
// 				$json['seller'] = '1';		
// 			} else {
// 				$json['seller'] = '2';		
// 				$json['message'] = 'Waiting for seller approval';
// 			}	
// 		} else {
// 			$json['seller'] = '0';		
// 			$json['message'] = 'Become a Seller';
// 		}
// 	
// 	}

    public function index() {	
		$this->load->model('account/customer');
		$this->load->language('purpletree_multivendor/sellerregister');
		$this->document->setTitle($this->language->get('text_seller_login'));
        $json['status'] = false;
        $json['message'] = 'Invalid email/password!';
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		    $isSeller = $this->model_extension_purpletree_multivendor_vendor->isSeller($this->customer->getId());
		    if($isSeller && $isSeller['store_status']){
		         $sellerDetail = $this->model_extension_purpletree_multivendor_vendor->getStoreDetail($this->customer->getId());
		         $json['status'] = true;
                 $json['message'] = 'Login success!';
                 $json['data']['storename'] = $sellerDetail['store_name'];
                 $json['data']['storeemail'] = $sellerDetail['store_email'];
                 $json['data']['storeaddress'] = $sellerDetail['store_address'];
                 $json['data']['storeid'] = $isSeller['id'];
                 $json['data']['customerid'] = $sellerDetail['seller_id'];
		    }else if(!$isSeller['store_status']){
		       $json['message'] = $this->language->get('seller_not_approved');
		    }
		}
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['sellerid']);
		$this->load->model('extension/purpletree_multivendor/vendor');
		if(!empty($customer_info['customer_id'])){
	        $store_detail = $this->model_extension_purpletree_multivendor_vendor->isSeller($customer_info['customer_id']);			 
			if(isset($store_detail['store_status']) && ($store_detail['multi_store_id'] != $this->config->get('config_store_id'))){
				
			    $this->error['warning'] = $this->language->get('error_seller_not_found');
			
		    }
		}		
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
?>