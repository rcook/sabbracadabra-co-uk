<?php

include_once("numo/modules/accounts/classes/Account.php");
include_once("numo/modules/shopping_cart/classes/Product.php");
include_once("numo/modules/shopping_cart/classes/Discount.php");

class Order {
	function Order($id = "") {
	  $this->currencyOptionsShort = array("AUD" => "$","CAD" => "$","EUR" => "&#128;","NZD" => "$","GBP" => "&#163;","USD" => '$');
	  $this->items = array();
	  if ($id != "") {
		  $this->load($id);
	  }


	  $this->billingFormErrors = array();
	  $this->shippingFormErrors = array();
	  $this->creditCardFormErrors = array();



	}

	function setCouponCode($couponCode) {
		$coupons = $this->getAvailableCoupons();
		if (array_key_exists($couponCode, $coupons)) {
		  $this->attributes['coupon_code'] = $couponCode;
		}
	}

	function load($id) {
	  global $dbObj;

      $this->items = array();

	  $query = "SELECT * FROM `shopping_cart_orders` WHERE id='{$id}'";
	  $dbObj->set_charset("latin1");
	  $result = $dbObj->query($query);
	  $this->attributes = mysql_fetch_array($result);
	  $dbObj->set_charset("utf8");

	  $query = "SELECT oi.*, p.slot_1, p.slot_2, p.shipping, p.shipping2 FROM `shopping_cart_order_items` oi, shopping_cart_products p WHERE oi.product_id=p.id AND oi.order_id='{$id}'";
	  $result = $dbObj->query($query);
	  while ($orderItem =  mysql_fetch_array($result)) {
		$itemID = $orderItem['id'];
		$this->items["{$itemID}"] = $orderItem;
		$this->items["{$itemID}"]['attributes'] = array();

	  	$query = "SELECT * FROM `shopping_cart_order_item_attributes` WHERE order_item_id='{$itemID}'";
	  	$attributeResult = $dbObj->query($query);
	  	while ($orderItemAttribute =  mysql_fetch_array($attributeResult)) {
			$attributeID = $orderItemAttribute['id'];
			$this->items["{$itemID}"]['attributes']["$attributeID"] = $orderItemAttribute;
		}

	  }

	  // get system taxation settings
	  $query = "SELECT * FROM shopping_cart_settings WHERE site_id='".NUMO_SITE_ID."'";
	  $result = $dbObj->query($query);
	  $record = mysql_fetch_array($result);

	  $this->attributes['tax_display_preference']            = $record['tax_display_preference'];
	  $this->attributes['shipping_taxation_rate']            = $record['shipping_taxation_rate'];
	  $this->attributes['send_admin_email_order_pending']    = $record['send_admin_email_order_pending'];
	  $this->attributes['send_admin_email_order_completed']  = $record['send_admin_email_order_completed'];
	  $this->attributes['offline_collect_billing_address']   = $record['offline_collect_billing_address'];
	  $this->attributes['offline_collect_shipping_address']  = $record['offline_collect_shipping_address'];
	  $this->attributes['packing_slip_address']				 = $record['packing_slip_address'];



	  // get system taxation settings
	  $query = "SELECT * FROM shopping_cart_fields WHERE input_type='money' AND  slot='2' AND site_id='".NUMO_SITE_ID."'";
	  $result = $dbObj->query($query);
	  $record = mysql_fetch_array($result);

	  $this->attributes['cart_currency'] = $record['input_options'];
	  $currency = $this->attributes['cart_currency'];
	  $this->attributes['currency_symbol'] = $this->currencyOptionsShort["$currency"];

	  // load taxes into system
	  $query = "SELECT * FROM shopping_cart_taxes WHERE site_id='".NUMO_SITE_ID."'";
	  $result = $dbObj->query($query);
	  $this->taxRates = array();
	  while ($record = mysql_fetch_array($result)) {
	    $taxRateID = $record['tax_rate_id'];
		$this->taxRates["{$taxRateID}"] = $record;
	  }
	}


  function processCreditCardForm($data) {
	  $cardType = isValidCardNumber($data['credit_card_number']);

	  if (!$cardType) {
		  $this->creditCardFormErrors['credit_card_number'] = true;
	  }

	  if ($data['cardholder_name'] == "") {
		  $this->creditCardFormErrors['cardholder_name'] = true;
	  }

	  if ($data['cvv'] == "" ||
				($cardType == "visa" && strlen($data['cvv']) != 3) ||
				($cardType == "mastercard" && strlen($data['cvv']) != 3) ||
				($cardType == "diners" && strlen($data['cvv']) != 3) ||
				($cardType == "discover" && strlen($data['cvv']) != 3) ||
				($cardType == "amex" && strlen($data['cvv']) != 4)) {
		  $this->creditCardFormErrors['cvv'] = true;
	  }

	  // validate date
	  if ($data['expiry_date_year'] == date("Y") && $data['expiry_date_month'] < date("m")) {
		  $this->creditCardFormErrors['expiry_date'] = true;
		  $this->creditCardFormErrors['expiry_date_month'] = true;
		  if ($data['expiry_date_month'] == 12) {
		    $this->creditCardFormErrors['expiry_date_year'] = true;
		  }
	  }
  }

  function processBillingForm($data) {
	  if ($data['billing_attention'] == "") {
		  $this->billingFormErrors['billing_attention'] = true;
	  }
	  if ($data['billing_street_address'] == "") {
		  $this->billingFormErrors['billing_street_address'] = true;
	  }
	  if ($data['billing_city'] == "") {
		  $this->billingFormErrors['billing_city'] = true;
	  }

	  // validate postal codes
	  if (($data['billing_country'] == "US" && !preg_match('/^\d{5}(-\d{4})?$/', $data['billing_zip'])) ||
	      ($data['billing_country'] == "CA" && !preg_match('/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/', $data['billing_zip']))) {
		  $this->billingFormErrors['billing_zip'] = true;

	  }


	  if ($data['billing_state'] == "" && ($data['billing_country'] == "US" || $data['billing_country'] == "CA" || $data['billing_country'] == "AU")) {
		  $this->billingFormErrors['billing_state'] = true;
	  } else if (($data['billing_country'] == "US" && !array_key_exists($data['billing_state'], getAmericanStatesArray())) ||
				 ($data['billing_country'] == "CA" && !array_key_exists($data['billing_state'], getCanadianProvincesArray())) ||
				 ($data['billing_country'] == "AU" && !array_key_exists($data['billing_state'], getAustralianProvincesArray()))) {
			  $this->billingFormErrors['billing_state'] = true;
	  }


	  if ($data['billing_country'] == "") {
		  $this->billingFormErrors['billing_country'] = true;
	  }

  }

