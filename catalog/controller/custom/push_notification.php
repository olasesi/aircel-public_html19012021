<?php
  class ControllerCustomPushNotification extends Controller {
    public function subscribe () {
      $this->load->model('custom/push_notification');
      $userid   = isset($this->request->post['customer_id']) ? $this->request->post['customer_id'] : $this->customer->getId() !== null ? $this->customer->getId() :  '';
      $token    = $this->request->post['token'];
      $platform = isset($this->request->post['platform']) ? $this->request->post['platform'] : 'browser'; 
      $usertype = isset($this->request->post['usertype']) ? $this->request->post['usertype'] : 'buyer'; 
      $insert = $this->model_custom_push_notification->addNotificationSubcriber($userid,$token,$usertype,$platform); 
      if($insert){
        $json['message'] = 'Subscription successful';
		    $json['status'] = true;
      }else{
        $json['message'] = 'Subscription Failed';
		    $json['status'] = false;
      }
      $this->response->addHeader('Content-Type: application/json');
      $this->response->addHeader('Access-Control-Allow-Origin: *');
      $this->response->addHeader('Access-Control-Allow-Headers: *');
	  $this->response->setOutput(json_encode($json));
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function key()
    {
      $this->load->model('custom/push_notification');
      $json = ['status'=>false,'message'=>'Unable to fetch subscription key'];
      $config = $this->model_custom_push_notification->getPushKeys();
      if($config){
        $json['message'] = 'Success';
        $json['data'] = $config['web_push_public_key'];
		    $json['status'] = true;
      }
      $this->response->addHeader('Content-Type: application/json');
      $this->response->addHeader('Access-Control-Allow-Origin: *');
      $this->response->addHeader('Access-Control-Allow-Headers: *');
	  $this->response->setOutput(json_encode($json));

    }
    
    public function unsubscribe(){
      $this->load->model('custom/push_notification');
      $token = $this->request->post['token'];
      $this->model_custom_push_notification->unSubscribe($token);
      $json['message'] = 'Success';
	  $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
      $this->response->addHeader('Access-Control-Allow-Origin: *');
      $this->response->addHeader('Access-Control-Allow-Headers: *');
	  $this->response->setOutput(json_encode($json));
    }
    
    public function unsubscribeuser(){
      $this->load->model('custom/push_notification');
      $user = $this->request->post['user'];
      $this->model_custom_push_notification->unsubscribeUserNotifications($user);
      $json['message'] = 'Success';
	    $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
      $this->response->addHeader('Access-Control-Allow-Origin: *');
      $this->response->addHeader('Access-Control-Allow-Headers: *');
	  $this->response->setOutput(json_encode($json));
    }
    
    public function updatetoken()
    {
      $this->load->model('custom/push_notification');
      $oldtoken = $this->request->post['oldtoken'];
      $newtoken = $this->request->post['newtoken'];
      $this->model_custom_push_notification->updateNotificationToken($oldtoken,$newtoken);
      $json['message'] = 'Token update success';
	    $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
	  $this->response->setOutput(json_encode($json)); 
    }
    
    public function send(){
        $this->load->model('custom/push_notification');
        $sellerid =  $this->request->post['sellerid'];
        
        $res = $this->model_custom_push_notification->sendSellerPushNotification($sellerid,'Product: Microsoft Wireless Mobile Mouse 1850  Order Status: Pending','Order Information',array( 'page'=>"order","id"=>"1373","date"=>"2020-03-27 17:34:11" ),'');
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: *');
	    $this->response->setOutput($res);
    }

    public function tapped(){
      $this->load->model('custom/push_notification');
      $notificationId =  $this->request->post['notificationid'];
      $subscriptionId =  $this->request->post['subscriptionid'];
      
      $res = $this->model_custom_push_notification->updateNotificationClicks($notificationId);
      $res = $this->model_custom_push_notification->updateSuscriberNotificationClicks($subscriptionId);
      $this->response->addHeader('Content-Type: application/json');
      $this->response->addHeader('Access-Control-Allow-Origin: *');
      $this->response->addHeader('Access-Control-Allow-Headers: *');
      $this->response->setOutput($res);
    }

    public function recieved(){
      $this->load->model('custom/push_notification');
      $subscriptionId =  $this->request->post['subscriptionid'];
      $notificationId =  $this->request->post['notificationid'];
      $res = $this->model_custom_push_notification->updateNotificationRecieves($notificationId);
      $res = $this->model_custom_push_notification->updateSubscriberNotificationRecieves($subscriptionId);
      $this->response->addHeader('Content-Type: application/json');
      $this->response->addHeader('Access-Control-Allow-Origin: *');
      $this->response->addHeader('Access-Control-Allow-Headers: *');
      $this->response->setOutput($res);
    }
  }
?>