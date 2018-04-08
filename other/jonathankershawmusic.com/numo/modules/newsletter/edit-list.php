<?php
if($_POST['cmd'] == "update") {
	$sql = "UPDATE newsletter_subscription_lists SET name='".$_POST['name']."',availability='".$_POST['availability']."' WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
	$dbObj->query($sql);

	//redirect to manage subscription lists page
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-list/');
}
?>
<style>
ul.form_display li input.text_input {width: 300px;}
ul.form_display li label{width:170px;float:left;}
</style>
<?php
$sql = "SELECT * FROM newsletter_subscription_lists WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<h2>Create Subscription List</h2>
<form method="post">
<ul class="form_display">
	<li><label for="name">Name:</label><input type="text" class="text_input" id="name" name="name" value="<?=$row['name']?>" autocomplete="off" /></li>
	<li><label for="availability">Subscription Availability:</label>
	<select name="availability" id="availability">
		<option value="0">Public (Everyone)</option>
		<option value="1" <?php if($row['availability'] == 1) { print 'selected="selected"'; } ?>>Restricted (Logged In Account Holders)</option>
		<option value="2" <?php if($row['availability'] == 2) { print 'selected="selected"'; } ?>>Private (Administrators Only)</option>
	</select>
	</li>
	<li><label for="form_input_submit">&nbsp;</label><input class='btn btn-large btn-success btn-default' type="submit" name="nocmd" id="form_input_submit" value="Save Changes" /></li>
</ul>
<input type="hidden" name="cmd" value="update" />
</form>
<?php
}
?>