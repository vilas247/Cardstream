<?php
/**
	* Token Validation Page
	* Author 247Commerce
	* Date 22 FEB 2021
*/
if(!isset($_SESSION)){
	session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('config.php');
require_once('db-config.php');
require_once('helper.php');

$conn = getConnection();
$email_id = '';
if(isset($_SESSION['is247Email'])){
	$email_id = $_SESSION['is247Email'];
}

if(isset($_REQUEST['merchant_id']) && isset($_REQUEST['cardstream_signature']) && isset($_REQUEST['acess_token']) && isset($_REQUEST['store_hash'])){
	$conn = getConnection();
	if(!empty($email_id)){
		$stmt = $conn->prepare("select * from cardstream_token_validation where email_id='".$email_id."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		//print_r($result[0]);exit;
		if (isset($result[0])) {
			$result = $result[0];
			$sellerdb = $result['sellerdb'];
			if(!empty($_REQUEST['merchant_id']) && !empty($_REQUEST['cardstream_signature']) && !empty($_REQUEST['acess_token']) && !empty($_REQUEST['store_hash']) && !empty($_REQUEST['is_enable'])){
				$valid = createCustomPage($email_id,$_REQUEST['store_hash'],$_REQUEST['acess_token']);
				if($valid){
					$data = createFolder($sellerdb,$email_id);
					$sql = 'update cardstream_token_validation set merchant_id="'.$_REQUEST['merchant_id'].'",cardstream_signature="'.$_REQUEST['cardstream_signature'].'",acess_token="'.$_REQUEST['acess_token'].'",store_hash="'.$_REQUEST['store_hash'].'" where email_id="'.$email_id.'"';
					//echo $sql;exit;
					$stmt = $conn->prepare($sql);
					$stmt->execute();
					if($_REQUEST['is_enable'] && $_REQUEST['is_enable'] == 1){
						$stmt_s = $conn->prepare("select * from cardstream_scripts where script_email_id='".$email_id."'");
						$stmt_s->execute();
						$stmt_s->setFetchMode(PDO::FETCH_ASSOC);
						$result_s = $stmt_s->fetchAll();
						//print_r($result[0]);exit;
						if (isset($result_s[0])) {
						}else{
							$res = createScripts($sellerdb,$_REQUEST['acess_token'],$_REQUEST['store_hash'],$email_id);
							if($res == "1"){
								$usql = "update cardstream_token_validation set is_enable=1 where email_id='".$email_id."'";
								//echo $usql;exit;
								$stmt_u = $conn->prepare($usql);
								$stmt_u->execute();
							}
						}
					}
					header("Location:dashboard.php?enable=1");
				}else{
					header("Location:dashboard.php?error=1");
				}
			}else{
				header("Location:dashboard.php");
			}
		}else{
			header("Location:dashboard.php");
		}
	}else{
		header("Location:dashboard.php");
	}
}else{
	header("Location:dashboard.php");
}
function createCustomPage($email_id,$store_hash,$acess_token){
	
	$conn = getConnection();
	$valid = false;
	$url = STORE_URL.$store_hash.'/v2/pages';
	$header = array(
		"X-Auth-Token: ".$acess_token,
		"Accept: application/json",
		"Content-Type: application/json"
	);
	$request = array(
			  "body"=> "<head>
						<link rel=\"stylesheet\" href=\"".BASE_URL."/css/order-confirmation.css\">
						<script src=\"".BASE_URL."js/jquery.min.js\"></script>
						<script src=\"".BASE_URL."js/order-confirmation.js\"></script>
						</head>
						<body>
						<h1>Please Wait</h1>
						</body>",
			  "channel_id"=> 1,
			  "has_mobile_version"=> false,
			  "is_customers_only"=> false,
			  "is_homepage"=> false,
			  "is_visible"=> false,
			  "mobile_body"=> "",
			  "name"=> "CardSteam Custom Order Confirmation",
			  "parent_id"=> 0,
			  "search_keywords"=> "",
			  "sort_order"=> 0,
			  "type"=> "raw",
			  "url"=> "/cardtream-custom-order-confirmation"
			);
	$request = json_encode($request,true);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	$res = curl_exec($ch);
	curl_close($ch);
	//print_r($res);exit;
	$log_sql = 'insert into api_log(email_id,type,action,api_url,api_request,api_response) values("'.$email_id.'","BigCommerce","Custom Page","'.addslashes($url).'","'.addslashes($request).'","'.addslashes($res).'")';
	//echo $log_sql;exit;
	$conn->exec($log_sql);
	if(!empty($res)){
		$check_errors = json_decode($res);
		if(isset($check_errors->errors)){
		}else{
			if(json_last_error() === 0){
				$res = json_decode($res,true);
				if(isset($res['id'])){
					$valid = true;
					$sqli = "insert into 247custompages(email_id,page_bc_id,api_response) values('".$email_id."','".$res['id']."','".addslashes(json_encode($res))."')";
					$conn->exec($sqli);
				}
			}
		}
	}
	return $valid;
}
function createScripts($sellerdb,$acess_token,$store_hash,$email_id){
	$conn = getConnection();
	$url = array();
	$rStatus = 0;
	$url[] = JS_SDK;
	$url[] = BASE_URL.$sellerdb.'/custom_script.js';
	foreach($url as $k=>$v){
		//$auth_token = '4ir2j1tpf5cw3pzx7ea4ual2jrei8cd';
		$header = array(
			"X-Auth-Client: ".$acess_token,
			"X-Auth-Token: ".$acess_token,
			"Accept: application/json",
			"Content-Type: application/json"
		);
		$location = 'head';
		$cstom_url = BASE_URL.$sellerdb.'/custom_script.js';
		if($v == $cstom_url){
			$location = 'footer';
		}
		$request = '{
		  "name": "CardStreamApp",
		  "description": "CardStream payment files",
		  "html": "<script src=\"'.$v.'\"></script>",
		  "auto_uninstall": true,
		  "load_method": "default",
		  "location": "'.$location.'",
		  "visibility": "checkout",
		  "kind": "script_tag",
		  "consent_category": "essential"
		}';
		//print_r($request);exit;
		$url = STORE_URL.$store_hash.'/v3/content/scripts';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$res = curl_exec($ch);
		curl_close($ch);
		//print_r($res);exit;
		$log_sql = 'insert into api_log(email_id,type,action,api_url,api_request,api_response) values("'.$email_id.'","BigCommerce","script_tag_injection","'.addslashes($url).'","'.addslashes($request).'","'.addslashes($res).'")';
		//echo $log_sql;exit;
		$conn->exec($log_sql);
		if(!empty($res)){
			$response = json_decode($res,true);
			if(isset($response['data']['uuid'])){
				$sql = 'insert into cardstream_scripts(script_email_id,script_filename,script_code,status,api_response) values("'.$email_id.'","'.basename($v).'","'.$response['data']['uuid'].'","1","'.addslashes($res).'")';
				//echo $sql;exit;
				$conn->exec($sql);
				$rStatus++;
			}
		}
	}
	if($rStatus >= 2){
		return 1;
	}
	if($rStatus >= 2){
		return 0;
	}
}
/* creating folder Based on Seller */
function createFolder($sellerdb,$email_id){
	$conn = getConnection();
	if(!empty($sellerdb)){
		$folderPath = './'.$sellerdb;
		$filecontent = '$("head").append("<script src=\"'.BASE_URL.'js/247carstreamloader.js\" ></script>");';
		$filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.BASE_URL.'css/247carstreamloader.css\" />");';
		$filecontent .= '$(document).ready(function() {
	var stIntId = setInterval(function() {
		if($(".checkout-step--payment").length > 0) {
			if($("#247cardsteampayment").length == 0){
				$(".checkout-step--payment .checkout-view-header").after(\'<div id="247cardsteampayment" class="checkout-form" style="padding:1px"><form id="cardstreampaymentForm" name="cardstreampayment"><input type="hidden" id="247cardstreamkey" value="'.base64_encode(json_encode($email_id)).'" ><button type="submit" class="button button--action button--large button--slab optimizedCheckout-buttonPrimary" style="background-color: #424242;border-color: #424242;color: #fff;">CardStream Payments</button></form></div>\');
				clearInterval(stIntId);
			}
		}
	}, 1000);
	$("body").on("click","#cardstreampaymentForm",function(e){
		e.preventDefault();
		var text = "Please wait...";
		var current_effect = "bounce";
		var key = $("body #247cardstreamkey").val();
		$("#247cardsteampayment").waitMe({
			effect: current_effect,
			text: text,
			bg: "rgba(255,255,255,0.7)",
			color: "#000",
			maxSize: "",
			waitTime: -1,
			source: "'.BASE_URL.'images/img.svg",
			textPos: "vertical",
			fontSize: "",
			onClose: function(el) {}
		});
		var checkDownlProd = false;
		$.ajax({
			type: "GET",
			dataType: "json",
			url: "/api/storefront/cart",
			success: function (res) {
				if(res.length > 0){
					if(res[0]["id"] != undefined){
						var cartId = res[0]["id"];
						var cartCheck = res[0]["lineItems"];
						checkDownlProd = checkOnlyDownloadableProducts(cartCheck);
						if(cartId != ""){
							$.ajax({
								type: "GET",
								dataType: "json",
								url: "/api/storefront/checkouts/"+cartId,
								success: function (cartres) {
									var cartData = window.btoa(JSON.stringify(cartres));
									var billingAddress = "";
									var consignments = "";
									var bstatus = 0;
									var sstatus = 0;
									if(typeof(cartres.billingAddress) != "undefined" && cartres.billingAddress !== null) {
										billingAddress = cartres.billingAddress;
										bstatus = billingAddressValdation(billingAddress);
									}
									if(checkDownlProd){
										if(typeof(cartres.consignments) != "undefined" && cartres.consignments !== null) {
											consignments = cartres.consignments;
											sstatus = shippingAddressValdation(consignments);
										}
									}
									if(bstatus ==0 && sstatus == 0){
										$.ajax({
											type: "POST",
											dataType: "json",
											crossDomain: true,
											url: "'.BASE_URL.'authentication.php",
											dataType: "json",
											data:{"authKey":key,"cartId":cartId,cartData:cartData},
											success: function (res) {
												$("#247cardsteampayment").waitMe("hide");
												if(res.status){
													var data = res.data;
													window.location.href=data;
												}
											},error: function(){
												$("#247cardsteampayment").waitMe("hide");
											}
										});
									}else{
										alert("Please Select Billing Address and Shipping Address");
										$("#247cardsteampayment").waitMe("hide");
									}
								},error: function(){
									$("#247cardsteampayment").waitMe("hide");
								}
							});
						}
					}
				}
			},error: function(){
				$("#247cardsteampayment").waitMe("hide");
			}
		});
		
	});
});
function billingAddressValdation(billingAddress){
	var errorCount = 0;
	if(typeof(billingAddress.firstName) != "undefined" && billingAddress.firstName !== null && billingAddress.firstName !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.lastName) != "undefined" && billingAddress.lastName !== null && billingAddress.lastName !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.address1) != "undefined" && billingAddress.address1 !== null && billingAddress.address1 !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.email) != "undefined" && billingAddress.email !== null && billingAddress.email !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.city) != "undefined" && billingAddress.city !== null && billingAddress.city !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.stateOrProvince) != "undefined" && billingAddress.stateOrProvince !== null && billingAddress.stateOrProvince !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.postalCode) != "undefined" && billingAddress.postalCode !== null && billingAddress.postalCode !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.country) != "undefined" && billingAddress.country !== null && billingAddress.country !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.phone) != "undefined" && billingAddress.phone !== null && billingAddress.phone !== "") {
		
	}else{
		errorCount++;
	}
	
	return errorCount;
}

