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
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
      <title>V9</title>
      <!-- Bootstrap -->
      <link href="css/bootstrap.css" rel="stylesheet">
      <link href="css/main.css" rel="stylesheet">
      <link href="css/media.css" rel="stylesheet">
      <link href="sofia/stylesheet.css" rel="stylesheet">
	  <link href="css/custom.css" rel="stylesheet">
      <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
   </head>
   <body style="background-color:#fbfbfd">
      <section class="top">
         <div class="container">
            <div class="row">
               <div class="col-md-2 col-xs-4 logo">
                  <a href="#">
                  <img src="images/logo.png">
                  </a>
               </div>
               <div class="col-md-10 ">
                  <div class="navbar navbar-inverse navbar-static-top " role="navigation">
                     <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        </button>
                     </div>
                     <div class="collapse navbar-collapse navbar-ex1-collapse">
                        <ul class="nav navbar-nav navbar-right">
                           <li class="active"><a href="#">Home </a></li>
                           <li><a href="#">About</a></li>
                           <li><a href="#">Brands</a></li>
                           <li><a href="#">Partners</a></li>
                           <li><a href="#">Contact</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <section class="card-stream">
         <div class="container">
			<?php
				$stmt = $conn->prepare("select * from cardstream_token_validation where email_id='".$email_id."'");
				$stmt->execute();
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
				$result = $stmt->fetchAll();
				//print_r($result[0]);exit;
				if (isset($result[0])) {
					$result = $result[0];
					if(!empty($result['merchant_id']) && !empty($result['cardstream_signature']) && !empty($result['acess_token']) && !empty($result['store_hash'])){
						$payment_option = $result['payment_option'];
						$enabled = false;
						if($result['is_enable'] == 1){
							$enabled = true;
						}
			?>
				<div class="row">
					<div class="white-bg dash-head">
						<form action="updateSettings.php" method="POST" >
						<div class="col-md-12">
							<ul class="user-detail">
								<li>
									<h5 class="user-head">Name</h5>
									<p class="user-para">
										<?= $result['email_id'] ?>
									</p>

									<h5 class="user-head">Merchant Id</h5>
									<p class="user-para"><?= $result['merchant_id'] ?></p>
								</li>
								<li>
									<h5 class="user-head">CardStream Signature</h5>
									<p class="user-para"><?= $result['cardstream_signature'] ?></p>
									
								</li>
								<li>
									<h5 class="user-head">Access Token</h5>
									<p class="user-para"><?= $result['acess_token'] ?></p>
									
									<h5 class="user-head">Store Hash</h5>
									<p class="user-para"><?= $result['store_hash'] ?></p>
									
								</li>
								<li>
									<h5 class="user-head">Payment Options</h5>
									
									<div class="radio">
										<label class="radio-container">
										<input type="radio" name="payment_option" <?= ($payment_option == "CFO")?'checked':'' ?> value="CFO" >
										<span class="radio-checkmark"></span>
										Capture on order placed
									</label>
									</div>
									<div class="radio">
										<label class="radio-container">
											<input type="radio" name="payment_option" <?= ($payment_option == "CFS")?'checked':'' ?> value="CFS" />
											<span class="radio-checkmark"></span>
											Capture on Shipment
										</label>
									</div>
								</li>
								<li>
									<h5 class="user-head">Action</h5>
									<label class="switch">
									  <input id="actionChange" type="checkbox" <?= ($enabled)?'checked':'' ?> value="<?= ($enabled)?'1':'0' ?>" />
									  <span class="slider round"></span>
									</label>
								</li>
							</ul>
							<div class="col-md-12 section-update">
								<button type="submit" class="btn btn-order">UPDATE</button>
							</div>
						</div>
						</form>
					</div>
				</div>
			<?php
					}else{
			?>
            <div class="col-md-12">
               <div class="box1">
                  <div class="row">
                     <div class="col-md-12">
                        <div class="brd pb30">
                           Add your payment gateway to BigCommerce Store <a href="#" class="add_btn glyphicon glyphicon-plus"><span>Add</span></a>
                        </div>
                     </div>
					 <form class="form-horizontal" action="validateToken.php" method="POST" >
                     <div class="col-md-12">
                        <div class="row brd pt30 pb30">
                           <div class="col-md-12 pb20">MID ID and Signature Key will be available from Vendor name goes here (or Cardstream)</div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="exampleInputEmail1">MID:</label>
                                 <input type="email" class="form-control" name="merchant_id" required id="exampleInputEmail1" placeholder="••••••••••••••••••••••••••••••••••••••••••">
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="exampleInputEmail1">Signature Key:</label>
                                 <input type="email" class="form-control" name="cardstream_signature" required id="exampleInputEmail1" placeholder="••••••••••••••••••••••••••••••••••••••••••">
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-12">
                        <div class="row pt30 pb30">
                           <div class="col-md-12 pb20">You can get the below details from BigCommerce Admin Panel. Click here to find how</div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="exampleInputEmail1">BigCommerce Store API Token:</label>
                                 <input type="email" class="form-control" required name="acess_token" id="exampleInputEmail1" placeholder="••••••••••••••••••••••••••••••••••••••••••">
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label for="exampleInputEmail1">BigCommerce Store Hash:</label>
                                 <input type="email" class="form-control" required name="store_hash" id="exampleInputEmail1" placeholder="••••••••••••••••••••••••••••••••••••••••••">
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-12">
                        <div class=" pb30">
                           <div class="c-left">Status of your Payment Gateway </div>
                           <div class="c-right">
                              <select data-menu>
                                 <option selected>Enable</option>
                                 <option>Disable</option>
                              </select>
                           </div>
                        </div>
                     </div>
					 </form>
                  </div>
               </div>
            </div>
            <div class="col-md-12 pt30 text-right"><a href="#" class="add_btn glyphicon glyphicon-save-file"><span>Save</span></a> </div>
			<?php }
			} ?>
		 </div>
      </section>
	  <!-- Modal -->
		<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
			  <div class="modal-content">
				<div class="modal-header">
				  <h5 class="modal-title" id="exampleModalLongTitle"><span><img src="images/icons/trash-purple.svg" style="margin-top: -5px;"></span> <span class="purple">Disable CardStream</span>  </h5>
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button>
				</div>
				<div class="modal-body" id="modalContent">
				  Are you sure you want to disable <strong>CardStream in BigCommerce?</strong>.
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-order" id="cancelConfirm" data-dismiss="modal">Cancel</button>
				  <button type="button" class="btn btn-order" id="deleteConfirm">Disable</button>
				</div>
			  </div>
			</div>
		  </div>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="js/jquery.min.js"></script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <script src="js/bootstrap.min.js"></script>
      <script type="text/javascript" src="js/bootstrap-show-password.min.js"></script>
	  <style>
		.modal-backdrop{
			opacity: 0!important;
		}
	  </style>
      <script type="text/javascript">
		$(document).ready(function() {
			$(".modal-backdrop").remove();
			$("#password").password('toggle');
			$('body').on('change','#actionChange',function(){
				var val = $(this).val();
				if(val == "0"){
					var url = 'enable.php';
					window.location.href = url;
				}else{
					$('body #exampleModalCenter').modal('show');
				}
			});
			$('body').on('click','#deleteConfirm',function(e){
				var url = 'disable.php';
				window.location.href = url;
			});
			$('body').on('click','#cancelConfirm,.close',function(e){
				$('body #exampleModalCenter').modal('hide');
				$('#actionChange').trigger('click');
			});
		});
      </script>
      <script>
         $('select[data-menu]').each(function() {
         
             let select = $(this),
                 options = select.find('option'),
                 menu = $('<div />').addClass('select-menu'),
                 button = $('<div />').addClass('button'),
                 list = $('<ul />'),
                 arrow = $('<em />').prependTo(button);
         
             options.each(function(i) {
                 let option = $(this);
                 list.append($('<li />').text(option.text()));
             });
         
             menu.css('--t', select.find(':selected').index() * -41 + 'px');
         
             select.wrap(menu);
         
             button.append(list).insertAfter(select);
         
             list.clone().insertAfter(button);
         
         });
         
         $(document).on('click', '.select-menu', function(e) {
         
             let menu = $(this);
         
             if(!menu.hasClass('open')) {
                 menu.addClass('open');
             }
         
         });
         
         $(document).on('click', '.select-menu > ul > li', function(e) {
         
             let li = $(this),
                 menu = li.parent().parent(),
                 select = menu.children('select'),
                 selected = select.find('option:selected'),
                 index = li.index();
         
             menu.css('--t', index * -41 + 'px');
             selected.attr('selected', false);
             select.find('option').eq(index).attr('selected', true);
         
             menu.addClass(index > selected.index() ? 'tilt-down' : 'tilt-up');
         
             setTimeout(() => {
                 menu.removeClass('open tilt-up tilt-down');
             }, 500);
         
         });
         
         $(document).click(e => {
             e.stopPropagation();
             if($('.select-menu').has(e.target).length === 0) {
                 $('.select-menu').removeClass('open');
             }
         })
         
         
      </script>
   </body>
</html>