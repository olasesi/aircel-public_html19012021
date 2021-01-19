<?php
class ControllerExtensionPaymentPPAdaptive extends Controller {
	public function index() {
		$this->load->language('extension/payment/pp_adaptive');

		$data['text_testmode'] = $this->language->get('text_testmode');
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['testmode'] = $this->config->get('payment_pp_adaptive_test');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
			$data['business'] = $this->config->get('payment_pp_adaptive_admin_email');
			$data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						
						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$data['products'][] = array(
					'name'     => htmlspecialchars($product['name']),
					'model'    => htmlspecialchars($product['model']),
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
					'quantity' => $product['quantity'],
					'option'   => $option_data,
					'weight'   => $product['weight']
				);
			}

			$data['discount_amount_cart'] = 0;

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

			if ($total > 0) {
				$data['products'][] = array(
					'name'     => $this->language->get('text_total'),
					'model'    => '',
					'price'    => $total,
					'quantity' => 1,
					'option'   => array(),
					'weight'   => 0
				);
			} else {
				$data['discount_amount_cart'] -= $total;
			}

			$data['currency_code'] = $order_info['currency_code'];
			$data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
			$data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
			$data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$data['country'] = $order_info['payment_iso_code_2'];
			$data['email'] = $order_info['email'];
			$data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['lc'] = $this->session->data['language'];
			$data['return'] = $this->url->link('checkout/success');
			$notify_url = $this->url->link('extension/payment/pp_adaptive/callback', '', true);
			$cancel_return = $this->url->link('checkout/checkout', '', true);

			if (!$this->config->get('payment_pp_adaptive_transaction')) {
				$data['paymentaction'] = 'authorization';
			} else {
				$data['paymentaction'] = 'sale';
			}
			$data['custom'] = $this->session->data['order_id'];
			
			$pts_order_products=array();
			$order_id = $this->session->data['order_id'];
			$customer_mail=$order_info['email'];

