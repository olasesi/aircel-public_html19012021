<?php
  /**
   * Controller for the push Nofication
   */
  class ControllerCustomPushNotification extends Controller {
    private $error = array();
    /**
     * Route to add list all push notification subscribers
     *
     * @param string $user_token
     * @return opencartview
     **/
    public function subscribers()
    {
        var_dump(134);
        exit;
      $this->load->language('custom/push_notification');
      $this->document->setTitle($this->language->get('heading_title'));

      if (isset($this->request->get['filter_audience'])) {
        $filter_audience = $this->request->get['filter_audience'];
        $data['filter_audience'] = $this->request->get['filter_audience'];
      } else {
        $filter_audience = '';
      }

      if (isset($this->request->get['filter_platform'])) {
        $filter_platform = $this->request->get['filter_platform'];
        $data['filter_platform'] = $this->request->get['filter_platform'];
      } else {
        $filter_platform = '';
      }

      if (isset($this->request->get['filter_status'])) {
        $filter_status = $this->request->get['filter_status'];
        $data['filter_status'] = $this->request->get['filter_status'];
      } else {
        $filter_status = '';
      }

      if (isset($this->request->get['filter_date_added'])) {
        $filter_date_added = date('Y-m-d',strtotime($this->request->get['filter_date_added']));
        $data['filter_date_added'] = $this->request->get['filter_date_added'];
      } else {
        $filter_date_added = '';
      }



      if (isset($this->request->get['sort'])) {
        $sort = $this->request->get['sort'];
      } else {
        $sort = 'id';
      }
  
      if (isset($this->request->get['order'])) {
        $order = $this->request->get['order'];
      } else {
        $order = 'ASC';
      }
  
      if (isset($this->request->get['page'])) {
        $page = $this->request->get['page'];
      } else {
        $page = 1;
      }
  
      $url = '';
  
      if (isset($this->request->get['sort'])) {
        $url .= '&sort=' . $this->request->get['sort'];
      }
  
      if (isset($this->request->get['order'])) {
        $url .= '&order=' . $this->request->get['order'];
      }
  
      if (isset($this->request->get['page'])) {
        $url .= '&page=' . $this->request->get['page'];
      }

      $data['breadcrumbs'] = array();

      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
      );

      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('heading_title'),
        'href' => $this->url->link('custom/push_notification/subscribers', 'user_token=' . $this->session->data['user_token'] . $url, true)
      );

      $filter_data = array(
        'filter_date_added'=>$filter_date_added,
        'filter_status'=>$filter_status,
        'filter_platform'=>$filter_platform,
        'filter_audience'=>$filter_audience,
        'sort'  => $sort,
        'order' => $order,
        'start' => ($page - 1) * $this->config->get('config_limit_admin'),
        'limit' => $this->config->get('config_limit_admin')
      );
      $this->load->model('custom/push_notification');
      $subscribers_total  = $this->model_custom_push_notification->getSubscribersTotal($filter_data);
      $data['subscribers'] = $this->model_custom_push_notification->getSubscribers($filter_data);
      $pagination = new Pagination();
      $pagination->total = $subscribers_total;
      $pagination->page = $page;
      $pagination->limit = $this->config->get('config_limit_admin');
      $pagination->url = $this->url->link('custom/push_notification/subscribers', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

      $data['pagination'] = $pagination->render();

      $data['results'] = sprintf($this->language->get('text_pagination'), ($subscribers_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($subscribers_total - $this->config->get('config_limit_admin'))) ? $subscribers_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $subscribers_total, ceil($subscribers_total / $this->config->get('config_limit_admin')));

      $data['user_token'] = $this->session->data['user_token'];
      $data['sort'] = $sort;
      $data['order'] = $order;

      $data['header'] = $this->load->controller('common/header');
      $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');
      $this->response->setOutput($this->load->view('custom/push_notification/subscribers', $data));
  
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
    public function schedules()
    {
        var_dump(134);
        exit;
      $this->load->language('custom/push_notification');
      $this->document->setTitle($this->language->get('heading_title'));

      if (isset($this->request->get['filter_audience'])) {
        $filter_audience = $this->request->get['filter_audience'];
        $data['filter_audience'] = $this->request->get['filter_audience'];
      } else {
        $filter_audience = '';
      }

      if (isset($this->request->get['filter_platform'])) {
        $filter_platform = $this->request->get['filter_platform'];
        $data['filter_platform'] = $this->request->get['filter_platform'];
      } else {
        $filter_platform = '';
      }

      if (isset($this->request->get['filter_status'])) {
        $filter_status = $this->request->get['filter_status'];
        $data['filter_date_added'] = $this->request->get['filter_status'];
      } else {
        $filter_status = '';
      }

      if (isset($this->request->get['filter_date_added'])) {
        $filter_date_added = date('Y-m-d',strtotime($this->request->get['filter_date_added']));
        $data['filter_date_added'] = $this->request->get['filter_date_added'];
      } else {
        $filter_date_added = '';
      }
      if (isset($this->request->get['filter_broadcast_date'])) {
        $filter_broadcast_date = date('Y-m-d',strtotime($this->request->get['filter_broadcast_date']));
        $data['filter_broadcast_date'] = $this->request->get['filter_broadcast_date'];
      } else {
        $filter_broadcast_date = '';
      }

      

      if (isset($this->request->get['sort'])) {
        $sort = $this->request->get['sort'];
      } else {
        $sort = 'name';
      }
  
      if (isset($this->request->get['order'])) {
        $order = $this->request->get['order'];
      } else {
        $order = 'DESC';
      }
  
      if (isset($this->request->get['page'])) {
        $page = $this->request->get['page'];
      } else {
        $page = 1;
      }
  
      $url = '';
  
      if (isset($this->request->get['sort'])) {
        $url .= '&sort=' . $this->request->get['sort'];
      }
  
      if (isset($this->request->get['order'])) {
        $url .= '&order=' . $this->request->get['order'];
      }
  
      if (isset($this->request->get['page'])) {
        $url .= '&page=' . $this->request->get['page'];
      }

      $data['breadcrumbs'] = array();

      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
      );

      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('heading_title'),
        'href' => $this->url->link('custom/push_notification/subscribers', 'user_token=' . $this->session->data['user_token'] . $url, true)
      );
      
      $filter_data = array(
        'filter_audience'  => $filter_audience,
        'filter_platform'  => $filter_platform,
        'filter_status'  => $filter_status,
        'filter_date_added'  => $filter_date_added,
        'filter_broadcast_date'  => $filter_broadcast_date,
        'sort'  => $sort,
        'order' => $order,
        'start' => ($page - 1) * $this->config->get('config_limit_admin'),
        'limit' => $this->config->get('config_limit_admin')
      );

      $this->load->model('custom/push_notification');
      $schedules = $this->model_custom_push_notification->getNotifications($filter_data);    
      foreach($schedules as $schedule){
        $data['schedules'][] = [
          'title'=>$schedule['title'],
          'schedule_date'=>date("Y-m-d",strtotime($schedule['date_added'])),
          'audience'=>$schedule['audience'],
          'platform'=>$schedule['platform'],
          'broadcast_date'=>date('Y-m-d',strtotime($schedule['broadcast_date'])),
          'broadcast_time'=>date('H:i:s',strtotime($schedule['broadcast_date'])),
          'status'=>$schedule['status'],
          'clicks'=>$schedule['clicks'],
          'delivery'=>$schedule['delivery'],
          'broadcast'=>$this->url->link('custom/push_notification/broadcast', 'user_token=' . $this->session->data['user_token'].'&schedule_id='.$schedule['schedule_id'].$url, true),
          'edit'=>$this->url->link('custom/push_notification/edit', 'user_token=' . $this->session->data['user_token'].'&schedule_id='.$schedule['schedule_id'].$url, true),
          'delete'=>$this->url->link('custom/push_notification/delete', 'user_token=' . $this->session->data['user_token'].'&schedule_id='.$schedule['schedule_id'].$url, true),
        ];
      }

      $schedule_total  = $this->model_custom_push_notification->getNotificationsTotal($filter_data);

      $pagination = new Pagination();
      $pagination->total = $schedule_total;
      $pagination->page = $page;
      $pagination->limit = $this->config->get('config_limit_admin');
      $pagination->url = $this->url->link('custom/push_notification/schedules', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

      $data['pagination'] = $pagination->render();

      $data['results'] = sprintf($this->language->get('text_pagination'), ($schedule_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($schedule_total - $this->config->get('config_limit_admin'))) ? $schedule_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $schedule_total, ceil($schedule_total / $this->config->get('config_limit_admin')));


      $data['user_token'] = $this->session->data['user_token'];
      $data['sort'] = $sort;
      $data['order'] = $order;

      $data['add'] = $this->url->link('custom/push_notification/add', 'user_token=' . $this->session->data['user_token'] . $url, true);

      $data['header'] = $this->load->controller('common/header');
      $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');
      $this->response->setOutput($this->load->view('custom/push_notification/schedule', $data));
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
    public function add()
    {
        var_dump(134);
        exit;
      $this->load->language('custom/push_notification');
      $this->load->model('custom/push_notification');
      $this->document->setTitle($this->language->get('heading_title'));

      if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
        
        $this->request->post['broadcast_date'] = $this->request->post['broadcast_now'] ?   $this->request->post['broadcast_date'] = date("Y-m-d H:i:s") : $this->request->post['broadcast_date'];
        
        $scheduleId = $this->model_custom_push_notification->addNotification($this->request->post);
        $this->session->data['success'] = $this->language->get('text_add_success');

        if($this->request->post['broadcast_now']){
          $this->broadcastNotification($scheduleId);
        }
        $url = '';
  
        if (isset($this->request->get['sort'])) {
          $url .= '&sort=' . $this->request->get['sort'];
        }
  
        if (isset($this->request->get['order'])) {
          $url .= '&order=' . $this->request->get['order'];
        }
  
        if (isset($this->request->get['page'])) {
          $url .= '&page=' . $this->request->get['page'];
        }
        
        $this->response->redirect($this->url->link('custom/push_notification/schedules', 'user_token=' . $this->session->data['user_token'] . $url, true));
      }

      $this->getForm();
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
    public function edit()
    {
         var_dump(134);
        exit;
      $this->load->language('custom/push_notification');
      $this->load->model('custom/push_notification');
      if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
        if($notification['status'] !=  'pending'){
          $scheduleId = $this->model_custom_push_notification->addNotification($this->request->post);
          $this->session->data['success'] = $this->language->get('text_add_success');
        }else{
          $this->model_custom_push_notification->updateNotification($this->request->get['schedule_id'],$this->request->post);
        }
  
        $this->session->data['success'] = $this->language->get('text_edit_success');
  
        $url = '';
  
        if (isset($this->request->get['sort'])) {
          $url .= '&sort=' . $this->request->get['sort'];
        }
  
        if (isset($this->request->get['order'])) {
          $url .= '&order=' . $this->request->get['order'];
        }
  
        if (isset($this->request->get['page'])) {
          $url .= '&page=' . $this->request->get['page'];
        }
    
        $this->response->redirect($this->url->link('custom/push_notification/schedules', 'user_token=' . $this->session->data['user_token'] . $url, true));
      }
      $this->getForm();
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
    protected function getForm()
    {
        var_dump(134);
        exit;
      $data['text_form'] = !isset($this->request->get['schedule_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

      if (isset($this->error['warning'])) {
        $data['error_warning'] = $this->error['warning'];
      } else {
        $data['error_warning'] = '';
      }
  
      if (isset($this->error['title'])) {
        $data['error_title'] = $this->error['title'];
      } else {
        $data['error_title'] = array();
      }

      if (isset($this->error['message'])) {
        $data['error_message'] = $this->error['message'];
      } else {
        $data['error_message'] = array();
      }
      if (isset($this->error['audience'])) {
        $data['error_audience'] = $this->error['audience'];
      } else {
        $data['error_audience'] = array();
      }

      if (isset($this->error['platform'])) {
        $data['error_platform'] = $this->error['platform'];
      } else {
        $data['error_platform'] = array();
      }
      
      if (isset($this->error['broadcast_date'])) {
        $data['error_broadcast_date'] = $this->error['broadcast_date'];
      } else {
        $data['error_broadcast_date'] = array();
      }
      
      if (isset($this->error['image'])) {
        $data['error_image'] = $this->error['image'];
      } else {
        $data['error_image'] = array();
      }

      if (isset($this->error['weblink'])) {
        $data['error_weblink'] = $this->error['weblink'];
      } else {
        $data['error_weblink'] = array();
      }

      if (isset($this->error['mobile_link_type'])) {
        $data['error_mobile_link_type'] = $this->error['mobile_link_type'];
      } else {
        $data['error_mobile_link_type'] = array();
      }
  
      if (isset($this->error['mobile_link_id'])) {
        $data['error_mobile_link_id'] = $this->error['mobile_link_id'];
      } else {
        $data['error_mobile_link_id'] = array();
      }

      $url = '';

      if (isset($this->request->get['sort'])) {
        $url .= '&sort=' . $this->request->get['sort'];
      }

      if (isset($this->request->get['order'])) {
        $url .= '&order=' . $this->request->get['order'];
      }

      if (isset($this->request->get['page'])) {
        $url .= '&page=' . $this->request->get['page'];
      }


      $data['breadcrumbs'] = array();

      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
      );

      $data['breadcrumbs'][] = array(
        'text' => $this->language->get('heading_title'),
        'href' => $this->url->link('custom/push_notification/schedules', 'user_token=' . $this->session->data['user_token'] . $url, true)
      );

      $data['cancel'] = $this->url->link('custom/push_notification/schedules', 'user_token=' . $this->session->data['user_token'] . $url, true);
      $data['broadcast'] = $this->url->link('custom/push_notification/broadcast', 'user_token=' . $this->session->data['user_token'] . $url, true);
      if (!isset($this->request->get['schedule_id'])) {
        $data['action'] = $this->url->link('custom/push_notification/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
      } else {
        $data['action'] = $this->url->link('custom/push_notification/edit', 'user_token=' . $this->session->data['user_token'] . '&schedule_id=' . $this->request->get['schedule_id'] . $url, true);
      }

      if (isset($this->request->get['schedule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
        $schedule_info = $this->model_custom_push_notification->getNotification($this->request->get['schedule_id']);
      }
  
      if (isset($this->request->post['title'])) {
        $data['title'] = $this->request->post['title'];
      } elseif (!empty($schedule_info)) {
        $data['title'] = $schedule_info['title'];
      } else {
        $data['title'] = '';
      }

      if (isset($this->request->post['message'])) {
        $data['message'] = $this->request->post['message'];
      } elseif (!empty($schedule_info)) {
        $data['message'] = $schedule_info['message'];
      } else {
        $data['message'] = '';
      }

      if (isset($this->request->post['audience'])) {
        $data['audience'] = $this->request->post['audience'];
      } elseif (!empty($schedule_info)) {
        $data['audience'] = $schedule_info['audience'];
      } else {
        $data['audience'] = '';
      }

      if (isset($this->request->post['platform'])) {
        $data['platform'] = $this->request->post['platform'];
      } elseif (!empty($schedule_info)) {
        $data['platform'] = $schedule_info['platform'];
      } else {
        $data['platform'] = '';
      }

      if (isset($this->request->post['broadcast_date'])) {
        $data['broadcast_date'] = $this->request->post['broadcast_date'];
      } elseif (!empty($schedule_info)) {
        $data['broadcast_date'] = $schedule_info['broadcast_date'];
      } else {
        $data['broadcast_date'] = '';
      }

      if (isset($this->request->post['web_link'])) {
        $data['web_link'] = $this->request->post['web_link'];
      } elseif (!empty($schedule_info)) {
        $data['web_link'] = $schedule_info['web_link'];
      } else {
        $data['web_link'] = '';
      }

      if (isset($this->request->post['mobile_link'])) {
        $data['mobile_link'] = $this->request->post['mobile_link'];
      } elseif (!empty($schedule_info)) {
        $data['mobile_link'] = $schedule_info['mobile_link'];
      } else {
        $data['mobile_link'] = '';
      }

      if (isset($this->request->post['mobile_link_type'])) {
        $data['mobile_link_type'] = $this->request->post['mobile_link_type'];
      } elseif (!empty($schedule_info)) {
        $data['mobile_link_type'] = $schedule_info['mobile_link_type'];
      } else {
        $data['mobile_link_type'] = '';
      }
  

      $this->load->model('tool/image');
      if (isset( $this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
        $image = $this->request->post['image'];
        $thumb = $this->request->post['image'];
        $data['image'] = $this->model_tool_image->resize(DIR_IMAGE . $this->request->post['image'], 100, 100);
      }  elseif (!empty($schedule_info) && is_file(DIR_IMAGE .  $schedule_info['image_url'])){
        $data['image'] = $schedule_info['image_url'];
        $data['thumb'] = $this->model_tool_image->resize($schedule_info['image_url'], 100, 100);
      }else {
        $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        $data['image'] = '';
      }

      $data['header'] = $this->load->controller('common/header');
		  $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');
      
      $this->response->setOutput($this->load->view('custom/push_notification/form', $data));
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
    public function validateForm()
    {
        var_dump(134);
        exit;
      if (!$this->user->hasPermission('modify', 'custom/push_notification')) {
        $this->error['warning'] = $this->language->get('error_permission');
      }

      if ((utf8_strlen($this->request->post['title']) < 1) || (utf8_strlen($this->request->post['title']) > 256)) {
        $this->error['title'] = $this->language->get('error_title');
      }
  
      if ((utf8_strlen($this->request->post['message']) < 1) || (utf8_strlen($this->request->post['message']) > 256)) {
        $this->error['message'] = $this->language->get('error_message');
      }

      if ((utf8_strlen($this->request->post['audience']) < 3) || (utf8_strlen($this->request->post['audience']) > 15) || !in_array($this->request->post['audience'],['all','buyers','sellers'])) {
        $this->error['audience'] = $this->language->get('error_audience');
      }

      if ((utf8_strlen($this->request->post['platform']) < 3) || (utf8_strlen($this->request->post['platform']) > 15) || !in_array($this->request->post['platform'],['all','browser','mobile'])) {
        $this->error['platform'] = $this->language->get('error_platform');
      }
      if ((utf8_strlen($this->request->post['image']) < 3) || (utf8_strlen($this->request->post['image']) > 256)) {
        $this->error['image'] = $this->language->get('error_image');
      }

      if (!$this->request->post['broadcast_now'] && ((utf8_strlen($this->request->post['broadcast_date']) < 10) || (utf8_strlen($this->request->post['broadcast_date']) > 20))) {
        $this->error['broadcast_date'] = $this->language->get('error_broadcast_date');
      }

      if (in_array($this->request->post['platform'],['all','browser']) && ((utf8_strlen($this->request->post['web_link']) < 10) || (utf8_strlen($this->request->post['web_link']) > 512))) {
        $this->error['web_link'] = $this->language->get('error_web_link');
      }
      if ( !in_array($this->request->post['platform'],['all','mobile']) && (!in_array($this->request->post['mobile_link_type'],['product','category']) || (utf8_strlen($this->request->post['mobile_link_type']) < 3) || (utf8_strlen($this->request->post['mobile_link_type']) > 10))) {
        $this->error['mobile_link_type'] = $this->language->get('error_mobile_link_type');
      }
      if (in_array($this->request->post['platform'],['all','mobile']) && ((utf8_strlen($this->request->post['mobile_link']) < 1) || (utf8_strlen($this->request->post['mobile_link']) > 5))) {
        $this->error['mobile_link'] = $this->language->get('error_mobile_link');
      }

      return !$this->error;
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
    public function broadcast()
    {
        var_dump(134);
        exit;
      $this->load->language('custom/push_notification');
      $this->load->model('custom/push_notification');
      $scheduleId = $this->request->get['schedule_id'];
      
      $this->broadcastNotification($scheduleId);

      $this->session->data['success'] = $this->language->get('text_broadcast_success');
      $this->response->redirect($this->url->link('custom/push_notification/schedules', 'user_token=' . $this->session->data['user_token'] . $url, true));
    } 


    private  function broadcastNotification($scheduleId){
        var_dump($scheduleId);
        exit;
      $notification = $this->model_custom_push_notification->getNotification($scheduleId);
      if($notification){
        if($notification['status'] !=  'pending'){
          $notification['image'] = $notification['image_url'];
          $scheduleId = $this->model_custom_push_notification->addNotification($notification);
          $this->session->data['success'] = $this->language->get('text_add_success');
        }
        $filter = [
          'audience'=>$notification['audience'],
          'platform'=> $notification['platform']
        ];
        $subscribers = $this->model_custom_push_notification->getAllSubscribers($filter);
        var_dump($subscribers);
        exit;
        $browserSubscribers = [];
        $mobileSubscribers = [];
        $this->load->model('tool/image');
        foreach ($subscribers as $subscriber) {
          if($subscriber['platform'] == 'mobile'){
            $mobileSubscribers[] = $subscriber['token'];
            $data = [
              'page'=>$notification['mobile_link_type'],
              'id'=>$notification['mobile_link'],
              'image'=>$this->model_tool_image->resize($notification['image_url'],400,300)
            ];
            $res = $this->model_custom_push_notification->sendMobilePushNotification($subscriber['token'],$notification['message'],$notification['title'],$data,$this->model_tool_image->resize($notification['image_url'],400,300));
          }else{
            $res = $this->model_custom_push_notification->sendWebPush($scheduleId,$subscriber['id'],html_entity_decode($subscriber['token']),$notification['message'],$notification['title'],$this->model_tool_image->resize($notification['image_url'],400,300),$notification['web_link']);
          }
        }
      }
      $this->model_custom_push_notification->updatePushNotificationStatus($scheduleId,'published');
      $this->session->data['success'] = $this->language->get('text_broadcast_success');
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
    public function reschedule()
    {
      # code...
    }
  }
?>