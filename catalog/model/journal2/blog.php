<?php

require_once(DIR_SYSTEM . 'journal2/classes/journal2_utils.php');

class ModelJournal2Blog extends Model {

    private static $BLOG_KEYWORD = null;
    private static $BLOG_KEYWORDS = null;
    private static $is_installed = null;
    private static $author_name = null;

    private $db_prefix = '';

    public function __construct($registry) {
        parent::__construct($registry);
        $this->db_prefix = $this->db->escape(DB_PREFIX);
        $this->language_id = (int)$this->config->get('config_language_id');
        $this->store_id = (int)$this->config->get('config_store_id');
    }

    public function isEnabled() {
        if (!defined('JOURNAL_INSTALLED')) {
            return false;
        }

        if (self::$is_installed === null) {
            $query = $this->db->query(str_replace('_', '\_', 'show tables like "' . DB_PREFIX . 'journal2_blog%"'));
            self::$is_installed = $query->num_rows >= 9;
            if ($query->num_rows >= 9 && $query->num_rows < 11) {
                /* create table */
                $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'journal2_blog_category_to_store` (
                    `category_id` int(11),
                    `store_id` int(11)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8');

                /* assign current categories to the default store */
                $this->db->query('INSERT INTO `' . DB_PREFIX . 'journal2_blog_category_to_store` (category_id, store_id) SELECT category_id, 0 as store_id FROM `' . DB_PREFIX . 'journal2_blog_category`');

                /* create table */
                $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'journal2_blog_post_to_store` (
                    `post_id` int(11),
                    `store_id` int(11)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8');

                /* assign current posts to the default store */
                $this->db->query('INSERT INTO `' . DB_PREFIX . 'journal2_blog_post_to_store` (post_id, store_id) SELECT post_id, 0 as store_id FROM `' . DB_PREFIX . 'journal2_blog_post`');
            }
        }

        if (self::$is_installed !== true) {
            return false;
        }

        return $this->journal2->settings->get('config_blog_settings.status', '1') === '1';
    }

