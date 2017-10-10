<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_NOTIFY_SHOW_MODIFY) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$user_permission = get_session("user_permission");
$uid = $user_permission["ID"];

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "NotifyPreview";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("notify_preview_title");
$oRecord->src_table = "notify_message";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->buttons_options["update"]["display"] = false;


$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("notify_preview_area");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData(basename(VG_SITE_ADMIN)), new ffData(basename(VG_SITE_ADMIN))),
                            array(new ffData(basename(VG_SITE_RESTRICTED)), new ffData(basename(VG_SITE_RESTRICTED))), 
                            array(new ffData(basename(VG_SITE_MANAGE)), new ffData(basename(VG_SITE_MANAGE)))
                       );      
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("nothing");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("notify_preview_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("information"), new ffData(ffTemplate::_get_word_by_code("information"))),
                            array(new ffData("warning"), new ffData(ffTemplate::_get_word_by_code("warning")))
                       );      
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "count";
$oField->label = ffTemplate::_get_word_by_code("notify_preview_count");
$oField->control_type = "label";
$oField->base_type = "Number";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("notify_preview_title");
$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "message";
$oField->label = ffTemplate::_get_word_by_code("notify_preview_message");
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->control_type = "textarea";
$oField->control_type = "label";
$oField->encode_entities = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "url";
$oField->label = ffTemplate::_get_word_by_code("notify_preview_url");
$oField->control_type = "label";
$oField->extended_type = "email";
$oField->template_file = "ffControl_link.html";
$oRecord->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview";
$oButton->action_type = "gotourl";
$oButton->url = FF_SITE_PATH . $cm->oPage->page_path . "/modify?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("edit");
$oRecord->addActionButton($oButton);
//$oButton->label = "Anteprima";//Definita nell'evento


$cm->oPage->addContent($oRecord);

?>