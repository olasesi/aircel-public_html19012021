<?php
class ControllerModuleJournal2Slider extends Controller {

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
        $this->load->model('journal2/menu');
        $this->load->model('tool/image');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer');
        $this->load->model('catalog/information');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.slider_cache');
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

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_slider_' . $setting['module_id'], implode('; ', $css));
            $this->journal2->settings->set('module_journal2_slider_' . $setting['module_id'] . '_classes', implode(' ', $this->data['disable_on_classes']));
            $this->journal2->settings->set('module_journal2_slider_' . $setting['module_id'] . '_video', Journal2Utils::getVideoBackgroundSettings(Journal2Utils::getProperty($module_data, 'video_background.value.text')));
        }

        $this->journal2->html_classes->removeClass('backface');

        $cache_property = "module_journal_slider_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();
            $this->data['module_id'] = $setting['module_id'];
            $caption_id = 0;

            /* slider position */
            $height = Journal2Utils::getProperty($module_data, 'height', 400);
            $width = null;
            $content_padding_left = $this->journal2->settings->get('boxed_container_pl', 20);
            $content_padding_right = $this->journal2->settings->get('boxed_container_pr', 20);
            $column_left_width = $this->journal2->settings->get('left_column_width', 220);
            $column_right_width = $this->journal2->settings->get('right_column_width', 220);
            switch ($setting['position']) {
                case 'column_left':
                    $width = $column_left_width;
                    $this->data['width'] = "max-width: {$width}px";
                    $this->data['slider_class'] = 'journal-slider';
                    break;
                case 'column_right':
                    $width = $column_right_width;
                    $this->data['width'] = "max-width: {$width}px";
                    $this->data['slider_class'] = 'journal-slider';
                    break;
                case 'content_top':
                case 'content_bottom':
                    if (Journal2Utils::getDevice() === 'phone') {
                        $image = Journal2Utils::getProperty($module_data, 'slides.0.image');
                        if (is_array($image)) {
                            $image = Journal2Utils::getProperty($image, $this->config->get('config_language_id'));
                        }

                        if (is_file(DIR_IMAGE . $image)) {
                            list($iw, $ih) = getimagesize(DIR_IMAGE . $image);
                            $ratio = (float)$iw / $ih;
                            $width = $ratio * $height;
                        }
                    } else {
                        $cl = $this->journal2->settings->get('config_columns_left');
                        $cr = $this->journal2->settings->get('config_columns_right');

                        if ($this->journal2->settings->get('extended_layout')) {
                            $width = $this->journal2->settings->get('site_width', 1024) - ($cl * $column_left_width + $cl * $content_padding_left) - ($cr * $column_right_width + $cr * $content_padding_right);
                        } else {
                            $width = $this->journal2->settings->get('site_width', 1024) - ($cl * $column_left_width + $cl * $content_padding_left) - ($cr * $column_right_width + $cr * $content_padding_right);
                        }
                    }
                    $this->data['width'] = "max-width: {$width}px";
                    $this->data['slider_class'] = 'journal-slider';
                    break;
                case 'top':
                case 'bottom':
                    $width = $this->journal2->settings->get('site_width', 1024);
                    if (Journal2Utils::getProperty($module_data, 'fullwidth')) {
                        $this->data['width'] = "max-width: 100%";
                        $this->data['slider_class'] = 'journal-fullwidth-slider';
                    } else {
                        $this->data['width'] = "max-width: {$width}px";
                        $this->data['slider_class'] = 'journal-slider';
                    }
                    break;
                case 'multi_module':
                    $width = $setting['width'];
                    $height = $setting['height'];
                    $this->data['width'] = "max-width: {$width}px";
                    $this->data['slider_class'] = 'journal-slider';
                    break;
            }

            /* global style data */
            $this->data['global_style'] = array();

            $this->data['js_options'] = Journal2Utils::getProperty($module_data, 'js_options', array());
            $this->data['js_options']['startwidth'] = $width;
            $this->data['js_options']['startheight'] = $height;
            if (Journal2Utils::getProperty($module_data, 'spinner', '0') == '0') {
                $this->data['js_options']['spinner'] = '';
            }
            $this->data['js_options']['startheight'] = $height;
            $this->data['height'] = $height;

            if (Journal2Utils::getProperty($module_data, 'hidecaptionsonmobile')) {
                $this->data['js_options']['hideAllCaptionAtLimit'] = 760;
            }

            $slides = Journal2Utils::getProperty($module_data, 'slides', array());
            $slides = Journal2Utils::sortArray($slides);
            $_slides = array();

            $first = true;

            $this->data['css'] = array();

            if ($x = Journal2Utils::getProperty($module_data, 'js_options.navigationVOffset', 0)) {
                $this->data['css'][] = ".journal-slider-{$setting['module_id']} .tp-bullets { bottom: {$x}px !important }";
            }

            foreach ($slides as $slide) {
                if (isset($slide['status']) && !$slide['status']) continue;
                $slide_data = array();
                if ($first) {
                    $slide_data[] = 'data-fstransition="fade"';
                    $slide_data[] = 'data-fsslotamount="0"';
                    $slide_data[] = 'data-fsmasterspeed="0"';
                    $first = false;
                }
                $slide_data[] = 'data-transition="' . Journal2Utils::getProperty($slide, 'transition', 'fade') . '"';
                $slide_data[] = 'data-easing="' . Journal2Utils::getProperty($slide, 'easing', 'Expo.easeOut') . '"';
                $slide_data[] = 'data-masterspeed="' . Journal2Utils::getProperty($slide, 'masterspeed', 800) . '"';
                if (Journal2Utils::getProperty($slide, 'slotamount')) {
                    $slide_data[] = 'data-slotamount="' . Journal2Utils::getProperty($slide, 'slotamount') . '"';
                }
                if (Journal2Utils::getProperty($slide, 'delay')) {
                    $slide_data[] = 'data-delay="' . Journal2Utils::getProperty($slide, 'delay') . '"';
                }
                $link = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($slide, 'link'));
                if ($link) {
                    $slide_data[] = 'data-link="' . $link . '"';
                    if (Journal2Utils::getProperty($slide, 'link_new_window')) {
                        $slide_data[] = 'data-target="_blank"';
                    }
                }

				if (Journal2Utils::getProperty($slide, 'classname')) {
					$slide_data[] = 'class="' . Journal2Utils::getProperty($slide, 'classname') . '"';
				}

                $captions = Journal2Utils::getProperty($slide, 'captions', array());
                $captions = Journal2Utils::sortArray($captions);
                $_captions = array();

                foreach ($captions as $caption) {
                    if (isset($caption['status']) && !$caption['status']) continue;
                    $caption_id++;
                    $caption_data = array();
                    $caption_classes = array();

                    if (Journal2Utils::getProperty($caption, 'multilanguage_position', '0') == '0') {
                        $x = Journal2Utils::getProperty($caption, 'x', '1');
                        $y = Journal2Utils::getProperty($caption, 'y', '1');
                    } else {
                        $x = Journal2Utils::getProperty($caption, 'x_ml.value.' . $this->config->get('config_language_id'), '1');
                        $y = Journal2Utils::getProperty($caption, 'y_ml.value.' . $this->config->get('config_language_id'), '1');
                    }

                    switch (Journal2Utils::getProperty($caption, 'position', 'custom')) {
                        case 'custom':
                            $caption_data[] = 'data-x="' . $x . '"';
                            $caption_data[] = 'data-y="' . $y . '"';
                            break;
                        case 'center':
                            $caption_data[] = 'data-x="center"';
                            $caption_data[] = 'data-y="center"';
                            $caption_data[] = 'data-hoffset="' . $x . '"';
                            $caption_data[] = 'data-voffset="' . $y . '"';
                            break;
                        case 'custom2':
                            $caption_data[] = 'data-x="right"';
                            $caption_data[] = 'data-y="top"';
                            $caption_data[] = 'data-hoffset="' . $x . '"';
                            $caption_data[] = 'data-voffset="' . $y . '"';
                            break;
                    }

                    if (Journal2Utils::getProperty($caption, 'speed')) {
                        $caption_data[] = 'data-speed="' . Journal2Utils::getProperty($caption, 'speed') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'start')) {
                        $caption_data[] = 'data-start="' . Journal2Utils::getProperty($caption, 'start') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'endspeed')) {
                        $caption_data[] = 'data-endspeed="' . Journal2Utils::getProperty($caption, 'endspeed') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'end')) {
                        $caption_data[] = 'data-end="' . Journal2Utils::getProperty($caption, 'end') .'"';
                    }
                    $caption_data[] = 'data-easing="' . Journal2Utils::getProperty($caption, 'easing', 'Expo.easeOut') . '"';
                    $caption_data[] = 'data-endeasing="' . Journal2Utils::getProperty($caption, 'endeasing', 'Expo.easeOut') . '"';
                    $content = '';

                    $css = array();

                    if (Journal2Utils::getDevice() !== 'desktop' && ($ml = Journal2Utils::getProperty($caption, 'mobile_margin_left'))) {
                        $this->data['css'][] = "#jcaption-{$module}-{$caption_id} { margin-left: {$ml}px !important }";
                    }

                    switch (Journal2Utils::getProperty($caption, 'type')) {
                        case 'image':
                            $image = Journal2Utils::getProperty($caption, 'image', 'no_image.jpg');
                            if (is_array($image)) {
                                $image = Journal2Utils::getProperty($image, $this->config->get('config_language_id'));
                            }
                            if (!$image || !file_exists(DIR_IMAGE . $image)) {
                                $image = 'no_image.jpg';
                            }
                            $image = Journal2Utils::resizeImage($this->model_tool_image, $image);
                            $alt = Journal2Utils::getProperty($caption, 'caption_name');
                            $image_width = (int)Journal2Utils::getProperty($caption, 'image_width');
                            $image_height = (int)Journal2Utils::getProperty($caption, 'image_height');
                            $dim = array();
                            if ($image_width && $image_height) {
                                $dim[] = 'width="' . $image_width . '"';
                                $dim[] = 'height="' . $image_height . '"';
                            }
                            $content = '<img src="' . $image . '" alt="' . $alt .'" ' . implode(' ', $dim) . ' />';
                            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($caption, 'shadow')) as $sett) {
                                $css[] = $sett;
                            }
                            foreach (Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($caption, 'border')) as $sett) {
                                $css[] = $sett;
                            }
                            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($caption, 'shadow_active')) as $sett) {
                                $this->data['global_style'][] = "a#jcaption-{$module}-{$caption_id}:active {" . $sett . " !important; }";
                            }
                            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($caption, 'shadow_hover')) as $sett) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover {" . $sett . " !important; }";
                            }

                            break;
                        case 'text':
                            $caption_classes[] = 'tp-text';
                            $fv = Journal2Utils::getProperty($caption, 'text_font.value.v');
                            $fg = false;
                            if (Journal2Utils::getProperty($caption, 'text_font.value.font_type') === 'google') {
                                $fg = true;
                                $font_name = Journal2Utils::getProperty($caption, 'text_font.value.font_name');
                                $font_subset = Journal2Utils::getProperty($caption, 'text_font.value.font_subset');
                                $font_weight = Journal2Utils::getProperty($caption, 'text_font.value.font_weight');
                                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                                $this->google_fonts[] = array(
                                    'name'  => $font_name,
                                    'subset'=> $font_subset,
                                    'weight'=> $font_weight
                                );
                                $weight = filter_var(Journal2Utils::getProperty($caption, 'text_font.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                                $css[] = 'font-weight: ' . ($weight ? $weight : 400);
                                $css[] = "font-family: '" . Journal2Utils::getProperty($caption, 'text_font.value.font_name') . "'";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_font.value.font_type') === 'system') {
                                if ($fv !== '2') {
                                    $css[] = 'font-weight: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_weight');
                                }
                                $css[] = 'font-family: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_family');
                            }
                            if ($fv === '2') {
                                if (!$fg && ($value = Journal2Utils::getProperty($caption, 'text_font.value.font_weight'))) {
                                    $css[] = 'font-weight: ' . $value;
                                }
                                if ($value = Journal2Utils::getProperty($caption, 'text_font.value.font_style')) {
                                    $css[] = 'font-style: ' . $value;
                                }
                                $value = Journal2Utils::getProperty($caption, 'text_font.value.font_size');
                                if (Journal2Utils::getDevice() === 'phone') {
                                    $value2 = Journal2Utils::getProperty($caption, 'text_font.value.font_size_mobile');
                                    if ($value2 && $value2 !== '---') {
                                        $value = $value2;
                                    }
                                }
                                if ($value && $value !== '---') {
                                    $css[] = 'font-size: ' . $value;
                                }
                                if ($value = Journal2Utils::getProperty($caption, 'text_font.value.text_transform')) {
                                    $css[] = 'text-transform: ' . $value;
                                }
                                if ($value = Journal2Utils::getProperty($caption, 'text_font.value.letter_spacing')) {
                                    $css[] = 'letter-spacing: ' . $value . 'px';
                                }
                            } else {
                                if (Journal2Utils::getProperty($caption, 'text_font.value.font_type') !== 'none') {
                                    $css[] = 'font-size: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_size');
                                    $css[] = 'font-style: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_style');
                                    $css[] = 'text-transform: ' . Journal2Utils::getProperty($caption, 'text_font.value.text_transform');
                                    if ($letter_spacing = Journal2Utils::getProperty($caption, 'text_font.value.letter_spacing')) {
                                        $css[] = 'letter-spacing: ' . $letter_spacing . 'px';
                                    }
                                }
                            }
                            if (Journal2Utils::getProperty($caption, 'text_font.value.color.value.color')) {
                                $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_font.value.color.value.color'));
                            }
                            $css[] = 'text-align: ' . Journal2Utils::getProperty($caption, 'text_align', 'center');
                            if (Journal2Utils::getProperty($caption, 'text_bgcolor.value.color')) {
                                $css[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_bgcolor.value.color'));
                            }
                            if (Journal2Utils::getProperty($caption, 'text_hover_color.value.color')) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover { color: " . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_hover_color.value.color')) . " !important; }";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_hover_bg_color.value.color')) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover { background-color: " . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_hover_bg_color.value.color')) . " !important; }";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_border')) {
                                $css = array_merge($css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($caption, 'text_border')));
                            }
                            if (Journal2Utils::getProperty($caption, 'text_hover_border_color.value.color')) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover { border-color: " . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_hover_border_color.value.color')) . " !important; }";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_top') !== null) {
                                $css[] = 'padding-top: ' . Journal2Utils::getProperty($caption, 'text_padding_top') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_right') !== null) {
                                $css[] = 'padding-right: ' . Journal2Utils::getProperty($caption, 'text_padding_right') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_bottom') !== null) {
                                $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($caption, 'text_padding_bottom') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_left') !== null) {
                                $css[] = 'padding-left: ' . Journal2Utils::getProperty($caption, 'text_padding_left') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_line_height')) {
                                $css[] = 'line-height: ' . Journal2Utils::getProperty($caption, 'text_line_height') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_max_width')) {
                                $css[] = 'max-width: ' . Journal2Utils::getProperty($caption, 'text_max_width') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_line', 'single') === 'multi') {
                                $css[] = 'white-space: normal;';
                            }

                            $content = Journal2Utils::getProperty($caption, 'text.value.' . $this->config->get('config_language_id'));
                            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($caption, 'shadow')) as $sett) {
                                $css[] = $sett;
                            }
                            foreach (Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($caption, 'bg_image')) as $sett) {
                                $css[] = $sett;
                            }
                            foreach (Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($caption, 'bg_image_hover')) as $sett) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover {" . $sett . " !important; }";
                            }
                            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($caption, 'shadow_active')) as $sett) {
                                $this->data['global_style'][] = "a#jcaption-{$module}-{$caption_id}:active {" . $sett . " !important; }";
                            }
                            foreach (Journal2Utils::getShadowCssProperties(Journal2Utils::getProperty($caption, 'shadow_hover')) as $sett) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover {" . $sett . " !important; }";
                            }
                            break;
                        case 'video':
                            if (Journal2Utils::getProperty($caption, 'video_fullwidth')) {
                                $caption_classes[] = 'fullscreenvideo';
                                $caption_data[] = 'data-forceCover="1"';
                                $width = '100%';
                                $height = '100%';
                            } else {
                                $width = Journal2Utils::getProperty($caption, 'video_width', 100);
                                $height = Journal2Utils::getProperty($caption, 'video_height', 100);
                            }
                            switch (Journal2Utils::getProperty($caption, 'video_type')) {
                                case 'youtube':
                                    $video_id = Journal2Utils::getProperty($caption, 'video_yt_id');
                                    if (!$video_id) continue;
                                    $content = '<iframe src="//www.youtube.com/embed/' . $video_id .'?enablejsapi=1&html5=1&amp;hd=1&amp;wmode=opaque&amp;controls=1&amp;showinfo=0;rel=0;" width="' . $width . '" height="' . $height . '"></iframe>';
                                    break;
                                case 'vimeo':
                                    $video_id = Journal2Utils::getProperty($caption, 'video_vm_id');
                                    if (!$video_id) continue;
                                    $content = '<iframe src="//player.vimeo.com/video/' . $video_id .'?title=0&amp;byline=0&amp;portrait=0;api=1" width="' . $width . '" height="' . $height . '"></iframe>';
                                    break;
                                case 'local':
                                    $file_extensions = array('mp4', 'webm', 'ogg');
                                    $content = '<video class="" preload="none" width="' . $width . '" height="' . $height .'" data-setup="{}"';
                                    if (Journal2Utils::getProperty($caption, 'video_loop', '0') == '1') {
                                        $content .= ' loop';
                                    }
                                    $poster_path = Journal2Utils::getProperty($caption, 'video_path') . '.png';
                                    if (file_exists(DIR_APPLICATION . '../' .  $poster_path)) {
                                        $content .= ' poster="' . $poster_path . '" data-setup="{}"';
                                    }
                                    $content .= '>';
                                    foreach ($file_extensions as $file_extension) {
                                        $video_path = Journal2Utils::getProperty($caption, 'video_path') . '.' . $file_extension;
                                        if (file_exists(DIR_APPLICATION . '../' .  $video_path)) {
                                            $content .= '<source src="' . $video_path . '" type="video/' . $file_extension . '" />';
                                        }
                                    }
                                    $content .= '</video>';
                                    break;
                            }
                            if (Journal2Utils::getProperty($caption, 'video_autoplay')) {
                                $caption_data[] = 'data-autoplay="true"';
                                $caption_data[] = 'data-forcerewind="on"';
                            } else {
                                $caption_data[] = 'data-autoplay="false"';
                            }
                            if (Journal2Utils::getProperty($caption, 'video_autoplayonlyfirsttime')) {
                                $caption_data[] = 'data-autoplayonlyfirsttime="true"';
                            } else {
                                $caption_data[] = 'data-autoplayonlyfirsttime="false"';
                            }
                            if (Journal2Utils::getProperty($caption, 'video_nextslideatend')) {
                                $caption_data[] = 'data-nextslideatend="true"';
                            } else {
                                $caption_data[] = 'data-nextslideatend="false"';
                            }
                            if (!Journal2Utils::getProperty($caption, 'video_volume')) {
                                $caption_data[] = 'data-volume="mute"';
                            }
                            break;
                    }
                    $caption_classes[] = Journal2Utils::getProperty($caption, 'animation_in', 'fade');
                    $caption_classes[] = Journal2Utils::getProperty($caption, 'animation_out', 'fadeout');

					if (Journal2Utils::getProperty($caption, 'classname')) {
						$caption_classes[] = Journal2Utils::getProperty($caption, 'classname');
					}

                    if (Journal2Utils::getProperty($caption, 'animation_in') === 'customin') {
                        $custom_in = array();
                        $custom_out = array();
                        // in
                        if (Journal2Utils::getProperty($caption, 'custom_in_transition_x') !== null) {
                            $custom_in[] = 'x:' . Journal2Utils::getProperty($caption, 'custom_in_transition_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_transition_y') !== null) {
                            $custom_in[] = 'y:' . Journal2Utils::getProperty($caption, 'custom_in_transition_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_scale_x') !== null) {
                            $custom_in[] = 'scaleX:' . Journal2Utils::getProperty($caption, 'custom_in_scale_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_scale_y') !== null) {
                            $custom_in[] = 'scaleY:' . Journal2Utils::getProperty($caption, 'custom_in_scale_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_rotation_x') !== null) {
                            $custom_in[] = 'rotationX:' . Journal2Utils::getProperty($caption, 'custom_in_rotation_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_rotation_y') !== null) {
                            $custom_in[] = 'rotationY:' . Journal2Utils::getProperty($caption, 'custom_in_rotation_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_rotation_z') !== null) {
                            $custom_in[] = 'rotationZ:' . Journal2Utils::getProperty($caption, 'custom_in_rotation_z');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_transform_perspective', '500') !== null) {
                            $custom_in[] = 'transformPerspective:' . Journal2Utils::getProperty($caption, 'custom_in_transform_perspective', '500');
                        }
                        $custom_in[] = 'opacity:' . Journal2Utils::getProperty($caption, 'custom_in_opacity');
                        $custom_in[] = 'transformOrigin:' . Journal2Utils::getProperty($caption, 'transformOriginXin', 'center') . ' ' . Journal2Utils::getProperty($caption, 'transformOriginYin', 'center');
                        // out
                        if (Journal2Utils::getProperty($caption, 'custom_out_transition_x') !== null) {
                            $custom_out[] = 'x:' . Journal2Utils::getProperty($caption, 'custom_out_transition_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_transition_y') !== null) {
                            $custom_out[] = 'y:' . Journal2Utils::getProperty($caption, 'custom_out_transition_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_scale_x') !== null) {
                            $custom_out[] = 'scaleX:' . Journal2Utils::getProperty($caption, 'custom_out_scale_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_scale_y') !== null) {
                            $custom_out[] = 'scaleY:' . Journal2Utils::getProperty($caption, 'custom_out_scale_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_rotation_x') !== null) {
                            $custom_out[] = 'rotationX:' . Journal2Utils::getProperty($caption, 'custom_out_rotation_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_rotation_y') !== null) {
                            $custom_out[] = 'rotationY:' . Journal2Utils::getProperty($caption, 'custom_out_rotation_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_rotation_z') !== null) {
                            $custom_out[] = 'rotationZ:' . Journal2Utils::getProperty($caption, 'custom_out_rotation_z');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_transform_perspective', '500') !== null) {
                            $custom_out[] = 'transformPerspective:' . Journal2Utils::getProperty($caption, 'custom_out_transform_perspective', '500');
                        }
                        $custom_out[] = 'opacity:' . Journal2Utils::getProperty($caption, 'custom_out_opacity');
                        $custom_out[] = 'transformOrigin:' . Journal2Utils::getProperty($caption, 'transformOriginXout', 'center') . ' ' . Journal2Utils::getProperty($caption, 'transformOriginYout', 'center');
                        $caption_data[] = 'data-customin="' . implode(';',$custom_in) . '"';
                        $caption_data[] = 'data-customout="' . implode(';',$custom_out) . '"';
                    }

                    $_captions[] = array(
                        'id'            => "{$module}-{$caption_id}",
                        'content'       => $content,
                        'data'          => implode(' ', $caption_data),
                        'classes'       => implode(' ', $caption_classes),
                        'css'           => implode('; ', $css),
                        'link'          => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($caption, 'link')),
                        'target'        => Journal2Utils::getProperty($caption, 'link_new_window') ? ' target="_blank"' : ''
                    );
                }

                $image = Journal2Utils::getProperty($slide, 'image');
                if (is_array($image)) {
                    $image = Journal2Utils::getProperty($image, $this->config->get('config_language_id'));
                }
                if (!file_exists(DIR_IMAGE . $image)) {
                    $image = 'no_image.jpg';
                }

                $thumb = Journal2Utils::getProperty($slide, 'thumb');
                if (is_array($thumb)) {
                    $thumb = Journal2Utils::getProperty($thumb, $this->config->get('config_language_id'));
                }

                if (!$thumb || !file_exists(DIR_IMAGE . $thumb)) {
                    $thumb = $image;
                }

                $slide_data[] = 'data-thumb="' . Journal2Utils::resizeImage($this->model_tool_image, $thumb, $this->data['js_options']['thumbWidth'], $this->data['js_options']['thumbHeight'], 'crop') . '"';

                $_slides[] = array(
                    'image'     => Journal2Utils::resizeImage($this->model_tool_image, $image),
                    'name'      => Journal2Utils::getProperty($slide, 'slide_name'),
                    'data'      => implode(' ', $slide_data),
                    'captions'  => $_captions
                );
            }

            $this->data['module'] = $module;
            $this->data['slides'] = $_slides;
            $this->data['js_options']['thumbAmount'] = min(Journal2Utils::getProperty($this->data['js_options'], 'thumbAmount'), count($this->data['slides']));
			$this->data['js_options']['touchenabled'] = count($_slides) > 1 ? 'on' : 'off';
            $this->data['timer'] = Journal2Utils::getProperty($module_data, 'timer');
            $this->data['preload_images'] = Journal2Utils::getProperty($module_data, 'preload_images', '1');
            $this->data['dummy_image'] = Journal2Utils::resizeImage($this->model_tool_image, 'data/journal2/transparent.png');

            $this->template = 'journal2/module/slider_advanced.tpl';

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

        $this->document->addStyle('catalog/view/theme/journal2/lib/rs-plugin/css/settings.css');
        $this->document->addScript('catalog/view/theme/journal2/lib/rs-plugin/js/jquery.themepunch.tools.min.js');
        $this->document->addScript('catalog/view/theme/journal2/lib/rs-plugin/js/jquery.themepunch.revolution.js');

        $output = $this->render();

        Journal2::stopTimer(get_class($this));

        return $output;
    }

}
