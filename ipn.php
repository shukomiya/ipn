<?php

ini_set( 'display_errors', 1 );

mb_language('ja');
mb_internal_encoding("utf-8");

date_default_timezone_set('Asia/Tokyo');

$enable_sandbox = false;

$my_email_address = 'info@komish.com';
if ($enable_sandbox){
	$my_email_address = 'info_seller@komish.com';
}

require('PaypalIPN.php');
require('2mailasp.php');

$ipn = new PaypalIPN();

// Use the sandbox endpoint during testing.
if ($enable_sandbox){
	$ipn->useSandbox();
}
$verified = $ipn->verifyIPN();

// inspect IPN validation result and act accordingly
$paypal_ipn_status = "VERIFICATION INVALID";

if ($verified) {
    /*
     * Process IPN
     * A list of variables is available here:
     * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
     */
	$paypal_ipn_status = "VERIFICATION VALID";

	$payed = false;
	$payment_status = $_POST['payment_status'];
	if (strcmp($payment_status, 'Completed') == 0){
		$payed = true;
	}
	
	$receiver_email = $_POST['receiver_email'];
	if (strcmp($receiver_email, $my_email_address) != 0){
		mb_send_mail('info@komish.com',"ipn error","not match my email address=$receiver_email", "From: $my_email_address");
		exit;
	}
	
		
	$first_name     = $_POST['first_name'];
	$last_name      = $_POST['last_name'];
	$payer_email    = $_POST['payer_email'];
	$address_zip	= $_POST['address_zip'];
	$address_state  = $_POST['address_state'];
	$address_city  = $_POST['address_city'];
	$address_street  = $_POST['address_street'];
	$contact_phone	= $_POST['contact_phone'];
	$item_name      = $_POST['item_name'];
	$item_number    = $_POST['item_number'];
	$txn_id         = $_POST['txn_id'];
	$mc_gross		= $_POST['mc_gross'];
	$date = date('Y/m/d H:i:s');
	
	$mail_text		=	"\r\n";
	$mail_text		.=	"支払状況　：$payment_status\r\n";
	$mail_text 		.=	"購入者名　：$last_name $first_name\r\n";
	$mail_text		.=	"E-MAIL　　：$payer_email\r\n";
	$mail_text		.=	"郵便番号　：$address_zip\r\n";
	$mail_text		.=	"住所　　　：$address_state$address_city$address_street\r\n";
	$mail_text		.=	"電話番号　：$contact_phone\r\n";
	$mail_text		.=	"商品名　　：$item_name\r\n";
	$mail_text		.=	"商品番号　：$item_number\r\n";
	$mail_text		.=	"価格　　　：$mc_gross\r\n";
	$mail_text		.= 	"購入日　　: $date\r\n";
	$mail_text		.=	"取引ID　　：$txn_id\r\n";
	
	$mail_text .= "\r\n";
	
	$mailFrom = "From: " . mb_encode_mimeheader("決済通知") . " <$receiver_email>";  
	$subject  = "Paypal決済通知";
	
	mb_send_mail($my_email_address,$subject,$mail_text,$mailFrom);
	if ($payed){
		$query = array(
			"product" => $item_name,
			"item_number" => $item_number,
			"fname" => $last_name,
			"email" => $payer_email
		);
		post_query($query, "https://plus.komish.com/o/fromipn.php/");
	}
} else {
	// IPN invalid, log for manual investigation
	$data_text = "";
	foreach ($_POST as $key => $value) {
	    $data_text .= $key . " = " . $value . "\r\n";
	}
	
	$test_text = "";
	if ($_POST["test_ipn"] == 1) {
	    $test_text = "Test ";
	}
	
	if (!empty($data_text)){
		$mail_text = "$paypal_ipn_status\r\n$test_text\r\n$data_text\r\n";
	}else{
		$mail_text = "$paypal_ipn_status\r\nPOST data nothing\r\n";
	}
	
	$mailFrom = "From: " . mb_encode_mimeheader("決済エラー通知") . " <$my_email_address>";  
	$subject  = "Paypal決済エラー通知";
	mb_send_mail($my_email_address,$subject,$mail_text, $mailFrom);
}

// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
header("HTTP/1.1 200 OK");

