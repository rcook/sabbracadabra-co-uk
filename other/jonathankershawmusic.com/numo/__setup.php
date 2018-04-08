<?php

// Include database connection information
if (defined("DB_CONNECTION_INFO_LOCATION")) {
	require(DB_CONNECTION_INFO_LOCATION);
} else {
	// include database connection information
	require("configuration/database_connection_information.php");
}
//if (file_exists("classes/Numo.php")) {
  include("classes/Numo.php");
//} else {
	//class Numo { }
	//$numo = new Numo();
//}
if (!class_exists("Numo")) {
	class Numo { function Numo(){} }
}

$cookieDomain = ".".str_replace("www.", "", NUMO_SERVER_ADDRESS);

session_set_cookie_params(0, '/', $cookieDomain);

//Include helper function information
require("classes/functions.php");

numo_secure("backend");

// start session
numo_session_start();


//print $cookieDomain;
//exit;
// if logout requested kill session information
if($_GET['cmd'] == "exit") {
	session_unset(); //clear all session information
	header("Location: ./"); //refresh page
}

DEFINE('MODULES_FOLDER_NAME', 'modules');

$modules = array();
$extensions = array();
$modulesToInstall = array();
$extensionsToInstall = array();

foreach ($_POST as $key => $value) {
	//print $key."=".$value."<br>";
}
//include initialization process code
require("configuration/initialization_process.php");

// database connection information has not been entered. Prompt to install module
if ((!defined('DATABASE_HOST') || $_POST['next_step'] != "") && !$doneInstall) {
	// include initialization display
	//print DATABASE_HOST;
	//print "<br> next step: ".$_POST['next_step'];
	include('configuration/initialization.php');
	exit();
}

//check to see if key code is valid for domain
//if(!valid_key_code()) {
//	include('configuration/invalid_key.php');
//	exit();
//}

// Include master class files
require("classes/Database.php");

// check to database connection
if(!$dbObj->valid_connection) {
	//print "bad connection";

	$_POST['next_step'] = 2; //set script to jump to database connection information prompt
	$installError = true; //flag error occured

	// include initialization display
	include('configuration/initialization.php');
	exit();
} else {
	$sql = "SHOW TABLES";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		// tables setup in database.  assume the module is install correctly

	} else {
		// no tables found, try to run the initialization, but skip to the account setup section
		$_POST['next_step'] = "0";
		$_POST['database_host'] = DATABASE_HOST;
		$_POST['database_name'] = DATABASE_NAME;
		$_POST['database_username'] = DATABASE_USERNAME;
		$_POST['database_password'] = DATABASE_PASSWORD;

		// include initialization display
		include('configuration/initialization.php');
		exit();
	}
}

if (!defined("NUMO_SITE_ID")) {
  $numo->assertSiteID();
  $numoDomain = $numo->getRegisteredDomain();
  
} else {
	//print NUMO_SITE_ID;
}

$numo->loadSettings();

if (!defined("ABSOLUTE_ROOT_PATH")) {
  define("ABSOLUTE_ROOT_PATH", "");
}
// Include language syntax constant variables
require("configuration/syntax.php");

if (defined('NUMO_SYNTAX_NUMO_TIMEZONE_CODE') && NUMO_SYNTAX_NUMO_TIMEZONE_CODE != "") {
  date_default_timezone_set(NUMO_SYNTAX_NUMO_TIMEZONE_CODE);
} else {
  date_default_timezone_set("America/Chicago");
}

// get a list
$installedModules = array();

// query database for names of installed modules
$sql     = "SELECT name FROM modules WHERE site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

while ($row = mysql_fetch_array($results)) {
	if ($row['status'] == "1" || $row['status'] == "") {
	  $installedModules[$row['name']] = 1;
	}
}

// try to open the MODULES folder
if ($modulesFolder = @opendir(MODULES_FOLDER_NAME)){


// add settings
	if((@include MODULES_FOLDER_NAME."/accounts/configuration/includes.php")) {
		$modules[] = "accounts";
	}

	//add settings
	if((@include MODULES_FOLDER_NAME."/settings/configuration/includes.php")) {
		$modules[] = "settings";
	}

	// cycle thru each file in the MODULES folder
	while ($moduleFolderName = readdir($modulesFolder)) {

		//print $moduleFolderName."<br>";
		if (($HTTP_HOST == DEMO_SERVER || $doDemoModules) && !$numoModules["$moduleFolderName"]) {
			//print "continuing";
			continue;
		}
		//print "Yup<br>";

		//ingore if item named with periods or starts with an underscore
		if($moduleFolderName == "." || $moduleFolderName == ".." || substr($moduleFolderName, 0, 1) == "_"){
			continue; //exit WHILE loop
		}
 
		if (is_dir(MODULES_FOLDER_NAME."/".$moduleFolderName)){
			if(array_key_exists($moduleFolderName,$installedModules)) {
				//try to include configuration file that is present in modules
				if((@include MODULES_FOLDER_NAME."/".$moduleFolderName."/configuration/includes.php")) {
					//include file present do additional tasks to setup module
					if($moduleFolderName != "settings" && $moduleFolderName != "accounts") {
						$modules[] = $moduleFolderName;
					}
				} else {
				}
			} else if (REMOTE_SERVICE !== true) {
				//add to list of modules needing to be installed
				$modulesToInstall[$moduleFolderName] = $moduleFolderName;
			}
		} else {
		}
	}

}
// confirm visitor is authorized (logged in)
require("login.php");
require("classes/Access.php");
?>