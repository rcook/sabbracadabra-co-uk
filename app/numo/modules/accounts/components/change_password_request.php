<?php
//***************************************************************************
// check to see if visitor should have access
//***************************************************************************
//not logged in or authorized
if(!isset($PARAMS['aid'])) {
	//don't show component when the visitor is NOT logged into an account
	return;
}
//***************************************************************************
?>
<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/accounts/components/styles/change_password.css" />
<table id="numo_account_change_password_request"><tr><td>
<?php
if($_POST['cmd'] == "update_password") {
	require("numo/modules/".$matches[1]."/classes/Account.php");
	
	if($_POST['slot_2'] == $_POST['repeat_password']) {
		//update password
		$accountObj = new Account($PARAMS['aid']);
		$accountObj->update($_POST);
		
		$sql = "DELETE FROM pending_requests WHERE id='".$_GET['id']."'";
		//print $sql;
		$dbObj->query($sql);
		
		print "<p>".NUMO_SYNTAX_ACCOUNT_UPDATE_PASSWORD_REQUEST_COMPLETE."</p></td></tr></table>";
		return;
	} else {
		print "<p class='error'>".NUMO_SYNTAX_ACCOUNT_PASSWORDS_DONT_MATCH."</p>";
	}
}
?>
<form method="post">
	<ul>
		<li><label for="password"><?=NUMO_SYNTAX_ACCOUNT_UPDATE_PASSWORD_FIELD_LABEL?>:</label><input type="password" id="slot_2" name="slot_2" value="<?=$_POST['slot_2']?>" /></li>
		<li><label for="repeat_password"><?=NUMO_SYNTAX_ACCOUNT_UPDATE_PASSWORD_REPEAT_FIELD_LABEL?>:</label><input type="password" id="repeat_password" name="repeat_password" value="" /></li>
		<li><label for="submit_account_change_password_cmd"></label><input type="submit" id="submit_account_change_password_cmd" name="nocmd" value="<?=NUMO_SYNTAX_ACCOUNT_UPDATE_PASSWORD_BUTTON_LABEL?>" /></li>
	</ul>
	<input type="hidden" name="cmd" value="update_password" />
</form>
</td></tr></table>