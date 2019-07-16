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
function api_get_anagraph_rel_anagraph($limit_data = null, $params = null, $sort_field = null, $sort_dir = null, $search = null) {
    $db = ffDB_Sql::factory();
    $schema = array();
    


	$sSQL_add_field = "";
    $sSQL_add_field_empty = "";
    $sort = "";
	if(is_array($schema["add_field"]) && count($schema["add_field"])) {
        $sSQL_having = "";
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

	if($params && array_key_exists("category", $params)) {
		$managed_params["category"] = $params["category"];
		unset($params["category"]);
	}
    if(strlen($search) || (is_array($sort_field) && count($sort_field)) || (is_array($params) && count($params))) {
        $sSQL_having = "";
        
        $sSQL = "SELECT * FROM anagraph LIMIT 1";
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
		                if(strlen($sSQL_having))
		                    $sSQL_having .= " OR ";
		                
		                $sSQL_having .= " `" . $field_value->name . "` LIKE '%" . $db->toSql(str_replace(" ", "%", $search), "Text", false) . "%' COLLATE utf8_general_ci";
					}
	            }
			}
        }
    }

    
    if(strlen($managed_params["category"])) {
        $sSQL = "SELECT * FROM anagraph_categories";
        $db->query($sSQL);
        if($db->nextRecord()) {
            do {
                if(ffCommon_url_rewrite($db->getField("name")->getValue()) == $managed_params["category"]) {
                    $ID_category = $db->getField("ID")->getValue();
                    break;
                }
            } while($db->nextRecord());
        }
    }
    //anagraph_fields.enable_in_grid = '1' 
    $sSQL = "SELECT DISTINCT
                anagraph_fields.ID 
                , anagraph_fields.name AS name
                , extended_type.name AS extended_type
                , extended_type.ff_name AS ff_extended_type 
            FROM anagraph_fields
                INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
                INNER JOIN anagraph ON anagraph.ID_type = anagraph_fields.ID_type
                INNER JOIN extended_type on extended_type.ID = anagraph_fields.ID_extended_type
            WHERE 1
            	$sSQL_Where_params
                " . (strlen($managed_params["category"])
                    ? ($managed_params["category"] == "nocategory"
                        ? " AND anagraph.categories = '' "
                        : " AND FIND_IN_SET(" . $db->tosql($ID_category, "Number") . ", anagraph.categories) "
                    )
                    : ""
                ) . "
                " . ($limit_data === null 
                        ? "" 
                        : " AND anagraph.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
                    ) . "    
            ORDER BY anagraph_fields.`order_thumb`";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $arrFormField = array();
        $sSQL_field = "";
        do {
            $key_field = md5($db->getField("name", "Text")->getValue());
            
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
                $sSQL_field .= ", (SELECT 
                                    GROUP_CONCAT(IF(anagraph_rel_nodes_fields.`description_text` = ''
                                            , anagraph_rel_nodes_fields.`description`
                                            , anagraph_rel_nodes_fields.`description_text`
                                        )  
                                        SEPARATOR ''
                                    )
                                FROM
                                    anagraph_rel_nodes_fields
                                WHERE
                                    anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                    AND anagraph_rel_nodes_fields.ID_fields IN ( " . $db->tosql($arrFormField_value["ID"], "Text", false) . " )
                                ) AS " . $db->tosql("custom." . ffCommon_url_rewrite($arrFormField_value["name"]));

                if(strlen($search)) {
                    if(strlen($sSQL_having))
                        $sSQL_having .= " OR ";
                    
                    $sSQL_having .= " `" . "custom." . ffCommon_url_rewrite($arrFormField_value["name"]) . "` LIKE '%" . $db->toSql($search, "Text", false) . "%' COLLATE utf8_general_ci";
                }
            }
        }
    }

    $user = Auth::get("user");
    $sSQL = "SELECT anagraph_categories.ID
                    , anagraph_categories.name
                    , anagraph_categories.limit_by_groups 
            FROM anagraph_categories
            ORDER BY anagraph_categories.name";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $allowed_ana_cat = "";
        do {
            $limit_by_groups = $db->getField("limit_by_groups")->getValue();
            if(strlen($limit_by_groups)) {
                $limit_by_groups = explode(",", $limit_by_groups);
                
                if(array_search($user->acl, $limit_by_groups) !== false) {
                    if(strlen($allowed_ana_cat))
                        $allowed_ana_cat .= ",";

                    $allowed_ana_cat .= $db->getField("ID", "Number", true);
                }
            } else {
                if(strlen($allowed_ana_cat))
                    $allowed_ana_cat .= ",";

                $allowed_ana_cat .= $db->getField("ID", "Number", true);
            }
        
        } while($db->nextRecord());
    }

    $sSQL = "SELECT anagraph.*
            $sSQL_field
            , IF(anagraph.uid > 0 AND " . CM_TABLE_PREFIX . "mod_security_users.avatar <> ''
                , " . CM_TABLE_PREFIX . "mod_security_users.avatar
                , anagraph.avatar
            ) AS avatar
            , (" . (Cms::env("AREA_ECOMMERCE_SHIPPING_LIMIT_STATE") > 0
                ? "''"
                : "IF(anagraph.billstate > 0
                            , (SELECT
									IFNULL(
										(SELECT " . FF_PREFIX . "international.description
											FROM " . FF_PREFIX . "international
											WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
												AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
												AND " . FF_PREFIX . "international.is_new = 0
                                            ORDER BY " . FF_PREFIX . "international.description
                                            LIMIT 1
										)
										, " . FF_SUPPORT_PREFIX . "state.name
									) AS description
                                FROM
                                    " . FF_SUPPORT_PREFIX . "state
                                    LEFT JOIN " . FF_PREFIX . "international ON " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
                                WHERE " . FF_SUPPORT_PREFIX . "state.ID = anagraph.billstate                                
                                ORDER BY description
                                )
                            , ''
                        )
                    "
            ) . ") AS billstate_label
            , IF(anagraph.uid > 0 AND " . CM_TABLE_PREFIX . "mod_security_users.email <> ''
                , " . CM_TABLE_PREFIX . "mod_security_users.email
                , anagraph.email
            ) AS anagraph_email
            , anagraph.tel AS anagraph_tel
            , (IF(anagraph.uid > 0
                , IF(anagraph.billreference = ''
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
                , IF(anagraph.billreference = ''
                    , CONCAT(anagraph.name, ' ', anagraph.surname)
                    , anagraph.billreference
                )
            )) AS reference
            , GROUP_CONCAT(anagraph_categories.name ORDER BY anagraph_categories.name SEPARATOR ',') AS categories_name 
        FROM anagraph 
            LEFT JOIN anagraph_categories ON FIND_IN_SET(anagraph_categories.ID, anagraph.categories)
            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
        WHERE 1
            " . ($limit_data === null 
                ? "" 
                : " AND anagraph.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
            ) . "
            " . (strlen($allowed_ana_cat) && $managed_params["category"] != "nocategory"
                ? " AND anagraph_categories.ID IN (" . $db->toSql($allowed_ana_cat, "Text", false) . ")"
                : ""
            ) . "
            " . (strlen($managed_params["category"])
                ? ($managed_params["category"] == "nocategory"
                    ? " AND anagraph.categories = '' "
                    : " AND FIND_IN_SET(" . $db->tosql($ID_category, "Number")  . ", anagraph.categories) "
                )
                : "" 
            ) . "
        GROUP BY anagraph.ID
		HAVING 1 " . (strlen($sSQL_having)
			            ? " AND (" . $sSQL_having . ")"
			            : "" 
			        ) . "
        ORDER BY " . ($sort === null 
                ? "" 
                : (strpos($sort, "`") === false && strpos($sort, ".") !== false ? substr($sort, strpos($sort, ".") + 1) : $sort) .  
                	($sort_dir === null ? "" : " " . $sort_dir) . ", "
            ) . "anagraph.last_update DESC, billreference";        

    return array("schema" => $schema
                , "sql" => $sSQL
        );
}
