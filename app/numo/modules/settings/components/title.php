<?php
global $SYSTEM_ERROR_ID;

$sql = "SELECT `title` FROM language_syntax WHERE syntax_id='".$SYSTEM_ERROR_ID."' AND site_id='".NUMO_SITE_ID."'";
//print $sql;
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	print $row['title'];
} else {
	print "Unknown Error";
}

//free SQL result
mysql_free_result($result);
?>