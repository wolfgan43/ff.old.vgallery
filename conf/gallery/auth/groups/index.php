<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if(!AREA_GROUPS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

/*$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));

if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents/auth/groups/ffGrid_div.html")) {
    $oGrid->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . $cm->oPage->theme . "/contents/auth/groups";
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/contents/auth/groups/ffGrid_div.html")) {
    $oGrid->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/contents/auth/groups";
}*/
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Groups";
$oGrid->title = ffTemplate::_get_word_by_code("groups");
$oGrid->source_SQL = "SELECT 
							" . CM_TABLE_PREFIX . "mod_security_groups.*
							, (SELECT COUNT(*) FROM " . CM_TABLE_PREFIX . "mod_security_users
								INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
								WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
							) AS count_user
						FROM 
							" . CM_TABLE_PREFIX . "mod_security_groups
                        [WHERE]
                        [HAVING]
						[ORDER] ";

$oGrid->order_default = "name";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "GroupModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = AREA_GROUPS_SHOW_MODIFY;
$oGrid->display_delete_bt = AREA_GROUPS_SHOW_MODIFY;
$oGrid->display_new = AREA_GROUPS_SHOW_MODIFY;

// Ricerca

// Chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "gid";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Visualizzazione
$oField = ffField::factory($cm->oPage);
$oField->id = "gid";
$oField->container_class = "gid";
$oField->label = ffTemplate::_get_word_by_code("groups_gid");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("groups_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "registration";
$oField->container_class = "registration";
$oField->label = ffTemplate::_get_word_by_code("groups_registration");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("enabled"))),
                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("disabled")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("disabled");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "level";
$oField->container_class = "level";
$oField->label = ffTemplate::_get_word_by_code("groups_level");
$oField->base_type = "Number";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "count_user";
$oField->container_class = "count_user";
$oField->label = ffTemplate::_get_word_by_code("groups_count_user");
$oField->base_type = "Number";
$oGrid->addContent($oField);

if(AREA_USERS_SHOW_MODIFY) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "members";
	$oButton->class = cm_getClassByFrameworkCss("user", "icon");
	$oButton->label = ffTemplate::_get_word_by_code("groups_members");
	//$oButton->label = "preview";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/members?gid=[gid_VALUE]&ret_url=" . urlencode($cm->oPage->getRequestUri());
	$oButton->aspect = "link";
	//$oButton->image = "permissions.png";
	$oButton->template_file = "ffButton_link_fixed.html";                           
	$oGrid->addGridButton($oButton);
}
if(AREA_SETTINGS_CUSTOM_SHOW_MODIFY) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "custom_settings";
	$oButton->class = "ico-custom-permissions";
	$oButton->label = ffTemplate::_get_word_by_code("groups_custom_settings");
	//$oButton->label = "preview";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/custom_settings?[KEYS]ret_url=" . urlencode($cm->oPage->getRequestUri());
	$oButton->aspect = "link";
	//$oButton->image = "permissions.png";
	$oButton->template_file = "ffButton_link_fixed.html";                           
	$oGrid->addGridButton($oButton);
}
if(AREA_SETTINGS_SHOW_MODIFY) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "permissions";
	$oButton->label = ffTemplate::_get_word_by_code("permissions");
	//$oButton->label = "preview";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . VG_SITE_PERMISSION . "/modify?gid=[gid_VALUE]&ret_url=" . urlencode($cm->oPage->getRequestUri());
	$oButton->aspect = "link";
	//$oButton->image = "permissions.png";
	$oButton->template_file = "ffButton_link_fixed.html";                           
	$oGrid->addGridButton($oButton);
}
$cm->oPage->addContent($oGrid);

?>