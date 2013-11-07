<?php
if($_POST['cmd'] == "create") {
	global $_POST;
	global $numo;

	if($_POST['type_id'] != "") {
		if(!class_exists('Account')) {
			require("numo/modules/accounts/classes/Account.php");
		}

		$accountObj = new Account();
		if ($numo->extensions['captcha']) {
			require_once("numo/extensions/captcha/components/util.php");
		}
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
		$sql = "SELECT `name`,`slot`, `input_type` FROM `fields` WHERE type_id='".$_POST['type_id']."' AND show_on_registration=1 AND required=1";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			
						$slotId = $row['slot'];

			if($_POST['slot_'.$row['slot']] == "") {
				$errors[$row['slot']] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_VALUE_REQUIRED;
			} else if ($row['input_type'] == "captcha" && function_exists("checkCaptchaCode") && !checkCaptchaCode(trim($_POST['slot_'.$row['slot']]))) {
				$errors["$slotId"] = "INCORRECT";		
				//print "incorrect".trim($_POST['slot_'.$row['slot']]);
				
			}
		}
        //print count($errors);
		if(count($errors) == 0) {
			global $_SESSION;

			$accountId = $accountObj->create($_POST);

			$sql = "SELECT a.id, a.type_id, a.is_admin, a.pending, a.activated, a.slot_2, a.slot_4 FROM accounts a, `types` t WHERE a.`id`='".$accountId."' AND a.type_id=t.id AND a.pending<>3 AND t.site_id='".NUMO_SITE_ID."'";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
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

				$getStringValues = "";

				foreach($_GET as $key => $value) {
					if($key != "where") {
						$getStringValues .= "&".$key."=".$value;
					}
				}

				header('Location: ?view=cart'.$getStringValues);
			}

			return;
		}
	}
}
$sql = "SELECT s.`default_account_group` FROM `shopping_cart_settings` s, `types` t WHERE s.`default_account_group`=t.`id` AND s.`site_id`=".NUMO_SITE_ID;
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<td valign="top" style="border: 1px dotted #ddd;"><h2><?=NUMO_SYNTAX_SHOPPING_CART_NEW_CUSTOMER_LABEL?></h2><p><?=NUMO_SYNTAX_SHOPPING_CART_CREATE_ACCOUNT_LABEL?></p>
[NUMO.ACCOUNTS: REGISTRATION BOX(id=<?=$row['default_account_group']?>)]
</td>
<?php
}
?>