<html>
<head>
	<title>Order Packing Slip</title>
	<style>
	body {padding: 30px 20px 0px 20px; margin: 0px; font-family: Arial, sans-serif;}
	div{margin: 0px; padding: 0px;}
	h1 {text-align: left; font-size: 25px; margin: 10px 0px; padding: 0px; color: #000;}
	h2 {text-align: right; font-size: 40px;margin: 0px; padding: 0px; color: #CCC;}
	p {margin: 0px; padding: 5px 0px; color: #333; font-size: 13px;}
	th {text-align: left;}
	#company_address {padding: 5px 0px 0px 10px;}
	#order_details {padding: 5px 0px 0px 80px;}
	#shipping_details {margin: 10px 0px 0px 80px;}
	#shipping_details h3 {margin: 0px; padding: 5px; background: #333; color: #fff;}
	#shipping_details p {margin: 0px 0px 0px 5px; padding: 5px 0px; color: #333;}
	.shopping_cart_product_order_contents, .transaction_summary {border: 1px solid #999; border-collapse: collapse; font-size: 13px;}
	.shopping_cart_product_order_contents td,.shopping_cart_product_order_contents th {border: 1px solid #999; vertical-align: top; padding: 5px;}
	.qty_column {text-align: center; width: 30px;}
	.cost_column {text-align: right;  width: 100px;}
	.product_item_name {text-decoration: underline; font-size: 12px; color: #000;}
	</style>
</head>
<body>
<?php


$sql = "SELECT *, DATE_FORMAT(`payment_date`,'%b %e, %Y') as 'payment_date' FROM `shopping_cart_orders` WHERE `id`='".$_GET['id']."' AND `site_id`='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<table border="0" width="700px">
<tr>
<td width="350px" valign="top">
	<?php
	$sql = "SELECT * FROM `shopping_cart_settings` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$settings = $dbObj->query($sql);

	if($settings = mysql_fetch_array($settings)) {
	?>
	<h1><?=$settings['company_name']?></h1>
	<div id="company_address">
	<p><?=nl2br($settings['packing_slip_address'])?></p>
	</div>
	<?php
	}
	?>
</td>
<td valign="top">
	<h2><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_LABEL?></h2>
	<div id="order_details">
	<p><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_DATE_LABEL?>: <?=date('F j, Y')?></p>
	<p><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_ORDER_LABEL?>: <?=$_GET['id']?></p>
	</div>

	<div id="shipping_details">
	<h3><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_SHIP_TO_LABEL?></h3>
	<p><?=$row['first_name']." ".$row['last_name']?><br><?=$row['address_street']?><br><?=$row['address_city']?>, <?=$row['address_state']?> <?=$row['address_zip']?><br><?=$row['address_country']?></p>
	</div>
</td>
</tr>
<tr>
<td colspan="2">
	<table class="shopping_cart_product_order_contents" width="100%">
	<tr><th class="qty_column"><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_QUANTITY_LABEL?></th><th><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_ITEM_LABEL?></th><th class="cost_column"><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_PRICE_LABEL?></th></tr>

	<?php
	$purchaseTotal = 0;

	$sql = "SELECT i.`id`, i.`quantity`, i.`item_cost`, p.`slot_1` FROM `shopping_cart_order_items` i, `shopping_cart_products` p WHERE i.`order_id`=".$_GET['id']." AND i.`product_id`=p.`id` AND p.`site_id`=".NUMO_SITE_ID." ORDER BY i.`id` asc";
	//print $sql;
	$results = $dbObj->query($sql);

	while($item_row = mysql_fetch_array($results)) {
		$itemAttributes = "";

		$sql = "SELECT pa.`label`, pa.`type`, oa.`value`, oa.`attribute_id` FROM `shopping_cart_order_item_attributes` oa, `shopping_cart_optional_product_attributes` pa WHERE oa.`order_item_id`='".$item_row['id']."' AND oa.`attribute_id`=pa.`id`";
		//print $sql."<br>";
		$attributes = $dbObj->query($sql);

		while($attribute = mysql_fetch_array($attributes)) {
			if($attribute['type'] == "text") {
				$itemAttributes .= $attribute['label'].": ".$attribute['value']."<br>";
			} else {
				$sql = "SELECT `label` FROM `shopping_cart_optional_product_attribute_options` WHERE `attribute_id`='".$attribute['attribute_id']."' AND `id`='".$attribute['value']."'";
				//print $sql."<br>";
				$result = $dbObj->query($sql);

				if($option = mysql_fetch_array($result)) {
					$itemAttributes .= $attribute['label'].": ".$option['label']."<br>";
				}

				mysql_free_result($result);
			}
		}

		$itemCost = $item_row['quantity'] * $item_row['item_cost'];
		$purchaseTotal += $itemCost;
		?>
		<tr><td class="qty_column"><?=$item_row['quantity']?></td><td><p class="product_item_name"><?=$item_row['slot_1']."</p>".$itemAttributes?></td><td class="cost_column"><?=number_format($itemCost, 2, '.', ',')?></td></tr>
		<?php
	}

if ($settings['tax_display_preference'] == 2) {
$purchaseTotal += $row['mc_shipping'];

} else {
$purchaseTotal += $row['mc_shipping'] + $row['tax'];
}
	?>
	<tr><td colspan="2" style="text-align: right;"><?=NUMO_SYNTAX_SHOPPING_CART_SHIPPING_LABEL?></td><td class="cost_column"><?=number_format($row['mc_shipping'], 2, '.', ',')?></td></tr>
	<?php
	if($row['tax'] > 0) {
	?>
	<tr><td colspan="2" style="text-align: right;"><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_TAX_LABEL?> <?php if ($settings['tax_display_preference'] == 2) { print "Included"; } ?></td><td class="cost_column"><?=number_format($row['tax'], 2, '.', ',')?></td></tr>
	<?php
	}
	?>
	<tr><td colspan="2" style="text-align: right; background: #ededed; font-weight: bold;"><?=NUMO_SYNTAX_SHOPPING_CART_TOTAL_LABEL?></td><td class="cost_column"><?=number_format($purchaseTotal, 2, '.', ',')?></td></tr>
	</table>
</td>
</tr>
</table>
<?php
}
?>
</body>
</html>