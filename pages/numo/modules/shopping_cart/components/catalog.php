[NUMO.SHOPPING CART: BREADCRUMB]
<?php
   //print $_SERVER['REQUEST_URI'];
   //phpinfo(); 
    $sql = "SELECT shopping_cart_discount, show_original_price FROM types WHERE site_id='".NUMO_SITE_ID."' AND id='{$_SESSION['type_id']}'";
	$discountResult = $dbObj->query($sql);
    $shoppingCartDiscount = mysql_fetch_array($discountResult);
	$showOriginalPrice   = ($shoppingCartDiscount['show_original_price'] == 1);
	$shoppingCartDiscount = number_format($shoppingCartDiscount['shopping_cart_discount'], 2, '.', '');
	$dbObj->query("SET NAMES UTF8");
	
	$sql = "SELECT * FROM `shopping_cart_settings` WHERE `site_id`='".NUMO_SITE_ID."'";
	$settings = $dbObj->query($sql);
	$settings = mysql_fetch_array($settings);
	
	$sql = "SELECT * FROM `shopping_cart_taxes` WHERE `site_id`='".NUMO_SITE_ID."'";
	$taxRates = array();
	$taxRateResult = @$dbObj->query($sql);
	if (@mysql_num_rows($taxRateResult) > 0) {
		while ($taxRateRecord = mysql_fetch_array($taxRateResult)) {
		  $taxRateID = $taxRateRecord['tax_rate_id'];
		  $taxRates["$taxRateID"] = $taxRateRecord;
		}
	}

//check to see if the function has already been declared by another instance of the component
if(!function_exists('display_shopping_product_description')) {
	function display_shopping_product_description($description) {
		//check to see if the string is longer than 200 characters
		if(strlen($description) > 200) {
			//find the next space after 200 characters
			$pos = strpos(substr($description, 200)," ");

			//check to see if space was found after the 200th character
			if ($pos === false) {
				//no space found in last part of the description so just split at 150
				if (stristr($description,'<table')) {

				  return substr($description, 0,200)."...";

				} else {

				  return nl2br(substr($description, 0,200))."...";
				}
			} else {
				if (stristr($description,'<table')) {
				  return substr($description, 0,($pos + 200))."...";

				} else {
				//continue until the space found then cut off
				return nl2br(substr($description, 0,($pos + 200)))."...";
				}
			}
		}
		//print "x";
		$description = str_replace("&pound;", "&amp;pound;", $description);
	   if (stristr($description,'<table')) {
	     return $description;
		} else {
		//string less than 200 characters so no need cut off
		return nl2br($description);
	   }
	}
}
$showAuthBoxes = false;

if($_POST['cmd'] == "create" && !isset($_SESSION['account_id'])) {
	print "[NUMO.SHOPPING CART: REGISTER]";
}

if($_POST['numo_cmdc'] == NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_SHOPPING_LABEL) {
	if (strstr($_SERVER['REQUEST_URI'], "manage.numo")) {
	  header('Location: '.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog');
	} else {
	  header('Location: ?');
	}
}

if($_POST['numo_cmdb'] == NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_BUTTON_LABEL && !isset($_SESSION['account_id'])) {
	print '<table cellpadding="10" cellspacing="10"><tr><td valign="top" style="border: 1px dotted #ddd;"><h2>'.NUMO_SYNTAX_SHOPPING_CART_RETURNING_CUSTOMER_LABEL.'</h2><p>'.NUMO_SYNTAX_SHOPPING_CART_LOGIN_TO_ACCOUNT_LABEL.'</p>[NUMO.ACCOUNTS: LOGIN BOX]</td>[NUMO.SHOPPING CART: REGISTER]</tr></table>';
	return;
}

if($_POST['cmd'] == "login" && !isset($_SESSION['account_id'])) {
	print "[NUMO.ACCOUNTS: LOGIN BOX]";
}

$getStringValues = "";

foreach($_GET as $key => $value) {
	if($key != "where" && $key != "page" && $key != "product_id" && $key != "numo_cmd") {
		$getStringValues .= "&".$key."=".$value;
	}
}

/**********************************************/
/**************SETUP SESSION INFO**************/
/**********************************************/
//print $_SESSION['shopper_id'].":".$_SESSION['account_id']."<br>";
//if a visitor is NOT logged in and does not have a shopper id set
if(!isset($_SESSION['shopper_id']) && !isset($_SESSION['account_id'])) {
	//create partial account
	$sql = "INSERT INTO `accounts` (type_id,pending,ip_address,slot_2) VALUES (0,3,'".$_SERVER['REMOTE_ADDR']."','".crypt(time())."')";
	//print $sql."<br>";
	$dbObj->query($sql);

	//get account id for last insert
	$sql = "SELECT LAST_INSERT_ID() as 'account_id'";
	//print $sql."<br>";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		//set account id to cart id
		$_SESSION['shopper_id'] = $row['account_id'];
	}

//if an visitor is logged in but does not have a shopper id set
} else if(!isset($_SESSION['shopper_id']) && isset($_SESSION['account_id'])) {
	$_SESSION['shopper_id'] = $_SESSION['account_id'];

} else if(isset($_SESSION['shopper_id']) && isset($_SESSION['account_id']) && $_SESSION['shopper_id'] != $_SESSION['account_id']) {
	//get
	$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['account_id']."'";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	//if existing pending order for ACCOUNT merge temp account pending order items
	if($row = mysql_fetch_array($results)) {
		//get
		$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
		//print $sql."<br>";
		$orders = $dbObj->query($sql);

		//account has a order pending purchase, add to order
		if($order = mysql_fetch_array($orders)) {
			$sql = "UPDATE `shopping_cart_order_items` SET `order_id`='".$row['id']."' WHERE `order_id`='".$order['id']."'";
			//print $sql."<br>";
			$dbObj->query($sql);

			$sql = "DELETE FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
			//print $sql."<br>";
			$dbObj->query($sql);

			$sql = "DELETE FROM `accounts` WHERE `pending`=3 AND `id`='".$_SESSION['shopper_id']."'";
			//print $sql."<br>";
			$dbObj->query($sql);

		//no pending orders, just remove account
		}	else {
			$sql = "DELETE FROM `accounts` WHERE `pending`=3 AND `id`='".$_SESSION['shopper_id']."'";
			//print $sql."<br>";
			$dbObj->query($sql);
		}

	//if no orders under account just change account id on existing order
	} else {
			$sql = "UPDATE `shopping_cart_orders` SET `account_id`='".$_SESSION['account_id']."' WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
			//print $sql."<br>";
			$dbObj->query($sql);

			$sql = "DELETE FROM `accounts` WHERE `pending`=3 AND `id`='".$_SESSION['shopper_id']."'";
			//print $sql."<br>";
			$dbObj->query($sql);
	}

	$_SESSION['shopper_id'] = $_SESSION['account_id'];

}
//print $_SESSION['shopper_id'].":".$_SESSION['account_id']."<br>";
/**********************************************/
/**********************************************/

