<?php
class ControllerApiCheckout extends Controller {
    public function addorder () {
        $this->load->model('custom/cart');
		$this->load->model('custom/address');
        $this->load->model('setting/extension');
        $this->load->model('account/customer');
		$this->load->model('checkout/order');

    	$json = array();
        $order_data = array();
        $tqty = 0;
        $subtotal = 0;
        $json['status'] = true;
        
        $customer_id = $this->request->post['customer_id'];
		$address_id = $this->request->post['address_id'];
		$shippingoption = $this->request->post['shippingoption'];
		$paymentoption = $this->request->post['paymentoption'];

        // customer info
		$customerInfo = $this->model_account_customer->getCustomer($this->request->post['customer_id']);
		
        //products
        $products = $this->model_custom_cart->getProducts($customerInfo['customer_id']);
		$taxes = $this->model_custom_cart->getTaxes($customerInfo['customer_id']);
		
		//********************added 09/11/2019 for emailing
	    $message ="<div style='text-align:left;font-size=12px;color=#000000;font-family=serif'>";
	    /*$message .=  "Thank you for registering with us.<br /><br />";
	    $message .= "Click the link below to verify your email<br />";
	    $message .= "<a href='http://www.miratechnologiesng.com/scripts/verifyemail.php?token=" . $token . "'> Verify Email</a>";
	    $message .= "</div>";
	    $secondary = "greatestgreatemeka@gmail.com";*/
        //$response = sendEmail($secondary,$username,$message);

        $mheaders = "MIME-Version: 1.0"  . "\r\n";
        $mheaders .= "Content-type:text/html; charset=UTF-8" . "\r\n";		
        //$email = $_REQUEST['email'] ;
        $subject = "Mira Technologies: Thank You";
        //$message = $_REQUEST['messg'] ;
        //$message = wordwrap($message, 70);	

        $mheaders .= "From: sales@obejor.com.ng" . "\r\n"; //$_REQUEST['email'];
        $mheaders .= "Reply-To: sales@obejor.com.ng" . "\r\n";
        //$response = mail($username .",ici@miratechnologiesng.com,icisystemng@gmail.com",$subject,$message, $mheaders);        
		//****End emailing
		
		foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

                $tqty += $product_total;
                
				if ($product['minimum'] > $product_total) {
					$json['error']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}
				$currencysession = "";
                if(isset($this->session->data['currency']) AND ($this->session->data['currency'] == "NGN")){
                    $currencysession = $this->session->data['currency'];
                }else{
                    $currencysession = "NGN";
                }

				  $order_data['products'][] = array(
					'cart_id'    => $product['cart_id'],
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'quantity'   => $product['quantity'],
					'stock'      => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'shipping'   => $product['shipping'],
					'price'      => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
		        	'total'      => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'],
					//'price'      => $this->currency->format($product['price'], $this->session->data['currency']),
				    //'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
		        	//'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
					//'total'      => $this->currency->format($product['price'] * $product['quantity'], $this->session->data['currency']),
					'reward'     => $product['reward']
				 );

				//added for emailing 09/11/2019
				$message .= "Cart_id: " . $product['cart_id'] . " <br />";
				$message .= "Product_id: " . $product['product_id'] . " <br />";
				$message .= "Name: " . $product['name'] . " <br />";
				$message .= "model: " . $product['model'] . " <br />";
				$message .= "Quantity: " . $product['quantity'] . " <br />";
				//$message .= "Stock: " . $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')) . " <br />";
				$message .= "Shipping: " . $product['shipping'] . " <br />";
				$message .= "aPrice: " . $product['price'] . " <br />";
				$message .= "aTotal: " . $product['price'] * $product['quantity'] . " <br />";
				$message .= "bPrice: " . $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) . " <br />";
				$message .= "bTotal: " . $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'] . " <br />";
				$message .= "cPrice: " . $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) . " <br />";
				$message .= "cTotal: " . $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']) . " <br />";
				$message .= "dPrice: " . $this->currency->format($product['price'], $this->session->data['currency']) . " <br />";
				$message .= "dTotal: " . $this->currency->format(($product['price'] * $product['quantity']), $this->session->data['currency']) . " <br />";
				
				$message .= "Tax Class ID: " . $product['tax_class_id'] . " <br />";
				$message .= "Config Tax: " . $this->config->get('config_tax') . " <br />";
				$message .= "Currency: " . $this->session->data['currency'] . " <br />";
				//**End emailing
				
				$subtotal1 = round($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'],2);

			    $subtotalstr = str_split(strval($subtotal1));
			    $finalsub ='';
			    if(count($subtotalstr) > 0){
    			    for($i=0;$i<count($subtotalstr);$i++){
	    		        if($subtotalstr[$i] !== ","){
	    		            $finalsub .= $subtotalstr[$i];
	    		        }
		    	    }
		    	}
				$subtotal += (double)$subtotal1; //(double)$finalsub;
				

			}
			
			//**Emailing 09/11/2019
			$message .= "</div>";
			mail("ici@miratechnologiesng.com,icisystemng@gmail.com",$subject,$message, $mheaders);        
			//**end emailing
			
		// Totals
		$totals = array();
		$total = 0;
        
		// Because __call can not keep var references so we put them into an array. 
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);
		
		$sort_order = array();

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);
		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				// We have to put the totals in an array so that they pass by reference.
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}
		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);
			
		$tax = $totals;
	    $taxcost = $tax[1]['value'];
			
		// Voucher
		$order_data['vouchers'] = array();
		if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$order_data['vouchers'][] = array(
						'code'             => $voucher['code'],
						'description'      => $voucher['description'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'price'            => $this->currency->format($voucher['amount'], $this->session->data['currency']),			
						'amount'           => $voucher['amount']
					);
				}
			}
			
	    $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_name'] = $this->config->get('config_name');
		
		if ($order_data['store_id']) {
				$order_data['store_url'] = $this->config->get('config_url');
		} else {
			if ($this->request->server['HTTPS']) {
				$order_data['store_url'] = HTTPS_SERVER;
			} else {
				$order_data['store_url'] = HTTP_SERVER;
			}
		}
		
		// customer info
		$customer_info = $this->model_account_customer->getCustomer($this->request->post['customer_id']);
		
		$order_data['customer_id'] = $customer_info['customer_id'];
		$order_data['customer_group_id'] = $customer_info['customer_group_id'];
		$order_data['firstname'] = $customer_info['firstname'];
		$order_data['lastname'] = $customer_info['lastname'];
		$order_data['email'] = $customer_info['email'];
		$order_data['telephone'] = $customer_info['telephone'];
		$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
		
        $address = $this->model_custom_address->getAddress($customer_id, $address_id);
		
        // payment data
		$order_data['payment_firstname'] = $address['firstname'];
		$order_data['payment_lastname'] = $address['lastname'];
		$order_data['payment_company'] = $address['company'];
		$order_data['payment_address_1'] = $address['address_1'];
		$order_data['payment_address_2'] = $address['address_2'];
		$order_data['payment_city'] = $address['city'];
		$order_data['payment_postcode'] = $address['postcode'];
		$order_data['payment_zone'] = $address['zone'];
		$order_data['payment_zone_id'] = $address['zone_id'];
		$order_data['payment_country'] = $address['country'];
		$order_data['payment_country_id'] = $address['country_id'];
		$order_data['payment_address_format'] = $address['address_format'];
		$order_data['payment_custom_field'] = (isset($address['payment_address']['custom_field']) ? $address['payment_address']['custom_field'] : array());
		
		
		if ($this->request->post['paymentoption'] == 'rave') {
		    $order_data['payment_method'] = 'Rave by Flutterwave';
			$order_data['payment_code'] = 'rave';
		} elseif ( $this->request->post['paymentoption'] == 'cod') {
		    $order_data['payment_method'] = 'Cash On Delivery';
			$order_data['payment_code'] = 'cod';
		} else {
			$order_data['payment_method'] = '';
			$order_data['payment_code'] = '';
		}
		
        //shipping details
        $order_data['shipping_firstname'] = $address['firstname'];
		$order_data['shipping_lastname'] =  $address['lastname'];
		$order_data['shipping_company'] = $address['company'];
		$order_data['shipping_address_1'] = $address['address_1'];
		$order_data['shipping_address_2'] = $address['address_2'];
		$order_data['shipping_city'] = $address['city'];
		$order_data['shipping_postcode'] = $address['postcode'];
		$order_data['shipping_zone'] = $address['zone'];
		$order_data['shipping_zone_id'] = 	$address['zone_id'];
		$order_data['shipping_country'] = 		$address['country'];
		$order_data['shipping_country_id'] = $address['country_id'];
		$order_data['shipping_address_format'] = $address['address_format'];
		$order_data['shipping_custom_field'] = (isset($address['payment_address']['custom_field']) ? $address['payment_address']['custom_field'] : array());
		
		$shippingresults = $this->model_setting_extension->getExtensions('shipping');
		$shipmethod_data = array();
		
		foreach ($shippingresults as $result) {
    		if ($this->config->get('shipping_' . $result['code'] . '_status')) {
    			$this->load->model('extension/shipping/' . $result['code']);
    			$this->load->model('custom/address');
                $shipping_address = $this->model_custom_address->getAddress($customer_id, $address_id);
    			$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($shipping_address);
    			
                if($result['code'] == "pickup" AND $from_abroad == "YES") { //added 29/08/2019
                }else{ //added 29/08/2019
    			  if ($quote) {
    				$shipmethod_data[$result['code']] = array(
    					'title'      => $quote['title'],
    					'quote'      => $quote['quote'],
    					'sort_order' => $quote['sort_order'],
    					'error'      => $quote['error']
    				);
    			  }
                } //added 29/08/2019
    		}
    	}
    	
    	$sort_order = array();
    
    	foreach ($shipmethod_data as $key => $value) {
    		$sort_order[$key] = $value['sort_order'];
    	}
    
    	array_multisort($sort_order, SORT_ASC, $shipmethod_data);

        $shipping_location;
		if ($this->request->post['shippingoption'] == 'deliver') {
            $weight = $shipmethod_data['weight']['quote'];
            $main_keys = array_keys($weight);
            $deliver_data = array(
    			    'title'      => $weight[$main_keys[0]]['title'],
    			    'cost'      => $weight[$main_keys[0]]['cost'],
    			    'tdeliverycost'      => $weight[$main_keys[0]]['cost'] * $tqty
    			);
			$delivery = (int)$deliver_data['tdeliverycost'];
			$order_data['shipping_method'] = $weight[$main_keys[0]]['title'];
			$shipping_location = $weight[$main_keys[0]]['title'];
		    $order_data['shipping_code'] = $weight[$main_keys[0]]['code'];
    		
		} elseif ( $this->request->post['shippingoption'] == 'pickup') {
		    
    		$order_data['shipping_method'] = $shipmethod_data['pickup']['quote']['title'];
    		$shipping_location = $shipmethod_data['pickup']['quote']['title'];
    		$order_data['shipping_code'] = $shipmethod_data['pickup']['quote']['code'];
    		
		} else {
			$order_data['shipping_method'] = '';
			$shipping_location = '';
			$order_data['shipping_code'] = '';
		}

		
		
		$order_data['affiliate_id'] = 0;
		$order_data['commission'] = 0;
		$order_data['marketing_id'] = 0;
		$order_data['tracking'] = '';
		$order_data['tracking'] = '';
		
		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
		$order_data['currency_code'] = $this->session->data['currency'];
		$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];
		
		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		} else {
			$order_data['user_agent'] = '';
		}
		
		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		} else {
			$order_data['accept_language'] = '';
		}
		
		/**New code 10/12/2019****************************************************************/
        $mheaders = "MIME-Version: 1.0"  . "\r\n";
        $mheaders .= "Content-type:text/html; charset=UTF-8" . "\r\n";		
        //$email = $_REQUEST['email'] ;
        $subject = "Mira Technologies: Check Delivery";
        //$message = $_REQUEST['messg'] ;
        //$message = wordwrap($message, 70);	

        $mheaders .= "From: sales@obejor.com.ng" . "\r\n"; //$_REQUEST['email'];
        $mheaders .= "Reply-To: sales@obejor.com.ng" . "\r\n";		
        $message = "Sub Total = " . $subtotal;
        $message .= "Delivery = " . $delivery;
        $message .= "Tax = " . $taxcost;
        mail("ici@miratechnologiesng.com,icisystemng@gmail.com",$subject,$message, $mheaders);   
		/*end*********************************************************************************/
		
		$finaltotal = $subtotal  + $delivery + $taxcost;
		
		$order_data['comment'] = '';
		 if ($taxcost > 0) {
		     $orderTotal = array(
		        array("code" => "total", "title" => "Total", "value"=> $finaltotal, "sort_order" => 9),
		        array("code" => "sub_total", "title" => "Sub_Total", "value"=> $subtotal, "sort_order" => 1),
		        array("code" => "shipping", "title" => $shipping_location, "value"=> $delivery, "sort_order" => 3),
		        array("code" => "tax", "title" => "International Shipping", "value"=> $taxcost, "sort_order" => 4), //value for shipped from abroad
		      );
		 } else {
		      $orderTotal = array(
		        array("code" => "total", "title" => "Total", "value"=> $finaltotal, "sort_order" => 9),
		        array("code" => "sub_total", "title" => "Sub_Total", "value"=> $subtotal, "sort_order" => 1),
		        array("code" => "shipping", "title" => $shipping_location, "value"=> $delivery, "sort_order" => 3),
		      );
		 }
		$order_data['totals'] = $orderTotal;
		$order_data['total'] = $finaltotal;

		$orderid = $this->model_checkout_order->addOrder($order_data);
		
		
		
		
		$this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($orderid);

        if ($order_info) {
            $reference = uniqid('' . $orderid . '-');
            $json['data']['reference'] = $reference;
            $json['data']['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);/// * 100;
            $json['data']['email'] = $order_info['email'];
            $json['data']['firstname'] = $order_info['firstname'];
            $json['data']['lastname'] = $order_info['lastname'];
            $json['data']['currency'] = $order_info['currency_code'];
            switch ($order_info['currency_code']) {
                case 'GHS':
                    $country = 'GH';
                    break;
                case 'KES':
                    $country = 'KE';
                    break;
                case 'ZAR':
                    $country = 'ZA';
                    break;
                default:
                    $country = 'NG';
                    break;
            }
            $json['data']['country'] = $country;
            $json['modal_logo'] = $this->config->get('payment_rave_modal_logo');
            $json['modal_title'] = $this->config->get('payment_rave_modal_title');
            $json['modal_desc'] = $this->config->get('payment_rave_modal_desc');
            $json['data']['callback_url'] = $this->url->link('extension/payment/rave/callback', 'reference=' . rawurlencode($reference), 'SSL');
            // // return $this->load->view('extension/payment/rave', $data);

        }
		
		
		
		
		
		
		
		$json['data']['orderid'] = $orderid;
		$json['data']['order_data'] = $order_data;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    
}