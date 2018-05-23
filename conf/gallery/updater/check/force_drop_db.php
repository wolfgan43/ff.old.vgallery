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
  $db_drop = array(
  		"spesedispedizione" => true
  		, "loc_comuni" => true
  		, "loc_provincie" => true
  		, "loc_regioni" => true
  		, "loc_stati" => true
  		, "loc_zone" => true
  		, "ff_international__" => true
  		, "cm_layout" => true
  		, "cm_layout_cdn" => true
  		, "cm_layout_css" => true
  		, "cm_layout_js" => true
  		, "cm_layout_meta" => true
  		, "cm_layout_sect" => true
  );
  $db_drop_prefix = array();
  
  require_once(ffCommon_dirname(__FILE__) . "/manifesto.php");
  require(ffCommon_dirname(__FILE__) . "/exclude_fs.php");
   /* $fs_external = array();    
    $sSQL = "SELECT updater_externals.path
                , updater_externals.status 
            FROM updater_externals
            WHERE updater_externals.domain = " . $db->toSql(DOMAIN_INSET) . " 
                AND updater_externals.status > 0";
    $db->query($sSQL);
    if($db->nextRecord()) { 
        do {
            $fs_external[$db->getField("path", "Text", true)] = $db->getField("status", "Number", true);
        } while($db->nextRecord());
    }*/

    if(is_array($manifesto) && count($manifesto)) {
        foreach($manifesto AS $manifesto_key => $manifesto_value) {
            $skip_force_drop = false;
            if($manifesto_value["enable"]) {
                if (is_array($manifesto_value["path"]) && count($manifesto_value["path"])) {
                    foreach ($manifesto_value["path"] AS $path_value) {
                        if (array_key_exists($path_value, $fs_exclude)) {
                            $skip_force_drop = true;
                        }
                    }
                } elseif (strlen($manifesto_value["path"])) {
                    if (array_key_exists($manifesto_value["path"], $fs_exclude)) {
                        $skip_force_drop = true;
                    }
                }
            }
            if(!$skip_force_drop) {
                if(is_array($manifesto_value["db"])) {
                    if(is_array($manifesto_value["db"]["table_prefix"])) {
                        foreach($manifesto_value["db"]["table_prefix"] AS $db_value) {
                            if(strlen($db_value)) {
                                $db_drop_prefix[] = $db_value;
                            }
                        }
                    } else {
                        if(strlen($manifesto_value["db"]["table_prefix"])) {
                            $db_drop_prefix[] = $manifesto_value["db"]["table_prefix"];
                        }
                    }
                    if(is_array($manifesto_value["db"]["tables"])) {
                        foreach($manifesto_value["db"]["tables"] AS $db_value) {
                            if(strlen($db_value)) {
                                $db_drop[$db_value] = true;
                            }
                        }
                    } else {
                        if(strlen($manifesto_value["db"]["tables"])) {
                            $db_drop[$manifesto_value["db"]["tables"]] = true;
                        }
                    }
                }
            }
        }
    }
  
