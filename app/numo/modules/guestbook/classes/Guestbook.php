<?php
class NumoGuestbook {
  var $id;

  function NumoGuestbook($id = 0) {
  	$this->id = $id;
  }

  function create($info) {
  	global $_SERVER;
  	global $dbObj;

		$attachments = array();

		$activationUrl = "N/A";

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
		if(array_key_exists('status', $info)) {
			$fieldsList .= ",status";
			$valuesList .= ",'".$info['status']."'";
		}

		//account id of responder
		if(array_key_exists('account_id', $info)) {
			$fieldsList .= ",account_id";
			$valuesList .= ",'".$info['account_id']."'";
		}

		//ip address
		if(array_key_exists('ip_address', $info)) {
			$fieldsList .= ",ip_address";
			$valuesList .= ",'".$info['ip_address']."'";
		}

		//slots
		foreach($info as $key => $value) {
			//replace single quotes to prevent query corruption
			$value = str_replace("'","&#39;",$value);

			if(substr($key,0,5) == "slot_") {
				if(is_array($value)) {
					$newValue = "";

					foreach($value as $k => $v) {
						$newValue .= $v.",";
					}

					$value = substr($newValue,0,-1);
				}

				$fieldsList .= ",".$key;
				$valuesList .= ",'".$value."'";
			}
		}

		//insert account into database
		$sql = "INSERT INTO `guestbook_responses` (`type_id`,`when_created`".$fieldsList.") VALUES ('".$info['guestbook_id']."','".date("y/m/d H:i:s")."'".$valuesList.")";
		$dbObj->query($sql);
  }

	function update($info) {
  	global $dbObj;

  	//update account information
		foreach($info as $key => $value) {
			//custom slot fields
			if(substr($key,0,5) == "slot_") {
				if(is_array($value)) {
					$newValue = "";

					foreach($value as $k => $v) {
						$newValue .= htmlentities($v).",";
					}

					$value = substr($newValue,0,-1);
				}

				//check to see if PHP is set to automatically escape characters
				if (!get_magic_quotes_gpc()) {
					//if PHP isn't set to escape POST/GET information, manually escape value
					$setString .= ",".$key."='".addslashes($value)."'";
				} else {
					$setString .= ",".$key."='".$value."'";
				}

			//pending and activated values
			} else if($key == "pending" || $key == "status" || $key == "account_id") {
				$setString .= ",".$key."='".$value."'";
			}
		}

		//remove first comma from string
		$setString = substr($setString,1);

		//update account information
		$sql = "UPDATE `guestbook_responses` SET ".$setString." WHERE id='".$this->id."'";
		//print $sql."<br>";
		$dbObj->query($sql);
	}

	function remove() {
		global $dbObj;

		$sql = "DELETE FROM `guestbook_responses` WHERE id='".$this->id."'";
		//print $sql."<br>";
		$dbObj->query($sql);
	}

	function approve() {
		global $dbObj;

		$sql = "UPDATE `guestbook_responses` SET pending=0 WHERE id='".$this->id."'";
		//print $sql."<br>";
		$dbObj->query($sql);
	}

}
?>