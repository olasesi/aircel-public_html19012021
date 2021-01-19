<?php 
class ControllerExtensionAccountPurpletreeMultivendorSellertemplateproduct extends Controller{
		
	private $error = array();
	
	public function index(){
		
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}else{
			        if(isset($store_detail['store_status']) && $store_detail[  'multi_store_id'] != $this->config->get('config_store_id')){	
						$this->response->redirect($this->url->link('account/account','', true));
				    }
		        }
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');			
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		if(!$this->config->get('module_purpletree_multivendor_seller_product_template')){
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true));
		}
		
		$this->load->language('purpletree_multivendor/sellertemplateproduct');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		 
		$this->load->model('extension/purpletree_multivendor/sellertemplateproduct');
		
		$this->getList();
	}	
	public function edit() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');			
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true));
		}
		if(!isset($this->request->get["template_id"])) {
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true));
		}

		//$this->load->language('purpletree_multivendor/metals_spot_price');		
		$this->load->language('purpletree_multivendor/sellertemplateproduct');

		$this->document->setTitle($this->language->get('heading_title'));

		//$this->document->addScript('catalog/view/javascript/purpletree_style.js');
		$this->load->model('extension/purpletree_multivendor/sellertemplateproduct');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
		if($this->validateForm()) {
			$this->request->post['seller_id'] = $this->customer->getId();
			
			if($this->request->get['id'] != ''){
				$this->model_extension_purpletree_multivendor_sellertemplateproduct->editProductTemplate($this->request->get['id'],$this->request->post);
			}else{
				
				$this->model_extension_purpletree_multivendor_sellertemplateproduct->addProductTemplate($this->request->get['template_id'],$this->request->post);
			}
			$temp_product_id = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getTemplateProductId($this->request->get['template_id']);
			$minprice = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getMinPrice($temp_product_id);
					
		
		if(!empty($minprice)){
			$this->model_extension_purpletree_multivendor_sellertemplateproduct->updatePrice($minprice);
		}
		else {
			$this->model_extension_purpletree_multivendor_sellertemplateproduct->updateZero($temp_product_id);
		}
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true));
	
		}
		}
		$this->getForm();
	}
	
	
	
	protected function getForm() {
		$data['heading_title'] = $this->language->get('text_edit');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_plus'] = $this->language->get('text_plus');
		$data['text_minus'] = $this->language->get('text_minus');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_option'] = $this->language->get('text_option');
		$data['text_option_value'] = $this->language->get('text_option_value');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_percent'] = $this->language->get('text_percent');
		$data['text_amount'] = $this->language->get('text_amount');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_fixed'] = $this->language->get('text_fixed');
        $data['text_percentage'] = $this->language->get('text_percentage');		
		$data['entry_minimum'] = $this->language->get('entry_minimum');
		$data['entry_shipping'] = $this->language->get('entry_shipping');
		$data['entry_date_available'] = $this->language->get('entry_date_available');
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_stock_status'] = $this->language->get('entry_stock_status');
		$data['entry_price'] = $this->language->get('entry_price');
		$data['entry_price_extra'] = $this->language->get('entry_price_extra');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_points'] = $this->language->get('entry_points');
		$data['entry_option_points'] = $this->language->get('entry_option_points');
		$data['entry_subtract'] = $this->language->get('entry_subtract');		
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');		
		$data['button_back'] = $this->language->get('button_back');	
		$data['text_other'] = $this->language->get('text_other');	
		$data['entry_description'] = $this->language->get('entry_description');	
		
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
			}
		elseif (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		$url = '';
	
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct',  $url, true)
		);
		
			$data['action'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct/edit','&id=' . $this->request->get['id'].'&template_id=' . $this->request->get["template_id"] , true);		
		$seller_id = $this->customer->getId();
		$data['cancel'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct','', true);

		if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$product_info = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getProduct($this->request->get['id'],$seller_id);
			;
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		foreach($data['languages'] as $key => $value) {
				$data['languages'][$key]['activetab'] = '';
		}
		foreach($data['languages'] as $key => $value) {
				$data['languages'][$key]['activetab'] = 'active';
				break;
		}
		
		$data['seller_id'] = $seller_id;
		$this->load->model('tool/image');
		//echo"<pre>"; print_r($this->request->get);die;
		if(isset($product_info['template_id'])){
	    $seller_prices = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getTemplatePrice($product_info['template_id']);	
		}else{
			 $seller_prices = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getTemplatePrice($this->request->get['template_id']);
		}
      		if(!empty($seller_prices)) {
			foreach($seller_prices as $seller_price){
				if ($seller_price['store_logo']) {
					$image = $this->model_tool_image->resize($seller_price['store_logo'], '200' , '200');
				}else {
					$image = $this->model_tool_image->resize('placeholder.png', '200' , '200');
				}
				
					
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($seller_price['price'] , $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}
					$rating = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getStoreRating($seller_price['seller_id']);
					
					   $data['template_prices'][] = array(					  
						'template_id'  => $seller_price['template_id'],						
						'store_logo'       => $image,					
						'store_name'        => $seller_price['store_name'],
						'seller_id'        => $seller_price['seller_id'],						
						'price'       => $price,
						'status'      =>$seller_price['status'],
						'rating'      =>$rating,
						'minimum'      =>$seller_price['minimum']					 
					);
			}
		}
     
		if (!empty($product_info['image'])) {
			if(!empty($product_info['image'])){
				$data['template_image'] = $this->model_tool_image->resize($product_info['image'], '200' , '200');
			   }else {
					$data['template_image'] = $this->model_tool_image->resize('placeholder.png', '200' , '200');
	
				}
				$data['template_name']  = $product_info['name'];
				$data['templatetemplate_description_name']  = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
				}else{
						if($this->request->get['id'] == ''){
						$temp_info = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getTemplateInfo($this->request->get['template_id']);
						if(!empty($temp_info['image'])){
							$data['template_image'] = $this->model_tool_image->resize($temp_info['image'], '200' , '200');
						}else{
							$data['template_image'] = $this->model_tool_image->resize('placeholder.png', '200' , '200');
						}
						
						$data['template_name']  = $temp_info['name'];
						$data['templatetemplate_description_name']  = html_entity_decode($temp_info['description'], ENT_QUOTES, 'UTF-8');
					}
				}
				
		
		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($product_info)) {
			$data['price'] = $product_info['price'];
		} else {
			$data['price'] = '';
		}		
		

		if (isset($this->request->post['quantity'])) {
			$data['quantity'] = $this->request->post['quantity'];
		} elseif (!empty($product_info)) {
			$data['quantity'] = $product_info['quantity'];
		} else {
			$data['quantity'] = 1;
		}

		

		if (isset($this->request->post['subtract'])) {
			$data['subtract'] = $this->request->post['subtract'];
		} elseif (!empty($product_info)) {
			$data['subtract'] = $product_info['subtract'];
		} else {
			$data['subtract'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		}else {
			$data['sort_order'] = 1;
		}

		$this->load->model('extension/localisation/stock_status');

		$data['stock_statuses'] = $this->model_extension_localisation_stock_status->getStockStatuses();

		if (isset($this->request->post['stock_status_id'])) {
			$data['stock_status_id'] = $this->request->post['stock_status_id'];
		} elseif (!empty($product_info)) {
			$data['stock_status_id'] = $product_info['stock_status_id'];
		} else {
			$data['stock_status_id'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($product_info)) {
			$data['status'] = $product_info['status'];
		} else {
			$data['status'] = true;
		}
		$data['back'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true);
				
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('account/purpletree_multivendor/templateproduct_form', $data));
		
	}
	
	public function delete() {
		//echo"<pre>"; print_r($this->request->post); die;
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');			
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true));
		}
		$this->load->language('purpletree_multivendor/sellertemplateproduct');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/purpletree_multivendor/sellertemplateproduct');
