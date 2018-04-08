<?php
$lookupDate = date('Y-m-d');
$offset = 0;
//strtotime(

if(isset($_GET['offset']) && is_numeric($_GET['offset'])) {
	$lookupDate  = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - $_GET['offset'], date("Y")));
	$offset = $_GET['offset'];
}
?>
<style>
.transaction_summary {border: 1px solid #999; border-collapse: collapse; font-size: 12px; width: 552px;}
.transaction_summary td, .transaction_summary th {border: 1px solid #999; vertical-align: top; padding: 5px;}
.transaction_summary th {background: #cde;}
tr.refunded_order td {color: #900; font-weight: bold;}
</style>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/customer-orders/">Shopping Cart</a> <span class="divider">/</span></li>
  <li class="active">Daily Purchases</li>
</ul>		
<h3>Daily Purchases</h3>
<p>Date: <?=$lookupDate?></p>
<br />
<?php
$sql = "SELECT `id`, `first_name`, `last_name`, DATE_FORMAT(`payment_date`,'%l:%i %p') as payment_time, `mc_gross`, `mc_fee`, `payment_status` FROM `shopping_cart_orders` WHERE `processed`=1 AND `site_id`='".NUMO_SITE_ID."' AND DATE_FORMAT(`payment_date`,'%Y-%m-%d')='".$lookupDate."'  ORDER BY `payment_date` asc";
//print $sql."<br>";
$results = $dbObj->query($sql);

$dailyTotal = 0;
//counter for odd/even styling
$oddEvenCounter = 0;
$cancelledTransactions = array();
$cancelledTransactions['Denied'] = '';
$cancelledTransactions['Failed'] = '';
$cancelledTransactions['Refunded'] = '';
$cancelledTransactions['Voided'] = '';
$cancelledTransactions['Reversed'] = '';
$cancelledTransactions['Expired'] = '';

if(mysql_num_rows($results) > 0) {
	echo '<table border="1" class="transaction_summary"><tr><th>Customer</th><th>Time</th><th>Status</th><th>Gross</th><th>Fee</th><th>Net</th></tr>';

	while($row = mysql_fetch_array($results)) {
		$refundClass = "";

		if(array_key_exists($row['payment_status'],$cancelledTransactions)) {
			$refundClass = ' class="refunded_order"';
		}
		$dailyTotal += ($row['mc_gross'] - $row['mc_fee']);
		echo '<tr'.$refundClass.'><td><a href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'">'.$row['first_name'].' '.$row['last_name'].'</a></td><td>'.$row['payment_time'].'</td><td>'.$row['payment_status'].'</td><td>'.number_format($row['mc_gross'], 2, '.', ',').'</td><td>'.number_format($row['mc_fee'], 2, '.', ',').'</td><td>'.number_format(($row['mc_gross'] - $row['mc_fee']), 2, '.', ',').'</td></tr>';


	}
	echo '<tr><td colspan="5" style="background: #cde; color: #000; font-weight: bold; text-align: right;">Total</td><td>'.number_format($dailyTotal, 2, '.', ',').'</td></tr></table>';
} else {
	echo '<p style="font-style: italic; font-weight: bold;">No orders found.</p>';
}
?>
<br />
<div style="width: 552px; text-align: center; font-weight: bold;">Jump To:&nbsp;<a href="module/<?=$_GET['m']?>/daily-purchases/?offset=<?=($offset + 1)?>"><?=date('F j, Y', mktime(0, 0, 0, date("m"), date("d") - ($offset + 1), date("Y")))?></a><?php if($offset > 0) { ?>&nbsp;|&nbsp;<a href="module/<?=$_GET['m']?>/daily-purchases/?offset=<?=($offset - 1)?>"><?=date('F j, Y', mktime(0, 0, 0, date("m"), date("d") - ($offset - 1), date("Y")))?></a><?php } ?></div>