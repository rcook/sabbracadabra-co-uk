<?php
//***************************************************************************
// check to see if visitor is logged in already
//***************************************************************************
if(isset($_SESSION['account_id'])) {
	//don't show component when the visitor is already logged into an account
	return;
}
//***************************************************************************
?>
<style>
#numo_account_registration_box_component { margin-left: 0px; }
#numo_account_registration_box_component form { margin: 0px; padding: 0px}
#numo_account_registration_box_component form ul {list-style-type: none; margin: 0px; padding: 0px}
#numo_account_registration_box_component form ul li {margin: 0px; padding: 0px; color: #f00; font-size: 13px;}
#numo_account_registration_box_component form ul li label {font-size: 13px; width: 110px; display: inline-block; color: #666; font-weight: bold; padding: 3px 0px;}
#numo_account_registration_box_component p {color: #060; font-size: 12px; text-align: center; font-weight: bold; padding: 0px 0px 4px 0px; margin: 0px;}
#numo_account_registration_box_component p.error {color: #f00;}
#numo_account_registration_box_component input { width: 105px; }
</style>
<div class="divider"></div>
<div class="whatsnew">
<div class="box1"><div class="bt"><div></div></div><div class="i1"><div class="i2"><div class="i3"><div class="box1-inner"><div class="box1-2"><div class="box1-6"><div class="box1-4"><div class="box1-8"><div class="box1-5"><div class="box1-7"><div class="box1-3"><div class="box1-1"><div class="box1-content">
<h3>Register</h3>
<table id="numo_account_registration_box_component"><tr><td>
<?php
//***************************************************************************
// check to see if registration has been allowed for the account group
//***************************************************************************
$sql = "SELECT name FROM types WHERE allow_registration=1 AND id=".$PARAMS['id']." AND site_id=".NUMO_SITE_ID;
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	//do nothing
} else {
	print "<p class='error'>Registration is unavailable for this group.</p></td></tr></table></div></div></div></div>";
	return;
}
//***************************************************************************

if($_POST['cmd'] == "create") {
	if($_POST['type_id'] != "") {
		require("numo/modules/".$matches[1]."/classes/Account.php");

		$accountObj = new Account();

		//check to ensure email address unique
		if($accountObj->email_in_use($_POST['slot_3'])) {
			//print message alerting of un-unique email
			$errors[3] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_EMAIL_IN_USE;
		} else if(!isValidEmail($_POST['slot_3'])) {
			$errors[3] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_EMAIL_NOT_VALID;
		}

		//check to ensure username unique
		if($accountObj->username_in_use($_POST['slot_1'])) {
			$proceed = false;
			//print message alerting of un-unique username
			$errors[1] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_USERNAME_IN_USE;
		}

		//load field information for accounts group
		$sql = "SELECT `name`,`slot` FROM `fields` WHERE type_id=".$PARAMS['id']." AND show_on_registration=1 AND required=1";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			if($_POST['slot_'.$row['slot']] == "") {
				$errors[$row['slot']] = "* ".NUMO_SYNTAX_ACCOUNT_REGISTRATION_VALUE_REQUIRED;
			}
		}

		if(count($errors) == 0) {
			$accountId = $accountObj->create($_POST);

			if($accountId != null || $accountId != "") {
				//redirect to edit page for new account group
				//header('Location: '.NUMO_FOLDER_PATH.'/module/accounts/account-edit/?id='.$accountId);
				print "<p>".NUMO_SYNTAX_ACCOUNT_REGISTRATION_ACCOUNT_CREATED."</p></td></tr></table></div></div></div></div></div></div></div></div></div></div></div></div></div><div class="bb"><div></div></div></div>
</div>";
				return;
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
		$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `fields` WHERE type_id=".$PARAMS['id']." AND show_on_registration=1 ORDER BY `position`,`name`";
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
</div></div></div></div></div></div></div></div></div></div></div></div></div><div class="bb"><div></div></div></div>
</div>
