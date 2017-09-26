<?php
cm::getInstance()->addEvent("on_before_page_process", "mod_trivia_on_before_page_process", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("on_before_routing", "mod_trivia_on_before_rounting", ffEvent::PRIORITY_NORMAL);
cm::getInstance()->addEvent("mod_security_on_created_session", "mod_trivia_mod_security_on_created_session", ffEvent::PRIORITY_NORMAL);

define("MOD_TRIVIA_PATH", $cm->router->named_rules["trivia"]->reverse); 
define("MOD_TRIVIA_SERVICES_PATH", $cm->router->named_rules["trivia_services"]->reverse);

if(!function_exists("global_settings")) {
	function global_settings($key = null) {
		static $global_settings = false;

		if($global_settings === false) {
			$global_settings = mod_restricted_get_all_setting();
		}

		if(array_key_exists($key, $global_settings)) {
			return $global_settings[$key];
		} else {
			return null;
		}
	}	
}

function mod_trivia_on_before_page_process($cm) {
	$globals = ffGlobals::getInstance("gallery"); 
	
	if(strpos($cm->path_info, MOD_TRIVIA_PATH) !== false) {
        if(strlen(global_settings("MOD_TRIVIA_THEME")) && is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . global_settings("MOD_TRIVIA_THEME"))) {
		    $cm->layout_vars["theme"] = global_settings("MOD_TRIVIA_THEME");
        }
	}
}

function mod_trivia_on_before_rounting($cm) {
	$permission = check_trivia_permission();
	if($permission !== true
		&&
		(!(is_array($permission) && count($permission)
			&& ($permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")]
			)
		))
	) {
    	$cm->modules["restricted"]["menu"]["trivia"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["trivia"]["elements"]["question"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["trivia"]["elements"]["categories"]["hide"] = true;
		$cm->modules["restricted"]["menu"]["trivia"]["elements"]["achievement"]["hide"] = true;
	} else {
		if(strpos($cm->path_info, MOD_TRIVIA_PATH) !== false) {
            if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "library/jquery.ui/themes/" . global_settings("MOD_TRIVIA_JQUERYUI_THEME"))) {
    		    $cm->oPage->jquery_ui_force_theme = global_settings("MOD_TRIVIA_JQUERYUI_THEME");
            }

			if($permission["primary_group"] != global_settings("MOD_TRIVIA_GROUP_ADMIN")) {
				$cm->modules["restricted"]["menu"]["trivia"]["elements"]["categories"]["hide"] = false;
				$cm->modules["restricted"]["menu"]["trivia"]["elements"]["question"]["hide"] = false;
				$cm->modules["restricted"]["menu"]["trivia"]["elements"]["achievement"]["hide"] = false;
			}
			if($permission["primary_group"] != global_settings("MOD_TRIVIA_GROUP_USER")) {
				
			}
			
			if(function_exists("check_function") && check_function("system_set_js")) {
				system_set_js($cm->oPage, $cm->path_info, false, "/modules/trivia/themes/javascript", true);
			}
		}
	}
}

