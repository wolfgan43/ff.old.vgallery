<?php

$db = ffDB_Sql::factory();
if(strpos($cm->real_path_info, MOD_TRIVIA_PATH) === 0)
{
	$path_info = substr($cm->real_path_info, strlen(MOD_TRIVIA_PATH));
	$arrPathInfo = explode("/", $path_info);
	$UserNID = get_session("UserNID");

	$arrLevel = array();
	$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_level.*
			FROM " . CM_TABLE_PREFIX . "mod_trivia_level
			WHERE 1
			ORDER BY " . CM_TABLE_PREFIX . "mod_trivia_level.ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrLevel[$db->getField("smart_url", "Text", true)]["ID"] = $db->getField("ID", "Number", true);
			$arrLevel[$db->getField("smart_url", "Text", true)]["name"] = $db->getField("name", "Text", true);
		} while($db->nextRecord());
	}

	if(strlen($path_info)) {
		$smart_url_category = $arrPathInfo[1];
		if(count($arrLevel)) {
			$smart_url_level = $arrPathInfo[2];
			$smart_url_question = $arrPathInfo[3];
			$smart_url_answer = $arrPathInfo[4];
		} else {
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
		if(!$strError)
		{
				$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_score.*
							FROM " . CM_TABLE_PREFIX . "mod_trivia_score
							WHERE " . CM_TABLE_PREFIX . "mod_trivia_score.ID_user = " . $db->toSql(get_session("UserNID", "Number")) . "
								AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_category = " . $db->toSql($ID_category, "Number") . "
								AND " . CM_TABLE_PREFIX . "mod_trivia_score.ID_level = " . $db->toSql($ID_level, "Number") . "
								AND " . CM_TABLE_PREFIX . "mod_trivia_score.archived = 0";
				$db->query($sSQL);
				if($db->nextRecord())
				{
					$ID_score = $db->getField("ID", "Number", true);
				}

				$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_trivia_score_answer
							SET " . CM_TABLE_PREFIX . "mod_trivia_score_answer.question_time = " . $db->toSql(time(), "Number") . "
							WHERE " . CM_TABLE_PREFIX . "mod_trivia_score_answer.answer_time = 0
								AND " . CM_TABLE_PREFIX . "mod_trivia_score_answer.ID_score = " . $db->toSql($ID_score, "Number");
				$db->execute($sSQL);
			
		}
	}	
}
?>