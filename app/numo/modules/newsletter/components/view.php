<?php
$newsletterId = $_GET['id'];

global $displayStyles;
global $sectionValues;
    $dbObj->query("SET NAMES UTF8");

$sql = "SELECT * FROM newsletter_messages WHERE id='".$newsletterId."' AND site_id='".NUMO_SITE_ID."'";

if(!isset($_GET['id'])) {
	$sql = "SELECT * FROM newsletter_messages WHERE status=1 AND site_id='".NUMO_SITE_ID."' ORDER BY id desc LIMIT 1";
}

//print $sql;
$result = $dbObj->query($sql);

if($newsletterInfo = mysql_fetch_array($result)) {
	//$layout = substr(NUMO_FOLDER_PATH,1)."modules/newsletter/layouts/".$newsletterInfo['layout'];
	$layout = $_SERVER['DOCUMENT_ROOT'].NUMO_FOLDER_PATH."modules/newsletter/layouts/".$newsletterInfo['layout'];

	// escape & symbol in text
	$newsletterMessage = str_replace("&", "%26", $newsletterInfo['message']);

	//replace end of section code with & for parsing
	$newsletterMessage = str_replace("[NUMO|END]", "&", $newsletterMessage);

	//parse message sections
	parse_str($newsletterMessage, $sectionValues);

	//get layout HTML file contents
	$layoutDisplay = file_get_contents($layout);

	//get css class declarations for color and set for P, DIV, H1, H2, H3 to overwrite page default styles
	$pattern = '/(\..*?)\s{.*?(color): (.*?)[\s;]/i';
	preg_replace_callback($pattern, "setupDisplayStyles", $layoutDisplay);

	//display styles to overwrite page default text colors
	print "<style>".$displayStyles."</style>";

	//display newsletter message
	$pattern = '/<!'.'-- #BeginSection "(.*?)" -->(.*?)<!'.'-- #EndSection -->/si';
	$display = preg_replace_callback($pattern, 'initialize_section', $layoutDisplay);

	//display compliled message
	print $display;
}

function initialize_section($matches) {
	global $sectionValues;

	return '<!'.'-- #BeginSection "'.$matches[1].'" -->'.html_entity_decode(stripslashes($sectionValues[$matches[1]])).'<!'.'-- #EndSection -->';
}

function setupDisplayStyles($matches) {
	global $displayStyles;

	$displayStyles .= $matches[1]." p, ".$matches[1]." h1, ".$matches[1]." h2, ".$matches[1]." h3, ".$matches[1]." div {".$matches[2].":".$matches[3]."}\n";
}
?>