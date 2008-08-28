<?php

$query =<<<EOF

DROP TABLE IF EXISTS `admin_search_table`;
CREATE TABLE `admin_search_table` (
`id` int(11) NOT NULL auto_increment,
`status_id` int(11) default NULL,
`collection_id` int(11) default NULL,
`item_id` int(11) default NULL,
`value_text` text default NULL,
`updated` varchar(50) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `attribute`;
CREATE TABLE `attribute` (
`id` int(11) NOT NULL auto_increment,
`is_public` tinyint(1) default NULL,
`is_on_list_display` tinyint(1) default NULL,
`in_basic_search` tinyint(1) default NULL,
`mapped_admin_att_id` int(11) default NULL,
`sort_order` int(11) default NULL,
`collection_id` int(11) default NULL,
`html_input_type` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`usage_notes` varchar(2000) default NULL,
`attribute_name` varchar(200) default NULL,
`ascii_id` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `attribute_item_type`;
CREATE TABLE `attribute_item_type` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`item_type_id` int(11) default NULL,
`cardinality` varchar(20) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `collection`;
CREATE TABLE `collection` (
`id` int(11) NOT NULL auto_increment,
`is_public` tinyint(1) default NULL,
`visibility` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`created` varchar(50) default NULL,
`description` varchar(2000) default NULL,
`collection_name` varchar(200) default NULL,
`ascii_id` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `collection_manager`;
CREATE TABLE `collection_manager` (
`id` int(11) NOT NULL auto_increment,
`created` varchar(50) default NULL,
`expiration` varchar(50) default NULL,
`auth_level` varchar(20) default NULL,
`dase_user_eid` varchar(20) default NULL,
`collection_ascii_id` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
`id` int(11) NOT NULL auto_increment,
`item_id` int(11) default NULL,
`text` text default NULL,
`updated_by_eid` varchar(100) default NULL,
`updated` varchar(100) default NULL,
`p_serial_number` varchar(100) default NULL,
`p_collection_ascii_id` varchar(100) default NULL,
`type` varchar(10) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dase_user`;
CREATE TABLE `dase_user` (
`id` int(11) NOT NULL auto_increment,
`has_access_exception` tinyint(1) default NULL,
`current_search_cache_id` int(11) default NULL,
`max_items` int(11) default NULL,
`template_composite` varchar(2000) default NULL,
`backtrack` varchar(2000) default NULL,
`current_collections` varchar(2000) default NULL,
`last_action` varchar(2000) default NULL,
`last_item` varchar(2000) default NULL,
`display` varchar(20) default NULL,
`last_cb_access` varchar(50) default NULL,
`cb` varchar(200) default NULL,
`name` varchar(200) default NULL,
`eid` varchar(255) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `defined_value`;
CREATE TABLE `defined_value` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`value_text` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `input_template`;
CREATE TABLE `input_template` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`collection_manager_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
`id` int(11) NOT NULL auto_increment,
`item_type_id` int(11) default NULL,
`collection_id` int(11) default NULL,
`created_by_eid` varchar(50) default NULL,
`status` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`created` varchar(50) default NULL,
`serial_number` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `item_link`;
CREATE TABLE `item_link` (
`id` int(11) NOT NULL auto_increment,
`length` int(11) default NULL,
`item_unique` varchar(100) default NULL,
`title` varchar(100) default NULL,
`type` varchar(50) default NULL,
`rel` varchar(100) default NULL,
`href` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `item_type`;
CREATE TABLE `item_type` (
`id` int(11) NOT NULL auto_increment,
`collection_id` int(11) default NULL,
`description` varchar(2000) default NULL,
`ascii_id` varchar(200) default NULL,
`name` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `media_file`;
CREATE TABLE `media_file` (
`id` int(11) NOT NULL auto_increment,
`duration` int(11) default NULL,
`file_size` int(11) default NULL,
`width` int(11) default NULL,
`height` int(11) default NULL,
`item_id` int(11) default NULL,
`channels` varchar(20) default NULL,
`samplingrate` varchar(20) default NULL,
`framerate` varchar(20) default NULL,
`bitrate` varchar(20) default NULL,
`md5` varchar(200) default NULL,
`updated` varchar(50) default NULL,
`p_collection_ascii_id` varchar(200) default NULL,
`p_serial_number` varchar(200) default NULL,
`size` varchar(20) default NULL,
`mime_type` varchar(200) default NULL,
`filename` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `search_cache`;
CREATE TABLE `search_cache` (
`id` int(11) NOT NULL auto_increment,
`is_stale` tinyint(1) default NULL,
`cb_id` int(11) default NULL,
`sort_by` int(11) default NULL,
`exact_search` int(11) default NULL,
`attribute_id` int(11) default NULL,
`dase_user_id` int(11) default NULL,
`item_id_string` text default NULL,
`timestamp` varchar(50) default NULL,
`search_md5` varchar(40) default NULL,
`refine` varchar(2000) default NULL,
`collection_id_string` varchar(2000) default NULL,
`query` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `search_table`;
CREATE TABLE `search_table` (
`id` int(11) NOT NULL auto_increment,
`status_id` int(11) default NULL,
`collection_id` int(11) default NULL,
`item_id` int(11) default NULL,
`value_text` text default NULL,
`updated` varchar(50) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `subscription`;
CREATE TABLE `subscription` (
`id` int(11) NOT NULL auto_increment,
`tag_id` int(11) default NULL,
`dase_user_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
`id` int(11) NOT NULL auto_increment,
`is_public` tinyint(1) default NULL,
`admin_collection_id` int(11) default NULL,
`dase_user_id` int(11) default NULL,
`visibility` varchar(50) default NULL,
`eid` varchar(50) default NULL,
`type` varchar(50) default NULL,
`created` varchar(50) default NULL,
`ascii_id` varchar(200) default NULL,
`background` varchar(20) default NULL,
`description` varchar(200) default NULL,
`name` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tag_item`;
CREATE TABLE `tag_item` (
`id` int(11) NOT NULL auto_increment,
`sort_order` int(11) default NULL,
`item_id` int(11) default NULL,
`tag_id` int(11) default NULL,
`updated` varchar(50) default NULL,
`p_collection_ascii_id` varchar(200) default NULL,
`p_serial_number` varchar(20) default NULL,
`size` varchar(200) default NULL,
`annotation` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `value`;
CREATE TABLE `value` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`item_id` int(11) default NULL,
`value_text` text default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `value_revision_history`;
CREATE TABLE `value_revision_history` (
`id` int(11) NOT NULL auto_increment,
`added_text` text default NULL,
`deleted_text` text default NULL,
`timestamp` varchar(50) default NULL,
`collection_ascii_id` varchar(200) default NULL,
`attribute_name` varchar(200) default NULL,
`item_serial_number` varchar(200) default NULL,
`dase_user_eid` varchar(20) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


EOF;

