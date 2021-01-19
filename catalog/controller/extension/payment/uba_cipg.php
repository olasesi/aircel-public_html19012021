<?php
class ControllerExtensionPaymentUbaCipg extends Controller {

	public function index() {
		$this->load->language('extension/payment/uba_cipg');

		$data['text_testmode'] = $this->language->get('text_testmode');
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['testmode'] = $this->config->get('payment_uba_cipg_test');
		$data['noOfItems'] = $this->config->get('payment_uba_cipg_total');
		
		if (!$this->config->get('payment_uba_cipg_test')) {
			$data['action'] = $this->url->link('extension/payment/uba_cipg/pay', 'order=' . $this->session->data['order_id'], true);
		} else {
			$data['action'] = 'https://databaseendsrv.cloudapp.net/cipg-payportal/regptran';
		}
		// if (!$this->config->get('payment_uba_cipg_test')) {
		// 	$data['action'] = 'https://ucollect.ubagroup.com/cipg-payportal/regptran';
		// } else {
		// 	$data['action'] = 'https://databaseendsrv.cloudapp.net/cipg-payportal/regptran';
		// }
		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {

			$data['merchantId'] = $this->config->get('payment_uba_cipg_merchant');
			$data['description'] = $this->config->get('config_name');
			$data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

			$data['date'] = date("d/m/Y H:i:s");
			$data['countryCurrencyCode'] = 566;
			$data['customerFirstName'] = $order_info['payment_firstname'];
			$data['customerLastname'] = $order_info['payment_lastname'];
			$data['customerEmail'] = $order_info['email'];
			$data['customerPhoneNumber'] = $order_info['telephone'];
			$data['referenceNumber'] = uniqid('' . $this->session->data['order_id'] . '-');
			$data['serviceKey'] = $this->config->get('payment_uba_cipg_signature');	

			$data['approveurl'] = $this->url->link('checkout/success', '', true);
			$data['cancelurl'] = $this->url->link('checkout/payment', '', true);
			$data['declineurl'] = $this->url->link('checkout/cart', '', true);
			$data['callbackurl'] = $this->url->link('extension/payment/uba_cipg/callback', 'order=' . $this->session->data['order_id'], true);

			$data['custom'] = $this->session->data['order_id'];

			return $this->load->view('extension/payment/uba_cipg', $data);
		}

	}
	public function pay() {

		$post = array(
			"merchantId" => $this->config->get('payment_uba_cipg_merchant'),
			"description" => $this->config->get('config_name'),
			"total" => $_REQUEST["total"],
			"date"=> date("d/m/Y H:i:s"),
			"countryCurrencyCode"=> 566,
			"noOfItems"=> $this->config->get('payment_uba_cipg_total'),
			"customerFirstName" => $_REQUEST["customerFirstName"],
			"customerLastname" => $_REQUEST["customerLastname"],
			"customerEmail"=> $_REQUEST["customerEmail"],
			"customerPhoneNumber"=> $_REQUEST["customerPhoneNumber"],
			"referenceNumber" =>uniqid('' . $this->session->data['order_id'] . '-'), 
			"serviceKey" => $this->config->get('payment_uba_cipg_signature'),
			"approveurl" => $this->url->link('checkout/success', '', true),
			"cancelurl" => $this->url->link('checkout/payment', '', true),
			"declineurl" => $this->url->link('checkout/cart', '', true),
			"callbackurl" => $this->url->link('extension/payment/uba_cipg/callback', 'order=' . $this->session->data['order_id'], true),
		);

		if (!$this->config->get('payment_uba_cipg_test')) {
			$curl = curl_init('https://ucollect.ubagroup.com/cipg-payportal/regptran');
		} else {
			$curl = curl_init('https://databaseendsrv.cloudapp.net/cipg-payportal/regptran');
		}

		

		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($curl);
// 		echo "response = ". $response. "<br>";
		$returnCode=curl_getinfo($curl,CURLINFO_HTTP_CODE);
// 		echo "returnCode = ". $returnCode. "<br>";
		$link = 'https://ucollect.ubagroup.com/cipg-payportal/paytran';
		if ($returnCode==200) {
			$transactionid= $response;
			$paylink=$link."?id=".$transactionid;
			header("Location:".$paylink);
// 			echo "paylink = ". $paylink. "<br>";
		} else {
			switch ($returnCode) {
				case 200:break;					
				default:
					$result = 'error code' . $returnCode;
					break;	
			}
		}

		curl_close($curl);  


	}
	
	public function callback() {
		$this->load->language('extension/payment/uba_cipg');		

		if (isset($this->request->get['method']) && $this->request->get['method'] == 'decline') {
			$this->session->data['error'] = $this->language->get('error_declined');

			$this->response->redirect($this->url->link('checkout/cart'));
		}

		if (isset($this->request->post['custom'])) {
			$order_id = $this->request->post['custom'];
		} else {
			$order_id = 0;
		}

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

		// if ($order_info) { 
		// 	// Fraud Verification Step.
		// 	// $request = '';
		// 	$post = array(
		// 		"merchantId" => $data['merchantId'],
		// 		"description" => $data['description'],
		// 		"total" =>$data['total'],
		// 		"date"=> $data['date'],
		// 		"countryCurrencyCode"=> $data['countryCurrencyCode'],
		// 		"noOfItems"=> $data['noOfItems'],
		// 		"customerFirstName" => $data['customerFirstName'],
		// 		"customerLastname" => $data['customerLastname'],
		// 		"customerEmail"=> $data['custo	merEmail'],
		// 		"customerPhoneNumber"=> $data['customerPhoneNumber'],
		// 		"referenceNumber" =>$data['referenceNumber'], 
		// 		"serviceKey" => $data['serviceKey'],
		// 	);


			


		// 	if (!$this->config->get('payment_uba_cipg_test')) {
		// 		$curl = curl_init('https://ucollect.ubagroup.com/cipg-payportal/regptran');
		// 	} else {
		// 		$curl = curl_init('https://databaseendsrv.cloudapp.net/cipg-payportal/regptran');
		// 	}

			

		// 	curl_setopt($curl, CURLOPT_HEADER, false);
		// 	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		// 	curl_setopt($curl, CURLOPT_POST, true);
		// 	curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
		// 	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		// 	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		// 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			

		// 	$response = curl_exec($curl);
		// 	$returnCode=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		// 	$link = 'https://ucollect.ubagroup.com/cipg-payportal/paytran';
		// 	if ($returnCode==200) {
		// 		$transactionid= $response;
		// 		$paylink=$link."?id=".$transactionid;
		// 		header("Location:".$paylink);
		// 	} else {
		// 		switch ($returnCode) {
		// 			case 200:break;					
		// 			default:
		// 				$result = 'error code' . $returnCode;
		// 				break;	
		// 		}
		// 	}

		// 	curl_close($curl);                
		}
		
		// $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, "Transaction reference: ".$reference, true);
		
		// $this->response->redirect($redir_url);
	}

