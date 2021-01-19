<?php
class ControllerApiCart extends Controller {
	public function add() {
		$this->load->language('api/cart');
		$this->load->model('custom/cart');

		$json = array();
			
		if (!isset($this->session->data['api_id'])) { 
		    $json['status'] = false;
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
		    $json['status'] = true;
			if (isset($this->request->post['product'])) {
				$this->cart->clear();

				foreach ($this->request->post['product'] as $product) {
					if (isset($product['option'])) {
						$option = $product['option'];
					} else {
						$option = array();
					}

					$this->cart->add($product['product_id'], $product['quantity'], $option);
				}

				$json['success'] = $this->language->get('text_success');
				$json['message'] = $this->language->get('text_success');
			

				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
			    
			} elseif (isset($this->request->post['product_id'])) {
				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
				if ($product_info) {
					if (isset($this->request->post['quantity'])) {
						$quantity = $this->request->post['quantity'];
					} else {
						$quantity = 1;
					}

					if (isset($this->request->post['option'])) {
					   // foreach($)
						$option = array_filter(json_decode($_POST['option'],true));
						
				// 		echo var_dump($option);
						
					} else {
						$option = array();
					}

					$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

					foreach ($product_options as $product_option) {
						if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
							$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
						}
					}
					
					
			
					if (!isset($json['error']['option'])) {
				        $this->load->model('custom/cart');
		                $customer_id = $this->request->post['customer_id'];

						$response = $this->model_custom_cart->addCart($customer_id, $this->request->post['product_id'], $quantity, $option);


						$json['data'] ['cartid'] = $response;
						$json['message'] = $this->language->get('text_success');

						unset($this->session->data['shipping_method']);
						unset($this->session->data['shipping_methods']);
						unset($this->session->data['payment_method']);
						unset($this->session->data['payment_methods']);
					}
				} else {
					$json['error']['store'] = $this->language->get('error_store');
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit() {
	    $this->load->model('custom/cart');
		$this->load->language('api/cart');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
// 			$this->cart->update($this->request->post['key'], $this->request->post['quantity']);

            $this->load->model('custom/cart');
			$customer_id = $this->request->post['customer_id'];
			$cart_id = $this->request->post['cart_id'];
			$quantity = $this->request->post['quantity'];
			
			$this->model_custom_cart->update($customer_id, $cart_id, $quantity);
			
			$json['status'] = true;
			$json['success'] = $this->language->get('text_success'  );

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('api/cart');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Remove
			$cart_id = (int) $this->request->post['cart_id'];
			if (isset($cart_id) && is_int($cart_id) && $cart_id !== 0) {
			    $this->load->model('custom/cart');
    			$customer_id = $this->request->post['customer_id'];
    			
    			$quantity = $this->request->post['quantity'];
    			
				$this->model_custom_cart->remove($customer_id, $cart_id);

				unset($this->session->data['vouchers'][$this->request->post['key']]);

                
				// $json['reply'] = $reply;
				
				$json['success'] = $this->language->get('text_success');
				$json['message'] = $this->language->get('text_success');
				$json['status'] = true;

				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
				unset($this->session->data['reward']);
			} else {
			    $json['status'] = false;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function products() {
		$this->load->language('api/cart');
        $this->load->model('custom/cart');
        $this->load->model('setting/extension');
        $this->load->model('account/customer');
		$json = array();
		$subtotal = 0;
		if (!isset($this->session->data['api_id'])) {
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
		    $json['status'] = true;
		    
			// Stock
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$json['error']['stock'] = $this->language->get('error_stock');
			}

			$customer_id = $this->request->post['customer_id'];
			$cart_id = $this->request->post['cart_id'];
			$quantity = $this->request->post['quantity'];
			$customerInfo = $this->model_account_customer->getCustomer($this->request->post['customer_id']);
			// Products
			$json['data']['products'] = array();
			$products = $this->model_custom_cart->getProducts($customerInfo['customer_id']);
			$taxes = $this->model_custom_cart->getTaxes($customerInfo['customer_id']);
			
			
			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$json['error']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}
				$this->load->model('catalog/product');
				 $productCatalog = $this->model_catalog_product->getProduct($product['product_id']);
				 
                // var_dump($productImages);
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

				$json['data']['products'][] = array(
					'cart_id'    => $product['cart_id'],
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'quantity'   => $product['quantity'],
					'image'   => $productCatalog['image'],
					'stock'      => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'shipping'   => $product['shipping'],
					'price'      =>$this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
					'total'      => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'],
					'reward'     => $product['reward']
				);
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

			// Voucher
			$json['data']['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$json['data']['vouchers'][] = array(
						'code'             => $voucher['code'],
						'description'      => $voucher['description'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],
						'price'            => $this->currency->format($voucher['amount'], $this->session->data['currency']),			
						'amount'           => $voucher['amount']
					);
				}
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

			$json['data']['totals'] = array();

			foreach ($totals as $total) {
				$json['data']['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
			}
			
			$json['data']['totals'][] = array(
			    'title' => 'subtotalB',
				'text'  => $this->currency->format($subtotal,$this->session->data['currency']) //$subtotalstr[1] //$subtotal
		    );
		    
		    
		    $finaltotal = $subtotal + $totals[2]['value'];
		    $json['data']['paymenttotal'] = $this->currency->format($finaltotal,$this->session->data['currency']);
		    
		    
		    
		    
		    
		    
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
                    // $shipping_address = $this->model_custom_address->getAddress($this->request->post['customer_id']);
                    $shipping_address = $this->model_custom_address->getAddress(2);
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
		    
	
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
