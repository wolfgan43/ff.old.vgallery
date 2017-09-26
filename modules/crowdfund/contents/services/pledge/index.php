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
$pledge = array();
$smart_url = basename($cm->real_path_info);
if(strlen($smart_url)) 
{
    $idea = mod_crowdfund_get_idea(null, array("smart_url" => $smart_url));

    if(is_array($idea) && count($idea)) 
    {
        $tmp_idea = current($idea);
        $pledge = mod_crowdfund_get_pledge($tmp_idea["ID"]);
    }
}

switch($output) 
{
    case "html":
	      break;
    case "array":
	      print_r($pledge);
	      break;
    case "json":		
    default:
	    echo ffCommon_jsonenc($pledge, true);
}
exit;
?>