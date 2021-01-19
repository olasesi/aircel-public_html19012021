<?php
class ControllerApiCost extends Controller {
    public function index () {
        $this->load->language('api/cart');
        $this->load->model('custom/cart');
        $this->load->model('setting/extension');
        $this->load->model('account/customer');
		$json = array();
		$tqty = 0;
		$subtotal = 0;
		if (!isset($this->session->data['api_id'])) {
		    $json['status'] = true;
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
		    $json['status'] = true;
		    // Stock
			if (!$this->model_custom_cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$json['error']['stock'] = $this->language->get('error_stock');
			}

			$customer_id = $this->request->post['customer_id'];
			$customerInfo = $this->model_account_customer->getCustomer($this->request->post['customer_id']);
			// Products
			$products = $this->model_custom_cart->getProducts($customerInfo['customer_id']);
			$taxes = $this->model_custom_cart->getTaxes($customerInfo['customer_id']);
			
			
			foreach ($products as $product) {
				$product_total = 0;
				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}
                $tqty += $product_total;
                
				if ($product['minimum'] > $product_total) {
					$json['error']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
					);
				}

				$subtotal1 = round($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'],2);

			    $subtotalstr = str_split(strval($subtotal1));
			    $finalsub ='';
			    if(count($subtotalstr) > 0){
    			    for($i=0;$i<count($subtotalstr);$i++){
	    		        if($subtotalstr[$i] !== ","){
	    		            $finalsub .= $subtotalstr[$i];
	    		        }
		    	    }
		    	}
				$subtotal += (double)$subtotal1; //(double)$finalsub;
			}


			// Totals
			$totals = array();
			$total = 0;
            
			// Because __call can not keep var references so we put them into an array. 
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
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
			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);

            // Shipping Methods
        	$method_data = array();
        
        	$this->load->model('setting/extension');
        	$values = $this->model_setting_extension->getExtensions('shipping');
        	
        	$tproducts = $this->model_custom_cart->getProducts($this->request->post['customer_id']);
            $from_abroad = "NO";
            foreach ($tproducts as $product) {
        	     
        		if ($product['from_abroad'] == "YES") {
        			$from_abroad = "YES";
        			break;
        		}
        	}
        	foreach ($values as $value) {
    		    if ($this->config->get('shipping_' . $value['code'] . '_status')) {
        			$this->load->model('extension/shipping/' . $value['code']);
        			$this->load->model('custom/address');
                    $shipping_address = $this->model_custom_address->getAddress($this->request->post['customer_id'], $this->request->post['address_id'] );
                    
                    $weight  = $this->model_custom_cart->getWeight($this->request->post['customer_id']);
                    // var_dump($weight);
        			$quote = $value['code'] == 'weight' ? $this->{'model_extension_shipping_' . $value['code']}->getQuote($shipping_address,$weight) : $this->{'model_extension_shipping_' . $value['code']}->getQuote($shipping_address);
        			
                    if($value['code'] == "pickup" AND $from_abroad == "YES") { //added 29/08/2019
                    }else{ //added 29/08/2019
                    // var_dump($quote);
        			  if ($quote) {
        				$method_data[$value['code']] = array(
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

            if ($this->request->post['option'] == 'deliver') {
                
                $weight = $method_data['weight']['quote'];
                // var_dump($weight);
                $main_keys = array_keys($weight);
                $deliver_data = array(
    			    'title'         =>  $weight[$main_keys[0]]['title'],
    			    'cost'          =>  $weight[$main_keys[0]]['cost'],
    			    'tdeliverycost' =>  $weight[$main_keys[0]]['cost']
    			);
    			$delivery = (int)$deliver_data['tdeliverycost'];

        	} else {
        	    
        	    $pickup = $method_data['pickup']['quote'];
                $main_keys = array_keys($pickup);
                $deliver_data = array(
    			    'title'      => $pickup[$main_keys[0]]['title'],
    			    'cost'      => $pickup[$main_keys[0]]['cost'],
    			);
    			$delivery = (int)$pickup_data['cost'];
        	}

            
            $tax = $totals;
		    $taxcost = $tax[1]['value'];
		    
		    
		    if ($taxcost == 0 ) {
	           $json['data'] ['charges'] = array(
                    array('title'=> $deliver_data['title'],'value'=>$delivery),
	            ); 
		    } else {
		        $json['data'] ['charges'] = array(
                    array('title'=>'International Shipping','value'=>$taxcost),
                    array('title'=> $deliver_data['title'],'value'=>$delivery),
                );
		    }
		    
		    
		    $finaltotal = $subtotal  + $delivery + $taxcost;
		    $json['data']['subtotal'] = $subtotal;
		    $json['data']['total'] = $finaltotal;
		    
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
}