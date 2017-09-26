<?php
	$schema = array();
    $schema["db"]["table_prefix"] = "cm_mod_phonecall_";
    $schema["db"]["schema"]["structure"] = array();
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "phonecall-admin"
     														, "level" => 10
     													);
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "phonecall-user"
     														, "level" => 10
     													);
?>