<?php
class ControllerExtensionModulePurpletreeSellerpanel extends Controller {
	public function index() {
				   
				$data['purpletree_module_status'] =$this->config->get('module_purpletree_multivendor_status');
				$store_detail = $this->customer->isSeller();						
				if(isset($store_detail['store_status']) && $store_detail['multi_store_id'] == $this->config->get('config_store_id')){		 
		      $this->load->language('extension/module/purpletree_sellerpanel');

		      $data['heading_title'] = $this->language->get('heading_title');
		       $data['logged'] = $this->customer->isLogged();
		      $data['isSeller'] = $this->customer->isSeller();
			$data['module_purpletree_multivendor_status'] = $this->config->get('module_purpletree_multivendor_status');
			$data['module_purpletree_multivendor_become_seller'] = $this->config->get('module_purpletree_multivendor_become_seller');
		if ($this->config->get('module_purpletree_multivendor_status')) {
			$store_id = (isset($data['isSeller']['id'])?$data['isSeller']['id']:'');
				$data['text_sellerstore'] = $this->language->get('text_sellerstore');
				$data['text_dashboard_icon'] = $this->language->get('text_dashboard_icon');
				$data['text_downloads'] = $this->language->get('text_downloads');
				$data['text_sellerproduct'] = $this->language->get('text_sellerproduct');
				$data['text_seller_template_product'] = $this->language->get('text_seller_template_product');
				$data['text_sellerprofile'] = $this->language->get('text_sellerprofile');
				$data['text_sellerorder'] = $this->language->get('text_sellerorder');
				$data['text_sellercommission'] = $this->language->get('text_sellercommission');
				$data['text_seller_enquiries'] = $this->language->get('text_seller_enquiries');
				$data['text_removeseller'] = $this->language->get('text_removeseller');
				$data['text_becomeseller'] = $this->language->get('text_becomeseller');
				$data['text_sellerview'] = $this->language->get('text_sellerview');
				$data['text_approval'] = $this->language->get('text_approval');
				$data['text_sellerpayment'] = $this->language->get('text_sellerpayment');
				$data['text_sellerreview'] = $this->language->get('text_sellerreview');
				$data['text_sellerenquiry'] = $this->language->get('text_sellerenquiry');
				$data['text_shipping'] = $this->language->get('text_shipping');
				$data['text_bulkproductupload'] = $this->language->get('text_bulkproductupload');
				$data['text_dashboard'] = $this->language->get('text_dashboard');
				$data['text_selleroption'] = $this->language->get('text_selleroption');
				$data['text_subscription'] = $this->language->get('text_subscription');
				$data['text_subscriptions'] = $this->language->get('text_subscriptions');		
				$data['text_password'] = $this->language->get('text_password');
				$data['text_logout'] = $this->language->get('text_logout');				
				///BLOG///
				$data['text_blog_post'] = $this->language->get('text_blog_post');
				$data['text_blog_comment'] = $this->language->get('text_blog_comment');
				///BLOG///
				$data['text_commissioninvoice'] = $this->language->get('text_commissioninvoice');
				$data['sellerprofile'] = $this->url->link('account/edit', '', true);
				$data['downloadsitems'] = $this->url->link('extension/account/purpletree_multivendor/downloads', '', true);
				$data['sellerstore'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore', '', true);
				$data['dashboardicons'] = $this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true);
				$data['sellerproduct'] = $this->url->link('extension/account/purpletree_multivendor/sellerproduct', '', true);
				$data['module_purpletree_multivendor_seller_product_template'] = $this->config->get('module_purpletree_multivendor_seller_product_template');
				if($data['module_purpletree_multivendor_seller_product_template'] == 1){
				    $data['seller_template_product'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true);	
				}
				
				$data['sellerenquiries'] = $this->url->link('extension/account/purpletree_multivendor/sellerenquiries', '', true);
								
				$end_date_to = date('Y-m-d');
			    $end_date_from = date('Y-m-d', strtotime("-30 days"));				
				$data['sellerorder'] = $this->url->link('extension/account/purpletree_multivendor/sellerorder', 'filter_date_from='.$end_date_from.'&filter_date_to=' .$end_date_to.'', true);
				
				$data['sellercommission'] = $this->url->link('extension/account/purpletree_multivendor/sellercommission', '', true);
				$data['sellerpayment'] = $this->url->link('extension/account/purpletree_multivendor/sellerpayment', '', true);
				$data['removeseller'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/removeseller', '', true);
				$data['becomeseller'] = $this->url->link('extension/account/purpletree_multivendor/sellerregister', '', true);
				$data['sellerview'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/storeview&seller_store_id='.$store_id, '', true);
				$data['sellerreview'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/sellerreview', '', true);
				$data['bulkproductupload'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true);
				$data['shipping'] = $this->url->link('extension/account/purpletree_multivendor/shipping', '', true);
				$data['sellerenquiry'] = $this->url->link('extension/account/purpletree_multivendor/sellercontact/sellercontactlist', '', true);
				$data['dashboard'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
					
				if($this->config->get('module_purpletree_multivendor_subscription_plans')){
				$data['subscriptionplan'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', '', true);
				
				$data['subscriptions'] = $this->url->link('extension/account/purpletree_multivendor/subscriptions', '', true);
				}
				///BLOG///
				$data['sellerblogpost'] = $this->url->link('extension/account/purpletree_multivendor/sellerblogpost', '', true);
				$data['sellerblogcomment'] = $this->url->link('extension/account/purpletree_multivendor/sellerblogcomment', '', true);
				///BLOG///
				$data['commissioninvoice'] = $this->url->link('extension/account/purpletree_multivendor/commissioninvoice', '', true);
				////add password and logout menu////				
				if($this->config->get('module_purpletree_multivendor_hide_user_menu')){
				$data['logout'] = $this->url->link('account/logout', '', true);
                $data['password'] = $this->url->link('account/password', '', true);
				}
			    ////End add password and logout menu////
		}

		return $this->load->view('extension/module/purpletree_sellerpanel', $data);
	}
	}
}