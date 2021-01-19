<?php
// header('Access-Control-Allow-Credentials :true');
// header('Access-Control-Allow-Origin : http');
require_once 'gettoken.php';
require "common.php";

$fields = array(
    // cat_id is supposed to be dynamic, accepting it's input from the global post array;
   'cat_id' => $_POST['category_id']
);
$url = "https://www.obejor.com.ng/index.php?route=api/banners&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
