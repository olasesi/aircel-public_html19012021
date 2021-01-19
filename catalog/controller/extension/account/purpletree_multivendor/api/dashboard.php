<?php 
class ControllerExtensionAccountPurpletreeMultivendorApiDashboard extends Controller {
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
		$this->load->language('purpletree_multivendor/dashboard');
		$this->load->model('extension/purpletree_multivendor/dashboard');
		
		$json['data']['seller_orders'] = array();
		

		if (isset($this->session->data['error_warning'])) {
			$json['status'] = 'error_warning';
			$json['message'] = $this->error['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$json['message'] = '';
		}

		$filter_data = array(
			'limit'                => $this->config->get('config_limit_admin'),
			'seller_id'            => $this->customer->getId()
		);
		$orderstatus = 0;
			if(null !== $this->config->get('module_purpletree_multivendor_commission_status')) {
				$orderstatus = $this->config->get('module_purpletree_multivendor_commission_status');
			} else {
				$json['status'] = 'error_warning';
			    $json['message'] = $this->$this->language->get('module_purpletree_multivendor_commission_status_warning');
				
			}
		$filter_data1 = array(
			'seller_id'            => $this->customer->getId(),
			'order_status_id'            =>  $orderstatus
		);
		$seller_id = $this->customer->getId();
		
		$json['data']['total_sale'] = 0;
		$json['data']['total_commission'] = 0;
		
		$total_commission = 0;
		
		$total_sale = 0;
		$orderstatus = 0;
			if(null !== $this->config->get('module_purpletree_multivendor_commission_status')) {
				$orderstatus = $this->config->get('module_purpletree_multivendor_commission_status');
			} else {
				$json['status'] = 'error_warning';
			    $json['message'] = $this->$this->language->get('module_purpletree_multivendor_commission_status_warning');
			}
		$total_commission1 = $this->model_extension_purpletree_multivendor_dashboard->getTotalSellerOrderscommission($this->customer->getId(),$orderstatus);
		if(!empty($total_commission1)) {
			foreach($total_commission1 as $tot) {
					$total_commission+= $tot['commission'];
			}	
		}
		
		$sellerstore = $this->customer->isSeller();
		
		$json['data']['order_total'] = $this->model_extension_purpletree_multivendor_dashboard->getTotalSellerOrders($filter_data);
		
		$results = $this->model_extension_purpletree_multivendor_dashboard->getSellerOrders($filter_data);
		$seller_commissions = $this->model_extension_purpletree_multivendor_dashboard->getCommissions($filter_data);
		$curency = $this->config->get('config_currency');
		$currency_detail = $this->model_extension_purpletree_multivendor_dashboard->getCurrencySymbol($curency);
		
		$seller_payments = $this->model_extension_purpletree_multivendor_dashboard->getPayments($filter_data);
		$stotal_payments = $this->model_extension_purpletree_multivendor_dashboard->getTotalPayments($filter_data);
		$json['data']['total_payments'] = $this->currency->format($stotal_payments, $currency_detail['code'], $currency_detail['value']);
		$pending_payments = $this->model_extension_purpletree_multivendor_dashboard->pendingPayments($filter_data1);
		$totalpaymentss = 0;
		$orderstatus = 0;
			if(null !== $this->config->get('module_purpletree_multivendor_commission_status')) {
				$orderstatus = $this->config->get('module_purpletree_multivendor_commission_status');
			} else {
				$json['status'] = 'error_warning';
			    $json['message'] = $this->$this->language->get('module_purpletree_multivendor_commission_status_warning');
				
			}
		if(!empty($pending_payments)) {
			foreach($pending_payments as $paymentsss) {
				//print_r($paymentsss); //die;
				if($paymentsss['seller_order_status'] == $paymentsss['admin_order_status'] && $paymentsss['seller_order_status'] == $orderstatus && $paymentsss['admin_order_status'] == $orderstatus) {
					$totalpaymentss += $paymentsss['total_price'];
				}
			}
		}
		$json['data']['total_sale'] =$this->currency->format($this->model_extension_purpletree_multivendor_dashboard->getTotalsale($filter_data), $currency_detail['code'], $currency_detail['value']);
		$json['data']['seller_payments'] = array();
		$json['data']['seller_commissions'] = array();
	if($seller_payments){
			foreach($seller_payments as $seller_payment){
				$json['data']['seller_payments'] = array(
					'transaction_id' => $seller_payment['transaction_id'],
					'amount' => $this->currency->format($seller_payment['amount'], $currency_detail['code'], $currency_detail['value']),
					'payment_mode' => $seller_payment['payment_mode'],
					'status' => $seller_payment['status'],
					'created_at' => date($this->language->get('date_format_short'), strtotime($seller_payment['created_at']))
				);
				break;
			}
		}
		if($seller_commissions){
			foreach($seller_commissions as $seller_commission){
				$json['data']['seller_commissions'] = array(
					'order_id' => $seller_commission['order_id'],
					'product_name' => $seller_commission['name'],
					'price' => $this->currency->format($seller_commission['total_price'], $currency_detail['code'], $currency_detail['value']),
					'commission' => $this->currency->format($seller_commission['commission'], $currency_detail['code'], $currency_detail['value']),
					'created_at' => date($this->language->get('date_format_short'), strtotime($seller_commission['created_at']))
				);
					break;
			}
		}
	
		foreach ($results as $result) {

			 $total ='';
				$product_totals  = $this->model_extension_purpletree_multivendor_dashboard->getSellerOrdersTotal($seller_id,$result['order_id']);

				if(isset($product_totals['total'])){
					$total = $product_totals['total'];
				} else {
					$total = 0;
				}
				
				$product_commission  = $this->model_extension_purpletree_multivendor_dashboard->getSellerOrdersCommissionTotal($result['order_id'],$seller_id);
			
			//$total_sale+= $total;
			
			//$total_commission+= $product_commission['total_commission'];
		
			$json['data']['seller_orders'] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'admin_order_status'      => $result['admin_order_status'],
				'order_status'  => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'         => $this->currency->format($total, $result['currency_code'], $result['currency_value']),
				'commission'         => $this->currency->format($product_commission['total_commission'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified']))
			);
			break;
		}
		 $penpayems = $totalpaymentss - $stotal_payments - $total_commission;
		$json['data']['pending_payments'] = $this->currency->format($penpayems, $currency_detail['code'], $currency_detail['value']);
		
		
		
        $json['data']['total_order_commission'] = $this->currency->format($total_commission, $currency_detail['code'], $currency_detail['value']);
		
		
		
		
		$this->load->model('extension/localisation/order_status');
		//$json['data']['order_statuses'] = $this->model_extension_localisation_order_status->getOrderStatuses();
		
        $json['status'] = 'success';
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
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