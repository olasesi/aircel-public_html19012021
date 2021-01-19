<?php
// catalog/controller/api/custom.php
class ControllerApiCustom extends Controller {
  public function index() {
	$json = array();
	    
    $json['status'] = true;
    
    // Shipping Methods
	$method_data = array();

	$this->load->model('setting/extension');

	$results = $this->model_setting_extension->getExtensions('shipping');

    //**********added 29/08/2019*****************/

    $tproducts = $this->cart->getProducts();
    $from_abroad = "NO";
    foreach ($tproducts as $product) {
	     
		if ($product['from_abroad'] == "YES") {
			$from_abroad = "YES";
			break;
		}
	}
	//************End*******************************//			

	foreach ($results as $result) {
		if ($this->config->get('shipping_' . $result['code'] . '_status')) {
			$this->load->model('extension/shipping/' . $result['code']);
			$this->load->model('custom/address');
            $shipping_address = $this->model_custom_address->getAddress($this->request->post['customer_id']);
			$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($shipping_address);
			
            if($result['code'] == "pickup" AND $from_abroad == "YES") { //added 29/08/2019
            }else{ //added 29/08/2019
			  if ($quote) {
				$method_data[$result['code']] = array(
					'title'      => $quote['title'],
					'quote'      => $quote['quote'],
					'sort_order' => $quote['sort_order'],
					'error'      => $quote['error']
				);
			  }
            } //added 29/08/2019
		}
	}

	$sort_order = array();

	foreach ($method_data as $key => $value) {
		$sort_order[$key] = $value['sort_order'];
	}

	array_multisort($sort_order, SORT_ASC, $method_data);

	$json['shipping_methods'] = $method_data;

	$this->response->addHeader('Content-Type: application/json');
	$this->response->setOutput(json_encode($json));
    
  }
  public function test() {
      echo "hello";
  }
}