if($_POST['numo_cmd'] == "add_to_cart" || $_GET['numo_cmd'] == "add_to_cart") {
	$productId = $_POST['product_id'];

	if(isset($_GET['product_id'])) {
		$productId = $_GET['product_id'];
	}

	$orderId = 0;

	//get order id for last insert
	$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
	//print $sql."<br>";
	$orders = $dbObj->query($sql);

	// account has a order pending purchase, add to order
	if($order = mysql_fetch_array($orders)) {
		// set account id to cart id
		$orderId = $order['id'];

	// no existing pending orders, create a new one
	}	else {
		//create order for account
		$sql = "INSERT INTO `shopping_cart_orders` (account_id,site_id) VALUES ('".$_SESSION['shopper_id']."','".NUMO_SITE_ID."')";
		//print $sql."<br>";
		$dbObj->query($sql);

		//get order id for last insert
		$sql = "SELECT LAST_INSERT_ID() as 'id'";
		//print $sql."<br>";
		$result = $dbObj->query($sql);

		if($row = mysql_fetch_array($result)) {
			//set the order id
			$orderId = $row['id'];
		}
	}



	// insert item into shoppers cart
	$sql = "INSERT INTO `shopping_cart_order_items` (order_id,product_id) VALUES ('".$orderId."','".$productId."')";
	//print $sql."<br>";
	$dbObj->query($sql);

	//get order id for last insert
	$sql = "SELECT LAST_INSERT_ID() as 'item_id'";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		foreach($_POST as $key => $value) {
			if(substr($key, 0, 6) == "attr__" && $value != "") {
				$sql = "INSERT INTO `shopping_cart_order_item_attributes` (order_item_id,attribute_id,value) VALUES ('".$row['item_id']."','".substr($key, 6)."','".$value."')";
				//print $sql."<br>";
				$dbObj->query($sql);
				//print mysql_error();
			}
		}
	}
				//exit;

	//redirect to cart
	header('Location: ?view=cart'.$getStringValues);
} else if($_POST['numo_cmd'] == "update_cart_order") {
	foreach($_POST as $key => $value) {
		if(substr($key, 0, 11) == "cart_item__" && is_numeric($value)) {
			$key = substr($key, 11);

			//if no units wanted remove from cart
			if($value == "0") {
				$sql = "DELETE FROM `shopping_cart_order_items` WHERE `id`='".$key."'";
				//print $sql;
				$dbObj->query($sql);

				$sql = "DELETE FROM `shopping_cart_order_item_attributes` WHERE `order_item_id`='".$key."'";
				//print $sql;
				$dbObj->query($sql);

			} else {
				$sql = "UPDATE `shopping_cart_order_items` SET `quantity`=".$value." WHERE `id`='".$key."'";
				//print $sql;
				$dbObj->query($sql);
			}
		}
	}
}
/*foreach($_POST as $key => $value) {
	print $key." --> ".$value."<br>";
}*/


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

mysql_free_result($results);
?>
<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/shopping_cart/components/styles/catalog.css" />
<?php

