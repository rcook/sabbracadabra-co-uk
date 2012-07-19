<?php

if($_POST['cmd'] == "save") {
	$changes = "";

	foreach($_POST as $key => $value) {
		if($key != "cmd" && $key != "nocmd") {
			$sql = "UPDATE language_syntax SET value='".str_replace("\r\n",'<br>',$value)."' WHERE id='".$key."' AND site_id='".NUMO_SITE_ID."'";
			//print $sql;
			$dbObj->query($sql);
		}
	}
}
?>
<h2>Manage Syntax Settings</h2>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/jquery-ui-1.8.2.custom.min.js"></script>
<script type="text/javascript">
	$(function(){
		$('#tabs').tabs();
	});
</script>
<style>
table.table_data_layout {
	width: 100%; 
}
td.syntax_field_name {
	width: 250px;
}
table.table_data_layout textarea {
	width: 100% !important;
}
	
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
.ui-widget-header { border: 1px solid #2A61B3; background: #2A61B3 repeat-x; color: #ffffff; font-weight: bold; }
.ui-widget-header a { color: #ffffff; }

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { border: 1px solid #cccccc; background: #eee; font-weight: bold; color: #3473D1; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #3473D1; text-decoration: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { border: 1px solid #3473D1; background: #DBE6F7; font-weight: bold; color: #3473D1; }
.ui-state-hover a, .ui-state-hover a:hover { color: #3473D1; text-decoration: none; }
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active { border: 1px solid #DBE6F7; background: #ffffff; font-weight: bold; color: #2A61B3; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #2A61B3; text-decoration: none; }
.ui-widget :active { outline: none; }
.ui-tabs { position: relative; padding: .2em; zoom: 1; } /* position: relative prevents IE scroll bug (element with position: relative inside container with overflow: auto appear as "fixed") */
.ui-tabs .ui-tabs-nav { margin: 0; padding: .2em .2em 0; }
.ui-tabs .ui-tabs-nav li { list-style: none; float: left; position: relative; top: 1px; margin: 0 .2em 1px 0; border-bottom: 0 !important; padding: 0; white-space: nowrap; }
.ui-tabs .ui-tabs-nav li a { float: left; padding: .5em 1em; text-decoration: none; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected { margin-bottom: 0; padding-bottom: 1px; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected a, .ui-tabs .ui-tabs-nav li.ui-state-disabled a, .ui-tabs .ui-tabs-nav li.ui-state-processing a { cursor: text; }
.ui-tabs .ui-tabs-nav li a, .ui-tabs.ui-tabs-collapsible .ui-tabs-nav li.ui-tabs-selected a { cursor: pointer; } /* first selector in group seems obsolete, but required to overcome bug in Opera applying cursor: text overall if defined elsewhere... */
.ui-tabs .ui-tabs-panel { display: block; border-width: 0; padding: 1em 0em; background: none; }
.ui-tabs .ui-tabs-hide { display: none !important; }

.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 50px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 30px; margin: 10px 0px 10px 210px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
html {padding-bottom: 50px;}

</style>


<form method="post">
<div id="tabs">
<?php
$htmlNewLines = array("<br>", "<BR>", "<br />", "<BR />");
$oddEvenCounter = 0;

$sql = "SELECT id, value FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' ORDER BY id";
$results = $dbObj->query($sql);

$currentModule = "";
$contentHeadings = "";
$contentContainer = "";

while($row = mysql_fetch_array($results)) {
	$currentSetting = substr($row['id'], 0, strpos($row['id'], "-"));

	if($currentModule == "") {
		$currentModule = $currentSetting;

		$contentHeadings .= '<li><a href="#tabs-'.$oddEvenCounter.'">'.str_replace('_',' ',$currentSetting).'</a></li>';
		$contentContainer .= '<div id="tabs-'.$oddEvenCounter.'"><table class="table_data_layout">';

	} else if($currentModule != $currentSetting) {
		$currentModule = $currentSetting;
		$oddEvenCounter = $oddEvenCounter % 2 == 0 ? $oddEvenCounter : $oddEvenCounter + 1;

		$contentHeadings .= '<li><a href="#tabs-'.$oddEvenCounter.'">'.str_replace('_',' ',$currentSetting).'</a></li>';
		$contentContainer .= '</table></div><div id="tabs-'.$oddEvenCounter.'"><table class="table_data_layout">';
	}
    if ($row['id'] == "NUMO-TIMEZONE_CODE") {
		$extraDescription = "<br/><a style='width: auto; text-align: left; line-height: 1 !important;' href='http://www.php.net/manual/en/timezones.php' target='_blank'>(Available timezone codes)</a>"; 
	} else {
		$extraDescription = "";
	}
	$contentContainer .= '<tr class="'.($oddEvenCounter++ % 2 == 0 ? 'even' : 'odd').'"><td class="syntax_field_name">'.ucwords(strtolower(str_replace("_"," ",str_replace($currentModule."-","",$row['id'])))).$extraDescription.'</td><td><textarea name='.$row['id'].'>'.str_replace($htmlNewLines, "\r\n", $row['value']).'</textarea></td></tr>';
}

if($currentModule != "") {
	$contentContainer .= '</table></div>';
}

echo '<ul>'.$contentHeadings.'</ul>'.$contentContainer;
?>
</div>
	<div class="bttm_submit_button">
<input type="hidden" value="save" name="cmd" />
<input type="submit" value="Save Syntax Settings" name="nocmd" />
	</div>

</form>