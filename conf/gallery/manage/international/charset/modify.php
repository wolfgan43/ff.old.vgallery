<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_CHARSET_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "CharsetModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("charset_title");
$oRecord->src_table = CM_TABLE_PREFIX . "charset_decode";
$oRecord->addEvent("on_done_action", "CharsetModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oField->label = ffTemplate::_get_word_by_code("charset_code");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("charset_value");
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

function CharsetModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

	switch($action) {
		case "insert":
		case "delete":
			$sSQL = "UPDATE 
	                    `layout` 
	                SET 
	                    `layout`.`last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
	                ";
	        $db->execute($sSQL);
		default:		
	}

}
?>