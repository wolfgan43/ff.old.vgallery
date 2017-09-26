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

$record = system_ffComponent_resolve_record("module_form");

$dep_subvalue_name = false;
$src_subvalue_name = false;
$sSQL = "SELECT module_form_dep.dep_selection_value
            FROM  module_form_dep
            WHERE  module_form_dep.ID_form_fields = " . $db->toSql($_REQUEST["keys"]["formcnfield-ID"], "Number");
$db->query($sSQL);
if($db->nextRecord())
{
    do
    {
        if($db->getField("dep_selection_value", "Number", true) > 0)
        {
            $dep_subvalue_name = true;
        }
        if($db->getField("ID_selection_value", "Number", true) > 0)
        {
            $src_subvalue_name = true;
        }
        if($dep_subvalue_name && $src_subvalue_name)
            break;
    } while($db->nextRecord());
}


if(isset($_REQUEST["keys"]["ID-subval"]))
{
	$ID_subval = $_REQUEST["keys"]["ID-subval"];
}

if(isset($_REQUEST["keys"]["formcnfield-ID"]))
{
	$ID_form_field = $_REQUEST["keys"]["formcnfield-ID"];
}

//$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "FormModifyCriteria";
$oGrid->source_SQL = "SELECT field_dep.name AS dep_field_name
	                    , field_cond.name AS field_cond_name
	                    , field_dep_selection_value.name AS dep_subvalue_name
	                    , field_src_selection_value.name AS src_subvalue_name
	                    , module_form_dep.*
	                    FROM module_form_dep
	                        LEFT JOIN module_form_fields AS field_dep ON field_dep.ID = module_form_dep.dep_fields
	                        LEFT JOIN module_form_fields AS field_cond ON field_cond.ID = module_form_dep.ID_form_fields
	                        LEFT JOIN module_form_fields_selection_value AS field_dep_selection_value ON field_dep_selection_value.ID_form_fields = field_dep.ID
	                            AND field_dep_selection_value.ID = module_form_dep.dep_selection_value
	                        LEFT JOIN module_form_fields_selection_value AS field_src_selection_value ON field_src_selection_value.ID_form_fields = field_cond.ID
	                            AND field_src_selection_value.ID = module_form_dep.ID_selection_value
	                    WHERE module_form_dep.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                        " . ($ID_form_field
	                                ? "AND module_form_dep.ID_form_fields = " . $db->toSql($ID_form_field, "Number")
	                                : ""
	                        ) . "
	                        " . ($ID_subval
	                                ? "AND module_form_dep.ID_selection_value = " . $db->toSql($ID_subval, "Number")
	                                : ($ID_form_field
	                                        ? "AND module_form_dep.ID_selection_value = 0"
	                                        : ""
	                                )
	                        ) . "
	                        [AND] [WHERE] 
			                    [HAVING]
	                                    [ORDER]";
if($ID_form_field)
{
	$oGrid->order_default = "ID";
	$oGrid->user_vars["src"] = false;
} else
{
	$oGrid->order_default = "field_cond_name";
	$oGrid->user_vars["src"] = true;
}

$oGrid->addEvent("on_before_parse_row", "FormExtraDep_on_before_parse_row");
$oGrid->use_search = false;
$oGrid->full_ajax = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/deps/modify";
$oGrid->record_id = "CriteriaModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->buttons_options["export"]["display"] = false;
$oGrid->use_paging = false;
//	$oGrid->use_order = false; 

