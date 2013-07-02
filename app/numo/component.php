<?php
//print "a";
    $display = "";
	if (REMOTE_SERVICE === true && DIRECT_PROCESSING === true) {
		$remoteFile = "http://".$numo->getRootFolder(true, true)."/numo.htm?cmd=show_code"; 
		global $numo;
		if ($numo->remotefileExists($remoteFile)) {
			$display = file_get_contents($remoteFile);
		} else {
			$display = file_get_contents('numo/numo.htm');
		}

// if custom error page found display it
	} else if (file_exists("numo.htm")) {
	  $display = file_get_contents("numo.htm");

// display system default error page
} else if (file_exists("numo/numo.htm")) {
	$display = file_get_contents('numo/numo.htm');
} else {
	$display = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>[NUMO.SETTINGS: ERROR TITLE]</title>
	<style>
	h2 {margin: 0px; padding: 5px 0px; font-size: 1.1em; color: #2A61BD;}
	</style>
	<!-- this file is auto generated internally by the numo system -->
</head>
<body>
<h2>[NUMO.SETTINGS: ERROR TITLE]</h2>
[NUMO.SETTINGS: ERROR]
</body>
</html>';
}

$constantName = "NUMO_SYNTAX_".strtoupper($_GET['module'])."_".strtoupper($_GET['component'])."_COMPONENT_HEADING";
//print $constantName; 
if(defined($constantName)) {
	$display = str_replace("[NUMO.SETTINGS: ERROR TITLE]",constant($constantName),$display);
	$display = str_replace("[NUMO.SETTINGS: ERROR_TITLE]",constant($constantName),$display);
	$display = str_replace("<numo module='settings' component='error_title'></numo>",constant($constantName),$display);
} else {
	$display = str_replace("<h2>[NUMO.SETTINGS: ERROR TITLE]</h2>","",$display);
	$display = str_replace("[NUMO.SETTINGS: ERROR TITLE]","",$display);

}



$display = str_replace("[NUMO.SETTINGS: ERROR]","[NUMO.".$_GET['module'].": ".$_GET['component']."]",$display);
$display = str_replace("<numo module='settings' component='error'></numo>","[NUMO.".$_GET['module'].": ".$_GET['component']."]",$display);
$display = str_replace("<numo module='settings' component='error'></numo>","[ numo module='".$_GET['module']."' component='".$_GET['component']."'][/numo]",$display);
//print "a";
print $display;
//print "z";
//exit;
?>