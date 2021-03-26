<?php

if(!isset($_SESSION)){
	session_start();
}

if(isset($_SESSION['is247Auth']) && $_SESSION['is247Auth']){
	if(isset($_SESSION['is247Email']) && !empty($_SESSION['is247Email'])){
	}else{
		header("Location:login.php");
	}
}else{
	header("Location:login.php");
}