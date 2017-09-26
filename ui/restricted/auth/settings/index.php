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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!AREA_SETTINGS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$tpl = ffTemplate::factory(get_template_cascading("/", "settings.html"));
$tpl->load_file("settings.html", "main");
$tpl->set_var("row_class", cm_getClassByFrameworkCss("", "row-default"));

$oField = ffField::factory($cm->oPage);
$oField->id = "paths";
$oField->label = ffTemplate::_get_word_by_code("settings_path");
//$oField->properties["onchange"] = "document." . $cm->oPage->form_name . ".submit();";
$oField->parent_page = array(&$cm->oPage);
$tpl->set_var("paths", $oField->process());

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "addnew";
$oButton->action_type = "submit";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("settings_addnew");//Definita nell'evento
$oButton->parent_page = array(&$cm->oPage);
$oButton->properties["onclick"] = "if(document.getElementById('paths').value) { window.location.href = '" . $cm->oPage->site_path . $cm->oPage->page_path . "/modify/'+document.getElementById('paths').value + '?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "'; } return false;";
$tpl->set_var("addnew", $oButton->process());

$tpl->parse("SezSettingsAddnew", false);
$tpl->set_var("SezSettingsModify", "");


$cm->oPage->addContent($tpl->rpparse("main", false));

$oGrid = ffGrid::factory($cm->oPage);
//$oGrid->full_ajax = true;
$oGrid->id = "SettingsVGallery";
$oGrid->title = ffTemplate::_get_word_by_code("settingsvgallery_title");
$oGrid->source_SQL = "SELECT DISTINCT
                            `settings_rel_path`.path AS ID
                            , `settings_rel_path`.path
                        FROM
                            `settings_rel_path`
                        [WHERE] [ORDER]";

$oGrid->order_default = "path";
$oGrid->use_search = false;
$oGrid->display_delete_bt = false;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = false;
$oGrid->display_new = false;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "SettingsVGalleryModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("settingsvgallery_path");
$oGrid->addContent($oField);

/*
$oField = ffField::factory($cm->oPage);
$oField->id = "parent";
$oField->label = ffTemplate::_get_word_by_code("edit_general_path");
$oGrid->addContent($oField);*/

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "editrow";
//$oButton->label = "preview";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify[SettingsVGallery_path_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("edit");
//$oButton->image = "edit.png";
$oButton->display_label = false;
$oGrid->addGridButton($oButton);


$cm->oPage->addContent($oGrid);

