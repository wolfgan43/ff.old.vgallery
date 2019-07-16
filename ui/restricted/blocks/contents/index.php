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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

$db = ffDB_Sql::factory();
 
$simple_interface = false;
if (!(Auth::env("AREA_VGALLERY_SHOW_MODIFY") || Auth::env("AREA_VGALLERY_SHOW_ADDNEW") || Auth::env("AREA_VGALLERY_SHOW_DELETE") || Auth::env("AREA_VGALLERY_SHOW_SEO") || Auth::env("AREA_VGALLERY_SHOW_PERMISSION") || Auth::env("AREA_ECOMMERCE_SHOW_MODIFY"))) {
	$sSQL = "SELECT 
		CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
			FROM users_rel_vgallery
				INNER JOIN vgallery_nodes ON vgallery_nodes.ID = users_rel_vgallery.ID_nodes
				INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
			WHERE users_rel_vgallery.uid = " . $db->tosql(Auth::get("user")->id, "Number") . "
				AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) LIKE '" . $db->tosql($cm->real_path_info, "Text", false) . "%'
				AND users_rel_vgallery.cascading > 0
			ORDER BY vgallery_nodes.`order`, vgallery_nodes.`ID`
			";
	$db->query($sSQL);
	if($db->nextRecord()) {
		use_cache(false);
		$cm->real_path_info = $db->getField("full_path", "Text", true);
		$simple_interface = true;
	} else {
    	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	}
}

check_function("system_ffcomponent_set_title");
check_function("get_vgallery_card");

