<?php
require_once 'gettoken.php';
// echo $api . "i am in the if statement";

require "common.php";
 
// get list of products from the "Cart"
$url = 'https://www.obejor.com.ng/index.php?route=api/cart/products&api_token=476268bb6b2d8f8b9ef216c33b';
$json = do_curl_post_request($url);
$products_data = json_decode($json);
 
// fetch "key" of the product we want to edit
$product_key = $products_data->products[0]->cart_id;

// edit the product in the "Cart" using "key"
$url = 'https://www.obejor.com.ng/index.php?route=api/cart/edit&api_token=476268bb6b2d8f8b9ef216c33b';
$fields = array(
//   'key' => $product_key,
  'key' => '386',
  'quantity' => '1'
);
$json = do_curl_post_request($url, $fields);
$data = json_decode($json);
// var_dump($data);
var_dump($products_data);
echo $products_data;

