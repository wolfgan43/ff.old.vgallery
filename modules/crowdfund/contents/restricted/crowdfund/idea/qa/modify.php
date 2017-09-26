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
$arrTeam = array();

if($_REQUEST["keys"]["ID_idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
} elseif($_REQUEST["idea"] > 0) {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["idea"], "Number");
} else {
	$sSQL_Where = CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql(basename($cm->real_path_info), "Text");
}

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID 
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff AS goal_diff
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_step AS goal_step
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
				, " . FF_PREFIX . "currency.name AS currency_name 
			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
				INNER JOIN " . FF_PREFIX . "currency ON " . FF_PREFIX . "currency.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_currency 
			WHERE 1 
				AND " . $sSQL_Where;

$db->query($sSQL);
if($db->nextRecord()) 
{
	$arrLimit = array();
	$ID_idea = $db->getField("ID", "Number", true);
	$owner = $db->getField("owner", "Number", true);
	$languages = $db->getField("languages", "Text", true);	
	$smart_url = $db->getField("smart_url", "Text", true);	
	$goal_diff = $db->getField("goal_diff", "Number", true);
	$goal_step = $db->getField("goal_step", "Number", true);
	$currency_name = $db->getField("currency_name", "Text", true);

}

if(isset($_REQUEST["setactivated"]))
{
	$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa 
				SET " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.visible = " . $db->toSql($_REQUEST["setactivated"], "Number") . "
				WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
	$db->execute($sSQL);
	
	if($cm->isXHR()) {
	    if($_REQUEST["XHR_DIALOG_ID"]) {
	        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("QAModify")), true));
	    } else {
	        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("QAModify")), true));
	        //ffRedirect($_REQUEST["ret_url"]);
	    }
	} else {
		ffRedirect($_REQUEST["ret_url"]);
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
	$cm->oPage->addContent(confirm_dialog_success("question"));
} else
{
	if($UserNID)
	{
		$_REQUEST["keys"]["ID_user_anagraph"] = get_anagraph_ID($UserNID);
	}

	$_REQUEST["keys"]["ID_idea"] = $ID_idea;

	if(global_settings("MOD_CROWDFUND_ENABLE_TEAM")) 
	{
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.*
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team.ID_idea = " . $db->toSql($_REQUEST["keys"]["ID_idea"], "Number");
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			do {
				$arrTeam[$db->getField("ID_user_anagraph", "Number", true)] = 0;
			} while($db->nextRecord());
		}
	} else
	{
		$arrTeam[$owner] = 0;
	}



	if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false) {
		$simple_interface = false;
	} else {
		$simple_interface = true;
	}

	//menu
	if(isset($_REQUEST["keys"]["ID"]))
	{
		$tpl_menu = mod_crowdfund_get_menu_idea($smart_url, null, $arrLimit);
	}

	if($_REQUEST["keys"]["ID_user_anagraph"])
	{
		$oRecord = ffRecord::factory($cm->oPage);
		$oRecord->id = "QAModify";
		$oRecord->resources[] = $oRecord->id;
		$oRecord->title = ffTemplate::_get_word_by_code("qa_modify_title");
		$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_qa";
		$oRecord->addEvent("on_do_action", "QAModify_on_do_action");
		$oRecord->addEvent("on_done_action", "QAModify_on_done_action");
		$oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
		$oRecord->user_vars["ID_idea"] = $ID_idea;
		$oRecord->user_vars["smart_url"] = basename($cm->real_path_info);

		$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
		$oRecord->additional_fields["last_update"] =  new ffData(time(), "Number");


		if(!$cm->oPage->isXHR()) {
		//	$oRecord->fixed_post_content = $tpl_menu;
		}

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oRecord->addKeyField($oField);

		//if(!$_REQUEST["keys"]["ID"] > 0) {
			if($_REQUEST["keys"]["ID_idea"]  > 0) {
				$oRecord->insert_additional_fields["ID_idea"] =  new ffData($_REQUEST["keys"]["ID_idea"] , "Number");
			} else {
				$oField = ffField::factory($cm->oPage);
				$oField->id = "ID_idea";
				$oField->label = ffTemplate::_get_word_by_code("qa_modify_ID_idea");
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

		$oField = ffField::factory($cm->oPage);
		$oField->id = "question";
		$oField->container_class = "question";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_modify_question");
		$oField->extended_type = "Text";
		if($_REQUEST["keys"]["ID"] > 0) {
			$oField->control_type = "label";
			$oField->display_label = false;
			$oField->fixed_pre_content = "<h3>";
			$oField->fixed_post_content = "</h3>";
		} else {
			$oField->required = true;
		}
		$oRecord->addContent($oField);

		if($_REQUEST["keys"]["ID"] > 0  && ($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")] || array_key_exists($UserNID, $arrTeam))) {
			$oField = ffField::factory($cm->oPage); 
			$oField->id = "visible";
			$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_modify_visible");
			$oField->base_type = "Number";
			$oField->extended_type = "Boolean";
			$oField->control_type = "checkbox";
			$oField->checked_value = new ffData("1", "Number");
			$oField->unchecked_value = new ffData("0", "Number");
			$oField->fixed_post_content = "<br/>";
			$oRecord->addContent($oField); 

			$oField = ffField::factory($cm->oPage);
			$oField->id = "answer";
			$oField->label = ffTemplate::_get_word_by_code("crowdfund_qa_modify_answer");
			$oField->extended_type = "Text";
			$oField->required = true;
			$oRecord->addContent($oField);
		}
		$cm->oPage->addContent($oRecord);

		if(!$cm->oPage->isXHR() && strlen($tpl_menu)) {
			$cm->oPage->addContent($tpl_menu);
		}
	}
}
function QAModify_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
	
	switch($action) {
				
		default:
	}
}
function QAModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
	if($action == "insert")
	{
		if(!isset($_REQUEST["keys"]["ID"]))
		{
			echo email_system("qa");
			die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "url" => FF_SITE_PATH . $component->page_path . "/" . $component->user_vars["smart_url"] ."?success&ret_url=" . $_REQUEST["ret_url"]), true));
		}
	}

}

  
?>
