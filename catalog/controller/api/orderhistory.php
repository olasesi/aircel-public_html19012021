<?php
class ControllerApiOrderhistory extends Controller {
    public function getorder () {
   		$this->load->language('api/order');
   		$this->load->model('catalog/product');

		$json = array();
        $json['status'] = true;
        $order_id = $this->request->post['order_id'];
        $customer_id = $this->request->post['customer_id'];

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('custom/orders'); 			

			$order_info = $this->model_custom_orders->getOrder($order_id, $customer_id);
			if($order_info['payment_method'] != "Cash On Delivery"){
			    $order_info['payment_method'] = 'Rave';
			    $order_info['rave_image'] = 'https://files.readme.io/cff1437-Badge_1_1.png';
			}
// 			$order_info = $this->model_custom_orders->getOrder(226, 2);
			
			$order_prods = $this->model_custom_orders->getOrderProducts($order_id);
// 			$order_prods = $this->model_custom_orders->getOrderProducts(226);
			

			if ($order_info) {
				$json['data'] = $order_info;
				for ($i =0; $i < sizeof($order_prods); $i++) {
				    $this->load->model('catalog/product');
				    $prod = $order_prods[$i];
                    $product = $this->model_catalog_product->getProduct($prod['product_id']);
			    	$prod['image'] = $product['image'];
			    	$order_prods[$i] = $prod;
		    	   
				}
			    $json['data']['products'] = $order_prods;
				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    public function getorders () {
        $this->load->language('api/order');
		$this->load->model('custom/orders');

		$json = array();
		
		$customer_id = $this->request->post['customer_id'];
		
		// customer info
		$customerInfo = $this->model_custom_orders->getOrders($customer_id);
		

		
        $page = 1;

		$this->load->model('account/order');
		$this->load->model('custom/orders');


		$results = $this->model_custom_orders->getOrders($customer_id);
		$json['status'] = true;

		foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);

			$json['data'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'products'   => ($product_total),
				'total'      => $result['total'],
				'view'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
			);
		}
		
		
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
     
    
}