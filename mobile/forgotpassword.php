<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'email' => $_POST['email'],
    );
$url = "https://www.obejor.com.ng/index.php?route=api/customer/forgotPassword&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;