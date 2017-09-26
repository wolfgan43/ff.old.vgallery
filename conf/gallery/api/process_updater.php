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
$params = null;

$service_params["list"] = array(
    "out" => array(
    
    )
);

if(!$disable_service_process) {
	$db = ffDB_Sql::factory();

	$arrPathInfo = explode("/", trim($service_path_info, "/"));
	if(is_array($arrPathInfo) && count($arrPathInfo)) {
		$service_name = str_replace("-", "_", $arrPathInfo[0]);
		unset($arrPathInfo[0]);
		$service_params = array_values($arrPathInfo);	
	}
	
	$params = array();
	
	if(!$internal_service) {
        $php_array = array();
        $total_record = 0;
		
        $output_type = (isset($_REQUEST["out"])
                    ? $_REQUEST["out"]
                    : $service_output);
                    
        switch($output_type) {
            default:
                $page = (isset($_REQUEST["page"])
                            ? $_REQUEST["page"]
                            : 0);
                $rows = (isset($_REQUEST["rows"])
                            ? $_REQUEST["rows"]
                            : 10);
        }
        $str_param_url = "page=" . ($page + 1) . "&"
		    		    . "rows=" . $rows;
        //custom from service
        if(isset($_REQUEST["params"])) {
            $params = $_REQUEST["params"];
        }
		
		$params["public"] = true;
		
        $limit_data = (isset($_REQUEST["limit"]) && isset($_REQUEST["limit"]["key"]) && isset($_REQUEST["limit"]["value"])
                    ? $_REQUEST["limit"]
                    : null);
    } else {
    	if($parent_schema["relationship"][$service_name]["multi"]) 
    		$real_key = "key";
    	else 
    		$real_key = "rel_key";

        if(array_key_exists($service_name, $parent_schema["relationship"])
            && array_key_exists($real_key, $parent_schema["relationship"][$service_name]) 
            && strlen($parent_schema["relationship"][$service_name][$real_key])
            && array_key_exists("value", $parent_schema["relationship"][$service_name]) 
            && is_array($parent_schema["relationship"][$service_name]["value"])
        ) {
            $limit_data = array(
                            "key" => $parent_schema["relationship"][$service_name][$real_key]
                            , "value" => implode(",", array_keys($parent_schema["relationship"][$service_name]["value"]))
                        );
        } else {
            $limit_data = null;
        }
        $rows = null;
        $page = 0;
    }
	/*
	$real_service_module = null;
    if(is_array($service_module) && count($service_module)) {
	    foreach($service_module AS $service_module_value) {
		    if(file_exists(FF_DISK_PATH . "/modules/" . $service_module_value . "/library/service/get_" .  $service_name . "." . FF_PHP_EXT)) {
			    $real_service_module =  $service_module_value;
			    break;
		    }
	    }
    }*/
	
	if(strlen($service_name)) {
        if($limit_data === null && is_array($service_params) && count($service_params)) {
            if(is_numeric($service_params[0]))
            	$service_params_key = "ID";
            else
            	$service_params_key  = "name";

            $limit_data = array("key" => $service_params_key , "value" => $service_params[0]);
            $is_detail = true;
        }
		
		$sSQL = api_get_query($service, $limit_data, $params, $arrSortField, null, $search);
        //$res_service = call_user_func("service_get_" . $service_name, $limit_data, $params, $arrSortField, null, $search);
        //$sSQL = $res_service["sql"];
		if(strlen($sSQL)) {
			require_once(__DIR__ . "/parse." . FF_PHP_EXT);

		    $db->query($sSQL);
		    if($db->nextRecord()) {
		    	$arrServiceFileName = array();
				$arrServiceField = array();
			    if(($is_detail || $internal_service) && is_array($service_schema["relationship"]) && count($service_schema["relationship"])) {
			        foreach($service_schema["relationship"] AS $tmp_service_name => $tmp_service_params) {
		            	if(!array_key_exists($tmp_service_name, $php_array)) {
				            $real_service_path = "";
				            if(is_array($service_module) && count($service_module)) {
								foreach($service_module AS $service_module_value) {
									if(file_exists(FF_DISK_PATH . "/modules/" . $service_module_value . "/library/service/get_" .  $tmp_service_name . "." . FF_PHP_EXT)) {
										$real_service_path = "/modules/" . $service_module_value . "/" . "updater" . "/" . $tmp_service_name;
										break;
									}
								}
				            }
							if(!strlen($real_service_path)) {
								$real_service_path = "/" . "updater" . "/" . $tmp_service_name;
							}
							
							$arrServiceFileName[$real_service_path] = $tmp_service_params;
							if($tmp_service_params["multi"]) {
								$arrServiceField[$tmp_service_params["rel_key"]] = $tmp_service_name;
							} else {
								$arrServiceField[$tmp_service_params["key"]] = $tmp_service_name;
							}
						}						
			        }
				}
		    
				$i = 0;  
		        $total_record = $db->numRows();
		        if($rows === null)
		            $rows = $total_record;

		        if($page * $rows < $total_record) {
		            $seek = $page * $rows;
		            if($seek > 0)
		                $db->seek($seek);

		            do {
		                if($i >= $rows)
		                    break;

		                if($total_record == 1) {
		                    $index = null;
		                } else {
		                    $index = $i;
		                }
	               
						if(is_array($db->fields) && count($db->fields)) {  
		                    $tmp_php_array = array();

		                    foreach($db->fields AS $field_data) {
		                        $field_value = api_parse_field_by_type($service_schema["field"][$field_data->name], $db->getField($field_data->name, "Text", true), $field_data->name, true); 

	                        	$tmp_php_array[$field_data->name] = $field_value;

								if(count($arrServiceField) && array_key_exists($field_data->name, $arrServiceField)) {
									$service_schema["relationship"][$arrServiceField[$field_data->name]]["value"][$field_value] = true;
								}
							}

							if($internal_service) {
			                    if($index === null) 
			                        $php_array[$service_name] = $tmp_php_array;
			                    else
			                        $php_array[$service_name][$index] = $tmp_php_array;
							} else {
			                    if($is_detail) {
			                        if($index === null) 
			                            $php_array[$service_name] = $tmp_php_array;
			                        else
			                            $php_array[$service_name][$index] = $tmp_php_array;

								} elseif($index === null) 
			                        $php_array = $tmp_php_array;
			                    else
			                        $php_array[$index] = $tmp_php_array;
							}
						}
						
						$i++;
		    		} while($db->nextRecord());
		    	}

			    if(is_array($arrServiceFileName) && count($arrServiceFileName)) {
			        foreach($arrServiceFileName AS $real_service_path => $tmp_service_params) {
						if($tmp_service_params["multi"]) {
							if(isset($php_array[$service_name][$tmp_service_params["rel_key"]]))
								$service_schema["relationship"][$tmp_service_name]["value"][$php_array[$service_name][$tmp_service_params["rel_key"]]] = true;
						} else {
							if(isset($php_array[$service_name][$tmp_service_params["key"]]))
								$service_schema["relationship"][$tmp_service_name]["value"][$php_array[$service_name][$tmp_service_params["key"]]] = true;
						}

		            	$php_array = api_get_service($php_array, $service_schema, $real_service_path);
			        }
			    }
			}
            
            $remote_php_array = array();

             if(strpos(strtolower(MASTER_SITE), "www.") === 0) {
                $real_master_site = substr(MASTER_SITE, strpos(MASTER_SITE, ".") + 1);
            } else {
                $real_master_site = MASTER_SITE;
            }
            
            if($real_master_site != DOMAIN_NAME
            ) {
                $json_remote_php_array = file_get_contents("http://" . $real_master_site . "/api/updater/" . $service_path_info);
                if($json_remote_php_array)
                    $remote_php_array = json_decode($json_remote_php_array, true);
                    
                if(count($remote_php_array)) {
                    if($index === null && array_key_exists("ID", $remote_php_array))
                		$php_array = array_replace_recursive($php_array, $remote_php_array);
                	else
                		$php_array = array_replace_recursive(array($php_array), $remote_php_array);
                }
            }

		} else 
			$response_code = 204;
	} else {
		$response_code = 400;
	}

	http_response_code((strlen($response_code) ? $response_code : null));
 	switch($output_type) {
 		case "api":
            $return = $php_array;
            break;
 		case "service":
 			$return = $php_array;
 			break;
        case "html":
            if(check_function("get_update_by_service"))
            	echo get_update_by_service_html($php_array, $_REQUEST["url"]);
            	exit;
            break;
        default:
			$return = $php_array;
	}	

	
}