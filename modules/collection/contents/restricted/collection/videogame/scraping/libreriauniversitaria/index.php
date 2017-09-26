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
        $videogame = json_decode($_POST["videogame"], true);

        if(is_array($videogame) && count($videogame)) 
        {
            $title = trim($videogame["title"]);
            if(1) 
            {
                if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                    die(ffCommon_jsonenc(array("url" => $cm->router->named_rules["collection_videogame"]->reverse . "/scraping/metacritic/" . $videogame["console_smart_url"] . "?title=" . ffCommon_url_rewrite($videogame["title"]), "close" => true, "refresh" => true, "doredirects" => true), true));
                } else {
                   ffRedirect($cm->router->named_rules["collection_videogame"]->reverse . "/scraping/metacritic/" . $videogame["console_smart_url"] . "?title=" . ffCommon_url_rewrite($videogame["title"]));
                }
            } else
            {
                $ID_console = trim($videogame["console_ID"]);
                $cover = $videogame["cover"];
                $ean = trim($videogame["ean"]);
                $description = trim($videogame["description"]);

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

                if(strlen($cover)) {
                    $saved_images = save_image($cover, ffCommon_url_rewrite($title), "/collector/videogame/");
                } else {
                    $saved_images = "";
                }

                $sSQL = "INSERT INTO cm_mod_collection_videogame
                            (
                                    ID
                                    , title
                                    , smart_url
                                    , cover
                                    , genre
                                    , publisher
                                    , description
                                    , piattaforma
                            ) VALUES 
                            (
                                    null
                                    , " . $db->toSql($title) . "
                                    , " . $db->toSql(ffCommon_url_rewrite($title)) . "	
                                    , " . $db->toSql($saved_images) . "
                                    , " . $db->toSql($ID_genre) . "
                                    , " . $db->toSql($stringPublisher) . "
                                    , " . $db->toSql($description) . "
                                    , " . $db->toSql($ID_console) . "
                            )";
                $db->execute($sSQL);
                $ID_videogames = $db->getInsertID(true);

                if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                        die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . $cm->router->named_rules["collection_videogame"]->reverse . '/modify?keys[ID]=' . $ID_videogames, "close" => true, "refresh" => true, "doredirects" => true), true));
                } else {
                        ffRedirect($component->parent[0]->site_path . $cm->router->named_rules["collection_videogame"]->reverse . '/modify?keys[ID]=' . $ID_videogames);
                }
            }
        }
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

$console_code = console_code();

$filename = CM_MODULES_ROOT . "/collection/contents" . $cm->router->named_rules["collection"]->reverse . "/videogame/scraping/libreriauniversitaria/template.html";

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("template.html", "main");

$tpl->set_var("videogame_ean", ffCommon_specialchars($_REQUEST["ean"]));
$tpl->set_var("videogame_smart_url", "");
$tpl->set_var("console_code", $console_code[basename($cm->real_path_info)]["universitaria_code"]);
$tpl->set_var("ID_console", $console_code[basename($cm->real_path_info)]["ID"]);
$tpl->set_var("console_smart_url", basename($cm->real_path_info));

$buffer = $tpl->rpparse("main", false);

$cm->oPage->addContent($buffer);