if(system_ffcomponent_switch_by_path(__DIR__, false)) {
	$disable_dialog = false;
	
	if(isset($_REQUEST["repair"])) {
		$sSQL = "UPDATE vgallery SET
					display_name = REPLACE(name, '-', ' ')
				WHERE display_name = '' AND name <> ''";
		$db->execute($sSQL);

		$sSQL = "SELECT vgallery.*
				FROM vgallery
				WHERE 1";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$arrVgallery[$db->getField("ID", "Number", true)] = "/" . ffCommon_url_rewrite($db->getField("name", "Text", true));
			} while($db->nextRecord());
		}

		if(is_array($arrVgallery) && count($arrVgallery)) {
			foreach($arrVgallery AS $ID_vgallery => $parent) {
				$sSQL = "UPDATE vgallery SET
							name = " . $db->toSql(basename($parent)) . "
						WHERE vgallery.ID = " . $db->toSql($ID_vgallery, "Number");
				$db->execute($sSQL);	

				$sSQL = "SELECT vgallery_nodes.ID 
	            		FROM vgallery_nodes 
	            		WHERE vgallery_nodes.name = " . $db->toSql(basename($parent)) . " 
	            			AND vgallery_nodes.parent = '/'
	            			AND vgallery_nodes.public = 0
	            		ORDER BY IF(vgallery_nodes.ID_vgallery <> " . $ID_vgallery . "
	            			, 9999
	            			, vgallery_nodes.ID
	            		)";
		        $db->query($sSQL);
		        if($db->numRows() > 1) {
			        if($db->nextRecord()) {
		     			while($db->nextRecord()) {
		     				$arrDelNode[] = $db->getField("ID", "Number", true);
		     			};   
					}
				}
				$sSQL = "UPDATE vgallery_nodes SET
							ID_vgallery = " . $db->toSql($ID_vgallery, "Number") . "
						WHERE 
							parent LIKE '" . $db->toSql($parent, "Text", false) . "%'
							AND ID_vgallery <> " . $db->toSql($ID_vgallery, "Number");
				$db->execute($sSQL);
			}

			$sSQL = "UPDATE vgallery_nodes SET
							is_dir = '1'
						WHERE parent = '/'";
			$db->execute($sSQL);
			if(is_array($arrDelNode) && count($arrDelNode)) {
				$sSQL = "DELETE FROM vgallery_nodes WHERE ID IN(" . $db->toSql(implode(", ", $arrDelNode), "Text", true) . ")";
				$db->execute($sSQL);
			}
		}
	}

	if(isset($_REQUEST["__nocache__"])) {
		if(check_function("ecommerce_set_cache_price")) {
			$sSQL = "SELECT ecommerce_settings.* 
					FROM ecommerce_settings
					WHERE 
						(ecommerce_settings.buy_price_gross = 0 
							AND ecommerce_settings.buy_price > 0
						)
						OR
						(ecommerce_settings.basic_price_gross = 0 
							AND ecommerce_settings.basic_price > 0
						)
						OR
						(ecommerce_settings.basic_price_discounted_gross = 0 
							AND ecommerce_settings.basic_price > 0
							AND ecommerce_settings.basic_discount > 0
						)
						OR
						(ecommerce_settings.actual_price_gross = 0 
							AND (ecommerce_settings.actual_price > 0
								OR ecommerce_settings.actual_vat > 0
							)
						)
					";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					ecommerce_set_cache_price(
						$db->getField("ID", "Number", true)
						, $db->getField("buy_price", "Number")
						, $db->getField("basic_price", "Number")
						, $db->getField("decumulation", "Text", true)
						, $db->getField("vat", "Number", true)
						, $db->getField("basic_discount", "Number", true)
					);
				} while($db->nextRecord());
			}
		}
	}

	if(!$simple_interface) {
		$cm->oPage->addContent(null, true, "rel");    
	}
	
	if(strlen($cm->real_path_info)) 
	{
		$arrPathInfo = explode("/", ltrim($cm->real_path_info, "/"));
		$db->query("SELECT vgallery.*
		                        , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
		                    FROM vgallery 
		                        LEFT JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID
                        			AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) = " . $db->tosql("/" . $arrPathInfo[0]) . "
		                    WHERE vgallery.name =" . $db->tosql($arrPathInfo[0]) . " 
	                    		AND vgallery.public = 0");
		if($db->nextRecord()) {
			$ID_vgallery 				= $db->getField("ID", "Number", true);
		    $vgallery_name 				= $db->getField("name", "Text", true);
			$full_path 					= $db->getField("full_path", "Text", true);
		    $limit_level 				= $db->getField("limit_level", "Number", true);
			$show_ID 					= $db->getField("show_ID", "Number", true);
			$show_isbn 					= $db->getField("show_isbn", "Number", true);
		    $enable_ecommerce 			= $db->getField("enable_ecommerce", "Number", true);
		    $enable_ecommerce_all_level	= $db->getField("enable_ecommerce_all_level", "Number", true);
		    
		    $enable_multilang_visible 	= $db->getField("enable_multilang_visible", "Number", true);
		    $force_picture_link 		= $db->getField("force_picture_link", "Number", true);
		    $force_picture_ico_spacer 	= $db->getField("force_picture_ico_spacer", "Number", true);
		    $disable_dialog	 			= $db->getField("disable_dialog_in_edit", "Number", true); 
		    $drag_sort_node_enabled 	= $db->getField("drag_sort_node_enabled", "Number", true); 
		    $drag_sort_dir_enabled 		= $db->getField("drag_sort_dir_enabled", "Number", true); 
		    $show_owner_in_grid 		= $db->getField("show_owner_in_grid", "Number", true); 
		    $enable_tag 				= $db->getField("enable_tag", "Number", true);
			$limit_type 				= $db->getField("limit_type", "Text", true);
			
			$enable_multi_cat 			= $db->getField("enable_multi_cat", "Number", true);
			$enable_place 				= $db->getField("enable_place", "Number", true);
			$enable_priority 			= $db->getField("enable_priority", "Text", true);
					
			$orderby 					= $db->getField("back_orderby", "Text", true);
			$show_filteraz 				= $db->getField("back_filteraz", "Number", true);

			if($full_path != "/" . $vgallery_name) {
				$sSQL = "INSERT INTO `vgallery_nodes` 
				            (   `ID` 
				                , `ID_vgallery` 
				                , `name` 
				                , `order` 
				                , `parent` 
				                , `ID_type`
				                , `is_dir` 
				                , `last_update` 
				            )
				        VALUES 
				            (
				                NULL 
				                , " . $db->toSql($ID_vgallery) . "
				                , " . $db->toSql(ffCommon_url_rewrite($vgallery_name)) . "
				                , '0'
				                , " . $db->toSql("/") . "
				                , (SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.name = " . $db->toSql(new ffData("Directory")) . ")
				                , '1'
				                , " . $db->toSql(time(), "Number") . "
				            )";
				$db->execute($sSQL);
                $full_path = "/" . ffCommon_url_rewrite($vgallery_name);
                if(check_function("refresh_cache")) {
		            refresh_cache("V", $db->getInsertID(true), "insert", $full_path);
				}
			}

		    if((count(explode("/", $full_path)))  <= $limit_level) {
		        $allow_insert = true;
		    } else {
		        $allow_insert = false;
		    }
		    
		    $allow_insert_nodes = true;
		    $vgallery_title = ucwords(str_replace("-", " ", basename($cm->real_path_info)));

		    $sSQL = "SELECT vgallery_type.*
    				FROM vgallery_type 
    				WHERE " . (strlen($limit_type) ? " vgallery_type.ID IN (" . $db->tosql($limit_type, "Text", false) . ") " : " 1 ") . "
    				ORDER BY vgallery_type.ID";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
    			do {
    				if($db->getField("is_dir_default", "Number", true))
    					$arrAllowedType["dir"][] = $db->getField("name", "Number", true);
					else    		
						$arrAllowedType["node"][] = $db->getField("name", "Number", true);
				} while($db->nextRecord());
			}
		} else {
		    $allow_insert = false;
		    $allow_insert_nodes = false;
		}

		if(!$simple_interface && $allow_insert 
			&& 
				(
					(strpos($cm->path_info, VG_WS_ECOMMERCE) === 0 
						&& Auth::env("AREA_ECOMMERCE_SHOW_MODIFY") && Auth::env("AREA_ECOMMERCE_SETTINGS_SHOW_MODIFY")
					)
					||
					(strpos($cm->path_info, VG_WS_RESTRICTED) === 0
						&& (Auth::env("AREA_VGALLERY_DIR_SHOW_MODIFY") || Auth::env("AREA_VGALLERY_DIR_SHOW_DELETE"))
					)
					||
					(strpos($cm->path_info, VG_WS_RESTRICTED) !== 0 && strpos($cm->path_info, VG_WS_ECOMMERCE) !== 0 
					
					)
				)
		) {
			$allow_dir = true;
		} else {
			$allow_dir = false;
		}

		/**
		* Vgallery Fields
		*/	
		$arrSQL = array();
		
		$grid_node_id = "VGalleryNodes";
		if($enable_place && (
			$_REQUEST[$grid_node_id . "_ID_state_src"] 
			|| $_REQUEST[$grid_node_id . "_ID_region_src"] 
			|| $_REQUEST[$grid_node_id . "_ID_province_src"] 
			|| $_REQUEST[$grid_node_id . "_ID_city_src"]
		)) {
			$arrSQL["node"]["inner"]["place"] = FF_SUPPORT_PREFIX . "city ON " . FF_SUPPORT_PREFIX . "city.ID = vgallery_nodes.ID_place";
		}		
		
		if(isset($_REQUEST[$grid_node_id . "_searchall_src"])) {
			$search_term_node_sql = str_replace(array(" ", "-", "_", "*"), "%", $_REQUEST[$grid_node_id . "_searchall_src"]);
		}

		$grid_dir_id = "VGalleryDir";
		if(isset($_REQUEST[$grid_node_id . "_searchall_src"])) {
			$search_term_dir_sql = str_replace(array(" ", "-", "_", "*"), "%", $_REQUEST[$grid_dir_id . "_searchall_src"]);
		}
		
		$sSQL = "SELECT DISTINCT
		            vgallery_fields.ID 
		            , vgallery_fields.name AS name
		            , vgallery_fields_data_type.name AS data_type
		            , vgallery_fields.data_source
		            , vgallery_fields.data_limit
		            , extended_type.name AS extended_type
		            , extended_type.ff_name AS ff_extended_type
		            , vgallery_fields.ID_type AS ID_type
		            , vgallery_fields.disable_multilang AS disable_multilang
		            , vgallery_type.is_dir_default AS is_dir
		        FROM vgallery_fields
		            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
		            INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = vgallery_fields.ID_data_type
		            INNER JOIN extended_type on extended_type.ID = vgallery_fields.ID_extended_type
		        WHERE
	        		" . (strlen($limit_type) ? " vgallery_type.ID IN (" . $db->tosql($limit_type, "Text", false) . ") " : " 1 ") . "
		            AND vgallery_fields.enable_in_grid > 0 
		        ORDER BY vgallery_fields.enable_in_grid, vgallery_fields.`order_thumb`";
		$db->query($sSQL);
		if($db->nextRecord()) {
		    $arrFormField = array();
		    do {
	    		$key = ffCommon_url_rewrite($db->getField("name", "Text", true));
				$tmpField = array(
	    				"ID" 								=> $db->getField("ID", "Number", true)
	    				, "name"							=> $key
	    				, "extended_type"					=> $db->getField("extended_type", "Text", true)
	    				, "ff_extended_type"				=> $db->getField("ff_extended_type", "Text", true)
	    				, "data_type"						=> $db->getField("data_type", "Text", true)
	    				, "data_source"						=> $db->getField("data_source", "Text", true)
	    				, "data_limit"						=> $db->getField("data_limit", "Text", true)
	    				, "disable_multilang"				=> $db->getField("disable_multilang", "Text", true)
	    			);

	    		$is_dir = $db->getField("is_dir", "Number", true);
				if($_REQUEST["XHR_COMPONENT"]) {
	    			$sql_type = ($_REQUEST["XHR_COMPONENT"] == $grid_dir_id
	    				? "dir"
	    				: "node"
	    			);
				} else {
					$sql_type = $is_dir 
						? "dir" 
						: "node";
				}
	    			
				if($tmpField["data_type"] == "table.alt") {
					if(!$tmpField["data_limit"] || $tmpField["data_limit"] == "null") 
						$data_limit = "`name`";
					elseif(strpos($tmpField["data_limit"], ",") !== false) 
						$data_limit = " CONCAT(`" . str_replace(",", "`,`", $tmpField["data_limit"]) . "`) ";
					else
						$data_limit = "`" . $tmpField["data_limit"] . "`";
						
		            if($tmpField["data_source"] == "vgallery_nodes") {
	            		switch(trim($data_limit, "`")) {
	            			case "tags":
								$enable_tag = false;
								$data_limit = "`keywords`";
	            			
	            				break;
	            			case "owner":
								$show_owner_in_grid = false;
								$data_limit = "''";
								$tmpField["name"] = "anagraph";
	            				break;
	            			default:
	            		}

		                $arrSQL[$sql_type]["field"][$tmpField["name"]] = $data_limit . " AS `" . $db->tosql($tmpField["name"], "Text", false) . "`";
		            } else {
	            		$arrFormField["sql"]["table.alt"][$tmpField["data_source"]][$tmpField["name"]] = array(
	            			"data_limit" => $tmpField["data_limit"]
	            			, "ID_lang" => ($tmp_field["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID)
	            		);

						/**
						* Search table.alt
						*/
						if($search_term_node_sql) {
	        	 			$arrSQL["node"]["inner"]["search_" . $tmpField["data_source"]] = " `" . $tmpField["data_source"] . "` AS `vgallery_search_" . $tmpField["data_source"] ."` ON vgallery_search_alt.ID_nodes = vgallery_nodes.ID ";
	        	 			$arrSQL["node"]["search"]["search_" . $tmpField["name"]] = (is_array($tmpField["data_limit"]) 
																		? "CONCAT(`vgallery_search_" . $tmpField["data_source"] ."`.`" . implode("`, `vgallery_search_" . $tmpField["data_source"] ."`.`", $tmpField["data_limit"]) . "`)"
																		: (strlen($tmpField["data_limit"]) 
																			? "`vgallery_search_" . $tmpField["data_source"] ."`.`" . $tmpField["data_limit"] . "`"
																			: "`vgallery_search_" . $tmpField["data_source"] ."`.`name`"
																		)
																	) . " LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%' ";
						}
						if($search_term_dir_sql) {
	        	 			$arrSQL["dir"]["inner"]["search_" . $tmpField["data_source"]] = " `" . $tmpField["data_source"] . "` AS `vgallery_search_" . $tmpField["data_source"] ."` ON vgallery_search_alt.ID_nodes = vgallery_nodes.ID ";
	        	 			$arrSQL["dir"]["search"]["search_" . $tmpField["name"]] = (is_array($tmpField["data_limit"]) 
																		? "CONCAT(`vgallery_search_" . $tmpField["data_source"] ."`.`" . implode("`, `vgallery_search_" . $tmpField["data_source"] ."`.`", $tmpField["data_limit"]) . "`)"
																		: (strlen($tmpField["data_limit"]) 
																			? "`vgallery_search_" . $tmpField["data_source"] ."`.`" . $tmpField["data_limit"] . "`"
																			: "`vgallery_search_" . $tmpField["data_source"] ."`.`name`"
																		)
																	) . " LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%' ";
						}	            	

						/**
						* Order
						*/
	            		if($_REQUEST[$grid_node_id . "_order"] == $tmpField["name"]) {
							$field_sql = (is_array($tmpField["data_limit"]) 
											? "CONCAT(vgallery_order.`" . implode("`, vgallery_order.`", $tmpField["data_limit"]) . "`)"
											: (strlen($tmpField["data_limit"]) 
												? "vgallery_order.`" . $tmpField["data_limit"] . "`"
												: "vgallery_order.`name`"
											)
										);

							$arrSQL[$sql_type]["inner"]["order"] = " " . $tmpField["data_source"] . " AS vgallery_order ON vgallery_order.ID_nodes = vgallery_nodes.ID ";
	            		
							$field_sql = "( SELECT " . (is_array($tmpField["data_limit"]) 
																	? "CONCAT('" . implode("','", $tmpField["data_limit"]) . "')"
																	: (strlen($tmpField["data_limit"]) 
																		? $tmpField["data_limit"] 
																		: "name"
																	)
																) . " 
						        FROM
						            " . $tmpField["data_source"] . "
						        WHERE
						            " . $tmpField["data_source"] . ".ID_nodes = vgallery_nodes.ID)";
						} else {
							$field_sql = " '' ";
						}	            	
	            		$arrSQL[$sql_type]["field"][$tmpField["name"]] = $field_sql . " AS `" . $db->tosql($tmpField["name"], "Text", false) . "`";
		            }
		        } else {
		            $arrFormField["sql"]["default"][$tmpField["ID"]] = array(
	            		"name" => $tmpField["name"]
	            		, "field_name" => ($tmpField["data_type"] == "relationship"
	            			? "description_text"
	            			: "description"
	            		)
	            		, "ID_lang" => ($tmp_field["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID)
		            );

					/**
					* Search Rel
					*/
	        		if($search_term_node_sql) {
	        	 		$arrSQL["node"]["inner"]["search_rel"] = " vgallery_rel_nodes_fields AS vgallery_search_rel ON vgallery_search_rel.ID_nodes = vgallery_nodes.ID ";
	        	 		$arrSQL["node"]["search"]["search_rel"] = " vgallery_search_rel.`description` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	        	 														OR vgallery_search_rel.`description_text` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'";
					}
					if($search_term_dir_sql) {
	        	 		$arrSQL["dir"]["inner"]["search_rel"] = " vgallery_rel_nodes_fields AS vgallery_search_rel ON vgallery_search_rel.ID_nodes = vgallery_nodes.ID ";
	        	 		$arrSQL["dir"]["search"]["search_rel"] = " vgallery_search_rel.`description` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
	        	 														OR vgallery_search_rel.`description_text` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'";
					}

					/**
					* Order
					*/
		            if($_REQUEST[$grid_node_id . "_order"] == $tmpField["name"]) {
						$field_sql = " vgallery_order.`" . $arrFormField["sql"]["default"][$tmpField["ID"]]["field_name"] . "`";

						$arrSQL[$sql_type]["inner"]["order"] = " vgallery_rel_nodes_fields AS vgallery_order ON vgallery_order.ID_nodes = vgallery_nodes.ID 
																AND vgallery_order.ID_fields = " . $db->toSql($tmpField["ID"], "Number") . "
																AND vgallery_order.ID_lang = " . $db->toSql($arrFormField["sql"]["default"][$tmpField["ID"]]["ID_lang"], "Number");
					} else {
						$field_sql = " '' ";
					}

					$arrSQL[$sql_type]["field"][$tmpField["name"]] = $field_sql . " AS `" . $db->tosql($tmpField["name"], "Text", false) . "`";
		        }
		        
	    		if($is_dir) {
	    			$arrFormField["dir"][$key] = $tmpField;
	    			if(Cms::env("AREA_SHOW_ECOMMERCE") && $enable_ecommerce && $enable_ecommerce_all_level) {
	    				$arrFormField["node"][$key] = $tmpField;
	    			}
				} else {
					$arrFormField["node"][$key] = $tmpField;
				}
		    } while($db->nextRecord());
		}
		
		/**
		* Search General
		*/
		if($search_term_node_sql) {
			if(LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID) {
	            $arrSQL["node"]["search"]["default"] = " vgallery_nodes.`ID` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
            											OR vgallery_nodes.`meta_title` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	                                                    OR vgallery_nodes.`meta_title_alt` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	                                                    OR vgallery_nodes.`meta_description` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	                                                    OR vgallery_nodes.`keywords` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	                                                    OR vgallery_nodes.`name` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	                                                    OR vgallery_nodes.`parent` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	                                                    OR vgallery_nodes.`permalink` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'";

				$arrSQL["node"]["order"]["search"] = "	MATCH(meta_title_alt) AGAINST (" . $db->toSql($search_term_node_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_node_sql) . ", meta_title_alt) = 1, 0, 1)
														, MATCH(meta_title) AGAINST (" . $db->toSql($search_term_node_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_node_sql) . ", meta_title) = 1, 0, 1)
														, MATCH(meta_description) AGAINST (" . $db->toSql($search_term_node_sql) . ") DESC";                                                    
	        } else {
			    $arrSQL["node"]["inner"]["search_lang"] = " vgallery_nodes_rel_languages AS vgallery_search_lang ON vgallery_search_lang.ID_nodes = vgallery_nodes.ID 
	                                                            AND vgallery_search_lang.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
			    $arrSQL["node"]["search"]["search_lang"] = " vgallery_nodes.`ID` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
		    											OR vgallery_search_lang.`meta_title` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	        											OR vgallery_search_lang.`meta_title_alt` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	        											OR vgallery_search_lang.`meta_description` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	        											OR vgallery_search_lang.`keywords` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'
	        											OR vgallery_search_lang.`permalink` LIKE '%" . $db->toSql($search_term_node_sql, "Text", false) . "%'";

				$arrSQL["node"]["order"]["search"] = "	MATCH(vgallery_search_lang.meta_title_alt) AGAINST (" . $db->toSql($search_term_node_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_node_sql) . ", vgallery_search_lang.meta_title_alt) = 1, 0, 1)
														, MATCH(vgallery_search_lang.meta_title) AGAINST (" . $db->toSql($search_term_node_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_node_sql) . ", vgallery_search_lang.meta_title) = 1, 0, 1)
														, MATCH(vgallery_search_lang.meta_description) AGAINST (" . $db->toSql($search_term_node_sql) . ") DESC";                                                    

			}
			
			$arrSQL["node"]["where"]["search"] = " (" . implode(" OR ", $arrSQL["node"]["search"]) . ") ";
			//$arrSQL["dir"]["where"]["search"] = $arrSQL["node"]["where"]["search"];
			
			/*$arrSQL["dir"]["order"]["search"] = "
					    MATCH(meta_title) AGAINST (" . $db->toSql($search_term_node_sql). ") DESC
						, MATCH(meta_description) AGAINST (" . $db->toSql($search_term_node_sql) . ") DESC";*/
		}	
			
		if($search_term_dir_sql) {
			if(LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID) {
            	$arrSQL["dir"]["search"]["default"] = " vgallery_nodes.`ID` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
            											OR vgallery_nodes.`meta_title` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
                                                   		OR vgallery_nodes.`meta_title_alt` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    	OR vgallery_nodes.`meta_description` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
                                                   	 	OR vgallery_nodes.`keywords` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    	OR vgallery_nodes.`name` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
                                                   	 	OR vgallery_nodes.`parent` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
                                                   	 	OR vgallery_nodes.`permalink` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'";

				$arrSQL["dir"]["order"]["search"] = "	MATCH(meta_title_alt) AGAINST (" . $db->toSql($search_term_dir_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_dir_sql) . ", meta_title_alt) = 1, 0, 1)
														, MATCH(meta_title) AGAINST (" . $db->toSql($search_term_dir_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_dir_sql) . ", meta_title) = 1, 0, 1)
														, MATCH(meta_description) AGAINST (" . $db->toSql($search_term_dir_sql) . ") DESC";                                                    
        	} else {
		    	$arrSQL["dir"]["inner"]["search_lang"] = " vgallery_nodes_rel_languages AS vgallery_search_lang ON vgallery_search_lang.ID_nodes = vgallery_nodes.ID 
                                                            AND vgallery_search_lang.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
		    	$arrSQL["dir"]["search"]["search_lang"] = " vgallery_nodes.`ID` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
		    												OR vgallery_search_lang.`meta_title` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
	        													OR vgallery_search_lang.`meta_title_alt` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
	        												OR vgallery_search_lang.`meta_description` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
	        												OR vgallery_search_lang.`keywords` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'
	        												OR vgallery_search_lang.`permalink` LIKE '%" . $db->toSql($search_term_dir_sql, "Text", false) . "%'";

				$arrSQL["dir"]["order"]["search"] = "	MATCH(vgallery_search_lang.meta_title_alt) AGAINST (" . $db->toSql($search_term_dir_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_dir_sql) . ", vgallery_search_lang.meta_title_alt) = 1, 0, 1)
														, MATCH(vgallery_search_lang.meta_title) AGAINST (" . $db->toSql($search_term_dir_sql). ") DESC
														, IF(LOCATE(" . $db->toSql($search_term_dir_sql) . ", vgallery_search_lang.meta_title) = 1, 0, 1)
														, MATCH(vgallery_search_lang.meta_description) AGAINST (" . $db->toSql($search_term_dir_sql) . ") DESC";                                                    

			}
		
			$arrSQL["dir"]["where"]["search"] = " (" . implode(" OR ", $arrSQL["dir"]["search"]) . ") ";
		}
		
		$arrOrder_node_SQL 										= array();
		$arrOrder_dir_SQL 										= array();
		if($drag_sort_node_enabled)
			$arrOrder_node_SQL[] 								= "vgallery_nodes.`order`";

		if($drag_sort_dir_enabled)
			$arrOrder_dir_SQL[] 								= "vgallery_nodes.`order`";

		switch($orderby) {
			case "frontend": 
				$arrOrder_node_SQL[] 							= "vgallery_nodes.priority DESC";
				break;
			case "title":
				break;
			default:
				$arrOrder_node_SQL[] 							= "vgallery_nodes.last_update DESC";
			
		}
		
		$arrOrder_node_SQL[] 									= "vgallery_nodes.meta_title_alt";
		$arrOrder_dir_SQL[] 									= "full_path";	
	
		if($show_owner_in_grid)
			$arrSQL[$sql_type]["field"]["owner"] 				= "'' AS anagraph";

		if($simple_interface)
			$arrSQL[$sql_type]["where"]["owner"] 				= "vgallery_nodes.owner = " . $db->toSql(Auth::get("user")->id, "Number");

		$arrSQL["node"]["where"]["is_dir"] 						= "NOT(vgallery_nodes.is_dir > 0)";
		
		$arrSQL["dir"]["where"]["is_dir"] 						= "(vgallery_nodes.is_dir > 0)";

		
		
		/***
		* Ecommerce
		*/
		if(!$simple_interface && strpos($cm->path_info, VG_WS_ECOMMERCE) === 0) 
		{ 
			check_function("ecommerce_product_process");

		    $ecommerce_data =  ecommerce_product_process($arrSQL, $full_path);
			$arrSQL = $ecommerce_data["sql"];
			
			$cm->oPage->addContent(ecommerce_ffGrid_product_total($ecommerce_data["location"], $ecommerce_data["manage_full_path"])); 

			if($ecommerce_data["content"])
				$cm->oPage->addContent($ecommerce_data["content"], null, "filter");
		}

		/**
		* Process Vgallery Nodes
		*/
		$oGrid_node = ffGrid::factory($cm->oPage);
		$oGrid_node->ajax_addnew = !$disable_dialog;
		$oGrid_node->ajax_edit = !$disable_dialog;
		$oGrid_node->ajax_delete = true;
		$oGrid_node->ajax_search = true;
		$oGrid_node->id = $grid_node_id;
		$oGrid_node->search_simple_field_options["src_having"] = false;
		$oGrid_node->source_SQL = (is_array($arrSQL["node"]["union"]["pre"]) && count($arrSQL["node"]["union"]["pre"])
	                            		? "(" . implode(") UNION (", $arrSQL["node"]["union"]["pre"]) . ") UNION"
	                            		: ""
		                        ) . "
		                        (SELECT DISTINCT
		                            vgallery_nodes.*
		                            , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
		                            , " . ($limit_level > 1
	                            			? ($cm->real_path_info
					                                ? " REPLACE(SUBSTRING(vgallery_nodes.parent, LOCATE(CONCAT('/', vgallery.name), vgallery_nodes.parent) + LENGTH(vgallery.name) + 2), '/', ' " . ffTemplate::_get_word_by_code("path_sep") . " ') "
					                                : " CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) "
					                        )
					                        : " '' "
				                    ) . " AS display_path
		                            , vgallery.name AS vgallery_name
		                            " . (is_array($arrSQL["node"]["field"]) && count($arrSQL["node"]["field"])
	                            		? ", " . implode(", ", $arrSQL["node"]["field"])
	                            		: ""
		                            ) . "
		                        FROM 
		                            vgallery_nodes
		                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
		                            " . (is_array($arrSQL["node"]["inner"]) && count($arrSQL["node"]["inner"])
	                            		? " INNER JOIN " . implode(" INNER JOIN ", $arrSQL["node"]["inner"])
	                            		: ""
		                            ) . "
		                            " . (is_array($arrSQL["node"]["left"]) && count($arrSQL["node"]["left"])
	                            		? " LEFT JOIN " . implode(" LEFT JOIN ", $arrSQL["node"]["left"])
	                            		: ""
		                            ) . "
		                        WHERE
						            (vgallery_nodes.parent = " . $db->toSql($cm->real_path_info)  . " 
						                OR vgallery_nodes.parent LIKE '" . $db->toSql($cm->real_path_info . "/", "Text", false)  . "%'
						            )
		                            AND vgallery_nodes.name <> ''
									" . (is_array($arrSQL["node"]["where"]) && count($arrSQL["node"]["where"])
	                            			? " AND " . implode(" AND ", $arrSQL["node"]["where"])
	                            			: ""
			                        ) . "
		                        [AND]
		                        [WHERE]
								GROUP BY vgallery_nodes.ID 
								[HAVING]
								" . (is_array($arrSQL["node"]["order"]) && count($arrSQL["node"]["order"])
	                            		? " ORDER BY " . implode(", ", $arrSQL["node"]["order"]) . " [COLON] "
	                            		: ""
			                    ) . "
		                        [ORDER]
		                        )"
		                        . (is_array($arrSQL["node"]["union"]["post"]) && count($arrSQL["node"]["union"]["post"])
			                            ? " UNION (" . implode(") UNION (", $arrSQL["node"]["union"]["post"]) . ")"
			                            : ""
			                    );
		
		$oGrid_node->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "[parent_VALUEPATH]/[name_VALUE]";
		$oGrid_node->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/add";
		$oGrid_node->order_default = "ID";
		$oGrid_node->use_alpha = $show_filteraz;
		$oGrid_node->alpha_field = "meta_title_alt";		
		$oGrid_node->record_id = $grid_node_id . "Modify";
		$oGrid_node->resources[] = $oGrid_node->record_id;
		$oGrid_node->resources[] = "EcStockModify";
		$oGrid_node->resources[] = "EcAddDataModify";
		$oGrid_node->user_vars["force_picture_link"] = $force_picture_link;
		$oGrid_node->user_vars["force_picture_ico_spacer"] = $force_picture_ico_spacer;
		$oGrid_node->user_vars["sql_field"] = $arrFormField["sql"];
		$oGrid_node->user_vars["show_ID"] = $show_ID;
		$oGrid_node->user_vars["show_isbn"] = $show_isbn;
		
		$oGrid_node->setWidthDialog("huge");
		
		/**
		* Title
		*/
		system_ffcomponent_set_title(
			$vgallery_title
			, true
			, false
			, false
			, $oGrid_node
		);

		if($drag_sort_node_enabled) {
			$oGrid_node->widget_deps[] = array(
		        "name" => "labelsort"
		        , "options" => array(
		              &$oGrid_node
		            , array(
		                "resource_id" => "vgallery_nodes"
		                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
		            )
		        )
		    );

		    $oGrid_node->widget_deps[] = array(
		        "name" => "dragsort"
		        , "options" => array(
		              &$oGrid_node
		            , array(
		                "resource_id" => "vgallery_nodes"
		                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
		            )
		            , "ID"
		        )
		    );
		}

		$oGrid_node->addEvent("on_before_parse_row", "VGalleryNodesModify_on_before_parse_row");
		$oGrid_node->addEvent("on_loaded_data", "VGalleryNodesModify_on_loaded_data");

		if($simple_interface) {
			$oGrid_node->use_search = false;
			$oGrid_node->record_url = $cm->oPage->site_path . VG_SITE_VGALLERY . "/modify" . $cm->real_path_info;
			$oGrid_node->display_edit_bt = false;
			$oGrid_node->display_edit_url = $allow_insert_nodes;
			$oGrid_node->display_new = $allow_insert_nodes;
			$oGrid_node->display_delete_bt = $allow_insert_nodes;
		} else {
			$oGrid_node->use_search = true;
			$oGrid_node->display_edit_bt = false;
			$oGrid_node->display_edit_url = Auth::env("AREA_VGALLERY_SHOW_MODIFY") && $allow_insert_nodes;
			$oGrid_node->display_new = Auth::env("AREA_VGALLERY_SHOW_ADDNEW") && $allow_insert_nodes;
			$oGrid_node->display_delete_bt = Auth::env("AREA_VGALLERY_SHOW_DELETE") && $allow_insert_nodes;

			if(check_function("system_ffgrid_process_customize_field_button")) {
				system_ffgrid_process_customize_field_button($oGrid_node, "vgallery_node_fields", array("real_path_info" => $cm->real_path_info
																							, "path_info" => $cm->path_info));
			}
			if($limit_type) {
				$sSQL = "SELECT vgallery_type.ID
							, vgallery_type.name 
						FROM vgallery_type
						WHERE vgallery_type.ID IN(" . $limit_type . ")
							AND vgallery_type.is_dir_default = 0";
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$oButton = ffButton::factory($cm->oPage);
						$oButton->ajax = $oGrid_node->record_id;
						$oButton->id = "structure";
						$oButton->label = ffTemplate::_get_word_by_code("vgallery_structure") . " " . $db->getField("name", "Text", true);
					    $oButton->action_type = "gotourl";
					    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/structure?keys[ID]=" . $db->getField("ID", "Number", true);
					    $oButton->aspect = "link";
						$oGrid_node->addActionButtonHeader($oButton);			
					} while($db->nextRecord());
				}
			}
		}
		 
		// Ricerca
		if(!$enable_multi_cat) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "parent";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_display_path");
			$oField->widget = "actex";
			$oField->actex_update_from_db = true;
			$oField->source_SQL = "SELECT DISTINCT CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)
			                        , vgallery_nodes.meta_title_alt
			                        FROM vgallery_nodes
			                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
			                        WHERE vgallery_nodes.is_dir > 0  
			                        " . ($ID_vgallery > 0
			                            ? " AND vgallery.ID = " . $db->toSql($ID_vgallery, "Number") . "
			                                AND NOT(vgallery_nodes.name = vgallery.name
			                                    AND vgallery_nodes.parent = '/')
			                            "
			                            : ""
			                        ) . "
			                        ORDER BY meta_title_alt";
			$oField->encode_entities = false;
			$oGrid_node->addSearchField($oField);
		}

		if($enable_priority) {
			$oField = ffField::factory($cm->oPage);
	        $oField->id = "priority";
	        $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_priority");
	        $oField->base_type = "Number";
	        $oField->extended_type = "Selection";
	        $oField->multi_select_noone = true;
	        $oField->multi_select_noone_label = ffTemplate::_get_word_by_code("default");
			$oField->multi_select_noone_val = new ffData(0, "Number");
			
	        $arrPriorityDefault = array();
		    $arrPriorityDefault[1] = array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_priority_bottom")));
	        $arrPriorityDefault[2] = array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_very_low")));
	        $arrPriorityDefault[3] = array(new ffData("3", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_low")));
	        $arrPriorityDefault[4] = array(new ffData("4", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_normal")));
	        $arrPriorityDefault[5] = array(new ffData("5", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_hight")));
	        $arrPriorityDefault[6] = array(new ffData("6", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_very_hight")));
	        $arrPriorityDefault[7] = array(new ffData("7", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_top")));
	        
	        if($enable_priority == "-1") {
	            $oField->multi_pairs = $arrPriorityDefault;
			} else {
				$arrPriority = explode(",", $enable_priority);
				foreach($arrPriority AS $priority) {
					$oField->multi_pairs[] = $arrPriorityDefault[$priority];
				}
			}
			$oGrid_node->addSearchField($oField);	
		}
			
		if($enable_place) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_state";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_state");
			$oField->widget = "actex";
			$oField->actex_update_from_db = true;
			//$oField->actex_autocomp = true;
			$oField->actex_child = array("ID_region", "ID_province", "ID_place");
			$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
									, CONCAT(" . FF_SUPPORT_PREFIX . "state.name, ' (', COUNT(vgallery_nodes.ID), ')')
								FROM " . FF_SUPPORT_PREFIX . "state
									INNER JOIN " . FF_SUPPORT_PREFIX . "city ON " . FF_SUPPORT_PREFIX . "city.ID_state = " . FF_SUPPORT_PREFIX . "state.ID	
									INNER JOIN vgallery_nodes ON vgallery_nodes.ID_place = " . FF_SUPPORT_PREFIX . "city.ID
										AND vgallery_nodes.ID_vgallery = " . $db->toSql($ID_vgallery, "Number") . "
								GROUP BY " . FF_SUPPORT_PREFIX . "state.ID
								ORDER BY " . FF_SUPPORT_PREFIX . "state.name";
			$oField->encode_entities = false;
			$oGrid_node->addSearchField($oField);	
			
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_region";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_region");
			$oField->widget = "actex";
			$oField->actex_update_from_db = true;
			$oField->actex_father = "ID_state";
			$oField->actex_related_field = FF_SUPPORT_PREFIX . "region.ID_state";
			$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "region.ID
									, CONCAT(" . FF_SUPPORT_PREFIX . "region.name, ' (', COUNT(vgallery_nodes.ID), ')')
									, " . FF_SUPPORT_PREFIX . "region.ID_state
								FROM " . FF_SUPPORT_PREFIX . "region
									INNER JOIN " . FF_SUPPORT_PREFIX . "city ON " . FF_SUPPORT_PREFIX . "city.ID_region = " . FF_SUPPORT_PREFIX . "region.ID	
									INNER JOIN vgallery_nodes ON vgallery_nodes.ID_place = " . FF_SUPPORT_PREFIX . "city.ID
										AND vgallery_nodes.ID_vgallery = " . $db->toSql($ID_vgallery, "Number") . "
								[WHERE]
								GROUP BY " . FF_SUPPORT_PREFIX . "region.ID
								ORDER BY " . FF_SUPPORT_PREFIX . "region.name";
			$oField->encode_entities = false;
			$oField->setWidthComponent(6);
			$oGrid_node->addSearchField($oField);	

			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_province";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_province");
			$oField->widget = "actex";
			$oField->actex_update_from_db = true;
			$oField->actex_father = "ID_state";
			//$oField->actex_child = "ID_place";
			$oField->actex_related_field = FF_SUPPORT_PREFIX . "province.ID_state";
			$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "province.ID
									, CONCAT(" . FF_SUPPORT_PREFIX . "province.name, ' (', COUNT(vgallery_nodes.ID), ')')
									, " . FF_SUPPORT_PREFIX . "province.ID_state
								FROM " . FF_SUPPORT_PREFIX . "province
									INNER JOIN " . FF_SUPPORT_PREFIX . "city ON " . FF_SUPPORT_PREFIX . "city.ID_province = " . FF_SUPPORT_PREFIX . "province.ID	
									INNER JOIN vgallery_nodes ON vgallery_nodes.ID_place = " . FF_SUPPORT_PREFIX . "city.ID
										AND vgallery_nodes.ID_vgallery = " . $db->toSql($ID_vgallery, "Number") . "
								[WHERE]
								GROUP BY " . FF_SUPPORT_PREFIX . "province.ID
								ORDER BY " . FF_SUPPORT_PREFIX . "province.name";
			$oField->encode_entities = false;
			$oField->setWidthComponent(6);
			$oGrid_node->addSearchField($oField);
			
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_place";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_city");
			$oField->widget = "actex";
			$oField->actex_update_from_db = true;
			$oField->actex_autocomp = true;
			$oField->actex_father = "ID_state";
			$oField->actex_related_field = FF_SUPPORT_PREFIX . "city.ID_state";
			$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "city.ID
									, CONCAT(" . FF_SUPPORT_PREFIX . "city.name, ' (', COUNT(vgallery_nodes.ID), ')')
									, " . FF_SUPPORT_PREFIX . "city.ID_province
								FROM " . FF_SUPPORT_PREFIX . "city
									INNER JOIN vgallery_nodes ON vgallery_nodes.ID_place = " . FF_SUPPORT_PREFIX . "city.ID
										AND vgallery_nodes.ID_vgallery = " . $db->toSql($ID_vgallery, "Number") . "
								[WHERE]
								GROUP BY " . FF_SUPPORT_PREFIX . "city.ID
								ORDER BY " . FF_SUPPORT_PREFIX . "city.name";
			$oField->encode_entities = false;
			$oGrid_node->addSearchField($oField);		
		}		
		
		// Chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oField->order_SQL = implode(", ", $arrOrder_node_SQL);
		$oGrid_node->addKeyField($oField);
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "card";
		$oField->container_class = "card";
		$oField->display_label = false;	
		$oField->data_type = "";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_card");
		$oField->encode_entities = false;
		$oGrid_node->addContent($oField, false);

		if($show_ID && !$simple_interface) {
			$oField = ffField::factory($cm->oPage);
		    $oField->id = "ID";
		    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_ID");
		    $oGrid_node->addContent($oField, true, "last");
		}
			
		$oField = ffField::factory($cm->oPage);
		$oField->id = "meta_title";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_name");
		$oField->display = false;
		$oGrid_node->addContent($oField, true, "last");    

		$oField = ffField::factory($cm->oPage);
		$oField->id = "display_path";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_display_path");
		$oField->display = false;
		$oField->encode_entities = false;
		$oGrid_node->addContent($oField, true, "last");	  

		if($show_isbn) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "isbn";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_isbn");
			$oField->display = false;
			$oField->encode_entities = false;
			$oGrid_node->addContent($oField, true, "last");	 	
		}	
		
	    $oField = ffField::factory($cm->oPage);
		$oField->id = "published_at";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_published_at");
		$oField->display = false;
		$oField->base_type = "Timestamp";
	    $oField->extended_type = "Date";
	    $oField->app_type = "Date";
		$oGrid_node->addContent($oField, true, "last");  	
				
		if(is_array($arrFormField["node"]) && count($arrFormField["node"])) {
			$enable_tag = false;
		    
		    foreach($arrFormField["node"] AS $field_key => $field_value) {
			    $field_name = $field_value["name"];

			    $oField = ffField::factory($cm->oPage);
		        if($field_value["data_type"] == "data" 
			        || $field_value["data_type"] == "selection"
			        || $field_value["data_type"] == "relationship"
		            || $field_value["data_type"] == "table.alt"
		        ) 
					$oField->data_type = "db";
				else
					$oField->data_type = "";

			    $oField->id = $field_name;
			    $oField->class = strtolower($field_value["extended_type"]) . " " . $field_name;
			    $oField->label = ffTemplate::_get_word_by_code($field_name);
			    if($field_value["extended_type"] == "Image"
	    			|| $field_value["extended_type"] == "Upload"
	    			|| $field_value["extended_type"] == "UploadImage"
			    ) {
	    			$oField->control_type = "picture_no_link";
			        $oField->extended_type = "File";
			        $oField->file_full_path = true;
			        $oField->file_storing_path = null;
			        $oField->file_show_delete = false;

			        $oField->file_saved_view_url = $cm->oPage->site_path . constant("CM_SHOWFILES") . "/[_FILENAME_]";
					if($force_picture_ico_spacer) {
			            $oField->file_saved_preview_url = $cm->oPage->site_path . constant("CM_SHOWFILES") . "/" . FF_MAIN_THEME . "/images/spacer.gif";
					} else {
					    $oField->file_saved_preview_url = $cm->oPage->site_path . constant("CM_SHOWFILES") . "/thumb[parent_VALUEPATH]/[name_VALUE]/[_FILENAME_]";
					}	        
			    }
			    $oField->base_type = $field_value["ff_extended_type"];
			    $oField->encode_entities = false;
			    $oField->user_vars = $arrFormField[$field_key];
			    $oGrid_node->addContent($oField); 
		    } 
		}  

		if($show_owner_in_grid && !$simple_interface) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "anagraph";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_anagraph");
			$oGrid_node->addContent($oField);
		}
		if($enable_tag && !$simple_interface) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "keywords";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_keywords");
			$oGrid_node->addContent($oField);
		}	
		
		if(strpos($cm->path_info, VG_WS_ECOMMERCE) === 0) {
			ecommerce_ffGrid_product($oGrid_node, "node");
		} else {
			if(AREA_SEO_SHOW_MODIFY) {
		        $oButton = ffButton::factory($cm->oPage);
		        $oButton->id = "seo";
				$oButton->ajax = ($disable_dialog ? false : $oGrid_node->record_id);
				$oButton->action_type = "gotourl";
				$oButton->url = $cm->oPage->site_path . "/admin/seo?key=[ID_VALUE]&type=vgallery";
		        $oButton->aspect = "link";
				$oButton->label = ffTemplate::_get_word_by_code("seo");
				$oButton->display_label = false;
		        $oGrid_node->addGridButton($oButton);
		    }
		    if(1 || Cms::env("ENABLE_STD_PERMISSION")) {
		        $oButton = ffButton::factory($cm->oPage);
		        $oButton->id = "permissions"; 
				$oButton->ajax = ($disable_dialog ? false : $oGrid_node->record_id);
				$oButton->action_type = "gotourl";
				$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/permission?[KEYS]";
		        $oButton->aspect = "link";
				$oButton->label = ffTemplate::_get_word_by_code("permissions");
				$oButton->display_label = false;
		        $oGrid_node->addGridButton($oButton, "last");
		    }
		} 
		
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "clone"; 
		$oButton->ajax = true;
		$oButton->action_type = "gotourl";
		$oButton->frmAction = "clone";
		$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]";
		$oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("vgallery_clone");
		$oButton->display_label = false;
		$oGrid_node->addGridButton($oButton);
		
		if(Auth::env("AREA_VGALLERY_SHOW_VISIBLE")) {
		    $oButton = ffButton::factory($cm->oPage);
		    $oButton->id = "visible";
		    $oButton->ajax = true;
		    $oButton->action_type = "gotourl";
		    $oButton->frmAction = "setvisible";
		    $oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]";
		    $oButton->aspect = "link";
		    $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
		    $oButton->display_label = false;
		    $oGrid_node->addGridButton($oButton, "last");
		} 

		if(!$allow_dir) {
			$cm->oPage->addContent($oGrid_node); 		
			
		} else {
			$cm->oPage->addContent($oGrid_node, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_nodes_title")));			

			/**
			* Process Vgallery Dir
			*/
		    $oGrid_dir = ffGrid::factory($cm->oPage);
			$oGrid_dir->ajax_addnew = !$disable_dialog;
			$oGrid_dir->ajax_delete = true;
			$oGrid_dir->ajax_search = true;
		    $oGrid_dir->id = $grid_dir_id;
		    $oGrid_dir->source_SQL = (is_array($arrSQL["dir"]["union"]["pre"]) && count($arrSQL["dir"]["union"]["pre"])
	                            			? "(" . implode(") UNION (", $arrSQL["dir"]["union"]["pre"]) . ") UNION"
	                            			: ""
				                    ) . "
				                    (SELECT DISTINCT 
		                                vgallery_nodes.*
		                                , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
		                                , CONCAT('/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)) AS display_path
		                                , vgallery.name AS vgallery_name
		                                " . (is_array($arrSQL["dir"]["field"]) && count($arrSQL["dir"]["field"])
	                            			? ", " . implode(", ", $arrSQL["dir"]["field"])
	                            			: ""
	                            		) . "
		                            FROM 
		                                vgallery_nodes
		                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
			                            " . (is_array($arrSQL["dir"]["inner"]) && count($arrSQL["dir"]["inner"])
	                            			? " INNER JOIN " . implode(" INNER JOIN ", $arrSQL["dir"]["inner"])
	                            			: ""
			                            ) . "
			                            " . (is_array($arrSQL["dir"]["left"]) && count($arrSQL["dir"]["left"])
	                            			? " LEFT JOIN " . implode(" LEFT JOIN ", $arrSQL["dir"]["left"])
	                            			: ""
			                            ) . "
		                            WHERE 
										vgallery_nodes.parent LIKE '" . $db->toSql($cm->real_path_info, "Text", false)  . "%'
							            AND vgallery.name = " . $db->toSql($vgallery_name)  . "
			                            AND vgallery_nodes.name <> ''
										" . (is_array($arrSQL["dir"]["where"]) && count($arrSQL["dir"]["where"])
	                            				? " AND " . implode(" AND ", $arrSQL["dir"]["where"])
	                            				: ""
				                        ) . "
		                            [AND]
		                            [WHERE]
		                            [HAVING]
		                            " . (is_array($arrSQL["node"]["order"]) && count($arrSQL["node"]["order"])
	                            			? " ORDER BY " . implode(", ", $arrSQL["node"]["order"]) . " [COLON] "
	                            			: ""
				                    ) . "
		                            [ORDER] 
		                            )"
			                        . (is_array($arrSQL["dir"]["union"]["post"]) && count($arrSQL["dir"]["union"]["post"])
				                            ? " UNION (" . implode(") UNION (", $arrSQL["dir"]["union"]["post"]) . ")"
				                            : ""
				                    );
			$oGrid_dir->force_no_field_params = true;
		    $oGrid_dir->order_default = "ID";
		    $oGrid_dir->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "[parent_VALUEPATH]/[name_VALUE]";
		    $oGrid_dir->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "[parent_VALUEPATH]/[name_VALUE]";
		    $oGrid_dir->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/add?dir";
		    $oGrid_dir->record_id = $grid_dir_id . "Modify";
		    $oGrid_dir->resources[] = $oGrid_dir->record_id;
		    $oGrid_dir->use_search = true;
		    $oGrid_dir->addEvent("on_before_parse_row", "VGalleryDirModify_on_before_parse_row");
		    $oGrid_dir->user_vars["sql_field"] = $arrFormField["sql"];

			if($drag_sort_dir_enabled) {
				$oGrid_dir->widget_deps[] = array(
			        "name" => "labelsort"
			        , "options" => array(
			              &$oGrid_dir
			            , array(
			                "resource_id" => "vgallery_dir"
			                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
			            )
			        )
			    );

			    $oGrid_dir->widget_deps[] = array(
			        "name" => "dragsort"
			        , "options" => array(
			              &$oGrid_dir
			            , array(
			                "resource_id" => "vgallery_dir"
			                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
			            )
			            , "ID"
			        )
			    );
			}

		    $oGrid_dir->display_edit_bt = false;
		    $oGrid_dir->display_edit_url = Auth::env("AREA_VGALLERY_DIR_SHOW_MODIFY");
		    $oGrid_dir->display_delete_bt = Auth::env("AREA_VGALLERY_DIR_SHOW_DELETE");
		    $oGrid_dir->display_new = Auth::env("AREA_VGALLERY_DIR_SHOW_ADDNEW") && $allow_insert;

			if(check_function("system_ffgrid_process_customize_field_button")) {
				system_ffgrid_process_customize_field_button($oGrid_dir, "vgallery_dir_fields", array("real_path_info" => $cm->real_path_info
																							, "path_info" => $cm->path_info));
			} 
			
			if($limit_type) {
				$sSQL = "SELECT vgallery_type.ID
							, vgallery_type.name 
						FROM vgallery_type
						WHERE vgallery_type.ID IN(" . $limit_type . ")
							AND vgallery_type.is_dir_default > 0";
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$oButton = ffButton::factory($cm->oPage);
						$oButton->ajax = $oGrid_dir->record_id;
						$oButton->id = "structure";
						$oButton->label = ffTemplate::_get_word_by_code("vgallery_structure") . " " . $db->getField("name", "Text", true);
					    $oButton->action_type = "gotourl";
					    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/structure?keys[ID]=" . $db->getField("ID", "Number", true);
					    $oButton->aspect = "link";
						$oGrid_dir->addActionButtonHeader($oButton);			
					} while($db->nextRecord());
				}
			}			 		    
		    // Chiave
		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "ID";
		    $oField->base_type = "Number";
			$oField->order_SQL = implode(", ", $arrOrder_dir_SQL);
		    $oGrid_dir->addKeyField($oField);

		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "display_path";
		    $oField->src_having = true;
		    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_display_path");
		    $oGrid_dir->addContent($oField);
		        
			if(is_array($arrFormField["dir"]) && count($arrFormField["dir"])) {
			    foreach($arrFormField["dir"] AS $field_key => $field_value) {
				    $field_name = $field_value["name"];

				    $oField = ffField::factory($cm->oPage);
			        if($field_value["data_type"] == "data" 
				    || $field_value["data_type"] == "selection"
				    || $field_value["data_type"] == "relationship") 
						$oField->data_type = "db";
					else
						$oField->data_type = "";

				    $oField->id = $field_name;
				    $oField->class = strtolower($field_value["extended_type"]) . " " . $field_name;
				    $oField->label = ffTemplate::_get_word_by_code("vgallery_nodes_" . $field_name);
				    if($field_value["extended_type"] == "Image"
	    				|| $field_value["extended_type"] == "Upload"
	    				|| $field_value["extended_type"] == "UploadImage"
				    ) {
				        $oField->control_type = "picture_no_link";
				        $oField->extended_type = "File";
				        $oField->file_storing_path = FF_DISK_UPDIR;
				        $oField->file_show_delete = false;

				        $oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]"; 
				        $oField->file_temp_view_url = CM_SHOWFILES . "/[_FILENAME_]";
						if($force_picture_ico_spacer) {
				            $oField->file_saved_preview_url = CM_SHOWFILES . "/" . THEME_INSET . "/images/spacer.gif";
				            $oField->file_temp_preview_url = CM_SHOWFILES . "/" . THEME_INSET . "/images/spacer.gif";
						} else {
						    $oField->file_saved_preview_url = CM_SHOWFILES . "/thumb[real_parent_VALUEPATH]/[name_VALUE]/[_FILENAME_]";
						    $oField->file_temp_preview_url = CM_SHOWFILES . "/thumb[real_parent_VALUEPATH]/[name_VALUE]/[_FILENAME_]";
						}	        
				    }
				    $oField->base_type = $field_value["ff_extended_type"];
				    $oField->encode_entities = false;
				    $oField->src_having = true;
				    $oField->user_vars = $arrFormField[$field_key];
	/*
				    if($field_value["sort_default"]) {
	    				$oField->order_SQL = $field_name;
						if($drag_sort_dir_enabled) {
							$oField->order_SQL = " `order`" . (strlen($oField->order_SQL) ? ", " . $oField->order_SQL : "");
						}	    
					}*/
				    $oGrid_dir->addContent($oField, false); 
			    } 
			}		        
		        
		    if($show_owner_in_grid && !$simple_interface) {
			    $oField = ffField::factory($cm->oPage);
			    $oField->id = "anagraph";
			    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_nodes_anagraph");
			    $oField->src_having = true;
			    $oGrid_dir->addContent($oField);
		    }

			if(strpos($cm->path_info, VG_WS_ECOMMERCE) === 0) {
				ecommerce_ffGrid_product($oGrid_dir, "dir");
			} else {
				if(AREA_SEO_SHOW_MODIFY) {
			        $oButton = ffButton::factory($cm->oPage);
			        $oButton->id = "seo";
					$oButton->ajax = ($disable_dialog ? false : $oGrid_dir->record_id);
					$oButton->action_type = "gotourl";
					$oButton->url = $cm->oPage->site_path . "/admin/seo?key=[ID_VALUE]&type=vgallery";
			        $oButton->aspect = "link";
					$oButton->label = ffTemplate::_get_word_by_code("seo");
					$oButton->display_label = false;
			        $oGrid_dir->addGridButton($oButton);
			    }

			    if(Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
			        $oButton = ffButton::factory($cm->oPage);
			        $oButton->id = "properties"; 
					$oButton->ajax = ($disable_dialog ? false : $oGrid_dir->record_id);
					$oButton->action_type = "gotourl";
					$oButton->url = $cm->oPage->site_path . "/admin/aspect?key=[ID_VALUE]&type=vgallery";
			        $oButton->aspect = "link";
					$oButton->label = ffTemplate::_get_word_by_code("properties");
					$oButton->display_label = false;
			        $oGrid_dir->addGridButton($oButton, "last");
			    } 		
				if(Auth::env("AREA_ECOMMERCE_SHOW_MODIFY") && $enable_ecommerce) {
			        $oButton = ffButton::factory($cm->oPage);
			        $oButton->id = "ecommerce"; 
					$oButton->ajax = ($disable_dialog ? false : $oGrid_dir->record_id);
					$oButton->action_type = "gotourl";
					$oButton->url = $cm->oPage->site_path . "/admin/ecommerce?key=[ID_VALUE]";
			        $oButton->aspect = "link";
			        $oButton->class = Cms::getInstance("frameworkcss")->get("shopping-cart", "icon");
			        $oButton->label = ffTemplate::_get_word_by_code("shopping-cart");
			        $oButton->display_label = false;
			        $oGrid_dir->addGridButton($oButton, "last");
			    } 		
			} 
			
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "clone"; 
			$oButton->ajax = true;
			$oButton->action_type = "gotourl";
			$oButton->frmAction = "clone";
			$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]dir=1";
			$oButton->aspect = "link";
			$oButton->label = ffTemplate::_get_word_by_code("vgallery_clone");
			$oButton->display_label = false;
			$oGrid_dir->addGridButton($oButton);
			
			if(Auth::env("AREA_VGALLERY_SHOW_VISIBLE")) {
			    $oButton = ffButton::factory($cm->oPage);
			    $oButton->id = "visible";
			    $oButton->ajax = true;
			    $oButton->action_type = "gotourl";
			    $oButton->frmAction = "setvisible";
			    $oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]dir=1";
			    $oButton->aspect = "link";
			    $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
			    $oButton->display_label = false;
			    $oGrid_dir->addGridButton($oButton, "last");
			} 
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "edit";
			$oButton->ajax = ($disable_dialog ? false : $oGrid_dir->record_id);
			$oButton->action_type = "gotourl";
			$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]?[KEYS]";
			$oButton->aspect = "link";
			$oButton->label = ffTemplate::_get_word_by_code("vgallery_edit");
			$oButton->display_label = false;
			$oGrid_dir->addGridButton($oButton, "last");
			
		    $cm->oPage->addContent($oGrid_dir, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_dir_title"))); 
		}
	} 
	else 
	{
		$oGrid_vgallery = ffGrid::factory($cm->oPage);
		$oGrid_vgallery->ajax_addnew = !$disable_dialog;
		$oGrid_vgallery->ajax_delete = true;
		$oGrid_vgallery->ajax_search = true; 
		$oGrid_vgallery->id = "VGallery";
		$oGrid_vgallery->source_SQL = "SELECT 
		                            vgallery.ID
		                            , vgallery.name AS name
		                            , vgallery_nodes.visible AS visible
		                            , CONCAT(
                            			IF(vgallery.public > 0
                            				, " . $db->toSql(ffTemplate::_get_word_by_code("prefix_public_title")) . "
                            				, ''
                            			)
                            			, vgallery_nodes.name
                            			, ' ('
                            			, IFNULL(
                            				(SELECT count(nodes.ID) FROM vgallery_nodes AS nodes WHERE nodes.parent LIKE CONCAT('/', vgallery.name, '%') AND nodes.is_dir = 0 )
                            				, 0
                            			)
                            			, ')'
		                            ) AS display_name
		                            , vgallery_nodes.ID AS ID_vgallery_nodes
		                        FROM 
		                            vgallery  
		                            LEFT JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID AND vgallery_nodes.parent = '/'
		                        WHERE vgallery.public = 0
			                    [AND] [WHERE]
			                    [HAVING]
								[ORDER]";

		$oGrid_vgallery->order_default = "display_name";
		$oGrid_vgallery->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
		$oGrid_vgallery->record_id = "VGalleryModify";
		$oGrid_vgallery->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
		$oGrid_vgallery->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/setting/add";
		$oGrid_vgallery->bt_delete_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]/setting?[KEYS]&" . $oGrid_vgallery->record_id . "_frmAction=confirmdelete";
		$oGrid_vgallery->resources[] = $oGrid_vgallery->record_id;
		$oGrid_vgallery->display_edit_bt = false;
		$oGrid_vgallery->display_edit_url = Auth::env("AREA_VGALLERY_SHOW_MODIFY");
		$oGrid_vgallery->display_delete_bt = Auth::env("AREA_VGALLERY_SHOW_MODIFY");
		$oGrid_vgallery->display_new = Auth::env("AREA_VGALLERY_SHOW_MODIFY");
		$oGrid_vgallery->addEvent("on_before_parse_row", "VGallery_on_before_parse_row");

		/**
		* Title
		*/
		system_ffcomponent_set_title(
			ffTemplate::_get_word_by_code("vgallery_title")
			, true
			, false
			, false
			, $oGrid_vgallery
		);
		
		// Chiave
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid_vgallery->addKeyField($oField);
		
		// Visualizzazione
		$oField = ffField::factory($cm->oPage);
		$oField->id = "display_name";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_name");
		$oField->encode_entities = false;
		$oGrid_vgallery->addContent($oField);
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_name");
		$oField->encode_entities = false;
		$oGrid_vgallery->addContent($oField);

		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "edit";
		$oButton->ajax = ($disable_dialog ? false : $oGrid_vgallery->record_id);
		$oButton->action_type = "gotourl";
		$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/setting?[KEYS]";
		$oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("vgallery_edit");
		$oButton->display_label = false;
		$oGrid_vgallery->addGridButton($oButton);


		if(AREA_SEO_SHOW_MODIFY) {
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "seo"; 
			$oButton->ajax = ($disable_dialog ? false : $oGrid_vgallery->record_id);
			$oButton->action_type = "gotourl";
			$oButton->url = $cm->oPage->site_path . "/admin/seo?key=[ID_vgallery_nodes_VALUE]&type=vgallery";
			$oButton->aspect = "link";
			$oButton->label = ffTemplate::_get_word_by_code("seo");
			$oButton->display_label = false;
		    $oGrid_vgallery->addGridButton($oButton);
		}

		if(Auth::env("AREA_VGALLERY_SHOW_PERMISSION") && Cms::env("ENABLE_STD_PERMISSION")) {
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "permissions"; 
			$oButton->ajax = ($disable_dialog ? false : $oGrid_vgallery->record_id);
			$oButton->action_type = "gotourl";
			$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/permission?keys[ID]=[ID_vgallery_nodes_VALUE]";
			$oButton->aspect = "link";
			$oButton->label = ffTemplate::_get_word_by_code("permissions");
			$oButton->display_label = false;
		    $oGrid_vgallery->addGridButton($oButton);
		}
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "visible";
		$oButton->ajax = true;
		$oButton->action_type = "gotourl";
		$oButton->frmAction = "setvisible";
		$oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/setting?[KEYS]";
		$oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("status_frontend");
		$oButton->display_label = false;
		$oGrid_vgallery->addGridButton($oButton);
		
		if(MASTER_CONTROL) {
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "public";
			$oButton->ajax = true;
			$oButton->action_type = "gotourl";
			$oButton->frmAction = "setpublic";
			$oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/setting?[KEYS]";
			$oButton->aspect = "link";
			$oButton->display_label = false;
			$oGrid_vgallery->addGridButton($oButton);
		}
		
		if($simple_interface || !MASTER_CONTROL) {
			$cm->oPage->addContent($oGrid_vgallery); 		
		} else {
			$cm->oPage->addContent($oGrid_vgallery, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_items")));
			
			$oGrid_models = ffGrid::factory($cm->oPage);
			$oGrid_models->ajax_addnew = !$disable_dialog;
			$oGrid_models->ajax_delete = true;
			$oGrid_models->ajax_search = true; 
			$oGrid_models->id = "VGalleryModels";
			$oGrid_models->source_SQL = "SELECT 
			                            vgallery.ID
			                            , vgallery.name AS name
			                            , vgallery.public AS public
			                            , vgallery.public_cover AS public_cover
			                            , vgallery.public_description AS public_description
			                            , vgallery.public_link_doc AS public_link_doc
			                            , vgallery_nodes.visible AS visible
			                            , '' AS display_name
			                            , vgallery_nodes.ID AS ID_vgallery_nodes
			                        FROM 
			                            vgallery  
			                            LEFT JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID AND vgallery_nodes.parent = '/'
			                        WHERE vgallery.public > 0
				                    [AND] [WHERE]
				                    [HAVING]
									[ORDER]";

			$oGrid_models->order_default = "display_name";
			$oGrid_models->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
			$oGrid_models->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
			$oGrid_models->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/setting/add";
			$oGrid_models->record_id = "VGalleryModify";
			$oGrid_models->resources[] = $oGrid_models->record_id;
			$oGrid_models->display_edit_url = false;
			$oGrid_models->display_delete_bt = Auth::env("AREA_VGALLERY_SHOW_MODIFY");
			$oGrid_models->display_new = Auth::env("AREA_VGALLERY_SHOW_MODIFY");
			$oGrid_models->addEvent("on_before_parse_row", "VGallery_on_before_parse_row");

			/**
			* Title
			*/
			system_ffcomponent_set_title(
				ffTemplate::_get_word_by_code("vgallery_title")
				, true
				, false
				, false
				, $oGrid_models
			);
			
			// Chiave
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID";
			$oField->base_type = "Number";
			$oGrid_models->addKeyField($oField);

			// Visualizzazione
			$oField = ffField::factory($cm->oPage);
			$oField->id = "display_name";
			$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_name");
			$oField->encode_entities = false;
			$oGrid_models->addContent($oField);

			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "edit";
			$oButton->ajax = ($disable_dialog ? false : $oGrid_models->record_id);
			$oButton->action_type = "gotourl";
			$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/setting?[KEYS]";
			$oButton->aspect = "link";
			$oButton->label = ffTemplate::_get_word_by_code("vgallery_edit");
			$oButton->display_label = false;
			$oGrid_models->addGridButton($oButton);


			if(AREA_SEO_SHOW_MODIFY) {
				$oButton = ffButton::factory($cm->oPage);
				$oButton->id = "seo"; 
				$oButton->ajax = ($disable_dialog ? false : $oGrid_models->record_id);
				$oButton->action_type = "gotourl";
				$oButton->url = $cm->oPage->site_path . "/admin/seo?key=[ID_vgallery_nodes_VALUE]&type=vgallery";
				$oButton->aspect = "link";
				$oButton->label = ffTemplate::_get_word_by_code("seo");
				$oButton->display_label = false;
			    $oGrid_models->addGridButton($oButton);
			}

			if(Auth::env("AREA_VGALLERY_SHOW_PERMISSION") && Cms::env("ENABLE_STD_PERMISSION")) {
				$oButton = ffButton::factory($cm->oPage);
				$oButton->id = "permissions"; 
				$oButton->ajax = ($disable_dialog ? false : $oGrid_models->record_id);
				$oButton->action_type = "gotourl";
				$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/permission?keys[ID]=[ID_vgallery_nodes_VALUE]";
				$oButton->aspect = "link";
				$oButton->label = ffTemplate::_get_word_by_code("permissions");
				$oButton->display_label = false;
			    $oGrid_models->addGridButton($oButton);
			}
			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "visible";
			$oButton->ajax = true;
			$oButton->action_type = "gotourl";
			$oButton->frmAction = "setvisible";
			$oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/setting?[KEYS]";
			$oButton->aspect = "link";
			$oButton->label = ffTemplate::_get_word_by_code("status_frontend");
			$oButton->display_label = false;
			$oGrid_models->addGridButton($oButton);

			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "public";
			$oButton->ajax = true;
			$oButton->action_type = "gotourl";
			$oButton->frmAction = "setpublic";
			$oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]/setting?[KEYS]";
			$oButton->aspect = "link";
			$oButton->display_label = false;
			$oGrid_models->addGridButton($oButton);
		
			$cm->oPage->addContent($oGrid_models, "rel", null, array("title" => ffTemplate::_get_word_by_code("vgallery_models")));
						
			
		}
	}
}

