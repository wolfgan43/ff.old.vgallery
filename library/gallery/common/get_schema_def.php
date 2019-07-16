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
  function get_schema_def($selective = null, $load_module = true, $out = null) {
  		static $loaded_schema = null;

	  	if(!$loaded_schema) {
			if (is_file(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT)) {
				require(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT);
				/** @var include $schema */
				if (is_array($schema))
					$loaded_schema["schema"] = $schema;
			}
			if ($load_module) {
				/*$arrServiceFileName = glob(FF_DISK_PATH . "/library/" . THEME_INSET . "/service/include/*");
				if(is_array($arrServiceFileName) && count($arrServiceFileName)) {
					foreach($arrServiceFileName AS $real_service_name) {
						if(is_file($real_service_name) && strpos($real_service_name, "." . FF_PHP_EXT) !== false) {
							$tmp_service_name = ffGetFilename($real_service_name);

							$ServiceAvailable[] = $tmp_service_name;
						}
					}
				}*/

				$arrServiceModuleFileName = glob(FF_DISK_PATH . "/modules/*");
				if (is_array($arrServiceModuleFileName) && count($arrServiceModuleFileName)) {
					foreach ($arrServiceModuleFileName AS $real_service_module_path) {
						if (is_file($real_service_module_path . "/conf/schema." . FF_PHP_EXT)) {
							require($real_service_module_path . "/conf/schema." . FF_PHP_EXT);

							$ModuleAvailable[] = basename($real_service_module_path);
							if (is_array($schema))
								$loaded_schema["schema"] = array_replace_recursive($loaded_schema["schema"], $schema);
						}
					}
				}
			}

            if (is_file(FF_DISK_PATH . "/library/" . THEME_INSET . "/settings." . FF_PHP_EXT)) {
                require(FF_DISK_PATH . "/library/" . THEME_INSET . "/settings." . FF_PHP_EXT);
                if (is_array($schema))
                    $loaded_schema["schema"] = array_replace_recursive($loaded_schema["schema"], $schema);
            }

			if (is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/settings." . FF_PHP_EXT)) {
				require(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/settings." . FF_PHP_EXT);
				if (is_array($schema)) {
                    $loaded_schema["schema"] = array_replace_recursive($loaded_schema["schema"], $schema);
                }
			}

			/** @var include $settings_schema */
			$loaded_schema["settings"] 				= $settings_schema;
			/** @var include $service_schema */
			$loaded_schema["service"] 				= $service_schema;
			$loaded_schema["module"] 				= $ModuleAvailable;
		}


	  	$service_schema 							= $loaded_schema["service"];
		if ($selective !== false) {
	  		if (is_array($selective)) {
	  			foreach ($selective AS $selective_value) {
	  				if (!array_key_exists($selective_value, $service_schema))
	  					unset($service_schema[$selective_value]);
	  			}
	  		} elseif (strlen($selective)) {
	  			$service_schema 					= $service_schema[$selective];
	  		}
		}
		switch ($out) {
			case "multi_pairs":
				$res = array();
				if (is_array($service_schema) && count($service_schema)) {
					foreach ($service_schema AS $schema_key => $schema_data) {
						$res[] 						= array(new ffData($schema_key), new ffData(ucwords(str_replace("_", " ", $schema_key))));
					}
				}
				break;
			default:
				$res 								= $loaded_schema["schema"];
				$res["settings"] 					= $settings_schema;
				if ($selective !== false)
					$res["schema"] 					= $service_schema;

				if ($load_module)
					$res["module_available"] 		= $loaded_schema["module"];
		}
	  return $res;
  }

function resolve_include_api($path_info, $api_path, $schema_def = null, $out = null) {
	if(!$schema_def)
  		$schema_def 				= get_schema_def();

	  $real_path_info 				= null;
	  $include 						= null;

	  $service_module 				= $schema_def["module_available"];

	  $arrPath 						= explode("/", trim($path_info, "/"));
	  $target 						= $arrPath[0];
	  $is_valid_path 				= preg_replace("/[^a-z0-9\/-]+/i", "", $path_info) == $path_info;

	  if($api_path && $is_valid_path && is_file(FF_DISK_PATH . $api_path . $path_info . "." . FF_PHP_EXT)) {
		  $real_path_info 			= $path_info;
		  $include = FF_DISK_PATH . $api_path . $path_info . "." . FF_PHP_EXT;
	  } elseif($api_path && is_file(FF_DISK_PATH . $api_path . "/" . $target . "." . FF_PHP_EXT)) {
		  $real_path_info 			= substr($path_info, strlen($target) + 1);
		  $include 					= FF_DISK_PATH . $api_path . "/" . $target . "." . FF_PHP_EXT;
	  } elseif(is_array($service_module) && count($service_module)) {
		  foreach($service_module AS $module_name) {
			  if($is_valid_path && is_file(FF_DISK_PATH . "/modules/" . $module_name . $api_path . $path_info . "." . FF_PHP_EXT)) {
				  $real_path_info 	= $path_info;
				  $include 			= FF_DISK_PATH . "/modules/" . $module_name . $api_path . $path_info . "." . FF_PHP_EXT;
				  break;
			  } elseif(is_file(FF_DISK_PATH . "/modules/" . $module_name . $api_path . "/" . $target . "." . FF_PHP_EXT)) {
				  $real_path_info 	= substr($path_info, strlen($target) + 1);
				  $include 			= FF_DISK_PATH . "/modules/" . $module_name . $api_path . "/" . $target . "." . FF_PHP_EXT;
				  break;
			  }
		  }
	  }

	  if(!$real_path_info && is_file(FF_DISK_PATH . "/conf/gallery" . $api_path . "/" . $target . "." . FF_PHP_EXT)) {
		  $real_path_info 			= substr($path_info, strlen($target) + 1);
		  $include 					= FF_DISK_PATH . "/conf/gallery" . $api_path . "/" . $target . "." . FF_PHP_EXT;
	  }

	  $res = array(
	  	  "real_path_info" 			=> $real_path_info
		  , "include" 				=> $include
	  );

	  return ($out
	  	  ? $res[$out]
	  	  : $res
	  );
  }

function resolve_include_service($path_info, $schema_def = null) {
	if(!$schema_def)
		$schema_def 				= get_schema_def();

	$real_path_info 				= null;
	$include 						= null;

	$service_module 				= $schema_def["module_available"];

	if(is_file(FF_DISK_PATH . "/contents/services" . $path_info . "." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/contents/services" . $path_info . "." . FF_PHP_EXT;
	} elseif(is_file(FF_DISK_PATH . "/contents/srv" . $path_info . "." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/contents/srv" . $path_info . "." . FF_PHP_EXT;
	} elseif(is_file(FF_DISK_PATH . "/applets/services" . $path_info . "." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/applets/services" . $path_info . "." . FF_PHP_EXT;
	} elseif(is_file(FF_DISK_PATH . "/applets/services" . $path_info . "/index." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/applets/services" . $path_info . "/index." . FF_PHP_EXT;
	} elseif(is_file(FF_DISK_PATH . "/applets/srv" . $path_info . "." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/applets/srv" . $path_info . "." . FF_PHP_EXT;
	} elseif(is_array($service_module) && count($service_module)) {
		if(strpos($path_info, "/", 1)) {
			$arrPathinfo = explode("/", ltrim($path_info, "/"), 2);
			if(is_file(FF_DISK_PATH . "/modules/" . $arrPathinfo[0] . "/contents/services/" . $arrPathinfo[1] . "." . FF_PHP_EXT)) {
				$include = FF_DISK_PATH . "/modules/" . $arrPathinfo[0] . "/contents/services/" . $arrPathinfo[1] . "." . FF_PHP_EXT;
			}
		}
		if(!$include) {
			foreach ($service_module AS $module_name) {
				if (is_file(FF_DISK_PATH . "/modules/" . $module_name . "/contents/services" . $path_info . "." . FF_PHP_EXT)) {
					$include = FF_DISK_PATH . "/modules/" . $module_name . "/contents/services" . $path_info . "." . FF_PHP_EXT;
					break;
				} elseif (is_file(FF_DISK_PATH . "/modules/" . $module_name . "/contents/srv" . $path_info . "." . FF_PHP_EXT)) {
					$include = FF_DISK_PATH . "/modules/" . $module_name . "/contents/srv" . $path_info . "." . FF_PHP_EXT;
					break;
				}
			}
		}
	}

	if(!$include && is_file(FF_DISK_PATH . "/conf/gallery/srv" . $path_info . "." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/conf/gallery" . $path_info . "." . FF_PHP_EXT;
	}

	return $include;
}

function resolve_include_applet($path_info, $schema_def = null) {
	if(!$schema_def)
		$schema_def 				= get_schema_def();

	$real_path_info 				= null;
	$include 						= null;

	$service_module 				= $schema_def["module_available"];

	if(is_file(FF_DISK_PATH . "/applets" . $path_info . "/index." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/applets" . $path_info . "/index." . FF_PHP_EXT;
	} elseif(is_file(FF_DISK_PATH . "/applets" . $path_info . "." . FF_PHP_EXT)) {
		$include 					= FF_DISK_PATH . "/applets" . $path_info . "." . FF_PHP_EXT;
	} elseif(is_array($service_module) && count($service_module)) {
		foreach($service_module AS $module_name) {
			if(is_file(FF_DISK_PATH . "/modules/" . $module_name . "/applets" . $path_info . "/index." . FF_PHP_EXT)) {
				$include 			= FF_DISK_PATH . "/modules/" . $module_name . "/applets" . $path_info . "/index." . FF_PHP_EXT;
				break;
			} elseif(is_file(FF_DISK_PATH . "/modules/" . $module_name . "/applets" . $path_info . "." . FF_PHP_EXT)) {
				$include 			= FF_DISK_PATH . "/modules/" . $module_name . "/applets" . $path_info . "." . FF_PHP_EXT;
				break;
			}
		}
	}

	return $include;
}