function check_trivia_permission($check_group = null) {
	if(!MOD_SEC_GROUPS) 
		return true;

    $db = ffDB_Sql::factory();

    $user_permission = get_session("user_permission");
    $userID = get_session("UserID");

    if(is_array($user_permission) && count($user_permission) 
    	&& is_array($user_permission["groups"]) && count($user_permission["groups"])
    	&& $userID != MOD_SEC_GUEST_USER_NAME
    ) {
    	if(!array_key_exists("permissions_custom", $user_permission))
            $user_permission["permissions_custom"] = array();

	    if(!(array_key_exists("trivia", $user_permission["permissions_custom"]) && count($user_permission["permissions_custom"]["trivia"]))) {
	    	$user_permission["permissions_custom"]["trivia"] = array();
	    	
		    $strGroups = implode(",", $user_permission["groups"]);
			$strPermission = $db->toSql(global_settings("MOD_TRIVIA_GROUP_ADMIN"), "Text") 
							. "," . $db->toSql(global_settings("MOD_TRIVIA_GROUP_USER"), "Text"); 

		    $user_permission["permissions_custom"]["trivia"][global_settings("MOD_TRIVIA_GROUP_ADMIN")] = false;
		    $user_permission["permissions_custom"]["trivia"][global_settings("MOD_TRIVIA_GROUP_USER")] = false;
		    $user_permission["permissions_custom"]["trivia"]["primary_group"] = "";
		    
		    $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.name
		                , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
		            FROM " . CM_TABLE_PREFIX . "mod_security_groups
		              INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
		            WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
		              AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN ( " . $strPermission . " )";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
				do {
			        $user_permission["permissions_custom"]["trivia"][$db->getField("name", "Text", true)] = true;
			        $user_permission["permissions_custom"]["trivia"]["primary_group"] = $db->getField("name", "Text", true);
				} while($db->nextRecord());
		    }
		    
	        set_session("user_permission", $user_permission);
		}
		if($check_group === null) { 
	    	return $user_permission["permissions_custom"]["trivia"];
		} else {
			return $user_permission["permissions_custom"]["trivia"]["primary_group"];
		}
	}    
    return null;
}

function mod_trivia_mod_security_on_created_session($UserID, $UserNID, $Domain, $DomainID, $old_session_id) {
	$user_permission = get_session("user_permission");

	if($user_permission["primary_gid_name"] == global_settings("MOD_TRIVIA_GROUP_ADMIN")) {
		if(strlen(MOD_TRIVIA_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_TRIVIA_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_TRIVIA_PATH);
			}
		}
	}

	if(strtolower($user_permission["primary_gid_name"]) == "trivia"
		|| $user_permission["primary_gid_name"] == global_settings("MOD_TRIVIA_GROUP_USER")
	) {
		if(strlen(MOD_TRIVIA_PATH)) {
			if(strpos($_REQUEST["ret_url"], MOD_TRIVIA_PATH) !== 0) {
				ffRedirect(FF_SITE_PATH . MOD_TRIVIA_PATH);
			}
		}
	}
}

cm::getInstance()->addEvent("vg_on_user_profile_processed", "mod_trivia_vg_on_user_profile_processed");

function mod_trivia_vg_on_user_profile_processed($cm, $oRecord, $UserID) {
	$arrLevel = array();
	$user_achievement = mod_trivia_get_user_achievement($UserID, false, true, true);
	
	if(is_array($user_achievement) && count($user_achievement)) {
        $filename = cm_cascadeFindTemplate("/contents/achievement.html", "trivia");
	    /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/trivia/contents/achievement.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/trivia/themes", "/achievement.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file(basename($filename), "main");

		$i=1;

		foreach ($user_achievement AS $category_name => $achievement)
		{
			foreach ($achievement AS $key => $value)
			{
				if(strlen($value["level_name"])) {
					$arrLevel[ffCommon_url_rewrite($value["level_name"])] = $value["level_name"];
					$tpl->set_var("trivia_level_class", ffCommon_url_rewrite($value["level_name"]));
				} else {
					$tpl->set_var("trivia_level_class", "all");
				}
				$tpl->set_var("trivia_new_achievement_number", $i);
				$tpl->set_var("trivia_achievement_title", $value["name"]);
				
				if(strlen($value["file"]) && is_file(DISK_UPDIR . $value["file"])) {
					$achievement_img = CM_SHOWFILES . $value["file"];
				} else {
					$achievement_img = FF_SITE_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/images/spacer.gif";
				}
				$tpl->set_var("trivia_achievement_file", $achievement_img);
				$tpl->set_var("trivia_achievement_description", $value["description"]);
				
				if($value["is_set"]) {
					$tpl->set_var("trivia_achievement_acquired", " acquired");
				} else {
					$tpl->set_var("trivia_achievement_acquired", "");
				} 
				
				$tpl->parse("SezAchievementItem", true);

				$i++;
			}
			if(strlen($category_name)) {
				$tpl->set_var("trivia_category_class", ffCommon_url_rewrite($category_name));
				$tpl->set_var("trivia_category_title", $category_name);
			} else {
				$tpl->set_var("trivia_category_class", "all");
				$tpl->set_var("trivia_category_title", ffTemplate::_get_word_by_code("trivia_achievement_nocategory"));
			}
			$tpl->parse("SezCategory", true);
			$tpl->set_var("SezAchievementItem", "");
		}
		
		if(is_array($arrLevel) && count($arrLevel)) {
			foreach($arrLevel AS $level_key => $level_value) {
				$tpl->set_var("trivia_level_class", $level_key);
				$tpl->set_var("trivia_level_name", $level_value);
				$tpl->parse("SezLevelMenuItem", true);
			}
			$tpl->parse("SezLevelMenu", false);
		}		
		$buffer = $tpl->rpparse("main", false);	
	}

    if(strlen($buffer)) {
        $cm->oPage->addContent($buffer);
    }

}

