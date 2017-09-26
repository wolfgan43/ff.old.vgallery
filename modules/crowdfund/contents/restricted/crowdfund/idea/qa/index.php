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
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_current AS goal_current
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal AS goal
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_step AS goal_step
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.is_startup AS is_startup
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.equity AS equity
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.capital_funded AS capital_funded
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
	        , " . FF_PREFIX . "currency.name AS currency_name
			, " . FF_PREFIX . "currency.symbol AS currency_symbol
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.created	
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire	
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
			INNER JOIN " . FF_PREFIX . "currency ON " . FF_PREFIX . "currency.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_currency
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
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_qa") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
//$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
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
$oGrid->id = "QA";
//$oGrid->title = ffTemplate::_get_word_by_code("qa_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.*
                            , anagraph.avatar
                            , anagraph.email
					        , IF(anagraph.billreference = ''
                                , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
                                	, CONCAT(anagraph.name, ' ', anagraph.surname)
                                	, IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                )
                                , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                	, CONCAT(anagraph.name, ' ', anagraph.surname)
                                	, anagraph.billreference
                                )
				            ) AS anagraph
                        FROM
                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.ID_user_anagraph
							LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.ID_idea = " . $db->toSql($ID_idea, "Number"). "
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "created";
$oGrid->use_search = !$simple_interface;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/" . $smart_url;
$oGrid->record_id = "QAModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "qa_on_before_parse_row");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca
$oField = ffField::factory($cm->oPage);
$oField->id = "created";
$oField->container_class = "created";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_created");
$oField->base_type = "Timestamp";
$oField->display_label = false;
$oField->app_type = "Date";
$oField->order_dir = "DESC";
$oGrid->addContent($oField);

// Campi visualizzati
if(ENABLE_AVATAR_SYSTEM) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "avatar";
	$oField->display_label = false;
	$oField->container_class = "avatar";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_avatar");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
}
$oField = ffField::factory($cm->oPage);
$oField->id = "anagraph";
$oField->container_class = "anagraph";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_anagraph");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "question";
$oField->container_class = "question";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_question");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "answer";
$oField->container_class = "answer";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_answer");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "visible";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("crowdfund_idea_progress_active");
$oButton->template_file = "ffButton_link_fixed.html";                           
$oGrid->addGridButton($oButton);

$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);

$cm->oPage->addContent($oRecord);  

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}


function qa_on_before_parse_row($component) {
    if(isset($component->grid_fields["avatar"])) { 
    	if(check_function("get_user_avatar"))
    		$component->grid_fields["avatar"]->setValue(get_user_avatar($component->db[0]->getField("avatar", "Text", true), true, $component->db[0]->getField("email", "Text", true)));
	}
	
	if(isset($component->grid_buttons["visible"])) {
		if($component->db[0]->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
		    $component->grid_buttons["visible"]->action_type = "submit"; 
		    $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setactivated=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
		    if($_REQUEST["XHR_DIALOG_ID"]) {
			    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
		    } else {
			    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
		    }   
	    } else {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
		    $component->grid_buttons["visible"]->action_type = "submit";  
		    $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setactivated=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
		    if($_REQUEST["XHR_DIALOG_ID"]) {
			    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
		    } else {
			    $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setactivated', fields: [], 'url' : '[[frmAction_url]]'});";
		    }    
	    }
	    $component->grid_buttons["visible"]->display = true;
	}
}
?>
