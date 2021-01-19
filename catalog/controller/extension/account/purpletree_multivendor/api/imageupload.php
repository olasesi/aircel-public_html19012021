<?php 
class ControllerExtensionAccountPurpletreeMultivendorApiImageupload extends Controller{
	private $error = array(); 
	public $json = array(); 
	
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
		$seller_id 	= $this->customer->getId();
		$seller_folder = "Seller_".$seller_id;
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			//$path = 'admin/ptsseller/';
			$directory = DIR_IMAGE . 'catalog';
			$file = "";
				if (!is_dir($directory . '/' . $seller_folder)) {
							mkdir($directory . '/' . $seller_folder, 0777);
							chmod($directory . '/' . $seller_folder, 0777);
							@touch($directory . '/' . $seller_folder . '/' . 'index.html');
						}
					$directory = DIR_IMAGE . 'catalog'.'/'.$seller_folder;
					if(is_dir($directory)){
                        $allowed_file=array('gif','png','jpg','pdf','doc','docx','zip');
                        $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($_FILES['upload_file']['name'], ENT_QUOTES, 'UTF-8')));
						$extension = pathinfo($filename, PATHINFO_EXTENSION);
						if($filename != '') {
							if(in_array($extension,$allowed_file) ) {
								$file = md5(mt_rand()).'-'.$filename;
								move_uploaded_file($_FILES['upload_file']['tmp_name'], $directory.'/'.$file);
								$json['status'] = 'success';
								$json['message'] = $this->language->get('text_success');
								$json['file'] = 'catalog'.'/'.$seller_folder.'/'.$file;
							}     
						}                                
                    }
		} 
		$this->load->model('tool/image');
		if($file != '') {
			$json['banner_thumb'] = $this->model_tool_image->resize('catalog'.'/'.$seller_folder.'/'.$file, 100, 100);
		} else {
			$json['banner_thumb'] = $this->model_tool_image->resize('catalog/purpletree_banner.jpg', 100, 100);
		}
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