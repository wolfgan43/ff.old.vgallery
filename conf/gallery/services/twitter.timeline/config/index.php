<?php
    if (!Auth::env("AREA_SERVICES_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"]                   = "Boolean";
    $type_field["access_token"]                       = "String";
    $type_field["access_token_secret"]                       = "String";
    $type_field["consumer_key"]                       = "String";
    $type_field["consumer_secret"]                       = "String";
    $type_field["n_tweet"]                       = "String";
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(__DIR__)), $type_field);
