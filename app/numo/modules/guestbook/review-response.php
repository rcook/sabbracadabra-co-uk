<?php
if($_GET['display'] == "response_only") {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>Response</title>
	<link rel="stylesheet" type="text/css" href="../../../styles/template.css" />
</head>
<body>
<?php
}

if($_POST['cmdb'] == "Mark As Unread") {
	//change status of message to read
	$sql = "UPDATE `guestbook_responses` r, `guestbook_types` t SET r.`status`=1 WHERE r.`id`='".$_POST['id']."' AND r.`type_id`=t.`id` AND t.`site_id`='".NUMO_SITE_ID."'";
	$dbObj->query($sql);

	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/responses/');
	exit();
} else if($_POST['cmdb'] == "Delete") {
	$guestbookObj = new NumoGuestbook($_POST['id']);
	$guestbookObj->remove();

	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/responses/');
	exit();

} else if($_POST['cmdb'] == "Approve") {
	$guestbookObj = new NumoGuestbook($_POST['id']);
	$guestbookObj->approve();

	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/responses/');
	exit();

} else if ($_POST['cmdb'] == "Save") {
	$guestbookObj = new NumoGuestbook($_POST['id']);
	$guestbookObj->update($_POST);

	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/responses/');
	exit();
}
//change status of message to read
$sql = "UPDATE `guestbook_responses` r, `guestbook_types` t SET r.`status`=0 WHERE r.`id`='".$_GET['id']."' AND r.`type_id`=t.`id` AND t.`site_id`='".NUMO_SITE_ID."'";
$dbObj->query($sql);
?>
<style>
.messages_received {margin: 5px 0px;font-size: 12px; color: #111; border: 1px solid #bbb; border-collapse:collapse;}
.messages_received tr.new_message {font-weight: bold; }
.messages_received tr th {background: #cde;border-bottom: 1px solid #bbb; padding: 5px; text-align: left;}
.messages_received tr td {padding: 1px 5px; border-bottom: 1px solid #ccc; text-align: left;}
.messages_received tr td a {text-decoration: none;}
.messages_received tr td a:hover {text-decoration: underline;}
input.submit_normal {background: #2A61B3; border: 0px; color: #fff; font-weight: bold; padding: 3px; margin: 0px 2px; cursor: pointer;}
input.submit_hover {background: #2A61B3; border: 0px; color: #D9E8F6; font-weight: bold; padding: 3px; margin: 0px 2px; cursor: pointer;}
tr.button_row td {background: #ededed;border-bottom: 1px solid #bbb; padding: 5px 3px;}
.reponse_table tr td {vertical-align: top; border: 0px; padding: 3px 0px;}
.reponse_table tr th {vertical-align: top; border: 0px; padding: 3px 0px; font-weight: bold; background: none;}
</style>
<script>
function confirmRemove() {
	if(confirm("Are you absolutely sure you wish to remove this response?")) {
		return true;
	}

	return false;
}

function printResponse() {
	window.frames['print_response'].focus();
	window.frames['print_response'].print();
}
</script>
<?php
//load account information
$sql = "SELECT l.*,DATE_FORMAT(l.when_created,'%c/%e/%Y %l:%i %p') as 'when_created', t.`name`, (SELECT a.slot_4 FROM `accounts` a WHERE a.id=l.account_id) as 'responder' FROM `guestbook_responses` l, `guestbook_types` t WHERE l.id='".$_GET['id']."' AND l.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/guestbook/manage/">Guestbook</a> <span class="divider">/</span></li>
  <li><a href="module/guestbook/responses/">Manage Submissions</a> <span class="divider">/</span></li>
  <li class="active">Review</li>
</ul>
	<form method="post">
	<table class="table table-striped" cellpadding="0" cellspacing="0" width='600'>
	<?php
	if($_GET['display'] != "response_only") {
	?>
	<tr class="button_row">
		<td colspan="2"><input type="submit" name="cmdb" value="Mark As Unread" class="btn btn-warning" />
        <input type="submit" name="cmdb" value="Print" class="btn btn-info" />
        <input type="submit" name="cmdb" value="Delete" class="btn btn-danger" onclick="return confirmRemove();" />
                <?php if ($row['pending'] == "1") { ?>
        <input type='submit' name='cmdb' value='Approve' class='btn btn-success'  />
        <?php } ?>
        
        <input type="submit" name="cmdb" value="Save" class="btn btn-primary" />

        </td>
	</tr>
	<?php
	}

	if(isset($row['responder'])) {
	?>
	<tr><td style="font-weight: bold; width: 120px;">Submitted By:</td><td><a href="module/accounts/account-edit/?id=<?=$row['account_id']?>"><?=$row['responder']?></a></td></tr>
	<?php
	} else {
	?>
	<tr><td style="font-weight: bold; width: 120px;">Submitted By:</td><td>Guest</td></tr>
	<?php
	}
	?>
	<tr><td style="font-weight: bold; width: 120px;">Received:</td><td><?=$row['when_created']?></td></tr>
	<tr><td style="font-weight: bold; width: 120px;">IP Address:</td><td><?=$row['ip_address']?></td></tr>
</table>
<br/>
<table class='table table-striped'>
	<tr><th colspan="2">Response Copy</th></tr>
	<tr><td colspan="2">
		<table class="table" cellpadding="0" cellspacing="5" width='100%'>
		<?php
		//load field information for accounts group
		$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `guestbook_fields` WHERE type_id='".$row['type_id']."' ORDER BY `position`,`name`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($field = mysql_fetch_array($results)) {
			$fieldValue = html_entity_decode($row['slot_'.$field['slot']]);
			$fieldValue = str_replace('"','&#34;',$fieldValue);

			if($field['input_type'] == "heading") {
				print "<tr><td colspan='2'><h2>".$field['input_options']."</h2></td></tr>";

			} else if($field['input_type'] == "label") {
				print "<tr><td colspan='2'>".$field['input_options']."</td></tr>";

			} else if($fieldValue == "") {
				print '<tr><th>'.$field['name'].':</th><td style="font-style: italic; color: #666;">No response provided.</td></tr>';

			} else {
				if ($field['input_type'] == "text") {
					print '<tr><th width="200px">'.$field['name'].':</th><td><input style="width: 99%;" name="slot_'.$field['slot'].'" value="'.nl2br($fieldValue).'" /></td></tr>';
				
				} else if ($field['input_type'] == "textarea") {
				print '<tr><th width="200px">'.$field['name'].':</th><td><textarea style="width: 99%; height: 100px; " name="slot_'.$field['slot'].'">'.stripslashes($fieldValue).'</textarea></td></tr>';
					
				}
			}
		}
		?>
		</table>
	</td></tr>
	</table>

	<input type="hidden" name="id" value="<?=$row['id']?>" />
	</form>
<?php

if($_GET['display'] != "response_only") {
	print '<iframe src="module/'.$_GET['m'].'/review-response/?id='.$row['id'].'&display=response_only" id="print_response" name="print_response" width="1px" height="1px" frameborder="0"></iframe>';
} else {
	print '</body></html>';
}

mysql_free_result($result);
mysql_free_result($results);
} else {
	print '<p>Could not locate response.</p>';
}
?>