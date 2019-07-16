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
  function clone_by_schema($ID, $schema, $type, $params = array()) {
  	if(is_array($schema) && count($schema)) {
  		if($type == "updater") {
  			clone_by_remote_schema($ID, $schema);
		} else {
  			$db = ffDB_Sql::factory();

			$return_ID = 0;
  			foreach($schema AS $schema_name => $schema_params) {
  				if(is_array($schema_params) && count($schema_params)) {
					foreach($schema_params AS  $clone_key => $clone_value) { 
						if(isset($schema[$schema_name][$clone_key]["real_table"]) && strlen($schema[$schema_name][$clone_key]["real_table"])) {
							$real_table = $schema[$schema_name][$clone_key]["real_table"];
						} else {
							$real_table = $clone_key;
						}
						
						$sSQL = "SELECT * 
								FROM information_schema.COLUMNS 
								WHERE TABLE_NAME = " . $db->toSql($real_table) . "
									AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "'";
						$db->query($sSQL);
						if($db->nextRecord()) {
							do {
								$field_name = $db->getField("COLUMN_NAME", "Text", true);

								if(strtolower($db->getField("EXTRA", "Text", true)) == "auto_increment")
									continue;

								if(is_array($schema[$schema_name][$clone_key]["exclude_fields"]) && isset($schema[$schema_name][$clone_key]["exclude_fields"][$field_name]))
									continue;

								if(strlen($schema[$schema_name][$clone_key]["compare"]))
									$schema[$schema_name][$clone_key]["compare"] .= ", ";
								
								$schema[$schema_name][$clone_key]["fields"][] = "`" . $field_name . "`";
								if($return_ID && isset($schema[$schema_name][$clone_key]["use_return_ID"]) && $schema[$schema_name][$clone_key]["use_return_ID"] == $field_name) {
									$schema[$schema_name][$clone_key]["compare"] .= "'" . $return_ID . "' AS `" . $field_name . "`";
								} else {
									$schema[$schema_name][$clone_key]["compare"] .= "`" . $db->toSql($real_table, "Text", false) . "`.`" . $field_name . "`"; 
								}
							} while($db->nextRecord());
						}
						
						$sSQL = "INSERT INTO `" . $db->toSql($real_table, "Text", false) . "` 
									( " . implode(",", $schema[$schema_name][$clone_key]["fields"]) . " ) 
									SELECT  " . $schema[$schema_name][$clone_key]["compare"] . "
									FROM  `" . $db->toSql($real_table, "Text", false) . "` 
									WHERE 
									" . (isset($schema[$schema_name][$clone_key]["compare_key"])
										? "`" . $db->toSql($real_table, "Text", false) . "`.`" . $db->toSql($schema[$schema_name][$clone_key]["compare_key"], "Text", false) . "`=" . $db->toSql($ID, "Number")
										: $schema[$schema_name][$clone_key]["compare_str"]
									); 
						$db->execute($sSQL);

						if(isset($schema[$schema_name][$clone_key]["return_ID"])) {
							$return_ID = $db->getInsertID(true);
							$schema[$schema_name][$clone_key]["return_ID"] = $return_ID;
						}
					}
				}

				switch($type) {
					case "vgnode":
						foreach($schema_params AS  $clone_key => $clone_value) { 
							if($schema[$schema_name][$clone_key]["return_ID"] > 0) {
                                if(check_function("get_node_by_permalink"))
                                    $old_node = get_node_by_id($schema[$schema_name][$clone_key]["return_ID"], array("parent", "smart_url", "meta_title"));

	                			$old_parent = stripslash($old_node["parent"]) . "/" . $old_node["smart_url"];
                                    
								if(strrpos($old_node["smart_url"], "-" . $ID) !== false)
									$old_node["smart_url"] = substr($old_node["smart_url"], 0, strrpos($old_node["smart_url"], "-" . $ID));
								
								if(strrpos($old_node["smart_url"], "-" . $schema[$schema_name][$clone_key]["return_ID"]) === false) {
									$new_smart_url = $old_node["smart_url"] . "-" . $schema[$schema_name][$clone_key]["return_ID"];
								} else {
									$new_smart_url = $old_node["smart_url"];
								}
                                if(OLD_VGALLERY) {
								    $sSQL = "UPDATE vgallery_rel_nodes_fields SET 
					    					    vgallery_rel_nodes_fields.description = " . $db->toSql($new_smart_url) . "
										    WHERE vgallery_rel_nodes_fields.ID_fields = (SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'smart_url' AND vgallery_type.name = 'System')
											    AND vgallery_rel_nodes_fields.ID_nodes = " . $db->toSql($schema[$schema_name][$clone_key]["return_ID"], "Number");
								    $db->execute($sSQL);
                                } else {
                                    $sSQL = "UPDATE vgallery_nodes_rel_languages SET 
                                                vgallery_nodes_rel_languages.smart_url = " . $db->toSql($new_smart_url) . "
                                            WHERE vgallery_nodes_rel_languages.ID_nodes = " . $db->toSql($schema[$schema_name][$clone_key]["return_ID"], "Number");
                                    $db->execute($sSQL);
                                }
                                if(!$old_node["meta_title"])
                                	$old_node["meta_title"] = ucwords(str_replace("-", " " , $old_node["smart_url"]));

						        $db->execute("UPDATE vgallery_nodes 
						                    SET vgallery_nodes.name = " . $db->toSql($new_smart_url) . "
						                    	, vgallery_nodes.meta_title = " . $db->toSql($old_node["meta_title"] . " - Copy") . "
						                        , vgallery_nodes.is_clone = '1'
						                    WHERE
						                        vgallery_nodes.ID = " . $db->toSql($schema[$schema_name][$clone_key]["return_ID"], "Number")
						                );

							    $new_parent = stripslash($old_node["parent"]) . "/" . $new_smart_url;
					            if($old_parent != $new_parent) {
						            $db->execute("UPDATE vgallery_rel_nodes_fields 
						                        SET vgallery_rel_nodes_fields.description = REPLACE(vgallery_rel_nodes_fields.description, " . $db->toSql($old_parent)  . ", " . $db->toSql($new_parent) . ")
						                        WHERE
	                            					vgallery_rel_nodes_fields.ID_nodes = " . $db->toSql($schema[$schema_name][$clone_key]["return_ID"], "Number")  . "
						                        "
						                    );

                					if(is_dir(FF_DISK_UPDIR . $old_parent) && check_function("fs_operation")) {
										full_copy(FF_DISK_UPDIR . $old_parent, FF_DISK_UPDIR . $new_parent, false);  
									}
								}

						      //  if(check_function("update_vgallery_models"))
		            			//	update_vgallery_models("update", $params["ID_vgallery"], $schema[$schema_name][$clone_key]["return_ID"], $params["vgallery_name"], $params["vgallery_parent_old"], $new_smart_url, $params["gallery_model"]);

							}
						}
						break;
					case "vgtype":
						foreach($schema_params AS  $clone_key => $clone_value) { 
							if($schema[$schema_name][$clone_key]["return_ID"] > 0) {
							    $db->execute("UPDATE " . $clone_key . " 
							                SET " . $clone_key . ".is_clone = '1'
		                						, " . $clone_key . ".public = '0'
		                						, " . $clone_key . ".name = CONCAT(" . $clone_key . ".name, '-copy')
							                WHERE
							                    " . $clone_key . ".ID = " . $db->toSql($schema[$schema_name][$clone_key]["return_ID"], "Number")
							            );
							}
						}  
						break;
					default:
				}
  			}
  		}
	}
}

