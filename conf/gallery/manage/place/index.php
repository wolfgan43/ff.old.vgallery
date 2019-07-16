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

if(isset($_REQUEST["repair"]))
{
    repair_langing_places("city");
    repair_langing_places("province");
    repair_langing_places("region");
    repair_langing_places("state");
}

if(isset($_REQUEST["setvisible"]) && $_REQUEST["type"] && $_REQUEST["keys"]["ID"] > 0)
{
    repair_langing_place($_REQUEST["keys"]["ID"], $_REQUEST["type"], $_REQUEST["setvisible"]);

    if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("Place" . ucfirst(strtolower($type)) . "Modify")), true));
    } else {
    //    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("VGalleryModify")), true));

        ffRedirect(($_REQUEST["ret_url"] ? $_REQUEST["ret_url"] : $_SERVER["HTTP_REFERER"]));
    }
}


function repair_langing_places($place)
{
    $db = ffDB_Sql::factory();
    $sSQL = "SELECT " . FF_SUPPORT_PREFIX . $place .".*
            FROM " . FF_SUPPORT_PREFIX . $place . "
            WHERE " . FF_SUPPORT_PREFIX . $place . ".permalink <> ''
                AND " . FF_SUPPORT_PREFIX . $place . ".meta_title = ''";
    $db->query($sSQL);
    if($db->nextRecord())
    {
        do
        {
            repair_langing_place($db->getField("ID", "Number", true), $place, true);
        } while($db->nextRecord());
    }
}

function repair_langing_place($ID, $place, $visible = null)
{
    $db = ffDB_Sql::factory();

    switch($place)
    {
        case "city":
            $tbl = FF_SUPPORT_PREFIX . "city";
            $type = "CITY";
            break;
        case "province":
            $tbl = FF_SUPPORT_PREFIX . "province";
            $type = "PROVINCE";
            break;
        case "region":
            $tbl = FF_SUPPORT_PREFIX . "region";
            $type = "REGION";
            break;
        case "state":
            $tbl = FF_SUPPORT_PREFIX . "state";
            $type = "STATE";
            break;
        default:
    }

    if($tbl)
    {
        $sSQL = "SELECT " . $tbl . ".*
                    FROM " . $tbl . "
                     WHERE ID = " . $db->toSql($ID, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $place_name = $db->getField("name", "Text", true);
            $place_smart_url = $db->getField("smart_url", "Text", true);

            $query["update"] = array(
                "visible"                   => " visible = " . $db->toSql($visible === null ? " IF(visible, 0, 1) " : $visible)
            , "permalink"                   => " permalink = IF(permalink = '', " . $db->tosql(str_replace(
                        array("[PLACE]", "[PLACE_SMART_URL]")
                        , array($place_name, $place_smart_url)
                        , Cms::env("PLACE_" . $type . "_PERMALINK_PROTOTYPE")
                    )) . ", permalink) "
            , "meta_title"                  => " meta_title = IF(meta_title = '', " . $db->tosql(str_replace(
                        array("[PLACE]", "[PLACE_SMART_URL]")
                        , array($place_name, $place_smart_url)
                        , Cms::env("PLACE_" . $type . "_TITLE_PROTOTYPE")
                    )) . ", meta_title) "
            , "meta_description"            => " meta_description = IF(meta_description = '', " . $db->tosql(str_replace(
                        array("[PLACE]", "[PLACE_SMART_URL]")
                        , array($place_name, $place_smart_url)
                        , Cms::env("PLACE_" . $type . "_DESCRIPTION_PROTOTYPE")
                    )) . ", meta_description) "
            , "h1"                          => " h1 = IF(h1 = '', " . $db->tosql(str_replace(
                        array("[PLACE]", "[PLACE_SMART_URL]")
                        , array($place_name, $place_smart_url)
                        , Cms::env("PLACE_" . $type . "_HEADER_PROTOTYPE")
                    )) . ", h1) "
            );


            $sSQL = "UPDATE " . $tbl . " SET
                        " . implode(", ", $query["update"]) . "
                        WHERE ID = " . $db->toSql($ID, "Number");
            $db->execute($sSQL);
        }
    }
}


check_function("system_ffcomponent_set_title");
check_function("get_vgallery_card");

