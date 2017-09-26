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
function api_get_user($limit_data = null, $params = null, $sort_field = null, $sort_dir = null, $search = null) {
    $db = ffDB_Sql::factory();
    
    $sort = null;
    $schema = array("add_field" => array(
		"reference" => " (IFNULL(
			                IF(anagraph.billreference = ''
			                    , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
			                        , IF(CONCAT(anagraph.name, ' ', anagraph.surname) = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
			                            , CONCAT(anagraph.name, ' ', anagraph.surname)
			                            , CONCAT(CONCAT(anagraph.name, ' ', anagraph.surname), ' (', IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username), ')')
			                        )
			                        , IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
			                    )
			                    , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
			                        , CONCAT(anagraph.name, ' ', anagraph.surname)
			                        , CONCAT(anagraph.billreference, ' (', IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username), ')')
			                    )
			                )
			                , IF(" . CM_TABLE_PREFIX . "mod_security_users.username = ''
	                			, " . CM_TABLE_PREFIX . "mod_security_users.email
	                			, " . CM_TABLE_PREFIX . "mod_security_users.username
			                )
			            )) AS reference "       
    ));
    
    $sSQL_limit_field = "";
    if(is_array($limit_data)) {
		if(array_key_exists("field", $limit_data) && is_array($limit_data["field"])) {
			if(array_key_exists("id", $limit_data["field"]) && strlen($limit_data["field"]["id"])) {
				$real_limit_data_id = str_replace("custom.", "", $limit_data["field"]["id"]);

				if(strlen($sSQL_limit_field))
					$sSQL_limit_field .= " OR ";
				
				$sSQL_limit_field .= CM_TABLE_PREFIX . "mod_security_users.ID IN(" . $real_limit_data_id . ")";
				
				$arrLimitFieldID = explode(",", $real_limit_data_id);	
			}
			if(array_key_exists("name", $limit_data["field"]) && strlen($limit_data["field"]["name"])) {
				$real_limit_data_name = str_replace("custom.", "", $limit_data["field"]["name"]);

				if(strlen($sSQL_limit_field))
					$sSQL_limit_field .= " OR ";
					
				$sSQL_limit_field .= CM_TABLE_PREFIX . "mod_security_users.name IN(" . $real_limit_data_name . ")";
			}
			if(strlen($sSQL_limit_field)) {
				$sSQL_limit_field = " AND (" . $sSQL_limit_field . ")";
			}
		}
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

	$managed_params = array();
    if(strlen($search) || (is_array($sort_field) && count($sort_field)) || (is_array($params) && count($params))) {
        $sSQL_having = "";
        
        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.*
        			$sSQL_add_field_empty 
        		FROM " . CM_TABLE_PREFIX . "mod_security_users 
        		LIMIT 1";
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

    
    $sSQL = "SELECT DISTINCT
                " . CM_TABLE_PREFIX . "mod_security_users_fields.ID 
                , " . CM_TABLE_PREFIX . "mod_security_users_fields.field AS name 
            FROM " . CM_TABLE_PREFIX . "mod_security_users_fields
            	INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users
            WHERE 1
            	$sSQL_Where_params
				$sSQL_limit_field
		        " . (strlen($limit_data["key"]) && strlen($limit_data["value"]) 
		                ? (strpos($limit_data["value"], ",") === false && !is_numeric($limit_data["value"])
                        	? " AND " . CM_TABLE_PREFIX . "mod_security_users.`" . $limit_data["key"] . "` LIKE " . $db->toSql("%" . $limit_data["value"] . "%")
                        	: " AND " . CM_TABLE_PREFIX . "mod_security_users.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
		                )
		                : "" 
		            ) . "  
            ORDER BY " . CM_TABLE_PREFIX . "mod_security_users_fields.ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $arrFormField = array();
        $sSQL_field = "";
        do {
        	if(is_array($arrLimitFieldID) && count($arrLimitFieldID) && array_search($db->getField("ID", "Number", true), $arrLimitFieldID)) {
				$key_field = $db->getField("ID", "Number", true);
        	} else {
				$key_field = ffCommon_url_rewrite($db->getField("name", "Text", true));
        	}
            
            if(strlen($arrFormField[$key_field]["name"]))
                $arrFormField[$key_field]["name"] .=", ";

            $arrFormField[$key_field]["ID"] = $db->getField("ID", "Number")->getValue();
            $arrFormField[$key_field]["name"] .= "'" . $db->getField("name", "Text", true) . "'";
            $arrFormField[$key_field]["extended_type"] = $db->getField("extended_type", "Text")->getValue();
            $arrFormField[$key_field]["ff_extended_type"] = $db->getField("ff_extended_type", "Text")->getValue();
            
			api_extend_field_schema($schema["custom"], $arrFormField[$key_field]["extended_type"], $arrFormField[$key_field]["name"]);
            
          } while($db->nextRecord());

        $sSQL_field = "";  
        if(is_array($arrFormField) && count($arrFormField)) {
            foreach($arrFormField AS $arrFormField_key => $arrFormField_value) {
            	if(is_array($sort_field) && count($sort_field) && array_key_exists("custom." . $arrFormField_key, $sort_field)) {
            		if(strlen($sort))
            			$sort .= ", ";

					$sort .= $sort_field["custom." . $arrFormField_key];
            	}
            	
                $sSQL_field .= ", (SELECT 
                                    GROUP_CONCAT(" . CM_TABLE_PREFIX . "mod_security_users_fields.value SEPARATOR '')
                                FROM
                                    " . CM_TABLE_PREFIX . "mod_security_users_fields
                                WHERE
                                    " . CM_TABLE_PREFIX . "mod_security_users_fields.ID_users = " . CM_TABLE_PREFIX . "mod_security_users.ID
                                    AND " . CM_TABLE_PREFIX . "mod_security_users_fields.field IN ( " . $arrFormField_value["name"] . " )
                                ) AS " . $db->tosql("custom." . $arrFormField_key);

                if(strlen($search)) {
                    if(strlen($sSQL_having))
                        $sSQL_having .= " OR ";
                    
                    $sSQL_having .= " `" . "custom." . $arrFormField_key . "` LIKE '%" . $db->toSql($search, "Text", false) . "%' COLLATE utf8_general_ci";
                }
            }
        }
    }

    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.*
            $sSQL_field
			$sSQL_add_field
        FROM " . CM_TABLE_PREFIX . "mod_security_users
        	LEFT JOIN anagraph ON anagraph.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
        WHERE 1
            $sSQL_Where
			" . (strlen($limit_data["key"]) && strlen($limit_data["value"]) 
			        ? (strpos($limit_data["value"], ",") === false && !is_numeric($limit_data["value"])
	                    ? " AND " . CM_TABLE_PREFIX . "mod_security_users.`" . $limit_data["key"] . "` LIKE " . $db->toSql("%" . $limit_data["value"] . "%")
	                    : " AND " . CM_TABLE_PREFIX . "mod_security_users.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
			        )
			        : "" 
			    ) . "
        GROUP BY " . CM_TABLE_PREFIX . "mod_security_users.ID
		HAVING 1 " . (strlen($sSQL_having)
			            ? " AND (" . $sSQL_having . ")"
			            : "" 
			        ) . "
        ORDER BY " . ($sort === null 
                ? "" 
                : (strpos($sort, "`") === false && strpos($sort, ".") !== false ? substr($sort, strpos($sort, ".") + 1) : $sort) .  
                	($sort_dir === null ? "" : " " . $sort_dir) . ", "
            ) . CM_TABLE_PREFIX . "mod_security_users.username";        

    return array("schema" => $schema
                , "sql" => $sSQL
        );
}