function VGallery_on_before_parse_row($component) {
    $cm = cm::getInstance();

	if(isset($component->grid_fields["display_name"]) && !$component->grid_fields["display_name"]->getValue()) {
		$title = $component->db[0]->getField("name", "Text", true);
		$description[] = $component->db[0]->getField("public_description", "Text", true);

		$cover = $component->db[0]->getField("public_cover", "Text", true);
		if(strlen($cover)) {
			if(strpos($cover, "://") === false) {
				if(strpos(CM_SHOWFILES, "://") === false)
					$showfile_url = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . MASTER_SITE . "/" . CM_SHOWFILES . "/32x32";
				else 
					$showfile_url = CM_SHOWFILES . "/32x32";
			} else {
				$showfile_url = "";
			}
			$cover = $showfile_url . $cover; 
		}
			
		$component->grid_fields["display_name"]->setValue(get_vgallery_card($title, $cover, $description, $component->db[0]->getField("public_link_doc", "Text", true)));
	}	
	
    if(isset($component->grid_buttons["visible"])) {
	    if($component->db[0]->getField("visible", "Number", true)) {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=0";
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
	    } else {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=1";
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
	    }
	}
    
    if(isset($component->grid_buttons["public"])) {
	    if($component->db[0]->getField("public", "Number", true)) {
	    	$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->user_vars["url"] . "setpublic=0";
            $component->grid_buttons["public"]->class = Cms::getInstance("frameworkcss")->get("globe", "icon");
	    } else {
	    	$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->user_vars["url"] . "setpublic=1";
            $component->grid_buttons["public"]->class = Cms::getInstance("frameworkcss")->get("globe", "icon", array("transparent"));
	    }
	}	
}

