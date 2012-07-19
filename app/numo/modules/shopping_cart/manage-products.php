<?php
//remove listing
if($_POST['cmdb'] == "remove") {
	$productObj = new Product($_POST['listing_id']);
	$productObj->remove();
}

$sql = "SELECT name, slot, input_options FROM shopping_cart_fields WHERE site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

$slots = array();

while($row = mysql_fetch_array($results)) {
	$slots[$row['slot']]['name'] = $row['name'];

	if($row['slot'] == "2") {
		if($row['input_options'] == "GBP") {
			$slots[$row['slot']]['options'] = "&#163;";
		} else if($row['input_options'] == "EUR") {
			$slots[$row['slot']]['options'] = "&#128;";
		} else {
			// $row['input_options'] == "AUD" || $row['input_options'] == "CAD" || $row['input_options'] == "NZD" || $row['input_options'] == "USD"
			$slots[$row['slot']]['options'] = "$";
		}
	} else if($row['slot'] == "4") {
		$slots[$row['slot']]['options'] = trim($row['input_options']);
	}
}

mysql_free_result($results);
?>
<script>
function confirmRemove(listingId) {
	if(confirm("Are you absolutely sure you wish to remove this product?")) {
		document.forms['remove_product'].listing_id.value = listingId;
		document.forms['remove_product'].submit();
	}

	return false;
}
</script>
<style>
.table_data_layout tr td.spaced_col {padding: 0px 3px;}
</style>
<h2>Manage Products</h2>
<form method="post">
	<fieldset>
	<legend>Search</legend>
	<ul class="form_display">
		<li><label for="product_name"><?=$slots[1]['name']?>:</label><input type="text" id="product_name" name="product_name" value="<?=$_POST['product_name']?>" /></li>
		<li><label for="submit_cmd"></label><input type="submit" name="nocmd" id="submit_cmd" value="Search" /></li>
	</ul>
	</fieldset>
	<input type="hidden" name="cmd" value="search" />
</form>

<?php
$sql = "SELECT l.id, l.status, l.slot_1, l.slot_2, l.when_created FROM `shopping_cart_products` l WHERE l.slot_1 LIKE '%".$_POST['product_name']."%' AND l.site_id='".NUMO_SITE_ID."' ORDER BY l.status desc,l.slot_1,l.id";
//print $sql."<br>";
$results = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;

if(mysql_num_rows($results) > 0) {
	echo '<table class="table_data_layout"><tr><th>&nbsp;</th><th class="highlight_label">'.$slots[1]['name'].'</th><th>'.$slots[2]['name'].'</th><th>&nbsp</th></tr>';

	while($row = mysql_fetch_array($results)) {
		$displayStatusImageSrc = ($row['status'] == "1" ? 'product_enabled.gif' : 'product_disabled.gif');
		$displayStatusImageDesc = ($row['status'] == "1" ? 'Product is displayed in catalog' : 'Product will not appear in catalog');

		$priceDisplay = $row['slot_2'];

		if(is_numeric($priceDisplay)) {
			$priceDisplay = $slots[2]['options'] . number_format($priceDisplay, 2, '.', ',');
		}

		echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['slot_1'].'</td><td>'.$priceDisplay.'</td><td><a href="module/'.$_GET['m'].'/edit-product/?id='.$row['id'].'">Edit</a> <a href="module/'.$_GET['m'].'/product-stock/?id='.$row['id'].'">Stock</a> <a href="module/'.$_GET['m'].'/'.$_GET['i'].'/" onclick="return confirmRemove(\''.$row['id'].'\');">Remove</a></td></tr>';

		$oddEvenCounter++;
	}

	echo '</table>';
} else {
	echo '<p style="font-style: italic; font-weight: bold;">No Products Found</p>';
}
?>
<a href="module/<?=$_GET['m']?>/create-product/"><img src="modules/shopping_cart/images/create_button.jpg" alt="Create New Product" title="Create New Product" border="0" /></a>
<form method="post" name="remove_product" id="remove_product">
<input type="hidden" name="listing_id" value="" />
<input type="hidden" name="product_id" value="<?=$_POST['product_id']?>" />
<input type="hidden" name="product_name" value="<?=$_POST['product_name']?>" />
<input type="hidden" name="cmdb" value="remove" />
<input type="hidden" name="cmd" value="search" />
</form>