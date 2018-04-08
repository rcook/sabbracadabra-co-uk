<?php
	$link = str_replace("[File]", str_replace("/numo/", "/", NUMO_FOLDER_PATH)."manage.numo?module=shopping_cart&component=purchases", NUMO_SYNTAX_ACCOUNT_LINK);
	print str_replace("[Label]", "<i class='icon-credit-card'></i> My Purchases", $link);
?>