if($_GET['view'] == "cart" || $PARAMS['view'] == "cart") {
?>
<script>
function updatePayPalQuantity(elId,inpt) {
	var maxQuantity = document.getElementById("max_" + inpt.name).value;
	//alert(maxQuantity + " > " + inpt.value);
	if (maxQuantity < inpt.value) {
		inpt.form.submit();
	} else {
	  document.getElementById("numo_paypal_quantity_"+elId).value = inpt.value;
	}
}
function validateQuantities() {
	var myForm = document.cart_form;
	
	for (i = 0; i < myForm.elements.length; i++) {
		
		
		if (myForm.elements[i].id.search("max_cart_item__") == 0) {
		  currentMax = myForm.elements[i].value;
		  quantityGiven = document.getElementById(myForm.elements[i].id.replace("max_", "")).value;
		  if (currentMax < quantityGiven) {
			  document.getElementById(myForm.elements[i].id.replace("max_", "")).value = currentMax;
		     // document.getElementById("numo_paypal_quantity_"+elId).value = inpt.value;
		  }
			  
		}
		
	}
	return true;
	
}
</script>
<?php
	$counter = 1;
	$paypalItemInfo = "";
	$orderId = 0;

	$stockUsed = array();

	//get order id for last insert
	$sql = "SELECT i.`product_id`,i.order_id, i.id, i.`quantity`, p.`slot_1`, p.`slot_2`, p.`slot_8`, p.`slot_5`, p.`slot_7`, p.`shipping`,p.`shipping2` FROM `shopping_cart_order_items` i, `shopping_cart_orders` o, `shopping_cart_products` p WHERE o.`processed`=0 AND o.`account_id`='".$_SESSION['shopper_id']."' AND o.`id`=i.`order_id` AND i.`product_id`=p.`id` AND p.`site_id`='".NUMO_SITE_ID."' ORDER BY i.`id` asc";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	$totalCost = 0;
	$shippingCost = 0;
	$cartItems = array();

	print '<form name="cart_form" id="cart_form" method="post" action="" style="display: inline; margin: 0px; padding: 0px;">';
	print '<table class="view_cart_contents" width="100%"><tr><th>Qty</th><th>Product</th><th class="view_cart_contents_item_cost">Unit Cost</th>';

	print '<th class="view_cart_contents_item_cost">Total</th>';

	if ($settings['tax_display_preference'] == 1 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
	  print '<th class="view_cart_contents_item_tax">Tax Rate</th>';
	  print '<th class="view_cart_contents_item_tax">Tax Amount</th>';			 
	// show the price including the tax 
	} else if ($settings['tax_display_preference'] == 2) {
	  print '<th class="view_cart_contents_item_tax">Tax Rate</th>';
	  print '<th class="view_cart_contents_item_tax">Tax Included</th>';	 
	}

	print '</tr>';

	//account has a order pending purchase, add to order
	while($row = mysql_fetch_array($results)) {
	 // foreach ($row as $x => $y) {
		//  print $x."=".$y."<br>";
	 // }
	    $orderId = $row['order_id'];
		//$itemCost = $row['slot_2'];
				
				 // show price AND the tax
				 if ($settings['tax_display_preference'] == 1 || $settings['tax_display_preference'] == 0) {
					 	$itemCost = $row['slot_2'];
						$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);


					    //print number_format($row['slot_2'], 2, '.', ',');
						
						$taxRateID = $row['slot_8'];
						if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
							//print "<br>Plus ".$taxRates["{$taxRateID}"]["rate_name"]." (".rtrim($taxRates["{$taxRateID}"]["tax_rate"], '.0')."%): ";
							//print $slots['2']['options'].number_format($row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
													$itemTax = number_format($itemCost*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
							$taxRate = $taxRates["{$row['slot_8']}"]["tax_rate"];
						} else {
							$taxRate = 0;
						  $itemTax = 0;
						}
					   // print "should price and vat";
				 
				 // show the price including the tax 
				 } else if ($settings['tax_display_preference'] == 2) {
						
						$taxRateID = $row['slot_8'];
						if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
							$itemCost = number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
							$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);

							$itemTax = number_format($row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
														$taxRate = $taxRates["{$row['slot_8']}"]["tax_rate"];
//print ;

							//print "<br>Includes ".rtrim($taxRates["{$taxRateID}"]["tax_rate"], '.0')."% ".$taxRates["{$taxRateID}"]["rate_name"];
						} else {
					    	//print number_format($row['slot_2'], 2, '.', ',');
							$itemCost = $row['slot_2'];
							$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
							$itemTax = 0;
							$taxRate = 0;

						}
						
				 } else {
					$itemCost = $row['slot_2'];
												$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);

					$itemTax  = 0;
												$taxRate = 0;

					//print number_format($row['slot_2'], 2, '.', ',');
				 }		
				 //$totalTax += $itemTax;
				 $row['item_tax'] = $itemTax;
		$itemAttributes = "";
		$labelAttrUsed = false;

		$stockKey = $row['product_id'];

		//$sql = "SELECT pa.`label` as attribute_label, pao.`label` as option_label, pao.`cost` FROM `shopping_cart_order_item_attributes` oa, `shopping_cart_optional_product_attributes` pa, `shopping_cart_optional_product_attribute_options` pao WHERE oa.`order_item_id`='".$row['id']."' AND oa.`attribute_id`=pa.`id` AND pa.`id`=pao.`attribute_id` AND pao.`id`=oa.`value`";
		$sql = "SELECT pa.`label`, pa.`type`, oa.`value`, oa.`attribute_id` FROM `shopping_cart_order_item_attributes` oa, `shopping_cart_optional_product_attributes` pa WHERE oa.`order_item_id`='".$row['id']."' AND oa.`attribute_id`=pa.`id` ORDER BY pa.`position` asc";
		//print $sql."<br>";
		$attributes = $dbObj->query($sql);

		while($attribute = mysql_fetch_array($attributes)) {
			if($attribute['type'] == "text" || $attribute['type'] == "date" || $attribute['type'] == "text area") {
				$itemAttributes .= $attribute['label'].": ".$attribute['value']."<br>";
				$labelAttrUsed = true;
			} else if ($attribute['type'] == "section break") {
			  // do nothing
			} else {
				$sql = "SELECT `id`,`label`,`cost` FROM `shopping_cart_optional_product_attribute_options` WHERE `attribute_id`='".$attribute['attribute_id']."' AND `id`='".$attribute['value']."'";
				//print $sql."<br>";
				$result = $dbObj->query($sql);

				if($option = mysql_fetch_array($result)) {
					$itemAttributes .= $attribute['label'].": ".$option['label']."<br>";
					$itemCost += $option['cost'];
					$stockKey .= "-".$option['id'];
				}

				mysql_free_result($result);
			}
		}
	//	$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);

		if(array_key_exists($stockKey,$cartItems) && !$labelAttrUsed) {
			$cartItems[$stockKey]['quantity']   += $row['quantity'];

			$sql = "UPDATE `shopping_cart_order_items` SET `quantity`='".$cartItems[$stockKey]['quantity']."' WHERE `id`='".$cartItems[$stockKey]['id']."'";
			$dbObj->query($sql);

			$sql = "DELETE FROM `shopping_cart_order_items` WHERE `id`='".$row['id']."'";
			//print $sql;
			$dbObj->query($sql);

			$sql = "DELETE FROM `shopping_cart_order_item_attributes` WHERE `order_item_id`='".$row['id']."'";
			//print $sql;
			$dbObj->query($sql);
		} else {
			$tempId = $stockKey;

			if($labelAttrUsed) {
				$tempId = count($cartItems)."__".$stockKey;
			}

			$cartItems[$tempId] = array();
			$cartItems[$tempId]['id']         = $row['id'];
			$cartItems[$tempId]['stock_key']  = $stockKey;
			$cartItems[$tempId]['item_tax']     = $row['item_tax'];
			$cartItems[$tempId]['tax_rate']     = $taxRate;
			$cartItems[$tempId]['slot_1']     = $row['slot_1'];
			$cartItems[$tempId]['slot_5']     = $row['slot_5'];
			$cartItems[$tempId]['slot_8']     = $row['slot_8'];
			$cartItems[$tempId]['slot_7']     = $row['slot_7'];
			//print $row['slot_7']."x";
			$cartItems[$tempId]['quantity']   = $row['quantity'];
			$cartItems[$tempId]['shipping']   = $row['shipping'];
			$cartItems[$tempId]['shipping2']  = $row['shipping2'];
			$cartItems[$tempId]['unit_cost']  = number_format($itemCost, 2, '.', '');
			$cartItems[$tempId]['attributes'] = $itemAttributes;
		}
	}

	/***************************/
	/**  CHECK STOCK AMOUNTS  **/
	/***************************/
	foreach($cartItems as $key => $row) {
		$stockKey = $row['stock_key'];

		$sql = "SELECT `units` FROM `shopping_cart_product_stock` WHERE `key`='".$stockKey."'";
		//print $sql."<br>";
		$stock_result = $dbObj->query($sql);

		$unitsInStock = 0;

		if($stock = mysql_fetch_array($stock_result)) {
			$unitsInStock = $stock['units'];
			$stockNotUsed = false;
		} else {
			$stockNotUsed = true;
		}
		
		if (!$stockNotUsed) {
		  // $unitsInStock -= $row['quantity'];
		   $stockUsed["$stockKey"] = $row['quantity'];	
			
		} else {
		   $stockUsed["$stockKey"] = 0;	
		}
/*
		if(array_key_exists($stockKey, $stockUsed)) {
			$unitsInStock -= $stockUsed[$stockKey];
			$stockNotUsed = false;
			//print "y";
		} else {
			//print "x";
			$stockUsed[$stockKey] = 0;
			$stockNotUsed = true;
		}
		*/
        
		//print $unitsInStock."<br>";
		//print $stockUsed["$stockKey"];
		//print $stockNotUsed;
		
		// no units available
		if(!$stockNotUsed && $unitsInStock <= 0) {
			$row['item_tax'] = $row['item_tax'] * $row['quantity'];
			
			
			//remove item from order
			$sql = "DELETE FROM `shopping_cart_order_items` WHERE `id`='".$row['id']."'";
			//print $sql;
			$dbObj->query($sql);

			$sql = "DELETE FROM `shopping_cart_order_item_attributes` WHERE `order_item_id`='".$row['id']."'";
			$dbObj->query($sql);

			print '<tr><td class="view_cart_contents_item_quantity">&nbsp;</td><td class="view_cart_contents_item_info"><h4>'.$row['slot_1'].($row['slot_7'] != "" ? " #".$row['slot_7'] : "").'</h4><p class="shopping_cart_product_not_in_stock">'.NUMO_SYNTAX_SHOPPING_CART_NOT_IN_STOCK_LABEL.'</p></td><td class="view_cart_contents_item_cost">'.($slots['2']['options'].number_format("0", 2, '.', ',')).'</td>';
			print '<td class="view_cart_contents_item_cost">'.($slots['2']['options'].number_format("0", 2, '.', ',')).'</td>';

			if ($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
			  print '<td class="view_cart_contents_item_quantity">'.clean_num($row['tax_rate']).'%</td>';
			  print '<td class="view_cart_contents_item_quantity">'.$slots['2']['options'].$row['item_tax'].'</td>';
			} 

			print '</tr>';
        //print "cc";
		// some units available but not enough
		} else if(!$stockNotUsed && $unitsInStock < $row['quantity']) {
			$quantityAvailable = $unitsInStock;

			// if no stock used set shipping amount normally
			if($stockUsed[$stockKey] <= 1) {
				$shippingCost += $row['shipping'] + ($row['shipping2'] * ($quantityAvailable - 1));

			//if 1 or more units have been shown in the cart adjust shipping cost
			} else {
				if ($quantityAvailable - 1 > 0) {
				  $shippingCost += $row['shipping'] + ($row['shipping2'] * ($quantityAvailable - 1));
				  //$shippingCost += ($row['shipping2'] * $quantityAvailable);
				 
				  // disabled this december 29th as it is setting the main shipping amount to the secondary shipping amount if quantity is greater than 1
				  //$row['shipping'] = $row['shipping2'];
				}
			}

			//by default do not include any information about shipping ... let payapl settings handle shipping
			$paypalItemInfo .= '<input type="hidden" name="item_name_'.$counter.'" value="'.htmlentities($row['slot_1'].' ('.str_replace("<br>",", ",substr($row['attributes'],0,-4))).')"><input type="hidden" name="amount_'.$counter.'" value="'.$row['unit_cost'].'"><input type="hidden" name="quantity_'.$counter.'" id="numo_paypal_quantity_'.$counter.'" value="'.$quantityAvailable.'"><input type="hidden" name="item_number_'.$counter.'" value="'.$row['id'].'"><input type="hidden" name="tax_'.$counter.'" value="'.$row['item_tax'].'">';
            // print "bbb";
			
			 //exit;
			if($row['shipping'] > 0) {
				//$paypalItemInfo .= '<input type="hidden" name="item_name_'.$counter.'" value="'.htmlentities($row['slot_1'].' ('.str_replace("<br>",", ",substr($row['attributes'],0,-4))).')"><input type="hidden" name="amount_'.$counter.'" value="'.$row['unit_cost'].'"><input type="hidden" name="quantity_'.$counter.'" id="numo_paypal_quantity_'.$counter.'" value="'.$quantityAvailable.'"><input type="hidden" name="item_number_'.$counter.'" value="'.$row['id'].'"><input type="hidden" name="shipping_'.$counter.'" value="'.$row['shipping'].'"><input type="hidden" name="shipping2_'.$counter.'" value="'.$row['shipping2'].'">';
				if ($row['slot_5'] == 0) {
				 // $paypalItemInfo .= '<input type="hidden" name="item_name_'.$counter.'" value="'.htmlentities($row['slot_1'].' ('.str_replace("<br>",", ",substr($row['attributes'],0,-4))).')"><input type="hidden" name="amount_'.$counter.'" value="'.$row['unit_cost'].'"><input type="hidden" name="quantity_'.$counter.'" id="numo_paypal_quantity_'.$counter.'" value="'.$row['quantity'].'"><input type="hidden" name="item_number_'.$counter.'" value="'.$row['id'].'"><input type="hidden" name="shipping_'.$counter.'" value="'.$row['shipping'].'"><input type="hidden" name="shipping2_'.$counter.'" value="'.$row['shipping2'].'">';
				  $paypalItemInfo .= '<input type="hidden" name="shipping_'.$counter.'" value="'.$row['shipping'].'"><input type="hidden" name="shipping2_'.$counter.'" value="'.$row['shipping2'].'">';
				} else if ($row['slot_5'] == 1) {
				  $paypalItemInfo .= '<input type="hidden" name="weight_'.$counter.'" value="'.$row['shipping'].'"><input type="hidden" name="weight_unit_'.$counter.'" value="lbs">';
				}			
			
			}

			//update product cost
			$sql = "UPDATE `shopping_cart_order_items` SET `item_cost`='".$row['unit_cost']."' WHERE id='".$row['id']."'";
			//print $sql;
			$dbObj->query($sql);

			$unitCost = $row['unit_cost'];
			$row['unit_cost'] = $row['unit_cost'] * $quantityAvailable;
			$totalCost += $row['unit_cost'];
			if ($settings['tax_display_preference'] == 1|| ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
				$row['item_tax'] = $row['item_tax'] * $quantityAvailable;
				$totalCost += $row['item_tax'] * $quantityAvailable;
			}
$totalTax += $row['item_tax'];
			//update units for item in database to match the available units
			$sql = "UPDATE `shopping_cart_order_items` SET `quantity`='".$quantityAvailable."' WHERE `id`='".$row['id']."'";
			$dbObj->query($sql);

			print '<tr><td class="view_cart_contents_item_quantity"><input type="hidden" id="max_cart_item__'.$row['id'].'" value="'.$quantityAvailable.'" /><input type="text" id="cart_item__'.$row['id'].'" name="cart_item__'.$row['id'].'" onblur="updatePayPalQuantity(\''.$counter.'\',this);" value="'.$quantityAvailable.'" /><input type="submit" class="numo_submit_button" name="nonumo_cmd" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_UPDATE_BUTTON_LABEL.'" /></td><td class="view_cart_contents_item_info"><h4>'.$row['slot_1'].($row['slot_7'] != "" ? " #".$row['slot_7'] : "").'</h4><p>'.$row['attributes'].'</p><p class="shopping_cart_product_not_in_stock">'.str_replace("[quantity]",$quantityAvailable, NUMO_SYNTAX_SHOPPING_CART_FULL_QUANTITY_NOT_IN_STOCK_LABEL).'</p></td>';
			print '<td class="view_cart_contents_item_cost">'.($slots['2']['options'].number_format($unitCost, 2, '.', ',')).'</td>';
			print '<td class="view_cart_contents_item_cost">'.($slots['2']['options'].number_format($row['unit_cost'], 2, '.', ',')).'</td>';
			if ($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
			
			  print '<td class="view_cart_contents_item_quantity">';
			  if ($row['tax_rate'] > 0) {
				  print clean_num($row['tax_rate']).'%';
			  }
			  print '</td>';
			  print '<td class="view_cart_contents_item_quantity">';
			  if ($row['tax_rate'] > 0) {
				  print $slots['2']['options'].$row['item_tax'];
			  }
			  print '</td>';
			}
			
			print '</tr>';

			//increase paypal item counter
			$counter++;

		//there is enough product to cover order request
		} else {
			// if no stock used set shipping amount normally
			//print $stockUsed["$stockKey"];
			if($stockUsed[$stockKey] <= 1) {
				
				if ($row['slot_5'] == "0") {
				  $shippingCost += $row['shipping'] + ($row['shipping2'] * ($row['quantity'] - 1));
				} else if ($row['slot_5'] == "1") {
				  $shippingWeight += $row['shipping'] + ($row['shipping'] * ($row['quantity'] - 1));
				}
            //  print "shipping a";
			// if 1 or more units have been shown in the cart adjust shipping cost
			} else if ($row['slot_5'] == "0") {
				$shippingCost += $row['shipping'] + ($row['shipping2'] * ($row['quantity'] - 1));
				
				//$shippingCost += ($row['shipping2'] * $row['quantity']);
				// disabled this december 29th as it is setting the main shipping amount to the secondary shipping amount if quantity is greater than 1
				//$row['shipping'] = $row['shipping2'];
            //  print "shipping b";
			} else if ($row['slot_5'] == "1") {
				
				$shippingWeight += $row['shipping'] + ($row['shipping'] * ($row['quantity'] - 1));
			}
			//print $row['shipping'];
         
			//by default do not include any information about shipping ... let payapl settings handle shipping
			$paypalItemInfo .= '<input type="hidden" name="item_name_'.$counter.'" value="'.htmlentities($row['slot_1'].' ('.str_replace("<br>",", ",substr($row['attributes'],0,-4))).')'.($row['slot_7'] != "" ? " SKU #".$row['slot_7'] : "").'"><input type="hidden" name="amount_'.$counter.'" value="'.$row['unit_cost'].'"><input type="hidden" name="quantity_'.$counter.'" id="numo_paypal_quantity_'.$counter.'" value="'.$row['quantity'].'"><input type="hidden" name="item_number_'.$counter.'" value="'.$row['id'].'"><input type="hidden" name="tax_'.$counter.'" value="'.$row['item_tax'].'">';
 
			if($row['shipping'] > 0) {
				if ($row['slot_5'] == 0) {
				  //$paypalItemInfo .= '<input type="hidden" name="item_name_'.$counter.'" value="'.htmlentities($row['slot_1'].' ('.str_replace("<br>",", ",substr($row['attributes'],0,-4))).')"><input type="hidden" name="amount_'.$counter.'" value="'.$row['unit_cost'].'"><input type="hidden" name="quantity_'.$counter.'" id="numo_paypal_quantity_'.$counter.'" value="'.$row['quantity'].'"><input type="hidden" name="item_number_'.$counter.'" value="'.$row['id'].'"><input type="hidden" name="shipping_'.$counter.'" value="'.$row['shipping'].'"><input type="hidden" name="shipping2_'.$counter.'" value="'.$row['shipping2'].'">';
				  $paypalItemInfo .= '<input type="hidden" name="shipping_'.$counter.'" value="'.$row['shipping'].'"><input type="hidden" name="shipping2_'.$counter.'" value="'.$row['shipping2'].'">';
				} else if ($row['slot_5'] == 1) {
				  $paypalItemInfo .= '<input type="hidden" name="weight_'.$counter.'" value="'.$row['shipping'].'"><input type="hidden" name="weight_unit_'.$counter.'" value="lbs">'; 
				}
			} else {
			//	print "shipping is not greater than zero";
			}

			//update product cost
			$sql = "UPDATE `shopping_cart_order_items` SET `item_cost`='".$row['unit_cost']."' WHERE id='".$row['id']."'";
			//print $sql;
			$dbObj->query($sql);

			$unitCost = $row['unit_cost'];
			$row['unit_cost'] = $row['unit_cost'] * $row['quantity'];
			$totalCost += $row['unit_cost'];
			if ($settings['tax_display_preference'] == 1 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
				$row['item_tax'] = $row['item_tax'] * $row['quantity'];
				$totalCost += $row['item_tax'] * $row['quantity'];
				
			}
			$totalTax += $row['item_tax'];

			print '<tr><td class="view_cart_contents_item_quantity"><input type="hidden" id="max_cart_item__'.$row['id'].'" value="'.$unitsInStock.'" /><input type="text" id="cart_item__'.$row['id'].'" name="cart_item__'.$row['id'].'"  onblur="updatePayPalQuantity(\''.$counter.'\',this);" value="'.$row['quantity'].'" /><input type="submit" class="numo_submit_button" name="nonumo_cmd" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_UPDATE_BUTTON_LABEL.'" /></td>';
			print '<td class="view_cart_contents_item_info"><h4>'.$row['slot_1'].($row['slot_7'] != "" ? " #".$row['slot_7'] : "").'</h4><p>'.$row['attributes'].'</p></td><td class="view_cart_contents_item_cost">'.($slots['2']['options'].number_format($unitCost, 2, '.', ',')).'</td>';
		
			print '<td class="view_cart_contents_item_cost">'.($slots['2']['options'].number_format($row['unit_cost'], 2, '.', ',')).'</td>';
			if ($settings['tax_display_preference'] > 0|| ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
			  print '<td class="view_cart_contents_item_quantity">';
			  if ($row['tax_rate'] > 0) {
				  print clean_num($row['tax_rate']).'%';
			  }
			  print '</td>';
			  print '<td class="view_cart_contents_item_quantity">';
			  if ($row['tax_rate'] > 0) {
				  print $slots['2']['options'].$row['item_tax'];
			  }
			  print '</td>';
			} 	
			print '</tr>';

			//increase paypal item counter
			$counter++;
		}

		$stockUsed[$stockKey] += $row['quantity'];
	}
	/***************************/
 //   print "shipping cost $shippingCost<br>";
	if($shippingCost > 0) {
		$totalCost += $shippingCost;
		print '<tr><td colspan="3" style="text-align: right; font-weight: bold;">'.NUMO_SYNTAX_SHOPPING_CART_SHIPPING_LABEL.'</td><td class="view_cart_contents_item_cost" style="font-weight: bold;">'.($slots['2']['options'].number_format($shippingCost, 2, '.', ',')).'</td></tr>';
	} 
	

	print '<tr><td colspan="'.($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0) ? 3 : 3).'" style="text-align: right; font-weight: bold;">'.($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0) ? 'Pre Tax ' : '').NUMO_SYNTAX_SHOPPING_CART_TOTAL_LABEL.'</td><td class="view_cart_contents_item_cost" style="font-weight: bold;">'.($slots['2']['options'].number_format($totalCost-$totalTax, 2, '.', ',')).'</td></tr>';
			if ($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
				print "<tr>";
			    print '<td colspan="3" style="text-align: right; font-weight: bold;">TAX</td><td style="text-align: right; font-weight: bold;">'.number_format($totalTax, 2, '.', '').'</td>';
				print "</tr>";
				print '<tr><td colspan="3" style="text-align: right; font-weight: bold;">'.NUMO_SYNTAX_SHOPPING_CART_TOTAL_LABEL.'</td><td class="view_cart_contents_item_cost" style="font-weight: bold;">'.($slots['2']['options'].number_format($totalCost, 2, '.', ',')).'</td></tr>';
			// print '<td class="view_cart_contents_item_quantity">'.$slots['2']['options'].$itemTax.'</td>';
						 
			
			} 


	print '</table><input type="hidden" name="numo_cmd" value="update_cart_order" />';

		$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `account_id`='".$_SESSION['shopper_id']."'";
		$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		//if not logged in allow to login
		if(!isset($_SESSION['account_id'])) {
			print '</form><form method="post" style="display: inline; margin: 0px; padding: 0px;"><input type="submit" name="numo_cmdc" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_SHOPPING_LABEL.'" /><input type="submit" name="numo_cmdb" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_BUTTON_LABEL.'" /></form>';

		//if logged in create the paypal buy now button to checkout
		} else {
			print '</form><form method="post" style="display: inline; margin: 0px; padding: 0px;"><input type="submit" name="numo_cmdc" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_SHOPPING_LABEL.'" /></form>';

			$sql = "SELECT `request_shipping_details`,`paypal_email`,`store_mode` FROM `shopping_cart_settings` WHERE `site_id`='".NUMO_SITE_ID."'";
			$settings = $dbObj->query($sql);

			if($setting = mysql_fetch_array($settings)) {
				if($setting['store_mode'] == 1) {
					print '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return validateQuantities()">';
				} else {
					print '<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;"onsubmit="return validateQuantities()">';
				}

				 print '<input type="hidden" name="cmd" value="_cart"><input type="hidden" name="upload" value="1"><input type="hidden" name="invoice" value="'.$orderId.'"><input type="hidden" name="business" value="'.$setting['paypal_email'].'">'.$paypalItemInfo.'<input type="hidden" name="currency_code" value="'.$slots['2']['code'].'" />';

				if($setting['request_shipping_details'] == '1') {
					print '<input type="hidden" name="no_shipping" value="2">';
				} else {
					print '<input type="hidden" name="no_shipping" value="1">';
				}

				print '<input type="submit" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_BUTTON_LABEL.'"></form>';
			}
		}
	}

