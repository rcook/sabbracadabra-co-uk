<?php
if (file_exists("classes/Numo.php")) {
  include("classes/Numo.php");
} else {
	class Numo { }
	$numo = new Numo();
}

// Include database connection information
require("configuration/database_connection_information.php");

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



//include initialization process code
include("configuration/initialization_process.php");

// database connection information has not been entered. Prompt to install module
if ((!defined('DATABASE_HOST') || $_POST['next_step'] != "") && $_POST['next_step'] != "done") {
	// include initialization display
	
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

// Include language syntax constant variables
require("configuration/syntax.php");

if (defined('NUMO_SYNTAX_NUMO_TIMEZONE_CODE') && NUMO_SYNTAX_NUMO_TIMEZONE_CODE != "") {
  date_default_timezone_set(NUMO_SYNTAX_NUMO_TIMEZONE_CODE);
}

// get a list
$installedModules = array();

// query database for names of installed modules
$sql     = "SELECT name FROM modules";
$results = $dbObj->query($sql);

while ($row = mysql_fetch_array($results)) {
	$installedModules[$row['name']] = 1;
}

// try to open the MODULES folder
if ($modulesFolder = @opendir(MODULES_FOLDER_NAME)){
	//add settings
	if((@include MODULES_FOLDER_NAME."/settings/configuration/includes.php")) {
		$modules[] = "settings";
	}

//add settings
	if((@include MODULES_FOLDER_NAME."/accounts/configuration/includes.php")) {
		$modules[] = "accounts";
	}


	//cycle thru each file in the MODULES folder
	while ($moduleFolderName = readdir($modulesFolder)) {
		if ($HTTP_HOST == DEMO_SERVER && !$numoModules["$moduleFolderName"]) {
			continue;
		}
		
		//ingore if item named with periods or starts with an underscore
		if($moduleFolderName == "." || $moduleFolderName == ".." || substr($moduleFolderName, 0, 1) == "_"){
			continue; //exit WHILE loop
		}

		//check to see if item is a folder
		if (is_dir(MODULES_FOLDER_NAME."/".$moduleFolderName)){
			if(array_key_exists($moduleFolderName,$installedModules)) {
				//try to include configuration file that is present in modules
				if((@include MODULES_FOLDER_NAME."/".$moduleFolderName."/configuration/includes.php")) {
					//include file present do additional tasks to setup module
					if($moduleFolderName != "settings" && $moduleFolderName != "accounts") {
						$modules[] = $moduleFolderName;
					}
				}
			} else {
				//add to list of modules needing to be installed
				$modulesToInstall[$moduleFolderName] = $moduleFolderName;
			}
		}
	}


}

// confirm visitor is authorized (logged in)
require("login.php");
require("classes/Access.php");
?>