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
	$oGrid->id = "FBAlbumConfig";
	$oGrid->source_SQL = "SELECT 
							module_fbalbum.*
							 , IF(module_fbalbum.display_name = ''
								, REPLACE(module_fbalbum.name, '-', ' ')
								, module_fbalbum.display_name
							) AS display_name 
						FROM module_fbalbum 
						[WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "display_name";
	$oGrid->use_search = FALSE;
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/add";
	$oGrid->record_id = "FBAlbumConfigModify";
	$oGrid->resources[] = $oGrid->record_id;

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
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("fbalbum_name");
	$oGrid->addContent($oField); 

	$oField = ffField::factory($cm->oPage);
	$oField->id = "facebookID";
	$oField->container_class = "facebookID";
	$oField->label = ffTemplate::_get_word_by_code("fbalbum_facebookID");
	$oGrid->addContent($oField); 

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "preview";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview/[name_VALUE]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("preview");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

	$cm->oPage->addContent($oGrid);
}