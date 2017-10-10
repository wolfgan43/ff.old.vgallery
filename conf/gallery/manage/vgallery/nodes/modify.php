<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$simple_interface = false;
$force_display_record = false;
$count_editor = 0;

$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

if($_REQUEST["src"] == "vgallery")
	unset($_REQUEST["src"]);
	
if(check_function("get_schema_fields_by_type"))
	$src = get_schema_fields_by_type($src_type);

if(!isset($_REQUEST["keys"]["ID"]) && isset($_REQUEST["fullpath"]) && strlen($_REQUEST["fullpath"])) {
    $fullpath = stripslash($cm->real_path_info) . $_REQUEST["fullpath"];
    $sSQL = "SELECT `" . $src["table"] . "`.*
                , " . $src["sql"]["select"]["vgallery_name"] . " AS vgallery_name 
                , " . $src["sql"]["select"]["is_dir"] . " AS is_dir
            FROM `" . $src["table"] . "` 
            WHERE `" . $src["table"] . "`.`" . $src["field"]["parent"] . "` = " . $db_gallery->toSql(ffCommon_dirname($fullpath)) . "
                AND `" . $src["table"] . "`.`" . $src["field"]["name"] . "` = " . $db_gallery->toSql(basename($fullpath)) . "
                " . $src["sql"]["where"]["public"];
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number", true);
        if(!isset($_REQUEST["vname"])) {
            $_REQUEST["vname"] = $db_gallery->getField("vgallery_name", "Text", true);
        }
        if(!isset($_REQUEST["type"])) {
            $_REQUEST["type"] = ($db_gallery->getField("is_dir", "Number", true) > 0 ? "dir" : "node");
        }
        if(!isset($_REQUEST["extype"])) {
            $_REQUEST["extype"] = $src["table"];
        }
        if(!isset($_REQUEST["path"])) {
            $_REQUEST["path"] = $db_gallery->getField("parent", "Text", true);
        }
        if(!isset($_REQUEST["adv"])) {
            $_REQUEST["adv"] = 1;        
        }
    }
}

$adv_params = (isset($_REQUEST["adv"])
                ? $_REQUEST["adv"]
                : AREA_VGALLERY_TYPE_SHOW_MODIFY);

/**
* Check Owner
*/
if (!(AREA_VGALLERY_SHOW_MODIFY)) { 
    $sSQL = "SELECT 
                CONCAT(IF(`" . $src["table"] . "`.`" . $src["field"]["parent"] . "` = '/', '', `" . $src["table"] . "`.`" . $src["field"]["parent"] . "`), '/', `" . $src["table"] . "`.`" . $src["field"]["name"] . "`) AS full_path
                , users_rel_vgallery.cascading AS cascading
                , users_rel_vgallery.visible AS visible
                , users_rel_vgallery.request AS request
                , " . $src["sql"]["select"]["vgallery_name"] . " AS vgallery_name 
            FROM users_rel_vgallery
                INNER JOIN `" . $src["table"] . "` ON `" . $src["table"] . "`.ID = users_rel_vgallery.ID_nodes
            WHERE users_rel_vgallery.uid = " . $db_gallery->tosql(get_session("UserNID"), "Number") . "
                AND '" . $db_gallery->tosql($cm->real_path_info, "Text", false) . "' LIKE CONCAT(IF(`" . $src["table"] . "`.`" . $src["field"]["parent"] . "` = '/', '', `" . $src["table"] . "`.`" . $src["field"]["parent"] . "`), '/', `" . $src["table"] . "`.`" . $src["field"]["name"] . "`, '%')
                " . $src["sql"]["where"]["public"] . "
            ORDER BY `" . $src["table"] . "`.`order`, `" . $src["table"] . "`.`ID`
            ";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        use_cache(false);

        if($db_gallery->numRows() > 1) {
            $strError = ffTemplate::_get_word_by_code("invalid_vgallery");
        } else {
            $limit_parent_path = $db_gallery->getField("full_path", "Text", true);
            $vgallery_force_visible = $db_gallery->getField("visible", "Number", true);
            
            if($db_gallery->getField("cascading", "Number", true)) {
                $cm->real_path_info = $limit_parent_path;
                $vgallery_force_name = "";
            } else {
                $_REQUEST["vname"] = $db_gallery->getField("vgallery_name", "Text", true);
                $_REQUEST["type"] = "node";
                $_REQUEST["extype"] = $src["table"];
                $_REQUEST["path"] = $limit_parent_path;

                $cm->real_path_info = $_REQUEST["path"];
                //$_REQUEST["ftype"] = "artista";
                
                $sSQL = "SELECT `" . $src["table"] . "`.* 
                        FROM `" . $src["table"] . "` 
                        WHERE `" . $src["table"] . "`.`" . $src["field"]["parent"] . "` LIKE '" . $db_gallery->tosql($db_gallery->getField("full_path"), "Text", false) . "%' 
                            AND 
                            (
                                `" . $src["table"] . "`.`" . $src["field"]["name"] . "` = " . $db_gallery->toSql(ffCommon_url_rewrite(get_session("UserID"))) . "
                            OR    
                                `" . $src["table"] . "`.owner = " . $db_gallery->toSql(get_session("UserNID"), "Number") . "
                            )
                            "; 
                $db_gallery->query($sSQL);
                if($db_gallery->nextRecord()) {
                    $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number", true);
                }

                $vgallery_force_name = get_session("UserID");
            } 
        }
        $simple_interface = true;
    } else {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }
}

