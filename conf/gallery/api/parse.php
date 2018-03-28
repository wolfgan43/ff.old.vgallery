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
 function api_get_field_by_tpl($tpl_field, $service, $sort = array()) {
	$arrTpl = array();
	$allowed_action = array();

	$tpl_field = ltrim($tpl_field, "[");
	$tpl_field = rtrim($tpl_field, "]");
	
	if(strpos($tpl_field, "action.") === 0) {
	    $allowed_action[$tpl_field] = "";
	}

	$skip_concat = false;
	$tpl_parsed_field_value = "";
	
	if(strpos($tpl_field, "::") !== false) {
		$arrAddParams = explode("::", $tpl_field);
		$field_user_params = $arrAddParams[1];
		$db_field_key = $arrAddParams[0];
	} else {
		$field_user_params = "";
		$db_field_key = $tpl_field;
	}
	
	if(strpos($db_field_key, ".") === false) {
	    //field in primary service
	    $tpl_parsed_field_value = "['" . $service["name"] . "']['" . $db_field_key . "']";
		
		if(is_array($sort) && count($sort))
	        $arrTpl[$sort["key"]]["sort"][$db_field_key] = "`" . $db_field_key . "` " . $sort["dir"];

	    if(is_numeric($db_field_key)) { 
	        $arrTpl["limit_field"]["id"][] = $db_field_key;
		} else {
			$arrTpl["limit_field"]["name"][] = "'" . $db_field_key . "'";
		}

		if(strlen($field_user_params)) {
            switch($service["schema"]["field"][$db_field_key]["type"]) {
                case "image":
                    $service["schema"]["field"][$db_field_key]["thumb"] = $field_user_params;
                    break;
                default:
                    $service["schema"]["field"][$db_field_key]["user_params"] = $field_user_params;
            }
		}
	} else {
		if(substr_count($db_field_key, ".") != strlen($db_field_key)) {
		    if(array_search(substr($db_field_key, 0, strpos($db_field_key, ".")), array_keys($service["schema"]["relationship"])) === false) {
		        //field in primary service
		        if(strpos($db_field_key, $service["name"] . ".") === false) {
			        $tpl_field_key = $db_field_key;
			        if(substr_count($db_field_key, ".") > 1) {
		                $arrTplFieldKey = explode(".", $db_field_key);
						$tpl_parsed_field_value = '$service_schema[' . $arrTplFieldKey[0] . "][" . $arrTplFieldKey[1] . "]";
						unset($arrTplFieldKey[0]);
						unset($arrTplFieldKey[1]);
						$tpl_parsed_field_value = $tpl_parsed_field_value . "|" . implode("|", $arrTplFieldKey);
			        } else {
						$tpl_parsed_field_value = '$service_schema[' . str_replace(".", "][", $db_field_key) . "]";	
			        }
				} else {
			        $tpl_field_key = substr($db_field_key, strpos($db_field_key, $service["name"] . ".") + strlen($service["name"] . "."));
				    $tpl_parsed_field_value = "['" . $service["name"] . "']['" . str_replace(".", "']['", $tpl_field_key) . "']";
			        
			        if(strpos($tpl_field_key, ".") !== false) {
			            $chek_tpl_field_key = substr($tpl_field_key, 0, strpos($tpl_field_key, "."));
			            if(array_search($chek_tpl_field_key, array_keys($service["schema"]["relationship"])) !== false
			                && strpos($tpl_field_key, $service["name"] . ".") === false
			                && array_search($chek_tpl_field_key, $service["internal"]) === false
			            )  {
			                //field in secondary service
			                $service["internal"][] = $chek_tpl_field_key;
			                $tpl_field_key = substr($tpl_field_key, strpos($tpl_field_key, $chek_tpl_field_key . ".") + strlen($chek_tpl_field_key . "."));
						    $tpl_parsed_field_value = "['" . $chek_tpl_field_key . "']['" . str_replace(".", "']['", $tpl_field_key) . "']";
			            } else {
							if(is_array($sort) && count($sort))
								$arrTpl[$sort["key"]]["sort"][$tpl_field_key] = "`" . $tpl_field_key . "` " . $sort["dir"];

							$arrTmp_tpl_field_key = explode(".", $tpl_field_key);
						    if(is_numeric(end($arrTmp_tpl_field_key))) { 
                        		$arrTpl["limit_field"]["id"][] = $tpl_field_key;
							} else {
								$arrTpl["limit_field"]["name"][] = "'" . $tpl_field_key . "'";
							}

							if(strlen($field_user_params)) {
                                switch($service["schema"]["field"][$tpl_field_key]["type"]) {
                                    case "image":
                                        $service["schema"]["field"][$tpl_field_key]["thumb"] = $field_user_params;
                                        break;
                                    default:
                                        $service["schema"]["field"][$tpl_field_key]["user_params"] = $field_user_params;
                                }
							}
			            }
			        } else {
			            if(is_array($sort) && count($sort))
							$arrTpl[$sort["key"]]["sort"][$tpl_field_key] = "`" . $tpl_field_key . "` " . $sort["dir"];

						if(is_numeric($tpl_field_key)) { 
                        	$arrTpl["limit_field"]["id"][] = $tpl_field_key;
						} else {
							$arrTpl["limit_field"]["name"][] = "'" . $tpl_field_key . "'";
						}

						if(strlen($field_user_params)) {
                            switch($service["schema"]["field"][$tpl_field_key]["type"]) {
                                case "image":
                                    $service["schema"]["field"][$tpl_field_key]["thumb"] = $field_user_params;
                                    break;
                                default:
                                    $service["schema"]["field"][$tpl_field_key]["user_params"] = $field_user_params;
                            }
						}
			        }
				}
		    } else {
		        $tpl_parsed_field_value = "['" . str_replace(".", "']['", $db_field_key) . "']";
		        if(array_search(substr($db_field_key, 0, strpos($db_field_key, ".")), $service["internal"]) === false)  {
		            //field in secondary service
		            $chek_tpl_field_key = substr($db_field_key, 0, strpos($db_field_key, "."));
		            
		            $service["internal"][] = $chek_tpl_field_key;
		        }
		        //continue;
		    }
		} else {
			$tpl_parsed_field_value = "";
		}
	}
	if(is_array($sort) && count($sort))
	    $arrTpl[$sort["key"]]["field"][$tpl_field] = $tpl_parsed_field_value;

	return array(
    	"tpl" => $arrTpl
    	, "allowed_action" => $allowed_action
    	, "schema" => $service["schema"]
    	, "internal" => $service["internal"]
	); 
}
	
