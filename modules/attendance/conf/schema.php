<?php
	$schema = array();
    $schema["db"]["table_prefix"] = "cm_mod_attendance_";
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "attendance-admin"
     														, "level" => 10
     													);
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "attendance-sedi"
     														, "level" => 10
     													);     													
    $schema["db"]["schema"]["data"]["cm_mod_security_groups"][] = array("ID" => null
     														, "name" => "attendance-dipendenti"
     														, "level" => 10
     													);     													
?>