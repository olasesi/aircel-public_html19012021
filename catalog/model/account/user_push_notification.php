<?php
  class ModelAccountUserPushNotification extends Model {
     public function addNotificationSubcriber($userId,$token,$accounttype = 'customer'){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_push_notice WHERE user_id = '" . (int)$userId ."' AND user_type = '".$accounttype."' AND user_token = '".$token."'");
		if(count($query->row) > 0) return ["status"=>"error", "message"=>"token already exists for this user "];
        $this->db->query("INSERT INTO " . DB_PREFIX . "user_push_notice SET user_id = '".(int)$userId."', user_token = '".$token. "', user_type = '".$accounttype."'");
        $insertId = $this->db->getLastId();
        return $insertId;
     }
     
     public function getUserTokens($userId,$accounttype = 'customer'){
        $sql = "SELECT * FROM " . DB_PREFIX . "user_push_notice WHERE user_id = '" . (int)$userId ."' AND user_type = '".$accounttype."'";
        $query = $this->db->query($sql);
		return $query->rows;
     }
     
      public function removeNotificationSubscriber($token,$accounttype = 'customer'){
        if(strlen($token) > 0){
          $query = $this->db->query("DELETE FROM " . DB_PREFIX . "user_push_notice WHERE  user_token = '" . $token  ."' AND user_type = '".$accounttype."'");
        }
        return;
      }
      
      public function unsubscribeUserNotifications($user,$accounttype = 'customer'){
           $query = $this->db->query("DELETE FROM " . DB_PREFIX . "user_push_notice WHERE user_id = '" . (int)$userId ."' AND user_type = '".$accounttype."'");
           return;
      }
      
      public function updateNotificationToken($oldtoken,$newtoken,$accounttype = 'customer'){
          $query = $this->db->query("UPDATE  " . DB_PREFIX . "user_push_notice SET user_token = '".$newtoken."' WHERE user_token = '" . $oldtoken ."' AND user_type = '".$accounttype."'");
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
              $recipients = $token['user_token'];
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
  }
?>
