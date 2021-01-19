<?php
// catalog/controller/api/manufacturer.php
class ControllerApiManufacturer extends Controller {
    
  public function index() {
    $this->load->language('api/manufacturer');
    $json = array();
 
    if (!isset($this->session->data['api_id'])) {
      $json['error']['warning'] = $this->language->get('error_permission');
    } else {
        // load model
        $this->load->model('catalog/manufacturer');
        
        $manufacturers = $this->model_catalog_manufacturer->getManufacturers();
        $json['success']['manufacturers'] = $manufacturers;
        
        $this->response->addHeader('Content-Type: application/json');
		return $this->response->setOutput(json_encode($json));
    }
     
    if (isset($this->request->server['HTTP_ORIGIN'])) {
      $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
      $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
      $this->response->addHeader('Access-Control-Max-Age: 1000');
      $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }
 
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
        // echo "index";

  }
 
  // Gets manufacturer by id
  public function manufacturer_id() {
      
    $this->load->language('api/manufacturer');
    $json = array();
    
    if (!isset($this->session->data['api_id'])) {
      $json['error']['warning'] = $this->language->get('error_permission');
    } else  {
        if (isset($this->request->get['man_id'])) {
			$man_id = $this->request->get['man_id'];
		} else {
			$man_id = 0;
			$json['status'] = 'error';
			$json['message'] = 'NO man_id provided';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		// load model
        $this->load->model('catalog/manufacturer');
        
        $manufacturer = $this->model_catalog_manufacturer->getManufacturer($man_id);
        $json['success']['manufacturer'] = $manufacturer;
    }
    
      
		
    if (isset($this->request->server['HTTP_ORIGIN'])) {
      $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
      $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
      $this->response->addHeader('Access-Control-Max-Age: 1000');
      $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }
 
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
    // echo "start";
  }
   
}