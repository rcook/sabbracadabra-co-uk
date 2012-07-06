<?php
function moduleOffline($moduleName) {
  global $dbObj;
  $result = $dbObj->query("SELECT `status` FROM modules WHERE `status`=1 AND name='{$moduleName}' AND site_id='".NUMO_SITE_ID."'");
  return mysql_num_rows($result) == 0;
}
function numo_secure($region = "") {
  global $_SERVER;
  if ($region == "backend" && $_SERVER['HTTPS'] != "on" && NUMO_SECURE_BACKEND === true) {
	 // print "stop";
	 // print NUMO_SECURE_BACKEND;
	  header("Location: https://".NUMO_SECURE_ADDRESS.NUMO_FOLDER_PATH);
	  exit;
  }
}

function numo_session_start() {
  global $secondarySavePath;
  
  $secondarySavePath = $_SERVER['DOCUMENT_ROOT'].NUMO_FOLDER_PATH."sessions";
  
  // functionality not working when open base cannot detect writble folders Dec 12, 2011
  @session_start();
  return;
  
  //print $secondarySavePath;
  
  if (USE_INTERNAL_SESSIONS === true) {
	 if (is_writable($secondarySavePath)) {
	   session_save_path ($secondarySavePath); 
	 } else {
	   print "The folder 'numo/sessions/' is not writable.  Please enable write priviledges for all users on this folder.";
	 }
  } 
  if (!is_writable(session_save_path())) {
	//print "The folder ".session_save_path()." is not writable.  Please enable write priviledges for all users on this folder.";
									 
  } else { 
    @session_start();
  }
}


//check login request details (admin or normal)
function login($username, $password) {
	global $_SESSION;
	global $dbObj;

	$sql = "SELECT a.id, a.type_id, a.is_admin, a.pending, a.activated, a.slot_2, a.slot_4 FROM accounts a, `types` t WHERE a.slot_1='".$username."' AND a.type_id=t.id AND a.pending<>'3' AND t.site_id='".NUMO_SITE_ID."'";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		if(crypt($password,$row['slot_2']) == $row['slot_2']) {
			$_SESSION['account_id'] = $row['id'];
			$_SESSION['type_id']    = $row['type_id'];
			$_SESSION['pending']    = $row['pending'];
			$_SESSION['activated']  = $row['activated'];
			$_SESSION['is_admin']   = $row['is_admin'];
			$_SESSION['full_name']  = $row['slot_4'];

			//free SQL result
			mysql_free_result($result);

			$sql = "UPDATE accounts SET last_accessed='".date("y/m/d H:i:s")."' WHERE id='".$row['id']."'";
			$dbObj->query($sql);

			return true;
		}
	}

	//free SQL result
	mysql_free_result($result);

	return false;
}

function valid_key_code() {
	if(crypt(PRODUCT_LICENSE_KEY,PRODUCT_LICENSE_CODE) == PRODUCT_LICENSE_CODE) {
		return true;
	}

	return false;
}


function isValidEmail($email) {
  $validEmailPattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/";
  if (preg_match($validEmailPattern, $email) > 0) { 
    return true;
  } else {
    return false; 
  }
}

function generate_list_options($options, $currentValue = "", $sep = "\r\n") {
	$returnStr   = "";

	if(is_array($options)) {
		foreach ($options as $key => $value) {
			if((is_array($currentValue) && in_array(html_entity_decode($key), $currentValue)) || (is_string($currentValue) && $currentValue == $key)) {
				$returnStr .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
			} else {
				$returnStr .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
	} else if(is_array($currentValue)) {
		$listOptions = explode($sep, trim($options));

		foreach ($listOptions as $key => $value) {
			if(in_array(html_entity_decode($value), $currentValue)) {
				$returnStr .= '<option value="'.$value.'" selected="selected">'.$value.'</option>';
			} else {
				$returnStr .= '<option value="'.$value.'">'.$value.'</option>';
			}
		}
	} else {
		$listOptions = explode($sep, trim($options));

		foreach ($listOptions as $key => $value) {
			if($currentValue == $value) {
				$returnStr .= '<option value="'.$value.'" selected="selected">'.$value.'</option>';
			} else {
				$returnStr .= '<option value="'.$value.'">'.$value.'</option>';
			}
		}
	}

	return $returnStr;
}

function check_license_key($productLicenseKey,$productName) {
	$ch = curl_init(); //init

	curl_setopt($ch, CURLOPT_URL, 'http://numo.server-apps.com/check/'); //setup request to website to check license key
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
	curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'lpk='.urlencode($productLicenseKey).'&domain='.urlencode($_SERVER["HTTP_HOST"]).'&pid='.urlencode($productName)); //pass product license key and domain name along to be checked

	//send request and save response to variable
	$response = @curl_exec($ch);

	/*

	------------------------
	|||  RESPONSE CODES  |||
	------------------------

	-1 = License key is not valid for the product it was entered for (i.e. license code for members module provided when installing a blog module)
	-2 = License already used for an installation on a different domain name
	-3 = Invalid domain name passed along
	 0 = Product license key does not exist (could not be found)
	 1 = License already used for an installation for THIS domain name
	 2 = License never used, record created at LM

	*/

	//check to see if the curl request completed without error
	if(curl_errno($ch)) {
		//send email to luckymarble for manual confirmation
		@mail("numo@luckymarble.com","NUMO License Check","Product License Key: ".$productLicenseKey."\nDomain: ".$_SERVER["HTTP_HOST"]."\nModule: ".$productName);

	//error with license key provided
	} else if($response <= 0) {
		curl_close($ch); //close curl connection

		if($response == -1) {
			return " ** Product license key not valid for '".$productName."' module";

		} else if($response == -2) {
			return " ** Product license key already in use";

		} else {
			return " ** Invalid product license key";
		}
	}

	curl_close($ch); //close curl connection
	return ""; //success
}

?>