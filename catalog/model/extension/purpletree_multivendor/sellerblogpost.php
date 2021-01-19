<?php
class ModelExtensionPurpletreeMultivendorSellerblogpost extends Model {

 public function getSellerstoreBySellerid($seller_id) {
		$sql = "SELECT pvs.store_name FROM " . DB_PREFIX . "purpletree_vendor_stores pvs WHERE pvs.seller_id='" . (int)$seller_id . "'";
		$query = $this->db->query($sql);
	//echo"<pre>";print_r($query->row['store_name']);die;
		return $query->row['store_name'];
	
	}
	
	public function getSellerstore() {
		$sql = "SELECT pvs.seller_id,pvs.store_name FROM " . DB_PREFIX . "purpletree_vendor_stores pvs ";

		$query = $this->db->query($sql);

		return $query->rows;
	
	}
	
	public function addPost($data,$seller_id) {
		if(isset($data['sort_order'])){
	        
	    }else{
	       $data['sort_order'] = 0;
	    }
		$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_blog_post SET seller_id = '" . (int)$seller_id . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', created_at = NOW(), updated_at = NOW()");

		$blog_post_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_blog_post SET image = '" . $this->db->escape($data['image']) . "' WHERE blog_post_id = '" . (int)$blog_post_id . "'");
		}

		foreach ($data['blog_description'] as $language_id => $value) {
			
			if($value['meta_title'] == ''){
				$value['meta_title'] = trim($value['title']);
			}
			
			if($value['meta_description'] == ''){
				
				$value['meta_description'] = strip_tags(html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8'));
			}
			
			if($value['meta_keyword'] == ''){
				$value['meta_keyword'] = $value['post_tags'];
			}
			
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_blog_post_description SET blog_post_id = '" . (int)$blog_post_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', post_tags = '".$this->db->escape($value['post_tags'])."'");
		}
		

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET query = 'blog_post_id=" . (int)$blog_post_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('purpletree_vendor_blog_post');

		return $blog_post_id;
	}

	public function editPost($blog_post_id, $data) {
		if(isset($data['sort_order'])){
	        
	    }else{
	       $data['sort_order'] = 0;
	    }
		$seller_id=$this->customer->getId();
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_blog_post SET  seller_id = '" . (int)$seller_id . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', updated_at = NOW() WHERE blog_post_id = '" . (int)$blog_post_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_blog_post SET image = '" . $this->db->escape($data['image']) . "' WHERE blog_post_id = '" . (int)$blog_post_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_blog_post_description WHERE blog_post_id = '" . (int)$blog_post_id . "'");

		foreach ($data['blog_description'] as $language_id => $value) {
			
			if($value['meta_title'] == ''){
				$value['meta_title'] = trim($value['title']);
			}
			
			if($value['meta_description'] == ''){
				
				$value['meta_description'] = strip_tags(html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8'));
			}
			
			if($value['meta_keyword'] == ''){
				$value['meta_keyword'] = $value['post_tags'];
			}
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_blog_post_description SET blog_post_id = '" . (int)$blog_post_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', post_tags = '".$this->db->escape($value['post_tags'])."'");
		}		
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'blog_post_id=" . (int)$blog_post_id . "'");

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET query = 'blog_post_id=" . (int)$blog_post_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('purpletree_vendor_blog_post');
	}

	public function copyPost($blog_post_id) {
		
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "purpletree_vendor_blog_post bp WHERE bp.blog_post_id = '" . (int)$blog_post_id . "'");
         
		$seller_id=$this->customer->getId(); 
		
		if ($query->num_rows) {
			$data = $query->row;

			$data['blog_description'] = $this->getBlogDescriptions($blog_post_id);
			$this->addPost($data,$seller_id);
		}
	}

	public function deletePost($blog_post_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_blog_post WHERE blog_post_id = '" . (int)$blog_post_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_blog_post_description WHERE blog_post_id = '" . (int)$blog_post_id . "'");		
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'blog_post_id=" . (int)$blog_post_id . "'");

		$this->cache->delete('purpletree_vendor_blog_post');
	}

	public function getPost($blog_post_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE query = 'blog_post_id=" . (int)$blog_post_id . "' LIMIT 0,1) AS keyword FROM " . DB_PREFIX . "purpletree_vendor_blog_post bp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd ON (bp.blog_post_id = bpd.blog_post_id) WHERE bp.blog_post_id = '" . (int)$blog_post_id . "' AND bpd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getBlogs($data = array(),$seller_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "purpletree_vendor_blog_post bp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd ON (bp.blog_post_id = bpd.blog_post_id) WHERE bpd.language_id = '" . (int)$this->config->get('config_language_id') . "'AND bp.seller_id = '" . (int)$seller_id . "'";
		
		$sql .= " GROUP BY bp.blog_post_id";
		

		$sort_data = array(
			'bpd.title',
			'bp.status',
			'bp.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY bpd.title";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getBlogByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_blog_post bp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd ON (bp.blog_post_id = bpd.blog_post_id) LEFT JOIN " . DB_PREFIX . "pts_blog_post_to_category bp2c ON (bp.blog_post_id = bp2c.blog_post_id) WHERE bpd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bp2c.blog_category_id = '" . (int)$category_id . "' ORDER BY bpd.title ASC");

		return $query->rows;
	}

	public function getBlogDescriptions($blog_post_id) {
		$blog_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_blog_post_description WHERE blog_post_id = '" . (int)$blog_post_id . "'");

		foreach ($query->rows as $result) {
			$blog_description_data[$result['language_id']] = array(
				'title'             => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'post_tags'     => $result['post_tags']
			);
		}

		return $blog_description_data;
	}

	public function getBlogCategories($blog_post_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pts_blog_post_to_category WHERE blog_post_id = '" . (int)$blog_post_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['blog_category_id'];
		}

		return $product_category_data;
	}

	public function getBlogStores($blog_post_id) {
		$post_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pts_blog_post_store WHERE blog_post_id = '" . (int)$blog_post_id . "'");

		foreach ($query->rows as $result) {
			$post_store_data[] = $result['store_id'];
		}

		return $post_store_data;
	}

	public function getTotalBlogs($data = array(),$seller_id) {
		//echo"<pre>"; print_R($seller_id); die;
		$sql = "SELECT COUNT(DISTINCT bp.blog_post_id) AS total FROM " . DB_PREFIX . "purpletree_vendor_blog_post bp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd ON (bp.blog_post_id = bpd.blog_post_id)";
		
		$sql .= " WHERE bpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sql .= " AND bp.seller_id = '" . (int)$seller_id . "'";

		
		
		
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
		public function getSeoUrlsByKeyword($keyword) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE keyword = '" . $this->db->escape($keyword) . "'");

		return $query->rows;
	}
}
