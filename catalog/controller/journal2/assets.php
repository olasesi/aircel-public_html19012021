<?php
class ControllerJournal2Assets extends Controller {

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

    public function css() {
        if (!$this->journal2->cache->getDeveloperMode() && $this->journal2->minifier->getMinifyCss()) {
            $file = $this->journal2->cache->getCachePath() . $this->journal2->cache->getJournalAssetsFileName('css');
            if (!file_exists($file)) {
                $this->data['selectors'] = $this->journal2->css_settings;
                $this->template = 'journal2/assets/css.tpl';
                file_put_contents($file, $this->render(), LOCK_EX);
            }
        } else {
            $this->data['selectors'] = $this->journal2->css_settings;
            $this->template = 'journal2/assets/css.tpl';
            $this->response->addHeader('Content-type: text/css');
            $this->response->setOutput($this->render());
        }
    }

    public function js() {
        if (!$this->journal2->cache->getDeveloperMode() && $this->journal2->minifier->getMinifyJs()) {
            $file = $this->journal2->cache->getCachePath() . $this->journal2->cache->getJournalAssetsFileName('js');
            if (!file_exists($file)) {
                $this->template = 'journal2/assets/js.tpl';

                $content_min = JSMin::minify($this->render());

                file_put_contents($file, $content_min, LOCK_EX);
            }
        } else {
            $this->template = 'journal2/assets/js.tpl';
            $this->response->addHeader('Content-type: application/javascript');
            $this->response->setOutput($this->render());
        }
    }

}
