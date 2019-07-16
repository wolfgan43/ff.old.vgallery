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
if (!Auth::env("AREA_SECTION_SHOW_MODIFY")) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
$db = ffDB_Sql::factory();
$globals = ffGlobals::getInstance("gallery");

$ID_layout_location = $_REQUEST["keys"]["ID"];
$layout_path = stripslash($_REQUEST["path"]);
if($layout_path == "")
	$layout_path = "/";


// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SectionModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("section_modify_title");
$oRecord->src_table = "layout_location";
$oRecord->addEvent("on_loaded_data", "SectionModify_on_loaded_data");
$oRecord->addEvent("on_do_action", "SectionModify_on_do_action");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
$oRecord->auto_populate_edit = true;

$oRecord->populate_edit_SQL = "SELECT layout_location.*
									FROM layout_location
									WHERE layout_location.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("section_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_layer";
$oField->label = ffTemplate::_get_word_by_code("section_modify_layer");
$oField->base_type = "Number";
$oField->widget = "actex";

$oField->source_SQL = "SELECT layout_layer.ID
                         , layout_layer.name
                        FROM layout_layer 
                        WHERE 1
                        ORDER BY layout_layer.`order`, layout_layer.name";
$oField->actex_update_from_db = true;
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_url = get_path_by_rule("pages-structure") . "/layer/modify"; //"?template=" . $ID_template;
$oField->resources[] = "LayerModify";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "is_main";
$oField->label = ffTemplate::_get_word_by_code("section_is_main");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField);
	
$oField = ffField::factory($cm->oPage);
$oField->id = "show_empty";
$oField->label = ffTemplate::_get_word_by_code("section_show_empty");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField);

$oRecord->addContent(null, true, "path"); 
$oRecord->groups["path"] = array(
				                 "title" => ffTemplate::_get_word_by_code("layout_location_path_title")
				                 , "cols" => 1
				                 
				              );
$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "LayoutLocationPath";
$oDetail->title = ffTemplate::_get_word_by_code("layout_location_path_title");
$oDetail->src_table = "layout_location_path";
$oDetail->order_default = "path";
$oDetail->fields_relationship = array ("ID_layout_location" => "ID");
//$oDetail->display_new_location = "Footer";
$oDetail->display_grid_location = "Footer";
$oDetail->min_rows = 1;
$oDetail->display_rowstoadd = false;
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
										'*' AS path
										, 1 AS visible
										, 1 AS cascading";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT *
									FROM
									(
										(
											SELECT 
												layout_location_path.ID AS ID
												, layout_location_path.path AS real_path
												, (REPLACE(REPLACE(REPLACE(layout_location_path.path, '%', '*'), '(.*)', '*'), '(.+)', '*')) AS path
												, layout_location_path.visible AS visible
												, layout_location_path.cascading AS cascading
												, layout_location_path.class
												, layout_location_path.default_grid
												, layout_location_path.grid_md
												, layout_location_path.grid_sm
												, layout_location_path.grid_xs
												, layout_location_path.fluid
												, layout_location_path.wrap
												, layout_location_path.width
											FROM layout_location_path
												INNER JOIN layout_location on layout_location.ID = layout_location_path.ID_layout_location
											WHERE layout_location_path.ID_layout_location = [ID_FATHER]
											ORDER BY layout_location_path.path DESC
												, layout_location_path.ID
										)
									) AS tbl_src
									ORDER BY LENGTH(real_path)
								";
$oDetail->addEvent("on_done_action", "LayoutLocationPath_on_done_action"); 
$oDetail->addEvent("on_before_process_row", "LayoutLocationPath_on_before_process_row");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->class = "layout-path";
$oField->container_class = "layout-path-rule " . Cms::getInstance("frameworkcss")->get(array(4), "col");
$oField->label = ffTemplate::_get_word_by_code("layout_location_path");
$oField->extended_type = "Selection";
$oField->widget = "autocomplete";
$oField->autocomplete_minLength = 0;
$oField->autocomplete_combo = true;
$oField->autocomplete_compare_having = "name";
$oField->autocomplete_readonly = false;
$oField->autocomplete_operation = "LIKE [[VALUE]%]"; 
$oField->source_SQL = "SELECT 
							CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS ID 
							, CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS name
						FROM static_pages
						WHERE 1
							AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
						[AND] [WHERE]
						[HAVING]
						[ORDER] [COLON] name
						[LIMIT]";
$oField->actex_update_from_db = true;
$oField->required = true;
$oField->multi_select_one = false;
$oField->default_value = new ffData("*");
$oField->properties["onkeyup"] = "javascript:ff.cms.admin.path(this)"; 
$oField->properties["onblur"] = "javascript:ff.cms.admin.path(this, true)"; 
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->label = ffTemplate::_get_word_by_code("layout_location_visible");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = $oField->checked_value;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cascading";
$oField->class = "layout-cascading";
$oField->label = ffTemplate::_get_word_by_code("layout_location_cascading");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
$oField->default_value = $oField->checked_value;
$oField->properties["onchange"] = "javascript:ff.cms.admin.pathCascading(this);";      
$oDetail->addContent($oField);

if(check_function("set_fields_grid_system"))
	set_fields_grid_system($oDetail);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);

