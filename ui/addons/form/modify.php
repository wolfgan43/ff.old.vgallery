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

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();
$globals = ffGlobals::getInstance("gallery");

if(is_array($globals->ecommerce["preview"]["vatTime"]) && count($globals->ecommerce["preview"]["vatTime"])) {
	foreach($globals->ecommerce["preview"]["vatTime"] AS $arrVatTime_key => $arrVatTime_value) {
		if(time() > $arrVatTime_key) {
			$actual_vat = $arrVatTime_value;
			break;
		}
	}
}

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_form", array(
	"name" => "IF(module_form.display_name = ''
				        , REPLACE(module_form.name, '-', ' ')
				        , module_form.display_name
				    )"
	, "enable_pricelist" => "IF(field_enable_pricelist
		                , (SELECT COUNT(module_form_fields.ID) AS count_pricelist
		                    FROM module_form_fields
		                    WHERE module_form_fields.ID_module = module_form.ID
		                        AND module_form_fields.`type` = 'pricelist'
		                )
		                , 0
		            )"
	, "field_enable_dep" => null
));

if(isset($_REQUEST["repair"])) {
	$sSQL = "UPDATE module_form_fields SET
				display_name = REPLACE(name, '-', ' ')
			WHERE display_name = '' AND name <> ''";
	$db->execute($sSQL);

	$sSQL = "SELECT module_form_fields.*
			FROM module_form_fields
			WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrVgalleryField[$db->getField("ID", "Number", true)] = ffCommon_url_rewrite($db->getField("name", "Text", true));
		} while($db->nextRecord());

		if(is_array($arrVgalleryField) && count($arrVgalleryField)) {
			foreach($arrVgalleryField AS $ID_field => $smart_url) {
				$sSQL = "UPDATE module_form_fields SET
							name = " . $db->toSql($smart_url) . "
						WHERE module_form_fields.ID = " . $db->toSql($ID_field, "Number");
				$db->execute($sSQL);				
			}
		}
	}
}



if($_REQUEST["keys"]["ID"] > 0 && $_REQUEST["frmAction"] == "clone") {
    if(check_function("MD_form_on_done_action"))
        MD_form_clone($_REQUEST["keys"]["ID"], $_REQUEST["clonename"]);

    //if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("FormConfigModify")), true));
   //} else {
    //    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("FormConfigModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
   // }
} 

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "FormConfigModify";
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
$oRecord->addEvent("on_do_action", "MD_form_config_on_do_action");

if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");
if(isset($_REQUEST["keys"]["ID"]))
	$oRecord->addEvent("on_done_action", "FormConfigField_on_done_action");

/* Title Block */
system_ffcomponent_set_title(
    $record["name"]
    , true
    , false
    , false
    , $oRecord
);     
    	
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$oRecord->addTab("Template");
$oRecord->setTabTitle("Template", ffTemplate::_get_word_by_code("form_config_template"));

$oRecord->addContent(null, true, "Template"); 
$oRecord->groups["Template"] = array(
	                             "title" => ffTemplate::_get_word_by_code("form_config_template")
	                             , "cols" => 1
	                             , "tab" => "Template"
	                          );

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("form_config_name");
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oField->container_class = "hidden";
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("form_config_name");
$oField->required = true;
$oRecord->addContent($oField, "Template");
	                          
