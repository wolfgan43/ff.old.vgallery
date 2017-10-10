<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_LAYOUT_SHOW_MODIFY) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(!$_REQUEST["keys"]["ID"]) {
    $sSQL = "SELECT * FROM layout_type WHERE layout_type.name = " . $db_gallery->toSql(basename($cm->real_path_info), "Text");
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID", "Number", true);
        $layout_name = $db_gallery->getField("description", "Text", true);
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "LayoutSettingsModify";
$oRecord->resources[] = $oRecord->id;
//$oRecord->title = ffTemplate::_get_word_by_code("layout_settings_title");
$oRecord->src_table = "layout_type";
$oRecord->addEvent("on_done_action", "LayoutSettingsModify_on_done_action");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . cm_getClassByFrameworkCss("vg-draft", "icon-tag", array("2x", "content")) . $layout_name . '</h1>';

$oRecord->buttons_options["delete"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "layout";
$oField->base_type = "Number";
$oField->value = new ffData("0", "Number");
$oRecord->addKeyField($oField);
 
 /*
$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("layout_settings_name");
$oField->control_type = "label";
$oRecord->addContent($oField);*/

$cm->oPage->addContent($oRecord);

$oDetail_settings = ffDetails::factory($cm->oPage);
$oDetail_settings->id = "LayoutSettingsDetail";
//$oDetail_settings->title = ffTemplate::_get_word_by_code("layout_settings_detail_title");
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
											, extended_type.name AS extended_type
	                                        , SUBSTRING( layout_settings.`group` FROM LOCATE('-', layout_settings.`group` ) + 1 ) AS  settings_group                                            
                                         FROM 
                                            layout_settings
                                            INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type AND layout_type.ID = [ID_FATHER]
                                            INNER JOIN extended_type ON extended_type.ID = layout_settings.ID_extended_type
                                         ORDER BY layout_settings.`group`, layout_settings.`order`, layout_settings.name
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
	                                        , SUBSTRING( layout_settings.`group` FROM LOCATE('-', layout_settings.`group` ) + 1 ) AS  settings_group
                                         FROM 
                                            layout_settings
                                            INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type AND layout_type.ID = [ID_FATHER]
                                            INNER JOIN extended_type ON extended_type.ID = layout_settings.ID_extended_type
                                         ORDER BY layout_settings.`group`, layout_settings.`order`, layout_settings.name
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

$oField = ffField::factory($cm->oPage);
$oField->id = "settings_group";
$oField->store_in_db = false;
$oDetail_settings->addHiddenField($oField);

$oRecord->addContent($oDetail_settings);
$cm->oPage->addContent($oDetail_settings);
  
// -------------------------
//          EVENTI
// -------------------------

function LayoutSettingsDetail_on_before_process_row($component, $record) {
	$db = ffDB_Sql::factory();

	if(defined("AREA_SHOW_" . strtoupper($record["settings_group"]->getValue())) && !constant("AREA_SHOW_" . strtoupper($record["settings_group"]->getValue()))) {
		return true;
	}             

    $type = $component->user_vars["tbl_src"];
    
    if(check_function("get_layout_settings"))
    	$layout_settings = get_layout_settings(NULL, $type); 
   
    $obj_page_field = $component->form_fields["value"];
    // $component->user_vars["group"][(strlen($record["settings_group"]->getValue()) ? "general" : $record["settings_group"]->getValue())] = true;
    $field_params = array(
        "extended_type" => $record["extended_type"]->getValue()
        , "label" => ffTemplate::_get_word_by_code($record["layout_settings_description"]->getValue())
        , "placeholder" => true
        , "container_class" => $record["settings_group"]->getValue() 
    );   
    
    if(strpos(strtoupper($record["layout_settings_description"]->getValue()), "PLUGIN") !== false) 
    {
    	$field_params["extended_type"] = "AutocompleteWritable";
    	$field_params["source_SQL"] = "SELECT js.name
    									, js.name
    									FROM js
    									WHERE 1
    									ORDER BY js.name";
    
    }

    if(strpos(strtoupper($record["layout_settings_description"]->getValue()), "HTMLTAG") !== false) 
    {
    	$field_params["extended_type"] = "AutocompleteWritable";
    	$field_params["source_SQL"] = "SELECT vgallery_fields_htmltag.tag
    									, CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')') AS tagattr
    									FROM vgallery_fields_htmltag
    									WHERE 1
    									ORDER BY tagattr";
    
    }
	
    if(strpos(strtoupper($record["layout_settings_description"]->getValue()), "TEMPLATE") !== false) 
    {
    	$st_class = $component->main_record[0]->user_vars["st_class"];
    	$tpl_path = $st_class[strtolower($record["settings_group"]->getValue())]["tpl_path"];

    	if(!$tpl_path) 
    		return true;
		
		$sSQL_template_file = array();
		$tpl_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/" . GALLERY_TPL_PATH . $tpl_path . "/*");
		if(is_array($tpl_file) && count($tpl_file)) {
		    foreach($tpl_file AS $real_file) {
		        if(is_file($real_file)) {
		        	$file_name = ffGetFilename($real_file);
 					if(strpos($file_name, "_") !== false)
						continue;
						
		            $sSQL_template_file[] = " (SELECT 
		                                " . $db->toSql($file_name) . " AS nameID
		                                , " . $db->toSql(ucfirst($file_name)) . " AS name
		                            )";
		        }
		    }
		}

		if(is_array($sSQL_template_file) && count($sSQL_template_file)) {
    		$field_params["extended_type"] = "Selection";
    		//$field_params["enable_actex"] = true;
    		$field_params["disable_select_one"] = true;
			$field_params["source_SQL"] = implode(" UNION ", $sSQL_template_file);
		} else {
			return true;
		}
    }    

    if(check_function("get_field_by_extension"))
        get_field_by_extension($obj_page_field, $field_params);
     
    if($component->db[0]->record["value"] === NULL) {
       $record["value"]->setValue($layout_settings[$record["layout_settings"]->getValue()], $obj_page_field->base_type);
    } else {
        $record["value"]->setValue($record["value"]->getValue(), $obj_page_field->base_type);
    }

}

function LayoutSettingsModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if(strlen($action)) {
        //UPDATE CACHE
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
                ";
        $db->execute($sSQL);
    }
}
?>