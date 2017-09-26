<?php
cm::getInstance()->addEvent("on_before_page_process", "mod_crowdfund_on_before_page_process", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("on_before_routing", "mod_crowdfund_on_before_rounting", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("mod_security_on_created_session", "mod_crowdfund_mod_security_on_created_session", ffEvent::PRIORITY_NORMAL);
//cm::getInstance()->addEvent("vg_on_form_done", "mod_crowdfund_save_question");

if(check_function("ecommerce_cart_callback")) {
	cm::getInstance()->addEvent("vg_on_cart_process", "ecommerce_cart_callback");
	cm::getInstance()->addEvent("vg_on_cart_confirm_process", "ecommerce_cart_callback");
	
	cm::getInstance()->addEvent("vg_on_mpay_payed", "ecommerce_cart_callback");
	cm::getInstance()->addEvent("vg_on_recalc_bill_done", "ecommerce_cart_callback");
	cm::getInstance()->addEvent("vg_on_delete_order", "ecommerce_cart_callback");
	cm::getInstance()->addEvent("vg_on_form_done", "mod_crowdfund_set_backer");
} 

define("MOD_CROWDFUND_PATH", $cm->router->named_rules["crowdfund"]->reverse); 
define("MOD_CROWDFUND_USER_PATH", $cm->router->named_rules["user_crowdfund_idea"]->reverse);
define("MOD_CROWDFUND_DASHBOARD_PATH", $cm->router->named_rules["user_crowdfund_dashboard"]->reverse);
define("MOD_CROWDFUND_SERVICES_PATH", $cm->router->named_rules["crowdfund_services"]->reverse);
define("MOD_CROWDFUND_QUESTION_PATH", $cm->router->named_rules["crowdfund_question"]->reverse);

function mod_crowdfund_on_before_page_process($cm) {
	$globals = ffGlobals::getInstance("gallery"); 
	
	
	$globals->user["menu"] = array(
						"/user" => array("label" => ffTemplate::_get_word_by_code("crowdfund_user_dashboard"))
						, "/user/idea" => array("label" => ffTemplate::_get_word_by_code("crowdfund_ideas_title"))
						, "/user/idea/attach" => false
						, "/user/idea/backers" => false
						, "/user/idea/basic" => false
						, "/user/idea/businessplan" => false
						, "/user/idea/estimatebudget" => false
						, "/user/idea/followers" => false
						, "/user/idea/owner" => false
						, "/user/idea/qa" => false
						, "/user/idea/reward" => false
						, "/user/idea/setting" => false
						, "/user/idea/team" => false
						, "/user/idea/timeline" => false
						, "/riconpense" => array("label" => ffTemplate::_get_word_by_code("crowdfund_reward_title"), "break" => true)
					);
	if(strlen((string) $cm->router->getRuleById("crowdfund_invest_" . strtolower(FF_LOCALE))->reverse)) {
		$globals->user["menu"][(string) $cm->router->getRuleById("crowdfund_invest_" . strtolower(FF_LOCALE))->reverse] = array("label" => ffTemplate::_get_word_by_code("crowdfund_nav_invest"), "break" => true);
	} elseif(strlen((string) $cm->router->getRuleById("crowdfund_invest")->reverse)) {
		$globals->user["menu"][(string) $cm->router->getRuleById("crowdfund_invest")->reverse] = array("label" => ffTemplate::_get_word_by_code("crowdfund_nav_invest"), "break" => true);
	}
	if(strlen((string) $cm->router->getRuleById("crowdfund_reward_" . strtolower(FF_LOCALE))->reverse)) {
		$globals->user["menu"][(string) $cm->router->getRuleById("crowdfund_reward_" . strtolower(FF_LOCALE))->reverse] = array("label" => ffTemplate::_get_word_by_code("crowdfund_nav_reward"), "break" => true);
	} elseif(strlen((string) $cm->router->getRuleById("crowdfund_reward")->reverse)) {
		$globals->user["menu"][(string) $cm->router->getRuleById("crowdfund_reward")->reverse] = array("label" => ffTemplate::_get_word_by_code("crowdfund_nav_reward"), "break" => true);
	}
	if(strlen((string) $cm->router->getRuleById("crowdfund_donate_" . strtolower(FF_LOCALE))->reverse)) {
		$globals->user["menu"][(string) $cm->router->getRuleById("crowdfund_donate_" . strtolower(FF_LOCALE))->reverse] = array("label" => ffTemplate::_get_word_by_code("crowdfund_nav_donate"), "break" => true);
	} elseif(strlen((string) $cm->router->getRuleById("crowdfund_donate")->reverse)) {
		$globals->user["menu"][(string) $cm->router->getRuleById("crowdfund_donate")->reverse] = array("label" => ffTemplate::_get_word_by_code("crowdfund_nav_donate"), "break" => true);
	}
	
	if(strpos($cm->path_info, MOD_CROWDFUND_PATH) !== false) {
        if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . global_settings("MOD_CROWDFUND_THEME"))) {
		    $cm->layout_vars["theme"] = global_settings("MOD_CROWDFUND_THEME");
        }
	}
}

function mod_crowdfund_on_before_rounting($cm) {
	$permission = check_crowdfund_permission();
	if(!(is_array($permission) && count($permission)
		&& ($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]
			|| $permission[global_settings("MOD_CROWDFUND_GROUP_USER")]
		)
	)) {
    	$cm->modules["restricted"]["menu"]["crowdfund"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["idea"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["categories"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["banner"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["help"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["anagraph"]["hide"] = true;
	} else {
		if(strpos($cm->path_info, MOD_CROWDFUND_PATH) !== false
			|| strpos($cm->path_info, MOD_CROWDFUND_USER_PATH) !== false
		) {
            if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "library/jquery.ui/themes/" . global_settings("MOD_CROWDFUND_JQUERYUI_THEME"))) {
    		    $cm->oPage->jquery_ui_force_theme = global_settings("MOD_CROWDFUND_JQUERYUI_THEME");
            }

			$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["idea"]["hide"] = false;
			if($permission["primary_group"] != global_settings("MOD_CROWDFUND_GROUP_ADMIN")) {
				$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["categories"]["hide"] = false;
				$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["banner"]["hide"] = false;
				$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["help"]["hide"] = false;
				$cm->modules["restricted"]["menu"]["crowdfund"]["elements"]["anagraph"]["hide"] = false;
			}
			if($permission["primary_group"] != global_settings("MOD_CROWDFUND_GROUP_USER")) {
				
			}
			
			if(function_exists("check_function") && check_function("system_set_js")) {
				system_set_js($cm->oPage, $cm->path_info, false, "/modules/crowdfund/themes/javascript", true);
			}
		}
	}
}

function check_crowdfund_permission($check_group = null) {
    $db = ffDB_Sql::factory();

    $user_permission = get_session("user_permission");
    $userID = get_session("UserID");
	if(is_array($user_permission) && count($user_permission) 
    	&& is_array($user_permission["groups"]) && count($user_permission["groups"])
    	&& $userID != MOD_SEC_GUEST_USER_NAME
    ) {
    	if(!array_key_exists("permissions_custom", $user_permission))
            $user_permission["permissions_custom"] = array();

	    if(!(array_key_exists("crowdfund", $user_permission["permissions_custom"]) && count($user_permission["permissions_custom"]["crowdfund"]))) {
	    	$user_permission["permissions_custom"]["crowdfund"] = array();
	    	
		    $strGroups = implode(",", $user_permission["groups"]);
			$strPermission = $db->toSql(global_settings("MOD_CROWDFUND_GROUP_ADMIN"), "Text") 
							. "," . $db->toSql(global_settings("MOD_CROWDFUND_GROUP_USER"), "Text")
							. "," . $db->toSql(global_settings("MOD_CROWDFUND_GROUP_PUBLIC_USER"), "Text"); 
				
		    $user_permission["permissions_custom"]["crowdfund"][global_settings("MOD_CROWDFUND_GROUP_ADMIN")] = false;
		    $user_permission["permissions_custom"]["crowdfund"][global_settings("MOD_CROWDFUND_GROUP_USER")] = false;
			$user_permission["permissions_custom"]["crowdfund"][global_settings("MOD_CROWDFUND_GROUP_PUBLIC_USER")] = false;
		    $user_permission["permissions_custom"]["crowdfund"]["primary_group"] = "";
		    
		    $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.name
		                , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
		            FROM " . CM_TABLE_PREFIX . "mod_security_groups
		              INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
		            WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
		              AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN ( " . $strPermission . " )";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
				do {
			        $user_permission["permissions_custom"]["crowdfund"][$db->getField("name", "Text", true)] = true;
			        $user_permission["permissions_custom"]["crowdfund"]["primary_group"] = $db->getField("name", "Text", true);
				} while($db->nextRecord());
		    }
		    
	        set_session("user_permission", $user_permission);
		}
		//print_r($user_permission);
		if($check_group === null) { 
	    	return $user_permission["permissions_custom"]["crowdfund"];
		} else {
			return $user_permission["permissions_custom"]["crowdfund"]["primary_group"];
		}
	}    
    return null;
}

function mod_crowdfund_mod_security_on_created_session($UserID, $UserNID, $Domain, $DomainID, $old_session_id) {
	$user_permission = get_session("user_permission");

	if($user_permission["primary_gid_name"] == global_settings("MOD_CROWDFUND_GROUP_ADMIN")) {
		if(strlen(MOD_CROWDFUND_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_CROWDFUND_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_CROWDFUND_PATH);
			}
		}
	}

	if(strtolower($user_permission["primary_gid_name"]) == "crowdfund"
		|| $user_permission["primary_gid_name"] == global_settings("MOD_CROWDFUND_GROUP_USER")
		|| $user_permission["primary_gid_name"] == global_settings("MOD_CROWDFUND_GROUP_PUBLIC_USER")
	) {
		if(strlen(MOD_CROWDFUND_DASHBOARD_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_CROWDFUND_DASHBOARD_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_CROWDFUND_DASHBOARD_PATH);
			}
		}
	}
}

function mod_crowdfund_get_path_by_lang($type = "") {
	$cm = cm::getInstance();
	if(strlen($type))
		$type = "_" . $type;
	
	if($cm->router->getRuleById("crowdfund" . $type . "_" . strtolower(FF_LOCALE)) !== null) {
		$res_path = (string)$cm->router->getRuleById("crowdfund" . $type . "_" . strtolower(FF_LOCALE))->reverse;
	} else {
		$res_path = (string)$cm->router->getRuleById("crowdfund" . $type . "")->reverse;
	}
	
	return $res_path;
}

function mod_crowdfund_get_idea($navigation = null, $params = array(), $sort = null, $return_detail = false, $currency = true, $dashboard = null) {
	$db = ffDB_Sql::factory();
	//die("prova2");
	$UserNID = get_session("UserNID");
	
	$idea = array();
	
	
	$page = ($navigation["page"] > 0
				? $navigation["page"] 
				: 1
			);

	$rec_per_page = ($navigation["rec_per_page"] > 0
				? $navigation["rec_per_page"] 
				: global_settings("MOD_CROWDFUND_IDEA_REC_PER_PAGE")
			);

	$sort_field = (strlen($sort["field"])
				? $sort["field"] 
				: "last_update"
			);

	$sort_method = (strlen($sort["method"])
				? $sort["method"] 
				: "DESC"
			);
			

	$sSQL = "SELECT 
				" . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire AS expired
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title AS title
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.teaser AS teaser
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.description AS description
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages AS ID_languages
				, " . FF_PREFIX . "languages.code AS language_code
				, " . FF_PREFIX . "currency.symbol AS currency_symbol
				, IF  (" . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire > 0
					, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated > 0
						, (" . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire - ROUND((" . time() . " - " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated) / 86400, 0))
						, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire
					)
					, (" . global_settings("MOD_CROWDFUND_EXPIRATION") . " - " . global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY") . " - ROUND((" . time() . " - " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated) / 86400, 0)) 
				) AS expire
				, (SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID)
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID  
						AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph <> 
																					(
																					SELECT anagraph.ID 
																					FROM anagraph
																					WHERE anagraph.uid = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
																					ORDER BY anagraph.ID 
																					LIMIT 1
																					)
							OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private = 0
						)
				) AS count_backer
				, (SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID)
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
				) AS count_follower
				, " . (get_session("UserID") == MOD_SEC_GUEST_USER_NAME 
					? "0"
					: "(SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID)
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
							INNER JOIN anagraph ON anagraph.ID =  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_user_anagraph
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
							AND anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . " 
					)"
				) . " AS is_followed
			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
				INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
					AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
					AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title <> ''
				INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages
				INNER JOIN " . FF_PREFIX . "currency ON " . FF_PREFIX . "currency.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_currency
				INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
				" . (!global_settings("MOD_CROWDFUND_IDEA_ENABLE_TEAM_DIVISION_PROFILE") && is_array($params) && array_key_exists("owner", $params) && strlen($params["owner"])
						? " LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID"
						: ""
				) . "
				" . (is_array($params) && array_key_exists("banner", $params) && strlen($params["banner"])
					? " INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea ON " . CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_banner ON " . CM_TABLE_PREFIX . "mod_crowdfund_banner.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea.ID_banner
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_banner.`name` = " . $db->toSql($params["banner"])
					: ""
				) . "
				" . (is_array($params) && array_key_exists("backer", $params) && strlen($params["backer"])
					? " INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.`ID_user_anagraph` IN (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($params["backer"], "Number") . ")"
					: ""
				) . "
				" . (is_array($params) && array_key_exists("follower", $params) && strlen($params["follower"])
					? " INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.`ID_user_anagraph` IN (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($params["follower"], "Number") . ")"
					: ""
				) . "
				" . (global_settings("MOD_CROWDFUND_IDEA_ENABLE_TEAM_DIVISION_PROFILE") && is_array($params) && array_key_exists("team", $params) && strlen($params["team"])
					? " INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID"
					: ""
				) . "
			WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status > 0
				" . (global_settings("MOD_CROWDFUND_IDEA_LIMIT_ACTIVE_PROJECT")
					? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status_visible_decision > 0"
					: ""
				) . "
				" . (global_settings("MOD_CROWDFUND_IDEA_ACTIVE_PROJECT_BY_ADMIN")
					? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status_by_admin > 0"
					: ""
				) . "
				" . (is_array($params) && array_key_exists("ideas", $params) && strlen($params["ideas"])
					? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID IN (" . $db->toSql($params["ideas"], "Text", false) . ")"
					: ""
				) . "
				" . (is_array($params) && array_key_exists("smart_url", $params) && strlen($params["smart_url"])
					? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url = " . $db->toSql($params["smart_url"])
					: ""
				) . "
				" . (is_array($params) && array_key_exists("owner", $params) && strlen($params["owner"])
						? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql($params["owner"], "Number")
						. (!global_settings("MOD_CROWDFUND_IDEA_ENABLE_TEAM_DIVISION_PROFILE") 	
							? " OR " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.`ID_user_anagraph` IN (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($params["owner"], "Number") . ")"
							: ""
						)
						: ""
				) . "
				" . (global_settings("MOD_CROWDFUND_IDEA_ENABLE_TEAM_DIVISION_PROFILE") && is_array($params) && array_key_exists("team", $params) && strlen($params["team"])
					? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.`ID_user_anagraph` IN (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($params["team"], "Number") . ")"
					: ""
				) . "
			GROUP BY " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url
			ORDER BY 
				" . (is_array($params) && array_key_exists("banner", $params) && strlen($params["banner"])
					? CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea.`order`,  "
					: ""
				). "
				" . $sort_field . " " . $sort_method . "
				, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
					, 0
					, IF(" . CM_TABLE_PREFIX . "mod_security_users.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
						, 1
						, 2
					)
				)";
 
	$db->query($sSQL);
	if($db->nextRecord()) 
	{
		$count_idea = $db->numRows();
		$i = 1;
 
        $db->jumpToPage($page, $rec_per_page);
		do {
			$ID_idea = $db->getfield("ID", "Number", true);
			if (($ID_idea == 88 || $ID_idea == 125 || $ID_idea == 127 || $ID_idea == 157 || $ID_idea == 158 || $ID_idea == 162 || $ID_idea == 163) 
					&& ($UserNID != 1 && $UserNID != 4 && $UserNID != 6)) 
				continue;
			if($i > $rec_per_page)
				break;

			$smart_url = $db->getField("smart_url", "Text", true);
			$language_code = $db->getField("language_code", "Text", true);
			
			$idea[$smart_url]["ID"] = $ID_idea;
			
			$idea[$smart_url]["smart_url"] = $db->getField("smart_url", "Text", true);
			$idea[$smart_url]["title"] = $db->getField("title", "Text", true);
			$idea[$smart_url]["teaser"] = $db->getField("teaser", "Text", true);
			
			$idea[$smart_url]["languages_set"] = $db->getField("languages", "Text", true);

			$idea[$smart_url]["cover"] = $db->getField("cover", "Text", true);
			$idea[$smart_url]["description"] = $db->getField("description", "Text", true);
			if($currency)
			{
				$idea[$smart_url]["goal"] = $db->getField("goal", "Number")->getValue("Currency", FF_LOCALE);
			} else
			{
				$idea[$smart_url]["goal"] = $db->getField("goal", "Number", true);
			}
			$idea[$smart_url]["goal_step"] = $db->getField("goal_step", "Number")->getValue("Currency", FF_LOCALE);
			$idea[$smart_url]["goal_current"] = $db->getField("goal_current", "Number")->getValue("Currency", FF_LOCALE);

			$idea[$smart_url]["real_goal"] = $db->getField("goal", "Number")->getValue("Number", FF_LOCALE);
			$idea[$smart_url]["real_goal_current"] = $db->getField("goal_current", "Number")->getValue("Number", FF_LOCALE);

			if($idea[$smart_url]["real_goal"] > 0) {
				$idea[$smart_url]["goal_perc"] = round($idea[$smart_url]["real_goal_current"] * 100  / $idea[$smart_url]["real_goal"], 0);
			} else {
				$idea[$smart_url]["goal_perc"] = 0;
			}

			$idea[$smart_url]["equity"] = $db->getField("equity", "Number", true);
			$idea[$smart_url]["symbol"] = html_entity_decode($db->getField("currency_symbol", "Text", true), ENT_COMPAT, "UTF-8"); 
			$idea[$smart_url]["video"] = $db->getField("video", "Text", true);
			
			$idea[$smart_url]["skype_account"] = $db->getField("skype_account", "Text", true);
			
			$idea[$smart_url]["count_backer"] = $db->getField("count_backer", "Number", true);
			$idea[$smart_url]["count_follower"] = $db->getField("count_follower", "Number", true);
			
			$idea[$smart_url]["created"] = $db->getField("created", "Number", true);
			
			$idea[$smart_url]["owner"] = $db->getField("owner", "Number", true);
			$idea[$smart_url]["is_startup"] = $db->getField("is_startup", "Number", true);
			$idea[$smart_url]["is_followed"] = $db->getField("is_followed", "Number", true);
			$idea[$smart_url]["ID_anagraph_company"] = $db->getField("ID_anagraph_company", "Number", true);
			$idea[$smart_url]["capital_funded"] = $db->getField("capital_funded", "Number")->getValue("Currency", FF_LOCALE);
			
			$idea[$smart_url]["enable_donation"] = $db->getField("enable_donation", "Number", true);
			$idea[$smart_url]["enable_equity"] = $db->getField("enable_equity", "Number", true);
			$idea[$smart_url]["enable_pledge"] = $db->getField("enable_pledge", "Number", true);
			
			$total_capital = new ffData($db->getField("capital_funded", "Number", true) + $db->getField("goal", "Number", true), "Number");
			$idea[$smart_url]["total_capital"] = $total_capital->getValue("Currency", FF_LOCALE);
			
			$idea[$smart_url]["expired"] = $db->getField("expired", "Number", true);
			
			$expire = $db->getField("expire", "Number", true);
			if($expire < 0)
			{
				$expire = 0;
			}
			$idea[$smart_url]["expire"] = $expire;
			
			$idea[$smart_url]["activated"] = $db->getField("activated", "Number", true);
			$idea[$smart_url]["status_visible_decision"] = $db->getField("status_visible_decision", "Number", true);
			$i++;
		} while($db->nextRecord());
		
		
	}
	if($return_detail) {
		return array("data" => $idea, "count" => $count_idea, "rec_per_page" => $rec_per_page);
	} else {
		return $idea;
	}
}


//Businessplan
function mod_crowdfund_get_businessplan($ID_idea, $ID_lang = null, $override = null) 
{
	//print_r($override);
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

	static $businessplan = array();

	if($ID_lang === null)
		$ID_lang = LANGUAGE_INSET_ID;
	
	if(!array_key_exists($ID_idea, $businessplan) || $override !== null) 
	{
	    //BusinessPlan data Lang
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.ID_idea = " . $db->toSql($ID_idea, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.ID_languages = " . $db->toSql($ID_lang, "Number");
	    $db->query($sSQL);
	    if($db->nextRecord()) 
	    {
	        do
	        {
				if(is_array($override) && array_key_exists("description_product", $override)) {
					$businessplan[$ID_idea]["description_product"] = $override["description_product"]->getValue();
				} else
				{
					$businessplan[$ID_idea]["description_product"] = strip_tags($db->getField("description_product", "Text", true));
				}
				if(is_array($override) && array_key_exists("target_market", $override)) {
					$businessplan[$ID_idea]["target_market"] = $override["target_market"]->getValue();
				} else
				{
					$businessplan[$ID_idea]["target_market"] = strip_tags($db->getField("target_market", "Text", true));
				}
				if(is_array($override) && array_key_exists("commercial_strategy", $override)) {
					$businessplan[$ID_idea]["commercial_strategy"] = $override["commercial_strategy"]->getValue();
				} else
				{
					$businessplan[$ID_idea]["commercial_strategy"] = strip_tags($db->getField("commercial_strategy", "Text", true));
				}
				if(is_array($override) && array_key_exists("sell_goal", $override)) {
					$businessplan[$ID_idea]["sell_goal"] = $override["sell_goal"]->getValue();
				} else
				{
					$businessplan[$ID_idea]["sell_goal"] = strip_tags($db->getField("sell_goal", "Text", true));
				}
				if(is_array($override) && array_key_exists("investment_description", $override)) {
					$businessplan[$ID_idea]["investment_description"] = $override["investment_description"]->getValue();
				} else
				{
					$businessplan[$ID_idea]["investment_description"] = strip_tags($db->getField("investment_description", "Text", true));
				}
				if(is_array($override) && array_key_exists("weakness_strength", $override)) {
					$businessplan[$ID_idea]["weakness_strength"] = $override["weakness_strength"]->getValue();
				} else
				{
					$businessplan[$ID_idea]["weakness_strength"] = strip_tags($db->getField("weakness_strength", "Text", true));
				}
	        } while($db->nextRecord());
	    }
	}    
    return $businessplan[$ID_idea];
}

