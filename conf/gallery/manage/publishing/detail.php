<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_PUBLISHING_SHOW_DETAIL")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$ID_publishing = $_REQUEST["keys"]["ID"];

$ID_node = $_REQUEST["ID_node"];
//$contest = $_REQUEST["contest"];
$frmAction = $_REQUEST["frmAction"];
$ret_url = $_REQUEST["ret_url"];

if(isset($ID_publishing))
{
    $publishing_title = ffTemplate::_get_word_by_code("publishing_additem");
    $sSQL = "SELECT publishing.*
                    , IF(publishing.display_name = ''
                        , REPLACE(publishing.name, '-', ' ')
                        , publishing.display_name
                    ) AS display_name 
                    , (SELECT COUNT(DISTINCT rel_nodes.ID) 
                        FROM rel_nodes
                        WHERE 
                            (
                                (
                                    rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_publishing,  "Number") . "
                                    AND rel_nodes.contest_dst = " . $db_gallery->toSql("publishing", "Text") . "
                                ) 
                            OR 
                                (
                                    rel_nodes.ID_node_src = " . $db_gallery->toSql($ID_publishing,  "Number") . "
                                    AND rel_nodes.contest_src = " . $db_gallery->toSql("publishing", "Text") . "
                                )
                            )
                    ) AS count_publish       
                FROM publishing
                WHERE publishing.ID = " . $db_gallery->toSql($ID_publishing, "Number");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord())
    {
        $publishing_title .= ": " . ucwords($db_gallery->getField("display_name", "Text", true)) . "(" . $db_gallery->getField("count_publish", "Number", true) . "/"  . $db_gallery->getField("limit", "Number", true) . ")";
        $publishing_area = $db_gallery->getField("area", "Text", true);
        $publishing_contest = $db_gallery->getField("contest", "Text", true);
        $publishing_relative_path = $db_gallery->getField("relative_path", "Text", true);
        switch($publishing_area) {
            case "anagraph":
                $src_type = "anagraph";
                break;
            case "gallery":
                $src_type = "gallery";
                break;
            default:
                $src_type = "vgallery";
        }
        
        switch($src_type) {
            case "anagraph":
                $src_table =  "anagraph";
                break;
            case "gallery":
                $src_table =  "files";
                break;
            case "vgallery":
                $src_table =  "vgallery_nodes";
                break;
            default:
                $src_table = $src_type;
        }        
    }
}
switch($frmAction) {
    case"delrel":
    case"mydelete":
        if(!$ID_node > 0) {
            $ID_node = $_REQUEST["DetailModify_recordset"][$_REQUEST["row"]]["ID_node_src"];
        }

        $sSQL = "SELECT IF(rel_nodes.contest_src = 'publishing', rel_nodes.contest_dst, rel_nodes.contest_src) AS rel_type FROM rel_nodes
                    WHERE 
                        (
                            (
                                rel_nodes.ID_node_src = " . $db_gallery->toSql($ID_node, "Number") . "
                                AND rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_publishing,  "Number") . "
                                AND rel_nodes.contest_dst = " . $db_gallery->toSql("publishing", "Text") . "
                            ) 
                        OR 
                            (
                                rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_node, "Number") . "
                                AND rel_nodes.ID_node_src = " . $db_gallery->toSql($ID_publishing,  "Number") . "
                                AND rel_nodes.contest_src = " . $db_gallery->toSql("publishing", "Text") . "
                            )
                        )";            
        $db_gallery->query($sSQL); 
        if($db_gallery->nextRecord()) {
            $rel_type = $db_gallery->getField("rel_type", "Text", true);
        }

        if(check_function("refresh_cache")) {
        	refresh_cache_get_blocks_by_layout($publishing_area . "_" . $ID_publishing);
		}
        $sSQL = "DELETE FROM rel_nodes
                WHERE 
                    rel_nodes.ID_node_src = " . $db_gallery->toSql($ID_node, "Number") . "
                    AND rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_publishing,  "Number") . "
                    AND rel_nodes.contest_dst = " . $db_gallery->toSql("publishing", "Text");
        $db_gallery->execute($sSQL);
        if(!$db_gallery->affectedRows()) {
            $sSQL = "DELETE FROM rel_nodes
                    WHERE 
                        rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_node, "Number") . "
                        AND rel_nodes.ID_node_src = " . $db_gallery->toSql($ID_publishing,  "Number") . "
                        AND rel_nodes.contest_src = " . $db_gallery->toSql("publishing", "Text");
            $db_gallery->execute($sSQL);
        }

        if($_REQUEST["XHR_DIALOG_ID"]) {
            //die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("DetailModify")), true));
        } else {
        	
        	cm::jsonParse(array(
        		"close" => false
        		, "refresh" => true
        		, "resources" => array(
        			"DetailModify"
        		)
        	));
        	exit;

        	//die(ffCommon_jsonenc(array("url" => $_SERVER["REQUEST_URI"]))); //da togliere il doredirect e fare la chiamata ajax effettiva
            //ffRedirect($_SERVER["REQUEST_URI"]);            
        }
        break;
    case "addrel":
    	$db = ffDB_Sql::factory();
    	
    	$node = null;
    	
        switch($src_type) {
            case "gallery":
                $rel_nodes = $_REQUEST["DetailModify_relfiles"];
                if(strlen($rel_nodes)) {
                    if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                        $sSQL_Where = " files.ID = " . $db_gallery->tosql($rel_nodes, "Number");
                    } else {
                        $sSQL_Where = " files.ID IN(" . $db_gallery->tosql($rel_nodes, "Text", false) . ")";
                    }

                    //$rel_nodes_start = $_REQUEST["DetailModify_relfiles_start"];
                    //$rel_nodes_end = $_REQUEST["DetailModify_relfiles_end"];
                    $rel_type = "files";

                    $sSQL = "SELECT files.ID
                                , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS display_path
                            FROM files 
                            WHERE $sSQL_Where";
                    $db_gallery->query($sSQL);
                    if($db_gallery->nextRecord()) {
                        do {
                            $node[$db_gallery->getField("ID", "Number", true)]["node"] = $db_gallery->getField("display_path", "Text", true);
                            $node[$db_gallery->getField("ID", "Number", true)]["type"] = $rel_type;
                        } while($db_gallery->nextRecord());
                    }
                }
                break;
            case "vgallery":
                $db_gallery->query("SELECT vgallery.* FROM vgallery");
                if($db_gallery->nextRecord()) {
                    do {
                        if(isset($_REQUEST["DetailModify_rel" . $db_gallery->getField("name")->getValue()])) {
                            $rel_nodes = $_REQUEST["DetailModify_rel" . $db_gallery->getField("name")->getValue()];
                            if(strlen($rel_nodes)) {
                                if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                                    $sSQL_Where = " vgallery_nodes.ID = " . $db->tosql($rel_nodes, "Number");
                                } else {
                                    $sSQL_Where = " vgallery_nodes.ID IN(" . $db->tosql($rel_nodes, "Text", false) . ")";
                                }

                                //$rel_nodes_start = $_REQUEST["DetailModify_rel" . $db_gallery->getField("name")->getValue() . "_start"];
                                //$rel_nodes_end = $_REQUEST["DetailModify_rel" . $db_gallery->getField("name")->getValue() . "_end"];
                                $rel_type = $db_gallery->getField("name", "Text", true);

                                $sSQL = "SELECT vgallery_nodes.ID
                                            , vgallery_nodes.name 
                                            , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS display_path
                                            , IF(vgallery_nodes.is_dir > 0
    											, CONCAT(
						                            REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
						                            , IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
						                        )
							                    , CONCAT(
													IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
													, IF(vgallery_nodes.isbn
														, ' ISBN: '
														, ''
													)
						                            , vgallery_nodes.isbn
							                        , REPLACE(CONCAT(' (', vgallery_nodes.parent, ') '), '-', ' ')
							                    )
	                                        ) AS display_real_name
                                        FROM vgallery_nodes 
                                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                        WHERE $sSQL_Where ";
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    do {
                                        $node[$db->getField("ID", "Number", true)]["node"] = (strlen($db->getField("display_real_name", "Text", true)) ? $db->getField("display_real_name", "Text", true) : $db->getField("display_path", "Text", true));
                                        $node[$db->getField("ID", "Number", true)]["type"] = $rel_type;
                                    } while($db->nextRecord());
                                }
                                break;
                            }
                        }
                    } while($db_gallery->nextRecord());
                }

                if($node === null && isset($_REQUEST["DetailModify_relall"])) {
                    $rel_nodes = $_REQUEST["DetailModify_relall"];
                    if(strlen($rel_nodes)) {
                        if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                            $sSQL_Where = " vgallery_nodes.ID = " . $db_gallery->tosql($rel_nodes, "Number");
                        } else {
                            $sSQL_Where = " vgallery_nodes.ID IN(" . $db_gallery->tosql($rel_nodes, "Text", false) . ")";
                        }

                        //$rel_nodes_start = $_REQUEST["DetailModify_relall_start"];
                        //$rel_nodes_end = $_REQUEST["DetailModify_relall_end"];
                        
                        $sSQL = "SELECT vgallery_nodes.ID AS ID
                                    , vgallery.name AS vgallery_name
                                    , vgallery_nodes.name AS node 
                                    , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS display_path
                                    , IF(vgallery_nodes.is_dir > 0
    									, CONCAT(
							                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
							                , IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
							            )
								        , CONCAT(
											IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
											, IF(vgallery_nodes.isbn
												, ' ISBN: '
												, ''
											)
							                , vgallery_nodes.isbn
								            , REPLACE(CONCAT(' (', vgallery_nodes.parent, ') '), '-', ' ')
								        )
		                            ) AS display_real_name
                                FROM vgallery_nodes 
                                    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery 
                                WHERE $sSQL_Where ";
                        $db_gallery->query($sSQL);
                        if($db_gallery->nextRecord()) {
                            do {
                                $node[$db_gallery->getField("ID", "Number", true)]["node"] = (strlen($db_gallery->getField("display_real_name", "Text", true)) ? $db_gallery->getField("display_real_name", "Text", true) : $db_gallery->getField("display_path", "Text", true));
                                $node[$db_gallery->getField("ID", "Number", true)]["type"] = $db_gallery->getField("vgallery_name", "Text", true);
                            } while($db_gallery->nextRecord());
                        }
                    }
                }            
                break;
            case "anagraph":
                $rel_nodes = $_REQUEST["DetailModify_relanagraph"];
                if(strlen($rel_nodes)) {
                    if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                        $sSQL_Where = " anagraph.ID = " . $db_gallery->tosql($rel_nodes, "Number");
                    } else {
                        $sSQL_Where = " anagraph.ID IN(" . $db_gallery->tosql($rel_nodes, "Text", false) . ")";
                    }

                    //$rel_nodes_start = $_REQUEST["DetailModify_relfiles_start"];
                    //$rel_nodes_end = $_REQUEST["DetailModify_relfiles_end"];
                    $rel_type = "anagraph";

                    $sSQL = "SELECT anagraph.ID
                                , CONCAT(
                                    IF(anagraph.avatar = ''
                                        , '" . Cms::getInstance("frameworkcss")->get("noimg", "icon-tag", "2x") . " ' 
                                        , CONCAT('<img src=\"" . CM_SHOWFILES . "/32x32', anagraph.avatar, '\" />')  
                                    ) 
                                    , anagraph.name
                                    , ' '
                                    , anagraph.surname
                                ) AS display_path
                            FROM anagraph 
                            WHERE $sSQL_Where";
                    $db_gallery->query($sSQL);
                    if($db_gallery->nextRecord()) {
                        do {
                            $node[$db_gallery->getField("ID", "Number", true)]["node"] = $db_gallery->getField("display_path", "Text", true);
                            $node[$db_gallery->getField("ID", "Number", true)]["type"] = $rel_type;
                        } while($db_gallery->nextRecord());
                    }
                }            
                break;
            default:
                $rel_nodes = $_REQUEST["DetailModify_relanagraph"];
                if(strlen($rel_nodes)) {
                    if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                        $sSQL_Where = " " . $src_type . ".ID = " . $db_gallery->tosql($rel_nodes, "Number");
                    } else {
                        $sSQL_Where = " " . $src_type . ".ID IN(" . $db_gallery->tosql($rel_nodes, "Text", false) . ")";
                    }

                    //$rel_nodes_start = $_REQUEST["DetailModify_relfiles_start"];
                    //$rel_nodes_end = $_REQUEST["DetailModify_relfiles_end"];
                    $rel_type = $src_type;

                    $sSQL = "SELECT " . $src_type . ".ID
                                , " . $src_type . ".name AS display_path
                            FROM " . $src_type . " 
                            WHERE $sSQL_Where";
                    $db_gallery->query($sSQL);
                    if($db_gallery->nextRecord()) {
                        do {
                            $node[$db_gallery->getField("ID", "Number", true)]["node"] = $db_gallery->getField("display_path", "Text", true);
                            $node[$db_gallery->getField("ID", "Number", true)]["type"] = $rel_type;
                        } while($db_gallery->nextRecord());
                    }
                }            
        }
        
        
        

        
        if(is_array($node) && count($node)) {
        	foreach($node AS $node_key => $node_value) {
        		$is_valid = true;
        		
        		$rel_nodes = $node_key;
        		$rel_type = $node_value["type"];
        		$node_name = $node_value["node"];
	            
	            if(is_array($_REQUEST["DetailModify_recordset"]) && count($_REQUEST["DetailModify_recordset"])) {
	                foreach($_REQUEST["DetailModify_recordset"] AS $check_key => $check_value) {
	                    if($check_value["ID_node_src"] == $rel_nodes 
	                        && $check_value["contest_src"] == $rel_type
	                        && $check_value["nodes"] == $node_name
	                    ) {
	                        $is_valid = false;
	                        break;
	                    }
	                }
	            }
	            if($is_valid) {
	                $_REQUEST["DetailModify_rows"] = $_REQUEST["DetailModify_rows"] + 1;
	                $_REQUEST["DetailModify_recordset_ori"][]= array("ID" => ''
	                                                                , "ID_node_src" => ''
	                                                                , "contest_src" => ''
	                                                                , "ID_node_dst" => ''
	                                                                , "contest_dst" => ''
	                                                                , "nodes" => ''
	                                                                , "date_begin" => ''
	                                                                , "date_end" => ''
	                                                            );    
	                $_REQUEST["DetailModify_recordset"][]= array("ID" => ''
	                                                                , "ID_node_src" => $rel_nodes
	                                                                , "contest_src" => $rel_type
	                                                                , "ID_node_dst" => $ID_publishing
	                                                                , "nodes" => $node_name
	                                                                , "contest_dst" => "publishing"
	                                                                , "date_begin" => ''
	                                                                , "date_end" => ''
	                                                            );    
	              /*  $sSQL = "INSERT INTO 
	                            rel_nodes
	                            (
		                            ID 
		                            , `ID_node_src`
		                            , `contest_src` 
		                            , `ID_node_dst` 
		                            , `contest_dst`
	                            )
	                            VALUES
	                            (
		                            '' 
		                            , " . $db_gallery->toSql($rel_nodes, "Number") . " 
		                            , " . $db_gallery->toSql($rel_type, "Text") . "
		                            , " . $db_gallery->toSql($ID_publishing, "Number") . "
		                            , " . $db_gallery->toSql("publishing", "Text") . "
	                            )
	                ";
	                $db_gallery->execute($sSQL);
	                
	                $sSQL = "UPDATE 
	                        `layout` 
	                    SET 
	                        `layout`.`last_update` = " . $db_gallery->toSql(time(), "Number") . "
	                    WHERE 
	                        (
	                            REPLACE(layout.value, " . $db_gallery->toSql("vgallery_") . ", '') = " . $db_gallery->toSql($ID_publishing, "Number") . "
	                            AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db_gallery->toSql("PUBLISHING") . ")
	                        )
	                        ";
	                $db_gallery->execute($sSQL);
	                
	                if(check_function("refresh_cache")) {
	                } 
	                */
	                	
				}
            }

    /*        $sSQL = "INSERT INTO 
                        rel_nodes
                        (
                        ID, 
                        `ID_node_src`, 
                        `contest_src`, 
                        `ID_node_dst`, 
                        `contest_dst`,
                        `date_begin`, 
                        `date_end` 

                        )
                        VALUES
                        (
                        '', 
                            " . $db_gallery->toSql($rel_nodes, "Number") . ", 
                            " . $db_gallery->toSql($rel_type, "Text") . ",
                            " . $db_gallery->toSql($ID_publishing, "Number") . ", 
                            " . $db_gallery->toSql("publishing", "Text") . ", 
                            " . $db_gallery->toSql(new ffData($rel_nodes_start, "Date", LANGUAGE_INSET)) . ", 
                            " . $db_gallery->toSql(new ffData($rel_nodes_end, "Date", LANGUAGE_INSET)) . " 
                        )
            ";
            $db_gallery->execute($sSQL);*/
            if($_REQUEST["XHR_DIALOG_ID"]) {
               // die(ffCommon_jsonenc(array("close" => false, "refresh" => false, "resources" => array("DetailModify")), true));
            } else {
                ffRedirect($_SERVER["REQUEST_URI"]);   
               /* cm::jsonParse(array(
        			"close" => false
        			, "refresh" => true
        			, "resources" => array(
        				"DetailModify"
        			)
        		));
        		exit;    */     
            }  

        }
        break;
    default:
}


