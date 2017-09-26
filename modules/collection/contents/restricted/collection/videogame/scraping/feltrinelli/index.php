<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$cm = cm::getInstance();
$db = ffDB_Sql::factory();

if(isset($_REQUEST["exec"]) && $_REQUEST["exec"])
{
    if(isset($_POST["videogame"]))
    {
        $db = ffDB_Sql::factory();
        $videogame = json_decode($_POST["videogame"], true);

        if(is_array($videogame) && count($videogame)) 
        {
            foreach($videogame AS $key => $value)
            {
                $title = trim($value["title"]);
                $smart_url = trim($value["smart_url"]);
                $cover = trim($value["detail"][0]["cover"]);
                $ean = trim($value["ean"]);
                $description = trim($value["detail"][0]["description"]);

                if(strlen(trim($value["detail"][0]["publisher"])))
                {
                    $arrIDPublisher = array();
                    $publisher = trim($value["detail"][0]["publisher"]);
                    
                    if(strpos($publisher,", ")) {
                        $arrPublisher = explode(", ", $publisher);
                    } else {
                        $arrPublisher[] = $publisher;
                    }

                    foreach($arrPublisher AS $publisher_key => $publisher_value) 
                    {
                        $ID_publisher = check_subtable_values("videogame", "publisher", $publisher_value);

                        if(!array_key_exists($ID_publisher, $arrIDPublisher))
                            $arrIDPublisher[$ID_publisher] = $ID_publisher;
                    }

                    if(count($arrIDPublisher)) {
                        $stringPublisher = implode(",", $arrIDPublisher);
                    }
                }
                
                if(strlen($value["detail"][0]["genere"]))
                {
                    $ID_genre = check_subtable_values("videogame", "genre", $value["detail"][0]["genere"]);
                }
            }
                        
            if(strlen($cover)) {
                $saved_images = save_image($cover, ffCommon_url_rewrite($title), "/collector/videogame/");
            } else {
                $saved_images = "";
            }
                        
                        
            if(strlen($smart_url))
            {
                $sSQL = "SELECT cm_mod_collection_videogame.ID
                                        FROM cm_mod_collection_videogame
                                        WHERE cm_mod_collection_videogame.smart_url = " . $db->toSql($smart_url);
                $db->query($sSQL);
                if($db->nextRecord())
                {
                    $ID_videogames = $db->getField("ID", "Number", true);
                    $sSQL = "UPDATE cm_mod_collection_videogame SET
                                                    cover = " . $db->toSql($saved_images) . "
                                                    , genre = " . $db->toSql($ID_genre) . "
                                                    , publisher = " . $db->toSql($stringPublisher) . "
                                                    , ean = " . $db->toSql($ean) . "
                                                    , description = " . $db->toSql($description) . "
                                            WHERE cm_mod_collection_videogame.ID = " . $db->toSql($ID_videogames);
                    $db->execute($sSQL);
                }
            } else 
            {
                $sSQL = "INSERT INTO cm_mod_collection_videogame
                            (
                                    ID
                                    , title
                                    , smart_url
                                    , cover
                                    , genre
                                    , ID_publisher
                                    , ean
                                    , description
                            ) VALUES 
                            (
                                    null
                                    , " . $db->toSql($title) . "
                                    , " . $db->toSql(ffCommon_url_rewrite($title)) . "	
                                    , " . $db->toSql($saved_images) . "
                                    , " . $db->toSql($ID_genre) . "
                                    , " . $db->toSql($stringPublisher) . "
                                    , " . $db->toSql($ean) . "
                                    , " . $db->toSql($description) . "
                            )";
                $db->execute($sSQL);
                $ID_videogames = $db->getInsertID(true);
            }
			
            if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                    die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . $cm->router->named_rules["collection_videogame"]->reverse . '/modify?keys[ID]=' . $ID_videogames, "close" => true, "refresh" => true, "doredirects" => true), true));
            } else {
                    ffRedirect($component->parent[0]->site_path . $cm->router->named_rules["collection_videogame"]->reverse . '/modify?keys[ID]=' . $ID_videogames);
            }
        }
    } elseif(isset($_POST["obj"]))
    {
        $res = array();
        $obj = json_decode($_POST["obj"], true);
        $tplDetail = file_get_contents(key($obj));
        preg_match("/<body.*\/body>/s", $tplDetail, $tplDetailBody);
        if(strlen($tplDetailBody[0]))
            echo ffCommon_jsonenc(array("tpl" => $tplDetailBody[0]), true);
    } elseif(isset($_POST["params"]))
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
$filename = CM_MODULES_ROOT . "/collection/contents" . $cm->router->named_rules["collection"]->reverse . "/videogame/scraping/feltrinelli/template.html";

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("template.html", "main");
if(isset($_REQUEST["title"]))
{
	$tpl->set_var("videogame_title", str_replace("-", "+",$_REQUEST["title"]));
	$tpl->set_var("videogame_smart_url", ffCommon_specialchars($_REQUEST["title"]));
} elseif(isset($_REQUEST["ean"])) {
	$tpl->set_var("videogame_title", ffCommon_specialchars($_REQUEST["ean"]));
	$tpl->set_var("videogame_smart_url", "");
}
$tpl->set_var("ID_console", 192);

$buffer = $tpl->rpparse("main", false);

$cm->oPage->addContent($buffer);