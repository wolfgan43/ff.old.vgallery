<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

use_cache(false);

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$ID_idea = 0;

if($_REQUEST["keys"]["ID"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
} elseif($_REQUEST["idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["idea"], "Number");
} else {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql(basename($cm->real_path_info), "Text");
}

if(!strlen($_REQUEST["ret_url"])) {
	$_REQUEST["ret_url"] = ffCommon_dirname($cm->oPage->page_path);
} 

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID 
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url
			, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
	            FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
	            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
	                AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
	        ) AS name
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
		WHERE 1 
			AND " . $sSQL_Where;
$db->query($sSQL);
if($db->nextRecord()) {
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true);
	$smart_url = $db->getField("smart_url", "Text", true);
	$idea_name = $db->getField("name", "Text", true);
	
	if(!$permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]) {
		if($db->getField("owner", "Number", true) != $UserNID) {
			if(global_settings("MOD_CROWDFUND_ENABLE_TEAM")) {
				$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.*
								, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission AS role_permission
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
							INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_role
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . $db->toSql($ID_idea, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph = (SELECT anagraph.ID 
																									FROM anagraph 
																									WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . " 
																									ORDER BY anagraph.ID 
																									LIMIT 1)
							AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission = ''
								OR FIND_IN_SET(" . $db->toSql(basename($cm->oPage->page_path)) . ", " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission))";
				$db->query($sSQL);
				if($db->nextRecord()) {
					$allow_edit = true;
					if(strlen($db->getField("role_permission", "Text", true))) {
						$arrLimit = explode(",", $db->getField("role_permission", "Text", true));
					}
				} else {
					$allow_edit = false;
				}
			} else {
				$allow_edit = false;
			}
		} else {
			$allow_edit = true;
		}
	} else {
		$allow_edit = true;
	}
			
	if($allow_edit) {
		$_REQUEST["keys"]["ID"] = $ID_idea;
	}
}

if(!$_REQUEST["keys"]["ID"] > 0) {
	if($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]) {
		ffRedirect(FF_SITE_PATH . ffcommon_dirname($cm->oPage->page_path) . "/modify?ret_url=" . urlencode(FF_SITE_PATH . ffcommon_dirname($cm->oPage->page_path)));
	} else {
	    ffRedirect(FF_SITE_PATH . MOD_CROWDFUND_USER_PATH);
	}
}

if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false) {
	$simple_interface = false;
} else {
	$simple_interface = true;
	if(!$_REQUEST["keys"]["ID"] > 0) {
		ffRedirect(FF_SITE_PATH . MOD_CROWDFUND_USER_PATH);
	}
}

//set Header
if(check_function("set_header_page"))
	set_header_page($idea_name);

//menu
$tpl_menu = mod_crowdfund_get_menu_idea($smart_url, null, $arrLimit);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_timeline") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
//$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
//$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["delete"]["display"] = false;


$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

//$oRecord->fixed_post_content = $tpl_menu;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Timeline";
//$oGrid->title = ffTemplate::_get_word_by_code("backers_title");
$oGrid->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.*
						, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.title
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_timeline = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
								) AS title
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.description
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_timeline = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
								) AS description
                        FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_idea = " . $db->toSql($ID_idea, "Number") . "
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";
$oGrid->order_default = "date";
$oGrid->use_search = !$simple_interface; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/" . $smart_url;
$oGrid->record_id = "TimelineModify";
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

$oField = ffField::factory($cm->oPage);
$oField->id = "file";
$oField->display_label = false;
$oField->container_class = "file";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_file");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/crowdfund/idea/" . $ID_idea . "/timeline/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/crowdfund/idea/" . $ID_idea . "/timeline";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
$oField->control_type = "picture_no_link";
//$oField->fixed_pre_content = "http://" . DOMAIN_INSET . FF_SITE_PATH . "/crowdfund/idea/" . $ID_idea . "/timeline/[ID_VALUE]";
$oGrid->addContent($oField, false);


$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->container_class = "title";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->container_class = "description";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_description");  
$oGrid->addContent($oField);
/*
// Campi di ricerca
$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->container_class = "last_update";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_last_update");
$oField->base_type = "Timestamp";
$oField->app_type = "Date";
$oField->order_dir = "DESC";
$oGrid->addContent($oField);
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "date";
$oField->container_class = "date";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_date");
$oField->base_type = "Timestamp";
$oField->app_type = "Date";
$oField->order_dir = "DESC"; 
$oGrid->addContent($oField);

$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);

$cm->oPage->addContent($oRecord);  

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

?>
