<?php
  class ControllerApiPushNotifications extends Controller {
    public function subscribe () {
      $this->load->model('account/user_push_notification');
      $userid = $this->request->post['customer_id'];
      $token = $this->request->post['token'];
      $insert = $this->model_account_user_push_notification->addNotificationSubcriber($userid,$token); 
      if($insert['status'] != 'error' ){
        $json['message'] = 'Subscription successful';
		$json['status'] = true;
      }else{
        $json['message'] = 'Subscription Failed';
		$json['status'] = false;
      }
      $this->response->addHeader('Content-Type: application/json');
	  $this->response->setOutput(json_encode($json));
    }
    
    public function unsubscribe(){
      $this->load->model('account/user_push_notification');
      $token = $this->request->post['token'];
      $this->model_account_user_push_notification->removeNotificationSubscriber($token);
      $json['message'] = 'Success';
	  $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
	  $this->response->setOutput(json_encode($json));
    }
    
    public function unsubscribeuser(){
      $this->load->model('account/user_push_notification');
      $user = $this->request->post['user'];
      $this->model_account_user_push_notification->unsubscribeUserNotifications($user);
      $json['message'] = 'Success';
	  $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
	  $this->response->setOutput(json_encode($json));
    }
    
    public function updatetoken()
    {
      $this->load->model('account/user_push_notification');
      $oldtoken = $this->request->post['oldtoken'];
      $newtoken = $this->request->post['newtoken'];
      $this->model_account_user_push_notification->updateNotificationToken($oldtoken,$newtoken);
      $json['message'] = 'Token update success';
	  $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
	  $this->response->setOutput(json_encode($json)); 
    }
    
    public function send(){
        $this->load->model('account/user_push_notification');
        $sellerid =  $this->request->post['sellerid'];
        
        $res = $this->model_account_user_push_notification->sendSellerPushNotification($sellerid,'Product: Microsoft Wireless Mobile Mouse 1850  Order Status: Pending','Order Information',array( 'page'=>"order","id"=>"1373","date"=>"2020-03-27 17:34:11" ),'');
        $this->response->addHeader('Content-Type: application/json');
	    $this->response->setOutput($res);
    }
    
    
  }
?>