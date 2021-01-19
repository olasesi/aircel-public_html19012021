<?php
require "common.php";
 
// set up params
$url = 'https://www.obejor.com.ng/index.php?route=api/login';
 
$fields = array(
  'username' => 'purpletreemultivendor',
  'key' => '0wkgW4cAkhhugc51S3BkUyv4gCeAgxY0aH1H3YQSenDDGmlTZloCSDvdYcrdmY45IswGmebDow7CKuETfMnQQPD10aIEeowtFrtD2UrEvp5iIdFNZSuBbZcldpDWULnk2ebjhPEyNqYIYglnoeWiyH7RZZeH7KOWbyhmky6u6UwXn40OMI707aFfWwbbVcWoO8OOkPRB7ZLOPr3qmVQzcsewZ6pCydkXEmFGgexnHki0L6qeHhr6VMR85NkOYagz',
);
 
$json = do_curl_request($url, $fields);
$data = json_decode($json);
var_dump($data);
