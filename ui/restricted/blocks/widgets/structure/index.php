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

if (!Auth::env("AREA_PUBLISHING_SHOW_DETAIL")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");
if(system_ffcomponent_switch_by_path(__DIR__, false)) {
	$db = ffDB_Sql::factory();
	//$cm->real_path_info = ffCommon_dirname($cm->real_path_info);
/*	$field_name = basename($cm->real_path_info);
	$cm->real_path_info = ffCommon_dirname($cm->real_path_info);
	if(check_function("system_ffcomponent_set_title")) {

	    //system_ffcomponent_resolve_by_path("src");
	}*/
	$src_type = ($_REQUEST["src"]
	    ? $_REQUEST["src"]
	    : "vgallery"
	); 

	if(check_function("get_schema_fields_by_type")) {
	    $src = get_schema_fields_by_type($src_type, "vgallery");

		//Override Pathinfo
		if($src_type != $src["type"]) {
			$_REQUEST["keys"]["permalink"] .= "-" . $src_type;
		}

		//$_REQUEST["keys"]["permalink"] .= "/" . $field_name;
		//$cm->real_path_info = $_REQUEST["keys"]["permalink"];	
	}

	$record = system_ffComponent_resolve_record("publishing");

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->dialog_action_button = true;
	//$oGrid->title = ffTemplate::_get_word_by_code("form_config_fields");
	$oGrid->id = "PublishingModifyFields";
	$oGrid->source_SQL = "SELECT publishing_fields.* 
								, " . $src["type"] . "_fields.name AS name
		                    FROM publishing_fields
	                            INNER JOIN " . $src["type"] . "_fields ON " . $src["type"] . "_fields.ID = publishing_fields.ID_fields
	                            INNER JOIN " . $src["type"] . "_type ON " . $src["type"] . "_type.ID = " . $src["type"] . "_fields.ID_type
		                    WHERE publishing_fields.ID_publishing = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
		                        [AND] [WHERE] 
		                    [HAVING] 
		                    [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->use_search = false;
	$oGrid->use_order = false;
	$oGrid->use_paging = false;

    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/structure/[name_VALUE]";
    $oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/structure/[name_VALUE]";
    $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/structure/add";
	$oGrid->record_id = "PublishingExtraFieldModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->buttons_options["export"]["display"] = false;
	$oGrid->widget_deps[] = array(
		"name" => "dragsort"
		, "options" => array(
		      &$oGrid
		    , array(
		        "resource_id" => "publishing_fields"
		        , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
		    )
		    , "ID"
		)
	);
	//$oGrid->addEvent("on_before_parse_row", "PublishingModifyFields_on_before_parse_row");
	
	/**
	* Title
	*/
	system_ffcomponent_set_title(
		$record["name"] . ": " . ffTemplate::_get_word_by_code("publishing_fields_title")
		, true
		, false
		, false
		, $oGrid
	);			

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oField->order_SQL = " `parent_thumb`, `order_thumb`, ID";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_name");
	$oGrid->addContent($oField);	

	$cm->oPage->addContent($oGrid);
}