function mod_trivia_control_smart_url($table)
{
	$db = ffDB_Sql::factory();
	$sSQL = "UPDATE " . CM_TABLE_PREFIX . $table . "
				SET " . CM_TABLE_PREFIX . $table . ".smart_url = 
					CONCAT( " . CM_TABLE_PREFIX . $table . ".smart_url, '-', " . CM_TABLE_PREFIX . $table . ".ID)
				WHERE " . CM_TABLE_PREFIX . $table . ".smart_url IN
											(
												SELECT smart_url
												FROM	(SELECT " . CM_TABLE_PREFIX . $table . ".smart_url AS smart_url
															, COUNT(" . CM_TABLE_PREFIX . $table . ".ID) AS answer_count
															FROM " . CM_TABLE_PREFIX . $table . "
															WHERE 1
															GROUP BY " . CM_TABLE_PREFIX . $table . ".smart_url
															HAVING answer_count > 1
														) AS tbl

											)";
	$db->execute($sSQL);
}


function mod_trivia_init_score($ID_question, $ID_category = 0, $ID_level = 0, $ID_answer = 0) {
	$db = ffDB_Sql::factory();
	
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_score.*
			FROM " . CM_TABLE_PREFIX . "mod_trivia_score
			WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql(get_session("UserNID"), "Number") . "
				AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_category = " . $db->toSql($ID_category, "Number") . "
				AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_level = " . $db->toSql($ID_level, "Number") . "
				AND " . CM_TABLE_PREFIX . "mod_trivia_score.archived = 0";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$ID_score = $db->getField("ID", "Number", true);
	} else {
		$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_trivia_score
				(
					ID
					, ID_user
					, ID_category
					, ID_level
					, score
					, archived
				)
				VALUES
				(
					null
					, " . $db->toSql(get_session("UserNID"), "Number") . "
					, " . $db->toSql($ID_category, "Number") . "
					, " . $db->toSql($ID_level, "Number") . "
					, 0
					, 0
				)";
		$db->execute($sSQL);
		$ID_score = $db->getInsertID(true);
	}

	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_score_answer.*
			FROM " . CM_TABLE_PREFIX . "mod_trivia_score_answer
			WHERE " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_score = " . $db->toSql($ID_score, "Number") . "
				AND " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_question = " . $db->toSql($ID_question, "Number") ."
			ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID DESC";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$ID_question_score = $db->getField("ID", "Number", true);

		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_trivia_score_answer SET
					" . ($ID_answer > 0
						? CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_answer = " . $db->toSql($ID_answer, "Number") . "
							, " . CM_TABLE_PREFIX . "mod_trivia_score_answer.answer_time = " . $db->toSql(time(), "Number") . "
							, " . CM_TABLE_PREFIX . "mod_trivia_score_answer.is_right = (SELECT IF(" . CM_TABLE_PREFIX . "mod_trivia_question.ID_answer_right = " . $db->toSql($ID_answer, "Number") . "
																									, 1
																									, 0
																								)
																							FROM " . CM_TABLE_PREFIX . "mod_trivia_question
																							WHERE " . CM_TABLE_PREFIX . "mod_trivia_question.ID = " . $db->toSql($ID_question, "Number") . "
																						) "
						: CM_TABLE_PREFIX . "mod_trivia_score_answer.question_time = " . $db->toSql(time(), "Number")
					) . "
				WHERE " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID = " . $db->toSql($ID_question_score, "Number");
		$db->execute($sSQL);
	} else {
		$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_trivia_score_answer
				(
					ID
					, ID_score
					, ID_answer
					, ID_question
					, question_time
					, answer_time
					, is_right

				)
				VALUES
				(
					null
					, " . $db->toSql($ID_score, "Number") . "
					, " . $db->toSql($ID_answer, "Number") . "
					, " . $db->toSql($ID_question, "Number") . "
					, " . $db->toSql(time(), "Number") . "
					, " . $db->toSql(($ID_answer > 0 ? time() : 0), "Number") . "
					, 0
				)";
		$db->execute($sSQL);
	}
}

