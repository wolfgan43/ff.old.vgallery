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

if (!AREA_PUBLISHING_SHOW_DETAIL) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

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
                                    rel_nodes.ID_node_dst = " . $db->toSql($ID_publishing,  "Number") . "
                                    AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . "
                                ) 
                            OR 
                                (
                                    rel_nodes.ID_node_src = " . $db->toSql($ID_publishing,  "Number") . "
                                    AND rel_nodes.contest_src = " . $db->toSql("publishing", "Text") . "
                                )
                            )
                    ) AS count_publish       
                FROM publishing
                WHERE publishing.ID = " . $db->toSql($ID_publishing, "Number");
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $publishing_title .= ": " . ucwords($db->getField("display_name", "Text", true)) . "(" . $db->getField("count_publish", "Number", true) . "/"  . $db->getField("limit", "Number", true) . ")";
        $publishing_area = $db->getField("area", "Text", true);
        $publishing_contest = $db->getField("contest", "Text", true);
        $publishing_relative_path = $db->getField("relative_path", "Text", true);
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
                                rel_nodes.ID_node_src = " . $db->toSql($ID_node, "Number") . "
                                AND rel_nodes.ID_node_dst = " . $db->toSql($ID_publishing,  "Number") . "
                                AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . "
                            ) 
                        OR 
                            (
                                rel_nodes.ID_node_dst = " . $db->toSql($ID_node, "Number") . "
                                AND rel_nodes.ID_node_src = " . $db->toSql($ID_publishing,  "Number") . "
                                AND rel_nodes.contest_src = " . $db->toSql("publishing", "Text") . "
                            )
                        )";            
        $db->query($sSQL); 
        if($db->nextRecord()) {
            $rel_type = $db->getField("rel_type", "Text", true);
        }

        if(check_function("refresh_cache")) {
        	refresh_cache_get_blocks_by_layout($publishing_area . "_" . $ID_publishing);
		}
        $sSQL = "DELETE FROM rel_nodes
                WHERE 
                    rel_nodes.ID_node_src = " . $db->toSql($ID_node, "Number") . "
                    AND rel_nodes.ID_node_dst = " . $db->toSql($ID_publishing,  "Number") . "
                    AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text");
        $db->execute($sSQL);
        if(!$db->affectedRows()) {
            $sSQL = "DELETE FROM rel_nodes
                    WHERE 
                        rel_nodes.ID_node_dst = " . $db->toSql($ID_node, "Number") . "
                        AND rel_nodes.ID_node_src = " . $db->toSql($ID_publishing,  "Number") . "
                        AND rel_nodes.contest_src = " . $db->toSql("publishing", "Text");
            $db->execute($sSQL);
        }

        if($_REQUEST["XHR_CTX_ID"]) {
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
                        $sSQL_Where = " files.ID = " . $db->tosql($rel_nodes, "Number");
                    } else {
                        $sSQL_Where = " files.ID IN(" . $db->tosql($rel_nodes, "Text", false) . ")";
                    }

                    //$rel_nodes_start = $_REQUEST["DetailModify_relfiles_start"];
                    //$rel_nodes_end = $_REQUEST["DetailModify_relfiles_end"];
                    $rel_type = "files";

                    $sSQL = "SELECT files.ID
                                , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS display_path
                            FROM files 
                            WHERE $sSQL_Where";
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        do {
                            $node[$db->getField("ID", "Number", true)]["node"] = $db->getField("display_path", "Text", true);
                            $node[$db->getField("ID", "Number", true)]["type"] = $rel_type;
                        } while($db->nextRecord());
                    }
                }
                break;
            case "vgallery":
                $db->query("SELECT vgallery.* FROM vgallery");
                if($db->nextRecord()) {
                    do {
                        if(isset($_REQUEST["DetailModify_rel" . $db->getField("name")->getValue()])) {
                            $rel_nodes = $_REQUEST["DetailModify_rel" . $db->getField("name")->getValue()];
                            if(strlen($rel_nodes)) {
                                if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                                    $sSQL_Where = " vgallery_nodes.ID = " . $db->tosql($rel_nodes, "Number");
                                } else {
                                    $sSQL_Where = " vgallery_nodes.ID IN(" . $db->tosql($rel_nodes, "Text", false) . ")";
                                }

                                //$rel_nodes_start = $_REQUEST["DetailModify_rel" . $db->getField("name")->getValue() . "_start"];
                                //$rel_nodes_end = $_REQUEST["DetailModify_rel" . $db->getField("name")->getValue() . "_end"];
                                $rel_type = $db->getField("name", "Text", true);

                                $sSQL = "SELECT vgallery_nodes.ID
                                            , vgallery_nodes.name 
                                            , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS display_path
                                            , IFNULL(
                                                (
                                                    SELECT 
                                                        IF(vgallery_nodes.is_dir > 0
                                                            , CONCAT(
                                                                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
                                                                , (GROUP_CONCAT(DISTINCT 
                                                                    IF(vgallery_rel_nodes_fields.description_text = ''
                                                                        , vgallery_rel_nodes_fields.description
                                                                        , vgallery_rel_nodes_fields.description_text
                                                                    ) 
                                                                    ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                                )
                                                            )
                                                            , CONCAT(
                                                                (GROUP_CONCAT(DISTINCT 
                                                                    IF(vgallery_rel_nodes_fields.description_text = ''
                                                                        , vgallery_rel_nodes_fields.description
                                                                        , vgallery_rel_nodes_fields.description_text
                                                                    ) 
                                                                    ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                                )
                                                                , REPLACE(IF(REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"]) . ", '') = '', '', CONCAT(' (', REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"] . "/") . ", ''), ') ')), '-', ' ')
                                                            )
                                                        ) AS name
                                                    FROM vgallery_rel_nodes_fields 
                                                        INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                                    WHERE 
                                                        1
                                                        AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
                                                        AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
                                                        AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                )
                                                , vgallery_nodes.name
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
                    } while($db->nextRecord());
                }

                if($node === null && isset($_REQUEST["DetailModify_relall"])) {
                    $rel_nodes = $_REQUEST["DetailModify_relall"];
                    if(strlen($rel_nodes)) {
                        if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                            $sSQL_Where = " vgallery_nodes.ID = " . $db->tosql($rel_nodes, "Number");
                        } else {
                            $sSQL_Where = " vgallery_nodes.ID IN(" . $db->tosql($rel_nodes, "Text", false) . ")";
                        }

                        //$rel_nodes_start = $_REQUEST["DetailModify_relall_start"];
                        //$rel_nodes_end = $_REQUEST["DetailModify_relall_end"];
                        
                        $sSQL = "SELECT vgallery_nodes.ID AS ID
                                    , vgallery.name AS vgallery_name
                                    , vgallery_nodes.name AS node 
                                    , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS display_path
                                    , (
                                        SELECT CONCAT(IF(ISNULL(GROUP_CONCAT(vgallery_rel_nodes_fields.description_text))
                                                                , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)
                                                                , GROUP_CONCAT(DISTINCT vgallery_rel_nodes_fields.description_text ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                            )
                                                        ) 
                                        FROM vgallery_rel_nodes_fields 
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                        WHERE 
                                            vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
                                            AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 )
                                            AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                    ) AS display_real_name
                                FROM vgallery_nodes 
                                    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery 
                                WHERE $sSQL_Where ";
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            do {
                                $node[$db->getField("ID", "Number", true)]["node"] = (strlen($db->getField("display_real_name", "Text", true)) ? $db->getField("display_real_name", "Text", true) : $db->getField("display_path", "Text", true));
                                $node[$db->getField("ID", "Number", true)]["type"] = $db->getField("vgallery_name", "Text", true);
                            } while($db->nextRecord());
                        }
                    }
                }            
                break;
            case "anagraph":
                $rel_nodes = $_REQUEST["DetailModify_relanagraph"];
                if(strlen($rel_nodes)) {
                    if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                        $sSQL_Where = " anagraph.ID = " . $db->tosql($rel_nodes, "Number");
                    } else {
                        $sSQL_Where = " anagraph.ID IN(" . $db->tosql($rel_nodes, "Text", false) . ")";
                    }

                    //$rel_nodes_start = $_REQUEST["DetailModify_relfiles_start"];
                    //$rel_nodes_end = $_REQUEST["DetailModify_relfiles_end"];
                    $rel_type = "anagraph";

                    $sSQL = "SELECT anagraph.ID
                                , CONCAT(
                                    IF(anagraph.avatar = ''
                                        , '" . cm_getClassByFrameworkCss("noimg", "icon-tag", "2x") . " ' 
                                        , CONCAT('<img src=\"" . CM_SHOWFILES . "/32x32', anagraph.avatar, '\" />')  
                                    ) 
                                    , anagraph.name
                                    , ' '
                                    , anagraph.surname
                                ) AS display_path
                            FROM anagraph 
                            WHERE $sSQL_Where";
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        do {
                            $node[$db->getField("ID", "Number", true)]["node"] = $db->getField("display_path", "Text", true);
                            $node[$db->getField("ID", "Number", true)]["type"] = $rel_type;
                        } while($db->nextRecord());
                    }
                }            
                break;
            default:
                $rel_nodes = $_REQUEST["DetailModify_relanagraph"];
                if(strlen($rel_nodes)) {
                    if(is_numeric($rel_nodes) && $rel_nodes > 0) {
                        $sSQL_Where = " " . $src_type . ".ID = " . $db->tosql($rel_nodes, "Number");
                    } else {
                        $sSQL_Where = " " . $src_type . ".ID IN(" . $db->tosql($rel_nodes, "Text", false) . ")";
                    }

                    //$rel_nodes_start = $_REQUEST["DetailModify_relfiles_start"];
                    //$rel_nodes_end = $_REQUEST["DetailModify_relfiles_end"];
                    $rel_type = $src_type;

                    $sSQL = "SELECT " . $src_type . ".ID
                                , " . $src_type . ".name AS display_path
                            FROM " . $src_type . " 
                            WHERE $sSQL_Where";
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        do {
                            $node[$db->getField("ID", "Number", true)]["node"] = $db->getField("display_path", "Text", true);
                            $node[$db->getField("ID", "Number", true)]["type"] = $rel_type;
                        } while($db->nextRecord());
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
		                            , " . $db->toSql($rel_nodes, "Number") . " 
		                            , " . $db->toSql($rel_type, "Text") . "
		                            , " . $db->toSql($ID_publishing, "Number") . "
		                            , " . $db->toSql("publishing", "Text") . "
	                            )
	                ";
	                $db->execute($sSQL);
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
                            " . $db->toSql($rel_nodes, "Number") . ", 
                            " . $db->toSql($rel_type, "Text") . ",
                            " . $db->toSql($ID_publishing, "Number") . ", 
                            " . $db->toSql("publishing", "Text") . ", 
                            " . $db->toSql(new ffData($rel_nodes_start, "Date", LANGUAGE_INSET)) . ", 
                            " . $db->toSql(new ffData($rel_nodes_end, "Date", LANGUAGE_INSET)) . " 
                        )
            ";
            $db->execute($sSQL);*/
            if($_REQUEST["XHR_CTX_ID"]) {
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
//$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content-adv">' . cm_getClassByFrameworkCss("vg-publishing", "icon-tag", array("2x", "content-adv")) . $publishing_title . '</h1>';
if(check_function("system_ffcomponent_set_title"))
	$oRecord->setTitle(system_ffcomponent_set_title(
		$publishing_title
		, array(
			"name" => "vg-publishing"
			, "type" => "content-adv"
		)
	), 'admin-title vg-content-adv');
$oRecord->setTitle(cm_getClassByFrameworkCss("vg-publishing", "icon-tag", array("2x", "content-adv")) . $publishing_title, 'admin-title vg-content-adv');

$oRecord->src_table = "publishing";
if(0 && $_REQUEST["XHR_CTX_ID"]) {
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
if($db->getField("full_selection", "Number", true) > 0) { 
    ffDialog(false, "okonly", ffTemplate::_get_word_by_code("publishing_warning"), ffTemplate::_get_word_by_code("publishing_disable_manual_selection"), "", $_REQUEST["ret_url"], $cm->oPage->site_path . $cm->oPage->page_path . "/dialog");
} 

switch($oRecord->user_vars["src_type"]) {
    case "gallery":
    //  if(check_function("check_fs"))
    //      check_fs(DISK_UPDIR . $oRecord->user_vars["relative_path"], $oRecord->user_vars["relative_path"]);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "relfiles";
        $oField->label = ffTemplate::_get_word_by_code("rel_files");
        $oField->base_type = "Number";
        //$oField->widget = "actex";
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
                                                        AND rel_nodes.contest_src = " . $db->toSql("files", "Text") . "
                                                        AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . " 
                                                        AND rel_nodes.ID_node_dst = " . $db->toSql($ID_publishing, "Number") . "
                                                    )
                                            )
                                       AND files.parent LIKE '" . $db->toSql($oRecord->user_vars["relative_path"], "Text", false) . "%'
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
        if($_REQUEST["XHR_CTX_ID"]) {
            $oAddRel->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'addrel'});";
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
                                    , rel_nodes.class AS class
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
                             AND vgallery.name = " . $db->toSql($oRecord->user_vars["contest"]) . "
                             AND (vgallery_nodes.parent = " . $db->toSql("/" . $oRecord->user_vars["contest"] . stripslash($oRecord->user_vars["relative_path"]))  . " 
                                OR vgallery_nodes.parent LIKE '" . $db->toSql("/" . $oRecord->user_vars["contest"] . stripslash($oRecord->user_vars["relative_path"]), "Text", false)  . "/%'
                             )";
           // $display_fields = " , CONCAT('/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)) AS display_path";
            $display_fields = " , IFNULL(
                                    (
                                        SELECT 
                                            IF(vgallery_nodes.is_dir > 0
                                                , CONCAT(
                                                    REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
                                                    , (GROUP_CONCAT(DISTINCT 
                                                        IF(vgallery_rel_nodes_fields.description_text = ''
                                                            , vgallery_rel_nodes_fields.description
                                                            , vgallery_rel_nodes_fields.description_text
                                                        ) 
                                                        ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                    )
                                                )
                                                , CONCAT(
                                                    (GROUP_CONCAT(DISTINCT 
                                                        IF(vgallery_rel_nodes_fields.description_text = ''
                                                            , vgallery_rel_nodes_fields.description
                                                            , vgallery_rel_nodes_fields.description_text
                                                        ) 
                                                        ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                    )
                                                    , REPLACE(IF(REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"]) . ", '') = '', '', CONCAT(' (', REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"] . "/") . ", ''), ') ')), '-', ' ')
                                                )
                                            ) AS name
                                        FROM vgallery_rel_nodes_fields 
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                        WHERE 
                                            1
                                            AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
                                            AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
                                            AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                    )
                                    , vgallery_nodes.name
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
                            AND (vgallery_nodes.parent = " . $db->toSql($oRecord->user_vars["relative_path"])  . " 
                                OR vgallery_nodes.parent LIKE '" . $db->toSql($oRecord->user_vars["relative_path"], "Text", false)  . "/%'
                            ) ";

            //$display_fields = " , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS display_path";
            $display_fields = " , IFNULL(
                                    (
                                        SELECT 
                                            IF(vgallery_nodes.is_dir > 0
                                                , CONCAT(
                                                    REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
                                                    , (GROUP_CONCAT(DISTINCT 
                                                        IF(vgallery_rel_nodes_fields.description_text = ''
                                                            , vgallery_rel_nodes_fields.description
                                                            , vgallery_rel_nodes_fields.description_text
                                                        ) 
                                                        ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                    )
                                                )
                                                , CONCAT(
                                                    (GROUP_CONCAT(DISTINCT 
                                                        IF(vgallery_rel_nodes_fields.description_text = ''
                                                            , vgallery_rel_nodes_fields.description
                                                            , vgallery_rel_nodes_fields.description_text
                                                        ) 
                                                        ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                    )
                                                    , REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(' (', vgallery_nodes.parent, ') ')), '-', ' ')
                                                )
                                            ) AS name
                                        FROM vgallery_rel_nodes_fields 
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                        WHERE 
                                            1
                                            AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
                                            AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
                                            AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                    )
                                    , vgallery_nodes.name
                                ) AS display_path";
        }

        $oField = ffField::factory($cm->oPage);
        $oField->id = "rel" . $contest;
        $oField->label = ffTemplate::_get_word_by_code("rel_" . $contest);
        $oField->base_type = "Number";
        //$oField->widget = "actex";
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

    /* //DA sistemare FRONTEND
    " . (AREA_SHOW_ECOMMERCE && AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK
                                            ? "
                                                INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_nodes.ID 
                                                    AND ecommerce_settings.actual_qta > 0 "
                                            : ""
                                    ) . "
    */
        $oField->resources[] = "DetailModify";
        $oField->source_SQL = "SELECT DISTINCT vgallery_nodes.ID
                                    $display_fields
                               FROM vgallery_nodes
                                    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                    
                               WHERE " . $contest_sql . "
                                    AND (" . (AREA_SHOW_ECOMMERCE && AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK
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
                                                    AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . " 
                                                    AND rel_nodes.ID_node_dst = " . $db->toSql($ID_publishing, "Number") . " 
                                                )
                                            WHERE 1
                                    )
                                    " . (ENABLE_STD_PERMISSION
                                        ? "
                                            AND vgallery_nodes.ID
                                                NOT IN 
                                                (
                                                    SELECT vgallery_rel_nodes_fields.ID_nodes
                                                        FROM vgallery_rel_nodes_fields
                                                    WHERE
                                                        vgallery_rel_nodes_fields.ID_fields = (SELECT vgallery_fields.ID 
                                                                                                FROM vgallery_fields 
                                                                                                    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type 
                                                                                                WHERE vgallery_fields.name = " .  $db->toSql("visible", "Text") . " 
                                                                                                    AND vgallery_type.name = " .  $db->toSql("System", "Text") . ")
                                                        AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                        AND vgallery_rel_nodes_fields.description_text = " . $db->toSql("0", "Text") . "
                                                )
                                        "
                                        : " AND vgallery_nodes.visible > 0"
                                    ) . "
                                   " . ($publish_hide_dir
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
    /*
                                    " . (AREA_SHOW_ECOMMERCE && AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK
                                            ? "
                                                AND (
                                                        (
                                                            SELECT ecommerce_settings.actual_qta 
                                                            FROM ecommerce_settings 
                                                            WHERE ecommerce_settings.ID_items = vgallery_nodes.ID 
                                                             GROUP BY ecommerce_settings.ID_items
                                                        ) > 0
                                                        OR (vgallery_nodes.is_dir > 0)
                                                    )"
                                            : ""
                                    ) . "
    */
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
        
        if($_REQUEST["XHR_CTX_ID"]) {
            $oAddRel->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'addrel'});";
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
                                        IF( 
                                            " . (ENABLE_STD_PERMISSION
                                                ? "
                                                    vgallery_nodes.ID
                                                        NOT IN 
                                                        (
                                                            SELECT vgallery_rel_nodes_fields.ID_nodes
                                                                FROM vgallery_rel_nodes_fields
                                                            WHERE
                                                                vgallery_rel_nodes_fields.ID_fields = (SELECT vgallery_fields.ID 
                                                                                                        FROM vgallery_fields 
                                                                                                            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type 
                                                                                                        WHERE vgallery_fields.name = " .  $db->toSql("visible", "Text") . " 
                                                                                                            AND vgallery_type.name = " .  $db->toSql("System", "Text") . ")
                                                                AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                                AND vgallery_rel_nodes_fields.description_text = " . $db->toSql("0", "Text") . "
                                                        )
                                                "
                                                : " vgallery_nodes.visible"
                                            ) . "
                                               " . ($publish_hide_dir
                                                   ? " AND IF(vgallery.limit_level = (LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) - LENGTH(REPLACE(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), '/', '')))
                                                        , 1
                                                        , NOT(vgallery_nodes.is_dir > 0) 
                                                    )"
                                                   : ""
                                               ) . "
                                            , IFNULL(
                                                (
                                                    SELECT 
                                                        IF(vgallery_nodes.is_dir > 0
                                                            , CONCAT(
                                                                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
                                                                , (GROUP_CONCAT(DISTINCT 
                                                                    IF(vgallery_rel_nodes_fields.description_text = ''
                                                                        , vgallery_rel_nodes_fields.description
                                                                        , vgallery_rel_nodes_fields.description_text
                                                                    ) 
                                                                    ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                                )
                                                            )
                                                            , CONCAT(
                                                                (GROUP_CONCAT(DISTINCT 
                                                                    IF(vgallery_rel_nodes_fields.description_text = ''
                                                                        , vgallery_rel_nodes_fields.description
                                                                        , vgallery_rel_nodes_fields.description_text
                                                                    ) 
                                                                    ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                                )
                                                                , REPLACE(IF(REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"]) . ", '') = '', '', CONCAT(' (', REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"] . "/") . ", ''), ') ')), '-', ' ')
                                                            )
                                                        ) AS name
                                                    FROM vgallery_rel_nodes_fields 
                                                        INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                                    WHERE 
                                                        1
                                                        AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
                                                        AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
                                                        AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                )
                                                , vgallery_nodes.name
                                            )
                                            , CONCAT('<del>'
                                                , IFNULL(
                                                    (
                                                        SELECT 
                                                            IF(vgallery_nodes.is_dir > 0
                                                                , CONCAT(
                                                                    REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
                                                                    , (GROUP_CONCAT(DISTINCT 
                                                                        IF(vgallery_rel_nodes_fields.description_text = ''
                                                                            , vgallery_rel_nodes_fields.description
                                                                            , vgallery_rel_nodes_fields.description_text
                                                                        ) 
                                                                        ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                                    )
                                                                )
                                                                , CONCAT(
                                                                    (GROUP_CONCAT(DISTINCT 
                                                                        IF(vgallery_rel_nodes_fields.description_text = ''
                                                                            , vgallery_rel_nodes_fields.description
                                                                            , vgallery_rel_nodes_fields.description_text
                                                                        ) 
                                                                        ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
                                                                    )
                                                                    , REPLACE(IF(REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"]) . ", '') = '', '', CONCAT(' (', REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"] . "/") . ", ''), ') ')), '-', ' ')
                                                                )
                                                            ) AS name
                                                        FROM vgallery_rel_nodes_fields 
                                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                                        WHERE 
                                                            1
                                                            AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
                                                            AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
                                                            AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                                    )
                                                    , vgallery_nodes.name
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
                                    , rel_nodes.class AS class
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
                                        , '" . cm_getClassByFrameworkCss("noimg", "icon-tag", "2x") . " ' 
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
                                                    AND rel_nodes.contest_dst = " . $db->toSql("publishing", "Text") . " 
                                                    AND rel_nodes.ID_node_dst = " . $db->toSql($ID_publishing, "Number") . " 
                                                )
                                            WHERE 1
                                    )                                
                                    " . ($oRecord->user_vars["contest"]
                                        ? "AND FIND_IN_SET(" . $db->toSql($oRecord->user_vars["contest"], "Number") . ", anagraph.categories)"
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
        
        if($_REQUEST["XHR_CTX_ID"]) {
            $oAddRel->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'addrel'});";
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
                                                , '" . cm_getClassByFrameworkCss("noimg", "icon-tag", "2x") . " ' 
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
                                        ? "AND FIND_IN_SET(" . $db->toSql($oRecord->user_vars["contest"], "Number") . ", " . $oRecord->user_vars["src_table"] . ".categories)"
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
        
        if($_REQUEST["XHR_CTX_ID"]) {
            $oAddRel->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'addrel'});";
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
                                    , rel_nodes.class AS class
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
if($_REQUEST["XHR_CTX_ID"]) {
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
/*
                                "SELECT 
	                                rel_nodes.ID AS ID
	                                , IF(ID_node_src = [ID_FATHER] AND contest_src = 'publishing'
	                                    , IF(contest_dst <> 'files'
	                                        , (SELECT  
													IFNULL(
                										(
							                                SELECT 
							                                    IF(vgallery_nodes.is_dir > 0
    																, CONCAT(
						                                                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
						                                                , (GROUP_CONCAT(DISTINCT 
                                                        					IF(vgallery_rel_nodes_fields.description_text = ''
                                                        						, vgallery_rel_nodes_fields.description
                                                        						, vgallery_rel_nodes_fields.description_text
                                                        					) 
                                                        					ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
						                                                )
						                                            )
							                                        , CONCAT(
						                                                (GROUP_CONCAT(DISTINCT 
                                                        					IF(vgallery_rel_nodes_fields.description_text = ''
                                                        						, vgallery_rel_nodes_fields.description
                                                        						, vgallery_rel_nodes_fields.description_text
                                                        					) 
                                                        					ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
						                                                )
							                                            , REPLACE(IF(REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"]) . ", '') = '', '', CONCAT(' (', REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"] . "/") . ", ''), ') ')), '-', ' ')
							                                        )
	                                        					) AS name
							                                FROM vgallery_rel_nodes_fields 
							                                    INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
							                                WHERE 
							                                    1
							                                    AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
							                                    AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
							                                    AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
						                                )
						                                , vgallery_nodes.name
						                            ) AS name 
                                            	FROM vgallery_nodes 
                                            	WHERE vgallery_nodes.ID = ID_node_dst
	                                        )
	                                        , (SELECT name 
                                            	FROM files 
                                            	WHERE files.ID = ID_node_dst
	                                        )
	                                    )
	                                    ,  IF(contest_src <> 'files'
	                                        , (SELECT
													IF( 
														" . (ENABLE_STD_PERMISSION
							                                ? "
							                                    vgallery_nodes.ID
							                                        NOT IN 
							                                        (
							                                            SELECT vgallery_rel_nodes_fields.ID_nodes
							                                                FROM vgallery_rel_nodes_fields
							                                            WHERE
							                                                vgallery_rel_nodes_fields.ID_fields = (SELECT vgallery_fields.ID 
							                                                                                        FROM vgallery_fields 
							                                                                                            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type 
							                                                                                        WHERE vgallery_fields.name = " .  $db->toSql("visible", "Text") . " 
							                                                                                            AND vgallery_type.name = " .  $db->toSql("System", "Text") . ")
							                                                AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
							                                                AND vgallery_rel_nodes_fields.description_text = " . $db->toSql("0", "Text") . "
							                                        )
							                                "
							                                : " vgallery_nodes.visible"
							                            ) . "
                               							" . ($publish_hide_dir
                               		    					? " AND IF(vgallery.limit_level = (LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) - LENGTH(REPLACE(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), '/', '')))
							                                        , 1
							                                        , NOT(vgallery_nodes.is_dir > 0) 
							                                    )"
                               		    					: ""
                               							) . "
														, IFNULL(
                											(
								                                SELECT 
								                                    IF(vgallery_nodes.is_dir > 0
    																	, CONCAT(
							                                                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
							                                                , (GROUP_CONCAT(DISTINCT 
                                                        						IF(vgallery_rel_nodes_fields.description_text = ''
                                                        							, vgallery_rel_nodes_fields.description
                                                        							, vgallery_rel_nodes_fields.description_text
                                                        						) 
                                                        						ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
							                                                )
							                                            )
								                                        , CONCAT(
							                                                (GROUP_CONCAT(DISTINCT 
                                                        						IF(vgallery_rel_nodes_fields.description_text = ''
                                                        							, vgallery_rel_nodes_fields.description
                                                        							, vgallery_rel_nodes_fields.description_text
                                                        						) 
                                                        						ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
							                                                )
								                                            , REPLACE(IF(REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"]) . ", '') = '', '', CONCAT(' (', REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"] . "/") . ", ''), ') ')), '-', ' ')
								                                        )
	                                        						) AS name
								                                FROM vgallery_rel_nodes_fields 
								                                    INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
								                                WHERE 
								                                    1
								                                    AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
								                                    AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
								                                    AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
							                                )
							                                , vgallery_nodes.name
							                            )
								                        , CONCAT('<del>'
								                            , IFNULL(
                												(
									                                SELECT 
									                                    IF(vgallery_nodes.is_dir > 0
    																		, CONCAT(
								                                                REPLACE(IF(vgallery_nodes.parent = '/', '', CONCAT(vgallery_nodes.parent, '/')), '-', ' ')
								                                                , (GROUP_CONCAT(DISTINCT 
                                                        							IF(vgallery_rel_nodes_fields.description_text = ''
                                                        								, vgallery_rel_nodes_fields.description
                                                        								, vgallery_rel_nodes_fields.description_text
                                                        							) 
                                                        							ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' - ')
								                                                )
								                                            )
									                                        , CONCAT(
								                                                (GROUP_CONCAT(DISTINCT 
                                                        							IF(vgallery_rel_nodes_fields.description_text = ''
                                                        								, vgallery_rel_nodes_fields.description
                                                        								, vgallery_rel_nodes_fields.description_text
                                                        							) 
                                                        							ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' - ')
								                                                )
									                                            , REPLACE(IF(REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"]) . ", '') = '', '', CONCAT(' (', REPLACE(vgallery_nodes.parent, " . $db->toSql($oRecord->user_vars["contest"] . "/") . ", ''), ') ')), '-', ' ')
									                                        )
	                                        							) AS name
									                                FROM vgallery_rel_nodes_fields 
									                                    INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
									                                WHERE 
									                                    1
									                                    AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
									                                    AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 OR vgallery_fields.enable_smart_url > 0)
									                                    AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
								                                )
								                                , vgallery_nodes.name
								                            )
								                            , '</del>'
								                        )
							                        ) AS name
                                            	FROM vgallery_nodes 
                                            		INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                            	WHERE vgallery_nodes.ID = ID_node_src
	                                        )
	                                        , (SELECT name 
                                            	FROM files 
                                            	WHERE files.ID = ID_node_src
	                                        )
	                                    )
	                                ) AS nodes
	                                , rel_nodes.ID_node_src AS ID_node_src
	                                , rel_nodes.contest_src AS contest_src
	                                , rel_nodes.ID_node_dst AS ID_node_dst
	                                , rel_nodes.contest_dst AS contest_dst
	                                , rel_nodes.date_begin AS date_begin
	                                , rel_nodes.date_end AS date_end
	                                , rel_nodes.class AS class
	                                , [ID_FATHER] AS ID_node_dst
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
	                            ORDER BY rel_nodes.ID";
*/
$oDetail->display_grid_location = "Footer";

$oField->parent = array($oDetail);
//$f_publish_start->parent = array($oDetail);
//$f_publish_end->parent = array($oDetail);

$oDetail->fixed_post_content = '<div class="' . cm_getClassByFrameworkCss("", "row-default") . " " . $oField->get_control_class() . '">' . $oField->process() . $oAddRel->process() . '</div>';


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
			if(check_function("refresh_cache")) {
				refresh_cache_get_blocks_by_layout($component->main_record[0]->user_vars["area"] . "_" . $component->main_record[0]->key_fields["ID"]->getValue());
			}
    		break;
	}    
}
