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
require_once(FF_DISK_PATH . "/conf/gallery/api/index." . FF_PHP_EXT);
//todo: ffCommon_crossDomains

switch($cm->real_path_info) {
	case "/install":
		$valid_domain 	= false;

		$referer 		= $_SERVER["HTTP_REFERER"];
		$user_agent 	= $_SERVER["HTTP_USER_AGENT"];
		if(strpos($user_agent, "FFCMS-") === 0 && $referer) {
			$db = ffDB_Sql::factory();

			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.* 
			FROM " . CM_TABLE_PREFIX . "mod_security_domains 
			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db->toSql($referer);
				$db->query($sSQL);
				if($db->nextRecord()) {
					$ID_domain = $db->getField("ID", "Number",true);
					$ftp_ip = ($db->getField("ip_address", "Text", true)
						? $db->getField("ip_address", "Text", true)
						: null
					);
					$ftp_host = $db->getField("nome", "Text", true);
					$ftp_user = $db->getField("ftp_user", "Text", true);
					$ftp_password = $db->getField("ftp_password", "Text", true);
					$ftp_path = $db->getField("ftp_path", "Text", true);

					$token = $db->getField("token", "Text", true);
					if($user_agent == $token) {
						echo ffCommon_jsonenc(updater_installer($ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip, $auth_user, $auth_password, $token, "execute"), true);
					} else {
						http_response_code("401");
					}
				}
		}

		exit;
	default:

}



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
		$relative_api_path = str_replace(__CMS_DIR__ . "/conf/gallery", "", __DIR__);
		
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