//idea
function mod_crowdfund_get_idea_basic($ID_idea, $ID_lang = null, $override = null) 
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $idea_basic = array();
	
    if($ID_lang === null)
		$ID_lang = LANGUAGE_INSET_ID;

    if(!array_key_exists($ID_idea, $idea_basic) || $override !== null) 
    {
		//Idea data
		
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.*
					, (SELECT anagraph_rel_nodes_fields.description 
							FROM anagraph_rel_nodes_fields 
								LEFT JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields
							WHERE anagraph_fields.name = 'start up innovativa'
								AND anagraph_rel_nodes_fields.ID_nodes = cm_mod_crowdfund_idea.ID_anagraph_company
					) AS start_up_innovativa
					, (SELECT anagraph_rel_nodes_fields.description 
							FROM anagraph_rel_nodes_fields 
								LEFT JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields
							WHERE anagraph_fields.name = 'Visura PDF'
								AND anagraph_rel_nodes_fields.ID_nodes = cm_mod_crowdfund_idea.ID_anagraph_company
					) AS visura_PDF
					,(SELECT anagraph_rel_nodes_fields.description 
							FROM anagraph_rel_nodes_fields 
								LEFT JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields
							WHERE anagraph_fields.name = 'autocertification'
								AND anagraph_rel_nodes_fields.ID_nodes = cm_mod_crowdfund_idea.ID_anagraph_company
					) AS autocertification
					, (SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID)
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
								AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = (
																						SELECT anagraph.uid 
																						FROM anagraph 
																						WHERE anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph
																					)
								AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private > 0
					) AS first_offer
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql($ID_lang, "Number");
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			do
			{
				$idea_basic[$ID_idea]["first_offer"] =  $db->getField("first_offer", "Number", true);
				$idea_basic[$ID_idea]["activated"] =  $db->getField("activated", "Number", true);
				$idea_basic[$ID_idea]["start_up_innovativa"] =  $db->getField("start_up_innovativa", "Number", true);
				$idea_basic[$ID_idea]["visura_PDF"] =  $db->getField("visura_PDF", "Text", true);
				$idea_basic[$ID_idea]["autocertification"] =  $db->getField("autocertification", "Text", true);
				
				if(is_array($override) && array_key_exists("smart_url", $override)) {
					$idea_basic[$ID_idea]["smart_url"] = $override["smart_url"]->getValue();
				} else {
					$idea_basic[$ID_idea]["smart_url"] = $db->getField("smart_url", "Text", true);
				}
				if(is_array($override) && array_key_exists("cover", $override)) {
					$idea_basic[$ID_idea]["cover"] = $override["cover"]->getValue();
				} else {
					$idea_basic[$ID_idea]["cover"] = $db->getField("cover", "Text", true);
				}
				if(is_array($override) && array_key_exists("categories", $override)) {
					$idea_basic[$ID_idea]["category"] = $override["categories"]->getValue();
				} else {
					$idea_basic[$ID_idea]["category"] = $db->getField("categories", "Text", true);
				}
				if(is_array($override) && array_key_exists("video", $override)) {
					$idea_basic[$ID_idea]["video"] = $override["video"]->getValue();
				} else {
					$idea_basic[$ID_idea]["video"] = $db->getField("video", "Text", true);
				}
				if(is_array($override) && array_key_exists("website", $override)) {
					$idea_basic[$ID_idea]["website"] = $override["website"]->getValue();
				} else {
					$idea_basic[$ID_idea]["website"] = $db->getField("website", "Text", true);
				}
				if(is_array($override) && array_key_exists("goal", $override)) {
					$idea_basic[$ID_idea]["goal"] = $override["goal"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["goal"] = $db->getField("goal", "Number", true); //non processa decimal correttamente col true
				}
				if(is_array($override) && array_key_exists("goal_step", $override)) {
					$idea_basic[$ID_idea]["goal_step"] = $override["goal_step"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["goal_step"] = $db->getField("goal_step", "Number", true);
				}
				if(is_array($override) && array_key_exists("skype_account", $override)) {
					$idea_basic[$ID_idea]["skype_account"] = $override["skype_account"]->getValue();
				} else {
					$idea_basic[$ID_idea]["skype_account"] = $db->getField("skype_account", "Text", true);
				}
				if(is_array($override) && array_key_exists("equity", $override)) {
					$idea_basic[$ID_idea]["equity"] = $override["equity"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["equity"] = $db->getField("equity", "Number", true);
				}
				if(is_array($override) && array_key_exists("languages", $override)) {
					$idea_basic[$ID_idea]["languages"] = $override["languages"]->getValue();
				} else {
					$idea_basic[$ID_idea]["languages"] = $db->getField("languages", "Text", true);
				}
				if(is_array($override) && array_key_exists("is_startup", $override)) {
					$idea_basic[$ID_idea]["is_startup"] = $override["is_startup"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["is_startup"] = $db->getField("is_startup", "Number", true);
				}
				if(is_array($override) && array_key_exists("ID_anagraph_company", $override)) {
					$idea_basic[$ID_idea]["anagraph_company"] = $override["ID_anagraph_company"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["anagraph_company"] = $db->getField("ID_anagraph_company", "Number", true);
				}
				if(is_array($override) && array_key_exists("capital_funded", $override)) {
					$idea_basic[$ID_idea]["capital_funded"] = $override["capital_funded"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["capital_funded"] = $db->getField("capital_funded", "Number", true);
				}
				if(is_array($override) && array_key_exists("ID_currency", $override)) {
					$idea_basic[$ID_idea]["currency"] = $override["ID_currency"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["currency"] = $db->getField("ID_currency", "Number", true);
				}
				if(is_array($override) && array_key_exists("title", $override)) {
					$idea_basic[$ID_idea]["title"] = $override["title"]->getValue();
				} else {
					$idea_basic[$ID_idea]["title"] = $db->getField("title", "Text", true);
				}
				if(is_array($override) && array_key_exists("teaser", $override)) {
					$idea_basic[$ID_idea]["teaser"] = $override["teaser"]->getValue();
				} else {
					$idea_basic[$ID_idea]["teaser"] = $db->getField("teaser", "Text", true);
				}
				if(is_array($override) && array_key_exists("description", $override)) {
					$idea_basic[$ID_idea]["description"] = $override["description"]->getValue();
				} else {
					$idea_basic[$ID_idea]["description"] = $db->getField("description", "Text", true);
				}
				if(is_array($override) && array_key_exists("is_innovative", $override)) {
					$idea_basic[$ID_idea]["is_innovative"] = $override["is_innovative"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["is_innovative"] = $db->getField("is_innovative", "Number", true);
				}
				if(is_array($override) && array_key_exists("innovative_autocertification", $override)) {
					$idea_basic[$ID_idea]["innovative_autocertification"] = $override["innovative_autocertification"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["innovative_autocertification"] = $db->getField("innovative_autocertification", "Number", true);
				}
				if(is_array($override) && array_key_exists("innovative_documentation", $override)) {
					$idea_basic[$ID_idea]["innovative_documentation"] = $override["innovative_documentation"]->getValue();
				} else {
					$idea_basic[$ID_idea]["innovative_documentation"] = $db->getField("innovative_documentation", "Text", true);
				}
				if(is_array($override) && array_key_exists("enable_donation", $override)) {
					$idea_basic[$ID_idea]["enable_donation"] = $override["enable_donation"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["enable_donation"] = $db->getField("enable_donation", "Number", true);
				}
				if(is_array($override) && array_key_exists("enable_pledge", $override)) {
					$idea_basic[$ID_idea]["enable_pledge"] = $override["enable_pledge"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["enable_pledge"] = $db->getField("enable_pledge", "Number", true);
				}
				if(is_array($override) && array_key_exists("enable_equity", $override)) {
					$idea_basic[$ID_idea]["enable_equity"] = $override["enable_equity"]->getValue("Number");
				} else {
					$idea_basic[$ID_idea]["enable_equity"] = $db->getField("enable_equity", "Number", true);
				}
			} while($db->nextRecord());
		}
    }    
	
    return $idea_basic[$ID_idea];
}

//Pledge
function mod_crowdfund_get_pledge($ID_idea, $ID_lang = null, $max_value_pledge = true)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();
	
    static $pledge = array();

    if($ID_lang === null)
		$ID_lang = LANGUAGE_INSET_ID;

    if(!array_key_exists($ID_idea, $pledge)) 
    {
		//Pledge data
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.*
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.*
					, ( SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID)
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_reward = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private = 0
					) AS limit_used
					, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff >= " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price 
						, 0
						, 1						
					) AS disabled
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($ID_idea, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_languages = " . $db->toSql($ID_lang, "Number") . "
						" . ($max_value_pledge === true
									? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price <= " . $db->toSql(global_settings("MOD_CROWDFUND_MAX_VALUE_PLEDGE"), "Number")
									: ""
								) . "
					ORDER BY " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.price";
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			do
			{
				$pledge_ID = $db->getField("ID", "Number", true);
				$pledge[$ID_idea][$pledge_ID]["title"] = $db->getField("title", "Text", true);
				$pledge[$ID_idea][$pledge_ID]["description"] = $db->getField("description", "Text", true);
				$pledge[$ID_idea][$pledge_ID]["limit"] = $db->getField("limit", "Number", true);
				$pledge[$ID_idea][$pledge_ID]["limit_used"] = $db->getField("limit_used", "Number", true);
				$pledge[$ID_idea][$pledge_ID]["disabled"] = $db->getField("disabled", "Number", true);
				$pledge[$ID_idea][$pledge_ID]["price"] = $db->getField("price", "Number", true);
				$pledge[$ID_idea][$pledge_ID]["backer"] = $pledge[$ID_idea][$pledge_ID]["limit"];

				if ($pledge[$ID_idea][$pledge_ID]["limit"] > 0)
				{
					$pledge[$ID_idea][$pledge_ID]["backer"] = $pledge[$ID_idea][$pledge_ID]["limit"] - $pledge[$ID_idea][$pledge_ID]["limit_used"];
				} else
				{
					$pledge[$ID_idea][$pledge_ID]["backer"] = true;
				}
			} while($db->nextRecord());
		}
    } 
	return $pledge[$ID_idea];
}

//attach
function mod_crowdfund_get_attach($ID_idea, $timeline = null)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $attach = array();
    
    if(!array_key_exists($ID_idea, $attach) || $timeline === true) 
    {  
	    //Attach data
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_attach.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_attach
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_attach.ID_idea = " . $db->toSql($ID_idea, "Number");
	    $db->query($sSQL);
	    if($db->nextRecord()) 
	    {
			if($timeline === true)
			{
				do
				{
					$last_update = $db->getField("last_update", "Number", true);
					$attach[$last_update]["type"] = "attach";
					$attach[$last_update]["title"] = $db->getField("title", "Text", true);
					$attach[$last_update]["file"] = $db->getField("file", "Text", true); 
				} while($db->nextRecord());
			} else
			{
				do
				{
					$attach_ID = $db->getField("ID", "Number", true);
					$attach[$ID_idea][$attach_ID]["title"] = $db->getField("title", "Text", true);
					$attach[$ID_idea][$attach_ID]["file"] = $db->getField("file", "Text", true);
				} while($db->nextRecord());
			}
	    }
	}
	if ($timeline === true)
	{
		return $attach;
	} else
	{
		return $attach[$ID_idea];
	}
}

//creazione timeline
function mod_crowdfund_create_timeline($ID_idea, $date_start = null, $date_end = null)
{ 
    $cm = cm::getInstance();

    static $timeline = array();
	$attach = $backer = $follower = $timeline_data = array();
    
    if(!array_key_exists($ID_idea, $timeline)) 
    {
	    $attach = mod_crowdfund_get_attach($ID_idea, true);
		$backer = mod_crowdfund_get_backers($ID_idea, false, false, false, true);
		$follower = mod_crowdfund_get_follower($ID_idea, true);
		$timeline_data = mod_crowdfund_get_timeline($ID_idea);
		
		$tmp_timeline = $attach + $backer + $follower + $timeline_data;
		
		ksort($tmp_timeline);
		$begin = key($tmp_timeline);
		end($tmp_timeline);
		$key = key($tmp_timeline);
		if (is_array($tmp_timeline) && count($tmp_timeline))
		{
			if($date_end !== null && $date_start !== null)
			{
				if($date_start > $begin)
					$date_start = $begin;
				if($date_end < $key)
					$date_end = $key;

				$date = new DateTime(); 
				$date->setTimestamp($date_start); 
				if(check_function("datediff"))
					$number_month = datediff ("m", $date_start, $date_end, true);

				for($i=0; $i<$number_month + 2; $i++)
				{
					if($i>0) 
						$date->add(new DateInterval("P" . 1 . "M")); 
					$timeline[$date->format("Y-m")] = array();
				}

			} 
			
			foreach ($tmp_timeline as $tmp_timeline_key => $tmp_timeline_value) 
			{
				$timeline[date("Y-m", $tmp_timeline_key)][$tmp_timeline_key] = $tmp_timeline_value;
			}
		}
	}
    return $timeline;
}

//timeline
function mod_crowdfund_get_timeline($ID_idea)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $timeline = array();
    
    if(!array_key_exists($ID_idea, $timeline)) 
    {  
	    //timeline data
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.*
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_timeline = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_idea = " . $db->toSql($ID_idea, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
						
	    $db->query($sSQL);
	    if($db->nextRecord()) 
	    {
	        do
	        {
				$date = $db->getField("date", "Number", true); 
				$timeline[$date]["ID"] = $db->getField("ID", "Number", true);
				$timeline[$date]["type"] = "timeline"; 
				$timeline[$date]["title"] = $db->getField("title", "Text", true);
				$timeline[$date]["file"] = $db->getField("file", "Text", true); 
				$timeline[$date]["description"] = $db->getField("description", "Text", true);
	        } while($db->nextRecord());
	    }
	}
    return $timeline;
}
 
//cash flow
function mod_crowdfund_get_cash_flow($ID_idea, $net_income = null, $rev = false, $currency = true)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $cash_flow = array();

    if(!array_key_exists($ID_idea, $cash_flow)) 
    {  
    	//cash flow data
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.*
					,(SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.net_income
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.ID_idea = " . $db->toSql($ID_idea, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.year =  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.year
					)AS net_income
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.ID_idea = " . $db->toSql($ID_idea, "Number") . "
					ORDER BY `year` ASC";
	    $db->query($sSQL);
	    
	    if($db->nextRecord()) 
	    {
	        do
	        {
				$cashflow_from_operation = new ffData($db->getField("net_income", "Number", true) + 
								$db->getField("cashflow_depreciation_amortization", "Number", true), "Number");
				$cashflow_from_investing = new ffData($db->getField("cashflow_capital_expenditure", "Number", true) - 
								$db->getField("cashflow_acquisitions", "Number", true), "Number");
				$cashflow_from_financing = new ffData($db->getField("cashflow_dividends_paid", "Number", true) + 
								$db->getField("cashflow_share_issue", "Number", true), "Number");
				$total_cashflow = new ffData($cashflow_from_operation->getValue("Number") +
							  $cashflow_from_investing->getValue("Number") +
							  $cashflow_from_financing->getValue("Number"), "Number");
				$year = $db->getField("year", "Number", true);
				if($currency)
				{
					$cash_flow[$ID_idea]["default"][$year]["cashflow_net_income"]					= $db->getField("net_income", "Number")->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_depreciation_amortization"]	= $db->getField("cashflow_depreciation_amortization", "Number")->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_operation"]			= $cashflow_from_operation->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_capital_expenditure"]			= $db->getField("cashflow_capital_expenditure", "Number")->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_acquisitions"]					= $db->getField("cashflow_acquisitions", "Number")->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_investing"]			= $cashflow_from_investing->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_dividends_paid"]				= $db->getField("cashflow_dividends_paid", "Number")->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_share_issue"]					= $db->getField("cashflow_share_issue", "Number")->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_financing"]			= $cashflow_from_financing->getValue("Currency", FF_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["bold_total_cashflow"]					= $total_cashflow->getValue("Currency", FF_LOCALE);
				} else
				{
					$cash_flow[$ID_idea]["default"][$year]["cashflow_net_income"]					= $db->getField("net_income", "Number", true);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_depreciation_amortization"]	= $db->getField("cashflow_depreciation_amortization", "Number", true);
					$cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_operation"]			= $cashflow_from_operation->getValue("Number", FF_SYSTEM_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_capital_expenditure"]			= $db->getField("cashflow_capital_expenditure", "Number", true);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_acquisitions"]					= $db->getField("cashflow_acquisitions", "Number", true);
					$cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_investing"]			= $cashflow_from_investing->getValue("Number", FF_SYSTEM_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_dividends_paid"]				= $db->getField("cashflow_dividends_paid", "Number", true);
					$cash_flow[$ID_idea]["default"][$year]["cashflow_share_issue"]					= $db->getField("cashflow_share_issue", "Number", true);
					$cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_financing"]			= $cashflow_from_financing->getValue("Number", FF_SYSTEM_LOCALE);
					$cash_flow[$ID_idea]["default"][$year]["bold_total_cashflow"]					= $total_cashflow->getValue("Number", FF_SYSTEM_LOCALE);
				}
				
				$cash_flow[$ID_idea]["rev"]["cashflow_net_income"][$year]					= $cash_flow[$ID_idea]["default"][$year]["cashflow_net_income"];
				$cash_flow[$ID_idea]["rev"]["cashflow_depreciation_amortization"][$year]	= $cash_flow[$ID_idea]["default"][$year]["cashflow_depreciation_amortization"];
				$cash_flow[$ID_idea]["rev"]["bold_cashflow_from_operation"][$year]			= $cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_operation"];
				$cash_flow[$ID_idea]["rev"]["cashflow_capital_expenditure"][$year]			= $cash_flow[$ID_idea]["default"][$year]["cashflow_capital_expenditure"];
				$cash_flow[$ID_idea]["rev"]["cashflow_acquisitions"][$year]					= $cash_flow[$ID_idea]["default"][$year]["cashflow_acquisitions"];
				$cash_flow[$ID_idea]["rev"]["bold_cashflow_from_investing"][$year]			= $cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_investing"];
				$cash_flow[$ID_idea]["rev"]["cashflow_dividends_paid"][$year]				= $cash_flow[$ID_idea]["default"][$year]["cashflow_dividends_paid"];
				$cash_flow[$ID_idea]["rev"]["cashflow_share_issue"][$year]					= $cash_flow[$ID_idea]["default"][$year]["cashflow_share_issue"];
				$cash_flow[$ID_idea]["rev"]["bold_cashflow_from_financing"][$year]			= $cash_flow[$ID_idea]["default"][$year]["bold_cashflow_from_financing"];
				$cash_flow[$ID_idea]["rev"]["bold_total_cashflow"][$year]					= $cash_flow[$ID_idea]["default"][$year]["bold_total_cashflow"];
			} while($db->nextRecord());
	    }
	}
	if($rev) 
	{
		return $cash_flow[$ID_idea]["rev"];
	} else 
	{
		return $cash_flow[$ID_idea]["default"];	
	}
}  

//income statement
function mod_crowdfund_get_income_statement($ID_idea, $rev = false, $currency = true)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $income_statement = array();
    
    if(!array_key_exists($ID_idea, $income_statement)) 
    {  
	    //income statement data
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.*
		      FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement
		      WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.ID_idea = " . $db->toSql($ID_idea, "Number") . "
		      ORDER BY `year` ASC";
	    $db->query($sSQL);
	    if($db->nextRecord()) 
	    {
			do
			{
				$year = $db->getField("year", "Number", true);
				$gross_margin = new ffData($db->getField("total_revenue", "Number", true) - 
							$db->getField("cost_good_service", "Number", true), "Number"
						);
				$ebitda = new ffData($gross_margin->getValue("Number") -
							$db->getField("human_resource", "Number", true) -
							$db->getField("marketing_cost", "Number", true) -
							$db->getField("other_cost", "Number", true), "Number"
						);
				$ebit = new ffData($ebitda->getValue("Number") -
							$db->getField("depreciation_amortization", "Number", true), "Number"
						);
				$pre_tax_profit = new ffData($ebit->getValue("Number") -
							$db->getField("finantial_interest", "Number", true), "Number"
						);
				if($currency)
				{
					$income_statement[$ID_idea]["default"][$year]["total_revenue"] 				= $db->getField("total_revenue", "Number")->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["cost_good_service"] 			= $db->getField("cost_good_service", "Number")->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["bold_gross_margin"] 			= $gross_margin->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["human_resource"] 			= $db->getField("human_resource", "Number")->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["marketing_cost"] 			= $db->getField("marketing_cost", "Number")->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["other_cost"] 				= $db->getField("other_cost", "Number")->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["bold_EBITDA"] 				= $ebitda->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["depreciation_amortization"] 	= $db->getField("depreciation_amortization", "Number")->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["bold_EBIT"] 					= $ebit->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["finantial_interest"] 		= $db->getField("finantial_interest", "Number")->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["bold_pre_tax_profit"] 		= $pre_tax_profit->getValue("Currency", FF_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["net_income"] 				= $db->getField("net_income", "Number")->getValue("Currency", FF_LOCALE);
				} else
				{
					$income_statement[$ID_idea]["default"][$year]["total_revenue"] 				= $db->getField("total_revenue", "Number", true);
					$income_statement[$ID_idea]["default"][$year]["cost_good_service"] 			= $db->getField("cost_good_service", "Number", true);
					$income_statement[$ID_idea]["default"][$year]["bold_gross_margin"] 			= $gross_margin->getValue("Number", FF_SYSTEM_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["human_resource"] 			= $db->getField("human_resource", "Number", true);
					$income_statement[$ID_idea]["default"][$year]["marketing_cost"] 			= $db->getField("marketing_cost", "Number", true);
					$income_statement[$ID_idea]["default"][$year]["other_cost"] 				= $db->getField("other_cost", "Number", true);
					$income_statement[$ID_idea]["default"][$year]["bold_EBITDA"] 				= $ebitda->getValue("Number", FF_SYSTEM_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["depreciation_amortization"] 	= $db->getField("depreciation_amortization", "Number", true);
					$income_statement[$ID_idea]["default"][$year]["bold_EBIT"] 					= $ebit->getValue("Number", FF_SYSTEM_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["finantial_interest"] 		= $db->getField("finantial_interest", "Number", true);
					$income_statement[$ID_idea]["default"][$year]["bold_pre_tax_profit"] 		= $pre_tax_profit->getValue("Number", FF_SYSTEM_LOCALE);
					$income_statement[$ID_idea]["default"][$year]["net_income"] 				= $db->getField("net_income", "Number", true);
				}
				$income_statement[$ID_idea]["rev"]["total_revenue"][$year] 				= $income_statement[$ID_idea]["default"][$year]["total_revenue"];
				$income_statement[$ID_idea]["rev"]["cost_good_service"][$year] 			= $income_statement[$ID_idea]["default"][$year]["cost_good_service"];
				$income_statement[$ID_idea]["rev"]["bold_gross_margin"][$year] 			= $income_statement[$ID_idea]["default"][$year]["bold_gross_margin"];
				$income_statement[$ID_idea]["rev"]["human_resource"][$year] 			= $income_statement[$ID_idea]["default"][$year]["human_resource"];
				$income_statement[$ID_idea]["rev"]["marketing_cost"][$year] 			= $income_statement[$ID_idea]["default"][$year]["marketing_cost"];
				$income_statement[$ID_idea]["rev"]["other_cost"][$year] 				= $income_statement[$ID_idea]["default"][$year]["other_cost"];
				$income_statement[$ID_idea]["rev"]["bold_EBITDA"][$year] 				= $income_statement[$ID_idea]["default"][$year]["bold_EBITDA"];
				$income_statement[$ID_idea]["rev"]["depreciation_amortization"][$year] 	= $income_statement[$ID_idea]["default"][$year]["depreciation_amortization"];
				$income_statement[$ID_idea]["rev"]["bold_EBIT"][$year] 					= $income_statement[$ID_idea]["default"][$year]["bold_EBIT"];
				$income_statement[$ID_idea]["rev"]["finantial_interest"][$year] 		= $income_statement[$ID_idea]["default"][$year]["finantial_interest"];
				$income_statement[$ID_idea]["rev"]["bold_pre_tax_profit"][$year] 		= $income_statement[$ID_idea]["default"][$year]["bold_pre_tax_profit"];
				$income_statement[$ID_idea]["rev"]["net_income"][$year] 				= $income_statement[$ID_idea]["default"][$year]["net_income"];
			} while($db->nextRecord());
		}
	}
	if($rev) 
	{
		return $income_statement[$ID_idea]["rev"];
	} else 
	{
		return $income_statement[$ID_idea]["default"];	
	}
    
}  

function mod_crowdfund_get_team($ID_idea, $role = null, $service = false)
{
	$cm = cm::getInstance();
    $db = ffDB_Sql::factory();
	
	static $team = array();
	
	if(!array_key_exists($ID_idea, $team) || $service) 
    {  
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.*
					, anagraph.* 
					, IF(anagraph.billreference = ''
						, IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
							, CONCAT(anagraph.name, ' ', anagraph.surname)
							, " . CM_TABLE_PREFIX . "mod_security_users.username)
								, anagraph.billreference 
							) AS reference
					, (IFNULL (
						(SELECT anagraph_rel_nodes_fields.description FROM anagraph_rel_nodes_fields 
						WHERE anagraph_rel_nodes_fields.ID_fields = 3
						AND anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
						LIMIT 1)
						, 0)) AS value
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.name AS role_name
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.last_update
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
						INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_role = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_role 
						LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . $db->toSql($ID_idea, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_role_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
							. (strlen($role) == true
									? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team_rel_languages.role_name = " . $db->toSql($role, "Text") 
									: ""
						) . "
					ORDER BY " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_role
						, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.last_update";
		$db->query($sSQL); 
		if($db->nextRecord())  
		{
			do 
			{
				$ID_user_anagraph = $db->getField("ID", "Number", true); 
				$team[$ID_idea][$ID_user_anagraph]["public"] = $db->getField("public", "Number", true);
				$team[$ID_idea][$ID_user_anagraph]["member_avatar"] = $db->getField("avatar", "Text", true);
				$team[$ID_idea][$ID_user_anagraph]["member_reference"] = $db->getField("reference", "Text", true);
				$team[$ID_idea][$ID_user_anagraph]["member_email"] = $db->getField("email", "Text", true);
				$team[$ID_idea][$ID_user_anagraph]["slug"] = $db->getField("username_slug", "Text", true);
				$team[$ID_idea][$ID_user_anagraph]["role"] = $db->getField("role_name", "Text", true);
				$team[$ID_idea][$ID_user_anagraph]["linkedin"] = $db->getField("value", "Text", true);
				
			} while($db->nextRecord());
		}
	}
	return $team[$ID_idea];
}

//backers
function mod_crowdfund_get_backers($ID_idea, $owner = null, $ID_backer = false, $uid_backer = false, $timeline = false)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $backers = array();

    if((!array_key_exists($ID_idea, $backers) && !$timeline) || $timeline) {  
	    //backers data 
	    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.*
						, IF(anagraph.avatar = ''
							, " . CM_TABLE_PREFIX . "mod_security_users.avatar
							, anagraph.avatar
						) AS avatar
						, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID AS ID_backer
						, " . CM_TABLE_PREFIX . "mod_security_users.public AS public
						, " . CM_TABLE_PREFIX . "mod_security_users.username_slug AS slug
						, " . CM_TABLE_PREFIX . "mod_security_users.username AS username
						, IF(anagraph.email = '' 
							, " . CM_TABLE_PREFIX . "mod_security_users.email
							, anagraph.email
						) AS email
						, " . CM_TABLE_PREFIX . "mod_security_users.public AS public
			            , IF(anagraph.billreference = ''
			                , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
			                    , CONCAT(anagraph.name, ' ', anagraph.surname)
			                    , " . CM_TABLE_PREFIX . "mod_security_users.username
			                )
			                , anagraph.billreference 
			            ) AS reference
						, (" . ((global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT")) 
							? " IFNULL(	
										(
											SELECT ecommerce_documents_bill.ID 
											FROM ecommerce_documents_bill 
											WHERE ecommerce_documents_bill.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_bill
										)
										, 0		
									)
								"
							: "1"
						) . ") AS confirmed
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
						INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea
						LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") ."
							" . ($ID_backer == true
								? " AND anagraph.ID = " . $db->toSql($ID_backer, "Number")
								: ""
							) . "
							" . ($uid_backer == true
								? " AND anagraph.uid = " . $db->toSql($uid_backer, "Number")
								: ""
							) . "
							" . ($owner === true
								? " AND anagraph.uid = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private > 0" 
								: ($owner === false
									? " AND NOT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private > 0)"
									: ""
								)
							);
		$db->query($sSQL);
	    if($db->nextRecord()) 
	    {
	    	do 
			{
				if($timeline)
				{
					$last_update = $db->getField("last_update", "Number", true);
					$backers[$last_update]["type"] = "backer";
					$backers[$last_update]["name"] = $db->getField("reference", "Text", true);
					$backers[$last_update]["avatar"] = $db->getField("avatar", "Text", true);
					$backers[$last_update]["price"] = $db->getField("price", "Text", true);
					$backers[$last_update]["public"] = $db->getField("public", "Number", true);
					$backers[$last_update]["slug"] = $db->getField("slug", "Text", true);
					$backers[$last_update]["email"] = $db->getField("email", "Text", true);
					$backers[$last_update]["created"] = $db->getField("created", "Number", true);
					$backers[$last_update]["last_update"] = $db->getField("last_update", "Number", true);
					$backers[$last_update]["confirmed"] = $db->getField("confirmed", "Number", true);
				} else
				{
					$ID_backers = $db->getField("ID", "Number", true);
					$backers[$ID_idea][$ID_backers]["backer_avatar"] = $db->getField("avatar", "Text", true);
					$backers[$ID_idea][$ID_backers]["backer_reference"] = $db->getField("reference", "Text", true);
					$backers[$ID_idea][$ID_backers]["backer_public"] = $db->getField("public", "Number", true);
					$backers[$ID_idea][$ID_backers]["backer_slug"] = $db->getField("slug", "Text", true);
					$backers[$ID_idea][$ID_backers]["backer_email"] = $db->getField("email", "Text", true);
					$backers[$ID_idea][$ID_backers]["backer_price"] = $db->getField("price", "Number", true);
					$backers[$ID_idea][$ID_backers]["ID_reward"] = $db->getField("ID_reward", "Number", true);
					$backers[$ID_idea][$ID_backers]["ID_user_anagraph"] = $db->getField("ID_user_anagraph", "Number", true);
					$backers[$ID_idea][$ID_backers]["created"] = $db->getField("created", "Number", true);
					$backers[$ID_idea][$ID_backers]["last_update"] = $db->getField("last_update", "Number", true);
					$backers[$ID_idea][$ID_backers]["confirmed"] = $db->getField("confirmed", "Number", true);
				}
			} while($db->nextRecord());
	    }
	}	
	if ($timeline)
	{
		return $backers;
	} else
	{
		return $backers[$ID_idea];
	}
}

