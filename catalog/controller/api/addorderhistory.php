<?php
class ControllerApiAddorderhistory extends Controller {
    public function index() {
		$json = array();
		/*if(isset($this->request->post['order_id']) AND ($this->request->post['order_id'] != null) AND ($this->request->post['order_id'] != 0)){
		    $json['value'] = "VALUE";
		}else{
		    $json['value'] = "EMPTY";
		}*/
		$order_id = $this->request->post['order_id'];
// 		$json['order_id'] = $order_id;
		//$json['order_info'] = $this->model_checkout_order->getOrder($order_id);
		$successMessage = '<h3>Order Confirmation</h3><p>Your order was successfully submitted. A confirmation email has just been sent to you, kindly check your e-mail inbox or Spam folder for confirmation, Our Customer Service may contact you shortly to verify your order.</p><h3>Shipping</h3><p>You will receive an update about your order when it has been shipped.</p><h3>For inquiry</h3><p>Please direct any questions you have concerning this order to our customer help-line(help@obejor.com/07040002622) <br>Thanks for shopping with Obejor.</p>';;
		
		$paymentoption = $this->request->post['paymentoption'];
		$this->load->model('checkout/order');
		if ($this->request->post['paymentoption'] == 'cod') {
			
			if (isset($order_id) && $order_id > 0 && $order_id != 0) {
		    	$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_cod_order_status_id'), $comment = '', $notify = true, $override = false);
    		    $successMessage = '<h3>Order Confirmation</h3><p>Your order was successfully submitted. A confirmation email has just been sent to you, kindly check your e-mail inbox or Spam folder for confirmation, Our Customer Service may contact you shortly to verify your order.</p><h3>Shipping</h3><p>You will receive an update about your order when it has been shipped.</p><h3>For inquiry</h3><p>Please direct any questions you have concerning this order to our customer help-line(help@obejor.com/07040002622) <br>Thanks for shopping with Obejor.</p>';;
    		    $json['status'] = true;
    			$json['message'] = $successMessage;

                //added 20/01/2020 ici
                //$this->load->model('checkout/order');
                $order_info = $this->model_checkout_order->getOrder($order_id);
        
		        $bmessg = "Your order, No: " . $order_id . " on Obejor.com.ng has been received. We shall contact you with more info about delivery. Thank you for shopping at Obejor.";
		        $postdata = "api_token=TAqD7Yl9v9dZzoVytQMWxCHK2uD86GiNqyyyrrvm9Rw4pLgWPWl8j6FhKThg&from=Obejor&to=" . $order_info['telephone'] . "&body=" . $bmessg . "&dnd=3";
		
		        $ch = curl_init();
        		curl_setopt($ch, CURLOPT_URL, "https://www.bulksmsnigeria.com/api/v1/sms/create");
        		//curl_setopt($ch, CURLOPT_URL, "http://www.estoresms.com/smsapi.php?");
        		//curl_setopt($ch, CURLOPT_HEADER, 1);
        		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        		curl_setopt ($ch, CURLOPT_POST, 1); 
        		curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	            curl_exec($ch);
		        curl_close($ch);		
		        //end    

    			
			} else {
			    $json['status'] = false;
			}
			
		} elseif ($this->request->post['paymentoption'] == 'rave') {
            $referencet = $this->request->post['reference'];
            $flw_referencet = $this->request->post['flw_reference'];
            $tmessg = "Order ID: " . $order_id . " REF: " . $referencet . " FLW Ref: " . $flw_referencet;
		    	if (isset($order_id) && $order_id > 0) {
		    	    $json['status'] = true;
		    	    $returnValue = $this->callback();
    		        if (isset($returnValue['orderid']) && $returnValue['orderid'] > 0) {
                        $this->model_checkout_order->addOrderHistory($returnValue['orderid'], $returnValue['orderstatusid'],$returnValue['comment'], $notify = true, $override = false);
    		        } else {
	    	            $json['status'] = false;
		        	}
    		        $json['data'] = $returnValue['message'];
    		        $json['message'] = $successMessage . " " . $tmessg;

                    //added 20/01/2020 ici
                    //$this->load->model('checkout/order');
                    $order_info = $this->model_checkout_order->getOrder($order_id);
        
		            $bmessg = "Your order, No: " . $order_id . " on Obejor.com.ng has been received. We shall contact you with more info about delivery. Thank you for shopping at Obejor.";
		            $postdata = "api_token=TAqD7Yl9v9dZzoVytQMWxCHK2uD86GiNqyyyrrvm9Rw4pLgWPWl8j6FhKThg&from=Obejor&to=" . $order_info['telephone'] . "&body=" . $bmessg . "&dnd=3";
		
		            $ch = curl_init();
        		    curl_setopt($ch, CURLOPT_URL, "https://www.bulksmsnigeria.com/api/v1/sms/create");
        		    //curl_setopt($ch, CURLOPT_URL, "http://www.estoresms.com/smsapi.php?");
        		    //curl_setopt($ch, CURLOPT_HEADER, 1);
        		    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        		    curl_setopt ($ch, CURLOPT_POST, 1); 
        		    curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	                curl_exec($ch);
		            curl_close($ch);		
		            //end    
    		        
		    	} else {
	    	        $json['status'] = false;
		    	}
		     
		} else {
		    $json['status'] = false;
		}
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
    public function verify_payment($reference) {
        if ($this->config->get('payment_rave_live')) {
            $url =  'https://api.ravepay.co/flwv3-pug/getpaidx/api/verify';
            $secret_key = $this->config->get('payment_rave_live_secret_key');
            
        } else {
            $url =  'https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/verify';
            $secret_key = $this->config->get('payment_rave_test_secret_key');
            
        }
        
        $response = [];
        $postdata = array(
            'flw_ref' => $reference,
            'SECKEY' => $secret_key,
            // 'SECKEY' =>  $secret_key = $this->config->get('payment_rave_test_secret_key'),
          'sslverify' => false
        );
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));                                              
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $headers = [
            'Content-Type: application/json',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        $result =  json_decode($response);
        return $result;
    }
    
