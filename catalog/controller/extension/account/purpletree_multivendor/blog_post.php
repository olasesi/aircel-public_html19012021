<?php
class ControllerExtensionAccountPurpletreeMultivendorBlogPost extends Controller{
	private $error = array();
	
	public function index(){ 
	    if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}else{
			        if(isset($store_detail['store_status']) && $store_detail[  'multi_store_id'] != $this->config->get('config_store_id')){	
						$this->response->redirect($this->url->link('account/account','', true));
				    }
		        }
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		$this->load->language('purpletree_multivendor/blog_post');
		
		$this->load->model('extension/purpletree_multivendor/blog_post');

		$data['breadcrumbs'] = array();
		$data['post_comments'] = array();
		$data['comment_count'] = 0;
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
		);

		if (isset($this->request->get['blog_post_id'])) {
			$blog_post_id = (int)$this->request->get['blog_post_id'];
		} else {
			$blog_post_id = 0;
		}
		
		$post_info = $this->model_extension_purpletree_multivendor_blog_post->getPost($blog_post_id);

		if ($post_info) {
			$this->load->model('tool/image');
			$this->document->setTitle($post_info['meta_title']);
			$this->document->setDescription($post_info['meta_description']);
			$this->document->setKeywords($post_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $post_info['title'],
				'href' => $this->url->link('extension/purpletree_multivendor/blog_post', 'blog_post_id=' .  $blog_post_id,true)
			);

			$data['heading_title'] = $post_info['title'];

			$data['button_continue'] = $this->language->get('button_continue');
			$data['button_comment'] = $this->language->get('button_comment');
			
			$data['entry_name'] = $this->language->get('entry_name');
			$data['entry_email'] = $this->language->get('entry_email');
			$data['entry_text'] = $this->language->get('entry_text');
			
			$data['text_comment_heading'] = $this->language->get('text_comment_heading');
			$data['text_comment_empty'] = $this->language->get('text_comment_empty');
			$data['text_comment_reply'] = $this->language->get('text_comment_reply');
			
			if (isset($this->error['name'])) {
				$data['error_name'] = $this->error['name'];
			} else {
				$data['error_name'] = '';
			}

			if (isset($this->error['email'])) {
				$data['error_email_id'] = $this->error['email'];
			} else {
				$data['error_email_id'] = '';
			}
			
			if (isset($this->error['text'])) {
				$data['error_text'] = $this->error['text'];
			} else {
				$data['error_text'] = '';
			}
			
			if (isset($this->request->post['name'])) {
				$data['name'] = $this->request->post['name'];
			} else {
				$data['name'] = '';
			}
			
			if (isset($this->request->post['email_id'])) {
				$data['email_id'] = $this->request->post['email_id'];
			} else {
				$data['email_id'] = '';
			}
			
			if (isset($this->request->post['text'])) {
				$data['text'] = $this->request->post['text'];
			} else {
				$data['text'] = '';
			}
			
			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}
		
			if ($post_info['image']) {
				$data['image'] = $this->model_tool_image->resize($post_info['image'], '200' , '200');
			} else {
				$data['image'] = $this->model_tool_image->resize('placeholder.png', '200', '200');
			}
		//	$data['author'] = $post_info['author'];
			$data['post_tags'] = explode(',',$post_info['post_tags']);
			$data['post_date'] = date('d M Y', strtotime($post_info['created_at']));
			$data['description'] = html_entity_decode($post_info['description'], ENT_QUOTES, 'UTF-8');
			
			/******************************* Post Comments ****************************************/
			$post_comments = $this->model_extension_purpletree_multivendor_blog_post->getPostComments($blog_post_id);
			$data['comment_count'] = count($post_comments);

			foreach($post_comments as $post_comment){
				$data['post_comments'][] = array(
					'name' => $post_comment['name'],
					'text' => $post_comment['text'],
					'date' => date('d M Y', strtotime($post_comment['created_at']))
				);
			}
			
			$data['action'] = $this->url->link('extension/account/purpletree_multivendor/blog_post/addcomment', 'blog_post_id=' . $blog_post_id,true);
			
			$data['continue'] = $this->url->link('common/home','',true);
			
			$data['text_popupar_posts'] = $this->language->get('text_popupar_posts');
			
			$data['pts_blogs'] = array();
			
			$results = $this->model_extension_purpletree_multivendor_blog_post->getPopularBlog(5);

			if ($results) {
				foreach ($results as $result) {
					
						 if ($result['image']) {
							$image = $this->model_tool_image->resize($result['image'], 200, 200);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', 200, 200);
						} 
					
               
				   $data['pts_blogs'][] = array(
						'blog_post_id'  => $result['blog_post_id'],
						'thumb'         => $image,
						'title'         => $result['title'],
						'date'          => date('d M'),
						'href'          => $this->url->link('extension/account/purpletree_multivendor/blog_post', 'blog_post_id=' . $result['blog_post_id'],true)
					);
				}
			}
			
			// For Popular Tags
			
			$data['text_popupar_tags'] = $this->language->get('text_popupar_tags');
			$data['pts_tags'] = array();
			$results = $this->model_extension_purpletree_multivendor_blog_post->getPopularTags();
			if ($results) {
				foreach ($results as $result) {
					
					$data['pts_tags'][] = array(
						'tags' => explode(',', $result['post_tags']),
						'href' => $this->url->link('extension/account/purpletree_multivendor/blog_post', 'blog_post_id=' . $result['blog_post_id'],true)
					);
				}
			}
			

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/purpletree_multivendor/blog_post_detail', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('extension/account/purpletree_multivendor/blog_post', 'blog_post_id=' . $blog_post_id,true)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home','',true);

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	public function addcomment(){
		$this->load->language('purpletree_multivendor/blog_post');
		
		$this->load->model('extension/purpletree_multivendor/blog_post');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) { 
			$this->model_extension_purpletree_multivendor_blog_post->addComment($this->request->get['blog_post_id'],$this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_comment_add');
			
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/blog_post', 'blog_post_id='.$this->request->get['blog_post_id'], true));
		}
		
		$this->index();
	}
	public function all_blog(){    
	    $this->load->language('extension/module/purpletree_sellerblog');
	    $this->load->language('purpletree_multivendor/blog_post');		
		$this->load->model('extension/purpletree_multivendor/blog_post');	
	
		$data['breadcrumbs'] = array();	
		
		$data['blog'] = array();	
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {		
			$limit = $this->config->get('config_limit_admin');
		}
		
		$data['text_readmore'] = $this->language->get('text_readmore');
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_blog_heading'),
				'href' => $this->url->link('extension/account/purpletree_multivendor/blog_post/all_blog','', true)
			);		
		
		$filter_data = array(			
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);		
		
		$blog_total = $this->model_extension_purpletree_multivendor_blog_post->getTotalBlog($filter_data);		
		
		$results = $this->model_extension_purpletree_multivendor_blog_post->getBlog($filter_data);
		
		if ($results) {
			$this->document->setTitle($this->language->get('text_blog_heading'));
			$this->load->model('tool/image');			

			$data['heading_title'] = $this->language->get('text_blog_heading');

			$data['button_continue'] = $this->language->get('button_continue');	
			
			foreach($results as $result) {								  
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('module_purpletree_sellerblog_width'), $this->config->get('module_purpletree_sellerblog_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('module_purpletree_sellerblog_width'), $this->config->get('module_purpletree_sellerblog_height'));
				}

				$data['blog'][] = array(
					'blog_post_id'  => $result['blog_post_id'],
					'thumb'       => $image,
					'title'        => $result['title'],
					'description' => substr(utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')), 0, 20) . '...',
					'date'     => date('d M', strtotime($result['created_at'])),
					'href'        => $this->url->link('extension/account/purpletree_multivendor/blog_post', 'blog_post_id=' . $result['blog_post_id'])
				);
			}		
			$data['text_readmore'] = $this->language->get('text_readmore');
			$pagination = new Pagination();
			$pagination->total = $blog_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('extension/purpletree_blog/blog_post', 'page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($blog_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($blog_total - $limit)) ? $blog_total : ((($page - 1) * $limit) + $limit), $blog_total, ceil($blog_total / $limit));
			
			$data['continue'] = $this->url->link('common/home');            
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/purpletree_multivendor/blog_posts', $data)); 
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('extension/account/purpletree_multivendor/blog_post/all_blog','',true)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	
	protected function validateForm() {
		if ((utf8_strlen(trim($this->request->post['name'])) < 3) || (utf8_strlen(trim($this->request->post['name'])) > 32)) { 
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen(trim($this->request->post['text'])) < 10) || (utf8_strlen(trim($this->request->post['text'])) > 350)) {
			$this->error['text'] = $this->language->get('error_text');
		}
		
		if ((utf8_strlen($this->request->post['email_id']) > 96) || !filter_var($this->request->post['email_id'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		return !$this->error;
	}
}
?>