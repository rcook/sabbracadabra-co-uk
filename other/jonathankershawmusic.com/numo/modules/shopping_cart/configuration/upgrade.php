<?
// add tax rates
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_taxes");
$exists = (@mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("CREATE TABLE `shopping_cart_taxes` (
`tax_rate_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`site_id` INT( 11 ) NOT NULL DEFAULT '1',
`rate_name` VARCHAR( 50 ) NOT NULL ,
`tax_rate` DOUBLE( 6, 2 ) NOT NULL ,
PRIMARY KEY ( `tax_rate_id` , `site_id` )
)");
}

// add ability to allow users to have shopping cart discounts
$result = $dbObj->query("SHOW COLUMNS FROM `types` LIKE 'shopping_cart_discount'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `types` ADD `shopping_cart_discount`  DOUBLE( 6, 2 ) NOT NULL default 0.00");
	$dbObj->query("ALTER TABLE `types` ADD `show_original_price`  tinyint (4) NOT NULL default 1");
}

// add field to show order date, which is often different payment date
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_orders` LIKE 'order_date'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `order_date` timestamp default CURRENT_TIMESTAMP");
	$dbObj->query("UPDATE shopping_cart_orders SET order_date=payment_date WHERE payment_date<>'0000-00-00 00:00:00'");
}

// add a number of new default attributes for the shopping cart product
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_fields` LIKE 'visibility'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `shopping_cart_fields` ADD `visibility` tinyint(4) default 0");
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=1 WHERE id=1"); // product name
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=1 WHERE id=2"); // price
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=1 WHERE id=3"); // description
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=1 WHERE id=4"); // technical specs
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=0 WHERE id=5"); // shipping type
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=0 WHERE id=6"); // internal
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=1 WHERE id=7"); // sku/id
  $dbObj->query("UPDATE `shopping_cart_fields` SET visibility=0 WHERE id=8"); // tax rate
}

// add in the ability to add dynamic attributes to the product
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'available_slots'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `available_slots` text");

  $dbObj->query("UPDATE shopping_cart_settings SET available_slots='9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30' WHERE site_id='".NUMO_SITE_ID."'");
}

// add in the ability to have a user definable "view cart" page
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'view_cart_page'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `view_cart_page` text");
}

// add in the ability to save a tax display preference
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'tax_display_preference'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `tax_display_preference` tinyint(4) default 1");
}

// add in the ability to have the catalog restricted or visible
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'catalog_visibility'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `catalog_visibility` int(11) default 0");
	$dbObj->query("CREATE TABLE IF NOT EXISTS `shopping_cart_category_permissions` (`id` int(11) NOT NULL auto_increment,`account_type_id` int(11) NOT NULL, `category_id` int(11) NOT NULL, PRIMARY KEY  (`id`))");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-RESTRICTED_MESSAGE', '".NUMO_SITE_ID."', 'We\'re sorry, this catalog is restricted.')");
}

// add in the ability to set different product attributes as user selectable "order by" via the catalog (2012-11-22)
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_fields` LIKE 'orderable'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_fields` ADD `orderable` tinyint(4) default 1");
	$dbObj->query("UPDATE shopping_cart_fields SET orderable=0 WHERE slot>2 && slot<9");
}

// add in the ability to save a default "order by" field (2012-11-22)
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'order_by_field'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `order_by_field` varchar(255) default ''");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `show_breadcrumb` tinyint(4) default '1'");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `show_order_chooser` tinyint(4) default '1'");
}

// add in the ability to save a default "order by" field (2012-11-22)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-ORDER_BY'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_BY', '".NUMO_SITE_ID."', 'Order By')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_BY_DIRECTION', '".NUMO_SITE_ID."', 'Direction')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_BY_DIRECTION_ASCENDING', '".NUMO_SITE_ID."', 'Ascending')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_BY_DIRECTION_DESCENDING', '".NUMO_SITE_ID."', 'Descending')");
}

// add in syntax for "send order shipped email" functionality (2012-11-23)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-ORDER_SHIPPED_SUBJECT'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_SHIPPED_SUBJECT', '".NUMO_SITE_ID."', 'Your Order #[Order ID] from SITE NAME has been shipped.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_SHIPPED_EMAIL_MESSAGE', '".NUMO_SITE_ID."', \"Hello [Name],\n\nThis message is to notify you that your order #[Order ID] has been shipped.\n\nRegards,\nSITE NAME Staff\")");
}
		
// add in syntax for "category meta description tag" functionality (2012-11-26)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-DEFAULT_CATEGORY_META_DESCRIPTION'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-DEFAULT_CATEGORY_META_DESCRIPTION', '".NUMO_SITE_ID."', 'Unique and Exciting Products from SITE NAME')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-DEFAULT_CATEGORY_META_KEYWORDS', '".NUMO_SITE_ID."', '[Product Names]')");
}

// add in the have user-definable description/keywords for the shopping-cart categories
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_categories` LIKE 'description'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `shopping_cart_categories` ADD `description` varchar(255) default ''");
  $dbObj->query("ALTER TABLE `shopping_cart_categories` ADD `keywords` varchar(255) default '[Product Names]'");

}

