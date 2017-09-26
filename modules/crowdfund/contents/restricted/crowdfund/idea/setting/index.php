<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$anagraph_params_company = "ct=" . global_settings("MOD_CROWDFUND_ANAGRAPH_TYPE_COMPANY");
$anagraph_params_user = "ct=" . global_settings("MOD_CROWDFUND_ANAGRAPH_TYPE_USER");
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
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_anagraph_company
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
	$ID_anagraph_company = $db->getField("ID_anagraph_company", "Number", true);
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
$tpl_detail_empty = mod_crowdfund_get_idea_required($ID_idea);

$cm->oPage->tplAddJs("idea_modify"
	, array(
		"file" => "user_idea_modify.js"
		, "path" => "/modules/crowdfund/themes/javascript"
));

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_setting") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
$oRecord->addEvent("on_do_action", "IdeaModify_on_do_action");
$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
$oRecord->user_vars["ID_idea"] = $ID_idea;
$oRecord->user_vars["owner"] = $owner;
$oRecord->user_vars["activation"] = $activated;
$oRecord->fixed_pre_content = $tpl_detail_empty . "<br />";
//$oRecord->fixed_post_content = $tpl_menu;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_INNOVATIVE")) 
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "is_innovative";
	$oField->container_class = "innovative";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_is_innovative");
	$oField->required = true;
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
								array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("yes"))),
								array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no")))
						   );
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_IS_INNOVATIVE");
	$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "innovative_text";
	$oField->container_class = "innovative-text";
	$oField->label =  ffTemplate::_get_word_by_code("crowdfund_idea_modify_innovative_certification");
	$oField->data_type = "";
	$oField->store_in_db = false;
	$oField->display_label = false;
	$oField->base_type = "Text";
	$oField->extended_type = "Text";
	$oField->control_type = "textarea";
	$oField->default_value = new ffData(ffTemplate::_get_word_by_code("crowdfund_idea_modify_innovative_certification_text"));
	$oField->properties["readonly"] = "readonly";
	$oRecord->addContent($oField);	

	$oField = ffField::factory($cm->oPage);
	$oField->id = "innovative_autocertification";
	$oField->container_class = "innovative-autocertification";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_innovative_autocertification");
	$oField->base_type = "Number";
	$oField->control_type = "radio";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
								array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
								array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
						   );
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "innovative_documentation";
	$oField->container_class = "innovative-documentation";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_innovative_documentation");
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
	//$oField->uploadify_model = "horizzontal";
	$oRecord->addContent($oField);
}


$oField = ffField::factory($cm->oPage);
$oField->id = "ID_currency";
$oField->container_class = "currency";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_ID_currency");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT 
		                            " . FF_PREFIX . "currency.ID 
		                            , " . FF_PREFIX . "currency.name
		                        FROM " . FF_PREFIX . "currency
		                        WHERE " . FF_PREFIX . "currency.status = '1'
		                        ORDER BY " . FF_PREFIX . "currency.name
		                        ";
if($goal_current > 0 ) {
	$oField->control_type = "label";
} else {
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CURRENCY");
}
$oRecord->addContent($oField);	

$oField = ffField::factory($cm->oPage);
$oField->id = "goal";
$oField->container_class = "goal";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal");
$oField->base_type = "Number";
$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_GOAL");
$oField->widget = "slider";
$oField->min_val = ($goal_current > 0 ? $goal_current : 1);
$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
$oField->step = global_settings("MOD_CROWDFUND_GOAL_STEP");
$oField->fixed_post_content = '<span class="symbol">' . $currency_symbol . '</span>' . mod_crowdfund_process_help_hint($oField->id);
$oRecord->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "languages";
$oField->container_class = "languages";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_languages");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT 
		                            " . FF_PREFIX . "languages.ID AS ID_languages
		                            , " . FF_PREFIX . "languages.description AS language 
		                        FROM " . FF_PREFIX . "languages
		                        WHERE " . FF_PREFIX . "languages.status = 1
		                        ORDER BY " . FF_PREFIX . "languages.description
		                        ";
		                         
$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_LANGUAGES");
$oField->widget = "checkgroup";
$oField->grouping_separator = ",";
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
$oRecord->addContent($oField);

if (global_settings("MOD_CROWDFUND_EXPIRATION_DECISION"))
{
	$backer = mod_crowdfund_get_backers($ID_idea, false);
	if(!(is_array($backer) && count($backer)))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "expire";
		$oField->container_class = "expire";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_expire");
		$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_EXPIRE");
		$oField->extended_type = "Selection";
		$oField->base_type = "Number";
		$max_expiration = (global_settings("MOD_CROWDFUND_EXPIRATION") + global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY"));
		if(global_settings("MOD_CROWDFUND_EXPIRATION") + global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY") > 365) {
			$max_expiration = 365;
		} else {
			$max_expiration = global_settings("MOD_CROWDFUND_EXPIRATION");
		}
		if($max_expiration >= 30)
			$arrExpiration["30"] = array(new ffData("30", "Number"), new ffData("1 " . ffTemplate::_get_word_by_code("mounth")));
		if($max_expiration >= 60)
			$arrExpiration["60"] = array(new ffData("60", "Number"), new ffData("2 " . ffTemplate::_get_word_by_code("mounths")));
		if($max_expiration >= 90)
			$arrExpiration["90"] = array(new ffData("90", "Number"), new ffData("3 " . ffTemplate::_get_word_by_code("mounths")));
		if($max_expiration >= 180)
			$arrExpiration["180"] = array(new ffData("180", "Number"), new ffData("6 " . ffTemplate::_get_word_by_code("mounths")));

		if(array_search($max_expiration, array_keys($arrExpiration)) === false) 
		{
			$arrExpiration[$max_expiration] = array(new ffData($max_expiration, "Number"), new ffData($max_expiration . " " . ffTemplate::_get_word_by_code("days")));
			ksort($arrExpiration);
		}
		$oField->multi_pairs = $arrExpiration;
		$oField->default_value = new ffData($max_expiration, "Number");

		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number") . " 
					AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire > 0";
		$db->query($sSQL);
		if($db->nextRecord()) 
		{ 
 			$oField->multi_select_one = false;
		}
		$oRecord->addContent($oField); 
	}
	
}
if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_SKYPE"))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "skype_account";
	$oField->container_class = "sky_account";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_skype_account");
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_SKYPE_ACCOUNT");
	$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
	$oRecord->addContent($oField);
} 

