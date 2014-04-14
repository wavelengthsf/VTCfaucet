<?php
/*
Drip page - will randomly choose a drip amount, validate data and log transacations
(c) Paul Unterberg (wavelength@gmail.com) 

*/
ini_set( "display_errors",0);
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
register_shutdown_function('shutdown'); 
session_start();
  require_once('recaptchalib.php');
  $privatekey = "";//from recaptcha 

//Use Mailgun's API
require 'vendor/autoload.php';
use Mailgun\Mailgun;

//Email validation function
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) 
        && preg_match('/@.+\./', $email);
}



function rand_string( $length ) {
//Generate a random string
    $chars = "abcdefghijklmnpqrstuvwxyz123456789";
$em1 =  substr(str_shuffle($chars),0,$length);

return $em1; 

}

function getUserIpAddr()
//Get user's IPs
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) //if from shared
    {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //if from a proxy
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
        return $_SERVER['REMOTE_ADDR'];
    }
}

if (!isset($_POST['email'])) {header('Location: index');}

$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
  if (!$resp->is_valid) {
    // What happens when the CAPTCHA was entered incorrectly
    header("Location: index?mode=7");
	die;
  }   //here to handle a successful verification
else
{

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

$passph = rand_string(15); //generate passphrase for user
$passph = strtoupper($passph);

$passph2 = rand_string(5); //generate passphrase for entropy 
$passph2 = strtoupper($passph2);
  
$wallet = $_POST['wallet'];
$email = $_POST['email'];

//Check if its a valid email or from an allowed provider. If not, kick them back to the main page.
//Note, .htaccess rules let Apache ignore the .php here for extra security.
//--------------------------------
if (isValidEmail($email) == FALSE ) {header('Location: index?mode=4');}
if (isSaneEmail($email) == 1)  {header('Location: index?mode=6');}

//Get the address and email requested - sanitize them
$wallet = htmlspecialchars($wallet);
$email = htmlspecialchars($email);

//Generate the amount of the drip
$tosend =rand(2, 5);
$tosend2 =rand(2, 4);
$bonus =rand(1, 2);
$tosend2 = $tosend2 * .001;
$tosend = $tosend * .001;
$tosend = $tosend + $tosend2;
$tosend = $tosend * $bonus;

//Set the limit per IP and per address
$limtip = 3;
$addresslimit = 1;

//Get the user's IP and convert it to a string
$IP = getUserIpAddr();

$IP = strval($IP);

/*
Block out several popular email anonymizers. This will prevent people from using them and getting drips. 

*/
$notallowed = array('yopmail.com','tempemail.net','hideme.be','cuvox.de','dayrep.com','einrot.com','fleckens.hu','gustr.com','jourrapide.com'
,'rhyta.com','superrito.com','teleworm.us','eyepaste.com','armyspy.com', 'fakeinbox.com','grr.la','sharklasers.com','spam4.me','guerrillamail.com‎'
,'mailinator.com','daintly.com','guerrillamail.de','notsharingmy.info','dayrep.com','free-bitcoin.esy.es');

 $domain = array_pop(explode('@', $email));
 $domain = strtolower($domain);
 
    if ( in_array($domain, $notallowed))
    {
		//Bad email player;
		header('Location: index?mode=6');
		die;
    }

//Run a check on the DB for the user's IP
// Dynamic items, we don't want SQL Injection: IP  - sanitized above

	
$ipaa = "select count(IP) as CNT FROM vtcf_user where IP = '";
$ipaa = $ipaa.$IP."';";

$resultX = mysqli_query($con,$ipaa);

	while($rowa = mysqli_fetch_array($resultX))
	  {
		$ipcnt=$rowa['CNT'];	

	}

//If the IP count comes back as greater than our limit, deny the drip.	
if (intval($ipcnt) > $limtip ) 
{  header('Location: index?mode=5'); die;}


//Check the DB for the same address being used 
// Using dynamic items, we don't want SQL Injection: Wallet - sanitized above


$ipaa1 = "select count(wallet) as CNT FROM vtcf_user where wallet = '";
$ipaa1 = $ipaa1.$wallet."';";

$resultX = mysqli_query($con,$ipaa1);

	while($rowa = mysqli_fetch_array($resultX))
	  {
		$ipcntw=$rowa['CNT'];	

	}
//If it is used over our limit here, kick the user out ;

if (intval($ipcntw) > $addresslimit ) 
{  header('Location: index?mode=2'); die;}

/*
Check to make sure the email address isn't used
*/
$getOID = "select count(email) as CNT from vtcf_user where email = '".$email."'";

$result1 = mysqli_query($con,$getOID);

	while($row = mysqli_fetch_array($result1))
	  {
		$dbCode=$row['CNT'];	

	}

if ($dbCode > 0) { header('Location: index?mode=2'); die;}

else
{
/*
So far, we've checked:

1) Email is not used over the limit (1)
2) Wallet address not used over the limit (set above)
3) IP not used over the limit (set above)
4) Email is not from an anonymous provider (prevents fraud)

Now, we want to insert the record into the DB and send out the confirm email

Inserting dynamic items, we don't want SQL Injection:

Email - sanitized above
Tosend - Drip amount, server generated, clean
Passphrase 1 and 2 - Server generated, clean

Wallet address - sanitized
IP - generated by the server and then sanitized 

*/

$userinsert="
INSERT INTO `vtcf_user` (`email`, `dt`, `status`, `sent`, `phrase`, `entropy`,`wallet`,`IP`) 
VALUES ('".$email."', CURRENT_TIMESTAMP, '0', '".$tosend ."', '".$passph."', '".$passph2."','".$wallet."','".$IP."');";
$result = mysqli_query($con,$userinsert);
if (mysqli_connect_errno())
  {
  //DB error logging stuff - don't forget to add this for your system
  }

  /* 
 Email copy for the bot.  
  */
$mailcopy = "Hello,

I'm the bot that runs vertcoin-faucet.com and I need to verify this email address. 

Enter the following verification code: ".$passph ."

on http://www.vertcoin-faucet.com/verify

Once you've verified this email, I will send  ".$tosend." VTC to the address you gave
=================
Thanks";

// First, instantiate the SDK with your API credentials and define your domain. 
// I used mailgun, this part will change if you use another email provider. 
$mg = new Mailgun($mapikey);
$domain = "vertcoin-faucet.com";

$maillarray= array('from' => 'bot@vertcoin-faucet.com', 
                                'subject' => 'Vertcoin Faucet: Verify Email');
$maillarray['to'] = $email;
$maillarray['text'] = $mailcopy;

$mg->sendMessage($domain,$maillarray);

//Success! Send the user back to index with the correct controller

$loc="Location: index?mode=1&amt=".$tosend."&email=".$email;
header($loc);
die;
}}

?>
