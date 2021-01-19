<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type,x-prototype-version,x-requested-with');



if(isset($_SESSION['response'])) {
  $response_arr = json_decode($_SESSION['response'], true);
  $api = $response_arr['api_token'];

} else {
    $curl = curl_init();
    $username = 'purpletreemultivendor';
    $key = '0wkgW4cAkhhugc51S3BkUyv4gCeAgxY0aH1H3YQSenDDGmlTZloCSDvdYcrdmY45IswGmebDow7CKuETfMnQQPD10aIEeowtFrtD2UrEvp5iIdFNZSuBbZcldpDWULnk2ebjhPEyNqYIYglnoeWiyH7RZZeH7KOWbyhmky6u6UwXn40OMI707aFfWwbbVcWoO8OOkPRB7ZLOPr3qmVQzcsewZ6pCydkXEmFGgexnHki0L6qeHhr6VMR85NkOYagz';
    
    $post = array("username"=> $username, "key"=> $key);

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.obejor.com.ng/index.php?route=api%2Flogin",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post,
    ));

    $_SESSION['response'] = curl_exec($curl);

    $err = curl_error($curl);
    
    $response_arr = json_decode($_SESSION['response'], true);
    $api = $response_arr['api_token'];
    


}