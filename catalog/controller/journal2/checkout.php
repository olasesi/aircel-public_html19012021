<?php

/**
 * @property Journal2 $journal2
 * @property ModelJournal2Checkout model_journal2_checkout
 */

class ControllerJournal2Checkout extends Controller {

    protected $data = array();

    protected function renderView($template) {
        if (version_compare(VERSION, '2.2', '<')) {
            $template = $this->config->get('config_template') . '/template/' . $template;
        }

        $template = str_replace($this->config->get('config_template') . '/template/' . $this->config->get('config_template') . '/template/', $this->config->get('config_template') . '/template/', $template);
        $this->template = $template;

        if (version_compare(VERSION, '3', '>=')) {
            return $this->load->view(str_replace('.tpl', '', $this->template), $this->data);
        }

        return Front::$IS_OC2 ? $this->load->view($this->template, $this->data) : parent::render();
    }

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->language('checkout/cart');
        $this->load->language('checkout/checkout');

        if (version_compare(VERSION, '2', '>=')) {
            $this->load->language('checkout/coupon');
            $this->load->language('checkout/voucher');

            $this->load->model('account/activity');
            $this->load->model('account/custom_field');
            $this->load->model('tool/upload');
        }
        $this->load->model('account/address');
        $this->load->model('account/customer');
        $this->load->model('account/customer_group');
        $this->load->model('journal2/checkout');
        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');
    }

    public function index() {
        if (!$this->checkCart()) {
            $this->response->redirect(Journal2Utils::link('checkout/cart'));
            exit;
        }

		if (!$this->customer->isLogged() && $this->config->get('config_customer_price')) {
			$this->response->redirect(Journal2Utils::link('account/login'));
			exit;
		}

		$this->journal2->html_classes->addClass('quick-checkout-page');

        $this->load->language('checkout/checkout');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => Journal2Utils::link('common/home')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_cart'),
            'href' => Journal2Utils::link('checkout/cart')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => Journal2Utils::link('checkout/checkout', '', 'SSL')
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->document->setTitle($this->language->get('heading_title'));

		if (version_compare(VERSION, '3', '>=')) {
			$this->journal2->minifier->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->journal2->minifier->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->journal2->minifier->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->journal2->minifier->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->journal2->minifier->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->journal2->minifier->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			// Required by klarna
			if ($this->config->get('klarna_account') || $this->config->get('klarna_invoice')) {
				$this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
			}
		} else if (version_compare(VERSION, '2', '>=')) {
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			// Required by klarna
			if ($this->config->get('klarna_account') || $this->config->get('klarna_invoice')) {
				$this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
			}
		} else {
			$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
			$this->document->addScript('catalog/view/theme/journal2/lib/bootstrap/button.js');
		}

        $this->data['default_auth'] = Journal2Utils::getProperty($this->session->data, 'journal_checkout_account', $this->journal2->settings->get('one_page_default_auth', 'register'));

        // address data
        if ($this->isLoggedIn()) {
            $this->data['is_logged_in'] = true;
            $this->data['payment_address'] = $this->renderAddressForm('payment');
            $this->data['shipping_address'] = $this->renderAddressForm('shipping');
        } else {
            $this->data['is_logged_in'] = false;
            $this->data['allow_guest_checkout'] = $this->allowGuestCheckout();
            $this->data['register_form'] = $this->renderRegisterForm();
        }

        // shipping
        if ($this->isShippingRequired()) {
            $this->data['is_shipping_required'] = true;
            $this->data['shipping_methods'] = $this->shipping(true);
        } else {
            $this->data['is_shipping_required'] = false;
        }

        // payment
        $this->data['payment_methods'] = $this->payment(true);

        // coupon + voucher
        $this->data['coupon_voucher_reward'] = $this->renderCouponVoucherReward();

        // cart
        $this->data['cart'] = $this->cart(true);

        // checkboxes
        if (!$this->isLoggedIn() && $this->config->get('config_account_id')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

            if ($information_info) {
                $this->data['text_privacy'] = sprintf($this->language->get('text_agree'), Journal2Utils::link(version_compare(VERSION, '2', '>=') ? 'information/information/agree' : 'information/information/info', 'information_id=' . $this->config->get('config_account_id'), 'SSL'), $information_info['title'], $information_info['title']);
            } else {
                $this->data['text_privacy'] = '';
            }
        } else {
            $this->data['text_privacy'] = '';
        }

        if ($this->config->get('config_checkout_id')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

            if ($information_info) {
                $this->data['text_agree'] = sprintf($this->language->get('text_agree'), Journal2Utils::link(version_compare(VERSION, '2', '>=') ? 'information/information/agree' : 'information/information/info', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);
            } else {
                $this->data['text_agree'] = '';
            }
        } else {
            $this->data['text_agree'] = '';
        }

        if ($this->data['text_privacy'] === $this->data['text_agree']) {
            $this->data['text_privacy'] = '';
        }

        $this->data['text_comments'] = $this->language->get('text_comments');
        if ($this->isLoggedIn()) {
            $this->data['entry_newsletter'] = false;
        } else {
            $this->data['entry_newsletter'] = sprintf($this->language->get('entry_newsletter'), $this->config->get('config_name'));
        }

        $this->data['comment'] = $this->model_journal2_checkout->getComment();

        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $this->data['error_warning'] = '';
        }

        if (version_compare(VERSION, '2', '>=')) {
            $this->data['column_left'] = $this->load->controller('common/column_left');
            $this->data['column_right'] = $this->load->controller('common/column_right');
            $this->data['content_top'] = $this->load->controller('common/content_top');
            $this->data['content_bottom'] = $this->load->controller('common/content_bottom');
            $this->data['footer'] = $this->load->controller('common/footer');
            $this->data['header'] = $this->load->controller('common/header');
        } else {
            $this->children = array(
                'common/column_left',
                'common/column_right',
                'common/content_top',
                'common/content_bottom',
                'common/footer',
                'common/header'
            );
        }

        $this->model_journal2_checkout->save();

        $this->response->setOutput($this->renderView('journal2/checkout/checkout.tpl'));

    }

    public function save() {
        if ($value = Journal2Utils::getProperty($this->request->post, 'shipping_address_id')) {
            $this->session->data['shipping_address'] = $this->model_account_address->getAddress($value);
            $this->model_journal2_checkout->setAddress('shipping', $this->session->data['shipping_address']);
        }

        if ($value = Journal2Utils::getProperty($this->request->post, 'shipping_country_id')) {
            $this->model_journal2_checkout->setAddress('shipping', array(
                'country_id'    => $value,
                'zone_id'       => Journal2Utils::getProperty($this->request->post, 'shipping_zone_id'),
                'postcode'      => Journal2Utils::getProperty($this->request->post, 'shipping_postcode'),
            ));
        }

        if ($value = Journal2Utils::getProperty($this->request->post, 'shipping_method')) {
			$this->model_journal2_checkout->getShippingMethods();
            $shipping = explode('.', $value);
            $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
        }

        if ($value = Journal2Utils::getProperty($this->request->post, 'payment_address_id')) {
            $this->session->data['payment_address'] = $this->model_account_address->getAddress($value);
            $this->model_journal2_checkout->setAddress('payment', $this->session->data['payment_address']);
        }

        if ($value = Journal2Utils::getProperty($this->request->post, 'payment_country_id')) {
            $this->model_journal2_checkout->setAddress('payment', array(
                'country_id'    => $value,
                'zone_id'       => Journal2Utils::getProperty($this->request->post, 'payment_zone_id'),
                'postcode'      => Journal2Utils::getProperty($this->request->post, 'payment_postcode'),
            ));
        }

        if ($value = Journal2Utils::getProperty($this->request->post, 'payment_method')) {
            $this->session->data['payment_method'] = $this->session->data['payment_methods'][$value];
        }

        $this->model_journal2_checkout->save();

        header('Content-Type: application/json');
        echo '{}';

        exit;
    }

    public function confirm() {
        /* exit if page is accessed via get method */
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            return;
        }
        $order_data = $this->model_journal2_checkout->getOrder();

        $new_payment_address = null;
        $new_shipping_address = null;

        $register_account = Journal2Utils::getProperty($this->request->post, 'account') === 'register';

        $errors = array();
        $redirect_cart = '';

        if (!$this->checkCart()) {
            $errors['cart'] = '';
            $redirect_cart = Journal2Utils::link('checkout/cart');
        }

        if ($this->isLoggedIn()) {
            // payment data
            if (Journal2Utils::getProperty($this->request->post, 'payment_address') === 'existing') {
                $address_info = $this->model_account_address->getAddress(Journal2Utils::getProperty($this->request->post, 'payment_address_id'));
                $order_data = array_replace($order_data, $this->getAddressData($address_info, '', 'payment_'));
            } else {
                $new_payment_address = $this->getAddressData($this->request->post, 'payment_', 'payment_');
                $order_data = array_replace($order_data, $new_payment_address);
                $errors = array_merge($errors, $this->validateAddressData($new_payment_address, 'payment_'));
            }

            // shipping data
            if ($this->isShippingRequired()) {
                if (Journal2Utils::getProperty($this->request->post, 'shipping_address') === 'existing') {
                    $address_info = $this->model_account_address->getAddress(Journal2Utils::getProperty($this->request->post, 'shipping_address_id'));
                    $order_data = array_replace($order_data, $this->getAddressData($address_info, '', 'shipping_'));
                } else {
                    $new_shipping_address = $this->getAddressData($this->request->post, 'shipping_', 'shipping_');
                    $order_data = array_replace($order_data, $new_shipping_address);
                    $errors = array_merge($errors, $this->validateAddressData($new_shipping_address, 'shipping_'));
                }
            }

            // customer data
            if (!$errors) {
                $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

                $order_data['customer_id'] = $this->customer->getId();
                $order_data['customer_group_id'] = $customer_info['customer_group_id'];
                $order_data['firstname'] = $customer_info['firstname'];
                $order_data['lastname'] = $customer_info['lastname'];
                $order_data['email'] = $customer_info['email'];
                $order_data['telephone'] = $customer_info['telephone'];
                $order_data['fax'] = $customer_info['fax'];
                if (version_compare(VERSION, '2', '>=')) {
                    $order_data['custom_field'] = version_compare(VERSION, '2.1', '>=') ? json_decode($customer_info['custom_field'], true) : unserialize($customer_info['custom_field']);
                }
            }
        } else {
            // check firstname, lastname
            $errors = array_merge($errors, $this->validateUserData($this->request->post, $register_account));

            // check customer group id
            if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                $order_data['customer_group_id'] = $this->request->post['customer_group_id'];
            } else {
                $order_data['customer_group_id'] = $this->config->get('config_customer_group_id');
            }

            // check passwords if register
            if ($register_account) {
                $errors = array_merge($errors, $this->validatePassword($this->request->post));
            }

            // check payment address
            $new_payment_address = $this->getAddressData($this->request->post, 'payment_', 'payment_');
            $order_data = array_replace($order_data, $new_payment_address);
            $errors = array_merge($errors, $this->validateAddressData($new_payment_address, 'payment_', false));

            // add payment firstname and lastname
            $order_data['firstname'] = $this->request->post['firstname'];
            $order_data['lastname'] = $this->request->post['lastname'];
            $order_data['email'] = $this->request->post['email'];
            $order_data['telephone'] = $this->request->post['telephone'];
            $order_data['fax'] = isset($this->request->post['fax']) ? $this->request->post['fax'] : '';
            $order_data['custom_field'] = Journal2Utils::getProperty($this->request->post, 'custom_field', array());
            $order_data['payment_firstname'] = $order_data['firstname'];
            $order_data['payment_lastname'] = $order_data['lastname'];

            // check delivery address
            if ($this->isShippingRequired()) {
                if (!Journal2Utils::getProperty($this->request->post, 'shipping_address')) {
                    $new_shipping_address = $this->getAddressData($this->request->post, 'shipping_', 'shipping_');
                    $order_data = array_replace($order_data, $new_shipping_address);
                    $errors = array_merge($errors, $this->validateAddressData($new_shipping_address, 'shipping_'));
                } else {
                    $order_data = array_replace($order_data, $this->getAddressData($order_data, 'payment_', 'shipping_'));
                }
            }
        }

        // payment method
        if ($payment_method = Journal2Utils::getProperty($this->session->data, 'payment_methods.' . Journal2Utils::getProperty($this->request->post, 'payment_method') . '.title')) {
            $order_data['payment_method'] = $payment_method;
            $order_data['payment_code'] = Journal2Utils::getProperty($this->request->post, 'payment_method');
        } else {
            $errors['payment_method'] = str_replace('&nbsp;', '', strip_tags($this->language->get('error_no_payment')));
        }

        // shipping method
        if ($this->isShippingRequired()) {
            $shipping = explode('.', Journal2Utils::getProperty($this->request->post, 'shipping_method'));
            if (is_array($shipping) && count($shipping) > 1) {
                $shipping_method = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
                if ($shipping_method) {
                    $order_data['shipping_method'] = $shipping_method['title'];
                    $order_data['shipping_code'] = Journal2Utils::getProperty($this->request->post, 'shipping_method');
                } else {
                    $order_data['shipping_method'] = 'no shipping method';
					$errors['shipping_method'] = str_replace('&nbsp;', '', strip_tags($this->language->get('error_no_shipping')));
                }
            } else {
                $order_data['shipping_method'] = 'no shipping method';
				$errors['shipping_method'] = str_replace('&nbsp;', '', strip_tags($this->language->get('error_no_shipping')));
            }
        }

        // order totals
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        if (version_compare(VERSION, '3', '>=')) {
			$this->load->model('setting/extension');
			$results = $this->model_setting_extension->getExtensions('total');
		} else if (version_compare(VERSION, '2', '>=')) {
            $this->load->model('extension/extension');
            $results = $this->model_extension_extension->getExtensions('total');
        } else {
            $this->load->model('setting/extension');
            $results = $this->model_setting_extension->getExtensions('total');
        }

        $sort_order = array();

        foreach ($results as $key => $value) {
            if (version_compare(VERSION, '3', '>=')) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			} else {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if (version_compare(VERSION, '3', '>=')) {
				$status = $this->config->get('total_' . $result['code'] . '_status');
			} else {
				$status = $this->config->get($result['code'] . '_status');
			}

			if ($status) {
                if (version_compare(VERSION, '2.3', '<')) {
                    $this->load->model('total/' . $result['code']);
                } else {
                    $this->load->model('extension/total/' . $result['code']);
                }

                if (version_compare(VERSION, '2.2', '<')) {
                    $this->{'model_total_' . $result['code']}->getTotal($totals, $total, $taxes);
                } else if (version_compare(VERSION, '2.3', '<')) {
                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_total_' . $result['code']}->getTotal($total_data);
                } else {
                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
            }
        }

        $sort_order = array();

        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $totals);

        $order_data['totals'] = $totals;
        $order_data['total'] = $total;

        // order products
        $order_data['products'] = array();

        foreach ($this->cart->getProducts() as $product) {
            $option_data = array();

            foreach ($product['option'] as $option) {
                $option_data[] = array(
                    'product_option_id'       => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id'               => $option['option_id'],
                    'option_value_id'         => $option['option_value_id'],
                    'name'                    => $option['name'],
                    'value'                   => version_compare(VERSION, '2', '>=') ? $option['value'] : $option['option_value'],
                    'type'                    => $option['type']
                );
            }

            $order_data['products'][] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'option'     => $option_data,
                'download'   => $product['download'],
                'quantity'   => $product['quantity'],
                'subtract'   => $product['subtract'],
                'price'      => $product['price'],
                'total'      => $product['total'],
                'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward'     => $product['reward']
            );
        }

        // Gift Voucher
        $order_data['vouchers'] = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $order_data['vouchers'][] = array(
                    'description'      => $voucher['description'],
                    'code'             => substr(md5(mt_rand()), 0, 10),
                    'to_name'          => $voucher['to_name'],
                    'to_email'         => $voucher['to_email'],
                    'from_name'        => $voucher['from_name'],
                    'from_email'       => $voucher['from_email'],
                    'voucher_theme_id' => $voucher['voucher_theme_id'],
                    'message'          => $voucher['message'],
                    'amount'           => $voucher['amount']
                );
            }
        }

        // comment + checkboxes
        $order_data['comment'] = Journal2Utils::getProperty($this->request->post, 'comment');

        if (!$this->isLoggedIn() && $this->config->get('config_account_id')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

            if ($information_info && !isset($this->request->post['privacy'])) {
                $errors['privacy'] = sprintf($this->language->get('error_agree'), $information_info['title']);
            }
        }

        if ($this->config->get('config_checkout_id')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

            if ($information_info && !isset($this->request->post['agree'])) {
                $errors['agree'] = sprintf($this->language->get('error_agree'), $information_info['title']);
            }
        }

        if ($this->config->get('config_account_id') == $this->config->get('config_checkout_id')) {
            unset($errors['privacy']);
        }

        $redirect = '';

        // update order
        $this->model_journal2_checkout->setOrderData($order_data);
