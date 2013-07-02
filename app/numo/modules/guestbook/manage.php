<?php
if($_POST['cmd'] == "remove") {
	//remove listings for type
	$sql = "DELETE FROM `form_responses` WHERE type_id='".$_POST['type_id']."'";
	//print $sql."<br>";
	$dbObj->query($sql);

	//remove fields set for type
	$sql = "DELETE FROM `guestbook_fields` WHERE type_id='".$_POST['type_id']."'";
	//print $sql."<br>";
	$dbObj->query($sql);

	//remove type
	$sql = "DELETE FROM `guestbook_types` WHERE id='".$_POST['type_id']."' AND site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$dbObj->query($sql);
}

//if no id specified create a new guestbook


if(!isset($_GET['id'])) {

		$sql = "SELECT `id` FROM `guestbook_types` WHERE `site_id`='".NUMO_SITE_ID."'";
		//print $sql."<br>";
		$result = $dbObj->query($sql);


	if($row = mysql_fetch_array($result)) {
	//	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/?id='.$row['id']);
	//	exit();
	} else {
		
		//create guestbook
		$sql = "INSERT INTO `guestbook_types` (`site_id`,`available_slots`,`name`,`restrict_access`,`show_registration`,`default_group`,`confirmation_type`,`send_notification`,`confirmation_value`,`notification_email`,`include_form_info`) VALUES ('".NUMO_SITE_ID."','3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30','Guestbook',0,0,0,1,0,'Thank-you for your response!','',0)";
		//print $sql."<br>";
		$dbObj->query($sql);

		$sql = "SELECT LAST_INSERT_ID() as 'guestbook_id'";
		//print $sql."<br>";
		$result = $dbObj->query($sql);

		if($row = mysql_fetch_array($result)) {
			//add default fields
			$sql = "INSERT INTO `guestbook_fields` (`type_id`,`name`,`slot`,`position`,`required`,`locked`,`input_type`,`input_options`,`regex`) VALUES ('".$row['guestbook_id']."','Name',1,1,1,1,'text','',''), ('".$row['guestbook_id']."','Comments',2,2,1,0,'textarea','','')";
			//print $sql."<br>";
			$dbObj->query($sql);

			//redirect to edit type page
			//header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/?id='.$row['guestbook_id']);
		//	exit();
		}
	}
}
?>
<script>
function confirmRemove(groupId) {
	if(confirm("Are you absolutely sure you wish to remove this guestbook and all of its responses?")) {
		document.forms['remove_group'].type_id.value = groupId;
		document.forms['remove_group'].submit();
	}

	return false;
}
</script>
<style>
table tr td {text-align: center;}
table tr td.group_name {text-align: left;}
form#remove_group { padding-top: 75px; } 

</style>
<h2>Manage Guestbooks</h2>
<?php
$sql = "SELECT * FROM `guestbook_types` WHERE site_id='".NUMO_SITE_ID."' ORDER BY `name`";
////print $sql."<br>";
$results = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;

echo '<table class="table_data_layout"><tr><th class="highlight_label">Title</th><th>Public</th><th>&nbsp</th></tr>';

while($row = mysql_fetch_array($results)) {
	echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td class="group_name">'.$row['name'].'</td><td><img src="images/'.(($row['restrict_access'] == "1") ? 'no' : "yes").'.gif" />'.
			 '</td><td><a href="module/'.$_GET['m'].'/edit/?id='.$row['id'].'">Manage</a><a href="module/'.$_GET['m'].'/components/?id='.$row['id'].'">Get Component Code</a><a href="module/'.$_GET['m'].'/'.$_GET['i'].'/" onclick="return confirmRemove(\''.$row['id'].'\');">Remove</a></td></tr>';

	$oddEvenCounter++;
}

echo '</table>';
?>
<a href="module/<?=$_GET['m']?>/edit/"><img src="modules/guestbook/images/create_button.jpg" alt="Create New Guestbook" title="Create New Guestbook" border="0" /></a>
<form method="post" name="remove_group" id="remove_group">
<input type="hidden" name="type_id" value="" />
<input type="hidden" name="cmd" value="remove" />
</form>