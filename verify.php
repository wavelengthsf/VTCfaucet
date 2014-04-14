<?php
/*
Email Verification page - Verifies the email address and code
(c) Paul Unterberg (wavelength@gmail.com) 

*/
ini_set( "display_errors",0); //no errors in Prod
ob_start;
function shutdown() {
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
   ob_clean();
   // Enter your DB logging here.
header("Location: internal_error.html");
die;
  }
}

//Use Mailgun's API
require 'vendor/autoload.php';
use Mailgun\Mailgun;


/*
DB and Mail API area. Reset this with your info! 
*/ 
$ini_array = parse_ini_file("db_settings.conf"); //Load the config file. This should be set to be not publicly accessible on your server.
$dbname= "";
$host="";
$dbuser="";
$dbpass="";
$mapikey=""; //Get this from Mailgun
$maildomain=""; //Get this from Mailgun 
$con=mysqli_connect($host,$dbuser,$dbpass,$dbname);

/*
Get inputs and sanitize them
*/
$wallet = $_POST['wallet'];
$email = $_POST['email'];
$wallet = htmlspecialchars($wallet);
$email = htmlspecialchars($email);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vertcoin Faucet</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.css?key=2934" rel="stylesheet">
	<link href="css/cover.css" rel="stylesheet">
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
function validateE(){
//Validate input with JS
var sendbtn = document.getElementById('getcoin');

var x = document.getElementById('email');
 var atpos=x.value.indexOf("@");
 var dotpos=x.value.lastIndexOf(".");
 if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
  {

$('#email').addClass("has-error");
$('#eError').css('display','block');
sendbtn.disabled = true;
  } else {

$('#email').removeClass("has-error");

$('#eError').css('display','none');
sendbtn.disabled = false;

}}