// add in the ability to save a default "order by" field (2012-11-22)
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'require_account_at_checkout'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `require_account_at_checkout` tinyint(4) default '1'");
}


// add in syntax for "category meta description tag" functionality (2012-11-26)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-OUT_OF_STOCK_LABEL'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-OUT_OF_STOCK_LABEL', '".NUMO_SITE_ID."', 'OUT OF STOCK')");
}


// add in the ability to set whether to send an email upon order initiated/completed (April 16, 2013)
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'send_admin_email_order_pending'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {

	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `send_admin_email_order_pending` tinyint(4) default '0'");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `send_admin_email_order_completed` tinyint(4) default '1'");
	
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `store_mode_order_collection_on` tinyint(4) default '0'");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAY_VIA_PAYPAL_LABEL', '".NUMO_SITE_ID."', 'Pay online via PayPal (VISA/MC/AMEX)')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CHOOSE_PAYMENT_METHOD_LABEL', '".NUMO_SITE_ID."', 'Please Choose Your Method Of Payment')");
}


// add ability for shipping taxation rate, plus offline payment configurations (April 17, 2013
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'shipping_taxation_rate'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `shipping_taxation_rate` tinyint(4) default '0'");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `offline_payment_types` varchar(255) default '0'");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `offline_collect_billing_address` tinyint(4) default '0'");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `offline_collect_shipping_address` tinyint(4) default '0'");
}

// add language syntax for offline order (April 17, 2013)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-PAY_OFFLINE_VIA_CREDIT_CARD_LABEL'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAY_OFFLINE_VIA_CREDIT_CARD_LABEL', '".NUMO_SITE_ID."', 'Pay via Credit Card')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAY_OFFLINE_VIA_INVOICE_LABEL', '".NUMO_SITE_ID."', 'Send Me An Invoice')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAY_OFFLINE_VIA_PURCHASE_ORDER_LABEL', '".NUMO_SITE_ID."', 'Pay with Purchase Order')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAY_OFFLINE_VIA_CHECK_LABEL', '".NUMO_SITE_ID."', 'Pay via Check/Bank Draft')");

	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CREDIT_CARD_NUMBER_LABEL', '".NUMO_SITE_ID."', 'Credit Card #')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CREDIT_CARD_EXPIRY_DATE_LABEL', '".NUMO_SITE_ID."', 'Expiry Date')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CREDIT_CARD_CVV_LABEL', '".NUMO_SITE_ID."', 'CVV')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CREDIT_CARD_CARD_HOLDER_LABEL', '".NUMO_SITE_ID."', 'Name On Card')");
	
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PURCHASE_ORDER_LABEL', '".NUMO_SITE_ID."', 'Purchase Order #')");

	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_COLLECTION_DONE', '".NUMO_SITE_ID."', 'Thank you.  Your order has been received.  You will receive an email once your order has been shipped.  If you have any questions about your order, please call 1-800-555-1234.')");
}


// add ability for shipping taxation rate, plus offline payment configurations (April 17, 2013
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_orders` LIKE 'billing_first_name'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_first_name` text");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_last_name` text");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_address_street` text");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_address_zip` varchar(25)");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_address_city` varchar(200)");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_address_state` varchar(100)");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_address_country` varchar(100)");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `billing_address_country_code` varchar(10)");
}


