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

function system_get_layers($selective = NULL, $settings_path = null) {
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    $arrLayer = array();
    if(!$settings_path)
    	$settings_path = $globals->settings_path;

    check_function("get_class_by_grid_system");
   	
   	
	if($globals->page["template"]) 
    { 
    	if($globals->page["template"]["layers"]["key"])
    		$arrWhere[] = "layout_layer.ID IN(" . implode(", ", $globals->page["template"]["layers"]["key"]) . ")";
		
		$arrName = array();
		if(is_array($globals->page["template"]["layers"]["name"]))
			$arrName = $globals->page["template"]["layers"]["name"];
		if(is_array($globals->page["template"]["unknown"]))
			$arrName = $arrName + $globals->page["template"]["unknown"];
		
    	if(count($arrName))
    		$arrWhere[] = "layout_layer.name IN('" . implode("', '", array_keys($arrName)) . "')";
		if(is_array($arrWhere) && count($arrWhere))
			$sSQL_where = " AND (" . implode(" OR ", $arrWhere) . ") ";    
    
    	$sSQL_join = " LEFT JOIN layout_layer_path ON layout_layer_path.ID_layout_layer = layout_layer.ID ";
		$sSQL_order = " IF(" . $db->toSql($settings_path, "Text") . " LIKE CONCAT(layout_layer_path.path, IF(layout_layer_path.cascading, '%', '')), 0, 1) ";
		$is_visible = true;
	} else 
	{
		$sSQL_join = " INNER JOIN layout_layer_path ON layout_layer_path.ID_layout_layer = layout_layer.ID ";
		$sSQL_where = (!$selective
							? "	AND " . $db->toSql($settings_path, "Text") . " LIKE CONCAT(layout_layer_path.path, IF(layout_layer_path.cascading, '%', ''))"
		                    : (is_array($selective)
			                    ? " AND layout_layer.ID IN(" . $db->toSql(implode(",", $selective), "Text", false) . ")"
			                    : " AND layout_layer.name = " . $db->toSql($selective, "Text")
			                )
				        );
		$sSQL_order = "`order`";

	}
	if($sSQL_where) {    
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
		            $sSQL_join
		        WHERE 1
					$sSQL_where
		        ORDER BY 
		             $sSQL_order
		             , layout_layer.ID
		             , layout_layer_path.ID";
		$db->query($sSQL);	
		if($db->nextRecord()) {
			$arrLayer = array();
			do {
				$path 															= $db->getField("path", "Text", true);
				$layer_jolly 													= substr_count($path, "%");
				//$layer_path 													= preg_replace('/\/+/', '/', str_replace("%", "", $path));
				$layer_path 													= str_replace(array("//", "%"), array("/", ""), $path);
				if(!$layer_path)
					$layer_path 												= "/";
							
				$ID_layer 														= $db->getField("ID", "Number", true);
				$layer_name 													= $db->getField("name", "Text", true);
				$layer_visible 													= $db->getField("visible", "Number", true);
				$layer_relevance 												= ($layer_path == $settings_path
																					? -999
																					: substr_count($settings_path, "/") - substr_count($layer_path, "/")
																				);			
				$layer_diff 													= (strlen($settings_path) - strlen($layer_path));
				if(!array_key_exists($ID_layer, $arrLayer)) {
					if(array_key_exists($layer_name, $globals->page["template"]["unknown"])) {
						$globals->page["template"]["layers"]["vars"][$layer_name] = $ID_layer;
						$globals->page["template"]["found"][$layer_name] = "layers";
						unset($globals->page["template"]["unknown"][$layer_name]);
					}
						
					$arrLayer[$ID_layer] = array(
												"ID"							=> $ID_layer
												, "name" 						=> $layer_name
												, "show_empty" 					=> $db->getField("show_empty", "Number", true)
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
																					, $db->getField("class", "Text", true)
																					, $db->getField("fluid", "Number", true)
																					, null
																					, $db->getField("wrap", "Number", true)
																					, $db->getField("width", "Text", true)
																					, $arrLayer[$ID_layer]
																				);					
					$arrLayer[$ID_layer]["relevance"] 							= $layer_relevance;
					$arrLayer[$ID_layer]["diff"] 								= $layer_diff;
					$arrLayer[$ID_layer]["jolly"] 								= $layer_jolly;
					$arrLayer[$ID_layer]["visible"] 							= $layer_visible;

					//if($layer_relevance == -999)
					//	break;
				}
			} while($db->nextRecord());

			if(!$is_visible)
			{
				$arrLayer = array_filter($arrLayer, function($layer) {
					if($layer["visible"]) {
						return true;
					}
				});			
			}
		}
	}
    return $arrLayer;
}

