<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'customer_id' => $_POST['customerid'],
    'product_id' => $_POST['productid'],
    'lastname' => $_POST['lastname'],
    'address_1' => $_POST['address_1'],
    'address_2' => $_POST['address_2'],
    'postcode' => $_POST['postcode'],
    'company' => $_POST['company'],
    'city' => $_POST['city'],
    'zone_id' => $_POST['zone_id'],
    'address_id' => $_POST['addressid'],
    'shippingoption' => $_POST['shippingoption'],
    'paymentoption' => $_POST['paymentoption'],
    'option' => $_POST['option'],
    'country_id' => $_POST['country_id']
    );
$url = "https://www.obejor.com.ng/index.php?route=api/shippingmethods&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