$oField = ffField::factory($cm->oPage);
$oField->id = "show_title";
$oField->label = ffTemplate::_get_word_by_code("form_config_show_title");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "privacy";
$oField->label = ffTemplate::_get_word_by_code("form_config_privacy");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "require_note";
$oField->label = ffTemplate::_get_word_by_code("form_config_require_note");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_pre_content";
$oField->label = ffTemplate::_get_word_by_code("form_config_fixed_pre_content");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "fixed_post_content";
$oField->label = ffTemplate::_get_word_by_code("form_config_fixed_post_content");
$oField->extended_type = "Text";
$oRecord->addContent($oField, "Template");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_form_path";
$oField->class = "input advanced";
$oField->label = ffTemplate::_get_word_by_code("form_config_tpl_form_path");
$oRecord->addContent($oField, "Template"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "display_view_mode";
$oField->class = "input advanced";
$oField->label = ffTemplate::_get_word_by_code("form_config_display_view_mode");
$oRecord->addContent($oField, "Template");

$oRecord->addTab("Email");
$oRecord->setTabTitle("Email", ffTemplate::_get_word_by_code("form_config_email"));

$oRecord->addContent(null, true, "Email"); 
$oRecord->groups["Email"] = array(
	                             "title" => ffTemplate::_get_word_by_code("form_config_email")
	                             , "cols" => 1
	                             , "tab" => "Email"
	                          );
$oField = ffField::factory($cm->oPage);
$oField->id = "send_mail";
$oField->label = ffTemplate::_get_word_by_code("form_config_send_mail");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Email");

$oField = ffField::factory($cm->oPage);
$oField->id = "send_copy_to_guest";
$oField->label = ffTemplate::_get_word_by_code("form_config_send_copy_to_guest");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Email");

$oField = ffField::factory($cm->oPage);
$oField->id = "force_from_with_domclass";
$oField->class = "input advanced";
$oField->label = ffTemplate::_get_word_by_code("form_config_force_FROM_with_domclass");
$oRecord->addContent($oField, "Email");

$oField = ffField::factory($cm->oPage);
$oField->id = "force_to_with_user";
$oField->class = "input advanced";
$oField->label = ffTemplate::_get_word_by_code("form_config_force_TO_with_user");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Email");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_email";
$oField->label = ffTemplate::_get_word_by_code("form_fields_email");
$oField->base_type = "Number";
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT  ID, name FROM email";
$oField->actex_dialog_url = $cm->oPage->site_path . STAGE_ADMIN . "/utility/email/modify";
$oField->actex_dialog_edit_params = array("keys[email-ID]" => $oRecord->id . "_" . $oField->id);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=EmailModify_confirmdelete";
$oField->resources[] = "EmailModify";
$oRecord->addContent($oField, "Email");

$oRecord->addTab("Report");
$oRecord->setTabTitle("Report", ffTemplate::_get_word_by_code("form_config_report"));

$oRecord->addContent(null, true, "Report"); 
$oRecord->groups["Report"] = array(
	                             "title" => ffTemplate::_get_word_by_code("form_config_report")
	                             , "cols" => 1
	                             , "tab" => "Report"
	                          );

$oField = ffField::factory($cm->oPage);
$oField->id = "report";
$oField->label = ffTemplate::_get_word_by_code("form_config_report");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Report");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_report_path";
$oField->class = "input advanced";
$oField->label = ffTemplate::_get_word_by_code("form_config_tpl_report_path");
$oRecord->addContent($oField, "Report");

$oField = ffField::factory($cm->oPage);
$oField->id = "force_redirect";
$oField->class = "input advanced";
$oField->label = ffTemplate::_get_word_by_code("form_config_action");
$oRecord->addContent($oField, "Report");

$oRecord->addTab("Revision");
$oRecord->setTabTitle("Revision", ffTemplate::_get_word_by_code("form_config_revision"));

$oRecord->addContent(null, true, "Revision"); 
$oRecord->groups["Revision"] = array(
	                             "title" => ffTemplate::_get_word_by_code("form_config_revision")
	                             , "cols" => 1
	                             , "tab" => "Revision"
	                          );

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_revision";
$oField->label = ffTemplate::_get_word_by_code("form_config_enable_revision");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Revision");
	                          


$oField = ffField::factory($cm->oPage);
$oField->id = "limit_by_groups";
$oField->label = ffTemplate::_get_word_by_code("form_config_groups");
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT DISTINCT gid, IF(name='" . MOD_SEC_GUEST_GROUP_NAME . "', 'default', name) FROM " . CM_TABLE_PREFIX . "mod_security_groups ORDER BY name";
$oField->control_type = "input";
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "Revision");

/***
*  FIELDS
*/
$oRecord->addTab("Field");
$oRecord->setTabTitle("Field", ffTemplate::_get_word_by_code("form_config_field"));


if($_REQUEST["keys"]["ID"]) {
	$oRecord->addContent(null, true, "Field"); 
	$oRecord->groups["Field"] = array(
	                                 "title" => ffTemplate::_get_word_by_code("form_config_field")
	                                 , "cols" => 1
	                                 , "tab" => "Field"
	                              );
	                              
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->ajax_addnew = true;
	$oGrid->ajax_delete = true;
	$oGrid->ajax_search = true;
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
	$oField->id = "aspect";
	$oField->container_class = "aspect";
	$oField->label = ffTemplate::_get_word_by_code("form_field_aspect");
	$oField->base_type = "Text";
	$oField->data_type = "";
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
            

	$oRecord->addContent($oGrid, "Field");
	$cm->oPage->addContent($oGrid);
}

/**
* Fields Default Settings
*/
$oRecord->addTab("DefaultField");
$oRecord->setTabTitle("DefaultField", ffTemplate::_get_word_by_code("form_config_fields_settings"));

$oRecord->addContent(null, true, "DefaultField"); 
$oRecord->groups["DefaultField"] = array(
                                 "title" => ffTemplate::_get_word_by_code("form_config_fields_settings")
                                 , "cols" => 1
                                 , "tab" => "DefaultField"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "field_enable_dep";
$oField->label = ffTemplate::_get_word_by_code("form_field_enable_dep");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE); 
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_enable_pricelist";
$oField->label = ffTemplate::_get_word_by_code("form_field_enable_pricelist");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "DefaultField");
                              
