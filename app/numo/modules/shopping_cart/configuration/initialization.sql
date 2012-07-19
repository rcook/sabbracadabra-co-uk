CREATE TABLE IF NOT EXISTS `shopping_cart_categories` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `parent_id` int(11) NOT NULL, `label` text NOT NULL, `position` int(11) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_fields` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `name` varchar(100) NOT NULL COMMENT 'name (label) of the field displayed', `slot` int(11) NOT NULL COMMENT 'slot number the field information can be found within', `position` int(11) NOT NULL COMMENT 'the position order the field should be displayed in', `required` int(1) NOT NULL default '0' COMMENT 'required to be filled out to create an account', `locked` int(1) NOT NULL default '0' COMMENT 'field cannot be removed', `input_type` varchar(50) NOT NULL COMMENT 'how the information should be input (i.e. select, text, number)', `input_options` text NOT NULL COMMENT 'any additional information that is needed for a given field type (i.e. list options for option list)', PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_optional_product_attributes` (`id` int(11) NOT NULL auto_increment, `product_id` int(11) NOT NULL, `label` text NOT NULL, `type` varchar(15) NOT NULL, `options` text NOT NULL, `required` int(1) NOT NULL default '0', `position` int(11) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_optional_product_attribute_options` (`id` int(11) NOT NULL auto_increment, `attribute_id` int(11) NOT NULL, `status` int(1) NOT NULL default '1' COMMENT 'rather than remove an option its status will be set to 0 so sales will still be reported properly', `label` text NOT NULL, `cost` decimal(10,2) NOT NULL default '0.00', PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_orders` (`id` int(11) NOT NULL auto_increment, `account_id` int(11) NOT NULL, `site_id` int(11) NOT NULL, `processed` int(1) NOT NULL default '0' COMMENT '1 = order processed by paypal, 0 = order has not been processed', `shipped` int(1) NOT NULL default '0' COMMENT '0 = not shipped, 1 = order shipped', `txn_id` varchar(20) NOT NULL, `first_name` text NOT NULL, `last_name` text NOT NULL, `address_street` text NOT NULL, `address_zip` varchar(10) NOT NULL, `address_city` varchar(200) NOT NULL, `address_state` varchar(100) NOT NULL, `address_country` varchar(100) NOT NULL, `address_country_code` varchar(10) NOT NULL, `address_status` varchar(20) NOT NULL, `contact_phone` varchar(50) NOT NULL, `mc_shipping` decimal(10,2) NOT NULL, `mc_handling` decimal(10,2) NOT NULL, `mc_currency` varchar(4) NOT NULL, `mc_fee` decimal(10,2) NOT NULL, `mc_gross` decimal(10,2) NOT NULL, `payment_type` varchar(20) NOT NULL, `payment_status` varchar(20) NOT NULL, `payment_date` datetime NOT NULL, `tax` decimal(5,4) NOT NULL, `settle_currency` varchar(4) NOT NULL, `settle_amount` decimal(10,2) NOT NULL, `exchange_rate` decimal(8,8) NOT NULL, `reason_code` varchar(100) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000
CREATE TABLE IF NOT EXISTS `shopping_cart_order_items` (`id` int(11) NOT NULL auto_increment, `order_id` int(11) NOT NULL, `product_id` int(11) NOT NULL, `item_cost` decimal(10,2) NOT NULL default '0.00', `quantity` int(11) NOT NULL default '1', PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000
CREATE TABLE IF NOT EXISTS `shopping_cart_order_item_attributes` (`id` int(11) NOT NULL auto_increment, `order_item_id` int(11) NOT NULL, `attribute_id` int(11) NOT NULL, `value` text NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_products` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `account_id` int(11) NOT NULL default '0', `pending` int(1) NOT NULL default '0', `status` int(1) NOT NULL default '1', `when_created` datetime NOT NULL, `when_expired` datetime NOT NULL, `shipping` decimal(10,2) NOT NULL default '0.00', `shipping2` decimal(10,2) NOT NULL default '0.00', `slot_1` text NOT NULL COMMENT 'title', `slot_2` text NOT NULL, `slot_3` text NOT NULL, `slot_4` text NOT NULL, `slot_5` text NOT NULL, `slot_6` text NOT NULL, `slot_7` text NOT NULL, `slot_8` text NOT NULL, `slot_9` text NOT NULL, `slot_10` text NOT NULL, `slot_11` text NOT NULL, `slot_12` text NOT NULL, `slot_13` text NOT NULL, `slot_14` text NOT NULL, `slot_15` text NOT NULL, `slot_16` text NOT NULL, `slot_17` text NOT NULL, `slot_18` text NOT NULL, `slot_19` text NOT NULL, `slot_20` text NOT NULL, `slot_21` text NOT NULL, `slot_22` text NOT NULL, `slot_23` text NOT NULL, `slot_24` text NOT NULL, `slot_25` text NOT NULL, `slot_26` text NOT NULL, `slot_27` text NOT NULL, `slot_28` text NOT NULL, `slot_29` text NOT NULL, `slot_30` text NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_product_categories` (`id` int(11) NOT NULL auto_increment, `product_id` int(11) NOT NULL, `category_id` int(11) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_product_images` (`id` int(11) NOT NULL auto_increment, `listing_id` int(11) NOT NULL, `file_name` text NOT NULL, `description` text NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_product_stock` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `key` text NOT NULL, `units` int(11) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `shopping_cart_settings` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `paypal_email` text NOT NULL, `company_name` text NOT NULL, `packing_slip_address` text NOT NULL, `store_mode` int(1) NOT NULL default '1' COMMENT '1 = live, 0 = sandbox', `request_shipping_details` int(1) NOT NULL default '1', `default_account_group` int(11) NOT NULL default '1' COMMENT 'the account group that new customer accounts will be created within', PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
INSERT INTO `shopping_cart_settings` (`id`, `site_id`, `paypal_email`, `company_name`, `packing_slip_address`, `store_mode`, `request_shipping_details`, `default_account_group`) VALUES (1, 1, 'you@paypal.com', 'Your Company Name', 'Street Address St\r\nCity, State, Zip\r\nPhone: (123) 456-7890', 1, 1, 1)
INSERT INTO `shopping_cart_fields` (`id`, `site_id`, `name`, `slot`, `position`, `required`, `locked`, `input_type`, `input_options`) VALUES (1, 1, 'Product Name', 1, 1, 1, 1, 'text', ''), (2, 1, 'Price', 2, 2, 1, 1, 'money', 'USD'), (3, 1, 'Short Description', 3, 3, 1, 1, 'text box', '')
INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('SHOPPING_CART-PAYMENT_PROCESSING', 1, 'Thank-you, we are currently processing your order.  Once your order has processed you should receive an email notification.'),('SHOPPING_CART-ITEMS_IN_CART_LABEL', 1, 'items in cart'),('SHOPPING_CART-NO_PRODUCTS_MESSAGE', 1, 'No products available'),('SHOPPING_CART-VIEW_CART_LABEL', 1, 'View Cart'),('SHOPPING_CART-PACKING_SLIP_LABEL', 1, 'Packaging Slip'),('SHOPPING_CART-PACKING_SLIP_SHIP_TO_LABEL', 1, 'Ship To:'),('SHOPPING_CART-PACKING_SLIP_QUANTITY_LABEL', 1, 'Qty'),('SHOPPING_CART-PACKING_SLIP_ITEM_LABEL', 1, 'Item'),('SHOPPING_CART-PACKING_SLIP_PRICE_LABEL', 1, 'Price'),('SHOPPING_CART-TOTAL_LABEL', 1, 'Total'),('SHOPPING_CART-PACKING_SLIP_DATE_LABEL', 1, 'Date'),('SHOPPING_CART-PACKING_ORDER_LABEL', 1, 'Order #'),('SHOPPING_CART-SHIPPING_LABEL', 1, 'Shipping'),('SHOPPING_CART-PACKING_SLIP_TAX_LABEL', 1, 'Tax'),('SHOPPING_CART-ORDER_DATE_LABEL', 1, 'Date'),('SHOPPING_CART-ORDER_STATUS_LABEL', 1, 'Status'),('SHOPPING_CART-ORDER_AMOUNT_LABEL', 1, 'Amount'),('SHOPPING_CART-ORDER_NOT_COMPLETE_LABEL', 1, 'Payment Declined'),('SHOPPING_CART-ORDER_HAS_SHIPPED_LABEL', 1, 'Shipped'),('SHOPPING_CART-ORDER_PENDING_SHIPMENT_LABEL', 1, 'Pending Shipment'),('SHOPPING_CART-ORDER_PENDING_LABEL', 1, 'Pending Payment'),('SHOPPING_CART-ORDER_REFUNDED_LABEL', 1, 'Refunded'),('SHOPPING_CART-RETURNING_CUSTOMER_LABEL', 1, 'Returning Customer'),('SHOPPING_CART-LOGIN_TO_ACCOUNT_LABEL', 1, 'Login'),('SHOPPING_CART-NEW_CUSTOMER_LABEL', 1, 'New Customer?'),('SHOPPING_CART-CREATE_ACCOUNT_LABEL', 1, 'Create an account'),('SHOPPING_CART-NOT_IN_STOCK_LABEL', 1, 'The product you selected is currently out of stock.'),('SHOPPING_CART-FULL_QUANTITY_NOT_IN_STOCK_LABEL', 1, 'Only [quantity] units are available for purchase.'),('SHOPPING_CART-ALTERNATIVE_IMAGES_LABEL', '1', 'Alternative Images:'), ('SHOPPING_CART-ALTERNATIVE_IMAGES_CLICK_MESSAGE', '1', 'Click on an image to change view'),('SHOPPING_CART-BUY_NOW_LABEL', '1', 'Buy Now'),('SHOPPING_CART-MORE_PRODUCT_DETAILS_LABEL', '1', 'Click here for more details.'),('SHOPPING_CART-CATALOG_BACK_LINK_LABEL', '1', 'Back'),('SHOPPING_CART-CATALOG_NEXT_LINK_LABEL', '1', 'Next'),('SHOPPING_CART-CHECKOUT_BUTTON_LABEL', '1', 'Checkout'),('SHOPPING_CART-CHECKOUT_CONTINUE_BUTTON_LABEL', '1', 'Continue'),('SHOPPING_CART-CHECKOUT_UPDATE_BUTTON_LABEL', '1', 'Update'),('SHOPPING_CART-CHECKOUT_CONTINUE_SHOPPING_LABEL', '1', 'Continue Shopping'),('SHOPPING_CART-SEARCH_BUTTON_LABEL', '1', 'Search'),('SHOPPING_CART-BREADCRUMB_HOME_LABEL', '1', 'Catalog')
INSERT INTO `modules` (`site_id`, `name`) VALUES (1,'shopping_cart')