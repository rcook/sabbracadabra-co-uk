<?php
if(isset($_SESSION['account_id'])) {
	$link = str_replace("[File]", "?cmd=exit", NUMO_SYNTAX_ACCOUNT_LINK);
	print str_replace("[Label]", NUMO_SYNTAX_ACCOUNT_LOGOUT_LABEL, $link);
  if ($_SESSION['is_admin'] == "1" && $PARAMS['show_admin'] == "1") {
	$link = str_replace("[File]", NUMO_FOLDER_PATH, NUMO_SYNTAX_ACCOUNT_LINK);
	print str_replace("[Label]", "NUMO Admin", $link);

  }
} else if (file_exists("login.htm")) {
	$link = str_replace("[File]", str_replace("/numo/", "/", NUMO_FOLDER_PATH)."login.htm", NUMO_SYNTAX_ACCOUNT_LINK);
	print str_replace("[Label]", NUMO_SYNTAX_ACCOUNT_LOGIN_BUTTON_LABEL, $link);

}

?>