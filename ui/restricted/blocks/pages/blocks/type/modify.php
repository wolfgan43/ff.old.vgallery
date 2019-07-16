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

if (!Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "LayoutTypeModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "layout_type";
$oRecord->addEvent("on_done_action", "LayoutTypeModify_on_done_action");
$oRecord->tab = true;

system_ffcomponent_set_title(
	ffTemplate::_get_word_by_code("layout_type_modify_title")
	, array(
		"name" => "cube"
	)
	, false
	, false
	, $oRecord
);	
		
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "layout";
$oField->base_type = "Number";
$oField->value = new ffData("0", "Number");
$oRecord->addKeyField($oField);

$group_general = "source";
$oRecord->addContent(null, true, $group_general); 
$oRecord->groups[$group_general] = array(
										"title" => ffTemplate::_get_word_by_code("layout_" . $group_general)
                                      );

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_name");
$oField->control_type = "label";
$oField->required = true;
$oRecord->addContent($oField, $group_general);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("layout_type_modify_description");
$oField->extended_type = "Text";
$oRecord->addContent($oField, $group_general);

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
$oRecord->addContent($oField, $group_general);

$group_settings = "settings";
$oRecord->addContent(null, true, $group_settings); 

$oRecord->groups[$group_settings] = array(
                                            "title" => ffTemplate::_get_word_by_code("layout_" . $group_settings)
                                          );    

$oDetail_settings = ffDetails::factory($cm->oPage);
$oDetail_settings->id = "LayoutSettingsDetail";
$oDetail_settings->tab = "left";
$oDetail_settings->tab_label = "settings_group";
$oDetail_settings->src_table = "layout_settings_rel";
$oDetail_settings->order_default = "ID";
$oDetail_settings->addEvent("on_before_process_row", "LayoutSettingsDetail_on_before_process_row");
$oDetail_settings->addEvent("on_before_parse_row", "LayoutSettingsDetail_on_before_parse_row");  
$oDetail_settings->display_delete = false;
$oDetail_settings->display_new = false;
$oDetail_settings->fields_relationship = array ("ID_layout" => "layout");
$oDetail_settings->auto_populate_insert = false;
$oDetail_settings->populate_insert_SQL = "SELECT 
                                                null AS ID
                                                , null AS ID_layout
                                                , layout_settings.ID AS ID_layout_settings
                                                , layout_settings.name AS layout_settings
                                                , IF(layout_settings.description = '', layout_settings.name, layout_settings.description) AS layout_settings_description
                                                , (
                                                        SELECT value
                                                        FROM layout_settings_rel
                                                        WHERE layout_settings_rel.ID_layout_settings = layout_settings.ID
                                                        AND layout_settings_rel.ID_layout = 0
                                                    )  AS value
                                                , extended_type.name AS extended_type
                                                , SUBSTRING( layout_settings.`group` FROM LOCATE('-', layout_settings.`group` ) + 1 ) AS  settings_group
                                             FROM 
                                                layout_settings
                                                INNER JOIN layout_type ON layout_type.ID = layout_settings.ID_layout_type AND layout_type.ID = [ID_FATHER]
                                                INNER JOIN extended_type ON extended_type.ID = layout_settings.ID_extended_type
                                             ORDER BY layout_settings.`group`, layout_settings.`order`, layout_settings.name";
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
                                             ORDER BY layout_settings.`group`, layout_settings.`order`, layout_settings.name";
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

$oRecord->addContent($oDetail_settings, $group_settings);
$cm->oPage->addContent($oDetail_settings);

$cm->oPage->addContent($oRecord);   


// -------------------------
//          EVENTI
// -------------------------
function LayoutSettingsDetail_on_before_process_row($component, $record) {
	$db = ffDB_Sql::factory();

	if(defined("AREA_SHOW_" . strtoupper($record["settings_group"]->getValue())) && !constant("AREA_SHOW_" . strtoupper($record["settings_group"]->getValue()))) {
		return true;
	}             
 
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
	    if(check_function("system_get_js_plugins"))
			$field_params["source_SQL"] = system_get_js_libs(null, "sql_distinct");
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
		$tpl_file = glob(__CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/" . GALLERY_TPL_PATH . $tpl_path . "/*");
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
       $record["value"]->setValue($component->user_vars["layout_settings"][$record["layout_settings"]->getValue()], $obj_page_field->base_type);
    } else {
        $record["value"]->setValue($record["value"]->getValue(), $obj_page_field->base_type);
    }
 

	
	
}
       
function LayoutSettingsDetail_on_before_parse_row($component, $record, $record_key) {
	$component->tpl[0]->set_var("SectFormTitle", "");
}    
    
          
function LayoutTypeModify_on_done_action($component, $action) {
   

    
}
