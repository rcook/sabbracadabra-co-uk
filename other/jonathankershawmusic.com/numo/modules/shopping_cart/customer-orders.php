<?php 

if ($_GET['cmd'] == "delete") { 
  $delete = "DELETE FROM shopping_cart_orders WHERE id='{$_GET['id']}'";
  $dbObj->query($delete);
  
  $sql = "SELECT * FROM  shopping_cart_order_items WHERE order_id='{$_GET['id']}'";
  $result = $dbObj->query($sql);
  while ($rec = mysql_fetch_array($result)) {
	 $delete = "DELETE FROM shopping_cart_order_item_attributes WHERE order_item_id='{$rec['id']}'";
	 $dbObj->query($delete);
  }
  
  $delete = "DELETE FROM shopping_cart_order_item_attributes WHERE order_id='{$_GET['id']}'";
  $dbObj->query($delete);
} else if ($_GET['cmd'] == "delete_all_abandoned") {


	$sql = "SELECT o.`id`, a.slot_1, o.account_id FROM `shopping_cart_orders` o, `accounts` a WHERE a.id=o.account_id AND o.`processed`=0 AND (UPPER(o.`payment_status`)<>'COMPLETED' AND UPPER(o.`payment_status`)<>'PROCESSED' AND UPPER(o.`payment_status`)<>'CANCELED_REVERSAL' AND UPPER(o.`payment_status`)<>'PENDING') AND payment_type='' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY o.`order_date`";
	//print $sql."<br>";
	$results = $dbObj->query($sql);
//	print mysql_error();

       while($row = mysql_fetch_array($results)) {
			$sql = "DELETE FROM shopping_cart_order_items WHERE `order_id`='{$row['id']}'";
	        //$results2 = $dbObj->query($sql);
		//print $sql."<br>";
	          $dbObj->query($sql);
		//$results = $dbObj->query($sql);
		//print mysql_error();
		if ($row['slot_1'] == "" && $row['slot_3'] == "") {
	          $sql = "DELETE FROM accounts WHERE slot_1='' AND slot_3='' AND id='{$row['account_id']}'";
	        //  print $sql."<br>";
	          $dbObj->query($sql);
	        }
		
		$sql = "DELETE FROM shopping_cart_orders WHERE id='{$row['id']}'";
		//print $sql."<br>";
		$dbObj->query($sql);
	}
	
	// do final clean up
	$delete = "DELETE FROM accounts WHERE slot_1='' AND slot_3='' AND type_id='0'";
	$dbObj->query($sql);
	
	
	$sql = "SELECT * FROM shopping_cart_order_items ORDER BY order_id";
	//print $sql."<br>";
	$results = $dbObj->query($sql);
	//print mysql_error();
	$orderItemsToDelete = 0;
	
	while ($row = mysql_fetch_array($results)) {
	  $query2 = "SELECT * FROM shopping_cart_orders WHERE id='{$row['order_id']}'";
	  //print $query2."<br>";
	  $result2 = $dbObj->query($query2);
	  if (mysql_num_rows($result2) == 0) {
	    $orderItemsToDelete++;
	    $sql = "DELETE FROM shopping_cart_order_items WHERE `id`='{$row['id']}'";
	   // print mysql_error();
	   // print $sql."<br>";
	    $dbObj->query($sql);
	    $sql = "DELETE FROM shopping_cart_order_item_attributes WHERE `order_item_id`='{$row['id']}'";
	    $dbObj->query($sql);
	  //  print mysql_error();
	   // print $sql."<br>";
	  }
	  mysql_free_result($result2);
	}
	//print "order items to delete: $orderItemsToDelete";
	//$sql = "DELETE FROM `shopping_cart_orders` WHERE `processed`=0 AND (UPPER(`payment_status`)<>'COMPLETED' AND UPPER(`payment_status`)<>'PROCESSED' AND UPPER(`payment_status`)<>'CANCELED_REVERSAL' AND UPPER(`payment_status`)<>'PENDING')  AND payment_type='' AND `site_id`='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	//$results = $dbObj->query($sql);
	
	$sql = "SELECT * FROM shopping_cart_order_item_attributes";
	//print $sql."<br>";
	$results = $dbObj->query($sql);
	//print mysql_error();
	$orderItemsAttributesToDelete = 0;
	
	while ($row = mysql_fetch_array($results)) {
	  $query2 = "SELECT * FROM shopping_cart_order_items WHERE id='{$row['order_item_id']}'";
	  //print $query2."<br>";
	  $result2 = $dbObj->query($query2);
	  if (mysql_num_rows($result2) == 0) {
	    $orderItemsAttributesToDelete++;
	    $sql = "DELETE FROM shopping_cart_order_item_attributes WHERE `id`='{$row['id']}'";
	    $dbObj->query($sql);
	   // print $sql."<br>"; 
	  }
	  mysql_free_result($result2);
	}
		//print "x:".$orderItemsAttributesToDelete;
	//print mysql_error();
}
?>	
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/customer-orders/">Shopping Cart</a> <span class="divider">/</span></li>
  <li class="active">Order Lookup</li>
