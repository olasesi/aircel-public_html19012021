<?php
// catalog/controller/api/review.php
class ControllerApiReview extends Controller {
    
     // Get add Reviews by product id
    public function addreview() {
        
        $this->load->language('api/review');
        $json = array();
        
        if (!isset($this->session->data['api_id'])) {
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
		    
		     if (isset($this->request->get['prod_id'])) {
    			$prod_id = $this->request->get['prod_id'];
    		} else { 
    		    $prod_id = 0;
    			$json['status'] = 'error';
    			$json['message'] = 'NO prod_id provided';
    			$this->response->addHeader('Content-Type: application/json');
    			return $this->response->setOutput(json_encode($json));
    		}
		    
		    // Add keys for missing post vars
			$keys = array(
				'name',
				'text',
				'rating',
			);
			
			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}
			
			if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
				$json['error']['name'] = $this->language->get('error_name');
			}
			
			if ((utf8_strlen(trim($this->request->post['text'])) < 1) || (utf8_strlen(trim($this->request->post['text'])) > 120)) {
				$json['error']['text'] = $this->language->get('error_text');
			}
			
			if ((utf8_strlen(trim($this->request->post['rating'])) < 1) || (utf8_strlen(trim($this->request->post['rating'])) > 5)) {
				$json['error']['rating'] = $this->language->get('error_rating');
			}

            $data = array(
            	'name'      => $this->request->post['name'],
    			'text'       => $this->request->post['text'],
    			'rating'        => $this->request->post['rating'],
            );

			// load model
            $this->load->model('catalog/review');
            
            $reviews = $this->model_catalog_review->addReview($prod_id, $data);
            
            $json['success'] = $this->language->get('text_address');
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
        
    }
    
    // Get Reviews by product id
    public function getreviews() {
        
        $this->load->language('api/review');
        $json = array();
        
        if (!isset($this->session->data['api_id'])) {
          $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->get['prod_id'])) {
    			$prod_id = $this->request->get['prod_id'];
    		} else { 
    		    $prod_id = 0;
    			$json['status'] = 'error';
    			$json['message'] = 'NO prod_id provided';
    			$this->response->addHeader('Content-Type: application/json');
    			return $this->response->setOutput(json_encode($json));
    		}
    		
    		// load model
            $this->load->model('catalog/review');
            
            $reviews = $this->model_catalog_review->getReviewsByProductId($prod_id);
            $json['success']['reviews'] = $reviews;
        }
        
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    
    }
    
}