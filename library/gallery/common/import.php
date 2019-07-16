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
check_function("write");

 
function import() {

}

function get_importcsv_def($out = null, $target = null, $node = null) {
	$db = ffDB_Sql::factory();

	$schema = array(
		"/form/pricelist" => array(
			"label" => ffTemplate::_get_word_by_code("import_form_pricelist")
			, "nodes" => array(
				"table" => "module_form"
				, "key" => "ID"
				, "label" => "IF(display_name = '', name, display_name)"
			)
			, "table" => array(
				"module_form_pricelist" => array(
					"key" => "ID"
					, "type" => "2D"
					, "only_insert" => "sid"
					, "source_fields" => null
					, "fields" => array(
						"ID" => null
						, "ID_module" => "[NODE]"
						, "price" => array(
							"value" => "[VALUE]"
							, "required" => true
							, "validator" => null
							, "extended_type" => "Number"
						)
						, "weight" => array(
							"value" => "[VALUE]"
							, "required" => null
							, "validator" => null
							, "extended_type" => "Number"
						)
					)
				)
				, "module_form_pricelist_detail" => array(
					"key" => "ID"
					, "type" => "3D"
					, "source" => array(
						"value" => array(
							"table" => "module_form_fields"
							, "rel" => array(
								"validator" => array(
									"ID_rel" => "ID_check_control"
									, "table" => "check_control"
									, "ID_table" => "ID"
									, "table_field_name" => "name"
								)
							)
							, "key" => "ID"
							, "label" => "name"
							, "where" => " ID_module = [NODE] AND `type` = 'pricelist'"
						)
					)
					, "fields" => array(
						"ID" => null
						, "ID_form_pricelist" => "[MODULE_FORM_PRICELIST_KEY]"
						, "ID_form_fields" => "[SOURCE_KEY]"
						, "value" => array(
							 "value" => "[VALUE]"
							, "required" => "required"
							, "validator" => "validator"
							, "no_compare" => true
						)
					)
				)
			)
			, "record" => array(
				"url" => FF_SITE_PATH . "/restricted/module/form/extra/pricelist"
				, "key" => "ID"
				, "resources" => array("FormPricelistModify")
				, "ID" => "FormPricelistModify"
				, "title" => ffTemplate::_get_word_by_code("form_pricelist_import")
			)
		)
	
	);
	
		
	if(is_array($target)) {
		foreach($target AS $target_value) {
			if(!array_key_exists($target_value, $schema))
				unset($schema[$target_value]);
		}
	} elseif(strlen($target_value)) {
		$schema = $schema[$target_value];
	}		
	
	switch($out) {
		case "record":
			if(is_array($schema) && count($schema)) {
				foreach($schema AS $schema_key => $schema_data) {
					$res[] = $schema_data["record"];
				}
			}
			break;
		case "table":
		case "table_multi_pairs":
			$res = array();
			if(is_array($schema) && count($schema)) {
				foreach($schema AS $schema_key => $schema_data) {
					if($out == "table_multi_pairs")
						$res[] = array(new ffData($schema_key), new ffData($schema_data["label"]));
					else
						$res[] = array($schema_key, $schema_data["label"]);
				}
			}
			break;
		case "node":
		case "node_multi_pairs":
			if(is_array($schema) && count($schema)) {
				foreach($schema AS $schema_key => $schema_data) {
					if(array_key_exists("nodes", $schema_data)) {
						if(is_array($schema_data["nodes"])) {
							$sSQL = "SELECT " . $schema_data["nodes"]["key"] . " AS `key`
										, " . $schema_data["nodes"]["label"] . " AS `name`
									FROM " . $schema_data["nodes"]["table"] . "
									WHERE 1
									ORDER BY name;";
							$db->query($sSQL);
							if($db->nextRecord()) {
								do {
									if($out == "node_multi_pairs")
										$res[] = array($db->getField("key"), $db->getField("name"), new ffData($schema_key));
									else
										$res[] = array($db->getField("key", "Number", true), $db->getField("name", "Text", true), $schema_key);
								} while($db->nextRecord());
							}
						
						}
					}
				}
			}
			break;
		case "source_field":
		case "source_field_multi_pairs":
			if(is_array($schema) && count($schema)) {
				foreach($schema AS $schema_key => $schema_data) {	
					if(array_key_exists("table", $schema_data)) {
						if(is_array($schema_data["table"])) {
							foreach($schema_data["table"] AS $table_key => $table_data) {
								$res["structure"]["tbl"][$table_key] = array(
									"key" => $table_data["key"]
									, "value" => null
									, "type" => $table_data["type"]
									, "only_insert" => $table_data["only_insert"]
								);

								if(array_key_exists("fields", $table_data) && is_array($table_data["fields"]) && count($table_data["fields"])) {
									if(array_key_exists("source", $table_data) && is_array($table_data["source"])) {
										foreach($table_data["source"] AS $field_name => $field_data) {
											if(!array_key_exists($field_name, $table_data["fields"]))
												continue;

											$arrField = array();
											$sSQL_Field = "";
											$sSQL_Join = "";
											$sSQL_Where = "";
											
											if(array_key_exists("required", $table_data["fields"][$field_name]) && $table_data["fields"][$field_name]["required"] !== null) {
												if($table_data["fields"][$field_name]["required"] === true) {
													$arrField["required"] = " 1 AS `required`";
												} elseif($table_data["fields"][$field_name]["required"] === false) {
													$arrField["required"] = " 0 AS `required`";
												} else {
													$arrField["required"] = " `" . $field_data["table"] . "`.`" . $table_data["fields"][$field_name]["required"] . "` AS `required`";
												}
											}

											if(array_key_exists("validator", $table_data["fields"][$field_name]) && $table_data["fields"][$field_name]["validator"] !== null) {
												$arrField["validator"] = " `" . $field_data["table"] . "`.`" . $table_data["fields"][$field_name]["validator"] . "` AS `validator`";
											}

											if(array_key_exists("rel", $field_data) && is_array($field_data["rel"]) && count($field_data["rel"])) {
												foreach($field_data["rel"] AS $rel_field => $rel_condition) {
													$arrField[$rel_field] = " `" . $rel_condition["table"] . "`.`"  . $rel_condition["table_field_name"] . "` AS `" . $rel_field . "`";
													$sSQL_Join .= " LEFT JOIN `" . $rel_condition["table"] . "` ON `" . $field_data["table"] . "`.`" . $rel_condition["ID_rel"] . "` = `" . $rel_condition["table"] . "`.`" . $rel_condition["ID_table"] . "` ";
												}
											}
											if(count($arrField))
												$sSQL_Field = ", " . implode(", ", $arrField);

											$sSQL_Where = str_replace("[NODE]", $db->toSql($node), $field_data["where"]);
											if(strlen($sSQL_Where))
												$sSQL_Where = " AND " . $sSQL_Where;
											
											$sSQL = "SELECT `" . $field_data["table"] . "`.`" . $field_data["key"] . "` AS `key`
														, `" . $field_data["table"] . "`.`" . $field_data["label"] . "` AS `name`
														$sSQL_Field
													FROM `" . $field_data["table"] . "`
														$sSQL_Join
													WHERE 1 
														$sSQL_Where
													ORDER BY name;";
											$db->query($sSQL);
											if($db->nextRecord()) {
												do {
													$res["structure"][$db->getField("key", "Number", true)] = array(
														"table" => $table_key
														, "label" => $db->getField("name", "Text", true)
													);

													$target = array();
													foreach($table_data["fields"] AS $actual_field_key => $actual_field_data) {
														if($actual_field_data) {
															if(is_array($actual_field_data)) {
																$target[$actual_field_key]["value"] = $actual_field_data["value"];
																
																if($actual_field_key != $field_name) {
																	if(array_key_exists("required", $actual_field_data) && $actual_field_data["required"] !== null)
																		$res["structure"][$db->getField("key", "Number", true)]["required"] = $actual_field_data["required"];
																	if(array_key_exists("validator", $actual_field_data) && strlen($actual_field_data["validator"]))
																		$res["structure"][$db->getField("key", "Number", true)]["validator"] = explode(",", $actual_field_data["validator"]);
																	if(array_key_exists("extended_type", $actual_field_data) && $actual_field_data["extended_type"] !== null)
																		$res["structure"][$db->getField("key", "Number", true)]["extended_type"] = $actual_field_data["extended_type"];
																} else {
																	if(array_key_exists("required", $actual_field_data))
																		$res["structure"][$db->getField("key", "Number", true)]["required"] = $db->getField("required", "Text", true);
																	if(array_key_exists("validator", $actual_field_data) && strlen($db->getField("validator", "Text", true)))
																		$res["structure"][$db->getField("key", "Number", true)]["validator"] = explode(",", $db->getField("validator", "Text", true));
																	if(array_key_exists("extended_type", $actual_field_data))
																		$res["structure"][$db->getField("key", "Number", true)]["extended_type"] = $db->getField("extended_type", "Text", true);
																}
															} else {
																$target[$actual_field_key]["value"] = $actual_field_data;
															}	
															
															$target[$actual_field_key]["value"] = str_replace("[SOURCE_KEY]", $db->getField("key", "Number", true), $target[$actual_field_key]["value"]);

														}
													}

													$res["structure"][$db->getField("key", "Number", true)]["target"] = $target;

													if($out == "source_field_multi_pairs")
														$res["fields"][strtolower($db->getField("name", "Text", true))] = array($db->getField("key"), new ffData(ucwords($db->getField("name", "Text", true)) . ($res["structure"][$db->getField("key", "Number", true)]["required"] ? "*" : "")));
													else
														$res["fields"][strtolower($db->getField("name", "Text", true))] = array($db->getField("key", "Number", true), ucwords($db->getField("name", "Text", true)) . ($res["structure"][$db->getField("key", "Number", true)]["required"] ? "*" : ""));

												} while($db->nextRecord());
											}
										}
									} else {
										foreach($table_data["fields"] AS $field_key => $field_value) {
											if($field_value){
												if(is_array($field_value)) {
													if(strpos($field_value["value"], "[NODE]") !== false) {
														$res["structure"]["hidden"][$field_key] = array(
															"table" => $table_key
															, "value" => str_replace("[NODE]", $node, $field_value["value"])
														);
													} else {
														$res["structure"][$field_key] = array(
															"table" => $table_key
															, "label" => ucwords($field_key)
															, "target" => array(
																$field_key => array(
																	"value" => $field_value["value"]
																)
															)
														);
														if(array_key_exists("required", $field_value) && $field_value["required"] !== null)
															$res["structure"][$field_key]["required"] = $field_value["required"];
														if(array_key_exists("validator", $field_value) && strlen($field_value["validator"]))
															$res["structure"][$field_key]["validator"] = explode(",", $field_value["validator"]);
														if(array_key_exists("extended_type", $field_value) && $field_value["extended_type"] !== null)
															$res["structure"][$field_key]["extended_type"] = $field_value["extended_type"];

														if($out == "source_field_multi_pairs")
															$res["fields"][strtolower($field_key)] = array(new ffData($field_key), new ffData(ucwords($field_key) . ($res["structure"][$field_key]["required"] ? "*" : "")));
														else
															$res["fields"][strtolower($field_key)] = array($field_key, ucwords($field_key) . ($res["structure"][$field_key]["required"] ? "*" : ""));
															
													}
												} else {
													if(strpos($field_value, "[NODE]") !== false) {
														$res["structure"]["hidden"][$field_key] = array(
															"table" => $table_key
															, "value" => str_replace("[NODE]", $node, $field_value)
														);
													} else {
														if($out == "source_field_multi_pairs")
															$res["fields"][strtolower($field_key)] = array(new ffData($field_key), new ffData(ucwords($field_key)));
														else
															$res["fields"][strtolower($field_key)] = array($field_key, ucwords($field_key));
														
														$res["structure"][$field_key] = array(
															"table" => $table_key
															, "target" => array(
																$field_key => array(
																	"value" => $field_value
																)
															)
														);
													}
												}
											}
										}
									}
								}
							}
						}
					}
					$res["record"] = $schema_data["record"];
				}
			}
			if(is_array($res["fields"]))
				ksort($res["fields"]);

			break;
		default:
  			$res = $schema;
	}
	
	return $res;	
}

 function get_importcsv_fields($first_col = array(), $target_table = null, $target_node = null) {
	$db = ffDB_Sql::factory();

	if($target_table === null) {
		$target_table = get_session("importcsvtarget");
	} else {
		set_session("importcsvtarget", $target_table);
	}

	if($target_node === null) {
		$target_node = get_session("importcsvnode");
	} else {
		set_session("importcsvnode", $target_node);
	}

	$res = get_importcsv_def("source_field_multi_pairs", $target_table, $target_node);

	$found_match = 0;
	if(is_array($first_col) && count($first_col) && is_array($field) && count($field)) {
		foreach($field AS $key => $value) {
			if(array_search(ffCommon_url_rewrite($field[$key][0]->getValue()), $first_col)) {
				$found_match++;
			}
		}
	}
	if($found_match > 0 && (($found_match * 2) - count($first_col)) >= 0) {
		$skip_first_col = true;
	} else {
		$skip_first_col = false;
	}
		
	return array("record" => $res["record"]
				, "structure" => $res["structure"]
				, "field" => $res["fields"]
				, "skip_first_col" => $skip_first_col
	);
}

