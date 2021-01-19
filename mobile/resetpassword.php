<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'password' => $_POST['newpassword'],
    'confirm' => $_POST['confirm'],
    'oldpassword' => $_POST['oldpassword'],
    'customer_id' => $_POST['customerid'],
    );
$url = "https://www.obejor.com.ng/index.php?route=api/customer/resetPassword&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;