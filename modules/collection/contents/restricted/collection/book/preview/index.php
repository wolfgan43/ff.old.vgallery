<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

if(isset($_REQUEST["keys"]["ID"]))
{
    $db = ffDB_Sql::factory();
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.*
                , " . CM_TABLE_PREFIX . "mod_collection_book_author.name AS author_name
                , " . CM_TABLE_PREFIX . "mod_collection_book_author.smart_url AS author_smart_url
                , " . CM_TABLE_PREFIX . "mod_collection_book_editore.name AS publisher_name
                , " . CM_TABLE_PREFIX . "mod_collection_book_editore.smart_url AS publisher_smart_url
                FROM " . CM_TABLE_PREFIX . "mod_collection_book
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_editore ON " . CM_TABLE_PREFIX . "mod_collection_book_editore.ID = " . CM_TABLE_PREFIX . "mod_collection_book.edizione 
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_author ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_book_author.ID,  " . CM_TABLE_PREFIX . "mod_collection_book.author)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $filename = cm_cascadeFindTemplate("/contents/book/index.html", "collection");
        //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/book/index.html", $cm->oPage->theme);
        $tpl = ffTemplate::factory(ffCommon_dirname($filename));
        $tpl->load_file("index.html", "main");
        
        $tpl->set_var("img_path", $db->getField("cover", "Text", true));
        $tpl->set_var("img_title", $db->getField("title", "Text", true));
        $tpl->set_var("book_title", $db->getField("title", "Text", true));
        $tpl->set_var("book_edizione", $db->getField("publisher_name", "Text", true));
        $tpl->set_var("book_edizione_link", $cm->router->named_rules["collection_book"]->reverse . "?editore=" . $db->getField("publisher_smart_url", "Text", true));
        $year = $db->getField("year", "Text", true);
        if(strlen($year)) {
            $tpl->set_var("book_year", $year);
            $tpl->parse("SezBookYear", false);
        } else {
            $tpl->set_var("SezBookYear", "");
        }
        
        $book_page = $db->getField("page", "Text", true);
        if(strlen($book_page)) {
            $tpl->set_var("book_page", $book_page);
            $tpl->parse("SezBookPage", false);
        } else {
            $tpl->set_var("SezBookPage", "");
        }
        do {
            $tpl->set_var("book_author",$db->getField("author_name", "Text", true));
            $tpl->set_var("book_author_link", $cm->router->named_rules["collection_book"]->reverse . "?author=" . $db->getField("author_smart_url", "Text", true));
            $tpl->parse("SezBookAuthor", true);
        } while ($db->nextRecord());
        
        $description = $db->getField("description", "Text", true);
        if(strlen($year)) {
            $tpl->set_var("book_description", $description);
            $tpl->parse("SezBookDescription", false);
        } else {
            $tpl->set_var("SezBookDescription", "");
        }
        
        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_shelf.name AS shelf_name
                        , " . CM_TABLE_PREFIX . "mod_collection_book_shelf.smart_url AS shelf_smart_url
                        , " . CM_TABLE_PREFIX . "mod_collection_book_location.name AS location_name
                        , " . CM_TABLE_PREFIX . "mod_collection_book_location.smart_url AS location_smart_url
                        , " . CM_TABLE_PREFIX . "mod_collection_book_library.name AS library_name
                        , " . CM_TABLE_PREFIX . "mod_collection_book_library.smart_url AS library_smart_url
                    FROM " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields
                        LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_shelf ON " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID = " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_shelf
                        LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_location ON " . CM_TABLE_PREFIX . "mod_collection_book_location.ID = " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_location
                        LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_library ON " . CM_TABLE_PREFIX . "mod_collection_book_library.ID = " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_library 
                    WHERE " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_book = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
        $db->query($sSQL);
        if($db->nextRecord())
        {
            do {
                $tpl->set_var("book_shelf", $db->getField("shelf_name", "Text", true));
                $tpl->set_var("book_shelf_link", $cm->router->named_rules["collection_book"]->reverse . "?shelf=" . $db->getField("shelf_smart_url", "Text", true) . "&library=" . $db->getField("library_smart_url", "Text", true) . "&location=" . $db->getField("location_smart_url", "Text", true));
                $tpl->set_var("book_library", $db->getField("library_name", "Text", true));
                $tpl->set_var("book_library_link", $cm->router->named_rules["collection_book"]->reverse . "?library=" . $db->getField("library_smart_url", "Text", true) . "&location=" . $db->getField("location_smart_url", "Text", true));
                $tpl->set_var("book_location", $db->getField("location_name", "Text", true));
                $tpl->set_var("book_location_link", $cm->router->named_rules["collection_book"]->reverse . "?location=" . $db->getField("location_smart_url", "Text", true));
                $tpl->parse("SezBookPlaceItem", true);
            } while($db->nextRecord());
            $tpl->parse("SezBookPlace", false);
        } else {
            $tpl->set_var("SezBookPlace", "");
        }
    }

    $cm->oPage->tplAddCSS("collection"
        , array(
            "file" => "collection.css"
            , "path" => "/modules/collection/themes/restricted/css"
    ));
    $cm->oPage->tplAddCSS("book"
        , array(
            "file" => "book.css"
            , "path" => "/modules/collection/themes/restricted/css"
    ));

    $cm->oPage->addContent($tpl->rpparse("main", false));
}