function VGalleryDirModify_on_before_parse_row($component) {
	$cm = cm::getInstance();

    if($component->db[0]->getField("is_clone", "Number", true) > 0) {
		$component->row_class = "clone";
    } else {
		$component->row_class = "";
    }

    if(isset($component->grid_buttons["visible"])) {
	    if($component->db[0]->getField("visible", "Number", true)) {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "&setvisible=0";
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
	    } else {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "&setvisible=1";
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
	    }
	}
    
    if(isset($component->grid_buttons["public"])) {
	    if($component->db[0]->getField("public", "Number", true)) {
	    	$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->user_vars["url"] . "&setpublic=0";
            $component->grid_buttons["public"]->class = Cms::getInstance("frameworkcss")->get("globe", "icon");
	    } else {
	    	$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->user_vars["url"] . "&setpublic=1";
            $component->grid_buttons["public"]->class = Cms::getInstance("frameworkcss")->get("globe", "icon", array("transparent"));
	    }
	}
	
	if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
		$file_permission = get_file_permission($component->db[0]->getField("full_path", "Text", true), "vgallery_nodes");
	
	if(!check_mod($file_permission, 2, false)) {
		$component->display_edit_bt = false;
    	$component->display_edit_url = false;
    	$component->visible_delete_bt = false;

    	foreach($component->grid_buttons AS $button_key => $button_value) {
    		$component->grid_buttons[$button_key]->display = false;
		}
	} else {
		$component->display_edit_bt = false;
    	$component->display_edit_url = true;
    	$component->visible_delete_bt = true; 

    	foreach($component->grid_buttons AS $button_key => $button_value) {
    		$component->grid_buttons[$button_key]->display = true;
		}
	}
}

