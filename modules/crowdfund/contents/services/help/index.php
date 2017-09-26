<?php
$db = ffDB_Sql::factory();

if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{
	ffRedirect(str_replace("/parsedata", "", $_SERVER["REQUEST_URI"]), 301); // TO FIX!!!
}

$smart_url = basename($cm->real_path_info);

$out = (isset($_REQUEST["out"]) && strlen($_REQUEST["out"])
		    ? $_REQUEST["out"]
		    : "json"
	  );

$suggest = mod_crowdfund_get_idea_suggest(); 

switch($out) 
{
    case "html":
	echo $suggest[$smart_url];
		break;
    case "array":
	      break;
    case "json":		
    default:
	  echo ffCommon_jsonenc($suggest, true);
}
exit;
?>