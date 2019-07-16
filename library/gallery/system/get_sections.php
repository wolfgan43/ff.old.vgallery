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
define("LAYOUT_TYPE_TABLE_NAME", "layout_type");

function system_get_layers($selective = NULL) {
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    $arrLayer = array();
    $settings_path = $globals->settings_path;

	check_function("get_class_by_grid_system");

	$sSQL = "
	        SELECT  
	            layout_layer.*
	            , layout_layer_path.visible             AS visible
	            , layout_layer_path.path                AS path
                , layout_layer_path.width 				AS width
	            , layout_layer_path.class 				AS class
	            , layout_layer_path.fluid 				AS fluid
	            , layout_layer_path.wrap 				AS wrap
	            
	        FROM layout_layer
	            INNER JOIN layout_layer_path ON layout_layer_path.ID_layout_layer = layout_layer.ID 
	        WHERE 1
	            " . (!$selective
	                    ? "	AND " . $db->toSql($settings_path, "Text") . " LIKE CONCAT(layout_layer_path.path, IF(layout_layer_path.cascading, '%', ''))"
	                    : (is_array($selective)
		                    ? " AND layout_layer.ID IN(" . $db->toSql(implode(",", $selective), "Text", false) . ")"
		                    : " AND layout_layer.name = " . $db->toSql($selective, "Text")
		                )
	            ) . "
	        ORDER BY 
	             `order`
	             , layout_layer.ID
	             , layout_layer_path.ID";
	$db->query($sSQL);
    $arrLayer                                                               = array();
    $recordset                                                              = $db->getRecordset();
    if(is_array($recordset) && count($recordset)) {
        foreach($recordset AS $record) {
			$path 															= $record["path"];
			$layer_jolly 													= substr_count($path, "%");
			//$layer_path 													= preg_replace('/\/+/', '/', str_replace("%", "", $path));
			$layer_path 													= str_replace(array("//", "%"), array("/", ""), $path);
			if(!$layer_path)
				$layer_path 												= "/";

			$ID_layer 														= $record["ID"];
			$layer_visible 													= $record["visible"];
			$layer_relevance 												= ($layer_path == $settings_path
																				? -999
																				: substr_count($settings_path, "/") - substr_count($layer_path, "/")
																			);
			$layer_diff 													= (strlen($settings_path) - strlen($layer_path));
			if(!array_key_exists($ID_layer, $arrLayer)) {
				$arrLayer[$ID_layer] = array(
											"ID"							=> $ID_layer
											, "name" 						=> $record["name"]
											, "show_empty" 					=> $record["show_empty"]
									);
			}
			if(!array_key_exists("relevance", $arrLayer[$ID_layer])
				|| $arrLayer[$ID_layer]["relevance"] > $layer_relevance
				|| ($arrLayer[$ID_layer]["relevance"] == $layer_relevance
					&& $arrLayer[$ID_layer]["diff"] > $layer_diff
				)
				|| ($arrLayer[$ID_layer]["relevance"] == $layer_relevance
					&& $arrLayer[$ID_layer]["diff"] == $layer_diff
					&& $arrLayer[$ID_layer]["jolly"] > $layer_jolly
				)
			) {
				$arrLayer[$ID_layer] 										= get_class_layout_by_grid_system(
																				"layer"
																				, $record["class"]
																				, $record["fluid"]
																				, null
																				, $record["wrap"]
																				, $record["width"]
																				, $arrLayer[$ID_layer]
																			);
				$arrLayer[$ID_layer]["relevance"] 							= $layer_relevance;
				$arrLayer[$ID_layer]["diff"] 								= $layer_diff;
				$arrLayer[$ID_layer]["jolly"] 								= $layer_jolly;
				$arrLayer[$ID_layer]["visible"] 							= $layer_visible;

				//if($layer_relevance == -999)
				//	break;
			}
		};

		$arrLayer = array_filter($arrLayer, function($layer) {
			if($layer["visible"]) {
				return true;
			}
		});
	}

    return $arrLayer;
}

function system_get_sections($selective = NULL, $process_blocks = false) {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    $template = array();
	$template["layers"] 							= array();
	$template["sections"]                           = array();
	$template["stats"]["main_section"] 				= array();

    $settings_path 									= $globals->settings_path;

	check_function("get_class_by_grid_system");

	if(!$selective) {
		$template["layers"] = system_get_layers();

		$layer_set = $db->toSql(implode(",", array_keys($template["layers"])), "Text", false);
	}

	if($globals->page["template"])
    { 
    	if($globals->page["template"]["sections"]["key"])
    		$arrWhere[] = "layout_location.ID IN(" . implode(", ", $globals->page["template"]["sections"]["key"]) . ")";
		
		$arrName = array();
		if(is_array($globals->page["template"]["sections"]["name"]))
			$arrName = $globals->page["template"]["sections"]["name"];
		if(is_array($globals->page["template"]["unknown"]))
			$arrName = $arrName + $globals->page["template"]["unknown"];
		
    	if(count($arrName))
    		$arrWhere[] = "layout_location.name IN('" . implode("', '", array_keys($arrName)) . "')";
    		
    	if(is_array($template["layers"]) && count($template["layers"]))
    		$arrWhere[] = "layout_location.ID_layer IN(" . implode(", ", array_keys($template["layers"])) . ")";
    	
		if(is_array($arrWhere) && count($arrWhere))
			$sSQL_where = " AND (" . implode(" OR ", $arrWhere) . ") ";    

		$sSQL_join = " LEFT JOIN layout_location_path ON layout_location_path.ID_layout_location = layout_location.ID ";
		$sSQL_order = " IF(" . $db->toSql($settings_path, "Text") . " LIKE CONCAT(layout_location_path.path, IF(layout_location_path.cascading, '%', '')), 0, 1) ";
		$is_visible = true;
	} else 
	{
	    $sSQL_join = " INNER JOIN layout_location_path ON layout_location_path.ID_layout_location = layout_location.ID ";
		$sSQL_where = (!$selective
			            ? " AND " . $db->toSql($settings_path, "Text") . " LIKE CONCAT(layout_location_path.path, IF(layout_location_path.cascading, '%', ''))"
		                	. ($layer_set
		                		? " AND layout_location.ID_layer IN(" . $layer_set . ") "
		                		: ""
		                	)
			            : (is_array($selective)
			                ? " AND layout_location.ID IN(" . $db->toSql(implode(",", $selective), "Text", false) . ")"
			                : ($selective === true
			                     ? " AND layout_location.is_main > 0 "
			                     : " AND layout_location.name = " . $db->toSql($selective, "Text")
			                ) 
			            )
		            );
		$sSQL_order = "interface_level";
	}
	
	if($sSQL_where) {
		$sSQL = "
		        SELECT  
		            layout_location.*
	                , layout_location_path.visible          AS visible
	                , layout_location_path.path             AS path
		            , layout_location_path.width 			AS width
		            , layout_location_path.class 			AS class
		            , layout_location_path.default_grid 	AS default_grid
		            , layout_location_path.grid_md 			AS grid_md
		            , layout_location_path.grid_sm 			AS grid_sm
		            , layout_location_path.grid_xs 			AS grid_xs
		            , layout_location_path.fluid 			AS fluid
		            , layout_location_path.wrap 			AS wrap
		        FROM layout_location
		            $sSQL_join
		        WHERE 1
					$sSQL_where
		        ORDER BY 
		            $sSQL_order
		            , layout_location.ID
		            , layout_location_path.ID";
		            
		            //FIELD(layout_location.ID_layer, " . $layer_set . ")
		$db->query($sSQL);
        $recordset                                                              = $db->getRecordset();
        if(is_array($recordset) && count($recordset)) {
            foreach($recordset AS $record) {
				$path 															= $record["path"];
				$section_jolly 													= substr_count($path, "%");
				//$section_path 													= preg_replace('/\/+/', '/', str_replace("%", "", $path));
				$section_path 													= str_replace(array("//", "%"), array("/", ""), $path);
				if(!$section_path)
					$section_path 												= "/";
			
				$ID_layer 														= $record["ID_layer"];
				$ID_section 													= $record["ID"];
				$section_name													= $record["name"];
				$section_is_main 												= $record["is_main"];
				$section_visible 												= $record["visible"];
				$section_relevance 												= ($section_path == $settings_path
																					? -999
																					: substr_count($settings_path, "/") - substr_count($section_path, "/")
																				);
				$section_diff 													= (strlen($settings_path) - strlen($section_path));
				if(!array_key_exists($ID_section, $template["sections"])) {
					if($globals->page["template"]["unknown"][$section_name]) {
						$globals->page["template"]["sections"]["vars"][$section_name] = $ID_section;
						$globals->page["template"]["found"][$section_name] = "sections";
						unset($globals->page["template"]["unknown"][$section_name]);
					}
					$template["sections"][$ID_section] = array(	
																"ID" 			=> $ID_section
																, "name" 		=> $section_name
																, "ID_layer" 	=> $ID_layer
																, "layer"		=> ""
																, "last_update"	=> $record["last_update"]
																, "show_empty" 	=> $record["show_empty"]
																, "is_main" 	=> $section_is_main
																, "blocks"		=> array()
															);
				}
				
				if(!array_key_exists("relevance", $template["sections"][$ID_section]) 
					|| $template["sections"][$ID_section]["relevance"] > $section_relevance
					|| ($template["sections"][$ID_section]["relevance"] == $section_relevance 
						&& $template["sections"][$ID_section]["diff"] > $section_diff 
					)
					|| ($template["sections"][$ID_section]["relevance"] == $section_relevance 
						&& $template["sections"][$ID_section]["diff"] == $section_diff 
						&& $template["sections"][$ID_section]["jolly"] > $section_jolly 
					)
				) {
					$template["sections"][$ID_section] 							= get_class_layout_by_grid_system(
																					"section"
																					, $record["class"]
																					, $record["fluid"]
																					, array(
                                                                                        $record["grid_xs"]
																						, $record["grid_sm"]
																						, $record["grid_md"]
																						, $record["default_grid"]
																					)
																					, $record["wrap"]
																					, $record["width"]
																					, $template["sections"][$ID_section]
																				);
					$template["sections"][$ID_section]["relevance"] 			= $section_relevance;
					$template["sections"][$ID_section]["diff"] 					= $section_diff;
					$template["sections"][$ID_section]["jolly"] 				= $section_jolly;
					$template["sections"][$ID_section]["visible"] 				= $section_visible;
					//if($section_relevance == -999)
					//	break;
				}
				if($selective)
					$arrLayers[$ID_layer] 										= $ID_layer;
				
			}
			
			if($selective)
				$template["layers"] = system_get_layers($arrLayers);

			$template["sections"] = array_filter($template["sections"], function(&$section) use (&$template) {
				if(defined("SKIP_MAIN_CONTENT") && $section["is_main"])
					return false;

				if($section["visible"]) {
					//$template["section_keys"][] 									= $section["ID"];
					if($template["layers"][$section["ID_layer"]])
						$template["layers"][$section["ID_layer"]]["sections"][$section["name"]] 	= $section["ID"];
					
					$section["layer"] 																= $template["layers"][$section["ID_layer"]]["name"];
					if($section["is_main"]) 
            			$template["stats"]["main_section"][]										= $section["ID"];

					return true;
				}
			});				
		}
	}

    if(!$template["stats"]["primary_section"])
        $template["stats"]["primary_section"] = $template["stats"]["main_section"][0];  
        
	$framework_css = Cms::getInstance("frameworkcss")->getFramework();
    if(is_array($framework_css))
        $template["container"]["class"] = $framework_css["class"]["container"];
    else
        $template["container"]["class"] = "container";

    $template["container"]["wrap"] = false;
    $template["container"]["sign"] = "px";
    $template["container"]["width"] = "1024";    
    
    if(Auth::isAdmin())
    	$template["navadmin"] = $template["sections"];

    if($process_blocks)
    	$template = system_get_blocks($template);

    return $template;
}

