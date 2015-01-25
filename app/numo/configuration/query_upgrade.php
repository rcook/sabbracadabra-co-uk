<?php
// Include database connection information
if (defined("DB_CONNECTION_INFO_LOCATION")) {
	require(DB_CONNECTION_INFO_LOCATION);
} else {
	// include database connection information
	require("database_connection_information.php");
}

include("../classes/Numo.php");

if (!class_exists("Numo")) {
	class Numo { function Numo(){} }
}

$cookieDomain = ".".str_replace("www.", "", NUMO_SERVER_ADDRESS);

session_set_cookie_params(0, '/', $cookieDomain);

//Include helper function information
require("../classes/functions.php");

DEFINE('MODULES_FOLDER_NAME', 'modules');

$modules = array();
$extensions = array();
$modulesToInstall = array();
$extensionsToInstall = array();

// Include master class files
require("../classes/Database.php");

// check to database connection
if(!$dbObj->valid_connection) {
    print "Invalid Database Connection";
	
	exit();
} else {
   $dbObj->query("CREATE TABLE IF NOT EXISTS `pending_upgrades` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `transaction_id` varchar(255) NOT NULL, `verification_id` varchar(255) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
   $result = $dbObj->query("SELECT * FROM pending_upgrades WHERE site_id='".NUMO_SITE_ID."' AND transaction_id='{$_POST[tid]}'");
   if ($_POST['tid'] == "") {
	 print "INVALID transaction id";
   } else if (mysql_num_rows($result) == 0) {																							  
	   $ch = curl_init(); //init 
	
		curl_setopt($ch, CURLOPT_URL, "http://numo.server-apps.com/upgrade/query/"); //setup request to website to check license key
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
		curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'tid='.$_POST['tid'].'&tos='.$_POST['tos']);
	
		// send request and save response to variable
		$response = @curl_exec($ch);
		print $response; 
		if (strstr($response, "VERIFIED")) {
		   $dbObj->query("INSERT INTO pending_upgrades (site_id, transaction_id, verification_id) VALUES ('".NUMO_SITE_ID."', '{$_POST[tid]}', '{$_POST[tos]}')");
		   print "INSERT COMPLETE";
	
		} else {
			print "COULD NOT VERIFY {$_POST['tid']} :: {$_POST['tos']}";
		}
   } else {
	   print "RECORD ALREADY EXISTS";
   }
}


?>