function VGalleryNodesModify_on_before_parse_row($component) {
    $cm = cm::getInstance();

    $row_class = "";
    if($component->db[0]->getField("is_clone", "Number", true) > 0)
		$row_class .= " clone";
    
	if(!$component->db[0]->getField("visible", "Number", true))
		$row_class .= " novisible";
	
	$component->row_class = trim($row_class);
	
	$first_picture = true;
	$resolve_relationship = check_function("resolve_relationship");

	$ID_node = $component->key_fields["ID"]->getValue();
	$full_path = $component->db[0]->getField("full_path", "Text", true);

	if(strpos($full_path, "-" . $ID_node) === false && is_dir(FF_DISK_UPDIR . $full_path . "-" . $ID_node)) {
		$img_path = $full_path . "-" . $ID_node;
	} else {
		$img_path = $full_path;
	}

	if(is_file(FF_DISK_UPDIR . $img_path . ".jpg")) {
		$cover = $img_path . ".jpg";
	} else {
		$tmp_img = glob(FF_DISK_UPDIR . $img_path . "/*");
		if(is_array($tmp_img) && count($tmp_img)) {
			sort($tmp_img);
			foreach($tmp_img AS $tmp_img_key => $tmp_img_value) {
				if(is_file($tmp_img_value)) {
					$mime = ffMedia::getMimeTypeByFilename(FF_DISK_UPDIR . $img_path  . "/" . basename($tmp_img_value));
					if(strpos($mime, "image") !== false) {
						$cover = $img_path  . "/". basename($tmp_img_value);
						break;
					}
				}
			}
		}
	}	
		
	foreach($component->grid_fields AS $grid_key => $grid_value) {
		if($resolve_relationship 
			&& !strlen($component->grid_fields[$grid_key]->getValue())
			&& $component->grid_fields[$grid_key]->user_vars["data_type"] == "relationship"
		) {
				$component->grid_fields[$grid_key]->setValue(resolve_relationship($ID_node
										, $component->grid_fields[$grid_key]->user_vars["ID"]
										, $component->grid_fields[$grid_key]->user_vars["data_source"]
										, $component->grid_fields[$grid_key]->user_vars["data_limit"]
										, LANGUAGE_INSET_ID
									));
			
			
		}
		
		if($component->grid_fields[$grid_key]->extended_type == "File") {
			$image_path = $grid_value->getValue();

			if($first_picture) {
				if((!(strlen($image_path) && is_file(realpath(FF_DISK_UPDIR . $image_path))))) {
					$image_path = $cover;
				}
				
		        $first_picture = false;
		        if($component->user_vars["force_picture_link"]) { 
		        	$component->grid_fields[$grid_key]->control_type = "picture";
				} else {
		        	$component->grid_fields[$grid_key]->control_type = "picture_no_link";
				}
			} else {
				$component->grid_fields[$grid_key]->control_type = "picture";
			}
			
			if(strlen($image_path)) {
	        	$component->grid_fields[$grid_key]->setValue($image_path);
	        	if($component->user_vars["force_picture_ico_spacer"]) {
	                $component->grid_fields[$grid_key]->file_saved_preview_url = CM_SHOWFILES . "/" . FF_MAIN_THEME . "/images/spacer.gif";
	                $component->grid_fields[$grid_key]->file_temp_preview_url = CM_SHOWFILES . "/" . FF_MAIN_THEME . "/images/spacer.gif";
				} else {
	        		$component->grid_fields[$grid_key]->file_saved_preview_url = CM_SHOWFILES . "/thumb" . ffCommon_dirname($image_path) . "/[_FILENAME_]";
	        		$component->grid_fields[$grid_key]->file_temp_preview_url = CM_SHOWFILES . "/thumb" . ffCommon_dirname($image_path) . "/[_FILENAME_]";
				}
			} else {
				$component->grid_fields[$grid_key]->setValue("");
	        	$component->grid_fields[$grid_key]->control_type = "";
			}
		}
	} reset($component->grid_fields);
	
	if(isset($component->grid_fields["card"])) {
		$title = $component->db[0]->getField("meta_title_alt", "Text", true);
		if(!$title)
			$title = $component->db[0]->getField("meta_title", "Text", true);
		if(!$title)
			$title = ucwords(str_replace("-", " ", $component->db[0]->getField("name", "Text", true)));
		
		if($component->user_vars["show_ID"])
			$description["ID"] = $component->db[0]->getField("ID", "Number", true);

		if($component->db[0]->getField("priority", "Number", true)) {
 			$arrPriorityDefault = array();
	        $arrPriorityDefault[1] = ffTemplate::_get_word_by_code("vgallery_priority_bottom");
            $arrPriorityDefault[2] = ffTemplate::_get_word_by_code("vgallery_very_low");
            $arrPriorityDefault[3] = ffTemplate::_get_word_by_code("vgallery_low");
            $arrPriorityDefault[4] = ffTemplate::_get_word_by_code("vgallery_normal");
            $arrPriorityDefault[5] = ffTemplate::_get_word_by_code("vgallery_hight");
            $arrPriorityDefault[6] = ffTemplate::_get_word_by_code("vgallery_very_hight");
            $arrPriorityDefault[7] = ffTemplate::_get_word_by_code("vgallery_top");	
            	
			$description["Priority"] = $arrPriorityDefault[$component->db[0]->getField("priority", "Number", true)];
		}
			
		if($component->db[0]->getField("isbn", "Text", true))
			$description["ISBN"] = $component->db[0]->getField("isbn", "Text", true);

		if($component->db[0]->getField("published_at", "Text", true))
			$description["Publish"] = $component->db[0]->getField("published_at", "Timestamp")->getValue("Date", FF_LOCALE);
			
		$component->grid_fields["card"]->setValue(get_vgallery_card($title, $cover, $description, $component->db[0]->getField("permalink", "Text", true)));

		if(isset($component->grid_fields["ID"]))
			$component->grid_fields["ID"]->setValue("");
	}

    if(isset($component->grid_buttons["visible"])) {
	    if($component->db[0]->getField("visible", "Number", true)) {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=0";
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
	    } else {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=1";
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
	    }
	}
    
    if(isset($component->grid_buttons["public"])) {
	    if($component->db[0]->getField("public", "Number", true)) {
	    	$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->user_vars["url"] . "setpublic=0";
            $component->grid_buttons["public"]->class = Cms::getInstance("frameworkcss")->get("globe", "icon");
	    } else {
	    	$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->user_vars["url"] . "setpublic=1";
            $component->grid_buttons["public"]->class = Cms::getInstance("frameworkcss")->get("globe", "icon", array("transparent"));
	    }
	}
	
	if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
		$file_permission = get_file_permission($component->db[0]->getField("full_path", "Text", true), "vgallery_nodes");

	if(!check_mod($file_permission, 2, false)) {
		$component->display_edit_bt = false;
    	$component->display_edit_url = false;
    	$component->visible_delete_bt = false;

    	foreach($component->grid_buttons AS $button_key => $button_value) {
    		$component->grid_buttons[$button_key]->display = false;
		}
	} else {
		$component->display_edit_bt = false;
    	$component->display_edit_url = true;
    	$component->visible_delete_bt = true; 
    	
    	foreach($component->grid_buttons AS $button_key => $button_value) {
    		$component->grid_buttons[$button_key]->display = true;
		}
	}
}

