CREATE TABLE IF NOT EXISTS `modules` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `name` varchar(100) NOT NULL, `license_key` varchar(100) NOT NULL default '', PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0
CREATE TABLE IF NOT EXISTS `accounts` (`id` int(11) NOT NULL auto_increment, `type_id` int(11) NOT NULL, `is_admin` int(11) NOT NULL default '0', `pending` int(1) NOT NULL default '0', `activated` int(1) NOT NULL default '0', `ip_address` varchar(25) NOT NULL, `when_created` datetime NOT NULL, `last_accessed` datetime NOT NULL, `slot_1` text NOT NULL COMMENT 'login id', `slot_2` text NOT NULL COMMENT 'password', `slot_3` text NOT NULL COMMENT 'email address', `slot_4` text NOT NULL COMMENT 'full name', `slot_5` text NOT NULL, `slot_6` text NOT NULL, `slot_7` text NOT NULL, `slot_8` text NOT NULL, `slot_9` text NOT NULL, `slot_10` text NOT NULL, `slot_11` text NOT NULL, `slot_12` text NOT NULL, `slot_13` text NOT NULL, `slot_14` text NOT NULL, `slot_15` text NOT NULL, `slot_16` text NOT NULL, `slot_17` text NOT NULL, `slot_18` text NOT NULL, `slot_19` text NOT NULL, `slot_20` text NOT NULL, `slot_21` text NOT NULL, `slot_22` text NOT NULL, `slot_23` text NOT NULL, `slot_24` text NOT NULL, `slot_25` text NOT NULL, `slot_26` text NOT NULL, `slot_27` text NOT NULL, `slot_28` text NOT NULL, `slot_29` text NOT NULL, `slot_30` text NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `fields` (`id` int(11) NOT NULL auto_increment, `type_id` int(11) NOT NULL, `name` varchar(100) NOT NULL COMMENT 'name (label) of the field displayed', `slot` int(11) NOT NULL COMMENT 'slot number the field information can be found within', `position` int(11) NOT NULL COMMENT 'the position order the field should be displayed in', `required` int(1) NOT NULL default '0' COMMENT 'required to be filled out to create an account', `locked` int(1) NOT NULL default '0' COMMENT 'field cannot be removed', `show_on_registration` int(1) NOT NULL default '1' COMMENT 'display on registration component', `input_type` varchar(50) NOT NULL COMMENT 'how the information should be input (i.e. select, text, number)', `input_options` text NOT NULL COMMENT 'any additional information that is needed for a given field type (i.e. list options for option list)', `regex` text NOT NULL COMMENT 'regular expression to match input on', PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `sites` (`id` int(11) NOT NULL auto_increment, `domain` text NOT NULL, `name` varchar(100) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `types` (`id` int(11) NOT NULL auto_increment, `site_id` int(11) NOT NULL, `name` varchar(100) NOT NULL, `available_slots` text NOT NULL, `allow_registration` int(1) NOT NULL default '0', `require_approval` int(1) NOT NULL default '0', `require_activation` int(1) NOT NULL default '0', PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
CREATE TABLE IF NOT EXISTS `language_syntax` (`id` varchar(50) NOT NULL,`site_id` int(11) NOT NULL, `value` text NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0
CREATE TABLE IF NOT EXISTS `pending_requests` (`id` varchar(50) NOT NULL, `site_id` int(11) NOT NULL, `account_id` int(11) NOT NULL, `module` varchar(50) NOT NULL, `component` varchar(50) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `sites` (`domain`,`name`) VALUES ('single domain','Default')
INSERT INTO `types` (`site_id`,`name`,`available_slots`) VALUES (1,'default','5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30')
INSERT INTO `fields` (`type_id`,`name`,`slot`,`position`,`required`,`locked`,`show_on_registration`,`input_type`,`input_options`,`regex`) VALUES (1,'Username',1,3,1,1,1,'text','',''),(1,'Password',2,4,1,1,1,'password','',''),(1,'Email Address',3,2,1,1,1,'text','','^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$'),(1,'Name',4,1,1,1,1,'text','','')
INSERT INTO `language_syntax` (`id`, `site_id`, `value`) VALUES ('NUMO-INVALID_REQUEST_CODE', 1, 'Invalid request code. Please try again or contact your website administrator.'), ('NUMO-ADMINISTRATIVE_EMAIL_ADDRESS', 1, 'admin@sitename.com'), ('NUMO-FILE_NOT_FOUND_TITLE', 1, 'File Not Found'), ('NUMO-FILE_NOT_FOUND', 1, 'Oops! We were unable to locate the file you requested.')