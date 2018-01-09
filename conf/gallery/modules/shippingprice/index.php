<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oGrid = ffGrid::factory($oPage);
$oGrid->id = $oGrid->user_vars["MD_chk"]["id"];
$oGrid->class = $oGrid->user_vars["MD_chk"]["id"];
$oGrid->source_SQL = "SELECT 
							ecommerce_mpay_zone.*
						FROM 
							ecommerce_mpay_zone
                        [WHERE]
						[ORDER] ";

$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = false;
$oGrid->display_new = false;
$oGrid->use_own_location = $oGrid->user_vars["MD_chk"]["own_location"];


$oField = ffField::factory($oPage);
$oField->id = "shippingprice" . "-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("edit_general_name");
$oField->properties["style"]["width"] = "80px";
$oGrid->addContent($oField);

$oField = ffField::factory($oPage);
$oField->id = "description";
$oField->container_class = "description";
$oField->label = ffTemplate::_get_word_by_code("edit_general_description");
$oField->base_type = "Text";
$oField->data_type = "callback";
if(check_function("MD_shippingprice_populate_report"))
	$oField->data_source = "MD_shippingprice_populate_report"; 
$oGrid->addContent($oField);

$oButton = ffButton::factory($oPage);
$oButton->id = "database";
$oButton->action_type = "gotourl";
$oButton->url = stripslash($oGrid->user_vars["MD_chk"]["page_url"]) . "/zone?[KEYS]&ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "detail.png";
$oButton->label = ffTemplate::_get_word_by_code("detail_zone");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);


$oPage->addContent($oGrid);

?>
