<?php
$licenseCheckResponse = "";

include('configuration/landing.php');

if ($_POST['cmd'] == "change_module_status") {
  $update = "UPDATE modules SET `status`='{$_POST['status']}' WHERE name='{$_POST['module']}' AND site_id='".NUMO_SITE_ID."'";
  $dbObj->query($update);
}
if($_POST['cmd'] == "install_new_module") {
	// if valid license key
	//print "yup";
	//print check_license_key($_POST['license_key'],$_POST['module']);
	if(($licenseCheckResponse = check_license_key($_POST['license_key'],$_POST['module'])) == "") {
		//print "inside if for ".$_POST['license_key']."-".$_POST['module'];
		//run module initialization SQL code
		run_sql_configuration($_POST['module'], false, $_POST['license_key']);
		header('Location: '.NUMO_FOLDER_PATH);
	}
} else if ($_POST['cmd'] == "update_license_key") { 
   // print "yup";
	if(($licenseCheckResponse = check_license_key($_POST['license_key'],$_POST['module'])) == "") {
		//print "inside if for ".$_POST['license_key']."-".$_POST['module'];
		//run module initialization SQL code
		$sql = "UPDATE modules SET license_key='{$_POST['license_key']}' WHERE license_key='' AND site_id='".NUMO_SITE_ID."' AND name='".$_POST['module']."'";
		//print $sql;
		$dbObj->query($sql); 
		//print mysql_error();
		//print "done {$_POST['module']}-{$_POST['license_key']}";
		//run_sql_configuration($_POST['module'], false, $_POST['license_key']);
		//header('Location: '.NUMO_FOLDER_PATH); 
	} else {
		//print "not done -- bad license key check";
	}
}

if(count($modulesToInstall) > 0) {
	foreach($modulesToInstall as $key => $module) {
		?>
		<div class="module_pending_install">
		 <h2>Module: <?=$module?></h2>
		 <p>Pending Installation</p>
		 <?php if($_POST['module'] == $module) { print '<p class="error">Error: '.str_replace("**","",$licenseCheckResponse).'</p>'; } ?>
		 <form method="post">
		 	<label for="license_key__<?=$module?>">License Key:</label><input type="text" name="license_key" id="license_key__<?=$module?>" />
		 	<input type="hidden" name="module" value="<?=$module?>" />
		 	<input type="hidden" name="cmd" value="install_new_module" />
		 	<input type="submit" name="nocmd" value="Install" />
		 </form>
		</div>
		<?php
	}
}
$result = $dbObj->query("SHOW COLUMNS FROM `modules` LIKE 'status'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `modules` ADD `status` tinyint(4) default 1");
}

$result = $dbObj->query("SHOW COLUMNS FROM `modules` LIKE 'license_key'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `modules` ADD `license_key` varchar(100) default ''");
}
//print mysql_error();

foreach( $modules as $key => $module) {
	$query = "SELECT * FROM modules WHERE name='{$module}' AND site_id='".NUMO_SITE_ID."'";
	//print $query;
	$moduleResult = $dbObj->query($query);
	$moduleRecord = mysql_fetch_array($moduleResult);
	if ($moduleRecord['license_key'] == "" && $moduleRecord['name'] != "accounts" && $moduleRecord['name'] != "settings") { ?>
		<div class="module_install_completed">
		 <h2><?=ucwords(str_replace("_", " ", $module))?></h2>
         <hr />
		 <p>Requires License Key</p>
		 <form method="post">
		 	<label for="license_key__<?=$module?>">License Key:</label><input type="text" name="license_key" id="license_key__<?=$module?>" />
		 	<input type="hidden" name="module" value="<?=$module?>" />
		 	<input type="hidden" name="cmd" value="update_license_key" />
		 	<input type="submit" name="nocmd" value="Re-Key" />
		 </form>
		</div>
        <?php
	} else {
	  include('modules/'.$module.'/configuration/landing.php');
	}
}

?>
<form name="change_module_status" id="change_module_status" method='post'>
<input type='hidden' name='module' value='' />
<input type='hidden' name='status' value='' />
<input type='hidden' name='cmd' value='change_module_status' />
</form>
<script>
function changeModuleStatus(module, newStatus) {
	document.change_module_status.module.value = module;
	document.change_module_status.status.value = newStatus;
	document.change_module_status.submit();
}
var maxDivHeight = 0;
$(document).ready(function() {
						   $(".module_install_completed").each(function() {
																		if ($(this).height() > maxDivHeight) {
																			maxDivHeight = $(this).height();
																		}
						  
						   });
						    $(".module_install_completed").height(maxDivHeight);
						   });
</script>