function system_get_sections($selective = NULL, $settings_path = null, $process_blocks = false) {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    $template = array();
	$template["layers"] 							= array();
	$template["sections"]                           = array();
	$template["stats"]["main_section"] 				= array();

	if(!$settings_path)
    	$settings_path = $globals->settings_path;

	check_function("get_class_by_grid_system");

	if(!$selective) {
		$template["layers"] = system_get_layers(null, $settings_path);
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
		if($db->nextRecord()) {
			do {
				$path 															= $db->getField("path", "Text", true);
				$section_jolly 													= substr_count($path, "%");
				//$section_path 													= preg_replace('/\/+/', '/', str_replace("%", "", $path));
				$section_path 													= str_replace(array("//", "%"), array("/", ""), $path);
				if(!$section_path)
					$section_path 												= "/";
			
				$ID_layer 														= $db->getField("ID_layer", "Number", true);
				$ID_section 													= $db->getField("ID", "Number", true);
				$section_name													= $db->getField("name", "Text", true);
				$section_is_main 												= $db->getField("is_main", "Number", true);
				$section_visible 												= $db->getField("visible", "Number", true);
				$section_relevance 												= ($section_path == $settings_path
																					? -999
																					: substr_count($settings_path, "/") - substr_count($section_path, "/")
																				);
				$section_diff 													= (strlen($settings_path) - strlen($section_path));
				if(!array_key_exists($ID_section, $template["sections"])) {
					if(array_key_exists($section_name, $globals->page["template"]["unknown"])) {
						$globals->page["template"]["sections"]["vars"][$section_name] = $ID_section;
						$globals->page["template"]["found"][$section_name] = "sections";
						unset($globals->page["template"]["unknown"][$section_name]);
					}
					$template["sections"][$ID_section] = array(	
																"ID" 			=> $ID_section
																, "name" 		=> $section_name
																, "ID_layer" 	=> $ID_layer
																, "layer"		=> ""
																, "last_update"	=> $db->getField("last_update", "Number", true)
																, "show_empty" 	=> $db->getField("show_empty", "Number", true)
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
																					, $db->getField("class", "Text", true)
																					, $db->getField("fluid", "Number", true)
																					, array(
																						$db->getField("grid_xs", "Number", true)
																						, $db->getField("grid_sm", "Number", true)
																						, $db->getField("grid_md", "Number", true)
																						, $db->getField("default_grid", "Number", true)
																					)
																					, $db->getField("wrap", "Number", true)
																					, $db->getField("width", "Text", true)
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
				
			} while($db->nextRecord());
			
			if($selective)
				$template["layers"] = system_get_layers($arrLayers, $settings_path);			

			$template["sections"] = array_filter($template["sections"], function(&$section) use (&$template, $is_visible) {
				if(defined("SKIP_MAIN_CONTENT") && $section["is_main"])
					return false;

				if($is_visible || $section["visible"]) {
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
        
	$framework_css = cm_getFrameworkCss();
    if(is_array($framework_css))
        $template["container"]["class"] = $framework_css["class"]["container"];
    else
        $template["container"]["class"] = "container";

    $template["container"]["wrap"] = false;
    $template["container"]["sign"] = "px";
    $template["container"]["width"] = "1024";    
    
    if(AREA_SHOW_NAVBAR_ADMIN)
    	$template["navadmin"] = $template["sections"];

    if($process_blocks)
    	$template = system_get_blocks($template, $settings_path);    
    
    return $template;
}

function system_get_blocks($template, $settings_path = null, $navadmin = AREA_SHOW_NAVBAR_ADMIN) {
	if(defined("SKIP_VG_CONTENT"))
		return $template;

    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    $userNID = get_session("UserNID");
    $is_guest = (!$userNID || $userNID == MOD_SEC_GUEST_USER_ID) && $globals->page["cache"] != "guest"; 
    if(!$settings_path)
    	$settings_path = $globals->settings_path;

    	
    	
    if($globals->page["template"]) 
    { 
    	if($globals->page["template"]["blocks"]["key"])
    		$arrWhere[] = "layout.ID IN(" . implode(", ", $globals->page["template"]["blocks"]["key"]) . ")";
		
		$arrName = array();
		if(is_array($globals->page["template"]["blocks"]["name"]))
			$arrName = $globals->page["template"]["blocks"]["name"];
		if(is_array($globals->page["template"]["unknown"]))
			$arrName = $arrName + $globals->page["template"]["unknown"];
		
    	if(count($arrName))
    		$arrWhere[] 		= "layout.smart_url IN('" . implode("', '", array_keys($arrName)) . "')";

    	if(is_array($template["sections"]) && count($template["sections"]))
    		$arrWhere[] = "layout.ID_location IN(" . implode(", ", array_keys($template["sections"])) . ")";

		if(is_array($arrWhere) && count($arrWhere))
			$sSQL_where 		= " AND (" . implode(" OR ", $arrWhere) . ") ";
		
		$sSQL_join 				= " LEFT JOIN layout_path ON layout_path.ID_layout = layout.ID ";	
		$sSQL_order 			= " IF(" . $db->toSql($settings_path, "Text") . " LIKE CONCAT(layout_path.ereg_path, IF(layout_path.cascading, '%', '')), 0, 1) ";
		$is_visible 			= true;
	} elseif($template["sections"]) {
	    $section_set 			= $db->toSql(implode(",", array_keys($template["sections"])), "Text", false);

		$sSQL_join 				= " INNER JOIN layout_path ON layout_path.ID_layout = layout.ID ";
		$sSQL_where 			= " AND " . $db->toSql($settings_path) . " LIKE CONCAT(layout_path.ereg_path, IF(layout_path.cascading, '%', ''))
									AND layout.ID_location IN(" . $section_set . ") ";
		$sSQL_order 			= " FIELD(layout.ID_location, " . $section_set . ") ";
	}

	if($sSQL_where) {
		$sSQL = "
		        SELECT layout.*
	                    , layout_path.class 												AS block_class
	                    , layout_path.default_grid 											AS block_default_grid
	                    , layout_path.grid_md 												AS block_grid_md
	                    , layout_path.grid_sm 												AS block_grid_sm
	                    , layout_path.grid_xs 												AS block_grid_xs
	                    , layout_path.fluid 												AS block_fluid
	                    , layout_path.wrap 													AS block_wrap
	                    , " . LAYOUT_TYPE_TABLE_NAME . ".name 								AS type
	                    , " . LAYOUT_TYPE_TABLE_NAME . ".description 						AS type_description
	                    , IF(" . LAYOUT_TYPE_TABLE_NAME . ".`class` = ''
	                        , layout.value
	                        , " . LAYOUT_TYPE_TABLE_NAME . ".`class`
	                    ) 	                                                                AS type_class
	                    , " . LAYOUT_TYPE_TABLE_NAME . ".`group` 							AS type_group
	                    , " . LAYOUT_TYPE_TABLE_NAME . ".`multi_id` 						AS multi_id
	                    , " . LAYOUT_TYPE_TABLE_NAME . ".`tpl_path` 						AS tpl_path
			            , " . LAYOUT_TYPE_TABLE_NAME . ".frequency 							AS frequency
			            , layout_path.ereg_path												AS path
			            , layout_path.visible 												AS visible
		        FROM layout
	                INNER JOIN " . LAYOUT_TYPE_TABLE_NAME . " ON " . LAYOUT_TYPE_TABLE_NAME . ".ID = layout.ID_type
	                $sSQL_join 
		        WHERE 1
					$sSQL_where
		        ORDER BY 
	        		$sSQL_order
		            , layout.`order`
	                , layout.ID
		            , layout_path.ID";
		$db->query($sSQL);
	    if($db->nextRecord()) {
    		$template["blocks"] = array();
	        $arrLayoutSettings = array();
	        
			do {
				$path 															= $db->getField("path", "Text", true);
				$block_jolly 													= substr_count($path, "%");
				//$block_path 													= preg_replace('/\/+/', '/', str_replace("%", "", $path));
				$block_path 													= str_replace(array("//", "%"), array("/", ""), $path);
				if(!$block_path)
					$block_path 												= "/";
				
				$block_prefix 													= "L";
				$ID_block														= $db->getField("ID", "Number", true);
				$block_name														= $db->getField("smart_url", "Text", true);
				$ID_block_type													= $db->getField("ID_type", "Number", true);
	            $ID_section 													= $db->getField("ID_location", "Number", true);
	            $block_type 													= $db->getField("type", "Text", true);
				$block_value 													= $db->getField("value", "Text", true);
				$block_params 													= $db->getField("params", "Text", true);
	            $block_visible 													= $db->getField("visible", "Number", true);
				$block_relevance 												= ($block_path == $settings_path
																					? -999
																					: substr_count($settings_path, "/") - substr_count($block_path, "/")
																				);
				$block_diff 													= (strlen($settings_path) - strlen($block_path));
				$block_smart_url												= ($db->getField("smart_url", "Text", true)
																					? $db->getField("smart_url", "Text", true)
																					: $block_prefix . $ID_block
																				);

	            if($block_type == "ECOMMERCE" && strpos($settings_path, VG_SITE_CART) === 0)
	                continue;

				if(!array_key_exists($ID_block, $template["blocks"])) {
					if(array_key_exists($block_name, $globals->page["template"]["unknown"])) {
						$globals->page["template"]["blocks"]["vars"][$block_name] = $ID_block;
						$globals->page["template"]["found"][$block_name] = "blocks";
						unset($globals->page["template"]["unknown"][$block_name]);
					}

					$arrLayoutSettings["ID_block"][] 							= $ID_block;
	                $arrLayoutSettings["ID_type"][] 							= $ID_block_type;			

					$template["blocks"][$ID_block] 								= array(	
																					"ID" 						=> $ID_block
																					, "ID_type"					=> $ID_block_type
																					, "ID_section"				=> $ID_section
																					, "prefix" 					=> $block_prefix
																					, "smart_url" 				=> $block_smart_url
																					, "title"					=> $db->getField("name", "Text", true)
																					, "type_class"				=> $db->getField("type_class", "Text", true)
																					, "type_group"				=> $db->getField("type_group", "Text", true)
																					, "multi_id"				=> $db->getField("multi_id", "Text", true)
																					, "type"					=> $block_type
																					, "location"				=> $template["sections"][$ID_section]["name"]
																					, "template"				=> $db->getField("template", "Text", true)
																					, "template_detail"			=> $db->getField("template_detail", "Text", true)
																					, "tpl_path"				=> $db->getField("tpl_path", "Text", true)
																					, "value"					=> $block_value
																					, "params"					=> $block_params
																					, "last_update"				=> $db->getField("last_update", "Number", true)
																					, "frequency"				=> $db->getField("frequency", "Text", true)
																					, "use_in_content"			=> $db->getField("use_in_content", "Number", true)
																					, "ajax"					=> $db->getField("use_ajax", "Number", true)
																					, "ajax_on_ready"			=> $db->getField("ajax_on_ready", "Text", true)
																					, "ajax_on_event"			=> $db->getField("ajax_on_event", "Text", true)
																					, "content"					=> ""
																					, "settings"				=> null
																					, "db" 						=> array(
																													"value" => $block_value
																													, "params" => $block_params
																												)
																				);
					if($db->getField("js_lib", "Text", true)) {
						$arrJsLibs = explode(",", $db->getField("js_lib", "Text", true));
						foreach($arrJsLibs AS $js_name) {
							$globals->js["library"][$js_name] = true;
							//$template["resources"]["js"]["request"][$js_name] = true;
						}
					}
					// Si presume che vengano caricati da file questi
					if($db->getField("js", "Text", true)) {
						$globals->js["embed"][$block_smart_url] = $db->getField("js", "Text", true);
						//$template["resources"]["js"]["embed"][$block_smart_url] = $db->getField("js", "Text", true);
					}

					if($db->getField("css", "Text", true)) {
						$globals->css["embed"][$block_smart_url] = $db->getField("css", "Text", true);
						//$template["resources"]["css"]["embed"][$block_smart_url] = $db->getField("css", "Text", true);
					}																				
					if($block_type == "ECOMMERCE"
	                    || (!$is_guest 
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
					$template["blocks"][$ID_block] 								= get_class_layout_by_grid_system($template["blocks"][$ID_block]["type_class"]
                    																						, $db->getField("block_class", "Text", true)
                    																						, $db->getField("block_fluid", "Number", true)
                    																						, array(
                    																							$db->getField("block_grid_xs", "Number", true)
                    																							, $db->getField("block_grid_sm", "Number", true)
                    																							, $db->getField("block_grid_md", "Number", true)
                    																							, $db->getField("block_default_grid", "Number", true)
                    																						)
                    																						, $db->getField("block_wrap", "Number", true)
                    																						, false
                    																						, $template["blocks"][$ID_block]
                    																					);
					$template["blocks"][$ID_block]["relevance"] 				= $block_relevance;
					$template["blocks"][$ID_block]["diff"] 						= $block_diff;
					$template["blocks"][$ID_block]["jolly"] 					= $block_jolly;
					$template["blocks"][$ID_block]["visible"] 					= $block_visible;
					$template["blocks"][$ID_block]["db"]["real_path"] 			= stripslash($block_path);
					
					
					switch($block_type) {
						case "VIRTUAL_GALLERY":
							$block_type_converted = "vgallery";
							$block_value_converted = "/" . $block_value . stripslash($block_params);
							break;					
						case "PUBLISHING":
							$block_type_converted = "publishing";
							$block_value_converted = substr($block_value, strrpos($block_value, "_") + 1);
							break;	
						case "STATIC_PAGES_MENU":
							$block_type_converted = "page";
							$block_value_converted = $block_value;
							break;					
						case "TAGS_MENU":
							$block_type_converted = "tag";
							$block_value_converted = $block_value;
							break;	
						default:
							$block_type_converted = $block_type;
							$block_value_converted = $block_value;
					}
					
					$template["blocks_by_type"][$block_type_converted][$ID_block] 		= $block_value_converted;
				}
	        } while($db->nextRecord());

	        if(check_function("get_layout_settings")) {
	            $arrLayoutSettings["data"] = get_layout_settings($arrLayoutSettings["ID_block"], $arrLayoutSettings["ID_type"]);
			}

			$template["blocks"] = array_filter($template["blocks"], function(&$block) use (&$template, $arrLayoutSettings, $is_visible, $navadmin) {
				if($navadmin) {
					if($template["navadmin"][$block["ID_section"]])
						$template["navadmin"][$block["ID_section"]]["blocks"][$block["ID"]] 			= $block;
					else
						$template["navadmin"]["unknown"]["blocks"][$block["ID"]] 						= $block;
				}
				if($is_visible || $block["visible"]) {
					if($template["sections"][$block["ID_section"]])
						$template["sections"][$block["ID_section"]]["blocks"][$block["smart_url"]] 		= $block["ID"];
						
	                if(!$template["stats"]["primary_section"] && $template["sections"][$block["ID_section"]]["is_main"])
	                    $template["stats"]["primary_section"] 											= $block["ID_section"];

					$block["settings"] 																	= (array_key_exists($block["type"] . "-" . $block["ID"], $arrLayoutSettings["data"]) 
																											? $arrLayoutSettings["data"][$block["type"] . "-" . $block["ID"]] 
																											: $arrLayoutSettings["data"][$block["type"] . "-0"] 
																										);	
					return true;
				}
			});	
	    }  
	}
//ffErrorHandler::raise("ASD", E_USER_WARNING, null, get_defined_vars());
  	return $template;
}

function system_get_block_type($name = null)
{
    static $blocktype = null;

    if(!$blocktype) {
        check_function("Filemanager");

        $fs = new Filemanager("php", FF_DISK_PATH . "/cache" . "/block-type" . "." . FF_PHP_EXT, "blocktype");

        $blocktype = $fs->read();
        if(!$blocktype) {
            $db = ffDB_Sql::factory();

            $sSQL = "SELECT " . LAYOUT_TYPE_TABLE_NAME . ".*
					FROM " . LAYOUT_TYPE_TABLE_NAME . "
					WHERE 1
					ORDER BY " . LAYOUT_TYPE_TABLE_NAME . ".ID";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $layout_type 																= ffCommon_url_rewrite($db->getField("name", "Text", true));
                    $smart_url 																	= $db->getField("smart_url", "Text", true);
                    $group 																		= $db->getField("group", "Text", true);
                    $allow_creation 															= $db->getField("allow_creation", "Number", true);

                    $blocktype[$layout_type] = $db->record;
                    $blocktype["rev"][$db->getField("ID", "Number", true)] = $layout_type;
                    if($smart_url) {
                        $blocktype["smart_url"][$smart_url] = $layout_type;
                        $blocktype["group"][$group][$smart_url] = $layout_type;
                        if($allow_creation)
                            $blocktype["group-creation"][$group][$smart_url] = $layout_type;
                    }

                } while($db->nextRecord());

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
