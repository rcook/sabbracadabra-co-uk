<?php
function newsletter_show_list_availability($flag) {
	if($flag == 0) {
		return "Public";
	} else if($flag == 1) {
		return "Restricted";
	} else if($flag == 2) {
		return "Private";
	}
}

if($_POST['cmd'] == "remove") {
	$sql = "DELETE FROM newsletter_subscription_lists WHERE id='".$_POST['list_id']."' AND site_id='".NUMO_SITE_ID."'";
	$dbObj->query($sql);
}
?>
<h2>Manage Subscription Lists</h2>
<table class="table_data_layout"><tr><th class="highlight_label">Name</th><th>Availability</th><th>Subscribers</th><th>&nbsp;</th></tr>
<?php
$sql = "SELECT l.*, (SELECT COUNT(*) FROM newsletter_subscribers WHERE subscription_list_id=l.id) as 'sub_count' FROM newsletter_subscription_lists l WHERE l.site_id='".NUMO_SITE_ID."' ORDER BY l.name";

$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<tr><td><?=$row['name']?></td><td align='center'><?=newsletter_show_list_availability($row['availability'])?></td><td align='center'><?=$row['sub_count']?></td><td><a href="module/<?=$_GET['m']?>/edit-list/?id=<?=$row['id']?>">Edit</a> <a href="module/<?=$_GET['m']?>/manage-subscribers/?list=<?=$row['id']?>">Manage Subscribers</a> <a href="module/<?=$_GET['m']?>/<?=$_GET['i']?>/" onclick="return confirmRemove('<?=$row['id']?>');">Remove</a></td></tr>
<?
}
?>
</table>
<a href="module/<?=$_GET['m']?>/create-list/"><img src="modules/newsletter/images/create_button.jpg" alt="Create New List" title="Create New List" border="0" /></a>
<form method="post" name="remove_subscription_list" id="remove_subscription_list">
<input type="hidden" name="list_id" value="" />
<input type="hidden" name="cmd" value="remove" />
</form>
<script>
function confirmRemove(id) {
	if(confirm("Are you absolutely sure you wish to remove this subscription list?")) {
		document.forms['remove_subscription_list'].list_id.value = id;
		document.forms['remove_subscription_list'].submit();
	}

	return false;
}
</script>