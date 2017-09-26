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
	$languages = $db->getField("languages", "Text", true);	
	$smart_url = $db->getField("smart_url", "Text", true);
	$idea_name = $db->getField("name", "Text", true);
	$currency_name = $db->getField("currency_name", "Text", true);
	$currency_symbol = $db->getField("currency_symbol", "Text", true);
	$goal = $db->getField("goal", "Number", true);
	$goal_current = $db->getField("goal_current", "Number", true);
	$owner = $db->getField("owner", "Number", true);
	$min_offer = ceil ($goal/100);
	$max_offer = floor ($goal/10);
	$is_startup = $db->getField("is_startup", "Number", true);
	$enable_equity = $db->getField("enable_equity", "Number", true);
	$equity = $db->getField("equity", "Number", true);
	$capital_funded = $db->getField("capital_funded", "Number", true);
	$goal_step_value = $db->getField("goal_step", "Number", true);
	if($is_startup) {
		if($equity > 0) {
			$capital_funded = round(( (100 - $equity) * $goal / $equity ), 2);
			$goal_step = round($goal / ($equity * 10), 2); 
		} else {
			$capital_funded = 0;
			$goal_step = round($goal / 1000, 2);
		}
	} else {
		if($goal + $capital_funded > 0) {
			$equity = round($goal * 100 / ($goal + $capital_funded), 2);
		} else {
			$equity = 0;
		}

		$goal_step = round($goal / 1000, 2);
	}
	if($enable_equity)
	{
		if($goal_step_value < $goal_step) 
		{
			$goal_step_value = $goal_step;
		}
	} else
	{
		$goal_step = 0;
	}
	$total_capital = $goal + $capital_funded;

	$created = $db->getField("created", "Number", true);
	$activated = $db->getField("activated", "Number", true);
	$expiration = $db->getField("expire", "Number", true); 
	if($expiration)
	{
		$end_idea = $activated + $expiration * 86400;
	} else
	{
		$end_idea = $activated + (global_settings("MOD_CROWDFUND_EXPIRATION") - global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY")) * 86400;
	}
	

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
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_team") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
//$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["delete"]["display"] = false;

//$oRecord->fixed_post_content = $tpl_menu;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "Team";
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.*
							, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.name
										FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages
										WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_role = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.ID
											AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
							) AS role  
							, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID AS member_ID	
							, anagraph.avatar
					        , IF(anagraph.billreference = ''
                                , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
                                	, CONCAT(anagraph.name, ' ', anagraph.surname)
                                	, IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                )
                                , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                	, CONCAT(anagraph.name, ' ', anagraph.surname)
                                	, anagraph.billreference
                                )
				            ) AS name
                        FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph
							INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                            INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_role
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . $db->toSql($ID_idea, "Number"). "
						[AND] [WHERE] 
                        [HAVING]
						[ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = !$simple_interface; 
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/" . $smart_url;
$oGrid->record_id = "TeamModify";
$oGrid->resources[] = $oGrid->record_id; 
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "team_on_before_parse_row");

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

if(ENABLE_AVATAR_SYSTEM) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "avatar";
	$oField->display_label = false;
    $oField->label = ffTemplate::_get_word_by_code("crowdfund_team_member_avatar");
    $oField->encode_entities = false;
    $oGrid->addContent($oField, false);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_member_name");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "role";
$oField->container_class = "role";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_member_role_name");
$oGrid->addContent($oField);

$oRecord->addContent($oGrid);
$cm->oPage->addContent($oGrid);

$cm->oPage->addContent($oRecord);  

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}
function team_on_before_parse_row($component) {
	$db = ffDB_Sql::factory();
	
    if(isset($component->grid_fields["avatar"])) { 
    	if(check_function("get_user_avatar")) 
    		$component->grid_fields["avatar"]->setValue(get_user_avatar($component->db[0]->getField("avatar", "Text", true), true, $component->db[0]->getField("email", "Text", true)));
	}
}
?>
