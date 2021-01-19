<?php
require_once DIR_SYSTEM . 'journal2/classes/journal2_newsletter.php';

class ControllerModuleJournal2Newsletter extends Controller {

    private static $CACHEABLE = null;
    private $google_fonts = array();

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

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.newsletter_cache');
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

        $this->data['css'] = '';

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_newsletter_' . $setting['module_id'], implode('; ', $css));
            $this->journal2->settings->set('module_journal2_newsletter_' . $setting['module_id'] . '_classes', implode(' ', $this->data['disable_on_classes']));
            $this->journal2->settings->set('module_journal2_newsletter_' . $setting['module_id'] . '_video', Journal2Utils::getVideoBackgroundSettings(Journal2Utils::getProperty($module_data, 'video_background.value.text')));

            /* inner css */
            $css = array();
            if (Journal2Utils::getProperty($module_data, 'fullwidth')) {
                $css[] = 'max-width: 100%';
                $css[] = 'padding-left: ' . $padding;
                $css[] = 'padding-right: ' . $padding;
            } else {
                $css[] = 'max-width: ' . $this->journal2->settings->get('site_width', 1024) . 'px';
            }
            $css = array_merge($css, Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'module_shadow')));
            $this->data['css'] = implode('; ', $css);
        }

        /* border */
        if (Journal2Utils::getProperty($module_data, 'module_border')) {
            $border = implode('; ', Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'module_border')));
            $this->data['css'] = isset($this->data['css']) ? ($this->data['css'] . '; ' . $border) : $border;
        }

        $cache_property = "module_journal_carousel_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $this->data['module'] = mt_rand();
            $this->data['module_id'] = $setting['module_id'];

            $this->data['text_class'] = Journal2Utils::getProperty($module_data, 'text_position', 'left');

			if ($this->informationId()) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->informationId());

				if ($information_info) {
					$this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->informationId(), true), $information_info['title'], $information_info['title']);
				} else {
					$this->data['text_agree'] = '';
				}
			} else {
				$this->data['text_agree'] = '';
			}

            /* heading title */
            $this->data['heading_title'] = Journal2Utils::getProperty($module_data, 'module_title.value.' . $this->config->get('config_language_id'), '');
            /* text */
            $css = array();
            $this->data['module_text'] = Journal2Utils::getProperty($module_data, 'module_text.value.' . $this->config->get('config_language_id'), '');
            $fv = Journal2Utils::getProperty($module_data, 'module_text_font.value.v');
            $fg = false;
            if (Journal2Utils::getProperty($module_data, 'module_text_font.value.font_type') === 'google') {
                $fg = true;
                $font_name = Journal2Utils::getProperty($module_data, 'module_text_font.value.font_name');
                $font_subset = Journal2Utils::getProperty($module_data, 'module_text_font.value.font_subset');
                $font_weight = Journal2Utils::getProperty($module_data, 'module_text_font.value.font_weight');
                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                $this->google_fonts[] = array(
                    'name'  => $font_name,
                    'subset'=> $font_subset,
                    'weight'=> $font_weight
                );
                $weight = filter_var(Journal2Utils::getProperty($module_data, 'module_text_font.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                $css[] = 'font-weight: ' . ($weight ? $weight : 400);
                $css[] = "font-family: '" . Journal2Utils::getProperty($module_data, 'module_text_font.value.font_name') . "'";
            }
            if (Journal2Utils::getProperty($module_data, 'module_text_font.value.font_type') === 'system') {
                if ($fv !== '2') {
                    $css[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, 'module_text_font.value.font_weight');
                }
                $css[] = 'font-family: ' . Journal2Utils::getProperty($module_data, 'module_text_font.value.font_family');
            }
            if ($fv === '2') {
                if (!$fg && ($value = Journal2Utils::getProperty($module_data, 'module_text_font.value.font_weight'))) {
                    $css[] = 'font-weight: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'module_text_font.value.font_style')) {
                    $css[] = 'font-style: ' . $value;
                }
                $value = Journal2Utils::getProperty($module_data, 'module_text_font.value.font_size');
                if (Journal2Utils::getDevice() === 'phone') {
                    $value2 = Journal2Utils::getProperty($module_data, 'module_text_font.value.font_size_mobile');
                    if ($value2 && $value2 !== '---') {
                        $value = $value2;
                    }
                }
                if ($value && $value !== '---') {
                    $css[] = 'font-size: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'module_text_font.value.text_transform')) {
                    $css[] = 'text-transform: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'module_text_font.value.letter_spacing')) {
                    $css[] = 'letter-spacing: ' . $value . 'px';
                }
            } else {
                if (Journal2Utils::getProperty($module_data, 'module_text_font.value.font_type') !== 'none') {
                    $css[] = 'font-size: ' . Journal2Utils::getProperty($module_data, 'module_text_font.value.font_size');
                    $css[] = 'font-style: ' . Journal2Utils::getProperty($module_data, 'module_text_font.value.font_style');
                    $css[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, 'module_text_font.value.text_transform');
                    if ($letter_spacing = Journal2Utils::getProperty($module_data, 'module_text_font.value.letter_spacing')) {
                        $css[] = 'letter-spacing: ' . $letter_spacing . 'px';
                    }
                }
            }
            if (Journal2Utils::getProperty($module_data, 'module_text_font.value.color.value.color')) {
                $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'module_text_font.value.color.value.color'));
            }
            $this->data['font_css'] = implode('; ', $css);

            /* input */
            $this->data['input_placeholder'] = Journal2Utils::getProperty($module_data, 'input_placeholder.value.' . $this->config->get('config_language_id'));
            $input_style = array();
            if (Journal2Utils::getProperty($module_data, 'input_height')) {
                $input_style[] = 'height: ' . Journal2Utils::getProperty($module_data, 'input_height') . 'px';
            }
            $input_field_style = array();
            if (Journal2Utils::getProperty($module_data, 'input_bg_color.value.color')) {
                $input_field_style[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'input_bg_color.value.color'));
            }
            if (Journal2Utils::getProperty($module_data, 'input_border')) {
                $input_field_style = array_merge($input_field_style, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'input_border')));
            }
            if (Journal2Utils::getProperty($module_data, 'input_font.value.font_type') === 'google') {
                $font_name = Journal2Utils::getProperty($module_data, 'input_font.value.font_name');
                $font_subset = Journal2Utils::getProperty($module_data, 'input_font.value.font_subset');
                $font_weight = Journal2Utils::getProperty($module_data, 'input_font.value.font_weight');
                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                $this->google_fonts[] = array(
                    'name'  => $font_name,
                    'subset'=> $font_subset,
                    'weight'=> $font_weight
                );
                $weight = filter_var(Journal2Utils::getProperty($module_data, 'input_font.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                $input_field_style[] = 'font-weight: ' . ($weight ? $weight : 400);
                $input_field_style[] = "font-family: '" . Journal2Utils::getProperty($module_data, 'input_font.value.font_name') . "'";
            }
            if (Journal2Utils::getProperty($module_data, 'input_font.value.font_type') === 'system') {
                $input_field_style[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, 'input_font.value.font_weight');
                $input_field_style[] = 'font-family: ' . Journal2Utils::getProperty($module_data, 'input_font.value.font_family');
            }
            if (Journal2Utils::getProperty($module_data, 'input_font.value.font_type') !== 'none') {
                $input_field_style[] = 'font-size: ' . Journal2Utils::getProperty($module_data, 'input_font.value.font_size');
                $input_field_style[] = 'font-style: ' . Journal2Utils::getProperty($module_data, 'input_font.value.font_style');
                $input_field_style[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, 'input_font.value.text_transform');
            }
            if (Journal2Utils::getProperty($module_data, 'input_font.value.color.value.color')) {
                $input_field_style[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'input_font.value.color.value.color'));
            }
            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'input_shadow')) as $sett) {
               $input_style[] = $sett;
            }
            $this->data['input_style'] = implode('; ', $input_style);
            $this->data['input_field_style'] = implode('; ', $input_field_style);

            /* submit */
            $this->data['button_text'] = Journal2Utils::getProperty($module_data, 'button_text.value.' . $this->config->get('config_language_id'), '');
            $this->data['button_icon'] = Journal2Utils::getIconOptions2(Journal2Utils::getProperty($module_data, 'button_icon'));
            $button_style = array();
            if (Journal2Utils::getProperty($module_data, 'button_offset_top')) {
                $button_style[] = 'top: ' . Journal2Utils::getProperty($module_data, 'button_offset_top') . 'px';
            }
            if (Journal2Utils::getProperty($module_data, 'button_offset_left')) {
                $button_style[] = 'right: ' . Journal2Utils::getProperty($module_data, 'button_offset_left') . 'px';
            }
            if (Journal2Utils::getProperty($module_data, 'button_border')) {
                $button_style = array_merge($button_style, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'button_border')));
            }
            if (Journal2Utils::getProperty($module_data, 'button_font')) {
                $button_style = array_merge($button_style, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'button_border')));
            }

            $fv = Journal2Utils::getProperty($module_data, 'button_font.value.v');
            $fg = false;

            if (Journal2Utils::getProperty($module_data, 'button_font.value.font_type') === 'google') {
                $fg = true;
                $font_name = Journal2Utils::getProperty($module_data, 'button_font.value.font_name');
                $font_subset = Journal2Utils::getProperty($module_data, 'button_font.value.font_subset');
                $font_weight = Journal2Utils::getProperty($module_data, 'button_font.value.font_weight');
                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                $this->google_fonts[] = array(
                    'name'  => $font_name,
                    'subset'=> $font_subset,
                    'weight'=> $font_weight
                );
                $weight = filter_var(Journal2Utils::getProperty($module_data, 'button_font.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                $button_style[] = 'font-weight: ' . ($weight ? $weight : 400);
                $button_style[] = "font-family: '" . Journal2Utils::getProperty($module_data, 'button_font.value.font_name') . "'";
            }
            if (Journal2Utils::getProperty($module_data, 'button_font.value.font_type') === 'system') {
                if ($fv !== '2') {
                    $button_style[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, 'button_font.value.font_weight');
                }
                $button_style[] = 'font-family: ' . Journal2Utils::getProperty($module_data, 'button_font.value.font_family');
            }
            if ($fv === '2') {
                if (!$fg && ($value = Journal2Utils::getProperty($module_data, 'button_font.value.font_weight'))) {
                    $css[] = 'font-weight: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'button_font.value.font_style')) {
                    $css[] = 'font-style: ' . $value;
                }
                $value = Journal2Utils::getProperty($module_data, 'button_font.value.font_size');
                if (Journal2Utils::getDevice() === 'phone') {
                    $value2 = Journal2Utils::getProperty($module_data, 'button_font.value.font_size_mobile');
                    if ($value2 && $value2 !== '---') {
                        $value = $value2;
                    }
                }
                if ($value && $value !== '---') {
                    $css[] = 'font-size: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'button_font.value.text_transform')) {
                    $css[] = 'text-transform: ' . $value;
                }
                if ($value = Journal2Utils::getProperty($module_data, 'button_font.value.letter_spacing')) {
                    $css[] = 'letter-spacing: ' . $value . 'px';
                }
            } else {
                if (Journal2Utils::getProperty($module_data, 'button_font.value.font_type') !== 'none') {
                    $button_style[] = 'font-size: ' . Journal2Utils::getProperty($module_data, 'button_font.value.font_size');
                    $button_style[] = 'font-style: ' . Journal2Utils::getProperty($module_data, 'button_font.value.font_style');
                    $button_style[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, 'button_font.value.text_transform');
                }
            }
            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'button_shadow')) as $sett) {
                $button_style[] = $sett;
            }
            foreach (Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'button_bg_image')) as $sett) {
                $button_style[] = $sett;
            }
            $this->data['global_style'] = array();
            if ($color = Journal2Utils::getProperty($module_data, 'button_font_color_hover.value.color')) {
                $this->data['global_style'][] = "#journal-newsletter-{$this->data['module']} .newsletter-button:hover { color: " . $color . " !important}";
            }
            if ($color = Journal2Utils::getProperty($module_data, 'button_border_hover.value.color')) {
                $this->data['global_style'][] = "#journal-newsletter-{$this->data['module']} .newsletter-button:hover { border-color: " . $color . " !important}";
            }
            foreach (Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'button_bg_image_hover')) as $sett) {
                $this->data['global_style'][] = "#journal-newsletter-{$this->data['module']} .newsletter-button:hover {" . $sett . " !important}";
            }
            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'button_shadow_active')) as $sett) {
                $this->data['global_style'][] = "#journal-newsletter-{$this->data['module']} a.newsletter-button:active {" . $sett . " !important; }";
            }
            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($module_data, 'button_shadow_hover')) as $sett) {
                $this->data['global_style'][] = "#journal-newsletter-{$this->data['module']} .newsletter-button:hover {" . $sett . " !important; }";
            }
            if (Journal2Utils::getProperty($module_data, 'button_font.value.color.value.color')) {
                $button_style[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'button_font.value.color.value.color'));
            }
            if (Journal2Utils::getProperty($module_data, 'button_background.value.color')) {
                $button_style[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'button_background.value.color'));
            }
            $this->data['button_style'] = implode('; ', $button_style);

            /* background */
            $module_css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'module_background'));
            if ($padding = Journal2Utils::getProperty($module_data, 'module_padding_top')) {
                $module_css[] = 'padding-top: ' . $padding . 'px';
            }
            if ($padding = Journal2Utils::getProperty($module_data, 'module_padding_right')) {
                $module_css[] = 'padding-right: ' . $padding . 'px';
            }
            if ($padding = Journal2Utils::getProperty($module_data, 'module_padding_bottom')) {
                $module_css[] = 'padding-bottom: ' . $padding . 'px';
            }
            if ($padding = Journal2Utils::getProperty($module_data, 'module_padding_left')) {
                $module_css[] = 'padding-left: ' . $padding . 'px';
            }
            $this->data['module_css'] = implode('; ', $module_css);

            $this->template = 'journal2/module/newsletter.tpl';

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

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

    public function subscribe() {
        $response = array();

		if ($this->informationId()) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->informationId());
		} else {
			$information_info = null;
		}

        if ($information_info && (!isset($this->request->post['agree']) || !$this->request->post['agree'])) {
			$response['status'] = 'error';
			$response['message'] = sprintf($this->language->get('error_agree'), $information_info['title']);
		} else if ($this->validateEmail()) {
            $newsletter = new Journal2Newsletter($this->registry, $this->request->post['email']);
            if ($newsletter->isSubscribed()) {
                $response['status'] = 'error';
                $response['unsubscribe'] = 1;
                $response['message'] = $this->journal2->settings->get('newsletter_confirm_unsubscribe_message', 'Already subscribed. Unsubscribe?');
            } else {
                $newsletter->subscribe();
                // Clear Thinking: MailChimp Integration
				if (version_compare(VERSION, '2.1', '<')) $this->load->library('mailchimp_integration');
				$mailchimp_integration = new MailChimp_Integration($this->registry);
				$mailchimp_integration->send(array('newsletter' => 1, 'email' => $this->request->post['email'], 'customer_id' => $this->customer->getId()));
				// end
                $response['status'] = 'success';
                $response['message'] = $this->journal2->settings->get('newsletter_subscribed_message', 'Thank you for subscribing on our newsletter.');
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = $this->journal2->settings->get('newsletter_invalid_email_message', 'Invalid E-Mail.');
        }
        $this->response->setOutput(json_encode($response));
    }

    public function unsubscribe() {
        $response = array();
        if ($this->validateEmail()) {
            $newsletter = new Journal2Newsletter($this->registry, $this->request->post['email']);
            if ($newsletter->isSubscribed()) {
                $newsletter->unsubscribe();
                // Clear Thinking: MailChimp Integration
				if (version_compare(VERSION, '2.1', '<')) $this->load->library('mailchimp_integration');
				$mailchimp_integration = new MailChimp_Integration($this->registry);
				$mailchimp_integration->send(array('newsletter' => 0, 'email' => $this->request->post['email'], 'customer_id' => $this->customer->getId()));
				// end
                $response['status'] = 'success';
                $response['message'] = $this->journal2->settings->get('newsletter_unsubscribed_message', 'You have been unsubscribed from our newsletter.');
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Your E-Mail was not found.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = $this->journal2->settings->get('newsletter_invalid_email_message', 'Invalid E-Mail.');
        }
        $this->response->setOutput(json_encode($response));
    }

    private function validateEmail() {
        return isset($this->request->post['email']) && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email']);
    }

    private function informationId() {
    	if (!$this->journal2->settings->get('newsletter_privacy', '1')) {
    		return 0;
		}

		return (int)$this->journal2->settings->get('newsletter_privacy_information');
	}
}