$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_ID_form_fields_group";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_group");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_ID_extended_type";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_extended_type");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT CONCAT('show_no_default_', ID) AS ID
                            , CONCAT(" . $db->toSql(ffTemplate::_get_word_by_code("form_fields_show_no_default") . " ") . ", name) AS name
                        FROM extended_type 
                        ORDER BY name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_hide_label";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_hide_label");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_0");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_placeholder";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_placeholder");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_0");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_disable_free_input";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_disable_free_input");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_0");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_vgallery_field";
$oField->label = ffTemplate::_get_word_by_code("form_fields_vgallery_field");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_disable_select_one";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_disable_select_one");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_0");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_val_min";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_val_min");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_val_max";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_val_max");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_val_step";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_val_step");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_require";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_require");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_ID_check_control";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_check_control");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT CONCAT('show_no_default_', ID) AS ID
                            , CONCAT(" . $db->toSql(ffTemplate::_get_word_by_code("form_fields_show_no_default") . " ") . ", name) AS name
                        FROM check_control 
                        ORDER BY name";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_unic_value";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_unic_value");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_0");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_send_mail";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_send_mail");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_1");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_enable_in_mail";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_enable_in_mail");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_1");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_enable_in_grid";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_enable_in_grid");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_1");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_enable_in_menu";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_enable_in_menu");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_0");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_enable_in_document";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_enable_in_document");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_1");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_enable_tip";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_enable_tip");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_1");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_writable";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_writable");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_1");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_hide";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_hide");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                        , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no_default_0");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_preload_by_domclass";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_preload_by_domclass");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_fixed_pre_content";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_fixed_pre_content");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_fixed_post_content";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_fixed_post_content");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_preload_by_db";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_preload_by_db");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");


$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_domclass";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_domclass");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

