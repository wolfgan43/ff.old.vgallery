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
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
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
	$languages = $db->getField("languages", "Text", true);	
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
$tpl_detail_empty = mod_crowdfund_get_idea_required($ID_idea);

$cm->oPage->tplAddJs("idea_modify"
	, array(
		"file" => "user_idea_modify.js"
		, "path" => "/modules/crowdfund/themes/javascript"
));
//$cm->oPage->tplAddJs("teaser", "basic.js", FF_SITE_PATH . "/modules/crowdfund/themes/javascript");
$cm->oPage->tplAddJs("basic"
	, array(
		"file" => "basic.js"
		, "path" => "/modules/crowdfund/themes/javascript"
));
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_basic") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->buttons_options["delete"]["display"] = false;

$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
$oRecord->fixed_pre_content = $tpl_detail_empty . "<br />";
//$oRecord->fixed_post_content = $tpl_menu;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "categories";
$oField->container_class = "categories";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_categories");
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CATEGORY");
$oField->extended_type = "Selection";
$oField->widget = "autocompletetoken";
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare_having = "name"; 
$oField->source_SQL = "SELECT 
							" . CM_TABLE_PREFIX . "mod_crowdfund_categories.ID
							, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.title
								FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_categories = " . CM_TABLE_PREFIX . "mod_crowdfund_categories.ID
									AND " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
							) AS name
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories.visible > 0
						[AND] [WHERE]
						[HAVING]
						[ORDER] [COLON] name
						[LIMIT]"; 

$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id); 
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cover";
$oField->container_class = "cover";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_cover");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/crowdfund/idea/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/crowdfund/idea";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
//$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
//$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->widget = "uploadify"; 
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_COVER");
//$oField->uploadify_model = "horizzontal";
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "video";
$oField->container_class = "video";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_video");
$oField->base_type = "Text";
$oField->extended_type = "Text";
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_VIDEO");
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "website";
$oField->container_class = "website";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_website");
$oField->base_type = "Text";
//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_WEBSITE");
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
$oRecord->addContent($oField);

$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang
		FROM " . FF_PREFIX . "languages 
		WHERE FIND_IN_SET(" . FF_PREFIX . "languages.ID, " . $db->toSql($languages) . ")";
$db->query($sSQL);
if($db->nextRecord()) {
	$count_lang = $db->getField("count_lang", "Number", true);
}

$oDetail = ffDetails::factory($cm->oPage);
if($count_lang > 1) {
    $oDetail->tab = true;
    $oDetail->tab_label = "language";
}
$oDetail->id = "IdeaDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("drafts_modify_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_idea" => "ID");
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "IdeaDetail_on_do_action");
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT 
                                    " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language 
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                FROM " . FF_PREFIX . "languages
                                WHERE
                                " . FF_PREFIX . "languages.status = '1'";
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID AS ID
                                    , " . FF_PREFIX . "languages.ID AS ID_languages
                                    , " . FF_PREFIX . "languages.description AS language
                                    , " . FF_PREFIX . "languages.code AS code_lang 
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url AS smart_url
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title AS title
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.teaser AS teaser
                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.description AS description
                                FROM " . FF_PREFIX . "languages
                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = [ID_FATHER]
                                WHERE
                                    " . FF_PREFIX . "languages.status = '1'
		                            AND FIND_IN_SET(" . FF_PREFIX . "languages.ID, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
	                                    											FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
	                                    											WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                                    										)
		                                )											
		                        ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "language";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_languages");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_ID_languages");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "code_lang";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_code");
$oField->store_in_db = false;
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_smart_url");
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->container_class = "title";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_title");
//$oField->required = true;
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "teaser";
$oField->container_class = "teaser";
$oField->extended_type = "Text";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_teaser");
//$oField->required = true;
//$oField->properties["maxlength"] = 200;
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->container_class = "description";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_description") . mod_crowdfund_process_help_hint($oField->id);
$oField->control_type = "textarea";
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
    $oField->widget = "ckeditor";
} else {
    $oField->widget = "";
}
$oField->ckeditor_group_by_auth = true;
$oField->extended_type = "Text";
$oField->base_type = "Text";
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);

$cm->oPage->addContent($oRecord);

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}






function IdeaDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
	//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
	
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        //echo $component->main_record[0]->key_fields["ID"]->value;
        foreach($component->recordset AS $rst_key => $rst_value) {
			$smart_url = ffCommon_url_rewrite($component->recordset[$rst_key]["title"]->getValue());
			$ID_languages = $component->recordset[$rst_key]["ID_languages"]->getValue();

			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url
       			 FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
       			 WHERE 1 " . (isset($ID_node) && $ID_node>0
							? (" AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea !=" . $db->tosql($ID_node, "Number") . "
								AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url = " . $db->toSql($smart_url)
								)
							:"");
			
			$db->query($sSQL);
			if($db->nextRecord()) {
				$component->displayError(ffTemplate::_get_word_by_code("crowdfund_idea_smart_url_not_unic") . "(" . $component->recordset[$rst_key]["language"]->getValue() . ")");
				return true;
			}

			$component->recordset[$rst_key]["smart_url"]->setValue($smart_url);
			
			if($component->recordset[$rst_key]["code_lang"]->getValue() == LANGUAGE_DEFAULT) {
				$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
							" . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql($component->recordset[$rst_key]["smart_url"]->getValue()) . " 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_node);
				$db->execute($sSQL);
			}

        }
    }
}  

function IdeaModify_on_done_action($component, $action) {

	
}
?>