function mod_crowdfund_control_offert_limit($ID_idea, $ID_user_anagraph = false, $uid_backer = false)
{
	$control = false;
	if(global_settings("MOD_CROWDFUND_IDEA_LIMIT_OFFER"))
	{
		$offer = mod_crowdfund_get_backers($ID_idea, null, $ID_user_anagraph, $uid_backer);

		if(is_array($offer) && count($offer)>global_settings("MOD_CROWDFUND_IDEA_LIMIT_OFFER_NUMBER") - 1) 
		{
			$control = true;
		}
	}
	return $control;
}

//follower
function mod_crowdfund_get_follower($ID_idea, $timeline = null)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $followers = array();

    if(!array_key_exists($ID_idea, $followers) || $timeline === true) 
    {  
		//followers data
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.*
					, IF(anagraph.avatar = ''
						, " . CM_TABLE_PREFIX . "mod_security_users.avatar
						, anagraph.avatar
					) AS avatar
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID AS ID_follower
					, " . CM_TABLE_PREFIX . "mod_security_users.public AS public
					, " . CM_TABLE_PREFIX . "mod_security_users.username_slug AS slug
					, " . CM_TABLE_PREFIX . "mod_security_users.username AS username
					, IF(anagraph.email = '' 
						, " . CM_TABLE_PREFIX . "mod_security_users.email
						, anagraph.email
					) AS email
					, " . CM_TABLE_PREFIX . "mod_security_users.public AS public
		            , IF(anagraph.billreference = ''
		                , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
		                    , CONCAT(anagraph.name, ' ', anagraph.surname)
		                    , " . CM_TABLE_PREFIX . "mod_security_users.username
		                )
		                , anagraph.billreference
		            ) AS reference
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
					INNER JOIN anagraph ON anagraph.ID =  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_user_anagraph
					LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . $db->toSql($ID_idea, "Number");
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			if($timeline === true)
			{
				do
				{
					$last_update = $db->getField("last_update", "Number", true);
					$followers[$last_update]["type"] = "follow";
					$followers[$last_update]["name"] = $db->getField("reference", "Text", true);
					$followers[$last_update]["avatar"] = $db->getField("avatar", "Text", true);
					$followers[$last_update]["public"] = $db->getField("public", "Number", true);
					$followers[$last_update]["slug"] = $db->getField("slug", "Text", true);
					$followers[$last_update]["email"] = $db->getField("email", "Text", true);
				} while($db->nextRecord());
			} else
			{
				do
				{
					$followers_ID = $db->getField("ID_follower", "Number", true);
					$followers[$ID_idea][$followers_ID]["follower_avatar"] = $db->getField("avatar", "Text", true);
					$followers[$ID_idea][$followers_ID]["follower_reference"] = $db->getField("reference", "Text", true);
					$followers[$ID_idea][$followers_ID]["follower_public"] = $db->getField("public", "Number", true);
					$followers[$ID_idea][$followers_ID]["follower_slug"] = $db->getField("slug", "Text", true);
					$followers[$ID_idea][$followers_ID]["follower_email"] = $db->getField("email", "Text", true);
					$followers[$ID_idea][$followers_ID]["ID_user_anagraph"] = $db->getField("ID_user_anagraph", "Number", true);
				} while($db->nextRecord());
			}
		}
	}
	if ($timeline === true)
	{
		return $followers;
	} else
	{
		return $followers[$ID_idea]; 
	}
}

//qa
function mod_crowdfund_get_qa($ID_idea)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $qa = array();

    if(!array_key_exists($ID_idea, $qa)) 
    { 
		//qa data
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.*
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.ID AS qa_ID
					, anagraph.* 
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa
					INNER JOIN anagraph ON anagraph.ID =  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.ID_user_anagraph
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.ID_idea = " . $db->toSql($ID_idea, "Number") . "
					AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.visible = 1
					AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.answer <> ''";
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			do
			{
				$qa_ID = $db->getField("qa_ID", "Number", true);
				$qa[$ID_idea][$qa_ID]["avatar"] = $db->getField("avatar", "Text", true);
				$qa[$ID_idea][$qa_ID]["ID_user_anagraph"] = $db->getField("ID_user_anagraph", "Number", true);
				$qa[$ID_idea][$qa_ID]["name"] = $db->getField("name", "Text", true);
				$qa[$ID_idea][$qa_ID]["surname"] = $db->getField("surname", "Text", true);
				$qa[$ID_idea][$qa_ID]["username"] = $db->getField("slug", "Text", true);
				$qa[$ID_idea][$qa_ID]["email"] = $db->getField("email", "Text", true);
				$qa[$ID_idea][$qa_ID]["question"] = $db->getField("question", "Text", true);
				$qa[$ID_idea][$qa_ID]["answer"] = $db->getField("answer", "Text", true);
			} while($db->nextRecord());
		}
	}
	return $qa[$ID_idea];
}

//author
function mod_crowdfund_get_author($ID_author, $ID_idea_now = null, $number_project_year = null)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $author = array();  

    if(!array_key_exists($ID_author, $author))
    { 
        //author data
        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.*
	                , IF(" . CM_TABLE_PREFIX . "mod_security_users.billreference = ''
	                    , IF(CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, " . CM_TABLE_PREFIX . "mod_security_users.surname = '')
	                    	, " . CM_TABLE_PREFIX . "mod_security_users.username
	                    	, CONCAT(" . CM_TABLE_PREFIX . "mod_security_users.name, ' ', " . CM_TABLE_PREFIX . "mod_security_users.surname)
	                    )
	                    , " . CM_TABLE_PREFIX . "mod_security_users.billreference
	                ) AS reference
					
	            FROM " . CM_TABLE_PREFIX . "mod_security_users
		        WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($ID_author, "Number") ."
				ORDER BY " . CM_TABLE_PREFIX . "mod_security_users.ID";
		$db->query($sSQL);
        if($db->nextRecord()) 
        {
			$author[$ID_author]["avatar"] = $db->getField("avatar", "Text", true); 
			$author[$ID_author]["billaddress"] = $db->getField("billaddress", "Text", true);
			$author[$ID_author]["billcap"] = $db->getField("billcap", "Text", true);
			$author[$ID_author]["billcf"] = $db->getField("billcf", "Text", true);
			$author[$ID_author]["billpiva"] = $db->getField("billpiva", "Text", true);
			$author[$ID_author]["billprovince"] = $db->getField("billprovince", "Text", true);
			$author[$ID_author]["billreference"] = $db->getField("billreference", "Text", true);
			$author[$ID_author]["billstate"] = $db->getField("billstate", "Number", true);
			$author[$ID_author]["billtown"] = $db->getField("billtown", "Text", true);
			$author[$ID_author]["email"] = $db->getField("email", "Text", true);
			$author[$ID_author]["username"] = $db->getField("username", "Text", true);
			$author[$ID_author]["reference"] = $db->getField("reference", "Text", true);
			$author[$ID_author]["name"] = $db->getField("name", "Text", true);
			$author[$ID_author]["surname"] = $db->getField("surname", "Text", true);
			$author[$ID_author]["tel"] = $db->getField("tel", "Text", true);
			$author[$ID_author]["ID_languages"] = $db->getField("ID_languages", "Number", true);
			$author[$ID_author]["slug"] = $db->getField("username_slug", "Text", true);
			$author[$ID_author]["public"] = $db->getField("public", "Number", true);
			$author[$ID_author]["custom"] = array();
			
			$sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
						, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire AS expired
						, IF  (" . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire > 0
							, IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated > 0
								, (" . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire - ROUND((" . time() . " - " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated) / 86400, 0))
								, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.expire
							)
							, (" . global_settings("MOD_CROWDFUND_EXPIRATION") . " - " . global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY") . " - ROUND((" . time() . " - " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated) / 86400, 0)) 
						) AS expire
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql($ID_author, "Number") . "
						" . ($number_project_year === true
								? " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated >= " . $db->toSql(time() - 365 * 86400, "Number")
								: ""
						) . "
					GROUP BY " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url";  
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				$current_project = 0;
				$completed_project = 0;
				$total_project = 0;
				$current_project_active_now = 0;
				
				do
				{
					$ID = $db->getField("ID", "Number", true);
					$goal_current = $db->getField("goal_current", "Number", true);
					$goal = $db->getField("goal", "Number", true);
					$status = $db->getField("status", "Number", true);
					$expire = $db->getField("expire", "Number", true);
					$project_life = $db->getField("expired", "Number", true);
					$activation = $db->getField("activated", "Number", true);
					$status_visible_decision = $db->getField("status_visible_decision", "Number", true);
					
					if ($expire)
					{
						if($ID != $ID_idea_now)
						{
							if($project_life >= (time() - $activation)/86400 && $status_visible_decision)
							{
								$current_project_active_now++;
							}
						}
						if($status > 0)
						{
							$current_project++;
							
						}
					} else
					{
						if ($goal_current >= $goal)
						{
							$completed_project++;
						}
					}
					$total_project++;
				} while($db->nextRecord());
				
				$author[$ID_author]["current_project"]				= $current_project;
				$author[$ID_author]["completed_project"]			= $completed_project;
				$author[$ID_author]["total_project"]				= $total_project;
				$author[$ID_author]["current_project_active_now"]	= $current_project_active_now;
			}
			
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users_fields.*
					FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
					WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($ID_author, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				do
				{
					$social = $db->getField("field", "Text", true);
				    $author[$ID_author]["custom"][$social] = $db->getField("value", "Text", true);
				} while($db->nextRecord());
			
			}
        }
    }
	
	return $author[$ID_author];
}

//company
function mod_crowdfund_get_company($ID_company)
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();

    static $company = array();

    if(!array_key_exists($ID_company, $company)) 
    { 
		//$ID_company = 4;
        //company data
        $sSQL = "SELECT anagraph.*
					, " . FF_PREFIX . "loc_state.name AS billstate
				FROM anagraph
					INNER JOIN " . FF_PREFIX . "loc_state ON " . FF_PREFIX . "loc_state.ID = anagraph.billstate
				WHERE anagraph.ID = " . $db->toSql($ID_company, "Number") . "
				ORDER BY anagraph.ID";
	    $db->query($sSQL);
	    if($db->nextRecord()) 
	    {
			$company[$ID_company]["avatar"] = $db->getField("avatar", "Text", true);
			$company[$ID_company]["billaddress"] = $db->getField("billaddress", "Text", true);
			$company[$ID_company]["billcap"] = $db->getField("billcap", "Text", true);
			$company[$ID_company]["billcf"] = $db->getField("billcf", "Text", true);
			$company[$ID_company]["billpiva"] = $db->getField("billpiva", "Text", true);
			$company[$ID_company]["billprovince"] = $db->getField("billprovince", "Text", true);
			$company[$ID_company]["billreference"] = $db->getField("billreference", "Text", true);
			$company[$ID_company]["billstate"] = $db->getField("billstate", "Number", true);
			$company[$ID_company]["billtown"] = $db->getField("billtown", "Text", true);
			$company[$ID_company]["email"] = $db->getField("email", "Text", true);
			$company[$ID_company]["categories"] = $db->getField("categories", "Text", true);
			$company[$ID_company]["owner"] = $db->getField("owner", "Number", true);
			$company[$ID_company]["name"] = $db->getField("name", "Text", true);
			$company[$ID_company]["surname"] = $db->getField("surname", "Text", true);
			$company[$ID_company]["tel"] = $db->getField("tel", "Text", true);
			$company[$ID_company]["slug"] = $db->getField("slug", "Text", true);
			$company[$ID_company]["custom"] = array();

			$sSQL = "SELECT anagraph_rel_nodes_fields.*
						, anagraph_fields.name AS field_name
						FROM anagraph_rel_nodes_fields
							INNER JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields 
						WHERE anagraph_rel_nodes_fields.ID_nodes = " . $db->toSql($ID_company, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				do
				{
				    $company[$ID_company]["custom"][$db->getField("field_name", "Text", true)] = $db->getField("description", "Text", true);
				} while($db->nextRecord());
			}
        }
    }
	return $company[$ID_company];
}

function mod_crowdfund_get_idea_required($ID_idea, $override = null)  
{
    $cm = cm::getInstance();
	$idea_detail = mod_crowdfund_get_idea_basic($ID_idea, null, $override);
	$idea_businessplan = mod_crowdfund_get_businessplan($ID_idea, null, $override);
	
	$smart_url = $idea_detail["smart_url"];
	$idea_detail["is_innovative"]= !((bool) $idea_detail["is_innovative"] ^ (bool) $idea_detail["innovative_autocertification"]);
	$idea_detail["is_startup"]= (strlen($idea_detail["is_startup"]) ? true : false);
	
	
	if(global_settings("MOD_CROWDFUND_IDEA_COMPANY_INNOVATIVE"))
	{
		if($idea_detail["start_up_innovativa"])
		{
			if(strlen($idea_detail["autocertification"]))
			{
				$idea_detail["is_statup_innovative"] = true;
			} else
			{
				$idea_detail["is_statup_innovative"] = false;
			}
		}
	}
	
	if(global_settings("MOD_CROWDFUND_IDEA_COMPANY_VISURA"))
	{
		if(strlen($idea_detail["visura_PDF"]))
		{
			$idea_detail["is_company_enabled"] = true;
		} else
		{
			$idea_detail["is_company_enabled"] = false;
		}
	}
	
	if ($override === null)
	{
        $filename = cm_cascadeFindTemplate("/contents/required/index.html", "crowdfund");
	    /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/required/index.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/required/index.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("index.html", "main");

		if(strlen($_REQUEST["ret_url"])) {
			$ret_url = $_REQUEST["ret_url"];
		} else {
			$ret_url = FF_SITE_PATH . MOD_CROWDFUND_USER_PATH;
		}
		
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);
		$tpl->set_var("idea_base_path", $cm->oPage->site_path . ffcommon_dirname($cm->oPage->page_path));
		$tpl->set_var("ret_url", urlencode($ret_url));
		$tpl->set_var("user_path", MOD_CROWDFUND_USER_PATH);
		$tpl->set_var("smart_url", $smart_url);
	} 
	$idea_required = mod_crowdfund_get_idea_structure("idea");
	$count_pledge = mod_crowdfund_get_pledge($ID_idea);
	$count = 0;
	
	
	if(is_array($idea_required) && count($idea_required)) 
	{
		foreach ($idea_required as $idea_required_key => $idea_required_value) 
		{
			if ($override === null)
			{
				$tpl->set_var("item_name", $idea_required_key);
			}
			if(is_array($idea_required_value) && count($idea_required_value))
			{
				if	(
						array_key_exists("required", $idea_required_value) && 
						is_array($idea_required_value["required"]) && 
						count($idea_required_value["required"])
					) 
				{
					
					foreach($idea_required_value["required"] AS $submenu_key => $submenu_value) 
					{
						if ($submenu_value)
						{
							if (
									
									($submenu_key == "enable_reward" && 
										(!$idea_detail["enable_equity"] && !$idea_detail["enable_donation"] && 
											(!$idea_detail["enable_pledge"] || 
													!(is_array($count_pledge) && count($count_pledge))
											)
										)
									) || ($submenu_key == "company_is_innovative" && $idea_detail["start_up_innovativa"] && !$idea_detail["is_statup_innovative"])
									|| ($submenu_key == "company_visura" && !$idea_detail["is_company_enabled"])
									|| ($submenu_key != "enable_reward" && $submenu_key != "company_is_innovative" && $submenu_key != "company_visura" && !$idea_detail[$submenu_key] && !$idea_businessplan[$submenu_key])
									
								)
							{
								
								if ($override === null)
								{
									$tpl->set_var("item_label", ffTemplate::_get_word_by_code("crowdfund_" . $idea_required_value["prefix"] . $submenu_key));
									$tpl->parse("SezRequiredItem", true);
								}
								$count++;
							}
						}
					}
				}
			}
		}
		
	}
	if ($override === null)
	{
		if ($count)
		{
			$tpl->parse("SezRequired", false); 
		} else 
		{
			$tpl->set_var("SezRequired", "");
		}
		$buffer = $tpl->rpparse("main", false);
		
		return $buffer;
	} else
	{
		return $count;
	}
}

function mod_crowdfund_get_idea_suggest()
{
	$cm = cm::getInstance();
	$db = ffDB_Sql::factory();

	static $suggest = null;
	
	if($suggest === null)
	{
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help.field_name
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help
						INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages.ID_help = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help.ID
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_help_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") ;
		$db->query($sSQL); 
		if($db->nextRecord())
		{
			do
			{
				$help_name = $db->getField("field_name", "Text", true); 
				$suggest[$help_name] = $db->getField("description", "Text", true);
			} while($db->nextRecord());
		}
	}
	return $suggest;
}

function mod_crowdfund_process_help_hint ($field_name)
{
	$cm = cm::getInstance();
	
		
	$help = mod_crowdfund_get_idea_suggest();
	$hint = ffCommon_url_rewrite($field_name);
	$help_hint = str_replace("-", "_", $hint);
	if(is_array($help) && array_key_exists($help_hint, $help))
	{
		$cm->oPage->tplAddJs("jquery.plugins.qtip2");
		if(check_function("get_resource_cascading")) 
		{
            get_resource_cascading($cm->oPage, "/", "jquery.qtip.css", true, FF_THEME_DIR . "/library/plugins/jquery.qtip2");
        }
		
		return '<a class="hint" href="javascript:void(0);" rel = "/services/help/' . $help_hint . '"></a>';
	}
	return null;
}


function mod_crowdfund_get_required_field($struct, $type = null)
{
	static $required_field = null;
	if($required_field === null) 
	{
		if(!is_array($required_field) || !array_key_exists($type, $required_field)) 
		{
			switch($type) 
			{
				case "field":
					$required_field[$type] = array();
					foreach ($struct as $struct_key => $struct_value) 
					{
						foreach ($struct_value AS $struct_sub_key => $struct_sub_value)
						{
							if(is_array($struct_sub_value["required"])) 
							{
								$tmp_required = $struct_sub_value["required"];
								array_walk($tmp_required, "mod_crowdfund_get_required_field_walk", array($struct_sub_value["prefix"], $struct_sub_value["postfix"]));
								$required_field[$type] = array_merge($required_field[$type], $tmp_required);								
							}
						}
					}
					break;
				default:
					$required_field["menu"] = $struct;
			}
		}
	}
	if(strlen($type)) {
		return $required_field[$type]; 
	} else {
		return $required_field[$type]; 
	}
}

function mod_crowdfund_get_required_field_walk(&$value, $key, $params) 
{
	$value = array("required" => $value, "prefix" => $params[0], "postfix" => $params[1]);
	return $value;
}

