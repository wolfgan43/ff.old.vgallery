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

$db = ffDB_Sql::factory();

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "nationRegion";
$oGrid->title = ffTemplate::_get_word_by_code("nation_region_title");
$oGrid->source_SQL = "SELECT DISTINCT " . FF_PREFIX . "loc_region.*
						FROM " . FF_PREFIX . "loc_region
							INNER JOIN " . FF_PREFIX . "loc_province ON " . FF_PREFIX . "loc_province.ID_region = " . FF_PREFIX . "loc_region.ID
						WHERE " . FF_PREFIX . "loc_province.ID_state = " . $db->toSql($_REQUEST["keys"]["ID-state"], "Number") . "
							" . ($_REQUEST["keys"]["ID-province"] > 0 
									? " AND " . FF_PREFIX . "loc_province.ID = " . $db->toSql($_REQUEST["keys"]["ID-province"])
									: ""
							) . "

						[AND]
						[WHERE] 
						[HAVING] 
						[ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->display_new = false;
//$oGrid->display_edit_bt = false;
//$oGrid->display_edit_url = false;
$oGrid->display_delete_bt = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "NationCityModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave

$oField = ffField::factory($cm->oPage);
$oField->id = "ID-region";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("nation_region_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "zone";
$oField->label = ffTemplate::_get_word_by_code("nation_region_zone");
$oGrid->addContent($oField);

if(!isset($_REQUEST["keys"]["ID-province"])) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "city";
	$oButton->class = "icon ico-nation-province";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/province?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("nation_province");
	//$oButton->image = "set.png";
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
}

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oButton->action_type = "gotourl";
$oButton->url = "[RET_URL]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("Back");
$oGrid->addActionButton($oButton);

$cm->oPage->addContent($oGrid); 