<?php
//remove listing
if($_POST['cmdb'] == "Delete") {
	foreach($_POST as $key => $value) {
		if(substr($key,0,8) == "remove__") {
			$guestbookObj = new NumoGuestbook(substr($key,8));
			$guestbookObj->remove();
		}
	}
}
?>
<style>
.messages_received {font-size: 12px; color: #111; border: 1px solid #bbb; border-collapse:collapse;}
.messages_received tr.new_message {font-weight: bold; }
.messages_received tr th {background: #ededed;border-bottom: 1px solid #bbb; padding: 5px;}
.messages_received tr td {padding: 1px 5px; border-bottom: 1px solid #ccc;}
.messages_received tr td a {text-decoration: none;}
.messages_received tr td a:hover {text-decoration: underline;}
input.submit_normal {background: #2A61B3; border: 0px; color: #fff; font-weight: bold; padding: 3px; margin: 0px; cursor: pointer;}
input.submit_hover {background: #2A61B3; border: 0px; color: #D9E8F6; font-weight: bold; padding: 3px; margin: 0px; cursor: pointer;}
tr.button_row td {background: #ededed;border-bottom: 1px solid #bbb; padding: 5px;}
</style>
<script>
var selected = true
function selectAll() {
  var t = document.message_form.elements
  for (var i=0; i < t.length; i++) {
    if (t[i].type == "checkbox") {
      t[i].checked = selected;
    }
  }
  selected = !selected;
}

function confirmRemove() {
	if(confirm("Are you absolutely sure you wish to remove this response?")) {
		return true;
	}

	return false;
}
</script>
<h2>Guestbook Entries</h2>
<form method="post" name="message_form">
<?php
$startPos = 0;
$numPerPage = 10;

if($_POST['page']) {
	$startPos = $_POST['page'];
} else if($_GET['page']) {
	$startPos = $_GET['page'];
}

$sql = "SELECT l.`pending`, l.`status`, l.id, DATE_FORMAT(l.when_created,'%c/%e/%Y %l:%i %p') as 'when_created', (SELECT a.slot_4 FROM `accounts` a WHERE a.id=l.account_id) as 'responder', (SELECT COUNT(*) FROM `guestbook_responses`, `guestbook_types` WHERE `guestbook_responses`.type_id=`guestbook_types`.id AND `guestbook_types`.site_id='".NUMO_SITE_ID."') as 'max_rows' FROM `guestbook_responses` l, `guestbook_types` t WHERE l.type_id=t.id AND t.site_id='".NUMO_SITE_ID."' ORDER BY l.`when_created` desc LIMIT ".($startPos * $numPerPage).",".$numPerPage;
//print $sql."<br>";
$results = $dbObj->query($sql);

if(mysql_num_rows($results) > 0) {
	$moreRows = true;
	$rowsDisplayed = (($startPos + 1) * $numPerPage);

	echo '<table class="messages_received" cellpadding="0" cellspacing="0"><tr><th><a href="javascript:selectAll();"><img src="modules/guestbook/images/checkmark.jpg" border="0" /></a></th><th>&nbsp</th><th style="padding-right: 30px;">Submitted By</th><th>Recieved</th><th>&nbsp</th><th>&nbsp;</th></tr>';

	while($row = mysql_fetch_array($results)) {
		if($rowsDisplayed >= $row['max_rows']) {
			$moreRows = false;
		}

		$responderCell = '<td style="font-style: italic;">Guest</td>';

		if(isset($row['responder'])) {
			$responderCell = '<td>'.$row['responder'].'</td>';
		}
		if($row['status'] == 1) {
			echo '<tr class="new_message"><td><input type="checkbox" name="remove__'.$row['id'].'" value="0" /></td><td><img src="modules/'.$_GET['m'].'/images/new.jpg" alt="New" title="New" /></td>'.$responderCell.'<td class="spaced_col">'.$row['when_created'].'</td><td><a href="module/'.$_GET['m'].'/review-response/?id='.$row['id'].'">[Review]</a></td><td>'.($row['pending'] == 1 ? ' Pending' : '').'</td></tr>';
		} else {
			echo '<tr><td><input type="checkbox" name="remove__'.$row['id'].'" value="0" /></td><td><img src="modules/'.$_GET['m'].'/images/read.jpg" alt="Read" title="Read" /></td>'.$responderCell.'<td class="spaced_col">'.$row['when_created'].'</td><td><a href="module/'.$_GET['m'].'/review-response/?id='.$row['id'].'">[Review]</a></td><td>'.($row['pending'] == 1 ? ' Pending' : '').'</td></tr>';
		}
	}

	echo '<tr class="button_row"><td colspan="5"><input type="submit" name="cmdb" value="Delete" class="submit_normal" onclick="return confirmRemove();" onfocus="this.className=\'submit_hover\'" onblur="this.className=\'submit_normal\'" onmouseover="this.className=\'submit_hover\'" onmouseout="this.className=\'submit_normal\'" /></td><td style="text-align: right;">'.($startPos > 0 ? '<a href="module/'.$_GET['m'].'/responses/?page='.($startPos - 1).'">Previous</a>' : '').($startPos > 0 && $moreRows ? ' | ' : '').($moreRows ? '<a href="module/'.$_GET['m'].'/responses/?page='.($startPos + 1).'">Next</a>' : '').'</td></tr>';
	echo '</table>';
} else {
	echo '<p style="font-style: italic; font-weight: bold;">No responses found.</p>';
}
?>

<input type="hidden" name="name" value="<?=$_POST['name']?>" />
<input type="hidden" name="page" value="<?=$startPos?>" />
<input type="hidden" name="cmd" value="search" />
</form>