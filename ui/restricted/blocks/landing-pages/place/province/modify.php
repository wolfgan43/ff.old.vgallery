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

check_function("system_ffcomponent_set_title");
	
$res = system_ffComponent_resolve_record(FF_SUPPORT_PREFIX . "province");

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PlaceProvinceModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = FF_SUPPORT_PREFIX . "province";
$oRecord->tab = true;
$oRecord->addEvent("on_do_action", "PlaceProvinceModify_on_do_action");
$oRecord->addEvent("on_done_action", "PlaceProvinceModify_on_done_action");

 /* Title Block */
system_ffcomponent_set_title(
    $res["name"]
    , true
    , false
    , false
    , $oRecord
);    

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$group_field = "general";
$oRecord->addContent(null, true, $group_field); 
$oRecord->groups[$group_field] = array(
									"title" => ffTemplate::_get_word_by_code("place_" . $group_field)
								 );  


$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("place_province_name");
$oField->required = true;
$oField->setWidthComponent(5);
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "sigle";
$oField->label = ffTemplate::_get_word_by_code("place_province_sigle");
$oField->required = true;
$oField->setWidthComponent(2);
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("place_province_smart_url");
$oField->widget = "slug";
$oField->slug_title_field = "name";
$oField->required = true;
$oField->setWidthComponent(5);
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_state";
$oField->label = ffTemplate::_get_word_by_code("place_state");
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
							, " . FF_SUPPORT_PREFIX . "state.name
						FROM " . FF_SUPPORT_PREFIX . "state
						[WHERE]
						ORDER BY " . FF_SUPPORT_PREFIX . "state.name";
$oField->required = true;
$oField->actex_dialog_url = FF_SITE_PATH . VG_SITE_RESTRICTED . "/place/state/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->resources[] = "PlaceStateModify";
$oField->actex_child = "ID_region";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_region";
$oField->label = ffTemplate::_get_word_by_code("place_region");
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->source_SQL = "SELECT " . FF_SUPPORT_PREFIX . "region.ID
							, " . FF_SUPPORT_PREFIX . "region.name
							, " . FF_SUPPORT_PREFIX . "region.ID_state
						FROM " . FF_SUPPORT_PREFIX . "region
						[WHERE]
						ORDER BY " . FF_SUPPORT_PREFIX . "region.name";
$oField->required = true;
$oField->actex_dialog_url = FF_SITE_PATH . VG_SITE_RESTRICTED . "/place/region/modify"; 
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
//$oField->actex_dialog_add_params = array("ID_state" => "[ID_FAHTER]");
$oField->resources[] = "PlaceRegionModify";
$oField->actex_father = "ID_state";
$oField->actex_related_field = "ID_state";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "coord";
$oField->label = ffTemplate::_get_word_by_code("place_province_coord");
$oField->widget = "gmap";
$oField->gmap_start_zoom = 9;
if(check_function("set_field_gmap")) { 
	$oField = set_field_gmap($oField);
}
$oRecord->addContent($oField, $group_field);

/*
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_zone";
$oField->label = ffTemplate::_get_word_by_code("place_state_zone");
$oField->base_type = "Number";
$oRecord->addContent($oField, $group_field);
*/

$group_field = "landingpage";
$oRecord->addContent(null, true, $group_field); 
$oRecord->groups[$group_field] = array(
									"title" => ffTemplate::_get_word_by_code("place_" . $group_field)
								 );  

$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = ffTemplate::_get_word_by_code("place_province_visible");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "permalink";
$oField->label = ffTemplate::_get_word_by_code("landing_page_permalink");
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "h1";
$oField->label = ffTemplate::_get_word_by_code("landing_page_h1");
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "pre_content";
$oField->label = ffTemplate::_get_word_by_code("landing_page_pre_content");
$oField->extended_type = "Text";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "post_content";
$oField->label = ffTemplate::_get_word_by_code("landing_page_post_content");
$oField->extended_type = "Text";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, $group_field);


$group_field = "seo";
$oRecord->addContent(null, true, $group_field); 
$oRecord->groups[$group_field] = array(
									"title" => ffTemplate::_get_word_by_code("place_" . $group_field)
								 );  

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_title";
$oField->label = ffTemplate::_get_word_by_code("seo_meta_title");
$oField->placeholder = false;
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_description";
$oField->label = ffTemplate::_get_word_by_code("seo_meta_description");
$oField->placeholder = false;
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_robots";
$oField->label = ffTemplate::_get_word_by_code("seo_meta_robots");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
	                        array(new ffData("noindex, follow"), new ffData("noindex, follow")),
	                        array(new ffData("noindex, nofollow"), new ffData("noindex, nofollow")),
	                        array(new ffData("index, nofollow"), new ffData("index, nofollow"))
	                    );
