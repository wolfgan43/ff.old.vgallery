<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}
$anagraph_params_company = "ct=" . global_settings("MOD_CROWDFUND_ANAGRAPH_TYPE_USER") . "&af=0&cg=0&cnf=0&fu=1&cef=1&ug=" . global_settings("MOD_CROWDFUND_GROUP_USER");

use_cache(false);

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$ID_idea = 0;

if($_REQUEST["keys"]["ID_idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID_idea"], "Number");
} elseif($_REQUEST["idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["idea"], "Number");
} else {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql(basename($cm->real_path_info), "Text");
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
	$title = $db->getField("name", "Text", true);
	
}
if(!$permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]) 
{
	if($db->getField("owner", "Number", true) != $UserNID) 
	{
		if(global_settings("MOD_CROWDFUND_ENABLE_TEAM")) 
		{
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
							OR FIND_IN_SET(" . $db->toSql(basename(ffCommon_dirname($cm->oPage->page_path))) . ", " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission))";
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				$allow_edit = true;
				if(strlen($db->getField("role_permission", "Text", true))) 
				{
					$arrLimit = explode(",", $db->getField("role_permission", "Text", true));
				}
			} else 
			{
				$allow_edit = false;
			}
		} else 
		{
			$allow_edit = false;
		}
	} else 
	{
		$allow_edit = true;
	}
} else 
{
	$allow_edit = true;
}			
if($allow_edit) {
	$_REQUEST["keys"]["ID_idea"] = $ID_idea; 
}
if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false) {
	$simple_interface = false;
} else {
	$simple_interface = true;
}

//menu
$tpl_menu = mod_crowdfund_get_menu_idea($smart_url, null, $arrLimit);

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "TeamModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_team_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_team";
$oRecord->addEvent("on_do_action", "TeamModify_on_do_action");
$oRecord->addEvent("on_done_action", "TeamModify_on_done_action");
$oRecord->user_vars["ID_idea"] = $ID_idea;
$oRecord->user_vars["title"] = $title;

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

if(!$cm->oPage->isXHR()) {
	//$oRecord->fixed_post_content = $tpl_menu;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if($_REQUEST["keys"]["ID_idea"] > 0) 
{
		$oRecord->insert_additional_fields["ID_idea"] =  new ffData($_REQUEST["keys"]["ID_idea"], "Number");
} else 
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_idea";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_timeline_modify_ID_idea");
	$oField->base_type = "Number";
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = 
												(SELECT " . FF_PREFIX . "languages.ID 
													FROM " . FF_PREFIX . "languages 
													WHERE " . FF_PREFIX . "languages.code = " . $db->toSql(FF_LOCALE) . ")
												) AS title
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql(get_session("UserNID"), "Number");
	$oField->required = true;
	$oRecord->addContent($oField);
}
		
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_user_anagraph";
$oField->container_class = "anagraph_user";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_team_modify_ID_user_anagraph");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT anagraph.ID
							, IF(anagraph.billreference = ''
                                , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
                                	, CONCAT(anagraph.name, ' ', anagraph.surname)
                                	, IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                )
                                , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                	, CONCAT(anagraph.name, ' ', anagraph.surname)
                                	, anagraph.billreference
                                )
				            ) AS Fname
							FROM anagraph
								INNER JOIN anagraph_type ON anagraph_type.ID = anagraph.ID_type
								INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
							WHERE 1
								AND anagraph.ID NOT IN (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph
															FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
															WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . $db->toSql($_REQUEST["keys"]["ID_idea"], "Number") . "
																AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID <> " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
														)
								AND anagraph_type.name = " . $db->toSql(global_settings("MOD_CROWDFUND_ANAGRAPH_TYPE_USER")) . "
								" . ($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]
									? "" 
									: " AND anagraph.owner = " . $db->toSql($UserNID, "Number")
								) . "
							GROUP BY anagraph.ID
							ORDER BY Fname";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_RESTRICTED  . "/anagraph/all/modify?" . $anagraph_params_company;
$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
$oField->resources[] = "AnagraphModify";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_role";
$oField->container_class = "ID-role";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_team_modify_ID_role");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.ID
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.name
										FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages
										WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_role = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.ID
											AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
								) AS name
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role
							WHERE 1";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

function TeamModify_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
	
	switch($action) {
		case "insert":
			break;
		case "update":
			break;
		case "confirmdelete":
			break;
		default:
	}
}
function TeamModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) 
	{
		$member_ID = $component->form_fields["ID_user_anagraph"]->getValue("Number");
		$role = $component->form_fields["ID_role"]->getDisplayValue();
		$idea_title = $component->user_vars["title"];
		$ID_idea = $component->user_vars["ID_idea"];
		
		switch($action) 
		{  
			case "insert":
			case "update":
				
				$sSQL = "SELECT IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
                                	, CONCAT(anagraph.name, ' ', anagraph.surname)
                                	, IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
								) AS name
							FROM anagraph
								INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
							WHERE anagraph.ID = " . $db->toSql($member_ID, "Number");
				$db->query($sSQL);
/*
				$sSQL = "SELECT anagraph.* 
							FROM anagraph
							WHERE anagraph.ID = " . $db->toSql($member_ID, "Number");
				$db->query($sSQL); 
 * 
 */
				if($db->nextRecord())  
				{
//					$name_member = $db->getField("name", "Text", true) . " " . $db->getField("surname", "Text", true);
					$name_member = $db->getField("name", "Text", true);
					$to[0]["name"] = $name_member;
					$to[0]["mail"] = $db->getField("email", "Text", true);

					$fields["idea"]["title"] = $idea_title;
					$fields["idea"]["member_name"] = $name_member;
					if(strlen($role))
					{
						$fields["idea"]["member_team_description"] = $role;
					}

					if(check_function("process_mail")) 
					{ 
						$rc = process_mail(email_system("team"), $to, NULL, NULL, $fields, null, null, null, false, null, false);
					}
				}
					break;
				case "confirmdelete":
					break;
				default: 
					break;
		}
    }
}
?>