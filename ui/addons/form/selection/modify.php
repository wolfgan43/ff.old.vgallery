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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

system_ffComponent_resolve_record("module_form_fields_selection");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormConfigSelectionModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_form_fields_selection";
if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

/* Title Block */
	system_ffcomponent_set_title(
		null
		, true
		, false
		, false
		, $oRecord
	);		
	
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name");
$oField->required = true;
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "selectionSource";
$oField->label = ffTemplate::_get_word_by_code("form_selection_source");
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->base_type = "Text";
$oField->multi_pairs =  array (
                            array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph"))),
                            array(new ffData("user"), new ffData(ffTemplate::_get_word_by_code("user"))),
                            array(new ffData("vgallery"), new ffData(ffTemplate::_get_word_by_code("vgallery"))),
							array(new ffData("city"), new ffData(ffTemplate::_get_word_by_code("city"))),
                            array(new ffData("province"), new ffData(ffTemplate::_get_word_by_code("province"))),
                            array(new ffData("region"), new ffData(ffTemplate::_get_word_by_code("region"))),
							array(new ffData("state"), new ffData(ffTemplate::_get_word_by_code("state")))
                       );
$oField->actex_child = "field";
$oField->actex_update_from_db = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("addCustomSource");
$oField->actex_on_update_bt = 'function(obj, old_value) {
								if(obj["value"]) {
									jQuery("#form-config-selection-value").parents(".row").hide();
								} else {
									jQuery("#form-config-selection-value").parents(".row").show();
								}
							}';
$oRecord->addContent($oField);

$query_string = "SELECT nameID, name, type, group_name
					FROM
					(
						SELECT nameID, name, type, group_name FROM
						(
							(
								SELECT anagraph_fields.ID AS nameID
										, anagraph_fields.name AS name
										, anagraph_type.name AS group_name
										, " . $db->toSql("anagraph") . " AS type
									FROM anagraph_type
										INNER JOIN anagraph_fields ON anagraph_fields.ID_type = anagraph_type.ID
									WHERE 1
									ORDER BY anagraph_type.name,anagraph_fields.name
							) 
								UNION 
							(
								SELECT cm_mod_security_users_fields.ID AS nameID 
										, cm_mod_security_users_fields.field AS name
										, '' AS group_name
										, " . $db->toSql("user") . " AS type
								FROM cm_mod_security_users_fields
							)
								UNION
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("city") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql(FF_SUPPORT_PREFIX . "city") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("province") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql(FF_SUPPORT_PREFIX . "province") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION 
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("region") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql(FF_SUPPORT_PREFIX . "region") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION 
							(
								SELECT COLUMN_NAME COLLATE utf8_general_ci AS nameID 
									, COLUMN_NAME COLLATE utf8_general_ci AS name
									, '' AS group_name
									, " . $db->toSql("state") . " AS type
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql(FF_SUPPORT_PREFIX . "state") . "
									AND TABLE_SCHEMA =  '" . FF_DATABASE_NAME . "'
							)
								UNION
							(
								SELECT vgallery_fields.ID
									, vgallery_fields.name AS name
									, vgallery_type.name AS group_name
									, " . $db->toSql("vgallery") . " AS type
								FROM vgallery_fields 
									INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
								ORDER BY LOWER(vgallery_type.name),LOWER(vgallery_fields.name)
							)
						) AS tbl_src 
						ORDER BY type, name
					) AS macro_tbl
				[WHERE]
				ORDER BY macro_tbl.type, macro_tbl.name";


$oField = ffField::factory($cm->oPage);
$oField->id = "field";
$oField->label = ffTemplate::_get_word_by_code("form_selection_field"); 
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->source_SQL = $query_string;
$oField->actex_father = "selectionSource";
$oField->actex_related_field = "type";
$oField->actex_group = "group_name";
$oField->actex_hide_empty = true; 
$oField->actex_update_from_db = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_form_fields_father";
$oField->label = ffTemplate::_get_word_by_code("register_config_fields_father");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT ID, name FROM module_form_fields_selection WHERE ID <> " . $db->toSql($_REQUEST["keys"]["formsel-ID"], "Number") . " ORDER BY name";
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_form_fields_child";
$oField->label = ffTemplate::_get_word_by_code("register_config_fields_child");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT ID, name FROM module_form_fields_selection WHERE ID <> " . $db->toSql($_REQUEST["keys"]["formsel-ID"], "Number") . " ORDER BY name";
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

$oDetail_fields = ffDetails::factory($cm->oPage);
$oDetail_fields->id = "form-config-selection-value";
$oDetail_fields->title = ffTemplate::_get_word_by_code("form_selection_name_value");
$oDetail_fields->src_table = "module_form_fields_selection_value";
$oDetail_fields->order_default = "order";
$oDetail_fields->fields_relationship = array ("ID_selection" => "ID");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail_fields->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_name");
$oField->required = true;
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_order");
$oField->base_type = "Number";
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "qta";
$oField->label = ffTemplate::_get_word_by_code("form_fields_qta");
$oField->base_type = "Number";
$oField->default_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "price";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_price");
$oField->base_type = "Number";
$oField->app_type = "Currency";
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "vat";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name_value_vat");
$oField->base_type = "Number";
$oField->default_value = new ffData("20", "Number");
$oDetail_fields->addContent($oField);

$oRecord->addContent($oDetail_fields);
$cm->oPage->addContent($oDetail_fields);

