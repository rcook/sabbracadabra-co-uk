<?php
//***************************************************************************
// check to see if visitor is logged in
//***************************************************************************
if(!isset($_SESSION['account_id'])) {
	//don't show component when the visitor is NOT logged into an account
	return;
}
//***************************************************************************
?>
<style>
#numo_account_update_account_details_component form { margin: 0px; padding: 0px}
#numo_account_update_account_details_component form ul {list-style-type: none; margin: 0px; padding: 0px}
#numo_account_update_account_details_component form ul li {margin: 0px; padding: 0px; font-size: 13px;}
#numo_account_update_account_details_component form ul li label {font-size: 13px; width: 120px; display: inline-block; font-weight: bold; }
#numo_account_update_account_details_component p {color: #060; font-size: 12px; text-align: center; font-weight: bold; padding: 0px 0px 4px 0px; margin: 0px;}
#numo_account_update_account_details_component p.error {color: #f00; font-size: 12px; text-align: center; font-weight: bold;}
</style>
<table id="numo_account_update_account_details_component"><tr><td>
<?php
if($_POST['cmd'] == "update_account_details") {
	if($_SESSION['type_id'] != "") {
		require("numo/modules/".$matches[1]."/classes/Account.php");
		$proceed    = true;

		//load field information for accounts group
		$sql = "SELECT `name`,`slot` FROM fields WHERE type_id=".$_SESSION['type_id']." AND slot>3 AND show_on_registration=1 AND required=1";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			if($_POST['slot_'.$row['slot']] == "") {
				$proceed = false;
				$errors[$row['slot']] = NUMO_SYNTAX_ACCOUNT_REGISTRATION_VALUE_REQUIRED;
			}
		}

		if($proceed) {
			$accountObj = new Account($_SESSION['account_id']);
			$accountObj->update($_POST);

			print "<p>".NUMO_SYNTAX_ACCOUNT_DETAILS_UPDATED."</p>";
		}
	}
}
?>
<form method="post">
	<ul>
		<?php
		//load field information for accounts group
		$sql = "SELECT * FROM accounts WHERE id=".$_SESSION['account_id'];
		//print $sql."<br>";
		$result = $dbObj->query($sql);

		if($row = mysql_fetch_array($result)) {
			//load field information for accounts group
			$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM fields WHERE type_id=".$_SESSION['type_id']." AND slot>3 ORDER BY `position`,`name`";
			//print $sql."<br>";
			$results = $dbObj->query($sql);

			while($field = mysql_fetch_array($results)) {
				if($field['input_type'] == "dropdown list") {
					print '<li>
									<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
									<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_list_options($field['input_options'],$row['slot_'.$field['slot']]).'</select> '.$errors[$field['slot']].'
								</li>';
				} else {
					print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$row['slot_'.$field['slot']].'" /> '.$errors[$field['slot']].'</li>';
				}
			}
		}
		?>
		<li><label for="submit_account_update_details_cmd"></label><input type="submit" id="submit_account_update_details_cmd" name="nocmd" value="<?=NUMO_SYNTAX_ACCOUNT_UPDATE_ACCOUNT_BUTTON_LABEL?>" /></li>
	</ul>
	<input type="hidden" name="cmd" value="update_account_details" />
</form>
</td></tr></table>