<?php
	$schema = array();
    $schema["db"]["table_prefix"] = "cm_mod_webdir_";
    $schema["db"]["schema"]["structure"] = array();
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "webdir-admin"
     														, "level" => 10
     													);
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "webdir-user"
     														, "level" => 10
     													);
?>