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

    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_book 
                SET " . CM_TABLE_PREFIX . "mod_collection_book.favourite = " . $db->toSql($favourite_decision, "Number") . "
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
    
    if($cm->isXHR()) {
        if($_REQUEST["XHR_DIALOG_ID"]) {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("bookModify")), true));
        } else {
            die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("bookModify")), true));
        }
    } else {
        ffRedirect($_REQUEST["ret_url"]);
    }
}
if(isset($_REQUEST["choice-title"]) && strlen($_REQUEST["choice-title"]))
{
    if(isset($_REQUEST["list-title"]) && strlen($_REQUEST["list-title"])) {
        $params = "&list-title=" . $_REQUEST["list-title"];
        $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse . '/modify/list-title?list_title=' . $_REQUEST["list-title"]);
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
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.*
                    , GROUP_CONCAT( " . CM_TABLE_PREFIX . "mod_collection_book_author.name) AS author_name
                FROM " . CM_TABLE_PREFIX . "mod_collection_book
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_author ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_book_author.ID,  " . CM_TABLE_PREFIX . "mod_collection_book.author)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.ID IN (" . $db->toSql($stringID, "Text", false) . ")"
            . " GROUP BY ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_book"]->reverse . "/modify?keys[ID]=" . $db->getField("ID", "number", true) . $params . $ret_url);
            $tpl->set_var("choose_elem_title", $db->getField("title", "Text", true));
            $tpl->set_var("choose_elem_detail", $db->getField("author_name", "Text", true));
            $tpl->parse("SezElemDetail", false);
            $tpl->parse("SezElemChoose", true);
        } while($db->nextRecord()); 
    }
    
    $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_book"]->reverse . "/scraping/google/?title=" . $_REQUEST["title"] . $params . $ret_url);
    $tpl->set_var("media_type", "book");
    $cm->oPage->addContent($tpl->rpparse("main", false));
} elseif(isset($_REQUEST["choice-ean"]) && strlen($_REQUEST["choice-ean"]))
{
    $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse);
    if(isset($_REQUEST["list-ean"]) && strlen($_REQUEST["list-ean"])) {
        $params = "&list-ean=" . $_REQUEST["list-ean"];
        $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse . '/modify/list-ean?list_ean=' . $_REQUEST["list-ean"]);
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
    
    $tpl->set_var("choose_book_title_block", ffTemplate::_get_word_by_code("mod_collection_book_title_already_stored"));
    $tpl->set_var("choose_title_modify_already_stored", ffTemplate::_get_word_by_code("mod_collection_title_already_stored_modify"));
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.*
                    , GROUP_CONCAT( " . CM_TABLE_PREFIX . "mod_collection_book_author.name) AS author_name
                FROM " . CM_TABLE_PREFIX . "mod_collection_book
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_author ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_book_author.ID,  " . CM_TABLE_PREFIX . "mod_collection_book.author)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.ID IN (" . $db->toSql($stringID, "Text", false) . ")"
                . " GROUP BY ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_book"]->reverse . "/modify?keys[ID]=" . $db->getField("ID", "number", true) . $params . $ret_url);
            $tpl->set_var("choose_elem_title", $db->getField("title", "Text", true));
            $tpl->set_var("choose_elem_author", $db->getField("author_name", "Text", true));
            $tpl->parse("SezElemChoose", true);
        } while($db->nextRecord()); 
    }
    
    $tpl->set_var("choose_manually_text", ffTemplate::_get_word_by_code("mod_collection_book_add_new"));
    $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_book"]->reverse . "/scraping/google/?ean=" . $_REQUEST["ean"] . $params . $ret_url);
    $tpl->set_var("media_type", "book");
    $cm->oPage->addContent($tpl->rpparse("main", false));
} else 
{
    $action_decided = "";
    if(strlen($cm->real_path_info))
        $action_decided = basename($cm->real_path_info);

    $oRecord = ffRecord::factory($cm->oPage); 
    $oRecord->id = "bookModify";
    $oRecord->resources[] = $oRecord->id;
    $oRecord->title = ffTemplate::_get_word_by_code("mod_collection_book_modify_title");
    $oRecord->src_table = CM_TABLE_PREFIX . "mod_collection_book";
    $oRecord->addEvent("on_do_action", "bookModify_on_do_action");
    $oRecord->addEvent("on_done_action", "bookModify_on_done_action");
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
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_cover");
            $oField->base_type = "Text";
            $oField->extended_type = "File";
            $oField->file_storing_path = DISK_UPDIR . "/collector/book/";
            $oField->file_temp_path = DISK_UPDIR . "/collector/book/";
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
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_title");
            $oRecord->addContent($oField);

                    $oField = ffField::factory($cm->oPage);
            $oField->id = "author";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_author");
            $oField->extended_type = "Selection";
            $oField->widget = "autocompletetoken";
            $oField->autocompletetoken_minLength = 0;
            $oField->autocompletetoken_theme = "";
            $oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
            $oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
            $oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
            $oField->autocompletetoken_combo = true;
            $oField->autocompletetoken_compare_having = "name"; 
            $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_author.ID
                                                                    ,  " . CM_TABLE_PREFIX . "mod_collection_book_author.name
                                                            FROM " . CM_TABLE_PREFIX . "mod_collection_book_author
                                                            [AND] [WHERE]
                                                            [HAVING]
                                                            [ORDER] [COLON] name
                                                            [LIMIT]"; 
            $oRecord->addContent($oField);

            

            $oField = ffField::factory($cm->oPage);
            $oField->id = "year";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_year");
            $oRecord->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "edizione";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_edizione");
            $oField->extended_type = "Selection";
            $oField->widget = "autocomplete";
            $oField->autocomplete_minLength = 0;
            $oField->autocomplete_combo = true;
            $oField->autocomplete_compare_having = "name";
            $oField->autocomplete_readonly = true;
            $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_editore.ID
                                        ,  " . CM_TABLE_PREFIX . "mod_collection_book_editore.name
                                    FROM " . CM_TABLE_PREFIX . "mod_collection_book_editore
                                    [AND] [WHERE]
                                    [HAVING]
                                    [ORDER] [COLON] name
                                    [LIMIT]"; 
            $oField->actex_update_from_db = true; 
            $oRecord->addContent($oField);

            $oField = ffField::factory($cm->oPage);
            $oField->id = "isbn10";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_modify_isbn10");
            $oRecord->addContent($oField);

                    $oField = ffField::factory($cm->oPage);
            $oField->id = "isbn13";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_modify_isbn13");
            $oRecord->addContent($oField);

                    $oField = ffField::factory($cm->oPage);
            $oField->id = "page";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_modify_pagine");
                    $oRecord->addContent($oField);

                    $oField = ffField::factory($cm->oPage);
            $oField->id = "description";
                    $oField->control_type = "textarea";
                    $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_modify_description");
                    $oRecord->addContent($oField);
                    
            $oDetail = ffDetails::factory($cm->oPage);
            $oDetail->id = "booklocation";
            $oDetail->title = ffTemplate::_get_word_by_code("mod_collection_book_modify_location");
            $oDetail->src_table = "cm_mod_collection_book_rel_fields";
            $oDetail->order_default = "ID";
            $oDetail->fields_relationship = array("ID_book" => "ID");
            
            $oField = ffField::factory($cm->oPage);
            $oField->id = "ID";
            $oField->base_type = "Number";
            $oDetail->addKeyField($oField);
            
                    $oField = ffField::factory($cm->oPage);
            $oField->id = "ID_shelf";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_location");
            $oField->extended_type = "Selection";
            $oField->widget = "actex";
            $oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID
                                        , CONCAT(" . CM_TABLE_PREFIX . "mod_collection_book_library.name, ' - ', " . CM_TABLE_PREFIX . "mod_collection_book_shelf.name) AS decision_name
                                        , " . CM_TABLE_PREFIX . "mod_collection_book_location.name AS group_location 
                                    FROM " . CM_TABLE_PREFIX . "mod_collection_book_shelf
                                        LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_location ON  " . CM_TABLE_PREFIX . "mod_collection_book_location.ID = " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_location
                                        LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_library ON  " . CM_TABLE_PREFIX . "mod_collection_book_library.ID = " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_library
                                    [AND] [WHERE]
                                    [HAVING]
                                    [ORDER] [COLON] group_location, decision_name
                                    [LIMIT]"; 
            $oField->actex_update_from_db = true; 
            $oField->actex_group = "group_location";
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
                                                        ORDER BY description"; 
        $oDetail->addContent($oField);
        $oRecord->addContent($oDetail);
        $cm->oPage->addContent($oDetail);

        } elseif($action_decided == "title")
        {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "title";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_title");
            $oField->required = true;
            $oRecord->addContent($oField);
        } elseif($action_decided == "list-title")
        {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "list_title";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_list_title");
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
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_ean");
            $oField->required = true;
            $oRecord->addContent($oField);
        } elseif($action_decided == "list-ean")
        {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "list_ean";
            $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_list_ean");
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
        $oField->label = ffTemplate::_get_word_by_code("mod_collection_book_modify_addnew_how");
        $oField->store_in_db = false;
        $oField->extended_type = "Selection";
        $oField->multi_pairs = array (
                                    array(new ffData("manually"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_manually")))
                                    , array(new ffData("title"), new ffData(ffTemplate::_get_word_by_code("mod_collection_film_insert_by_title")))
                                    , array(new ffData("list-title"), new ffData(ffTemplate::_get_word_by_code("mod_collection_book_insert_by_list_title")))
                                    , array(new ffData("ean"), new ffData(ffTemplate::_get_word_by_code("mod_collection_book_insert_by_EAN")))
                                    , array(new ffData("list-ean"), new ffData(ffTemplate::_get_word_by_code("mod_collection_book_insert_by_list_EAN")))
                               );
        $oField->multi_select_one = false;
        $oRecord->addContent($oField);
    }
    $cm->oPage->addContent($oRecord);
}


function bookModify_on_do_action($component, $action) {
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
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_book
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.smart_url = " . $db->toSql(ffcommon_url_rewrite($title));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringBook))
                                $stringBook .= "-";
                            $stringBook .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-title=" . $stringBook . "&title=" . ffCommon_url_rewrite($title), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-title=" . $stringBook . "&title=" . ffCommon_url_rewrite($title));
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?title=" . ffCommon_url_rewrite($title), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?title=" . ffCommon_url_rewrite($title));
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
                    
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_book
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.smart_url = " . $db->toSql(ffcommon_url_rewrite($title));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringBook))
                                $stringBook .= "-";
                            $stringBook .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-title=" . $stringBook . "&title=" . ffCommon_url_rewrite($title) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-title=" . $stringBook . "&title=" . ffCommon_url_rewrite($title) . $params);
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?title=" . ffCommon_url_rewrite($title) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?title=" . ffCommon_url_rewrite($title) . $params);
                        }
                    }
                }
                else if (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "ean") 
                {
                    $ean = $component->form_fields["ean"]->getValue();
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_book
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.isbn10 = " . $db->toSql(ffcommon_url_rewrite($ean)) . "
                                    OR " . CM_TABLE_PREFIX . "mod_collection_book.isbn13 = " . $db->toSql(ffcommon_url_rewrite($ean));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringBook))
                                $stringBook .= "-";
                            $stringBook .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-ean=" . $stringBook . "&ean=" . ffCommon_url_rewrite($ean), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-ean=" . $stringBook . "&ean=" . ffCommon_url_rewrite($ean));
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?ean=" . ffCommon_url_rewrite($component->form_fields["ean"]->getValue()), "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?ean=" . ffCommon_url_rewrite($component->form_fields["ean"]->getValue()));
                        }
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
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.ID
                                FROM " . CM_TABLE_PREFIX . "mod_collection_book
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.isbn10 = " . $db->toSql(ffcommon_url_rewrite($ean)) . "
                                    OR " . CM_TABLE_PREFIX . "mod_collection_book.isbn13 = " . $db->toSql(ffcommon_url_rewrite($ean));
                    $db->query($sSQL);
                    if($db->nextRecord())
                    {
                        do {
                            if(strlen($stringBook))
                                $stringBook .= "-";
                            $stringBook .= $db->getField("ID", "Number", true);
                        } while ($db->nextRecord());
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . "?choice-ean=" . $stringBook . "&ean=" . ffCommon_url_rewrite($ean) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . "?choice-ean=" . $stringBook . "&ean=" . ffCommon_url_rewrite($ean) . $params);
                        }
                    } else 
                    {
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?ean=" . ffCommon_url_rewrite($ean) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($component->parent[0]->site_path . ffCommon_dirname($component->parent[0]->page_path) . "/scraping/google/?ean=" . ffCommon_url_rewrite($ean) . $params);
                        }
                    }
                }
            break;
        } 
    }
}

function bookModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        if($_REQUEST["keys"]["ID"] > 0 || (isset($component->user_vars["action_decided"]) && $component->user_vars["action_decided"] == "manually"))
        {
            switch ($action) 
            {
                case "insert":
                case "update":
                    if(isset($component->detail["booklocation"]->recordset) && count($component->detail["booklocation"]->recordset))
                    {
                        foreach($component->detail["booklocation"]->recordset AS $value) 
                        {
                            if(isset($value["ID_shelf"]) && $value["ID_shelf"]->getValue() > 0) 
                            {
                                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_shelf.*
                                            FROM " . CM_TABLE_PREFIX . "mod_collection_book_shelf
                                            WHERE " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID = " . $db->toSql($value["ID_shelf"]->getValue());
                                $db->query($sSQL);
                                if($db->nextRecord())
                                {
                                    $ID_library = $db->getField("ID_library", "Number", true);
                                    $ID_location = $db->getField("ID_location", "Number", true);
                                    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields SET
                                                " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_location = " . $db->toSql($ID_location, "Number") . "
                                                , " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_library = " . $db->toSql($ID_library, "Number") . "
                                                WHERE " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID = " . $db->toSql($value["ID"]->getValue(), "Number");
                                    $db->execute($sSQL);
                                }
                            }
                        }
                    }
                    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_book SET
                                " . CM_TABLE_PREFIX . "mod_collection_book.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["title"]->getValue())) . "
                                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
                    $db->execute($sSQL);
                break;
            }
            
        }
        
    }
} 