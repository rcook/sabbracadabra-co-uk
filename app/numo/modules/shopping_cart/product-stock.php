<?php

if ($_POST['cmd'] == "enable") {
  $sql = "INSERT INTO `shopping_cart_product_stock` (`key`,`units`,`site_id`) VALUES ('".$_POST['id']."','0',".NUMO_SITE_ID.")";
  $dbObj->query($sql);

} else if ($_POST['nocmd'] == "Disable Stock on this Item") {
	//print "x";
	foreach($_POST as $key => $value) {
		if(substr($key,0,7) == "stock__") {
			if(!is_numeric($value)) {
				$value = 0;
			}
  $sql = "DELETE FROM `shopping_cart_product_stock` WHERE site_id='".NUMO_SITE_ID."' AND `key`='".substr($key,7)."'";
    $dbObj->query($sql);

 // print $sql;
/*
			$sql = "SELECT `id` FROM `shopping_cart_product_stock` WHERE `key`='".substr($key,7)."' AND `site_id`=".NUMO_SITE_ID;
			//print $sql."<br>";
			$results = $dbObj->query($sql);

			if(mysql_num_rows($results) > 0) {
				$sql = "UPDATE `shopping_cart_product_stock` SET `units`=".$value." WHERE `key`='".substr($key,7)."' AND `site_id`=".NUMO_SITE_ID;
				//print $sql."<br>";
				$dbObj->query($sql);
			} else {
				$sql = "INSERT INTO `shopping_cart_product_stock` (`key`,`units`,`site_id`) VALUES ('".substr($key,7)."',".$value.",".NUMO_SITE_ID.")";
				//print $sql."<br>";
				$dbObj->query($sql);
			}
			*/
		}
	}
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-products/');


} else if($_POST['cmd'] == "update") {
	foreach($_POST as $key => $value) {
		if(substr($key,0,7) == "stock__") {
			if(!is_numeric($value)) {
				$value = 0;
			}

			$sql = "SELECT `id` FROM `shopping_cart_product_stock` WHERE `key`='".substr($key,7)."' AND `site_id`=".NUMO_SITE_ID;
			//print $sql."<br>";
			$results = $dbObj->query($sql);

			if(mysql_num_rows($results) > 0) {
				$sql = "UPDATE `shopping_cart_product_stock` SET `units`=".$value." WHERE `key`='".substr($key,7)."' AND `site_id`=".NUMO_SITE_ID;
				//print $sql."<br>";
				$dbObj->query($sql);
			} else {
				$sql = "INSERT INTO `shopping_cart_product_stock` (`key`,`units`,`site_id`) VALUES ('".substr($key,7)."',".$value.",".NUMO_SITE_ID.")";
				//print $sql."<br>";
				$dbObj->query($sql);
			}
		}
	}

	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-products/');
}

?>
<style>
.stock_amount {width: 50px;}
.inner_stock_options {margin-left: 30px;}
.main_stock_heading {font-weight: bold; text-decoration: underline;}
</style>
<h2>Manage Product Stock</h2>
<form method="post">
<?php
$attributes = array();

//get all SELECTABLE product attributes
$sql = "SELECT a.`id`, a.`label` FROM `shopping_cart_optional_product_attributes` a, `shopping_cart_products` p WHERE a.`type`='dropdown list' AND a.`product_id`='".$_GET['id']."' AND a.`product_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."' ORDER BY a.`position` asc";
//print $sql;
$results = $dbObj->query($sql);

$counter = 0;

if(mysql_num_rows($results) > 0) {
	while($row = mysql_fetch_array($results)) {
		$attributes[$counter]['label'] = $row['label'];
		$attributes[$counter]['id'] = $row['id'];
		$attributeOptions = array();

		//get all options for product attribute
		$sql = "SELECT `id`, `label` FROM `shopping_cart_optional_product_attribute_options` WHERE `attribute_id`='".$row['id']."' AND `status`=1 ORDER BY `id`";
		$options = $dbObj->query($sql);

		while($option = mysql_fetch_array($options)) {
			$attributeOptions[$option['id']] = $option['label'];
		}

		$attributes[$counter]['options'] = $attributeOptions;

		$counter++;
	}

	print "<table>".get_optional_stock_options($attributes,$_GET['id'],0,"")."</table>";
?>
<input type="hidden" name="cmd" value="update" />
<input type="submit" name="nocmd" value="Update" />
<input type="submit" name="nocmd" value="Disable Stock on this Item" />
<?php	
} else {
	$sql = "SELECT `units` FROM `shopping_cart_product_stock` WHERE `key`='".$_GET['id']."'";
	//print $sql."<br>";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		print '<table><tr><td><label for="stock">Stock:</label></td><td><input class="stock_amount" type="text" name="stock__'.$_GET['id'].'" id="stock" value="'.$row['units'].'" />&nbsp;units</td></tr></table>';
?>
<input type="hidden" name="cmd" value="update" />
<input type="submit" name="nocmd" value="Update" />
<input type="submit" name="nocmd" value="Disable Stock on this Item" />

<?php
	//no stock record found
	} else {
		print '<table><tr><td><label for="stock">Stock:</label></td><td>';
		print '<input type="submit" name="nocmd" value="Enable" />';
		//print '<input class="stock_amount" type="text" name="stock__'.$_GET['id'].'" id="stock" value="0" />&nbsp;units';
		print '</td></tr></table>';
?>
<input type="hidden" name="cmd" value="enable" />
<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
<?php		
		
	}
}
?>
</form>
<?php
function get_optional_stock_options($attributes,$key, $pos, $str) {
	foreach($attributes[$pos]['options'] as $id => $option) {
		//no more options, show stock input
		if(($pos+1) == count($attributes)) {
			global $dbObj;

			$sql = "SELECT `units` FROM `shopping_cart_product_stock` WHERE `key`='".$key."-".$id."'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				$str .= '<tr><td><label for="stock__'.$key."-".$id.'">'.$option.':</label></td><td><input class="stock_amount" type="text" name="stock__'.$key."-".$id.'" id="stock__'.$key."-".$id.'" value="'.$row['units'].'" />&nbsp;units</td></tr>';
			} else {
				$str .= '<tr><td><label for="stock__'.$key."-".$id.'">'.$option.':</label></td><td><input class="stock_amount" type="text" name="stock__'.$key."-".$id.'" id="stock__'.$key."-".$id.'" value="0" />&nbsp;units</td></tr>';
			}

			mysql_free_result($result);
		//get the next level of options
		} else if($pos < count($attributes)) {
			if($pos == 0) {
				$str .= '<tr><td><label class="main_stock_heading" for="'.$key."-".$id.'">'.$option.'</label><table class="inner_stock_options">'.get_optional_stock_options($attributes,$key."-".$id,($pos+1)).'</table></td></tr>';

			} else {
				$str .= '<tr><td><label for="'.$key."-".$id.'">'.$option.'</label><table class="inner_stock_options">'.get_optional_stock_options($attributes,$key."-".$id,($pos+1)).'</table></td></tr>';
			}
		}
	}

	return $str;
}
?>