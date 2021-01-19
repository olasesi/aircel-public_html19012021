<?php 
class ModelExtensionPurpletreeMultivendorBulkproductupload extends Model{
	
	public function sellerTotalProduct($seller_id){
		$query=$this->db->query("SELECT COUNT(*) AS total_product FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id='".(int) $seller_id."'");
	if($query->num_rows){	
		return $query->row;
	} else {
		return NULL;	
	}
	}
	
	public function getlastproductId(){
		$query=$this->db->query("SELECT product_id FROM " . DB_PREFIX . "product ORDER BY `product_id` DESC LIMIT 0,1");
		if($query->num_rows){	 
			return $query->row['product_id'];
		} else {
			return NULL;	
		}
	} 
	public function getNoOfProduct($seller_id){
		$query=$this->db->query("SELECT pvp.no_of_product FROM " . DB_PREFIX . "purpletree_vendor_plan pvp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvsp.seller_id='".(int) $seller_id."' AND pvsp.status=1");
		if($query->num_rows){	
			return $query->row;
		} else {
			return NULL;	
		}
	}
	
	public function getSsellerplanStatus($seller_id) {
			//$query=$this->db->query("SELECT status_id FROM ". DB_PREFIX ."purpletree_vendor_plan_subscription WHERE seller_id='".(int)$seller_id."'");
			 $sql="SELECT pvps.status_id FROM ". DB_PREFIX ."purpletree_vendor_plan pvp  LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_description pvpd ON (pvp.plan_id=pvpd.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) LEFT JOIN ". DB_PREFIX ."purpletree_vendor_plan_subscription pvps ON ((pvps.seller_id = pvsp.seller_id) AND (pvps.status_id = pvp.status)) WHERE pvpd.language_id='".(int)$this->config->get('config_language_id') ."' AND pvsp.seller_id='".(int)$seller_id."' AND pvsp.status=1";
			$query = $this->db->query($sql);
			if($query->num_rows){
			return $query->row['status_id'];
			} else { 
			return false;
			}
		}
	
	public function addProductGeneralTab($data) {

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$value['product_id'] . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}			
		}	
	public function editProductGeneralTab($data) {
		foreach ($data['product_description'] as $language_id => $value) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$value['product_id']. "' and language_id = '" . (int)$language_id . "'");					
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$value['product_id']. "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}				
		}
	public function addProductDataTab($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "',image ='". $this->db->escape($data['image']) ."', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		return $this->db->getLastId();
	}
		public function editProductDataTab($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "',image ='". $this->db->escape($data['image']) ."', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW() WHERE product_id = '" . (int)$data['product_id'] . "'");

		//return $this->db->getLastId();
	}
	
	public function addProductLinkTab($data) {
		
	if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . $this->db->escape($data['product_id']) . "', download_id = '" . (int)$download_id . "'");
			}
		} 

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $key=>$category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . $this->db->escape($data['product_id']) . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['product_filter'])  && !empty($data['product_filter'])) {
 			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . $this->db->escape($data['product_id']) . "', filter_id = '" . (int)$filter_id . "'");
			}
		} 

	 if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . $this->db->escape($data['product_id']). "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . $this->db->escape($data['product_id']) . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . $this->db->escape($data['product_id']) . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . $this->db->escape($data['product_id']). "'");
			}
		} 

	if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . $this->db->escape($data['product_id']) . "', store_id = '" . (int)$store_id . "'");
			}
		}  

	}
	
