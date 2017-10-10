<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->title = ffTemplate::_get_word_by_code("marker");
$oGrid->id = "MapsMarker";
$oGrid->source_SQL = "SELECT module_maps_marker.*
	                            FROM 
	                                module_maps_marker
	                            WHERE 
	                                module_maps_marker.ID_module_maps = " . $db_gallery->toSql($_REQUEST["keys"]["mapscnf-ID"], "Number") . "
						[AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "coords_title";
$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "MapsMarkerModify";
$oGrid->resources[] = $oGrid->record_id;

$oField = ffField::factory($cm->oPage);
$oField->id = "mapsmrk-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "coords_title";
$oField->label = ffTemplate::_get_word_by_code("marker_coords_title");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oButton->action_type = "gotourl";
$oButton->url = "[RET_URL]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("marker_back");
$oGrid->addActionButton($oButton);


$cm->oPage->addContent($oGrid);
?>