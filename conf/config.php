<?php
	global $ff_global_setting;
/*
	$ff_global_setting["ffPage_html"]["cdn_version"] = array(
															"jquery" => array(
	                                                            "major" => "1"  
	                                                            , "minor" => "11"
	                                                            , "build" => "1"
	                                                        )
															, "jquery.ui" => array(
	                                                            "major" => "1"  
	                                                            , "minor" => "10"
	                                                            , "build" => "4"
	                                                        )

															, "swfobject" => array(
	                                                            "major" => "2"  
	                                                            , "minor" => "2"
	                                                        )
														);*/

if(defined("SHOWFILES_IS_RUNNING")) { 
	$ff = ffGlobals::getInstance("ff");
	if($ff->showfiles_events) {
        $ff->showfiles_events->addEvent("on_warning", "showfiles_on_missing_resource", ffEvent::PRIORITY_NORMAL);
		$ff->showfiles_events->addEvent("on_error", "showfiles_on_before_parsing_error", ffEvent::PRIORITY_NORMAL);
    }
    //cm::_addEvent("showfiles_on_error", "showfiles_on_before_parsing_error", ffEvent::PRIORITY_NORMAL);

	function showfiles_on_missing_resource($mode) {
		$res = null;
		if(!function_exists("write_notification")) {
			require_once(FF_DISK_PATH . "/library/" . THEME_INSET . "/common." . FF_PHP_EXT);

            if(check_function("write_notification"))
				write_notification("_error_notfound_path", "CM ShowFiles: " . $mode, "warning", "", $_SERVER["REQUEST_URI"]);
        }
		http_response_code(404);	//da migliorare usando cache_olp_path e quindi redirect 301
		if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/404.png")) {
			$res["base_path"] = FF_DISK_PATH . FF_THEME_DIR;
			$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/404.png";
		} elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/images/error-pages/404.png")) {
			$res["base_path"] = FF_DISK_PATH . FF_THEME_DIR;
			$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET  . "/images/error-pages/404.png";
		}
		
		if(stripos($mode, "x") !== false) {
	        $res["wizard"]["mode"] 					= explode("x", strtolower($mode));
	        $res["wizard"]["method"] 				= "proportional";
	        $res["wizard"]["resize"] 				= true;
	    } elseif(strpos($mode, "-") !== false) {
	        $res["wizard"]["mode"] 					= explode("-", $mode);
	        $res["wizard"]["method"] 				= "crop";
	        $res["wizard"]["resize"] 				= false;
	    }		
		return $res;
	}
    
    function showfiles_on_before_parsing_error($strError) {
        http_response_code(404);    //da migliorare usando cache_olp_path e quindi redirect 301
        exit;
    }
} 
