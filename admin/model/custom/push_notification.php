<?php
    require DIR_APPLICATION . 'model/custom/vendor/autoload.php';
    use Minishlink\WebPush\WebPush;
    use Minishlink\WebPush\Subscription;
  class ModelCustomPushNotification extends Model {

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function addNotification($data = [])
    {
        $sql = "INSERT INTO ".DB_PREFIX."notification_schedules SET title= '".$this->db->escape($data['title'])."', message = '".$this->db->escape($data['message'])."', image_url= '".$this->db->escape($data['image'])."', audience = '".$this->db->escape($data['audience'])."', platform = '".$this->db->escape($data['platform'])."', web_link = '".$this->db->escape($data['web_link'])."', mobile_link_type = '".$this->db->escape($data['mobile_link_type'])."', mobile_link = '".$this->db->escape($data['mobile_link'])."', broadcast_date = '".$this->db->escape($data['broadcast_date'])."' ";
        $this->db->query($sql);
        return $this->db->getLastId();
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
    public function updateNotification($scheduleId,$data = [])
    {
        $sql = "UPDATE ".DB_PREFIX."notification_schedules SET title= '".$this->db->escape($data['title'])."', message = '".$this->db->escape($data['message'])."', image_url= '".$this->db->escape($data['image'])."', audience = '".$this->db->escape($data['audience'])."', platform = '".$this->db->escape($data['platform'])."', web_link = '".$this->db->escape($data['web_link'])."', mobile_link_type = '".$this->db->escape($data['mobile_link_type'])."', mobile_link = '".$this->db->escape($data['mobile_link'])."', broadcast_date = '".$this->db->escape($data['broadcast_date'])."' WHERE schedule_id = '".$scheduleId."'" ;

        return $this->db->query($sql);
    }


    /**
     * Get Subscribers using filter
     *
     * @param array $filter Array of filter sort, order, limit,audience,status, date_created,$date_modified
     * @return bool or @return Array subscribers
     **/
    public function getSubscribers($data = array())
    {
        $sql = 'SELECT *,(SELECT CONCAT(firstname," ",lastname) FROM '.DB_PREFIX.'customer WHERE customer_id = p.customer_id ) AS subscriber, (SELECT email FROM '.DB_PREFIX.'customer WHERE customer_id = p.customer_id ) AS email, IF(usertype = "seller",(SELECT store_name FROM '.DB_PREFIX.'purpletree_vendor_stores WHERE seller_id = p.customer_id LIMIT 1 ), "" ) AS store FROM `'.DB_PREFIX.'push_subscribers` p  WHERE  id != 0';


        if (!empty($data['filter_audience']) && $data['filter_audience'] !== 'all') {
	    	$sql .= " AND usertype = '" . $data['filter_audience'] . "'";
        }
        
        if (!empty($data['filter_platform'])  && $data['filter_platform'] !== 'all') {
	    	$sql .= " AND platform = '" . $data['filter_platform'] . "'";
        }
        
        if (!empty($data['filter_status']) && $data['filter_status'] !== 'all') {
			$sql .= " AND status = '" . $data['filter_status'] . "'";
        }
        
        if (!empty($data['filter_date_added'])) {
			$sql .= " AND date_added = '%" . $data['filter_date_added'] . "%'";
        }
        
        $sort_data = array(
            'id',
            'customer',
			'email',
			'usertype',
			'date_added',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY id";
		}

		if (isset($data['order'])) {
			$sql .= ' '.$data['order'];
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
        }
        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        $query = $this->db->query($sql);
        return $query->rows;
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
    public function getAllSubscribers($data = [])
    {
        $sql = 'SELECT *,(SELECT CONCAT(firstname," ",lastname) FROM '.DB_PREFIX.'customer WHERE customer_id = p.customer_id ) AS subscriber, (SELECT email FROM '.DB_PREFIX.'customer WHERE customer_id = p.customer_id ) AS email, IF(usertype = "seller",(SELECT store_name FROM '.DB_PREFIX.'purpletree_vendor_stores WHERE seller_id = p.customer_id LIMIT 1 ), "" ) AS store FROM `'.DB_PREFIX.'push_subscribers` p WHERE status = "subscribed"';

        if(isset($data['audience']) && $data['audience'] !== 'all'){
            $sql .= " AND p.usertype ='".$data['audience']."'";
        }
        
        if(isset($data['platform'])  && $data['platform'] !== 'all'){
            $sql .= " AND p.platform ='".$data['platform']."'";
        }         
        
        $query = $this->db->query($sql);
        return $query->rows;
    }


    public function getSubscribersTotal($data = [])
    {   
        $sql = 'SELECT COUNT(*) AS total FROM `'.DB_PREFIX.'push_subscribers` WHERE  id != 0 ';
        if (!empty($data['filter_audience']) && $data['filter_audience'] !== 'all') {
	    	$sql .= " AND usertype = '" . $data['filter_audience'] . "'";
        }
        
        if (!empty($data['filter_platform'])  && $data['filter_platform'] !== 'all') {
	    	$sql .= " AND platform = '" . $data['filter_platform'] . "'";
        }
        
        if (!empty($data['filter_status']) && $data['filter_status'] !== 'all') {
			$sql .= " AND status = '" . $data['filter_status'] . "'";
        }
        
        if (!empty($data['filter_date_added'])) {
			$sql .= " AND date_added = '%" . $data['filter_date_added'] . "%'";
        }
        $query = $this->db->query($sql);
        return $query->row['total'];
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
    public function getNotifications($data = array())
    {
        $sql = 'SELECT * FROM `'.DB_PREFIX.'notification_schedules` n WHERE n.schedule_id != 0';

        if (!empty($data['filter_audience'])  && $data['filter_audience'] != 'all' ) {
	    	$sql .= " AND audience = '" . $data['filter_audience'] . "'";
        }

        if (!empty($data['filter_platform'])  && $data['filter_platform'] != 'all') {
	    	$sql .= " AND platform = '" . $data['filter_platform'] . "'";
        }

        if (!empty($data['filter_broadcast_date'])) {
	    	$sql .= " AND broadcast_date LIKE '%" . $data['filter_broadcast_date'] . "%'";
        }
        
        if (!empty($data['filter_status']) && $data['filter_status'] != 'all') {
			$sql .= " AND status = '" . $data['filter_status'] . "'";
        }
        
        if (!empty($data['filter_date_added'])) {
			$sql .= " AND date_added LIKE '%" . $data['filter_date_added'] . "%'";
        }
        
        $sort_data = array(
            'id',
            'delivery',
			'clicks',
			'status',
			'audience',
			'platform',
			'broadcast_date',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY schedule_id";
		}

		if (isset($data['order']) ) {
			$sql .= ' '.$data['order'];
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
        }
        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        
        $query = $this->db->query($sql);
        return $query->rows;
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
    public function getNotification($scheduleId)
    {
        $sql = "SELECT * FROM `".DB_PREFIX."notification_schedules` n WHERE schedule_id = '".$scheduleId."'";
        $query = $this->db->query($sql);
        return $query->row;
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
    public function getNotificationsTotal($filter = [])
    {
        $sql = 'SELECT COUNT(*) AS total FROM `'.DB_PREFIX.'notification_schedules` WHERE schedule_id != 0 ';
        
        if (!empty($filter['filter_platform'])  && $filter['filter_platform'] != 'all' ) {
	    	$sql .= " AND platform = '" . $filter['filter_platform'] . "'";
        }
        
        
        if (!empty($filter['filter_audience'])  && $filter['filter_audience'] != 'all' ) {
	    	$sql .= " AND audience = '" . $filter['filter_audience'] . "'";
        }
        
        if (!empty($filter['filter_status'])  && $filter['filter_status'] != 'all' ) {
			$sql .= " AND status = '" . $filter['filter_status'] . "'";
        }
        
        if (!empty($filter['filter_date_added'])) {
			$sql .= " AND date_added LIKE '%" . $filter['filter_date_added'] . "%'";
        }

        if (!empty($filter['filter_broadcast_date'])) {
			$sql .= " AND broadcast_date = '%" . $filter['filter_broadcast_date'] . "%'";
        }

        $query = $this->db->query($sql);
        return $query->row['total'];
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
    public function removeNotificationSubscriber($token,$accounttype = 'customer')
    {
      if(strlen($token) > 0){
        $query = $this->db->query("DELETE FROM " . DB_PREFIX . "user_push_notice WHERE  user_token = '" . $token  ."' AND user_type = '".$accounttype."'");
      }
      return;
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
    public function unsubscribeUserNotifications($user,$accounttype = 'customer')
    {
        $query = $this->db->query("UPDATE" . DB_PREFIX . "user_push_notice  SET status = 'unsubscribed' WHERE user_id = '" . (int)$userId ."' AND user_type = '".$this->db->escape($accounttype)."'");
        return;
    }

    public function updatePushNotificationStatus($scheduleId,$status)
    {
        $sql = "UPDATE " . DB_PREFIX . "notification_schedules SET status = '".$this->db->escape($status)."' WHERE schedule_id = '" . (int)$scheduleId ."'" ;
        $query = $this->db->query($sql);
        return;
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
    public function sendCustomerPushNotification($userId,$message,$title,$data,$image = '')
    {
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
        
        var_dump($result);
        exit;
        curl_close($ch);
        return $result;
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
    public function sendSellerPushNotification($sellerid,$message,$title,$data,$image = '')
    {
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

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Array $recipients Array of tokens to be sent
     * @param string $title Title of the notification
     * @param string $message Message to be sent with the token
     * @param string $image Image to be attached with the notification
     * @param Array $data Other data
     * 
     * **/
    public function sendMobilePushNotification($recipients,$message,$title,$data,$image = '')
    {
        $response = array();

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
                
        var_dump($result);
        exit;
        return json_encode($response);
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
    public function sendWebPush($notificationId,$subscriptionId,$subscriptionToken,$message,$title,$image,$url)
    {
        $this->load->model('setting/setting');
        $customPush = $this->model_setting_setting->getSetting('custom_push');
        // here I'll get the subscription endpoint in the POST parameters
        // but in reality, you'll get this information in your database
        // because you already stored it (cf. push_subscription.php)
        $subscription = Subscription::create(json_decode($subscriptionToken, true));

        $auth = array(
            'VAPID' => array(
                'subject' =>$url,
                'publicKey' => $customPush['web_push_public_key'], // don't forget that your public key also lives in app.js
                'privateKey' => $customPush['web_push_private_key'], // in the real world, this would be in a secret file
            ),
        );

        $webPush = new WebPush($auth);

        $report = $webPush->sendOneNotification( $subscription,'{"id":"'.$notificationId.'","subscriptionid":"'.$subscriptionId.'", "message":"'.$message.'", "title": "'.$title.'","image":"'.$image.'","url":"'.$url.'"}');

        // handle eventual errors here, and remove the subscription from your server if it is expired
        $endpoint = $report->getRequest()->getUri()->__toString();

        if ($report->isSuccess()) {
            echo "[v] Message sent successfully for subscription {$endpoint}.";
        } else {
            echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
        }
    }



    
  }
?>
