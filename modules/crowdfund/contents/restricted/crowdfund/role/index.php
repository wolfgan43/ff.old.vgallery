<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission) && $permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Role";
$oGrid->title = ffTemplate::_get_word_by_code("crowdfund_role_title");
$oGrid->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.*
						, IFNULL(
									(	
										SELECT " . FF_PREFIX . "international.description
                            			FROM " . FF_PREFIX . "international
                            			WHERE " . FF_PREFIX . "international.word_code = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.name
											AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
									)
									, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.name
										FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages
										WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_role = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.ID
											AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
								)) AS name
                        FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role
                        WHERE 1
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "RoleModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_role_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "permission";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_role_permission");
$oGrid->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_help_languages");
$oField->source_SQL = "SELECT " . FF_PREFIX . "languages.ID
						, " . FF_PREFIX . "languages.description 
						FROM " . FF_PREFIX . "languages
						WHERE " . FF_PREFIX . "languages.status > 0  
						ORDER BY " . FF_PREFIX . "languages.description";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oGrid->addContent($oField);
*/


$cm->oPage->addContent($oGrid);
?>