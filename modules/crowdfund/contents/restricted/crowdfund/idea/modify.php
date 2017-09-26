<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}
//print_r($permission);
if(!$permission[global_settings("MOD_CROWDFUND_GROUP_PUBLIC_USER")])
{
$anagraph_params_company = "ct=" . global_settings("MOD_CROWDFUND_ANAGRAPH_TYPE_COMPANY");

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

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.*
                , " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url
                , " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
                , " . FF_PREFIX . "currency.name AS currency_name
                , " . CM_TABLE_PREFIX . "mod_crowdfund_idea.is_startup AS is_startup
                , " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner AS owner
                , (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title
	            FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
	            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
	                AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
	        ) AS name
            FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
                INNER JOIN " . FF_PREFIX . "currency ON " . FF_PREFIX . "currency.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID_currency
            WHERE 1 
                AND " . $sSQL_Where
                . ($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")]
                        ? ""
                        : " AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner = " . $db->toSql($UserNID, "Number")
                );

$db->query($sSQL);
if($db->nextRecord()) {
    $ID_idea = $db->getField("ID", "Number", true);
    $_REQUEST["keys"]["ID"] = $ID_idea;
    $languages = $db->getField("languages", "Text", true);	
    $smart_url = $db->getField("smart_url", "Text", true);
    $idea_name = $db->getField("name", "Text", true);
    $owner = $db->getField("owner", "Number", true);
    $currency_name = $db->getField("currency_name", "Text", true);
    $status_by_user = $db->getField("status_visible_decision", "Number", true); 
    $status_by_admin = $db->getField("status_by_admin", "Number", true); 
    $status_by_polihub = $db->getField("status_by_polihub", "Number", true); 
    $activated = $db->getField("activated", "Number", true); 
    $iban = $db->getField("iban", "Text", true); 
}


if($_REQUEST["keys"]["ID"] > 0)
{
    if(isset($_REQUEST["setactivated"])) {
        $db = ffDB_Sql::factory();
        if($_REQUEST["setactivated"]) {
            $status_visible_decision = true;
            if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_LIMIT_ACTIVE_PROJECT"))
            {
                $author = mod_crowdfund_get_author($UserNID, $ID_idea, true);
                if($author["total_project"] >= global_settings("MOD_CROWDFUND_IDEA_LIMIT_ACTIVE_PROJECT_EACH_YEARS"))
                {
                        mod_notifier_add_message_to_queue(ffTemplate::_get_word_by_code("crowdfund_idea_too_many_active_ideas_this_years") . " (" . global_settings("MOD_CROWDFUND_IDEA_LIMIT_ACTIVE_PROJECT_EACH_YEARS") . ")");
                        $status_visible_decision = false;
                }
                if($author["current_project_active_now"] >= global_settings("MOD_CROWDFUND_IDEA_LIMIT_ACTIVE_PROJECT"))
                {
                        mod_notifier_add_message_to_queue(ffTemplate::_get_word_by_code("crowdfund_idea_too_many_active_ideas") . " (" . global_settings("MOD_CROWDFUND_IDEA_LIMIT_ACTIVE_PROJECT") . ")");
                        $status_visible_decision = false;
                }
            }
        } else {
            $status_visible_decision = false;
        }

        $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
                    SET " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status_visible_decision = " . $db->toSql($status_visible_decision, "Number") . "
                    WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
        $db->execute($sSQL);

        if(global_settings("MOD_CROWDFUND_IDEA_ACTIVE_PROJECT_BY_ADMIN") && $status_visible_decision) {
            $to[0]["name"] = A_FROM_NAME;
            $to[0]["mail"] = "pale619@hotmail.com";//A_FROM_EMAIL;

            $fields["idea"]["title"] = $idea_name;
            $fields["active"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/modify?keys[ID]=" . $ID_idea . "&keys[smart_url]=" . $smart_url . "&frmAction=active&setstatusbyadmin=1&ret_url=" . urlencode(FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse);
   /*?*/         $fields["display"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/modify?keys[ID]=" . $ID_idea . "&keys[smart_url]=" . $smart_url . "&frmAction=active&ret_url=" . urlencode(FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse);

            if(check_function("process_mail")) 
            { 
                $rc = process_mail(email_system("Request Idea Activation"), $to, NULL, NULL, $fields, null, null, null, false, null, false);
            }
        }
        
        if($status_visible_decision && $status_by_admin && $status_by_polihub && strlen($iban) && !$activated)
        {
            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
                        SET " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated = " . $db->toSql(time(), "Number") . "
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
            $db->execute($sSQL);
        }

        if($cm->isXHR()) {
            if($_REQUEST["XHR_DIALOG_ID"]) {
                die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("IdeaModify")), true));
            } else {
                die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("IdeaModify")), true));
            }
        } else {
            ffRedirect($_REQUEST["ret_url"]);
        }
    } elseif(isset($_REQUEST["setstatusbyadmin"]))
    {
        if($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")])
        {
            $db = ffDB_Sql::factory();
            /*
            $sSQL_activated = " IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated > 0
                                                            , " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated
                                                            , " . $db->toSql(($status_visible_decision ? time() : 0), "Number") . "
                                                    )";  
            */
            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
                        SET " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status_by_admin = " . $db->toSql($_REQUEST["setstatusbyadmin"], "Number") . "
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
            $db->execute($sSQL);
            
            if(global_settings("MOD_CROWDFUND_IDEA_ACTIVE_PROJECT_BY_POLIHUB") && $_REQUEST["setstatusbyadmin"])
            {
                $to[0]["name"] = A_FROM_NAME;
                $to[0]["mail"] = "pale619@hotmail.com";//A_FROM_EMAIL;

                $fields["idea"]["title"] = $idea_name;
                $fields["active"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/modify?keys[ID]=" . $ID_idea . "&keys[smart_url]=" . $smart_url . "&frmAction=active&setstatusbyadmin=1&ret_url=" . urlencode(FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse);
       /*?*/    $fields["display"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/modify?keys[ID]=" . $ID_idea . "&keys[smart_url]=" . $smart_url . "&frmAction=active&ret_url=" . urlencode(FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse);

                if(check_function("process_mail")) 
                { 
                    $rc = process_mail(email_system("Request Idea Activation"), $to, NULL, NULL, $fields, null, null, null, false, null, false);
                }
            }
            
            if($status_by_user && $_REQUEST["setstatusbyadmin"] && $status_by_polihub && strlen($iban) && !$activated)
            {
                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
                            SET " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated = " . $db->toSql(time(), "Number") . "
                            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                $db->execute($sSQL);
            }
            
            if($cm->isXHR()) {
                if($_REQUEST["XHR_DIALOG_ID"]) {
                    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("IdeaModify")), true));
                } else {
                    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("IdeaModify")), true));
                }
            } else {
                    ffRedirect($_REQUEST["ret_url"]);
            }
        } 
    } elseif(isset($_REQUEST["setstatusbypolihub"]))
    {
        if($permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")])
        {
            $db = ffDB_Sql::factory();
            /*
            $sSQL_activated = " IF(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated > 0
                                                            , " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated
                                                            , " . $db->toSql(($status_visible_decision ? time() : 0), "Number") . "
                                                    )";  
            */
            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
                        SET " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status_by_polihub = " . $db->toSql($_REQUEST["setstatusbypolihub"], "Number") . "
                        WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
            $db->execute($sSQL);
            
            if($_REQUEST["setstatusbypolihub"])
            {
                $to[0]["name"] = A_FROM_NAME;
                $to[0]["mail"] = "pale619@hotmail.com";//A_FROM_EMAIL;

                $fields["idea"]["title"] = $idea_name;
                $fields["active"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/modify?keys[ID]=" . $ID_idea . "&keys[smart_url]=" . $smart_url . "&frmAction=active&setstatusbyadmin=1&ret_url=" . urlencode(FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse);
       /*?*/    $fields["display"]["link"] = "http://" . DOMAIN_INSET . FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/modify?keys[ID]=" . $ID_idea . "&keys[smart_url]=" . $smart_url . "&frmAction=active&ret_url=" . urlencode(FF_SITE_PATH . $cm->router->getRuleById("user_crowdfund_idea")->reverse);

                if(check_function("process_mail")) 
                { 
                    $rc = process_mail(email_system("Request Idea Activation"), $to, NULL, NULL, $fields, null, null, null, false, null, false);
                }
            }
            
            if($status_by_user && $status_by_admin && $_REQUEST["setstatusbypolihub"] && strlen($iban) && !$activated)
            {
                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea 
                            SET " . CM_TABLE_PREFIX . "mod_crowdfund_idea.activated = " . $db->toSql(time(), "Number") . "
                            WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                $db->execute($sSQL);
            }
            
            if($cm->isXHR()) {
                if($_REQUEST["XHR_DIALOG_ID"]) {
                    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("IdeaModify")), true));
                } else {
                    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("IdeaModify")), true));
                }
            } else {
                ffRedirect($_REQUEST["ret_url"]);
            }
        } 
    }
} 