function VGalleryNodesModify_on_loaded_data($component, &$records) {
	$db = ffDB_Sql::factory();

	if(is_array($records) && count($records)) {
		foreach($records AS $key => $value) {
			$arrOwner[$value["owner"]][] = $key;
			$arrNodes[$value["ID"]] = $key;
		}
	}
	
	if(isset($component->grid_fields["anagraph"])) {
		if(is_array($arrOwner) && count($arrOwner)) {
			$sSQL = "SELECT 
						" . CM_TABLE_PREFIX . "mod_security_users.ID AS ID
						, (IF(anagraph.uid > 0
							, IF(anagraph.billreference = ''
							    , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
		                            , IF(CONCAT(anagraph.name, ' ', anagraph.surname) = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
		                                , CONCAT(anagraph.name, ' ', anagraph.surname)
		                                , CONCAT(CONCAT(anagraph.name, ' ', anagraph.surname), ' (', IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username), ')')
		                            )
		                            , IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
							    )
							    , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
		                            , CONCAT(anagraph.name, ' ', anagraph.surname)
		                            , CONCAT(anagraph.billreference, ' (', IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username), ')')
							    )
							)
							, IF(anagraph.billreference = ''
		                        , CONCAT(anagraph.name, ' ', anagraph.surname)
		                        , anagraph.billreference
							)
						)) AS value
					FROM " . CM_TABLE_PREFIX . "mod_security_users
						INNER JOIN anagraph ON anagraph.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID IN (" . $db->toSql(implode(",", array_keys($arrOwner)), "Text", false) . ")";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$ID = $db->getField("ID", "Number", true);
					if(is_array($arrOwner[$ID]) && count($arrOwner[$ID])) {
						foreach($arrOwner[$ID] AS $record_key) {
							$records[$record_key]["anagraph"] = $db->getField("value", "Text", true);
						}
					}
				} while($db->nextRecord());
			}
		}	
	}
	
	if(is_array($component->user_vars["sql_field"]) && count($component->user_vars["sql_field"])) {
		if(is_array($component->user_vars["sql_field"]["table.alt"]) && count($component->user_vars["sql_field"]["table.alt"])) {
			foreach($component->user_vars["sql_field"]["table.alt"] AS $data_source => $fields) {
			    $sSQL = "SELECT " . $data_source . ".*
				        FROM
				            " . $data_source . "
				        WHERE
				            " . $data_source . ".ID_nodes IN (" . $db->toSql(implode(",", array_keys($arrNodes)), "Text", false) . ")";
				$db->query($sSQL);
				if($db->nextRecord()) {
					$field_lang = (isset($db->record["ID_lang"])
									? true
									: false
								);
				
					do {
						$record_key = $arrNodes[$db->getField("ID_nodes", "Number", true)]; 
						foreach($fields AS $field_key => $field_data) {
							$data_limit = ($field_data["data_limit"]
								? $field_data["data_limit"]
								: "name"
							);

							if(isset($records[$record_key][$field_key])) {
								if($field_lang && $db->getField($field_lang, "Number", true) != $field_data["lang"])
									continue;

								$arrFieldValue = array();
								if(is_array($data_limit)) {
									foreach($data_limit AS $data_limit_value) {
										$arrFieldValue[] = $db->getField($data_limit_value, "Text", true);
									}
								} else {
									$arrFieldValue[] = $db->getField($data_limit, "Text", true);
								}
								
								$records[$record_key][$field_key] = implode(" ", $arrFieldValue);
							}
						}
					} while($db->nextRecord());
				}
			}
		}
		if(is_array($component->user_vars["sql_field"]["default"]) && count($component->user_vars["sql_field"]["default"])) {
 			$sSQL = " 
	            SELECT vgallery_rel_nodes_fields.ID_nodes 				AS ID_nodes
	            	, vgallery_rel_nodes_fields.ID_lang 				AS ID_lang
	            	, vgallery_rel_nodes_fields.ID_fields 				AS ID_fields
		            , vgallery_rel_nodes_fields.description_text 		AS description_text
		            , vgallery_rel_nodes_fields.description 			AS description
				FROM
				    vgallery_rel_nodes_fields
				WHERE
				    vgallery_rel_nodes_fields.ID_nodes IN (" . $db->toSql(implode(",", array_keys($arrNodes)), "Text", false) . ")
				    AND vgallery_rel_nodes_fields.ID_fields IN ( " . $db->toSql(implode(",", array_keys($component->user_vars["sql_field"]["default"])), "Text", false) . " )";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$record_key = $arrNodes[$db->getField("ID_nodes", "Number", true)]; 
					$ID_field = $db->getField("ID_fields", "Number", true);
					$field_key = $component->user_vars["sql_field"]["default"][$ID_field]["name"];

					if(isset($records[$record_key][$field_key])) {
						if($db->getField("ID_lang", "Number", true) != $component->user_vars["sql_field"]["default"][$ID_field]["ID_lang"])
							continue;

						$records[$record_key][$field_key] = $db->getField($component->user_vars["sql_field"]["default"][$ID_field]["field_name"], "Text", true);
					}
				} while($db->nextRecord());
			}
		}
	}
}