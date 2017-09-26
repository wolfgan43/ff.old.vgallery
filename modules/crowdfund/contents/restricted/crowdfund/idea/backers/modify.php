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
$actual_owner = false;
$simple_interface = true;

$arrType["menu"] = array("invest" => array("limit_mpay" => global_settings("MOD_CROWDFUND_IDEA_EQUITY_LIMIT_PAYMENT"))
						, "reward" => array("limit_mpay" => global_settings("MOD_CROWDFUND_IDEA_PLEDGE_LIMIT_PAYMENT"))
						,"donate" => array("limit_mpay" => global_settings("MOD_CROWDFUND_IDEA_DONATION_LIMIT_PAYMENT"))
					);
$arrType["selected"] = array(
	"name" => ""
	, "label" => "modify"
	, "url" => ""
	, "limit_mpay" => ""
);

if(is_array($arrType["menu"]) && count($arrType["menu"])) {
    foreach($arrType["menu"] AS $arrType_key => $arrType_value) {
        $arrType["menu"][$arrType_key]["label"] = $arrType_key;
        $arrType["menu"][$arrType_key]["name"] = $arrType_key;

        if((string) $cm->router->getRuleById("crowdfund_" . $arrType_key . "_" . strtolower(FF_LOCALE))->reverse) {
                $arrType["menu"][$arrType_key]["url"] = (string) $cm->router->getRuleById("crowdfund_" . $arrType_key . "_" . strtolower(FF_LOCALE))->reverse;
        } else {
                $arrType["menu"][$arrType_key]["url"] = (string) $cm->router->getRuleById("crowdfund_" . $arrType_key)->reverse;
        }
		
        if(!strlen($arrType["selected"]["url"])) {
                if(!strlen($arrType["selected"]["url"]) && strlen((string) $cm->router->getRuleById("crowdfund_" . $arrType_key . "_" . strtolower(FF_LOCALE))->reverse) && strpos($cm->path_info, (string) $cm->router->getRuleById("crowdfund_" . $arrType_key . "_" . strtolower(FF_LOCALE))->reverse) === 0)
                        $arrType["selected"]["url"] = (string) $cm->router->getRuleById("crowdfund_" . $arrType_key . "_" . strtolower(FF_LOCALE))->reverse;
                if(!strlen($arrType["selected"]["url"]) && strlen((string) $cm->router->getRuleById("crowdfund_" . $arrType_key)->reverse) && strpos($cm->path_info, (string) $cm->router->getRuleById("crowdfund_" . $arrType_key)->reverse) === 0)
                        $arrType["selected"]["url"] = (string) $cm->router->getRuleById("crowdfund_" . $arrType_key)->reverse;

                if(strlen($arrType["selected"]["url"])) {
                        $arrType["selected"]["label"] = $arrType_key;
                        $arrType["selected"]["name"] = $arrType_key;
                        $arrType["selected"]["limit_mpay"] = $arrType_value["limit_mpay"]; 
                }
        }
    }
}

if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false && !isset($_REQUEST["force"])) {
	$actual_owner = null;

	if(!strlen($arrType["selected"]["url"]))
		$simple_interface = false;

	if($actual_owner === null) {
		$sSQL = "SELECT anagraph.* FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number");
		$db->query($sSQL);
		if($db->nextRecord()) {
			$_REQUEST["keys"]["ID_user_anagraph"] = $db->getField("ID", "Number", true);
		}
	}
} else {
	$actual_owner = $UserNID;

	$simple_interface = false;
	$_REQUEST["force"] = $arrType["selected"]["label"];
}



