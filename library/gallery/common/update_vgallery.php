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
  function update_vgallery($component, $action) {
  	$db = ffDB_Sql::factory();
    
    $arrTableAlt = array();
	if(check_function("get_schema_def")) {
		$schema = get_schema_def(false);
	}  

	$vgallery_name = $component->user_vars["vgallery_name"];
	$ID_vgallery = $component->user_vars["ID_vgallery"];
	$ID_node = $component->key_fields["ID"]->getValue();

  	if(isset($component->form_fields["parent"])) {
	    $vgallery_parent = $component->form_fields["parent"]->getValue();
	} elseif($component->user_vars["parent_old"]) {
		$vgallery_parent = $component->user_vars["parent_old"];
	} else {
	    $vgallery_parent = "/" . $component->user_vars["vgallery_name"];
	}

	$folder = stripslash($vgallery_parent) . "/" 
			. ($action == "insert" || $component->user_vars["is_clone"]
				? ($component->user_vars["arrSmartUrl"] && is_array($component->user_vars["arrSmartUrl"][LANGUAGE_DEFAULT_ID])
					? ffCommon_url_rewrite(implode(" " , $component->user_vars["arrSmartUrl"][LANGUAGE_DEFAULT_ID]))
					: $component->user_vars["name"]
				)
				: (isset($component->form_fields["seo_" . $lang_code ."_smart_url"])
					? ffCommon_url_rewrite($component->form_fields["seo_" . $lang_code ."_smart_url"]->getValue())
					: $component->user_vars["name_old"]
				)
			);
		
	if(isset($component->form_fields["is_dir"])) {
        $is_dir = $component->form_fields["is_dir"]->getValue();
    } else {
        $is_dir = $component->user_vars["is_dir"];
    }


	if(is_array($component->form_fields) && count($component->form_fields)) {
//	    check_function("resolve_relationship");
	    check_function("get_short_description");

		$arrMetaDescription = array();
	    foreach($component->form_fields AS $field_key => $field_value) 
	    {
	        $enable_smart_url = $field_value->user_vars["smart_url"];
	        $arrValue = null;

	        if($field_value->store_in_db == false
				&& strpos($field_value->id, "field_") === 0
			) {
	            if($field_value->user_vars["data_type"] == "relationship") 
	            {
	            	$arrRelValue = array_fill_keys(array_filter(explode(",", $field_value->value->getValue())), $field_value->user_vars["data_source"]);
					if(is_array($arrRel[$field_value->user_vars["data_source"]]))
						$arrRel[$field_value->user_vars["data_source"]] = array_replace($arrRel[$field_value->user_vars["data_source"]], $arrRelValue);
					else
	            		$arrRel[$field_value->user_vars["data_source"]] = $arrRelValue;
            		
					if(is_array($field_value->user_vars["src"])) {
						$arrRelNew = explode(",",  $field_value->value->getValue());
						$arrRelOld = explode(",", $field_value->value_ori->getValue());
						
						$arrRelchange = array();
						$arrRelInsert = array_filter(array_diff($arrRelNew, $arrRelOld));
						if(count($arrRelInsert))
							$arrRelchange = array_fill_keys($arrRelInsert, "insert");
							
						$arrRelDelete = array_filter(array_diff($arrRelOld, $arrRelNew));
						if(count($arrRelDelete))
							$arrRelchange = $arrRelchange + array_fill_keys($arrRelDelete, "delete");
						
						if(is_array($arrRelchange) && count($arrRelchange)) {
							foreach($arrRelchange AS $rel_key => $rel_action) {
								$arrTmp = array();
								$exec = "insert"; 
								$sSQL = "SELECT " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.*
									FROM " . $component->user_vars["src"]["type"] . "_rel_nodes_fields
						            WHERE `ID_fields` = " . $db->toSql($field_value->user_vars["ID_field"], "Number") . "
						                AND `ID_nodes` = " . $db->toSql($rel_key, "Number") . "
						                " . ($component->user_vars["src"]["field"]["lang"]
				                    		? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
				                    		: ""
						                );
								$db->query($sSQL);
								if($db->nextRecord()) {
									
									$exec = "update"; 
									if($db->getField("limit", "Text", true)) {
										$limit = explode(",", $db->getField("limit", "Text", true));
										
										$arrTmp["limit"] = array_combine($limit, $limit);
										$arrTmp["description"] = $db->getField("description_text", "Text", true);
									}
								}

								
								switch($rel_action) {
									case "insert":
										$arrTmp["limit"][$ID_node] = $ID_node;
										$arrTmp["description"] = trim($arrTmp["description"] . " " . $component->user_vars["header"]);
										break;
									case "delete":
										unset($arrTmp["limit"][$ID_node]);
										$arrTmp["description"] = trim(preg_replace('/\s+/', ' ', str_replace($component->user_vars["header"], "", $arrTmp["description"])));
										break;
									default:
										$exec = false;
								}
								if($exec == "update") {
									$sSQL = "UPDATE      
							            `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
							        SET 
							            " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`description_text` = " . $db->toSql($arrTmp["description"]) . " 
										, " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`last_update` = " . $db->toSql(time(), "Number") . "
	    								, " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`limit` = " . $db->toSql(implode(",", $arrTmp["limit"])) . "
							        WHERE `ID_fields` = " . $db->toSql($field_value->user_vars["ID_field"], "Number") . "
							            AND `ID_nodes` = " . $db->toSql($rel_key, "Number") . "
							            " . ($component->user_vars["src"]["field"]["lang"]
			                    			? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
			                    			: ""
							            );
							        //echo $sSQL . "<br><br>";
					              	$db->execute($sSQL);
								} elseif($exec == "insert") {
									$sSQL = "INSERT INTO  
					                            `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
					                        ( 
					                            `ID`
					                            , `description_text`
					                            , `last_update`
												, `limit`
					                            , `ID_fields`
					                            , `ID_nodes`
					                            " . ($component->user_vars["src"]["field"]["lang"]
		                                			? ", `" . $component->user_vars["src"]["field"]["lang"] . "`"
		                                			: ""
					                            ) . "
					                        )
					                        VALUES
					                        (
					                            null
					                            , " . $db->toSql($arrTmp["description"]) . " 
												, " . $db->toSql(time(), "Number") . "
												, " . $db->toSql(implode(",", $arrTmp["limit"])) . "
					                            , " . $db->toSql($field_value->user_vars["ID_field"], "Number") . " 
					                            , " . $db->toSql($rel_key, "Number") . " 
					                            " . ($component->user_vars["src"]["field"]["lang"]
		                                			? ", " . $db->toSql($field_value->user_vars["ID_lang"])
		                                			: ""
					                            ) . "
					                        )";
					                //echo $sSQL . "<br><br>";
					                $db->execute($sSQL);									
								}
							}
						}
					} elseif(isset($field_value->user_vars["data_resolved"]) && is_array($arrRelValue)) {
						$arrValue["ori"] = "";
						$arrValue["new"] = $field_value->user_vars["data_resolved"];
					
						$sSQL = "SELECT " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.*
								FROM " . $component->user_vars["src"]["type"] . "_rel_nodes_fields
					            WHERE `ID_fields` = " . $db->toSql($field_value->user_vars["ID_field"], "Number") . "
					                AND `ID_nodes` = " . $db->toSql($ID_node, "Number") . "
					                " . ($component->user_vars["src"]["field"]["lang"]
				                    	? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
				                    	: ""
					                );
					    //echo $sSQL . "<br><br>";
						$db->query($sSQL);
						if($db->nextRecord()) {
				            $sSQL = "UPDATE      
				                `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
				            SET 
				                " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`description_text` = " . $db->toSql($arrValue["new"]) . " 
								, " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`last_update` = " . $db->toSql(time(), "Number") . "
	    						, " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`limit` = " . $db->toSql(implode(",", array_keys($arrRelValue))) . "
				            WHERE `ID_fields` = " . $db->toSql($field_value->user_vars["ID_field"], "Number") . "
				                AND `ID_nodes` = " . $db->toSql($ID_node, "Number") . "
				                " . ($component->user_vars["src"]["field"]["lang"]
			                    	? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
			                    	: ""
				                );
			                $db->execute($sSQL);
			               // echo $sSQL . "<br><br>";
						} else {
			                $sSQL = "INSERT INTO  
			                            `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
			                        ( 
			                            `ID`
			                            , `description_text`
			                            , `last_update`
										, `limit`
			                            , `ID_fields`
			                            , `ID_nodes`
			                            " . ($component->user_vars["src"]["field"]["lang"]
		                                	? ", `" . $component->user_vars["src"]["field"]["lang"] . "`"
		                                	: ""
			                            ) . "
			                        )
			                        VALUES
			                        (
			                            null
			                            , " . $db->toSql($arrValue["new"]) . " 
										, " . $db->toSql(time(), "Number") . "
										, " . $db->toSql(implode(",", array_keys($arrRelValue))) . "
			                            , " . $db->toSql($field_value->user_vars["ID_field"], "Number") . " 
			                            , " . $db->toSql($ID_node, "Number") . " 
			                            " . ($component->user_vars["src"]["field"]["lang"]
		                                	? ", " . $db->toSql($field_value->user_vars["ID_lang"])
		                                	: ""
			                            ) . "
			                        )";
			                $db->execute($sSQL);
			                //echo $sSQL . "<br><br>";
						}
