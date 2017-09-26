<?php
	$schema = array();
    $schema["db"]["table_prefix"] = "cm_mod_crowdfund_";
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "crowdfund-admin"
     														, "level" => 10
     													);
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "crowdfund-user"
     														, "level" => 10
     													);
     													
    $schema["db"]["schema"]["data"]["anagraph_type"][] = array("ID" => null
     														, "name" => "CrowdFunderUser"
     													);     													
    $schema["db"]["schema"]["data"]["anagraph_type"][] = array("ID" => null
     														, "name" => "CrowdFunderCompany"
     													);     
     													
     													
     													//showfile mode  startup
     													//showfile mode  last-startup													
    $schema["applets"]["banner"]["params"] = array("type" => array("table" => "cm_mod_crowdfund_banner"
    																, "field" => "name"
    																/* , "value" => "" */
    														)
    											);
?>