if($_REQUEST["keys"]["ID_idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID_idea"], "Number");
} elseif($_REQUEST["idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["idea"], "Number");
} else {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql(basename($cm->real_path_info), "Text");
}

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID 
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff  AS goal_diff
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_step AS goal_step
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.enable_equity
			, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.enable_pledge
				, (SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID) 
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
				)
				, 0
			) AS enable_reward
			, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.enable_donation
			, " . FF_PREFIX . "currency.name AS currency_name
			, " . FF_PREFIX . "currency.symbol AS currency_symbol
            , (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
                FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
                WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
                    AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
            ) AS name
			, (". ($_REQUEST["keys"]["ID"] > 0 
				? "
					SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.price
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID"
				: "0"
			) . ") AS actual_price
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
			INNER JOIN " . FF_PREFIX . "currency ON " . FF_PREFIX . "currency.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_currency
		WHERE 1 
			AND " . $sSQL_Where;

$db->query($sSQL);
if($db->nextRecord()) 
{
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true);     
	
	$languages = $db->getField("languages", "Text", true);	
	$smart_url = $db->getField("smart_url", "Text", true);	
	$idea_name = $db->getField("name", "Text", true);	
	
	$goal_diff = $db->getField("goal_diff", "Number", true);
	$goal_step = $db->getField("goal_step", "Number", true);
	$currency_name = $db->getField("currency_name", "Text", true);
	$currency_symbol = $db->getField("currency_symbol", "Text", true);
	
	$arrType["menu"]["invest"]["status"] = $db->getField("enable_equity", "Number", true);
        if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE"))
            $arrType["menu"]["reward"]["status"] = $db->getField("enable_reward", "Number", true);
        if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
	$arrType["menu"]["donate"]["status"] = $db->getField("enable_donation", "Number", true);
	
	$arrType["selected"]["status"] = $arrType["menu"][$arrType["selected"]["name"]]["status"];
	
	$actual_price = $db->getField("actual_price", "Number", true);

	
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
																									LIMIT 1)							AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role.permission = ''
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
if($allow_edit || $UserNID == 1 || $UserNID == 4 || $UserNID == 6) {
	$_REQUEST["keys"]["ID_idea"] = $ID_idea; 
}


if($_REQUEST["keys"]["ID_idea"] > 0) {
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.*
			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
			WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . 
				" AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($_REQUEST["keys"]["ID_idea"], "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {	
		$ID_reward = $db->getField("ID_reward", "Number", true);
		$ID_user_anagraph = $db->getField("ID_user_anagraph", "Number", true);
		if($ID_user_anagraph > 0) {
			$_REQUEST["keys"]["ID_user_anagraph"] = $ID_user_anagraph;
		}
		
		if(!strlen($arrType["selected"]["url"]) && strlen($db->getField("type", "Text", true))) {
			$arrType["selected"] = $arrType["menu"][$db->getField("type", "Text", true)];
		}
	}
}

if($actual_owner !== null) {
	//menu
	$tpl_menu = mod_crowdfund_get_menu_idea($smart_url, null, $arrLimit);
} else {
	if(!$arrType["selected"]["status"]) {
		foreach($arrType["menu"] AS $menu_value) {
			if($menu_value["status"]) {
				ffRedirect(FF_SITE_PATH . $menu_value["url"] . $cm->real_path_info);
			}
		}
	}
}

