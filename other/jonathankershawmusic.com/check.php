<?php

/************************************************************************/
/* Purpose: To check if the requested page is available for the website */
/* visitor to view.  If the visitor does have access to view the page   */
/* display and replace any component code bits within the document;     */
/* otherwise, display an error message page explaining the issue        */
/************************************************************************/

  error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
 // error_reporting(E_ALL);
  //error_reporting(0);
// change headers so form information doesn't expire
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter("must-revalidate");
if ($_GET['subfolder'] != "") {
	//print "subfolder = {$subfolder}";
}
//var_dump($_GET);
if ($_GET['cmd'] == "show_server_environment") {
  phpinfo();
  exit;
}
//Include helper function information
require("numo/classes/functions.php");
if (defined("DB_CONNECTION_INFO_LOCATION")) {
	require(DB_CONNECTION_INFO_LOCATION);
} else {
	// include database connection information
	require("numo/configuration/database_connection_information.php");
}//
//print "yup";
// start session
if (function_exists("numo_session_start")) {
  numo_session_start();
  if ($_SESSION['account_id'] != 0 && $_SESSION['account_id'] != "") {
		$_SESSION['last_active'] = date("Y-m-d H:i:s");
  }
  //print $_SESSION['last_active'];
}
//session_start();

ob_start();
// clean POST inputs
foreach($_POST as $key => $value) {
	if(is_string($value)) {
		//print "before: $value <br>";
		$_POST[$key] = htmlentities($value, ENT_QUOTES, 'UTF-8');
			//	print "after: {$_POST[$key]} <br>";

		//$_POST['content'] = htmlentities($_POST['content']
	}
	if (REMOTE_SERVICE === true) {
		//print "P $key = $value <br>";
	}
}
$_GET['where'] = str_replace("../", "", $_GET['where']);
$_GET['where'] = ltrim($_GET['where'], "/");
// clean GET inputs
foreach($_GET as $key => $value) {
	if(is_string($value)) {
		$_GET[$key] = htmlentities($value, ENT_QUOTES, 'UTF-8');

	}
	if (REMOTE_SERVICE === true) {
		if ($key == "_") {
			unset($_GET["$key"]);
		}
		//print "G $key = $value <br>";
	}
}

// initialize error id variable
$SYSTEM_ERROR_ID = 0;
$installed = true;


// file name that was requested
$fileName = $_GET['where'];
//print "FN: {$fileName}<br>";
//exit;
// check if fileName value is empty ... is so look for default page
if ($fileName == "" || is_dir($fileName)) {
	//build list of default file names to check for
	$defaultFileNames = array();
	$defaultFileNames[] = "index.htm";
	$defaultFileNames[] = "index.html";
	$defaultFileNames[] = "default.htm";
	$defaultFileNames[] = "default.html";
	$defaultFileNames[] = "index.php";

	//cycle through default file names
	for($i = 0; $i < count($defaultFileNames); $i++) {
		//check to see if file exists in website
		if (is_dir($fileName)) {
		  if (trim($_GET['where'], "/") == $_GET['where']) {
		    header("Location: ".$_GET['where']."/");
		    exit;
		  }
		  $defaultFileNames["$i"] = trim($_GET['where'], "/")."/".$defaultFileNames["$i"];
		}

		if(file_exists($defaultFileNames[$i])) {
			//update file name variable
			$fileName = $defaultFileNames[$i];
			//breakout of loop
			break;
		}
	}
}
//Include master class files
require("numo/classes/Database.php");

//if (file_exists("numo/classes/Numo.php") || @readlink("numo/classes/Numo.php") !== false) {
  include("numo/classes/Numo.php");
//  print "v";
//} else {
	if (!class_exists("Numo")) {
	class Numo { function Numo(){} }
	}
	//print "c";
//}
//print "f".$fileName;
//check if connected to database
if(!$dbObj->valid_connection) {
	$installed = false;

	//check if file exists on the server
	if(file_exists($fileName)) {
		//display requested file

		display_file($fileName);

	//file could not be found
	} else {
		//show page not found page
		print "File Not Found";
	}

	exit();
} else {
	//print "a";
}