  function processShippingForm($data) {
	  if ($data['shipping_attention'] == "") {
		  $this->shippingFormErrors['shipping_attention'] = true;
	  }
	  if ($data['shipping_street_address'] == "") {
		  $this->shippingFormErrors['shipping_street_address'] = true;
	  }
	  if ($data['shipping_city'] == "") {
		  $this->shippingFormErrors['shipping_city'] = true;
	  }
	  if ($data['shipping_country'] == "CA") {
		  $data['shipping_zip'] = strtoupper($data['shipping_zip']);
		  $_POST['shipping_zip'] = strtoupper($data['shipping_zip']);
	  }

	  // validate postal codes
	  if (($data['shipping_country'] == "US" && !preg_match('/^\d{5}(-\d{4})?$/', $data['shipping_zip'])) ||
	      ($data['shipping_country'] == "CA" && !preg_match('/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$/', $data['shipping_zip']))) {
		  $this->shippingFormErrors['shipping_zip'] = true;

	  }

	  if ($data['shipping_state'] == "" && ($data['shipping_country'] == "US" || $data['shipping_country'] == "CA" || $data['shipping_country'] == "AU")) {
		  $this->shippingFormErrors['shipping_state'] = true;
	  } else if (($data['shipping_country'] == "US" && !array_key_exists($data['shipping_state'], getAmericanStatesArray())) ||
				 ($data['shipping_country'] == "CA" && !array_key_exists($data['shipping_state'], getCanadianProvincesArray())) ||
				 ($data['shipping_country'] == "AU" && !array_key_exists($data['shipping_state'], getAustralianProvincesArray()))) {
			  $this->shippingFormErrors['shipping_state'] = true;
	  }


	  if ($data['shipping_country'] == "") {
		  $this->shippingFormErrors['shipping_country'] = true;

	  }

  }

  // 0 = pending
  // 1 = completed
  function setOrderPrimaryStatus($status) {
	  global $dbObj;

	  $update = "UPDATE shopping_cart_orders SET processed='{$status}' WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
	  $this->attributes['processed'] = $status;
  }

  // true/false
  function setOrderPaymentStatus($status) {
	  global $dbObj;

	  $update = "UPDATE shopping_cart_orders SET payment_status='{$status}' WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
	  $this->attributes['payment_status'] = $status;

  }

  function setOrderPaymentMethod($method) {
	  global $dbObj;

	  $update = "UPDATE shopping_cart_orders SET payment_type='{$method}' WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
	  $this->attributes['payment_type'] = $method;

  }


  function setOrderRetryTrace($trace) {
	  global $dbObj;

	  $update = "UPDATE shopping_cart_orders SET retry_trace='{$trace}' WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
	  $this->attributes['retry-trace'] = $trace;

  }

  function process($method, $data) {
	global $dbObj;

	$customerAccount = new Account($this->attributes['account_id']);

	if ($method == "manual") {
		$query = "SELECT * FROM shopping_cart_orders WHERE retry_trace='{$data['retry-trace']}'";
		$result = $dbObj->query($query);
		if (mysql_num_rows($result) == 0) {
			$this->setOrderRetryTrace($data['retry-trace']);
			$this->setOrderPrimaryStatus(1);
			$this->setOrderPaymentStatus("Pending");
			$this->updateOrderDate();
			$this->updateOrderTotal($this->calculateOrderTotal());

			if ($this->attributes['offline_collect_shipping_address'] == 1) {
			  $this->updateShippingDetails($data);
			} else {
			  $this->updateShippingDetails($customerAccount->getShippingData());
			}

			if ($this->attributes['offline_collect_billing_address'] == 1) {
			  $this->updateBillingDetails($data);
			} else {
			  $this->updateBillingDetails($customerAccount->getBillingData());
			}

			$this->updateOrderPaymentMethod($data);

			$this->updateItemsStock($data);

			// send a receipt of order confirmation
			$this->sendCustomerReceiptOfOrder();

			// send a notification email to the webmaster
			if ($this->attributes['send_admin_email_order_completed'] == 1) {
				$this->sendAdminNotificationOfCompletedOrder();
			}
		}

	} else if ($method == "paypal") {
		// send a receipt of order confirmation
		$this->sendCustomerReceiptOfOrder();

		// send a notification email to the webmaster
		if ($this->attributes['send_admin_email_order_completed'] == 1) {
			$this->sendAdminNotificationOfCompletedOrder();
		}
	}



  }

