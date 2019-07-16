<?php
    if (!Auth::env("AREA_SERVICES_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"]                   = "Boolean";
    $type_field["v"]                       = "String";
    $type_field["cid"]                       = "String";
    $type_field["content"]                       = "String";
    $type_field["server"]                       = "String";
	if(check_function("system_services_modify"))
		$oGrid = system_services_modify(basename(ffCommon_dirname(__DIR__)), $type_field);

	if(is_object($oGrid)) {
		$cm->oPage->fixed_pre_content = ffTemplate::_get_word_by_code("google_services");
		$cm->oPage->addContent($oGrid);
	}