function shippingAddressValdation(shippingAddress){
	var errorCount = 0;
	if(shippingAddress.length > 0){
		if(typeof(shippingAddress[0].shippingAddress) != "undefined" && shippingAddress[0].shippingAddress !== null && shippingAddress[0].shippingAddress !== "") {
			shippingAddress = shippingAddress[0].shippingAddress;
			if(typeof(shippingAddress.firstName) != "undefined" && shippingAddress.firstName !== null && shippingAddress.firstName !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.lastName) != "undefined" && shippingAddress.lastName !== null && shippingAddress.lastName !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.address1) != "undefined" && shippingAddress.address1 !== null && shippingAddress.address1 !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.city) != "undefined" && shippingAddress.city !== null && shippingAddress.city !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.stateOrProvince) != "undefined" && shippingAddress.stateOrProvince !== null && shippingAddress.stateOrProvince !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.postalCode) != "undefined" && shippingAddress.postalCode !== null && shippingAddress.postalCode !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.country) != "undefined" && shippingAddress.country !== null && shippingAddress.country !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.phone) != "undefined" && shippingAddress.phone !== null && shippingAddress.phone !== "") {
				
			}else{
				errorCount++;
			}
		}
	}else{
		errorCount++;
	}
	return errorCount;
}
function checkOnlyDownloadableProducts(cartData){
	var status = false;
	if(cartData != ""){
		if(cartData.physicalItems.length > 0 || cartData.customItems.length > 0){
			status = true;
		}
		else{
			if(cartData.digitalItems.length > 0){
				status = false;
			}
		}
	}
	return status;
}
';
		$filename = 'custom_script.js';
		$res = saveFile($filename,$filecontent,$folderPath);
	}
}
?>