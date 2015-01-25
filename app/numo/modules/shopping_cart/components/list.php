<?php
//get all categories


	$sql = "SELECT * FROM `shopping_cart_settings` WHERE `site_id`='".NUMO_SITE_ID."'";
	$settings = $dbObj->query($sql);
	$settings = mysql_fetch_array($settings);

	//var_dump($PARAMS);
	if ($settings['catalog_visibility'] == "1") {
		$sql = "SELECT pc.* FROM `shopping_cart_categories` pc, `shopping_cart_category_permissions` cp WHERE cp.category_id=pc.id AND cp.account_type_id='".$_SESSION['type_id']."' AND pc.site_id='".NUMO_SITE_ID."' ORDER BY pc.`position`";
	} else {
		$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."'ORDER BY `position`";
		
	}
	
//print $sql."<br>";
$results = $dbObj->query($sql);
//print mysql_error();

$categories = array();

while($row = mysql_fetch_array($results)) {
	$categories["{$row['id']}"] = array('label' => $row['label'],'parent_id' => $row['parent_id']);
	//print $row['id']."<br>";
//	print "<pre>";
	//var_dump($row);
	//print "</pre>";
}
//var_dump($categories);
//check to see if the function has already been declared by another instance of the component
if(!function_exists('display_shopping_cart_category_links')) {
	
	//recursive function that prints categories as a nested html unorderd list
	function display_shopping_cart_category_links($parent,$categories, $wrapWithUL = true) {
	//	print "yah";
		global $MANAGE_NUMO_LOCATION;
		$hasChildren = false;
		global $PARAMS;
		//var_dump($PARAMS);
        //print "doing level";
		//print sizeof($categories);
		foreach($categories as $key => $value) {
			//print $key."=".$value."<br>";
			//var_dump($value);
			//print $value['label']." (".$key."): ".$value['parent_id']." --> ".$parent."<br>";
			
			if($value['parent_id'] == $parent) {
			//	print "in";
				//print "Yup". $value['parent_id'];
				//if this is the first child print '<ul>'
				if (!$hasChildren && $wrapWithUL) {
				//	print "ok";
					
					//don't print '<ul>' multiple times 
					$hasChildren = true;
				//	print "ok2";
					if ($PARAMS['render_within_main_menu'] != "1" || ($wrapWithUL && $parent != 0 )) { 
 				//	print "ok3";
					  print '<ul>'."\r\n";
					} 
				//	print "ok4";
				}
				//print "a";
				print '<li><a href="'.$MANAGE_NUMO_LOCATION.'?module=shopping_cart&component=catalog&cid=' . $key . '">' . $value['label'] . '</a>'."\r\n";
				//print "start";
				
				display_shopping_cart_category_links($key,$categories);
				//print "end";
				//call function again to generate nested list for subcategories belonging to this category
				print '</li>'."\r\n";
			} else {
				//print "not in";
			}
		}

		if ($hasChildren && $wrapWithUL) {
		  if ($PARAMS['render_within_main_menu'] != "1" || ($wrapWithUL && $parent != 0 )) {	
			print '</ul>'."\r\n";
		  }
		}
	}
}
// these next three lines are need for the online demo
$PARAMS_local = $PARAMS;
global $PARAMS;
$PARAMS = $PARAMS_local;


//generate starting with parent categories (that have a 0 parent)
if ($PARAMS['wrap_style'] == "no_ul") {
	$wrapWithUL = false;
} else {
  $wrapWithUL = true;	 
}
//print sizeof($categories);
display_shopping_cart_category_links(0,$categories, $wrapWithUL);
?>