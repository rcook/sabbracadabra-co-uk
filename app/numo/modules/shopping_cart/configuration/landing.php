<?php
include("upgrade.php");

//remove all temporary accounts that were created 2 days ago
//$sql = "DELETE FROM `accounts` WHERE `type_id`=0 AND `when_created` <= '".date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-5, date("Y")))."'";
//print $sql;
//$dbObj->query($sql);
?>
<div class="module_install_completed">
<img class='icon' src="images/shopping_cart.png" />
<a href="http://www.i3dthemes.com/support/numo_shopping_cart/" target="_blank"><img alt='Help' title='Help' class='help-icon' src="images/help.png" /></a>
<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='status-online' href="javascript:changeModuleStatus('shopping_cart', 0);" title='All SHOPPING CART related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='status-offline' href="javascript:changeModuleStatus('shopping_cart', 1);" title='All SHOPPING CART related components are now OFFLINE'>offline</a> 
<?php } ?>

<h2>Shopping Cart</h2>
<hr />
 
<h3>Today's Orders</h3>
<ul>
<?php
$sql = "SELECT o.`id`, o.`first_name`, o.`last_name`, o.`payment_status`, (o.`mc_gross` - o.`mc_fee`) as net_total FROM `shopping_cart_orders` o WHERE o.`processed`=1 AND o.`site_id`='".NUMO_SITE_ID."' AND payment_date LIKE '".date('Y-m-d')."%' ORDER BY o.`payment_date` desc";
//print $sql."<br>";
$results = $dbObj->query($sql);

$statusLabel = "";
$purchaseNetTotal = 0;

while($row = mysql_fetch_array($results)) {
	print '<li>'.$row['first_name'].' '.$row['last_name'].' ('.$row['payment_status'].') <a href="module/shopping_cart/review-order/?id='.$row['id'].'">[review]</a></li>';
	$purchaseNetTotal += $row['net_total'];
}
?>
<li style="font-weight: bold;">Net Total: <?=$purchaseNetTotal?></li>
</ul>
<p><br /><a href="module/shopping_cart/customer-orders/">[View All]</a></p>
</div>