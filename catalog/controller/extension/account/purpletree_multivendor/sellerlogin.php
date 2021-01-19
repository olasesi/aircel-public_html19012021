<?php
class ControllerExtensionAccountPurpletreeMultivendorSellerlogin extends Controller {
	private $error = array();

	public function index() {	
	
		 $data['loggedcus'] = '';
		 
		 if ($this->customer->isLogged()) {

			$data['loggedcus'] = $this->customer->getId();
			$this->load->model('extension/purpletree_multivendor/vendor');

			$store_detail = $this->model_extension_purpletree_multivendor_vendor->isSeller($this->customer->getId());				   
			if($store_detail){
				if($store_detail['is_removed']==1){
					$this->response->redirect($this->url->link(	'extension/account/purpletree_multivendor/sellerstore/becomeseller', '', true));
				} else {
					if($store_detail['store_status']==1){
						if($store_detail['multi_store_id']== $this->config->get('config_store_id')){
						    $this->response->redirect($this->url->link(	'extension/account/purpletree_multivendor/dashboardicons', '', true));
					    } else {
						   $this->response->redirect($this->url->link(	'account/account', '', true));
				     	}							
					} else {
						$this->response->redirect($this->url->link(	'account/account', '', true));
					}
				}
			}
							$this->response->redirect($this->url->link(	'extension/account/purpletree_multivendor/sellerregister', '', true));
		} 
		
		 $this->load->model('account/customer');

		//$this->load->language('account/login');
		//$this->load->language('account/ptsregister');
		$this->load->language('purpletree_multivendor/sellerregister');
		
		$this->document->setTitle($this->language->get('text_seller_login'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			// Unset guest
			unset($this->session->data['guest']);

			// Default Shipping Address
			$this->load->model('account/address');

			if ($this->config->get('config_tax_customer') == 'payment') {
				$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			if ($this->config->get('config_tax_customer') == 'shipping') {
				$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			// Wishlist
			if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
				$this->load->model('account/wishlist');

				foreach ($this->session->data['wishlist'] as $key => $product_id) {
					$this->model_account_wishlist->addWishlist($product_id);

					unset($this->session->data['wishlist'][$key]);
				}
			}

			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' =>$this->language->get('text_seller_login_page'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/sellerlogin', '', true)
		);

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('extension/account/purpletree_multivendor/sellerlogin', '', true);
		$data['sellerregister'] = $this->url->link('extension/account/purpletree_multivendor/sellerregister', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);

		if (isset($this->session->data['redirect'])) {
			$data['redirect'] = $this->session->data['redirect'];

			unset($this->session->data['redirect']);
		} else {
			$data['redirect'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}
		
		$data['heading_title'] = $this->language->get('text_seller_login');

		$data['text_new_customer'] = $this->language->get('text_new_customer');
		$data['text_register'] = $this->language->get('text_register');
		$data['text_register_account'] = $this->language->get('text_register_account');
		$data['text_returning_customer'] = $this->language->get('text_returning_customer');
		$data['text_i_am_returning_customer'] = $this->language->get('text_i_am_returning_customer');
		$data['text_forgotten'] = $this->language->get('text_forgotten');

		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_password'] = $this->language->get('entry_password');

		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_login'] = $this->language->get('button_login');
		
		$data['text_seller_login_page'] = $this->language->get('text_seller_login_page');
		$data['text_new_seller'] = $this->language->get('text_new_seller');
		$data['text_register_new'] = $this->language->get('text_register_new');
		$data['text_seller_login'] = $this->language->get('text_seller_login');
		$data['error_seller_not_found'] = $this->language->get('error_seller_not_found');
		$data['text_seller_register_page'] = $this->language->get('text_seller_register_page');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/purpletree_multivendor/sellerlogin', $data));
	}

	protected function validate() {;
		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
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
