<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ModulesModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("modules_modify_title");
$oRecord->src_table = "modules";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "module_name";
$oField->label = ffTemplate::_get_word_by_code("modules_name");
$oField->extended_type = "Selection";

foreach(glob(FF_DISK_PATH . "/conf" . GALLERY_PATH_MODULE . "/*", GLOB_ONLYDIR) as $real_file) { 
    if (is_dir($real_file)) {
        $oField->multi_pairs[] = array(new ffData(basename($real_file)), new ffData(ffTemplate::_get_word_by_code(basename($real_file))));
        
    }
}

$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "module_params";
$oField->label = ffTemplate::_get_word_by_code("modules_params");
$oRecord->addContent($oField);


$cm->oPage->addContent($oRecord);

/*
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "ModulesModifyDetail";
$oDetail->title = ffTemplate::_get_word_by_code("modules_modify_detail_title");
$oDetail->src_table = "rel_modules_path";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_module" => "ID");
$oDetail->display_new = true;
$oDetail->display_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("modules_modify_detail_path");
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->label = ffTemplate::_get_word_by_code("modules_modify_detail_cascading");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oDetail->addContent($oField);

$oRecord->setDetail($oDetail);
$cm->oPage->addContent($oDetail);
           */
//if($_REQUEST["keys"]["IDs"]) {

?>
