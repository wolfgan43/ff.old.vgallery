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
$qa = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) 
{
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));

    if(is_array($idea) && count($idea)) 
    {
        $tmp_idea = current($idea);
        $qa = mod_crowdfund_get_qa($tmp_idea["ID"]);
    }
}

switch($output) 
{
    case "html":
        $filename = cm_cascadeFindTemplate("/contents/services/qa.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/qa.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/qa.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("qa.html", "main");
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);
		
		if(0) {
			$cm->oPage->widgetLoad("dialog");
			$cm->oPage->widgets["dialog"]->process(
				 "CFqa"
				 , array(
					"tpl_id" => null
					//"name" => "myTitle"
					, "url" => ""
					, "title" => ""
					, "callback" => ""
					, "class" => ""
					, "params" => array(
					)
					, "resizable" => true
					, "position" => "center"
					, "draggable" => true
				    , "doredirects" => true
				)
				, $cm->oPage
			);			
			
			if(mod_security_check_session(false)) {
				$tpl->set_var("qa_addnew_url", "javascript:void(0);");
			} else {
				$tpl->set_var("qa_addnew_url", FF_SITE_PATH . "/login?ret_url=" . $cm->oPage->getRequestUri());
			}
			$tpl->parse("SezQAddNew", false);
		}
		
		if (is_array($qa) && count($qa))
		{
			$switch_style = "positive";
			foreach($qa AS $qa_key => $qa_value) 
			{
				if((bool) strip_tags($qa_value["answer"]))
				{
					if(check_function("get_user_avatar")) 
					{
						$tpl->set_var("idea_qa_avatar", get_user_avatar($qa_value["avatar"], false, $qa_value["email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_OWNER_ICO")));
						$tpl->parse("SezQAImage", false);
					}
					if (strlen($qa_value["name"]) && strlen($qa_value["surname"]))
					{
						$tpl->set_var("qa_ID_name", $qa_value["name"]. " " . $qa_value["surname"]);
					} else 
					{
						$tpl->set_var("qa_ID_name", $qa_value["username"]);
					}
					$tpl->set_var("idea_qa_ID", $qa_key);
					$tpl->set_var("idea_qa_question", $qa_value["question"]);
					$tpl->set_var("idea_qa_answer", $qa_value["answer"]);
					
					if ($switch_style == "negative")
					{
						$switch_style = "positive";
					} else 
					{
						$switch_style = "negative";
					}
					$tpl->set_var("switch_style", $switch_style);
					$tpl->parse("SezQAItem", true);
				}
				
			}
			$tpl->parse("SezQA", false);
		}
		echo $tpl->rpparse("main", false);    
	      break;
    case "array":
	      print_r($qa);
	      break;
    case "json":		
    default:
	    echo ffCommon_jsonenc($qa, true);
}
exit;
?>