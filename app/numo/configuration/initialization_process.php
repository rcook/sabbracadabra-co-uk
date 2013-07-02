<?php
$productKeyErrors = array();
//save database connection information if submitted
if($_POST['cmd'] == "numo_install" || $_POST['cmd'] == "numo_reinstall") {
	
	//confirm product key entered
	if($_POST['next_step'] == "2") {
		foreach($_POST as $key => $productLicenseKey) {
			if(substr($key,0,13) == "product_key__") {
				$productName = substr($key,13);

				//check license key, if  string returned there was an error flagged
				if(($returnValue = check_license_key($productLicenseKey,$productName)) != "") {
					$productKeyErrors[$productName] = $returnValue;
					$_POST['next_step'] = 1; //do not continue to the next step, stay at current step
				}
			}
		}

	//confirm database connection information entered
	} else if($_POST['next_step'] == "3") {
		//include database class
		require("classes/Database.php");

		//re-init database class using the connection information provided by user
		$dbObj = new NumoDatabase($_POST['database_host'], $_POST['database_name'], $_POST['database_username'], $_POST['database_password']);

        if (!$dbObj->valid_connection && $_POST['database_host'] != "localhost") {
		  $dbObj = new NumoDatabase("localhost", $_POST['database_name'], $_POST['database_username'], $_POST['database_password']);
		  if ($dbObj->valid_connection) {
			  $_POST['database_host'] = "localhost";
		  }
		}
		
		//check to see if the database connection was setup (go into if condition if fail)
		if(!$dbObj->valid_connection) {
			
			$installError = true; //flag error occured

			$_POST['next_step'] = 2; //do not continue to the next step
		} else {
			// query database to see if the users table is setup and an administrator account has been created
			$sql = "SELECT id FROM accounts";
			$result = $dbObj->query($sql);

			// if table does exist and administrator account is created skip to login box
			if($row = @mysql_fetch_array($result)) {
				save_mysql_connection_information($_POST['database_host'], $_POST['database_name'], $_POST['database_username'], $_POST['database_password']);
			}
		}

	//confirm administrative user created
	}

	if($_POST['next_step'] == "done") {
		//print "we are done";
		// include database class
		require("classes/Database.php");

		// re-init database class using the connection information provided by user
		$dbObj = new NumoDatabase($_POST['database_host'], $_POST['database_name'], $_POST['database_username'], $_POST['database_password']);

		// run SQL commands for each module to complete database initialization
		setup_mysql_database($_POST['cmd'] == "numo_reinstall");

		//create administrative user account
		$sql = "INSERT INTO `accounts` (`type_id`,`is_admin`,`pending`,`activated`,`ip_address`,`when_created`,`slot_1`,`slot_2`,`slot_3`,`slot_4`) VALUES (1,1,0,1,'".$_SERVER['REMOTE_ADDR']."','".date("y/m/d H:i:s")."','".$_POST['account_username']."','".crypt($_POST['account_password'])."','".$_POST['account_email']."','".$_POST['account_name']."')";
		$dbObj->query($sql);
        if ($_SERVER['REMOTE_ADDR'] == "216.139.217.104") {
          //print $sql;
          print mysql_error();
        }

		//save connection information and key codes to file
		save_mysql_connection_information($_POST['database_host'], $_POST['database_name'], $_POST['database_username'], $_POST['database_password'], $_POST['system_setting_ssl_url'], $_POST['system_setting_secure_backend_via_ssl'], $_POST['system_setting_secure_frontend_via_ssl']);
		unset($_POST['next_step']);
		$doneInstall = true;
		require("configuration/database_connection_information.php");

		//exit;
	}
} else if ($_POST['cmd'] == "numo-remote-init") {
	require("classes/Database.php");

		// re-init database class using the connection information provided by user
	//	$dbObj = new NumoDatabase($_POST['database_host'], $_POST['database_name'], $_POST['database_username'], $_POST['database_password']);
	//print "yup";
	$sql = "INSERT INTO `sites` (`domain`,`name`) VALUES ('{$domain}','{$domain}')";
//	print $sql;
	$dbObj->query($sql);
	$sql = "SELECT * FROM sites WHERE domain='{$domain}' and `name`='{$domain}'";
	$result = $dbObj->query($sql);
	$record = mysql_fetch_array($result);
	$siteID = $record['id'];
	
	$sql    = "INSERT INTO `types` (`site_id`,`name`,`available_slots`) VALUES ({$siteID},'default','5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30')";
	$dbObj->query($sql);
	$sql    = "SELECT * FROM `types` WHERE site_id='{$siteID}' and `name`='default'";
	$result = $dbObj->query($sql);
	$record = mysql_fetch_array($result);
	$typeID = $record['id'];
	
	$sql = "INSERT INTO `fields` (`type_id`,`name`,`slot`,`position`,`required`,`locked`,`show_on_registration`,`input_type`,`input_options`,`regex`) VALUES ('{$typeID}','Username',1,3,1,1,1,'text','',''),('{$typeID}','Password',2,4,1,1,1,'password','',''),('{$typeID}','Email Address',3,2,1,1,1,'text','','^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$'),('{$typeID}','Name',4,1,1,1,1,'text','','')";
	$dbObj->query($sql);
	
	$sql = "INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('NUMO-INVALID_REQUEST_CODE', '{$siteID}', 'Invalid request code. Please try again or contact your website administrator.'), ('NUMO-ADMINISTRATIVE_EMAIL_ADDRESS', '{$siteID}', '{$_POST[account_email]}'), ('NUMO-FILE_NOT_FOUND_TITLE', '{$siteID}', 'File Not Found'), ('NUMO-FILE_NOT_FOUND', '{$siteID}', 'Oops! We were unable to locate the file you requested.')";
	$dbObj->query($sql);
	
	$sql = "INSERT INTO `accounts` (`type_id`,`is_admin`,`pending`,`activated`,`ip_address`,`when_created`,`slot_1`,`slot_2`,`slot_3`,`slot_4`) VALUES ('{$typeID}',1,0,1,'".$_SERVER['REMOTE_ADDR']."','".date("y/m/d H:i:s")."','".$_POST['account_username']."','".crypt($_POST['account_password'])."','".$_POST['account_email']."','".$_POST['account_name']."')";
	$dbObj->query($sql);
		
	//run module initialization SQL code
	run_sql_configuration("accounts", false, $_POST['license_key'], $siteID);
	run_sql_configuration("settings", false, $_POST['license_key'], $siteID);
} else if($_POST['cmd'] == "remote_install_new_module") {
		require("classes/Database.php");

	// if valid license key
	//print "yup";
	//foreach ($_POST as $key => $value) {
	//	print $key."=".$value."<br>";
	//}
	//print check_license_key($_POST['license_key'],$_POST['module']);
	//if(($licenseCheckResponse = check_license_key($_POST['license_key'],$_POST['module'])) == "") {
		//print "inside if for ".$_POST['license_key']."-".$_POST['module'];
		//run module initialization SQL code
		run_sql_configuration($_POST['module'], false, $_POST['license_key'], $_POST['site_id']);
		//header('Location: '.NUMO_FOLDER_PATH);
	//} else {
		//print "nope";
	//}
} else if ($_POST['cmd'] == "activate" || $_POST['cmd'] == "activate-all") {
  require("configuration/database_connection_information.php");
  require("classes/Database.php");

  $reinstallAllowedServerIP = "216.139.217.104";

  if ($_SERVER['REMOTE_ADDR'] == $reinstallAllowedServerIP) {
    if ($_POST['cmd'] == "activate-all") {
      setup_mysql_database();
    } else if(($licenseCheckResponse = check_license_key($_POST['license_key'], $_POST['module'])) == "") {
		//run module initialization SQL code
		run_sql_configuration($_POST['module'], false, $_POST['license_key']);
		print "ACTIVATED: ".$_POST['module'];
	} else {
	    print "INVALID KEY FOR MODULE";
	}
  }
  exit;

} else if ($_POST['cmd'] == "test_db") {
  $reinstallAllowedServerIP = "216.139.217.104";
  if ($_SERVER['REMOTE_ADDR'] == $reinstallAllowedServerIP) {

	require("classes/Database.php");

	//re-init database class using the connection information provided by user
	$dbObj = new NumoDatabase($_POST['database_host'], $_POST['database_name'], $_POST['database_username'], $_POST['database_password']);

	//check to see if the database connection was setup (go into if condition if fail)
	if($dbObj->valid_connection) {
	  print "SUCCESS";
	} else {
	  print "FAILURE";
	}

  }
  exit;
}