  function updateItemsStock($data) {
	  global $dbObj;

			// if payment status changed to 'complete' then update stock
				foreach($this->items as $itemId => $item) {
					$quantity = $item['quantity'];
					$stockId = "";
					 $product = new Product($item['product_id']);


					 foreach ($item['attributes'] as $attributeID => $attribute) {
					   $aid = $attribute['attribute_id'];
					   if ($product->attributes["{$aid}"]["type"] == "dropdown list") {
					     $stockId .= "-".$attribute['value'];
					   }
					 }

					$sql = "SELECT `product_id` FROM `shopping_cart_order_items` WHERE `id`='".$itemId."'";
					$result = $dbObj->query($sql);

					if($row = mysql_fetch_array($result)) {
						// add product id to the start of the stock key
						$stockId = $row['product_id'].$stockId;

						$sql = "UPDATE `shopping_cart_product_stock` SET `units`=`units` - ".$quantity." WHERE `key`='".$stockId."'";
	  					//logLine($logging, $logFile, "SQL: {$sql}");
						//print $sql."<br>";
						$dbObj->query($sql);

						// also do update if it is an egood
						$query = "SELECT * FROM shopping_cart_products WHERE `id`='".$row['product_id']."'";
						//logLine($logging, $logFile, "SQL: {$query}");
						$scpResult = $dbObj->query($query);
						$prodRecord = mysql_fetch_array($scpResult);

						// 0 = cost based shipping
						// 1 = weight based shipping
						// 2 = egood
						//logLine($logging, $logFile, "shipping method: {$prodRecord['slot_5']}");

						if ($prodRecord['slot_5'] == "2") {
							$fieldValueData = explode("\r\n", $prodRecord['slot_6']);
							$fValues = array();

							foreach ($fieldValueData as $data) {
								//logLine($logging, $logFile, "egood:: {$data}");

								$dataX = explode(":", $data);
								$dataKey = $dataX[0];
								$dataValue = $dataX[1];
								if ($dataKey == "accounts") {
									if ($dataValue == "" || $dataValue == "0") {
										// do nothing
									} else {
										$acct = new Account($accountID);
										$acct->changeGroup($dataValue);

								//		logLine($logging, $logFile, "eGood - Upgrade account to user type: $dataValue");

										$acct->update(array("pending" => "0", "activated" => "1"));

									}

								} else if ($dataKey == "access_control") {
									$files = explode(",", $dataValue);
									if (is_array($files)) {
										foreach ($files as $fileID) {
											if ($fileID != "") {
												$dbObj->query("DELETE FROM `user_permissions` WHERE account_id='{$accountID}' AND protected_file_id='{$fileID}'");

												$sql = "INSERT INTO `user_permissions` (`account_id`,`protected_file_id`,`show_on_menu`)
														  VALUES ('{$accountID}','{$fileID}',1)";
												$dbObj->query($sql);

											//	logLine($logging, $logFile, "eGood - Grant Access to Page: $fileID");

											}
										}
									}

								} else if ($dataKey == "newsletter") {
									$lists = explode(",", $dataValue);
									if (is_array($lists)) {
										foreach ($lists as $listID) {
											if ($listID != "") {
												$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('{$accountID}','{$listID}')";
												@$dbObj->query($sql);

												//logLine($logging, $logFile, "eGood - Newsletter Subscription List: $fileID");

											}
										}
									}

								} else if ($dataKey == "listing_service") {
									$contributorQuery = "SELECT * FROM `listing_contributor_profiles` WHERE `account_id`='{$accountID}''";
									$contributorResult = $dbObj->query($contributorQuery);

									if (mysql_num_rows($contributorResult) > 0) {
										$contributorRecord = mysql_fetch_array($contributorResult);
										$allowedTypes = explode(",", $contributorRecord['allowed_types']);
										$realAllowedTypes = array();
										foreach ($allowedTypes as $x) {
											$realAllowedTypes["{$x}"] = true;
										}
										$allowedTypes = explode(",", $dataValue);
										foreach ($allowedTypes as $x) {
											$realAllowedTypes["{$x}"] = true;
										}
										$allowedTypesStr = "";
										foreach ($realAllowedTypes as $x => $y) {
											$allowedTypesStr .= $y.",";
										}
										$allowedTypesStr = rtrim($allowedTypesStr, ",");
										$sql = "UPDATE `listing_contributor_profiles` SET `allowed_types`='{$allowedTypesStr}' WHERE `account_id`='{$accountID}'";

									} else {
										$sql = "INSERT INTO `listing_contributor_profiles`
										(`account_id`,`when_created`,`allowed_types`) VALUES
										('{$accountID}','".date('y/m/d H:i:s')."','{$dataValue}')";
									}
									//logLine($logging, $logFile, "SQL: {$sql}");

									$dbObj->query($sql);

								}
							}
						} else {

						}
					} else {
  					//  logLine($logging, $logFile, "ISSUE: Record Not Found");

					}
				}



  }

  function updateOrderPaymentMethod($data) {
	  global $dbObj;

	if ($data['type'] == "credit_card") {
	  $this->setOrderPaymentMethod("Manual/Credit Card");

	  $update = "UPDATE shopping_cart_orders SET ".
	  			"account_number='".$this->encrypt($data['credit_card_number'])."', ".
	  			"account_verification_number='".$this->encrypt($data['cvv'])."', ".
	  			"account_name='{$data['cardholder_name']}', ".
	  			"account_expiry_date='{$data['expiry_date_month']}/{$data['expiry_date_year']}' ".
				"WHERE id='{$this->attributes['id']}'";
	  $dbObj->set_charset("latin1");
	  $dbObj->query($update);
	 // print $update;
	  $dbObj->set_charset("utf8");

      print mysql_error();

	} else if ($data['type'] == "invoice") {
	  $this->setOrderPaymentMethod("Manual/Invoice");

	} else if ($data['type'] == "purchase_order") {
	  $this->setOrderPaymentMethod("Manual/Purchase Order");
	  $update = "UPDATE shopping_cart_orders SET ".
	  			"account_number='{$data['purchase_order_number']}', ".
	  			"account_verification_number='', ".
	  			"account_name='', ".
	  			"account_expiry_date='' ".
				"WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
	} else if ($data['type'] == "check") {
	  $this->setOrderPaymentMethod("Manual/Check");
	}
	/*
	print "cc number before: {$data['credit_card_number']}<br>";
	$query = "select account_number from shopping_cart_orders WHERE id='{$this->attributes['id']}'";
	print $query;
	  $dbObj->set_charset("latin1");
	  $result = $dbObj->query($query);
	  print $update;
	  $record = mysql_fetch_array($result);
	  $dbObj->set_charset("utf8");
	  print $record['account_number'];
	print "cc number after1: ".$this->decrypt($record['account_number'])."<br>";
	print "cc number after2: ".$this->decrypt($this->encrypt($data['credit_card_number']))."<br>";
	*/


  }