    public function getBlogKeyword() {
        if (self::$BLOG_KEYWORD === null) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "journal2_config WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND `key` = 'blog_settings'");
            if (!$query->num_rows) {
                self::$BLOG_KEYWORD = false;
            } else {
                $value = json_decode($query->row['value'], true);
                self::$BLOG_KEYWORD = Journal2Utils::getProperty($value, 'keyword.value.' . $this->config->get('config_language_id'));
            }
        }
        return self::$BLOG_KEYWORD;
    }

    public function getBlogKeywords() {
        if (self::$BLOG_KEYWORDS === null) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "journal2_config WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND `key` = 'blog_settings'");
            if (!$query->num_rows) {
                self::$BLOG_KEYWORDS = false;
            } else {
                self::$BLOG_KEYWORDS = array();
                $value = json_decode($query->row['value'], true);
                $keywords = Journal2Utils::getProperty($value, 'keyword.value', array());
                foreach ($keywords as $keyword) {
                    self::$BLOG_KEYWORDS[$keyword] = $keyword;
                    self::$BLOG_KEYWORDS[$keyword . '/'] = $keyword . '/';
                }
            }
        }
        return self::$BLOG_KEYWORDS;
    }

    public function rewriteCategory($category_id) {
        $category = $this->getCategory($category_id);
        return $category && $category['keyword'] ? $category['keyword'] : null;
    }

    public function rewritePost($post_id) {
        $post = $this->getPost($post_id);
        return $post && $post['keyword'] ? $post['keyword'] : null;
    }

    public function getSettings() {
        return $this->journal2->settings->get('config_blog_settings');
    }

    public function getCategories() {
        $query = $this->db->query("
            SELECT
                c.category_id,
                cd.name
            FROM `{$this->db_prefix}journal2_blog_category` c
            LEFT JOIN `{$this->db_prefix}journal2_blog_category_description` cd ON c.category_id = cd.category_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_category_to_store` c2s ON c.category_id = c2s.category_id
            WHERE cd.language_id = {$this->language_id} AND c.status = 1 AND c2s.store_id = {$this->store_id}
            ORDER BY c.sort_order
        ");

        return $query->rows;
    }

    public function getCategoriesByPostId($post_id) {
        $post_id = (int)$post_id;
        $query = $this->db->query("
            SELECT
                c.category_id,
                cd.name
            FROM `{$this->db_prefix}journal2_blog_category` c
            LEFT JOIN `{$this->db_prefix}journal2_blog_category_description` cd ON c.category_id = cd.category_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_category` p2c ON c.category_id = p2c.category_id
            WHERE
              cd.language_id = {$this->language_id}
              AND p2c.post_id = {$post_id}
              AND c.status = 1
        ");

        return $query->rows;
    }

    public function getCategory($category_id) {
        $category_id = (int)$category_id;

        $query = $this->db->query("
            SELECT
                c.category_id,
                cd.name,
                cd.description,
                cd.meta_title,
                cd.meta_keywords,
                cd.meta_description,
                cd.keyword
            FROM `{$this->db_prefix}journal2_blog_category` c
            LEFT JOIN `{$this->db_prefix}journal2_blog_category_description` cd ON c.category_id = cd.category_id
            WHERE c.category_id = {$category_id} AND cd.language_id = {$this->language_id} AND c.status = 1
        ");

        return $query->row;
    }

    public function getPost($post_id) {
        $post_id = (int)$post_id;

        $query = $this->db->query("
            SELECT
                p.post_id,
                p.image,
                p.comments,
                p.date_created,
                pd.name,
                pd.description,
                pd.meta_title,
                pd.meta_keywords,
                pd.meta_description,
                pd.keyword,
                pd.tags,
                a.username,
                a.firstname,
                a.lastname
            FROM `{$this->db_prefix}journal2_blog_post` p
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_description` pd ON p.post_id = pd.post_id
            LEFT JOIN `{$this->db_prefix}user` a ON p.author_id = a.user_id
            WHERE p.post_id = {$post_id} AND pd.language_id = {$this->language_id} AND p.status = 1
        ");

        return $query->row;
    }

    public function getPosts($data = array()) {
        $sql = "
            SELECT
                p.post_id,
                p.image,
                p.date_created as date,
                pd.name,
                pd.description,
                a.username,
                a.firstname,
                a.lastname,
                a.email,
                (
                    SELECT count(*)
                    FROM `{$this->db_prefix}journal2_blog_comments` bc
                    WHERE bc.post_id = p.post_id AND bc.status = 1 AND bc.parent_id = 0
                ) as comments
            FROM `{$this->db_prefix}journal2_blog_post` p
            ";

        if (isset($data['category_id']) && $data['category_id']) {
            $sql .= " LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_category` p2c ON p.post_id = p2c.post_id";
        }

        $sql .= "
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_description` pd ON p.post_id = pd.post_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_store` p2s ON p.post_id = p2s.post_id
            LEFT JOIN `{$this->db_prefix}user` a ON p.author_id = a.user_id
            WHERE pd.language_id = {$this->language_id} AND p2s.store_id = {$this->store_id}
        ";

        if (isset($data['category_id']) && $data['category_id']) {
            $sql .= " AND p2c.category_id = " . (int)$data['category_id'];
        }

        if (isset($data['tag']) && $data['tag']) {
            $sql .= " AND pd.tags LIKE '%" . $this->db->escape($data['tag']) . "%'";
        }

        if (isset($data['search']) && $data['search']) {
            $temp_1 = array();
            $temp_2 = array();

            $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['search'])));

            foreach ($words as $word) {
                $temp_1[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                $temp_2[] = "pd.description LIKE '%" . $this->db->escape($word) . "%'";
            }

            if ($temp_1) {
                $sql .= ' AND ((' . implode(" AND ", $temp_1) . ') OR (' . implode(" AND ", $temp_2) . '))';
            }
        }

        if (isset($data['post_ids'])) {
            $sql .= ' AND p.post_id IN (' . $data['post_ids'] . ')';
        }

        $sql .= ' AND p.status = 1';

        $sql .= ' GROUP BY p.post_id';

        if (isset($data['sort']) && $data['sort'] === 'newest') {
            $sql .= ' ORDER BY p.date_created DESC';
        }

        if (isset($data['sort']) && $data['sort'] === 'oldest') {
            $sql .= ' ORDER BY p.date_created ASC';
        }

        if (isset($data['sort']) && $data['sort'] === 'comments') {
            $sql .= ' ORDER BY comments DESC';
        }

        if (isset($data['sort']) && $data['sort'] === 'views') {
            $sql .= ' ORDER BY p.views DESC';
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

    public function getRelatedPosts($product_id, $limit = 5) {
        $product_id = (int)$product_id;
        $limit = (int)$limit;
        $sql = "
            SELECT
                p.post_id,
                p.image,
                p.date_created as date,
                pd.name,
                pd.description,
                a.username,
                a.firstname,
                a.lastname,
                (
                    SELECT count(*)
                    FROM `{$this->db_prefix}journal2_blog_comments` bc
                    WHERE bc.post_id = p.post_id AND bc.status = 1 AND bc.parent_id = 0
                ) as comments
            FROM `{$this->db_prefix}journal2_blog_post` p
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_product` p2p ON p.post_id = p2p.post_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_description` pd ON p.post_id = pd.post_id
            LEFT JOIN `{$this->db_prefix}user` a ON p.author_id = a.user_id
            WHERE pd.language_id = {$this->language_id} AND p2p.product_id = {$product_id} AND p.status = 1
            ORDER BY pd.name ASC
            LIMIT 0, {$limit}
        ";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getPostsTotal($data = array()) {
        $sql = "
            SELECT
                count(*) as num
            FROM `{$this->db_prefix}journal2_blog_post` p
        ";

        if (isset($data['category_id']) && $data['category_id']) {
            $sql .= " LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_category` p2c ON p.post_id = p2c.post_id";
        }

        $sql .= "
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_description` pd ON p.post_id = pd.post_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_store` p2s ON p.post_id = p2s.post_id
            WHERE pd.language_id = {$this->language_id} AND p2s.store_id = {$this->store_id}
        ";

        if (isset($data['category_id']) && $data['category_id']) {
            $sql .= " AND p2c.category_id = " . (int)$data['category_id'];
        }

        if (isset($data['tag']) && $data['tag']) {
            $sql .= " AND pd.tags LIKE '%" . $this->db->escape($data['tag']) . "%'";
        }

        if (isset($data['search']) && $data['search']) {
            $temp_1 = array();
            $temp_2 = array();

            $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['search'])));

            foreach ($words as $word) {
                $temp_1[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                $temp_2[] = "pd.description LIKE '%" . $this->db->escape($word) . "%'";
            }

            if ($temp_1) {
                $sql .= ' AND ((' . implode(" AND ", $temp_1) . ') OR (' . implode(" AND ", $temp_2) . '))';
            }
        }

        $sql .= ' AND p.status = 1';

        $query = $this->db->query($sql);

        return $query->row['num'];
    }

    public function updateViews($post_id) {
        $post_id = (int)$post_id;

        $this->db->query("UPDATE `{$this->db_prefix}journal2_blog_post` SET views = if (views IS NULL, 0, views) + 1 WHERE post_id = {$post_id}");
    }

    public function getRelatedProducts($post_id) {
        $post_id = (int)$post_id;

        $query = $this->db->query("SELECT product_id FROM `{$this->db_prefix}journal2_blog_post_to_product` WHERE post_id = {$post_id}");

        $results = array();

        foreach ($query->rows as $row) {
            $results[$row['product_id']] = $this->model_catalog_product->getProduct($row['product_id']);
        }

        return $results;
    }

    public function getCommentsStatus($post_id) {
        $post = $this->getPost($post_id);

        if (!$post) {
            return false;
        }

        if ($post['comments'] != 2) {
            return (bool)$post['comments'];
        }

        return $this->journal2->settings->get('config_blog_settings.comments', '1') === '1';
    }

    public function createComment($data) {
        $parent_id = (int)Journal2Utils::getProperty($data, 'parent_id', 0);
        $post_id = (int)Journal2Utils::getProperty($data, 'post_id', 0);
        $name = $this->db->escape(Journal2Utils::getProperty($data, 'name', ''));
        $email = $this->db->escape(Journal2Utils::getProperty($data, 'email', ''));
        $website = $this->db->escape(Journal2Utils::getProperty($data, 'website', ''));
        $comment = $this->db->escape(Journal2Utils::getProperty($data, 'comment', ''));
        $status = (int)$this->journal2->settings->get('config_blog_settings.auto_approve_comments', '1');

        if (version_compare(VERSION, '2.1', '<')) {
            $this->load->library('user');
        }

        if (version_compare(VERSION, '2.2', '>=')) {
            $this->user = new \Cart\User($this->registry);
        } else {
            $this->user = new User($this->registry);
        }

        if ($this->user->isLogged()) {
            $customer_id = 0;
            $author_id = $this->user->getId();
        } else if ($this->customer->isLogged()) {
            $customer_id = $this->customer->getId();
            $author_id = 0;
        } else {
            $customer_id = 0;
            $author_id = 0;
        }

        $sql = "
            INSERT INTO `{$this->db_prefix}journal2_blog_comments`
            (parent_id, post_id, customer_id, author_id, name, email, website, comment, status, date)
            VALUES
            ({$parent_id}, {$post_id}, {$customer_id}, {$author_id}, '{$name}', '{$email}', '{$website}', '{$comment}', {$status}, NOW())
        ";

        $this->db->query($sql);

        return $this->getComment($this->db->getLastId());
    }

    public function getComments($post_id) {
        $post_id = (int)$post_id;

        $query = $this->db->query("
            SELECT
                *
            FROM `{$this->db_prefix}journal2_blog_comments` bc
            LEFT JOIN `{$this->db_prefix}journal2_blog_post` p ON p.post_id = bc.post_id
            WHERE bc.post_id = {$post_id} AND bc.parent_id = 0 AND bc.status = 1 AND p.status = 1
            ORDER BY bc.date ASC
        ");

        $comments = $query->rows;
        $replies = array();

        $comment_ids = array();

        foreach ($query->rows as $row) {
            $comment_ids[] = $row['comment_id'];
        }

        if ($comment_ids) {
            $comment_ids = implode(',', $comment_ids);
            $query = $this->db->query("
                SELECT
                    *
                FROM `{$this->db_prefix}journal2_blog_comments` bc
                WHERE bc.post_id = {$post_id} AND parent_id IN ({$comment_ids}) AND status = 1
            ");

            foreach ($query->rows as $row) {
                if (!isset($replies[$row['parent_id']])) {
                    $replies[$row['parent_id']] = array();
                }
                $replies[$row['parent_id']][] = $row;
            }

        }

        foreach ($comments as &$comment) {
            if ($comment['website']) {
                $comment['website'] = trim($comment['website']);
                $comment['website'] = trim($comment['website'], '/');
                $comment['website'] = parse_url($comment['website'], PHP_URL_SCHEME) !== null ? $comment['website'] : ('http://' . $comment['website']);
            }
            $comment['replies'] = isset($replies[$comment['comment_id']]) ? $replies[$comment['comment_id']] : array();
        }

        return $comments;
    }

    public function getComment($comment_id) {
        $comment_id = (int)$comment_id;

        $query = $this->db->query("
            SELECT
                comment_id,
                website,
                name,
                email,
                comment,
                date
            FROM `{$this->db_prefix}journal2_blog_comments` bc
            WHERE bc.comment_id = {$comment_id} AND status = 1
        ");

        return $query->row;
    }

    public function getTags($limit = 15) {
        $sql = "
            SELECT
                pd.tags as tags
            FROM `{$this->db_prefix}journal2_blog_post` p
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_description` pd ON p.post_id = pd.post_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_store` p2s ON p.post_id = p2s.post_id
            WHERE pd.language_id = {$this->language_id} AND p.status = 1 AND p2s.store_id = {$this->store_id}
        ";
        $query = $this->db->query($sql);
        $tags = array();
        $i = 0;
        foreach ($query->rows as $row) {
            foreach (explode(',', $row['tags']) as $tag) {
                $tag = trim($tag);
                if (!$tag) continue;
                $tags[$tag] = $tag;
                $i++;
                if ($i == $limit) {
                    return $tags;
                }
            }
        }
        return $tags;
    }

    public function getLatestComments($limit = 5) {
        $limit = (int)$limit;
        $query = $this->db->query("
            SELECT
                bc.comment_id,
                bc.email,
                bc.comment,
                bc.post_id,
                bc.name,
                pd.name as post
            FROM `{$this->db_prefix}journal2_blog_comments` bc
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_description` pd ON bc.post_id = pd.post_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_post` p ON p.post_id = bc.post_id
            LEFT JOIN `{$this->db_prefix}journal2_blog_post_to_store` p2s ON p.post_id = p2s.post_id
            WHERE pd.language_id = {$this->language_id} AND bc.parent_id = 0 AND bc.status = 1 AND p.status = 1 AND p2s.store_id = {$this->store_id}
            ORDER BY date desc
            LIMIT {$limit}
        ");
        return $query->rows;
    }

    public function getBlogCategoryLayoutId($category_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "journal2_blog_category_to_layout WHERE category_id = '" . (int)$category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return $query->row['layout_id'];
        } else {
            return false;
        }
    }

    public function getBlogPostLayoutId($post_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "journal2_blog_post_to_layout WHERE post_id = '" . (int)$post_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return $query->row['layout_id'];
        } else {
            return false;
        }
    }

    public function getAuthorName($data) {
        if (self::$author_name === null) {
            self::$author_name = $this->journal2->settings->get('config_blog_settings.author_name', 'username');
        }
        $author = '';
        if (self::$author_name === 'firstname' && isset($data['firstname'])) {
            $author = $data['firstname'];
        } else if (self::$author_name === 'full' && isset($data['firstname']) && isset($data['lastname'])) {
            $author = $data['firstname'] . ' ' . $data['lastname'];
        }
        $author = trim($author);
        if (!$author) {
            $author = $data['username'];
        }
        return $author;
    }

    public function getAuthorEmail($data) {
        return Journal2Utils::getProperty($data, 'email');
    }

    public function getAdminInfo($user_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$user_id . "' AND status = '1'");
        return $query->row;
    }

}