function SectionModify_on_loaded_data($component) {
    if(strtolower($component->form_fields["name"]->getValue()) == "content") {
        $component->form_fields["name"]->control_type = "label";
        $component->buttons_options["delete"]["display"] = false; 
    } else {
        $component->form_fields["name"]->control_type = "";
        $component->buttons_options["delete"]["display"] = true; 
    }    
}

function SectionModify_on_do_action($component, $action) {
	if(strlen($action)) {
		$component->form_fields["name"]->setValue(preg_replace('/[^a-zA-Z0-9\-]/', '', ffCommon_remove_hypens($component->form_fields["name"]->getValue())));
	}	
}


function LayoutLocationPath_on_done_action($component, $action) {
	$db = ffDB_Sql::factory();

	switch($action) {
		case "insert":
		case "update":
			if(is_array($component->recordset) && count($component->recordset)) {
				$arrLayoutPath = array();
				$arrLayoutPathDelete = array();

				foreach($component->recordset AS $key => $value) {
					$tmp_path = $value["path"]->getValue();
					do {
						$tmp_path = str_replace("//", "/*/", $tmp_path);
					} while(str_replace("//", "/*/", $tmp_path) != $tmp_path);

					$cascading = $value["cascading"]->getValue();
					$ID_layout_path = $value["ID"]->getValue();
					$real_path = "";
					$is_cascading = false;

					if(preg_replace('/[^a-zA-Z0-9\-\/]/', '', $tmp_path) != $tmp_path) {
						$arrPath = explode("/", $tmp_path);
						if(is_array($arrPath) && count($arrPath)) {
							foreach($arrPath AS $arrPath_key => $arrPath_value) {
								if(preg_replace('/[^a-zA-Z0-9\-\/]/', '', $arrPath_value) != $arrPath_value)
									break;

								if(strlen($arrPath_value)) {
									if(strlen($real_path))
										$real_path .= "/";

									$real_path .= $arrPath_value;
								}
							}
						}
						
						$real_path = $tmp_path;
						if(substr($real_path, -1) == "*") {
							$real_path = substr($real_path, 0,-1);
							$is_cascading = true;
						}

						//$real_path = str_replace("*", "(.+)", $real_path);
						$real_path = str_replace("*", "%", $real_path); //experimental

						if($is_cascading && $cascading) {
							//$real_path .= "(.*)";
							$real_path .= "%"; //experimental
						} 
						//$real_ereg_path = str_replace("(.+)(.*)", "(.+)/(.*)", $real_ereg_path);
					} else {
						$real_path = $tmp_path;
					}
					if(array_key_exists($real_path, $arrLayoutPath)) {
						$arrLayoutPathDelete[] = $ID_layout_path;
					} else {
						$arrLayoutPath[$real_path] = array("ID" => $ID_layout_path
																, "path" => $real_path
															);
					}					
				}

				if(is_array($arrLayoutPath) && count($arrLayoutPath)) {
					ksort($arrLayoutPath);
					
					foreach($arrLayoutPath AS $arrLayoutPath_key => $arrLayoutPath_value) {
						if(strlen($arrLayoutPath_value["path"])) {
							$sSQL = "UPDATE layout_location_path SET 
										path = " . $db->toSql($arrLayoutPath_value["path"]) . "
									WHERE layout_location_path.ID = " . $db->toSql($arrLayoutPath_value["ID"], "Number");
							$db->execute($sSQL);
						}
					}
				}
				if(is_array($arrLayoutPathDelete) && count($arrLayoutPathDelete)) {
					$sSQL = "DELETE FROM layout_location_path WHERE ID IN(" . $db->toSql(implode(",", $arrLayoutPathDelete), "Text", false) . ")";
					$db->execute($sSQL);
				}				

			    $sSQL = "DELETE FROM cache_sid WHERE unic_key LIKE " . $db->toSql("unic_admin_menu%");
				$db->query($sSQL);
				
				$cache = get_session("cache");
				unset($cache["sid"]);
				set_session("cache", $cache);
			}
			break;
		default:
	}
	
}

function LayoutLocationPath_on_before_process_row($component, $record, $elem) 
{
	if(isset($component->recordset[$elem]["grid_md"]) && isset($component->recordset[$elem]["grid_sm"]))
	{
		$sum_col = $component->recordset[$elem]["default_grid"]->getValue() + $component->recordset[$elem]["grid_md"]->getValue() + $component->recordset[$elem]["grid_sm"]->getValue();
		if(isset($component->recordset[$elem]["grid_xs"]))
			$sum_col += $component->recordset[$elem]["grid_xs"]->getValue();
		if(!$sum_col)
		{
			$component->recordset[$elem]["default_grid"]->setValue("12");
			$component->recordset[$elem]["grid_md"]->setValue("12");
			$component->recordset[$elem]["grid_sm"]->setValue("12");
			$component->recordset_ori[$elem]["default_grid"]->setValue("12");
			$component->recordset_ori[$elem]["grid_md"]->setValue("12");
			$component->recordset_ori[$elem]["grid_sm"]->setValue("12");
			if(isset($component->recordset[$elem]["grid_xs"])) {
				$component->recordset[$elem]["grid_xs"]->setValue("12");
				$component->recordset_ori[$elem]["grid_xs"]->setValue("12");
			}
		}
	}
	
}