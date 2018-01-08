<?php
$globals = ffGlobals::getInstance("gallery");
if(check_function("system_gallery_error_document")) {
	system_gallery_error_document($globals->page["user_path"]);
}
exit;

//da fare l'autenticazione
$cm = cm::getInstance();
if(check_function("get_schema_def")) {
	$res = get_schema_def();

	$service_module = $res["module_available"];
	$service_schema = $res["schema"];
/*
	if(is_file(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT)) {
		require(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT);	
	}

	$arrServiceFileName = glob(FF_DISK_PATH . "/library/" . THEME_INSET . "/service/include/*");
	if(is_array($arrServiceFileName) && count($arrServiceFileName)) {
		foreach($arrServiceFileName AS $real_service_name) {
		    if(is_file($real_service_name) && strpos($real_service_name, "." . FF_PHP_EXT) !== false) {
	        	$tmp_service_name = ffGetFilename($real_service_name);
	        	
        		$ServiceAvailable[] = $tmp_service_name;
		    }
		}
	}

	$arrServiceModuleFileName = glob(FF_DISK_PATH . "/modules/*");
	if(is_array($arrServiceModuleFileName) && count($arrServiceModuleFileName)) {
		foreach($arrServiceModuleFileName AS $real_service_module_path) {
			if(is_file($real_service_module_path . "/conf/schema." . FF_PHP_EXT)) {
				require($real_service_module_path . "/conf/schema." . FF_PHP_EXT);	
			}
		}
	}
	unset($arrServiceModuleFileName);
*/
	/*if(strpos($real_service_path, "/modules") === 0) {
		$arrServicePath = explode("/", $real_service_path);
		if(is_file(FF_DISK_PATH . "/" . $arrServicePath[1] . "/" . $arrServicePath[2] . "/conf/schema." . FF_PHP_EXT)) {
			require(FF_DISK_PATH . "/" . $arrServicePath[1] . "/" . $arrServicePath[2] . "/conf/schema." . FF_PHP_EXT);	
		}
		unset($arrServicePath);
	}*/
/*
	        	if(strpos($cm->real_path_info, "/" . $tmp_service_name) !== false) {
					$real_service_path = substr($cm->real_path_info, 0, strpos($cm->real_path_info, "/" . $tmp_service_name) + strlen("/" . $tmp_service_name));
					$real_service_path_info = substr($cm->real_path_info, strlen($real_service_path));
	        	}
*/


	exit;
	
	//in teoria sotto nn serve a nulla
	/*
	if(isset($_POST["data"])) {
		$return = array();

		$multi_service = json_decode($_POST["data"], true);
		unset($_POST["data"]);
		if($multi_service !== false) {
			foreach($multi_service AS $multi_service_name => $multi_service_instances) {
				if(is_array($multi_service_instances) && count($multi_service_instances)) {
					foreach($multi_service_instances AS $service_instance) {
						$real_service_path = "";
						$real_service_path_info = "";
						foreach($ServiceAvailable AS $ServiceAvailable_value) {
							if(strpos($service_instance["path"], "/" . $ServiceAvailable_value) !== false) {
								$real_service_path = substr($service_instance["path"], 0, strpos($service_instance["path"], "/" . $ServiceAvailable_value) + strlen("/" . $ServiceAvailable_value));
								$real_service_path_info = substr($service_instance["path"], strlen($real_service_path));
								break;							
							}
						}
						if(strlen($real_service_path)) {
							if(array_key_exists(basename(ffCommon_dirname($real_service_path)), $service_schema)) {
								parse_str($service_instance["query"], $_REQUEST);

							    $return = array_replace_recursive($return, service_get_code_by_service($real_service_path, $real_service_path_info, $service_schema[basename(ffCommon_dirname($real_service_path))], false, $service_instance, $php_array));
							}
						}
					}
				}
			}
		}

        echo ffCommon_jsonenc($return, true);
        exit;
	} else {
		if(!strlen($service_path_info)) {
			$service_path_info = $cm->real_path_info;
		}
		
		$real_service_path = "";
		$real_service_path_info = "";
		foreach($ServiceAvailable AS $ServiceAvailable_value) {
			if(strpos($service_path_info, "/" . $ServiceAvailable_value) !== false) {
				$real_service_path = substr($service_path_info, 0, strpos($service_path_info, "/" . $ServiceAvailable_value) + strlen("/" . $ServiceAvailable_value));
				$real_service_path_info = substr($service_path_info, strlen($real_service_path));
				$arrPathInfo = explode("/", trim($real_service_path_info, "/"));
				if(is_array($arrPathInfo) && count($arrPathInfo))
					$real_service_alt_path = str_replace("-", "_", $arrPathInfo[0]);

				unset($arrPathInfo);
				break;
			}
		}

		if(strlen($real_service_path)) {
			if(array_key_exists(basename(ffCommon_dirname($real_service_path)), $service_schema)) {
			    $php_array = service_get_code_by_service($real_service_path, $real_service_path_info, $service_schema[basename(ffCommon_dirname($real_service_path))], $internal_service, $parent_schema, $php_array);
			} elseif(strlen($real_service_alt_path) && array_key_exists($real_service_alt_path, $service_schema)) {
				$php_array = service_get_code_by_service($real_service_path, $real_service_path_info, $service_schema[$real_service_alt_path], $internal_service, $parent_schema, $php_array);
			} else {
                $sError = "service schema undefined: " . $real_service_path . (strlen($real_service_alt_path) ? " => " . $real_service_alt_path : $real_service_path_info);
			}
		}
		
		if(!$php_array) {
			echo $sError;
			if($cm->isXHR())
				http_response_code(500);
			else
				http_response_code(404);
			
			exit;
		}
	}*/
}
