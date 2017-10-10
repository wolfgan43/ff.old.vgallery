<?php
if (!AREA_UPDATER_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
}

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ExternalsModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("externals_title");
$oRecord->src_table = "updater_externals";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oField->label = ffTemplate::_get_word_by_code("externals_path");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "domain";
$oField->label = ffTemplate::_get_word_by_code("externals_domain");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("externals_status");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("active"))),
                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("disactive")))
                       );
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);
?>