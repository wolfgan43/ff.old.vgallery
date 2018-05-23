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
	require_once(__DIR__ . "/common.php");

	$params = updater_get_params($cm);
	if(!$params["invalid"]) {
	    @set_time_limit(0);
		$tbl 							= array();

		$db_updater 					= new ffDB_Sql();
		$db_updater_detail 				= new ffDB_Sql();
		$db_compare 					= new ffDB_Sql();

		$contest 						= $_REQUEST["contest"];
        if(isset($_REQUEST["t"]))
            $tbl["name"] 				= $_REQUEST["t"];
        if(isset($_REQUEST["l"]))
            $tbl["limit"] 				= $_REQUEST["l"];

        $mode 							= $_REQUEST["mode"];

        $db_res 						= array();
        $db_exclude 					= array();
        $db_exclude_prefix 				= array();
        
        if(is_array($params["exclude"]["db"]) && count($params["exclude"]["db"])) {
            $db_exclude 				= array_merge($db_exclude, $params["exclude"]["db"]);
        }
        
        if(is_array($params["exclude"]["db_prefix"]) && count($params["exclude"]["db_prefix"])) {
            $db_exclude_prefix 			= array_merge($db_exclude_prefix, $params["exclude"]["db_prefix"]);
        }
        
        $sSQL = "SHOW TABLES";   
        $db_updater->query($sSQL);
        if($db_updater->nextRecord()) {
            switch (basename($params["user_path"])) {
                case "structure":
                    do {
                        $skip_table = false;
                        if(array_key_exists($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $db_exclude)) {
                        	$skip_table = true;
                        }

                        if(!$skip_table && is_array($db_exclude_prefix) && count($db_exclude_prefix)) {
                            foreach($db_exclude_prefix AS $exclue_prefix_value) {
                                if(strlen($exclue_prefix_value)
                                    && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $exclue_prefix_value) === 0
                                ) {
                                    $skip_table = true;
                                    break;
                                }
                            }
                        }

                        if(!$skip_table && REAL_REMOTE_HOST == REMOTE_HOST && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "cm_mod_") === 0) {
                        	$arrTableModule = explode("_", substr($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), strlen("cm_mod_")));

                        	//print_r($arrTableModule);
                        	if(!is_dir(FF_DISK_PATH . "/modules/" . $arrTableModule[0])) {
								//$skip_table = true;
                            } elseif(is_file(FF_DISK_PATH . "/modules/" . $arrTableModule[0] . "/conf/schema.php")) {
                        	    $schema = array();
                                require(FF_DISK_PATH . "/modules/" . $arrTableModule[0] . "/conf/schema.php");
                                if(strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $schema["db"]["table_prefix"]) === 0) {
                                    $skip_table = true;
                                }
                            }
                        }

                        if(!$skip_table) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();
                            
                            $sSQL = "DESCRIBE `" . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false) . "`";
                            $db_updater_detail->query($sSQL);
                            if($db_updater_detail->nextRecord()) {
                                do {
                                    $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Field", "Text", true)] = array(
                                        "Type" => $db_updater_detail->getField("Type", "Text", true)
                                        , "Null" => $db_updater_detail->getField("Null", "Text", true)
                                        , "Key" => ($db_updater_detail->getField("Key", "Text", true) != "PRI" ? "" : $db_updater_detail->getField("Key", "Text", true))
                                        , "Default" => ($db_updater_detail->getField("Default", "Text", true) === NULL ? "" : $db_updater_detail->getField("Default", "Text", true))
                                        , "Extra" => $db_updater_detail->getField("Extra", "Text", true)
                                    );
                                } while($db_updater_detail->nextRecord());
                                ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                                reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            }
                        }
                    } while($db_updater->nextRecord());
                    break;
                case "indexes":
                    do {
                        $skip_table = false;
                        if(array_key_exists($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $db_exclude)) {
                            $skip_table = true;
                        }

                        if(!$skip_table && is_array($db_exclude_prefix) && count($db_exclude_prefix)) {
                            foreach($db_exclude_prefix AS $exclue_prefix_value) {
                                if(strlen($exclue_prefix_value) 
                                    && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $exclue_prefix_value) === 0
                                ) {
                                    $skip_table = true;
                                    break;
                                }
                            }
                        }
                        
                        if(!$skip_table && REAL_REMOTE_HOST == REMOTE_HOST && strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "cm_mod_") === 0) {
                        	$arrTableModule = explode("_", substr($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), strlen("cm_mod_")));
                        	if(!is_dir(FF_DISK_PATH . "/modules/" . $arrTableModule[0])) {
								$skip_table = true;
                            } elseif(is_file(FF_DISK_PATH . "/modules/" . $arrTableModule[0] . "/conf/schema.php")) {
                                $schema = array();
                                require(FF_DISK_PATH . "/modules/" . $arrTableModule[0] . "/conf/schema.php");
                                if(strpos($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), $schema["db"]["table_prefix"]) === 0) {
                                    $skip_table = true;
                                }
                            }
                        }
                        
                        if(!$skip_table) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();
                            
                            $sSQL = "SHOW INDEX FROM `" . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false) . "`";
                            $db_updater_detail->query($sSQL);
                            if($db_updater_detail->nextRecord()) {
                                do {
                                    if(isset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Key_name", "Text", true)])) {
                                        $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Key_name", "Text", true)]["Column_name"] .= ", " . "`" . $db_updater_detail->getField("Column_name", "Text", true) . "`";
                                    } else {
                                        $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)][$db_updater_detail->getField("Key_name", "Text", true)] = array(
                                            "Non_unique" => $db_updater_detail->getField("Non_unique", "Text", true)
                                            , "Key_name" => $db_updater_detail->getField("Key_name", "Text", true)
                                            , "Seq_in_index" => $db_updater_detail->getField("Seq_in_index", "Text", true)
                                            , "Column_name" => "`" . $db_updater_detail->getField("Column_name", "Text", true) . "`"
                                            , "Collation" => $db_updater_detail->getField("Collation", "Text", true)
                                            , "Sub_part" => $db_updater_detail->getField("Sub_part", "Text", true)
                                            , "Packed" => $db_updater_detail->getField("Packed", "Text", true)
                                            , "Null" => $db_updater_detail->getField("Null", "Text", true)
                                            , "Index_type" => $db_updater_detail->getField("Index_type", "Text", true)
                                            , "Comment" => $db_updater_detail->getField("Comment", "Text", true)
                                        );
                                    }
                                } while($db_updater_detail->nextRecord());
                                ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                                reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            }
                        }
                    } while($db_updater->nextRecord());
                    break;
                case "data":
					$db_include = array();
					$db_master_include = array();
                    require(__DIR__ . "/include_db.php");

		            if(is_array($params["include"]["db"]) && count($params["include"]["db"])) {
		                $db_include = array_merge($db_include, $params["include"]["db"]);
		            }
                    
                    $db_rel = array();
                    do {
                        if(is_array($tbl) && count($tbl) && array_key_exists("name", $tbl) && strlen($tbl["name"])) {
                            if($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true) != $tbl["name"])
                                continue;
                        }
                        if(isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)])) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();
                            if(is_array($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"]) && count($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"])) {
								foreach($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"] AS $rel_key => $rel_value) {
									$db_rel[$rel_value] = true;
								}								
                            }

							$column = array();
							$sSQL = "DESCRIBE `" . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false) . "`";
							$db_updater_detail->query($sSQL);
							if($db_updater_detail->nextRecord()) {
								do {
									$column[] = $db_updater_detail->getField("Field", "Text", true);
								} while($db_updater_detail->nextRecord());
								sort($column);
								reset($column);
							}

                            $sSQL_table_sort = "";
                            $sSQL_table_limit = "";
                            if($sync) { 
                                if(is_array($tbl) && count($tbl) && array_key_exists("limit", $tbl) && $tbl["limit"] > 0) {
                                    $sSQL_table_limit = " LIMIT " . (($tbl["limit"] - 1) * 10000) . ", " . ($tbl["limit"] * 10000);
                                }
                                if(isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"])) {
                                    if(is_array($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"]) && count($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"])) {
                                        foreach($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"] AS $compare_key => $compare_value) {
                                            if(strlen($sSQL_table_sort))
                                                $sSQL_table_sort .= ", "; 
                                            
                                            $sSQL_table_sort .= " `" . $compare_key . "` ";
                                        }
                                        reset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["compare"]);
                                    }
                                }                                    
                                if(!strlen($sSQL_table_sort)) {
                                    if(array_search("ID", $column) === false) {
                                        $sSQL_table_sort = "`" . $column[0] . "`";
                                    } else {
                                        $sSQL_table_sort = "`ID`";
                                    }
                                }
                                $sSQL_table_sort = " ORDER BY " . $sSQL_table_sort;
                            }  
                                                      
                            $sSQL = "SELECT * 
                                    FROM " . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false)  
                                    . (strlen($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"])
                                        ? " WHERE " .  $db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"]
                                        : ""
                                    ) 
                                    . $sSQL_table_sort
                                    . $sSQL_table_limit;

                            $db_updater_detail->query($sSQL);
                            if($db_updater_detail->nextRecord()) {
                                do {
                                    $record_key = "";
                                    $tmp_value = array();
                                    $tmp_res = array();
                                    
                                    foreach($column AS $column_value) {
                                        if($mode == "compact") {
                                            $tmp_value[$column_value] = "";
                                        } else {
                                            $tmp_value[$column_value] = $db_updater_detail->getField($column_value, "Text", true); 
                                        }
                                        
                                        if(
                                            (
                                                (
                                                    !count($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"]) 
                                                &&
                                                    $column_value != "ID"
                                                )
                                            || 
                                                isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["key"][$column_value])
                                            )
                                            //&& !isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"][$db_updater_detail->getField($column_value, "Text", true)])
                                        ) {
                                        	if(strlen($record_key))
                                        		$record_key .= "-";
                                        		
                                            if(isset($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"][$column_value])) {
                                                $compare_tbl = $db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["rel"][$column_value];
                                                if(count($db_include[$contest][$compare_tbl]["key"])) {
                                                    $sSQL_compare = "";
                                                    $sSQL_compare_field = "";
                                                    foreach($db_include[$contest][$compare_tbl]["key"] AS $compare_key => $compare_value) {
                                                    	if(0 && is_array($db_include[$contest][$compare_tbl]["rel"]) && array_key_exists($compare_key, $db_include[$contest][$compare_tbl]["rel"])) {
															// da finire importante
	                                                        if(strlen($sSQL_compare_field))
	                                                            $sSQL_compare_field .= ", "; 

															$sSQL_compare_field .= updater_resolve_rel_data($db_include[$contest][$compare_tbl]["rel"][$compare_key], $compare_key, $compare_value, $db_include[$contest], $db_compare);
                                                    	} else {
	                                                        if(strlen($sSQL_compare_field))
	                                                            $sSQL_compare_field .= ", "; 
	                                                        
	                                                        $sSQL_compare_field .= " `" . $compare_key . "` ";
                                                    	}
                                                    	
                                                    }
                                                    reset($db_include[$contest][$compare_tbl]["key"]);
                                                    
                                                    $sSQL_compare = "SELECT CONCAT(" . $sSQL_compare_field . ") AS compare FROM " . $compare_tbl . " WHERE `ID` = " . $db_compare->toSql($db_updater_detail->getField($column_value, "Text", true)); 
                                                    $db_compare->query($sSQL_compare);
                                                    if($db_compare->nextRecord())
                                                        $record_key .= $db_compare->getField("compare", "Text", true);
                                                } else {
                                                    $record_key .= $db_updater_detail->getField($column_value, "Text", true);
                                                }
                                            } else {
                                                $record_key .= $db_updater_detail->getField($column_value, "Text", true);
                                            }
                                        } 
                                    }
                                    //if(array_key_exists("record_" . md5($record_key), $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)])) {
									//	echo $tmp_value . "<br>";
                                    //}
                                    $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["R" . md5($record_key)] = $tmp_value;
                                } while($db_updater_detail->nextRecord());
                                ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                                reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            }
                        }
                    } while($db_updater->nextRecord());
                    
                    if(is_array($db_rel) && count($db_rel)) {
						foreach($db_res AS $db_res_key => $db_res_value) {
							if(array_key_exists($db_res_key, $db_rel)) {
								$db_rel[$db_res_key] = $db_res_value;
								unset($db_res[$db_res_key]);
							}
						}
						foreach($db_rel AS $db_rel_key => $db_rel_value) {
							if(!is_array($db_rel[$db_rel_key])) {
								unset($db_rel[$db_rel_key]);
							}
						}
						$db_res = array_merge($db_rel, $db_res); 
                    }
                    break; 
                default:
					$db_include = array();
					$db_master_include = array();
					require(__DIR__ . "include_db.php");

					if(is_array($params["include"]["db"]) && count($params["include"]["db"])) {
						$db_include = array_merge($db_include, $params["include"]["db"]);
					}

					$column = array();
					$sSQL = "DESCRIBE `" . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false) . "`";
					$db_updater_detail->query($sSQL);
					if($db_updater_detail->nextRecord()) {
						$column = array();
						do {
							$column[] = $db_updater_detail->getField("Field", "Text", true);
						} while($db_updater_detail->nextRecord());
						sort($column);
						reset($column);
					}

					do {
                        $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)] = array();

                        $sSQL = "SELECT COUNT(*) AS count_record
                                FROM " . $db_updater_detail->toSql($db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true), "Text", false)  
                                . (strlen($db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"])
                                    ? " WHERE " .  $db_include[$contest][$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["data"]
                                    : ""
                                );

                        $db_updater_detail->query($sSQL);
                        if($db_updater_detail->nextRecord()) {
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["record"] = $db_updater_detail->getField("count_record", "Text", true);
                            $db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]["column"] = count($column);
                            ksort($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                            reset($db_res[$db_updater->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true)]);
                        }

                    } while($db_updater->nextRecord());
            }
            //ksort($db_res);
            reset($db_res);
        }
      	
        if($mode == "compact") 
            echo md5(json_encode($db_res));
        else
            echo json_encode($db_res);
        
        exit; 
    } else {
		die($params["invalid"]);
    }
    