if(system_ffcomponent_switch_by_path(__DIR__)) {
    $cm->oPage->addContent(null, true, "rel");

    /**
     * Title
     */
    system_ffcomponent_set_title(
        ffTemplate::_get_word_by_code("place_title")
        , true
        , false
        , false
    );

    /**
     * Place State
     */
    $arrWhere = array();
    $sSQL_Where = "";
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
            case "noseo":
                $arrWhere[] = "permalink != '' AND meta_title = ''";
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
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/state/modify";
    $oGrid->record_id = "PlaceStateModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->addEvent("on_before_parse_row", "Place_on_before_parse_row");


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
    $oButton->id = "visible";
    $oButton->ajax = true;
    $oButton->action_type = "gotourl";
    $oButton->frmAction = "setvisible";
    $oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . "?[KEYS]type=state&";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "city";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/city?ID_state=[ID_VALUE]";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("place_city");
    $oButton->icon = Cms::getInstance("frameworkcss")->get("building", "icon-tag");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "province";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/province?ID_state=[ID_VALUE]";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("place_province");
    $oButton->icon = Cms::getInstance("frameworkcss")->get("map-o", "icon-tag");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "region";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/region?ID_state=[ID_VALUE]";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("place_region");
    $oButton->icon = Cms::getInstance("frameworkcss")->get("globe", "icon-tag");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "error";
    $oField->label = ffTemplate::_get_word_by_code("place_error");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array(
        array(new ffData("place"), new ffData(ffTemplate::_get_word_by_code("place_relation_noset")))
    , array(new ffData("coord"), new ffData(ffTemplate::_get_word_by_code("geolocation_noset")))
    , array(new ffData("duplicate"), new ffData(ffTemplate::_get_word_by_code("place_duplicate")))
    , array(new ffData("noseo"), new ffData(ffTemplate::_get_word_by_code("place_noseo")))
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

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("place_state")));

    /**
     * Place Region
     */
    $arrWhere = array();
    $sSQL_Where = "";
    if($_REQUEST["PlaceRegion_permalink_src"]) {
        switch($_REQUEST["PlaceRegion_permalink_src"]) {
            case "permalink":
                $arrWhere[] = "permalink <> ''";
                break;
            case "nopermalink":
                $arrWhere[] = "permalink = ''";

                break;
            default:
        }

        unset($_REQUEST["PlaceRegion_permalink_src"]);
    }

    if($_REQUEST["PlaceRegion_error_src"]) {
        switch($_REQUEST["PlaceRegion_error_src"]) {
            case "place":
                $arrWhere[] = "(ID_state = 0)";
                break;
            case "coord":
                $arrWhere[] = "coord_title = ''";
                break;
            case "duplicate":
                $arrWhere[] = "LOCATE('-duplicate', name)";
                break;
            case "noseo":
                $arrWhere[] = "permalink != '' AND meta_title = ''";
                break;
            case "noerror":
                $arrWhere[] = "ID_state > 0 AND coord_title != ''";
                break;
            default:
        }

        unset($_REQUEST["PlaceRegion_error_src"]);
    }

    if(is_array($arrWhere) && count($arrWhere)) {
        $sSQL_Where = " WHERE " . implode(" AND ", $arrWhere) . " [AND]";
    }

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->id = "PlaceRegion";
    $oGrid->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "region.* 
							FROM " . FF_SUPPORT_PREFIX . "region
							$sSQL_Where
							[WHERE]
							[HAVING] 
							[ORDER]";
    $oGrid->order_default = "name";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/region/modify";
    $oGrid->record_id = "PlaceRegionModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->addEvent("on_before_parse_row", "Place_on_before_parse_row");



    // Campi chiave
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);


    // Campi visualizzati
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("place_region");
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
    $oField->label = ffTemplate::_get_word_by_code("place_region_permalink");
    $oGrid->addContent($oField);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "visible";
    $oButton->ajax = true;
    $oButton->action_type = "gotourl";
    $oButton->frmAction = "setvisible";
    $oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . "?[KEYS]type=region&";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "city";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/city?ID_region=[ID_VALUE]";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("place_city");
    $oButton->icon = Cms::getInstance("frameworkcss")->get("building", "icon-tag");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "province";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/province?ID_region=[ID_VALUE]";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("place_province");
    $oButton->icon = Cms::getInstance("frameworkcss")->get("map-o", "icon-tag");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "error";
    $oField->label = ffTemplate::_get_word_by_code("place_error");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array(
        array(new ffData("place"), new ffData(ffTemplate::_get_word_by_code("place_relation_noset")))
    , array(new ffData("coord"), new ffData(ffTemplate::_get_word_by_code("geolocation_noset")))
    , array(new ffData("duplicate"), new ffData(ffTemplate::_get_word_by_code("place_duplicate")))
    , array(new ffData("noseo"), new ffData(ffTemplate::_get_word_by_code("place_noseo")))
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

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("place_region")));


    /**
     * Place Province
     */
    $arrWhere = array();
    $sSQL_Where = "";
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
            case "noseo":
                $arrWhere[] = "permalink != '' AND meta_title = ''";
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
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/province/modify";
    $oGrid->record_id = "PlaceProvinceModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->addEvent("on_before_parse_row", "Place_on_before_parse_row");



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
    $oButton->id = "visible";
    $oButton->ajax = true;
    $oButton->action_type = "gotourl";
    $oButton->frmAction = "setvisible";
    $oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . "?[KEYS]type=province&";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "city";
    $oButton->action_type = "gotourl";
    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/city?ID_province=[ID_VALUE]";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("place_city");
    $oButton->icon = Cms::getInstance("frameworkcss")->get("building", "icon-tag");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "error";
    $oField->label = ffTemplate::_get_word_by_code("place_error");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array(
        array(new ffData("place"), new ffData(ffTemplate::_get_word_by_code("place_relation_noset")))
    , array(new ffData("coord"), new ffData(ffTemplate::_get_word_by_code("geolocation_noset")))
    , array(new ffData("duplicate"), new ffData(ffTemplate::_get_word_by_code("place_duplicate")))
    , array(new ffData("noseo"), new ffData(ffTemplate::_get_word_by_code("place_noseo")))
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

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("place_province")));


    /**
     * Place City
     */
    $arrWhere = array();
    $sSQL_Where = "";
    if($_REQUEST["PlaceCity_permalink_src"]) {
        switch($_REQUEST["PlaceCity_permalink_src"]) {
            case "permalink":
                $arrWhere[] = "permalink <> ''";
                break;
            case "nopermalink":
                $arrWhere[] = "permalink = ''";

                break;
            default:
        }

        unset($_REQUEST["PlaceCity_permalink_src"]);
    }

    if($_REQUEST["PlaceCity_error_src"]) {
        switch($_REQUEST["PlaceCity_error_src"]) {
            case "place":
                $arrWhere[] = "(ID_state = 0 OR ID_province = 0 OR ID_region = 0 OR province_name = '' OR province_sigle = '')";
                break;
            case "coord":
                $arrWhere[] = "coord_title = ''";
                break;
            case "duplicate":
                $arrWhere[] = "LOCATE('-duplicate', name)";
                break;
            case "noseo":
                $arrWhere[] = "permalink != '' AND meta_title = ''";
                break;
            case "noerror":
                $arrWhere[] = "ID_state > 0 AND ID_province > 0 AND ID_region > 0 AND province_name != '' AND province_sigle != '' AND coord_title != ''";
                break;
            default:
        }

        unset($_REQUEST["PlaceCity_error_src"]);
    }

    if(is_array($arrWhere) && count($arrWhere)) {
        $sSQL_Where = " WHERE " . implode(" AND ", $arrWhere) . " [AND]";
    }

    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->id = "PlaceCity";
    $oGrid->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "city.* 
                                , (SELECT COUNT(vgallery_nodes.ID) 
                                    FROM vgallery_nodes 
                                    WHERE vgallery_nodes.ID_place = " . FF_SUPPORT_PREFIX . "city.ID
                                ) AS count_place
                                , (SELECT COUNT(module_maps_marker.ID) 
                                    FROM module_maps_marker 
                                    WHERE module_maps_marker.ID_city = " . FF_SUPPORT_PREFIX . "city.ID
                                ) AS count_marker
							FROM " . FF_SUPPORT_PREFIX . "city
							$sSQL_Where
							[WHERE]
							[HAVING] 
							[ORDER]";
    $oGrid->order_default = "name";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/city/modify";
    $oGrid->record_id = "PlaceCityModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->addEvent("on_before_parse_row", "Place_on_before_parse_row");


    // Campi chiave
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);


    // Campi visualizzati
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("place_city");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "province_sigle";
    $oField->label = ffTemplate::_get_word_by_code("place_sigle");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_province";
    $oField->label = ffTemplate::_get_word_by_code("place_province");
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "province.ID
								, " . FF_SUPPORT_PREFIX . "province.name
							FROM " . FF_SUPPORT_PREFIX . "province
							ORDER BY " . FF_SUPPORT_PREFIX . "province.name";
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
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
    $oField->id = "count_place";
    $oField->label = ffTemplate::_get_word_by_code("place_count");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "count_marker";
    $oField->label = ffTemplate::_get_word_by_code("place_marker");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "permalink";
    $oField->label = ffTemplate::_get_word_by_code("place_city_permalink");
    $oGrid->addContent($oField);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "visible";
    $oButton->ajax = true;
    $oButton->action_type = "gotourl";
    $oButton->frmAction = "setvisible";
    $oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . "?[KEYS]type=city&";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("status_frontend");
    $oButton->display = false;
    $oGrid->addGridButton($oButton);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "error";
    $oField->label = ffTemplate::_get_word_by_code("place_error");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array(
        array(new ffData("place"), new ffData(ffTemplate::_get_word_by_code("place_relation_noset")))
    , array(new ffData("coord"), new ffData(ffTemplate::_get_word_by_code("geolocation_noset")))
    , array(new ffData("duplicate"), new ffData(ffTemplate::_get_word_by_code("place_duplicate")))
    , array(new ffData("noseo"), new ffData(ffTemplate::_get_word_by_code("place_noseo")))
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

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("place_city")));



    function Place_on_before_parse_row ($component)
    {
        if(isset($component->grid_buttons["visible"])) {
            if($component->db[0]->getField("permalink", "Text", true) && $component->db[0]->getField("visible", "Number", true)) {
                $component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=0";
                $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
            } else {
                $component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=1";
                $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
            }
        }
    }
}