$oField = ffField::factory($cm->oPage);
$oField->id = "field_default_custom";
$oField->label = ffTemplate::_get_word_by_code("form_fields_show_custom");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                        array(new ffData("show_no"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no")))
                    );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
$oField->default_value = new ffData("show_no");
$oRecord->addContent($oField, "DefaultField");

/**
* Ecommerce
*/
if(AREA_SHOW_ECOMMERCE) {
    $oRecord->addTab("Ecommerce");
    $oRecord->setTabTitle("Ecommerce", ffTemplate::_get_word_by_code("form_config_ecommerce"));

    $oRecord->addContent(null, true, "Ecommerce"); 
    $oRecord->groups["Ecommerce"] = array(
                                     "title" => ffTemplate::_get_word_by_code("form_config_ecommerce")
                                     , "cols" => 1
                                     , "tab" => "Ecommerce"
                                  );

    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_ecommerce";
    $oField->label = ffTemplate::_get_word_by_code("form_config_enable_ecommerce");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array(
                                array(new ffData("moregoods"), new ffData(ffTemplate::_get_word_by_code("fields_are_goods")))
                                , array(new ffData("onegood"), new ffData(ffTemplate::_get_word_by_code("fields_in_good")))
    );
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_ecommerce_weight";
    $oField->label = ffTemplate::_get_word_by_code("form_config_enable_ecommerce_weight");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "skip_form_cart";
    $oField->label = ffTemplate::_get_word_by_code("form_config_skip_form_cart");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "skip_shipping_calc";
    $oField->label = ffTemplate::_get_word_by_code("form_config_skip_shipping_calc");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");

  	$oField = ffField::factory($cm->oPage);
    $oField->id = "discount_perc";
    $oField->label = ffTemplate::_get_word_by_code("form_config_discount_perc");
    $oField->base_type = "Number";
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "discount_val";
    $oField->label = ffTemplate::_get_word_by_code("form_config_discount_val");
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_sum_quantity";
    $oField->label = ffTemplate::_get_word_by_code("form_config_enable_sum_quantity");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "reset_cart";
    $oField->label = ffTemplate::_get_word_by_code("form_config_reset_cart");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "restore_default_by_cart";
    $oField->label = ffTemplate::_get_word_by_code("form_config_restore_default_by_cart");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_dynamic_cart";
    $oField->label = ffTemplate::_get_word_by_code("form_config_enable_dynamic_cart");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_dynamic_cart_advanced";
    $oField->label = ffTemplate::_get_word_by_code("form_config_enable_dynamic_cart_advanced");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "field_default_show_price_in_label";
    $oField->label = ffTemplate::_get_word_by_code("form_fields_show_price_in_label");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array(
                            array(new ffData("show_no_default_1"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_yes")))
                            , array(new ffData("show_no_default_0"), new ffData(ffTemplate::_get_word_by_code("form_fields_show_no_default_no")))
                        );
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("form_fields_show_yes");
    $oField->default_value = new ffData("show_no_default_0");
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "fixed_cart_qta";
    $oField->label = ffTemplate::_get_word_by_code("form_config_fixed_cart_qta");
    $oField->base_type = "Number";
    $oField->default_value = new ffData("1", "Number");
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "fixed_cart_price";
    $oField->label = ffTemplate::_get_word_by_code("form_config_fixed_cart_price");
    $oField->base_type = "Number";
    $oField->app_type = "Currency";
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "fixed_cart_vat";
    $oField->label = ffTemplate::_get_word_by_code("form_config_fixed_cart_vat");
    $oField->base_type = "Number";
    $oField->default_value = new ffData($actual_vat, "Number");
    $oRecord->addContent($oField, "Ecommerce");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "hide_vat";
    $oField->label = ffTemplate::_get_word_by_code("form_config_hide_vat");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "fixed_cart_weight";
    $oField->label = ffTemplate::_get_word_by_code("form_config_fixed_cart_weight");
    $oField->base_type = "Number";
    $oRecord->addContent($oField, "Ecommerce");    

    $oField = ffField::factory($cm->oPage);
    $oField->id = "hide_weight";
    $oField->label = ffTemplate::_get_word_by_code("form_config_hide_weight");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField, "Ecommerce");    

	$oField = ffField::factory($cm->oPage);
	$oField->id = "decumulation";
	$oField->label = ffTemplate::_get_word_by_code("ecommerce_decumulation");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
	                            array(new ffData("scorporo"), new ffData(ffTemplate::_get_word_by_code("price_with_vat"))),
	                            array(new ffData("incorporo"), new ffData(ffTemplate::_get_word_by_code("price_without_vat")))
	                       );      
	$oField->required = true;
	$oField->multi_select_one = false;
	$oField->default_value = new ffData("scorporo", "Text");
    $oRecord->addContent($oField, "Ecommerce");    
}

$cm->oPage->addContent($oRecord);

function FormConfigField_on_before_parse_row($component) {
    if(isset($component->grid_fields["aspect"])) 
    {
        
    }
}

function FormConfigField_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
	if(strlen($action))
    {
        switch ($action) {
			case "insert":
            case "update":
				break;
			case "confirmdelete":
				if(isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"] > 0)
				{
					MD_form_delete($_REQUEST["keys"]["ID"]);
				}
			default:
                break;	
		}
	}
}

function MD_form_config_on_do_action($component, $action) {
    
    $theme = $component->parent[0]->theme;
    $form_path = "/form/" . ffCommon_url_rewrite($component->form_fields["name"]->getValue());

    switch ($action) {
        case "insert":
        	if(check_function("fs_operation")) {
        		$res = xcopy(FF_THEME_DIR . "/" . cm_getMainTheme() . "/ff/ffRecord/ffRecord.html"
        					, FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $form_path . "/form.tpl"
        				);
        		$res = xcopy(FF_THEME_DIR . "/" . cm_getMainTheme() . "/ff/ffRecord/ffRecord.html"
        					, FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $form_path . "/report.tpl"
        				);
			}

            break;
        case "update":
        
            break;
        case "confirmdelete":
        	if(check_function("fs_operation"))
            	xpurge_dir(FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH .  "/modules" . $form_path);
            break;
            
        default:            
    }
}

function MD_form_delete($ID_form) 
{
	$db = ffDB_Sql::factory();
	
	$sSQL = "DELETE FROM module_form_fields_selection_value
				WHERE module_form_fields_selection_value.ID_form_fields IN (SELECT module_form_fields.ID
																			FROM module_form_fields
																			WHERE module_form_fields.ID_module = " . $db->toSql($ID_form, "Number") . 
																		")";
	$db->execute($sSQL);
	
	$sSQL = "DELETE FROM module_form_fields
				WHERE module_form_fields.ID_module = " . $db->toSql($ID_form, "Number");
	$db->execute($sSQL);
	
	$sSQL = "DELETE FROM module_form_nodes
				WHERE module_form_nodes.ID_module = " . $db->toSql($ID_form, "Number");
	$db->execute($sSQL);
	
	$sSQL = "DELETE FROM module_form_dep
				WHERE module_form_dep.ID_module = " . $db->toSql($ID_form, "Number");
	$db->execute($sSQL);

	$sSQL = "DELETE FROM module_form_pricelist_detail
				WHERE module_form_pricelist_detail.ID_form_pricelist IN (SELECT module_form_pricelist.ID
																			FROM module_form_pricelist
																			WHERE module_form_pricelist.ID_module = " . $db->toSql($ID_form, "Number") . 
																		")";
	$db->execute($sSQL);

	$sSQL = "DELETE FROM module_form_pricelist
				WHERE module_form_pricelist.ID_module = " . $db->toSql($ID_form, "Number");
	$db->execute($sSQL);
}