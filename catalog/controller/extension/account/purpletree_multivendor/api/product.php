<?php
class ControllerExtensionAccountPurpletreeMultivendorApiProduct extends Controller {
	private $error = array();
	public function index() {
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
		 if (isset($this->request->get['id']) && ctype_digit($this->request->get['id'])) {
                $this->getSeller($this->request->get['id']);
            }
	}
	    public function getSeller($id) {
	
        $json = array('success' => false);
			/******* get seller details to show on product page ******/
			 if($this->config->get('module_purpletree_multivendor_status')){
				$this->load->model('extension/purpletree_multivendor/sellerproduct');
				$this->load->model('extension/purpletree_multivendor/vendor');
				$seller_detail = $this->model_extension_purpletree_multivendor_sellerproduct->getSellername($id);
				if($seller_detail){
					$json['data']['seller_review_status'] = $this->config->get('module_purpletree_multivendor_seller_review');
					$seller_detailss = $this->model_extension_purpletree_multivendor_vendor->getStoreDetail($seller_detail['seller_id']);
					$seller_rating = $this->model_extension_purpletree_multivendor_vendor->getStoreRating($seller_detail['seller_id']);
					$json['success'] = true;
					$json['data']['seller_detail'] = array(
						'seller_name' => $seller_detail['store_name'],
						'store_id' => $seller_detail['id'],
						'seller_rating' => (isset($seller_rating['rating'])?$seller_rating['rating']:'0'),
						'seller_count' => (isset($seller_rating['count'])?$seller_rating['count']:'0'),
						'seller_id' => $seller_detail['seller_id']
					);
				}
			} 
					/******* get seller details to show on product page ******/

        $this->sendResponse($json);
    }
	    private function sendResponse($json)
    {
        if ($this->debugIt) {
            echo '<pre>';
            print_r($json);
            echo '</pre>';
        } else {
            $this->response->setOutput(json_encode($json));
        }
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
