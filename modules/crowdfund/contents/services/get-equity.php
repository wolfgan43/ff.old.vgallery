<?php
	$preview_equity = 0; 
	$smart_url = basename($cm->real_path_info);

	$price = $_REQUEST["price"];
	if(strlen($smart_url) && $price > 0) {
		$idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));
		$idea_value = current($idea);
  		if($idea_value["equity"] > 0) {
  			$max_equity = round(($idea_value["equity"] * ($idea_value["real_goal"] - $idea_value["real_goal_current"])) / $idea_value["real_goal"], 2);
			$preview_equity = round(($idea_value["equity"] * $price) / $idea_value["real_goal"], 2);
		}
	} else {
		$preview_equity = 0;
	}
	echo ffCommon_jsonenc(array("equity" => $preview_equity, "maxequity" => $max_equity), true);
	
	exit;
?>
