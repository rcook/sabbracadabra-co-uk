<?php
$sql = "SELECT title,id FROM newsletter_messages WHERE status=1 AND site_id=".NUMO_SITE_ID." ORDER BY id desc";
//print $sql;
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
	$link = str_replace("[File]", "manage.numo?module=newsletter&component=view&id=".$row['id'], NUMO_SYNTAX_NEWSLETTER_LINK);
	print str_replace("[Label]", $row['title'], $link);	
}
?>