    public function callback() {
        $demo = array();
        $reference = $this->request->post['reference'];
        $flw_reference = $this->request->post['flw_reference'];
        
        // $reference = '278-5d971f7700387';
        // $flw_reference = 'FLW-MOCK-079ccd3a68b1752198147d53cdd1f58a';
       
        
        // order id is what comes before the first dash in trxref
        $order_id = substr($reference, 0, strpos($reference, '-'));
        // if no dash were in transation reference, we will have an empty order_id
        if(!$order_id) {
            $order_id = 0;
        }
        
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        
        
        if ($order_info) {

                if ($this->config->get('rave_debug')) {
                    $this->log->write('rave :: CALLBACK DATA: ' . print_r($this->request->get, true));
                }
                $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
                $result = $this->verify_payment($flw_reference);
                
                $this->load->model('localisation/order_status');
                $order_status_id = $this->config->get('payment_config_order_status_id');
                if ($result->data->flwMeta->chargeResponse === '00' || $result->data->flwMeta->chargeResponse === '0') {
                    if($amount ==  $result->data->amount){
                        
                        
                        $order_status_id = $this->config->get('payment_rave_approved_status_id');
                        $demo['order_status_id'] = $this->model_localisation_order_status->getOrderStatus($order_status_id);
                        $demo['message'] = $result->message;
                        $redir_url = $this->url->link('checkout/success');
                    }else{
                        
                        $order_status_id = $this->config->get('payment_rave_error_status_id');
                        $demo['order_status_id'] = $this->model_localisation_order_status->getOrderStatus($order_status_id);
                        $demo['message'] = $result->message;
                        $redir_url = $this->url->link('checkout/checkout', 'Invalid amount paid', 'SSL');
                    }
                    
                } else {
                        $demo['order_status_id'] = $order_status_id = $this->config->get('payment_rave_error_status_id');
                        $demo['message'] = $result->message;
                        $redir_url = $this->url->link('checkout/checkout', '', 'SSL');
                }
                $demo['orderid'] = $order_id;
                $demo['orderstatusid'] = $order_status_id;
                $demo['comment'] = "Transaction reference: ".$reference;
                $demo['message'] = $result;
                // $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, "Transaction reference: ".$reference, $notify = true, $override = false);
            }
            return $demo;
    
    }
}