<?php 
class ControllerExtensionAccountPurpletreeMultivendorApiSellerpayment extends Controller{
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
		$this->load->language('purpletree_multivendor/sellerpayment');
		$this->load->model('extension/purpletree_multivendor/sellerpayment');
		
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
		
		 if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		} 
		
		$seller_id = $this->customer->getId();
		
		$json['data']['seller_payments'] = array();
		$filter_data = array(
			'filter_date_from'    => $filter_date_from,
			'filter_date_to' => $filter_date_to,
			'start'                => ($page - 1) * 4,
			'limit'                => 4,
			'seller_id'				=>$seller_id
		);
		$seller_payments = $this->model_extension_purpletree_multivendor_sellerpayment->getPayments($filter_data);
		
		$total_payments = $this->model_extension_purpletree_multivendor_sellerpayment->getTotalPayments($filter_data);
		$curency = $this->config->get('config_currency');
		$currency_detail = $this->model_extension_purpletree_multivendor_sellerpayment->getCurrencySymbol($curency);
		
		if(!empty($seller_payments)){
			foreach($seller_payments as $seller_payment){
				$json['data']['seller_payments'][] = array(
					'transaction_id' => $seller_payment['transaction_id'],
					'amount' => $this->currency->format($seller_payment['amount'], $currency_detail['code'], $currency_detail['value']),
					'payment_mode' => $seller_payment['payment_mode'],
					'status' => $seller_payment['status'],
					'created_at' => date($this->language->get('date_format_short'), strtotime($seller_payment['created_at']))
				);
			}
				$json['status'] = 'success';
		} else {
				$json['status'] = 'success';
				$json['message'] = 'No Data';
		}
		
		//$json['data']['pagination']['total'] = $total_payments;
		//$json['data']['pagination']['page'] = $page;
		//$json['data']['pagination']['limit'] = $this->config->get('config_limit_admin');
		//$json['data']['results'] = sprintf($this->language->get('text_pagination'), ($total_payments) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_payments - $this->config->get('config_limit_admin'))) ? $total_payments : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_payments, ceil($total_payments / $this->config->get('config_limit_admin')));
		
		$json['data']['filter_date_from'] = $filter_date_from;
		$json['data']['filter_date_to'] = $filter_date_to;
		
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