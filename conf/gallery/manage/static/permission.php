<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_STATIC_SHOW_PERMISSION && Cms::env("ENABLE_STD_PERMISSION"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$ID_static_pages = $_REQUEST["keys"]["ID"];
$path = urldecode($_REQUEST["path"]);

$db_gallery->query("SELECT 
                        CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS full_path
                        , static_pages.ID
                    FROM static_pages
                    WHERE 
                        static_pages.ID = " . $db_gallery->tosql($ID_static_pages, "Number"));
if($db_gallery->nextRecord()) {
    $ID_static_pages = $db_gallery->getField("ID")->getValue();
    $full_path =  $db_gallery->getField("full_path")->getValue();
}
//if(check_function("get_file_permission"))
	//$file_permission = get_file_permission($full_path, "static_pages");

if(strlen($ID_static_pages)) {
    //$cm->oPage->addContent(null, true, "rel"); 

    $sWhere = "";
    $src_path = $full_path;
    do {
        $src_folder_name = basename($src_path);
        $src_folder_path = ffCommon_dirname($src_path);
        if (strlen($sWhere))
            $sWhere .= " OR ";
        $sWhere .= " (static_pages.parent = " . $db_gallery->toSql($src_folder_path, "Text") . " AND static_pages.name = " . $db_gallery->toSql($src_folder_name, "Text") . " )";
    } while($src_folder_name != "" && $src_path = ffCommon_dirname($src_path));
    

    
    $sSQL = "SELECT 
                *
                , " . $db_gallery->tosql($ID_static_pages, "Number") . " AS ID_static_pages
                , (
                    SELECT
                        IF(static_pages_rel_groups.mod & 1 = 0, 0, 1) AS result
                    FROM
                        static_pages INNER JOIN static_pages_rel_groups ON static_pages.ID = static_pages_rel_groups.ID_static_pages
                    WHERE 
                        static_pages_rel_groups.gid IN (" . CM_TABLE_PREFIX . "mod_security_groups.gid)
						" . ($globals->ID_domain > 0
							? " AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number")
							: ""
						) . "
                        AND (" . $sWhere . ")

                        AND (
                                (static_pages.parent =  " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " AND static_pages.name =  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                            OR
                                (
                                    (static_pages.parent <> " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " OR static_pages.name <>  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                                AND
                                    (static_pages_rel_groups.mod & 4 = 0)
                                )
                            )
                    
                    ORDER  BY 
                        LENGTH(CONCAT(static_pages.parent, static_pages.name)) DESC
                        , static_pages_rel_groups.mod DESC

                    LIMIT 1 
                ) AS `read`
                , (
                    SELECT
                        IF(static_pages_rel_groups.mod & 2 = 0, 0, 2) AS result
                    FROM
                        static_pages INNER JOIN static_pages_rel_groups ON static_pages.ID = static_pages_rel_groups.ID_static_pages
                    WHERE 
                        static_pages_rel_groups.gid IN (" . CM_TABLE_PREFIX . "mod_security_groups.gid)
						" . ($globals->ID_domain > 0
							? " AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number")
							: ""
						) . "
                        AND (" . $sWhere . ")

                        AND (
                                (static_pages.parent =  " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " AND static_pages.name =  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                            OR
                                (
                                    (static_pages.parent <> " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " OR static_pages.name <>  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                                AND
                                    (static_pages_rel_groups.mod & 4 = 0)
                                )
                            )
                    
                    ORDER  BY 
                        LENGTH(CONCAT(static_pages.parent, static_pages.name)) DESC
                        , static_pages_rel_groups.mod DESC

                    LIMIT 1 
                ) AS `write`
                , (
                    SELECT
                        IF(static_pages_rel_groups.mod & 4 = 0, 0, 4) AS result
                    FROM
                        static_pages INNER JOIN static_pages_rel_groups ON static_pages.ID = static_pages_rel_groups.ID_static_pages
                    WHERE 
                        static_pages_rel_groups.gid IN (" . CM_TABLE_PREFIX . "mod_security_groups.gid)
						" . ($globals->ID_domain > 0
							? " AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number")
							: ""
						) . "
                        AND (" . $sWhere . ")

                        AND (
                                (static_pages.parent =  " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " AND static_pages.name =  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                            OR
                                (
                                    (static_pages.parent <> " . $db_gallery->toSql(ffCommon_dirname($full_path), "Text") . " OR static_pages.name <>  " . $db_gallery->toSql(basename($full_path), "Text") . ") 
                                AND
                                    (static_pages_rel_groups.mod & 4 = 0)
                                )
                            )
                    
                    ORDER  BY 
                        LENGTH(CONCAT(static_pages.parent, static_pages.name)) DESC
                        , static_pages_rel_groups.mod DESC

                    LIMIT 1 
                ) AS `not`
            FROM " . CM_TABLE_PREFIX . "mod_security_groups
            [WHERE] [ORDER]";
            
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "StaticPermissionGroups";
    $cm->oPage->addBounceComponent("StaticPermissionGroups");
    $oGrid->title = ffTemplate::_get_word_by_code("static_permission_groups");
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


    $oField = ffField::factory($cm->oPage);
    $oField->id = "gid";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_static_pages";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("admin_static_groups_name");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "read";
    $oField->label = ffTemplate::_get_word_by_code("admin_static_groups_read");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    /*$oField->value = new ffData((check_mod($file_permission, 1, false, true)
                                            ? "1"
                                            : ""
                                        )    
                                    , "Number");*/ 
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "write";
    $oField->label = ffTemplate::_get_word_by_code("admin_static_groups_write");
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
    $oField->label = ffTemplate::_get_word_by_code("admin_static_groups_negative");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("4", "Number");
    /*$oField->value = new ffData((check_mod($file_permission, 4, false, true)
                                            ? "4"
                                            : ""
                                        )    
                                    , "Number");*/ 
    $oGrid->addContent($oField);
    
    $cm->oPage->addContent($oGrid);
    
    //$cm->oPage->addContent($oGrid, "rel", null, array("title" => "Groups")); 
    //$cm->oPage->addContent("Not Avaible", "rel", null, array("title" => "Users")); 
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "StaticModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("static_page_title");
$oRecord->src_table = "static_pages";
$oRecord->addEvent("on_do_action", "StaticModify_on_do_action");
$oRecord->addEvent("on_done_action", "StaticModify_on_done_action");
$oRecord->allow_delete     = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("static_page_name");
$oField->control_type = "label";
$oRecord->addContent($oField);
         
$oField = ffField::factory($cm->oPage);
$oField->id = "parent";
$oField->label = ffTemplate::_get_word_by_code("static_page_parent");
$oField->extended_type = "Selection";
$oField->source_SQL = " SELECT
						IF(static_pages.parent = '/', CONCAT( static_pages.parent, static_pages.name ), CONCAT( static_pages.parent, '/', static_pages.name )) AS ID,
						IF(static_pages.name = '', 'home', CONCAT( 'home', IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name )) AS name
						FROM static_pages 
                        WHERE 1
							" . ($globals->ID_domain > 0
								? " AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number")
								: ""
							) . "
                        ORDER BY name";