function mod_crowdfund_get_idea_structure($type = null, $limit = array())
{
	static $menu = array();
	//Menu Basic
	if(!count($limit) || array_search("basic", $limit) !== false) {
		$menu_basic["category"] = false;
		$menu_basic_required["category"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CATEGORY");
		$menu_basic["cover"] = false;
		$menu_basic_required["cover"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_COVER");
		$menu_basic["video"] = false;
		$menu_basic_required["video"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_VIDEO");
		$menu_basic["website"] = false;
		$menu_basic_required["website"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_WEBSITE");
		$menu_basic["title"] = false;
		$menu_basic_required["title"] = true;
		$menu_basic["teaser"] = false;
		$menu_basic_required["teaser"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_TEASER");
		$menu_basic["description"] = false;
		$menu_basic_required["description"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_DESCRIPTION");
		
			

	    $menu["idea"]["basic"] = array 
				(
				"label" => ffTemplate::_get_word_by_code("crowdfund_idea_basic")
				, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_basic_description")
				, "prefix" => "idea_modify_"
				, "menu" => $menu_basic
				, "required" => $menu_basic_required
				  );
	}
    //Menu BusinessPlan
    if(!count($limit) || array_search("businessplan", $limit) !== false) {
	    $menu["idea"]["businessplan"] = array
			      ( 
					"label" => ffTemplate::_get_word_by_code("crowdfund_idea_businessplan")
					, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_businessplan_description")
					, "prefix" => "businessplan_detail_"
					, "menu" => array()
					, "required" => array
					(
						"description_product" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_DESCRIPTION_PRODUCT")
						, "target_market" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_TARGET_MARKET")
						, "commercial_strategy" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_COMMERCIAL_STRATEGY")
						, "sell_goal" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_SELL_GOAL")
						, "investment_description" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INVESTMENT_DESCRIPTION")
						, "weakness_strength" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_WEAKNESS_STRENGTH")
					  )
			  );
	}
    //Menu Estimate Budget
    if(!count($limit) || array_search("estimatebudget", $limit) !== false) {
    	$menu["idea"]["estimatebudget"] = array
				( 
				    "label" => ffTemplate::_get_word_by_code("crowdfund_idea_estimatebudget")
				    , "description" => ffTemplate::_get_word_by_code("crowdfund_idea_estimatebudget_description")
					, "postfix" => "_detail_title"
				    , "menu" => array
					      (
						"income_statement" => false
						, "cash_flow" => false
					      )
					, "required" => array 
					(
						"total_revenue" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_TOTAL_REVENUE")
						, "cost_good_service" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_COST_GOOD_SERVICE")
						, "human_resource" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_HUMAN_RESOURCE")
						, "marketing_cost" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_MARKETING_COST")
						, "other_cost" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_OTHER_COST")
						, "depreciation_amortization" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_DEPRECIATION_AMORTIZATION")
						, "finantial_interest" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_FINANCIAL_INTEREST")
						, "net_income" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_INCOME_STATEMENT_NET_INCOME")
						, "cashflow_depreciation_amortization" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CASH_FLOW_DEPRECIATION_AMORTIZATION")
						, "cashflow_capital_expenditure" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CASH_FLOW_CAPITAL_EXPENDITURE")
						, "cashflow_acquisitions" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CASH_FLOW_ACQUISITIONS")
						, "cashflow_dividends_paid" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CASH_FLOW_DIVIDENDS_PAID")
						, "cashflow_share_issue" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CASH_FLOW_SHARE_ISSUE")

					)
				);
	}
	
	//Menu Setting
	if(!count($limit) || array_search("setting", $limit) !== false) {
	    if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_INNOVATIVE"))
		{
			$menu_setting["is_innovative"] = false;
			$menu_setting_required["is_innovative"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_IS_INNOVATIVE");
		}
		$menu_setting["goal"] = false;
		$menu_setting_required["goal"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_GOAL");
		$menu_setting["currency"] = false;
		$menu_setting_required["currency"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CURRENCY");
		$menu_setting["languages"] = false;
		$menu_setting_required["languages"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_LANGUAGES");
		//$menu_setting["activated"] = false;
		//$menu_setting_required["activated"] = true;
		if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_SKYPE"))
		{
		$menu_setting["skype_account"] = false;
		$menu_setting_required["skype_account"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_SKYPE_ACCOUNT");
		}
		$menu_setting["anagraph_company"] = false;
		$menu_setting_required["anagraph_company"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_ANAGRAPH_COMPANY");
		if(global_settings("MOD_CROWDFUND_IDEA_COMPANY_INNOVATIVE"))
		{
			$menu_setting_required["company_is_innovative"] = true;
		}
		if(global_settings("MOD_CROWDFUND_IDEA_COMPANY_VISURA"))
		{
			$menu_setting_required["company_visura"] = true;
		}
    	$menu["idea"]["setting"] = array
			  (
				"label" => ffTemplate::_get_word_by_code("crowdfund_idea_setting")
				, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_setting_description")
				, "prefix" => "idea_modify_"
				, "menu" => $menu_setting
				, "required" => $menu_setting_required
			  );
	}
    
	if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY") || global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") || global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
    {
		//Menu Reward
		if(!count($limit) || array_search("reward", $limit) !== false) {
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY"))
				$menu_reward["equity"] = false;
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE"))
				$menu_reward["pledge"] = false;
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
				$menu_reward["donation"] = false;
			if(is_array($menu_reward))
				$menu_reward_required["enable_reward"] = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_REWARD");
			
			$menu["idea"]["reward"] = array 
				  (
					"label" => ffTemplate::_get_word_by_code("crowdfund_idea_equity")//ffTemplate::_get_word_by_code("crowdfund_idea_reward")
					, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_reward_description")
					, "prefix" => "label_"
					, "menu" => $menu_reward
					, "required" => $menu_reward_required
				  ); 
		} 
	}
				
	
	//Menu First Offer
	if(!count($limit) || array_search("owner", $limit) !== false) {
    	$menu["idea"]["owner"] = array
			  (
				"label" => ffTemplate::_get_word_by_code("crowdfund_first_offer")
				, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_first_offer_description")
				, "prefix" => ""
				, "menu" => array 
							(
								"first_offer" => false
							)
				, "required" => array 
								(
									"first_offer" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_FIRST_OFFER")
								)
			  );
	}
	

    //Menu Attach
    if(!count($limit) || array_search("attach", $limit) !== false) {
    	$menu["idea"]["attach"] = array
			  (
				"label" => ffTemplate::_get_word_by_code("crowdfund_idea_attach")
				, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_attach_description")
				, "prefix" => ""
				, "menu" => false
				, "required" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_ATTACH")
			  );
	}
	
	if(global_settings("MOD_CROWDFUND_ENABLE_TEAM"))
	{
		if(!count($limit) || array_search("team", $limit) !== false) {
			$menu["idea"]["team"] = array
			  (
				"label" => ffTemplate::_get_word_by_code("crowdfund_idea_team")
				, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_team_description")
				, "prefix" => ""
				, "menu" => false
	//			, "required" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_ATTACH")
			  );
		}
	}
	
	if(global_settings("MOD_CROWDFUND_ENABLE_TIMELINE")) //ID_idea == 88
	{
		if(!count($limit) || array_search("timeline", $limit) !== false) {
			$menu["idea"]["timeline"] = array
			  (
				"label" => ffTemplate::_get_word_by_code("crowdfund_idea_timeline")
				, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_timeline_description")
				, "prefix" => ""
				, "menu" => false
	//			, "required" => global_settings("MOD_CROWDFUND_IDEA_REQUIRE_ATTACH")
			  );
		}
	}

	if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_QUESTION"))
	{
		//Menu Question and Answer
		if(!count($limit) || array_search("qa", $limit) !== false) {
			$menu["feedback"]["qa"] = array
				  (
					  "label" => ffTemplate::_get_word_by_code("crowdfund_idea_qa")
					  , "description" => ffTemplate::_get_word_by_code("crowdfund_idea_qa_description")
				  );
		}
	}

    if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY") || global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") || global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
    {
		//Menu Backers
		if(!count($limit) || array_search("backers", $limit) !== false) {
			$menu["feedback"]["backers"] = array
					  (   
					"label" => ffTemplate::_get_word_by_code("crowdfund_idea_backers")
					, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_backers_description")
					  );
		}
	}
	if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_FOLLOWER"))
	{
		//Menu Followers
		if(!count($limit) || array_search("followers", $limit) !== false) {
			$menu["feedback"]["followers"] = array
					  (
					"label" => ffTemplate::_get_word_by_code("crowdfund_idea_followers")
					, "description" => ffTemplate::_get_word_by_code("crowdfund_idea_followers_description")
					  );
		}
	}
	
	if ($type === null)
		$res = $menu;
	elseif(array_key_exists($type, $menu)) {
		$res = $menu[$type];
	} else {
		$res = mod_crowdfund_get_required_field($menu, $type);
	}
	
	return $res;
}


function mod_crowdfund_get_menu_backer($smart_url, $type) 
{
	$cm = cm::getInstance();
	$i=0;
	if(is_array($type) && count($type) > 1) {
        $filename = cm_cascadeFindTemplate("/contents/backer/menu.html", "crowdfund");
	    /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/backer/menu.html", $cm->oPage->theme, false);
	    if ($filename === null)
	        $filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/backer/menu.html", $cm->oPage->theme);*/

	    $tpl = ffTemplate::factory(ffCommon_dirname($filename));
	    $tpl->load_file("menu.html", "main");

	    $tpl->set_var("theme", $cm->oPage->theme);
	    $tpl->set_var("site_path", $cm->oPage->site_path);
	    $tpl->set_var("idea_base_path", $cm->oPage->site_path . ffcommon_dirname($cm->oPage->page_path));
	    $tpl->set_var("ret_url", urlencode($_REQUEST["ret_url"]));

		$arrQueryString = array();

		if($_REQUEST["keys"]["ID"] > 0) {
			$arrQueryString[] = "keys[ID]=" . urlencode($_REQUEST["keys"]["ID"]);
		}
		if(isset($_REQUEST["force"])) {
			$arrQueryString[] = "force=" . urlencode($_REQUEST["force"]);
		}

		if($_REQUEST["price"] > 0) {
			$arrQueryString[] = "price=" . urlencode($_REQUEST["price"]);
		}
		if($_REQUEST["ret_url"] > 0) {
			$arrQueryString[] = "ret_url=" . urlencode($_REQUEST["ret_url"]);
		}

		foreach($type["menu"] AS $menu_key => $menu_value) {
			if($menu_value["status"]) {
				$i++;
				$tpl->set_var("type_payment_name", ffTemplate::_get_word_by_code("crowdfund_backer_menu_" . $menu_key));

				if($type["selected"]["name"] == $menu_key) {
					$tpl->set_var("SezBackerTypePaymentLink", "");
					$tpl->parse("SezBackerTypePaymentNoLink", false);
				} else {
					if($cm->oPage->isXHR() && strlen($_REQUEST["XHR_DIALOG_ID"])) {
						$tpl->set_var("type_payment_url", "javascript:ff.ffPage.dialog.goToUrl('" . $_REQUEST["XHR_DIALOG_ID"] . "', '" . $menu_value["url"] . "/" . $smart_url . (count($arrQueryString) ? "?" . implode("&", $arrQueryString) : "") . "');");
					} else {
						$tpl->set_var("type_payment_url", $menu_value["url"] . "/" . $smart_url . (count($arrQueryString) ? "?" . implode("&", $arrQueryString) : ""));	
					}

					$tpl->parse("SezBackerTypePaymentLink", false);
					$tpl->set_var("SezBackerTypePaymentNoLink", "");
				}
				$tpl->parse("SezBackerTypePayment", true);
			}
		}
		if($i>1)
			return $tpl->rpparse("main", false);
		else
			return null;
	}
}

function mod_crowdfund_idea_show_tab($smart_url, $value) 
{
	$menu = mod_crowdfund_get_menu_idea($smart_url, false);
	switch($value) {
		case "businessplan":
			if ($menu["idea"]["businessplan"]["menu"]["description_product"] ||
				$menu["idea"]["businessplan"]["menu"]["target_market"] ||
				$menu["idea"]["businessplan"]["menu"]["commercial_strategy"] ||
				$menu["idea"]["businessplan"]["menu"]["sell_goal"] ||
				$menu["idea"]["businessplan"]["menu"]["investment_description"] ||
				$menu["idea"]["businessplan"]["menu"]["weakness_strength"]	
				)
			{
				$res = true;
			}
			break;
		case "incomestatement":
			if ($menu["idea"]["estimatebudget"]["menu"]["income_statement"])
			{
				$res = true;
			}
			break; 
		case "cashflow":
			if ($menu["idea"]["estimatebudget"]["menu"]["cash_flow"])
			{
				$res = true;
			}
			break;
		case "attach":
			if ($menu["idea"]["attach"]["menu"])
			{
				$res = true;
			}
			break;
		default:
	}

	return $res;
}

function mod_crowdfund_get_menu_idea($smart_url, $tpl = null, $limit = array()) 
{
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();
    

    //menu
    static $menu = array();
    
    if(!count($menu)) {
	    $sSQL = "SELECT " . FF_PREFIX . "languages.*
					," . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
				FROM " . FF_PREFIX . "languages
					INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea ON FIND_IN_SET(" . FF_PREFIX . "languages.ID, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages)
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql($smart_url);
	    $db->query($sSQL);
	    if($db->nextRecord())  
	    {
	        $ID_idea = $db->getField("ID", "Number", true);
	        $allowed_lang = array();
	        do 
	        {
				$ID_language = $db->getField("ID", "Number", true);
				$language_code = $db->getField("code", "Text", true);
				$allowed_lang[$ID_language] = $language_code;
				$enable_equity = $db->getField("enable_equity", "Number", true);
				$enable_pledge = $db->getField("enable_pledge", "Number", true);
				$enable_donation = $db->getField("enable_donation", "Number", true);
				
	        } while($db->nextRecord());
	    }
	    
	    $menu = mod_crowdfund_get_idea_structure(null, $limit);
		
	    //BusinessPlan data Lang 
	    if(array_key_exists("businessplan", $menu["idea"])) {
		    $businessplan = mod_crowdfund_get_businessplan($ID_idea);
		    $menu["idea"]["businessplan"]["menu"]["description_product"]	= (bool) strip_tags($businessplan["description_product"]);
		    $menu["idea"]["businessplan"]["menu"]["target_market"]			= (bool) strip_tags($businessplan["target_market"]);
		    $menu["idea"]["businessplan"]["menu"]["commercial_strategy"]	= (bool) strip_tags($businessplan["commercial_strategy"]);
		    $menu["idea"]["businessplan"]["menu"]["sell_goal"]				= (bool) strip_tags($businessplan["sell_goal"]);
		    $menu["idea"]["businessplan"]["menu"]["investment_description"]	= (bool) strip_tags($businessplan["investment_description"]);
		    $menu["idea"]["businessplan"]["menu"]["weakness_strength"]		= (bool) strip_tags($businessplan["weakness_strength"]);
		}
				
	    //Idea data Lang
	    if(array_key_exists("basic", $menu["idea"]) || array_key_exists("setting", $menu["idea"]))
		    $idea_basic = mod_crowdfund_get_idea_basic($ID_idea);
		    
		if(array_key_exists("basic", $menu["idea"])) {
		    $menu["idea"]["basic"]["menu"]["cover"]					= (bool) $idea_basic["cover"];
		    $menu["idea"]["basic"]["menu"]["category"]				= (bool) $idea_basic["category"];
		    $menu["idea"]["basic"]["menu"]["video"]					= (bool) $idea_basic["video"];
		    $menu["idea"]["basic"]["menu"]["website"]				= (bool) $idea_basic["website"];

		    $menu["idea"]["basic"]["menu"]["teaser"]				= (bool) $idea_basic["teaser"];
		    $menu["idea"]["basic"]["menu"]["description"]			= (bool) $idea_basic["description"];
			$menu["idea"]["basic"]["menu"]["title"]					= (bool) $idea_basic["title"];
		}
		//setting
		if(array_key_exists("setting", $menu["idea"])) {
		    $menu["idea"]["setting"]["menu"]["goal"]				= (bool) ((int)$idea_basic["goal"]);
		    $menu["idea"]["setting"]["menu"]["goal_step"]			= (bool) $idea_basic["goal_step"];
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_SKYPE"))
			{
				$menu["idea"]["setting"]["menu"]["skype_account"]		= (bool) $idea_basic["skype_account"];
			}
			$menu["idea"]["setting"]["menu"]["languages"]			= (bool) $idea_basic["languages"];
		    $menu["idea"]["setting"]["menu"]["anagraph_company"]	= (bool) $idea_basic["anagraph_company"];
			$menu["idea"]["setting"]["menu"]["currency"]			= (bool) $idea_basic["currency"];

		    $is_innovative											= (bool) $idea_basic["is_innovative"];
		    $innovative_autocertification							= (bool) $idea_basic["innovative_autocertification"];
		    
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_INNOVATIVE") && !($innovative_autocertification ^ $is_innovative))
			{
				$menu["idea"]["setting"]["menu"]["is_innovative"]	= true;
			} 

		}
		//reward
		if(array_key_exists("reward", $menu["idea"])) {
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY") && $enable_equity)
			{
				$menu["idea"]["reward"]["menu"]["equity"]				= true;
			} elseif (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY") && !$enable_equity) 
			{
				$menu["idea"]["reward"]["menu"]["equity"]				= false;
			}

			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION") && $enable_donation)
			{
				$menu["idea"]["reward"]["menu"]["donation"]			= (bool) $idea_basic["enable_donation"];
			}

		    //Pledge data
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") && $enable_pledge)
			{
				$pledge = mod_crowdfund_get_pledge($ID_idea);
				$menu["idea"]["reward"]["menu"]["pledge"] = (bool) count($pledge);
			} elseif (global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") && !$enable_pledge) 
			{
				$menu["idea"]["reward"]["menu"]["pledge"]				= false;
			}
		}
	    
	        
	    
	    
	    //Attach data 
	    if(array_key_exists("attach", $menu["idea"])) {
		    $attach = mod_crowdfund_get_attach($ID_idea);
		    $menu["idea"]["attach"]["menu"] = count($attach);
		}
		
		//First Offer
		if(array_key_exists("owner", $menu["idea"])) {
			$first_backer = mod_crowdfund_get_backers($ID_idea, true);
		    $menu["idea"]["owner"]["menu"]["first_offer"] = (is_array($first_backer) && count($first_backer));
		}

		//cash flow
		if(array_key_exists("estimatebudget", $menu["idea"])) {
		    $cash_flow = mod_crowdfund_get_cash_flow($ID_idea);

			$count_cash_flow = 0;
		    if(is_array($cash_flow) && count($cash_flow)) 
			{
				foreach($cash_flow AS $cash_flow_year => $cash_flow_value) 
				{
					if(is_array($cash_flow_value) && count($cash_flow_value)) 
					{
						foreach($cash_flow_value AS $cash_flow_year_res) {
							if($cash_flow_year_res > 0) {
								$count_cash_flow++;
								break;
							}
						}
					}
					if($count_cash_flow) {
						break;
					}
				}
		    }

		    $menu["idea"]["estimatebudget"]["menu"]["cash_flow"] = (bool) $count_cash_flow;
		
		    //income statement
		    $income_statement = mod_crowdfund_get_income_statement($ID_idea);
			$count_income_statement = 0;
		    if(is_array($income_statement) && count($income_statement)) 
			{
				foreach($income_statement AS $income_statement_year => $income_statement_value) 
				{
					if(is_array($income_statement_value) && count($income_statement_value)) 
					{
						foreach($income_statement_value AS $income_statement_year_res) 
						{
							if($income_statement_year_res > 0) 
							{
								$count_income_statement++;
								break;
							}
						}
					}
					if($count_income_statement)
					break;
				}
		    }

		    $menu["idea"]["estimatebudget"]["menu"]["income_statement"] = (bool) $count_income_statement;
		}
	}

	if($tpl !== false) {
		if(strlen($tpl))
			$tpl_name = $tpl;
		else 
			$tpl_name = "menu";

        $filename = cm_cascadeFindTemplate("/contents/idea/" . $tpl_name . ".html", "crowdfund");
	    /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/idea/" . $tpl_name . ".html", $cm->oPage->theme, false);
	    if ($filename === null)
	        $filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/idea/" . $tpl_name . ".html", $cm->oPage->theme);*/

	    $tpl = ffTemplate::factory(ffCommon_dirname($filename));
	    $tpl->load_file($tpl_name . ".html", "main");

	    $tpl->set_var("theme", $cm->oPage->theme);
	    $tpl->set_var("site_path", $cm->oPage->site_path);
	    $tpl->set_var("idea_base_path", $cm->oPage->site_path . ffcommon_dirname($cm->oPage->page_path));
	    $tpl->set_var("smart_url", $smart_url);
	    $tpl->set_var("ret_url", urlencode($_REQUEST["ret_url"]));


	    if(is_array($menu) && count($menu)) 
	    {
	        foreach($menu AS $type_key => $type_value) 
	        {
        		$sez_type = ucfirst($type_key);
        		if(is_array($type_value) && count($type_value))
				{
					$count_menu_item = 1;
			        foreach($type_value AS $menu_key => $menu_value) 
			        {
		        		$tpl->set_var("Sez" . $sez_type . "SubMenu", "");
		        		$tpl->set_var("item_count", "");
		        		$tpl->set_var("item_status", "number");
						$tpl->set_var("item_index", $count_menu_item++);
						$prefix = $menu_value["prefix"];
						$postfix = $menu_value["postfix"];
						
						if(array_key_exists("menu", $menu_value)) {
							if(is_array($menu_value["menu"])) {
								if(count($menu_value["menu"])) {
									$count_item_done = 0;
									foreach($menu_value["menu"] AS $submenu_key => $submenu_value) {
										if($submenu_value)
											$count_item_done++;

										if(basename($cm->oPage->page_path) != $menu_key)
											continue;
										
										if($submenu_value)
											$tpl->set_var("item_class", "done");
										else
											$tpl->set_var("item_class", "circle");

										$tpl->set_var("item_url", "javascript:void(0);");
										
										if ($submenu_key == "title")
											$tpl->set_var("item_label", ffTemplate::_get_word_by_code("crowdfund_idea_detail_" . $submenu_key));
										else
										{
											$tpl->set_var("item_label", ffTemplate::_get_word_by_code("crowdfund_" . $prefix . $submenu_key . $postfix)); 
										}
										$tpl->parse("Sez" . $sez_type . "SubMenuItem", true);
									}

									$tpl->set_var("item_count", $count_item_done . " / " . count($menu_value["menu"]));

									if($count_item_done == count($menu_value["menu"])) {
										$tpl->set_var("item_status", "done");
										$tpl->set_var("item_index", "");
									}
									
									if(basename($cm->oPage->page_path) == $menu_key) 
										$tpl->parse("Sez" . $sez_type . "SubMenu", false);
								}
							} else {
								if(strlen($menu_value["menu"])) {
									$tpl->set_var("item_count", $menu_value["menu"]);
								}
							}
						}
						
						if(basename($cm->oPage->page_path) == $menu_key) 
						{
							$tpl->set_var("item_class", "selected");
						} 
						else 
						{
							$tpl->set_var("item_class", "item");
						}
						
						$process_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "?ret_url=" . urlencode($cm->oPage->site_path . ffcommon_dirname($cm->oPage->page_path) . "/" . $menu_key . "/" . $smart_url . "?ret_url=" . urlencode($_REQUEST["ret_url"]));
						//{idea_base_path}/{item_name}/{smart_url}?ret_url={ret_url}
						$tpl->set_var("idea_modify_action", "jQuery(this).closest('.login').css({'opacity': 0.5, 'pointer-events': 'none'}); ff.pluginLoad('ff.ajax', '/themes/library/ff/ajax.js', function(){ ff.ajax.doRequest({
											'action' : 'IdeaModify_update'
											, 'formName'	: 'IdeaModify'
											, 'injectid'    : 'IdeaModify'
											, 'url'			: '" . $_SERVER["REQUEST_URI"] . "'
										});
									});");
								
								
								//"jQuery(this).closest('form').action = '" . $process_url . "'; document.getElementById('frmAction').value = 'IdeaModify_update'; jQuery(this).closest('form')..submit();");
						$tpl->set_var("item_name", $menu_key);
						$tpl->set_var("item_label", $menu_value["label"]);
						$tpl->set_var("item_description", $menu_value["description"]);
						
						$tpl->parse("Sez" . $sez_type . "MenuItem", true);
					}

					$tpl->parse("Sez" . $sez_type . "Menu", false);
				}
	        }
	    }
	    $tpl_menu = $tpl->rpparse("main", false);

	    return $tpl_menu;
	}

	return $menu;
} 

function mod_crowdfund_update_goal($ID_idea, $update = false) {
	$db = ffDB_Sql::factory();
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.price
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers 
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
					" . (global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") 
						?  (global_settings("MOD_CROWDFUND_IDEA_CONSIDER_ALL_OFFER")
							? ""
							: " AND (" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.is_private > 0
								OR IFNULL(	
											(
												SELECT ecommerce_documents_bill.ID 
												FROM ecommerce_documents_bill 
												WHERE ecommerce_documents_bill.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_bill
											)
											, 0		
										) > 0
							)"
						)
						: ""
					);
    $db->query($sSQL);
    if($db->nextRecord()) 
	{
       	$goal_current = 0;
		do 
		{
			$goal_current += $db->getField("price", "Number", True);
		} while ($db->nextRecord()); 
		
    }
    
    if($update) {
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
					" . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_current = " . $db->toSql($goal_current, "Number")."
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal - " . $db->toSql($goal_current, "Number")."
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number");
		$db->execute($sSQL);
    }
     
    return $goal_current;
}

function Idea_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    $UserNID = get_session("UserNID");
	
	
    if(strlen($action)) {
		if($component->user_vars["ID_idea"] > 0) {
			$ID_idea = $component->user_vars["ID_idea"];
		} else {
			$ID_idea = $component->key_fields["ID"]->getValue();
		}

		$goal_current = mod_crowdfund_update_goal($ID_idea);

		$idea = mod_crowdfund_get_idea_required($ID_idea, (is_array($component->detail["IdeaDetail"]->recordset[0]) ? $component->detail["IdeaDetail"]->recordset[0] : (is_array($component->detail["BusinessPlanDetail"]->recordset[0]) ? $component->detail["BusinessPlanDetail"]->recordset[0] : false)));
		
		$status = 1;
		
		if ($idea)
		{
			$status = 0; 
		}
		
		
		
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
					" . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_current = " . $db->toSql($goal_current, "Number")."
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal - " . $db->toSql($goal_current, "Number")."
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status = " . $db->toSql($status, "Number")."
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number");
		$db->execute($sSQL);
	}
	
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.*
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
					INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID 
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number") . "
					AND  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") ;
					
	$db->query($sSQL);
	if($db->nextRecord()) 
	{
		$title = $db->getField("title", "Text", true);
		$teaser = $db->getField("teaser", "Text", true);
		$smart_url = $db->getField("smart_url", "Text", true);
		$video = $db->getField("video", "Text", true); //$component->form_fields["video"]->value->getValue("Text");//
		$owner = $db->getField("owner", "Number", true);
		$is_showed = ($db->getField("status_by_admin", "Number", true) &&
						$db->getField("activated", "Number", true) &&
						$db->getField("status_visible_decision", "Number", true) &&
						$db->getField("status", "Number", true));
		//echo $video;  
		if (strlen($video))  
		{
			if (strpos($video, 'youtu'))
			{
				if(strpos($video, 'iframe width'))
				{
					$video_correct = $video; 
				} else
				{
					if(strpos($video, "youtube.com/watch?")) 
					{
						$video_url = substr($video, strpos($video, "=")+1);
					} elseif(strpos($video, "youtu.be"))
					{
						$video_url = substr($video, strpos($video, ".be")+4);
					}
					$video_correct = '<iframe width="560" height="315" src="http://www.youtube.com/embed/' . $video_url . '" frameborder="0" allowfullscreen></iframe>';
				}
			} elseif (strpos($video, "vimeo.com"))
			{ 
				if(strpos($video, 'iframe')) 
				{
					$video_correct = substr($video, 0, strpos($video, "/iframe")+8);  
				} elseif(strpos($video, '//vimeo.com/'))
				{
					$video_url = substr($video, strpos($video, ".com")+5);
					$video_correct = '<iframe src="http://player.vimeo.com/video/' . $video_url . '" width="500" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
				}
			} else
			{
				$component->tplDisplayerror(ffTemplate::_get_word_by_code("crowdfund_host_video_unknown"));
				return true;
			}
			
			$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
						" . CM_TABLE_PREFIX . "mod_crowdfund_idea.video = " . $db->toSql($video_correct, "Text") . "
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->tosql($ID_idea, "Number");
			$db->execute($sSQL);
			
			if($is_showed) 
			{
				if(check_function("system_lib_facebook")) 
				{
					$sSQL = "SELECT anagraph.*
							FROM anagraph
							WHERE anagraph.uid = " . $db->toSql($owner, "Number");
					$db->query($sSQL);
					if($db->nextRecord()) 
					{
						$crowdfund_public_path = mod_crowdfund_get_path_by_lang("public");
						$complete_name = $db->getField("name", "Text", true) . " " . $db->getField("surname", "Text", true);
						$res = facebook_publish(ffTemplate::_get_word_by_code("crowdfund_facebook_project") . " " . $title . " " . ffTemplate::_get_word_by_code("crowdfund_facebook_of") . " " . $complete_name
						, DOMAIN_INSET . FF_SITE_PATH . $crowdfund_public_path . "/" . $smart_url
						, "http://" . DOMAIN_INSET . FF_SITE_PATH . CM_SHOWFILES . "/crowdfundme-social" . "/images/spacer.gif"
						, $title
						, "" 
						, $teaser
						, array()//array("name" => CM_LOCAL_APP_NAME, "link" => "http://" . DOMAIN_INSET)	
						, "" // place serve read_stream ...ma non serve
						, "" // spazio per raccogliere le persone citate
						, "{'value':'EVERYONE'}"//funzionano solo self e fiends.. serve read_stream per {'value':'EVERYONE'} e {'value':'ALL_FRIENDS'} e {'value':'FRIENDS_OF_FRIENDS'}
						); 
					}

				}
				$res = array("class" => "new_project", "label" => ffTemplate::_get_word_by_code("crowdfund_label_new_project"));
			}
		}
	}
}

function mod_crowdfund_process_chart($smart_url, $target_class = "chart", $estimatebudget = null)
{
	$cm = cm::getInstance();
	if(strlen($smart_url)) {
        $filename = cm_cascadeFindTemplate("/contents/idea/chart.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/idea/chart.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/idea/chart.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));  
		$tpl->load_file("chart.html", "main");

		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);
		$tpl->set_var("ret_url", urlencode($_REQUEST["ret_url"]));
		$tpl->set_var("smart_url", $smart_url);
		$tpl->set_var("target_class", $target_class);
		$tpl->set_var("decision_chart", global_settings("MOD_CROWDFUND_ENABLE_HIGH_CHART")); 
		
		if(!global_settings("MOD_CROWDFUND_ENABLE_HIGH_CHART"))   
		{
			$tpl->set_var("only_positive_value", global_settings("MOD_CROWDFUND_GRAPH_CONSIDER_ONLY_POSITIVE"));
			$tpl->set_var("graph_coloumn_decision", global_settings("MOD_CROWDFUND_GRAPH_COST_SUM"));  
			if($estimatebudget)  
			{
				$tpl->set_var("estimate_budget", true);
			} else
			{
				$tpl->set_var("estimate_budget", false);
			}
		}
		
		$tpl->parse("SezChartPreview", false);
		
		if(global_settings("MOD_CROWDFUND_ENABLE_HIGH_CHART")) 
		{
			$cm->oPage->tplAddJs("HighChart"
                , array(
                    "file" => "highcharts.js"
                    , "path" => "http://code.highcharts.com"
            ));
			$cm->oPage->tplAddJs("Exporting"
                , array(
                    "file" => "exporting.js"
                    , "path" => "http://code.highcharts.com/modules"
            ));
			$cm->oPage->tplAddJs("ff.crowdfund.highChart"
                , array(
                    "file" => "chart.js"
                    , "path" => "/modules/crowdfund/themes/javascript"
            ));
		} else
		{
			$cm->oPage->tplAddJs("AmCharts"
                , array(
                    "file" => "amcharts.js"
                    , "path" => "http://www.amcharts.com/lib"
            ));
			$cm->oPage->tplAddJs("ff.crowdfund.chart"
                , array(
                    "file" => "chart.js"
                    , "path" => "/modules/crowdfund/themes/javascript"
            ));
		}
		$buffer = $tpl->rpparse("main", false);
	} else {
		$buffer = "";
	}
	return $buffer;
}

function mod_crowdfund_process_idea($smart_url, $tpl_path, $params = array(), $dashboard = null) {
	$cm = cm::getInstance();
	$db = ffDB_Sql::factory();
	$globals = ffGlobals::getInstance("gallery");
	$UserNID = get_session("UserNID");
	$UserID = get_session("UserID");
	$cm->oPage->tplAddJs("jquery.plugins.qtip2");

	$decide_businessplan = $decide_estimate_budget =
	$decide_attach = $decide_backer_follower = $decide_qa = 1;
	
	
	$key = array("businessplan", "estimate_budget", "attach", "backer_follower", "qa");
	
	if ($UserID == MOD_SEC_GUEST_USER_NAME)
	{
		foreach ($key as $value) 
		{ 
			$value_name = strtoupper($value);
			if ((global_settings("MOD_CROWDFUND_IDEA_" . $value_name . "_DECISION_NOT_VIEW")) == false) 
			{
				${"decide_" . $value} = 0;
			}
		}
		
	} 
	
	
	$idea_title = (isset($params["title"])
							? $params["title"]
							: "idea"
						);
	
	if(strlen($smart_url)) {
		$ico_mode = (isset($params["ico_mode"])
							? $params["ico_mode"]
							: global_settings("MOD_CROWDFUND_DETAIL_ICO")
						);
						
		$is_detail = true;
	  	$idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url), null, false, true, $dashboard);
		$tmp_idea = current($idea);
		
		$ID_idea = $tmp_idea["ID"];
		
	} else {
		$prefix_pagenav = "ideapage";
		$enable_nav = (isset($params["enable_nav"])
							? $params["enable_nav"]
							: false
						);
		$rec_per_page = (isset($params["rec_per_page"])
							? $params["rec_per_page"]
							: global_settings("MOD_CROWDFUND_IDEA_REC_PER_PAGE")
						);
		$page =	(intval($_REQUEST[$prefix_pagenav . "_" . "page"]) > 0
				            ? $_REQUEST[$prefix_pagenav . "_" . "page"]
				            : 1
				        );
		$ico_mode = (isset($params["ico_mode"])
							? $params["ico_mode"]
							: global_settings("MOD_CROWDFUND_THUMB_ICO")
						);
		$max_col = (isset($params["col"])
							? $params["col"]
							: global_settings("MOD_CROWDFUND_IDEA_COL")
						);
								
		$is_detail = false;

		if($enable_nav) {
			$page_nav = ffPageNavigator::factory($cm->oPage, FF_DISK_PATH, FF_SITE_PATH, null, $cm->oPage->theme);
			$page_nav->oPage = array(&$cm->oPage);
			$page_nav->id = $prefix_pagenav;
			if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
				$page_nav->doAjax = true;
			else
				$page_nav->doAjax = false;

  			$page_nav->PagePerFrame = 6;

			$page_nav->prefix = $page_nav->id . "_";
			$page_nav->nav_selector_elements = array(floor($rec_per_page / 2), $rec_per_page, $rec_per_page * 2);
			$page_nav->nav_selector_elements_all = false;

			$page_nav->display_prev = true;
			$page_nav->display_next = true;
			$page_nav->display_first = true;
			$page_nav->display_last = true;
			
			$page_nav->with_frames = false;
			$page_nav->with_choice = false;
			$page_nav->with_totelem = true;
			$page_nav->nav_display_selector = false;

			$page_nav->page             = $page;

			$page_nav->records_per_page = $rec_per_page;
		}
		//forzare le idee in home
		
		if(array_key_exists("idea", $params) && is_array($params["idea"])) {
			$idea_params = $params["idea"];
		} else {
			$idea_params = null;
		} 
		
		$idea_detail = mod_crowdfund_get_idea(array("page" => $page, "rec_per_page" => $rec_per_page), $idea_params, null, true);
		
		$tot_page = ceil($idea_detail["count"] / $idea_detail["rec_per_page"]);
		$idea = $idea_detail["data"];
	}
	
	
	if(is_array($idea) && count($idea)) {//&& $idea["status_visible_decision"]&& $tmp_idea["status_visible_decision"]
		$tpl = ffTemplate::factory(ffCommon_dirname($tpl_path));
		$tpl->load_file(basename($tpl_path), "main");
		
		$cm->oPage->tplAddJs("jquery.plugins.jqbar");
		$cm->oPage->tplAddJs("idea"
            , array(
                "file" => "idea.js"
                , "path" => "/modules/crowdfund/themes/javascript"
        ));
		
		//check_function("get_short_description");
		$col = 1;

		$crowdfund_public_path = (!$smart_url && basename($globals->settings_path) ? $globals->settings_path : mod_crowdfund_get_path_by_lang("public"));
		$crowdfund_invest_path = mod_crowdfund_get_path_by_lang("invest");
		$crowdfund_donate_path = mod_crowdfund_get_path_by_lang("donate");
		$crowdfund_reward_path = mod_crowdfund_get_path_by_lang("reward");
		$crowdfund_question_path = mod_crowdfund_get_path_by_lang("question");

		if($enable_nav && $tot_page > 1) {
			$page_nav->num_rows = $idea_detail["count"];
			$tpl->set_var("unic_id_lower", strtolower($page_nav->prefix));
			$tpl->set_var("page", $page_nav->page);
			$tpl->set_var("PageNavigator", $page_nav->process(false));
		}

		if(is_array($idea_title)) {
			if(count($idea_title)) {
                $str_class = "";
                $str_title = "";
				foreach($idea_title AS $title_value) {
					if(strlen($title_value)) {
						if(strlen($str_class))
							$str_class .= " ";

						$str_class .= ffCommon_url_rewrite($title_value);

						if(strlen($str_title))
							$str_title .= "_";

						$str_title .= ffCommon_url_rewrite($title_value);
					}
				}
			}
		} elseif(strlen($idea_title)) {
			$str_title = ffCommon_url_rewrite($idea_title);
			$str_class = $idea_title;
		}

		if(strlen($str_title)) {
			$tpl->set_var("idea_class", " " . $str_class);
			$tpl->set_var("idea_title", ffTemplate::_get_word_by_code("crowdfund_" . $str_title . "_title"));

			$tpl->parse("SezTitle", false);
		} else {
			$tpl->set_var("idea_class", "");
			$tpl->set_var("SezTitle", "");
		}
		
		
		$tpl->set_var("crowdfund_public_path", $crowdfund_public_path);
		
		if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") && global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) 
		{
			$cm->oPage->widgetLoad("dialog");
			$cm->oPage->widgets["dialog"]->process(
				 "BackersModify"
				 , array(
					"tpl_id" => null
					//"name" => "myTitle"
					, "url" => ""
					, "title" => ffTemplate::_get_word_by_code("crowdfund_backer_payment_title")
					, "callback" => ""
					, "class" => ""
					, "params" => array(
					)
					, "resizable" => true
					, "position" => "center"
					, "draggable" => true
					, "doredirects" => false
				)
				, $cm->oPage
			);					
		}	
			
		foreach($idea AS $idea_key => $idea_value) 
		{
			$offer = mod_crowdfund_control_offert_limit($idea_value["ID"], false, $UserNID);
			$company = mod_crowdfund_get_company($idea_value["ID_anagraph_company"]);
			
			if($is_detail > 0) {
				if(check_function("set_header_page"))
					set_header_page($idea_value["title"], $idea_value["teaser"], $idea_value["categories"], array("image" =>  $idea_value["cover"]));
				if($idea_value["owner"] == $UserNID)
				{
					$tpl->set_var("idea_modify_url", FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/basic/" . $idea_value["smart_url"] . "?ret_url=" . urlencode($cm->oPage->getRequestUri()));
					$tpl->parse("SezModifyIdeaShortcut", false);  
				} else {
					$tpl->set_var("SezModifyIdeaShortcut", "");
				}

			} 

			$count_slider = 0;

			$tpl->set_var("col", $col);
			$tpl->set_var("smart_url", $idea_value["smart_url"]);
			$tpl->set_var("idea_smart_url", FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]);

			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_FOLLOWER"))
			{
				if($UserID == MOD_SEC_GUEST_USER_NAME && (global_settings("MOD_CROWDFUND_IDEA_DISABLE_FOLLOW_GUEST") || global_settings("MOD_CROWDFUND_IDEA_FORM_FOLLOW")))
				{
					$tpl->set_var("crowdfund_follow_label", ffTemplate::_get_word_by_code("crowdfund_label_follow"));
					$tpl->set_var("idea_follow_class", "");
					if(global_settings("MOD_CROWDFUND_IDEA_DISABLE_FOLLOW_GUEST")) 
					{
						$tpl->set_var("idea_follow_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
					} elseif(global_settings("MOD_CROWDFUND_IDEA_FORM_FOLLOW"))
					{
						$tpl->set_var("idea_follow_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_FOLLOW") . "', '" . $idea_value["smart_url"] . "', 'follow');"); 
					}
					$tpl->parse("SezFollow", false);
				} elseif ($UserID != MOD_SEC_GUEST_USER_NAME && $idea_value["owner"] != $UserNID)
				{
					$tpl->set_var("idea_follow_url", "CFollow(this, '" . $idea_value["smart_url"] . "');");
					if($idea_value["is_followed"]) 
					{
						$tpl->set_var("crowdfund_follow_label", ffTemplate::_get_word_by_code("crowdfund_label_followed"));
						$tpl->set_var("idea_follow_class", " followed");
					} else 
					{
						$tpl->set_var("crowdfund_follow_label", ffTemplate::_get_word_by_code("crowdfund_label_follow")); 
						$tpl->set_var("idea_follow_class", "");
					}
					$tpl->parse("SezFollow", false);
				} else
					$tpl->set_var("SezFollow", "");
			} else
			{
				$tpl->set_var("SezFollow", "");
			}

			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION") && $idea_value["enable_donation"])  
			{
				if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION_DECISION") && get_session("UserID") == MOD_SEC_GUEST_USER_NAME) 
				{
					$tpl->set_var("idea_donation_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
					$tpl->set_var("idea_donation_rel", "");
					$tpl->parse("SezDonationButton", false);
				} elseif(get_session("UserID") != MOD_SEC_GUEST_USER_NAME && !$offer && $idea_value["enable_donation"] && !$idea_value["enable_equity"])
				{
					if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") && (is_array($company) && count($company) && strlen($company["email"])) && ($idea_value["ID"] == 88 || $idea_value["ID"] == 127)) 
					{
						if(global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) {
							$tpl->set_var("idea_donation_url", "ff.ffPage.dialog.doOpen('BackersModify', '" . FF_SITE_PATH . $crowdfund_donate_path . "/" . $idea_value["smart_url"] . "');");
						} else {
							$tpl->set_var("idea_donation_url", "window.location.href = '" . FF_SITE_PATH . $crowdfund_donate_path . "/" . $idea_value["smart_url"] . "';");
						}
						$tpl->set_var("idea_donation_rel", "");
						$tpl->set_var("active", "");
						$tpl->parse("SezDonationButton", false);
					} elseif(global_settings("MOD_CROWDFUND_IDEA_FORM_DONATION") > 0)  
					{
						$tpl->set_var("idea_donation_rel", ' rel="L' . global_settings("MOD_CROWDFUND_IDEA_FORM_DONATION") . '" ');
						$tpl->set_var("idea_donation_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_DONATION") . "', '" . $idea_value["smart_url"] . "', 'donation', '" . $idea_value["symbol"] . "');"); 
						$tpl->set_var("active", "");
						$tpl->parse("SezDonationButton", false);
					} else 
					{
						$tpl->set_var("idea_donation_rel", "");
						$tpl->set_var("idea_donation_url", ""); 
						$tpl->set_var("active", "");
						$tpl->set_var("SezDonationButton", "");
					}
				} else
				{
					$tpl->set_var("idea_donation_rel", "");
					$tpl->set_var("idea_donation_url", ""); 
					$tpl->set_var("active", "");
					$tpl->set_var("SezDonationButton", "");
				}
			} else {
				$tpl->set_var("SezDonationButton", "");  
			} 

			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY") && $idea_value["enable_equity"]) 
			{
				if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY_DECISION") && get_session("UserID") == MOD_SEC_GUEST_USER_NAME) 
				{
					$tpl->set_var("idea_invest_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
					$tpl->set_var("idea_invest_rel", "");
					$tpl->parse("SezInvest", false);
				} elseif(get_session("UserID") != MOD_SEC_GUEST_USER_NAME && !$offer)
				{           
					if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") && (is_array($company) && count($company) && strlen($company["email"])))
					{
                        if(global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) {
							$tpl->set_var("idea_invest_url", "ff.ffPage.dialog.doOpen('BackersModify', '" . FF_SITE_PATH . $crowdfund_invest_path . "/" . $idea_value["smart_url"] . "?ret_url=" . $_SERVER["REQUEST_URI"] . "');");
						} else {
							$tpl->set_var("idea_invest_url", "window.location.href = '" . FF_SITE_PATH . $crowdfund_invest_path . "/" . $idea_value["smart_url"] . "';");
						}
						$tpl->set_var("idea_invest_rel", "");

						$tpl->parse("SezInvest", false);
					} elseif(global_settings("MOD_CROWDFUND_IDEA_FORM_INVEST") > 0) 
					{
						$tpl->set_var("idea_invest_rel", ' rel="L' . global_settings("MOD_CROWDFUND_IDEA_FORM_INVEST") . '" ');
						$tpl->set_var("idea_invest_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_INVEST") . "', '" . $idea_value["smart_url"] . "', 'equity', '" . $idea_value["symbol"] . "');");
						$tpl->parse("SezInvest", false);
					} else 
					{
						$tpl->set_var("SezInvest", "");
					}
				} else
				{
					$tpl->set_var("SezInvest", "");
				}
			} else 
			{
				$tpl->set_var("SezInvest", "");
			}

			$author = mod_crowdfund_get_author($idea_value["owner"]);
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EMAIL") && strlen($author["email"])) 
			{
				if(global_settings("MOD_CROWDFUND_IDEA_FORM_REQUEST_INFO") > 0) {
					$tpl->set_var("form_id", global_settings("MOD_CROWDFUND_IDEA_FORM_REQUEST_INFO"));
					$tpl->set_var("idea_question_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_REQUEST_INFO") . "', '" . $idea_value["smart_url"] . "');");
					$tpl->parse("SezRequestInfo", global_settings("MOD_CROWDFUND_IDEA_FORM_REQUEST_INFO"));
				} else 
				{
					$tpl->set_var("SezRequestInfo", "");
				}
			} else
			{
				$tpl->set_var("SezRequestInfo", "");
			}
			$tpl->set_var("crowdfund_services",  MOD_CROWDFUND_SERVICES_PATH);
			$tpl->set_var("idea_smart_url_base", $idea_value["smart_url"]); 

			$value_business_plan = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "businessplan");
			if($value_business_plan && $decide_businessplan)// 
			{
				$tpl->parse("SezBusinessPlanService", false);
			} else
			{
				$tpl->set_var("SezBusinessPlanService", "");
			}

			$value_income_statement = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "incomestatement");
			$value_cashflow = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "cashflow");

			if(($value_income_statement || $value_cashflow) && $decide_estimate_budget)
			{
				$tpl->parse("SezEstimateBudgetService", false);
			} else
			{
				$tpl->set_var("SezEstimateBudgetService", "");
			}

			$value_attach = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "attach"); 
			if($value_attach && $decide_attach) 
			{
				$tpl->parse("SezAttachService", false);
			} else
			{
				$tpl->set_var("SezAttachService", "");
			} 

			if (($idea_value["count_follower"] || $idea_value["count_backer"]) && $decide_backer_follower)// 
			{
				$tpl->parse("SezBackerFollowerService", false);
			} else
			{
				$tpl->set_var("SezBackerFollowerService", "");
			}

			$qa_value = mod_crowdfund_get_qa($ID_idea);
			if (is_array($qa_value) && count($qa_value) && $decide_qa)
			{
				$tpl->parse("SezQAService", false);
			} else
			{
				$tpl->set_var("SezQAService", "");
			}
			
			$timeline_value = mod_crowdfund_create_timeline($ID_idea);
			if (is_array($timeline_value) && count($timeline_value))
			{ 
				$tpl->parse("SezTimelineService", false);
			} else
			{
				$tpl->set_var("SezTimelineService", "");
			}
			
		if ($UserNID == 1 || $UserNID == 6)
		{ 
			$tpl->parse("SezTeamService", false);
		} else
		{
			$tpl->set_var("SezTeamService", "");
		}

		if(strlen($idea_value["cover"])) {
			$count_slider++;
			$tpl->set_var("idea_cover", FF_SITE_PATH . CM_SHOWFILES . "/" . $ico_mode . $idea_value["cover"]);
		} else {
			$tpl->set_var("idea_cover", FF_SITE_PATH . CM_SHOWFILES . "/" . THEME_INSET . "/images/spacer.gif");
		}


			$tpl->set_var("idea_title", $idea_value["title"]);
			//$tpl->set_var("idea_teaser", ishort_description($idea_value["teaser"], 200, "Text"));
			$tpl->set_var("idea_teaser", $idea_value["teaser"]);
			$tpl->set_var("idea_description", $idea_value["description"]);

			$tpl->set_var("idea_goal_current", $idea_value["goal_current"]);
			$tpl->set_var("idea_goal", $idea_value["goal"]);
			$tpl->set_var("idea_goal_perc", $idea_value["goal_perc"]);


			if($idea_value["enable_equity"] && global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY"))
			{
				$tpl->set_var("idea_equity", $idea_value["equity"] . "%");
				$tpl->parse("SezEquity", false);
			}
			else
			{
				$tpl->set_var("SezEquity", "");
			}
			if($idea_value["enable_pledge"] && global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") && !$offer)
			{
				$tpl->parse("SezPledge", false);
			}
			else
			{
				$tpl->set_var("SezPledge", "");
			}
/*				if($idea_value["enable_donation"] && global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
			{
				$tpl->parse("SezDonation", false);
			}
			else
			{
				$tpl->set_var("SezDonation", "");
			}
*/
			$tpl->set_var("idea_currency_symbol", $idea_value["symbol"]);
			$tpl->set_var("idea_expiration", $idea_value["expire"]);

			$chart_value = array
			( 
				"income_statement" => mod_crowdfund_get_income_statement($tmp_idea["ID"], false, false)
				, "cash_flow" => mod_crowdfund_get_cash_flow($tmp_idea["ID"], false, false, false)
			);


			if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY_COMPLEX") || ((is_array($chart_value) && (count($chart_value["income_statement"]) || count($chart_value["cash_flow"]))) && ($ID_idea == 88 || $ID_idea == 125 || ($ID_idea == 41 && $UserNID == 1)))) //in attesa di altre condizioni
			{
				if(is_array($chart_value) && (count($chart_value["income_statement"]) || count($chart_value["cash_flow"])))
				{
					$tpl->set_var("idea_summary_chart", mod_crowdfund_process_chart($smart_url, "chart-summary", true));
					$tpl->parse("SezSummaryChart", false); 
				} else
				{
					$tpl->set_var("SezSummaryChart", ""); 
				}


				if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY_COMPLEX")) 
				{
					if ($idea_value["is_startup"])
					{
						$tpl->set_var("SezNotStartUpCapital", "");
						$tpl->parse("SezStartUpCapital", false);  
					} else
					{
						$tpl->set_var("SezStartUpCapital", "");
						$tpl->parse("SezNotStartUpCapital", false);
					}

					if (!(int)$idea_value["capital_funded"])
					{
						$tpl->set_var("SezCapital", "");
						$tpl->parse("SezNoCapital", false);
					} else 
					{
						$tpl->set_var("SezNoCapital", "");
						$tpl->set_var("idea_capital", $idea_value["capital_funded"]);
						$tpl->parse("SezCapital", false);
					}
					$tpl->set_var("idea_total_capital", $idea_value["total_capital"]);
					$tpl->parse("SezSummaryCapital", false);
				} else
				{
					$tpl->set_var("SezSummaryCapital", "");
				}
				$tpl->parse("SezSummary", false);
			} else 
			{
				$tpl->set_var("SezSummary", "");
			}
			$tpl->set_var("idea_count_follower", $idea_value["count_follower"]);
			$tpl->set_var("idea_count_backer", $idea_value["count_backer"]);


			if(is_array($author) && count($author)) 
			{
				if ($author["public"])
				{
					$tpl->set_var("owner_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $author["slug"]);
					$tpl->parse("SezOwnerProfileLink", false);
				}
				else
				{
					$tpl->set_var("SezOwnerProfileLink", ""); 
				}
				if(check_function("get_user_avatar")) {
					$tpl->set_var("owner_avatar", get_user_avatar($author["avatar"], false, $author["email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_OWNER_ICO")));
					$tpl->parse("SezOwnerAvatar", false);
				}
				$tpl->set_var("owner_reference", $author["reference"]);

				if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EMAIL") && strlen($author["email"])) {
					$tpl->set_var("owner_email", $author["email"]);
					$tpl->parse("SezOwnerEmail", false);
				} else {
					$tpl->set_var("SezOwnerEmail", "");
				}
				$tpl->set_var("current_project", $author["current_project"]);
				$tpl->set_var("completed_project", $author["completed_project"]);

				if(is_array($author["custom"]) && count($author["custom"])) 
				{
					foreach($author["custom"] AS $custom_key => $custom_value) 
					{
						if ($custom_key != "other" && $custom_key != "Biography")
						{
							if(strlen($custom_value)) 
							{
								$tpl->set_var("owner_" . strtolower($custom_key), $custom_value);
							} else {
								$tpl->set_var("owner_" . strtolower($custom_key), "javascript:void(0);");
								$tpl->set_var("has_" . strtolower($custom_key), "na");
							}
							$tpl->parse("SezOwner" . ucfirst($custom_key), false);
						}
					}
				}
				if(global_settings("MOD_CROWDFUND_ENABLE_TEAM"))
				{
					$team = mod_crowdfund_get_team($ID_idea);
					$i = 1;
					if(is_array($team) && count($team)) {
						foreach($team AS $mamber_key => $member_value)
						{
							if(check_function("get_user_avatar")) 
							{ 
								$tpl->set_var("member_avatar", get_user_avatar($member_value["member_avatar"], false, $member_value["member_email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_TEAM_ICO")));
							}
							$tpl->set_var("member_reference", $member_value["member_reference"]);
							if($member_value["public"])
							{
								$tpl->set_var("member_team_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $member_value["slug"]);
								$tpl->parse("SezTeamMemberHeader", false);
								$tpl->parse("SezTeamMemberFooter", false);
							} else
							{
								$tpl->set_var("SezTeamMemberHeader", "");
								$tpl->set_var("SezTeamMemberFooter", "");
							}
							$tpl->set_var("team_column", "col" . $i);
							if($i < 4)
							{
								$i++;
							} else
							{
								$i = 1;
							}
							$tpl->parse("SezTeamMember", true);
						}
						$tpl->parse("SezTeam", false);
					}
				} else
				{
					$tpl->set_var("SezTeam", "");
				}

				$tpl->parse("SezOwner", false);		
			} else {
				$tpl->set_var("SezOwner", "");
			}



			
			if(is_array($company) && count($company)) {
				if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_COMPANY_DETAIL"))
				{
					$tpl->set_var("company_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $company["slug"]);
					$tpl->parse("SezCompanyProfileLink", false);
				} else
				{
					$tpl->set_var("SezCompanyProfileLink", "");
				}
				if(check_function("get_user_avatar")) {
					$tpl->set_var("company_avatar", get_user_avatar($company["avatar"], false, false, FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_COMPANY_ICO")));
					$tpl->parse("SezCompanyAvatar", false);
				}
				$tpl->set_var("company_reference", $company["billreference"]);
				$tpl->set_var("company_piva", $company["billpiva"]);
				$tpl->set_var("company_cf", $company["billcf"]);
				$tpl->set_var("company_address", $company["billaddress"]);
				$tpl->set_var("company_cap", $company["billcap"]);
				$tpl->set_var("company_city", $company["billtown"]);
				$tpl->set_var("company_province", $company["billprovince"]);
				$tpl->set_var("company_state", $company["billstate"]);

				if(is_array($company["custom"]) && count($company["custom"])) {
					foreach($company["custom"] AS $custom_key => $custom_value) {
						if(strlen($custom_value)) {
							$tpl->set_var("company_" . $custom_key, $custom_value);
							$tpl->parse("SezCompany" . ucfirst($custom_key), false);
						} else {
							$tpl->set_var("SezCompany" . ucfirst($custom_key), "");
						}
					}
				}
				$tpl->parse("SezCompany", false);		
			} else {
				$tpl->set_var("SezCompany", "");
			}

			if(strlen($idea_value["skype_account"]) && global_settings("MOD_CROWDFUND_IDEA_ENABLE_SKYPE")) {
				$tpl->set_var("idea_skype_account", $idea_value["skype_account"]);
				$tpl->parse("SezSkypeChat", false);
			} else {
				$tpl->set_var("SezSkypeChat", "");
			}

			if($is_detail) { 
				$pledge = mod_crowdfund_get_pledge($idea_value["ID"]); 

				if(is_array($pledge) && count($pledge) && $idea_value["enable_pledge"] && global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") && !$offer) 
				{
					$switch_style = "positive";
					foreach ($pledge AS $pledge_key => $pledge_value)  
					{
						if ($pledge_value["backer"] && !$pledge_value["disabled"])
						{
							$tpl->set_var("pledge_available", "available");
							if (get_session("UserID") == MOD_SEC_GUEST_USER_NAME && global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE_DECISION"))
							{
								$tpl->set_var("idea_pledge_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
								$tpl->set_var("idea_pledge_rel", "");
								$tpl->parse("SezPledgeAvailableHeader", false);
								$tpl->parse("SezPledgeAvailableFooter", false);
							} else
							{
								if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") && $idea_value["ID_anagraph_company"] > 0 && $idea_value["ID"] == 88) 
								{
									if(global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) {
										$tpl->set_var("idea_pledge_url", "ff.ffPage.dialog.doOpen('BackersModify', '" . FF_SITE_PATH . $crowdfund_reward_path . "/" . $idea_value["smart_url"] . "?price=" . $pledge_value["price"] . "');");
									} else {
										$tpl->set_var("idea_pledge_url", "window.location.href = '" . FF_SITE_PATH . $crowdfund_reward_path . "/" . $idea_value["smart_url"] . "?price=" . $pledge_value["price"] . "';");
									}
									$tpl->set_var("idea_pledge_rel", "");
									$tpl->parse("SezPledgeAvailableHeader", false);
									$tpl->parse("SezPledgeAvailableFooter", false);
								} elseif(global_settings("MOD_CROWDFUND_IDEA_FORM_REWARD") > 0) 
								{
									$pledge_price = new ffData($pledge_value["price"], "Number");
									$tpl->set_var("idea_pledge_rel", ' rel="L' . global_settings("MOD_CROWDFUND_IDEA_FORM_REWARD") . '" ');
									$tpl->set_var("idea_pledge_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_REWARD") . "', '" . $idea_value["smart_url"] . "', 'pledge', '" . $pledge_price->getValue("Number", FF_SYSTEM_LOCALE) . "', '" . $pledge_price->getValue("Currency", FF_LOCALE) . " " . $idea_value["symbol"] . "');");
									$tpl->parse("SezPledgeAvailableHeader", false);
									$tpl->parse("SezPledgeAvailableFooter", false);
								} else 
								{
									$tpl->set_var("SezPledgeAvailableHeader", "");
									$tpl->set_var("SezPledgeAvailableFooter", "");
								}
							}  
						} else  
						{
							$tpl->set_var("pledge_available", "not-available");
							$tpl->set_var("SezPledgeAvailableHeader", "");
							$tpl->set_var("SezPledgeAvailableFooter", "");
						}							


						$tpl->set_var("pledge_available_backers", $pledge_value["backer"]);
						$tpl->set_var("pledge_price", $pledge_value["price"]);
						if ($pledge_value["backers"])
						{
							$tpl->set_var("pledge_backers", $pledge_value["backers"]);
							$tpl->parse("SezPledgeItemBackers", false);
						} else
						{
							$tpl->set_var("SezPledgeItemBackers", "");
						}
						if ($pledge_value["limit"]) 
						{
							$tpl->set_var("pledge_limit", $pledge_value["limit"]);
							$tpl->parse("SezPledgeItemLimit", false);
						} else
						{
							$tpl->set_var("SezPledgeItemLimit", "");
						}

						$tpl->set_var("pledge_description", $pledge_value["description"]);
						if ($switch_style == "negative")
						{
							$switch_style = "positive";
						} else 
						{
							$switch_style = "negative";
						}
						$tpl->set_var("switch_style", $switch_style);
						$tpl->parse("SezPledgeItem", true);
					}
					$tpl->parse("SezPledge", false);
				} else {
					$tpl->set_var("SezPledge", "");
				}

				$attach = mod_crowdfund_get_attach($ID_idea);
				if(is_array($attach) && count($attach)) 
				{
					foreach($attach AS $attach_key => $attach_value) 
					{
						if(strlen($attach_value["file"]) && is_file(DISK_UPDIR . $attach_value["file"])) {
							$file_ext = cm_get_filename($attach_value["file"], false);
							if($file_ext == "jpg"
								|| $file_ext == "png"
								|| $file_ext == "gif"
							) {
								$tpl->set_var("idea_gallery_attach", FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_GALLERY_ICO") . $attach_value["file"]);
								$tpl->set_var("idea_gallery_attach_name", $attach_value["title"]);
								$tpl->parse("SezGalleryItem", true);

								$count_slider++;
							}
						}
					}
				}
				if(strlen($idea_value["video"])) 
				{
					$count_slider++;
					$display_video = true;

					$tpl->set_var("idea_video", $idea_value["video"]);
					$tpl->parse("SezVideo", false);
				} 
				else 
				{
					$display_video= false;
					$tpl->set_var("SezVideo", "");
				}

				if($count_slider) 
				{
					$tpl->set_var("idea_cover", FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_GALLERY_ICO") . $idea_value["cover"]);

					if($count_slider > 1) 
					{
						if($display_video) 
						{
							$cm->oPage->tplAddJs("Froogaloop"
                                , array(
                                    "file" => "froogaloop.js"
                                    , "path" => FF_THEME_DIR . "/library/plugins/froogaloop"
                            ));
							$tpl->set_var("slidertype", "flexslider video");
						}
						else 
						{
							$tpl->set_var("slidertype", "flexslider");
						}

					    $cm->oPage->tplAddJs("jquery.flexslider");
						$cm->oPage->tplAddJs("idea-detail"
                            , array(
                                "file" => "idea-detail.js"
                                , "path" => "/modules/crowdfund/themes/javascript"
                        ));
						if(check_function("get_resource_cascading")) {
							get_resource_cascading($cm->oPage, "/", "flexslider.css", true, FF_THEME_DIR . "/library/plugins/jquery.flexslider");
						}
						if(strlen($idea_value["cover"])) {
							$tpl->parse("SezCover", false);						
						} else {
							$tpl->set_var("SezCover", "");
						}
					} 
					else 
					{
						$tpl->set_var("slidertype", "gallery");
						$tpl->parse("SezCover", false);
					}
					$tpl->parse("SezGallery", false);
				} 
				else
				{
					$tpl->set_var("SezGallery", "");
				}
			}

			$tpl->parse("SezIdea", true);
			$col++;
			if($col > $max_col)
				$col = 1; 
		}
	$buffer = $tpl->rpparse("main", false);
	}
	return $buffer;	
}


cm::getInstance()->addEvent("vg_on_user_profile_processed", "mod_crowdfund_vg_on_user_profile_processed");

function mod_crowdfund_vg_on_user_profile_processed($cm, $oRecord, $UserID) 
{
	if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
	{
        $filename = cm_cascadeFindTemplate("/contents/user/profile_new.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/user/profile_new.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/user/profile_new.html", $cm->oPage->theme);*/
	} else
	{
        $filename = cm_cascadeFindTemplate("/contents/user/profile.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/user/profile.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/user/profile.html", $cm->oPage->theme);*/
	}
	if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
	{
		$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
														"idea" => array("owner" => $UserID)
														, "title" => array("owner", "")
													)
											);
	} else 
	{
		$buffer = mod_crowdfund_process_idea(null, $filename, array(
															"idea" => array("owner" => $UserID)
															, "title" => array("owner", "") 
														)
												);
	}
	if(strlen($buffer)) {
		$cm->oPage->addContent($buffer);
	}
	
	if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
	{
		$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
														"idea" => array("backer" => $UserID)
                                                        , "title" => array("backer", "") 
													)
											);
	} else 
	{
		$buffer = mod_crowdfund_process_idea(null, $filename, array(
                                                        "idea" => array("backer" => $UserID)
                                                        , "title" => array("backer", "") 
                                                    )
                                            );
	}
	if(strlen($buffer)) {
		$cm->oPage->addContent($buffer);
	}
    
    if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
	{
		$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
														 "idea" => array("follower" => $UserID)
                                                        , "title" => array("follower", "")
													)
											);
	} else 
	{
		$buffer = mod_crowdfund_process_idea(null, $filename, array(
                                                        "idea" => array("follower" => $UserID)
                                                        , "title" => array("follower", "") 
                                                    )
                                            );
	}
    if(strlen($buffer)) {
        $cm->oPage->addContent($buffer);
    }

}
/* LAVORO SUI FORM */
function mod_crowdfund_save_question($component) {
	$db = ffDB_Sql::factory();
	ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
	/*$field_project = global_settings("MOD_CROWDFUND_IDEA_FORM_FIELD_PROJECT");
	$ID_field_project = 0;
	if(strlen($field_project)) {
		$sSQL = "SELECT module_form_fields.* 
				FROM module_form_fields
				WHERE module_form_fields.name = " . $db->toSql($field_project);
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				if(array_key_exists($db->getField("ID", "Number", true), $component->form_fields)) {
					$ID_field_project = $db->getField("ID", "Number", true);
					break;	
				}
			} while($db->nextRecord());
		}
	}*/
}

function mod_crowdfund_set_backer($component) {
	$db = ffDB_Sql::factory();

	$field_project = global_settings("MOD_CROWDFUND_IDEA_FORM_FIELD_PROJECT");
	$ID_field_project = 0;
	if(strlen($field_project)) {
		$sSQL = "SELECT module_form_fields.* 
				FROM module_form_fields
				WHERE module_form_fields.name = " . $db->toSql($field_project);
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				if(array_key_exists($db->getField("ID", "Number", true), $component->form_fields)) {
					$ID_field_project = $db->getField("ID", "Number", true);
					break;	
				}
			} while($db->nextRecord());
		}
	}

	$field_price = global_settings("MOD_CROWDFUND_IDEA_FORM_FIELD_PRICE");
	$ID_field_price = 0;
	if(strlen($field_price)) {
		$sSQL = "SELECT module_form_fields.* 
				FROM module_form_fields
				WHERE module_form_fields.name = " . $db->toSql($field_price);
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				if(array_key_exists($db->getField("ID", "Number", true), $component->form_fields)) {
					$ID_field_price = $db->getField("ID", "Number", true);	
					break;	
				}
			} while($db->nextRecord());
		}
	}
	
	$sSQL = "SELECT layout.ID 
			FROM layout 
			WHERE layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = 'Module')
				AND layout.value = 'form'
				AND layout.params = " . $db->toSql($component->user_vars["form_name"]);
	$db->query($sSQL);
	if($db->nextRecord()) {
		$ID_field_type = $db->getField("ID", "Number", true);
	}

	switch ($ID_field_type) {
		case global_settings("MOD_CROWDFUND_IDEA_FORM_DONATION"):
			$type = "donate";
			break;
		case global_settings("MOD_CROWDFUND_IDEA_FORM_REWARD"):
			$type = "reward";
			break;
		case global_settings("MOD_CROWDFUND_IDEA_FORM_INVEST"):
			$type = "invest";
			break;
		default:
		
	}
		
	if($ID_field_project > 0 
		&& strlen($component->form_fields[$ID_field_project]->getValue())
		&& $ID_field_price > 0
		&& $component->form_fields[$ID_field_price]->getValue() > 0
	) {	
		$price = $component->form_fields[$ID_field_price]->getValue();
				
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.*
					, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
				FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
					INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url = " . $db->toSql(ffCommon_url_rewrite(trim($component->form_fields[$ID_field_project]->getValue())));
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$ID_idea = $db->getField("ID", "Number", true);
			$title = $db->getField("title", "Text", true);
			$teaser = $db->getField("teaser", "Text", true);
			$smart_url = $db->getField("smart_url", "Text", true);
			$cover = $db->getField("cover", "Text", true);
		}
		
		$sSQL = "SELECT anagraph.* 
					FROM anagraph
					WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . "
					ORDER BY anagraph.ID
					LIMIT 1 ";
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$ID_user_anagraph = $db->getField("ID", "Number", true);
			$complete_name = $db->getField("name", "Text", true) . " " . $db->getField("surname", "Text", true);
		}
		
		if($ID_idea > 0 && $ID_user_anagraph > 0) {
			$sSQL = "SELECT COUNT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID) AS count_backer
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($ID_idea, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_user_anagraph = " . $db->toSql($ID_user_anagraph, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) {
				$count_backer = $db->getField("count_backer", "Number", true);
			}
			
			$enable_insert = false;
			if(global_settings("MOD_CROWDFUND_LIMIT_OFFER_PER_PROJECT")) {
				if(global_settings("MOD_CROWDFUND_LIMIT_OFFER_PER_PROJECT") > $count_backer) {
					$enable_insert = true;
				}
			} else {
				$enable_insert = true;
			}
			
			if($enable_insert) {
				$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
						(
							ID
							, ID_idea
							, ID_user_anagraph
							, price
							, created
							, last_update
							, ID_reward
							, ID_bill
							, type
							, is_private
							, confirmed_price
						)
						VALUES
						(
							null
							, ". $db->toSql($ID_idea, "Number") . "
							, ". $db->toSql($ID_user_anagraph, "Number") . "
							, ". $db->toSql($price, "Number") . "
							, ". $db->toSql(time(), "Number") . "
							, ". $db->toSql(time(), "Number") . "
							, ". $db->toSql(0, "Number") . "
							, ". $db->toSql(0, "Number") . "
							, ". $db->toSql($type) . "
							, ". $db->toSql(0, "Number") . "
							, ". $db->toSql(0, "Number") . "
						)";
				$db->execute($sSQL);
				
				mod_crowdfund_update_goal($ID_idea, true);
				
				$crowdfund_public_path = mod_crowdfund_get_path_by_lang("public");
						
				if(check_function("system_lib_facebook")) 
				{
					$res = facebook_publish(
						$complete_name . " " . ffTemplate::_get_word_by_code("crowdfund_offer_" . $type) . " " . $title
						, DOMAIN_INSET . FF_SITE_PATH . $crowdfund_public_path . "/" . $smart_url
						, "http://" . DOMAIN_INSET . FF_SITE_PATH . CM_SHOWFILES . "/crowdfundme-social" . $cover     
						, $title
						, ""
						, $teaser
						, array()//array("name" => CM_LOCAL_APP_NAME, "link" => "http://" . DOMAIN_INSET)	
						, "" // place serve read_stream ...ma non serve
						, "" // spazio per raccogliere le persone citate
						, "{'value':'EVERYONE'}"//funzionano solo self e fiends.. serve read_stream per {'value':'EVERYONE'} e {'value':'ALL_FRIENDS'} e {'value':'FRIENDS_OF_FRIENDS'}
					); 
				}

				$res = array("class" => "followed", "label" => ffTemplate::_get_word_by_code("crowdfund_label_offered"));
			}
		}
	}
