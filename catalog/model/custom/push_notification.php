<?php
  require DIR_APPLICATION . 'model/custom/vendor/autoload.php';

  class ModelCustomPushNotification extends Model {
    private $webpushServer = 'https://www.push.obejorgroup.com/api/';
    
    public function getPushKeys(){
      $this->load->model('setting/setting');
      $config = $this->model_setting_setting->getSetting('custom_push');
      if(!$config){
        $key = $this->do_curl($this->webpushServer.'webpush/getkey',[],['api-key: 852e0b-868862-1510e2-412cdd-9bf302']);
        $key = json_decode($key,true);
        if($key && $key['status']){
          $store_id = 0;
          $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape('custom_push') . "', `key` = '" . $this->db->escape('web_push_public_key') . "', `value` = '" . $this->db->escape($key['data']) . "'");
          $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape('custom_push') . "', `key` = '" . $this->db->escape('web_push_private_key') . "', `value` = '" . $this->db->escape('') . "'");   
          $config = $this->model_setting_setting->getSetting('custom_push');
        }
        return $config;
      }else{
        return $config;
      }
    }
    
     public function addNotificationSubcriber($userId,$token,$accounttype = 'buyer',$platform = 'mobile'){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "push_subscribers WHERE token = '".$token."'");
	    if(count($query->row) > 0 && $userId){
	        $sql = "ÜPDATE " . DB_PREFIX . "push_subscribers  SET customer_id ='".(int)$userId."' WHERE token = '".$token."'";
	        $query = $this->db->query($sql);
	    }else if(count($query->row) < 1 && strlen($token) > 0){
	        $this->db->query("INSERT INTO " . DB_PREFIX . "push_subscribers SET customer_id = '".(int)$userId."', token = '".$token. "', usertype = '".$accounttype."',platform =  '".$platform."'");
            $insertId = $this->db->getLastId();
            return $insertId;
	    }
	    return false;
     }
     
     public function getUserTokens($userId,$accounttype = 'buyer',$platform = 'mobile'){
        $sql = "SELECT * FROM " . DB_PREFIX . "push_subscribers WHERE customer_id = '" . (int)$userId ."' AND usertype = '".$accounttype."' AND platform = '".$platform."'";
        $query = $this->db->query($sql);
		    return $query->rows;
     }
     
    public function unSubscribe($token){
      if(strlen($token) > 0){
        $query = $this->db->query("DELETE FROM " . DB_PREFIX . "push_subscribers WHERE  token = '".$token."'");
      }
      return;
    }
    
    public function unsubscribeUserNotifications($user,$accounttype = 'buyer',$platform = 'mobile'){
      $query = $this->db->query("DELETE FROM " . DB_PREFIX . "push_subscribers WHERE customer_id = '" . (int)$userId ."' AND usertype = '".$accounttype."'");
      return;
    }
    
    public function updateNotificationToken($oldtoken,$newtoken,$accounttype = 'buyer'){
        $query = $this->db->query("UPDATE  " . DB_PREFIX . "push_subscribers SET token = '".$newtoken."' WHERE token = '" . $oldtoken ."' AND usertype = '".$accounttype."'");
        return;
    }

    public function updateNotificationClicks($scheduleId)
    {
      $query = $this->db->query("UPDATE  " . DB_PREFIX . "push_notification_schedules SET clicks = clicks + 1 WHERE schedule_id = '" . $this->db->escape($scheduleId) ."'");
      return;
    }
    
    public function updateSuscriberNotificationClicks($subscriptionId)
    {
      $query = $this->db->query("UPDATE  " . DB_PREFIX . "push_subscribers SET clicked = clicked + 1 WHERE id = '" . (int)$subscriptionId ."' OR token = '".$subscriptionId."'");
      return;
    }
    public function updateNotificationRecieves($scheduleId)
    {
      $query = $this->db->query("UPDATE  " . DB_PREFIX . "push_notification_schedules SET delivery = delivery + 1 WHERE schedule_id = '" . (int)$scheduleId ."'");
      return;
    }
    public function updateSubscriberNotificationRecieves($subscriptionId)
    {
      $query = $this->db->query("UPDATE  " . DB_PREFIX . "push_subscribers SET recieved = recieved + 1 WHERE id = '" . $this->db->escape($subscriptionId) ."' OR token = '".$subscriptionId."'");
      return;
    }
    
    public function sendCustomerPushNotification($userId,$message,$title,$data,$image = ''){
        $tokenrows =  $this->getUserTokens($userId);
        $i = 0;
        foreach($tokenrows as $token){
            if($i == 0 )$recipients = $token;
            else $recipients .= ','.$token;
            $i++;
        }
        $url = "https://fcm.googleapis.com/fcm/send";
        $data = array_merge(array(
                'title'=> $title, 
                'body' =>  $message,
                'sound'=>'Default',
                'image'=> $image,
                'icon'=>'notification_icon'
            ), $data);
        $arrayToSend = array(
            'to' => $recipients,
            'priority' => 'high', 
            'notification' => array(
                'title'=> $title, 
                'body' =>  $message,
                'sound'=>'Default',
                'image'=> $image,
                'icon'=>'notification_icon'
            ),
            'data' => $data 
        );
        $headers = array('Authorization:key=AAAAYMEnM8I:APA91bHPHwbavxzJxExd1sFk79BZRGnyPj9DvtzUiY8fddPPOuYHwoCAlLfbHmnpLolZkdr2W55YbtCywkYwmanGhz7kc4WTi3o3Ordqj1KskhNv5t7rW4N3Q6EmbjzVIOgclewXiLgW', 'Content-Type:application/json' );
        $json = json_encode($arrayToSend);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    public function sendSellerPushNotification($sellerid,$message,$title,$data,$image = ''){
        $tokenrows =  $this->getUserTokens($sellerid,'seller');
        $response = array();
        $data['page'] = $data['page'] == null && $data['type'] != null ? $data['type'] : $data['page'];
        foreach($tokenrows as $token){
            $recipients = $token['token'];
            $url = "https://fcm.googleapis.com/fcm/send";
            $data = array_merge(array(
                'title'=> $title, 
                'body' =>  $message,
                'sound'=>'Default',
                'image'=> $image,
                'icon'=>'notification_icon'
            ), $data);
            $arrayToSend = array(
                'to' => $recipients,
                'priority' => 'high', 
                'notification' => array(
                    'title'=> $title, 
                    'body' =>  $message,
                    'sound'=>'Default',
                    'image'=> $image,
                    'icon'=>'notification_icon',
                    'click_action'=>"FCM_PLUGIN_ACTIVITY"
                ),
                'data' => $data 
            );
            $headers = array('Authorization:key=AAAACtGgOmo:APA91bFT0kLPzmudlQwBtx2ROoVMC66bp3svIueJK3zvgpFEeXbcMN88hU3DP_ihBeV8PgvOI9uLyaQ9RufIKoSwCQqPAyXzcbpmzXwax-eeB0eKxwMClHPkb8L98hzOY6LFMup6FRu4', 'Content-Type:application/json' );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($arrayToSend));
            $result = curl_exec($ch);
            curl_close($ch);
            array_push($response,json_decode($result,true));
        }
        return json_encode($response);
    }

    public function do_curl($url,$params,$headers,$post = false)
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $params_string = '';
      if (is_array($params) && count($params)) {
        foreach($params as $key=>$value) {
          $params_string .= $key.'='.$value.'&'; 
        }
        rtrim($params_string, '&');
      }
      if($post){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POST, count($params));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
      }else{
        $url .= '?'.$params_string;
      }
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
      $result = curl_exec($ch);
    //   var_dump( $result);
      curl_close($ch);
      return $result;
    }
  }
?>
