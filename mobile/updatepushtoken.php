<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'oldtoken' => $_POST['oldtoken'],
    'newtoken' => $_POST['newtoken'],
);
$url = "https://www.obejor.com.ng/index.php?route=api/pushnotifications/updatetoken&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
?>