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
 

$db->query("SELECT module_search.*
                        FROM 
                            module_search
                        WHERE 
                            module_search.name = " . $db->toSql($MD_chk["params"][0]));
if($db->nextRecord()) {
	$framework_css = cm_getFrameworkCss();	

    $ID_search = $db->getField("ID")->getValue();
    $search_name = $db->getField("name")->getValue();
    $require_note = $db->getField("require_note")->getValue();
    $tpl_search_path = $db->getField("tpl_search_path")->getValue();
    $show_title = $db->getField("show_title")->getValue();
    $area = $db->getField("area")->getValue();
    $contest = $db->getField("contest")->getValue();
}

//$tpl_data["custom"] = "adv-search.html";
$tpl_data["custom"] = $layout["smart_url"] . ".html";
$tpl_data["base"] = null;

$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
if($tpl_data["result"]["path"] && $tpl_data[$tpl_data["result"]["type"]]) {
    $oRecord->template_dir = $tpl_data["result"]["path"];
    $oRecord->template_file = $tpl_data[$tpl_data["result"]["type"]];
    if(check_function("MD_search_on_done_action")) {
    	$oRecord->addEvent("on_tpl_parse", "MD_search_on_tpl_parse");
	}
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = $MD_chk["id"];
$oRecord->class = $MD_chk["id"];
$oRecord->use_own_location = $MD_chk["own_location"];
$oRecord->skip_action = true;

$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("search_start"); 

$sSQL = "SELECT module_search_fields.*
                , extended_type.name AS extended_type
                , extended_type.ff_name AS ff_extended_type
                , check_control.ff_name AS check_control
                , module_search_fields_group.name AS `group_field`
                , module_search_fields_group.default_grid AS group_default_grid
                , module_search_fields_group.grid_md AS group_grid_md
                , module_search_fields_group.grid_sm AS group_grid_sm
                , module_search_fields_group.grid_xs AS group_grid_xs
            FROM module_search_fields
                LEFT JOIN extended_type ON extended_type.ID = module_search_fields.ID_extended_type
                LEFT JOIN check_control ON check_control.ID = module_search_fields.ID_check_control
                LEFT JOIN module_search_fields_group ON module_search_fields_group.ID = module_search_fields.ID_search_fields_group
            WHERE module_search_fields.ID_module = " . $db->toSql($ID_search, "Number") . "
                AND NOT(module_search_fields.hide > 0)
            ORDER BY module_search_fields.`order`, module_search_fields.name";
$db->query($sSQL);
if($db->nextRecord()) 
{
    do { 
        $field_name = $db->getField("name")->getValue();

        $arrField[$field_name]["ID"]                                = $db->getField("ID")->getValue();
        $arrField[$field_name]["name"]                              = $db->getField("name")->getValue();
        $arrField[$field_name]["group"]["field"]                    = ($db->getField("group_field")->getValue() 
                                                                        ? ffCommon_url_rewrite($db->getField("group_field")->getValue()) 
                                                                        : null);
        $arrField[$field_name]["group"]["class"]["default"]         = ffCommon_url_rewrite($db->getField("group_field")->getValue());
        $arrField[$field_name]["class"]["default"]                  = "search_" . ffCommon_url_rewrite($arrField[$field_name]["name"]);
        $arrField[$field_name]["unic_value"]                        = $db->getField("unic_value", "Number", true);
        $arrField[$field_name]["writable"]                          = $db->getField("writable", "Number", true);
        $arrField[$field_name]["ID_fields"]                      	= $db->getField("ID_fields", "Number", true);
        $arrField[$field_name]["data_source"]                       = $db->getField("data_source", "Text", true);
        $arrField[$field_name]["data_limit"]                        = $db->getField("data_limit", "Number", true);
        $arrField[$field_name]["disable_select_one"]                = $db->getField("disable_select_one", "Number", true);
        $arrField[$field_name]["check_control"]                     = $db->getField("check_control")->getValue();
        $arrField[$field_name]["extended_type"]                     = $db->getField("extended_type")->getValue();
        $arrField[$field_name]["ff_extended_type"]                  = $db->getField("ff_extended_type")->getValue();
        $arrField[$field_name]["display_label"]                     = !$db->getField("hide_label", "Number", true);
        $arrField[$field_name]["area"]                              = $area;

        if(strlen($db->getField("custom_placeholder", "Text", true)))
            $arrField[$field_name]["placeholder"] = ffTemplate::_get_word_by_code($db->getField("custom_placeholder", "Text", true));
        else
            $arrField[$field_name]["placeholder"] = ffTemplate::_get_word_by_code($arrField[$field_name]["name"]);

        if(is_array($framework_css))
        {
            if(!array_key_exists("grid", $arrField[$field_name]["group"]["class"])) {
                $arrField[$field_name]["group"]["class"]["grid"] = cm_getClassByFrameworkCss(array(
                        (int) $db->getField("group_grid_xs", "Number", true)
                        ,(int) $db->getField("group_grid_sm", "Number", true)
                        ,(int) $db->getField("group_grid_md", "Number", true)
                        ,(int) $db->getField("group_default_grid", "Number", true)
                ), "col");
            }

            $arrField[$field_name]["framework_css"]["component"] = array(
                $db->getField("default_grid", "Number", true)
                , $db->getField("grid_md", "Number", true)
                , $db->getField("grid_sm", "Number", true)
                , $db->getField("grid_xs", "Number", true)
            );

            if($arrField[$field_name]["display_label"]) {
                $arrField[$field_name]["framework_css"]["label"] = array(
                    $db->getField("label_default_grid", "Number", true)
                    , $db->getField("label_grid_md", "Number", true)
                    , $db->getField("label_grid_sm", "Number", true)
                    , $db->getField("label_grid_xs", "Number", true)
                );
            }
        }
    } while($db->nextRecord());
}

if(is_array($arrField) && count($arrField)) 
{
    foreach($arrField AS $field_key => $field_value) 
    {
        if (strlen($field_value["group"]["field"]) && !isset($oRecord->groups[$field_value["group"]["field"]])) 
        { 
            $oRecord->addContent(null, true, $field_value["group"]["field"]); 
            if($use_tab) {
                $oRecord->addTab($field_value["group"]["field"]);
                $oRecord->setTabTitle($field_value["group"]["field"], ffTemplate::_get_word_by_code("search_" . $field_value["group"]["field"]));
            } else {
                $gridGroup[$field_value["group"]["field"]] = $db->toSql($field_value["group"]["field"], "Text");
            }
            $oRecord->groups[$field_value["group"]["field"]] = array(
                "title" => ffTemplate::_get_word_by_code("search_" . $field_value["group"]["field"])
                , "cols" => 1
                , "class" => implode(" ", array_filter($field_value["group"]["class"]))
                , "tab" => ($use_tab ? $field_value["group"]["field"] : null)
            );
        }

        if(is_array($field_value["class"]) && count($field_value["class"]))
            $field_class = implode(" ", $field_value["class"]);

        switch ($field_value["name"]) 
        {
            default:
                $field_id = $field_value["ID"];

                $obj_page_field = ffField::factory($cm->oPage);
                $obj_page_field->store_in_db = false;
                
                $obj_page_field->container_class = $field_class;
                $obj_page_field->user_vars["name"] = $field_value["name"];
                $obj_page_field->data_type = ""; 

                if(check_function("get_field_by_extension"))
                    $js .= get_field_by_extension($obj_page_field, $field_value, "search");
                $obj_page_field->id = $field_value["name"];
                $obj_page_field->autocomplete_readonly = false;

               // if(isset($_GET[ffCommon_url_rewrite($field_value["name"])]) && strlen($_GET[ffCommon_url_rewrite($field_value["name"])])) {
                //    $obj_page_field->default_value = new ffData($_GET[ffCommon_url_rewrite($field_value["name"])], $field_value["ff_extended_type"]);
               // }
                $obj_page_field->properties["onkeydown"] = "return ff.submitProcessKey(event, '" . $MD_chk["id"] . "_ActionButtonInsert')";
                
                $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                break;
        }
        
        if(strlen($js)) {
	        $js = '
	            jQuery(function() {
	                    ' . $js . '
	            });';

	        $cm->oPage->tplAddJs("ff.cms.search.init", array(
            	"embed" => $js
	        ));
	    } else {
	    	$cm->oPage->tplAddJs("ff.cms.search");
	    }

        $cm->oPage->addContent($oRecord);  
    }
}
 
if(check_function("get_layout_settings")) {
	$arrLayout = get_layout_by_block($area, "/" . $contest);

	$unic_id = $arrLayout["prefix"] . $arrLayout["ID"] . "T";

	$oRecord->buttons_options["insert"]["action_type"] = "gotourl";
	//$oRecord->buttons_options["insert"]["url"] = "javascript:ff.cms.search.block(this, '/" . $area . "/" . $contest . "','" . $unic_id . "');";
	$oRecord->buttons_options["insert"]["url"] = "javascript:ff.cms.search.block(this, '/" . $contest . "','" . $unic_id . "');";
	$oRecord->properties["data-advsrc-target"] = $unic_id;
}

function MD_search_on_tpl_parse($component, $tpl) {
	$tpl->set_var("insert:onclick", $component->buttons_options["insert"]["url"]);
	$tpl->set_var("properties", $component->getProperties());
}