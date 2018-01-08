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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
require_once(__DIR__ . "/check/common.php");

$params = updater_get_params($cm);

$limit_operation = (isset($_REQUEST["lo"]) && strlen($_REQUEST["lo"])
	? $_REQUEST["lo"]
	: 200
);
$nowarning = (isset($_REQUEST["nowarning"]) && strlen($_REQUEST["nowarning"])
	? $_REQUEST["nowarning"]
	: false
);
if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
	$json = true;
} else {
	$json = $_REQUEST["json"];
}
$execute = $_REQUEST["exec"];

$db = $params["db"];
$realPathInfo = $params["user_path"];

$file_get_contents_master_failed = array();
$file_get_contents_slave_failed = array();

if(class_exists("ffTemplate")) {
	$drop_table_label = ffTemplate::_get_word_by_code("drop_table");
	$need_table_label = ffTemplate::_get_word_by_code("need_create_table");
	
	$delete_label = ffTemplate::_get_word_by_code("delete_record");
	$update_label = ffTemplate::_get_word_by_code("update_record");
	$addnew_label = ffTemplate::_get_word_by_code("create_record");
	
	$wrong_source_data_label = ffTemplate::_get_word_by_code("wrong_source_data");
	$master_server_label = ffTemplate::_get_word_by_code("master_server_same_domain");
    $updater_configuration_label = ffTemplate::_get_word_by_code("updater_not_configurated");
    
    $file_get_contents_master_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_master_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    
    $file_get_contents_slave_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_slave_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    
    $unknown = ffTemplate::_get_word_by_code("unknown");
    $restrictions_in_effect_master = ffTemplate::_get_word_by_code("restrictions_in_effect_master");
    $restrictions_in_effect_slave = ffTemplate::_get_word_by_code("restrictions_in_effect_slave");
} else {
    $drop_table_label = "Drop table";
    $need_table_label = "Need create table";

    $delete_label = "Delete file";
    $update_label = "Update file";
    $addnew_label = "Addnew file";

    $wrong_source_data_label = "Wrong source_data";
    $master_server_label = "Master server same domain";
    $updater_configuration_label = "Updater not configurated";

    $file_get_contents_master_failed["expire_date"] = "Expire Date";
    $file_get_contents_master_failed["different_host"] = "Different Host";
    
    $file_get_contents_slave_failed["expire_date"] = "Expire Date";
    $file_get_contents_slave_failed["different_host"] = "Different Host";
        
    $unknown = "Unknown";
    $restrictions_in_effect_master = "restrictions_in_effect_master";
    $restrictions_in_effect_slave = "restrictions_in_effect_slave";
}
@set_time_limit(0);

$count_operation = 0;
define("LIMIT_OPERATION", $limit_operation);