if (!defined("NUMO_SITE_ID")) {
	//print $numo."y";
	//exit;
  $numo->assertSiteID();

}

//print "zz".NUMO_SITE_ID;
$numo->loadSettings();
// if logout requested kill session information
if($_GET['cmd'] == "exit") {
	//clear all session information
	session_unset();

    $returnURL = $_SESSION['HTTP_REFERER'];
    if ($returnURL == "") {
      $returnURL = $_SERVER['REQUEST_URI'];
    }


	//refresh page
	header("Location: ".str_replace("?cmd=exit", "", $returnURL));
}

if (REMOTE_SERVICE === true) {

	if (DIRECT_PROCESSING === true) {
		// define(RENDER_RELATIVE_PATH, "./"); // was ../ but changed Nov 19, 2012
	  $pathData = explode("/", NUMO_FOLDER_PATH);
	  array_pop($pathData);

	  $MANAGE_NUMO_LOCATION = "/".$numo->getRootFolder(false, true)."/manage.numo";
	  $MANAGE_NUMO_LOCATION_LOCAL = "http://".$numo->getRootFolder()."/manage.numo";
	  $fileName = "http://".$numo->getRootFolder()."/".$fileName."?cmd=show_code";
	  //print $numo->getRootFolder();
	//print "b";
	} else {
	   define(RENDER_RELATIVE_PATH, "../");

	  $MANAGE_NUMO_LOCATION_LOCAL = "http://".$numo->getRootFolder()."/numo.htm";
	  $MANAGE_NUMO_LOCATION = "http://".$numo->getRootFolder()."/numo.htm";
	//print "x";
	}
	//print "v";
} else {

	define(RENDER_RELATIVE_PATH, "");
    define(ABSOLUTE_ROOT_PATH, "");

	$MANAGE_NUMO_LOCATION = substr(NUMO_FOLDER_PATH,0, -5)."manage.numo";
	$MANAGE_NUMO_LOCATION_LOCAL = $MANAGE_NUMO_LOCATION;
}
$cache = "";
//print NUMO_SITE_ID;
//Include language syntax constant variables
//print "current encoding is ".mysql_client_encoding()."<br>";
$dbObj->query("SET NAMES UTF8");
//print "current encoding is ".mysql_client_encoding()."<br>";

require("numo/configuration/syntax.php");

if (defined('NUMO_SYNTAX_NUMO_TIMEZONE_CODE') && NUMO_SYNTAX_NUMO_TIMEZONE_CODE != "") {
  date_default_timezone_set(NUMO_SYNTAX_NUMO_TIMEZONE_CODE);
} else {
  date_default_timezone_set("America/Chicago");
}

//number of permission.  only used if the file is found to be protected by the "is_protected" function 
$permissionId = 0;


