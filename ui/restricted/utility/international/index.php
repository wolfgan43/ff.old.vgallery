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

if (!(AREA_INTERNATIONAL_SHOW_MODIFY || AREA_LANGUAGES_SHOW_MODIFY || AREA_CHARSET_SHOW_MODIFY)) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "international";
$oGrid->source_SQL = "SELECT * FROM " . FF_PREFIX . "international [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "InternationalModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addEvent("on_before_parse_row", "international_on_before_parse_row");

/**
* Title
*/
system_ffcomponent_set_title(
	ffTemplate::_get_word_by_code("international_title")
	, array("name" => "language")
	, false
	, false
	, $oGrid
);

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_SQL = "last_update [ORDER_DIR], ID [ORDER_DIR], word_code";
$oField->order_dir = "DESC";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->label = ffTemplate::_get_word_by_code("international_ID");
$oField->base_type = "Number";
$oGrid->addContent($oField);

$system_modules = array("restricted", "notifier", "security");

$arrModules[] = array(new ffData("area_admin"), new ffData(ffTemplate::_get_word_by_code("area_admin")));
$arrModules[] = array(new ffData("area_restricted"), new ffData(ffTemplate::_get_word_by_code("area_restricted")));
$arrModules[] = array(new ffData("area_manage"), new ffData(ffTemplate::_get_word_by_code("area_manage")));
$arrModules[] = array(new ffData("area_email"), new ffData(ffTemplate::_get_word_by_code("area_email")));

$module_file = glob(FF_DISK_PATH . "/modules/*");
if(is_array($module_file) && count($module_file)) {
    foreach($module_file AS $real_dir) {
        if(is_dir($real_dir) && array_search(basename($real_dir), $system_modules) === false) {
            $arrModules[] = array(new ffData("module_" . basename($real_dir)), new ffData(ffTemplate::_get_word_by_code("module") . " " . ucfirst(basename($real_dir))));
        }
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("international_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = $arrModules;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("international_type_default");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "word_code";
$oField->label = ffTemplate::_get_word_by_code("international_word_code");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_lang";
$oField->label = ffTemplate::_get_word_by_code("international_languages");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . FF_PREFIX . "languages.ID, " . FF_PREFIX . "languages.description FROM " . FF_PREFIX . "languages WHERE 1";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("international_description");
$oGrid->addContent($oField);

if(strpos(MASTER_SITE, DOMAIN_NAME) === false) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "revert"; 
	$oButton->class = "icon ico-revert";
	//$oButton->label = "preview";
	$oButton->action_type = "submit";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("revert");
	//$oButton->image = "edit.png";
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
}
$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("International"))); 

if (AREA_LANGUAGES_SHOW_MODIFY) {
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->id = "languagesPanel";
    $oGrid->source_SQL = "SELECT * FROM " . FF_PREFIX . "languages [WHERE] [HAVING] [ORDER]";
    $oGrid->order_default = "description";
    $oGrid->use_search = true;
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/languages/modify";
    $oGrid->record_id = "LanguagesModify";
    $oGrid->resources[] = $oGrid->record_id;
	$oGrid->addEvent("on_before_parse_row", "lang_on_before_parse_row");

    // Campi chiave
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    // Campi di ricerca

    // Campi visualizzati
    $oField = ffField::factory($cm->oPage);
    $oField->id = "description";
    $oField->label = ffTemplate::_get_word_by_code("languages_code");
    $oGrid->addContent($oField);
	
    $oField = ffField::factory($cm->oPage);
    $oField->id = "tiny_code";
    $oField->label = ffTemplate::_get_word_by_code("languages_tiny_code");
    $oGrid->addContent($oField);
	
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "visible";
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link"; 
	$oButton->label = ffTemplate::_get_word_by_code("status_frontend");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("languages"))); 
}

if(AREA_CHARSET_SHOW_MODIFY) {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "charset";
	$oGrid->source_SQL = "SELECT * FROM " . CM_TABLE_PREFIX . "charset_decode [WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->use_search = true;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/charset/modify";
	$oGrid->record_id = "CharsetModify";
	$oGrid->resources[] = $oGrid->record_id;

	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Campi di ricerca

	// Campi visualizzati
	$oField = ffField::factory($cm->oPage);
	$oField->id = "code";
	$oField->label = ffTemplate::_get_word_by_code("charset_code");
	$oGrid->addContent($oField);


	$oField = ffField::factory($cm->oPage);
	$oField->id = "value";
	$oField->label = ffTemplate::_get_word_by_code("charset_value");
	$oGrid->addContent($oField);

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("charset_decode")));	 
}

if(AREA_CURRENCY_SHOW_MODIFY) {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "currency";
	$oGrid->source_SQL = "SELECT * FROM " . FF_PREFIX . "currency [WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->use_search = true;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/currency/modify";
	$oGrid->record_id = "CurrencyModify";
	$oGrid->resources[] = $oGrid->record_id;
	// Campi chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Campi di ricerca

	// Campi visualizzati
	$oField = ffField::factory($cm->oPage);
	$oField->id = "code";
	$oField->label = ffTemplate::_get_word_by_code("currency_code");
	$oGrid->addContent($oField);


	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("currency_name");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "symbol";
	$oField->label = ffTemplate::_get_word_by_code("currency_symbol");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);

    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("currency")));	 
}


function international_on_before_parse_row($component) {
    $cm = cm::getInstance();
    if($component->db[0]->getField("is_new", "Number", true) > 0) {
        $component->row_class = "red";
    } else {
        $component->row_class = "green"; 
    }
    
    if(isset($component->grid_buttons["revert"])) {
        if($component->db[0]->getField("exclude_update", "Number", true) > 0) {
            if($component->grid_buttons["revert"]->action_type == "submit") {
                $record_url = $component->grid_buttons["revert"]->parent[0]->record_url;
                $component->grid_buttons["revert"]->form_action_url = $record_url . "?[KEYS]" . "&ret_url=" . urlencode($component->parent[0]->getRequestUri());
                if($_REQUEST["XHR_CTX_ID"]) {
                    $component->grid_buttons["revert"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'revert', fields: [], 'url' : '[[frmAction_url]]'});";
                } else {
                    $component->grid_buttons["revert"]->jsaction = "ff.ajax.doRequest({'action': 'revert', fields: [], 'url' : '[[frmAction_url]]'});";
                }   
            }
            $component->grid_buttons["revert"]->display = true;
        } else {
            $component->grid_buttons["revert"]->display = false;
        }
    }
	
}

function lang_on_before_parse_row($component) {
	
	if(isset($component->grid_buttons["visible"])) {
		if($component->db[0]->getField("status", "Number", true)) {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit"; 
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }   
	    } else {
			$component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit";     
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }    
	    }
	}
	
}
