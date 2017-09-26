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

if (!AREA_SEO_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");
system_ffcomponent_resolve_by_path();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "GalleryModify";
$oRecord->resources[] = $oRecord->id;	
	
if(is_file(DISK_UPDIR . $_REQUEST["keys"]["permalink"])) {
	$db = ffDB_Sql::factory();

	$file_name = basename($_REQUEST["keys"]["permalink"]);
	$file_title = ucwords(str_replace("-", " " , basename($_REQUEST["keys"]["permalink"])));
	$file_path = ffCommon_dirname($_REQUEST["keys"]["permalink"]);
	
	$db->query("SELECT files.*
	                    FROM files 
	                    WHERE 
	                    files.parent = " . $db->tosql($file_path, "Text") . "
	                    AND files.name = " . $db->tosql($file_name, "Text"));
	if($db->nextRecord()) {
	    $_REQUEST["keys"]["ID"] = $db->getField("ID")->getValue();
	    $ID_files = $db->getField("ID")->getValue();
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
	                , " . $db->toSql($file_name) . "
	                , " . $db->toSql($file_path) . "
	                , '0'
	                , " . $db->toSql(time(), "Number") . "
	                , " . $db->toSql(time(), "Number") . "
	                , " . $db->toSql(get_session("UserNID"), "Number") . "
	            )";
	    $db->execute($sSQL);
	    $ID_files = $db->getInsertID(true);
	    $_REQUEST["keys"]["ID"] = $ID_files;
	}		

	$tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/contents/admin/media");
	$tpl->load_file("preview.html", "main");                              
	$tpl->set_var("container_class", cm_getClassByFrameworkCss(array(4), "col", "preview"));
	$tpl->set_var("download_class", cm_getClassByFrameworkCss("download", "icon")); 
	$tpl->set_var("modify_class", cm_getClassByFrameworkCss("crop", "icon")); 
	$tpl->set_var("media_path", $_REQUEST["keys"]["permalink"]); 
	$tpl->set_var("view_path", FF_SITE_PATH . "/cm/showfiles.php"); 
	$tpl->set_var("preview_path", FF_SITE_PATH . "/cm/showfiles.php". "/200x200"); 	
	if(check_function("get_literal_size"))
		$tpl->set_var("media_size", get_literal_size(filesize(FF_DISK_PATH . FF_UPDIR . $_REQUEST["keys"]["permalink"]))); 
	$tpl->set_var("media_time", time()); 
	$tpl->set_var("row_class", cm_getClassByFrameworkCss("row", "form")); 
	$tpl->set_var("control_class", cm_getClassByFrameworkCss("control", "form")); 
	$tpl->set_var("info_class", cm_getClassByFrameworkCss("info", "callout") . " " . cm_getClassByFrameworkCss("text-overflow", "util")); 

	$arrParentPath = explode("/", trim($file_path, "/"));
	if(is_array($arrParentPath) && count($arrParentPath)) {
		foreach($arrParentPath AS $arrParentPath_value) { 
		    $parent_title = ucwords(str_replace("-", " " , $arrParentPath_value));
		    $str_menu_parent_path .= ($str_menu_parent_path ? '<ul>' : '') . '<li class="' . cm_getClassByFrameworkCss(array("text-nowrap", "text-overflow"), "util") . '" title="' . $parent_title . '"><a href="javascript:void(0);"' . cm_getClassByFrameworkCss("folder-open", "icon-tag") . " " . $parent_title . '</a>';
		}
	}

	$tpl->set_var("media_tree_path", '<ul class="nopadding">' . $str_menu_parent_path . str_repeat("</li></ul>", substr_count($file_path, "/")));  
	$tpl->set_var("media_name", $file_title); 
	$tpl->set_var("media_name_normalized", $file_name); 
	$cm->oPage->addContent($tpl);  
	
	$oRecord->src_table = "files";
	$oRecord->buttons_options["delete"]["display"] = AREA_GALLERY_SHOW_DELETE;
	$oRecord->user_vars["path"] = $file_path;
	$oRecord->setWidthComponent(8);
	$oRecord->class = "nopadding";
	$oRecord->addEvent("on_do_action", "GalleryModify_on_do_action");
	$oRecord->addEvent("on_done_action", "GalleryModify_on_done_action");
	$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));	

	/**
	* Title
	*/
	if(check_function("system_ffcomponent_set_title")) {
		system_ffcomponent_set_title(
			$file_title
			, array(
				"name" => "image"
				, "type" => "content"
			)
			, false
			, false
			, $oRecord
		);
	}
	$labelWidth = array(2,3,5,5);	                
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oRecord->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->container_class = "hidden";
	$oField->label = ffTemplate::_get_word_by_code("gallery_modify_name");
	$oField->required = true;
	$oRecord->addContent($oField);
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "parent";         
    $oField->container_class = "hidden";
	$oField->label = ffTemplate::_get_word_by_code("gallery_modify_parent");
	$oField->widget = "actex";
	$oField->actex_update_from_db = true;
	$oField->source_SQL = "SELECT DISTINCT 
				                CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
				                , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
				            FROM files
				            WHERE
				                files.parent NOT LIKE '" . $db->toSql($file_path, "Text", false) . "%'
				                AND is_dir = '1'
				            ORDER BY full_path";
	$oField->required = true;
	$oRecord->addContent($oField);	*/
	
	$oDetail = ffDetails::factory($cm->oPage);
	if(check_function("get_locale")) {
		$arrLang = get_locale("lang", true);
		if(count($arrLang) > 1) {
		    $oDetail->tab = true;
    		$oDetail->tab_label = "language";
		}
	}
    $oDetail->id = "GalleryModifyDetail";
    $oDetail->src_table = "files_rel_languages";
    $oDetail->order_default = "ID";
    $oDetail->fields_relationship = array ("ID_files" => "ID");
    $oDetail->display_new = false;
    $oDetail->display_delete = false;
    $oDetail->auto_populate_insert = true;
    $oDetail->populate_insert_SQL = "SELECT 
                                    " . FF_PREFIX . "languages.ID 																AS ID_languages
                                    , " . FF_PREFIX . "languages.description 													AS language
                                    , '1' 																						AS `visible`
                                    FROM " . FF_PREFIX . "languages
                                    WHERE
                                        " . FF_PREFIX . "languages.status = '1'";
    $oDetail->auto_populate_edit = true;
    $oDetail->addEvent("on_do_action", "GalleryModifyDetail_on_do_action");
    $oDetail->populate_edit_SQL = "SELECT 
                                        files_rel_languages.ID 																	AS ID
                                        , " . FF_PREFIX . "languages.ID 														AS ID_languages
                                        , " . FF_PREFIX . "languages.description 												AS language
                                        , " . FF_PREFIX . "languages.code 														AS code_lang
                                        , '1' 																					AS visible
                                        , IF(ISNULL(files_rel_languages.alias) || files_rel_languages.alias = ''
                                        	, '" . ffGetFilename($file_name) . " - " . str_replace("/", " " , trim($file_path, "/")) . "'
                                        	, files_rel_languages.alias
                                        ) 																						AS alias
                                        , IF(ISNULL(files_rel_languages.description) || files_rel_languages.description = ''
                                        	, '" . ucwords(str_replace("/", " " , trim($file_path, "/"))) . " " . ffGetFilename($file_title) . "'
                                        	, files_rel_languages.description
                                        ) 																						AS description
                                        , IF(ISNULL(files_rel_languages.alt_url), '', files_rel_languages.alt_url) 				AS alt_url
                                    FROM " . FF_PREFIX . "languages
                                        LEFT JOIN files_rel_languages ON  files_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND files_rel_languages.ID_files = [ID_FATHER]
                                    WHERE
                                        " . FF_PREFIX . "languages.status = '1'";

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
	/*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "visible";
    $oField->label = ffTemplate::_get_word_by_code("gallery_field_visible");
    //$oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->unchecked_value = new ffData("0");
    $oField->checked_value = new ffData("1");
    $oField->default_value = new ffData("1");
    $oDetail->addContent($oField);*/

	$oField = ffField::factory($cm->oPage);
	$oField->id = "alt_url";
	$oField->label = ffTemplate::_get_word_by_code("gallery_field_alt_url");
	$oDetail->addContent($oField);

    $oRecord->addContent($oDetail);
    $cm->oPage->addContent($oDetail);
} else {
	$oRecord->hide_all_controls = true;
	if(check_function("process_html_page_error"))
		$oRecord->fixed_pre_content = process_html_page_error(404);
}

$cm->oPage->addContent($oRecord);
  

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
        
        if(check_function("refresh_cache")) {
        	refresh_cache("G", md5(stripslash($actual_path) . "/" . $item_name), $action, stripslash($actual_path) . "/" . $item_name);				
		}
    
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
