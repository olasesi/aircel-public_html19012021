<?php
class ControllerJournal2Snippets extends Controller {

    const FB_IMG_WIDTH          = 600;
    const FB_IMG_HEIGHT         = 315;
    const TWITTER_IMG_WIDTH     = 200;
    const TWITTER_IMG_HEIGHT    = 200;

    protected $data = array();

    private $s_type         = null;
    private $s_title        = null;
    private $s_description  = null;
    private $s_url          = null;
    private $s_image        = null;

    protected function render() {
        if (version_compare(VERSION, '2.2', '<')) {
            $this->template = $this->config->get('config_template') . '/template/' . $this->template;
        }

        $this->template = str_replace($this->config->get('config_template') . '/template/' . $this->config->get('config_template') . '/template/', $this->config->get('config_template') . '/template/', $this->template);

        if (version_compare(VERSION, '3', '>=')) {
            return $this->load->view(str_replace('.tpl', '', $this->template), $this->data);
        }

        return Front::$IS_OC2 ? $this->load->view($this->template, $this->data) : parent::render();
    }

    public function index() {
        $this->load->model('tool/image');

        /* blog manager compatibility */
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : null;
        if ($route !== null && in_array($route, array('blog/article', 'blog/category'))) {
            return;
        }
        /* end of blog manager compatibility */

        /* default values */
        $this->s_type = 'website';
        $this->s_title = $this->config->get('config_name');
        $meta_description = $this->config->get('config_meta_description');
        if (is_array($meta_description)) {
            $lang_id = $this->config->get('config_language_id');
            if (isset($meta_description[$lang_id])) {
                $this->s_description = $meta_description[$lang_id] . '...';
            }
        } else {
            $this->s_description = $meta_description  . '...';
        }

		if (isset($this->request->server['HTTPS']) && $this->request->server['HTTPS']) {
			$this->s_url = $this->config->get('config_ssl');
		} else {
			$this->s_url = $this->config->get('config_url');
		}

		if ($this->journal2->settings->get('retina_logo') && is_file(DIR_IMAGE . $this->journal2->settings->get('retina_logo'))) {
			$this->s_image = $this->journal2->settings->get('retina_logo');
		} else {
			$this->s_image = $this->config->get('config_logo');
		}

        /* overwrite values */
        switch ($this->journal2->page->getType()) {
            case 'product':
                $this->load->model('catalog/product');
                $product_info = $this->model_catalog_product->getProduct($this->journal2->page->getId());
                if ($product_info) {
                    $this->s_type = 'product';
                    $this->s_title = $product_info['name'];
                    $this->s_description = trim(utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, 300));
                    $this->s_image = $product_info['image'];
                    $this->s_url = $this->url->link('product/product', 'product_id=' . $this->journal2->page->getId());
                    $this->journal2->settings->set('product_description', $product_info['meta_description']);

                    $this->journal2->settings->set('product_google_snippet', 'itemscope itemtype="http://schema.org/Product"');

                    if ((float)$product_info['special']) {
						$price = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
					}

					if (version_compare(VERSION, '2.2', '>=')) {
						$price = $this->currency->format($price, $this->session->data['currency'], '', false);
					} else {
						$price = $this->currency->format($price, '', '', false);
					}

					$this->journal2->settings->set('product_price', number_format($price, 2));

                    $this->journal2->settings->set('product_price_currency', $this->session->data['currency']);
                    $this->journal2->settings->set('product_num_reviews', $product_info['reviews']);
                    $this->journal2->settings->set('product_in_stock', $product_info['quantity'] > 0 ? 'yes' : 'no');
                    /* review ratings */
                    $this->language->load('product/product');

                    $this->load->model('catalog/review');

                    $this->data['text_on'] = $this->language->get('text_on');
                    $this->data['text_no_reviews'] = $this->language->get('text_no_reviews');

                    if (isset($this->request->get['page'])) {
                        $page = (int)$this->request->get['page'];
                    } else {
                        $page = 1;
                    }

                    $this->data['reviews'] = array();

                    $review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

                    $results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

                    foreach ($results as $result) {
                        $this->data['reviews'][] = array(
                            'author'     => $result['author'],
                            'text'       => $result['text'],
                            'rating'     => (int)$result['rating'],
                            'reviews'    => sprintf($this->language->get('text_reviews'), (int)$review_total),
                            'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
                        );
                    }

                    $pagination = new Pagination();
                    $pagination->total = $review_total;
                    $pagination->page = $page;
                    $pagination->limit = 5;
                    $pagination->text = $this->language->get('text_pagination');
                    $pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

                    $this->data['pagination'] = $pagination->render();

                    $this->template = 'product/review.tpl';

                    $this->journal2->settings->set('product_reviews', $this->render());
                }
                break;

            case 'category':
                $this->load->model('catalog/category');
                $category_info = $this->model_catalog_category->getCategory($this->journal2->page->getId());
                if ($category_info) {
                    $this->s_title = $category_info['name'];
                    $this->s_description = trim(utf8_substr(strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8')), 0, 300));
                    $this->s_image = $category_info['image'];
                    $this->s_url = $this->url->link('product/category', 'path=' . $this->journal2->page->getId());
                }
                break;

            case 'manufacturer':
                $this->load->model('catalog/manufacturer');
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->journal2->page->getId());
                if ($manufacturer_info) {
                    $this->s_title = $manufacturer_info['name'];
                    $this->s_description = $manufacturer_info['name'];
                    $this->s_image = $manufacturer_info['image'];
                    $this->s_url = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->journal2->page->getId());
                }
                break;

			case 'journal-blog':
				if ($this->journal2->page->getId()) {
					$this->s_url = $this->url->link('journal2/blog', 'journal_blog_category_id=' . $this->journal2->page->getId());
				} else {
					$this->s_url = $this->url->link('journal2/blog');
				}
				break;

            case 'journal-blog-post':
                $this->load->model('journal2/blog');
                $post_info = $this->model_journal2_blog->getPost($this->journal2->page->getId());
                if ($post_info) {
                    $this->s_type = 'article';
                    $this->s_title = Journal2Utils::getProperty($post_info, 'name');
                    $this->s_description = trim(utf8_substr(strip_tags(html_entity_decode(Journal2Utils::getProperty($post_info, 'description'), ENT_QUOTES, 'UTF-8')), 0, 300));
                    $this->s_image = Journal2Utils::getProperty($post_info, 'image');
                    $this->s_url = $this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $this->journal2->page->getId());
                }
                break;

