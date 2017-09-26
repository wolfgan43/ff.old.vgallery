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
			$goal_step = ceil($goal / ($equity * 10)); 
		} else {
			$capital_funded = 0;
			$goal_step = ceil($goal / 1000);
		}
	} else {
		if($goal + $capital_funded > 0) {
			$equity = round($goal * 100 / ($goal + $capital_funded), 2);
		} else {
			$equity = 0;
		}

		$goal_step = ceil($goal / 1000);
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
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_reward") . " - " . $idea_name;
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
//$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
$oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->fixed_pre_content = $tpl_detail_empty . "<br />";
$oRecord->user_vars["ID_idea"] = $ID_idea;

//$oRecord->fixed_post_content = $tpl_menu;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY"))
{
	$cm->oPage->tplAddJs("equity_complex"
		, array(
			"file" => "equity_complex.js"
			, "path" => "/modules/crowdfund/themes/javascript"
	));

	$oRecord->addTab("equity");
	$oRecord->setTabTitle("equity", ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity_group"));
	$oRecord->addContent(null, true, "equity"); 
	$oRecord->groups["equity"] = array(
									"title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity_group")
									, "cols" => 1
									, "tab" => "equity"
									, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity_group_description")
								 );
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_equity";
	$oField->container_class = "enable-pledge";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_enable_equity");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
	$oRecord->addContent($oField, "equity");

	if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY_COMPLEX"))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "capital_funded";
		$oField->container_class = "capital-funded";
		$oField->base_type = "Number";
		if($is_startup) {
			$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_capital_estimate");
			$oField->control_type = "label";
			$oField->app_type = "Currency";
			$oField->data_type = "";
			$oField->default_value = new ffData($capital_funded, "Number");
		} else {
			$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_capital_funded");
			$oField->widget = "slider";
			$oField->min_val = 0; 
			$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
			$oField->step = global_settings("MOD_CROWDFUND_GOAL_STEP");
			//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CAPITAL_FUNDED");
		}
		$oField->fixed_post_content = '<span class="symbol">' . $currency_symbol . '</span>' . mod_crowdfund_process_help_hint($oField->id);
		$oRecord->addContent($oField, "equity");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "goal";
		$oField->container_class = "goal";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal_complex");
		$oField->base_type = "Number";
		$oField->app_type = "Currency";
		$oField->fixed_pre_content = '<span class="operator"> + </span>';
		$oField->fixed_post_content = '<span class="symbol">' . $currency_symbol . '</span>' . mod_crowdfund_process_help_hint($oField->id);
		$oField->control_type = "label";
		$oRecord->addContent($oField, "equity");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "total_capital";
		$oField->container_class = "total-capital"; 
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_total_capital");
		$oField->data_type = "";
		$oField->store_in_db = false;
		$oField->base_type = "Number";
		$oField->app_type = "Currency";
		$oField->control_type = "label";
		$oField->default_value = new ffData($total_capital, "Number");
		$oField->fixed_pre_content = '<span class="operator"> = </span>';
		$oField->fixed_post_content = '<span class="symbol">' . $currency_symbol . '</span>' . mod_crowdfund_process_help_hint($oField->id);
		$oRecord->addContent($oField, "equity");


		$oField = ffField::factory($cm->oPage);
		$oField->id = "equity";
		$oField->container_class = "equity";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity");
		$oField->base_type = "Number";

		if($is_startup) {
			$oField->required = true;
			$oField->widget = "slider";
			$oField->fixed_post_content = '	<span class="symbol">' . "%" . '</span>
											<span class="modify-total">
												<label>' . ffTemplate::_get_word_by_code("crowdfund_idea_modify_total_variable") . '</label>
													<input type="checkbox" class="increase-capital" value="' . $total_capital . '" />
												</span>' 
											. mod_crowdfund_process_help_hint($oField->id);
			$oField->min_val = 1;
			$oField->max_val = 100; 
			$oField->step = 1;
		} else {
			$oField->app_type = "Currency";
			$oField->data_type = "";
			$oField->default_value = new ffData($equity, "Number");
			$oField->control_type = "label";
			$oField->fixed_post_content = '<span class="symbol">%</span>' . mod_crowdfund_process_help_hint($oField->id); 
		}
		$oRecord->addContent($oField, "equity");

		if($goal > 0 && $goal_step > 0) {
			$goal_step_normalized = round((round($goal_step_value * 100 / $goal, 2) * $goal / 100) / $goal_step, 0) * $goal_step;
			$min_equity = ($goal_step_normalized * 100 / $goal);
			$goal_step_equity = $min_equity * $equity / 100;
			if($goal_step_normalized > 0) {
				$max_share = round($goal / $goal_step_normalized, 2);
			} else {
				$max_share = 0;
			}
		} else {
			$goal_step_normalized = 0;
			$min_equity = 0;
			$goal_step_equity = 0;
			$max_share = 0;
		}

		$min_equity = new ffData($min_equity, "Number");
		$goal_step_equity = new ffData($goal_step_equity, "Number");

		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "goal_step";
		$oField->container_class = "goal_step";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal_step");
		$oField->base_type = "Number";
		//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_GOAL_STEP");
		$oField->widget = "slider";
		$oField->fixed_post_content = '<span class="symbol">' . $currency_symbol . '</span>
					<div class="additional-stat">
					<div class="min-equity">' . ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity_crowdfund") . '<span>' . $min_equity->getValue("Currency", FF_LOCALE) . '%</span></div>
					<div class="equity-total">' . ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity_total") . '<span>' . $goal_step_equity->getValue("Currency", FF_LOCALE) . '%</span></div>
					<div class="max-share">' . ffTemplate::_get_word_by_code("crowdfund_idea_modify_max_share") .  '<span>' . round($max_share, 2) . '</span></div> 
					</div>'
					. mod_crowdfund_process_help_hint($oField->id);
		$oField->min_val = 0;
		$oField->max_val = $goal; //global_settings("MOD_CROWDFUND_MAX_GOAL") / 10;
		$oField->step = $goal_step;
		//$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
		//$oField->properties["readonly"] = "readonly";
		$oField->data_type = "";
		$oField->default_value = new ffData($goal_step_normalized, "Number");
		$oRecord->addContent($oField, "equity");
	} else {
		$cm->oPage->tplAddJs("equity_simple"
			, array(
				"file" => "equity_simple.js"
				, "path" => "/modules/crowdfund/themes/javascript"
		));

		$oField = ffField::factory($cm->oPage);
		$oField->id = "goal";
		$oField->container_class = "goal";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal_simple");
		$oField->base_type = "Number";
		$oField->app_type = "Currency";
		$oField->fixed_post_content =  '<span class="symbol label">' . $currency_symbol . '</span>' . mod_crowdfund_process_help_hint($oField->id);
		$oField->control_type = "label";
		$oRecord->addContent($oField, "equity");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "equity";
		$oField->container_class = "equity";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity");
		$oField->base_type = "Number";
		$oField->required = true;
		$oField->widget = "slider";
		$oField->fixed_post_content = '<span class="label">' . "%" . '</span>' . mod_crowdfund_process_help_hint($oField->id);
		$oField->min_val = 1;
		$oField->max_val = 100; 
		$oField->step = 1;
		$oRecord->addContent($oField, "equity");
		
		if($goal > 0 && $goal_step > 0) {
			$goal_step_normalized = round((round($goal_step_value * 100 / $goal, 2) * $goal / 100) / $goal_step, 0) * $goal_step;
			$min_equity = ($goal_step_normalized * 100 / $goal);
			$goal_step_equity = $min_equity * $equity / 100;
			if($goal_step_normalized > 0) {
				$max_share = round($goal / $goal_step_normalized, 2);
			} else {
				$max_share = 0;
			}
		} else {
			$goal_step_normalized = 0;
			$min_equity = 0;
			$goal_step_equity = 0;
			$max_share = 0;
		}

		$min_equity = new ffData($min_equity, "Number");
		$goal_step_equity = new ffData($goal_step_equity, "Number");


		$oField = ffField::factory($cm->oPage);
		$oField->id = "goal_step";
		$oField->container_class = "goal_step";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal_step_simple");
		$oField->base_type = "Number";
		//$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_GOAL_STEP");
		$oField->widget = "slider";
		$oField->fixed_post_content = '<span class="label">,00 '  . $currency_symbol . '</span>
					<div class="additional-stat">
					<div class="min-equity">' . ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity_crowdfund_simple") . '<span>' . $min_equity->getValue("Currency", FF_LOCALE) . '%</span></div>
					<div class="max-share">' . ffTemplate::_get_word_by_code("crowdfund_idea_modify_max_share") .  '<span>' . round($max_share, 2) . '</span></div> 
					</div>'
					. mod_crowdfund_process_help_hint($oField->id);
		$oField->min_val = 0;
		$oField->max_val = $goal; //global_settings("MOD_CROWDFUND_MAX_GOAL") / 10;
		$oField->step = $goal_step;
		//$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
		//$oField->properties["readonly"] = "readonly";
		$oField->data_type = "";
		$oField->default_value = new ffData($goal_step_normalized, "Number");
		$oRecord->addContent($oField, "equity");

	}
}

