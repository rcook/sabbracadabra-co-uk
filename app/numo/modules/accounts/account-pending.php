<?php

//remove account
if($_POST['cmdb'] == "remove") {
	$accountObj = new Account($_POST['account_id']);
	$accountObj->remove();
}


	$sql = "SELECT * FROM `types` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		$groupID = $row['id'];
		$groupData["$groupID"] = $row;
	}

?>
<script>
function confirmRemove(accountId) {
	if(confirm("Are you absolutely sure you wish to remove this account?")) {
		document.forms['remove_account'].account_id.value = accountId;
		document.forms['remove_account'].submit();
	}

	return false;
}
</script>
<h2>Pending Accounts</h2>
<form method="post">
	<fieldset>
	<legend>Search</legend>
	<ul class="form_display">
		<li><label for="name">Name:</label><input type="text" id="name" name="name" value="<?=$_POST['name']?>" /></li>
		<li><label for="email">Email Address:</label><input type="text" id="email" name="email" value="<?=$_POST['email']?>" /></li>
		<li><label for="username">Username:</label><input type="text" id="username" name="username" value="<?=$_POST['username']?>" /></li>
		<!--<li><label for="group">Group:</label><select id="group" name="group"></select></li>-->
		<li><label for="submit_cmd"></label><input type="submit" name="nocmd" id="submit_cmd" value="Search" /></li>
	</ul>
	<input type="hidden" name="cmd" value="search" />
  </fieldset>

</form>

<?php
$sql = "SELECT a.id, a.slot_1, a.slot_3, a.slot_4, t.name, t.id as type_id, a.activated FROM accounts a, `types` t WHERE a.slot_1 LIKE '%".$_POST['username']."%' AND a.slot_3 LIKE '%".$_POST['email']."%' AND a.slot_4 LIKE '%".$_POST['name']."%' AND a.pending=1 AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$results = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;

echo '<table class="table_data_layout">';
echo '<tr>';
echo '<th class="highlight_label">Name</th><th class="nosort">Email Address</th><th class="nosort">Username</th><th class="nosort">Group</th><th class="nosort">Flags</th><th class="nosort">&nbsp</th></tr>';

while($row = mysql_fetch_array($results)) {
	echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td>'.$row['slot_4'].'</td>';
	echo '<td>'.$row['slot_3'].'</td><td>'.$row['slot_1'].'</td><td>'.$row['name'].'</td>';
	echo '<td>';
	if ($groupData["{$row['type_id']}"]['require_activation'] == 1) {
		// pending == 3 == subscriber
		if ($row['activated'] == 0 && $row['pending'] != 3) {
			print "Pending Activation";
		} 
	}
	echo '<td><a href="module/'.$_GET['m'].'/account-edit/?id='.$row['id'].'">Review</a> <a href="module/'.$_GET['m'].'/'.$_GET['i'].'/" onclick="return confirmRemove(\''.$row['id'].'\');">Remove</a></td></tr>';

	$oddEvenCounter++;
}

echo '</table>';
?>
<form method="post" name="remove_account" id="remove_account">
<input type="hidden" name="account_id" value="" />
<input type="hidden" name="name" value="<?=$_POST['name']?>" />
<input type="hidden" name="email" value="<?=$_POST['email']?>" />
<input type="hidden" name="username" value="<?=$_POST['username']?>" />
<input type="hidden" name="cmdb" value="remove" />
<input type="hidden" name="cmd" value="search" />
</form>