$oField->multi_select_one_label = "index, follow";
$oField->placeholder = false;
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "meta";
$oField->label = ffTemplate::_get_word_by_code("seo_meta");
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->control_type = "textarea";
$oField->placeholder = false;
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "keywords";
$oField->label = ffTemplate::_get_word_by_code("seo_keywords");
$oField->source_SQL = "SELECT search_tags.name
	                        , search_tags.name 
	                    FROM search_tags
	                    WHERE search_tags.ID_lang = " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
	                        [AND] [WHERE] 
	                    [ORDER] [COLON] search_tags.name
	                    [LIMIT]";
$oField->widget = "autocomplete";
$oField->multi_select_one = false;
$oField->actex_update_from_db = true;
$oField->autocomplete_multi = true;
$oField->autocomplete_readonly = false;
$oField->autocomplete_minLength = 0;
$oField->autocomplete_combo = true; 
$oField->autocomplete_compare = "name";
$oField->grouping_separator = ",";
$oField->placeholder = false;
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "httpstatus";
$oField->label = ffTemplate::_get_word_by_code("seo_httpstatus");
$oField->extended_type = "Selection";
$oField->multi_pairs = ffGetHTTPStatus();
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default");
$oField->placeholder = false;
$oRecord->addContent($oField, $group_field);

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_canonical";
$oField->label = ffTemplate::_get_word_by_code("seo_meta_canonical");
$oField->placeholder = false;
$oRecord->addContent($oField, $group_field);

$cm->oPage->addContent($oRecord);

function PlaceProvinceModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
	
	switch($action) {
        case "insert":
            $sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.*
                    FROM " . FF_SUPPORT_PREFIX . "province
                    WHERE " . FF_SUPPORT_PREFIX . "province.smart_url = " . $db->toSql($component->form_fields["smart_url"]->value);
            $db->query($sSQL);
            if($db->nextRecord()) {
                $component->tplDisplayError(ffTemplate::_get_word_by_code("duplicate_province_smart_url"));
                return true;
            }
            break;
        case "update":
            $sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.*
                    FROM " . FF_SUPPORT_PREFIX . "province
                    WHERE " . FF_SUPPORT_PREFIX . "province.smart_url = " . $db->toSql($component->form_fields["smart_url"]->value) . "
                        AND " . FF_SUPPORT_PREFIX . "province.ID <> " . $db->toSql($component->key_fields["ID"]->value);
            $db->query($sSQL);
            if($db->nextRecord()) {
                $ID_province = $db->getField("ID", "Number", true);
                $province_name = $db->getField("name", "Text", true);
                $province_sigle = $db->getField("sigle", "Text", true);
                $province_smart_url = $db->getField("smart_url", "Text", true);
                
                $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "city SET ID_province = " . $db->toSql($ID_province, "Number") . "
                            , province_name = " . $db->toSql($province_name) . "
                                , province_sigle = " . $db->toSql($province_sigle) . "
                                , province_smart_url = " . $db->toSql($province_smart_url) . "
                        WHERE " . FF_SUPPORT_PREFIX . "city.ID_province = " . $db->toSql($component->key_fields["ID"]->value);
                $db->execute($sSQL);

                $component->form_fields["smart_url"]->setValue($component->form_fields["smart_url"]->getValue() . "-" . $component->key_fields["ID"]->getValue());
                $component->form_fields["name"]->setValue($component->form_fields["name"]->getValue() . "-duplicate");
            }
            break;
        default:
    }
}


function PlaceProvinceModify_on_done_action($component, $action) {
	$db = ffDB_Sql::factory();
	
	switch($action) {
		case "insert":
		case "update":
            check_function("get_coords_by_address");

            set_province_by_address_info(array(
                        "lat" => $component->form_fields["coord"]->value["lat"]->getValue()
                        , "lng" => $component->form_fields["coord"]->value["lng"]->getValue()
                    )
                    , $component->key_fields["ID"]->getValue()
                );
			break;
		default:
	}
}