// -------------------------
//          RECORD
// -------------------------

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PublishingDetail";
$oRecord->resources[] = $oRecord->id;
//$oRecord->title = ffTemplate::_get_word_by_code("publishing_detail_title");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content-adv">' . Cms::getInstance("frameworkcss")->get("vg-publishing", "icon-tag", array("2x", "content-adv")) . $publishing_title . '</h1>';

$oRecord->src_table = "publishing";
if(0 && $_REQUEST["XHR_DIALOG_ID"]) {
	$oRecord->allow_update = false; // da debaggare prima di rimettere a true
	$oRecord->buttons_options["cancel"]["display"] = false;
} else {
	$oRecord->allow_update = true; // da debaggare prima di rimettere a true
	//$oRecord->addEvent("on_done_action", "PublishingDetail_on_done_action");
}
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->allow_insert = false;
$oRecord->allow_delete = false;

$oRecord->user_vars["ID_publishing"] = $ID_publishing;
$oRecord->user_vars["area"] = $publishing_area;
$oRecord->user_vars["contest"] = $publishing_contest;
$oRecord->user_vars["relative_path"] = $publishing_relative_path;
$oRecord->user_vars["src_type"] = $src_type;
$oRecord->user_vars["src_table"] = $src_table;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
  /*
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("publishing_name");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("publishing_area");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contest";
$oField->label = ffTemplate::_get_word_by_code("publishing_contest");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit";
$oField->label = ffTemplate::_get_word_by_code("publishing_limit");
$oField->base_type = "Number";
$oField->control_type = "label";
$oRecord->addContent($oField);   */

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$publish_hide_dir = false; //da aggiungere hide dir
if($db_gallery->getField("full_selection", "Number", true) > 0) { 
    ffDialog(false, "okonly", ffTemplate::_get_word_by_code("publishing_warning"), ffTemplate::_get_word_by_code("publishing_disable_manual_selection"), "", $_REQUEST["ret_url"], $cm->oPage->site_path . $cm->oPage->page_path . "/dialog");
} 

