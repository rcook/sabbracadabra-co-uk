<?php
$numoCartItemCount = 0;


if(isset($_SESSION['shopper_id'])) {
	//get order id for last insert
	$sql = "SELECT i.`quantity` FROM `shopping_cart_order_items` i, `shopping_cart_orders` o, `shopping_cart_products` p WHERE o.`processed`=0 AND o.`account_id`='".$_SESSION['shopper_id']."' AND o.`id`=i.`order_id` AND i.`product_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."' ORDER BY i.`id` asc";
	//print $sql;
	$results = $dbObj->query($sql);

	//account has a order pending purchase, add to order
	while($row = mysql_fetch_array($results)) {
		$numoCartItemCount += $row['quantity'];
	}

	mysql_free_result($results);
}
?>
<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/shopping_cart/components/styles/cart_items.css" />

<div class="numo_cart_items_component">
<h4><?=$numoCartItemCount." ".NUMO_SYNTAX_SHOPPING_CART_ITEMS_IN_CART_LABEL?></h4>
<a class="numo_cart_item_component_link" href="/manage.numo?module=shopping_cart&component=catalog&view=cart"><?=NUMO_SYNTAX_SHOPPING_CART_VIEW_CART_LABEL?></a>
</div>