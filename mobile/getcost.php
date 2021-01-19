<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'customer_id' => $_POST['customerid'],
    'address_id' => $_POST['addressid'],
    'option' => $_POST['option'],
    );
$url = "https://www.obejor.com.ng/index.php?route=api/cost&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
