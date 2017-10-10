<?php
if (!AREA_SETTINGS_CUSTOM_SHOW_MODIFY) {
    FormsDialog(false, "OkOnly", ffTemplate::_get_word_by_code("dialog_title_accessdenied"), ffTemplate::_get_word_by_code("dialog_description_invalidpath"), "", $site_path . "/", THEME_INSET);
}

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);


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
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("groups_name");
$oField->control_type = "label";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "GroupsSettings";
$oDetail->title = ffTemplate::_get_word_by_code("groups_settings_title");
$oDetail->src_table = CM_TABLE_PREFIX . "mod_security_groups_fields";
$oDetail->order_default = "order";
$oDetail->fields_relationship = array ("ID_groups" => "gid");
$oDetail->tab = true;
$oDetail->tab_label = "field";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "field";
$oField->label = ffTemplate::_get_word_by_code("groups_settings_field");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_extended_type";
$oField->label = ffTemplate::_get_word_by_code("groups_settings_extended_type");
if(check_function("set_field_extended_type"))
	$oField = set_field_extended_type($oField);

$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "writable";
$oField->label = ffTemplate::_get_word_by_code("groups_settings_writable");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("groups_settings_default_value");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("groups_settings_order");
$oField->base_type = "Number";
$oField->order_SQL = " `order`, field";
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

?>