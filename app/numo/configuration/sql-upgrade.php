<?php
$query = "SELECT * FROM `language_syntax` WHERE site_id='1' AND id='NUMO-TIMEZONE_CODE'";
$upgradeResult = $dbObj->query($query);
if (mysql_num_rows($upgradeResult) == 0) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('NUMO-TIMEZONE_CODE', 1, '')");
}
?>