//echo"<pre>";print_R($this->request->get['id']);die;
		if (isset($this->request->get['id'])) {
			
				$this->model_extension_purpletree_multivendor_sellertemplateproduct->deleteProduct($this->request->get['id']);
				$temp_product_id = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getTemplateProductId($this->request->get['template_id']);
			    $minprice = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getMinPrice($temp_product_id);
					
		
		if(!empty($minprice)){
			$this->model_extension_purpletree_multivendor_sellertemplateproduct->updatePrice($minprice);
		}
		else {
			$this->model_extension_purpletree_multivendor_sellertemplateproduct->updateZero($temp_product_id);
		}
		

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct','', true));
		}

		$this->getList();
	}
	
	protected function getList(){
	
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_all'] = $this->language->get('text_all');
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = null;
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = null;
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', $url, true)
		);
		
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];

			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}		
			
		$data['delete'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct/delete', $url, true);
		

		$data['products'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin'),
			'seller_id'		  => $this->customer->getId()	
		);
		
		$product_total = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getTotalSellerProducts($filter_data);
		$seller_id = $this->customer->getId();	
		$results = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getSellerProducts($filter_data);
		
		$this->load->model('tool/image');
		
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}
			if(($result['price'] == 0) &&($result['status'] != 1)){
				$price = '';
			}else{
				
				$price = $this->currency->format($result['price'],  $this->session->data['currency'], '', false);
			}
	
			$data['products'][] = array(
			       'id'      => $result['seller_template_id'],
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['product_name'],
				'model'      => $result['model'],
				'price'      => $price,
				
				'quantity'   => $result['quantity'],
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'statusid'     => $result['status'],
				'stock_status'     => $result['stock_status'],
				
				'edit'       => $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct/edit','id=' . $result["seller_template_id"].'&template_id=' . $result["id"].$url, true),
				'delete'       => $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct/delete','id=' . $result['seller_template_id'].'&template_id=' . $result["id"].$url, true)
			);
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}


		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
		
		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_price'] = $filter_price;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;
			$this->load->language('purpletree_multivendor/sellertemplateproduct');
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');	
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$this->response->setOutput($this->load->view('account/purpletree_multivendor/templateproduct_list', $data));
		
	}
	
	protected function validateForm() {
		

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	public function autocomplete() {

		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			
			$this->load->model('extension/purpletree_multivendor/sellertemplateproduct');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

				$seller_id = $this->customer->getId();

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit,
				'seller_id' => $seller_id
			);

			$results = $this->model_extension_purpletree_multivendor_sellertemplateproduct->getProducts($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['product_name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],					
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>