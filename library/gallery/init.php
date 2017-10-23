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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
/*	local_common.php
	----------------
	In this file take place all extra stuff defined by user, site relative.
	Forms not define any code in this file, only manage it.
*/

function cache_set($ID, $tbl = null) {
	$globals = ffGlobals::getInstance("gallery");
	
	$tbl = strtoupper($tbl);
	if(!$tbl)
		$tbl = "M";

	switch($tbl) {
		case "V":
		case "G":
		case "S":
		case "D":
		case "T":
		case "M":
			break;
		default:
			$ID = $tbl . "-" . $ID;
			$tbl = "M";
	}

	$globals->cache["data_blocks"][$tbl][$ID] = str_replace(",", "", $ID);
}

function set_cache_data($tbl, $ID, $modal = null, $value = null) {
    $globals = ffGlobals::getInstance("gallery");
/*
            V = Virtual Gallery
            G = Gallery
            S = Static Menu
            D = Draft Database
            T = Draft Html
            M = Module
* 
*/
	if(1) {
	    cache_set($ID, $tbl);
	} else {
	    switch($tbl) {
	        default:
	            $prefix = $tbl;
	    }
	    
	    $globals->cache["data_blocks"][$prefix . $modal . "-" . $ID] = $value;
	}
}

function get_template_cascading($path, $tpl_data, $sub_path = "", $force_base_path = null, $location = "") 
{
    $globals = ffGlobals::getInstance("gallery");
    
    $tmp_path = $path;
    $real_path = NULL;

    if(strlen($location))
    	$location = "/" . strtolower($location);
	
	if(is_array($tpl_data))
	{
		$tpl_prefix 		= $tpl_data["prefix"];
		$tpl_custom_name 	= $tpl_data["custom"];
		$tpl_base_name 		= $tpl_data["base"];
		
		if(!$sub_path)
			$sub_path = $tpl_data["path"];

        $tpl_type			= "custom";
	}
	else {
		$tpl_custom_name = $tpl_data;
		$tpl_base_name = $tpl_data;
	}

	if(strlen($tpl_custom_name)) {
		if($tpl_prefix) {
			if($globals->html["frontend"]["/" . $tpl_prefix . "_" . $tpl_custom_name]) {
				$real_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents";
				$real_prefix = $tpl_prefix . "_";
			}
		}
		if($real_path === NULL) {
			do {
		         if($location && $globals->html["frontend"][stripslash($tmp_path) . $location . "/" . $tpl_custom_name]) {
		            $real_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents" . stripslash($tmp_path) . $location;
		         } elseif($globals->html["frontend"][stripslash($tmp_path) . "/" . $tpl_custom_name]) {
		            $real_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents" . stripslash($tmp_path);
				 }
		     } while($tmp_path != ffCommon_dirname($tmp_path) && $real_path === NULL && $tmp_path = ffCommon_dirname($tmp_path));
		}
	}     

    if($real_path === NULL) {
    	if(strlen($tpl_base_name)) 
    	{
			if(strlen($force_base_path)) {
				$real_path = $force_base_path;
			} else {
				$real_path = __CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/contents" . stripslash($sub_path);
			}
    	
			$tpl_type = "base";
		}
    }

    if(is_array($tpl_data))
    	return array("path" => $real_path
    				, "type" => $tpl_type
    				, "prefix" => $real_prefix
    			);
	else 
    	return $real_path;
}


