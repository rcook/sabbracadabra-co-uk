<?php
if (!function_exists("clean_num")) {
function clean_num($num){
                return trim(trim($num, '0'), '.');
        }
}

//unset($_SESSION['shopper_id']);
	include_once("numo/modules/shopping_cart/classes/Discount.php");
	$sql = "SELECT * FROM `shopping_cart_settings` WHERE `site_id`='".NUMO_SITE_ID."'";
	$settings = $dbObj->query($sql);
	$settings = mysql_fetch_array($settings);
	if ($_GET['numo'] == "shopping-cart" && $_GET['cid'] != "") {
		$_GET['pid'] = "";
		//print "yup";
	}
	
	if ($PARAMS['pid'] != "") {
		$_GET['pid'] = $PARAMS['pid'];
	}
	
	if ($settings['show_breadcrumb'] == "" || $settings['show_breadcrumb'] == 1) {
	include("breadcrumb.php");
	}

 // print "<pre>";
//  print var_dump($_POST);
// print "</pre>";

// print "<pre>";
 // print var_dump($_GET);
 // print "</pre>";

   //print $_SERVER['REQUEST_URI'];
   //phpinfo();
    $sql = "SELECT shopping_cart_discount, show_original_price FROM types WHERE site_id='".NUMO_SITE_ID."' AND id='{$_SESSION['type_id']}'";
	$discountResult = $dbObj->query($sql);
    $shoppingCartDiscount = mysql_fetch_array($discountResult);
	$showOriginalPrice   = ($shoppingCartDiscount['show_original_price'] == 1);
	$shoppingCartDiscount = number_format($shoppingCartDiscount['shopping_cart_discount'], 2, '.', '');
	$dbObj->query("SET NAMES UTF8");


	if (REMOTE_SERVICE === true) {
		$_GET = array_merge($_GET, $PARAMS);
	    $_POST = array_merge($_POST, $PARAMS);
	}
	//foreach ($_SERVER as $x => $y) {
		//print "$x = $y <br>";
	//}
	//print "post<br>";
	foreach ($_POST as $x => $y) {
	//	print "P $x = $y <br>";
	}

	foreach ($_GET as $x => $y) {
		//print "G $x = $y <br>";
	}
	if ($_POST['return_page'] == "" && $_GET['return_page'] == "" && $_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI']) {
	//	print "x";
	    if (REMOTE_SERVICE == true) {
			//if (DIRECT_PROCESSING === true) {
		  //    $settings['return_page'] = "http://".$numo->getRootFolder(true, true)."?module=shopping_cart&component=catalog&cid={$_GET['cid']}";
			//} else {
		      $settings['return_page'] = $MANAGE_NUMO_LOCATION."?module=shopping_cart&component=catalog&cid={$_GET['cid']}";
			//}
		} else {
	      $settings['return_page'] = str_replace("&view=cart", "", $_SERVER['HTTP_REFERER']);
		  $settings['return_page'] = preg_replace('/&return_page=([^&]*?)/', '', $settings['return_page']);
		}
	} else if ($_POST['return_page'] != "") {
	//	print "y";
		$settings['return_page'] =  htmlspecialchars_decode(urldecode($_POST['return_page']));
	} else if ($_GET['return_page'] != "") {
	//	print "z";
	//	print $_GET['return_page']."<br>";
		$settings['return_page'] = htmlspecialchars_decode(urldecode($_GET['return_page']));
	//	print $settings['return_page']."<br>";
	} else {
	//  print "v";
	  // added December 31, 2012 (possible side effects, but required for pages such as mycatalog.htm page
		  $settings['return_page'] = $_SERVER['HTTP_REFERER'];

	}

	//print "settings: ".$settings['return_page']."<br>";
	//exit;
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

if($_POST['numo_cmd'] == "update_cart_order" ) {
	
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
// update when we checkout or continue
} else if ($_GET['method'] == "paypal" || $_POST['numo_cmdb'] == NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_SHOPPING_LABEL) {
	foreach($_POST as $key => $value) {
		
		if(substr($key, 0, 12) == "item_number_" && is_numeric($value)) {
			$itemNumber = substr($key, 12);
			$key = $value;
			$quantity = $_POST["quantity_{$itemNumber}"];
			
			//if no units wanted remove from cart
			if($quantity == "0") {
				$sql = "DELETE FROM `shopping_cart_order_items` WHERE `id`='".$key."'";
				//print $sql;
				$dbObj->query($sql);

				$sql = "DELETE FROM `shopping_cart_order_item_attributes` WHERE `order_item_id`='".$key."'";
				//print $sql;
				$dbObj->query($sql);

			} else {
				$sql = "UPDATE `shopping_cart_order_items` SET `quantity`='".$quantity."' WHERE `id`='".$key."'";
				//print $sql."<br>";
				$dbObj->query($sql);
			}
		}
	}	
}

if($_POST['cmd'] == "create" && !isset($_SESSION['account_id'])) {
	print "[NUMO.SHOPPING CART: REGISTER]";
}

