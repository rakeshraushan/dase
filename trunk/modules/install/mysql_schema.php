<?php

$query =<<<EOF

CREATE TABLE `{$table_prefix}admin_search_table` (
`id` int(11) NOT NULL auto_increment,
`status_id` int(11) default NULL,
`collection_id` int(11) default NULL,
`item_id` int(11) default NULL,
`value_text` text default NULL,
`updated` varchar(50) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}attribute` (
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


CREATE TABLE `{$table_prefix}attribute_category` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`category_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}attribute_item_type` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`item_type_id` int(11) default NULL,
`cardinality` varchar(20) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}category` (
`id` int(11) NOT NULL auto_increment,
`term` varchar(200) default NULL,
`scheme_id` int(11) default NULL,
`label` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}category_scheme` (
`id` int(11) NOT NULL auto_increment,
`uri` varchar(200) default NULL,
`name` varchar(200) default NULL,
`description` varchar(2000) default NULL,
`fixed` tinyint(1) default NULL,
`created_by_eid` varchar(50) default NULL,
`created` varchar(50) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}collection` (
`id` int(11) NOT NULL auto_increment,
`is_public` tinyint(1) default NULL,
`visibility` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`created` varchar(50) default NULL,
`description` varchar(2000) default NULL,
`collection_name` varchar(200) default NULL,
`item_count` int(11) default 0,
`ascii_id` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}collection_category` (
`id` int(11) NOT NULL auto_increment,
`collection_id` int(11) default NULL,
`category_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}collection_manager` (
`id` int(11) NOT NULL auto_increment,
`created` varchar(50) default NULL,
`expiration` varchar(50) default NULL,
`auth_level` varchar(20) default NULL,
`dase_user_eid` varchar(20) default NULL,
`collection_ascii_id` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}comment` (
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

CREATE TABLE `{$table_prefix}content` (
`id` int(11) NOT NULL auto_increment,
`item_id` int(11) default NULL,
`text` text default NULL,
`updated_by_eid` varchar(100) default NULL,
`updated` varchar(100) default NULL,
`p_serial_number` varchar(100) default NULL,
`p_collection_ascii_id` varchar(100) default NULL,
`type` varchar(100) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}dase_user` (
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
`service_key_md5` varchar(200) default NULL,
`eid` varchar(255) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}defined_value` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`value_text` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}input_template` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`collection_manager_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}item` (
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


CREATE TABLE `{$table_prefix}item_category` (
`id` int(11) NOT NULL auto_increment,
`item_id` int(11) default NULL,
`category_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}item_type` (
`id` int(11) NOT NULL auto_increment,
`collection_id` int(11) default NULL,
`description` varchar(2000) default NULL,
`ascii_id` varchar(200) default NULL,
`name` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}item_type_relation` (
`id` int(11) NOT NULL auto_increment,
`parent_type_id` int(11) default NULL,
`child_type_id` int(11) default NULL,
`category_scheme_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}media_file` (
`id` int(11) NOT NULL auto_increment,
`file_size` int(11) default NULL,
`width` int(11) default NULL,
`height` int(11) default NULL,
`item_id` int(11) default NULL,
`md5` varchar(200) default NULL,
`updated` varchar(50) default NULL,
`p_collection_ascii_id` varchar(200) default NULL,
`p_serial_number` varchar(200) default NULL,
`size` varchar(20) default NULL,
`mime_type` varchar(200) default NULL,
`filename` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}search_cache` (
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


CREATE TABLE `{$table_prefix}search_table` (
`id` int(11) NOT NULL auto_increment,
`status_id` int(11) default NULL,
`collection_id` int(11) default NULL,
`item_id` int(11) default NULL,
`value_text` text default NULL,
`updated` varchar(50) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}subscription` (
`id` int(11) NOT NULL auto_increment,
`tag_id` int(11) default NULL,
`dase_user_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}tag` (
`id` int(11) NOT NULL auto_increment,
`is_public` tinyint(1) default NULL,
`admin_collection_id` int(11) default NULL,
`dase_user_id` int(11) default NULL,
`visibility` varchar(50) default NULL,
`eid` varchar(50) default NULL,
`type` varchar(50) default NULL,
`created` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`ascii_id` varchar(200) default NULL,
`background` varchar(20) default NULL,
`description` varchar(200) default NULL,
`item_count` int(11) default 0,
`name` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}tag_category` (
`id` int(11) NOT NULL auto_increment,
`tag_id` int(11) default NULL,
`category_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}tag_item` (
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


CREATE TABLE `{$table_prefix}tag_item_category` (
`id` int(11) NOT NULL auto_increment,
`tag_item_id` int(11) default NULL,
`category_id` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}value` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` int(11) default NULL,
`item_id` int(11) default NULL,
`value_text` text default NULL,
PRIMARY KEY (`id`),
KEY `value_text` (`value_text`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `{$table_prefix}value_revision_history` (
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

