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

$record = system_ffComponent_resolve_record("module_search", array(
	"name" => "IF(module_search.display_name = ''
				        , REPLACE(module_search.name, '-', ' ')
				        , module_search.display_name
				    )"
));

if(isset($_REQUEST["repair"])) {
	$sSQL = "UPDATE module_search_fields SET
				display_name = REPLACE(name, '-', ' ')
			WHERE display_name = '' AND name <> ''";
	$db->execute($sSQL);

	$sSQL = "SELECT module_search_fields.*
			FROM module_search_fields
			WHERE 1";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrVgalleryField[$db->getField("ID", "Number", true)] = ffCommon_url_rewrite($db->getField("name", "Text", true));
		} while($db->nextRecord());

		if(is_array($arrVgalleryField) && count($arrVgalleryField)) {
			foreach($arrVgalleryField AS $ID_field => $smart_url) {
				$sSQL = "UPDATE module_search_fields SET
							name = " . $db->toSql($smart_url) . "
						WHERE module_search_fields.ID = " . $db->toSql($ID_field, "Number");
				$db->execute($sSQL);				
			}
		}
	}
}


$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SearchConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_search";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_search.*
									, IF(module_search.display_name = ''
										, REPLACE(module_search.name, '-', ' ')
										, module_search.display_name
									) AS display_name
								FROM module_search 
								WHERE module_search.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");
$oRecord->tab = true;

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

if($_REQUEST["keys"]["ID"]) {
    $group_settings = "settings";
    $oRecord->addContent(null, true, $group_settings); 
    $oRecord->groups[$group_settings] = array(
        "title" => ffTemplate::_get_word_by_code("layout_" . $group_settings)
      );  
}

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("search_config_name");
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oField->container_class = "hidden";
$oRecord->addContent($oField, $group_settings);

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("search_config_name");
$oField->required = true;
$oRecord->addContent($oField, $group_settings);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("publishing_area");
$oField->widget = "actex";
$oField->multi_pairs = array (
                            array(new ffData("anagraph"), new ffData(ffTemplate::_get_word_by_code("anagraph"))),
                            array(new ffData("gallery"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
                            array(new ffData("vgallery"), new ffData(ffTemplate::_get_word_by_code("vgallery")))
                       );      
$oField->actex_child = "contest";
if($_REQUEST["keys"]["ID"]) {
    $oField->properties["disabled"] = "disabled"; 
}
$oRecord->addContent($oField, $group_settings);

$oField = ffField::factory($cm->oPage);
$oField->id = "contest";
$oField->label = ffTemplate::_get_word_by_code("publishing_contest");
$oField->widget = "actex";
$oField->source_SQL = "
                        SELECT nameID, name, type FROM
                        (
	                        (
		                        SELECT 
		                            name AS nameID, 
		                            name,
		                            'vgallery' AS type
		                        FROM 
		                            vgallery
		                        WHERE vgallery.status > 0
	                        ) 
	                        UNION 
	                        (
		                        SELECT 
		                            'files' AS nameID, 
		                            'files' AS name,
		                            'gallery' AS type
	                        )
                                UNION 
	                        (
		                        SELECT 
                                                anagraph_categories.smart_url AS nameID
                                                , anagraph_categories.name AS name
                                                , 'anagraph' AS type
                                            FROM
                                                anagraph_categories
	                        )
                        ) AS tbl_src
                        [WHERE]";  
$oField->actex_father = "area";
$oField->actex_child = "relative_path";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;
if($_REQUEST["keys"]["ID"]) {
    $oField->properties["disabled"] = "disabled"; 
}
$oRecord->addContent($oField, $group_settings);

$oField = ffField::factory($cm->oPage);
$oField->id = "relative_path";
$oField->label = ffTemplate::_get_word_by_code("search_relative_path");
$oField->actex_hide_empty = true;
$oField->widget = "actex";
$oField->source_SQL = "
                    SELECT nameID, name, type FROM
                    (
                        (
	                        SELECT 
	                            IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS nameID
	                            , IF(SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1) = '', '/', SUBSTRING(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name), LENGTH(CONCAT('/', vgallery.name)) + 1)) AS name
	                            , vgallery.name AS type
	                        FROM
	                            vgallery_nodes
	                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
	                        WHERE vgallery_nodes.name <> ''
	                        	AND vgallery_nodes.is_dir > 0
	                        HAVING name <> '/'
	                        ORDER BY type, name
                        )
                    ) AS tbl_src
                    [WHERE]
                    ORDER BY tbl_src.name";
$oField->actex_father = "contest";
$oField->actex_hide_empty = "all";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;    
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("all");
$oField->multi_select_one_val = new ffData("/");
$oRecord->addContent($oField, $group_settings);

if($_REQUEST["keys"]["ID"] > 0)
{
	$oRecord->addContent(null, true, "field"); 

	$group_field = "field";
	$oRecord->groups[$group_field] = array(
        "title" => ffTemplate::_get_word_by_code("search_config_fields")
    );

	$oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->ajax_addnew = true;
    $oGrid->ajax_delete = true;
    $oGrid->ajax_search = true;
    $oGrid->id = "SearchFields";
    $oGrid->source_SQL = "SELECT module_search_fields.*  
		                FROM module_search_fields
		                WHERE module_search_fields.ID_module = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
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
    $oGrid->record_id = "SearchExtraFieldModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->buttons_options["export"]["display"] = false;
    $oGrid->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid
            , array(
                "resource_id" => "search_fields"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
            , "ID"
        )
    );

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oField->order_SQL = " `order`";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("search_config_fields_name");
    $oField->required = true;
    $oGrid->addContent($oField);

    $oRecord->addContent($oGrid, "field");
    $cm->oPage->addContent($oGrid);
}   
    
$cm->oPage->addContent($oRecord);
