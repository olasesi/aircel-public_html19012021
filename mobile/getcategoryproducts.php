<?php
require_once 'gettoken.php';
require "common.php";

$limit = isset($_POST['limit']) ? $_POST['limit'] : 30;
$pageno = isset($_POST['pageno']) ? $_POST['pageno'] : 1;
$fields = array(
    // categoryId is supposed to be dynamic, accepting it's input from the global post array;
    'filter_category_id' => $_POST['categoryid'],
    'filter_filter'      => $_POST['filter'],
    'sort'               => $_POST['sort'],
    'order'              => $_POST['order'],
    'start'              => ($pageno - 1) * $limit,
    'limit'              => $limit
);
$url = "https://www.obejor.com.ng/index.php?route=api/category/categoryProducts&api_token=".$api;
$json = do_curl_post_request($url, $fields);
echo $json;