function clone_by_remote_schema($service_name, $remote_schema, $service_schema = null) {
	if($service_schema === null) {
		if(check_function("get_schema_def")) {
			$service = get_schema_def();
			
			$service_schema = $service["schema"];
		}
		
/*		if(is_file(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT)) {
			require(FF_DISK_PATH . "/library/" . THEME_INSET . "/schema." . FF_PHP_EXT);	
		}

		$arrServiceModuleFileName = glob(FF_DISK_PATH . "/modules/*");
		if(is_array($arrServiceModuleFileName) && count($arrServiceModuleFileName)) {
			foreach($arrServiceModuleFileName AS $real_service_module_path) {
				if(is_file($real_service_module_path . "/conf/schema." . FF_PHP_EXT)) {
					require($real_service_module_path . "/conf/schema." . FF_PHP_EXT);	
				}
			}
		}
		unset($arrServiceModuleFileName);*/
	}

    if(array_key_exists("relationship", $service_schema[$service_name])
        && is_array($service_schema[$service_name]["relationship"]) 
        && count($service_schema[$service_name]["relationship"])
    ) {
        foreach($service_schema[$service_name]["relationship"] AS $service_rel_key => $service_rel_value) {
            if(!$service_rel_value["multi"]
                && !isset($service_schema[$service_rel_key]["relationship"][$service_name])
            ) {
                if(array_key_exists("ID", $remote_schema[$service_rel_key])) {
                    clone_by_remote_schema($service_rel_key, $remote_schema, $service_schema);
                } else {
                    if(strpos($remote_schema[$service_name][$service_rel_value["key"]], ",") !== false) {
                        $remote_schema_field_compare = explode(",", $remote_schema[$service_name][$service_rel_value["key"]]);
                    } else {
                        $remote_schema_field_compare[] = $remote_schema[$service_name][$service_rel_value["key"]];
                    }

                    foreach($remote_schema[$service_rel_key] AS $remote_schema_field) {
                        if(array_search($remote_schema_field[$service_rel_value["rel_key"]], $remote_schema_field_compare) !== false) {
                            $tmp_remote_schema = $remote_schema;
                            $tmp_remote_schema[$service_rel_key] = $remote_schema_field;
                            clone_by_remote_schema($service_rel_key, $tmp_remote_schema, $service_schema);    
                        }
                    }
                    
                }

            }
        }
    }
	
	if(array_key_exists($service_name, $service_schema)
		&& array_key_exists($service_name, $remote_schema)
	) {
		if(is_array($remote_schema[$service_name]) && count($remote_schema[$service_name])) {
			if(array_key_exists("ID", $remote_schema[$service_name])) {
				$res = clone_by_remote_schema_field($service_name, $remote_schema[$service_name], $remote_schema, $service_schema);
			} else {
  				foreach($remote_schema[$service_name] AS $remote_schema_field) {
					$res = clone_by_remote_schema_field($service_name, $remote_schema_field, $remote_schema, $service_schema);
					//TODO: il servizio principale nn deve mai passare qui
				}
			}		
		}
	}

	if(array_key_exists("relationship", $service_schema[$service_name])
		&& is_array($service_schema[$service_name]["relationship"]) 
		&& count($service_schema[$service_name]["relationship"])
	) {
		foreach($service_schema[$service_name]["relationship"] AS $service_rel_key => $service_rel_value) {
			if($service_rel_value["multi"]) {
				if(!array_key_exists("value", $service_schema[$service_name]["relationship"][$service_rel_key])) {
					$service_schema[$service_name]["relationship"][$service_rel_key]["value"] = $res["ID"];  
                    if($res["is_new"] !== false)
                        clone_by_remote_schema($service_rel_key, $remote_schema, $service_schema);
				}
			}
		}
	}
}


