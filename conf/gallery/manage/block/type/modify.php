<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
   
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "LayoutTypeModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("layout_type_modify_title");
$oRecord->src_table = "layout_type";
$oRecord->addEvent("on_done_action", "LayoutTypeModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "layout";
$oField->base_type = "Number";
$oField->value = new ffData("0", "Number");
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_name");
$oField->control_type = "label";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_description");
$oField->extended_type = "Text";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "frequency";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_frequency");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("always"), new ffData(ffTemplate::_get_word_by_code("always"))),
                            array(new ffData("hourly"), new ffData(ffTemplate::_get_word_by_code("hourly"))),
                            array(new ffData("daily"), new ffData(ffTemplate::_get_word_by_code("daily"))),
                            array(new ffData("weekly"), new ffData(ffTemplate::_get_word_by_code("weekly"))),
                            array(new ffData("monthly"), new ffData(ffTemplate::_get_word_by_code("monthly"))),
                            array(new ffData("yearly"), new ffData(ffTemplate::_get_word_by_code("yearly"))),
                            array(new ffData("never"), new ffData(ffTemplate::_get_word_by_code("never")))
                       ); 
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);   



$oRecord->addTab("LayoutSettingsDetail");
$oRecord->setTabTitle("LayoutSettingsDetail", ffTemplate::_get_word_by_code("layout_settings_detail_title"));

$oRecord->addContent(null, true, "LayoutSettingsDetail"); 
$oRecord->groups["LayoutSettingsDetail"] = array(
			                     "title" => ffTemplate::_get_word_by_code("layout_settings_detail_title")
			                     , "cols" => 1
			                     , "tab" => "LayoutSettingsDetail"
			                  );            

$oDetail_settings = ffDetails::factory($cm->oPage);
$oDetail_settings->id = "LayoutSettingsDetail";
$oDetail_settings->title = ffTemplate::_get_word_by_code("layout_settings_detail_title");
$oDetail_settings->src_table = "layout_settings_rel";
$oDetail_settings->order_default = "ID";
$oDetail_settings->addEvent("on_before_process_row", "LayoutSettingsDetail_on_before_process_row"); 
$oDetail_settings->display_delete = false;
$oDetail_settings->display_new = false;
$oDetail_settings->fields_relationship = array ("ID_layout" => "layout");

$oDetail_settings->auto_populate_insert = false;
$oDetail_settings->populate_insert_SQL = "SELECT 
                                            layout_settings.ID AS ID_layout_settings
                                            , '' AS value
                                         FROM 
                                            layout_settings
                                            INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type AND layout_type.ID = [ID_FATHER]
                                         ORDER BY layout_settings.name
                                         ";
$oDetail_settings->auto_populate_edit = true;
$oDetail_settings->populate_edit_SQL = "
                                    SELECT 
                                            (
                                                SELECT ID
                                                FROM layout_settings_rel
                                                WHERE layout_settings_rel.ID_layout_settings = layout_settings.ID
                                                AND layout_settings_rel.ID_layout = 0
                                            ) 
                                            AS ID
                                            , 0 AS ID_layout
                                            , layout_settings.ID AS ID_layout_settings
                                            , layout_settings.name AS layout_settings
                                            , IF(layout_settings.description = '', layout_settings.name, layout_settings.description) AS layout_settings_description
                                            , 
                                                (
                                                    SELECT value
                                                    FROM layout_settings_rel
                                                    WHERE layout_settings_rel.ID_layout_settings = layout_settings.ID
                                                    AND layout_settings_rel.ID_layout = 0
                                                ) 
                                            AS value
                                            , extended_type.name AS extended_type
                                         FROM 
                                            layout_settings
                                            INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type AND layout_type.ID = [ID_FATHER]
                                            INNER JOIN extended_type ON extended_type.ID = layout_settings.ID_extended_type
                                         ORDER BY layout_settings.name
                                         ";
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail_settings->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_layout_settings";
$oDetail_settings->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = "";
$oDetail_settings->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "layout_settings";
$oField->store_in_db = false;
$oDetail_settings->addHiddenField($oField);
                 
