<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    // categoryId is supposed to be dynamic, accepting it's input from the global post array;
   'categoryId' => $_POST['categoryId']
);
$url = "https://www.obejor.com.ng/index.php?route=api/category&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
