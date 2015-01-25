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
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/customer-orders/">Shopping Cart</a> <span class="divider">/</span></li>
  <li class="active">Manage Products</li>
  <li>&nbsp; <a href="module/shopping_cart/create-product/" style='margin-top: -2px;' class='btn btn-success btn-mini'>Create New Product</a></li>
</ul>

<h3>Manage Products</h3>
<form method="post">
	<fieldset>
	<legend>Search</legend>
	<ul class="form_display">
		<li><label for="product_name"><?=$slots[1]['name']?>:</label><input type="text" id="product_name" name="product_name" value="<?=$_POST['product_name']?>" /> <input style="margin: -10px 0px 0px 10px" type="submit" name="nocmd" id="submit_cmd" class='btn btn-primary' value="Search" /></li>
		
	</ul>
	</fieldset>
	<input type="hidden" name="cmd" value="search" />
</form>

<?php
$sql = "SELECT l.id, l.status, l.slot_1, l.slot_2, l.when_created FROM `shopping_cart_products` l WHERE l.slot_1 LIKE '%".$_POST['product_name']."%' AND l.site_id='".NUMO_SITE_ID."' AND l.status>=0 ORDER BY l.status desc,l.slot_1,l.id";
//print $sql."<br>";
$results = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;

if(mysql_num_rows($results) > 0) {
	echo '<table class="table table-striped"><tr><th style="width: 20px">&nbsp;</th><th class="highlight_label">'.$slots[1]['name'].'</th>';
	echo '<th style="width: 60px; text-align:center">'.$slots[2]['name'].'</th>';
	echo '<th class="show_stock" style="width: 200px;">Stock</th>';
	echo '<th>&nbsp</th></tr>';

	while($row = mysql_fetch_array($results)) {
		$displayStatusImageSrc = ($row['status'] == "1" ? 'product_enabled.gif' : 'product_disabled.gif');
		$displayStatusImageDesc = ($row['status'] == "1" ? 'Product is displayed in catalog' : 'Product will not appear in catalog');

		$priceDisplay = $row['slot_2'];

		if(is_numeric($priceDisplay)) {
			$priceDisplay = $slots[2]['options'] . number_format($priceDisplay, 2, '.', ',');
		}

		echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['slot_1'].'</td><td style="text-align: right;" >'.$priceDisplay.'</td>';
		echo '<td class="show_stock">';
			$sql = "SELECT sum(units) as total_units FROM `shopping_cart_product_stock` WHERE `key`='".$row['id']."' OR `key` LIKE '".$row['id']."-%'";
	  //print $sql."<br>";
	       $stockResult = $dbObj->query($sql);
	  //print mysql_error();
		   $stockRecord = mysql_fetch_array($stockResult);
		   if ($stockRecord['total_units'] > 0) {
			   $showStock = true;
		   }
		echo $stockRecord['total_units'];
		echo '</td>';
		echo '<td style="text-align: right;"><a class="btn btn-primary" href="module/'.$_GET['m'].'/edit-product/?id='.$row['id'].'">Edit</a> <a class="btn" href="module/'.$_GET['m'].'/product-stock/?id='.$row['id'].'">Stock</a> <a class="btn btn-danger" href="module/'.$_GET['m'].'/'.$_GET['i'].'/" onclick="return confirmRemove(\''.$row['id'].'\');">Remove</a></td></tr>';

		$oddEvenCounter++;
	}

	echo '</table>';
} else {
	echo '<p style="font-style: italic; font-weight: bold;">No Products Found</p>';
}
if ($showStock) {
?>
<style>
.show_stock { text-align: center !important;}
</style>
<?php	
} else {
	?>
<style>
.show_stock { display: none;}
</style>
    
    <?php
}
?>
<form method="post" name="remove_product" id="remove_product">
<input type="hidden" name="listing_id" value="" />
<input type="hidden" name="product_id" value="<?=$_POST['product_id']?>" />
<input type="hidden" name="product_name" value="<?=$_POST['product_name']?>" />
<input type="hidden" name="cmdb" value="remove" />
<input type="hidden" name="cmd" value="search" />
</form>