public function editProductLinkTab($data,$product_id) {

		if (isset($data['product_download'])) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . $this->db->escape($product_id) . "'");
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . $this->db->escape($data['product_id']) . "', download_id = '" . (int)$download_id . "'");
			}
		}
		if (isset($data['product_category'])) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . $this->db->escape($product_id). "'");
			foreach ($data['product_category'] as $category_id) {
							
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . $this->db->escape($data['product_id']) . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['product_filter']) && !empty($data['product_filter'])) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . $this->db->escape($product_id). "'");
			foreach ($data['product_filter'] as $filter_id) {

				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . $this->db->escape($data['product_id']) . "', filter_id = '" . (int)$filter_id . "'");
			}
		} 
		if (isset($data['product_related'])) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . $this->db->escape($product_id) . "'");
			foreach ($data['product_related'] as $related_id) {	
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . $this->db->escape($data['product_id']) . "', related_id = '" . (int)$related_id . "'");
						
			}
		} 
		
		if (isset($data['product_store'])) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . $this->db->escape($product_id)."'");
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . $this->db->escape($data['product_id']). "', store_id = '" . (int)$store_id . "'");
			}
		} 
	}
	/* -------------------------------------------< Atribute >-------------------------- */
 public function addProductAttributeTab($data) { 

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_attribute['product_id'] . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO ". DB_PREFIX ."product_attribute SET product_id = '" . (int)$product_attribute['product_id']  . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

	 } 