//write database connection information to file
function save_mysql_connection_information($host, $name, $username, $password, $sslAddress = "", $secureBackend = false, $secureFrontend = false) {
	global $_SERVER;
	if ($sslAddress == "") {
		$sslAddress = $_SERVER['HTTP_HOST'];
	}

	//open and write to database connection information file
	$f = fopen("configuration/database_connection_information.php", w); //open for write

	fwrite($f, "<"."?php\n");
	fwrite($f, "define(DATABASE_HOST, '".$host."');\n");
	fwrite($f, "define(DATABASE_NAME, '".$name."');\n");
	fwrite($f, "define(DATABASE_USERNAME, '".$username."');\n");
	fwrite($f, "define(DATABASE_PASSWORD, '".$password."');\n");
	fwrite($f, "define(NUMO_SITE_ID, '1');\n");
	fwrite($f, "define(NUMO_SERVER_ADDRESS, '".$_SERVER["HTTP_HOST"]."');\n");
	fwrite($f, "define(NUMO_FOLDER_PATH, '".substr($_SERVER["PHP_SELF"],0,(strrpos($_SERVER["PHP_SELF"], "/")))."/');\n");

	fwrite($f, "define(NUMO_SECURE_ADDRESS,  '{$sslAddress}');\n");
	fwrite($f, "define(NUMO_SECURE_BACKEND,   ".($secureBackend ? "true" : "false").");\n");
	fwrite($f, "define(NUMO_SECURE_FRONTEND,  ".($secureFrontend ? "true" : "false").");\n");
	
	$secondarySavePath = $_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI']."/sessions";
	if (@!is_writable(session_save_path()) && @is_writable($secondarySavePath)) {
	  fwrite($f, "define(USE_INTERNAL_SESSIONS, true);\n");
    } else {  
	  fwrite($f, "define(USE_INTERNAL_SESSIONS, false);\n");
		
	}

	fwrite($f, "?".">");

	fclose($f); //close
	
	// attempt to change the permissions
	@chmod ("configuration/database_connection_information.php", 0444);
}


