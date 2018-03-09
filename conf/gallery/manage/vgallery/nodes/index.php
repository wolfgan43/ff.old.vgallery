<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$simple_interface = false;
if (!(AREA_VGALLERY_SHOW_MODIFY || AREA_VGALLERY_SHOW_ADDNEW || AREA_VGALLERY_SHOW_DELETE || AREA_VGALLERY_SHOW_RELATIONSHIP || AREA_VGALLERY_SHOW_SEO || AREA_VGALLERY_SHOW_PERMISSION || AREA_ECOMMERCE_SHOW_MODIFY)) {
	$sSQL = "SELECT 
		CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
			FROM users_rel_vgallery
				INNER JOIN vgallery_nodes ON vgallery_nodes.ID = users_rel_vgallery.ID_nodes
				INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
			WHERE users_rel_vgallery.uid = " . $db_gallery->tosql(get_session("UserNID"), "Number") . "
				AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) LIKE '" . $db_gallery->tosql($cm->real_path_info, "Text", false) . "%'
				AND users_rel_vgallery.cascading > 0
			ORDER BY vgallery_nodes.`order`, vgallery_nodes.`ID`
			";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		use_cache(false);
		$cm->real_path_info = $db_gallery->getField("full_path", "Text", true);
		$simple_interface = true;
	} else {
    	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	}
}


//$cm->oPage->form_method = "post";
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
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			do {
				ecommerce_set_cache_price(
					$db_gallery->getField("ID", "Number", true)
					, $db_gallery->getField("buy_price", "Number")
					, $db_gallery->getField("basic_price", "Number")
					, $db_gallery->getField("decumulation", "Text", true)
					, $db_gallery->getField("vat", "Number", true)
					, $db_gallery->getField("basic_discount", "Number", true)
				);
			} while($db_gallery->nextRecord());
		}
	}
}

//, ( SELECT count(*) FROM vgallery_nodes WHERE vgallery_nodes.parent = full_path AND (vgallery_nodes.is_dir > 0) ) AS count_nodes
if(strlen($cm->real_path_info)) {
	$arrPathInfo = explode("/", ltrim($cm->real_path_info, "/"));
	$db_gallery->query("SELECT vgallery.*
	                        , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
	                    FROM vgallery 
	                        LEFT JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID
                        		AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) = " . $db_gallery->tosql("/" . $arrPathInfo[0]) . "
	                    WHERE vgallery.name =" . $db_gallery->tosql($arrPathInfo[0]) . " 
	                    	AND vgallery.public = 0");
	if($db_gallery->nextRecord()) {
		$ID_vgallery 				= $db_gallery->getField("ID", "Number", true);
	    $vgallery_name 				= $db_gallery->getField("name", "Text", true);
		$full_path 					= $db_gallery->getField("full_path", "Text", true);
	    $limit_level 				= $db_gallery->getField("limit_level", "Number", true);
		$show_ID 					= $db_gallery->getField("show_ID", "Number", true);
		$show_isbn 					= $db_gallery->getField("show_isbn", "Number", true);
	    //$count_nodes 				= $db_gallery->getField("count_nodes")->getValue();
	    $enable_ecommerce 			= $db_gallery->getField("enable_ecommerce", "Number", true);
	    $enable_ecommerce_all_level	= $db_gallery->getField("enable_ecommerce_all_level", "Number", true);
	    
	    $enable_multilang_visible 	= $db_gallery->getField("enable_multilang_visible", "Number", true);
	    $force_picture_link 		= $db_gallery->getField("force_picture_link", "Number", true);
	    $force_picture_ico_spacer 	= $db_gallery->getField("force_picture_ico_spacer", "Number", true);
	    $disable_dialog_in_edit 	= $db_gallery->getField("disable_dialog_in_edit", "Number", true); 
	    $drag_sort_node_enabled 	= $db_gallery->getField("drag_sort_node_enabled", "Number", true); 
	    $drag_sort_dir_enabled 		= $db_gallery->getField("drag_sort_dir_enabled", "Number", true); 
	    $show_owner_in_grid 		= $db_gallery->getField("show_owner_in_grid", "Number", true); 
	    $enable_tag 				= $db_gallery->getField("enable_tag", "Number", true);
		$limit_type 				= $db_gallery->getField("limit_type", "Text", true);

		$enable_multi_cat 			= $db_gallery->getField("enable_multi_cat", "Number", true);
		$enable_place 				= $db_gallery->getField("enable_place", "Number", true);
		$enable_priority 			= $db_gallery->getField("enable_priority", "Text", true);

		$orderby 					= $db_gallery->getField("back_orderby", "Text", true);
		$show_filteraz 				= $db_gallery->getField("back_filteraz", "Number", true);
		

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
			                , " . $db_gallery->toSql($ID_vgallery) . "
			                , " . $db_gallery->toSql(ffCommon_url_rewrite($vgallery_name)) . "
			                , '0'
			                , " . $db_gallery->toSql("/") . "
			                , (SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.name = " . $db_gallery->toSql(new ffData("Directory")) . ")
			                , '1'
			                , " . $db_gallery->toSql(time(), "Number") . "
			            )";
			$db_gallery->execute($sSQL);
			$full_path = "/" . ffCommon_url_rewrite($vgallery_name);

            if(check_function("refresh_cache")) {
	            refresh_cache("V", $db_gallery->getInsertID(true), "insert", $full_path);
			}
		}

	    if((count(explode("/", $full_path)))  <= $limit_level) {
	        $allow_insert = true;
	    } else {
	        $allow_insert = false;
	    }
	    
	    $allow_insert_nodes = true;
	    $vgallery_title = ffTemplate::_get_word_by_code($db_gallery->getField("name")->getValue());

		$denied_insert_ajax_node = false;
		$denied_insert_ajax_dir = false;
	    
	    $sSQL = "SELECT vgallery_type.*
    				, (SELECT COUNT(vgallery_fields.ID) 
    					FROM vgallery_fields 
    						INNER JOIN extended_type on extended_type.ID = vgallery_fields.ID_extended_type
    					WHERE vgallery_fields.ID_type = vgallery_type.ID 
    						AND extended_type.name = 'GMap'
    				) AS ajax_denied
    			FROM vgallery_type 
    			WHERE " . (strlen($limit_type) ? " vgallery_type.ID IN (" . $db_gallery->tosql($limit_type, "Text", false) . ") " : " 1 ") . "
    			ORDER BY vgallery_type.ID";
	    $db_gallery->query($sSQL);
	    if($db_gallery->nextRecord()) {
			$arrAjaxDenied = array();
    		do {
		        if($db_gallery->getField("ajax_denied", "Number", true) > 0) {
					$arrAjaxDenied[$db_gallery->getField("ID", "Number", true)] = true;
					if($db_gallery->getField("is_dir_default", "Number", true))
						$denied_insert_ajax_dir = true;
					else	
						$denied_insert_ajax_node = true;
				}

    			if($db_gallery->getField("is_dir_default", "Number", true))
    				$arrAllowedType["dir"][] = $db_gallery->getField("name", "Number", true);
				else    		
					$arrAllowedType["node"][] = $db_gallery->getField("name", "Number", true);
			} while($db_gallery->nextRecord());
		}
	} else {
	   // $count_nodes = 0;
	    $allow_insert = false;
	    $allow_insert_nodes = false;
	    $vgallery_title = "";
	    
		$denied_insert_ajax_node = true;
		$denied_insert_ajax_dir = true;
	}
} else {

}

