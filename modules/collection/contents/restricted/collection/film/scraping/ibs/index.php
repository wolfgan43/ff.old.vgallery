<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

if(isset($_REQUEST["exec"]) && $_REQUEST["exec"])
{
	if(isset($_POST["school"]))
	{
		$z = 0;
		$db = ffDB_Sql::factory();
		$school = json_decode($_POST["school"], true);
		if(is_array($school) && count($school)) {
                    if(strlen($value["list_ean"])) {
                        $params = "&list-ean=" . $value["list_ean"];
                    }
                if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                        die(ffCommon_jsonenc(array("url" => $cm->router->named_rules["collection_film"]->reverse . "/scraping/imdb/?title=" . ffCommon_url_rewrite($school["title"]) . $params, "close" => true, "refresh" => true, "doredirects" => true), true));
                    } else {
                       ffRedirect($cm->router->named_rules["collection_film"]->reverse . "/scraping/imdb/?title=" . ffCommon_url_rewrite($school["title"]) . $params);
                    }
			
		}
	}
	if(isset($_POST["params"]))
	{
            echo "ciao";
		$params = json_decode($_POST["params"], true);
                $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
                $context = stream_context_create($opts);
		$tplList = file_get_contents($params["url"], FALSE, $context);
                $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_film"]->reverse);
                $params = "&list-title=" . $params["list_title"];
                
                die();
                if(strlen($tplList))
                    echo ffCommon_jsonenc(array("tpl" => $tplList), true);
	}
	
	
	exit;
}
$filename = CM_MODULES_ROOT . "/collection/contents" . $cm->router->named_rules["collection"]->reverse . "/film/scraping/ibs/template.html";

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("template.html", "main");
$tpl->set_var("ean", trim($_REQUEST["ean"]));

$buffer = $tpl->rpparse("main", false);

$cm->oPage->addContent($buffer);