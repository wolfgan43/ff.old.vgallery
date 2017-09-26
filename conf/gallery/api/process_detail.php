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

$service_params["detail"] = array(
    "out" => array(
    
    )
);

if(!$disable_service_process) { 
    if(!$internal_service) {
        $php_array = array();
        $total_record = 0;

        $output_type = (isset($_REQUEST["out"])
                    ? $_REQUEST["out"]
                    : $service_output);

	    $tpl_params = (isset($_REQUEST["tpl_params"])
	    				? json_decode($_REQUEST["tpl_params"], true)
	    				: (is_array($parent_schema) && array_key_exists("tpl", $parent_schema)
	    					? $parent_schema["tpl"]
	    					: null
	    				)
	    			);
     	$tpl_fields = (isset($_REQUEST["tpl_fields"])
	    			? json_decode($_REQUEST["tpl_fields"], true)
	    			: (is_array($parent_schema) && array_key_exists("fields", $parent_schema)
	    				? $parent_schema["fields"]
	    				: null
	    			)
	    		);
        $compact = (isset($_REQUEST["compact"])
                    ? $_REQUEST["compact"]
                    : true);

        $service_ext = (isset($_REQUEST["service"]) && strlen($_REQUEST["service"])
                    ? $_REQUEST["service"]
                    : null);
        $srv_id = (isset($_REQUEST["srvid"])
                    ? $_REQUEST["srvid"]
                    : "");
       /*
        switch($output_type) {
            case "datatable":
                $rows = (isset($_REQUEST["iDisplayLength"])
                            ? ($_REQUEST["iDisplayLength"])
                            : 10);
                $page = (isset($_REQUEST["iDisplayStart"])
                            ? ($rows > 0 ? floor(intval($_REQUEST["iDisplayStart"] / $rows)) : 0)
                            : 0);
                $sort = (isset($_REQUEST["iSortCol_0"])
                            ? $_REQUEST["mDataProp_" . $_REQUEST["iSortCol_0"]]
                            : null);
                $sort_dir = (isset($_REQUEST["sSortDir_" . "0"])
                            ? $_REQUEST["sSortDir_" . "0"]
                            : "asc");
                $search = (isset($_REQUEST["sSearch"])
                            ? $_REQUEST["sSearch"]
                            : null);
                break;
            default:
                $page = (isset($_REQUEST["page"])
                            ? $_REQUEST["page"]
                            : 0);
                $rows = (isset($_REQUEST["rows"])
                            ? $_REQUEST["rows"]
                            : 10);
                $sort = (isset($_REQUEST["sort"])
                            ? $_REQUEST["sort"]
                            : null);
                $sort_dir = (isset($_REQUEST["sort_dir"])
                            ? $_REQUEST["sort_dir"]
                            : "asc");
                $search = (isset($_REQUEST["s"])
                            ? $_REQUEST["s"]
                            : null);
        }
        $str_param_url = "page=" . ($page + 1) . "&"
		    		    . "rows=" . $rows . "&"
		    		    . "sort=" . str_replace("cell", "", $sort) . "&"
		    		    . "sortDir=" . $sort_dir . "&"
		    		    . "search=" . $search;
        */
        //custom from service
        if(isset($_REQUEST["params"])) {
            $params = $_REQUEST["params"];
        }

        $limit_data = (isset($_REQUEST["limit"]) && isset($_REQUEST["limit"]["key"]) && isset($_REQUEST["limit"]["value"])
	                    ? $_REQUEST["limit"]
	                    : (basename($service_path_info)
	                    	? array("ID" => basename($service_path_info))
	                    	: null
	                    )
                    );
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

    $arrInternalService = array();
    if($service_ext !== null) {
        $arrInternalService = explode(",", $service_ext);
    }

    $db = ffDb_Sql::factory();

    $exclude_field = array();
    $custom_template = false;
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
    
	$arrTpl = array();
    if(is_array($tpl_params) && count($tpl_params)) {
        $custom_template = true;

        $sSQL_tpl = "";
        $sSQL_having = "";
        foreach($tpl_params AS $tpl_params_key => $tpl_params_value) {
            if(array_key_exists("fieldTpl", $tpl_params_value)) {
            	$res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $tpl_params_value["fieldTpl"], $tpl_tags);
            	$tpl_tags = $tpl_tags[0];
			} else {
				$tpl_tags = $tpl_params_value["fields"];
			}

            if(is_array($tpl_tags) && count($tpl_tags)) {
                foreach($tpl_tags AS  $tpl_tags_key => $tpl_tags_value) {
                    $res = api_get_field_by_tpl($tpl_tags_value
                    								, array("name" => $service_name
                    										, "schema" => $service_schema
                    										, "internal" => $arrInternalService
                    								)
                    								, array("key" => $tpl_params_value["key"]
                    										, "dir" => $sort_dir
                    								)
                    							);

                    $service_schema = $res["schema"];
                    $arrInternalService = $res["internal"];
                    $arrTpl = array_replace_recursive($arrTpl, $res["tpl"]);
                }
            }
			$arrTpl[$tpl_params_value["key"]]["tpl"] = $tpl_params_value["fieldTpl"];
          //  $exclude_field[] = $tpl_params_value["fieldKey"];
        }

		if(is_array($tpl_fields) && count($tpl_fields)) {
			foreach($tpl_fields AS $tpl_fields_value) {
                $res = api_get_field_by_tpl($tpl_fields_value
                    							, array("name" => $service_name
                    									, "schema" => $service_schema
                    									, "internal" => $arrInternalService
                    							)
                    						);

                $service_schema = $res["schema"];
                $arrInternalService = $res["internal"];
                $arrTpl = array_replace_recursive($arrTpl, $res["tpl"]);
			}
		}

		if(is_array($arrTpl["limit_field"]) && count($arrTpl["limit_field"])) {
			if(array_key_exists("id", $arrTpl["limit_field"]) && is_array($arrTpl["limit_field"]["id"])) {
				$limit_data["field"]["id"] = implode(", ", $arrTpl["limit_field"]["id"]);	
			}
			if(array_key_exists("name", $arrTpl["limit_field"]) && is_array($arrTpl["limit_field"]["name"])) {
				$limit_data["field"]["name"] = implode(", ", $arrTpl["limit_field"]["name"]);	
			}
			
			unset($arrTpl["limit_field"]);
		}
				
		if(strlen($sort) && is_array($arrTpl[$sort]["sort"]) && count($arrTpl[$sort]["sort"])) {
			$arrSortField = $arrTpl[$sort]["sort"];
		} else {
			$arrSortField = null;
		}
		
		$sSQL = api_get_query($service, $limit_data, $params, $arrSortField, null, $search);

        //$res_service = call_user_func("service_get_" . $service_name, $limit_data, $params, $arrSortField, null, $search);
        //$sSQL = $res_service["sql"];
		if(strlen($sSQL)) {
	        if(!$compact) {
	            $sSQL_field = ", tbl_src.* ";
	        } else {
	            $sSQL_field = ", tbl_src.ID AS ID ";
	            if(is_array($arrInternalService) && count($arrInternalService)) {
	                foreach($arrInternalService AS $arrInternalService_key => $arrInternalService_value) {
	                    if(strlen($arrInternalService_value) && array_key_exists($arrInternalService_value, $service_schema["relationship"])) {
	                        if(strlen($sSQL_field))
	                            $sSQL_field .= ", ";

	                        $sSQL_field .= " tbl_src." . $service_schema["relationship"][$arrInternalService_value]["rel_key"] . " AS " . $service_schema["relationship"][$arrInternalService_value]["rel_key"];
	                    }
	                }
	            }
	        }

	        /*$sSQL = "SELECT " . $sSQL_tpl 
	                    . $sSQL_field . "
	                FROM (" . $sSQL . " ) AS tbl_src
	                WHERE 1
	                HAVING 1 " . ($search === null
	                    ? ""
	                    : "AND (" . $sSQL_having . ")"
	                ) . "
	                ORDER BY " . ($sort === null 
	                    ? "" 
	                    : (strpos($sort, ".") === false ? $sort : substr($sort, strpos($sort, ".") + 1)) . " " . $sort_dir);*/
		}
    } else {
    
        $sSQL = api_get_query($service, $limit_data, $params, $sort, $sort_dir, $search);
        //$res_service = call_user_func("service_get_" . $service_name, $limit_data, $params, $sort, $sort_dir, $search);
        //$sSQL = $res_service["sql"];
        		
    }

	//execute primary query
    if(strlen($sSQL)) {
    	require_once(__DIR__ . "/parse." . FF_PHP_EXT);
    	
	    $db->query($sSQL);
	    if ($db->nextRecord())
	    {
	        $i = 0;  
	        $total_record = $db->numRows();
    
	        if($internal_service) {
	            if(array_key_exists($service_name, $parent_schema["relationship"])) {
	                $index = $parent_schema["relationship"][$service_name]["value"][$db->getField($parent_schema["relationship"][$service_name]["key"] , "Number", true)];
	            } else {
	                $index = null;
	            }
	        } else {
	            $index = $i;
	        }

	        /*if($index === null)
	            continue;*/

	        $service_schema["ID"][$db->getField("ID", "Number", true)] = $i;
	        if(array_key_exists("relationship", $service_schema) && is_array($service_schema["relationship"]) && count($service_schema["relationship"])) {
		        foreach($service_schema["relationship"] AS $relationship_key => $relationship_value) {
		            $service_schema["relationship"][$relationship_key]["value"][$db->getField($service_schema["relationship"][$relationship_key]["rel_key"], "Number", true)][] = $i;
		        }
			}
	        if(is_array($db->fields) && count($db->fields)) {  
	            $tmp_php_array = array();

				if(is_array($service["prototype"]))	{
					$tmp_php_array = $service["prototype"];
					array_walk_recursive($tmp_php_array, function(&$value, $key) use(&$db, $service_schema) {
						if($db->record[$key]) {
							if($value === null)
					 			$value = api_parse_field_by_type($service_schema["field"][$key], $db->record[$key], $key);
					 	} elseif($value && isset($db->record[$value])) {
					 		$value = api_parse_field_by_type($service_schema["field"][$value], $db->record[$value], $value);
					 	}
			        });
				} else {
		            foreach($db->fields AS $field_data) {
		                $field_value = api_parse_field_by_type($service_schema["field"][$field_data->name], $db->getField($field_data->name, "Text", true), $field_data->name, $custom_template); 
			            if(strpos($field_data->name, ".") !== false) {
			                $str_sub_field = "";
			                $arrSubField = explode(".", $field_data->name);

			                if(is_array($arrSubField) && count($arrSubField)) {
			                    foreach($arrSubField AS $arrSubField_value) {
			                        if(strlen($arrSubField_value)) {
			                            $str_sub_field .= '["' . $arrSubField_value . '"]';
			                        }
			                    }
			                }
			                if(strlen($str_sub_field)) {
			                    if($internal_service) {
			                        if($parent_schema["relationship"][$service_name]["multi"]) {
			                            $str_sub_field = '$tmp_php_array["' . $service_name . '"][$i]' . $str_sub_field . ' = $field_value;';
			                        } else {
			                            $str_sub_field = '$tmp_php_array["' . $service_name . '"]' . $str_sub_field . ' = $field_value;';
			                        }
			                    } else {
			                        if(array_search($field_data->name, $exclude_field) === false) {
			                            $str_sub_field = '$tmp_php_array["' . $service_name . '"]' . $str_sub_field . ' = $field_value;';
			                        } else {
			                            $str_sub_field = '$tmp_php_array' . $str_sub_field . ' = $field_value;';
			                        }
			                    }
			                    eval($str_sub_field);
			                }
			            } else {
			                if(array_search($field_data->name, $exclude_field) === false) {
			                    if($internal_service) {
			                        if($parent_schema["relationship"][$service_name]["multi"]) {
			                            $tmp_php_array[$service_name][$i][$field_data->name] = $field_value;
			                        } else {
			                            $tmp_php_array[$service_name][$field_data->name] = $field_value;
			                        }
			                    } else {
			                        $tmp_php_array[$service_name][$field_data->name] = $field_value;
			                    }
			                } else {
			                    if($internal_service) {
			                        if($parent_schema["relationship"][$service_name]["multi"]) {
			                            $tmp_php_array[$service_name][$i][$field_data->name] = $field_value;
			                        } else {
			                            $tmp_php_array[$service_name][$field_data->name] = $field_value;
			                        }
			                    } else {
			                        $tmp_php_array[$field_data->name] = $field_value;
			                    }
			                }
			            }
		            }
				}
					            
	            if($internal_service && is_array($index)) {
	                foreach($index AS $index_value) {
	                    if(array_key_exists($index_value, $php_array)) {
	                        if($parent_schema["relationship"][$service_name]["multi"] && array_key_exists($service_name, $php_array[$index_value])) {
	                            $php_array[$index_value][$service_name] = array_merge($php_array[$index_value][$service_name], $tmp_php_array[$service_name]);
	                        } else {
	                            $php_array[$index_value] = array_merge($php_array[$index_value], $tmp_php_array);
	                        }
	                    }
	                }
	            } else {
	                if($internal_service) {
	                    if(array_key_exists($index, $php_array)) {
	                        if($parent_schema["relationship"][$service_name]["multi"] && array_key_exists($service_name, $php_array[$index])) {
	                            $php_array[$index][$service_name] = array_merge($php_array[$index][$service_name], $tmp_php_array[$service_name]);
	                        } else {
	                            $php_array[$index] = array_merge($php_array[$index], $tmp_php_array);
	                        }
	                    }
	                } else {
	                    $php_array[$index] = $tmp_php_array;
	                }
	            }
	        }
	    }

	    if(is_array($arrInternalService) && count($arrInternalService)) {
	        foreach($arrInternalService AS $arrInternalService_value) {
	            if(strpos($arrInternalService_value, ".") === false) {
	                $arrTmpService[0] = $arrInternalService_value;
	                $arrTmpService[1] = "list";
	            } else {
	                $arrTmpService = explode(".", $arrInternalService_value);
	            }
	            /*
	            $real_service_path = "";
	            if(is_array($service_module) && count($service_module)) {
					foreach($service_module AS $service_module_value) {
						if(file_exists(FF_DISK_PATH . "/modules/" . $service_module_value . "/library/service/get_" .  $arrTmpService[0] . "." . FF_PHP_EXT)) {
							$real_service_path = "/modules/" . $service_module_value . "/" . $arrTmpService[0] . "/" . $arrTmpService[1];
							break;
						}
					}
	            }
				if(!strlen($real_service_path)) {
					$real_service_path = "/" . $arrTmpService[0] . "/" . $arrTmpService[1];
				}

				$php_array = api_get_service($php_array, $service_schema, $real_service_path);*/
				
				$php_array = api_get_service($php_array, $service_schema, "/" . $arrTmpService[0] . "/" . $arrTmpService[1]);
	        }
	    }
	}

    if(!$internal_service) { 
	    if(is_array($php_array) && count($php_array)) { 
		    foreach($php_array AS $php_array_key => $php_array_value) {  
			    if(is_array($arrTpl) && count($arrTpl)) {
				    foreach($arrTpl AS $arrTpl_key => $arrTpl_value) {
				    	$arrFinalParams[$arrTpl_key] = "";
					    $arrFinal[$php_array_key][$arrTpl_key] = $arrTpl_value["tpl"];
					    if(is_array($arrTpl_value["field"]) && count($arrTpl_value["field"])) {
						    foreach($arrTpl_value["field"] AS $field_key => $field_value) {
							    if(strlen($field_value)) {
								    $returnSource = null;
								    if(substr($field_value, 0, 1) == '$') {
									    if(strpos($field_value, "|") === false) {
										    eval('$source = ' . $field_value . ";");

										    if(!is_array($source)) {
											    $res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $field_value, $source_tags);
											    if(is_array($source_tags[1]) && count($source_tags[1])) { 
												    $source = end($source_tags[1]);
											    }
										    }
									    } else {
										    $arrSource = explode("|", $field_value);
										    eval('$source = ' . $arrSource[0] . ";");

										    if(!is_array($source)) {
											    $res = preg_match_all("/\[([\w\{\}\.\:\=\-\|]+)\]/U", $arrSource[0], $source_tags);
											    if(is_array($source_tags[1]) && count($source_tags[1])) { 
												    $source = end($source_tags[1]);
											    }
										    }

										    unset($arrSource[0]);
										    $returnSource = implode(".", $arrSource);
									    }
									    
									    if((is_array($source) || strlen($source))) {
										    $arrFinalParams[$arrTpl_key][$field_key] = api_parse_action($srv_id, $service_schema, $service_name, $source, $php_array_value[$service_name], $service_path_info, $str_param_url, $limit_data, $returnSource);
										    $arrFinal[$php_array_key][$arrTpl_key] = str_replace("[" . $field_key . "]", $arrFinalParams[$arrTpl_key], $arrFinal[$php_array_key][$arrTpl_key]);
									    } else {
                                            if($compact) {
                                            	$arrFinalParams[$arrTpl_key][$field_key] = "";
										        $arrFinal[$php_array_key][$arrTpl_key] = str_replace("[" . $field_key . "]", "", $arrFinal[$php_array_key][$arrTpl_key]);	
                                            }
									    }
								    } else {
									    eval('$source = $php_array_value' . $field_value . ";");
                                        if($compact || strlen($source)) {
                                        	$arrFinalParams[$arrTpl_key][$field_key] = $source;
									        $arrFinal[$php_array_key][$arrTpl_key] = str_replace("[" . $field_key . "]", $source, $arrFinal[$php_array_key][$arrTpl_key]);	
                                        }
								    }
							    }
						    }
					    }
				    }
			    }
		    }
	    }

	    if(!is_array($arrFinal))
		    $arrFinal = $php_array;

	    //$arrFinal = array_msort($arrFinal, array($sort=> ($sort_dir == "asc" ? SORT_ASC : SORT_DESC)));
	    $arrFinal = (array_key_exists("0", $arrFinal) && is_array($arrFinal[0]) ? $arrFinal[0] : array());
	    $php_array = (array_key_exists("0", $php_array) && is_array($php_array[0]) ? $php_array[0] : array());

        switch($output_type) {
            case "api":
            	$return = (array_key_exists($service_name, $arrFinal) ? $arrFinal[$service_name] : $arrFinal);
                break;
            case "service":
            	$return = $php_array;
                break;
            case "xls":
        	    if(check_function("class.PHPexcel")) {
				    $objPHPExcel = new PHPExcel();
				    
				    $objPHPExcel->getActiveSheet()->setTitle($service_name);

				    $arrParseData = service_parse_xls($arrFinal, array($service_name, "custom"), $service_schema["export"]["exclude"]);
				    if(is_array($arrParseData) && count($arrParseData)) {
					    $col = 0;
					    foreach($arrParseData AS $arrParseData_key => $arrParseData_value) {
						    if(strpos($arrParseData_value["key"], "_") !== false) {
							    $str_field_label = substr($arrParseData_value["key"], 0, strrpos($arrParseData_value["key"], "_"));
						    } else {
							    $str_field_label = $arrParseData_value["key"];
						    }
						    $str_field_label = ffTemplate::_get_word_by_code($str_field_label);
						    
						    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(ffCommon_colNumber2Letter($col) . 1, parse_xls_normalizeTag($str_field_label));
						    $objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . 1)->getAlignment()->setWrapText(true);
						    $objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . 1)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

						    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(ffCommon_colNumber2Letter($col) . 2, parse_xls_normalizeTag($arrParseData_value["value"]));
						    $objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . 2)->getAlignment()->setWrapText(true);
						    $objPHPExcel->setActiveSheetIndex(0)->getStyle(ffCommon_colNumber2Letter($col) . 2)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

						    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(ffCommon_colNumber2Letter($col))->setAutoSize(true);

						    $col++;
					    }
				    }

				    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

				    $filename = ffCommon_url_rewrite($service_name);

				    header('Content-Type: application/vnd.ms-excel');
				    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
				    header('Cache-Control: max-age=0');
		    		http_response_code((strlen($response_code) ? $response_code : null));

				    $objWriter->save('php://output');
				    exit;
        	    }
        	    break;
            default:
                $return = array(
                        /*"tpl" => $arrFinal
                        ,*/ "obj" => $arrFinalParams
                        , "count" => $total_record
                    );

                if(!$compact) {
                    $return["data"] = array();
                    
                    if(!$compact) {
                        $return["data"] = array_merge($return["data"], $php_array);
                    }
                }
                
		    	if($parent_schema === null) {
		    		http_response_code((strlen($response_code) ? $response_code : null));

               		echo ffCommon_jsonenc($return, true);
               		exit;
				} else {
					$return[$service_name . "-detail"][str_replace('"', '\\"', $parent_schema["instance"])] = $return;
				}
        }
       // exit;
    }

    /*
    function array_msort($array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\''.$col.'\'],'.$order.',';
        }
        $eval = substr($eval,0,-1).');';
        eval($eval);
        $ret = array();
        $i = 0;
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
        	    
                $k = substr($k,1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$i][$col] = $array[$k][$col];
                
                $i++;
            }
        }
        $ret = array_combine(range(0, count($ret)-1), $ret);

        return $ret;
    }*/
}
