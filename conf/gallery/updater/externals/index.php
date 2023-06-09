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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if (!Auth::env("AREA_UPDATER_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "externals";
$oGrid->title = ffTemplate::_get_word_by_code("externals_title");
$oGrid->source_SQL = "SELECT * FROM updater_externals [WHERE] [ORDER]";
$oGrid->order_default = "path";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "ExternalsModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("externals_path");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "domain";
$oField->label = ffTemplate::_get_word_by_code("externals_domain");
$oGrid->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("externals_status");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active"))),
                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("disactive")))
                       );
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);