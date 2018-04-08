<?php
include("upgrade.php");

//remove all temporary accounts that were created 2 days ago
//$sql = "DELETE FROM `accounts` WHERE `type_id`=0 AND `when_created` <= '".date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-5, date("Y")))."'";
//print $sql;
//$dbObj->query($sql);
?>
<div class="module_install_completed animated fadeInLeft">
<!--<img class='icon' src="images/shopping_cart.png" />-->
          <span class='fa-stack fa-1x pull-left' style='margin-right: 10px;'> 
            <i class='fa fa-circle fa-stack-2x'></i>
            <i class='fa fa-shopping-cart fa-stack-1x fa-inverse'></i>
          </span> 
<a class='pull-right' href="http://www.i3dthemes.com/support/numo_shopping_cart/" target="_blank" style='margin-top: 5px; border-bottom: 0px; color: #336699;'><i title='Help' class='fa fa-question-circle'></i></a>

<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='label label-info pull-right' style='margin-right: 10px;margin-top:4px;' href="javascript:changeModuleStatus('<?=$moduleRecord['name']?>', 0);" title='All SHOPPING CART related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='label label-important pull-right'  style='margin-right: 10px;margin-top:4px; ' href="javascript:changeModuleStatus('<?=$moduleRecord['name']?>', 1);" title='All SHOPPING CART related components are now OFFLINE'>offline</a> 
<?php } ?>
<h2 style='line-height: 30px;'>Shopping Cart</h2>
<hr />
<h3 style='margin-bottom: 0px;margin-top:0px; line-height: 20px; padding-top: 20px;'>Today's Orders</h3>
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
<p><br /><a class='btn btn-small btn-default' href="module/shopping_cart/customer-orders/">View All</a></p>
</div>