<?php
class ControllerApiHomepage extends Controller {
    public function index() {
        $this->load->language('api/category');
        $json = array();
        
        if (!$this->request->server['REQUEST_METHOD'] == 'POST') {
            $json['status'] = FALSE;
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            $json['status'] = TRUE;
            // load model
            $this->load->model('journal2/product');
            $this->load->model('journal2/module');
            $this->load->model('catalog/product');
            $this->load->model('custom/banners');
            $this->load->model('catalog/category');
            $this->load->model('localisation/zone');
            $this->load->model('setting/extension');
            $zone_info           = $this->model_localisation_zone->getZonesByCountryId(156);
            $parentcategories    = $categories = $this->model_catalog_category->getCategories(0);
            $banners             = $this->model_custom_banners->getMainBanners();
            $randomIds           = $this->model_journal2_product->getRandomProducts(6);
            $randomDealsIds      = $this->model_journal2_product->getRandomProducts(6);
            $mostViewedProducts  = $this->model_journal2_product->getMostViewed(8);
            $bestSellersInfo     = $this->model_journal2_product->getBestsellers(20, $filter_category = false);
            $latestProductsInfo  = $this->model_journal2_product->getLatest(20, $filter_category = false);
            $specialProductsInfo = $this->model_journal2_product->getSpecials(20, $filter_category = false);
            $availableShipping   = $this->model_setting_extension->getExtensions('shipping');
            $dealsproducts       = array();
            $featuredproducts    = array();
            $homebanners         = array();
            $bestSellersProducts = array();
            $latestProducts      = array();
            $discountProducts    = array();
            $available_shipping  = array();
           
            $specialCustomModuleProducts    = array();
            $specialProductsCustomModuleIds = array();
            $specialProductsCustomModule    = $this->model_journal2_module->getModule(250,array("limit"=>6,"sort"=>"rand()"));
            $specialdealtitle               = $specialProductsCustomModule['module_data']['product_sections'][0]['section_title']["value"][1];
            $specialdealtitle               = isset($specialdealtitle) ? $specialdealtitle : "Special Deals";
            $SpecialCountdownProductsId     = $this->model_journal2_product->getSpecialCountdownProducts(array("limit"=>1));
            $SpecialCountdownTime           = $this->model_journal2_product->getSpecialCountdown($SpecialCountdownProductsId[0]['product_id']);
        

            for($i = 0; $i < sizeof($randomDealsIds); $i++){
                $product = $this->model_catalog_product->getProduct($randomIds[$i]['product_id']);
                $productfeatured = $this->model_catalog_product->getProduct($randomDealsIds[$i]['product_id']);
                if($product['quantity'] > 0 ){
                    if(count($dealsproducts) < 4){
                     array_push($dealsproducts,$product);
                    }
                }
                if($productfeatured['quantity'] > 0 ){
                    array_push($featuredproducts,$productfeatured);
                }
            }

            foreach ($banners['module_data']['slides'] as $result) {
                if($result['status'] == 1) {
                    array_push($homebanners,$result['image']["1"]);
                }
            }
            
            foreach ($specialProductsCustomModule['module_data']['product_sections'][0]['products'] as $result) {
                array_push($specialProductsCustomModuleIds,$result['data']['id']);
            }
            $specialProductsCustomModuleIds = $this->shuffleItems($specialProductsCustomModuleIds);
            foreach ($specialProductsCustomModuleIds as $item) {
                $tmpProduct = $this->model_catalog_product->getProduct($item);
                if ($tmpProduct !== false) {
                  if(count($specialCustomModuleProducts) == 4){ break; }
                  else if($tmpProduct['quantity'] > 0) array_push($specialCustomModuleProducts,$tmpProduct);
                }
            }
            
             
            $bestSellersIds = array_keys($bestSellersInfo);
            $bestSellersIds = $this->shuffleItems($bestSellersIds);
            for( $i = 0;$i < count($bestSellersIds); $i++){
                if (count($bestSellersProducts) == 4)
                {
                  break;
                }else if($bestSellersInfo[$bestSellersIds[$i]]['quantity'] > 0){
                 array_push($bestSellersProducts,$bestSellersInfo[$bestSellersIds[$i]]);
                }
            }
            
            $latestProductsIds = array_keys($latestProductsInfo);
            $latestProductsIds = $this->shuffleItems($latestProductsIds);
            for( $i = 0;$i < count($latestProductsIds); $i++){
                if(count($latestProducts) == 4) break;
                else if($latestProductsInfo[$latestProductsIds[$i]]['quantity'] > 0){
                    array_push($latestProducts,$latestProductsInfo[$latestProductsIds[$i]]);
                }
            }
            
            $specialProductsIds = array_keys($specialProductsInfo);
            $specialProductsIds = $this->shuffleItems($specialProductsIds);
            for( $i = 0;$i < count($specialProductsIds); $i++){
                if(count($discountProducts) == 4) break;
                else if($specialProductsInfo[$specialProductsIds[$i]]['quantity'] > 0 && !in_array($specialProductsInfo[$specialProductsIds[$i]],$discountProducts)){
                   array_push($discountProducts,$specialProductsInfo[$specialProductsIds[$i]]);
                }
                
            }
            
            $json['data'] ['states']     = $zone_info;
            $json['data'] ['banners']    = $homebanners    ;
            $json['data'] ['categories'] = array(
                array("name" => "Automobile", "category_id"=>"653", "icon"=> "car"),
                array("name" => "Computers", "category_id"=>"254" , "icon"=> "desktop"),
                array("name" => "Electronics", "category_id"=>"349", "icon"=> "plug"),
                array("name" => "Fashion", "category_id"=>"344" , "icon"=> "tshirt"),
                array("name" => "Phone & Tablets", "category_id"=>"367", "icon"=> "mobile-alt"),
                array("name" => "Health & Beauty", "category_id"=>"842", "icon"=> "heartbeat"),
                array("name" => "Home & Office", "category_id"=>"457", "icon"=> "home"),
                array("name" => "Kiddies World", "category_id"=>"425", "icon"=> "child")
            );
           
            $json['data'] ['sections'] = array(
                        array('name'=>$specialdealtitle,'products'=>$specialCustomModuleProducts,'time' => $SpecialCountdownTime, 'module_id'=> '007'),
                        array('name'=>'Deals of the week','products'=>$dealsproducts, 'module_id'=> '005'),
                        array('name'=>'Latest Arrivals','products'=>$latestProducts, 'module_id'=> '003'),
                        array('name'=>'Bestsellers','products'=>$bestSellersProducts, 'module_id'=> '002'),
                        array('name'=>'Discount Deals','products'=>$discountProducts, 'module_id'=> '006'),
                        array('name'=>'Most Viewed Products','products'=>$mostViewedProducts, 'module_id'=> '004'),
                        array('name'=>'Featured Products','products'=>$featuredproducts, 'module_id'=> '001'),
            );
 	
			foreach ($availableShipping as $result) {
			    if ($this->config->get('shipping_' . $result['code'] . '_status')) {
			       array_push($available_shipping,$result);
			    }
			}
			
			/*checks for curent  enabled shipping methods*/
            $json['data'] ['shippingoptions']['pickup']   = is_numeric(array_search("pickup", array_column($available_shipping, 'code')));
            $json['data'] ['shippingoptions']['deliver']  = is_numeric(array_search("weight", array_column($available_shipping, 'code')));
            $json['data'] ['minpayondeliver']             = 50000;

         
            $this->response->addHeader('Content-Type: application/json');
    		return $this->response->setOutput(json_encode($json));
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
    
    public function sections()
    {
        $this->load->model('journal2/product');
        $this->load->model('journal2/module');
        $this->load->model('catalog/product');
        
            
        $randomIds                  = $this->model_journal2_product->getRandomProducts(6);
        $randomDealsIds             = $this->model_journal2_product->getRandomProducts(6);
        $mostViewedProducts         = $this->model_journal2_product->getMostViewed(6);
        $bestSellersInfo            = $this->model_journal2_product->getBestsellers(20, $filter_category = false);
        $latestProductsInfo         = $this->model_journal2_product->getLatest(4, $filter_category = false);
        $specialProductsInfo        = $this->model_journal2_product->getSpecials(6, $filter_category = false);
        $SpecialCountdownProductsId = $this->model_journal2_product->getSpecialCountdownProducts(array("limit"=>1));
        $SpecialCountdownTime       = $this->model_journal2_product->getSpecialCountdown($SpecialCountdownProductsId[0]['product_id']);
        $bestSellersIds             = array_keys($bestSellersInfo);
        $latestProductsIds          = array_keys($latestProductsInfo);
        $specialProductsIds         = array_keys($specialProductsInfo);
        $dealsproducts              = array();
        $featuredproducts           = array();
        $homebanners                = array();
        $bestSellersProducts        = array();
        $latestProducts             = array();
        $discountProducts           = array();
        $specialCustomModuleProducts    = array();
        $specialProductsCustomModuleIds = array();
        $specialProductsCustomModule    = $this->model_journal2_module->getModule(250,array("limit"=>20,"sort"=>"rand()"));
        $specialdealtitle               = $specialProductsCustomModule['module_data']['product_sections'][0]['section_title']["value"][1];
        $specialdealtitle               = isset($specialdealtitle) ? $specialdealtitle : "Special Deals";
        
        foreach ($specialProductsCustomModule['module_data']['product_sections'][0]['products'] as $result) {
            array_push($specialProductsCustomModuleIds,$result['data']['id']);
        }
        $specialProductsCustomModuleIds = $this->shuffleItems($specialProductsCustomModuleIds);
        foreach ($specialProductsCustomModuleIds as $item) {
            $tmpProduct = $this->model_catalog_product->getProduct($item);
            if ($tmpProduct !== false) {
              if(count($specialCustomModuleProducts) == 4){ break; }
              else if($tmpProduct['quantity'] > 0) array_push($specialCustomModuleProducts,$tmpProduct);
            }
        }
        $bestSellersIds = $this->shuffleItems($bestSellersIds);
        for( $i = 0;$i < count($bestSellersIds); $i++){
            if (count($bestSellersProducts) == 4)
            {
              break;
            }else if($bestSellersInfo[$bestSellersIds[$i]]['quantity'] > 0){
             array_push($bestSellersProducts,$bestSellersInfo[$bestSellersIds[$i]]);
            }
        }
            
        
        for( $i = 0;$i < count($latestProductsIds); $i++){
            array_push($latestProducts,$latestProductsInfo[$latestProductsIds[$i]]);
        }
            
            
        for( $i = 0;$i < count($specialProductsIds); $i++){
            if($specialProductsInfo[$specialProductsIds[$i]]['quantity'] > 0){
             array_push($discountProducts,$specialProductsInfo[$specialProductsIds[$i]]);
            }
            
        }
        
        for($i = 0; $i < sizeof($randomIds); $i++){
            $product                = $this->model_catalog_product->getProduct($randomIds[$i]['product_id']);
            $productfeatured        = $this->model_catalog_product->getProduct($randomDealsIds[$i]['product_id']);
            array_push($dealsproducts,$product);
            array_push($featuredproducts,$productfeatured);
        }
        $json['status'] = TRUE;
        $json['data']   = array(
            array('name'=> $specialdealtitle,'products'=>$specialCustomModuleProducts,'time' => $SpecialCountdownTime, 'module_id'=> '007'),
            array('name'=>'Discount Deals','products'=>$discountProducts, 'module_id'=> '006'),
            array('name'=>'Deals of the week','products'=>$dealsproducts, 'module_id'=> '005'),
            array('name'=>'Latest Arrivals','products'=>$latestProducts, 'module_id'=> '003'),
            array('name'=>'Bestsellers','products'=>$bestSellersProducts, 'module_id'=> '002'),
            array('name'=>'Most Viewed Products','products'=>$mostViewedProducts, 'module_id'=> '004'),
            array('name'=>'Featured Products','products'=>$featuredproducts, 'module_id'=> '001'),
        );
        if (isset($this->request->server['HTTP_ORIGIN'])) {
          $this->response->addHeader('Access-Control-Allow-Origin: ' . '*');
          $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          $this->response->addHeader('Access-Control-Max-Age: 1000');
          $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function getstates()
    {   
        $this->load->model('localisation/zone');
        $json['status']          = TRUE;
		$zone_info               = $this->model_localisation_zone->getZonesByCountryId(156);
        $json['data']            = $zone_info;
        if (isset($this->request->server['HTTP_ORIGIN'])) {
          $this->response->addHeader('Access-Control-Allow-Origin: ' . '*');
          $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          $this->response->addHeader('Access-Control-Max-Age: 1000');
          $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    public function getmaincategories()
    {   
        $json['status']              = TRUE;
        $json['data']  = array(
            array("name" => "Automobile", "category_id"=>"653", "icon"=> "car"),
            array("name" => "Computers", "category_id"=>"254" , "icon"=> "desktop"),
            array("name" => "Electronics", "category_id"=>"349", "icon"=> "plug"),
            array("name" => "Fashion", "category_id"=>"344" , "icon"=> "tshirt"),
            array("name" => "Phone & Tablets", "category_id"=>"367", "icon"=> "mobile-alt"),
            array("name" => "Health & Beauty", "category_id"=>"842", "icon"=> "heartbeat"),
            array("name" => "Home & Office", "category_id"=>"457", "icon"=> "home"),
            array("name" => "Kiddies World", "category_id"=>"425", "icon"=> "child"),
            array("name" => "Grocery", "category_id"=>"641", "icon"=> "shopping-basket")
        );
        if (isset($this->request->server['HTTP_ORIGIN'])) {
          $this->response->addHeader('Access-Control-Allow-Origin: ' . '*');
          $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          $this->response->addHeader('Access-Control-Max-Age: 1000');
          $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    private function shuffleItems($items= array())
    {
      $currentIndex = count($items);
      $temporaryValue; 
      $randomIndex;
      // While there remain elements to shuffle...
      while (0 !== $currentIndex) {
      // Pick a remaining element...
        $randomIndex = floor($this->random() * $currentIndex);
        $currentIndex -= 1;
      // And swap it with the current element.
        $temporaryValue = $items[$currentIndex];
        $items[$currentIndex] = $items[$randomIndex];
        $items[$randomIndex] = $temporaryValue;
      }
      return $items;
    }
    
    private function random() {
      return (float)rand()/(float)getrandmax();
    }
}