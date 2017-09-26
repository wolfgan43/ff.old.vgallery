<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();

if(strlen(basename($cm->real_path_info)))
{
    $smart_url_console = basename($cm->real_path_info);
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma
                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.smart_url = " . $db->toSql($smart_url_console);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $console = $smart_url_console;
        $ID_console = $db->getField("ID", "Number", true);
    }
}

if($_REQUEST["keys"]["ID"] > 0 && isset($_REQUEST["setfavourite"]))
{
   
    if($_REQUEST["setfavourite"]) {
        $favourite_decision = true;
    } else {
        $favourite_decision = false;
    }

    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_videogame 
                SET " . CM_TABLE_PREFIX . "mod_collection_videogame.favourite = " . $db->toSql($favourite_decision, "Number") . "
                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
    
    if($cm->isXHR()) {
        if($_REQUEST["XHR_DIALOG_ID"]) {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("videogameModify")), true));
        } else {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("videogameModify")), true));
        }
    } else {
        ffRedirect($_REQUEST["ret_url"]);
    }
}

if(isset($_REQUEST["choice-title"]) && strlen($_REQUEST["choice-title"]))
{
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
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame.*
                    , GROUP_CONCAT( " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.name) AS piattaforma_name
                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.ID,  " . CM_TABLE_PREFIX . "mod_collection_videogame.piattaforma)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.ID IN (" . $db->toSql($stringID, "Text", false) . ")
                GROUP BY ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_videogame"]->reverse . "/modify?keys[ID]=" . $db->getField("ID", "number", true));
            $tpl->set_var("choose_elem_title", $db->getField("title", "Text", true));
            $piattaforma_string = $db->getField("piattaforma_name", "Text", true);
            if(strlen($piattaforma_string))
            {
                $tpl->set_var("choose_elem_detail", $piattaforma_string);
                $tpl->parse("SezElemDetail", false);
            }
            $tpl->parse("SezElemChoose", true);
        } while($db->nextRecord()); 
    }
    $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_videogame"]->reverse . "/scraping/metacritic/" . $smart_url_console . "?title=" . $_REQUEST["title"]);
    $tpl->set_var("media_type", "videogame");
    $cm->oPage->addContent($tpl->rpparse("main", false));
} elseif(isset($_REQUEST["choice-ean"]) && strlen($_REQUEST["choice-ean"]))
{
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
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame.*
                    , GROUP_CONCAT( " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.name) AS piattaforma_name
                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.ID,  " . CM_TABLE_PREFIX . "mod_collection_videogame.piattaforma)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.ID IN (" . $db->toSql($stringID, "Text", false) . ")
                GROUP BY ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_videogame"]->reverse . "/modify?keys[ID]=" . $db->getField("ID", "number", true));
            $tpl->set_var("choose_elem_title", $db->getField("title", "Text", true));
            $piattaforma_string = $db->getField("piattaforma_name", "Text", true);
            if(strlen($piattaforma_string))
            {
                $tpl->set_var("choose_elem_detail", $piattaforma_string);
                $tpl->parse("SezElemDetail", false);
            }
            $tpl->parse("SezElemChoose", true);
        } while($db->nextRecord()); 
    }
    
    $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_videogame"]->reverse . "/scraping/libreriauniversitaria/" . $smart_url_console . "?ean=" . $_REQUEST["ean"]);
    $tpl->set_var("media_type", "videogame");
    $cm->oPage->addContent($tpl->rpparse("main", false));
} else 
{
$action_decided = "";
if(isset($_REQUEST["addnew"]))
    $action_decided = $_REQUEST["addnew"];



$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "videogameModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_collection_videogame";
$oRecord->addEvent("on_do_action", "videogameModify_on_do_action");
$oRecord->addEvent("on_done_action", "videogameModify_on_done_action");
$oRecord->user_vars["action_decided"] = $action_decided;
if(strlen($console))
{
    $oRecord->user_vars["console"] = $console;
    $oRecord->user_vars["ID_console"] = $ID_console;
}

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
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_cover");
        $oField->base_type = "Text";
        $oField->extended_type = "File";
        $oField->file_storing_path = DISK_UPDIR . "/collector/videogame/";
        $oField->file_temp_path = DISK_UPDIR . "/collector/videogame/";
        $oField->file_full_path = true;
        $oField->file_check_exist = true;
        $oField->file_normalize = true;
        $oField->file_show_preview = true;
        $oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
        $oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/70x70/[_FILENAME_]";
        $oField->control_type = "file";
        $oField->file_show_delete = true;
        $oField->widget = "uploadify"; 
        if(check_function("set_field_uploader")) { 
                $oField = set_field_uploader($oField);
        }
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "title";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_title");
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "year";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_year");
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "vote";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_metacritic_vote");
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "personal_vote";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_personal_vote");
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "publisher";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_publisher");
        $oField->base_type = "Number";
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.ID
                                        ,  " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.name
                                    FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher
                                    ORDER BY name";
        $oField->widget = "activecomboex";
        $oField->actex_update_from_db = true;
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "genre";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_genre");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name"; 
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_genre.ID
                                    ,  " . CM_TABLE_PREFIX . "mod_collection_videogame_genre.name
                                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_genre
                                [AND] [WHERE]
                                [HAVING]
                                [ORDER] [COLON] name
                                [LIMIT]"; 
        $oRecord->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "piattaforma";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_console");
        $oField->extended_type = "Selection";
        $oField->widget = "autocompletetoken";
        $oField->autocompletetoken_minLength = 0;
        $oField->autocompletetoken_theme = "";
        $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
        $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
        $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
        $oField->autocompletetoken_combo = true;
        $oField->autocompletetoken_compare_having = "name"; 
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.ID
	    							,  " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.name
	                            FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma
	                            [AND] [WHERE]
	                            [HAVING]
	                            [ORDER] [COLON] name
	                            [LIMIT]"; 
        $oField->default_value = new ffData($ID_console, "number");
        $oRecord->addContent($oField);
		
        $oField = ffField::factory($cm->oPage);
        $oField->id = "description";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_description");
        $oField->control_type = "textarea";
        $oRecord->addContent($oField);
    } elseif($action_decided == "title")
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "title";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_title");
        $oRecord->addContent($oField);
    } elseif($action_decided == "ean")
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ean";
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_ean");
        $oRecord->addContent($oField);
    }
} else 
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "insert_new";
    $oField->store_in_db = false;
    $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_addnew_how");
    $oField->extended_type = "Selection";
    $oField->multi_pairs = array (
	                        array(new ffData("manually"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_manually")))
	                        , array(new ffData("title"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_by_title")))
	                        , array(new ffData("ean"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_by_EAN")))
	                   );
    $oField->multi_select_one = false;
    $oRecord->addContent($oField);
    
    if(!strlen($console))
    {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "console";
        $oField->store_in_db = false;
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_modify_console");
        $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.smart_url
                                        ,  " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.name
                                    FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma
                                    ORDER BY name";
        $oField->widget = "activecomboex";
        $oField->actex_update_from_db = true;
        $oField->required = true;
        $oRecord->addContent($oField);
    }
}
$cm->oPage->addContent($oRecord);
}

function videogameModify_on_do_action($component, $action) 
{
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        if($component->user_vars["console"]) {
            $console_smart_url = $component->user_vars["console"];
        } elseif(isset($component->form_fields["console"])) {
            $console_smart_url = $component->form_fields["console"]->getValue();
        }
        
        switch ($action) 
        {
            case "insert":
                if (isset($component->form_fields["insert_new"]) && strlen($component->form_fields["insert_new"]->getValue())) 
                {
                    if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                        die(ffCommon_jsonenc(array("url" => $component->site_path . $component->page_path . "/modify/" . $console_smart_url . "?addnew=" . $component->form_fields["insert_new"]->getValue() . "?ret_url=" . urlencode($component->parent[0]->site_path . $component->parent[0]->page_path), "close" => true, "refresh" => true, "doredirects" => true), true));
                    } else {
                        ffRedirect($component->site_path . $component->page_path . "/modify/" . $console_smart_url . "?addnew=" . $component->form_fields["insert_new"]->getValue() . "?ret_url=" . urlencode($component->parent[0]->site_path . $component->parent[0]->page_path));
                    }
                }
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "manually") 
                {
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame.*
                                                            ,  " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.smart_url AS piattaforma_smart_url
                                                    FROM " . CM_TABLE_PREFIX . "mod_collection_videogame
                                                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.ID,  " . CM_TABLE_PREFIX . "mod_collection_videogame.piattaforma)
                                                    WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["title"]->getValue()));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        $ID_videogame = $db->getField("ID", "Number", true);
                        $string_piattaforma = $db->getField("piattaforma", "Text", true);
                        do {
                            $list_piattaforme[$db->getField("piattaforma_smart_url", "Text", true)] = 0;
                        } while ($db->nextRecord());

                        if(!array_key_exists($console_smart_url, $list_piattaforme))
                        {
                            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_videogame SET
                                        " . CM_TABLE_PREFIX . "mod_collection_videogame.piattaforma = " . $db->toSql($string_piattaforma . "," . $component->user_vars["ID_console"]) . "
                                        WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.ID = " . $db->toSql($ID_videogame, "Number");
                            $db->execute($sSQL);
                        }
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/modify/" . $console_smart_url . "/?keys[ID]=" . $ID_videogame, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/modify/" . $console_smart_url . "/?keys[ID]=" . $ID_videogame);
                        }
                    }
                } 
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "title") 
                {
                    $title = $component->form_fields["title"]->getValue();
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.smart_url = " . $db->toSql(ffcommon_url_rewrite($title));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringVideogame))
                                $stringVideogame .= "-";
                            $stringVideogame .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-title=" . $stringFilm . "&title=" . ffCommon_url_rewrite($title), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-title=" . $stringVideogame . "&title=" . ffCommon_url_rewrite($title));
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/metacritic/" . $console_smart_url . "?title=" . ffCommon_url_rewrite($component->form_fields["title"]->getValue()), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/metacritic/" . $console_smart_url . "?title=" . ffCommon_url_rewrite($component->form_fields["title"]->getValue()));
                        }
                    }
                } 
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "ean") 
                {
                    $ean = $component->form_fields["ean"]->getValue();
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.ean = " . $db->toSql(ffcommon_url_rewrite($ean));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringVideogame))
                                $stringVideogame .= "-";
                            $stringVideogame .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-ean=" . $stringFilm . "&ean=" . ffCommon_url_rewrite($ean), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-ean=" . $stringVideogame . "&ean=" . ffCommon_url_rewrite($ean));
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/libreriauniversitaria/" . $console_smart_url . "?ean=" . ffCommon_url_rewrite($component->form_fields["ean"]->getValue()), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "scraping/libreriauniversitaria/" . $console_smart_url . "?ean=" . ffCommon_url_rewrite($component->form_fields["ean"]->getValue()));
                        }
                    }
                }
            break;
        } 
    }
}

function videogameModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        if($_REQUEST["keys"]["ID"] > 0 || (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "manually"))
        {
            switch ($action) 
            {
                case "insert":
                case "update":
                    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_videogame SET
                                " . CM_TABLE_PREFIX . "mod_collection_videogame.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["title"]->getValue())) . "
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
                    $db->execute($sSQL);
                    break;
            }
        }
    }
}