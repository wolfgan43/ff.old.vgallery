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

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

if(system_ffcomponent_switch_by_path(__DIR__)) {
	$disable_dialog = true;

	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->ajax_addnew = true;
	$oGrid->ajax_edit = false;
	$oGrid->ajax_delete = true;
	$oGrid->ajax_search = true;
	$oGrid->id = "ShareConfig";
	$oGrid->source_SQL = "SELECT * FROM module_share [WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "name";
	$oGrid->use_search = FALSE;
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/add";
	$oGrid->record_id = "ShareConfigModify";
	$oGrid->resources[] = $oGrid->record_id;
	//$oGrid->display_labels = false;

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
    
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("share_name");
	$oField->base_type = "Text";
	$oGrid->addContent($oField); 

	$oField = ffField::factory($cm->oPage);
	$oField->id = "service_type";
	$oField->label = ffTemplate::_get_word_by_code("share_service_type");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
	                            array(new ffData("addthis"), new ffData("Add This"))
                            		, array(new ffData("sharethis"), new ffData("Share This")) 
	                       );
	$oGrid->addContent($oField); 

	$cm->oPage->addContent($oGrid);
}