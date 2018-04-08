<?php
if($_POST['cmd'] == "add") {
	//create account
	$sql = "INSERT INTO accounts (type_id,pending,slot_1,slot_2,slot_3,slot_4) VALUES ('".$_POST['group_id']."',3,'','".crypt(time())."','".$_POST['email']."','".$_POST['name']."')";
	$dbObj->query($sql);

	//lookup account id
	$sql = "SELECT LAST_INSERT_ID() as 'account_id'";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		if(isset($_POST['lists'])) {
			//add account to subscription lists requested
			foreach($_POST['lists'] as $key => $value) {
				$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('".$row['account_id']."','".$value."')";
				//print $sql."<bR>";
				$dbObj->query($sql);
			}
		}
	}

	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-subscribers/');
}
?>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li class="active"><a href='module/newsletter/manage-subscribers/'>Newsletter Subscribers</a> <span class="divider">/</span></li>
  <li class="active">Create Subscriber</li>
</ul>
<h2>Create New Subscriber</h2>
<?php
$displayMessage = "";

if($_POST['cmd'] == "step2" && !isValidEmail($_POST['email'])) {
	$_POST['cmd'] = "";
	$displayMessage = "<p style='color: #900; font-weight: bold;'>Please provide a valid email address.</p>";
}

if($_POST['cmd'] == "step2") {
//check to see if any accounts exist with email address
$sql = "SELECT a.id FROM accounts a, `types` t WHERE a.slot_3='".$_POST['email']."' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql;
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/edit-subscriber/?id='.$row['id']);
} else {
	$subscriberAccountGroup = 0;

	$sql = "SELECT default_account_group FROM newsletter_settings WHERE site_id='".NUMO_SITE_ID."'";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		$subscriberAccountGroup = $row['default_account_group'];

		if($row['default_account_group'] == "" || $row['default_account_group'] == "0") {
			print "<p style='font-weight: bold;'>A default account group must be selected before a new subscriber can be created.  Please <a href='module/newsletter/configure/'>click here</a> to be redirected to the configuration page where you can select your default account group for new subscribers.</p>";
			exit();
		}
	}
//if one found forward to edit screen
//else allow add
	?>
	<style>
	ul.form_display li label{width:auto; padding-right: 5px;}
	ul.form_display li {line-height: 20px; vertical-align: bottom;}
	.checkbox_list {padding-left: 5px;}
	</style>
	<form method="post">
		<fieldset>
		<legend>Step 2: Enter Details</legend>
		<ul class="form_display">
			<li><label for="subscriber_name">Name:</label><input type="text" name="name" id="subscriber_name" value="" /></li>
			<li><h3>Subscription Lists</h3></li>
			<?php
			$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
			$results = $dbObj->query($sql);

			while($row = mysql_fetch_array($results)) {
			?>
				<li><input type="checkbox" name="lists[]" id="list_<?=$row['id']?>" value="<?=$row['id']?>" /><label class="checkbox_list" for="list_<?=$row['id']?>"><?=$row['name']?></label></li>
			<?php
			}
			?>
		</ul>
		<div style="clear: both;"><br /></div>
		<input type="submit" name="nocmd" id="submit_input" value="Add" />

		</fieldset>
		<input type="hidden" name="cmd" value="add" />
		<input type="hidden" name="email" value="<?=$_POST['email']?>" />
		<input type="hidden" name="group_id" value="<?=$subscriberAccountGroup?>" />
	</form>
	<?php
	}
} else {
?>
<style>
ul.form_display li label{width:100px;}
</style>
<form method="post">
	<fieldset>
	<legend>Step 1: Enter Email Address</legend>
	<?=$displayMessage?>
	<ul class="form_display">
		<li><label for="subscriber_email">Email:</label><input type="text" name="email" id="subscriber_email" value="<?=$_POST['email']?>" /></li>
		<li><label for="submit_input">&nbsp;</label><input class='btn btn-large' onclick="history.go(-1)" type="button" value="Back" /> <input class='btn btn-primary btn-large' style='margin-left: 35px;' type="submit" name="nocmd" id="submit_input" value="Next" /></li>
	</ul>
	</fieldset>
	<input type="hidden" name="cmd" value="step2" />
</form>
<?php
}
?>