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
  	function system_get_js_plugins($type = null, $return = "sql") {
		$globals = ffGlobals::getInstance("gallery");
		$db = ffDB_Sql::factory();
		$arrSQL = array();

		static $arrPlugins = null;
		if(!$arrPlugins)
			$arrPlugins = glob(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/javascript/plugins/*");

		if(is_array($arrPlugins) && count($arrPlugins)) {
			foreach($arrPlugins AS $plugin_path) {
		    	if(is_dir($plugin_path)) {
					$tmp = "";	
					$is_observe = false;	    		
					$objPlugin = null;
		    		$plugin_name = basename($plugin_path);
		    		
					if(strpos($plugin_name, "jquery.") === 0) {
						$lib_base = "jquery";
						$plugin_key = substr($plugin_name, strlen("jquery."));
					} else {
						$lib_base = "library";
						$plugin_key = $plugin_name;
					}
					
		    		if(is_file($plugin_path . "/libs." . FF_PHP_EXT)) {
						$tmp = include($plugin_path . "/libs." . FF_PHP_EXT);
						$is_observe = true;
		    		} elseif(is_file(FF_THEME_DISK_PATH . "/library/plugins/" . $plugin_name . "/libs." . FF_PHP_EXT)) {
						$tmp = include(FF_THEME_DISK_PATH . "/library/plugins/" . $plugin_name . "/libs." . FF_PHP_EXT);
					} elseif(is_file($plugin_path . "/" . $plugin_name . ".observe.js")) {
						$objPlugin = true;
					}
					
					if($tmp) {
						if($plugin_name == $plugin_key)
							$objPlugin = ($is_observe 
								? $tmp[$lib_base]["all"]["js_defs"][$plugin_key]["js_defs"]["observe"]
								: $tmp[$lib_base]["all"]["js_defs"][$plugin_key]
							);
						else
							$objPlugin = ($is_observe
								? $tmp[$lib_base]["all"]["js_defs"]["plugins"]["js_defs"][$plugin_key]["js_defs"]["observe"]
								: $tmp[$lib_base]["all"]["js_defs"]["plugins"]["js_defs"][$plugin_key]
							);
					}						
//print_r($objPlugin);
					if($objPlugin) {
						$res[$plugin_key] = system_get_js_obj($plugin_name, $plugin_key, $type, (bool) $tmp, $objPlugin, $return);
						
					}
		    	}
			}
		}
		//da eliminare tutti i riferimenti alla tabella js. se cerchi FROM js ne trovi
		if($return == "sql") {
			array_unshift($res, "(
									SELECT 
							        " . $db->toSql("ajaxcontent") . " 								AS ID
							        , " . $db->toSql("Ajax Content") . " 							AS name
							        , " . $db->toSql(($type == "Number" ? "1" : "content")) . " 	AS type
							    )");

			$res = "SELECT tbl_src.*
					FROM (
						" . implode(" UNION ", $res) . "
					) AS tbl_src
					[WHERE]
					ORDER BY tbl_src.name";
		}

		return $res;
		
	}
	
	
	function system_get_js_libs($type = null, $return = "sql") {
		$globals = ffGlobals::getInstance("gallery");
		$db = ffDB_Sql::factory();
		$arrSQL = array();

		static $arrPlugins = null;
		if(!$arrPlugins)
			$arrPlugins = glob(FF_THEME_DISK_PATH . "/library/plugins/*");

		if(is_array($arrPlugins) && count($arrPlugins)) {
			foreach($arrPlugins AS $plugin_path) {
		    	if(is_dir($plugin_path)) {
					$tmp = "";		    		
					$objPlugin = null;
					$plugin_name = basename($plugin_path);
					
					$arrPluginName = explode(".", $plugin_name);
					if(count($arrPluginName) == 1) {
						$lib_base = "library";
						$plugin_key = $plugin_name;
					} else {
						$lib_base = $arrPluginName[0];
						unset($arrPluginName[0]);
						$plugin_key = basename(implode(".", $arrPluginName));
					}
					
		    		if(is_file($plugin_path . "/libs." . FF_PHP_EXT)) {
						$tmp = include($plugin_path . "/libs." . FF_PHP_EXT);
					}
					
					if($tmp) {
						if($plugin_name == $plugin_key)
							$objPlugin = $tmp[$lib_base]["all"]["js_defs"][$plugin_key];
						else
							$objPlugin = $tmp[$lib_base]["all"]["js_defs"]["plugins"]["js_defs"][$plugin_key];
					}						
					
					if($objPlugin) {
						$res[$plugin_name] = system_get_js_obj($plugin_name, $plugin_key, $type, (bool) $tmp, $objPlugin, $return);
						
					}
		    	}
			}
		}
		//da eliminare tutti i riferimenti alla tabella js. se cerchi FROM js ne trovi
		if(strpos($return, "sql") !== false) {
			$res = "SELECT tbl_src.*
					FROM (
						" . implode(" UNION ", $res) . "
					) AS tbl_src
					[WHERE]
					ORDER BY tbl_src.name";
		}

		return $res;	
	}
	
	function system_get_js_obj($name, $key, $type, $is_lib, $objPlugin, $return = "sql") {
		$db = ffDB_Sql::factory();
		
		$sql											= "";
		$arrSql 										= array();
		$plugin 										= array();
		$plugin["key"] 									= $key;
		$plugin["name"]									= ucwords(str_replace(".", " " , $plugin["key"]));
		$plugin["type"] 								= $objPlugin["type"];
		$plugin["limit"] 								= array("content", "image", "nolink");
		$plugin["tpl"] 									= "listdiv";
		$plugin["libs"] 								= $is_lib;

		if(!$plugin["type"]) {
			if(strpos($name, "carousel") 	!== false)
				$plugin["type"] 						= "carousel";
			elseif(strpos($name, "slide")	!== false)
				$plugin["type"] 						= "slider";
			elseif(strpos($name, "tree") 	!== false)
				$plugin["type"] 						= "tree";
			elseif(strpos($name, "box") 	!== false)
				$plugin["type"] 						= "viewer";
			elseif(strpos($name, "menu") 	!== false)
				$plugin["type"] 						= "menu";
			elseif(strpos($name, "player") 	!== false)
				$plugin["type"] 						= "player";
		}
		
		switch($plugin["type"]) {
			case "carousel":
				break;
			case "slider":
				$plugin["tpl"]							= "divimg";
				break;
			case "tree":
				$plugin["tpl"] 							= "ulli";
				break;
			case "viewer":
				$plugin["limit"] 						= array("content","image");
				break;
			case "menu":
				$plugin["tpl"] 							= "ulli";
				break;
			case "player":
				$plugin["limit"] 						= array("image");
				break;
			default:
		}

		if($objPlugin["tpl"])
			$plugin["tpl"] 								= $objPlugin["tpl"];
			
		$sql =  "(
					SELECT 
				    " . $db->toSql($plugin["key"]) . " 					AS ID
				    , " . $db->toSql($plugin["name"]) . " 				AS name
				)";
			
		if(!$plugin["limit"] || array_search("content", $plugin["limit"]) !== false) {
			switch($type) {
				case "Number": 
					$plugin_type = "1";
					break;
				case "extended_type":
				default:
					$plugin_type = "content";
			}
					
			$arrSql[] = "(
						SELECT 
				        " . $db->toSql($plugin["key"]) . " 					AS ID
				        , " . $db->toSql($plugin["name"]) . " 				AS name
				        , " . $db->toSql($plugin_type) . " 					AS type
				    )";
		}
		if(!$plugin["limit"] || array_search("image", $plugin["limit"]) !== false) {
			switch($type) {
				case "Number": 
					$plugin_type = "2";
					break;
				case "extended_type":
				default:
					$plugin_type = "image";
			}
			
			$arrSql[] = "(
						SELECT 
				        " . $db->toSql($plugin["key"]) . " 					AS ID
				        , " . $db->toSql($plugin["name"]) . " 				AS name
				        , " . $db->toSql($plugin_type) . " 					AS type
				    )";
		}
		if(!$plugin["limit"] || array_search("nolink", $plugin["limit"]) !== false) {
			switch($type) {
				case "Number": 
					$plugin_type = "0";
					break;
				case "extended_type":
				default:
					$plugin_type = "0";
			}
			
			$arrSql[] = "(
						SELECT 
				        " . $db->toSql($plugin["key"]) . " 					AS ID
				        , " . $db->toSql($plugin["name"]) . " 				AS name
				        , " . $db->toSql($plugin_type) . " 					AS type
				    )";
		}	

		return ($return == "sql"
			? implode(" UNION ", $arrSql)
			: ($return == "sql_distinct"
				? $sql
				: $plugin
			)
		);
	}