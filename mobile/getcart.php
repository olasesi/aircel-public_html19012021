<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'cart_id' => $_POST['cartid'],
    'product_id' => $_POST['productid'],
    'customer_id' => $_POST['customerid'],
    'quantity' => $_POST['quantity']
    );
$url = "https://www.obejor.com.ng/index.php?route=api/cart/products&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;