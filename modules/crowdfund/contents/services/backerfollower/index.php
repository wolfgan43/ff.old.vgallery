<?php
$cm = cm::getInstance();
$db = ffDB_Sql::factory();

if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{
	ffRedirect(str_replace("/parsedata", "", $_SERVER["REQUEST_URI"]), 301); // TO FIX!!!
}

$output = (isset($_REQUEST["out"]) && strlen($_REQUEST["out"])
		    ? $_REQUEST["out"]
		    : "json"
	  );
$UserNID = get_session("UserNID"); 

$backerfollower = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) 
{
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));
    if(is_array($idea) && count($idea)) 
    {
        $tmp_idea = current($idea);
		$currency = $tmp_idea["symbol"];
		$owner = $tmp_idea["owner"];
        $backerfollower = array( 
			      "backers" => mod_crowdfund_get_backers($tmp_idea["ID"], false)
			      , "followers" => mod_crowdfund_get_follower($tmp_idea["ID"])
			);
	}
}

switch($output) 
{
    case "html":
        $filename = cm_cascadeFindTemplate("/contents/services/backerfollower.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/backerfollower.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/backerfollower.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("backerfollower.html", "main");
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);
		
		if (is_array($backerfollower["backers"]) && count($backerfollower["backers"]))
		{
			$switch_style = "positive";
		    foreach($backerfollower["backers"] AS $backerfollower_key => $backerfollower_value) 
		    {
				
		        $tpl->set_var("idea_backer_ID", $backerfollower_key);
				if(check_function("get_user_avatar")) 
				{
					$tpl->set_var("idea_backer_avatar", get_user_avatar($backerfollower_value["backer_avatar"], false, $backerfollower_value["backer_email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_OWNER_ICO")));
					$tpl->parse("SezBackersImage", false);
				}
				
				if($backerfollower_value["backer_public"] || $UserNID == $owner) 
				{
					$tpl->set_var("idea_backer_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $backerfollower_value["backer_slug"]);
					$tpl->parse("SezBackerUrlHeader", false);
					$tpl->parse("SezBackerUrlFooter", false);
				} else {
					$tpl->set_var("SezBackerUrlHeader", "");
					$tpl->set_var("SezBackerUrlFooter", "");
				}
				$tpl->set_var("idea_backer_name", ucwords($backerfollower_value["backer_reference"]));

				$tpl->set_var("idea_backer_price", $backerfollower_value["backer_price"]);
				$tpl->set_var("idea_backer_currency", $currency . " " . ($backerfollower_value["confirmed"] ? ffTemplate::_get_word_by_code("crowdfund_backer_price_confirmed") : ffTemplate::_get_word_by_code("crowdfund_backer_price_not_confirmed")));
				if ($switch_style == "negative")
				{
					$switch_style = "positive";
				} else 
				{
					$switch_style = "negative";
				}
				$tpl->set_var("switch_style", $switch_style);
		        $tpl->parse("SezBackersItem", true);
		    }

		    $tpl->parse("SezBackers", false);
		} 
		
		if (is_array($backerfollower["followers"]) && count($backerfollower["followers"]))
		{
			$switch_style = "positive";
		    foreach($backerfollower["followers"] AS $backerfollower_key => $backerfollower_value) 
		    {
		        $tpl->set_var("idea_follower_ID", $backerfollower_key);
				if(check_function("get_user_avatar")) 
				{
					$tpl->set_var("idea_follower_avatar", get_user_avatar($backerfollower_value["follower_avatar"], false, $backerfollower_value["follower_email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_OWNER_ICO")));
					$tpl->parse("SezFollowersImage", false);
				}
				
				if($backerfollower_value["follower_public"] || $UserNID == $owner)  
				{
					$tpl->set_var("idea_follower_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $backerfollower_value["follower_slug"]);
					$tpl->parse("SezFollowerUrlHeader", false);
					$tpl->parse("SezFollowerUrlFooter", false);
				} else {
					$tpl->set_var("SezFollowerUrlHeader", "");
					$tpl->set_var("SezFollowerUrlFooter", "");
				}
				$tpl->set_var("idea_follower_name", ucwords($backerfollower_value["follower_reference"]));

				if ($switch_style == "negative")
				{
					$switch_style = "positive";
				} else 
				{
					$switch_style = "negative";
				}
				$tpl->set_var("switch_style", $switch_style);
				$tpl->parse("SezFollowersItem", true); 
		    }

		    $tpl->parse("SezFollowers", false);
		}
		if(	(is_array($backerfollower["backers"]) && count($backerfollower["backers"]))
			|| (is_array($backerfollower["followers"]) && count($backerfollower["followers"]))
		) {
			$tpl->parse("SezBackersFollowers", false);
		}
		echo $tpl->rpparse("main", false);    
		break;
    case "array":
	      print_r($backerfollower);
	      break;
    case "json":		
    default:
	  echo ffCommon_jsonenc($backerfollower, true);
}
exit;
?>
