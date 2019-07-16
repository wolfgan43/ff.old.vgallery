<?php
    if (!(Auth::env("AREA_SERVICES_SHOW_MODIFY")|| $force_company_data)) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["enable_international"] = "Boolean";
    $type_field["force_compilation"] = "Boolean";
    $type_field["tpl"] = "String";
    $type_field["company_name"] = "String";
    $type_field["cf"] = "String";
    $type_field["piva"] = "String";
    $type_field["address"] = "String";
    $type_field["cap"] = "String";
    $type_field["city"] = "String";
    $type_field["prov"] = "String";
    $type_field["state"] = "String";
    $type_field["tel"] = "String";
    $type_field["fax"] = "String";
    $type_field["email"] = "String";
    $type_field["info"] = "Text";
    $type_field["label"] = "Boolean";
    
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(__DIR__)), $type_field);
