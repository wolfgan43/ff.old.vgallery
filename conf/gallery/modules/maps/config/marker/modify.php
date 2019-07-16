<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

$oRecord->id = "MapsMarkerModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("marker_modify");
$oRecord->src_table = "module_maps_marker";

if(check_function("MD_general_on_done_action"))
    $oRecord->addEvent("on_done_action", "addMarker_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "mapsmrk-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);


$oRecord->insert_additional_fields = array("ID_module_maps" => new ffData($_REQUEST["keys"]["mapscnf-ID"]));

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
$oField->base_type = "Text";
$oField->setWidthComponent(6);
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

function addMarker_on_done_action ($component, $action) {
    if(strlen($action)) {
        $db = ffDB_Sql::factory();
        switch ($action) {
            case "insert":
            case "update":
                if(strlen($component->form_fields["coords"]->value["title"]->value_text)) {
                    $smart_url = ffcommon_url_rewrite($component->form_fields["coords"]->value["title"]->value_text);
                    
                    $sSQL = "UPDATE module_maps_marker
                                SET smart_url = " . $db->toSql($smart_url, "Text") . "
                                WHERE ID = " . $db->toSql($component->key_fields["mapsmrk-ID"]->getValue(), "Number");
                    $db->execute($sSQL);
                }
                break;
            default:
                break;
        }
    }
}