  function sendCustomerReceiptOfPayment() {
	  global $dbObj;
	  $customerAccount = new Account($this->attributes['account_id']);


	  // admin email
	 // $to = NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS;
	  $to = $customerAccount->attributes['slot_3'];
	  $subject = str_replace("[Order ID]", $this->attributes['id'], NUMO_SYNTAX_SHOPPING_CART_PAYMENT_RECEIVED_EMAIL_SUBJECT);
	  $headers = "From: ".NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
	  $headers .= 'Content-Type: text/html; charset="iso-8859-1"'."\r\n";

	  $paymentTypeInstructions = "";
	  if ($this->attributes['payment_type'] == "Manual/Credit Card") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_DETAILS_DESCRIPTION;
	  } else if ($this->attributes['payment_type'] == "Manual/Purchase Order") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_PURCHASE_ORDER_DETAILS_DESCRIPTION;
	  } else if ($this->attributes['payment_type'] == "Manual/Invoice") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_INVOICE_DETAILS_DESCRIPTION;
	  } else if ($this->attributes['payment_type'] == "Manual/Check") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CHECK_DETAILS_DESCRIPTION;
	  }


	  $message = NUMO_SYNTAX_SHOPPING_CART_PAYMENT_RECEIVED_EMAIL_MESSAGE;
	  $message = str_replace("[Payment Type Instructions]", $paymentTypeInstructions, $message);
	  $message = str_replace("[Company Address]", $this->attributes['packing_slip_address'], $message);
	  $message = str_replace("[Customer Name]", $customerAccount->attributes['slot_4'], $message);
	  $message = str_replace("[Order Total]", $this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($this->attributes['mc_gross'], '2', '.', ''), $message);
	  $message = str_replace("[Order ID]", $this->attributes['id'], $message);
	  $message = str_replace("[Order Items]", $this->getItemsTable(), $message);


  	  mail($to, $subject, $message, $headers);

  }

  function sendCustomerReceiptOfOrder() {
	  global $dbObj;
	  $customerAccount = new Account($this->attributes['account_id']);


	  // admin email
	//  $to = NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS;
	  $to = $customerAccount->attributes['slot_3'];
	  $subject = str_replace("[Order ID]", $this->attributes['id'], NUMO_SYNTAX_SHOPPING_CART_ORDER_RECEIVED_EMAIL_SUBJECT);
	  $headers = "From: ".NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
	  $headers .= 'Content-Type: text/html; charset="iso-8859-1"'."\r\n";

	  $paymentTypeInstructions = "";
	  if ($this->attributes['payment_type'] == "Manual/Credit Card") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CART_CREDIT_CARD_DETAILS_DESCRIPTION;
	  } else if ($this->attributes['payment_type'] == "Manual/Purchase Order") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_PURCHASE_ORDER_DETAILS_DESCRIPTION;
	  } else if ($this->attributes['payment_type'] == "Manual/Invoice") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_INVOICE_DETAILS_DESCRIPTION;
	  } else if ($this->attributes['payment_type'] == "Manual/Check") {
		  $paymentTypeInstructions = NUMO_SYNTAX_SHOPPING_CHECK_DETAILS_DESCRIPTION;
	  }

	  $message = NUMO_SYNTAX_SHOPPING_CART_ORDER_RECEIVED_EMAIL_MESSAGE;
	  $message = str_replace("[Payment Type Instructions]", $paymentTypeInstructions, $message);
	  $message = str_replace("[Company Address]", $this->attributes['packing_slip_address'], $message);
	  $message = str_replace("[Customer Name]", $customerAccount->attributes['slot_4'], $message);
	  $message = str_replace("[Order Total]", $this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($this->attributes['mc_gross'], '2', '.', ''), $message);
	  $message = str_replace("[Order ID]", $this->attributes['id'], $message);
	  $message = str_replace("[Order Items]", $this->getItemsTable(), $message);

	 $message = str_replace("[Payment Type Instructions]", $paymentTypeInstructions, $message);

  	  mail($to, $subject, $message, $headers);

  }


  function sendAdminNotificationOfPendingOrder() {
	  global $dbObj;
	  $customerAccount = new Account($this->attributes['account_id']);


	  // admin email
	  $to = NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS;
	  $subject = str_replace("[Order ID]", $this->attributes['id'], NUMO_SYNTAX_SHOPPING_CART_ORDER_PENDING_ADMIN_EMAIL_SUBJECT);
	  $headers = "From: ".NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
	  $headers .= 'Content-Type: text/html; charset="iso-8859-1"'."\r\n";

	  $message = NUMO_SYNTAX_SHOPPING_CART_ORDER_PENDING_ADMIN_EMAIL_MESSAGE;
	  $message = str_replace("[Payment Method]", $this->attributes['payment_method'], $message);
	  $message = str_replace("[Customer Name]", $customerAccount->attributes['slot_4'], $message);
	  $message = str_replace("[Order Total]", $this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($this->calculateOrderTotal(), 2, '.', ''), $message);
	  $message = str_replace("[Order ID]", $this->attributes['id'], $message);
	  $message = str_replace("[Order Items]", $this->getItemsTable(), $message);

  	  mail($to, $subject, $message, $headers);
  }

  function sendAdminNotificationOfCompletedOrder() {
	  global $dbObj;
	  $customerAccount = new Account($this->attributes['account_id']);


	  // admin email
	  $to = NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS;
	  $subject = str_replace("[Order ID]", $this->attributes['id'], NUMO_SYNTAX_SHOPPING_CART_ORDER_COMPLETED_ADMIN_EMAIL_SUBJECT);
	  $headers = "From: ".NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
	  $headers .= 'Content-Type: text/html; charset="iso-8859-1"'."\r\n";

	  $message = NUMO_SYNTAX_SHOPPING_CART_ORDER_COMPLETED_ADMIN_EMAIL_MESSAGE;
	  $message = str_replace("[Payment Method]", $this->attributes['payment_method'], $message);
	  $message = str_replace("[Customer Name]", $customerAccount->attributes['slot_4'], $message);
	  $message = str_replace("[Order Total]", $this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($this->attributes['mc_gross'], '2', '.', ''), $message);
	  $message = str_replace("[Order ID]", $this->attributes['id'], $message);
	  $message = str_replace("[Order Items]", $this->getItemsTable(), $message);

  	  mail($to, $subject, $message, $headers);
  }

  function getItemsTable() {
	 $html = "<table style='max-width: 600px; width: 100%'>";
		 $html .= "<tr>";
		 $html .= "<th style='border-bottom: 2px solid #aaaaaa;'>#</th>";
		 $html .= "<th style='border-bottom: 2px solid #aaaaaa; text-align: left; padding-left: 5px; padding-right: 10px;'>Item</th>";
		 $html .= "<th style='border-bottom: 2px solid #aaaaaa; text-align: left; padding-left: 5px; padding-right: 10px;'>&nbsp;</th>";
		 $html .= "<th style='border-bottom: 2px solid #aaaaaa; width: 100px;'>Unit Cost</td>";
		 $html .= "<th style='border-bottom: 2px solid #aaaaaa; width: 100px;'>Total</td>";
		 $html .= "</tr>";
		 $rowCount = 1;
	 foreach ($this->items as $itemID => $item) {
		 $attributes = "";
	     $product = new Product($item['product_id']);


		 foreach ($item['attributes'] as $attributeID => $attribute) {
		   $aid = $attribute['attribute_id'];

		   $attributes .= $product->attributes["{$aid}"]['label'].": ".$attribute['value']."<br>";;
		 }
		 $rowStyle = "1px solid #cccccc";
		 if ($rowCount++ >= sizeof($this->items)) {
			 $rowStyle = "2px solid #aaaaaa";
		 }
		// print $rowCount;
		 $html .= "<tr>";
		 $html .= "<td style='border-bottom: {$rowStyle};'>{$item['quantity']}</td>";
		 $html .= "<td style='border-bottom: {$rowStyle};'>{$item['slot_1']}</td>";
		 $html .= "<td style='border-bottom: {$rowStyle}'>{$attributes}</td>";
		 $html .= "<td style='border-bottom: {$rowStyle}; text-align: right; padding-right: 10px; padding-left: 10px;'>".$this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($this->getItemTotal($itemID, false), 2, '.', '')."</td>";
		 $html .= "<td style='border-bottom: {$rowStyle}; text-align: right; padding-right: 10px; padding-left: 10px;'>".$this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($this->getItemTotal($itemID), 2, '.', '')."</td>";
		 $html .= "</tr>";
	 }
	 $taxTotal = $this->getOrderTax();
	 $itemsTotal = $this->getItemSubtotal();
	 $shippingTotal = $this->getShippingTotal();
	     if ($taxTotal + $shippingTotal > 0) {
			 // subtotal
			 $html .= "<tr>";
			 $html .= "<td>&nbsp;</td>";
			 $html .= "<td>&nbsp;</td>";
			 $html .= "<td  colspan='2' style='margin-top: 0px; border-bottom: 1px solid #cccccc; text-align: right; padding-right: 10px;'>Sub-Total</td>";
			 $html .= "<td style='margin-top: 0px; border-bottom: 1px solid #cccccc; text-align: right; padding-right: 10px; padding-left: 10px;'>".$this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($itemsTotal, 2, '.', '')."</td>";
			 $html .= "</tr>";
		 }
		 if ($shippingTotal > 0) {
			 // shipping
			 $html .= "<tr>";
			 $html .= "<td>&nbsp;</td>";
			 $html .= "<td>&nbsp;</td>";
			 $html .= "<td colspan='2' style='border-bottom: 1px solid #cccccc; text-align: right; padding-right: 10px;'>Shipping</td>";
			 $html .= "<td style='border-bottom: ".($taxTotal == 0 ? 2 : 1)."px solid #cccccc; text-align: right; padding-right: 10px; padding-left: 10px;'>".$this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($shippingTotal, 2, '.', '')."</td>";
			 $html .= "</tr>";
  		 }
         if ($taxTotal > 0) {
			 // taxes
			 $html .= "<tr>";
			 $html .= "<td>&nbsp;</td>";
			 $html .= "<td>&nbsp;</td>";
			 if ($this->attributes['tax_display_preference'] == 3) {
			   $html .= "<td  colspan='2' style='border-bottom: 2px solid #aaaaaa; text-align: right; padding-right: 10px;'>Taxes Included</td>";
			 } else {
			   $html .= "<td  colspan='2' style='border-bottom: 2px solid #aaaaaa; text-align: right; padding-right: 10px;'>Taxes</td>";
			 }
			 $html .= "<td style='border-bottom: 2px solid #aaaaaa; text-align: right; padding-right: 10px; padding-left: 10px;'>".$this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($taxTotal, 2, '.', '')."</td>";
			 $html .= "</tr>";
		 }
	  	 // order total
		 $html .= "<tr>";
		 $html .= "<td>&nbsp;</td>";
		 $html .= "<td>&nbsp;</td>";
		 $html .= "<td style='text-align: right; padding-right: 10px;' colspan='2' ><b>Total</b></td>";
		 $html .= "<td style='font-weight: bold; padding-left: 10px; text-align: right; padding-right: 10px;'>".$this->currencyOptionsShort["{$this->attributes['cart_currency']}"].number_format($this->calculateOrderTotal(), 2, '.', '')."</td>";
		 $html .= "</tr>";

	  $html .= "</table>";
	  //print $html;
	  return $html;
	}

  function encrypt($whatToEncrypt) {
    $key = $this->attributes['id'];
	//$key = mktime();
    $key1 = $key;
	$key2 = $key % 2;
	$key3 = $key % 3;
	$key4 = $key % 5;

	$totalSalt = $key1 + $key2 + $key3 + $key4;
	//print "salt is: $totalSalt<br>";;
	//print "original key is: $key<br>";;
	$modifiedNumber = number_format($whatToEncrypt - $totalSalt, 0, '', '');
	$ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB);
	$iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
	$encryptedCardNumber = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $totalSalt, $modifiedNumber, MCRYPT_MODE_CFB, $iv);
    //print "iv: $iv <br><br>";
	//print "tweeked: $modifiedNumber <br>";
	//print "ivSize: $ivSize"."<br>";
	//print "ivSize2: ".strlen($iv)."<br>";

	$encryptedString = $iv.$encryptedCardNumber;
	return $encryptedString;
  }

 function decrypt($startCardNumber) {
    $key = $this->attributes['id'];

    $key1 = $key;
	$key2 = $key % 2;
	$key3 = $key % 3;
	$key4 = $key % 5;

	$totalSalt = $key1 + $key2 + $key3 + $key4;

	$iv = substr($startCardNumber, 0, 32);

	$encryptedCardNumber = str_replace($iv, "", $startCardNumber);

	$decryptedCardNumber = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $totalSalt, $encryptedCardNumber, MCRYPT_MODE_CFB, $iv));

	$cardNumber = number_format($decryptedCardNumber + $totalSalt, 0, '', '');

	return $cardNumber;
  }


  function getItemSubtotal($subtractDiscount = false) {
	$itemTotal = 0;
	if ($subtractDiscount) {
		//print "uh huh";
	}
	foreach ($this->items as $itemID => $item) {
	  $itemTotal += $this->getItemTotal($itemID, true, $subtractDiscount);
	}


	return $itemTotal;
  }

  function getItemTotal($itemID, $multiplyByQuantity = true, $includeDiscount = false) {
	  $item = $this->items["$itemID"];
	  $product = new Product($item['product_id']);
	  $baseProductPrice = $product->attributes['slot_2'];

	  foreach ($item['attributes'] as $cartAttributeID => $attribute) {
		$productAttribute = $product->attributes["{$attribute['attribute_id']}"];

		if ($productAttribute['type'] == "dropdown list") {
		  $selectedAttributeOption = $attribute['value'];

		  $selectedAttributePrice = $productAttribute['options']["$selectedAttributeOption"]["cost"];
		  $baseProductPrice += $selectedAttributePrice;
		}
	  }
	  if ($includeDiscount && $item['discount'] != "") {
		  // based off of $ value
		  if ($item['discount']->attributes['amount_type'] == "0") {
		    //  we don't take it off of the item, because it comes off as a rebate

		  // based off of %
		  } else if ($item['discount']->attributes['amount_type'] == "1") {
			  $discount = $item['discount']->getRebateAmount($baseProductPrice) * ($multiplyByQuantity ? $item['quantity'] : 1);
		  }
		 // $baseProductPrice = $baseProductPrice - ;
	  }
	  return $baseProductPrice * ($multiplyByQuantity ? $item['quantity'] : 1) - $discount;
  }

  function getItemShipping($itemID) {
	  $item = $this->items["$itemID"];
	  $shippingTotal = 0;
	  $product = new Product($item['product_id']);
	  if ($product->attributes['slot_5'] == "0") {
		  $baseShippingAmount      = $product->attributes['shipping'];
		  $secondaryShippingAmount = $product->attributes['shipping2'];

		  $baseItemShippingTotal = $baseShippingAmount;
		  $secondaryShippingQuantity = $item['quantity'] - 1;
		  if ($secondaryShippingQuantity > 0) {
			  $baseItemShippingTotal += ($secondaryShippingQuantity * $secondaryShippingAmount);
		  }

		  $shippingTotal += $baseItemShippingTotal;
	  }

	  return $shippingTotal;

  }

  function getShippingTotal() {
	$shippingTotal = 0;

	foreach ($this->items as $itemID => $item) {
		$shippingTotal += $this->getItemShipping($itemID);
	}
	return $shippingTotal;
  }

  function getOrderTax($includeDiscount = false) {
	$orderTax = 0;

	foreach ($this->items as $itemID => $item) {
	  $product = new Product($item['product_id']);
	  $taxField = $product->attributes['slot_8'];
	  $itemTaxAmount = 0;
	  if ($this->taxRates["$taxField"] != "") {
		  $productTaxRate = $this->taxRates["$taxField"]["tax_rate"];
		  // net tax -- (taxes on top)
		  if ($this->attributes['tax_display_preference'] == 0 || $this->attributes['tax_display_preference'] == 1) {
			$itemTaxAmount = number_format($this->getItemTotal($itemID, true, $includeDiscount) * $productTaxRate / 100, 2, '.', '');

		  // gross taxes -- (taxes displayed included, but still on top of base product price)
		  } else if ($this->attributes['tax_display_preference'] == 2) {
			$itemTaxAmount = number_format($this->getItemTotal($itemID, true, $includeDiscount) * $productTaxRate / 100, 2, '.', '');

		  // gross taxes -- (taxes displayed included, but included in base product price)
		  } else if ($this->attributes['tax_display_preference'] == 3) {
			$itemTaxAmount = number_format($this->getItemTotal($itemID, true, $includeDiscount) - ($this->getItemTotal($itemID) / (1 + $productTaxRate / 100)), 2, '.', '');
		  }
	  }
	  $orderTax += $itemTaxAmount;
	}

	// calculate tax on shipping
	$orderTax += $this->getShippingTax();

	return $orderTax;
  }

  function getShippingTax() {
	$shippingTaxAmount = 0;

	// calculate tax on shipping
	if ($this->attributes['shipping_taxation_rate'] > 0) {
		 $taxField = $this->attributes['shipping_taxation_rate'];
		 $shippingTaxRate = $this->taxRates["$taxField"]["tax_rate"];
		 $shippingTaxAmount = 0;

		  // net tax -- (taxes on top)
		  if ($this->attributes['tax_display_preference'] == 0 || $this->attributes['tax_display_preference'] == 1) {
			$shippingTaxAmount = number_format($this->getShippingTotal() * $shippingTaxRate / 100, 2, '.', '');

		  // gross taxes -- (taxes displayed included, but still on top of base product price)
		  } else if ($this->attributes['tax_display_preference'] == 2) {
			$shippingTaxAmount = number_format($this->getShippingTotal() * $shippingTaxRate / 100, 2, '.', '');

		  // gross taxes -- (taxes displayed included, but included in base product price)
		  } else if ($this->attributes['tax_display_preference'] == 3) {
			$shippingTaxAmount = number_format($this->getShippingTotal() / (100 + $shippingTaxRate / 100), 2, '.', '');
		  }
	}

    return $shippingTaxAmount;
  }

  function calculateOrderTotal() {
	  $taxOnTop = $this->attributes['tax_display_preference'] < 3;
	  $orderTotal = 0;
	  $orderTotal += $this->getItemSubtotal();
	  $orderTotal += $this->getShippingTotal();
	  if ($taxOnTop) {
	    $orderTotal += $this->getOrderTax();
	  }

	  return $orderTotal;


  }

  function updateOrderTotal($amount, $fee = 0.00) {
	  global $dbObj;
	  $this->attributes['mc_gross'] = $amount;
	  $this->attributes['mcfee'] = $fee;
	  $update = "UPDATE shopping_cart_orders SET ".
	  			"mc_gross='{$this->attributes['mc_gross']}', ".
	  			"mc_fee='{$this->attributes['mc_fee']}' ".
				"WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
  }

  function updateOrderDate() {
	  global $dbObj;
	  $this->attributes['order_date'] = date("Y-m-d H:i:s");
	  $update = "UPDATE shopping_cart_orders SET ".
	  			"order_date='{$this->attributes['order_date']}' ".
				"WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
  }

  function updateShippingDetails($data) {
	  global $dbObj;
    // parse country data
		$countryList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_COUNTRY_LIST));
		$countries = array();
		foreach ($countryList as $cData) {
			$countryData = explode("=", $cData);
			$key = $countryData[0];
			$value = $countryData[1];
			$countries["$key"] = $value;
		}

		// parse state/province data
		$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_AMERICAN_STATE_LIST));
		$states = array();
		foreach ($statesList as $sData) {
			$satesData = explode("=", $sData);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}

		$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_CANADIAN_PROVINCE_LIST));
		foreach ($statesList as $sData) {
			$satesData = explode("=", $sData);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}

		$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_AUSTRALIAN_PROVINCE_LIST));
		foreach ($statesList as $sData) {
			$satesData = explode("=", $sData);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}

	  if ($data['shipping_first_name'] == "" && $data['shipping_attention'] != "") {
		$nameData = explode(" ", $data['shipping_attention']);
		$data['shipping_first_name'] = array_shift($nameData);
		$data['shipping_last_name'] = implode(" ", $nameData);

	  }

	  if ($data['shipping_country_code'] == "" && strlen($data['shipping_country']) == 2) {
		$data['shipping_country_code'] = $data['shipping_country'];
		$data['shipping_country'] = $countries["{$data['shipping_country_code']}"];

	  }


	  $update = "UPDATE shopping_cart_orders SET ".
	  			"first_name='{$data['shipping_first_name']}', ".
	  			"last_name='{$data['shipping_last_name']}', ".
	  			"address_street='{$data['shipping_street_address']}', ".
	  			"address_city='{$data['shipping_city']}', ".
	  			"address_state='{$data['shipping_state']}', ".
	  			"address_zip='{$data['shipping_zip']}', ".
	  			"address_country='{$data['shipping_country']}', ".
	  			"address_country_code='{$data['shipping_country_code']}' ".
				"WHERE id='{$this->attributes['id']}'";
	  $dbObj->query($update);
  }

  function maskCreditCardNumber($cardNumber) {
		$cardNumber = str_replace(" ", "", $cardNumber);
		$cardNumber  = str_replace("-", "", $cardNumber);

		$numToHide = strlen($cardNumber) - 4;
		$lastFourDigits = substr($cardNumber, $numToHide, 4);
		return sprintf("%'*".strlen($cardNumber)."s", $lastFourDigits);

  }
  function updateBillingDetails($data) {
	  global $dbObj;

    // parse country data
		$countryList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_COUNTRY_LIST));
		$countries = array();
		foreach ($countryList as $cData) {
			$countryData = explode("=", $cData);
			$key = $countryData[0];
			$value = $countryData[1];
			$countries["$key"] = $value;
		}

		// parse state/province data
		$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_AMERICAN_STATE_LIST));
		$states = array();
		foreach ($statesList as $sData) {
			$satesData = explode("=", $sData);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}

		$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_CANADIAN_PROVINCE_LIST));
		foreach ($statesList as $sData) {
			$satesData = explode("=", $sData);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}

		$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_AUSTRALIAN_PROVINCE_LIST));
		foreach ($statesList as $sData) {
			$satesData = explode("=", $sData);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}

	  if ($data['billing_first_name'] == "" && $data['billing_attention'] != "") {
		$nameData = explode(" ", $data['billing_attention']);
		$data['billing_first_name'] = array_shift($nameData);
		$data['billing_last_name'] = implode(" ", $nameData);

	  }

	  if ($data['billing_country_code'] == "" && strlen($data['billing_country']) == 2) {
		$data['billing_country_code'] = $data['billing_country'];
		$data['billing_country'] = $countries["{$data['billing_country_code']}"];

	  }
	  $update = "UPDATE shopping_cart_orders SET ".
	  			"billing_first_name='{$data['billing_first_name']}', ".
	  			"billing_last_name='{$data['billing_last_name']}', ".
	  			"billing_address_street='{$data['billing_street_address']}', ".
	  			"billing_address_city='{$data['billing_city']}', ".
	  			"billing_address_state='{$data['billing_state']}', ".
	  			"billing_address_zip='{$data['billing_zip']}', ".
	  			"billing_address_country='{$data['billing_country']}', ".
	  			"billing_address_country_code='{$data['billing_country_code']}' ".
				"WHERE id='{$this->attributes['id']}'";
	//			print $update;
	  $dbObj->query($update);
	//  print mysql_error();
  }

  function getItemsMatchingProductIDs($productIDs) {
	$matchingItems = array();
	foreach ($this->items as $itemID => $item) {
	  if (in_array($item['product_id'], $productIDs)) {
		  $matchingItems["$itemID"] = $item;
	  }

	}

    return $matchingItems;
  }

  function getMatchingItemsSubtotal($items) {
	$itemTotal = 0;

	foreach ($items as $itemID => $item) {
	  $itemTotal += $this->getItemTotal($itemID);
	}


	return $itemTotal;

  }


  function getAvailableCoupons() {
	global $dbObj;
	$coupons = array();
	return $coupons;
	$query = "SELECT id, access_qualifier FROM shopping_cart_discount WHERE status=1 AND (visibility='2') AND start_date<='".date("Y-m-d H:i")."' AND end_date>'".date("Y-m-d H:i")."' AND site_id='".NUMO_SITE_ID."'";
	$result = $dbObj->query($query);
	while ($couponRecord = mysql_fetch_array($result)) {

		$discountID = $couponRecord['id'];
		$couponCode = $couponRecord['access_qualifier'];

		$coupons["{$couponCode}"] = new Discount($couponID);
	}
	return $coupons;
  }

  function getDiscounts() {
	global $dbObj;
	$discounts = array();
	return $discounts;
	$query = "SELECT id FROM shopping_cart_discount WHERE status=1 AND (visibility='1' OR (visibility='2' AND access_qualifier='{$this->attributes['coupon_code']}')) AND start_date<='".date("Y-m-d H:i")."' AND end_date>'".date("Y-m-d H:i")."' AND site_id='".NUMO_SITE_ID."'";
	//print $query;
	$result = $dbObj->query($query);

	while ($discountRecord = mysql_fetch_array($result)) {
	  $includeDiscount = false;
	  $myDiscount = new Discount($discountRecord['id']);
	  $myDiscount->attributes['currency_symbol'] = $this->attributes['currency_symbol'];
	  // if this is an order level discount
	  if ($myDiscount->attributes['qualifier_scope'] == "0") {
		 // print "hmmm";
		  // is a value based discount
		  if ($myDiscount->attributes['discount_type'] == "0") {

			   $totalToRebate = $this->getItemSubtotal(false);

			   $taxOnTop = $this->attributes['tax_display_preference'] < 3;

			  // if ($taxOnTop) {
			  //   $totalToRebate = $this->getItemSubtotal(false) + $this->getOrderTax();
			  // } else {
			//	 $totalToRebate = $this->getItemSubtotal(false);
			  // }

			 if ($totalToRebate >= $myDiscount->attributes['scope_quantifier']) {
				$includeDiscount = true;
				$myDiscount->calculateRebateAmount($totalToRebate);
			 }
		  // is a quantity based discount
		  } else if ($myDiscount->attributes['discount_type'] == "1") {

		  }
		  if ($includeDiscount && (($discounts['order'] != "" && $myDiscount->getRebateAmount() > $discounts["order"]->getRebateAmount()) || $discounts['order'] == "")) {
			$discounts['order'] = $myDiscount;
		  }

	  // if this is a product category discount
	  } else if ($myDiscount->attributes['qualifier_scope'] == "1") {
		  /*
		  $categories = $this->getItemsMatchingProductCategoriesIDs(explode(",", $myDiscount->attributes['scope_extension_id']));

		  //$itemsTotal = $this->getMatchingItemsSubtotal($items);

		  // based off of value
		  if ($myDiscount->attributes['discount_type'] == "0") {
			  if ($itemsTotal >= $myDiscount->attributes['scope_quantifier']) {
				$includeDiscount = true;
				$myDiscount->calculateRebateAmount($itemsTotal);
				foreach ($items as $itemID => $item) {
				  $this->items["{$itemID}"]["discount"] = $myDiscount;
				}
			  }
		  // based off of quantity
		  } else if ($myDiscount->attributes['discount_type'] == "1") {
			  $totalItems = sizeof($items);
			  $myItemsTotal = 0;


				foreach ($items as $itemID => $item) {
				    print $item['quantity']."--".$myDiscount->attributes['scope_quantifier']."<br>";
					if ($item['quantity'] >= $myDiscount->attributes['scope_quantifier']) {
				      $this->items["{$itemID}"]["discount"] = $myDiscount;
					  $myItemsTotal += $this->getItemTotal($itemID);
					}
				}

				if ($myItemsTotal > 0) {
					//print "yup on $myItemsTotal";
					$includeDiscount = true;
					$myDiscount->calculateRebateAmount($myItemsTotal);
					//print $myDiscount->getRebateAmount();
				}

		  }
		  if ($includeDiscount && (($discounts['product'] != "" && $myDiscount->getRebateAmount() > $discounts["product"]->getRebateAmount()) || $discounts['product'] == "")) {
			$discounts['product'] = $myDiscount;
		  }
*/
	  // if this is a product level discount
	  } else if ($myDiscount->attributes['qualifier_scope'] == "2") {
		  $items = $this->getItemsMatchingProductIDs(explode(",", $myDiscount->attributes['scope_extension_id']));
		  $itemsTotal = $this->getMatchingItemsSubtotal($items);

		  // based off of value
		  if ($myDiscount->attributes['discount_type'] == "0") {
			  if ($itemsTotal >= $myDiscount->attributes['scope_quantifier']) {
				$includeDiscount = true;
				$myDiscount->calculateRebateAmount($itemsTotal);
				foreach ($items as $itemID => $item) {
				  $this->items["{$itemID}"]["discount"] = $myDiscount;
				}
			  }
		  // based off of quantity
		  } else if ($myDiscount->attributes['discount_type'] == "1") {
			  $totalItems = sizeof($items);
			  $myItemsTotal = 0;


				foreach ($items as $itemID => $item) {
				    print $item['quantity']."--".$myDiscount->attributes['scope_quantifier']."<br>";
					if ($item['quantity'] >= $myDiscount->attributes['scope_quantifier']) {
				      $this->items["{$itemID}"]["discount"] = $myDiscount;
					  $myItemsTotal += $this->getItemTotal($itemID);
					}
				}

				if ($myItemsTotal > 0) {
					//print "yup on $myItemsTotal";
					$includeDiscount = true;
					$myDiscount->calculateRebateAmount($myItemsTotal);
					//print $myDiscount->getRebateAmount();
				}

		  }
		  if ($includeDiscount && (($discounts['product'] != "" && $myDiscount->getRebateAmount() > $discounts["product"]->getRebateAmount()) || $discounts['product'] == "")) {
			$discounts['product'] = $myDiscount;
		  }


	  }


	}

	return $discounts;
  }

  function getDiscountTotal() {
    $total = 0;

	$discounts = $this->getDiscounts();

	foreach ($discounts as $discountID => $discount) {
	  $total += $discount->getRebateAmount();
	}

	return $total;
  }
}
?>