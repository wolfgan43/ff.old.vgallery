<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "layout";
//$oGrid->title = ffTemplate::_get_word_by_code("block_title");
$oGrid->source_SQL = "SELECT layout.* 
						FROM layout 
						WHERE 1
						[AND] [WHERE] 
						[HAVING] 
						[ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "LayoutModify";
$oGrid->resources[] = $oGrid->record_id;
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);



// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("block_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_location";
$oField->label = ffTemplate::_get_word_by_code("block_ID_location");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT ID, name FROM layout_location";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tblsrc";
$oField->data_source = "ID_type";
$oField->label = ffTemplate::_get_word_by_code("block_ID_type");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT
                           layout_type.ID
                           , layout_type.description
                       FROM
                            layout_type";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("block_title"))); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "layoutType";
//$oGrid->title = ffTemplate::_get_word_by_code("layout_type_title");
$oGrid->source_SQL = "SELECT * FROM layout_type [WHERE] [ORDER]";
$oGrid->order_default = "description";
$oGrid->use_search = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/type/modify";
$oGrid->record_id = "LayoutTypeModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("layout_type_name");
$oGrid->addContent($oField);

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
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("layout_type_title"))); 
?>