if ($_GET['method'] == "choose") {
	$_GET['view'] = "";
	$PARAMS['view'] = "";
	?>
    <link rel="stylesheet" type="text/css" href="//<?php print ($_SERVER['HTTPS'] == "on" ? NUMO_SECURE_ADDRESS : NUMO_SERVER_ADDRESS).NUMO_FOLDER_PATH; ?>modules/shopping_cart/components/styles/catalog.css" />
<div id='choose-payment-method'>
<h2><?php echo NUMO_SYNTAX_SHOPPING_CART_CHOOSE_PAYMENT_METHOD_LABEL; ?></h2>
<ul id='checkout-buttons'>
<?php if ($settings['store_mode'] == "1") { ?>
<li><FORM id="paypalsubmitform" name="paypalsubmit" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;"><input class='btn' type='submit' value="<?php echo NUMO_SYNTAX_SHOPPING_CART_PAY_VIA_PAYPAL_LABEL; ?>" />
<?php foreach ($_POST as $key => $value) { ?>
<input type='hidden' name="<?=$key?>" value="<?=$value?>" />
<?php } ?>
</FORM></li>
<?php } else if ($settings['store_mode'] == "0") { ?>
<li><FORM id="paypalsubmitform" name="paypalsubmit" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;"><input class='btn' type='submit' value="<?php echo NUMO_SYNTAX_SHOPPING_CART_PAY_VIA_PAYPAL_LABEL; ?> (TEST MODE)" />
<?php foreach ($_POST as $key => $value) { ?>
<input type='hidden' name="<?=$key?>" value="<?=$value?>" />
<?php } ?>
</FORM></li>
<?php } ?>
<?php if ($settings['store_mode_order_collection_on'] == 1) { 
$offlinePaymentTypes = explode(",", $settings['offline_payment_types']);
foreach ($offlinePaymentTypes as $paymentType) {
?>
<li><FORM action="?module=shopping_cart&component=catalog&method=manual&type=<?php echo $paymentType;?>" method="post" style="display: inline; margin: 0px; padding: 0px;">

<input type='submit' class='btn' value="<?php
switch ($paymentType) {
	case 'credit_card': print NUMO_SYNTAX_SHOPPING_CART_PAY_OFFLINE_VIA_CREDIT_CARD_LABEL; break;
	case 'invoice': print NUMO_SYNTAX_SHOPPING_CART_PAY_OFFLINE_VIA_INVOICE_LABEL; break;
	case 'purchase_order': print NUMO_SYNTAX_SHOPPING_CART_PAY_OFFLINE_VIA_PURCHASE_ORDER_LABEL; break;
	case 'check': print NUMO_SYNTAX_SHOPPING_CART_PAY_OFFLINE_VIA_CHECK_LABEL; break;
}
	?>" />
<?php foreach ($_POST as $key => $value) { ?>
<input type='hidden' name="<?=$key?>" value="<?=$value?>" />
<?php } ?>
</FORM></li>
<?php } ?>
<?php } ?>
</ul>
</div>
<?php	
 return;
} else if ($_GET['method'] == "manual") {

	include_once("numo/modules/shopping_cart/classes/Order.php");
	$order = new Order($_POST['invoice']);

	?>    <link rel="stylesheet" type="text/css" href="//<?php print ($_SERVER['HTTPS'] == "on" ? NUMO_SECURE_ADDRESS : NUMO_SERVER_ADDRESS).NUMO_FOLDER_PATH; ?>modules/shopping_cart/components/styles/catalog.css" />
<?php
 
  if ($settings['offline_collect_billing_address'] == 1 && $_POST['manual-cmd'] != "") {
	  $order->processBillingForm($_POST);
	  $billingAddressFormErrors = sizeof($order->billingFormErrors);
	  
	  
  }
  if ($settings['offline_collect_shipping_address'] == 1 && $_POST['manual-cmd'] != "") {
	  $order->processShippingForm($_POST);
	  $shippingAddressFormErrors = sizeof($order->shippingFormErrors);
  }
  
  if ($_GET['type'] == "credit_card" && $_POST['manual-cmd'] != "") {
	  $order->processCreditCardForm($_POST);
	  	  
  } else if ($_GET['type'] == "purchase_order") {
	  $order->processPurchasOrderForm($_POST);
  
  }
	
	
  // process order	
  if ($_POST['manual-cmd'] == "Complete" && 
	  (($settings['offline_collect_billing_address'] == 0 || sizeof($order->billingFormErrors) == 0) 
		&& 
	   ($settings['offline_collect_shipping_address'] == 0 || sizeof($order->shippingFormErrors) == 0)
	   &&
	   !($_GET['type'] == "credit_card" && sizeof($order->creditCardFormErrors) > 0)
	   )) {
	  //print "shippingAddressFormErrors: $shippingAddressFormErrors";
	  $order = new Order($_POST['invoice']);
	  $_POST['type'] = $_GET['type'];
	  $_POST['method'] = $_GET['method'];
	  $order->process("manual", $_POST);
	  ?>
  <div class='notice'>
    <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_ORDER_COLLECTION_DONE; ?></h4>
    <p>
<?php 

	  $paymentTypeInstructions = "";


if ($_GET['type'] == "credit_card") { 
				  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_DETAILS_DESCRIPTION;
  
	  } else if ($_GET['type'] == "invoice") { 
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CART_INVOICE_DETAILS_DESCRIPTION;
		  
	  } else if ($_GET['type'] == "purchase_order") { 
		  		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CART_PURCHASE_ORDER_DETAILS_DESCRIPTION;

	  } else if ($_GET['type'] == "check") { 
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CART_CHECK_DETAILS_DESCRIPTION;
		  
	  } 
	  
	  $paymentTypeInstructions = str_replace("[Company Address]", $order->attributes['packing_slip_address'], $paymentTypeInstructions);	  
	  $paymentTypeInstructions = str_replace("[Order Total]", $order->currencyOptionsShort["{$order->attributes['cart_currency']}"].number_format($order->attributes['mc_gross'], '2', '.', ''), $paymentTypeInstructions);	  
	  
	  print $paymentTypeInstructions;
	  ?>

      </p>
   </div>     
   <?php
  
  } else {
	  ?>
      <form class='form-horizontal' role='form' method="post" method='?module=shopping_cart&component=catalog&method=manual&type=<?php echo $_GET['type'];?>'>
		 <?php foreach ($_POST as $key => $value) { ?>
        <input type='hidden' name="<?=$key?>" value="<?=$value?>" />
        <?php } ?>
        <?php if ($_POST['manual-cmd'] == "" && $order->attributes['payment_type'] != "Manual/".ucwords(str_replace("_", " ", $_GET['type']))) {
			//print "yup";
      	  if ($order->attributes['send_admin_email_order_pending'] == 1) {
			 // print "yuuup";
	        $order->setOrderPaymentMethod("Manual/".ucwords(str_replace("_", " ", $_GET['type'])));
	        $order->sendAdminNotificationOfPendingOrder();
	      }
	     } 
		 
		  if ($_POST['manual-cmd'] == "") { ?>
          <input type='hidden' name='retry-trace' value='<?php echo number_format(array_sum(explode(' ', microtime())), 4, '.', ''); ?>' />
          <?php } else { ?>
           <input type='hidden' name='retry-trace' value='<?php echo $_POST['retry-trace']; ?>' /> 
         
          <?php } ?>
      <?php
  if ($settings['offline_collect_shipping_address'] == 1) {

      if (sizeof($order->shippingFormErrors) > 0 && is_array($order->shippingFormErrors)) {
      //var_dump($order->shippingFormErrors);
      }

	  ?>
      <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_SHIPPING_DETAILS_LABEL; ?></h4>
	  <p><?php echo NUMO_SYNTAX_SHOPPING_CART_SHIPPING_DETAILS_DESCRIPTION; ?></p>
       <ul class='address-section'> 
        <li <?php if ($order->shippingFormErrors["shipping_attention"]) { print "class='error'"; } ?>><label for='shipping_attention'><?php echo NUMO_SYNTAX_SHOPPING_CART_ATTENTION_LABEL; ?></label><input value="<?php echo $_POST['shipping_attention']; ?>" type='text' name='shipping_attention' id='shipping_attention' /></li>
        <li <?php if ($order->shippingFormErrors["shipping_street_address"]) { print "class='error'"; } ?>><label for='shipping_street_address'><?php echo NUMO_SYNTAX_SHOPPING_CART_STREET_ADDRESS_LABEL; ?></label><input value="<?php echo $_POST['shipping_street_address']; ?>" type='text' name='shipping_street_address' id='shipping_street_address' /></li>
        <li <?php if ($order->shippingFormErrors["shipping_city"]) { print "class='error'"; } ?>><label for='shipping_city'><?php echo NUMO_SYNTAX_SHOPPING_CART_CITY_LABEL; ?></label><input  value="<?php echo $_POST['shipping_city']; ?>" type='text' name='shipping_city' id='shipping_city' /></li>
        <li <?php if ($order->shippingFormErrors["shipping_zip"]) { print "class='error'"; } ?>><label for='shipping_zip'><?php echo NUMO_SYNTAX_SHOPPING_CART_ZIP_LABEL; ?></label><input value="<?php echo $_POST['shipping_zip']; ?>"  type='text' name='shipping_zip' id='shipping_zip' /></li>
        <li id='shipping_state_li' <?php if ($order->shippingFormErrors["shipping_state"]) { print "class='error'"; } ?>><label for='shipping_state'><?php echo NUMO_SYNTAX_SHOPPING_CART_STATE_LABEL; ?></label><select name='shipping_state' id='shipping_state'><?php echo generate_state_province_options(NUMO_SYNTAX_SHOPPING_CART_STATE_LABEL, $_POST['shipping_state']); ?></select></li>
        <li <?php if ($order->shippingFormErrors["shipping_country"]) { print "class='error'"; } ?>><label for='shipping_country'><?php echo NUMO_SYNTAX_SHOPPING_CART_COUNTRY_LABEL; ?></label><select onchange='modifyStateDropdown(this, "shipping_state", "shipping")'  name='shipping_country' id='shipping_country' /><?php echo generate_country_options(NUMO_SYNTAX_SHOPPING_CART_COUNTRY_LABEL, $_POST['shipping_country']); ?></select></li>
      </ul>     <?php
  }
	  
  if ($settings['offline_collect_billing_address'] == 1) {

      if (sizeof($order->billingFormErrors) > 0 && is_array($order->billingFormErrors)) {
      //var_dump($order->billingFormErrors);
      }

	  ?>
      
      <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_BILLING_DETAILS_LABEL; ?></h4>
	  <p><?php echo NUMO_SYNTAX_SHOPPING_CART_BILLING_DETAILS_DESCRIPTION; ?></p>
     
      <ul class='address-section'> 
        <li <?php if ($order->billingFormErrors["billing_attention"]) { print "class='error'"; } ?>><label for='billing_attention'><?php echo NUMO_SYNTAX_SHOPPING_CART_ATTENTION_LABEL; ?></label><input  value="<?php echo $_POST['billing_attention']; ?>" type='text' name='billing_attention' id='billing_attention' /></li>
        <li <?php if ($order->billingFormErrors["billing_street_address"]) { print "class='error'"; } ?>><label for='billing_street_address'><?php echo NUMO_SYNTAX_SHOPPING_CART_STREET_ADDRESS_LABEL; ?></label><input  value="<?php echo $_POST['billing_street_address']; ?>" type='text' name='billing_street_address' id='billing_street_address' /></li>
        <li <?php if ($order->billingFormErrors["billing_city"]) { print "class='error'"; } ?>><label for='billing_city'><?php echo NUMO_SYNTAX_SHOPPING_CART_CITY_LABEL; ?></label><input value="<?php echo $_POST['billing_city']; ?>" type='text' name='billing_city' id='billing_city' /></li>
        <li <?php if ($order->billingFormErrors["billing_zip"]) { print "class='error'"; } ?>><label for='billing_zip'><?php echo NUMO_SYNTAX_SHOPPING_CART_ZIP_LABEL; ?></label><input value="<?php echo $_POST['billing_zip']; ?>" type='text' name='billing_zip' id='billing_zip' /></li>
        <li id='billing_state_li' <?php if ($order->billingFormErrors["billing_state"]) { print "class='error'"; } ?>><label for='billing_state'><?php echo NUMO_SYNTAX_SHOPPING_CART_STATE_LABEL; ?></label><select name='billing_state' id='billing_state'><?php echo generate_state_province_options(NUMO_SYNTAX_SHOPPING_CART_STATE_LABEL, $_POST['billing_state']); ?></select></li>
        <li <?php if ($order->billingFormErrors["billing_country"]) { print "class='error'"; } ?>><label for='billing_country'><?php echo NUMO_SYNTAX_SHOPPING_CART_COUNTRY_LABEL; ?></label><select onchange='modifyStateDropdown(this, "billing_state", "billing")' name='billing_country' id='billing_country' /><?php echo generate_country_options(NUMO_SYNTAX_SHOPPING_CART_COUNTRY_LABEL, $_POST['billing_country']); ?></select></li>
      </ul>     
      <?php
  }
  
	  if ($_GET['type'] == "credit_card") {
	  ?>
      <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_DETAILS_LABEL; ?></h4>
	  <p><?php echo NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_DETAILS_DESCRIPTION; ?></p>
      <ul class='credit-card-section'> 
        <li <?php if ($order->creditCardFormErrors["credit_card_number"]) { print "class='error'"; } ?>><label for='credit_card_number'><?php echo NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_NUMBER_LABEL; ?></label><input autocomplete=off value="<?php echo $_POST['credit_card_number']; ?>" type='text' name='credit_card_number' id='credit_card_number' /></li>
        <li <?php if ($order->creditCardFormErrors["expiry_date"]) { print "class='error'"; } ?>><label for='expiry_date_month'><?php echo NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_EXPIRY_DATE_LABEL; ?></label><select <?php if ($order->creditCardFormErrors["expiry_date_month"]) { print "class='error'"; } ?> name='expiry_date_month' id='expiry_date_month'><?php echo generate_expiry_month_options($_POST['expiry_date_month']); ?></select><select <?php if ($order->creditCardFormErrors["expiry_date_year"]) { print "class='error'"; } ?> name='expiry_date_year' id='expiry_date_year'><?php echo generate_expiry_year_options($_POST['expiry_date_year']); ?></select></li>
        <li <?php if ($order->creditCardFormErrors["cvv"]) { print "class='error'"; } ?>><label for='cvv'><?php echo NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_CVV_LABEL; ?></label><input autocomplete=off value="<?php echo $_POST['cvv']; ?>" type='text' name='cvv' id='cvv' /></li>
        <li <?php if ($order->creditCardFormErrors["cardholder_name"]) { print "class='error'"; } ?>><label for='cardholder_name'><?php echo NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_CARD_HOLDER_LABEL; ?></label><input value="<?php echo $_POST['cardholder_name']; ?>" type='text' name='cardholder_name' id='cardholder_name' /></li>
      </ul>     

      <?php
		  
	  } else if ($_GET['type'] == "invoice") {
	  ?>
      <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_INVOICE_DETAILS_LABEL; ?></h4>
	  <p><?php echo NUMO_SYNTAX_SHOPPING_CART_INVOICE_DETAILS_DESCRIPTION; ?></p>
      <?php
		  
	  } else if ($_GET['type'] == "purchase_order") {
	  ?>
      <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_PURCHASE_ORDER_DETAILS_LABEL; ?></h4>
	  <p><?php echo NUMO_SYNTAX_SHOPPING_CART_PURCHASE_ORDER_DETAILS_DESCRIPTION; ?></p>
      <?php
		  
	  } else if ($_GET['type'] == "check") {
	  ?>
      <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_CHECK_DETAILS_LABEL; ?></h4>
	  <p><?php 
	  
	  $checkMessage = NUMO_SYNTAX_SHOPPING_CART_CHECK_DETAILS_DESCRIPTION; 
	  
	  $checkMessage = str_replace("[Company Address]", $order->attributes['packing_slip_address'], $checkMessage);	  
	 // $paymentTypeInstructions = str_replace("[Order Total]", $order->currencyOptionsShort["{$order->attributes['cart_currency']}"].number_format($order->attributes['mc_gross'], '2', '.', ''), $paymentTypeInstructions);	  
	  
	  $checkMessage = str_replace("[Order Total]", $order->currencyOptionsShort["{$order->attributes['cart_currency']}"].number_format($order->calculateOrderTotal(), 2, '.', ','), $checkMessage);
	  echo $checkMessage;
?></p>
      <?php
		  
	  }
	  
	  	  // display final 'message'

	  ?>
      <script type="text/javascript">
	  function modifyStateDropdown(countrySelect, idOfStateSelect, formType) {
		var itemsToShow = "";
		  var stateSelect = document.getElementById(idOfStateSelect);
		  var ausProv = stateSelect.getElementsByClassName("aus-prov");

		for (i = 0; i < ausProv.length; i++) {
			  ausProv[i].style.display = "none";
		  }
		  var cadProv = stateSelect.getElementsByClassName("cad-prov");
		  
		  for (i = 0; i < cadProv.length; i++) {
			  cadProv[i].style.display = "none";
		  }
		  
		  var usState = stateSelect.getElementsByClassName("us-state");
		  
		  for (i = 0; i < usState.length; i++) {
			  usState[i].style.display = "none";
		  }		
		  
		  if (countrySelect.options[countrySelect.selectedIndex].value == "US") {
		     itemsToShow = stateSelect.getElementsByClassName("us-state");
		  } else if (countrySelect.options[countrySelect.selectedIndex].value == "AU") {
		     itemsToShow = stateSelect.getElementsByClassName("aus-prov");
			  
		  } else if (countrySelect.options[countrySelect.selectedIndex].value == "CA") {
		     itemsToShow = stateSelect.getElementsByClassName("cad-prov");
			  
		  }
		  if (itemsToShow == "") {
			  var formTypeLi = document.getElementById(formType+ "_state_li");
			  formTypeLi.style.display = "none";
		  } else {
			  document.getElementById(formType+ "_state_li").style.display = "";
			  
			  for (i = 0; i < itemsToShow.length; i++) {
				  itemsToShow[i].style.display = "block";
			  }		
			  
		  }
	  }
	  </script>
      <h4><?php echo NUMO_SYNTAX_SHOPPING_CART_COMPLETE_ORDER_LABEL; ?></h4>
	  <p><?php echo NUMO_SYNTAX_SHOPPING_CART_COMPLETE_ORDER_DESCRIPTION; ?></p>
	  <input type='submit' class='btn btn-primary' value='<?php echo NUMO_SYNTAX_SHOPPING_CART_COMPLETE_ORDER_BUTTON_LABEL; ?>' />
      <input type='hidden' name='manual-cmd' value='Complete' />
	  
   </form>
   <?php
  }  
?>


<?php
   return;
} else if ($_GET['method'] == "paypal") {
	include_once("numo/modules/shopping_cart/classes/Order.php");
	$order = new Order($_POST['invoice']);
	if ($order->attributes['send_admin_email_order_pending'] == 1 && $order->attributes['payment_type'] != "PayPal") {
	  $order->setOrderPaymentMethod("PayPal");
	 // print "sent email";
	  $order->sendAdminNotificationOfPendingOrder();
	}
?>
<?php if ($settings['store_mode'] == "1") { ?>
<FORM id="paypalsubmitform" name="paypalsubmit" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;">
<?php foreach ($_POST as $key => $value) { ?>
<input type='hidden' name="<?=$key?>" value="<?=$value?>" />
<?php } ?>
</FORM>
<?php } else if ($settings['store_mode'] == "0") { ?>
<FORM id="paypalsubmitform" name="paypalsubmit" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;">
<?php foreach ($_POST as $key => $value) { ?>
<input type='hidden' name="<?=$key?>" value="<?=$value?>" />
<?php } ?>
</FORM>
<?php } ?>
<script type="text/javascript">
document.forms["paypalsubmit"].submit();
</script>
<?
	

  //print "should decide to do something here for paypal";	
  return;
}