public function editProductAttributeTab($data) {

		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE product_id = '" . (int)$product_attribute['product_id'] . "' AND language_id = '" . (int)$product_attribute['language_id'] . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
		foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_attribute['product_id'] . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}
	}	 
	/* -------------------------------------------< /Atribute >-------------------------- */
		/* -------------------------------------------< Recurring >-------------------------- */
	
	public function addProductRecurringTab($data,$product_id) {

		if (isset($data['product_recurring'])) {
			foreach ($data['product_recurring'] as $product_recurring) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_recurring['product_id']. ", customer_group_id = " . (int)$product_recurring['customer_group_id'] . ", `recurring_id` = " . (int)$product_recurring['recurring_id']);
			}
		}
	    }
	
	public function editProductRecurringTab($data,$product_id) {


		if (isset($data['product_recurring'])) {

			foreach ($data['product_recurring'] as $product_recurring) {

				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = '" . (int)$product_id."' and  recurring_id='".(int)$product_recurring['recurring_id']."' and customer_group_id='".(int)$product_recurring['customer_group_id']."'"); 

				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_recurring['product_id']. ", customer_group_id = " . (int)$product_recurring['customer_group_id'] . ", `recurring_id` = " . (int)$product_recurring['recurring_id']);
			}
		}
	    }
	/* -------------------------------------------< /Recurring >-------------------------- */
	/* *************************************************************************************** */
	/* -------------------------------------------< Discount >-------------------------- */
	public function addProductDiscountTab($data) {

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . $product_discount['product_id']. "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}
	
	}
		public function editProductDiscountTab($data) {
		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {

				$this->db->query("UPDATE " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_discount['product_id']. "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "' WHERE product_discount_id = '" . (int)$product_discount['product_discount_id']. "'");
			}
		}
	
	}
	   /* -------------------------------------------< /Discount >-------------------------- */	
	   /* ********************************************************************************** */
		/* -------------------------------------------< Special >-------------------------- */
	public function addProductSpecialTab($data,$product_id) {
			if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_special['product_id'] . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
	}

	public function editProductSpecialTab($data,$product_id	) {

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {				

				$this->db->query("UPDATE " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_special['product_id'] . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "' WHERE product_special_id = '" . $this->db->escape($product_special['product_special_id']) . "' ");
			}		
		}
}
		/* -------------------------------------------< /Special >-------------------------- */
	/* ************************************************************************************* */
	/* -------------------------------------------< Reward Points >-------------------------- */
		public function addProductRewardpointsTab($data) {
		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				if ((int)$product_reward['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_reward['product_id'] . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
				}
			}
		}
			
	}
		public function editProductRewardpointsTab($data) {

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$value['product_id'] . "'");
				
				if ((int)$value['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$value['product_id']. "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
				}
			}
		}
			
	}
	
  /* -------------------------------------------< /Reward Points >-------------------------- */
  /* ************************************************************************************* */
	/* -------------------------------------------< SEO >-------------------------- */
		public function addProductSeoTab($data) {
				if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$language['product_id']. "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
			
	}
		public function editProductSeoTab($data) {
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url']as $store_id => $language) {			
				foreach ($language as $language_id => $keyword) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$language['product_id'] . "' and language_id = '" . (int)$language_id . "'");
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$language['product_id'] . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}		
	}
	
		/* -------------------------------------------< /SEO >------------------------- */
		/* ******************************************************************************/
		/* -------------------------------------------< Design >-------------------------- */
		public function addProductDesignTab($data) {
		 if (isset($data['product_layout'])) {
			 foreach ($data['product_layout'] as $product_id => $value) {
			foreach ($value as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id. "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
			 }
		  }	
		}
		public function editProductDesignTab($data) {

		if (isset($data['product_layout'])) {
			foreach( $data['product_layout'] as $product_id => $designValue){
			foreach ($designValue  as $store_id => $layout_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id. "'");
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
		  }
	 }
	
		/* -------------------------------------------< /Design >-------------------------- */

		
  	/* ******************************************************************************/
		/* -------------------------------------------< Seller >-------------------------- */
		public function assignDataToSeller($data) {
		 if (isset($data['seller_data'])) {
			 foreach ($data['seller_data'] as $key => $sellerData) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_products SET product_id = '" . (int)$sellerData['product_id']. "', seller_id = '" . (int)$sellerData['seller_id'] . "', is_approved = '" . (int)$sellerData['is_approved']. "', created_at= now(), updated_at= now() ");

			 }
		  }	
		}
		
		public function editAssignDataToSeller($data) {
		 if (isset($data['seller_data'])) {
			 foreach ($data['seller_data'] as $key => $sellerData) {
				 $this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_products WHERE product_id = '" . (int)$sellerData['product_id']. "'");
				 
				$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_products SET product_id = '" . (int)$sellerData['product_id']. "', seller_id = '" . (int)$sellerData['seller_id'] . "', is_approved = '" . (int)$sellerData['is_approved']. "', created_at= now(), updated_at= now() ");

			 }
		  }	
		}
	
		/* -------------------------------------------< /Seller >-------------------------- */
		/* *********************************************************************************/
		/* -------------------------------------------< Image >-------------------------- */
		public function addProductImageTab($data) {

				if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $key => $product_images) {
			 
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_images['product_id'] . "', image = '" . $this->db->escape(trim($product_images['image'])) . "', sort_order = '" . (int)$product_images['sort_order'] . "'");
				}
				
			}
		}
		
		public function editProductImageTab($data) {
		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $key => $product_images) {

				$this->db->query("UPDATE " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_images['product_id'] . "', image = '" . $this->db->escape(trim($product_images['image'])) . "', sort_order = '" . (int)$product_images['sort_order'] . "' WHERE product_image_id = '" . (int)$product_images['product_image_id'] . "'");
				}
				
			}
		}
	
		/* -------------------------------------------< /Image >-------------------------- */
		/* *********************************************************************************/
		/* -------------------------------------------< Product Option >-------------------------- */
		public function addProductOptionTab($data) {

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as  $product_option) {
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_option['product_id']. "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
		
		public function editProductOptionTab($data) {

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as  $product_option) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_option['product_id'] . "' and  option_id = '" . (int)$product_option['option_id'] . "'");	
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_option['product_id']. "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
	
		/* -------------------------------------------< / Product Option >-------------------------- */
		/* --------------------------------------< Product Option value >-------------------------- */
		public function addProductOptionValueTab($data) {
		if (isset($data['product_option_values'])) {
			foreach ($data['product_option_values'] as  $product_option_value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_value['product_option_id']. "', product_id = '" . (int)$product_option_value['product_id'] . "', option_id = '" . (int)$product_option_value['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
				}
			}
		}
		
		public function editProductOptionvalueTab($data) {

		if (isset($data['product_option_values'])) {
			foreach ($data['product_option_values'] as  $product_option_value) {
				
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_option_value['product_id'] . "' and  option_id = '" . (int)$product_option_value['option_id'] . "' and option_value_id = '" . (int)$product_option_value['option_value_id'] . "'");				
				
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_value['product_option_id']. "', product_id = '" . (int)$product_option_value['product_id'] . "', option_id = '" . (int)$product_option_value['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
				}
			}
		}
	
		/* -----------------------------------< / Product Option value >-------------------------- */
		
	public function getLanguageByCode($language_code) {
		$query = $this->db->query("SELECT `language_id` FROM " . DB_PREFIX . "language WHERE code = '" . (int)$language_code . "'");

		return $query->row;
	}
		public function getAllLanguage() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language");
		return $query->rows;
	}

	public function getTotalProductProductTable($data) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$data['product_id']. "'");
		return $query->row['total'];
	}
	public function getTotalProductsProductTable() {
		$query = $this->db->query("SELECT `product_id` FROM " . DB_PREFIX . "product");
		return $query->rows;
	}
	
	public function getAllSellerId() {
		$query = $this->db->query("SELECT `seller_id`,`store_name` FROM " . DB_PREFIX . "purpletree_vendor_stores ORDER BY seller_id ASC");
		return $query->rows;
	}

	public function getOptionId($value) {
		
		$query = $this->db->query("SELECT option_id as opt_id FROM " . DB_PREFIX . "option_description WHERE name='".$this->db->escape($value)."'");
		if ($query->num_rows) {
			return $query->row['opt_id'];
		} else {
			return null;	
		}
	}
	public function getOptionValueId($name,$option_id){
	$query = $this->db->query("SELECT option_value_id as opt_value_id FROM " . DB_PREFIX . "option_value_description WHERE name='".$this->db->escape($name)."' and option_id='".$this->db->escape($option_id)."' ");
					if ($query->num_rows) {
			return $query->row['opt_value_id'];	
		} else {
			return null;	
		}	
	}
	
		public function getProductOptionId($product_id,$option_id){
	$query = $this->db->query("SELECT product_option_id as product_opt_id FROM " . DB_PREFIX . "product_option WHERE product_id='".$this->db->escape($product_id)."' and option_id='".$this->db->escape($option_id)."' ");
			if ($query->num_rows) {
			return $query->row['product_opt_id'];
		} else {
			return null;	
		}		
	}
	
			public function getProductDiscountId(){
	$query = $this->db->query("SELECT product_discount_id FROM " . DB_PREFIX . "product_discount");
			if ($query->num_rows) {
			return $query->rows;
		} else {
			return null;	
		}		
	}
	
	public function getProductSpecialId(){
	$query = $this->db->query("SELECT product_special_id FROM " . DB_PREFIX . "product_special");
			if ($query->num_rows) {
			return $query->rows;
		} else {
			return null;	
		}		
	}
	
		public function getProductImageId(){
	$query = $this->db->query("SELECT product_image_id FROM " . DB_PREFIX . "product_image");
			if ($query->num_rows) {
			return $query->rows;
		} else {
			return null;	
		}		
	}
	
	
	public function getExportSeoUrlData($tableName,$seller_id,$language){
		if($seller_id!=''){
		$query=$this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id= ".(int)$seller_id."");
		$seller_product_data=array_column($query->rows,"product_id");
		
		$query1=$this->db->query("SELECT * FROM " . DB_PREFIX . "".$this->db->escape($tableName)." WHERE language_id=".(int)$language."");
		$data=array();
		foreach($query1->rows as $k=>$vv){
			$p=explode('product_id=',$vv['query']);
				if(isset($p[1]) && $p[1] != '') {
			if(in_array($p[1],$seller_product_data)){
		$data[$p[1]]=array('product_id'		=>	$p[1],
							'store_id'		=>	$vv['store_id'],
							'keyword'		=>	$vv['keyword']
					);	
			}
				}
		}
		} else {			
		$query1=$this->db->query("SELECT * FROM " . DB_PREFIX . "".$this->db->escape($tableName)." WHERE language_id=".(int)$language."");
		$data=array();
		
		foreach($query1->rows as $k=>$vv){
		$p=explode('product_id=',$vv['query']);
		if(isset($p[1]) && $p[1] != '') {
		$data[$p[1]]=array('product_id'		=>	$p[1],
							'store_id'		=>	$vv['store_id'],
							'keyword'		=>	$vv['keyword']
					);	
		}
		}
		}
		return $data;
		}
	public function getExportData($tableName,$seller_id,$language){

			$andoperator='';
			$cmdWhere='';
			$lang ='';	
			if($tableName=='seo_url'){
			$orderBy='seo_url_id';	
			}else {
				$orderBy='product_id';
			}
			if($seller_id!=''){
			$filterBySellerId="product_id IN (SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id= ".(int)$seller_id." ) ";					
			} else {
			$filterBySellerId='';			
			}
			if($tableName=='product_description' || $tableName=='product_attribute' || $tableName=='seo_url')
			{
				$lang=' language_id='.$language; 
					if($seller_id!='')
					{
						$andoperator=' AND ';	
					} else {
						$andoperator='';
					}
				if(isset($seller_id) or ($lang != ''))
				{
					$cmdWhere=' WHERE ';	
				}
			} else {
			if($seller_id!='')
			{
				$cmdWhere=' WHERE ';	
			}			
			}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "".$this->db->escape($tableName).$cmdWhere.$filterBySellerId.$andoperator.$lang." ORDER BY ".$orderBy." ASC ");
		

				
			if ($query->num_rows > 0) {
			return $query->rows;
		} else {
			return null;	
		}
		} 
		public function getDataByProductId($tableName,$product_id){
			
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "".$this->db->escape($tableName)." WHERE product_id=". (int)$product_id);
			if ($query->num_rows > 0) {
			return $query->rows;
		} else {
			return null;	
		}		
		}
		
		public function getProductId($product_id) {
		$product_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$productid = "";
		foreach ($query->rows as $result) {
			if($productid == '') {
			$product_data['product_id'] = $result['product_id'];
			$productid = $result['product_id'];
			} else {
			$product_data['product_id'] = $productid.','.$result['product_id'];
			$productid = $productid.','.$result['product_id'];
			}
		}
		return $product_data;
	}
	
			public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$downloid = "";
		foreach ($query->rows as $result) {
			if($downloid == '') {
			$product_download_data['download_id'] = $result['download_id'];
			$downloid = $result['download_id'];
			} else {
			$product_download_data['download_id'] = $downloid.','.$result['download_id'];
			$downloid = $downloid.','.$result['download_id'];			
			}
		}

		return $product_download_data;
	}
	
		public function getProductCategories($product_id) {
		$product_category_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$catid = "";
		foreach ($query->rows as $result) {
			if($catid == '') {
			$product_category_data['category_id'] = $result['category_id'];
			$catid = $result['category_id'];
			} else {
			$product_category_data['category_id'] = $catid.','.$result['category_id'];
			$catid = $catid.','.$result['category_id'];
			}
		}
		return $product_category_data;
	}
	
		public function getProductFilter($product_id) {
		$product_filter_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		$filterid = "";
		foreach ($query->rows as $result) {
			if($filterid == '') {
			$product_filter_data['filter_id'] = $result['filter_id'];
			$filterid = $result['filter_id'];
			} else {
			$product_filter_data['filter_id'] = $filterid.','.$result['filter_id'];
			$filterid = $filterid.','.$result['filter_id'];
			}
		}
		return $product_filter_data;
	}
	
	
	public function getProductRelated($product_id) {
		$product_related_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$productRelatedid = "";
		foreach ($query->rows as $result) {
			if($productRelatedid == '') {
			$product_related_data['related_id'] = $result['related_id'];
			$productRelatedid = $result['related_id'];
			} else {
			$product_related_data['related_id'] = $productRelatedid.','.$result['related_id'];
			$productRelatedid = $productRelatedid.','.$result['related_id'];
			}
		}
		return $product_related_data;
	}
	
	public function getProductToStore($product_id) {
		$product_store_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$productStoreId = "";
		foreach ($query->rows as $result) {
			if($productStoreId == '') {
			$product_store_data['store_id'] = $result['store_id'];
			$productStoreId = $result['store_id'];
			} else {
			$product_store_data['store_id'] = $productStoreId.','.$result['store_id'];
			$productStoreId = $productStoreId.','.$result['store_id'];
			}
		}
		return $product_store_data;
	}


		public function getTableName($tableName)
		{
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "".$tableName."`");
			if ($query->num_rows) {
				$table=array();
				$tableName=array();
				foreach($query->rows as $key =>$value)
				{
				if($value['Field'] != 'viewed') {
				$table[]=$value['Field'];	
					}	
				}
				$excelCell=array('AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ');
				$Cell=array_slice(array_merge(range('A','Z'),$excelCell),0,count($table),true);	
				return array_combine($Cell,$table);
		} else {
			return null;	
		}
		}
		public function getOptionType($id){
				$query = $this->db->query("SELECT type AS option_type FROM " . DB_PREFIX . "option WHERE option_id=". (int)$id);

			if ($query->num_rows > 0) {
			return $query->row['option_type'];
		} else {
			return null;	
		}
			
		}
		
				public function getOptionName($id){
				$query = $this->db->query("SELECT name AS option_type FROM " . DB_PREFIX . "option_description WHERE option_id=". (int)$id);

			if ($query->num_rows > 0) {
			return $query->row['option_type'];
		} else {
			return null;	
		}
			
		}
		
		
		
		public function getAttributeName($id){
				$query = $this->db->query("SELECT name AS attribute_name FROM " . DB_PREFIX . "attribute_description WHERE attribute_id=". (int)$id);

			if ($query->num_rows > 0) {
			return $query->row['attribute_name'];
		} else {
			return null;	
		}
			
		}
		
				public function getAttributeId($name,$langId){

				$query = $this->db->query("SELECT attribute_id AS attribute FROM " . DB_PREFIX . "attribute_description WHERE name= '". trim($name)."' AND language_id= ".(int)$langId);

			if ($query->num_rows > 0) {
			return $query->row['attribute'];
		} else {
			return null;	
		}
			
		}
		

		
