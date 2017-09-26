<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

use_cache(false);

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$filename = cm_cascadeFindTemplate("/contents/dashboard/index.html", "crowdfund");
/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/dashboard/index.html", $cm->oPage->theme, false);
if ($filename === null)
	$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/dashboard/index.html", $cm->oPage->theme);*/

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("index.html", "main");

$tpl->set_var("theme", $cm->oPage->theme);
$tpl->set_var("site_path", $cm->oPage->site_path);
$tpl->set_var("ret_url", urlencode($_REQUEST["ret_url"]));
$tpl_dashboard = $tpl->rpparse("main", false);

$cm->oPage->addContent($tpl_dashboard);

//set Header
if(check_function("set_header_page"))
	set_header_page(ffTemplate::_get_word_by_code("crowdfund_dashboard_title"));

if(global_settings("MOD_CROWDFUND_NEW_DESIGN")) 
{
    $filename = cm_cascadeFindTemplate("/contents/user/profile_new.html", "crowdfund");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/user/profile_new.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/user/profile_new.html", $cm->oPage->theme);*/
} else
{
    $filename = cm_cascadeFindTemplate("/contents/user/profile.html", "crowdfund");
	/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/user/profile.html", $cm->oPage->theme, false);
	if ($filename === null)
		$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/user/profile.html", $cm->oPage->theme);*/
}

if(!$permission[global_settings("MOD_CROWDFUND_GROUP_PUBLIC_USER")])
{
	if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
	{
		$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
														"idea" => array("owner" => get_session("UserNID"))
														, "title" => array("owner", "")
													)
											);
	} else 
	{
		$buffer = mod_crowdfund_process_idea(null, $filename, array(
														"idea" => array("owner" => get_session("UserNID"))
														, "title" => array("owner", "")
													)
											);
	}

if(strlen($buffer)) {
    $cm->oPage->addContent($buffer);
}
}

if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
{
	$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
                                                    "idea" => array("backer" => get_session("UserNID"))
                                                    , "title" => array("backer", "") 
                                                )
                                        );
} else
{
	$buffer = mod_crowdfund_process_idea(null, $filename, array(
                                                    "idea" => array("backer" => get_session("UserNID"))
                                                    , "title" => array("backer", "") 
                                                )
                                        );
}

if(strlen($buffer)) 
{
    $cm->oPage->addContent($buffer);
}


if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
{
	$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
                                                    "idea" => array("follower" => get_session("UserNID"))
                                                    , "title" => array("follower", "") 
                                                )
                                        );
} else 
{
	$buffer = mod_crowdfund_process_idea(null, $filename, array(
                                                    "idea" => array("follower" => get_session("UserNID"))
                                                    , "title" => array("follower", "") 
                                                )
                                        );
}

if(strlen($buffer)) 
{
    $cm->oPage->addContent($buffer);
}

if(!$permission[global_settings("MOD_CROWDFUND_GROUP_PUBLIC_USER")])
{

if(global_settings("MOD_CROWDFUND_IDEA_ENABLE_TEAM_DIVISION_PROFILE"))
{
	if(global_settings("MOD_CROWDFUND_NEW_DESIGN"))
	{
		$buffer = mod_crowdfund_process_idea_new(null, $filename, array(
															"idea" => array("team" => get_session("UserNID"))
															, "title" => array("team", "") 
														), true
												);
	} else
	{
		$buffer = mod_crowdfund_process_idea(null, $filename, array(
																"idea" => array("team" => get_session("UserNID"))
																, "title" => array("team", "") 
															), true
													);
	}
	
	
	if(strlen($buffer)) 
	{
		$cm->oPage->addContent($buffer);
	}
}
}
?>
