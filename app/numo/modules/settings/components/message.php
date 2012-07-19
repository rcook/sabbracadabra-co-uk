<?php
global $SYSTEM_ERROR_ID;

$sql = "SELECT `message` FROM language_syntax WHERE syntax_id='".$SYSTEM_ERROR_ID."' AND site_id='".NUMO_SITE_ID."'";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	print $row['message'];
} else {
	print "An unexpected error occured.  Please try again.";
}

//free SQL result
mysql_free_result($result);

?>