//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
}

function mod_crowdfund_process_idea_new($smart_url, $tpl_path, $params = array(), $dashboard = null) {
    $cm = cm::getInstance();
    $db = ffDB_Sql::factory();
    $globals = ffGlobals::getInstance("gallery");
    $UserNID = get_session("UserNID");
    $UserID = get_session("UserID");
    $cm->oPage->tplAddJs("jquery.plugins.qtip2");
    $cm->oPage->tplAddJs("jquery.ui");
   /* Remove jquery ui css
    $css_deps 		= array(
                                  "jquery.ui.core"        => array(
                                                  "file" => "jquery.ui.core.css"
                                                , "path" => null
                                                , "rel" => "jquery.ui"
                                        ), 
                                  "jquery.ui.theme"        => array(
                                                  "file" => "jquery.ui.theme.css"
                                                , "path" => null
                                                , "rel" => "jquery.ui"
                                        ), 
                                  "jquery.ui.tabs"        => array(
                                                  "file" => "jquery.ui.tabs.css"
                                                , "path" => null
                                                , "rel" => "jquery.ui"
                                        )
                        );	

                        if(is_array($css_deps) && count($css_deps)) {
                                foreach($css_deps AS $css_key => $css_value) {
                                        $rc = $cm->oPage->widgetResolveCss($css_key, $css_value, $cm->oPage);

                                        $cm->oPage->tplAddCss(preg_replace('/[^0-9a-zA-Z]+/', "", $css_key), $rc["file"], $rc["path"], "stylesheet", "text/css", false, false, null, false, "bottom");
                                }
                        }
    */
    $decide_businessplan = $decide_estimate_budget =
    $decide_attach = $decide_backer_follower = $decide_qa = 1;


    $key = array("businessplan", "estimate_budget", "attach", "backer_follower", "qa");
	
    if ($UserID == MOD_SEC_GUEST_USER_NAME)
    {
        foreach ($key as $value) 
        { 
            $value_name = strtoupper($value);
            if ((global_settings("MOD_CROWDFUND_IDEA_" . $value_name . "_DECISION_NOT_VIEW")) == false) 
            {
                ${"decide_" . $value} = 0;
            }
        }
    } 
	
    $idea_title = (isset($params["title"])
                        ? $params["title"]
                        : "idea"
                    );
	
    if(strlen($smart_url)) 
    {
        $ico_mode = (isset($params["ico_mode"])
                        ? $params["ico_mode"]
                        : global_settings("MOD_CROWDFUND_DETAIL_ICO")
                    );

        $is_detail = true;
        $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url), null, false, true, $dashboard);
        $tmp_idea = current($idea);

        $ID_idea = $tmp_idea["ID"];
    } else 
    {
        $prefix_pagenav = "ideapage";
        $enable_nav = (isset($params["enable_nav"])
                            ? $params["enable_nav"]
                            : false
                        );
        $rec_per_page = (isset($params["rec_per_page"])
                            ? $params["rec_per_page"]
                            : global_settings("MOD_CROWDFUND_IDEA_REC_PER_PAGE")
                        );
        $page =	(intval($_REQUEST[$prefix_pagenav . "_" . "page"]) > 0
                        ? $_REQUEST[$prefix_pagenav . "_" . "page"]
                        : 1
                    );
        $ico_mode = (isset($params["ico_mode"])
                        ? $params["ico_mode"]
                        : global_settings("MOD_CROWDFUND_THUMB_ICO")
                    );
        $max_col = (isset($params["col"])
                        ? $params["col"]
                        : global_settings("MOD_CROWDFUND_IDEA_COL")
                    );

        $is_detail = false;

        if($enable_nav) {
            $page_nav = ffPageNavigator::factory($cm->oPage, FF_DISK_PATH, FF_SITE_PATH, null, $cm->oPage->theme);
            $page_nav->oPage = array(&$cm->oPage);
            $page_nav->id = $prefix_pagenav;
            if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
                $page_nav->doAjax = true;
            else
                $page_nav->doAjax = false;

            $page_nav->PagePerFrame = 6;

            $page_nav->prefix = $page_nav->id . "_";
            $page_nav->nav_selector_elements = array(floor($rec_per_page / 2), $rec_per_page, $rec_per_page * 2);
            $page_nav->nav_selector_elements_all = false;

            $page_nav->display_prev = true;
            $page_nav->display_next = true;
            $page_nav->display_first = true;
            $page_nav->display_last = true;

            $page_nav->with_frames = false;
            $page_nav->with_choice = false;
            $page_nav->with_totelem = true;
            $page_nav->nav_display_selector = false;

            $page_nav->page             = $page;

            $page_nav->records_per_page = $rec_per_page;
        }
        //forzare le idee in home

        if(array_key_exists("idea", $params) && is_array($params["idea"])) {
                $idea_params = $params["idea"];
        } else {
                $idea_params = null;
        } 

        $idea_detail = mod_crowdfund_get_idea(array("page" => $page, "rec_per_page" => $rec_per_page), $idea_params, null, true);

        $tot_page = ceil($idea_detail["count"] / $idea_detail["rec_per_page"]);
        $idea = $idea_detail["data"];
    }
	
	
    if(is_array($idea) && count($idea)) {//&& $idea["status_visible_decision"]&& $tmp_idea["status_visible_decision"]
        $tpl = ffTemplate::factory(ffCommon_dirname($tpl_path));
        $tpl->load_file(basename($tpl_path), "main");
		
        $cm->oPage->tplAddJs("jquery.plugins.jqbar");
        $cm->oPage->tplAddJs("idea"
            , array(
                "file" => "idea.js"
                , "path" => "/modules/crowdfund/themes/javascript"
        ));

        //check_function("get_short_description");
        $col = 1;

        $crowdfund_public_path = (!$smart_url && basename($globals->settings_path) ? $globals->settings_path : mod_crowdfund_get_path_by_lang("public"));
        $crowdfund_invest_path = mod_crowdfund_get_path_by_lang("invest");
        $crowdfund_donate_path = mod_crowdfund_get_path_by_lang("donate");
        $crowdfund_reward_path = mod_crowdfund_get_path_by_lang("reward");
		$crowdfund_question_path = mod_crowdfund_get_path_by_lang("question");
		//echo MOD_CROWDFUND_QUESTION_PATH . "ASD";
		//echo $crowdfund_invest_path . " - " . $crowdfund_question_path;

        if($enable_nav && $tot_page > 1) {
            $page_nav->num_rows = $idea_detail["count"];
            $tpl->set_var("unic_id_lower", strtolower($page_nav->prefix));
            $tpl->set_var("page", $page_nav->page);
            $tpl->set_var("PageNavigator", $page_nav->process(false));
        }

        if(is_array($idea_title)) {
            if(count($idea_title)) {
                $str_class = "";
                $str_title = "";
                foreach($idea_title AS $title_value) {
                    if(strlen($title_value)) {
                        if(strlen($str_class))
                                $str_class .= " ";

                        $str_class .= ffCommon_url_rewrite($title_value);

                        if(strlen($str_title))
                                $str_title .= "_";

                        $str_title .= ffCommon_url_rewrite($title_value);
                    }
                }
            }
        } elseif(strlen($idea_title)) {
            $str_title = ffCommon_url_rewrite($idea_title);
            $str_class = $idea_title;
        }

        if(strlen($str_title)) {
            $tpl->set_var("idea_class", " " . $str_class);
            $tpl->set_var("idea_title", ffTemplate::_get_word_by_code("crowdfund_" . $str_title . "_title"));

            $tpl->parse("SezTitle", false);
        } else {
            $tpl->set_var("idea_class", "");
            $tpl->set_var("SezTitle", "");
        }
		
		
        $tpl->set_var("crowdfund_public_path", $crowdfund_public_path);
		
        if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") && global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) 
        {
            $cm->oPage->widgetLoad("dialog");
            $cm->oPage->widgets["dialog"]->process(
                     "BackersModify"
                     , array(
                            "tpl_id" => null
                            //"name" => "myTitle"
                            , "url" => ""
                            , "title" => ffTemplate::_get_word_by_code("crowdfund_backer_payment_title")
                            , "callback" => ""
                            , "class" => ""
                            , "params" => array(
                            )
                            , "resizable" => true
                            , "position" => "center"
                            , "draggable" => true
                            , "doredirects" => false
                    )
                    , $cm->oPage
            );					
        }	
		
		if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EMAIL")) 
        {
            $cm->oPage->widgetLoad("dialog");
            $cm->oPage->widgets["dialog"]->process(
                     "RequestInfo"
                     , array(
                            "tpl_id" => null
                            //"name" => "myTitle"
                            , "url" => ""
                            , "title" => ffTemplate::_get_word_by_code("crowdfund_request_info")
                            , "callback" => ""
                            , "class" => ""
                            , "params" => array(
                            )
                            , "resizable" => true
                            , "position" => "center"
                            , "draggable" => true
                            , "doredirects" => false
                    )
                    , $cm->oPage
            );					
        }	
			
        foreach($idea AS $idea_key => $idea_value) 
        {
            $offer = mod_crowdfund_control_offert_limit($idea_value["ID"], false, $UserNID);
            $company = mod_crowdfund_get_company($idea_value["ID_anagraph_company"]);

            if($is_detail > 0) {
                if(check_function("set_header_page"))
                    set_header_page($idea_value["title"], $idea_value["teaser"], $idea_value["categories"], array("image" =>  $idea_value["cover"]));
                if($idea_value["owner"] == $UserNID)
                {
                    $tpl->set_var("idea_modify_url", FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/basic/" . $idea_value["smart_url"] . "?ret_url=" . urlencode($cm->oPage->getRequestUri()));
                    $tpl->parse("SezModifyIdeaShortcut", false);  
                } else {
                    $tpl->set_var("SezModifyIdeaShortcut", "");
                }

            } 

            $count_slider = 0;

            $tpl->set_var("col", $col);
            $tpl->set_var("smart_url", $idea_value["smart_url"]);
            $tpl->set_var("idea_smart_url", FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]);
            if(!($idea_value["expire"] > 0))
            {
                $tpl->parse("SezIdeaEnd", false);
                $tpl->set_var("SezOfferButtonClick", "");
                $tpl->set_var("SezFollow", "");
				$tpl->set_var("SezIdeaFund", "");
                $tpl->set_var("idea_equity", "");
                $tpl->set_var("SezInvestCircPercent", "");
                $tpl->set_var("container_equity_active", "inactive"); 
                $tpl->set_var("container_donation_active", "inactive");
                $tpl->set_var("container_pledge_active", "inactive");
            } else
            {
				
            if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_FOLLOWER"))
            {
                if($UserID == MOD_SEC_GUEST_USER_NAME && (global_settings("MOD_CROWDFUND_IDEA_DISABLE_FOLLOW_GUEST") || global_settings("MOD_CROWDFUND_IDEA_FORM_FOLLOW")))
                {
                    $tpl->set_var("crowdfund_follow_label", ffTemplate::_get_word_by_code("crowdfund_label_follow"));
                    $tpl->set_var("idea_follow_class", "");
                    if(global_settings("MOD_CROWDFUND_IDEA_DISABLE_FOLLOW_GUEST")) 
                    {
                        $tpl->set_var("idea_follow_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
                    } elseif(global_settings("MOD_CROWDFUND_IDEA_FORM_FOLLOW"))
                    {
                        $tpl->set_var("idea_follow_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_FOLLOW") . "', '" . $idea_value["smart_url"] . "', 'follow');"); 
                    }
                    $tpl->parse("SezFollow", false);
                } elseif ($UserID != MOD_SEC_GUEST_USER_NAME && $idea_value["owner"] != $UserNID)
                {
                    $tpl->set_var("idea_follow_url", "CFollow(this, '" . $idea_value["smart_url"] . "');");
                    if($idea_value["is_followed"]) 
                    {
                        $tpl->set_var("crowdfund_follow_label", ffTemplate::_get_word_by_code("crowdfund_label_followed"));
                        $tpl->set_var("idea_follow_class", " followed");
                    } else 
                    {
                        $tpl->set_var("crowdfund_follow_label", ffTemplate::_get_word_by_code("crowdfund_label_follow")); 
                        $tpl->set_var("idea_follow_class", "");
                    }
                    $tpl->parse("SezFollow", false);
                } else
                    $tpl->set_var("SezFollow", "");
            } else
            {
                $tpl->set_var("SezFollow", "");
            }
			
            if(((global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION") && $idea_value["enable_donation"]) ||
                    (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY") && $idea_value["enable_equity"]) ||
                    (global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") && $idea_value["enable_pledge"]))
				&& (is_array($company) && count($company) && strlen($company["email"])))
            {
                $pledge = mod_crowdfund_get_pledge($idea_value["ID"], null, true, true);
				if($idea_value["goal"] == $idea_value["goal_current"])
				{
					$tpl->parse("SezIdeaFund", false);
					$tpl->set_var("SezOfferButtonClick", "");
					$tpl->set_var("idea_equity", "");
					$tpl->set_var("SezInvestCircPercent", "");
					$tpl->set_var("container_equity_active", "inactive"); 
					$tpl->set_var("container_donation_active", "inactive");
					$tpl->set_var("container_pledge_active", "inactive");
				} else
				{
					$tpl->set_var("SezIdeaFund", "");
                if((global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION_DECISION") || global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY_DECISION"))
                        && get_session("UserID") == MOD_SEC_GUEST_USER_NAME) 
                {
                    if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
                    {
                        if($idea_value["enable_donation"])
                        {
                            $tpl->set_var("idea_donation_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
                            $tpl->set_var("idea_donation_rel", "");
                            $tpl->set_var("SezDonationSpanHide", "");
                            $tpl->set_var("container_donation_active", "");
                            $tpl->parse("SezDonationButtonHide", false);
                        } else
                        {
                            $tpl->set_var("idea_donation_url", "");
                            $tpl->set_var("idea_donation_rel", "");
                            $tpl->parse("SezDonationSpanHide", false);
                            $tpl->set_var("container_donation_active", "inactive");
                            $tpl->set_var("SezDonationButtonHide", "");
                        }
                        $tpl->parse("SezDonationActive", false);
                    } else 
                    {
                        $tpl->set_var("SezDonationActive", "");
                    }
                    if($idea_value["enable_equity"])
                    {
                            $tpl->set_var("idea_invest_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
                            $tpl->set_var("idea_invest_rel", "");
                            $tpl->parse("SezInvestButtonHide", false);
                            $tpl->parse("SezInvest", false);
                            $tpl->set_var("container_equity_active", "");
                            $tpl->set_var("idea_equity", $idea_value["equity"] . "%");
                            $tpl->parse("SezInvestCircPercent", false);
                            $tpl->set_var("SezInvestSpanHide", "");
                    } else
                    {
                            $tpl->set_var("idea_invest_url", "");
                            $tpl->set_var("idea_invest_rel", "");
                            $tpl->parse("SezInvestSpanHide", false);
                            $tpl->set_var("container_equity_active", "inactive");
                            $tpl->set_var("idea_equity", "");
                            $tpl->set_var("SezInvestCircPercent", "");
                            $tpl->parse("SezInvest", false);
                            $tpl->set_var("SezInvestButtonHide", "");
                    } 
                    if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE"))
                    {

                        if(is_array($pledge) && count($pledge))
                        {
                            $tpl->parse("SezPledgeButtonHide", false);
                            $tpl->set_var("container_pledge_active", "");
                            $tpl->set_var("SezPledgeSpanHide", "");
                        } else
                        {
                            $tpl->set_var("container_pledge_active", "inactive");
                            $tpl->parse("SezPledgeSpanHide", false); 
                            $tpl->set_var("SezPledgeButtonHide", "");
                        }
                        $tpl->parse("SezPledgeActive", false);
                    } else
                    {
                        $tpl->set_var("SezPledgeActive", "");
                    }
                } elseif(get_session("UserID") != MOD_SEC_GUEST_USER_NAME && !$offer)
                {
                    if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
                    {
                        if($idea_value["enable_donation"]) 
                        {
                            if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") && (is_array($company) && count($company) && strlen($company["email"]))) 
                            {
                                if(global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) {
                                    $tpl->set_var("idea_donation_url", "ff.ffPage.dialog.doOpen('BackersModify', '" . FF_SITE_PATH . $crowdfund_donate_path . "/" . $idea_value["smart_url"] . "');");
                                } else {
                                    $tpl->set_var("idea_donation_url", "window.location.href = '" . FF_SITE_PATH . $crowdfund_donate_path . "/" . $idea_value["smart_url"] . "';");
                                }
                                $tpl->set_var("idea_donation_rel", "");
                                $tpl->parse("SezDonationButtonHide", false);
                                $tpl->set_var("SezDonationSpanHide", "");
                                $tpl->set_var("container_donation_active", "");
                                $tpl->parse("SezDonation", false);
                            } elseif(global_settings("MOD_CROWDFUND_IDEA_FORM_DONATION") > 0)  
                            {
                                $tpl->set_var("idea_donation_rel", ' rel="L' . global_settings("MOD_CROWDFUND_IDEA_FORM_DONATION") . '" ');
                                $tpl->set_var("idea_donation_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_DONATION") . "', '" . $idea_value["smart_url"] . "', 'donation', '" . $idea_value["symbol"] . "');"); 
                                $tpl->parse("SezDonation", false);
                                $tpl->set_var("SezDonationSpanHide", "");
                                $tpl->set_var("container_donation_active", "");
                                $tpl->parse("SezDonationButtonHide", false);
                            } else 
                            {
                                $tpl->set_var("idea_donation_rel", "");
                                $tpl->set_var("idea_donation_url", "");
                                $tpl->set_var("SezDonationButtonHide", "");
                                $tpl->set_var("SezDonation", "");
                                $tpl->set_var("container_donation_active", "inactive");
                                $tpl->parse("SezDonationSpanHide", false);
                            }

                        } else 
                        {
                            $tpl->set_var("idea_donation_rel", "");
                            $tpl->set_var("idea_donation_url", "");
                            $tpl->set_var("SezDonationButtonHide", "");
                            $tpl->set_var("container_donation_active", "inactive");
                            $tpl->parse("SezDonationSpanHide", false);
                            $tpl->set_var("SezDonation", "");
                        }
                        $tpl->parse("SezDonationActive", false);
                    } else
                    {
                        $tpl->set_var("SezPledgeActive", "");
                    }
                    if($idea_value["enable_equity"])
                    {
                        if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT")) 
                        {
                            if(global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) {
                                $tpl->set_var("idea_invest_url", "ff.ffPage.dialog.doOpen('BackersModify', '" . FF_SITE_PATH . $crowdfund_invest_path . "/" . $idea_value["smart_url"] . "?ret_url=" . $_SERVER["REQUEST_URI"] . "');");
                            } else {
                                $tpl->set_var("idea_invest_url", "window.location.href = '" . FF_SITE_PATH . $crowdfund_invest_path . "/" . $idea_value["smart_url"] . "';");
                            }
                            $tpl->set_var("idea_invest_rel", "");
                            $tpl->parse("SezInvestButtonHide", false);
                            $tpl->set_var("SezInvestSpanHide", "");
                            $tpl->set_var("container_equity_active", "");
                            $tpl->set_var("idea_equity", $idea_value["equity"] . "%");
                            $tpl->parse("SezInvestCircPercent", false); 
                            $tpl->parse("SezInvest", false);
                        }  elseif(0 && global_settings("MOD_CROWDFUND_IDEA_FORM_INVEST") > 0) //NON voglio form
                        {
                            $tpl->set_var("idea_invest_rel", ' rel="L' . global_settings("MOD_CROWDFUND_IDEA_FORM_INVEST") . '" ');
                            $tpl->set_var("idea_invest_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_INVEST") . "', '" . $idea_value["smart_url"] . "', 'equity', '" . $idea_value["symbol"] . "');");
                            $tpl->parse("SezInvestButtonHide", false);
                            $tpl->set_var("SezInvestSpanHide", "");
                            $tpl->set_var("container_equity_active", "");
                            $tpl->set_var("idea_equity", $idea_value["equity"] . "%");
                            $tpl->parse("SezInvestCircPercent", false);
                            $tpl->parse("SezInvest", false);
                        } else 
                        {
                            $tpl->set_var("idea_invest_rel", "");
                            $tpl->set_var("idea_invest_url", "");
                            $tpl->set_var("SezInvestButtonHide", "");
                            $tpl->set_var("container_equity_active", "inactive");
                            $tpl->set_var("idea_equity", "");
                            $tpl->set_var("SezInvestCircPercent", "");
                            $tpl->parse("SezInvestSpanHide", false); 
                            $tpl->set_var("SezInvest", "");
                        }
                    } else 
                    {
                        $tpl->set_var("idea_invest_url", "");
                        $tpl->set_var("idea_invest_rel", "");
                        $tpl->set_var("SezInvestButtonHide", "");
                        $tpl->set_var("SezInvest", "");
                        $tpl->set_var("container_equity_active", "inactive");
                        $tpl->set_var("idea_equity", "");
                        $tpl->set_var("SezInvestCircPercent", "");
                        $tpl->parse("SezInvestSpanHide", false); 
                    }
                    if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE"))
                    {
                        if($idea_value["enable_pledge"]) 
                        {
                            if(is_array($pledge) && count($pledge))
                            {
                                $tpl->parse("SezPledgeButtonHide", false);
                                $tpl->set_var("SezPledgeSpanHide", "");
                                $tpl->set_var("container_pledge_active", "");
                                $tpl->parse("SezPledge", false);
                            }else 
                            {
                                $tpl->set_var("SezPledge", "");
                                $tpl->set_var("SezPledgeButtonHide", "");
                                $tpl->set_var("container_pledge_active", "inactive");
                                $tpl->parse("SezPledgeSpanHide", false);  
                            }
                        } else
                        {
                            $tpl->set_var("SezPledge", "");
                            $tpl->set_var("SezPledgeButtonHide", "");
                            $tpl->set_var("container_pledge_active", "inactive");
                            $tpl->parse("SezPledgeSpanHide", false);  
                        }
                        $tpl->parse("SezPledgeActive", false);
                    } else
                    {
                        $tpl->set_var("SezPledgeActive", "");
                    }
                } else
                {
                    if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_DONATION"))
                    {
                        $tpl->set_var("SezDonation", "");
                        $tpl->set_var("idea_donation_rel", "");
                        $tpl->set_var("idea_donation_url", "");
                        $tpl->set_var("SezDonationButtonHide", "");
                        $tpl->set_var("container_donation_active", "inactive");
                        $tpl->parse("SezDonationSpanHide", false);
                        $tpl->parse("SezDonationActive", false);
                    } else
                    {
                        $tpl->set_var("SezDonationActive", "");
                    }

                    $tpl->set_var("SezInvest", "");
                    $tpl->set_var("idea_invest_rel", "");
                    $tpl->set_var("idea_invest_url", "");
                    $tpl->set_var("SezInvestButtonHide", "");
                    $tpl->set_var("container_equity_active", "inactive");
                    $tpl->set_var("idea_equity", "");
                    $tpl->set_var("SezInvestCircPercent", "");
                    $tpl->parse("SezInvestSpanHide", false);

                    if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE"))
                    {
                        $tpl->set_var("SezPledge", "");
                        $tpl->set_var("container_pledge_active", "inactive");
                        $tpl->parse("SezPledgeSpanHide", false);  
                        $tpl->parse("SezPledgeActive", false);
                    } else
                    {
                        $tpl->set_var("SezPledgeActive", "");
                    }
                }
                $tpl->parse("SezOfferButtonClick", false);
				}
            } else {
                $tpl->set_var("container_equity_active", "inactive");
                $tpl->set_var("idea_equity", "");
                $tpl->set_var("SezInvestCircPercent", "");
                $tpl->set_var("SezOfferButtonClick", "");
            } 
            $tpl->set_var("SezIdeaEnd", "");
            $cm->oPage->tplAddJs("idea-offer"
                , array(
                    "file" => "idea-offer.js"
                    , "path" => "/modules/crowdfund/themes/javascript"
            ));
            }
            $author = mod_crowdfund_get_author($idea_value["owner"]);
			if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EMAIL") && strlen($author["email"])) 
			{
				if(global_settings("MOD_CROWDFUND_IDEA_FORM_REQUEST_INFO") > 0) {
					$tpl->set_var("form_id", global_settings("MOD_CROWDFUND_IDEA_FORM_REQUEST_INFO"));
					if($UserID == MOD_SEC_GUEST_USER_NAME)
						$tpl->set_var("idea_question_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . MOD_CROWDFUND_QUESTION_PATH . "/" . $idea_value["smart_url"] . "?ret_url=" . $_SERVER["REQUEST_URI"]) . "';");
					else
						$tpl->set_var("idea_question_url", "ff.ffPage.dialog.doOpen('RequestInfo', '" . FF_SITE_PATH . MOD_CROWDFUND_QUESTION_PATH . "/" . $idea_value["smart_url"] . "?ret_url=" . $_SERVER["REQUEST_URI"] . "');");
					$tpl->parse("SezRequestInfo", global_settings("MOD_CROWDFUND_IDEA_FORM_REQUEST_INFO"));
				} else 
				{
					$tpl->set_var("SezRequestInfo", "");
				}
			} else
			{
				$tpl->set_var("SezRequestInfo", "");
			}
			$tpl->set_var("crowdfund_services",  MOD_CROWDFUND_SERVICES_PATH);
			$tpl->set_var("idea_smart_url_base", $idea_value["smart_url"]); 

			$value_business_plan = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "businessplan");
			if($value_business_plan && $decide_businessplan)// 
			{
				$tpl->parse("SezBusinessPlanService", false);
			} else
			{
				$tpl->set_var("SezBusinessPlanService", "");
			}

			$value_income_statement = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "incomestatement");
			$value_cashflow = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "cashflow");

			if(($value_income_statement || $value_cashflow) && $decide_estimate_budget)
			{
				$tpl->parse("SezEstimateBudgetService", false);
			} else
			{
				$tpl->set_var("SezEstimateBudgetService", "");
			}

			$value_attach = mod_crowdfund_idea_show_tab($idea_value["smart_url"], "attach"); 
			if($value_attach && $decide_attach) 
			{
				$tpl->parse("SezAttachService", false);
			} else
			{
				$tpl->set_var("SezAttachService", "");
			} 

			if (($idea_value["count_follower"] || $idea_value["count_backer"]) && $decide_backer_follower)// 
			{
				$tpl->parse("SezBackerFollowerService", false);
			} else
			{
				$tpl->set_var("SezBackerFollowerService", "");
			}

			$qa_value = mod_crowdfund_get_qa($ID_idea);
			if (is_array($qa_value) && count($qa_value) && $decide_qa)
			{
				$tpl->parse("SezQAService", false);
			} else
			{
				$tpl->set_var("SezQAService", "");
			}
			
			$timeline_value = mod_crowdfund_create_timeline($ID_idea);
			if (is_array($timeline_value) && count($timeline_value))
			{ 
				$tpl->parse("SezTimelineService", false);
			} else
			{
				$tpl->set_var("SezTimelineService", "");
			}
		
			$team_value = mod_crowdfund_get_team($ID_idea);
			if (is_array($team_value) && count($team_value))
			{ 
				$tpl->parse("SezTeamService", false);
			} else
			{
				$tpl->set_var("SezTeamService", "");
			}

			if(strlen($idea_value["cover"])) {
				$count_slider++;
				$tpl->set_var("idea_cover", FF_SITE_PATH . CM_SHOWFILES . "/" . $ico_mode . $idea_value["cover"]);
			} else {
				$tpl->set_var("idea_cover", FF_SITE_PATH . CM_SHOWFILES . "/" . THEME_INSET . "/images/spacer.gif");
			}


			$tpl->set_var("idea_title", $idea_value["title"]);
			//$tpl->set_var("idea_teaser", ishort_description($idea_value["teaser"], 200, "Text"));
			$tpl->set_var("idea_teaser", $idea_value["teaser"]);
			$tpl->set_var("idea_description", $idea_value["description"]);

			$tpl->set_var("idea_goal_current", $idea_value["goal_current"]);
			$tpl->set_var("idea_goal", $idea_value["goal"]);
			$tpl->set_var("idea_goal_perc", $idea_value["goal_perc"]);


			$tpl->set_var("idea_currency_symbol", $idea_value["symbol"]);
			
			if($idea_value["expire"] > 0)
			{
				$tpl->set_var("idea_expiration", $idea_value["expire"]);
				$tpl->parse("SezTimeRes", false);
			} else
			{
				$tpl->set_var("SezTimeRes", "");
			}
			if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY_COMPLEX") || $value_income_statement || $value_cashflow) //in attesa di altre condizioni 
			{
				$chart_value = array
				( 
					"income_statement" => mod_crowdfund_get_income_statement($tmp_idea["ID"], false, false)
					, "cash_flow" => mod_crowdfund_get_cash_flow($tmp_idea["ID"], false, false, false)
				);
				if(is_array($chart_value) && ($value_income_statement || $value_cashflow))
				{
					$tpl->set_var("idea_summary_chart", mod_crowdfund_process_chart($smart_url, "chart-summary", true));
					$tpl->parse("SezSummaryChart", false); 
				} else
				{
					$tpl->set_var("SezSummaryChart", ""); 
				}


				if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_EQUITY_COMPLEX")) 
				{
					if ($idea_value["is_startup"])
					{
						$tpl->set_var("SezNotStartUpCapital", "");
						$tpl->parse("SezStartUpCapital", false);  
					} else
					{
						$tpl->set_var("SezStartUpCapital", "");
						$tpl->parse("SezNotStartUpCapital", false);
					}

					if (!(int)$idea_value["capital_funded"])
					{
						$tpl->set_var("SezCapital", "");
						$tpl->parse("SezNoCapital", false);
					} else 
					{
						$tpl->set_var("SezNoCapital", "");
						$tpl->set_var("idea_capital", $idea_value["capital_funded"]);
						$tpl->parse("SezCapital", false);
					}
					$tpl->set_var("idea_total_capital", $idea_value["total_capital"]);
					$tpl->parse("SezSummaryCapital", false);
				} else
				{
					$tpl->set_var("SezSummaryCapital", "");
				}
				$tpl->parse("SezSummary", false);
			} else 
			{
				$tpl->set_var("SezSummary", "");
			}
			$tpl->set_var("idea_count_follower", $idea_value["count_follower"]);
			$tpl->set_var("idea_count_backer", $idea_value["count_backer"]);


			if(is_array($author) && count($author)) 
			{
				if ($author["public"])
				{
					$tpl->set_var("owner_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $author["slug"]);
					$tpl->parse("SezOwnerProfileLink", false);
				}
				else
				{
					$tpl->set_var("SezOwnerProfileLink", ""); 
				}
				if(check_function("get_user_avatar")) {
					$tpl->set_var("owner_avatar", get_user_avatar($author["avatar"], false, $author["email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_OWNER_ICO")));
					$tpl->parse("SezOwnerAvatar", false);
				}
				$tpl->set_var("owner_reference", $author["reference"]);

				if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_EMAIL") && strlen($author["email"])) {
					$tpl->set_var("owner_email", $author["email"]);
					$tpl->parse("SezOwnerEmail", false);
				} else {
					$tpl->set_var("SezOwnerEmail", "");
				}
				$tpl->set_var("current_project", $author["current_project"]);
				$tpl->set_var("completed_project", $author["completed_project"]);

				if(is_array($author["custom"]) && count($author["custom"])) 
				{
					foreach($author["custom"] AS $custom_key => $custom_value) 
					{
						if ($custom_key != "other" && $custom_key != "Biography")
						{
							if(strlen($custom_value)) 
							{
								if(strpos($custom_value, "http") === 0)
								{
									$tpl->set_var("owner_" . strtolower($custom_key), $custom_value);
								} else 
								{
									$tpl->set_var("owner_" . strtolower($custom_key), "http://" . $custom_value);
								}
							} else {
								if($custom_key == "website")
								{ 
									$tpl->set_var("SezOwner" . ucfirst($custom_key), "");
									continue;
								}
								$tpl->set_var("owner_" . strtolower($custom_key), "javascript:void(0);");
								$tpl->set_var("has_" . strtolower($custom_key), "na");
							}
							$tpl->parse("SezOwner" . ucfirst($custom_key), false);
						}
					}
				}
				if(global_settings("MOD_CROWDFUND_ENABLE_TEAM"))
				{
					$team = mod_crowdfund_get_team($ID_idea);
					$i = 1;
					if(is_array($team) && count($team)) {
						foreach($team AS $mamber_key => $member_value)
						{
							if(check_function("get_user_avatar")) 
							{ 
								$tpl->set_var("member_avatar", get_user_avatar($member_value["member_avatar"], false, $member_value["member_email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_TEAM_ICO")));
							}
							$tpl->set_var("member_reference", $member_value["member_reference"]);
							if($member_value["public"])
							{
								$tpl->set_var("member_team_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $member_value["slug"]);
								$tpl->parse("SezTeamMemberHeader", false);
								$tpl->parse("SezTeamMemberFooter", false);
							} else
							{
								$tpl->set_var("SezTeamMemberHeader", "");
								$tpl->set_var("SezTeamMemberFooter", "");
							}
							$tpl->set_var("team_column", "col" . $i);
							if($i < 4)
							{
								$i++;
							} else
							{
								$i = 1;
							}
							$tpl->parse("SezTeamMember", true);
						}
						$tpl->parse("SezTeam", false);
					}
				} else
				{
					$tpl->set_var("SezTeam", "");
				}

				$tpl->parse("SezOwner", false);		
			} else {
				$tpl->set_var("SezOwner", "");
			}



			
			if(is_array($company) && count($company)) {
				if (global_settings("MOD_CROWDFUND_IDEA_ENABLE_COMPANY_DETAIL"))
				{
					$tpl->set_var("company_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $company["slug"]);
					$tpl->parse("SezCompanyProfileLink", false);
				} else
				{
					$tpl->set_var("SezCompanyProfileLink", "");
				}
				if(check_function("get_user_avatar")) {
					$tpl->set_var("company_avatar", get_user_avatar($company["avatar"], false, false, FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_COMPANY_ICO")));
					$tpl->parse("SezCompanyAvatar", false);
				}
				$tpl->set_var("company_reference", $company["billreference"]);
				$tpl->set_var("company_piva", $company["billpiva"]);
				$tpl->set_var("company_cf", $company["billcf"]);
				$tpl->set_var("company_address", $company["billaddress"]);
				$tpl->set_var("company_cap", $company["billcap"]);
				$tpl->set_var("company_city", $company["billtown"]);
				$tpl->set_var("company_province", $company["billprovince"]);
				$tpl->set_var("company_state", $company["billstate"]);

				if(is_array($company["custom"]) && count($company["custom"])) {
					foreach($company["custom"] AS $custom_key => $custom_value) {
						if(strlen($custom_value)) {
							$tpl->set_var("company_" . $custom_key, $custom_value);
							$tpl->parse("SezCompany" . ucfirst($custom_key), false);
						} else {
							$tpl->set_var("SezCompany" . ucfirst($custom_key), "");
						}
					}
				}
				$tpl->parse("SezCompany", false);		
			} else {
				$tpl->set_var("SezCompany", "");
			}

			if(strlen($idea_value["skype_account"]) && global_settings("MOD_CROWDFUND_IDEA_ENABLE_SKYPE")) {
				$tpl->set_var("idea_skype_account", $idea_value["skype_account"]);
				$tpl->parse("SezSkypeChat", false);
			} else {
				$tpl->set_var("SezSkypeChat", "");
			}

			if($is_detail) { 
				if(is_array($pledge) && count($pledge) && $idea_value["enable_pledge"] && global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE") && !$offer) 
				{
					$switch_style = "positive";
					foreach ($pledge AS $pledge_key => $pledge_value)  
					{
						if ($pledge_value["backer"] && !$pledge_value["disabled"])
						{
							$tpl->set_var("pledge_available", "available");
							if (get_session("UserID") == MOD_SEC_GUEST_USER_NAME && global_settings("MOD_CROWDFUND_IDEA_ENABLE_PLEDGE_DECISION"))
							{
								$tpl->set_var("idea_pledge_url", "window.location.href = '" . FF_SITE_PATH . "/login?ret_url=" . urlencode(FF_SITE_PATH . $crowdfund_public_path . "/" . $idea_value["smart_url"]) . "';");
								$tpl->set_var("idea_pledge_rel", "");
								$tpl->parse("SezPledgeAvailableHeader", false);
								$tpl->parse("SezPledgeAvailableFooter", false);
							} else
							{
								if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_PAYMENT") && $idea_value["ID_anagraph_company"] > 0 && $idea_value["ID"] == 88) 
								{
									if(global_settings("MOD_CROWDFUND_IDEA_PAYMENT_USE_DIALOG")) {
										$tpl->set_var("idea_pledge_url", "ff.ffPage.dialog.doOpen('BackersModify', '" . FF_SITE_PATH . $crowdfund_reward_path . "/" . $idea_value["smart_url"] . "?price=" . $pledge_value["price"] . "');");
									} else {
										$tpl->set_var("idea_pledge_url", "window.location.href = '" . FF_SITE_PATH . $crowdfund_reward_path . "/" . $idea_value["smart_url"] . "?price=" . $pledge_value["price"] . "';");
									}
									$tpl->set_var("idea_pledge_rel", "");
									$tpl->parse("SezPledgeAvailableHeader", false);
									$tpl->parse("SezPledgeAvailableFooter", false);
								} elseif(global_settings("MOD_CROWDFUND_IDEA_FORM_REWARD") > 0) 
								{
									$pledge_price = new ffData($pledge_value["price"], "Number");
									$tpl->set_var("idea_pledge_rel", ' rel="L' . global_settings("MOD_CROWDFUND_IDEA_FORM_REWARD") . '" ');
									$tpl->set_var("idea_pledge_url", "CFideaDialog('" . global_settings("MOD_CROWDFUND_IDEA_FORM_REWARD") . "', '" . $idea_value["smart_url"] . "', 'pledge', '" . $pledge_price->getValue("Number", FF_SYSTEM_LOCALE) . "', '" . $pledge_price->getValue("Currency", FF_LOCALE) . " " . $idea_value["symbol"] . "');");
									$tpl->parse("SezPledgeAvailableHeader", false);
									$tpl->parse("SezPledgeAvailableFooter", false);
								} else 
								{
									$tpl->set_var("SezPledgeAvailableHeader", "");
									$tpl->set_var("SezPledgeAvailableFooter", "");
								}
							}  
						} else  
						{
							$tpl->set_var("pledge_available", "not-available");
							$tpl->set_var("SezPledgeAvailableHeader", "");
							$tpl->set_var("SezPledgeAvailableFooter", "");
						}							


						$tpl->set_var("pledge_available_backers", $pledge_value["backer"]);
						$tpl->set_var("pledge_price", $pledge_value["price"]);
						if ($pledge_value["backers"])
						{
							$tpl->set_var("pledge_backers", $pledge_value["backers"]);
							$tpl->parse("SezPledgeItemBackers", false);
						} else
						{
							$tpl->set_var("SezPledgeItemBackers", "");
						}
						if ($pledge_value["limit"]) 
						{
							$tpl->set_var("pledge_limit", $pledge_value["limit"]);
							$tpl->parse("SezPledgeItemLimit", false);
						} else
						{
							$tpl->set_var("SezPledgeItemLimit", "");
						}

						$tpl->set_var("pledge_description", $pledge_value["description"]);
						if ($switch_style == "negative")
						{
							$switch_style = "positive";
						} else 
						{
							$switch_style = "negative";
						}
						$tpl->set_var("switch_style", $switch_style);
						$tpl->parse("SezPledgeItem", true);
					}
					$tpl->parse("SezPledge", false);
				} else {
					$tpl->set_var("SezPledge", "");
				}

				$attach = mod_crowdfund_get_attach($ID_idea);
				if(is_array($attach) && count($attach)) 
				{
					foreach($attach AS $attach_key => $attach_value) 
					{
						if(strlen($attach_value["file"]) && is_file(DISK_UPDIR . $attach_value["file"])) {
							$file_ext = cm_get_filename($attach_value["file"], false);
							if($file_ext == "jpg"
								|| $file_ext == "png"
								|| $file_ext == "gif"
							) {
								$tpl->set_var("idea_gallery_attach", FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_GALLERY_ICO") . $attach_value["file"]);
								$tpl->set_var("idea_gallery_attach_name", $attach_value["title"]);
								$tpl->parse("SezGalleryItem", true);

								$count_slider++;
							}
						}
					}
				}
				if(strlen($idea_value["video"])) 
				{
					$count_slider++;
					$display_video = true;

					$tpl->set_var("idea_video", $idea_value["video"]);
					$tpl->parse("SezVideo", false);
				} 
				else 
				{
					$display_video= false;
					$tpl->set_var("SezVideo", "");
				}

				if($count_slider) 
				{
					$tpl->set_var("idea_cover", FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_GALLERY_ICO") . $idea_value["cover"]);

					if($count_slider > 1) 
					{
						if($display_video) 
						{
							$cm->oPage->tplAddJs("Froogaloop"
                                , array(
                                    "file" => "froogaloop.js"
                                    ,"path" => FF_THEME_DIR . "/library/plugins/froogaloop"
                            ));
							$tpl->set_var("slidertype", "flexslider video");
						}
						else 
						{
							$tpl->set_var("slidertype", "flexslider");
						}

						$cm->oPage->tplAddJs("jquery.plugins.flexslider");
						$cm->oPage->tplAddJs("idea-detail"
                            , array(
                                "file" => "idea-detail.js"
                                , "path" => "/modules/crowdfund/themes/javascript"
                        ));
						if(check_function("get_resource_cascading")) {
							get_resource_cascading($cm->oPage, "/", "flexslider.css", true, FF_THEME_DIR . "/library/plugins/jquery.flexslider");
						}
						if(strlen($idea_value["cover"])) {
							$tpl->parse("SezCover", false);						
						} else {
							$tpl->set_var("SezCover", "");
						}
					} 
					else 
					{
						$tpl->set_var("slidertype", "gallery");
						$tpl->parse("SezCover", false);
					}
					$tpl->parse("SezGallery", false);
				} 
				else
				{
					$tpl->set_var("SezGallery", "");
				}
			}

			$tpl->parse("SezIdea", true);
			$col++;
			if($col > $max_col)
				$col = 1; 
		}
	$buffer = $tpl->rpparse("main", false);
	}
	return $buffer;	
}

function control_user_complete_information($ID_user = null, $costante = "", $only_incomplete = false, $db = null)
{
    $cm = cm::getInstance();

    if($db === null)
        $db = ffDb_Sql::factory();
    if($ID_user === null)
        $ID_user = get_session("UserNID");
    
    $account_complete = true;
    
    $options = mod_security_get_settings($cm->path_info);
    
	$sSQL = "SELECT " . $options["table_name"] . ".*
                FROM " . $options["table_name"] . "
                WHERE " . $options["table_name"] . ".ID = " . $db->toSql($ID_user, "Number");
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $userInfo = array (
			"name" => $db->getField("name" , "Text", true)
			, "surname" => $db->getField("surname" , "Text", true)
			, "email" => $db->getField("email" , "Text", true)
			, "billcf" => $db->getField("billcf" , "Text", true)
			, "billaddress" => $db->getField("billaddress" , "Text", true)
			, "billtown" => $db->getField("billtown" , "Text", true)
			, "billcap" => $db->getField("billcap" , "Text", true)
			, "billprovince" => $db->getField("billprovince" , "Text", true)
		);
    }
    
    $sSQL = "SELECT " . $options["table_dett_name"] . ".value
					, " . $options["table_dett_name"] . ".field
                FROM " . $options["table_dett_name"] . "
                WHERE " . $options["table_dett_name"] . ".ID_users = " . $db->toSql($ID_user, "Number");
    $db->query($sSQL);
    if($db->nextRecord())
    {
		do {
			$userInfo[$db->getField("field", "Text", true)] = $db->getField("value" , "Text", true);
		} while ($db->nextRecord());
	}
	
	if(strlen($costante))
	{
		$information_user_required = explode(",",MOD_CROWDFUND_USER_INFO);
		if(is_array($information_user_required) && count($information_user_required))
		{
            $infoUserIncomplete = "";
			foreach($information_user_required AS $key => $field) 
			{
				if(array_key_exists($field, $userInfo) && $userInfo[$field] != "")
				{
					continue;

				} else
				{
					if(strlen($infoUserIncomplete))
						$infoUserIncomplete .= ", ";
					$infoUserIncomplete .= ffTemplate::_get_word_by_code($field);
					$arrInfoUserIncomplete["user_info"][$field] = 0;
				}
			}
		}
	
		$information_investment_required = explode(",",$costante);
		if(is_array($information_investment_required) && count($information_investment_required))
		{
			foreach($information_investment_required AS $key => $field) 
			{
				if(array_key_exists($field, $userInfo) && $userInfo[$field] != "" && $userInfo[$field] != "0")
				{
					continue;

				} else
				{
					if(strlen($infoUserIncomplete))
						$infoUserIncomplete .= ", ";
					$infoUserIncomplete .= ffTemplate::_get_word_by_code($field);
					$arrInfoUserIncomplete["relative_field"][$field] = 0;
				}
			}
		}
		
	
		
		if($only_incomplete)
		{
			if(strlen($infoUserIncomplete))
			{
				$returnInfo = array(
					"incomplete_string"			=> ffTemplate::_get_word_by_code("check_info_incomplete_prefix")
					, "incomplete"				=> $infoUserIncomplete
					, "arrInfoUserIncomplete"	=> $arrInfoUserIncomplete
					, "url"						=> "/user/profile/account/" . get_session("UserID")
					, "title"					=> ffTemplate::_get_word_by_code("check_missing_info_title")
					, "string"					=> ffTemplate::_get_word_by_code("go_profile")
				);
			}
			
			return $returnInfo;
		}
	}
    
    return $userInfo; 
}

function get_anagraph_ID($ID_user) 
{
	$db = ffDb_Sql::factory();
	
	$sSQL = "SELECT anagraph.ID
				FROM anagraph
				WHERE anagraph.uid = " . $db->toSql($ID_user, "Number") . "
				LIMIT 1";
	$db->query($sSQL);
	if($db->nextRecord())
	{
		$ID_anagraph = $db->getField("ID", "Number", true);
	}
	return $ID_anagraph;
}

function get_mod_security_user_ID($ID_user_anagraph)
{
    $db = ffDb_Sql::factory();
    $UserInfo = array();
    
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.*
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
					, anagraph.email
                FROM " . CM_TABLE_PREFIX . "mod_security_users 
					INNER JOIN anagraph ON anagraph.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID 
                WHERE anagraph.ID = " . $db->toSql($ID_user_anagraph, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $UserInfo["ID_user_anagraph"] = $ID_user_anagraph;
        $UserInfo["UserID"] = $db->getField("username", "Text", true);
        $UserInfo["UserNID"] = $db->getField("ID", "Number", true);
		$UserInfo["anagraph"] = $db->getField("anagraph", "Text", true);
		$UserInfo["email"] = $db->getField("email", "Text", true);
    }

    return $UserInfo;
}

function confirm_dialog_success($name, $string = "", $dialog_name)
{
	$cm = cm::getInstance();

	$filename = cm_cascadeFindTemplate("/contents/confirm_dialog/index.html", "crowdfund");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/confirm_dialog/index.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/confirm_dialog/index.html", $cm->oPage->theme);*/

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file("index.html", "main");
	
	$tpl->set_var("crowdfund_dialog_confirm_success", ffTemplate::_get_word_by_code("crowdfund_dialog_" . $name . "_confirm_success") . $string);
	
	$tpl->set_var("crowdfund_dialog_name", "'" . $dialog_name . "'"); 
	$buffer = $tpl->rpparse("main", false);
		
	return $buffer;
}

function get_team_email($ID_idea)
{
    $db = ffDb_Sql::factory();
    static $recipents_mail = array();
    
    if(!array_key_exists($ID_idea, $recipents_mail)) 
    { 
        if(global_settings("MOD_CROWDFUND_ENABLE_TEAM"))
        {
            $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph
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
                            , anagraph.email
                        FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_user_anagraph
                            INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid 
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . $db->toSql($ID_idea, "Number");
            $db->query($sSQL);
            if($db->nextRecord())
            {
                $i = 0;
                do {
                        $recipents_mail[$ID_idea][$i]["name"] = $db->getField("anagraph", "Text", true);
                        $recipents_mail[$ID_idea][$i]["mail"] = $db->getField("email", "Text", true);
                        $i++;
                } while ($db->nextRecord());
            }
        } else
        {
            $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
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
                            , anagraph.email
                        FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
                            INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
                            INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid 
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_idea, "Number");
            $db->query($sSQL);
            if($db->nextRecord())
            {
                $recipents_mail[$ID_idea][0]["name"] = $db->getField("anagraph", "Text", true);
                $recipents_mail[$ID_idea][0]["mail"] = $db->getField("email", "Text", true);
            }
        }
    }
    return $recipents_mail[$ID_idea];
}
/*
function login_widget($db = null, $path = "/login", ret_url = "")
{
	$userNID = get_session("UserNID");
	$userID = get_session("UserID");
	
	if(!$userNID || $userID == MOD_SEC_GUEST_USER_NAME)
	{
		$cm->oPage->widgetLoad("dialog");
		$cm->oPage->widgets["dialog"]->process(
			 "UserLoginWidget"
			 , array(
				"tpl_id" => null
				//"name" => "myTitle"
				, "url" => ""
				, "title" => ffTemplate::_get_word_by_code("user_login_widget")
				, "callback" => ""
				, "class" => ""
				, "params" => array(
				)
				, "resizable" => true
				, "position" => "center"
				, "draggable" => true
				, "doredirects" => false
			)
			, $cm->oPage
		);
		
		"ff.ffPage.dialog.doOpen('UserLoginWidget', '" . FF_SITE_PATH . $path . "?ret_url=" . $ret_url . "');"
	}
}
*/
?>