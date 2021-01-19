<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'token' => $_POST['token'],
);
$url = "https://www.obejor.com.ng/index.php?route=api/pushnotifications/unsubscribe&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
?>