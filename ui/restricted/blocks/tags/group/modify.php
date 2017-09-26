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

$db = ffDB_Sql::factory();

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setstatus"]) && $_REQUEST["keys"]["ID"] > 0) {
    $sSQL = "UPDATE search_tags_group
            SET search_tags_group.status = " . $db->toSql($_REQUEST["setstatus"], "Number") . "
            WHERE search_tags_group.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
    
    if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("TagsGroupModify")), true));
    } else {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("TagsGroupModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    }    
}

$type = $_REQUEST["extype"];
$user_path = basename($cm->real_path_info);
if(!isset($_REQUEST["keys"]["ID"])) {
	$sSQL = "SELECT search_tags_group.*
			FROM search_tags_group
			WHERE search_tags_group.smart_url = " . $db->toSql($user_path);
	$db->query($sSQL);
	if($db->nextRecord()) {
		$_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);
	}
}

/*
$sSQL = "SELECT cm_layout.* 
			FROM cm_layout 
			WHERE cm_layout.path = " . $db->toSql("/");
$db->query($sSQL);
if ($db->nextRecord()) {
    $framework_css = cm_getFrameworkCss($db->getField("framework_css", "Text", true));
    $framework_css_name = $framework_css["name"];
}*/

$framework_css = $cm->oPage->framework_css;
$template_framework = $framework_css["name"];

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "TagsGroupModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "search_tags_group";

$sSQL = "SELECT ID, name
            FROM vgallery
            WHERE 1";
$db->query($sSQL);
if($db->nextRecord())
{
    do {
        $arrVgallery[] = array(new ffData("vgallery"), new ffData("/" . $db->getField("name", "Text", true)),new ffData(ffTemplate::_get_word_by_code($db->getField("name", "Text", true))));
    } while ($db->nextRecord());
}

$sSQL = "SELECT ID, name
            FROM anagraph_categories
            WHERE 1";
$db->query($sSQL);
if($db->nextRecord())
{
    do {
        $arrVgallery[] = array(new ffData("anagraph"), new ffData("/" . $db->getField("name", "Text", true)),new ffData(ffTemplate::_get_word_by_code($db->getField("name", "Text", true))));
    } while ($db->nextRecord());
}