// add ability for colletion of credit card/purchase order data
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_orders` LIKE 'account_number'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `account_number` text");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `account_verification_number` text");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `account_name` text");
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `account_expiry_date` varchar(7)");

}



// add language syntax for offline order (April 17, 2013)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-SHIPPING_DETAILS_LABEL'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-SHIPPING_DETAILS_LABEL', '".NUMO_SITE_ID."', 'Shipping Details')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-BILLING_DETAILS_LABEL', '".NUMO_SITE_ID."', 'Billing Details')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CREDIT_CARD_DETAILS_LABEL', '".NUMO_SITE_ID."', 'Credit Card Details')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PURCHASE_ORDER_DETAILS_LABEL', '".NUMO_SITE_ID."', 'Purchase Order Details')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-INVOICE_DETAILS_LABEL', '".NUMO_SITE_ID."', 'Invoice Details')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CHECK_DETAILS_LABEL', '".NUMO_SITE_ID."', 'Check/Bank Draft Details')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-COMPLETE_ORDER_LABEL', '".NUMO_SITE_ID."', 'Complete Order')");

	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-SHIPPING_DETAILS_DESCRIPTION', '".NUMO_SITE_ID."', 'Please specify the address that your order is to be shipped to.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-BILLING_DETAILS_DESCRIPTION', '".NUMO_SITE_ID."', 'Please specify your billing address.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CREDIT_CARD_DETAILS_DESCRIPTION', '".NUMO_SITE_ID."', 'Your order will be shipped only once your credit card has been charged.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PURCHASE_ORDER_DETAILS_DESCRIPTION', '".NUMO_SITE_ID."', 'Your order will be shipped upon validation of your purchase order.  Please FAX a copy of your completed and signed purchase order to 555-555-1234.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-INVOICE_DETAILS_DESCRIPTION', '".NUMO_SITE_ID."', 'Payment will be due immediately upon receipt of your invoice.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CHECK_DETAILS_DESCRIPTION', '".NUMO_SITE_ID."', 'Please send a check or bank draft for the amount of [Order Total] to:<br/><address>[Company Address]</address><br/>Your order will not be shipped until payment has been received.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-COMPLETE_ORDER_DESCRIPTION', '".NUMO_SITE_ID."', 'Click the COMPLETE ORDER button, to submit your order.')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-COMPLETE_ORDER_BUTTON_LABEL', '".NUMO_SITE_ID."', 'Complete Order!')");

	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ATTENTION_LABEL', '".NUMO_SITE_ID."', 'Name')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-STREET_ADDRESS_LABEL', '".NUMO_SITE_ID."', 'Street Address')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-CITY_LABEL', '".NUMO_SITE_ID."', 'City')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-STATE_LABEL', '".NUMO_SITE_ID."', 'State/Province')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ZIP_LABEL', '".NUMO_SITE_ID."', 'Zip/Postal Code')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-COUNTRY_LABEL', '".NUMO_SITE_ID."', 'Country')");

}


// add language syntax and support for zero priced products (April 18, 2013)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-FROM_PRICE'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-FROM_PRICE', '".NUMO_SITE_ID."', 'From [price]')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-FREE_LABEL', '".NUMO_SITE_ID."', 'FREE')");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `zero_priced_display` tinyint(4) default '0'");
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `zero_priced_display_when_attributes` tinyint(4) default '2'");
}

// add in the ability to set color for optional product attributes (April 18, 2013)
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_optional_product_attributes` LIKE 'label_fg_color'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {

	$dbObj->query("ALTER TABLE `shopping_cart_optional_product_attributes` ADD `label_fg_color` varchar(7) default ''");
}


