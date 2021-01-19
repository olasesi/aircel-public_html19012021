<?php
class ControllerJournal2Common extends Controller {

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

    public function index() {
        $this->load->language('journal2/common');
        $this->helpers();
        $this->languageVars();
        $this->adminWarnings();
    }

    private function helpers() {
        $this->journal2->is_https = isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'));
        $this->journal2->store_url = $this->journal2->is_https ? $this->config->get('config_ssl') : $this->config->get('config_url');
        $this->journal2->img_url = $this->journal2->store_url . 'image/';
    }

    private function languageVars() {
        $this->journal2->settings->set('welcome_text', sprintf($this->language->get('j2_welcome_text'), Journal2Utils::link('account/login', '', 'SSL'), Journal2Utils::link('account/register', '', 'SSL')));
        $this->journal2->settings->set('logged_in_text', sprintf($this->language->get('j2_logged_in_text'), Journal2Utils::link('account/account', '', 'SSL'), $this->customer->getFirstName(), Journal2Utils::link('account/logout', '', 'SSL')));
    }

    private function adminWarnings() {
        if (version_compare(VERSION, '2.1', '<')) {
            $this->load->library('user');
        }

        if (version_compare(VERSION, '2.2', '>=')) {
            $this->user = new \Cart\User($this->registry);
        } else {
            $this->user = new User($this->registry);
        }

        if ($this->user->isLogged()) {
            if ($this->journal2->is_https) {
                $current_url = parse_url('https://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI']);
                $config_url = parse_url(HTTPS_SERVER);
            } else {
                $current_url = parse_url('http://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI']);
                $config_url = parse_url(HTTP_SERVER);
            }

            if ($config_url['scheme'] . $config_url['host'] !== $current_url['scheme'] . $current_url['host']) {
                $this->journal2->admin_warnings = 'Store address conflict!';
            }
        }
    }

}
