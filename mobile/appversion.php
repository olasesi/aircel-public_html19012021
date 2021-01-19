<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json');
$version = [
    "status"=>true, 
    "data"=>[
        "versionnumber"=>"1.0.10"
    ]
];
echo json_encode($version);
?>