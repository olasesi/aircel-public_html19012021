<?php

require_once 'gettoken.php';
require "common.php";

$fields = array(
    // productId is supposed to be dynamic, accepting it's input from the global post array;
  'productid' => $_POST['productid']
);
$url = "https://www.obejor.com.ng/index.php?route=api/product/product_id&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