function clone_by_remote_schema_field($service_name, $remote_schema_field, $remote_schema, $service_schema = null, $allow_insert = true) {
	$db = ffDB_Sql::factory();

	$system_fields = array(
		"ID"                    => NULL
		, "is_clone"            => true
		, "public"              => false
		, "last_update"         => time()
		, "created"             => time()
		, "owner"               => Auth::get("user")->id
	
	);
	$compare_fields = array(
		"is_clone"              => false
		, "public"              => false
		, "public_cover"        => false
		, "public_description"  => false
		, "public_link_doc"     => false
	);

	$exclude_fields = array();	
	$rel_fields = array();	
	$table_fields = array();

    if(array_key_exists("field_convert", $service_schema[$service_name])
        && is_array($service_schema[$service_name]["field_convert"])) 
        $arrFieldConvert = $service_schema[$service_name]["field_convert"];
    else 
        $arrFieldConvert = array();

	if(array_key_exists("relationship", $service_schema[$service_name])
		&& is_array($service_schema[$service_name]["relationship"]) 
		&& count($service_schema[$service_name]["relationship"])
	) {
		foreach($service_schema[$service_name]["relationship"] AS $service_rel_key => $service_rel_value) {
			if(!$service_rel_value["multi"]) {
				if(strpos($remote_schema_field[$service_rel_value["key"]], ",") !== false) {
					$remote_schema_field_compare = explode(",", $remote_schema_field[$service_rel_value["key"]]);
				} else {
					$remote_schema_field_compare[] = $remote_schema_field[$service_rel_value["key"]];
				}

				if(is_array($remote_schema[$service_rel_key]) && count($remote_schema[$service_rel_key])) {
					if(array_key_exists("ID", $remote_schema[$service_rel_key])) {
                        if(array_search($remote_schema[$service_rel_key]["ID"], $remote_schema_field_compare) !== false) {
						    $rel_remote_field[] = $remote_schema[$service_rel_key];
                        }
					} else {
						foreach($remote_schema[$service_rel_key] AS $remote_schema_field_key => $remote_schema_field_value) {
							if(array_search($remote_schema_field_value["ID"], $remote_schema_field_compare) !== false) {
								$rel_remote_field[] = $remote_schema[$service_rel_key][$remote_schema_field_key];
								//break;
							}
						}
					}				
				}
				
				if(is_array($rel_remote_field)) {
					//if(count($rel_remote_field) > 1) { nn so a cosa serve. Nel vgallery limit_type smette di funzionare l'associazione se messo
					//	clone_by_remote_schema($service_rel_key, $remote_schema, $service_schema);
					//} else {
						$ID_rel = array();
						if(isset($service_schema[$service_rel_key]["relationship"][$service_name])) {
							if(!array_key_exists("value", $service_schema[$service_rel_key]["relationship"][$service_name])) {
								foreach($rel_remote_field AS $rel_remote_field_value) {
                                	$res = clone_by_remote_schema_field($service_rel_key, $rel_remote_field_value, $remote_schema, $service_schema);
                                	$ID_rel	[] = $res["ID"];
								}
 							    $service_schema[$service_rel_key]["relationship"][$service_name]["value"] = implode(",", array_filter($ID_rel));
                            }
                            $rel_fields[$service_rel_value["key"]] = $service_schema[$service_rel_key]["relationship"][$service_name]["value"];
						} else {
							foreach($rel_remote_field AS $rel_remote_field_value) {
                            	$res = clone_by_remote_schema_field($service_rel_key, $rel_remote_field_value, $remote_schema, $service_schema, false);
								$ID_rel[] = $res["ID"];
							}
							$rel_fields[$service_rel_value["key"]] = implode(",", array_filter($ID_rel));
						}
					//}
				}
			}
		}
	}

	$sSQL = "SELECT * 
			FROM information_schema.COLUMNS 
			WHERE TABLE_NAME = " . $db->toSql($service_name) . "
				AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "'";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$table_fields[] = $db->getField("COLUMN_NAME", "Text", true);
		} while($db->nextRecord());
	}
	//if(!is_array($remote_schema_field))
	//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
	$arrFieldCompare = array();

	foreach($remote_schema_field AS $field_key => $field_value) {
		if(array_search($field_key, $table_fields) === false)
			continue;

		if(array_key_exists($field_key, $exclude_fields)) {
			continue;
		} elseif(array_key_exists($field_key, $system_fields)) {
			$arrFieldKey[] = "`" . $field_key ."`";
			$arrFieldValue[] = ($system_fields[$field_key] === null
				? "NULL"
				: $db->toSql($system_fields[$field_key], (is_numeric($system_fields[$field_key]) ? "Number" : "Text"))
			);
			if(!array_key_exists($field_key, $arrFieldConvert) && array_key_exists($field_key, $compare_fields) && $compare_fields[$field_key])
				$arrFieldCompare[] = "`" . $field_key ."` = " . $db->toSql($system_fields[$field_key], (is_numeric($system_fields[$field_key]) ? "Number" : "Text"));

			$addSqlReplace["search"][] = "[" . $field_key . "]";
			$addSqlReplace["replace"][] = $system_fields[$field_key];				
		} elseif(array_key_exists($field_key, $rel_fields)) {
			$arrFieldKey[] = "`" . $field_key ."`";
			$arrFieldValue[] = $db->toSql($rel_fields[$field_key], (is_numeric($rel_fields[$field_key]) ? "Number" : "Text"));

			if(!array_key_exists($field_key, $arrFieldConvert) && (!array_key_exists($field_key, $compare_fields) || (array_key_exists($field_key, $compare_fields) && $compare_fields[$field_key])))
				$arrFieldCompare[] = "`" . $field_key ."` = " . $db->toSql($rel_fields[$field_key], (is_numeric($rel_fields[$field_key]) ? "Number" : "Text"));

			$addSqlReplace["search"][] = "[" . $field_key . "]";
			$addSqlReplace["replace"][] = $rel_fields[$field_key];				
		} else {
			if($service_schema[$service_name]["field"][$field_key]["unic"]) {
				$sSQL = "SELECT `" . $service_name . "`.* 
						FROM `" . $service_name . "` 
						WHERE `" . $field_key ."` = " . $db->toSql($field_value) . " 
							OR `" . $field_key ."` LIKE " . $db->toSql($field_value . "-%");
				$db->query($sSQL);
				if($db->nextRecord()) {
					$field_value .= "-" . ($db->numRows() + 1);
				}
			}
			
			$arrFieldKey[] = "`" . $field_key ."`";
			$arrFieldValue[] = $db->toSql($field_value, (is_numeric($field_value) ? "Number" : "Text"));

			if(!array_key_exists($field_key, $arrFieldConvert) && (!array_key_exists($field_key, $compare_fields) || (array_key_exists($field_key, $compare_fields) && $compare_fields[$field_key])))
				$arrFieldCompare[] = "`" . $field_key ."` = " . $db->toSql($field_value, (is_numeric($field_value) ? "Number" : "Text"));
			
			$addSqlReplace["search"][] = "[" . $field_key . "]";
			$addSqlReplace["replace"][] = $field_value;
		}
	
	}

	$sSQL = "SELECT `" . $service_name . "`.* FROM `" . $service_name . "` WHERE " . implode(" AND ", $arrFieldCompare);
	$db->query($sSQL);

    $is_new = null;
	if($db->nextRecord()) {
		$ID = $db->getField("ID", "Number", true);
        $is_new = false;
	} elseif($allow_insert) {
		$sSQL = "INSERT INTO `" . $service_name . "` (" . implode(",", $arrFieldKey) . ") VALUES (" .  implode(",", $arrFieldValue) . ")";
		$db->execute($sSQL);
		$ID = $db->getInsertID(true);
        $is_new = true;
		//echo $sSQL . "\n\n<br><br>";

		if(is_array($service_schema[$service_name]["sql"]["insert"]) && count($service_schema[$service_name]["sql"]["insert"])) {
			foreach($service_schema[$service_name]["sql"]["insert"] AS $sql_insert) {
				$addSqlReplace["search"][] = "[ID_result]";
				$addSqlReplace["replace"][] = $ID;

				$sql_insert = str_replace($addSqlReplace["search"], $addSqlReplace["replace"], $sql_insert);

				$db->execute($sql_insert);
			}
		}		
	}
	
	return array("ID" => $ID, "is_new" => $is_new);
}
