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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if (!Auth::env("AREA_LAYER_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$framework_css = Cms::getInstance("frameworkcss")->getFramework();
$framework_css_name = $framework_css["name"];

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Layer";
$oGrid->title = ffTemplate::_get_word_by_code("layer_title");
$oGrid->source_SQL = "SELECT layout_layer.* 
                            FROM layout_layer 
                            WHERE 1
                            [AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "order";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "LayerModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->widget_deps[] = array(
	"name" => "dragsort"
	, "options" => array(
		  &$oGrid
		, array(
			"resource_id" => "layout_layer"
			, "service_path" => get_path_by_rule("services", "restricted") . "/sort"
		)
		, "ID"
	)
);
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca
 
// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("section_name");
$oGrid->addContent($oField);

if(!$framework_css_name) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "width";
	$oField->label = ffTemplate::_get_word_by_code("layer_width");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage); 
	$oField->id = "show_empty";
	$oField->label = ffTemplate::_get_word_by_code("layer_show_empty");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
		                        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
		                        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes"))),
		                   );   
	$oField->multi_select_one = false;
	$oGrid->addContent($oField);	
}

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("layer_order");
$oField->base_type = "Number";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);