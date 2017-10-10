<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if(isset($_REQUEST["path"]))
	$path = stripslash(urldecode($_REQUEST["path"]));
else
	$path = $cm->real_path_info;

if($path == "")
    $path = "/";

$is_owner = false;
if (!AREA_GALLERY_SHOW_MODIFY) {
    if(is_file(DISK_UPDIR . $path)) {
		if(ENABLE_STD_PERMISSION) {
    		if(check_function("get_file_permission"))
    			$file_permission = get_file_permission(ffCommon_dirname($path));
    		if($file_permission["owner"] > 0 && $file_permission["owner"] === get_session("UserNID")) {
    			use_cache(false);
    			$is_owner = true;
			} else {
    			ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");	
			}
		} else {
			$owner = $_REQUEST["owner"];
			if($owner == get_session("UserNID")) {
    			use_cache(false);
    			$is_owner = true;
			} else {
    			ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");	
			}
		}
	} else {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	}
}  

$gallery_title = ffTemplate::_get_word_by_code("modify_gallery") . ": " . basename($path);
		
if(strpos($cm->oPage->page_path, VG_SITE_GALLERY) === 0) {
	$simple_interface = true;
} else {
	$simple_interface = false;
}

if(is_file(FF_DISK_PATH . FF_UPDIR . $path)) {
    $real_file = $path;
} elseif(is_file(FF_DISK_PATH . FF_UPDIR . "/tmp" . $path)) {
    $real_file = "/tmp" . $path;
}

if(!$real_file) //da fare l'addnew
    ffErrorHandler::raise("Resouce Missing", E_USER_ERROR, null, get_defined_vars());
    
$db_gallery->query("SELECT files.*
                    FROM files 
                    WHERE 
                    files.parent = " . $db_gallery->tosql(ffCommon_dirname($path), "Text") . "
                    AND files.name = " . $db_gallery->tosql(basename($path), "Text"));
if($db_gallery->nextRecord()) {
    $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID")->getValue();
    $ID_files = $db_gallery->getField("ID")->getValue();
} else {
    $sSQL = "INSERT INTO files
            (
                ID
                , name
                , parent
                , is_dir
                , created
                , last_update
                , owner
            )
            VALUES
            (
                null
                , " . $db_gallery->toSql(basename($path)) . "
                , " . $db_gallery->toSql(ffCommon_dirname($path)) . "
                , '0'
                , " . $db_gallery->toSql(time(), "Number") . "
                , " . $db_gallery->toSql(time(), "Number") . "
                , " . $db_gallery->toSql(get_session("UserNID"), "Number") . "
            )";
    $db_gallery->execute($sSQL);
    $ID_files = $db_gallery->getInsertID(true);
    $_REQUEST["keys"]["ID"] = $ID_files;
}

$tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/contents/admin/media");
$tpl->load_file("preview.html", "main");                              
$tpl->set_var("container_class", cm_getClassByFrameworkCss(array(4), "col", "preview"));
$tpl->set_var("download_class", cm_getClassByFrameworkCss("download", "icon")); 
$tpl->set_var("modify_class", cm_getClassByFrameworkCss("crop", "icon")); 
$tpl->set_var("media_path", $real_file); 
$tpl->set_var("view_path", CM_SHOWFILES); 
$tpl->set_var("preview_path", CM_SHOWFILES . "/200x200"); 
if(check_function("get_literal_size"))
	$tpl->set_var("media_size", get_literal_size(filesize(FF_DISK_PATH . FF_UPDIR . $real_file))); 
$tpl->set_var("media_time", time()); 
$tpl->set_var("row_class", cm_getClassByFrameworkCss("row", "form")); 
$tpl->set_var("control_class", cm_getClassByFrameworkCss("control", "form")); 
$tpl->set_var("info_class", cm_getClassByFrameworkCss("info", "callout") . " " . cm_getClassByFrameworkCss("text-overflow", "util")); 


$parent_path = ffCommon_dirname($path);
$arrParentPath = explode("/", trim($parent_path, "/"));
if(is_array($arrParentPath) && count($arrParentPath)) {
    foreach($arrParentPath AS $arrParentPath_value) { 
        $parent_title = ucwords(str_replace("-", " " , $arrParentPath_value));
        $str_menu_parent_path .= ($str_menu_parent_path ? '<ul>' : '') . '<li class="' . cm_getClassByFrameworkCss(array("text-nowrap", "text-overflow"), "util") . '" title="' . $parent_title . '"><a href="javascript:void(0);"' . cm_getClassByFrameworkCss("folder-open", "icon-tag") . " " . $parent_title . '</a>';
    }
}
//$tpl->set_var("media_path", $parent_path); 

$tpl->set_var("media_tree_path", '<ul class="nopadding">' . $str_menu_parent_path . str_repeat("</li></ul>", substr_count($parent_path, "/")));  


$tpl->set_var("media_name", basename($path)); 
$tpl->set_var("media_name_normalized", ffCommon_url_rewrite(basename($path))); 


$cm->oPage->addContent($tpl);  

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "GalleryModify";
$oRecord->resources[] = $oRecord->id;
//$oRecord->title = ffTemplate::_get_word_by_code("gallery_title");
$oRecord->src_table = "files";
$oRecord->buttons_options["delete"]["display"] = AREA_GALLERY_SHOW_DELETE;
//$oRecord->buttons_options["update"]["label"] = ffTemplate::_get_word_by_code("gallery_nodes_updateback");
//$oRecord->buttons_options["update"]["index"] = 3;
$oRecord->buttons_options["print"]["display"] = false;

$oRecord->user_vars["path"] = ffCommon_dirname($path);
/* Title Block */
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . cm_getClassByFrameworkCss("vg-gallery", "icon-tag", array("2x", "content")) . $gallery_title . '</h1>';
$oRecord->setWidthComponent(8);
$oRecord->class = "nopadding";

$oRecord->addEvent("on_do_action", "GalleryModify_on_do_action");
$oRecord->addEvent("on_done_action", "GalleryModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "hidden";
$oField->label = ffTemplate::_get_word_by_code("gallery_modify_name");
if($path == "/" && $_REQUEST["keys"]["ID"]) {
	$oField->control_type = "label";
} else {
	$oField->required = true;
} 
$oRecord->addContent($oField);

if(!$simple_interface) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "parent";         
    $oField->container_class = "hidden";
	$oField->label = ffTemplate::_get_word_by_code("gallery_modify_parent");
	if($_REQUEST["keys"]["ID"]) {
		$oField->control_type = "label";
	} else {
		$oField->extended_type = "Selection";
		$oField->source_SQL = "SELECT DISTINCT 
				                    CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
				                    , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
				                FROM files
				                WHERE
				                    files.parent NOT LIKE '" . $db_gallery->toSql($path, "Text", false) . "%'
				                    AND is_dir = '1'
				                ORDER BY full_path";
		$oField->required = true;
	}
	$oRecord->addContent($oField);
} 
                     /*
$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("gallery_modify_order");
$oField->base_type = "Number";
$oRecord->addContent($oField); */


$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);



