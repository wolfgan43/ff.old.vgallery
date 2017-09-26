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

check_function("system_ffcomponent_set_title");
$record = system_ffComponent_resolve_record(array(
	"table" => "module_form"
	, "key" => "formcnf-ID"
));

$src_is_empty_selection = null;
$sSQL = "SELECT module_form_dep.* 
		FROM module_form_dep 
		WHERE module_form_dep.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$db->query($sSQL);
if($db->nextRecord()) {
	$dep_fields = $db->getField("dep_fields", "Number", true);
	$ID_field = $db->getField("ID_form_fields", "Number", true);
	$src_is_empty_selection = false;
	$module_form_title = ffTemplate::_get_word_by_code("modify_module_form_dep_record");
} else {
	$module_form_title = ffTemplate::_get_word_by_code("addnew_module_form_dep_record");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "CriteriaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "module_form_dep";
$oRecord->insert_additional_fields["ID_module"] = $_REQUEST["keys"]["formcnf-ID"];
$oRecord->addEvent("on_do_action", "FormExtraDep_on_do_action");

/* Title Block */
system_ffcomponent_set_title(
    $module_form_title
    , true
    , false
    , false
    , $oRecord
);  	

if(isset($_REQUEST["keys"]["formcnfield-ID"]) && $_REQUEST["keys"]["formcnfield-ID"] > 0)
{
    $ID_field = $_REQUEST["keys"]["formcnfield-ID"];
    $oRecord->insert_additional_fields["ID_form_fields"] = $ID_field;
}
    
if(isset($_REQUEST["keys"]["ID-subval"]))
{
    $oRecord->insert_additional_fields["ID_selection_value"] = $_REQUEST["keys"]["ID-subval"];
}
if(isset($_REQUEST[$oRecord->id . "_ID_form_fields"])) {
    $ID_field = $_REQUEST[$oRecord->id . "_ID_form_fields"];
} 

$field_is_boolean = false;

if($ID_field)
{
    $sSQL = "SELECT extended_type.name,
                module_form_fields_selection_value.ID AS ID_subvalue
                FROM module_form_fields
                    INNER JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                    LEFT JOIN module_form_fields_selection_value ON module_form_fields_selection_value.ID_form_fields = module_form_fields.ID
                WHERE module_form_fields.ID = " . $db->toSql($ID_field, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $type_element = $db->getField("name", "Text", true);
        if($type_element == "Boolean")
        {
            $field_is_boolean = true;
        }
    }
    
}

if(!$field_is_boolean && $src_is_empty_selection === null && !array_key_exists("ID_form_fields", $oRecord->insert_additional_fields))
{
    if($ID_field > 0) {
            $sSQL = "SELECT module_form_fields_selection_value.ID 
                    , module_form_fields_selection_value.name
                    , module_form_fields_selection_value.ID_form_fields
                FROM module_form_fields_selection_value
                WHERE module_form_fields_selection_value.ID_form_fields = " . $db->toSql($ID_field, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $src_is_empty_selection = true;
        }
    }
}

if(isset($_REQUEST[$oRecord->id . "_ID_selection_value"])) {
	$src_is_empty_selection = false;
} 

 if(isset($_REQUEST[$oRecord->id . "_dep_fields"])) {
	$dep_fields = $_REQUEST[$oRecord->id . "_dep_fields"];
        
}  

