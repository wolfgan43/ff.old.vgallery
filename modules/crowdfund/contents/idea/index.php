<?php
	$globals = ffGlobals::getInstance("mod_crowdfund");
	$db = ffDB_Sql::factory();
	$cm = cm::getInstance();
//print_r($_SERVER);
	use_cache(false);
	$UserNID = get_session("UserNID");

	$user_permission = get_session("user_permission");

	$smart_url = basename($cm->real_path_info);
	$ID_idea = 0;
	$is_detail = null;
	if($UserNID == 1)
	{
		//echo global_settings("MOD_CROWDFUND_NEW_DESIGN");
	}
	if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))//strpos($cm->path_info, "/facebook") === 0)//strpos($cm->real_path_info, "facebook"))
	{
		if(strlen($smart_url)) { 
			$tpl_name = "detail_new";
		} else {
			$tpl_name = "index_new";

		}

        $filename = cm_cascadeFindTemplate("/contents" . "/idea/" . $tpl_name . ".html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/idea/" . $tpl_name . ".html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents" . "/idea/" . $tpl_name . ".html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents" . "/idea/" . $tpl_name . ".html", $cm->oPage->theme);*/
		
		$buffer = mod_crowdfund_process_idea_new($smart_url, $filename, array("enable_nav" => true));
	} else
	{  
		if(strlen($smart_url)) {
		$tpl_name = "detail";

		} else {
		$tpl_name = "index";

	}
        $filename = cm_cascadeFindTemplate("/contents" . "/idea/" . $tpl_name . ".html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/contents" . $cm->path_info . "/idea/" . $tpl_name . ".html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents" . "/idea/" . $tpl_name . ".html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate($cm->module_path . "/themes", "/contents" . "/idea/" . $tpl_name . ".html", $cm->oPage->theme);*/
		$buffer = mod_crowdfund_process_idea($smart_url, $filename, array("enable_nav" => true));
	}
	if(strlen($buffer))
		$cm->oPage->addContent($buffer);
?>
