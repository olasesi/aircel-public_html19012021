<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'customer_id' => $_POST['customerid'],
    'token' => $_POST['token'],
    'platform' => 'mobile',
);
$url = "https://www.obejor.com.ng/index.php?route=custom/push_notification/subscribe&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
?>