<fieldset>
<legend>Newsletter Subscriptions</legend>
<table class="table_data_layout"><tr><th class="highlight_label">List Name</th><th>Subscribes</th></tr>
<?php
$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
$results = $dbObj->query($sql);

if(mysql_num_rows($results) > 0) {
	while($row = mysql_fetch_array($results)) {
		$sql = "SELECT id FROM newsletter_subscribers WHERE subscription_list_id='".$row['id']."' AND account_id='".$_GET['id']."'";
		$result = $dbObj->query($sql);

		if($subscriber = mysql_fetch_array($result)) {
	?>
		<tr><td><?=$row['name']?></td><td align='center'>Yes</td></tr>
	<?php
		}	else {
	?>
		<tr><td><?=$row['name']?></td><td align='center'>No</td></tr>
	<?php
		}
		mysql_free_result($result);
	}
?>
</table>
<br />
<a href="module/newsletter/edit-subscriber/?id=<?=$_GET['id']?>">[Manage Subscriptions]</a>
<?php
}	else {
	print "<p>This account currently does not subscribe to any newsletter lists.</p>";
}
?>
</fieldset>