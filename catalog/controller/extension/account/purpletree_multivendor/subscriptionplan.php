<?php
class ControllerExtensionAccountPurpletreeMultivendorSubscriptionplan extends Controller {

	private $error = array();

		public function index() {
				if (!$this->customer->isLogged()) {
					$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
					$this->response->redirect($this->url->link('account/login', '', true));
				}

					$store_detail = $this->customer->isSeller();
				if(!isset($store_detail['store_status'])){
					$this->response->redirect($this->url->link('account/account', '', true));
				}else{
			        if(isset($store_detail['store_status']) && $store_detail[  'multi_store_id'] != $this->config->get('config_store_id')){	
						$this->response->redirect($this->url->link('account/account','', true));
				    }
		        }

				if(!$this->customer->validateSeller()) {
					$this->load->language('purpletree_multivendor/ptsmultivendor');
					$this->session->data['error_warning'] = $this->language->get('error_license');
					$this->response->redirect($this->url->link('account/account', '', true));
				}

				if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
					$this->response->redirect($this->url->link('account/account', '', true));
				}

					$this->load->language('purpletree_multivendor/subscriptionplan');
					$this->document->setTitle($this->language->get('heading_title'));
					$this->load->model('extension/purpletree_multivendor/subscriptionplan');	
					$this->getPlan();	
				}
			
		public function add() {
				$url="";
				if (!$this->customer->isLogged()) {
					$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
					$this->response->redirect($this->url->link('account/login', '', true));
				}

					$store_detail = $this->customer->isSeller();
				if(!isset($store_detail['store_status'])){
					$this->response->redirect($this->url->link('account/account', '', true));
				}
				if(!$this->customer->validateSeller()) {
					$this->load->language('purpletree_multivendor/ptsmultivendor');
					$this->session->data['error_warning'] = $this->language->get('error_license');
					$this->response->redirect($this->url->link('account/account', '', true));
				}
				
				if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
					$this->response->redirect($this->url->link('account/account', '', true));
				}
				
					$this->load->language('purpletree_multivendor/subscriptionplan');
					$this->document->setTitle($this->language->get('heading_title'));
					$this->load->model('extension/purpletree_multivendor/subscriptionplan');
					$data=array();
				// for new seller 
				if(isset($this->request->post['plan_id'])){ 
					$plan_id=$this->request->post['plan_id'];
					$seller_id=$this->customer->getId();
				}
					$startt_when =$this->request->post['s_date'];
					$s_date = $this->request->post['s_date'];
					$data['plan_id']=$plan_id;
					$data['seller_id']=$seller_id;
					$data['startt_when']=$startt_when;
					$current_plan=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlan($seller_id);
				if($startt_when == 1) {
					$current_plan_start_date=$this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlanByPlanId($seller_id,$plan_id);
					
					$current_plan_start_date1=$this->model_extension_purpletree_multivendor_subscriptionplan->getLastPlan($seller_id,$plan_id);
					
					$validity=$this->model_extension_purpletree_multivendor_subscriptionplan->validity($plan_id);
					
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				
				$current_plan_end_date=($current_plan_start_date1['new_end_date']!='0000-00-00 00:00:00')?date('m/d/Y H:i:s',strtotime($current_plan_start_date1['new_end_date'])):date('m/d/Y H:i:s', strtotime($current_plan_start_date1['start_date']. ' + '.$validity.' days'));
				
				} else {
				$current_plan_end_date=($current_plan_start_date1['end_date']!='0000-00-00 00:00:00')?date('m/d/Y H:i:s',strtotime($current_plan_start_date1['end_date'])):date('m/d/Y H:i:s', strtotime($current_plan_start_date1['start_date']. ' + '.$validity.' days'));	
				}
					
				$data['start_date'] =date('Y-m-d H:i:s',strtotime($current_plan_end_date));
					
					
					/* $data['start_date'] = ($current_plan['end_date']!='0000-00-00 00:00:00')?date('Y-m-d H:i:s',strtotime($current_plan['end_date'])):date('Y-m-d H:i:s', strtotime($current_plan['start_date']. ' + '.$current_plan['validity'].' days')); */
				} else {
					$data['start_date'] = date('Y-m-d H:i:s');
				}

					$data['current_date'] = date('Y-m-d H:i:s');
					$data['end_date']='';
					$old_invoice_id=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceId($seller_id);
					$result=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscribePlanInfo($plan_id);
					$currentplan=$this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlan($seller_id);
				if($this->config->get("module_purpletree_multivendor_tax_name")){
					$tax_name=$this->config->get("module_purpletree_multivendor_tax_name");		
				} else {
					$tax_name='';	
				}

				if($this->config->get("module_purpletree_multivendor_tax_value")){
					$tax=$this->config->get("module_purpletree_multivendor_tax_value");		
				} else {
					$tax=0;	
				}
					$current_invoice=$this->model_extension_purpletree_multivendor_subscriptionplan->getSellerCurrentPlan($seller_id);
					$curr_invoice=array();

				if(!empty($current_invoice)){
					foreach($current_invoice as $value){
						$curr_invoice[$value['code']]=$value['price'];
					}
				}

				if($this->config->get("module_purpletree_multivendor_joining_fees")){
					$joining_fee=$result['joining_fee'];
				} else {
					$joining_fee=0;	
				}
				
				if($this->config->get("module_purpletree_multivendor_subscription_price")){
					$subscription_price=$result['subscription_price'];	
				} else {
					$subscription_price=0;	
				}

				$data['totals']['plan']=array();
				$data['totals']['plan'][]=array(
					'sort_order'=>0,
					'code'=>'subscription_price',
					'title'=>'Subscription Price',
					'value'=>$subscription_price
				);
if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){		
if($startt_when!=1){			
				$data['totals']['plan'][]=array(
					'sort_order'=>1,
					'code'=>'joining_fee',
					'title'=>'Joining Fee',
					'value'=>$joining_fee
				);
} else {
	$joining_fee=0;
}
} else {
				$data['totals']['plan'][]=array(
					'sort_order'=>1,
					'code'=>'joining_fee',
					'title'=>'Joining Fee',
					'value'=>$joining_fee
				);	
}
				$a_joiningfee = $joining_fee;
if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				if(isset($currentplan)){				
					$a_joiningfee=$joining_fee-	$current_plan['joining_fee'];
					$data['totals']['plan'][]=array(
						'sort_order'=>2,
						'code'=>'adjustment_Joining_fee',
						'title'=>'Adjustment Joining fee',
						'value'=>$a_joiningfee
					);	
					$subscription_price = $subscription_price-$this->remindPrice($current_plan['start_date'],$current_plan['validity'],$current_plan['subscription_price'],$s_date);
					$data['totals']['plan'][]=array(
						'sort_order'=>3,
						'code'=>'adjustment_subscription_price',
						'title'=>'Adjustment Subscription Price',
						'value'=>$subscription_price
					);
					$previous_balance=0;

					if($subscription_price<0){
						$previous_balance = $subscription_price;
					}
				}
}
				$subscription_price=$a_joiningfee+$subscription_price ;
				$total_amount= $subscription_price;
				$cal_tax=($total_amount*$tax)/100;
				$data['totals']['plan'][]=array(
					'sort_order'=>4,
					'code'=>'tax',
					'title'=>$tax_name.' ('.$tax.'%)',
					'value'=>$cal_tax
				);

				$current_invo=0;
				if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				if(isset($currentplan)){
					if(isset($curr_invoice['previous_balance'])){
					$current_invo=$curr_invoice['previous_balance'];	
					}				
				}
				}
				$total=$total_amount+$cal_tax+$current_invo;
				$invoice_bal=0;
				if($total<0){
					$invoice_bal=$total;	
				} 
