<?php
$cm = cm::getInstance();

if(isset($_REQUEST["exec"]) && $_REQUEST["exec"])
{
	if(isset($_POST["school"]))
	{
		$z = 0;
		$db = ffDB_Sql::factory();
		$school = json_decode($_POST["school"], true);
		if(is_array($school) && count($school)) {
			print_r($school);
			
		}
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
$tpl->set_var("film_title", ffCommon_specialchars($_REQUEST["title"]));

$buffer = $tpl->rpparse("main", false);

$cm->oPage->addContent($buffer);