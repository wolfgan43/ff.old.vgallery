<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$sSQL = "SELECT cm_layout.* 
			FROM cm_layout 
			WHERE cm_layout.path = " . $db_gallery->toSql("/");
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
	$framework_css = cm_getFrameworkCss($db_gallery->getField("framework_css", "Text", true));
	$framework_css_name = $framework_css["name"];
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormConfigGroupModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("form_group_modify");
$oRecord->src_table = "module_form_fields_group";
$oRecord->buttons_options["print"]["display"] = false;

if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "formgrp-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("form_group_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cover";
$oField->label = ffTemplate::_get_word_by_code("form_group_cover");
$oField->base_type = "Text";
$oField->control_type = "file";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/form/group/[name_FATHER]";
$oField->file_temp_path = DISK_UPDIR . "/tmp/form/group";
$oField->file_max_size = MAX_UPLOAD;
$oField->file_allowed_mime = array();                    
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_show_filename = true; 
$oField->file_show_delete = true;
$oField->file_writable = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
    $oField = set_field_uploader($oField);
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cover_mode";
$oField->label = ffTemplate::_get_word_by_code("form_group_cover_mode");
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "showfiles_modes ORDER BY name";
if(AREA_PROPERTIES_SHOW_MODIFY) {
    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/image/modify";
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
    $oField->resources[] = "ExtrasImageModify";
}
$oRecord->addContent($oField);

if(strlen($framework_css_name)) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "default_grid";
	$oField->label = ffTemplate::_get_word_by_code($setting . "_group_default_grid");
	$oField->base_type = "Number";
	$oField->default_value = new ffData(12, "Number");
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "grid_md";
	$oField->label = ffTemplate::_get_word_by_code($setting . "_group_grid_md");
	$oField->base_type = "Number";
	$oField->default_value = new ffData(12, "Number");
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "grid_sm";
	$oField->label = ffTemplate::_get_word_by_code($setting . "_group_grid_sm");
	$oField->base_type = "Number";
	$oField->default_value = new ffData(12, "Number");
	$oRecord->addContent($oField);

	if($framework_css_name == "bootstrap") {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_xs";
		$oField->label = ffTemplate::_get_word_by_code($setting . "_group_grid_xs");
		$oField->base_type = "Number";
		$oField->default_value = new ffData(12, "Number");
		$oRecord->addContent($oField);
	}
}

$cm->oPage->addContent($oRecord);