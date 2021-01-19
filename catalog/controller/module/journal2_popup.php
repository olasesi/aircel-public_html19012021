<?php
/* @property ModelCatalogManufacturer model_catalog_manufacturer */
/* @property ModelCatalogCategory model_catalog_category */
class ControllerModuleJournal2Popup extends Controller {

    private static $CACHEABLE = null;
    private $google_fonts = array();
    private $contact_error = array();

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

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');
        $this->load->model('journal2/menu');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.popup_cache');
        }
    }

    public function index($setting) {
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }

        Journal2::startTimer(get_class($this));

        /* get module data from db */
        $module_data = $this->model_journal2_module->getModule($setting['module_id']);
        if (!$module_data || !isset($module_data['module_data']) || !$module_data['module_data']) return;
        $module_data = $module_data['module_data'];

        /* device detection */
        $this->data['disable_on_classes'] = array();

        if ($this->journal2->settings->get('responsive_design')) {
            $device = Journal2Utils::getDevice();

            if ($setting['position'] === 'column_left' || $setting['position'] === 'column_right') {
                if ($device === 'phone') {
                    return;
                }

                if ($device === 'tablet') {
                    if ($setting['position'] === 'column_left' && $this->journal2->settings->get('left_column_on_tablet', 'on') !== 'on') {
                        return;
                    }

                    if ($setting['position'] === 'column_right' && $this->journal2->settings->get('right_column_on_tablet', 'on') !== 'on') {
                        return;
                    }
                }
            }

            if (Journal2Utils::getProperty($module_data, 'enable_on_phone', '1') == '0') {
                if ($device === 'phone') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-phone';
                }
            }

            if (Journal2Utils::getProperty($module_data, 'enable_on_tablet', '1') == '0') {
                if ($device === 'tablet') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-tablet';
                }
            }

            if (Journal2Utils::getProperty($module_data, 'enable_on_desktop', '1') == '0') {
                if ($device === 'desktop') {
                    return;
                } else {
                    $this->data['disable_on_classes'][] = 'hide-on-desktop';
                }
            }
        }

        if (!version_compare(VERSION, '2.0.2', '<') && $this->config->get('config_google_captcha_status')) {
            $this->data['site_key'] = $this->config->get('config_google_captcha_public');
        } else {
            $this->data['site_key'] = '';
        }

        $cache_property = "module_journal_popup_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        $this->data['cookie_name'] = 'popup-' . Journal2Utils::getProperty($module_data, 'do_not_show_again_cookie');
        $this->data['show_only_once'] = Journal2Utils::getProperty($module_data, 'show_only_once', '0');


        if ($this->data['is_ajax'] = isset($setting['position']) && $setting['position'] === 'ajax') {
            $cache = null;
        } else {
            if (isset($this->request->cookie[$this->data['cookie_name']])) {
                return;
            }

            $this->journal2->html_classes->addClass('noscroll');

            if ($this->data['show_only_once']) {
                setcookie($this->data['cookie_name'], 1, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
            }
        }

        if ($cache === null || self::$CACHEABLE !== true) {
            /* set global module properties */
            $this->data['module'] = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            /* dimensions */
            $width = Journal2Utils::getProperty($module_data, 'width', 600);
            $header_height = Journal2Utils::getProperty($module_data, 'title_height', 40);
            $footer_height = Journal2Utils::getProperty($module_data, 'footer_height', 60);
            $newsletter_height = Journal2Utils::getProperty($module_data, 'newsletter_height', 80);
            $content_height = Journal2Utils::getProperty($module_data, 'height', 400);
            $height = $header_height + $footer_height + $content_height;

            /* newsletter */
            if (Journal2Utils::getProperty($module_data, 'newsletter')) {
                $this->data['newsletter'] = $this->getChild('module/journal2_newsletter', array (
                    'module_id' => Journal2Utils::getProperty($module_data, 'newsletter_id'),
                    'layout_id' => -1,
                    'position'  => 'footer'
                ));
                $this->data['newsletter_style'] = array();
                $color = Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'newsletter_bg_color.value.color'));
                if ($color) {
                    $this->data['newsletter_style'][] = "background-color: {$color}";
                }
                if ($newsletter_height) {
                    $this->data['newsletter_style'][] = "height: {$newsletter_height}px";
                    $height += $newsletter_height;
                }
            } else {
                $this->data['newsletter'] = false;
            }

            /* header */
            $this->data['close_button'] = (int)Journal2Utils::getProperty($module_data, 'close_button');
            $this->data['title'] = Journal2Utils::getProperty($module_data, 'title.value.' . $this->config->get('config_language_id'));
            $this->data['header_style'] = array();
            $color = Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'title_bg_color.value.color'));
            if ($color) {
                $this->data['header_style'][] = "background-color: {$color}";
            }
            if ($header_height) {
                $this->data['header_style'][] = "height: {$header_height}px";
            }
            $this->data['header_style'] = array_merge($this->data['header_style'], $this->getFontSettings($module_data, 'title_font'));
            if (!$this->data['title']) {
                $height -= $header_height;
            }
			$this->data['header_style'][] = "text-align: " . Journal2Utils::getProperty($module_data, 'title_align');

            /* content */
            $this->data['type'] = Journal2Utils::getProperty($module_data, 'type', 'text');
            if ($this->data['type'] === 'contact') {
                $this->data['is_j2_popup'] = true;
                $this->data['action'] = $this->url->link('information/contact');
                $this->data['captcha'] = '';
                /* reset oc variables */
                foreach (array('content_top', 'name', 'error_name', 'email', 'error_email', 'enquiry', 'error_enquiry', 'captcha', 'error_captcha') as $var) {
                    $this->data[$var] = false;
                }
                /* load language */
                $this->language->load('information/contact');
                $this->data['heading_title'] = $this->language->get('heading_title');
                $this->data['entry_name'] = $this->language->get('entry_name');
                $this->data['entry_email'] = $this->language->get('entry_email');
                $this->data['entry_enquiry'] = $this->language->get('entry_enquiry');
                $this->data['entry_captcha'] = $this->language->get('entry_captcha');
                $this->data['product_id'] = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : null;
                $this->data['button_submit'] = $this->getButtonStyle($module_data, 'button_submit', 'Submit');

                /* captcha */
                if (version_compare(VERSION, '2.1', '>=')) {
                    if (!isset($this->request->get['route'])) {
                        $this->request->get['route'] = 'common/home';
                    }
                    if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
                        $this->data['captcha'] = $this->load->controller('captcha/' . $this->config->get('config_captcha'), $this->error);
                    } else {
                        $this->data['captcha'] = '';
                    }
                }
            } else {
                $this->data['content'] = Journal2Utils::getProperty($module_data, 'text.' . $this->config->get('config_language_id'), '&nbsp;');
                $this->data['content_style'] = array();
                $this->data['content_style'][] = "height: {$content_height}px";
                if ($padding = Journal2Utils::getProperty($module_data, 'padding')) {
                    $this->data['content_style'][] = "padding: {$padding}px";
                }
                if (!$this->data['content']) {
                    $height -= $content_height;
                }
                if (Journal2Utils::getProperty($module_data, 'content_overflow', '1') == '1') {
                    $this->data['content_overflow'] = 'overflow-on';
                } else {
                    $this->data['content_overflow'] = '';
                }
            }

            /* footer */
            $this->data['footer'] = false;
            $this->data['footer_style'] = array();
            $color = Journal2Utils::getProperty($module_data, 'footer_bg_color.value.color');
            if ($color) {
                $this->data['footer_style'][] = "background-color: " . Journal2Utils::getColor($color);
            }
            if ($footer_height) {
                $this->data['footer_style'][] = "height: {$footer_height}px";
            }
            if ($this->data['type'] === 'contact') {
                $this->data['footer_style'][] = "text-align: " . Journal2Utils::getProperty($module_data, 'button_submit_position');;
            }
            $this->data['button_1'] = $this->getButtonStyle($module_data, 'button_1');
            $this->data['button_2'] = $this->getButtonStyle($module_data, 'button_2');
            $this->data['do_not_show_again'] = Journal2Utils::getProperty($module_data, 'do_not_show_again', '0');
            $this->data['do_not_show_again_text'] = Journal2Utils::getProperty($module_data, 'do_not_show_again_text.value.' . $this->config->get('config_language_id'), "Don't show again.");
            $this->data['do_not_show_again_font'] = $this->getFontSettings($module_data, 'do_not_show_again_font');
            $this->data['footer_buttons_class'] = '';
            if ($this->data['button_1']['status'] || $this->data['button_1']['status']) {
                $this->data['footer'] = true;
                $this->data['footer_buttons_class'] = 'has-btn';
            }
            if ($this->data['do_not_show_again']) {
                $this->data['footer'] = true;
            }

            /* global styles */
            $this->data['global_style'] = array();
            $this->data['global_style'][] = "width: {$width}px";
            if ($this->data['type'] === 'text') {
                $this->data['global_style'][] = "height: {$height}px";
            }
            $this->data['global_style'] = array_merge($this->data['global_style'], Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background')));

            /* timers */
            $this->data['open_after'] = (int)Journal2Utils::getProperty($module_data, 'open_after', '0');
            $this->data['close_after'] = (int)Journal2Utils::getProperty($module_data, 'close_after', '0');

            /* render*/
            $this->template = 'journal2/module/popup.tpl';

            if (self::$CACHEABLE === true) {
                $html = Minify_HTML::minify($this->render(), array(
                    'xhtml' => false,
                    'jsMinifier' => 'j2_js_minify'
                ));
                $this->journal2->cache->set($cache_property, $html);
                $this->journal2->cache->set($cache_property . '_fonts', json_encode($this->google_fonts));
            }
        } else {
            if ($fonts = $this->journal2->cache->get($cache_property . '_fonts')) {
                $fonts = json_decode($fonts, true);
                if (is_array($fonts)) {
                    foreach ($fonts as $font) {
                        $this->journal2->google_fonts->add($font['name'], $font['subset'], $font['weight']);
                    }
                }
            }
            $this->template = 'journal2/cache/cache.tpl';
            $this->data['cache'] = $cache;
        }

        $this->document->addScript('catalog/view/theme/journal2/lib/jqueryc/jqueryc.js');

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

    public function show() {
        echo $this->getChild('module/journal2_popup', array (
            'module_id' => $this->request->get['module_id'],
            'layout_id' => -1,
            'position'  => 'ajax'
        ));
    }

    public function contact() {
        $data = array();
        $this->language->load('information/contact');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $from =  '';
            if (isset($this->request->post['product_id'])) {
                $from = $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']);
            } else if (isset($this->request->post['url'])) {
                $from = $this->request->post['url'];
            }
            if ($from) {
                $from = PHP_EOL . PHP_EOL . 'Sent from <a href="' . $from . '">' . $from . '</a>';
            }

            if (version_compare(VERSION, '3', '>=')) {
                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            } else if (version_compare(VERSION, '2', '>=')) {
                if (version_compare(VERSION, '2.0.2', '<')) {
                    $mail = new Mail($this->config->get('config_mail'));
                } else {
                    $mail = new Mail();
                    $mail->protocol = $this->config->get('config_mail_protocol');
                    $mail->parameter = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
                }
            } else {
                $mail = new Mail();
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
            }

            $mail->setTo($this->config->get('config_email'));
            $mail->setFrom($this->request->post['email']);
            $mail->setSender($this->request->post['name']);
            $mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));
            $mail->setText(strip_tags(html_entity_decode($this->request->post['enquiry'] . $from, ENT_QUOTES, 'UTF-8')));
            $mail->send();

            // Send to additional alert emails
            $emails = explode(',', $this->config->get('config_mail_alert_email'));

            foreach ($emails as $email) {
                $email = trim($email);

                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }

            $data['status'] = 'success';
            $data['message'] = strip_tags(html_entity_decode($this->language->get(version_compare(VERSION, '2', '>=') ? 'text_success' : 'text_message'), ENT_QUOTES, 'UTF-8'));
        } else {
            $this->session->data['captcha'] = md5(mt_rand());
            $data['status'] = 'error';
            $data['error'] = $this->contact_error;
        }
        $this->response->setOutput(json_encode($data));
    }

    private function validate() {
        if (!utf8_strlen($this->request->post['name'])) {
            $this->contact_error['name'] = $this->language->get('error_name');
        }

        if (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
            $this->contact_error['email'] = $this->language->get('error_email');
        }

        if (!utf8_strlen($this->request->post['enquiry'])) {
            $this->contact_error['enquiry'] = $this->language->get('error_enquiry');
        }

        if (version_compare(VERSION, '2.0.2', '<')) {
            if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
                $this->contact_error['captcha'] = $this->language->get('error_captcha');
            }
        } else if (version_compare(VERSION, '2.1', '<')){
            if ($this->config->get('config_google_captcha_status')) {
                $recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->config->get('config_google_captcha_secret')) . '&response=' . $this->request->post['g-recaptcha-response'] . '&remoteip=' . $this->request->server['REMOTE_ADDR']);

                $recaptcha = json_decode($recaptcha, true);

                if (!$recaptcha['success']) {
                    $this->contact_error['g-recaptcha'] = $this->language->get('error_captcha');
                }
            }
        } else {
            if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
                $captcha_error = $this->load->controller('captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha_error) {
                    $this->contact_error['captcha'] = $captcha_error;
                    $this->contact_error['g-recaptcha'] = $captcha_error;
                }
            }
        }

        return !$this->contact_error;
    }

    private function getFontSettings($module_data, $property) {
        $css = array();

        $fv = Journal2Utils::getProperty($module_data, $property . '.value.v');
        $fg = false;

        if (Journal2Utils::getProperty($module_data, $property . '.value.font_type') === 'google') {
            $fg = true;
            $font_name = Journal2Utils::getProperty($module_data, $property . '.value.font_name');
            $font_subset = Journal2Utils::getProperty($module_data, $property . '.value.font_subset');
            $font_weight = Journal2Utils::getProperty($module_data, $property . '.value.font_weight');
            $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
            $this->google_fonts[] = array(
                'name'  => $font_name,
                'subset'=> $font_subset,
                'weight'=> $font_weight
            );
            $weight = filter_var(Journal2Utils::getProperty($module_data, $property . '.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
            $css[] = 'font-weight: ' . ($weight ? $weight : 400);
            $css[] = "font-family: '" . Journal2Utils::getProperty($module_data, $property . '.value.font_name') . "'";
        }
        if (Journal2Utils::getProperty($module_data, $property . '.value.font_type') === 'system') {
            if ($fv !== '2') {
                $css[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_weight');
            }
            $css[] = 'font-family: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_family');
        }
        if ($fv === '2') {
            if (!$fg && ($value = Journal2Utils::getProperty($module_data, $property . '.value.font_weight'))) {
                $css[] = 'font-weight: ' . $value;
            }
            if ($value = Journal2Utils::getProperty($module_data, $property . '.value.font_style')) {
                $css[] = 'font-style: ' . $value;
            }
            $value = Journal2Utils::getProperty($module_data, $property . '.value.font_size');
            if (Journal2Utils::getDevice() === 'phone') {
                $value2 = Journal2Utils::getProperty($module_data, $property . '.value.font_size_mobile');
                if ($value2 && $value2 !== '---') {
                    $value = $value2;
                }
            }
            if ($value && $value !== '---') {
                $css[] = 'font-size: ' . $value;
            }
            if ($value = Journal2Utils::getProperty($module_data, $property . '.value.text_transform')) {
                $css[] = 'text-transform: ' . $value;
            }
            if ($value = Journal2Utils::getProperty($module_data, $property . '.value.letter_spacing')) {
                $css[] = 'letter-spacing: ' . $value . 'px';
            }
        } else {
            if (Journal2Utils::getProperty($module_data, $property . '.value.font_type') !== 'none') {
                $css[] = 'font-size: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_size');
                $css[] = 'font-style: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_style');
                $css[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, $property . '.value.text_transform');
            }
        }
        if (Journal2Utils::getProperty($module_data, $property . '.value.color.value.color')) {
            $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, $property . '.value.color.value.color'));
        }
        return $css;
    }

    private function getButtonStyle($module_data, $property, $default = '') {
        $style = $this->getFontSettings($module_data, $property . '_font');
        if ($color = Journal2Utils::getProperty($module_data, $property . '_bgcolor.value.color')) {
            $style[] = 'background-color: ' . Journal2Utils::getColor($color);
        }

        $hover_style = array();
        if ($color = Journal2Utils::getProperty($module_data, $property . '_hover_bgcolor.value.color')) {
            $hover_style[] = 'background-color: ' . Journal2Utils::getColor($color) . ' !important';
        }

        return array(
            'status'        => Journal2Utils::getProperty($module_data, $property),
            'text'          => Journal2Utils::getProperty($module_data, $property . '_text.value.' . $this->config->get('config_language_id'), $default),
            'icon'          => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($module_data, $property . '_icon')),
            'icon_position' => Journal2Utils::getProperty($module_data, $property . '_icon_position', 'right'),
            'link'          => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($module_data, $property . '_link')),
            'target'        => Journal2Utils::getProperty($module_data, $property . '_new_window') ? 'target="_blank"' : '',
            'style'         => implode('; ', $style),
            'hover_style'   => implode('; ', $hover_style)
        );
    }

}
