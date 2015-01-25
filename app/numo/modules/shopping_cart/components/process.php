<?php
error_reporting(E_ALL ^ E_NOTICE);

/* testing code 

$_POST['invoice'] = 1032;
$_POST['item_number1'] = 1104;
$_POST['item_number2'] = 1105;
$_POST['quantity1'] = 4;
$_POST['quantity2'] = 3;
$_POST['txn_id'] = "test".time();
$_POST['first_name'] = "test";
$_POST['last_name'] = "user";
$_POST['payer_email'] = "test@test.com";
$_POST['mc_gross'] = "105.00";


*/

global $testMode;
$testMode = false;
if (REMOTE_SERVICE === true) {
$testMode = false;	
} 
if ($_GET['cmd'] == "test") {
  $_POST['invoice'] = 33333333;
  $testMode = true;

}
require_once($_SERVER['DOCUMENT_ROOT'].NUMO_FOLDER_PATH."modules/accounts/classes/Account.php");
include_once("numo/modules/shopping_cart/classes/Order.php");
$order = new Order($_POST['invoice']);


/*** LOGGING SETUP BEGIN ***/
$logging = false; // value may be false or true (set to true to turn logging on)
$logFile = false; // do not modify
global $_GET;
if (REMOTE_SERVICE === true) {
  $folder = ABSOLUTE_ROOT_PATH."numo/modules/shopping_cart/components/";
	
} else {
  $folder = str_replace("check.php", "", str_replace("numo/modules/shopping_cart/components/process.php", "", $_SERVER['SCRIPT_FILENAME']))."numo/modules/shopping_cart/components/";
}
$logFileName = "{$folder}pp_log.txt";

if (!$logging) {
	$logFile = fopen($logFileName, 'r');
	if ($logFile) {
		$firstLine = fread($logFile, "60");
		if (strpos($firstLine, "DYNAMIC LOGGING ON") !== false) {
			$logging = true;
		}
	}
	fclose($logFile);
}

if ($logging || $_GET['cmd'] == "turn_logging_on") {

	@chmod($logFileName, 0666);
	
	if ($_GET['cmd'] == "wipe_log" || $_GET['cmd'] == "turn_logging_on") {
		$mode = "w+";
	} else {
		$mode = "a+";
	}
	$logFile = fopen($logFileName, $mode);
	if ($_GET['cmd'] == "turn_logging_on") {
	  $logging = true;
	  logLine($logging, $logFile, "DYNAMIC LOGGING ON\n#\n#");
	  $logging = false;
	  fclose($logFile);
	
	} else if ($_GET['cmd'] == "wipe_log") {
	  $logging = false;
	  fwrite($logFile, "");
	  fclose($logFile);
		
	}
	logLine($logging, $logFile, "Begin Event");

}

function logLine($logging, $logFile, $line) {
  global $testMode;
  if ($logging && $logFile) {
    fwrite($logFile, date("Y-m-d H:i:s").": ".trim($line)."\n");
  } else if ($testMode) {
	  print $line."<br>";
  }
}
/*** LOGGING SETUP END ***/

$itemNumbersCheck = "";
$purchasedItems = array();

//$email = "christa@luckymarble.com";
$header = "";
$emailtext = "";

// Read the post from PayPal and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) { 
  logLine($logging, $logFile, "Magic Quotes Function Found");
  $get_magic_quotes_exists = true;
} else {
  logLine($logging, $logFile, "Magic Quotes Off");
}
if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
    logLine($logging, $logFile, "Magic Quotes Slashes Stripped");
} else {
	logLine($logging, $logFile, "Magic Quotes Off");

}
foreach ($_POST as $key => $value) {
    logLine($logging, $logFile, "POST['$key']=$value");
	
	if(substr($key,0,11) == "item_number") {
		$itemNumbersCheck .= "`id`<>".$value." AND ";
		$purchasedItems[$value] = $_POST['quantity'.substr($key,11)];
	}
    
    $value = str_replace("&lt;", "<", $value);
    $value = str_replace("&gt;", ">", $value);    
    $value = str_replace("&#039;", "'", $value);
    $value = str_replace("&amp;", "&", $value);

  // Handle escape characters, which depends on setting of magic quotes
  if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
  	$value = urlencode(stripslashes($value));
  } else {
    $value = urlencode($value);
  }
  $req .= "&$key=$value";
}

