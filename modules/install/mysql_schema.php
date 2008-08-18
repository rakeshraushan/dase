<?php

$query =<<<EOF

-- phpMyAdmin SQL Dump
-- version 2.11.2
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Aug 17, 2008 at 10:55 AM
-- Server version: 5.0.27
-- PHP Version: 4.3.9

-- (pk commented out) SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `pkeane_dase`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_search_table`
--

CREATE TABLE `admin_search_table` (
  `id` int(11) NOT NULL auto_increment,
  `status_id` int(11) default NULL,
  `collection_id` int(11) default NULL,
  `item_id` int(11) default NULL,
  `value_text` text,
  `updated` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `attribute`
--

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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_item_type`
--

CREATE TABLE `attribute_item_type` (
  `id` int(11) NOT NULL auto_increment,
  `attribute_id` int(11) default NULL,
  `item_type_id` int(11) default NULL,
  `cardinality` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `id` int(11) NOT NULL auto_increment,
  `is_public` tinyint(1) default NULL,
  `visibility` varchar(50) default NULL,
  `updated` varchar(50) default NULL,
  `created` varchar(50) default NULL,
  `description` varchar(2000) default NULL,
  `collection_name` varchar(200) default NULL,
  `ascii_id` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `collection_manager`
--

CREATE TABLE `collection_manager` (
  `id` int(11) NOT NULL auto_increment,
  `created` varchar(50) default NULL,
  `expiration` varchar(50) default NULL,
  `auth_level` varchar(20) default NULL,
  `dase_user_eid` varchar(20) default NULL,
  `collection_ascii_id` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id` int(11) NOT NULL auto_increment,
  `item_id` int(11) default NULL,
  `text` text,
  `updated_by_eid` varchar(100) default NULL,
  `updated` varchar(100) default NULL,
  `p_serial_number` varchar(100) default NULL,
  `p_collection_ascii_id` varchar(100) default NULL,
  `type` varchar(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `dase_user`
--

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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `defined_value`
--

CREATE TABLE `defined_value` (
  `id` int(11) NOT NULL auto_increment,
  `attribute_id` int(11) default NULL,
  `value_text` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `input_template`
--

CREATE TABLE `input_template` (
  `id` int(11) NOT NULL auto_increment,
  `attribute_id` int(11) default NULL,
  `collection_manager_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `id` int(11) NOT NULL auto_increment,
  `item_type_id` int(11) default NULL,
  `collection_id` int(11) default NULL,
  `created_by_eid` varchar(50) default NULL,
  `status` varchar(50) default NULL,
  `updated` varchar(50) default NULL,
  `created` varchar(50) default NULL,
  `serial_number` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `item_link`
--

CREATE TABLE `item_link` (
  `id` int(11) NOT NULL auto_increment,
  `length` int(11) default NULL,
  `item_unique` varchar(100) default NULL,
  `title` varchar(100) default NULL,
  `type` varchar(50) default NULL,
  `rel` varchar(100) default NULL,
  `href` varchar(2000) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `item_type`
--

CREATE TABLE `item_type` (
  `id` int(11) NOT NULL auto_increment,
  `collection_id` int(11) default NULL,
  `description` varchar(2000) default NULL,
  `ascii_id` varchar(200) default NULL,
  `name` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `media_attribute`
--

CREATE TABLE `media_attribute` (
  `id` int(11) NOT NULL auto_increment,
  `sort_order` int(11) default NULL,
  `label` varchar(200) default NULL,
  `term` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `media_file`
--

CREATE TABLE `media_file` (
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `media_value`
--

CREATE TABLE `media_value` (
  `id` int(11) NOT NULL auto_increment,
  `media_attribute_id` int(11) default NULL,
  `media_file_id` int(11) default NULL,
  `text` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_cache`
--

CREATE TABLE `search_cache` (
  `id` int(11) NOT NULL auto_increment,
  `is_stale` tinyint(1) default NULL,
  `cb_id` int(11) default NULL,
  `sort_by` int(11) default NULL,
  `exact_search` int(11) default NULL,
  `attribute_id` int(11) default NULL,
  `dase_user_id` int(11) default NULL,
  `item_id_string` text,
  `timestamp` varchar(50) default NULL,
  `search_md5` varchar(40) default NULL,
  `refine` varchar(2000) default NULL,
  `collection_id_string` varchar(2000) default NULL,
  `query` varchar(2000) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_table`
--

CREATE TABLE `search_table` (
  `id` int(11) NOT NULL auto_increment,
  `status_id` int(11) default NULL,
  `collection_id` int(11) default NULL,
  `item_id` int(11) default NULL,
  `value_text` text,
  `updated` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `id` int(11) NOT NULL auto_increment,
  `tag_id` int(11) default NULL,
  `dase_user_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag_item`
--

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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_history`
--

CREATE TABLE `user_history` (
  `id` int(11) NOT NULL auto_increment,
  `updated` varchar(50) default NULL,
  `summary` varchar(2000) default NULL,
  `title` varchar(100) default NULL,
  `type` varchar(20) default NULL,
  `href` varchar(2000) default NULL,
  `eid` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `value`
--

CREATE TABLE `value` (
  `id` int(11) NOT NULL auto_increment,
  `attribute_id` int(11) default NULL,
  `item_id` int(11) default NULL,
  `value_text` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `value_revision_history`
--

CREATE TABLE `value_revision_history` (
  `id` int(11) NOT NULL auto_increment,
  `added_text` text,
  `deleted_text` text,
  `timestamp` varchar(50) default NULL,
  `collection_ascii_id` varchar(200) default NULL,
  `attribute_name` varchar(200) default NULL,
  `item_serial_number` varchar(200) default NULL,
  `dase_user_eid` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
EOF;