/*************************/
/***  PRODUCT DISPLAY  ***/
/*************************/
} else if(is_numeric($_GET['pid'])) {
	$sql = "SELECT p.*, (SELECT i.`file_name` FROM `shopping_cart_product_images` i WHERE i.`listing_id`=p.`id` ORDER BY i.`id` asc LIMIT 1) as image_name, (SELECT i.`description` FROM `shopping_cart_product_images` i WHERE i.`listing_id`=p.`id` AND i.file_name=image_name ORDER BY i.`id` asc LIMIT 1) as image_description FROM `shopping_cart_products` p WHERE p.`status`=1 AND p.`id`='".$_GET['pid']."' AND p.`site_id`='".NUMO_SITE_ID."' ORDER BY id";
	//print $sql;
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {

?>
<script type="text/javascript" src="<?=NUMO_FOLDER_PATH.'modules/shopping_cart/javascript/calendarDateInput.js'?>"></script>
<script>
function validate(theForm) {
  var requiredFields = theForm['required[]'];
  if (!requiredFields) {
    return true;
  } else {
    for (value in requiredFields) {
      var myField = requiredFields[value];
      if (myField.name == "required[]") {
        var myFieldValue = myField.value;
        var myRequiredField = theForm[myFieldValue];
        if (myRequiredField.value == "") {
          var fieldName = document.getElementById(myRequiredField.name + "__fieldName").innerHTML.replace(":", "");
          alert("You must enter a value for the " + fieldName);
          return false;
        }
      }
    }
  }
  return true;
}
</script>
	<table border="0">
		<tr>
			<td style="padding: 0px 40px 0px 0px; text-align: center; vertical-align: top">
				<a href="<?=str_replace('/numo/','',NUMO_FOLDER_PATH)?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>" onclick="window.open('<?=str_replace('/numo/','',NUMO_FOLDER_PATH)?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>','<?=$_GET['pid']?>','location=0,status=0,scrollbars=1,width=650,height=500'); return false;"><img class="product_thumbnail_large" src="<?=NUMO_FOLDER_PATH.'modules/shopping_cart/'.(is_null($row['image_name']) ? 'images/coming_soon_sm.jpg' : 'uploads/'.$row['image_name'])?>" alt="<?=$row['image_description']?>" title="<?=$row['image_description']?>" /></a>
				<?
				if(!is_null($row['image_name'])) {
				?>
				<a href="<?=str_replace('/numo/','',NUMO_FOLDER_PATH)?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>" onclick="window.open('<?=str_replace('/numo/','',NUMO_FOLDER_PATH)?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>','<?=$_GET['pid']?>','location=0,status=0,scrollbars=1,width=650,height=500'); return false;">View Larger</a>
				<?php
				}
				?>
			</td>

			<td width="100%">
			<h3 class='numo_shopping_cart_product_name'><?=$row['slot_1']?></h3>
                          <?php if ($row['slot_7'] != "") { ?>
                <p class='product_sku'><?php print $slots['7']['name'].': '.$row['slot_7']?></p>
              <?php } ?>  
			<p style="font-size: 14px;"><?=$slots['2']['name'].": "?>
			<? if (is_numeric($row['slot_2'])) {
				/*
				 print $slots['2']['options'];
				 // show price AND the tax
				 if ($settings['tax_display_preference'] == 1) {
					    print number_format($row['slot_2'], 2, '.', ',');
						
						$taxRateID = $row['slot_8'];
						if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
							print "<br>Plus ".$taxRates["{$taxRateID}"]["rate_name"]." (".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."%): ";
							print $slots['2']['options'].number_format($row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
						}
					   // print "should price and vat";
				 
				 // show the price including the tax 
				 } else if ($settings['tax_display_preference'] == 2) {
						
						$taxRateID = $row['slot_8'];
						if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
							print number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');

							print "<br>Includes ".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."% ".$taxRates["{$taxRateID}"]["rate_name"];
						} else {
					    	print number_format($row['slot_2'], 2, '.', ',');
							
						}
						
				 } else {
					print number_format($row['slot_2'], 2, '.', ',');
				 }
				 */
					 $afterDiscount = $row['slot_2'] - ($row['slot_2'] * ($shoppingCartDiscount / 100));
					 // show price AND the tax
					 if ($settings['tax_display_preference'] == 1) {
							if ($shoppingCartDiscount > 0) {
								if ($showOriginalPrice) { 
								  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span>";
								}
								print " <span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
								$row['slot_2'] = $afterDiscount;
							} else {
								print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
							}
							
							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								print "<br>Plus ".$taxRates["{$taxRateID}"]["rate_name"]." (".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."%): ";
								print $slots['2']['options'].number_format($row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
							}
						   // print "should price and vat";
					 
					 // show the price including the tax 
					 } else if ($settings['tax_display_preference'] == 2) {
							
							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								if ($shoppingCartDiscount > 0) {
									$afterDiscount = number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') - (number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') * $shoppingCartDiscount / 100);
									if ($showOriginalPrice) { 
									  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',')."</span>";
									}
									print " <span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
									$row['slot_2'] = $afterDiscount;
								} else {
									print $slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
								}
								
								//print $slots['2']['options'].;
	
								print "<br>Includes ".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."% ".$taxRates["{$taxRateID}"]["rate_name"];
							} else {
								if ($shoppingCartDiscount > 0) {
									if ($showOriginalPrice) { 
									  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
									}
									print "<span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
								} else {
									print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
								}
								
								//print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
								
							}
							
					 } else {
						if ($shoppingCartDiscount > 0) {
							if ($showOriginalPrice) { 
							  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
							}
							print "<span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
						} else {
							print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
						}
						
					 }
				} else {
					print 'N/A';
				}          
			?>
        
            </p>

			<form method="post" onsubmit="return validate(this)">
			<?php
			//load field information for accounts group
			$sql = "SELECT * FROM `shopping_cart_optional_product_attributes` WHERE `product_id`='".$_GET['pid']."' ORDER BY `position`,`label`";
			//print $sql."<br>";
			$results = $dbObj->query($sql);

			if(mysql_num_rows($results) > 0) {
				print '<table class="numo_product_purchase_options">';

				while($field = mysql_fetch_array($results)) {

					print '<tr>';

					if ($field['type'] == 'section break') {
					  print '<td colspan="2">';

					} else {
					  print '<td id="attr__'.$field['id'].'__fieldName" style="font-size: 13px; vertical-align: top; padding-top: 3px;">'.$field['label'].': </td><td>';
					}

					if($field['type'] == "dropdown list") {
						//load field information for accounts group
						$sql = "SELECT id,label,cost FROM `shopping_cart_optional_product_attribute_options` WHERE `attribute_id`='".$field['id']."' AND `status`=1 ORDER BY `id`";
						//print $sql."<br>";
						$optionals = $dbObj->query($sql);

						print '<select name="attr__'.$field['id'].'">';

						while($option = mysql_fetch_array($optionals)) {
							print '<option value="'.$option['id'].'">'.$option['label'];

							if($option['cost'] > 0) {
								print ' (+'.($slots['2']['options'].number_format($option['cost'], 2, '.', ',')).')';
							}

							print '</option>';
						}

						print '</select>';

						mysql_free_result($optionals);
					} else if ($field['type'] == "text area") {
					?>
					<textarea name="attr__<?=$field['id']?>"></textarea>
					<?php
					} else if ($field['type'] == "date") {
					?>
					<script>DateInput("attr__<?=$field['id']?>", true, "YYYY-MM-DD", "<?=date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1))?>")</script>
					<?php
					} else if ($field['type'] == "section break") {
					?>
					<div class="product_catalog_display_section_break"></div>
					<?php
					} else {
					?>
						<input class='attr' type="text" name="attr__<?=$field['id']?>" value="" />
					<?php
					}
                    if ($field['required'] == 1) {
                      print "<input type='hidden' name='required[]' value='attr__{$field['id']}' />";
                    }
					print '</td></tr>';
				}
				print '<tr><td colspan="2" style="text-align: right;"><input type="submit" class="product_catalog_display_price_box" name="nonumo_cmd" value="'.NUMO_SYNTAX_SHOPPING_CART_BUY_NOW_LABEL.'" /></td></tr>';
				print '</table>';
			} else {
			?>
			<input type="submit" class="product_catalog_display_price_box" name="nonumo_cmd" value="<?=NUMO_SYNTAX_SHOPPING_CART_BUY_NOW_LABEL?>" />
			<?php
			}

			mysql_free_result($results);
			?>

			<input type="hidden" name="product_id" value="<?=$_GET['pid']?>" />
			<input type="hidden" name="numo_cmd" value="add_to_cart" />
			</form>
			</td>
		</tr>
		<tr>
			<td colspan="2"><h4><?=$slots['3']['name']?></h4><p><?php if (stristr($row['slot_3'], "<tabl")) { print $row['slot_3']; } else { print nl2br($row['slot_3']); } ?></p></td>
		</tr>
        <?php if ($row['slot_4'] != "") { ?>
		<tr>
			<td colspan="2"><h4><?=$slots['4']['name']?></h4><p><?php if (stristr($row['slot_4'], "<tabl")) { print $row['slot_4']; } else { print nl2br($row['slot_4']); } ?></p></td>
		</tr>
        
        <?php } ?>
	</table>
