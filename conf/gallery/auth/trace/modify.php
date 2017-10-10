<?php
if (!(AREA_TRACE_SHOW_MODIFY && defined("MOD_SEC_ENABLE_USER_TRACE") && constant("MOD_SEC_ENABLE_USER_TRACE"))) {
    FormsDialog(false, "OkOnly", ffTemplate::_get_word_by_code("dialog_title_accessdenied"), ffTemplate::_get_word_by_code("dialog_description_invalidpath"), "", $site_path . "/", THEME_INSET);
}

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if($_REQUEST["TraceModify_frmAction"] == "confirmdelete" && strlen($_REQUEST["keys"]["ID"])) {
	$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_access
			WHERE " . CM_TABLE_PREFIX . "mod_security_users_access.access = " . $db_gallery->toSql($_REQUEST["keys"]["ID"]);
	$db_gallery->execute($sSQL);
		
	$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_users_access_tracert 
			WHERE " . CM_TABLE_PREFIX . "mod_security_users_access_tracert.access = " . $db_gallery->toSql($_REQUEST["keys"]["ID"]);
	$db_gallery->execute($sSQL);
	

	if($_REQUEST["XHR_DIALOG_ID"]) {
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
							" . CM_TABLE_PREFIX . "mod_security_users_access_tracert.access = " . $db_gallery->toSql($_REQUEST["keys"]["ID"]) . "
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
?>