if(defined("MASTER_SITE") && strlen(MASTER_SITE)) {
    if(MASTER_SITE != DOMAIN_INSET) { 
        $params = explode("/", $realPathInfo);
        $contest = $params[1];
        
        $sync = false;
        if($contest == "sync") 
        {
        	$contest = "sync-" . $params[2];
			$sync = true;
        }   
        
        $target_master_site = MASTER_SITE;
        if($sync)
        {
        	if(defined("PRODUCTION_SITE") && strlen(PRODUCTION_SITE))
        		$target_master_site = PRODUCTION_SITE;
        	elseif(defined("DEVELOPMENT_SITE") && strlen(DEVELOPMENT_SITE))
        		$target_master_site = DEVELOPMENT_SITE;
        }

	    $json_master = @file_get_contents("http://" . $target_master_site . REAL_PATH . "/updater/check/db.php" . ($sync ? "/sync" : "") . "/data?contest=" . urlencode($contest) . "&s=" . urlencode(DOMAIN_INSET));
	    if($json_master === false && strpos($target_master_site, "www.") === false) {
	        $json_master = @file_get_contents("http://www." . $target_master_site . REAL_PATH . "/updater/check/db.php" . ($sync ? "/sync" : "") . "/data?contest=" . urlencode($contest) . "&s=" . urlencode("www." . DOMAIN_INSET));
	    }
        
        if(strlen($json_master))
            $arr_master = json_decode($json_master, true);

        if(is_array($arr_master)) {
			$json_slave = file_post_contents(
				"http://" . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/db.php" . ($sync ? "/sync" : "") . "/data?contest=" . urlencode($contest) . "&s=" . urlencode(DOMAIN_INSET)
				, null
				, (defined("AUTH_USERNAME")
				? AUTH_USERNAME
				: null
			)
				, (defined("AUTH_PASSWORD")
				? AUTH_PASSWORD
				: null
			)
				, "GET"
				, "120"
			);
			if($json_slave === false && strpos(DOMAIN_INSET, "www.") === false) {
				$json_slave = file_post_contents(
					"http://www." . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/db.php" . ($sync ? "/sync" : "") . "/data?contest=" . urlencode($contest) . "&s=" . urlencode("www." . DOMAIN_INSET)
					, null
					, (defined("AUTH_USERNAME")
					? AUTH_USERNAME
					: null
				)
					, (defined("AUTH_PASSWORD")
					? AUTH_PASSWORD
					: null
				)
					, "GET"
					, "120"
				);
			}

            if(strlen($json_slave))
                $arr_slave = json_decode($json_slave, true);

            if(is_array($arr_slave)) {
                $operation = array();
                $operation_top = array();
                $operation_bottom = array();
                require(ffCommon_dirname(__FILE__) . "/check/include_db.php");

                if($contest != "international") {
                    foreach($arr_slave AS $table_key => $table_value) {
                        if(!isset($arr_master[$table_key])) {
                            $sSQL = "DROP TABLE `" . $table_key . "`";
                            $operation_bottom[] = array("data" => $drop_table_label . " " . $table_key, "value" => $sSQL);
                            //elimina la tabella deprecata 
                        } else {
                            if(is_array($arr_slave[$table_key]) && count($arr_slave[$table_key])) {
                                foreach($arr_slave[$table_key] AS $record_key => $record_value) {
                                    if(!isset($arr_master[$table_key][$record_key])) {
                                        $sSQL_field = "";
                                        foreach($arr_slave[$table_key][$record_key] AS $field_key => $field_value) {
                                            if(strlen($sSQL_field))
                                                $sSQL_field .= " AND "; 

                                            $sSQL_field .= " `" . $field_key . "` = " . $db->toSql($field_value, "Text") . " ";
                                        }

										if(array_key_exists("operation", $db_include[$contest][$table_key])) {
											if(array_key_exists("delete", $db_include[$contest][$table_key]["operation"])) {
												if($db_include[$contest][$table_key]["operation"]["delete"]) {
													$allow_operation = true;
												} else {
													$allow_operation = false;
												}
											} else {
												$allow_operation = false;
											}
										} else {
											$allow_operation = true;
										}

										if($allow_operation) {
	                                        $sSQL = "DELETE FROM `" . $table_key . "` WHERE " . $sSQL_field . " ";
	                                        if(count($db_include[$contest][$table_key]["rel"])) {
	                                            $operation_bottom[] = array("data" => $delete_label . " " . $table_key . " (" . $record_key . ")", "value" => $sSQL);
	                                        } else {
	                                            $operation_top[] = array("data" => $delete_label . " " . $table_key . " (" . $record_key . ")", "value" => $sSQL);
	                                        }
										}
                                        //Elimina il campo deprecato
                                    }
                                }
                            }
                        }
                    } 
                    reset($arr_slave);
                }
                foreach($arr_master AS $table_key => $table_value) {
                    if(!isset($arr_slave[$table_key])) {
                        /*if(!$execute) {
	                        $sSQL = "";
	                        $operation_top[] = array("data" => $need_table_label . " " . $table_key, "value" => $sSQL);
						}*/
                        //crea la tabella con tutti i campi nuovi
                    } else {
                        if(is_array($arr_master[$table_key]) && count($arr_master[$table_key])) {
                            foreach($arr_master[$table_key] AS $record_key => $record_value) {
                                if(isset($arr_master[$table_key][$record_key]["ID"])) {
                                    $db_rel[$table_key][$arr_master[$table_key][$record_key]["ID"]] = $arr_master[$table_key][$record_key];
								}	

                                if(!isset($arr_slave[$table_key][$record_key])) {
                                    $sSQL_field_key = "";
                                    $sSQL_field_value = "";
                                    foreach($arr_master[$table_key][$record_key] AS $field_key => $field_value) {
                                        if(strlen($sSQL_field_key))
                                            $sSQL_field_key .= ", "; 

                                        $sSQL_field_key .= " `" . $field_key . "` ";

                                        if(strlen($sSQL_field_value))
                                            $sSQL_field_value .= ", "; 

                                            
                                        if(isset($db_include[$contest][$table_key]["rel"][$field_key])) {
                                            $sSQL_compare = "";
                                            if(is_array($db_rel[$db_include[$contest][$table_key]["rel"][$field_key]][$field_value]) && count($db_rel[$db_include[$contest][$table_key]["rel"][$field_key]][$field_value])) {
                                                foreach($db_rel[$db_include[$contest][$table_key]["rel"][$field_key]][$field_value] AS $compare_key => $compare_value) {
                                                    if($compare_key == "ID" && !array_key_exists($compare_key, $db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["key"])) {
														continue;
                                                    }

                                                    if(count($db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["compare"]) > 0) {
                                                        if(!isset($db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["compare"][$compare_key]) || !$db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["compare"][$compare_key])
                                                            continue;
                                                    } 

                                                    if(strlen($sSQL_compare))
                                                        $sSQL_compare .= " AND "; 

                                                    if(is_array($db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["rel"]) && array_key_exists($compare_key, $db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["rel"])) {
				                                        //functione
				                                        $sSQL_compare .= resolve_rel_data($db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["rel"][$compare_key], $compare_key, $compare_value, $db_include[$contest], $db_rel, $db);
													} else {
                                                        $sSQL_compare .= " `" . $compare_key . "` = " . $db->toSql($compare_value, "Text") . " ";
													}
                                                }
                                                reset($db_rel[$db_include[$contest][$table_key]["rel"][$field_key]][$field_value]);
                                            }

											if(is_array($db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]) && array_key_exists("altKey", $db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]])) {
												$key_rel = $db_include[$contest][$db_include[$contest][$table_key]["rel"][$field_key]]["altKey"];
											} else {
												$key_rel = "ID";											
											}

                                            $sSQL_field_value .= " IFNULL( 
                                            							(
                                            								SELECT IFNULL(`" . $db_include[$contest][$table_key]["rel"][$field_key] . "`.`" . $key_rel . "`, 0) AS ID 
                                                                            FROM `" . $db_include[$contest][$table_key]["rel"][$field_key] . "`
                                                                            WHERE " . (strlen($sSQL_compare) ? $sSQL_compare : $key_rel . " = " . $field_value) . "
                                                                            LIMIT 1
                                            							)
                                                                        , 0
                                            						)";
                                        } elseif($field_key == "ID") {
                                            if($db_include[$contest][$table_key]["key"]["ID"]) {
                                                $sSQL_field_value .= " " . $db->toSql($field_value, "Text") . " ";

		                                        $sSQL = "DELETE FROM `" . $table_key . "` WHERE ID = " . $db->toSql($field_value, "Text") . " ";
		                                        $operation[] = array("data" => $delete_label . " " . $table_key . " (" . $record_key . ")", "value" => $sSQL);
                                            } else {
                                                $sSQL_field_value .= " NULL ";
                                            }
                                        } else {
                                            $sSQL_field_value .= " " . $db->toSql($field_value, "Text") . " ";
                                        }
                                    }
                                    
									if(array_key_exists($table_key, $db_include[$contest])
										&& array_key_exists("operation", $db_include[$contest][$table_key])
									) {
										if(array_key_exists("insert", $db_include[$contest][$table_key]["operation"])) {
											if($db_include[$contest][$table_key]["operation"]["insert"]) {
												$allow_operation = true;
											} else {
												$allow_operation = false;
											}
										} else {
											$allow_operation = false;
										}
									} else {
										$allow_operation = true;
									}

									if($allow_operation) {
	                                    $sSQL = "INSERT INTO `" . $table_key . "` ( " . $sSQL_field_key . " ) VALUES ( " . $sSQL_field_value . " ) ";
	                                    
	                                    if(count($db_include[$contest][$table_key]["rel"])) {
	                                        $operation_bottom[] = array("data" => $addnew_label . " " . $table_key . " (" . $record_key . ")", "value" => $sSQL);
	                                    } else {
	                                        $operation_top[] = array("data" => $addnew_label . " " . $table_key . " (" . $record_key . ")", "value" => $sSQL);
	                                    }
                                    }
                                    //crea il campo nuovo
                                } else {
                                    $exclude_record = false;
                                    if(is_array($db_include[$contest][$table_key]["exclude"]) && count($db_include[$contest][$table_key]["exclude"])) {
                                        foreach($db_include[$contest][$table_key]["exclude"] AS $field_key => $field_value) {
                                            if(isset($arr_slave[$table_key][$record_key][$field_key])) {
                                                if($arr_slave[$table_key][$record_key][$field_key] == $field_value) {
                                                    $exclude_record = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    if($exclude_record)
                                        continue;
                                    
                                    $tmp = array_diff_assoc($arr_master[$table_key][$record_key], $arr_slave[$table_key][$record_key]); 
                                    if(is_array($db_include[$contest][$table_key]["rel"]))
                                    	$tmp = array_diff_key($tmp, $db_include[$contest][$table_key]["rel"]);
                                    
                                    if(count($db_include[$contest][$table_key]["compare"])) {
                                        $tmp = array_intersect_key($tmp, $db_include[$contest][$table_key]["compare"]);
                                    }
                                    if((!isset($tmp["ID"]) && count($tmp) > 0) || (isset($tmp["ID"]) && count($tmp) > 1)) {
                                        $sSQL_field_master = "";
                                        $sSQL_field_slave = "";
                                        foreach($arr_master[$table_key][$record_key] AS $field_key => $field_value) {
											if(count($db_include[$contest][$table_key]["compare"]) > 0) {
                                            	if($db_include[$contest][$table_key]["compare"][$field_key] !== true)
                                                	continue;
                                            } 
                                        	
                                            if($field_key != "ID" && !isset($db_include[$contest][$table_key]["rel"][$field_key])) {
	                                            if(strlen($sSQL_field_master))
	                                                $sSQL_field_master .= ", "; 

                                                $sSQL_field_master .= " `" . $field_key . "` = " . $db->toSql($field_value, "Text") . " ";
											}
                                        }
                                        foreach($arr_slave[$table_key][$record_key] AS $field_key => $field_value) {
											if(count($db_include[$contest][$table_key]["compare"]) > 0) {
                                            	if($db_include[$contest][$table_key]["compare"][$field_key] !== true)
                                                	continue;
                                            } 

                                            if(strlen($sSQL_field_slave))
                                                $sSQL_field_slave .= " AND "; 

                                                $sSQL_field_slave .= " `" . $field_key . "` = " . $db->toSql($field_value, "Text") . " ";
                                        }

										if(array_key_exists("operation", $db_include[$contest][$table_key])) {
											if(array_key_exists("update", $db_include[$contest][$table_key]["operation"])) {
												if($db_include[$contest][$table_key]["operation"]["update"]) {
													$allow_operation = true;
												} else {
													$allow_operation = false;
												}
											} else {
												$allow_operation = false;
											}
										} else {
											$allow_operation = true;
										}

										if($allow_operation) {
	                                        $sSQL = "UPDATE `" . $table_key . "` SET " . $sSQL_field_master . " WHERE " . $sSQL_field_slave;
	                                        if(count($db_include[$contest][$table_key]["rel"])) {
	                                            $operation_bottom[] = array("data" => $update_label . " " . $table_key . " (" . $record_key . ")", "value" => $sSQL);
	                                        } else {
	                                            $operation_top[] = array("data" => $update_label . " " . $table_key . " (" . $record_key . ")", "value" => $sSQL);
	                                        }
										}
                                        //Modifica il campo
                                    }
                                }
                            }
                        }
                    }
                }
                reset($arr_master); 

                $operation = array_merge($operation_top, $operation_bottom);
                
                if($json) {
                    if($execute) {
                        if(is_array($operation) && count($operation)) {
                            foreach($operation AS $operation_key => $operation_value) {
                                if(LIMIT_OPERATION > 0 && $count_operation >= LIMIT_OPERATION) {
                                    break;
                                }

                                $sSQL = $operation_value["value"];
                                $db->execute($sSQL);
                                
                                unset($operation[$operation_key]);
                                
                                $count_operation++;
                            } 
                            reset($operation);
                            
                            if($contest == "international") {
                                $i18n_error = false;
                                
                                $db->query("SELECT * FROM " . FF_PREFIX . "languages");
                                if($db->nextRecord()) {
                                    do {
                                        if($handle = @fopen(CM_CACHE_PATH . "/international" . "/" . strtoupper($db->getField("code", "Text", true)) . "." . FF_PHP_EXT, 'w')) {
                                            $i18n_content = "";
                                            if(@fwrite($handle, $i18n_content) === FALSE) {
                                                $i18n_error = true;
                                            }
                                            @fclose($handle);
                                        } else {
                                            $i18n_error = true;
                                        }
                                    } while($db->nextRecord());
                                }
                            }
                        }
                         echo json_encode(array("record" => array_values($operation), "limit" => $limit_operation));
                    } else {
                        echo json_encode(array("record" => $operation));
                    }
                    exit;
                }

                $sSQL = "";
                if(is_array($operation) && count($operation)) {
                    foreach($operation AS $operation_key => $operation_value) {
                        if(strlen($sSQL)) 
                            $sSQL .= " UNION ";
                        $sSQL .= " ( SELECT '0' AS `check`
                                , " . $db->toSql($operation_key, "Number") . " AS `ID`
                                , " . $db->toSql($operation_value["data"], "Text") . " AS `operation`
                                , " . $db->toSql($operation_value["value"], "Text") . " AS `sql` ) ";
                    }
					if(class_exists("ffGrid")) {
	                    $oGrid = ffGrid::factory($cm->oPage);
	                    $oGrid->id = "UpdaterCheck";
	                    $oGrid->title = ffTemplate::_get_word_by_code("updater_title");
	                    $oGrid->source_SQL = $sSQL . " [WHERE] [ORDER]";
	                    $oGrid->order_default = "ID";
	                    $oGrid->display_edit_bt = false;
	                    $oGrid->display_edit_url = false;
	                    $oGrid->display_delete_bt = false;
	                    $oGrid->display_new = false;
	                    
	                    $oGrid->addEvent("on_do_action", "UpdaterCheck_on_do_action");
	                    $oGrid->use_paging = false;
	                    $oGrid->user_vars["operations"] = $operation;
	                    $oGrid->user_vars["contest"] = $contest;

	                    // Campi chiave
	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "ID";
	                    $oField->base_type = "Number";
	                    $oGrid->addKeyField($oField);

	                    // Campi visualizzati
	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "check";
	                    $oField->label = ffTemplate::_get_word_by_code("updater_check");
	                    $oField->control_type = "checkbox";
	                    $oField->extended_type = "Boolean";
	                    $oField->checked_value = new ffData("1");
	                    $oField->unchecked_value = new ffData("0");
	                    $oGrid->addContent($oField);

	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "operation";
	                    $oField->label = ffTemplate::_get_word_by_code("updater_operation");
	                    $oGrid->addContent($oField);

	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "sql";
	                    $oField->label = ffTemplate::_get_word_by_code("updater_sql");
	                    $oGrid->addContent($oField);

	                    $oButton = ffButton::factory($cm->oPage);
	                    $oButton->id = "check_all";
	                    $oButton->action_type = "gotourl";
	                    $oButton->url = "#";
	                    $oButton->aspect = "link";
	                    $oButton->label = ffTemplate::_get_word_by_code("updater_check_all");
	                    $oButton->properties["onclick"] = 'if(jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\') == false) { jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\', \'checked\'); } else { jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\', \'\'); }';
	                    $oGrid->addActionButton($oButton);

	                    $oButton = ffButton::factory($cm->oPage);
	                    $oButton->id = "update";
	                    $oButton->action_type = "submit";
	                    $oButton->frmAction = "update";
	                    $oButton->aspect = "link";
	                    $oButton->label = ffTemplate::_get_word_by_code("updater_execute");
	                    $oGrid->addActionButton($oButton);

	                    $oButton = ffButton::factory($cm->oPage);
	                    $oButton->id = "cancel";
	                    $oButton->action_type = "gotourl";
	                    $oButton->url = "[RET_URL]";
	                    $oButton->aspect = "link";
	                    $oButton->label = ffTemplate::_get_word_by_code("updater_cancel");
	                    $oGrid->addActionButton($oButton);
	                    
	                    $cm->oPage->addContent($oGrid);
					} else {
						print_r($operation);
						exit;
					}
                } else {
                    if(function_exists("ffRedirect")) {
                        ffRedirect(urldecode($_REQUEST["ret_url"]));
					} else {
                        header("Location: " . urldecode($_REQUEST["ret_url"]));
                        exit;
					}
                }
            } else {
                $strError = $wrong_source_data_label . " (" . (!is_array($arr_slave) && isset($file_get_contents_slave_failed[$json_slave]) ? $file_get_contents_slave_failed[$json_slave] : (strlen($json_slave) ? $json_slave : $restrictions_in_effect_slave)) . ")";
            }
        } else {
            $strError = $wrong_source_data_label . " (" . (!is_array($arr_master) && isset($file_get_contents_master_failed[$json_master]) ? $file_get_contents_master_failed[$json_master] : (strlen($json_master) ? $json_master : $restrictions_in_effect_master)) . ")";
        }
    } else {
        if(!$nowarning)
        	$strError = $master_server_label;
    }
} else {
	if(!$nowarning)
    	$strError = $updater_configuration_label;
}

