<?php
	$schema = array();
    $schema["db"]["table_prefix"] = "applet_trivia_";
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "trivia-admin"
     														, "level" => 10
     													);
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "trivia-user"
     														, "level" => 10
     													);     													
?>