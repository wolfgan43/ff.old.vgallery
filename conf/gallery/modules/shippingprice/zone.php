<?php
require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$ID_zone = $_REQUEST["keys"]["shippingprice-ID"];

$cm->oPage->form_method = "POST";

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = $oGrid->user_vars["MD_chk"]["id"];
$oGrid->class = $oGrid->user_vars["MD_chk"]["id"];
$oGrid->source_SQL = "SELECT 
							ecommerce_shipping_price.*
						FROM 
							ecommerce_shipping_price
						WHERE ID_zone = " . $db_gallery->toSql(new ffData($ID_zone, "Number", FF_SYSTEM_LOCALE), "Number") . "
                        [WHERE]
						[ORDER] ";

$oGrid->order_default = "pesominimo";
$oGrid->use_search = FALSE;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = false;
$oGrid->display_new = false;


$oField = ffField::factory($cm->oPage);
$oField->id = "shippingprice" . "-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "weight_min";
$oField->container_class = "weightmin";
$oField->label = ffTemplate::_get_word_by_code("ecommerce_shipping_min_size");
$oField->base_type = "Number";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "weight_max";
$oField->container_class = "weightmax";
$oField->label = ffTemplate::_get_word_by_code("ecommerce_shipping_max_size");
$oField->base_type = "Number";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "price";
$oField->container_class = "price";
$oField->label = ffTemplate::_get_word_by_code("ecommerce_shipping_price");
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oField->container_class = "back";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->ret_url;
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("ecommerce_shipping_back");//Definita nell'evento
$oGrid->addActionButton($oButton);


$cm->oPage->addContent($oGrid);
?>