$oField->control_type = "label";
$oRecord->addContent($oField);


$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number")
                                    );

$cm->oPage->addContent($oRecord);


   

// -------------------------
//          EVENTI
// -------------------------
 

  

function StaticModify_on_do_action($component, $action) {
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
    	$ID_node = $component->key_fields["ID"]->getValue();
    	
    	 switch($action) {
            case "insert":
                break;
            case "update":
            	/*
            	$old_parent = stripslash($component->form_fields["parent"]->value_ori->getValue()) . "/" . $component->user_vars["old_name"];
                if(!strlen($component->user_vars["name"])) {
					$component->user_vars["name"] = ffCommon_url_rewrite($component->detail["StaticModifyLanguages"]->recordset[0]["title"]->getValue());
                }

                $new_parent = stripslash($component->form_fields["parent"]->value->getValue()) . "/" . $component->user_vars["name"];

                if($old_parent != $new_parent) {
                    $sSQL = "SELECT * FROM static_pages WHERE IF(static_pages.parent = '/', CONCAT( static_pages.parent, static_pages.name ), CONCAT( static_pages.parent, '/', static_pages.name )) = " . $db->toSql($new_parent, "Text");
                    $db->query($sSQL);
                    if($db->numRows()) {
                        $component->tplDisplayError(ffTemplate::_get_word_by_code("element_not_unic"));
                        return true;
                    }
                }*/

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
                                                FROM static_pages_rel_groups
                                                WHERE static_pages_rel_groups.gid = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["gid"], "Number") . "
                                                        AND static_pages_rel_groups.ID_static_pages = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["ID_static_pages"], "Number");
                                    $db->query($sSQL);
                                    if($db->nextRecord()) {
                                        $db->execute("UPDATE static_pages_rel_groups
                                                        SET static_pages_rel_groups.mod = " . $db->toSql($mod) . "
                                                        WHERE static_pages_rel_groups.gid = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["gid"], "Number") . "
                                                            AND static_pages_rel_groups.ID_static_pages = " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["ID_static_pages"], "Number")
                                        );
                                    } else {
                                        $db->execute("INSERT INTO static_pages_rel_groups
                                                        (
                                                            `ID_static_pages`
                                                            , `gid`
                                                            , `mod`
                                                        )   
                                                        VALUES
                                                        (
                                                            " . $db->toSql($component->parent[0]->components[$bounce_value]->recordset_keys[$rel_key]["ID_static_pages"], "Number") . "
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
            case "confirmdelete":
				$sSQL = "SELECT static_pages.ID_drafts
							, drafts.name AS draft_name 
						FROM static_pages 
							INNER JOIN drafts ON drafts.ID = static_pages.ID_drafts
						WHERE static_pages.ID = " . $db->toSql($ID_node);
				$db->query($sSQL);
				if($db->nextRecord()) {
					$ID_draft = $db->getField("ID_drafts", "Number", true);
					$draft_name = $db->getField("draft_name", "Text", true);
					
					$sSQL = "DELETE FROM drafts_rel_languages WHERE ID_drafts = " . $db->toSql($ID_draft, "Number");
					$db->execute($sSQL);

					$sSQL = "DELETE FROM drafts WHERE ID = " . $db->toSql($ID_draft, "Number");
					$db->execute($sSQL);
					
					$sSQL = "SELECT layout.*
							FROM layout
							WHERE layout.value = " . $db->toSql($ID_draft) . "
								AND layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = " . $db->toSql("STATIC_PAGE_BY_DB") . ")
								AND layout.ID_location = (SELECT layout_location.ID FROM layout_location WHERE layout_location.name = " . $db->toSql("Content") . ")
							";
					$db->query($sSQL);
					if($db->nextRecord()) {
						$ID_layout = $db->getField("ID", "Number", true);

						$sSQL = "DELETE FROM layout_path WHERE ID_layout = " . $db->toSql($ID_layout, "Number");
						$db->execute($sSQL);

						$sSQL = "DELETE FROM layout WHERE ID = " . $db->toSql($ID_layout, "Number");
						$db->execute($sSQL);
						
					}
					
				}
          	default:  
		 }
    	
    	
	}
    //ffErrorHandler::raise($component->form_fields["name"]->value_ori->getValue(), E_USER_ERROR, null, get_defined_vars());
