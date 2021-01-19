<?php
class ModelExtensionPurpletreeMultivendorSellerblogcomment extends Model {

	public function deleteComment($comment_id) {
		
		$query = $this->db->query("SELECT blog_post_id FROM " . DB_PREFIX . "purpletree_vendor_blog_post_comment WHERE blog_comment_id = '" . (int)$comment_id . "'");
		$result = $query->row;
		/* 
		$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_blog_post_description SET total_comments = total_comments - 1 WHERE blog_post_id = '" . (int)$result['blog_post_id'] . "'");
		 */
		$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_blog_post_comment WHERE blog_comment_id = '" . (int)$comment_id . "'");
	}

	public function getComment($comment_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT bpd.title FROM " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd WHERE bpd.blog_post_id = bpc.blog_post_id AND bpd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS post FROM " . DB_PREFIX . "purpletree_vendor_blog_post_comment bpc WHERE bpc.blog_comment_id = '" . (int)$comment_id . "'");

		return $query->row;
	}

	public function getComments($data = array()) {
	
		$seller_id=$this->customer->getId();
       $sql = "SELECT bpc.*,bp.seller_id,(SELECT bpd.title FROM " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd WHERE bpd.blog_post_id = bpc.blog_post_id AND bpd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS post FROM " . DB_PREFIX . "purpletree_vendor_blog_post_comment bpc LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd ON(bpc.blog_post_id = bpd.blog_post_id)
        LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post bp ON(bpc.blog_post_id = bp.blog_post_id) WHERE bpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		$sql .= " AND bp.seller_id = '" . (int)$seller_id . "'";
		
		if (!empty($data['filter_post'])) {
			$sql .= " AND bpd.title LIKE '" . $this->db->escape($data['filter_post']) . "%'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND bpc.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND bpc.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(bpc.created_at) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$sql .= " ORDER BY bpc.blog_comment_id DESC";

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

	public function getTotalComments($data = array()) {
		$seller_id=$this->customer->getId();
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "purpletree_vendor_blog_post_comment bpc LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post_description bpd ON(bpc.blog_post_id = bpd.blog_post_id)
        LEFT JOIN " . DB_PREFIX . "purpletree_vendor_blog_post bp ON(bpc.blog_post_id = bp.blog_post_id) WHERE bpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
        $sql .= " AND bp.seller_id = '" . (int)$seller_id . "'";
		if (!empty($data['filter_post'])) {
			$sql .= " AND bpd.title LIKE '" . $this->db->escape($data['filter_post']) . "%'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND bpc.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND bpc.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(bpc.created_at) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

}