<?php

require_once 'gettoken.php';
require "common.php";

file_put_contents('./notificationlog.txt',json_encode($_POST),FILE_APPEND );
$fields = array(
    'notificationid' => $_POST['notificationid'],
    'subscriptionid' => $_POST['subscriptionid'],
);
$url = "https://www.obejor.com.ng/index.php?route=custom/push_notification/recieved&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
?>
subscriptionid