// add language syntax for support notification emails (April 23, 2013)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-ORDER_COMPLETED_ADMIN_EMAIL_SUBJECT'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_COMPLETED_ADMIN_EMAIL_SUBJECT', '".NUMO_SITE_ID."', 'Completed Sale Notification Order #[Order ID] from YOUR SITE NAME')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_COMPLETED_ADMIN_EMAIL_MESSAGE', '".NUMO_SITE_ID."', 'This is a notification of a COMPLETED order for [Customer Name] of [Order Total].<br/><br/>[Customer Invoice Info]<br/><br/>[Order Items]')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_PENDING_ADMIN_EMAIL_SUBJECT', '".NUMO_SITE_ID."', 'Pending Order Notification from YOUR SITE NAME')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_PENDING_ADMIN_EMAIL_MESSAGE', '".NUMO_SITE_ID."', 'This is a notification of a PENDING order via [Payment Method] for [Customer Name] of [Order Total].<br/><br/>[Customer Invoice Info]<br/><br/>[Order Items]')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_RECEIVED_EMAIL_SUBJECT', '".NUMO_SITE_ID."', 'Your Order Has Been Received at YOUR SITE NAME')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-ORDER_RECEIVED_EMAIL_MESSAGE', '".NUMO_SITE_ID."', 'Hello [Customer Name],<br><br>Greetings from YOUR SITE NAME and thank you for your order!  It has been received and will be shipped once payment can be confirmed.  Please review your order details below:<br><br>[Order Items]<br/><br/>If you have any questions about your order, please contact us at 1-800-555-1234.<br/><br/>[Payment Type Instructions]')");
}


// add language syntax for payment received notification emails (April 24, 2013)
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-PAYMENT_RECEIVED_EMAIL_SUBJECT'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAYMENT_RECEIVED_EMAIL_SUBJECT', '".NUMO_SITE_ID."', 'Your Payment for Order #[Order ID] Has Been Received | YOUR SITE NAME')");
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAYMENT_RECEIVED_EMAIL_MESSAGE', '".NUMO_SITE_ID."', 'Hello [Customer Name],<br><br>This message is to notify you that we have received payment your payment of [Order Total] for your order (#[Order ID]).<br/><br/> Please review your order details below:<br><br>[Order Items]<br/><br/>If you have any questions about your order, please contact us at 1-800-555-1234.')");
	$dbObj->query("ALTER TABLE `shopping_cart_orders`  CHANGE `payment_type` `payment_type` VARCHAR(35)");
	$dbObj->query("ALTER TABLE `shopping_cart_orders`  CHANGE `payment_type` `payment_type` VARCHAR(30) NOT NULL");
}


// add ability for shipping taxation rate, plus offline payment configurations (April 17, 2013
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_orders` LIKE 'retry_trace'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_orders` ADD `retry_trace` double(15,4)");
}



// add in syntax for legacy field July 10, 2013
$result = $dbObj->query("SELECT * FROM language_syntax WHERE site_id='".NUMO_SITE_ID."' AND id='SHOPPING_CART-BREADCRUMB_HOME_LABEL'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-BREADCRUMB_HOME_LABEL', '".NUMO_SITE_ID."', 'Catalog')");
}

// add support for lightbox product image viewing (July 16 2013)
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'product_details_use_lightbox'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `product_details_use_lightbox` tinyint(4) default '1'");
}

$result = $dbObj->query("SELECT * FROM shopping_cart_discount LIMIT 1");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("CREATE TABLE IF NOT EXISTS `shopping_cart_discount` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `discount_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 = total, 1 = quantity',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `visibility` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0 = pending, 1 = global, 3 = coupon',
  `amount_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 = $, 1 = %',
  `qualifier_scope` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 = order, 1 = product, 4 = product category',
  `amount` double(10,2) NOT NULL DEFAULT '0.00',
  `scope_extension_id` text NOT NULL,
  `discount_scope` tinyint(4) NOT NULL COMMENT '0 = rebate, 1 = shipping',
  `discount_name` varchar(50) NOT NULL,
  `when_created` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `site_id` bigint(20) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `scope_quantifier` double(10,2) NOT NULL DEFAULT '0.00',
  `access_qualifier` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
}

// add support for row vs grid display
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'catalog_display'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `catalog_display` tinyint(4) default 0");
}

// add ability to direct the user to a thank you page
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'paypal_return_url'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `paypal_return_url` text");
}

// add ability to direct the user to a cancel page
$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings` LIKE 'paypal_cancel_url'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
	$dbObj->query("ALTER TABLE `shopping_cart_settings` ADD `paypal_cancel_url` text");
}
?>