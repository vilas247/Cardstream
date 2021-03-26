<?php
/**
	* Token Validation Page
	* Author 247Commerce
	* Date 22 MAR 2021
*/
require_once('config.php');
require_once('db-config.php');

if(isset($_REQUEST['email_id']) && isset($_REQUEST['password'])){
	$conn = getConnection();
	$email_id = @$_REQUEST['email_id'];
	if(!empty($email_id)){
		$stmt = $conn->prepare("select * from cardstream_token_validation where email_id='".$email_id."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		//print_r($result[0]);exit;
		if (isset($result[0])) {
			header("Location:signup.php?error=1&errorMsg=".base64_encode("Email already Exists Please Login."));
		}else{
			$sellerdb = '247c'.strtotime(date('y-m-d h:m:s'));
			$sql = 'insert into cardstream_token_validation(email_id,sellerdb,password) values("'.$email_id.'","'.$sellerdb.'","'.$_REQUEST['password'].'")';
			$conn->exec($sql);
			header("Location:login.php?signup=1");
		}
	}else{
		header("Location:signup.php");
	}
}else{
	header("Location:signup.php");
}

/* creating folder Based on Seller */
function createFolder($sellerdb,$email_id){
	$conn = getConnection();
	if(!empty($sellerdb)){
		$folderPath = './'.$sellerdb;
		$filecontent = '$("head").append("<script src=\"'.BASE_URL.'js/247loader.js\" ></script>");';
		$filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.BASE_URL.'css/247loader.css\" />");';
		$filecontent .= '$(document).ready(function() {
	var stIntId = setInterval(function() {
		if($(".checkout-step--payment").length > 0) {
			if($("#247dnapayment").length == 0){
				$(".checkout-step--payment .checkout-view-header").after(\'<div id="247dnapayment" class="checkout-form" style="padding:1px"><form id="carStreamForm" name="cardstreampayent"><input type="hidden" id="247cardstreamkey" value="'.base64_encode(json_encode($email_id)).'" ><button type="submit" class="button button--action button--large button--slab optimizedCheckout-buttonPrimary" style="background-color: #424242;border-color: #424242;color: #fff;">CardStream Payments</button></form></div>\');
				clearInterval(stIntId);
			}
		}
	}, 1000);
	$("body").on("click","#carStreamForm",function(e){
		e.preventDefault();
		var text = "Please wait...";
		var current_effect = "bounce";
		var key = $("body #247cardstreamkey").val();
		$("#247dnapayment").waitMe({
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
												$("#247dnapayment").waitMe("hide");
												if(res.status){
													var data = JSON.parse(window.atob(res.data));
													window.DNAPayments.openPaymentWidget(data);
												}
											},error: function(){
												$("#247dnapayment").waitMe("hide");
											}
										});
									}else{
										alert("Please Select Billing Address and Shipping Address");
										$("#247dnapayment").waitMe("hide");
									}
								},error: function(){
									$("#247dnapayment").waitMe("hide");
								}
							});
						}
					}
				}
			},error: function(){
				$("#dnapaymentForm").waitMe("hide");
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