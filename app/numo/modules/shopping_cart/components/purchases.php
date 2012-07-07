<?php
if(!isset($_SESSION['account_id'])) {
	print "[NUMO.ACCOUNTS: LOGIN BOX]";
	return;
}


foreach($_GET as $key => $value) {
	if($key != "where" && $key != "page" && $key != "product_id" && $key != "numo_cmd") {
		$getStringValues .= "&".$key."=".$value;
	}
}
?>
<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/shopping_cart/components/styles/purchases.css" />
<?php
/******************************/
/*    DISPLAY ORDER DETAILS   */
/******************************/
if(isset($_GET['oid'])) {
	$sql = "SELECT o.`id`, o.`tax`, o.`mc_shipping` FROM `shopping_cart_orders` o, `accounts` a WHERE o.`account_id`=a.`id` AND a.`id`='".$_SESSION['account_id']."' AND o.`id`='".$_GET['oid']."' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY payment_date desc";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	if($row = mysql_fetch_array($results)) {
		?>
		<table border="0" width="700px">
		<tr>
		<td colspan="2">
			<table class="numo_shopping_cart_product_order_contents" width="100%">
			<tr><th class="numo_shopping_cart_product_order_qty_column"><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_QUANTITY_LABEL?></th><th><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_ITEM_LABEL?></th><th class="numo_shopping_cart_product_order_cost_column"><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_PRICE_LABEL?></th></tr>

			<?php
			$purchaseTotal = 0;

			$sql = "SELECT i.`id`, i.`quantity`, i.`item_cost`, p.`slot_1` FROM `shopping_cart_order_items` i, `shopping_cart_orders` o, `shopping_cart_products` p WHERE o.`id`=i.`order_id` AND i.`order_id`='".$row['id']."' AND o.`account_id`='".$_SESSION['account_id']."' AND i.`product_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."' ORDER BY i.`id` asc";
			//print $sql;
			$item_results = $dbObj->query($sql);

			while($item_row = mysql_fetch_array($item_results)) {
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
				<tr><td class="numo_shopping_cart_product_order_qty_column"><?=$item_row['quantity']?></td><td><p class="numo_shopping_cart_product_order_product_item_name"><?=$item_row['slot_1']."</p>".$itemAttributes?></td><td class="numo_shopping_cart_product_order_cost_column"><?=number_format($itemCost, 2, '.', ',')?></td></tr>
				<?php
			}

			$purchaseTotal += $row['mc_shipping'] + $row['tax'];

			if($row['mc_shipping'] > 0) {
			?>
			<tr><td colspan="2" style="text-align: right;"><?=NUMO_SYNTAX_SHOPPING_CART_SHIPPING_LABEL?></td><td class="numo_shopping_cart_product_order_cost_column"><?=number_format($row['mc_shipping'], 2, '.', ',')?></td></tr>
			<?php
			}

			if($row['tax'] > 0) {
			?>
			<tr><td colspan="2" style="text-align: right;"><?=NUMO_SYNTAX_SHOPPING_CART_PACKING_SLIP_TAX_LABEL?></td><td class="numo_shopping_cart_product_order_cost_column"><?=number_format($row['tax'], 2, '.', ',')?></td></tr>
			<?php
			}
			?>
			<tr><td colspan="2" style="text-align: right; background: #ededed; font-weight: bold;"><?=NUMO_SYNTAX_SHOPPING_CART_TOTAL_LABEL?></td><td class="numo_shopping_cart_product_order_cost_column"><?=number_format($purchaseTotal, 2, '.', ',')?></td></tr>
			</table>
		</td>
		</tr>
		</table>
		<?php
	}
/******************************/
/*    DISPLAY PURCHASE LIST   */
/******************************/
} else {
	$sql = "SELECT o.`id`, o.`shipped`, o.`mc_currency`, o.`mc_gross`, o.`payment_status`, DATE_FORMAT(o.`payment_date`,'%M %e, %Y') as 'payment_date' FROM `shopping_cart_orders` o, `accounts` a WHERE o.`processed`=1 AND o.`account_id`=a.`id` AND a.`id`='".$_SESSION['account_id']."' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY payment_date desc";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

?>
	<table class="numo_shopping_cart_product_order_contents" width="100%">
	<tr><th><?=NUMO_SYNTAX_SHOPPING_CART_ORDER_DATE_LABEL?></th><th><?=NUMO_SYNTAX_SHOPPING_CART_ORDER_STATUS_LABEL?></th><th class="numo_shopping_cart_product_order_cost_column"><?=NUMO_SYNTAX_SHOPPING_CART_ORDER_AMOUNT_LABEL?></th></tr>
<?php
	while($row = mysql_fetch_array($results)) {
		$orderStatus = NUMO_SYNTAX_SHOPPING_CART_ORDER_NOT_COMPLETE_LABEL;

		if($row['payment_status'] == "Completed" || $row['payment_status'] == "Processed" || $row['payment_status'] == "Canceled_Reversal") {
			//item has been sent to customer
			if($row['shipped'] == "1") {
				$orderStatus = NUMO_SYNTAX_SHOPPING_CART_ORDER_HAS_SHIPPED_LABEL;
			//item has not been set to customer
			} else {
				$orderStatus = NUMO_SYNTAX_SHOPPING_CART_ORDER_PENDING_SHIPMENT_LABEL;
			}
		} else if($row['payment_status'] == "Pending") {
			$orderStatus = NUMO_SYNTAX_SHOPPING_CART_ORDER_PENDING_LABEL;
		} else if($row['payment_status'] == "Refunded" || $row['payment_status'] == "Reversed") {
			$orderStatus = NUMO_SYNTAX_SHOPPING_CART_ORDER_REFUNDED_LABEL;
		}

	?>
		<tr><td><?=$row['payment_date']?></td><td><a href="?oid=<?=$row['id'].$getStringValues?>"><?=$orderStatus?></a></th><td class="numo_shopping_cart_product_order_cost_column"><?=$row['mc_gross']?></td></tr>
	<?php
	}
?>
	</table>
<?php
}
?>