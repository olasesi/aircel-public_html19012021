<?php
class ControllerExtensionModuleBrowserNotification extends Controller{

    private $error = array();

    public function index(){

        $data = array(); 

        $this->load->language('extension/module/browser_notification');
        $this->document->setTitle($this->language->get('heading_title'));

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

            $this->load->model('extension/module/browser_notification');
            $this->model_extension_module_browser_notification->createTable();
            
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_browser_notification', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

        $data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/browser_notification', 'user_token=' . $this->session->data['user_token'], true)
		);

        $data['entry_apiKey']       = $this->language->get('entry_apiKey');


        if (isset($this->request->post['module_browser_notification_apiKey'])) {
			$data['apiKey'] = $this->request->post['module_browser_notification_apiKey'];
		} else {
			$data['apiKey'] = $this->config->get('module_browser_notification_apiKey');
		}
		if (isset($this->request->post['module_browser_notification_authDomain'])) {
			$data['authDomain'] = $this->request->post['module_browser_notification_authDomain'];
		} else {
			$data['authDomain'] = $this->config->get('module_browser_notification_authDomain');
		}
		if (isset($this->request->post['module_browser_notification_databaseURL'])) {
			$data['databaseURL'] = $this->request->post['module_browser_notification_databaseURL'];
		} else {
			$data['databaseURL'] = $this->config->get('module_browser_notification_databaseURL');
		}
		if (isset($this->request->post['module_browser_notification_storageBucket'])) {
			$data['storageBucket'] = $this->request->post['module_browser_notification_storageBucket'];
		} else {
			$data['storageBucket'] = $this->config->get('module_browser_notification_storageBucket');
        }
        
        if (isset($this->request->post['module_browser_notification_messagingSenderId'])) {
			$data['messagingSenderId'] = $this->request->post['module_browser_notification_messagingSenderId'];
		} else {
			$data['messagingSenderId'] = $this->config->get('module_browser_notification_messagingSenderId');
        }
        
        if (isset($this->request->post['module_browser_notification_serverKey'])) {
			$data['server_key'] = $this->request->post['module_browser_notification_serverKey'];
		} else {
			$data['server_key'] = $this->config->get('module_browser_notification_serverKey');
        }
        
        if (isset($this->request->post['module_browser_notification_status'])) {
			$data['status'] = $this->request->post['module_browser_notification_status'];
		} else {
			$data['status'] = $this->config->get('module_browser_notification_status');
		}


        $data['action']['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module');
        $data['action']['save'] = "";
        
		$data['error'] = $this->error;	
        
        $data['header']             = $this->load->controller('common/header');
        $data['column_left']        = $this->load->controller('common/column_left');
        $data['footer']             = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/browser_notification', $data));
    }


    public function validate(){

        if (!$this->user->hasPermission('modify', 'extension/module/browser_notification')) {
			$this->error['permission'] = true;
			return false;
		}
		
		if (!utf8_strlen($this->request->post['module_browser_notification_apiKey'])) {
			$this->error['apiKey'] = true;
		}

		if (!utf8_strlen($this->request->post['module_browser_notification_authDomain'])) {
			$this->error['authDomain'] = true;
		}

		if (!utf8_strlen($this->request->post['module_browser_notification_databaseURL'])) {
			$this->error['databaseURL'] = true;
        }
        
        if (!utf8_strlen($this->request->post['module_browser_notification_storageBucket'])) {
			$this->error['storageBucket'] = true;
        }
        
        if (!utf8_strlen($this->request->post['module_browser_notification_messagingSenderId'])) {
			$this->error['messagingSenderId'] = true;
        }

        if (!utf8_strlen($this->request->post['module_browser_notification_serverKey'])) {
			$this->error['serverKey'] = true;
        }
        
		return empty($this->error);
    }

    public function send(){

        $data = array(); 

        $this->load->model('extension/module/browser_notification');
        $this->model_extension_module_browser_notification->createTable();

        
        $this->load->model('extension/module/browser_notification');
        $devices = $this->model_extension_module_browser_notification->getDeviceIds();
        $data['device_count'] = count($devices);
        $error_message = '';
        $success_message = '';
        $title = '';
        $body = '';
        $action = ''; 
        $this->load->language('extension/module/browser_notification');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['error_message'] = '';
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $title = $this->request->post['title'];
            $body = $this->request->post['body'];
            $action = $this->request->post['action'];

            if($title != "" && $body != ''){

                // get Google FCM Server Key
                $serverKey = $this->config->get('module_browser_notification_serverKey');
                if($serverKey != ""){

                    $response = $this->executeFCM($serverKey, $title, $body, $action);

                    if($response){

                        $title = '';
                        $body = '';
                        $action = '';
                        $success_message = $this->language->get('success_notification');
                    }else{

                        $error_message = $this->language->get('error_api');
                    }

                }else{

                    $error_message = $this->language->get('error_module');
                }

            }else{

                $error_message = $this->language->get('error_notification');
            }
        }


        $data['title'] = $title;
        $data['body'] = $body;
        $data['action'] = $action;
        $data['error_message'] = $error_message;
        $data['success_message'] = $success_message;
        
        $data['header']             = $this->load->controller('common/header');
        $data['column_left']        = $this->load->controller('common/column_left');
        $data['footer']             = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/browser_notification_send', $data));
    }


    public function executeFCM($serverKey, $title, $body, $action){

        // get device Ids
        $this->load->model('extension/module/browser_notification');
        $devices = $this->model_extension_module_browser_notification->getDeviceIds();
        $registration_ids = array();
        if(!empty($devices)){

            foreach($devices as $device){
                
                $registration_ids[] = $device['token'];
            }
        }else{
            return false;
        }

        // Execute FCM Curl
        $url = "https://fcm.googleapis.com/fcm/send";
        $notification = array('title' =>$title , 'body' => $body, 'sound' => 'default', 'badge' => '1', 'click_action' => $action);
        $arrayToSend = array('registration_ids' => $registration_ids, 'notification' => $notification,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if ($response === FALSE) {
            return false;
        }else{
            return true;
        }
        curl_close($ch);
    }
}
?>