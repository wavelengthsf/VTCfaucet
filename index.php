<?php
/*
Main page - will display different results based on sent parameters
(c) Paul Unterberg (wavelength@gmail.com) 

*/
//Turn off error display in Prod
ini_set( "display_errors", 0);

//Shutdown function if we hit an error 
function shutdown() {
    $error = error_get_last();
    if ($error['type'] == E_ERROR) {
   ob_clean();
   # add in your DB logging routine here, removed from this source
header("Location: internal_error.html");
  }
}
register_shutdown_function('shutdown');
  require_once('recaptchalib.php');
  $publickey = ""; // you got this from the signup page at recaptcha

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Vertcoin faucet giving a small amount of Vertcoin free to an email address.">

	<title>Vertcoin Faucet</title>
	

    <!-- Bootstrap -->
    <link href="css/bootstrap.css?key=2934" rel="stylesheet">
	<link href="css/cover.css" rel="stylesheet">
	<script type="text/javascript" src="http://challenge.asirra.com/js/AsirraClientSide.js"></script>

	<style type="text/css">
#animation-container { 
animation: inout 3s; 
animation-iteration-count: 1;
-webkit-animation: inout 3s; /* Safari % Chrome */
-webkit-animation-iteration-count: 1; 
margin: auto;

} 

@keyframes inout { 
0%   { transform: scale(0, 0) } 
25%   { transform: scale(2, 2) }
50%   { transform: scale(0, 0) }
100%  { transform: scale(1, 1) } 
}

@-webkit-keyframes inout { /* Safari % Chrome */ 
0%   { -webkit-transform: scale(0, 0) } 
25%  { -webkit-transform: scale(2, 2) }
50%  { -webkit-transform: scale(0, 0) }
100% { -webkit-transform: scale(1, 1) } 
} 


 .inner-circle { 
background-image: url('vtcround.gif');
background-repeat: no-repeat; 
height: 205px;
width: 205px;
margin: auto;
} 
	</style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  
<script type="text/javascript">
//JS to validate fields 
function validateA(){
var sendbtn = document.getElementById('getcoin');
var addylen = $('#wallet').val().length;
var addrfield = document.getElementById('wallet');

var leftchar = $('#wallet').val().substr(0,1);

if (addylen!=34)
  {
$('#VTCAddress').addClass("has-error");
$('#aError').css('display','block');
sendbtn.disabled = true;

  }
    else {
$('#VTCAddress').removeClass("has-error");
$('#aError').css('display','none');

sendbtn.disabled = false;
//
}
if (leftchar!="V")
  {
$('#VTCAddress').addClass("has-error");
$('#aError').css('display','block');

sendbtn.disabled = true;
  }
  else {
$('#VTCAddress').removeClass("has-error");

$('#aError').css('display','none');
sendbtn.disabled = false;
}}

function validateE(){
var emlen = $('#email').val().length;
var sendbtn = document.getElementById('getcoin');
var x = document.getElementById('email');
 var atpos=x.value.indexOf("@");
 var dotpos=x.value.lastIndexOf(".");
 if (atpos<1 && emlen != 0 || dotpos<atpos+2 && emlen != 0 || dotpos+2>=x.length && emlen != 0) 
  {
$('#EMAddress').addClass("has-error");

$('#eError').css('display','block');
sendbtn.disabled = true;
  } else {
$('#EMAddress').removeClass("has-error");
$('#eError').css('display','none');
sendbtn.disabled = false;
}}

</script>
  </head>
 
  <body>

    <div class="site-wrapper">

      <div class="site-wrapper-inner">

        <div class="cover-container">

          <div class="masthead clearfix">
            <div class="inner">
              <h3 class="masthead-brand"></h3>


            </div>

          </div>

          <div class="inner cover">


<?php

//Clear anything cached 
$mode = 0;

//Check if the variables are set, and switch between posts and gets as needed. Strip out bad things

