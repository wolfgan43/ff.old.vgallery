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

$attach = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) 
{
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));

    if(is_array($idea) && count($idea)) 
    {
        $tmp_idea = current($idea);
        $attach = mod_crowdfund_get_attach($tmp_idea["ID"]);
    }
}

switch($output) 
{
    case "html":
        $filename = cm_cascadeFindTemplate("/contents/services/attach.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/attach.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/attach.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("attach.html", "main");
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);

		if (is_array($attach) && count($attach))
		{
			foreach($attach AS $attach_key => $attach_value) 
			{
				$tpl->set_var("idea_attach_title", $attach_value["title"]);
				$tpl->set_var("idea_attach_file", FF_SITE_PATH . CM_SHOWFILES . $attach_value["file"]);
				$tpl->parse("SezAttachItem", true);
			}
			$tpl->parse("SezAttach", false);
		}
		echo $tpl->rpparse("main", false);    
		break;
    case "array":
	      print_r($attach);
	      break;
    case "json":		
    default:
	  echo ffCommon_jsonenc($attach, true);
}
exit;
?>