</ul>													
<style>
.table_data_layout td,.table_data_layout th,.highlight_label {padding: 0px 5px;}
.price-col {text-align: right !important; padding-right: 40px !important;  } 
</style>
<form method="post">
	<fieldset>
	<legend>Search</legend>
	<ul class="form_display">
		<li><label for="order_id">Order #:</label><input type="text" id="order_id" name="order_id" value="<?=$_POST['order_id']?>" /></li>
		<li><label for="order_status">Status:</label><select id="order_status" name="order_status"><option value="">All</option><option value="Completed" <? if($_POST['order_status'] == "Completed") { print 'selected="selected"'; }?>>Payment Complete</option><option value="Pending" <? if($_POST['order_status'] == "Pending") { print 'selected="selected"'; }?>>Payment Pending</option><option value="Refunded" <? if($_POST['order_status'] == "Refunded") { print 'selected="selected"'; }?>>Payment Refunded</option><option value="Reversed" <? if($_POST['order_status'] == "Reversed") { print 'selected="selected"'; }?>>Payment Chargeback</option></select></li>
		<li><label for="order_shipped">Shipped:</label><select id="order_shipped" name="order_shipped"><option value="">All</option><option value="1" <? if($_POST['order_shipped'] == "1") { print 'selected="selected"'; }?>>Yes</option><option value="0" <? if($_POST['order_shipped'] == "0") { print 'selected="selected"'; }?>>No</option></select></li>
		<li><label for="submit_cmd">&nbsp;</label><input type="submit" name="nocmd" id="submit_cmd" class='btn btn-primary'  value="Search" /></li>
	</ul>
	</fieldset>
	<input type="hidden" name="cmd" value="search" />
