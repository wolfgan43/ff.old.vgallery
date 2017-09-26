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

if (!AREA_DRAFT_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}    

check_function("system_ffcomponent_set_title");
$record = system_ffComponent_resolve_record("drafts");

$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "DraftModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "drafts";
$oRecord->buttons_options["delete"]["display"] = AREA_DRAFT_SHOW_DELETE;
$oRecord->addEvent("on_done_action", "DraftModify_on_done_action");
$oRecord->insert_additional_fields["owner"] =  new ffData(get_session("UserNID"), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

/**
* Title
*/
system_ffcomponent_set_title(
	$record["name"]
	, true
	, false
	, false
	, $oRecord
);

$labelWidth = array(2,3,5,5);	                
	
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("html_modify_name");
$oField->required = true;
$oField->setWidthLabel($labelWidth);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "hidden";
$oField->required = true;
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oRecord->addContent($oField);

if($globals->ID_domain > 0) {
	$oRecord->additional_fields["ID_domain"] =  new ffData($globals->ID_domain, "Number");
} else {
	$sSQL = "SELECT cache_page_alias.* FROM cache_page_alias WHERE `status` > 0";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_domain";
		$oField->label = ffTemplate::_get_word_by_code("drafts_modify_domain");
		$oField->widget = "actex";
		$oField->source_SQL = "SELECT cache_page_alias.`ID`
									, cache_page_alias.`host`
								FROM cache_page_alias
								WHERE cache_page_alias.`status` > 0
								ORDER BY cache_page_alias.`host`";
		$oField->actex_update_from_db = true;
		$oRecord->addContent($oField);
	}	
}


$oDetail = ffDetails::factory($cm->oPage);
if(check_function("get_locale")) {
	$arrLang = get_locale("lang", true);
	if(count($arrLang) > 1) {
	    $oDetail->tab = "right";
    	$oDetail->tab_label = "language";
        $oDetail->display_group_title = false;
	}
}
$oDetail->id = "DraftsDetail";
$oDetail->src_table = "drafts_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_drafts" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
                                    " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language 
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                " . FF_PREFIX . "languages.status = '1'
                                ORDER BY IF(" . FF_PREFIX . "languages.ID = " . LANGUAGE_DEFAULT_ID . "
                                            , 0
                                            , " . FF_PREFIX . "languages.description)";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    drafts_rel_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , drafts_rel_languages.title AS title
                                    , drafts_rel_languages.value AS value
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN drafts_rel_languages ON  drafts_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND drafts_rel_languages.ID_drafts = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
                                GROUP BY " . FF_PREFIX . "languages.ID
                                ORDER BY IF(" . FF_PREFIX . "languages.ID = " . LANGUAGE_DEFAULT_ID . "
                                            , 0
                                            , " . FF_PREFIX . "languages.description)";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("drafts_detail_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("drafts_detail_ID_languages");
$oField->base_type = "Number";
$oField->required = true;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("drafts_detail_value");
$oField->display_label = false;
$oField->extended_type = "Text";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
	$oField->ckeditor_group_by_auth = true;
}
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
     
$cm->oPage->addContent($oRecord);   


function DraftModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
    	if(check_function("refresh_cache")) {
    		refresh_cache("D", $component->key_fields["ID"]->getValue(), "update");
		}
    }
}