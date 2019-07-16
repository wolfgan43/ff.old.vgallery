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

$db = ffDB_Sql::factory();

if (!Auth::env("AREA_MODULES_SHOW_MODIFY")) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$array_rel = array(
    1 => " AND name = 'String' "
    , 2 => " AND name = 'String' "
    , 3 => " AND name IN ( 'Selection', 'Autocomplete' ) "
    , 5 => " AND name = 'String' "
    , 6 => " AND name = 'String' "
    , 7 => " AND name = 'Date' "
    , 8 => " AND name = 'String' "
    , 9 => " AND name = 'String' "
    , 10 => " AND name IN ( 'Selection', 'Autocomplete' ) "
    , 11 => " AND name = 'String' "
    , 13 => " AND name IN ( 'Selection', 'Autocomplete' ) "
    , 14 => " AND name = 'Date' "
    , 15 => " AND name = 'String' "
    , 16 => " AND name = 'String' "
    , 17 => " AND name IN ( 'Selection', 'Autocomplete' ) "
    , 18 => " AND name IN ( 'Selection', 'Autocomplete' ) "
);

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record(array(
	"table" => "module_search_fields"
	, "key" => "ID"
	, "primary" => array(
		"table" => "module_search"
		, "key" => "ID"
		, "fields" => array(
			"ID_module" => "ID"
			, "area" => null
			, "contest" => null
		)
	)
	, "if_request" => array(
		"field" => array(
			"table" => "module_search_fields"
			, "key" => "ID"
			, "fields" => array(
				"copy_field" => "ID"
				, "name" => "IF(display_name = ''
										, REPLACE(name, '-', ' ')
										, display_name
									)"
			)
		)
	)
), array(
	"name" => "IF(module_search_fields.display_name = ''
					, REPLACE(module_search_fields.name, '-', ' ')
					, module_search_fields.display_name
				)"
	, "ID_module" => null
));




