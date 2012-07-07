<?php
//***************************************************************************
// check to see if visitor is logged in already
//***************************************************************************
if(isset($_SESSION['account_id'])) {
	//don't show component when the visitor is already logged into an account
	return;
	
}
global $numo;
//***************************************************************************
?>
<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/accounts/components/styles/registration_box.css" />

<table id="numo_account_registration_box_component"><tr><td>
<?php
//***************************************************************************
// check to see if registration has been allowed for the account group
//***************************************************************************
$sql = "SELECT name, require_approval FROM `types` WHERE allow_registration=1 AND id='".$PARAMS['id']."' AND site_id='".NUMO_SITE_ID."'";
$result = $dbObj->query($sql);

$accountPendingValue = 0;

if($row = mysql_fetch_array($result)) {
	if($row['require_approval'] == '1') {
		$accountPendingValue = 1;
	}
} else {
	print "<p class='error'>Registration is unavailable for this group.</p></td></tr></table>";
	return;
}
//***************************************************************************

if($_POST['cmd'] == "create") {

	if($_POST['type_id'] != "") {
		if(!class_exists('Account')) {
			require("numo/modules/".$matches[1]."/classes/Account.php");
		}
		if ($numo->extensions['captcha']) {
			require_once("numo/extensions/captcha/components/util.php");
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
		$sql = "SELECT `name`,`slot`, `input_type` FROM `fields` WHERE type_id='".$PARAMS['id']."' AND show_on_registration=1 AND required=1";
		//print $sql."<br>";
		//exit;
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			//print "a";
			//exit;
			$slotId = $row['slot'];

			if($_POST['slot_'.$row['slot']] == "") {
				$errors[$row['slot']] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_VALUE_REQUIRED;
			} else if ($row['input_type'] == "captcha" && function_exists("checkCaptchaCode") && !checkCaptchaCode(trim($_POST['slot_'.$row['slot']]))) {
				$errors["$slotId"] = "INCORRECT";		
				//print "incorrect".trim($_POST['slot_'.$row['slot']]);
				
			}
		}

			if(count($errors) == 0) {
			$accountId = $accountObj->create(array_merge($_POST,array('pending' => $accountPendingValue)));

			if($accountId != null || $accountId != "") {
				if(isset($PARAMS['redirect'])) {
					//redirect back to custom page
					header("Location: ".$PARAMS['redirect']);
				
				} else {
					//redirect to edit page for new account group
	
					//header('Location: '.NUMO_FOLDER_PATH.'/module/accounts/account-edit/?id='.$accountId);
					print "<p>".NUMO_SYNTAX_ACCOUNT_REGISTRATION_ACCOUNT_CREATED."</p></td></tr></table>";
					return;				
				}
				
				

			} else {
				print "<p class='error'>".NUMO_SYNTAX_ACCOUNT_REGISTRATION_UNABLE_TO_CREATE_ACCOUNT."</p>";
				$PARAMS['id'] = $_POST['type_id'];
			}
		}
	}
}
?>
<form method="post">
	<ul>
		<?php
		//load field information for accounts group
		$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `fields` WHERE type_id='".$PARAMS['id']."' AND show_on_registration=1 ORDER BY `position`,`name`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($field = mysql_fetch_array($results)) {
			if($field['input_type'] == "password") {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input type="password" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$_POST['slot_'.$field['slot']].'" autocomplete="off" /> '.$errors[$field['slot']].'</li>';

			} else if($field['input_type'] == "dropdown list") {
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_list_options($field['input_options'],$_POST['slot_'.$field['slot']]).'</select> '.$errors[$field['slot']].'
							</li>';
			} else if($field['input_type'] == "captcha") {
				print '<li><label style="vertical-align: top;" for="slot_'.$field['slot'].'">'.$field['name'].':</label><div style="display: inline-block; ">[NUMO*CAPTCHA: RENDER IMAGE]<input class="numo_text_input_short" type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="" autocomplete="off" /> '.$errors[$field['slot']].'</div></li>';

			
			} else {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$_POST['slot_'.$field['slot']].'" /> '.$errors[$field['slot']].'</li>';
			}
		}
		?>
		<li><label for="submit_account_registration_cmd"></label><input type="submit" id="submit_account_registration_cmd" name="nocmd" value="<?=NUMO_SYNTAX_ACCOUNT_REGISTRATION_BUTTON_LABEL?>" /></li>
	</ul>
	<input type="hidden" name="type_id" value="<?=$PARAMS['id']?>" />
	<input type="hidden" name="cmd" value="create" />
</form>
</td></tr></table>