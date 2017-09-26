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

$businessplan = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) {

    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));

    if(is_array($idea) && count($idea)) 
    {
        $tmp_idea = current($idea);
        $businessplan = mod_crowdfund_get_businessplan($tmp_idea["ID"]);
	}
}

switch($output) 
{
    case "html":
        $filename = cm_cascadeFindTemplate("/contents/services/businessplan.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/businessplan.html", $cm->oPage->theme, false);
		if ($filename === null) 
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/businessplan.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("businessplan.html", "main");
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);
		
		$count = 0;
		if ((bool) strip_tags($businessplan["commercial_strategy"]))
		{
			$tpl->set_var("idea_businessplan_commercial_strategy", $businessplan["commercial_strategy"]);
			$tpl->parse("SezCommercialStrategy", false);
			$count = 1;
		}
		if ((bool) strip_tags($businessplan["description_product"]))	
		{
			$tpl->set_var("idea_businessplan_description_product", $businessplan["description_product"]);
			$tpl->parse("SezDescriptionProduct", false);
			$count = 1;
		}		
		if ((bool) strip_tags($businessplan["investment_description"]))
		{
			$tpl->set_var("idea_businessplan_investment_description", $businessplan["investment_description"]);
			$tpl->parse("SezInvestmentDescription", false);
			$count = 1;
		}
		if ((bool) strip_tags($businessplan["sell_goal"]))
		{
			$tpl->set_var("idea_businessplan_sell_goal", $businessplan["sell_goal"]);
			$tpl->parse("SezSellGoal", false);
			$count = 1;
		}
		if ((bool) strip_tags($businessplan["target_market"]))
		{
			$tpl->set_var("idea_businessplan_target_market", $businessplan["target_market"]);
			$tpl->parse("SezTargetMarket", false);
			$count = 1;
		}
		if	((bool) strip_tags($businessplan["weakness_strength"]))
		{
			$tpl->set_var("idea_businessplan_weakness_strength", $businessplan["weakness_strength"]);
			$tpl->parse("SezWeaknessStrenght", false);
			$count = 1;
		}
			
		if(count)
		{
			$tpl->parse("SezBusinessPlan", false);
		} else
		{
			$tpl->set_var("SezBusinessPlan", "");
		}
		
		
		echo $tpl->rpparse("main", false);    
	    break;
    case "array":
	    print_r($businessplan);
	    break;
    case "json":		
    default:
	    echo ffCommon_jsonenc($businessplan, true);
}
exit;
?>