$dep_is_selection = false;
$dep_is_boolean = false;
$dep_is_multipler = false;
if($dep_fields > 0) {
    $sSQL = "SELECT extended_type.name,
                module_form_fields_selection_value.ID AS ID_subvalue
                FROM module_form_fields
                    INNER JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                    LEFT JOIN module_form_fields_selection_value ON module_form_fields_selection_value.ID_form_fields = module_form_fields.ID
                WHERE module_form_fields.ID = " . $db->toSql($dep_fields, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $type_element = $db->getField("name", "Text", true);
        if($type_element == "Boolean")
        {
            $dep_is_boolean = true;
            $oRecord->update_additional_fields["operator"] = "==";
            $oRecord->update_additional_fields["dep_selection_value"] = 0;
        } elseif($db->getField("ID_subvalue", "Number", true))
        {
            $dep_is_selection = true;
            //$oRecord->update_additional_fields["operator"] = "";
            $oRecord->update_additional_fields["value"] = "";
        }
    } else {
        $dep_is_multipler = true;
        $oRecord->update_additional_fields["dep_selection_value"] = 0;
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(!array_key_exists("ID_form_fields", $oRecord->insert_additional_fields))
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_form_fields";
    $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_ID_fields");
    $oField->base_type = "Number";
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->required = true;
    $oField->source_SQL = "SELECT module_form_fields.ID 
                                    , module_form_fields.name AS name
                                    , module_form_fields_group.name AS group_name
                                FROM module_form_fields
                                    LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                                WHERE module_form_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "
                                ORDER BY module_form_fields.`order`, module_form_fields_group.name, module_form_fields.name";
    $oField->actex_update_from_db = true;
    $oField->actex_group = "group_name";
    if(!$field_is_boolean)
        $oField->actex_child = "ID_selection_value";
    if($_REQUEST["XHR_CTX_ID"])
		$oField->actex_on_change = "function(obj, old_value, action) { 
			if(action == 'change') {
				jQuery('#" . $oRecord->id . "_ID_selection_value').val('');
				jQuery('#" . $oRecord->id . "_dep_fields').val('');
				jQuery('#" . $oRecord->id . "_dep_selection_value').val('');
				jQuery('#" . $oRecord->id . "_value').val('');
				
				ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'}); return true; 
			}
		}";
    else
		$oField->actex_on_change = "function(obj, old_value, action) { 
			if(action == 'change') {
				jQuery('#" . $oRecord->id . "_ID_selection_value').val('');
				jQuery('#" . $oRecord->id . "_dep_fields').val('');
				jQuery('#" . $oRecord->id . "_dep_selection_value').val('');
				jQuery('#" . $oRecord->id . "_value').val('');

				ff.ajax.doRequest({'action' : 'refresh'}); return true; 
			}
		}";
    $oRecord->addContent($oField);
    
    if($field_is_boolean)
    {
        $oRecord->insert_additional_fields["ID_selection_value"] = 0;
        $oRecord->update_additional_fields["ID_selection_value"] = 0;
    } else
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_selection_value";
        $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_selection_value");
        $oField->base_type = "Number";
        $oField->widget = "actex";
		//$oField->widget = "activecomboex";
        $oField->source_SQL = "SELECT module_form_fields_selection_value.ID 
                                        , IF(length(module_form_fields_selection_value.name) > 0, module_form_fields_selection_value.name, module_form_fields_selection_value.qta)
                                        , module_form_fields_selection_value.ID_form_fields
                                    FROM module_form_fields_selection_value
                                    [WHERE] 
                                    ORDER BY name, qta";
        $oField->actex_father = "ID_form_fields";
        $oField->actex_hide_empty = true;
        $oField->display_label = false;
        $oField->actex_update_from_db = true;
        $oField->multi_select_noone = true;
        $oField->multi_select_noone_label = ffTemplate::_get_word_by_code("all");
        $oField->multi_select_noone_val = new ffData("0", "Number");
        $oField->actex_related_field = "ID_form_fields";
        $oRecord->addContent($oField);
    }
} 