			if ( $this->config->get('module_purpletree_multivendor_commission_status')!= null) {
				$sellerorders = $this->db->query("SELECT * FROM `" . DB_PREFIX . "purpletree_vendor_orders` WHERE order_id = '" . (int)$order_id . "'");
				
			$shipcommsvirtial = '0';
			$dsdsds = array();
			$pay_admin_commission=0;
			$pay_seller_commission=array();
			$seller_payment=0;

				if(!empty($sellerorders->rows)) {
					foreach($sellerorders->rows as $sellerorder) {
						$sql1111 = "SELECT `store_commission` FROM `" . DB_PREFIX . "purpletree_vendor_stores` WHERE seller_id = '" . (int)$sellerorder['seller_id'] . "'";
						$totalshipingorder = '0';
								$getShippingOrderTotal = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "purpletree_order_total` WHERE order_id = '" . (int)$order_id . "' AND seller_id = '" . (int)$sellerorder['seller_id'] . "' AND code ='seller_shipping'");
						if($getShippingOrderTotal->num_rows){
							$totalshipingorder = $getShippingOrderTotal->row['value'];
						}
				
						$query = $this->db->query($sql1111);
						$seller_commission = $query->row;
						/* if($sellerorder['order_status_id'] == $this->config->get('module_purpletree_multivendor_commission_status')) { */
							 //category_commission
				        $productid = $sellerorder['product_id'];	
						$catids =$this->getProductCategory($productid );
						$commission_cat = array();
						$catttt = array();
						 $shippingcommision = 0;
							 if($totalshipingorder != 0) {
								 if (null !== $this->config->get('module_purpletree_multivendor_shipping_commission')) {
									 if(!array_key_exists($sellerorder['seller_id'],$dsdsds)) {
									 $shippingcommision = (($this->config->get('module_purpletree_multivendor_shipping_commission')*$totalshipingorder)/100);
									 $dsdsds[$sellerorder['seller_id']] = $shippingcommision;
									 }
								 }
							 }
						if(!empty($catids)){
							foreach($catids as $cat) {
								$sql = "SELECT * FROM " . DB_PREFIX . "purpletree_vendor_categories_commission where category_id = '".(int)$cat['category_id']."'";
								$query = $this->db->query($sql);
								$commission_cat[] = $query->rows;
							}
						
						}
						$commission = -1;
						$commission1 = -1;
						$comipercen = 0;
						$comifixs = 0;
						
						if(!empty($commission_cat)) {
						 foreach($commission_cat as $catts) {
						 foreach($catts as $catt) {
								$comifix = 0;
							 if(isset($catt['commison_fixed']) && $catt['commison_fixed'] != '') {
								$comifix = $catt['commison_fixed'];
							 }
								$comiper = 0;
							 if(isset($catt['commission']) && $catt['commission'] != '') {
								$comiper = $catt['commission'];
							 }
							
							 if (null !== $this->config->get('module_purpletree_multivendor_seller_group') && $this->config->get('module_purpletree_multivendor_seller_group') == 1) {
								$sqlgrop = "Select `customer_group_id` from `" . DB_PREFIX . "customer` where customer_id= ".$sellerorder['seller_id']." ";
								$querygrop = $this->db->query($sqlgrop);
								$sellergrp = $querygrop->row;
								if($catt['seller_group'] == $sellergrp['customer_group_id']) {
									$commipercent = (($comiper*$sellerorder['total_price'])/100);
									$commission1 = $comifix + $commipercent + $shippingcommision;
									if($commission1 > $commission) {
										$comipercen 		= $comiper;
										$comifixs 			= $comifix;
										$shippingcommision 	= $shippingcommision;
										$commission 		= $commission1;
									}
								}
							 } else {
								 $commipercent = (($comiper*$sellerorder['total_price'])/100);
									$commission1 = $comifix + $commipercent + $shippingcommision;
									if($commission1 > $commission) {
										$comipercen 		= $comiper;
										$comifixs 			= $comifix;
										$shippingcommision 	= $shippingcommision;
										$commission 		= $commission1;
									} 
							 }
						   }
						 }
						}
						if($commission != -1) {
							$commission = $commission;
						}
						//category_commission
						elseif(isset($seller_commission['store_commission']) && ($seller_commission['store_commission'] != NULL || $seller_commission['store_commission'] != '')){
							$comipercen = $seller_commission['store_commission'];
							$commission = (($sellerorder['total_price']*$seller_commission['store_commission'])/100)+$shippingcommision;
						} else {
							$comipercen = $this->config->get('module_purpletree_multivendor_commission');
							$commission = (($sellerorder['total_price']*$this->config->get('module_purpletree_multivendor_commission'))/100)+$shippingcommision;
						}
						$seller_payment = $sellerorder['total_price']-$commission;
						 if(!isset($pay_seller_commission[$sellerorder['seller_id']])){
							$pay_seller_commission[$sellerorder['seller_id']] = $seller_payment ;
						} else {
							$pay_seller_commission[$sellerorder['seller_id']] += $seller_payment;
						} 
						 
					}
				}
			}
		$total_price = $this->db->query("SELECT value FROM " . DB_PREFIX . "order_total WHERE order_id = '".(int)$order_id."' AND code='total'")->row['value'];
		$sell_amount=0;
		foreach($pay_seller_commission as $ptseller_id=>$ptamount){
			$sell_amount+=$ptamount;
		}
		$admin_amount=$total_price-$sell_amount;
			
	$this->load->model('extension/payment/pp_adaptive');
	 
	$receiverList=array();
	$seller_detail=array();
				$receiver['receiver'][]=array(
					'email'=>$this->config->get('payment_pp_adaptive_admin_email'),
					'amount'=>$admin_amount,
					//'phoneCountry'=>'',
					//'phoneNumber'=>$this->config->get('config_telephone'),
					//'phoneExtn'=>'',
					//'primaryReceiver'=>'false',
					//'invoiceId'=>'',
				//	'paymentType'=>'- Select -',
				//	'paymentSubType'=>''
				
				);	


			
			foreach($pay_seller_commission as $pts_seller_id=>$pts_amount){
				$seller_detail=$this->model_extension_payment_pp_adaptive->getSellerDetail($pts_seller_id);
				$receiver['receiver'][]=array(
					'email'=>$seller_detail['seller_paypal_id'],
					'amount'=>$pts_amount,
					//'phoneCountry'=>'',
					//'phoneNumber'=>$seller_detail['store_phone'],
					//'phoneExtn'=>'',
					//'primaryReceiver'=>'false',
					//'invoiceId'=>'',
					//'paymentType'=>'- Select -',
					//'paymentSubType'=>''
				);
			}
			//pts adaptive

			$notify_url = $this->url->link('extension/payment/pp_adaptive/callback&order_id='.$order_id, '', true);
			$data['adaptivepayment']=array(
				'actionType'=>'PAY',
				'currencyCode'=>'USD',
				'returnUrl'=>$notify_url,		
				'cancelUrl'=>$this->url->link('checkout/checkout'),
				//'feesPayer'=>'SENDER',
				//'ipnNotificationUrl'=>$notify_url,
				//'memo'=>'',
				//'pin'=>'',			
				//'preapprovalKey'=>'',			
				'requestEnvelope'=>array("errorLanguage"=> "en_US",
										"detailLevel" => "ReturnAll"),
				'receiverList'=>$receiver,			
				//'reverseAllParallelPaymentsOnError'=>'false',		
			//	'senderEmail'=>$customer_mail,		
				//'trackingId'=>'',		
				//'fundingConstraint'=>'',		
			//	'emailIdentifier'=>'',		
			//	'senderCountryCode'=>'',		
			//	'senderPhoneNumber'=>'',		
			//	'senderExtension'=>'',	
				//'useCredentials'=>'',
				
			); 

		if (!$this->config->get('payment_pp_adaptive_test')) {
			$url="https://svcs.paypal.com/AdaptivePayments/Pay";
		} else {
			$url="https://svcs.sandbox.paypal.com/AdaptivePayments/Pay";
		}
		
		$ch = curl_init();
		$inputs=json_encode($data['adaptivepayment']);
		$username 	= $this->config->get('payment_pp_adaptive_admin_username');
		$password 	= $this->config->get('payment_pp_adaptive_admin_password');
		$signature 	= $this->config->get('payment_pp_adaptive_admin_signature');
		$appid 		= $this->config->get('payment_pp_adaptive_admin_appid');

		$header = array(
		'X-PAYPAL-SECURITY-USERID:'.$username,
		'X-PAYPAL-SECURITY-PASSWORD:'.$password,
		'X-PAYPAL-SECURITY-SIGNATURE:'.$signature,
		'X-PAYPAL-APPLICATION-ID:'.$appid,
		'X-PAYPAL-REQUEST-DATA-FORMAT: JSON',
		'X-PAYPAL-RESPONSE-DATA-FORMAT: JSON'
		);

		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$inputs);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);
		curl_close ($ch);
		$response=array();
		$response = json_decode($server_output, true); 
		if (!$response) {
				if($this->config->get('payment_pp_adaptive_debug')){
				$this->log->write('PP_ADAPTIVE :: CURL failed ' . curl_error($ch) . '(' . curl_errno($ch) . ')');
				}
			} else {
				if($this->config->get('payment_pp_adaptive_debug')){
				$this->log->write('Paypal Adaptive :: curl Success ');
				}
			}
		$status=$response['responseEnvelope']['ack'];

		if(strtoupper($status)=='SUCCESS'){
		$paymentkey=$response['payKey'];
	if (!$this->config->get('payment_pp_adaptive_test')) {
			$data['action'] = 'https://www.paypal.com/webscr&cmd=_ap-payment';
			$data['paymentkey'] = $paymentkey;
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_adaptive_paykey where order_id = '".(int)$order_id."'"); 
			if($query->num_rows>0){
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_adaptive_paykey SET payment_key='".$paymentkey."' where order_id = '".(int)$order_id."'");
			} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "purpletree_vendor_adaptive_paykey` SET order_id='".(int)$order_id."',payment_key='".$paymentkey."'");
			}
		} else {
			$data['action'] = 'https://www.sandbox.paypal.com/webscr&cmd=_ap-payment';
			$data['paymentkey'] = $paymentkey;
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_adaptive_paykey where order_id = '".(int)$order_id."'"); 
			if($query->num_rows>0){
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_adaptive_paykey SET payment_key='".$paymentkey."' where order_id = '".(int)$order_id."'");
			} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "purpletree_vendor_adaptive_paykey` SET order_id='".(int)$order_id."',payment_key='".$paymentkey."'");
			}				 
		}
	}
			return $this->load->view('extension/payment/pp_adaptive', $data);
		}
	}
	public function getProductCategory($productid){
		
		$sql = "SELECT category_id FROM " . DB_PREFIX . "product_to_category where 	product_id = '".(int)$productid."'"; 
		
		  $query = $this->db->query($sql);
		  
		  return $query->rows;  
		}
	public function callback() {
		//errorlog 
	 	if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		} 
		$payment_key = $this->db->query("SELECT payment_key FROM " . DB_PREFIX . "purpletree_vendor_adaptive_paykey where order_id = '".(int)$order_id."'")->row['payment_key']; 

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$request = 'cmd=_notify-validate';

			foreach ($this->request->get as $key => $value) {
				$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
			}

			if (!$this->config->get('payment_pp_adaptive_test')) {
			$url = 'https://svcs.paypal.com/AdaptivePayments/PaymentDetails';
			} else {
				$url ='https://svcs.sandbox.paypal.com/AdaptivePayments/PaymentDetails';
			}
		$username 	= $this->config->get('payment_pp_adaptive_admin_username');
		$password 	= $this->config->get('payment_pp_adaptive_admin_password');
		$signature 	= $this->config->get('payment_pp_adaptive_admin_signature');
		$appid 		= $this->config->get('payment_pp_adaptive_admin_appid');

		$header = array(
		'X-PAYPAL-SECURITY-USERID:'.$username,
		'X-PAYPAL-SECURITY-PASSWORD:'.$password,
		'X-PAYPAL-SECURITY-SIGNATURE:'.$signature,
		'X-PAYPAL-REQUEST-DATA-FORMAT: NV',
		'X-PAYPAL-RESPONSE-DATA-FORMAT: JSON',
		'X-PAYPAL-APPLICATION-ID:'.$appid
		);
		$payment_key1='payKey='.$payment_key.'&requestEnvelope.errorLanguage=en_US';

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$payment_key1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);
		curl_close ($ch);
		$response=array();
		$response = json_decode($server_output, true); 
		$this->load->model('extension/payment/pp_adaptive');

			if (!$response) {
				if($this->config->get('payment_pp_adaptive_debug')){
				$this->log->write('PP_ADAPTIVE :: CURL failed ' . curl_error($ch) . '(' . curl_errno($ch) . ')');
				}
			} else {
				if($this->config->get('payment_pp_adaptive_debug')){
				$this->log->write('Paypal Adaptive :: Adaptive Payment Success ');
				}
			}

			if (strcmp($response['responseEnvelope']['ack'], 'Success') == 0 ) {
				
		$responseData=array();
		if(!empty($response['paymentInfoList']['paymentInfo'])){
			foreach($response['paymentInfoList']['paymentInfo'] as $key => $value){
				$seller_id=$this->model_extension_payment_pp_adaptive->getSellerId($value['receiver']['email']);
				if($seller_id){
						$transactionId='';
						$transactionStatus='';
					if(isset($value['transactionId'])){
						$transactionId=$value['transactionId'];
					}
					if(isset($value['transactionStatus'])){
						$transactionStatus=$value['transactionStatus'];
					}
					 $responseData[]=array(
					'seller_id'=>$seller_id,
					'transactionId'=>$transactionId,
					'transactionStatus'=>$transactionStatus,
					'senderTransactionStatus'=>$value['senderTransactionStatus'],
					'email'=>$value['receiver']['email'],
					'amount'=>$value['receiver']['amount'],
					'paymentType'=>$value['receiver']['paymentType'],
					'accountId'=>$value['receiver']['accountId']					
					);
				}
			}
		}
				
				if($order_id){
					$comm_data=array();
					$invoicedata=array();
					$pendingmsg='Payment for some item in this order is fail. Please contact to Adminstrator <br>';
					$successmsg='';
					$order_status_id='';
					if(!empty($responseData)){
						foreach($responseData as $keys=>$values1){
								if($values1['senderTransactionStatus']!='COMPLETED'){
									if($this->config->get('payment_pp_adaptive_debug')){
									$this->log->write('Paypal Adaptive :: Sender Transaction Status '.$values1['senderTransactionStatus'].' AND Transaction Status- '.$values1['transactionStatus']);
									}
									$pendingmsg.="Transaction status: ".$values1['senderTransactionStatus']."<br>";
									$status='Pending';
									$status_id=1;
									$order_status_id=1;
								   } else {
								if($this->config->get('payment_pp_adaptive_debug')){
									$this->log->write('Paypal Adaptive :: Sender Transaction Status '.$values1['senderTransactionStatus'].' AND Transaction Status- '.$values1['transactionStatus']);
									
									}
									$status='Complete';
									$status_id=2;
									$successmsg.="Transaction status: ".$values1['senderTransactionStatus']."<br>";	
									
								}
						}
					}
					
			
	if($order_status_id==1){
		$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $pendingmsg,$notify = true);
		$this->response->redirect($this->url->link('checkout/success', '', true));
	} else {
		$order_status_id = $this->config->get('payment_pp_adaptive_order_status_id');
		$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $successmsg,$notify = true);
		if(!empty($responseData)){
			foreach($responseData as $keys=>$valuess){
				$total_commission_data= $this->model_extension_payment_pp_adaptive->totalCommission($valuess['seller_id'],$order_id);
				
				$total_price_data= $this->model_extension_payment_pp_adaptive->totalPrice($valuess['seller_id'],$order_id);
							
				$invoice_id= $this->model_extension_payment_pp_adaptive->savelinkid($total_price_data['total_price'],$total_commission_data['commission'],$valuess['amount']);
				if($invoice_id){
				$invoicedata[$valuess['seller_id']]=$invoice_id;
							$transData=array(
								'invoice_id'=>$invoice_id,
								'seller_id'=>$valuess['seller_id'],
								'transaction_id'=>$valuess['transactionId'],
								'amount'=>$valuess['amount'],
								'payment_mode'=>'Online',
								'status'=>$status,
								'status_id'=>$status_id,
								);
								$this->model_extension_payment_pp_adaptive->saveTranDetail($transData);
								$this->model_extension_payment_pp_adaptive->saveTranHistory($transData); 
				}
			}
		}
				$commisionsss=array();
					$commisionsss= $this->model_extension_payment_pp_adaptive->getCommissionData($order_id);
					 if(!empty($commisionsss)) {
						 foreach($commisionsss as $keyes=>$commisionss){
						if($commisionss['invoice_status'] == 0) {
							$linkid=$invoicedata[$commisionss['seller_id']];
							$this->model_extension_payment_pp_adaptive->saveCommisionInvoice($commisionss,$linkid);
						}
					 }
					} 
					$this->response->redirect($this->url->link('checkout/success', '', true));
		
					}	
				} 

			} else {
				
				//$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
				$this->response->redirect($this->url->link('checkout/failure', '', true));
			}

		}
	}
}?>