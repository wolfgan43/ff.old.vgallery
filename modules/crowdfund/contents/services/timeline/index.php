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

$timeline = array();
$smart_url = basename($cm->real_path_info);

if(strlen($smart_url)) 
{ 
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));
	if(is_array($idea) && count($idea)) 
    {
		
		$month = array 
					(
						ffTemplate::_get_word_by_code("january")
						, ffTemplate::_get_word_by_code("february")
						, ffTemplate::_get_word_by_code("march")
						, ffTemplate::_get_word_by_code("april")
						, ffTemplate::_get_word_by_code("may")
						, ffTemplate::_get_word_by_code("june")
						, ffTemplate::_get_word_by_code("july")
						, ffTemplate::_get_word_by_code("august")
						, ffTemplate::_get_word_by_code("september")
						, ffTemplate::_get_word_by_code("october")
						, ffTemplate::_get_word_by_code("november")
						, ffTemplate::_get_word_by_code("december")
					);
		
		$tmp_idea = current($idea);
		
		$symbol = $tmp_idea["symbol"];
		$activated = $tmp_idea["activated"];
		$create = $tmp_idea["created"];
		
	
		if($tmp_idea["expired"] > 0)
		{ 
			$expire = $activated + $tmp_idea["expired"] * 86400;
		} else
		{
			$expire = $activated + (global_settings("MOD_CROWDFUND_EXPIRATION") - global_settings("MOD_CROWDFUND_EXPIRATION_NECESSARY_DELAY")) * 86400;
		}  
		$timeline = mod_crowdfund_create_timeline($tmp_idea["ID"], $tmp_idea["activated"],$expire);
		
		
	}
}

