<?php
if (!AREA_GROUPS_SHOW_MODIFY) {
    FormsDialog(false, "OkOnly", ffTemplate::_get_word_by_code("dialog_title_accessdenied"), ffTemplate::_get_word_by_code("dialog_description_invalidpath"), "", $site_path . "/", THEME_INSET);
}

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$gid = $_REQUEST["keys"]["gid"];

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "GroupModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("groups_modify");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_security_groups";

$oField = ffField::factory($cm->oPage);
$oField->id = "gid";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("groups_name");
$oField->id = "name";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "registration";
$oField->label = ffTemplate::_get_word_by_code("groups_registration");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("disabled"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("enabled")))
                       );
$oField->multi_select_one = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("groups_level");
$oField->id = "level";
$oField->base_type = "Number";
$oRecord->addContent($oField);


$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_groups_fields.* 
			, extended_type.name AS extended_type
		FROM " . CM_TABLE_PREFIX . "mod_security_groups_fields
			INNER JOIN extended_type ON extended_type.ID = " . CM_TABLE_PREFIX . "mod_security_groups_fields.ID_extended_type
		WHERE " . CM_TABLE_PREFIX . "mod_security_groups_fields.ID_groups = " . $db_gallery->toSql($gid, "Number") . "
		ORDER BY " . CM_TABLE_PREFIX . "mod_security_groups_fields.`order`, " . CM_TABLE_PREFIX . "mod_security_groups_fields.field";
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
	$oRecord->addEvent("on_done_action", "GroupModify_on_done_action");
	$oRecord->addContent(null, true, "settings"); 
	$oRecord->groups["settings"] = array(
		                                     "title" => ffTemplate::_get_word_by_code("groups_settings")
		                                     , "cols" => 1
		                                  );
	$db_selection = ffDB_Sql::factory();
	do {
        $field_name = $db_gallery->getField("field")->getValue();
        $field_id = $db_gallery->getField("ID")->getValue();
        
        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = $field_id;
        $obj_page_field->label = ffTemplate::_get_word_by_code($field_name);
        $obj_page_field->user_vars["name"] = $field_name;
        $obj_page_field->data_type = "";
        $obj_page_field->store_in_db = false;  
        
        $writable = $db_gallery->getField("writable")->getValue();

        $selection_value = array();        
        
        switch($db_gallery->getField("extended_type")->getValue())
        {
            case "Selection":
            case "Option":
                $obj_page_field->base_type = "Text";

                if($writable) {
                    if($db_gallery->getField("extended_type")->getValue() == "Option") {
                        $obj_page_field->control_type = "radio";
                        $obj_page_field->extended_type = "Selection";
                        $obj_page_field->widget = "";
                    } else {
                        $obj_page_field->control_type = "combo";
                        $obj_page_field->extended_type = "Selection";
                        //$obj_page_field->widget = "activecomboex";
                        //$obj_page_field->actex_update_from_db = true;
                    }
                } else {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "label";
                }
                
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $db_selection->query("
                                    SELECT DISTINCT nameID, name
                                    FROM 
                                    (
                                        (
                                            SELECT 
                                                vgallery_rel_nodes_fields.description AS nameID
                                                , vgallery_rel_nodes_fields.description  AS name
                                                , vgallery_fields.`order_backoffice` AS `order`
                                           FROM vgallery_rel_nodes_fields
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                           WHERE 
                                                vgallery_fields.ID = " . $db_gallery->toSql($db_gallery->getField("ID_field")) . " 
                                                AND " . FF_PREFIX . "languages.code = ". $db_gallery->toSql(LANGUAGE_INSET, "Text") . "
                                       )
                                    ) AS tbl_src
                                    ORDER BY tbl_src.`order`, tbl_src.name");
/*                                              Non ha senso non esiste $db_gallery->getField("ID_selection", "Number"))
                                       ) UNION (
                                           SELECT
                                                module_form_fields_selection_value.name AS nameID
                                                , module_form_fields_selection_value.name AS name
                                                , module_form_fields_selection_value.`order` AS `order`
                                               FROM module_form_fields_selection 
                                                    INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
                                               WHERE module_form_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
*/
                                    
	            if($db_selection->nextRecord()) {
	                do {
	                    $selection_value[] = array(new ffData($db_selection->getField("nameID")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("name")->getValue())));
	                } while($db_selection->nextRecord());
	            }
				
				$obj_page_field->multi_pairs = $selection_value;
				$obj_page_field->encode_entities = false;
                
                $type_value = "Text";
                break;
            case "Group":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Selection";
                $obj_page_field->control_type = "input";

                if(!$writable)
                    $obj_page_field->properties["disabled"] = "disabled";
                    
                $obj_page_field->widget = "checkgroup";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = ";";

                $db_selection->query("
                                    SELECT DISTINCT nameID, name
                                    FROM 
                                    (
                                        (
                                            SELECT 
                                                vgallery_rel_nodes_fields.description AS nameID
                                                , vgallery_rel_nodes_fields.description  AS name
                                                , vgallery_fields.`order_backoffice` AS `order`
                                           FROM vgallery_rel_nodes_fields
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                                INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                           WHERE 
                                                vgallery_fields.ID = " . $db_gallery->toSql($db_gallery->getField("ID_field")) . " 
                                                AND " . FF_PREFIX . "languages.code = ". $db_gallery->toSql(LANGUAGE_INSET, "Text") . "
                                       )
                                    ) AS tbl_src
                                    ORDER BY tbl_src.`order`, tbl_src.name");
/*                                         Non ha senso non esiste $db_gallery->getField("ID_selection", "Number"))
                                       ) UNION (
                                           SELECT
                                                module_form_fields_selection_value.name AS nameID
                                                , module_form_fields_selection_value.name AS name
                                                , module_form_fields_selection_value.`order` AS `order`
                                               FROM module_form_fields_selection_value 
                                                    INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
                                               WHERE module_form_fields_selection_value.ID_selection = " . $db_gallery->toSql($db_gallery->getField("ID_selection", "Number")) . "
*/
                                    
                if($db_selection->nextRecord()) {
                    do {
                        $selection_value[] = array(new ffData($db_selection->getField("name")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("name")->getValue())));
                    } while($db_selection->nextRecord());
                }

                $obj_page_field->multi_pairs = $selection_value;
                $obj_page_field->encode_entities = false;

                $type_value = "Text";
                break;
            case "Text":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";
                
                if(!$writable) {
                  $obj_page_field->default_value = new ffData(get_word_by_code("form_" . preg_replace('/[^a-zA-Z0-9]/', '',  $field_name) . "_text_" . $oRecord->user_vars["MD_chk"]["params"][0]), "Text");
                  $obj_page_field->properties["readonly"] = "readonly";
                }
                    
                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "TextBB":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";

                if($writable) {
                    $obj_page_field->control_type = "textarea";
                    if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/tiny_mce/tiny_mce.js")) {
                        $obj_page_field->widget = "tiny_mce";
                    } else {
                        $obj_page_field->widget = "";
                    }
                } else {
                    $obj_page_field->control_type = "label";
                    $obj_page_field->widget = "";
                }

                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "TextCK":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";

                if($writable) {
                    $obj_page_field->control_type = "textarea";
                    if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
                        $obj_page_field->widget = "ckeditor";
                    } else {
                        $obj_page_field->widget = "";
                    }
                    $obj_page_field->ckeditor_group_by_auth = true;
                } else {
                    $obj_page_field->control_type = "label";
                    $obj_page_field->widget = "";
                }

                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;
                
            case "Boolean":
                $obj_page_field->base_type = "Number";
                $obj_page_field->extended_type = "Boolean";
                $obj_page_field->control_type = "checkbox";

                if(!$writable)
                    $obj_page_field->properties["disabled"] = "disabled";

                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData("0", "Number");
                $obj_page_field->checked_value = new ffData("1", "Number");
                $obj_page_field->grouping_separator = "";
                $type_value = "Number";
                break;

            case "Date":
                $obj_page_field->base_type = "Date";
                $obj_page_field->extended_type = "Date";
                
                if($writable) {
                    $obj_page_field->control_type = "input";
                    $obj_page_field->widget = "datepicker";
                } else {
                    $obj_page_field->control_type = "label";
                    $obj_page_field->widget = "";
                }
                
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Date";
                break;

            case "DateCombo":
                $obj_page_field->base_type = "Date";
                $obj_page_field->extended_type = "Date";
                
                if($writable) {
                    $obj_page_field->control_type = "input";
                    $obj_page_field->widget = "datechooser";
                } else {
                    $obj_page_field->control_type = "label";
                    $obj_page_field->widget = "";
                }
                
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Date";
                break;

            case "Image":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "File";

                $obj_page_field->file_storing_path = DISK_UPDIR . "/users/[register-ID_VALUE]";
                $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
                $obj_page_field->file_max_size = MAX_UPLOAD;

                $obj_page_field->file_show_filename = true; 
                $obj_page_field->file_full_path = false;
                $obj_page_field->file_check_exist = false;
                $obj_page_field->file_normalize = true;
                 
                $obj_page_field->file_show_preview = true;
                $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[register-ID_VALUE]/[_FILENAME_]";
                $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[register-ID_VALUE]/[_FILENAME_]";
                $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[_FILENAME_]";
                $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[_FILENAME_]";

                if($writable) {
                    $obj_page_field->control_type = "file";

                    $obj_page_field->file_show_delete = true;
                    $obj_page_field->file_writable = false;
                    
                    $obj_page_field->widget = "kcfinder"; 
					if(check_function("set_field_uploader")) { 
						$obj_page_field = set_field_uploader($obj_page_field);
					}
                } else {
                    $obj_page_field->control_type = "picture_no_link";

                    $obj_page_field->file_show_delete = false;
                    $obj_page_field->file_writable = false;

                    $obj_page_field->widget = "";
                }

                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "Upload":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "File";

                $obj_page_field->file_storing_path = DISK_UPDIR . "/users/[register-ID_VALUE]";
                $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
                $obj_page_field->file_max_size = MAX_UPLOAD;

                $obj_page_field->file_show_filename = true; 
                $obj_page_field->file_full_path = false;
                $obj_page_field->file_check_exist = false;
                $obj_page_field->file_normalize = true;
                 
                $obj_page_field->file_show_preview = true;
                $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[register-ID_VALUE]/[_FILENAME_]";
                $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[register-ID_VALUE]/[_FILENAME_]";
                $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[_FILENAME_]";
                $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[_FILENAME_]";

                if($writable) {
                    $obj_page_field->control_type = "file";

                    $obj_page_field->file_show_delete = true;
                    $obj_page_field->file_writable = false;
                    
                    $obj_page_field->widget = "uploadify";
					if(check_function("set_field_uploader")) { 
						$obj_page_field = set_field_uploader($obj_page_field);
					}
                } else {
                    $obj_page_field->control_type = "picture_no_link";

                    $obj_page_field->file_show_delete = false;
                    $obj_page_field->file_writable = false;

                    $obj_page_field->widget = "";
                }

                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "UploadImage":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "File";

                $obj_page_field->file_storing_path = DISK_UPDIR . "/users/[register-ID_VALUE]";
                $obj_page_field->file_temp_path = DISK_UPDIR . "/users";
                $obj_page_field->file_max_size = MAX_UPLOAD;

                $obj_page_field->file_show_filename = true; 
                $obj_page_field->file_full_path = false;
                $obj_page_field->file_check_exist = false;
                $obj_page_field->file_normalize = true;
                 
                $obj_page_field->file_show_preview = true;
                $obj_page_field->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[register-ID_VALUE]/[_FILENAME_]";
                $obj_page_field->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[register-ID_VALUE]/[_FILENAME_]";
                $obj_page_field->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/users/[_FILENAME_]";
                $obj_page_field->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/avatar/users/[_FILENAME_]";

                if($writable) {
                    $obj_page_field->control_type = "file";

                    $obj_page_field->file_show_delete = true;
                    $obj_page_field->file_writable = false;
                    
                    $obj_page_field->widget = "kcuploadify"; 
					if(check_function("set_field_uploader")) { 
						$obj_page_field = set_field_uploader($obj_page_field);
					}
                } else {
                    $obj_page_field->control_type = "picture_no_link";

                    $obj_page_field->file_show_delete = false;
                    $obj_page_field->file_writable = false;

                    $obj_page_field->widget = "";
                }

                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "Number":
                $obj_page_field->base_type = "Number";
                $obj_page_field->extended_type = "";

                if($writable) 
                    $obj_page_field->control_type = "input";
                else
                    $obj_page_field->control_type = "label";

                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData(""); 
                $obj_page_field->checked_value = new ffData(""); 
                $obj_page_field->grouping_separator = "";
                $type_value = "Number";
                break;

                
            default: // String
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";

                if($writable) 
                    $obj_page_field->control_type = "input";
                else
                    $obj_page_field->control_type = "label";

                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
        }
		
		$obj_page_field->default_value = $db_gallery->getField("value", $type_value);

        $oRecord->addContent($obj_page_field, "settings");
	} while($db_gallery->nextRecord());
}
$cm->oPage->addContent($oRecord);

function GroupModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();  

    if($action == "insert" || $action == "update") {
        $gid = $component->key_fields["gid"]->getValue();

		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_groups_fields.*
					FROM " . CM_TABLE_PREFIX . "mod_security_groups_fields
                    WHERE " . CM_TABLE_PREFIX . "mod_security_groups_fields.ID_groups = " . $db->toSql($gid, "Number");
		$db->query($sSQL);
		if($db->nextRecord())
		{
			do {
				$arrGroupsField[$db->getField("field", "Text", true)] = 0;
			} while ($db->nextRecord());
		}
		
        foreach($component->form_fields AS $field_key => $field_value) {
            if($field_value->store_in_db == false && isset($field_value->user_vars["name"]) && strlen($field_value->user_vars["name"])) {
				if(is_array($arrGroupsField) && array_key_exists($field_value->user_vars["name"], $arrGroupsField))
				{
					$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_groups_fields
								SET " . CM_TABLE_PREFIX . "mod_security_groups_fields.value =  " . $db->toSql($field_value->value) . "
							WHERE " . CM_TABLE_PREFIX . "mod_security_groups_fields.ID_groups = " . $db->toSql($gid, "Number") . " 
							AND " . CM_TABLE_PREFIX . "mod_security_groups_fields.field = " . $db->toSql($field_value->user_vars["name"]);
					$db->execute($sSQL);
				} else
				{
                    $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_groups_fields
                            (
                                ID
                                , ID_groups
                                , field
                                , value
                            )
                            VALUES
                            (
                                null
                                , " . $db->toSql($gid, "Number") . "
                                , " . $db->toSql($field_value->user_vars["name"]) . "
                                , " . $db->toSql($field_value->value) . "
                            )";
                    $db->execute($sSQL); 
                }
            }
        }
	}
}
?>