<?php
function api_get_layout($limit_data = null, $params = null, $sort_field = null, $sort_dir = null, $search = null) {
    $db = ffDB_Sql::factory();
    $schema = array();
    $sort = null;

    $sSQL_limit_field = "";
    if(is_array($limit_data)) {
		if(array_key_exists("field", $limit_data) && is_array($limit_data["field"])) {
			if(array_key_exists("id", $limit_data["field"]) && strlen($limit_data["field"]["id"])) {
				$real_limit_data_id = str_replace("custom.", "", $limit_data["field"]["id"]);
				
				if(strlen($sSQL_limit_field))
					$sSQL_limit_field .= " OR ";
				
				$sSQL_limit_field .= "module_form_fields.ID IN(" . $real_limit_data_id . ")";
				
				$arrLimitFieldID = explode(",", $real_limit_data_id);	
			}
			if(array_key_exists("name", $limit_data["field"]) && strlen($limit_data["field"]["name"])) {
				$real_limit_data_name = str_replace("custom.", "", $limit_data["field"]["name"]);
				
				if(strlen($sSQL_limit_field))
					$sSQL_limit_field .= " OR ";
					
				$sSQL_limit_field .= "module_form_fields.name IN(" . $real_limit_data_name . ")";
			}
			if(strlen($sSQL_limit_field)) {
				$sSQL_limit_field = " AND (" . $sSQL_limit_field . ")";
			}
		}
    }

    
    if($ID_form	> 0) {
    	$sSQL = "SELECT module_form.* FROM module_form WHERE module_form.ID = " . $db->toSql($ID_form, "Number");
	} elseif(strlen($limit_data["key"]) && strlen($limit_data["value"])) {
		$sSQL = "SELECT module_form.ID AS ID
					, module_form.enable_revision AS enable_revision
				FROM module_form
					INNER JOIN module_form_nodes ON module_form.ID = module_form_nodes.ID_form
				WHERE module_form_nodes.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")";
	}
	
	if(strlen($sSQL)) {
	    $db->query($sSQL);
	    if($db->nextRecord()) {
    		$ID_form = $db->getField("ID", "Number", true);
			$enable_revision = $db->getField("enable_revision", "Number", true);
		}  

		if($enable_revision) {
			$schema["add_field"]["revision"] = "(SELECT COUNT(DISTINCT module_form_revision.ID) AS count_revision 
													FROM module_form_revision
														INNER JOIN module_form_rel_nodes_fields ON module_form_rel_nodes_fields.ID_module_revision = module_form_revision.ID
													WHERE module_form_rel_nodes_fields.ID_form_nodes = module_form_nodes.ID
												) AS revision";
			$schema["add_field"]["revision_last_created"] = "(SELECT module_form_revision.created AS created 
										                        FROM module_form_revision
										                            INNER JOIN module_form_rel_nodes_fields ON module_form_rel_nodes_fields.ID_module_revision = module_form_revision.ID
										                        WHERE module_form_rel_nodes_fields.ID_form_nodes = module_form_nodes.ID
										                        ORDER BY module_form_revision.created DESC
										                        LIMIT 1
										                    ) AS revision_last_created";
			$schema["add_field"]["revision_tag"] = "(SELECT module_form_revision.tag AS tag 
							                            FROM module_form_revision
							                                INNER JOIN module_form_rel_nodes_fields ON module_form_rel_nodes_fields.ID_module_revision = module_form_revision.ID
							                            WHERE module_form_rel_nodes_fields.ID_form_nodes = module_form_nodes.ID
							                            ORDER BY module_form_revision.created DESC
							                            LIMIT 1
							                        ) AS revision_tag";
		}
	    
	    $sSQL_add_field = "";
	    $sSQL_add_field_empty = "";
		if(is_array($schema["add_field"]) && count($schema["add_field"])) {
			foreach($schema["add_field"] AS $add_field_key => $add_field_value) {
				$sSQL_add_field .= ", " . $add_field_value;
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

		if(array_key_exists("category", $params)) {
			$managed_params["category"] = $params["category"];
			unset($params["category"]);
		}
		if(array_key_exists("revision", $params)) {
			$managed_params["revision"] = $params["revision"];
			unset($params["revision"]);
		}
	    if(strlen($search) || (is_array($sort_field) && count($sort_field)) || (is_array($params) && count($params))) {
	        $sSQL_having = "";
	        
	        $sSQL = "SELECT module_form_nodes.* 
        				$sSQL_add_field_empty
        			FROM module_form_nodes LIMIT 1";
	        $db->query($sSQL);
	        if(is_array($db->fields) && count($db->fields)) {
			    if(is_array($params) && count($params)) {
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
			                if(strlen($sSQL_having))
			                    $sSQL_having .= " OR ";
			                
			                $sSQL_having .= " `" . $field_value->name . "` LIKE '%" . $db->toSql(str_replace(" ", "%", $search), "Text", false) . "%' COLLATE utf8_general_ci";
						}
		            }
				}
	        }
	    }

		if(strlen($managed_params["category"])) {
	        $sSQL = "SELECT module_form.*
        			FROM module_form";
	        $db->query($sSQL);
	        if($db->nextRecord()) {
	            do {
	                if(ffCommon_url_rewrite($db->getField("name")->getValue()) == $managed_params["category"]) {
	                    $ID_form = $db->getField("ID")->getValue();
	                    break;
	                }
	            } while($db->nextRecord());
	        }
	    }
		$sSQL = "SELECT 
		            module_form_fields.ID 
		            , module_form_fields.name 
	                , extended_type.name AS extended_type
	                , extended_type.ff_name AS ff_extended_type 
	                , module_form_fields_group.name AS group_name
		        FROM module_form_fields
		            INNER JOIN module_form ON module_form.ID = module_form_fields.ID_module
		            INNER JOIN extended_type on extended_type.ID = module_form_fields.ID_extended_type
		            LEFT JOIN module_form_fields_group ON module_form_fields_group.ID = module_form_fields.ID_form_fields_group
		        WHERE module_form.ID = " . $db->toSql($ID_form, "Number"). "
		            $sSQL_limit_field
		        ORDER BY module_form_fields.`orders`";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
    		//AND module_form_fields.enable_in_grid = '1' 

	        $arrFormField = array();
	        $sSQL_field = "";
	        do {
        		if(is_array($arrLimitFieldID) && count($arrLimitFieldID) && array_search($db->getField("ID", "Number", true), $arrLimitFieldID) !== false) {
					$key_field = $db->getField("ID", "Number", true);
        		} else {
					if(array_key_exists(ffCommon_url_rewrite($db->getField("name", "Text", true)), $arrFormField)) {
						if(strlen($db->getField("group_name", "Text", true)) && !array_key_exists(ffCommon_url_rewrite($db->getField("group_name", "Text", true) . " " . $db->getField("name", "Text", true)), $arrFormField)) {
							$key_field = ffCommon_url_rewrite($db->getField("group_name", "Text", true) . " " . $db->getField("name", "Text", true));
						} else {
							$key_field = $db->getField("ID", "Number", true);
						}
					} else {
						$key_field = ffCommon_url_rewrite($db->getField("name", "Text", true));
					}
        		}
	            
	            if(strlen($arrFormField[$key_field]["ID"]))
	                $arrFormField[$key_field]["ID"] .=", ";

	            $arrFormField[$key_field]["ID"] .= $db->getField("ID", "Number")->getValue();
	            $arrFormField[$key_field]["name"] = ffCommon_url_rewrite($db->getField("name", "Text")->getValue());
	            $arrFormField[$key_field]["extended_type"] = $db->getField("extended_type", "Text")->getValue();
	            $arrFormField[$key_field]["ff_extended_type"] = $db->getField("ff_extended_type", "Text")->getValue();
	            
				api_extend_field_schema($schema["custom"], $arrFormField[$key_field]["extended_type"], $arrFormField[$key_field]["name"]);
	            
	        } while($db->nextRecord());

	        $sSQL_field = "";  
	        if(is_array($arrFormField) && count($arrFormField)) {
	            foreach($arrFormField AS $arrFormField_key => $arrFormField_value) {
            		if(is_array($sort_field) && count($sort_field) && array_key_exists("custom." . $arrFormField_key, $sort_field) !== false) {
            			if(strlen($sort))
            				$sort .= ", ";

						$sort .= $sort_field["custom." . $arrFormField_key];
            		}

			        $sSQL_field .= ", (SELECT 
		        						GROUP_CONCAT(module_form_rel_nodes_fields.value SEPARATOR '')
			                        FROM
			                            module_form_rel_nodes_fields
			                        WHERE
			                            module_form_rel_nodes_fields.ID_form_nodes = module_form_nodes.ID
			                            AND module_form_rel_nodes_fields.ID_form_fields IN ( " . $db->tosql($arrFormField_value["ID"], "Text", false) . " )
			                            " . ($enable_revision
                            				? " AND module_form_rel_nodes_fields.ID_module_revision = " . (isset($managed_params["revision"]) && $params > 0
                            								? $db->toSql($managed_params["revision"], "Number")
                            								: "IF(module_form_nodes.ID_actual_revision > 0
                            										, module_form_nodes.ID_actual_revision
                            										, (SELECT MAX(max_revision.ID_module_revision) 
                            											FROM module_form_rel_nodes_fields AS max_revision
                            											WHERE max_revision.ID_form_nodes = module_form_rel_nodes_fields.ID_form_nodes
                            										)
                            									)"
                            							) . "
                            						"
                            				: ""
			                            ) . "
			                        LIMIT 1
			                        ) AS " . $db->tosql("custom." . $arrFormField_key);

	                if(strlen($search)) {
	                    if(strlen($sSQL_having))
	                        $sSQL_having .= " OR ";
	                    
	                    $sSQL_having .= " `" . "custom." . $arrFormField_key . "` LIKE '%" . $db->toSql($search, "Text", false) . "%' COLLATE utf8_general_ci";
	                }
	            }
	        }
	    }

	    $sSQL = "SELECT module_form_nodes.*
	                $sSQL_field
	                $sSQL_add_field
	             FROM module_form_nodes
	                INNER JOIN module_form ON module_form.ID = module_form_nodes.ID_form
	             WHERE module_form.ID = " . $db->toSql($ID_form, "Number") . "
	             	$sSQL_Where_params
		            " . (strlen($limit_data["key"]) && strlen($limit_data["value"]) 
		                    ? (strpos($limit_data["value"], ",") === false && !is_numeric($limit_data["value"])
                        		? " AND module_form_nodes.`" . $limit_data["key"] . "` LIKE " . $db->toSql("%" . $limit_data["value"] . "%")
                        		: " AND module_form_nodes.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
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
	            ) . "module_form_nodes.created DESC, module_form_nodes.name";        
	}
    return array("schema" => $schema
                , "sql" => $sSQL
        );
}