//        $this->model_journal2_checkout->save();

        if (Journal2Utils::getProperty($this->request->get, 'saveOnly') === 'true') {
            header('Content-Type: application/json');
            echo json_encode(array(
                'order_data'=> $order_data
            ));
            exit;
        }

        if (!$errors) {
            if ($this->isLoggedIn()) {
                // save new payment address
                if ($new_payment_address) {
                    $this->addAddress($this->getAddressData($new_payment_address, 'payment_'), $this->customer->getId());
                }

                // save new shipping address
                if ($new_shipping_address && $new_shipping_address !== $new_payment_address) {
                    $this->addAddress($this->getAddressData($new_shipping_address, 'shipping_'), $this->customer->getId());
                }

                $this->model_journal2_checkout->updateCustomer();
            } else if ($register_account) {
                $redirect = $this->registerAccount();
                $this->model_journal2_checkout->updateCustomer();
            } else {
                if (Journal2Utils::getProperty($this->request->post, 'newsletter')) {
                    if (!class_exists('Journal2Newsletter')) {
                        require_once DIR_SYSTEM . 'journal2/classes/journal2_newsletter.php';
                    }
                    $newsletter = new Journal2Newsletter($this->registry, Journal2Utils::getProperty($this->request->post, 'email'));
                    if (!$newsletter->isSubscribed()) {
                        $newsletter->subscribe();
                        // Clear Thinking: MailChimp Integration
        				/*if (version_compare(VERSION, '2.1', '<')) $this->load->library('mailchimp_integration');
        				$mailchimp_integration = new MailChimp_Integration($this->registry);
        				$mailchimp_integration->send(array('newsletter' => 1, 'email' => $this->request->post['email'], 'customer_id' => $this->customer->getId()));*/
        				// end
                    }
                }
                $this->session->data['guest'] = $this->getAddressData($order_data, 'payment_');
            }
        }

        $this->session->data['journal_checkout_account'] = Journal2Utils::getProperty($this->request->post, 'account');
        $this->session->data['journal_checkout_shipping_address'] = Journal2Utils::getProperty($this->request->post, 'shipping_address', '0');

        // send response
        header('Content-Type: application/json');
        echo json_encode(array(
            'errors'    => $errors ? $errors : null,
            'account_status' => $this->isLoggedIn() ? 1 : 0,
            'redirect'  => $redirect,
            'redirect_cart' => $redirect_cart,
            'order_data'=> $order_data
        ));
        exit;
    }

    public function shipping($return = false) {
        $this->data['text_shipping_method'] = $this->language->get('text_shipping_method');

        $this->data['shipping_methods'] = $this->model_journal2_checkout->getShippingMethods();
        $this->data['code'] = $this->model_journal2_checkout->getShippingMethodCode();

        if (!$this->data['shipping_methods']) {
            $this->data['error_warning'] = sprintf($this->language->get('error_no_shipping'), Journal2Utils::link('information/contact'));
        } else {
            $this->data['error_warning'] = '';
        }

        if ($return) {
            return $this->renderView('journal2/checkout/shipping_methods.tpl');
        } else {
            $this->response->setOutput($this->renderView('journal2/checkout/shipping_methods.tpl'));
        }
    }

    public function payment($return = false) {
        $this->data['text_payment_method'] = $this->language->get('text_payment_method');

        $this->data['payment_methods'] = $this->model_journal2_checkout->getPaymentMethods();
        $this->data['code'] = $this->model_journal2_checkout->getPaymentMethodCode();

        if (!$this->data['payment_methods']) {
            $this->data['error_warning'] = sprintf($this->language->get('error_no_payment'), Journal2Utils::link('information/contact'));
        } else {
            $this->data['error_warning'] = '';
        }

        if ($return) {
            return $this->renderView('journal2/checkout/payment_methods.tpl');
        } else {
            $this->response->setOutput($this->renderView('journal2/checkout/payment_methods.tpl'));
        }
    }

    public function cart($return = false) {
        $this->data['text_recurring_item'] = $this->language->get('text_recurring_item');
        $this->data['text_payment_recurring'] = $this->language->get('text_payment_recurring');

        $this->data['button_update'] = $this->language->get('button_update');
        $this->data['button_remove'] = $this->language->get('button_remove');

        $this->data['column_image'] = $this->language->get('column_image');
        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_model'] = $this->language->get('column_model');
        $this->data['column_quantity'] = $this->language->get('column_quantity');
        $this->data['column_price'] = $this->language->get('column_price');
        $this->data['column_total'] = $this->language->get('column_total');

        $this->data['products'] = $this->model_journal2_checkout->getProducts();
        $this->data['vouchers'] = $this->model_journal2_checkout->getVouchers();
        $this->data['totals']   = $this->model_journal2_checkout->getTotals();

        if ($value = Journal2Utils::getProperty($this->session->data, 'payment_method.code')) {
            if (version_compare(VERSION, '2', '>=')) {
                if (version_compare(VERSION, '2.3', '<')) {
                    $this->data['payment'] = $this->load->controller('payment/' . $value);
                } else {
                    $this->data['payment'] = $this->load->controller('extension/payment/' . $value);
                }
            } else {
                $this->data['payment'] = $this->getChild('payment/' . $this->session->data['payment_method']['code']);
            }
        } else {
            $this->data['payment'] = '';
        }

        $this->data['payment_code'] = Journal2Utils::getProperty($this->session->data, 'payment_method.code');

        if ($return) {
            return $this->renderView('journal2/checkout/cart.tpl');
        } else {
            $this->response->setOutput($this->renderView('journal2/checkout/cart.tpl'));
        }
    }

    public function cart_update() {
        $key = Journal2Utils::getProperty($this->request->post, 'key');
        $qty = Journal2Utils::getProperty($this->request->post, 'quantity');
        $this->cart->update($key, $qty);

        $json = array();

        if (!$this->checkCart()) {
            $json['redirect'] = Journal2Utils::link('checkout/cart');
        } else {
            $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), Journal2Utils::currencyFormat($this->model_journal2_checkout->getTotal()));
        }

        echo json_encode($json);
        exit;
    }

    public function cart_delete() {
        $key = Journal2Utils::getProperty($this->request->post, 'key');

        $this->cart->remove($key);

        unset($this->session->data['vouchers'][$key]);

        $json = array();

        if (!$this->checkCart()) {
            $json['redirect'] = Journal2Utils::link('checkout/cart');
        } else {
            $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), Journal2Utils::currencyFormat($this->model_journal2_checkout->getTotal()));
        }

        echo json_encode($json);
        exit;
    }

    public function coupon() {
        $json = array();

        $this->load->model('checkout/coupon');

        if (isset($this->request->post['coupon'])) {
            $coupon = $this->request->post['coupon'];
        } else {
            $coupon = '';
        }

        $coupon_info = $this->model_checkout_coupon->getCoupon($coupon);

        if (empty($this->request->post['coupon'])) {
            $json['error'] = $this->language->get('error_coupon');
        } elseif ($coupon_info) {
            $this->session->data['coupon'] = $this->request->post['coupon'];

            $this->session->data['success'] = $this->language->get('text_success');

            $json['redirect'] = Journal2Utils::link('checkout/cart');
        } else {
            $json['error'] = $this->language->get('error_coupon');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function voucher() {
        $json = array();

        if (isset($this->request->post['voucher'])) {
            $voucher = $this->request->post['voucher'];
        } else {
            $voucher = '';
        }

        if (version_compare(VERSION, '2.1', '<')) {
            $this->load->model('checkout/voucher');
            $voucher_info = $this->model_checkout_voucher->getVoucher($voucher);
        } else if (version_compare(VERSION, '2.3', '<')) {
            $this->load->model('total/voucher');
            $voucher_info = $this->model_total_voucher->getVoucher($voucher);
        } else {
            $this->load->model('extension/total/voucher');
            $voucher_info = $this->model_extension_total_voucher->getVoucher($voucher);
        }

        if (empty($this->request->post['voucher'])) {
            $json['error'] = $this->language->get('error_voucher');
        } elseif ($voucher_info) {
            $this->session->data['voucher'] = $this->request->post['voucher'];

            $this->session->data['success'] = $this->language->get('text_success');

            $json['redirect'] = Journal2Utils::link('checkout/cart');
        } else {
            $json['error'] = $this->language->get('error_voucher');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function reward() {
        $this->load->language('checkout/reward');

        $json = array();

        $points = $this->customer->getRewardPoints();

        $points_total = 0;

        foreach ($this->cart->getProducts() as $product) {
            if ($product['points']) {
                $points_total += $product['points'];
            }
        }

        if (empty($this->request->post['reward'])) {
            $json['error'] = $this->language->get('error_reward');
        }

        if ($this->request->post['reward'] > $points) {
            $json['error'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
        }

        if ($this->request->post['reward'] > $points_total) {
            $json['error'] = sprintf($this->language->get('error_maximum'), $points_total);
        }

        if (!$json) {
            $this->session->data['reward'] = abs($this->request->post['reward']);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['redirect'])) {
                $json['redirect'] = Journal2Utils::link($this->request->post['redirect']);
            } else {
                $json['redirect'] = Journal2Utils::link('checkout/cart');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function checkCart() {
        // Validate cart has products and has stock.
        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            return false;
        }

        // Validate minimum quantity requirements.
        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                return false;
            }
        }

        return true;
    }

    private function isShippingRequired() {
        return $this->cart->hasShipping();
    }

    private function isLoggedIn() {
        return $this->customer->isLogged();
    }

    private function allowGuestCheckout() {
        return $this->config->get(version_compare(VERSION, '2', '>=') ? 'config_checkout_guest' : 'config_guest_checkout') && !$this->config->get('config_customer_price') && !$this->cart->hasDownload();
    }

    private function renderAddressForm($type, $name = true) {
        $this->data['type'] = $type;
        $this->data['name'] = $name;

        $this->data['button_upload'] = $this->language->get('button_upload');
        $this->data['text_address_existing'] = $this->language->get('text_address_existing');
        $this->data['text_address_new'] = $this->language->get('text_address_new');
        $this->data['text_select'] = $this->language->get('text_select');
        $this->data['text_none'] = $this->language->get('text_none');

        $this->data['entry_firstname'] = $this->language->get('entry_firstname');
        $this->data['entry_lastname'] = $this->language->get('entry_lastname');
        $this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_company_id'] = $this->language->get('entry_company_id');
        $this->data['entry_tax_id'] = $this->language->get('entry_tax_id');
        $this->data['entry_address_1'] = $this->language->get('entry_address_1');
        $this->data['entry_address_2'] = $this->language->get('entry_address_2');
        $this->data['entry_postcode'] = $this->language->get('entry_postcode');
        $this->data['entry_city'] = $this->language->get('entry_city');
        $this->data['entry_country'] = $this->language->get('entry_country');
        $this->data['entry_zone'] = $this->language->get('entry_zone');

        $this->data['custom_fields'] = $this->model_journal2_checkout->getCustomFields($type);
        $this->data['order_data'] = $this->model_journal2_checkout->getOrder();

        $this->data['addresses'] = $this->customer->isLogged() ? $this->model_account_address->getAddresses() : array();
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        $address = $this->model_journal2_checkout->getAddress($type);
        foreach ($address as $key => $value) {
            $this->data[$key] = $value;
        }

        if (!version_compare(VERSION, '2', '>=') && $this->customer->isLogged()) {
            $customer_group_info = $this->model_account_customer_group->getCustomerGroup($this->customer->getCustomerGroupId());

            if ($customer_group_info) {
                $this->data['company_id_display'] = $customer_group_info['company_id_display'];
            } else {
                $this->data['company_id_display'] = '';
            }

            if ($customer_group_info) {
                $this->data['company_id_required'] = $customer_group_info['company_id_required'];
            } else {
                $this->data['company_id_required'] = '';
            }

            if ($customer_group_info) {
                $this->data['tax_id_display'] = $customer_group_info['tax_id_display'];
            } else {
                $this->data['tax_id_display'] = '';
            }

            if ($customer_group_info) {
                $this->data['tax_id_required'] = $customer_group_info['tax_id_required'];
            } else {
                $this->data['tax_id_required'] = '';
            }
        }

        return $this->renderView('journal2/checkout/address_form.tpl');
    }

    private function renderRegisterForm() {
        $this->data['text_register'] = $this->language->get('text_register');
        $this->data['text_guest'] = $this->language->get('text_guest');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_password'] = $this->language->get('entry_password');
        $this->data['text_forgotten'] = $this->language->get('text_forgotten');
        $this->data['text_loading'] = $this->journal2->settings->get('one_page_lang_loading_text', $this->language->get('text_loading'));
        $this->data['button_login'] = $this->language->get('button_login');
        $this->data['text_i_am_returning_customer'] = $this->language->get('text_i_am_returning_customer');
        $this->data['text_returning_customer'] = $this->language->get('text_returning_customer');

        $this->data['text_your_details'] = $this->language->get('text_your_details');
        $this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
        $this->data['entry_firstname'] = $this->language->get('entry_firstname');
        $this->data['entry_lastname'] = $this->language->get('entry_lastname');
        $this->data['entry_telephone'] = $this->language->get('entry_telephone');
        $this->data['entry_fax'] = $this->language->get('entry_fax');
        $this->data['text_your_password'] = $this->language->get('text_your_password');
        $this->data['entry_confirm'] = $this->language->get('entry_confirm');
        $this->data['text_your_address'] = $this->language->get('text_your_address');
        $this->data['entry_shipping'] = $this->language->get('entry_shipping');

        $this->data['customer_groups'] = array();
        $this->data['customer_group_id'] = $this->model_journal2_checkout->getCustomerGroupId();
        if (is_array($this->config->get('config_customer_group_display'))) {
            $this->load->model('account/customer_group');

            $customer_groups = $this->model_account_customer_group->getCustomerGroups();

            foreach ($customer_groups  as $customer_group) {
                if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                    $this->data['customer_groups'][] = $customer_group;
                }
            }
        }

        $this->data['payment_address_form'] = $this->renderAddressForm('payment', false);
        $this->data['shipping_address_form'] = $this->renderAddressForm('shipping');
        $this->data['shipping_address'] = Journal2Utils::getProperty($this->session->data, 'journal_checkout_shipping_address', '1');
        $this->data['is_shipping_required'] = $this->isShippingRequired();

        $this->data['forgotten'] = Journal2Utils::link('account/forgotten', '', 'SSL');

        $this->data['custom_fields'] = $this->model_journal2_checkout->getCustomFields();
        $this->data['order_data'] = $this->model_journal2_checkout->getOrder();

        return $this->renderView('journal2/checkout/register.tpl');
    }

    private function renderCouponVoucherReward() {
        $this->data['text_loading'] = $this->journal2->settings->get('one_page_lang_loading_text', $this->language->get('text_loading'));

        $this->data['coupon_status'] = $this->config->get(version_compare(VERSION, '3', '>=') ? 'total_coupon_status' : 'coupon_status');
        $this->data['entry_coupon'] = $this->language->get('entry_coupon');
        $this->data['button_coupon'] = $this->language->get('button_coupon');
        $this->data['coupon'] = Journal2Utils::getProperty($this->session->data, 'coupon');

        $this->data['voucher_status'] = $this->config->get(version_compare(VERSION, '3', '>=') ? 'total_voucher_status' : 'voucher_status');
        $this->data['entry_voucher'] = $this->language->get('entry_voucher');
        $this->data['button_voucher'] = $this->language->get('button_voucher');
        $this->data['voucher'] = Journal2Utils::getProperty($this->session->data, 'voucher');

        $points = $this->customer->getRewardPoints();

        $points_total = 0;

        foreach ($this->cart->getProducts() as $product) {
            if ($product['points']) {
                $points_total += $product['points'];
            }
        }

        if (version_compare(VERSION, '2.3', '<')) {
            $this->load->language('total/reward');

        } else {
            $this->load->language('extension/total/reward');
        }

        $this->data['reward_status'] = $points && $points_total && $this->config->get(version_compare(VERSION, '3', '>=') ? 'total_reward_status' : 'reward_status');
        $this->data['entry_reward'] = $this->language->get('entry_reward');
        $this->data['button_reward'] = $this->language->get('button_reward');
        $this->data['reward'] = Journal2Utils::getProperty($this->session->data, 'reward');
        $this->data['reward_use'] = sprintf($this->language->get('entry_reward'), $points_total);
        $this->data['reward_total'] = sprintf($this->language->get('text_reward'), $points);

        return $this->renderView('journal2/checkout/coupon_voucher_reward.tpl');
    }

    private function getAddressData($array, $key = '', $prefix = '') {
        $keys = array(
            'address_1',
            'address_2',
            'address_id',
            'address_format',
            'city',
            'company',
            'company_id',
            'country',
            'country_id',
            'firstname',
            'lastname',
            'method',
            'postcode',
            'tax_id',
            'zone',
            'zone_id'
        );

        $result = array();

        foreach ($keys as $k) {
            $result[$prefix . $k] = Journal2Utils::getProperty($array, $key . $k, '');
        }

        if ($result[$prefix . 'country_id']) {
            $country_info = $this->model_localisation_country->getCountry($result[$prefix . 'country_id']);
            if ($country_info) {
                if (!$result[$prefix . 'country']) {
                    $result[$prefix . 'country'] = $country_info['name'];
                }
                $result[$prefix . 'address_format'] = $country_info['address_format'];
            }
        }

        if (!$result[$prefix . 'zone'] && $result[$prefix . 'zone_id']) {
            $zone_info = $this->model_localisation_zone->getZone($result[$prefix . 'zone_id']);
            if ($zone_info) {
                $result[$prefix . 'zone'] = $zone_info['name'];
            }
        }

        if (version_compare(VERSION, '2', '>=')) {
            $result[$prefix . 'custom_field'] = Journal2Utils::getProperty($array, $key . 'custom_field', array());
        }

        return $result;
    }

    private function validateUserData($data, $register) {
        $errors = array();

        // firstname
        if ((utf8_strlen(trim($data['firstname'])) < 1) || (utf8_strlen(trim($data['firstname'])) > 32)) {
            $errors['firstname'] = $this->language->get('error_firstname');
        }

        // lastname
        if ((utf8_strlen(trim($data['lastname'])) < 1) || (utf8_strlen(trim($data['lastname'])) > 32)) {
            $errors['lastname'] = $this->language->get('error_lastname');
        }

        // email
        if ((utf8_strlen($data['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $data['email'])) {
            $errors['email'] = $this->language->get('error_email');
        } else if ($register && $this->model_account_customer->getTotalCustomersByEmail($data['email'])) {
            $errors['email'] = $this->language->get('error_exists');
        }

        // telephone
        if (($this->journal2->settings->get('one_page_phone_required', '1') == '1') && ((utf8_strlen($data['telephone']) < 3) || (utf8_strlen($data['telephone']) > 32))) {
            $errors['telephone'] = $this->language->get('error_telephone');
        }

        // Custom field validation
        if (version_compare(VERSION, '2', '>=')) {
            $custom_fields = $this->model_journal2_checkout->getCustomFields();

            foreach ($custom_fields as $custom_field) {
                if (($custom_field['location'] == 'account') && $custom_field['required'] && !($data['custom_field'][$custom_field['custom_field_id']])) {
                    $errors['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                }
            }
        }

        return $errors;
    }

    private function validatePassword($data) {
        $errors = array();

        if ((utf8_strlen($data['password']) < 4) || (utf8_strlen($data['password']) > 20)) {
            $errors['password'] = $this->language->get('error_password');
        }

        if ($data['confirm'] != $data['password']) {
            $errors['confirm'] = $this->language->get('error_confirm');
        }

        return $errors;
    }

    private function validateAddressData($data, $key, $name = true) {
        $errors = array();

        if ($name) {
            // firstname
            if ((utf8_strlen(trim($data[$key . 'firstname'])) < 1) || (utf8_strlen(trim($data[$key . 'firstname'])) > 32)) {
                $errors[$key . 'firstname'] = $this->language->get('error_firstname');
            }

            // lastname
            if ((utf8_strlen(trim($data[$key . 'lastname'])) < 1) || (utf8_strlen(trim($data[$key . 'lastname'])) > 32)) {
                $errors[$key . 'lastname'] = $this->language->get('error_lastname');
            }
        }

        if ((utf8_strlen(trim($data[$key . 'address_1'])) < 3) || (utf8_strlen(trim($data[$key . 'address_1'])) > 128)) {
            $errors[$key . 'address_1'] = $this->language->get('error_address_1');
        }

        if ((utf8_strlen($data[$key . 'city']) < 2) || (utf8_strlen($data[$key . 'city']) > 32)) {
            $errors[$key . 'city'] = $this->language->get('error_city');
        }

        $country_info = $this->model_localisation_country->getCountry($data[$key . 'country_id']);

        if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($data[$key . 'postcode'])) < 2 || utf8_strlen(trim($data[$key . 'postcode'])) > 10)) {
            $errors[$key . 'postcode'] = $this->language->get('error_postcode');
        }

        if ($data[$key . 'country_id'] == '') {
            $errors[$key . 'country'] = $this->language->get('error_country');
        }

        if (!isset($data[$key . 'zone_id']) || $data[$key . 'zone_id'] == '' || !is_numeric($data[$key . 'zone_id'])) {
            $errors[$key . 'zone'] = $this->language->get('error_zone');
        }

        // Custom field validation
        if (version_compare(VERSION, '2', '>=')) {
            $custom_fields = $this->model_journal2_checkout->getCustomFields();
            foreach ($custom_fields as $custom_field) {
                if (($custom_field['location'] == 'address') && $custom_field['required'] && !($data[$key . 'custom_field'][$custom_field['custom_field_id']])) {
                    $errors[$key . 'custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                }
            }
        } else {
            $customer_group = $this->model_account_customer_group->getCustomerGroup(Journal2Utils::getProperty($this->request->post, 'customer_group_id', $this->model_journal2_checkout->getCustomerGroupId()));

            if ($customer_group) {
                // Company ID
                if ($customer_group['company_id_display'] && $customer_group['company_id_required'] && empty($data[$key . 'company_id'])) {
                    $errors[$key . 'company_id'] = $this->language->get('error_company_id');
                }

                // Tax ID
                if ($customer_group['tax_id_display'] && $customer_group['tax_id_required'] && empty($data[$key . 'tax_id'])) {
                    $errors[$key . 'tax_id'] = $this->language->get('error_tax_id');
                }
            }

            // VAT Validation
            $this->load->helper('vat');

            if ($country_info && $this->config->get('config_vat') && $data[$key . 'tax_id'] && (vat_validation($country_info['iso_code_2'], $data[$key . 'tax_id']) == 'invalid')) {
                $errors[$key . 'tax_id'] = $this->language->get('error_vat');
            }
        }

        return $errors;
    }

    private function registerAccount() {
        $redirect = '';

        $data = $this->getAddressData($this->request->post, 'payment_');

        $data = array_merge($data, array(
            'firstname'     => Journal2Utils::getProperty($this->request->post, 'firstname'),
            'lastname'      => Journal2Utils::getProperty($this->request->post, 'lastname'),
            'customer_group_id'=> Journal2Utils::getProperty($this->request->post, 'customer_group_id', $this->config->get('config_customer_group_id')),
            'custom_field'  => array(
                'account'   => Journal2Utils::getProperty($this->request->post, 'custom_field'),
                'address'   => Journal2Utils::getProperty($this->request->post, 'payment_custom_field'),
            ),
            'email'         => Journal2Utils::getProperty($this->request->post, 'email'),
            'telephone'     => Journal2Utils::getProperty($this->request->post, 'telephone'),
            'fax'           => Journal2Utils::getProperty($this->request->post, 'fax'),
            'password'      => Journal2Utils::getProperty($this->request->post, 'password'),
            'newsletter'    => Journal2Utils::getProperty($this->request->post, 'newsletter')
        ));

        $customer_id = $this->model_account_customer->addCustomer($data);

        if (version_compare(VERSION, '3', '>=')) {
        	$address_data = $this->getAddressData($this->request->post, 'payment_');

        	$address_data['firstname'] = $this->request->post['firstname'];
			$address_data['lastname'] = $this->request->post['lastname'];

			$address_id = $this->addAddress($address_data, $customer_id);

			// Set the address as default
			$this->model_account_customer->editAddressId($customer_id, $address_id);
		}

        // Clear any previous login attempts for unregistered accounts.
        if (version_compare(VERSION, '2', '>=')) {
            $this->model_account_customer->deleteLoginAttempts($data['email']);
        }

        $this->session->data['account'] = 'register';

        $customer_group_info = $this->model_account_customer_group->getCustomerGroup(Journal2Utils::getProperty($this->request->post, 'customer_group_id', $this->config->get('config_customer_group_id')));

        if ($customer_group_info && !$customer_group_info['approval']) {
            $this->customer->login($data['email'], $data['password']);

            if (Journal2Utils::getProperty($this->request->post, 'shipping_address') != '1') {
                $this->addAddress($this->getAddressData($this->request->post, 'shipping_'), $customer_id);
            }

            // Add to activity log
            $activity_data = array(
                'customer_id' => $customer_id,
                'name' => $data['firstname'] . ' ' . $data['lastname']
            );

            if (version_compare(VERSION, '2', '>=')) {
                $this->model_account_activity->addActivity('register', $activity_data);
            }
        } else {
            $redirect = Journal2Utils::link('account/success');
        }

        return $redirect;
    }

    public function login() {
        $this->load->language('checkout/checkout');

        $json = array();

        if ($this->customer->isLogged()) {
            $json['redirect'] = Journal2Utils::link('checkout/checkout', '', 'SSL');
        }

        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            $json['redirect'] = Journal2Utils::link('checkout/cart');
        }

        if (version_compare(VERSION, '2', '>=')) {
            if (!$json) {
                $this->load->model('account/customer');

                // Check how many login attempts have been made.
                $login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

                if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
                    $json['error']['warning'] = $this->language->get('error_attempts');
                }

                // Check if customer has been approved.
                $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

				if (version_compare(VERSION, '3', '>=')) {
					if ($customer_info && !$customer_info['status']) {
						$json['error']['warning'] = $this->language->get('error_approved');
					}
				} else {
					if ($customer_info && !$customer_info['approved']) {
						$json['error']['warning'] = $this->language->get('error_approved');
					}
				}

                if (!isset($json['error'])) {
                    if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
                        $json['error']['warning'] = $this->language->get('error_login');

                        $this->model_account_customer->addLoginAttempt($this->request->post['email']);
                    } else {
                        $this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
                    }
                }
            }

            if (!$json) {
                unset($this->session->data['guest']);

                $this->load->model('account/address');

                $address_info = $this->model_account_address->getAddress($this->customer->getAddressId());

                if ($this->config->get('config_tax_customer') == 'payment') {
                    $this->session->data['payment_address'] = $address_info;
                }

                if ($this->config->get('config_tax_customer') == 'shipping') {
                    $this->session->data['shipping_address'] = $address_info;
                }

                $this->model_journal2_checkout->setAddress('shipping', $address_info);
                $this->model_journal2_checkout->setAddress('payment', $address_info);
                $this->model_journal2_checkout->save();

                $json['redirect'] = Journal2Utils::link('checkout/checkout', '', 'SSL');

                // Add to activity log
                $this->load->model('account/activity');

                $activity_data = array(
                    'customer_id' => $this->customer->getId(),
                    'name' => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
                );

                $this->model_account_activity->addActivity('login', $activity_data);
            }
        } else {
            if (!$json) {
                if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
                    $json['error']['warning'] = $this->language->get('error_login');
                }

                $this->load->model('account/customer');

                $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

                if ($customer_info && !$customer_info['approved']) {
                    $json['error']['warning'] = $this->language->get('error_approved');
                }
            }

            if (!$json) {
                unset($this->session->data['guest']);

                // Default Addresses
                $this->load->model('account/address');

                $address_info = $this->model_account_address->getAddress($this->customer->getAddressId());

                if ($address_info) {
                    if ($this->config->get('config_tax_customer') == 'shipping') {
                        $this->session->data['shipping_country_id'] = $address_info['country_id'];
                        $this->session->data['shipping_zone_id'] = $address_info['zone_id'];
                        $this->session->data['shipping_postcode'] = $address_info['postcode'];
                    }

                    if ($this->config->get('config_tax_customer') == 'payment') {
                        $this->session->data['payment_country_id'] = $address_info['country_id'];
                        $this->session->data['payment_zone_id'] = $address_info['zone_id'];
                    }
                } else {
                    unset($this->session->data['shipping_country_id']);
                    unset($this->session->data['shipping_zone_id']);
                    unset($this->session->data['shipping_postcode']);
                    unset($this->session->data['payment_country_id']);
                    unset($this->session->data['payment_zone_id']);
                }

                $json['redirect'] = Journal2Utils::link('checkout/checkout', '', 'SSL');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function addAddress($data, $customer_id = null) {
    	if (version_compare(VERSION, '3', '>=')) {
			return $this->model_account_address->addAddress($customer_id, $data);
		}
    	return $this->model_account_address->addAddress($data);
	}

	public function clear_success_message() {
    	if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}
	}

}
