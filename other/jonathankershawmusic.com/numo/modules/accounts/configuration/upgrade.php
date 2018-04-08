<?php

// add special account group login/registration direction fields -- added March 4, 2013
$result = $dbObj->query("SHOW COLUMNS FROM `types` LIKE 'registration_completion_page'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `types` ADD `registration_completion_page` varchar(255)");
	$dbObj->query("ALTER TABLE `types` ADD `login_completion_page` varchar(255)");
  //print "done";
}


// add in syntax for country list -- April 16, 2013
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='NUMO-COUNTRY_LIST'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('NUMO-COUNTRY_LIST', '".NUMO_SITE_ID."', 'AU=Australia<br>CA=Canada<br>NZ=New Zealand<br>UK=United Kingdom<br>US=United States')");
}

// add in syntax for state/province list -- April 16, 2013
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='NUMO-CANADIAN_PROVINCE_LIST'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('NUMO-CANADIAN_PROVINCE_LIST', '".NUMO_SITE_ID."','AB=Alberta<br>BC=British Columbia<br>MB=Manitoba<br>NB=New Brunswick<br>NL=Newfoundland and Labrador<br>NS=Nova Scotia<br>NT=Northwest Territories<br>NU=Nunavut<br>ON=Ontario<br>PE=Prince Edward Island<br>QC=Quebec<br>SK=Saskatchewan<br>YT=Yukon')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('NUMO-AUSTRALIAN_PROVINCE_LIST', '".NUMO_SITE_ID."','ACT=Australian Capital Territory<br>NSW=New South Wales<br>N T=Northern Territory<br>QLD=Queensland<br>SA=South Australia<br>TAS=Tasmania<br>VIC=Victoria<br>W A=Western Australia')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('NUMO-AMERICAN_STATE_LIST', '".NUMO_SITE_ID."','AL=Alabama<br>AK=Alaska<br>AS=American Somoa<br>AZ=Arizona<br>AR=Arkansas<br>AE=Armed Forces Africa, Canada, Middle East, Europe<br>AA=Armed Forces America (except Canada)<br>AP=Armed Forces Pacific<br>CA=California<br>CO=Colorado<br>CT=Connecticut<br>DE=Delaware<br>DC=District of Columbia<br>FM=Federated States of Micronesia<br>FL=Florida<br>GA=Georgia<br>GU=Guam<br>HI=Hawaii<br>ID=Idaho<br>IL=Illinois<br>IN=Indiana<br>IA=Iowa<br>KS=Kansas<br>KY=Kentucky<br>LA=Louisiana<br>ME=Maine<br>MH=Marshall Islands<br>MD=Maryland<br>MA=Massachusetts<br>MI=Michigan<br>MN=Minnesota<br>MS=Mississippi<br>MO=Missouri<br>MT=Montana<br>NE=Nebraska<br>NV=Nevada<br>NH=New Hampshire<br>NJ=New Jersey<br>NM=New Mexico<br>NY=New York<br>NC=North Carolina<br>ND=North Dakota<br>MP=Northern Mariana Islands<br>OH=Ohio<br>OK=Oklahoma<br>OR=Oregon<br>PM=Palau<br>PA=Pennsylvania<br>PR=Puerto Rico<br>RI=Rhode Island<br>SC=South Carolina<br>SD=South Dakota<br>TN=Tennessee<br>TX=Texas<br>VI=US Virgin Islands<br>UT=Utah<br>VT=Vermont<br>VA=Virginia<br>WA=Washington<br>WV=West Virginia<br>WI=Wisconsin<br>WY=Wyoming')");
}

// update "email" field type to type "email" -- May 13, 2013
$result = $dbObj->query("SELECT * FROM `fields` WHERE input_type='text' AND slot='3'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if ($exists) {
	$dbObj->query("UPDATE `fields` SET input_type='email' WHERE slot='3'");
}

?>