if(isset($_REQUEST["success"])) 
{ 
	if(is_file(FF_DISK_PATH . "/themes/site/css/dialog_confirm.css"))
		$cm->oPage->tplAddCss("dialog-confirm-css"
            , array(
                "file" => "dialog_confirm.css"
                , "path" => "/themes/site/css"
                , "async" => true
        ));
	$cm->oPage->addContent(confirm_dialog_success("backer"));
} else
{

$tpl_backer_menu = mod_crowdfund_get_menu_backer($smart_url, $arrType);
if(strlen($tpl_backer_menu)) {
	$cm->oPage->addContent($tpl_backer_menu);
}

$crowdfund_public_path = mod_crowdfund_get_path_by_lang("public");

if(!isset($_REQUEST["ret_url"]))
	$_REQUEST["ret_url"] = FF_SITE_PATH . $crowdfund_public_path . "/" . $smart_url;

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "BackersModify";
$oRecord->resources[] = $oRecord->id;  
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_backers_" . $arrType["selected"]["label"] . "_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_backers";
$oRecord->addEvent("on_do_action", "BackersModify_on_do_action");
$oRecord->addEvent("on_done_action", "BackersModify_on_done_action");
$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
$oRecord->user_vars["ID_idea"] = $ID_idea;
$oRecord->user_vars["smart_url"] = $smart_url;
$oRecord->user_vars["idea_name"] = $idea_name;
$oRecord->user_vars["type"] = $arrType["selected"];

$oRecord->buttons_options["delete"]["display"] = false;
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields["last_update"] =  new ffData(time(), "Number");
$oRecord->additional_fields["type"] =  new ffData($arrType["selected"]["name"]);


$oRecord->fixed_pre_content = ffTemplate::_get_word_by_code("crowdfund_backers_" . $arrType["selected"]["label"] . "_description");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

//if(!$_REQUEST["keys"]["ID"] > 0) {
	if($_REQUEST["keys"]["ID_idea"] > 0) {
		$oRecord->insert_additional_fields["ID_idea"] =  new ffData($_REQUEST["keys"]["ID_idea"], "Number");
	} else {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_idea";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_modify_ID_idea");
		$oField->base_type = "Number";
		$oField->widget = "activecomboex";
		$oField->actex_update_from_db = true;
		$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
									, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
                            			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
                            			WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
                            				AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
		                            ) AS title
								FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql(get_session("UserNID"), "Number") . "
								";
		$oField->required = true;
		$oRecord->addContent($oField);
	}
//}

if($_REQUEST["keys"]["ID_user_anagraph"] > 0) {
	$oRecord->insert_additional_fields["ID_user_anagraph"] =  new ffData($_REQUEST["keys"]["ID_user_anagraph"], "Number");
} else { 
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_user_anagraph";
	$oField->container_class = "anagraph_user";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_modify_ID_user_anagraph");
	$oField->base_type = "Number";
	$oField->source_SQL = "SELECT
							anagraph.ID
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
}


