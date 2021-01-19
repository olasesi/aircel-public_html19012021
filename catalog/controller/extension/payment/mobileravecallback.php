<?php
class ControllerExtensionPaymentMobileravecallback extends Controller {
    public function index()
    {
        $this->language->load('extension/payment/rave');

        
        $data['text_testmode'] = $this->language->get('text_testmode');
        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['livemode'] = $this->config->get('payment_rave_live');

        if ($this->config->get('payment_rave_live')) {
            $data['public_key'] = $this->config->get('payment_rave_live_public_key');
            $data['script_tag'] = 'https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js';
        } else {
            $data['public_key'] = $this->config->get('payment_rave_test_public_key');
            $data['script_tag'] = 'https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/flwpbf-inline.js';

        }
        
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($order_info) {

            $data['reference'] = uniqid('' . $this->session->data['order_id'] . '-');
            $data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);/// * 100;
            $data['email'] = $order_info['email'];
            $data['firstname'] = $order_info['firstname'];
            $data['lastname'] = $order_info['lastname'];
            $data['currency'] = $order_info['currency_code'];
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
            $data['country'] = $country;
            $data['modal_logo'] = $this->config->get('payment_rave_modal_logo');
            $data['modal_title'] = $this->config->get('payment_rave_modal_title');
            $data['modal_desc'] = $this->config->get('payment_rave_modal_desc');
            $data['callback_url'] = $this->url->link('extension/payment/mobileravecallback/callback', 'reference=' . rawurlencode($data['reference']), 'SSL');
            return $this->load->view('extension/payment/mobileravecallback', $data);

        }
    }

    protected function verify_payment($reference)
    {
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


    public function callback()
    {
        
        if (isset($this->request->get['reference']) && (isset($this->request->get['flw_reference']) || isset($this->request->get['flwref']))) {
          $reference = $this->request->get['reference'];
          $flw_reference = isset($this->request->get['flw_reference']) ?$this->request->get['flw_reference'] : $this->request->get['flwref'];
           
            
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
                
                $paymentstatus = array("status" => "","message" => "","order_status" => "");
                $tstatus = true;
                $pmessage = "";
                $order_status_id = $this->config->get('payment_config_order_status_id');
                if ($result->data->flwMeta->chargeResponse === '00' || $result->data->flwMeta->chargeResponse === '0') {
                    if($amount ==  $result->data->amount){
                        $order_status_id = $this->config->get('payment_rave_approved_status_id');
                        //$redir_url = $this->url->link('checkout/success');
                        $tstatus = true;
                        $pmessage = '<h3>Order Confirmation</h3><p>Your order was successfully submitted. A confirmation email has just been sent to you, kindly check your e-mail inbox or Spam folder for confirmation, Our Customer Service may contact you shortly to verify your order.</p><h3>Shipping</h3><p>You will receive an update about your order when it has been shipped.</p><h3>For inquiry</h3><p>Please direct any questions you have concerning this order to our customer help-line(help@obejor.com/07040002622) <br>Thanks for shopping with Obejor.</p>';
                        $porder_status = $order_status_id;
                    }else{
                        $order_status_id = $this->config->get('payment_rave_error_status_id');
                        //$redir_url = $this->url->link('checkout/checkout', 'Invalid amount paid', 'SSL');
                        $tstatus = false;
                        $pmessage = "INCOMPLETE PAYMENT";
                        $porder_status = $order_status_id;
                    }
                    
                } else {
                    $order_status_id = $this->config->get('payment_rave_error_status_id');
                    //$redir_url = $this->url->link('checkout/checkout', '', 'SSL');
                    $tstatus = false;
                    $pmessage = "NOT SUCCESSFUL ";
                    $porder_status = $order_status_id;
                }
 
                $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, "Transaction reference: ".$reference, true);
                
                $paymentstatus["status"] = $tstatus;
                $paymentstatus["message"] = $pmessage;
                $paymentstatus["order_status"] = $porder_status;

                $this->response->addHeader('Content-Type: application/json');
        		$this->response->setOutput(json_encode($paymentstatus));

                //$this->response->redirect($redir_url);
            }
        }
    }

}