if(strlen($ID_files)) {
	$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
	    $count_lang = $db_gallery->getField("count_lang", "Number", true);
	}
	$oDetail = ffDetails::factory($cm->oPage);
	if($count_lang > 1) {
	    $oDetail->tab = true;
	    $oDetail->tab_label = "language";
	}
    $oDetail->id = "GalleryModifyDetail";
    //$oDetail->title = ffTemplate::_get_word_by_code("gallery_field_title");
    $oDetail->src_table = "files_rel_languages";
    $oDetail->order_default = "ID";
    $oDetail->fields_relationship = array ("ID_files" => "ID");
    $oDetail->display_new = false;
    $oDetail->display_delete = false;
    $oDetail->auto_populate_insert = true;
    $oDetail->populate_insert_SQL = "SELECT 
                                    " . $db_gallery->toSql($ID_files, "Number") . " AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , " . FF_PREFIX . "languages.code AS code_lang
                                    , '1' AS `visible`
                                    FROM " . FF_PREFIX . "languages
                                    WHERE
                                        " . FF_PREFIX . "languages.status = '1'";
    $oDetail->auto_populate_edit = true;
    $oDetail->addEvent("on_do_action", "GalleryModifyDetail_on_do_action");
    $oDetail->populate_edit_SQL = "SELECT 
                                        files_rel_languages.ID AS ID
                                        , " . FF_PREFIX . "languages.ID AS ID_languages
                                        , " . FF_PREFIX . "languages.description AS language
                                        , " . FF_PREFIX . "languages.code AS code_lang
                                        , IF(ISNULL(files_rel_languages.visible), '1', files_rel_languages.visible) AS visible
                                        , IF(ISNULL(files_rel_languages.alias), '', files_rel_languages.alias) AS alias
                                        , IF(ISNULL(files_rel_languages.description), '', files_rel_languages.description) AS description
                                        , IF(ISNULL(files_rel_languages.alt_url), '', files_rel_languages.alt_url) AS alt_url
                                    FROM " . FF_PREFIX . "languages
                                        LEFT JOIN files_rel_languages ON  files_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND files_rel_languages.ID_files = " . $db_gallery->toSql($ID_files, "Number") . "
                                    WHERE
                                        " . FF_PREFIX . "languages.status = '1'
                                    ";

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oDetail->addKeyField($oField);  

    $oField = ffField::factory($cm->oPage);
    $oField->id = "language";
    $oField->label = ffTemplate::_get_word_by_code("gallery_field_languages");
    $oField->store_in_db = false;
    $oDetail->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_languages";
    $oField->label = ffTemplate::_get_word_by_code("gallery_field_ID_languages");
    $oField->base_type = "Number";
    $oField->required = true;
    $oDetail->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "code_lang";
    $oField->label = ffTemplate::_get_word_by_code("gallery_field_code_lang");
    $oField->store_in_db = false;
    $oDetail->addHiddenField($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "alias";
    $oField->label = ffTemplate::_get_word_by_code("gallery_field_alias");
    $oDetail->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "description";
    $oField->label = ffTemplate::_get_word_by_code("gallery_field_description");
    $oField->extended_type = "Text";
    $oDetail->addContent($oField);

    if(!$simple_interface) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "visible";
        $oField->label = ffTemplate::_get_word_by_code("gallery_field_visible");
        //$oField->base_type = "Number";
        $oField->extended_type = "Boolean";
        $oField->unchecked_value = new ffData("0");
        $oField->checked_value = new ffData("1");
        $oField->default_value = new ffData("1");
        $oDetail->addContent($oField);
    } else {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "visible";
        $oField->default_value = new ffData("1");
        $oDetail->addHiddenField($oField);    
    }
        
    if(!$simple_interface) {
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "alt_url";
	    $oField->label = ffTemplate::_get_word_by_code("gallery_field_alt_url");
	    /*if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
	        $oField->widget = "ckeditor";
	    } else {
	        $oField->widget = "";
	    }*/
	    //$oField->ckeditor_group_by_auth = true;
	    $oDetail->addContent($oField);
	}

    $oRecord->addContent($oDetail);
    $cm->oPage->addContent($oDetail);
}






    