function importcsv_open($filename = null, $sep_field = null, $page = null, $limit = null, $ref = null) {
	$globals = ffGlobals::getInstance("wizard");
	
	if($filename === null) {
		$filename = get_session("importcsv");
	} else {
		set_session("importcsvsep", $filename);
	}
	if($sep_field === null) {
		$sep_field = get_session("importcsvsep");
	} else {
		set_session("importcsvsep", $sep_field);
	}
	
	if($page === null) {
		$page = (isset($_REQUEST["page"])
					? $_REQUEST["page"]
					: (get_session("importcsvpage") > 0
						? get_session("importcsvpage")
						: 1
					)
				);
	}
	set_session("importcsvpage", $page);

	if($limit === null) {
		$limit = (isset($_REQUEST["limit"])
					? $_REQUEST["limit"]
					: (get_session("importcsvlimit") > 0
						? get_session("importcsvlimit")
						: 100
					)
				);
	}

	set_session("importcsvlimit", $limit);
		
	if($ref === null) {
		$ref = (isset($_REQUEST["ref"])
					? $_REQUEST["ref"]
					: (get_session("importcsvref") > 0
						? get_session("importcsvref")
						: time()
					)
				);
	}
	set_session("importcsvref", $ref);

	$count_data = 0;
	$globals->import_fields = array();
	if(is_file(FF_DISK_UPDIR . "/importcsv/" . $filename)) {
		if(!get_session("importcsvlinetotal") > 0) {
			$linecount = count(file(FF_DISK_UPDIR . "/importcsv/" . $filename));
			set_session("importcsvlinetotal", $linecount);
		}

		$handle = fopen(FF_DISK_UPDIR . "/importcsv/" . $filename, "r");
		if ($handle)
		{
			while (!feof($handle))
			{
				$count_data++;

				$buffer = fgetcsv($handle, 0, $sep_field, '"');
				if(!is_array($buffer))
					continue;
				
		        if($count_data <= ($page - 1) * $limit)
		            continue;
		            
		        if($count_data > (($page - 1) * $limit) + $limit)
		            break;
        					
					
				$globals->import_fields[] = $buffer;
			}
			fclose($handle);
		}
	}
}
function importcsv_exec($csv_rel_field = null, $skip_first_row = null, $page = null, $ref = null) {
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("wizard");
	$db = ffDB_Sql::factory();	

	if($csv_rel_field === null) {
		$csv_rel_field = get_session("importcsv_rel_field");
	} else {
		set_session("importcsv_rel_field", $csv_rel_field);
	}
	if($page === null) {
		$page = get_session("importcsvpage");
	} else {
		set_session("importcsvpage", $page);
	}
	if($ref === null) {
		$ref = get_session("importcsvref");
	} else {
		set_session("importcsvref", $ref);
	}


	$arrData = get_importcsv_fields();
	
	$strError = "";
	$target_tbl = array();
	if(is_array($arrData["structure"]) && count($arrData["structure"])) {
		foreach($arrData["structure"] AS $structure_key => $structure_value) {
			if(array_key_exists("required", $structure_value)
				&& array_search($structure_key, $csv_rel_field) === false
			) {
				$strError .= ffTemplate::_get_word_by_code("field_require") . " " . $structure_value["label"];
			}
		}
	}

	if(!strlen($strError)) {
		if(is_array($arrData["structure"]) && count($arrData["structure"])
			&& array_key_exists("hidden", $arrData["structure"])
			&& is_array($arrData["structure"]["hidden"])
		) {
			foreach($arrData["structure"]["hidden"] AS $hidden_name => $hidden_data) {
				$target_tbl[$hidden_data["table"]]["insert"]["header"][$hidden_name] = "`" . $hidden_name . "`";
				$target_tbl[$hidden_data["table"]]["insert"]["body"][$hidden_name] = $db->toSql(trim(preg_replace('/\p{Cc}+/u', ' ', $hidden_data["value"])));
				$target_tbl[$hidden_data["table"]]["update"]["fields"][$hidden_name] = "`" . $hidden_name . "` = " . $db->toSql(trim(preg_replace('/\p{Cc}+/u', ' ', $hidden_data["value"])));

				if(!(array_key_exists("no_compare", $hidden_data) && $hidden_data["no_compare"]))
					$target_tbl[$hidden_data["table"]]["update"]["compare"][$hidden_name] = "`" . $hidden_name . "` = " . $db->toSql(trim(preg_replace('/\p{Cc}+/u', ' ', $hidden_data["value"])));
			}
		
		}	
	
		if(is_array($globals->import_fields) && count($globals->import_fields)) {
			foreach($globals->import_fields AS $key => $value) {
				if($page == 1 && $key == 0 && $skip_first_row > 0) {
					continue;
				}

				$operations = array();
				$field_insert_key = "";
				$field_insert_value = "";
				$field_update = "";
				$field_compare = "";
				$tofix = "";
				if(is_array($value) && count($value)) {
					foreach($value AS $csv_key => $csv_value) {
						$field_key = $csv_rel_field["field_" . $csv_key];

						if($arrData["structure"][$field_key]["extended_type"]) {
							$csv_value = new ffData($csv_value, $arrData["structure"][$field_key]["extended_type"], FF_LOCALE);
							$csv_value = $csv_value->getValue($arrData["structure"][$field_key]["extended_type"], FF_SYSTEM_LOCALE);
						}
						
						if(array_key_exists($field_key , $arrData["structure"])
							&& array_key_exists("key", $arrData["structure"][$field_key]) 
							&& strlen($arrData["structure"][$field_key]["key"])
						) {
							$real_field_key = $arrData["structure"][$field_key]["key"];
						} else {
							$real_field_key = $field_key;
						}

						if(!strlen($real_field_key))
							continue;

/*
						if(!strlen(trim($csv_value))
							&& array_key_exists($field_key , $arrData["structure"])
							&& array_key_exists("required", $arrData["structure"][$field_key]) 
							&& $arrData["structure"][$field_key]["required"] 
						) {
                            if(strpos($tofix, $real_field_key) === false) {
                                if(strlen($tofix))
                                    $tofix     .=",";

                                $tofix         .= $real_field_key;
                            }
							//$strError = ffTemplate::_get_word_by_code("field_is_empty") . " " . $real_field_key;
							//break;
						}*/							
						
/*
						if(array_key_exists($field_key , $arrData["structure"])
							&& array_key_exists("validator", $arrData["structure"][$field_key])
						) {
							foreach($arrData["structure"][$field_key]["validator"] AS $validator_key => $validator_name) {
								$check_validator = ffValidator::getInstance($validator_name);
								if($check_validator->checkValue(new ffData(trim($csv_value)), "", array()) !== false) {
									if(strpos($tofix, $real_field_key) === false) {
										if(strlen($tofix))
											$tofix 	.=",";

										$tofix 		.= $real_field_key;
									}								
								}
							}
						}*/
						$target_table = $arrData["structure"][$field_key]["table"];
						if(is_array($operations) 
							&& array_key_exists($target_table, $operations)
							&& array_key_exists($field_key, $operations[$target_table])
						) 
							continue;

						if(array_key_exists("target", $arrData["structure"][$field_key])) {
							if($arrData["structure"]["tbl"][$target_table]["type"] == "3D") {
								$row = $field_key;
							} elseif($arrData["structure"]["tbl"][$target_table]["type"] == "2D") {
								$row = "unic";
							}
						
							foreach($arrData["structure"][$field_key]["target"] AS $field_name => $field_data) {
								$operations[$target_table][$row]["insert"]["header"][$field_name] = "`" . $field_name . "`";
								$operations[$target_table][$row]["insert"]["body"][$field_name] = "IFNULL((" . str_replace("[VALUE]", $db->toSql(trim(preg_replace('/\p{Cc}+/u', ' ', $csv_value))), $field_data["value"]) . "), '')";
								
								$operations[$target_table][$row]["update"]["fields"][$field_name] = "`" . $field_name . "` = IFNULL((" . str_replace("[VALUE]", $db->toSql(trim(preg_replace('/\p{Cc}+/u', ' ', $csv_value))), $field_data["value"]) . "), '')";

								if(!(array_key_exists("no_compare", $field_data) && $field_data["no_compare"]))
									$operations[$target_table][$row]["update"]["compare"][$field_name] = "`" . $field_name . "` = IFNULL((" . str_replace("[VALUE]", $db->toSql(trim(preg_replace('/\p{Cc}+/u', ' ', $csv_value))), $field_data["value"]) . "), '')";
							}

							if(array_key_exists($target_table, $target_tbl)
								&& is_array($target_tbl[$target_table])
							) {
								$operations[$target_table][$row] = array_replace_recursive($target_tbl[$target_table], $operations[$target_table][$row]);
							}
							

/*
							if(count($operations[$target_table][$row]["update"]["fields"]) && count($operations[$target_table][$row]["update"]["compare"])) {
								$operations[$target_table][$row]["check"]["sql"] = "
									SELECT `" . $target_table . "`.*
									FROM `" . $target_table . "`
									WHERE " . implode(" AND ", $operations[$target_table][$row]["update"]["compare"]) 
										. ($arrData["structure"]["tbl"][$target_table]["only_insert"]
											? " AND `" . $arrData["structure"]["tbl"][$target_table]["only_insert"] . "` = " . $db->toSql(md5(implode("|", $operations[$target_table][$row]["sid"])))
											: ""
									);

								$operations[$target_table][$row]["update"]["sql"] = "
									UPDATE `" . $target_table . "` SET
										" . implode(", ", $operations[$target_table][$row]["update"]["fields"]) . "
									WHERE " . implode(" AND ", $operations[$target_table][$row]["update"]["compare"]);
							}
							if(count($operations[$target_table][$row]["insert"]["body"])) {
								$operations[$target_table][$row]["insert"]["sql"] = "
									INSERT INTO `" . $target_table . "`
									(
										ID
										, " . implode(", ", $operations[$target_table][$row]["insert"]["header"])
										. ($arrData["structure"]["tbl"][$target_table]["only_insert"]
											? ", `" . $arrData["structure"]["tbl"][$target_table]["only_insert"] . "`"
											: ""
										) . "
									)
									VALUES
									(
										null
										, " . implode(", ", $operations[$target_table][$row]["insert"]["body"]) 
										. ($arrData["structure"]["tbl"][$target_table]["only_insert"]
											? ", " . $db->toSql(md5(implode("|", $operations[$target_table][$row]["sid"])))
											: ""
										) . "
									)";
							}	*/
														
						}
						
						
						
						
						
						
						
						
						/*
						
						if(array_key_exists($field_key , $arrData["structure"])
							&& array_key_exists("value", $arrData["structure"][$field_key]) 
							&& strlen($arrData["structure"][$field_key]["value"])
						) {
							$real_field_value = "IFNULL((" . str_replace("[VALUE]", $db->toSql(trim($csv_value)), $arrData["structure"][$field_key]["value"]) . "), '')";
						} else {
							$real_field_value = $db->toSql(trim($csv_value));
						}						
						
						if(strlen($field_insert_key))
							$field_insert_key .= ", ";

						$field_insert_key 	.= "`" . $real_field_key . "`";

						if(strlen($field_insert_value))
							$field_insert_value .= ", ";

						$field_insert_value .= $real_field_value;

						if(strlen($field_update))
							$field_update .= ", ";

						$field_update 		.= "`" . $real_field_key . "` = " . $real_field_value;

						if(array_key_exists($field_key , $arrData["structure"])
							&& array_key_exists("require", $arrData["structure"][$field_key])
							&& $arrData["structure"][$field_key]["require"]
                            && strlen(trim($csv_value))
						) { 
							if(strlen($field_compare))
								$field_compare .= " AND ";
								
							$field_compare 		.= "LOWER(`" . $real_field_key . "`) = " . strtolower($real_field_value);
						}*/
						$operations["sid"][] = $field_key . "=" . $csv_value;								
						
					}


					if(is_array($arrData["structure"]["tbl"]) && count($arrData["structure"]["tbl"])) {
						$tbl_key_resolve = array();
						foreach($arrData["structure"]["tbl"] AS $tbl_key => $tbl_data) {
							if(is_array($operations[$tbl_key]) && count($operations[$tbl_key])) {
								foreach($operations[$tbl_key] AS $operations_key => $operations_data) {
									if(count($operations_data["update"]["compare"])) {
										$operations_data["check"]["sql"] = "
											SELECT `" . $tbl_key . "`.*
											FROM `" . $tbl_key . "`
											WHERE " . implode(" AND ", $operations_data["update"]["compare"])
												. ($tbl_data["only_insert"]
													? " AND `" . $tbl_data["only_insert"] . "` = " . $db->toSql(md5(implode("|", $operations["sid"])))
													: ""
												);
									}
									if(count($operations_data["update"]["fields"])) {
										$operations_data["update"]["sql"] = "
											UPDATE `" . $tbl_key . "` SET
												" . implode(", ", $operations_data["update"]["fields"]) . "
											WHERE " . implode(" AND ", $operations_data["update"]["compare"]);
									}								
									if(count($operations_data["insert"]["body"])) {
										$operations_data["insert"]["sql"] = "
											INSERT INTO `" . $tbl_key . "`
											(
												ID
												, " . implode(", ", $operations_data["insert"]["header"]) 
												. ($tbl_data["only_insert"]
													? ", `" . $tbl_data["only_insert"] . "`"
													: ""
												) . "
											)
											VALUES
											(
												null
												, " . implode(", ", $operations_data["insert"]["body"])
												. ($tbl_data["only_insert"]
													? ", " . $db->toSql(md5(implode("|", $operations["sid"])))
													: ""
												) . "
											)";									
									}								
									if(is_array($tbl_key_resolve) && count($tbl_key_resolve)) {
										foreach($tbl_key_resolve AS $tbl_tag => $tbl_ID) {
											if(!$tbl_data["only_insert"]) {
												$operations_data["check"]["sql"] = str_replace("[" . $tbl_tag . "]", $tbl_ID, $operations_data["check"]["sql"]);
												$operations_data["update"]["sql"] = str_replace("[" . $tbl_tag . "]", $tbl_ID, $operations_data["update"]["sql"]);
											}
											$operations_data["insert"]["sql"] = str_replace("[" . $tbl_tag . "]", $tbl_ID, $operations_data["insert"]["sql"]);
										}
									}

									$allow_insert = true;
									if(strlen($operations_data["check"]["sql"])) {
										$db->query($operations_data["check"]["sql"]);
										if($db->nextRecord()) {
											$tbl_key_resolve[strtoupper($tbl_key . "_KEY")] = $db->getField($tbl_data["key"], "Number", true);
//echo $operations_data["update"]["sql"] . "\n\n";											
											if(strlen($operations_data["update"]["sql"])) {
												$db->execute($operations_data["update"]["sql"]);
												$allow_insert = false;
											}
										}
									}
									if($allow_insert && strlen($operations_data["insert"]["sql"])) {
//echo $operations_data["insert"]["sql"] , "\n\n";
										$db->execute($operations_data["insert"]["sql"]);
										$tbl_key_resolve[strtoupper($tbl_key . "_KEY")] = $db->getInsertID(true);
									}
								}
							}							
						}
					}
				}
				/*
				if(!$strError) {
					$ID_node = 0;
					if(strlen($field_compare)) {
						$sSQL = "SELECT " . $arrData["table"] . ".* FROM " . $arrData["table"] . " WHERE " . $field_compare;
                       // echo($sSQL . "<br>"); 
						$db->query($sSQL);
						if($db->nextRecord()) {
							$ID_node = $db->getField("ID", "Number", true);
						}
					}
					$sSQL = "";
					if($ID_node > 0) {
						if(strlen($field_update))
							$sSQL = "UPDATE " . $arrData["table"] . " SET " . $field_update . ", import = " . $db->toSql($ref, "Number") . ", tofix = " . $db->toSql($tofix). " WHERE ID = " . $db->toSql($ID_node, "Number");
					} else {
						if(strlen($field_insert_key) && strlen($field_insert_value))
							$sSQL = "INSERT INTO " . $arrData["table"] . " (ID, " . $field_insert_key . ", import, tofix) VALUES (null, " . $field_insert_value . ", " . $db->toSql($ref, "Number"). ", " . $db->toSql($tofix) . ")";
					}
					if(strlen($sSQL))
						$db->execute($sSQL);
						//echo($sSQL . "<br>");
				}*/
			}

			set_session("importcsvpage", $page + 1);

			set_session("importcsvlineprocessed", ((int) get_session("importcsvlineprocessed")) + count($globals->import_fields));

			//return true;	
			
			if(isset($_REQUEST["importcsv"]) && $cm->isXHR()) {
				echo ffCommon_jsonenc(array("callback" => "continueImportCSV(" . count($globals->import_fields) . ", " . $page . ")"), true);
				exit;
			} else {
				ffRedirect(FF_SITE_PATH . $cm->oPage->page_path . "/step2?" . (isset($_REQUEST["XHR_DIALOG_ID"]) ? "XHR_DIALOG_ID=" . $_REQUEST["XHR_DIALOG_ID"] . "&" : "") . "importcsv=continue" . (isset($_REQUEST["ret_url"]) ? "&ret_url=" . rawurldecode($_REQUEST["ret_url"]) : ""));
			}
		} else {
			set_session("importcsvlineprocessed", "0");
			set_session("importcsvpage", 1);
			
			if(isset($_REQUEST["XHR_DIALOG_ID"])) {
				echo ffCommon_jsonenc(array("callback" => "ff.ffPage.dialog.doAction('" . $_REQUEST["XHR_DIALOG_ID"] . "', 'update', '" . $arrData["record"]["ID"] . "_')"), true);
				exit;
			} else {
				ffRedirect($_REQUEST["ret_url"]);
			}
				
			
		}
	} else {
		return $strError;
	}
	return false;
}



