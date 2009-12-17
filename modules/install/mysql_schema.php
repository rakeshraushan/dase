<?php

$query =<<<EOF

CREATE TABLE `{$table_prefix}attribute` (
`id` int(11) NOT NULL auto_increment,
`is_public` boolean default NULL,
`is_on_list_display` boolean default NULL,
`in_basic_search` boolean default NULL,
`is_repeatable` boolean default 1,
`is_required` boolean default 0,
`mapped_admin_att_id` integer default NULL,
`sort_order` integer default NULL,
`collection_id` integer default NULL,
`html_input_type` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`usage_notes` varchar(2000) default NULL,
`attribute_name` varchar(200) default NULL,
`ascii_id` varchar(200) default NULL,
`modifier_type` varchar(200) default NULL,
`modifier_defined_list` text default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}attribute_item_type` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` integer default NULL,
`item_type_id` integer default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}collection` (
`id` int(11) NOT NULL auto_increment,
`is_public` boolean default NULL,
`item_count` integer default NULL,
`visibility` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`created` varchar(50) default NULL,
`description` varchar(2000) default NULL,
`collection_name` varchar(200) default NULL,
`ascii_id` varchar(200) default NULL,
`admin_notes` text default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}collection_manager` (
`id` int(11) NOT NULL auto_increment,
`created_by_eid` varchar(50) default NULL,
`created` varchar(50) default NULL,
`expiration` varchar(50) default NULL,
`auth_level` varchar(20) default NULL,
`dase_user_eid` varchar(20) default NULL,
`collection_ascii_id` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}comment` (
`id` int(11) NOT NULL auto_increment,
`item_id` integer default NULL,
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
`item_id` integer default NULL,
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
`has_access_exception` boolean default NULL,
`current_search_cache_id` integer default NULL,
`max_items` integer default NULL,
`updated` varchar(50) default NULL,
`created` varchar(50) default NULL,
`service_key_md5` varchar(200) default NULL,
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

CREATE TABLE `{$table_prefix}defined_value` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` integer default NULL,
`value_text` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}input_template` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` integer default NULL,
`collection_manager_id` integer default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}item` (
`id` int(11) NOT NULL auto_increment,
`item_type_id` integer default NULL,
`collection_id` integer default NULL,
`p_collection_ascii_id` varchar(200) default NULL,
`created_by_eid` varchar(50) default NULL,
`status` varchar(50) default NULL,
`updated` varchar(50) default NULL,
`comments_updated` varchar(50) default NULL,
`comments_count` integer default NULL,
`content_length` integer default NULL,
`created` varchar(50) default NULL,
`serial_number` varchar(200) default NULL,
`item_type_ascii_id` varchar(200) default NULL,
`item_type_name` varchar(200) default NULL,
`collection_name` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}item_type` (
`id` int(11) NOT NULL auto_increment,
`collection_id` integer default NULL,
`description` varchar(2000) default NULL,
`ascii_id` varchar(200) default NULL,
`name` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}media_file` (
`id` int(11) NOT NULL auto_increment,
`file_size` integer default NULL,
`width` integer default NULL,
`height` integer default NULL,
`item_id` integer default NULL,
`md5` varchar(200) default NULL,
`updated` varchar(50) default NULL,
`p_collection_ascii_id` varchar(200) default NULL,
`p_serial_number` varchar(200) default NULL,
`size` varchar(20) default NULL,
`mime_type` varchar(200) default NULL,
`filename` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}recent_view` (
`id` int(11) NOT NULL auto_increment,
`url` varchar(4000) default NULL,
`title` varchar(200) default NULL,
`dase_user_eid` varchar(100) default NULL,
`timestamp` varchar(50) default NULL,
`type` varchar(20) default NULL,
`count` int(11) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}tag` (
`id` int(11) NOT NULL auto_increment,
`is_public` boolean default NULL,
`item_count` integer default NULL,
`admin_collection_id` integer default NULL,
`dase_user_id` integer default NULL,
`updated` varchar(50) default NULL,
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

CREATE TABLE `{$table_prefix}tag_category` (
`id` int(11) NOT NULL auto_increment,
`category_id` integer default NULL,
`tag_id` integer default NULL,
`scheme` varchar(200) default NULL,
`label` varchar(200) default NULL,
`term` varchar(200) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}tag_item` (
`id` int(11) NOT NULL auto_increment,
`sort_order` integer default NULL,
`item_id` integer default NULL,
`tag_id` integer default NULL,
`updated` varchar(50) default NULL,
`p_collection_ascii_id` varchar(200) default NULL,
`p_serial_number` varchar(200) default NULL,
`size` varchar(200) default NULL,
`annotation` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}value` (
`id` int(11) NOT NULL auto_increment,
`attribute_id` integer default NULL,
`item_id` integer default NULL,
`value_text` text default NULL,
`url` varchar(2000) default NULL,
`modifier` varchar(2000) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$table_prefix}value_revision_history` (
`id` int(11) NOT NULL auto_increment,
`added_text` text default NULL,
`deleted_text` text default NULL,
`timestamp` varchar(50) default NULL,
`collection_ascii_id` varchar(200) default NULL,
`attribute_name` varchar(200) default NULL,
`item_serial_number` varchar(200) default NULL,
`added_modifier` varchar(2000) default NULL,
`deleted_modifier` varchar(2000) default NULL,
`dase_user_eid` varchar(20) default NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