$sql = "SELECT * FROM `shopping_cart_settings` WHERE site_id='".NUMO_SITE_ID."'";
			logLine($logging, $logFile, "sql: ".$sql);

if ($testMode) {
  //print $sql."<br>";
}
$result = $dbObj->query($sql);
$storeSettings = mysql_fetch_array($result);
if($storeSettings['store_mode'] == "0") {
	$connectionPoint = 'www.sandbox.paypal.com';
} else {
	$connectionPoint = 'www.paypal.com';
}
// Post back to PayPal to validate
$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
$header .= "Host: {$connectionPoint}\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n";
$header .= "Connection: close\r\n\r\n";

/*************************************/
/*        POST URL FOR CHECK         */
/*-----------------------------------*/
/* 1) TEST: www.sandbox.paypal.com   */
/* 2) LIVE: www.paypal.com           */
/*************************************/


$fp = fsockopen ($connectionPoint, 80, $errno, $errstr, 30);

/*************************************/

// Process validation from PayPal
if (!$fp) { // HTTP ERROR
  logLine($logging, $logFile, "Could not connect to {$connectionPoint}");
  
} else {
	$msgAdd = "";
    logLine($logging, $logFile, "Putting Header\n{$header}");
    logLine($logging, $logFile, "Putting Request: {$req}");

	// NO HTTP ERROR
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
        logLine($logging, $logFile, "Response: {$res}");
		
		if (strstr (trim($res), "VERIFIED") || $testMode) {
            logLine($logging, $logFile, "Response VERIFIED");
			// TODO:
			// Check the payment_status is Completed
			// Check that txn_id has not been previously processed
			$sql = "SELECT `id`,`payment_status`,`account_id` FROM `shopping_cart_orders` WHERE `txn_id`='".$_POST['txn_id']."' AND txn_id<>''";
            logLine($logging, $logFile, "SQL: {$sql}");
			//$msgAdd .= $sql."\n\n";
			$result = $dbObj->query($sql);   
            
			logLine($logging, $logFile, "num rows: ".mysql_num_rows($result));
			logLine($logging, $logFile, "isset(_POST[parent_txn_id]): ".isset($_POST['parent_txn_id']));

			if(mysql_num_rows($result) == 0 && isset($_POST['parent_txn_id'])) {
				$sql = "SELECT `id`,`payment_status`,`account_id` FROM `shopping_cart_orders` WHERE `txn_id`='".$_POST['parent_txn_id']."'";
            	logLine($logging, $logFile, "SQL: {$sql}");
				//$msgAdd .= $sql."\n\n";
				$result = $dbObj->query($sql);
			}

			// order already processed ... update order STATUS information
			if($row = mysql_fetch_array($result)) {
			  logLine($logging, $logFile, "EXISTING ORDER FOUND");
				
				//if status has changed update order information
				if($row['payment_status'] != $_POST['payment_status']) {
					//change basic order properties
					$sql = "UPDATE `shopping_cart_orders` SET `payment_status`='".$_POST['payment_status']."',`reason_code`='".$_POST['reason_code']."',`mc_fee`='".$_POST['mc_fee']."',`mc_gross`='".$_POST['mc_gross']."' WHERE `id`='".$row['id']."'";
					$dbObj->query($sql);
					//mail($email, "Live-VERIFIED OLD IPN", $msgAdd.$sql . "\n\n" . $req);
  					logLine($logging, $logFile, "SQL: {$sql}");
					
					//if payment status changed to 'complete' then update stock
				}

			//new order to process
			} else {
			
			
				
  				logLine($logging, $logFile, "NEW ORDER");
				
				// should get the customer number
				$sql = "SELECT `account_id` FROM `shopping_cart_orders` WHERE `id`='".$_POST['invoice']."'";
				$acctResult = $dbObj->query($sql);
				$acctRecInfo = mysql_fetch_array($acctResult);
				$accountID = $acctRecInfo['account_id'];
				
				// if no account id exists, then there is no shopping cart order, and subsequently the invoice id does not exist in our systme
				// this can be caused by paypal accounts that use more than one shopping cart, and receive secondary order notifcations to this process script.
				if ($accountID == "") {
  				  logLine($logging, $logFile, "Unrecognized invoice id: ".$_POST['invoice']);
				  print "Unrecognized Invoice ID";
				} else {
				// update the user's account if they are of "pending" type
				$update = "UPDATE `accounts` SET activated=1, pending=0, type_id='{$storeSettings['default_account_group']}', slot_4='{$_POST['first_name']} {$_POST['last_name']}', slot_3='{$_POST['payer_email']}', slot_1='guest".time()."' WHERE pending=3 AND type_id=0 AND id='{$accountID}'";
  				logLine($logging, $logFile, "SQL: {$update}");
				$dbObj->query($update);
				
				$sql = "UPDATE `shopping_cart_orders` SET `processed`=1,`txn_id`='".$_POST['txn_id']."',`contact_phone`='".$_POST['contact_phone']."',`first_name`='".$_POST['first_name']."',`last_name`='".$_POST['last_name']."',`address_street`='".$_POST['address_street']."',`address_zip`='".$_POST['address_zip']."',`address_city`='".$_POST['address_city']."',`address_state`='".$_POST['address_state']."',`address_country`='".$_POST['address_country']."',`address_country_code`='".$_POST['address_country_code']."',`address_status`='".$_POST['address_status']."',`mc_shipping`='".$_POST['mc_shipping']."',`mc_handling`='".$_POST['mc_handling']."',`mc_currency`='".$_POST['mc_currency']."',`mc_fee`='".$_POST['mc_fee']."',`mc_gross`='".$_POST['mc_gross']."',`payment_type`='".$_POST['payment_type']."',`payment_status`='".$_POST['payment_status']."',`payment_date`='".date("Y-m-d H:i:s", strtotime($_POST['payment_date']))."',`tax`='".$_POST['tax']."',`reason_code`='".$_POST['reason_code']."' WHERE `id`='".$_POST['invoice']."'";

				// if PAYMENT was settled from a different currency (store has select a different currency than the account) record settling information
				if(isset($_POST['settle_currency'])) {
  					logLine($logging, $logFile, "Using Different Currency");
					
					$sql = "UPDATE `shopping_cart_orders` SET `processed`=1,`txn_id`='".$_POST['txn_id']."',`contact_phone`='".$_POST['contact_phone']."',`first_name`='".$_POST['first_name']."',`last_name`='".$_POST['last_name']."',`address_street`='".$_POST['address_street']."',`address_zip`='".$_POST['address_zip']."',`address_city`='".$_POST['address_city']."',`address_state`='".$_POST['address_state']."',`address_country`='".$_POST['address_country']."',`address_country_code`='".$_POST['address_country_code']."',`address_status`='".$_POST['address_status']."',`mc_shipping`='".$_POST['mc_shipping']."',`mc_handling`='".$_POST['mc_handling']."',`mc_currency`='".$_POST['mc_currency']."',`mc_fee`='".$_POST['mc_fee']."',`mc_gross`='".$_POST['mc_gross']."',`payment_type`='".$_POST['payment_type']."',`payment_status`='".$_POST['payment_status']."',`payment_date`='".date("Y-m-d H:i:s", strtotime($_POST['payment_date']))."',`tax`='".$_POST['tax']."',`reason_code`='".$_POST['reason_code']."',`settle_currency`='".$_POST['settle_currency']."',`settle_amount`='".$_POST['settle_amount']."',`exchange_rate`='".$_POST['exchange_rate']."' WHERE `id`='".$_POST['invoice']."'";
				}
				
  				logLine($logging, $logFile, "SQL: {$sql}");

				//mail($email, "Live-VERIFIED NEW IPN", $req);
				$dbObj->query($sql);

				//loop through order items in database and make sure only items that are part of the order are saved
				$sql = "DELETE FROM `shopping_cart_order_items` WHERE `order_id`='".$_POST['invoice']."' AND (".substr($itemNumbersCheck,0,-5).")";
				//mail($email, "Live-VERIFIED NEW IPN SQL", $sql);
				$dbObj->query($sql);
  				logLine($logging, $logFile, "SQL: {$sql}");

				// if payment status changed to 'complete' then update stock
				foreach($purchasedItems as $itemId => $quantity) {
					$stockId = "";

					$sql = "SELECT `value` FROM `shopping_cart_order_item_attributes` WHERE `order_item_id`='".$itemId."'";
  					logLine($logging, $logFile, "SQL: {$sql}");
					//print $sql."<br>";
					$results = $dbObj->query($sql);

					while($row = mysql_fetch_array($results)) {
						$stockId .= "-".$row['value'];
					}

					$sql = "SELECT `product_id` FROM `shopping_cart_order_items` WHERE `id`='".$itemId."'";
  					logLine($logging, $logFile, "SQL: {$sql}");
					//print $sql."<br>";
					$result = $dbObj->query($sql);

					if($row = mysql_fetch_array($result)) {
						// add product id to the start of the stock key
						$stockId = $row['product_id'].$stockId;

						$sql = "UPDATE `shopping_cart_product_stock` SET `units`=`units` - ".$quantity." WHERE `key`='".$stockId."'";
	  					logLine($logging, $logFile, "SQL: {$sql}");
						//print $sql."<br>";
						$dbObj->query($sql);
						
						// also do update if it is an egood
						$query = "SELECT * FROM shopping_cart_products WHERE `id`='".$row['product_id']."'";
						logLine($logging, $logFile, "SQL: {$query}");
						$scpResult = $dbObj->query($query);
						$prodRecord = mysql_fetch_array($scpResult);
						
						// 0 = cost based shipping
						// 1 = weight based shipping
						// 2 = egood
						logLine($logging, $logFile, "shipping method: {$prodRecord['slot_5']}");
						
						if ($prodRecord['slot_5'] == "2") {
							$fieldValueData = explode("\r\n", $prodRecord['slot_6']);
							$fValues = array();
							
							foreach ($fieldValueData as $data) {
								logLine($logging, $logFile, "egood:: {$data}");
								
								$dataX = explode(":", $data);
								$dataKey = $dataX[0];
								$dataValue = $dataX[1];
								if ($dataKey == "accounts") {
									if ($dataValue == "" || $dataValue == "0") {
										// do nothing
									} else {
										$acct = new Account($accountID);
										$acct->changeGroup($dataValue);
										
										logLine($logging, $logFile, "eGood - Upgrade account to user type: $dataValue");
										
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
												
												logLine($logging, $logFile, "eGood - Grant Access to Page: $fileID");
												
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
												
												logLine($logging, $logFile, "eGood - Newsletter Subscription List: $fileID");
												
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
									logLine($logging, $logFile, "SQL: {$sql}");
									
									$dbObj->query($sql);
				
								}
							}
						} else {
							
						}
					} else {
  					  logLine($logging, $logFile, "ISSUE: Record Not Found");
						
					}
				}
				
				$order = new Order($_POST['invoice']);
				$order->sendCustomerReceiptOfOrder();
				if ($order->attributes['send_admin_email_order_completed'] == 1) {
					$order->sendAdminNotificationOfCompletedOrder();
				}
			   }

			}

			//mail($email, "Live-VERIFIED IPN", $emailtext . "\n\n" . $req);
		} else if (strcmp ($res, "INVALID") == 0) {
			 logLine($logging, $logFile, "RESPONSE INVALID");

			// If 'INVALID', send an email. TODO: Log for manual investigation.
			// This would happen if it is not a real order... since PayPal doesn't have a record of the order
			foreach ($_POST as $key => $value){
				$emailtext .= $key . " = " .$value ."\n\n";
			}
			//mail($email, "Live-INVALID IPN", $emailtext . "\n\n" . $req);
		}
		if ($testMode) {
		  break;	
		}
	}
}
fclose ($fp);
if ($logging && $logFile) {
	logLine($logging, $logFile, date("Y-m-d H:i:s").": End Event\n#\n#");

	fclose($logFile);
}
?>