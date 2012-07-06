<?php
	$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings`");
	$shoppingCartExists = (@mysql_num_rows($result))?TRUE:FALSE;

//remove group

if($_POST['cmd'] == "remove") {
	// remove accounts for group
	$sql = "DELETE FROM accounts WHERE type_id='".$_POST['type_id']."'";
	$dbObj->query($sql);

	// remove fields set for group
	$sql = "DELETE FROM `fields` WHERE type_id='".$_POST['type_id']."'";
	$dbObj->query($sql);

	// remove permissions se for group
	$sql = "DELETE FROM permissions WHERE type_id='".$_POST['type_id']."'";
	$dbObj->query($sql);

	// remove group
	$sql = "DELETE FROM `types` WHERE id='".$_POST['type_id']."' AND site_id='".NUMO_SITE_ID."'";
	$dbObj->query($sql);
}
?>
<script>
var currentUsersGroup = <?php if($row = mysql_fetch_array($dbObj->query("SELECT `type_id` FROM `accounts` WHERE `id`='".$_SESSION['account_id']."'"))) { echo $row['type_id']; } else { echo $_SESSION['type_id']; } ?>;
function confirmRemove(groupId) {
	if(currentUsersGroup == groupId) {
		alert('ACCOUNT GROUP CANNOT BE REMOVED.\n\nYou are currently logged into an account that is part of this account group.  If you wish to remove this account group and all accounts within it, please login to an administrative account that is not part of this account group.');
		return false;
	}

	if(confirm("Are you absolutely sure you wish to remove this account group and its accounts?")) {
		document.forms['remove_group'].type_id.value = groupId;
		document.forms['remove_group'].submit();
	}

	return false;
}
</script>
<style>
table tr td {text-align: center;}
table tr td.group_name {text-align: left;}
</style>
<h2>Manage Groups</h2>
<?php
$sql = "SELECT * FROM `types` WHERE site_id='".NUMO_SITE_ID."' ORDER BY `name`";
////print $sql."<br>";
$results = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;

echo '<table class="table_data_layout"><tr><th class="highlight_label">Name</th><th>Registration</th><th>Approval</th><th>Activation</th><th>&nbsp</th></tr>';

while($row = mysql_fetch_array($results)) {
	echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td class="group_name">'.$row['name'].'</td><td><img src="images/'.(($row['allow_registration'] == "0") ? 'no' : "yes").'.gif" />'.
			 '</td><td><img src="images/'.(($row['require_approval'] == "0") ? 'no' : "yes").'.gif" /></td><td><img src="images/'.(($row['require_activation'] == "0") ? 'no' : "yes").'.gif" />';
	echo '</td><td>';
	if ($access->hasAccess($_GET['m'], "group-edit")) { 
	  echo '<a href="module/'.$_GET['m'].'/group-edit/?id='.$row['id'].'">Manage</a>';
	}
	if ($access->hasAccess($_GET['m'], "group-components")) { 
	  echo '<a href="module/'.$_GET['m'].'/group-components/?id='.$row['id'].'">Get Component Code</a>';
	}
	if ($_SESSION['is_admin'] == "1") { 
	  echo '<a href="module/'.$_GET['m'].'/'.$_GET['i'].'/" onclick="return confirmRemove(\''.$row['id'].'\');">Remove</a>';
	}
	echo '</td></tr>';

	$oddEvenCounter++;
}

echo '</table>';
?>
<form method="post" name="remove_group" id="remove_group">
<input type="hidden" name="type_id" value="" />
<input type="hidden" name="cmd" value="remove" />
</form>