function api_parse_field_by_type($field, $data, $data_key = "", $exclude_tag = false, $module = null) {
    if($data === null) {
        $data = "";
    }

    if(is_array($field)) {
	    if(array_key_exists("type", $field)) {
		    switch($field["type"]) {
		        case "image":
                    if(array_key_exists("thumb", $field)) {
                        $field_thumb = $field["thumb"];
                    }
		            if(strlen($data)
		                && (substr(strtolower($data), 0, 7) == "http://"
		                    || substr(strtolower($data), 0, 8) == "https://"
                            || substr($data, 0, 2) == "//"
		                )
		            ) {
		                $res = $data;
		            } else if(strlen($data) && file_exists(DISK_UPDIR . $data) && is_file(DISK_UPDIR . $data)) { 
		                $res = cm_showfiles_get_abs_url((strlen($field_thumb) ? "/" . $field_thumb : "") . $data);
		            } else {
		                $res = cm_showfiles_get_abs_url("/" . CM_DEFAULT_THEME . "/images/spacer.gif");
		            }

		            if(!$exclude_tag) {
		                $res = '<img src="' . $res . '" alt="' . ffCommon_url_rewrite(ffGetFilename($res), " ") . '" />';
		            }
		            break;
		        case "timestamp":
        			if($data > 0) {
        				$res = new ffData($data, "Timestamp", FF_SYSTEM_LOCALE);
        				$res = $res->getValue("Date", FF_LOCALE);
					} else {
						$res = "";
					}
        			break;
		        case "date":
        			if($data > 0) {
        				$res = new ffData($data, "Date", FF_SYSTEM_LOCALE);
        				$res = $res->getValue("Date", FF_LOCALE);
					} else {
						$res = "";
					}
        			break;
		        case "currency":
        			$res = new ffData($data, "Number", FF_SYSTEM_LOCALE);
        			$res = $res->getValue("Currency", FF_LOCALE);
        			break;
		        case "boolean";
		            if($data) {
		                $res = 1;
		            } else {
		                $res = 0;
		            }
		            break;
		        default:
		            if(strpos($field["type"], "-to-") !== false) {
		                $arrDefSource = explode("-to-", $field["type"]);
		                if(strpos($field["type"], "image-") !== false) {
		                
		                } elseif(strpos($field["type"], "timestamp-") !== false) {
		                    $arrDef = array(
		                        "datetime" => "d/m/Y H:i"
		                        , "time" => "H:i"
		                        , "mmss" => "i' s''" 
		                        , "hhmmss" => array("G" => "[RES] - 1", " i' s''")
		                    );
							$real_def_source = explode(":", $arrDefSource[1]);
		                    if(array_key_exists($real_def_source[0], $arrDef)) {
	                    		if(is_array($arrDef[$real_def_source[0]]) && count($arrDef[$real_def_source[0]])) {
									$res = "";
									foreach($arrDef[$real_def_source[0]] AS $arrDef_key => $arrDef_value) {
										if(!is_numeric($arrDef_key) && strlen($arrDef_value)) {
											eval('$res .= ' . str_replace("[RES]", date($arrDef_key, $data), $arrDef_value) . ";");
										} else {
											 $res .= date($arrDef_value, $data);
										}
									}
	                    		} else {
	                        		$res = date($arrDef[$real_def_source[0]], $data);
								}
		                    } else {
		                        $res = date("H:i:s", $data); 
		                    }
		                } elseif(strpos($field["type"], "file-") !== false) {
		                    $arrDef = array(
		                        "basename" => '$res = basename($data);'
		                        , "dirname" => '$res = ffCommon_dirname($data);'
		                    );
							$real_def_source = explode(":", $arrDefSource[1]);
		                    if(array_key_exists($real_def_source[0], $arrDef)) {
		                        eval($arrDef[$real_def_source[0]]);
		                    } else {
		                        $res = $data;
		                    }
			                if(is_file(DISK_UPDIR . $data)) {
								if(isset($real_def_source) && $real_def_source[1] == "nolink") {
			                        $res = '<span class="found">' . $res . '</span>';
								} else {
			                        $res = '<a href="' . cm_showfiles_get_abs_url($data) . '" target="_blank">' . $res . '</a>';    
			                    }
							}
		                } else {
		                    $arrDef = array(
		                        "comma" => ","
		                        , "dot" => "."
		                        , "space" => " "
		                        , "div" => "div"
		                        , "span" => "span"
		                    );
		                    if(array_key_exists($arrDefSource[0], $arrDef)) {
		                        $arrRes = explode($arrDef[$arrDefSource[0]], $data);
		                        if(is_array($arrRes) && count($arrRes)) {
									$res = "";
		                            foreach($arrRes AS $arrRes_key => $arrRes_value) {
		                                if(strlen($arrRes_value)) {
		                                    $res .= '<' . $arrDef[$arrDefSource[1]] . ($data_key ? ' class="' . ffCommon_url_rewrite($data_key) . '"' : '') . '>' . ffTemplate::_get_word_by_code($arrRes_value) . "</" . $arrDef[$arrDefSource[1]] . ">";
		                                }
		                            }
		                        }
		                    } else {
		                        $res = $data;
		                    }
		                }
		            }
			}
		} else {
			$res = $data;
		}
		if(strlen($res)) {
			if(array_key_exists("permissions", $field)) {
				if(is_array($field["permissions"]) && count($field["permissions"])) {
					$user_permission = get_session("user_permission");
					
					foreach($field["permissions"] AS $group_key => $func_name) {
						if(array_key_exists($group_key, $user_permission["groups"])) {
							switch($func_name) {
								case "censor":
									$res = substr($res, 0, 1) . str_repeat("*", strlen($res) - 2 ) . substr($res, -1);
									break;
								default:
							}
							
						}
					}
				}
			}
		}
	} else {
		$res = $data;
	}
    return $res;
}
 
  function api_parse_action($srv_id, $service_schema, $service_name, $schema, $field, $service_path_info, $str_param_url = "", $limit_data = null, $return = null, $bt_ret = false) {
        
		/*
	    $schema["action"]["addnew"] = array("path" => "modify"
    									, "action" => "insert"
    									, "params" => array()
    								);
	    $schema["action"]["edit"] = array("ID" => "keys[anagraph-ID]"
    									, "ID_value" => "[ID]"
    									, "path" => "modify"
    									, "action" => "update"
    									, "params" => array()
    								);
	    $schema["action"]["delete"] = array("ID" => "keys[anagraph-ID]"
    									, "ID_value" => "[ID]"
    									, "path" => "modify"
    									, "action" => "delete"
    									, "component" => null
    									, "params" => array()
    								);
	    $schema["action"]["visible"] = array("ID" => "keys[anagraph-ID]"
    									, "ID_value" => "[ID]"
    									, "path" => "modify"
    									, "action" => "update"
    									, "component" => null
    									, "params" => array("setvisible" => array("value" => "[visible]"
    																				, "type" => "NOT"
    																				, "extended_type" => "Boolean" //non gestito
    																			)
    									)
    								);		
		*/

		$permission_action = "";
		$schema_default = $service_schema["action"]["default"];
		if(!is_array($schema_default))
			$schema_default = array();
		
		if(!is_array($schema)) {
			switch($schema) {
				case "addnew":
					$schema = array("action" => "insert");
					break;
				case "edit":
					$schema = array("action" => "update");
					break;
				case "delete":
					$schema = array("action" => "delete"
									, "component" => null
									, "source_action" => "confirmdelete"
								);
					break;
				default:
					$permission_action = $schema;
					$schema = array("action" => "update"
									, "source_action" => $schema);
					break;
			}
		} else {
			if(array_key_exists("real_action", $schema)) {
				$permission_action = $schema["real_action"];
			}
		}

		$prototype["action"] = $schema["action"];
		if(array_key_exists("source_action", $schema))
			$prototype["source_action"] = $schema["source_action"];

		if(array_key_exists("component", $schema)) {
			if($schema["component"] === null) {
				$prototype["component"] = str_replace(" " , "", ucwords(str_replace("_", " ", $service_name))) . "Modify";
			} elseif(strlen($schema["component"])) {
				$prototype["component"] = $schema["component"];
			}
			
		}

		switch($prototype["action"]) {
			case "update":
				if(!strlen($prototype["class"]))
					$prototype["class"] = "edit";
				
				//Permission
				if(!strlen($permission_action))
					$permission_action = "modify";
			case "delete":
				if(!strlen($prototype["class"]))
					$prototype["class"] = "delete";
					
				$prototype["ID"] = (array_key_exists("ID", $schema) && strlen($schema["ID"])
									? $schema["ID"]
									: (array_key_exists("ID", $schema_default)
										? $schema_default["ID"]
										: "keys[ID]"
									)
						);
				$prototype["ID_value"] = (array_key_exists("ID_value", $schema)
									? $schema["ID_value"]
									: (array_key_exists("ID_value", $schema_default)
										? $schema_default["ID_value"]
										: "[ID]"
									)
						);
				
				//Permission
				if(!strlen($permission_action))
					$permission_action = "delete";
			case "insert":
				if(!strlen($prototype["class"]))
					$prototype["class"] = "addnew";

				$prototype["path"] = api_parse_action_tag(array_key_exists("path", $schema)
									? $schema["path"]
									: (array_key_exists("source_action", $prototype)
										? ($prototype["source_action"] != "confirmdelete"
                                            ? $prototype["source_action"]
                                            : "modify"
                                        )
										: (array_key_exists("path", $schema_default)
											? $schema_default["path"]
											: "modify"
										)
									)
						, $service_path_info);
				$prototype["params"] = (array_key_exists("params", $schema) && is_array($schema["params"])
									? $schema["params"]
									: array()
						);
				
				//Permission
				if(!strlen($permission_action))
					$permission_action = "addnew";
			default:
				if(!strlen($prototype["class"]))
					$prototype["class"] = (array_key_exists("class", $schema_default)
											? $schema_default["class"]
											: "custom"
										);

				$prototype["url"] = (array_key_exists("url", $schema)
									? $schema["url"]
									: (array_key_exists("url", $schema_default)
										? $schema_default["url"]
										: null
									)
						);
				$prototype["js"] = (array_key_exists("js", $schema)
									? $schema["js"]
									: (array_key_exists("js", $schema_default)
										? $schema_default["js"]
										: null
									)
						);
				$prototype["html"] = (array_key_exists("html", $schema)
									? $schema["html"]
									: (array_key_exists("html", $schema_default)
										? $schema_default["html"]
										: null
									)
						);
				
				//Permission
				if(!strlen($permission_action))
					$permission_action = $prototype["action"];
		}

		$user_permission = get_session("user_permission");
		if(array_key_exists("permissions_service", $user_permission)) {
			if(strlen($permission_action)) {
				if(array_key_exists("AREA_" . strtoupper($service_name) . "_SHOW_" . strtoupper($permission_action), $user_permission["permissions_service"])) {
					$button_permission["specific"] = $user_permission["permissions_service"]["AREA_" . strtoupper($service_name) . "_SHOW_" . strtoupper($permission_action)];
				} elseif(array_key_exists("AREA_SHOW_" . strtoupper($service_name), $user_permission["permissions_service"])) {
					$button_permission["default"] = $user_permission["permissions_service"]["AREA_SHOW_" . strtoupper($service_name)];
				}
			} else {
				if(array_key_exists("AREA_SHOW_" . strtoupper($service_name), $user_permission["permissions_service"])) {
					$button_permission["default"] = $user_permission["permissions_service"]["AREA_SHOW_" . strtoupper($service_name)];
				}
			}
		} elseif(array_key_exists("permissions", $user_permission)) {
			if(strlen($permission_action)) {
				if(array_key_exists("AREA_" . strtoupper($service_name) . "_SHOW_" . strtoupper($permission_action), $user_permission["permissions"])) {
					$button_permission["specific"] = $user_permission["permissions"]["AREA_" . strtoupper($service_name) . "_SHOW_" . strtoupper($permission_action)];
				} elseif(array_key_exists("AREA_SHOW_" . strtoupper($service_name), $user_permission["permissions"])) {
					$button_permission["default"] = $user_permission["permissions"]["AREA_SHOW_" . strtoupper($service_name)];
				}
			} else {
				if(array_key_exists("AREA_SHOW_" . strtoupper($service_name), $user_permission["permissions"])) {
					$button_permission["default"] = $user_permission["permissions"]["AREA_SHOW_" . strtoupper($service_name)];
				}
			}
		} else {
			$button_permission = null;
		}

		$enable_button = true;
		if(is_array($button_permission)) {
			if(array_key_exists("specific", $button_permission)) {
				$enable_button = $button_permission["specific"];
			} elseif(array_key_exists("default", $button_permission)) {
				$enable_button = $button_permission["default"];
			}
		}
		
		if($prototype["url"] === null) {
			if(array_key_exists("ID_value", $prototype)) {
				if(strlen($prototype["ID_value"])) {
				    $res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $prototype["ID_value"], $schema_tags);
				    if(is_array($schema_tags) && count($schema_tags)) {
				        foreach($schema_tags[0] AS  $schema_tags_key => $schema_tags_value) {
	            			if(array_key_exists($schema_tags[1][$schema_tags_key], $field)) {
								$prototype["ID_value"] = str_replace($schema_tags[0][$schema_tags_key], $field[$schema_tags[1][$schema_tags_key]], $prototype["ID_value"]);
	            			}
						}
					}
				}
			}

			if(array_key_exists("params", $schema) && is_array($prototype["params"]) && count($prototype["params"])) {
				foreach($prototype["params"] AS $params_key => $params_value) {
				    if(is_array($prototype["params"][$params_key])) {
				        if(array_key_exists("value", $prototype["params"][$params_key])
				        	&& strlen($prototype["params"][$params_key]["value"])
				        ) {
						    $res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $prototype["params"][$params_key]["value"], $schema_tags);
						    if(is_array($schema_tags) && count($schema_tags)) {
						    	$str_params_url = "";
						        foreach($schema_tags[0] AS  $schema_tags_key => $schema_tags_value) {
	            					if(array_key_exists($schema_tags[1][$schema_tags_key], $field)) {
										$prototype["params"][$params_key]["value"] = str_replace($schema_tags[0][$schema_tags_key], $field[$schema_tags[1][$schema_tags_key]], $prototype["params"][$params_key]["value"]);
	            					} else {
										$prototype["params"][$params_key]["value"] = str_replace($schema_tags[0][$schema_tags_key], "", $prototype["params"][$params_key]["value"]);
	            					}
	            					
	            					if(array_key_exists("type", $prototype["params"][$params_key])) {
										switch($prototype["params"][$params_key]["type"]) {
											case "NOT":
												$prototype["params"][$params_key]["value"] = !$prototype["params"][$params_key]["value"];
												break;
											default:
										}											
	            					}

	            					if(strlen($str_params_url))
	            						$str_params_url .= "&";

	            					$str_params_url .= $params_key . "=" . urlencode($prototype["params"][$params_key]["value"]);
								}
							}
				        }
				    } else if(strlen($params_value)) {
					    $res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $params_value, $schema_tags);
					    if(is_array($schema_tags) && count($schema_tags)) {
					    	$str_params_url = "";
					        foreach($schema_tags[0] AS  $schema_tags_key => $schema_tags_value) {
	            				if(array_key_exists($schema_tags[1][$schema_tags_key], $field)) {
									$prototype["params"][$params_key] = str_replace($schema_tags[0][$schema_tags_key], $field[$schema_tags[1][$schema_tags_key]], $prototype["params"][$params_key]);
	            				} else {
									$prototype["params"][$params_key] = str_replace($schema_tags[0][$schema_tags_key], "", $prototype["params"][$params_key]);
	            				}

	            				if(strlen($str_params_url))
	            					$str_params_url .= "&";

	            				$str_params_url .= $params_key . "=" . urlencode($prototype["params"][$params_key]);
							}
						}
					}
				}
			}

			$url = @parse_url($_SERVER["HTTP_REFERER"]);
			$prototype["url"] = $prototype["path"] 
						. "?"
						. ($prototype["ID"]
							? $prototype["ID"] . "=" . urlencode($prototype["ID_value"] ) . "&" 
							: ""
						)
						. (strlen($str_params_url) > 0
							? $str_params_url . "&"
							: ""
						)
						. ($prototype["component"]  
							? "frmAction=" . $prototype["component"] . "_" . (strlen($prototype["source_action"]) ? $prototype["source_action"] : $prototype["action"]) . "&"
							: ""
						) . (is_array($limit_data) 
								&& array_key_exists("key", $limit_data) && strlen($limit_data["key"])
								&& array_key_exists("value", $limit_data) && strlen($limit_data["value"])
                            ? $limit_data["key"] . "=" . $limit_data["value"]
                            : ""
                        );
						//. (strlen($url["query"]) ? $url["query"] : "");

			$prototype["url"] = trim($prototype["url"], "&");
		} else {
			if(strlen($prototype["url"])) {
				$res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $prototype["url"], $schema_tags);
				if(is_array($schema_tags) && count($schema_tags)) {
				    foreach($schema_tags[0] AS  $schema_tags_key => $schema_tags_value) {
	            		if(array_key_exists($schema_tags[1][$schema_tags_key], $field)) {
							$prototype["url"] = str_replace($schema_tags[0][$schema_tags_key], $field[$schema_tags[1][$schema_tags_key]], $prototype["url"]);
	            		}
					}
				}
			}
		}

        if($bt_ret === false) {
		    if(strpos($return, ".") === false) {
			    $return_key = $return;
			    $return_params = "";
		    } else {
			    $arrReturn = explode(".", $return);
			    $return_key = $arrReturn[0];
			    unset($arrReturn[0]);
			    $return_params = implode(".", $arrReturn);
		    }
        	
		    if(strlen($return_key) && !array_key_exists($return_key, $prototype)) {
			    $return_key = "html";
			    $return_params = "dialog";
		    }
		    if($prototype["source_action"] == "export") {
		    	$str_query_string = "";
				$arrQueryString = explode("&", $_SERVER["QUERY_STRING"]);
				if(is_array($arrQueryString) && count($arrQueryString)) {
					foreach($arrQueryString AS $arrQueryString_value) {
						$tmp_query_string_param = explode("=", $arrQueryString_value);
						if($tmp_query_string_param[0] == "compact"
							|| $tmp_query_string_param[0] == "paction"
							|| strpos($tmp_query_string_param[0], "params") !== false
						) {
							continue;
						}
						if(strlen($arrQueryString_value))
							$arrQueryString_value .= "&";

						$str_query_string .= $arrQueryString_value;
					}
				}
				$prototype["class"] = "export";
				if(!strlen($return_params))
					$return_params = "xls";

				if(strlen($str_query_string))
					$str_query_string = "?" . trim($str_query_string, "&") . "&out=" . $return_params;
				else 
					$str_query_string = "?out=" . $return_params;

				$path_info = $_SERVER["PATH_INFO"];
				if($path_info == "/index")
					$path_info = "/";

				$prototype["url"] = $path_info . $str_query_string;
		    }
		    if($prototype["html"] === null) { //icon ico-' . $prototype["class"]
			    $prototype["html"] = '<a class="' . cm_getClassByFrameworkCss($prototype["class"], "icon") . '" href="javascript:ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . $return_params . '\', \'' . $prototype["source_action"] . '\');"></a>';
		    }
		    
		    if($prototype["js"] === null) {
			    $prototype["js"] = 'ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . $return_params . '\', \'' . $prototype["source_action"] . '\');';
		    }
		    

		    if(strlen($return_key) && array_key_exists($return_key, $prototype)) {
		    } else {
			    return $prototype["html"];
		    }
            if($enable_button) { 
				$ret = $prototype[$return_key];	
			} else {
				switch($return_key) {
					case "js":
						$ret = "void(0);";
						break;
					case "html":
					case "url":
					default:
						$ret = "";
						break;
				}
            }
            return $ret;
        } else {
        	if(is_array($bt_ret) && count($bt_ret)) {
        		if($enable_button) { //icon ico-' . $prototype["class"] . '
        			if(array_key_exists("action." . $prototype["class"] . ".html", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".html"] = '<a class="' . cm_getClassByFrameworkCss($prototype["class"], "icon") . '" href="javascript:ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "" . '\', \'' . $prototype["source_action"] . '\');"></a>';
					if(array_key_exists("action." . $prototype["class"] . ".html.dialog", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".html.dialog"] = '<a class="' . cm_getClassByFrameworkCss($prototype["class"], "icon") . '" href="javascript:ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "dialog" . '\', \'' . $prototype["source_action"] . '\');"></a>';
		            if(array_key_exists("action." . $prototype["class"] . ".html.request", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".html.request"] = '<a class="' . cm_getClassByFrameworkCss($prototype["class"], "icon") . '" href="javascript:ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "request" . '\', \'' . $prototype["source_action"] . '\');"></a>';
		            if(array_key_exists("action." . $prototype["class"] . ".js", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".js"] = 'ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "" . '\', \'' . $prototype["source_action"] . '\');';
		            if(array_key_exists("action." . $prototype["class"] . ".js.dialog", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".js.dialog"] = 'ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "dialog" . '\', \'' . $prototype["source_action"] . '\');';
		            if(array_key_exists("action." . $prototype["class"] . ".js.request", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".js.request"] = 'ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "request" . '\', \'' . $prototype["source_action"] . '\');';
		            if(array_key_exists("action." . $prototype["class"] . ".url", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".url"] = $prototype["url"];
				} else {
		            if(array_key_exists("action." . $prototype["class"] . ".html", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".html"] = '';
		            if(array_key_exists("action." . $prototype["class"] . ".html.dialog", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".html.dialog"] = '';
		            if(array_key_exists("action." . $prototype["class"] . ".html.request", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".html.request"] = '';
		            if(array_key_exists("action." . $prototype["class"] . ".js", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".js"] = 'void(0);';
		            if(array_key_exists("action." . $prototype["class"] . ".js.dialog", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".js.dialog"] = 'void(0);';
		            if(array_key_exists("action." . $prototype["class"] . ".js.request", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".js.request"] = 'void(0);';
		            if(array_key_exists("action." . $prototype["class"] . ".url", $bt_ret))
	            		$ret["action." . $prototype["class"] . ".url"] = "";
				}
        	} else {
        		if($enable_button) {  //icon ico-' . $prototype["class"] . '
		            $ret["action." . $prototype["class"] . ".html"] = '<a class="' . cm_getClassByFrameworkCss($prototype["class"], "icon") . '" href="javascript:ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "" . '\', \'' . $prototype["source_action"] . '\');"></a>';
		            $ret["action." . $prototype["class"] . ".html.dialog"] = '<a class="' . cm_getClassByFrameworkCss($prototype["class"], "icon") . '" href="javascript:ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "dialog" . '\', \'' . $prototype["source_action"] . '\');"></a>';
		            $ret["action." . $prototype["class"] . ".html.request"] = '<a class="' . cm_getClassByFrameworkCss($prototype["class"], "icon") . '" href="javascript:ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "request" . '\', \'' . $prototype["source_action"] . '\');"></a>';
		            $ret["action." . $prototype["class"] . ".js"] = 'ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "" . '\', \'' . $prototype["source_action"] . '\');';
		            $ret["action." . $prototype["class"] . ".js.dialog"] = 'ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "dialog" . '\', \'' . $prototype["source_action"] . '\');';
		            $ret["action." . $prototype["class"] . ".js.request"] = 'ff.tplService.parseAction(\'' . ($srv_id ? $srv_id : $service_name) . '\',\'' . $prototype["url"] . '\', \'' . $str_param_url . '\', \'' . "request" . '\', \'' . $prototype["source_action"] . '\');';
		            $ret["action." . $prototype["class"] . ".url"] = $prototype["url"];
				} else {
		            $ret["action." . $prototype["class"] . ".html"] = '';
		            $ret["action." . $prototype["class"] . ".html.dialog"] = '';
		            $ret["action." . $prototype["class"] . ".html.request"] = '';
		            $ret["action." . $prototype["class"] . ".js"] = 'void(0);';
		            $ret["action." . $prototype["class"] . ".js.dialog"] = 'void(0);';
		            $ret["action." . $prototype["class"] . ".js.request"] = 'void(0);';
		            $ret["action." . $prototype["class"] . ".url"] = "";
				}        	
        	}

            return $ret;
        }
		
	}
	
	
	function api_parse_action_tag($data, $path_info) {
		$data = str_replace("[PATHINFO]", $path_info, $data);
		
		return $data;
	}
 
 
	function api_parse_xls($source, $arrExcludeLabel = array(), $exclude = array(), $actual_label = "") {
		$res = array();

		if(is_array($source) && count($source)) {
			foreach($source AS $key => $value) {
				if(is_array($value) && count($value)) {
					if(!(count($arrExcludeLabel) && array_search($key, $arrExcludeLabel) !== false)) {
						if(strlen($actual_label))
							$actual_label .= "-";

						$actual_label .= $key;
					}

					$res = array_merge($res, api_parse_xls($value, $arrExcludeLabel, $exclude, $actual_label));
				} else {
					if(is_array($exclude) && array_search($key, $exclude) !== false) {
						continue;
					}

					$next_i = count($res);
					$res[$next_i]["key"] = (strlen($actual_label) ? $actual_label . "-" : "") . $key;
					$res[$next_i]["value"] = $value;
				}
			}
		}
		return $res;
	}
  	
  	function parse_xls_normalizeTag($buffer) {
		$res = str_replace("</div>", "</div><br />", $buffer);
		$res = str_replace("</span>", "</span> ", $res);
		$res = str_replace("</label>", "</label> ", $res);
		$res = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $res);

		$res = trim(strip_tags($res), "\n");
		
		return $res;
	}