if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				$data['totals']['plan'][]=array(
					'sort_order'=>5,
					'code'=>'previous_balance',
					'title'=>'Previous Balance',
					'value'=>$invoice_bal
				 );
}
				$total=$total_amount+$cal_tax+$current_invo;
				//if plan free or grand total less then zero
				$data['vendor_invoice_status']=1;
				if($total<=0){
				$data['vendor_invoice_status']=2;
				}
				
				
				//end
				$invoice_mail=array();
				foreach($data['totals']['plan'] as $resultPlan){
					if($resultPlan['code']!='previous_balance'){
						 $invoice_mail['mail'][]=array(
							'title'=>$resultPlan['title'],
							'price'=>$resultPlan['value']
						);
					} else {
						$invoice_mail['mail'][]=array(
							'title'=>$resultPlan['title'],
							'price'=>$current_invo
						);
					}
				}				
				$start=date('d-m-Y H:i:s', strtotime($data['start_date']));
				$end=date('d-m-Y H:i:s', strtotime($data['start_date']. ' + '.$result['validity'].' days'));
				$customer = $this->model_extension_purpletree_multivendor_subscriptionplan->getCustomer($this->customer->getId());
				$message='';
				$message.='Seller Name- '.$customer['firstname'].' '.$customer['lastname'].'<br>';
				$message.='Email Id- '.$customer['email'].'<br>';
				$message.='Plan Name- '.$result['plan_name'].'<br>';
				$message.='No Of Product- '.$result['no_of_product'].'<br>';
				$message.='Validity- '.$result['validity'].'<br>';
				$message.='Start Date- '.$start.'<br>';
				$message.='End Date- '.$end.'<br>';
				foreach($invoice_mail['mail'] as $msg){
					$message.=$msg['title'].'- '.$this->currency->format($msg['price'], $this->session->data['currency']).'<br>';	
				}
				$message.='Grand Total- '.$total.'<br>';
		// end new seller 
				if (($this->request->server['REQUEST_METHOD'] == 'POST') /* && $this->validateForm() */) {

					$invoice_id=$this->model_extension_purpletree_multivendor_subscriptionplan->addSellerMultiplePlan($data);
					
					$sellerExist=$this->model_extension_purpletree_multivendor_subscriptionplan->SellerExist($this->customer->getId());
					$this->load->language('purpletree_multivendor/subscriptionplan');
					$email_subject= $this->language->get('email_subject');
					if(!$sellerExist){
						$sellerExist=$this->model_extension_purpletree_multivendor_subscriptionplan->addFirstSellerPlan($this->customer->getId());	
						$email_subject = $this->language->get('email_first_subject');
					}
					//if plan free or grand total less then zero
					if($total<=0){
					$this->model_extension_purpletree_multivendor_subscriptionplan->enableSellerSubscription($this->customer->getId());
					}
				//end
					// Mail 		
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
					$mail->setTo($customer['email']);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender(html_entity_decode('Seller Name', ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode(sprintf( $email_subject , $customer['firstname']), ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($message);
					$mail->send();
					//end mail
						// Mail 		
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
					//$mail->setTo($customer['email']);
					$mail->setTo($this->config->get('config_email'));
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender(html_entity_decode('Seller Name', ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode(sprintf( $email_subject , $customer['firstname']), ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($message);
					$mail->send();
					$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/subscriptionplan/invoice', '' . 'invoice_id=' . $invoice_id .'&old_invoice_id='.$old_invoice_id. $url, true));
				}
			$this->getplan();
		}
		public function addSellerPaymentComment() {

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('purpletree_multivendor/subscriptionplan');
		$this->document->setTitle($this->language->get('heading_title'));
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
		$this->response->redirect($this->url->link('account/account', '', true));
		}
		$this->load->model('extension/purpletree_multivendor/subscriptionplan');
			if (($this->request->server['REQUEST_METHOD'] == 'POST')  && $this->validateForm() ) {
			$invoice_id=$this->model_extension_purpletree_multivendor_subscriptionplan->addSellerPaymentHistory($this->request->post);
			$customer = $this->model_extension_purpletree_multivendor_subscriptionplan->getCustomer($this->customer->getId());
				$customer = $this->model_extension_purpletree_multivendor_subscriptionplan->getCustomer($this->customer->getId());
				$message='';
				$message.='Seller Name- '.$customer['firstname'].' '.$customer['lastname'].'<br>';
				$message.='Email Id- '.$customer['email'].'<br><br>';
				$message.='Comment-<br> '.$this->request->post['comment'].'<br>';
			// Mail 
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			$mail->setTo($customer['email'] );
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode('Seller Name', ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf( $this->language->get('email_comment_subject') ,  $customer['firstname']), ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($message);
			$mail->send();
			//end mail
			// Mail 
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			//$mail->setTo($customer['email'] );
			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode('Seller Name', ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf( $this->language->get('email_comment_subject') ,  $customer['firstname']), ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($message);
			$mail->send();
			//end mail
			$this->session->data['success'] = $this->language->get('text_success');
            $url ='';
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/subscriptionplan/invoice', '' . 'invoice_id=' . $this->request->post['invoice_id'] .'&old_invoice_id='.$this->request->get['invoice_id']. $url, true));
		}
		$this->getplan();
	}
		public function paymentOffline(){ 
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('purpletree_multivendor/subscriptionplan');
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
		$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->model('extension/purpletree_multivendor/subscriptionplan');
		$invoice_id=$this->request->get['invoice_id'];
		$old_invoice_id=$this->request->get['old_invoice_id'];

		$data=array();
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

			if (isset($this->error['comment'])) {
			$data['error_commnent'] = $this->error['comment'];
			} else {
			$data['error_commnent'] = '';
			}
			
			$url ='';
			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', $url, true)
			);

			$data['heading_offline_payment']= $this->language->get('heading_offline_payment');
			$data['column_enter_payment']= $this->language->get('column_enter_payment');
			$data['entry_enter_payment']= $this->language->get('column_enter_payment');
			$data['button_save_offline']= $this->language->get('column_enter_payment');

		$data['invoice_id']=$invoice_id;
			$data['action']=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan/addSellerPaymentComment', '' . 'invoice_id=' . $invoice_id .'&old_invoice_id='.$old_invoice_id. $url, true);
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/purpletree_multivendor/offline_payment_form', $data));

	}

		public function notify(){ 
		$this->load->model('extension/purpletree_multivendor/subscriptionplan');
		$logger = new Log('error.log');
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
	foreach ($raw_post_array as $keyval) {
		$keyval = explode ('=', $keyval);
		if (count($keyval) == 2)
			$myPost[$keyval[0]] = urldecode($keyval[1]);
	}
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc')) {
		$get_magic_quotes_exists = true;
	}
	
	foreach ($myPost as $key => $value) {
		if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
			$value = urlencode(stripslashes($value));
		} else {
			$value = urlencode($value);
		}
		$req .= "&$key=$value";
	}
	// Post IPN data back to PayPal to validate the IPN data is genuine
	// Without this step anyone can fake IPN data

	$ch = curl_init("https://www.paypal.com/cgi-bin/webscr");
	if ($ch == FALSE) {
		return FALSE;
	}
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

		// Set TCP timeout to 30 seconds

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		$res = curl_exec($ch);

	if (curl_errno($ch) != 0) // cURL error
		{
			$logger->write(date('[Y-m-d H:i e] ')."Can't connect to PayPal to validate IPN message: " . curl_error($ch));
		curl_close($ch);
		exit;
	} else {
			// Log the entire HTTP response if debug is switched on.
			$logger->write(date('[Y-m-d H:i e] ')."HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req ");
			$logger->write(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res");
			curl_close($ch);
	}

// Inspect IPN validation result and act accordingly
// Split response headers and payload, a better way for strcmp
	$payment_response = $res;
	$tokens = explode("\r\n\r\n", trim($res));
	$res = trim(end($tokens));
	if (strcmp ($res, "VERIFIED") == 0) {
	// assign posted variables to local variables
	foreach($_POST as $key=>$value) {
		$logger->write(date('[Y-m-d H:i e] ')."Paypal response for ".$key." is ".$value);
	}
	try {
		$payment_status = $_POST['payment_status'];
	if($payment_status == "Completed") {
		$status_id = 2;
	} else {
		$status_id = 1;
	}

	$pending_reason = "";
	$subsject = '';
	if(isset($_POST['transaction_subject']) && $_POST['transaction_subject'] != '') {
		 $subsject = ", Transaction Subject is ".$_POST['transaction_subject'];
	}

	if(isset($_POST['pending_reason']) && $_POST['pending_reason'] !='') {
	$pending_reason = ", Pending Reason is ".$_POST['pending_reason'];
	}

	$comment = "Payment Status is ".$_POST['payment_status'].", Verify Sign is ".$_POST['verify_sign']." ".$pending_reason.", IPN Track Id is ".$_POST['ipn_track_id'].$subsject;
	$txn_id = $_POST['txn_id'];
	$dataarraypaypal = array('invoice_id' => $_POST['custom'],
							 'status_id'  => $status_id,
							 'comment'  => $comment,
							'transaction_id'  => $txn_id
							);

	$this->model_extension_purpletree_multivendor_subscriptionplan->addSellerPaymentHistoryfrompaypal($dataarraypaypal);
	} catch(Exception $e){ 
		$logger->write("Something went wrong after payment from Paypal ".$e->getMessage()); 				

			}
// check whether the payment_status is Completed
	//$logger->write(date('[Y-m-d H:i e] '). "Verified IPN: $req ");
} else if (strcmp ($res, "INVALID") == 0) {
	// log for manual investigation
	// Add business logic here which deals with invalid IPN messages
	$logger->write(date('[Y-m-d H:i e] '). "Invalid IPN: $req");
}
	}
	
		
	public function invoice(){ 
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('purpletree_multivendor/subscriptionplan');
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->document->setTitle($this->language->get('heading_title'));
		if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
		$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->model('extension/purpletree_multivendor/subscriptionplan');
		if(isset($this->request->get['paymentfrom']) && $this->request->get['paymentfrom'] == 'paypal'){
			$this->session->data['error_warning'] = "Your Payment is being processed by Paypal. Please refresh or check your invoice after sometime.";
		}

		$invoice_id=$this->request->get['invoice_id'];
		$seller_id = $this->customer->getId();

		$old_invoice_id = $this->model_extension_purpletree_multivendor_subscriptionplan->getoldinvoiceId($seller_id,$invoice_id);

		$data=array();
		//paypal data
		$data['return_url'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan/invoice/', '&invoice_id='.$invoice_id.'&paymentfrom=paypal', true);
		$data['notify_url'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan/notify', '', true);
		$data['origina_seller_name'] = $this->model_extension_purpletree_multivendor_subscriptionplan->getsellernamefromsell($seller_id);
		
		$data['currency_currency'] = $this->session->data['currency'];
		$data['currency_currency1'] = (NULL != $this->config->get('module_purpletree_multivendor_paypal_currency'))?$this->config->get('module_purpletree_multivendor_paypal_currency'):$this->config->get('config_currency');
		
		$data['currency_currency'] = $this->session->data['currency'];
		$data['module_purpletree_multivendor_paypal_email'] = (NULL != $this->config->get('module_purpletree_multivendor_paypal_email') && $this->config->get('module_purpletree_multivendor_paypal_email') != '')?$this->config->get('module_purpletree_multivendor_paypal_email'):'';
		$data['config_email'] = (NULL != $this->config->get('config_email') && $this->config->get('config_email') != '')?$this->config->get('config_email'):'';

		//paypal data
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
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', $url, true)
			);

			$data['text_invoice_id']=$this->language->get('text_invoice_id	');	
			$data['text_admin_storename']=$this->language->get('text_admin_storename');	
			$data['text_telephone']=$this->language->get('text_telephone');	
			$data['text_email']=$this->language->get('text_email');	
			$data['text_website']=$this->language->get('text_website');	
			$data['text_seller_storename']=$this->language->get('text_seller_storename');	
			$data['text_seller_name']=$this->language->get('text_seller_name');	
			$data['column_start_date']=$this->language->get('column_start_date');	
			$data['column_end_date']=$this->language->get('column_end_date');	
			$data['text_invoice_number']=$this->language->get('text_invoice_number ');	
			$data['text_created_date']=$this->language->get('text_created_date');	
			$data['text_status']=$this->language->get('text_status');	
			$data['heading_payment_history']=$this->language->get('heading_payment_history');	
			$data['column_payment_mode']=$this->language->get('column_payment_mode');	
			$data['column_transaction_id']=$this->language->get('column_transaction_id');	
			$data['column_payment_date']=$this->language->get('column_payment_date');	
			$data['column_comment']=$this->language->get('column_comment');	
			$data['text_invoice_id']=$this->language->get('text_invoice_id');	
			$data['button_pay_offline']=$this->language->get('button_pay_offline');	
			$data['text_invoice_number']=$this->language->get('text_invoice_id');	
			$data['heading_make_payment']=$this->language->get('heading_make_payment');	
			$data['text_grand_total']=$this->language->get('text_grand_total');	
			//$data['module_purpletree_multivendor_paypal']=$this->language->get('module_purpletree_multivendor_paypal');

			$data['invoice_id']=$invoice_id;

			if(isset($invoice_id)){
			$data['invoice_data']=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlanId($invoice_id,$old_invoice_id);
			 }
			$data['invoice']=array();
			 foreach($data['invoice_data'] as $value){
				$data['invoice']['seller_id']= $value['seller_id'];
				$data['invoice']['plan_id']= $value['plan_id'];
				$data['invoice']['payment_mode']= $value['payment_mode'];
				$data['invoice']['status_id']= $value['status_id'];
				$data['invoice']['status_id_id']= $value['status_id_id'];
				$data['invoice']['created_date']= $value['created_date'];
				foreach($data['invoice_data']['invoice']['item'] as $items){
				$data['invoice']['item'][]=array(
			'title'=>$items['title'],
			'code'=>$items['code'],
				'price'=>$this->currency->format($items['price'], $this->session->data['currency'])
				);
				} 
			 } 

			 $fff1 = array();
			 foreach($data['invoice_data']['invoice']['item'] as $key => $value){
				$fff1[$value['code']] = $value['price'];
			 }

			 if(array_key_exists('adjustment_Joining_fee',$fff1)){
				   unset($fff1['joining_fee']);
				}



			 if(array_key_exists('adjustment_subscription_price',$fff1)){
				   unset($fff1['subscription_price']);
			 }

			 //$data['grand_total_no_currency'] = array_sum(array_values($fff1));
			 
			 $data['grand_total_no_currency'] = $this->currency->convert(array_sum(array_values($fff1)), $this->config->get('config_currency'), $this->session->data['currency']);
			 $data['grand_total_no_currency1'] = $this->currency->convert(array_sum(array_values($fff1)), $this->config->get('config_currency'), $data['currency_currency1']);
			 $data['customarray'] = serialize(array('a' => 'ab',
										  'b' => 'cd',
			 ));

			 $data['grand_total'] = $this->currency->format(array_sum(array_values($fff1)), $this->session->data['currency']);

			$seller_store=$this->model_extension_purpletree_multivendor_subscriptionplan->getStoreDetail($this->customer->getId());
			$this->load->model('extension/purpletree_multivendor/vendor');
	        $cus_seller_email  = $this->model_extension_purpletree_multivendor_vendor->getCustomerEmailId($this->customer->getId());

				$data['store_info']=array(
						'name' => $seller_store['store_name'],
						'address'=> $seller_store['store_address'],
						'email' =>$cus_seller_email,
						'telephone' => $seller_store['store_phone'],
						'city' => $seller_store['store_city'],
						'state' => $seller_store['store_state'],
						'zip' => $seller_store['store_zipcode'],
						'country' => $seller_store['store_country'],
						'fax' => ''
						);

					

					$data['admin_info']=array(
						'name' => $this->config->get('config_name'),
						'address' => $this->config->get('config_address'),
						'email' => $this->config->get('config_email'),
						'telephone' => $this->config->get('config_telephone'),
						'fax' => $this->config->get('config_fax'),
						'url' => $this->config->get('config_url')
						);

						

			if(isset($data['invoice_data']['invoice']['plan_id'])){
				$result=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscribePlanInfo($data['invoice_data']['invoice']['plan_id']);
			}
			

			if(!empty($result)){
		$pts_date= $this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlanByPlanId1($invoice_id);		
				
					$data['newplan'] = array(
						'plan_id'        => $result['plan_id'],
						'plan_name'        => $result['plan_name'],
						'plan_description'  => html_entity_decode($result['plan_description'], ENT_QUOTES, 'UTF-8'),
					'plan_short_description'  => strip_tags(html_entity_decode($result['plan_short_description'], ENT_QUOTES, 'UTF-8')),
						'no_of_product'  => $result['no_of_product'],
						'joining_fee'  => $this->currency->format($result['joining_fee'], $this->session->data['currency']),

						'subscription_price'  => $this->currency->format($result['subscription_price'], $this->session->data['currency']),
						'validity'  => $result['validity'],
						'start_date'  => date('d/m/Y H:i:s',strtotime($pts_date['start_date'])),
						'end_date'        => date('d/m/Y H:i:s', strtotime($pts_date['start_date']. ' + '.$result['validity'].' days')),

						'subscribe'        =>$this->url->link('extension/account/purpletree_multivendor/dashboard', '' . 'plan_id=' . $result['plan_id'] . $url, true)

					);
				}
		$data['payment_history']=array();			
		$data['payment_history']=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceHistory($invoice_id);	
			$data['payment_offline']=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan/paymentOffline', '' . 'invoice_id=' . $invoice_id .'&old_invoice_id='.$old_invoice_id. $url, true);

			$data['payment_online']=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan/paymentOffline', '' . 'invoice_id=' . $invoice_id . $url, true);
			
			$data['print_invoice']=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan/print_invoice', '' . 'invoice_id=' . $invoice_id . $url, true);

            $data['text_no_results'] = $this->language->get('text_no_results');
			$data['text_address'] = $this->language->get('text_address');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/purpletree_multivendor/seller_subscription_plan_invoice', $data));
	}	

		public function print_invoice(){ 
		
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('purpletree_multivendor/subscriptionplan');
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->document->setTitle($this->language->get('heading_title'));
		if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
		$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->model('extension/purpletree_multivendor/subscriptionplan');
		if(isset($this->request->get['paymentfrom']) && $this->request->get['paymentfrom'] == 'paypal'){
			$this->session->data['error_warning'] = "Your Payment is being processed by Paypal. Please refresh or check your invoice after sometime.";
		}

		$invoice_id=$this->request->get['invoice_id'];
		$seller_id = $this->customer->getId();

		$old_invoice_id = $this->model_extension_purpletree_multivendor_subscriptionplan->getoldinvoiceId($seller_id,$invoice_id);

		$data=array();
		//paypal data
		$data['return_url'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan/invoice/', '&invoice_id='.$invoice_id.'&paymentfrom=paypal', true);
		$data['notify_url'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan/notify', '', true);
		$data['origina_seller_name'] = $this->model_extension_purpletree_multivendor_subscriptionplan->getsellernamefromsell($seller_id);
		$data['currency_currency'] = $this->session->data['currency'];
		$data['purpletree_multivendor_paypal'] = (NULL != $this->config->get('module_purpletree_multivendor_paypal') && $this->config->get('module_purpletree_multivendor_paypal') != '')?$this->config->get('module_purpletree_multivendor_paypal'):'';
		$data['config_email'] = (NULL != $this->config->get('config_email') && $this->config->get('config_email') != '')?$this->config->get('config_email'):'';

		//paypal data
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
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', $url, true)
			);

			$data['text_invoice_id']=$this->language->get('text_invoice_id	');	
			$data['text_admin_storename']=$this->language->get('text_admin_storename');	
			$data['text_telephone']=$this->language->get('text_telephone');	
			$data['text_email']=$this->language->get('text_email');	
			$data['text_website']=$this->language->get('text_website');	
			$data['text_seller_storename']=$this->language->get('text_seller_storename');	
			$data['text_seller_name']=$this->language->get('text_seller_name');	
			$data['column_start_date']=$this->language->get('column_start_date');	
			$data['column_end_date']=$this->language->get('column_end_date');	
			$data['text_invoice_number']=$this->language->get('text_invoice_number ');	
			$data['text_created_date']=$this->language->get('text_created_date');	
			$data['text_status']=$this->language->get('text_status');	
			$data['heading_payment_history']=$this->language->get('heading_payment_history');	
			$data['column_payment_mode']=$this->language->get('column_payment_mode');	
			$data['column_transaction_id']=$this->language->get('column_transaction_id');	
			$data['column_payment_date']=$this->language->get('column_payment_date');	
			$data['column_comment']=$this->language->get('column_comment');	
			$data['text_invoice_id']=$this->language->get('text_invoice_id');	
			$data['button_pay_offline']=$this->language->get('button_pay_offline');	
			$data['text_invoice_number']=$this->language->get('text_invoice_id');	
			$data['heading_make_payment']=$this->language->get('heading_make_payment');	
			$data['text_grand_total']=$this->language->get('text_grand_total');	
			//$data['module_purpletree_multivendor_paypal']=$this->language->get('module_purpletree_multivendor_paypal');

			$data['invoice_id']=$invoice_id;

			if(isset($invoice_id)){
			$data['invoice_data']=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlanId($invoice_id,$old_invoice_id);
			 }
			$data['invoice']=array();
			 foreach($data['invoice_data'] as $value){
				$data['invoice']['seller_id']= $value['seller_id'];
				$data['invoice']['plan_id']= $value['plan_id'];
				$data['invoice']['payment_mode']= $value['payment_mode'];
				$data['invoice']['status_id']= $value['status_id'];
				$data['invoice']['status_id_id']= $value['status_id_id'];
				$data['invoice']['created_date']= $value['created_date'];
				foreach($data['invoice_data']['invoice']['item'] as $items){
				$data['invoice']['item'][]=array(
			'title'=>$items['title'],
			'code'=>$items['code'],
				'price'=>$this->currency->format($items['price'], $this->session->data['currency'])
				);
				} 
			 } 

			 $fff1 = array();
			 foreach($data['invoice_data']['invoice']['item'] as $key => $value){
				$fff1[$value['code']] = $value['price'];
			 }

			 if(array_key_exists('adjustment_Joining_fee',$fff1)){
				   unset($fff1['joining_fee']);
				}



			 if(array_key_exists('adjustment_subscription_price',$fff1)){
				   unset($fff1['subscription_price']);
			 }

			 $data['grand_total_no_currency'] = array_sum(array_values($fff1));
			 $data['customarray'] = serialize(array('a' => 'ab',
										  'b' => 'cd',
			 ));

			 $data['grand_total'] = $this->currency->format(array_sum(array_values($fff1)), $this->session->data['currency']);

			$seller_store=$this->model_extension_purpletree_multivendor_subscriptionplan->getStoreDetail($this->customer->getId());
			$this->load->model('extension/purpletree_multivendor/vendor');
	        $cus_seller_email  = $this->model_extension_purpletree_multivendor_vendor->getCustomerEmailId($this->customer->getId());

				$data['store_info']=array(
						'name' => $seller_store['store_name'],
						'address'=> $seller_store['store_address'],
						'email' => $cus_seller_email,
						'telephone' => $seller_store['store_phone'],
						'city' => $seller_store['store_city'],
						'state' => $seller_store['store_state'],
						'zip' => $seller_store['store_zipcode'],
						'country' => $seller_store['store_country'],
						'fax' => ''
						);

					

					$data['admin_info']=array(
						'name' => $this->config->get('config_name'),
						'address' => $this->config->get('config_address'),
						'email' => $this->config->get('config_email'),
						'telephone' => $this->config->get('config_telephone'),
						'fax' => $this->config->get('config_fax'),
						'url' => $this->config->get('config_url')
						);

						

			if(isset($data['invoice_data']['invoice']['plan_id'])){
				$result=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscribePlanInfo($data['invoice_data']['invoice']['plan_id']);
			}

			

			if(!empty($result)){
				$pts_date= $this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlanByPlanId1($invoice_id);	
				
					$data['newplan'] = array(
						'plan_id'        => $result['plan_id'],
						'plan_name'        => $result['plan_name'],
						'plan_description'  => html_entity_decode($result['plan_description'], ENT_QUOTES, 'UTF-8'),
					'plan_short_description'  => strip_tags(html_entity_decode($result['plan_short_description'], ENT_QUOTES, 'UTF-8')),
						'no_of_product'  => $result['no_of_product'],
						'joining_fee'  => $this->currency->format($result['joining_fee'], $this->session->data['currency']),

						'subscription_price'  => $this->currency->format($result['subscription_price'], $this->session->data['currency']),

						'validity'  => $result['validity'],
						'start_date'  => date('d/m/Y',strtotime($pts_date['start_date'])),
						'end_date'        => date('d/m/Y', strtotime($pts_date['start_date']. ' + '.$result['validity'].' days')),
						'subscribe'        =>$this->url->link('extension/account/purpletree_multivendor/dashboard', '' . 'plan_id=' . $result['plan_id'] . $url, true)
					);
				}
				
		$data['payment_history']=array();			
		$data['payment_history']=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceHistory($invoice_id);	
			$data['payment_offline']=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan/paymentOffline', '' . 'invoice_id=' . $invoice_id .'&old_invoice_id='.$old_invoice_id. $url, true);

			$data['payment_online']=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan/paymentOffline', '' . 'invoice_id=' . $invoice_id . $url, true);

            $data['text_no_results'] = $this->language->get('text_no_results');
			$data['text_address'] = $this->language->get('text_address');
			
			$data['HTTPS_SERVER'] = HTTPS_SERVER;
			$this->response->setOutput($this->load->view('account/purpletree_multivendor/print_subscription_plan_invoice', $data));
	}
	
	public function subscribe(){ 

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}
		if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
		$this->response->redirect($this->url->link('account/account', '', true));
		}

		if(!isset($this->request->get['plan_id'])){
		$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/subscriptionplan', '', true));	
		}
		if(!isset($this->request->get['status'])){
		$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/subscriptionplan', '', true));	
		}

		$this->load->language('purpletree_multivendor/subscriptionplan');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/purpletree_multivendor/subscriptionplan');

	if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){	
	$data['multiplePlan']=1;
	} 
		
		//$invoice_status=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceStatus($this->customer->getId());
		$invoice_status=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlanInvoiceStatus($this->customer->getId(),$this->request->get['plan_id']);
		if(isset($invoice_status)){
			if($invoice_status!=2){
			$this->session->data['error_warning'] = 'Subscription Plan Pending';
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/subscriptionplan', '', true));
			}
			} 
		if(isset($this->request->get['plan_id'])){
		$plan_id=$this->request->get['plan_id'];	
		}

		if(isset($this->request->get['status'])){
		$data['pts_plan_status']=$this->request->get['status'];	
		$pts_plan_status=$this->request->get['status'];	
		
		}


	 	if(isset($this->request->get['s_date'])){
		$s_date=$this->request->get['s_date'];		
		}else {
		$s_date='0';		
		} 

		$seller_id=$this->customer->getId();
		$data=array();
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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', $url, true)
		);

	// language

		$data['heading_create_plan_confirmation']=$this->language->get('heading_create_plan_confirmation');
		$data['column_new_plan']=$this->language->get('column_new_plan');
		$data['button_view_description']=$this->language->get('button_view_description');
		$data['column_description']=$this->language->get('column_description');
		$data['column_start_date']=$this->language->get('column_start_date');
		$data['column_end_date']=$this->language->get('column_end_date');
		$data['column_validity']=$this->language->get('column_validity');
		$data['column_subscription_price']=$this->language->get('column_subscription_price');
		$data['text_start_now']=$this->language->get('text_start_now');
		$data['text_start_at_end']=$this->language->get('text_start_at_end');
		$data['button_save_generate_invoice']=$this->language->get('button_save_generate_invoice');
		$data['heading_current_plan']=$this->language->get('heading_current_plan');

		$currentplan=$this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlan($seller_id);

			if(!empty($currentplan)){
			$data['currentplan'] = array(
			'plan_name'        => $currentplan['plan_name'],
			'subscription_price'        => $this->currency->format($currentplan['subscription_price'], $this->session->data['currency']),
			'plan_description'  => html_entity_decode($currentplan['plan_description'], ENT_QUOTES, 'UTF-8'),
			'status'        => ($currentplan['status'])?$this->language->get('text_enabled'):$this->language->get('text_disabled'),
			'start_date'        => ($currentplan['start_date']!='0000-00-00 00:00:00')?date('d/m/Y H:i:s',strtotime($currentplan['start_date'])):'',
			'end_date'        => ($currentplan['end_date']!='0000-00-00 00:00:00')?date('d/m/Y H:i:s',strtotime($currentplan['end_date'])):date('d/m/Y H:i:s', strtotime($currentplan['start_date']. ' + '.$currentplan['validity'].' days')),
			'validity'        => $currentplan['validity']
			);
			} 

			$result=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscribePlanInfo($plan_id);

			$current_plan=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlan($seller_id);

			if($this->config->get("module_purpletree_multivendor_tax_name")){
			$tax_name=$this->config->get("module_purpletree_multivendor_tax_name");		
			} else {
			$tax_name='';	
			}

			if($this->config->get("module_purpletree_multivendor_tax_value")){
			$tax=$this->config->get("module_purpletree_multivendor_tax_value");		
			} else {
			$tax=0;	
			}
			$current_invoice=$this->model_extension_purpletree_multivendor_subscriptionplan->getSellerCurrentPlan($seller_id);
			$curr_invoice=array();
			if(!empty($current_invoice)){
			foreach($current_invoice as $value){
			$curr_invoice[$value['code']]=$value['price'];
			}
			}
			if($this->config->get("module_purpletree_multivendor_joining_fees")){
			$joining_fee=$result['joining_fee'];
			} else {
			$joining_fee=0;	
			}

			if($this->config->get("module_purpletree_multivendor_subscription_price")){
			$subscription_price=$result['subscription_price'];	
			} else {
			$subscription_price=0;	
			}
			$data['totals']['plan']=array();
			$data['subscription_price']=$this->currency->format($subscription_price, $this->session->data['currency']);
	if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){		
			if($pts_plan_status!='renew'){
			$data['totals']['plan'][]=array(
			'sort_order'=>1,
			'code'=>'joining_fee',
			'title'=>'Joining Fee',
			'value'=>$this->currency->format($joining_fee, $this->session->data['currency'])
			);
			} else {
			$joining_fee=0;	
			}
	} else {
		
			$data['totals']['plan'][]=array(
			'sort_order'=>1,
			'code'=>'joining_fee',
			'title'=>'Joining Fee',
			'value'=>$this->currency->format($joining_fee, $this->session->data['currency'])
			);
	}
			  $a_joiningfee = $joining_fee;
			if(isset($currentplan)){
			if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
			$a_joiningfee=$joining_fee-	$current_plan['joining_fee'];
			$data['totals']['plan'][]=array(
			'code'=>'adjustment_Joining_fee',
			'title'=>'Adjustment Joining fee',
			'value'=>$this->currency->format($a_joiningfee, $this->session->data['currency'])
			);	
			}	
			
		
		if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
		$data['planid']=$this->model_extension_purpletree_multivendor_subscriptionplan->sellerMultiplePlanId($this->customer->getId());
		} else {
			$data['planid']=$this->model_extension_purpletree_multivendor_subscriptionplan->sellerPlanId($this->customer->getId());
		}
		if(!empty($result)){			
				$data['newplan'] = array(
				'plan_id'        => $result['plan_id']
				);
		}

	if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
		$planids=array();
	if(!empty($data['planid'])){
		foreach($data['planid'] as $ke=>$va){
		$planids[]=$va['plan_id'];	
		}
	}
		if(in_array($data['newplan']['plan_id'],$planids)){
		$data['subscriptions'] = 1;
		$s_date = 1;
		}
	} else {
		if($data['planid'] == $data['newplan']['plan_id']){
		$data['subscriptions'] = 1;
		$s_date = 1;
		}	
	}
		if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
			$subscription_price=$subscription_price;
			$data['totals']['plan'][]=array(
			'code'=>'subscription_price',
			'title'=>'Subscription Price',
			'value'=>$this->currency->format($subscription_price, $this->session->data['currency'])
			);	
			
		} else {
			$subscription_price=$subscription_price-$this->remindPrice($current_plan['start_date'],$current_plan['validity'],$current_plan['subscription_price'],$s_date);
			$data['totals']['plan'][]=array(
			'code'=>'adjustment_subscription_price',
			'title'=>'Adjustment Subscription Price',
			'value'=>$this->currency->format($subscription_price, $this->session->data['currency'])
			);
		}


			$previous_balance=0;
				if($subscription_price<0){
					$previous_balance=$subscription_price;
				}

			}
			$subscription_price=$a_joiningfee+$subscription_price;
			$total_amount= $subscription_price;

			$cal_tax=($total_amount*$tax)/100;
			$data['totals']['plan'][]=array(
			'sort_order'=>3,
			'code'=>'tax',
			'title'=>$tax_name.' ('.$tax.'%)',
			'value'=>$this->currency->format($cal_tax, $this->session->data['currency'])
			);

			$current_invo=0;
			if(isset($currentplan)){
				if(isset($curr_invoice['previous_balance'])){	
				if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				$current_invo=$curr_invoice['previous_balance'];
				$data['totals']['plan'][]=array(
				'sort_order'=>4,
				'code'=>'previous_balance',
				'title'=>'Previous Balance',
				'value'=>$this->currency->format($current_invo, $this->session->data['currency'])
				);
				}
				}
			}

		$total=$total_amount+$cal_tax+$current_invo;
			$data['totals']['plan'][]=array(
			'sort_order'=>4,
			'code'=>'total',
			'title'=>$this->language->get('text_grand_total'),
			'value'=>$this->currency->format($total, $this->session->data['currency'])
			);

		if(!empty($result)){
			
		$pts_start_date=date('m/d/Y H:i:s');			
 		if($s_date==1){		
		$current_plan_start_date=$this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlanByPlanId($seller_id,$plan_id);
		
		$current_plan_start_date1=$this->model_extension_purpletree_multivendor_subscriptionplan->getLastPlan($seller_id,$plan_id);
		
		if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
		
		$current_plan_end_date=($current_plan_start_date1['new_end_date']!='0000-00-00 00:00:00')?date('m/d/Y H:i:s',strtotime($current_plan_start_date1['new_end_date'])):date('m/d/Y H:i:s', strtotime($current_plan_start_date1['start_date']. ' + '.$result['validity'].' days'));
		} else {
		$current_plan_end_date=($current_plan_start_date1['end_date']!='0000-00-00 00:00:00')?date('m/d/Y H:i:s',strtotime($current_plan_start_date1['end_date'])):date('m/d/Y H:i:s', strtotime($current_plan_start_date1['start_date']. ' + '.$result['validity'].' days'));	
		}
		$pts_start_date=$current_plan_end_date;
		} 
	
			$data['newplan'] = array(
				'plan_id'        => $result['plan_id'],
				'plan_name'        => $result['plan_name'],
				'plan_description'  => html_entity_decode($result['plan_description'], ENT_QUOTES, 'UTF-8'),
				'plan_short_description'  => strip_tags(html_entity_decode($result['plan_short_description'], ENT_QUOTES, 'UTF-8')),
				'no_of_product'  => $result['no_of_product'],
				'joining_fee'  => $this->currency->format($result['joining_fee'], $this->session->data['currency']),
				'subscription_price'  =>$this->currency->format($result['subscription_price'], $this->session->data['currency']),
				'price'  =>$result['subscription_price'],
				'validity'  => $result['validity'],
				'start_date'  => date('d/m/Y H:i:s',strtotime($pts_start_date)),
				'end_date'  =>  date('d/m/Y H:i:s', strtotime($pts_start_date. ' + '.$result['validity'].' days')),
				'subscribe'        =>$this->url->link('extension/account/purpletree_multivendor/dashboard', '' . 'plan_id=' . $result['plan_id'] . $url, true)
			);	
		}

		$c_joining_fee = $data['newplan']['joining_fee'];			
		$c_subscription_price = $data['newplan']['subscription_price'];					
		$n_joining_fee = $data['newplan']['joining_fee'];			
		$n_subscription_price = $data['newplan']['subscription_price'];	

		$data['action'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan/add', '', true);
		$data['seller_id']=$seller_id;
		$data['plan_id']=$plan_id;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/purpletree_multivendor/plan_confirmation', $data));
	}	

		public function getPlan(){ 
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
			}
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		if($this->config->get('module_purpletree_multivendor_subscription_plans')!=1){
		$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('purpletree_multivendor/subscriptionplan');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/purpletree_multivendor/subscriptionplan');

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
			'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', $url, true)

		);
		$data['title_subscription_plans']=$this->language->get('title_subscription_plans');
		$data['column_no_of_products']=$this->language->get('column_no_of_products');
		$data['text_featured_products']=$this->language->get('text_featured_products');
		$data['text_category_featured_products']=$this->language->get('text_category_featured_products');
		$data['column_joining_fees']=$this->language->get('column_joining_fees');
		$data['text_featured_store']=$this->language->get('text_featured_store');
		$data['column_subscription_fees']=$this->language->get('column_subscription_fees');
		$data['column_validity']=$this->language->get('column_validity');

		$data['button_view_all']=$this->language->get('button_view_all');
		$data['button_update']=$this->language->get('button_update');
		$data['button_subscribe']=$this->language->get('button_subscribe');
		$data['column_description']=$this->language->get('column_description');
		$data['heading_current_plan']=$this->language->get('heading_current_plan');
		$data['column_sellected_plan']=$this->language->get('column_sellected_plan');
		$data['column_start_date']=$this->language->get('column_start_date');
		$data['column_end_date']=$this->language->get('column_end_date');
		$data['text_subscription_status']=$this->language->get('text_subscription_status');
		$data['column_allowed_products']=$this->language->get('column_allowed_products');
		$data['column_used_products']=$this->language->get('column_used_products');
		$data['text_invoive_status']=$this->language->get('text_invoive_status');
		$data['button_renew']='Renew';
		
		$data['multiple_subscription_plan']=0;
		if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
		$data['multiple_subscription_plan']=$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active');	
		}
		
		
		$data['seller_status']=$this->model_extension_purpletree_multivendor_subscriptionplan->SellerPlanStatus($this->customer->getId());

		$plan=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlan($this->customer->getId());
		// echo "<pre>";
		// print_r($plan);
		// die;
		$data['subscription_status']=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscriptionStauts($this->customer->getId());

		$total_seller_assign_product=$this->model_extension_purpletree_multivendor_subscriptionplan->getTotalSellerPorduct($this->customer->getId());

		$data['seller_used_product']=$total_seller_assign_product;

		$invoice_stauts=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceStauts($this->customer->getId(),$plan['plan_id']);	

		$invoic_id=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceId($this->customer->getId());

		$invoice_status1=$this->model_extension_purpletree_multivendor_subscriptionplan->invoiceStauts($invoic_id);

		$subscription_status=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscriptionStauts($this->customer->getId());

		$total_featured_product = $this->model_extension_purpletree_multivendor_subscriptionplan->sellerTotalFeaturedProduct($this->customer->getId());

		$total_category_featured_product = $this->model_extension_purpletree_multivendor_subscriptionplan->sellerTotalCategpryFeaturedProduct($this->customer->getId());


		$total_seller_featured_product=0;
		if($total_featured_product!=NULL){
		$total_seller_featured_product=$total_featured_product;
		}	

		$total_seller_category_featured_product=0;
		if($total_category_featured_product!=NULL){
		$total_seller_category_featured_product=$total_category_featured_product;

		}
			$total_featured_product = $this->model_extension_purpletree_multivendor_subscriptionplan->sellerTotalFeaturedProduct($this->customer->getId());

		$total_category_featured_product = $this->model_extension_purpletree_multivendor_subscriptionplan->sellerTotalCategpryFeaturedProduct($this->customer->getId());

		$total_seller_featured_product=0;
		if($total_featured_product!=NULL){
		$total_seller_featured_product=$total_featured_product;
		}	

		$total_seller_category_featured_product=0;
		if($total_category_featured_product!=NULL){
		$total_seller_category_featured_product=$total_category_featured_product;
		}
	 			if($plan){	
				$data['sellerplan'] = array(
				'plan_id' => $plan['plan_id'],
				'plan_name'        => $plan['plan_name'],
				'no_of_product'  => $plan['no_of_product'],
				'joining_fee'  => $this->currency->format($plan['joining_fee'], $this->session->data['currency']),
				'subscription_price'  => $this->currency->format($plan['subscription_price'], $this->session->data['currency']),
				'validity'  => $plan['validity'],
				'start_date'  => ($plan['start_date']!='0000-00-00 00:00:00')?date('d/m/Y H:i:s',strtotime($plan['start_date'])):'',
				'end_date'  => ($plan['end_date']!='0000-00-00 00:00:00')?date('d/m/Y H:i:s',strtotime($plan['end_date'])):date('d/m/Y H:i:s',strtotime('+ '.$plan['validity'].' days',strtotime($plan['start_date']))),

				'subscription_status'=>($subscription_status)?$this->language->get('text_enabled'):$this->language->get('text_disabled'),
				'invoice_status'=>$invoice_status1,				
				'featured_product'=>$plan['no_of_featured_product'],			
				'total_featured_product'=>$total_seller_featured_product,			
				'category_featured_prodcut'=>$plan['no_of_category_featured_product'],				
				'total_category_featured_prodcut'=>$total_seller_category_featured_product,
				'featured_store'=>$plan['featured_store']?'Yes':'No'				

				);
				}  
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				$data['planid']=$this->model_extension_purpletree_multivendor_subscriptionplan->sellerMultiplePlanId($this->customer->getId());	
				} else {
				$data['planid']=$this->model_extension_purpletree_multivendor_subscriptionplan->sellerPlanId($this->customer->getId());
				}
				$payment_status = $this->model_extension_purpletree_multivendor_subscriptionplan->getstatuslist();
	
			$results=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscriptionPlan();

			//$invoice_status=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceStatus($this->customer->getId());
		
			if(isset($results)){
			foreach ($results as $result) {
				//$this->getstausfromid($invoice['status_id']);
				
				$invoice_status=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlanInvoiceStatus($this->customer->getId(),$result['plan_id']);
				
				if($invoice_status ==2 or $invoice_status ==NULL){
				$no_of_product=$result['no_of_product'];
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				$no_of_plan_product=$this->model_extension_purpletree_multivendor_subscriptionplan->getNoOfPlanProduct($this->customer->getId());
				$no_of_product=$no_of_plan_product['no_of_product'];	
				if($no_of_product==NULL){
				$no_of_product=0;	
				}
			}										
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
					$pts_sts='';
					if(!empty($data['planid'])){
					foreach($data['planid'] as $key111=>$value111){
					$plan_ids[]=$value111['plan_id'];
					}
				if(in_array($result['plan_id'],$plan_ids)){
					$pts_sts='renew';
				} else {
					$pts_sts='subscribe';
				}
				}
				} else {
				if($result['plan_id']===$data['planid']){
				$pts_sts='renew';	
				} else {
				$pts_sts='subscribe';
				}				
				}					
					
					$link=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan/subscribe', 'plan_id='.$result['plan_id'].'&status='.$pts_sts, true);
				
				} else {
					$this->session->data['error_warning'] =$this->language->get('error_subscription_plan_pending');
					//unset($this->session->data['error_warning']);
					$link=$this->url->link('extension/account/purpletree_multivendor/subscriptionplan','', true);	
				}

				
				$active=$this->language->get('text_active');

				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
					$active_plan='';
				if(!empty($data['planid'])){
					
					foreach($data['planid'] as $key111=>$value111){
					$plan_ids[]=$value111['plan_id'];
					}
				if(in_array($result['plan_id'],$plan_ids)){
					$active_plan='plans plan-active';
				
				} else {
					$active_plan='';
				}
				}
				} else {
					
				if($result['plan_id']===$data['planid']){
				$active_plan='plans plan-active';	
				} else {
				$active_plan='';
				}				
				}
					
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
						$pts_active='';
						$active_sts=0;
					if(!empty($data['planid'])){
					foreach($data['planid'] as $key111=>$value111){
					$plan_ids[]=$value111['plan_id'];
					}
				if(in_array($result['plan_id'],$plan_ids)){
					
					$subscriptionactive=$this->model_extension_purpletree_multivendor_subscriptionplan->sellerSubscriptionStatus($this->customer->getId());
					
					$subscriptionPlanStatus=$this->model_extension_purpletree_multivendor_subscriptionplan->getMultipleSubscriptionInvoiceStatus($this->customer->getId(),$result['plan_id']);
					if($subscriptionactive && $subscriptionPlanStatus==2){
					$pts_active='active';
					$active_sts=1;
					} else {
					$pts_active='inactive';
					$active_sts=1;
					}
				} else {
					$pts_active='';
					$active_sts=0;
				}
				}
				} else {
				if($result['plan_id']===$data['planid']){
				$subscriptionactive=$this->model_extension_purpletree_multivendor_subscriptionplan->sellerSubscriptionStatus($this->customer->getId());
				
				$subscriptionPlanStatus=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscriptionInvoiceStatus($this->customer->getId(),$result['plan_id']);
				
				if($subscriptionactive && $subscriptionPlanStatus==2){
				$pts_active='active';
				$active_sts=1;		
				} else {
				$pts_active='inactive';
				$active_sts=1;	
				}				
				} else {
				$pts_active='';
				$active_sts=0;
				}				
				}

				$data['subscriptions'][]= array(
				'active_plan' => $active_plan,
				'active' => $pts_active,
				'plan_id' => $result['plan_id'],
				'status' => $result['status'],
				'plan_name'        => $result['plan_name'],
				'plan_description'  => html_entity_decode($result['plan_description'], ENT_QUOTES, 'UTF-8'),

				'plan_short_description'  => strip_tags(html_entity_decode($result['plan_short_description'], ENT_QUOTES, 'UTF-8')),
				'no_of_product'  => $result['no_of_product'],
				'joining_fee'  => $this->currency->format($result['joining_fee'], $this->session->data['currency']),

				'subscription_price'  => $this->currency->format($result['subscription_price'], $this->session->data['currency']),
				'validity'  => $result['validity'],
				'subscribe'        =>$link,
				'subscription_status' =>($data['subscription_status'])?$this->language->get('text_enabled'):$this->language->get('text_disabled'),
				'invoice_status' =>$invoice_status,
				'featured_products' =>$result['no_of_featured_product'],
				'category_featured_prodcuts' =>$result['no_of_category_featured_product'],
				'featured_store' =>$result['featured_store']?'Yes':'No',
				'invoice'=>$this->url->link('extension/account/purpletree_multivendor/subscriptions&filter_plan_id='.$result['plan_id'], '', true)
		
			);
		} 
		}
		$subscriptionss=array();
		$subscriptionsss=array();
		if(!empty($data['subscriptions'])){
		foreach($data['subscriptions'] as $key4=>$value4){
			if($value4['active']=='active'){
				$subscriptionss[]=$value4;
			} else {
				$subscriptionsss[]=$value4;
			}
		}
		foreach($subscriptionsss as $key3=>$value3){
		array_push($subscriptionss,$value3);		
		}
		$data['subscriptions']=array();
		$data['subscriptions']=$subscriptionss;
		}

		$data['button_update'] = $this->language->get('button_update');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/purpletree_multivendor/subscription_plan', $data));

	}

		public function remindPrice($start_date,$validity,$s_price,$s_date){
				$this->load->language('purpletree_multivendor/subscriptionplan');
		$this->document->setTitle($this->language->get('heading_title'));
		$price=0;
		if($s_date == '1') {
			return $price;
		}

		$date1=date_create(date('Y-m-d'));
		$date2=date_create(date('Y-m-d',strtotime($start_date)));
		$diff=date_diff($date2,$date1);

		$r_date=$validity-((int)$diff->format("%a"));
		
		if($r_date>=0){
		$price=($s_price*$r_date)/$validity;
		}

		return $price;
		}

			protected function validateForm() {

			if ((utf8_strlen($this->request->post['comment']) < 1) || (utf8_strlen($this->request->post['comment']) > 255)) {
				$this->error['comment']= $this->language->get('text_comment');
			}
			if ($this->error && !isset($this->error['warning'])) {
				$this->error['warning'] = $this->language->get('error_warning');
			}
				return !$this->error;
			}
}
?>
