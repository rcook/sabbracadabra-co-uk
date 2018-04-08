<?php
//***************************************************************************
// check to see if visitor is logged in already
//***************************************************************************
//print "c";
if(isset($_SESSION['account_id'])) {
	//don't show component when the visitor is already logged into an account
	return;
}
//***************************************************************************
?>
<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/accounts/components/styles/forgot_password.css" />

<style>
</style>
<?php if ($PARAMS['title'] != "") { ?>
<h3><?php echo $PARAMS['title']; ?></h3>
<?php } ?>
<table id="numo_account_forgot_password_component"><tr><td>
<?php
if($_POST['cmd'] == "forgot password") {
	require("numo/modules/".$matches[1]."/classes/Account.php");
	$accountObj = new Account();
	$success = $accountObj->retrieve_password($_POST['email']);
	if (!$success && defined('NUMO_SYNTAX_ACCOUNT_NO_SUCH_ACCOUNT')) {
	  print "<p>".NUMO_SYNTAX_ACCOUNT_NO_SUCH_ACCOUNT."</p></td></tr></table>";
		
	} else {

	  print "<p>".NUMO_SYNTAX_ACCOUNT_FORGOT_LOGIN_INFO_MESSAGE_SENT."</p></td></tr></table>";
	}
	return;
}
?>
<form method="post">
	<ul>
		<li><label for="email"><?=NUMO_SYNTAX_ACCOUNT_FORGOT_LOGIN_INFO_EMAIL_FIELD_LABEL?>:</label><input type="text" id="email" name="email" value="" /></li>
		<li><label for="submit_account_forgot_password_cmd"></label><input type="submit" id="submit_account_forgot_password_cmd" name="nocmd" value="<?=NUMO_SYNTAX_ACCOUNT_FORGOT_LOGIN_INFO_BUTTON_LABEL?>" /></li>				
	</ul>
	<input type="hidden" name="cmd" value="forgot password" />
</form>
</td></tr></table>