//run sql commands for the numo system and each of the modules
function setup_mysql_database($reinstall = false, $siteID = 1) {
	global $dbObj; //allow access to database class
    global $_SERVER;

    // only allow reinstall if remote address matches secured Lucky Marble API server
    $reinstallAllowedServerIP = "216.139.217.104";

	//run initialization SQL code (main & for each module)
	$lines = file("configuration/initialization.sql");

	// Loop through our array, show HTML source as HTML source; and line numbers too.
	foreach ($lines as $lineNum => $line) {
		  $line = str_replace("NUMO_SITE_ID", $siteID, $line);
	  if ($reinstall && strpos($line, "CREATE TABLE IF NOT EXISTS") !== false && $_SERVER['REMOTE_ADDR'] == $reinstallAllowedServerIP) {
	    $matches = array();
	    $pattern = '/CREATE TABLE IF NOT EXISTS `([^`]*?)`/';
	    preg_match($pattern, $line, $matches);

	    $tableName = $matches[1];
	    $drop = "DROP TABLE `{$tableName}`";
	    $dbObj->query($drop);
	    //print $drop."<br>";
	  }
	  //print $line."<br>";
	  $dbObj->query($line); //run SQL query
	}
    
		//cycle through module folder and
		if ($modulesFolder = @opendir(MODULES_FOLDER_NAME)){
			//cycle thru each file in the MODULES folder
			while ($moduleFolderName = readdir($modulesFolder)) {
				//ingore if item named with periods or starts with an underscore
				if($moduleFolderName == "." || $moduleFolderName == ".." || substr($moduleFolderName, 0, 1) == "_"){
					continue;
				} 
				if ($_POST["product_key__{$moduleFolderName}"] == "" && $_POST['common_license_key'] != "") {
					$_POST["product_key__{$moduleFolderName}"] = $_POST['common_license_key'];
				}
	
				run_sql_configuration($moduleFolderName, false, $_POST["product_key__{$moduleFolderName}"], $siteID);
			}
	
		}
	
  //  print $_SERVER['REMOTE_ADDR'];
  //exit;
}

//run the sql configuration commands for a given module
function run_sql_configuration($name = "", $reinstall = false, $licenseKey = "", $siteID = 1) {
	global $dbObj;
	global $_SERVER;
	if ($siteID == "") {
		$siteID = 1;
	} 

    // only allow reinstall if remote address matches secured Lucky Marble API server
    $reinstallAllowedServerIP = "216.139.217.104";

	//check to make sure SQL configuration file exists
	if(file_exists(MODULES_FOLDER_NAME."/".$name."/configuration/initialization.sql")) {
		//load array of file contents
		$lines = file(MODULES_FOLDER_NAME."/".$name."/configuration/initialization.sql");

		// Loop through our array, show HTML source as HTML source; and line numbers too.
		foreach ($lines as $lineNum => $line) {
		  $line = str_replace("NUMO_SITE_ID", $siteID, $line);
		  if ($reinstall && strpos($line, "CREATE TABLE IF NOT EXISTS") !== false && $_SERVER['REMOTE_ADDR'] == $reinstallAllowedServerIP) {
			$matches = array();
			$pattern = '/CREATE TABLE IF NOT EXISTS `([^`]*?)`/';
			preg_match($pattern, $line, $matches);

			$tableName = $matches[1];
			$drop = "DROP TABLE `{$tableName}`";
			$dbObj->query($drop);
	        //print $drop."<br>";

		  }

		  if (strstr($line, 'LAST_INSERT_ID')) {
			 $lastInsert = "SELECT LAST_INSERT_ID()";
			 $res = $dbObj->query($lastInsert);
			 $record = mysql_fetch_array($res);
			 $lastInsertID = $record['LAST_INSERT_ID()'];
			 $line = str_replace("LAST_INSERT_ID", $lastInsertID, $line);
		  }

		  //print ":SQL: ".$line."<br>"; 
		  $dbObj->query($line); //run SQL query
		  if ($_SERVER['REMOTE_ADDR'] == $reinstallAllowedServerIP || $siteID != 1) {
			print $name.": ".$line."<br>".mysql_error()."<br>";
		  }
		}
	  $update = "UPDATE modules SET license_key='{$licenseKey}' WHERE name='{$name}' AND site_id='{$siteID}' AND license_key=''";
	  //print $update;
	  $dbObj->query($update);
	}
	
}
?>