<?php
if ($_POST['account_id'] == "") {
	return false;
}
//error_reporting (E_ALL);
// assert priviledge table here
$result = $dbObj->query("CREATE TABLE IF NOT EXISTS `admin_privileges` (
`account_id` INT( 11 ) NOT NULL ,
`module` VARCHAR( 255 ) NOT NULL ,
`components` TEXT NOT NULL ,
`site_id` INT( 11 ) NOT NULL,
PRIMARY KEY ( `account_id` , `module` , `site_id` )
)");
foreach ($_POST as $x => $y) {
	//print $x."=".$y."<br>";
}
if($_POST['cmd'] == "save") {
	$changes = "";
	//print "yes";
    $moduleComponents = array();
	foreach($_POST['components'] as $key => $value) {
	//	print "$key = $value <br>";
		$keyData = explode("__", $value);
		$module = $keyData[0];
		$moduleComponents["{$module}"][] = $keyData[1];
	}
	$dbObj->query("DELETE FROM admin_privileges WHERE account_id='{$_POST['account_id']}' AND site_id='".NUMO_SITE_ID."'");

	//print "DELETE FROM admin_privileges WHRE account_id='{$_POST['account_id']}' AND site_id='".NUMO_SITE_ID."'";
	foreach ($moduleComponents as $moduleName => $components) {
	  
	  $componentsData =  implode(",", $components);
	  $update = "REPLACE INTO admin_privileges SET account_id='{$_POST['account_id']}', site_id='".NUMO_SITE_ID."', module='{$moduleName}', components='{$componentsData}'";
	//  print $update;
	  $dbObj->query($update);
	}
		
  header("Location: ".NUMO_FOLDER_PATH."module/accounts/account-edit/?id={$_POST['account_id']}");
  exit;
		
	
} 
?>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/settings/general/">System</a> <span class="divider">/</span></li>
  <li class="active">Administrative Access</li>
</ul>
<h3>Manage Administrative Functions Security</h3>
<script type="text/javascript">
	$(function(){
	//	$('#tabs').tabs();
	
	jQuery('#securitytabs a').click(function (e) {
      e.preventDefault();
      jQuery(this).tab('show');
	  
    })
	jQuery('#securitytabs a:first').tab('show');
	});
</script>
<style>

.error {color: #900; font-weight: bold;}

</style>


<form method="post">
<input type='hidden' name='account_id' value='<?=$_POST['account_id']?>' />
<div id="tabs">
<?php
$currentModule = "";

foreach ($modules as $currentSetting) {

	if($currentModule == "") {
		$currentModule = $currentSetting;

		$contentHeadings .= '<li><a href="#tabs-'.$currentModule.'">'.ucwords(str_replace('_',' ',$currentSetting)).'</a></li>';
		$contentContainer .= '<div class="tab-pane" id="tabs-'.$currentModule.'"><table class="table_data_layout">';

	} else if($currentModule != $currentSetting) {
		$currentModule = $currentSetting;
		$oddEvenCounter = $oddEvenCounter % 2 == 0 ? $oddEvenCounter : $oddEvenCounter + 1;

		$contentHeadings .= '<li><a href="#tabs-'.$currentModule.'">'.ucwords(str_replace('_',' ',$currentSetting)).'</a></li>';
		$contentContainer .= '</table></div><div class="tab-pane" id="tabs-'.$currentModule.'"><table class="table_data_layout">';
	}
	// open file handler for directory specified
	
	if ($handle = opendir(MODULES_FOLDER_NAME."/".$currentSetting)) {
		// loop through each file/folder in directory
		while (false !== ($file = readdir($handle))) {
			//print $file." -- ".strrpos($file, ".php")." -- ". strlen($file)."<br>";
			if (strrpos($file, ".php") + 4 == strlen($file) ) {
				$component = str_replace(".php", "", $file);
				$contentContainer .= '<tr class="'.($oddEvenCounter++ % 2 == 0 ? 'even' : 'odd').'"><td><input style="vertical-align: top; margin-top: 0px; padding-top: 0px;" type="checkbox" name="components[]" value="'.$currentSetting.'__'.$component.'" '.($access->hasAccess($currentSetting, $component, $_POST['account_id']) ? "checked" : "").' /> <label style="vertical-align: top; margin: -3px 0px 0px 10px; display: inline-block; ">'.ucwords(strtolower(str_replace("_"," ",str_replace("-"," ",$component)))).'</label></td></tr>';
			}
		}
	
		// close file handler
		closedir($handle);
	}

    
}

if($currentModule != "") {
	$contentContainer .= '</table></div>';
}

echo '<ul class="nav nav-tabs" id="securitytabs">'.$contentHeadings.'</ul><div class="tab-content">'.$contentContainer."</div>";
?>
</div>
<input type="hidden" value="save" name="cmd" />
<input type="submit" class='btn btn-success btn-large' value="Save" name="nocmd" />
</form>