switch ($record["area"]) {
    case "anagraph":
        $sSQL_query = "SELECT ID, name, ID_extended_type
                    FROM anagraph_fields
                    WHERE 1
                    ORDER BY name";
        break;
    case "vgallery":
        $sSQL_query = "SELECT vgallery_fields.ID, vgallery_fields.name, ID_extended_type
                        FROM vgallery_fields 
                            INNER JOIN vgallery ON FIND_IN_SET(vgallery_fields.ID_type,vgallery.limit_type) 
                        WHERE vgallery.name = " . $db->toSql($record["contest"], "Text") . "
                        ORDER BY name";
        break;
    default:
        break;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SearchExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_search_fields";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_search_fields.*
									, IF(module_search_fields.display_name = ''
										, REPLACE(module_search_fields.name, '-', ' ')
										, module_search_fields.display_name
									) AS display_name
								FROM module_search_fields 
								WHERE module_search_fields.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$oRecord->insert_additional_fields["ID_module"] = new ffData($record["ID_module"], "Number");
$oRecord->addEvent("on_do_action", "SearchExtraFieldModify_on_do_action");
$oRecord->addEvent("on_done_action", "SearchExtraFieldModify_on_done_action");
 
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
$oField->data_source = "ID";
$oRecord->addKeyField($oField);

if($record["noentry"]) 
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "copy-from";
    $oField->label = ffTemplate::_get_word_by_code("form_fields_copy");
    $oField->source_SQL = "SELECT module_search_fields.ID
                                    , module_search_fields.name
                                    , module_search.name AS grp_name
                                FROM module_search_fields 
                                    INNER JOIN module_search ON module_search.ID = module_search_fields.ID_module
                                ORDER BY module_search_fields.name";
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->actex_group = "grp_name";
    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("search_fields_addnew");
    $oField->store_in_db = false;
    $oRecord->addContent($oField);    
} else 
{
    $sSQL = "SELECT ID, name
                FROM extended_type 
                WHERE 1";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $arrExtType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
            $arrExtTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
        } while($db->nextRecord());
    }

    if($record["copy_field"] > 0 || $_REQUEST["keys"]["ID"] > 0) 
    {
        $selected_field = true;
        if($record["copy_field"])
            $ID_value = $record["copy_field"];
        else
            $ID_value = $_REQUEST["keys"]["ID"];
        
        $sSQL = "SELECT module_search_fields.* 
                    FROM module_search_fields
                    WHERE module_search_fields.ID = " . $db->toSql($ID_value, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {	
            $field_default["name"]                          = $db->getField("name", "Text", true);
            $field_default["ID_extended_type"]              = $db->getField("ID_extended_type", "Number", true);
            $field_default["ID_selection"]                  = $db->getField("ID_selection", "Number", true);
            $field_default["disable_select_one"]            = $db->getField("disable_select_one", "Number", true);
            $field_default["ID_form_fields_group"]          = $db->getField("ID_form_fields_group", "Number", true);
            $field_default["ID_check_control"]              = $db->getField("ID_check_control", "Number", true);
            $field_default["unic_value"]                    = $db->getField("unic_value", "Number", true);
            $field_default["enable_tip"]                    = $db->getField("enable_tip", "Number", true);
            $field_default["writable"]                      = $db->getField("writable", "Number", true);
            $field_default["hide"]                          = $db->getField("hide", "Number", true);
            $field_default["order"]                         = $db->getField("order", "Number", true);
            
            $field_name = $db->getField("name", "Text", true);
            $actual_control_type = $arrControlTypeRev[$field_default["ID_check_control"]];
            $actual_ext_type = $arrExtTypeRev[$field_default["ID_extended_type"]];
            $actual_disable_free_input = $field_default["disable_free_input"];
            $rel_fields_type = $db->getField("ID_fields", "Text", true);
        }
    }
    
    if(isset($_REQUEST[$oRecord->id . "_ID_fields"])) {
        $ID_related_fields = $_REQUEST[$oRecord->id . "_ID_fields"];
    } elseif($rel_fields_type) {
        $ID_related_fields = $rel_fields_type;
    } 
    
    $string_query = "";
    if($ID_related_fields) {
        switch ($record["area"]) {
            case "anagraph":
                $sSQL = "SELECT ID_extended_type, selection_data_source
                            FROM anagraph_fields
                            WHERE ID = " . $db->toSql($ID_related_fields, "Number");
                break;
            case "vgallery":
                $sSQL = "SELECT ID_extended_type, selection_data_source
                    FROM vgallery_fields
                    WHERE ID = " . $db->toSql($ID_related_fields, "Number");
                break;
        }
        if(strlen($sSQL)) {
            $db->query($sSQL);
            if($db->nextRecord()) {
                $string_query = $array_rel[$db->getField("ID_extended_type", "Number", true)];
                $selection_data_source = $db->getField("selection_data_source", "Text", true);
            }
        }
    }

    
    
    
    if(strlen($sSQL_query)) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_fields";
        $oField->label = ffTemplate::_get_word_by_code("search_config_fields");
        $oField->base_type = "Number";
        $oField->source_SQL = $sSQL_query;
        $oField->widget = "actex";
		//$oField->widget = "activecomboex";
        $oField->required = true;
        $oField->actex_update_from_db = true;
        if($_REQUEST["XHR_CTX_ID"])
            $oField->actex_on_change = "function(obj, old_value, action) { 
	            if(action == 'change') {
            		ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'}); 
	            }
            }";
        else
            $oField->actex_on_change = "function(obj, old_value, action) { 
	            if(action == 'change') {
            		ff.ajax.doRequest({'action' : 'refresh'}); 
            	}
            }";
        $oRecord->addContent($oField);
        
        $oRecord->user_vars["area"] = $record["area"];
        $oRecord->user_vars["contest"] = $record["contest"];
    }

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_extended_type";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_extended_type");
    $oField->extended_type = "Selection";
    if(check_function("set_field_extended_type"))
            $oField = set_field_extended_type($oField, $string_query);
    $oField->required = true;
    $oField->default_value = new ffData($field_default["ID_extended_type"], "Number");
    if($_REQUEST["XHR_CTX_ID"])
            $oField->actex_on_change = "function(obj, old_value, action) { 
	            if(action == 'change') {
            		ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'}); 
            	}
            }";
    else
            $oField->actex_on_change = "function(obj, old_value, action) { 
            	if(action == 'change') {
            		ff.ajax.doRequest({'action' : 'refresh'}); 
            		}
            	}";
    $oRecord->addContent($oField);
    
        
    if(check_function("get_schema_def") && strlen($selection_data_source) && !is_int($selection_data_source)) {
        $service_schema = get_schema_def();
        
        if(is_array($service_schema["schema"][$selection_data_source]["relationship"]) && count($service_schema["schema"][$selection_data_source]["relationship"])) {
            foreach($service_schema["schema"][$selection_data_source]["relationship"] AS $key => $value) {
                $multi_pairs_data_source[$key] = array(new ffData($key, "Text"), new ffData($key, "Text"));
                if(strlen($sSQL_data_limit))
                    $sSQL_data_limit .= " UNION ";
                $sSQL_data_limit .= "(SELECT ID AS ID, name, '" . $key . "' AS table_name FROM " . $db->toSql($key, "Text", false) . ")";
            }
            
            if(strlen($sSQL_data_limit)) {
                $db->query($sSQL_data_limit);
                if($db->nextRecord()) {
                    do {
                        $multi_pairs_data_limit[$db->getField("name", "Text", true) . "-" . $db->getField("ID", "Number", true)] = array(new ffData($db->getField("table_name", "Text", true), "Text"), new ffData($db->getField("ID", "Number", true), "Number"), new ffData($db->getField("name", "Text", true), "Text"));
                    } while ($db->nextRecord());
                }
                if(is_array($multi_pairs_data_source) && count($multi_pairs_data_source))
                    ksort($multi_pairs_data_source);
                if(is_array($multi_pairs_data_limit) && count($multi_pairs_data_limit))
                    ksort($multi_pairs_data_limit);
            }
            if(is_array($multi_pairs_data_limit) && count($multi_pairs_data_limit)) {
                $oField = ffField::factory($cm->oPage);
                $oField->id = "data_source";
                $oField->label = ffTemplate::_get_word_by_code("search_config_data_source");
                $oField->base_type = "Text";
                $oField->multi_pairs = $multi_pairs_data_source;
                $oField->widget = "actex";
				//$oField->widget = "activecomboex";
                $oField->actex_child = "data_limit";
                $oField->actex_update_from_db = true;
                $oRecord->addContent($oField);
                
                $oField = ffField::factory($cm->oPage);
                $oField->id = "data_limit";
                $oField->label = ffTemplate::_get_word_by_code("search_config_data_limit");
                $oField->base_type = "Text";
                $oField->multi_pairs = $multi_pairs_data_limit;
                $oField->widget = "actex";
				//$oField->widget = "activecomboex";
                $oField->actex_father = "data_source";
                $oField->actex_related_field = "ID_type";
                $oField->actex_update_from_db = true;
                $oRecord->addContent($oField);
            }
        }
    }
		
