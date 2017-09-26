<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();

if($_REQUEST["keys"]["ID"] > 0 && isset($_REQUEST["setfavourite"]))
{
   
    if($_REQUEST["setfavourite"]) {
        $favourite_decision = true;
    } else {
        $favourite_decision = false;
    }

    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_film 
                SET " . CM_TABLE_PREFIX . "mod_collection_film.favourite = " . $db->toSql($favourite_decision, "Number") . "
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
    
    if($cm->isXHR()) {
        if($_REQUEST["XHR_DIALOG_ID"]) {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("filmModify")), true));
        } else {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("filmModify")), true));
        }
    } else {
        ffRedirect($_REQUEST["ret_url"]);
    }
}

if(isset($_REQUEST["choice-title"]) && strlen($_REQUEST["choice-title"]))
{
    $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse);
    if(isset($_REQUEST["list-title"]) && strlen($_REQUEST["list-title"])) {
        $params = "&list-title=" . $_REQUEST["list-title"];
        $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse . '/modify/list-title?list_title=' . $_REQUEST["list-title"]);
    } 
    if(strpos($_REQUEST["choice-title"], "-")) {
        $stringID = str_replace("-", ",", $_REQUEST["choice-title"]);
    } else {
        $stringID = $_REQUEST["choice-title"];
    }
    $filename = cm_cascadeFindTemplate("/contents/template-decision.html", "collection");
    //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/template-decision.html", $cm->oPage->theme);

    $tpl = ffTemplate::factory(ffCommon_dirname($filename));
    $tpl->load_file("template-decision.html", "main");
    
    $tpl->set_var("choose_title_block", ffTemplate::_get_word_by_code("mod_collection_title_already_stored"));
    $tpl->set_var("choose_title_modify_already_stored", ffTemplate::_get_word_by_code("mod_collection_title_already_stored_modify"));
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film.*
                    , GROUP_CONCAT( " . CM_TABLE_PREFIX . "mod_collection_film_director.name) AS director_name
                FROM " . CM_TABLE_PREFIX . "mod_collection_film
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_film_director ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_film_director.ID,  " . CM_TABLE_PREFIX . "mod_collection_film.director)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film.ID IN (" . $db->toSql($stringID, "Text", false) . ")
                GROUP BY ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_film"]->reverse . "/modify?keys[ID]=" . $db->getField("ID", "number", true) . $params . $ret_url);
            $tpl->set_var("choose_elem_title", $db->getField("title", "Text", true));
            $director_string = $db->getField("director_name", "Text", true);
            if(strlen($director_string))
            {
                $tpl->set_var("choose_elem_detail", $director_string);
                $tpl->parse("SezElemDetail", false);
            }
            $tpl->parse("SezElemChoose", true);
        } while($db->nextRecord()); 
    }
    
    $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_film"]->reverse . "/scraping/imdb/?title=" . $_REQUEST["title"] . $params . $ret_url);
    $tpl->set_var("media_type", "film");
    $cm->oPage->addContent($tpl->rpparse("main", false));
} elseif(isset($_REQUEST["choice-ean"]) && strlen($_REQUEST["choice-ean"]))
{
    $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse);
    if(isset($_REQUEST["list-ean"]) && strlen($_REQUEST["list-ean"])) {
        $params = "&list-ean=" . $_REQUEST["list-ean"];
        $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse . '/modify/list-ean?list_ean=' . $_REQUEST["list-ean"]);
    } 
    if(strpos($_REQUEST["choice-ean"], "-")) {
        $stringID = str_replace("-", ",", $_REQUEST["choice-ean"]);
    } else {
        $stringID = $_REQUEST["choice-ean"];
    }
    $filename = cm_cascadeFindTemplate("/contents/template-decision.html", "collection");
    //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/template-decision.html", $cm->oPage->theme);

    $tpl = ffTemplate::factory(ffCommon_dirname($filename));
    $tpl->load_file("template-decision.html", "main");
    
    $tpl->set_var("choose_title_block", ffTemplate::_get_word_by_code("mod_collection_title_already_stored"));
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film.*
                    , GROUP_CONCAT( " . CM_TABLE_PREFIX . "mod_collection_film_director.name) AS director_name
                FROM " . CM_TABLE_PREFIX . "mod_collection_film
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_film_director ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_film_director.ID,  " . CM_TABLE_PREFIX . "mod_collection_film.director)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film.ID IN (" . $db->toSql($stringID, "Text", false) . ")
                GROUP BY ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_film"]->reverse . "/modify?keys[ID]=" . $db->getField("ID", "number", true) . $params . $ret_url);
            $tpl->set_var("choose_elem_title", $db->getField("title", "Text", true));
            $director_string = $db->getField("director_name", "Text", true);
            if(strlen($director_string))
            {
                $tpl->set_var("choose_elem_detail", $director_string);
                $tpl->parse("SezElemDetail", false);
            }
            $tpl->parse("SezElemChoose", true);
        } while($db->nextRecord()); 
    }
    
    $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_film"]->reverse . "/scraping/imdb/?title=" . $_REQUEST["title"] . $params . $ret_url);
    $tpl->set_var("media_type", "film");
    $cm->oPage->addContent($tpl->rpparse("main", false));
} else 
{
    $action_decided = "";
if(strlen($cm->real_path_info))
    $action_decided = basename($cm->real_path_info);

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "filmModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("mod_collection_film_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_collection_film";
$oRecord->addEvent("on_do_action", "filmModify_on_do_action");
$oRecord->addEvent("on_done_action", "filmModify_on_done_action");
$oRecord->user_vars["action_decided"] = $action_decided;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if((isset($_REQUEST["keys"]["ID"])) || strlen($action_decided))
{
    if($_REQUEST["keys"]["ID"] > 0 || ($action_decided == "manually"))
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "cover";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_cover");
        $oField->base_type = "Text";
        $oField->extended_type = "File";
        $oField->file_storing_path = DISK_UPDIR . "/collector/film/";
        $oField->file_temp_path = DISK_UPDIR . "/collector/film/";
        //$oField->file_max_size = MAX_UPLOAD;
        $oField->file_full_path = true;
        $oField->file_check_exist = true;
        $oField->file_normalize = true;
        $oField->file_show_preview = true;
        $oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
        $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/70x70/[_FILENAME_]";
        //$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
        //$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[_FILENAME_]";
        $oField->control_type = "file";
        $oField->file_show_delete = true;
        $oField->widget = "uploadify"; 
        if(check_function("set_field_uploader")) { 
                $oField = set_field_uploader($oField);
        }
        //$oField->required = global_settings("MOD_CROWDFUND_IDEA_REQUIRE_COVER");
        //$oField->uploadify_model = "horizzontal";
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "title";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_title");
        $oField->required = true;
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "original_title";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_original_title");
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "year";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_year");
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "vote";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_imdb_vote");
        $oRecord->addContent($oField);
	
        $oField = ffField::factory($cm->oPage);
        $oField->id = "personal_vote";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_personal_vote");
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "link";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_imbd_link");
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "duration";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_duration");
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "genre";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_genre");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name"; 
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_genre.ID
                                        ,  " . CM_TABLE_PREFIX . "mod_collection_film_genre.name
                                FROM " . CM_TABLE_PREFIX . "mod_collection_film_genre
                                [AND] [WHERE]
                                [HAVING]
                                [ORDER] [COLON] name
                                [LIMIT]"; 
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "description";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_description");
        $oField->control_type = "textarea";
        $oRecord->addContent($oField);
	
        $oField = ffField::factory($cm->oPage);
        $oField->id = "director";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_director");
        $oField->extended_type = "Selection";
        $oField->widget = "autocomplete";
        $oField->autocomplete_minLength = 0;
        $oField->autocomplete_combo = true;
        $oField->autocomplete_compare_having = "name";
        $oField->autocomplete_readonly = true;
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_director.ID
        							,  " . CM_TABLE_PREFIX . "mod_collection_film_director.name
	                            FROM " . CM_TABLE_PREFIX . "mod_collection_film_director
	                            [AND] [WHERE]
	                            [HAVING]
	                            [ORDER] [COLON] name
	                            [LIMIT]"; 
        $oField->actex_update_from_db = true; 
        $oRecord->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "actor";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_actor");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name"; 
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_actor.ID
                                    ,  " . CM_TABLE_PREFIX . "mod_collection_film_actor.name
                                FROM " . CM_TABLE_PREFIX . "mod_collection_film_actor
                                [AND] [WHERE]
                                [HAVING]
                                [ORDER] [COLON] name
                                [LIMIT]"; 
        $oRecord->addContent($oField);
		
	$oDetail = ffDetails::factory($cm->oPage);
        $oDetail->id = "filmsupport";
        $oDetail->title = ffTemplate::_get_word_by_code("mod_collection_film_modify_suppot");
        $oDetail->src_table = "cm_mod_collection_film_rel_fields";
        $oDetail->order_default = "ID";
        $oDetail->fields_relationship = array("ID_film" => "ID");

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID";
        $oField->base_type = "Number";
        $oDetail->addKeyField($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_support";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_suppot");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
       
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name"; 
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_support.ID
                                    ,  " . CM_TABLE_PREFIX . "mod_collection_film_support.name
                                FROM " . CM_TABLE_PREFIX . "mod_collection_film_support
                                [AND] [WHERE]
                                [HAVING]
                                [ORDER] [COLON] name
                                [LIMIT]"; 
        $oDetail->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_quality";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_quality");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name"; 
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_quality.ID
                                    ,  " . CM_TABLE_PREFIX . "mod_collection_film_quality.name
                                FROM " . CM_TABLE_PREFIX . "mod_collection_film_quality
                                [AND] [WHERE]
                                [HAVING]
                                [ORDER] [COLON] name
                                [LIMIT]"; 
        $oDetail->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "language";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_language");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "description"; 
        $oField->source_SQL = "SELECT ff_languages.ID
                                    ,  ff_languages.description
                                FROM ff_languages
                                [AND] [WHERE]
                                [HAVING]
                                [ORDER] [COLON] description
                                [LIMIT]"; 
        $oDetail->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "subtitle";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_subtitle");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "description"; 
        $oField->source_SQL = "SELECT ff_languages.ID
                                    ,  ff_languages.description
                                FROM ff_languages
                                [AND] [WHERE]
                                [HAVING]
                                [ORDER] [COLON] description
                                [LIMIT]"; 
        $oDetail->addContent($oField);
        
        $oRecord->addContent($oDetail);
        $cm->oPage->addContent($oDetail);	
		
		
    } elseif($action_decided == "title")
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "title";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_title");
        $oField->required = true;
        $oRecord->addContent($oField);
    } elseif($action_decided == "list-title")
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "list_title";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_list_title");
        if(isset($_REQUEST["list_title"])) {
            $oField->default_value = new ffData($_REQUEST["list_title"]);
            $oField->data_type = "";
        }
        $oField->required = true;
        $oField->control_type = "textarea";
        $oRecord->addContent($oField);
    } elseif($action_decided == "ean")
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ean";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_ean");
        $oField->required = true;
        $oRecord->addContent($oField);
    } elseif($action_decided == "list-ean")
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "list_ean";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_list_ean");
        if(isset($_REQUEST["list_ean"])) {
            $oField->default_value = new ffData($_REQUEST["list_ean"]);
            $oField->data_type = "";
        }
        $oField->required = true;
        $oField->control_type = "textarea";
        $oRecord->addContent($oField);
    }
} else 
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "insert_new";
    $oField->label = ffTemplate::_get_word_by_code("mod_collection_film_modify_addnew_how");
    $oField->store_in_db = false;
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array (
	                        array(new ffData("manually"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_manually")))
	                        , array(new ffData("title"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_by_title")))
	                        , array(new ffData("list-title"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_by_list_title")))
                                /*, array(new ffData("ean"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_by_EAN")))
                                , array(new ffData("list-ean"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_by_list_EAN")))*/
	                   );
    $oField->multi_select_one = false;
    $oRecord->addContent($oField);
}
$cm->oPage->addContent($oRecord);
}


function filmModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        switch ($action) 
        {
            case "insert":
                if (isset($component->form_fields["insert_new"]) && strlen($component->form_fields["insert_new"]->getValue())) 
                {
                    if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                        die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . $component->parent[0]->page_path . "/modify/" . $component->form_fields["insert_new"]->getValue() . "?ret_url=" . urlencode($component->parent[0]->site_path . $component->parent[0]->page_path), "close" => true, "refresh" => true, "doredirects" => true), true));
                    } else {
                        ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "/modify/" . $component->form_fields["insert_new"]->getValue() . "?ret_url=" . urlencode($component->parent[0]->site_path . $component->parent[0]->page_path));
                    }
                }
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "title") 
                {
                    $title = $component->form_fields["title"]->getValue();
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_film
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_film.smart_url = " . $db->toSql(ffcommon_url_rewrite($title));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringFilm))
                                $stringFilm .= "-";
                            $stringFilm .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-title=" . $stringFilm . "&title=" . ffCommon_url_rewrite($title), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-title=" . $stringFilm . "&title=" . ffCommon_url_rewrite($title));
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/imdb/?title=" . ffCommon_url_rewrite($component->form_fields["title"]->getValue()), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/imdb/?title=" . ffCommon_url_rewrite($component->form_fields["title"]->getValue()));
                        }
                    }
                }
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "list-title") 
                {
                    $list_title = $component->form_fields["list_title"]->getValue();
                    $arrTitle = explode("--", $list_title);
                    $title = $arrTitle[0];
                    foreach($arrTitle AS $key => $value)
                    {
                        if($key != 0) {
                            if(strlen($new_list))
                                $new_list .= "--";
                            $new_list .=  $value;  
                        }
                        if(strlen($new_list))
                            $params = "&list-title=" . $new_list;
                    }
                    
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_film
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_film.smart_url = " . $db->toSql(ffcommon_url_rewrite($title));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringFilm))
                                $stringFilm .= "-";
                            $stringFilm .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-title=" . $stringFilm . "&title=" . ffCommon_url_rewrite($title) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-title=" . $stringFilm . "&title=" . ffCommon_url_rewrite($title) . $params);
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/imdb/?title=" . ffCommon_url_rewrite($title) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/imdb/?title=" . ffCommon_url_rewrite($title) . $params);
                        }
                    }
                }
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "ean") 
                {
                    $ean = $component->form_fields["ean"]->getValue();
                    if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                        die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/ibs/?ean=" . ffCommon_url_rewrite($component->form_fields["ean"]->getValue()), "close" => true, "refresh" => true, "doredirects" => true), true));
                    } else {
                        ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/ibs/?ean=" . ffCommon_url_rewrite($component->form_fields["ean"]->getValue()));
                    }
                }
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "list-ean") 
                {
                    $list_ean = $component->form_fields["list_ean"]->getValue();
                    $arrEan = explode("-", $list_ean);
                    $ean = $arrEan[0];
                    foreach($arrEan AS $key => $value)
                    {
                        if($key != 0) {
                            if(strlen($new_list))
                                $new_list .= "-";
                            $new_list .=  $value;  
                        }
                        if(strlen($new_list))
                            $params = "&list-ean=" . $new_list;
                    }
                    if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                        die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/ibs/?ean=" . ffCommon_url_rewrite($ean) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                    } else {
                        ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/ibs/?ean=" . ffCommon_url_rewrite($ean) . $params);
                    }
                    
                }
            break;
        } 
    }
}

function filmModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        if($_REQUEST["keys"]["ID"] > 0 || (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "manually"))
        {
                switch ($action) 
                {
                        case "insert":
                        case "update":
                                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_film SET
                                                        " . CM_TABLE_PREFIX . "mod_collection_film.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["title"]->getValue())) . "
                                                        WHERE " . CM_TABLE_PREFIX . "mod_collection_film.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
                                $db->execute($sSQL);
                        break;
                }
        }
    }
}