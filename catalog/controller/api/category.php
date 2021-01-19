 <?php
// header('Access-Control-Allow-Origin : *');
// catalog/controller/api/category.php
class ControllerApiCategory extends Controller {
    
  // Gets all categories with  parent id = 0
  public function index() {
      
    $this->load->language('api/category');
    $json = array();
 
   
        // load model
        $this->load->model('catalog/category');
        $cat_id =  $this->request->post['categoryId'] || 0;
        $categories = $this->model_catalog_category->getCategories($cat_id);
        for($i = 0;$i < count($categories); $i++){
            $categories[$i]['name'] = htmlspecialchars_decode($categories[$i]['name']);
            $category = $categories[$i];
            $child_sub = $this->model_catalog_category->getCategories($category['category_id']);
            for($j = 0; $j < count($child_sub); $j++ ){
                $grand_sub = $child_sub[$j];
                $child_sub[$j]['sub'] = $this->model_catalog_category->getCategories($grand_sub['category_id']);
                $child_sub[$j]['name'] = html_entity_decode($child_sub[$j]['name'], ENT_QUOTES);
                // for($j = 0; $k < count($child_sub[$j]['sub']); $k++ ){
                //     $child_sub[$j]['sub'][$k]['name'] = html_entity_decode($child_sub[$j]['sub'][$k]['name'], ENT_QUOTES);
                // }
            }
            $categories[$i]['sub'] = $child_sub;
            

        }
        $json['status'] = TRUE;
        $json['data'] = $categories;
   
     
    if (isset($this->request->server['HTTP_ORIGIN'])) {
      $this->response->addHeader('Access-Control-Allow-Origin: ' . '*');
      $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
      $this->response->addHeader('Access-Control-Max-Age: 1000');
      $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }
 
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));

  }
  
  // Gets category by id
  public function category_id() {
      
    $this->load->language('api/category');
    $json = array();
    
    if (!isset($this->session->data['api_id'])) {
      $json['error']['warning'] = $this->language->get('error_permission');
    } else  {
        if (isset($this->request->post['cat_id'])) {
			$cat_id = $this->request->post['cat_id'];
		} else {
			$cat_id = 0;
			$json['status'] = 'error';
			$json['message'] = 'NO cat_id provided';
			$this->response->addHeader('Content-Type: application/json');
			return $this->response->setOutput(json_encode($json));
		}
		
		// load model
        $this->load->model('catalog/category');
        
        $categories = $this->model_catalog_category->getCategory($cat_id);
        if (true) {
            // $categories['name'] = htmlspecialchars_decode($categories['name'], ENT_QUOTES);
            $categories['name'] = html_entity_decode($categories['name'], ENT_QUOTES);
        }
        
        $json['status'] = TRUE;
        $json['data'] = $categories;
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
  
  public function categoryProducts() {
     $this->load->language('api/category');
     $json = array();
 
    if (!isset($this->session->data['api_id'])) {
      $json['status'] = FALSE;
      $json['error']['warning'] = $this->language->get('error_permission');
    } else {
      // load model
      $this->load->model('catalog/product');
      $filters = $_POST; // all post fields as array
      $categoryProducts = $this->model_catalog_product->getProducts($filters);
      $products = array();
      $productIds = array_keys($categoryProducts);
      for( $i = 0;$i < count($productIds); $i++){
          array_push($products,$categoryProducts[$productIds[$i]]);
      }
      $json['status'] = TRUE;
      $json['data'] = $products;
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