if($_POST['numo_cmdc'] == NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_SHOPPING_LABEL) {
	if ($settings['return_page'] != "") {
		$_GET['return_url'] = $settings['return_page'];
		
        if (DIRECT_PROCESSING === true) {
		  header("Location: http://".$numo->getRegisteredDomain()."/".$settings['return_page']);

		} else {
		  header("Location: ".$settings['return_page']);
		}
	} else if (strstr($_SERVER['REQUEST_URI'], "manage.numo")) {
	  header('Location: '.str_replace('/numo/','',NUMO_FOLDER_PATH).'/manage.numo?module=shopping_cart&component=catalog');
	} else {
	  header('Location: ?');
	}
	return; 
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
	if($key != "where" && $key != "page" && $key != "product_id" && $key != "numo_cmd" && $key != "args") {
		$getStringValues .= "&".$key."=".urlencode($value);
	}
}

/**********************************************/
/**************SETUP SESSION INFO**************/
/**********************************************/
//if a visitor is NOT logged in and does not have a shopper id set
if(!isset($_SESSION['shopper_id']) && !isset($_SESSION['account_id'])) {
	//create partial account
	$sql = "INSERT INTO `accounts` (type_id,pending,ip_address,slot_2) VALUES (0,3,'".$_SERVER['REMOTE_ADDR']."','".crypt(time())."')";
	$dbObj->query($sql);

	//get account id for last insert
	$sql = "SELECT LAST_INSERT_ID() as 'account_id'";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		//set account id to cart id
		$_SESSION['shopper_id'] = $row['account_id'];
	}

//if an visitor is logged in but does not have a shopper id set
} else if(!isset($_SESSION['shopper_id']) && isset($_SESSION['account_id'])) {
	$_SESSION['shopper_id'] = $_SESSION['account_id'];

} else if(isset($_SESSION['shopper_id']) && isset($_SESSION['account_id']) && $_SESSION['shopper_id'] != $_SESSION['account_id']) {
	$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['account_id']."'";
	$results = $dbObj->query($sql);

	// if existing pending order for ACCOUNT merge temp account pending order items
	if($row = mysql_fetch_array($results)) {
		$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
		$orders = $dbObj->query($sql);

		// account has a order pending purchase, add to order
		if($order = mysql_fetch_array($orders)) {
			$sql = "UPDATE `shopping_cart_order_items` SET `order_id`='".$row['id']."' WHERE `order_id`='".$order['id']."'";
			$dbObj->query($sql);

			$sql = "DELETE FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
			$dbObj->query($sql);

			$sql = "DELETE FROM `accounts` WHERE `pending`=3 AND `id`='".$_SESSION['shopper_id']."'";
			$dbObj->query($sql);
		//	print $sql." -- alpha";

		// no pending orders, just remove account
		} else {
			$sql = "DELETE FROM `accounts` WHERE `pending`=3 AND `id`='".$_SESSION['shopper_id']."'";
			$dbObj->query($sql);
		//	print $sql." -- beta";
		}

	// if no orders under account just change account id on existing order
	} else {
			$sql = "UPDATE `shopping_cart_orders` SET `account_id`='".$_SESSION['account_id']."' WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
			$dbObj->query($sql);

			$sql = "DELETE FROM `accounts` WHERE `pending`=3 AND `id`='".$_SESSION['shopper_id']."'";
			$dbObj->query($sql);
	//		print $sql." -- charlie";
	}
	
	$_SESSION['shopper_id'] = $_SESSION['account_id'];
//exit;
}

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
	if ($settings['return_page'] != "") {
		$getStringValues .= "&return_page=".urlencode($settings['return_page']);
	}

//exit;
	if ($settings['view_cart_page'] != "") {
	  $_GET['return_url'] = "http://".$numo->getRootFolder().$settings['view_cart_page']."?{$getStringValues}";
	  header('Location: '.$settings['view_cart_page']."?{$getStringValues}");

	} else {
	  if ($_GET['return_url'] == "") {
	    $_GET['return_url'] = $MANAGE_NUMO_LOCATION."?module=shopping_cart&component=catalog&view=cart";
		if (REMOTE_SERVICE === true ) {
			$_GET['view'] = "cart";
			foreach ($_GET as $x => $v) {
				//print "$x = $v <br>";
			}
		  if (DIRECT_PROCESSING === true) {
			 // print $getStringValues;

			 // header('Location: ?view=cart'.$getStringValues."");
			 // exit;
			  //print $getStringValues;
		  }

		} else {

			 header('Location: ?view=cart'.$getStringValues);
			 exit;
		}

	  } else {
	    $_GET['return_url'] = str_replace("component=catalog", "component=catalog&view=cart", $_GET['return_url']);
	    $_GET['return_url'] = str_replace("numo_cmd=add_to_cart", "", $_GET['return_url']);
			if (REMOTE_SERVICE === true) {
			foreach ($_GET as $x => $v) {
				//print "v $x = $v <br>";
			}
			} else {
				 header('Location: ?view=cart'.$getStringValues);
			}
		}

	}
	
}  

$sql = "SELECT * FROM shopping_cart_fields WHERE site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

$slots = array();

while($row = mysql_fetch_array($results)) {

	$slots[$row['slot']]['name'] = $row['name'];
	$slots[$row['slot']]['position'] = $row['position'];
	$slots[$row['slot']]['input_type'] = $row['input_type'];
	if ($row['visibility'] == "") {
		$row['visibility'] = 1;
	}
	$slots[$row['slot']]['visibility'] = $row['visibility'];

	if($row['slot'] == "2") {
		$slots[$row['slot']]['code'] = $row['input_options'];

		if($row['input_options'] == "GBP") {
			$slots[$row['slot']]['options'] = "&#163;";
		} else if($row['input_options'] == "EUR") {
			$slots[$row['slot']]['options'] = "&#128;";
		} else {
			$slots[$row['slot']]['options'] = "$";
		}
	} else if($row['slot'] == "4") {
		$slots[$row['slot']]['options'] = trim($row['input_options']);
	}
}

mysql_free_result($results);
?>
<link rel="stylesheet" type="text/css" href="//<?php print NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH; ?>modules/shopping_cart/components/styles/catalog.css" />
<?php

if($_GET['view'] == "cart" || $PARAMS['view'] == "cart") {
?>
<script>
function updatePayPalQuantity(elId,inpt) {
	var myForm = document.cart_form;
	var maxQuantity = document.getElementById("max_" + inpt.name).value;
	//alert(maxQuantity + " > " + inpt.value);
	if (maxQuantity < inpt.value) {
		myForm.submit();
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

	print '<form name="cart_form" id="cart_form" method="post" action="';
	if (REMOTE_SERVICE === true && DIRECT_PROCESSING === true ) {
		print "manage.numo?module=shopping_cart&component=catalog&view=cart";
	}
	print '" style="display: inline; margin: 0px; padding: 0px;">';
	print '<table class="view_cart_contents" width="100%"><tr><th>Qty</th><th>Product</th><th class="view_cart_contents_item_cost">Unit Cost</th>';

	print '<th class="view_cart_contents_item_cost">Total</th>';

	if ($settings['tax_display_preference'] == 1 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
	  print '<th class="view_cart_contents_item_tax">Tax Rate</th>';
	  print '<th class="view_cart_contents_item_tax">Tax Amount</th>';
	// show the price including the tax
	} else if ($settings['tax_display_preference'] == 2) {
	  print '<th class="view_cart_contents_item_tax">Tax Rate</th>';
	  print '<th class="view_cart_contents_item_tax">Tax Included</th>';
	} else if ($settings['tax_display_preference'] == 3) {
	  print '<th class="view_cart_contents_item_tax">Tax Rate</th>';
	  print '<th class="view_cart_contents_item_tax">Tax Included</th>';
	}
	print '</tr>';
	include_once("numo/modules/shopping_cart/classes/Order.php");
	//$discounts = $order->getDiscounts();
    if (mysql_num_rows($results) == 0) {
			  $order = new Order();
	
	}
	//print "youwzaz";
	//var_dump($_POST);
	//account has a order pending purchase, add to order
	while($row = mysql_fetch_array($results)) {
		if ($orderId == 0 || $orderId == "") {
		 // print "order id is nothing<br>";
		  $order = new Order($row['order_id']);
		  if ($_POST['coupon_code'] != "") {
			//  print "hello!";
			  $order->setCouponCode($_POST['coupon_code']);
		  }
		} else {
		  if ($_POST['coupon_code'] != "") {
			//  print "hello!";
			  $order->setCouponCode($_POST['coupon_code']);
		  }
			
			//print "we have an order id {$orderId}";
		}
	 // foreach ($row as $x => $y) {
		//  print $x."=".$y."<br>";
	 // }
	    $orderId = $row['order_id'];
		//$itemCost = $row['slot_2'];

		$itemAttributes = "";
		$labelAttrUsed = false;
		$itemCost = 0;
		$stockKey = $row['product_id'];

		//$sql = "SELECT pa.`label` as attribute_label, pao.`label` as option_label, pao.`cost` FROM `shopping_cart_order_item_attributes` oa, `shopping_cart_optional_product_attributes` pa, `shopping_cart_optional_product_attribute_options` pao WHERE oa.`order_item_id`='".$row['id']."' AND oa.`attribute_id`=pa.`id` AND pa.`id`=pao.`attribute_id` AND pao.`id`=oa.`value`";
		$sql = "SELECT pa.`label`, pa.`type`, oa.`value`, oa.`attribute_id` FROM `shopping_cart_order_item_attributes` oa, `shopping_cart_optional_product_attributes` pa WHERE oa.`order_item_id`='".$row['id']."' AND oa.`attribute_id`=pa.`id` ORDER BY pa.`position` asc";
		//print $sql."<br>";
		$attributes = $dbObj->query($sql);

		while($attribute = mysql_fetch_array($attributes)) {
			//print $itemCost."<br>";
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

				 // show price AND the tax
				 if ($settings['tax_display_preference'] == 1 || $settings['tax_display_preference'] == 0) {
					 	$itemCost += $row['slot_2'];

						$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);


					    //print number_format($row['slot_2'], 2, '.', ',');

						$taxRateID = $row['slot_8'];
						if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
							//print "<br>Plus ".$taxRates["{$taxRateID}"]["rate_name"]." (".rtrim($taxRates["{$taxRateID}"]["tax_rate"], '.0')."%): ";
							//print $slots['2']['options'].number_format($row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
													//$itemTax = number_format($itemCost*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
							$itemTax =$itemCost*$taxRates["{$row['slot_8']}"]["tax_rate"]/100;
							$taxRate = $taxRates["{$row['slot_8']}"]["tax_rate"];
						} else {
							$taxRate = 0;
						  $itemTax = 0;
						}
					   // print "should price and vat";

				 // show the price including the tax
				 } else if ($settings['tax_display_preference'] == 2) {
					 	$itemCost += $row['slot_2'];

						//$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
						//print "item cost: ".$itemCost."<br>";
						$taxRateID = $row['slot_8'];
						if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
							$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
							$taxRate = $taxRates["{$row['slot_8']}"]["tax_rate"];
						//	$itemTax = number_format($itemCost*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
							$itemTax = $itemCost*$taxRates["{$row['slot_8']}"]["tax_rate"]/100;

							$itemCost += $itemTax;

//print ;

							//print "<br>Includes ".$taxRates["{$taxRateID}"]["tax_rate"]."% ".$taxRates["{$taxRateID}"]["rate_name"];
							//print "<br>Includes ".$itemTax ;
						} else {
					    	//print number_format($row['slot_2'], 2, '.', ',');
							//print $row['slot_2']."vv<br>";
							//$itemCost += $row['slot_2'];
							$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
							$itemTax = 0;
							$taxRate = 0;

						}

				 } else if ($settings['tax_display_preference'] == 3) {
					 	$itemCost += $row['slot_2'];

						//$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
						//print "item cost: ".$itemCost."<br>";
						$taxRateID = $row['slot_8'];
						if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
							$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
							$taxRate = $taxRates["{$row['slot_8']}"]["tax_rate"];
							//$itemTax = number_format($itemCost - ($itemCost / (1 + $taxRates["{$row['slot_8']}"]["tax_rate"]/100)), 2, '.', ',');
							$itemTax = $itemCost - ($itemCost / (1 + $taxRates["{$row['slot_8']}"]["tax_rate"]/100));
						//	$shippingTaxAmount = number_format($shippingCost - ($shippingCost / (1 + $shippingTaxRate / 100)), 2, '.', '');  

							//$itemCost += $itemTax;

//print ;

							//print "<br>Includes ".$taxRates["{$taxRateID}"]["tax_rate"]."% ".$taxRates["{$taxRateID}"]["rate_name"];
							//print "<br>Includes ".$itemTax ;
						} else {
					    	//print number_format($row['slot_2'], 2, '.', ',');
							//print $row['slot_2']."vv<br>";
							//$itemCost += $row['slot_2'];
							$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
							$itemTax = 0;
							$taxRate = 0;

						}

				 } else {
					$itemCost += $row['slot_2'];
												$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);

					$itemTax  = 0;
												$taxRate = 0;

					//print number_format($row['slot_2'], 2, '.', ',');
				 }
	//	print "item cost currently is $itemCost<br>";

				 //$totalTax += $itemTax;
				// $row['item_tax'] = number_format($itemTax, 2, '.', '');
				 $row['item_tax'] = $itemTax;
			//	 print "item tax: ".$itemTax."<br>";
