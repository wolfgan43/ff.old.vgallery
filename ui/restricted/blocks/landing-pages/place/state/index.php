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

if (!AREA_PLACES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();
$arrWhere = array();
$sSQL_Where = "";

/**
* Place State
*/
if($_REQUEST["PlaceState_permalink_src"]) {
	switch($_REQUEST["PlaceState_permalink_src"]) {
		case "permalink":
			$arrWhere[] = "permalink <> ''";
			break;
		case "nopermalink":
			$arrWhere[] = "permalink = ''";

			break;
		default:		
	}

	unset($_REQUEST["PlaceState_permalink_src"]);
}

if($_REQUEST["PlaceState_error_src"]) {
	switch($_REQUEST["PlaceState_error_src"]) {
		case "place":
			$arrWhere[] = "sigle = ''";
			break;
		case "coord":
			$arrWhere[] = "coord_title = ''";
			break;
		case "duplicate":
			$arrWhere[] = "LOCATE('-duplicate', name)";
			break;
		case "noerror":
			$arrWhere[] = "sigle != '' AND coord_title != ''";
			break;
		default:		
	}

	unset($_REQUEST["PlaceState_error_src"]);
}

if(is_array($arrWhere) && count($arrWhere)) {
	$sSQL_Where = " WHERE " . implode(" AND ", $arrWhere) . " [AND]";
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "PlaceState";
$oGrid->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.* 
						FROM " . FF_SUPPORT_PREFIX . "state
						$sSQL_Where
						[WHERE] 
						[HAVING] 
						[ORDER]";
$oGrid->order_default = "name";
//$oGrid->use_search = true;
//$oGrid->display_new = false;
//$oGrid->display_delete_bt = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "PlaceStateModify";
$oGrid->resources[] = $oGrid->record_id;


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);


// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("place_state");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sigle";
$oField->label = ffTemplate::_get_word_by_code("place_state_sigle");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_lang";
$oField->label = ffTemplate::_get_word_by_code("place_state_lang");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . FF_PREFIX . "languages.ID
							, CONCAT(" . FF_PREFIX . "languages.description, IF(" . FF_PREFIX . "languages.status > 0, '', CONCAT(' (', '" . ffTemplate::_get_word_by_code("disabled_language") . "', ') '))) 
						FROM " . FF_PREFIX . "languages 
						ORDER BY " . FF_PREFIX . "languages.description";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_currency";
$oField->label = ffTemplate::_get_word_by_code("place_state_currency");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . FF_PREFIX . "currency.ID
							, " . FF_PREFIX . "currency.name
						FROM " . FF_PREFIX . "currency 
						ORDER BY " . FF_PREFIX . "currency.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
$oGrid->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "permalink";
$oField->label = ffTemplate::_get_word_by_code("place_state_permalink");
$oGrid->addContent($oField);		

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "city";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/city?ID_state=[ID_VALUE]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("place_city");
$oButton->icon = cm_getClassByFrameworkCss("building", "icon-tag");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "province";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/province?ID_state=[ID_VALUE]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("place_province");
$oButton->icon = cm_getClassByFrameworkCss("map-o", "icon-tag");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "region";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/region?ID_state=[ID_VALUE]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("place_region");
$oButton->icon = cm_getClassByFrameworkCss("globe", "icon-tag");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oField = ffField::factory($cm->oPage);
$oField->id = "error";
$oField->label = ffTemplate::_get_word_by_code("place_error");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("place"), new ffData(ffTemplate::_get_word_by_code("place_relation_noset")))
	, array(new ffData("coord"), new ffData(ffTemplate::_get_word_by_code("geolocation_noset")))
    , array(new ffData("duplicate"), new ffData(ffTemplate::_get_word_by_code("place_duplicate")))
	, array(new ffData("noerror"), new ffData(ffTemplate::_get_word_by_code("no")))
);
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "permalink";
$oField->label = ffTemplate::_get_word_by_code("place_permalink");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("permalink"), new ffData(ffTemplate::_get_word_by_code("yes")))
	, array(new ffData("nopermalink"), new ffData(ffTemplate::_get_word_by_code("no")))
);
$oGrid->addSearchField($oField);	

$cm->oPage->addContent($oGrid); 