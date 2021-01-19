<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    // cat_id is supposed to be dynamic, accepting it's input from the global post array;
   'email' => $_POST['email'],
   'password' => $_POST['password']
);
$url = "https://www.obejor.com.ng/index.php?route=api/loginuser&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;