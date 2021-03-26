<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
      <title>CardStream</title>
      <!-- Bootstrap -->
      <link href="css/bootstrap.css" rel="stylesheet">
      <link href="css/main.css" rel="stylesheet">
      <link href="css/media.css" rel="stylesheet">
      <link href="sofia/stylesheet.css" rel="stylesheet">
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
      <section class="content-area">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="row">
                     <div class="box">
                        <div class="col-md-12">
                           <h1>Hello!</h1>
                           <h4>We’re happy to have you here!</h4>
						   <?php
								$error=0;
								if(isset($_REQUEST['error']) && $_REQUEST['error'] == 1){
									$error=1;
								} 
							?>
							<div><span id="error_show" style="color:red;<?= ($error == 1)?'':'display:none;' ?>" ><?= base64_decode($_REQUEST['errorMsg']) ?> </span></div>
							<form method="POST" action="signupValid.php" >
                              <div class="form-group">
                                 <label for="exampleInputEmail1">Enter your email iD</label>
                                 <input type="email" name="email_id" class="form-control" id="exampleInputEmail1" placeholder="hello@cardstream.com">
                              </div>
                              <div class="form-group">
                                 <label for="exampleInputPassword1">Password</label>
                                 <input type="password" id="password" name="password" class="form-control1" data-toggle="password" placeholder="••••••••••">
                              </div>
                              <button type="submit" class="btn btn-primary btn-lg btn-block">Create New Account</button>
							</form>
                        </div>
                        <div class="col-md-12 vendor-logo text-center"><img src="images/vendor_logo.jpg"></div>
                     </div>
                  </div>
               </div>
               <div class="col-md-12 signin text-center">I'm already a member. <a href="login.php">Sign In</a></div>
            </div>
         </div>
      </section>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="js/jquery.min.js"></script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <script src="js/bootstrap.min.js"></script>
      <script type="text/javascript" src="js/bootstrap-show-password.min.js"></script>
      <script type="text/javascript">
         $("#password").password('toggle');
         
      </script>
   </body>
</html>