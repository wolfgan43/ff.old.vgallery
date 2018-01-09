<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "FormManage";
$oGrid->source_SQL = "SELECT module_form.* 
						FROM module_form 
						[WHERE] 
						[HAVING] 
						[ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url = "";
$oGrid->record_id = "";
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = false;
$oGrid->display_delete_bt = false;
$oGrid->display_new = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "formmng-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("form_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 
                        
$oButton = ffButton::factory($cm->oPage);
$oButton->id = "database";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/detail/[FormManage_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "detail.png";
$oButton->label = ffTemplate::_get_word_by_code("manage_detail");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);
                       
$cm->oPage->addContent($oGrid);
?>