</form>
<?php
if($_POST['cmd'] == "search" && ($_POST['order_id'] != "" || $_POST['order_shipped'] != "" || $_POST['order_status'] != "")) {
?>
	<h4>Results</h4>
<?php
	$sql = "SELECT o.`id`, o.`first_name`, o.`last_name`, DATE_FORMAT(o.`payment_date`,'%b %e, %Y') as payment_date,o.`mc_gross`, o.`payment_status`, o.`shipped` FROM `shopping_cart_orders` o WHERE o.`processed`=1 AND o.`id` LIKE '%".$_POST['order_id']."%' AND o.`shipped` LIKE '%".$_POST['order_shipped']."%' AND o.`payment_status` LIKE '%".$_POST['order_status']."%' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY o.`payment_date` desc";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	//counter for odd/even styling
	$oddEvenCounter = 0;

	if(mysql_num_rows($results) > 0) {
		echo '<table class="table table-striped"><tr><th style="width: 20px;">&nbsp;</th><th style="width: 40px">Shipped</th><th style="width: 100px">Date</th><th class="highlight_label">Customer</th><th style="width: 50px">Total</th><th>&nbsp</th></tr>';

		while($row = mysql_fetch_array($results)) {
			$displayStatusImageSrc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'product_enabled.gif' : 'product_disabled.gif');
			$displayStatusImageDesc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'payment completed' : 'payment not complete');

			echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td style="text-align: center"><img src="images/'.($row['shipped'] == "1" ? 'yes' : 'no').'.gif" /></td><td>'.$row['payment_date'].'</td><td>'.$row['first_name'].' '.$row['last_name'].'</td><td class="price-col">'.number_format($row['mc_gross'], 2, '.', ',').'</td><td style="text-align: right; width: 150px;"><a href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'" class="btn" >Review</a></td></tr>';

			$oddEvenCounter++;
		}

		echo '</table>';
	} else {
		echo '<p style="font-style: italic; font-weight: bold;">No results found.</p>';
	}

} else {
?>
	<h4>Orders</h4>
	<?php
	$sql = "SELECT o.`id`, o.`first_name`, o.`last_name`, o.payment_type, DATE_FORMAT(o.`payment_date`,'%b %e, %Y') as payment_date, DATE_FORMAT(o.`order_date`,'%b %e, %Y') as order_date,o.`mc_gross`, o.`payment_status`, o.`shipped` FROM `shopping_cart_orders` o WHERE o.`processed`=1 AND (UPPER(o.`payment_status`)='COMPLETED' OR UPPER(o.`payment_status`)='PROCESSED' OR UPPER(o.`payment_status`)='CANCELED_REVERSAL' OR UPPER(o.`payment_status`)='PENDING') AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY o.`payment_date` desc LIMIT 100";
	//print $sql;
	$results = $dbObj->query($sql);

	//counter for odd/even styling
	$oddEvenCounter = 0;

	if(mysql_num_rows($results) > 0) {
		echo '<table class="table table-striped"><tr><th style="width: 20px;">&nbsp;</th><th style="width: 40px;text-align: center">ID</th><th style="width: 40px;">Shipped</th><th style="width: 100px">Date</th><th class="highlight_label">Customer</th><th>Method</th><th style="width: 70px">Total</th><th>&nbsp</th></tr>';

		while($row = mysql_fetch_array($results)) {
			$displayStatusImageSrc = (strtoupper($row['payment_status']) == "PENDING" ? 'product_pending.gif' : 'product_enabled.gif');
			$displayStatusImageDesc = (strtoupper($row['payment_status']) == "PENDING" ? 'payment pending' : 'payment completed');

			echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['id'].'</td><td style="text-align: center"><img src="images/'.($row['shipped'] == "1" ? 'yes' : 'no').'.gif" /></td><td>'.$row['order_date'].'</td><td>'.$row['first_name'].' '.$row['last_name'].'</td><td>'.$row['payment_type'].'</td><td class="price-col">'.number_format($row['mc_gross'], 2, '.', ',').'</td><td style="text-align: right;  width: 150px;"><a class="btn" href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'">Review</a></td></tr>';

			$oddEvenCounter++;
		}

		echo '</table>';
	} else {
		echo '<p style="font-style: italic; font-weight: bold;">No processed orders found.</p>';
	}
	?>
 <br/>
	<h4>Refunds</h4>

	<?php
	$sql = "SELECT o.`id`, o.`first_name`, o.`last_name`,DATE_FORMAT(o.`payment_date`,'%b %e, %Y') as payment_date,o.`mc_gross`, o.`payment_status`, o.`shipped` FROM `shopping_cart_orders` o WHERE o.`processed`=1 AND UPPER(o.`payment_status`)<>'COMPLETED' AND UPPER(o.`payment_status`)<>'PROCESSED' AND UPPER(o.`payment_status`)<>'CANCELED_REVERSAL' AND UPPER(o.`payment_status`)<>'PENDING' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY o.`payment_date` desc LIMIT 100";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	//counter for odd/even styling
	$oddEvenCounter = 0;

	if(mysql_num_rows($results) > 0) {
		echo '<table class="table table-striped"><tr><th style="width: 20px;">&nbsp;</th><th style="width: 40px; text-align: center;">ID</td><th style="width: 40px">Shipped</th><th style="width: 100px">Date</th><th class="highlight_label">Customer</th><th>Method</th><th style="width: 70px">Total</th><th>&nbsp</th></tr>';

		while($row = mysql_fetch_array($results)) {
			$displayStatusImageSrc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'product_enabled.gif' : 'product_disabled.gif');
			$displayStatusImageDesc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'Payment Completed' : 'Payment Not Complete');

			echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['id'].'</td><td style="text-align: center"><img src="images/'.($row['shipped'] == "1" ? 'yes' : 'no').'.gif" /></td><td>'.$row['payment_date'].'</td><td>'.$row['first_name'].' '.$row['last_name'].'</td><td>'.$row['payment_type'].'</td><td class="price-col">'.number_format($row['mc_gross'], 2, '.', ',').'</td><td style="text-align: right;  width: 150px;"><a class="btn" href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'">Review</a></td></tr>';

			$oddEvenCounter++;
		}

		echo '</table>';
	} else {
		echo '<p style="font-style: italic; font-weight: bold;">No refunded orders found.</p>';
	}
	?>
    <br/>
	<h4>Pending Orders</h4>
    <p>"Pending Orders" are orders which checkout was started but (possibly) abandonned.</p>

	<?php
	$sql = "SELECT  o.`id`, a.`slot_4` as `first_name`, DATE_FORMAT(o.`order_date`,'%b %e, %Y') as o_date, o.`payment_type`, o.`mc_gross`, o.`payment_status`, o.`shipped` FROM `shopping_cart_orders` o, `accounts` a WHERE a.id=o.account_id AND o.`processed`=0 AND (UPPER(o.`payment_status`)<>'COMPLETED' AND UPPER(o.`payment_status`)<>'PROCESSED' AND UPPER(o.`payment_status`)<>'CANCELED_REVERSAL') AND o.`site_id`='".NUMO_SITE_ID."' AND payment_type<>'' ORDER BY o.`order_date` desc LIMIT 100";
	$results = $dbObj->query($sql);

	//counter for odd/even styling
	$oddEvenCounter = 0;

	if(mysql_num_rows($results) > 0) {
		echo '<table class="table table-striped"><tr><th style="width: 20px;">&nbsp;</th><th style="width: 40px;">ID</th><th style="width: 100px">Date</th><th class="highlight_label">User</th><th>Method</th><th style="width: 70px">Total</th><th>&nbsp</th></tr>';
        $orderTotal = 0;
		while($row = mysql_fetch_array($results)) {
			$sql = "SELECT i.`id`, i.`quantity`, i.`item_cost`, p.`slot_1` FROM `shopping_cart_order_items` i, `shopping_cart_products` p WHERE i.`order_id`='{$row['id']}' AND i.`product_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."'  ORDER BY i.`id` asc";
			//print $sql;
			$results2 = $dbObj->query($sql);
			
			while($item_row = mysql_fetch_array($results2)) {

			
				$itemCost = $item_row['quantity'] * $item_row['item_cost'];
				$orderTotal += $itemCost;
			
			}


			$displayStatusImageSrc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'product_enabled.gif' : 'product_disabled.gif');
			$displayStatusImageDesc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'payment completed' : 'payment not complete');
            
			echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['id'].'</td><td>'.($row['o_date'] == "" ? "Unknown" : $row['o_date']).'</td><td>'.($row['first_name'].$row['last_name'] == "" ? "Guest" : $row['first_name'].' '.$row['last_name']).'</td><td>'.$row['payment_type'].'</td><td class="price-col">'.number_format($orderTotal, 2, '.', ',').'</td><td style="text-align: right;  width: 150px;"><a href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'" class="btn" >Review</a> <a class="btn btn-danger"  href="module/'.$_GET['m'].'/customer-orders/?cmd=delete&id='.$row['id'].'" onclick="return confirm(\'Are you sure you want to permanently delete this order from the system?\')">Delete</a></td></tr>';

			$oddEvenCounter++;
		}

		echo '</table>';
	} else {
		echo '<p style="font-style: italic; font-weight: bold;">No pending orders found.</p>';
	}	
	?>
    <br/>

	<h4>Abandoned Carts</h4>

