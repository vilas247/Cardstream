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

$res = array();
$res['status'] = false;
$res['data'] = '';
$res['msg'] = '';

$logger = new Logger('Authentication');
$logger->pushHandler(new StreamHandler('var/logs/CARDSTREAM_auth_log.txt', Logger::INFO));
$logger->info("authKey: ".$_REQUEST['authKey']);
$logger->info("cartData: ".$_REQUEST['cartData']);

if(isset($_REQUEST['authKey'])){
	$valid = validateAuthentication($_REQUEST);
	if($valid){
		$email_id = json_decode(base64_decode($_REQUEST['authKey']));
		if (filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
			$conn = getConnection();
			$stmt = $conn->prepare("select * from cardstream_token_validation where email_id='".$email_id."'");
			$stmt->execute();
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$result = $stmt->fetchAll();

			if (isset($result[0])) {
				$result = $result[0];
				$payment_option = $result['payment_option'];
				if(!empty($result['merchant_id']) && !empty($result['cardstream_signature']) && !empty($result['acess_token']) && !empty($result['store_hash'])){
					$sellerdb = $result['sellerdb'];
					$acess_token = $result['acess_token'];
					$store_hash = $result['store_hash'];
					//$cartData = getCartData($email_id,$_REQUEST['cartId'],$acess_token,$store_hash);
					$string = base64_decode($_REQUEST['cartData']);

					$string = preg_replace("/[\r\n]+/", " ", $string);
					$json = utf8_encode($string);
					$cartData = json_decode($json,true);
					if(!empty($cartData) && isset($cartData['id'])){
						$totalAmount = $cartData['grandTotal'];
						
						$currency = $cartData['cart']['currency']['code'];
						$billingAddress = $cartData['billingAddress'];
						$invoiceId = "247cardstream_".time();
						
						$transaction_type = "SALE";
						if($payment_option == "CFO"){
							$transaction_type = "SALE";
						}
						if($payment_option == "CFS"){
							$transaction_type = "captureDelay";
						}
						
						$isql = 'insert into order_payment_details(type,email_id,invoice_id,cart_id,total_amount,amount_paid,currency,status,params) values("'.$transaction_type.'","'.$email_id.'","'.$invoiceId.'","'.$cartData['id'].'","'.$cartData['grandTotal'].'","0.00","'.$currency.'","PENDING","'.$_REQUEST['cartData'].'")';
						$conn->exec($isql);
						$res['status'] = true;
						$url = BASE_URL."cardstreamPay.php?invoiceId=".base64_encode(json_encode($invoiceId));
						$res['data'] = $url;
					}
				}
			}
		}
	}
}
echo json_encode($res);exit;

function validateAuthentication($request){
	$valid = true;
	if(isset($request['authKey'])){
		
	}else{
		$valid = false;
	}
	if(isset($request['cartId'])){
		
	}else{
		$valid = false;
	}
	if(isset($request['cartData'])){
		
	}else{
		$valid = false;
	}
	return $valid;
}
?>