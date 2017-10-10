<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_DRAFT_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}    

$ID_drafts = $_REQUEST["keys"]["ID"];

$sSQL = "SELECT drafts_rel_languages.value 
        FROM drafts_rel_languages
            INNER JOIN drafts ON drafts.ID = drafts_rel_languages.ID_drafts
            INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = drafts_rel_languages.ID_languages
            WHERE drafts.ID = " . $db_gallery->toSql($ID_drafts, "Number") . "
            AND " . FF_PREFIX . "languages.code = " . $db_gallery->toSql(LANGUAGE_INSET, "Text");
$db_gallery->query($sSQL);
if ($db_gallery->nextRecord()) {
    $value = $db_gallery->getField("value")->getValue();

    check_function("set_generic_tags");
    $value = set_generic_tags($value, $globals->settings_path);
}

$cm->oPage->fixed_pre_content = $value;

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oButton->action_type = "gotourl";
$oButton->url = "[RET_URL]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("back");
$oButton->parent_page = array(&$cm->oPage);

$cm->oPage->process_params();

$cm->oPage->fixed_pre_content .= "<div class=\"prev_button\" >" . $oButton->process() . "</div>";


?>