if(strstr($fileName,"process.numo")) {
	display_error_file("process_pending_request.php");
 // print "d";
} else if(strstr($fileName,"manage.numo")) {
//	print "x";

	display_error_file("component.php");
 // print "3";

} else if (strstr($fileName,"component.numo")) {
	//print "a";
	display_error_file("blank_component.php");
	//return;

//check to see if the file is protected or not
} else if($dbObj->valid_connection && is_protected($fileName)) {
	//print "b";
	//check to see if visitor has permission to view the file
	if(has_view_permissions()) {
		//print "has view";
		//check if user account is pending their activation
		if($_SESSION['activated'] == 0) {
			//1 is the number for the LANGUAGE_SYNTAX for this message
			$SYSTEM_ERROR_ID = "ACCOUNT_PENDING_ACTIVATION_ALERT";

			//display pending activation page message
			display_error_file();

			//clear session information so that next they will be prompted next time they wish to see a protected file
			session_unset();

		//check if user account is pending administrative review
		} else if($_SESSION['pending'] == 1) {

			//1 is the number for the LANGUAGE_SYNTAX for this message
			$SYSTEM_ERROR_ID = "ACCOUNT_PENDING_REVIEW";

			//display pending review page message
			display_error_file();

			//clear session information so that next they will be prompted next time they wish to see a protected file
			session_unset();
		} else if ((REMOTE_SERVICE === true && DIRECT_PROCESSING === true) && $numo->remoteFileExists($fileName)) {
			//print "yup";
			display_file($fileName);

		//check if file exists on the server
		} else if(file_exists($fileName)) {
			//display requested file
			display_file($fileName);

		//file could not be found
		} else {

			//show page not found page
			$SYSTEM_ERROR_ID = "NUMO_FILE_NOT_FOUND";
			display_error_file();
		}

	//do not have permission to view file
	} else {
		//not logged in
		if(!isset($_SESSION['type_id'])) {
			//include('numo/error_pages/login.htm');
			$SYSTEM_ERROR_ID = "RESTRICTED_LOGIN_REQUIRED";
			display_error_file();

		//is logged in but access hasn't been granted
		} else {
			//show no permissions page
			$SYSTEM_ERROR_ID = "RESTRICTED_PERMISSION_NOT_GRANTED";
			display_error_file();
		}
	}

//not a protected file. display.
} else {
	//print $fileName."<br>";

	if ((REMOTE_SERVICE === true && DIRECT_PROCESSING === true && $numo->remoteFileExists($fileName)) || file_exists($fileName)) {
	//check if file exists on the server
//	if(file_exists($fileName)) {
		//print "d";
		//display requested file
		//print "e";
		//exit;

		display_file($fileName);

	//file could not be found
	} else {
		//print "b";
		//show page not found page
		$SYSTEM_ERROR_ID = "NUMO_FILE_NOT_FOUND";
		display_error_file();
	}
}
ob_end_flush();
update_check_header();

/******************************************************/
/*                 FUNCTIONS                          */
/******************************************************/

// check to see if a document has been marked as protected or not
//if (!function_exists("is_protected")) {
function is_protected($file) {
	global $dbObj;
	global $permissionId;
	global $numo;

	//check to see if Access Control module installed
	$sql = "SELECT name FROM modules where name='access_control' AND site_id='".NUMO_SITE_ID."'";
	//print $sql;
	$results = $dbObj->query($sql);

	if($row = mysql_fetch_array($results)) {
		//query the database to see if the document has been marked as protected
		//$sqlQuery = "SELECT id FROM protected_files WHERE file_name='".$file."'";
		//$file = str_replace('$', '\$', $file);
		$file = str_replace("'", "", $file);
		$file = str_replace('"', '', $file);
		if (REMOTE_SERVICE === true) {
			$file = str_replace("http://".$numo->getRootFolder()."/", "", $file);
			$file = str_replace("?cmd=show_code", "", $file);
		}
		// this was changed march 13, 2012 as the regular expression was seemed unrequired and interfered with special characters
		//$sql = "SELECT id FROM `protected_files` WHERE (SELECT '^{$file}' REGEXP protected_files.file_name)";
		$sql = "SELECT id FROM `protected_files` WHERE file_name='{$file}' AND site_id='".NUMO_SITE_ID."'";
		//print $sql;
		$result = $dbObj->query($sql);

		//if result found the document is protected
		if($row = mysql_fetch_array($result)) {
		//	print "yup is protected";
			$permissionId = $row['id'];
			return true;

		// attempt to match the folder name
		// this new functionality added February 12, 2013 to handle the case of protection of folders, which was loosely
		// supported by the REGEX above.
		} else {
			$folderPathParts = explode("/", $file);
			$currentfolderPath = "";
			foreach ($folderPathParts as $part) {
				$currentFolderPath .= $part."/";
				$sql = "SELECT id FROM `protected_files` WHERE file_name='{$currentFolderPath}' AND site_id='".NUMO_SITE_ID."'";
				//print $sql;
				$result = $dbObj->query($sql);
				if($row = mysql_fetch_array($result)) {
					$permissionId = $row['id'];
					return true;
				}
			}
		}

		//free SQL results
		mysql_free_result($result);
		mysql_free_result($results);
	}

	//no result found, file not protected
	return false;
}
//}
function has_view_permissions() {
	global $dbObj;
	global $permissionId;
	global $_SESSION;

	//if visitor not logged into a account they will not have permission to view the file
	if(!isset($_SESSION['type_id'])) {
		return false;
	}

	if (isset($_SESSION['account_id'])) {
	   $query = "SELECT type_id FROM accounts a, `types` t WHERE a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."' AND a.id='{$_SESSION['account_id']}'";
	   $result = $dbObj->query($query);
	   if($row = mysql_fetch_array($result)) {
	     $_SESSION['type_id'] = $row['type_id'];

	   } else {
	   }
	}
	//query the database to see if the vistors account group has permission to view the file
	$sqlQuery = "SELECT id FROM permissions WHERE protected_file_id=".$permissionId." AND type_id=".$_SESSION['type_id'];
	//print $sqlQuery;
	$result = $dbObj->query($sqlQuery);
//print mysql_error();
	//if result found, group has permissions
	if($row = mysql_fetch_array($result)) {

		return true;

	//else, check if the users account has permissions
	} else {
	//print "nope";
	//exit;
		//free SQL result
		mysql_free_result($result);

		//query the database to see if the vistors ACCOUNT has permission to view the file
		$sqlQuery = "SELECT id FROM user_permissions WHERE protected_file_id=".$permissionId." AND `account_id`=".$_SESSION['account_id'];
		//print $sqlQuery;
		$result = @$dbObj->query($sqlQuery);

		if($row = @mysql_fetch_array($result)) {
			return true;
		}
	}

	//free SQL result
	@mysql_free_result($result);

	//no results found. no permissions granted.
	return false;
}

