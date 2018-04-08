<?php
$errorMsg = "";

if($_POST['cmd'] == "create") {
	if($_POST['type_id'] != "") {
		$proceed    = true;
		$accountObj = new Account();

		//!!!!!check to ensure email address unique
		if($accountObj->email_in_use($_POST['slot_3'])) {
			$proceed = false;
			//print message alerting of un-unique email
			$errorMsg = "<p>Email address already in use, please enter a different email address.</p>";
		} else if(!isValidEmail($_POST['slot_3'])) {
			$proceed = false;
			//print message alerting of invalid email
			$errorMsg = "<p>Email address enter is not valid, please enter a valid email address.</p>";
		}

		//!!!!!check to ensure username unique
		if($accountObj->username_in_use($_POST['slot_1'])) {
			$proceed = false;
			//print message alerting of un-unique username
			$errorMsg = "<p>Username already in use, please enter a different username.</p>";
		}

		if($proceed) {
			$accountId = $accountObj->create($_POST);

			if($accountId != null || $accountId != "") {
				//redirect to edit page for new account group
				header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/account-edit/?id='.$accountId);
			} else {
				print "<h2>System Error.  Unable to create new account.</h2>";
			}
		}
	}
}
?>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/accounts/account-manage/">Accounts</a> <span class="divider">/</span></li>
  <li class="active">Create Account</li>
</ul>

<h2>Create Account</h2>
<?php
if($_POST['type_id'] != "") {
print $errorMsg;
?>
<form method="post">
	<fieldset>
	<legend>Enter Account Details</legend>
	<ul class="form_display">
		<?php
		//load field information for accounts group
		$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `fields` WHERE type_id='".$_POST['type_id']."' ORDER BY `position`,`name`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($field = mysql_fetch_array($results)) {
			if($field['input_type'] == "password") {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input type="password" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="" autocomplete="off" /></li>';

			} else if($field['input_type'] == "dropdown list") {
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_list_options($field['input_options'],$_POST['slot_'.$field['slot']]).'</select>
							</li>';
			} else {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$_POST['slot_'.$field['slot']].'" /></li>';
			}
		}
		?>
		<li><label for="submit_cmd"></label><input type="submit" id="submit_cmd" name="nocmd" class='btn btn-primary btn-large' value="Create" /></li>
	</ul>
	</fieldset>
	<input type="hidden" name="type_id" value="<?=$_POST['type_id']?>" />
	<input type="hidden" name="cmd" value="create" /> 
</form>
<?php
} else {
?>
<form method="post">
	<fieldset>
	<legend>Select An Account Group</legend>
	<ul class="form_display">
		<li><label for="type_id">Account Group:</label><select id="type_id" name="type_id"><?=list_account_group_options()?></select><input  style='vertical-align: top; margin-left: 10px;' class='btn btn-primary' type="submit" id="submit_cmd" name="nocmd" value="Next" />
	</li>
    </ul>
	</fieldset>
</form>
<?php
}

function list_account_group_options() {
	global $dbObj;

	$returnStr = "";

	$sql = "SELECT id, name FROM `types` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		$returnStr .= "<option value=\"".$row['id']."\">".$row['name']."</option>";
	}

	return $returnStr;
}
?>