/* 		public function getOptionValueId($name,$opt_id,$langId){

				$query = $this->db->query("SELECT option_value_id AS optValueId FROM " . DB_PREFIX . "option_value_description WHERE name= '". trim($name)."' AND language_id= ".(int)$langId.' AND option_id= '.$opt_id);

			if ($query->num_rows > 0) {
			return $query->row['optValueId'];
		} else {
			return null;	
		}
			
		} */
		
		public function getOptionValueName($optId,$OptValueId,$langId){

				$query = $this->db->query("SELECT name AS optvaluename FROM " . DB_PREFIX . "option_value_description WHERE option_id=". (int)$optId.' AND option_value_id = '.(int)$OptValueId.' AND language_id = '.(int)$langId);

			if ($query->num_rows > 0) {
			return $query->row['optvaluename'];
		} else {
			return null;	
		}	
		}
		public function is_approved($product_id){

				$query = $this->db->query("SELECT is_approved AS approved FROM " . DB_PREFIX . "purpletree_vendor_products WHERE product_id=". (int)$product_id);
			if ($query->num_rows > 0) {
			return $query->row['approved'];
		} else {
			return null;	
		}
			
		}
		
		public function getTaxClass($taxClassId){

				$query = $this->db->query("SELECT title AS taxtitle FROM " . DB_PREFIX . "tax_class WHERE tax_class_id=". $taxClassId);

			if ($query->num_rows > 0) {
			return $query->row['taxtitle'];
		} else {
			return null;	
		}	
		}
		
		public function getStockStatus1($stockStatusId){

				$query = $this->db->query("SELECT name AS stockstatus FROM " . DB_PREFIX . "stock_status WHERE stock_status_id=". (int)$stockStatusId);

			if ($query->num_rows > 0) {
			return $query->row['stockstatus'];
		} else {
			return null;	
		}	
		}
		
		
		public function getManufacturerName($manufacturer_id){

				$query = $this->db->query("SELECT name AS manufacturerName FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id=". (int)$manufacturer_id);

			if ($query->num_rows > 0) {
			return $query->row['manufacturerName'];
		} else {
			return null;	
		}	
		}
		
		public function getWeightClassName($weight_class_id, $languageId){

				$query = $this->db->query("SELECT title AS titleName FROM " . DB_PREFIX . "weight_class_description WHERE weight_class_id=". (int)$weight_class_id." AND language_id= ".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}	
		}		
		public function getlengthClassName($length_class_id, $languageId){

				$query = $this->db->query("SELECT title AS titleName FROM " . DB_PREFIX . "length_class_description WHERE length_class_id=". (int)$length_class_id." AND language_id= ".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}	
		}		
		public function getCustomerGroupName($customer_group_id, $languageId){
				
			
				$query = $this->db->query("SELECT name AS titleName FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id=". (int)$customer_group_id." AND language_id= ".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}	
		}
		
		
		public function getProductDownloadsName($download_id, $languageId){

				$query = $this->db->query("SELECT name AS titleName FROM " . DB_PREFIX . "download_description WHERE download_id=". (int)$download_id." AND language_id= ".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}	
		}
		
				