if($strError) {
    if($json) {
        echo json_encode(array("record" => array(), "error" => $strError));
        exit;
    } elseif(is_object($cm)) {
        $cm->oPage->fixed_pre_content = $strError;
    } else {
    	echo $strError;
    	exit;
    }
} else {
	echo json_encode(array("record" => array()));
	exit;	
}

function UpdaterCheck_on_do_action($component, $action) {
    $operations = $component->user_vars["operations"];
    $contest = $component->user_vars["contest"];
    
    $db = ffDB_Sql::factory();
    
    foreach($component->recordset_keys AS $key => $value) {
        $sSQL = $operations[$value["ID"]]["value"];
        $db->execute($sSQL);
    }
    
    if($contest == "international") {
        $i18n_error = false;
        
        $db->query("SELECT * FROM " . FF_PREFIX . "languages");
        if($db->nextRecord()) {
            do {
                if($handle = @fopen(CM_CACHE_PATH . "/international" . "/" . strtoupper($db->getField("code", "Text", true)) . "." . FF_PHP_EXT, 'w')) {
                    $i18n_content = "";
                    if(@fwrite($handle, $i18n_content) === FALSE) {
                        $i18n_error = true;
                    }
                    @fclose($handle);
                } else {
                    $i18n_error = true;
                }
            } while($db->nextRecord());
        }
    }
    
    if(is_array($operations) && count($operations))
        ffRedirect($_SERVER["REQUEST_URI"]);
    else 
        ffRedirect($component->parent[0]->ret_url);
}  