function get_template_cascading_old($path, $tpl_data, $sub_path = "", $force_base_path = null, $location = "") 
{
    $cm = cm::getInstance();
    
    $tmp_path = $path;
    $real_path = NULL;

    if(strlen($location))
    	$location = "/" . strtolower($location);
	
	if(is_array($tpl_data))
	{
		$tpl_prefix 		= $tpl_data["prefix"];
		$tpl_custom_name 	= $tpl_data["custom"];
		$tpl_base_name 		= $tpl_data["base"];
		
		if(!$sub_path)
			$sub_path = $tpl_data["path"];

        $tpl_type			= "custom";
	}
	else {
		$tpl_custom_name = $tpl_data;
		$tpl_base_name = $tpl_data;
	}
	
	if(strlen($tpl_custom_name)) {
		if($tpl_prefix) {
			if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents/" . $tpl_prefix . "_" . $tpl_custom_name)) {
				$real_path = FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents";
				$real_prefix = $tpl_prefix . "_";
			}
		}
		if($real_path === NULL) {
			do {
				if(isset($_REQUEST["__tplcascading__"]) && get_session("UserID") == SUPERADMIN_USERNAME) {
					if($location)
						echo FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents" . stripslash($tmp_path) . $location . "/" . $tpl_custom_name . "<br>";

					echo FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents" . stripslash($tmp_path) . "/" . $tpl_custom_name . "<br>";
					if($cm->oPage->theme != FRONTEND_THEME)
						echo FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents" . stripslash($tmp_path) . "/" . $tpl_custom_name . "<br>";
					echo FF_THEME_DIR . "/" . THEME_INSET . "/contents" . "/" . $tpl_custom_name . "<br>";
					if(strlen($force_base_path))
						echo $force_base_path . "/" . $tpl_custom_name;
					echo "<br>";
				}     	 
		         if($location && is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents" . stripslash($tmp_path) . $location . "/" . $tpl_custom_name)) {
		            $real_path = FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents" . stripslash($tmp_path) . $location;
		         } elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents" . stripslash($tmp_path) . "/" . $tpl_custom_name)) {
		            $real_path = FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents" . stripslash($tmp_path);
		         } elseif($cm->oPage->theme != FRONTEND_THEME && is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents" . stripslash($tmp_path) . "/" . $tpl_custom_name)) {
		            $real_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents" . stripslash($tmp_path);
				 }
		     } while($tmp_path != ffCommon_dirname($tmp_path) && $real_path === NULL && $tmp_path = ffCommon_dirname($tmp_path));
		}
	}     

    if($real_path === NULL) {
    	if(strlen($tpl_base_name)) 
    	{
			if(is_file(__CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/contents" . stripslash($sub_path) . "/" . $tpl_base_name)) {
			    $real_path = __CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/contents" . stripslash($sub_path);
			} elseif(strlen($force_base_path) && is_file($force_base_path . "/" . $tpl_base_name)) {
			    $real_path = $force_base_path;
			}
			$tpl_type = "base";
		}
    }

    if(is_array($tpl_data))
    	return array("path" => $real_path
    				, "type" => $tpl_type
    				, "prefix" => $real_prefix
    			);
	else 
    	return $real_path;
}




function setJsRequest($tag, $type = "request") 
{
    $globals = ffGlobals::getInstance("gallery");
    //if (!isset($globals->js))
     //   $globals->js = array();

    if(!isset($globals->js[$type]))
        $globals->js[$type] = array();

    if(is_array($tag)) {
    	foreach($tag AS $js_value => $js_check) {
    		if($js_value && $js_check)
				$globals->js[$type][$js_value] = true;
    	}
    } else if(strlen($tag)) {
        $js = explode(",", $tag);
        foreach($js as $js_value) {
            if(strlen($js_value)) {
                $globals->js[$type][$js_value] = true;
            }
        }       
        $globals->js[$type][$tag] = true;
    }
}

/*
function check_page_alias($settings_path, $host, $return = "settings_path") {
	$globals = ffGlobals::getInstance("gallery");
	$cache = get_session("cache");
	
	if(!(is_array($cache) && array_key_exists("domains", $cache) && array_key_exists($host, $cache["domains"]))) {
		$db = ffDB_Sql::factory();
		$destination_path = "";
		$ID_domain = 0;
		
		$sSQL = "SELECT cache_page_alias.* 
				FROM cache_page_alias
				WHERE (" . $db->toSql($host) . " LIKE cache_page_alias.host 
						OR " . $db->toSql($settings_path, "Text") . " LIKE CONCAT(destination, '%')
					)
					AND cache_page_alias.status > 0
				ORDER BY ABS(LENGTH(" . $db->toSql($host) . ") - LENGTH(cache_page_alias.host))";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$destination_path = $db->getField("destination", "Text", true);
			
			if(!$db->getField("force_primary_domain", "Number", true))
				$ID_domain = $db->getField("ID", "Number", true);
		}
		$cache["domains"][$host]["strip_user_path"] = $destination_path;
		$cache["domains"][$host]["ID"] = $ID_domain;
		
		set_session("cache", $cache);
	}

	$globals->strip_user_path = $cache["domains"][$host]["strip_user_path"];
	$globals->ID_domain = $cache["domains"][$host]["ID"];
	
	switch($return) {
		case "settings_path":
			if (strlen($cache["domains"][$host]["strip_user_path"]) && strpos($settings_path, $cache["domains"][$host]["strip_user_path"]) !== 0)
			{
				$settings_path = $cache["domains"][$host]["strip_user_path"] . stripslash($settings_path);
			}
			//$globals->user_path = $settings_path;
			$res = $settings_path;
			break;
		case "ID_domain":
			$res = $cache["domains"][$host]["ID"];
			break;
		case "strip_user_path":
			$res = $cache["domains"][$host]["strip_user_path"];
			break;
		default:
			$res = null;
	}

	return $res;
}*/