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

$UserNID = get_session("UserNID");
$estimatebudget = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) 
{
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));
    if(is_array($idea) && count($idea)) 
    {
		$tmp_idea = current($idea);
        $estimatebudget = array( 
			      "income_statement" => mod_crowdfund_get_income_statement($tmp_idea["ID"], true)
			      ,"cash_flow" => mod_crowdfund_get_cash_flow($tmp_idea["ID"], false , true)
			  );
			  
    }
}

switch($output) 
{
    case "html":
        $filename = cm_cascadeFindTemplate("/contents/services/estimatebudget.html", "crowdfund");
		/*$filename = cm_moduleCascadeFindTemplate(FF_THEME_DISK_PATH, "/modules/crowdfund/contents/services/estimatebudget.html", $cm->oPage->theme, false);
		if ($filename === null)
			$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/crowdfund/themes", "/contents/services/estimatebudget.html", $cm->oPage->theme);*/

		$tpl = ffTemplate::factory(ffCommon_dirname($filename));
		$tpl->load_file("estimatebudget.html", "main");
		$tpl->set_var("theme", $cm->oPage->theme);
		$tpl->set_var("site_path", $cm->oPage->site_path);
	
		if (is_array($estimatebudget["income_statement"]) && count($estimatebudget["income_statement"]))
		{
			if ((is_array($estimatebudget["cash_flow"]) && count($estimatebudget["cash_flow"]))) //in attesa di altre condizioni
			{
				$tpl-> set_var("idea_summary_chart", mod_crowdfund_process_chart($smart_url, "chart-estimatebudget")); 
				$tpl->parse("SezSummaryChart", false);
			} else
			{
				$tpl->set_var("SezSummaryChart", "");
			}
			$first = true;
			$switch_style = "positive";
			$is_bold = "";
			foreach($estimatebudget["income_statement"] AS $income_statement_key => $income_statement_value) 
			{
				$tpl->set_var("SezItemField", "");
				if(is_array($income_statement_value) && count($income_statement_value)) 
				{
					$tpl->set_var("income_statement_years", "");
					$tpl->parse("SezIncomeStatementItemHeader", true);

				    
				    if(strpos($income_statement_key, "bold_") !== false) {
						$income_statement_key_label = str_replace("bold_", "", $income_statement_key);
						$is_bold = true;
					} else {
						$income_statement_key_label = $income_statement_key;
						$is_bold = false;
					}
				    $tpl->set_var("field_class", ffCommon_url_rewrite($income_statement_key_label));
				    $tpl->set_var("field_value", ffTemplate::_get_word_by_code("crowdfund_income_statement_detail_" . $income_statement_key_label));
					$tpl->parse("SezItemField", true);
					foreach($income_statement_value AS $income_year => $income_value) 
					{
						if($first) 
						{
						    $tpl->set_var("income_statement_years", $income_year);
						    $tpl->parse("SezIncomeStatementItemHeader", true);
						}
						if ($is_bold)
						{
							$tpl->set_var("is_bold", " bold");
							
						} else 
						{
							$tpl->set_var("is_bold", "");
						}
						$tpl->set_var("field_class", "right");
						$tpl->set_var("field_value", $income_value);
						$tpl->parse("SezItemField", true);
					}
					$first = false;
				}
				if ($switch_style == "negative")
				{
					$switch_style = "positive";
				} else 
				{
					$switch_style = "negative";
				}
				
				$tpl->set_var("switch_style", $switch_style);
				$tpl->parse("SezIncomeStatementItem", true);
			}
			$tpl->parse("SezIncomeStatement", false);
		}
		

		if (is_array($estimatebudget["cash_flow"]) && count($estimatebudget["cash_flow"]))
		{
			$first = true;
			$switch_style = "positive";
			foreach($estimatebudget["cash_flow"] AS $cash_flow_key => $cash_flow_value) 
			{
				$tpl->set_var("SezCashFlowItemField", "");
				if(is_array($cash_flow_value) && count($cash_flow_value)) 
				{
					$tpl->set_var("cashflow_years", "");
					$tpl->parse("SezCashFlowItemHeader", true);

				    if(strpos($cash_flow_key, "bold_") !== false) {
						$cash_flow_key_label = str_replace("bold_", "", $cash_flow_key);
						$is_bold = true;
					} else {
						$cash_flow_key_label = $cash_flow_key;
						$is_bold = false;
					}
					$tpl->set_var("cash_flow_field_class", ffCommon_url_rewrite($cash_flow_key_label)); 
					$key = str_replace("cashflow_", "", $cash_flow_key_label);
				    $tpl->set_var("cash_flow_field_value", ffTemplate::_get_word_by_code("crowdfund_cash_flow_detail_" . $key));
					$tpl->parse("SezCashFlowItemField", true);
					foreach($cash_flow_value AS $cash_flow_year => $cashflow_value) 
					{
						
						if($first) 
						{
						    $tpl->set_var("cashflow_years", $cash_flow_year);
						    $tpl->parse("SezCashFlowItemHeader", true);
						}
						
						if ($is_bold)
						{
							$tpl->set_var("is_bold", " bold");
							
						} else 
						{
							$tpl->set_var("is_bold", "");
						}
						
						$tpl->set_var("cash_flow_field_class", "right");
						$tpl->set_var("cash_flow_field_value", $cashflow_value);
						$tpl->parse("SezCashFlowItemField", true);
					}
					$first = false;
				}
				if ($switch_style == "negative")
				{
					$switch_style = "positive";
				} else 
				{
					$switch_style = "negative";
				}
				$tpl->set_var("switch_style", $switch_style);
				$tpl->parse("SezCashFlowItem", true);
			}
			$tpl->parse("SezCashFlow", false);
			
		}
		if(	(is_array($estimatebudget["cash_flow"]) && count($estimatebudget["cash_flow"]))
			|| (is_array($estimatebudget["income_statement"]) && count($estimatebudget["income_statement"]))
		) {
			$tpl->parse("SezEstimateBudget", false);
		}
		echo $tpl->rpparse("main", false);
		break;
    case "array":
	      print_r($estimatebudget);
	      break;
    case "json":		
    default:
	  echo ffCommon_jsonenc($estimatebudget, true);
}
exit;
?>
