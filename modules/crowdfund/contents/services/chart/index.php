<?php
$db = ffDB_Sql::factory();

if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{
	ffRedirect(str_replace("/parsedata", "", $_SERVER["REQUEST_URI"]), 301); // TO FIX!!!
}

$output =	(isset($_REQUEST["out"]) && strlen($_REQUEST["out"])
				? $_REQUEST["out"]
				: "json"
			);

$data = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) 
{
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url), null, false, false);
    if(is_array($idea) && count($idea)) 
    {
		$min_net_income = 0;
		$price = 0;
		$i = -1;
		$tmp_idea = current($idea); 
        $chart = array
				( 
					"income_statement" => mod_crowdfund_get_income_statement($tmp_idea["ID"], false, false)
					, "cash_flow" => mod_crowdfund_get_cash_flow($tmp_idea["ID"], false, false, false)
				);
		if(count(array_keys($chart["income_statement"])) > count(array_keys($chart["cash_flow"]))) 
		{
			$arrYear = array_keys($chart["income_statement"]);
		} else 
		{
			$arrYear = array_keys($chart["cash_flow"]);
		}
		
		$data = array	(
							"value" => array()
							, "params" => array
											(
												"net_income" => true 
												, "total_revenue" => true
												, "goal" => true
												, "cost_united" => false
												, "sum_column" => true
												, "area" => true
											)
						);
		
		foreach ($arrYear as $year) 
		{ 
			$i++;
			$year_price[$i] = $chart["income_statement"][$year]["other_cost"] + 
								$chart["income_statement"][$year]["marketing_cost"] +
								$chart["income_statement"][$year]["human_resource"] +
								$chart["income_statement"][$year]["cost_good_service"];

			$price = $year_price[$i] + $price;
			$net_income = $chart["income_statement"][$year]["net_income"];
			if($net_income < $min_net_income)
			{
				$min_net_income = $net_income;
			}
			$total_revenue = $chart["income_statement"][$year]["total_revenue"];
			$goal = $tmp_idea["goal"];
			$cost["cost" . $i] = $year_price[$i];
			
			if(is_array($year_jump) && count($year_jump))
			{
				if(global_settings("MOD_CROWDFUND_GRAPH_CONSIDER_YEARS_NULL"))
				{
					if(global_settings("MOD_CROWDFUND_ENABLE_HIGH_CHART"))
					{
						$data["value"]["year"][] = $year_jump["year"];
						$data["value"]["cost"][] = 0;
						$data["value"]["total_revenue"][] = 0;
						$data["value"]["net_income"][] = 0;
						$data["value"]["goal"][] = $goal; 
					} else
					{
						$data["value"][] = array_merge
								(array	 
									( 
										"date" => $year_jump["year"]
										, "net_income" => 0
										, "total_revenue" => 0
										, "goal" => $goal
										, "cost_united" => $price
										, "cost" => 0
									)
									, $cost
								);
					}
					unset($year_jump["year"]);
				}
			}
			
			if($year_price[$i] > 0 || $net_income > 0 || $total_revenue > 0)
			{
				if(global_settings("MOD_CROWDFUND_ENABLE_HIGH_CHART"))
				{
					$data["value"]["year"][] = $year;
					$data["value"]["cost"][] = $year_price[$i];
					$data["value"]["total_revenue"][] = $total_revenue;
					$data["value"]["net_income"][] = $net_income;
					$data["value"]["goal"][] = $goal; 
				} else
				{
					$data["value"][] = array_merge
							(array	 
								( 
									"date" => $year
									, "net_income" => $net_income
									, "total_revenue" => $total_revenue
									, "goal" => $goal
									, "cost_united" => $price
									, "cost" => $year_price[$i]
								)
								, $cost
							);
				}
			} else
			{
				$year_jump["year"] = $year;
				continue;
			}
			
		}
		if(global_settings("MOD_CROWDFUND_GRAPH_CONSIDER_ONLY_POSITIVE"))
		{
			$data["min_net_income"] = 0;
		}
		else
		{
			$data["min_net_income"] = $min_net_income;   
		}
	}
}

switch($output) 
{
    case "html":
		break;
    case "array":
		print_r($data);
		break;
    case "json":		
    default:
		echo ffCommon_jsonenc($data, true);
}
exit; 
?>