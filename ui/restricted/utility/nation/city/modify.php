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

if (!(AREA_LANGUAGES_SHOW_MODIFY && defined("MOD_SEC_ENABLE_USER_TRACE") && constant("MOD_SEC_ENABLE_USER_TRACE"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "NationCityModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("nation_city_title");
$oRecord->src_table = FF_PREFIX . "loc_city";
$oRecord->buttons_options["delete"]["display"] = false;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID-city";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("nation_city_name");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cap";
$oField->label = ffTemplate::_get_word_by_code("nation_city_cap");
$oField->src_having = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sigle_province";
$oField->label = ffTemplate::_get_word_by_code("nation_city_sigle_province");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "miron_island";
$oField->label = ffTemplate::_get_word_by_code("nation_city_miron_island");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes"))),
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no")))
                       );
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);