if (isset($_GET['mode'])) {$mode = $_GET['mode'];}
if (isset($_POST['mode'])) {$mode = $_POST['mode'];}
if (isset($_GET['email'])) {$em = htmlspecialchars($_GET['email']);}
if (isset($_GET['amt'])) {$amt = htmlspecialchars($_GET['amt']);}


//Strip out bad stuff from mode
$mode = htmlspecialchars($mode);

//Mode is our controller - it tells us what code to show on the page
//Based on modes, show different messages
//
switch ($mode)
{
case 1:
echo "		  			  						<!--<img src=\"img/vtclogo3.gif\" />-->
<br> <br> 
<div id=\"animation-container\">
<div class=\"inner-circle\">
</div></div>
<div class=\"alert alert-success\"> <strong>Check your email!  </strong> <br> The faucet is sending you ".$amt."  VTC! We've sent a verification code to the email address: ". $em . "<br> To verify your email, visit: <a href=\"verify\"> Verify Email </a> </div>";

break;

case 2:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong>Over limit! </strong> <br> Sorry, we show that you've already received coins at that address for this time period. </div>
<br>";

break;

case 3:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong> Not enough coins! </strong> <br> I'm sorry - I don't have enough coins to send you right now. I will send you coins once more are donated. </div>
<br>";

break;

case 4:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong> Invalid Email! </strong> <br> I'm sorry - your email appears to be bad. </div>
<br>";

break;
case 5:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong> Over IP Limit! </strong> <br> I'm sorry - your IP has already gotten several drips </div>
<br>";

break;

case 6:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong> Disposable email  </strong> <br> Due to people scamming, you can't use that email provider. </div>
<br>";

break;

case 7:

echo "<img src=\"img/vtclogo3.gif\" /><br> <br> <form action=\"dripp.php\" method=\"Post\">
<div class=\"alert alert-danger\"> <strong> Recaptcha failed  </strong> <br> Are you human?! </div>
<br>
			<table style=\"width: 87%\"> 


			<tr> <td style=\"width: 13%;  margin-top: 10px;\"> Wallet Address</td> <td style=\"width: 87%;  margin-top: 10px;\"><div class=\"input-group\" id=\"VTCAddress\">
 <span class=\"input-group-addon\" id=\"curSYm\">VTC</span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" onblur=\"validateA()\" placeholder=\"ex: VazJmQNApG49CF2gAbK6EZpsz42tZFTbNi\" id=\"wallet\" name=\"wallet\" length=\"75\" required>  <br></div></tr>
  <tr><td colspan=\"2\">&nbsp; <div id=\"aError\" style=\"display: none;color:red;\"> Bad address! </div></td></tr>
			<tr> <td style=\"width: 33%;  margin-top: 10px;\"> Email Address</td> <td style=\"width: 67%;  margin-top: 10px;\"> <div class=\"input-group\" id=\"EMAddress\">			
  <span class=\"input-group-addon\">&nbsp;&nbsp;@&nbsp;&nbsp;</span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" placeholder=\"ex: you@example.com\" id=\"email\" onblur=\"validateE()\" name=\"email\" length=\"75\" required> <br></div>
 </td> </tr>
   <tr><td colspan=\"2\"> &nbsp; <div id=\"eError\" style=\"display: none\"> <span style=\"color:red;\"> Bad email! </span> </div></td></tr>
   <tr><td> &nbsp; </td> <td>
"
 .recaptcha_get_html($publickey)
 ."	
 </td></tr>
			<tr> <td colspan=\"2\" style=\"text-align: right\"> <input type=\"Submit\" id=\"getcoin\" value=\"Get Coins\" class=\"btn btn-primary\" style=\"hover: #eee; background-color:#3f824d; margin-top: 10px; height: 50px; width: 66%\" ></td> </tr>
			</table>
			
			</form>";
break;

case 8:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong> Invalid Email  </strong> <br> Due to people scamming, you can't use that email address. </div>
<br>";

break;


case 9:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong> Scammer Alert  </strong> <br>You're an ahole - trying to scam more? </div>
<br>";

break;


default:

echo "<img src=\"img/vtclogo3.gif\" /><br> <br> <form action=\"dripp.php\" method=\"Post\" onsubmit=\"return MySubmitForm();\">
			<table style=\"width: 87%\"> 


			<tr> <td style=\"width: 13%;  margin-top: 10px;\"> Wallet Address</td> <td style=\"width: 87%;  margin-top: 10px;\"><div class=\"input-group\" id=\"VTCAddress\">
 <span class=\"input-group-addon\" id=\"curSYm\">VTC</span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" onblur=\"validateA()\" placeholder=\"ex: VazJmQNApG49CF2gAbK6EZpsz42tZFTbNi\" id=\"wallet\" name=\"wallet\" length=\"75\" required>  <br></div></tr>
  <tr><td colspan=\"2\">&nbsp; <div id=\"aError\" style=\"display: none;color:red;\"> Bad address! </div></td></tr>
			<tr> <td style=\"width: 33%;  margin-top: 10px;\"> Email Address</td> <td style=\"width: 67%;  margin-top: 10px;\"> <div class=\"input-group\" id=\"EMAddress\">			
  <span class=\"input-group-addon\">&nbsp;&nbsp;@&nbsp;&nbsp;</span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" placeholder=\"ex: you@example.com\" id=\"email\" onblur=\"validateE()\" name=\"email\" length=\"75\" required> <br></div>
 </td> </tr>
   <tr><td colspan=\"2\"> &nbsp; <div id=\"eError\" style=\"display: none\"> <span style=\"color:red;\"> Bad email! </span> </div></td></tr>
   <tr><td> &nbsp; </td> <td>
"
 .recaptcha_get_html($publickey)
 ."	
 </td></tr>
			<tr> <td colspan=\"2\" style=\"text-align: right\"> <input type=\"Submit\" id=\"getcoin\" value=\"Get Coins\" class=\"btn btn-primary\" style=\"hover: #eee; background-color:#3f824d; margin-top: 10px; height: 50px; width: 66%\" ></td> </tr>
			</table>
			
			</form>";

	
break;
}
?>
              <a href="#" class="btn btn-lg btn-default">Learn more</a> </p> -->
           <br>  <p> Vertcoin is an ASIC-resistant crypto-currency. This faucet will give you a small amount of coins (between .002 and .03) to help get you started. There's a limit of one drip per email. </p><br> Other VTC links: <br>
			<ul class="nav nav-pills nav-justified">
