<?php
$sql = "SELECT `slot_1` FROM `shopping_cart_products` WHERE `id`='".$_GET['pid']."' AND `site_id`='".NUMO_SITE_ID."'";
//print $sql;
$result = $dbObj->query($sql);

if($product = mysql_fetch_array($result)) {
	$mainImage = "";
	$productThumbnails = "";
	$columnCounter = 1;

	$sql = "SELECT i.`file_name`,i.`description` FROM `shopping_cart_product_images` i, `shopping_cart_products` p WHERE i.`listing_id`='".$_GET['pid']."' AND i.`listing_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."' ORDER BY i.`id` asc";
	//print $sql;
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		if($mainImage == "") {
			$mainImage = '<img class="numo_product_main_image" id="product_main_image" src="'.NUMO_FOLDER_PATH.'modules/shopping_cart/uploads/'.$row['file_name'].'" />';
		}

		if(($columnCounter % 2) == 1) {
			$productThumbnails .= "<tr>";
		}

		$productThumbnails .= '<td><img class="numo_product_thumbnail" onclick="document.getElementById(\'product_main_image\').src = \''.NUMO_FOLDER_PATH.'modules/shopping_cart/uploads/'.$row['file_name'].'\';" src="'.NUMO_FOLDER_PATH.'modules/shopping_cart/uploads/'.$row['file_name'].'" alt="'.$row['description'].'" title="'.$row['description'].'" /></td>';

		if(($columnCounter % 2) == 0) {
			$productThumbnails .= "</tr>";
		}

		$columnCounter++;
	}

	if(($columnCounter % 2) == 0) {
		$productThumbnails .= '<td class="numo_no_product_thumbnail">&nbsp;</td></tr>';
	}
?>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/shopping_cart/components/styles/images.css" />

</head>
<body>
	<h1><?=$product['slot_1']?></h1>
	<table border="0" class="numo_product_image_table">
	<tr>
		<td><?=$mainImage?></td>
		<?php
		//only display alternative views option if there is more than one image for the product
		if($columnCounter > 2) {
		?>
		<td>
			<h2><?=NUMO_SYNTAX_SHOPPING_CART_ALTERNATIVE_IMAGES_LABEL?></h2>
			<p><?=NUMO_SYNTAX_SHOPPING_CART_ALTERNATIVE_IMAGES_CLICK_MESSAGE?></p>
			<table border="0" class="numo_product_image_thumnails_table"><?=$productThumbnails?></table>
		</td>
		<?php
		}
		?>
	</tr>
	</table>
</body>
</html>
<?php
//no product found matching PID
} else {
	exit();
}
?>