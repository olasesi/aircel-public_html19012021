<?php 
class ControllerExtensionAccountPurpletreeMultivendorSellerpayment extends Controller{
	public function index(){
		
 $this->document->addScript('catalog/view/javascript/purpletree/jquery/datetimepicker/moment/moment.min.js'); 
 $this->document->addScript('catalog/view/javascript/purpletree/jquery/datetimepicker/moment/moment-with-locales.min.js'); 
 $this->document->addScript('catalog/view/javascript/purpletree/jquery/datetimepicker/bootstrap-datetimepicker.min.js'); 
 $this->document->addStyle('catalog/view/javascript/purpletree/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/sellerpayment', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}else{
			if(isset($store_detail['store_status']) && $store_detail['multi_store_id'] != $this->config->get('config_store_id')){	
						$this->response->redirect($this->url->link('account/account','', true));
				   }
		}
		
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		$this->load->language('purpletree_multivendor/sellerpayment');
			
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/purpletree_multivendor/sellerpayment');
		
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_payment'] = $this->language->get('text_payment');
		$data['text_trnasaction'] = $this->language->get('text_trnasaction');
		$data['text_amount'] = $this->language->get('text_amount');
		$data['text_payment_mode'] = $this->language->get('text_payment_mode');
		$data['text_status'] = $this->language->get('text_status');
		$data['text_created_at'] = $this->language->get('text_created_at');
		$data['text_empty'] = $this->language->get('text_empty');
		$data['entry_date_from'] = $this->language->get('entry_date_from');
		$data['entry_date_to'] = $this->language->get('entry_date_to');
		$data['button_filter'] = $this->language->get('button_filter');
		if (isset($this->request->get['filter_date_from'])) {
			$filter_date_from = $this->request->get['filter_date_from'];
		} else {
			$end_date = date('Y-m-d', strtotime("-30 days"));
			$filter_date_from = $end_date;
		}

		if (isset($this->request->get['filter_date_to'])) {
			$filter_date_to = $this->request->get['filter_date_to'];
		} else {
			$end_date = date('Y-m-d');
			$filter_date_to = $end_date;
		}
		$url = '';

		if (isset($this->request->get['filter_date_from'])) {
			$url .= '&filter_date_from=' . $this->request->get['filter_date_from'];
		}

		if (isset($this->request->get['filter_date_to'])) {
			$url .= '&filter_date_to=' . $this->request->get['filter_date_to'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/sellerpayment', '', true)
		);
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$seller_id = $this->customer->getId();
		
		$data['seller_payments'] = array();
		$filter_data = array(
			'filter_date_from'    => $filter_date_from,
			'filter_date_to' => $filter_date_to,
			'start'                => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                => $this->config->get('config_limit_admin'),
			'seller_id'				=>$seller_id
		);
		$seller_payments = $this->model_extension_purpletree_multivendor_sellerpayment->getPayments($filter_data);
		
		$total_payments = $this->model_extension_purpletree_multivendor_sellerpayment->getTotalPayments($filter_data);
		
		$curency = $this->session->data['currency'];
		
		$currency_detail = $this->model_extension_purpletree_multivendor_sellerpayment->getCurrencySymbol($curency);
		
		if($seller_payments){
			foreach($seller_payments as $seller_payment){
				$data['seller_payments'][] = array(
					'transaction_id' => $seller_payment['transaction_id'],
					'amount' => $this->currency->format($seller_payment['amount'], $currency_detail['code'], $currency_detail['value']),
					'payment_mode' => $seller_payment['payment_mode'],
					'status' => $seller_payment['status'],
					'created_at' => date($this->language->get('date_format_short'), strtotime($seller_payment['created_at']))
				);
			}
		}
		
		$pagination = new Pagination();
		$pagination->total = $total_payments;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/account/purpletree_multivendor/sellerpayment', 'page={page}', true);

		$data['pagination'] = $pagination->render();
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($total_payments) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_payments - $this->config->get('config_limit_admin'))) ? $total_payments : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_payments, ceil($total_payments / $this->config->get('config_limit_admin')));
		
		$data['filter_date_from'] = $filter_date_from;
		$data['filter_date_to'] = $filter_date_to;
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		 
		$this->response->setOutput($this->load->view('account/purpletree_multivendor/payment_list', $data));
	}
}
?>