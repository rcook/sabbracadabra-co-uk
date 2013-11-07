<?php
	// added april 11, 2013 -- enable advanced login restrictions
	$result = $dbObj->query("SHOW COLUMNS FROM `sites` LIKE 'admin_require_captcha'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if (!$exists) {
		$dbObj->query("ALTER TABLE `sites` ADD `admin_require_captcha` tinyint (4) default 1");
		$dbObj->query("ALTER TABLE `sites` ADD `login_attempts_threshold` tinyint (4) default 5");
		$dbObj->query("ALTER TABLE `sites` ADD `bad_login_freeze_period` int (11) default 30");
		$dbObj->query("ALTER TABLE `sites` ADD `lock_account_based_on_geolocation` tinyint (4) default 1");

		$dbObj->query("ALTER TABLE `accounts` ADD `current_bad_access_attempts` int (11) default 0");
		$dbObj->query("ALTER TABLE `accounts` ADD `last_bad_access_attempt_time` datetime");

	}
	
	// added april 22, 2013 -- enable ssl settings stored in db (rather than in config file)
	$result = $dbObj->query("SHOW COLUMNS FROM `sites` LIKE 'ssl_address'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if (!$exists) {

		$dbObj->query("ALTER TABLE `sites` ADD `ssl_address` varchar (255) default ''");
		$dbObj->query("ALTER TABLE `sites` ADD `ssl_secure_backend` tinyint (4) default 0");
		$dbObj->query("ALTER TABLE `sites` ADD `ssl_secure_frontend` tinyint (4) default 0");
		$dbObj->query("ALTER TABLE `sites` ADD `use_internal_sessions` tinyint (4) default 0");

	}
// added april 22, 2013 -- enable ssl settings stored in db (rather than in config file)
	$result = $dbObj->query("SHOW COLUMNS FROM `sites` LIKE 'location'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if (!$exists) {

		$dbObj->query("ALTER TABLE `sites` ADD `location` varchar (255) default ''");
	}
		
?>