<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'search' => $_POST['search'],
    'pageno' => $_POST['pageno']
    );
$url = "https://www.obejor.com.ng/index.php?route=api/search&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;