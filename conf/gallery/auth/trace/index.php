<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

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
?>