if(strlen($cm->real_path_info)) {
	if(!$simple_interface && $allow_insert 
		&& 
			(
				(strpos($cm->path_info, VG_SITE_MANAGE) === 0 
					&& AREA_ECOMMERCE_SHOW_MODIFY && AREA_ECOMMERCE_SETTINGS_SHOW_MODIFY
				)
				||
				(strpos($cm->path_info, VG_SITE_RESTRICTED) === 0
					&& (AREA_VGALLERY_DIR_SHOW_MODIFY || AREA_VGALLERY_DIR_SHOW_DELETE)
				)
				||
				(strpos($cm->path_info, VG_SITE_RESTRICTED) !== 0 && strpos($cm->path_info, VG_SITE_MANAGE) !== 0 
				
				)
			)
	) {
		$allow_dir = true;
	} else {
		$allow_dir = false;
	}

	if(!$simple_interface && !$allow_dir) {
		//$drag_sort_node_enabled = false;
		//$drag_sort_dir_enabled = false;
	}


	if($denied_insert_ajax_dir || $denied_insert_ajax_node) {
		if(check_function("set_field_gmap")) { 
			if(set_field_gmap())
			{
				$denied_insert_ajax_dir = false;
				$denied_insert_ajax_node = false;
				$arrAjaxDenied = array();		
			}
		}
	}
	
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
	
	//, IF(vgallery_type.sort_default = vgallery_fields.ID, 1, 0) AS sort_default	
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
	        	vgallery_fields.ID_type IN ( " . $db_gallery->toSql($limit_type, "Text", false) . ")
	            AND vgallery_fields.enable_in_grid > 0 
	        ORDER BY vgallery_fields.enable_in_grid, vgallery_fields.`order_thumb`";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
	    $arrFormField = array();
	    do {
	    	$key = ffCommon_url_rewrite($db_gallery->getField("name", "Text", true));
			$tmpField = array(
	    			"ID" 								=> $db_gallery->getField("ID", "Number", true)
	    			, "name"							=> $key
	    			, "extended_type"					=> $db_gallery->getField("extended_type", "Text", true)
	    			, "ff_extended_type"				=> $db_gallery->getField("ff_extended_type", "Text", true)
	    			, "data_type"						=> $db_gallery->getField("data_type", "Text", true)
	    			, "data_source"						=> $db_gallery->getField("data_source", "Text", true)
	    			, "data_limit"						=> $db_gallery->getField("data_limit", "Text", true)
	    			, "disable_multilang"				=> $db_gallery->getField("disable_multilang", "Text", true)
	    			//, "sort_default"					=> $db_gallery->getField("sort_default", "Number", true)
	    		);
	    	
			/*if($tmpField["data_type"] == "table.alt") {
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
							$data_limit = "";
							$tmpField["name"] = "anagraph";
	            			break;
	            		default:
	            	}

	                $arrSQL["node"]["field"][$tmpField["name"]] = $data_limit . " AS `" . $db_gallery->tosql($tmpField["name"], "Text", false) . "`";
	            } else {
	            	$arrFormField["sql"]["table.alt"][$tmpField["data_source"]][$tmpField["name"]] = array(
	            		"data_limit" => $tmpField["data_limit"]
	            		, "ID_lang" => ($tmp_field["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID)
	            	);
	            	$arrSQL["node"]["field"][$tmpField["name"]] = "'' AS `" . $db_gallery->tosql($tmpField["name"], "Text", false) . "`";
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
	            $arrSQL["node"]["field"][$tmpField["name"]] = "'' AS `" . $db_gallery->tosql($tmpField["name"], "Text", false) . "`";
	        }*/			
	    	$is_dir = $db_gallery->getField("is_dir", "Number", true);
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

	                $arrSQL[$sql_type]["field"][$tmpField["name"]] = $data_limit . " AS `" . $db_gallery->tosql($tmpField["name"], "Text", false) . "`";
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
																) . " LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%' ";
					}
					if($search_term_dir_sql) {
	        	 		$arrSQL["dir"]["inner"]["search_" . $tmpField["data_source"]] = " `" . $tmpField["data_source"] . "` AS `vgallery_search_" . $tmpField["data_source"] ."` ON vgallery_search_alt.ID_nodes = vgallery_nodes.ID ";
	        	 		$arrSQL["dir"]["search"]["search_" . $tmpField["name"]] = (is_array($tmpField["data_limit"]) 
																	? "CONCAT(`vgallery_search_" . $tmpField["data_source"] ."`.`" . implode("`, `vgallery_search_" . $tmpField["data_source"] ."`.`", $tmpField["data_limit"]) . "`)"
																	: (strlen($tmpField["data_limit"]) 
																		? "`vgallery_search_" . $tmpField["data_source"] ."`.`" . $tmpField["data_limit"] . "`"
																		: "`vgallery_search_" . $tmpField["data_source"] ."`.`name`"
																	)
																) . " LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%' ";
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
	            	$arrSQL[$sql_type]["field"][$tmpField["name"]] = $field_sql . " AS `" . $db_gallery->tosql($tmpField["name"], "Text", false) . "`";
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
	        	 	$arrSQL["node"]["search"]["search_rel"] = " vgallery_search_rel.`description` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
	        	 													OR vgallery_search_rel.`description_text` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'";
				}
				if($search_term_dir_sql) {
	        	 	$arrSQL["dir"]["inner"]["search_rel"] = " vgallery_rel_nodes_fields AS vgallery_search_rel ON vgallery_search_rel.ID_nodes = vgallery_nodes.ID ";
	        	 	$arrSQL["dir"]["search"]["search_rel"] = " vgallery_search_rel.`description` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
	        	 													OR vgallery_search_rel.`description_text` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'";
				}
				/**
				* Order
				*/
	            if($_REQUEST[$grid_node_id . "_order"] == $tmpField["name"]) {
					$field_sql = " vgallery_order.`" . $arrFormField["sql"]["default"][$tmpField["ID"]]["field_name"] . "`";

					$arrSQL[$sql_type]["inner"]["order"] = " vgallery_rel_nodes_fields AS vgallery_order ON vgallery_order.ID_nodes = vgallery_nodes.ID 
															AND vgallery_order.ID_fields = " . $db_gallery->toSql($tmpField["ID"], "Number") . "
															AND vgallery_order.ID_lang = " . $db_gallery->toSql($arrFormField["sql"]["default"][$tmpField["ID"]]["ID_lang"], "Number");
				} else {
					$field_sql = " '' ";
				}

				$arrSQL[$sql_type]["field"][$tmpField["name"]] = $field_sql . " AS `" . $db_gallery->tosql($tmpField["name"], "Text", false) . "`";
	        }
	        
	        
	        
	    	if($is_dir) {
	    		$arrFormField["dir"][$key] = $tmpField;
	    		if(AREA_SHOW_ECOMMERCE && $enable_ecommerce && $enable_ecommerce_all_level) {
	    			$arrFormField["node"][$key] = $tmpField;
	    		}
			} else {
				$arrFormField["node"][$key] = $tmpField;
			}
	    } while($db_gallery->nextRecord());
	}
	
	/**
	* Search General
	*/
	if($search_term_node_sql) {
		if(LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID) {
            $arrSQL["node"]["search"]["default"] = " vgallery_nodes.`ID` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
            										OR vgallery_nodes.`meta_title` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`meta_title_alt` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`meta_description` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`keywords` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`name` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`parent` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`permalink` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'";

			$arrSQL["node"]["order"]["search"] = "	MATCH(meta_title_alt) AGAINST (" . $db_gallery->toSql($search_term_node_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_node_sql) . ", meta_title_alt) = 1, 0, 1)
													, MATCH(meta_title) AGAINST (" . $db_gallery->toSql($search_term_node_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_node_sql) . ", meta_title) = 1, 0, 1)
													, MATCH(meta_description) AGAINST (" . $db_gallery->toSql($search_term_node_sql) . ") DESC";                                                    
        } else {
		    $arrSQL["node"]["inner"]["search_lang"] = " vgallery_nodes_rel_languages AS vgallery_search_lang ON vgallery_search_lang.ID_nodes = vgallery_nodes.ID 
                                                            AND vgallery_search_lang.ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number");
		    $arrSQL["node"]["search"]["search_lang"] = " vgallery_nodes.`ID` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
		    										OR vgallery_search_lang.`meta_title` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`meta_title_alt` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`meta_description` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`keywords` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`permalink` LIKE '%" . $db_gallery->toSql($search_term_node_sql, "Text", false) . "%'";

			$arrSQL["node"]["order"]["search"] = "	MATCH(vgallery_search_lang.meta_title_alt) AGAINST (" . $db_gallery->toSql($search_term_node_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_node_sql) . ", vgallery_search_lang.meta_title_alt) = 1, 0, 1)
													, MATCH(vgallery_search_lang.meta_title) AGAINST (" . $db_gallery->toSql($search_term_node_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_node_sql) . ", vgallery_search_lang.meta_title) = 1, 0, 1)
													, MATCH(vgallery_search_lang.meta_description) AGAINST (" . $db_gallery->toSql($search_term_node_sql) . ") DESC";                                                    

		}
		
		$arrSQL["node"]["where"]["search"] = " (" . implode(" OR ", $arrSQL["node"]["search"]) . ") ";
		//$arrSQL["dir"]["where"]["search"] = $arrSQL["node"]["where"]["search"];
		
		/*$arrSQL["dir"]["order"]["search"] = "
				    MATCH(meta_title) AGAINST (" . $db_gallery->toSql($search_term_node_sql). ") DESC
					, MATCH(meta_description) AGAINST (" . $db_gallery->toSql($search_term_node_sql) . ") DESC";*/
	}	
		
	if($search_term_dir_sql) {
		if(LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID) {
            $arrSQL["dir"]["search"]["default"] = " vgallery_nodes.`ID` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
            										OR vgallery_nodes.`meta_title` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`meta_title_alt` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`meta_description` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`keywords` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`name` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`parent` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
                                                    OR vgallery_nodes.`permalink` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'";

			$arrSQL["dir"]["order"]["search"] = "	MATCH(meta_title_alt) AGAINST (" . $db_gallery->toSql($search_term_dir_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_dir_sql) . ", meta_title_alt) = 1, 0, 1)
													, MATCH(meta_title) AGAINST (" . $db_gallery->toSql($search_term_dir_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_dir_sql) . ", meta_title) = 1, 0, 1)
													, MATCH(meta_description) AGAINST (" . $db_gallery->toSql($search_term_dir_sql) . ") DESC";                                                    
        } else {
		    $arrSQL["dir"]["inner"]["search_lang"] = " vgallery_nodes_rel_languages AS vgallery_search_lang ON vgallery_search_lang.ID_nodes = vgallery_nodes.ID 
                                                            AND vgallery_search_lang.ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number");
		    $arrSQL["dir"]["search"]["search_lang"] = " vgallery_nodes.`ID` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
		    										OR vgallery_search_lang.`meta_title` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`meta_title_alt` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`meta_description` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`keywords` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'
	        										OR vgallery_search_lang.`permalink` LIKE '%" . $db_gallery->toSql($search_term_dir_sql, "Text", false) . "%'";

			$arrSQL["dir"]["order"]["search"] = "	MATCH(vgallery_search_lang.meta_title_alt) AGAINST (" . $db_gallery->toSql($search_term_dir_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_dir_sql) . ", vgallery_search_lang.meta_title_alt) = 1, 0, 1)
													, MATCH(vgallery_search_lang.meta_title) AGAINST (" . $db_gallery->toSql($search_term_dir_sql). ") DESC
													, IF(LOCATE(" . $db_gallery->toSql($search_term_dir_sql) . ", vgallery_search_lang.meta_title) = 1, 0, 1)
													, MATCH(vgallery_search_lang.meta_description) AGAINST (" . $db_gallery->toSql($search_term_dir_sql) . ") DESC";                                                    

		}
		
		$arrSQL["dir"]["where"]["search"] = " (" . implode(" OR ", $arrSQL["dir"]["search"]) . ") ";
	}		

	$arrOrder_node_SQL = array();
	$arrOrder_dir_SQL = array();
	if($drag_sort_node_enabled)
		$arrOrder_node_SQL[] = "vgallery_nodes.`order`";

	if($drag_sort_dir_enabled)
		$arrOrder_dir_SQL[] = "vgallery_nodes.`order`";

	switch($orderby) {
		case "frontend": 
			$arrOrder_node_SQL[] = "vgallery_nodes.priority DESC";
			break;
		case "title":
			break;
		default:
			$arrOrder_node_SQL[] = "vgallery_nodes.last_update DESC";
		
	}	
	
	$arrOrder_node_SQL[] = "vgallery_nodes.meta_title_alt";
	$arrOrder_dir_SQL[] = "vgallery_nodes.meta_title_alt";
	
	if($show_owner_in_grid)
		$arrSQL[$sql_type]["field"]["owner"] 				= "'' AS anagraph";

	if($simple_interface)
		$arrSQL[$sql_type]["where"]["owner"] 				= "vgallery_nodes.owner = " . $db_gallery->toSql(get_session("UserNID"), "Number");

	$arrSQL["node"]["where"]["is_dir"] 					= "NOT(vgallery_nodes.is_dir > 0)";
	
	$arrSQL["dir"]["where"]["is_dir"] 					= "(vgallery_nodes.is_dir > 0)";

	
	/***
	* Ecommerce
	*/
	if(!$simple_interface && strpos($cm->path_info, VG_SITE_MANAGE) === 0) 
	{ 
		check_function("ecommerce_product_process");

	    $ecommerce_data =  ecommerce_product_process($arrSQL, $full_path);
		$arrSQL = $ecommerce_data["sql"];
		
		$cm->oPage->addContent(ecommerce_ffGrid_product_total($ecommerce_data["location"], $ecommerce_data["manage_full_path"])); 

		if($ecommerce_data["content"])
			$cm->oPage->addContent($ecommerce_data["content"], null, "filter");
	}

	if($simple_interface) {
		$ret_url = urldecode($_REQUEST["ret_url"]);
	} else {
		$cm->oPage->addContent(null, true, "rel");    
	}

	
	$oGrid_node = ffGrid::factory($cm->oPage);
	//$oGrid_node->full_ajax = true;
	$oGrid_node->ajax_addnew = !$disable_dialog_in_edit && !$denied_insert_ajax_node;
	$oGrid_node->ajax_delete = true;
	$oGrid_node->ajax_search = true;
	$oGrid_node->id = $grid_node_id;
	$oGrid_node->search_simple_field_options["src_having"] = false;
	$oGrid_node->title = ""; //ffTemplate::_get_word_by_code("vgallery_node" . $vgallery_title);
	$oGrid_node->source_SQL = (is_array($arrSQL["node"]["union"]["pre"]) && count($arrSQL["node"]["union"]["pre"])
	                            	? "(" . implode(") UNION (", $arrSQL["node"]["union"]["pre"]) . ") UNION"
	                            	: ""
	                        ) . "
	                        (SELECT DISTINCT
	                            vgallery_nodes.*
	                            , vgallery_nodes.parent AS real_parent
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
					            (vgallery_nodes.parent = " . $db_gallery->toSql($cm->real_path_info)  . " 
					                OR vgallery_nodes.parent LIKE '" . $db_gallery->toSql($cm->real_path_info . "/", "Text", false)  . "%'
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
	$oGrid_node->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/modify";
	$oGrid_node->order_default = "ID";
	$oGrid_node->use_alpha = $show_filteraz;
	$oGrid_node->alpha_field = "meta_title_alt";
	
/*
	if(strpos($cm->path_info, VG_SITE_RESTRICTED) === 0) {
		$oGrid_node->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/modify";
		$oGrid_node->order_default = "ID";
	} else {
	    $oGrid_node->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/modify";
		$oGrid_node->order_default = (strlen($sort_node_default) 
		                            ? $sort_node_default 
		                            : "ID");
	}
*/
	$oGrid_node->record_id = $grid_node_id . "Modify";
	$oGrid_node->resources[] = $oGrid_node->record_id;
	$oGrid_node->resources[] = "EcStockModify";
	$oGrid_node->resources[] = "EcAddDataModify";
	//$oGrid_node->resources_set = $oGrid_node->resources;
	//&ftype=" . urlencode($arrAllowedType["node"][0]) . "&
	$oGrid_node->addit_insert_record_param = "type=node&vname=" . urlencode($vgallery_name) . "&path=" . urlencode($full_path) . "&extype=vgallery_nodes&adv=1&";
	$oGrid_node->addit_record_param = "type=node&vname=" . urlencode($vgallery_name) . "&path=" . urlencode($full_path) . "&extype=vgallery_nodes&adv=1&";
	//$oGrid_node->grid_disposition_options["button"]["wrap_col"] = true;
	$oGrid_node->user_vars["ajax_denied"] = $arrAjaxDenied;
	$oGrid_node->user_vars["force_picture_link"] = $force_picture_link;
	$oGrid_node->user_vars["force_picture_ico_spacer"] = $force_picture_ico_spacer;
	$oGrid_node->user_vars["disable_dialog_in_edit"] = $disable_dialog_in_edit;
	$oGrid_node->user_vars["sql_field"] = $arrFormField["sql"];
	$oGrid_node->user_vars["show_ID"] = $show_ID;
	$oGrid_node->user_vars["show_isbn"] = $show_isbn;
	
	

	if($drag_sort_node_enabled) {
		$oGrid_node->widget_deps[] = array(
	        "name" => "labelsort"
	        , "options" => array(
	              &$oGrid_node
	            , array(
	                "resource_id" => "vgallery_nodes"
	                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
	            )
	        )
	    );

	    $oGrid_node->widget_deps[] = array(
	        "name" => "dragsort"
	        , "options" => array(
	              &$oGrid_node
	            , array(
	                "resource_id" => "vgallery_nodes"
	                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
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
		$oGrid_node->display_edit_url = AREA_VGALLERY_SHOW_MODIFY && $allow_insert_nodes;
		$oGrid_node->display_new = AREA_VGALLERY_SHOW_ADDNEW && $allow_insert_nodes;
		$oGrid_node->display_delete_bt = AREA_VGALLERY_SHOW_DELETE && $allow_insert_nodes;

		if(check_function("system_ffgrid_process_customize_field_button")) {
			system_ffgrid_process_customize_field_button($oGrid_node, "vgallery_node_fields", array("real_path_info" => $cm->real_path_info
																						, "path_info" => $cm->path_info));
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
		                            ? " AND vgallery.ID = " . $db_gallery->toSql($ID_vgallery, "Number") . "
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
									AND vgallery_nodes.ID_vgallery = " . $db_gallery->toSql($ID_vgallery, "Number") . "
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
									AND vgallery_nodes.ID_vgallery = " . $db_gallery->toSql($ID_vgallery, "Number") . "
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
									AND vgallery_nodes.ID_vgallery = " . $db_gallery->toSql($ID_vgallery, "Number") . "
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
									AND vgallery_nodes.ID_vgallery = " . $db_gallery->toSql($ID_vgallery, "Number") . "
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

		        $oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
				if($force_picture_ico_spacer) {
		            $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . THEME_INSET . "/images/spacer.gif";
				} else {
				    $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[real_parent_VALUEPATH]/[name_VALUE]/[_FILENAME_]";
				}	        
		    }
		    $oField->base_type = $field_value["ff_extended_type"];
		    $oField->encode_entities = false;
		    $oField->user_vars = $arrFormField[$field_key];
/*
		    if($field_value["sort_default"]) {
	    		$oField->order_SQL = $field_name;
				if($drag_sort_node_enabled) {
					$oField->order_SQL = " `order`" . (strlen($oField->order_SQL) ? ", " . $oField->order_SQL : "");
				}	    
			}*/
		    $oGrid_node->addContent($oField); 
	    } 
	} else {
  
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
	// Visualizzazione   
	if(strpos($cm->path_info, VG_SITE_RESTRICTED) === 0) {
	   /* if(AREA_VGALLERY_SHOW_RELATIONSHIP) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "relationships";
	        if(0 || $_REQUEST["XHR_DIALOG_ID"]) {
	            $oButton->form_action_url = ""; //impostato nell'evento
	            $oButton->jsaction = "";
	        } else {
		        $oButton->action_type = "gotourl";
		        $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/relationship?[KEYS]" . $oGrid_node->addit_record_param . "ret_url=" . urlencode($cm->oPage->getRequestUri());
			}
	        $oButton->aspect = "link";
			//$oButton->image = "relationships.png";
			$oButton->label = ffTemplate::_get_word_by_code("relationships");
	        $oGrid_node->addGridButton($oButton);
	    } */
	    if(AREA_SEO_SHOW_MODIFY) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "seo";
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
			//$oButton->image = "seo.png";
			$oButton->label = ffTemplate::_get_word_by_code("seo");
			$oButton->display_label = false;
	        $oGrid_node->addGridButton($oButton);
	    }
	    if(ENABLE_STD_PERMISSION) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "permissions"; 
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
			//$oButton->image = "permissions.png";
			$oButton->label = ffTemplate::_get_word_by_code("permissions");
			$oButton->display_label = false;
	        $oGrid_node->addGridButton($oButton, "last");
	    }
	    if(AREA_PROPERTIES_SHOW_MODIFY) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "properties"; 
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
	        //$oButton->image = "layout_setting.png";                           
			$oButton->label = ffTemplate::_get_word_by_code("properties");
			$oButton->display_label = false;
	        $oGrid_node->addGridButton($oButton, "last");
	    }    
	} elseif(strpos($cm->path_info, VG_SITE_MANAGE) === 0) {
		ecommerce_ffGrid_product($oGrid_node, "node");    
	} else {
	   /* if(AREA_VGALLERY_SHOW_RELATIONSHIP) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "relationships";
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
	        $oButton->label = ffTemplate::_get_word_by_code("relationships");
	        //$oButton->image = "relationships.png";
	        $oGrid_node->addGridButton($oButton);
	    } */
	    if(AREA_VGALLERY_SHOW_SEO) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "seo";
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
	        $oButton->label = ffTemplate::_get_word_by_code("seo");
	        //$oButton->image = "seo.png";
			$oButton->display_label = false;
	        $oGrid_node->addGridButton($oButton);
	    }
	    if(AREA_VGALLERY_SHOW_PERMISSION && ENABLE_STD_PERMISSION) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "permissions";
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
	        $oButton->label = ffTemplate::_get_word_by_code("permissions");
	        //$oButton->image = "permissions.png";
			$oButton->display_label = false;
	        $oGrid_node->addGridButton($oButton, "last");
	    }
	    if(AREA_PROPERTIES_SHOW_MODIFY) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "properties";
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
	        $oButton->label = ffTemplate::_get_word_by_code("properties");
	        //$oButton->image = "layout_setting.png";
			$oButton->display_label = false;
	        $oGrid_node->addGridButton($oButton, "last");
	    }    
	    if(AREA_ECOMMERCE_SHOW_MODIFY && $enable_ecommerce) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "ecommerce"; 
	        $oButton->class = cm_getClassByFrameworkCss("cog", "icon");
	        $oButton->form_action_url = ""; //impostato nell'evento
	        $oButton->jsaction = "";
	        $oButton->aspect = "link";
			//$oButton->image = "shopping-cart.png";
	        $oButton->label = ffTemplate::_get_word_by_code("shopping-cart");
			$oButton->display_label = false;
	        $oGrid_node->addGridButton($oButton, "last");
	    }    
	}
	
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "clone"; 
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("vgallery_clone");
	$oButton->display_label = false;
	$oGrid_node->addGridButton($oButton);
	
	if(AREA_VGALLERY_SHOW_VISIBLE) {
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "visible";
	    $oButton->action_type = "gotourl";
	    $oButton->url = "";
	    $oButton->aspect = "link";
	    $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
		$oButton->display_label = false;
	    $oGrid_node->addGridButton($oButton, "last");
	} 

	if(MASTER_CONTROL && !$simple_interface && strpos($cm->path_info, VG_SITE_ADMIN) === 0) {
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "public";
		$oButton->action_type = "gotourl";
		$oButton->url = "";
		$oButton->aspect = "link";
		$oButton->display_label = false;
		$oGrid_node->addGridButton($oButton, "last");
	}

	if($allow_dir) {
		$cm->oPage->addContent($oGrid_node, "rel", null, array("title" => $vgallery_title));
	} else {
		$cm->oPage->addContent($oGrid_node); 		
	}

	if($allow_dir) {
		check_function("set_generic_tags");

	    $oGrid_dir = ffGrid::factory($cm->oPage);
	    //$oGrid_dir->full_ajax = true;
	    $oGrid_dir->ajax_addnew = !$disable_dialog_in_edit && !$denied_insert_ajax_dir;
		$oGrid_dir->ajax_delete = true;
		$oGrid_dir->ajax_search = true;
	    $oGrid_dir->id = $grid_dir_id;
	    $oGrid_dir->title = ""; //ffTemplate::_get_word_by_code("vgallery_dir" . $vgallery_title);
	    $oGrid_dir->source_SQL = (is_array($arrSQL["dir"]["union"]["pre"]) && count($arrSQL["dir"]["union"]["pre"])
	                            		? "(" . implode(") UNION (", $arrSQL["dir"]["union"]["pre"]) . ") UNION"
	                            		: ""
			                    ) . "
			                    (SELECT DISTINCT 
	                                vgallery_nodes.*
	                                , vgallery_nodes.parent AS real_parent
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
									(vgallery_nodes.parent = " . $db_gallery->toSql($cm->real_path_info)  . " 
						                OR vgallery_nodes.parent LIKE '" . $db_gallery->toSql($cm->real_path_info . "/", "Text", false)  . "%'
						                OR (vgallery_nodes.parent = '/' AND vgallery_nodes.name = " . $db_gallery->toSql($vgallery_name)  . ")
						            )
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
	    $oGrid_dir->order_default = (strlen($sort_dir_default)
	                                ? $sort_dir_default 
	                                : "ID");
	    $oGrid_dir->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info .  "/modify";
	    $oGrid_dir->record_id = $grid_dir_id . "Modify";
	    $oGrid_dir->resources[] = $oGrid_dir->record_id;
	    //$oGrid_dir->resources_set = $oGrid_dir->resources; 
	    $oGrid_dir->use_search = true;
	    //&ftype=" . urlencode($arrAllowedType["dir"][0]) . "
	    $oGrid_dir->addEvent("on_before_parse_row", "VGalleryDirModify_on_before_parse_row");
	    $oGrid_dir->addit_insert_record_param = "type=dir&vname=" . urlencode($vgallery_name) . "&path=" . urlencode($full_path) . "&extype=vgallery_nodes&adv=1&";
	    $oGrid_dir->addit_record_param = "type=dir&vname=" . urlencode($vgallery_name) . "&path=" . urlencode($full_path) . "&extype=vgallery_nodes&adv=1&";
	    $oGrid_dir->user_vars["ajax_denied"] = $arrAjaxDenied;
	    $oGrid_dir->user_vars["disable_dialog_in_edit"] = $disable_dialog_in_edit;
	    $oGrid_dir->user_vars["sql_field"] = $arrFormField["sql"];

		if($drag_sort_dir_enabled) {
			$oGrid_dir->widget_deps[] = array(
		        "name" => "labelsort"
		        , "options" => array(
		              &$oGrid_dir
		            , array(
		                "resource_id" => "vgallery_dir"
		                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
		            )
		        )
		    );

		    $oGrid_dir->widget_deps[] = array(
		        "name" => "dragsort"
		        , "options" => array(
		              &$oGrid_dir
		            , array(
		                "resource_id" => "vgallery_dir"
		                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
		            )
		            , "ID"
		        )
		    );
		}

	    
	    if(strpos($cm->path_info, VG_SITE_MANAGE) === 0) {
	        $oGrid_dir->display_edit_bt = false;
	        $oGrid_dir->display_edit_url = false;
	        $oGrid_dir->display_delete_bt = false;
	        $oGrid_dir->display_new = false;
	    } else {
	        $oGrid_dir->display_edit_bt = false;
	        $oGrid_dir->display_edit_url = AREA_VGALLERY_DIR_SHOW_MODIFY;
	        $oGrid_dir->display_delete_bt = AREA_VGALLERY_DIR_SHOW_DELETE;
	        $oGrid_dir->display_new = AREA_VGALLERY_DIR_SHOW_ADDNEW && $allow_insert;

			if(check_function("system_ffgrid_process_customize_field_button")) {
				system_ffgrid_process_customize_field_button($oGrid_dir, "vgallery_dir_fields", array("real_path_info" => $cm->real_path_info
																							, "path_info" => $cm->path_info));
			}        
	    }

	    // Chiave
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
		$oField->order_SQL = implode(", ", $arrOrder_dir_SQL);
	    $oGrid_dir->addKeyField($oField);
	    /*
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "real_parent";
	    $oField->base_type = "Text";
	    $oGrid_dir->addKeyField($oField);
	    
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "name";
	    $oField->base_type = "Text";
	    $oGrid_dir->addKeyField($oField);*/

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
			        $oField->file_storing_path = DISK_UPDIR;
			        $oField->file_show_delete = false;

			        $oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]"; 
			        $oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
					if($force_picture_ico_spacer) {
			            $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . THEME_INSET . "/images/spacer.gif";
			            $oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . THEME_INSET . "/images/spacer.gif";
					} else {
					    $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[real_parent_VALUEPATH]/[name_VALUE]/[_FILENAME_]";
					    $oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[real_parent_VALUEPATH]/[name_VALUE]/[_FILENAME_]";
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
	     // Visualizzazione
	    if(strpos($cm->path_info, VG_SITE_RESTRICTED) === 0) {
	     /*   if(AREA_VGALLERY_SHOW_RELATIONSHIP) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "relationships"; 
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "relationships.png";
				$oButton->label = ffTemplate::_get_word_by_code("relationships");
	            $oGrid_dir->addGridButton($oButton);
	        }  */
	        if(AREA_VGALLERY_SHOW_SEO) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "seo"; 
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "seo.png";
				$oButton->label = ffTemplate::_get_word_by_code("seo");
				$oButton->display_label = false;
	            $oGrid_dir->addGridButton($oButton);
	        }
	        if(AREA_VGALLERY_SHOW_PERMISSION && ENABLE_STD_PERMISSION) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "permissions"; 
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "permissions.png";
				$oButton->label = ffTemplate::_get_word_by_code("permissions");
				$oButton->display_label = false;
	            $oGrid_dir->addGridButton($oButton, "last");
	        }
	        if(AREA_PROPERTIES_SHOW_MODIFY) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "properties"; 
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "layout_setting.png";
				$oButton->label = ffTemplate::_get_word_by_code("properties");
				$oButton->display_label = false;
	            $oGrid_dir->addGridButton($oButton, "last");
	        }    
	    } elseif(strpos($cm->path_info, VG_SITE_MANAGE) === 0) { 
			ecommerce_ffGrid_product($oGrid_dir, "dir");
	    } else {
	      /*  if(AREA_VGALLERY_SHOW_RELATIONSHIP) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "relationships"; 
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "relationships.png";
				$oButton->label = ffTemplate::_get_word_by_code("relationships");
	            $oGrid_dir->addGridButton($oButton);
	        }  */
	        if(AREA_VGALLERY_SHOW_SEO) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "seo";
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "seo.png";
				$oButton->label = ffTemplate::_get_word_by_code("seo");
				$oButton->display_label = false;
	            $oGrid_dir->addGridButton($oButton);
	        }
	        if(AREA_VGALLERY_SHOW_PERMISSION && ENABLE_STD_PERMISSION) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "permissions"; 
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "permissions.png";
				$oButton->label = ffTemplate::_get_word_by_code("permissions");
				$oButton->display_label = false;
	            $oGrid_dir->addGridButton($oButton, "last");
	        }
	        if(AREA_PROPERTIES_SHOW_MODIFY) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "properties";
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "layout_setting.png";
				$oButton->label = ffTemplate::_get_word_by_code("properties");
				$oButton->display_label = false;
	            $oGrid_dir->addGridButton($oButton, "last");
	        }    
	        if(AREA_ECOMMERCE_SHOW_MODIFY && $enable_ecommerce) {
	            $oButton = ffButton::factory($cm->oPage);
	            $oButton->id = "ecommerce";
	            $oButton->class = "shopping-cart-all";
		        $oButton->form_action_url = ""; //impostato nell'evento
		        $oButton->jsaction = "";
	            $oButton->aspect = "link";
	            //$oButton->image = "shopping-cart.png";
	            $oButton->label = ffTemplate::_get_word_by_code("shopping_cart");
				$oButton->display_label = false;
	            $oGrid_dir->addGridButton($oButton, "last");
	        }    
	    }
	    
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "clone"; 
	    $oButton->action_type = "gotourl";
	    $oButton->url = "";
	    $oButton->aspect = "link";
	    $oButton->label = ffTemplate::_get_word_by_code("vgallery_clone");
		$oButton->display_label = false;
	    $oGrid_dir->addGridButton($oButton);
	    
	    if(AREA_VGALLERY_SHOW_VISIBLE) {
	        $oButton = ffButton::factory($cm->oPage);
	        $oButton->id = "visible";
	        $oButton->action_type = "gotourl";
	        $oButton->url = "";
	        $oButton->aspect = "link";
	        $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
	        $oButton->template_file = "ffButton_link_image.html";                           
	        $oGrid_dir->addGridButton($oButton, "last");
	    }
	    
	    if(MASTER_CONTROL && !$simple_interface) {
		    $oButton = ffButton::factory($cm->oPage);
		    $oButton->id = "public";
		    $oButton->action_type = "gotourl";
		    $oButton->url = "";
		    $oButton->aspect = "link";
			$oButton->display_label = false;
		    $oGrid_dir->addGridButton($oButton, "last");
		}     
	    $cm->oPage->addContent($oGrid_dir, "rel", null, array("title" => $vgallery_title . " - " . ffTemplate::_get_word_by_code("vgallery_dir_title"))); 
	}
}