switch($output) 
{
	case "html":
        $filename = cm_cascadeFindTemplate("/contents/services/timeline.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/timeline.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/timeline.html", $cm->oPage->theme);*/
		
		
		
		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("timeline.html", "main");
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);

		if (is_array($timeline) && count($timeline))
		{
			$count = 1;
			//gestisce stile
			$switch_style = "positive";
				
			foreach($timeline AS $timeline_key => $timeline_value) 
			{
				if((is_array($timeline_value) && count($timeline_value)))
				{
					list($yy, $mm) = explode("-", $timeline_key);
					$number_month = (int) $mm - 1; 

					$tpl->set_var("crowdfund_timeline_month", $month[$number_month]);
					$tpl->set_var("crowdfund_timeline_year", $yy); 

					$tpl->set_var("SezTimelineMonthEvent", ""); 
					foreach($timeline_value AS $timeline_subkey => $timeline_subvalue)
					{
						$tpl->set_var("SezTimelineEndFunding", "");
						if ($timeline_subkey > $expire && $count)
						{
							$count = 0;
							$tpl->parse("SezTimelineEndFunding", false);
						}

						$tpl->set_var("timeline_date", date("d/m/Y", $timeline_subkey));
						$tpl->set_var("type", $timeline_subvalue["type"]);

						if ($timeline_subvalue["type"] == "follow")
						{
							$tpl->set_var("timeline_follow_name", $timeline_subvalue["name"]);
							if(check_function("get_user_avatar")) 
							{
								$tpl->set_var("timeline_follower_avatar", get_user_avatar($timeline_subvalue["avatar"], false, $timeline_subvalue["email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_TEAM_ICO")));
							}
							$tpl->set_var("SezTimelineMonthEventBacker", "");
							$tpl->set_var("SezTimelineMonthEventAttach", "");
							$tpl->set_var("SezTimelineMonthEventPrevision", "");
							if($timeline_subvalue["public"])
							{
								$tpl->set_var("timeline_follow_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $timeline_subvalue["slug"]);
								$tpl->parse("SezTimelineFollowHeader", false);
								$tpl->parse("SezTimelineFollowFooter", false);
							} else
							{
								$tpl->set_var("SezTimelineFollowHeader", "");
								$tpl->set_var("SezTimelineFollowFooter", "");
							}
							$tpl->parse("SezTimelineMonthEventFollow", false);
						}
						elseif ($timeline_subvalue["type"] == "backer")
						{
							$tpl->set_var("timeline_backer_name", $timeline_subvalue["name"]);
							$tpl->set_var("timeline_backer_price", $timeline_subvalue["price"] . $symbol);
							if(check_function("get_user_avatar")) 
							{
								$tpl->set_var("timeline_backer_avatar", get_user_avatar($timeline_subvalue["avatar"], false, $timeline_subvalue["email"], FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_TEAM_ICO")));
							}
							$tpl->set_var("SezTimelineMonthEventFollow", "");
							$tpl->set_var("SezTimelineMonthEventAttach", "");
							$tpl->set_var("SezTimelineMonthEventPrevision", "");
							if($timeline_subvalue["public"])
							{
								$tpl->set_var("timeline_backer_smart_url", FF_SITE_PATH . USER_RESTRICTED_PATH . "/" . $timeline_subvalue["slug"]);
								$tpl->parse("SezTimelineBackerHeader", false);
								$tpl->parse("SezTimelineBackerFooter", false);
							} else
							{
								$tpl->set_var("SezTimelineBackerHeader", "");
								$tpl->set_var("SezTimelineBackerFooter", "");
							}
							$tpl->parse("SezTimelineMonthEventBacker", false);
						} elseif ($timeline_subvalue["type"] == "attach")
						{
							$tpl->set_var("idea_timeline_file", FF_SITE_PATH . CM_SHOWFILES . $timeline_subvalue["file"]);
							$tpl->set_var("timeline_attach_title", $timeline_subvalue["title"]);
							$tpl->set_var("timeline_attach_file", FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_TIMELINE_ICO") . $timeline_subvalue["file"]);
							$tpl->set_var("SezTimelineMonthEventFollow", "");
							$tpl->set_var("SezTimelineMonthEventBacker", "");
							$tpl->set_var("SezTimelineMonthEventPrevision", "");
							$tpl->parse("SezTimelineMonthEventAttach", false);
						} elseif ($timeline_subvalue["type"] == "timeline")
						{ 
							$tpl->set_var("timeline_prevision_title", $timeline_subvalue["title"]);
							if(strlen ($timeline_subvalue["file"]))
							{
								$tpl->set_var("timeline_prevision_file", FF_SITE_PATH . CM_SHOWFILES . "/" . global_settings("MOD_CROWDFUND_TIMELINE_ICO") . $timeline_subvalue["file"]);
								$tpl->parse("SezTimelineMonthEventPrevisionAttach", false);
							} else 
							{
								$tpl->set_var("SezTimelineMonthEventPrevisionAttach", "");
							}
							if(strlen ($timeline_subvalue["description"]))
							{
								$tpl->set_var("timeline_prevision_description", $timeline_subvalue["description"]);
								$tpl->parse("SezTimelineMonthEventPrevisionDescription", false);
							} else 
							{
								$tpl->set_var("SezTimelineMonthEventPrevisionDescription", "");
							}
							$tpl->set_var("SezTimelineMonthEventFollow", "");
							$tpl->set_var("SezTimelineMonthEventBacker", "");
							$tpl->set_var("SezTimelineMonthEventAttach", "");
							$tpl->parse("SezTimelineMonthEventPrevision", false); 
						}
						if ($switch_style == "positive") 
						{
							$switch_style = "negative"; 
						} else 
						{
							$switch_style = "positive";  
						}

						$tpl->set_var("is_positive", $switch_style);
						$tpl->parse("SezTimelineMonthEvent", true); 
					}
					$tpl->parse("SezTimelineMonth", true); 
				}
			}
			$tpl->parse("SezTimeline", false);
		}  
		echo $tpl->rpparse("main", false);    
		break;
    case "array":
		print_r($timeline);
		break;
	case "json":		
	default:
		echo ffCommon_jsonenc($timeline, true);
}
exit;
?>