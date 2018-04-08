<h2>Manage Settings</h2>
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

$htmlNewLines = array("<br>", "<BR>", "<br />", "<BR />");
$oddEvenCounter = 0;

$sql = "SELECT id, value FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' ORDER BY id";
$results = $dbObj->query($sql);

echo '<form method="post"><table class="table_data_layout"><tr><th class="highlight_label">Message Name</th><th>Value</th></tr>';

while($row = mysql_fetch_array($results)) {
	echo '<tr class="'.($oddEvenCounter++ % 2 == 0 ? 'even' : 'odd').'"><td>'.ucwords(strtolower(str_replace("_"," ",str_replace("-",": ",$row['id'])))).'</td><td><textarea name='.$row['id'].'>'.str_replace($htmlNewLines, "\r\n", $row['value']).'</textarea></td></tr>';
}

echo '</table><input type="hidden" value="save" name="cmd" /><input type="submit" value="Save" name="nocmd" /></form>';
?>