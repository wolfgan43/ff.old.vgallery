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

	function showfiles_on_missing_resource($user_path, $referer, $mode)
	{
		$res = null;

		if (!function_exists("cache_writeLog")) {
			require_once(FF_DISK_PATH . "/library/" . THEME_INSET . "/system/cache." . FF_PHP_EXT);

			cache_writeLog("Mode: " . $mode . " URL: " . $_SERVER["HTTP_HOST"] . $user_path . " REFERER: " . $referer, "resource_missing");
		}

		if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/conf/common.php")) {
			require_once(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/conf/common.php");
			if(function_exists("cms_showfiles_on_warning")) {
				$res = cms_showfiles_on_warning($user_path, $referer, $mode);
			}
		}

		if(!$res) {
			//da migliorare usando cache_olp_path e quindi redirect 301
			$mime = ffMimeTypeByFilename($_SERVER["REQUEST_URI"]);
			switch ($mime) {
				case "image/svg+xml":
				case "image/jpeg":
				case "image/png":
				case "image/gif":
					$res["base_path"] = FF_DISK_PATH . FF_THEME_DIR;
					if (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/noimg.svg")) {
						$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/noimg.svg";
					} else {
						$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . CM_DEFAULT_THEME . "/images/noimg.svg";
					}

					if (stripos($mode, "x") !== false) {
						$res["wizard"]["mode"] = explode("x", strtolower($mode));
						$res["wizard"]["method"] = "proportional";
						$res["wizard"]["resize"] = true;
					} elseif (strpos($mode, "-") !== false) {
						$res["wizard"]["mode"] = explode("-", $mode);
						$res["wizard"]["method"] = "crop";
						$res["wizard"]["resize"] = false;
					}
					break;
				default:

			}
		}

		if(!$res)
			http_response_code(404);

		return $res;
	}
    
    function showfiles_on_before_parsing_error($strError) {
        http_response_code(404);    //da migliorare usando cache_olp_path e quindi redirect 301
        exit;
    }
} 
