<?php
//get all categories


	$sql = "SELECT * FROM `shopping_cart_settings` WHERE `site_id`='".NUMO_SITE_ID."'";
	$settings = $dbObj->query($sql);
	$settings = mysql_fetch_array($settings);


	if ($settings['catalog_visibility'] == "1") {
		$sql = "SELECT pc.* FROM `shopping_cart_categories` pc, `shopping_cart_category_permissions` cp WHERE cp.category_id=pc.id AND cp.account_type_id='".$_SESSION['type_id']."' AND pc.site_id='".NUMO_SITE_ID."' ORDER BY pc.`position`";
	} else {
		$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."'ORDER BY `position`";
		
	}
	
//print $sql."<br>";
$results = $dbObj->query($sql);
print mysql_error();

$categories = array();

while($row = mysql_fetch_array($results)) {
	$categories[$row['id']] = array('label' => $row['label'],'parent_id' => $row['parent_id']);
	//print $row['id']."<br>";
}

//check to see if the function has already been declared by another instance of the component
if(!function_exists('display_shopping_cart_category_links')) {
	//recursive function that prints categories as a nested html unorderd list
	function display_shopping_cart_category_links($parent,$categories, $wrapWithUL = true) {
		global $MANAGE_NUMO_LOCATION;
		$hasChildren = false;
		global $PARAMS;
        //print "doing level";
		foreach($categories as $key => $value) {
			//print $key."<br>";
			//print $value['label']." (".$key."): ".$value['parent_id']." --> ".$parent."<br>";
			if($value['parent_id'] == $parent) {
				//print "Yup". $value['parent_id'];
				//if this is the first child print '<ul>'
				if (!$hasChildren && $wrapWithUL) {
					
					//don't print '<ul>' multiple times 
					$hasChildren = true;
					if ($PARAMS['render_within_main_menu'] != "1") { 
					print '<ul>'."\r\n";
					} 
				}

				print '<li><a href="'.$MANAGE_NUMO_LOCATION.'?module=shopping_cart&component=catalog&cid=' . $key . '">' . $value['label'] . '</a>'."\r\n";

				display_shopping_cart_category_links($key,$categories);

				//call function again to generate nested list for subcategories belonging to this category
				print '</li>'."\r\n";
			}
		}

		if ($hasChildren && $wrapWithUL) {
		  if ($PARAMS['render_within_main_menu'] != "1") {	
			print '</ul>'."\r\n";
		  }
		}
	}
}

//generate starting with parent categories (that have a 0 parent)
if ($PARAMS['wrap_style'] == "no_ul") {
	$wrapWithUL = false;
} else {
  $wrapWithUL = true;	 
}
display_shopping_cart_category_links(0,$categories, $wrapWithUL);
?>