if(strpos($cm->path_info, (string) $cm->router->getRuleById("user_crowdfund_idea")->reverse) === false
	&& strpos($cm->path_info, (string) $cm->router->getRuleById("crowdfund_idea_new")->reverse) === false
	&& strpos($cm->path_info, (string) $cm->router->getRuleById("crowdfund_idea_new_" . strtolower(LANGUAGE_INSET))->reverse) === false
) {
	$simple_interface = false;
} else {
	$simple_interface = true;
	if($_REQUEST["keys"]["ID"] > 0) {
		if($_REQUEST["IdeaModify_frmAction"] != "confirmdelete" && !isset($_REQUEST["setiban"])) {
			ffRedirect($cm->oPage->site_path . MOD_CROWDFUND_USER_PATH . "/basic/" . $smart_url);
		}
	}
}

$cm->oPage->tplAddJs("idea_modify"
	, array(
		"file" => "user_idea_modify.js"
		, "path" => "/modules/crowdfund/themes/javascript"
));

//set Header
if(check_function("set_header_page"))
	set_header_page($idea_name);


$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "IdeaModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_idea_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea";
$oRecord->insert_additional_fields["owner"] =  new ffData($UserNID, "Number");
$oRecord->insert_additional_fields["created"] =  new ffData(time(), "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));
if(isset($_REQUEST["setiban"]))
{
    $oRecord->buttons_options["delete"]["display"] = false;
} else
{
    $oRecord->addEvent("on_done_action", "IdeaModify_on_done_action");
    $oRecord->addEvent("on_done_action", "Idea_on_done_action", ffEvent::PRIORITY_LOW);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(isset($_REQUEST["setiban"]))
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "iban";
    $oField->container_class = "iban";
    $oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_iban");
    $oField->base_type = "Text";
    $oField->required = true;
    $oRecord->addContent($oField);
} else
{
    

if(!$simple_interface) {
	$oRecord->addTab("basic");
	$oRecord->setTabTitle("basic", ffTemplate::_get_word_by_code("crowdfund_idea_modify_basic"));

	$oRecord->addContent(null, true, "basic");
	$oRecord->groups["basic"] = array(
		                                     "title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_basic")
		                                     , "cols" => 1
		                                     , "tab" => "basic"
		                                  );


	$oField = ffField::factory($cm->oPage);
	$oField->id = "categories";
	$oField->container_class = "categories";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_categories");
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CATEGORY");
	$oField->extended_type = "Selection";
	$oField->widget = "autocompletetoken";
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_theme = "";
	$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
	$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
	$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
	$oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
	$oField->autocompletetoken_combo = true;
	$oField->autocompletetoken_compare_having = "name";
	$oField->source_SQL = "SELECT 
								" . CM_TABLE_PREFIX . "mod_crowdfund_categories.ID
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.title
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_categories = " . CM_TABLE_PREFIX . "mod_crowdfund_categories.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
								) AS name
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories.visible > 0
							[AND] [WHERE]
							[HAVING]
							[ORDER] [COLON] name
							[LIMIT]"; 
	$oRecord->addContent($oField, "basic");

	
	if (global_settings("MOD_CROWDFUND_EXPIRATION_DECISION"))
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
		$oField->multi_select_one = false;
		$oRecord->addContent($oField, "basic");
	
	}
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "cover";
	$oField->container_class = "cover";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_cover");
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
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_COVER");
	//$oField->uploadify_model = "horizzontal";
	$oRecord->addContent($oField, "basic");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "video";
	$oField->container_class = "video";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_video");
	$oField->base_type = "Text";
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_VIDEO");
	$oRecord->addContent($oField, "basic");
	
	$sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang
			FROM " . FF_PREFIX . "languages 
			WHERE FIND_IN_SET(" . FF_PREFIX . "languages.ID, " . $db->toSql($languages) . ")";
	$db->query($sSQL);
	if($db->nextRecord()) {
		$count_lang = $db->getField("count_lang", "Number", true);
	}
	
	$oDetail = ffDetails::factory($cm->oPage);
	if($count_lang > 1) {
	    $oDetail->tab = true;
	    $oDetail->tab_label = "language";
	}
	$oDetail->id = "IdeaDetail";
	$oDetail->title = "";//ffTemplate::_get_word_by_code("drafts_modify_detail_title");
	$oDetail->widget_discl_enable = false;
	$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages";
	$oDetail->order_default = "ID";
	$oDetail->fields_relationship = array ("ID_idea" => "ID");
	$oDetail->display_new = false;
	$oDetail->display_delete = false;
	$oDetail->addEvent("on_do_action", "IdeaDetail_on_do_action");
	$oDetail->auto_populate_insert = true;
	$oDetail->populate_insert_SQL = "SELECT 
	                                    " . FF_PREFIX . "languages.ID AS ID_languages
	                                    , " . FF_PREFIX . "languages.description AS language 
	                                    , " . FF_PREFIX . "languages.code AS code_lang 
	                                FROM " . FF_PREFIX . "languages
	                                WHERE
	                                " . FF_PREFIX . "languages.status = '1'";
	$oDetail->auto_populate_edit = true;
	$oDetail->populate_edit_SQL = "SELECT 
	                                    " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID AS ID
	                                    , " . FF_PREFIX . "languages.ID AS ID_languages
	                                    , " . FF_PREFIX . "languages.description AS language
	                                    , " . FF_PREFIX . "languages.code AS code_lang 
	                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url AS smart_url
	                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title AS title
	                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.teaser AS teaser
	                                    , " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.description AS description
	                                FROM " . FF_PREFIX . "languages
	                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON  " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = [ID_FATHER]
	                                WHERE
	                                    " . FF_PREFIX . "languages.status = '1'
	                                    AND FIND_IN_SET(" . FF_PREFIX . "languages.ID, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
	                                    												FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
	                                    												WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
	                                    											)
	                                    	)											
	                                ";

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oDetail->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "language";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_languages");
	$oField->store_in_db = false;
	$oDetail->addHiddenField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_languages";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_ID_languages");
	$oField->base_type = "Number";
	$oDetail->addHiddenField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "code_lang";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_code");
	$oField->store_in_db = false;
	$oDetail->addHiddenField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "smart_url";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_smart_url");
	$oDetail->addHiddenField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "title";
	$oField->container_class = "title";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_title");
	$oField->required = true;
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "teaser";
	$oField->container_class = "teaser";
	$oField->extended_type = "Text";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_teaser");
	$oField->required = true;
	$oField->properties["maxlength"] = 200;
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "description";
	$oField->container_class = "description";
	$oField->display_label = false;
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_value");
	$oField->control_type = "textarea";
	if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
	    $oField->widget = "ckeditor";
	} else {
	    $oField->widget = "";
	}
	$oField->ckeditor_group_by_auth = true;
	$oField->extended_type = "Text";
	$oField->base_type = "Text";
	$oDetail->addContent($oField);

	$oRecord->addContent($oDetail, "basic");
	$cm->oPage->addContent($oDetail);


	$oRecord->addTab("requestoffer");
	$oRecord->setTabTitle("requestoffer", ffTemplate::_get_word_by_code("crowdfund_idea_modify_requestoffer"));

	$oRecord->addContent(null, true, "requestoffer");
	$oRecord->groups["requestoffer"] = array(
		                                     "title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_requestoffer")
		                                     , "cols" => 1
		                                     , "tab" => "requestoffer"
		                                  );
        /*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "is_startup";
	$oField->container_class = "startup";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_is_startup");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_IS_STARTUP");
	$oField->multi_pairs = array (
		                        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
		                        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
		                   );
	$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
	
	$oRecord->addContent($oField, "requestoffer");
        */
	$oField = ffField::factory($cm->oPage);
	$oField->id = "capital_funded";
	$oField->container_class = "capital-funded";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_capital_funded");
	$oField->base_type = "Number";
	$oField->widget = "slider"; 
	$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
	$oField->min_val = 0;
	$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
	$oField->step = 1;
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CAPITAL_FUNDED");
	$oRecord->addContent($oField, "requestoffer");
	
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
						GROUP BY anagraph.ID
						ORDER BY Fname";
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("crowdfund_company_no_exist");
	$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params_company;
	$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
	$oField->resources[] = "AnagraphModify";
	//$oField->required = true;
	$oRecord->addContent($oField, "requestoffer");
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "goal";
	$oField->container_class = "goal";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal");
	$oField->base_type = "Number";
	$oField->required = true;
	$oField->widget = "slider";
	
	$oField->min_val = 0;
	$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
	$oField->step = global_settings("MOD_CROWDFUND_GOAL_STEP");
	$oField->fixed_post_content = ',00 <span class="symbol">' . $currency_name . '</span>';
	$oRecord->addContent($oField, "requestoffer");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "goal_current";
	$oField->container_class = "goal_current";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal_current");
	$oField->base_type = "Number";
	$oField->widget = "slider";
	$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
	$oField->min_val = global_settings("MOD_CROWDFUND_GOAL_STEP");
	$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
	$oField->step = global_settings("MOD_CROWDFUND_GOAL_STEP");
	$oRecord->addContent($oField, "requestoffer");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "equity";
	$oField->container_class = "equity";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_equity");
	$oField->base_type = "Number";
	$oField->required = true;
	$oField->widget = "slider";
	$oField->fixed_post_content = '<span class="symbol">' . "%" . '</span>';
	$oField->min_val = 0;
	$oField->max_val = 100;
	$oField->step = 1;
	$oRecord->addContent($oField, "requestoffer");

	if($_REQUEST["keys"]["ID"] > 0) {
		$oGrid = ffGrid::factory($cm->oPage);
		$oGrid->full_ajax = true;
		$oGrid->id = "Pledge";
		$oGrid->title = ffTemplate::_get_word_by_code("pledge_title");
		$oGrid->source_SQL = "SELECT
		                            " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.*
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
		                        WHERE 1
		                        [AND] [WHERE] 
		                        [HAVING]
		                        [ORDER]";

		$oGrid->order_default = "price";
		$oGrid->use_search = false;
		$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/pledge/modify";
		$oGrid->addit_insert_record_param = "idea=" . $_REQUEST["keys"]["ID"] . "&";
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

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID_idea";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

		// Campi di ricerca

		// Campi visualizzati
		$oField = ffField::factory($cm->oPage);
		$oField->id = "price";
		$oField->container_class = "price";
		$oField->label = ffTemplate::_get_word_by_code("pledge_price");
		$oField->base_type = "Number";
		$oField->app_type = "Currency";
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "title";
		$oField->container_class = "title";
		$oField->label = ffTemplate::_get_word_by_code("pledge_name");
		$oGrid->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "description";
		$oField->container_class = "description";
		$oField->label = ffTemplate::_get_word_by_code("pledge_description");
		$oGrid->addContent($oField);

		$oRecord->addContent($oGrid, "requestoffer");
		$cm->oPage->addContent($oGrid);
	}

	$oRecord->addTab("company");
	$oRecord->setTabTitle("company", ffTemplate::_get_word_by_code("crowdfund_idea_modify_company"));

	$oRecord->addContent(null, true, "company");
	$oRecord->groups["company"] = array(
		                                     "title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_company")
		                                     , "cols" => 1
		                                     , "tab" => "company"
		                                  );


		                                  
		                                  
	$oRecord->addTab("businessplan");
	$oRecord->setTabTitle("businessplan", ffTemplate::_get_word_by_code("crowdfund_idea_modify_businessplan"));

	$oRecord->addContent(null, true, "businessplan");
	$oRecord->groups["businessplan"] = array(
		                                     "title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_businessplan")
		                                     , "cols" => 1
		                                     , "tab" => "businessplan"
		                                  );

	$oRecord->addTab("setting");
	$oRecord->setTabTitle("setting", ffTemplate::_get_word_by_code("crowdfund_idea_modify_setting"));

	$oRecord->addContent(null, true, "setting");
	$oRecord->groups["setting"] = array(
		                                     "title" => ffTemplate::_get_word_by_code("crowdfund_idea_modify_setting")
		                                     , "cols" => 1
		                                     , "tab" => "setting"
		                                  );

	if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_INNOVATIVE"))
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
		$oRecord->addContent($oField, "setting");

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
		$oRecord->addContent($oField, "setting");	

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
		$oRecord->addContent($oField, "setting");

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
		$oRecord->addContent($oField, "setting");
	}
		                                  
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
	                                 
	$oField->required = true;
	$oField->widget = "checkgroup";
	$oField->grouping_separator = ",";
	$oRecord->addContent($oField, "setting");
	
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
	                                 
	$oField->required = true;
	$oRecord->addContent($oField, "setting");	
	
	if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_SKYPE"))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "skype_account";
		$oField->container_class = "sky_account";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_skype_account");
		$oRecord->addContent($oField, "setting");
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
	$oRecord->addContent($oField, "setting");
} else {
	$oRecord->insert_additional_fields["goal_step"] = new ffData(global_settings("MOD_CROWDFUND_GOAL_STEP"), "Number");
		
	if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_INNOVATIVE"))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "is_innovative";
		$oField->container_class = "innovative";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_is_innovative");
		$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_IS_INNOVATIVE");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array (
									array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("yes"))),
									array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("no")))
							   );
		$oField->required = true;
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
	$oField->id = "title";
	$oField->container_class = "title";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_title");
	$oField->required = true;
	$oField->store_in_db = false;
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "teaser";
	$oField->container_class = "teaser";
	$oField->extended_type = "Text";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_detail_teaser");
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_TEASER");
	$oField->store_in_db = false;
	$oRecord->addContent($oField);

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
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CURRENCY");
	$oRecord->addContent($oField);	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "goal";
	$oField->container_class = "goal";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal");
	$oField->base_type = "Number";
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_GOAL");
	$oField->widget = "slider";
	$oField->fixed_post_content = ',00 <span class="symbol">' . $currency_name . '</span>';
	$oField->min_val = 0;
	$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
	$oField->step = global_settings("MOD_CROWDFUND_GOAL_STEP");
	$oRecord->addContent($oField);
