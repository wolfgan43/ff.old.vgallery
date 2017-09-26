<?php
$permission = check_trivia_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[global_settings("MOD_TRIVIA_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "QuestionModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("trivia_question_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_trivia_question";
$oRecord->addEvent("on_done_action", "QuestionModify_on_done_action");
$oRecord->addEvent("on_do_action", "QuestionModify_on_do_action");

$oField = ffField::factory($cm->oPage); 
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_question_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("trivia_categories_modify_smart_url");
$oField->widget = "slug";
$oField->slug_title_field = "name";
$oField->properties["readonly"] = "readonly";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_category";
$oField->container_class = "ID-category";
$oField->label = ffTemplate::_get_word_by_code("trivia_question_modify_category");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_category.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_category.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_category
							WHERE 1";
$oField->widget = "activecomboex";
$oField->required = true;
$oField->actex_update_from_db = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_level";
$oField->container_class = "ID-level";
$oField->label = ffTemplate::_get_word_by_code("trivia_question_modify_level");
$oField->required = true;
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_trivia_level.ID
								, " . CM_TABLE_PREFIX . "mod_trivia_level.name
							FROM " . CM_TABLE_PREFIX . "mod_trivia_level
							WHERE 1";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_available");
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);   

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "AnswerDetail";
$oDetail->title = ffTemplate::_get_word_by_code("trivia_answer_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_trivia_answer";
$oDetail->order_default = "name";
$oDetail->fields_relationship = array ("ID_question" => "ID");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("trivia_answer_name");
$oField->required = true;
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "smart_url";
$oField->label = ffTemplate::_get_word_by_code("trivia_answer_smart_url");
$oField->widget = "slug";
$oField->slug_title_field = "name";
$oField->properties["readonly"] = "readonly";
$oDetail->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_answer_right";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("trivia_answer_right");
$oField->store_in_db = false;
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->properties["onclick"] = "
	if(jQuery(this).prop('checked'))
	{
		jQuery('.ffDetails input[type=checkbox]').not(this).removeAttr('checked')
	};";
$oDetail->addContent($oField);

$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);      

function QuestionModify_on_do_action($component, $action) 
{
	//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
	$db = ffDB_Sql::factory();
	if(strlen($action)) 
	{
		switch($action) 
		{
			case "insert":
			case "update":
				$answer = $component->detail["AnswerDetail"]->recordset;
				$right_answer = 0;
				if(count($answer) < global_settings("MOD_TRIVIA_MIN_ANSWER_PER_QUESTION") || count($answer) > global_settings("MOD_TRIVIA_MAX_ANSWER_PER_QUESTION"))
				{
					$component->tplDisplayError(ffTemplate::_get_word_by_code("trivia_error_number_of_answer") . " (min " . global_settings("MOD_TRIVIA_MIN_ANSWER_PER_QUESTION") . ", max " . global_settings("MOD_TRIVIA_MAX_ANSWER_PER_QUESTION") . ")");
					return true;
				}
				foreach($answer AS $answer_key => $answer_value) 
				{
					$answer_value["smart_url"]->setValue(ffCommon_url_rewrite($answer_value["name"]->getValue()));
					if($answer_value["ID_answer_right"]->getValue("Number"))
					{
						$right_answer++;
					}
				}
				if(!$right_answer) 
				{
					$component->tplDisplayError(ffTemplate::_get_word_by_code("trivia_choose_right_answer"));
					return true;  
				}
		}
	}
}
function QuestionModify_on_done_action($component, $action) 
{
    $db = ffDB_Sql::factory();
    if(strlen($action)) 
	{
		switch($action) 
		{
			case "insert":
			case "update":
				$answer = $component->detail["AnswerDetail"]->recordset;
				foreach($answer AS $answer_key => $answer_value)
				{
					if($answer_value["ID_answer_right"]->getValue("Number"))
					{
						$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_trivia_question SET 
									" . CM_TABLE_PREFIX . "mod_trivia_question.ID_answer_right = " . $db->toSql($answer_value["ID"]->getValue("Number"), "Number") . "  
								WHERE " . CM_TABLE_PREFIX . "mod_trivia_question.ID = " . $db->toSql($component->key_fields["ID"]->getValue("Number"), "Number");
						$db->execute($sSQL);
						break;
					} 
				}
				mod_trivia_control_smart_url("mod_trivia_answer");
				mod_trivia_control_smart_url("mod_trivia_question");
				/*
				$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_trivia_answer 
							SET " . CM_TABLE_PREFIX . "mod_trivia_answer.smart_url = 
								CONCAT( " . CM_TABLE_PREFIX . "mod_trivia_answer.smart_url, '-', " . CM_TABLE_PREFIX . "mod_trivia_answer.ID)
							WHERE " . CM_TABLE_PREFIX . "mod_trivia_answer.smart_url IN
														(
															SELECT smart_url
															FROM	(SELECT " . CM_TABLE_PREFIX . "mod_trivia_answer.smart_url AS smart_url
																		, COUNT(cm_mod_trivia_answer.ID) AS answer_count
																		FROM " . CM_TABLE_PREFIX . "mod_trivia_answer
																		WHERE 1
																		GROUP BY " . CM_TABLE_PREFIX . "mod_trivia_answer.smart_url
																		HAVING answer_count > 1
																	) AS tbl
														
														)";
				$db->execute($sSQL);
				 * 
				 */
		}
	}
}
?>