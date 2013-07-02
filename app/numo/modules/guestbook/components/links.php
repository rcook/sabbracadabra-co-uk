<?php
$sql = "SELECT * FROM guestbook_types WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
//print $sql;
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
	$link = str_replace("[File]", str_replace("/numo/", "/", NUMO_FOLDER_PATH)."manage.numo?module=guestbook&component=display&id=".$row['id'], NUMO_SYNTAX_ACCOUNT_LINK);
	print str_replace("[Label]", $row['name'], $link);
}

?>