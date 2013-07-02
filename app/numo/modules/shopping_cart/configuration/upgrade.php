<?
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

?>