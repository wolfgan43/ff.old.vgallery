-- phpMyAdmin SQL Dump
-- version 2.11.3deb1ubuntu1.2
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generato il: 12 Nov, 2010 at 07:42 PM
-- Versione MySQL: 5.0.51
-- Versione PHP: 5.2.4-2ubuntu5.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `www_blueocarina_net`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph`
--

DROP TABLE IF EXISTS `anagraph`;
CREATE TABLE IF NOT EXISTS `anagraph` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `order` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_type` int(11) NOT NULL default '0',
  `last_update` int(10) NOT NULL default '0',
  `shippingreference` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingaddress` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingcap` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingtown` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingprovince` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingstate` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `categories` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_type` (`ID_type`),
  KEY `vgallery_type` (`ID_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph_categories`
--

DROP TABLE IF EXISTS `anagraph_categories`;
CREATE TABLE IF NOT EXISTS `anagraph_categories` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph_fields`
--

DROP TABLE IF EXISTS `anagraph_fields`;
CREATE TABLE IF NOT EXISTS `anagraph_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_type` int(11) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_extended_type` int(11) NOT NULL default '0',
  `ID_group_backoffice` int(11) NOT NULL default '0',
  `require` char(1) collate utf8_unicode_ci NOT NULL,
  `ID_check_control` int(11) NOT NULL default '0',
  `unic_value` char(1) collate utf8_unicode_ci NOT NULL,
  `send_mail` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_mail` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_grid` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_menu` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_document` char(1) collate utf8_unicode_ci NOT NULL,
  `writable` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_module` (`ID_type`),
  KEY `ID_extended_type` (`ID_extended_type`),
  KEY `ID_selection` (`ID_selection`),
  KEY `ID_check_control` (`ID_check_control`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph_type_group`
--

DROP TABLE IF EXISTS `anagraph_type_group`;
CREATE TABLE IF NOT EXISTS `anagraph_type_group` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph_fields_selection`
--

DROP TABLE IF EXISTS `anagraph_fields_selection`;
CREATE TABLE IF NOT EXISTS `anagraph_fields_selection` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_vgallery_type` int(11) NOT NULL,
  `ID_vgallery_fields` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_vgallery_type` (`ID_vgallery_type`),
  KEY `ID_vgallery_fields` (`ID_vgallery_fields`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph_fields_selection_value`
--

DROP TABLE IF EXISTS `anagraph_fields_selection_value`;
CREATE TABLE IF NOT EXISTS `anagraph_fields_selection_value` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_selection` int(11) NOT NULL default '0',
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_selection` (`ID_selection`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph_rel_nodes_fields`
--

DROP TABLE IF EXISTS `anagraph_rel_nodes_fields`;
CREATE TABLE IF NOT EXISTS `anagraph_rel_nodes_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `description` text collate utf8_unicode_ci NOT NULL,
  `description_text` text collate utf8_unicode_ci NOT NULL,
  `ID_fields` int(11) NOT NULL default '0',
  `ID_nodes` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_fields` (`ID_fields`),
  KEY `ID_nodes` (`ID_anagraph`),
  KEY `fields_nodes_lang` (`ID_fields`,`ID_anagraph`),
  FULLTEXT KEY `description` (`value`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anagraph_type`
--

DROP TABLE IF EXISTS `anagraph_type`;
CREATE TABLE IF NOT EXISTS `anagraph_type` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cache_page`
--

DROP TABLE IF EXISTS `cache_page`;
CREATE TABLE IF NOT EXISTS `cache_page` (
  `ID` int(11) NOT NULL auto_increment,
  `user_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `disk_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_user` int(11) NOT NULL,
  `lang` varchar(255) collate utf8_unicode_ci NOT NULL,
  `get` varchar(255) collate utf8_unicode_ci NOT NULL,
  `post` varchar(255) collate utf8_unicode_ci NOT NULL,
  `section_blocks` varchar(255) collate utf8_unicode_ci NOT NULL,
  `layout_blocks` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ff_blocks` varchar(255) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  `frequency` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_user` (`ID_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cache_sid`
--

DROP TABLE IF EXISTS `cache_sid`;
CREATE TABLE IF NOT EXISTS `cache_sid` (
  `sid` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` text collate utf8_unicode_ci NOT NULL,
  `unic_key` text collate utf8_unicode_ci NOT NULL,
  `uid` int(11) NOT NULL,
  FULLTEXT KEY `sid` (`sid`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `check_control`
--

DROP TABLE IF EXISTS `check_control`;
CREATE TABLE IF NOT EXISTS `check_control` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `regexp` text collate utf8_unicode_ci NOT NULL,
  `ff_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_charset_decode`
--

DROP TABLE IF EXISTS `cm_charset_decode`;
CREATE TABLE IF NOT EXISTS `cm_charset_decode` (
  `ID` int(11) NOT NULL auto_increment,
  `code` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_layout`
--

DROP TABLE IF EXISTS `cm_layout`;
CREATE TABLE IF NOT EXISTS `cm_layout` (
  `ID` int(11) NOT NULL auto_increment,
  `path` tinytext collate utf8_unicode_ci NOT NULL,
  `layer` varchar(255) collate utf8_unicode_ci NOT NULL,
  `page` varchar(255) collate utf8_unicode_ci NOT NULL,
  `theme` varchar(255) collate utf8_unicode_ci NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `main_theme` varchar(255) collate utf8_unicode_ci NOT NULL,
  `reset_css` char(1) collate utf8_unicode_ci NOT NULL,
  `reset_js` char(1) collate utf8_unicode_ci NOT NULL,
  `class_body` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `reset_cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `ignore_defaults` char(1) collate utf8_unicode_ci NOT NULL,
  `exclude_ff_js` char(1) collate utf8_unicode_ci NOT NULL,
  `exclude_form` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_layout_css`
--

DROP TABLE IF EXISTS `cm_layout_css`;
CREATE TABLE IF NOT EXISTS `cm_layout_css` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout` int(11) NOT NULL,
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `file` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout` (`ID_layout`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_layout_js`
--

DROP TABLE IF EXISTS `cm_layout_js`;
CREATE TABLE IF NOT EXISTS `cm_layout_js` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout` int(11) NOT NULL,
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `file` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `plugin_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `js_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout` (`ID_layout`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_layout_meta`
--

DROP TABLE IF EXISTS `cm_layout_meta`;
CREATE TABLE IF NOT EXISTS `cm_layout_meta` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `content` varchar(255) collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout` (`ID_layout`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_layout_sect`
--

DROP TABLE IF EXISTS `cm_layout_sect`;
CREATE TABLE IF NOT EXISTS `cm_layout_sect` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout` (`ID_layout`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_restricted_settings`
--

DROP TABLE IF EXISTS `cm_mod_restricted_settings`;
CREATE TABLE IF NOT EXISTS `cm_mod_restricted_settings` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_domains` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `descrizione` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_security_domains`
--

DROP TABLE IF EXISTS `cm_mod_security_domains`;
CREATE TABLE IF NOT EXISTS `cm_mod_security_domains` (
  `ID` int(11) NOT NULL auto_increment,
  `nome` varchar(255) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `company_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `creation_date` date NOT NULL,
  `expiration_date` date NOT NULL,
  `time_zone` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `billing_status` int(11) NOT NULL,
  `ip_address` varchar(255) collate utf8_unicode_ci NOT NULL,
  `version` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_security_domains_fields`
--

DROP TABLE IF EXISTS `cm_mod_security_domains_fields`;
CREATE TABLE IF NOT EXISTS `cm_mod_security_domains_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_domains` int(11) NOT NULL,
  `group` varchar(255) collate utf8_unicode_ci NOT NULL,
  `field` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_security_groups`
--

DROP TABLE IF EXISTS `cm_mod_security_groups`;
CREATE TABLE IF NOT EXISTS `cm_mod_security_groups` (
  `gid` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `registration` char(1) collate utf8_unicode_ci NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_security_timezones`
--

DROP TABLE IF EXISTS `cm_mod_security_timezones`;
CREATE TABLE IF NOT EXISTS `cm_mod_security_timezones` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_security_users`
--

DROP TABLE IF EXISTS `cm_mod_security_users`;
CREATE TABLE IF NOT EXISTS `cm_mod_security_users` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_domains` int(11) NOT NULL,
  `level` char(1) collate utf8_unicode_ci NOT NULL,
  `expiration` datetime NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  `active_sid` varchar(255) collate utf8_unicode_ci NOT NULL,
  `username` varchar(64) collate utf8_unicode_ci NOT NULL,
  `password` varchar(64) collate utf8_unicode_ci NOT NULL,
  `primary_gid` int(4) NOT NULL default '0',
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingreference` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingaddress` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingcap` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingtown` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingprovince` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingstate` int(11) NOT NULL default '0',
  `enable_ecommerce_data` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_manage` char(1) collate utf8_unicode_ci NOT NULL,
  `ID_module_register` int(11) NOT NULL,
  `public` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_module_register` (`ID_module_register`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_security_users_fields`
--

DROP TABLE IF EXISTS `cm_mod_security_users_fields`;
CREATE TABLE IF NOT EXISTS `cm_mod_security_users_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_users` int(11) NOT NULL,
  `field` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_mod_security_users_rel_groups`
--

DROP TABLE IF EXISTS `cm_mod_security_users_rel_groups`;
CREATE TABLE IF NOT EXISTS `cm_mod_security_users_rel_groups` (
  `uid` int(4) NOT NULL default '0',
  `gid` int(4) NOT NULL default '0',
  UNIQUE KEY `uid` (`uid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_showfiles`
--

DROP TABLE IF EXISTS `cm_showfiles`;
CREATE TABLE IF NOT EXISTS `cm_showfiles` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `field_file` varchar(255) collate utf8_unicode_ci NOT NULL,
  `path_full` varchar(255) collate utf8_unicode_ci NOT NULL,
  `path_temp` varchar(255) collate utf8_unicode_ci NOT NULL,
  `source` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_showfiles_modes`
--

DROP TABLE IF EXISTS `cm_showfiles_modes`;
CREATE TABLE IF NOT EXISTS `cm_showfiles_modes` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_showfiles` int(11) NOT NULL,
  `alignment` varchar(255) collate utf8_unicode_ci NOT NULL,
  `alpha` int(11) NOT NULL,
  `bgcolor` varchar(255) collate utf8_unicode_ci NOT NULL,
  `dim_x` varchar(255) collate utf8_unicode_ci NOT NULL,
  `dim_y` varchar(255) collate utf8_unicode_ci NOT NULL,
  `max_x` int(11) NOT NULL,
  `max_y` int(11) NOT NULL,
  `mode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `format` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shortdesc` tinytext collate utf8_unicode_ci NOT NULL,
  `theme` varchar(255) collate utf8_unicode_ci NOT NULL,
  `when` varchar(255) collate utf8_unicode_ci NOT NULL,
  `wmk_enable` char(1) collate utf8_unicode_ci NOT NULL,
  `wmk_alignment` varchar(255) collate utf8_unicode_ci NOT NULL,
  `wmk_image` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cm_showfiles_where`
--

DROP TABLE IF EXISTS `cm_showfiles_where`;
CREATE TABLE IF NOT EXISTS `cm_showfiles_where` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_showfiles` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `dbskip` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `comment_rel_module_form`
--

DROP TABLE IF EXISTS `comment_rel_module_form`;
CREATE TABLE IF NOT EXISTS `comment_rel_module_form` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_nodes` int(11) NOT NULL,
  `ID_form` int(11) NOT NULL,
  `tbl_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_form_node` int(11) NOT NULL,
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `uid` int(11) NOT NULL,
  `nick` varchar(255) collate utf8_unicode_ci NOT NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `website` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_form` (`ID_form`),
  KEY `ID_form_node` (`ID_form_node`),
  KEY `ID_nodes` (`ID_nodes`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `drafts`
--

DROP TABLE IF EXISTS `drafts`;
CREATE TABLE IF NOT EXISTS `drafts` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `att` char(1) collate utf8_unicode_ci NOT NULL,
  `order` double NOT NULL default '0',
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `drafts_rel_languages`
--

DROP TABLE IF EXISTS `drafts_rel_languages`;
CREATE TABLE IF NOT EXISTS `drafts_rel_languages` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_drafts` int(11) NOT NULL,
  `ID_languages` int(11) NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_drafts` (`ID_drafts`),
  KEY `ID_languages` (`ID_languages`),
  KEY `drafts_languages` (`ID_drafts`,`ID_languages`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_documents_bill`
--

DROP TABLE IF EXISTS `ecommerce_documents_bill`;
CREATE TABLE IF NOT EXISTS `ecommerce_documents_bill` (
  `ID` int(11) NOT NULL auto_increment,
  `SID` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_anagraph` int(11) NOT NULL,
  `ID_ddt` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `operation` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_type` int(11) NOT NULL,
  `pdf` varchar(255) collate utf8_unicode_ci NOT NULL,
  `bill_id` varchar(255) collate utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `object` varchar(255) collate utf8_unicode_ci NOT NULL,
  `note` text collate utf8_unicode_ci NOT NULL,
  `total_price` double NOT NULL,
  `total_discount` double NOT NULL,
  `real_price` double NOT NULL,
  `real_vat` double NOT NULL,
  `shipping_price` double NOT NULL,
  `total_bill` double NOT NULL,
  `total_account` double NOT NULL,
  `unsolved` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_anagraph` (`ID_anagraph`),
  KEY `ID_ddt` (`ID_ddt`),
  KEY `ID_type` (`ID_type`),
  KEY `SID` (`SID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_documents_bill_detail`
--

DROP TABLE IF EXISTS `ecommerce_documents_bill_detail`;
CREATE TABLE IF NOT EXISTS `ecommerce_documents_bill_detail` (
  `ID` int(11) NOT NULL auto_increment,
  `SID` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_bill` int(11) NOT NULL default '0',
  `tbl_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_items` int(11) NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `qta` int(11) NOT NULL default '0',
  `price` double NOT NULL,
  `discount` int(3) NOT NULL,
  `vat` int(3) NOT NULL,
  `vat_indetraible` int(3) NOT NULL,
  `decumulation` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_items` (`ID_items`),
  KEY `tbl_src` (`tbl_src`),
  KEY `tblsrc_items` (`tbl_src`,`ID_items`),
  KEY `ID_bill` (`ID_bill`),
  KEY `SID` (`SID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=1;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_documents_bill_detail_rel`
--

DROP TABLE IF EXISTS `ecommerce_documents_bill_detail_rel`;
CREATE TABLE IF NOT EXISTS `ecommerce_documents_bill_detail_rel` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_cost` int(11) NOT NULL,
  `ID_revenue` int(11) NOT NULL,
  `qta_cost` int(11) NOT NULL,
  `vat_cost` double NOT NULL,
  `price_cost` double NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_cost` (`ID_cost`),
  KEY `ID_revenue` (`ID_revenue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_documents_ddt`
--

DROP TABLE IF EXISTS `ecommerce_documents_ddt`;
CREATE TABLE IF NOT EXISTS `ecommerce_documents_ddt` (
  `ID` int(11) NOT NULL auto_increment,
  `operation` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `price` double NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_documents_payments`
--

DROP TABLE IF EXISTS `ecommerce_documents_payments`;
CREATE TABLE IF NOT EXISTS `ecommerce_documents_payments` (
  `ID` int(11) NOT NULL auto_increment,
  `SID` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_bill` int(11) NOT NULL,
  `ID_ecommerce_mpay` int(11) NOT NULL,
  `ID_ecommerce_mpay_container` int(11) NOT NULL,
  `date` date NOT NULL,
  `operation` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` double NOT NULL,
  `payed_value` double NOT NULL,
  `rebate` double NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_ecommerce_mpay` (`ID_ecommerce_mpay`),
  KEY `ID_ecommerce_mpay_container` (`ID_ecommerce_mpay_container`),
  KEY `SID` (`SID`),
  KEY `ID_bill` (`ID_bill`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_documents_type`
--

DROP TABLE IF EXISTS `ecommerce_documents_type`;
CREATE TABLE IF NOT EXISTS `ecommerce_documents_type` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ecommerce` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_mpay`
--

DROP TABLE IF EXISTS `ecommerce_mpay`;
CREATE TABLE IF NOT EXISTS `ecommerce_mpay` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `days` int(11) NOT NULL,
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  `ecommerce` char(1) collate utf8_unicode_ci NOT NULL,
  `service_test_domain` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_test_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_test_account` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_test_secure` char(1) collate utf8_unicode_ci NOT NULL,
  `service_test_mac_key` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_domain` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_account` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_secure` char(1) collate utf8_unicode_ci NOT NULL,
  `service_mac_key` varchar(255) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_mpay_container`
--

DROP TABLE IF EXISTS `ecommerce_mpay_container`;
CREATE TABLE IF NOT EXISTS `ecommerce_mpay_container` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `info` text collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_mpay_rel_container`
--

DROP TABLE IF EXISTS `ecommerce_mpay_rel_container`;
CREATE TABLE IF NOT EXISTS `ecommerce_mpay_rel_container` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_ecommerce_mpay` int(11) NOT NULL,
  `ID_ecommerce_mpay_container` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_ecommerce_mpay` (`ID_ecommerce_mpay`),
  KEY `ID_ecommerce_mpay_container` (`ID_ecommerce_mpay_container`),
  KEY `mpay_container` (`ID_ecommerce_mpay`,`ID_ecommerce_mpay_container`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_order`
--

DROP TABLE IF EXISTS `ecommerce_order`;
CREATE TABLE IF NOT EXISTS `ecommerce_order` (
  `ID` int(11) NOT NULL auto_increment,
  `SID` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_anagraph` int(11) NOT NULL,
  `shippingaddress` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingcap` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingtown` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingprovince` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shippingstate` int(11) NOT NULL,
  `shippingreference` text collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `ID_type` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `object` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_bill` int(11) NOT NULL,
  `ID_ecommerce_shipping` int(11) NOT NULL,
  `is_cart` char(1) collate utf8_unicode_ci NOT NULL,
  `ID_user_cart` int(11) NOT NULL,
  `mpay_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `SID` (`SID`),
  KEY `ID_anagraph` (`ID_anagraph`),
  KEY `ID_type` (`ID_type`),
  KEY `ID_bill` (`ID_bill`),
  KEY `ID_ecommerce_shipping` (`ID_ecommerce_shipping`),
  KEY `ID_user_cart` (`ID_user_cart`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_order_detail`
--

DROP TABLE IF EXISTS `ecommerce_order_detail`;
CREATE TABLE IF NOT EXISTS `ecommerce_order_detail` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_order` int(11) NOT NULL default '0',
  `ID_bill_detail` int(11) NOT NULL,
  `ID_bill_cost_detail` int(11) NOT NULL,
  `tbl_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_items` int(11) NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `date_since` date NOT NULL,
  `date_to` date NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `qta` int(11) NOT NULL default '0',
  `stock_used` int(11) NOT NULL,
  `reserve_stock_used` int(11) NOT NULL,
  `price` double NOT NULL,
  `discount` int(3) NOT NULL,
  `account` int(3) NOT NULL,
  `weight` double NOT NULL,
  `vat` int(3) NOT NULL,
  `vat_indetraible` int(3) NOT NULL,
  `decumulation` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `tbl_src` (`tbl_src`),
  KEY `ID_items` (`ID_items`),
  KEY `tblsrc_items` (`tbl_src`,`ID_items`),
  KEY `ID_bill_detail` (`ID_bill_detail`),
  KEY `ID_order` (`ID_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=1;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_order_detail_specialsupport`
--

DROP TABLE IF EXISTS `ecommerce_order_detail_specialsupport`;
CREATE TABLE IF NOT EXISTS `ecommerce_order_detail_specialsupport` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_order_detail` int(11) NOT NULL default '0',
  `ID_ecommerce_specialsupport` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `discount` int(3) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_order_detail` (`ID_order_detail`),
  KEY `ID_ecommerce_specialsupport` (`ID_ecommerce_specialsupport`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=1;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_order_history`
--

DROP TABLE IF EXISTS `ecommerce_order_history`;
CREATE TABLE IF NOT EXISTS `ecommerce_order_history` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_order` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_order` (`ID_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_pricelist`
--

DROP TABLE IF EXISTS `ecommerce_pricelist`;
CREATE TABLE IF NOT EXISTS `ecommerce_pricelist` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_ecommerce_settings` int(11) NOT NULL,
  `att` char(1) collate utf8_unicode_ci NOT NULL,
  `date_since` date NOT NULL default '0000-00-00',
  `date_to` date NOT NULL default '0000-00-00',
  `qta_since` int(11) NOT NULL,
  `qta_to` int(11) NOT NULL,
  `price` double NOT NULL default '0',
  `stock` int(11) NOT NULL,
  `reserve_stock` int(11) NOT NULL,
  `discount` int(3) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_ecommerce_settings` (`ID_ecommerce_settings`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_settings`
--

DROP TABLE IF EXISTS `ecommerce_settings`;
CREATE TABLE IF NOT EXISTS `ecommerce_settings` (
  `ID` int(11) NOT NULL auto_increment,
  `tbl_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_items` int(11) NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `mode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `usestock` char(1) collate utf8_unicode_ci NOT NULL,
  `usereservestock` char(1) collate utf8_unicode_ci NOT NULL,
  `useunic` char(1) collate utf8_unicode_ci NOT NULL,
  `useshipping` char(1) collate utf8_unicode_ci NOT NULL,
  `usediscount` char(1) collate utf8_unicode_ci NOT NULL,
  `useaccount` char(1) collate utf8_unicode_ci NOT NULL,
  `useqta_min` char(1) collate utf8_unicode_ci NOT NULL,
  `usepricelist` char(1) collate utf8_unicode_ci NOT NULL,
  `usespecialsupport` char(1) collate utf8_unicode_ci NOT NULL,
  `stock` int(11) NOT NULL,
  `reserve_stock` int(11) NOT NULL,
  `weight` double NOT NULL,
  `buy_price` double NOT NULL,
  `basic_price` double NOT NULL,
  `basic_discount` int(3) NOT NULL,
  `account` int(3) NOT NULL,
  `qta_min` int(11) NOT NULL,
  `show_vat` char(1) collate utf8_unicode_ci NOT NULL,
  `vat` varchar(3) collate utf8_unicode_ci NOT NULL,
  `vat_indetraible` int(3) NOT NULL COMMENT 'da gestire',
  `decumulation` varchar(255) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `actual_qta` int(11) NOT NULL,
  `actual_vat` double NOT NULL,
  `actual_price` double NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `tblsrc_items` (`tbl_src`,`ID_items`),
  KEY `tbl_src` (`tbl_src`),
  KEY `ID_items` (`ID_items`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_shipping`
--

DROP TABLE IF EXISTS `ecommerce_shipping`;
CREATE TABLE IF NOT EXISTS `ecommerce_shipping` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  `ecommerce` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_shipping_price`
--

DROP TABLE IF EXISTS `ecommerce_shipping_price`;
CREATE TABLE IF NOT EXISTS `ecommerce_shipping_price` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_ecommerce_shipping` int(11) NOT NULL,
  `zona` int(11) NOT NULL default '0',
  `weight_min` double NOT NULL default '0',
  `weight_max` double NOT NULL default '0',
  `price` double NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_ecommerce_shipping` (`ID_ecommerce_shipping`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_specialsupport`
--

DROP TABLE IF EXISTS `ecommerce_specialsupport`;
CREATE TABLE IF NOT EXISTS `ecommerce_specialsupport` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_ecommerce_settings` int(11) NOT NULL,
  `att` char(1) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `price` double NOT NULL default '0',
  `discount` int(3) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_ecommerce_settings` (`ID_ecommerce_settings`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `email`
--

DROP TABLE IF EXISTS `email`;
CREATE TABLE IF NOT EXISTS `email` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_email_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `from_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `from_email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `email_address`
--

DROP TABLE IF EXISTS `email_address`;
CREATE TABLE IF NOT EXISTS `email_address` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `uid` int(11) NOT NULL default '0',
  `error` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `email_rel_address`
--

DROP TABLE IF EXISTS `email_rel_address`;
CREATE TABLE IF NOT EXISTS `email_rel_address` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_email` int(11) NOT NULL default '0',
  `ID_address` int(11) NOT NULL default '0',
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_email` (`ID_email`),
  KEY `ID_address` (`ID_address`),
  KEY `email_address` (`ID_email`,`ID_address`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `extended_type`
--

DROP TABLE IF EXISTS `extended_type`;
CREATE TABLE IF NOT EXISTS `extended_type` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ff_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ff_international`
--

DROP TABLE IF EXISTS `ff_international`;
CREATE TABLE IF NOT EXISTS `ff_international` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_lang` int(11) NOT NULL default '0',
  `word_code` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `lang_wordcode` (`ID_lang`,`word_code`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `ff_languages`
--

DROP TABLE IF EXISTS `ff_languages`;
CREATE TABLE IF NOT EXISTS `ff_languages` (
  `ID` int(11) NOT NULL auto_increment,
  `code` char(3) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  `tiny_code` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Struttura della tabella `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE IF NOT EXISTS `files` (
  `ID` int(4) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `parent` varchar(255) collate utf8_unicode_ci NOT NULL,
  `is_dir` char(1) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `last_update` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Struttura della tabella `files_description`
--

DROP TABLE IF EXISTS `files_description`;
CREATE TABLE IF NOT EXISTS `files_description` (
  `ID` int(4) NOT NULL auto_increment,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `ID_files` int(4) NOT NULL default '0',
  `ID_languages` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_files` (`ID_files`),
  KEY `ID_languages` (`ID_languages`),
  KEY `files_languages` (`ID_files`,`ID_languages`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `files_rel_groups`
--

DROP TABLE IF EXISTS `files_rel_groups`;
CREATE TABLE IF NOT EXISTS `files_rel_groups` (
  `ID_files` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `mod` char(1) collate utf8_unicode_ci NOT NULL,
  KEY `ID_files` (`ID_files`),
  KEY `gid` (`gid`),
  KEY `files_gid` (`ID_files`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `files_rel_languages`
--

DROP TABLE IF EXISTS `files_rel_languages`;
CREATE TABLE IF NOT EXISTS `files_rel_languages` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_files` int(11) NOT NULL default '0',
  `ID_languages` int(11) NOT NULL default '0',
  `alias` varchar(255) collate utf8_unicode_ci NOT NULL,
  `keywords` text collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `description_language` text collate utf8_unicode_ci NOT NULL,
  `smart_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `meta_title_alt` varchar(255) collate utf8_unicode_ci NOT NULL,
  `meta_title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `meta_description` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_files` (`ID_files`),
  KEY `ID_languages` (`ID_languages`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `js`
--

DROP TABLE IF EXISTS `js`;
CREATE TABLE IF NOT EXISTS `js` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `base_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `src_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `preload_cnf` varchar(255) collate utf8_unicode_ci NOT NULL,
  `load_css` varchar(255) collate utf8_unicode_ci NOT NULL,
  `postload_cnf` varchar(255) collate utf8_unicode_ci NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL default '0',
  `async` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `js_config`
--

DROP TABLE IF EXISTS `js_config`;
CREATE TABLE IF NOT EXISTS `js_config` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_js` int(11) NOT NULL default '0',
  `src_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `action` varchar(255) collate utf8_unicode_ci NOT NULL,
  `when` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_js` (`ID_js`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `js_dipendence`
--

DROP TABLE IF EXISTS `js_dipendence`;
CREATE TABLE IF NOT EXISTS `js_dipendence` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_js_plugin` int(11) NOT NULL default '0',
  `ID_js_libs` int(11) NOT NULL default '0',
  `param_value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_js_plugin` (`ID_js_plugin`),
  KEY `ID_js_libs` (`ID_js_libs`),
  KEY `js_plugin_libs` (`ID_js_plugin`,`ID_js_libs`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout`
--

DROP TABLE IF EXISTS `layout`;
CREATE TABLE IF NOT EXISTS `layout` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_type` int(11) NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  `params` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_location` int(11) NOT NULL default '0',
  `order` int(11) NOT NULL default '0',
  `last_update` int(10) NOT NULL,
  `use_ajax` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_type` (`ID_type`),
  KEY `ID_location` (`ID_location`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_layer`
--

DROP TABLE IF EXISTS `layout_layer`;
CREATE TABLE IF NOT EXISTS `layout_layer` (
  `ID` int(11) NOT NULL auto_increment,
  `force_width` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `order` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_location`
--

DROP TABLE IF EXISTS `layout_location`;
CREATE TABLE IF NOT EXISTS `layout_location` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `interface_level` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `ID_layer` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layer` (`ID_layer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_location_path`
--

DROP TABLE IF EXISTS `layout_location_path`;
CREATE TABLE IF NOT EXISTS `layout_location_path` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout_location` int(11) NOT NULL default '0',
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout_location` (`ID_layout_location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_path`
--

DROP TABLE IF EXISTS `layout_path`;
CREATE TABLE IF NOT EXISTS `layout_path` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout` int(11) NOT NULL default '0',
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ereg_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout` (`ID_layout`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_settings`
--

DROP TABLE IF EXISTS `layout_settings`;
CREATE TABLE IF NOT EXISTS `layout_settings` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout_type` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_extended_type` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout_type` (`ID_layout_type`),
  KEY `ID_extended_type` (`ID_extended_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_settings_rel`
--

DROP TABLE IF EXISTS `layout_settings_rel`;
CREATE TABLE IF NOT EXISTS `layout_settings_rel` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_layout` int(11) NOT NULL,
  `ID_layout_settings` int(11) NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_layout` (`ID_layout`),
  KEY `ID_layout_settings` (`ID_layout_settings`),
  KEY `layout_settings` (`ID_layout`,`ID_layout_settings`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_type`
--

DROP TABLE IF EXISTS `layout_type`;
CREATE TABLE IF NOT EXISTS `layout_type` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `source` varchar(255) collate utf8_unicode_ci NOT NULL,
  `frequency` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `layout_type_plugin`
--

DROP TABLE IF EXISTS `layout_type_plugin`;
CREATE TABLE IF NOT EXISTS `layout_type_plugin` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_js` int(11) NOT NULL,
  `ID_layout_type` int(11) NOT NULL,
  `force_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_js` (`ID_js`),
  KEY `ID_layout_type` (`ID_layout_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `loc_comuni`
--

DROP TABLE IF EXISTS `loc_comuni`;
CREATE TABLE IF NOT EXISTS `loc_comuni` (
  `id` int(11) NOT NULL auto_increment,
  `nome` varchar(255) collate utf8_unicode_ci NOT NULL,
  `cap` varchar(5) collate utf8_unicode_ci NOT NULL,
  `siglaprovincia` char(2) collate utf8_unicode_ci NOT NULL,
  `isolaminore` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `siglaprovincia` (`siglaprovincia`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `loc_provincie`
--

DROP TABLE IF EXISTS `loc_provincie`;
CREATE TABLE IF NOT EXISTS `loc_provincie` (
  `id` int(11) NOT NULL auto_increment,
  `nome` varchar(255) collate utf8_unicode_ci NOT NULL,
  `sigla` char(2) collate utf8_unicode_ci NOT NULL,
  `idstato` int(11) NOT NULL default '0',
  `idregione` int(11) NOT NULL default '0',
  `zona` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idstato` (`idstato`),
  KEY `idregione` (`idregione`),
  KEY `zona` (`zona`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `loc_regioni`
--

DROP TABLE IF EXISTS `loc_regioni`;
CREATE TABLE IF NOT EXISTS `loc_regioni` (
  `id` int(11) NOT NULL auto_increment,
  `nome` varchar(25) collate utf8_unicode_ci default NULL,
  `zona` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `zona` (`zona`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `loc_stati`
--

DROP TABLE IF EXISTS `loc_stati`;
CREATE TABLE IF NOT EXISTS `loc_stati` (
  `id` int(11) NOT NULL auto_increment,
  `nome` varchar(255) collate utf8_unicode_ci NOT NULL,
  `zona` int(11) NOT NULL default '0',
  `vat_enable` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `zona` (`zona`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=1;

-- --------------------------------------------------------

--
-- Struttura della tabella `ecommerce_mpay_zone`
--

DROP TABLE IF EXISTS `ecommerce_mpay_zone`;
CREATE TABLE IF NOT EXISTS `ecommerce_mpay_zone` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_mpay` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `ID` int(11) NOT NULL auto_increment,
  `module_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `module_params` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  FULLTEXT KEY `module_name` (`module_name`,`module_params`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_calendar`
--

DROP TABLE IF EXISTS `module_calendar`;
CREATE TABLE IF NOT EXISTS `module_calendar` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `private_key` varchar(255) collate utf8_unicode_ci NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `show_title` char(1) collate utf8_unicode_ci NOT NULL,
  `show_navigation` char(1) collate utf8_unicode_ci NOT NULL,
  `show_date` char(1) collate utf8_unicode_ci NOT NULL,
  `show_print` char(1) collate utf8_unicode_ci NOT NULL,
  `show_tab` char(1) collate utf8_unicode_ci NOT NULL,
  `show_list_calendar` char(1) collate utf8_unicode_ci NOT NULL,
  `show_timezone` char(1) collate utf8_unicode_ci NOT NULL,
  `start_mode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `bgcolor` varchar(6) collate utf8_unicode_ci NOT NULL,
  `show_border` char(1) collate utf8_unicode_ci NOT NULL,
  `calendars` varchar(255) collate utf8_unicode_ci NOT NULL,
  `timezone` varchar(255) collate utf8_unicode_ci NOT NULL,
  `start_day` int(2) NOT NULL,
  `color` varchar(6) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_form`
--

DROP TABLE IF EXISTS `module_form`;
CREATE TABLE IF NOT EXISTS `module_form` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `order` varchar(255) collate utf8_unicode_ci NOT NULL,
  `force_redirect` varchar(255) collate utf8_unicode_ci NOT NULL,
  `fixed_pre_content` char(1) collate utf8_unicode_ci NOT NULL,
  `fixed_post_content` char(1) collate utf8_unicode_ci NOT NULL,
  `privacy` char(1) collate utf8_unicode_ci NOT NULL,
  `send_mail` char(1) collate utf8_unicode_ci NOT NULL,
  `ID_email` int(11) NOT NULL default '0',
  `report` char(1) collate utf8_unicode_ci NOT NULL,
  `require_note` char(1) collate utf8_unicode_ci NOT NULL,
  `tpl_form_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_report_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `limit_by_groups` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_email` (`ID_email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_form_fields`
--

DROP TABLE IF EXISTS `module_form_fields`;
CREATE TABLE IF NOT EXISTS `module_form_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module` int(11) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_extended_type` int(11) NOT NULL default '0',
  `ID_selection` int(11) NOT NULL default '0',
  `ID_form_fields_group` int(11) NOT NULL default '0',
  `require` char(1) collate utf8_unicode_ci NOT NULL,
  `ID_check_control` int(11) NOT NULL default '0',
  `unic_value` char(1) collate utf8_unicode_ci NOT NULL,
  `send_mail` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_mail` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_grid` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_menu` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_document` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_tip` char(1) collate utf8_unicode_ci NOT NULL,
  `writable` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_module` (`ID_module`),
  KEY `ID_extended_type` (`ID_extended_type`),
  KEY `ID_selection` (`ID_selection`),
  KEY `ID_form_fields_group` (`ID_form_fields_group`),
  KEY `ID_check_control` (`ID_check_control`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_form_fields_group`
--

DROP TABLE IF EXISTS `module_form_fields_group`;
CREATE TABLE IF NOT EXISTS `module_form_fields_group` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_form_fields_selection`
--

DROP TABLE IF EXISTS `module_form_fields_selection`;
CREATE TABLE IF NOT EXISTS `module_form_fields_selection` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_vgallery_type` int(11) NOT NULL,
  `ID_vgallery_fields` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_vgallery_type` (`ID_vgallery_type`),
  KEY `ID_vgallery_fields` (`ID_vgallery_fields`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_form_fields_selection_value`
--

DROP TABLE IF EXISTS `module_form_fields_selection_value`;
CREATE TABLE IF NOT EXISTS `module_form_fields_selection_value` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_selection` int(11) NOT NULL default '0',
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_selection` (`ID_selection`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_form_nodes`
--

DROP TABLE IF EXISTS `module_form_nodes`;
CREATE TABLE IF NOT EXISTS `module_form_nodes` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_form` int(11) NOT NULL default '0',
  `ip_visitor` varchar(255) collate utf8_unicode_ci NOT NULL,
  `uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_form_rel_nodes_fields`
--

DROP TABLE IF EXISTS `module_form_rel_nodes_fields`;
CREATE TABLE IF NOT EXISTS `module_form_rel_nodes_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_form_nodes` int(11) NOT NULL default '0',
  `ID_form_fields` int(11) NOT NULL default '0',
  `value` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_form_nodes` (`ID_form_nodes`),
  KEY `ID_form_fields` (`ID_form_fields`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_maps`
--

DROP TABLE IF EXISTS `module_maps`;
CREATE TABLE IF NOT EXISTS `module_maps` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `contest` varchar(255) collate utf8_unicode_ci NOT NULL,
  `relative_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `MapType` varchar(255) collate utf8_unicode_ci NOT NULL,
  `GLargeMapControl3D` char(1) collate utf8_unicode_ci NOT NULL,
  `GMapTypeControl` char(1) collate utf8_unicode_ci NOT NULL,
  `GScaleControl` char(1) collate utf8_unicode_ci NOT NULL,
  `GOverviewMapControl` char(1) collate utf8_unicode_ci NOT NULL,
  `enableGooglePhysical` char(1) collate utf8_unicode_ci NOT NULL,
  `enableGoogleEarth` char(1) collate utf8_unicode_ci NOT NULL,
  `enableGoogleBar` char(1) collate utf8_unicode_ci NOT NULL,
  `enableStreetView` char(1) collate utf8_unicode_ci NOT NULL,
  `streetView_width` int(11) NOT NULL,
  `streetView_height` int(11) NOT NULL,
  `enableStreetOverlay` char(1) collate utf8_unicode_ci NOT NULL,
  `enableStreetPhoto` char(1) collate utf8_unicode_ci NOT NULL,
  `layers` varchar(255) collate utf8_unicode_ci NOT NULL,
  `coords_lat` varchar(255) collate utf8_unicode_ci NOT NULL,
  `coords_lng` varchar(255) collate utf8_unicode_ci NOT NULL,
  `coords_zoom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `coords_title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `icon` varchar(255) collate utf8_unicode_ci NOT NULL,
  `icon_width` int(11) NOT NULL,
  `icon_height` int(11) NOT NULL,
  `shadow` varchar(255) collate utf8_unicode_ci NOT NULL,
  `shadow_width` int(11) NOT NULL,
  `shadow_height` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_maps_marker`
--

DROP TABLE IF EXISTS `module_maps_marker`;
CREATE TABLE IF NOT EXISTS `module_maps_marker` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module_maps` int(11) NOT NULL,
  `ID_node` int(11) NOT NULL,
  `ID_lang` int(11) NOT NULL,
  `coords_lat` varchar(255) collate utf8_unicode_ci NOT NULL,
  `coords_lng` varchar(255) collate utf8_unicode_ci NOT NULL,
  `coords_zoom` varchar(255) collate utf8_unicode_ci NOT NULL,
  `coords_title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_newsletter`
--

DROP TABLE IF EXISTS `module_newsletter`;
CREATE TABLE IF NOT EXISTS `module_newsletter` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `url_width` varchar(255) collate utf8_unicode_ci NOT NULL,
  `url_height` varchar(255) collate utf8_unicode_ci NOT NULL,
  `form` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_register`
--

DROP TABLE IF EXISTS `module_register`;
CREATE TABLE IF NOT EXISTS `module_register` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_user_menu` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_ecommerce_data` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_manage_account` char(1) collate utf8_unicode_ci NOT NULL,
  `public` char(1) collate utf8_unicode_ci NOT NULL,
  `force_redirect` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_privacy` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_require_note` char(1) collate utf8_unicode_ci NOT NULL,
  `activation` int(11) NOT NULL,
  `generate_password` char(1) collate utf8_unicode_ci NOT NULL,
  `primary_gid` int(11) NOT NULL,
  `default` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_register_fields`
--

DROP TABLE IF EXISTS `module_register_fields`;
CREATE TABLE IF NOT EXISTS `module_register_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module` int(11) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_extended_type` int(11) NOT NULL default '0',
  `ID_selection` int(11) NOT NULL default '0',
  `ID_form_fields_group` int(11) NOT NULL default '0',
  `require` char(1) collate utf8_unicode_ci NOT NULL,
  `ID_check_control` int(11) NOT NULL default '0',
  `unic_value` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_mail` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_grid` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_menu` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_in_document` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_tip` char(1) collate utf8_unicode_ci NOT NULL,
  `writable` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_module_register` (`ID_module`),
  KEY `ID_extended_type` (`ID_extended_type`),
  KEY `ID_selection` (`ID_selection`),
  KEY `ID_form_fields_group` (`ID_form_fields_group`),
  KEY `ID_check_control` (`ID_check_control`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_register_rel_form`
--

DROP TABLE IF EXISTS `module_register_rel_form`;
CREATE TABLE IF NOT EXISTS `module_register_rel_form` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module_register` int(11) NOT NULL,
  `ID_form` int(11) NOT NULL,
  `request` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `public` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_module_register` (`ID_module_register`),
  KEY `ID_form` (`ID_form`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_register_rel_gid`
--

DROP TABLE IF EXISTS `module_register_rel_gid`;
CREATE TABLE IF NOT EXISTS `module_register_rel_gid` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module_register` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `value` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_module_register` (`ID_module_register`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_register_rel_vgallery`
--

DROP TABLE IF EXISTS `module_register_rel_vgallery`;
CREATE TABLE IF NOT EXISTS `module_register_rel_vgallery` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module_register` int(11) NOT NULL,
  `ID_vgallery_nodes` int(11) NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `request` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_module_register` (`ID_module_register`),
  KEY `ID_form` (`ID_vgallery_nodes`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_search`
--

DROP TABLE IF EXISTS `module_search`;
CREATE TABLE IF NOT EXISTS `module_search` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `limit_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `area` varchar(255) collate utf8_unicode_ci NOT NULL,
  `contest` varchar(255) collate utf8_unicode_ci NOT NULL,
  `relative_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_search_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `hide_header` char(1) collate utf8_unicode_ci NOT NULL,
  `require_note` char(1) collate utf8_unicode_ci NOT NULL,
  `show_title` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_search_group`
--

DROP TABLE IF EXISTS `module_search_group`;
CREATE TABLE IF NOT EXISTS `module_search_group` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_search_vgallery`
--

DROP TABLE IF EXISTS `module_search_vgallery`;
CREATE TABLE IF NOT EXISTS `module_search_vgallery` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module` int(11) NOT NULL default '0',
  `ID_vgallery_type` int(11) NOT NULL,
  `ID_vgallery_fields` int(11) NOT NULL,
  `ID_extended_type` int(11) NOT NULL default '0',
  `ID_module_search_group` int(11) NOT NULL,
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_module` (`ID_module`),
  KEY `ID_vgallery_type` (`ID_vgallery_type`),
  KEY `ID_vgallery_fields` (`ID_vgallery_fields`),
  KEY `ID_extended_type` (`ID_extended_type`),
  KEY `ID_module_search_group` (`ID_module_search_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_share`
--

DROP TABLE IF EXISTS `module_share`;
CREATE TABLE IF NOT EXISTS `module_share` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `service_account` varchar(255) collate utf8_unicode_ci NOT NULL,
  `simple_share` text collate utf8_unicode_ci NOT NULL,
  `advanced_force_absolute` char(1) collate utf8_unicode_ci NOT NULL,
  `advanced_css` text collate utf8_unicode_ci NOT NULL,
  `advanced_html` text collate utf8_unicode_ci NOT NULL,
  `advanced_jsmain` text collate utf8_unicode_ci NOT NULL,
  `advanced_jsdep` text collate utf8_unicode_ci NOT NULL,
  `active` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_swf`
--

DROP TABLE IF EXISTS `module_swf`;
CREATE TABLE IF NOT EXISTS `module_swf` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `swf_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_xml` char(1) collate utf8_unicode_ci NOT NULL,
  `xml_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `xml_varname` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tbl_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  `items` varchar(255) collate utf8_unicode_ci NOT NULL,
  `show_sez_title` char(1) collate utf8_unicode_ci NOT NULL,
  `show_title` char(1) collate utf8_unicode_ci NOT NULL,
  `show_image` char(1) collate utf8_unicode_ci NOT NULL,
  `show_link` char(1) collate utf8_unicode_ci NOT NULL,
  `show_description` char(1) collate utf8_unicode_ci NOT NULL,
  `show_date` char(1) collate utf8_unicode_ci NOT NULL,
  `ID_publishing` int(11) NOT NULL default '0',
  `limit` int(11) NOT NULL default '0',
  `play` char(1) collate utf8_unicode_ci NOT NULL,
  `loop` char(1) collate utf8_unicode_ci NOT NULL,
  `menu` char(1) collate utf8_unicode_ci NOT NULL,
  `quality` varchar(255) collate utf8_unicode_ci NOT NULL,
  `scale` varchar(255) collate utf8_unicode_ci NOT NULL,
  `salign` varchar(255) collate utf8_unicode_ci NOT NULL,
  `wmode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `bgcolor` varchar(255) collate utf8_unicode_ci NOT NULL,
  `base` varchar(255) collate utf8_unicode_ci NOT NULL,
  `swliveconnect` char(1) collate utf8_unicode_ci NOT NULL,
  `flashvars` varchar(255) collate utf8_unicode_ci NOT NULL,
  `devicefont` varchar(255) collate utf8_unicode_ci NOT NULL,
  `allowscriptaccess` varchar(255) collate utf8_unicode_ci NOT NULL,
  `seamlesstabbing` varchar(255) collate utf8_unicode_ci NOT NULL,
  `allowfullscreen` char(1) collate utf8_unicode_ci NOT NULL,
  `allownetworking` char(1) collate utf8_unicode_ci NOT NULL,
  `align` varchar(255) collate utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  `version` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_title_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_main_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_parent_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_row_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_row_image_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_row_field_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_sub_parent_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_sub_row_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_sub_row_image_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tpl_sub_row_field_tag` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_publishing` (`ID_publishing`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_swf_criteria`
--

DROP TABLE IF EXISTS `module_swf_criteria`;
CREATE TABLE IF NOT EXISTS `module_swf_criteria` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module_swf` int(11) NOT NULL default '0',
  `src_fields` varchar(255) collate utf8_unicode_ci NOT NULL,
  `operator` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_module_swf` (`ID_module_swf`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_swf_vgallery`
--

DROP TABLE IF EXISTS `module_swf_vgallery`;
CREATE TABLE IF NOT EXISTS `module_swf_vgallery` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_module_swf` int(11) NOT NULL default '0',
  `ID_vgallery_fields` int(11) NOT NULL default '0',
  `value` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(4) NOT NULL default '0',
  `enable_lastlevel` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_thumb_label` char(1) collate utf8_unicode_ci NOT NULL,
  `thumb_limit` int(11) NOT NULL default '0',
  `enable_thumb_cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `display_view_mode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_sort` char(1) collate utf8_unicode_ci NOT NULL,
  `settings_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_thumb_empty` char(1) collate utf8_unicode_ci NOT NULL,
  `alt_field_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_module_swf` (`ID_module_swf`),
  KEY `ID_vgallery_fields` (`ID_vgallery_fields`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `notify_message`
--

DROP TABLE IF EXISTS `notify_message`;
CREATE TABLE IF NOT EXISTS `notify_message` (
  `ID` int(11) NOT NULL auto_increment,
  `area` varchar(255) collate utf8_unicode_ci NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `message` text collate utf8_unicode_ci NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `notify_schedule`
--

DROP TABLE IF EXISTS `notify_schedule`;
CREATE TABLE IF NOT EXISTS `notify_schedule` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `area` varchar(255) collate utf8_unicode_ci NOT NULL,
  `job` varchar(255) collate utf8_unicode_ci NOT NULL,
  `period` int(11) NOT NULL,
  `hour` time NOT NULL,
  `owner` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  `last_job` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `publishing`
--

DROP TABLE IF EXISTS `publishing`;
CREATE TABLE IF NOT EXISTS `publishing` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `parent` varchar(255) collate utf8_unicode_ci NOT NULL,
  `area` varchar(255) collate utf8_unicode_ci NOT NULL,
  `contest` varchar(255) collate utf8_unicode_ci NOT NULL,
  `relative_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `random` varchar(255) collate utf8_unicode_ci NOT NULL,
  `limit` int(11) NOT NULL default '0',
  `sort_default` int(11) NOT NULL,
  `sort_method` varchar(255) collate utf8_unicode_ci NOT NULL,
  `show_title` char(1) collate utf8_unicode_ci NOT NULL,
  `show_description` char(1) collate utf8_unicode_ci NOT NULL,
  `show_date` char(1) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `publishing_criteria`
--

DROP TABLE IF EXISTS `publishing_criteria`;
CREATE TABLE IF NOT EXISTS `publishing_criteria` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_publishing` int(11) NOT NULL default '0',
  `src_fields` varchar(255) collate utf8_unicode_ci NOT NULL,
  `operator` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_publishing` (`ID_publishing`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `publishing_fields`
--

DROP TABLE IF EXISTS `publishing_fields`;
CREATE TABLE IF NOT EXISTS `publishing_fields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_publishing` int(11) NOT NULL DEFAULT '0',
  `ID_fields` int(11) NOT NULL DEFAULT '0',
  `order_thumb` int(4) NOT NULL DEFAULT '0',
  `enable_lastlevel` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `enable_thumb_label` int(1) NOT NULL,
  `enable_thumb_empty` int(1) NOT NULL,
  `thumb_limit` int(11) NOT NULL DEFAULT '0',
  `parent_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enable_thumb_cascading` int(1) NOT NULL,
  `display_view_mode_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enable_sort` int(1) NOT NULL,
  `settings_type_thumb` int(11) NOT NULL,
  `ID_thumb_htmltag` int(11) NOT NULL,
  `custom_thumb_field` text COLLATE utf8_unicode_ci NOT NULL,
  `ID_label_thumb_htmltag` int(11) NOT NULL,
  `fixed_pre_content_thumb` text COLLATE utf8_unicode_ci NOT NULL,
  `fixed_post_content_thumb` text COLLATE utf8_unicode_ci NOT NULL,
  `field_fluid_thumb` int(1) NOT NULL,
  `field_grid_thumb` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `field_class_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label_fluid_thumb` int(1) NOT NULL,
  `label_grid_thumb` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `label_class_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `field_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_publishing` (`ID_publishing`),
  KEY `ID_vgallery_fields` (`ID_fields`),
  KEY `settings_type_thumb` (`settings_type_thumb`),
  KEY `ID_thumb_htmltag` (`ID_thumb_htmltag`),
  KEY `ID_label_thumb_htmltag` (`ID_label_thumb_htmltag`),
  KEY `field_hash` (`field_hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `rel_nodes`
--

DROP TABLE IF EXISTS `rel_nodes`;
CREATE TABLE IF NOT EXISTS `rel_nodes` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_node_src` int(11) NOT NULL default '0',
  `contest_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_node_dst` int(11) NOT NULL default '0',
  `contest_dst` varchar(255) collate utf8_unicode_ci NOT NULL,
  `date_begin` date NOT NULL default '0000-00-00',
  `date_end` date NOT NULL default '0000-00-00',
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `highlight` varchar(255) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `contest_src` (`contest_src`),
  KEY `ID_node_src` (`ID_node_src`),
  KEY `contest_dst` (`contest_dst`),
  KEY `ID_node_dst` (`ID_node_dst`),
  KEY `contest_node_src` (`contest_src`,`ID_node_src`),
  KEY `contest_node_dst` (`contest_dst`,`ID_node_dst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `ID` int(4) NOT NULL auto_increment,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `area` varchar(255) collate utf8_unicode_ci NOT NULL,
  `type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `criteria` text collate utf8_unicode_ci NOT NULL,
  `dependence` varchar(255) collate utf8_unicode_ci NOT NULL,
  `info` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_rel_path`
--

DROP TABLE IF EXISTS `settings_rel_path`;
CREATE TABLE IF NOT EXISTS `settings_rel_path` (
  `ID` int(11) NOT NULL auto_increment,
  `path` text collate utf8_unicode_ci NOT NULL,
  `uid` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `mod` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`),
  FULLTEXT KEY `path` (`path`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_rel_path_settings`
--

DROP TABLE IF EXISTS `settings_rel_path_settings`;
CREATE TABLE IF NOT EXISTS `settings_rel_path_settings` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_rel_path` int(11) NOT NULL default '0',
  `ID_settings` int(11) NOT NULL default '0',
  `value` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_rel_path` (`ID_rel_path`),
  KEY `ID_settings` (`ID_settings`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_thumb`
--

DROP TABLE IF EXISTS `settings_thumb`;
CREATE TABLE IF NOT EXISTS `settings_thumb` (
  `ID` int(11) NOT NULL auto_increment,
  `tbl_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  `items` varchar(255) collate utf8_unicode_ci NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `thumb_container_ID_mode` int(11) NOT NULL,
  `thumb_container` int(11) NOT NULL,
  `thumb_item` int(11) NOT NULL,
  `thumb_rec_per_page` int(11) NOT NULL,
  `thumb_image` int(11) NOT NULL,
  `thumb_image_detail` varchar(255) collate utf8_unicode_ci NOT NULL,
  `thumb_ID_image` int(11) NOT NULL,
  `thumb_display_view_mode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `preview_container_ID_mode` int(11) NOT NULL,
  `preview_container` int(11) NOT NULL,
  `preview_item` int(11) NOT NULL,
  `preview_image` int(11) NOT NULL,
  `preview_image_detail` varchar(255) collate utf8_unicode_ci NOT NULL,
  `preview_ID_image` int(11) NOT NULL,
  `preview_display_view_mode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  `max_upload` int(11) NOT NULL,
  `allowed_ext` varchar(255) collate utf8_unicode_ci NOT NULL,
  `max_items` int(11) NOT NULL,
  `allow_insert_dir` char(1) collate utf8_unicode_ci NOT NULL,
  `allow_insert_file` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `thumb_container_ID_mode` (`thumb_container_ID_mode`),
  KEY `thumb_ID_image` (`thumb_ID_image`),
  KEY `preview_container_ID_mode` (`preview_container_ID_mode`),
  KEY `preview_ID_image` (`preview_ID_image`),
  FULLTEXT KEY `tblsrc_items` (`tbl_src`,`items`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_thumb_image`
--

DROP TABLE IF EXISTS `settings_thumb_image`;
CREATE TABLE IF NOT EXISTS `settings_thumb_image` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `fix_x` int(11) NOT NULL,
  `fix_y` int(11) NOT NULL,
  `background` varchar(6) collate utf8_unicode_ci NOT NULL,
  `transparent` char(1) collate utf8_unicode_ci NOT NULL,
  `alpha` int(3) NOT NULL,
  `align` char(255) collate utf8_unicode_ci NOT NULL,
  `frame_size` int(11) NOT NULL,
  `frame_color` varchar(6) collate utf8_unicode_ci NOT NULL,
  `resize` char(1) collate utf8_unicode_ci NOT NULL,
  `mode` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_thumb_word_dir` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_thumb_word_file` char(1) collate utf8_unicode_ci NOT NULL,
  `word_color` varchar(6) collate utf8_unicode_ci NOT NULL,
  `word_size` int(11) NOT NULL,
  `word_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `word_align` varchar(255) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  `max_upload` int(11) NOT NULL,
  `force_icon` varchar(255) collate utf8_unicode_ci NOT NULL,
  `allowed_ext` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_thumb_mode`
--

DROP TABLE IF EXISTS `settings_thumb_mode`;
CREATE TABLE IF NOT EXISTS `settings_thumb_mode` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `operation` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `spesedispedizione`
--

DROP TABLE IF EXISTS `spesedispedizione`;
CREATE TABLE IF NOT EXISTS `spesedispedizione` (
  `id` int(11) NOT NULL auto_increment,
  `att` char(1) collate utf8_unicode_ci NOT NULL,
  `datainizio` date NOT NULL default '0000-00-00',
  `datafine` date NOT NULL default '0000-00-00',
  `zona` int(11) NOT NULL default '0',
  `pesominimo` double NOT NULL default '0',
  `pesomassimo` double NOT NULL default '0',
  `costounitario` double NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `static_pages`
--

DROP TABLE IF EXISTS `static_pages`;
CREATE TABLE IF NOT EXISTS `static_pages` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `parent` varchar(255) collate utf8_unicode_ci NOT NULL,
  `location` varchar(255) collate utf8_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL default '0',
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `static_pages_rel_languages`
--

DROP TABLE IF EXISTS `static_pages_rel_languages`;
CREATE TABLE IF NOT EXISTS `static_pages_rel_languages` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_static_pages` int(11) NOT NULL default '0',
  `ID_languages` int(11) NOT NULL default '0',
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `visible` char(1) collate utf8_unicode_ci NOT NULL,
  `alternative_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `smart_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `meta_title_alt` varchar(255) collate utf8_unicode_ci NOT NULL,
  `meta_title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `meta_description` varchar(255) collate utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_static` (`ID_static_pages`,`ID_languages`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `updater_exclude`
--

DROP TABLE IF EXISTS `updater_exclude`;
CREATE TABLE IF NOT EXISTS `updater_exclude` (
  `ID` int(11) NOT NULL auto_increment,
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `updater_externals`
--

DROP TABLE IF EXISTS `updater_externals`;
CREATE TABLE IF NOT EXISTS `updater_externals` (
  `ID` int(11) NOT NULL auto_increment,
  `domain` varchar(255) collate utf8_unicode_ci NOT NULL,
  `path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `status` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users_rel_module_form`
--

DROP TABLE IF EXISTS `users_rel_module_form`;
CREATE TABLE IF NOT EXISTS `users_rel_module_form` (
  `ID` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `ID_form` int(11) NOT NULL,
  `ID_form_node` int(11) NOT NULL,
  `request` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `public` char(1) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_form` (`ID_form`),
  KEY `ID_form_node` (`ID_form_node`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users_rel_vgallery`
--

DROP TABLE IF EXISTS `users_rel_vgallery`;
CREATE TABLE IF NOT EXISTS `users_rel_vgallery` (
  `ID` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `ID_nodes` int(11) NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `request` char(1) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_form` (`ID_vgallery_nodes`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery`
--

DROP TABLE IF EXISTS `vgallery`;
CREATE TABLE IF NOT EXISTS `vgallery` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL default '0',
  `limit_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `limit_level` int(11) NOT NULL,
  `insert_on_lastlevel` char(1) collate utf8_unicode_ci NOT NULL,
  `sort_method` varchar(255) collate utf8_unicode_ci NOT NULL,
  `enable_ecommerce` char(1) collate utf8_unicode_ci NOT NULL,
  `enable_ecommerce_all_level` char(1) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_fields`
--

DROP TABLE IF EXISTS `vgallery_fields`;
CREATE TABLE IF NOT EXISTS `vgallery_fields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ID_type` int(11) NOT NULL DEFAULT '0',
  `ID_group_thumb` int(11) NOT NULL,
  `ID_group_detail` int(11) NOT NULL,
  `ID_group_backoffice` int(11) NOT NULL,
  `order_thumb` int(4) NOT NULL DEFAULT '0',
  `order_detail` int(4) NOT NULL,
  `order_backoffice` int(4) NOT NULL,
  `ID_extended_type` int(11) NOT NULL DEFAULT '0',
  `selection_data_source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `selection_data_limit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `settings_type_thumb` int(11) NOT NULL,
  `settings_type_detail` int(11) NOT NULL,
  `ID_data_type` int(11) NOT NULL DEFAULT '0',
  `data_source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_limit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_sort` int(11) NOT NULL,
  `data_sort_method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enable_in_document` int(11) NOT NULL,
  `enable_thumb` int(1) NOT NULL,
  `enable_thumb_label` int(1) NOT NULL,
  `enable_thumb_empty` int(1) NOT NULL,
  `enable_lastlevel` int(1) NOT NULL,
  `enable_detail` int(1) NOT NULL,
  `enable_detail_label` int(1) NOT NULL,
  `enable_detail_empty` int(1) NOT NULL,
  `enable_sort` int(1) NOT NULL,
  `thumb_limit` int(1) NOT NULL,
  `enable_thumb_cascading` int(1) NOT NULL,
  `enable_detail_cascading` int(1) NOT NULL,
  `display_view_mode_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_view_mode_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enable_smart_url` int(11) NOT NULL,
  `enable_in_menu` int(11) NOT NULL,
  `meta_description` int(11) NOT NULL,
  `enable_in_grid` int(11) NOT NULL,
  `enable_in_mail` int(11) NOT NULL,
  `enable_in_cart` int(11) NOT NULL,
  `require` int(1) NOT NULL,
  `ID_check_control` int(11) NOT NULL,
  `limit_by_groups` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `limit_by_groups_frontend` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `limit_thumb_by_layouts` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `limit_detail_by_layouts` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `disable_multilang` int(1) NOT NULL,
  `ID_thumb_htmltag` int(11) NOT NULL,
  `ID_detail_htmltag` int(11) NOT NULL,
  `custom_thumb_field` text COLLATE utf8_unicode_ci NOT NULL,
  `custom_detail_field` text COLLATE utf8_unicode_ci NOT NULL,
  `schemaorg` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ID_label_thumb_htmltag` int(11) NOT NULL,
  `ID_label_detail_htmltag` int(11) NOT NULL,
  `fixed_pre_content_thumb` text COLLATE utf8_unicode_ci NOT NULL,
  `fixed_post_content_thumb` text COLLATE utf8_unicode_ci NOT NULL,
  `fixed_pre_content_detail` text COLLATE utf8_unicode_ci NOT NULL,
  `fixed_post_content_detail` text COLLATE utf8_unicode_ci NOT NULL,
  `field_fluid_thumb` int(1) NOT NULL,
  `field_grid_thumb` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `field_class_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label_fluid_thumb` int(1) NOT NULL,
  `label_grid_thumb` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `label_class_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `field_fluid_detail` int(1) NOT NULL,
  `field_grid_detail` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `field_class_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label_fluid_detail` int(1) NOT NULL,
  `label_grid_detail` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `label_class_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enable_tip` int(1) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_type` (`ID_type`),
  KEY `ID_extended_type` (`ID_extended_type`),
  KEY `ID_data_type` (`ID_data_type`),
  KEY `ID_check_control` (`ID_check_control`),
  KEY `ID_group_thumb` (`ID_group_thumb`),
  KEY `ID_group_detail` (`ID_group_detail`),
  KEY `ID_group_backoffice` (`ID_group_backoffice`),
  KEY `settings_type_thumb` (`settings_type_thumb`),
  KEY `settings_type_detail` (`settings_type_detail`),
  KEY `enable_thumb` (`enable_thumb`),
  KEY `enable_detail` (`enable_detail`),
  KEY `data_sort` (`data_sort`),
  KEY `limit_by_groups` (`limit_by_groups`),
  KEY `limit_by_groups_frontend` (`limit_by_groups_frontend`),
  KEY `limit_thumb_by_layouts` (`limit_thumb_by_layouts`),
  KEY `limit_detail_by_layouts` (`limit_detail_by_layouts`),
  KEY `ID_thumb_htmltag` (`ID_thumb_htmltag`),
  KEY `ID_detail_htmltag` (`ID_detail_htmltag`),
  KEY `ID_label_thumb_htmltag` (`ID_label_thumb_htmltag`),
  KEY `ID_label_detail_htmltag` (`ID_label_detail_htmltag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_fields_data_type`
--

DROP TABLE IF EXISTS `vgallery_fields_data_type`;
CREATE TABLE IF NOT EXISTS `vgallery_fields_data_type` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_fields_selection`
--

DROP TABLE IF EXISTS `vgallery_fields_selection`;
CREATE TABLE IF NOT EXISTS `vgallery_fields_selection` (
  `ID` int(11) NOT NULL auto_increment,
  `value` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_fields_selection_type`
--

DROP TABLE IF EXISTS `vgallery_fields_selection_value`;
CREATE TABLE IF NOT EXISTS `vgallery_fields_selection_value` (
    `ID` int(11) NOT NULL auto_increment,
    `name` varchar(255) collate utf8_unicode_ci NOT NULL,
    `ID_selection` int(11) NOT NULL,
    `order` int(11) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_groups`
--

DROP TABLE IF EXISTS `vgallery_groups`;
CREATE TABLE IF NOT EXISTS `vgallery_groups` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_menu` int(11) NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `meta_title_alt` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_groups_menu`
--

DROP TABLE IF EXISTS `vgallery_groups_menu`;
CREATE TABLE IF NOT EXISTS `vgallery_groups_menu` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_groups_fields`
--

DROP TABLE IF EXISTS `vgallery_groups_fields`;
CREATE TABLE IF NOT EXISTS `vgallery_groups_fields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_group` int(11) NOT NULL DEFAULT '0',
  `ID_fields` int(11) NOT NULL DEFAULT '0',
  `order_detail` int(4) NOT NULL DEFAULT '0',
  `enable_detail_label` int(1) NOT NULL,
  `enable_detail_empty` int(1) NOT NULL,
  `parent_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enable_detail_cascading` int(1) NOT NULL,
  `display_view_mode_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `settings_type_detail` int(11) NOT NULL,
  `ID_detail_htmltag` int(11) NOT NULL,
  `custom_detail_field` text COLLATE utf8_unicode_ci NOT NULL,
  `ID_label_detail_htmltag` int(11) NOT NULL,
  `fixed_pre_content_detail` text COLLATE utf8_unicode_ci NOT NULL,
  `fixed_post_content_detail` text COLLATE utf8_unicode_ci NOT NULL,
  `field_fluid_detail` int(1) NOT NULL,
  `field_grid_detail` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `field_class_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label_fluid_detail` int(1) NOT NULL,
  `label_grid_detail` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `label_class_detail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `field_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_group` (`ID_group`),
  KEY `ID_fields` (`ID_fields`),
  KEY `ID_detail_htmltag` (`ID_detail_htmltag`),
  KEY `ID_label_detail_htmltag` (`ID_label_detail_htmltag`),
  KEY `settings_type_detail` (`settings_type_detail`),
  KEY `field_hash` (`field_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_nodes`
--

DROP TABLE IF EXISTS `vgallery_nodes`;
CREATE TABLE IF NOT EXISTS `vgallery_nodes` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_vgallery` int(11) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `parent` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ID_type` int(11) NOT NULL default '0',
  `is_dir` char(1) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL default '0',
  `owner` int(11) NOT NULL,
  `alternative_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID_vgallery` (`ID_vgallery`),
  KEY `ID_type` (`ID_type`),
  KEY `vgallery_type` (`ID_vgallery`,`ID_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_nodes_rel_groups`
--

DROP TABLE IF EXISTS `vgallery_nodes_rel_groups`;
CREATE TABLE IF NOT EXISTS `vgallery_nodes_rel_groups` (
  `ID_vgallery_nodes` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `mod` char(1) collate utf8_unicode_ci NOT NULL,
  KEY `gid` (`gid`),
  KEY `ID_vgallery_nodes` (`ID_vgallery_nodes`),
  KEY `vgallery_gid` (`ID_vgallery_nodes`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_rel`
--

DROP TABLE IF EXISTS `vgallery_rel`;
CREATE TABLE IF NOT EXISTS `vgallery_rel` (
  `ID` int(11) NOT NULL auto_increment,
  `ID_node_src` int(11) NOT NULL,
  `ID_vgallery` int(11) NOT NULL,
  `cascading` char(1) collate utf8_unicode_ci NOT NULL,
  `contest_src` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_rel_nodes_fields`
--

DROP TABLE IF EXISTS `vgallery_rel_nodes_fields`;
CREATE TABLE IF NOT EXISTS `vgallery_rel_nodes_fields` (
  `ID` int(11) NOT NULL auto_increment,
  `description` text collate utf8_unicode_ci NOT NULL,
  `ID_fields` int(11) NOT NULL default '0',
  `ID_nodes` int(11) NOT NULL default '0',
  `ID_lang` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `ID_fields` (`ID_fields`),
  KEY `ID_nodes` (`ID_nodes`),
  KEY `ID_lang` (`ID_lang`),
  KEY `fields_nodes_lang` (`ID_fields`,`ID_nodes`,`ID_lang`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `vgallery_type`
--

DROP TABLE IF EXISTS `vgallery_type`;
CREATE TABLE IF NOT EXISTS `vgallery_type` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `sort_default` int(11) NOT NULL,
  `is_dir_default` char(1) collate utf8_unicode_ci NOT NULL,
  `last_update` int(10) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
