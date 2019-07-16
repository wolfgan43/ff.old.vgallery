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
if (!Auth::env("AREA_MODULES_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
//$oGrid->full_ajax = true;
$oGrid->ajax_delete	= true;
$oGrid->ajax_search	= true;
$oGrid->id = "modules";
$oGrid->title = ffTemplate::_get_word_by_code("modules");
$oGrid->source_SQL = "SELECT
                            modules.*
                            , CONCAT(UPPER(SUBSTR(module_name, 1, 1)), SUBSTR(module_name, 2)) AS component
                        FROM
                            modules
                        [WHERE]
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "component";
$oGrid->use_search = true;
$oGrid->record_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_url = get_path_by_rule("addons") . "/[module_name_VALUE]/[module_params_VALUE]";
$oGrid->record_id = "[component_VALUE]ConfigModify";
//$oGrid->resources[] = $oGrid->record_id;
$oGrid->resources[] = "modules";

$oGrid->display_new = false;
/*if($permission["drafts"] < 2) {
    
    $oGrid->display_edit_bt = false;
    $oGrid->display_edit_url = false;
    $oGrid->display_delete_bt = false;
}*/
// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "module_name";
$oField->display = false;
$oField->label = ffTemplate::_get_word_by_code("module_name");
$oGrid->addContent($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "component";
$oField->label = ffTemplate::_get_word_by_code("module_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "module_params";
$oField->label = ffTemplate::_get_word_by_code("module_params");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "database";
//$oButton->class = "icon ico-detail";
$oButton->action_type = "gotourl";
$oButton->url = get_path_by_rule("addons") . "/[module_name_VALUE]/[module_params_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&[GLOBALS]";
$oButton->aspect = "link";
//$oButton->image = "detail.png";
$oButton->label = ffTemplate::_get_word_by_code("module_detail");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid);


