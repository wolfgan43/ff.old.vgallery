<?php
	$db = ffDB_Sql::factory();
	die("SD");
	$businesspplan = array();
	$smart_url = basename($cm->real_path_info);
	if(strlen($smart_url)) {

	  	$idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));
	  	
	    if(is_array($idea) && count($idea)) 
	    {
	    	$tmp_idea = current($idea);
	    	$businesspplan = mod_crowdfund_get_businessplan($tmp_idea["ID"]);
		}
	}
	
	echo ffCommon_jsonenc($businesspplan, true);
	exit;
?>
