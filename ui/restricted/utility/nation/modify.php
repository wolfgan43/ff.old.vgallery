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

if(isset($_REQUEST["keys"]["ID-state"]) && !($_REQUEST["keys"]["ID-state"] > 0) && strlen($_REQUEST["name"])) {
	unset($_REQUEST["keys"]["ID-state"]);
}

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "NationModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("nation_title");
$oRecord->src_table = FF_PREFIX . "loc_state";
$oRecord->buttons_options["delete"]["display"] = false;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID-state";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("nation_name");
$oField->control_type = "label";
$oField->default_value = new ffData($_REQUEST["name"]);
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_lang";
$oField->label = ffTemplate::_get_word_by_code("nation_ID_lang");
$oField->base_type = "Number";
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT " . FF_PREFIX . "languages.ID, CONCAT(" . FF_PREFIX . "languages.description, IF(" . FF_PREFIX . "languages.status > 0, '', CONCAT(' (', '" . ffTemplate::_get_word_by_code("disabled_language") . "', ') '))) FROM " . FF_PREFIX . "languages ORDER BY " . FF_PREFIX . "languages.description";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
//$oField->required = true;
$oRecord->addContent($oField);

if(AREA_SHOW_ECOMMERCE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_currency";
	$oField->label = ffTemplate::_get_word_by_code("nation_ID_currency");
	$oField->base_type = "Number";
	$oField->widget = "actex";
	//$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->source_SQL = "SELECT " . FF_PREFIX . "currency.ID, " . FF_PREFIX . "currency.name FROM " . FF_PREFIX . "currency ORDER BY " . FF_PREFIX . "currency.name";
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
	//$oField->required = true;
	$oRecord->addContent($oField);
}

$cm->oPage->addContent($oRecord);