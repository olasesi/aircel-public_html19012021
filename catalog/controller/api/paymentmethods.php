<?php
// catalog/controller/api/custom.php
class ControllerApiPaymentmethods extends Controller {
    public function index() {
		$this->load->language('api/payment');
		$this->load->model('custom/cart');
		$this->load->model('custom/address');
        $this->load->model('account/customer');
		$this->load->model('checkout/order');
		
		$customer_id = $this->request->post['customer_id'];
		$cart_id = $this->request->post['cart_id'];
		$quantity = $this->request->post['quantity'];
		$address_id = $this->request->post['address_id'];
		$shippingoption = $this->request->post['shippingoption'];
		$paymentoption = $this->request->post['paymentoption'];
		
		// Delete past shipping methods and method just in case there is an error
		unset($this->session->data['payment_methods']);
		unset($this->session->data['payment_method']);

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			
			if (!$json) {
				// Totals
				$totals = array();
				$taxes = $this->model_custom_cart->getTaxes($customerInfo['customer_id']);
				$total = 0;

				// Because __call can not keep var references so we put them into an array. 
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);

				$this->load->model('setting/extension');

				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				// Payment Methods
				$json['payment_methods'] = array();

				$this->load->model('setting/extension');

				$results = $this->model_setting_extension->getExtensions('payment');

				$recurring = $this->cart->hasRecurringProducts();

				foreach ($results as $result) {
					if ($this->config->get('payment_' . $result['code'] . '_status')) {
						$this->load->model('extension/payment/' . $result['code']);
						$total = 32424;
                        $shipping_address = $this->model_custom_address->getAddress($this->request->post['customer_id'], $this->request->post['address_id'] );
						$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($shipping_address, $total);

						if ($method) {
							$json['payment_methods'][$result['code']] = $method;
						}
					}
				}

				$sort_order = array();

				foreach ($json['payment_methods'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $json['payment_methods']);

				if ($json['payment_methods']) {
					$this->session->data['payment_methods'] = $json['payment_methods'];
				} else {
					$json['error'] = $this->language->get('error_no_payment');
				}
			}
			
			$json['$results'] = $results;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
}