if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
{
	$oRecord->addTab("donation");
	$oRecord->setTabTitle("donation", ffTemplate::_get_word_by_code("crowdfund_idea_modify_donation_group"));
	$oRecord->addContent(null, true, "donation"); 
	$oRecord->groups["donation"] = array(
									"title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_donation_group")
									, "cols" => 1
									, "tab" => "donation"
									, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_donation_group_description")
								 );
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_donation";
	$oField->container_class = "enable-donation";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_enable_donation");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
	$oRecord->addContent($oField, "donation");
}

if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE"))
{
	$oRecord->addTab("pledge");
	$oRecord->setTabTitle("pledge", ffTemplate::_get_word_by_code("crowdfund_idea_modify_pledge_group"));
	$oRecord->addContent(null, true, "pledge"); 
	$oRecord->groups["pledge"] = array(
									"title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_pledge_group")
									, "cols" => 1
									, "tab" => "pledge"
									, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_pledge_group_description")
								 );
	$oField = ffField::factory($cm->oPage);
	$oField->id = "enable_pledge";
	$oField->container_class = "enable-pledge";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_enable_pledge");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number");
	$oField->unchecked_value = new ffData("0", "Number");
	$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id) . ffTemplate::_get_word_by_code("crowdfund_idea_pledge_limitation_value");
	$oRecord->addContent($oField, "pledge");


	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "Pledge";
	//$oGrid->title = ffTemplate::_get_word_by_code("crowdfund_pledge_title");
	$oGrid->source_SQL = "SELECT
								" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.*
								, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.`limit` > 0
									, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.`limit`
									, " . $db->toSql(ffTemplate::_get_word_by_code("crowdfund_pledge_unilimited")) . "
								) AS `limit`
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.title
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
								) AS title
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.description
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
								) AS description
							FROM
								" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($ID_idea, "Number"). "
							[AND] [WHERE] 
							[HAVING]
							[ORDER]";

	$oGrid->order_default = "price";
	$oGrid->use_search = !$simple_interface;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/" . $smart_url;
	$oGrid->record_id = "PledgeModify";
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
	$oField->id = "price";
	$oField->container_class = "price";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_price");
	$oField->fixed_pre_content = '<span class="symbol">' . $currency_symbol . " " . '</span>';
	$oField->base_type = "Number";
	$oField->app_type = "Currency";
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "limit";
	$oField->container_class = "limit";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_limit");
	//$oField->base_type = "Number";
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "title";
	$oField->container_class = "title";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_name");
	$oGrid->addContent($oField);*/

	$oField = ffField::factory($cm->oPage);
	$oField->id = "description";
	$oField->container_class = "description";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_description");
	$oGrid->addContent($oField);

	$oRecord->addContent($oGrid, "pledge");
        $cm->oPage->addContent($oGrid);
}


