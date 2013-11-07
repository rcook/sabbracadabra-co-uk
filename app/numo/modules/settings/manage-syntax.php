<?php

if($_POST['cmd'] == "save") {
	$changes = "";

	foreach($_POST as $key => $value) {
		if($key != "cmd" && $key != "nocmd") {
			$sql = "UPDATE language_syntax SET value='".str_replace("\r\n",'<br>',$value)."' WHERE id='".$key."' AND site_id='".NUMO_SITE_ID."'";
			//print $sql."<br>";
			$dbObj->query($sql);
		}
	}
}
?>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/settings/general/">System</a> <span class="divider">/</span></li>
  <li class="active">Syntax Settings</li>
</ul>
<h3>Manage Syntax Settings</h3>
<script type="text/javascript">
	$(function(){
	//	$('#tabs').tabs();
	
					
	jQuery('#syntaxtabs a').click(function (e) {
      e.preventDefault();
      jQuery(this).tab('show');
	  
    })
	jQuery('#syntaxtabs a:first').tab('show');
	});
</script>
<style>
table.table_data_layout {
	width: 100%; 
}
table.table_data_layout {
	border: 0px !important;
}

td.syntax_field_name {
	width: 250px;
		font-size: 12px !important;

}
table.table_data_layout textarea {
	width: 98% !important;
}


.bttm_submit_button { position: fixed; bottom: 0px; right: 0px; background: #aaaaaa;  width: 100%; height: 70px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input { margin: 10px 0px 10px 210px;}
html {padding-bottom: 70px;}

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
	if ($currentSetting == "RESTRICTED") {
		$mod = "ACCESS_CONTROL";
	} else {
		$mod = $currentSetting;
	}
	//print $currentSetting;
	if (moduleInstalled($mod) || $currentSetting == "NUMO" || $currentSetting == "ACCOUNT") {
	
		if($currentModule == "") {
			$currentModule = $currentSetting;
	
			$contentHeadings .= '<li><a href="#tabs-'.$oddEvenCounter.'">'.str_replace('_',' ',$currentSetting).'</a></li>';
			$contentContainer .= '<div  class="tab-pane" id="tabs-'.$oddEvenCounter.'"><table class="table_data_layout">';
	
		} else if($currentModule != $currentSetting) {
			$currentModule = $currentSetting;
			$oddEvenCounter = $oddEvenCounter % 2 == 0 ? $oddEvenCounter : $oddEvenCounter + 1;
	
			$contentHeadings .= '<li><a href="#tabs-'.$oddEvenCounter.'">'.str_replace('_',' ',$currentSetting).'</a></li>';
			$contentContainer .= '</table></div><div  class="tab-pane" id="tabs-'.$oddEvenCounter.'"><table class="table_data_layout">';
		}
		if ($row['id'] == "NUMO-TIMEZONE_CODE") {
			$extraDescription = "<br/><a style='width: auto; text-align: left; line-height: 1 !important;' href='http://www.php.net/manual/en/timezones.php' target='_blank'>(Available timezone codes)</a>"; 
		} else {
			$extraDescription = "";
		}
		
	    $contentContainer .= '<tr class="'.($oddEvenCounter++ % 2 == 0 ? 'even' : 'odd').'"><td class="syntax_field_name">'.ucwords(strtolower(str_replace("_"," ",str_replace($currentModule."-","",$row['id'])))).$extraDescription.'</td><td><textarea name='.$row['id'].'>'.str_replace($htmlNewLines, "\r\n", $row['value']).'</textarea></td></tr>';
	}
}

if($currentModule != "") {
	$contentContainer .= '</table></div>';
}

echo '<ul  class="nav nav-tabs" id="syntaxtabs" >'.$contentHeadings.'</ul><div class="tab-content">'.$contentContainer."</div>";
?>
</div>
	<div class="bttm_submit_button">
<input type="hidden" value="save" name="cmd" />
<input type="submit" value="Save" class='btn btn-large btn-success' name="nocmd" />
	</div>

</form>