<?php

// add special account group login/registration direction fields -- added March 4, 2013
$result = $dbObj->query("SHOW COLUMNS FROM `types` LIKE 'registration_completion_page'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `types` ADD `registration_completion_page` varchar(255)");
	$dbObj->query("ALTER TABLE `types` ADD `login_completion_page` varchar(255)");
  //print "done";
}

?>