<?php
/**
	* Token Validation Page
	* Author 247Commerce
	* Date 30 SEP 2020
*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');
}

require_once('config.php');
require_once('db-config.php');

require 'log-autoloader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$logger = new Logger('Authentication');
$logger->pushHandler(new StreamHandler('var/logs/CARSTREAM_auth_log.txt', Logger::INFO));
$logger->info("invoiceId: ".$_REQUEST['invoiceId']);

if(isset($_REQUEST['invoiceId'])){
	$invoiceId = json_decode(base64_decode($_REQUEST['invoiceId']),true);
	$conn = getConnection();
	
	$stmt_order_payment = $conn->prepare("select * from order_payment_details where invoice_id='".$invoiceId."'");
	$stmt_order_payment->execute();
	$stmt_order_payment->setFetchMode(PDO::FETCH_ASSOC);
	$result_order_payment = $stmt_order_payment->fetchAll();
	if (isset($result_order_payment[0])) {
		$result_order_payment = $result_order_payment[0];
		
		$string = base64_decode($result_order_payment['params']);
		$string = preg_replace("/[\r\n]+/", " ", $string);
		$json = utf8_encode($string);
		$cartData = json_decode($json,true);
		$stmt = $conn->prepare("select * from cardstream_token_validation where email_id='".$result_order_payment['email_id']."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		//print_r($result[0]);exit;
		if (isset($result[0])) {
			$result = $result[0];
			$payment_option = $result['payment_option'];
			$action = "CFO";
			if($payment_option == "CFO"){
				$action = "SALE";
			}
			if($payment_option == "CFS"){
				$action = "captureDelay";
			}
			$billingAddress = $cartData['billingAddress'];
			$cardStream = '';
			$cardStream .= '<form id="cardstream_form" name="cardstream_form" action="'.CARDSTREAM_URL.'" method="post">';
			$cardStream .= '<input type="hidden" name="merchantID" value="'.$result['merchant_id'].'">';
			$cardStream .= '<input type="hidden" name="action" value="'.$action.'">';
			$cardStream .= '<input type="hidden" name="type" value="1">';
			$cardStream .= '<input type="hidden" name="countryCode" value="'.$billingAddress['countryCode'].'">';
			$cardStream .= '<input type="hidden" name="currencyCode" value="'.$cartData['cart']['currency']['code'].'">';
			$cardStream .= '<input type="hidden" name="amount" value="'.$result_order_payment['total_amount'].'">';
			$cardStream .= '<input type="hidden" name="orderRef" value="'.$invoiceId.'">';
			$cardStream .= '<input type="hidden" name="transactionUnique" value="5f9400a817966">';
			$cardStream .= '<input type="hidden" name="redirectURL" value="'.BASE_URL.'success.php">';
			$cardStream .= '<input type="hidden" name="signature" value="'.$result['cardstream_signature'].'">';
			$cardStream .= '</form>';
			$cardStream .= '<script language="javascript">document.cardstream_form.submit();</script>';
			echo $cardStream;
		}
	}
}
?>