//prendo tutti le risposte corrette dell'utente, le metto in un array strutturato e ritorno il massimo
//punteggio per ciascuna domanda di ciascun livello di ogni cateria(a cui l'utente abbia risposto
//correttamente almeno una volta) oltre che il numero di diverse domande a cui ha risposto
function mod_trivia_get_answer_score($ID_user)
{
	$db = ffDB_Sql::factory();
	static $answer_score = array();
	
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_score.*
				, " . CM_TABLE_PREFIX . "mod_trivia_score_answer.*
				FROM " . CM_TABLE_PREFIX . "mod_trivia_score_answer
					INNER JOIN " . CM_TABLE_PREFIX . "mod_trivia_score ON " . CM_TABLE_PREFIX . "mod_trivia_score.ID = " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_score
				WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql($ID_user, "Number") ."
					AND " . CM_TABLE_PREFIX . "mod_trivia_score_answer.is_right = 1
				ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_score_answer.answer_time - " . CM_TABLE_PREFIX . "mod_trivia_score_answer.question_time DESC";
	$db->query($sSQL);
	if($db->nextRecord()) 
	{
		do
		{
			$ID_question = $db->getField("ID_question", "Number", true);
			$level = $db->getField("ID_level", "Number", true);
			$category = $db->getField("ID_category", "Number", true);
			$time = $db->getField("answer_time", "Number", true) - $db->getField("question_time", "Number", true);
			$answer_score[$ID_user][$category][$level][$ID_question] = $time;
			$answer_score[$ID_user]["total-answer"][] = $ID_question;
		} while($db->nextRecord());  
	}
	return $answer_score[$ID_user];
}

//salvo in un array tutti i punteggi delle partite di un giocatore
//la ricerca può essere ottimizzata per categoria e livello
function mod_trivia_get_score($ID_user, $ID_category = false, $ID_level = false) 
{
	$db = ffDB_Sql::factory();
	static $score = array();
	
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_score.*
					FROM " . CM_TABLE_PREFIX . "mod_trivia_score
					WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql($ID_user, "Number") ."
							" . ($ID_category == true
								? " AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_category = " . $db->toSql($ID_category, "Number")
								: ""
							) . "
							" . ($ID_level == true
								? " AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_level = " . $db->toSql($ID_level, "Number")
								: ""
							) . "
					ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_score.score DESC";
		$db->query($sSQL);
		if($db->nextRecord()) 
	    {
			do
			{
				$level = $db->getField("ID_level", "Number", true);
				$category = $db->getField("ID_category", "Number", true);
				$score[$ID_user][$category][$level][] = $db->getField("score", "Number", true);
			} while($db->nextRecord());  
		}
	
	return $score[$ID_user];
}

//calcolo il massimo punteggio per ogni livello di ciascuna categoria (schermata gioca)
function mod_trivia_get_max_score($ID_user, $ID_category, $ID_level) 
{
	$max_score = 0;
	$score = mod_trivia_get_score($ID_user, $ID_category, $ID_level);
	if(is_array($score[$ID_category][$ID_level]) && count($score[$ID_category][$ID_level]))
	{
		$max_score = max($score[$ID_category][$ID_level]);
	}
	return $max_score;
}

