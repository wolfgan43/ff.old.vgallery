<?php
$globals = ffGlobals::getInstance("mod_crowdfund");
$db = ffDB_Sql::factory();

use_cache(false);

$user_permission = get_session("user_permission");

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea.* 
			, " . CM_TABLE_PREFIX . "mod_crowdfund_banner.* 
			, (
				SELECT " . CM_TABLE_PREFIX . "showfiles_modes.name
				FROM " . CM_TABLE_PREFIX . "showfiles_modes
				WHERE " . CM_TABLE_PREFIX . "showfiles_modes.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_banner.ID_showfiles_modes
			) AS showfiles_modes_name
		FROM " . CM_TABLE_PREFIX . "mod_crowdfund_banner 
			INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea ON " . CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea.ID_banner = " . CM_TABLE_PREFIX . "mod_crowdfund_banner.ID
		WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_banner.name = " . $db->toSql($applet_params["type"]);
$db->query($sSQL);
if($db->nextRecord()) {
	$idea_ID = array();
	$rec_per_page = $db->getField("limit", "Number", true);
	$count_col = $db->getField("col", "Number", true);
	$showfiles_modes_name = $db->getField("showfiles_modes_name", "Text", true);
	
	//le idee che mostro sono quelle indicate nell'area restricted
	do
	{
		$ID_idea =  $db->getField("ID_idea", "Number", true);
		$idea_ID[$ID_idea] = $ID_idea;
	} while($db->nextRecord());
	
	$idea_decision = implode(',', array_keys($idea_ID)); 
	
	if(!strlen($showfiles_modes_name))
		$showfiles_modes_name = global_settings("MOD_CROWDFUND_APPLET_BANNER_ICO_POPULAR");
	
	if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
	{
        $filename = cm_cascadeFindTemplate("/applets/" . $applet_params["type"] . "_new.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/applets/" . $applet_params["type"] . "_new.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/applets/" . $applet_params["type"] . "_new.html", $cm->oPage->theme, false);*/

		if($filename === null) {
            $filename = cm_cascadeFindTemplate("/applets/banner_new.html", "crowdfund");
			/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/applets/banner_new.html", $cm->oPage->theme, false);
			if ($filename === null)
				$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/applets/banner_new.html", $cm->oPage->theme);*/
		}

		$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
															"rec_per_page" => $rec_per_page
															, "ico_mode" => $showfiles_modes_name
															, "col" => $count_col
															, "title" => array("banner", $applet_params["type"])
															, "idea" => array 
															(
																"ideas" => $idea_decision
															)
														)
												);
	} else
	{
        $filename = cm_cascadeFindTemplate("/applets/" . $applet_params["type"] . ".html", "crowdfund");
	    /*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/applets/" . $applet_params["type"] . ".html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/applets/" . $applet_params["type"] . ".html", $cm->oPage->theme, false);*/

		if($filename === null) {
            $filename = cm_cascadeFindTemplate("/applets/banner.html", "crowdfund");
			/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/applets/banner.html", $cm->oPage->theme, false);
			if ($filename === null)
				$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/applets/banner.html", $cm->oPage->theme);*/
		}

		$buffer = mod_crowdfund_process_idea(null, $filename, array(
															"rec_per_page" => $rec_per_page
															, "ico_mode" => $showfiles_modes_name
															, "col" => $count_col
															, "title" => array("banner", $applet_params["type"])
															, "idea" => array 
															(
																"ideas" => $idea_decision
															)
														)
												);
	}	
	

	$out_buffer = $buffer;
}
?>