/*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "unic_value";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_unic_value");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_tip";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_enable_tip");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField);
*/
    $oField = ffField::factory($cm->oPage);
    $oField->id = "writable";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_writable");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "hide";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_hide");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_search_fields_group";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_group");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT ID, name 
                            FROM module_search_fields_group 
                            ORDER BY name";
    $oField->widget = "actex";
    $oField->actex_update_from_db = true;
    $oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/modify"; 
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=SearchConfigGroupModify_confirmdelete";
    $oField->resources[] = "SearchConfigGroupModify";
    $oRecord->addContent($oField);
		
    $oField = ffField::factory($cm->oPage);
    $oField->id = "custom_placeholder";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_custom_placeholder");
    $oRecord->addContent($oField);

    
	$framework_css = Cms::getInstance("frameworkcss")->getFramework();
    $framework_css_name = $framework_css["name"];
		
    if(strlen($framework_css_name)) {
        if($framework_css_name == "bootstrap")
            $columns = array(3,3,3,3);	
        elseif($framework_css_name == "foundation")
            $columns = array(4,4,4);
        else
            $columns = null;

        $oField = ffField::factory($cm->oPage);
        $oField->id = "default_grid";
        $oField->label = ffTemplate::_get_word_by_code("field_default_grid");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(12, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "grid_md";
        $oField->label = ffTemplate::_get_word_by_code("field_grid_md");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(12, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "grid_sm";
        $oField->label = ffTemplate::_get_word_by_code("field_grid_sm");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(12, "Number");
        $oRecord->addContent($oField);

        if($framework_css_name == "bootstrap") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "grid_xs";
            $oField->label = ffTemplate::_get_word_by_code("field_grid_xs");
            $oField->base_type = "Number";
            $oField->widget = "slider";
            $oField->min_val = "0";
            $oField->max_val = "12";
            $oField->step = "1";
            $oField->setWidthComponent($columns);
            $oField->default_value = new ffData(12, "Number");
            $oRecord->addContent($oField);
        }
			
        $oField = ffField::factory($cm->oPage);
        $oField->id = "label_default_grid";
        $oField->label = ffTemplate::_get_word_by_code("label_group_default_grid");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(3, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "label_grid_md";
        $oField->label = ffTemplate::_get_word_by_code("label_group_grid_md");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(3, "Number");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "label_grid_sm";
        $oField->label = ffTemplate::_get_word_by_code("label_group_grid_sm");
        $oField->base_type = "Number";
        $oField->widget = "slider";
        $oField->min_val = "0";
        $oField->max_val = "12";
        $oField->step = "1";
        $oField->setWidthComponent($columns);
        $oField->default_value = new ffData(3, "Number");
        $oRecord->addContent($oField);

        if($framework_css_name == "bootstrap") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "label_grid_xs";
            $oField->label = ffTemplate::_get_word_by_code("label_group_grid_xs");
            $oField->base_type = "Number";
            $oField->widget = "slider";
            $oField->min_val = "0";
            $oField->max_val = "12";
            $oField->step = "1";
            $oField->setWidthComponent($columns);
            $oField->default_value = new ffData(3, "Number");
            $oRecord->addContent($oField);
        }
    }
		
    $oField = ffField::factory($cm->oPage);
    $oField->id = "hide_label";
    $oField->label = ffTemplate::_get_word_by_code("search_config_hide_label");
    $oField->base_type = "Number";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
    $oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
    $oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE); 
    $oRecord->addContent($oField);

}
$cm->oPage->addContent($oRecord);
    
