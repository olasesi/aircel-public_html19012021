<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'customer_id' => $_POST['customerid'],
    'product_id' => $_POST['productid'],
    'quantity' => $_POST['quantity'],
    'option' => $_POST['option']
    );
$url = "https://www.obejor.com.ng/index.php?route=api/cart/add&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;