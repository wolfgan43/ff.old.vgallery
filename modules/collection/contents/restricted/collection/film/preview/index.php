<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

if(isset($_REQUEST["keys"]["ID"]))
{
    $db = ffDB_Sql::factory();
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film.*
                , " . CM_TABLE_PREFIX . "mod_collection_film_director.name AS director_name
                , " . CM_TABLE_PREFIX . "mod_collection_film_director.smart_url AS director_smart_url
                , " . CM_TABLE_PREFIX . "mod_collection_film_actor.name AS actor_name
                , " . CM_TABLE_PREFIX . "mod_collection_film_actor.smart_url AS actor_smart_url
		FROM " . CM_TABLE_PREFIX . "mod_collection_film
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_film_director ON " . CM_TABLE_PREFIX . "mod_collection_film_director.ID = " . CM_TABLE_PREFIX . "mod_collection_film.director 
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_film_actor ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_film_actor.ID,  " . CM_TABLE_PREFIX . "mod_collection_film.actor)
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film.ID = " . $_REQUEST["keys"]["ID"];
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $filename = cm_cascadeFindTemplate("/contents/film/index.html", "collection");
        //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/film/index.html", $cm->oPage->theme);
        $tpl = ffTemplate::factory(ffCommon_dirname($filename));
        $tpl->load_file("index.html", "main");	
        
        $tpl->set_var("img_path", FF_SITE_PATH . CM_SHOWFILES . "/170x170" . $db->getField("cover", "Text", true));
        $tpl->set_var("img_title", $db->getField("title", "Text", true));
        
        $tpl->set_var("film_title", $db->getField("title", "Text", true));
        
        $film_original_title = $db->getField("original_title", "Text", true);
        if(strlen($film_original_title))  {
            $tpl->set_var("film_original_title", $film_original_title);
            $tpl->parse("SezFilmOriginalTitle", false);
        } else {
            $tpl->set_var("SezFilmOriginalTitle", "");
        }
        
        $tpl->set_var("film_director", $db->getField("director_name", "Text", true));
        $tpl->set_var("film_director_link", $cm->router->named_rules["collection_film"]->reverse . "?director=" . $db->getField("director_smart_url", "Text", true));
        
        $year = $db->getField("year", "Text", true);
        if(strlen($year)) {
            $tpl->set_var("film_year", $year);
            $tpl->parse("SezFilmYear", false);
        } else {
            $tpl->set_var("SezFilmYear", "");
        }
        
        $film_duration = $db->getField("duration", "Text", true);
        if(strlen($film_duration)) {
            $tpl->set_var("film_duration", $film_duration);
            $tpl->parse("SezFilmDuration", false);
        } else {
            $tpl->set_var("SezFilmDuration", "");
        }
        
        $film_imdb_vote = $db->getField("vote", "Number", true);
        if($film_imdb_vote) {
            $tpl->set_var("film_imdb_vote", $film_imdb_vote);
            $tpl->parse("SezFilmIMDbVote", false);
        } else {
            $tpl->set_var("SezFilmIMDbVote", "");
        }
        
        $film_personal_vote = $db->getField("personal_vote", "Number", true);
        if($film_personal_vote) {
            $tpl->set_var("film_personal_vote", $film_personal_vote);
            $tpl->parse("SezFilmPersonalVote", false);
        } else {
            $tpl->set_var("SezFilmPersonalVote", "");
        }
        
        $link = $db->getField("link", "Text", true);
        if(strlen($link)) {
            $tpl->set_var("film_link", $link);
            $tpl->parse("SezFilmLink", false);
        } else {
            $tpl->set_var("SezFilmLink", "");
        }
        
        $description = $db->getField("description", "Text", true);
        if(strlen($year)) {
            $tpl->set_var("film_description", $description);
            $tpl->parse("SezFilmDescription", false);
        } else {
            $tpl->set_var("SezFilmDescription", "");
        }
        
        $genre = $db->getField("genre", "Text", true);
        
        do {
            $tpl->set_var("film_actor",$db->getField("actor_name", "Text", true));
            $tpl->set_var("film_actor_link", $cm->router->named_rules["collection_film"]->reverse . "?actor=" . $db->getField("actor_smart_url", "Text", true));
            $tpl->parse("SezFilmActor", true);
        } while ($db->nextRecord());
        
        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_support.name AS support_name
                        , " . CM_TABLE_PREFIX . "mod_collection_film_support.smart_url AS support_smart_url
                        , " . CM_TABLE_PREFIX . "mod_collection_film_quality.name AS quality_name
                        , " . CM_TABLE_PREFIX . "mod_collection_film_quality.smart_url AS quality_smart_url
                        , GROUP_CONCAT(language.description) AS language
                        , " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.subtitle
                        , " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID AS ID_key
                    FROM " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields
                        LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_film_support ON " . CM_TABLE_PREFIX . "mod_collection_film_support.ID = " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID_support
                        LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_film_quality ON " . CM_TABLE_PREFIX . "mod_collection_film_quality.ID = " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID_quality
                        LEFT JOIN ff_languages AS language ON FIND_IN_SET(language.ID ," . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.language)
                    WHERE " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID_film = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                    GROUP BY " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID";
        $db->query($sSQL);
        if($db->nextRecord())
        {
            do {
                $ID = $db->getField("ID_key", "Nimber", true);
                $tpl->set_var("film_support_name", $db->getField("support_name", "Text", true));
                $tpl->set_var("film_support_link", $cm->router->named_rules["collection_film"]->reverse . "?support=" . $db->getField("support_smart_url", "Text", true));
                $tpl->set_var("film_quality", $db->getField("quality_name", "Text", true));
                $tpl->set_var("film_quality_link", $cm->router->named_rules["collection_film"]->reverse . "?quality=" . $db->getField("quality_smart_url", "Text", true));
                $tpl->set_var("film_language", $db->getField("language", "Text", true));
                $subtitle = $db->getField("subtitle", "Text", true);
                if(strlen($subtitle))
                {
                    $db2 = ffDB_Sql::factory();
                    $sSQL2 = "SELECT GROUP_CONCAT(ff_languages.description) AS subtitle_string
                                FROM ff_languages
                                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields ON FIND_IN_SET(ff_languages.ID ," . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.subtitle)
                                 WHERE " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID = " . $db2->toSql($ID, "Number");
                    $db2->query($sSQL2);
                    if($db2->nextRecord())
                    {
                        $tpl->set_var("film_subtitle", $db2->getField("subtitle_string", "Text", true));
                    } else {
                        $tpl->set_var("film_subtitle", "");
                    }
                                
                } else {
                    $tpl->set_var("film_subtitle", "");
                }
                $tpl->parse("SezFilmSupportItem", true);
            } while($db->nextRecord());
            $tpl->parse("SezFilmSupport", false);
        } else {
            $tpl->set_var("SezFilmSupport", "");
        }
		
      
        
        
        if(strlen($genre))
        {
            $sSQL = "SELECT cm_mod_collection_film_genre.*
                        FROM cm_mod_collection_film_genre
                        WHERE cm_mod_collection_film_genre.ID IN (" . $db->toSql($genre, "Text", false) . ")";
            $db->query($sSQL);
            if($db->nextRecord())
            {
                do {
                    $tpl->set_var("film_genre",$db->getField("name", "Text", true));
                    $tpl->set_var("film_genre_link", $cm->router->named_rules["collection_film"]->reverse . "?genre=" . $db->getField("ID", "Number", true));
                    $tpl->parse("SezFilmGenreItem", true); 
                } while ($db->nextRecord());
            }
            $tpl->parse("SezFilmGenre", false); 
        } else {
            $tpl->set_var("SezFilmGenre", ""); 
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