<?php

$currencyOptionsShort = array("AUD" => "$","CAD" => "$","EUR" => "&#128;","NZD" => "$","GBP" => "&#163;","USD" => "$");
$sql = "SELECT * FROM `shopping_cart_fields` WHERE slot='2' AND site_id='".NUMO_SITE_ID."'";
			$currencyResult	= $dbObj->query($sql);
			$currencyRecord = mysql_fetch_array($currencyResult);
			$currencySymbol = $currencyOptionsShort["{$currencyRecord['input_options']}"];

//remove listing
if($_POST['cmdb'] == "remove") {
	$discountObj = new Discount($_POST['discount_id']);
	$discountObj->remove();
} else if ($_POST['cmdb'] == "pause") {
	$discountObj = new Discount($_POST['discount_id']);
	$discountObj->pause();
} else if ($_POST['cmdb'] == "unpause") {
	$discountObj = new Discount($_POST['discount_id']);
	$discountObj->reenable();
}

$sql = "SELECT name, slot, input_options FROM shopping_cart_fields WHERE site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

$slots = array();

while($row = mysql_fetch_array($results)) {
	$slots[$row['slot']]['name'] = $row['name'];

	if($row['slot'] == "2") {
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

mysql_free_result($results);
?>
<script>
function confirmRemove(discountID) {
	if(confirm("Are you absolutely sure you wish to remove this offer?")) {
		document.forms['remove_product'].discount_id.value = discountID;
		document.forms['remove_product'].submit();
	}

	return false;
}

function pauseOffer(discountID) {
	document.forms['pause_offer'].discount_id.value = discountID;
	document.forms['pause_offer'].submit();
	
}
function unPauseOffer(discountID) {
	document.forms['unpause_offer'].discount_id.value = discountID;
	document.forms['unpause_offer'].submit();
}
</script>
<style>
.table_data_layout tr td.spaced_col {padding: 0px 3px;}
</style>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/customer-orders/">Shopping Cart</a> <span class="divider">/</span></li>
  <li class="active">Discounts &amp; Sales</li>
  <li>&nbsp; <a href="module/shopping_cart/edit-discount/" style='margin-top: -2px;' class='btn btn-success btn-mini'>Create New Offer</a></li>
</ul>

<h3>Discounts &amp; Sales</h3>
<!--
<form method="post">
	<fieldset>
	<legend>Search</legend>
	<ul class="form_display">
		<li><label for="product_name"><?=$slots[1]['name']?>:</label><input type="text" id="product_name" name="product_name" value="<?=$_POST['product_name']?>" /> <input style="margin: -10px 0px 0px 10px" type="submit" name="nocmd" id="submit_cmd" class='btn btn-primary' value="Search" /></li>
		
	</ul>
	</fieldset>
	<input type="hidden" name="cmd" value="search" />
</form>
-->
<?php
$sql = "SELECT * FROM `shopping_cart_discount` l WHERE l.discount_name LIKE '%".$_POST['discount_name']."%' AND l.status>-1 AND l.site_id='".NUMO_SITE_ID."' ORDER BY l.visibility desc,l.discount_name,l.when_created";
$results = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;

if(mysql_num_rows($results) > 0) {
	echo '<table class="table table-striped"><tr><th style="width: 115px">&nbsp;</th>
	<th style="width: 150px">Starts</th>
	<th style="width: 150px">Ends</th>
	<th>Visibility</th>
	<th>Description</th>
	<th>Scope</th>
	<th style="width: 75px; text-align: center;">Amount</th>
	<!--<th >Applied To</th>-->
	<th style="width: 155px;">&nbsp</th></tr>';

	while($row = mysql_fetch_array($results)) {
		
		//$displayStatusImageSrc = ($row['status'] == "1" ? 'product_enabled.gif' : 'product_disabled.gif');
		//$displayStatusImageDesc = ($row['status'] == "1" ? 'Product is displayed in catalog' : 'Product will not appear in catalog');

		$priceDisplay = $row['slot_2'];

		if(is_numeric($priceDisplay)) {
			$priceDisplay = $slots[2]['options'] . number_format($priceDisplay, 2, '.', ',');
		}

		echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'">';
		echo '<td>';
		if (strtotime($row['start_date']) > time()) {
			if ($row['status'] == "1") {
			  echo '<span class="badge badge-info">Scheduled</span>';
			  echo ' <span title="Pause" style="cursor: pointer" class="pull-right badge badge-inverse" onclick="pauseOffer(\''.$row['id'].'\')"><i class="icon-pause"></i></span>';
			} else if ($row['status'] == "0") {
			  echo '<span class="badge badge-warning">PAUSED</span>';
			  echo ' <span title="Enable" style="cursor: pointer" class="pull-right badge badge-inverse" onclick="unPauseOffer(\''.$row['id'].'\')"><i class="icon-play"></i></span>';
			}

			
		} else if (strtotime($row['end_date']) < time()) {
			echo '<span class="badge badge-important">Expired</span>';
		} else {
			if ($row['status'] == "1") {
			  echo '<span class="badge badge-success">LIVE</span>';
			  echo ' <span title="Pause" style="cursor: pointer" class="pull-right badge badge-inverse" onclick="pauseOffer(\''.$row['id'].'\')"><i class="icon-pause"></i></span>';
			} else if ($row['status'] == "0") {
			  echo '<span class="badge badge-warning">PAUSED</span>';
			  echo ' <span title="Enable" style="cursor: pointer" class="pull-right badge badge-inverse" onclick="unPauseOffer(\''.$row['id'].'\')"><i class="icon-play"></i></span>';
			  
			}
			
		}
	    echo '</td>';
		
		echo '<td>';
		echo date("M j, Y (H:i)", strtotime($row['start_date']));
		echo '</td>';

		echo '<td>';
		echo date("M j, Y (H:i)", strtotime($row['end_date']));
		echo '</td>';

		echo '<td>';
		
		if ($row['visibility'] == "1") {
			print "Public Offer";
		} else if ($row['visibility'] == "2") {
			print "Coupon: ".$row['access_qualifier'];
		} else if ($row['visibility'] == "3") {
			print "User Specific";
		} else if ($row['visibility'] == "4") {
			print "User Group Specific";
		}
		
		echo '</td>';

		echo '<td>'.$row['discount_name'].'</td>';
		echo '<td>';
		if ($row['qualifier_scope'] == "0") {
			print "On Entire Order ";
		} else if ($row['qualifier_scope'] == "1") {
			print "Product Category Items ";
		} else if ($row['qualifier_scope'] == "2") {
			print "Specific Product Items ";
		} 
		if ($row['discount_type'] == "0") {
			print "Value ";
		} else if ($row['discount_type'] == "1") {
			print "Quantity ";
		}
		print ">= ";
		print $row['scope_quantifier'];
	
		echo '</td>';

		echo '<td style="text-align: right;">';
		if ($row['amount_type'] == "0") {
			print $currencySymbol;
		} 
		print $row['amount'];
		if ($row['amount_type'] == "1") {
		  print "%";	
		}
		echo '</td>';		
		echo '<!--<td>';

if ($row['discount_scope'] == "0") {
			print "Order Total";
		} else {
			print "Shipping";
		}
		
		echo '</td>-->';
		
		echo '<td style="text-align: right;"><a class="btn btn-primary" href="module/'.$_GET['m'].'/edit-discount/?id='.$row['id'].'">Edit</a> <a class="btn btn-danger" href="module/'.$_GET['m'].'/'.$_GET['i'].'/" onclick="return confirmRemove(\''.$row['id'].'\');">Remove</a></td></tr>';

		$oddEvenCounter++;
	}

	echo '</table>';
} else {
	echo '<p style="font-style: italic; font-weight: bold;">No Offers Found</p>';
}
?>
<form method="post" name="remove_product" id="remove_product">
<input type="hidden" name="discount_id" value="" />
<input type="hidden" name="cmdb" value="remove" />
<input type="hidden" name="cmd" value="search" />
</form>

<form method="post" name="unpause_offer" id="unpause_offer">
<input type="hidden" name="discount_id" value="" />
<input type="hidden" name="cmdb" value="unpause" />
<input type="hidden" name="cmd" value="search" />
</form>

<form method="post" name="pause_offer" id="pause_offer">
<input type="hidden" name="discount_id" value="" />
<input type="hidden" name="cmdb" value="pause" />
<input type="hidden" name="cmd" value="search" />
</form>