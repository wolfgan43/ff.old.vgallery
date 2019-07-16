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
if (!(Auth::env("AREA_VGALLERY_SHOW_PERMISSION") && Cms::env("ENABLE_STD_PERMISSION"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");
$record = system_ffComponent_resolve_record("vgallery_nodes", array(
	"full_path" => "(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)"
));

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGalleryNodesModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "vgallery_nodes";
$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->addEvent("on_do_action", "VGalleryNodesModify_on_do_action");
$oRecord->addEvent("on_done_action", "VGalleryNodesModify_on_done_action");

/* Title Block */
system_ffcomponent_set_title(
	$record["name"]
	, true
	, false
	, false
	, $oRecord
);	


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->additional_fields["last_update"] =  new ffData(time(), "Number");


$cm->oPage->addContent($oRecord);

if($ID_vgallery_nodes) {
    $sWhere = "";
    $src_path = $record["full_path"];
    do {
        $src_folder_name = basename($src_path);
        $src_folder_path = ffCommon_dirname($src_path);
        if (strlen($sWhere))
            $sWhere .= " OR ";
        $sWhere .= " (vgallery_nodes.parent = " . $db->toSql($src_folder_path, "Text") . " AND vgallery_nodes.name = " . $db->toSql($src_folder_name, "Text") . " )";
    } while($src_folder_name != "" && $src_path = ffCommon_dirname($src_path));
    
    
    $sSQL = "SELECT 
                *
                , " . $db->tosql($ID_vgallery_nodes, "Number") . " AS ID_vgallery_nodes
                , (
                    SELECT
                        IF(vgallery_nodes_rel_groups.mod & 1 = 0, 0, 1) AS result
                    FROM
                        vgallery_nodes INNER JOIN vgallery_nodes_rel_groups ON vgallery_nodes.ID = vgallery_nodes_rel_groups.ID_vgallery_nodes
                    WHERE 
                        vgallery_nodes_rel_groups.gid IN (" . CM_TABLE_PREFIX . "mod_security_groups.gid)
                        AND (" . $sWhere . ")

                        AND (
                                (vgallery_nodes.parent =  " . $db->toSql(ffCommon_dirname($record["full_path"]), "Text") . " AND vgallery_nodes.name =  " . $db->toSql(basename($record["full_path"]), "Text") . ") 
                            OR
                                (
                                    (vgallery_nodes.parent <> " . $db->toSql(ffCommon_dirname($record["full_path"]), "Text") . " OR vgallery_nodes.name <>  " . $db->toSql(basename($record["full_path"]), "Text") . ") 
                                AND
                                    (vgallery_nodes_rel_groups.mod & 4 = 0)
                                )
                            )
                    
                    ORDER  BY 
                        LENGTH(CONCAT(vgallery_nodes.parent, vgallery_nodes.name)) DESC
                        , vgallery_nodes_rel_groups.mod DESC

                    LIMIT 1 
                ) AS `read`
                , (
                    SELECT
                        IF(vgallery_nodes_rel_groups.mod & 2 = 0, 0, 2) AS result
                    FROM
                        vgallery_nodes INNER JOIN vgallery_nodes_rel_groups ON vgallery_nodes.ID = vgallery_nodes_rel_groups.ID_vgallery_nodes
                    WHERE 
                        vgallery_nodes_rel_groups.gid IN (" . CM_TABLE_PREFIX . "mod_security_groups.gid)
                        AND (" . $sWhere . ")

                        AND (
                                (vgallery_nodes.parent =  " . $db->toSql(ffCommon_dirname($record["full_path"]), "Text") . " AND vgallery_nodes.name =  " . $db->toSql(basename($record["full_path"]), "Text") . ") 
                            OR
                                (
                                    (vgallery_nodes.parent <> " . $db->toSql(ffCommon_dirname($record["full_path"]), "Text") . " OR vgallery_nodes.name <>  " . $db->toSql(basename($record["full_path"]), "Text") . ") 
                                AND
                                    (vgallery_nodes_rel_groups.mod & 4 = 0)
                                )
                            )
                    
                    ORDER  BY 
                        LENGTH(CONCAT(vgallery_nodes.parent, vgallery_nodes.name)) DESC
                        , vgallery_nodes_rel_groups.mod DESC

                    LIMIT 1 
                ) AS `write`
                , (
                    SELECT
                        IF(vgallery_nodes_rel_groups.mod & 4 = 0, 0, 4) AS result
                    FROM
                        vgallery_nodes INNER JOIN vgallery_nodes_rel_groups ON vgallery_nodes.ID = vgallery_nodes_rel_groups.ID_vgallery_nodes
                    WHERE 
                        vgallery_nodes_rel_groups.gid IN (" . CM_TABLE_PREFIX . "mod_security_groups.gid)
                        AND (" . $sWhere . ")

                        AND (
                                (vgallery_nodes.parent =  " . $db->toSql(ffCommon_dirname($record["full_path"]), "Text") . " AND vgallery_nodes.name =  " . $db->toSql(basename($record["full_path"]), "Text") . ") 
                            OR
                                (
                                    (vgallery_nodes.parent <> " . $db->toSql(ffCommon_dirname($record["full_path"]), "Text") . " OR vgallery_nodes.name <>  " . $db->toSql(basename($record["full_path"]), "Text") . ") 
                                AND
                                    (vgallery_nodes_rel_groups.mod & 4 = 0)
                                )
                            )
                    
                    ORDER  BY 
                        LENGTH(CONCAT(vgallery_nodes.parent, vgallery_nodes.name)) DESC
                        , vgallery_nodes_rel_groups.mod DESC

                    LIMIT 1 
                ) AS `not`
            FROM " . CM_TABLE_PREFIX . "mod_security_groups
            [WHERE] [ORDER]";
            
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "VGalleryPermissionGroups";
    $cm->oPage->addBounceComponent("VGalleryPermissionGroups");
    $oGrid->title = ffTemplate::_get_word_by_code("vgallery_permission_groups");
    $oGrid->source_SQL = $sSQL;
	$oGrid->include_all_records = true;
	
    $oGrid->order_default = "name";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/permission";
    $oGrid->record_id = "permission";
    $oGrid->use_search = false;
    $oGrid->use_paging = false;
    $oGrid->display_edit_bt = false;
    $oGrid->display_edit_url = false;
    $oGrid->display_delete_bt = false;
    $oGrid->display_new = false;
    $oGrid->buttons_options["export"]["display"] = false;


    $oField = ffField::factory($cm->oPage);
    $oField->id = "gid";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_vgallery_nodes";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_groups_name");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "read";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_groups_read");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    /*$oField->value = new ffData((check_mod($file_permission, 1, false, true)
                                            ? "1"
                                            : ""
                                        )    
                                    , "Number"); */
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "write";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_groups_write");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("2", "Number");
    /*$oField->value = new ffData((check_mod($file_permission, 2, false, true)
                                            ? "2"
                                            : ""
                                        )    
                                    , "Number"); */
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "not";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_groups_negative");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("4", "Number");
   /* $oField->value = new ffData((check_mod($file_permission, 4, false, true)
                                            ? "4"
                                            : ""
                                        )    
                                    , "Number"); */
    $oGrid->addContent($oField);
    
    $oRecord->addContent($oGrid);
    $cm->oPage->addContent($oGrid);
    
    //$cm->oPage->addContent($oGrid, "rel", null, array("title" => "Groups")); 
    //$cm->oPage->addContent("Not Avaible", "rel", null, array("title" => "Users")); 
}




   

