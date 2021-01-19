<?php
class ControllerJournal2Modules extends Controller {

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

    protected function getChild($child, $args = array()) {
        return version_compare(VERSION, '2', '>=') ? $this->load->controller($child, $args) : parent::getChild($child, $args);
    }

    public function index() {
        /* check maintenance mode */
        if ($this->config->get('config_maintenance')) {
            if (version_compare(VERSION, '2.1', '<')) {
                $this->load->library('user');
            }

            if (version_compare(VERSION, '2.2', '>=')) {
                $this->user = new \Cart\User($this->registry);
            } else {
                $this->user = new User($this->registry);
            }

            if (!$this->user->isLogged()) {
                return;
            }
        }

        $this->load->model('design/layout');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/information');

        if (isset($this->request->get['route'])) {
            $route = (string)$this->request->get['route'];
        } else {
            $route = 'common/home';
        }

        $layout_id = 0;

        $this->load->model('journal2/blog');

        if ($route == 'journal2/blog' && isset($this->request->get['journal_blog_category_id'])) {
            $layout_id = $this->model_journal2_blog->getBlogCategoryLayoutId($this->request->get['journal_blog_category_id']);
        }

        if ($route == 'journal2/blog/post' && isset($this->request->get['journal_blog_post_id'])) {
            $layout_id = $this->model_journal2_blog->getBlogPostLayoutId($this->request->get['journal_blog_post_id']);
        }

        if ($route == 'product/category' && isset($this->request->get['path'])) {
            $path = explode('_', (string)$this->request->get['path']);

            $layout_id = $this->model_catalog_category->getCategoryLayoutId(end($path));
        }

        if ($route == 'product/product' && isset($this->request->get['product_id'])) {
            $layout_id = $this->model_catalog_product->getProductLayoutId($this->request->get['product_id']);
        }

        if ($route == 'information/information' && isset($this->request->get['information_id'])) {
            $layout_id = $this->model_catalog_information->getInformationLayoutId($this->request->get['information_id']);
        }

        if (!$layout_id) {
            $layout_id = $this->model_design_layout->getLayout($route);
        }

        if (!$layout_id) {
            $layout_id = $this->config->get('config_layout_id');
        }

        if (version_compare(VERSION, '2', '>=')) {
            $this->renderModulesOc2($layout_id);
        } else {
            $this->renderModules($layout_id);
        }
    }

