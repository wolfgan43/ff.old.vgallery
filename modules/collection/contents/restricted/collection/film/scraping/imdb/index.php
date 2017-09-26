<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$cm = cm::getInstance();
$db = ffDB_Sql::factory();

if(isset($_REQUEST["exec"]) && $_REQUEST["exec"])
{
    if(isset($_POST["film"]))
    {
        $db = ffDB_Sql::factory();
        $film = json_decode($_POST["film"], true);

        if(is_array($film) && count($film)) 
        {
            foreach($film AS $key => $value)
            {
                $title = trim($value["title"]);
                $url = trim($value["url"]);
                $list_ean = $value["list_ean"];
                $list_title = $value["list_title"];
                
                $cover = substr(trim($value["detail"][0]["avatar"]),0, strpos(trim($value["detail"][0]["avatar"]), "._V1_"));
                if(strlen($value["detail"][0]["original-title"]))
                    $original_title = substr($value["detail"][0]["original-title"], 0 , strpos($value["detail"][0]["original-title"], '"'));
                if(strlen(trim($value["detail"][0]["director"])))
                {
                    $director = trim($value["detail"][0]["director"]);
                    $sSQL = "SELECT cm_mod_collection_film_director.*
                                FROM cm_mod_collection_film_director
                                WHERE cm_mod_collection_film_director.name = " . $db->toSql($director);
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $ID_director = $db->getField("ID", "Number", true);
                    } else {
                        $sSQL = "INSERT INTO cm_mod_collection_film_director
                                    (	
                                        ID
                                        , name
                                        , smart_url
                                    ) VALUES
                                    (
                                        null
                                        , " . $db->toSql($director) . "
                                        , " . $db->toSql(ffCommon_url_rewrite($director)) . "
                                    )";
                        $db->execute($sSQL);
                        $ID_director = $db->getInsertID(true);
                    }
                }
                
                if(is_array($value["detail"][0]["cast"]) && count($value["detail"][0]["cast"]))
                {
                    $arrIDActor = array();
                    foreach($value["detail"][0]["cast"] AS $actor_key => $actor_value) 
                    {
                        $sSQL = "SELECT cm_mod_collection_film_actor.*
                                    FROM cm_mod_collection_film_actor
                                    WHERE cm_mod_collection_film_actor.smart_url = " . $db->toSql(ffCommon_url_rewrite($actor_value["name"]));
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $ID_actor = $db->getField("ID", "Number", true);
                        } else 
                        {
                            $avatar = substr(trim($actor_value["image"]),0, strpos(trim($actor_value["image"]), "._V1_"));
                            if(strlen($avatar))
                            {
                                $saved_actor_images = save_image($avatar, ffCommon_url_rewrite($actor_value["name"]), "/collector/film/actor/");
                            } else {
                                $saved_actor_images = "";
                            }
                            
                            $sSQL = "INSERT INTO cm_mod_collection_film_actor
                                        (	
                                            ID
                                            , name
                                            , smart_url
                                            , avatar
                                        ) VALUES
                                        (
                                            null
                                            , " . $db->toSql($actor_value["name"]) . "
                                            , " . $db->toSql(ffCommon_url_rewrite($actor_value["name"])) . "
                                            , " . $db->toSql($saved_actor_images) . "
                                        )";
                            $db->execute($sSQL);
                            $ID_actor = $db->getInsertID(true);
                        }
                        if(!array_key_exists($ID_actor, $arrIDActor))
                            $arrIDActor[$ID_actor] = $ID_actor;
                    }
                }
                if(strlen($value["detail"][0]["genre"]))
                {
                    if(strpos($value["detail"][0]["genre"],","))
                        $arrGenre = explode(",", $value["detail"][0]["genre"]);
                    else
                        $arrGenre[] = $value["detail"][0]["genre"];
                    foreach($arrGenre AS $genre_key => $genre_value) 
                    {
                        $sSQL = "SELECT cm_mod_collection_film_genre.*
                                    FROM cm_mod_collection_film_genre
                                    WHERE cm_mod_collection_film_genre.name = " . $db->toSql($genre_value);
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $ID_genre = $db->getField("ID", "Number", true);
                        } else 
                        {
                            $sSQL = "INSERT INTO cm_mod_collection_film_genre
                                        (	
                                            ID
                                            , name
                                            , smart_url
                                        ) VALUES
                                        (
                                            null
                                            , " . $db->toSql($genre_value) . "
                                            , " . $db->toSql(ffCommon_url_rewrite($genre_value)) . "
                                        )";
                            $db->execute($sSQL);
                            $ID_genre = $db->getInsertID(true);
                        }
                        $arrIDGenre[] = $ID_genre;
                    }
                }
                $duration = trim($value["detail"][0]["length"]);
                $year = trim($value["detail"][0]["published"]);
                $rating = trim($value["detail"][0]["rating"]) * 10;
                $description = trim($value["detail"][0]["description"]);
            }
            if(is_array($arrIDActor) && count($arrIDActor))
                $stringActor = implode(",", $arrIDActor);
            if(is_array($arrIDGenre) && count($arrIDGenre))
                $stringGenre = implode(",", $arrIDGenre);
            
            if(strlen($cover))
                $saved_images = save_image($cover, ffCommon_url_rewrite($title), "/collector/film/");
            else
                $saved_images = "";
                        
            $sSQL = "INSERT INTO cm_mod_collection_film
                        (	
                                ID
                                , title
                                , smart_url
                                , original_title
                                , cover
                                , duration
                                , year
                                , vote
                                , description
                                , link
                                , genre
                                , director
                                , actor
                        ) VALUES
                        (
                                null
                                , " . $db->toSql($title) . "
                                , " . $db->toSql(ffCommon_url_rewrite($title)) . "
                                , " . $db->toSql($original_title) . "
                                , " . $db->toSql($saved_images) . "
                                , " . $db->toSql($duration) . "
                                , " . $db->toSql($year) . "
                                , " . $db->toSql($rating) . "
                                , " . $db->toSql($description) . "
                                , " . $db->toSql($url) . "
                                , " . $db->toSql($stringGenre) . "
                                , " . $db->toSql($ID_director) . "
                                , " . $db->toSql($stringActor) . "
                        )";
            $db->execute($sSQL);
            $ID_film = $db->getInsertID(true);
            $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse);
            if(strlen($list_title))
            {
                $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse . '/modify/list-title?list_title=' . $list_title);
            } elseif(strlen($list_ean))
            {
                $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse . '/modify/list-ean?list_ean=' . $list_ean);
            }
            if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                    die(ffCommon_jsonenc(array("url" => $cm->router->named_rules["collection_film"]->reverse . "/modify?keys[ID]=" . $ID_film . $ret_url, "close" => true, "refresh" => true, "doredirects" => true), true));
            } else {

               ffRedirect($cm->router->named_rules["collection_film"]->reverse . "/modify?keys[ID]=" . $ID_film . $ret_url);  
            }
        }
    }
    if(isset($_POST["choose"]))
    {
        $choose = json_decode($_POST["choose"], true);
        $filename = cm_cascadeFindTemplate("/contents/template-decision.html", "collection");
        //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/template-decision.html", $cm->oPage->theme);

        $tpl = ffTemplate::factory(ffCommon_dirname($filename));
        $tpl->load_file("template-decision.html", "main");
        
        $max = 8;
        if(count($choose) < 8)
            $max = count($choose);
        $i = 0;
        
        $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse);
            
        foreach($choose AS $key => $value)
        {
            if(strlen($value["list_title"])) {
                $params = "&list-title=" . $value["list_title"];
                $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse . '/modify/list-title?list_title=' . $value["list_title"]);
            } elseif(strlen($value["list_ean"])) {
                $params = "&list-ean=" . $value["list_ean"];
                $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse . '/modify/list-ean?list_ean=' . $value["list_ean"]);
            }
            if($i < $max) {
                $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_film"]->reverse . "/scraping/imdb/?params=" . urlencode($value["params"]) . $params . $ret_url);
                $tpl->set_var("choose_elem_title", $value["title"]);
                $tpl->parse("SezElemChoose", true);
            } else {
                break;
            }
            $i++;
        }

        $tpl->set_var("choose_title_modify_already_stored", ffTemplate::_get_word_by_code("mod_collection_title_source_db"));
        $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_film"]->reverse . "/modify/manually?" . $params . $ret_url);
        $tpl->set_var("media_type", "film");
        $buffer = $tpl->rpparse("main", false);
        echo ffCommon_jsonenc(array("tpl" => $buffer), true);
    }
    if(isset($_POST["obj"]))
    {
        $res = array();
        $obj = json_decode($_POST["obj"], true);
        $tplDetail = file_get_contents(key($obj));
        preg_match("/<body.*\/body>/s", $tplDetail, $tplDetailBody);
        if(strlen($tplDetailBody[0]))
            echo ffCommon_jsonenc(array("tpl" => $tplDetailBody[0]), true);
    }
    if(isset($_POST["params"]))
    {
        $tplListBody = array();
        $params = json_decode($_POST["params"], true);
        $tplList = file_get_contents($params["url"]);
        preg_match("/<body.*\/body>/s", $tplList, $tplListBody);
        if(strlen($tplListBody[0]))
            echo ffCommon_jsonenc(array("tpl" => $tplListBody[0]), true);
    }
    
    exit;
}

$filename = CM_MODULES_ROOT . "/collection/contents" . $cm->router->named_rules["collection"]->reverse . "/film/scraping/imdb/template.html";

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("template.html", "main");
$tpl->set_var("film_title", str_replace("-", "+",$_REQUEST["title"]));
if(isset($_REQUEST["list-title"]))
    $tpl->set_var("list_title", ffCommon_specialchars($_REQUEST["list-title"]));
elseif(isset($_REQUEST["list-ean"]))
    $tpl->set_var("list_ean", ffCommon_specialchars($_REQUEST["list-ean"]));
if(isset($_REQUEST["params"]))
    $tpl->set_var("params", ffCommon_specialchars($_REQUEST["params"]));
$buffer = $tpl->rpparse("main", false);

$cm->oPage->addContent($buffer);