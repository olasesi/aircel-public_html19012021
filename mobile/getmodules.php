<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'moduleid' => $_POST['moduleid'],
    'pageno' => $_POST['pageno']
    );
$url = "https://www.obejor.com.ng/index.php?route=api/modules&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;