$oField = ffField::factory($cm->oPage);
$oField->id = "layout_settings_description";
$oField->store_in_db = false;
$oDetail_settings->addHiddenField($oField);
                 
$oField = ffField::factory($cm->oPage);
$oField->id = "extended_type";
$oField->store_in_db = false;
$oDetail_settings->addHiddenField($oField);

$oRecord->addContent($oDetail_settings, "LayoutSettingsDetail");
$cm->oPage->addContent($oDetail_settings);


$oRecord->addTab("LayoutTypePlugin");
$oRecord->setTabTitle("LayoutTypePlugin", ffTemplate::_get_word_by_code("layout_type_modify_plugin_title"));

$oRecord->addContent(null, true, "LayoutTypePlugin"); 
$oRecord->groups["LayoutTypePlugin"] = array(
			                     "title" => ffTemplate::_get_word_by_code("layout_type_modify_plugin_title")
			                     , "cols" => 1
			                     , "tab" => "LayoutTypePlugin"
			                  );    

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "LayoutTypePlugin";
$oDetail->title = ffTemplate::_get_word_by_code("layout_type_modify_plugin_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = "layout_type_plugin";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_layout_type" => "ID");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);
	
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_js";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_plugin_ID_js");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;

$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/js/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=JsModify_confirmdelete";
$oField->resources[] = "JsModify";

$oField->source_SQL = "SELECT ID, name FROM js WHERE js.type = 'plugin' ORDER BY js.name";
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit_ext_type";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_plugin_limit_ext_type");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT DISTINCT ID, name FROM extended_type ORDER BY name";
$oField->control_type = "input";
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_plugin_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no"))),
                            array(new ffData("image"), new ffData(ffTemplate::_get_word_by_code("image"))),
                            array(new ffData("content"), new ffData(ffTemplate::_get_word_by_code("content")))
                       ); 
//$oField->required = true;
$oDetail->addContent($oField);

$oRecord->addContent($oDetail, "LayoutTypePlugin");
$cm->oPage->addContent($oDetail);
// -------------------------
//          EVENTI
// -------------------------