function SearchExtraFieldModify_on_do_action($component, $action) { 
	$cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    switch($action) {
        case "insert":
            if(isset($component->form_fields["copy-from"])) {
				ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . $cm->real_path_info . "?field=" . $component->form_fields["copy-from"]->getValue());
            }
            break;	
        default:
            break;
    }
    return false;
}

function SearchExtraFieldModify_on_done_action($component, $action) { 
    $db = ffDB_Sql::factory();

    switch($action) { 
        case "insert":
        case "update":
            if(isset($component->user_vars["area"])) {
                switch ($component->user_vars["area"]) {
                    case "anagraph":
                        $sSQL_query = "SELECT ID, name, ID_extended_type
                                    FROM anagraph_fields
                                    WHERE ID = " . $db->toSql($component->form_fields["ID_fields"]->getValue()) . "
                                    ORDER BY name";
                        break;
                    case "vgallery":
                        $sSQL_query = "SELECT vgallery_fields.ID, vgallery_fields.name, ID_extended_type
                                        FROM vgallery_fields 
                                            INNER JOIN vgallery ON FIND_IN_SET(vgallery_fields.ID_type,vgallery.limit_type) 
                                        WHERE vgallery.name = " . $db->toSql($component->user_vars["area"], "Text") . "
                                            AND vgallery_fields.ID = " . $db->toSql($component->form_fields["ID_fields"]->getValue()) . "
                                        ORDER BY name";
                        break;
                    default:
                        break;
                }
                if(strlen($sSQL_query)) {
                    $db->query($sSQL_query);
                    if($db->nextRecord()) {
                        $field_name = $db->getField("name", "Text", true);
                        $sSQL = "UPDATE module_search_fields
                                    SET name = " . $db->toSql($field_name, "Text") . "
                                    WHERE ID = " . $db->toSql($component->key_fields["ID"]->getValue());
                        $db->execute($sSQL);
                    }
                }
            }
            break;	
        default:
            break;
    }
    return false;
}