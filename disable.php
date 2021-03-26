<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<?php
/**
	* Feed List Page
	* Author 247Commerce
	* Date 22 FEB 2021
*/
if(!isset($_SESSION)){
	session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once('db-config.php');
require_once('config.php');
require_once('hooks.php');

/*require 'log-autoloader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;*/

$conn = getConnection();
$email_id = '';
if(isset($_SESSION['is247Email'])){
	$email_id = $_SESSION['is247Email'];
}

	if(!empty($email_id)){
		$stmt = $conn->prepare("select * from cardstream_token_validation where email_id='".$email_id."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		//print_r($result[0]);exit;
		if (isset($result[0])) {
			$result = $result[0];
			if(!empty($result['merchant_id']) && !empty($result['cardstream_signature']) && !empty($result['acess_token']) && !empty($result['store_hash'])){
				$sellerdb = $result['sellerdb'];
				$acess_token = $result['acess_token'];
				$store_hash = $result['store_hash'];
				deleteScripts($sellerdb,$acess_token,$store_hash,$email_id);
				$usql = "update cardstream_token_validation set is_enable=0 where email_id='".$email_id."'";
				//echo $usql;exit;
				$stmt = $conn->prepare($usql);
				$stmt->execute();
				header("Location:dashboard.php");
			}else{
				header("Location:dashboard.php");
			}
		}else{
			header("Location:dashboard.php");
		}
	}else{
		header("Location:dashboard.php");
	}

function deleteScripts($sellerdb,$acess_token,$store_hash,$email_id){
	$rStatus = 0;
	$conn = getConnection();
	$stmt = $conn->prepare("select * from cardstream_scripts where script_email_id='".$email_id."'");
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();
	//print_r($result[0]);exit;
	if (count($result) > 0) {
		foreach($result as $k=>$v){
			//$auth_token = '4ir2j1tpf5cw3pzx7ea4ual2jrei8cd';
			$header = array(
				"X-Auth-Client: ".$acess_token,
				"X-Auth-Token: ".$acess_token,
				"Accept: application/json",
				"Content-Type: application/json"
			);
			$request = '';
			//print_r($request);exit;
			$url = STORE_URL.$store_hash.'/v3/content/scripts/'.$v['script_code'];
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"DELETE");
			curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$res = curl_exec($ch);
			//print_r($res);exit;
			curl_close($ch);
			$log_sql = 'insert into api_log(email_id,type,action,api_url,api_request,api_response) values("'.$email_id.'","BigCommerce","script_tag_deletion","'.addslashes($url).'","'.addslashes($request).'","'.addslashes($res).'")';
			//echo $log_sql;exit;
			$conn->exec($log_sql);
			if(empty($res)){
				$sql = 'delete from cardstream_scripts where script_id='.$v['script_id'];
				//echo $sql;exit;
				$conn->exec($sql);
				$rStatus++;
			}
		}
	}
}
?>