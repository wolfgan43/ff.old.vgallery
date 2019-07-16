<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_MODULES_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

$db_gallery->query("SELECT module_maps.*
                        FROM module_maps
                        WHERE module_maps.ID = " . $db_gallery->toSql($_REQUEST["keys"]["mapscnf-ID"], "Number")
                    );
if($db_gallery->nextRecord()) {
	$default_coords = array(
		"lat" => $db_gallery->getField("coords_lat", "Number")
		, "lng" => $db_gallery->getField("coords_lng", "Number")
		, "zoom" => $db_gallery->getField("coords_zoom", "Number")
		, "title" => $db_gallery->getField("coords_title")
	
	);
}

$oRecord = ffRecord::factory($cm->oPage);

$oRecord->id = "MapsMarkerModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("marker_modify");
$oRecord->src_table = "module_maps_marker";

//if(check_function("MD_general_on_done_action"))
//	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "mapsmrk-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oRecord->insert_additional_fields = array(
	"ID_module_maps" => new ffData($_REQUEST["keys"]["mapscnf-ID"])
);
$oRecord->additional_fields = array(
	"tbl_src" => new ffData($src_type)
	, "ID_node" => new ffData($_REQUEST["node"], "Number")
);

$oField = ffField::factory($cm->oPage);
$oField->id = "coords";
$oField->display_label = false;
$oField->label = ffTemplate::_get_word_by_code("marker_coords");
$oField->widget = "gmap";
$oField->gmap_draggable = true;
$oField->gmap_start_zoom = 10;
$oField->gmap_force_search = true;
if(check_function("set_field_gmap")) { 
	$oField = set_field_gmap($oField);
}
$oField->default_value = $default_coords;
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->display_label = false;
$oField->label = ffTemplate::_get_word_by_code("marker_description");
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->setWidthComponent(6);
$oField->base_type = "Text";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);
?>
