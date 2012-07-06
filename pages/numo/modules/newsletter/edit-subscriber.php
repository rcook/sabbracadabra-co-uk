<?php
if(!isset($_GET['id'])) {
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-subscribers/');
}

if($_POST['cmd'] == "update") {
	$sql = "UPDATE accounts SET slot_3='".$_POST['email']."',slot_4='".$_POST['name']."' WHERE id='".$_POST['account_id']."'";
	//print $sql;
	$dbObj->query($sql);

	$sql = "DELETE FROM newsletter_subscribers WHERE account_id='".$_POST['account_id']."'";
	//print $sql;
	$dbObj->query($sql);

	if(isset($_POST['lists'])) {
	  print sizeof($_POST['lists']);
		foreach($_POST['lists'] as $key => $value) {
			$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('".$_POST['account_id']."','".$value."')";
			$dbObj->query($sql);
		}
	} else {
    }

	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-subscribers/');
}
?>
<h2>Manage Subscriber</h2>
<form method="post">
<?php
$sql = "SELECT slot_3 as 'email', slot_4 as 'name' FROM accounts a, `types` t WHERE a.id='".$_GET['id']."' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<p><b>Name:</b> <input type="text" name="name" value="<?=str_replace('"','&#34;',$row['name'])?>" /></p>
<p><b>Email:</b> <input type="text" name="email" value="<?=str_replace('"','&#34;',$row['email'])?>" /></p>
<?php
}

mysql_free_result($result);
?>
<h2>Subscription Lists</h2>
	<ul class="form_display">
	<?php
	$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		$sql = "SELECT id FROM newsletter_subscribers WHERE subscription_list_id='".$row['id']."' AND account_id=".$_GET['id'];
		$result = $dbObj->query($sql);

		if($subscriber = mysql_fetch_array($result)) {
	?>
		<li><input type="checkbox" name="lists[]" checked="checked" id="list_<?=$row['id']?>" value="<?=$row['id']?>" /><label for="list_<?=$row['id']?>"><?=$row['name']?></label></li>
	<?php
		}	else {
	?>
		<li><input type="checkbox" name="lists[]" id="list_<?=$row['id']?>" value="<?=$row['id']?>" /><label for="list_<?=$row['id']?>"><?=$row['name']?></label></li>
	<?php
		}

		mysql_free_result($result);
	}
	?>
	</ul>
	<div style="clear:both;"><br /></div>
	<input type="submit" name="nocmd" id="submit_input" value="Save" />
	<input type="hidden" name="account_id" value="<?=$_GET['id']?>" />
	<input type="hidden" name="cmd" value="update" />
</form>