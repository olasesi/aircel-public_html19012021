<?php 
class ControllerExtensionAccountPurpletreeMultivendorApiSellerproduct extends Controller{
	private $error = array();
	private $json = array();
	public function index(){
// 		$this->checkPlugin();

		$this->load->language('purpletree_multivendor/api');
			$json['status'] = false;
			$json['message'] = $this->language->get('no_data');
		if (!$this->customer->isMobileApiCall()) { 
			$json['status'] = false;
			$json['message'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		$this->load->model('extension/purpletree_multivendor/vendor');
		$storeExist = $this->model_extension_purpletree_multivendor_vendor->getStore($this->request->post['storeid']);
		$sellerExist = $this->model_extension_purpletree_multivendor_vendor->isSeller($this->request->post['sellerid']);
		
		if(!isset($storeExist['store_status'])  || !isset($sellerExist['store_status'])  ){
			$json['status'] = false;
			$json['message'] = $this->language->get('seller_not_approved');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json)); 
		}
		if( !($storeExist['id']  == $sellerExist['id'])){
		    	$json['status'] = false;
			$json['message'] = 'Invalid store/seller';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json)); 
		}
		if(!$this->customer->validateSeller()) {		
			$json['status'] = false;
			$json['message'] = $this->language->get('error_license');
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		$this->load->language('purpletree_multivendor/sellerproduct');
		$this->load->language('purpletree_multivendor/metals_spot_price');
		
		$this->load->model('extension/purpletree_multivendor/sellerproduct');
		
		$json = $this->getList();
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}	
	public function productimage(){
			$this->checkPlugin(); 
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
									$json['message'] = $this->language->get('Image uploaded successfully');
									$json['file'] = 'catalog'.'/'.$seller_folder.'/'.$file;
								}     
							}                                
						}
			} 

			$this->load->model('tool/image');
			if($file != '') {
				$json['product_thumb'] = $this->model_tool_image->resize('catalog'.'/'.$seller_folder.'/'.$file, 100, 100);;
			} else {
				$json['product_thumb'] = $this->model_tool_image->resize('image/cache/no_image.jpg', 100, 100);
			}
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
	public function delete() {
		$this->load->language('purpletree_multivendor/api');
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
		$this->load->language('purpletree_multivendor/sellerproduct');
		$this->load->model('extension/purpletree_multivendor/sellerproduct');

		if (isset($this->request->get['product_id']) ) {
			$this->model_extension_purpletree_multivendor_sellerproduct->deleteProduct($this->request->get['product_id']);
			$this->session->data['success'] = $this->language->get('text_success_delete');
			$json['status'] = 'success';
			$json['message'] =  $this->language->get('text_success_delete');
		}
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function add() {
		$this->checkPlugin();
			$this->load->language('purpletree_multivendor/api');
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

		$this->load->language('purpletree_multivendor/sellerproduct');
		$this->load->model('extension/purpletree_multivendor/sellerproduct');
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$requestjson2 = file_get_contents('php://input');
			$requestjson1 = json_decode($requestjson2, true);
			    $requestjson1['seller_id'] = $this->customer->getId();
			    $requestjson1['seller_id'] = $this->customer->getId();
		    	//product_description
				$requestjson1['product_description'][1]['name'] = $requestjson1['name'];
				$requestjson1['product_description'][1]['description'] = $requestjson1['description'];
				$requestjson1['product_description'][1]['tag'] = $requestjson1['tag'];
				$requestjson1['product_description'][1]['meta_title'] =  $requestjson1['meta_title'];
				$requestjson1['product_description'][1]['meta_description'] = $requestjson1['meta_description'];
				$requestjson1['product_description'][1]['meta_keyword'] = $requestjson1['meta_keyword'];
				$requestjson1['product_seo_url'][1]['product_seo_url'] = $requestjson1['product_seo_url'];
				//discount
				$requestjson1['product_discount'][0]['customer_group_id'] = $requestjson1['customer_group_id'];
				$requestjson1['product_discount'][0]['quantity'] = $requestjson1['discount_quantity'];
				$requestjson1['product_discount'][0]['priority'] = $requestjson1['discount_priority'];
				$requestjson1['product_discount'][0]['price'] = $requestjson1['discount_price'];
				$requestjson1['product_discount'][0]['date_start'] = $requestjson1['discount_date_start'];
				$requestjson1['product_discount'][0]['date_end'] = $requestjson1['discount_date_end'];
				//special
				$requestjson1['product_special'][0]['price'] = $requestjson1['special_price'];
				$requestjson1['product_special'][0]['priority'] = $requestjson1['special_priority'];
				$requestjson1['product_special'][0]['customer_group_id'] = $requestjson1['customer_group_idspecial'];
				$requestjson1['product_special'][0]['date_start'] = $requestjson1['special_date_start'];
				$requestjson1['product_special'][0]['date_end'] = $requestjson1['special_date_end'];
				//rewards points
				$requestjson1['product_reward']['points'] = $requestjson1['rewardpoints'];
				$requestjson1['product_reward'][1]['points'] = $requestjson1['points'];
				//SEO
				$requestjson1['product_seo_url'][0][1] = $requestjson1['keyword'];

				$this->model_extension_purpletree_multivendor_sellerproduct->addProduct($requestjson1);
			   //$this->session->data['success'] = $this->language->get('text_success_add');
				$json['status'] = 'success';
				$json['message'] =  $this->language->get('text_success_add');
		}
		$this->getList();
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}
	public function edit() {
		$this->checkPlugin();
			$this->load->language('purpletree_multivendor/api');
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

		$this->load->language('purpletree_multivendor/sellerproduct');
		$this->load->model('extension/purpletree_multivendor/sellerproduct');
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$requestjson2 = file_get_contents('php://input');
			$requestjson1 = json_decode($requestjson2, true);
			//echo"<pre>"; print_r($requestjson1); die;
		        $requestjson1['image'] = $requestjson1['image'];
		       /// echo"<pre>"; print_r($requestjson1['image']); die;
				$requestjson1['product_description'][1]['name'] = $requestjson1['name'];
				$requestjson1['product_description'][1]['description'] = $requestjson1['description'];
				$requestjson1['product_description'][1]['tag'] = $requestjson1['tag'];
				$requestjson1['product_description'][1]['meta_title'] =  $requestjson1['meta_title'];
				$requestjson1['product_description'][1]['meta_description'] = $requestjson1['meta_description'];
				$requestjson1['product_description'][1]['meta_keyword'] = $requestjson1['meta_keyword'];
				$requestjson1['product_seo_url'] = $requestjson1['product_seo_url'];
				$requestjson1['product_special'][0]['price'] = $requestjson1['specialprice'];
				$requestjson1['product_special'][0]['priority'] = $requestjson1['priority'];
				$requestjson1['product_special'][0]['customer_group_id'] = $requestjson1['customer_group_id'];
				$requestjson1['product_special'][0]['date_start'] = $requestjson1['date_start'];
				$requestjson1['product_special'][0]['date_end'] = $requestjson1['date_end'];
				
				$requestjson1['product_discount'][0]['customer_group_id'] = $requestjson1['customer_group_iddiscount'];
				$requestjson1['product_discount'][0]['quantity'] = $requestjson1['quantitydiscount'];
				$requestjson1['product_discount'][0]['priority'] = $requestjson1['prioritydiscount'];
				$requestjson1['product_discount'][0]['price'] = $requestjson1['pricediscount'];
				$requestjson1['product_discount'][0]['date_start'] = $requestjson1['date_startdiscount'];
				$requestjson1['product_discount'][0]['date_end'] = $requestjson1['date_enddiscount'];
				$requestjson1['product_seo_url'][0][1] = $requestjson1['keyword'];
				
				$requestjson1['product_reward']['points'] = $requestjson1['rewardpoints'];
				$requestjson1['product_reward'][1]['points'] = $requestjson1['points'];
				
				$this->model_extension_purpletree_multivendor_sellerproduct->editProduct($requestjson1['product_id'],$requestjson1);
				//$this->session->data['success'] = $this->language->get('text_success_add');
				$json['status'] = 'success';
				$json['message'] =  $this->language->get('text_success_add');
		}
		$this->index();
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}
	public function Productform(){
		$this->checkPlugin();

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
		$this->load->language('purpletree_multivendor/sellerstore');
		$store_id = (isset($store_detail['id'])?$store_detail['id']:'');
		$this->load->model('extension/purpletree_multivendor/vendor');
		
		$store_detail = $this->customer->isSeller();

		if (isset($store_id)) {
			$json['data']['store_id'] = $store_id;
		} else {
			$json['data']['store_id'] = 0;
		}
		
		if (isset($this->session->data['success'])) {
			$json['message'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$json['message'] = '';
		}
		
		if (isset($this->error['store_name'])) {
			$json['messages']['store_name'] = $this->error['store_name'];
			$json['status'] = 'error';
		}
		
		if (isset($this->error['store_seo'])) {
			$json['messages']['store_seo'] = $this->error['store_seo'];
			$json['status'] = 'error';
		} 
		if (isset($this->error['error_file_upload'])) {
			$json['messages']['error_file_upload'] = $this->error['error_file_upload'];
			$json['status'] = 'error';
		}
		
		if (isset($this->error['store_email'])) {
			$json['messages']['store_email'] = $this->error['store_email'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_phone'])) {
			$json['messages']['store_phone'] = $this->error['store_phone'];
			$json['status'] = 'error';
			}
				
		if (isset($this->error['store_address'])) {
			$json['messages']['store_address'] = $this->error['store_address'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_city'])) {
			$json['messages']['store_city'] = $this->error['store_city'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_country'])) {
			$json['messages']['store_country'] = $this->error['store_country'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['error_storezone'])) {
			$json['messages']['error_storezone'] = $this->error['error_storezone'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_zipcode'])) {
			$json['messages']['store_zipcode'] = $this->error['store_zipcode'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_shipping'])) {
			$json['messages']['store_shipping'] = $this->error['store_shipping'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_return'])) {
			$json['messages']['store_return'] = $this->error['store_return'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_meta_keywords'])) {
			$json['messages']['store_meta_keywords'] = $this->error['store_meta_keywords'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_meta_description'])) {
			$json['messages']['store_meta_description'] = $this->error['store_meta_description'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_bank_details'])) {
			$json['messages']['store_bank_details'] = $this->error['store_bank_details'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_tin'])) {
			$json['messages']['store_tin'] = $this->error['store_tin'];
			$json['status'] = 'error';
			}
		
		if (isset($this->error['store_shipping_charge'])) {
			$json['messages']['store_shipping_charge'] = $this->error['store_shipping_charge'];
			$json['status'] = 'error';
			}
		if (isset($this->error['warning'])) {
			$json['message'] = $this->error['warning'];
			$json['status'] = 'error';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		$this->load->model('tool/image');
		$this->load->model('extension/localisation/stock_status');
		$this->load->model('extension/localisation/length_class');
		$this->load->model('extension/purpletree_multivendor/sellerproduct');
		$this->load->model('extension/purpletree_multivendor/customer_group');
		$this->load->model('extension/localisation/weight_class');
		$seller_id = $this->customer->getId();
		if (isset($seller_id) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$product_infoq = $this->model_extension_purpletree_multivendor_sellerproduct->getProduct($this->request->get['product_id'],$seller_id);
			$json['data']['stock_status'] = $this->model_extension_localisation_stock_status->getStockStatuses();
			$json['data']['product_attributes'] = $this->model_extension_purpletree_multivendor_sellerproduct->getAttributes();
			$json['data']['product_lengthclasses'] = $this->model_extension_localisation_length_class->getLengthClasses();
			$json['data']['weight_classes'] = $this->model_extension_localisation_weight_class->getWeightClasses();
			$json['data']['customer_groups'] = $this->model_extension_purpletree_multivendor_customer_group->getCustomerGroups();
			$json['data']['product_reward'] = $this->model_extension_purpletree_multivendor_sellerproduct->getProductRewards($this->request->get['product_id']);

		}
        if (isset($this->request->post['product_special'])) {
			$product_specials = $this->request->post['product_special'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_specials = $this->model_extension_purpletree_multivendor_sellerproduct->getProductSpecials($this->request->get['product_id']);
		} else {
			$product_specials = array();
		}
		$data['product_specials'] = array();

		foreach ($product_specials as $product_special) {
		$json['data']['product_specials'] = array(
				'customer_group_id' => $product_special['customer_group_id'],
				'priority'          => $product_special['priority'],
				'price'             => $product_special['price'],
				'date_start'        => ($product_special['date_start'] != '0000-00-00') ? $product_special['date_start'] : '',
				'date_end'          => ($product_special['date_end'] != '0000-00-00') ? $product_special['date_end'] :  ''
			);
		}
		if (isset($this->request->post['product_discount'])) {
			$product_discounts = $this->request->post['product_discount'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_discounts = $this->model_extension_purpletree_multivendor_sellerproduct->getProductDiscounts($this->request->get['product_id']);
		} else {
			$product_discounts = array();
		}

    	$json['data']['product_discounts'] = array();

		foreach ($product_discounts as $product_discount) {
			$json['data']['product_discounts'] = array(
				'customer_group_id' => $product_discount['customer_group_id'],
				'quantity'          => $product_discount['quantity'],
				'priority'          => $product_discount['priority'],
				'price'             => $product_discount['price'],
				'date_start'        => ($product_discount['date_start'] != '0000-00-00') ? $product_discount['date_start'] : '',
				'date_end'          => ($product_discount['date_end'] != '0000-00-00') ? $product_discount['date_end'] : ''
			);
		}
		$filter_data = "";
		$json['data']['product_category'] = array();
		$results = $this->model_extension_purpletree_multivendor_sellerproduct->getCategories($filter_data);
		foreach($results as $result) {
			$json['data']['product_category'][] = array(
					'category_id' => $result['category_id'],
					'name'        => $result['name']
			);
		}
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$json['data']['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 50, 50);
		} elseif (!empty($product_infoq) && is_file(DIR_IMAGE . $product_infoq['image'])) {
			$json['data']['thumb'] = $this->model_tool_image->resize($product_infoq['image'], 50, 50);
		} else {
			$json['data']['thumb'] = $this->model_tool_image->resize('no_image.png', 50, 50);
		}
		
		if (isset($this->request->post['product_image'])) {
			$product_images = $this->request->post['product_image'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_images = $this->model_extension_purpletree_multivendor_sellerproduct->getProductImages($this->request->get['product_id']);
		} else {
			$product_images = array();
		}

		$json['data']['product_images'] = array();

		foreach ($product_images as $product_image) {
			if (is_file(DIR_IMAGE . $product_image['image'])) {
				$image = $product_image['image'];
				$thumb = $product_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
		}

		$json['data']['product_images']= array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $product_image['sort_order']
			);
		}
		
		if (isset($this->request->post['keyword'])) {
			$json['data']['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($product_infoq)) {
			$json['data']['keyword'] = $product_infoq['keyword'];
		} else {
			$json['data']['keyword'] = '';
		}
		if (isset($this->request->post['model'])) {
			$json['data']['model'] = $this->request->post['model'];
		} elseif (!empty($product_infoq)) {
			$json['data']['model'] = $product_infoq['model'];
		} else {
			$json['data']['model'] = '';
		}
		if (isset($this->request->post['name'])) {
			$json['data']['name'] = $this->request->post['name'];
		} elseif(!empty($product_infoq)) {
			$json['data']['name'] = $product_infoq['name'];
		} else {
		    $json['data']['name'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['sku'] = $product_infoq['sku'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['sku'] = $this->request->get['sku'];
		} else {
			$json['data']['sku'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['ean'] = $product_infoq['ean'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['ean'] = $this->request->get['ean'];
		} else {
			$json['data']['ean'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['jan'] = $product_infoq['jan'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['jan'] = $this->request->get['jan'];
		} else {
			$json['data']['jan'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['isbn'] = $product_infoq['isbn'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['isbn'] = $this->request->get['isbn'];
		} else {
			$json['data']['isbn'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['mpn'] = $product_infoq['mpn'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['mpn'] = $this->request->get['mpn'];
		} else {
			$json['data']['mpn'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['location'] = $product_infoq['location'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['location'] = $this->request->get['location'];
		} else {
			$json['data']['location'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['quantity'] = $product_infoq['quantity'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['quantity'] = $this->request->get['quantity'];
		} else {
			$json['data']['quantity'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['stock_status_id'] = $product_infoq['stock_status_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['stock_status_id'] = $this->request->get['stock_status_id'];
		} else {
			$json['data']['stock_status_id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['image'] = $product_infoq['image'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['image'] = $this->request->get['image'];
		} else {
			$json['data']['image'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['manufacturer_id'] = $product_infoq['manufacturer_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['manufacturer_id'] = $this->request->get['manufacturer_id'];
		} else {
			$json['data']['manufacturer_id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['shipping'] = $product_infoq['shipping'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['shipping'] = $this->request->get['shipping'];
		} else {
			$json['data']['shipping'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['price'] = $product_infoq['price'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['price'] = $this->request->get['price'];
		} else {
			$json['data']['price'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['price_extra_type'] = $product_infoq['price_extra_type'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['price_extra_type'] = $this->request->get['price_extra_type'];
		} else {
			$json['data']['price_extra_type'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['price_extra'] = $product_infoq['price_extra'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['price_extra'] = $this->request->get['price_extra'];
		} else {
			$json['data']['price_extra'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['points'] = $product_infoq['points'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['points'] = $this->request->get['points'];
		} else {
			$json['data']['points'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['tax_class_id'] = $product_infoq['tax_class_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['tax_class_id'] = $this->request->get['tax_class_id'];
		} else {
			$json['data']['tax_class_id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['weight'] = $product_infoq['weight'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['weight'] = $this->request->get['weight'];
		} else {
			$json['data']['weight'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['date_available'] = $product_infoq['date_available'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['date_available'] = $this->request->get['date_available'];
		} else {
			$json['data']['date_available'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['weight_class_id'] = $product_infoq['weight_class_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['weight_class_id'] = $this->request->get['weight_class_id'];
		} else {
			$json['data']['weight_class_id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['length'] = $product_infoq['length'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['length'] = $this->request->get['length'];
		} else {
			$json['data']['length'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['width'] = $product_infoq['width'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['width'] = $this->request->get['width'];
		} else {
			$json['data']['width'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['height'] = $product_infoq['height'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['height'] = $this->request->get['height'];
		} else {
			$json['data']['height'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['length_class_id'] = $product_infoq['length_class_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['length_class_id'] = $this->request->get['length_class_id'];
		} else {
			$json['data']['length_class_id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['subtract'] = $product_infoq['subtract'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['subtract'] = $this->request->get['subtract'];
		} else {
			$json['data']['subtract'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['minimum'] = $product_infoq['minimum'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['minimum'] = $this->request->get['minimum'];
		} else {
			$json['data']['minimum'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['sort_order'] = $product_infoq['sort_order'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['sort_order'] = $this->request->get['sort_order'];
		} else {
			$json['data']['sort_order'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['status'] = $product_infoq['status'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['status'] = $this->request->get['status'];
		} else {
			$json['data']['status'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['viewed'] = $product_infoq['viewed'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['viewed'] = $this->request->get['viewed'];
		} else {
			$json['data']['viewed'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['date_added'] = $product_infoq['date_added'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['date_added'] = $this->request->get['date_added'];
		} else {
			$json['data']['date_added'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['date_modified'] = $product_infoq['date_modified'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['date_modified'] = $this->request->get['date_modified'];
		} else {
			$json['data']['date_modified'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['language_id'] = $product_infoq['language_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['language_id'] = $this->request->get['language_id'];
		} else {
			$json['data']['language_id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['description'] = $product_infoq['description'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['description'] = $this->request->get['description'];
		} else {
			$json['data']['description'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['tag'] = $product_infoq['tag'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['tag'] = $this->request->get['tag'];
		} else {
			$json['data']['tag'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['meta_title'] = $product_infoq['meta_title'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['meta_title'] = $this->request->get['meta_title'];
		} else {
			$json['data']['meta_title'] = '';
		}
				if (!empty($product_infoq)) {
			$json['data']['meta_description'] = $product_infoq['meta_description'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['meta_description'] = $this->request->get['meta_description'];
		} else {
			$json['data']['meta_description'] = '';
		}
				if (!empty($product_infoq)) {
			$json['data']['meta_keyword'] = $product_infoq['meta_keyword'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['meta_keyword'] = $this->request->get['meta_keyword'];
		} else {
			$json['data']['meta_keyword'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['id'] = $product_infoq['id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['id'] = $this->request->get['id'];
		} else {
			$json['data']['id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['seller_id'] = $product_infoq['seller_id'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['seller_id'] = $this->request->get['seller_id'];
		} else {
			$json['data']['seller_id'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['is_featured'] = $product_infoq['is_featured'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['is_featured'] = $this->request->get['is_featured'];
		} else {
			$json['data']['is_featured'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['is_category_featured'] = $product_infoq['is_category_featured'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['is_category_featured'] = $this->request->get['is_category_featured'];
		} else {
			$json['data']['is_category_featured'] = '';
		}
		if (!empty($product_infoq)) {
			$json['data']['is_approved'] = $product_infoq['is_approved'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['is_approved'] = $this->request->get['is_approved'];
		} else {
			$json['data']['is_approved'] = '';
		}
				if (!empty($product_infoq)) {
			$json['data']['created_at'] = $product_infoq['created_at'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['created_at'] = $this->request->get['created_at'];
		} else {
			$json['data']['created_at'] = '';
		}
				if (!empty($product_infoq)) {
			$json['data']['updated_at'] = $product_infoq['updated_at'];
		} elseif(isset($this->request->get['seller_id'])) {
			$json['data']['updated_at'] = $this->request->get['updated_at'];
		} else {
			$json['data']['updated_at'] = '';
		}
		      	
		// End download document file of store
		$json['status'] = 'success';
		$this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
	}
	protected function getForm() {
	    $this->load->model('extension/localisation/stock_status');
	    $this->load->model('extension/purpletree_multivendor/sellerproduct');
	    $this->load->model('extension/purpletree_multivendor/customer_group');
		$json['data']['stock_status'] = $this->model_extension_localisation_stock_status->getStockStatuses();
		$json['data']['product_attributes'] = $this->model_extension_purpletree_multivendor_sellerproduct->getAttributes();
		$json['data']['customer_groups'] = $this->model_extension_purpletree_multivendor_customer_group->getCustomerGroups();
		
		$json['data']['text_form'] = $this->language->get('text_add') ;
			$data['seller_id'] = $this->customer->getId();
			


		if (isset($this->error['warning'])) {
			$json['status'] = 'error';
			$json['message'] = $this->error['warning'];
			return $json;
		} else {
			$json['message'] = '';
		}

       if (isset($this->error['name'])) {
		    $json['status'] = 'error';
			$json['message'] = $this->error['name']; 
			return $json;
		} else {
			$json['message'] = '';
		}

		$json['status'] = 'success';
		return $json;
	}
	protected function getList(){
		
		//$json['data']['text_all'] = $this->language->get('text_all');
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		 if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		} 
		
		if (isset($this->error['warning'])) {
			$json['status'] = 'error';
			$json['message'] = $this->error['warning'];
			return $json;
		}
		
		if (isset($this->session->data['success'])) {
			$json['status'] = 'success';
			$json['message'] = $this->session->data['success'];
			return $json;
			
			unset($this->session->data['success']);
		}
		
		if (isset($this->session->data['error_warning'])) {
			$json['status'] = 'error';
			$json['message'] = $this->error['warning'];
			return $json;

			unset($this->session->data['error_warning']);
		}
		$json['data']['products'] = array();

	
		$filter_data = array(
			'seller_id'		  => $this->request->post['sellerid']	
		);
		$product_total = $this->model_extension_purpletree_multivendor_sellerproduct->getTotalSellerProducts($filter_data);
		$seller_id = $this->request->post['sellerid'];
		$filter_data = array(
			'filter_name'	  => $filter_name,
			//'filter_model'	  => $filter_model,
			//'filter_price'	  => $filter_price,
			//'filter_quantity' => $filter_quantity,
			//'filter_status'   => $filter_status,
			//'sort'            => $sort,
			//'order'           => $order,
			'start'           => ($page - 1) * 100,
			'limit'           => 100,
			'seller_id'		  => $this->request->post['sellerid']	
		);
		$results = $this->model_extension_purpletree_multivendor_sellerproduct->getSellerProducts($filter_data);
		
		$this->load->model('tool/image');
		
		if(!empty($results)) {
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 200, 200);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 200, 200);
			}

			$special = false;

			$product_specials = $this->model_extension_purpletree_multivendor_sellerproduct->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $product_special['price'];

					break;
				}
			}
			 $price_extra = 0;
			if($result['price_extra_type'] == 1){$price_extra = $result['price_extra'];}
			elseif($result['price_extra_type'] == 2){$price_extra = $result['price'] * $result['price_extra']/100;}
			elseif(!$result['price_extra_type']){
			if($result['metal'] == 1 && $metals_extra_price_default[0] > 0){ // Gold
			$price_extra = $result['price'] * $metals_extra_price_default[0]/100;
			}
			if($result['metal'] == 2 && $metals_extra_price_default[1] > 0){ // Silver
			$price_extra = $result['price'] * $metals_extra_price_default[1]/100;
			}
			if($result['metal'] == 3 && $metals_extra_price_default[2] > 0){ // Platinum
			$price_extra = $result['price'] * $metals_extra_price_default[2]/100;
			}
			if($result['metal'] == 4 && $metals_extra_price_default[3] > 0){ // Palladium
			$price_extra = $result['price'] * $metals_extra_price_default[3]/100;
			}
			if($result['metal'] == 5 && $metals_extra_price_default[4] > 0){ // Copper
			$price_extra = $result['price'] * $metals_extra_price_default[4]/100;
			}
			if($result['metal'] == 6 && $metals_extra_price_default[5] > 0){ // Rhodium
			$price_extra = $result['price'] * $metals_extra_price_default[5]/100;
			}
			}
			$price = $this->currency->format($result['price'] + $price_extra,  $this->config->get('config_currency'), '', false);

			$json['data']['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				// 'price'      => $result['price'],
				'price'      => $price,
				// 'price1'      => $this->currency->format($price, $result['currency_code'], $result['currency_value']),
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'is_approved'     => $result['is_approved'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
			);
		}
		$json['message'] = 'Products found!';
		$json['status'] = true;
		} else {
			$json['message'] = 'No Data';
			$json['status'] = true;
		}
		/* if (isset($this->request->post['selected'])) {
			$json['data']['selected'] = (array)$this->request->post['selected'];
		} else {
			$json['data']['selected'] = array();
		} */
		//$json['data']['pagination']['total'] = $product_total;
		//$json['data']['pagination']['page'] = $page;
		//$json['data']['pagination']['limit'] = $this->config->get('config_limit_admin');

		//$json['data']['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
		
		$json['data']['total'] = $product_total;
		$json['data']['filter_name'] = $filter_name;
		//$json['data']['filter_model'] = $filter_model;
		//$json['data']['filter_price'] = $filter_price;
		//$json['data']['filter_quantity'] = $filter_quantity;
		//$json['data']['filter_status'] = $filter_status;

// 		$json['data']['sort'] = $sort;
// 		$json['data']['order'] = $order;
		
		
		return $json;
	}

 private function checkPlugin() {
		header('Access-Control-Allow-Origin:*');
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		header('Access-Control-Max-Age: 286400');
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Headers: purpletreemultivendor,Purpletreemultivendor,PURPLETREEMULTIVENDOR,xocmerchantid,XOCMERCHANTID,Xocmerchantid,XOCSESSION,xocsession,Xocsession,content-type,CONTENT-TYPE');
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