// -------------------------
//          EVENTI
// -------------------------

function GalleryModifyDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory(); 
    
    switch($action) {
        case "insert":
        case "update":
            if(isset($component->main_record[0]->form_fields["parent"])) {
            	$actual_path = $component->main_record[0]->form_fields["parent"]->value->getValue();
            	$actual_path_old = $component->main_record[0]->form_fields["parent"]->value_ori->getValue();
			} else {
            	$actual_path = $component->main_record[0]->user_vars["path"];
            	$actual_path_old = $component->main_record[0]->user_vars["path"];
			}
            
        	$item_name = $component->main_record[0]->form_fields["name"]->value->getValue();
        	$item_name_old = $component->main_record[0]->form_fields["name"]->value_ori->getValue();
        	
        	$new_parent = stripslash($actual_path) . "/" . $item_name;
        	$old_parent = stripslash($actual_path_old) . "/" . $item_name_old;

            if(is_array($component->recordset) && count($component->recordset)) {
                foreach($component->recordset AS $rst_key => $rst_value) {
                    $smart_url = $component->recordset[$rst_key]["alias"]->getValue();
                    $meta_title = $component->recordset[$rst_key]["alias"]->getValue();

                    if(strlen($smart_url) || strlen($meta_title)) {
                        $sSQL = "SELECT 
                                    IF(smart_url = '', 1, 0) AS isset_smart_url
                                    , IF(meta_title = '', 1, 0) AS isset_meta_title
                                FROM files_rel_languages 
                                WHERE 
                                    `ID` = " . $db->toSql($component->recordset[$rst_key]["ID"]);
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $isset_smart_url = $db->getField("isset_smart_url")->getValue();
                            $isset_meta_title = $db->getField("isset_meta_title")->getValue();
                        }                        
                        
                        if($isset_smart_url) {
                            //Inserisce/Aggiorna lo Smart_url e meta_title
                            $sSQL = "SELECT * 
                                    FROM files_rel_languages 
                                    WHERE 
                                        `smart_url` = " . $db->toSql(ffCommon_url_rewrite(strip_tags($smart_url))) . " 
                                        AND `ID` <> " . $db->toSql($component->recordset[$rst_key]["ID"]) . "
                                        AND `ID_languages` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
                            $db->query($sSQL);
                            if($db->nextRecord()) {
                                $sSQL = "SELECT * 
                                        FROM files_rel_languages 
                                        WHERE 
                                            `smart_url` = '" . $db->toSql(ffCommon_url_rewrite(strip_tags($smart_url)) . "-", "Text", false) . "%' 
                                            AND `ID` <> " . $db->toSql($component->recordset[$rst_key]["ID"]) . "
                                            AND `ID_languages` = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
                                $db->query($sSQL);
                                $smart_url .= "-" . $db->numRows() + 1;
                            }
                            
                            $sSQL = "SELECT ID
                                        FROM `files_rel_languages` 
                                        WHERE `ID` = " . $db->toSql($component->recordset[$rst_key]["ID"]);
                            $db->query($sSQL);
                            if($db->nextRecord()) {
                                $sSQL = "UPDATE 
                                    `files_rel_languages` 
                                SET 
                                    `smart_url` = " . $db->toSql(ffCommon_url_rewrite(strip_tags($smart_url))) . " 
                                WHERE 
                                    `ID` = " . $db->toSql($component->recordset[$rst_key]["ID"]);
                                $db->execute($sSQL);
                            } else {
                                $sSQL = "INSERT INTO  
                                            `files_rel_languages` 
                                        ( 
                                            `ID` , 
                                            `smart_url` ,
                                            `ID_files` , 
                                            `ID_languages`,
                                            `visible`
                                            
                                        )
                                        VALUES
                                        (
                                            ''
                                            , " . $db->toSql(ffCommon_url_rewrite(strip_tags($smart_url))) . " 
                                            , " . $db->toSql($component->main_record[0]->key_fields["ID"]) . " 
                                            , " . $db->toSql($component->recordset[$rst_key]["ID_languages"]) . " 
                                            , " . $db->toSql("1", "Number") . " 
                                        )";
                                $db->execute($sSQL);
                            }
                        }
                        if($isset_meta_title) {
                            $sSQL = "SELECT ID
                                        FROM `files_rel_languages`
                                        WHERE `ID` = " . $db->toSql($component->recordset[$rst_key]["ID"]);
                            $db->query($sSQL);
                            if($db->nextRecord()) {
                                $sSQL = "UPDATE 
                                    `files_rel_languages` 
                                SET 
                                    `meta_title` = " . $db->toSql(strip_tags($meta_title)) . " 
                                WHERE 
                                    `ID` = " . $db->toSql($component->recordset[$rst_key]["ID"]);
                                $db->execute($sSQL);
                            } else {
                                $sSQL = "INSERT INTO  
                                            `files_rel_languages` 
                                        ( 
                                            `ID` , 
                                             `meta_title` ,
                                            `ID_files` , 
                                            `ID_languages`,
                                            `visible`
                                        )
                                        VALUES
                                        (
                                            ''
                                            , " . $db->toSql(strip_tags($meta_title)) . " 
                                            , " . $db->toSql($component->main_record[0]->key_fields["ID"]) . " 
                                            , " . $db->toSql($component->recordset[$rst_key]["ID_languages"]) . " 
                                            , " . $db->toSql("1", "Number") . " 
                                        )";
                                $db->execute($sSQL); 
                            }
                        }
                    }
					if(check_function("refresh_cache")) {
						refresh_cache("G", md5($old_parent), $action, $old_parent);
					}
                }
            }
        //ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());  
        break;
    } 
}


function GalleryModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
    //        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
        $new_name = ffCommon_url_rewrite(ffGetFilename($component->form_fields["name"]->getValue())) 
                    . (pathinfo($component->form_fields["name"]->getValue(), PATHINFO_EXTENSION)
                        ? "." . ffCommon_url_rewrite(pathinfo($component->form_fields["name"]->getValue(), PATHINFO_EXTENSION))
                        : ""
                    );

        if(isset($component->form_fields["parent"])) {
            $actual_path = $component->form_fields["parent"]->value->getValue();
            $actual_path_old = $component->form_fields["parent"]->value_ori->getValue();
		} else {
            $actual_path = $component->user_vars["path"];
            $actual_path_old = $component->user_vars["path"];
		}

        switch($action) {
            case "insert":
                $db->query("SELECT * 
                            FROM files 
                            WHERE files.parent = " . $db->toSql($actual_path) . "
                                AND files.name = " . $db->toSql($new_name)
                        );
                if($db->nextRecord()) {
                    $component->tplDisplayError(ffTemplate::_get_word_by_code("name_not_unic"));
                    return true;
                } else {
                    $component->form_fields["name"]->setValue($new_name);
                }
                break;
            case "update":
                    if(is_file(DISK_UPDIR . stripslash($actual_path_old) . "/" . $component->form_fields["name"]->value_ori->getValue()) || is_dir(DISK_UPDIR . stripslash($actual_path_old) . "/" . $component->form_fields["name"]->value_ori->getValue())) {
                        $db->query("SELECT * 
                                    FROM files 
                                    WHERE files.parent = " . $db->toSql($actual_path) . "
                                        AND files.name = " . $db->toSql($new_name) . "
                                        AND files.ID <> " . $db->toSql($component->key_fields["ID"]->value)
                                );
                        if($db->nextRecord()) {
                            $component->tplDisplayError(ffTemplate::_get_word_by_code("name_not_unic"));
                            return true;
                        } else {
                            $component->form_fields["name"]->setValue($new_name);
                            
                            $old_parent = stripslash($actual_path_old) . "/" . $component->form_fields["name"]->value_ori->getValue();
                            $new_parent = stripslash($actual_path) . "/" . $component->form_fields["name"]->value->getValue();

                            if($old_parent != $new_parent) {
	                            if(@rename(DISK_UPDIR . $old_parent, DISK_UPDIR . $new_parent)) {
	                                $db->execute("UPDATE vgallery_rel_nodes_fields 
	                                            SET vgallery_rel_nodes_fields.description = REPLACE(vgallery_rel_nodes_fields.description, " . $db->toSql($old_parent)  . ", " . $db->toSql($new_parent) . ")
	                                            WHERE vgallery_rel_nodes_fields.description LIKE '%" . $db->toSql($old_parent, "Text", false)  . "%'"
	                                        );
	                            } else {
	                                $component->tplDisplayError(ffTemplate::_get_word_by_code("unable_rename_item"));
	                                return true;
	                            }
							}
                        }
                    } else {
                       // $component->tplDisplayError(ffTemplate::_get_word_by_code("item_not_exist"));
                       // return true;
                    }
                break;
            case "delete":
                break;
            case "confirmdelete":
				if($component->key_fields["ID"]->getValue() > 0) {
					$db->query("SELECT CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
								FROM files 
								WHERE files.ID = " . $db->toSql($component->key_fields["ID"]->value)
							);
					if($db->nextRecord()) {
                        $full_path = $db->getField("full_path", "Text", true);
                        if($full_path && ffCommon_dirname($full_path) != $full_path && check_function("fs_operation"))
						    purge_dir(DISK_UPDIR . $full_path, $full_path, true);
					}
				}
                break;
        } 
        return false;
    }
    return true;
}


function GalleryModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());
    
    if(strlen($action)) {
        if(isset($component->form_fields["parent"])) {
            $actual_path = $component->form_fields["parent"]->value->getValue();
            $actual_path_old = $component->form_fields["parent"]->value_ori->getValue();
		} else {
            $actual_path = $component->user_vars["path"];
            $actual_path_old = $component->user_vars["path"];
		}

        $item_name = $component->form_fields["name"]->getValue();
        $ID_node = $component->key_fields["ID"]->getValue();
		
		$new_parent = stripslash($actual_path) . "/" . $item_name;
		/*
		 $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = " . $db->toSql(time()) . " 
                ";
        $db->execute($sSQL);*/
        
        if(check_function("refresh_cache")) {
        	refresh_cache("G", md5(stripslash($actual_path) . "/" . $item_name), $action, stripslash($actual_path) . "/" . $item_name);				
		}
        //UPDATE CACHE
       /* $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `files`.last_update FROM files WHERE files.ID = " . $db->toSql($ID_node, "Number") . ") 
                WHERE 
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("GALLERY") . ")
                    )
                    OR
                    (
                        LOCATE(layout.value, " . $db->toSql(stripslash($actual_path) . "/" . $item_name) . ") > 0
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("GALLERY_MENU") . ")
                    )
                    OR
                    (
                        REPLACE(layout.value, " . $db->toSql("gallery") . ", '') = (SELECT publishing.ID FROM publishing WHERE publishing.contest  = " . $db->toSql("files") . " AND LOCATE(publishing.relative_path, " . $db->toSql(stripslash($actual_path) . "/" . $item_name) . ") > 0 )
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("PUBLISHING") . ")
                    )
                    ";
        $db->execute($sSQL);*/
        //UPDATE CACHE 
    
	    switch($action) { 
	        case "insert":
	            $path = $_REQUEST["path"];
	            $ret_url = $_REQUEST["ret_url"];

	            ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "/modify?path=" . urlencode($path) . "&ret_url=" . urlencode($ret_url));
	            break;
	        case "update":
	            break;
	        case "delete":
	            break;
	        case "confirmdelete":
	            break;
	    }
	    
	    if(($action == "insert" || $action == "update") && check_function("set_field_permalink"))
			set_field_permalink("files", $component->key_fields["ID"]->getValue());

	}    

    return true;
}
?>
