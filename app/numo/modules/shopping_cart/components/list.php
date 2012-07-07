<?php
//get all categories
$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."' ORDER BY `position`";
$results = $dbObj->query($sql);

$categories = array();

while($row = mysql_fetch_array($results)) {
	$categories[$row['id']] = array('label' => $row['label'],'parent_id' => $row['parent_id']);
}

//check to see if the function has already been declared by another instance of the component
if(!function_exists('display_shopping_cart_category_links')) {
	//recursive function that prints categories as a nested html unorderd list
	function display_shopping_cart_category_links($parent,$categories) {
		$hasChildren = false;

		foreach($categories as $key => $value) {
			//print $value['label']." (".$key."): ".$value['parent_id']." --> ".$parent."<br>";
			if($value['parent_id'] == $parent) {
				//if this is the first child print '<ul>'
				if (!$hasChildren) {
					//don't print '<ul>' multiple times
					$hasChildren = true;

					print '<ul>'."\r\n";
				}

				print '<li><a href="'.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog&cid=' . $key . '">' . $value['label'] . '</a>'."\r\n";

				display_shopping_cart_category_links($key,$categories);

				//call function again to generate nested list for subcategories belonging to this category
				print '</li>'."\r\n";
			}
		}

		if ($hasChildren) print '</ul>'."\r\n";
	}
}

//generate starting with parent categories (that have a 0 parent)
display_shopping_cart_category_links(0,$categories);
?>