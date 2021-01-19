<?php 
class ControllerExtensionAccountPurpletreeMultivendorDashboardicons extends Controller {
	private $error = array();
	
	public function index(){
			
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		
		$store_detail = $this->customer->isSeller();
		//echo "ID: " . $store_detail; // . " " . $store_detail['store_status']; //$this->customer->getId();
		
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}else{
			        if(isset($store_detail['store_status']) && $store_detail[  'multi_store_id'] != $this->config->get('config_store_id')){	
						$this->response->redirect($this->url->link('account/account','', true));
				    }
		        }	
		
		$this->load->language('purpletree_multivendor/dashboard');
		$this->load->model('extension/purpletree_multivendor/dashboard');
		$data['seller_orders'] = array();
		

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];

			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}
		$url ='';
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title1'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
		);
		
		$data['totalorders'] = $this->model_extension_purpletree_multivendor_dashboard->getCountSeen($this->customer->getId());
		$data['totalenqures'] = $this->model_extension_purpletree_multivendor_dashboard->getCountSeen1($this->customer->getId());
		$data['isSeller'] = $this->customer->isSeller();
		$store_id = (isset($data['isSeller']['id'])?$data['isSeller']['id']:'');
		$this->load->model('localisation/order_status');
		$this->document->setTitle($this->language->get('heading_title1'));
		$data['heading_title']=$this->language->get('heading_title1');
		/////////////////////////
		$data['text_Manage_Downloads'] = $this->language->get('text_Manage_Downloads');
		$data['text_reviews'] = $this->language->get('text_reviews');


		$data['text_Users'] = $this->language->get('text_Users'); //13-12-2019 added code

		
		$data['text_Seller_Account'] = $this->language->get('text_Seller_Account');
		$data['text_Customer_Enquiries'] = $this->language->get('text_Customer_Enquiries');
		$data['text_Bulk_product_upload'] = $this->language->get('text_Bulk_product_upload');
		$data['text_Orders'] = $this->language->get('text_Orders');
		$data['text_Seller_Store'] = $this->language->get('text_Seller_Store');
		$data['text_Store_information'] = $this->language->get('text_Store_information');
		$data['text_View_Store'] = $this->language->get('text_View_Store');
		$data['text_Subscription_Invoice'] = $this->language->get('text_Subscription_Invoice');
		$data['text_Seller_Payments'] = $this->language->get('text_Seller_Payments');
		$data['text_Subscription_plan'] = $this->language->get('text_Subscription_plan');
		$data['text_Payments'] = $this->language->get('text_Payments');
		$data['text_Commisions'] = $this->language->get('text_Commisions');
		$data['text_shiiping_rate'] = $this->language->get('text_shiiping_rate');
		$data['text_Manage_products'] = $this->language->get('text_Manage_products');
		$data['text_seller_template_product'] = $this->language->get('text_seller_template_product');
		$data['text_blog_post'] = $this->language->get('text_blog_post');
		$data['text_blog_comment'] = $this->language->get('text_blog_comment');
		$data['text_commissioninvoice'] = $this->language->get('text_commissioninvoice');
		$data['text_sellerenquiries'] = $this->language->get('text_sellerenquiries');
		///////////////////
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		
				$data['sellerprofile'] = $this->url->link('extension/account/edit', '', true);
				$data['downloadsitems'] = $this->url->link('extension/account/purpletree_multivendor/downloads', '', true);
				$data['sellerstore'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore', '', true);
				$data['sellerproduct'] = $this->url->link('extension/account/purpletree_multivendor/sellerproduct', '', true);
				$data['sellerusers'] = $this->url->link('extension/account/purpletree_multivendor/sellerusers', '', true);
				$orderstatus = 0;
				$end_date_to = date('Y-m-d');
			    $end_date_from = date('Y-m-d', strtotime("-30 days"));				
				$data['sellerorder'] = $this->url->link('extension/account/purpletree_multivendor/sellerorder', 'filter_date_from='.$end_date_from.'&filter_date_to=' .$end_date_to.'', true);
				$data['sellercommission'] = $this->url->link('extension/account/purpletree_multivendor/sellercommission', '', true);
				$data['sellerpayment'] = $this->url->link('extension/account/purpletree_multivendor/sellerpayment', '', true);
				$data['removeseller'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/removeseller', '', true);
				$data['becomeseller'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/becomeseller', '', true);
				$data['sellerview'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/storeview&seller_store_id='.$store_id, '', true);
				$data['sellerreview'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/sellerreview', '', true);
				$data['sellerenquiry'] = $this->url->link('extension/account/purpletree_multivendor/sellercontact/sellercontactlist', '', true);
				$data['dashboardicons'] = $this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true);
				$data['dashboard'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
				$data['shipping'] = $this->url->link('extension/account/purpletree_multivendor/shipping', '', true);
				$data['bulkproductupload'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true);
				
				$data['purpletree_multivendor_subscription_plans'] = $this->config->get('module_purpletree_multivendor_subscription_plans');
				if($this->config->get('module_purpletree_multivendor_subscription_plans')==1){
				
				$data['subscriptionplan'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', '', true);
				
				$data['subscriptions'] = $this->url->link('extension/account/purpletree_multivendor/subscriptions', '', true);
				
				}
				$data['sellerblogpost'] = $this->url->link('extension/account/purpletree_multivendor/sellerblogpost', '', true);
				$data['sellerblogcomment'] = $this->url->link('extension/account/purpletree_multivendor/sellerblogcomment', '', true);
				$data['commissioninvoice'] = $this->url->link('extension/account/purpletree_multivendor/commissioninvoice', '', true);
				$data['module_purpletree_multivendor_seller_product_template'] = $this->config->get('module_purpletree_multivendor_seller_product_template');
				if($data['module_purpletree_multivendor_seller_product_template'] == 1){
					$data['seller_template_product'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true);
				}
				
				$data['sellerenquiries'] = $this->url->link('extension/account/purpletree_multivendor/sellerenquiries', '', true);
		$this->response->setOutput($this->load->view('account/purpletree_multivendor/dashboardicons', $data));
	}	

}
