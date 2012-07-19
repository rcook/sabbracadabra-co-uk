<?php
if(isset($_SESSION['account_id'])) {
	$sql = "SELECT `".$PARAMS['i']."` FROM accounts WHERE id='".$_SESSION['account_id']."'";
	//print $sql."<br>";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		print $row[$PARAMS['i']];
	}
}
?>