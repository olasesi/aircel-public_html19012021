<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'customer_id' => $_POST['customerid'],
    'address_id' => $_POST['addressid'],
    'shippingoption' => $_POST['shippingoption'],
    'paymentoption' => $_POST['paymentoption'],
    'reference' => $_POST['reference'],
    'flw_reference' => $_POST['flw_reference'],
    'order_id' => $_POST['orderid'],
    'option' => $_POST['option'],
    );
$url = "https://www.obejor.com.ng/index.php?route=api/addorderhistory&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;