function updater_installer($ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip = null, $auth_user, $auth_password, $token, $action = "check") {
	$strError = "";
	$count_check_file = 0;
	$arrBasicInstallFile = array();

	$directory = new RecursiveDirectoryIterator(FF_DISK_PATH . "/conf/gallery/install");
	$files = new RecursiveIteratorIterator($directory);
	$files = array_keys(iterator_to_array($files));

	foreach($files as $file) {
		if(basename($file) != "." && basename($file) != ".." && basename(ffCommon_dirname($file)) != "shield")
			$arrBasicInstallFile[] = str_replace(FF_DISK_PATH, "", $file);
	}

	$directory = new RecursiveDirectoryIterator(FF_DISK_PATH . "/conf/gallery/updater");
	$files = new RecursiveIteratorIterator($directory);
	$files = array_keys(iterator_to_array($files));

	foreach($files as $file) {
		if(basename($file) != "." && basename($file) != "..")
			$arrBasicInstallFile[] = str_replace(FF_DISK_PATH, "", $file);
	}

	if(!$ftp_ip)
		$ftp_ip = gethostbyname($ftp_host);

	if($ftp_ip === false && strpos($ftp_host, "www.") === false)
		gethostbyname("www." . $ftp_host);

	$server_ip = gethostbyname($_SERVER["HTTP_HOST"]);
	if($ftp_ip == $server_ip)
		$ftp_host = "localhost";

	if(strlen($ftp_host) && strlen($ftp_user) && strlen($ftp_password) && strlen($ftp_path)) {
		if($ftp_ip)
			$conn_id = @ftp_connect($ftp_ip);
		if($conn_id === false)
			$conn_id = @ftp_connect($ftp_host, 21, 3);

		if($conn_id === false && $ftp_host == "localhost")
			$conn_id = @ftp_connect("127.0.0.1");
		if($conn_id === false && $ftp_host == "localhost")
			$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

		if($conn_id === false && strpos($ftp_host, "www.") === false && $ftp_host != "localhost")
			$conn_id = @ftp_connect("www." . $ftp_host, 21, 3);

		if($conn_id !== false) {
			// login with username and password
			if(@ftp_login($conn_id, $ftp_user, $ftp_password)) {
				$local_path = $ftp_path;
				$part_path = "";
				$real_ftp_path = NULL;

				if(@ftp_chdir($conn_id, $local_path)) {
					$real_ftp_path = $local_path;
				}

				if($real_ftp_path !== NULL) {
					foreach($arrBasicInstallFile AS $arrBasicInstallFile_value) {
						if($action == "execute") {
							$part_path = "";
							foreach(explode("/", ffCommon_dirname($arrBasicInstallFile_value)) AS $tmp_path) {
								if(strlen($tmp_path)) {
									$part_path .= "/" . $tmp_path;

									if(!@ftp_chdir($conn_id, $real_ftp_path . $part_path)) {
										if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
											$strError .= ffTemplate::_get_word_by_code("creation_failure_directory") . " (" . $real_ftp_path . $part_path . ")" . "<br>";
									}
								}
							}

							if(@ftp_size($conn_id, $real_ftp_path . $arrBasicInstallFile_value) >= 0) {
								@ftp_delete($conn_id, $real_ftp_path . $arrBasicInstallFile_value);
							}
							$ret = @ftp_nb_put($conn_id
								, $real_ftp_path . $arrBasicInstallFile_value
								, FF_DISK_PATH . $arrBasicInstallFile_value
								, FTP_BINARY
								, FTP_AUTORESUME
							);

							while ($ret == FTP_MOREDATA) {

								// Do whatever you want
								// Continue uploading...
								$ret = @ftp_nb_continue($conn_id);
							}
							if ($ret != FTP_FINISHED) {
								$strError .= ffTemplate::_get_word_by_code("upload_failure_file") . " (" . $real_ftp_path . $arrBasicInstallFile_value . ")" . "<br>";
							} else {
								$count_check_file++;
							}
						} else {
							if(@ftp_size($conn_id, $real_ftp_path . $arrBasicInstallFile_value) >= 0) {
								$count_check_file++;
							}
						}
					}
					if($action == "execute") {
						$config_updater_path = "/themes/site/conf/config.updater.php";

						$part_path = "";
						foreach(explode("/", ffCommon_dirname($config_updater_path)) AS $tmp_path) {
							if(strlen($tmp_path)) {
								$part_path .= "/" . $tmp_path;

								if(!@ftp_chdir($conn_id, $real_ftp_path . $part_path)) {
									if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
										$strError .= ffTemplate::_get_word_by_code("creation_failure_directory") . " (" . $real_ftp_path . $part_path . ")" . "<br>";
								}
							}
						}

						$config_updater_content = file_get_contents(FF_DISK_PATH . "/conf/gallery/install/config-updater.tpl");
						$vars = array(
							"[FTP_USERNAME]"						=> $ftp_user
							, "[FTP_PASSWORD]"						=> $ftp_password
							, "[FTP_PATH]"							=> ($ftp_path ? $ftp_path : "/")
							, "[AUTH_USERNAME]"						=> $auth_user
							, "[AUTH_PASSWORD]"						=> $auth_password
							, "[MASTER_SITE]"						=> DOMAIN_INSET
							, "[MASTER_TOKEN]"						=> $token
						);
						$config_updater_content = str_replace(array_keys($vars), array_values($vars), $config_updater_content);

						$tempHandle = @tmpfile();
						@fwrite($tempHandle, $config_updater_content);
						@rewind($tempHandle);

						if(@ftp_size($conn_id, $real_ftp_path . $config_updater_path) >= 0) {
							@ftp_delete($conn_id, $real_ftp_path . $config_updater_path);
						}

						$ret = @ftp_nb_fput($conn_id
							, $real_ftp_path . $config_updater_path
							, $tempHandle
							, FTP_BINARY
							, FTP_AUTORESUME
						);
						while ($ret == FTP_MOREDATA) {
							// Do whatever you want
							// Continue upload...
							$ret = @ftp_nb_continue($conn_id);
						}
						if ($ret != FTP_FINISHED) {
							$strError .= ffTemplate::_get_word_by_code("upload_failure_file") . " (" . $real_ftp_path . $config_updater_path . ")" . "<br>";
						}
					}
				} else {
					$strError = ffTemplate::_get_word_by_code("ftp_unavaible_root_dir");
				}
			} else {
				$strError = ffTemplate::_get_word_by_code("ftp_access_denied");
			}
		} else {
			$strError = ffTemplate::_get_word_by_code("ftp_connection_failure");
		}
		// close the connection and the file handler
		@ftp_close($conn_id);
	} else {
		$strError = ffTemplate::_get_word_by_code("ftp_not_configutated");
	}

	return array("total" => count($arrBasicInstallFile), "count" => $count_check_file, "error" => $strError);
}