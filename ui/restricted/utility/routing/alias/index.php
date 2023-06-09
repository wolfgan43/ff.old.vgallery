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

if (!AREA_ROUTING_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "alias";
$oGrid->title = ffTemplate::_get_word_by_code("alias_title");
$oGrid->source_SQL = "SELECT 
                            cache_page_alias.*
                        FROM
                            cache_page_alias
                        [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "host";
$oGrid->use_search = true;
$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?ret_url=". urlencode($cm->oPage->getRequestUri());
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "AliasModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = Auth::env("AREA_SITEMAP_SHOW_ADDNEW");
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = Auth::env("AREA_SITEMAP_SHOW_MODIFY");
//$oGrid->display_delete_bt = false;
$oGrid->addEvent("on_before_parse_row", "routing_alias_on_before_parse_row");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "host";
$oField->label = ffTemplate::_get_word_by_code("alias_host");
$oGrid->addContent($oField);

 $oField = ffField::factory($cm->oPage);
$oField->id = "destination";
$oField->label = ffTemplate::_get_word_by_code("alias_destination");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "status";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("alias_status");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid);

    
function routing_alias_on_before_parse_row($component) {
    $cm = cm::getInstance();

    if(isset($component->grid_buttons["status"])) {
        $record_url = $component->grid_buttons["status"]->parent[0]->record_url;

        if($component->db[0]->getField("status", "Number", true)) {
            $component->grid_buttons["status"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
            $component->grid_buttons["status"]->icon = null;
            if(0) {
                $component->grid_buttons["status"]->action_type = "gotourl";
                $component->grid_buttons["status"]->url = $record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            } else {
                $component->grid_buttons["status"]->action_type = "submit";
                $component->grid_buttons["status"]->form_action_url = $record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setvisible=0&ret_url=" . urlencode($component->parent[0]->getRequestUri()); 
                if($_REQUEST["XHR_CTX_ID"]) {
                    $component->grid_buttons["status"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                } else {
                    $component->grid_buttons["status"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                }
            }   
        } else {
            $component->grid_buttons["status"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
            $component->grid_buttons["status"]->icon = null;
            if(0) {
                $component->grid_buttons["status"]->action_type = "gotourl";
                $component->grid_buttons["status"]->url = $record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            } else {
                $component->grid_buttons["status"]->action_type = "submit";     
                $component->grid_buttons["status"]->form_action_url = $record_url . "?[KEYS]" . $component->grid_buttons["status"]->parent[0]->addit_record_param . "setvisible=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());

                if($_REQUEST["XHR_CTX_ID"]) {
                    $component->grid_buttons["status"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                } else {
                    $component->grid_buttons["status"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                }
            }    
        }
    }
}