/**
* Title
*/
system_ffcomponent_set_title(
    ffTemplate::_get_word_by_code("form_deps_title")
    , true
    , false
    , false
    , $oGrid
);


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if(!$ID_form_field)
{
	if(!$ID_subval)
	{
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "src_subvalue_name";
	    $oField->label = ffTemplate::_get_word_by_code("module_form_cond_field_name_selection_value");
	    $oGrid->addContent($oField);
	}
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "field_cond_name";
	$oField->label = ffTemplate::_get_word_by_code("module_form_cond_field_name");
	$oGrid->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "dep_field_name";
$oField->label = ffTemplate::_get_word_by_code("module_form_dep_field_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "operator";
$oField->label = ffTemplate::_get_word_by_code("module_form_dep_operator");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
	                        array(new ffData("=="), new ffData(ffTemplate::_get_word_by_code("="))),
	                        array(new ffData("<"), new ffData(ffTemplate::_get_word_by_code("<"))),
	                        array(new ffData(">"), new ffData(ffTemplate::_get_word_by_code(">"))),
	                        array(new ffData("<="), new ffData(ffTemplate::_get_word_by_code("<="))),
	                        array(new ffData(">="), new ffData(ffTemplate::_get_word_by_code(">="))),
	                        array(new ffData("<>"), new ffData(ffTemplate::_get_word_by_code("<>"))),
							array(new ffData("null"), new ffData(""))
	                   );
$oField->multi_select_one = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dep_subvalue_name";
$oField->label = ffTemplate::_get_word_by_code("module_form_dep_subvalue_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("module_form_dep_value");
$oGrid->addContent($oField);

if($cm->oPage->isXHR()) {
	if(strlen($_REQUEST["XHR_CTX_ID"])) {
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "close";
	    $oButton->action_type = "submit";
	    $oButton->jsaction = "ff.ffPage.dialog.doAction('" . $_REQUEST["XHR_CTX_ID"] . "', 'close');";
	    $oButton->aspect = "link";
	    $oButton->label = ffTemplate::_get_word_by_code("bt_close");
	    $oGrid->addActionButton($oButton);
	}
	
}

$cm->oPage->addContent($oGrid);


function FormExtraDep_on_before_parse_row($component) {
    //ffErrorHandler::raise("as", E_USER_ERROR, null,get_defined_vars());
    $dep_field = $component->db[0]->getField("dep_fields", "Number", true);
    $db = ffDB_Sql::factory();
    $dep_is_boolean = false;
    //echo $dep_field . ",";
    $sSQL = "SELECT module_form_fields.ID,
                    extended_type.name
                FROM module_form_fields
                    INNER JOIN extended_type ON extended_type.ID = module_form_fields.ID_extended_type
                WHERE module_form_fields.ID = " . $db->toSql($dep_field, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $type_element = $db->getField("name", "Text", true);
        if($type_element == "Boolean")
        {
            $dep_is_boolean = true;
        }
    }
    
    if($component->user_vars["src"])
    {
        if(isset($component->grid_fields["src_subvalue_name"]) && strlen($component->grid_fields["src_subvalue_name"]->getValue()))
        {
            $component->grid_fields["src_subvalue_name"]->fixed_pre_content = ffTemplate::_get_word_by_code("module_form_src_subvalue_name_content") . "<span class='form-src-subvalue'>";
            $component->grid_fields["src_subvalue_name"]->fixed_post_content = "</span>";
            $component->grid_fields["field_cond_name"]->fixed_pre_content = ffTemplate::_get_word_by_code("module_form_cond_field_name_plus_subvalue_content") . "<span class='form-src'>";
            $component->grid_fields["field_cond_name"]->fixed_post_content = "</span>";
        } else
        {
            $component->grid_fields["src_subvalue_name"]->fixed_pre_content = "";
            $component->grid_fields["src_subvalue_name"]->fixed_post_content = "";
            $component->grid_fields["field_cond_name"]->fixed_pre_content = ffTemplate::_get_word_by_code("module_form_cond_field_name_content") . "<span class='form-src'>";
            $component->grid_fields["field_cond_name"]->fixed_post_content = "</span>";
        }
    }
    
    if(isset($component->grid_fields["dep_subvalue_name"]) && strlen($component->grid_fields["dep_subvalue_name"]->getValue()))
    {
        $component->grid_fields["value"]->fixed_pre_content = "";
        $component->grid_fields["value"]->fixed_post_content = "";
        $component->grid_fields["dep_subvalue_name"]->fixed_pre_content = /*ffTemplate::_get_word_by_code("module_form_dep_subvalue_name_content") . */"<span class='form-dep-subvalue'>";
        $component->grid_fields["dep_subvalue_name"]->fixed_post_content = "</span>";
        $component->grid_fields["dep_field_name"]->fixed_pre_content = ffTemplate::_get_word_by_code("module_form_dep_field_name_plus_subvalue_content") . "<span class='form-dep'>";
        $component->grid_fields["dep_field_name"]->fixed_post_content = "</span>";
    } else
    { 
        $component->grid_fields["dep_subvalue_name"]->fixed_pre_content = "";
        $component->grid_fields["dep_subvalue_name"]->fixed_post_content = "";
        $component->grid_fields["dep_field_name"]->fixed_pre_content = ffTemplate::_get_word_by_code("module_form_dep_field_name_content") . "<span class='form-dep'>";
        $component->grid_fields["dep_field_name"]->fixed_post_content = "</span>";
        if($dep_is_boolean)
        {
			
            $component->grid_fields["operator"]->setValue("null");
            if($component->grid_fields["value"]->getValue())
                $component->grid_fields["value"]->setValue(ffTemplate::_get_word_by_code("selected"));
            else
                $component->grid_fields["value"]->setValue(ffTemplate::_get_word_by_code("not_selected"));
            $component->grid_fields["value"]->fixed_pre_content = ffTemplate::_get_word_by_code("module_form_dep_field_boolean_name_content") . "<span class='form-dep'>";
            $component->grid_fields["value"]->fixed_post_content = "</span>";
        } else
        {
            $component->grid_fields["value"]->fixed_pre_content = "";
            $component->grid_fields["value"]->fixed_post_content = "";
        }
    }
    
}