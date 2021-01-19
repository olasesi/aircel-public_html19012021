<?php
class ControllerExtensionCommonSeoUrlCommonHome extends Controller
{
    public function index()
    {
        if ($this->config->get('config_seo_url') && !$this->config->get('seo_url_common_home_status')) {
            $this->url->addRewrite($this);
            $this->config->set('seo_url_common_home_status', 1);
        }
    }
    public function rewrite($link)
    {
        $url      = '';
        $data     = array();
        $url_info = parse_url(str_replace('&amp;', '&', $link));
        if (empty($url_info['query'])) {
            return $link;
        }
        parse_str($url_info['query'], $data);
        if (isset($data['route']) && $data['route'] == 'common/home') {
            $is_common_home = true;
        } else {
            $is_common_home = false;
        }
        if ($is_common_home) {
            unset($data['route']);
            $query = '';
            if ($data) {
                foreach ($data as $key => $value) {
                    $query .= '&' . rawurlencode((string) $key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string) $value));
                }
                if ($query) {
                    $query = '?' . str_replace('&', '&amp;', trim($query, '&'));
                }
            }
            return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
        } else {
            return $link;
        }
    }
}