function resolve_rel_data($table, $key, $value, $db_include, $db_rel, $db) {
	$sSQL_compare = "";
	$sSQL_sub_compare = "";
	if(is_array($db_rel[$table][$value]) && count($db_rel[$table][$value])) {
		foreach($db_rel[$table][$value] AS $compare_sub_key => $compare_sub_value) {
			if($compare_sub_key != "ID") {
				if(count($db_include[$table]["compare"]) > 0) {
					if(!isset($db_include[$table]["compare"][$compare_sub_key]) || !$db_include[$table]["compare"][$compare_sub_key])
					    continue;
				} 

				if(is_array($db_include[$table]["rel"]) && array_key_exists($compare_sub_key, $db_include[$table]["rel"])) {
				   $sSQL_sub_compare .= resolve_rel_data($db_include[$table]["rel"], $key, $value, $db_include, $db_rel, $db);
				} else {
					if(strlen($sSQL_sub_compare))
						$sSQL_sub_compare .= " AND "; 

	                $sSQL_sub_compare .= " `" . $compare_sub_key . "` = " . $db->toSql($compare_sub_value, "Text") . " ";
				}
			}
		}
		reset($db_rel[$table][$value]);
	}
	
	if(is_array($db_include[$table]) &&  array_key_exists("altKey", $db_include[$table])) {
		$key_rel = $db_include[$table]["altKey"];
	} else {
		$key_rel = "ID";
	}


	$sSQL_compare .= " `" . $key . "` = " . " 
	                        IFNULL(
						        ( 
							        SELECT IFNULL(`" . $table . "`.`" . $key_rel . "`, 0) AS ID 
							        FROM `" . $table . "`
							        WHERE " . (strlen($sSQL_sub_compare) ? $sSQL_sub_compare : $key_rel . " = " . $value) . "
							        LIMIT 1
							     ) 
							     , 0
							)";

	return $sSQL_compare;
}
