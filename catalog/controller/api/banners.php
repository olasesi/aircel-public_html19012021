<?php
class ControllerApiBanners extends Controller {
    
    public function index() {
        $this->load->language('api/category');
        $json = array();
        
        if (!isset($this->session->data['api_id'])) {
            $json['status'] = FALSE;
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            // load model
            $this->load->model('custom/banners');
            $this->load->model('extension/catalog/seo_url'); //ModelExtensionCatalogSeoUrl
            // $keyword = 'garrett-leight-millwood-sunglasses';
            //  $seo = $this->model_extension_catalog_seo_url->getSeoUrlsByKeyword($keyword);
             $banners = $this->model_custom_banners->getMainBanners();
             $json['status'] = TRUE;
             $json['data'] = array();
             $homebanners = array();
             $homebannersurl = array();
             foreach ($banners['module_data']['slides'] as $result) {
                if($result['status'] == 1) {
                    array_push($homebanners,$result['image']["1"]);
                    // array_push($homebannersurl,$result['link']['menu_item']);
                }
             }
            
            
             $json['data'] = $homebanners;
            //  $json['data'] ['$homebannersurl'] = $homebannersurl;
            //  $json['data'] ['$banners'] = $seo;
            //  $json['data'] ['$banners'] = $banners;
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
          $this->response->addHeader('Access-Control-Allow-Origin: ' . '*');
          $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          $this->response->addHeader('Access-Control-Max-Age: 1000');
          $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
 
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        
    }
    
}