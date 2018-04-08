<?php
$sql     = "SELECT id,value FROM language_syntax WHERE site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
	DEFINE('NUMO_SYNTAX_'.str_replace('-','_',$row['id']), $row['value']);
}
?>