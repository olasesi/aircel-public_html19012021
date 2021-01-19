<?php
class ModelExtensionShippingPurpletreeShipping extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/purpletree_shipping');
		$method_data = array();
		$quote_data = array();
		$getshippingcharge = $this->cart->getSellerShippingCharge($address);
		if($getshippingcharge == '0') {
			$getshippingcharge = '0';
		}
		if($getshippingcharge != 'a') {
			if($getshippingcharge == '') {
				$getshippingcharge = 0 ;
			}
		$quote_data['purpletree_shipping'] = array(
			'code'         => 'purpletree_shipping.purpletree_shipping',
			'title'        => $this->language->get('text_title'),
			'cost'         => $getshippingcharge,
			'tax_class_id' => 0,
			'text'         => $this->currency->format($getshippingcharge, $this->session->data['currency'])
		);
			
		$method_data = array(
			'code'       => 'purpletree_shipping',
			'title'      => $this->language->get('text_title'),
			'quote'      => $quote_data,
			'sort_order' => $this->config->get('purpletree_shipping_sort_order'),
			'error'      => false
		);
		}

		return $method_data;
	}
}