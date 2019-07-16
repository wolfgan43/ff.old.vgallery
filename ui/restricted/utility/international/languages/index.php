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

if (!Auth::env("AREA_LANGUAGES_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "languagesPanel";
$oGrid->source_SQL = "SELECT * FROM " . FF_PREFIX . "languages [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "LanguagesModify";
$oGrid->resources[] = $oGrid->record_id;

/**
* Title
*/
system_ffcomponent_set_title(
	ffTemplate::_get_word_by_code("languages_title")
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
$oField->label = ffTemplate::_get_word_by_code("languages_code");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("languages_status");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("not_active"))),
                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active")))
                       );
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tiny_code";
$oField->label = ffTemplate::_get_word_by_code("languages_tiny_code");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);