//    $component->form_fields["name"]->setValue(ffCommon_url_rewrite($component->form_fields["name"]->getValue()), "Text");
}


function StaticModify_on_done_action($component, $action) {
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    
    if(strlen($action)) {
        $ID_node = $component->key_fields["ID"]->getValue();

        switch($action) {
            case "insert":
                break;
            case "update":
                $old_parent = stripslash($component->form_fields["parent"]->value_ori->getValue()) . "/" . $component->user_vars["old_name"];
                $new_parent = stripslash($component->form_fields["parent"]->value->getValue()) . "/" . $component->user_vars["name"];

                if($old_parent != $new_parent) {
                    $sSQL = "UPDATE static_pages SET static_pages.parent = REPLACE(static_pages.parent, " . $db->toSql($old_parent, "Text") . ", " . $db->toSql($new_parent, "Text") . ") 
                    		WHERE static_pages.parent LIKE '" . $db->toSql($old_parent, "Text", false) . "%'
							" . ($globals->ID_domain > 0
								? " AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number")
								: ""
							);
                    $db->execute($sSQL);
                    
                    $sSQL = "UPDATE layout_path  SET layout_path.path = REPLACE(layout_path.path, " . $db->toSql($old_parent, "Text") . ", " . $db->toSql($new_parent, "Text") . ") 
                    		WHERE layout_path.path LIKE '" . $db->toSql($old_parent, "Text", false) . "%'
							" . ($globals->ID_domain > 0
								? " AND layout_path.ID_layout IN(
                    				SELECT layout.ID
                    				FROM layout
                    				WHERE 1								
										AND layout.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
								)"
								: ""
							);
                    $db->execute($sSQL);
                }
                break;
            case "confirmdelete":
            	$old_parent = stripslash($component->form_fields["parent"]->value_ori->getValue()) . "/" . $component->user_vars["old_name"];
            	break;
            default:
        }
		
        //UPDATE CACHE
        /*$sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `static_pages`.last_update FROM static_pages WHERE static_pages.ID = " . $db->toSql($ID_node, "Number") . ") 
                WHERE 
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("STATIC_PAGES_MENU") . ")
                    )
                    ";
        $db->execute($sSQL);*/
        //UPDATE CACHE 
    }
}
?>
