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

if (!(Auth::env("AREA_PUBLISHING_SHOW_ADDNEW") || Auth::env("AREA_PUBLISHING_SHOW_MODIFY") || Auth::env("AREA_PUBLISHING_SHOW_DELETE") || Auth::env("AREA_PUBLISHING_SHOW_DETAIL"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
} 
check_function("system_ffcomponent_set_title");

if(system_ffcomponent_switch_by_path(__DIR__, array("modify", "structure/modify"))) {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "publishing";
	//$oGrid->title = ffTemplate::_get_word_by_code("publishing");
	$oGrid->source_SQL = "SELECT
	                            publishing.*
	                        FROM
	                            publishing
	                        [WHERE]
	                        [HAVING]	
	                        [ORDER]";

	$oGrid->order_default = "name";
	$oGrid->use_search = true;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
	$oGrid->record_id = "PublishingModify";
	$oGrid->resources[] = $oGrid->record_id;
	//$oGrid->addit_insert_record_param = "extype=publishing&";
	//$oGrid->addit_record_param = "extype=publishing&";
	$oGrid->display_new = Auth::env("AREA_PUBLISHING_SHOW_ADDNEW");
	$oGrid->display_edit_bt = false;
	$oGrid->display_edit_url = Auth::env("AREA_PUBLISHING_SHOW_MODIFY");
	$oGrid->display_delete_bt = Auth::env("AREA_PUBLISHING_SHOW_DELETE");

	/**
	* Title
	*/
	system_ffcomponent_set_title(
		ffTemplate::_get_word_by_code("publishing")
		, true
		, false
		, false
		, $oGrid
	);	

	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Campi di ricerca


	// Campi visualizzati
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("publishing_name");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "area";
	$oField->label = ffTemplate::_get_word_by_code("publishing_area");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "contest";
	$oField->label = ffTemplate::_get_word_by_code("publishing_contest");
	$oGrid->addContent($oField);


	if(Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->ajax = $oGrid->record_id;
	    $oButton->id = "properties"; 
	    $oButton->action_type = "gotourl";
	    $oButton->url = get_path_by_rule("blocks-appearance") ."?extype=publishing&path=/[publishing_name_VALUE]";
	    $oButton->aspect = "link";
		//$oButton->image = "layout_setting.png";
		$oButton->label = ffTemplate::_get_word_by_code("layout_setting");
		$oButton->display_label = false;
	    $oGrid->addGridButton($oButton);
	}

	if(Auth::env("AREA_PUBLISHING_SHOW_DETAIL")) {
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "addnew";
	    $oButton->action_type = "gotourl";
	    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/[publishing_name_VALUE]/contents?[KEYS]";
	    $oButton->aspect = "link";
		//$oButton->image = "add.png";
		$oButton->label = ffTemplate::_get_word_by_code("publishing_add");
		$oButton->display_label = false;
	    $oGrid->addGridButton($oButton);
	}

	if(Auth::env("AREA_PUBLISHING_SHOW_DETAIL")) {
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "preview";
	    $oButton->action_type = "gotourl";
	    $oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/[publishing_name_VALUE]/preview?[KEYS]";
	    $oButton->aspect = "link";
		//$oButton->image = "preview.png";
		$oButton->label = ffTemplate::_get_word_by_code("preview");
		$oButton->display_label = false;
	    $oGrid->addGridButton($oButton);
	}

	$cm->oPage->addContent($oGrid);
}
