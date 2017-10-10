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
  		$res = array();

		if(is_file(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT)) {
			require(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT);
			if(is_array($schema))	
				$res = $schema;
		}
		if($load_module) {
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
			if(is_array($arrServiceModuleFileName) && count($arrServiceModuleFileName)) {
				foreach($arrServiceModuleFileName AS $real_service_module_path) {
					if(is_file($real_service_module_path . "/conf/schema." . FF_PHP_EXT)) {
						require($real_service_module_path . "/conf/schema." . FF_PHP_EXT);	

						$ModuleAvailable[] = basename($real_service_module_path);
						if(is_array($schema))
							$res = array_replace_recursive($res, $schema); 
					}
				}
			}
		}
		
		if(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/settings." . FF_PHP_EXT)) {
			require(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/settings." . FF_PHP_EXT);
			if(is_array($schema))	
				$res = array_replace_recursive($res, $schema);
		}	
				
		if($selective !== false) {
			if(is_array($selective)) {
				foreach($selective AS $selective_value) {
					if(!array_key_exists($selective_value, $service_schema))
						unset($service_schema[$selective_value]);
				}
			} elseif(strlen($selective)) {
				$service_schema = $service_schema[$selective];
			}
		}			

		switch($out) {
			case "multi_pairs":
				$res = array();
				if(is_array($service_schema) && count($service_schema)) {
					foreach($service_schema AS $schema_key => $schema_data) {
						$res[] = array(new ffData($schema_key), new ffData(ucwords(str_replace("_", " ", $schema_key))));
					}
				}
				break;
			default:
  				$res["settings"] = $settings_schema;
  				if($selective !== false)
  					$res["schema"] = $service_schema;

  				//$res["service_available"] = $ServiceAvailable;
  				if($load_module)
  					$res["module_available"] = $ModuleAvailable;  					
		}
		
		return $res;
  }