if(!strlen($strError)) {
	$js = "";
    $user_permission = get_session("user_permission");
    $ID_vgallery_nodes = $_REQUEST["keys"]["ID"];
    $gallery_model = null;
    
    if($simple_interface && !$_REQUEST["XHR_DIALOG_ID"]) {
        $ret_url = urldecode($_REQUEST["ret_url"]);
    }    

	if($ID_vgallery_nodes > 0)
	{
		$vgallery_nodes_title = ffTemplate::_get_word_by_code("modify_" . $src["type"]);
        $sSQL = "SELECT `" . $src["table"] . "`.* 
					, " . $src["sql"]["select"]["vgallery_name"] . " AS vgallery_name 
					, " . $src["sql"]["select"]["is_dir"] . " AS is_dir
                FROM `" . $src["table"] . "` 
                WHERE `" . $src["table"] . "`.ID = " . $db_gallery->tosql($ID_vgallery_nodes, "Number");
        $db_gallery->query($sSQL);
        if($db_gallery->nextRecord()) {
			$vgallery_name              = $db_gallery->getField("vgallery_name", "Text", true);
            if($src["field"]["parent"])
            	$vgallery_parent_old        = $db_gallery->getField($src["field"]["parent"], "Text", true);
            if($src["field"]["smart_url"])
            	$vgallery_name_old          = $db_gallery->getField($src["field"]["smart_url"], "Text", true);
            if($src["field"]["title"])
            	$vgallery_meta_title        = $db_gallery->getField($src["field"]["title"], "Text", true);
            if($src["field"]["header"])
            	$vgallery_meta_header       = $db_gallery->getField($src["field"]["header"], "Text", true);
            if($src["field"]["description"])
            	$vgallery_meta_description  = $db_gallery->getField($src["field"]["description"], "Text", true);
			if($src["field"]["robots"])
            	$vgallery_meta_robots       = $db_gallery->getField($src["field"]["robots"], "Text", true);
            if($src["field"]["canonical"])
            	$vgallery_meta_canonical    = $db_gallery->getField($src["field"]["canonical"], "Text", true);
            if($src["field"]["meta"])
            	$vgallery_meta              = $db_gallery->getField($src["field"]["meta"], "Text", true);
            if($src["field"]["httpstatus"])
            	$vgallery_httpstatus        = $db_gallery->getField($src["field"]["httpstatus"], "Number", true);
            if($src["field"]["is_dir"])
            	$vgallery_is_dir            = $db_gallery->getField($src["field"]["is_dir"], "Number", true);
			if($src["field"]["visible"])
				$vgallery_visible           = $db_gallery->getField($src["field"]["visible"], "Number", true);

            $vgallery_type              = $db_gallery->getField($src["field"]["ID_type"], "Number", true);
            $highlight                  = $db_gallery->getField("highlight", "Text", true);
            if($highlight)
                $highlight              = explode(",", $highlight);
            
            if($src["field"]["clone"])
            	$vgallery_is_clone		= $db_gallery->getField($src["field"]["clone"], "Number", true);
            
            $type                       = ($vgallery_is_dir ? "dir" : "node");
			$path                       = $vgallery_parent_old;
			$vgallery_permalink        	= $db_gallery->getField($src["field"]["permalink"], "Text", true);
			
            $vgallery_nodes_title .= ": " . stripslash($vgallery_parent_old) . "/" . $vgallery_name_old;
		}    
	} else {
		$sSQL = "SELECT `" . $src["table"] . "`.* 
                FROM `" . $src["table"] . "` 
                WHERE 1
                ORDER BY `" . $src["table"] . "`.ID DESC";
        $db_gallery->query($sSQL);
        if($db_gallery->nextRecord()) {
        	$ID_vgallery_nodes_tmp = $db_gallery->getField("ID", "Number", true) + 1;
		}

		$vgallery_name = $_REQUEST["vname"];
		$type = $_REQUEST["type"];
		$path = $_REQUEST["path"];
		$field_type = $_REQUEST["ftype"];
	}
	
	if(check_function("get_table_support"))
		$tbl_supp = get_table_support();	
	
    /**
    * Init VGallerySettings
    */
	$arrLangMultiPairs = array();
    $page_field = array();
	$page_group = array();  

    if(!is_array($src["settings"]) && strlen($src["settings"]))
    {
	    if($tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]) {
			$src["settings"] = array(
        		"limit_type" 							=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["limit_type"]
        		, "limit_level" 						=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["limit_level"]
        		, "insert_on_lastlevel" 				=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["insert_on_lastlevel"]
        		, "ID_vgallery" 						=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["ID"]
        		, "enable_highlight" 					=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_highlight"]
        		, "show_owner_by_categories" 			=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["show_owner_by_categories"]
        		, "show_isbn" 							=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["show_isbn"]
        		, "enable_multilang" 					=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_multilang_visible"]
        		, "drag_sort_node_enabled" 				=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["drag_sort_node_enabled"]
        		, "drag_sort_dir_enabled" 				=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["drag_sort_dir_enabled"]
        		, "enable_tag" 							=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_tag"]
				, "enable_multi_cat" 					=> ($vgallery_is_dir
															? false
															: $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_multi_cat"]
														)	
				, "enable_place" 						=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_place"]
				, "enable_referer" 						=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_referer"]
				, "enable_priority" 					=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_priority"]
        		, "enable_tab" 							=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_tab"]
        		, "enable_model" 						=> true
        		, "enable_adv_group"					=> true
        		, "enable_adv_visible" 					=> true
        		, "name"								=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["name"]
        		, "enable_email_notify_on_insert"		=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_email_notify_on_insert"]
        		, "enable_email_notify_on_update"		=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["enable_email_notify_on_update"]
        		, "email_notify_show_detail"			=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["email_notify_show_detail"]
        		, "data_ext"							=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["data_ext"]
        		, "permalink_rule"						=> $tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["permalink_rule"]
        	);
			if($tbl_supp[$src["settings"]]["smart_url"][$vgallery_name]["use_user_as_prefix_in_fs"])
	            $src["settings"]["prefix_file_system"] = VG_SITE_USER . "/" . ffCommon_url_rewrite(get_session("UserID"));
	            
	        if($src["settings"]["enable_referer"] && $ID_vgallery_nodes > 0) {
	        	$sSQL = "SELECT vgallery_nodes.ID
	        			FROM vgallery_nodes
	        			WHERE vgallery_nodes.referer = " . $db_gallery->toSql($ID_vgallery_nodes, "Number");
	        	$db_gallery->query($sSQL);
	        	if($db_gallery->nextRecord()) {
	        		$vgallery_is_referer = $db_gallery->getField("ID", "Number", true);
	        	}
	        }
	    } else {
            $src["settings"] = array();
        }
	}

	if(is_array($tbl_supp[$src["type"] . "_type"]["smart_url"]) && count($tbl_supp[$src["type"] . "_type"]["smart_url"])) {
		if($src["settings"]["limit_type"])
    		$arrLimitType = explode(",", $src["settings"]["limit_type"]);	

		foreach($tbl_supp[$src["type"] . "_type"]["smart_url"] AS $type_name => $arrType) {
        	if(is_array($arrLimitType) && array_search($arrType["ID"], $arrLimitType) === false)
        		continue;
        	
        	$type_smart_url = ffCommon_url_rewrite($type_name);
			if($arrType["is_dir_default"])
                $arrAllowedType["dir"][$type_smart_url] = $arrType["ID"];
            else            
                $arrAllowedType["node"][$type_smart_url] = $arrType["ID"];

		}
	}

	/*
	$sSQL = "SELECT " . $src["type"] . "_type.* 
            FROM " . $src["type"] . "_type 
            WHERE 1
            ORDER BY " . $src["type"] . "_type.ID";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
    	if($src["settings"]["limit_type"])
    		$arrLimitType = explode(",", $src["settings"]["limit_type"]);
    
        do {
        	$ID_type = $db_gallery->getField("ID", "Number", true);
        	$type_smart_url = ffCommon_url_rewrite($db_gallery->getField("name", "Text", true));
        	$type_is_dir = $db_gallery->getField("is_dir_default", "Number", true);
        	
            if($type_is_dir)
                $arrType["dir"][$type_smart_url] = $ID_type;
            else            
                $arrType["node"][$type_smart_url] = $ID_type;

        	if(is_array($arrLimitType) && array_search($ID_type, $arrLimitType) === false)
        		continue;

            if($type_is_dir)
                $arrAllowedType["dir"][$type_smart_url] = $ID_type;
            else            
                $arrAllowedType["node"][$type_smart_url] = $ID_type;
        } while($db_gallery->nextRecord());
    }*/

	if(!$ID_vgallery_nodes) {
		if(isset($arrAllowedType[$type][$field_type]) && strlen($arrAllowedType[$type][$field_type])) {
            $default_field_type = $arrAllowedType[$type][$field_type];
            $vgallery_is_dir = ($type == "dir" ? true : false);
        } else {
            if($simple_interface) {
                if(is_array($arrAllowedType["node"]) && count($arrAllowedType["node"])) {
                    $default_field_type = current($arrAllowedType["node"]);
                } else {
                    $default_field_type = "";
                }
                $vgallery_is_dir = false;
            } else {
                if(is_array($arrAllowedType[$type]) && count($arrAllowedType[$type]) == 1) {
                    $default_field_type = current($arrAllowedType[$type]);
                } else {
                    $default_field_type = "";
                }
                $vgallery_is_dir = ($type == "dir" ? true : false);
            }
        }
        
        $vgallery_type = $default_field_type;

        if($vgallery_is_dir)
            $vgallery_nodes_title = ffTemplate::_get_word_by_code("addnew_" . $src["type"] . "_dir");
        else
            $vgallery_nodes_title = ffTemplate::_get_word_by_code("addnew_" . $src["type"] . "_nodes");
    }	
	
    $vgallery_record_id = ($type == "dir"
                        ? "VGalleryDirModify"
                        : "VGalleryNodesModify"
                    );   

	switch($src["type"] . "-" . $type) {
		case "anagraph-dir":
		case "anagraph-node":
			$src["sql"]["query"]["parent"] = "	(
											SELECT '/' AS full_path
											, " . $db_gallery->toSql(ffTemplate::_get_word_by_code("nothing")) . " AS display_path
										) UNION (
											SELECT DISTINCT 
		                                        CONCAT('/', anagraph_categories.name) AS full_path
		                                        , anagraph_categories.name AS display_path
		                                    FROM anagraph_categories
		                                    WHERE 1
												" . ($src["settings"]["insert_on_lastlevel"]
		                                            ? " AND LENGTH(CONCAT('/', anagraph_categories.name)) - LENGTH(REPLACE(CONCAT('/', anagraph_categories.name), '/', '')) >= " . $db_gallery->tosql($src["settings"]["limit_level"], "Number")
		                                            : ""
		                                        ) . "
		                                        " . (strlen($limit_parent_path)
		                                            ? " AND CONCAT('/', anagraph_categories.name) LIKE '" . $db_gallery->toSql($limit_parent_path, "Text", false) . "%'"
		                                            : ""
		                                        ) . "
		                                    [ORDER] [COLON] full_path
		                                    [LIMIT]
	                                    )";
	        $src["sql"]["query"]["ID_type"] = "SELECT " . (($ID_vgallery_nodes > 0 || strlen($default_field_type)) ? "ID" : "name") . "
	                    					, name 
				                        FROM anagraph_type 
				                        WHERE 1
				                        " . ($src["settings"]["limit_type"]
				                                ? " AND anagraph_type.ID IN(" . $db_gallery->tosql($src["settings"]["limit_type"], "Text", false) . ") " 
				                                : ""
				                            ) ."
			                            [ORDER] [COLON] name
			                            [LIMIT]
				                        ";
			break;
		case "gallery-dir":
		case "gallery-node":
			$src["sql"]["query"]["parent"] = "SELECT DISTINCT 
		                                    CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
		                                    , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS display_path
		                                FROM files
		                                WHERE files.is_dir > 0
											" . ($src["settings"]["insert_on_lastlevel"]
		                                        ? " AND LENGTH(CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name)) - LENGTH(REPLACE(CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name), '/', '')) >= " . $db_gallery->tosql($src["settings"]["limit_level"], "Number")
		                                        : ""
		                                    ) . "
		                                    " . (strlen($limit_parent_path)
		                                        ? " AND CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) LIKE '" . $db_gallery->toSql($limit_parent_path, "Text", false) . "%'"
		                                        : ""
		                                    ) . "
		                                [ORDER] [COLON] full_path
		                                [LIMIT]
		                                ";
	        $src["sql"]["query"]["ID_type"] = null;
			break;
		case "vgallery-dir":
			$src["sql"]["query"]["parent"] = "SELECT DISTINCT 
	                                            CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
	                                            , CONCAT('/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)) AS display_path
	                                        FROM vgallery_nodes
	                                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                                        WHERE vgallery_nodes.is_dir > 0
	                                            AND vgallery_nodes.name <> ''
	                                            AND vgallery.ID = " . $db_gallery->toSql($src["settings"]["ID_vgallery"], "Number") . "
		                                    [ORDER] [COLON] full_path
		                                    [LIMIT]
	                                        ";
	        $src["sql"]["query"]["ID_type"] = "SELECT " . (($ID_vgallery_nodes > 0 || strlen($default_field_type)) ? "ID" : "name") . "
	                    					, name 
				                        FROM vgallery_type 
				                        WHERE " . (OLD_VGALLERY 
                                                ? "vgallery_type.name <> 'System'"
                                                : "1"
                                            ) . "
				                            AND vgallery_type.is_dir_default > 0
				                            " . ($src["settings"]["limit_type"]
				                                    ? " AND vgallery_type.ID IN(" . $db_gallery->tosql($src["settings"]["limit_type"], "Text", false) . ") " 
				                                    : ""
				                                ) ."
		                                [ORDER] [COLON] NAME
		                                [LIMIT]
				                        ";	                                        
			break;
		case "vgallery-node":
			$src["sql"]["query"]["parent"] = "SELECT DISTINCT 
	                                        CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
	                                        , CONCAT('/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)) AS display_path
	                                    FROM vgallery_nodes
	                                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                                    WHERE vgallery_nodes.is_dir > 0
	                                        AND vgallery_nodes.name <> ''
	                                        AND vgallery.ID = " . $db_gallery->toSql($src["settings"]["ID_vgallery"], "Number") . "
	                                        " . ($src["settings"]["insert_on_lastlevel"]
	                                            ? " AND LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) - LENGTH(REPLACE(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), '/', '')) >= " . $db_gallery->tosql($src["settings"]["limit_level"], "Number")
	                                            : ""
	                                        ) . "
	                                        " . (strlen($limit_parent_path)
	                                            ? " AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) LIKE '" . $db_gallery->toSql($limit_parent_path, "Text", false) . "%'"
	                                            : ""
	                                        ) . "
		                                [ORDER] [COLON] full_path
		                                [LIMIT]
	                                    "; 
	        $src["sql"]["query"]["ID_type"] = "SELECT " . (($ID_vgallery_nodes > 0 || strlen($default_field_type)) ? "ID" : "name") . "
	                    					, name 
				                        FROM vgallery_type 
				                        WHERE " . (OLD_VGALLERY 
                                                ? "vgallery_type.name <> 'System'"
                                                : "1"
                                            ) . "
				                            AND vgallery_type.is_dir_default = 0
				                            " . ($src["settings"]["limit_type"]
				                                    ? " AND vgallery_type.ID IN(" . $db_gallery->tosql($src["settings"]["limit_type"], "Text", false) . ") " 
				                                    : ""
				                                ) ."
		                                [ORDER] [COLON] name
		                                [LIMIT]
				                        ";	 	                                    
			break;
		default:
			$src["sql"]["query"]["parent"] = "SELECT DISTINCT 
		                                    CONCAT(IF(`" . $src["table"] . "`.parent = '/', '', `" . $src["table"] . "`.parent), '/', `" . $src["table"] . "`.name) AS full_path
		                                    , CONCAT(IF(`" . $src["table"] . "`.parent = '/', '', `" . $src["table"] . "`.parent), '/', `" . $src["table"] . "`.name) AS display_path
		                                FROM `" . $src["table"] . "`
		                                WHERE `" . $src["table"] . "`.is_dir > 0
											" . ($src["settings"]["insert_on_lastlevel"]
		                                        ? " AND LENGTH(CONCAT(IF(`" . $src["table"] . "`.parent = '/', '', `" . $src["table"] . "`.parent), '/', `" . $src["table"] . "`.name)) - LENGTH(REPLACE(CONCAT(IF(`" . $src["table"] . "`.parent = '/', '', `" . $src["table"] . "`.parent), '/', `" . $src["table"] . "`.name), '/', '')) >= " . $db_gallery->tosql($src["settings"]["limit_level"], "Number") 
		                                        : ""
		                                    ) . "
		                                    " . (strlen($limit_parent_path)
		                                        ? " AND CONCAT(IF(`" . $src["table"] . "`.parent = '/', '', `" . $src["table"] . "`.parent), '/', `" . $src["table"] . "`.name) LIKE '" . $db_gallery->toSql($limit_parent_path, "Text", false) . "%'"
		                                        : ""
		                                    ) . "
		                                [ORDER] [COLON] full_path
		                                [LIMIT]
		                                ";
		    $src["sql"]["query"]["ID_type"] = "SELECT " . (($ID_vgallery_nodes > 0 || strlen($default_field_type)) ? "ID" : "name") . "
	                    					, name 
	                                    FROM " . $src["type"] . "_type 
	                                    WHERE 1
	                                    " . ($src["settings"]["limit_type"]
	                                            ? " AND " . $src["type"] . "_type.ID IN(" . $db_gallery->tosql($src["settings"]["limit_type"], "Text", false) . ") " 
	                                            : ""
	                                        ) ."
		                                [ORDER] [COLON] name
		                                [LIMIT]
										";
	}


	
	/****************
    * Service Actions
    */
    
    if($src["field"]["public"] && isset($_REQUEST["frmAction"]) && isset($_REQUEST["setpublic"]) && $ID_vgallery_nodes > 0) {
        $db = ffDB_Sql::factory();
        
        $sSQL = "UPDATE `" . $src["table"] . "` 
                SET `" . $src["table"] . "`.`" . $src["field"]["public"] . "` = " . $db_gallery->toSql($_REQUEST["setpublic"], "Number") . "
                WHERE `" . $src["table"] . "`.ID = " . $db_gallery->toSql($ID_vgallery_nodes, "Number");
        $db_gallery->execute($sSQL);


        
        if($_REQUEST["XHR_DIALOG_ID"]) {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array($vgallery_record_id)), true));
        } else {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array($vgallery_record_id)), true));
            //ffRedirect($_REQUEST["ret_url"]);
        }
    } 
    if($src["field"]["clone"] && $ID_vgallery_nodes > 0 && $_REQUEST["frmAction"] == "clone") {
        $sSQL = "SELECT `" . $src["table"] . "`.*
                    , " . $src["sql"]["select"]["vgallery_name"] . " AS vgallery_name 
                    , " . $src["sql"]["select"]["ID_vgallery"] . " AS ID_vgallery 
                FROM `" . $src["table"] . "` 
                WHERE `" . $src["table"] . "`.ID = " . $db_gallery->toSql($ID_vgallery_nodes);
        $db_gallery->query($sSQL);
        if($db_gallery->nextRecord()) {
            $params = array(
                "ID_vgallery" => $db_gallery->getField("ID_vgallery", "Number", true)
                , "ID_vgallery_nodes" => $ID_vgallery_nodes
                , "vgallery_name" => $db_gallery->getField("vgallery_name", "Text", true)
                , "vgallery_parent_old" => $db_gallery->getField("parent", "Text", true)
                , "vgallery_name_old" => $db_gallery->getField("name", "Text", true)
                , "gallery_model" => ""
            );
        }
        switch($src["type"]) {
        	case "anagraph":
		        $clone_schema = array(
                    "anagraph" => array(
                            "anagraph" => array("compare_key" => "ID", "return_ID" => "") 
                            , "anagraph_rel_nodes_fields" => array("compare_key" => "ID_nodes", "use_return_ID" => "ID_nodes")
                            , "rel_nodes_src" => array("real_table" => "rel_nodes", "use_return_ID" => "ID_node_src", "compare_str" => "
                                                                    (`rel_nodes`.`ID_node_src` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
                                                                        AND `rel_nodes`.`contest_src` = " . $db_gallery->toSql($vgallery_name, "Text") . "
                                                                        AND `rel_nodes`.`contest_dst` <> 'publishing' 
                                                                    )"
                                                )
                            , "rel_nodes_dst" => array("real_table" => "rel_nodes", "use_return_ID" => "ID_node_dst", "compare_str" => " 
                                                                    (`rel_nodes`.`ID_node_dst` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
                                                                        AND `rel_nodes`.`contest_dst` = " . $db_gallery->toSql($vgallery_name, "Text") . "
                                                                        AND `rel_nodes`.`contest_src` <> 'publishing' 
                                                                    )"
                                                )
                            , "module_maps_marker" => array("compare_key" => "ID_node", "use_return_ID" => "ID_node", "compare_str" => "
                                                                        `module_maps_marker`.`tbl_src` = " . $db_gallery->toSql($src["table"])
                            					)
                        )
                    );
        		break;
        	case "gallery":
				$clone_schema = null;        	
        		break;
        	case "vgallery":
		        $clone_schema = array(
                    "vgallery" => array(
                            "vgallery_nodes" => array("compare_key" => "ID", "return_ID" => "") 
                            , "vgallery_rel_nodes_fields" => array("compare_key" => "ID_nodes", "use_return_ID" => "ID_nodes")
                            , "vgallery_nodes_rel_groups" => array("compare_key" => "ID_vgallery_nodes", "use_return_ID" => "ID_vgallery_nodes")
                            , "rel_nodes_src" => array("real_table" => "rel_nodes", "use_return_ID" => "ID_node_src", "compare_str" => "
                                                                    (`rel_nodes`.`ID_node_src` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
                                                                        AND `rel_nodes`.`contest_src` = " . $db_gallery->toSql($vgallery_name, "Text") . "
                                                                        AND `rel_nodes`.`contest_dst` <> 'publishing' 
                                                                    )"
                                                )
                            , "rel_nodes_dst" => array("real_table" => "rel_nodes", "use_return_ID" => "ID_node_dst", "compare_str" => " 
                                                                    (`rel_nodes`.`ID_node_dst` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
                                                                        AND `rel_nodes`.`contest_dst` = " . $db_gallery->toSql($vgallery_name, "Text") . "
                                                                        AND `rel_nodes`.`contest_src` <> 'publishing' 
                                                                    )"
                                                )
                            , "ecommerce_settings" => array("use_return_ID" => "ID_items", "compare_str" => " 
                                                                    (`ecommerce_settings`.`ID_items` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
                                                                        AND `ecommerce_settings`.`tbl_src` = " . $db_gallery->toSql("vgallery_nodes", "Text") . "
                                                                    )"
                                                                    , "exclude_fields" => array("stock" => true, "actual_qta" => true)
                                                )
                            , "module_maps_marker" => array("compare_key" => "ID_node", "use_return_ID" => "ID_node", "compare_str" => "
                            										`module_maps_marker`.`tbl_src` = " . $db_gallery->toSql($src["table"]))
                        )
                    );
        		break;
        	default:
				$clone_schema = array(
                    $src["type"] => array(
                            $src["type"] => array("compare_key" => "ID", "return_ID" => "") 
                            , $src["type"] . "_rel_nodes_fields" => array("compare_key" => "ID_nodes", "use_return_ID" => "ID_nodes")
                            , "rel_nodes_src" => array("real_table" => "rel_nodes", "use_return_ID" => "ID_node_src", "compare_str" => "
                                                                    (`rel_nodes`.`ID_node_src` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
                                                                        AND `rel_nodes`.`contest_src` = " . $db_gallery->toSql($vgallery_name, "Text") . "
                                                                        AND `rel_nodes`.`contest_dst` <> 'publishing' 
                                                                    )"
                                                )
                            , "rel_nodes_dst" => array("real_table" => "rel_nodes", "use_return_ID" => "ID_node_dst", "compare_str" => " 
                                                                    (`rel_nodes`.`ID_node_dst` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
                                                                        AND `rel_nodes`.`contest_dst` = " . $db_gallery->toSql($vgallery_name, "Text") . "
                                                                        AND `rel_nodes`.`contest_src` <> 'publishing' 
                                                                    )"
                                                )
                            , "module_maps_marker" => array("compare_key" => "ID_node", "use_return_ID" => "ID_node", "compare_str" => "
                                                                        `module_maps_marker`.`tbl_src` = " . $db_gallery->toSql($src["table"])
                            					)
                        )
                    );        	
        }

        if(is_array($clone_schema) && check_function("clone_by_schema"))
            clone_by_schema($ID_vgallery_nodes, $clone_schema, "vgnode");        
    
        if($_REQUEST["XHR_DIALOG_ID"]) {
            die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array($vgallery_record_id)), true));
        } else {
            die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array($vgallery_record_id)), true));
            //ffRedirect($_REQUEST["ret_url"]);
        }
    }

	if(AREA_VGALLERY_SHOW_VISIBLE && $ID_vgallery_nodes > 0 && isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"])) {
        if(check_function("get_locale")) 
			$arrLang = get_locale("lang", true);
        
        if(is_array($arrLang) && count($arrLang)) { 
            check_function("update_vgallery_seo");
            foreach($arrLang AS $lang_code => $lang) {                    
                update_vgallery_seo(
                	null
                	, $ID_vgallery_nodes
                	, $lang["ID"]
                	, null
                	, null
                	, null
                	, $_REQUEST["setvisible"]
                	, null
                	, null
                	, $src["field"]
                	, ($lang["ID"] == LANGUAGE_DEFAULT_ID ? "primary" : null)
                );
            }
        } 
        
	    if(check_function("refresh_cache")) {
	        refresh_cache(
                "V"
                , $ID_vgallery_nodes
                , ($_REQUEST["setvisible"] 
                    ? "insert" 
                    : "update"
                )
                , ($vgallery_permalink 
                    ? $vgallery_permalink
                    : stripslash($vgallery_parent_old) . "/" . $vgallery_name_old
                )
            );
		}
	    $sSQL = "UPDATE " . $src["type"] . "_rel_nodes_fields SET 
	                `nodes` = ''
	            WHERE " . $src["type"] . "_rel_nodes_fields.`nodes` <> ''
	                AND FIND_IN_SET(" . $db_gallery->toSql($ID_vgallery_nodes, "Number") . ", " . $src["type"] . "_rel_nodes_fields.`nodes`)";
	    $db_gallery->execute($sSQL);

	    if($_REQUEST["XHR_DIALOG_ID"]) {
	        die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array($vgallery_record_id)), true));
	    } else {
	        die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array($vgallery_record_id)), true));
	        //ffRedirect($_REQUEST["ret_url"]);
	    }
	} 


    
	if($path == "/" && $vgallery_name_old == $vgallery_name) {
		$cm->oPage->addContent(ffTemplate::_get_word_by_code("vgallery_main"));
	} else {
	    //if(!($limit_level > 1)) {
	        //$vgallery_parent_old = "/" . $vgallery_name;
	    //}
	    
	    if($ID_vgallery_nodes > 0 || strlen($default_field_type)) 
	    {
			if(check_function("get_schema_def")) {
				$schema = get_schema_def();
			}


            /****
            * Load Languages
            */
	    	if(check_function("get_locale"))
	    		$arrLang = get_locale("lang", true);

	        /****
	        * Load Advanced Gropup
	        */
	        //if(check_function("get_vgallery_type_group"))
			//	$page_group = get_vgallery_type_group($vgallery_type, "backoffice");

	        $adv_group = (is_array($page_group) && count($page_group));
	        $field_enable_multilang = ($src["settings"]["enable_multilang"] && count($arrLang) > 1 ? true : false);

	        if(!$adv_group) {
	            $group_smart_url = LANGUAGE_DEFAULT;
				if(!array_key_exists($group_smart_url, $page_group)) {
					$page_group[$group_smart_url] = array(
		                "ID" => null
		                , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
		                , "column" => cm_getClassByFrameworkCss(array(12), "col")
		                /*, "class" => $group_smart_url*/
		                , "optional" => false
		                , "visible" => true
		                , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
		                , "is_system" => false
		            ); 
				}

				 if($field_enable_multilang && count($arrLang) > 1) {
	                foreach($arrLang AS $lang_code => $lang) {
		                $arrLangMultiPairs[$lang_code] = array(new ffData(strtolower($lang_code)), new ffData($lang["description"]));
                        $arrLangRev[$lang["ID"]] = $lang_code;

						$group_smart_url = $lang_code;
			            $page_group[$group_smart_url] = array(
			                "ID" => null
			                , "name" => $lang["description"]
			                , "column" => cm_getClassByFrameworkCss(array(12), "col")
			                , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
			                , "optional" => false
			                , "visible" => true
			                , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
			                , "is_system" => false
			            );
	                }            

                    if(!array_key_exists("lang", $page_group)) {
                        $group_smart_url = "lang";
                        $page_group[$group_smart_url] = array(
                            "ID" => null
                            , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
                            , "column" => cm_getClassByFrameworkCss(array(12), "col")
                            , "optional" => false
                            , "visible" => true
                            , "tab" => ($src["settings"]["enable_tab"] ?  $group_smart_url : null)  
                            , "is_system" => true
                        );
                    }
		        }	        

				if(!array_key_exists("setting", $page_group)) {
		            $group_smart_url = "setting";
		            $page_group[$group_smart_url] = array(
		                "ID" => null
		                , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
		                , "column" => cm_getClassByFrameworkCss(array(12), "col")
		                /*, "class" => $group_smart_url*/
		                , "optional" => false
		                , "visible" => true
		                , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
		                , "is_system" => true
		            );
				}
                if($src["settings"]["enable_highlight"] && !array_key_exists("highlight", $page_group)) {
                    $group_smart_url = "highlight";
                    $page_group[$group_smart_url] = array(
                        "ID" => null
                        , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
                        , "column" => cm_getClassByFrameworkCss(array(12), "col")
                        /*, "class" => $group_smart_url*/
                        , "optional" => false
                        , "visible" => true
                        , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
                        , "is_system" => true
                    );
                }                
	        }	        

			if(count($page_group) && array_key_exists("optional", $page_group)) {
	            $page_field["optional"]["swicth_option"] = array(
	                "ID_field" => null
	                , "ID" => "swicth_option"
	                , "name" => ""
	                , "smart_url" => ffCommon_url_rewrite("swicth-option")
	                , "extended_type" => "Option"
	                , "data_type" => null
	                , "data_source" => ""
	                , "data_limit" => ""
	                , "properties" => array("onclick" => "jQuery('#" . $vgallery_record_id . " fieldset.mopt').hide(); jQuery('#" . $vgallery_record_id . " fieldset.mopt.' + jQuery(this).val()).fadeIn();")
	                , "multi_pairs" => $page_group["optional"]["multi_pairs"]
	            );
	            $js .= "jQuery('#" . $vgallery_record_id . "_" . $page_field["optional"]["swicth_option"]["ID"] . "_0').click();";
	        }
	        	        
	        if($src["settings"]["enable_multi_cat"] && $src["field"]["cats"] && $src["settings"]["limit_level"] > 1 && (strlen($ID_vgallery_nodes) || strlen($default_field_type))) {
        		if(!array_key_exists("cats", $page_group)) {
		            $group_smart_url = "cats";
		            $page_group[$group_smart_url] = array(
		                "ID" => null
		                , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url) //. '<a href="javascript:void(0);" class="' . cm_getClassByFrameworkCss("plus", "icon") . '" onclick="' . "ff.ffPage.dialog.doOpen('VGalleryDirModify', '/restricted/vgallery/benessere/modify?type=dir&vname=" . $vgallery_name . "&path=" . "/" . $vgallery_name . "&extype=vgallery_nodes&adv=1');" . '"></a>'
		                , "column" => cm_getClassByFrameworkCss(array(12), "col")
		                /*, "class" => $group_smart_url*/
		                , "optional" => false
		                , "visible" => true
		                , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
		                , "is_system" => true
		            );
        		}
	            $page_field["cats"]["choice_cat"] = array(
	                "ID_field" => null
	                , "ID" => $src["field"]["cats"]
	                , "name" => ""
	                , "smart_url" => ffCommon_url_rewrite("choice-cat")
	                , "extended_type" => "Group"
	                , "data_type" => null
	                , "data_source" => ""
	                , "data_limit" => ""
	                , "store_in_db" => true
	                , "source_SQL" => "SELECT DISTINCT 
	                                        vgallery_nodes.ID
	                                        , REPLACE(CONCAT(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)), '-', ' ') AS display_path
	                                    FROM vgallery_nodes
	                                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                                    WHERE vgallery_nodes.is_dir > 0
	                                    	AND vgallery_nodes.visible > 0
	                                        AND vgallery_nodes.name <> ''
	                                        AND vgallery.ID = " . $db_gallery->toSql($src["settings"]["ID_vgallery"], "Number") . "
	                                        AND LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) - LENGTH(REPLACE(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), '/', '')) >= " . $db_gallery->tosql($src["settings"]["limit_level"], "Number") . "
	                                        " . (strlen($limit_parent_path)
	                                            ? " AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) LIKE '" . $db_gallery->toSql($limit_parent_path, "Text", false) . "%'"
	                                            : ""
	                                        ) . "
		                                [ORDER] [COLON] display_path
		                                [LIMIT]"
	            );
	        }
	        
	        if($src["settings"]["enable_place"] && $src["field"]["place"]) {
        		if(!array_key_exists("place", $page_group)) {
		            $group_smart_url = "place";
		            $page_group[$group_smart_url] = array(
		                "ID" => null
		                , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
		                , "column" => cm_getClassByFrameworkCss(array(12), "col")
		                /*, "class" => $group_smart_url*/
		                , "optional" => false
		                , "visible" => true
		                , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
		                , "is_system" => true
		            );
        		}
	            $page_field["place"]["map"] = array(
	                "ID_field" => null
	                , "ID" => $src["field"]["place"]
	                , "name" => ""
	                , "smart_url" => ffCommon_url_rewrite("place-map")
	                , "extended_type" => "GMap" 
	                , "store_in_db" => true
	            );
				$page_field["place"]["ID_place"] = array(
	                "ID_field" => null
	                , "ID" => $src["field"]["ID_place"] //DA FINIER
	                , "name" => ""
	                , "smart_url" => ffCommon_url_rewrite("place-city")
	                , "extended_type" => "SelectionWritable" 
	                , "store_in_db" => true
	                , "select" => array(
	                	"data_source" => FF_SUPPORT_PREFIX . "city"  
	                	, "data_limit" => ""
	                )
	            );	             
	        }

	        if($src["settings"]["enable_referer"] && $src["field"]["referer"] && !$vgallery_is_referer) {
        		if(!array_key_exists("referer", $page_group)) {
		            $group_smart_url = "referer";
		            $page_group[$group_smart_url] = array(
		                "ID" => null
		                , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
		                , "column" => cm_getClassByFrameworkCss(array(12), "col")
		                /*, "class" => $group_smart_url*/
		                , "optional" => false
		                , "visible" => true
		                , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
		                , "is_system" => true
		            );
        		}
	            $page_field["referer"]["choice_referer"] = array(
	                "ID_field" => null
	                , "ID" => $src["field"]["referer"]
	                , "name" => ""
	                , "smart_url" => ffCommon_url_rewrite("choice-referer")
	                , "extended_type" => "Autocomplete"
	                , "data_type" => null
	                , "data_source" => ""
	                , "data_limit" => ""
	                , "store_in_db" => true
	                , "compare" => "meta_title_alt"
	                , "source_SQL" => "SELECT vgallery_nodes.ID AS ID
	                                        , vgallery_nodes.meta_title_alt AS name 
	                                    FROM vgallery_nodes
	                                    	INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                                    WHERE vgallery_nodes.referer = 0
	                                    	AND vgallery.name = " . $db_gallery->toSql($vgallery_name) . "
	                                    [AND] [WHERE] 
	                                    ORDER BY vgallery_nodes.meta_title_alt
	                                    [LIMIT]"
	            );
	        }

	        if($src["settings"]["enable_tag"] != 0 && $src["field"]["tags"]) {
        		if(!array_key_exists("tags", $page_group)) {
		            $group_smart_url = "tags";
		            $page_group[$group_smart_url] = array(
		                "ID" => null
		                , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
		                , "column" => cm_getClassByFrameworkCss(array(12), "col")
		                /*, "class" => $group_smart_url*/
		                , "optional" => false
		                , "visible" => true
		                , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
		                , "is_system" => true
		            );
        		}
	            $page_field["tags"]["choice_tag"] = array(
	                "ID_field" => null
	                , "ID" => $src["field"]["tags"]
	                , "name" => ""
	                , "smart_url" => ffCommon_url_rewrite("choice-tag")
	                , "extended_type" => "AutocompleteMulti"
	                , "data_type" => null
	                , "data_source" => ""
	                , "data_limit" => ""
	                , "store_in_db" => true
	                , "source_SQL" => "SELECT search_tags.code
	                                        , IFNULL(
	                                        	(
	                                        		SELECT search_tags_by_lang.name
	                                        		FROM search_tags AS search_tags_by_lang
	                                        		WHERE search_tags_by_lang.code = search_tags.code
	                                        			AND search_tags_by_lang.ID_lang = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number") . " 
	                                        		LIMIT 1
	                                        	), search_tags.name
	                                        ) AS name
	                                    FROM search_tags
	                                    WHERE 
											" . ($src["settings"]["enable_tag"] == -1 
                                                        ? "1"
                                                        : " FIND_IN_SET(" . $db_gallery->toSql($src["settings"]["enable_tag"], "Number") . ", search_tags.categories)"
                                                    ) . "	                                    	
	                                    	AND search_tags.ID_lang = " . $db_gallery->toSql(LANGUAGE_DEFAULT_ID, "Number") . " 
	                                        [AND] [WHERE] 
	                                    [ORDER] [COLON] search_tags.name
	                                    [LIMIT]"
	            );
	        }

			if($ID_vgallery_nodes && !array_key_exists("seo", $page_group)) {
				//if($ID_vgallery_nodes) {
					$sSQL = "SELECT " . $src["seo"]["table"] . ".*
							FROM " . $src["seo"]["table"] . "
							WHERE " . $src["seo"]["table"] . "." . $src["seo"]["rel_key"] . " = " . $db_gallery->toSql($ID_vgallery_nodes);
					$db_gallery->query($sSQL);
					if($db_gallery->nextRecord()) {
						do {
							$ID_lang                                        	= $db_gallery->getField($src["seo"]["rel_lang"], "Number", true);
							$lang_code                                      	= $arrLangRev[$ID_lang];
                        	
                        	$arrSeo[$lang_code]["permalink"] 					= $db_gallery->getField($src["seo"]["permalink"], "Text", true);
                            if($src["seo"]["permalink_parent"])
								$arrSeo[$lang_code]["permalink_parent"] 		= $db_gallery->getField($src["seo"]["permalink_parent"], "Text", true);
							if($src["seo"]["smart_url"])
								$arrSeo[$lang_code]["smart_url"] 				= $db_gallery->getField($src["seo"]["smart_url"], "Text", true);
							if($src["seo"]["title"])
								$arrSeo[$lang_code]["title"] 					= $db_gallery->getField($src["seo"]["title"], "Text", true);
							if($src["seo"]["header"])
								$arrSeo[$lang_code]["header"]                   = $db_gallery->getField($src["seo"]["header"], "Text", true);
							if($src["seo"]["description"])
								$arrSeo[$lang_code]["description"]              = $db_gallery->getField($src["seo"]["description"], "Text", true);
                            
                            if($src["seo"]["robots"])
								$arrSeo[$lang_code]["robots"]                   = $db_gallery->getField($src["seo"]["robots"], "Text", true);
                            if($src["seo"]["canonical"])
								$arrSeo[$lang_code]["canonical"]                = $db_gallery->getField($src["seo"]["canonical"], "Text", true);
                            if($src["seo"]["meta"])
								$arrSeo[$lang_code]["meta"]                     = $db_gallery->getField($src["seo"]["meta"], "Text", true);
                            if($src["seo"]["httpstatus"])
								$arrSeo[$lang_code]["httpstatus"]               = $db_gallery->getField($src["seo"]["httpstatus"], "Number", true);
						} while($db_gallery->nextRecord());
					}
				//}

		        $group_smart_url = "seo";
		        $page_group[$group_smart_url] = array(
		            "ID" => null
		            , "name" => ffTemplate::_get_word_by_code("vgallery_backoffice_" . $group_smart_url)
		            , "column" => cm_getClassByFrameworkCss(array(12), "col")
		            /*, "class" => $group_smart_url*/
		            , "optional" => false
		            , "visible" => true
		            , "tab" => ($src["settings"]["enable_tab"] ? $group_smart_url : null)
		            , "is_system" => true
		        );
			}
	        

				
	        /****
	        * Load Fields
	        */ 
	        $sSQL = " 
	                SELECT " . $src["type"] . "_fields.*
	                    , " . $src["type"] . "_type.name AS type_name
	                    , IF(" . $src["type"] . "_fields.ID_data_type = " .  $db_gallery->toSql($tbl_supp["data_type"]["smart_url"]["table.alt"]["ID"], "Number") . "
							, CONCAT(" . $src["type"] . "_fields.data_source, " . $src["type"] . "_fields.data_limit)
							, " . $src["type"] . "_fields.ID
						) AS group_data
	                FROM " . $src["type"] . "_fields  
	                    INNER JOIN " . $src["type"] . "_type ON " . $src["type"] . "_type.ID = " . $src["type"] . "_fields.ID_type
	                    " . (strlen($ID_vgallery_nodes) 
	                        ? " INNER JOIN `" . $src["table"] . "` ON `" . $src["table"] . "`.ID_type = " . $src["type"] . "_fields.ID_type"
	                        : ""
	                    ) . "
	                WHERE 
	                    " . (strlen($ID_vgallery_nodes) 
	                        ? " (`" . $src["table"] . "`.ID = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " )"
	                        : " (" . $src["type"] . "_fields.ID_type = " . $db_gallery->toSql($default_field_type, "Number") . " )"
	                    ) . "
	                GROUP BY group_data
	                ORDER BY " . $src["type"] . "_fields.`order_backoffice`, " . $src["type"] . "_fields.`ID`
	             ";
	        $db_gallery->query($sSQL);
	        if ($db_gallery->nextRecord()) 
	        {
	            $file_properties = null;
	            $count_smart_url = 0;
	            $disable_multilang_count = 0;
	            $page_field_count = 0;
	            $group_smart_url = "";
				
				$arrDataExt = explode(",", $src["settings"]["data_ext"]);
	            do 
	            {
	            	$tmp_multi_field = array();
	                
	                $limit_by_groups = $db_gallery->getField("limit_by_groups", "Text", true);
	                if(strlen($limit_by_groups)) {
	                    $limit_by_groups = explode(",", $limit_by_groups);
	                    
	                    if(!count(array_intersect($user_permission["groups"], $limit_by_groups))) {
	                        continue;
	                    }
	                }

	                $ID_data_type 			= $db_gallery->getField("ID_data_type", "Number", true);
					$data_type 				= $tbl_supp["data_type"]["rev"][$ID_data_type];
					$data_source 			= $db_gallery->getField("data_source", "Text", true);
					$data_limit 			= $db_gallery->getField("data_limit", "Text", true);
	                $select_data_source 	= $db_gallery->getField("selection_data_source", "Text", true);
	                $select_data_limit 		= $db_gallery->getField("selection_data_limit", "Text", true);
					
					if(!$tbl_supp["data_type"]["smart_url"][$data_type]["editable"])
						continue;
					
					$is_field_system = false;
					if($data_type == "table.alt" 
						&& $data_source == $src["table"] 
						&& strlen($data_limit) 
					) {
						if(strpos($data_limit, ",") === false) {
							if(array_search($data_limit, $src["field"]) !== false) {
								$is_field_system = true;
							}
						} else {
							$arrDataLimit = explode(",", $data_limit);
							foreach($arrDataLimit AS $arrDataLimit_value) {
								if(array_search($arrDataLimit_value, $src["field"]) !== false) {
									$is_field_system = true;
									break;
								}
							}
						}
					}
					if($is_field_system)
						continue;

	                $ID_field 				= $db_gallery->getField("ID", "Number", true);
	                $field_name 			= $db_gallery->getField("name", "Text", true);
	                $field_smart_url 		= ffCommon_url_rewrite($field_name);
	                $showfiles 				= $tbl_supp["showfiles"]["rev"][$db_gallery->getField("settings_type_detail", "Number", true)];
	                
	                $max_upload 			= $tbl_supp["showfiles"]["smart_url"][$showfiles]["max_upload"];
	                $allowed_ext 			= $tbl_supp["showfiles"]["smart_url"][$showfiles]["allowed_ext"];
					$ID_check_control 		= $db_gallery->getField("ID_check_control", "Number", true);
					
					$check_control 			= $tbl_supp["check_control"]["rev"][$ID_check_control];
	                $ID_extended_type 		= $db_gallery->getField("ID_extended_type", "Number", true);
						
					if($data_type == "relationship") { 
						$arrRel[$data_source]["count"]++;
						$arrRel[$data_source]["ext"] = (array_search($ID_field, $arrDataExt) === false
														? false
														: true
													);
						$arrRel[$data_source]["keys"][] = "field_" . ($field_enable_multilang
							? LANGUAGE_DEFAULT . "_"
							: ""
						) . $field_smart_url;

						if($select_data_source) {
							continue;
						}
					}
					
	                if($adv_group) {
	                	$ID_group = $db_gallery->getField("ID_group_backoffice", "Number", true);
	                	if($ID_group && $tbl_supp["data_group"]["rev"][$ID_group])
		                    $group_smart_url 				= ffCommon_url_rewrite($tbl_supp["data_group"]["rev"][$ID_group]);
	                }


	                $tmp_field 								= array();
	                $tmp_field["ID_field"] 					= $ID_field;
	                $tmp_field["name"] 						= $field_name;
	                $tmp_field["smart_url"] 				= $field_smart_url;
	                $tmp_field["extended_type"] 			= $tbl_supp["extended_type"]["rev"][$ID_extended_type];
	                $tmp_field["extended_type_group"] 		= $tbl_supp["extended_type"]["smart_url"][$tmp_field["extended_type"]]["group"];
	                $tmp_field["data_type"] 				= $data_type;
	                $tmp_field["data_source"] 				= $data_source;
	                switch($tmp_field["data_source"]) {
	                	case "anagraph":
	                		$tmp_field["data_source_table"] = "anagraph";
	                		break;
	                	case "files":
	                		$tmp_field["data_source_table"] = "files";
	                		break;
	                	default:
	                		$tmp_field["data_source_table"] = "vgallery_nodes";
	                }
	                
	                $tmp_field["data_limit"] 				= $data_limit;
	                $tmp_field["data_ext"] 					= $data_ext;
	                
	                $tmp_field["select"]["data_source"] 	= $select_data_source;
	                $tmp_field["select"]["data_limit"] 		= $select_data_limit;
	                
	                if($tmp_field["data_type"] == "table.alt" && !$tmp_field["data_limit"])
	                	$tmp_field["writable"] 				= false;

                    if($tmp_field["extended_type_group"] == "select") {
                    	if($tmp_field["select"]["data_source"] == "-1") {
                            $tmp_field["select"]["writable"] = true;
                            $tmp_field["select"]["table"] = $src["type"] . "_rel_nodes_fields";
                            $tmp_field["select"]["sWhere"] = " AND " . $src["type"] . "_rel_nodes_fields.ID_fields = " . $db_gallery->toSql($tmp_field["ID_field"], "Number");
                            $tmp_field["select"]["field"] = "description";
	                        $tmp_field["select"]["ID"] = "description";
						} elseif(!strlen($tmp_field["select"]["data_source"]) || is_numeric($tmp_field["select"]["data_source"])) {
                            $tmp_field["select"]["table"] = $src["type"] . "_fields_selection_value";
                            $tmp_field["select"]["sWhere"] = " AND " . $src["type"] . "_fields_selection_value.ID_selection = " . $db_gallery->toSql($tmp_field["select"]["data_source"], "Number");
                            $tmp_field["select"]["field"] = "name";
	                        $tmp_field["select"]["ID"] = "name";
                        } else {
                            $tmp_field["select"]["sWhere"] = "";
                            $tmp_field["select"]["table"] = $tmp_field["select"]["data_source"];
                            $tmp_field["select"]["field"] = "name";

                            if(strlen($tmp_field["select"]["data_limit"]) && $tmp_field["select"]["data_limit"] != "null" /*&& strpos($tmp_field["select"]["data_limit"], "name") === false*/) {
                                if(strpos($tmp_field["select"]["data_limit"], ",") === false) {
                                    $tmp_field["select"]["field"] = "`" . $tmp_field["select"]["data_limit"] . "`";
                                } else {
                                    $arrSelectDataLimit = array();
                                    $arrDataLimit = explode(",", $tmp_field["select"]["data_limit"]);
                                    foreach($arrDataLimit AS $arrDataLimit_value) {
                                        if(!isset($schema["schema"][$tmp_field["select"]["table"]]["field"][$arrDataLimit_value]) 
                                            || (
                                                !isset($schema["schema"][$tmp_field["select"]["table"]]["field"][$arrDataLimit_value]["type"])
                                                && 
                                                !isset($schema["schema"][$tmp_field["select"]["table"]]["field"][$arrDataLimit_value]["data_source"])
                                            )
                                        ) {
                                            $arrSelectDataLimit[] = "`" . $arrDataLimit_value . "`";
                                        }
                                    }
                                    if(count($arrSelectDataLimit))
                                        $tmp_field["select"]["field"] = "CONCAT(" . implode(", ' ', ", $arrSelectDataLimit) . ")";
                                        $tmp_field["select"]["having"] = true; 
                                    
                                }
                            }
                        }  

                        if(is_array($schema["db"]["selection_data_source"][$tmp_field["select"]["table"]]) && array_key_exists("query", $schema["db"]["selection_data_source"][$tmp_field["select"]["table"]])) {
                            $tmp_field["query"] =  str_replace("[DISPLAY_VALUE]", $tmp_field["select"]["field"], $schema["db"]["selection_data_source"][$tmp_field["select"]["table"]]["query"]);
                        }                        
                    }

					//$tmp_field["real_name"] = preg_replace('/[^a-zA-Z0-9]/', '', $db_gallery->getField("type_name")->getValue() . $field_name);
	                $tmp_field["enable_tip"] 								= $db_gallery->getField("enable_tip", "Number", true);

	                $tmp_field["vgallery_name"] 							= $vgallery_name;
	                $tmp_field["ID_vgallery_node"] 							= $ID_vgallery_nodes;
	                $tmp_field["vgallery_node_name"]						= $vgallery_name_old;
	                $tmp_field["vgallery_node_parent"] 						= $vgallery_parent_old;
	                $tmp_field["vgallery_permalink"] 						= $vgallery_permalink;

					$gallery_sub_dir = "";
	                if($tmp_field["data_type"] == "media" 
	                    && $tmp_field["data_source"] == "files"
	                ) {
	                    $gallery_model[$field_name] = "/" . ffCommon_url_rewrite($field_name);
                		$tmp_field["file_multi"] = true;
                		$gallery_sub_dir = "/" . ffCommon_url_rewrite($field_name);
	                }
					switch($tmp_field["extended_type"]) {
						case "Image":
						case "Upload":
						case "UploadImage":
			                $tmp_field["user_vars"]["gallery_sub_dir"] = $gallery_sub_dir;
			                $tmp_field["user_vars"]["prefix_file_system"] = $src["settings"]["prefix_file_system"];
			                
			                if(strlen($vgallery_name_old)) {
			                    $tmp_field["file_storing_path"] = DISK_UPDIR . $src["settings"]["prefix_file_system"] . stripslash($vgallery_parent_old) . "/" . $vgallery_name_old  . (AREA_VGALLERY_ADD_ID_IN_REALNAME ? "-[ID_VALUE]" : "") . $gallery_sub_dir;
			                    $tmp_field["file_temp_path"] = DISK_UPDIR . "/tmp" . $src["settings"]["prefix_file_system"] . stripslash($vgallery_parent_old) . "/" . $vgallery_name_old . $gallery_sub_dir;
			                } else {
			                    if($vgallery_parent_old) {
			                        $tmp_field["file_storing_path"] = DISK_UPDIR . $src["settings"]["prefix_file_system"] . $vgallery_parent_old . (AREA_VGALLERY_ADD_ID_IN_REALNAME ? "-[ID_VALUE]" : "") . $gallery_sub_dir;
			                        $tmp_field["file_temp_path"] = DISK_UPDIR . "/tmp" . $src["settings"]["prefix_file_system"] . $vgallery_parent_old . $gallery_sub_dir;
			                    } else {
			                        $tmp_field["file_storing_path"] = DISK_UPDIR . $src["settings"]["prefix_file_system"] . "/" . $vgallery_name . $gallery_sub_dir;
			                        $tmp_field["file_temp_path"] = DISK_UPDIR . "/tmp" . $src["settings"]["prefix_file_system"] . "/" . $vgallery_name . $gallery_sub_dir;
			                    }
			                }  
			                
							break;
						default:
					}
              
	                $tmp_field["file_allowed_ext"]							= $allowed_ext;
	                if($max_upload > 0 && $max_upload < MAX_UPLOAD)
	                    $tmp_field["file_max_upload"] 						= $max_upload;

	                
	                if($tmp_field["extended_type"] == "TextBB"
	                    || $tmp_field["extended_type"] == "TextCK"
	                ) {
	                    $count_editor++;
	                }
	                
	                $tmp_field["user_vars"]["smart_url"] 					= $db_gallery->getField("enable_smart_url", "Number", true);
	                $tmp_field["user_vars"]["meta_description"] 			= $db_gallery->getField("meta_description", "Text", true);
	                if($tmp_field["user_vars"]["smart_url"] > 0) {
	                    $count_smart_url++;
	                }
	                $tmp_field["user_vars"]["disable_multilang"] 			= ($tmp_field["data_type"] == "table.alt" || $tmp_field["data_type"] == "relationship" || $tmp_field["data_type"] == "media" || $tmp_field["extended_type_group"] == "select" || $tmp_field["extended_type_group"] == "special"
	                															? true
	                															: $db_gallery->getField("disable_multilang", "Number", true)
															                );
	                if($tmp_field["user_vars"]["disable_multilang"] > 0) {
	                    $disable_multilang_count++; 
	                }                

	                $tmp_field["require"] 									= $db_gallery->getField("require", "Number", true);
	                $tmp_field["check_control"] 							= $tbl_supp["check_control"]["smart_url"][$check_control]["ff_name"];

					$tmp_field["container_class"]							= $db_gallery->getField("field_class_backoffice", "Text", true);

					$field_grid 											= (strlen($db_gallery->getField("field_grid_backoffice", "Text", true)) 
																				? explode(",", $db_gallery->getField("field_grid_backoffice", "Text", true)) 
																				: ""
																			);					
				    switch($db_gallery->getField("field_fluid_backoffice", "Number", true)) {
				        case -1:
				            $tmp_field["framework_css"]["component"]       	= "row" . ($cm->oPage->framework_css["is_fluid"] ? "-fluid" : "");
				            break;
				        case -2:
				            $tmp_field["framework_css"]["component"]       	= "row" . ($cm->oPage->framework_css["is_fluid"] ? "-fluid" : "");
				            break; 
				        case -3:
				            break;
				        case 1:
				            break;
				        case 2:
				            break;
				        default:
				        	if($field_grid) {
					            $tmp_field["framework_css"]["component"]	= array_reverse($field_grid);
							}
				    }
				    
					$label_grid 											= (strlen($db_gallery->getField("label_grid_backoffice", "Text", true)) 
																				? explode(",", $db_gallery->getField("label_grid_backoffice", "Text", true)) 
																				: "" 
																			);

					if($label_grid && array_sum($label_grid) / count($label_grid) < 12) {
					    switch($db_gallery->getField("label_fluid_backoffice", "Number", true)) {
					        case 2:
					            $tmp_field["framework_css"]["label"]		= array_reverse($label_grid);
					            break;
					        default:
					            $tmp_field["framework_css"]["label"]		= array_reverse($label_grid);
					    }				    	
					}

	            	if($tmp_field["data_type"] == "table.alt" && strlen($tmp_field["data_limit"])) {
                        if($tmp_field["data_source"] == $src["table"]) {
                            if(strpos($tmp_field["data_limit"], ",") === false)
                                $tmp_field["ID"] 							= $tmp_field["data_limit"];

                            $tmp_field["store_in_db"] 						= true;
                            $tmp_field["user_vars"]["disable_multilang"] 	= true;
						}
						
						$data_limit = explode(",", $tmp_field["data_limit"]);
						if(count($data_limit) > 1) {
							//$tmp_field["framework_css"]["component"]		= (int) floor(12 / count($data_limit)); //da capire perche da errore senza il cast
							foreach($data_limit AS $data_limit_value) {
								if(strlen($data_limit_value)) {
									$tmp_multi_smart_url 									= ffCommon_url_rewrite($tmp_field["smart_url"] . " " . $data_limit_value);

									$tmp_multi_field[$tmp_multi_smart_url] 					= $tmp_field;
					                $tmp_multi_field[$tmp_multi_smart_url]["name"] 			= $tmp_field["name"] . "_" . $data_limit_value;
					                $tmp_multi_field[$tmp_multi_smart_url]["smart_url"] 	= ffCommon_url_rewrite($tmp_field["smart_url"] . " " . $data_limit_value);
					                $tmp_multi_field[$tmp_multi_smart_url]["data_limit"] 	= $data_limit_value;
								}
							}						
						}
	            	}

					if(!count($tmp_multi_field))
						$tmp_multi_field[$field_smart_url] = $tmp_field;

	                if($field_enable_multilang) {
	                    if(!$tmp_field["user_vars"]["disable_multilang"]) {
	                        foreach($arrLang AS $lang_code => $lang) {
	                            $group_smart_url = $lang_code;
									
								if(is_array($tmp_multi_field) && count($tmp_multi_field)) {
									foreach($tmp_multi_field AS $field_smart_url => $tmp_field) {
			                            $page_field[$group_smart_url]["field_" . $lang_code . "_" . $field_smart_url] = $tmp_field;
			                            $page_field[$group_smart_url]["field_" . $lang_code . "_" . $field_smart_url]["ID"] = ($tmp_field["ID"] ? $tmp_field["ID"] : "field_" . $lang_code . "_" . $field_smart_url);
			                            $page_field[$group_smart_url]["field_" . $lang_code . "_" . $field_smart_url]["user_vars"]["ID_lang"] = $lang["ID"];
			                            if($adv_group)
                            				$page_field[$group_smart_url]["field_" . $lang_code . "_" . $field_smart_url]["user_vars"]["code_lang"] = $lang_code;

			                            if($lang["ID"] == LANGUAGE_DEFAULT_ID){
			                                if($tmp_field["user_vars"]["smart_url"] && !$tmp_field["require"])
			                                    $page_field[$group_smart_url]["field_" . $lang_code . "_" . $field_smart_url]["require"] = true;
			                            } else {
			                                if(!DISABLE_SMARTURL_CONTROL && $tmp_field["user_vars"]["smart_url"] && !$tmp_field["require"]) 
			                                    $page_field[$group_smart_url]["field_" . $lang_code . "_" . $field_smart_url]["require"] = true;
			                            }
									}
								}
	                        }
	                    } else {
	                        $group_smart_url = LANGUAGE_DEFAULT;
							
							if(is_array($tmp_multi_field) && count($tmp_multi_field)) {
								foreach($tmp_multi_field AS $field_smart_url => $tmp_field) {
			                        $page_field[$group_smart_url]["field_" . LANGUAGE_DEFAULT . "_" . $field_smart_url] = $tmp_field;
			                        $page_field[$group_smart_url]["field_" . LANGUAGE_DEFAULT . "_" . $field_smart_url]["ID"] = ($tmp_field["ID"] ? $tmp_field["ID"] : "field_" . LANGUAGE_DEFAULT . "_" . $field_smart_url);
			                        $page_field[$group_smart_url]["field_" . LANGUAGE_DEFAULT . "_" . $field_smart_url]["user_vars"]["ID_lang"] = LANGUAGE_DEFAULT_ID;
									//if($adv_group)
                        				//$page_field[$group_smart_url]["field_" . LANGUAGE_DEFAULT . "_" . $field_smart_url]["user_vars"]["code_lang"] = LANGUAGE_DEFAULT;

			                        if($tmp_field["user_vars"]["smart_url"] && !$tmp_field["require"])
			                            $page_field[$group_smart_url]["field_" . LANGUAGE_DEFAULT . "_" . $field_smart_url]["require"] = true;
								}
							}
	                    }
	                } else {
	                    $group_smart_url = LANGUAGE_DEFAULT;
	                
						if(is_array($tmp_multi_field) && count($tmp_multi_field)) {
							foreach($tmp_multi_field AS $field_smart_url => $tmp_field) {
			                    $page_field[$group_smart_url]["field_" . $field_smart_url] = $tmp_field;
			                    $page_field[$group_smart_url]["field_" . $field_smart_url]["ID"] = ($tmp_field["ID"] ? $tmp_field["ID"] : "field_" . $field_smart_url);
			                    $page_field[$group_smart_url]["field_" . $field_smart_url]["user_vars"]["ID_lang"] = LANGUAGE_DEFAULT_ID;
			                    
			                    //if($adv_group)
                    				//$page_field[$group_smart_url]["field_" . $field_smart_url]["user_vars"]["code_lang"] = LANGUAGE_DEFAULT;

			                    if($tmp_field["user_vars"]["smart_url"] && !$tmp_field["require"])
			                        $page_field[$group_smart_url]["field_" . $field_smart_url]["require"] = true;
							}
						}
	                }
	                $page_field_count++;
	            } while($db_gallery->nextRecord());
	        }
			
			//switch rel by field related
	        if(is_array($arrRel) && count($arrRel)) {
	        	foreach($arrRel AS $rel_vgallery => $rel_nodes) {
	        		if($rel_nodes["ext"]) {
	        			$rel_src = get_schema_fields_by_type("vgallery");
	        				
	        			if(!$field_rel[$tbl_supp["vgallery"]["smart_url"][$rel_vgallery]["type"]["node"][0]]) {
	        				$page_field_seek = array_search($rel_nodes["keys"][0], array_keys($page_field[LANGUAGE_DEFAULT]));
	        				 $sSQL = " 
					                SELECT " . $rel_src["type"] . "_fields.*
					                    , " . $rel_src["type"] . "_type.name AS type_name
					                FROM " . $rel_src["type"] . "_fields  
					                    INNER JOIN " . $rel_src["type"] . "_type ON " . $rel_src["type"] . "_type.ID = " . $rel_src["type"] . "_fields.ID_type
					                WHERE 
										" . $rel_src["type"] . "_type.name = " . $db_gallery->toSql($tbl_supp["vgallery"]["smart_url"][$rel_vgallery]["type"]["node"][0]) . "
										AND " . $rel_src["type"] . "_fields.ID_data_type = " .  $db_gallery->toSql($tbl_supp["data_type"]["smart_url"]["relationship"]["ID"], "Number") . "
										AND " . $rel_src["type"] . "_fields.data_source = " . $db_gallery->toSql($vgallery_name) . "
					                GROUP BY " . $rel_src["type"] . "_fields.ID
					                ORDER BY " . $rel_src["type"] . "_fields.`order_backoffice`, " . $rel_src["type"] . "_fields.`ID`
					             ";
					        $db_gallery->query($sSQL);
					        if ($db_gallery->nextRecord()) 
					        {
				        		do {
				        			$tmp_field 						= $page_field[LANGUAGE_DEFAULT][$rel_nodes["keys"][0]];
									$tmp_field["user_vars"]["src"]	= array(
																		"ID" => $rel_nodes["keys"][0]
																		, "ID_field" => $tmp_field["ID_field"]
																		, "name" => $tmp_field["name"]
																		, "smart_url" => $tmp_field["smart_url"]
																	);

				        			$tmp_field["ID_field"] 			= $db_gallery->getField("ID", "Text", true); 
				        			$tmp_field["name"] 				= $db_gallery->getField("name", "Text", true); 
				        			$tmp_field["smart_url"] 		= ffCommon_url_rewrite($tmp_field["name"]);
				        			$tmp_field["ID"]				= "field_" . ($field_enable_multilang
																		? LANGUAGE_DEFAULT . "_"
																		: ""
																	) . $tmp_field["smart_url"];
				        			//$tmp_field["data_source"] 	= $db_gallery->getField("data_source", "Text", true); 
				        			//$tmp_field["data_limit"] 		= $db_gallery->getField("data_limit", "Text", true); 
				        			
				        			
				        			$field_rel[$tbl_supp["vgallery"]["smart_url"][$rel_vgallery]["type"]["node"][0]][$tmp_field["ID"]] = $tmp_field;
				        		} while($db_gallery->nextRecord());

				        		$page_field[LANGUAGE_DEFAULT] = array_slice($page_field[LANGUAGE_DEFAULT], 0, $page_field_seek, true) 
									+ $field_rel[$tbl_supp["vgallery"]["smart_url"][$rel_vgallery]["type"]["node"][0]]
									+ array_slice($page_field[LANGUAGE_DEFAULT], $page_field_seek, count($page_field[LANGUAGE_DEFAULT]) - 1, true);

								unset($page_field[LANGUAGE_DEFAULT][$rel_nodes["keys"][0]]);
							}
						}
	        		}
	        	} 
	        }
	      //  print_r($page_field);
	        
			if(count($page_group) && array_key_exists("lang", $page_group) && count($arrLangMultiPairs) > 1) {
			    $page_field["lang"]["swicth_lang"] = array(
			        "ID_field" => null
			        , "ID" => "swicth_lang"
			        , "name" => ""
			        , "smart_url" => ffCommon_url_rewrite("swicth-lang")
			        , "extended_type" => "Option"
			        , "data_type" => ""
			        , "data_source" => ""
			        , "data_limit" => ""
			        , "properties" => array("onclick" => "jQuery('#" . $vgallery_record_id . " .mlang').hide(); jQuery('#" . $vgallery_record_id . " .mlang.' + jQuery(this).val()).fadeIn();")
			        , "multi_pairs" => $arrLangMultiPairs
			    );
			    
			 	if(!$src["settings"]["enable_tab"])
			   		$js .= "jQuery('#" . $vgallery_record_id . "_" . $page_field["lang"]["swicth_lang"]["ID"] . "_" . LANGUAGE_DEFAULT . "').click();";
			}
            
            if(count($page_group) && array_key_exists("seo", $page_group)) {
                if(!count($arrLangMultiPairs))
                    $arrLangMultiPairs[LANGUAGE_DEFAULT] = array(new ffData(LANGUAGE_DEFAULT), new ffData(LANGUAGE_DEFAULT));
                
                foreach($arrLangMultiPairs AS $lang_code => $ff_lang) {
                	if($src["seo"]["title"]) {
	                    $page_field["seo"]["seo_" . $lang_code . "_title"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_title"
	                        , "name" => ""
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffTemplate::_get_word_by_code("seo_meta_title")
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_meta_title : $arrSeo[$lang_code]["title"])
	                        , "encode_entities" => false
	                    );
					}                    
                    if($src["seo"]["header"]) {
		                $page_field["seo"]["seo_" . $lang_code . "_header"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_header"
	                        , "name" => ""
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffTemplate::_get_word_by_code("seo_meta_title_alt")
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_meta_header : $arrSeo[$lang_code]["header"])
	                        , "encode_entities" => false
	                    );
					}
					if($src["seo"]["description"]) {
	                    $page_field["seo"]["seo_" . $lang_code . "_description"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_description"
	                        , "name" => ""
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffTemplate::_get_word_by_code("seo_meta_description")
	                        , "extended_type" => "Text"
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_meta_description : $arrSeo[$lang_code]["description"])
	                        , "encode_entities" => false
	                    );
					}
					if($src["seo"]["smart_url"]) {
	                    $page_field["seo"]["seo_" . $lang_code . "_smart_url"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_smart_url"
	                        , "name" => ""
	                        , "column" => cm_getClassByFrameworkCss(array(6), "col")
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffCommon_url_rewrite("seo_smart_url") . " (" . ffCommon_dirname($lang_code == LANGUAGE_DEFAULT 
	                        	? $vgallery_permalink 
	                        	: $arrSeo[$lang_code]["permalink"]
	                        ) . ")"
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_name_old : $arrSeo[$lang_code]["smart_url"])
	                        , "encode_entities" => false
	                    );
					}
					if($src["seo"]["robots"]) {
	                    $page_field["seo"]["seo_" . $lang_code . "_robots"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_robots"
	                        , "name" => ""
	                        , "column" => cm_getClassByFrameworkCss(array(6), "col")
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffCommon_url_rewrite("seo_meta_robots")
	                        , "extended_type" => "Selection"
	                        , "multi_pairs" => array (
	                                            array(new ffData("noindex, follow"), new ffData("noindex, follow")),
	                                            array(new ffData("noindex, nofollow"), new ffData("noindex, nofollow")),
	                                            array(new ffData("index, nofollow"), new ffData("index, nofollow"))
	                                        )
	                        , "select_one_label" => "index, follow"
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_meta_robots : $arrSeo[$lang_code]["robots"])
	                        , "encode_entities" => false
	                    );
					}
					if($src["seo"]["meta"]) {
	                    $page_field["seo"]["seo_" . $lang_code . "_meta"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_meta"
	                        , "name" => ""
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffTemplate::_get_word_by_code("seo_meta_other")
	                        , "extended_type" => "Text"
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_meta : $arrSeo[$lang_code]["meta"])
	                        , "encode_entities" => false
	                    );
					}
					if($src["seo"]["httpstatus"]) {
	                    $page_field["seo"]["seo_" . $lang_code . "_httpstatus"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_httpstatus"
	                        , "name" => ""
	                        , "column" => cm_getClassByFrameworkCss(array(6), "col")
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffCommon_url_rewrite("seo_meta_httpstatus")
	                        , "extended_type" => "Selection"
	                        , "multi_pairs" => ffGetHTTPStatus()
	                        , "select_one_label" => ffTemplate::_get_word_by_code("default")
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_httpstatus : $arrSeo[$lang_code]["httpstatus"])
	                        , "encode_entities" => false
	                    );
					}
                    if($src["seo"]["canonical"]) {
	                    $page_field["seo"]["seo_" . $lang_code . "_canonical"] = array(
	                        "ID_field" => null
	                        , "ID" => "seo_" . $lang_code . "_canonical"
	                        , "name" => ""
	                        , "column" => cm_getClassByFrameworkCss(array(6), "col")
	                        , "class" => ($src["settings"]["enable_tab"] ? "" : "mlang " . strtolower($lang_code))
	                        , "label" => ffCommon_url_rewrite("seo_meta_canonical")
	                        , "default_value" => ($lang_code == LANGUAGE_DEFAULT ? $vgallery_canonical : $arrSeo[$lang_code]["canonical"])
	                        , "encode_entities" => false
	                    );		            
					}
                }
            }

	        /******
	        * Visible code
	        */
	        if(!ENABLE_STD_PERMISSION && ENABLE_ADV_PERMISSION && $src["settings"]["enable_adv_visible"] && !$simple_interface && $field_enable_multilang && $page_field_count > $disable_multilang_count && $adv_params) 
	        {
	            $visible_isset = true;

	            $field_name = "visible";
	            $field_smart_url = "visible";

	            $tmp_field = array();
	            $tmp_field["name"] = "system_" . $field_name;
	            $tmp_field["label"] = ffTemplate::_get_word_by_code($src["type"] . "_" . $field_name);
	            $tmp_field["smart_url"] = $field_smart_url;
	            $tmp_field["extended_type"] = "Boolean";
	            $tmp_field["data_type"] = "system";
	            $tmp_field["data_source"] = "";
	            $tmp_field["data_limit"] = "";
	            $tmp_field["enable_tip"] = false;
	            $tmp_field["require"] = false;
	            $tmp_field["check_control"] = "";
	            $tmp_field["max_upload"] = "";
	            $tmp_field["allowed_ext"] = "";
	            $tmp_field["default_value"] = "1";
	            
	            $tmp_field["user_vars"]["smart_url"] = false;
	            $tmp_field["user_vars"]["meta_description"] = false;
	            $tmp_field["user_vars"]["disable_multilang"] = false;

	            foreach($arrLang AS $lang_code => $lang) {
	                if($adv_group)
	                    $group_smart_url = "publishing";
	                else
	                    $group_smart_url = ffCommon_url_rewrite($lang_code);

	                if(is_array($page_field[$group_smart_url])) {
	                    $page_field[$group_smart_url] = array("system_" . $lang_code . "_" . $field_smart_url => $tmp_field) + $page_field[$group_smart_url];    
	                } else {
	                    $page_field[$group_smart_url]["system_" . $lang_code . "_" . $field_smart_url] = $tmp_field;
	                }
	                
	                $page_field[$group_smart_url]["system_" . $lang_code . "_" . $field_smart_url]["ID"] = "system_" . $lang_code . "_" . $field_smart_url;
	                $page_field[$group_smart_url]["system_" . $lang_code . "_" . $field_smart_url]["user_vars"]["ID_lang"] = $lang["ID"];
	                if($adv_group)
                    	$page_field[$group_smart_url]["system_" . $lang_code . "_" . $field_smart_url]["user_vars"]["code_lang"] = $lang_code;
	            }
	        }
	    }    
	   
	    /***************
	    * Start Record
	    */

	    $oRecord = ffRecord::factory($cm->oPage);
	    $oRecord->id = $vgallery_record_id;
	    $oRecord->resources[] = $oRecord->id;
	    $oRecord->class = "ffRecord " . $vgallery_name;
	    //$oRecord->resources_get = $oRecord->resources; 
	    //$oRecord->title = ffTemplate::_get_word_by_code("vgallery_" . $vgallery_name . "_title");
	    /* Title Block */
	    if($simple_interface)
	        $oRecord->title = ffTemplate::_get_word_by_code("vgallery_" . $vgallery_name . "_title");
	    else
	        $oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content' . ($cm->isXHR() ? "" : " " . cm_getClassByFrameworkCss(12, "col")) . '">' . cm_getClassByFrameworkCss("vg-virtual-gallery", "icon-tag", array("2x", "content")) . $vgallery_nodes_title . '</h1>';

	    $oRecord->src_table = $src["table"];
	    if($simple_interface) {
	        $oRecord->buttons_options["cancel"]["display"] = false;
	        if($vgallery_force_name)
	            $oRecord->buttons_options["delete"]["display"] = false;
	        else
	            $oRecord->buttons_options["delete"]["display"] = $ID_vgallery_nodes;
	    } else {
	        $oRecord->buttons_options["delete"]["display"] = AREA_VGALLERY_SHOW_DELETE;    
	    }
	    $oRecord->addEvent("on_do_action", "VGalleryNodesModify_on_do_action");
	    $oRecord->addEvent("on_done_action", "VGalleryNodesModify_on_done_action");
		
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "save";
	    $oButton->label = ffTemplate::_get_word_by_code("save");
	    $oButton->class = "activebuttons";
			    $oButton->aspect = "link";
	    if($_REQUEST["frmAction"] == "save") {
		    $oButton->frmAction = "save";
			$cm->oPage->ret_url = "";	    
	    }
	    $oRecord->addActionButton($oButton);		
		
		$oRecord->user_vars["src"] = $src;
	    $oRecord->user_vars["name_old"] = $vgallery_name_old;
	    $oRecord->user_vars["parent_old"] = $path;
        $oRecord->user_vars["permalink"] = ($vgallery_permalink
                                                ? $vgallery_permalink
                                                : stripslash($vgallery_parent_old) . "/" . $vgallery_name_old
                                            );
	    $oRecord->user_vars["title"] = $vgallery_meta_title;
	    $oRecord->user_vars["header"] = $vgallery_meta_header;
	    $oRecord->user_vars["vgallery_name"] = $vgallery_name;
	    $oRecord->user_vars["is_dir"] = $vgallery_is_dir;
		$oRecord->user_vars["is_clone"] = $vgallery_is_clone;
	    $oRecord->user_vars["ID_vgallery"] = $src["settings"]["ID_vgallery"];
	    $oRecord->user_vars["ID_type"] = $vgallery_type;
	    $oRecord->user_vars["force_name"] = $vgallery_force_name;
	    $oRecord->user_vars["vgallery_visible"] = ($vgallery_force_visible === null
	                                                ? AREA_VGALLERY_SHOW_VISIBLE
	                                                : $vgallery_force_visible
	                                            );
	    $oRecord->user_vars["lang"] = $arrLang;
	    $oRecord->user_vars["enable_multilang"] = $field_enable_multilang;
	    $oRecord->user_vars["limit_type"] = $arrAllowedType[$type];

	    if($src["field"]["clone"])
	    	$oRecord->additional_fields["is_clone"] = new ffData("0", "Number");

	    $oRecord->additional_fields[$src["field"]["last_update"]] =  new ffData(time(), "Number");
	    $oRecord->insert_additional_fields[$src["field"]["created"]] =  new ffData(time(), "Number");

	    /*if($src["field"]["parent"] && !($src["settings"]["limit_level"] > 1)) 
	        $oRecord->additional_fields["parent"] = new ffData("/" . $vgallery_name, "Text");*/

		if($src["settings"]["ID_vgallery"])
	    	$oRecord->insert_additional_fields["ID_vgallery"] =  new ffData($src["settings"]["ID_vgallery"], "Number");

        if(!$src["settings"]["show_owner_by_categories"])
	        $oRecord->insert_additional_fields["owner"] =  new ffData(get_session("UserNID"), "Number");

	    

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
	    $oField->app_type = "Number";
	    $oRecord->addKeyField($oField);
	    
	    if(strlen($ID_vgallery_nodes) || strlen($default_field_type)) 
	    {
	        if($vgallery_visible && !$count_smart_url > 0) {
	            $oRecord->strError = ffTemplate::_get_word_by_code("smart_url_not_set");
	            $oRecord->contain_error = true;
	            $oRecord->skip_action = true;
	            $oRecord->buttons_options["insert"]["display"] = false;
	            $force_display_record = true;
	        }

	        if(!$oRecord->contain_error) {
	            $sSQL_field = "";
					            
	            check_function("get_field_by_extension");

	            foreach($page_group AS $page_group_key => $page_group_value) {
	                if($page_group_value === null) {
	                    $real_group_key = null;
	                } else {
	                    if(!$page_group_value["visible"])
	                        continue;
	                    
	                    $real_group_key = $page_group_key;
	                    
	                    if($page_group_value["tab"]) {
	                        $oRecord->addTab($page_group_value["tab"]);
	                        $oRecord->setTabTitle($page_group_value["tab"], ($page_group_value["name"] ? $page_group_value["name"] : ffTemplate::_get_word_by_code("vgallery_backoffice_" . $page_group_key)));
	                    }

	                    $oRecord->addContent(null, true, $page_group_key); 
	                    $oRecord->groups[$page_group_key] = array(
	                                                        "title" => ($page_group_value["name"] ? $page_group_value["name"] : ffTemplate::_get_word_by_code("vgallery_backoffice_" . $page_group_key))
	                                                        , "cols" => 1
	                                                        , "class" => ($page_group_value["is_system"] ? "grp-sys" : "grp-std") . ($adv_group && $page_group_value["column"] ? " " . $page_group_value["column"] : "") . ($page_group_value["class"] ? " " . $page_group_value["class"] : "") . ($page_group_value["optional"] ? " mopt" : "")
	                                                        , "tab" => $page_group_value["tab"]
	                                                     );    
	                }      

	                if(is_array($page_field) && count($page_field)
	                    && array_key_exists($page_group_key, $page_field)
	                ) {
	                    foreach($page_field[$page_group_key] AS $field_key => $field_value) {
	                    	$component = array(
	                    		"name" => null
	                    		, "store_in_db" => (isset($field_value["store_in_db"]) ? $field_value["store_in_db"] : false)
	                    		, "title" => ""
	                    		, "data_source" => ""
								, "compare_key" => null
								, "where" => array()
								, "order_by" => "ID"
								, "record_url" => ""
								, "record_id" => ""
								, "record_key" => "ID"
								, "record_params" => ""
	                    		, "fields" => array()
	                    		, "hidden_fields" => array()
	                    	);
	                    	
							$field_value["src"] = $src;
	                        if($field_value["data_type"] !== null) {
	                            switch($field_value["data_type"]) 
	                            {
	                            	case "system":
										if($ID_vgallery_nodes > 0) {
	                            			if(OLD_VGALLERY) {
												$sSQL_field .= ", (
			                                        SELECT 
			                                            description 
			                                        FROM " . $src["type"] . "_rel_nodes_fields 
			                                        WHERE ID_nodes = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
														" . ($src["seo"]["rel_field"]
			                                                    ? " AND `" . $src["seo"]["rel_field"] . "` = " . $db_gallery->toSql($field_value["ID_field"], "Number")
			                                                    : ""
			                                            ) . "
			                                            " . ($src["seo"]["rel_lang"]
			                                                    ? " AND `" . $src["seo"]["rel_lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
			                                                    : ""
			                                            ) . "
			                                        LIMIT 1
			                                    ) AS `" . $field_key . "`";				                            		
											} else {
												$sSQL_field .= ", (
			                                        SELECT " . $src["seo"]["table"] . "." . $src["seo"]["visible"] . "
			                                        FROM " . $src["seo"]["table"] . "
			                                        WHERE " . $src["seo"]["rel_key"] . " = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
														" . ($src["seo"]["rel_field"]
			                                                    ? " AND `" . $src["seo"]["rel_field"] . "` = " . $db_gallery->toSql($field_value["ID_field"], "Number")
			                                                    : ""
			                                            ) . "		                                            
			                                            " . ($src["seo"]["rel_lang"]
			                                                    ? " AND `" . $src["seo"]["rel_lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
			                                                    : ""
			                                            ) . "
			                                        LIMIT 1
			                                    ) AS `" . $field_key . "`";				                            		
											}
										}
	                            		break;
	                            	case "relationship":
	                            		if($ID_vgallery_nodes > 0) {
	                            			if(is_array($field_value["user_vars"]["src"])) {
												$sSQL_field .= ", (
							                                    SELECT GROUP_CONCAT(`ID_nodes`)
							                                    FROM " . $src["type"] . "_rel_nodes_fields 
							                                    	INNER JOIN " . $src["table"] . " ON " . $src["table"] . ".ID = " . $src["type"] . "_rel_nodes_fields.ID_nodes
							                                    	INNER JOIN " . $src["type"] . " ON " . $src["type"] . ".ID = " . $src["table"] . ".ID_" . $src["type"] . "
							                                    WHERE FIND_IN_SET(" . $db_gallery->toSql($ID_vgallery_nodes, "Number") . ", `limit`)
							                                    	AND " . $src["type"] . ".name = " . $db_gallery->toSql($field_value["data_source"]) . "
							                                        AND ID_fields = " . $db_gallery->toSql($field_value["ID_field"], "Number") . "
							                                        " . ($src["field"]["lang"]
							                                                ? " AND `" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
							                                                : ""
							                                        ) . "
							                                ) AS `" . $field_key . "`";				                            			
	                            			} elseif($arrRel[$field_value["data_source"]]["count"] > 1)
	                            			{
                                               /* $sSQL_field .= ", ( SELECT GROUP_CONCAT(`" . $field_value["data_source_table"] . "`.ID ORDER BY `" . $field_value["data_source_table"] . "`.meta_title_alt)
			                                        FROM `" . $field_value["data_source_table"] . "`
			                                            INNER JOIN " . $src["type"] . "_rel_nodes_fields
			                                                ON 
			                                                (
			                                                    `" . $field_value["data_source_table"] . "`.ID IN(`" . $src["type"] . "_rel_nodes_fields`.`limit`)
			                                                )
			                                            WHERE ID_nodes = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
							                                        AND ID_fields = " . $db_gallery->toSql($field_value["ID_field"], "Number") . "
							                                        " . ($src["field"]["lang"]
							                                                ? " AND `" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
							                                                : ""
							                                        ) . "
			                                    ) AS `" . $field_key . "`";*/
												
                                                $sSQL_field .= ", (
							                                    SELECT `limit`
							                                    FROM " . $src["type"] . "_rel_nodes_fields 
							                                    WHERE ID_nodes = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
							                                        AND ID_fields = " . $db_gallery->toSql($field_value["ID_field"], "Number") . "
							                                        " . ($src["field"]["lang"]
							                                                ? " AND `" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
							                                                : ""
							                                        ) . "
							                                    LIMIT 1
							                                ) AS `" . $field_key . "`";			
											} else {
												$sSQL_field .= ", ( SELECT GROUP_CONCAT(`" . $field_value["data_source_table"] . "`.ID ORDER BY `" . $field_value["data_source_table"] . "`.meta_title_alt)
			                                        FROM `" . $field_value["data_source_table"] . "`
			                                            INNER JOIN rel_nodes
			                                                ON 
			                                                (
			                                                    (
			                                                        rel_nodes.ID_node_src = `" . $field_value["data_source_table"] . "`.ID
			                                                        AND rel_nodes.contest_src = " . $db_gallery->toSql($field_value["data_source"], "Text") . "
			                                                        AND rel_nodes.contest_dst = " . $db_gallery->toSql($vgallery_name, "Text") . " 
			                                                        AND rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
			                                                    ) 
			                                                OR 
			                                                    (
			                                                        rel_nodes.ID_node_dst = `" . $field_value["data_source_table"] . "`.ID
			                                                        AND rel_nodes.contest_dst = " . $db_gallery->toSql($field_value["data_source"], "Text") . "
			                                                        AND rel_nodes.contest_src = " . $db_gallery->toSql($vgallery_name, "Text") . " 
			                                                        AND rel_nodes.ID_node_src = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . " 
			                                                    )
			                                                )
			                                            WHERE 1
			                                    ) AS `" . $field_key . "`";
											}
										}
	                            		break;
	                            	case "table.alt":
	                            		if($ID_vgallery_nodes > 0) {
	                            			$key = ($schema["db"]["data_source"][$field_value["data_source"]]["key"]
	                            						? $schema["db"]["data_source"][$field_value["data_source"]]["key"]
	                            						: "ID"
	                            					);
											
	                            			if(!isset($schema["db"]["data_source"][$field_value["data_source"]]["sWhere"]) && is_array($schema["db"]["data_source"][$field_value["data_source"]]["fields"]) && count($schema["db"]["data_source"][$field_value["data_source"]]["fields"])) {
	                            				foreach($schema["db"]["data_source"][$field_value["data_source"]]["fields"] AS $schema_field_key => $schema_field_data) {
													if($schema_field_data["hide"] && $schema_field_data["require"]) {
														$schema_field_value = "";
														if(strlen($schema_field_data["value"]) && isset($_REQUEST[$schema_field_data["value"]])) {
															$schema_field_value = $_REQUEST[$schema_field_data["value"]];
														} elseif($schema_field_data["value"] === null) {
															$schema_field_value = $ID_vgallery_nodes;
														}

														if(strlen($schema_field_value))
															$schema["db"]["data_source"][$field_value["data_source"]]["sWhere"] .= " AND `" . $field_value["data_source"] . "`.`" . $schema_field_key . "` = " . $db_gallery->toSql($schema_field_value);
													}
	                            				}
	                            			}

											$arrDataLimit = explode(",", $field_value["data_limit"]);
											if(count($arrDataLimit) > 1) {
	                                            ffErrorHandler::raise("non puo entrare qui: Chidedere ad Alex", E_USER_ERROR, null, get_defined_vars());
											} elseif(strlen($field_value["data_limit"])) {
												if($field_value["extended_type"] == "GMap") 
												{
	                                    			$sSQL_field .= ", (
				                                                SELECT 
				                                                    `" . $field_value["data_source"] . "`.`" .  $field_value["data_limit"] . "_lat` 
				                                                FROM `" . $field_value["data_source"] . "`
				                                                WHERE `" . $field_value["data_source"] . "`.`" . $key . "` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
	                                                        		" . $schema["db"]["data_source"][$field_value["data_source"]]["sWhere"] . "
				                                                    " . ($src["field"]["lang"]
	                                                            		? " AND `" . $field_value["data_source"] . "`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
	                                                            		: ""
				                                                    ) . "
				                                                LIMIT 1
				                                                ) AS `" . $field_key . "_lat`";														
	                                    			$sSQL_field .= ", (
				                                                SELECT 
				                                                    `" . $field_value["data_source"] . "`.`" .  $field_value["data_limit"] . "_lng` 
				                                                FROM `" . $field_value["data_source"] . "`
				                                                WHERE `" . $field_value["data_source"] . "`.`" . $key . "` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
	                                                        		" . $schema["db"]["data_source"][$field_value["data_source"]]["sWhere"] . "
				                                                    " . ($src["field"]["lang"]
	                                                            		? " AND `" . $field_value["data_source"] . "`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
	                                                            		: ""
				                                                    ) . "
				                                                LIMIT 1
				                                                ) AS `" . $field_key . "_lng`";														
	                                    			$sSQL_field .= ", (
				                                                SELECT 
				                                                    `" . $field_value["data_source"] . "`.`" .  $field_value["data_limit"] . "_zoom` 
				                                                FROM `" . $field_value["data_source"] . "`
				                                                WHERE `" . $field_value["data_source"] . "`.`" . $key . "` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
	                                                        		" . $schema["db"]["data_source"][$field_value["data_source"]]["sWhere"] . "
				                                                    " . ($src["field"]["lang"]
	                                                            		? " AND `" . $field_value["data_source"] . "`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
	                                                            		: ""
				                                                    ) . "
				                                                LIMIT 1
				                                                ) AS `" . $field_key . "_zoom`";		
	                                    			$sSQL_field .= ", (
				                                                SELECT 
				                                                    `" . $field_value["data_source"] . "`.`" .  $field_value["data_limit"] . "_title` 
				                                                FROM `" . $field_value["data_source"] . "`
				                                                WHERE `" . $field_value["data_source"] . "`.`" . $key . "` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
	                                                        		" . $schema["db"]["data_source"][$field_value["data_source"]]["sWhere"] . "
				                                                    " . ($src["field"]["lang"]
	                                                            		? " AND `" . $field_value["data_source"] . "`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
	                                                            		: ""
				                                                    ) . "
				                                                LIMIT 1
				                                                ) AS `" . $field_key . "_title`";										
												} else {
		                                            if($field_value["data_source"] == $src["table"]) {
                                                		$component["data_source"] = $field_value["data_limit"];
		                                            } else {
	                            						$sSQL_field .= ", ( SELECT `" .  $field_value["data_limit"] . "`
	                            							FROM `" . $field_value["data_source"] . "`
	                            							WHERE `" . $key . "` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
	                            								" . $schema["db"]["data_source"][$field_value["data_source"]]["sWhere"] . "
	                            							LIMIT 1
	                            						) AS `" . $field_key . "`"; 
		                                            }
												}
											} else {
	                                            $sSQL_field .= ", '' AS `" . $field_key . "`";                                                 
	                                        }
										}
	                            		break;
	                            	case "google.maps":
	                            		$tbl_map_marker = "module_maps_marker";
										$component["name"] = str_replace("component", "", strtolower($field_value["extended_type"]));
	                            		$component["title"] = ($schema["db"]["data_source"][$tbl_map_marker]["label"]
	                            			? $schema["db"]["data_source"][$tbl_map_marker]["label"]
	                            			: ffTemplate::_get_word_by_code($field_value["name"])
	                            		);
	                            		$component["data_source"] = ($schema["db"]["data_source"][$tbl_map_marker]["table"] ? $schema["db"]["data_source"][$tbl_map_marker]["table"] : $tbl_map_marker);
										$component["compare_key"] = ($schema["db"]["data_source"][$tbl_map_marker]["key"]
	                            			? $schema["db"]["data_source"][$tbl_map_marker]["key"]
	                            			: "ID_node"
	                            		);	 
	                            		$component["order_by"] = ($schema["db"]["data_source"][$tbl_map_marker]["order_by"]
	                            			? $schema["db"]["data_source"][$tbl_map_marker]["order_by"]
	                            			: "ID"
	                            		);
	                            		$component["record_url"] = $schema["db"]["data_source"][$tbl_map_marker]["record_url"];
	                            		$component["record_id"] = $schema["db"]["data_source"][$tbl_map_marker]["record_id"];
	                            		$component["record_key"] = ($schema["db"]["data_source"][$tbl_map_marker]["record_key"]
	                            			? $schema["db"]["data_source"][$tbl_map_marker]["record_key"]
	                            			: "ID"
	                            		);
	                            		if(is_array($schema["db"]["data_source"][$tbl_map_marker]["record_params"]) && count($schema["db"]["data_source"][$tbl_map_marker]["record_params"])) {
	                            			foreach($schema["db"]["data_source"][$tbl_map_marker]["record_params"] AS $record_param_key => $record_param_value) {
	                            				if($record_param_value === null) {
	                            					$component["record_params"] .= $record_param_key . "=" . $db_gallery->toSql(
	                            						($ID_vgallery_nodes
								    						? $ID_vgallery_nodes
								    						: $ID_vgallery_nodes_tmp
								    					), "Number", false) . "&";
												} elseif(strlen($record_param_value) && is_string($record_param_value)) {
	                            					$component["record_params"] .= $record_param_key . "=" . $db_gallery->toSql($record_param_value, "Text", false) . "&";
	                            					$component["where"][] = " AND `" . $record_param_key . "`=" .  $db_gallery->toSql($record_param_value);
	                            					$component["hidden_fields"][$record_param_key] = new ffData($record_param_value);
												} elseif($_REQUEST[$record_param_key]) {
													if($record_param_value["request"] && $_REQUEST[$record_param_value["request"]]) {
	                            						$component["record_params"] .= $record_param_value["request"] . "=" . $db_gallery->toSql($_REQUEST[$record_param_value["request"]], "Text", false) . "&";
														$component["hidden_fields"][$record_param_value["request"]] = new ffData($_REQUEST[$record_param_value["request"]]);	                            									
	                            						if($record_param_value["field"])
	                            							$component["where"][] = " AND `" . $record_param_value["field"] . "`=" .  $db_gallery->toSql($_REQUEST[$record_param_value["request"]]);
														
													} else {
	                            						$component["record_params"] .= $record_param_key . "=" . $db_gallery->toSql($_REQUEST[$record_param_key], "Text", false) . "&";
	                            						$component["where"][] = " AND `" . $record_param_key . "`=" .  $db_gallery->toSql($_REQUEST[$record_param_key]);
	                            						$component["hidden_fields"][$record_param_key] = new ffData($_REQUEST[$record_param_key]);
													}
												}
	                            			}
	                            		}

	                            		$tmp_fields = array();
										$tmp_fields = $schema["db"]["data_source"][$tbl_map_marker]["field_default"];
										if(!$tmp_fields)
											$tmp_fields[] = $component["order_by"];

	                            		foreach($tmp_fields AS $tmp_fields_name) {
	                            			if($schema["db"]["data_source"][$tbl_map_marker]["fields"][$tmp_fields_name]["hide"])
	                            				continue;

	                            			$component["fields"][$tmp_fields_name] = $schema["db"]["data_source"][$tbl_map_marker]["fields"][$tmp_fields_name];
	                            			$component["fields"][$tmp_fields_name]["ID"] = $tmp_fields_name;
	                            			$component["fields"][$tmp_fields_name]["label"] = ucwords(str_replace(array("-", "_"), " ", $tmp_fields_name));
	                            		}	                            		
	                            		break;
	                            	case "selection":
	                            		/*$key = ($schema["db"]["data_source"][$field_value["data_source"]]["key"]
	                            					? $schema["db"]["data_source"][$field_value["data_source"]]["key"]
	                            					: "ID"
	                            				);
										
	                            		if(!isset($schema["db"]["data_source"][$field_value["data_source"]]["sWhere"]) && is_array($schema["db"]["data_source"][$field_value["data_source"]]["fields"]) && count($schema["db"]["data_source"][$field_value["data_source"]]["fields"])) {
	                            			foreach($schema["db"]["data_source"][$field_value["data_source"]]["fields"] AS $schema_field_key => $schema_field_data) {
												if($schema_field_data["hide"] && $schema_field_data["require"]) {
													$schema_field_value = "";
													if(strlen($schema_field_data["value"]) && isset($_REQUEST[$schema_field_data["value"]])) {
														$schema_field_value = $_REQUEST[$schema_field_data["value"]];
													} elseif($schema_field_data["value"] === null) {
														$schema_field_value = $ID_vgallery_nodes;
													}

													if(strlen($schema_field_value))
														$schema["db"]["data_source"][$field_value["data_source"]]["sWhere"] .= " AND `" . $field_value["data_source"] . "`.`" . $schema_field_key . "` = " . $db_gallery->toSql($schema_field_value);
												}
	                            			}
	                            		}*/
	                            				                            	
										if(strlen($field_value["data_limit"])) {
	                            			$component["name"] = str_replace("component", "", strtolower($field_value["extended_type"]));
	                            			$component["title"] = ($schema["db"]["data_source"][$field_value["data_source"]]["label"]
	                            				? $schema["db"]["data_source"][$field_value["data_source"]]["label"]
	                            				: ffTemplate::_get_word_by_code($field_value["name"])
	                            			);
	                            			$component["data_source"] = ($schema["db"]["data_source"][$field_value["data_source"]]["table"] ? $schema["db"]["data_source"][$field_value["data_source"]]["table"] : $field_value["data_source"]);
											$component["compare_key"] = ($schema["db"]["data_source"][$field_value["data_source"]]["key"]
	                            				? $schema["db"]["data_source"][$field_value["data_source"]]["key"]
	                            				: "ID_node"
	                            			);	 

	                            			$component["record_url"] = $schema["db"]["data_source"][$field_value["data_source"]]["record_url"];
	                            			$component["record_id"] = $schema["db"]["data_source"][$field_value["data_source"]]["record_id"];
	                            			$component["record_key"] = ($schema["db"]["data_source"][$field_value["data_source"]]["record_key"]
	                            				? $schema["db"]["data_source"][$field_value["data_source"]]["record_key"]
	                            				: "ID"
	                            			);
	                            			if(is_array($schema["db"]["data_source"][$field_value["data_source"]]["record_params"]) && count($schema["db"]["data_source"][$field_value["data_source"]]["record_params"])) {
	                            				foreach($schema["db"]["data_source"][$field_value["data_source"]]["record_params"] AS $record_param_key => $record_param_value) {
	                            					if($record_param_value === null) {
	                            						$component["record_params"] .= $record_param_key . "=" . $db_gallery->toSql(
	                            							($ID_vgallery_nodes
								    							? $ID_vgallery_nodes
								    							: $ID_vgallery_nodes_tmp
								    						), "Number", false) . "&";
													} elseif(strlen($record_param_value) && is_string($record_param_value)) {
	                            						$component["record_params"] .= $record_param_key . "=" . $db_gallery->toSql($record_param_value, "Text", false) . "&";
	                            						$component["where"][] = " AND `" . $record_param_key . "`=" .  $db_gallery->toSql($record_param_value);
	                            						$component["hidden_fields"][$record_param_key] = new ffData($record_param_value);
													} elseif($_REQUEST[$record_param_key]) {
														if($record_param_value["request"] && $_REQUEST[$record_param_value["request"]]) {
	                            							$component["record_params"] .= $record_param_value["request"] . "=" . $db_gallery->toSql($_REQUEST[$record_param_value["request"]], "Text", false) . "&";
															$component["hidden_fields"][$record_param_value["request"]] = new ffData($_REQUEST[$record_param_value["request"]]);	                            									
	                            							if($record_param_value["field"])
	                            								$component["where"][] = " AND `" . $record_param_value["field"] . "`=" .  $db_gallery->toSql($_REQUEST[$record_param_value["request"]]);
															
														} else {
	                            							$component["record_params"] .= $record_param_key . "=" . $db_gallery->toSql($_REQUEST[$record_param_key], "Text", false) . "&";
	                            							$component["where"][] = " AND `" . $record_param_key . "`=" .  $db_gallery->toSql($_REQUEST[$record_param_key]);
	                            							$component["hidden_fields"][$record_param_key] = new ffData($_REQUEST[$record_param_key]);
														}
													}
	                            				}
	                            			}

	                            			$tmp_fields = array();
	                            			if($field_value["data_limit"] && $field_value["data_limit"] !== "null") {
	                            				$tmp_fields = explode(",", $field_value["data_limit"]);
											} else {
												$tmp_fields = $schema["db"]["data_source"][$field_value["data_source"]]["field_default"];
												if(!$tmp_fields)
													$tmp_fields[] = $component["order_by"];
											}

	                            			foreach($tmp_fields AS $tmp_fields_name) {
	                            				if($schema["db"]["data_source"][$field_value["data_source"]]["fields"][$tmp_fields_name]["hide"])
	                            					continue;

	                            				$component["fields"][$tmp_fields_name] = $schema["db"]["data_source"][$field_value["data_source"]]["fields"][$tmp_fields_name];
	                            				$component["fields"][$tmp_fields_name]["ID"] = $tmp_fields_name;
	                            				$component["fields"][$tmp_fields_name]["label"] = ucwords(str_replace(array("-", "_"), " ", $tmp_fields_name));
	                            			}
											
											$component["order_by"] = ($schema["db"]["data_source"][$field_value["data_source"]]["order_by"] && $component["fields"][$schema["db"]["data_source"][$field_value["data_source"]]["order_by"]]
	                            				? $schema["db"]["data_source"][$field_value["data_source"]]["order_by"]
	                            				: $component["record_key"]
	                            			);	                            			
										}
	                            		break;
									case "media":
										if($ID_vgallery_nodes > 0) {
	                            			//ffErrorHandler::raise("ASD", E_USER_ERROR, null,get_defined_vars());
	                            			$arrGallery = null;
	                            			$arrMedia = glob(DISK_UPDIR . stripslash($field_value["vgallery_node_parent"]) . "/" . $field_value["vgallery_node_name"] . "/" . $field_value["smart_url"] . "/*");
	                            			if(is_array($arrMedia) && count($arrMedia)) {
	                            				foreach($arrMedia AS $arrMedia_path) {
													if(strpos(basename($arrMedia_path), "conversion") === false && is_file($arrMedia_path) ) {
														$arrGallery[] = str_replace(DISK_UPDIR, "", $arrMedia_path);
													}
	                            				}
	                            			}
	                            			
											$sSQL_field .= ", (" . $db_gallery->toSql((is_array($arrGallery) 
												? implode(",", $arrGallery)
												: ""
											)) . ") AS `" . $field_key . "`";	
	/*
											$sSQL_field .= ", (
		                                            SELECT 
		                                                description 
		                                            FROM " . $src["type"] . "_rel_nodes_fields 
		                                            WHERE ID_nodes = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
		                                                AND ID_fields = " . $db_gallery->toSql($field_value["ID_field"], "Number") . "
		                                                " . ($src["field"]["lang"]
		                                                        ? " AND `" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
		                                                        : ""
		                                                ) . "
		                                            LIMIT 1
		                                            ) AS `" . $field_key . "`";		
													*/													
										}
	                            		break;										
									default:
										if($ID_vgallery_nodes > 0) {
											if($field_value["extended_type"] == "GMap") 
											{
												$sSQL_field .= ", (
		                                                    SELECT 
		                                                        `module_maps_marker`.`coords_lat` 
		                                                    FROM `module_maps_marker`
		                                                    WHERE `module_maps_marker`.`ID_node` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
		                                                        AND `module_maps_marker`.`tbl_src` = " . $db_gallery->toSql($src["table"]) . "
																" . ($src["field"]["lang"]
																	? " AND `module_maps_marker`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
																	: ""
																) . "
		                                                    LIMIT 1
		                                                    ) AS `" . $field_key . "_lat`"; 
	                                    		$sSQL_field .= ", (
		                                                    SELECT 
		                                                        `module_maps_marker`.`coords_lng` 
		                                                    FROM `module_maps_marker`
		                                                    WHERE `module_maps_marker`.`ID_node` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
		                                                        AND `module_maps_marker`.`tbl_src` = " . $db_gallery->toSql($src["table"]) . "
		                                                        " . ($src["field"]["lang"]
																	? " AND `module_maps_marker`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number") 
																	: ""
																) . "
		                                                    LIMIT 1
		                                                    ) AS `" . $field_key . "_lng`"; 
	                                    		$sSQL_field .= ", (
		                                                    SELECT 
		                                                        `module_maps_marker`.`coords_zoom` 
		                                                    FROM `module_maps_marker`
		                                                    WHERE `module_maps_marker`.`ID_node` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
		                                                        AND `module_maps_marker`.`tbl_src` = " . $db_gallery->toSql($src["table"]) . "
		                                                        " . ($src["field"]["lang"]
	                                                        		? " AND `module_maps_marker`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
	                                                        		: ""
		                                                        ) . "
		                                                    LIMIT 1
		                                                    ) AS `" . $field_key . "_zoom`"; 
	                                    		$sSQL_field .= ", (
		                                                    SELECT 
		                                                        `module_maps_marker`.`coords_title` 
		                                                    FROM `module_maps_marker`
		                                                    WHERE `module_maps_marker`.`ID_node` = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
		                                                        AND `module_maps_marker`.`tbl_src` = " . $db_gallery->toSql($src["table"]) . "
		                                                        " . ($src["field"]["lang"]
		                                                            ? " AND `module_maps_marker`.`" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
		                                                            : ""
		                                                        ) . "
		                                                    LIMIT 1
		                                                    ) AS `" . $field_key . "_title`";										
											} 
											else 
											{
												$sSQL_field .= ", (
		                                                SELECT 
		                                                    description 
		                                                FROM " . $src["type"] . "_rel_nodes_fields 
		                                                WHERE ID_nodes = " . $db_gallery->toSql($ID_vgallery_nodes, "Number") . "
		                                                    AND ID_fields = " . $db_gallery->toSql($field_value["ID_field"], "Number") . "
		                                                    " . ($src["field"]["lang"]
		                                                            ? " AND `" . $src["field"]["lang"] . "` = " . $db_gallery->toSql($field_value["user_vars"]["ID_lang"], "Number")
		                                                            : ""
		                                                    ) . "
		                                                LIMIT 1
		                                                ) AS `" . $field_key . "`";											
											}
										}
	                            		break;
	                            }
	                            ////nn va bene compo
	                            if(!$component["name"] && !$ID_vgallery_nodes) {
		                            $sSQL_field .= ", (
		                                                " . $db_gallery->toSql($field_value["default_value"], $field_value["ff_extended_type"]). "
		                                                ) AS `" . $field_key . "`"; 
		                            
		                            if($field_value["extended_type"] == "GMap") {
		                                $sSQL_field .= ", (
		                                                    ''
		                                                    ) AS `" . $field_key . "_lat`"; 
		                                $sSQL_field .= ", (
		                                                    ''
		                                                    ) AS `" . $field_key . "_lng`"; 
		                                $sSQL_field .= ", (
		                                                   ''
		                                                    ) AS `" . $field_key . "_zoom`"; 
		                                $sSQL_field .= ", (
		                                                    ''
		                                                    ) AS `" . $field_key . "_title`"; 
		                            }
	                            }
	                        }            

							switch($component["name"]) {
								case "grid":
									$obj_page_field = ffGrid::factory($cm->oPage);
									$obj_page_field->full_ajax = true;
								    $obj_page_field->id = str_replace("field_", "detail-", $field_value["ID"]);
								    $obj_page_field->title = $component["title"];
								    $obj_page_field->source_SQL = "SELECT `" . $component["data_source"] . "`.* 
								    							FROM `" . $component["data_source"] . "`
								    							WHERE `" . $component["data_source"] . "`.`" . $component["compare_key"] . "` = " . $db_gallery->toSql(
								    								($ID_vgallery_nodes
								    									? $ID_vgallery_nodes
								    									: $ID_vgallery_nodes_tmp
								    								), "Number") 
								    							. implode(" ", $component["where"]) . "
								    							
								    							[AND] [WHERE]
								    							[HAVING]
								    							[ORDER]";
								    $obj_page_field->order_default = $component["order_by"];	
								    if($component["fields"][$component["order_by"]]["extended_type"] == "GMap" && strpos($obj_page_field->order_default, "_title") === false) 
								    	$obj_page_field->order_default .= "_title";

									if($component["record_url"]) {
									    $obj_page_field->record_url = $component["record_url"];
									    //$obj_page_field->display_edit_bt = true;
									    $obj_page_field->record_id = $component["record_id"];
									    if($component["record_params"]) {
									        $obj_page_field->addit_insert_record_param = $component["record_params"];
									        $obj_page_field->addit_record_param = $component["record_params"];
										}
									    $obj_page_field->resources[] = $component["record_id"];
									} else {
										$obj_page_field->display_new = false;
										$obj_page_field->display_edit_url = false;
										$obj_page_field->display_delete_bt = false;
									}
									$obj_page_field->buttons_options["export"]["display"] = false;
								    $obj_page_field->use_paging = false;
								    $obj_page_field->use_search = false;

								    $oField = ffField::factory($cm->oPage);
								    $oField->id = $component["record_key"];
								    $oField->data_source = "ID";
								    $oField->base_type = "Number";
								    $obj_page_field->addKeyField($oField);	

								    if(is_array($component["fields"]) && count($component["fields"])) {
										foreach($component["fields"] AS $field_sub_name => $field_sub_value) {
											$field_sub_value["writable"] = false;
											$field_sub_value["encode_entities"] = false;
	                            			if($field_sub_value["extended_type"] == "GMap") { 
	                            				$field_sub_value["ID"] .= "_title";
											} else {
												$field_sub_value["extended_type"] = "default";
											}
										    
										    $oField = ffField::factory($cm->oPage);

										    $js .= get_field_by_extension($oField, $field_sub_value, "vgallery");

										    $obj_page_field->addContent($oField);																					
										}
								    }
								    $cm->oPage->addContent($obj_page_field);								
									break;
								case "detail":
									$obj_page_field = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
								    $obj_page_field->id = str_replace("field_", "detail-", $field_value["ID"]);
								    $obj_page_field->title = $component["title"];
								    $obj_page_field->src_table = $component["data_source"];
								    $obj_page_field->order_default = $component["order_by"];
								    if($component["fields"][$component["order_by"]]["extended_type"] == "GMap" && strpos($obj_page_field->order_default, "_title") === false) 
								    	$obj_page_field->order_default .= "_title";
								    
								    $obj_page_field->fields_relationship = array ($component["compare_key"] => "ID");
								    //$obj_page_field->display_new_location = "Footer";
								    $obj_page_field->display_grid_location = "Footer";
								    $obj_page_field->display_rowstoadd = false;

								    $oField = ffField::factory($cm->oPage);
								    $oField->id = $component["record_key"];
								    $oField->data_source = "ID";
								    $oField->base_type = "Number";
								    $obj_page_field->addKeyField($oField);	
									
								    if(is_array($component["hidden_fields"]) && count($component["hidden_fields"])) {
										foreach($component["hidden_fields"] AS $field_sub_name => $field_sub_value) {
											$obj_page_field->insert_additional_fields[$field_sub_name] = $field_sub_value;
										}
								    }

								    if(is_array($component["fields"]) && count($component["fields"])) {
										foreach($component["fields"] AS $field_sub_name => $field_sub_value) {
										    $oField = ffField::factory($cm->oPage);

										    $js .= get_field_by_extension($oField, $field_sub_value, "vgallery");

										    $obj_page_field->addContent($oField);																					
										}
								    }
								    $cm->oPage->addContent($obj_page_field);
									break;
								case "gmap":
									break;
								default:
		                            $obj_page_field = ffField::factory($cm->oPage);
		                            $obj_page_field->store_in_db = $component["store_in_db"];
		                            $obj_page_field->data_source = $component["data_source"];

			                        $js .= get_field_by_extension($obj_page_field, $field_value, "vgallery");
							}
                                                
							
							

	                        $oRecord->addContent($obj_page_field, $real_group_key);
	                    }
	                }
	            }

	            if(strlen($sSQL_field)) {
	                $oRecord->auto_populate_insert = true;
	                $oRecord->populate_insert_SQL = "SELECT 
	                                                    " . $db_gallery->toSql($path) . " AS `parent`
	                                                    , " . $db_gallery->toSql($type == "node" ? "0" : "1", "Number") . " AS `id_dir`
	                                                    , " . $db_gallery->toSql("load fadeIn") . " AS `ajax_on_event`
	                                                    , " . $db_gallery->toSql("0", "Number") . " AS `highlight`
	                                                    , " . $db_gallery->toSql("1", "Number") . " AS `visible`
	                                                    , " . $db_gallery->toSql("") . " AS `tags`
	                                                    , " . $db_gallery->toSql("") . " AS `place_title`
	                                                    , " . $db_gallery->toSql("") . " AS `place_lat`
	                                                    , " . $db_gallery->toSql("") . " AS `place_lng`
	                                                    , " . $db_gallery->toSql("") . " AS `place_zoom`
	                                                    , " . $db_gallery->toSql("") . " AS `ID_place`
	                                                    $sSQL_field
	                                                ";
	                $oRecord->auto_populate_edit = true;
	                $oRecord->populate_edit_SQL = "SELECT `" . $src["table"] . "`.*
	                                                    $sSQL_field
	                                                FROM `" . $src["table"] . "`
	                                                WHERE `" . $src["table"] . "`.ID = " . $db_gallery->toSql($ID_vgallery_nodes, "Number");
	            }

	            if(array_key_exists("setting", $page_group)) {
	                $force_display_record = true;
                        if($src["field"]["published_at"]) {
	                        $oField = ffField::factory($cm->oPage);
	                        $oField->id = $src["field"]["published_at"];
	                        $oField->container_class = "published_at";
	                        $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_published_at");
	                        $oField->base_type = "timestamp"; 
	                        $oField->extended_type = "DateTime";
	                        $oField->app_type = "DateTime";
							$oField->setWidthLabel(6);
                            $oField->default_value = new ffData(time());
                            $oField->widget = "datepicker";
	                        $oRecord->addContent($oField, "setting");
						}
					if($ID_vgallery_nodes) {
                        if($src["field"]["created"]) {
	                        $oField = ffField::factory($cm->oPage);
	                        $oField->id = $src["field"]["created"];
	                        $oField->container_class = "created";
	                        $oField->store_in_db = false;
	                        $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_created");
	                        $oField->base_type = "timestamp"; 
	                        $oField->extended_type = "DateTime";
	                        $oField->app_type = "DateTime";
							$oField->control_type = "empty";
							$oField->setWidthLabel(6);
	                        $oRecord->addContent($oField, "setting");	                
						}
                        if($src["field"]["last_update"]) {
	                        $oField = ffField::factory($cm->oPage);
	                        $oField->id = $src["field"]["last_update"];
	                        $oField->container_class = "last_update";
	                        $oField->store_in_db = false;
	                        $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_last_update");
	                        $oField->base_type = "timestamp"; 
	                        $oField->extended_type = "DateTime";
	                        $oField->app_type = "DateTime";
							$oField->control_type = "empty";
							$oField->setWidthLabel(6);
	                        $oRecord->addContent($oField, "setting");
						}
                                               
	                } 
  					
		            if($src["field"]["parent"] && !$src["settings"]["enable_multi_cat"] && $src["settings"]["limit_level"] > 1 && (strlen($ID_vgallery_nodes) || strlen($default_field_type))) {
		            	$parent_isset = true;

		                $oField = ffField::factory($cm->oPage);
		                $oField->id = $src["field"]["parent"];
		                $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_parent");
		                $oField->extended_type = "Selection";
		                $oField->source_SQL = $src["sql"]["query"]["parent"];
		                $oField->default_value = new ffData($path, "Text");
		                $oField->required = true;
		                $oRecord->addContent($oField, "setting");
		            }

	                if($src["field"]["visible"] && !$visible_isset) {
                        $visible_field_isset = true;

                        $oField = ffField::factory($cm->oPage);
                        $oField->id = $src["field"]["visible"];
                        $oField->container_class = "visible";
                        $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_visible");
                        $oField->base_type = "Number";
                        $oField->control_type = "checkbox";
                        $oField->extended_type = "Boolean";
                        $oField->checked_value = new ffData("1", "Number");
                        $oField->unchecked_value = new ffData("0", "Number");
                        $oField->default_value = new ffData($component->user_vars["vgallery_visible"], "Number");
                        $oRecord->addContent($oField, "setting");	                
	                }
	                
	                if(($src["field"]["ID_type"] && !$simple_interface && $adv_params)
	                    && ($ID_vgallery_nodes > 0 || count($arrAllowedType[$type]) > 1)
	                ) {
	                    $ID_type_isset = true;

	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = $src["field"]["ID_type"];
	                    $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_type");
	                    $oField->base_type = "Number";
	                    $oField->extended_type = "Selection";
	                    $oField->source_SQL = $src["sql"]["query"]["ID_type"];
	                    $oField->default_value = new ffData($default_field_type, "Number");
	                    $oField->multi_select_one = false;
	                    if($ID_vgallery_nodes > 0 || strlen($default_field_type)) {
	                        $oField->control_type = "label";
	                        if($ID_vgallery_nodes > 0) {
	                            $oField->store_in_db = false;
	                            $ID_type_isset = false;
							}
	                    } else {
	                        $oField->required = true;
	                    }

	                    if(AREA_VGALLERY_TYPE_SHOW_MODIFY) {
                    		$oField->widget = "actex";
                    		$oField->actex_update_from_db = true;
						    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery/type/extra?src=" . $src["type"];
						    $oField->actex_dialog_show_add = false;
						    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
						    //$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=vgalleryTypeGroupModify_confirmdelete";
						    $oField->resources[] = "VGalleryTypeModify";
	                    }
	                    $oRecord->addContent($oField, "setting");
	                }            

	                if($src["settings"]["enable_priority"] && $src["field"]["priority"]) {
                        $oField = ffField::factory($cm->oPage);
                        $oField->id = $src["field"]["priority"];
                        $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_priority");
                        $oField->base_type = "Number";
                        $oField->extended_type = "Selection";
                        $oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");

                        $arrPriorityDefault = array();
	                    $arrPriorityDefault[1] = array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_priority_bottom")));
                        $arrPriorityDefault[2] = array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_very_low")));
                        $arrPriorityDefault[3] = array(new ffData("3", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_low")));
                        $arrPriorityDefault[4] = array(new ffData("4", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_normal")));
                        $arrPriorityDefault[5] = array(new ffData("5", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_hight")));
                        $arrPriorityDefault[6] = array(new ffData("6", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_very_hight")));
                        $arrPriorityDefault[7] = array(new ffData("7", "Number"), new ffData(ffTemplate::_get_word_by_code("vgallery_top")));
                        
                        if($src["settings"]["enable_priority"] == "-1") {
                        	$oField->multi_pairs = $arrPriorityDefault;
						} else {
							$arrPriority = explode(",", $src["settings"]["enable_priority"]);
							foreach($arrPriority AS $priority) {
								$oField->multi_pairs[] = $arrPriorityDefault[$priority];
							}
						}
                        $oRecord->addContent($oField, "setting");	                
	                }		                
	                
 					if(!$simple_interface && $adv_params
	                    && ($src["settings"]["limit_level"] > 1 && ($ID_vgallery_nodes > 0 || strlen($default_field_type)))
	                ) {
	                	if($src["field"]["is_dir"]) {
		                    $is_dir_isset = true;

		                    $oField = ffField::factory($cm->oPage);
		                    $oField->id = $src["field"]["is_dir"];
		                    $oField->container_class = "is_dir";
		                    $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_is_dir");
		                    $oField->base_type = "Number";
		                    $oField->control_type = "checkbox";
		                    $oField->extended_type = "Boolean";
		                    $oField->checked_value = new ffData("1", "Number");
		                    $oField->unchecked_value = new ffData("0", "Number");
		                    if($type == "node") {
		                        $oField->default_value = new ffData("0", "Number");
		                    } elseif($type == "dir") {
		                        $oField->default_value = new ffData("1", "Number");
		                    }
		                    $oField->properties["onchange"] = "javascript:ff.cms.admin.isDir();";
		                    $oRecord->addContent($oField, "setting");
						}	     
						if($src["field"]["ajax"]) {
		                    $oField = ffField::factory($cm->oPage);
		                    $oField->id = $src["field"]["ajax"]["enable"];
		                    $oField->container_class = "use-ajax";
		                    $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_use_ajax");
		                    $oField->control_type = "checkbox";
		                    $oField->extended_type = "Boolean";
		                    $oField->checked_value = new ffData("1");
		                    $oField->unchecked_value = new ffData("0");
		                    $oField->properties["onchange"] = "javascript:ff.cms.admin.UseAjax();";
		                    $oRecord->addContent($oField, "setting");

		                    $oField = ffField::factory($cm->oPage);
		                    $oField->id = $src["field"]["ajax"]["event"];
		                    $oField->container_class = "use-ajax-dep on-event";
		                    $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_ajax_on_event");
		                    $oField->extended_type = "Selection";
		                    $oField->multi_pairs = array (
		                                                array(new ffData("load fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fade"))),
		                                                array(new ffData("load show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_show"))),
		                                                array(new ffData("load fadeToggle"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fadeToggle"))),
		                                                array(new ffData("load hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_hide"))),
		                                                array(new ffData("reload show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_show"))), 
		                                                array(new ffData("reload fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_fade"))),
		                                                array(new ffData("reload hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_hide")))
		                                           );
		                    $oField->default_value = new ffData("load fadeIn");
		                    $oField->multi_select_one = false;
		                    $oRecord->addContent($oField, "setting");
						}
	                }
	                
	                if(!$simple_interface 
	                    && (strlen($ID_vgallery_nodes) || strlen($default_field_type)) 
	                    && $adv_params
	                    && ($type == "dir" && !$src["settings"]["drag_sort_dir_enabled"])
	                    && ($type != "dir" && !$src["settings"]["drag_sort_node_enabled"])
	                    && $src["field"]["order"]
	                ) {
	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = $src["field"]["order"];
	                    $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_order");
	                    $oField->base_type = "Number";
	                    $oRecord->addContent($oField, "setting");
	                }

                    if($src["settings"]["show_owner_by_categories"] && $src["field"]["owner"]) {
                        $oField = ffField::factory($cm->oPage);
                        $oField->id = $src["field"]["owner"];
                        $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_owner");
                        $oField->base_type = "Number";
                        //$oField->extended_type = "Selection";
                        $oField->widget = "autocomplete";
                        $oField->source_SQL = "SELECT
                                                    anagraph.ID AS ID
                                                    , CONCAT(anagraph.name, ' ', anagraph.surname) AS full_name
                                                    , IF(anagraph.avatar = ''
                                                    	, '" . cm_getClassByFrameworkCss("noimg", "icon-tag", "2x") . " ' 
                                                    	, IF(LOCATE('.svg', anagraph.avatar)
                                                    		, CONCAT('<img src=\"', anagraph.avatar, '\" width=\"32\" height\"32\" />')  
                                                    		, CONCAT('<img src=\"" . CM_SHOWFILES . "/32x32', anagraph.avatar, '\" />')  
                                                    	)
                                                    ) AS image
                                                FROM anagraph
                                                WHERE 
                                                    " . ($src["settings"]["show_owner_by_categories"] == "-1"
                                                        ? "1"
                                                        : " CONCAT(',',  anagraph.categories , ',') REGEXP ',(" . str_replace(",", "|", $src["settings"]["show_owner_by_categories"]). "),' "
                                                    ) . "
                                                [AND] [WHERE]
                                                [HAVING]
                                                [ORDER] [COLON] full_name 
                                                [LIMIT]";
                        $oField->autocomplete_compare = "CONCAT(anagraph.name, ' ', anagraph.surname)";
                    	$oField->actex_update_from_db = true;
                    	$oField->autocomplete_combo = true;
                    	$oField->autocomplete_minLength = 0; 
                    	$oField->encode_entities = false;
                    	
                        //$oField->multi_select_one = false;
                        $oRecord->addContent($oField, "setting");
                    }
                    
                    if($src["settings"]["show_isbn"]) {
	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = $src["field"]["isbn"];
	                    $oField->container_class = "isbn";
	                    $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_isbn");
	                    $oField->fixed_post_content = '<img class="qrcode" src="https://chart.googleapis.com/chart?chs=80x80&cht=qr&chl=&choe=UTF-8" />
	                    <script type="text/javascript">
	                    	jQuery(function() {
	                    		jQuery(".qrcode").attr("src", "https://chart.googleapis.com/chart?chs=80x80&cht=qr&chl=" + jQuery("#VGalleryNodesModify_isbn").val() + "&choe=UTF-8");
	                    	});
	                    </script>
	                    ';
	                    $oField->framework_css["fixed_post_content"] = true;
	                    $oField->properties["onkeyup"] = "javascript:jQuery(this).closest('.isbn').find('.qrcode').attr('src', 'https://chart.googleapis.com/chart?chs=80x80&cht=qr&chl=' + jQuery(this).val() + '&choe=UTF-8');";
	                    $oRecord->addContent($oField, "setting");
	                }
                }

                if($src["settings"]["enable_highlight"] && array_key_exists("highlight", $page_group)) {
                    if(!$simple_interface && $adv_params
                        && (($ID_vgallery_nodes > 0 || strlen($default_field_type)))
                    ) {
                       /* $oField = ffField::factory($cm->oPage);
                        $oField->id = "class";
                        $oField->container_class = "custom-class";
                        $oField->label = ffTemplate::_get_word_by_code($src["type"] . "_modify_custom_class");
                        $oRecord->addContent($oField, "setting");  */
                    }

                    if(check_function("set_fields_grid_system")) {
                        set_fields_grid_system($oRecord, array(
                                "group" => "highlight"
                                , "fluid" => array( 
                                    "prefix" => "highlight"
                                    , "one_field" => $highlight
                                    , "choice" => false
                                    , "col" => array(
                                    	"default_value" => 0
                                    )
                                )
                                , "class" => true
                                , "wrap" => false
                                , "image" => array(
                                    "prefix" => "highlight_ID_image"
                                    , "default_value" => array(
                                        $file_properties_thumb["image"]["src"]["default"]["ID"]
                                        , $file_properties_thumb["image"]["src"]["md"]["ID"]
                                        , $file_properties_thumb["image"]["src"]["sm"]["ID"]
                                        , $file_properties_thumb["image"]["src"]["xs"]["ID"]
                                    )
                                )
                            )
                        );                 
                    } 
                }

	            if(!strlen($ID_vgallery_nodes)) {
	                if($src["field"]["parent"] && !$parent_isset)
	                    $oRecord->insert_additional_fields[$src["field"]["parent"]] = new ffData($path);
	                if($src["field"]["is_dir"] && !$is_dir_isset)
	                    $oRecord->insert_additional_fields[$src["field"]["is_dir"]] = new ffData($oRecord->user_vars["is_dir"], "Number");
	                if($src["field"]["ID_type"] && !$ID_type_isset)
	                    $oRecord->insert_additional_fields[$src["field"]["ID_type"]] = new ffData($oRecord->user_vars["ID_type"], "Number");
                    if(!$visible_field_isset)
                        $oRecord->insert_additional_fields[$src["field"]["visible"]] =  new ffData($oRecord->user_vars["vgallery_visible"], "Number");
	            } else {
	                if($src["field"]["parent"] && !$parent_isset)
	                    $oRecord->additional_fields[$src["field"]["parent"]] = new ffData($path);
	                if($src["field"]["is_dir"] && !$is_dir_isset)
	                    $oRecord->additional_fields[$src["field"]["is_dir"]] = new ffData($oRecord->user_vars["is_dir"], "Number");
	                if($src["field"]["ID_type"] && !$ID_type_isset)
	                    $oRecord->additional_fields[$src["field"]["ID_type"]] = new ffData($oRecord->user_vars["ID_type"], "Number");
                        
	            }
	        }
	    }

	    
	    $oRecord->user_vars["gallery_model"] = $gallery_model;
	    
	    if($page_field_count > 0 || $force_display_record) {
		    if(!$adv_group && !$src["settings"]["enable_tab"]) {
		    	if(!$cm->isXHR())
		    		$oRecord->framework_css["actions"]["col"] = array(4);

		        $js .= '
		            jQuery("FIELDSET.grp-sys").wrapAll(\'<div class="group-system' . ($cm->oPage->framework_css ? " " . cm_getClassByFrameworkCss(array(4), "col") : "") . '" />\'); 
		            jQuery("FIELDSET.grp-std").wrapAll(\'<div class="group-standard' . ($cm->oPage->framework_css ? " " . cm_getClassByFrameworkCss(array(8), "col") : "") . '" />\');
		        ';
		    }


	        $cm->oPage->addContent($oRecord);
	    }

	    $cm->oPage->tplAddJs("ff.cms.admin", "ff.cms.admin.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools");

	    $js = '<script type="text/javascript">
	            jQuery(function() {
	                ' . $js . '
	            });
	            
	            ff.pluginLoad("ff.cms.admin", "' . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.admin.js" . '", function() {
	                ff.cms.admin.UseAjax();
	                ff.cms.admin.isDir();
	            });
	        </script>';
	        
	        /*
 $( "#StaticModifyLanguages_jtab" ).on( "tabsactivate", function( event, ui ) {
                        ff.cms.admin.makeNewUrl();
                    });

                    ff.cms.admin.makeNewUrl();    	        
	        
	        
	        */
	        
	        
	    $cm->oPage->addContent($js);
	}
}
  
function VGalleryNodesModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();

    if($action == "insert" || $action == "update") {
 		if(is_array($component->form_fields) && count($component->form_fields)) {
	        $arrSmartUrl = array();

	        foreach($component->form_fields AS $field_key => $field_value) {
	            $enable_smart_url = $field_value->user_vars["smart_url"];
	            $str_value = "";
	            if(strpos($field_value->id, "field_") === 0
	                && (is_array($field_value->value) || strlen($field_value->value->getValue()))
	            ) {
	                if($field_value->user_vars["data_type"] == "relationship") {
	                	switch($field_value->user_vars["data_source"]) {
	                		case "anagraph":
			                    $sSQL = "SELECT " . (strlen($field_value->user_vars["data_limit"])
				                            ? "CONCAT(" . str_replace(",", ", ' ', ", $field_value->user_vars["data_limit"]) . ")"
				                            : "name"
				                        ) . " AS `description`
					                    FROM " . $field_value->user_vars["data_source"] . "
					                    WHERE 
					                        " . $field_value->user_vars["data_source"] . ".`ID` IN(" . $db->toSql($field_value->getValue(), "Number", false) . ")
					                    ORDER BY " . $field_value->user_vars["data_source"] . ".`ID`";
	                			break;
	                		case "files";
			                    $sSQL = "SELECT name AS `description`
					                    FROM " . $field_value->user_vars["data_source"] . "
					                    WHERE 
					                        " . $field_value->user_vars["data_source"] . ".`ID` IN(" . $db->toSql($field_value->getValue(), "Number", false) . ")
					                    ORDER BY " . $field_value->user_vars["data_source"] . ".`ID`";	                			
					            break;
							default:	                	
			                    $sSQL = "SELECT DISTINCT GROUP_CONCAT(DISTINCT " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`description` ORDER BY " . $component->user_vars["src"]["type"] . "_fields.`order_thumb` SEPARATOR ' ') AS `description`
				                    FROM " . $component->user_vars["src"]["type"] . "_rel_nodes_fields 
				                        INNER JOIN " . $component->user_vars["src"]["type"] . "_fields ON " . $component->user_vars["src"]["type"] . "_fields.ID = " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.ID_fields
				                        INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = " . $component->user_vars["src"]["type"] . "_fields.ID_data_type
				                        INNER JOIN extended_type ON extended_type.ID = " . $component->user_vars["src"]["type"] . "_fields.ID_extended_type
				                    WHERE 
				                        " . (strlen($field_value->user_vars["data_limit"])
				                            ? "" . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`ID_fields` IN (" . $db->toSql($field_value->user_vars["data_limit"], "Text", false) . ")"
				                            : 1
				                        ) . "
				                        AND " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`ID_nodes` IN (" . $db->toSql($field_value->getValue(), "Number", false) . ")
				                        " . ($component->user_vars["src"]["seo"]["rel_lang"]
	                        				? " AND " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`" . $component->user_vars["src"]["seo"]["rel_lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"], "Number")
	                        				: ""
				                        ) . "
				                        AND extended_type.`group` LIKE 'text%'
				                    GROUP BY " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`ID_nodes`
				                    ORDER BY " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`ID_nodes`";

	                	}
	                    $db->query($sSQL);
	                    if($db->nextRecord()) {
	                        do {
	                            if(strlen($str_value))
	                                $str_value .= " ";
	                            $str_value .= $db->getField("description", "Text", true);
	                        } while($db->nextRecord());
	                    }
	                    $str_value_ori = $str_value;
	                    if(strlen($str_value))
	                        $component->form_fields[$field_key]->user_vars["data_resolved"] = $str_value;
	                } else {
	                    if($field_value->user_vars["extended_type"] == "GMap" && is_array($field_value->value)) {
	                        $field_data 		= new ffData($field_value->value["search"], "Text");
	                        $field_data_ori 	= new ffData($field_value->value_ori["search"], "Text");
	                    } else {
	                        $field_data 		= $field_value->value;
	                        $field_data_ori 	= $field_value->value_ori;
	                    }

	                    $str_value 				= $field_data->getValue();
						$str_value_ori 			= $field_data_ori->getValue();

	                    if($action == "update" 
	                        && $component->form_fields[$field_key]->user_vars["name"] == "system_visible" 
	                        && $component->form_fields[$field_key]->value->getValue() != $component->form_fields[$field_key]->value_ori->getValue()
	                    ) {
	                        $arrSmartUrl[$field_value->user_vars["ID_lang"]]["visible"] = ($component->form_fields[$field_key]->value->getValue() > 0 ? "insert" : "confirmdelete");
	                    }
	                }

	                if($enable_smart_url > 0 && strlen($str_value)) {
	                    if($field_value->user_vars["disable_multilang"] > 0) {
	                    	if(is_array($component->user_vars["lang"]) && count($component->user_vars["lang"])) {
	                    		foreach($component->user_vars["lang"] AS $lang_code => $lang) {
			                        if(strlen($arrSmartUrl[$lang["ID"]]["smart_url"][$enable_smart_url])) {
			                            $arrSmartUrl[$lang["ID"]]["smart_url"][$enable_smart_url] .= " ";
			                        }
			                        $arrSmartUrl[$lang["ID"]]["smart_url"][$enable_smart_url] .= $str_value;

			                        if(strlen($arrSmartUrl[$lang["ID"]]["smart_url_ori"][$enable_smart_url])) {
			                            $arrSmartUrl[$lang["ID"]]["smart_url_ori"][$enable_smart_url] .= " ";
			                        }
			                        $arrSmartUrl[$lang["ID"]]["smart_url_ori"][$enable_smart_url] .= $str_value_ori;
								}
	                    	}
	                    } else {
	                        if(strlen($arrSmartUrl[$field_value->user_vars["ID_lang"]]["smart_url"][$enable_smart_url])) {
	                            $arrSmartUrl[$field_value->user_vars["ID_lang"]]["smart_url"][$enable_smart_url] .= " ";
	                        }
	                        $arrSmartUrl[$field_value->user_vars["ID_lang"]]["smart_url"][$enable_smart_url] .= $str_value;

	                        if(strlen($arrSmartUrl[$field_value->user_vars["ID_lang"]]["smart_url_ori"][$enable_smart_url])) {
	                            $arrSmartUrl[$field_value->user_vars["ID_lang"]]["smart_url_ori"][$enable_smart_url] .= " ";
	                        }
	                        $arrSmartUrl[$field_value->user_vars["ID_lang"]]["smart_url_ori"][$enable_smart_url] .= $str_value_ori;
	                    }
	                }
	            } 
	        }
	        $component->user_vars["arrSmartUrl"] = $arrSmartUrl;
	    }

	    if(is_array($component->user_vars["lang"]) && count($component->user_vars["lang"])) {
	        if(!array_key_exists(LANGUAGE_DEFAULT, $component->user_vars["lang"])) {
	            $component->tplDisplayError(ffTemplate::_get_word_by_code("invalid_language_default"));
	            return true;
	        }	    
	        foreach($component->user_vars["lang"] AS $lang_code => $lang) {
	            $smart_url = "";
	            if(array_key_exists($lang["ID"], $arrSmartUrl) 
	                && array_key_exists("smart_url", $arrSmartUrl[$lang["ID"]]) 
	                && is_array($arrSmartUrl[$lang["ID"]]["smart_url"]) && count($arrSmartUrl[$lang["ID"]]["smart_url"])
	            ) {
	                ksort($arrSmartUrl[$lang["ID"]]["smart_url"]);
	                foreach($arrSmartUrl[$lang["ID"]]["smart_url"] AS $arrSmartUrl_value) {
	                    if(strlen($smart_url)) {
	                        $smart_url .= " ";
	                    }
	                    $smart_url .= $arrSmartUrl_value;
	                }
	            }


	            if($lang_code == LANGUAGE_DEFAULT) {
                    $visible = (isset($component->form_fields["system_" . $lang_code . "_visible"])
                                ? $component->form_fields["system_" . $lang_code . "_visible"]->getValue()
                                : (isset($component->form_fields[$component->user_vars["src"]["field"]["visible"]])
                                    ? $component->form_fields[$component->user_vars["src"]["field"]["visible"]]->getValue()
                                    : false
                                )
                    );
                    
                    if($visible && !strlen($smart_url)) {
                        $component->tplDisplayError(ffTemplate::_get_word_by_code("smart_url_default_empty"));
                        return true;
                    }
	                if(strlen($smart_url)) {
 	                    if(strlen($component->user_vars["force_name"])) {
	                        $component->user_vars["name"] = ffCommon_url_rewrite($component->user_vars["force_name"]);
	                    } else {
	                        $component->user_vars["name"] = ffCommon_url_rewrite($smart_url);
	                    }
	                } else {
	                    $component->user_vars["name"] = $component->user_vars["name_old"];
	                }
	            } elseif($component->user_vars["enable_multilang"]) {
	                if(!strlen($smart_url) && !DISABLE_SMARTURL_CONTROL) {
	                    $component->tplDisplayError(ffTemplate::_get_word_by_code("smart_url_empty") . ": " . $lang_code);
	                    return true;
	                }
	            }
	        }
	    }  
	    
        if(isset($component->form_fields["tags"])) {
            $str_compare_tag = "";
            $arrTags = explode(",", $component->form_fields["tags"]->getValue());
            if(is_array($arrTags) && count($arrTags)) {
                foreach($arrTags AS $tag_value) {
                    if(strlen($tag_value)) {
                        if(!is_numeric($tag_value)) {
							$ID_tag = 0;
                        	$arrNewCategories = array();
                        	$real_value = trim($tag_value);

							$sSQL = "SELECT search_tags.*
									FROM search_tags
									WHERE search_tags.smart_url = " . $db->toSql(ffCommon_url_rewrite($real_value));
							$db->query($sSQL);
							if($db->nextRecord()) {
								if($db->getField("ID_lang", "Number", true) == LANGUAGE_DEFAULT_ID) {
									$arrCategories = array();
									$ID_tag	= $db->getField("ID", "Number", true);

									if($db->getField("categories", "Text", true)) {
										$arrCategories = explode(",", $db->getField("categories", "Text", true));
										if(array_search($component->user_vars["src"]["settings"]["enable_tag"], $arrCategories) === false) {
											$arrNewCategories = $arrCategories;
											$arrNewCategories[] = $component->user_vars["src"]["settings"]["enable_tag"];
										}
									} elseif($component->user_vars["src"]["settings"]["enable_tag"] > 0) {
										$arrNewCategories[] = $component->user_vars["src"]["settings"]["enable_tag"];
									}
									
								} elseif($db->getField("code", "Number", true) > 0) {
									$ID_tag	= $db->getField("code", "Number", true);

									$sSQL = "SELECT search_tags.*
											FROM search_tags
											WHERE search_tags.ID = " . $db->toSql($ID_tag, "Number");
									$db->query($sSQL);
									if($db->nextRecord()) {
										if($db->getField("categories", "Text", true)) {
											$arrCategories = explode(",", $db->getField("categories", "Text", true));
											if(array_search($component->user_vars["src"]["settings"]["enable_tag"], $arrCategories) === false) {
												$arrNewCategories = $arrCategories;
												$arrNewCategories[] = $component->user_vars["src"]["settings"]["enable_tag"];
											}
										}
									}
								}
							}

							if(is_array($arrNewCategories) && count($arrNewCategories)) {
								$sSQL = "UPDATE search_tags SET categories = " . $db->toSql(implode(",", $arrNewCategories)) . "
										WHERE search_tags.ID = " . $db->toSql($ID_tag, "Number");
								$db->execute($sSQL);
							}
							
							if(!$ID_tag) {
	                            $sSQL = "INSERT INTO search_tags
	                                    (
	                                        ID
	                                        , name
	                                        , smart_url
	                                        , ID_lang
	                                        , code
	                                        , categories
	                                        , status
	                                    ) VALUES (
	                                        null
	                                        , " . $db->toSql($real_value) . "
	                                        , " . $db->toSql(ffCommon_url_rewrite($real_value)) . "
	                                        , " . $db->toSql(LANGUAGE_DEFAULT_ID) . "
	                                        , " . $db->toSql($component->user_vars["src"]["settings"]["enable_tag"]) . "
	                                        , " . $db->toSql($real_value) . "
	                                        , 0
	                                    )";
	                            $db->execute($sSQL);
	                            $ID_tag = $db->getInsertID(true);
							}
                            
                            $sSQL = "UPDATE search_tags SET 
                                		code = ID 
                                	WHERE search_tags.ID = " . $db->toSql($ID_tag, "Number");
                            $db->execute($sSQL);
                        } else {
	                        $ID_tag = $tag_value;
                        }

                        if($ID_tag > 0) {
                            if(strlen($str_compare_tag))
                                $str_compare_tag .= ",";

                            $str_compare_tag .= $db->toSql($ID_tag, "Number");
                        }
                    }                
                }
            }

            $component->form_fields["tags"]->setValue($str_compare_tag);
        }    
    }
    
    switch($action) {
        case "insert":
             if(isset($component->form_fields["parent"])) {
                $actual_path = $component->form_fields["parent"]->getValue();
                $actual_old_path = $component->form_fields["parent"]->value_ori->getValue();
            } else {
                $actual_path = $component->user_vars["parent_old"];
                $actual_old_path = $component->user_vars["parent_old"];
            }
        	if(is_array($component->form_fields) && count($component->form_fields)) {
        		foreach($component->form_fields AS $field_key => $field_value) {
                    $field_value->file_storing_path = DISK_UPDIR . $field_value->user_vars["prefix_file_system"] . stripslash($actual_path) . "/" . $component->user_vars["name"] . $field_value->user_vars["gallery_sub_dir"];

        		}
        	}
        	break;
           /* if(!isset($component->detail["VGalleryNodesModifyDetail"])) {
                $vgallery_name = $_REQUEST["vname"];
                $type = $_REQUEST["type"];
                $path = $_REQUEST["path"];
                $ret_url = $_REQUEST["ret_url"];
                $ftype = $component->form_fields["ID_type"]->getValue();

                ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "/modify?type=" . urlencode($type) . "&vname=" . urlencode($vgallery_name) . "&path=" . urlencode($path) . "&extype=vgallery_nodes&ftype=" . urlencode($ftype) . "&ret_url=" . urlencode($ret_url));
            }*/
        case "update": 
 
            break;
        case "delete":
            break;
        case "confirmdelete":
            if(check_function("delete_vgallery")) {
                $db->query("SELECT 
                				" . $component->user_vars["src"]["sql"]["select"]["vgallery_name"] . " AS vgallery_name 
                                , `" . $component->user_vars["src"]["table"] . "`.`" . $component->user_vars["src"]["field"]["name"] . "` AS name
                                , `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` AS parent
                            FROM `" . $component->user_vars["src"]["table"] . "` 
                            WHERE `" . $component->user_vars["src"]["table"] . "`.ID = " . $db->toSql($component->key_fields["ID"]->value)
                        );
                if($db->nextRecord()) {
                    $vgallery_name = $db->getField("vgallery_name", "Text", true);
                    delete_vgallery($db->getField("parent", "Text", true), $db->getField("name", "Text", true), $vgallery_name);
                }
            }
            break;
    }
    return false;
}


function VGalleryNodesModify_on_done_action($component, $action) {
	$cm = cm::getInstance();
    $db = ffDB_Sql::factory();
        //ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());

    if(strlen($action)) {
        $check_rel_user = false;
		//FRAMEWORK EVENT WITH WG-MPAY
		$res = $cm->doEvent("vg_on_vgallery_action_done", array(&$component, $action));        

        $db->query("SELECT 
                        " . $component->user_vars["src"]["type"] . "_type.name AS type
                        , " . $component->user_vars["src"]["type"] . "_type.strip_stop_words AS strip_stop_words
                        , " . $component->user_vars["src"]["type"] . "_type.tags_in_keywords AS tags_in_keywords
                        , " . $component->user_vars["src"]["type"] . "_type.rule_meta_title AS rule_meta_title
                        , " . $component->user_vars["src"]["type"] . "_type.rule_meta_description AS rule_meta_description
                    FROM `" . $component->user_vars["src"]["table"] . "` 
                        INNER JOIN " . $component->user_vars["src"]["type"] . "_type ON " . $component->user_vars["src"]["type"] . "_type.ID = `" . $component->user_vars["src"]["table"] . "` .ID_type
                    WHERE `" . $component->user_vars["src"]["table"] . "` .ID = " . $db->toSql($component->key_fields["ID"]->value)
                );
        if($db->nextRecord()) {
            $type = $db->getField("type", "Text", true);
            $strip_stop_words = $db->getField("strip_stop_words", "Number", true);
            $tags_in_keywords = $db->getField("tags_in_keywords", "Number", true);
            $rule_meta_title = $db->getField("rule_meta_title", "Text", true);
            $rule_meta_description = $db->getField("rule_meta_description", "Text", true);
        }

        $component->user_vars["strip_stop_words"] = $strip_stop_words;
        $component->user_vars["tags_in_keywords"] = $tags_in_keywords;
        $component->user_vars["rule_meta_title"] = $rule_meta_title;
        $component->user_vars["rule_meta_description"] = $rule_meta_description;

        $ID_node = $component->key_fields["ID"]->getValue();
        if(isset($component->form_fields["is_dir"])) {
            $is_dir = $component->form_fields["is_dir"]->getValue();
        } else {
            $is_dir = $component->user_vars["is_dir"];
        }
        
        if(!is_array($component->user_vars["src"]["settings"]) && strlen($component->user_vars["src"]["settings"])) {
			$db->query("SELECT 
        				`" . $component->user_vars["src"]["settings"] . "`.*
                    FROM `" . $component->user_vars["src"]["table"] . "`  
                        INNER JOIN `" . $component->user_vars["src"]["settings"] . "` ON `" . $component->user_vars["src"]["settings"] . "`.ID = `" . $component->user_vars["src"]["table"] . "` .ID_" . $component->user_vars["src"]["settings"] . "
                    WHERE `" . $component->user_vars["src"]["table"] . "` .ID = " . $db->toSql($component->key_fields["ID"]->value)
                );
        	if($db->nextRecord()) {  
				$component->user_vars["src"]["settings"] = array(
        			"limit_type" 							=> $db->getField("limit_type", "Text", true)
        			, "limit_level" 						=> $db->getField("limit_level", "Number", true)
        			, "insert_on_lastlevel" 				=> $db->getField("insert_on_lastlevel", "Number", true)
        			, "ID_vgallery" 						=> $db->getField("ID", "Number", true)
        			, "enable_highlight" 					=> $db->getField("enable_highlight", "Number", true)
        			, "show_owner_by_categories" 			=> $db->getField("show_owner_by_categories", "Text", true)
        			, "show_isbn" 							=> $db->getField("show_isbn", "Number", true)
        			, "enable_multilang" 					=> $db->getField("enable_multilang_visible", "Number", true)
        			, "drag_sort_node_enabled" 				=> $db->getField("drag_sort_node_enabled", "Number", true)
        			, "drag_sort_dir_enabled" 				=> $db->getField("drag_sort_dir_enabled", "Number", true)
        			, "enable_tag" 							=> $db->getField("enable_tag", "Number", true)
					, "enable_multi_cat" 					=> ($is_dir
																? false
																: $db->getField("enable_multi_cat", "Number", true)
															)	
					, "enable_place" 						=> $db->getField("enable_place", "Number", true)
					, "enable_referer" 						=> $db->getField("enable_referer", "Number", true)
        			, "enable_tab" 							=> $db->getField("enable_tab", "Number", true)
        			, "enable_model" 						=> true
        			, "enable_adv_group"					=> true
        			, "enable_adv_visible" 					=> true
        			, "name"								=> $db->getField("name", "Text", true)
        			, "enable_email_notify_on_insert"		=> $db->getField("enable_email_notify_on_insert", "Number", true)
        			, "enable_email_notify_on_update"		=> $db->getField("enable_email_notify_on_update", "Number", true)
        			, "email_notify_show_detail"			=> $db->getField("email_notify_show_detail", "Number", true)
        			, "data_ext"							=> $db->getField("data_ext", "Text", true)
        			, "permalink_rule"						=> $db->getField("permalink_rule", "Text", true)
        		);
			}
        }
        

        /*
        if(isset($component->form_fields["ID_type"])) {
            $ID_type = $component->form_fields["ID_type"]->getValue();
        } else {
            $ID_type = $component->user_vars["ID_type"];
        }*/

        
        if(isset($component->form_fields["parent"])) {
            $actual_path = $component->form_fields["parent"]->getValue();
            $actual_old_path = $component->form_fields["parent"]->value_ori->getValue();
        } else {
            $actual_path = $component->user_vars["parent_old"];
            $actual_old_path = $component->user_vars["parent_old"];
        }

        $item_name = $component->user_vars["name"];
        $item_name_old = $component->user_vars["name_old"];
/*        
 		$db->query("SELECT * 
                    FROM `" . $component->user_vars["src"]["table"] . "`  
                    WHERE `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` = " . $db->toSql($actual_path) . "
                        AND `" . $component->user_vars["src"]["table"] . "`.`" . $component->user_vars["src"]["field"]["name"] . "` = " . $db->toSql($item_name) . "
                        AND `" . $component->user_vars["src"]["table"] . "` .ID <> " . $db->toSql($ID_node)
                );
        if($db->nextRecord()) {
            $not_unic = true;
        } else {
            $not_unic = false;
        }

        if(($not_unic || AREA_VGALLERY_ADD_ID_IN_REALNAME) && strpos($item_name, "-" . $ID_node) === false && !$is_dir) {
        	$item_name = $item_name . "-" . $ID_node;
            $component->user_vars["name"] = $item_name;
        }*/

        switch($action) {
            case "insert":
                if(check_function("update_vgallery")) {
                    $smart_url_default = update_vgallery($component, $action);

                    if(!strlen($smart_url_default)) {
                         $sSQL = "DELETE FROM 
                                `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
                            WHERE `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_node, "Number");
                        $db->execute($sSQL);

                         $sSQL = "DELETE FROM 
                                `" . $component->user_vars["src"]["table"] . "_rel_languages` 
                            WHERE `" . $component->user_vars["src"]["table"] . "_rel_languages`.`ID_nodes` = " . $db->toSql($ID_node, "Number");
                        $db->execute($sSQL);

                         $sSQL = "DELETE FROM 
                                `" . $component->user_vars["src"]["table"] . "`  
                            WHERE `" . $component->user_vars["src"]["table"] . "` .`ID` = " . $db->toSql($ID_node, "Number");
                        $db->execute($sSQL);
                    }
                }
                
                if(!strlen($item_name)) {
                    $component->tplDisplayError(ffTemplate::_get_word_by_code("name_empty"));
                    return true;
                }                
                
                if(isset($component->insert_additional_fields["owner"]))
                    $owner = $component->insert_additional_fields["owner"]->getValue();
                else
                    $owner = null;


                if(ENABLE_ADV_PERMISSION && $component->user_vars["src"]["settings"]["enable_adv_group"] && $owner > 0) 
                {
                    $db_gid = ffDB_Sql::factory();
                    
                    $user_permission = get_session("user_permission");
                    $primary_gid = ($user_permission["primary_gid_default"] > 0
                                        ? $user_permission["primary_gid_default"]
                                        : $user_permission["primary_gid"]
                                );
                    if($primary_gid > 0) {
                        $sSQL = "INSERT INTO `vgallery_nodes_rel_groups` 
                                ( 
                                    `ID_vgallery_nodes` 
                                    , `gid`  
                                    , `mod` 
                                )
                                VALUES
                                (
                                    " . $db_gid->toSql($ID_node, "Number") . "
                                    , " . $db_gid->toSql($primary_gid, "Number") . "
                                    , '3' 
                                )";
                        $db_gid->execute($sSQL);
                    }                
                    if(is_array($user_permission["groups"]) && count($user_permission["groups"])) {
                        foreach($user_permission["groups"] AS $gid) {
                            if($primary_gid == $gid)
                                continue;

                            $sSQL = "INSERT INTO `vgallery_nodes_rel_groups` 
                                    ( 
                                        `ID_vgallery_nodes` 
                                        , `gid`  
                                        , `mod` 
                                    )
                                    VALUES
                                    (
                                        " . $db_gid->toSql($ID_node, "Number") . "
                                        , " . $db_gid->toSql($gid, "Number") . "
                                        , '1' 
                                    )";
                            $db_gid->execute($sSQL);
                        }
                    }
                }
                    
                    
               // if($component->user_vars["src"]["settings"]["enable_model"] && check_function("update_vgallery_models"))
               //     update_vgallery_models($action, $ID_vgallery, $ID_node, $vgallery_name, $actual_path, $item_name, $component->user_vars["gallery_model"], $owner);
                
                $check_rel_user = true;
                
                if($component->user_vars["src"]["settings"]["enable_email_notify_on_insert"]) {
                    $fields["general"]["ID"] = $ID_node;
                    $fields["general"]["name"] = $item_name;
                    $fields["general"]["parent"] = $actual_path;
                    $fields["general"]["owner"] = get_session("UserID");
                    
                    if($component->user_vars["src"]["settings"]["email_notify_show_detail"]) {
                        if(is_array($component->detail["VGalleryNodesModifyDetail"]->form_fields) && count($component->detail["VGalleryNodesModifyDetail"]->form_fields)) {
                            foreach($component->detail["VGalleryNodesModifyDetail"]->form_fields AS $field_key => $field_value) {
                                if(is_array($field_value->value)) {
                                    $real_data = $field_value->value["search"];
                                } else {
                                    if(($field_value->user_vars["extended_type"] == "Image"
                                        || $field_value->user_vars["extended_type"] == "Upload"
                                        || $field_value->user_vars["extended_type"] == "UploadImage"
                                        ) && file_exists(DISK_UPDIR . $field_value->getValue())
                                    ) {
                                        $real_data = '<img src="' . cm_showfiles_get_abs_url("/100x100" . $field_value->getValue()) . '" />';
                                    } elseif($field_value->user_vars["extended_type"] == "Link") {
                                        if(check_function("transmute_inlink"))
                                            $real_data = transmute_inlink($field_value->getValue());
                                    } else {
                                        $real_data = $field_value->getValue();
                                    }
                                }
                                
                                if(strlen($field_value->group)) {
                                    $fields[$field_value->group][$field_value->label] = $real_data;
                                } else {
                                    $fields["general"][$field_value->label] = $real_data;
                                }
                            }
                        }
                    }
                    if(check_function("process_mail")) {
                        $rc = process_mail(email_system("Notify Insert VGallery " . $component->user_vars["src"]["settings"]["name"]), "", NULL, NULL, $fields, null, null, null, false, false, true);
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_ID") . '</label>' . $ID_node . '</div>';
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_name") . '</label>' . $item_name . '</div>';
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_parent") . '</label>' . $actual_path . '</div>';
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_owner") . '</label>' . get_session("UserID") . '</div>';

                        $strGroupField = '<div class="description">' . $strField . '</div>';
                        if($rc) {
                            $strGroupField .= '<div class="mail-status">' . $rc . '</div>';    
                        } else {
                            $strGroupField .= '<div class="mail-status">' . ffTemplate::_get_word_by_code("mail_send_successfully") . '</div>';    
                        }
                    }
                    if(check_function("write_notification"))
                        write_notification("_notify_insert_" . $component->user_vars["src"]["type"] . "_" . $component->user_vars["src"]["settings"]["name"], $strGroupField, "information", "restricted", FF_SITE_PATH . VG_SITE_RESTRICTED . "/" . $component->user_vars["src"]["type"] . stripslash("/" . ffCommon_url_rewrite($component->user_vars["src"]["settings"]["name"])));
                }
                break;
            case "update":
                if(check_function("update_vgallery")) {
                    $smart_url_default = update_vgallery($component, $action);

                    if(!strlen($smart_url_default)) {
                         $sSQL = "DELETE FROM 
                                `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
                            WHERE `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_node, "Number");
                        $db->execute($sSQL);

                         $sSQL = "DELETE FROM 
                                `" . $component->user_vars["src"]["table"] . "_rel_languages` 
                            WHERE `" . $component->user_vars["src"]["table"] . "_rel_languages`.`ID_nodes` = " . $db->toSql($ID_node, "Number");
                        $db->execute($sSQL);
						
                         $sSQL = "DELETE FROM 
                                `" . $component->user_vars["src"]["table"] . "`  
                            WHERE `" . $component->user_vars["src"]["table"] . "`.`ID` = " . $db->toSql($ID_node, "Number");
                        $db->execute($sSQL);
                    }
                }

                if(!strlen($item_name)) {
                    $component->tplDisplayError(ffTemplate::_get_word_by_code("name_empty"));
                    return true;
                }
/*
                $db->query("SELECT * 
                            FROM `" . $component->user_vars["src"]["table"] . "`  
                            WHERE `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` = " . $db->toSql($actual_path) . "
                                AND `" . $component->user_vars["src"]["table"] . "`.`" . $component->user_vars["src"]["field"]["name"] . "` = " . $db->toSql($item_name) . "
                                AND `" . $component->user_vars["src"]["table"] . "`.ID <> " . $db->toSql($component->key_fields["ID"]->value)
                        );
                if($db->nextRecord()) {
                    $not_unic = true;
                } else {
                    $not_unic = false;
                }

                if($not_unic || (AREA_VGALLERY_ADD_ID_IN_REALNAME && strpos($item_name, "-" . $ID_node) === false && !$is_dir)) {
                    $item_name = $item_name . "-" . $ID_node;
                    $db->execute("UPDATE `" . $component->user_vars["src"]["table"] . "` 
                                SET `" . $component->user_vars["src"]["table"] . "`.`" . $component->user_vars["src"]["field"]["name"] . "` = " . $db->toSql($item_name) . "
                                WHERE
                                    `" . $component->user_vars["src"]["table"] . "`.ID = " . $db->toSql($ID_node, "Number")
                            );
                } 
*/                
                $old_parent = stripslash($actual_old_path) . "/" . $item_name_old;
                $new_parent = stripslash($actual_path) . "/" . $smart_url_default;

                $arrTableUpdate = array();
                $arrOldPath = array();
               // $arrFileOldNew = array();
				if(check_function("get_schema_fields_by_type"))
					$src_media = get_schema_fields_by_type("media");	                

	            if(is_array($component->form_fields) && count($component->form_fields)) {
	                foreach($component->form_fields AS $field_key => $field_value) {
	                    if(strpos($field_value->id, "field_") === 0   
	                        && ($field_value->user_vars["extended_type"] == "Image" 
	                            || $field_value->user_vars["extended_type"] == "Upload" 
	                            || $field_value->user_vars["extended_type"] == "UploadImage"
	                        ) 
	                        /*&& strlen($field_value->user_vars["data_source"])*/
	                    ) {
	                        if(strlen($field_value->getValue())) {
	                            $arrFile = explode(",", $field_value->getValue());
	                            if(is_array($arrFile) && count($arrFile)) {
	                                //$arrOldPath = array();
	                                foreach($arrFile AS $file_path) {
										if(strlen($file_path)) {
				                            /*if(is_file(DISK_UPDIR . $new_parent . "/" . basename($file_path))) {   
				                                $arrFileOldNew[] = $new_parent . "/" . basename($file_path);
				                            } elseif(is_file(DISK_UPDIR . $new_parent . $field_value->user_vars["gallery_sub_dir"] . "/" . basename($file_path))) {
				                                $arrFileOldNew[] = $new_parent . $field_value->user_vars["gallery_sub_dir"] . "/" . basename($file_path);    
				                            } else
				                            */
				                            
				                            if(is_file(DISK_UPDIR . $file_path)) {
				                                if($field_value->user_vars["gallery_sub_dir"] && is_dir(DISK_UPDIR . ffCommon_dirname(ffCommon_dirname($file_path)) . $field_value->user_vars["gallery_sub_dir"]))
				                                	$old_path = ffCommon_dirname(ffCommon_dirname($file_path));
				                                else
				                                	$old_path = ffCommon_dirname($file_path);

												if($old_path != $new_parent)
				                                	$arrOldPath[$file_path] = $new_parent . $field_value->user_vars["gallery_sub_dir"] . "/" . basename($file_path);
				                            }
										}
	                                }
	                            }

	                            
							} 
	                    }
		                //$field_value->setValue(implode(",", array_filter($arrFileNew)));
	                }
	            }         

 				if(is_array($arrOldPath) && count($arrOldPath) && check_function("fs_operation")) {
	                foreach($arrOldPath AS $old_file => $new_file) {
	                    full_copy(DISK_UPDIR . str_replace("//", "/", $old_file), DISK_UPDIR . $new_file, true);
	                    //echo str_replace("//", "/", $old_file) . " ==> " . $new_file . "<br>";
	                    
						$arrTableUpdate[$component->user_vars["src"]["type"] . "_rel_nodes_fields" . $old_file] = "
										UPDATE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields 
			                            	SET " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description = REPLACE(" . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description, " . $db->toSql($old_file)  . ", " . $db->toSql($new_file) . ")
			                            WHERE
			                                " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.ID_nodes = " . $db->toSql($ID_node, "Number")  . "
			                                AND " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description NOT LIKE '" . $db->toSql($new_file, "Text", false) . "%/%'";

						if(strpos($new_file, "-" . $ID_node) !== false) {
							$arrTableUpdate[$component->user_vars["src"]["type"] . "_rel_nodes_fields" . $old_file . "-duplicate"] = "
										UPDATE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields 
			                            	SET " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description = REPLACE(" . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description, " . $db->toSql($new_file . "-" . $ID_node) . ", " . $db->toSql($new_file) . ")
				                        WHERE
				                            " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.ID_nodes = " . $db->toSql($ID_node, "Number")  . "
				                            AND " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description LIKE '%" . $db->toSql($new_file . "-" . $ID_node, "Text", false) . "%'";  
						}		                    
		                if($src_media["table"]) {
							$arrTableUpdate[$src_media["table"] . $old_file] = "
								UPDATE `" . $src_media["table"] . "` 
									SET `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` = REPLACE(`" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "`, " . $db->toSql($old_file)  . ", " . $db->toSql($new_file) . ")
								WHERE
									(`" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` = " . $db->toSql($old_file)  . " 
										OR `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` LIKE '" . $db->toSql($old_file, "Text", false)  . "/%'
									)
									AND `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` NOT LIKE '" . $db->toSql($new_file, "Text", false) . "%/%'
									AND `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` = " . $db->toSql($new_file);

							if(strpos($new_file, "-" . $ID_node) !== false) {
								$arrTableUpdate[$src_media["table"] . $old_file . "-duplicate"] = "
									UPDATE `" . $src_media["table"] . "` 
										SET `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` = REPLACE(`" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "`, " . $db->toSql($new_file . "-" . $ID_node) . ", " . $db->toSql($new_file) . ")
									WHERE `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` LIKE '" . $db->toSql($new_file . "-" . $ID_node, "Text", false) . "%'";  
							}
						}
	                }
	            }	            
	            
                if($old_parent != $new_parent) {
                    if(is_dir(DISK_UPDIR . $old_parent) && check_function("fs_operation")) {
                        full_copy(DISK_UPDIR . $old_parent, DISK_UPDIR . $new_parent, true);
					}
					
 					$arrTableUpdate["layout"] = "UPDATE layout 
						                            SET layout.params = " . $db->toSql(substr($new_parent, strlen("/" . $component->user_vars["src"]["settings"]["name"]))) . "
						                            WHERE layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = 'VIRTUAL_GALLERY')
						                                AND layout.value = " . $db->toSql($component->user_vars["src"]["settings"]["name"]) . "
						                                AND layout.params = " . $db->toSql(strpos($old_parent, "/" . $component->user_vars["src"]["settings"]["name"]) === 0
                                    														? substr($old_parent, strlen("/" . $component->user_vars["src"]["settings"]["name"]))
                                    														: $old_parent
                                    													);

					$arrTableUpdate[$component->user_vars["src"]["table"] . $old_parent] = "
									UPDATE `" . $component->user_vars["src"]["table"] . "` 
		                            	SET `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` = REPLACE(`" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "`, " . $db->toSql($old_parent)  . ", " . $db->toSql($new_parent) . ")
		                            WHERE
		                            (`" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` = " . $db->toSql($old_parent)  . " 
		                                OR `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` LIKE '" . $db->toSql($old_parent, "Text", false)  . "/%'
		                            )
		                            AND `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` NOT LIKE '" . $db->toSql($new_parent, "Text", false) . "%/%'
		                            AND `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` <> " . $db->toSql($new_parent);

	     			if(strpos($new_parent, "-" . $ID_node) !== false) {
						$arrTableUpdate[$component->user_vars["src"]["table"] . $old_parent . "-duplicate"] = "
									UPDATE `" . $component->user_vars["src"]["table"] . "` 
		                            	SET `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` = REPLACE(`" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "`, " . $db->toSql($new_parent . "-" . $ID_node) . ", " . $db->toSql($new_parent) . ")
			                        WHERE `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` LIKE '" . $db->toSql($new_parent . "-" . $ID_node, "Text", false) . "%'";
					}
	     
	                $arrTableUpdate[$component->user_vars["src"]["type"] . "_rel_nodes_fields" . $old_parent] = "
	                				UPDATE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields 
		                            	SET " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description = REPLACE(" . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description, " . $db->toSql($old_parent)  . ", " . $db->toSql($new_parent) . ")
		                            WHERE
		                                " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.ID_nodes = " . $db->toSql($ID_node, "Number") . "
		                                AND " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description NOT LIKE '" . $db->toSql($new_parent, "Text", false) . "%/%'";
	                    
					if(strpos($new_parent, "-" . $ID_node) !== false) {
	                	$arrTableUpdate[$component->user_vars["src"]["type"] . "_rel_nodes_fields" . $old_parent . "-duplicate"] = "
	                				UPDATE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields 
		                            	SET " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description = REPLACE(" . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description, " . $db->toSql($new_parent . "-" . $ID_node) . ", " . $db->toSql($new_parent) . ")
		                            WHERE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.description LIKE '%" . $db->toSql($new_parent . "-" . $ID_node, "Text", false) . "%'";
					}
					
	                if($src_media["table"]) {
						$arrTableUpdate[$src_media["table"] . $old_parent] = "
							UPDATE `" . $src_media["table"] . "` 
								SET `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` = REPLACE(`" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "`, " . $db->toSql($old_parent)  . ", " . $db->toSql($new_parent) . ")
							WHERE
								(`" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` = " . $db->toSql($old_parent)  . " 
									OR `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` LIKE '" . $db->toSql($old_parent, "Text", false)  . "/%'
								)
								AND `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` NOT LIKE '" . $db->toSql($new_parent, "Text", false) . "%/%'
								AND `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` <> " . $db->toSql($new_parent);

						if(strpos($new_parent, "-" . $ID_node) !== false) {
							$arrTableUpdate[$src_media["table"] . $old_parent . "-duplicate"] = "
								UPDATE `" . $src_media["table"] . "` 
									SET `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` = REPLACE(`" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "`, " . $db->toSql($new_parent . "-" . $ID_node) . ", " . $db->toSql($new_parent) . ")
								WHERE `" . $src_media["table"] . "`.`" .  $src_media["field"]["parent"] . "` LIKE '" . $db->toSql($new_parent . "-" . $ID_node, "Text", false) . "%'";
						}
					}

					/*if($src_media["seo"]["table"]) {   //$field_value non ce qui
						$arrTableUpdate[$field_value->user_vars["gallery_sub_dir"] . "-" . $src_media["seo"]["table"]] = "
							UPDATE " . $src_media["seo"]["table"] . " 
								SET " . $src_media["seo"]["table"] . ".`" . $src_media["seo"]["permalink"] . "` = REPLACE(" . $src_media["seo"]["table"] . ".`" . $src_media["seo"]["permalink"] . "`, " . $db->toSql($old_parent . $field_value->user_vars["gallery_sub_dir"])  . ", " . $db->toSql($new_parent . $field_value->user_vars["gallery_sub_dir"]) . ")
									, " . $src_media["seo"]["table"] . ".`" . $src_media["seo"]["permalink_parent"] . "` = REPLACE(" . $src_media["seo"]["table"] . "." . $src_media["seo"]["permalink"] . ", " . $db->toSql($old_parent . $field_value->user_vars["gallery_sub_dir"])  . ", " . $db->toSql($new_parent . $field_value->user_vars["gallery_sub_dir"]) . ")
							WHERE
								`" . $src_media["seo"]["table"] . "`.`" .  $src_media["seo"]["permalink"] . "` LIKE '" . $db->toSql($old_parent, "Text", false)  . "/%'
							";
					}*/							                            
				}

				if(is_array($arrTableUpdate) && count($arrTableUpdate)) {
					foreach($arrTableUpdate AS $gallery_key => $sSQL_update) {
					    $db->execute($sSQL_update);
					}
				}

              //  if($component->user_vars["src"]["settings"]["enable_model"] && check_function("update_vgallery_models"))
              //      update_vgallery_models($action, $ID_vgallery, $ID_node, $component->user_vars["src"]["settings"]["name"], $actual_path, $item_name, $component->user_vars["gallery_model"]);
                
                $check_rel_user = true;
                
                if($component->user_vars["src"]["settings"]["enable_email_notify_on_update"]) {
                    $fields["general"]["ID"] = $ID_node;
                    $fields["general"]["name"] = $item_name;
                    $fields["general"]["parent"] = $actual_path;
                    $fields["general"]["owner"] = get_session("UserID");

                    if($component->user_vars["src"]["settings"]["email_notify_show_detail"]) {
                        if(is_array($component->detail["VGalleryNodesModifyDetail"]->form_fields) && count($component->detail["VGalleryNodesModifyDetail"]->form_fields)) {
                            foreach($component->detail["VGalleryNodesModifyDetail"]->form_fields AS $field_key => $field_value) {
                                if(is_array($field_value->value)) {
                                    $real_data = $field_value->value["search"];
                                } else {
                                    if(($field_value->user_vars["extended_type"] == "Image"
                                        || $field_value->user_vars["extended_type"] == "Upload"
                                        || $field_value->user_vars["extended_type"] == "UploadImage"
                                        ) && file_exists(DISK_UPDIR . $field_value->getValue())
                                    ) {
                                        $real_data = '<img src="' . cm_showfiles_get_abs_url("/thumb" . $field_value->getValue()) . '" />';
                                    } elseif($field_value->user_vars["extended_type"] == "Link") {
                                        if(check_function("transmute_inlink"))
                                            $real_data = transmute_inlink($field_value->getValue());
                                    } else {
                                        $real_data = $field_value->getValue();
                                    }
                                }

                                if(strlen($field_value->group)) {
                                    $fields[$field_value->group][$field_value->label] = $real_data;
                                } else {
                                    $fields["general"][$field_value->label] = $real_data;
                                }
                            }
                        }
                    }
                    if(check_function("process_mail")) {
                        $rc = process_mail(email_system("Notify Update VGallery " . $component->user_vars["src"]["settings"]["name"]), "", NULL, NULL, $fields, null, null, null, false, false, true);
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_ID") . '</label>' . $ID_node . '</div>';
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_name") . '</label>' . $item_name . '</div>';
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_parent") . '</label>' . $actual_path . '</div>';
                        $strField .= '<div class="row"><label>' . ffTemplate::_get_word_by_code("notify_" . preg_replace('/[^a-zA-Z0-9]/', '', $component->user_vars["src"]["settings"]["name"]) . "_owner") . '</label>' . get_session("UserID") . '</div>';

                        $strGroupField = '<div class="description">' . $strField . '</div>';
                        if($rc) {
                            $strGroupField .= '<div class="mail-status">' . $rc . '</div>';    
                        } else {
                            $strGroupField .= '<div class="mail-status">' . ffTemplate::_get_word_by_code("mail_send_successfully") . '</div>';    
                        }
                    }
                    if(check_function("write_notification"))
                        write_notification("_notify_update_" . $component->user_vars["src"]["type"] . "_" . $component->user_vars["src"]["settings"]["name"], $strGroupField, "information", "restricted", FF_SITE_PATH . VG_SITE_RESTRICTED . "/" . $component->user_vars["src"]["type"] . stripslash("/" . ffCommon_url_rewrite($component->user_vars["src"]["settings"]["name"])));
                }
                break;
            case "delete":
                break;
            case "confirmdelete":
                $old_parent = stripslash($actual_old_path) . "/" . $item_name_old;

                //remove cache of relationship
                if($ID_node > 0) {
                    $sSQL = "UPDATE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields SET 
                                `nodes` = ''
                            WHERE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`nodes` <> ''
                                AND FIND_IN_SET(" . $db->toSql($ID_node, "Number") . ", " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`nodes`)";
                    $db->execute($sSQL);
                }
                break;
        }

		if(($action == "insert" || $action == "update") && check_function("set_field_permalink"))
			$arrPermalink = set_field_permalink(
				$component->user_vars["src"]["table"]
				, $component->key_fields["ID"]->getValue()
				, $component->user_vars["src"]["settings"]["enable_multi_cat"] && !$is_dir
				, false
				, $component->user_vars["src"]["settings"]["permalink_rule"]
			);

       if(check_function("check_user_request"))
           $request_vgallery = check_user_vgallery_request(array("name" => get_session("UserID"), "ID" => get_session("UserNID")), null, null, null, "rel");
        
        if($check_rel_user && is_array($request_vgallery) && count($request_vgallery)) {
            foreach($request_vgallery AS $request_vgallery_key => $request_vgallery_value) {
                if(strlen($request_vgallery_value["vgallery"]) && $request_vgallery_value["ID"] > 0) {
                    $sSQL = "SELECT * 
                            FROM rel_nodes
                            WHERE 
                                `ID_node_src` =  " . $db->toSql($ID_node, "Number") . "
                                AND `contest_src` = " . $db->toSql($component->user_vars["src"]["settings"]["name"], "Text") . "
                                AND `ID_node_dst` = " . $db->toSql($request_vgallery_value["ID"], "Number") . " 
                                AND `contest_dst` = " . $db->toSql($request_vgallery_value["vgallery"], "Text") . "
                            ";
                    $db->query($sSQL);
                    if(!$db->numRows()) {
                        $sSQL = "INSERT INTO 
                                    rel_nodes
                                    (
                                    ID, 
                                    `ID_node_src`, 
                                    `contest_src`, 
                                    `ID_node_dst`, 
                                    `contest_dst`,
                                    `cascading`
                                    )
                                    VALUES
                                    (
                                    '', 
                                        " . $db->toSql($ID_node, "Number") . ", 
                                        " . $db->toSql($component->user_vars["src"]["settings"]["name"], "Text") . ",
                                        " . $db->toSql($request_vgallery_value["ID"], "Number") . ", 
                                        " . $db->toSql($request_vgallery_value["vgallery"], "Text") . ",
                                        " . $db->toSql($is_dir, "Number") . "
                                    )
                        ";
                        $db->execute($sSQL);
                    }
                }
            }
        }
		
		
		$ID_vgallery = $component->user_vars["ID_vgallery"];
		if(check_function("refresh_cache")) {
			if(is_array($arrPermalink) && count($arrPermalink)) {
                refresh_cache("V", $ID_node, $action, $arrPermalink);
			} else {
				refresh_cache(
                    "V"
                    , $ID_node
                    , $action
                    , $component->user_vars["permalink"]
                );
			}
		}

		//remove cache of relationship
		if($ID_node > 0) {
			$sSQL = "UPDATE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields SET 
		            	`nodes` = ''
					WHERE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`nodes` <> ''
		            	AND FIND_IN_SET(" . $db->toSql($ID_node, "Number") . ", " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`nodes`)";
			$db->execute($sSQL);
		}

		if($component->user_vars["src"]["field"]["lang"] && !array_key_exists(LANGUAGE_DEFAULT, $component->user_vars["lang"])) {
		     $sSQL = "DELETE FROM 
		            `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
		        WHERE `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields`.`ID_nodes` = " . $db->toSql($ID_node, "Number");
		    $db->execute($sSQL);

             $sSQL = "DELETE FROM 
                    `" . $component->user_vars["src"]["table"] . "_rel_languages` 
                WHERE `" . $component->user_vars["src"]["table"] . "_rel_languages`.`ID_nodes` = " . $db->toSql($ID_node, "Number");
            $db->execute($sSQL);

		     $sSQL = "DELETE FROM 
		            `" . $component->user_vars["src"]["table"] . "` 
		        WHERE `" . $component->user_vars["src"]["table"] . "` .`ID` = " . $db->toSql($ID_node, "Number");
		    $db->execute($sSQL);

		    $component->tplDisplayError(ffTemplate::_get_word_by_code("invalid_language_default"));
		    return true;
		}
    }
    return false;
}