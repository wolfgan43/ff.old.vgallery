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

if(isset($_REQUEST["repair"])) {
	$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.*
            FROM " . FF_SUPPORT_PREFIX . "province
            WHERE (ID_state = 0 OR ID_region = 0 OR sigle = '')";
    $db->query($sSQL);
    if($db->nextRecord()) {
        check_function("get_coords_by_address");
        do {
            $address = "";
            if($db->getField("coord_lat", "Number", true) && $db->getField("coord_lng", "Number", true)) {
                $address = array(
                    "lat" => $db->getField("coord_lat", "Number", true)
                    , "lng" => $db->getField("coord_lng", "Number", true)
                );
            } else {
                $address = $db->getField("name", "Text", true);
            }
            if($address) {
                set_province_by_address_info($address
                    , $db->getField("ID", "Number", true)
                );            
            }
        } while($db->nextRecord());
    }
}

$arrWhere = array();
$sSQL_Where = "";

/**
* Place Province
*/
if($_REQUEST["ID_state"])
	$arrWhere[] = FF_SUPPORT_PREFIX . "province.ID_state = " . $db->toSql($_REQUEST["ID_state"], "Number", true);
if($_REQUEST["ID_region"])
	$arrWhere[] = FF_SUPPORT_PREFIX . "province.ID_region = " . $db->toSql($_REQUEST["ID_region"], "Number", true);
	
if($_REQUEST["PlaceProvince_permalink_src"]) {
	switch($_REQUEST["PlaceProvince_permalink_src"]) {
		case "permalink":
			$arrWhere[] = "permalink <> ''";
			break;
		case "nopermalink":
			$arrWhere[] = "permalink = ''";

			break;
		default:		
	}

	unset($_REQUEST["PlaceProvince_permalink_src"]);
}

if($_REQUEST["PlaceProvince_error_src"]) {
	switch($_REQUEST["PlaceProvince_error_src"]) {
		case "place":
			$arrWhere[] = "(ID_state = 0 OR ID_region = 0 OR sigle = '')";
			break;
		case "coord":
			$arrWhere[] = "coord_title = ''";
			break;
		case "duplicate":
			$arrWhere[] = "LOCATE('-duplicate', name)";
			break;
		case "noerror":
			$arrWhere[] = "ID_state > 0 AND ID_region > 0 AND sigle != '' AND coord_title != '')";
			break;
		default:		
	}

	unset($_REQUEST["PlaceProvince_error_src"]);
}

if(is_array($arrWhere) && count($arrWhere)) {
	$sSQL_Where = " WHERE " . implode(" AND ", $arrWhere) . " [AND]";
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "PlaceProvince";
$oGrid->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "province.* 
						FROM " . FF_SUPPORT_PREFIX . "province
						$sSQL_Where
						[WHERE] 
						[HAVING] 
						[ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->display_new = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "PlaceProvinceModify";
$oGrid->resources[] = $oGrid->record_id;


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("place_province");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sigle";
$oField->label = ffTemplate::_get_word_by_code("place_province_sigle");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_region";
$oField->label = ffTemplate::_get_word_by_code("place_region");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "region.ID
							, " . FF_SUPPORT_PREFIX . "region.name
						FROM " . FF_SUPPORT_PREFIX . "region
						ORDER BY " . FF_SUPPORT_PREFIX . "region.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
$oGrid->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_state";
$oField->label = ffTemplate::_get_word_by_code("place_state");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
							, " . FF_SUPPORT_PREFIX . "state.name
						FROM " . FF_SUPPORT_PREFIX . "state
						ORDER BY " . FF_SUPPORT_PREFIX . "state.name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "permalink";
$oField->label = ffTemplate::_get_word_by_code("place_province_permalink");
$oGrid->addContent($oField);	

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "city";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/city?ID_province=[ID_VALUE]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("place_city");
$oButton->icon = Cms::getInstance("frameworkcss")->get("building", "icon-tag");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oField = ffField::factory($cm->oPage);
$oField->id = "error";
$oField->label = ffTemplate::_get_word_by_code("place_error");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	array(new ffData("place"), new ffData(ffTemplate::_get_word_by_code("place_relation_noset")))
	, array(new ffData("coord"), new ffData(ffTemplate::_get_word_by_code("geolocation_noset")))
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