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


    function api_get_service($php_array, $parent_schema, $service_path_info = null, $version = "1.0") {
        $internal_service = true;
        $compact = false;   
        $service_output = "service";

        require(FF_DISK_PATH . "/conf/gallery/api/" . $version . "/index." . FF_PHP_EXT);

        /** @var include $return */
        return $return;
    }

    //old deprecated to check
     function api_get_code_by_service($service_path, $service_path_info, $service_schema = array(), $internal_service = false, $parent_schema = null, $php_array = array()) {
         /** @var used in include $service_name */
         $service_name = basename(ffCommon_dirname($service_path));

        if(is_file(__DIR__ . "/process_" . basename($service_path) . "." . FF_PHP_EXT)) {
            require __DIR__ . "/process_" . basename($service_path) . "." . FF_PHP_EXT;

			if($internal_service)
                /** @var include $php_array */
                return $php_array;
            else
                /** @var include $return */
                return $return;
        }

        return false;
    }
    
    function api_get_code($service, $php_array = array()) {
    	//static $service_module = array();

        $service_name = $service["name"];
        $service_path_info = $service["path_info"];
        $service_schema = $service["schema"];
        $service_module = $service["module"];
        if($service["internal"] && is_array($service["parent_schema"])) {
	        $internal_service = $service["internal"];
	        $parent_schema = $service["parent_schema"];
		} else {
			$service_output = "api";
		}    

        /*if(strpos($service_path, "/modules/") === 0) {
        	$tmp_arrModule = explode("/", substr($service_path, strlen("/modules/")));
        	if(strlen($tmp_arrModule[0]) && array_search($tmp_arrModule[0], $service_module) === false) {
				$service_module[] = $tmp_arrModule[0];	
        	}
        	unset($tmp_arrModule);
		}*/

        if(is_file(__DIR__ . "/process_" . $service["type"] . "." . FF_PHP_EXT)) {
            require __DIR__ . "/process_" . $service["type"] . "." . FF_PHP_EXT;

            /** @var include $return */
            return $return;
        }

        return false;
    }    
    
    
    function api_get_query($service, $limit_data = null, $params = null, $sort_field = null, $sort_dir = null, $search = null) {
        if(!function_exists("api_get_" . $service["name"])) {
	        if(strlen($service["module"][$service["name"]]) && is_file(FF_DISK_PATH . "/modules/" . $service["module"][$service["name"]] . "/api/get_" . $service["name"] . "." . FF_PHP_EXT)) {
        		require_once(FF_DISK_PATH . "/modules/" .  $service["module"][$service["name"]] . "/api/get_" . $service["name"] . "." . FF_PHP_EXT);
	        } elseif(file_exists(__DIR__ . "/get_" . $service["name"] . "." . FF_PHP_EXT)) {
				require_once(__DIR__ . "/get_" . $service["name"] . "." . FF_PHP_EXT);
	        }
		}
    	if(function_exists("api_get_" . $service["name"])) {
			$res = call_user_func("api_get_" . $service["name"], $limit_data, $params, $sort_field, $sort_dir, $search);
			$sSQL = $res["sql"];
		}

		if(!$sSQL) {
			$db = ffDB_Sql::factory();
			$sort = null;
			$res = array(
				"fields" => array()
			);


			$arrFormField = array();
			
    		$primary_table = ($service["schema"]["table"]
    			? $service["schema"]["table"]
    			: $service["name"]
    		);

			$sSQL_primary_field = $primary_table . ".*";
			$sSQL_field = "";
			$sSQL_primary_where = "";

			if($service["ID"]) {
				if(is_array($service["ID"])) {
					foreach($service["ID"] AS $field_name => $field_value) {
						$res["fields"][$field_name] = $field_value;			
						
						$sSQL_primary_where .= " AND " . $primary_table . ".`" . $field_name . "` = " . $db->toSql($field_value);
					}
				} elseif(is_numeric($service["ID"])) {
					$res["fields"]["ID"] = $service["ID"];			
					
				   	$sSQL_primary_where .= " AND " . $primary_table . ".`ID` = " . $db->toSql($service["ID"], "Number");
				}
			}
			
			
			/**
			* Category
			*/
			if($service["schema"]["category"] && $params["category"]) {
				$managed_params["category"] = $params["category"];
				unset($params["category"]);
				
				$db->query(api_process_sql($service["schema"]["category"]["table"], $service["schema"]["category"]["field"], $managed_params["category"], $res["fields"]));
				if($db->nextRecord()) {
					if(is_array($db->fields) && count($db->fields)) {  
						$res["fields"] = array_replace($res["fields"], $db->record);
						
						if($db->record[$service["schema"]["category"]["field"]["primary_rel"]]) {
							$sSQL_primary_where .= " AND " . $primary_table . "." . $service["schema"]["category"]["field"]["primary_rel"] . " = " . $db->getField($service["schema"]["category"]["field"]["primary_rel"], "Text", true);
						}
					}
				} else {
					return false;
				}
			}

			/**
			* Revision
			*/
			if($service["schema"]["revision"] && $params["revision"]) {
				$managed_params["revision"] = $params["revision"];
				unset($params["revision"]);
				
				$db->query(api_process_sql($service["schema"]["revision"]["table"], $service["schema"]["revision"]["field"], $managed_params["revision"], $res["fields"]));
				if($db->nextRecord()) {
					if(is_array($db->fields) && count($db->fields)) {  
						$res["fields"] = array_replace($res["fields"], $db->record);
						
						if($db->record[$service["schema"]["revision"]["field"]["primary_rel"]]) {
							$sSQL_primary_where .= " AND " . $primary_table . "." . $service["schema"]["revision"]["field"]["primary_rel"] . " = " . $db->getField($service["schema"]["revision"]["field"]["primary_rel"], "Text", true);
						}
					}
				}				
			}

			if($service["add_field"]) {
				$sSQL_add_field = "";
				$sSQL_add_field_empty = "";
				if(is_array($service["add_field"]) && count($service["add_field"])) {
					$arrResFlip = array_keys($res["fields"]);
					array_walk($arrResFlip, "api_process_tags");
                    $sSQL_having = "";
					foreach($service["add_field"] AS $add_field_key => $add_field_value) {
						$sSQL_add_field .= ", (" . str_ireplace($arrResFlip, $res["fields"], $add_field_value) . ") AS `" . $add_field_key . "`";
						$sSQL_add_field_empty .= ", '' AS " . $add_field_key;

						if(is_array($sort_field) && count($sort_field)) {
            				if(array_key_exists($add_field_key, $sort_field)) {
            					if(strlen($sort))
            						$sort .= ", ";

								$sort .= $sort_field[$add_field_key];
            				}
						}
					    if(strlen($search)) {
						    if(strlen($sSQL_having))
						        $sSQL_having .= " OR ";
						    
						    $sSQL_having .= " `" . $add_field_key . "` LIKE '%" . $db->toSql(str_replace(" ", "%", $search), "Text", false) . "%' COLLATE utf8_general_ci";
						}
					}
				}				
			}


			
			/* Primary Table*/
			if($limit_data["field"]["primary"]) {
				if(is_array($limit_data["field"]["primary"]) && count($limit_data["field"]["primary"])) {
					foreach($limit_data["field"]["primary"] AS $field_name => $field_AS) {
						$arrPrimaryField[] = '`' . $primary_table . '`.`' . $field_name . "` AS `" . $field_AS . "`";
					}
					
					$sSQL_primary_field = implode(", ", $arrPrimaryField);
				} elseif(!$limit_data["field"]["primary"]) {
					$sSQL_primary_field = $primary_table . ".ID";
				}
			}
			
			if($service["schema"]["external_field"] && $limit_data["field"]["external"] !== false) {
				if($params["external_field"]) {
					$managed_params["external_field"] = $params["external_field"];
					unset($params["external_field"]);
				}					
				/* Table external*/
				if($limit_data["field"]["id"]) {
					$res["fields"]["limit_id"] = $limit_data["field"]["id"];
					
					$arrLimitFieldID = explode(",", $limit_data["field"]["id"]);	
				}
				if(is_array($limit_data["field"]["name"]) && count($limit_data["field"]["name"])) {
					$res["fields"]["limit_name"] = implode(", '", $limit_data["field"]["name"]);
				} elseif($limit_data["field"]["name"]) {
					$res["fields"]["limit_name"] = $db->toSql($limit_data["field"]["name"]);
				}

				$db->query(api_process_sql($service["schema"]["external_field"]["primary"]["table"], $service["schema"]["external_field"]["primary"]["field"], $managed_params["external_field"], $res["fields"]));
				if($db->nextRecord()) {
					do {
        				if(is_array($arrLimitFieldID) && count($arrLimitFieldID) && array_search($db->getField("ID", "Number", true), $arrLimitFieldID) !== false) {
							$key_field = $db->getField("ID", "Number", true);
        				} else {
							$key_field = ffCommon_url_rewrite($db->getField("name", "Text", true));
        				}

        				$arrFormField[$key_field] = $db->record;

						api_extend_field_schema($res["custom"], $arrFormField[$key_field]["type"], $arrFormField[$key_field]["name"]);
					} while($db->nextRecord());

					if(is_array($arrFormField) && count($arrFormField)) {
						foreach($arrFormField AS $arrFormField_key => $arrFormField_value) {
            				if(is_array($sort_field) && count($sort_field) && array_key_exists($arrFormField_key, $sort_field) !== false) {
            					if(strlen($sort))
            						$sort .= ", ";

								$sort .= $sort_field[$arrFormField_key];
            				}

							$sSQL_field .= ", (" . api_process_sql($service["schema"]["external_field"]["storage"]["table"], $service["schema"]["external_field"]["storage"]["field"], $arrFormField_value["ID"], $res["fields"]) . "
											    LIMIT 1
										    ) AS " . $db->tosql($arrFormField_key);
							if(strlen($search)) {
								if(strlen($sSQL_having))
								    $sSQL_having .= " OR ";
								
								$sSQL_having .= " `"  . $arrFormField_key . "` LIKE '%" . $db->toSql($search, "Text", false) . "%' COLLATE utf8_general_ci";
							}
						}
					}
				}
			}

			
			/**
			* SEARCH
			*/
			if(strlen($search) || (is_array($sort_field) && count($sort_field)) || (is_array($params) && count($params))) {
				$arrWhere = array();
				
				$sSQL = "SELECT " . $primary_table . ".* 
        					$sSQL_add_field_empty
        				FROM " . $primary_table . " 
        				LIMIT 1";
				$db->query($sSQL);
				if(is_array($db->fields) && count($db->fields)) {
					if(is_array($params) && count($params)) {
                        $sSQL_Where_params = "";
    					foreach($params AS $param_key => $param_value) {
    						if(array_key_exists($param_key, $db->fields)) {
    							$sSQL_Where_params .= " AND `" . $param_key . "` = " . $db->toSql($param_value);
    						}
    					}
					} 
        			if(strlen($search) || (is_array($sort_field) && count($sort_field))) {
					    foreach($db->fields AS $field_value) {
							if(is_array($sort_field) && count($sort_field)) {
            					if(array_key_exists($field_value->name, $sort_field)) {
            						if(strlen($sort))
            							$sort .= ", ";

									$sort .= $sort_field[$field_value->name];
            					}
							}
					        if(strlen($search)) {
						        $arrWhere[] = " `" . $field_value->name . "` LIKE '%" . $db->toSql(str_replace(" ", "%", $search), "Text", false) . "%' COLLATE utf8_general_ci";
							}
					    }
					    if(count($arrWhere)) {
					    	$sSQL_primary_where .= " AND (" . implode(" OR ", $arrWhere). ")";
					    }
					}
				}
			}

			
			$sSQL = "SELECT $sSQL_primary_field
				        $sSQL_field
				        $sSQL_add_field
				     FROM " . $primary_table . "
				     WHERE 1
				     	$sSQL_primary_where
				     	" . (strlen($limit_data["key"]) && strlen($limit_data["value"]) 
						        ? (!is_numeric($limit_data["value"])
                        			? " AND " . $primary_table . ".`" . $limit_data["key"] . "` LIKE " . $db->toSql("%" . $limit_data["value"] . "%")
                        			: " AND " . $primary_table . ".`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
						        )
						        : "" 
						    ) . "  
					 HAVING 1 " . (strlen($sSQL_having)
							    ? " AND (" . $sSQL_having . ")"
							    : "" 
							) . "
					ORDER BY " . ($sort === null 
				        ? "" 
				        : (strpos($sort, "`") === false && strpos($sort, ".") !== false ? substr($sort, strpos($sort, ".") + 1) : $sort) .  
                			($sort_dir === null ? "" : " " . $sort_dir) . ", "
				    ) . $primary_table . ".ID DESC";  
		}

		return $sSQL;    
    }
    
    
    
    function api_process_sql($table, $fields, $key, $data) {
    	$db = ffDB_Sql::factory();
    		
		$sSQL_Select_params = "";
		if(is_array($fields["select"]) && count($fields["select"])) {
			$arrSelectField = array();
			foreach($fields["select"] AS $field_name => $field_AS) {
				$arrSelectField[] = $field_name . " AS `" . ($field_AS ? $field_AS : $field_name) . "`";
			}
			
			$sSQL_Select_params = implode(", ", $arrSelectField);
		}		
							
		$sSQL_Join_params = "";
		if(is_array($fields["join"]) && count($fields["join"])) {
			$arrJoin = array();
			foreach($fields["join"] AS $join_name => $join_fields) {
				if(is_array($join_fields) && count($join_fields)) {
					$arrJoinField = array();
					foreach($join_fields AS $join_fields_primary => $join_fields_external) {
						$arrJoinField[] = $join_name . "." . $join_fields_primary . " = " . $table . "." . $join_fields_external;
					}
					$arrJoin[] = $join_name . " ON " . implode(" AND ", $arrJoinField);
				}
			}
			if(count($arrJoin))
				$sSQL_Join_params = " INNER JOIN " . implode(" INNER JOIN ", $arrJoin);
		}
		
		$sSQL_Where_params = ""; 
		if(is_array($fields["where"]) && count($fields["where"])) {
			foreach($fields["where"] AS $field_key => $field_where) {
				if(strpos($field_where, "[VALUE]") !== false) {
					if($data[$field_key])
						$sSQL_Where_params .= " AND " . str_replace("[VALUE]", $db->toSql($data[$field_key]), $field_where);
				} elseif(strpos($field_where, "[KEY]") !== false)
					$sSQL_Where_params .= " AND " . str_replace("[KEY]", $db->toSql($key), $field_where);
				else
					$sSQL_Where_params .= " AND " . $field_where;

			}
		}    

		$sSQL_Order_params = ""; 
		if(is_array($fields["order"]) && count($fields["order"])) {
			foreach($fields["order"] AS $field_name => $field_dir) {
				$sSQL_Order_params .= "`" . $field_name . "` " . $field_dir . ", ";
			}
		}
		$sSQL_Order_params .= $table . ".ID DESC";		

		$sSQL = "SELECT $sSQL_Select_params
        		FROM " . $table . "
        			$sSQL_Join_params
        		WHERE 1
        			$sSQL_Where_params
        		ORDER BY $sSQL_Order_params";    

    	return $sSQL;
    }
 function api_extend_field_schema($schema, $extended_type, $field_name) {
  	  switch($extended_type) {
  	  	  case "Image":
  	  	  case "Upload":
  	  	  case "UploadImage":
  	  	  	$schema[$field_name] = "image";
  	  	  	break;
  	  	  case "Date":
  	  	  case "DateCombo":
  	  	  	$schema[$field_name] = "date";
  	  	  default:
  	  }
  }
 function api_process_tags(&$element) {
 	$element = "[" . $element . "]";
 }
