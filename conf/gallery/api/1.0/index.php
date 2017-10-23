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
 
if(!check_function("get_schema_def"))
	return;
	
$schema_def = get_schema_def();

//$ServiceAvailable = $schema_def["service_available"]; // da togliere
$service_api 		= $schema_def["api"];
$service_module 	= $schema_def["module_available"];
$service_schema 	= $schema_def["schema"];

$return = false;
//todo: ffCommon_crossDomains
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
if(!strlen($service_path_info)) {
	$service_path_info = $cm->real_path_info;
}
$request_method = strtolower($_SERVER["REQUEST_METHOD"]);


/** 
 * Get hearder Authorization
 * */
function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}


if($service_api["oAuth"])
{
	$settings_path = $service_path_info;
	do {

		if(isset($service_api["oAuth"][$settings_path])) {
		    $server["rules"] = $service_api["oAuth"][$settings_path];
		    break;
		}
	} while($settings_path != "/" && ($settings_path = dirname($settings_path)));

	if($server["rules"]["scopes"][$request_method]) 
	{	
		if($server["rules"]["scopes"][$request_method])
		{
			if(!$_REQUEST["access_token"])
			{	
				switch($request_method)
				{
					case "get":
						$_GET["access_token"] = getBearerToken();
						break;
					case "post":
					default:
						$_POST["access_token"] = getBearerToken();
				}
			}
			$server["scopes"]["available"] = $server["rules"]["scopes"][$request_method];

			require FF_DISK_PATH . "/library/OAuth2/Autoloader.php";
			OAuth2\Autoloader::register();

			$server["oAuth2"] = modsec_getOauth2Server();
			$server["request"] = OAuth2\Request::createFromGlobals();
			$server["response"] = new OAuth2\Response();

			if(is_array($server["scopes"]["available"])) 
			{	
				foreach($server["scopes"]["available"] AS $scope) 
				{
					$server["oAuth2"]->verifyResourceRequest($server["request"], $server["response"], $scope);
					if($server["oAuth2"]->verifyResourceRequest($server["request"], $server["response"], $scope))
						$server["scopes"]["valid"][] = $scope;
				}
			}
			else 
			{
				if($server["oAuth2"]->verifyResourceRequest($server["request"], $server["response"], $server["scopes"]["available"]))
					$server["scopes"]["valid"][] = $server["scopes"]["available"];
				
			}

			if(!$server["scopes"]["valid"]) {
				$server["oAuth2"]->getResponse()->send();
				exit;
			}
		}
	}
}

require_once(FF_DISK_PATH . "/conf/gallery/api/index." . FF_PHP_EXT);

switch($request_method) {
	case "patch":
		break;
	case "delete":
		break;
	case "post":
	case "get":
	default:
		$arrPath = explode("/", trim($service_path_info, "/"));
		$target = $arrPath[0];
		check_function("get_schema_fields_by_type");
		$relative_api_path = str_replace(FF_DISK_PATH . "/conf/gallery", "", __DIR__);

		if(/*preg_replace("/[^a-z0-9\/-]+/i", "", $service_path_info) == $service_path_info &&*/ is_file(FF_DISK_PATH . $relative_api_path . $service_path_info . "." . FF_PHP_EXT)) {
			$cm->real_path_info = $service_path_info;

			require_once(FF_DISK_PATH . $relative_api_path . $service_path_info . "." . FF_PHP_EXT);
		} elseif(is_file(FF_DISK_PATH . $relative_api_path . "/" . $target . "." . FF_PHP_EXT)) {
			$cm->real_path_info = substr($service_path_info, strlen($target) + 1);

			require_once(FF_DISK_PATH . $relative_api_path . "/" . $target . "." . FF_PHP_EXT);
		} elseif(is_array($service_module) && count($service_module)) {
			foreach($service_module AS $module_name) {
				if(/*preg_replace("/[^a-z0-9\/-]+/i", "", $service_path_info) == $service_path_info &&*/ is_file(FF_DISK_PATH . "/modules/" . $module_name . $relative_api_path . $service_path_info . "." . FF_PHP_EXT)) {
					$cm->real_path_info = $service_path_info;

					require_once(FF_DISK_PATH . "/modules/" . $module_name . $relative_api_path . $service_path_info . "." . FF_PHP_EXT);
				} elseif(is_file(FF_DISK_PATH . "/modules/" . $module_name . $relative_api_path . "/" . $target . "." . FF_PHP_EXT)) {
					$cm->real_path_info = substr($service_path_info, strlen($target) + 1);

					require_once(FF_DISK_PATH . "/modules/" . $module_name . $relative_api_path . "/" . $target . "." . FF_PHP_EXT);
					break;
				}
			}
		}

		if($return === false && is_file(FF_DISK_PATH . "/conf/gallery" . $relative_api_path . "/" . $target . "." . FF_PHP_EXT)) {
			$cm->real_path_info = substr($service_path_info, strlen($target) + 1);

			require_once(FF_DISK_PATH . "/conf/gallery" . $relative_api_path . "/" . $target . "." . FF_PHP_EXT);
		}

		

/*			
		if($return === false && is_file(FF_DISK_PATH . "/conf/gallery" . $relative_api_path . "/" . $target . "." . FF_PHP_EXT)) {
			$cm->real_path_info = "/" . $target;
			
			require_once(FF_DISK_PATH . "/conf/gallery" . $relative_api_path . "/" . $target . "." . FF_PHP_EXT);
		}
*/
		if($return === false) {
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
					$service["path_info"] = $cm->real_path_info;
				if(!$service["schema"])
					$service["schema"] = $service_schema[$service["name"]];

				if(!$service["type"]) {
					if(count($arrPath) > 1)
						$service["type"] = "detail";
					else
						$service["type"] = "list";
				}
				if($service["type"] == "detail") {
					if(!$service["ID"])
						$service["ID"] = $arrPath[count($arrPath) - 1];
					
					if(!(is_numeric($service["ID"]) && $service["ID"] > 0))
						$error = ffTemplate::_get_word_by_code("missing_key");
				}

				if(!$error)
					$return = api_get_code($service);
			}
		}
		//print_r($return);
		//die();
		
		if(!isset($return)) {
			if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
				http_response_code(500);
			else
				http_response_code(404);
			
			exit;
		}

		echo ffCommon_jsonenc($return, true);
}

exit;
/*
if(is_array($_POST) && count($_POST)) {
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
}*/