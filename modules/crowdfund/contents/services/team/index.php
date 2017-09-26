<?php
$db = ffDB_Sql::factory();

if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{
	ffRedirect(str_replace("/parsedata", "", $_SERVER["REQUEST_URI"]), 301); // TO FIX!!!
}
		
$output = (isset($_REQUEST["out"]) && strlen($_REQUEST["out"])
		    ? $_REQUEST["out"]
		    : "html" 
	  );

$team = array();
$smart_url = basename($cm->real_path_info);

if(strlen($smart_url)) 
{ 
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));
	if(is_array($idea) && count($idea)) 
    {
		$tmp_idea = current($idea);
		$team = mod_crowdfund_get_team($tmp_idea["ID"], null, true);
	}
}

switch($output) 
{
	case "html":
        $filename = cm_cascadeFindTemplate("/contents/services/team.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/team.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/team.html", $cm->oPage->theme);*/
		
		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("team.html", "main");
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);

		if (is_array($team) && count($team))
		{
			foreach($team AS $team_key => $team_value) 
			{
				if(array_key_exists("linkedin", $team_value) && (strlen($team_value["linkedin"]) && (strpos($team_value["linkedin"], "linkedin.com/in/") || strpos($team_value["linkedin"], "linkedin.com/pub/"))))
				{
					$tpl->set_var("team_linkedin_account", $team_value["linkedin"]);
					$tpl->parse("SezTeamMemberLinkedin", false);
				} else 
				{
					if ($team_value["public"])
					{
						$tpl->set_var("member_team_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $team_value["slug"]);
						$tpl->parse("SezTeamMemberProfileLinkTop", false);
						$tpl->parse("SezTeamMemberProfileLinkBottom", false);
						$tpl->parse("SezTeamMemberAvatarLinkTop", false);
						$tpl->parse("SezTeamMemberAvatarLinkBottom", false);
						$tpl->parse("SezGoToProfileButton", false);
					}
					else
					{
						$tpl->set_var("SezTeamMemberProfileLinkTop", "");
						$tpl->set_var("SezTeamMemberProfileLinkBottom", "");
						$tpl->set_var("SezTeamMemberAvatarLinkTop", "");
						$tpl->set_var("SezTeamMemberAvatarLinkBottom", "");
						$tpl->set_var("SezGoToProfileButton", "");
					}
					if(check_function("get_user_avatar")) 
					{
						$tpl->set_var("team_avatar", get_user_avatar($team_value["member_avatar"], false, $team_value["member_email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_OWNER_ICO")));
						$tpl->parse("SezTeamMemberAvatar", false);
					}
					$tpl->set_var("team_reference", $team_value["member_reference"]);
					$tpl->set_var("team_role", $team_value["role"]);

					$tpl->parse("SezTeamMember", true);
				}
			}
		}  
		echo $tpl->rpparse("main", false);    
		break;
    case "array":
		print_r($team);
		break;
	case "json":		
	default:
		echo ffCommon_jsonenc($team, true);
}
exit;
?>