switch ($arrType["selected"]["name"]) {
	case "reward":
		if($_REQUEST["price"] > 0 && !$ID_reward) {
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge 
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($ID_idea, "Number") . "
						AND (
								(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit - (
										SELECT COUNT(*) 
											FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
											WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_reward = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
												AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
												" . ($_REQUEST["keys"]["ID"] > 0
													? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID <> " . $db->toSql($_REQUEST["keys"]["ID"], "Number")
													: ""
												) . "
										)
								) > 0
								OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit = 0
						)
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price <= " . $db->toSql($_REQUEST["price"], "Number") . "
					ORDER BY " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price DESC
					LIMIT 1";
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_reward = $db->getField("ID", "Number", true); 
			}
			
		}
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "price";
		$oField->container_class = "price";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_modify_price");
		$oField->base_type = "Number";
		$oField->widget = "slider";
		$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
		$oField->min_val = 1;
		$oField->max_val = $goal_diff + $actual_price;
		$oField->step = 1; //global_settings("MOD_CROWDFUND_GOAL_STEP");
		$oField->default_value = new ffData($_REQUEST["price"], "Number");
		$oRecord->addContent($oField);

		//AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit - (SELECT COUNT(*) FROM )) > 0
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge 
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($ID_idea, "Number") . "
					AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit - (
							SELECT COUNT(*) 
								FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_reward = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
									AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
									" . ($_REQUEST["keys"]["ID"] > 0
										? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID <> " . $db->toSql($_REQUEST["keys"]["ID"], "Number")
										: ""
									) . "
							)
					) > 0
					OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit = 0) 
				";
		$db->query($sSQL);
		if($db->nextRecord()) {
			if($cm->oPage->isXHR()) {
				$js_content = "ff.pluginLoad('idea-backer', '/modules/crowdfund/themes/javascript/idea-backer.js', function() {
					setTimeout('ideaBackerInit();', 100);
				});";

				$cm->oPage->tplAddJs("idea-backer"
                    , array(
                        "embed" => $js_content
                ));
			} else {
				$cm->oPage->tplAddJs("idea-backer"
                    , array(
                        "file" => "idea-backer.js"
                        , "path" => "/modules/crowdfund/themes/javascript"
                ));
			}
			

			$oGrid = ffGrid::factory($cm->oPage);
			$oGrid->full_ajax = true;
			$oGrid->id = "Pledge";
			//$oGrid->title = ffTemplate::_get_word_by_code("backers_title");
			$oGrid->source_SQL = "SELECT * 
									FROM
									(
										(
											SELECT 0 AS ID
												, 0 AS price
												, " . $db->toSql(ffTemplate::_get_word_by_code("crowdfund_pledge_noreward_title")) . " AS title
												, " . $db->toSql(ffTemplate::_get_word_by_code("crowdfund_pledge_noreward_description")) . " AS description
										) UNION (
											SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
												, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price
												, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.title
													FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages
													WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
														AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_languages = (" . $db->toSql(LANGUAGE_INSET_ID, "Number") . ")
												) AS title
												, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.description
													FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages
													WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
														AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_languages = (" . $db->toSql(LANGUAGE_INSET_ID, "Number") . ")
												) AS description
											FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge 
												INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea
											WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($ID_idea, "Number") . "
												AND (
														(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit - (
																SELECT COUNT(*) 
																	FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
																	WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_reward = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
																		AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
																		" . ($_REQUEST["keys"]["ID"] > 0
																			? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID <> " . $db->toSql($_REQUEST["keys"]["ID"], "Number")
																			: ""
																		) . "
																)
														) > 0
														OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit = 0
												) 
												AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff >= " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price 
												[AND] [WHERE]
										) 
									) AS tbl_src
									[ORDER]
									[HAVING]";
 			$oGrid->order_default = "price";
			$oGrid->use_search = false;
			$oGrid->record_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/reward/modify/" . $smart_url;
			$oGrid->record_id = "PledgeModify";
			$oGrid->resources[] = $oGrid->record_id;
			$oGrid->addEvent("on_before_parse_row", "Pledge_on_before_parse_row");
			$oGrid->display_new = false;
			$oGrid->display_edit_bt = false;
			$oGrid->display_edit_url = false;
			$oGrid->display_delete_bt = false;
			$oGrid->use_paging = false;
			$oGrid->user_vars["reward"] = (int) $ID_reward; 
			$oGrid->user_vars["currency_symbol"] = $currency_symbol; 
			

			// Campi chiave
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID";
			$oField->base_type = "Number";
			$oGrid->addKeyField($oField);
			
			// Campi di ricerca
			
			// Campi visualizzati
		    $oField = ffField::factory($cm->oPage);
		    $oField->id = "check";
		    $oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_check");
		    $oField->data_type = "";
		    $oField->encode_entities = false;	
		    $oGrid->addContent($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "price";
			$oField->container_class = "price";
			$oField->label = ffTemplate::_get_word_by_code("crowdfund_pledge_price");
			$oField->encode_entities = false;
			$oGrid->addContent($oField, false);


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
			$oField->encode_entities = false;
			$oGrid->addContent($oField, false);

		
			$oRecord->addContent($oGrid);
			$cm->oPage->addContent($oGrid);
			


		
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_reward";
			$oField->container_class = "reward";
			//$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_modify_reward");
			$oField->base_type = "Number";
			$oField->extended_type = "Selection";
			$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
										, CONCAT(FORMAT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price, 2)
											, ' '
											, (SELECT SUBSTRING(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.description, 1, 10)
												FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages
												WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
													AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
											)
										) AS title 
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge 
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($ID_idea, "Number") . "
										AND ((" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit - (
												SELECT COUNT(*) 
													FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
													WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_reward = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
														AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
														" . ($_REQUEST["keys"]["ID"] > 0
															? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID <> " . $db->toSql($_REQUEST["keys"]["ID"], "Number")
															: ""
														) . "
												)
										) > 0
										OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit = 0)
									ORDER BY title";
			$oField->properties["style"]["display"] = "none";
			$oField->default_value = new ffData($ID_reward, "Number");
			$oRecord->addContent($oField);
		}
		break;
	case "donate":
		$oField = ffField::factory($cm->oPage);
		$oField->id = "price";
		$oField->container_class = "price";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_modify_price");
		$oField->base_type = "Number";
		$oField->widget = "slider";
		$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
		$oField->min_val = 1;
		$oField->max_val = $goal_diff + $actual_price;
		$oField->step = 1; //global_settings("MOD_CROWDFUND_GOAL_STEP");
		$oField->default_value = new ffData($_REQUEST["price"], "Number");
		$oRecord->addContent($oField);
		break;
	case "invest":
	default:
		$oField = ffField::factory($cm->oPage);
		$oField->id = "price";
		$oField->container_class = "price";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_backers_modify_price");
		$oField->base_type = "Number";
		$oField->widget = "slider";
		$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
		$oField->min_val = ($goal_step > 0 ? ($goal_step > $goal_diff ? $goal_diff : $goal_step) : global_settings("MOD_CROWDFUND_GOAL_STEP"));
		$oField->max_val = $goal_diff + $actual_price;
		$oField->step = 1; //global_settings("MOD_CROWDFUND_GOAL_STEP");
		$oField->default_value = new ffData($_REQUEST["price"], "Number");
		$oRecord->addContent($oField);
		break;
}
$cm->oPage->addContent($oRecord);