function system_get_blocks($template, $where = null) {
	if(defined("SKIP_VG_CONTENT"))
		return $template;

    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

	check_function("get_class_by_grid_system");

	$shards 																	= array();
	$query 																		= null;
	$userNID 																	= Auth::get("user")->id;
	$params = array(
		"is_guest" 																=> Auth::isGuest() && $globals->page["cache"] != "guest"
		, "flags" => array(
			"ajax" 																=> false
			, "path" 															=> false
			, "preload"															=> $where["preload"]
			, "xhr"																=> $where["xhr"]
		)
	);

	if(!$where)
		$where 																	= $globals->page["template"]["blocks"];
	elseif($where) {
		if(is_array($where))
			$settings_path = $where["path"];
		else
			$settings_path = $where;
	}

	if($where) {
		//todo: da convertire in id il params. Al momento viene passato lo smart-url della categoria ma non matcha perche si aspetta l'ID
		if($where["anagraph"]) {
			$shards 															= $shards + $where["anagraph"];
			$arrWhere[] 														= "(layout.params IN('" . implode("', '", array_keys($where["anagraph"])) . "')
																					AND layout.value = 'anagraph'
																				)";
		}
		//todo: misteriosamente non torna alcun elemento
		if(is_array($where["vgallery"]) && count($where["vgallery"])) {
			$shards 															= $shards + $where["vgallery"];
			foreach($where["vgallery"] AS $vgallery_path => $vgallery_name) {
				$vgallery[basename($vgallery_name)][] 							= $vgallery_path;
			}
			foreach($vgallery AS $vgallery_name => $vgallery_path) {
				$arrWhere[] 													= "(layout.params IN('" . implode("', '", $vgallery_path) . "')
																					AND layout.value = " . $db->toSql($vgallery_name) . "
																				)";
			}

		}
		if($where["blocks"]["key"])
			$arrWhere[] 														= "layout.ID IN(" . implode(", ", $where["blocks"]["key"]) . ")";

		$arrName 																= array();
		if(is_array($where["blocks"]["name"])) {
			$shards 															= $shards + $where["blocks"]["name"];
			$arrName 															= $where["blocks"]["name"];
		}
		if(is_array($where["unknown"])) {
			$shards 															= $shards + $where["unknown"];
			$arrName 															= $arrName + $where["unknown"];
		}
		if(count($arrName))
			$arrWhere[] 														= "layout.smart_url IN('" . implode("', '", array_keys($arrName)) . "')";

		if(is_array($arrWhere) && count($arrWhere)) {
			$query["group"][] 													= "layout.ID";
			$query["where"]["custom"]											= " (" . implode(" OR ", $arrWhere) . ") ";
		}
	}

	if (!$query && $template["sections"]) {
		if(!$settings_path)
			$settings_path														= $globals->settings_path;

		$section_set 															= $db->toSql(implode(",", array_keys($template["sections"])), "Text", false);

		$query["where"]["location"] 											= "layout.ID_location IN(" . $section_set . ")";
		$query["order"][] 														= "FIELD(layout.ID_location, " . $section_set . ")";
	}

	if($settings_path) {
		$params["flags"]["path"]												= true;
		$params["flags"]["ajax"]												= true;

		$query["join"]["path"] 													= "INNER JOIN layout_path ON layout_path.ID_layout = layout.ID";
		$query["where"]["path"] 												= $db->toSql($settings_path) . " LIKE CONCAT(layout_path.ereg_path, IF(layout_path.cascading, '%', ''))";
	}


	if($query) {
		$block_type_loaded 														= system_get_block_type();

		$query["select"][] 														= "layout.*";
		$query["order"][] 														= "layout.`order`";
		$query["order"][] 														= "layout.ID";

		if($params["flags"]["path"]) {
			$query["select"][] 													= "layout_path.class 			AS block_class";
			$query["select"][] 													= "layout_path.default_grid 	AS block_default_grid";
			$query["select"][] 													= "layout_path.grid_md 			AS block_grid_md";
			$query["select"][] 													= "layout_path.grid_sm 			AS block_grid_sm";
			$query["select"][] 													= "layout_path.grid_xs 			AS block_grid_xs";
			$query["select"][] 													= "layout_path.fluid 			AS block_fluid";
			$query["select"][] 													= "layout_path.wrap 			AS block_wrap";
			$query["select"][] 													= "layout_path.ereg_path		AS path";
			$query["select"][] 													= "layout_path.visible 			AS visible";

			$query["order"][] 													= "layout_path.ID";

		}


		$sSQL = "
		        SELECT " . implode(", ", $query["select"]) . "
		        FROM layout
					" . ($query["join"]
				? " " . implode("", $query["join"])
				: ""
			). "
		        WHERE " . implode(" AND ", $query["where"]). " 
		        " . ($query["group"]
				? "GROUP BY " . implode(", ", $query["group"])
				: ""
			). "
		        ORDER BY 
		        	" . implode(", ", $query["order"]);
		$db->query($sSQL);
        $recordset                                                              = $db->getRecordset();
        if(is_array($recordset) && count($recordset)) {
            $template["blocks"] 												= array();
            //$arrLayoutSettings 													= array();
            foreach($recordset AS $record) {
				$block_prefix 													= "L";
				$ID_block														= $record["ID"];
				$block_name														= $record["smart_url"];
				$ID_block_type													= $record["ID_type"];
				$block_type_smart_url 											= $block_type_loaded["rev"][$ID_block_type];
				$ID_section 													= $record["ID_location"];
				$block_type 													= $block_type_loaded[$block_type_smart_url]["name"]; //$record["type"];
				$block_value 													= $record["value"];
				$block_params 													= $record["params"];
				$block_smart_url												= ($block_name
					? $block_name
					: $block_prefix . $ID_block
				);

				//if($block_type == "ECOMMERCE" && strpos($settings_path, VG_SITE_CART) === 0)
				//	continue;

				if($params["flags"]["path"]) {
					$path 														= $record["path"];
					$block_jolly 												= substr_count($path, "%");
					//$block_path 												= preg_replace('/\/+/', '/', str_replace("%", "", $path));
					$block_path 												= str_replace(array("//", "%"), array("/", ""), $path);
					if(!$block_path)
						$block_path 											= "/";
					$block_visible 												= $record["visible"];
					$block_relevance 											= ($block_path == $settings_path
																					? -999
																					: substr_count($settings_path, "/") - substr_count($block_path, "/")
																				);
					$block_diff 												= (strlen($settings_path) - strlen($block_path));
				}

				if(!array_key_exists($ID_block, $template["blocks"])) {
					if($block_name && $globals->page["template"]["unknown"][$block_name]) {
						$globals->page["template"]["blocks"]["vars"][$block_name] = $ID_block;
						$globals->page["template"]["found"][$block_name] = "blocks";
						unset($globals->page["template"]["unknown"][$block_name]);
					}

					/*$arrLayoutSettings["ID_block"][] 							= $ID_block;
	                $arrLayoutSettings["ID_type"][] 							= $ID_block_type;*/

					$template["blocks"][$ID_block] 								= array(
																					"ID" 						=> $ID_block
																					, "ID_type"					=> $ID_block_type
																					, "ID_section"				=> $ID_section
																					, "prefix" 					=> "L"
																					, "smart_url" 				=> $block_smart_url
																					, "title"					=> $record["name"]
																					, "type_class"				=> ($block_type_loaded[$block_type_smart_url]["class"]
																													? $block_type_loaded[$block_type_smart_url]["class"]
																													: $block_value
																												) //$record["type_class"]
																					, "type_group"				=> $block_type_loaded[$block_type_smart_url]["group"] //$record["type_group"]
																					, "multi_id"				=> $block_type_loaded[$block_type_smart_url]["multi_id"] //$record["multi_id"]
																					, "type"					=> $block_type
																					, "location"				=> $template["sections"][$ID_section]["name"]
																					, "template"				=> $record["template"]
																					, "tpl_path"				=> $block_type_loaded[$block_type_smart_url]["tpl_path"] //$record["tpl_path"]
																					, "value"					=> $block_value
																					, "params"					=> $block_params
																					, "last_update"				=> $record["last_update"]
																					, "frequency"				=> $block_type_loaded[$block_type_smart_url]["frequency"] //$record["frequency"]
																					, "use_in_content"			=> $record["use_in_content"]
																					, "content"					=> ""
																					, "settings"				=> null
																					, "db" 						=> array(
																													"value" => $block_value
																													, "params" => $block_params
																												)
																				);
					if($params["flags"]["ajax"]) {
						$template["blocks"][$ID_block]["ajax"]					= $record["use_ajax"];
						$template["blocks"][$ID_block]["ajax_on_ready"]			= $record["ajax_on_ready"];
						$template["blocks"][$ID_block]["ajax_on_event"]			= $record["ajax_on_event"];
					} else {
						$template["blocks"][$ID_block]["ajax"] 					= false;
					}


                    if($record["js_lib"]) {
                        $arrJsLibs = explode(",", $record["js_lib"]);

                        foreach($arrJsLibs AS $js_name) {
                            if(strpos($js_name, "/") === 0) {
                                $globals->js["link"][pathinfo($js_name, PATHINFO_FILENAME)] = $js_name;
                            } else {
                                $globals->js["library"][$js_name] = true;
                            }
                            //$template["resources"]["js"]["request"][$js_name] = true;
                        }
                    }

                    // Si presume che vengano caricati da file questi
                    if($record["js"]) {
                        if(strpos(trim($record["js"]), "/") === 0) {
                            $arrLibs = explode(",", $record["js"]);
                            foreach($arrLibs AS $lib_name) {
                                if(strpos($lib_name, "/") === 0) {
                                    $globals->js["link"][pathinfo($lib_name, PATHINFO_FILENAME)] = $lib_name;
                                } else {
                                    $globals->js["library"][$lib_name] = true;
                                }
                            }
                        } else {
                            $globals->js["embed"][$block_smart_url] = $record["js"];
                        }
                    }

                    if($record["css"]) {
                        if(strpos(trim($record["css"]), "/") === 0) {
                            $arrLibs = explode(",", $record["css"]);
                            foreach($arrLibs AS $lib_name) {
                                if(strpos($lib_name, "/") === 0) {
                                    $globals->css["link"][pathinfo($lib_name, PATHINFO_FILENAME)] = $lib_name;
                                }
                            }
                        } else {
                            $globals->css["embed"][$block_smart_url] = $record["css"];
                        }
                    }

					if($block_type == "ECOMMERCE"
	                    || (!$params["is_guest"]
	                        && ($block_type == "LOGIN"
                        		||
                        		$block_type == "USER"
	                        )
	                    ) 
	                ) {
						$template["blocks"][$ID_block]["ajax"] = true;
						if(!$template["blocks"][$ID_block]["ajax_on_ready"] || $template["blocks"][$ID_block]["ajax_on_ready"] == "preload")
							$template["blocks"][$ID_block]["ajax_on_ready"] 	= "inview";
						if(!$template["blocks"][$ID_block]["ajax_on_ready"])
							$template["blocks"][$ID_block]["ajax_on_event"] 	= "load fadeIn";
							
		                if($block_type == "ECOMMERCE") {
		                    $frame["sys"]["layouts"] = preg_replace('/[^a-zA-Z0-9]/', '', $ID_block);
		                    //$frame["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
		                    $serial_frame = json_encode($frame);
		
		                    $globals->ecommerce["sid"] 							= set_sid($serial_frame);
		                    $globals->ecommerce["id"] 							= "cart" . $template["sections"][$ID_section]["name"] . $template["blocks"][$ID_block]["prefix"] . $ID_block;
		                    $globals->ecommerce["unic_id"] 						= $template["blocks"][$ID_block]["prefix"] . $ID_block;								
						}
	                }
				}

				if($params["flags"]["path"]) {
					if(!array_key_exists("relevance", $template["blocks"][$ID_block])
						|| $template["blocks"][$ID_block]["relevance"] > $block_relevance
						|| ($template["blocks"][$ID_block]["relevance"] == $block_relevance
							&& $template["blocks"][$ID_block]["diff"] > $block_diff
						)
						|| ($template["blocks"][$ID_block]["relevance"] == $block_relevance
							&& $template["blocks"][$ID_block]["diff"] == $block_diff
							&& $template["blocks"][$ID_block]["jolly"] > $block_jolly
						)
					) {
						$template["blocks"][$ID_block] 							= get_class_layout_by_grid_system($template["blocks"][$ID_block]["type_class"]
																					, $record["block_class"]
																					, $record["block_fluid"]
																					, array(
                                                                                        $record["block_grid_xs"]
																						, $record["block_grid_sm"]
																						, $record["block_grid_md"]
																						, $record["block_default_grid"]
																					)
																					, $record["block_wrap"]
																					, false
																					, $template["blocks"][$ID_block]
																				);
						$template["blocks"][$ID_block]["relevance"] 			= $block_relevance;
						$template["blocks"][$ID_block]["diff"] 					= $block_diff;
						$template["blocks"][$ID_block]["jolly"] 				= $block_jolly;
						$template["blocks"][$ID_block]["visible"] 				= $block_visible;
						$template["blocks"][$ID_block]["db"]["real_path"] 		= stripslash($block_path);
					}
				}
	        }

	        /*if(check_function("get_layout_settings")) {
	            $arrLayoutSettings["data"] = get_layout_settings($arrLayoutSettings["ID_block"], $arrLayoutSettings["ID_type"]);
			}*/

			if($params["flags"]["path"]) {
				$template["blocks"] = array_filter($template["blocks"], function(&$block) use (&$template/*, $arrLayoutSettings*/) {
					if($template["navadmin"])
						$template["navadmin"][$block["ID_section"]]["blocks"][$block["ID"]] 				= $block;

					if($block["visible"]) {
						if(!$block["ajax"]) {
							$template["blocks_by_type"][$block["type"]]["blocks"][$block["smart_url"]] 	= $block;
							if($block["type"] == "PUBLISHING") {
								$arrPublishing = explode("_", $block["db"]["value"]);
								$template["blocks_by_type"][$block["type"]]["keys"][$arrPublishing[0]][] 	= $arrPublishing[1];
							} else {
								$template["blocks_by_type"][$block["type"]]["keys"][$block["db"]["value"]] 	= ($block["db"]["params"]
																												? $block["db"]["params"]
																												: $block["db"]["value"]
																											);
							}
						}
						if(!$template["primary_section"] && $template["sections"][$block["ID_section"]]["is_main"])
							$template["primary_section"] 													= $block["ID_section"];

						$template["sections"][$block["ID_section"]]["blocks"][$block["smart_url"]] 			= $block["ID"];

                        $block["settings"]                                                                  = Cms::getPackage($block["smart_url"]);
                        if(!$block["settings"]) {
                            $block["settings"]                                                              = Cms::getPackage($block["type"]);
                        }
                        /*
						$block["settings"] 																	= (array_key_exists($block["type"] . "-" . $block["ID"], $arrLayoutSettings["data"])
																												? $arrLayoutSettings["data"][$block["type"] . "-" . $block["ID"]]
																												: $arrLayoutSettings["data"][$block["type"] . "-0"]
																											);*/
						return true;
					}
				});
			} else {
				foreach($template["blocks"] AS $ID_block => $block) {
					$template["blocks_by_type"][$block["type"]]["blocks"][$block["smart_url"]] 			= $block;
					if($block["type"] == "PUBLISHING") {
						$arrPublishing = explode("_", $block["db"]["value"]);
						$template["blocks_by_type"][$block["type"]]["keys"][$arrPublishing[0]][]			= $arrPublishing[1];
					} else {
						$template["blocks_by_type"][$block["type"]]["keys"][$block["db"]["value"]] 			= ($block["db"]["params"]
																												? $block["db"]["params"]
																												: $block["db"]["value"]
																											);
					}

                    $template["blocks"][$ID_block]["settings"]                                              = Cms::getPackage($template["blocks"][$ID_block]["smart_url"]);
                    if(!$template["blocks"][$ID_block]["settings"]) {
                        $template["blocks"][$ID_block]["settings"]                                          = Cms::getPackage($template["blocks"][$ID_block]["type"]);
                    }

                    /*$template["blocks"][$ID_block]["settings"] 												= (array_key_exists($block["type"] . "-" . $block["ID"], $arrLayoutSettings["data"])
																												? $arrLayoutSettings["data"][$block["type"] . "-" . $block["ID"]]
																												: $arrLayoutSettings["data"][$block["type"] . "-0"]
																											);*/
				}
			}
	    }
	}
	//$params["flags"]["preload"] = 1;
	if($params["flags"]["preload"] && is_array($template["blocks_by_type"]) && count($template["blocks_by_type"])) {
		$template["buffer"] = array(
			"container" 			=> array()
			, "blocks" 				=> array()
		);

		$params = array(
			"main_content" 			=> false
			, "user_path" 			=> $globals->user_path
			, "settings_path" 		=> $globals->settings_path
			, "search" 				=> $globals->search
			, "navigation" 			=> $globals->navigation
			, "xhr" 				=> $params["flags"]["xhr"]
			, "prefix" 				=> $shards
		);
//$template["main_section"]

		foreach($template["blocks_by_type"] AS $type => $blocks) {
			$callback = (function_exists("system_block_" . $type)
				? "system_block_" . $type
				: "system_block_WIDGET"
			);

			$template["buffer"]["blocks"] = $template["buffer"]["blocks"] + call_user_func_array(
				$callback
				, array(
					$blocks["blocks"]
					, $params
					, $blocks["keys"]
				)
			);
		}
	}

  	return $template;
}
function system_block_parse($layout, $buffer, $xhr = false, $start = false, $error = null) {
	if($error) {
		$res["error"] 		= $error["content"];
		$res["status"] 		= $error["status"];
	}
	if($xhr) {
		$res["target"] 		= "#" .$layout["prefix"] . $layout["ID"];
		$res["html"] 		= $buffer["content"];
	} else {
		$res["pre"] 		= $buffer["pre"];
		$res["content"] 	= $buffer["content"];
		$res["post"] 		= $buffer["post"];
	}

	if($start && DEBUG_PROFILING === true) {
		$res["exTime"] = Debug::stopWatch($start);
	}
	return $res;
}
function system_block_STATIC_PAGE_BY_DB($layouts, $params = array(), $data_storage = null) {
    check_function("process_static_page");

    //todo:  multi blocks by type da togliere ciclo
    foreach($layouts AS $ID_layout => $layout) {
        $start = Debug::startWatch();
        $buffer = process_static_page($layout["type"], $layout["db"]["value"], $params["user_path"], $layout);
        $res[($params["prefix"][$ID_layout]
            ? $params["prefix"][$ID_layout] . "/"
            : ""
        ) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
    }

    return $res;
}
function system_block_STATIC_PAGE_BY_FILE($layouts, $params = array(), $data_storage = null) {
    check_function("process_static_page");

    //todo:  multi blocks by type da togliere ciclo
    foreach($layouts AS $ID_layout => $layout) {
        $start = Debug::startWatch();
        $buffer = process_static_page($layout["type"], $layout["db"]["value"], $params["user_path"], $layout);
        $res[($params["prefix"][$ID_layout]
            ? $params["prefix"][$ID_layout] . "/"
            : ""
        ) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
    }

    return $res;
}

function system_block_GALLERY($layouts, $params = array(), $data_storage = null) {
	check_function("get_file_permission");
	check_function("process_gallery_thumb");
	check_function("process_gallery_view");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$buffer = null;
		$error = null;
		if (strlen($layout["db"]["real_path"]) && $layout["db"]["real_path"] != "/") {
			if (strlen($layout["db"]["params"]) && strpos($layout["db"]["real_path"], $layout["db"]["params"]) === 0) {
				$available_path = substr($layout["db"]["real_path"], strlen($layout["db"]["params"]));
			} else {
				if (strpos($params["settings_path"], stripslash($layout["db"]["real_path"])) === 0) {
					$available_path = substr($params["settings_path"], strlen(stripslash($layout["db"]["real_path"])));
				} else {
					$available_path = $params["settings_path"];
				}
			}
		} else {
			$available_path = $params["settings_path"];
		}

		$real_path = realpath(FF_DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
		if ((!$params["main_content"] || $layout["use_in_content"] == "-1") && $real_path === false && strlen($available_path) && $available_path != "/") {
			do {
				$available_path = ffCommon_dirname($available_path);
				$real_path = realpath(FF_DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
				if ($real_path !== false)
					break;
			} while ($available_path != "/");
		}

		/*if(!$params["main_content"] || (strpos($params["settings_path"], stripslash($layout["db"]["value"]) . $available_path) === 0 && is_dir(FF_DISK_UPDIR . $params["settings_path"]))
		) {
			$valid_gallery_path = true;
		} else {
			$valid_gallery_path = true;
		}*/

		if ($real_path) {
			if (!is_dir($real_path)) {
				$available_path = $layout["db"]["value"];
				$real_path = realpath(FF_DISK_UPDIR . stripslash($layout["db"]["value"]));
			} else {
				if (strpos($available_path, $layout["db"]["value"]) === 0) {
					$available_path = stripslash(substr($available_path, strlen($layout["db"]["value"])));
				} else {
					$available_path = stripslash($layout["db"]["value"]) . stripslash($available_path);
				}
			}

			if ($available_path == "")
				$available_path = "/";

			if (Cms::env("ENABLE_STD_PERMISSION"))
				$file_permission = get_file_permission($available_path, "files", null, true);

			//File permessi Cartella (controllo se l'utente ha diritti di lettura)
			if (check_mod($file_permission, 1, true, Auth::env("AREA_GALLERY_SHOW_MODIFY"))) {
				if (is_dir($real_path)) {
					$rst_file = array();
					$rst_dir = array();
					$arr_real_path = glob($real_path . "/*");
					if (is_array($arr_real_path) && count($arr_real_path)) {
						foreach ($arr_real_path AS $real_file) {
							$file = str_replace(FF_DISK_UPDIR, "", $real_file);
							$description = "";
							if ((is_dir($real_file) /*&& basename($real_file) != ffMedia::STORING_BASE_NAME && basename($real_file) != GALLERY_TPL_PATH*/) || (is_file($real_file) && strpos(basename($real_file), "pdf-conversion") === false) && strpos(basename($real_file), ".") !== 0) {
								if (Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
									$file_permission = get_file_permission($file);
								if (check_mod($file_permission, 1, true, Auth::env("AREA_GALLERY_SHOW_MODIFY"))) {
									$rst_dir[$file]["permission"] = $file_permission;
								}
							}
						}
					}
					$rst_item = array_merge($rst_dir, $rst_file);
					$buffer = process_gallery_thumb($rst_item, $available_path, NULL, $params["user_path"], NULL, $layout);
				} elseif (is_file($real_path)) {
					$buffer = process_gallery_view($available_path, NULL, $params["user_path"], $layout);
				}
				if ($params["page_invalid"] === null) {
					$params["page_invalid"] = false;
				}
			} else {
				$error["content"] = ffTemplate::_get_word_by_code("error_access_denied");
				$error["status"] = "404";
			}
		}

		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start, $error);
	}

	return $res;
}

function system_block_MODULE($layouts, $params = array(), $data_storage = null) {
	$cm = cm::getInstance();

	check_function("set_template_var");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$buffer = null;
		if (isset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
			if (is_array($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
				$cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"]);
				$cm->oPage->tpl[0]->parse("SectWidgetsHeaders", true);

				$buffer["content"] = /*$cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"] .*/
					$cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["html"] /*. $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]*/
				;

				$cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]);
				$cm->oPage->tpl[0]->parse("SectWidgetsFooters", true);
			} else {
				$buffer["content"] = $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])];
			}

			unset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
		}
		if (!strlen($buffer["content"]) && isset($cm->oPage->contents["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
			$buffer["content"] = $cm->oPage->contents["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["data"];
			unset($cm->oPage->contents["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
		}

		if (is_object($buffer["content"])) {
			if (isset($cm->oPage->components_buffer[$buffer["content"]->id])) {
				$buffer["content"] = /*$cm->oPage->components_buffer[$buffer["content"]->id]["headers"] .*/
					$cm->oPage->components_buffer[$buffer["content"]->id]["html"] /*. $cm->oPage->components_buffer[$buffer["content"]->id]["footers"]*/
				;
			} else {
				$buffer["content"] = "";
			}
		}
		if (strlen($buffer["content"])) {
			setJsRequest($layout["settings"]["AREA_MODULE_PLUGIN"]);
			/**
			 * Admin Father Bar
			 */
			if (Auth::env("AREA_MODULES_SHOW_MODIFY")) {
				$admin_menu["admin"]["unic_name"] = $layout["prefix"] . $layout["ID"];
				$admin_menu["admin"]["title"] = $layout["title"] . ": " . $params["user_path"];
				$admin_menu["admin"]["class"] = $layout["type_class"];
				$admin_menu["admin"]["group"] = $layout["type_group"];
				$admin_menu["admin"]["adddir"] = "";
				$admin_menu["admin"]["addnew"] = "";
				$admin_menu["admin"]["modify"] = "";
				$admin_menu["admin"]["delete"] = "";
				$admin_menu["admin"]["extra"] = "";

				$admin_menu["admin"]["ecommerce"] = "";
				if (Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
					$admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
					$admin_menu["admin"]["layout"]["type"] = $layout["type"];
				}
				$admin_menu["admin"]["setting"] = ""; //$layout["type"];
				if (Auth::env("MODULE_SHOW_CONFIG")) {
					$admin_menu["admin"]["module"]["value"] = $layout["db"]["value"];
					$admin_menu["admin"]["module"]["params"] = $layout["db"]["params"];
				}
				if (is_dir(FF_DISK_PATH . "/conf/gallery/modules/" . $layout["db"]["value"] . "/extra"))
					$admin_menu["admin"]["module"]["extra"] = FF_SITE_PATH . VG_SITE_RESTRICTED . "/modules/" . $layout["db"]["value"] . "/extra/" . $layout["db"]["params"];
				else
					$admin_menu["admin"]["module"]["extra"] = "";

				$admin_menu["sys"]["path"] = $params["user_path"];
				$admin_menu["sys"]["type"] = "admin_toolbar";
				$admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
			}

			set_cache_data("M", $layout["db"]["value"] . "-" . $layout["db"]["params"]);
			//$globals->cache["data_blocks"]["M" . "" . "-" . md5($layout["db"]["value"] . "-" . $layout["db"]["params"])] = $layout["db"]["value"] . "-" . $layout["db"]["params"];

			/**
			 * Process Block Header
			 */
			$tpl = null;
			$block["class"]["type"] = $layout["db"]["value"];
			$block["class"]["default"] = $layout["db"]["params"];

			$block = get_template_header($params["user_path"], $admin_menu, $layout, $tpl, $block);

			$buffer["pre"] 			= $block["tpl"]["pre"];
			$buffer["post"] 		= $block["tpl"]["post"];
		}
		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_VIRTUAL_GALLERY($layouts, $params = array(), $data_storage = null)
{
	check_function("vgallery_init");

	//todo:  multi blocks by type da togliere ciclo
	foreach ($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$buffer = vgallery_init($params, $layout, $data_storage);
		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_PUBLISHING($layouts, $params = array(), $data_storage = null) {
	check_function("process_gallery_thumb");
	check_function("process_vgallery_thumb");
	check_function("set_template_var");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$buffer = null;
		$publish = explode("_", $layout["db"]["value"]);
		if (is_array($publish) && count($publish) == 2) {
			$publishing = array();
			$publishing["ID"] = $publish[1];
			$publishing["src"] = $publish[0];

			$source_user_path = $layout["db"]["params"]
				? $layout["db"]["params"]
				: (strlen($layout["db"]["real_path"]) && $layout["db"]["real_path"] != "/"
					? $layout["db"]["real_path"]
					: NULL
				);
			if ($publish[0] == "gallery") {
				$buffer = process_gallery_thumb(NULL, NULL, NULL, $source_user_path, $publishing, $layout);
			} elseif ($publish[0] == "vgallery" || $publish[0] == "anagraph") {
				$buffer = process_vgallery_thumb(
					NULL
					, "publishing"
					, array(
						"source_user_path" => $source_user_path
					, "user_path" => $params["user_path"]
					, "allow_insert" => false
					, "publishing" => $publishing
					)
					, $layout
				);
			}
		}

		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_VGALLERY_MENU($layouts, $params = array(), $data_storage = null) {
	check_function("process_vgallery_menu");

	// @todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$part_virtual_path = explode("/", $layout["db"]["value"]);
		$vgallery_name = $part_virtual_path[1];
		unset($part_virtual_path[0]);
		unset($part_virtual_path[1]);

		$virtual_path = "/" . implode("/", $part_virtual_path);

		$source_user_path = $layout["db"]["params"]
			? $layout["db"]["params"]
			: (strlen($layout["settings"]["AREA_VGALLERY_START_PATH"])
				? $layout["settings"]["AREA_VGALLERY_START_PATH"]
				: $layout["db"]["real_path"]
			);

		if($virtual_path != ffCommon_dirname($virtual_path)) {
			$source_user_path = str_replace($virtual_path, "", $source_user_path);
		}

		$buffer = process_vgallery_menu($virtual_path, $vgallery_name, $source_user_path, $layout);
		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return  $res;
}

function system_block_GALLERY_MENU($layouts, $params = array(), $data_storage = null) {
	check_function("process_gallery_menu");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		if ($layout["settings"]["AREA_DIRECTORIES_SHOW_ONLYHOME"]) {
			$available_path = $layout["db"]["value"];
			$source_user_path = $layout["db"]["params"]
				? $layout["db"]["params"]
				: NULL;
		} else {
			if ($layout["db"]["real_path"] != "/") {
				if (strpos($params["settings_path"], $layout["db"]["real_path"]) === 0) {
					$available_path = substr($params["settings_path"], strlen($layout["db"]["real_path"]));
				} else {
					$available_path = $params["settings_path"];
				}
			} else {
				$available_path = $params["settings_path"];
			}

			$real_path = realpath(FF_DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
			if ($real_path === false && $available_path != "/") {
				do {
					$available_path = ffCommon_dirname($available_path);
					$real_path = realpath(FF_DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
					if ($real_path !== false)
						break;
				} while ($available_path != "/");
			}

			$source_user_path = $layout["db"]["params"]
				? $layout["db"]["params"] . stripslash($available_path)
				: NULL;

			if (!is_dir($real_path)) {
				$available_path = $layout["db"]["value"];
			} else {
				if (strpos($available_path, $layout["db"]["value"]) === 0) {
					$available_path = stripslash(substr($available_path, strlen($layout["db"]["value"])));
				} else {
					$available_path = stripslash($layout["db"]["value"]) . stripslash($available_path);
				}
			}

			if ($available_path == "")
				$available_path = "/";
		}

		$buffer = process_gallery_menu($available_path, $source_user_path, $layout);
		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_STATIC_PAGES_MENU($layouts, $params = array(), $data_storage = null) {
	check_function("process_static_menu");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		if ($layout["db"]["value"] == "/home")
			$layout["db"]["value"] = "/";

		if ($layout["settings"]["AREA_STATIC_FOLLOW_PATH"] && (strlen($layout["db"]["value"]) && strpos($params["settings_path"], $layout["db"]["value"]) === 0)) {
			if ($layout["settings"]["AREA_STATIC_SHOW_ONLYHOME"]) {
				if (strlen(stripslash($layout["db"]["value"])) && strpos($params["settings_path"], $layout["db"]["value"]) === 0) {
					$tmp_ArrPath = explode("/", substr($params["settings_path"], strlen($layout["db"]["value"])));
					$virtual_path = $layout["db"]["value"] . "/" . $tmp_ArrPath[1];
				} else {
					$tmp_ArrPath = explode("/", $params["settings_path"]);
					$virtual_path = "/" . $tmp_ArrPath[1];
				}
			} else {
				$virtual_path = $params["settings_path"];
			}
		} else {
			$virtual_path = $layout["db"]["value"];
		}

		$buffer = process_static_menu($virtual_path, $params["user_path"], null, $layout);
		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_VGALLERY_GROUP($layouts, $params = array(), $data_storage = null) {
	$db = ffDB_Sql::factory();

	check_function("get_vgallery_group");
	check_function("process_vgallery_menu_group");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$buffer = null;
		if($layout["db"]["real_path"] != "/") {
			if($layout["db"]["real_path"] == $params["settings_path"]) {
				$available_path = "";
			} else {
				$available_path = str_replace($layout["db"]["real_path"] . "/", "/", $params["settings_path"]);
			}
		} else
			$available_path = $params["settings_path"];


		//$virtual_path = stripslash($available_path);

		$virtual_path = stripslash($layout["db"]["params"]) . stripslash($available_path);
		if(strlen($virtual_path)) {
			$vgallery_group = get_vgallery_group(basename($params["user_path"]));
			if($vgallery_group) {
				$params["user_path"] = ffCommon_dirname($params["user_path"]);
				$virtual_path = ffCommon_dirname($virtual_path);
			}

			$sSQL = "SELECT vgallery_groups.ID
									, CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
								FROM vgallery_nodes 
									INNER JOIN vgallery_fields ON vgallery_fields.ID_type = vgallery_nodes.ID_type
									INNER JOIN vgallery_groups_fields ON vgallery_groups_fields.ID_fields = vgallery_fields.ID
									INNER JOIN vgallery_groups ON vgallery_groups.ID = vgallery_groups_fields.ID_group
								WHERE 
									vgallery_groups.ID_menu = " . $db->toSql($layout["db"]["value"]) . "
									AND " . $db->toSql($virtual_path) . " LIKE CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name, '%')
								ORDER BY LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) DESC
								LIMIT 1";
			$db->query($sSQL);
            $recordset                                                              = $db->getRecordset();
            if(is_array($recordset) && count($recordset)) {
                $real_path = str_replace($recordset[0]["full_path"], "", $virtual_path);
                $real_user_path = $params["user_path"];
                if(strlen($real_path)) {
					do {
						$real_user_path = ffcommon_dirname($params["user_path"]);
						$real_path = ffcommon_dirname($real_path);
					} while($real_path != "/");
				}

				$buffer = process_vgallery_menu_group($real_user_path, $layout["db"]["value"], null, $layout);
			}
		}

		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_WIDGET($layouts, $params = array(), $data_storage = null) {
	check_function("process_gallery_menu");
	check_function("set_template_var");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		switch ($layout["type"]) {
			case "ECOMMERCE":
				if (check_function("ecommerce_cart_widget"))
					$buffer = ecommerce_cart_widget($params["user_path"], $layout);
				break;
			case "LANGUAGES":
				if (check_function("process_language"))
					$buffer = process_language(LANGUAGE_INSET, $params["user_path"], $layout);
				break;
			case "SEARCH":
				if (check_function("process_omnisearch"))
					$buffer = process_omnisearch($params["user_path"], $layout);
				break;
			case "LOGIN":
				if (check_function("process_login"))
					$buffer = process_login($params["user_path"], $layout);
				break;
			case "ORINAV":
				if (check_function("process_breadcrumb"))
					$buffer = process_breadcrumb($params["user_path"], $params["settings_path"], $layout["db"]["real_path"], $layout);
				break;
			default:
				ffErrorHandler::raise("lost static pages type: [" . $layout["type"] . "]", E_USER_WARNING, NULL, NULL);
		}

		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_COMMENT($layouts, $params = array(), $data_storage = null) {
	$cm = cm::getInstance();

	check_function("process_gallery_menu");
	check_function("set_template_var");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$buffer = null;
		if(Auth::env("AREA_COMMENT_SHOW_MODIFY")) {
			$admin_menu["admin"]["unic_name"] = $layout["prefix"] . $layout["ID"];
			$admin_menu["admin"]["title"] = $layout["title"] . ": " . $params["user_path"];
			$admin_menu["admin"]["class"] = $layout["type_class"];
			$admin_menu["admin"]["group"] = $layout["type_group"];
			$admin_menu["admin"]["adddir"] = "";
			$admin_menu["admin"]["addnew"] = "";
			$admin_menu["admin"]["modify"] = "";
			$admin_menu["admin"]["delete"] = "";
			$admin_menu["admin"]["extra"] = "";
			$admin_menu["admin"]["ecommerce"] = "";
			if(Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
				$admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
				$admin_menu["admin"]["layout"]["type"] = $layout["type"];
			}
			if(Auth::env("AREA_SETTINGS_SHOW_MODIFY")) {
				$admin_menu["admin"]["setting"] = ""; //$layout["type"];
			}
			if(Auth::env("MODULE_SHOW_CONFIG")) {
				$admin_menu["admin"]["module"]["value"] = $layout["db"]["value"];
				$admin_menu["admin"]["module"]["params"] = $layout["db"]["params"];
			}
			$admin_menu["sys"]["path"] = $params["user_path"];
			$admin_menu["sys"]["type"] = "admin_toolbar";
			$admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
		}

		if(isset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
			if(is_array($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
				$cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"]);
				$cm->oPage->tpl[0]->parse("SectWidgetsHeaders", true);

				$buffer["content"] = /*$cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"] .*/ $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["html"] /*. $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]*/;

				$cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]);
				$cm->oPage->tpl[0]->parse("SectWidgetsFooters", true);
			} else {
				$buffer["content"] = $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])];
			}
			unset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
		}
		if(!strlen($buffer["content"]) && isset($cm->oPage->contents["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
			$buffer["content"] = $cm->oPage->contents["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["data"];
			unset($cm->oPage->contents["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
		}

		/**
		 * Process Block Header
		 */
		$tpl = null;
		$block = get_template_header($params["user_path"], $admin_menu, $layout, $tpl);

		$buffer["pre"] 			= $block["tpl"]["pre"];
		$buffer["post"] 		= $block["tpl"]["post"];

		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_USER($layouts, $params = array(), $data_storage = null) {
	check_function("process_user_menu");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		$buffer = process_user_menu(null, null, Cms::env("AREA_SHOW_ECOMMERCE"), $params["user_path"], $layout);
		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_block_FORMS_FRAMEWORK($layouts, $params = array(), $data_storage = null) {
	check_function("process_forms_framework");
	check_function("set_template_var");

	//todo:  multi blocks by type da togliere ciclo
	foreach($layouts AS $ID_layout => $layout) {
		$start = Debug::startWatch();
		if (Auth::env("AREA_FORMS_FRAMEWORK_SHOW_MODIFY")) {
			$admin_menu["admin"]["unic_name"] = $layout["prefix"] . $layout["ID"];
			$admin_menu["admin"]["title"] = $layout["title"] . ": " . $params["user_path"];
			$admin_menu["admin"]["class"] = $layout["type_class"];
			$admin_menu["admin"]["group"] = $layout["type_group"];
			$admin_menu["admin"]["adddir"] = "";
			$admin_menu["admin"]["addnew"] = "";
			$admin_menu["admin"]["modify"] = "";
			$admin_menu["admin"]["delete"] = "";
			$admin_menu["admin"]["extra"] = "";
			$admin_menu["admin"]["ecommerce"] = "";
			if (Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
				$admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
				$admin_menu["admin"]["layout"]["type"] = $layout["type"];
			}
			if (Auth::env("AREA_SETTINGS_SHOW_MODIFY")) {
				$admin_menu["admin"]["setting"] = "";
			}

			$admin_menu["sys"]["path"] = $params["user_path"];
			$admin_menu["sys"]["type"] = "admin_toolbar";
			$admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
		}

		$buffer = process_forms_framework($layout["db"]["value"], $layout["db"]["params"], $params["user_path"], $layout);

		/**
		 * Process Block Header
		 */
		$block = get_template_header($params["user_path"], $admin_menu, $layout);

		$buffer["pre"] 			= $block["tpl"]["pre"];
		$buffer["post"] 		= $block["tpl"]["post"];

		$res[($params["prefix"][$ID_layout]
				? $params["prefix"][$ID_layout] . "/"
				: ""
			) . $ID_layout] = system_block_parse($layout, $buffer, $params["xhr"], $start);
	}

	return $res;
}

function system_get_block_type($name = null)
{
    static $blocktype = null;

    if(!$blocktype) {
        check_function("Filemanager");

        $fs = new Filemanager("php", CM_CACHE_DISK_PATH . "/block-type", "blocktype");

        $blocktype = $fs->read();
        if(!$blocktype) {
            $db = ffDB_Sql::factory();

            $sSQL = "SELECT " . LAYOUT_TYPE_TABLE_NAME . ".*
					FROM " . LAYOUT_TYPE_TABLE_NAME . "
					WHERE 1
					ORDER BY " . LAYOUT_TYPE_TABLE_NAME . ".ID";
            $db->query($sSQL);
            $recordset                                                                          = $db->getRecordset();
            if(is_array($recordset) && count($recordset)) {
                foreach($recordset AS $record) {
                    $layout_type 																= ffCommon_url_rewrite($record["name"]);
                    $smart_url 																	= $record["smart_url"];
                    $group 																		= $record["group"];
                    $allow_creation 															= $record["allow_creation"];

                    $blocktype[$layout_type] = $db->record;
                    $blocktype["rev"][$record["ID"]] = $layout_type;
                    if($smart_url) {
                        $blocktype["smart_url"][$smart_url] = $layout_type;
                        $blocktype["group"][$group][$smart_url] = $layout_type;
                        if($allow_creation)
                            $blocktype["group-creation"][$group][$smart_url] = $layout_type;
                    }

                }

                if(!$blocktype["smart_url"])
                    $blocktype["smart_url"] = array();


                $applets = system_get_block_applets($blocktype);
                $schema["forms-framework"]  = $applets["def"];

                $addons = system_get_block_addons($blocktype);
                $schema["module"] = $addons["def"];

                $blocktype["sql"] = array(
                    "tblsrc" => implode(" UNION ", $applets["tblsrc"] + $addons["tblsrc"])
                , "items" => implode(" UNION ", $applets["items"])
                , "subitems" => implode(" UNION ", $applets["subitems"])
                );

                foreach($schema AS $layout_type => $def)
                {
                    $arrBlockSchema = array_fill_keys(array_keys($def), $layout_type);
                    if(is_array($arrBlockSchema)&& count($arrBlockSchema))
                    {
                        $group = $blocktype[$layout_type]["group"];

                        $blocktype["smart_url"] = $blocktype["smart_url"] + $arrBlockSchema;

                        if (!$blocktype["group"][$group])
                            $blocktype["group"][$group] = array();
                        $blocktype["group"][$group] = $blocktype["group"][$group] + $arrBlockSchema;

                        if ($blocktype[$layout_type]["allow_creation"]) {
                            if (!$blocktype["group-creation"][$group])
                                $blocktype["group-creation"][$group] = array();

                            $blocktype["group-creation"][$group] = $blocktype["group-creation"][$group] + $arrBlockSchema;
                        }
                    }
                    $blocktype[$layout_type]["child"] = $schema[$layout_type];

                }
            }

            $fs->write($blocktype);
        }
    }

    if($name)
        return $blocktype[$name];
    else
        return $blocktype;
}

function system_get_block_addons($block_type) {
    static $schema = null;

    if(!$schema) {
        $db = ffDB_Sql::factory();

        $schema["tblsrc"] = array();

        $arrFile = glob(FF_DISK_PATH . VG_ADDONS_PATH . "/*/schema." . FF_PHP_EXT);
        if(is_array($arrFile) && count($arrFile)) {
            foreach($arrFile AS $real_file) {
                $addon_key = basename(ffcommon_dirname($real_file));

                require($real_file);

                $schema["tblsrc"][$addon_key] .= "
				(
					SELECT 
						" . $db->toSql($addon_key) . " AS nameID
						, " . $db->toSql(ucwords($addon_key)) . " AS name
						, " . LAYOUT_TYPE_TABLE_NAME . ".group AS `group`
						, " . LAYOUT_TYPE_TABLE_NAME . ".class  AS `class`
						, " . LAYOUT_TYPE_TABLE_NAME . ".row_template AS row_template
						, " . LAYOUT_TYPE_TABLE_NAME . ".coloumn_template AS coloumn_template
						, " . LAYOUT_TYPE_TABLE_NAME . ".`priority` AS `priority`
						, " . LAYOUT_TYPE_TABLE_NAME . ".`order`
					FROM
						" . LAYOUT_TYPE_TABLE_NAME . "
					WHERE " . LAYOUT_TYPE_TABLE_NAME . ".ID = " . $db->toSql($block_type["module"]["ID"], "Number") . "
				)";

                $schema["def"][$addon_key] = array(
                    "name" => $addon_key
                , "description" => ffTemplate::_get_word_by_code($addon_key)
                );
            }
        }
    }

    return $schema;
}

function system_get_block_applets($block_type/*, $return = "sql"*/) {
    static $arrApplets = null;

    if(!$arrApplets)
    {
        $db = ffDB_Sql::factory();

        $ff_applets = glob(FF_DISK_PATH . "/applets/*");
        if(is_array($ff_applets) && count($ff_applets)) {
            foreach($ff_applets AS $real_dir) {
                if(is_dir($real_dir) && file_exists($real_dir . "/index." . FF_PHP_EXT)) {
                    $relative_path = str_replace(FF_DISK_PATH, "", $real_dir);
                    /* $sSQL_ff .= " (
                                     SELECT
                                         " . $db->toSql("applet:" . $relative_path, "Text") . " AS nameID
                                         , " . $db->toSql(basename($relative_path), "Text") . " AS name
                                         , " . $st["FORMS_FRAMEWORK"] . " AS type
                                     )
                                     UNION";*/
                    $arrApplets["def"][trim($relative_path, "/")] = array(
                        "name" => basename($relative_path)
                    , "description" => ffCommon_dirname($relative_path)
                    );

                    $arrApplets["items"][$relative_path] = " 
            						(
			                            SELECT 
											" . $db->toSql($relative_path) . " AS nameID
											, " . $db->toSql(basename($relative_path)) . " AS name
											, " . $db->toSql($block_type["forms-framework"]["ID"], "Number") . " AS type
											, " . $db->toSql(basename($relative_path)) . " AS real_name
										FROM
											" . LAYOUT_TYPE_TABLE_NAME . "
										WHERE " . LAYOUT_TYPE_TABLE_NAME . ".ID = " . $db->toSql($block_type["forms-framework"]["ID"], "Number") . "
		                            )";
                }

                if(is_file(FF_DISK_PATH . "/applets/" . basename($relative_path) . "/schema." . FF_PHP_EXT)) {
                    require FF_DISK_PATH . "/applets/" . basename($relative_path) . "/schema." . FF_PHP_EXT;

                    /** @var include $schema */
                    if(is_array($schema) && count($schema)
                        && array_key_exists("applets", $schema)
                        && is_array($schema["applets"]) && count($schema["applets"])
                        && array_key_exists(basename($relative_path), $schema["applets"])
                        && is_array($schema["applets"][basename($relative_path)]) && count($schema["applets"][basename($relative_path)])
                        && array_key_exists("params", $schema["applets"][basename($relative_path)])
                        && is_array($schema["applets"][basename($relative_path)]["params"]) && count($schema["applets"][basename($relative_path)]["params"])
                    ) {
                        foreach($schema["applets"][basename($relative_path)]["params"] AS $applets_params_key => $applets_params_value) {
                            if(array_key_exists("table", $applets_params_value)
                                && strlen($applets_params_value["table"])
                                && array_key_exists("field", $applets_params_value)
                                && strlen($applets_params_value["field"])
                            ) {
                                $arrApplets["subitems"]["/applets/" . basename($relative_path)] = "(
													SELECT 
																CONCAT(" . $db->toSql($applets_params_key . ":") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . ") AS nameID
												                , REPLACE(CONCAT(" . $db->toSql($applets_params_key . ": ") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . "), '-', ' ') AS name
												                , " . $db->toSql($relative_path) . " AS type
													
													FROM " . $applets_params_value["table"] . "
													WHERE 1 
														" . (array_key_exists("where", $applets_params_value) && strlen($applets_params_value["where"])
                                        ?  " AND " . $applets_params_value["where"]
                                        : ""
                                    ) . "
												)";
                            }
                            if(array_key_exists("value", $applets_params_value)
                                && strlen($applets_params_value["value"])
                            ) {
                                if(is_array($applets_params_value["value"]) && count($applets_params_value["value"])) {
                                    foreach($applets_params_value["value"] AS $applets_params_data) {
                                        $arrApplets["subitems"]["/applets/" . basename($relative_path) . "/" . $applets_params_data] = " (
												            SELECT 
												                " . $db->toSql($applets_params_key . ":"  . $applets_params_data) . " AS nameID
												                , " . $db->toSql($applets_params_key . ":"  . $applets_params_data) . " AS name
												                , " . $db->toSql($relative_path) . " AS type
											            )";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $ff_modules = glob(FF_DISK_PATH . "/modules/*");
        if(is_array($ff_modules) && count($ff_modules)) {
            foreach($ff_modules AS $real_module_dir) {
                if(is_dir($real_module_dir)) {
                    $module_name = basename($real_module_dir);

                    $ff_applets = glob(FF_DISK_PATH . "/modules/" . basename($real_module_dir) . "/applets/*");
                    if(is_array($ff_applets) && count($ff_applets)) {
                        foreach($ff_applets AS $real_applet_dir) {
                            if(is_dir($real_applet_dir) && file_exists($real_applet_dir . "/index." . FF_PHP_EXT)) {
                                $relative_path = str_replace(FF_DISK_PATH, "", $real_applet_dir);
                                /*$sSQL_ff .= " (
                                                SELECT
                                                    " . $db->toSql("applet:" . $relative_path, "Text") . " AS nameID
                                                    , " . $db->toSql(basename($real_module_dir) . ": " . basename($relative_path), "Text") . " AS name
                                                    , " . $st["FORMS_FRAMEWORK"] . " AS type
                                                )
                                                UNION";*/

                                $arrApplets["def"][trim($relative_path, "/")] = array(
                                    "name" => basename($relative_path)
                                , "description" => ffCommon_dirname(ffCommon_dirname($relative_path))
                                );

                                $arrApplets["items"][$relative_path] = "
            									(
						                            SELECT 
														" . $db->toSql($relative_path, "Text") . " AS nameID
														, " . $db->toSql(basename($real_module_dir) . ": " . basename($relative_path)) . " AS name
														, " . $db->toSql($block_type["forms-framework"]["ID"], "Number") . " AS type
														, " . $db->toSql(basename($real_module_dir) . "-" . basename($relative_path)) . " AS real_name
													FROM
														" . LAYOUT_TYPE_TABLE_NAME . "
													WHERE " . LAYOUT_TYPE_TABLE_NAME . ".ID = " . $db->toSql($block_type["forms-framework"]["ID"], "Number") . "
					                            )
					                            ";

                                if(is_file(FF_DISK_PATH . "/modules/" . basename($real_module_dir) . "/conf/schema." . FF_PHP_EXT)) {
                                    require FF_DISK_PATH . "/modules/" . basename($real_module_dir) . "/conf/schema." . FF_PHP_EXT;

                                    if(is_array($schema) && count($schema)
                                        && array_key_exists("applets", $schema)
                                        && is_array($schema["applets"]) && count($schema["applets"])
                                        && array_key_exists(basename($relative_path), $schema["applets"])
                                        && is_array($schema["applets"][basename($relative_path)]) && count($schema["applets"][basename($relative_path)])
                                        && array_key_exists("params", $schema["applets"][basename($relative_path)])
                                        && is_array($schema["applets"][basename($relative_path)]["params"]) && count($schema["applets"][basename($relative_path)]["params"])
                                    ) {
                                        foreach($schema["applets"][basename($relative_path)]["params"] AS $applets_params_key => $applets_params_value) {
                                            if(array_key_exists("table", $applets_params_value)
                                                && strlen($applets_params_value["table"])
                                                && array_key_exists("field", $applets_params_value)
                                                && strlen($applets_params_value["field"])
                                            ) {
                                                $arrApplets["subitems"]["/applets/" . basename($relative_path)] = "(
																	SELECT 
																				CONCAT(" . $db->toSql($applets_params_key . ":") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . ") AS nameID
												                                , REPLACE(CONCAT(" . $db->toSql($applets_params_key . ": ") . ", " . $applets_params_value["table"] . "." . $applets_params_value["field"] . "), '-', ' ') AS name
												                                , " . $db->toSql($relative_path) . " AS type
																	
																	FROM " . $applets_params_value["table"] . "
																	WHERE 1 
																		" . (array_key_exists("where", $applets_params_value) && strlen($applets_params_value["where"])
                                                        ?  " AND " . $applets_params_value["where"]
                                                        : ""
                                                    ) . "
																)";
                                            }
                                            if(array_key_exists("value", $applets_params_value)
                                                && strlen($applets_params_value["value"])
                                            ) {
                                                if(is_array($applets_params_value["value"]) && count($applets_params_value["value"])) {
                                                    foreach($applets_params_value["value"] AS $applets_params_data) {
                                                        $arrApplets["subitems"]["/applets/" . basename($relative_path) . "/" . $applets_params_data] = " (
												                            SELECT 
												                                " . $db->toSql($applets_params_key . ":"  . $applets_params_data) . " AS nameID
												                                , " . $db->toSql($applets_params_key . ":"  . $applets_params_data) . " AS name
												                                , " . $db->toSql($relative_path) . " AS type
											                            )";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if(is_array($arrApplets["items"]) && count($arrApplets["items"])) {
            $arrApplets["tblsrc"][$module_name] .= "
				(
					SELECT 
						" . $db->toSql($block_type["forms-framework"]["ID"], "Number") . " AS nameID
						, " . $db->toSql($block_type["forms-framework"]["description"]) . " AS name
						, " . LAYOUT_TYPE_TABLE_NAME . ".group AS `group`
						, " . LAYOUT_TYPE_TABLE_NAME . ".class  AS `class`
						, " . LAYOUT_TYPE_TABLE_NAME . ".row_template AS row_template
						, " . LAYOUT_TYPE_TABLE_NAME . ".coloumn_template AS coloumn_template
						, " . LAYOUT_TYPE_TABLE_NAME . ".`priority` AS `priority`
						, " . LAYOUT_TYPE_TABLE_NAME . ".`order`
					FROM
						" . LAYOUT_TYPE_TABLE_NAME . "
					WHERE " . LAYOUT_TYPE_TABLE_NAME . ".ID = " . $db->toSql($block_type["forms-framework"]["ID"], "Number") . "
				)";



        }
    }
    /*
    switch($return) {
        case "array":
            $res = $arrApplets["def"];
            break;
        case "sql":
        default:
            $res = array(
                "tblsrc" => implode(" UNION ", $arrApplets["tblsrc"])
                , "items" => implode(" UNION ", $arrApplets["items"])
                , "subitems" => implode(" UNION ", $arrApplets["subitems"])
            );
    }*/
    return $arrApplets;
    /*
    $res = array(
                "def" => $arrApplets["def"]
                , "tblsrc" => implode(" UNION ", $arrApplets["tblsrc"])
                , "items" => implode(" UNION ", $arrApplets["items"])
                , "subitems" => implode(" UNION ", $arrApplets["subitems"])
            );

    return $res;*/
}