//print "x".$itemCost;
	//	$itemCost = $itemCost - ($itemCost * $shoppingCartDiscount / 100);
		
		if(array_key_exists($stockKey,$cartItems) && !$labelAttrUsed) {
		    if ($_GET['buynow'] == "1") {
			  $cartItems[$stockKey]['quantity']   = 1;
		    } else {
			  $cartItems[$stockKey]['quantity']   += $row['quantity'];
			}

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
		$cartItems["$key"]["counter"] = $counter;
		$stockKey = $row['stock_key'];

		$sql = "SELECT `units` FROM `shopping_cart_product_stock` WHERE `key`='".$stockKey."'";
		$stock_result = $dbObj->query($sql);

		$unitsInStock = 999999;

		if($stock = mysql_fetch_array($stock_result)) {
			$unitsInStock = $stock['units'];
			$stockNotUsed = false;
		} else {
			$stockNotUsed = true;
		}

		if (!$stockNotUsed) {
		   $stockUsed["$stockKey"] = $row['quantity'];

		} else {
		   $stockUsed["$stockKey"] = 0;
		}

		// no units available
		if(!$stockNotUsed && $unitsInStock <= 0) {
			$row['item_tax'] = $row['item_tax'] * $row['quantity'];
			//print "b";

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
			  print '<td class="view_cart_contents_item_cost">'.$slots['2']['options'].$row['item_tax'].'</td>';
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
				  if ($settings['tax_display_preference'] == 2) {
				    $itemAmount = $row['unit_cost'] - $row['item_tax'];
				  } else {
				    $itemAmount = $row['unit_cost'];
				  }


			$paypalItemInfo .= '<input type="hidden" name="item_name_'.$counter.'" value="'.htmlentities($row['slot_1'].' ('.str_replace("<br>",", ",substr($row['attributes'],0,-4))).')"><input type="hidden" name="amount_'.$counter.'" value="'.$itemAmount.'"><input type="hidden" name="quantity_'.$counter.'" id="numo_paypal_quantity_'.$counter.'" value="'.$quantityAvailable.'"><input type="hidden" name="item_number_'.$counter.'" value="'.$row['id'].'">';
			if ($row['item_tax'] > 0) {
				$paypalItemInfo .= '<input type="hidden" name="tax_'.$counter.'" value="'.$row['item_tax'].'">';
			}
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
			if ($settings['tax_display_preference'] == 1 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
				$row['item_tax'] = $row['item_tax'] * $quantityAvailable;
				//$totalCost += $row['item_tax'] * $quantityAvailable;
				$totalCost += $row['item_tax'];
			} else if ($settings['tax_display_preference'] == 2) {
				$row['item_tax'] = $row['item_tax'] * $row['quantity'];

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
			  print '<td class="view_cart_contents_item_cost">';
			  if ($row['tax_rate'] > 0) {
				  print $slots['2']['options'].number_format($row['item_tax'], 2, '.', '');
			  }
			  print '</td>';
			}

			print '</tr>';

			//increase paypal item counter
			$counter++;

		//there is enough product to cover order request
		} else {
			$itemShippingCost = 0;
			$itemShippingWeight = 0;
			// if no stock used set shipping amount normally
			//print $stockUsed["$stockKey"];
			if($stockUsed[$stockKey] <= 1) {

				if ($row['slot_5'] == "0") {
					$itemShippingCost = $row['shipping'] + ($row['shipping2'] * ($row['quantity'] - 1));
				    $shippingCost += $itemShippingCost;
				 // print "x".$shippingCost;
				} else if ($row['slot_5'] == "1") {
					//print "y";
					$itemShippingWeight = $row['shipping'] + ($row['shipping'] * ($row['quantity'] - 1));
				  $shippingWeight += $itemShippingWeight;
				}
            //  print "shipping a";
			// if 1 or more units have been shown in the cart adjust shipping cost
			} else if ($row['slot_5'] == "0") {
			//	print "z";
				$itemShippingCost = $row['shipping'] + ($row['shipping2'] * ($row['quantity'] - 1));
				$shippingCost += $itemShippingCost;


				//$shippingCost += ($row['shipping2'] * $row['quantity']);
				// disabled this december 29th as it is setting the main shipping amount to the secondary shipping amount if quantity is greater than 1
				//$row['shipping'] = $row['shipping2'];
            //  print "shipping b";
			} else if ($row['slot_5'] == "1") {
				$itemShippingWeight = $row['shipping'] + ($row['shipping'] * ($row['quantity'] - 1));
				$shippingWeight += $itemShippingWeight;
			}
			//print $row['shipping'];
  				  if ($settings['tax_display_preference'] == 2) {
				    $itemAmount = $row['unit_cost'] - $row['item_tax'];
				  } else {
				    $itemAmount = $row['unit_cost'];
				  }

			//by default do not include any information about shipping ... let payapl settings handle shipping
			$paypalItemInfo .= '<input type="hidden" name="item_name_'.$counter.'" value="'.htmlentities($row['slot_1'].' ('.str_replace("<br>",", ",substr($row['attributes'],0,-4))).')'.($row['slot_7'] != "" ? " SKU #".$row['slot_7'] : "").'"><input type="hidden" name="amount_'.$counter.'" value="'.$itemAmount.'"><input type="hidden" name="quantity_'.$counter.'" id="numo_paypal_quantity_'.$counter.'" value="'.$row['quantity'].'"><input type="hidden" name="item_number_'.$counter.'" value="'.$row['id'].'">';
			//print $row['item_tax'];
			if ($row['item_tax'] > 0) {
				$paypalItemInfo .= '<input type="hidden" name="tax_'.$counter.'" value="'.$row['item_tax'].'">';
			}

			if($itemShippingCost+$itemShippingWeight > 0) {
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
				$totalCost += $row['item_tax'];

			} else if ($settings['tax_display_preference'] == 2) {
				$row['item_tax'] = $row['item_tax'] * $row['quantity'];
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
			  print '<td class="view_cart_contents_item_cost">';
			  if ($row['tax_rate'] > 0) {
				  print $slots['2']['options'].number_format($row['item_tax'], 2, '.', '');
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
	if ($shippingCost > 0) {
	  $shippingTaxAmount = 0;
	
	  // calculate tax on shipping
	  if ($settings['shipping_taxation_rate'] > 0) {
		 $taxField = $settings['shipping_taxation_rate'];
		 $shippingTaxRate = $taxRates["{$taxField}"]["tax_rate"];
		 $shippingTaxAmount = 0;

		  // net tax -- (taxes on top)
		  if ($settings['tax_display_preference'] == 0 || $settings['tax_display_preference'] == 1) {
			$shippingTaxAmount = number_format($shippingCost * $shippingTaxRate / 100, 2, '.', '');  

		  // gross taxes -- (taxes displayed included, but still on top of base product price)	  
		  } else if ($settings['tax_display_preference'] == 2) {
			$shippingTaxAmount = number_format($shippingCost * $shippingTaxRate / 100, 2, '.', '');  
		    $shippingCost += $shippingTaxAmount;
		  
		  // gross taxes -- (taxes displayed included, but included in base product price)	    
		  } else if ($settings['tax_display_preference'] == 3) {
			$shippingTaxAmount = number_format($shippingCost - ($shippingCost / (1 + $shippingTaxRate / 100)), 2, '.', '');  		  
		  }
	   }
  	   $totalTax += $shippingTaxAmount;
	   $totalCost += $shippingCost;

		print '<tr><td colspan="3" style="text-align: right; font-weight: bold;">';
		print NUMO_SYNTAX_SHOPPING_CART_SHIPPING_LABEL.'</td>';
		print '<td class="view_cart_contents_item_cost" style="font-weight: bold;">'.($slots['2']['options'].number_format($shippingCost, 2, '.', ',')).'</td>';
		
		if ($shippingTaxAmount > 0) {
		  print '<td class="view_cart_contents_item_quantity">'.clean_num($shippingTaxRate).'%</td>';
		  print '<td  class="view_cart_contents_item_cost" style="font-weight: bold;">'.($slots['2']['options'].number_format($shippingTaxAmount, 2, '.', ',')).'</td>';
		}
		print '</tr>';
	}

	$order = new Order($order->attributes["id"]); // need to re-initialize it so that the discounts pull from the current quantity (in the event that the quantities are reduced due to stock levels)
		  if ($_POST['coupon_code'] != "") {
			//  print "hello!";
			  $order->setCouponCode($_POST['coupon_code']);
		  }
	$discounts = $order->getDiscounts();
	//print "v".sizeof($discounts)."x"; 
	//var_dump($discounts);
  	foreach ($discounts as $discountScope => $discountz) {
		if ($discountScope == "product") {
		foreach ($discountz as $discount) { 
		//print "c:".$discount->attributes['qualifier_scope']."<br>";
	//	print "d:".$discount->attributes['amount_type']."<br>";
		  if ($discount->attributes['qualifier_scope'] != "0"  ) {
			print "<tr><td colspan='3' style='text-align: right; font-weight: bold;'>".$discount->getName()." (".$discount->getDescription().")"."</td>";
			print "<td class='view_cart_contents_item_cost'  style='font-weight: bold;'>-".($slots['2']['options'].number_format($discount->getRebateAmount(), 2, '.', ''))."</td>";
			if ($discount->attributes['discount_tax_removed'] > 0) {
				print "<td>&nbsp;</td><td class='view_cart_contents_item_cost'  style='font-weight: bold;'>-".($slots['2']['options'].number_format($discount->attributes['discount_tax_removed'], 2, '.', ''))."</td>";
			}
			print "</tr>";
		foreach ($discount->qualifiedProducts as $itemID => $item) {
			foreach ($cartItems as $xid => $xitem) {
				if ($xitem['id'] == $itemID) {
					//var_dump($cartItems["$itemID"]);
			        $thisCounter = $xitem["counter"];
			        $thisRebateAmount = number_format($discount->getRebateAmount($order->getItemTotal($itemID)), 2, '.', '');
			       $paypalItemInfo .= "<input type='hidden' name='discount_amount_{$thisCounter}' value='{$thisRebateAmount}' />";	
				   if ($row['item_tax'] > 0) {
					   $taxRemoved = $order->items["$itemID"]["individual_tax_removed"];
					//   print "removing $taxRemoved <br>";
					   $find = '<input type="hidden" name="tax_'.$thisCounter.'" value="'.$xitem['item_tax'].'">';
					//   $replace = '<input type="hidden" name="tax_'.$thisCounter.'" value="'.number_format($xitem['item_tax']-($taxRemoved/$xitem['quantity']), 2, '.', '').'">';
					   $replace = '<input type="hidden" name="tax_rate_'.$thisCounter.'" value="'.$xitem['tax_rate'].'">';
			           $paypalItemInfo = str_replace($find, $replace, $paypalItemInfo);
				   }
				}
			}
		}
			//unset($discounts["$discountScope"]);
				 // $totalCost = $totalCost - $discount->getRebateAmount();
	
		  } 
		}
		}
	  //$totalCost = $totalCost - $discount->getRebateAmount();
	}  
	
	$totalCost = $order->getItemSubtotal(true) + $order->getOrderTax(true) + $shippingCost;
	
	$totalTax  = $order->getOrderTax(true);
	//$totalTax = $order->getOrderTax();
	if ($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0)) {
	  print '<tr><td colspan="'.($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0) ? 3 : 3).'" style="text-align: right; font-weight: bold;">'.($settings['tax_display_preference'] > 0 || ($settings['tax_display_preference'] == 0 && sizeof($taxRates) > 0) ? 'Pre Tax ' : '').NUMO_SYNTAX_SHOPPING_CART_TOTAL_LABEL.'</td><td class="view_cart_contents_item_cost" style="font-weight: bold;">'.($slots['2']['options'].number_format($totalCost-$totalTax, 2, '.', ',')).'</td></tr>';
	  print "<tr>";
	  print '<td colspan="3" style="text-align: right; font-weight: bold;">TAX</td>';
	  print '<td style="text-align: right; font-weight: bold;">'.number_format($totalTax, 2, '.', '').'</td>';
	  print "</tr>";
	}
	$orderRebate = 0;
	foreach ($discounts as $discountScope => $discount) {		
		if ($discountScope == "order") {
			$orderRebate .= $discount->getRebateAmount();
		//	print "yup";
		//	print "<pre>";
		//	var_dump($discount);
		//	print "</pre>";
	//	foreach ($discountz as $discount) {
	    print "<tr><td colspan='3' style='text-align: right; font-weight: bold;'>".$discount->getName()." (".$discount->getDescription().")"."</td>";
	    print "<td class='view_cart_contents_item_cost'  style='font-weight: bold;'>-".($slots['2']['options'].number_format($discount->getRebateAmount(), 2, '.', ''))."</td>";
	    print "<tr>";
	  $totalCost = $totalCost - $discount->getRebateAmount();
	//	}
		}
	}
	if ($orderRebate > 0) {
			$paypalItemInfo .= "<input type='hidden' name='discount_amount_cart' value='{$orderRebate}' />";	
	}

	$coupons = $order->getAvailableCoupons();

	if (sizeof($coupons) > 0 && $order->attributes['coupon_code'] == "") {
		print "<tr><td colspan='3' style='text-align: right; font-weight: bold;'>Coupon Code</td>";
		print "<td class='view_cart_contents_item_cost'  style='font-weight: bold;'><input style='width: 50px;' type='text' name='coupon_code' /><input type='submit' class='btn btn-small' value='Apply' /></td>";
		print "<tr>";
	} 
	/*
  	foreach ($discounts as $discountScope => $discount) {
		
	  if ($discount->attributes['qualifier_scope'] != "0" && $discount->attributes['amount_type'] != "0" ) {
	    print "<tr><td colspan='3' style='text-align: right; font-weight: bold;'>".$discount->getName()." (".$discount->getDescription().")"."</td>";
	    print "<td class='view_cart_contents_item_cost'  style='font-weight: bold;'>-".($slots['2']['options'].number_format($discount->getRebateAmount(), 2, '.', ''))."</td>";
	    print "<tr>";
		unset($discounts["$discountScope"]);
			 // $totalCost = $totalCost - $discount->getRebateAmount();

	  }
	  //$totalCost = $totalCost - $discount->getRebateAmount();
	}  
	*/


	  print '<tr><td colspan="3" style="text-align: right; font-weight: bold;">'.NUMO_SYNTAX_SHOPPING_CART_TOTAL_LABEL.'</td>';
	  print '<td class="view_cart_contents_item_cost" style="font-weight: bold;">'.($slots['2']['options'].number_format($totalCost, 2, '.', ',')).'</td></tr>';

	print '</table><input type="hidden" name="numo_cmd" value="update_cart_order" />';
	if ($order->attributes['coupon_code'] != "") {
				print '<input type="hidden" name="coupon_code" value="'.$order->attributes['coupon_code'].'" />';
	}
			print '<input type="hidden" name="return_page" value="'.urlencode($settings['return_page']).'" />';

		$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `account_id`='".$_SESSION['shopper_id']."'";
		$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		//if not logged in allow to login
		if(!isset($_SESSION['account_id']) && $settings['require_account_at_checkout'] != "0") {
			print '</form>';

			print '<form ';

			if ($settings['view_cart_page'] != "" && strstr($_SERVER['REQUEST_URI'],$settings['view_cart_page'])) {
			//	print 'action="manage.numo?module=shopping_cart&component=catalog"';
			}
			//if ($settings['return_page'] == "") {
			//if ($settings['view_cart_page'] != "" && strstr($_SERVER['REQUEST_URI'],$settings['view_cart_page'])) {
			//	print 'action="manage.numo?module=shopping_cart&component=catalog"';
			//} else {
			//	print 'action="'.urlencode($settings['return_page']).'"';
			//}
			print ' method="post" style="display: inline; margin: 0px; padding: 0px;">';
			print '<input type="hidden" name="return_page" value="'.urlencode($settings['return_page']).'" />';
			print '<input type="submit" name="numo_cmdc" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_SHOPPING_LABEL.'" /><input type="submit" name="numo_cmdb" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_BUTTON_LABEL.'" /></form>';

		//if logged in create the paypal buy now button to checkout
		} else {
			print '</form>';
			//print $_SERVER['REQUEST_URI']."--".$settings['view_cart_page'];
			print '<form class="continue_form" ';
			//if ($settings['return_page'] == "") {
			//if ($settings['view_cart_page'] != "" && strstr($_SERVER['REQUEST_URI'],$settings['view_cart_page'])) {
			//	print 'action="manage.numo?module=shopping_cart&component=catalog"';
			//} else {
			//	print 'action="'.$settings['return_page'].'"';
			//}
			if ($settings['view_cart_page'] != "" && strstr($_SERVER['REQUEST_URI'],$settings['view_cart_page'])) {
			//	print 'action="manage.numo?module=shopping_cart&component=catalog"';
			}
			print ' method="post" style="display: inline; margin: 0px; padding: 0px;">';
			print '<input type="hidden" name="return_page" value="'.$settings['return_page'].'" />';
           
			print '<input type="submit" name="numo_cmdc" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_CONTINUE_SHOPPING_LABEL.'" /></form>';

			$sql = "SELECT * FROM `shopping_cart_settings` WHERE `site_id`='".NUMO_SITE_ID."'";
			$settings = $dbObj->query($sql);

	if ($_GET['buynow'] == "1") { ?>
	<div style='text-align: center;'><div>Please wait while you are redirected to PayPal to make your payment.  If you haven't been redirected in 15 seconds, click the Checkout button below.</div>

	<?php }

			if($setting = mysql_fetch_array($settings)) {
				$totalPaymentOptions = 0;
				if ($setting['store_mode'] > -1) { $totalPaymentOptions++; }
				if ($setting['store_mode_order_collection_on'] == 1) { 
				//	$totalPaymentOptions += sizeof(explode(",", $settings['offline_payment_types']));
				  if ($setting['offline_payment_types'] != "") {
					 $offlinePaymentOptions = sizeof(explode(",", $setting['offline_payment_types']));
					 if ($offlinePaymentOptions > 0) {
						$totalPaymentOptions++;
					 }
				}
					//if ($offlinePaymentOptions > 
				
				}
//print $totalPaymentOptions;
$doCheckout = false; 
				if ($totalPaymentOptions > 1) {   
				  $doCheckout = true;
					print '<FORM id="paypalsubmitform" name="paypalsubmit" action="https://'.$numo->siteSettings['ssl_address'].array_shift(explode("?", $_SERVER['REQUEST_URI'])).'?module=shopping_cart&component=catalog&method=choose" method="post" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return validateQuantities()">';

				} else if ($setting['store_mode_order_collection_on'] == 1 && $offlinePaymentOptions > 0) {
				      $doCheckout = true;
					if ($offlinePaymentOptions == 1) { 
					  print '<FORM id="paypalsubmitform" name="paypalsubmit" action="https://'.$numo->siteSettings['ssl_address'].array_shift(explode("?", $_SERVER['REQUEST_URI'])).'?module=shopping_cart&component=catalog&method=manual&type='.$setting['offline_payment_types'].'" method="post" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return validateQuantities()">';
					} else {
					  print '<FORM id="paypalsubmitform" name="paypalsubmit" action="https://'.$numo->siteSettings['ssl_address'].array_shift(explode("?", $_SERVER['REQUEST_URI'])).'?module=shopping_cart&component=catalog&method=manual" method="post" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return validateQuantities()">';
					
					}
				} else if ($setting['store_mode'] == 1) {
				      $doCheckout = true;
					  $folderpath = explode("?", $_SERVER['REQUEST_URI']);
					  					  $folderpath = array_shift($folderpath);
					print '<FORM id="paypalsubmitform" name="paypalsubmit" action="http://'.NUMO_SERVER_ADDRESS.$folderpath.'?module=shopping_cart&component=catalog&method=paypal" method="post" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return validateQuantities()">';
				//	print '<FORM id="paypalsubmitform" name="paypalsubmit" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return validateQuantities()">';
				
				} else if ($setting['store_mode'] == 0) {
				      $doCheckout = true;
					  $folderpath = explode("?", $_SERVER['REQUEST_URI']);
					  					  $folderpath = array_shift($folderpath);

					print '<FORM id="paypalsubmitform" name="paypalsubmit" action="http://'.NUMO_SERVER_ADDRESS.$folderpath.'?module=shopping_cart&component=catalog&method=paypal" method="post" style="display: inline; margin: 0px; padding: 0px;" onsubmit="return validateQuantities()">';
					//print '<FORM id="paypalsubmitform" name="paypalsubmit" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" style="display: inline; margin: 0px; padding: 0px;"onsubmit="return validateQuantities()">';
				}
			if ($doCheckout) {
		//		print "<input type='text' name='discount_rate_cart' value=''>";
				 print '<input type="hidden" name="cmd" value="_cart"><input type="hidden" name="upload" value="1"><input type="hidden" name="invoice" value="'.$orderId.'"><input type="hidden" name="business" value="'.$setting['paypal_email'].'">'.$paypalItemInfo.'<input type="hidden" name="currency_code" value="'.$slots['2']['code'].'" />';

				if($setting['request_shipping_details'] == '1') {
					print '<input type="hidden" name="no_shipping" value="2">';
				} else {
					print '<input type="hidden" name="no_shipping" value="1">';
				}
				if ($setting['paypal_return_url'] != "") {
					print '<input type="hidden" name="return" value="'.$setting['paypal_return_url'].'">';
					
				}
				if ($setting['paypal_cancel_url'] != "") {
					print '<input type="hidden" name="cancel_return" value="'.$setting['paypal_cancel_url'].'">';
					
				}
				//var_dump($settings);
				print '<input type="submit" value="'.NUMO_SYNTAX_SHOPPING_CART_CHECKOUT_BUTTON_LABEL.'"></form>';
			}
			
			}
		}
	}
	if ($_GET['buynow'] == "1") { ?>
	</div>
	<style>
	  form#cart_form { display: none !important; }
	  form#paypalsubmitform { margin-top: -999px;}
	  form.continue_form { display: none !important; }
	</style>
	<script type="text/javascript">
	  document.forms['paypalsubmit'].submit();
	</script>

	<?php
	}

/*************************/
/***  PRODUCT DISPLAY  ***/
/*************************/
} else if(is_numeric($_GET['pid'])) {
	global $productDisplayed;
	if ($productDisplayed == true) {
		//print "blah";
		return; 
	} else {
		//print "no";
	} 
	$productDisplayed = true;
	
	
	
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
            
				<?php
                if ($settings['product_details_use_lightbox'] == "1") { 
					 if (is_null($row['image_name'])) { ?>
                     <img class="product_thumbnail_large" src="<?php if (REMOTE_SERVICE === true) {
						
					print "http://".NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH."module/shopping_cart/uploads/images/coming_soon_sm.jpg";
				
			} else {
				print NUMO_FOLDER_PATH."modules/shopping_cart/".(is_null($row['image_name']) ? 'images/coming_soon_sm.jpg' : 'uploads/'.$row['image_name']);
			}
			?>" />
            <? } else {
				 numo_enqueue_js("https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js", "jquery", "1.9.0");
				 ?>
                 
                 <link rel="stylesheet" href="<?php echo NUMO_FOLDER_PATH; ?>styles/pretty-photo/css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
                 <script src="<?php echo NUMO_FOLDER_PATH; ?>styles/pretty-photo/js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
               <?php
			   $query = "SELECT i.* FROM `shopping_cart_product_images` i WHERE i.`listing_id`='{$_GET['pid']}'  ORDER BY i.`id` asc";
			   $imageResult = $dbObj->query($query);
			   $count = 0;
			   while ($imageRow = mysql_fetch_array($imageResult)) {
				  
			    if (REMOTE_SERVICE === true) {
					$imgURL =  "http://".NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH."uploads/modules/shopping_cart/".$imageRow['file_name'];
				} else {
				   $imgURL = 	NUMO_FOLDER_PATH."modules/shopping_cart/".(is_null($imageRow['file_name']) ? 'images/coming_soon_sm.jpg' : 'uploads/'.$imageRow['file_name']);
				}
				?>
                <a href="<?php echo $imgURL; ?>" rel="prettyPhoto[gallery1]" title="<?php print $imageRow['description']; ?> "><img class="<?php if ($count == 0) { print 'product_thumbnail_large'; } else { print 'product_thumbnail_small'; } ?>" src="<?php echo $imgURL; ?>" /></a>

				<?php
				 $count++;
					}
			}
				} else {
				?>
                <a href="http://<?php echo $numo->getRootFolder(); ?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>" onclick="window.open('http://<?php echo NUMO_SERVER_ADDRESS; ?><?=str_replace('/numo/','',NUMO_FOLDER_PATH)?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>','<?=$_GET['pid']?>','location=0,status=0,scrollbars=1,width=650,height=500'); return false;"><img class="product_thumbnail_large" src="<?php if (REMOTE_SERVICE === true) {
				if (is_null($row['image_name'])) {
					print "http://".NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH."module/shopping_cart/uploads/images/coming_soon_sm.jpg";
				} else {
					
					  print "http://".NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH."uploads/modules/shopping_cart/".$row['image_name'];
					
				}
			} else {
				print NUMO_FOLDER_PATH."modules/shopping_cart/".(is_null($row['image_name']) ? 'images/coming_soon_sm.jpg' : 'uploads/'.$row['image_name']);
			}
			?>" alt="<?=$row['image_description']?>" title="<?=$row['image_description']?>" /></a>
            <?php  
			if(!is_null($row['image_name'])) {
				?>
				<a href="http://<?php echo $numo->getRootFolder(); ?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>" onclick="window.open('http://<?php echo NUMO_SERVER_ADDRESS; ?><?=str_replace('/numo/','',NUMO_FOLDER_PATH)?>/component.numo?module=shopping_cart&component=images&pid=<?=$_GET['pid']?>','<?=$_GET['pid']?>','location=0,status=0,scrollbars=1,width=650,height=500'); return false;">View Larger</a>
				<?php
				}
				}
				?>
            <script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function(){
				jQuery("a[rel^='prettyPhoto']").prettyPhoto({social_tools: '', animation_speed:'normal',theme:'light_square',slideshow:10000, autoplay_slideshow: true});
			});
			</script>
			</td>

			<td width="100%">
			<h3 class='numo_shopping_cart_product_name'><?=$row['slot_1']?></h3>
                          <?php if ($row['slot_7'] != "") { ?>
                <p class='product_sku'><?php print $slots['7']['name'].': '.$row['slot_7']?></p>
              <?php } ?>
			<p style="font-size: 14px;">
			<? if (is_numeric($row['slot_2'])) {
				$showPlusSign = true;
						 $supplementalBasePrice = 0;
					 // if the price of the product is zero, iterate through the available attributes to see if there is a "from" price available
					 if ($row['slot_2'] == 0 && $settings['zero_priced_display_when_attributes'] == "2") {
						 $attrQuery = "SELECT pao.cost FROM shopping_cart_optional_product_attributes pa, shopping_cart_optional_product_attribute_options pao WHERE pa.product_id='{$row['id']}' AND pa.type='dropdown list' AND pa.id=pao.attribute_id AND pao.cost>0 ORDER BY pao.cost";
					     $attrResult = $dbObj->query($attrQuery);
						//print $attrQuery;
						 $attrRecord = mysql_fetch_array($attrResult);
						 $supplementalBasePrice = $attrRecord['cost'];
					    
					 } 
					 if ($row['slot_2'] == 0) {
						 $showPlusSign = false;
					 }
					 if ($row['slot_2'] == 0  && $settings['zero_priced_display_when_attributes'] == 1) {
						// don't show any price 
						//print "ff";
					 } else if ($row['slot_2'] == 0 && $row['product_attr'] > 0 && ($settings['zero_priced_display_when_attributes'] == 0 || $supplementalBasePrice == 0 || $settings['zero_priced_display'] == "")) {
						print ($slots['2']['name'].": ").$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
					 } else if ($row['slot_2'] == 0 && $supplementalBasePrice == 0) {
						if ($settings['zero_priced_display'] == 0 || $settings['zero_priced_display'] == "") {
						  print ($slots['2']['name'].": ").$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
							
						} else if ($settings['zero_priced_display'] == 1)  {
						  // down't show any price
						
						} else if ($settings['zero_priced_display'] == 2) {
						  print NUMO_SYNTAX_SHOPPING_CART_FREE_LABEL;	
						}
					 } else {
						 $priceField = "[price]";
						 if (NUMO_SYNTAX_SHOPPING_CART_FROM_PRICE !== "" && $supplementalBasePrice > 0 && $row['slot_2'] == 0 && $settings['zero_priced_display_when_attributes'] == 2) {
							 $priceField = NUMO_SYNTAX_SHOPPING_CART_FROM_PRICE;
						 }
						 $row['slot_2'] += $supplementalBasePrice;
						 
						// print "x";
						 
					 $afterDiscount = $row['slot_2'] - ($row['slot_2'] * ($shoppingCartDiscount / 100));
					 // show price AND the tax
					 if ($settings['tax_display_preference'] == 1) {
							if ($shoppingCartDiscount > 0) {
								if ($showOriginalPrice) {
								  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span>";
								}
								print " <span>".($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
								$row['slot_2'] = $afterDiscount;
							} else {
								print str_replace("[price]", $slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
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
									print " <span>".($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
									$row['slot_2'] = $afterDiscount;
								} else {
									print ($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ','), $priceField);
								}

								//print $slots['2']['options'].;

								print "<br>Includes ".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."% ".$taxRates["{$taxRateID}"]["rate_name"];
							} else {
								if ($shoppingCartDiscount > 0) {
									if ($showOriginalPrice) {
									  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
									}
									print "<span>".($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
								} else {
									print ($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
								}

								//print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');

							}

					 } else {
						if ($shoppingCartDiscount > 0) {
							if ($showOriginalPrice) {
							  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
							}
							print "<span>".($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
						} else {
							print ($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
						}
					 }
					 }
				} else {
					print 'N/A';
				}
			?>

            </p>

			<form method="post" onsubmit="return validate(this)">
			<input type="hidden" name="return_page" value="<?php echo $settings['return_page']; ?>" />
			<?php if ($_GET['buynow'] == "1") { ?>
			<input type="hidden" name="buynow" value="1" />
			<?php } ?>

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
					  print '<td id="attr__'.$field['id'].'__fieldName" style="font-size: 13px; vertical-align: top; padding-top: 3px;';
					  if ($field['label_fg_color'] != "") {
						  print "color: {$field['label_fg_color']};";
					  }
					  print '">'.$field['label'].': </td><td>';
					}

					if($field['type'] == "dropdown list") {
						//load field information for accounts group
						$sql = "SELECT id,label,cost FROM `shopping_cart_optional_product_attribute_options` WHERE `attribute_id`='".$field['id']."' AND `status`=1 ORDER BY `id`";
						//print $sql."<br>";
						$optionals = $dbObj->query($sql);

						print '<select name="attr__'.$field['id'].'">';

						while($option = mysql_fetch_array($optionals)) {
							print '<option value="'.$option['id'].'">'.$option['label'];
							if ($settings['tax_display_preference'] == 2) {

							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								if ($shoppingCartDiscount > 0) {
									$afterDiscount = number_format($option['cost'] + $option['cost']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') - (number_format($option['cost'] + $option['cost']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') * $shoppingCartDiscount / 100);
									//if ($showOriginalPrice) {
									//  print "<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',')."</span>";
									//}
									//print " <span>".$slots['2']['options'].number_format($afterDiscount, 2, '.', ',')."</span>";
									$option['cost'] = $afterDiscount;
								} else {
									$option['cost'] = number_format($option['cost'] + $option['cost']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
								}

								//print $slots['2']['options'].;

								//print "<br>Includes ".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."% ".$taxRates["{$taxRateID}"]["rate_name"];
							}
							}
							if($option['cost'] > 0) {
								print ' ('.($showPlusSign ? "+" : "").($slots['2']['options'].number_format($option['cost'], 2, '.', ',')).')';
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
       <?php
	   $displayInfo = $slots;
	   unset($displayInfo[8]);
	   unset($displayInfo[7]);
	   unset($displayInfo[6]);
	   unset($displayInfo[5]);
	   unset($displayInfo[2]);
	   unset($displayInfo[1]);
	   if (!function_exists("sortSlots")) {
	   function sortSlots($a, $b) {
		  // print $a["position"]." x? ".$b["position"]."<br>";
		   //print $a." x? ".$b."<br>";
		   //foreach ($a as $x => $y) {
			//   print $x."=".$y."<br>";
		   //}
		   if ($a['position'] < $b['position']) {
			   return -1;
		   } else if ($a['position'] > $b['position']) {
			   return 1;
		   } else {
			   return 0;
		   }
	   }
	   }
	   uasort ($displayInfo, "sortSlots");

	   foreach ($displayInfo as $slotNum => $slotData) {
			   $fieldData = $row["slot_{$slotNum}"];
			   if ($slotData['visibility'] == "1" && $fieldData != "" && $slotData["name"] != "") {

				if ($slotData["input_type"] == 'link') { ?>
		<tr>
			<td colspan="2"><h4><?=$displayInfo["{$slotNum}"]['name']?></h4><p><a href="<?php if (!strstr($fieldData, "http://") && strstr($fieldData, "https://")) { print "http://"; }
			echo $fieldData; ?>" target="_blank"><?php echo $fieldData; ?></a></p></td>
		</tr>
			<?php	} else if ($slotData['input_type'] == "money") { ?>

			<?php	} else { ?>
		<tr>
			<td colspan="2"><h4><?=$displayInfo["{$slotNum}"]['name']?></h4><p><?php if (stristr($fieldData, "<tabl")) { print $fieldData; } else { print nl2br($fieldData); } ?></p></td>
		</tr>
        <?php }
          }
	   }
	   ?>
	</table>
<?php
	}
} else {
 //print "yup";
	if ($settings['show_order_chooser'] == 1) { ?>
 [NUMO.SHOPPING CART: ORDER CHOOSER]
    <?php
	}

	$pageNumber = 0;
	$itemsPerPage = 10;
	if ($PARAMS['items_per_page'] != "") {
		$itemsPerPage = $PARAMS['items_per_page'];
	}
	$searchTerms = "";

	if(isset($_GET['page']) && is_numeric($_GET['page'])) {
		$pageNumber = $_GET['page'];
	}

	$startPosition = $pageNumber * $itemsPerPage;
	$whereStr = "";
	//$categoryDescription = constant('NUMO_SYNTAX_SHOPPING_CART_DEFAULT_CATEGORY_META_DESCRIPTION');
    $categoryTitle = ""; 
	$categoryDescription = constant('NUMO_SYNTAX_SHOPPING_CART_DEFAULT_CATEGORY_META_DESCRIPTION');
	$shoppingCartKeywordString = constant('NUMO_SYNTAX_SHOPPING_CART_DEFAULT_CATEGORY_META_KEYWORDS');
	
	if(isset($_POST['search_terms'])) {
	        $searchTerms = "&search_terms={$_POST['search_terms']}";
		$whereStr = "AND (`slot_1` LIKE '%".$_POST['search_terms']."%' OR `slot_3` LIKE '%".$_POST['search_terms']."%')";
	} else if(isset($_GET['search_terms'])) {
	        $searchTerms = "&search_terms={$_GET['search_terms']}&";
		$whereStr = "AND (`slot_1` LIKE '%".$_GET['search_terms']."%' OR `slot_3` LIKE '%".$_GET['search_terms']."%')";
	} else if(isset($_GET['cid']) && is_numeric($_GET['cid'])) {
		//$whereStr = "AND id=c.product_id AND c.category_id='".$_GET['cid']."'";
		//$whereStr = "AND p.id=(SELECT product_id FROM `shopping_cart_product_categories` WHERE category_id='".$_GET['cid']."' AND product_id=p.id AND site_id='".NUMO_SITE_ID."')";
		//$whereStr = "AND p.id IN (SELECT product_id FROM `shopping_cart_product_categories` WHERE category_id='".$_GET['cid']."' AND product_id=p.id AND site_id='".NUMO_SITE_ID."')";
		$whereStr = "AND p.id=pc.product_id AND pc.category_id='".$_GET['cid']."' ";
		$doAlt = true;
	    
		$categoryQuery = "SELECT * FROM `shopping_cart_categories` WHERE id='".$_GET['cid']."' AND site_id='".NUMO_SITE_ID."'";
		$categoryResult = $dbObj->query($categoryQuery);
		$categoryRecord = mysql_fetch_array($categoryResult);
		$categoryTitle 		 = $categoryRecord['label'];
		$categoryDescription = $categoryRecord['description'];
		$shoppingCartKeywordString  = $categoryRecord['keywords'];
	} else if(isset($PARAMS['cid'])) {
		//$whereStr = "AND p.id=(SELECT product_id FROM `shopping_cart_product_categories` WHERE category_id='".$PARAMS['cid']."' AND product_id=p.id AND site_id='".NUMO_SITE_ID."')";
		//$whereStr = "AND p.id IN (SELECT product_id FROM `shopping_cart_product_categories` WHERE category_id='".$PARAMS['cid']."' AND product_id=p.id AND site_id='".NUMO_SITE_ID."')";
		$whereStr = "AND p.id=pc.product_id AND pc.category_id='".$PARAMS['cid']."' AND site_id='".NUMO_SITE_ID."'";
		$doAlt = true;


		$categoryQuery = "SELECT * FROM `shopping_cart_categories` WHERE id='".$PARAMS['cid']."' AND site_id='".NUMO_SITE_ID."'";

		$categoryResult = $dbObj->query($categoryQuery);
		$categoryRecord = mysql_fetch_array($categoryResult);
		$categoryTitle 		 = $categoryRecord['label'];
		$categoryDescription = $categoryRecord['description'];
		$shoppingCartKeywordString  = $categoryRecord['keywords'];
	}
	// && $_SESSION['pending'] == 0 && $_SESSION['activated'] == 1

	// need to implment search terms in the future
	if ($settings['catalog_visibility'] == "1") {
	    if(isset($_GET['cid']) && is_numeric($_GET['cid'])) {
			//$whereStr = "AND id=c.product_id AND c.category_id='".$_GET['cid']."'";
		//	$whereStr = "AND p.id=(SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp  WHERE cp.category_id=pc.category_id AND cp.category_id='".$_GET['cid']."' AND product_id=p.id AND cp.account_type_id='".$_SESSION['type_id']."')";
			//$whereStr = "AND p.id IN (SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp  WHERE cp.category_id=pc.category_id AND cp.category_id='".$_GET['cid']."' AND product_id=p.id AND cp.account_type_id='".$_SESSION['type_id']."')";
			$whereStr = "AND p.id=pc.product_id AND  cp.category_id=pc.category_id AND cp.category_id='".$_GET['cid']."' AND cp.account_type_id='".$_SESSION['type_id']."'";
		    $doAlt = true;
		    $doAlt2 = true;

		} else if(isset($PARAMS['cid'])) {
			//$whereStr = "AND p.id=(SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp  WHERE cp.category_id=pc.category_id AND cp.category_id='".$PARAMS['cid']."'  AND product_id=p.id AND cp. account_type_id='".$_SESSION['type_id']."')";
		//	$whereStr = "AND p.id IN (SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp  WHERE cp.category_id=pc.category_id AND cp.category_id='".$PARAMS['cid']."'  AND product_id=p.id AND cp. account_type_id='".$_SESSION['type_id']."')";
			
			$whereStr = "AND p.id=pc.product_id AND cp.category_id=pc.category_id AND cp.category_id='".$PARAMS['cid']."' AND cp.account_type_id='".$_SESSION['type_id']."'";
		    $doAlt = true;
		    $doAlt2 = true;
		} else {
			//$whereStr = "AND p.id=(SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp WHERE cp.category_id=pc.category_id AND product_id=p.id AND cp.account_type_id='".$_SESSION['type_id']."')";
			//$whereStr = "AND p.id IN (SELECT pc.product_id FROM `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp WHERE cp.category_id=pc.category_id AND product_id=p.id AND cp.account_type_id='".$_SESSION['type_id']."'";
			
			$whereStr = "AND p.id=pc.product_id AND cp.category_id=pc.category_id  AND cp.account_type_id='".$_SESSION['type_id']."'";
		    $doAlt = true;
		    $doAlt2 = true;

		}
	}

	$productCount = 0;

		if ($doAlt2) { 
		//print "<br>a<br>";
		
		  $sql = "SELECT COUNT(*) as 'count' FROM `shopping_cart_products` p, `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp WHERE `status`=1 ".$whereStr." AND `site_id`='".NUMO_SITE_ID."'";
		
		} else if ($doAlt) {	
		//print "<br>b<br>";
		  $sql = "SELECT COUNT(*) as 'count' FROM `shopping_cart_products` p, `shopping_cart_product_categories` pc WHERE `status`=1 ".$whereStr." AND `site_id`='".NUMO_SITE_ID."'";
		
		} else {
		  $sql = "SELECT COUNT(*) as 'count' FROM `shopping_cart_products` p WHERE `status`=1 ".$whereStr." AND `site_id`='".NUMO_SITE_ID."'";
		}		
		//print $sql;
		$result = $dbObj->query($sql);
//print mysql_error();
		if($row = mysql_fetch_array($result)) {
			$productCount = $row['count'];
		}

		if ($_GET['ob'] != "") {
			$orderBy = 'p.`'.htmlentities($_GET['ob']).'`';
		    if ($_GET['obd'] == "DESC") {
			  $orderBy .= ' DESC';

			} else if ($_GET['obd'] == "ASC" || $_GET['obd'] == "") {
			  $orderBy .= ' ASC';

			}

		} else {
			if ($settings['order_by_field'] == "") {
				$orderBy = 'p.slot_1';
			} else {
				$orderBy = 'p.'.$settings['order_by_field'];
				if ($_GET['obd'] == "DESC") {
				  $orderBy = str_replace("ASC", "DESC", $orderBy);
				} else if ($_GET['obd'] == "ASC" || $_GET['obd'] == "") {
				  $orderBy = str_replace("DESC", "ASC", $orderBy);
				}
			}
		}
		if ($doAlt2) {
		  $sql = "SELECT p.*,(SELECT COUNT(*) FROM `shopping_cart_optional_product_attributes` pa WHERE pa.`product_id`=p.`id`) as product_attrs FROM `shopping_cart_products` p,  `shopping_cart_product_categories` pc, `shopping_cart_category_permissions` cp    WHERE p.`status`=1 AND p.`site_id`='".NUMO_SITE_ID."' ".$whereStr." ORDER BY {$orderBy} LIMIT ".$startPosition.",".$itemsPerPage;
			
		} else if ($doAlt) {
		  $sql = "SELECT p.*,(SELECT COUNT(*) FROM `shopping_cart_optional_product_attributes` pa WHERE pa.`product_id`=p.`id`) as product_attrs FROM `shopping_cart_products` p,  `shopping_cart_product_categories` pc    WHERE p.`status`=1 AND p.`site_id`='".NUMO_SITE_ID."' ".$whereStr." ORDER BY {$orderBy} LIMIT ".$startPosition.",".$itemsPerPage;
		} else {
		  $sql = "SELECT p.*,(SELECT COUNT(*) FROM `shopping_cart_optional_product_attributes` WHERE `product_id`=p.`id`) as product_attrs FROM `shopping_cart_products` p  WHERE p.`status`=1 AND p.`site_id`='".NUMO_SITE_ID."' ".$whereStr." ORDER BY {$orderBy} LIMIT ".$startPosition.",".$itemsPerPage;
	    }
	  // SELECT p.*, i.file_name as 'image_name', i.description as 'image_description', (SELECT COUNT(*) FROM `shopping_cart_optional_product_attributes` WHERE `product_id`=p.`id`) as product_attrs FROM `shopping_cart_products` p LEFT JOIN (SELECT y.* FROM `shopping_cart_product_images` y INNER JOIN (SELECT * FROM shopping_cart_product_images ORDER BY id ASC) x ON (y.id=x.listing_id) GROUP BY y.listing_id) i ON (p.`id`=i.`listing_id`) WHERE p.`status`=1 AND p.`site_id`='1' ORDER BY p.`slot_1` LIMIT 0,10print $sql;
		//print $sql;
		$results = $dbObj->query($sql);

		if(mysql_num_rows($results) > 0) {
			$shoppingCartKeywords = "";
		    
			//$productCount = 0;
		?>
        <?php if ($settings['catalog_display'] == "1") { print "<div class='product_catalog_display_grid'>"; 
		} else { ?>
		<table class="product_catalog_display">
        <?php } ?>
		<?php

				while($row = mysql_fetch_array($results)) {
					$shoppingCartKeywords .= $row['slot_1'].",";
					//$productCount = $row['product_count'];
					$imageQuery = "SELECT * FROM `shopping_cart_product_images` WHERE listing_id='{$row['id']}' ORDER BY id ASC LIMIT 1";
				//	print $imageQuery;
					$imageResult = $dbObj->query($imageQuery);
					$imageRow = mysql_fetch_array($imageResult);
				if ($settings['catalog_display'] == "1") { 
				  print "<div class='product_catalog_display_grid_item'>"; 
				} else {
			      print "<tr>";
				}
				?>

				  				<?php
                if ($settings['catalog_display'] == "1") {  
					  print "<div class='product-image-container'>"; 
					  } else {
					  print "<td>";
					}
?>

				  <a href="?pid=<?=$row['id'].$getStringValues?>"><img class="product_thumbnail" src="<?php if (REMOTE_SERVICE === true) {
				if (is_null($imageRow['file_name'])) {
					print "http://".NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH."modules/shopping_cart/uploads/images/coming_soon_sm.jpg";
				} else {
					print "http://".NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH."uploads/modules/shopping_cart/".$imageRow['file_name'];
				}
			} else {
				print NUMO_FOLDER_PATH."modules/shopping_cart/".(is_null($imageRow['file_name']) ? 'images/coming_soon_sm.jpg' : 'uploads/'.$imageRow['file_name']);
			}
			?>" alt="<?=htmlentities($imageRow['description'])?>" title="<?=htmlentities($imageRow['description'])?>" /></a>
				<?php
                if ($settings['catalog_display'] == "1") {  
					  print "</div><div class='product-details'>"; 
					  } else {
					  print "</td><td>";
					}
?>
				<a href="?pid=<?=$row['id'].$getStringValues?>"><?=$row['slot_1']?></a>
							  <?php if ($row['slot_7'] != "") { ?>
					<p class='product_sku'><?php print $row['slot_7']?></p>
				  <?php } ?>
				<p style="font-size: 14px">
				<? if (is_numeric($row['slot_2'])) {
					 $afterDiscount = $row['slot_2'] - ($row['slot_2'] * ($shoppingCartDiscount / 100));
					 $supplementalBasePrice = 0;
					 // if the price of the product is zero, iterate through the available attributes to see if there is a "from" price available
					 if ($row['slot_2'] == 0 && $settings['zero_priced_display_when_attributes'] == "2" && $row['product_attrs'] > 0) {
						 $attrQuery = "SELECT pao.cost FROM shopping_cart_optional_product_attributes pa, shopping_cart_optional_product_attribute_options pao WHERE pa.product_id='{$row['id']}' AND pa.type='dropdown list' AND pa.id=pao.attribute_id AND pao.cost>0 ORDER BY pao.cost";
					     $attrResult = $dbObj->query($attrQuery);
						
						 $attrRecord = mysql_fetch_array($attrResult);
						 $supplementalBasePrice = $attrRecord['cost'];
					    
					 } 
					 
					 if ($row['slot_2'] == 0 && $row['product_attr'] > 0 && $settings['zero_priced_display_when_attributes'] == 1) {
						// don't show any price 
					 } else if ($row['slot_2'] == 0 && $row['product_attr'] > 0 && ($settings['zero_priced_display_when_attributes'] == 0 || $supplementalBasePrice == 0 || $settings['zero_priced_display'] == "")) {
						print ($slots['2']['name'].": ").$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
					 } else if ($row['slot_2'] == 0 && $supplementalBasePrice == 0) {
						if ($settings['zero_priced_display'] == 0 || $settings['zero_priced_display'] == "") {
						  print ($slots['2']['name'].": ").$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',');
							
						} else if ($settings['zero_priced_display'] == 1)  {
						  // down't show any price
						} else if ($settings['zero_priced_display'] == 2) {
						  print NUMO_SYNTAX_SHOPPING_CART_FREE_LABEL;	
						}
					 } else {
						 $priceField = "[price]";
						 if (NUMO_SYNTAX_SHOPPING_CART_FROM_PRICE !== "" && $supplementalBasePrice > 0 && $row['slot_2'] == 0 && $settings['zero_priced_display_when_attributes'] == 2) {
							 $priceField = NUMO_SYNTAX_SHOPPING_CART_FROM_PRICE;
						 }
						 $row['slot_2'] += $supplementalBasePrice;
						 
					  // show price AND the tax
					    if ($settings['tax_display_preference'] == 1) {
							if ($shoppingCartDiscount > 0) {
								if ($showOriginalPrice) {
								  print ($slots['2']['name'].": ")."<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span>";
								}
								print " <span>".str_replace("[price]", $slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
								$row['slot_2'] = $afterDiscount;
							} else {
								print ($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
							}

							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								print "<br>Plus ".$taxRates["{$taxRateID}"]["rate_name"]." (".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."%): ";
								print $slots['2']['options'].number_format($row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',');
							}
						   // print "should price and vat";

					 // show the price including the tax (tax on top of list price)
					 } else if ($settings['tax_display_preference'] == 2) {

							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								if ($shoppingCartDiscount > 0) {
									$afterDiscount = number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') - (number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',') * $shoppingCartDiscount / 100);
									if ($showOriginalPrice) {
									  print ($slots['2']['name'].": ")."<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ',')."</span>";
									}
									print " <span>".str_replace("[price]", $slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
									$row['slot_2'] = $afterDiscount;
								} else {
									print ($slots['2']['name'].": ").str_replace("[price]",$slots['2']['options'].number_format($row['slot_2'] + $row['slot_2']*$taxRates["{$row['slot_8']}"]["tax_rate"]/100, 2, '.', ','), $priceField);
								}

								//print $slots['2']['options'].;

								print "<br>Includes ".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."% ".$taxRates["{$taxRateID}"]["rate_name"];
							} else {
								if ($shoppingCartDiscount > 0) {
									if ($showOriginalPrice) {
									  print ($slots['2']['name'].": ")."<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
									}
									print "<span>".str_replace("[price]",$slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
								} else {
									print ($slots['2']['name'].": ").str_replace("[price]",$slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
								}

								//print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ','); 

							}
							
					 // show the price including the tax (tax included in list price)
					 } else if ($settings['tax_display_preference'] == 3) {

							$taxRateID = $row['slot_8'];
							if ($taxRateID > 0 && $taxRates["{$taxRateID}"]["tax_rate"] > 0) {
								if ($shoppingCartDiscount > 0) {
									$afterDiscount = number_format($row['slot_2'], 2, '.', ',') - (number_format($row['slot_2'], 2, '.', ',') * $shoppingCartDiscount / 100);
									if ($showOriginalPrice) {
									  print ($slots['2']['name'].": ")."<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span>";
									}
									print " <span>".str_replace("[price]", $slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
									$row['slot_2'] = $afterDiscount;
								} else {
									
									print ($slots['2']['name'].": ").str_replace("[price]",$slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
								}

								//print $slots['2']['options'].;

								print "<br>Includes ".clean_num($taxRates["{$taxRateID}"]["tax_rate"])."% ".$taxRates["{$taxRateID}"]["rate_name"];
							} else {
								if ($shoppingCartDiscount > 0) {
									if ($showOriginalPrice) {
									  print ($slots['2']['name'].": ")."<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
									}
									print "<span>".str_replace("[price]",$slots['2']['options'].number_format($afterDiscount, 2, '.', ','), $priceField)."</span>";
								} else {
									print ($slots['2']['name'].": ").str_replace("[price]",$slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
								}

								//print $slots['2']['options'].number_format($row['slot_2'], 2, '.', ','); 

							}

					 } else {
						if ($shoppingCartDiscount > 0) {
							if ($showOriginalPrice) {
							  print ($slots['2']['name'].": ")."<span style='text-decoration:line-through;'>".$slots['2']['options'].number_format($row['slot_2'], 2, '.', ',')."</span> ";
							}
							print "<span>".str_replace("[price]",$slots['2']['options'].number_format($afterDiscount, 2, '.', ','),$priceField)."</span>";
						} else {
							print ($slots['2']['name'].": ").str_replace("[price]", $slots['2']['options'].number_format($row['slot_2'], 2, '.', ','), $priceField);
						}

					 }
					 }
				} else {
					print 'N/A';
				}
				?></p><p style="font-size: 13px"><?=display_shopping_product_description($row['slot_3'])?>&nbsp;&nbsp;
				<br/><a style="font-weight: normal;" href="?pid=<?=$row['id'].$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_MORE_PRODUCT_DETAILS_LABEL?></a></p>
                <?php
				if ($settings['catalog_display'] == "1") {  
					  print "</div>"; 
					  } else {
					  print "</td>";
					}

				//direct to the product details page
				$select = "SELECT * FROM shopping_cart_product_stock WHERE site_id='".NUMO_SITE_ID."' AND `key`='{$row['id']}'";
				//print $select;
				$res = $dbObj->query($select);
				$stock = mysql_fetch_array($res);
				if ($stock['units'] == "0") { 
				if ($settings['catalog_display'] == "1") {  
					  print "<div class='out-of-stock-container out-of-stock'>".NUMO_SYNTAX_SHOPPING_CART_OUT_OF_STOCK_LABEL."</div></div>";
					  } else {
					  print "<td style='width: 150px; vertical-align:middle; text-align: right;' class='out-of-stock'>".NUMO_SYNTAX_SHOPPING_CART_OUT_OF_STOCK_LABEL."</td></tr>";
					}
				} else if ($row['slot_2'] == 0) {
					if ($settings['catalog_display'] == "1") {  
					  print "<div class='attribute-container'></div></div>"; 
					  } else {
					  print "<td style='width: 150px; vertical-align:middle; text-align: right; '></td></tr>";
					}
					
				 } else if($row['product_attrs'] > 0) {
					if ($settings['catalog_display'] == "1") {  
					  print "<div class='attribute-container'>"; 
					  } else {
					  print "<td style='width: 150px; vertical-align:middle; text-align: right;'>";
					}
					
				   ?>

				<a href="?pid=<?=$row['id'].$getStringValues?>" style="color: #fff; text-decoration: none;" class="product_catalog_display_price_box"><?=NUMO_SYNTAX_SHOPPING_CART_BUY_NOW_LABEL?></a>
                <?php
				if ($settings['catalog_display'] == "1") { 
				  print "</div></div>"; 
				} else {
			      print "</td></tr>";
				}
                
				//add item directly to cart
				} else {
				
                  if ($settings['catalog_display'] == "1") {  
				  print "<div class='add-to-cart-button-container'>"; 
				  } else {
			      print "<td style='width: 150px; vertical-align:middle; text-align: right;'>";
				}
				?>
				

				<a href="?product_id=<?=$row['id'].'&numo_cmd=add_to_cart'.$getStringValues?>" style="color: #fff; text-decoration: none;" class="product_catalog_display_price_box"><?=NUMO_SYNTAX_SHOPPING_CART_BUY_NOW_LABEL?></a>
                
               <?php if ($settings['catalog_display'] == "1") {  print "</div>"; } print "</td>"; ?>          
                <?php
				if ($settings['catalog_display'] == "1") { 
				  print "</div>"; 
				} else {
			      print "</tr>";
				}
				}
				?>
				<?php
				}

        	if ($settings['catalog_display'] == "1") { 
				  print "<div class='numo_catalog_back_next_grid_container' style='text-align:center'>"; 
				  ?>
                  <?php if($pageNumber > 0) { ?><a href="?page=<?=($pageNumber - 1).$searchTerms.$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_CATALOG_BACK_LINK_LABEL?></a><?php } ?> <?php if($pageNumber > 0 && ($startPosition + $itemsPerPage)  < $productCount) { ?> | <?php } ?> <?php if(($startPosition + $itemsPerPage) < $productCount) { ?> <a href="?page=<?=($pageNumber + 1).$searchTerms.$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_CATALOG_NEXT_LINK_LABEL?></a><?php } ?>

                  <?
				} else {
			      print "<tr>"
				  ?>
                  		<td colspan="3" style="text-align: center;" class="numo_catalog_back_next"><?php if($pageNumber > 0) { ?><a href="?page=<?=($pageNumber - 1).$searchTerms.$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_CATALOG_BACK_LINK_LABEL?></a><?php } ?> <?php if($pageNumber > 0 && ($startPosition + $itemsPerPage)  < $productCount) { ?> | <?php } ?> <?php if(($startPosition + $itemsPerPage) < $productCount) { ?> <a href="?page=<?=($pageNumber + 1).$searchTerms.$getStringValues?>"><?=NUMO_SYNTAX_SHOPPING_CART_CATALOG_NEXT_LINK_LABEL?></a><?php } ?></td>
                  <?php
				}

        	if ($settings['catalog_display'] == "1") { 
				  print "</div>"; 
				} else {
			      print "</tr>";
				}

		 if ($settings['catalog_display'] == "1") { print "</div>"; 
		} else { ?>
        </table>
		<?php
		}
		//print "hello";
	    //$categoryDescription = str_replace("[Category Name]
		define("SYSTEM_META_TITLE", $categoryTitle);
		define("SYSTEM_META_DESCRIPTION", $categoryDescription);
		define("SYSTEM_META_KEYWORDS",   str_replace("[Product Names]", trim($shoppingCartKeywords, ","), $shoppingCartKeywordString));

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

?>