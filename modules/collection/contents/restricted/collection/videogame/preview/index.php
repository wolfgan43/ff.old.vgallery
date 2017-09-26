<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

if(isset($_REQUEST["keys"]["ID"]))
{
    $db = ffDB_Sql::factory();
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame.*
                , " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.name AS publisher_name
                , " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.smart_url AS publisher_smart_url
                , " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.name AS piattaforma_name
                , " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.smart_url AS piattaforma_smart_url
                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher ON " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.ID = " . CM_TABLE_PREFIX . "mod_collection_videogame.publisher 
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.ID,  " . CM_TABLE_PREFIX . "mod_collection_videogame.piattaforma)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame.ID = " . $_REQUEST["keys"]["ID"];
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $filename = cm_cascadeFindTemplate("/contents/videogame/index.html", "collection");
        //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/videogame/index.html", $cm->oPage->theme);
        $tpl = ffTemplate::factory(ffCommon_dirname($filename));
        $tpl->load_file("index.html", "main");	
        
        $tpl->set_var("img_path", FF_SITE_PATH . CM_SHOWFILES . "/170x170" . $db->getField("cover", "Text", true));
        $tpl->set_var("img_title", $db->getField("title", "Text", true));
        
        $tpl->set_var("videogame_title", $db->getField("title", "Text", true));
        
        $tpl->set_var("videogame_publisher", $db->getField("publisher_name", "Text", true));
        $tpl->set_var("videogame_publisher_link", $cm->router->named_rules["collection_videogame"]->reverse . "?publisher=" . $db->getField("publisher_smart_url", "Text", true));
        
        $year = $db->getField("year", "Text", true);
        if(strlen($year)) {
            $tpl->set_var("videogame_year", $year);
            $tpl->parse("SezVideogameYear", false);
        } else {
            $tpl->set_var("SezVideogameYear", "");
        }
        
        $videogame_metacritic_vote = $db->getField("vote", "Number", true);
        if($videogame_metacritic_vote) {
            $tpl->set_var("videogame_metacritic_vote", $videogame_metacritic_vote);
            $tpl->parse("SezVideogameMetacriticVote", false);
        } else {
            $tpl->set_var("SezVideogameMetacriticVote", "");
        }
        
        $videogame_personal_vote = $db->getField("personal_vote", "Number", true);
        if($videogame_personal_vote) {
            $tpl->set_var("videogame_personal_vote", $videogame_personal_vote);
            $tpl->parse("SezVideogamePersonalVote", false);
        } else {
            $tpl->set_var("SezVideogamePersonalVote", "");
        }
        
        $description = $db->getField("description", "Text", true);
        if(strlen($year)) {
            $tpl->set_var("videogame_description", $description);
            $tpl->parse("SezVideogameDescription", false);
        } else {
            $tpl->set_var("SezVideogameDescription", "");
        }
        
        $genre = $db->getField("genre", "Text", true);
        
        do {
            $tpl->set_var("videogame_piattaforma",$db->getField("piattaforma_name", "Text", true));
            $tpl->set_var("videogame_piattaforma_link", $cm->router->named_rules["collection_videogame"]->reverse . "?piattaforma=" . $db->getField("piattaforma_smart_url", "Text", true));
            $tpl->parse("SezVideogamePiattaforma", true);
        } while ($db->nextRecord());
        
        if(strlen($genre))
        {
            $sSQL = "SELECT cm_mod_collection_videogame_genre.*
                        FROM cm_mod_collection_videogame_genre
                        WHERE cm_mod_collection_videogame_genre.ID IN (" . $db->toSql($genre, "Text", false) . ")";
            $db->query($sSQL);
            if($db->nextRecord())
            {
                do {
                    $tpl->set_var("videogame_genre",$db->getField("name", "Text", true));
                    $tpl->set_var("videogame_genre_link", $cm->router->named_rules["collection_videogame"]->reverse . "?genre=" . $db->getField("ID", "Number", true));
                    $tpl->parse("SezVideogameGenreItem", true); 
                } while ($db->nextRecord());
            }
            $tpl->parse("SezVideogameGenre", false); 
        } else {
            $tpl->set_var("SezVideogameGenre", ""); 
        }
        
        
    }

    $cm->oPage->tplAddCSS("collection"
        , array(
            "file" => "collection.css"
            , "path" => "/modules/collection/themes/restricted/css"
    ));
    $cm->oPage->tplAddCSS("videogame"
        , array(
            "file" => "videogame.css"
            , "path" => "/modules/collection/themes/restricted/css"
    ));

    $cm->oPage->addContent($tpl->rpparse("main", false));
}