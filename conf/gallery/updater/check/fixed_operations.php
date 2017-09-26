<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
    $operation_fixed = array(); 
    $operation_fixed["2009-10-25"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_static' AND COLUMN_NAME <> 'ID_static_pages' AND TABLE_NAME = 'static_pages_rel_languages' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2009-10-25"]["than"] = "ALTER TABLE `static_pages_rel_languages` CHANGE `ID_static` `ID_static_pages` INT( 11 ) NOT NULL DEFAULT '0'";
    $operation_fixed["2009-10-26"]["if"] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_rel_settings' AND Table_Name <> 'layout_settings_rel' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2009-10-26"]["than"] = "RENAME TABLE `layout_rel_settings`  TO `layout_settings_rel`";
//   $operation_fixed["2010-08-25"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'is_dir' AND TABLE_NAME = 'vgallery_nodes' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
//   $operation_fixed["2010-08-25"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `vgallery_nodes` WHERE is_dir = '') AS val"; 
//   $operation_fixed["2010-08-25"]["than"] = "UPDATE `vgallery_nodes` SET is_dir = IF(vgallery_nodes.ID_type = (SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.name = 'Directory'), 1, 0) WHERE is_dir = ''";
  //  $operation_fixed["2010-10-27"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_settings_rel' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
 //   $operation_fixed["2010-10-27"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_settings_rel` WHERE value = 'toolbar.admin') AS val"; 
//    $operation_fixed["2010-10-27"]["than"] = "UPDATE `layout_settings_rel` SET value = 'toolbaradmin' WHERE value = 'toolbar.admin'";
    
    //$operation_fixed["2011-01-21"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'data_blocks' AND TABLE_NAME = 'cache_page' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    //$operation_fixed["2011-01-21"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `cache_page` WHERE data_blocks = '' AND ff_blocks = '' AND user_path <> '/' AND force_visualization = '') AS val"; 
    //$operation_fixed["2011-01-21"]["than"][] = "DELETE FROM `cache_page` WHERE `cache_page`.`data_blocks` = '' AND user_path <> '/' AND force_visualization = ''";
    //$operation_fixed["2011-01-21"]["than"][] = "TRUNCATE TABLE `cache_sid`";

//    $operation_fixed["2011-03-08"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'enable_multilang_visible' AND TABLE_NAME = 'vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
//    $operation_fixed["2011-03-08"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `vgallery` WHERE enable_multilang_visible = '') AS val"; 
//    $operation_fixed["2011-03-08"]["than"][] = "UPDATE `vgallery` SET enable_multilang_visible = '1' WHERE enable_multilang_visible = ''";
    
//    $operation_fixed["2011-04-26"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_mode' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
//    $operation_fixed["2011-04-26"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'public' AND TABLE_NAME = 'settings_thumb_mode' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 0, 1 ) AS val"; 
//    $operation_fixed["2011-04-26"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `settings_thumb_mode` WHERE 1) AS val"; 
//    $operation_fixed["2011-04-26"]["than"][] = "TRUNCATE TABLE `settings_thumb_mode`"; 

    $operation_fixed["2011-07-16"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_zone' AND TABLE_NAME = 'ecommerce_shipping_price' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2011-07-16"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `ecommerce_shipping_price` WHERE ID_zone = 0) AS val"; 
    $operation_fixed["2011-07-16"]["than"] = "UPDATE `ecommerce_shipping_price` SET ID_zone = 1 WHERE ID_zone = 0";

    $operation_fixed["2011-11-17"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'visible' AND TABLE_NAME = 'vgallery_nodes' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2011-11-17"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `vgallery_nodes` WHERE visible NOT IN('0', '1')) AS val"; 
    $operation_fixed["2011-11-17"]["than"][] = "UPDATE `vgallery_nodes` SET visible = (SELECT IF(vgallery_rel_nodes_fields.description = '', '0', vgallery_rel_nodes_fields.description) FROM vgallery_rel_nodes_fields WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID AND vgallery_rel_nodes_fields.ID_fields = (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.name = 'visible' AND vgallery_fields.ID_type = (SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.name = 'System')) AND vgallery_rel_nodes_fields.ID_lang = (SELECT ff_languages.ID FROM ff_languages WHERE ff_languages.code = 'ITA') LIMIT 1) WHERE visible = ''";
    $operation_fixed["2011-11-17"]["than"][] = "UPDATE `vgallery_nodes` SET visible = '1' WHERE visible NOT IN('0', '1')";
    
    $operation_fixed["2012-02-15"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'value' AND TABLE_NAME = 'cache_sid' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";     
    $operation_fixed["2012-02-15"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `cache_sid` WHERE SUBSTRING(cache_sid.value, 1, 1) <> '{' ) AS val";
    $operation_fixed["2012-02-15"]["than"][] = "TRUNCATE TABLE `cache_sid`";
    $operation_fixed["2012-02-15"]["than"][] = "TRUNCATE TABLE `cache_page`";
    
    $operation_fixed["2012-11-15"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
    $operation_fixed["2012-11-15"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout` WHERE layout.ID NOT IN ( SELECT compare_layout_path.ID_layout FROM layout_path AS compare_layout_path) ) AS val";
    $operation_fixed["2012-11-15"]["than"][] = "INSERT INTO layout_path ( ID, `ID_layout`, `path`, `ereg_path`, `visible`, `cascading` ) SELECT null, layout.ID , '/', '', '1', '1' FROM  `layout` WHERE ID NOT IN ( SELECT compare_layout_path.ID_layout FROM layout_path AS compare_layout_path )";
    
    $operation_fixed["2013-10-13"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'vgallery_nodes' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
    $operation_fixed["2013-10-13"]["if"][] = "SELECT (SELECT IF(COUNT(*), 1, 0 ) AS val FROM `vgallery_nodes` WHERE name = '' AND parent = '/' AND vgallery_nodes.ID > 0) AS val"; 
    $operation_fixed["2013-10-13"]["than"][] = "DELETE FROM `vgallery_nodes` WHERE name = '' AND parent = '/'";
    $operation_fixed["2013-10-13"]["than"][] = "INSERT INTO `vgallery_nodes` (ID, is_dir, parent, visible) VALUES (null, '1', '/', '1')";
    $operation_fixed["2013-10-13"]["than"][] = "UPDATE `vgallery_nodes` SET ID = 0 WHERE name = '' AND parent = '/'";

    $operation_fixed["2013-10-17"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_location' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
    $operation_fixed["2013-10-17"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_location` WHERE layout_location.ID NOT IN ( SELECT compare_layout_location_path.ID_layout_location FROM layout_location_path AS compare_layout_location_path) ) AS val";
    $operation_fixed["2013-10-17"]["than"][] = "INSERT INTO layout_location_path ( ID, `ID_layout_location`, `path`, `visible`, `cascading` ) SELECT null, layout_location.ID , '%', '1', '1' FROM  `layout_location` WHERE ID NOT IN ( SELECT compare_layout_location_path.ID_layout_location FROM layout_location_path AS compare_layout_location_path )";
    
    $operation_fixed["2013-10-23"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'uid' AND TABLE_NAME = 'vgallery_rel_nodes_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2013-10-23"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `vgallery_rel_nodes_fields` WHERE vgallery_rel_nodes_fields.`description` LIKE '%<input class=\"toolbar\" type=\"hidden\"%' AND vgallery_rel_nodes_fields.`uid` = 0) AS val";
    $operation_fixed["2013-10-23"]["than"][] = "UPDATE vgallery_rel_nodes_fields SET `nodes` = '', `description` = '' WHERE vgallery_rel_nodes_fields.`description` LIKE '%<input class=\"toolbar\" type=\"hidden\"%' AND vgallery_rel_nodes_fields.`uid` = 0";

    $operation_fixed["2013-10-25"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_location_path' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
    $operation_fixed["2013-10-25"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_location_path` WHERE layout_location_path.path LIKE '%(.*)%' OR layout_location_path.path LIKE '%(.+)%' ) AS val";
    $operation_fixed["2013-10-25"]["than"][] = "UPDATE `layout_location_path` SET `path` = REPLACE(REPLACE(layout_location_path.path, '(.+)', '%'), '(.*)', '%') WHERE layout_location_path.path LIKE '%(.*)%' OR layout_location_path.path LIKE '%(.+)%'";

    $operation_fixed["2013-10-26"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_path' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
    $operation_fixed["2013-10-26"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_path` WHERE layout_path.ereg_path LIKE '%(.*)%' OR layout_path.ereg_path LIKE '%(.+)%' ) AS val";
    $operation_fixed["2013-10-26"]["than"][] = "UPDATE `layout_path` SET `ereg_path` = REPLACE(REPLACE(layout_path.ereg_path, '(.+)', '%'), '(.*)', '%') WHERE layout_path.ereg_path LIKE '%(.*)%' OR layout_path.ereg_path LIKE '%(.+)%'";
     
    //$operation_fixed["2013-09-22"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_template' AND TABLE_NAME = 'layout_location' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";     
    //$operation_fixed["2013-09-22"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_location` WHERE layout_location.ID_template = 0) AS val";
    //$operation_fixed["2013-09-22"]["than"][] = " UPDATE `layout_location` SET `layout_location`.`ID_template` = 1 WHERE `layout_location`.`ID_template` = 0 "; 
	
	
// INIZIO FIX UPDATER GIORGIO 
	$operation_fixed["2014-01-21"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21"]["than"][] = "DELETE 
													FROM cm_showfiles_modes
													WHERE cm_showfiles_modes.name IN	(SELECT settings_thumb_image.name
																							FROM settings_thumb_image
																						)";
	$operation_fixed["2014-01-21"]["than"][] = "INSERT INTO cm_showfiles_modes 
													( ID,`alignment`, `alpha`, `bgcolor`, `dim_x`, `dim_y`, `mode`, `name`, `format`, `format_jpg_quality`, `wmk_alignment`, `wmk_image`, `transparent`, `frame_size`, `frame_color`, `resize`, `enable_thumb_word_dir`, `enable_thumb_word_file`, `word_color`, `word_size`, `word_type`, `word_align`, `last_update`, `max_upload`, `force_icon`, `allowed_ext`, `enable_thumb_image_dir`, `enable_thumb_image_file`, `wmk_alpha`, `wmk_mode`) 
													SELECT null, align , alpha, background, fix_x, fix_y, mode, name, extension, jpg_compress, image_align, image_cover, transparent, `frame_size`, `frame_color`, `resize`, `enable_thumb_word_dir`, `enable_thumb_word_file`, `word_color`, `word_size`, `word_type`, `word_align`, `last_update`, `max_upload`, `force_icon`, `allowed_ext`, `enable_thumb_image_dir`, `enable_thumb_image_file`, `image_alpha`, `image_mode` 
													FROM  `settings_thumb_image` 
													WHERE 1";
	
	$operation_fixed["2014-01-21-vfd"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vfd"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vfd"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type_detail' AND Table_Name = 'vgallery_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vfd"]["than"] = "UPDATE vgallery_fields 
													SET vgallery_fields.settings_type_detail =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = vgallery_fields.settings_type_detail
																								LIMIT 1	
																						)
													WHERE vgallery_fields.settings_type_detail > 0";

	$operation_fixed["2014-01-21-vf-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vf-o"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vf-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type' AND Table_Name = 'vgallery_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vf-o"]["than"] = "UPDATE vgallery_fields 
													SET vgallery_fields.settings_type =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = vgallery_fields.settings_type
																								LIMIT 1
																						)
													WHERE vgallery_fields.settings_type > 0";

	$operation_fixed["2014-01-21-vf-n"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vf-n"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vf-n"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type_thumb' AND Table_Name = 'vgallery_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vf-n"]["than"] = "UPDATE vgallery_fields 
													SET vgallery_fields.settings_type_thumb =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = vgallery_fields.settings_type_thumb
																								LIMIT 1
																						)
													WHERE vgallery_fields.settings_type_thumb > 0";
	
	$operation_fixed["2014-01-21-vgf-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vgf-o"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vgf-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type_detail' AND Table_Name = 'vgallery_groups_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-vgf-o"]["than"] = "UPDATE vgallery_groups_fields 
													SET vgallery_groups_fields.settings_type_detail =	( SELECT cm_showfiles_modes.ID
																										FROM cm_showfiles_modes
																										INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																										WHERE settings_thumb_image.ID = vgallery_groups_fields.settings_type_detail
																										LIMIT 1
																									)
													WHERE vgallery_groups_fields.settings_type_detail > 0";

	$operation_fixed["2014-01-21-msf-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-msf-o"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-msf-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type' AND Table_Name = 'module_swf_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-msf-o"]["than"] = "UPDATE module_swf_vgallery 
													SET module_swf_vgallery.settings_type =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = module_swf_vgallery.settings_type
																								LIMIT 1
																							)	
													WHERE module_swf_vgallery.settings_type > 0";

	$operation_fixed["2014-01-21-msf-n"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-msf-n"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-msf-n"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type_thumb' AND Table_Name = 'module_swf_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-msf-n"]["than"] = "UPDATE module_swf_vgallery 
													SET module_swf_vgallery.settings_type_thumb =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = module_swf_vgallery.settings_type_thumb
																								LIMIT 1
																							)	
													WHERE module_swf_vgallery.settings_type_thumb > 0";
	
	
	$operation_fixed["2014-01-21-pv-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pv-o"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pv-o"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type' AND Table_Name = 'publishing_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pv-o"]["than"] = "UPDATE publishing_vgallery 
													SET publishing_vgallery.settings_type =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = publishing_vgallery.settings_type
																								LIMIT 1	
																						)
													WHERE publishing_vgallery.settings_type > 0";

	$operation_fixed["2014-01-21-pv-n"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pv-n"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pv-n"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type_thumb' AND Table_Name = 'publishing_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pv-n"]["than"] = "UPDATE publishing_vgallery 
													SET publishing_vgallery.settings_type_thumb =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = publishing_vgallery.settings_type_thumb
																								LIMIT 1	
																						)
													WHERE publishing_vgallery.settings_type_thumb > 0";

	$operation_fixed["2014-01-21-pf"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pf"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pf"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type_thumb' AND Table_Name = 'publishing_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-pf"]["than"] = "UPDATE publishing_fields 
													SET publishing_fields.settings_type_thumb =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = publishing_fields.settings_type_thumb
																								LIMIT 1	
																						)
													WHERE publishing_fields.settings_type_thumb > 0";
													
	$operation_fixed["2014-01-21-x"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'settings_thumb_image' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-x"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE Table_Name = 'cm_showfiles_modes' AND COLUMN_NAME IN ('transparent', 'resize', 'enable_thumb_word_dir', 'enable_thumb_word_file', 'word_color', 'word_size' , 'word_align', 'last_update', 'max_upload', 'force_icon', 'allowed_ext', 'enable_thumb_image_dir', 'enable_thumb_image_file', 'wmk_alpha', 'wmk_mode') AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";
	$operation_fixed["2014-01-21-x"]["than"][] = "UPDATE settings_thumb 
													SET settings_thumb.thumb_ID_image =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = settings_thumb.thumb_ID_image
																								LIMIT 1
																						)
													WHERE settings_thumb.thumb_ID_image > 0";
	$operation_fixed["2014-01-21-x"]["than"][] = "UPDATE settings_thumb 
													SET settings_thumb.preview_ID_image =	( SELECT cm_showfiles_modes.ID
																								FROM cm_showfiles_modes
																								INNER JOIN settings_thumb_image ON settings_thumb_image.name = cm_showfiles_modes.name
																								WHERE settings_thumb_image.ID = settings_thumb.preview_ID_image
																								LIMIT 1
																							)
													WHERE settings_thumb.preview_ID_image > 0";	
	$operation_fixed["2014-01-21-x"]["than"][] = "DROP TABLE settings_thumb_image";

    $operation_fixed["2014-09-22"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'ff_international' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-09-22"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val  FROM (SELECT  COUNT( * ) AS count_code FROM  `ff_international` WHERE 1 GROUP BY  `word_code` ,  `ID_lang` HAVING count_code > 1) AS tbl_src) AS val"; 
    $operation_fixed["2014-09-22"]["than"][] = "DELETE FROM `ff_international` WHERE  `ff_international`.ID NOT IN
    											(
													SELECT ID FROM (
														SELECT  ID, COUNT( * ) AS count_code
														FROM (
															SELECT `ff_international`.* 
															FROM  `ff_international` 
															WHERE 1 
															ORDER BY IF(description = '', 1, 0), ID desc
														) AS internal_tbl_src
														GROUP BY  `word_code` , `ID_lang` 
														HAVING count_code > 1
													) AS tbl_src
												)
												AND `ff_international`.ID IN
												(
													SELECT ID 
													FROM (
														SELECT ID
														FROM  `ff_international` 
														INNER JOIN 
														(
															SELECT GROUP_CONCAT( ID ) AS mID
															FROM  `ff_international` 
															WHERE 1 
															GROUP BY  `word_code` , `ID_lang` 
															HAVING COUNT( * ) > 1
														) AS internal_tbl_src ON FIND_IN_SET( `ff_international`.ID, internal_tbl_src.mID ) 
													) AS tbl_src
												)";
    $operation_fixed["2014-10-08"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'alt_path' AND COLUMN_NAME <> 'alt_url' AND TABLE_NAME = 'files_rel_languages' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-10-08"]["than"] = "ALTER TABLE `files_rel_languages` CHANGE `alt_path` `alt_url` VARCHAR( 255 ) NOT NULL";
												
    $operation_fixed["2014-10-09"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'alternative_path' AND COLUMN_NAME <> 'alt_url' AND TABLE_NAME = 'static_pages_rel_languages' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-10-09"]["than"] = "ALTER TABLE `static_pages_rel_languages` CHANGE `alternative_path` `alt_url` VARCHAR( 255 ) NOT NULL";

    
    $operation_fixed["2014-12-01"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'value' AND COLUMN_NAME <> 'description' AND TABLE_NAME = 'anagraph_rel_nodes_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-01"]["than"] = "ALTER TABLE `anagraph_rel_nodes_fields` CHANGE `value` `description` TEXT NOT NULL";

    $operation_fixed["2014-12-02"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_anagraph' AND COLUMN_NAME <> 'ID_nodes' AND TABLE_NAME = 'anagraph_rel_nodes_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-02"]["than"] = "ALTER TABLE `anagraph_rel_nodes_fields` CHANGE `ID_anagraph` `ID_nodes` INT( 11 ) NOT NULL DEFAULT '0'";
	
    $operation_fixed["2014-12-08"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'anagraph_fields_group' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-08"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'anagraph_type_group' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-08"]["than"] = "DROP TABLE `anagraph_type_group`";

    $operation_fixed["2014-12-09"]["if"] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'anagraph_fields_group' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-09"]["than"] = "RENAME TABLE `anagraph_fields_group`  TO `anagraph_type_group`";
    
    $operation_fixed["2014-12-10"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'parent' AND COLUMN_NAME <> 'parent_detail' AND TABLE_NAME = 'anagraph_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-10"]["than"] = "ALTER TABLE `anagraph_fields` CHANGE `parent` `parent_detail` VARCHAR( 255 ) NOT NULL";

    $operation_fixed["2014-12-11"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type' AND COLUMN_NAME <> 'settings_type_thumb' AND TABLE_NAME = 'anagraph_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-11"]["than"] = "ALTER TABLE `anagraph_fields` CHANGE `settings_type` `settings_type_thumb` VARCHAR( 255 ) NOT NULL";

    $operation_fixed["2014-12-12"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'display_view_mode' AND COLUMN_NAME <> 'display_view_mode_thumb' AND TABLE_NAME = 'anagraph_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-12"]["than"] = "ALTER TABLE `anagraph_fields` CHANGE `display_view_mode` `display_view_mode_thumb` VARCHAR( 255 ) NOT NULL";

    $operation_fixed["2014-12-13"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'order' AND COLUMN_NAME <> 'order_thumb' AND TABLE_NAME = 'anagraph_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-13"]["than"] = "ALTER TABLE `anagraph_fields` CHANGE `order` `order_thumb` INT( 4 ) NOT NULL DEFAULT '0'";
    
    $operation_fixed["2014-12-14"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'parent' AND COLUMN_NAME <> 'parent_detail' AND TABLE_NAME = 'vgallery_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-14"]["than"] = "ALTER TABLE `vgallery_fields` CHANGE `parent` `parent_detail` VARCHAR( 255 ) NOT NULL";

    $operation_fixed["2014-12-15"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type' AND COLUMN_NAME <> 'settings_type_thumb' AND TABLE_NAME = 'vgallery_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-15"]["than"] = "ALTER TABLE `vgallery_fields` CHANGE `settings_type` `settings_type_thumb` VARCHAR( 255 ) NOT NULL";

    $operation_fixed["2014-12-16"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'display_view_mode' AND COLUMN_NAME <> 'display_view_mode_thumb' AND TABLE_NAME = 'vgallery_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-16"]["than"] = "ALTER TABLE `vgallery_fields` CHANGE `display_view_mode` `display_view_mode_thumb` VARCHAR( 255 ) NOT NULL";
 
    $operation_fixed["2014-12-17"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'settings_type' AND COLUMN_NAME <> 'settings_type_thumb' AND TABLE_NAME = 'publishing_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-17"]["than"] = "ALTER TABLE `publishing_vgallery` CHANGE `settings_type` `settings_type_thumb` VARCHAR( 255 ) NOT NULL";

    $operation_fixed["2014-12-18"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'display_view_mode' AND COLUMN_NAME <> 'display_view_mode_thumb' AND TABLE_NAME = 'publishing_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-18"]["than"] = "ALTER TABLE `publishing_vgallery` CHANGE `display_view_mode` `display_view_mode_thumb` VARCHAR( 255 ) NOT NULL";

    $operation_fixed["2014-12-19"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'order' AND COLUMN_NAME <> 'order_thumb' AND TABLE_NAME = 'vgallery_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2014-12-19"]["than"] = "ALTER TABLE `vgallery_fields` CHANGE `order` `order_thumb` INT( 4 ) NOT NULL DEFAULT '0'";

    $operation_fixed["2015-01-09"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'wrap' AND COLUMN_NAME <> 'thumb_wrap' AND TABLE_NAME = 'settings_thumb' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-09"]["than"] = "ALTER TABLE `settings_thumb` CHANGE `wrap` `thumb_wrap` INT( 1 ) NOT NULL DEFAULT '0'";

    $operation_fixed["2015-01-10"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'default_grid' AND COLUMN_NAME <> 'thumb_grid' AND TABLE_NAME = 'settings_thumb' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-10"]["than"][] = "ALTER TABLE `settings_thumb` CHANGE `default_grid` `thumb_grid` VARCHAR( 12 ) NOT NULL";
    $operation_fixed["2015-01-10"]["than"][] = "UPDATE `settings_thumb` SET `thumb_grid` = CONCAT(grid_xs, ',', grid_sm, ',', grid_md, ',', thumb_grid) WHERE 1";
    $operation_fixed["2015-01-10"]["than"][] = "ALTER TABLE `settings_thumb` DROP `grid_md`";
    $operation_fixed["2015-01-10"]["than"][] = "ALTER TABLE `settings_thumb` DROP `grid_sm`";
    $operation_fixed["2015-01-10"]["than"][] = "ALTER TABLE `settings_thumb` DROP `grid_xs`";
                                 
    $operation_fixed["2015-01-11"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'preview_large' AND COLUMN_NAME <> 'preview_grid' AND TABLE_NAME = 'settings_thumb' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-11"]["than"][] = "ALTER TABLE `settings_thumb` CHANGE `preview_large` `preview_grid` VARCHAR( 12 ) NOT NULL";
    $operation_fixed["2015-01-11"]["than"][] = "UPDATE `settings_thumb` SET `preview_grid` = CONCAT(preview_xs, ',', preview_small, ',', preview_medium, ',', preview_grid) WHERE 1";
    $operation_fixed["2015-01-11"]["than"][] = "ALTER TABLE `settings_thumb` DROP `preview_medium`";
    $operation_fixed["2015-01-11"]["than"][] = "ALTER TABLE `settings_thumb` DROP `preview_small`";
    $operation_fixed["2015-01-11"]["than"][] = "ALTER TABLE `settings_thumb` DROP `preview_xs`";

    $operation_fixed["2015-01-12"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'elem_grid_md' AND TABLE_NAME = 'settings_thumb' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-12"]["than"][] = "ALTER TABLE `settings_thumb` CHANGE `thumb_item` `thumb_item` VARCHAR( 12 ) NOT NULL";
    $operation_fixed["2015-01-12"]["than"][] = "UPDATE `settings_thumb` SET `thumb_item` = CONCAT(elem_grid_xs, ',', elem_grid_sm, ',', elem_grid_md, ',', thumb_item) WHERE 1";
    $operation_fixed["2015-01-12"]["than"][] = "ALTER TABLE `settings_thumb` DROP `elem_grid_md`";
    $operation_fixed["2015-01-12"]["than"][] = "ALTER TABLE `settings_thumb` DROP `elem_grid_sm`";
    $operation_fixed["2015-01-12"]["than"][] = "ALTER TABLE `settings_thumb` DROP `elem_grid_xs`";
    
    $operation_fixed["2015-01-13"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'thumb_item_md' AND TABLE_NAME = 'settings_thumb' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-13"]["than"][] = "ALTER TABLE `settings_thumb` CHANGE `thumb_item` `thumb_item` VARCHAR( 12 ) NOT NULL";
    $operation_fixed["2015-01-13"]["than"][] = "UPDATE `settings_thumb` SET `thumb_item` = CONCAT(thumb_item_xs, ',', thumb_item_sm, ',', thumb_item_md, ',', thumb_item) WHERE 1";
    $operation_fixed["2015-01-13"]["than"][] = "ALTER TABLE `settings_thumb` DROP `thumb_item_md`";
    $operation_fixed["2015-01-13"]["than"][] = "ALTER TABLE `settings_thumb` DROP `thumb_item_sm`";
    $operation_fixed["2015-01-13"]["than"][] = "ALTER TABLE `settings_thumb` DROP `thumb_item_xs`";
    
    $operation_fixed["2015-01-21"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'publishing_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-21"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'publishing_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-21"]["than"] = "DROP TABLE `publishing_fields`";

    $operation_fixed["2015-01-22"]["if"] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'publishing_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-22"]["than"] = "RENAME TABLE `publishing_vgallery`  TO `publishing_fields`";

    $operation_fixed["2015-01-23"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'publishing_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-23"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_vgallery_fields' AND COLUMN_NAME <> 'ID_fields' AND TABLE_NAME = 'publishing_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-23"]["than"] = "ALTER TABLE `publishing_fields` CHANGE `ID_vgallery_fields` `ID_fields` INT( 11 ) NOT NULL DEFAULT '0'";
    
    $operation_fixed["2015-01-25"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'value' AND TABLE_NAME = 'publishing_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-25"]["than"][] = "DELETE FROM `publishing_fields` WHERE `value` = 0";
    $operation_fixed["2015-01-25"]["than"][] = " ALTER TABLE `publishing_fields` DROP `value`";

    $operation_fixed["2015-01-26"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'public' AND COLUMN_NAME <> 'visible' AND TABLE_NAME = 'anagraph' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-01-26"]["than"] = "ALTER TABLE `anagraph` CHANGE `public` `visible` INT( 1 ) NOT NULL DEFAULT '0'";

    $operation_fixed["2015-02-27"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_vgallery_nodes' AND COLUMN_NAME <> 'ID_nodes' AND TABLE_NAME = 'users_rel_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-02-27"]["than"] = "ALTER TABLE  `users_rel_vgallery` CHANGE  `ID_vgallery_nodes`  `ID_nodes` INT( 11 ) NOT NULL";

    $operation_fixed["2015-03-01"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_anagraph_selection' AND COLUMN_NAME <> 'ID_selection' AND TABLE_NAME = 'anagraph_fields_selection_value' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-03-01"]["than"] = "ALTER TABLE  `anagraph_fields_selection_value` CHANGE  `ID_anagraph_selection`  `ID_selection` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-03-02"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form_fields_selection' AND COLUMN_NAME <> 'ID_selection' AND TABLE_NAME = 'module_form_fields_selection_value' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-03-02"]["than"] = "ALTER TABLE  `module_form_fields_selection_value` CHANGE  `ID_form_fields_selection`  `ID_selection` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-03-03"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_anagraph_fields_father' AND COLUMN_NAME <> 'ID_fields_father' AND TABLE_NAME = 'anagraph_fields_selection' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-03-03"]["than"][] = "ALTER TABLE  `anagraph_fields_selection` CHANGE  `ID_anagraph_fields_father`  `ID_fields_father` INT( 11 ) NOT NULL";   
    $operation_fixed["2015-03-03"]["than"][] = "ALTER TABLE  `anagraph_fields_selection` CHANGE  `ID_anagraph_fields_child`  `ID_fields_child` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-03-04"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'value' AND COLUMN_NAME <> 'name' AND TABLE_NAME = 'vgallery_fields_selection' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-03-04"]["than"] = "ALTER TABLE  `vgallery_fields_selection` CHANGE  `value`  `name` VARCHAR( 255 ) NOT NULL";   
    
    $operation_fixed["2015-04-29-a1"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'module_register_rel_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-a1"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'module_register_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-a1"]["than"] = "DROP TABLE `module_register_fields`";

    $operation_fixed["2015-04-29-a2"]["if"] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'module_register_rel_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-a2"]["than"] = "RENAME TABLE `module_register_rel_fields`  TO `module_register_fields`";
    
    $operation_fixed["2015-04-29-b"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_module_form' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_form_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-b"]["than"] = "ALTER TABLE  `module_form_fields` CHANGE  `ID_module_form`  `ID_module` INT( 11 ) NOT NULL"; 
    
    $operation_fixed["2015-04-29-e"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_module_register' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_register_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-e"]["than"] = "ALTER TABLE  `module_register_fields` CHANGE  `ID_module_register`  `ID_module` INT( 11 ) NOT NULL"; 
    
    $operation_fixed["2015-04-29-c"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form_fields_selection' AND COLUMN_NAME <> 'ID_fields_selection' AND TABLE_NAME = 'module_form_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-c"]["than"] = "ALTER TABLE  `module_form_fields` CHANGE  `ID_form_fields_selection`  `ID_fields_selection` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-04-29-d"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form_fields_selection' AND COLUMN_NAME <> 'ID_fields_selection' AND TABLE_NAME = 'module_register_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-d"]["than"] = "ALTER TABLE  `module_register_fields` CHANGE  `ID_form_fields_selection`  `ID_fields_selection` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-04-29-e"]["if"] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'module_search_rel_fields' AND Table_Name <> 'module_search_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-04-29-e"]["than"] = "RENAME TABLE `module_search_rel_fields`  TO `module_search_fields`";

    $operation_fixed["2015-06-21-a"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_fields_selection' AND COLUMN_NAME <> 'ID_selection' AND TABLE_NAME = 'anagraph_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-a"]["than"] = "ALTER TABLE  `anagraph_fields` CHANGE  `ID_fields_selection`  `ID_selection` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-b"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_fields_selection' AND COLUMN_NAME <> 'ID_selection' AND TABLE_NAME = 'module_form_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-b"]["than"] = "ALTER TABLE  `module_form_fields` CHANGE  `ID_fields_selection`  `ID_selection` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-c"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_fields_selection' AND COLUMN_NAME <> 'ID_selection' AND TABLE_NAME = 'module_register_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-c"]["than"] = "ALTER TABLE  `module_register_fields` CHANGE  `ID_fields_selection`  `ID_selection` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-d"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form_fields_selection' AND COLUMN_NAME <> 'ID_selection' AND TABLE_NAME = 'module_search_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-d"]["than"] = "ALTER TABLE  `module_search_fields` CHANGE `ID_form_fields_selection`  `ID_selection` INT( 11 ) NOT NULL";   
    
    $operation_fixed["2015-06-21-e"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_form_dep' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-e"]["than"] = "ALTER TABLE  `module_form_dep` CHANGE `ID_form`  `ID_module` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-f"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form_field' AND COLUMN_NAME <> 'ID_form_fields' AND TABLE_NAME = 'module_form_dep' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-f"]["than"] = "ALTER TABLE  `module_form_dep` CHANGE `ID_form_field`  `ID_form_fields` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-g"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form_field_selection_value' AND COLUMN_NAME <> 'ID_selection_value' AND TABLE_NAME = 'module_form_dep' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-g"]["than"] = "ALTER TABLE  `module_form_dep` CHANGE `ID_form_field_selection_value`  `ID_selection_value` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-h"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'dep_field_selection_value' AND COLUMN_NAME <> 'dep_selection_value' AND TABLE_NAME = 'module_form_dep' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-h"]["than"] = "ALTER TABLE  `module_form_dep` CHANGE `dep_field_selection_value`  `dep_selection_value` INT( 11 ) NOT NULL";   
    
    $operation_fixed["2015-06-21-i"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form_field' AND COLUMN_NAME <> 'ID_form_fields' AND TABLE_NAME = 'module_form_pricelist_detail' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-i"]["than"] = "ALTER TABLE  `module_form_pricelist_detail` CHANGE `ID_form_field`  `ID_form_fields` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-l"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_form_pricelist' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-l"]["than"] = "ALTER TABLE  `module_form_pricelist` CHANGE `ID_form`  `ID_module` INT( 11 ) NOT NULL";   
    
    $operation_fixed["2015-06-21-m"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_form_nodes' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-m"]["than"] = "ALTER TABLE  `module_form_nodes` CHANGE `ID_form`  `ID_module` INT( 11 ) NOT NULL";   
    
    $operation_fixed["2015-06-21-n"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'users_rel_module_form' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-n"]["than"] = "ALTER TABLE  `users_rel_module_form` CHANGE `ID_form`  `ID_module` INT( 11 ) NOT NULL";   

    $operation_fixed["2015-06-21-o"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_form' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_register_rel_form' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-21-o"]["than"] = "ALTER TABLE  `module_register_rel_form` CHANGE `ID_form`  `ID_module` INT( 11 ) NOT NULL";   
    
    $operation_fixed["2015-06-22"]["if"] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_location' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ) AND NOT(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'is_main' AND TABLE_NAME = 'layout_location' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' )), 1, 0 ) AS val"; 
    $operation_fixed["2015-06-22"]["than"] = "ALTER TABLE  `layout_location` ADD  `is_main` INT( 1 ) NOT NULL";    
    
    $operation_fixed["2015-06-23"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'is_main' AND TABLE_NAME = 'layout_location' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
	$operation_fixed["2015-06-23"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_location` WHERE LOWER(layout_location.name) = 'content' AND layout_location.is_main = '0' ) AS val";    
    $operation_fixed["2015-06-23"]["than"] = "UPDATE  `layout_location` SET is_main = '1' WHERE LOWER(layout_location.name) = 'content'";       
    
   	$operation_fixed["2015-09-17"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'main_theme' AND TABLE_NAME = 'cm_layout' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
   	$operation_fixed["2015-09-17"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `cm_layout` WHERE main_theme = 'restricted') AS val"; 
   	$operation_fixed["2015-09-17"]["than"] = "UPDATE `cm_layout` SET main_theme = 'responsive' WHERE main_theme = 'restricted'";

    $operation_fixed["2016-01-14"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_layer_path' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
    $operation_fixed["2016-01-14"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_layer` WHERE layout_layer.ID NOT IN ( SELECT compare_layout_layer_path.ID_layout_layer FROM layout_layer_path AS compare_layout_layer_path) ) AS val";
    $operation_fixed["2016-01-14"]["than"][] = "INSERT INTO layout_layer_path ( ID, `ID_layout_layer`, `path`, `visible`, `cascading`, `class`, `fluid`, `wrap`, `width` ) SELECT null, layout_layer.ID , '%', '1', '1', layout_layer.`class`, layout_layer.`fluid`, layout_layer.`wrap`, layout_layer.`width` FROM  `layout_layer` WHERE ID NOT IN ( SELECT compare_layout_layer_path.ID_layout_layer FROM layout_layer_path AS compare_layout_layer_path )";

    $operation_fixed["2016-01-15"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'layout_layer_path' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
    $operation_fixed["2016-01-15"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_layer_path` WHERE layout_layer_path.path LIKE '%(.*)%' OR layout_layer_path.path LIKE '%(.+)%' ) AS val";
    $operation_fixed["2016-01-15"]["than"][] = "UPDATE `layout_layer_path` SET `path` = REPLACE(REPLACE(layout_layer_path.path, '(.+)', '%'), '(.*)', '%') WHERE layout_layer_path.path LIKE '%(.*)%' OR layout_layer_path.path LIKE '%(.+)%'";

    $operation_fixed["2016-01-29"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ereg_path' AND TABLE_NAME = 'layout_path' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2016-01-29"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `layout_path` WHERE `ereg_path` = '') AS val"; 
    $operation_fixed["2016-01-29"]["than"] = "UPDATE `layout_path` SET `ereg_path` = IF(`path` = '/' AND `cascading` > 0, '%', CONCAT(`path`, IF(`cascading` > 0, '%', '')))  WHERE `layout_path`.`ereg_path` = ''";
    
    $operation_fixed["2016-06-10-a"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_module_search' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_search_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2016-06-10-a"]["than"] = "ALTER TABLE  `module_search_vgallery` CHANGE  `ID_module_search`  `ID_module` INT( 11 ) NOT NULL"; 

    if(!defined("SUPERADMIN_USERNAME"))
    	require_once(FF_DISK_PATH . "/conf/gallery/config/admin.php");
    	
	if(defined("SUPERADMIN_USERNAME") && SUPERADMIN_USERNAME) {
	    $operation_fixed["2016-03-22"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'cm_mod_security_users' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
	    $operation_fixed["2016-03-22"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 0, 1 ) AS val FROM `cm_mod_security_users` WHERE ID = 1 AND username = '" . SUPERADMIN_USERNAME . "' AND `password` = PASSWORD('" . SUPERADMIN_PASSWORD . "')) AS val"; 
	    $operation_fixed["2016-03-22"]["than"][] = "DELETE FROM `cm_mod_security_users` WHERE ID = 1"; 
	    $operation_fixed["2016-03-22"]["than"][] = "INSERT INTO `cm_mod_security_users` (`ID`, `level`, `status`, `username`, `password`, `primary_gid`) VALUES (1, 3, 1, '" . SUPERADMIN_USERNAME . "', PASSWORD('" . SUPERADMIN_PASSWORD . "'), 1)"; 

	    $operation_fixed["2016-03-23"]["if"][] = "SELECT IF(EXISTS(SELECT Table_Name FROM information_schema.TABLES WHERE Table_Name = 'cm_mod_security_users' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val";                                     
	    $operation_fixed["2016-03-23"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 0, 1 ) AS val FROM `cm_mod_security_users` WHERE ID = 2 AND username = 'guest' AND `password` = 'guest') AS val"; 
	    $operation_fixed["2016-03-23"]["than"][] = "DELETE FROM `cm_mod_security_users` WHERE ID = 2"; 
	    $operation_fixed["2016-03-23"]["than"][] = "INSERT INTO `cm_mod_security_users` (`ID`, `level`, `status`, `username`, `password`, `primary_gid`) VALUES (2, 0, 1, 'guest', 'guest', 2)"; 
	}
    
    $operation_fixed["2016-05-05"]["if"][] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'display_name' AND TABLE_NAME = 'drafts' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2016-05-05"]["if"][] = "SELECT (SELECT IF(COUNT( * ) > 0, 1, 0 ) AS val FROM `drafts` WHERE `display_name` = '') AS val"; 
    $operation_fixed["2016-05-05"]["than"] = "UPDATE `drafts` SET `display_name` = REPLACE(`name`, '-', ' ')  WHERE `display_name` = ''";
    
    $operation_fixed["2016-06-10-a"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_module_search' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_search_vgallery' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2016-06-10-a"]["than"] = "ALTER TABLE  `module_search_vgallery` CHANGE  `ID_module_search`  `ID_module` INT( 11 ) NOT NULL"; 

    $operation_fixed["2016-06-10-b"]["if"] = "SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'ID_module_search' AND COLUMN_NAME <> 'ID_module' AND TABLE_NAME = 'module_search_fields' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"; 
    $operation_fixed["2016-06-10-b"]["than"] = "ALTER TABLE  `module_search_fields` CHANGE  `ID_module_search`  `ID_module` INT( 11 ) NOT NULL"; 
