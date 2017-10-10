<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_PUBLISHING_SHOW_PERMISSION && ENABLE_STD_PERMISSION)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$ID_publishing = $_REQUEST["keys"]["ID"];

if($ID_publishing > 0) {
    //$cm->oPage->addContent(null, true, "rel"); 
    $sSQL = "SELECT 
                *
                , " . $db_gallery->tosql($ID_publishing, "Number") . " AS ID_publishing
                , (
                    SELECT
                        IF(publishing_rel_groups.mod & 1 = 0, 0, 1) AS result
                    FROM
                        publishing 
                        INNER JOIN publishing_rel_groups ON publishing.ID = publishing_rel_groups.ID_publishing
                    WHERE 
                        publishing_rel_groups.gid IN (" . CM_TABLE_PREFIX . "mod_security_groups.gid)
                        AND (" . $sWhere . ")

                        AND (
                                (vgallery_nodes.parent =  " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " AND vgallery_nodes.name =  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                            OR
                                (
                                    (vgallery_nodes.parent <> " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " OR vgallery_nodes.name <>  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
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
                                (vgallery_nodes.parent =  " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " AND vgallery_nodes.name =  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                            OR
                                (
                                    (vgallery_nodes.parent <> " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " OR vgallery_nodes.name <>  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
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
                                (vgallery_nodes.parent =  " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " AND vgallery_nodes.name =  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                            OR
                                (
                                    (vgallery_nodes.parent <> " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " OR vgallery_nodes.name <>  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
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
    
    $cm->oPage->addContent($oGrid);
    
    //$cm->oPage->addContent($oGrid, "rel", null, array("title" => "Groups")); 
    //$cm->oPage->addContent("Not Avaible", "rel", null, array("title" => "Users")); 
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGalleryNodesModify";
$oRecord->resources[] = $oRecord->id;
//$oRecord->resources_get = $oRecord->resources; 
$oRecord->title = ffTemplate::_get_word_by_code("vgallery_" . $vgallery_name . "_title");
$oRecord->src_table = "vgallery_nodes";
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false;

$oRecord->addEvent("on_do_action", "VGalleryNodesModify_on_do_action");
$oRecord->addEvent("on_done_action", "VGalleryNodesModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_nodes_modify_name");
$oField->control_type = "label";
$oField->store_in_db = false;
//$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "parent";
$oField->label = ffTemplate::_get_word_by_code("vgallery_nodes_modify_parent");
$oField->extended_type = "Selection";
if($type == "node") {
    $oField->source_SQL = "SELECT DISTINCT 
                                CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
                                , CONCAT('/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)) AS display_path
                            FROM vgallery_nodes
                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                            WHERE
                                (vgallery_nodes.is_dir > 0)
                                AND vgallery_nodes.name <> ''
                                AND vgallery.ID = " . $db_gallery->toSql($ID_vgallery, "Number") . "
                            ORDER BY full_path";
 } elseif($type == "dir") {
    $oField->source_SQL = "SELECT DISTINCT 
                                CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
                                , CONCAT('/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LOCATE(CONCAT('/', vgallery.name), CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) + LENGTH(vgallery.name) + 2)) AS display_path
                            FROM vgallery_nodes
                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                            WHERE
                                (vgallery_nodes.is_dir > 0)
                                AND vgallery_nodes.name <> ''
                                AND vgallery.ID = " . $db_gallery->toSql($ID_vgallery, "Number") . "
                            HAVING
                            (
                                LENGTH(IF(display_path = '/', display_path, CONCAT(display_path, '/'))) - LENGTH(REPLACE(IF(display_path = '/', display_path, CONCAT(display_path, '/')), '/', '')) < " . $db_gallery->toSql($limit_level, "Number") . "
                            )
                            ORDER BY full_path";
}
$oField->default_value = new ffData($path, "Text");
$oField->control_type = "label";
$oField->multi_select_one = false;
$oField->store_in_db = false;
//$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_type";
$oField->label = ffTemplate::_get_word_by_code("vgallery_nodes_modify_ID_type");
$oField->extended_type = "Selection";

$sSQL_dir = "SELECT ID, name 
                FROM vgallery_type 
                WHERE " . (OLD_VGALLERY
                        ? "vgallery_type.name <> 'system'"
                        : "1"
                    ) . "
                    AND name = 'Directory'
                ORDER BY name";

$sSQL_node = "SELECT ID, name 
                FROM vgallery_type 
                WHERE " . (OLD_VGALLERY
                        ? "vgallery_type.name <> 'system'"
                        : "1"
                    ) . "
                AND name <> 'Directory'
                " . ($limit_type
                        ? " AND vgallery_type.ID IN(" . $db_gallery->tosql($limit_type, "Text", false) . ") " 
                        : ""
                    ) ."
                ORDER BY name";

if($type == "node") {
	$oField->source_SQL = "(" . $sSQL_node . ") UNION (" . $sSQL_dir . ")";
} elseif($type == "dir") {
	$oField->source_SQL = "(" . $sSQL_dir  . ") UNION (" . $sSQL_node . ")";
}

$oField->multi_select_one = false;
$oField->control_type = "label";
$oField->store_in_db = false;
//$oField->required = true;
$oRecord->addContent($oField);

$oRecord->additional_fields = array("ID_vgallery" => new ffData($ID_vgallery, "Number")
                                    , "last_update" =>  new ffData(time(), "Number")
                                    );

if($ID_vgallery_nodes > 0 && !$cm->oPage->isXHR()) {
	$oRecord->buttons_options["update"]["label"] = ffTemplate::_get_word_by_code("vgallery_nodes_updateback");
	$oRecord->buttons_options["update"]["index"] = 3;

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "updatenext"; 
	$oButton->label = ffTemplate::_get_word_by_code("vgallery_nodes_updatenext");
	$oButton->action_type = "submit";
	$oButton->frmAction = "updatenext";
        $oButton->aspect = "link";
	$oRecord->addActionButton($oButton, 2);
	
	$oRecord->default_actions[$oButton->id] = "update";
}

$oRecord->addContent($oGrid);
$cm->oPage->addContent($oRecord);


   

// -------------------------
//          EVENTI
// -------------------------
 

  

function VGalleryNodesModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    switch($action) {
        case "insert":
            $db->query("SELECT * 
                        FROM vgallery_nodes 
                        WHERE vgallery_nodes.parent = " . $db->toSql($component->form_fields["parent"]->value) . "
                            AND vgallery_nodes.name = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue()))
                    );
            if($db->nextRecord()) {
                $component->tplDisplayError(ffTemplate::_get_word_by_code("name_not_unic"));
                return true;
            } else {
                $component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
            }
            break;
        case "update":
        case "updatenext":
                $db->query("SELECT * 
                            FROM vgallery_nodes 
                            WHERE vgallery_nodes.parent = " . $db->toSql($component->form_fields["parent"]->value) . "
                                AND vgallery_nodes.name = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . "
                                AND vgallery_nodes.ID <> " . $db->toSql($component->key_fields["ID"]->value)
                        );
                if($db->nextRecord()) {
                    $component->tplDisplayError(ffTemplate::_get_word_by_code("name_not_unic"));
                    return true;
                } else {
                    $component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()));
                    
                    $old_parent = stripslash($component->form_fields["parent"]->value_ori->getValue()) . "/" . $component->form_fields["name"]->value_ori->getValue();
                    $new_parent = stripslash($component->form_fields["parent"]->value->getValue()) . "/" . $component->form_fields["name"]->value->getValue();

		            $cache = get_session("cache");
					if(isset($cache["auth"][$old_parent])) {
						unset($cache["auth"][$old_parent]);
					}

		            set_session("cache", $cache);
		                                
                    $db->execute("UPDATE vgallery_nodes 
                                SET vgallery_nodes.parent = REPLACE(vgallery_nodes.parent, " . $db->toSql($old_parent)  . ", " . $db->toSql($new_parent) . ")
                                WHERE
                                (vgallery_nodes.parent = " . $db->toSql($old_parent)  . " 
                                	OR vgallery_nodes.parent LIKE '" . $db->toSql($old_parent, "Text", false)  . "/%'
                                )"
                            );
					
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
        $ID_node = $component->key_fields["ID"]->getValue();
        $sSQL = "SELECT publishing.*
                    FROM publishing
                    WHERE publishing.ID = " . $db->toSql($ID_node, "Number");
        $db->query($sSQL);
        if($db->nextRecord())
        {
            $publishing_area = $db->getField("area", "Text", true);
        }        

		if(check_function("refresh_cache")) {
        	refresh_cache_get_blocks_by_layout($publishing_area . "_" . $ID_node);
		}        
        
	    switch($action) {
	        case "insert":
	        case "update":
	           // if(check_function("update_vgallery_models"))
	            //	update_vgallery_models($action, $ID_vgallery, $ID_node, $vgallery_name, $actual_path, $item_name);
	            break;
	        case "updatenext":
	        	//if(check_function("update_vgallery_models"))
	           // 	update_vgallery_models($action, $ID_vgallery, $ID_node, $vgallery_name, $actual_path, $item_name);
		        $menu_edit = array(
		                            array("properties" => "/properties")
		                            , "modify" => "/modify"
		                            , "relationship" => "/relationship"
		                            , "seo" => "/seo"
		                        );
	            foreach($menu_edit AS $edit_key => $edit_value) {
					if(is_array($edit_value) && count($edit_value)) {
			            foreach($edit_value AS $edit_sub_key => $edit_sub_value) {
		            		if(constant("AREA_" . strtoupper($edit_sub_key) . "_SHOW_MODIFY")) {
								$next = $edit_sub_value;
								break;
							}
						}
						if(strlen($next))
							break;
					} else {
		                if(constant("AREA_VGALLERY_SHOW_" . strtoupper($edit_key))) {
							$next = $edit_value;
							break;
						}
					}
	            }

	            $vgallery_name = $_REQUEST["vname"];
	            $type = $_REQUEST["type"];
	            $path = $_REQUEST["path"];
	            $ret_url = $_REQUEST["ret_url"];

	            ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . $next . "?keys[ID]=" . $ID_node . "&type=" . urlencode($type) . "&vname=" . urlencode($vgallery_name) . "&path=" . urlencode($path) . "&extype=vgallery_nodes&ret_url=" . urlencode($ret_url));
	        case "delete":
	            break;
	        case "confirmdelete":
	            break;
	    }
	}
    return false;
}
?>
