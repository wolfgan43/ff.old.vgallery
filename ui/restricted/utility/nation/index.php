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

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "nation";
$oGrid->title = ffTemplate::_get_word_by_code("nation_title");
$oGrid->source_SQL = "SELECT " . FF_PREFIX . "loc_state.* 
							, " . FF_PREFIX . "ip2nationCountries.iso_country
						FROM " . FF_PREFIX . "ip2nationCountries
							LEFT JOIN " . FF_PREFIX . "loc_state ON " . FF_PREFIX . "ip2nationCountries.iso_country = " . FF_PREFIX . "loc_state.name
						WHERE " . FF_PREFIX . "ip2nationCountries.iso_country <> ''
						[AND]
						[WHERE] 
						[HAVING] 
						[ORDER]";
$oGrid->order_default = "iso_country";
$oGrid->use_search = true;
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "NationModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addit_record_param = "name=[iso_country_VALUE]&";

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID-state";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);


// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "iso_country";
$oField->label = ffTemplate::_get_word_by_code("nation_iso_country");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("nation_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_lang";
$oField->label = ffTemplate::_get_word_by_code("nation_ID_lang");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . FF_PREFIX . "languages.ID, CONCAT(" . FF_PREFIX . "languages.description, IF(" . FF_PREFIX . "languages.status > 0, '', CONCAT(' (', '" . ffTemplate::_get_word_by_code("disabled_language") . "', ') '))) FROM " . FF_PREFIX . "languages ORDER BY " . FF_PREFIX . "languages.description";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
$oField->src_having = true;
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "city";
$oButton->class = "icon ico-nation-city";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/city?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("nation_city");
//$oButton->image = "set.png";
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "province";
$oButton->class = "icon ico-nation-province";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/province?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("nation_province");
//$oButton->image = "set.png";
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "region";
$oButton->class = "icon ico-nation-region";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/region?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("nation_region");
//$oButton->image = "set.png";
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid); 



