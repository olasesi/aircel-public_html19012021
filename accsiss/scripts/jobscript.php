<?php
    session_start();
    //require '../model/dbmodel.php';

    date_default_timezone_set('Africa/Lagos');
    
	//$db_model = new DBModel();
	
	//exit("User: " . $_POST["username"] . " PS: " . $_POST["aky"]);
	if($_POST["email"] == " " || $_POST["email"] == "-" || trim($_POST["email"]) == ""){
	    exit("ERROR");
	}else{
	    $email = $_POST["email"];
	}
	if($_POST["fullname"] == " " || $_POST["fullname"] == "-" || $_POST["fullname"] == "%" || trim($_POST["fullname"]) == ""){
	    exit("ERROR");
	}else{
	    $fullname = $_POST["fullname"];
	}
	if(trim($_POST["message"]) == ""){
	    exit("ERROR");
	}else{
	    $cvmessage = $_POST["message"];
	}	

    //save the cv file
	$allowedExts = array("pdf");
	$temp = explode(".", $_FILES["cvfile"]["name"]);
	$returnvalue .= ' ' . $_FILES["cvfile"]["name"];
	$extension = end($temp);
	if (($_FILES["cvfile"]["type"] == "application/pdf") && ($_FILES["cvfile"]["size"] <= 102400) && in_array($extension, $allowedExts)){
		if ($_FILES["cvfile"]["error"] > 0){
		    $returnvalue = 'cverror'; //echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
		}else{
    		    //echo "Upload: " . $_FILES["userphoto"]["name"] . "<br>";
		    //echo "Type: " . $_FILES["userphoto"]["type"] . "<br>";
		    //echo "Size: " . ($_FILES["userphoto"]["size"] / 1024) . " kB<br>";
		    //echo "Temp file: " . $_FILES["userphoto"]["tmp_name"] . "<br>";

		    if (file_exists("../cvs/" . $fullname . $_FILES["cvfile"]["name"])){
		        $returnvalue = 'cverror'; //echo $_FILES["userphoto"]["name"] . " already exists. ";
      		}else{
		        move_uploaded_file($_FILES["cvfile"]["tmp_name"],"../cvs/" . $fullname . $_FILES["cvfile"]["name"]);
                $returnvalue = 'cvsuccess';
      		}
    	}
  	}else{
             $returnvalue = 'cverror';	    
  	}    
    //end saving the file
    $tlog = date("Y-m-d H:i:s");
    
    if($returnvalue == "cvsuccess"){
	    
	    //send to us
        $message ="<div style='text-align:left;font-size=12px;color=#000000;font-family=serif'>";
    	$message .= "Fullname: " . $fullname . "<br />";
        $message .= "email: " . $email . "<br />";
        $message .= "Message:<br /> " . $cvmessage . "<br />";
        $message .= "</div>";

        $mheaders = "MIME-Version: 1.0"  . "\r\n";
        $mheaders .= "Content-type:text/html; charset=UTF-8" . "\r\n";		
        $subject = "Obejor: CV";
        $message = wordwrap($message, 70);	

        $mheaders .= "From: info@obejor.com" . "\r\n"; //$_REQUEST['email'];
       $mheaders .= "Reply-To: careers@obejor.com" . "\r\n";
        $response = mail("careers@obejor.com,obejorbusiness@yahoo.com,icisystemng@gmail.com",$subject,$message, $mheaders);        
	
	
    	if($response){
    	    $response = "We have received your CV. <br /><br /> Thank you.";
    	}	

        //send confirmation to applicant
        $message ="<div style='text-align:left;font-size=12px;color=#000000;font-family=serif'>";
    	$message .= "We have received your CV.<br /><br />";
        $message .= "Thank you<br />";
        $message .= "</div>";

        $mheaders = "MIME-Version: 1.0"  . "\r\n";
        $mheaders .= "Content-type:text/html; charset=UTF-8" . "\r\n";		
        $subject = "Obejor: Your CV";
        $message = wordwrap($message, 70);	

        $mheaders .= "From: info@obejor.com" . "\r\n"; //$_REQUEST['email'];
        $mheaders .= "Reply-To: careers@obejor.com" . "\r\n";
        $response2 = mail($email . ",icisystemng@gmail.com",$subject,$message, $mheaders);        
    }else{
        $response = "Error saving this CV. The file may be too large";
    }
	
	echo $response; //$data->result; //$display; //["firstname"][0] . " " . $messages["lastname"][0]; //$data->result; // . " " . $data->[0]["firstname"] . " " . $data->message[0]["lastname"]);
    


	
?>