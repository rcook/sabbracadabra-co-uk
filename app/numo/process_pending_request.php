<?php
// stop displaying output
ob_start();

// generate request display
$sql = "SELECT account_id, module, component FROM pending_requests WHERE id='".$_GET['id']."' AND site_id=".NUMO_SITE_ID;
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	print "[NUMO.".$row['module'].": ".$row['component']."(aid=".$row['account_id'].")]";
} else {
	print NUMO_SYNTAX_NUMO_INVALID_REQUEST_CODE;
}

// copy buffered print text
$requestDisplay = ob_get_contents();

// clear buffered print text (start displaying output again)
ob_end_clean();

$display = "";

// if custom error page found display it
if(file_exists("numo.htm")) {
	$display = file_get_contents("numo.htm");

// display system default error page
} else {
	$display = file_get_contents('numo/numo.htm');
}

//$display = str_replace("[NUMO.SETTINGS: ERROR TITLE]","Processing Request...",$display);
$display = str_replace("[NUMO.SETTINGS: ERROR TITLE]","",$display);
$display = str_replace("[NUMO.SETTINGS: ERROR]",$requestDisplay,$display);

print $display;
?>