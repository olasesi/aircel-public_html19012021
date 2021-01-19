<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type,x-prototype-version,x-requested-with');
    require_once 'gettoken.php';
    require "common.php";
    
    $fields = array();
    $url    = "https://www.obejor.com.ng/index.php?route=api/homepage/sections&api_token=".$api;
    $json   = do_curl_post_request($url, $fields);
    echo $json;
?>