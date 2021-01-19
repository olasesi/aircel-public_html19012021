<?php

    $fromdate = $_REQUEST["fromdate"];
    $todate = $_REQUEST["todate"];
    
    $response = array(
        "result" => "",
        "message" => ""
        );  

	$query = "select oc_user_activity.date_added as date_added,oc_user.username as username,oc_user_activity.activity_id as activity_id,oc_user_activity.key as activity,
	oc_user_activity.data as data,oc_user_activity.ip as ip,oc_user_activity.user_id from oc_user_activity,oc_user where oc_user.user_id = oc_user_activity.user_id AND oc_user_activity.date_added BETWEEN '" . $fromdate . "' AND '" . $todate . "'";
     
	$db = mysqli_connect("localhost", "obejorng_obejor", "#obejor##19", "obejorng_store");
	if (mysqli_connect_errno()) {
	    $response["result"] = "ERROR";
    	$response["message"] = "Connection failed: " . mysqli_connect_error();
    	echo json_encode($response);
    	
	}else{
	
	  $result = mysqli_query($db,$query);
  	  while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
	    
	    $rowdata[] = array(
	        "date" => $row["date_added"],
	        "username" => $row["username"],
	        "activity_id" => $row["activity_id"],
	        "activity" => htmlentities($row["activity"],ENT_QUOTES), //$row["activity"],
	        "data" => htmlentities($row["data"],ENT_QUOTES), //$row["data"],
	        "ip" => $row["ip"]
	        );
						
	  }	
	  $response["result"] = "SUCCESS";
      $response["message"] = $rowdata;	
       
	}
	//echo count($rowdata);
    echo json_encode($response);    
    //echo " DATE: " . $fromdate . " To: " . $todate;
?>