if(1)
{/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "status_visible_decision";
	$oField->container_class = "status_visible_decision";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_status_visible_decision");
	$oField->widget = "activecomboex";
	$oField->multi_pairs = array (
	                        array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no"))),
	                        array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("yes")))
		               );
	$oField->multi_select_one  = false;
	$oRecord->addContent($oField);*/
}

$oField = ffField::factory($cm->oPage);
$oField->id = "visible";
$oField->container_class = "visible";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_visible");
$oField->widget = "activecomboex";
$oField->multi_pairs = array (
	                        array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("crowdfund_registered_people"))),
	                        array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("crowdfund_all_people")))
		               );
$oField->required = true;
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
$oRecord->addContent($oField);

 
$oField = ffField::factory($cm->oPage);
$oField->id = "is_startup";
$oField->container_class = "startup";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_is_startup");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
		                    array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
		                    array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
		               );
if($goal_current > 0) {
	$oField->multi_select_one  = false;	
	$oField->control_type = "label";
} else {
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_IS_STARTUP");	
}
$oRecord->addContent($oField);


/*
if(!($is_startup && $goal_current > 0)) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "capital_funded";
	$oField->container_class = "capital-funded";
	if($is_startup) {
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_capital_estimate");
	} else {
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_capital_funded");
	}
	$oField->base_type = "Number";
	$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
	if($goal_current > 0) {
		$oField->control_type = "label";
		$oField->app_type = "Currency";
	} else {
		$oField->widget = "slider";
		$oField->min_val = 0;
		$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
		$oField->step = 1;
		$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CAPITAL_FUNDED");
	}
	$oField->fixed_post_content = '<span class="symbol">' . $currency_symbol . '</span>' . mod_crowdfund_process_help_hint($oField->id);
	$oRecord->addContent($oField);
}
*/
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anagraph_company";
$oField->container_class = "anagraph-company";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_ID_anagraph_company");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT
						anagraph.ID
                        , " . (check_function("get_user_data")
                            ? get_user_data("reference", "anagraph", null, false)
                            : "''"
                        ) . " AS Fname
					FROM anagraph
						INNER JOIN anagraph_type ON anagraph_type.ID = anagraph.ID_type
					WHERE 1
						AND anagraph_type.name = " . $db->toSql(global_settings("MOD_CROWDFUND_ANAGRAPH_TYPE_COMPANY")) . "
						" . ($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]
							? ""
							: " AND anagraph.owner = " . $db->toSql($UserNID, "Number")
						) . "
						" . ($ID_anagraph_company
							? " AND anagraph.ID = " . $db->toSql($ID_anagraph_company, "Number")
							: ""
						) . "
					GROUP BY anagraph.ID
					ORDER BY Fname";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("crowdfund_company_no_exist");
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/anagraph/all/modify?" . $anagraph_params_company;
$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
$oField->resources[] = "AnagraphModify";
$oField->actex_dialog_show_delete = false;
$oField->actex_dialog_show_add = false;
$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_ANAGRAPH_COMPANY");
$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
if($ID_anagraph_company)
	$oField->multi_select_one = false;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}


function IdeaModify_on_do_action($component, $action) 
{
    $db = ffDB_Sql::factory();
	$ID_idea = $component->user_vars["ID_idea"];
	$UserNID = get_session("UserNID");
	$owner = $component->user_vars["owner"];
	
	switch ($action) {
		case "insert": 
	    case "update":
			
		
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.* 
			  FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
			  WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea)  
			  ;//."	  AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_current > " . $db->toSql($component->form_fields["goal"]->value);
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$active = $db->getField("activated", "Number", true);
		    $goal_current = $db->getField("goal_current", "Number", true);
			$enable_equity = $db->getField("enable_equity", "Number", true);
		    $new_goal = $component->form_fields["goal"]->getValue("Number");
		    
		    if ($goal_current > $new_goal && $enable_equity)
		    {
		        $component->tplDisplayError(ffTemplate::_get_word_by_code("crowdfund_idea_new_goal_toolow"));
		        return true;
		    }
		}
		break;
	    default:
	}
}


function IdeaModify_on_done_action($component, $action) 
{
	/*
	$db = ffDB_Sql::factory();
	$ID_idea = $component->user_vars["ID_idea"];
	$UserNID = get_session("UserNID");
	$owner = $component->user_vars["owner"];
	$activation = $component->user_vars["activation"];
	 
	switch ($action) {
		case "insert":
		case "update":
			if(!$activation)
			{   
				if($component->form_fields["status_visible_decision"]->getValue() && !$component->form_fields["status_visible_decision"]->value_ori->getValue())
				{
					$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
								" . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated = " . $db->toSql(time(), "Number")."
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number");
					$db->execute($sSQL);
				}
			}
			break;
		default:
	}
	 */
}
?>
