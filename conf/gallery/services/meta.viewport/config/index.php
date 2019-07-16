<?php
    if (!Auth::env("AREA_SERVICES_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["width"] = array(
        "extended_type" => "String"
        , "placeholder" => "device-width"
    );
    $type_field["height"] = "String";
    $type_field["initial-scale"] = array(
        "extended_type" => "String"
        , "placeholder" => "initial-scale=1.0"
    );
    $type_field["minimum-scale"] = "String";
    $type_field["maximum-scale"] = "String";   	
    $type_field["user-scalable"] = "String";       
   
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(__DIR__)), $type_field);
