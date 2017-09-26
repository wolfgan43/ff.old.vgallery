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
if (!(AREA_TRACE_SHOW_MODIFY && defined("MOD_SEC_ENABLE_USER_TRACE") && constant("MOD_SEC_ENABLE_USER_TRACE"))) {
    FormsDialog(false, "OkOnly", ffTemplate::_get_word_by_code("dialog_title_accessdenied"), ffTemplate::_get_word_by_code("dialog_description_invalidpath"), "", $site_path . "/", THEME_INSET);
}

$db = ffDB_Sql::factory();

if($_REQUEST["TraceModify_frmAction"] == "confirmdelete" && strlen($_REQUEST["keys"]["ID"])) {
	$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_access
			WHERE " . CM_TABLE_PREFIX . "mod_security_users_access.access = " . $db->toSql($_REQUEST["keys"]["ID"]);
	$db->execute($sSQL);
		
	$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_access_tracert 
			WHERE " . CM_TABLE_PREFIX . "mod_security_users_access_tracert.access = " . $db->toSql($_REQUEST["keys"]["ID"]);
	$db->execute($sSQL);
	

	if($_REQUEST["XHR_CTX_ID"]) {
		die(ffCommon_jsonenc(array("resources" => array("TraceModify"), "close" => true, "refresh" => true), true));
	} else {
		//die(ffCommon_jsonenc(array("resources" => array("TraceModify"), "close" => true, "refresh" => true), true));
		ffRedirect($_REQUEST["ret_url"]);
	}
}


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "traceDetail";
$oGrid->title = ffTemplate::_get_word_by_code("trace_title");
$oGrid->source_SQL = "SELECT
							" . CM_TABLE_PREFIX . "mod_security_users_access_tracert.*
						FROM " . CM_TABLE_PREFIX . "mod_security_users_access_tracert
						WHERE
							" . CM_TABLE_PREFIX . "mod_security_users_access_tracert.access = " . $db->toSql($_REQUEST["keys"]["ID"]) . "
						[AND]						
						[WHERE] 
						[HAVING] 
						[ORDER]";
$oGrid->order_default = "last_update";
$oGrid->use_search = true;
$oGrid->display_new = false;
$oGrid->display_edit_url = false;
$oGrid->display_edit_bt = false;
$oGrid->display_delete_bt = false;

$oGrid->resources[] = $oGrid->record_id;


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->label = ffTemplate::_get_word_by_code("trace_detail_last_update");
$oField->base_type = "Timestamp";
$oField->extended_type = "Date";
$oField->app_type = "DateTime";
$oField->order_dir = "desc";
$oGrid->addContent($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("trace_detail_path");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "get";
$oField->label = ffTemplate::_get_word_by_code("trace_detail_get");
$oField->base_type = "Text";
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "post";
$oField->label = ffTemplate::_get_word_by_code("trace_detail_post");
$oField->base_type = "Text";
$oField->encode_entities = false;
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid); 