<p>"Abandoned Carts" are shopping carts which were abanadoned before the user proceeded to checkout.</p>
	<?php
	$sql = "SELECT o.`id`, a.`slot_4` as `first_name`, DATE_FORMAT(o.`order_date`,'%b %e, %Y') as c_date,o.`mc_gross`, o.`payment_status`, o.`shipped` FROM `shopping_cart_orders` o, `accounts` a WHERE a.id=o.account_id AND o.`processed`=0 AND (UPPER(o.`payment_status`)<>'COMPLETED' AND UPPER(o.`payment_status`)<>'PROCESSED' AND UPPER(o.`payment_status`)<>'CANCELED_REVERSAL' AND UPPER(o.`payment_status`)<>'PENDING')  AND payment_type='' AND o.`site_id`='".NUMO_SITE_ID."' ORDER BY o.`order_date` desc LIMIT 100";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	//counter for odd/even styling
	$oddEvenCounter = 0;

	if(mysql_num_rows($results) > 0) {
		echo '<table class="table table-striped"><tr><th style="width: 20px;">&nbsp;</th><th style="width: 40px;">ID</th><th style="width: 100px;">Date</th><th class="highlight_label">User</th><th style="width: 70px">Amount</th><th>';
		?><a class="btn btn-danger pull-right" style='width: 116px;' href="module/shopping_cart/customer-orders/?cmd=delete_all_abandoned" onclick="return confirm('Are you sure you want to permanently delete all abandoned orders from the system?')"><i class='fa fa-trash-o'></i> Delete All</a><?php
		echo '</th></tr>';
        $orderTotal = 0;
		while($row = mysql_fetch_array($results)) {
			$orderTotal = 0;
			$sql = "SELECT i.`id`, i.`quantity`, i.`item_cost`, p.`slot_1` FROM `shopping_cart_order_items` i, `shopping_cart_products` p WHERE i.`order_id`='{$row['id']}' AND i.`product_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."'  ORDER BY i.`id` asc";
			//print $sql;
			$results2 = $dbObj->query($sql);
			
			while($item_row = mysql_fetch_array($results2)) {

			
				$itemCost = $item_row['quantity'] * $item_row['item_cost'];
				$orderTotal += $itemCost;
			
			}


			$displayStatusImageSrc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'product_enabled.gif' : 'product_disabled.gif');
			$displayStatusImageDesc = (strtoupper($row['payment_status']) == "COMPLETED" ? 'payment completed' : 'payment not complete');
            
			echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'"><td><img src="modules/shopping_cart/images/'.$displayStatusImageSrc.'" alt="'.$displayStatusImageDesc.'" title="'.$displayStatusImageDesc.'" /></td><td>'.$row['id'].'</td><td>'.($row['c_date'] == "" ? "Unknown" : $row['c_date']).'</td><td>'.($row['first_name'].$row['last_name'] == "" ? "Guest" : $row['first_name'].' '.$row['last_name']).'</td><td class="price-col">'.number_format($orderTotal, 2, '.', ',').'</td><td style="text-align: right;  width: 150px;"><a class="btn" href="module/'.$_GET['m'].'/review-order/?id='.$row['id'].'">Review</a> <a class="btn btn-danger" href="module/'.$_GET['m'].'/customer-orders/?cmd=delete&id='.$row['id'].'" onclick="return confirm(\'Are you sure you want to permanently delete this order from the system?\')">Delete</a></td></tr>';

			$oddEvenCounter++;
		}

		echo '</table>';
	} else {
		echo '<p style="font-style: italic; font-weight: bold;">No abandoned carts found.</p>';
	}		
}
?>