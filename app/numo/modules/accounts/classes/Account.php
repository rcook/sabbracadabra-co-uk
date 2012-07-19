<?php
if(!class_exists('Account')) {
class Account {
  var $id;

  function Account($id = 0) {
  	$this->id = $id;
  }

  function create($info) {
  	global $_SERVER;
  	global $dbObj;

	$activationUrl = "N/A";
	if ($info['activated'] == "") {
		$sql = "SELECT require_activation FROM types WHERE id='".$info['type_id']."'";
		//print $sql;
		$query = $dbObj->query($sql);
		$rec = mysql_fetch_array($query);
		if ($rec['require_activation'] == "1") {
		  $info['activated'] = 0;
		} else {
		  $info['activated'] = 1;
		}
		//foreach ($rec as $x => $y) {
	  	//  print $x."=".$y."<br>";
	    //}
    } else {
     //print "activated1: ".$info['activated'];
    }


    $sql = "SELECT require_approval FROM types WHERE id='".$info['type_id']."'";
    $query = $dbObj->query($sql);
    $rec = mysql_fetch_array($query);
    $pending = $rec['require_approval'];
    $info['pending'] = $pending;

//print "<br>activated2: ".$info['activated'];

  	if(isset($info['syscmd'])) {
  		$updateString = "";

			//activated
			if(array_key_exists('activated', $info)) {
				$updateString .= ",activated=".$info['activated'];
			}

			//is admin
			if(array_key_exists('is_admin', $info)) {
				$updateString .= ",is_admin=".$info['is_admin'];
			}

			//slots
			foreach($info as $key => $value) {
				if(substr($key,0,5) == "slot_") {
					$updateString .= ",".$key;

					if($key == "slot_2") {
						$updateString .= "='".crypt($value)."'";
					} else {
						$updateString .= "='".$value."'";
					}
				}
			}

  		//initialize the partial account
  		$sql = "UPDATE accounts SET pending='{$pending}',type_id='".$info['type_id']."' ".$updateString." WHERE id='".$info['account_id']."'";
			//print $sql."<br>";
			$dbObj->query($sql);

			// account still requires activation
			if($info['activated'] != "1") {
				$requestId = $info['account_id'].md5(time());

				$sql = "INSERT INTO pending_requests (id, site_id, account_id, module, component) VALUES ('".$requestId."','".NUMO_SITE_ID."','".$info['account_id']."','accounts','activate')";
				//print $sql."<br>";
				$dbObj->query($sql);

				$activationUrl = "http://".NUMO_SERVER_ADDRESS.str_replace('/numo/','/',NUMO_FOLDER_PATH)."process.numo?id=".$requestId;
			}
  	} else {

			//initialize variables
			$fieldsList = "";
			$valuesList = "";

			//type id
			if(array_key_exists('type_id', $info)) {
				$fieldsList .= ",type_id";
				$valuesList .= ",'".$info['type_id']."'";
			}

			//pending
			if(array_key_exists('pending', $info)) {
				$fieldsList .= ",pending";
				$valuesList .= ",'".$info['pending']."'";
			}

			//activated
			if(array_key_exists('activated', $info)) {
				$fieldsList .= ",activated";
				$valuesList .= ",'".$info['activated']."'";
			}

			//is admin
			if(array_key_exists('is_admin', $info)) {
				$fieldsList .= ",is_admin";
				$valuesList .= ",'".$info['is_admin']."'";
			}

			//slots
			foreach($info as $key => $value) {
				if(substr($key,0,5) == "slot_") {
					$fieldsList .= ",".$key;

					if($key == "slot_2") {
						$valuesList .= ",'".crypt($value)."'";
					} else {
						$valuesList .= ",'".$value."'";
					}
				}
			}

			//insert account into database
			$sql = "INSERT INTO `accounts` (`ip_address`,`when_created`".$fieldsList.") VALUES ('".$_SERVER['REMOTE_ADDR']."','".date("y/m/d H:i:s")."'".$valuesList.")";
			//print $sql."<br>";
			//return;

			$dbObj->query($sql);

			// account still requires activation
			if ($info['activated'] != "1") {
				$sql = "SELECT LAST_INSERT_ID() as account_id";
				$result = $dbObj->query($sql);

				if($row = mysql_fetch_array($result)) {
					$requestId = $row['account_id'].md5(time());

					$sql = "INSERT INTO pending_requests (id, site_id, account_id, module, component) VALUES ('".$requestId."','".NUMO_SITE_ID."','".$row['account_id']."','accounts','activate')";
					//print $sql."<br>";
					$dbObj->query($sql);

					$activationUrl = "http://".NUMO_SERVER_ADDRESS.str_replace('/numo/','/',NUMO_FOLDER_PATH)."process.numo?id=".$requestId;
				}
			}
		}
			//send welcome email message
			$headers  = 'From: '.NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1';


			$to = $info['slot_3'];
			$subject = NUMO_SYNTAX_ACCOUNT_WELCOME_EMAIL_SUBJECT;
			$message = nl2br(NUMO_SYNTAX_ACCOUNT_WELCOME_EMAIL);

			$adminNotificationSubject = nl2br(NUMO_SYNTAX_ACCOUNT_NEW_ACCOUNT_NOTIFICATION_SUBJECT);
			$adminNotificationMessage = nl2br(NUMO_SYNTAX_ACCOUNT_NEW_ACCOUNT_NOTIFICATION);


		if ($info['activated'] != "1") {
			//replace the activation link tag with an actual link
			$message = str_replace("[activation link]", "<a href='".$activationUrl."'>".$activationUrl."</a>", $message);
		} else {
			$message = str_replace("To complete your account creation please activate you account: [activation link]", "", $message);

		}


			$sql = "SELECT name, slot FROM `fields` WHERE type_id='".$info['type_id']."'";
			$results = $dbObj->query($sql);

			//replace any tags set for accuont information fields
			while($row = mysql_fetch_array($results)) {
				$subject = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $subject);
				$message = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $message);

				$adminNotificationSubject = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $adminNotificationSubject);
				$adminNotificationMessage = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $adminNotificationMessage);
			}



			// Mail it
			mail($to, $subject, $message, $headers);
			//print "mail sent";


		//alert administrator via email
		$headers  = 'From: '.NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1';
		mail(NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS, $adminNotificationSubject, $adminNotificationMessage, $headers);

		if(isset($info['syscmd'])) {
			return $info['account_id'];
		} else {
			$sql = "SELECT LAST_INSERT_ID() as 'account_id'";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				return $row['account_id'];
			}
		}
  }

  function update($info) {
  	global $dbObj;

  	  $query = "SELECT * FROM accounts WHERE id='{$this->id}'";
  	  $result = $dbObj->query($query);
  	  $rec   = mysql_fetch_array($result);

  	//update account information
		foreach($info as $key => $value) {
			//custom slot fields
			if(substr($key,0,5) == "slot_") {
				//if password field (slot_2) use special rules
				if($key == "slot_2") {
					//if a value for the password has been entered allow update. otherwise ignore field.
					if($value != "") {
						$setString .= ",".$key."='".crypt($value)."'";
					}
				} else {
					//check to see if PHP is set to automatically escape characters
					if (!get_magic_quotes_gpc()) {
						//if PHP isn't set to escape POST/GET information, manually escape value
						$setString .= ",".$key."='".addslashes($value)."'";
					} else {
						$setString .= ",".$key."='".$value."'";
					}
				}
			//pending and activated values
			} else if($key == "pending" || $key == "activated" || $key == "is_admin") {
				$setString .= ",".$key."=".$value;
			}
		}

		//remove first comma from string
		$setString = substr($setString,1);

		//update account information
		$sql = "UPDATE accounts SET ".$setString." WHERE id='".$this->id."'";
		$dbObj->query($sql);

		if ($info['pending'] == 0 && $rec['pending'] == 1) {
		    print "Approved email sent.<br>";
			$headers  = 'From: '.NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1';


			$to = $info['slot_3'];
			
			if (defined('NUMO_SYNTAX_ACCOUNT_APPROVED_EMAIL')) {
			  $subject = NUMO_SYNTAX_ACCOUNT_APPROVED_EMAIL_SUBJECT;
			  $message = nl2br(NUMO_SYNTAX_ACCOUNT_APPROVED_EMAIL);
			} else {
			  $subject = NUMO_SYNTAX_ACCOUNT_WELCOME_EMAIL_SUBJECT;
			  $message = nl2br(NUMO_SYNTAX_ACCOUNT_WELCOME_EMAIL);
			
			  //replace the activation link tag with an actual link
			  $message = str_replace("To complete your account creation please activate you account: [activation link]", "Your account has now been activated.", $message);
			  $message = str_replace("Below are your login details.", "", $message);
			  $message = str_replace("Username: [Username]<br>", "", $message);
			  $message = str_replace("Password: [Password]<br><br>", "", $message);
				
			}
           // exit;
			//$adminNotificationSubject = nl2br(NUMO_SYNTAX_ACCOUNT_NEW_ACCOUNT_NOTIFICATION_SUBJECT);
			//$adminNotificationMessage = nl2br(NUMO_SYNTAX_ACCOUNT_NEW_ACCOUNT_NOTIFICATION);


			$sql = "SELECT name, slot FROM `fields` WHERE type_id='".$rec['type_id']."'";
			$results = $dbObj->query($sql);

			//replace any tags set for accuont information fields
			while($row = mysql_fetch_array($results)) {
				$subject = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $subject);
				$message = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $message);

				//print $row['name']." = ".$info['slot_'.$row['slot']]."<br>";

				//$adminNotificationSubject = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $adminNotificationSubject);
				//$adminNotificationMessage = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $adminNotificationMessage);
			}
            if ($message != "") {
			// Mail it
			  mail($to, $subject, $message, $headers);
			}


		}
	}

	function remove() {
		global $dbObj;

		//$sql = "DELETE FROM accounts a, `types` t WHERE a.id='".$this->id."' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
		$sql = "DELETE FROM accounts WHERE id='".$this->id."'";
		//print $sql."<br>";
		$dbObj->query($sql);
	}

  function changeGroup($newType) {
    global $dbObj;
	
	$sql = "SELECT * FROM accounts WHERE id='".$this->id."'";
	$account = $dbObj->query($sql);

	if($info = mysql_fetch_array($account)) {
		$updateString = "";

		$sql = "SELECT type_id, slot, name, input_type FROM `fields` WHERE (type_id='".$info['type_id']."' OR type_id='".$newType."') ORDER BY name, input_type, type_id";
		$results = $dbObj->query($sql);
		$lastFieldName = "";
		$lastFieldType = "";
		$lastFieldSlot = "";
		$lastTypeId    = "";

		while($row = mysql_fetch_array($results)) {
			if($lastFieldName == $row['name'] && $lastFieldType == $row['input_type']) {
				//if($lastFieldSlot != $row['slot']) {
					if($lastTypeId == $info['type_id']) {
						$updateString .= ",slot_".$row['slot']."='".$info['slot_'.$lastFieldSlot]."'";
					} else {
						$updateString .= ",slot_".$lastTypeId."='".$info['slot_'.$row['slot']]."'";
					}
				//}
			}

			$lastFieldName = $row['name'];
			$lastFieldType = $row['input_type'];
			$lastFieldSlot = $row['slot'];
			$lastTypeId    = $row['type_id'];
		}
		//print $updateString."<br>";

		if(strlen($updateString) > 0) {
			$sql = "UPDATE accounts SET type_id='".$newType."' ".$updateString." WHERE id='".$this->id."'";
			//print $sql."<br>";
			$dbObj->query($sql);
		}
	}


  }
  function retrieve_password($email) {
  	global $dbObj;

		//generate special id to pass in email
		//insert special id so the system can confirm retrieval request
		//send email notification to reset password
  	$sql = "SELECT * FROM accounts WHERE slot_3='".$email."'";
  	//print $sql."<br>";
  	$result = $dbObj->query($sql);

  	if($info = mysql_fetch_array($result)) {
  		$requestId = $info['id'].md5(time());

			$sql = "INSERT INTO pending_requests (id, site_id, account_id, module, component) VALUES ('".$requestId."','".NUMO_SITE_ID."','".$info['id']."','accounts','change password request')";
			//print $sql."<br>";
			$dbObj->query($sql);

			$headers  = 'From: '.NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1';


			$to = $info['slot_3'];
			$subject = NUMO_SYNTAX_ACCOUNT_FORGOT_LOGIN_INFO_EMAIL_SUBJECT;
			$message = nl2br(NUMO_SYNTAX_ACCOUNT_FORGOT_LOGIN_INFO_EMAIL);

			$sql = "SELECT name, slot FROM `fields` WHERE type_id='".$info['type_id']."'";
			$results = $dbObj->query($sql);

			$updatePasswordURL = "http://".NUMO_SERVER_ADDRESS.str_replace('/numo/','/',NUMO_FOLDER_PATH)."process.numo?id=".$requestId;

			//replace the update password link tag with an actual link
			$message = str_replace("[update password link]", "<a href='".$updatePasswordURL."'>".$updatePasswordURL."</a>", $message);

			while($row = mysql_fetch_array($results)) {
				$subject = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $subject);
				$message = str_replace("[".$row['name']."]", $info['slot_'.$row['slot']], $message);
			}

			// Mail it
			mail($to, $subject, $message, $headers);




      $success = true;
		} 
	

		//free SQL result
		mysql_free_result($result);
		return $success;
  }

	function email_in_use($email) {
  	global $dbObj;

  	$sql = "SELECT a.id FROM accounts a, `types` t WHERE a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."' AND a.slot_3='".$email."' AND a.id<>'".$this->id."'";
  	//print $sql."<br>";
  	$result = $dbObj->query($sql);

  	if($row = mysql_fetch_array($result)) {
  		//email in use
  		return true;
		}

		//free SQL result
		mysql_free_result($result);

		//not in use
		return false;
	}

	function username_in_use($username) {
  	global $dbObj;

  	$sql = "SELECT a.id FROM accounts a, `types` t WHERE a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."' AND a.slot_1='".$username."' AND a.id<>'".$this->id."'";
  	//print $sql."<br>";
  	$result = $dbObj->query($sql);

  	if($row = mysql_fetch_array($result)) {
  		//username in use
  		return true;
		}

		//free SQL result
		mysql_free_result($result);

		//not in use
		return false;
	}
}
}
?>