<?php
class ControllerExtensionAccountPurpletreeMultivendorSellerregister extends Controller {
	private $error = array();

	public function index() {
		 $livecheck = 1;
		      if (!$this->customer->validateSeller($livecheck)) {
					$this->load->language('purpletree_multivendor/ptsmultivendor');
			        $this->session->data['error_warning'] = $this->language->get('error_license');			
			        $this->response->redirect($this->url->link('account/register', '', true));
			     }
				$data['loggedcus'] = '';
		 if ($this->customer->isLogged()) {
			$data['loggedcus'] = $this->customer->getId();
			$this->load->model('extension/purpletree_multivendor/vendor');

			   $store_detail = $this->model_extension_purpletree_multivendor_vendor->isSeller($this->customer->getId());	
				if($store_detail){
						if($store_detail['is_removed']==1){
							$this->response->redirect($this->url->link(	'extension/account/purpletree_multivendor/sellerstore/becomeseller', '', true));
						} else {
						if($store_detail['store_status']==1){
							if($store_detail['multi_store_id']== $this->config->get('config_store_id')){
							    $this->response->redirect($this->url->link(	'extension/account/purpletree_multivendor/dashboardicons', '', true));
						     } else {
							   $this->response->redirect($this->url->link(	'account/account', '', true));
					     	}
						} else {
							$this->response->redirect($this->url->link(	'account/account', '', true));
						}
						}
				}
		} 

		//$this->load->language('account/register');
		//$this->load->language('account/ptsregister');
		$this->load->language('purpletree_multivendor/sellerstore');
		$this->load->language('purpletree_multivendor/sellerregister');
		$this->document->setTitle($this->language->get('text_seller_register_page'));

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		$this->document->addScript('catalog/view/javascript/purpletree/jquery/jquery.validate.min.js');
    	 $this->document->addStyle('catalog/view/javascript/purpletree/codemirror/lib/codemirror.css'); 
         $this->document->addStyle('catalog/view/javascript/purpletree/codemirror/theme/monokai.css'); 
         $this->document->addScript('catalog/view/javascript/purpletree/codemirror/lib/codemirror.js'); 
         $this->document->addScript('catalog/view/javascript/purpletree/codemirror/lib/xml.js'); 
         $this->document->addScript('catalog/view/javascript/purpletree/codemirror/lib/formatting.js'); 

		$this->load->model('account/customer');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		    $statein = "";
			if ($this->customer->isLogged()) {
				$customer_id = $this->customer->getId();
				$emaildata = $this->model_account_customer->getCustomer($customer_id);
				$statein = "isLogged " . $customer_id;
			} else {
				$customer_id = $this->model_account_customer->addCustomer($this->request->post);
				$emaildata = $this->request->post;
				
				$t_customer_data = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
				$customer_id = $t_customer_data['customer_id'];
				
			}
			
		        /*$bmessg = "CID: " . $customer_id . " In Validate";
		        $postdata = "api_token=TAqD7Yl9v9dZzoVytQMWxCHK2uD86GiNqyyyrrvm9Rw4pLgWPWl8j6FhKThg&from=Obejor&to=08035066498&body=" . $bmessg . "&dnd=3";
		
		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL, "https://www.bulksmsnigeria.com/api/v1/sms/create");*/
		        //curl_setopt($ch, CURLOPT_URL, "http://www.estoresms.com/smsapi.php?");
		        //curl_setopt($ch, CURLOPT_HEADER, 1);
		        /*curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		        curl_setopt ($ch, CURLOPT_POST, 1); 
		        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	            curl_exec($ch);
		        curl_close($ch);*/
		        
		        //end    
		        
		        
			$store_logo = '';
			$store_banner = '';
			$path = 'image/catalog/Seller_'.$customer_id.'/';
			$file = "";
					if (!is_dir($path)) {
						@mkdir($path, 0777);
					}
					if(is_dir($path)){
                        if(isset($_FILES['upload_file']['name'])) {
                        $allowed_file=array('gif','png','jpg','pdf','doc','docx','zip');
                        $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($_FILES['upload_file']['name'], ENT_QUOTES, 'UTF-8')));
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    if($filename != '') {
                        if(in_array($extension,$allowed_file) ) {
                            $file = md5(mt_rand()).'-'.$filename;
                            $directory  = $path;
                        
                            move_uploaded_file($_FILES['upload_file']['tmp_name'], $directory.'/'.$file);
                        }     
                    }
                   
					}          
                    }
			
		
			
					if (!is_dir($path)) {
						@mkdir($path, 0777);
					}
					if(is_dir($path)){
                        if(isset($_FILES['store_logo']['name'])) {
                        $allowed_file=array('gif','png','jpg');
                        $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($_FILES['store_logo']['name'], ENT_QUOTES, 'UTF-8')));
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    if($filename != '') {
                        if(in_array($extension,$allowed_file) ) {
                            $file = md5(mt_rand()).'-'.$filename;
                            $directory  = $path;
							$store_logo = 'catalog/Seller_'.$customer_id.'/'.$file;
                        
                            move_uploaded_file($_FILES['store_logo']['tmp_name'], $directory.'/'.$file);
                        }     
                    }
                   
						}         
                    }
					if(is_dir($path)){
                        
                        $allowed_file=array('gif','png','jpg');
                        $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($_FILES['store_banner']['name'], ENT_QUOTES, 'UTF-8')));
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    if($filename != '') {
                        if(in_array($extension,$allowed_file) ) {
                            $file = md5(mt_rand()).'-'.$filename;
                            $directory  = $path;
                            $store_banner = 'catalog/Seller_'.$customer_id.'/'.$file;
                            move_uploaded_file($_FILES['store_banner']['tmp_name'], $directory.'/'.$file);
                        }     
                    }
                 
                                
                    }
					
			     $store_name = trim($this->request->post['store_name']);
					$this->load->model('extension/purpletree_multivendor/vendor');
					$file = '';
				
					
					$seller_id = $this->model_extension_purpletree_multivendor_vendor->addSeller($t_customer_data['customer_id'],$store_name ,$file,"ADMIN",$t_customer_data['customer_id'],1);
				// 	if ($customer_id){
				// 	    $this->subscribePlan($customer_id);
				// 	}
					$store_id = $this->model_extension_purpletree_multivendor_vendor->getStoreId($t_customer_data['customer_id']);
					$this->model_extension_purpletree_multivendor_vendor->editStore($store_id, $this->request->post,$file);
					$this->model_extension_purpletree_multivendor_vendor->editStoreImage($store_id,$store_logo,$store_banner);
								
		    ////////// Start register mail for seller////////////
		    $this->load->language('mail/register');
		    $this->load->language('account/ptsregister');
			$data['text_welcome'] = sprintf($this->language->get('text_welcome'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$data['text_login'] = $this->language->get('text_login');
			$data['text_approval'] = $this->language->get('text_approval');
			$data['text_service'] = $this->language->get('text_service');
			$data['text_thanks'] = $this->language->get('text_thanks');
			$this->load->model('account/customer_group');
				
			if (isset($emaildata['customer_group_id'])) {
				$customer_group_id = $emaildata['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
			$data['text_admin'] ="";
			if($this->config->get('module_purpletree_multivendor_seller_approval') == 1){
				$data['text_admin'] = $this->language->get('text_admin');
			}
						
			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
			
			if ($customer_group_info) {
				$data['approval'] = $customer_group_info['approval'];
			} else {
				$data['approval'] = '';
			}
				
			$data['login'] = $this->url->link('account/login', '', true);		
			$data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($emaildata['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(sprintf($this->language->get('text_subject_seller'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
			$mail->setText($this->load->view('account/purpletree_multivendor/register_mail', $data));
			//$mail->send();
		
		//////End register mail for seller////////////
		

        //added 06/03/2020 ici
		    $bmessg = $this->load->view('account/purpletree_multivendor/register_mail', $data);
	    	$subject = html_entity_decode(sprintf($this->language->get('text_subject_seller'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
	    	$cemail = $emaildata['email'];
	    	$postdata = "email=" . $cemail . "&body=" . $bmessg . "&subject=" . $subject;
		
	    	$ch = curl_init();
	    	curl_setopt($ch, CURLOPT_URL, "http://www.obejorgroup.com/sendemail.php");
	    	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    	curl_setopt ($ch, CURLOPT_POST, 1); 
	    	curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	        curl_exec($ch);
	    	curl_close($ch);	
		                
        //****end************************//

		
		/////// Start alert mail for admin///////////
		if (in_array('account', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/register');		
			$this->load->language('account/ptsregister');			
			$data['text_signup_seller'] = $this->language->get('text_signup_seller');
			$data['text_firstname'] = $this->language->get('text_firstname');
			$data['text_lastname'] = $this->language->get('text_lastname');
			$data['text_customer_group'] = $this->language->get('text_customer_group');
			$data['text_email'] = $this->language->get('text_email');
			$data['text_telephone'] = $this->language->get('text_telephone');
			
			if (isset($this->request->post['firstname'])) {
				$data['firstname'] = $this->request->post['firstname'];
			} else {
				$data['firstname'] = '';
			}

			if (isset($this->request->post['lastname'])) {
				$data['lastname'] = $this->request->post['lastname'];
			} else {
				$data['lastname'] = '';
			}
			
			$this->load->model('account/customer_group');
			
			if (isset($emaildata['customer_group_id'])) {
				$customer_group_id = $emaildata['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
			
			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
			
			if ($customer_group_info) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '';
			}
			
			$data['email'] = $emaildata['email'];
			$data['telephone'] = $emaildata['telephone'];

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($this->language->get('text_new_Seller'), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('account/purpletree_multivendor/register_alertmail', $data));
			//$mail->send();

        //added 06/03/2020 ici
		    $bmessg = $this->load->view('account/purpletree_multivendor/register_alertmail', $data);
	    	$subject = html_entity_decode($this->language->get('text_new_Seller'), ENT_QUOTES, 'UTF-8');
	    	$cemail = $this->config->get('config_email');
	    	$postdata = "email=" . $cemail . "&body=" . $bmessg . "&subject=" . $subject;
		
	    	$ch = curl_init();
	    	curl_setopt($ch, CURLOPT_URL, "http://www.obejorgroup.com/sendemail.php");
	    	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    	curl_setopt ($ch, CURLOPT_POST, 1); 
	    	curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	        curl_exec($ch);
	    	curl_close($ch);	
		                
        //****end************************//


			// Send to additional alert emails if new account email is enabled
			$emails1 = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails1 as $email1) {
				if (utf8_strlen($email1) > 0 && filter_var($email1, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email1);
					//$mail->send();
				}
			}		
		}	
		 /////// End alert mail for Admin ///////////
		 
            	if (!$this->customer->isLogged()) {
			// Clear any previous login attempts for unregistered accounts.
					$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);

					$this->customer->login($this->request->post['email'], $this->request->post['password']);
                    if (!$this->customer->isLogged()) {
                       $this->response->redirect($this->url->link('extension/account/purpletree_multivendor/sellerlogin', '', true));
                     }
					unset($this->session->data['guest']);

				}
				$this->response->redirect($this->url->link('account/success', '', true));
			//$this->response->redirect($this->url->link('account/success')); 
			//$this->response->redirect($this->url->link(	'extension/account/purpletree_multivendor/sellerstore/becomeseller', '', true));
			
		} //end for validate
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_seller_register_page'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/sellerregister', '', true)
		);
		
		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', true));

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		} 
		if (isset($this->error['seller_paypal_id'])) {
			$data['error_seller_paypal_id'] = $this->error['seller_paypal_id'];
		} else {
			$data['error_seller_paypal_id'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['custom_field'])) {
			$data['error_custom_field'] = $this->error['custom_field'];
		} else {
			$data['error_custom_field'] = array();
		}

		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}

		if (isset($this->error['confirm'])) {
			$data['error_confirm'] = $this->error['confirm'];
		} else {
			$data['error_confirm'] = '';
		}
		if (isset($this->error['seller_store'])) {
				$data['error_sellerstore'] = $this->error['seller_store'];
			} else {
				$data['error_sellerstore'] = '';
			}
	 /* if (isset($this->error['store_email'])) {
			$data['error_storeemail'] = $this->error['store_email'];
		} else {
			$data['error_storeemail'] = '';
		} */
		if (isset($this->error['store_seo'])) {
			$data['error_store_seo'] = $this->error['store_seo'];
		} else {
			$data['error_store_seo'] = '';
		}
			

		$data['action'] = $this->url->link('extension/account/purpletree_multivendor/sellerregister', '', true);
		$data['sellerlogin'] = $this->url->link('extension/account/purpletree_multivendor/sellerlogin', '', true);

		$data['customer_groups'] = array();

		if (is_array($this->config->get('config_customer_group_display'))) {
			$this->load->model('account/customer_group');

			$customer_groups = $this->model_account_customer_group->getCustomerGroups();

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$data['customer_groups'][] = $customer_group;
				}
			}
		}

		if (isset($this->request->post['customer_group_id'])) {
			$data['customer_group_id'] = $this->request->post['customer_group_id'];
		} else {
			$data['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} else {
			$data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} else {
			$data['telephone'] = '';
		}

		// Custom Fields
		$data['custom_fields'] = array();
		
		$this->load->model('account/custom_field');
		
		$custom_fields = $this->model_account_custom_field->getCustomFields();
		
		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'account') {
				$data['custom_fields'][] = $custom_field;
			}
		}
		
		if (isset($this->request->post['custom_field']['account'])) {
			$data['register_custom_field'] = $this->request->post['custom_field']['account'];
		} else {
			$data['register_custom_field'] = array();
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		if (isset($this->request->post['confirm'])) {
			$data['confirm'] = $this->request->post['confirm'];
		} else {
			$data['confirm'] = '';
		}

		/* if (isset($this->request->post['newsletter'])) {
			$data['newsletter'] = $this->request->post['newsletter'];
		} else {
			$data['newsletter'] = '';
		} */
		if (isset($this->request->post['store_name'])) {
				$data['store_name'] = $this->request->post['store_name'];
			} else {
				$data['store_name'] = '';
			}
		if (isset($this->request->post['store_live_chat_enable'])) { 
			$data['store_live_chat_enable'] = $this->request->post['store_live_chat_enable'];	
		} else { 
			$data['store_live_chat_enable'] = 0;
		}
		if (isset($this->request->post['store_live_chat_code'])) { 
			$data['store_live_chat_code'] = $this->request->post['store_live_chat_code'];		
		} else { 
			$data['store_live_chat_code'] = '';
		}
		/* if (isset($this->request->post['store_email'])) {
			$data['store_email'] = $this->request->post['store_email'];		
		} else {
			$data['store_email'] = '';
		} */
		
		if (isset($this->request->post['store_phone'])) {
			$data['store_phone'] = $this->request->post['store_phone'];		
		} else {
			$data['store_phone'] = '';
		}	
		if (isset($this->request->post['store_address'])) {
			$data['store_address'] = $this->request->post['store_address'];		
		} else {
			$data['store_address'] = '';
		}
		
		if (isset($this->request->post['store_country'])) {
			$data['store_country'] = $this->request->post['store_country'];		
		} else {
			$data['store_country'] = '';
		}
		
		if (isset($this->request->post['store_state'])) {
			$data['store_state'] = $this->request->post['store_state'];		
		} else {
			$data['store_state'] = '';
		}
		
		if (isset($this->request->post['store_city'])) {
			$data['store_city'] = $this->request->post['store_city'];		
		} else {
			$data['store_city'] = '';
		}
		
		if (isset($this->request->post['store_zipcode'])) {
			$data['store_zipcode'] = $this->request->post['store_zipcode'];		
		} else {
			$data['store_zipcode'] = '';
		}
		
		if (isset($this->request->post['store_shipping_policy'])) {
			$data['store_shipping_policy'] = $this->request->post['store_shipping_policy'];		
		} else {
			$data['store_shipping_policy'] = '';
		}
		
		if (isset($this->request->post['store_return_policy'])) {
			$data['store_return_policy'] = $this->request->post['store_return_policy'];		
		} else {
			$data['store_return_policy'] = '';
		}	
		if (isset($this->request->post['store_shipping_type'])) {
			$data['store_shipping_type'] = $this->request->post['store_shipping_type'];		
		} else {
			$data['store_shipping_type'] = 'pts_flat_rate_shipping';
		}
		if (isset($this->request->post['store_meta_keywords'])) {
			$data['store_meta_keywords'] = $this->request->post['store_meta_keywords'];
		} else {
			$data['store_meta_keywords'] = '';
		}
		
		if (isset($this->request->post['store_meta_description'])) {
			$data['store_meta_description'] = $this->request->post['store_meta_description'];
		} else {
			$data['store_meta_description'] = '';
		}
        if (isset($this->request->post['store_bank_details'])) {
			$data['store_bank_details'] = $this->request->post['store_bank_details'];		
		} else {
			$data['store_bank_details'] = '';
		}
		
		if (isset($this->request->post['store_tin'])) {
			$data['store_tin'] = $this->request->post['store_tin'];		
		} else {
			$data['store_tin'] = '';
		}		
		if (isset($this->request->post['store_shipping_charge'])) {
			$data['store_shipping_charge'] = $this->request->post['store_shipping_charge'];		
		} else {
			$data['store_shipping_charge'] = '';
		}	
		if (isset($this->request->post['seller_paypal_id'])) {
			$data['seller_paypal_id'] = $this->request->post['seller_paypal_id'];		
		} else {
			$data['seller_paypal_id'] = '';
		}	
			
		if (isset($this->request->post['store_logo'])) {
			$data['store_logo'] = $this->request->post['store_logo'];		
		} else {
			$data['store_logo'] = '';
		}


		$this->load->model('tool/image');

		if (isset($this->request->post['store_logo']) && is_file(DIR_IMAGE . $this->request->post['store_logo'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['store_logo'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (isset($this->request->post['store_banner'])) {
			$data['store_banner'] = $this->request->post['store_banner'];
		} else {
			$data['store_banner'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['store_banner']) && is_file(DIR_IMAGE . $this->request->post['store_banner'])) {
			$data['banner_thumb'] = $this->model_tool_image->resize($this->request->post['store_banner'], 100, 100);
		} else {
			$data['banner_thumb'] = $this->model_tool_image->resize('catalog/purpletree_banner.jpg', 100, 100);
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
			$data['agree'] = $this->request->post['agree'];
		} else {
			$data['agree'] = false;
		}
		if (isset($this->request->post['store_seo'])) { 
			$data['store_seo'] = $this->request->post['store_seo'];		
		} else { 
			$data['store_seo'] = '';
		}
		$data['module_purpletree_multivendor_allow_live_chat'] = 0;
		if(NULL !== $this->config->get('module_purpletree_multivendor_allow_live_chat')) {
			$data['module_purpletree_multivendor_allow_live_chat'] = $this->config->get('module_purpletree_multivendor_allow_live_chat');
		}
		$data['entry_allow_live_chat'] = $this->language->get('entry_allow_live_chat');
		$data['entry_live_chat_code'] = $this->language->get('entry_live_chat_code');
		$data['text_list'] = $this->language->get('text_list');
				$data['entry_firstname']=$this->language->get('entry_firstname');
		$data['text_personal_details']=$this->language->get('text_personal_details');
		$data['text_seller_information1']=$this->language->get('text_seller_information1');
		$data['text_payment_details1']=$this->language->get('text_payment_details1');
		$data['text_seller_login1']=$this->language->get('text_seller_login1');
		$data['text_new_customer_register']=$this->language->get('text_new_customer_register');
		$data['btn_prev']=$this->language->get('btn_prev');
		$data['btn_next']=$this->language->get('btn_next');
		$data['entry_customer_group']=$this->language->get('entry_customer_group');
		$data['entry_lastname']=$this->language->get('entry_lastname');
		$data['entry_email']=$this->language->get('entry_email');
		$data['entry_password']=$this->language->get('entry_password');
		$data['entry_confirm']=$this->language->get('entry_confirm');
		$data['entry_telephone']=$this->language->get('entry_telephone');
		$data['entry_storename']=$this->language->get('entry_storename');
		$data['entry_storeemail']=$this->language->get('entry_storeemail');
		$data['entry_storephone']=$this->language->get('entry_storephone');
		$data['entry_storelogo']=$this->language->get('entry_storelogo');
		$data['entry_storebanner']=$this->language->get('entry_storebanner');
		$data['entry_storeaddress']=$this->language->get('entry_storeaddress');
		$data['entry_storecountry']=$this->language->get('entry_storecountry');
		$data['entry_storezone']=$this->language->get('entry_storezone');
		$data['entry_storecity']=$this->language->get('entry_storecity');
		$data['text_yes']=$this->language->get('text_yes');
		$data['text_no']=$this->language->get('text_no');
		$data['entry_storepostcode']=$this->language->get('entry_storepostcode');
		$data['entry_storeshippingpolicy']=$this->language->get('entry_storeshippingpolicy');
		$data['entry_storereturn']=$this->language->get('entry_storereturn');
		$data['entry_storemetakeyword']=$this->language->get('entry_storemetakeyword');
		$data['entry_storemetadescription']=$this->language->get('entry_storemetadescription');
		$data['entry_storeseo']=$this->language->get('entry_storeseo');
		$data['entry_storemetakeyword']=$this->language->get('entry_storemetakeyword');
		$data['entry_storemetadescription']=$this->language->get('entry_storemetadescription');
		$data['entry_storeseo']=$this->language->get('entry_storeseo');
		$data['entry_storebankdetail']=$this->language->get('entry_storebankdetail');
		$data['entry_storebankdetail1']=$this->language->get('entry_storebankdetail1');
		$data['entry_storetin']=$this->language->get('entry_storetin');
		$data['entry_storetin1']=$this->language->get('entry_storetin1');
		$data['entry_storetin']=$this->language->get('entry_storetin');
		$data['entry_seller_paypal_id']=$this->language->get('entry_seller_paypal_id1');
		$data['text_select']=$this->language->get('text_select');
		$data['text_none']=$this->language->get('text_none');
		$data['btn_submit1']=$this->language->get('btn_submit1');
		$data['error_enter_firstname']=$this->language->get('error_enter_firstname');
		$data['error_enter_lastname']=$this->language->get('error_enter_lastname');
		$data['error_enter_password']=$this->language->get('error_enter_password');
		$data['error_enter_password_lenght']=$this->language->get('error_enter_password_lenght');
		$data['error_enter_email_address']=$this->language->get('error_enter_email_address');
		$data['error_enter_confirm_password']=$this->language->get('error_enter_confirm_password');
		
        $this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header'); 

		$this->response->setOutput($this->load->view('account/purpletree_multivendor/sellerregister', $data));
	}

	private function validate() {
		
		$this->load->model('extension/purpletree_multivendor/vendor');
		$store_info1 = $this->model_extension_purpletree_multivendor_vendor->getStoreNameByStoreName($this->request->post['store_name']);
		if(!empty($store_info1)){		
		    if ($store_info1 && (strtoupper(trim($this->request->post['store_name']))==strtoupper($store_info1['store_name']))) {
				$this->error['seller_store'] = $this->language->get('error_exist_storename');
				$this->error['warning'] = $this->language->get('error_warning');
		    }
		}
		$seller_seo = $this->model_extension_purpletree_multivendor_vendor->getStoreSeo($this->request->post['store_seo']);
		if((utf8_strlen($this->request->post['store_seo']))) {
		    $pattern = '/[\'\/~`\!@#\$%\^&\*\(\)\+=\{\}\[\]\|;:"\<\>,\.\?\\\ ]/';
		    if (preg_match($pattern, $this->request->post['store_seo'])==true) {
		    	$this->error['store_seo'] = $this->language->get('error_store_seo');
		    } else {
		    	if(isset($seller_seo['query'])){
					$this->error['store_seo'] = $this->language->get('error_storeseo');
		    	}
	    	}
		}
			if(!$this->customer->validateSeller()) {
				$this->error['warning1'] = $this->language->get('error_license');
			}				
			if((utf8_strlen($this->request->post['store_name']) < 5) || (utf8_strlen(trim($this->request->post['store_name'])) > 50)) {
			$this->error['seller_store'] = $this->language->get('error_storename');			
		}		
	/* 	if(!empty($this->request->post['store_email'])){
		$EMAIL_REGEX='/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/';
		
			if (preg_match($EMAIL_REGEX, $this->request->post['store_email'])==false)	
			{
				$this->error['store_email'] = $this->language->get('error_email');
			}
		} */
		if(!empty($this->request->post['seller_paypal_id'])){
		$EMAIL_REGEX='/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/';
		
			if (preg_match($EMAIL_REGEX, $this->request->post['seller_paypal_id'])==false)	
			{
				$this->error['seller_paypal_id'] = $this->language->get('error_email');
			}
		}
		if (!$this->customer->isLogged()) {
		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}		

		if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		// Customer Group
		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		// Custom field validation
		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		/* foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == 'account') {
				if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
					$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
					$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				}
			}
		} */

		if ((utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) || (utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		// Agree to terms
		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}
		}
		return !$this->error;
	}

	public function customfield() {
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function subscribePlan($customer_id) {
				$url="";							
					$this->load->language('purpletree_multivendor/subscriptionplan');
					$this->document->setTitle($this->language->get('heading_title'));
					$this->load->model('extension/purpletree_multivendor/subscriptionplan');
					$data=array();

				$plan_id=$this->model_extension_purpletree_multivendor_subscriptionplan->defaultPlan();
				if($plan_id){	
					$seller_id=$customer_id;
					$startt_when =0;
					$s_date = 0;
					$data['plan_id']=$plan_id;
					$data['seller_id']=$seller_id;
					$data['startt_when']=$startt_when;
					$current_plan=$this->model_extension_purpletree_multivendor_subscriptionplan->getPlan($seller_id);
				if($startt_when == 1) {
					
					$current_plan_start_date=$this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlanByPlanId($seller_id,$plan_id);
					
					$current_plan_start_date1=$this->model_extension_purpletree_multivendor_subscriptionplan->getLastPlan($seller_id,$plan_id);
					$validity=$this->model_extension_purpletree_multivendor_subscriptionplan->validity($plan_id);
					
				if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
					
				$current_plan_end_date=($current_plan_start_date1['new_end_date']!='0000-00-00 00:00:00')?date('m/d/Y H:i:s',strtotime($current_plan_start_date1['new_end_date'])):date('m/d/Y H:i:s', strtotime($current_plan_start_date1['start_date']. ' + '.$validity.' days'));
				
				} else {
				$current_plan_end_date=($current_plan_start_date1['end_date']!='0000-00-00 00:00:00')?date('m/d/Y H:i:s',strtotime($current_plan_start_date1['end_date'])):date('m/d/Y H:i:s', strtotime($current_plan_start_date1['start_date']. ' + '.$validity.' days'));	
				}
					
				$data['start_date'] =date('Y-m-d H:i:s',strtotime($current_plan_end_date));

				} else {
					$data['start_date'] = date('Y-m-d H:i:s');
				}
					$data['current_date'] = date('Y-m-d H:i:s');
					$data['end_date']='';
					$old_invoice_id=$this->model_extension_purpletree_multivendor_subscriptionplan->getInvoiceId($seller_id);
					$result=$this->model_extension_purpletree_multivendor_subscriptionplan->getSubscribePlanInfo($plan_id);
					$currentplan=$this->model_extension_purpletree_multivendor_subscriptionplan->getCurrentPlan($seller_id);
				if($this->config->get("module_purpletree_multivendor_tax_name")){
					$tax_name=$this->config->get("module_purpletree_multivendor_tax_name");		
				} else {
					$tax_name='';	
				}

				if($this->config->get("module_purpletree_multivendor_tax_value")){
					$tax=$this->config->get("module_purpletree_multivendor_tax_value");		
				} else {
					$tax=0;	
				}
					$current_invoice=$this->model_extension_purpletree_multivendor_subscriptionplan->getSellerCurrentPlan($seller_id);
					$curr_invoice=array();

				if(!empty($current_invoice)){
					foreach($current_invoice as $value){
						$curr_invoice[$value['code']]=$value['price'];
					}
				}

				if($this->config->get("module_purpletree_multivendor_joining_fees")){
					$joining_fee=$result['joining_fee'];
				} else {
					$joining_fee=0;	
				}
				
				if($this->config->get("module_purpletree_multivendor_subscription_price")){
					$subscription_price=$result['subscription_price'];	
				} else {
					$subscription_price=0;	
				}

				$data['totals']['plan']=array();
				$data['totals']['plan'][]=array(
					'sort_order'=>0,
					'code'=>'subscription_price',
					'title'=>'Subscription Price',
					'value'=>$subscription_price
				);
if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){		
if($startt_when!=1){			
				$data['totals']['plan'][]=array(
					'sort_order'=>1,
					'code'=>'joining_fee',
					'title'=>'Joining Fee',
					'value'=>$joining_fee
				);
} else {
	$joining_fee=0;
}
} else {
				$data['totals']['plan'][]=array(
					'sort_order'=>1,
					'code'=>'joining_fee',
					'title'=>'Joining Fee',
					'value'=>$joining_fee
				);	
}
				$a_joiningfee = $joining_fee;
if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				if(isset($currentplan)){				
					$a_joiningfee=$joining_fee -	$current_plan['joining_fee'];
					$data['totals']['plan'][]=array(
						'sort_order'=>2,
						'code'=>'adjustment_Joining_fee',
						'title'=>'Adjustment Joining fee',
						'value'=>$a_joiningfee
					);	
					$subscription_price = $subscription_price - $this->remindPrice($current_plan['start_date'],$current_plan['validity'],$current_plan['subscription_price'],$s_date);
					$data['totals']['plan'][]=array(
						'sort_order'=>3,
						'code'=>'adjustment_subscription_price',
						'title'=>'Adjustment Subscription Price',
						'value'=>$subscription_price
					);
					$previous_balance=0;

					if($subscription_price<0){
						$previous_balance = $subscription_price;
					}
				}
}
				$subscription_price=$a_joiningfee+$subscription_price ;
				$total_amount= $subscription_price;
				$cal_tax=($total_amount*$tax)/100;
				$data['totals']['plan'][]=array(
					'sort_order'=>4,
					'code'=>'tax',
					'title'=>$tax_name.' ('.$tax.'%)',
					'value'=>$cal_tax
				);

				$current_invo=0;
				if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				if(isset($currentplan)){
					if(isset($curr_invoice['previous_balance'])){
					$current_invo=$curr_invoice['previous_balance'];	
					}				
				}
				}
				$total=$total_amount+$cal_tax+$current_invo;
				$invoice_bal=0;
				if($total<0){
					$invoice_bal=$total;	
				} 
			if(!$this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
				$data['totals']['plan'][]=array(
					'sort_order'=>5,
					'code'=>'previous_balance',
					'title'=>'Previous Balance',
					'value'=>$invoice_bal
				 );
			}
				$total=$total_amount+$cal_tax+$current_invo;

				//if plan free or grand total less then zero
				$data['vendor_invoice_status']=1;
				if($total<=0){
				$data['vendor_invoice_status']=2;
				}
				
				
				//end
				$invoice_mail=array();
				foreach($data['totals']['plan'] as $resultPlan){
					if($resultPlan['code']!='previous_balance'){
						 $invoice_mail['mail'][]=array(
							'title'=>$resultPlan['title'],
							'price'=>$resultPlan['value']
						);
					} else {
						$invoice_mail['mail'][]=array(
							'title'=>$resultPlan['title'],
							'price'=>$current_invo
						);
					}
				}
				$start=($result['start_date']!='0000-00-00 00:00:00')?date('d/m/Y H:i:s',strtotime($result['start_date'])):'';
				$end=($result['end_date']!='0000-00-00 00:00:00')?date('d/m/Y H:i:s',strtotime($result['end_date'])):date('d/m/Y H:i:s', strtotime($result['start_date']. ' + '.$result['validity'].' days'));
				$customer = $this->model_extension_purpletree_multivendor_subscriptionplan->getCustomer($customer_id);
				$message='';
				$message.='Seller Name- '.$customer['firstname'].' '.$customer['lastname'].'<br>';
				$message.='Email Id- '.$customer['email'].'<br>';
				$message.='Plan Name- '.$result['plan_name'].'<br>';
				$message.='No Of Product- '.$result['no_of_product'].'<br>';
				$message.='Validity- '.$result['validity'].'<br>';
				$message.='Start Date- '.$start.'<br>';
				$message.='End Date- '.$end.'<br>';
				foreach($invoice_mail['mail'] as $msg){
					$message.=$msg['title'].'- '.$this->currency->format($msg['price'], $this->session->data['currency']).'<br>';	
				}
				$message.='Grand Total- '.$total.'<br>';
		// end new seller 
			//	if (($this->request->server['REQUEST_METHOD'] == 'POST') /* && $this->validateForm() */) {

					$invoice_id=$this->model_extension_purpletree_multivendor_subscriptionplan->addSellerMultiplePlan($data);
					
					$sellerExist=$this->model_extension_purpletree_multivendor_subscriptionplan->SellerExist($customer_id);
					$this->load->language('purpletree_multivendor/subscriptionplan');
					$email_subject= $this->language->get('email_subject');
					if(!$sellerExist){
						$sellerExist=$this->model_extension_purpletree_multivendor_subscriptionplan->addFirstSellerPlan($customer_id);	
						$email_subject = $this->language->get('email_first_subject');
					}
					//if plan free or grand total less then zero
					if($total<=0){
					$this->model_extension_purpletree_multivendor_subscriptionplan->enableSellerSubscription($customer_id);
					}
				//end
					// Mail 		
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
					$mail->setTo($customer['email']);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender(html_entity_decode('Seller Name', ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode(sprintf( $email_subject , $customer['firstname']), ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($message);
					//$mail->send();
					//end mail
						// Mail 		
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
					//$mail->setTo($customer['email']);
					$mail->setTo($this->config->get('config_email'));
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender(html_entity_decode('Seller Name', ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode(sprintf( $email_subject , $customer['firstname']), ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($message);
					//$mail->send();
					//$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/subscriptionplan/invoice', '' . 'invoice_id=' . $invoice_id .'&old_invoice_id='.$old_invoice_id. $url, true));
				//}
				}
			//$this->getplan();
		}
		
	public function remindPrice($start_date,$validity,$s_price,$s_date){
		$this->load->language('purpletree_multivendor/subscriptionplan');
		$this->document->setTitle($this->language->get('heading_title'));
		$price=0;
		if($s_date == '1') {
			return $price;
		}

		$date1=date_create(date('Y-m-d'));
		$date2=date_create(date('Y-m-d',strtotime($start_date)));
		$diff=date_diff($date2,$date1);

		$r_date=$validity-((int)$diff->format("%a"));
		
		if($r_date>=0){
		    $price=($s_price*$r_date)/$validity;
		}

		return $price;
	}

	
}
?>