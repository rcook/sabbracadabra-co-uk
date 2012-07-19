<?php
if($_POST['cmd'] == "create") {
	$sql = "INSERT INTO newsletter_subscription_lists (site_id,name,availability) VALUES ('".NUMO_SITE_ID."','".$_POST['name']."',".$_POST['availability'].")";
	$dbObj->query($sql);

	//redirect to manage subscription lists page
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-list/');
}
?>
<style>
ul.form_display li input.text_input {width: 300px;}
ul.form_display li label{width:170px;float:left;}
</style>
<h2>Create Subscription List</h2>
<form method="post">
<ul class="form_display">
	<li><label for="name">Name:</label><input type="text" class="text_input" id="name" name="name" value="" autocomplete="off" /></li>
	<li><label for="availability">Subscription Availability:</label>
	<select name="availability" id="availability">
		<option value="0">Public (Everyone)</option>
		<option value="1">Restricted (Logged In Account Holders)</option>
		<option value="2">Private (Administrators Only)</option>
	</select>
	</li>
	<li><label for="form_input_submit">&nbsp;</label><input type="submit" name="nocmd" id="form_input_submit" value="Create" /></li>
</ul>
<input type="hidden" name="cmd" value="create" />
</form>