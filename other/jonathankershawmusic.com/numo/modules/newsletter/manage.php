<?php
if($_POST['cmd'] == "remove") {
	$sql = "DELETE FROM newsletter_messages WHERE id='".$_POST['newsletter_id']."' AND site_id='".NUMO_SITE_ID."'";
	$dbObj->query($sql);
}
?>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li class="active">Manage Newsletters</li>
  <li>&nbsp; <a href="module/newsletter/compose-new/" style='margin-top: -2px;' class='btn btn-success btn-mini'>Create New Newsletter</a></li>
</ul>
<h2>Manage Newsletters</h2>
<table class="table table-striped"><tr><th>&nbsp;</th><th class="highlight_label">Title</th><th>Sent</th><th>&nbsp;</th></tr>
<?php
$sql = "SELECT * FROM newsletter_messages WHERE site_id='".NUMO_SITE_ID."' ORDER BY status asc, title, id desc";
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<tr><td style='width: 15px; cursor: pointer'><img src="modules/newsletter/images/message_<? if($row['status'] == "1") { print "enabled"; } else { print "disabled"; }?>.gif" alt="Message <? if($row['status'] == "1") { print "displayed by archives component (Online)"; } else { print "not displayed (Offline)"; }?>" title="Message <? if($row['status'] == "1") { print "displayed by archives component (Online)"; } else { print "not displayed (Offline)"; }?>" /></td>
<td><?=$row['title']?></td><td  style='width: 30px; text-align: center;'><?=$row['send_count']?></td><td style='text-align: right;'><a  class='btn' href="module/<?=$_GET['m']?>/compose/?id=<?=$row['id']?>">Edit</a> <a href="module/<?=$_GET['m']?>/send/?id=<?=$row['id']?>" class='btn btn-success'>Send</a> <a  class='btn btn-danger' href="module/<?=$_GET['m']?>/<?=$_GET['i']?>/" onclick="return confirmRemove('<?=$row['id']?>');">Remove</a></td></tr>
<?
}
?>
</table>
<!--
<a href="module/<?=$_GET['m']?>/compose-new/"><img src="modules/newsletter/images/compose_button.jpg" alt="Compose New" title="Compose New" border="0" /></a>-->
<form method="post" name="remove_message" id="remove_message">
<input type="hidden" name="newsletter_id" value="" />
<input type="hidden" name="cmd" value="remove" />
</form>
<script>
function confirmRemove(id) {
	if(confirm("Are you absolutely sure you wish to remove this newsletter message?")) {
		document.forms['remove_message'].newsletter_id.value = id;
		document.forms['remove_message'].submit();
	}

	return false;
}
</script>