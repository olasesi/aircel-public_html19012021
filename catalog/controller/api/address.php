<?php
class ControllerApiAddress extends Controller {
    
    public function index() {
		$json = array();

        // Add keys for missing post vars
		$keys = array(
			'customer_id',
			'firstname',
			'lastname',
			'company',
			'address_1',
			'address_2',
			'postcode',
			'city',
			'zone_id',
			'country_id'
		);

		foreach ($keys as $key) {
			if (!isset($this->request->post[$key])) {
				$this->request->post[$key] = '';
			}
		}
		$this->load->model('localisation/country');
		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
		
		if ($country_info) {
			$country = $country_info['name'];
			$iso_code_2 = $country_info['iso_code_2'];
			$iso_code_3 = $country_info['iso_code_3'];
			$address_format = $country_info['address_format'];
		} else {
			$country = '';
			$iso_code_2 = '';
			$iso_code_3 = '';
			$address_format = '';
		}

		$this->load->model('localisation/zone');
		$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

		if ($zone_info) {
			$zone = $zone_info['name'];
			$zone_code = $zone_info['code'];
		} else {
			$zone = '';
			$zone_code = '';
		}
		
		$json['status'] = true;
		$data = array(
			'firstname'      => $this->request->post['firstname'],
			'lastname'       => $this->request->post['lastname'],
			'company'        => $this->request->post['company'],
			'address_1'      => $this->request->post['address_1'],
			'address_2'      => $this->request->post['address_2'],
			'postcode'       => $this->request->post['postcode'],
			'city'           => $this->request->post['city'],
			'zone_id'        => $this->request->post['zone_id'],
			'zone'           => $zone,
			'zone_code'      => $zone_code,
			'country_id'     => $this->request->post['country_id'],
			'country'        => $country,
			'iso_code_2'     => $iso_code_2,
			'iso_code_3'     => $iso_code_3,
			'address_format' => $address_format,
			'custom_field'   => isset($this->request->post['custom_field']) ? $this->request->post['custom_field'] : array()
		);
		
		$this->load->model('account/address');
		$customer_id = $this->request->post['customer_id'];
		$address_id = $this->model_account_address->addAddress($customer_id, $data);
		
		$json['data'] ['address_id'] = $address_id;
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    public function getAddress() {
        $json = array();
        
        if(isset($this->request->post['customer_id'])) {
            $customer_id = $this->request->post['customer_id'];
            $this->load->model('custom/address');

    		$paymentaddressWithIds = $this->model_custom_address->getAddresses($customer_id);
			$paymentaddress = array();
			$addressIds = array_keys($paymentaddressWithIds);
			for($i = 0; $i < count($addressIds); $i++){
			    array_push($paymentaddress,$paymentaddressWithIds[$addressIds[$i]]);
			}

    		
            $json['status'] = true;
            $json['data'] = $paymentaddress;
        } else {
            $json['status'] = false;
        }
        
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
  
    
}