$arrayChild = array(
    array(new ffData("anagraph"), new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph")))
);


$ff_modules = glob(FF_DISK_PATH . "/modules/*");
foreach ($ff_modules as $value) {
    $arrModules[] = array(new ffData("modules"), new ffData($value),new ffData(basename($value)));
}


$result = array_merge($arrayChild, $arrVgallery, $arrModules);

//print_r($result);
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

 
if(!$type || $type == "general") {
	$oRecord->addContent(null, true, "General");
	$oRecord->groups["General"] = array(
		"title" => ffTemplate::_get_word_by_code("tag_group_title")
	);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("tag_group_name");
    $oField->setWidthComponent(6);
	$oRecord->addContent($oField, "General");
    
	$oField = ffField::factory($cm->oPage);
	$oField->id = "smart_url";
	$oField->label = ffTemplate::_get_word_by_code("tag_group_smart_url");
	$oField->widget = "slug";
	$oField->slug_title_field = "name";
    $oField->setWidthComponent(6);
	$oRecord->addContent($oField, "General");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "menu_tag";
    $oField->label = ffTemplate::_get_word_by_code("tag_group_show_in_menu_tag");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number");
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->default_value = new ffData("1", "Number");
    $oRecord->addContent($oField, "General");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "menu_search";
    $oField->label = ffTemplate::_get_word_by_code("tag_group_show_in_menu_search");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->checked_value = new ffData("1", "Number");
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->default_value = new ffData("1", "Number");
    $oRecord->addContent($oField, "General");

	if (check_function("set_fields_grid_system")) {
		set_fields_grid_system($oRecord, array(
			"group" => "General"
			, "fluid" => array(
				"name" => "fluid"
				, "label" => ffTemplate::_get_word_by_code("tag_group_fluid")
				, "prefix" => "grid"
				, "one_field" => true
				, "hide" => false
				, "full_row" => true
				, "default_value" => new ffData("1", "Number")
			)
			, "class" => array(
				"name" => "class"
			)
			, "wrap" => array(
				"name" => "wrap"
				, "one_field" => true
			)
		), $framework_css);
	}
}

if(!$type || $type == "overview") {
	$oRecord->addContent(null, true, "Overview");
	$oRecord->groups["Overview"] = array(
		"title" => ffTemplate::_get_word_by_code("tag_group_overview_title")
	);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "overview_limit";
	$oField->label = ffTemplate::_get_word_by_code("tag_group_overview_limit");
	$oRecord->addContent($oField, "Overview");


	if (check_function("set_fields_grid_system")) {
		set_fields_grid_system($oRecord, array(
			"group" => "Overview"
			, "fluid" => array(
				"name" => "overview_container_fluid"
				, "label" => ffTemplate::_get_word_by_code("tag_group_overview_container_fluid")
				, "prefix" => "overview_container_grid"
				, "one_field" => true
				, "hide" => false
				, "full_row" => true
				, "default_value" => new ffData("1", "Number")
			)
			, "class" => array(
				"name" => "overview_container_class"
			)
			, "wrap" => false
		), $framework_css);

		$oRecord->addContent(null, true, "OverviewItem");
		$oRecord->groups["OverviewItem"] = array(
			"title" => ffTemplate::_get_word_by_code("tag_group_overview_item_title")
		);
		
		set_fields_grid_system($oRecord, array(
			"group" => "OverviewItem"
			, "fluid" => array(
				"name" => "overview_item_fluid"
				, "label" => ffTemplate::_get_word_by_code("tag_group_overview_item_fluid")
				, "prefix" => "overview_item_grid"
				, "one_field" => true
				, "hide" => false
				, "full_row" => true
				, "default_value" => new ffData("1", "Number")
			)
			, "class" => array(
				"name" => "overview_item_class"
			)
			, "wrap" => array(
				"name" => "overview_wrap"
				, "one_field" => true
				, "multi" => array(
					"container" => array(
						"multi_pairs" => array(
							array(new ffData("-1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": " . ffTemplate::_get_word_by_code("grid_skip_all"))),
							array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) ? "." . cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) : "") . "")),
							array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) ? "." . cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) : "") . ""))
						)
					)
					, "row" => array(
						"multi_pairs" => array(
							array(new ffData("-1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": " . ffTemplate::_get_word_by_code("grid_skip_all"))),
							array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) ? "." . cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "-fluid" : "")) : "") . "")),
							array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) ? "." . cm_getClassByFrameworkCss("", "wrap" . ($framework_css["is_fluid"] ? "" : "-fluid")) : "") . ""))
						)
					)
				)
			)		
		), $framework_css);	
	}
}

$cm->oPage->addContent($oRecord); 

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "searchTagGroupRel";
$oDetail->title = ffTemplate::_get_word_by_code("search_tag_group_relation");
$oDetail->src_table = "search_tags_group_rel";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_group" => "ID"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dataSource";
$oField->data_source = "data_source";
$oField->label = ffTemplate::_get_word_by_code("anagraph_selection_source");
$oField->widget = "actex";
$oField->base_type = "Text";
$oField->multi_pairs =  array (
                            array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph"))),
                            array(new ffData("vgallery"), new ffData(ffTemplate::_get_word_by_code("vgallery"))),
                            array(new ffData("modules"), new ffData(ffTemplate::_get_word_by_code("modules")))
                       );
$oField->actex_child = array("dataLimit");
$oField->actex_update_from_db = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dataLimit";
$oField->data_source = "data_limit";
$oField->label = ffTemplate::_get_word_by_code("anagraph_selection_field"); 
$oField->widget = "actex";
//$oField->multi_pairs =  $result;
$oField->multi_pairs = array (
                            array(new ffData("vgallery"), new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("ASC"))),
                            array(new ffData("vgallery"), new ffData("DESC"), new ffData(ffTemplate::_get_word_by_code("DESC"))),
                            array(new ffData("anagraph"), new ffData("ASC"), new ffData(ffTemplate::_get_word_by_code("ASC"))),
                            array(new ffData("anagraph"), new ffData("DESC"), new ffData(ffTemplate::_get_word_by_code("DESC")))
                       );
$oField->actex_father = "dataSource";
//$oField->actex_related_field = "type";
//$oField->actex_group = "group_name";
$oField->actex_hide_empty = "all";
$oField->actex_update_from_db = true;
$oDetail->addContent($oField); 



$oRecord->addContent($oDetail, "relation");
$cm->oPage->addContent($oDetail); 