//display requested error page.
function display_error_file($fileName = 'numo.htm') {
	global $numo;
	global $SYSTEM_ERROR_ID;
	//if custom error page found display it
	//print $fileName;
	if (REMOTE_SERVICE === true && DIRECT_PROCESSING === true && $fileName == "numo.htm") {

		$remoteFile = "http://".$numo->getRootFolder(true, true)."/numo.htm?cmd=show_code";
		//print $remoteFile;
		display_file($remoteFile);
	} else {
		if(file_exists($fileName)) {
			display_file($fileName);

		//display system default error page
		} else {
			display_file('numo/'.$fileName);
		}
	}
}

//display requested file
function display_file($file) {
	global $dbObj;
	global $_SESSION;
	global $_GET;
	global $installed;
	global $fileName;
	global $numo;

	//get the file extension

  $dotLocation = strrpos($file, ".");
  $extensionData = explode("?", $file);

  $extension = strtolower(substr($extensionData[0], ($dotLocation + 1)));
  $pageDisplay = "";

	//include PHP code files to allow content to be properly processed
	if($extension == "php") {
		//stop diplaying output
		ob_start();

		//set page HTML contents
		include($file);

		//copy buffered print text
		$pageDisplay = ob_get_contents();

		//clear buffered print text (start displaying output again)
		ob_clean();

	//set header and print out file contents
	} else {
		//build array of document types
		$contentType['jpg']  = "image/jpeg";
		$contentType['jpeg'] = "image/jpeg";
		$contentType['gif']  = "image/gif";
		$contentType['png']  = "image/png";
		$contentType['bmp']  = "image/bmp";

		$contentType['swf']  = "application/x-shockwave-flash";
		$contentType['wav']  = "audio/x-wav";
		$contentType['mp3']  = "audio/mpeg";

		$contentType['mp4']  = "video/mpeg";
		$contentType['ogv']  = "video/ogg";
		$contentType['ogg']  = "video/ogg";
		$contentType['webm']  = "video/webm";

		$contentType['zip']  = "application/zip";
		$contentType['doc']  = "application/msword";
		$contentType['docx']  = "application/msword";
		$contentType['pdf']  = "application/pdf";
		$contentType['rtf']  = "application/rtf";
		$contentType['xls']  = "application/octet-stream";
		$contentType['xlsx']  = "application/octet-stream";

		$contentType['js']   = "application/javascript";
		$contentType['css']  = "text/css";
		$contentType['htm']  = "text/html";
		$contentType['html'] = "text/html";
		$contentType['txt']  = "text/plain";
		$contentType['xml']  = "text/xml";
		$contentType['vcf']  = "text/x-vcard";

		// if extension is defined in $contentType array update content type for display
		if (array_key_exists($extension, $contentType)) {


			  header('Content-type: '.$contentType[$extension]);
		} else {
		}
		// if the file is not text based, then just simply output the file
		if (!strstr($contentType[$extension], "text")) {
		  readfile($file);
		  exit;
		}
		//print $file;
		//print "donex";
		//exit;
		//print file contents
		$pageDisplay = file_get_contents($file);
	}
	//print $pageDisplay;
	//print "done";
	//exit;
	//print $pageDisplay;
//print "a";
	if($installed) {
	  //print $fileName."<br>";
	  //print $file."<br>";
	   if (strstr($file, "numo.htm") || strstr($fileName, ".numo")) {
		 $baseLocation = "";

		 $remoteFolder = $numo->getRootFolder(false);
		 $rootFolderSlashes = explode("/", $remoteFolder);
		 if ($remoteFolder == "") {
			 $rootFolderSlashes = array();
		 }

		 if (REMOTE_SERVICE && DIRECT_PROCESSING === true) {
			  $allSlashes = explode("/", str_replace("http://".$numo->getRootFolder(), "", $fileName), -1);
		 } else {
			  $allSlashes = explode("/", $fileName, -1);
		 }

		 $totalSlashes = sizeof($allSlashes);
		 for ($i = sizeof($rootFolderSlashes); $i < sizeof($allSlashes); $i++) {
			 $folderName = $allSlashes["$i"];
			 if ($folderName != "" && $folderName != $fileName) {

			   $baseLocation .= "../";
			 }
		 }
         if ($baseLocation != "") {
		   $pageDisplay = str_replace("<head>",'<head>
<base href="'.$baseLocation.'" />',$pageDisplay);
		 }
	   }
	   if (strstr($pageDisplay, "col-lg") || strstr($pageDisplay, "col-sm") || strstr($pageDisplay, "col-xs") || strstr($pageDisplay, "col-md")) {
		 global $bootstrapVersion;
		 $bootstrapVersion = 3;   
		// print "yup, have version 3";
	   }
		//replace all component code tags and print page display
		if ($_GET['cmd'] == "show_code") {
  		  print $pageDisplay;

		} else if (strstr($contentType["$extension"], "video") || strstr($contentType["$extension"], "application") || strstr($contentType["$extension"], "video")) {
		  print $pageDisplay;

		} else {


			if (strstr($pageDisplay, "bootstrap.min.css") || strstr($pageDisplay, "bootstrap.css")) {
				global $bsStyling;
				$bsStyling = true;
				
				//print "yup";
			} else {
				//print "nope";
			}

			ob_start();
			global $cache;
			$pattern = "/<script type='text\\/javascript' src='(\\.\\.\\/)*?Site\\/javascript\\/jquery-1.3.2.min.js'><\\/script>\r?\n?[\\s]*?(<script type='text\\/javascript' src='(\\.\\.\\/)*?Site\\/javascript\\/jquery.jqDock.min.js'><\\/script>)/";
			$replace = '$2';
			$pageDisplay = preg_replace($pattern, $replace, $pageDisplay);

			$pattern = '/\$\(/';
			$replace = 'jQuery(';
			$pageDisplay = preg_replace($pattern, $replace, $pageDisplay);



			$pattern = '/code.jquery.com\/jquery-latest.min.js/';
			$replace = 'ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js';
			$pageDisplay = preg_replace($pattern, $replace, $pageDisplay);

			$cache = $pageDisplay;
//print "okay";
			$finalDisplay =  preg_replace_callback('/\[NUMO\.(.*?): (.*?)\]/i',"replace_component_tags",$pageDisplay);
			//print "fd:$finalDisplay";
			
			$finalDisplay =  preg_replace_callback('/\<numo module=[\'"](.*?)[\'"] component=[\'"](.*?)[\'"]( params=[\'"](.*?)[\'"])?><\/numo>/i',"replace_component_tags",$finalDisplay);

			$finalDisplay =  preg_replace_callback('/\[NUMO\*(.*?): (.*?)\]/i',"replace_extension_tags",$finalDisplay);

			// update meta keywords, meta description tags in dynamically generated pages
			if (strstr($file, "numo.htm") || strstr($fileName, ".numo")) {
			  $finalDisplay = conditionMetaTags($finalDisplay);
			  $finalDisplay =  preg_replace_callback('/\[NUMO\.(.*?): (.*?)\]/i',"replace_component_tags",$finalDisplay);
			}

			print $finalDisplay;

			ob_flush();

		}


		//check if whois online module is installed
		$sql = "SELECT `name` FROM `modules` WHERE `site_id`=".NUMO_SITE_ID." AND `name`='whois_online' AND `status`=1";
		$whoisinstalledresult = $dbObj->query($sql);

		if ($whoisrow = mysql_fetch_array($whoisinstalledresult)) {
			if(($extension == "htm" || $extension == "html" || $extension == "php" || $extension == "pdf" || $extension == "doc" || $extension == "zip")) {
				$accountId = 0;
				$shopperId = 0;

				if(isset($_SESSION['account_id'])) {
					$accountId = $_SESSION['account_id'];
				}

				if(isset($_SESSION['shopper_id'])) {
					$shopperId = $_SESSION['account_id'];
				}


				if(substr($file,0,5) == "numo/" || $file == "numo.htm") {
					global $SYSTEM_ERROR_ID;
					if($SYSTEM_ERROR_ID != "") {
						$file = $_GET['where'];
					  $dotLocation = strrpos($file, ".");
					  $extension = strtolower(substr($file, ($dotLocation + 1)));
					} else {
						$file = ":NUMO:".ucwords(str_replace("_"," ",$_GET['module'])." ".str_replace("_"," ",$_GET['component']));
					}


				}
				if(REMOTE_SERVICE !== true && ($extension == "htm" || $extension == "html" || $extension == "php" || $extension == "pdf" || $extension == "doc" || $extension == "zip")) {
					// save page hit
					$sql = "INSERT INTO `whois_online_data` (`site_id`,`account_id`,`shopper_id`,`file_name`,`user_agent`,`when`,`ip_address`) VALUES ('".NUMO_SITE_ID."','".$accountId."','".$shopperId."','".$file."','".$_SERVER["HTTP_USER_AGENT"]."','".date("Y-m-d H:i:s")."','".$_SERVER["REMOTE_ADDR"]."')";
					// print $sql;
					$dbObj->query($sql);
				}
			}
		}
	} else {
		print $pageDisplay;
	}
}

//session_destroy();

function conditionMetaTags($pageDisplay) {
	global $metaDescription;
	global $metaKeywords;
	//print "Meta discription: $metaDescription<br>";
	//print "meta keywrods: $metaKeywords<br>";
        $newDisplay = preg_replace('/<meta name=[\'"]Keywords[\'"] content=[\'"](.*)?[\'"]/i',"<meta name='keywords' content=\"[NUMO.SETTINGS: META KEYWORDS]\"",$pageDisplay);

	if (strlen($newDisplay) == 0 || $newDisplay == $pageDisplay) {
	//	print "y";
	  $pageDisplay = str_replace("</title>", " [NUMO.SETTINGS: META TITLE]</title>\n<meta name='keywords' content=\"[NUMO.SETTINGS: META KEYWORDS]\" />", $pageDisplay);
	} else {
	 // print "n";
	}
	
	// in the even that there is no title tag, then we add one.
	if ($newDisplay == $pageDisplay) {
	  $pageDisplay = str_replace("<head>", "<head>\n<title>[NUMO.SETTINGS: META TITLE]</title>\n<meta name='keywords' content=\"[NUMO.SETTINGS: META KEYWORDS]\" />\n", $pageDisplay);
	}
    $newDisplay = preg_replace('/<meta name=[\'"]Description[\'"] content=[\'"](.*)?[\'"]/i',"<meta name='deywords' content=\"[NUMO.SETTINGS: META DESCRIPTION]\"",$pageDisplay);
	if (strlen($newDisplay) == 0 || $newDisplay == $pageDisplay) {
		$pageDisplay = str_replace("</title>", "</title>\n<meta name='description' content=\"[NUMO.SETTINGS: META DESCRIPTION]\" />", $pageDisplay);
	} else {
	 // print "";

	}
	
	

	return $pageDisplay;

}


//callback function that replaces component tags with content
function replace_component_tags($matches) {
	global $dbObj;
	global $numo;
	global $disableAll;
	global $cache;
	global $MANAGE_NUMO_LOCATION;
	global $fileName;
	global $PARAMS;
	global $bsStyling;
	global $bootstrapVersion;
	$bootstrapStyling = $bsStyling;
	$PARAMS = array();
  // var_dump($matches);
	//print $matches[0]."x<br>";
			// global $SYSTEM_ERROR_ID;
            // 		print $SYSTEM_ERROR_ID;
	// separate the component name from any parameters passed
	// if we have a <> new style tag with params="something=somethingelse" then parse this way
	if ($matches[4] != "") {
		$componentName = $matches[2];
		$paramString = str_replace("&amp;", "&", $matches[4]);
		//split params into array like $_GET
		parse_str($paramString, $PARAMS);

	// otherwise, we have a [] old style tag with (something=somethingelse) so parse this way
	} else {
		list($componentName, $paramString) = explode("(", $matches[2]);
		$paramString = str_replace("&amp;", "&", $paramString);
		//split params into array like $_GET
		$paramString = substr($paramString, 0, -1);
		parse_str($paramString, $PARAMS);
	}
	$MANAGE_NUMO_LOCATION_bu = $MANAGE_NUMO_LOCATION;

	if ($PARAMS['push'] != "") {
	  if (REMOTE_SERVICE === true) {
		$MANAGE_NUMO_LOCATION = "http://".$numo->getRegisteredDomain()."/".$PARAMS['push'];
	  } else {
		$MANAGE_NUMO_LOCATION = $PARAMS['push'];
	  }
	}

//var_dump($PARAMS);
	//stop diplaying output
	//print "x";
	//remove spaces from component name and replace with underscores and convert to lower case
	$componentName = strtolower(str_replace(" ", "_", $componentName));

  //  print $componentName."<br>";
	//remove spaces from module name and replace with underscores and convert to lower case

	$matches[1] = strtolower(str_replace(" ", "_", $matches[1]));
	$module = $matches[1];
	//print "{$module} {$componentName}<br>";
	//print moduleOffline($matches[1]);
	ob_start();

    if  (!moduleOffline($module)) {
		//print "{$module} {$componentName}<br>";
		//set page HTML contents
//print "v";
//print $_GET['iframe'];
		if ($_GET['iframe'] == "1") {
			?>
            <link title="default" href="http://<?php echo $numo->getRootFolder()?>/Site/styles/misc/styles.css" 	rel="stylesheet" type="text/css" />
			<link title="default" href="http://<?php echo $numo->getRootFolder()?>/Site/styles/fonts.css" 	rel="stylesheet" type="text/css" />
			<link title="default" href="http://<?php echo $numo->getRootFolder()?>/Site/styles/primary.css" 	rel="stylesheet" type="text/css" />
			<link title="default" href="http://<?php echo NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH; ?>styles/reset-iframe.css" 	rel="stylesheet" type="text/css" />
            <?php

		}
		if ($PARAMS["wrap"] != "") {
			print "<style>";
			include_once("numo/modules/settings/configuration/wrappers/wrap-{$PARAMS[wrap]}.css");
			print "</style>";
			include("numo/modules/settings/configuration/wrappers/wrap-{$PARAMS[wrap]}-start.htm");
		//	print "<div style='background-color: #ff0000'>";
		}
		//print $matches[1]."/".$componentName;
		ob_start();
		//print "start of $module, $componentName<br>";
		include("numo/modules/".$matches[1]."/components/".$componentName.".php");
		//print "end of $module, $componentName<br>";
		$componentDisplay2 = ob_get_contents();
		ob_end_flush();
		if ($PARAMS["wrap"] != "") {
			include("numo/modules/settings/configuration/wrappers/wrap-{$PARAMS[wrap]}-end.htm");
		}
		if ($componentDisplay2 != "") {
		//copy buffered print text
		 $componentDisplay = ob_get_contents();
		}

	//  print "Y";
	} else {
		//print "Z";
		//$componentDisplay = ob_get_contents();
	}

	//clear buffered print text (start displaying output again)
	ob_end_clean();
		$MANAGE_NUMO_LOCATION = $MANAGE_NUMO_LOCATION_bu;

	// do not parse additional components for the content sections edit page
	if (($matches[1] == "content_sections" || $matches[1] == "blog")  && $componentName == "manage" && $componentDisplay != "[NUMO.ACCOUNTS: LOGIN BOX]") {
		$componentData = $componentDisplay;
	} else {
	    $componentData = preg_replace_callback('/\[NUMO\.(.*?): (.*?)\]/i',"replace_component_tags",$componentDisplay);
	    $componentData = preg_replace_callback('/\<numo module=[\'"](.*?)[\'"] component=[\'"](.*?)[\'"]( params=[\'"](.*?)[\'"])?><\/numo>/i',"replace_component_tags",$componentData);
	}
	$cache .= $componentData;
	//replace all component code tags and return component HTML display
	return $componentData;
}

//callback function that replaces component tags with content
function replace_extension_tags($matches) {
	global $dbObj;
	global $numo;
	global $disableAll;

	//separate the component name from any parameters passed
	list($componentName, $paramString) = explode("(", $matches[2]);

	//split params into array like $_GET
	$paramString = substr($paramString, 0, -1);
	parse_str($paramString, $PARAMS);

	//stop diplaying output
	ob_start();

	//remove spaces from component name and replace with underscores and convert to lower case
	$componentName = strtolower(str_replace(" ", "_", $componentName));

	//remove spaces from module name and replace with underscores and convert to lower case
	$matches[1] = strtolower(str_replace(" ", "_", $matches[1]));
	  if ($_SERVER["SUBDOMAIN_DOCUMENT_ROOT"] != "") {
		$componentFileName = $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'].NUMO_FOLDER_PATH."extensions/".$matches[1]."/components/".$componentName.".php";

	  } else {
		$componentFileName = $_SERVER['DOCUMENT_ROOT'].NUMO_FOLDER_PATH."extensions/".$matches[1]."/components/".$componentName.".php";

	  }
//  $componentFileName = $_SERVER['DOCUMENT_ROOT'].NUMO_FOLDER_PATH."extensions/".$matches[1]."/components/".$componentName.".php";

   	 if (!$disableAll) {

		if (!file_exists($componentFileName)) {
			print "<p><b>Numo Error:</b> No such extension. {$matches[1]} -> {$componentName}</p>";
		} else {

		  //set page HTML contents
		  include($componentFileName);
		}
		//copy buffered print text
		$componentDisplay = ob_get_contents();

		//clear buffered print text (start displaying output again)
		ob_end_clean();
	}
	//replace all component code tags and return component HTML display
	return preg_replace_callback('/\[NUMO\*(.*?): (.*?)\]/i',"replace_extension_tags",$componentDisplay);
}

?>