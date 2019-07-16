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

if (!Auth::env("AREA_MODULES_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_maps", array(
	"name" => "IF(module_maps.display_name = ''
				    , REPLACE(module_maps.name, '-', ' ')
				    , module_maps.display_name
				)"
));
$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "MapsMarker";
$oGrid->source_SQL = "SELECT module_maps_marker.*
	                            FROM 
	                                module_maps_marker
	                            WHERE 
	                                module_maps_marker.ID_module_maps = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
						[AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "coords_title";
$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/field";
$oGrid->record_id = "MapsMarkerModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->buttons_options["export"]["display"] = false;

/* Title Block */
system_ffcomponent_set_title(
	null
	, true
	, false
	, false
	, $oGrid
);     


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
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