//					} else {
//						$str_value = resolve_relationship($ID_node, $field_value->user_vars["ID_field"], $field_value->user_vars["data_source"], $field_value->user_vars["data_limit"], $field_value->user_vars["ID_lang"]);
					} elseif($ID_node > 0 && $field_value->value_ori->getValue()) {
						$sSQL = "UPDATE      
			                    `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
			                SET 
			                    " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`description_text` = " . $db->toSql("") . " 
								, " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`last_update` = " . $db->toSql(time(), "Number") . "
	    						, " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.`limit` = " . $db->toSql("") . "
			                WHERE `ID_fields` = " . $db->toSql($field_value->user_vars["ID_field"], "Number") . "
			                    AND `ID_nodes` = " . $db->toSql($ID_node, "Number") . "
			                    " . ($component->user_vars["src"]["field"]["lang"]
			                    	? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
			                    	: ""
			                    );
		                $db->execute($sSQL);					
					}
				} elseif($field_value->user_vars["data_type"] == "table.alt") {
					if(!$field_value->user_vars["data_limit"])
						continue;
					if($field_value->user_vars["data_source"] == $component->user_vars["src"]["table"])
                        continue;

                    if(!isset($arrTableAlt[$field_value->user_vars["data_source"]]["fields"][$field_value->user_vars["data_limit"]])) {
                        if(is_array($field_value->value)) {
                        	foreach($field_value->value AS $multi_field_key => $multi_field_value) {
		                        $arrTableAlt[$field_value->user_vars["data_source"]]["fields"][$field_value->user_vars["data_limit"] . "_" . $multi_field_key] = $db->toSql($multi_field_value);
		                        $arrTableAlt[$field_value->user_vars["data_source"]]["insert"]["head"][] = "`" . $field_value->user_vars["data_limit"] . "_" . $multi_field_key . "`";
		                        $arrTableAlt[$field_value->user_vars["data_source"]]["insert"]["body"][] = $db->toSql($multi_field_value);
		                        $arrTableAlt[$field_value->user_vars["data_source"]]["update"][] = "`" . $field_value->user_vars["data_limit"] . "_" . $multi_field_key . "` = " .  $db->toSql($multi_field_value);
							}
                        } else {
	                        $arrTableAlt[$field_value->user_vars["data_source"]]["fields"][$field_value->user_vars["data_limit"]] = $db->toSql($field_value->value);
	                        $arrTableAlt[$field_value->user_vars["data_source"]]["insert"]["head"][] = "`" . $field_value->user_vars["data_limit"] . "`";
	                        $arrTableAlt[$field_value->user_vars["data_source"]]["insert"]["body"][] = $db->toSql($field_value->value);
	                        $arrTableAlt[$field_value->user_vars["data_source"]]["update"][] = "`" . $field_value->user_vars["data_limit"] . "` = " .  $db->toSql($field_value->value);
						}
                    }
                    $arrValue["ori"] = $field_value->value_ori->getValue();
					$arrValue["new"] = vgallery_convert_resource($field_value->value->getValue(), $folder);
				} else {
					if($field_value->user_vars["data_type"] == "media") {
					
		                $arrFileValue = explode($field_value->file_separator, $field_value->getValue());
						if(is_array($arrFileValue) && count($arrFileValue)) {
							foreach($arrFileValue AS $file_key => $file_value) {
								if(strlen($file_value) && !is_file(FF_DISK_PATH . $file_value)) {
									$real_file_value = (basename($file_value) ? basename($file_value) : $file_value);

									if(is_file($field_value->getFileFullPath($real_file_value, false))) {
										$tmp_filename = $field_value->getFileFullPath($real_file_value, false);


										$arrFileValue[$file_key] = str_replace($field_value->getFileBasePath(), "", $tmp_filename);
									}
								}
							}
						}
			            $field_data = new ffData(implode(",", $arrFileValue), "Text");
			    		//$field_data = $field_value->value;
			            $field_data_ori = $field_value->value_ori;
					} else { 
		               /*
		                if(
		                    (
		                        $field_value->user_vars["extended_type"] == "Image" 
		                        || $field_value->user_vars["extended_type"] == "Upload" 
		                        || $field_value->user_vars["extended_type"] == "UploadImage" 
		                    )
		                    && strlen($field_value->getValue()) 
		                ) {
		                    if(strpos($field_value->getValue(), "/") !== 0) {
		                        if(is_file($field_value->file_temp_path . "/" . $field_value->getValue())) {
		                            $field_value->setValue(str_replace(DISK_UPDIR, "", $field_value->file_storing_path) . "/" . $field_value->getValue());
		                            //@unlink($component->form_fields[$field_key]->file_temp_path . "/" . $field_value->getValue());
								} elseif(is_file($field_value->file_storing_path . "/" . $field_value->getValue())) {
		                            $field_value->setValue(str_replace(DISK_UPDIR, "", $field_value->file_storing_path) . "/" . $field_value->getValue());
								} else  {
									$field_value->setValue("/" . $field_value->getValue());
								}
							}
		                }*/

		                if($field_value->user_vars["extended_type"] == "GMap" && is_array($field_value->value)) {
		                    $sSQL = "SELECT module_maps_marker.* 
		                            FROM module_maps_marker 
		                            WHERE `ID_node` = " . $db->toSql($ID_node, "Number") . "
		                            	AND `module_maps_marker`.`tbl_src` = " . $db->toSql($component->user_vars["src"]["table"]) . "
										" . ($component->user_vars["src"]["field"]["lang"]
											? " AND `module_maps_marker`.`" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"], "Number")
											: ""
										);
			                $db->query($sSQL);
			                if($db->nextRecord()) {
                            	$ID_marker = $db->getField("ID", "Number", true);
				                $sSQL = "UPDATE 
				                    `module_maps_marker` 
				                SET 
				                    `coords_lat` = " . $db->toSql($field_value->value["lat"]) . " 
				                    , `coords_lng` = " . $db->toSql($field_value->value["lng"]) . " 
				                    , `coords_zoom` = " . $db->toSql($field_value->value["zoom"]) . " 
				                    , `coords_title` = " . $db->toSql($field_value->value["title"]) . " 
                                    , `smart_url` = " . $db->toSql(ffcommon_url_rewrite($field_value->value["title"]->getValue())) . " 
				                WHERE 
				                    `ID_node` = " . $db->toSql($ID_node, "Number") . "
				                    " . ($component->user_vars["src"]["field"]["lang"]
				                    	? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
				                    	: ""
				                    );
			                    $db->execute($sSQL);
			                } else {
			                    $sSQL = "INSERT INTO  
			                                `module_maps_marker` 
			                            ( 
			                                `ID`  
			                                , `ID_node`
			                                " . ($component->user_vars["src"]["field"]["lang"]
			                                	? ", `" . $component->user_vars["src"]["field"]["lang"] . "`"
			                                	: ""
			                                ) . "
			                                , `coords_lat`
			                                , `coords_lng`
			                                , `coords_zoom`
			                                , `coords_title`
			                                , `tbl_src`
                                            , `smart_url`
			                            )
			                            VALUES
			                            (
			                                null
			                                , " . $db->toSql($ID_node, "Number") . " 
			                                " . ($component->user_vars["src"]["field"]["lang"]
			                                	? ", " . $db->toSql($field_value->user_vars["ID_lang"])
			                                	: ""
			                                ) . "
			                                , " . $db->toSql($field_value->value["lat"]) . " 
			                                , " . $db->toSql($field_value->value["lng"]) . " 
			                                , " . $db->toSql($field_value->value["zoom"]) . " 
			                                , " . $db->toSql($field_value->value["title"]) . " 
			                                , " . $db->toSql($component->user_vars["src"]["table"]) . " 
                                            , " . $db->toSql(ffcommon_url_rewrite($field_value->value["title"]->getValue())) . " 
			                            )";
			                    $db->execute($sSQL);
			                }
		                
			                $field_data = new ffData($field_value->value["search"], "Text");
			                $field_data_ori = new ffData($field_value->value_ori["search"], "Text");
		                } elseif($field_value->user_vars["extended_type"] == "Image" 
		                    || $field_value->user_vars["extended_type"] == "Upload" 
		                    || $field_value->user_vars["extended_type"] == "UploadImage" 	                
		                ) {
		                	if(strlen($field_value->getValue())
		                		&& !is_file(DISK_UPDIR . $field_value->getValue())
		                		&& is_file(DISK_UPDIR . $folder . "/" . basename($field_value->getValue()))
		                	) {
		                		$field_value->setValue($folder . "/" . basename($field_value->getValue()));
		                	} 

                            $arrFileValue = explode($field_value->file_separator, $field_value->getValue());
                            if(is_array($arrFileValue) && count($arrFileValue)) {
                                foreach($arrFileValue AS $file_key => $file_value) {
                                    if(strlen($file_value) && !is_file(FF_DISK_PATH . $file_value)) {
                                        $real_file_value = (basename($file_value) ? basename($file_value) : $file_value);

                                        if(is_file($field_value->getFileFullPath($real_file_value, false))) {
                                            $tmp_filename = $field_value->getFileFullPath($real_file_value, false);


                                            $arrFileValue[$file_key] = str_replace($field_value->getFileBasePath(), "", $tmp_filename);
                                        }
                                    }
                                }
                            }
                            $field_data = new ffData(implode(",", $arrFileValue), "Text");
                            
						    //$field_data = $field_value->value;
		                    $field_data_ori = $field_value->value_ori;
						} else {
							if($field_value->value->data_type == "Text")
								$field_data = new ffData(vgallery_convert_resource($field_value->getValue(), $folder), "Text");
							else
		                    	$field_data = $field_value->value;

		                    $field_data_ori = $field_value->value_ori;
						}

						$arrValue["ori"] = $field_data_ori->getValue();
						$arrValue["new"] = $field_data->getValue();
					}

					$sSQL = "SELECT " . $component->user_vars["src"]["type"] . "_rel_nodes_fields.*
							FROM " . $component->user_vars["src"]["type"] . "_rel_nodes_fields
			                WHERE `ID_fields` = " . $db->toSql($field_value->user_vars["ID_field"], "Number") . "
			                    AND `ID_nodes` = " . $db->toSql($ID_node, "Number") . "
			                    " . ($component->user_vars["src"]["field"]["lang"]
			                    	? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
			                    	: ""
			                    );
					$db->query($sSQL);
					if($db->nextRecord()) {
		                $sSQL = "UPDATE      
		                    `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
		                SET 
		                    `description` = " . $db->toSql($field_data) . " 
		                WHERE `ID_fields` = " . $db->toSql($field_value->user_vars["ID_field"], "Number") . "
		                    AND `ID_nodes` = " . $db->toSql($ID_node, "Number") . "
		                    " . ($component->user_vars["src"]["field"]["lang"]
		                    	? " AND `" . $component->user_vars["src"]["field"]["lang"] . "` = " . $db->toSql($field_value->user_vars["ID_lang"])
		                    	: ""
		                    );
	                    $db->execute($sSQL);
					} else {
	                    $sSQL = "INSERT INTO  
	                                `" . $component->user_vars["src"]["type"] . "_rel_nodes_fields` 
	                            ( 
	                                `ID` 
	                                , `description`
	                                , `ID_fields` 
	                                , `ID_nodes` 
	                                " . ($component->user_vars["src"]["field"]["lang"]
	                                	? ", `" . $component->user_vars["src"]["field"]["lang"] . "`"
	                                	: ""
	                                ) . "
	                            )
	                            VALUES
	                            (
	                                null
	                                , " . $db->toSql($field_data) . " 
	                                , " . $db->toSql($field_value->user_vars["ID_field"], "Number") . " 
	                                , " . $db->toSql($ID_node, "Number") . " 
	                                " . ($component->user_vars["src"]["field"]["lang"]
	                                	? ", " . $db->toSql($field_value->user_vars["ID_lang"])
	                                	: ""
	                                ) . "
	                            )";
	                    $db->execute($sSQL);
	                }
	            }

		        if($field_value->user_vars["meta_description"] > 0 && is_array($arrValue) && count($arrValue)) {
					if($field_value->user_vars["disable_multilang"] > 0) {
	                    if(is_array($component->user_vars["lang"]) && count($component->user_vars["lang"])) {
	                    	foreach($component->user_vars["lang"] AS $lang_code => $lang) {
						        if(strlen($arrMetaDescription[$lang["ID"]]["new"][$field_value->user_vars["meta_description"]])) {
						            $arrMetaDescription[$lang["ID"]]["new"][$field_value->user_vars["meta_description"]] .= " ";
						        }
						        $arrMetaDescription[$lang["ID"]]["new"][$field_value->user_vars["meta_description"]] .= $arrValue["new"];
						        
						        if(strlen($arrMetaDescription[$lang["ID"]]["ori"][$field_value->user_vars["meta_description"]])) {
						            $arrMetaDescription[$lang["ID"]]["ori"][$field_value->user_vars["meta_description"]] .= " ";
						        }
						        $arrMetaDescription[$lang["ID"]]["ori"][$field_value->user_vars["meta_description"]] .= $arrValue["ori"];
							}
	                    }
	                } else {		            
		            	if($component->user_vars["src"]["field"]["lang"])
		            		$ID_lang = $field_value->user_vars["ID_lang"];
		            	else 
		            		$ID_lang = LANGUAGE_DEFAULT_ID;
			        
				        if(strlen($arrMetaDescription[$ID_lang]["new"][$field_value->user_vars["meta_description"]])) {
				            $arrMetaDescription[$ID_lang]["new"][$field_value->user_vars["meta_description"]] .= " ";
				        }
				        $arrMetaDescription[$ID_lang]["new"][$field_value->user_vars["meta_description"]] .= $arrValue["new"];
				        
				        if(strlen($arrMetaDescription[$ID_lang]["ori"][$field_value->user_vars["meta_description"]])) {
				            $arrMetaDescription[$ID_lang]["ori"][$field_value->user_vars["meta_description"]] .= " ";
				        }
				        $arrMetaDescription[$ID_lang]["ori"][$field_value->user_vars["meta_description"]] .= $arrValue["ori"];
					}
		        } 
		        
		        $arrFields[$field_key] = $arrValue;
	        } 
	    }
//print_r($arrRel);
//die();
		if(is_array($arrRel) && count($arrRel))
	    {
	    	$arrRelOld = array();
	    	$arrRelNew = array();
			foreach($arrRel AS $data_source => $arrRelNodes) {
				$sSQL = "SELECT rel_nodes.ID
								, IF(ID_node_dst = " . $db->toSql($ID_node, "Number") . " 
									, ID_node_src
									, ID_node_dst
								) AS ID_node
	                        FROM rel_nodes
								WHERE 
								(
									(
										rel_nodes.contest_src = " . $db->toSql($data_source) . "
										AND rel_nodes.contest_dst = " . $db->toSql($vgallery_name) . " 
										AND rel_nodes.ID_node_dst = " . $db->toSql($ID_node, "Number") . " 
									) 
								OR 
									(
										rel_nodes.contest_dst = " . $db->toSql($data_source) . "
										AND rel_nodes.contest_src = " . $db->toSql($vgallery_name) . " 
										AND rel_nodes.ID_node_src = " . $db->toSql($ID_node, "Number") . " 
									)
								)";
				$db->query($sSQL);
				if($db->nextRecord()) {
					do {
						$arrRelOld[$db->getField("ID_node", "Text", true)] = $db->getField("ID", "Text", true);
					} while($db->nextRecord());	
				}
				$arrRelNew = array_replace($arrRelNew, $arrRelNodes);
			}
			
	        $arrRelUnChange = array_intersect_key($arrRelOld, $arrRelNew);
	        $arrRelDelete = array_diff_key($arrRelOld, $arrRelUnChange);
			$arrRelInsert = array_diff_key($arrRelNew, $arrRelUnChange);
			
			if(is_array($arrRelDelete) && count($arrRelDelete)) {
				$rel_nodes_del = implode(",", $arrRelDelete);
	            $sSQL = "DELETE FROM rel_nodes WHERE ID IN(" . $db->toSql($rel_nodes_del, "Text", false) . ")";
				//echo $sSQL . "\n";
				$db->execute($sSQL);
	        }			
			if(is_array($arrRelInsert) && count($arrRelInsert)) {
	            foreach($arrRelInsert AS $ID_rel_node => $rel_data_source) {
					$sSQL = "INSERT INTO 
								rel_nodes
								(
									ID
									, `ID_node_src` 
									, `contest_src` 
									, `ID_node_dst` 
									, `contest_dst` 
									, `cascading`
								)
								VALUES
								(
							        null
								    , " . $db->toSql($ID_node, "Number") . "
								    , " . $db->toSql($vgallery_name, "Text") . "
								    , " . $db->toSql($ID_rel_node, "Number") . "
								    , " . $db->toSql($rel_data_source, "Text") . "
								    , " . ($component->user_vars["src"]["field"]["is_dir"]
								        ? "(SELECT `" . $component->user_vars["src"]["field"]["is_dir"] . "` FROM `" . $component->user_vars["src"]["table"] . "` WHERE `" . $component->user_vars["src"]["table"] . "`.ID = " . $db->toSql($ID_node, "Number") . ")"
								        : "'0'"
								    ) . "
								)";
					//echo $sSQL . "\n";
					$db->execute($sSQL);
	            }
	        }
	    }
	    	    
        if(is_array($arrTableAlt) && count($arrTableAlt)) {
            foreach($arrTableAlt AS $tbl_src => $data) {
                $key = ($schema["db"]["data_source"][$tbl_src]["key"]
                    ? $schema["db"]["data_source"][$tbl_src]["key"]
                    : "ID"
                ); 
                $sSQL = "SELECT `" . $tbl_src . "`.*
                        FROM `" . $tbl_src . "`
                        WHERE `" . $key . "` = " . $db->toSql($ID_node, "Number");
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $sSQL = "UPDATE `" . $tbl_src . "`
                            SET " . implode(", ", $data["update"]) . "
                        WHERE `" . $key . "` = " . $db->toSql($ID_node, "Number");
                } else {
                    $sSQL = "INSERT INTO 
                            `" . $tbl_src . "`
                        (
                            " . implode(", ", $data["insert"]["head"]) . "
                        )
                        VALUES
                        (
                            " . implode(", ", $data["insert"]["body"]) . "
                        )";
                }
                $db->execute($sSQL);
            }
        }
	}

   	if(isset($component->form_fields[$component->user_vars["src"]["field"]["place"]]) 
        && isset($component->form_fields[$component->user_vars["src"]["field"]["ID_place"]]) 
        && (!$component->form_fields[$component->user_vars["src"]["field"]["ID_place"]]->getValue() 
        	|| $component->form_fields[$component->user_vars["src"]["field"]["ID_place"]]->value->getValue() == $component->form_fields[$component->user_vars["src"]["field"]["ID_place"]]->value_ori->getValue() 
        )
        && $component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lat"]->getValue()
        && $component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lng"]->getValue()
        && check_function("get_coords_by_address")
    ) {
   		$arrPlace = get_google_address_info(array(
                "lat" => $component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lat"]->getValue()
                , "lng" => $component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lng"]->getValue()
            ), true, true);

        if($arrPlace["city"]["ID"]) {
   			$arrPrimaryField[$component->user_vars["src"]["field"]["ID_place"]] = $arrPrimaryField[$component->user_vars["src"]["field"]["ID_place"]] = "`" . $component->user_vars["src"]["field"]["ID_place"] . "` = " . $db->toSql($arrPlace["city"]["ID"], "Number");
		}
   	}

   	if(isset($component->form_fields["ID_type"]) && $component->user_vars["src"]["field"]["ID_type"] && array_search($component->form_fields["ID_type"]->getValue(), $component->user_vars["limit_type"]) === false) {
   		$arrNewField = array();
   		$arrDelField = array();
   		$ID_old_type = $component->form_fields["ID_type"]->getValue();
   		$ID_new_type = current($component->user_vars["limit_type"]);
   		
   		$sSQL = "SELECT " . $component->user_vars["src"]["type"] . "_fields.ID 
   					, " . $component->user_vars["src"]["type"] . "_fields.name 
   				FROM " . $component->user_vars["src"]["type"] . "_fields
   				WHERE ID_type = " . $db->toSql($ID_new_type, "Number");
   		$db->query($sSQL);
   		if($db->nextRecord()) {
   			do { 
				$arrNewField[ffCommon_url_rewrite($db->getField("name", "Text", true))]["new"] = $db->getField("ID", "Number", true);
   			} while($db->nextRecord());
   		}

   		$sSQL = "SELECT " . $component->user_vars["src"]["type"] . "_fields.ID 
   					, " . $component->user_vars["src"]["type"] . "_fields.name 
   				FROM " . $component->user_vars["src"]["type"] . "_fields
   				WHERE ID_type = " . $db->toSql($ID_old_type, "Number");
   		$db->query($sSQL);
   		if($db->nextRecord()) {
   			do { 
   				if(isset($arrNewField[ffCommon_url_rewrite($db->getField("name", "Text", true))])) {
					$arrNewField[ffCommon_url_rewrite($db->getField("name", "Text", true))]["old"] = $db->getField("ID", "Number", true);
				} else {
					unset($arrNewField[ffCommon_url_rewrite($db->getField("name", "Text", true))]);
					$arrDelField[ffCommon_url_rewrite($db->getField("name", "Text", true))] = $db->getField("ID", "Number", true);
				}
					
   			} while($db->nextRecord());
   		}

   		if(is_array($arrNewField) && count($arrNewField)) {
   			foreach($arrNewField AS $switchField) {
   				if(!$switchField["old"])
   					continue;

   				$sSQL = "UPDATE " . $component->user_vars["src"]["type"] . "_rel_nodes_fields
   							SET ID_fields = " . $db->toSql($switchField["new"], "Number"). "
   						WHERE `ID_nodes` = " . $db->toSql($ID_node, "Number") . "
   							AND ID_fields = " . $db->toSql($switchField["old"], "Number");
   				$db->execute($sSQL);
   			}
   		}
   		if(is_array($arrDelField) && count($arrDelField)) {
   			foreach($arrDelField AS $ID_del_field) {
   				$sSQL = "DELETE FROM " . $component->user_vars["src"]["type"] . "_rel_nodes_fields
   						WHERE `ID_nodes` = " . $db->toSql($ID_node, "Number") . "
   							AND ID_fields = " . $db->toSql($ID_del_field, "Number");
   				$db->execute($sSQL);
   			}
   		}
   		
   		$arrPrimaryField[$component->user_vars["src"]["field"]["ID_type"]] = "`" . $component->user_vars["src"]["field"]["ID_type"] . "` = " . $db->toSql($ID_new_type, "Number");
   	}

	
	if(is_array($arrPrimaryField) && count($arrPrimaryField)) {
		$sSQL = "UPDATE 
				`" . $component->user_vars["src"]["table"] . "` 
				SET " . implode(", ", $arrPrimaryField) . "
				WHERE `" . $component->user_vars["src"]["table"] . "`.`ID` = " . $db->toSql($ID_node, "Number");
		$db->execute($sSQL);
		
		if($arrPrimaryField[$component->user_vars["src"]["field"]["ID_place"]]
			&& $component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lat"]->getValue()
			&& $component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lng"]->getValue()
			&& $arrPlace["city"]["ID"]
		) {
			if($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["title"]->getValue() != $component->form_fields[$component->user_vars["src"]["field"]["place"]]->value_ori["title"]->getValue()) {
				$sSQL = "DELETE FROM module_maps_marker 
						WHERE module_maps_marker.ID_node = " . $db->toSql($ID_node, "Number") . "
							AND module_maps_marker.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value_ori["title"]->getValue())) . "
							AND module_maps_marker.coords_lat = " . $db->toSql($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value_ori["lat"]) . "
							AND module_maps_marker.coords_lng = " . $db->toSql($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value_ori["lng"]) . "
							AND tbl_src = " . $db->toSql($component->user_vars["src"]["table"]);
				$db->execute($sSQL);
			}


			$sSQL = "SELECT module_maps_marker.ID 
					FROM module_maps_marker 
					WHERE module_maps_marker.ID_node = " . $db->toSql($ID_node, "Number") . "
						AND module_maps_marker.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["title"]->getValue())) . "
						AND tbl_src = " . $db->toSql($component->user_vars["src"]["table"]);
			$db->query($sSQL);
			if(!$db->nextRecord()) {
				$sSQL = "INSERT INTO module_maps_marker
						(
							ID
							, ID_city
							, ID_lang
							, ID_module_maps
							, ID_node
							, coords_lat
							, coords_lng
							, coords_title
							, coords_zoom
							, description
							, smart_url
							, tbl_src
							, cap
							
						)
						VALUES
						(
							null
							, " . $db->toSql($arrPlace["city"]["ID"], "Number") . "
							, " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . "
							, 0
							, " . $db->toSql($ID_node, "Number") . "
							, " . $db->toSql($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lat"]) . "
							, " . $db->toSql($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["lng"]) . "
							, " . $db->toSql($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["title"]) . "
							, " . $db->toSql($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["zoom"]) . "
							, ''
							, " . $db->toSql(ffCommon_url_rewrite($component->form_fields[$component->user_vars["src"]["field"]["place"]]->value["title"]->getValue())) . "
							, " . $db->toSql($component->user_vars["src"]["table"]) . "
							, ''
						)";
				$db->execute($sSQL);
			}		
		}
		
	}

   
    $arrSmartUrl = $component->user_vars["arrSmartUrl"];
	if(is_array($component->user_vars["lang"]) && count($component->user_vars["lang"]) && check_function("update_vgallery_seo")) {
	    foreach($component->user_vars["lang"] AS $lang_code => $lang) {
	        $smart_url = "";
			$stop_words = $lang["stopwords"];

			if(array_key_exists($lang["ID"], $arrSmartUrl) 
			    && array_key_exists("smart_url", $arrSmartUrl[$lang["ID"]]) 
			) {
				if(is_array($arrSmartUrl[$lang["ID"]]["smart_url"]) && count($arrSmartUrl[$lang["ID"]]["smart_url"])) {
				    ksort($arrSmartUrl[$lang["ID"]]["smart_url"]);
				    $smart_url = implode(" ", $arrSmartUrl[$lang["ID"]]["smart_url"]);
				}

				if(is_array($arrSmartUrl[$lang["ID"]]["smart_url_ori"]) && count($arrSmartUrl[$lang["ID"]]["smart_url_ori"])) {
				    ksort($arrSmartUrl[$lang["ID"]]["smart_url_ori"]);
				    $smart_url_ori = implode(" ", $arrSmartUrl[$lang["ID"]]["smart_url_ori"]);
				}
			}
			
			if($component->user_vars["rule_meta_title"]) {
				$tpl_meta = ffTemplate::factory(null);
				$tpl_meta->load_content($component->user_vars["rule_meta_title"], "main");

				foreach ($tpl_meta->DVars["main"] AS $tpl_var) {
					$tpl_meta->set_var($tpl_var, $arrFields["field_" . $lang_code . "_" . ffCommon_url_rewrite($tpl_var)]["new"]);
				}
				$meta_title = $tpl_meta->rpparse("main", false);
			} else {
				$meta_title = strip_tags($smart_url);
				$meta_title_ori = strip_tags($smart_url_ori);
			}
		
			if($component->user_vars["rule_meta_description"]) {
				$tpl_meta = ffTemplate::factory(null);
				$tpl_meta->load_content($component->user_vars["rule_meta_description"], "main");

				foreach ($tpl_meta->DVars["main"] AS $tpl_var) { 
					$tpl_meta->set_var($tpl_var, $arrFields["field_" . $lang_code . "_" . ffCommon_url_rewrite($tpl_var)]["new"]);
				}
				$meta_description = strip_tags($tpl_meta->rpparse("main", false));
			} elseif(count($arrMetaDescription) && is_array($arrMetaDescription[$lang["ID"]]) && count($arrMetaDescription[$lang["ID"]])) {
            	ksort($arrMetaDescription[$lang["ID"]]["new"]);
            	ksort($arrMetaDescription[$lang["ID"]]["ori"]);

	            $meta_description["new"] = strip_tags(implode(" ", $arrMetaDescription[$lang["ID"]]["new"]));
	            $meta_description["ori"] = strip_tags(implode(" ", $arrMetaDescription[$lang["ID"]]["ori"])); 
			} else {
				$meta_description = null;
			}

			$primary_meta = array(
								"smart_url" => ($action == "insert" || $component->user_vars["is_clone"]
									? ffCommon_url_rewrite($smart_url)
									: (isset($component->form_fields["seo_" . $lang_code ."_smart_url"])
										? ffCommon_url_rewrite($component->form_fields["seo_" . $lang_code ."_smart_url"]->getValue())
										: $component->user_vars["name_old"]
									)
								)
								, "title" => (isset($component->form_fields["seo_" . $lang_code ."_title"]) && $component->form_fields["seo_" . $lang_code ."_title"]->getValue() != $meta_title_ori
									? $component->form_fields["seo_" . $lang_code ."_title"]->getValue()
									: $meta_title
								)
								, "header" => (isset($component->form_fields["seo_" . $lang_code ."_header"]) && $component->form_fields["seo_" . $lang_code ."_header"]->getValue() != $smart_url_ori
									? $component->form_fields["seo_" . $lang_code ."_header"]->getValue()
									: $smart_url
								)
								, "robots" => (isset($component->form_fields["seo_" . $lang_code ."_robots"])
									? $component->form_fields["seo_" . $lang_code ."_robots"]->getValue()
									: ""
								)
								, "meta" => (isset($component->form_fields["seo_" . $lang_code ."_meta"])
									? $component->form_fields["seo_" . $lang_code ."_meta"]->getValue()
									: ""
								)
								, "httpstatus" => (isset($component->form_fields["seo_" . $lang_code ."_httpstatus"])
									? $component->form_fields["seo_" . $lang_code ."_httpstatus"]->getValue()
									: ""
								)
								, "canonical" => (isset($component->form_fields["seo_" . $lang_code ."_canonical"])
									? $component->form_fields["seo_" . $lang_code ."_canonical"]->getValue()
									: ""
								)
							);

			if($lang_code == LANGUAGE_DEFAULT) {
				if(!strlen($smart_url)) {
					if(!DISABLE_SMARTURL_CONTROL) {
						$component->tplDisplayError(ffTemplate::_get_word_by_code("smart_url_empty"));
						return true;
					}
		        }
				
				$db->query("SELECT * 
		                    FROM `" . $component->user_vars["src"]["table"] . "`  
		                    WHERE `" . $component->user_vars["src"]["table"] . "`.`" .  $component->user_vars["src"]["field"]["parent"] . "` = " . $db->toSql($vgallery_parent) . "
		                        AND `" . $component->user_vars["src"]["table"] . "`.`" . $component->user_vars["src"]["field"]["name"] . "` = " . $db->toSql($primary_meta["smart_url"]) . "
		                        AND `" . $component->user_vars["src"]["table"] . "` .ID <> " . $db->toSql($ID_node)
		                );
		        if($db->nextRecord()) {
		            $not_unic = true;
		        } else {
		            $not_unic = false;
		        }

		        if(($not_unic || AREA_VGALLERY_ADD_ID_IN_REALNAME) && strpos($primary_meta["smart_url"], "-" . $ID_node) === false && !$is_dir) {
        			$postfix_smart_url = "-" . $ID_node;
		        }
		        
		        
				$default_smart_url = $primary_meta["smart_url"] . $postfix_smart_url;
		        
 				/*$sSQL = "UPDATE 
						`" . $component->user_vars["src"]["table"] . "` 
						SET `" . $component->user_vars["src"]["field"]["name"] . "` = " . $db->toSql($default_smart_url) . "
						WHERE `" . $component->user_vars["src"]["table"] . "`.`ID` = " . $db->toSql($ID_node, "Number");
				$db->execute($sSQL);*/
		    }

			$primary_meta["smart_url"] = $primary_meta["smart_url"] . $postfix_smart_url;

			if(isset($component->form_fields["seo_" . $lang_code ."_description"]) && $component->form_fields["seo_" . $lang_code ."_description"]->getValue() != $meta_description["ori"]) {
				$meta_description = strip_tags($component->form_fields["seo_" . $lang_code ."_description"]->getValue());
			} 
			
		    $arrMetaKeywords = array("new" => array(), "ori" => array());
			$meta_keywords = null;
			if($component->user_vars["tags_in_keywords"] && array_key_exists("tags", $component->form_fields)) {
				if(strlen($component->form_fields["tags"]->value->getValue())) {
					$str_compare_tag = "";
					$arrTags = explode(",", $component->form_fields["tags"]->value->getValue());
				    if(is_array($arrTags) && count($arrTags)) {
				        foreach($arrTags AS $tag_value) {
				        	if(strlen($tag_value)) {
				        		if(is_numeric($tag_value)) {
 									if(strlen($str_compare_tag))
                                		$str_compare_tag .= ",";

		                            $str_compare_tag .= $db->toSql($tag_value, "Number");				        	
								} else {
									$arrMetaKeywords["new"][] = $tag_value;
								}
							}
						}
					}
					if($str_compare_tag) {				
						$sSQL = "SELECT search_tags.name
								FROM search_tags
								WHERE search_tags.code IN(" . $db->toSql($str_compare_tag, "Text", false). ")
									AND search_tags.ID_lang = " . $db->toSql($lang["ID"]);
						$db->query($sSQL);
						if($db->nextRecord()) {
							do {
								$arrMetaKeywords["new"][] = $db->getField("name", "Text", true);
							} while($db->nextRecord());
						}
					}
				}
				if(strlen($component->form_fields["tags"]->value_ori->getValue())) {
					$str_compare_tag = "";
					$arrTags = explode(",", $component->form_fields["tags"]->value_ori->getValue());
				    if(is_array($arrTags) && count($arrTags)) {
				        foreach($arrTags AS $tag_value) {
				        	if(strlen($tag_value)) {
				        		if(is_numeric($tag_value)) {
 									if(strlen($str_compare_tag))
                                		$str_compare_tag .= ",";

		                            $str_compare_tag .= $db->toSql($tag_value, "Number");				        	
								} else {
									$arrMetaKeywords["ori"][] = $tag_value;
								}
							}
						}
					}			
					if($str_compare_tag) {				
						$sSQL = "SELECT search_tags.name
								FROM search_tags
								WHERE search_tags.code IN(" . $db->toSql($str_compare_tag, "Text", false). ")
									AND search_tags.ID_lang = " . $db->toSql($lang["ID"]);
						$db->query($sSQL);
						if($db->nextRecord()) {
							do {
								$arrMetaKeywords["ori"][] = $db->getField("name", "Text", true);
							} while($db->nextRecord());
						}
					}
				}				
				$meta_keywords = array(
					"new" => implode(",", $arrMetaKeywords["new"])
					, "ori"	=> implode(",", $arrMetaKeywords["ori"])
				);
			}
			
			$visible = null;
			if(isset($component->form_fields["system_" . $lang_code . "_visible"]))
				$visible = $component->form_fields["system_" . $lang_code . "_visible"]->getValue();

			$seo_update = update_vgallery_seo($primary_meta, $ID_node, $lang["ID"], $meta_description, $vgallery_parent, $meta_keywords, $visible, $stop_words, $component->user_vars["src"]["seo"], $component->user_vars["src"]["field"]);
	    }
	}
  
  	return $default_smart_url;
  }
  
  function vgallery_convert_resource($content, $full_path) {
  	static $gallery = null;
  	if(!$gallery) {
  		$gallery = glob(DISK_UPDIR . $full_path . "/*", GLOB_ONLYDIR);
  	}

	preg_match_all('#' . SITE_UPDIR . '[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $media);
	if(is_array($media) && count($media[0])) {
		foreach($media[0] AS $media_value) {
			$base_path = SITE_UPDIR;
			$file = $media_value;
		
			if(!is_file(FF_DISK_PATH . $file)) {
				if(is_file(DISK_UPDIR . $full_path . "/" . basename($media_value)))
					$field_path = "";
				elseif(is_file(DISK_UPDIR . $full_path . "/" . basename(ffCommon_dirname($media_value)) . "/" . basename($media_value)))
					$field_path = "/" . basename(ffCommon_dirname($media_value));
			
				if(is_file(DISK_UPDIR . $full_path . $field_path . "/" . basename($media_value)))
					$url_rewrite[$media_value] = $base_path . $full_path . $field_path . "/" . basename($media_value);
					
				if(is_array($gallery) && count($gallery)) {
					foreach($gallery AS $gallery_path) {
						if(is_file($gallery_path . "/" . basename($media_value))) {
							$url_rewrite[$media_value] = $base_path . $full_path . "/" . basename($gallery_path) . "/" . basename($media_value);
							break;
						}
					}
				}
			}
		}
	}
	

	preg_match_all('#' . CM_SHOWFILES . '[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $media);
	if(is_array($media) && count($media[0])) {
		foreach($media[0] AS $media_value) {
			$base_path = CM_SHOWFILES;
			$file = str_replace(CM_SHOWFILES, SITE_UPDIR, $media_value);
		
			if(!is_file(FF_DISK_PATH . $file)) {
				if(is_file(DISK_UPDIR . $full_path . "/" . basename($media_value)))
					$field_path = "";
				elseif(is_file(DISK_UPDIR . $full_path . "/" . basename(ffCommon_dirname($media_value)) . "/" . basename($media_value)))
					$field_path = "/" . basename(ffCommon_dirname($media_value));

				if(is_file(DISK_UPDIR . $full_path . $field_path . "/" . basename($media_value)))
					$url_rewrite[$media_value] = $base_path . $full_path . $field_path . "/" . basename($media_value);

				if(is_array($gallery) && count($gallery)) {
					foreach($gallery AS $gallery_path) {
						if(is_file($gallery_path . "/" . basename($media_value))) {
							$url_rewrite[$media_value] = $base_path . $full_path . "/" . basename($gallery_path) . "/" . basename($media_value);
							break;
						}
					}
				}
			}
		}
	}

	if(is_array($url_rewrite) && count($url_rewrite))
		$content = str_replace(array_keys($url_rewrite), array_values($url_rewrite), $content);


	return $content;
  }