$cm->oPage->addContent($oRecord);

if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}


function IdeaModify_on_done_action($component, $action) 
{
	
	$db = ffDB_Sql::factory();

	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->tosql($component->user_vars["ID_idea"], "Number");
	$db->query($sSQL);
	if($db->nextRecord()) 
	{
		$goal_step_now = (int) $component->form_fields["goal_step"]->value->getValue("Number");
		$enable_equity = $component->form_fields["enable_equity"]->value->getValue("Number");
		
		
		if ($enable_equity)
		{
			
			if ($goal_step_now > global_settings("MOD_CROWDFUND_IDEA_GOAL_STEP_MIN"))
			{
				$goal_step = $goal_step_now;
			} else
			{
				$goal_step = global_settings("MOD_CROWDFUND_IDEA_GOAL_STEP_MIN");
			}
		} else 
		{
			$goal_step = global_settings("MOD_CROWDFUND_IDEA_GOAL_STEP_MIN");
		}
		//echo $goal_step . "ciao" . $enable_equity . "salve";
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
					" . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_step = " . $db->toSql($goal_step, "Number") . "
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.equity = " . $db->toSql($component->form_fields["equity"]->value, "Number") . "
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->tosql($component->user_vars["ID_idea"], "Number");
		$db->execute($sSQL);
	}
			
	
}
?>
