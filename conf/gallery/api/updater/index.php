<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
 
//da fare l'autenticazione
require_once(FF_DISK_PATH . "/conf/gallery/sys/api/index." . FF_PHP_EXT);

if(check_function("get_schema_def")) {
	$schema_def = get_schema_def();
	$return = array();
	
	//$ServiceAvailable = $schema_def["service_available"]; // da togliere
	$service_module = $schema_def["module_available"];
	$service_schema = $schema_def["schema"];
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


	if(0 && isset($_POST["data"])) { //DA SISTEMARE NN SO COME NN SO NEMMENO SE QUESTO FILE SERVE ANCORA
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

							    $return = array_replace_recursive($return, api_get_code_by_service($real_service_path, $real_service_path_info, $service_schema[basename(ffCommon_dirname($real_service_path))], false, $service_instance, $php_array));
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
		$arrPath = explode("/", trim($service_path_info, "/"));
		$target = $arrPath[0];

		check_function("get_schema_fields_by_type");
		$relative_api_path = str_replace(FF_DISK_PATH, "", __DIR__);
		
		if(is_file(FF_DISK_PATH . $relative_api_path . "/" . $target . "." . FF_PHP_EXT)) {
			require_once(FF_DISK_PATH . $relative_api_path . "/" . $target . "." . FF_PHP_EXT);
		} elseif(is_array($service_module) && count($service_module)) {
			foreach($service_module AS $module_name) {
				if(is_file(FF_DISK_PATH . "/modules/" . $module_name . $relative_api_path . "/" . $target . "." . FF_PHP_EXT)) {
					require_once(FF_DISK_PATH . "/modules/" . $module_name . $relative_api_path . "/" . $target . "." . FF_PHP_EXT);
					break;
				}
			}
		}
		if(!$return && is_file(FF_DISK_PATH . "/conf/gallery/sys" . $relative_api_path . "/" . $target . "." . FF_PHP_EXT))
			require_once(FF_DISK_PATH . "/conf/gallery/sys" . $relative_api_path . "/" . $target . "." . FF_PHP_EXT);
		
		if(!$return) {
			if(!$service) {
				if($target == "pages") {
					$schema 				= get_schema_fields_by_type("page"); //da fare
				} elseif($target == "anagraph") {
	    			$schema 				= get_schema_fields_by_type("anagraph"); //da fare
				} elseif($target == "tags") {
	    			$schema 				= get_schema_fields_by_type("tags"); //da fare
				} elseif($target == "search") {
	    			$schema 				= get_schema_fields_by_type("search"); //da fare
				} elseif(isset($service_schema[$target])) {
					$service["name"] = $target;
				} elseif($target) {
					$schema_target			= get_schema_fields_by_type("/" . $target, "vgallery");

					if(isset($service_schema[$schema_target["table"]])) {
						$service["name"] = $schema_target["table"];
						$service["schema"] = $service_schema[$schema_target["table"]];
					}
				}
			}

			if($service) {
				$service["module"] = $service_module;
				$service["internal"] = true;
				if(!$service["path_info"])
					$service["path_info"] = "/" . $target;
				if(!$service["schema"])
					$service["schema"] = $service_schema[$service["name"]];

				if(!$service["type"]) {
					$service["type"] = "updater";
					/*if(count($arrPath) > 1)
						$service["type"] = "updater";
					else
						$service["type"] = "detail";*/
				}

				if(count($arrPath) > 1) {
					if(!$service["ID"])
						$service["ID"] = array("name" => $arrPath[count($arrPath) - 1]);
					
					if(!$service["ID"])
						$error = ffTemplate::_get_word_by_code("missing_key");
				} else {
					if(!$service["ID"])
						$service["ID"] = array("public" => 1);
				}

				if(!$error)
					$return = api_get_code($service);
			}
		}
		//print_r($return);
		//die();
		
		if(!$return) {
			if($cm->isXHR())
				http_response_code(500);
			else
				http_response_code(404);
			
			exit;
		}
		
		echo ffCommon_jsonenc($return, true);
		exit;
	}
}
