<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'search' => $_POST['search'],
    'categoryid' => $_POST['categoryid'],
    'pageno' => $_POST['pageno']
    );
$url = "https://www.obejor.com.ng/index.php?route=api/search/getproductsearch&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;