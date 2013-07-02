<?
	$result = $dbObj->query("SHOW COLUMNS FROM `sites` LIKE 'admin_require_captcha'");
	$advancedLoginFeatures = (mysql_num_rows($result))?TRUE:FALSE;

//error_reporting (E_ALL);
if ($_POST['nocmd'] == "Save") { 

$sql = "SELECT * FROM `sites` WHERE id='".NUMO_SITE_ID."'";
$result = $dbObj->query($sql);
$defaultSettings = mysql_fetch_array($result);

$domain = htmlentities($numo->extractDomainName($_POST['registered_domain']));
$name = htmlentities($_POST['website_name']);
$location = htmlentities($_POST['subfolder_location']);
 
// validate domain name
$sql = "SELECT * FROM `sites` WHERE domain='{$domain}'";
$result = $dbObj->query($sql);
$row = mysql_fetch_array($result);
$domainError = "";
if ($domain == "") {
	$domain = $defaultSettings['domain'];
	$domainError = "Domain cannot be blank.";
} else if ($row['domain'] == $domain && $domain != $defaultSettings['domain']) {
	$domainError = "Domain {$domain} is already in use.";
	$domain = $defaultSettings['domain'];
} else if (!isValidDomain($domain)) {
	$domainError = "{$domain} is not a valid domain.";
	$domain = $defaultSettings['domain'];
} 
if ($name == "") {
	$name = $domain;
}
	$location = trim($location, '/'); 

	$update = "UPDATE sites SET domain='{$domain}', name='{$name}', location='{$location}' WHERE id='".NUMO_SITE_ID."'";
	$dbObj->query($update);

	if ($advancedLoginFeatures) {
		if ($_POST['admin_require_captcha'] == "") {
		  $admin_require_captcha = 0;	
		} else {
			$admin_require_captcha = 1;
		}
		if ($_POST['login_attempts_threshold'] == "") {
		    $login_attempts_threshold = 0;	
		} else {
			$login_attempts_threshold = $_POST['login_attempts_threshold'];
		}
		
		if ($_POST['bad_login_freeze_period'] == "") {
		    $bad_login_freeze_period = 30;	
		} else {
			$bad_login_freeze_period = $_POST['bad_login_freeze_period'];
		}		
		
		$update = "UPDATE sites SET admin_require_captcha='{$admin_require_captcha}', login_attempts_threshold='{$login_attempts_threshold}', bad_login_freeze_period='{$bad_login_freeze_period}' WHERE id='".NUMO_SITE_ID."'";
		$dbObj->query($update);
		
	}

}
?>
<h2>Manage Settings</h2> 


<?php if (REMOTE_SERVICE !== true && false) { ?>
<p>Please choose from one of the following settings options</p>
<div class="settings-option-box"><h2>Language Syntax</h2>
<p>Change any configurable syntax element here.</p>
<form method="get" action="module/<?=$_GET['m']?>/manage-syntax/">
<input type='submit' value='Edit' />
</form>
</div>

<div class="settings-option-box">
<h2>SSL Security</h2>
<p>Change how your site is secured via SSL.<br/>&nbsp;</p>
<form method="get" action="module/<?=$_GET['m']?>/manage-ssl-security/">
<input type='submit' value='Edit' />
</form>
</div>
<?php } ?>
<?php if (REMOTE_SERVICE === true || true) { ?>
<style type='text/css'>
	.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 50px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 30px; margin: 10px 0px 10px 210px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
html {padding-bottom: 50px;}
ul.form_display li label { width: 250px !important; }
</style>
<form method="post">
<input type="hidden" name="nocmd" value="Save" />
		<fieldset>
			<legend><?php echo $numo->getRegisteredDomain(); ?></legend>
			<ul class="form_display">
			<?php

			$sql = "SELECT * FROM `sites` WHERE id='".NUMO_SITE_ID."'";
			//print $sql;
			$result = $dbObj->query($sql);
			$row = mysql_fetch_array($result);
			if (REMOTE_SERVICE === true) {
			?>
				<li><label for="registered_domain">Registered Domain:</label><input type="text" name="registered_domain" id="registered_domain" value="<?=$row['domain']?>" /> <?=$domainError?></li>
				<li><label for="website_name">Website Name:</label><input type="text" name="website_name" id="website_name" value="<?=$row['name']?>" /></li>
				<li><label for="subfolder_location">Subfolder Name:</label><input type="text" name="subfolder_location" id="subfolder_location" value="<?=$row['location']?>" /></li>
			<?php } ?>
            	<li><label for="admin_email">Administrative Email:</label><input type="text" name="admin_email" id="admin_email" value="<?php print NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS; ?>" /></li>
<?php if ($advancedLoginFeatures) { ?>
				<li><label for="admin_require_captcha">Dashboard Login Requires CAPTCHA:</label><input type="checkbox" name="admin_require_captcha" id="admin_require_captcha" value="1" <?php if ($row['admin_require_captcha'] == "1") { print "checked"; } ?> /></li>
				<li><label for="login_attempts_threshold">Number of Login Attempts Allowed:</label>
                <select name="login_attempts_threshold" id="login_attempts_threshold">
                  <option value="0">Unlimited</option>
                  <option <?php if ($row['login_attempts_threshold'] == "1") { print "selected"; } ?> value="1">1</option>
                  <option <?php if ($row['login_attempts_threshold'] == "2") { print "selected"; } ?> value="2">2</option>
                  <option <?php if ($row['login_attempts_threshold'] == "3") { print "selected"; } ?> value="3">3</option>
                  <option <?php if ($row['login_attempts_threshold'] == "4") { print "selected"; } ?> value="4">4</option>
                  <option <?php if ($row['login_attempts_threshold'] == "5") { print "selected"; } ?> value="5">5</option>
                </select></li>
				<li><label for="bad_login_freeze_period">Bad Login Freeze Period):</label>
                <select name="bad_login_freeze_period" id="bad_login_freeze_period">
                  <option <?php if ($row['account_freeze_period'] == "5") { print "selected"; } ?> value="5">5 minutes</option>
                  <option <?php if ($row['bad_login_freeze_period'] == "10") { print "selected"; } ?> value="10">10 minutes</option>
                  <option <?php if ($row['bad_login_freeze_period'] == "15") { print "selected"; } ?> value="15">15 minutes</option>
                  <option <?php if ($row['bad_login_freeze_period'] == "30") { print "selected"; } ?> value="30">30 minutes</option>
                  <option <?php if ($row['bad_login_freeze_period'] == "45") { print "selected"; } ?> value="45">45 minutes</option>
                  <option <?php if ($row['bad_login_freeze_period'] == "60") { print "selected"; } ?> value="60">1 hour</option>
                  <option <?php if ($row['bad_login_freeze_period'] == "120") { print "selected"; } ?> value="120">2 hours</option>
                  <option <?php if ($row['bad_login_freeze_period'] == "1440") { print "selected"; } ?> value="1440">24 hours</option>
                </select>
                </li>
<!--				<li><label for="restrict_access_geolocation">Restrict Logins to :</label><input type="text" name="subfolder_location" id="subfolder_location" value="<?=$row['location']?>" /></li>
-->
<?php } ?>
            </ul>
		</fieldset>
        	<br /><br /><br />
	<div class="bttm_submit_button">
	<input type="submit"  value="Save"  />
	</div>
    </form>    
<?php } ?>

