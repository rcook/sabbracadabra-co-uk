<style>
.numo_shopping_cart_product_order_contents {border: 1px solid #999; border-collapse: collapse; font-size: 12px; width: 552px;}
.numo_shopping_cart_product_order_contents td,.numo_shopping_cart_product_order_contents th {border: 1px solid #999; vertical-align: top; padding: 5px; text-align: left;}
.numo_shopping_cart_product_order_qty_column {text-align: center; width: 30px;}
.numo_shopping_cart_product_order_cost_column {text-align: right;  width: 100px;}
.numo_shopping_cart_product_order_product_item_name {text-decoration: underline; font-size: 12px;}
</style>
<fieldset>
<legend>Shopping Cart Purchases</legend>
<?php
$sql = "SELECT o.`id`, o.`shipped`, o.`mc_currency`, o.`mc_gross`, o.`payment_status`, DATE_FORMAT(o.`payment_date`,'%M %e, %Y') as 'payment_date' FROM `shopping_cart_orders` o, `accounts` a WHERE o.`processed`=1 AND o.`account_id`=a.`id` AND a.`id`=".$_GET['id']." AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY payment_date desc";
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
		<tr><td><?=$row['payment_date']?></td><td><a href="<?=NUMO_FOLDER_PATH?>module/shopping_cart/review-order/?id=<?=$row['id']?>"><?=$orderStatus?></a></th><td class="numo_shopping_cart_product_order_cost_column"><?=$row['mc_gross']?></td></tr>
	<?php
	}
?>
</table>
</fieldset>