// -------------------------
//          EVENTI
// -------------------------
 

  

function VGalleryNodesModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    switch($action) {
        case "insert":
            break;
        case "update":
        case "updatenext":
                if(is_array($component->parent[0]->bounce_components) && count($component->parent[0]->bounce_components)) {
                    foreach($component->parent[0]->bounce_components AS $bounce_value) {
                        if(is_array($component->parent[0]->components[$bounce_value]->recordset_values) && count($component->parent[0]->components[$bounce_value]->recordset_values)) {
                            foreach($component->parent[0]->components[$bounce_value]->recordset_values AS $rel_key => $rel_value) {
                                if(1/*$component->parent[0]->components[$bounce_value]->recordset_values[$rel_key]["read"] != $component->parent[0]->components[$bounce_value]->recordset_ori_values[$rel_key]["read"]
                                    || $component->parent[0]->components[$bounce_value]->recordset_values[$rel_key]["write"] != $component->parent[0]->components[$bounce_value]->recordset_ori_values[$rel_key]["write"]
                                    || $component->parent[0]->components[$bounce_value]->recordset_values[$rel_key]["not"] != $component->parent[0]->components[$bounce_value]->recordset_ori_values[$rel_key]["not"]
                                */) {
                                    $mod = $component->parent[0]->components[$bounce_value]->recordset_values[$rel_key]["read"] + $component->parent[0]->components[$bounce_value]->recordset_values[$rel_key]["write"] + $component->parent[0]->components[$bounce_value]->recordset_values[$rel_key]["not"];
                                    $sSQL = "SELECT ID
                                                FROM vgallery_nodes_rel_groups
                                                WHERE vgallery_nodes_rel_groups.gid = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["gid"], "Number") . "
                                                        AND vgallery_nodes_rel_groups.ID_vgallery_nodes = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["ID_vgallery_nodes"], "Number");
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $db->execute("UPDATE vgallery_nodes_rel_groups
                                                        SET vgallery_nodes_rel_groups.mod = " . $db->toSql($mod) . "
                                                        WHERE vgallery_nodes_rel_groups.gid = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["gid"], "Number") . "
                                                            AND vgallery_nodes_rel_groups.ID_vgallery_nodes = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["ID_vgallery_nodes"], "Number")
                                        );
                                    } else {
                                        $db->execute("INSERT INTO vgallery_nodes_rel_groups
                                                        (
                                                            `ID_vgallery_nodes`
                                                            , `gid`
                                                            , `mod`
                                                        )   
                                                        VALUES
                                                        (
                                                            " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["ID_vgallery_nodes"], "Number") . "
                                                            , " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["gid"], "Number") . "
                                                            , " . $db->toSql($mod) . "
                                                        )"
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            break;
        case "delete":
            break;
        case "confirmdelete":
            if(check_function("delete_vgallery")) {
                $db->query("SELECT vgallery.name AS vgallery_name
                            FROM vgallery_nodes 
                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                            WHERE vgallery_nodes.ID = " . $db->toSql($component->key_fields["ID"]->value)
                        );
                if($db->nextRecord()) {
                    $vgallery_name = $db->getField("vgallery_name", "Text")->getValue();
                    delete_vgallery($component->form_fields["parent"]->getValue(), $component->form_fields["name"]->getValue(), $vgallery_name);
                }
            }
            break;
    }
    return false;
}


function VGalleryNodesModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    
    if(strlen($action)) {
        if(check_function("get_schema_fields_by_type"))
            $src = get_schema_fields_by_type("vgallery");        
        
        $sSQL = "SELECT `" . $src["table"] . "`.* 
					, " . $src["sql"]["select"]["vgallery_name"] . " AS vgallery_name 
					, " . $src["sql"]["select"]["is_dir"] . " AS is_dir
                FROM `" . $src["table"] . "` 
                WHERE `" . $src["table"] . "`.ID = " . $db->tosql($component->key_fields["ID"]->value);
        $db->query($sSQL);
        if($db->nextRecord()) {
			$vgallery_name              = $db->getField("vgallery_name", "Text", true);
            $vgallery_parent_old        = $db->getField($src["field"]["parent"], "Text", true);
            $vgallery_name_old          = $db->getField($src["field"]["smart_url"], "Text", true);
            $vgallery_meta_title        = $db->getField($src["field"]["title"], "Text", true);
            $vgallery_meta_header       = $db->getField($src["field"]["header"], "Text", true);
            $vgallery_meta_description  = $db->getField($src["field"]["description"], "Text", true);

            $vgallery_meta_robots       = $db->getField($src["field"]["robots"], "Text", true);
            $vgallery_meta_canonical    = $db->getField($src["field"]["canonical"], "Text", true);
            $vgallery_meta              = $db->getField($src["field"]["meta"], "Text", true);
            $vgallery_httpstatus        = $db->getField($src["field"]["httpstatus"], "Number", true);
            
            $vgallery_is_dir            = $db->getField($src["field"]["is_dir"], "Number", true);
            $vgallery_type              = $db->getField("ID_type", "Number", true);
            $vgallery_visible           = $db->getField($src["field"]["visible"], "Number", true);
            $highlight                  = $db->getField("highlight", "Text", true);
            if($highlight)
                $highlight              = explode(",", $highlight);
            
            if($src["field"]["clone"])
            	$vgallery_is_clone		= $db->getField($src["field"]["clone"], "Number", true);
            
            $type                       = ($vgallery_is_dir ? "dir" : "node");
			$path                       = $vgallery_parent_old;
			$vgallery_permalink        	= $db->getField($src["field"]["permalink"], "Text", true);
			
            $vgallery_nodes_title .= ": " . stripslash($vgallery_parent_old) . "/" . $vgallery_name_old;
		}         
        
        $ID_vgallery = $component->additional_fields["ID_vgallery"]->getValue();
        $actual_path = $component->form_fields["parent"]->getValue();
        $item_name = $component->form_fields["name"]->getValue();
        $ID_node = $component->key_fields["ID"]->getValue();
        $ID_type = $component->form_fields["ID_type"]->getValue();

        if(check_function("refresh_cache")) {
	    	refresh_cache(
                "V"
                , $ID_node
                , "insert"
                , ($vgallery_permalink 
                    ? $vgallery_permalink
                    : stripslash($vgallery_parent_old) . "/" . $vgallery_name_old
                )
            );
		}  
	    switch($action) {
	        case "insert":
	            break;
	        case "update":
	            break;
	        case "updatenext":
	        case "delete":
	            break;
	        case "confirmdelete":
	            break;
	    }
	}
    return false;
}
