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
                $ID_console = trim($value["console_ID"]);
                $cover = trim($value["detail"][0]["cover"]);
                $rating = trim($value["rating"]);
                $year = trim($value["year"]);
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
                      
            $sSQL = "INSERT INTO cm_mod_collection_videogame
                        (
                                ID
                                , title
                                , smart_url
                                , cover
                                , genre
                                , publisher
                                , vote
                                , description
                                , year
                                , piattaforma
                        ) VALUES 
                        (
                                null
                                , " . $db->toSql($title) . "
                                , " . $db->toSql(ffCommon_url_rewrite($title)) . "	
                                , " . $db->toSql($saved_images) . "
                                , " . $db->toSql($ID_genre) . "
                                , " . $db->toSql($stringPublisher) . "
                                , " . $db->toSql($rating) . "
                                , " . $db->toSql($description) . "
                                , " . $db->toSql($year) . "
                                , " . $db->toSql($ID_console) . "
                        )";
            $db->execute($sSQL);
            $ID_videogames = $db->getInsertID(true);
	    $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_videogame"]->reverse);	
            if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                die(ffCommon_jsonenc(array("url" => $component->parent[0]->site_path . $cm->router->named_rules["collection_videogame"]->reverse . '/modify?keys[ID]=' . $ID_videogames . $ret_url, "close" => true, "refresh" => true, "doredirects" => true), true));
            } else {
                ffRedirect($component->parent[0]->site_path . $cm->router->named_rules["collection_videogame"]->reverse . '/modify?keys[ID]=' . $ID_videogames . $ret_url);
            }
        }
    } elseif(isset($_POST["obj"]))
    {
        $res = array();
        $obj = json_decode($_POST["obj"], true);
        $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $tplDetail = file_get_contents(key($obj), FALSE, $context);
        preg_match("/<body.*\/body>/s", $tplDetail, $tplDetailBody);
        if(strlen($tplDetailBody[0]))
            echo ffCommon_jsonenc(array("tpl" => $tplDetailBody[0]), true);
    } elseif(isset($_POST["params"]))
    {
        $tplListBody = array();
        $params = json_decode($_POST["params"], true);
        $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $tplList = file_get_contents($params["url"], FALSE, $context);
        preg_match("/<body.*\/body>/s", $tplList, $tplListBody);

        if(strlen($tplListBody[0]))
            echo ffCommon_jsonenc(array("tpl" => $tplListBody[0]), true);
    } elseif(isset($_POST["choose"]))
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
        foreach($choose AS $key => $value)
        {
            if($i < $max) {
                $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_videogame"]->reverse . "/scraping/metacritic/" . $value["console_smart_url"] . "?params=" . $value["params"]);
                $tpl->set_var("choose_elem_title", $value["title"]);
                $tpl->set_var("choose_elem_detail", $value["year"]);
                $tpl->parse("SezElemChoose", true);
            } else {
                break;
            }
            $i++;
        }
        
        $tpl->set_var("choose_title_modify_already_stored", ffTemplate::_get_word_by_code("mod_collection_title_source_db"));
        $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_videogame"]->reverse . "/modify/" . $value["console_smart_url"] . "?addnew=manually");
        $tpl->set_var("media_type", "videogame");
        $buffer = $tpl->rpparse("main", false);
        echo ffCommon_jsonenc(array("tpl" => $buffer), true);
    }
    exit;
}

$console_code = console_code();

$filename = CM_MODULES_ROOT . "/collection/contents" . $cm->router->named_rules["collection"]->reverse . "/videogame/scraping/metacritic/template.html";

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("template.html", "main");

$tpl->set_var("console_code", $console_code[basename($cm->real_path_info)]["metacritic_code"]);
$tpl->set_var("ID_console", $console_code[basename($cm->real_path_info)]["ID"]);
$tpl->set_var("console_smart_url", basename($cm->real_path_info));

if(isset($_REQUEST["title"]))
{
    $tpl->set_var("videogame_title", str_replace("-", "+",$_REQUEST["title"]));
    $tpl->set_var("videogame_smart_url", ffCommon_specialchars($_REQUEST["title"]));
    
} elseif(isset($_REQUEST["params"]))
{
    $tpl->set_var("params", ffCommon_specialchars($_REQUEST["params"]));
}

$buffer = $tpl->rpparse("main", false);

$cm->oPage->addContent($buffer);