if($ID_field > 0 && (!$src_is_empty_selection))
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "dep_fields";
    $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_fields");
    $oField->base_type = "Number";
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT module_form_fields.ID 
                                    , module_form_fields.name AS name
                                    , module_form_fields_group.name AS group_name
                                FROM module_form_fields
                                    LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
                                WHERE module_form_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["formcnf-ID"], "Number") . "
                                   AND module_form_fields.ID <> " . $db->toSql($ID_field, "Number") . "
                                ORDER BY module_form_fields.`order`, module_form_fields_group.name, module_form_fields.name";
    if($dep_is_multipler)
    {
        $oField->actex_child = "value";
    } elseif($dep_is_selection)
    {
        $oField->actex_child = "dep_selection_value";
    }
    $oField->actex_update_from_db = true;
    $oField->actex_group = "group_name";
    
    if($_REQUEST["XHR_CTX_ID"])
        $oField->actex_on_change = "function(obj, old_value, action) {
            if(action == 'change') {
				jQuery('#" . $oRecord->id . "_dep_selection_value').val('');
				jQuery('#" . $oRecord->id . "_value').val('');

		        ff.ajax.ctxDoRequest('" . $_REQUEST["XHR_CTX_ID"] . "', {'action' : 'refresh'}); return true; 
		    }
		}";
    else
	    $oField->actex_on_change = "function(obj, old_value, action) { 
	        if(action == 'change') {
				jQuery('#" . $oRecord->id . "_dep_selection_value').val('');
				jQuery('#" . $oRecord->id . "_value').val('');

				ff.ajax.doRequest({'action' : 'refresh'}); return true; 
			}
		}";
    $oRecord->addContent($oField);
    
	if(!$dep_is_boolean)
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "operator";
		$oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_operator");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array (
									array(new ffData("=="), new ffData(ffTemplate::_get_word_by_code("="))),
									array(new ffData("<>"), new ffData(ffTemplate::_get_word_by_code("<>")))
							   );
		if(!$dep_is_selection)
		{
			$oField->multi_pairs[] = array(new ffData("<"), new ffData(ffTemplate::_get_word_by_code("<")));
			$oField->multi_pairs[] = array(new ffData(">"), new ffData(ffTemplate::_get_word_by_code(">")));
			$oField->multi_pairs[] = array(new ffData("<="), new ffData(ffTemplate::_get_word_by_code("<=")));
			$oField->multi_pairs[] = array(new ffData(">="), new ffData(ffTemplate::_get_word_by_code(">=")));
		}
		$oField->multi_select_one = false;
		$oRecord->addContent($oField);
	}
	
    if($dep_is_selection)
    {
		$oField = ffField::factory($cm->oPage);
        $oField->id = "dep_selection_value";
        $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_selection_value");
        $oField->base_type = "Number";
        $oField->widget = "actex";
		//$oField->widget = "activecomboex";
        $oField->source_SQL = "SELECT module_form_fields_selection_value.ID 
                                        , module_form_fields_selection_value.name
                                        , module_form_fields_selection_value.ID_form_fields
                                    FROM module_form_fields_selection_value
                                    [WHERE] ";
        $oField->actex_father = "dep_fields";
        $oField->actex_hide_empty = true;
        $oField->display_label = false;
        $oField->actex_update_from_db = true;
        $oField->actex_related_field = "ID_form_fields";
        $oRecord->addContent($oField);
    } elseif($dep_is_boolean)
	{
		$oRecord->insert_additional_fields["operator"] = "==";
	}
    
    if($dep_fields > 0 && !$dep_is_selection) {
        
		$oField = ffField::factory($cm->oPage);
        $oField->id = "value";

        if($dep_is_boolean)
        {
            $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_operator");
            $oField->extended_type = "Selection";
            $oField->multi_pairs = array (
                                        array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("not_selected"))),
                                        array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("selected")))
                                   );
        } elseif($dep_is_multipler)
        {
            
            $oField->base_type = "Number";
            $oField->widget = "actex";
			//$oField->widget = "activecomboex";
            $oField->source_SQL = "SELECT module_form_fields_selection_value.qta AS ID
                                            , module_form_fields_selection_value.qta AS name
                                            , module_form_fields_selection_value.ID_form_fields
                                        FROM module_form_fields_selection_value
                                        [WHERE] ";
            $oField->actex_father = "dep_fields";
            $oField->actex_hide_empty = true;
            $oField->actex_update_from_db = true;
            $oField->actex_related_field = "ID_form_fields";
        } else {
            $oField->label = ffTemplate::_get_word_by_code("form_modify_criteria_value");
        }
        
        $oRecord->addContent($oField);
    }
}
$cm->oPage->addContent($oRecord);

function FormExtraDep_on_do_action($component, $action)
{
    if(strlen($action))
    {
        switch ($action) {
            case "insert":
            case "update":

                break;

            default:
                break;
        }
    }
}