function VGalleryDirModify_on_before_parse_row($component) {
	$cm = cm::getInstance();

    if($component->db[0]->getField("is_clone", "Number", true) > 0) {
		$component->row_class = "clone";
    } else {
		$component->row_class = "";
    }

	if($component->user_vars["disable_dialog_in_edit"] || (isset($component->user_vars["ajax_denied"][$component->db[0]->getField("ID_type", "Number", true)]) && $component->user_vars["ajax_denied"][$component->db[0]->getField("ID_type", "Number", true)]))
		$component->full_ajax = false;
	else
		$component->full_ajax = true;
	
    if(isset($component->grid_buttons["visible"])) {
	    if($component->db[0]->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit"; 
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible";
            }   
	    } else {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit";     
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible";
            }    
	    }
	}

    if(isset($component->grid_buttons["public"])) {
	    if($component->db[0]->getField("public", "Number", true)) {
	        //$component->grid_buttons["public"]->image = "visible.png";
            $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon");
            $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_no_public");
            $component->grid_buttons["public"]->action_type = "submit"; 
            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["public"]->action_type = "gotourl";
                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0&frmAction=setpublic";
            }   
	    } else {
            //$component->grid_buttons["public"]->image = "notvisible.png";
            $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon", array("transparent"));
            $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_public");
            $component->grid_buttons["public"]->action_type = "submit";     
            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["public"]->action_type = "gotourl";
                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1&frmAction=setpublic";
            }    
	    }
	}	
	
	if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
		$file_permission = get_file_permission($component->db[0]->getField("full_path")->getValue(), "vgallery_nodes");
	if(!check_mod($file_permission, 2, false)) {
		$component->display_edit_bt = false;
    	$component->display_edit_url = false;
    	$component->visible_delete_bt = false;

    	foreach($component->grid_buttons AS $button_key => $button_value) {
    			$component->grid_buttons[$button_key]->visible = false;
		}
	} else {
		$component->display_edit_bt = false;
    	$component->display_edit_url = true;
    	$component->visible_delete_bt = true; 

    	foreach($component->grid_buttons AS $button_key => $button_value) {
    			$component->grid_buttons[$button_key]->visible = true;
		}
		if(isset($component->grid_buttons["clone"])) {
	        $component->grid_buttons["clone"]->action_type = "submit"; 
	        $component->grid_buttons["clone"]->form_action_url = $component->grid_buttons["clone"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["clone"]->parent[0]->addit_record_param;
	        if($_REQUEST["XHR_DIALOG_ID"]) {
	            $component->grid_buttons["clone"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	        } else {
	            $component->grid_buttons["clone"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	            //$component->grid_buttons["visible"]->action_type = "gotourl";
	            //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible";
			}   
			$component->grid_buttons["clone"]->visible = true;
		}   /*
		if(isset($component->grid_buttons["relationships"])) {
	        if($component->grid_buttons["relationships"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyRelationship_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/" . $component->db[0]->getField("vgallery_name")->getValue() . "/relationship"
	                            . "?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("relationships")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["relationships"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyRelationship_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["relationships"]->visible = true;
		} */
		if(isset($component->grid_buttons["seo"])) {
	        if($component->grid_buttons["seo"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . VG_SITE_ADMIN . "/utility/seo/modify"
	                            . "?key=" . $component->key_fields["ID"]->getValue() 
	                    , "title" => ffTemplate::_get_word_by_code("seo")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["seo"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["seo"]->visible = true;
		} 
		if(isset($component->grid_buttons["permissions"])) {
	        if($component->grid_buttons["permissions"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyPermission_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/" . $component->db[0]->getField("vgallery_name")->getValue() . "/permission"
	                            . "?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("permissions")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["permissions"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyPermission_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["permissions"]->visible = true;
		}
		if(isset($component->grid_buttons["properties"])) {
	        if($component->grid_buttons["properties"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyProperties_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/" . $component->db[0]->getField("vgallery_name")->getValue() . "/properties"
	                            . "?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("properties")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["properties"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyProperties_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["properties"]->visible = true;
		} 
		if(isset($component->grid_buttons["ecommerce"])) {
	        if($component->grid_buttons["ecommerce"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyEcommerce_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . VG_SITE_MANAGE . "/vgallery/" . $component->db[0]->getField("vgallery_name")->getValue()
	                            . "/ecommerce/all?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("ecommerce_all_modify")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["ecommerce"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyEcommerce_" . $component->key_fields["ID"]->getValue() . "')";
	        }

			$component->grid_buttons["ecommerce"]->visible = true;
		}
	}
	//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
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
			    $sSQL = "SELECT " . $tmpField["data_source"] . ".*
				        FROM
				            " . $tmpField["data_source"] . "
				        WHERE
				            " . $tmpField["data_source"] . ".ID_nodes IN (" . $db->toSql(implode(",", array_keys($arrNodes)), "Text", false) . ")";
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

function VGalleryNodesModify_on_before_parse_row($component) {
    $cm = cm::getInstance();

    $row_class = "";
    if($component->db[0]->getField("is_clone", "Number", true) > 0)
		$row_class .= " clone";
    
	if(!$component->db[0]->getField("visible", "Number", true))
		$row_class .= " novisible";
	
	$component->row_class = trim($row_class);
	
	if($component->user_vars["disable_dialog_in_edit"] || (isset($component->user_vars["ajax_denied"][$component->db[0]->getField("ID_type", "Number", true)]) && $component->user_vars["ajax_denied"][$component->db[0]->getField("ID_type", "Number", true)]))
		$component->full_ajax = false;
	else
		$component->full_ajax = true;
	//ffErrorHandler::raise("ASDD", E_USER_ERROR, null, get_defined_vars());
	$first_picture = true;
	
	$resolve_relationship = check_function("resolve_relationship");

	$ID_node = $component->key_fields["ID"]->getValue();
	$full_path = $component->db[0]->getField("full_path", "Text", true);

	if(strpos($full_path, "-" . $ID_node) === false && is_dir(DISK_UPDIR . $full_path . "-" . $ID_node)) {
		$img_path = $full_path . "-" . $ID_node;
	} else {
		$img_path = $full_path;
	}

	if(is_file(DISK_UPDIR . $img_path . ".jpg")) {
		$cover = $img_path . ".jpg";
	} else {
		$tmp_img = glob(DISK_UPDIR . $img_path . "/*");
		if(is_array($tmp_img) && count($tmp_img)) {
			sort($tmp_img);
			foreach($tmp_img AS $tmp_img_key => $tmp_img_value) {
				if(is_file($tmp_img_value)) {
					$mime = ffMimeType(DISK_UPDIR . $img_path  . "/" . basename($tmp_img_value));
					if(strpos($mime, "image") !== false) {
						$cover = $img_path  . "/". basename($tmp_img_value);
						break;
					}
				}
			}
		}
	}		
	/*
	$tmp_img = glob(DISK_UPDIR . $component->db[0]->getField("full_path", "Text", true)  . "/*");
	if(is_array($tmp_img) && count($tmp_img)) {
		sort($tmp_img);
		foreach($tmp_img AS $tmp_img_key => $tmp_img_value) {
			if(is_file($tmp_img_value)) {
				$mime = ffMimeType(DISK_UPDIR . $component->db[0]->getField("full_path", "Text", true)  . "/" . basename($tmp_img_value));
	            if(strpos($mime, "image") !== false) {
					$cover = $component->db[0]->getField("full_path", "Text", true)  . "/". basename($tmp_img_value);
					break;
				}
			}
		}
	}*/	
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
				if((!(strlen($image_path) && is_file(realpath(DISK_UPDIR . $image_path))))) {
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
	                $component->grid_fields[$grid_key]->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . THEME_INSET . "/images/spacer.gif";
	                $component->grid_fields[$grid_key]->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . THEME_INSET . "/images/spacer.gif";
				} else {
	        		$component->grid_fields[$grid_key]->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb" . ffCommon_dirname($image_path) . "/[_FILENAME_]";
	        		$component->grid_fields[$grid_key]->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb" . ffCommon_dirname($image_path) . "/[_FILENAME_]";
				}
			} else {
				$component->grid_fields[$grid_key]->setValue("");
	        	$component->grid_fields[$grid_key]->control_type = "";
			}
		}
	} reset($component->grid_fields);
	
	if(isset($component->grid_fields["card"]) && check_function("get_vgallery_card")) {
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
	
	
	
        /*
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $oButton->form_action_url = $cm->oPage->site_path . ffCommon_dirname(ffCommon_dirname($cm->oPage->page_path)) . "/payments/" . $operation . "?payment=[ID_VALUE]&" . $oGrid_dir->addit_record_param . "ret_url=" . urlencode($cm->oPage->getRequestUri());
            //$oButton->jsaction = "javascript:ff.ffPage.dialog.doAjax('[[XHR_DIALOG_ID]]', 'nopay', 'Payments', undefined, undefined, 'Payments_innergrid', '[[frmAction_url]]')";
            $oButton->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'nopay', 'component' : 'Payments', 'section' : 'GridData', 'url' : '[[frmAction_url]]'});";
        } else {
            $oButton->action_type = "gotourl";
            $oButton->url = $cm->oPage->site_path . ffCommon_dirname(ffCommon_dirname($cm->oPage->page_path)) . "/payments/" . $operation . "?payment=[ID_VALUE]&" . $oGrid_dir->addit_record_param . "frmAction=nopay&ret_url=" . urlencode($cm->oPage->getRequestUri());
        }
         */
    if(isset($component->grid_buttons["visible"])) {
	    if($component->db[0]->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
	        $component->grid_buttons["visible"]->action_type = "submit"; 
	        $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0";
	        if($_REQUEST["XHR_DIALOG_ID"]) {
	            $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	        } else {
	            $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	            //$component->grid_buttons["visible"]->action_type = "gotourl";
	            //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible";
			}   
	    } else {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
	        $component->grid_buttons["visible"]->action_type = "submit";     
	        $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1";
	        if($_REQUEST["XHR_DIALOG_ID"]) {
	            $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	        } else {
	        	$component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
	            //$component->grid_buttons["visible"]->action_type = "gotourl";
	            //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible";
			}    
	    }
	}
    
    if(isset($component->grid_buttons["public"])) {
	    if($component->db[0]->getField("public", "Number", true)) {
	        //$component->grid_buttons["public"]->image = "visible.png";
            $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon");
            $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_no_public");
            $component->grid_buttons["public"]->action_type = "submit"; 
            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["public"]->action_type = "gotourl";
                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0&frmAction=setpublic";
            }   
	    } else {
            //$component->grid_buttons["public"]->image = "notvisible.png";
            $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon", array("transparent"));
            $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_public");
            $component->grid_buttons["public"]->action_type = "submit";     
            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1";
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["public"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["public"]->action_type = "gotourl";
                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1&frmAction=setpublic";
            }    
	    }
	}
	
	if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
		$file_permission = get_file_permission($component->db[0]->getField("full_path")->getValue(), "vgallery_nodes");
	if(!check_mod($file_permission, 2, false)) {
		$component->display_edit_bt = false;
    	$component->display_edit_url = false;
    	$component->visible_delete_bt = false;

    	foreach($component->grid_buttons AS $button_key => $button_value) {
    			$component->grid_buttons[$button_key]->visible = false;
		}
	} else {
		$component->display_edit_bt = false;
    	$component->display_edit_url = true;
    	$component->visible_delete_bt = true; 

    	/*foreach($component->grid_buttons AS $button_key => $button_value) {
    			$component->grid_buttons[$button_key]->visible = true;
		}*/
		
		if(isset($component->grid_buttons["clone"])) {
	        $component->grid_buttons["clone"]->action_type = "submit"; 
	        $component->grid_buttons["clone"]->form_action_url = $component->grid_buttons["clone"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["clone"]->parent[0]->addit_record_param;
	        if($_REQUEST["XHR_DIALOG_ID"]) {
	            $component->grid_buttons["clone"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	        } else {
	            $component->grid_buttons["clone"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	            //$component->grid_buttons["visible"]->action_type = "gotourl";
	            //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible";
			}   
			$component->grid_buttons["clone"]->visible = true;
		}  /*
		if(isset($component->grid_buttons["relationships"])) {
	        if($component->grid_buttons["relationships"]->action_type == "submit") { 
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyRelationship_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/" . $component->db[0]->getField("vgallery_name")->getValue() . "/relationship"
	                            . "?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("relationships")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["relationships"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyRelationship_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["relationships"]->visible = true;
		}   */
		if(isset($component->grid_buttons["seo"])) {
	        if($component->grid_buttons["seo"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . VG_SITE_ADMIN . "/utility/seo/modify"
	                            . "?key=" . $component->key_fields["ID"]->getValue() 
	                    , "title" => ffTemplate::_get_word_by_code("seo")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["seo"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifySeo_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["seo"]->visible = true;
		} 
		if(isset($component->grid_buttons["permissions"])) {
	        if($component->grid_buttons["permissions"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyPermission_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/" . $component->db[0]->getField("vgallery_name")->getValue() . "/permission"
	                            . "?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("permissions")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["permissions"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyPermission_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["permissions"]->visible = true;
		}
		if(isset($component->grid_buttons["properties"])) {
	        if($component->grid_buttons["properties"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyProperties_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . $cm->oPage->page_path . "/" . $component->db[0]->getField("vgallery_name")->getValue() . "/properties"
	                            . "?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("properties")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["properties"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyProperties_" . $component->key_fields["ID"]->getValue() . "')";
	        }
			$component->grid_buttons["properties"]->visible = true;
		} 
		if(isset($component->grid_buttons["ecommerce"])) {
	        if($component->grid_buttons["ecommerce"]->action_type == "submit") {
	            $cm->oPage->widgetLoad("dialog");
	            $cm->oPage->widgets["dialog"]->process(
	                 $component->id . "_modifyEcommerce_" . $component->key_fields["ID"]->getValue()
	                 , array(
	                    "tpl_id" => $component->id
	                    //"name" => "myTitle"
	                    , "url" => FF_SITE_PATH . VG_SITE_MANAGE . "/vgallery/" . $component->db[0]->getField("vgallery_name")->getValue()
	                            . "/ecommerce/all?keys[ID]=" . $component->key_fields["ID"]->getValue() 
	                            . "&" . $component->addit_record_param
	                    , "title" => ffTemplate::_get_word_by_code("ecommerce_all_modify")
	                    , "callback" => ""
	                    , "class" => ""
	                    , "params" => array()
	                )
	                , $cm->oPage
	            );
	            $component->grid_buttons["ecommerce"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_modifyEcommerce_" . $component->key_fields["ID"]->getValue() . "')";
	        }

			$component->grid_buttons["ecommerce"]->visible = true;
		}
	}
}