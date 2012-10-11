<?php
if ($PARAMS['id'] == "" && $_GET['id'] != "") {
  $PARAMS['id'] = $_GET['id'];
}

if($PARAMS['entry_saved'] == 1 || !($_POST['cmd'] == "submit_numo_form" && $_POST['guestbook_id'] == $PARAMS['id'])) {
    $dbObj->query("SET NAMES UTF8");


	$sql = "SELECT f.`slot`, f.`name`, f.`input_type` FROM `guestbook_fields` f, `guestbook_types` t where t.id=f.type_id AND f.`type_id`='".$PARAMS['id']."'";
	$results = $dbObj->query($sql);

	$sql = "SELECT *, DATE_FORMAT(when_created,'%c/%e/%Y %l:%i %p') as 'when_created_frmtd' FROM `guestbook_responses` WHERE pending='0' AND `type_id`='".$PARAMS['id']."' ORDER BY `when_created` desc";
	$responses = $dbObj->query($sql);
	//print mysql_error();
    ?>
    <?php
	$layout = "numo/modules/guestbook/layouts/listing.htm";
	$layoutDisplay = file_get_contents($layout);

	$content = preg_replace('/<style>.*?<\/style>/si', '', $layoutDisplay);
	$styles = preg_replace('/<\/style>.*/si', '</style>', $layoutDisplay);

	echo $styles;

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
		$replacements[1] = $response['when_created_frmtd'];
		$replacements[2] = str_replace('$', '\$', $entryDisplay);

		echo preg_replace($patterns, $replacements, $content);

		mysql_data_seek($results, 0);
	}

	mysql_free_result($results);
	mysql_free_result($responses);
}
if(!isset($PARAMS['entry_saved'])) {
?>
[NUMO.GUESTBOOK: SUBMIT FORM(id=<?php echo $PARAMS['id']; ?>&combo=1)]
<?php
}
?>