function LayoutSettingsDetail_on_before_process_row($component, $record) {

//    ffErrorHandler::raise("asd", E_USER_ERROR, NULL, get_defined_vars())   ;
	$db_selection = ffDB_Sql::factory();
    $type = $component->main_record[0]->key_fields["ID"]->getValue();
    //if(check_function("get_layout_settings"))
    	$layout_settings = Cms::getPackage($type); //get_layout_settings(NULL, $type);
   
    $obj_page_field = $component->form_fields["value"];
    $obj_page_field->label = $record["layout_settings_description"]->getValue();

    switch($record["extended_type"]->getValue())
    {
        case "Selection":
        case "Option":
            $obj_page_field->base_type = "Text";

            if($record["extended_type"]->getValue() == "Option") {
                $obj_page_field->control_type = "radio";
                $obj_page_field->extended_type = "Selection";
                $obj_page_field->widget = "";
			} else {
	            $obj_page_field->control_type = "combo";
	            $obj_page_field->extended_type = "Selection";
	            //$obj_page_field->widget = "activecomboex";
	            //$obj_page_field->actex_update_from_db = true;
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
                                       FROM vgallery_rel_nodes_fields
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                            INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                       WHERE 
                                            vgallery_fields.ID = " . $db_modules->toSql($db_modules->getField("ID_field")) . " 
                                            AND " . FF_PREFIX . "languages.code = ". $db_modules->toSql(LANGUAGE_INSET, "Text") . "
                                   ) UNION (
                                       SELECT
                                            module_form_fields_selection_value.name AS nameID
                                            , module_form_fields_selection_value.name AS name
                                           FROM module_form_fields_selection_value 
                                                INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
                                           WHERE module_form_fields_selection_value.ID_selection = " . $db_modules->toSql($db_modules->getField("ID_selection", "Number")) . "
                                   )
                                ) AS tbl_src
                                ORDER BY tbl_src.name");
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
                                       FROM vgallery_rel_nodes_fields
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                            INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                       WHERE 
                                            vgallery_fields.ID = " . $db_modules->toSql($db_modules->getField("ID_field")) . " 
                                            AND " . FF_PREFIX . "languages.code = ". $db_modules->toSql(LANGUAGE_INSET, "Text") . "
                                   ) UNION (
                                       SELECT
                                            module_form_fields_selection_value.name AS nameID
                                            , module_form_fields_selection_value.name AS name
                                           FROM module_form_fields_selection_value 
                                                INNER JOIN module_form_fields_selection ON module_form_fields_selection.ID = module_form_fields_selection_value.ID_selection
                                           WHERE module_form_fields_selection_value.ID_selection = " . $db_modules->toSql($db_modules->getField("ID_selection", "Number")) . "
                                   )
                                ) AS tbl_src
                                ORDER BY tbl_src.name");
            if($db_selection->nextRecord()) {
                do {
                    $selection_value[] = array(new ffData($db_selection->getField("nameID")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("name")->getValue())));
                } while($db_selection->nextRecord());
            }

            $obj_page_field->multi_pairs = $selection_value;
            $obj_page_field->encode_entities = false;

            $type_value = "Text";
            break;
        case "Text":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
            $obj_page_field->control_type = "textarea";
                
            $obj_page_field->widget = "";
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Text";
            break;

        case "TextBB":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";

            $obj_page_field->control_type = "textarea";
            if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/tiny_mce/tiny_mce.js")) {
                $obj_page_field->widget = "tiny_mce";
            } else {
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

            $obj_page_field->control_type = "textarea";
            if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
                $obj_page_field->widget = "ckeditor";
            } else {
                $obj_page_field->widget = "";
            }
            $obj_page_field->ckeditor_group_by_auth = true;

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Text";
            break;

        case "Boolean":
            $obj_page_field->base_type = "Number";
            $obj_page_field->extended_type = "Boolean";
            $obj_page_field->control_type = "checkbox";

            $obj_page_field->widget = "";
            $obj_page_field->unchecked_value = new ffData("0", "Number");
            $obj_page_field->checked_value = new ffData("1", "Number");
            $obj_page_field->grouping_separator = "";
            $type_value = "Number";
            break;

        case "Date":
            $obj_page_field->base_type = "Date";
            $obj_page_field->extended_type = "Date";
            $obj_page_field->control_type = "input";
            $obj_page_field->widget = "datepicker";
            
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Date";
            break;

        case "DateCombo":
            $obj_page_field->base_type = "Date";
            $obj_page_field->extended_type = "Date";
            $obj_page_field->control_type = "input";
            $obj_page_field->widget = "datechooser";
           
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Date";
            break;
            
        case "Image":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
            $obj_page_field->control_type = "input";
            $obj_page_field->widget = "kcfinder"; 
			if(check_function("set_field_uploader")) { 
				$obj_page_field = set_field_uploader($obj_page_field);
			}

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Text";
            break;

        case "Upload":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
            $obj_page_field->control_type = "input";
            $obj_page_field->widget = "uploadify";
			if(check_function("set_field_uploader")) { 
				$obj_page_field = set_field_uploader($obj_page_field);
			}

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Text";
            break;

        case "UploadImage":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
            $obj_page_field->control_type = "input";
            $obj_page_field->widget = "kcuploadify"; 
			if(check_function("set_field_uploader")) { 
				$obj_page_field = set_field_uploader($obj_page_field);
			}

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Text";
            break;

        case "Number":
            $obj_page_field->base_type = "Number";
            $obj_page_field->extended_type = "";
            $obj_page_field->control_type = "input";

            $obj_page_field->widget = "";
            $obj_page_field->unchecked_value = new ffData(""); 
            $obj_page_field->checked_value = new ffData(""); 
            $obj_page_field->grouping_separator = "";
            $type_value = "Number";
            break;

            
        default: // String
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
            $obj_page_field->control_type = "input";

            $obj_page_field->widget = "";
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Text";
    }

    if($component->db[0]->record["value"] === NULL) {
       $record["value"]->setValue($layout_settings[$record["layout_settings"]->getValue()], $type_value);
    } else {
        $record["value"]->setValue($record["value"]->getValue(), $type_value);
    }


}
          
          
function LayoutTypeModify_on_done_action($component, $action) {
   

    
}

?>