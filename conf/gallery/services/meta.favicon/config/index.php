<?php
    if (!AREA_SERVICES_SHOW_MODIFY) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["base"] = "String";
    $type_field["shortcut icon"] = "String";
    $type_field["mask-icon"] = "String";
    //$type_field["mask-icon-color"] = "String";
    $type_field["apple-touch-icon"] = "String";
    //$type_field["apple-touch-icon-size"] = "String";
    $type_field["icons"] = "Text";
   
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(ffCommon_dirname(__FILE__))), $type_field);
