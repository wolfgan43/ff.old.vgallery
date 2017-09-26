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
  $db_include = array();
  $db_include["international"]["ff_languages"] = array("rel" => array(), "data" => "", "key" => array("code" => true), "compare" => array("description" => true, "tiny_code" => true));
  $db_include["international"]["ff_international"] = array("rel" => array("ID_lang" => "ff_languages"), "data" => " is_new = 0", "key" => array("ID_lang" => true, "word_code" => true), "compare" => array("ID_lang" => true, "word_code" => true, "description" => true), "exclude" => array("exclude_update" => "1"));
//  $db_include["locations"]["loc_comuni"] = array("rel" => array(), "data" => "", "key" => array(), "compare" => array());
//  $db_include["locations"]["loc_provincie"] = array("rel" => array(), "data" => "", "key" => array(), "compare" => array());
//  $db_include["locations"]["loc_regioni"] = array("rel" => array(), "data" => "", "key" => array(), "compare" => array());
//  $db_include["locations"]["loc_stati"] = array("rel" => array(), "data" => "", "key" => array(), "compare" => array());
  $db_include["basic"]["check_control"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "name" => true), "compare" => array("ID" => true, "regexp" => true, "ff_name" => true));
  $db_include["basic"]["cm_layout"] = array("rel" => array(), "data" => "`path` IN('/', '/admin', '/restricted', '/manage', '/admin/login', '/restricted/login', '/manage/login', '/frame') AND domains = ''", "key" => array("path" => true), "compare" => array("path" => true, "main_theme" => true, "reset_css" => true, "reset_js" => true, "enable_cascading" => true, "reset_cascading" => true, "ignore_defaults" => true, "enable_gzip" => true, "compact_js" => true, "compact_css" => true, "ignore_defaults_main" => true, "domains" => true));
  $db_include["basic"]["cm_layout_css"] = array("rel" => array("ID_layout" => "cm_layout"), "data" => "`ID_layout` IN(SELECT ID FROM cm_layout WHERE `path` IN('/admin', '/restricted', '/manage', '/admin/login', '/restricted/login', '/manage/login', '/frame'))", "key" => array("ID_layout" => true, "path" => true), "compare" => array());
  $db_include["basic"]["cm_layout_js"] = array("rel" => array("ID_layout" => "cm_layout"), "data" => "`ID_layout` IN(SELECT ID FROM cm_layout WHERE `path` IN('/admin', '/restricted', '/manage', '/admin/login', '/restricted/login', '/manage/login', '/frame'))", "key" => array("ID_layout" => true, "plugin_path" => true, "js_path" => true), "compare" => array());
  $db_include["basic"]["cm_mod_security_groups"] = array("rel" => array(), "data" => "`gid` IN(1, 2, 3, 4, 5)", "key" => array(), "compare" => array(), "altKey" => "gid");
  $db_include["basic"]["cm_mod_security_users"] = array("rel" => array(), "data" => "`ID` IN(1, 2)", "key" => array("ID" => true), "compare" => array("ID" => true), "operation" => array("insert" => true));
  $db_include["basic"]["cm_mod_security_users_rel_groups"] = array("rel" => array(), "data" => "`uid` IN(1, 2) AND `gid` IN(1, 2, 3, 4, 5)", "key" => array(), "compare" => array());
  $db_include["basic"]["cm_showfiles"] = array("rel" => array(), "data" => "", "key" => array("name" => true), "compare" => array());
  $db_include["basic"]["cm_showfiles_modes"] = array("rel" => array("ID_showfiles" => "cm_showfiles"), "data" => "LOCATE('-', name) = 0", "key" => array("name" => true), "compare" => array("name" => true), "operation" => array("insert" => true, "update" => false, "delete" => false));
  $db_include["basic"]["cm_showfiles_where"] = array("rel" => array("ID_showfiles" => "cm_showfiles"), "data" => "", "key" => array("ID" => true, "name" => true), "compare" => array());
  //$db_include["basic"]["ecommerce_documents_type"] = array("rel" => array(), "data" => "", "key" => array(), "compare" => array());
  $db_include["basic"]["ff_languages"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "code" => true), "compare" => array("ID" => true, "code" => true, "description" => true, "tiny_code" => true, "stopwords" => true));
  
  if((MASTER_SITE == DOMAIN_INSET) || (MASTER_SITE != DOMAIN_INSET && is_dir(FF_DISK_PATH . "/conf/gallery/mc"))) {
	  $db_include["support"]["ff_ip2nation"] = array("rel" => array(), "data" => "", "key" => array("ip" => true, "country" => true), "compare" => array());
  }

  $db_include["support"]["ff_ip2nationCountries"] = array("rel" => array(), "data" => "", "key" => array("code" => true), "compare" => array());  
  $db_include["support"]["ff_currency"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "code" => true), "compare" => array("ID" => true, "code" => true, "symbol" => true));
  if(is_dir(FF_DISK_PATH . "/conf/gallery/ecommerce")) {
	  $db_include["support"]["ecommerce_mpay_zone"] = array("rel" => array(), "data" => "name IN('Zone 0', 'Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6')", "key" => array("ID" => true), "compare" => array("ID" => true, "name" => true));
	  $db_include["support"]["support_state"] = array("rel" => array("ID_zone" => "ecommerce_mpay_zone", "ID_lang" => "ff_languages"), "data" => "", "key" => array("ID" => true, "smart_url" => true), "compare" => array("ID" => true, "smart_url" => true));
  } else {
	  $db_include["support"]["support_state"] = array("rel" => array("ID_lang" => "ff_languages"), "data" => "", "key" => array("ID" => true, "smart_url" => true), "compare" => array("ID" => true, "smart_url" => true));
  }
  $db_include["support"]["support_region"] = array("rel" => array("ID_state" => "support_state"), "data" => "", "key" => array("ID" => true, "smart_url" => true), "compare" => array("ID" => true, "smart_url" => true));
  $db_include["support"]["support_province"] = array("rel" => array("ID_state" => "support_state", "ID_region" => "support_region"), "data" => "", "key" => array("ID" => true, "smart_url" => true), "compare" => array("ID" => true, "smart_url" => true));
  $db_include["support"]["support_city"] = array("rel" => array("ID_state" => "support_state", "ID_region" => "support_region", "ID_province" => "support_province"), "data" => "", "key" => array("ID" => true, "smart_url" => true), "compare" => array("ID" => true, "smart_url" => true));

  $db_include["basic"]["extended_type"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "name" => true), "compare" => array("ID" => true, "name" => true, "ff_name" => true, "limit_by_data_type" => true));
  $db_include["basic"]["files"] = array("rel" => array(), "data" => "`ID` = 1", "key" => array("ID" => true), "compare" => array("ID" => true, "name" => true, "parent" => true, "is_dir" => true));
  $db_include["basic"]["files_rel_groups"] = array("rel" => array("ID_files" => "files"), "data" => "`ID_files` =  1 AND `gid` IN(1, 2, 3, 4, 5)", "key" => array("ID_files" => true, "gid" => true), "compare" => array());
  //$db_include["basic"]["js"] = array("rel" => array(), "data" => "", "key" => array("name" => true), "compare" => array("name" => true));
  //$db_include["basic"]["js_config"] = array("rel" => array(), "data" => "", "key" => array(), "compare" => array());
  //$db_include["basic"]["js_dipendence"] = array("rel" => array("ID_js_plugin" => "js", "ID_js_libs" => "js"), "data" => "", "key" => array("ID_js_plugin" => true, "ID_js_libs" => true), "compare" => array());
  //$db_include["basic"]["layout_template"] = array("rel" => array(), "data" => "`ID` IN(1) OR layout_template.`public` > 1", "key" => array("ID" => true), "compare" => array("ID" => true));
  //$db_include["basic"]["layout_layer"] = array("rel" => array(), "data" => " `name` IN('layer1', 'layer2', 'layer3')", "key" => array("name" => true), "compare" => array("name" => true), "operation" => array("insert" => true, "update" => false, "delete" => false));
  //$db_include["basic"]["layout_location"] = array("rel" => array("ID_layer" => "layout_layer"), "data" => " `name` = 'Content' ", "key" => array("name" => true), "compare" => array("name" => true, "process_level" => true), "operation" => array("insert" => true, "update" => false, "delete" => false));
  $db_include["basic"]["layout_type"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "name" => true), "compare" => array());
  $db_include["basic"]["layout_settings"] = array("rel" => array("ID_layout_type" => "layout_type", "ID_extended_type" => "extended_type"), "data" => "", "key" => array("name" => true, "ID_layout_type" => true), "compare" => array());
  $db_include["basic"]["layout_settings_rel"] = array("rel" => array("ID_layout_settings" => "layout_settings"), "data" => "`ID_layout` = 0", "key" => array("ID_layout_settings" => true, "ID_layout" => true), "compare" => array("ID_layout" => true, "ID_layout_settings" => true));
  //$db_include["basic"]["layout_type_plugin"] = array("rel" => array("ID_layout_type" => "layout_type", "ID_js" => "js"), "data" => "", "key" => array("ID_layout_type" => true, "ID_js" => true), "compare" => array());
  $db_include["basic"]["notify_schedule"] = array("rel" => array(), "data" => "", "key" => array("area" => true, "job" => true), "compare" => array("name" => true, "area" => true, "job" => true));
  $db_include["basic"]["settings"] = array("rel" => array(), "data" => "", "key" => array("description" => true), "compare" => array());
  $db_include["basic"]["settings_rel_path"] = array("rel" => array("uid" => "cm_mod_security_users", "gid" => "cm_mod_security_groups"), "data" => "`ID` IN(1, 2, 3, 4, 5)", "key" => array("ID" => true), "compare" => array());
  $db_include["basic"]["settings_rel_path_settings"] = array("rel" => array("ID_rel_path" => "settings_rel_path", "ID_settings" => "settings"), "data" => "`ID_rel_path` IN(1, 2, 3, 4, 5)", "key" => array("ID_rel_path" => true, "ID_settings" => true), "compare" => array("ID_rel_path" => true, "ID_settings" => true));
  //$db_include["basic"]["settings_thumb_image"] = array("rel" => array(), "data" => "(`ID` IN(1, 2) OR settings_thumb_image.ID IN(SELECT IF(vgallery_fields.settings_type REGEXP '^[0-9]$', vgallery_fields.settings_type, 0) FROM vgallery_fields WHERE vgallery_fields.ID_type IN(SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.`public` > 0)))", "key" => array("ID" => true), "compare" => array("ID" => true));
  //$db_include["basic"]["settings_thumb_mode"] = array("rel" => array(), "data" => "`public` > 0", "key" => array("ID" => true, "name" => true), "compare" => array("ID" => true, "name" => true, "description" => true, "public" => true, "type" => true));
  $db_include["basic"]["static_pages"] = array("rel" => array(), "data" => "`ID` = 1", "key" => array("ID" => true), "compare" => array("name" => true, "parent" => true));
  $db_include["basic"]["static_pages_rel_groups"] = array("rel" => array("ID_static_pages" => "static_pages"), "data" => "`ID_static_pages` =  1 AND `gid` IN(1, 2, 3, 4, 5)", "key" => array("ID_static_pages" => true, "gid" => true), "compare" => array("ID_static_pages" => true, "gid" => true));
  $db_include["basic"]["static_pages_rel_languages"] = array("rel" => array(), "data" => "`ID_static_pages` = 1", "key" => array("ID_static_pages" => true, "ID_languages" => true), "compare" => array("ID_static_pages" => true, "ID_languages" => true));
  $db_include["basic"]["vgallery_fields_data_type"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "name" => true), "compare" => array());
   
  $db_include["basic"]["vgallery_type"] = array("rel" => array(), "data" => "`name` IN('Directory')", "key" => array("ID" => true, "name" => true), "compare" => array("ID" => true, "name" => true, "is_dir_default" => true), "operation" => array("insert" => true, "update" => true, "delete" => true));
  $db_include["basic"]["vgallery_fields"] = array("rel" => array("ID_data_type" => "vgallery_fields_data_type", "ID_extended_type" => "extended_type", "ID_type" => "vgallery_type"), "data" => "(`ID_type` IN(2))", "key" => array("name" => true, "ID_type" => true), "compare" => array("ID_data_type" => true, "ID_extended_type" => true, "ID_type" => true, "name" => true, "parent" => true, "order" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));  
 // $db_include["basic"]["vgallery"] = array("rel" => array(), "data" => "`public` > 0", "key" => array("name" => true), "compare" => array("name" => true, "is_dir_default" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));
  $db_include["basic"]["vgallery_nodes"] = array("rel" => array("ID_vgallery" => "vgallery"), "data" => "vgallery_nodes.`ID` = 0", "key" => array("ID" => true), "compare" => array("ID" => true, "name" => true, "parent" => true, "is_dir" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));
  $db_include["basic"]["vgallery_nodes_rel_groups"] = array("rel" => array("ID_vgallery_nodes" => "vgallery_nodes"), "data" => "`ID_vgallery_nodes` =  0 AND `gid` IN(1, 2, 3, 4, 5)", "key" => array(), "compare" => array());
  
  
  //$db_include["sync-basic"]["anagraph"] = array("rel" => array(), "data" => "", "key" => array("ID" => true), "compare" => array());
  //$db_include["sync-basic"]["anagraph_rel_nodes_fields"] = array("rel" => array("ID_anagraph" => "anagraph"), "data" => "", "key" => array("ID" => true), "compare" => array());
  //$db_include["sync-basic"]["cm_mod_security_users"] = array("rel" => array(), "data" => "", "key" => array("ID" => true), "compare" => array());
  //$db_include["sync-basic"]["cm_mod_security_users_fields"] = array("rel" => array("ID_users" => "cm_mod_security_users"), "data" => "", "key" => array("ID" => true), "compare" => array());
  //$db_include["sync-basic"]["cm_mod_security_users_rel_groups"] = array("rel" => array(), "data" => "", "key" => array(), "compare" => array());
  //$db_include["sync-basic"]["ecommerce_order"] = array("rel" => array("ID_anagraph" => "anagraph"), "data" => "", "key" => array("ID" => true), "compare" => array());
  //$db_include["sync-basic"]["ecommerce_order_detail"] = array("rel" => array("ID_order" => "ecommerce_order"), "data" => "", "key" => array("ID" => true), "compare" => array());
  //$db_include["sync-basic"]["ecommerce_order_history"] = array("rel" => array("ID_order" => "ecommerce_order"), "data" => "", "key" => array("ID" => true), "compare" => array());
  //$db_include["sync-basic"]["ecommerce_settings"] = array("rel" => array(), "data" => "", "key" => array("ID" => true), "compare" => array());
  $db_include["sync-international"]["ff_languages"] = array("rel" => array(), "data" => "", "key" => array("code" => true), "compare" => array("description" => true, "tiny_code" => true));
  $db_include["sync-international"]["ff_international"] = array("rel" => array("ID_lang" => "ff_languages"), "data" => " is_new = 0", "key" => array("ID_lang" => true, "word_code" => true), "compare" => array("ID_lang" => true, "word_code" => true, "description" => true), "exclude" => array());
  
  
  
  $db_include["unatantum"]["vgallery_fields_data_type"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "name" => true), "compare" => array());
  $db_include["unatantum"]["extended_type"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "name" => true), "compare" => array("ID" => true, "name" => true, "ff_name" => true));
  $db_include["unatantum"]["ff_languages"] = array("rel" => array(), "data" => "", "key" => array("ID" => true, "code" => true), "compare" => array("ID" => true, "code" => true, "description" => true, "tiny_code" => true));
  $db_include["unatantum"]["vgallery_type"] = array("rel" => array(), "data" => "`name` IN('Directory') OR `public` > 0 OR (vgallery_type.ID IN (SELECT vgallery_nodes.ID_type FROM vgallery_nodes WHERE vgallery_nodes.public > 0))", "key" => array("name" => true), "compare" => array("name" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));  
  
  $db_include["unatantum"]["vgallery"] = array("rel" => array(), "data" => "`public` > 0 OR vgallery.ID IN( SELECT vgallery_nodes.ID_vgallery FROM vgallery_nodes WHERE vgallery_nodes.public > 0)", "key" => array("name" => true), "compare" => array("name" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));
  
  $db_include["unatantum"]["vgallery_fields"] = array("rel" => array("ID_data_type" => "vgallery_fields_data_type", "ID_extended_type" => "extended_type", "ID_type" => "vgallery_type"), "data" => "(`ID_type` IN(2)) OR (ID_type IN (SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.`public` > 0)) OR (vgallery_fields.ID_type IN (SELECT vgallery_nodes.ID_type FROM vgallery_nodes WHERE vgallery_nodes.public > 0))", "key" => array("name" => true, "ID_type" => true), "compare" => array("ID_data_type" => true, "ID_extended_type" => true, "ID_type" => true, "name" => true, "parent" => true, "order" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));  
  $db_include["unatantum"]["vgallery_nodes"] = array("rel" => array("ID_vgallery" => "vgallery", "ID_type" => "vgallery_type"), "data" => "vgallery_nodes.`ID` = 0 OR vgallery_nodes.public > 0 OR (vgallery_nodes.parent = '/' AND vgallery_nodes.name = (SELECT vgallery.name FROM vgallery WHERE vgallery.`public` > 0 OR vgallery.ID IN( SELECT tblnode.ID_vgallery FROM vgallery_nodes AS tblnode WHERE tblnode.public > 0)))", "key" => array("name" => true, "parent" => true), "compare" => array("name" => true, "parent" => true, "is_dir" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));
  $db_include["unatantum"]["vgallery_rel_nodes_fields"] = array("rel" => array("ID_nodes" => "vgallery_nodes", "ID_fields" => "vgallery_fields", "ID_lang" => "ff_languages"), "data" => "ID_nodes IN( SELECT vgallery_nodes.ID FROM vgallery_nodes WHERE vgallery_nodes.public > 0)", "key" => array("ID_nodes" => true, "ID_fields" => true, "ID_lang" => true), "compare" => array("ID_nodes" => true, "ID_fields" => true, "ID_lang" => true), "operation" => array("insert" => true, "update" => false, "delete" => true));  
  
   