switch($oRecord->user_vars["src_type"]) {
    case "gallery":
    //  if(check_function("check_fs"))
    //      check_fs(FF_DISK_UPDIR . $oRecord->user_vars["relative_path"], $oRecord->user_vars["relative_path"]);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "relfiles";
        $oField->label = ffTemplate::_get_word_by_code("rel_files");
        $oField->base_type = "Number";
        //$oField->widget = "activecomboex";
       /* $oField->widget = "autocomplete";
        $oField->autocomplete_readonly = true;
        $oField->autocomplete_minLength = 0;
        $oField->autocomplete_delay = 300;
        $oField->autocomplete_multi = false;
        $oField->autocomplete_cache = true; 
        $oField->autocomplete_combo = true;*/
        
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        $oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "path";
        //$oField->autocompletetoken_limit = 1;
        
        $oField->resources[] = "DetailModify";
        $oField->source_SQL = "SELECT files.ID
                                    , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS path
                               FROM files
                               WHERE files.ID NOT IN ( 
                                            SELECT files.ID
                                            FROM files
                                                INNER JOIN rel_nodes
                                                    ON 
                                                    (
                                                        rel_nodes.ID_node_src = files.ID 
                                                        AND rel_nodes.contest_src = " . $db_gallery->toSql("files", "Text") . "
                                                        AND rel_nodes.contest_dst = " . $db_gallery->toSql("publishing", "Text") . " 
                                                        AND rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_publishing, "Number") . "
                                                    )
                                            )
                                       AND files.parent LIKE '" . $db_gallery->toSql($oRecord->user_vars["relative_path"], "Text", false) . "%'
                                       " . ($publish_hide_dir
                                           ? " AND NOT(files.is_dir > 0) "
                                           : ""
                                       ) . "
                               [AND] [WHERE]
                               [HAVING] 
                               [ORDER] [COLON] files.is_dir DESC, path
                               [LIMIT]";
        $oField->actex_update_from_db = true;
        $oField->parent_page = array($cm->oPage);
        

       /* $f_publish_start = ffField::factory($cm->oPage);
        $f_publish_start->id = "relfiles_start";
        $f_publish_start->base_type = "Date";
        $f_publish_start->widget = "datepicker";
        $f_publish_start->parent_page = array($cm->oPage);


        $f_publish_end = ffField::factory($cm->oPage);
        $f_publish_end->id = "relfiles_end";
        $f_publish_end->base_type = "Date";
        $f_publish_end->widget = "datepicker";
        $f_publish_end->parent_page = array($cm->oPage);
        */
        $oAddRel = ffButton::factory($cm->oPage);
        $oAddRel->id = "addrelfiles"; 
        $oAddRel->label = ffTemplate::_get_word_by_code("add_rel");
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $oAddRel->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'addrel'});";
        } else {
            $oAddRel->action_type = "submit";
            $oAddRel->frmAction = "addrel";
        }
                $oAddRel->aspect = "link";
        //$oAddRel->jsaction = "ff.doAjax('DetailModify');";
        //$oAddRel->jsaction =  "ff.ajax.doRequest({'component' : 'DetailModify'});";
        $oAddRel->parent_page = array($cm->oPage);

        //$oRecord->fixed_post_content = $oField->process() . $f_publish_start->process() . $f_publish_end->process() . $oAddRel->process();

        $sSQL_publishing_detail = "SELECT 
                                    rel_nodes.ID AS ID
                                    , (SELECT name 
                                        FROM files 
                                        WHERE files.ID = IF(ID_node_src = [ID_FATHER] AND contest_src = 'publishing'
                                                , ID_node_dst
                                                , ID_node_src
                                            )
                                    ) AS nodes
                                    , rel_nodes.ID_node_src AS ID_node_src
                                    , rel_nodes.contest_src AS contest_src
                                    , rel_nodes.ID_node_dst AS ID_node_dst
                                    , rel_nodes.contest_dst AS contest_dst
                                    , rel_nodes.date_begin AS date_begin
                                    , rel_nodes.date_end AS date_end
                                    , rel_nodes.highlight AS highlight
                                    , rel_nodes.`order` AS `order`
                                FROM rel_nodes 
                                WHERE 
                                (
                                    ID_node_src = [ID_FATHER] 
                                    AND contest_src = 'publishing'
                                ) 
                                OR 
                                (
                                    ID_node_dst = [ID_FATHER] 
                                    AND contest_dst ='publishing'
                                ) 
                                ORDER BY rel_nodes.`order`, rel_nodes.ID";
        break;
    case "vgallery":
        if($oRecord->user_vars["contest"]) {
            $contest = $oRecord->user_vars["contest"];  
            
            /*IF(vgallery.insert_on_lastlevel > 0
                                , IF(vgallery_nodes.is_dir > 0
                                    , 0
                                    , 1
                                )
                                , 1
                            )*/
            $contest_sql = " 1
                             AND vgallery.name = " . $db_gallery->toSql($oRecord->user_vars["contest"]) . "
                             AND (vgallery_nodes.parent = " . $db_gallery->toSql("/" . $oRecord->user_vars["contest"] . stripslash($oRecord->user_vars["relative_path"]))  . " 
                                OR vgallery_nodes.parent LIKE '" . $db_gallery->toSql("/" . $oRecord->user_vars["contest"] . stripslash($oRecord->user_vars["relative_path"]), "Text", false)  . "/%'
                             )";
           // $display_fields = " , CONCAT('/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)) AS display_path";
            $display_fields = " , IF(vgallery_nodes.is_dir > 0
    											, CONCAT(
						                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
						                , IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
						            )
							        , CONCAT(
										IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
										, IF(vgallery_nodes.isbn
											, ' ISBN: '
											, ''
										)
						                , vgallery_nodes.isbn
							            , REPLACE(CONCAT(' (', vgallery_nodes.parent, ') '), '-', ' ')
							        )
	                            ) AS display_path";
        } else {
            $contest = "all";
            
            /*IF(vgallery.insert_on_lastlevel > 0
                                , IF(vgallery_nodes.is_dir > 0
                                    , 0
                                    , 1
                                )
                                , 1
                            )*/
            $contest_sql = " 1
                            AND (vgallery_nodes.parent = " . $db_gallery->toSql($oRecord->user_vars["relative_path"])  . " 
                                OR vgallery_nodes.parent LIKE '" . $db_gallery->toSql($oRecord->user_vars["relative_path"], "Text", false)  . "/%'
                            ) ";

            //$display_fields = " , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS display_path";
            $display_fields = " , IF(vgallery_nodes.is_dir > 0
    											, CONCAT(
						                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
						                , IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
						            )
							        , CONCAT(
										IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
										, IF(vgallery_nodes.isbn
											, ' ISBN: '
											, ''
										)
						                , vgallery_nodes.isbn
							            , REPLACE(CONCAT(' (', vgallery_nodes.parent, ') '), '-', ' ')
							        )
	                            ) AS display_path";
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "rel" . $contest;
        $oField->label = ffTemplate::_get_word_by_code("rel_" . $contest);
        $oField->base_type = "Number";
        //$oField->widget = "activecomboex";
        /*$oField->widget = "autocomplete";
        $oField->autocomplete_readonly = true;
        $oField->autocomplete_minLength = 1;
        $oField->autocomplete_delay = 300;
        $oField->autocomplete_multi = false;
        $oField->autocomplete_cache = true; */
        
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        $oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "display_path";
    //            $oField->autocompletetoken_limit = 1;

        $oField->resources[] = "DetailModify";
        $oField->source_SQL = "SELECT DISTINCT vgallery_nodes.ID
                                    $display_fields
                               FROM vgallery_nodes
                                    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                    
                               WHERE " . $contest_sql . "
                                    AND (" . (Cms::env("AREA_SHOW_ECOMMERCE") && Cms::env("AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK")
                                            ? "IF(vgallery.enable_ecommerce > 0
                                                , IF(vgallery.use_pricelist_as_item_thumb > 0
                                                    , IFNULL( 
                                                        , (SELECT ecommerce_pricelist.actual_qta
                                                            FROM ecommerce_settings
                                                                INNER JOIN ecommerce_pricelist ON ecommerce_settings.ID = ecommerce_pricelist.ID_ecommerce_settings
                                                            WHERE ecommerce_settings.ID_items = vgallery_nodes.ID    
                                                        )
                                                        , 1
                                                    )
                                                    , IFNULL( 
                                                        (SELECT ecommerce_settings.actual_qta
                                                            FROM ecommerce_settings
                                                            WHERE ecommerce_settings.ID_items = vgallery_nodes.ID    
                                                        )
                                                        , 1
                                                    )
                                                )
                                                , 1
                                            )"
                                            : "1"
                                    ) . ") > 0 
                                    AND vgallery_nodes.ID NOT IN ( 
                                        SELECT vgallery_nodes.ID
                                        FROM
                                            vgallery_nodes
                                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                            INNER JOIN rel_nodes
                                                ON 
                                                (
                                                    rel_nodes.ID_node_src = vgallery_nodes.ID 
                                                    AND rel_nodes.contest_dst = " . $db_gallery->toSql("publishing", "Text") . " 
                                                    AND rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_publishing, "Number") . " 
                                                )
                                            WHERE 1
                                    )
                                    " . (Cms::env("ENABLE_STD_PERMISSION")
										? ""
										: (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && Cms::env("ENABLE_ADV_PERMISSION") && !OLD_VGALLERY
											? " AND vgallery_nodes_rel_languages.visible > 0 " 
											: " AND vgallery_nodes.visible > 0 "
										)
							   		)  
                                    . ($publish_hide_dir
                                       ? " AND IF(vgallery.limit_level = (LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) - LENGTH(REPLACE(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), '/', '')))
                                            , 1
                                            , NOT(vgallery_nodes.is_dir > 0) 
                                        )"
                                       : ""
                                   ) . "
                               [AND] [WHERE]
                               [HAVING]
                               [ORDER] [COLON] vgallery_nodes.is_dir DESC, display_path
                               [LIMIT]";
        $oField->actex_update_from_db = true;
        $oField->parent_page = array($cm->oPage);

      /*  $f_publish_start = ffField::factory($cm->oPage);
        $f_publish_start->id = "rel" . $contest . "_start";
        $f_publish_start->base_type = "Date";
        $f_publish_start->widget = "datepicker";
        $f_publish_start->parent_page = array($cm->oPage);


        $f_publish_end = ffField::factory($cm->oPage);
        $f_publish_end->id = "rel" . $contest . "_end";
        $f_publish_end->base_type = "Date";
        $f_publish_end->widget = "datepicker";
        $f_publish_end->parent_page = array($cm->oPage);
     */   
        $oAddRel = ffButton::factory($cm->oPage);
        $oAddRel->id = "addrel" . $contest; 
        $oAddRel->label = ffTemplate::_get_word_by_code("add_rel");
        
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $oAddRel->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'addrel'});";
        } else {
            $oAddRel->action_type = "submit";
            $oAddRel->frmAction = "addrel";
        }
        $oAddRel->aspect = "link";
        $oAddRel->parent_page = array($cm->oPage);

        //$oRecord->fixed_post_content = $oField->process() . $f_publish_start->process() . $f_publish_end->process() . $oAddRel->process();
        
        $sSQL_publishing_detail = "SELECT 
                                    rel_nodes.ID AS ID
                                    , (SELECT
                                        IF( " . (Cms::env("ENABLE_STD_PERMISSION")
													? " 1 "
													: (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && Cms::env("ENABLE_ADV_PERMISSION") && !OLD_VGALLERY
														? " vgallery_nodes_rel_languages.visible > 0 " 
														: " vgallery_nodes.visible > 0 "
													)
							   					)  
                                               . ($publish_hide_dir
                                                   ? " AND IF(vgallery.limit_level = (LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) - LENGTH(REPLACE(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), '/', '')))
                                                        , 1
                                                        , NOT(vgallery_nodes.is_dir > 0) 
                                                    )"
                                                   : ""
                                               ) . "
                                            , IF(vgallery_nodes.is_dir > 0
    											, CONCAT(
										            REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
										            , IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
										        )
											    , CONCAT(
													IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
													, IF(vgallery_nodes.isbn
														, ' ISBN: '
														, ''
													)
										            , vgallery_nodes.isbn
											        , REPLACE(CONCAT(' (', vgallery_nodes.parent, ') '), '-', ' ')
											    )
					                        )
                                            , CONCAT('<del>'
                                                , IF(vgallery_nodes.is_dir > 0
    												, CONCAT(
										                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
										                , IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
										            )
											        , CONCAT(
														IF(vgallery_nodes.meta_title_alt = '', vgallery_nodes.meta_title, vgallery_nodes.meta_title_alt)
														, IF(vgallery_nodes.isbn
															, ' ISBN: '
															, ''
														)
										                , vgallery_nodes.isbn
											            , REPLACE(CONCAT(' (', vgallery_nodes.parent, ') '), '-', ' ')
											        )
					                            )
                                                , '</del>'
                                            )
                                        ) AS name
                                        FROM vgallery_nodes 
                                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                        WHERE vgallery_nodes.ID = ID_node_src
                                    ) AS nodes
                                    , rel_nodes.ID_node_src AS ID_node_src
                                    , rel_nodes.contest_src AS contest_src
                                    , rel_nodes.ID_node_dst AS ID_node_dst
                                    , rel_nodes.contest_dst AS contest_dst
                                    , rel_nodes.date_begin AS date_begin
                                    , rel_nodes.date_end AS date_end
                                    , rel_nodes.highlight AS highlight
                                    , rel_nodes.`order` AS `order`
                                FROM rel_nodes 
                                WHERE 
                                    ID_node_dst = [ID_FATHER] 
                                    AND contest_dst ='publishing'
                                ORDER BY rel_nodes.`order`, rel_nodes.ID";        
        break;
    case "anagraph":
        $oField = ffField::factory($cm->oPage);
        $oField->id = "relanagraph";
        $oField->label = ffTemplate::_get_word_by_code("rel_anagraph");
        $oField->base_type = "Number";
        $oField->resources[] = "DetailModify";
        $oField->source_SQL = "SELECT DISTINCT anagraph.ID 
                                    , CONCAT(anagraph.name, ' ', anagraph.surname) AS display_path
                                    , IF(anagraph.avatar = ''
                                        , '" . Cms::getInstance("frameworkcss")->get("noimg", "icon-tag", "2x") . " ' 
                                        , CONCAT('<img src=\"" . CM_SHOWFILES . "/80x80', anagraph.avatar, '\" />')  
                                    ) AS image
                                FROM anagraph
                                WHERE 1
 									AND anagraph.ID NOT IN ( 
                                        SELECT anagraph.ID
                                        FROM
                                            anagraph
                                            INNER JOIN rel_nodes
                                                ON 
                                                (
                                                    rel_nodes.ID_node_src = anagraph.ID 
                                                    AND rel_nodes.contest_dst = " . $db_gallery->toSql("publishing", "Text") . " 
                                                    AND rel_nodes.ID_node_dst = " . $db_gallery->toSql($ID_publishing, "Number") . " 
                                                )
                                            WHERE 1
                                    )                                
                                    " . ($oRecord->user_vars["contest"]
                                        ? "AND FIND_IN_SET(" . $db_gallery->toSql($oRecord->user_vars["contest"], "Number") . ", anagraph.categories)"
                                        : ""
                                    ) . "
                               [AND] [WHERE]
                               [HAVING]
                               [ORDER] [COLON] display_path
                               [LIMIT]";
        $oField->widget = "autocomplete";
        $oField->autocomplete_compare = "CONCAT(anagraph.name, ' ', anagraph.surname)";
        $oField->actex_update_from_db = true;
        $oField->autocomplete_combo = true;
        $oField->autocomplete_minLength = 0;
        $oField->autocomplete_multi = true;
        $oField->actex_update_from_db = true;
        $oField->parent_page = array($cm->oPage);

        $oAddRel = ffButton::factory($cm->oPage);
        $oAddRel->id = "addrel" . $contest; 
        $oAddRel->label = ffTemplate::_get_word_by_code("add_rel");
        
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $oAddRel->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'addrel'});";
        } else {
            $oAddRel->action_type = "submit";
            $oAddRel->frmAction = "addrel";
        }
        $oAddRel->aspect = "link";
        $oAddRel->parent_page = array($cm->oPage);
        
        $sSQL_publishing_detail = "SELECT 
                                    rel_nodes.ID AS ID
                                    , (SELECT 
                                        CONCAT(
                                            IF(anagraph.avatar = ''
                                                , '" . Cms::getInstance("frameworkcss")->get("noimg", "icon-tag", "2x") . " ' 
                                                , CONCAT('<img src=\"" . CM_SHOWFILES . "/80x80', anagraph.avatar, '\" />')  
                                            ) 
                                            , anagraph.name
                                            , ' '
                                            , anagraph.surname
                                        ) AS display_path
                                        FROM anagraph 
                                        WHERE anagraph.ID = ID_node_src
                                    ) AS nodes
                                    , rel_nodes.ID_node_src AS ID_node_src
                                    , rel_nodes.contest_src AS contest_src
                                    , rel_nodes.ID_node_dst AS ID_node_dst
                                    , rel_nodes.contest_dst AS contest_dst
                                    , rel_nodes.date_begin AS date_begin
                                    , rel_nodes.date_end AS date_end
                                    , rel_nodes.class AS class
                                    , rel_nodes.highlight AS highlight
                                    , rel_nodes.`order` AS `order`
                                FROM rel_nodes 
                                WHERE 
                                    ID_node_dst = [ID_FATHER] 
                                    AND contest_dst ='publishing'
                                ORDER BY rel_nodes.`order`, rel_nodes.ID";
        break;
    default:
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "rel" . $oRecord->user_vars["src_type"];
        $oField->label = ffTemplate::_get_word_by_code("rel_" . $oRecord->user_vars["src_type"]);
        $oField->base_type = "Number";
        $oField->resources[] = "DetailModify";
        $oField->source_SQL = "SELECT DISTINCT " . $oRecord->user_vars["src_table"] . ".ID 
                                    , " . $oRecord->user_vars["src_table"] . ".name AS display_path
                                FROM " . $oRecord->user_vars["src_table"] . "
                                WHERE 1
                                    " . ($oRecord->user_vars["contest"]
                                        ? "AND FIND_IN_SET(" . $db_gallery->toSql($oRecord->user_vars["contest"], "Number") . ", " . $oRecord->user_vars["src_table"] . ".categories)"
                                        : ""
                                    ) . "
                               [AND] [WHERE]
                               [HAVING]
                               [ORDER] [COLON] display_path
                               [LIMIT]";
        $oField->widget = "autocomplete";
        $oField->autocomplete_compare = "" . $oRecord->user_vars["src_table"] . ".name";
        $oField->actex_update_from_db = true;
        $oField->autocomplete_combo = true;
        $oField->autocomplete_minLength = 0;
        $oField->autocomplete_multi = true;
        $oField->actex_update_from_db = true;
        $oField->parent_page = array($cm->oPage);

        $oAddRel = ffButton::factory($cm->oPage);
        $oAddRel->id = "addrel" . $contest; 
        $oAddRel->label = ffTemplate::_get_word_by_code("add_rel");
        
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $oAddRel->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'addrel'});";
        } else {
            $oAddRel->action_type = "submit";
            $oAddRel->frmAction = "addrel";
        }
        $oAddRel->aspect = "link";
        $oAddRel->parent_page = array($cm->oPage); 

        $sSQL_publishing_detail = "SELECT 
                                    rel_nodes.ID AS ID
                                    , (SELECT 
                                            " . $oRecord->user_vars["src_table"] . ".name
                                        FROM " . $oRecord->user_vars["src_table"] . "
                                        WHERE " . $oRecord->user_vars["src_table"] . ".ID = ID_node_src
                                    ) AS nodes
                                    , rel_nodes.ID_node_src AS ID_node_src
                                    , rel_nodes.contest_src AS contest_src
                                    , rel_nodes.ID_node_dst AS ID_node_dst
                                    , rel_nodes.contest_dst AS contest_dst
                                    , rel_nodes.date_begin AS date_begin
                                    , rel_nodes.date_end AS date_end
                                    , rel_nodes.highlight AS highlight
                                    , rel_nodes.`order` AS `order`
                                FROM rel_nodes 
                                WHERE 
                                    ID_node_dst = [ID_FATHER] 
                                    AND contest_dst ='publishing'
                                ORDER BY rel_nodes.`order`, rel_nodes.ID";
}

