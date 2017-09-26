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


if (!AREA_MODULES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_form", array(
	"enable_pricelist" => "IF(field_enable_pricelist
		                , (SELECT COUNT(module_form_fields.ID) AS count_pricelist
		                    FROM module_form_fields
		                    WHERE module_form_fields.ID_module = module_form.ID
		                        AND module_form_fields.`type` = 'pricelist'
		                )
		                , 0
		            )"
	, "field_enable_dep" => null
	, "enable_ecommerce" => null
));
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_form";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_form.*
									, IF(module_form.display_name = ''
										, REPLACE(module_form.name, '-', ' ')
										, module_form.display_name
									) AS display_name
								FROM module_form 
								WHERE module_form.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                               
$oRecord->addEvent("on_do_action", "FormExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "FormExtraFieldModify_on_done_action");

$oRecord->buttons_options["delete"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$module_form_title = ffTemplate::_get_word_by_code("module_form_fields_title");

/* Title Block */
system_ffcomponent_set_title(
	$module_form_title
	, true
	, false
	, false
	, $oRecord
);     

if(AREA_SHOW_ECOMMERCE && $record["enable_ecommerce"]) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "skip_vat_by_anagraph_type";
	$oField->label = ffTemplate::_get_word_by_code("form_config_skip_vat_by_anagraph_type");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oField->default_value = new ffData("0", "Number");
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "skip_shipping_calc";
	$oField->label = ffTemplate::_get_word_by_code("form_config_skip_shipping_calc");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oField->default_value = new ffData("1", "Number");
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "discount_perc";
	$oField->label = ffTemplate::_get_word_by_code("form_config_discount_perc");
	$oField->base_type = "Number";
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "discount_val";
	$oField->label = ffTemplate::_get_word_by_code("form_config_discount_val");
	$oField->base_type = "Number";
	$oField->app_type = "Currency";
	$oRecord->addContent($oField);
}	

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->ajax_addnew = true;
$oGrid->ajax_delete = true;
$oGrid->ajax_search = true;
$oGrid->dialog_action_button = true;
$oGrid->id = "FormConfigField";
$oGrid->source_SQL = "SELECT module_form_fields.* 
                            , module_form_fields_group.name AS group_name
                            , module_form_fields_group.`order` AS group_order
                            FROM module_form_fields
                            LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                        WHERE module_form_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                            [AND] [WHERE] 
                        [HAVING] 
                        [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = false;
$oGrid->use_order = false;
$oGrid->use_paging = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]";
$oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[name_VALUE]";
$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/add";
$oGrid->record_id = "FormExtraFieldModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->buttons_options["export"]["display"] = false;
$oGrid->widget_deps[] = array(
    "name" => "dragsort"
    , "options" => array(
          &$oGrid
        , array(
            "resource_id" => "form_fields"
            , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
        )
        , "ID"
    )
);
$oGrid->addEvent("on_before_parse_row", "FormConfigField_on_before_parse_row");
//$oGrid->addEvent("on_do_action", "FormExtraFieldModify_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oField->order_SQL = " `group_order`, `order`, name";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "group_name";
$oField->container_class = "group";
$oField->label = ffTemplate::_get_word_by_code("form_field_group");
$oField->base_type = "Text";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("form_field_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->container_class = "field-type";
$oField->label = ffTemplate::_get_word_by_code("form_field_type");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
							array(new ffData(""), new ffData(ffTemplate::_get_word_by_code("form_fields_type_simple")))
							, array(new ffData("price"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_price")))
							, array(new ffData("multiplier"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_multiplier")))
							, array(new ffData("pricelist"), new ffData(ffTemplate::_get_word_by_code("form_fields_type_pricelist")))
						);
$oField->multi_select_one = false;
$oGrid->addContent($oField); 

if($record["field_enable_dep"]) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->ajax = $oGrid->record_id;
	$oButton->id = "module_form_dep";
	$oButton->aspect = "link"; 
	$oButton->label = ffTemplate::_get_word_by_code("module_form_dep_rule_title");
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/deps";
	$oGrid->addActionButtonHeader($oButton);
}

if(AREA_SHOW_ECOMMERCE && $record["enable_pricelist"])
{
	$oButton = ffButton::factory($cm->oPage);
	$oButton->ajax = $oGrid->record_id;
	$oButton->id = "module_form_pricelist";
	$oButton->aspect = "link"; 
	$oButton->label = ffTemplate::_get_word_by_code("module_form_pricelist_title");
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/pricelist";
	$oGrid->addActionButtonHeader($oButton);
}    

$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);  

$cm->oPage->addContent($oRecord);

function FormConfigField_on_before_parse_row($component) {

}

function FormExtraFieldModify_on_do_action($component, $action) {
    
}


function FormExtraFieldModify_on_done_action($component, $action) {
    switch($action) {
        case "update":
        	if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("FormExtraFieldModify")), true));
			} else {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
			}
        	
        	break;
        case "confirmdelete":
        	if(check_function("MD_form_delete"))
        		MD_form_delete($component->key_fields["ID"]->getValue());

        	if(isset($_REQUEST["name"]) || isset($_REQUEST["noredirect"])) {
        		die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("FormExtraFieldModify")), true));
			} else {
				die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "doredirects" => true), true));
			}
        	break;
        default:
    }
    return true;
}