			case 'information':
				$this->s_url = $this->url->link('information/information', 'information_id=' . $this->journal2->page->getId());
				break;

			case 'contact':
				$this->s_url = $this->url->link('information/contact');
				break;
        }

        $metas = array();

        // Facebook
        $metas[] = array('key' => 'property', 'type' => 'og:title'       , 'content' => $this->s_title);
        $metas[] = array('key' => 'property', 'type' => 'og:site_name'   , 'content' => $this->config->get('config_name'));
        $metas[] = array('key' => 'property', 'type' => 'og:url'         , 'content' => str_replace('&amp;', '&', $this->s_url));
        $metas[] = array('key' => 'property', 'type' => 'og:description' , 'content' => $this->s_description);
        $metas[] = array('key' => 'property', 'type' => 'og:type'        , 'content' => $this->s_type);
        $metas[] = array('key' => 'property', 'type' => 'og:image'       , 'content' => Journal2Utils::resizeImage($this->model_tool_image, $this->s_image, self::FB_IMG_WIDTH, self::FB_IMG_HEIGHT, 'fit'));
        $metas[] = array('key' => 'property', 'type' => 'og:image:width' , 'content' => self::FB_IMG_WIDTH);
        $metas[] = array('key' => 'property', 'type' => 'og:image:height', 'content' => self::FB_IMG_HEIGHT);

        // Twitter
        $metas[] = array('key' => 'name', 'type' => 'twitter:card'           , 'content' => 'summary');
        $metas[] = array('key' => 'name', 'type' => 'twitter:title'          , 'content' => $this->s_title);
        $metas[] = array('key' => 'name', 'type' => 'twitter:description'    , 'content' => $this->s_description);
        $metas[] = array('key' => 'name', 'type' => 'twitter:image'          , 'content' => Journal2Utils::resizeImage($this->model_tool_image, $this->s_image, self::TWITTER_IMG_WIDTH, self::TWITTER_IMG_HEIGHT, 'fit'));
        $metas[] = array('key' => 'name', 'type' => 'twitter:image:width'    , 'content' => self::TWITTER_IMG_WIDTH);
        $metas[] = array('key' => 'name', 'type' => 'twitter:image:height'   , 'content' => self::TWITTER_IMG_HEIGHT);

        $this->journal2->settings->set('share_metas', $metas);
    }

}
