<?php
if($_POST['cmd'] == "Set Order Shipped") {
	$sql = "UPDATE `shopping_cart_orders` SET `shipped`=1 WHERE `id`='".$_GET['id']."'";
	//print $sql."<br>";
	$dbObj->query($sql);
} else if($_POST['cmd'] == "Order Shipped") {
	$sql = "UPDATE `shopping_cart_orders` SET `shipped`=0 WHERE `id`='".$_GET['id']."'";
	//print $sql."<br>";
	$dbObj->query($sql);
} else if($_POST['return_cmd'] == "Back") {
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/customer-orders/');
} else if ($_POST['cmd'] == "Complete") {
  $sql = "UPDATE `shopping_cart_orders` SET payment_status='Completed', txn_id='{$_POST['txn_id']}', contact_phone='{$_POST['contact_phone']}', mc_shipping='{$_POST['mc_shipping']}', processed=1, payment_date='{$_POST['payment_date']} {$_POST['payment_time']}', mc_gross='{$_POST['mc_gross']}', mc_fee='{$_POST['mc_fee']}', mc_currency='{$_POST['mc_currency']}', first_name='{$_POST['first_name']}', last_name='{$_POST['last_name']}', address_street='{$_POST['address_street']}', address_city='{$_POST['address_city']}', address_state='{$_POST['address_state']}', address_zip='{$_POST['address_zip']}', address_country='{$_POST['address_country']}' WHERE id='{$_GET['id']}'";
  $dbObj->query($sql);
 // print mysql_error();
 // print $sql;
}

