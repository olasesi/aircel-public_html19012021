<?php
require_once 'gettoken.php';
require "common.php";

$fields = array(
    'customer_id' => $_POST['customerid'],
    'firstname' => $_POST['firstname'],
    'lastname' => $_POST['lastname'],
    'address_1' => $_POST['address'],
    'address_2' => $_POST['address2'],
    'postcode' => $_POST['postcode'],
    'company' => $_POST['telephone'],
    'city' => $_POST['city'],
    'zone_id' => $_POST['zoneid'],
    'country_id' => $_POST['countryid']
    );
$url = "https://www.obejor.com.ng/index.php?route=api/address&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;
