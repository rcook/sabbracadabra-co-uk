<style>
.table_data_layout td,.table_data_layout th,.highlight_label {padding: 0px 5px;}
</style>
<form method="post">
	<fieldset>
	<legend>Search</legend>
	<ul class="form_display">
		<li><label for="order_id">Order #:</label><input type="text" id="order_id" name="order_id" value="<?=$_POST['order_id']?>" /></li>
		<li><label for="order_status">Status:</label><select id="order_status" name="order_status"><option value="">All</option><option value="Completed" <? if($_POST['order_status'] == "Completed") { print 'selected="selected"'; }?>>Payment Complete</option><option value="Pending" <? if($_POST['order_status'] == "Pending") { print 'selected="selected"'; }?>>Payment Pending</option><option value="Refunded" <? if($_POST['order_status'] == "Refunded") { print 'selected="selected"'; }?>>Payment Refunded</option><option value="Reversed" <? if($_POST['order_status'] == "Reversed") { print 'selected="selected"'; }?>>Payment Chargeback</option></select></li>
		<li><label for="order_shipped">Shipped:</label><select id="order_shipped" name="order_shipped"><option value="">All</option><option value="1" <? if($_POST['order_shipped'] == "1") { print 'selected="selected"'; }?>>Yes</option><option value="0" <? if($_POST['order_shipped'] == "0" || !isset($_POST['order_shipped'])) { print 'selected="selected"'; }?>>No</option></select></li>
		<li><label for="submit_cmd">&nbsp;</label><input type="submit" name="nocmd" id="submit_cmd" value="Search" /></li>
	</ul>
	</fieldset>
	<input type="hidden" name="cmd" value="search" />
</form>
<?php
if($_POST['cmd'] == "search" && ($_POST['order_id'] != "" || $_POST['order_shipped'] != "" || $_POST['order_status'] != "")) {
?>
	<h2>Results</h2>
<?php
	$sql = "SELECT o.`id`, o.`first_name`, o.`last_name`,DATE_FORMAT(o.`payment_date`,'%b %e, %Y') as payment_date,o.`mc_gross`, o.`payment_status`, o.`shipped` FROM `shopping_cart_orders` o WHERE o.`processed`=1 AND o.`id` LIKE '%".$_POST['order_id']."%' AND o.`shipped` LIKE '%".$_POST['order_shipped']."%' AND o.`payment_status` LIKE '%".$_POST['order_status']."%' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY o.`payment_date` desc";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	//counter for odd/even styling
	$oddEvenCounter = 0;

	if(mysql_num_rows($results) > 0) {
		echo '<table class="table_data_layout"><tr><th>&nbsp;</th><th>Date</th><th class="highlight_label">Customer</th><th>Shipped</th><th>Total</th><th>&nbsp</th></tr>';

		while($row = mysql_fetch_array($results)) {
			$displayStatusImageSrc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'product_enabled.gif' : 'product_disabled.gif');
			$displayStatusImageDesc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'payment completed' : 'payment not complete');

			echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['payment_date'].'</td><td>'.$row['first_name'].' '.$row['last_name'].'</td><td align="center"><img src="images/'.($row['shipped'] == "1" ? 'yes' : 'no').'.gif" /></td><td>'.number_format($row['mc_gross'], 2, '.', ',').'</td><td><a href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'">Review</a></td></tr>';

			$oddEvenCounter++;
		}

		echo '</table>';
	} else {
		echo '<p style="font-style: italic; font-weight: bold;">No results found.</p>';
	}

} else {
?>
	<h2>Refunds</h2>

	<?php
	$sql = "SELECT o.`id`, o.`first_name`, o.`last_name`,DATE_FORMAT(o.`payment_date`,'%b %e, %Y') as payment_date,o.`mc_gross`, o.`payment_status`, o.`shipped` FROM `shopping_cart_orders` o WHERE o.`processed`=1 AND UPPER(o.`payment_status`)<>'COMPLETED' AND UPPER(o.`payment_status`)<>'PROCESSED' AND UPPER(o.`payment_status`)<>'CANCELED_REVERSAL' AND UPPER(o.`payment_status`)<>'PENDING' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY o.`payment_date` desc";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	//counter for odd/even styling
	$oddEvenCounter = 0;

	if(mysql_num_rows($results) > 0) {
		echo '<table class="table_data_layout"><tr><th>&nbsp;</th><th>Date</th><th class="highlight_label">Customer</th><th>Shipped</th><th>Total</th><th>&nbsp</th></tr>';

		while($row = mysql_fetch_array($results)) {
			$displayStatusImageSrc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'product_enabled.gif' : 'product_disabled.gif');
			$displayStatusImageDesc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'payment completed' : 'payment not complete');

			echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['payment_date'].'</td><td>'.$row['first_name'].' '.$row['last_name'].'</td><td align="center"><img src="images/'.($row['shipped'] == "1" ? 'yes' : 'no').'.gif" /></td><td>'.number_format($row['mc_gross'], 2, '.', ',').'</td><td><a href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'">Review</a></td></tr>';

			$oddEvenCounter++;
		}

		echo '</table>';
	} else {
		echo '<p style="font-style: italic; font-weight: bold;">No refunded orders found.</p>';
	}
}
?>