if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
	$cm->oPage->addContent($tpl_menu);
}
}
function Pledge_on_before_parse_row($component) {
	if(isset($component->grid_fields["check"])) {
		$str_check = '<input type="radio" name="reward" value="' . $component->key_fields["ID"]->getValue() . '" price="' . $component->db[0]->getField("price", "Number")->getValue("Number", FF_SYSTEM_LOCALE) . '" ';

		if($component->user_vars["reward"] == $component->key_fields["ID"]->getValue())
			$str_check .= ' checked="checked" ';
		
		$str_check .= ' />';

		$component->grid_fields["check"]->setValue($str_check);
	}


	if(isset($component->grid_fields["price"])) {
		if($component->db[0]->getField("price", "Number", true) > 0) {
			$component->grid_fields["price"]->setValue($component->db[0]->getField("price", "Number")->getValue("Currency", FF_LOCALE) . '<span class="symbol"> ' . $component->user_vars["currency_symbol"] . '</span>');
		} else {
			$component->grid_fields["price"]->setValue("");
		} 
		
	}
}

function BackersModify_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
    $UserNID = get_session("UserNID");
        if(strlen($action))
        {
            $check = control_user_complete_information($UserNID);
            
        
	switch($action) {
            
                        
		case "insert":
			if($component->form_fields["price"]->getValue() < $component->form_fields["price"]->min_val) {
				$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_price_toolow"));
				return true;
			}
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($component->user_vars["ID_idea"], "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff < " . $db->toSql($component->form_fields["price"]->value);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_price_insert_toohigh"));
				return true;
			}
			if(global_settings("MOD_CROWDFUND_IDEA_LIMIT_OFFER"))
			{
				if(isset($component->form_fields["ID_user_anagraph"])) {
					$ID_user_anagraph = $component->form_fields["ID_user_anagraph"]->getValue();	
				} else if($_REQUEST["keys"]["ID_user_anagraph"] > 0) {
					$ID_user_anagraph = $_REQUEST["keys"]["ID_user_anagraph"];	
				}
				$offer = mod_crowdfund_control_offert_limit($component->user_vars["ID_idea"], $ID_user_anagraph);
				
				if($offer) 
				{
					$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_too_many_offers"));
					return true; 
				}
			}
			break;
		case "update":
			if($component->form_fields["price"]->getValue() < $component->form_fields["price"]->min_val) {
				$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_price_toolow"));
				return true;
			}

			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($component->user_vars["ID_idea"], "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff + " . $db->toSql($component->form_fields["price"]->value_ori) . " < " . $db->toSql($component->form_fields["price"]->value);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_idea_price_update_toohigh"));
				return true;
			}
			break;
		case "confirmdelete":
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers 
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($component->key_fields["ID"]->value);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_bill = $db->getField("ID_bill", "Number", true);
				if($ID_bill > 0) {
					if(check_function("ecommerce_delete_documents_bill_by_order")) {
						ecommerce_delete_documents_bill_by_order($ID_bill, "bill", true);
					}
				}
			}

			break;
		
		default:
	}
        if(!$check["check"])
            {
            //echo MOD_CROWDFUND_PATH;
            $component->redirect(FF_SITE_PATH . "/info?mifid=" . $check["mifid"] . "&account=" . $check["account"] . "&presente=" . $check["mifid_presente"] . "&smarturl=" . $component->user_vars["smart_url"]);  
        }
        }
}
function BackersModify_on_done_action($component, $action) {
    $UserNID = get_session("UserNID");
    $check = control_user_complete_information($UserNID);
    
    
    if($check["check"])
    {
    $db = ffDB_Sql::factory();
	
    if(strlen($action)) 
    {
        
            switch($action) 
            {
                case "retry": 
                    if($_REQUEST["retry"] > 0) {
                            set_session("cart_id", $_REQUEST["retry"]);
                            $component->redirect(FF_SITE_PATH . VG_SITE_CART . "/retry"); 
                    }
                    break;
                case "insert":
                case "update":
                    
                    if(global_settings("MOD_CROWDFUND_IDEA_CONSIDER_ALL_OFFER"))
                        {
                            mod_crowdfund_update_goal($component->user_vars["ID_idea"], true);
                        } else 
                        {
                            if ($component->user_vars["type"]["name"] == "reward")
                            {
                                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.*
                                            FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge
                                            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($component->user_vars["ID_idea"], "Number") . "
                                                AND ((" . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit - (
                                                        SELECT COUNT(*) 
                                                                FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
                                                                WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_reward = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
                                                                        AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($component->user_vars["ID_idea"], "Number") . "
                                                                        " . ($_REQUEST["keys"]["ID"] > 0
                                                                                ? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID <> " . $db->toSql($_REQUEST["keys"]["ID"], "Number")
                                                                                : ""
                                                                        ) . "
                                                                )) > 0
                                                    OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.limit = 0)
                                                    AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price <= " . $db->toSql($component->form_fields["price"]->value) . "
                                            ORDER BY " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price DESC
                                            LIMIT 1";
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                        $ID_reward = $db->getField("ID", "Number", true);
                                } else {
                                        $ID_reward = 0;
                                }

                                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers SET
                                                        ID_reward = " . $db->toSql($ID_reward, "Number") . "
                                                WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($component->key_fields["ID"]->value);
                                $db->execute($sSQL);
                            }

                            if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT")) {
                                $sSQL = "SELECT ecommerce_order.ID AS ID_order
                                            FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
                                                INNER JOIN ecommerce_documents_bill ON ecommerce_documents_bill.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_bill
                                                INNER JOIN ecommerce_order ON ecommerce_order.ID_bill = ecommerce_documents_bill.ID
                                            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($component->key_fields["ID"]->value);
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                        $ID_order = $db->getField("ID_order", "Number", true);
                                }

                                if($ID_order > 0) {
                                    set_session("cart_id", $ID_order);
                                    $component->redirect(FF_SITE_PATH . VG_SITE_CART . "/retry");
                                } else {
                                    if(check_function("ecommerce_cart_addtocart")) {
                                        $ID_detail = 0;

                                        $cart_item[$ID_detail]["quantity"] = "1";
                                        $cart_item[$ID_detail]["callback"] = "mod_crowdfund_check_payment";
                                        $cart_item[$ID_detail]["callback_data"] = array("ID_backer" => $component->key_fields["ID"]->getValue());

                                        $cart_item[$ID_detail]["custom"]["enable_sum_quantity"] = false;
                                        $cart_item[$ID_detail]["custom"]["description"]			= ffTemplate::_get_word_by_code("crowdfund_" . $component->user_vars["type"]["label"] . "_pay") . " " . $component->user_vars["idea_name"] ;	//required
                                        $cart_item[$ID_detail]["custom"]["enable_sum_quantity"]	= true;
                                        $cart_item[$ID_detail]["custom"]["price"]				= $component->form_fields["price"]->getValue();	//required
                                        $cart_item[$ID_detail]["custom"]["vat"]					= 0;
                                        //$cart_data["crowdfund"][$component->user_vars["smart_url"]]["ID_backer"] = $component->key_fields["ID"]->getValue();

                                        //set_session("cart_data", $cart_data);
                                        $preapproval_date = 0;
                                        if($component->user_vars["ID_idea"] > 0) {
                                            $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.* 
                                                            FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
                                                            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($component->user_vars["ID_idea"], "Number");
                                            $db->query($sSQL);
                                            if($db->nextRecord()) {
                                                $ID_anagraph_company = $db->getField("ID_anagraph_company", "Number", true);

                                                $created = $db->getField("created", "Number", true);
                                                $expiration = $db->getField("expire", "Number", true);
                                                $activated = $db->getField("activated", "Number", true);


                                                if($expiration)
                                                {
                                                        $preapproval_date = $activated + $expiration * 86400;
                                                } else
                                                {
                                                        $preapproval_date = $activated + (global_settings("MOD_CROWDFUND_EXPIRATION")) * 86400; 
                                                }
                                            }
                                        }

                                        $UserID = null;
                                        $UserNID = null;
                                        if(isset($component->form_fields["ID_user_anagraph"])) {
                                            $ID_user_anagraph = $component->form_fields["ID_user_anagraph"]->getValue();	
                                        } else if($_REQUEST["keys"]["ID_user_anagraph"] > 0) {
                                            $ID_user_anagraph = $_REQUEST["keys"]["ID_user_anagraph"];	
                                        }

                                        if($ID_user_anagraph > 0) {
                                            $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.* 
                                                FROM " . CM_TABLE_PREFIX . "mod_security_users 
                                                WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = 
                                                        (
                                                                SELECT anagraph.uid 
                                                                FROM anagraph 
                                                                WHERE anagraph.ID = " . $db->toSql($ID_user_anagraph, "Number"). "
                                                                ORDER BY anagraph.ID
                                                                LIMIT 1
                                                        )";
                                            $db->query($sSQL);
                                            if($db->nextRecord()) {
                                                $UserID = $db->getField("username", "Text", true);
                                                $UserNID = $db->getField("ID", "Number", true);
                                            }
                                        } 


                                        if(strlen($component->user_vars["type"]["limit_mpay"])) {
                                            $limit_mpay = explode(",", $component->user_vars["type"]["limit_mpay"]);
                                        } else {
                                            $limit_mpay = array();
                                        }

                                        $cart_res = ecommerce_cart_addtocart($cart_item, "", 0, $UserID, $UserNID, true, $preapproval_date, $ID_anagraph_company, $limit_mpay);
                                        set_session("cart_id", $cart_res["ID_order"]);
                                        $component->redirect(FF_SITE_PATH . VG_SITE_CART . "/additionaldata?ret_url=" . urlencode($component->parent[0]->site_path . $component->parent[0]->page_path . "/" . $component->user_vars["smart_url"] . "?keys[ID]=" . $component->key_fields["ID"]->getValue() . (strlen($_REQUEST["ret_url"]) ? "&ret_url=" . urlencode($_REQUEST["ret_url"]) : "")) . "&preserve");
                                    }
                                }
                            }
                        }
						if(isset($_REQUEST["XHR_DIALOG_ID"]))
							die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "url" => FF_SITE_PATH . $component->page_path . "/" . $component->user_vars["smart_url"] ."?success&ret_url=" . $_REQUEST["ret_url"]), true));
                    break;
                case "confirmdelete":
                    break;

                default: 
                    break;
    }
        
    }
    }
}
?>