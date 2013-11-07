<?php
$sql = "SELECT * FROM guestbook_types WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
//print $sql;
$results = $dbObj->query($sql);
$menu = "";
$guestbookCount = mysql_num_rows($result);

while($row = mysql_fetch_array($results)) {
	$link = str_replace("[File]", str_replace("/numo/", "/", NUMO_FOLDER_PATH)."manage.numo?module=guestbook&component=display&id=".$row['id'], NUMO_SYNTAX_ACCOUNT_LINK);
	if ($PARAMS['render_within_submenu'] != 1 && $bootstrapStyling) {
	  $menu .= str_replace("[Label]", "<i class='icon-book'></i> ".$row['name'], $link);
		
	} else {
	  $menu .= str_replace("[Label]", $row['name'], $link);
	}
}
	if ($menu != "") {
		if ($PARAMS['render_within_submenu'] == 1 && $guestbookCount > 1) {
			print "\n".'<li class="dropdown-submenu">';
			print '<a class="dropdown-toggle" href="#"><i class="icon-book"></i> Guestbooks</a>';
			print '<ul class="dropdown-menu">';
		} 
		
		print $menu;
		if ($PARAMS['render_within_submenu'] == 1) {
			print "</ul></li>";
		}

	}
?>