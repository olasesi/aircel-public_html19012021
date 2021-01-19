<?php
class ControllerJournal2Category extends Controller {

    protected $data = array();

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

    public function refine_images() {
        if (!in_array($this->journal2->settings->get('refine_category'), array('grid', 'carousel'))) return;
        if (!isset($this->request->get['route']) || $this->request->get['route'] !== 'product/category') return;

        $path = isset($this->request->get['path']) ? $this->request->get['path'] : false;
        if ($path) {
            $this->load->model('catalog/category');

            $parts = explode('_', (string)$path);
            $category_id = (int)array_pop($parts);

            $categories = $this->model_catalog_category->getCategories($category_id);

            $image_width = $this->journal2->settings->get('refine_image_width', 175);
            $image_height = $this->journal2->settings->get('refine_image_height', 175);
            $image_type = $this->journal2->settings->get('refine_image_type', 'fit');

            $data = array();
            foreach ($categories as $category) {
                $filters = array(
                    'filter_category_id'  => $category['category_id'],
                    'filter_sub_category' => true
                );

                if ($this->config->get('config_product_count')) {
                    $product_total = ' (' . $this->model_catalog_product->getTotalProducts($filters) . ')';
                } else {
                    $product_total = '';
                }

                $data[] = array(
                    'name'  => $category['name'] . $product_total,
                    'href'  => $this->url->link('product/category', 'path=' . $path . '_' . $category['category_id']),
                    'thumb'	=> Journal2Utils::resizeImage($this->model_tool_image, $category, $image_width, $image_height, $image_type)
                );
            }

            $this->journal2->settings->set('refine_category_images', $data);
        }
    }

}
?>