<?php
	}
} else {
	$pageNumber = 0;
	$itemsPerPage = 10;
	$searchTerms = "";

	if(isset($_GET['page']) && is_numeric($_GET['page'])) {
		$pageNumber = $_GET['page'];
	}

	$startPosition = $pageNumber * $itemsPerPage;

	$whereStr = "";

	if(isset($_POST['search_terms'])) {
		$whereStr = "AND (`slot_1` LIKE '%".$_POST['search_terms']."%' OR `slot_3` LIKE '%".$_POST['search_terms']."%')";
	} else if(isset($_GET['search_terms'])) {
		$whereStr = "AND (`slot_1` LIKE '%".$_GET['search_terms']."%' OR `slot_3` LIKE '%".$_GET['search_terms']."%')";
	} else if(isset($_GET['cid']) && is_numeric($_GET['cid'])) {
		//$whereStr = "AND id=c.product_id AND c.category_id='".$_GET['cid']."'";
		$whereStr = "AND p.id=(SELECT product_id FROM `shopping_cart_product_categories` WHERE category_id='".$_GET['cid']."' AND product_id=p.id)";
	} else if(isset($PARAMS['cid'])) {
		$whereStr = "AND p.id=(SELECT product_id FROM `shopping_cart_product_categories` WHERE category_id='".$PARAMS['cid']."' AND product_id=p.id)";
	}
	// && $_SESSION['pending'] == 0 && $_SESSION['activated'] == 1
	
	// need to implment search terms in the future
	
	if ($settings['catalog_visibility'] == "1") {
	    if(isset($_GET['cid']) && is_numeric($_GET['cid'])) {
			//$whereStr = "AND id=c.product_id AND c.category_id='".$_GET['cid']."'";
			$whereStr = "AND p.id=(SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp  WHERE cp.category_id=pc.category_id AND cp.category_id='".$_GET['cid']."' AND product_id=p.id AND cp.account_type_id='".$_SESSION['type_id']."')";
		} else if(isset($PARAMS['cid'])) {
			$whereStr = "AND p.id=(SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp  WHERE cp.category_id=pc.category_id AND cp.category_id='".$PARAMS['cid']."'  AND product_id=p.id ANDcp. account_type_id='".$_SESSION['type_id']."')";
		} else {
			$whereStr = "AND p.id=(SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp WHERE cp.category_id=pc.category_id AND product_id=p.id AND cp.account_type_id='".$_SESSION['type_id']."')";
		
		}
	}

	$productCount = 0;
    
		$sql = "SELECT COUNT(*) as 'count' FROM `shopping_cart_products` p WHERE `status`=1 ".$whereStr." AND `site_id`='".NUMO_SITE_ID."'";
		//print $sql;
		$result = $dbObj->query($sql);
	
		if($row = mysql_fetch_array($result)) {
			$productCount = $row['count'];
		} 
	
		$sql = "SELECT p.*,(SELECT COUNT(*) FROM `shopping_cart_optional_product_attributes` WHERE `product_id`=p.`id`) as product_attrs FROM `shopping_cart_products` p  WHERE p.`status`=1 AND p.`site_id`='".NUMO_SITE_ID."' ".$whereStr." ORDER BY p.`slot_1` LIMIT ".$startPosition.",".$itemsPerPage;
	   // SELECT p.*, i.file_name as 'image_name', i.description as 'image_description', (SELECT COUNT(*) FROM `shopping_cart_optional_product_attributes` WHERE `product_id`=p.`id`) as product_attrs FROM `shopping_cart_products` p LEFT JOIN (SELECT y.* FROM `shopping_cart_product_images` y INNER JOIN (SELECT * FROM shopping_cart_product_images ORDER BY id ASC) x ON (y.id=x.listing_id) GROUP BY y.listing_id) i ON (p.`id`=i.`listing_id`) WHERE p.`status`=1 AND p.`site_id`='1' ORDER BY p.`slot_1` LIMIT 0,10print $sql; 
		//print $sql;
		$results = $dbObj->query($sql);
	
		if(mysql_num_rows($results) > 0) {
			//$productCount = 0;
		?>
		<table class="product_catalog_display">
		<?php
	
				while($row = mysql_fetch_array($results)) {
					//$productCount = $row['product_count'];
					$imageQuery = "SELECT * FROM `shopping_cart_product_images` WHERE listing_id='{$row['id']}' ORDER BY id ASC LIMIT 1";
				//	print $imageQuery;
					$imageResult = $dbObj->query($imageQuery);
					$imageRow = mysql_fetch_array($imageResult);
				?>
				<tr>
				  <td>
	
				  <a href="?pid=<?=$row['id'].$getStringValues?>"><img class="product_thumbnail" src="<?=NUMO_FOLDER_PATH.'modules/shopping_cart/'.(is_null($imageRow['file_name']) ? 'images/coming_soon_sm.jpg' : 'uploads/'.$imageRow['file_name'])?>" alt="<?=htmlentities($imageRow['description'])?>" title="<?=htmlentities($imageRow['description'])?>" /></a></td>
				<td>
			  
				<a href="?pid=<?=$row['id'].$getStringValues?>"><?=$row['slot_1']?></a>
							  <?php if ($row['slot_7'] != "") { ?>
					<p class='product_sku'><?php print $row['slot_7']?></p>
				  <?php } ?>  
				<p style="font-size: 14px">
				<?=($slots['2']['name'].": ")?>
				<? if (is_numeric($row['slot_2'])) {
					 $afterDiscount = $row['slot_2'] - ($row['slot_2'] * ($shoppingCartDiscount / 100));
					 // show price AND the tax
					 if ($settings['tax_display_preference'] == 1) {
							if ($shoppingCartDiscount > 0) {
								if ($showOriginalPrice) { 
								  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span>";
								}
								print " <span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
								$row['slot_2'] = $afterDiscount;
							} else {
								print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
							}
							
							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								print "<br>Plus ".$taxRates["{$taxRateID}"]["rate_name"]." (".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."%): ";
								print $slots['2']['options'].number_format($row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
							}
						   // print "should price and vat";
					 
					 // show the price including the tax 
					 } else if ($settings['tax_display_preference'] == 2) {
							
							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								if ($shoppingCartDiscount > 0) {
									$afterDiscount = number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') - (number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') * $shoppingCartDiscount / 100);
									if ($showOriginalPrice) { 
									  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',')."</span>";
									}
									print " <span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
									$row['slot_2'] = $afterDiscount;
								} else {
									print $slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
								}
								
								//print $slots['2']['options'].;
	
								print "<br>Includes ".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."% ".$taxRates["{$taxRateID}"]["rate_name"];
							} else {
								if ($shoppingCartDiscount > 0) {
									if ($showOriginalPrice) { 
									  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
									}
									print "<span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
								} else {
									print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
								}
								
								//print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
								
							}
							
					 } else {
						if ($shoppingCartDiscount > 0) {
							if ($showOriginalPrice) { 
							  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
							}
							print "<span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
						} else {
							print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
						}
						
					 }
				} else {
					print 'N/A';
				}
				?></p><p style="font-size: 13px"><?=display_shopping_product_description($row['slot_3'])?>&nbsp;&nbsp;
				<br/><a style="font-weight: normal;" href="?pid=<?=$row['id'].$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_MORE_PRODUCT_DETAILS_LABEL?></a></p></td>
				<?php
				//direct to the product details page
				if($row['product_attrs'] > 0) {
				?>
				<td style="width: 150px; vertical-align:middle; text-align: right;">
				 
				<a href="?pid=<?=$row['id'].$getStringValues?>" style="color: #fff; text-decoration: none;" class="product_catalog_display_price_box"><?=NUMO_SYNTAX_SHOPPING_CART_BUY_NOW_LABEL?></a></td></tr>
				<?php
				//add item directly to cart
				} else {
				?>
				<td style="width: 150px; vertical-align:middle; text-align: right;">
				
				<a href="?product_id=<?=$row['id'].'&numo_cmd=add_to_cart'.$getStringValues?>" style="color: #fff; text-decoration: none;" class="product_catalog_display_price_box"><?=NUMO_SYNTAX_SHOPPING_CART_BUY_NOW_LABEL?></a></td></tr>
				<?php
				}
				?>
				<?php
				}
	
	
		?>
		<tr><td colspan="3" style="text-align: center;" class="numo_catalog_back_next"><?php if($pageNumber > 0) { ?><a href="?page=<?=($pageNumber - 1).$searchTerms.$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_CATALOG_BACK_LINK_LABEL?></a><?php } ?> <?php if($pageNumber > 0 && ($startPosition + $itemsPerPage)  < $productCount) { ?> | <?php } ?> <?php if(($startPosition + $itemsPerPage) < $productCount) { ?> <a href="?page=<?=($pageNumber + 1).$searchTerms.$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_CATALOG_NEXT_LINK_LABEL?></a><?php } ?></td></tr>
		</table>
		<?php
		} else {
			print "<p>";
			if ($settings['catalog_visibility'] == "0") {
			  print NUMO_SYNTAX_SHOPPING_CART_NO_PRODUCTS_MESSAGE;
			} else {
				print NUMO_SYNTAX_SHOPPING_CART_RESTRICTED_MESSAGE;
					
			}
			print "</p>";
		}

}
function clean_num($num){
                return trim(trim($num, '0'), '.');
        }
?>