<li><a href="http://vtc.li"  target="_blank">Zero-trust VTC Web Wallet </a></li>
				<li> <a href="http://vertcoin.org"  target="_blank"> Vertcoin Official Site </a></li>
				<li> <a href="http://reddit.com/r/vertcoin" target="_blank">Vertcoin Subreddit </a> </li>
				<li> <a href="http://vertcoinforum.com/" target="_blank">Official Vertcoin Forum </a> </li>
				<li> <a href="verify">Verify Email </a> </li>
</ul>

                  <div style="padding-top:15px; position: relative;">
			<br><br>
			<span style="font-size: 12px; color: black;"> Donate to the faucet: VazJmQNApG49CF2gAbK6EZpsz42tZFTbNi </span><br>
		

			<br> <span style="font-size: 9px; color: black;"> Created by <a href="http://www.reddit.com/user/deadwavelength/" target="_blank"> wavelength </a> | CSS by Bootstrap | Cover template by  <a href="https://twitter.com/mdo" target="_blank">@mdo</a> | <a href="faucet-stats">Stats</a></span>


          <div class="mastfoot">
   
            </div>
          </div>

        </div>
</div>
</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
	<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
//put in your GA code here
  ga('create', '', '');
  ga('send', 'pageview');

</script>
  </body>
</html>
