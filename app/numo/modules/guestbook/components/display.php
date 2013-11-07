<?php
if ($PARAMS['id'] == "" && $_GET['id'] != "") {
  $PARAMS['id'] = $_GET['id'];
}

if(!isset($PARAMS['entry_saved']) && FACEBOOK === true) {
	global $facebook;
		$user = $facebook->getUser(); 
if (!$user) { 
?>
          <p>Please <fb:login-button></fb:login-button> in order to activate or administer your FireWidget WebPortal. </p>
<? } else { 
?>
[NUMO.GUESTBOOK: SUBMIT FORM(id=<?php echo $PARAMS['id']; ?>&combo=1)]
<?php
}
}

if($PARAMS['entry_saved'] == 1 || !($_POST['cmd'] == "submit_numo_form" && $_POST['guestbook_id'] == $PARAMS['id'])) {
    $dbObj->query("SET NAMES UTF8");
	
	$sql = "SELECT * FROM `guestbook_types` t WHERE t.`id`='".$PARAMS['id']."' AND t.site_id='".NUMO_SITE_ID."'";
	//print $sql;
	$gbRes = $dbObj->query($sql);
	$gbRec = mysql_fetch_array($gbRes);
	

		define("SYSTEM_META_DESCRIPTION", $gbRec['name']);
		define("SYSTEM_META_KEYWORDS", $gbRec['name']);
		define("SYSTEM_META_TITLE", $gbRec['name']);


	$sql = "SELECT f.`slot`, f.`name`, f.`input_type` FROM `guestbook_fields` f, `guestbook_types` t where t.id=f.type_id AND f.`type_id`='".$PARAMS['id']."' AND site_id='".NUMO_SITE_ID."'";
	$results = $dbObj->query($sql);

	$sql = "SELECT *, DATE_FORMAT(when_created,'%c/%e/%Y %l:%i %p') as 'when_created_frmtd' FROM `guestbook_responses` WHERE pending='0' AND `type_id`='".$PARAMS['id']."' ORDER BY `when_created` desc";
	$responses = $dbObj->query($sql);
	//print mysql_error();
    
    if (mysql_num_rows($results) == 0) {
		return;
	}
	$layout = ABSOLUTE_ROOT_PATH."numo/modules/guestbook/layouts/listing.htm";
	$layoutDisplay = file_get_contents($layout);


	$content = preg_replace('/<style>.*?<\/style>/si', '', $layoutDisplay);
	$styles = preg_replace('/<\/style>.*/si', '</style>', $layoutDisplay);
    if (FACEBOOK !== true) {
	  echo $styles;
	}

	while($response = mysql_fetch_array($responses)) {
		$entryDisplay = '<table>';

		while($row = mysql_fetch_array($results)) {
			if($response['slot_'.$row['slot']] != "" && $row['slot'] != 1) {
				if($row['input_type'] == "textarea") {
					$entryDisplay .= '<tr><td>'.nl2br($response['slot_'.$row['slot']]).'</td></tr>';
				} else if ($row['input_type'] != "captcha") {
					$entryDisplay .= '<tr><td><span class="numo_guestbook_field_label">'.$row['name'].': </span>';
					if ($row['input_type'] == "website address") {
						if (!strstr($response['slot_'.$row['slot']], "http://") && !strstr($response['slot_'.$row['slot']], "https://")) {
							$websiteAddress = "http://".$response['slot_'.$row['slot']];
						} else {
							$websiteAddress = $response['slot_'.$row['slot']];
						}
						
						$entryDisplay .= "<a href='{$websiteAddress}' target='_blank'>{$websiteAddress}</a>";
					} else if ($row['input_type'] == "email") {
						$entryDisplay .= "<a href='mailto:{$response['slot_'.$row['slot']]}' target='_blank'>{$response['slot_'.$row['slot']]}</a>";
						
					} else {
					  $entryDisplay .= $response['slot_'.$row['slot']];	
					}
					$entryDisplay .= '</td></tr>';
				}
			}
		}
		$entryDisplay .= '</table>';

		$patterns = array();
		$patterns[0] = '/<!'.'-- #BeginSlot "slot_1" -->.*?<!'.'-- #EndSlot -->/si';
		$patterns[1] = '/<!'.'-- #BeginSlot "date" -->.*?<!'.'-- #EndSlot -->/si';
		$patterns[2] = '/<!'.'-- #BeginSlot "content" -->.*?<!'.'-- #EndSlot -->/si';

		$replacements = array();
		$replacements[0] = $response['slot_1'];
		if (FACEBOOK == true) {
		  $replacements[1] = date("F j, Y", strtotime($response['when_created_frmtd']));;
			
		} else {
		  $replacements[1] = $response['when_created_frmtd'];
		}
		$replacements[2] = str_replace('$', '\$', $entryDisplay);

		echo preg_replace($patterns, $replacements, $content);

		mysql_data_seek($results, 0);
	}

	mysql_free_result($results);
	mysql_free_result($responses);
}
if(!isset($PARAMS['entry_saved']) && FACEBOOK !== true) {
?>
[NUMO.GUESTBOOK: SUBMIT FORM(id=<?php echo $PARAMS['id']; ?>&combo=1)]
<?php
}
?>