<?php
  class ControllerExtensionAccountPurpletreeMultivendorApiPushnotification extends Controller {
    public function subscribe () {
      $this->load->model('account/user_push_notification');
      $userid = $this->request->post['sellerid'];
      $token = $this->request->post['token'];
      $insert = $this->model_account_user_push_notification->addNotificationSubcriber($userid,$token,'seller'); 
      if($insert){
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
      $this->model_account_user_push_notification->removeNotificationSubscriber($token,'seller');
      $json['message'] = 'Success';
	  $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
	  $this->response->setOutput(json_encode($json));
    }
    
    public function unsubscribeuser(){
      $this->load->model('account/user_push_notification');
      $user = $this->request->post['sellerid'];
      $this->model_account_user_push_notification->unsubscribeUserNotifications($user,'seller');
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
      $this->model_account_user_push_notification->updateNotificationToken($oldtoken,$newtoken,'seller');
      $json['message'] = 'Token update success';
	  $json['status'] = true;
      $this->response->addHeader('Content-Type: application/json');
	  $this->response->setOutput(json_encode($json)); 
    }
  }
?>