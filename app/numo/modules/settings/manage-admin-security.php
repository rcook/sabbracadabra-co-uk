<?php
if ($_POST['account_id'] == "") {
	return false;
}

// assert priviledge table here
$result = $dbObj->query("CREATE TABLE IF NOT EXISTS `admin_privileges` (
`account_id` INT( 11 ) NOT NULL ,
`module` VARCHAR( 255 ) NOT NULL ,
`components` TEXT NOT NULL ,
`site_id` INT( 11 ) NOT NULL,
PRIMARY KEY ( `account_id` , `module` , `site_id` )
)");

if($_POST['cmd'] == "save") {
	$changes = "";
    $moduleComponents = array();
	foreach($_POST['components'] as $key => $value) {
		$keyData = explode("__", $value);
		$module = $keyData[0];
		$moduleComponents["{$module}"][] = $keyData[1];
	}
	foreach ($moduleComponents as $moduleName => $components) {
	  
	  $componentsData =  implode(",", $components);
	  $update = "REPLACE INTO admin_privileges SET account_id='{$_POST['account_id']}', site_id='".NUMO_SITE_ID."', module='{$moduleName}', components='{$componentsData}'";
	  $dbObj->query($update);
	}
		
  header("Location: ".NUMO_FOLDER_PATH."module/accounts/account-edit/?id={$_POST['account_id']}");
  exit;
		
	
} 
?>
<h2>Manage Administrative Functions Security</h2>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/jquery-ui-1.8.2.custom.min.js"></script>
<script type="text/javascript">
	$(function(){
		$('#tabs').tabs();
	});
</script>
<style>

.error {color: #900; font-weight: bold;}

.ui-helper-hidden { display: none; }
.ui-helper-hidden-accessible { position: absolute; left: -99999999px; }
.ui-helper-reset { margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none; }
.ui-helper-clearfix:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
.ui-helper-clearfix { display: inline-block; }
/* required comment for clearfix to work in Opera \*/
* html .ui-helper-clearfix { height:1%; }
.ui-helper-clearfix { display:block; }
/* end clearfix */

.ui-widget-content { border: 1px solid #ccc; background: #fff; color: #333333; }
.ui-widget-content a { color: #333333; }
.ui-widget-header { border: 0px solid #2A61B3; background: none; color: #ffffff; font-weight: bold; }
.ui-widget-header a { color: #ffffff; }

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { font-size: 8pt; border: 1px solid #cccccc; background: #eee; font-weight: bold; color: #3473D1; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #3473D1; text-decoration: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { border: 1px solid #3473D1; background: #DBE6F7; font-weight: bold; color: #3473D1; }
.ui-state-hover a, .ui-state-hover a:hover { color: #3473D1; text-decoration: none; }
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active { border: 1px solid #DBE6F7; background: #ffffff; font-weight: bold; color: #2A61B3; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #2A61B3; text-decoration: none; }
.ui-widget :active { outline: none; }
.ui-tabs { position: relative; padding: .2em; zoom: 1; } /* position: relative prevents IE scroll bug (element with position: relative inside container with overflow: auto appear as "fixed") */
.ui-tabs .ui-tabs-nav { margin: 0; padding: .2em .2em 0; }
.ui-tabs .ui-tabs-nav li { list-style: none; float: left; position: relative; top: 1px; margin: 0 .2em 1px 0;  padding: 0; white-space: nowrap; }
.ui-tabs .ui-tabs-nav li a { float: left; padding: .5em 1em; text-decoration: none; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected { margin-bottom: 0; padding-bottom: 0px; border-bottom: 1px solid #ffffff;}
.ui-tabs .ui-tabs-nav li.ui-tabs-selected a, .ui-tabs .ui-tabs-nav li.ui-state-disabled a, .ui-tabs .ui-tabs-nav li.ui-state-processing a { cursor: text; }
.ui-tabs .ui-tabs-nav li a, .ui-tabs.ui-tabs-collapsible .ui-tabs-nav li.ui-tabs-selected a { cursor: pointer; } /* first selector in group seems obsolete, but required to overcome bug in Opera applying cursor: text overall if defined elsewhere... */
.ui-tabs .ui-tabs-panel { display: block; border-width: 0; padding: 1em 0em; background: none; }
.ui-tabs .ui-tabs-hide { display: none !important; }
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
		$contentContainer .= '<div id="tabs-'.$currentModule.'"><table class="table_data_layout">';

	} else if($currentModule != $currentSetting) {
		$currentModule = $currentSetting;
		$oddEvenCounter = $oddEvenCounter % 2 == 0 ? $oddEvenCounter : $oddEvenCounter + 1;

		$contentHeadings .= '<li><a href="#tabs-'.$currentModule.'">'.ucwords(str_replace('_',' ',$currentSetting)).'</a></li>';
		$contentContainer .= '</table></div><div id="tabs-'.$currentModule.'"><table class="table_data_layout">';
	}
	// open file handler for directory specified
	
	if ($handle = opendir(MODULES_FOLDER_NAME."/".$currentSetting)) {
		// loop through each file/folder in directory
		while (false !== ($file = readdir($handle))) {
			//print $file." -- ".strrpos($file, ".php")." -- ". strlen($file)."<br>";
			if (strrpos($file, ".php") + 4 == strlen($file) ) {
				$component = str_replace(".php", "", $file);
				$contentContainer .= '<tr class="'.($oddEvenCounter++ % 2 == 0 ? 'even' : 'odd').'"><td><input type="checkbox" name="components[]" value="'.$currentSetting.'__'.$component.'" '.($access->hasAccess($currentSetting, $component, $_POST['account_id']) ? "checked" : "").' />'.ucwords(strtolower(str_replace("_"," ",str_replace("-"," ",$component)))).'</td></tr>';
			}
		}
	
		// close file handler
		closedir($handle);
	}

    
}

if($currentModule != "") {
	$contentContainer .= '</table></div>';
}

echo '<ul>'.$contentHeadings.'</ul>'.$contentContainer;
?>
</div>
<input type="hidden" value="save" name="cmd" />
<input type="submit" value="Save" name="nocmd" />
</form>