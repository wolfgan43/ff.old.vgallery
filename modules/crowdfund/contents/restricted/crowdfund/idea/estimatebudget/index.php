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
$tpl_detail_empty = mod_crowdfund_get_idea_required($ID_idea);
 
$cm->oPage->tplAddJs("idea_modify"
	, array(
		"file" => "user_idea_modify.js"
		, "path" => "/modules/crowdfund/themes/javascript"
));

if($UserNID == 6)
	$cm->oPage->tplAddJs("estimate_budget"
		, array(
			"file" => "estimate_budget.js"
			, "path" => "/modules/crowdfund/themes/javascript"
	));

$current_year = date("Y", time());

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_estimatebudget") . " - " . $idea_name;
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


//Income Statement
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "IncomeStatementDetail";
$oDetail->title = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_idea" => "ID");
$oDetail->tab = true;
$oDetail->tab_label = "year";
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "IncomeStatementDetail_on_do_action");
$oDetail->addEvent("on_before_parse_row", "IncomeStatementDetail_on_before_parse_row");
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT (" . $db->toSql($current_year - 1, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 1, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 2, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 3, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 4, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 5, "Number") . ") AS `year`";
								
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.*
                                    , tbl_year.`year` AS `year`
									, @first_margin := (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.total_revenue - " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.cost_good_service) AS first_margin
									, @EBITDA := (@first_margin - " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.human_resource - " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.marketing_cost - " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.other_cost) AS EBITDA
									, @EBIT := (@EBITDA - " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.depreciation_amortization) AS EBIT
									, (@EBIT - " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.finantial_interest) AS pretax_profit
								FROM (
										SELECT (" . $db->toSql($current_year - 1, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 1, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 2, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 3, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 4, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 5, "Number") . ") AS `year` 
                                	) AS tbl_year
                                	LEFT JOIN  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement ON tbl_year.`year` = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.`year`
                                		AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.ID_idea = [ID_FATHER]
                                WHERE 1
			                        ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "year";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_year");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "total_revenue";
$oField->container_class = "total-revenue calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_total_revenue");
$oField->base_type = "Number";

$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cost_good_service";
$oField->container_class = "cost-good-service ce-a-b calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_cost_good_service");
$oField->base_type = "Number";

$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "first_margin";
$oField->container_class = "first-margin disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_gross_margin");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "human_resource";
$oField->container_class = "human-resource ce-f calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_human_resource");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "marketing_cost";
$oField->container_class = "marketing-cost ce-f calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_marketing_cost");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "other_cost";
$oField->container_class = "other-cost ce-f calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_other_cost");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "EBITDA";
$oField->container_class = "EBITDA disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_EBITDA");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "depreciation_amortization";
$oField->container_class = "depreciation-amortization ce-h calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_depreciation_amortization");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "EBIT";
$oField->container_class = "EBIT disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_EBIT");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "finantial_interest";
$oField->container_class = "finantial-interest ce-l calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_finantial_interest");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "pretax_profit";
$oField->container_class = "pretax-profit disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_pre_tax_profit");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "net_income";
$oField->container_class = "net-income calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_net_income");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);


$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);


//Cash Flow
$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "CashFlowDetail";
$oDetail->title = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_idea" => "ID");
$oDetail->tab = true;
$oDetail->tab_label = "year";
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "CashFlowDetail_on_do_action");
$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_SQL = "SELECT (" . $db->toSql($current_year - 1, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 1, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 2, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 3, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 4, "Number") . ") AS `year` 
								UNION
								SELECT (" . $db->toSql($current_year + 5, "Number") . ") AS `year`";
								
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_SQL = "SELECT 
                                    " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.*
                                    , tbl_year.`year` AS `year`
									, @total_revenue := (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.net_income) AS total_revenue
									, @cashflow_operations := (@total_revenue + " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.cashflow_depreciation_amortization) AS cashflow_operations
									, @cashflow_investing := (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.cashflow_capital_expenditure - " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.cashflow_acquisitions) AS cashflow_investing
									, @cashflow_financing := (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.cashflow_dividends_paid + " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.cashflow_share_issue) AS cashflow_financing
									, (@cashflow_operations + @cashflow_investing + @cashflow_financing) AS total_cashflow
                                FROM (
										SELECT (" . $db->toSql($current_year - 1, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 1, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 2, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 3, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 4, "Number") . ") AS `year` 
										UNION
										SELECT (" . $db->toSql($current_year + 5, "Number") . ") AS `year` 
                                	) AS tbl_year
                                	LEFT JOIN  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow ON tbl_year.`year` = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.`year`
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.ID_idea = [ID_FATHER]
									LEFT JOIN  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement ON tbl_year.`year` = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.`year`
                                		AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.ID_idea = [ID_FATHER]
									
                                WHERE 1
			                        ";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "year";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_year");
$oField->base_type = "Number";
$oDetail->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "total_revenue";
$oField->container_class = "total_revenue disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_net_income");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_depreciation_amortization";
$oField->container_class = "cashflow-depreciation-amortization sp-c calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_depreciation_amortization");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_operations";
$oField->container_class = "cashflow-operations disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_from_operation");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_capital_expenditure";
$oField->container_class = "cashflow-capital-expenditure calc capital";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_capital_expenditure");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_acquisitions";
$oField->container_class = "cashflow-acquisitions sp-f calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_acquisitions");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_investing";
$oField->container_class = "cashflow-investing disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_from_investing");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_dividends_paid";
$oField->container_class = "cashflow-dividends-paid sp-i calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_dividends_paid");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_share_issue";
$oField->container_class = "cashflow-share-issue sp-i calc";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_share_issue");
$oField->base_type = "Number";
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cashflow_financing";
$oField->container_class = "cashflow-financing disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_from_financing");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "total_cashflow";
$oField->container_class = "total-cashflow disabled";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_total_cashflow");
$oField->base_type = "Number";
$oField->properties["readonly"] = "readonly";
$oField->store_in_db = false;
$oField->fixed_post_content = "<span class='cent'>,00</span>" . mod_crowdfund_process_help_hint($oField->id);
$oDetail->addContent($oField);


$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);


$cm->oPage->addContent($oRecord);


if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}

function IncomeStatementDetail_on_before_parse_row($component, $rst_val, $ciao) {
    $db = ffDB_Sql::factory();
	//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
	$first_margin = $rst_val["total_revenue"]->getValue() - $rst_val["cost_good_service"]->getValue();
	
	//$rst_val["first-margin"]->setValue("ciao");
}  

function IdeaModify_on_done_action($component, $action) {
	
}

function IncomeStatementDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
        }
    }
}  

function IncomeStatementDetail_on_done_action($component, $action) {

	
}

function CashFlowDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
        foreach($component->recordset AS $rst_key => $rst_value) {
        }
    }
}  

function CashFlowDetail_on_done_action($component, $action) {

	
}
?>
