<?php
use_cache(false);

$permission = check_trivia_permission(); 


$db = ffDB_Sql::factory();
$arrPathInfo = explode("/", $cm->real_path_info);
$UserNID = get_session("UserNID");

$arrLevel = array();
$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_level.*
		FROM " . CM_TABLE_PREFIX . "mod_trivia_level
		WHERE 1
		ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_level.ID";
$db->query($sSQL);
if($db->nextRecord()) 
{
	do 
	{
		$arrLevel[$db->getField("smart_url", "Text", true)]["ID"] = $db->getField("ID", "Number", true);
		$arrLevel[$db->getField("smart_url", "Text", true)]["name"] = $db->getField("name", "Text", true);
	} while($db->nextRecord());
}

if(strlen($cm->real_path_info)) 
{
	if($permission !== true && !(is_array($permission) && count($permission))) 
	{
	    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
	}	
	
	$smart_url_category = $arrPathInfo[1];
	if(count($arrLevel)) 
	{
		$smart_url_level = $arrPathInfo[2];
		$smart_url_question = $arrPathInfo[3];
		$smart_url_answer = $arrPathInfo[4];
	} else 
	{
		$smart_url_question = $arrPathInfo[2];
		$smart_url_answer = $arrPathInfo[3];
	}
	
	if(strlen($smart_url_category)) {
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_category.* 
				FROM " . CM_TABLE_PREFIX . "mod_trivia_category 
				WHERE " . CM_TABLE_PREFIX . "mod_trivia_category.smart_url = " . $db->toSql($smart_url_category);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$ID_category = $db->getField("ID", "Number", true);
		} else {
			$strError = ffTemplate::_get_word_by_code("trivia_category_not_found");
		}
	}
	if(strlen($smart_url_level)) {
		if(array_key_exists($smart_url_level, $arrLevel)) 
		{
			$ID_level = $arrLevel[$smart_url_level]["ID"];
		} else 
		{
			$strError = ffTemplate::_get_word_by_code("trivia_level_not_found");
		}
	}
	
	if(strlen($smart_url_question)) 
	{
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_question.* 
				FROM " . CM_TABLE_PREFIX . "mod_trivia_question 
				WHERE " . CM_TABLE_PREFIX . "mod_trivia_question.smart_url = " . $db->toSql($smart_url_question);
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$ID_question = $db->getField("ID", "Number", true);
		} else 
		{
			$strError = ffTemplate::_get_word_by_code("trivia_question_not_found");
		}
	}
	
	if(strlen($smart_url_answer)) 
	{
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_answer.* 
				FROM " . CM_TABLE_PREFIX . "mod_trivia_answer 
				WHERE " . CM_TABLE_PREFIX . "mod_trivia_answer.smart_url = " . $db->toSql($smart_url_answer);
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$ID_answer = $db->getField("ID", "Number", true);
		} else 
		{
			$strError = ffTemplate::_get_word_by_code("trivia_answer_not_found");
		}
	}	

	$base_path = ($ID_category > 0
		? "/" . $smart_url_category 
		: ""
	) . 
	($ID_level > 0
		? "/" . $smart_url_level
		: ""
	);
	
	//controllo non ci siano errori
	if(!strlen($strError)) 
	{
		//verifico il giocatore non abbia abbandonato alla fine dello step
		if(!isset($_REQUEST['endgame']))
		{
			if($ID_question > 0 && $ID_answer > 0) 
			{
				mod_trivia_init_score($ID_question, $ID_category, $ID_level, $ID_answer);
				ffRedirect(FF_SITE_PATH . MOD_TRIVIA_PATH . $base_path . "?continue");
			} else 
			{
				$arrQuestion = array();
				$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_score_answer.*
							FROM " . CM_TABLE_PREFIX . "mod_trivia_score_answer
								INNER JOIN " . CM_TABLE_PREFIX . "mod_trivia_score ON " . CM_TABLE_PREFIX . "mod_trivia_score.ID = " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_score
							WHERE 1
								" . ($ID_category > 0
									? " AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_category = " . $db->toSql($ID_category, "Number")
									: ""
								) . "
								" . ($ID_level
									? " AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_level = " . $db->toSql($ID_level, "Number")
									: ""
								) . "
								AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql($UserNID, "Number") . "
								AND " . CM_TABLE_PREFIX . "mod_trivia_score.archived = 0
								AND " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_answer > 0
							ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID DESC";
				$db->query($sSQL);
				if($db->nextRecord()) 
				{
					$is_right = $db->getField("is_right", "Number", true);
					if($is_right)
					{
						$answer_time = $db->getField("answer_time", "Number", true);
						$question_time = $db->getField("question_time", "Number", true);
						if($answer_time - $question_time <= global_settings("MOD_TRIVIA_TIME_PER_MAX_POINT"))
						{
							$point = global_settings("MOD_TRIVIA_MAX_POINT_PER_RIGHT_ANSWER");
						} elseif($answer_time - $question_time <= global_settings("MOD_TRIVIA_TIME_PER_MIDDLE_POINT"))
						{
							$point = global_settings("MOD_TRIVIA_MIDDLE_POINT_PER_RIGHT_ANSWER");
						} else
						{
							$point = global_settings("MOD_TRIVIA_MIN_POINT_PER_RIGHT_ANSWER");
						}
					}
					do 
					{
						$arrQuestion["all"][] = $db->getField("ID_question", "Number", true);
						if($db->getField("is_right", "Number", true)) 
						{
							$arrQuestion["right"][] = $db->getField("ID_question", "Number", true);
						} else 
						{
							$arrQuestion["noright"][] = $db->getField("ID_question", "Number", true);
						}
					} while($db->nextRecord());
				} 
				if(count($arrQuestion["all"]) < global_settings("MOD_TRIVIA_QUESTION_PER_LEVEL"))
				{
					if($ID_category > 0 && $ID_level > 0) {
						$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_question.*
								FROM " . CM_TABLE_PREFIX . "mod_trivia_score_answer
									INNER JOIN " . CM_TABLE_PREFIX . "mod_trivia_score ON " . CM_TABLE_PREFIX . "mod_trivia_score.ID = " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_score
									INNER JOIN " . CM_TABLE_PREFIX . "mod_trivia_question ON " . CM_TABLE_PREFIX . "mod_trivia_question.ID = " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_question
								WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql(get_session("UserNID"), "Number") . "
									AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_category = " . $db->toSql($ID_category, "Number") . "
									AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_level = " . $db->toSql($ID_level, "Number") . "
									AND " . CM_TABLE_PREFIX . "mod_trivia_score.archived = 0
									AND " . CM_TABLE_PREFIX . "mod_trivia_score_answer.answer_time = 0
								ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID DESC";
						$db->query($sSQL);
						if($db->nextRecord()) {
							$ID_question = $db->getField("ID", "Number", true);
							$question_name = $db->getField("name", "Text", true);
							$question_smart_url = $db->getField("smart_url", "Text", true);
							$count_question = count($arrQuestion["all"]) + 1;
						}
					}
				
					if(!$ID_question) {
						$sSQL = "SELECT 
									" . CM_TABLE_PREFIX . "mod_trivia_question.*
									FROM " . CM_TABLE_PREFIX . "mod_trivia_question 
									WHERE 1 " . ($ID_category > 0
											? " AND " . CM_TABLE_PREFIX . "mod_trivia_question.ID_category = " . $db->toSql($ID_category, "Number")
											: ""
										) . "
										" . ($ID_level
											? " AND " . CM_TABLE_PREFIX . "mod_trivia_question.ID_level = " . $db->toSql($ID_level, "Number")
											: ""
										) . "
										" . ($ID_question
											? " AND " . CM_TABLE_PREFIX . "mod_trivia_question.ID = " . $db->toSql($ID_question, "Number")
											: ""
										) . "
										" . (is_array($arrQuestion) && count($arrQuestion)
											? " AND " . CM_TABLE_PREFIX . "mod_trivia_question.ID NOT IN(" . $db->toSql(implode(", ", $arrQuestion["all"]), "Text", false) . ")"
											: ""
										) . "
									ORDER BY RAND()";
						$db->query($sSQL);
						if($db->nextRecord()) 
						{
							$ID_question = $db->getField("ID", "Number", true);
							$question_name = $db->getField("name", "Text", true);
							$question_smart_url = $db->getField("smart_url", "Text", true);
							$count_question = count($arrQuestion["all"]) + 1;
						}
					}
					
					if($ID_question) 
					{
						if(!isset($_REQUEST["continue"])) {
							mod_trivia_init_score($ID_question, $ID_category, $ID_level);

							$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_answer.*
										FROM " . CM_TABLE_PREFIX . "mod_trivia_answer
										WHERE " . CM_TABLE_PREFIX . "mod_trivia_answer.ID_question = " . $db->toSql($ID_question, "Number") . "
										ORDER BY name";
							$db->query($sSQL);
							if($db->nextRecord()) 
							{
                                $filename = cm_cascadeFindTemplate("/contents/question.html", "trivia");
								/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/trivia/contents/question.html", $cm->oPage->theme, false);
								if ($filename === null)
									$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/trivia/themes", "/question.html", $cm->oPage->theme);*/

								$tpl = ffTemplate::factory(ffCommon_dirname($filename));
								$tpl->load_file("question.html", "main");

								$tpl->set_var("theme", $cm->oPage->theme);
								$tpl->set_var("site_path", $cm->oPage->site_path);
	                            $tpl->set_var("category_class", $smart_url_category);
	                            $tpl->set_var("level_class", $smart_url_level);
	                            $tpl->set_var("count_question", $count_question);
	                            $tpl->set_var("question_tot", global_settings("MOD_TRIVIA_QUESTION_PER_LEVEL"));
								if(count($arrQuestion["all"]))
								{
									//controllo se è finito lo step => almeno una domanda fatta, non è l'ultima e il
									//numero di domande è multiplo di "domande per step"
									if(!(count($arrQuestion["all"]) % global_settings("MOD_TRIVIA_QUESTION_PER_STEP")))
									{ 
										$step_score = mod_trivia_get_match_point($UserNID, $ID_category, $ID_level);
									
										//valorizzo la pagina dello step
										$tpl->set_var("domain_inset", DOMAIN_INSET);
										$tpl->set_var("trivia_path", MOD_TRIVIA_PATH);
										$tpl->set_var("step_score", $step_score["total"]);
										$tpl->set_var("trivia_leave_game", FF_SITE_PATH . MOD_TRIVIA_PATH . $base_path . "/?endgame");
										$tpl->set_var("trivia_social_facebook", FF_SITE_PATH . "/services/share/condividi");
										$tpl->set_var("trivia_social_twitter", FF_SITE_PATH . "/services/share/cinguetta");
										$tpl->parse("SezStep", false);
									}
								} else
								{
									//$cm->oPage->tplAddJs("google-search", "google-search.js", FF_SITE_PATH . "/modules/trivia/themes/javascript");
								}
								
								$cm->oPage->tplAddJs("ff.fn.trivia"
                                    , array(
                                        "file" => "answerVerify.js"
                                        , "path" => "/modules/trivia/themes/javascript"
                                ));
								$tpl->set_var("question_name", $question_name);
								$tpl->set_var("google-search_key", "http://www.google.com/search?q=" . $question_name);
								do 
								{
									$tpl->set_var("answer_url", FF_SITE_PATH . MOD_TRIVIA_PATH . $base_path . "/" . $question_smart_url . "/" . $db->getField("smart_url", "Text", true));
									$tpl->set_var("answer_name", $db->getField("name", "Text", true));
									$tpl->set_var("answer_class", ffCommon_url_rewrite($db->getField("name", "Text", true)));
									$tpl->parse("SezAnswer", true);
								} while($db->nextRecord());
							}
						} else {
                            $filename = cm_cascadeFindTemplate("/contents/control-answer.html", "trivia");
							/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/trivia/contents/control-answer.html", $cm->oPage->theme, false);
							if ($filename === null)
								$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/trivia/themes", "/control-answer.html", $cm->oPage->theme);*/

							$tpl = ffTemplate::factory(ffCommon_dirname($filename));
							$tpl->load_file("control-answer.html", "main");

							$cm->oPage->tplAddJs("ff.fn.trivia"
                                , array(
                                    "file" => "answerVerify.js"
                                    , "path" => "/modules/trivia/themes/javascript"
                            ));
							$tpl->set_var("theme", $cm->oPage->theme);
							$tpl->set_var("site_path", $cm->oPage->site_path);

							//controllo correttezza risposta
							if(isset($_REQUEST["continue"])) {
								if($is_right)
								{
									$tpl->set_var("question_point", $point);
									$tpl->parse("SezAnswerRight", false);
								} else
								{
									$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_error_sentence.*
												FROM " . CM_TABLE_PREFIX . "mod_trivia_error_sentence 
												WHERE 1
												ORDER BY RAND()";
									$db->query($sSQL);
									if($db->nextRecord()) 
									{
										$tpl->set_var("error_sentence", $db->getField("name", "Text", true));
									}
									$tpl->parse("SezAnswerWrong", false);
								}
								$tpl->parse("SezControlAnswer", false); 
							}
						}
					} else
					{
						//se le domande del livello sono finite, ma non ho finito il livello
						$tpl = mod_trivia_end_game($UserNID, $ID_category, $ID_level);
					}
				} else 
				{
					//se ho finito interamente il livello
					$tpl = mod_trivia_end_game($UserNID, $ID_category, $ID_level);
				}
			}
		} else
		{
			//se ho abbandonato ad uno step
			$tpl = mod_trivia_end_game($UserNID, $ID_category, $ID_level);
		}
	}
} else 
{
    $filename = cm_cascadeFindTemplate("/contents/category.html", "trivia");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/trivia/contents/category.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/trivia/themes", "/category.html", $cm->oPage->theme);*/

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file("category.html", "main");

	$tpl->set_var("theme", $cm->oPage->theme);
	$tpl->set_var("site_path", $cm->oPage->site_path);

	//restart all game by user;
	mod_trivia_end_game($UserNID);
	
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_category.*
			FROM " . CM_TABLE_PREFIX . "mod_trivia_category
			WHERE 1
			ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_category.`order`, " . CM_TABLE_PREFIX . "mod_trivia_category.name";
	$db->query($sSQL);
	if($db->nextRecord()) 
	{
		do 
		{
			$ID_category = $db->getField("ID", "Number", true);
			$tpl->set_var("SezLevel", "");
			$tpl->set_var("category_name", $db->getField("name", "Text", true));
			$tpl->set_var("category_class", $db->getField("smart_url", "Text", true));
			if(is_array($arrLevel) && count($arrLevel)) 
			{
				foreach($arrLevel AS $arrLevel_key => $arrLevel_value) 
				{
					$score = mod_trivia_get_max_score($UserNID, $ID_category, $arrLevel_value["ID"]);
					$tpl->set_var("category_url", FF_SITE_PATH . MOD_TRIVIA_PATH . "/" . $db->getField("smart_url", "Text", true) . "/" . $arrLevel_key);
					if($score)
					{
						$tpl->set_var("category_record", $score);
						$tpl->parse("SezRecordCategoryLevel", false);
					} else
					{
						$tpl->set_var("SezRecordCategoryLevel", "");
					}
					$tpl->set_var("level_name", $arrLevel_value["name"]);
					$tpl->set_var("level_class", $arrLevel_key);
					$tpl->parse("SezLevel", true);
				}
			} else 
			{
				$tpl->set_var("category_url", FF_SITE_PATH . MOD_TRIVIA_PATH . "/" . $db->getField("smart_url", "Text", true));
				$tpl->parse("SezLevel", false);
			}
			$tpl->parse("SezCategory", true);
		} while($db->nextRecord());
	}
}
if(strlen($strError)) 
{
    $filename = cm_cascadeFindTemplate("/contents/error.html", "trivia");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/trivia/contents/error.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/trivia/themes", "/error.html", $cm->oPage->theme);*/

	$tpl = ffTemplate::factory(ffCommon_dirname($filename));
	$tpl->load_file("error.html", "main");
	$tpl->set_var("str_error", $strError);
}
$cm->oPage->addContent($tpl->rpparse("main", false)); 
?>