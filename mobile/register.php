<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    // cat_id is supposed to be dynamic, accepting it's input from the global post array;
    'firstname' => $_POST['firstname'],
    'lastname' => $_POST['lastname'],
    'email' => $_POST['email'],
    'telephone' => $_POST['telephone'],
    'password' => $_POST['password'],
    'newsletter' => $_POST['newsletter'],
    'agree' => $_POST['agree']
);
$url = "https://www.obejor.com.ng/index.php?route=api/register&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