/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "goal_step";
	$oField->container_class = "goal_step";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_goal_step");
	$oField->base_type = "Number";
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_GOAL_STEP");
	$oField->widget = "slider";
	$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
	$oField->min_val = 0;
	$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL") / 10;
	$oField->step = global_settings("MOD_CROWDFUND_GOAL_STEP");
	$oRecord->addContent($oField);*/
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "categories";
	$oField->container_class = "categories";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_categories");
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CATEGORY");
	$oField->extended_type = "Selection";
	$oField->widget = "autocompletetoken";
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_theme = "";
	$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
	$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
	$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
	$oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
	$oField->autocompletetoken_combo = true;
	$oField->autocompletetoken_compare_having = "name";
	$oField->source_SQL = "SELECT 
								" . CM_TABLE_PREFIX . "mod_crowdfund_categories.ID
								, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.title
									FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages
									WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_categories = " . CM_TABLE_PREFIX . "mod_crowdfund_categories.ID
										AND " . CM_TABLE_PREFIX . "mod_crowdfund_categories_rel_languages.ID_languages = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
								) AS name
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_categories
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_categories.visible > 0
							[AND] [WHERE]
							[HAVING]
							[ORDER] [COLON] name
							[LIMIT]"; 
	$oRecord->addContent($oField);

	
	if (global_settings("MOD_CROWDFUND_EXPIRATION_DECISION"))
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
		$oField->multi_select_one = false;
		$oRecord->addContent($oField); 
	
	}
	
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
	$oRecord->addContent($oField);

	if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_SKYPE"))
	{
		$oField = ffField::factory($cm->oPage);
		$oField->id = "skype_account";
		$oField->container_class = "sky_account";
		$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_skype_account");
		$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_SKYPE_ACCOUNT");
		$oRecord->addContent($oField);
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "visible";
	$oField->container_class = "visible";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_visible");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array (
	                            array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("crowdfund_registered_people"))),
	                            array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("crowdfund_all_people")))
	                       );
	$oField->required = true;
	$oRecord->addContent($oField);

	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "is_startup";
	$oField->container_class = "startup";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_is_startup");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_IS_STARTUP");
	$oField->multi_pairs = array (
		                        array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
		                        array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
		                   );
	$oField->fixed_post_content = mod_crowdfund_process_help_hint($oField->id);
	$oRecord->addContent($oField);
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
						GROUP BY anagraph.ID
						ORDER BY Fname";
	$oField->widget = "activecomboex";
	$oField->actex_update_from_db = true;
	
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("crowdfund_company_no_exist");
	$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/anagraph/all/modify?" . $anagraph_params_company;
	$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => null);
	$oField->resources[] = "AnagraphModify";
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_ANAGRAPH_COMPANY");
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "capital_funded";
	$oField->container_class = "capital-funded";
	$oField->label = ffTemplate::_get_word_by_code("crowdfund_idea_modify_capital_funded");
	$oField->base_type = "Number";
        //$oField->extended_type ="Number";
        //$oField->extended_type = "Float";
	//$oField->widget = "slider";
	$oField->fixed_post_content = '<span class="symbol">' . $currency_name . '</span>';
	$oField->min_val = 0;
	$oField->max_val = global_settings("MOD_CROWDFUND_MAX_GOAL");
	$oField->step = 1;
	$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_CAPITAL_FUNDED");
	$oRecord->addContent($oField);
	
	
}
}
$cm->oPage->addContent($oRecord);
}



function IdeaDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    switch($action) {
    	case "insert":
    	case "update":
		    $user_permission = get_session("user_permission");
			if(strlen($user_permission["lang"]["current"]["code"])) {
				$language_code = $user_permission["lang"]["current"]["code"];
			} else {
				$language_code = LANGUAGE_DEFAULT;
			}
		
		    if(is_array($component->recordset) && count($component->recordset)) {
		        $ID_node = $component->main_record[0]->key_fields["ID"]->value;

		        foreach($component->recordset AS $rst_key => $rst_value) {
					$smart_url = ffCommon_url_rewrite($component->recordset[$rst_key]["title"]->getValue());
					$ID_languages = $component->recordset[$rst_key]["ID_languages"]->getValue();

					$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url
       					 FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
       					 WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea <> " . $db->toSql($ID_node, "Number") . "
       						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.smart_url = " . $db->toSql($smart_url) . "
       						AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql($ID_languages, "Number");
					$db->query($sSQL);
					if($db->nextRecord()) {
						$component->displayError(ffTemplate::_get_word_by_code("crowdfund_idea_smart_url_not_unic") . "(" . $component->recordset[$rst_key]["language"]->getValue() . ")");
						return true;
					}

					$component->recordset[$rst_key]["smart_url"]->setValue($smart_url);
					if($component->recordset[$rst_key]["code_lang"]->getValue() == $language_code) {
						$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
									" . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql($component->recordset[$rst_key]["smart_url"]->getValue()) . " 
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($ID_node);
						$db->execute($sSQL);
					}

		        }
		    }
	    	break;
	    default:
	}
}  

