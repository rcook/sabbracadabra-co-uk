<?php
$accountID = 0;

//***************************************************************************
// check to see if visitor is logged in
//***************************************************************************
if(isset($_SESSION['account_id'])) {
	//set ACCOUNT_ID value
	$accountID = $_SESSION['account_id'];
//not logged in or authorized
} else {
	//don't show component when the visitor is NOT logged into an account
	return;
}
//***************************************************************************
?>
<style>
#numo_account_update_password_component form { margin: 0px; padding: 0px}
#numo_account_update_password_component form ul {list-style-type: none; margin: 0px; padding: 0px}
#numo_account_update_password_component form ul li {margin: 0px; padding: 0px}
#numo_account_update_password_component form ul li label {font-size: 13px; width: 120px; display: inline-block; color: #000000;}
#numo_account_update_password_component p {color: #060; font-size: 12px; text-align: center; font-weight: bold; padding: 0px 0px 4px 0px; margin: 0px;}
#numo_account_update_password_component p.error {color: #f00; font-size: 12px; text-align: center; font-weight: bold;}
</style>
<table id="numo_account_update_password_component"><tr><td>
<?php
if($_POST['cmd'] == "update_password") {
	require("numo/modules/".$matches[1]."/classes/Account.php");

	if($_POST['slot_2'] == $_POST['repeat_password']) {
		//update password
		$accountObj = new Account($accountID);
		$accountObj->update($_POST);

		//clear value for password
		$_POST['slot_2'] = "";

		print "<p>".NUMO_SYNTAX_ACCOUNT_PASSWORD_UPDATED."</p>";
	} else {
		print "<p class='error'>".NUMO_SYNTAX_ACCOUNT_PASSWORDS_DONT_MATCH."</p>";
	}
}
?>

<form method="post">
	<ul>
		<li><label for="password"><?=NUMO_SYNTAX_ACCOUNT_UPDATE_PASSWORD_FIELD_LABEL?>:</label><input type="password" id="slot_2" name="slot_2" value="<?=$_POST['slot_2']?>" /></li>
		<li><label for="repeat_password"><?=NUMO_SYNTAX_ACCOUNT_UPDATE_PASSWORD_REPEAT_FIELD_LABEL?>:</label><input type="password" id="repeat_password" name="repeat_password" value="" /></li>
		<li><label for="submit_account_update_password_cmd"></label><input type="submit" id="submit_account_update_password_cmd" name="nocmd" value="<?=NUMO_SYNTAX_ACCOUNT_UPDATE_PASSWORD_BUTTON_LABEL?>" /></li>
	</ul>
	<input type="hidden" name="cmd" value="update_password" />
</form>
</td></tr></table>