/* 		public function getProductcategoryName($download_id, $languageId){

				$query = $this->db->query("SELECT name AS titleName FROM " . DB_PREFIX . "download_description WHERE download_id=". $download_id." AND language_id= ".$languageId);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}
}		 */
		
		public function getstoreByName($storeId){

				$query = $this->db->query("SELECT name AS titleName FROM " . DB_PREFIX . "store WHERE store_id=". (int)$storeId);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}	
		}
				
		public function getLayoutName($layout_id){

				$query = $this->db->query("SELECT name AS titleName FROM " . DB_PREFIX . "layout WHERE layout_id=". (int)$layout_id);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}	
		}		
		
		public function getRecurringName($recurring_id ,$languageId){

				$query = $this->db->query("SELECT name AS titleName FROM " . DB_PREFIX . "recurring_description WHERE recurring_id=". (int)$recurring_id." AND language_id= ".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['titleName'];
		} else {
			return null;	
		}	
		}
		
		public function getRecurringId($recurring ,$languageId){

				$query = $this->db->query("SELECT recurring_id FROM " . DB_PREFIX . "recurring_description WHERE name='". $this->db->escape($recurring)."' AND language_id= ".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['recurring_id'];
		} else {
			return '';	
		}	
		}		
		
		public function getCustomerGroupId($customer_group_name ,$languageId){

				$query = $this->db->query("SELECT customer_group_id FROM " . DB_PREFIX . "customer_group_description WHERE name='". $this->db->escape($customer_group_name)."' AND language_id= ".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['customer_group_id'];
		} else {
			return '';	
		}	
		}
		public function getStoreId($storeName){

				$query = $this->db->query("SELECT store_id FROM " . DB_PREFIX . "store WHERE name='". trim($storeName)."'");
			if ($query->num_rows > 0) {
			return $query->row['store_id'];
		}	
		return '';
		}		
		
		public function getCategoryId($name){

				$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name='". $this->db->escape($name)."'");

			if ($query->num_rows > 0) {
			return $query->row['category_id'];
		} else {
			return '';	
		}	
		}
				
		public function getDownloadId($name,$languageId){

				$query = $this->db->query("SELECT download_id FROM " . DB_PREFIX . "download_description WHERE name='". $this->db->escape($name)."' AND language_id=".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['download_id'];
		} else {
			return '';	
		}	
		}		
		
	
		
		public function getFilterId($name,$languageId,$gpId){

				$query = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "filter_description WHERE name='". trim($this->db->escape($name))."' AND language_id=".(int)$languageId." AND filter_group_id=".(int)$gpId);

			if ($query->num_rows > 0) {
			return $query->row['filter_id'];
		} else {
			return '';	
		}	
		}			
		
		public function getFilterGroupId($name,$languageId){
				$query = $this->db->query("SELECT filter_group_id FROM " . DB_PREFIX . "filter_group_description WHERE name='".trim($this->db->escape($name))."' AND language_id=".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['filter_group_id'];
		} else {
			return '';	
		}	
		}	

		public function getRelatedId($name,$languageId){

				$query = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "filter_description WHERE name='". $this->db->escape($name)."' AND language_id=".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['filter_id'];
		} else {
			return '';	
		}	
		}	

		public function getProductIdByName($name,$languageId){

				$query = $this->db->query("SELECT  	product_id FROM " . DB_PREFIX . "product_description WHERE name='". $this->db->escape($name)."' AND language_id=".(int)$languageId);

			if ($query->num_rows > 0) {
			return $query->row['product_id'];
		} else {
			return '';	
		}	
		}
		
		
				public function getStockStatusId($stock_status){

			$query = $this->db->query("SELECT stock_status_id  FROM " . DB_PREFIX . "stock_status WHERE  name='". $this->db->escape($stock_status)."'");
			 
		 if ($query->num_rows > 0) {
			return $query->row['stock_status_id'];
		} else {
			return null;	
		}	
		}
		
		public function getManufacturerId($manufacturer){

			$query = $this->db->query("SELECT manufacturer_id  FROM " . DB_PREFIX . "manufacturer WHERE  name='". $this->db->escape($manufacturer)."'");
			 
			
			if ($query->num_rows > 0) {
			return $query->row['manufacturer_id'];
		} else {
			return null;	
		}	
		}
		public function getTaxClassId($tex_class){

			$query = $this->db->query("SELECT tax_class_id  FROM " . DB_PREFIX . "tax_class WHERE  title='". $this->db->escape($tex_class)."'");
			 
			if ($query->num_rows > 0) {
			return $query->row['tax_class_id'];
		} else {
			return null;	
		}	
		}
		public function getWeightClassId($weight_class){

			$query = $this->db->query("SELECT weight_class_id  FROM " . DB_PREFIX . "weight_class_description WHERE  title='". $this->db->escape($weight_class)."'");
			 
			 if ($query->num_rows > 0) {
			return $query->row['weight_class_id'];
		} else {
			return null;	
		}	
		}
		public function getLengthClassId($length_class){

			$query = $this->db->query("SELECT length_class_id  FROM " . DB_PREFIX . "length_class_description WHERE  title='". $this->db->escape($length_class)."'");
			 
			if ($query->num_rows > 0) {
			return $query->row['length_class_id'];
		} else {
			return null;	
		}	
		}	
		public function getParentId($category_id){

			$query = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE  category_id='".(int)$category_id."'");			
			$kkk=array();
			foreach($query->rows as $value){
				foreach($value as $k => $v){	
					$kkk[] = $v;
				}	
			}
				return $kkk;	
		}
		
		public function getIsApproved($key='module_purpletree_multivendor_product_approval', $store_id = 0) {
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}
		public function getCategoryIds($name, $language_id){
				
				$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name='".trim($this->db->escape($name))."' AND language_id='".(int)trim($language_id)."'");

			if ($query->num_rows > 0) {
			return $query->rows;
		} else {
			return array();	
		}	
		}
		public function getCategoryIds1($name, $language_id){

				$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name='".trim($this->db->escape($name))."' AND language_id='".(int)trim($language_id)."'");

			if ($query->num_rows > 0) {
			return $query->row['category_id'];
		} else {
			return '';	
		}	
		}
			public function getLayoutId($name){

				$query = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout WHERE name='". $this->db->escape($name)."'");

			if ($query->num_rows > 0) {
			return $query->row['layout_id'];
		} else {
			return null;	
		}	
		}
		public function getFilter($filter_id) {
		$query = $this->db->query("SELECT *, (SELECT name FROM " . DB_PREFIX . "filter_group_description fgd WHERE f.filter_group_id = fgd.filter_group_id AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS `group` FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_id = '" . (int)$filter_id . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
		public function getProduct($product_id,$language) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$language . "'");

		return $query->row;
	}
		public function getCategory($category_id,$language) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id) AS path FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$language . "'");
		
		return $query->row;
	}
		public function getInProductId($product_id){

			$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE product_id='". (int)$product_id."'");

			if ($query->num_rows > 0) {
				return $product_id;
			} else {
				return NULL;	
			}	
		}

		public function getNoOfProductForMultiplePlan($seller_id){
		$query=$this->db->query("SELECT SUM(pvp.no_of_product) AS no_of_product FROM " . DB_PREFIX . "purpletree_vendor_plan pvp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_seller_plan pvsp ON (pvp.plan_id=pvsp.plan_id) WHERE pvsp.seller_id='".(int) $seller_id."' AND pvsp.new_status=1");
		if($query->num_rows){	
			return $query->row;
		} else {
			return NULL;	
		}
	}		
	
		
}
if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( !array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( !array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}
?>