function IdeaModify_on_done_action($component, $action) {
	$cm = cm::getInstance();
    $db = ffDB_Sql::factory();
	$UserNID = get_session("UserNID");
	switch($action) {
    	case "insert":
    	case "update":				
    		$user_permission = get_session("user_permission");
			if(strlen($user_permission["lang"]["current"]["code"])) {
				$language_code = $user_permission["lang"]["current"]["code"];
			} else {
				$language_code = LANGUAGE_DEFAULT;
			}

			$sSQL = "SELECT anagraph.*
						FROM anagraph
						WHERE anagraph.uid = " . $db->toSql($UserNID, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				$crowdfund_public_path = mod_crowdfund_get_path_by_lang("public");

				$ID_user = $db->getField("ID", "Number", true);  
				$user_name = $db->getField("name", "Text", true);
				$user_surname = $db->getField("surname", "Text", true);
				$user_email = $db->getField("email", "Text", true);
				$complete_name = $user_name . " " . $user_surname;
				
				$title = $component->form_fields["title"]->getValue();
				$smart_url = ffCommon_url_rewrite($title);					
				$teaser = $component->form_fields["teaser"]->getValue();
				
				if(check_function("system_lib_facebook")) 
				{
					
					$res = facebook_publish($user_name . " " . $user_surname . " " . ffTemplate::_get_word_by_code("crowdfund_new_project") . " " . $title
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
				$res = array("class" => "new_project", "label" => ffTemplate::_get_word_by_code("crowdfund_label_new_project"));
			}
			if(isset($component->form_fields["languages"])) {
				$sSQL = "SELECT " . FF_PREFIX . "languages.ID 
						FROM " . FF_PREFIX . "languages 
						WHERE FIND_IN_SET(" . FF_PREFIX . "languages.ID, " . $db->toSql($component->form_fields["languages"]->getValue()) . ")
							AND " . FF_PREFIX . "languages.code = " . $db->toSql($language_code);
				$db->query($sSQL);
				if(!$db->nextRecord()) {
					$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
								" . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages = CONCAT(" . CM_TABLE_PREFIX . "mod_crowdfund_idea.languages
										, ','
										, (SELECT " . FF_PREFIX . "languages.ID FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.code = " . $db->toSql($language_code) . ")
								)
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($component->key_fields["ID"]->value);
					$db->execute($sSQL);
				}
			}

			if($action == "insert") {
				if(isset($component->form_fields["title"])) {
					$title = $component->form_fields["title"]->getValue();
					$smart_url = ffCommon_url_rewrite($title);
					
					if(isset($component->form_fields["teaser"])) {
						$teaser = $component->form_fields["teaser"]->getValue();
					}
					
					$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea SET 
									" . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql($smart_url) . "
									, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_diff =  (" . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal - " . CM_TABLE_PREFIX . "mod_crowdfund_idea.goal_current)
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($component->key_fields["ID"]->value);
					$db->execute($sSQL);

					$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages 
							(
								ID
								, ID_idea
								, ID_languages
								, smart_url
								, title
								, description
								, teaser
							)
							VALUES
							(
								null
								, " . $db->toSql($component->key_fields["ID"]->value) . "
								, (SELECT " . FF_PREFIX . "languages.ID FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.code = " . $db->toSql($language_code) . ")
								, " . $db->toSql($smart_url) . "
								, " . $db->toSql($title) . "
								, ''
								, " . $db->toSql($teaser) . "
							)";
					$db->execute($sSQL);
					
					$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_crowdfund_idea_team 
							(
								ID
								, ID_idea
								, ID_user_anagraph
								, last_update
								, ID_role
							)
							VALUES
							(
								null
								, " . $db->toSql($component->key_fields["ID"]->value) . "
								, (SELECT anagraph.ID 
										FROM anagraph
										WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . "
										ORDER BY anagraph.ID
										LIMIT 1
									)
								, " . $db->toSql(time(), "Number") . "
								, " . $db->toSql(2, "Number") . "
							)";
					$db->execute($sSQL);
					ffredirect($cm->oPage->site_path . $cm->router->getRuleById("user_crowdfund_idea")->reverse . "/basic/" . $smart_url . (strlen($_REQUEST["ret_url"]) ? "?ret_url=" . urlencode($_REQUEST["ret_url"]) : ""));
				
				
				}
			}
	 		break;
		case "confirmdelete":
			$sSQL = "DELETE 
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			$sSQL = "DELETE 
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			$sSQL = "DELETE 
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_attach 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_attach.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			$sSQL = "DELETE 	
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			$sSQL = "DELETE 		
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_businessplan_rel_languages.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			$sSQL = "DELETE 			
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_cash_flow.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			$sSQL = "DELETE 				
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_timeline
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages
							INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_timeline 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				do
				{
					$ID_timeline[] = $db->getField("ID_timeline", "Number", true);
					
				} while ($db->nextRecord());
				foreach ($ID_timeline as $ID_timeline_key => $ID_timeline_value) 
				{
					$sSQL = "DELETE 					
								FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages 
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline_rel_languages.ID_timeline = " . $db->toSql($ID_timeline_value, "Number");
					$db->execute($sSQL);
				}
			}
			$sSQL = "DELETE 				
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_timeline.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			$sSQL = "DELETE 		
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_income_statement.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number"); 
			$db->execute($sSQL);
			
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages
							INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge  
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				do
				{
					$ID_pledge[] = $db->getField("ID_pledge", "Number", true);
					
				} while ($db->nextRecord());
				foreach ($ID_pledge as $ID_pledge_key => $ID_pledge_value) 
				{
					$sSQL = "DELETE 					
								FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages 
								WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge_rel_languages.ID_pledge = " . $db->toSql($ID_pledge_value, "Number");
					$db->execute($sSQL);
				}
			}
			
			$sSQL = "DELETE 			
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_pledge.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			
			$sSQL = "DELETE 			
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa 
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_qa.ID_idea = " . $db->toSql($component->key_fields["ID"]->value, "Number");
			$db->execute($sSQL);
			break;
	 	default:	
	}
}
?>
