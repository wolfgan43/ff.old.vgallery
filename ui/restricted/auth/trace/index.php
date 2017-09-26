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
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "trace";
$oGrid->title = ffTemplate::_get_word_by_code("trace_title");
$oGrid->source_SQL = "SELECT
							access AS ID
							, " . CM_TABLE_PREFIX . "mod_security_users_access.last_update AS last_update
							, " . CM_TABLE_PREFIX . "mod_security_users_access.remote_agent AS remote_agent
							, " . CM_TABLE_PREFIX . "mod_security_users.username AS username
							, " . FF_SUPPORT_PREFIX . "state.name AS state_name
							, IF(LOCATE('http', " . CM_TABLE_PREFIX . "mod_security_users_access.remote_agent)
								, ''
								, " . CM_TABLE_PREFIX . "mod_security_users_access.sess_id
							) AS bot_finder
						FROM " . CM_TABLE_PREFIX . "mod_security_users_access
							LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = " . CM_TABLE_PREFIX . "mod_security_users_access.ID_user
							LEFT JOIN " . FF_SUPPORT_PREFIX . "state ON " . FF_SUPPORT_PREFIX . "state.ID = " . CM_TABLE_PREFIX . "mod_security_users_access.ID_state
						[WHERE] 
						GROUP BY 
							" . CM_TABLE_PREFIX . "mod_security_users_access.ID_user
							, " . CM_TABLE_PREFIX . "mod_security_users_access.ID_state
							, " . CM_TABLE_PREFIX . "mod_security_users_access.remote_agent
							, bot_finder
						[HAVING] 
						[ORDER]";
$oGrid->order_default = "last_update";
$oGrid->use_search = true;
$oGrid->display_new = false;
$oGrid->display_delete_bt = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify"; 
$oGrid->record_id = "TraceModify";
$oGrid->resources[] = $oGrid->record_id;

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->label = ffTemplate::_get_word_by_code("trace_last_update");
$oField->base_type = "Timestamp";
$oField->extended_type = "DateTime";
$oField->app_type = "DateTime";
$oField->order_dir = "desc";
$oGrid->addContent($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oField->label = ffTemplate::_get_word_by_code("trace_username");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "state_name";
$oField->label = ffTemplate::_get_word_by_code("trace_state_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "remote_agent";
$oField->label = ffTemplate::_get_word_by_code("trace_remote_agent");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid); 