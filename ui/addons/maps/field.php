<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();
check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record(
	array(
		"table" => "module_maps"
		, "key" => "formcnf-ID"
	)
	, array(
		"coords_lat" => null
		, "coords_lng" => null
		, "coords_zoom" => null
		, "coords_title" => null
	)
);
$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

$default_coords = array(
	"lat" => new ffData($record["coords_lat"])
	, "lng" => new ffData($record["coords_lng"])
	, "zoom" => new ffData($record["coords_zoom"])
	, "title" => new ffData($record["coords_title"])

);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MapsMarkerModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_maps_marker";

//if(check_function("MD_general_on_done_action"))
//	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

/* Title Block */
system_ffcomponent_set_title(
	null
	, true
	, false
	, false
	, $oRecord
);	 


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->insert_additional_fields = array(
	"ID_module_maps" => new ffData($_REQUEST["keys"]["formcnf-ID"])
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
