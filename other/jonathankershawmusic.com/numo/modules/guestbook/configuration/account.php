<fieldset>
<legend>Guestbook Submissions</legend>
<?php
$sql = "SELECT t.`name`, DATE_FORMAT(r.when_created,'%c/%e/%Y %l:%i %p') as 'when_created', r.`id` FROM `guestbook_responses` r, `guestbook_types` t WHERE r.account_id='".$_GET['id']."' AND r.`type_id`=t.`id` AND t.`site_id`='".NUMO_SITE_ID."' ORDER BY `when_created` desc";
//print $sql;
$formResponses = $dbObj->query($sql);

if(mysql_num_rows($formResponses) > 0) {
?>
<table class="table table-striped"><tr><th class="highlight_label">Form</th><th>When</th><th>&nbsp</th></tr>
	<?php
	while($row = mysql_fetch_array($formResponses)) {
		echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td>'.$row['name'].'</td><td>'.$row['when_created'].'</td><td><a href="module/form_handler/review-response/?id='.$row['id'].'">Review</a></td></tr>';

		$oddEvenCounter++;
	}
	?>
</table>
<?php
}	else {
	print "<p>This account has not submitted to any guestbook.</p>";
}
?>
</fieldset>
<br/><br/>