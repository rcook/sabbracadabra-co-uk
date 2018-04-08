<?php
//if account id passed
if(isset($PARAMS['aid'])) {
	//update activation status of account
	$sql = "UPDATE accounts SET activated=1 WHERE id=".$PARAMS['aid'];
	$dbObj->query($sql);
	
	//remove pending request
	$sql = "DELETE FROM pending_requests WHERE id='".$_GET['id']."'";
	//print $sql;
	$dbObj->query($sql);
	
	print "<p>".NUMO_SYNTAX_ACCOUNT_ACTIVATION_COMPLETED."</p>";
	return;
} else {
	print "<p>".NUMO_SYNTAX_ACCOUNT_ACTIVATION_CODE_INVALID."</p>";
	return;
}