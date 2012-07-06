<?php
/**************************/
/*    PRODUCT SELECTED    */
/**************************/
if(is_numeric($_GET['pid'])) {
	$sql = "SELECT c.id, c.label, c.`parent_id` FROM `shopping_cart_product_categories` pc, `shopping_cart_categories` c WHERE pc.`product_id`='".$_GET['pid']."' AND c.`site_id`='".NUMO_SITE_ID."' AND c.id=pc.`category_id` ORDER BY pc.`id` desc";
	$results = $dbObj->query($sql);

	$lastParent = "";
	$breadcrumbStr = "";

	while($row = mysql_fetch_array($results)) {
		if($lastParent == $row['parent_id']) {
			continue;
		}
		if (strstr($_SERVER['REQUEST_URI'], "manage.numo")) {
			$breadcrumbStr = htmlentities(' �').' <a href="'.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog&cid='.$row['id'].'">'.$row['label'].'</a>'.$breadcrumbStr;
		} else {
			$breadcrumbStr = htmlentities(' �').' <a href="?cid='.$row['id'].'">'.$row['label'].'</a>'.$breadcrumbStr;
		}


		if($row['parent_id'] == 0) {
			break;
		}

		$lastParent = $row['parent_id'];
	}
	if (strstr($_SERVER['REQUEST_URI'], "manage.numo")) {
		print '<a href="'.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog">'.NUMO_SYNTAX_SHOPPING_CART_BREADCRUMB_HOME_LABEL.'</a>'.$breadcrumbStr;
	} else {
		print '<a href="?">'.NUMO_SYNTAX_SHOPPING_CART_BREADCRUMB_HOME_LABEL.'</a>'.$breadcrumbStr;
	}
	//print '<a href="'.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog">'.NUMO_SYNTAX_SHOPPING_CART_BREADCRUMB_HOME_LABEL.'</a>'.$breadcrumbStr;

/**************************/
/*   CATEGORY SELECTED    */
/**************************/
} else if((isset($_GET['cid']) && is_numeric($_GET['cid'])) || isset($PARAMS['cid'])) {
	$value = $PARAMS['cid'];
	$breadcrumbStr = "";

	if(isset($_GET['cid']) && is_numeric($_GET['cid'])) {
		$value = $_GET['cid'];
	}

	//get all categories for site
	$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."' ORDER BY `position`";
	$results = $dbObj->query($sql);

	$categories = array();

	while($row = mysql_fetch_array($results)) {
		$categories[$row['id']] = array('label' => $row['label'],'parent_id' => $row['parent_id']);
	}

	//loop thru the parents of the category and add them to the list of categories
	while(isset($categories[$value])) {
	
		if (strstr($_SERVER['REQUEST_URI'], "manage.numo")) {
			$breadcrumbStr = htmlentities(' �').' <a href="'.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog&cid='.$value.'">'.$categories[$value]['label'].'</a>'.$breadcrumbStr;
		} else {
			$breadcrumbStr = htmlentities(' �').' <a href="?cid='.$value.'">'.$categories[$value]['label'].'</a>'.$breadcrumbStr;
		}

		
		$value = $categories[$value]['parent_id'];
	}
	
	if (strstr($_SERVER['REQUEST_URI'], "manage.numo")) {
		print '<a href="'.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog">'.NUMO_SYNTAX_SHOPPING_CART_BREADCRUMB_HOME_LABEL.'</a>'.$breadcrumbStr;
	} else {
		print '<a href="?">'.NUMO_SYNTAX_SHOPPING_CART_BREADCRUMB_HOME_LABEL.'</a>'.$breadcrumbStr;
	}
}
?>