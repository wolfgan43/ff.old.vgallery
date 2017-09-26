<?php
$globals = ffGlobals::getInstance("mod_crowdfund");
$db = ffDB_Sql::factory();

use_cache(false);

$user_permission = get_session("user_permission");

if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
{
    $filename = cm_cascadeFindTemplate("/applets/lastidea_new.html", "crowdfund");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/applets/lastidea_new.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/applets/lastidea_new.html", $cm->oPage->theme);*/
	
	$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
														"rec_per_page" => global_settings("MOD_CROWDFUND_APPLET_LAST_IDEA_REC_PER_PAGE")
														, "ico_mode" => global_settings("MOD_CROWDFUND_APPLET_LASTIDEA_ICO")
														, "col" => 3
														, "title" => "lastidea"
														, "idea" => array 
														(
															"idea_decision_vision" => "yes"
														//	, "ideas" => "41,56,59,74,96,99"
														)  
													)
											); 
} else
{
    $filename = cm_cascadeFindTemplate("/applets/lastidea.html", "crowdfund");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/applets/lastidea.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/applets/lastidea.html", $cm->oPage->theme);*/
	
	$buffer = mod_crowdfund_process_idea(null, $filename, array(
														"rec_per_page" => global_settings("MOD_CROWDFUND_APPLET_LAST_IDEA_REC_PER_PAGE")
														, "ico_mode" => global_settings("MOD_CROWDFUND_APPLET_LASTIDEA_ICO")
														, "col" => 3
														, "title" => "lastidea"
														, "idea" => array 
														(
															"idea_decision_vision" => "yes"
														//	, "ideas" => "41,56,59,74,96,99"
														)  
													)
											); 
	
}

$out_buffer = $buffer;
?>