function validateC(){
var sendbtn = document.getElementById('getcoin');
var codelen = $('#code').val().length;

if (codelen<10)
  {
$('#code').addClass("has-error");
$('#cError').css('display','block');
sendbtn.disabled = true;

  }
    else {
$('#code').removeClass("has-error");
$('#cError').css('display','none');

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
$mode = 0;
if (isset($_GET['mode'])) {$mode = $_GET['mode'];}
if (isset($_POST['mode'])) {$mode = $_POST['mode'];}

$mode = htmlspecialchars($mode);

/*
Again, mode is serving as our controller, and tell the page what content to display.


*/

switch ($mode)
{

case 1:
if (isset($_GET['txid'])) {$txid = $_GET['txid'];}
echo "	<!--<img src=\"img/vtclogo3.gif\" />-->
<br> <br> 
<div id=\"animation-container\">
<div class=\"inner-circle\">
</div></div> <div class=\"alert alert-success\"> <strong>Vertcoin Sent! </strong> <br> We've sent some Vertcoin to the address provided. Enjoy! <br> Transaction ID: ".$txid." </div>";

break;

case 2:
echo "<img src=\"img/vtclogo3.gif\" /> <br> <br> <div class=\"alert alert-danger\"> <strong>Invalid Match! </strong> <br> I don't have a record of that email and code pair. Try again. </div>
<br>";
echo "<form action=\"vrfy.php\" method=\"Post\">
			<table style=\"width: 77%\"> 



			<tr> <td style=\"width: 33%;  margin-top: 10px;\"> Email Address</td> <td style=\"width: 67%;  margin-top: 10px;\"> <div class=\"input-group\">			
  <span class=\"input-group-addon\">@</span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" placeholder=\"ex: you@example.com\" onblur=\"validateE()\" id=\"email\" name=\"email\" length=\"75\" required> <br></div>
 </td> </tr>
   <tr><td colspan=\"2\"> &nbsp; <div id=\"eError\" style=\"display: none\"> <span style=\"color:red;\"> Bad email! </span> </div></td></tr>
   <tr> <td style=\"width: 33%;  margin-top: 10px;\"> Code</td> <td style=\"width: 67%;  margin-top: 10px;\"> <div class=\"input-group\">			
  <span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-lock\"></span></span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" placeholder=\"ex: JSHJ387373\" onblur=\"validateC()\" id=\"code\" name=\"code\" length=\"75\" required> <br></div>
 </td> </tr>
   <tr><td colspan=\"2\"> &nbsp; <div id=\"cError\" style=\"display: none\"> <span style=\"color:red;\"> Bad code! </span>  </td></tr>

	<tr> <td colspan=\"2\" style=\"text-align: right\"> <input type=\"Submit\" id=\"getcoin\" value=\"Verify\" class=\"btn btn-primary\" style=\"hover: #eee; background-color:#3f824d; margin-top: 10px; height: 50px; width: 66%\" > <br> <a href=\"index\">Cancel</a></td> </tr>
			</table>
			
			</form>";
break;

default:
echo "<img src=\"img/vtclogo3.gif\" /><br><form action=\"vrfy.php\" method=\"Post\">
			<table style=\"width: 77%\"> 



			<tr> <td style=\"width: 33%;  margin-top: 10px;\"> Email Address</td> <td style=\"width: 67%;  margin-top: 10px;\"> <div class=\"input-group\">			
  <span class=\"input-group-addon\">@</span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" placeholder=\"ex: you@example.com\" onblur=\"validateE()\" id=\"email\" name=\"email\" length=\"75\" required> <br></div>
 </td> </tr>
   <tr><td colspan=\"2\"> &nbsp; <div id=\"eError\" style=\"display: none\"> <span style=\"color:red;\"> Bad email! </span> </div></td></tr>
   <tr> <td style=\"width: 33%;  margin-top: 10px;\"> Code</td> <td style=\"width: 67%;  margin-top: 10px;\"> <div class=\"input-group\">			
  <span class=\"input-group-addon\"><span class=\"glyphicon glyphicon-lock\"></span></span>
  <input type=\"text\" class=\"form-control\" style=\"width:350px;\" placeholder=\"ex: JSHJ387373\" onblur=\"validateC()\" id=\"code\" name=\"code\" length=\"75\" required> <br></div>
 </td> </tr>
   <tr><td colspan=\"2\"> &nbsp; <div id=\"cError\" style=\"display: none\"> <span style=\"color:red;\"> Bad code! </span>  </td></tr>

	<tr> <td colspan=\"2\" style=\"text-align: right\"> <input type=\"Submit\" id=\"getcoin\" value=\"Verify\" class=\"btn btn-primary\" style=\"hover: #eee; background-color:#3f824d; margin-top: 10px; height: 50px; width: 66%\" > <br> <a href=\"index\">Cancel</a></td> </tr>
			</table>
			
			</form>";
break;
}
?>
		  </div>

					
            <div class="inner">
<div class="alert alert-info" style="background-color: #FAFAFA; -webkit-box-shadow: 0px 0px 4px #4195fc;
       -moz-box-shadow: 0px 0px 4px #4195fc;
            box-shadow: 0px 0px 4px #4195fc;">        
<span style="font-size:10px"><strong>Advertisement</strong></span><br>Want more ways to get Vertcoin? Try mining using these cards:
<br> 
<a href="http://www.amazon.com/gp/product/B00FR6XPL8/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00FR6XPL8&linkCode=as2&tag=vertcfauce-20" target="_blank">~350 KH/s - MSI R9 280X</a> (I use these in my mining computer)<br>
<a href="http://www.amazon.com/gp/product/B00FR6XP6I/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00FR6XP6I&linkCode=as2&tag=vertcfauce-20" target="_blank">~200 KH/s - MSI R9 270X</a><br>
<a target="_blank" href="http://www.amazon.com/s/?_encoding=UTF8&camp=1789&creative=390957&field-keywords=Radeon%20R9&linkCode=ur2&tag=vertcfauce-20&url=search-alias%3Daps">Other Radeon Cards</a><img src="https://ir-na.amazon-adsystem.com/e/ir?t=vertcfauce-20&l=ur2&o=1" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />
</div></p>
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

  ga('create', '', '');
  ga('send', 'pageview');

</script>
  </body>
</html>