$cm->oPage->addContent($oRecord);

$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "DetailModify";
$oDetail->resources[] = $oDetail->id;
//$oDetail->title = ffTemplate::_get_word_by_code("publishing_detail_modify");
$oDetail->src_table = "rel_nodes";     
$oDetail->addEvent("on_do_action", "PublishingDetailModify_on_do_action");
if($_REQUEST["XHR_DIALOG_ID"]) {
	$oDetail->ever_reload_data = false;
} else {
	$oDetail->ever_reload_data = false;
}
/* non funziona il parametro row e non passa i valori della action.
$tmp = ffButton::factory($cm->oPage);
$tmp->id 			= "deleterow";
$tmp->image 		= $oDetail->buttons_options["delete"]["image"];
$tmp->class         = $oDetail->buttons_options["delete"]["class"];
$tmp->aspect 		= "link";
$tmp->action_type 	= "submit";
$tmp->component_action = "";
$tmp->jsaction = "ff.ajax.doRequest({'action' : 'mydelete', 'addFields' : [{'name' : 'row', 'value' : [ROW]}]});";
$oDetail->addContentButton($tmp);

$oDetail->buttons_options["delete"]["display"] = true;*/
 
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_node_dst" => "ID");
$oDetail->display_new = false;
$oDetail->delete_istant = true;
$oDetail->display_delete = true;
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = $sSQL_publishing_detail;
$oDetail->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oDetail
            , array(
                "resource_id" =>  "publishing_node"
                , "service_path" => $cm->oPage->site_path . VG_SITE_SERVICES . "/sort"
            )
            , "ID"
        )
    );
