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
if (!AREA_STATIC_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");
if(system_ffcomponent_switch_by_path(__DIR__, false)) {
	$disable_dialog = false;
	$db = ffDB_Sql::factory();

	if(isset($_REQUEST["repair"])) {
		$sSQL = "SELECT static_pages.* 
				FROM static_pages
				WHERE static_pages.name = ''
					AND static_pages.parent = '/'";
		$db->query($sSQL);
		if(!$db->nextRecord()) {
			$sSQL = "INSERT INTO static_pages
					(
						ID
						, name
						, parent
						, owner
						, ID_domain
						, visible
						, meta_title
						, permalink
					)
					VALUES
					(
						null
						, ''
						, '/'
						, '-1'
						, '0'
						, '1'
						, ''
						, '/'				
					)";
			$db->execute($sSQL);
		}
	}
	
	
	if($cm->real_path_info == "/home")
		$cm->real_path_info = "";
	
	$block_type = system_get_block_type();	
	
	if(isset($_REQUEST["repair"])) {
		$sSQL = "UPDATE static_pages SET
					meta_title = REPLACE(name, '-', ' ')
				WHERE meta_title = '' AND name <> ''";
		$db->execute($sSQL);
	}	
	
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->ajax_addnew = !$disable_dialog;
	$oGrid->ajax_delete = true;
	$oGrid->ajax_search = true;
	$oGrid->id = "static";
	$oGrid->source_SQL = "SELECT
	                            static_pages.ID AS ID
	                            , IF(static_pages.name = '' AND static_pages.parent = '/'
	                            	, 'Home'
	                            	, IF(static_pages.meta_title
	                            		, static_pages.meta_title
	                            		, static_pages.name
	                            	)
	                            ) AS name
	                            , static_pages.parent
	                            , static_pages.visible AS visible
	                            , IF(static_pages.name = '' AND static_pages.parent = '/'
	                            	, 'home' 
	                            	, static_pages.name
	                            ) AS smart_url
	                            , IF(static_pages.name = '' AND static_pages.parent = '/'
	                            	, '/home'
	                            	, CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) 
	                            ) AS full_path 
	                            , static_pages.location AS location
	                            , LENGTH(CONCAT(static_pages.parent, static_pages.name)) - REPLACE(CONCAT(static_pages.parent, static_pages.name), '/', '') AS level
	                            , (SELECT GROUP_CONCAT(DISTINCT CONCAT(IF(layout.ID_type = '" . $block_type["gallery"]["ID"] . "', 'Album: ', ''), layout.value) SEPARATOR ' - ')
	                				FROM layout
	                					INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
	                				WHERE layout_path.visible > 0
	                					AND layout.ID_type IN(" . $db->toSql($block_type["gallery"]["ID"], "Number") . "," . $db->toSql($block_type["virtual-gallery"]["ID"], "Number") . ")
	                					AND CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) LIKE layout_path.ereg_path
				                ) AS appearance
	                        FROM
	                            static_pages
	                        WHERE 1 
	                        " . ($cm->real_path_info
	                        	? " AND static_pages.parent LIKE '" . $db->toSql($cm->real_path_info, "Text", false) . "%'"
	                        	: ""
	                        ). "
								" . ($globals->ID_domain > 0
									? " AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number")
									: ""
								) . "
	                        [AND] [WHERE]
	                        [HAVING]
	                        [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->use_search = true;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "[parent_VALUEPATH]/[smart_url_VALUE]";
	$oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "[parent_VALUEPATH]/[smart_url_VALUE]";
	$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/add";
	$oGrid->record_id = "PageModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->addEvent("on_before_parse_row", "static_on_before_parse_row");
	$oGrid->display_new = AREA_STATIC_SHOW_ADDNEW;
	$oGrid->display_edit_bt = false;
	$oGrid->display_edit_url = AREA_STATIC_SHOW_MODIFY;
	$oGrid->display_delete_bt = AREA_STATIC_SHOW_DELETE;
	$oGrid->setWidthDialog("large");
	$oGrid->widget_deps[] = array(
		"name" => "labelsort"
		, "options" => array(
		      &$oGrid
		    , array(
		        "resource_id" => "static_dir"
		        , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
		    )
		)
	);

	$oGrid->widget_deps[] = array(
		"name" => "dragsort"
		, "options" => array(
		      &$oGrid
		    , array(
		        "resource_id" => "static_dir"
		        , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
		    )
		    , "ID"
		)
	);

	/**
	* Title
	*/
	system_ffcomponent_set_title(
		null
		, true
		, false
		, false
		, $oGrid
	);
	
	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oField->order_SQL = "level, sort";
	$oGrid->addKeyField($oField);

	// Campi di ricerca


	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("static_name");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "parent";
	$oField->label = ffTemplate::_get_word_by_code("static_parent");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "location";
	$oField->label = ffTemplate::_get_word_by_code("static_location");
	$oGrid->addContent($oField);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "edit";
	$oButton->ajax = ($disable_dialog ? false : $oGrid->record_id);
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "[full_path_VALUEPATH]?[KEYS]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("static_edit");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);	

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "appearance";
	$oButton->action_type = "gotourl";
	$oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . "/appearance[full_path_VALUEPATH]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("static_appearance");
	$oButton->icon = cm_getClassByFrameworkCss("object-group fa", "icon-tag");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);		
	
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "builder";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "[full_path_VALUEPATH]/builder";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("static_builder");
	$oButton->icon = cm_getClassByFrameworkCss("industry", "icon-tag");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);	
		
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "visible";
	$oButton->ajax = true;
	$oButton->action_type = "gotourl";
	$oButton->frmAction = "setvisible";
	$oButton->user_vars["url"] = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[smart_url_VALUE]?[KEYS]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("status_frontend");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

	if(ENABLE_STD_PERMISSION) {
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "permissions"; 
		$oButton->ajax = ($disable_dialog ? false : $oGrid_models->record_id);
		$oButton->action_type = "gotourl";
		$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/[smart_url_VALUE]/permission?[KEYS]";
		$oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("permissions");
		$oButton->display_label = false;
	    $oGrid->addGridButton($oButton);
	}

	$cm->oPage->addContent($oGrid);
}

function static_on_before_parse_row($component) {
	$cm = cm::getInstance();

	if($component->db[0]->getField("smart_url", "Text", true) == "home" && $component->db[0]->getField("parent", "Text", true) == "/") {
		$component->display_edit_url = false;
		$component->display_delete_bt = false;
	} else {
		$component->display_edit_url = true;
		$component->display_delete_bt = true;
	}

	if(isset($component->grid_buttons["appearance"])) {
	    if($component->db[0]->getField("appearance", "Text", true)) {
	    	$component->grid_buttons["appearance"]->url = $component->grid_buttons["appearance"]->user_vars["url"];
	    	$component->grid_buttons["appearance"]->display = true;
		} else{
			$component->grid_buttons["appearance"]->display = false;
		}
	}	
	
	if(isset($component->grid_buttons["visible"])) {
	    if($component->db[0]->getField("visible", "Number", true)) {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=0";
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
	    } else {
	    	$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->user_vars["url"] . "setvisible=1";
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
	    }
	}	
}