<?php
/*
Email Verification processor  - Verifies the email address and code, sends out the VTC
(c) Paul Unterberg (wavelength@gmail.com) 

*/
ob_start();

session_start();
ini_set( "display_errors", 0);



function shutdown() {
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
   # Do whatever logging you need to here
   header("Location: internal_error.html");

  }
}
register_shutdown_function('shutdown'); 

//Load required JSONRPCClient library

require_once 'jsonRPCClient.php';

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) 
        && preg_match('/@.+\./', $email);
}


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


if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  //Do your DB error logging here
  }


//If the values aren't set, kick the user back to index  
if (!isset($_POST['email'])) { header('Location: index'); }
if (!isset($_POST['code'])) { header('Location: index'); }

//Get email and code values, sanitize them
$email = $_POST['email'];
$code = $_POST['code'];

$email = htmlspecialchars($email);
$code = htmlspecialchars($code);

//Check if email valid and allowed
if (isValidEmail($email) == FALSE ) {header('Location: verify?mode=2');}
 $notallowed = array('yopmail.com', 'eyepaste.com', 'fakeinbox.com','grr.la','sharklasers.com','spam4.me','guerrillamail.com‎','mailinator.com','daintly.com','guerrillamail.de','notsharingmy.info','dayrep.com','free-bitcoin.esy.es');
 $domain = array_pop(explode('@', $email));
    if ( in_array($domain, $notallowed))
    {
		header('Location: index.php?mode=6');
		die;
    }

// Verify the code against the DB 
// Email is input, which is sanitized

$getOID = "select phrase,sent,status,wallet from vtcf_user where status=0 and email = '".$email."';";
$result1 = mysqli_query($con,$getOID);
	while($row = mysqli_fetch_array($result1))
	  {
		$dbCode=$row['phrase'];	
		$amt=$row['sent'];
		$status=$row['status'];
		$wallet=$row['wallet'];
	}

//If we didn't get a code, that's a problem

if (strlen($dbCode) == 0) { header('Location: verify?mode=2');}



if ($dbCode == strtoupper($code))
{
//

/*
This uses the jsonRPCClient library to connect to the URL to connect to your RPC wallet server over SSL

Replace:
<<username>> - RPC user
<<password>> - RPC password
<<IP>> - Hostname or IP of wallet
<<port>> - Port of the wallet
For example, if your RPC user was satoshi and the password was btc, and the host was google.com potn 9889, you'd have:
https://satoshi:btc@google.com:9889

To send, we need to add a wallet pass phrase
<<passphrase>> - Wallet phrase
*/
$shardhost = "https://<<username>>:<<password>>@<<IP>>:<<port>>/";
$vcoin = new jsonRPCClient($shardhost); //Connect to the wallet using JSON RPC
$getbalance=$vcoin->getbalance()."\n"; //Get wallet balance

$bal=floatval($getbalance);

//If we have less than the amount plus the variable here (.05 for VTC faucet), then trigger error
if ($bal + .05 > $amt)
{
$unlockwallet=$vcoin->walletpassphrase('<<passphrase>>',10);
$TXID=$vcoin->sendtoaddress($wallet,floatval($amt))."\n";
$lockwallet=$vcoin->walletlock();

//Log that the transfer happened
$setSent = "update vtcf_user set status=1 where status=0 and email = '".$email."';";
$result1 = mysqli_query($con,$setSent);

//Success - send user to prev screen and show the TX ID
$redir="Location: verify?mode=1&txid=".$TXID;
header($redir);
}
else
{
header('Location: index?mode=3');

}

}
else
{

//If we have less than .05 coins in balance, throw an error.
//Log error here
header('Location: verify?mode=2');
}

?>
