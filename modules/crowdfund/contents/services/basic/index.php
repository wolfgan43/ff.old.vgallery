<?php
$db = ffDB_Sql::factory();

if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{
	ffRedirect(str_replace("/parsedata", "", $_SERVER["REQUEST_URI"]), 301); // TO FIX!!!
}

$output = (isset($_REQUEST["out"]) && strlen($_REQUEST["out"])
			? $_REQUEST["out"]
			: "json"
	      );

$idea_basic = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) 
{
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));

    if(is_array($idea) && count($idea)) 
    {
        $tmp_idea = current($idea);
        $idea_basic = mod_crowdfund_get_idea_basic($tmp_idea["ID"]);
    }
}

switch($output) 
{
    case "html":
			$filename = cm_cascadeFindTemplate("/contents/services/basic.html", "crowdfund");
	      /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/basic.html", $cm->oPage->theme, false);
	      if ($filename === null)
		      $filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/basic.html", $cm->oPage->theme);*/

	      $tpl = ffTemplate::factory(ffCommon_dirname($filename));
	      $tpl->load_file("basic.html", "main");
	      $tpl->set_var("theme", $cm->oPage->theme);
	      $tpl->set_var("site_path", $cm->oPage->site_path);
	      
	      $tpl->set_var("idea_title", $idea_basic["title"]);
	      $tpl->set_var("idea_teaser", $idea_basic["teaser"]);
	      $tpl->set_var("idea_description", $idea_basic["description"]);
	      
	      $tpl->parse("SezIdeaBasic", false);

	      echo $tpl->rpparse("main", false);    
	      break;
    case "array":
	      print_r($idea_basic);
	      break;
    case "json":		
    default:
	    echo ffCommon_jsonenc($idea_basic, true);
}
exit;
?>
