<?php
if(isset($_SESSION['account_id'])) {
	if ($PARAMS['show_accounts_page'] == "1") {
	  $link = str_replace("[File]", "//".$numo->getRootFolder()."/manage-account.htm", NUMO_SYNTAX_ACCOUNT_LINK);
	  print str_replace("[Label]", "<i class='icon-key'></i> Manage Account", $link);
	}

	if ($_SESSION['is_admin'] > 0 && $PARAMS['show_admin_link'] == 1) {
		$link = str_replace("[File]", "//".NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH, NUMO_SYNTAX_ACCOUNT_LINK);
		print str_replace("[Label]", "<i class='icon-cogs'></i> Admin Dashboard", $link);

	}
	if ($PARAMS['exit_to_home'] == "1") {
	  $link = str_replace("[File]", "//".$numo->getRootFolder()."?cmd=exit", NUMO_SYNTAX_ACCOUNT_LINK);
	} else {
	  $link = str_replace("[File]", "?cmd=exit", NUMO_SYNTAX_ACCOUNT_LINK);
	}
	print str_replace("[Label]", "<i class='icon-unlock-alt'></i> ".NUMO_SYNTAX_ACCOUNT_LOGOUT_LABEL, $link);

} else if (file_exists("login.htm")) {
	$link = str_replace("[File]", str_replace("/numo/", "/", NUMO_FOLDER_PATH)."login.htm", NUMO_SYNTAX_ACCOUNT_LINK);
	print str_replace("[Label]", NUMO_SYNTAX_ACCOUNT_LOGIN_BUTTON_LABEL, $link);

}

?>