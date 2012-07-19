<h2>Manage Subscribers</h2>
<form method="post">
	<fieldset>
	<legend>Search</legend>
	<ul class="form_display">
		<li><label for="subscriber_name">Name:</label><input type="text" name="name" id="subscriber_name" value="<?=$_POST['name']?>" /></li>
		<li><label for="subscriber_email">Email:</label><input type="text" name="email" id="subscriber_email" value="<?=$_POST['email']?>" /></li>
		<li><label for="subscription_list">List:</label>
			<select name="list" id="subscription_list">
				<option value="">ALL</option>
				<?php
				$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
				$results = $dbObj->query($sql);

				while($row = mysql_fetch_array($results)) {
					if(($row['id'] == $_POST['list']) || (!isset($_POST['list']) && ($row['id'] == $_GET['list']))) {
				?>
					<option value="<?=$row['id']?>" selected="selected"><?=$row['name']?></option>
				<?php
				}	else {
				?>
					<option value="<?=$row['id']?>"><?=$row['name']?></option>
				<?php
					}
				}
				?>
			</select>
		</li>
		<li><label for="submit_input">&nbsp;</label><input type="submit" name="nocmd" id="submit_input" value="Search" /></li>
	</ul>
	</fieldset>
	<input type="hidden" name="cmd" value="search" />
</form>
<div style="clear:both;"><br /></div>
<table class="table_data_layout"><tr><th class="highlight_label">Name</th><th>Email</th><th>&nbsp;</th></tr>
<?php
$sql = "SELECT DISTINCT a.id, a.slot_3, a.slot_4 FROM accounts a, newsletter_subscription_lists l, newsletter_subscribers s WHERE l.site_id='".NUMO_SITE_ID."' AND l.id=s.subscription_list_id AND s.account_id=a.id ORDER BY name";

if($_POST['cmd'] == "search") {
	if($_POST['list'] == "") {
		$sql = "SELECT DISTINCT a.id, a.slot_3, a.slot_4 FROM accounts a, newsletter_subscription_lists l, newsletter_subscribers s WHERE l.site_id='".NUMO_SITE_ID."' AND l.id=s.subscription_list_id AND s.account_id=a.id AND a.slot_4 LIKE '%".$_POST['name']."%' AND a.slot_3 LIKE '%".$_POST['email']."%' ORDER BY name";
	} else {
		$sql = "SELECT DISTINCT a.id, a.slot_3, a.slot_4 FROM accounts a, newsletter_subscription_lists l, newsletter_subscribers s WHERE l.site_id='".NUMO_SITE_ID."' AND l.id=s.subscription_list_id AND l.id=".$_POST['list']." AND s.account_id=a.id AND a.slot_4 LIKE '%".$_POST['name']."%' AND a.slot_3 LIKE '%".$_POST['email']."%' ORDER BY name";
	}

} elseif(isset($_GET['list'])) {
	$sql = "SELECT DISTINCT a.id, a.slot_3, a.slot_4 FROM accounts a, newsletter_subscription_lists l, newsletter_subscribers s WHERE l.site_id='".NUMO_SITE_ID."' AND l.id=s.subscription_list_id AND l.id='".$_GET['list']."' AND s.account_id=a.id ORDER BY name";
}
//print $sql;
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<tr><td><?=$row['slot_4']?></td><td><?=$row['slot_3']?></td><td><a href="module/<?=$_GET['m']?>/edit-subscriber/?id=<?=$row['id']?>">Edit</a></td></tr>
<?
}
?>
</table>

<a href="module/<?=$_GET['m']?>/create-subscriber/"><img src="modules/newsletter/images/create_button.jpg" alt="Create New Subscriber" title="Create New Subscriber" border="0" /></a>