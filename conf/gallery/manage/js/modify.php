<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_JS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

   
$type = basename($cm->real_path_info);
if(!strlen($type))
	$type = "plugin";
   
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "JsModify" . $type;
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("js_modify_title");
$oRecord->src_table = "js";
$oRecord->addEvent("on_done_action", "JsModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("js_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("js_modify_order");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "base_path";
$oField->label = ffTemplate::_get_word_by_code("js_modify_base_path");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("/themes/library"), new ffData("/themes/library")),
                            array(new ffData("/themes/library/plugins"), new ffData("/themes/library/plugins"))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("auto_detect");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "src_path";
$oField->label = ffTemplate::_get_word_by_code("js_modify_src_path");
$oField->required = true;
/*$oField->widget = "kcfinder"; 
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_show_filename = true; 
$oField->file_show_delete = true;
$oField->file_writable = true;
$oField->file_normalize = true;
$oField->file_show_preview = false;*/
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("js_modify_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("libs"), new ffData(ffTemplate::_get_word_by_code("libs"))),
                            array(new ffData("plugin"), new ffData(ffTemplate::_get_word_by_code("plugin")))
                       );      
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_mode_thumb_g";
$oField->label = ffTemplate::_get_word_by_code("js_modify_ID_mode_thumb_gallery");
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/mode/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasModeModify_confirmdelete";
$oField->resources[] = "ExtrasModeModify"; 
$oField->source_SQL = "SELECT ID, CONCAT(name, ' (', description, ')') FROM settings_thumb_mode WHERE `type` LIKE '%thumb%' ORDER BY name";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_mode_thumb_v";
$oField->label = ffTemplate::_get_word_by_code("js_modify_ID_mode_thumb_vgallery");
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/mode/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasModeModify_confirmdelete";
$oField->resources[] = "ExtrasModeModify"; 
$oField->source_SQL = "SELECT ID, CONCAT(name, ' (', description, ')') FROM settings_thumb_mode WHERE `type` LIKE '%thumb%' ORDER BY name";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_mode_preview";
$oField->label = ffTemplate::_get_word_by_code("js_modify_ID_mode_preview_vgallery");
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/mode/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasModeModify_confirmdelete";
$oField->resources[] = "ExtrasModeModify"; 
$oField->source_SQL = "SELECT ID, CONCAT(name, ' (', description, ')') FROM settings_thumb_mode WHERE `type` LIKE '%detail%' ORDER BY name";
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("js_modify_status");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "exclude_compact";
$oField->label = ffTemplate::_get_word_by_code("js_modify_exclude_compact");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oRecord->addTab("AdditionalFile" . $type);
$oRecord->setTabTitle("AdditionalFile" . $type, ffTemplate::_get_word_by_code("js_modify_additional_file"));

$oRecord->addContent(null, true, "AdditionalFile" . $type); 
$oRecord->groups["AdditionalFile" . $type] = array(
			                     "title" => ffTemplate::_get_word_by_code("js_modify_additional_file")
			                     , "cols" => 1
			                     , "tab" => "AdditionalFile" . $type
			                  );  
			                  
$oField = ffField::factory($cm->oPage);
$oField->id = "preload_cnf";
$oField->label = ffTemplate::_get_word_by_code("js_modify_preload_cnf");
$oField->widget = "listgroup";
$oField->grouping_separator = ";";
$oRecord->addContent($oField, "AdditionalFile" . $type);

$oField = ffField::factory($cm->oPage);
$oField->id = "load_css";
$oField->label = ffTemplate::_get_word_by_code("js_modify_load_css");
$oField->widget = "listgroup";
$oField->grouping_separator = ";";
$oRecord->addContent($oField, "AdditionalFile" . $type);

$oField = ffField::factory($cm->oPage);
$oField->id = "postload_cnf";
$oField->label = ffTemplate::_get_word_by_code("js_modify_postload_cnf");
$oField->widget = "listgroup";
$oField->grouping_separator = ";";
$oRecord->addContent($oField, "AdditionalFile" . $type);

$cm->oPage->addContent($oRecord);  

if($type != "libs") {
	$oRecord->addTab("JsModifyDependence" . $type);
	$oRecord->setTabTitle("JsModifyDependence" . $type, ffTemplate::_get_word_by_code("js_modify_dependence_title"));

	$oRecord->addContent(null, true, "JsModifyDependence" . $type); 
	$oRecord->groups["JsModifyDependence" . $type] = array(
				                     "title" => ffTemplate::_get_word_by_code("js_modify_dependence_title")
				                     , "cols" => 1
				                     , "tab" => "JsModifyDependence" . $type
				                  );  

	$oDetail = ffDetails::factory($cm->oPage);
	$oDetail->id = "JsModifyDependence" . $type;
	$oDetail->title = ffTemplate::_get_word_by_code("js_modify_dependence_title");
	$oDetail->widget_discl_enable = false;
	$oDetail->src_table = "js_dipendence";
	$oDetail->order_default = "ID";
	$oDetail->fields_relationship = array ("ID_js_plugin" => "ID");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oDetail->addKeyField($oField);
		
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_js_libs";
	$oField->label = ffTemplate::_get_word_by_code("js_modify_dependence_ID_js_libs");
	$oField->base_type = "Number";
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;

	$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/js/modify/libs";
	$oField->actex_dialog_edit_params = array("keys[ID]" => null);
	$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=JsModify_confirmdelete";
	$oField->resources[] = "JsModifylibs";

	$oField->source_SQL = "SELECT ID, name FROM js WHERE js.type = 'libs'";
	$oField->required = true;
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "param_value";
	$oField->label = ffTemplate::_get_word_by_code("js_modify_dependence_param_value");
	$oDetail->addContent($oField);

	$oRecord->addContent($oDetail, "JsModifyDependence" . $type);
	$cm->oPage->addContent($oDetail);
}


function JsModify_on_done_action($component, $action) {
	
	
}
?>