//calcolo i punti della partita, il numero di risposte corrette e salvo l'ID della partita
//viene chiamata anche alla fine di ogni step e in quel caso prende gli ultimi
//5 elementi della tabella (5 perchè all'ultimo non posso aver risposto => is_right = 0)
function mod_trivia_get_match_point($UserNID, $ID_category, $ID_level, $end_step = false) 
{
	$db = ffDB_Sql::factory();
	static $match = array();
	
	if(!array_key_exists($UserNID, $match))
	{
		$count = 0;
		$answer_series = 0;
		$value = 0;
		$total = 0;
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_score_answer.*
					FROM " . CM_TABLE_PREFIX . "mod_trivia_score_answer
						INNER JOIN " . CM_TABLE_PREFIX . "mod_trivia_score ON " . CM_TABLE_PREFIX . "mod_trivia_score.ID = " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_score 
					WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql($UserNID, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_trivia_score.archived = 0
						AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_category = " . $db->toSql($ID_category, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_level = " . $db->toSql($ID_level, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_trivia_score_answer.answer_time > 0
						ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID DESC ";
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$ID_game = $db->getField("ID_score", "Number", true);
			$number_question_match = 0;
			do 
			{
				if($db->getField("is_right", "Number", true))
				{
					$count++;
					$answer_series++;
					$answer_time = $db->getField("answer_time", "Number", true);
					$question_time = $db->getField("question_time", "Number", true);

					if($answer_time - $question_time <= global_settings("MOD_TRIVIA_TIME_PER_MAX_POINT"))
					{
						$answer_score = global_settings("MOD_TRIVIA_MAX_POINT_PER_RIGHT_ANSWER");
					} elseif($answer_time - $question_time <= global_settings("MOD_TRIVIA_TIME_PER_MIDDLE_POINT"))
					{
						$answer_score = global_settings("MOD_TRIVIA_MIDDLE_POINT_PER_RIGHT_ANSWER");
					} else
					{
						$answer_score = global_settings("MOD_TRIVIA_MIN_POINT_PER_RIGHT_ANSWER");
					}
					
					$total += $answer_score;
				
					$match["step"][intval($number_question_match / global_settings("MOD_TRIVIA_QUESTION_PER_STEP"))]["number"]++;
					$match["step"][intval($number_question_match / global_settings("MOD_TRIVIA_QUESTION_PER_STEP"))]["total"] = $match["step"][intval($number_question_match / global_settings("MOD_TRIVIA_QUESTION_PER_STEP"))]["total"] + $answer_score;
				} else
				{
					if($answer_series > $value)
					{
						$value = $answer_series;
					}
					$answer_series = 0;
				}
				$number_question_match++;
			} while($db->nextRecord());
		}
		if($value > $answer_series)
		{
			$match["series"] = $value;
		}
		else
		{
			$match["series"] = $answer_series;
		}

		$match["count"] = $count;
		$match["total"] = $total;
		$match["ID_game"] = $ID_game;
	}
	return $match;
}

//operazioni da fare al termine della partita
function mod_trivia_end_game($UserNID, $ID_category = null, $ID_level = null)
{
	$cm = cm::getInstance();
	$db = ffDB_Sql::factory();

	if($ID_category === null && $ID_level === null) {
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_trivia_score SET 
					" . CM_TABLE_PREFIX . "mod_trivia_score.archived = 1    
					WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql($UserNID, "Number");
		$db->execute($sSQL);
	} else {
		//salvo risultati e controllo che non sia un nuovo record
		$match = mod_trivia_get_match_point($UserNID, $ID_category, $ID_level);
		$max_score = mod_trivia_get_max_score($UserNID, $ID_category, $ID_level);
		
		$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_trivia_score SET 
					" . CM_TABLE_PREFIX . "mod_trivia_score.archived = 1    
					, " . CM_TABLE_PREFIX . "mod_trivia_score.score = " . $db->toSql($match["total"], "Number") . "
					WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql($UserNID, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID = " . $db->toSql($match["ID_game"], "Number");
		$db->execute($sSQL);
		
		//controllo trofei termine partita
		$arrPathInfo = explode("/", $cm->real_path_info);
		if(strlen($cm->real_path_info)) 
		{
			$smart_url_category = $arrPathInfo[1];
			$smart_url_level = $arrPathInfo[2];
		}
		
		$right_answer = mod_trivia_get_answer_score($UserNID);
		
		//trofeo bravissimo, velocissimo e totali di categoria
		if(count($right_answer[$ID_category][$ID_level]) == global_settings("MOD_TRIVIA_QUESTION_PER_LEVEL"))
		{
			if(max($right_answer[$ID_category][$ID_level]) <= global_settings("MOD_TRIVIA_TIME_PER_MAX_POINT"))
			{
				$new_achievement[] = $smart_url_category . "-" . $smart_url_level . "-velocissimo";
			}
			foreach($right_answer[$ID_category] AS $key => $value)
			{
				if($key == $ID_level)
				{
					continue;
				} else 
				{
					if(count($value) == global_settings("MOD_TRIVIA_QUESTION_PER_LEVEL"))
					{
						$new_achievement[] = $smart_url_category . "-completed";
					}
				}
			}
			$new_achievement[] = $smart_url_category . "-" . $smart_url_level . "-bravissimo";
		}
		if(is_array($match["step"]) && count($match["step"])) {
			$max_step = array("number" => 0, "total" => 0);
			foreach($match["step"] AS $step_value) {
				if($step_value["number"] > $max_step["number"]) {
					$max_step = $step_value;
				}
			}
		}

		if($max_step["number"] == global_settings("MOD_TRIVIA_QUESTION_PER_STEP"))
		{
			if($max_step["total"] == global_settings("MOD_TRIVIA_QUESTION_PER_STEP") * global_settings("MOD_TRIVIA_MAX_POINT_PER_RIGHT_ANSWER"))
			{
				$new_achievement[] = $smart_url_category . "-" . $smart_url_level. "-veloce";
			}
			$new_achievement[] = $smart_url_category . "-" . $smart_url_level. "-bravo";
		}
		
		//trofeo basato sul numero di risposte totale
		if(count($right_answer["total-answer"]) >= global_settings("MOD_TRIVIA_ACHIEVEMENT_SCOUT"))
		{
			if(count($right_answer["total-answer"]) >= global_settings("MOD_TRIVIA_ACHIEVEMENT_ESPLORATORE"))
			{
				$new_achievement[] = "esploratore";
			}
			$new_achievement[] = "scout";
		}
		
		if($match["series"] >= global_settings("MOD_TRIVIA_ACHIEVEMENT_SERIE_VINCENTE1"))
		{
			if($match["series"] >= global_settings("MOD_TRIVIA_ACHIEVEMENT_SERIE_VINCENTE2"))
			{
				if($match["series"] >= global_settings("MOD_TRIVIA_ACHIEVEMENT_SERIE_VINCENTE3"))
				{
					$new_achievement[] = $smart_url_category . "-" . $smart_url_level. "-serie-vincente3";
				}
				$new_achievement[] = $smart_url_category . "-" . $smart_url_level. "-serie-vincente2";
			}
			$new_achievement[] = $smart_url_category . "-" . $smart_url_level. "-serie-vincente1";
		}
		
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users_fields.*
					FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
					WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($UserNID, "Number") . "
						AND " . CM_TABLE_PREFIX . "mod_security_users_fields.value = 0"; 
		$db->query($sSQL);
		if($db->nextRecord())
		{
			do
			{
				$new_achievement[] = $db->getField("field", "Text", true);
			} while($db->nextRecord());
		}
		//controllo più aggiunta trofeo db
		if(is_array($new_achievement) && count($new_achievement))
		{
			$match_new_achievement = mod_trivia_achievement_acquired($UserNID, $new_achievement);
		}
		//fine parte trofei

		//carico template
        $filename = cm_cascadeFindTemplate("/contents/end-game.html", "trivia");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/trivia/contents/end-game.html", $cm->oPage->theme, false);
		if ($filename === null) 
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/trivia/themes", "/end-game.html", $cm->oPage->theme);*/
			$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("end-game.html", "main");

		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);
		$tpl->set_var("domain_inset", DOMAIN_INSET);
		$tpl->set_var("trivia_path", MOD_TRIVIA_PATH);
		$tpl->set_var("trivia_social_facebook", FF_SITE_PATH . "/services/share/condividi");
		$tpl->set_var("trivia_social_twitter", FF_SITE_PATH . "/services/share/cinguetta");
		
		//se ci sono nuovi trofei li mostro all'utente
		if(is_array($match_new_achievement) && count($match_new_achievement))
		{
			$i = 1;
			$user_achievement = mod_trivia_get_user_achievement($UserNID);
			foreach ($match_new_achievement AS $key => $value)
			{
				if(array_key_exists($value, $user_achievement)) 
				{
					//if(!$user_achievement[$value]["is_acquired"]) {
						$tpl->set_var("trivia_new_achievement_number", $i);
						$tpl->set_var("trivia_achievement_title", $user_achievement[$value]["name"]);
						if(strlen($user_achievement[$value]["file"]) && is_file(DISK_UPDIR . $user_achievement[$value]["file"])) {
							$achievement_img = CM_SHOWFILES . $user_achievement[$value]["file"];
						} else {
							$achievement_img = FF_SITE_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/images/spacer.gif";
						}
						$tpl->set_var("trivia_achievement_file", $achievement_img);
						$tpl->set_var("trivia_achievement_description", $user_achievement[$value]["description"]);
						$tpl->parse("SezAchievementItem", true);

						$i++;
					//}
				}
				
			}
			
			if($i > 1) 
				$tpl->parse("SezAchievement", false);
		}
		
		$tpl->set_var("score", $match["total"]);

		if($match["total"] > $max_score)
		{
			$tpl->parse("SezNewRecord", false); 
		} else
		{
			$tpl->set_var("SezNewRecord", "");
		}
		$tpl->set_var("trivia_gioca_url", "href = '" . FF_SITE_PATH . MOD_TRIVIA_PATH . "';");
		$cm->oPage->tplAddJs("endGame"
            , array(
                "file" => "endGame.js"
                , "path" => "/modules/trivia/themes/javascript"
            ));
		return $tpl;
	}
}

//ottengo i trofei dell'utente
//i trofei devono essere definiti sia in cm_mod_trivia_achievement che in cm_mod_security_users_fields
//avere lo stesso smart_url e status = 1
function mod_trivia_get_user_achievement($UserNID, $override = false, $full_achievement = false, $categorized = false)
{
	$db = ffDB_Sql::factory();
	static $achievement = array();
	
	if(!array_key_exists($UserNID, $achievement) || $override)
	{
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_achievement.*
						, IF(ISNULL(" . CM_TABLE_PREFIX . "mod_security_users_fields.value), '0', " . CM_TABLE_PREFIX . "mod_security_users_fields.value) AS is_acquired
						, IF(ISNULL(" . CM_TABLE_PREFIX . "mod_security_users_fields.value), '0', '1') AS is_set
						, " . CM_TABLE_PREFIX . "mod_trivia_category.name AS category_name
						, " . CM_TABLE_PREFIX . "mod_trivia_level.name AS level_name
						, " . CM_TABLE_PREFIX . "mod_security_users_fields.field
					FROM " . CM_TABLE_PREFIX . "mod_trivia_achievement
						LEFT JOIN " . CM_TABLE_PREFIX . "mod_trivia_category ON " . CM_TABLE_PREFIX . "mod_trivia_category.ID = " . CM_TABLE_PREFIX . "mod_trivia_achievement.ID_category
						LEFT JOIN " . CM_TABLE_PREFIX . "mod_trivia_level ON " . CM_TABLE_PREFIX . "mod_trivia_level.ID = " . CM_TABLE_PREFIX . "mod_trivia_achievement.ID_level
						LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users_fields ON " . CM_TABLE_PREFIX . "mod_trivia_achievement.smart_url = " . CM_TABLE_PREFIX . "mod_security_users_fields.field 
							AND " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($UserNID, "Number") . "
					WHERE 1
						AND " . CM_TABLE_PREFIX . "mod_trivia_achievement.status = 1
					ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_category.`order`
							, " . CM_TABLE_PREFIX . "mod_trivia_level.`order`
							, " . CM_TABLE_PREFIX . "mod_trivia_achievement.`order`
							, " . CM_TABLE_PREFIX . "mod_trivia_achievement.name
						"; 
		$db->query($sSQL);
		if($db->nextRecord())
		{
			do
			{
				if(!$full_achievement && !$db->getField("is_set", "Number", true))
					continue;

				$smart_url = $db->getField("smart_url", "Text", true);
				
				if($categorized) {
					$category_name = $db->getField("category_name", "text", true);
					
					$achievement[$category_name][$smart_url]["name"] = $db->getField("name", "Text", true);
					$achievement[$category_name][$smart_url]["file"] = $db->getField("file", "Text", true);
					$achievement[$category_name][$smart_url]["description"] = $db->getField("description", "Text", true);
					$achievement[$category_name][$smart_url]["is_acquired"] = $db->getField("is_acquired", "Number", true);
					$achievement[$category_name][$smart_url]["is_set"] = $db->getField("is_set", "Number", true);
					$achievement[$category_name][$smart_url]["category_name"] = $category_name;
					$achievement[$category_name][$smart_url]["level_name"] = $db->getField("level_name", "text", true);
				} else {
					$achievement[$smart_url]["name"] = $db->getField("name", "Text", true);
					$achievement[$smart_url]["file"] = $db->getField("file", "Text", true);
					$achievement[$smart_url]["description"] = $db->getField("description", "Text", true);
					$achievement[$smart_url]["is_acquired"] = $db->getField("is_acquired", "Number", true);
					$achievement[$smart_url]["is_set"] = $db->getField("is_set", "Number", true);
					$achievement[$smart_url]["category_name"] = $db->getField("category_name", "text", true);
					$achievement[$smart_url]["level_name"] = $db->getField("level_name", "text", true);
				}
			} while($db->nextRecord());
		}
	}
	return $achievement;
}

//aggiungo i trofei conquistati durante la partita (e non detenuti dall'utente) al db
//e ritorno un array con i nuovi trofei conquistati per mostrarli all'utente
function mod_trivia_achievement_acquired($UserNID, $new_achievement)
{
	$db = ffDB_Sql::factory();
	
	$acquired_achievement = array();
	$user_achievement = mod_trivia_get_user_achievement($UserNID, true);
	
	if(is_array($new_achievement) && count($new_achievement))
	{
		foreach($new_achievement AS $value)
		{
			if(!array_key_exists($value, $user_achievement))
			{
				$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_users_fields 
						(
							ID
							, ID_users
							, field
							, value
						)
						VALUES
						(
							null 
							, " . $db->toSql($UserNID, "Number") . "
							, " . $db->toSql($value, "String") . "
							, 1
						)";
				$db->execute($sSQL);
				
				$acquired_achievement[] = $value;
			} else {
				if(!$user_achievement[$value]["is_acquired"]) {
					$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_users_fields SET
									" . CM_TABLE_PREFIX . "mod_security_users_fields.value = 1
							   WHERE " . CM_TABLE_PREFIX . "mod_security_users_fields.field = " . $db->toSql($value, "Text") . "
								   AND " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . $db->toSql($UserNID, "Number");
					$db->execute($sSQL);
					$acquired_achievement[] = $value; 
				}
			}
		}
	}
	return $acquired_achievement;
}
?>