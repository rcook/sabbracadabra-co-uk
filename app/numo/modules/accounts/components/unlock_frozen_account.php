<?php
//***************************************************************************
// check to see if visitor should have access
//***************************************************************************
//not logged in or authorized
if(!isset($PARAMS['aid'])) {
	//don't show component when the visitor is NOT logged into an account
	return;
} else {
		require_once("numo/modules/accounts/classes/Account.php");

	$accountObj = new Account($PARAMS['aid']);
	$accountObj->unlock();
  	$sql = "DELETE FROM pending_requests WHERE id='".$_GET['id']."'";
	$dbObj->query($sql);

	print "<p>Account unlocked. You may now attempt to log in.  You will only get one opportunity to log in before your account is shut down again.</p>";
}

//***************************************************************************
?>