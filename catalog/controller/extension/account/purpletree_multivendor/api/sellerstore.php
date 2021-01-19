<?php 
class ControllerExtensionAccountPurpletreeMultivendorApiSellerstore extends Controller{
	private $error = array(); 
	
	public function index(){
		//$this->checkPlugin();

		$this->load->language('purpletree_multivendor/api');
			$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (!$this->customer->isLogged()) {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_logged');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_approved');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json)); 
		}
		if(!$this->customer->validateSeller()) {		
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
		$this->load->language('purpletree_multivendor/sellerstore');
		$store_id = (isset($store_detail['id'])?$store_detail['id']:'');
		$this->load->model('extension/purpletree_multivendor/vendor');
		
		$store_detail = $this->customer->isSeller();
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$requestjson2 = file_get_contents('php://input');
			$requestjson1 = json_decode($requestjson2, true);
			parse_str($requestjson1['form'],$requestjson);
			$requestjson['store_country'] 				= $requestjson1['store_country'];
			$requestjson['store_state'] 				= $requestjson1['store_state'];
			$requestjson['store_shipping_order_type']   = $requestjson1['store_shipping_order_type'];
			$requestjson['store_shipping_type'] 		= $requestjson1['store_shipping_type'];
			$requestjson['store_live_chat_enable'] 		= $requestjson1['store_live_chat_enable'];
			if($this->validateForm($requestjson)) {
			   $this->model_extension_purpletree_multivendor_vendor->editStore($store_id, $requestjson,$file);
			   $this->session->data['success'] = 'Store Information saved successfully';
			   $json['status'] = 'success';
			   $json['message'] =  'Store Information saved successfully';
			}
		}

		if (isset($store_id)) {
			$json['data']['store_id'] = $store_id;
		} else {
			$json['data']['store_id'] = 0;
		}
		
		if (isset($this->session->data['success'])) {
			$json['message'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$json['message'] = '';
		}
		
		if (isset($this->error['store_name'])) {
			$json['messages']['store_name'] = $this->error['store_name'];
			$json['status'] = 'error';
		}
		
		if (isset($this->error['store_seo'])) {
			$json['messages']['store_seo'] = $this->error['store_seo'];
			$json['status'] = 'error';
		} 
		if (isset($this->error['error_file_upload'])) {
			$json['messages']['error_file_upload'] = $this->error['error_file_upload'];
			$json['status'] = 'error';
		}
		
		if (isset($this->error['store_email'])) {
			$json['messages']['store_email'] = $this->error['store_email'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_phone'])) {
			$json['messages']['store_phone'] = $this->error['store_phone'];
			$json['status'] = 'error';
			}
				
		if (isset($this->error['store_address'])) {
			$json['messages']['store_address'] = $this->error['store_address'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_city'])) {
			$json['messages']['store_city'] = $this->error['store_city'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_country'])) {
			$json['messages']['store_country'] = $this->error['store_country'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['error_storezone'])) {
			$json['messages']['error_storezone'] = $this->error['error_storezone'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_zipcode'])) {
			$json['messages']['store_zipcode'] = $this->error['store_zipcode'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_shipping'])) {
			$json['messages']['store_shipping'] = $this->error['store_shipping'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_return'])) {
			$json['messages']['store_return'] = $this->error['store_return'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_meta_keywords'])) {
			$json['messages']['store_meta_keywords'] = $this->error['store_meta_keywords'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_meta_description'])) {
			$json['messages']['store_meta_description'] = $this->error['store_meta_description'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_bank_details'])) {
			$json['messages']['store_bank_details'] = $this->error['store_bank_details'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_tin'])) {
			$json['messages']['store_tin'] = $this->error['store_tin'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_shipping_charge'])) {
			$json['messages']['store_shipping_charge'] = $this->error['store_shipping_charge'];
			$json['status'] = 'error';
			}
		if (isset($this->error['warning'])) {
			$json['message'] = $this->error['warning'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (isset($store_id) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$seller_info = $this->model_extension_purpletree_multivendor_vendor->getStore($store_id);
		}
		 
		if (!empty($seller_info)) {
			$json['data']['seller_id'] = $seller_info['seller_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['seller_id'] = $this->request->get['seller_id'];
		} else {
			$json['data']['seller_id'] = $this->customer->getId();
		}
		
		if (isset($requestjson['seller_name'])) { 
			$json['data']['seller_name'] = $requestjson['seller_name'];
		} elseif (!empty($seller_info)) { 
			$json['data']['seller_name'] = $seller_info['seller_name'];
		}
		
			$json['data']['module_purpletree_multivendor_allow_live_chat'] = '0';
			if(NULL !== $this->config->get('module_purpletree_multivendor_allow_live_chat')) {
			$json['data']['module_purpletree_multivendor_allow_live_chat'] = '1';
     if (isset($requestjson['store_live_chat_enable'])) { 
			$json['data']['store_live_chat_enable'] = $requestjson['store_live_chat_enable'];
		} elseif (!empty($seller_info) && $seller_info['store_live_chat_enable'] != '') { 
			$json['data']['store_live_chat_enable'] = $seller_info['store_live_chat_enable'];
		} else { 
			$json['data']['store_live_chat_enable'] = 0;
		}

		if (isset($requestjson['store_live_chat_code'])) { 
			$json['data']['store_live_chat_code'] = $requestjson['store_live_chat_code'];
		} elseif (!empty($seller_info) && $seller_info['store_live_chat_code'] != '') { 
			$json['data']['store_live_chat_code'] = $seller_info['store_live_chat_code'];	
		} else {
			$json['data']['store_live_chat_code'] = $seller_info['store_live_chat_code'];	
		}
			}
		if (isset($requestjson['store_seo'])) { 
			$json['data']['store_seo'] = $requestjson['store_seo'];
		} elseif (!empty($seller_info)) { 
			$json['data']['store_seo'] = $seller_info['store_seo'];
		} 
		
		if (isset($requestjson['store_name'])) {
			$json['data']['store_name'] = $requestjson['store_name'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_name'] = $seller_info['store_name'];
		} 
		

		if (isset($requestjson['store_email'])) {
			$json['data']['store_email'] = $requestjson['store_email'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_email'] = $seller_info['store_email'];
		} 
		
		if (isset($requestjson['store_phone'])) {
			$json['data']['store_phone'] = $requestjson['store_phone'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_phone'] = $seller_info['store_phone'];
		} 
		
		if (isset($requestjson['store_description'])) {
			$json['data']['store_description'] = $requestjson['store_description'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_description'] = $seller_info['store_description'];
		} 
		
		if (isset($requestjson['store_address'])) {
			$json['data']['store_address'] = $requestjson['store_address'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_address'] = $seller_info['store_address'];
		} 
				$this->load->model('localisation/country');
			$json['data']['countries'] = $this->model_localisation_country->getCountries();
			
		if (isset($requestjson['store_country'])) {
			$json['data']['store_country'] = $requestjson['store_country'];
		} elseif (!empty($seller_info) && $seller_info['store_country'] != '' && $seller_info['store_country'] != '0') {
			$json['data']['store_country'] = $seller_info['store_country'];
		} else {
			if(!empty($json['data']['countries'])) {
				foreach($json['data']['countries'] as $countryy) {
					$json['data']['store_country'] = $countryy['country_id'];
					break;
				}
			}
		}
		if($json['data']['store_country']) {
			$json['data']['country_zones'] = $this->getZoneFromCountryinternal($json['data']['store_country']);
		}
			
		if (isset($requestjson['store_state'])) {
			$json['data']['store_state'] = $requestjson['store_state'];
		} elseif (!empty($seller_info) && $seller_info['store_state'] != '' && $seller_info['store_state'] != '0') {
			$json['data']['store_state'] = $seller_info['store_state'];
		} else {
			if(isset($json['data']['country_zones']) && !empty($json['data']['country_zones'])) {
				foreach($json['data']['country_zones'] as $state) {
					$json['data']['store_state'] = $state['zone_id'];
					break;
				}
			}
		}
		
		if (isset($requestjson['store_city'])) {
			$json['data']['store_city'] = $requestjson['store_city'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_city'] = $seller_info['store_city'];
		} 
		
		if (isset($requestjson['store_zipcode'])) {
			$json['data']['store_zipcode'] = $requestjson['store_zipcode'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_zipcode'] = $seller_info['store_zipcode'];
		} 
		
		if (isset($requestjson['store_shipping_policy'])) {
			$json['data']['store_shipping_policy'] = $requestjson['store_shipping_policy'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_shipping_policy'] = $seller_info['store_shipping_policy'];
		} 
		
		if (isset($requestjson['store_return_policy'])) {
			$json['data']['store_return_policy'] = $requestjson['store_return_policy'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_return_policy'] = $seller_info['store_return_policy'];
		} 
		
		if (isset($requestjson['store_meta_keywords'])) {
			$json['data']['store_meta_keywords'] = $requestjson['store_meta_keywords'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_meta_keywords'] = $seller_info['store_meta_keywords'];
		} 
		
		if (isset($requestjson['store_meta_description'])) {
			$json['data']['store_meta_description'] = $requestjson['store_meta_description'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_meta_description'] = $seller_info['store_meta_descriptions'];
		} 
		
		if (isset($requestjson['store_bank_details'])) {
			$json['data']['store_bank_details'] = $requestjson['store_bank_details'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_bank_details'] = $seller_info['store_bank_details'];
		} 
		
		if (isset($requestjson['store_tin'])) {
			$json['data']['store_tin'] = $requestjson['store_tin'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_tin'] = $seller_info['store_tin'];
		} 
		if (isset($requestjson['store_shipping_type'])) {
			$json['data']['store_shipping_type'] = $requestjson['store_shipping_type'];
		} elseif (!empty($seller_info) && isset($seller_info['store_shipping_type']) && $seller_info['store_shipping_type'] != '') {
			$json['data']['store_shipping_type'] = $seller_info['store_shipping_type'];
		} else {
			$json['data']['store_shipping_type'] = 'pts_flat_rate_shipping';
		}	
       if (isset($requestjson['store_shipping_order_type'])) {
			$json['data']['store_shipping_order_type'] = $requestjson['store_shipping_order_type'];
		} elseif (!empty($seller_info) && isset($seller_info['store_shipping_order_type']) && $seller_info['store_shipping_order_type'] != '') {
			$json['data']['store_shipping_order_type'] = $seller_info['store_shipping_order_type'];
		} else {
			$json['data']['store_shipping_order_type'] = 'pts_product_wise';
		}				
		
		if (isset($requestjson['store_shipping_charge'])) {
			$json['data']['store_shipping_charge'] = $requestjson['store_shipping_charge'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_shipping_charge'] = $seller_info['store_shipping_charge'];
		} 
		
		if (isset($requestjson['store_status'])) {
			$json['data']['store_status'] = $requestjson['store_status'];
		} elseif (!empty($seller_info) && $seller_info['store_status'] != '') {
			$json['data']['store_status'] = $seller_info['store_status'];
		} else {
			$json['data']['store_status'] = 0;
		}
				
		if (isset($requestjson['store_logo'])) {
			$json['data']['store_logo'] = $requestjson['store_logo'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_logo'] = $seller_info['store_logo'];
		} 


		$this->load->model('tool/image');

		if (isset($requestjson['store_logo']) && is_file(DIR_IMAGE . $requestjson['store_logo'])) {
			$json['data']['thumb'] = $this->model_tool_image->resize($requestjson['store_logo'], 100, 100);
		} elseif (!empty($seller_info) && is_file(DIR_IMAGE . $seller_info['store_logo'])) {
			$json['data']['thumb'] = $this->model_tool_image->resize($seller_info['store_logo'], 100, 100);
		} else {
			$json['data']['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (isset($requestjson['store_banner'])) {
			$json['data']['store_banner'] = $requestjson['store_banner'];
		} elseif (!empty($seller_info)) {
			$json['data']['store_banner'] = $seller_info['store_banner'];
		} 

		$this->load->model('tool/image');

		if (isset($requestjson['store_banner']) && is_file(DIR_IMAGE . $requestjson['store_banner'])) {
			$json['data']['banner_thumb'] = $this->model_tool_image->resize($requestjson['store_banner'], 100, 100);
		} elseif (!empty($seller_info) && is_file(DIR_IMAGE . $seller_info['store_banner'])) {
			$json['data']['banner_thumb'] = $this->model_tool_image->resize($seller_info['store_banner'], 100, 100);
		} else {
			$json['data']['banner_thumb'] = $this->model_tool_image->resize('catalog/purpletree_banner.jpg', 100, 100);
		}
		
		$json['data']['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if(!empty($seller_info['document'])){
				$json['data']['upload_file_existing'] = $seller_info['document'];
				$json['data']['upload_file_existing_href'] = "admin/ptsseller/".$seller_info['document'];
			}      	
		// End download document file of store
		$json['status'] = 'success';
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}

	public function getZoneFromCountryinternal($country_id)
	{
		$this->load->model('localisation/zone');
		return $this->model_localisation_zone->getZonesByCountryId($country_id);
	}
	public function getZoneFromCountry()
	{
		//$this->checkPlugin();
		$this->load->language('purpletree_multivendor/api');
		$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (!$this->customer->isLogged()) {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_logged');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_approved');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json)); 
		}
		if(!$this->customer->validateSeller()) {		
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
		if($this->request->get['country_id']) {
			$this->load->model('localisation/zone');
			$json['zones'] = $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']);
			$json['status'] = 'success';
		}
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}
	public function downloadAttachment()
	{
		$this->load->language('purpletree_multivendor/api');
			$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (!$this->customer->isLogged()) {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_logged');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_approved');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json)); 
		}
		if(!$this->customer->validateSeller()) {		
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
		$file="ptsseller/".$this->request->get["document"]; //file location 
		
        if(file_exists($file)) {

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
		
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit();
	}
	}	
	
	public function becomeseller(){
			//$this->checkPlugin();
			$this->load->language('purpletree_multivendor/api');
			$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (!$this->customer->isLogged()) {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_logged');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}

		if(!$this->customer->validateSeller()) {		
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
	if (!$this->customer->isMobileApiCall()) {
			$json['message'] = $this->language->get('error_permission');
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$this->load->language('purpletree_multivendor/sellerstore');
		
		$this->load->model('extension/purpletree_multivendor/vendor');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$requestjson2 = file_get_contents('php://input');
			$requestjson = json_decode($requestjson2, true);
			if($this->validateSeller($requestjson)) {
			$store_id = $this->model_extension_purpletree_multivendor_vendor->becomeSeller($this->customer->getId(), $requestjson);
			if($store_id) {
						$json['status'] = 'success';
						$json['store_id'] = $store_id;
			////// Start register mail for seller////////////
		
			$this->load->language('mail/register');
		    $this->load->language('account/ptsregister');
			$data['text_welcome'] = sprintf($this->language->get('text_welcome'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$data['text_login'] = $this->language->get('text_login');
			$data['text_approval'] = $this->language->get('text_approval');
			$data['text_service'] = $this->language->get('text_service');
			$data['text_thanks'] = $this->language->get('text_thanks');
			  $this->load->model('account/customer'); 
               $this->load->model('account/customer_group');
				$datacust = $this->model_account_customer->getCustomer($this->customer->getId());
			if (isset($datacust['customer_group_id'])) {
				$customer_group_id = $datacust['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
			$data['text_admin'] ="";
			if($this->config->get('module_purpletree_multivendor_seller_approval') == 1){
				$data['text_admin'] = $this->language->get('text_admin');
			}
						
			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
			
			if ($customer_group_info) {
				$data['approval'] = $customer_group_info['approval'];
			} else {
				$data['approval'] = '';
			}
				
			//$data['login'] = $this->url->link('account/login', '', true);		
			$data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($datacust['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(sprintf($this->language->get('text_subject_seller'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
			$mail->setText($this->load->view('account/purpletree_multivendor/register_mail', $data));
			$mail->send();
		
		//////End register mail for seller////////////
		// Send to main admin email if new account email is enabled
		if (in_array('account', (array)$this->config->get('config_mail_alert'))) {

			$this->load->language('mail/register');
		    /////// Start alert mail for admin///////////
			
			$this->load->language('account/ptsregister');
			
			$data['text_signup_seller'] = $this->language->get('text_signup_seller');
			$data['text_firstname'] = $this->language->get('text_firstname');
			$data['text_lastname'] = $this->language->get('text_lastname');
			$data['text_customer_group'] = $this->language->get('text_customer_group');
			$data['text_email'] = $this->language->get('text_email');
			$data['text_telephone'] = $this->language->get('text_telephone');
			
			$data['firstname'] = $datacust['firstname'];
			$data['lastname'] = $datacust['lastname'];
			
			$this->load->model('account/customer_group');
			
			if (isset($datacust['customer_group_id'])) {
				$customer_group_id = $datacust['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
			
			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
			
			if ($customer_group_info) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '';
			}
			
			$data['email'] = $datacust['email'];
			$data['telephone'] = $datacust['telephone'];

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($this->language->get('text_new_Seller'), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('account/purpletree_multivendor/register_alertmail', $data));
			$mail->send();

			// Send to additional alert emails if new account email is enabled
			$emails1 = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails1 as $email1) {
				if (utf8_strlen($email1) > 0 && filter_var($email1, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email1);
					$mail->send();
				}
			}
			}
		  /////// End alert mail for admin///////////
			$this->load->language('purpletree_multivendor/sellerstore');
				if($this->config->get('module_purpletree_multivendor_seller_approval')){
					$json['message'] = $this->language->get('text_approval');
				} else {
					$json['message'] = $this->language->get('text_seller_success');
				}
			} else {
				$json['status'] = 'error';
				$json['message'] = "Something went wrong. Seller not created.";
			}
		}		
	}
		
		if (isset($this->error['seller_store'])) {
			$json['message'] = $this->error['seller_store'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (isset($this->error['error_warning'])) {
			$json['message'] = $this->error['error_warning'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}
	
	public function reseller(){
		
		 $json['status'] = 'error';
		$json['message'] = 'No Data';
		$this->load->language('purpletree_multivendor/sellerstore');
		if (!$this->customer->isMobileApiCall()) {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));

		}

		if (!$this->customer->isLogged()) {
			$json['status'] = 'error';
			$json['message'] = 'Seller Not Logged In';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
	
		}
		$this->load->model('extension/purpletree_multivendor/vendor');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
		
			$store_id = $this->model_extension_purpletree_multivendor_vendor->reseller($this->customer->getId(), $requestjson);
			if($store_id){
				if($this->config->get('module_purpletree_multivendor_seller_approval')){
					$this->session->data['success'] = $this->language->get('text_approval');
				} else {
					$this->session->data['success'] = $this->language->get('text_seller_success');
					$json['status'] = 'success';
				    $json['message'] = $this->session->data['success'];
			        $this->response->addHeader('Content-Type: application/json');
			         return $this->response->setOutput(json_encode($json));
				}
			} else {
			$json['status'] = 'success';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
			}
		}
		$json['status'] = 'success';
	    $this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		
	}
	public function storeview(){
		
		//$this->checkPlugin();

		$this->load->language('purpletree_multivendor/api');
			$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if(!$this->customer->validateSeller()) {		
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
		
		$this->load->model('extension/purpletree_multivendor/vendor');
		
		$this->load->model('extension/purpletree_multivendor/sellerproduct');

		/* if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else { */
			$filter = '';
		//}

		/* if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else { */
			$sort = 'p.sort_order';
		//}

		/* if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else { */
			$order = 'ASC';
		//}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		/* if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else { */
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		//}
		$category_id=0;
		/* if(!empty($this->request->get['category']))
		{
			$category_id=$this->request->get['category'];
		} */
		
		
		$json['data']['seller_products'] = array();
		
		//$json['data']['toatl_seller_products'] = array();
		
		if(isset($this->request->get['seller_store_id'])){
			$sellerstore = $this->request->get['seller_store_id'];
		} else {
			$sellerstore_d = $this->customer->isSeller();
			$sellerstore = $sellerstore_d['id'];
		}

		$store_detail = $this->model_extension_purpletree_multivendor_vendor->getStore($sellerstore);
	
		if($store_detail  and ($store_detail['store_status']==1)){
			$json['data']['store_rating'] = $this->model_extension_purpletree_multivendor_vendor->getStoreRating($store_detail['seller_id']);
			if(isset($json['data']['store_rating']['rating'])) {
				$json['data']['store_rating']['rating'] = (int)$json['data']['store_rating']['rating'];
			}
			
			//$json['data']['module_purpletree_multivendor_store_email'] = $this->config->get('module_purpletree_multivendor_store_email');
			//$json['data']['module_purpletree_multivendor_store_phone'] = $this->config->get('module_purpletree_multivendor_store_phone');
			//$json['data']['module_purpletree_multivendor_store_address'] = $this->config->get('module_purpletree_multivendor_store_address');
		
			$json['data']['store_name'] = $store_detail['store_name'];
			$json['data']['seller_name'] = $store_detail['seller_name'];
			$json['data']['seller_store_id'] = $sellerstore;
			$json['data']['store_email'] = $store_detail['store_email'];
			$json['data']['store_phone'] = $store_detail['store_phone'];
			//$json['data']['store_tin'] = $store_detail['store_tin'];
			//$json['data']['store_zipcode'] = $store_detail['store_zipcode'];
			//$json['data']['store_description'] = html_entity_decode($store_detail['store_description'], ENT_QUOTES, 'UTF-8');
			$json['data']['store_address'] = html_entity_decode($store_detail['store_address'], ENT_QUOTES, 'UTF-8');
			
			$json['data']['seller_review_status'] = $this->config->get('module_purpletree_multivendor_seller_review');
			
			$this->load->model('tool/image');
			
			/* if (is_file(DIR_IMAGE . $store_detail['store_logo'])) {
				$json['data']['store_logo'] = $this->model_tool_image->resize($store_detail['store_logo'], 150, 150);
			} else {
				$json['data']['store_logo'] = $this->model_tool_image->resize('no_image.png', 150, 150);
			} */
			
			if (is_file(DIR_IMAGE . $store_detail['store_banner'])) {
				$json['data']['store_banner'] = $this->model_tool_image->resize($store_detail['store_banner'], 900, 300);
			} else {
				$json['data']['store_banner'] = $this->model_tool_image->resize('catalog/purpletree_banner.jpg', 900, 300);
			}
		$json['data']['seller_id'] = $store_detail['seller_id'];
		$store_detail = array(
			'seller_id' 		 => $store_detail['seller_id'],
			'category_id'		 => $category_id,
			'filter_filter'      => $filter,
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit,
			'status'             => 1,
			'is_approved'        => 1
		);
			
		///$store_detail['status'] = 1;
		//$store_detail['is_approved'] = 1;
		$seller_products = $this->model_extension_purpletree_multivendor_sellerproduct->getSellerProducts($store_detail);
		$toatl_seller_products = $this->model_extension_purpletree_multivendor_sellerproduct->getTotalSellerProducts($store_detail);
		if($seller_products){
			foreach($seller_products as $seller_product){
				
				if (is_file(DIR_IMAGE . $seller_product['image'])) {
				$image = $this->model_tool_image->resize($seller_product['image'], 150, 150);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 150, 150);
				}
				
				$price = $this->currency->format($this->tax->calculate($seller_product['price'], $seller_product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				
				$product_specials = $this->model_extension_purpletree_multivendor_sellerproduct->getProductSpecials($seller_product['product_id']);
				
				$special = false;
				
				foreach ($product_specials  as $product_special) {
					if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
						$special = $this->currency->format($this->tax->calculate($product_special['price'], $seller_product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						break;
					}
				}
				
				$json['data']['seller_products'][] = array(
					'product_id' => $seller_product['product_id'],
					'name' => $seller_product['name'],
					'price' => $price,
					'image' => $image,
					'special'    => $special,
					'minimum'     => $seller_product['minimum'] > 0 ? $seller_product['minimum'] : 1,
					//'description' => utf8_substr(strip_tags(html_entity_decode($seller_product['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length'))  . '..'
				);
			}
		}
		
			//$json['data']['limits'] = array();

			/* $limits = array_unique(array($this->config->get($this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$json['data']['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'seller_store_id'  => $sellerstore
				);
			} */
			//$json['data']['pagination']['total'] = $toatl_seller_products;
			//$json['data']['pagination']['page'] = $page;
			//$json['data']['pagination']['limit'] = $limit;

			//$json['data']['results'] = sprintf($this->language->get('text_pagination'), ($toatl_seller_products) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($toatl_seller_products - $limit)) ? $toatl_seller_products : ((($page - 1) * $limit) + $limit), $toatl_seller_products, ceil($toatl_seller_products / $limit));

			$json['data']['sort'] = $sort;
			$json['data']['order'] = $order;
			$json['data']['limit'] = $limit;
			
			
		}
		    $json['status'] = 'success';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));		
	}
	
	public function storedesc() { 
	    $json['status'] = 'error';
	    $json['message'] = 'No Data';
		//$this->checkPlugin();

		$this->load->language('purpletree_multivendor/api');
			$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (!$this->customer->isLogged()) {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_logged');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_approved');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json)); 
		}
		if(!$this->customer->validateSeller()) {		
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
		$this->load->language('purpletree_multivendor/storeview');
		
		$this->load->model('extension/purpletree_multivendor/vendor');

		if (isset($this->request->get['seller_store_id'])) {
			$store_id = (int)$this->request->get['seller_store_id'];
		} else {
			$store_id = 0;
		}

		$store_info = $this->model_extension_purpletree_multivendor_vendor->getStore($store_id);

		if ($store_info) {
			if(isset($this->request->get['path'])) {
			if(( null !== $this->request->get['path']) && $this->request->get['path']=="shippingpolicy"){
				$json['data']['store_policy'] = base64_encode(html_entity_decode($store_info['store_shipping_policy'], ENT_QUOTES, 'UTF-8')) . "\n";
				$json['status'] = 'success';
				$json['message'] = '';
			} elseif((null !== $this->request->get['path']) && $this->request->get['path']=="returnpolicy"){				
				$json['data']['store_policy'] = base64_encode(html_entity_decode($store_info['store_return_policy'], ENT_QUOTES, 'UTF-8')) . "\n";
				$json['status'] = 'success';
				$json['message'] = '';
			} elseif((null !== $this->request->get['path']) && $this->request->get['path']=="aboutstore"){
				
				$json['data']['store_policy'] = base64_encode(html_entity_decode($store_info['store_description'], ENT_QUOTES, 'UTF-8')) . "\n";
				$json['status'] = 'success';
				$json['message'] = '';
			}
		} else {
			$json['status'] = 'error';
				$json['message'] = 'path must be defined';
		}
		}
        //$json['status'] = 'success';
		$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
	}
	
	private function validateSeller($requestjson){
		
		$this->load->model('extension/purpletree_multivendor/vendor');
		if(!$this->customer->validateSeller()) {
			$this->error['error_warning'] = $this->language->get('error_license');
		}
		
		if($requestjson['become_seller']){ 
		
		if ((utf8_strlen(trim($requestjson['seller_storename'])) < 5) || (utf8_strlen(trim($requestjson['seller_storename'])) > 50)) {
			$this->error['seller_store'] = $this->language->get('error_storename');
		}
		}
		return !$this->error;
	}
	
	private function validateForm($requestjson){
		
		$seller_seo = $this->model_extension_purpletree_multivendor_vendor->getStoreSeo($requestjson['store_seo']);
		
		$store_info = $this->model_extension_purpletree_multivendor_vendor->getStoreByEmail($requestjson['store_email']);

		$pattern = '/[\'\/~`\!@#\$%\^&\*\(\)\+=\{\}\[\]\|;:"\<\>,\.\?\\\ ]/';
		if (preg_match($pattern, $requestjson['store_seo'])==true) {
			$this->error['store_seo'] = $this->language->get('error_store_seo');
		} elseif ((utf8_strlen($requestjson['store_seo']) < 3) || (utf8_strlen(trim($requestjson['store_seo'])) > 150)) {
			$this->error['store_seo'] = $this->language->get('error_storeseoempty');
		} elseif(isset($store_info['id'])){
			$seller_seot = "seller_store_id=".$store_info['id'];
			if(isset($seller_seo['query'])){
				if($seller_seo['query']!=$seller_seot){
					$this->error['store_seo'] = $this->language->get('error_storeseo');
				}
			}
		}
		/* if(!empty($_FILES['upload_file']['name'])) {
		 $allowed_file=array('gif','png','jpg','pdf','doc','docx','zip');
                        $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($_FILES['upload_file']['name'], ENT_QUOTES, 'UTF-8')));
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
					 if(!in_array($extension,$allowed_file) ) {
						$this->error['error_file_upload'] = $this->language->get('error_supported_file');
					 }
		} */
		//echo $requestjson['store_name'];
		if ((utf8_strlen(trim($requestjson['store_name'])) < 5) || (utf8_strlen(trim($requestjson['store_name'])) > 50)) {
			//echo "........";
			$this->error['store_name'] = 'Store Name '.$this->language->get('error_storename');
		}
		$EMAIL_REGEX='/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/';
		
		if (preg_match($EMAIL_REGEX, $requestjson['store_email'])==false)	
		{
			$this->error['store_email'] = $this->language->get('error_storeemail');
		}
		 $store_detail = $this->customer->isSeller();
		
		if (!isset($store_info['id'])) {
			if ($store_info) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		} else { 
			if ($store_info && ($store_detail['id'] != $store_info['id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		} 
		if(trim($requestjson['store_phone']) < 1){
			if ((utf8_strlen(trim($requestjson['store_phone'])) < 10) || (utf8_strlen(trim($requestjson['store_phone'])) > 12)) {
					$this->error['store_phone'] = $this->language->get('error_storephone');
			}
		}
		
		if ((utf8_strlen(trim($requestjson['store_address'])) < 5) || (utf8_strlen(trim($requestjson['store_address'])) > 101)) {
			$this->error['store_address'] = $this->language->get('error_storeaddress');
		}
		
		if ((utf8_strlen(trim($requestjson['store_city'])) < 3) || (utf8_strlen(trim($requestjson['store_city'])) > 50)) {
			$this->error['store_city'] = $this->language->get('error_storecity');
		}
		
		if (empty($requestjson['store_country'])) {
			$this->error['store_country'] = $this->language->get('error_storecountry');
		}
		
		if (empty($requestjson['store_state'])) {
			$this->error['error_storezone'] = $this->language->get('error_storezone');
		}
		
		if(trim($requestjson['store_zipcode']) >= 1){
			if ((utf8_strlen(trim($requestjson['store_zipcode'])) < 3) || (utf8_strlen(trim($requestjson['store_zipcode'])) > 12)) {
				$this->error['store_zipcode'] = $this->language->get('error_storepostcode');
			}
		}
		
		if ((utf8_strlen(trim($requestjson['store_meta_keywords'])) =='') ) {
			$this->error['store_meta_keywords'] = $this->language->get('error_storemetakeywords');
		}
		
		if ((utf8_strlen(trim($requestjson['store_meta_description']))=='') ) {
			$this->error['store_meta_description'] = $this->language->get('error_storemetadescription');
		}
		
		if ((utf8_strlen(trim($requestjson['store_bank_details'])) =='') ) {
			$this->error['store_bank_details'] = $this->language->get('error_storebankdetail');
		}
		
		if(trim($requestjson['store_shipping_charge']) < 0){
			$this->error['store_shipping_charge'] = $this->language->get('error_storeshippingcharge');
		}
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
	}
	
	public function removeseller(){
		
		$this->load->language('purpletree_multivendor/api');
			$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (!$this->customer->isLogged()) {
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_logged');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$json['status'] = 'error';
			$json['message'] = $this->language->get('seller_not_approved');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json)); 
		}
		if(!$this->customer->validateSeller()) {		
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
	
		$this->load->language('purpletree_multivendor/storeview');
		
		$seller_id = $this->customer->getId();
		
		$this->load->model('extension/purpletree_multivendor/vendor');
		
		$result = $this->model_extension_purpletree_multivendor_vendor->removeSeller($seller_id);		
		
		    $json['status'] = 'success';
		    $json['message'] = $this->language->get('text_remove_account_success');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		
	}
	

	public function sellerreview() { 
			//$this->checkPlugin();
			$this->load->language('purpletree_multivendor/api');
			$json['status'] = 'error';
			$json['message'] = $this->language->get('no_data');
		 if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = 'error';
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		} 
		$json['data']['customer_id'] = $this->customer->getId();
		
		$this->load->language('purpletree_multivendor/sellerreview');
		
		$this->load->model('extension/purpletree_multivendor/sellerreview');
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateReview()) {
		
			$this->model_extension_purpletree_multivendor_sellerreview->addReview($requestjson);

			$this->session->data['success'] = $this->language->get('text_success');
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_limit_admin');
		}
		
		if (isset($this->request->get['seller_id'])) {
			$seller_id = (int)$this->request->get['seller_id'];
		} else {
			$seller_id = 0;
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		}
		
		if (isset($this->error['review_title'])) {
			$json['message'] = $this->error['review_title'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}

		if (isset($this->error['rating'])) {
			$json['message'] = $this->error['rating'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		if (isset($this->error['review_description'])) {
			$json['message'] = $this->error['review_description'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		if (isset($this->error['no_can_review'])) {
			$json['message'] = $this->error['no_can_review'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		if(isset($this->request->get['seller_id'])){
			$json['data']['seller_id'] = $seller_id;
			$this->load->model('extension/purpletree_multivendor/sellerreview');
		if(!$this->model_extension_purpletree_multivendor_sellerreview->canReview($datasend = array('seller_id' =>$seller_id,'customer_id' =>$this->customer->getId()))) {
				$json['data']['warning'] = $this->language->get('no_can_review');
		}
		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 20;
		} 	

			$filter_data = array(
				'start'              => ($page - 1) * 4,
				'limit'              => 4,
				'seller_id' 		=> $seller_id,
				'customer_id'		=> $json['data']['customer_id']
			);
				
			$review_total = $this->model_extension_purpletree_multivendor_sellerreview->getTotalSellerReview($filter_data);
			
			if (isset($requestjson['review_title'])) { 
				$json['data']['review_title'] = $requestjson['review_title'];
			} else { 
				$json['data']['review_title'] = '';
			}
			
			if (isset($requestjson['review_description'])) { 
				$json['data']['review_description'] = $requestjson['review_description'];
			} else { 
				$json['data']['review_description'] = '';
			}
			
			if (isset($requestjson['seller_id'])) { 
				$json['data']['seller_id'] = $requestjson['seller_id'];
			} else { 
				$json['data']['seller_id'] = (isset($this->request->get['seller_id'])?$this->request->get['seller_id']:'');
			}
			$results = $this->model_extension_purpletree_multivendor_sellerreview->getSellerReview($filter_data);
			
			$this->model_extension_purpletree_multivendor_sellerreview->checkReview($filter_data);
			

			$json['data']['reviews'] = array();
			if ($results) {
				foreach($results as $result){
					$json['data']['reviews'][] = array(
						'customer_name'     => $result['customer_name'],
						'seller_id'     => $result['seller_id'],
						'review_title'     => $result['review_title'],
						'review_description'       => nl2br($result['review_description']),
						'rating'     => (int)$result['rating'],
						'date_added' => date($this->language->get('date_format_short'), strtotime($result['created_at']))
					);
						

				}
			}
			//$json['data']['pagination']['total'] = $review_total;
			//$json['data']['pagination']['page'] = $page;
			//$json['data']['pagination']['limit'] = $limit;
			//$json['data']['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($review_total - $limit)) ? $review_total : ((($page - 1) * $limit) + $limit), $review_total, ceil($review_total / $limit));
		} else{
			if($this->customer->isSeller()){
			$seller_id = $this->customer->getId();
			$filter_data = array(
				'start'              => ($page - 1) * 4,
				'limit'              => 4,
				'seller_id' 		=> $seller_id,
				'shown'				=> '1'
			);
				
			$review_total = $this->model_extension_purpletree_multivendor_sellerreview->getTotalSellerReview($filter_data);
			$results = $this->model_extension_purpletree_multivendor_sellerreview->getSellerReview($filter_data);
			$json['data']['reviews'] = array();
			if ($results) {
				foreach($results as $result){
					$json['data']['reviews'][] = array(
						'customer_name'     => $result['customer_name'],
						'review_title'     => $result['review_title'],
						'review_description'       => nl2br($result['review_description']),
						'rating'     => (int)$result['rating'],
						'status'     => (($result['status'])?$this->language->get('text_approved'):$this->language->get('text_notapproved')),
						'date_added' => date($this->language->get('date_format_short'), strtotime($result['created_at']))
					);
				}
				$json['status'] = 'success';
			} else {
				$json['status'] = 'success';
				$json['message'] = 'No Data';				
			}
			//$json['data']['pagination']['total'] = $review_total;
			//$json['data']['pagination']['page'] = $page;
			//$json['data']['pagination']['limit'] = $limit;
			//$json['data']['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($review_total - $limit)) ? $review_total : ((($page - 1) * $limit) + $limit), $review_total, ceil($review_total / $limit));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
	}
	
	private function validateReview(){
		
		if ((utf8_strlen($requestjson['review_title']) < 3) ) {
			$this->error['review_title'] = $this->language->get('error_title');
		}
		
		if ((empty($requestjson['rating'])) ) {
			$this->error['rating'] = $this->language->get('error_rating');
		}
		
		if ((utf8_strlen($requestjson['review_description']) < 5) ) {
			$this->error['review_description'] = $this->language->get('error_description_length');
		} elseif(empty($requestjson['review_description'])){
			$this->error['review_description'] = $this->language->get('error_description');
		}
		
		$this->load->model('extension/purpletree_multivendor/sellerreview');
		
		if(!$this->model_extension_purpletree_multivendor_sellerreview->canReview($requestjson)) {
				$this->error['no_can_review'] = $this->language->get('no_can_review');
		}
		
		return !$this->error;
	}
		  private function checkPlugin() {
		header('Access-Control-Allow-Origin:*');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 286400');
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Headers: purpletreemultivendor,Purpletreemultivendor,PURPLETREEMULTIVENDOR,xocmerchantid,XOCMERCHANTID,Xocmerchantid,XOCSESSION,xocsession,Xocsession,content-type,CONTENT-TYPE,Content-Type');
	    $this->config->set('config_error_display', 0);
		
        $this->response->addHeader('Content-Type: application/json');
 
        $json = array("success"=>false);

        /*check rest api is enabled*/
        if (!$this->config->get('feed_rest_api_status')) {
            $json["error"] = 'API is disabled. Enable it!';
        }


        $headers = apache_request_headers();

        $key = "";

        if(isset($headers['xocmerchantid'])){
            $key = $headers['xocmerchantid'];
        }else if(isset($headers['XOCMERCHANTID'])) {
            $key = $headers['XOCMERCHANTID'];
        } else if(isset($headers['Xocmerchantid'])) {
            $key = $headers['Xocmerchantid'];
        }

        /*validate api security key*/
         if (($this->config->get('rest_api_key') && ($key != $this->config->get('rest_api_key'))) || $key == "") {
            $json["error"] = 'Invalid secret key';
        }

	if(isset($json["error"])){			
		echo(json_encode($json));
		exit;
	}
    }
}

if( !function_exists('apache_request_headers') ) {
    function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';

        foreach($_SERVER as $key => $val) {
            if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);

                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                    foreach($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }

                    $arh_key = implode('-', $rx_matches);
                }

                $arh[$arh_key] = $val;
            }
        }

        return( $arh );
    }
}