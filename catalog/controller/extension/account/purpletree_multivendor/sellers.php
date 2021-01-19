<?php
class ControllerExtensionAccountPurpletreeMultivendorSellers extends Controller {
	public function index() {
		$data['error_warning'] = '';
		$this->load->language('purpletree_multivendor/sellers');

		$this->load->model('extension/purpletree_multivendor/sellers');

		$this->load->model('tool/image');

		$sort = 'seller';
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		}
		
		$filter = '';
		if (isset($this->request->get['search_text'])) {
			$filter = $this->request->get['search_text'];
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

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_heading'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/sellers', $url,true)
		);
		
		$data['text_heading'] = $this->language->get('text_heading');
		$data['text_product'] = $this->language->get('text_product');
		$data['text_sellercontact'] = $this->language->get('text_sellercontact');
		$data['text_sort'] = $this->language->get('text_sort');
		$data['text_limit'] = $this->language->get('text_limit');
		$data['text_name_asc'] = $this->language->get('text_name_asc');
		$data['text_name_desc'] = $this->language->get('text_name_desc');
		$data['text_refine'] = $this->language->get('text_refine');
		$data['text_empty'] = $this->language->get('text_empty');
		
		$this->document->setTitle($this->language->get('text_heading'));

		$data['heading_title'] = $this->language->get('text_heading');
		
		$filter_data_seller = array(
			'sort'               => $sort,
			'order'              => $order,
			'filter'              => $filter,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit	
		);
			
		//$seller_totals = $this->model_extension_purpletree_multivendor_sellers->getTotalSellers($filter_data_seller);
		$seller_totals= 0;
		$seller_lists = $this->model_extension_purpletree_multivendor_sellers->getSellers($filter_data_seller);
		$seller_liststotalss = $this->model_extension_purpletree_multivendor_sellers->getSellerstotal($filter_data_seller);
		
		$data['sellers'] = array();
		
		foreach ($seller_liststotalss as $seller_list11) {
			if($this->config->get('module_purpletree_multivendor_subscription_plans')){
						
			$getSsellerplanStatus11 = $this->model_extension_purpletree_multivendor_sellers->getSsellerplanStatus($seller_list11['seller_id']);
		    $invoiceStatus11 = $this->model_extension_purpletree_multivendor_sellers->getInvoiceStatus($seller_list11['seller_id']);
			if(!$getSsellerplanStatus11 && ($invoiceStatus11==NULL || $invoiceStatus11!=2)) {
			$subscription_status=0;			
			}	else {
				$seller_totals++;			
			}
			}else {
				$seller_totals++;		
			}
		}

		$data['sellers'] = array();
		
		foreach ($seller_lists as $seller_list) {
			if ($seller_list['store_logo']) {
				$data['seller_thumb'] = $this->model_tool_image->resize($seller_list['store_logo'],100 ,100 );
			} else {
				$data['seller_thumb'] = $this->model_tool_image->resize('placeholder.png', 100,100);
			}

			$data['seller_address'] = html_entity_decode($seller_list['store_address'], ENT_QUOTES, 'UTF-8');
			$data['seller_country'] = $seller_list['seller_country'];
			$data['seller_name'] = $seller_list['seller'];
			$data['store_name'] = $seller_list['store_name'];
			$subscription_status=1;
			if($this->config->get('module_purpletree_multivendor_subscription_plans')){
						
			$getSsellerplanStatus = $this->model_extension_purpletree_multivendor_sellers->getSsellerplanStatus($seller_list['seller_id']);
		    $invoiceStatus = $this->model_extension_purpletree_multivendor_sellers->getInvoiceStatus($seller_list['seller_id']);
			if(!$getSsellerplanStatus && ($invoiceStatus==NULL || $invoiceStatus!=2)) {
			$subscription_status=0;			
			}	else {
				//$seller_totals++;			
			}	
				
			}else {
				//$seller_totals++;			
			}	
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['search_text'])) {
				$url .= '&search_text=' . $this->request->get['search_text'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			
			$data['products'] = array();
			
			$filter_data = array(
				'start'              => 0,
				'limit'              => 5,
				'seller_id'			=> $seller_list['seller_id']	
			);
		
			$product_total = $this->model_extension_purpletree_multivendor_sellers->getTotalProducts($filter_data);

			$results = $this->model_extension_purpletree_multivendor_sellers->getProducts($filter_data);

			foreach ($results as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$image = $this->model_tool_image->resize($result['image'], 60, 60);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 60, 60);
				}

				$data['products'][] = array(
					'thumb'       => $image,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'],true)
				);
			}
			

			$data['sellers'][] = array(
				'seller_thumb' => $data['seller_thumb'],
				'seller_name' => $data['store_name'],
				'seller_address' => $data['seller_address'],
				'seller_country' => $data['seller_country'],
				'href'        => $this->url->link('extension/account/purpletree_multivendor/sellerstore/storeview', 'seller_store_id=' . $seller_list['id'],true),
				'seller_contact' => $this->url->link('extension/account/purpletree_multivendor/sellercontact/customerreply', 'seller_id=' . $seller_list['seller_id'],true),
				'product_total' => $product_total,
				'products' => $data['products'],
				'subscription_status'=>$subscription_status
			);
		}
		if($seller_totals ==0) {
			$data['error_warning'] = 'No Seller is available.';
		}
			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			
			if (isset($this->request->get['search_text'])) {
				$url .= '&search_text=' . $this->request->get['search_text'];
			}
			
			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'seller-ASC',
				'href'  => $this->url->link('extension/account/purpletree_multivendor/sellers', 'sort=seller&order=ASC' . $url,true)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'seller-DESC',
				'href'  => $this->url->link('extension/account/purpletree_multivendor/sellers','sort=seller&order=DESC' . $url,true)
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['search_text'])) {
				$url .= '&search_text=' . $this->request->get['search_text'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('extension/account/purpletree_multivendor/sellers', $url . '&limit=' . $value,true)
				);
			}

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			
			if (isset($this->request->get['search_text'])) {
				$url .= '&search_text=' . $this->request->get['search_text'];
			}

			$pagination = new Pagination();
			$pagination->total = $seller_totals;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('extension/account/purpletree_multivendor/sellers',$url . '&page={page}',true);

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($seller_totals) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($seller_totals - $limit)) ? $seller_totals : ((($page - 1) * $limit) + $limit), $seller_totals, ceil($seller_totals / $limit));


			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['filter'] = $filter;
			$data['limit'] = $limit;
			
			$currentpage = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			$this->session->data['ptsmv_current_page'] = $currentpage;
			$this->session->data['ptsmv_current_page_name'] = "Browse Sellers";

			$data['continue'] = $this->url->link('common/home','',true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/purpletree_multivendor/sellers', $data)); 
	}
}
