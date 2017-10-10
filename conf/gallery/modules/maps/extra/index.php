<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

if(!isset($_REQUEST["keys"]["mapscnf-ID"])) {
    if(!strlen(basename($cm->real_path_info)) && isset($_REQUEST["name"]))
    $cm->real_path_info = "/" . $_REQUEST["name"];

    $db_gallery->query("SELECT module_maps.*
                            FROM module_maps
                            WHERE module_maps.name = " . $db_gallery->toSql(new ffData( basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["mapscnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
    } 
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
$oGrid->addit_insert_record_param = "src=" . $src_type . "&";
$oGrid->addit_record_param = "src=" . $src_type . "&";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->buttons_options["export"]["display"] = false;

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
if($cm->isXHR())
	$oButton->label = ffTemplate::_get_word_by_code("ffRecord_update");
else
	$oButton->label = ffTemplate::_get_word_by_code("ffRecord_cancel");
$oGrid->addActionButton($oButton);


$cm->oPage->addContent($oGrid);
?>