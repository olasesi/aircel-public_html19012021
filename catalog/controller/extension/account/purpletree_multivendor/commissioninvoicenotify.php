<?php
class ControllerExtensionAccountPurpletreeMultivendorCommissioninvoicenotify extends Controller {

	public function index() {
	$this->load->model('extension/purpletree_multivendor/commissioninvoicenotify');
	$logger = new Log('error.log');
	$raw_post_data = file_get_contents('php://input');
	$raw_post_array = explode('&', $raw_post_data);
	$myPost = array();
	foreach ($raw_post_array as $keyval) {
		$keyval = explode ('=', $keyval);
		if (count($keyval) == 2)
			$myPost[$keyval[0]] = urldecode($keyval[1]);
	}
	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';
	if(function_exists('get_magic_quotes_gpc')) {
		$get_magic_quotes_exists = true;
	}


	foreach ($myPost as $key => $value) {
		if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
			$value = urlencode(stripslashes($value));
		} else {
			$value = urlencode($value);
		}
		$req .= "&$key=$value";
	}

	// Post IPN data back to PayPal to validate the IPN data is genuine
	// Without this step anyone can fake IPN data

	$ch = curl_init("https://www.paypal.com/cgi-bin/webscr");
	if ($ch == FALSE) {
		return FALSE;
	}
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

	// Set TCP timeout to 30 seconds
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

	$res = curl_exec($ch);


	if (curl_errno($ch) != 0) // cURL error
		{
			$logger->write(date('[Y-m-d H:i e] ')."Can't connect to PayPal to validate IPN message: " . curl_error($ch));
		curl_close($ch);
		exit;
	} else {
			// Log the entire HTTP response if debug is switched on.
			$logger->write(date('[Y-m-d H:i e] ')."HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req ");
			$logger->write(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res");
			curl_close($ch);
	}
// Inspect IPN validation result and act accordingly
// Split response headers and payload, a better way for strcmp
$payment_response = $res;

$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));
if (strcmp ($res, "VERIFIED") == 0) {
	// assign posted variables to local variables
	foreach($_POST as $key=>$value) {
		$logger->write(date('[Y-m-d H:i e] ')."Paypal response for ".$key." is ".$value);
	}
	try {
		$payment_status = $_POST['payment_status'];
	if($payment_status == "Completed") {
		$status = 'Complete';
	} else {
		$status = 'Pending';
	}
	$pending_reason = "";
	$subsject = '';
	if(isset($_POST['transaction_subject']) && $_POST['transaction_subject'] != '') {
		 $subsject = ", Transaction Subject is ".$_POST['transaction_subject'];
	}
	if(isset($_POST['pending_reason']) && $_POST['pending_reason'] !='') {
	$pending_reason = ", Pending Reason is ".$_POST['pending_reason'];
	}
	$comment = "Payment Status is ".$_POST['payment_status'].", Verify Sign is ".$_POST['verify_sign']." ".$pending_reason.", IPN Track Id is ".$_POST['ipn_track_id'].$subsject;
	$txn_id = $_POST['txn_id'];
	$dataarraypaypal = array('invoice_id' => $_POST['custom'],
							 'status'  => $status,
							 'comment'  => $comment,
							 'txn_id'  => $txn_id,
							 'payment_mode'  => 'Online',
							 'amount'  => $_POST['payment_gross']
							);

	$this->model_extension_purpletree_multivendor_commissioninvoicenotify->addPaypalPaymentHistory($dataarraypaypal);
	} catch(Exception $e){ 
						$logger->write("Something went wrong after payment from Paypal ".$e->getMessage()); 
						
			}
	// check whether the payment_status is Completed
	//$logger->write(date('[Y-m-d H:i e] '). "Verified IPN: $req ");
	
} else if (strcmp ($res, "INVALID") == 0) {
	// log for manual investigation
	// Add business logic here which deals with invalid IPN messages
	$logger->write(date('[Y-m-d H:i e] '). "Invalid IPN: $req");
}
	}

}
?>
