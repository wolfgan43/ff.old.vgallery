<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
//$oGrid->full_ajax = true;
$oGrid->ajax_delete	= true;
$oGrid->ajax_search	= true;
$oGrid->id = "modules";
$oGrid->title = ffTemplate::_get_word_by_code("modules");
$oGrid->source_SQL = "SELECT
                            modules.*
                            , CONCAT(UPPER(SUBSTR(module_name, 1, 1)), SUBSTR(module_name, 2)) AS component
                        FROM
                            modules
                        [WHERE]
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "component";
$oGrid->use_search = true;
$oGrid->record_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_url = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/content/modules" . "/[module_name_VALUE]" . "/config/modify/[module_params_VALUE]"; //$oPage->site_path . $oPage->page_path . "/modify";
$oGrid->record_id = "[component_VALUE]ConfigModify";
//$oGrid->resources[] = $oGrid->record_id;
$oGrid->resources[] = "modules";

$oGrid->display_new = false;
/*if($permission["drafts"] < 2) {
    
    $oGrid->display_edit_bt = false;
    $oGrid->display_edit_url = false;
    $oGrid->display_delete_bt = false;
}*/
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "module_name";
$oField->display = false;
$oField->label = ffTemplate::_get_word_by_code("module_name");
$oGrid->addContent($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "component";
$oField->label = ffTemplate::_get_word_by_code("module_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "module_params";
$oField->label = ffTemplate::_get_word_by_code("module_params");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "database";
//$oButton->class = "icon ico-detail";
$oButton->action_type = "gotourl";
$oButton->url = FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules" . "/[modules_module_name_VALUE]" . "/config/[modules_module_params_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&[GLOBALS]";
$oButton->aspect = "link";
//$oButton->image = "detail.png";
$oButton->label = ffTemplate::_get_word_by_code("module_detail");
$oButton->template_file = "ffButton_link_fixed.html";                           
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid);


?>
