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

if (!Auth::env("AREA_CHARSET_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "charset";
$oGrid->source_SQL = "SELECT * FROM " . CM_TABLE_PREFIX . "charset_decode [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "CharsetModify";
$oGrid->resources[] = $oGrid->record_id;

/**
* Title
*/
system_ffcomponent_set_title(
	ffTemplate::_get_word_by_code("charset_title")
	, array("name" => "language")
	, false
	, false
	, $oGrid
);

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oField->label = ffTemplate::_get_word_by_code("charset_code");
$oGrid->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("charset_value");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);