    private function renderModules($layout_id) {
        $this->load->model('setting/extension');

        $extensions = $this->model_setting_extension->getExtensions('module');

        $module_data_top = array();
        $module_data_bottom = array();
        $module_data_header = array();
        $module_data_footer = array();

        foreach ($extensions as $extension) {
            $modules = $this->config->get($extension['code'] . '_module');

            if ($modules) {
                foreach ($modules as $module) {
                    if (($module['layout_id'] == $layout_id || $module['layout_id'] == -1) && $module['position'] == 'top' && $module['status']) {
                        $module_data_top[] = array(
                            'module_id'  => $module['module_id'],
                            'code'       => $extension['code'],
                            'setting'    => $module,
                            'sort_order' => $module['sort_order']
                        );
                    }
                    if (($module['layout_id'] == $layout_id || $module['layout_id'] == -1) && $module['position'] == 'bottom' && $module['status']) {
                        $module_data_bottom[] = array(
                            'module_id'  => $module['module_id'],
                            'code'       => $extension['code'],
                            'setting'    => $module,
                            'sort_order' => $module['sort_order']
                        );
                    }
                    if (($module['layout_id'] == $layout_id || $module['layout_id'] == -1) && $module['position'] == 'header' && $module['status']) {
                        $module_data_header[] = array(
                            'module_id'  => $module['module_id'],
                            'code'       => $extension['code'],
                            'setting'    => $module,
                            'sort_order' => $module['sort_order']
                        );
                    }
                    if (($module['layout_id'] == $layout_id || $module['layout_id'] == -1) && $module['position'] == 'footer' && $module['status']) {
                        $module_data_footer[] = array(
                            'module_id'  => $module['module_id'],
                            'code'       => $extension['code'],
                            'setting'    => $module,
                            'sort_order' => $module['sort_order']
                        );
                    }
                }
            }
        }

        /* sort top modules */
        $sort_order = array();
        foreach ($module_data_top as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $module_data_top);

        /* sort bottom modules */
        $sort_order = array();
        foreach ($module_data_bottom as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $module_data_bottom);

        /* sort footer modules */
        $sort_order = array();
        foreach ($module_data_footer as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $module_data_footer);

        $this->template = 'journal2/common/modules.tpl';

        /* render top modules */
        $this->data['modules'] = array();
        foreach ($module_data_top as $module) {
            $type = $module['code'];
            $id = $module['module_id'];
            $module = $this->getChild('module/' . $module['code'], $module['setting']);
            if ($module) {
                $this->data['modules'][] = array(
                    'module_id' => $id,
                    'type'      => $type,
                    'module'    => $module
                );
            }
        }
        $this->journal2->settings->set('config_top_modules', $this->render());

        /* render bottom modules */
        $this->data['modules'] = array();
        foreach ($module_data_bottom as $module) {
            $type = $module['code'];
            $id = $module['module_id'];
            $module = $this->getChild('module/' . $module['code'], $module['setting']);
            if ($module) {
                $this->data['modules'][] = array(
                    'module_id' => $id,
                    'type'      => $type,
                    'module'    => $module
                );
            }
        }
        $this->journal2->settings->set('config_bottom_modules', $this->render());

        $this->template = 'journal2/common/footer_modules.tpl';

        /* render header modules */
        $this->data['modules'] = array();
        foreach ($module_data_header as $module) {
            $type = $module['code'];
            $id = $module['module_id'];
            $module = $this->getChild('module/' . $module['code'], $module['setting']);
            if ($module) {
                $this->data['modules'][] = array(
                    'module_id' => $id,
                    'type'      => $type,
                    'module'    => $module
                );
            }
        }
        $this->journal2->settings->set('config_header_modules', $this->render());


        /* render footer modules */
        $this->data['modules'] = array();
        foreach ($module_data_footer as $module) {
            $type = $module['code'];
            $id = $module['module_id'];
            $module = $this->getChild('module/' . $module['code'], $module['setting']);
            if ($module) {
                $this->data['modules'][] = array(
                    'module_id' => $id,
                    'type'      => $type,
                    'module'    => $module
                );
            }
        }
        $this->journal2->settings->set('config_footer_modules', $this->render());
    }

    private function renderModulesOc2($layout_id) {
        $modules = array();
        $modules = array_merge($modules, $this->model_design_layout->getLayoutModules($layout_id, 'top'));
        $modules = array_merge($modules, $this->model_design_layout->getLayoutModules($layout_id, 'bottom'));
        $modules = array_merge($modules, $this->model_design_layout->getLayoutModules($layout_id, 'header'));
        $modules = array_merge($modules, $this->model_design_layout->getLayoutModules($layout_id, 'footer'));

        $module_data_top = array();
        $module_data_bottom = array();
        $module_data_header = array();
        $module_data_footer = array();

        foreach ($modules as $module) {
            $part = explode('.', $module['code']);

            if (strpos($module['code'], 'journal2_') === 0 && $this->config->get($part[0] . '_' . $module['layout_module_id'] . '_status')) {
                $output = $this->load->controller('module/' . $part[0], array(
                    'position'  => $module['position'],
                    'layout_id' => $layout_id,
                    'module_id' => $part[1]
                ));
                if ($output) {
                    $var = 'module_data_' . $module['position'];
                    array_push($$var, array(
                        'module_id' => $part[1],
                        'type' => $part[0],
                        'module' => $output
                    ));
                }
            }
        }

        $this->template = 'journal2/common/modules.tpl';

        /* render top modules */
        if ($module_data_top) {
            $this->data['modules'] = $module_data_top;
            $this->journal2->settings->set('config_top_modules', $this->render());
        }

        /* render bottom modules */
        if ($module_data_bottom) {
            $this->data['modules'] = $module_data_bottom;
            $this->journal2->settings->set('config_bottom_modules', $this->render());
        }

        $this->template = 'journal2/common/footer_modules.tpl';

        /* render header modules */
        if ($module_data_header) {
            $this->data['modules'] = $module_data_header;
            $this->journal2->settings->set('config_header_modules', $this->render());
        }

        /* render footer modules */
        if ($module_data_footer) {
            $this->data['modules'] = $module_data_footer;
            $this->journal2->settings->set('config_footer_modules', $this->render());
        }
    }
}
?>