function import_layout_structure($file) {
	$db = ffDB_Sql::factory();

    $sSQL = "TRUNCATE TABLE layout_layer"; 
    $db->execute($sSQL);
    $sSQL = "TRUNCATE TABLE layout_layer_path"; 
    $db->execute($sSQL);
    $sSQL = "TRUNCATE TABLE layout_location"; 
    $db->execute($sSQL);
    $sSQL = "TRUNCATE TABLE layout_location_path"; 
    $db->execute($sSQL);
	
	if(is_file(FF_DISK_PATH . $file)) {
        $arrLayout = new SimpleXMLElement(FF_DISK_PATH . $file, null, true);
        $struct = xml2array($arrLayout);
        
        foreach($struct["layers"]["items"] AS $layer_name => $layer) {
        	$sSQL = "INSERT INTO layout_layer
        			(
        				`ID`
        				, `name`
        				, `width`
        				, `order`
        				, `show_empty`
        				, `fluid`
        				, `wrap`
        				, `class`
        			)
        			VALUES
        			(
        				null
        				, " . $db->toSql($layer["name"]) . "
        				, " . $db->toSql($layer["width"]) . "
        				, " . $db->toSql($layer["order"]) . "
        				, " . $db->toSql($layer["show_empty"]) . "
        				, " . $db->toSql($layer["fluid"]) . "
        				, " . $db->toSql($layer["wrap"]) . " 
        				, " . $db->toSql($layer["class"]) . "
        			)";
        	$db->execute($sSQL);
        	//echo $sSQL . "<br>";
        	$layerRel[$layer["ID"]] = $db->getInsertID(true);
		}

        foreach($struct["layers"]["rules"] AS $rule_name => $rule) {
			$sSQL = "INSERT INTO layout_layer_path
        			(
        				`ID`
        				, `ID_layout_layer`
        				, `path`
        				, `cascading`
        				, `visible`
        				, `width`
        				, `class`
        				, `fluid`
        				, `wrap`
        			)
        			VALUES
        			(
        				null
        				, " . $db->toSql($layerRel[$rule["ID_layout_layer"]]) . "
        				, " . $db->toSql($rule["path"]) . "
        				, " . $db->toSql($rule["cascading"]) . "
        				, " . $db->toSql($rule["visible"]) . "
        				, " . $db->toSql($rule["width"]) . "
        				, " . $db->toSql($rule["class"]) . "
        				, " . $db->toSql($rule["fluid"]) . "
        				, " . $db->toSql($rule["wrap"]) . "
        			)";
        	$db->execute($sSQL);
			//echo $sSQL . "<br>";
		}

        foreach($struct["locations"]["items"] AS $location_name => $location) {
        	$sSQL = "INSERT INTO layout_location
        			(
        				`ID`
        				, `name`
        				, `interface_level`
        				, `ID_layer`
        				, `last_update`
        				, `show_empty`
        				, `is_main`
        			)
        			VALUES
        			(
        				null
        				, " . $db->toSql($location["name"]) . "
        				, " . $db->toSql($location["interface_level"]) . "
        				, " . $db->toSql($layerRel[$location["ID_layer"]]) . "
        				, " . $db->toSql($location["last_update"]) . "
        				, " . $db->toSql($location["show_empty"]) . "
        				, " . $db->toSql($location["is_main"]) . "
        			)";
        	$db->execute($sSQL);
        	//echo $sSQL . "<br>";
        	
        	$locationRel[$location["ID"]] = $db->getInsertID(true);
		}

        foreach($struct["locations"]["rules"] AS $rule_name => $rule) {
			$sSQL = "INSERT INTO layout_location_path
        			(
        				`ID`
        				, `ID_layout_location`
        				, `path`
        				, `cascading`
        				, `visible`
        				, `width`
        				, `default_grid`
        				, `class`
        				, `grid_md`
        				, `grid_sm`
        				, `grid_xs`
        				, `fluid`
        				, `wrap`
        			)
        			VALUES
        			(
        				null
        				, " . $db->toSql($locationRel[$rule["ID_layout_location"]]) . "
        				, " . $db->toSql($rule["path"]) . "
        				, " . $db->toSql($rule["cascading"]) . "
        				, " . $db->toSql($rule["visible"]) . "
        				, " . $db->toSql($rule["width"]) . "
        				, " . $db->toSql($rule["default_grid"]) . "
        				, " . $db->toSql($rule["class"]) . "
        				, " . $db->toSql($rule["grid_md"]) . "
        				, " . $db->toSql($rule["grid_sm"]) . "
        				, " . $db->toSql($rule["grid_xs"]) . "
        				, " . $db->toSql($rule["fluid"]) . "
        				, " . $db->toSql($rule["wrap"]) . "
        			)";
        	//echo $sSQL . "<br>";

        	$db->execute($sSQL);
		}
    }
    
	write_data2file(html_entity_decode($struct["css"]),  FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/css/root.css");
	write_data2file(html_entity_decode($struct["js"]),  FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/javascript/root.js");
			
    return array(
    	"name" => $struct["name"]
    	, "svg" => $struct["svg"]
    	, "description" => $struct["description"]
    );
}