$oDetail->display_grid_location = "Footer";

$oField->parent = array($oDetail);
//$f_publish_start->parent = array($oDetail);
//$f_publish_end->parent = array($oDetail);

$oDetail->fixed_post_content = '<div class="' . Cms::getInstance("frameworkcss")->get("", "row-default") . " " . $oField->get_control_class() . '">' . $oField->process() . $oAddRel->process() . '</div>';


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->source_SQL = " `order`, ID";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_node_src";
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contest_src";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contest_dst";
$oDetail->addHiddenField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "nodes";
$oField->label = ffTemplate::_get_word_by_code("publishing_detail_nodes");
$oField->control_type = "label";
$oField->store_in_db = false;
$oField->encode_entities = false;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "date_begin";
$oField->label = ffTemplate::_get_word_by_code("publishing_detail_date_begin");
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "date_end";
$oField->label = ffTemplate::_get_word_by_code("publishing_detail_date_end");
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oDetail->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "class";
$oField->label = ffTemplate::_get_word_by_code("publishing_detail_class");
$oDetail->addContent($oField);


if(check_function("set_fields_grid_system")) {
    set_fields_grid_system($oDetail, array(
            "group" => "highlight"
            , "fluid" => array( 
                "prefix" => "highlight"
                , "one_field" => true
                , "choice" => false
                , "col" => array(
                    "default_value" => 0
                )
            )
            , "class" => false
            , "wrap" => false
        )
    );
}  

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
                         
// -------------------------
//          EVENTI
// -------------------------


function PublishingDetailModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();

    
    switch($action) {
        case "insert":
        case "update":
        	/*if($component->main_record[0]->user_vars["area"] == "gallery") {
        		$element_type = "G";
			} else {
				$element_type = "V";
			}

            if(is_array($component->recordset) && count($component->recordset)) {
                $valid_language_default = false;
                
                foreach($component->recordset AS $rst_key => $rst_value) {
	                $arrKeys[] = $component->recordset[$rst_key]["ID_node_src"];
				}
				if(is_array($arrKeys) && count($arrKeys)) {
					$db->query("SELECT * FROM vgallery_nodes WHERE vgallery_nodes.ID IN (" . $db->toSql(implode(", ", $arrKeys), "Text", false) . ")");
					if($db->nextRecord()) {
						$arrNode[] = $db->getField("ID", "Number", true);
					}
				}
			}*/
			
			if(check_function("refresh_cache")) {
				refresh_cache_get_blocks_by_layout($component->main_record[0]->user_vars["area"] . "_" . $component->main_record[0]->key_fields["ID"]->getValue());
			}

    		break;
	}    
}
?>
