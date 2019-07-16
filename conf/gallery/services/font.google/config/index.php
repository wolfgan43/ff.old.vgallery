<?php
    if (!Auth::env("AREA_SERVICES_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["font"] = "String";   	
    $type_field["font2"] = "String";   	
    $type_field["font3"] = "String";   	
    $type_field["font4"] = "String";   	
    $type_field["font5"] = "String";   	
    $type_field["font6"] = "String";   	
    $type_field["font7"] = "String";   	
    
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(__DIR__)), $type_field);