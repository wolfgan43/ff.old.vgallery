<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_LANGUAGES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}


if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && isset($_REQUEST["keys"]["ID"])) {
	$db = ffDB_Sql::factory();
	$sSQL = "UPDATE " . FF_PREFIX . "languages
				SET " . FF_PREFIX . "languages.status = " . $db->toSql($_REQUEST["setvisible"], "Number") . "
				WHERE " . FF_PREFIX . "languages.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
    
    @unlink(CM_CACHE_PATH . "/locale." . FF_PHP_EXT);
    @unlink(CM_CACHE_PATH . "/locale-nocurrent." . FF_PHP_EXT);
    
	die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("LanguagesModify")), true));
   
}
// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "LanguagesModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("languages_title");
$oRecord->src_table = FF_PREFIX . "languages";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code";
$oField->label = ffTemplate::_get_word_by_code("languages_code");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("languages_status") . (($_REQUEST["keys"]["ID"] == LANGUAGE_DEFAULT_ID) ? " (" . ffTemplate::_get_word_by_code("language_default") . ")" : "");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("not_active"))),
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("active")))
                       );
if($_REQUEST["keys"]["ID"] == LANGUAGE_DEFAULT_ID) {
	$oField->control_type = "label";
} else {
	$oField->required = true;
}
$oField->default_value = new ffData("1", "Number"); 
$oField->multi_select_one = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tiny_code";
$oField->label = ffTemplate::_get_word_by_code("languages_tiny_code");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("languages_description");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "stopwords";
$oField->label = ffTemplate::_get_word_by_code("stopwords");
$oField->widget = "autocomplete";
$oField->source_SQL = "SELECT stopwords
						, stopwords 
					FROM " . FF_PREFIX . "languages 
					WHERE ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number") . " 
					[AND] [WHERE] 
					[HAVING]
					[ORDER] [COLON] stopwords
					[LIMIT]";
$oField->actex_update_from_db = true;
$oField->autocomplete_minLength = 0;
$oField->autocomplete_readonly = false;
$oField->autocomplete_compare = "stopwords";
$oField->autocomplete_operation = "LIKE [[VALUE]%]";
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);
?>