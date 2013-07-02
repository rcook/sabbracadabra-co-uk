<?php
if($_POST['cmd'] == "create") {
	global $_POST;

	if($_POST['type_id'] != "") {
		if(!class_exists('Account')) {
			require(ABSOLUTE_ROOT_PATH."numo/modules/accounts/classes/Account.php");
		}

		$accountObj = new Account();

		//check to ensure email address unique
		if($accountObj->email_in_use($_POST['slot_3'])) {
			//check to see if the account using the email address is a partial account
			$sql = "SELECT a.id FROM accounts a, `types` t WHERE a.pending=3 and a.slot_3='".$_POST['slot_3']."' and a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
			//print $sql;
			$result =  $dbObj->query($sql);

			//it is a partial account, allow registration to continue
			if($row = mysql_fetch_array($result)) {
				$_POST['account_id'] = $row['id'];
				$_POST['syscmd'] = "initialize";

			//used by another FULLY registered account
			} else {
				//print message alerting of un-unique email
				$errors[3] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_EMAIL_IN_USE;
			}
		} else if(!isValidEmail($_POST['slot_3'])) {
			$errors[3] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_EMAIL_NOT_VALID;
		}

		//check to ensure username unique and not blank
		if($accountObj->username_in_use($_POST['slot_1']) || $_POST['slot_1'] == "") {
			$proceed = false;
			//print message alerting of un-unique username
			$errors[1] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_USERNAME_IN_USE;
		}

		//load field information for accounts group
		$sql = "SELECT `name`,`slot` FROM `fields` WHERE type_id='".$_POST['type_id']."' AND show_on_registration=1 AND required=1";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			if($_POST['slot_'.$row['slot']] == "") {
				$errors[$row['slot']] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_VALUE_REQUIRED;
			}
		}

		if(count($errors) == 0) {
			global $_SESSION;

			$accountId = $accountObj->create($_POST);
			header('Location: ?numo=registered'.$getStringValues);

			return;
		}
	}
}
?>
[NUMO.ACCOUNTS: REGISTRATION BOX(id=<?=$PARAMS['id']?>)]