?>
<style>
.shopping_cart_product_order_contents, .transaction_summary {border: 1px solid #999; border-collapse: collapse; font-size: 12px; width: 552px;}
.shopping_cart_product_order_contents td,.shopping_cart_product_order_contents th, .transaction_summary td, .transaction_summary th {border: 1px solid #999; vertical-align: top; padding: 5px;}
.transaction_summary th {background: #cde;}
.qty_column {text-align: center; width: 30px;}
.cost_column {text-align: right;  width: 100px;}
.product_item_name {text-decoration: underline; font-size: 12px;}
.transaction_heading {background: #ededed; color: #000;}
.payment_shipping_information {width: 550px; border: 1px solid #999;}
.payment_shipping_information h3 {margin: 0px; background: #ededed; color: #000; text-decoration: none; border-bottom: 1px solid #999;}
.payment_shipping_information h4 {}
.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 50px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 30px; margin: 10px 0px 10px 210px;}
input.order_bttn_shipped {background: #2A61BD; color: #779FE1; border: 1px solid #1A51AD; height: 30px; margin: 10px 0px 10px 210px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
html {padding-bottom: 50px;}
</style>
<h2>Review Order</h2>
<?php
$sql = "SELECT name, slot, input_options FROM shopping_cart_fields WHERE site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

$slots = array();

while($row = mysql_fetch_array($results)) {
	$slots[$row['slot']]['name'] = $row['name'];

	if($row['slot'] == "2") {
		$slots[$row['slot']]['code'] = $row['input_options'];

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

$sql = "SELECT *, DATE_FORMAT(`payment_date`,'%b %e, %Y') as 'payment_date', DATE_FORMAT(`payment_date`,'%l:%i %p') as 'payment_time' FROM `shopping_cart_orders` WHERE `id`='".$_GET['id']."' AND `site_id`='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;

if($row = mysql_fetch_array($result)) {
?>
<form method="post">
<table border="1" class="transaction_summary">
<?php if ($row['processed'] > 0) { ?>
<tr><td colspan="6" class="transaction_heading">Transaction Information</td></tr>

<tr><th>Transaction ID</th><td colspan="5"><?=$row['txn_id']?></td></tr>
<?php } else if ($_POST['cmd'] == "Mark Order as Processed") { ?>
<tr><td colspan="6" class="transaction_heading">Transaction Information</td></tr>

<tr><th>Transaction ID</th><td colspan="5"><input type='text' name='txn_id' /></td></tr>

<?php } else { ?>
<tr><td colspan="6" class="transaction_heading">Order Information</td></tr>
<?php } ?>
<?php
$sql = "SELECT a.`slot_4` as 'name' FROM `accounts` a, `types` t WHERE a.`id`='".$row['account_id']."' AND a.type_id=t.`id` AND t.`site_id`='".NUMO_SITE_ID."'";
//print $sql."<br>";
$purchaser_result = $dbObj->query($sql);

if($purchaser = mysql_fetch_array($purchaser_result)) {
?>
<tr><th>Purchaser</th><td colspan="5"><?=$purchaser['name']?>, <a href="module/accounts/account-edit/?id=<?=$row['account_id']?>">Review Account</a></td></tr>
<?php
}

if($row['contact_phone'] != "") {
?>
<tr><th>Phone Number</th><td colspan="5"><?=$row['contact_phone']?></td></tr>
<?php } else if ($_POST['cmd'] == "Mark Order as Processed") { ?>
<tr><th>Phone Number</th><td colspan="5"><input type='text' name='contact_phone' /></td></tr>

<?php } ?>
</table>
<br />
<?php if ($row['processed'] > 0 || $_POST['cmd'] == "Mark Order as Processed") { ?>
<table border="1" class="transaction_summary">
<tr><th>Date</th><th>Time</th><th>Status</th><th>Gross</th><th>Fee</th><th><?php if ($_POST['cmd'] == "Mark Order as Processed") { print "Currency"; } else { print "Net"; } ?></th></tr>
<?php if ( $_POST['cmd'] == "Mark Order as Processed") { ?>
<tr><td><input type="text" style='width: 75px;' name="payment_date" value="<?php echo date("Y-m-d"); ?>" /></td>
<td><input type="text" style='width: 75px;' name="payment_time" value="<?php echo date("H:i:s"); ?>" /></td>
<td>Completed</td><td><input style='width: 75px; text-align: right;'type="text" name="mc_gross" value="0.00" /></td><td><input style='width: 75px; text-align: right;'type="text" name="mc_fee" value="0.00" /></td><td> <input style='width: 40px; text-align: center;' type="text" name="mc_currency" value="USD" /></td></tr>

<?php } else { ?>
<tr><td><?=$row['payment_date']?></td><td><?=$row['payment_time']?></td><td><?=$row['payment_status']?></td><td><?=number_format($row['mc_gross'], 2, '.', ',')." ".$row['mc_currency']?></td><td><?=number_format($row['mc_fee'], 2, '.', ',')." ".$row['mc_currency']?></td><td><?=number_format(($row['mc_gross'] - $row['mc_fee']), 2, '.', ',')." ".$row['mc_currency']?></td></tr>
<?php } ?>
</table>
<br />

<table border="1" class="transaction_summary">
<tr><td class="transaction_heading">Shipping Information</td></tr>
<?php if ($_POST['cmd'] == "Mark Order as Processed") { ?>
<tr><td><input type='text' name='first_name' value='First Name' style='width: 145px;' /> <input style='width: 145px;' type='text' name='last_name' value='Last Name' /><br>
<input type='text' name='address_street' value='Street Address' style='width: 300px;' /><br>
<input type='text' name='address_city' value='City' />, <input style='width: 75px;' type='text' name='address_state' value='State' /> <input  style='width: 68px;' type='text' name='address_zip' value='ZIP' /><br>
<input type='text' name='address_country' value='Country' /></td></tr>

<?php } else { ?>
<tr><td><?=$row['first_name']." ".$row['last_name']?><br><?=$row['address_street']?><br><?=$row['address_city']?>, <?=$row['address_state']?> <?=$row['address_zip']?><br><?=$row['address_country']?></td></tr>
<?php } ?>
</table>
<br />
<? } ?>
<table class="shopping_cart_product_order_contents">
<tr><th class="qty_column">Qty</th><th>Item</th><th class="cost_column">Price</th></tr>

<?php
$purchaseTotal = 0;

$sql = "SELECT i.`id`, i.`quantity`, i.`item_cost`, p.`slot_1` FROM `shopping_cart_order_items` i, `shopping_cart_products` p WHERE i.`order_id`='".$_GET['id']."' AND i.`product_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."' ORDER BY i.`id` asc";
//print $sql;
$results = $dbObj->query($sql);

while($item_row = mysql_fetch_array($results)) {
	$itemAttributes = "";

	$sql = "SELECT pa.`label`, pa.`type`, oa.`value`, oa.`attribute_id` FROM `shopping_cart_order_item_attributes` oa, `shopping_cart_optional_product_attributes` pa WHERE oa.`order_item_id`='".$item_row['id']."' AND oa.`attribute_id`=pa.`id`";
	//print $sql."<br>";
	$attributes = $dbObj->query($sql);

	while($attribute = mysql_fetch_array($attributes)) {
		if($attribute['type'] == "text" || $attribute['type'] == "text area" || $attributes['type'] == "date") {
			$itemAttributes .= $attribute['label'].": ".$attribute['value']."<br><br>";
		} else if ($attribute['type'] == "section break") {
			$itemAttributes .= "<br>";
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
	<tr><td class="qty_column"><?=$item_row['quantity']?></td><td><p class="product_item_name"><?=$item_row['slot_1']."</p>".$itemAttributes?></td><td class="cost_column"><?=$slots['2']['options'].number_format($itemCost, 2, '.', ',')." ".$slots['2']['code']?></td></tr>
	<?php
}

$purchaseTotal += $row['mc_shipping'] + $row['tax'];
if($row['tax'] > 0) {
?>
<tr><td colspan="2" style="text-align: right;">Tax</td><td class="cost_column"><?=$slots['2']['options'].number_format($row['tax'], 2, '.', ',')." ".$slots['2']['code']?></td></tr>
<?php
}
?>
<tr><td colspan="2" style="text-align: right;">Shipping</td><td class="cost_column"><?php if ($_POST['cmd'] == "Mark Order as Processed") { ?><input type='text' name='mc_shipping' style='text-align: right; width: 75px;' value='0.00' /><?php } else { ?><?=$slots['2']['options'].number_format($row['mc_shipping'], 2, '.', ',')." ".$slots['2']['code']?><?php } ?></td></tr>
<tr><td colspan="2" style="text-align: right; background: #ededed; font-weight: bold;">Total</td><td class="cost_column"><?=$slots['2']['options'].number_format($purchaseTotal, 2, '.', ',')." ".$slots['2']['code']?></td></tr>
</table>
<br/><br/><br/>
	<div class="bttm_submit_button">
    <?php if ($row['processed'] > 0) { ?><input type="submit" <?php if($row['shipped'] == "1") { print 'class="order_bttn_shipped"'; } ?> name="cmd" value="<?php if($row['shipped'] == "1") { print 'Order Shipped'; } else { print 'Set Order Shipped'; } ?>" /><input type="button" name="nocmd" style="margin: 10px 0px 10px 10px;" value="View Packing Slip" onClick="window.open('<?=NUMO_FOLDER_PATH?>module/shopping_cart/packing-slip/?id=<?=$_GET['id']?>&display=response_only','packing_slip','width=750,height=500,menubar=yes,scrollbars=yes,resizable=yes')" /><?php } else if ($_POST['cmd'] == "Mark Order as Processed") { ?><input type="submit"  name="cmd" value="Complete" /><?php } else { ?><input type="submit"  name="cmd" value="Mark Order as Processed" /><?php } ?><input type="submit" name="return_cmd" style="margin: 10px 0px 10px 10px;"  value="Back" />
	</div>
</form>

<?php
}
?>