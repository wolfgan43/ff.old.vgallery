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
function api_get_form_revision($limit_data = null, $params = null, $sort_field = null, $sort_dir = null, $search = null) {
    $db = ffDB_Sql::factory();

    $sort = null;
    $schema = array("add_field" => array("counter" => " (@row := @row +1) AS counter"));
    $sSQL_limit_field = "";
    $sSQL_limit_field = "";
    
    if(is_array($limit_data)) {
    	if(array_key_exists("key", $limit_data)) {
	        if($limit_data["key"] == "ID_form_node") {
	            $sSQL_Where = " AND module_form_revision.ID IN (SELECT DISTINCT module_form_rel_nodes_fields.ID_module_revision 
	            												FROM module_form_rel_nodes_fields
	            												WHERE module_form_rel_nodes_fields.ID_form_nodes IN (" . $db->toSql($limit_data["value"], "Text", false) . ")
	            											)";
	            
	            unset($limit_data["key"]);
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
        
        $sSQL = "SELECT module_form_revision.* 
        			$sSQL_add_field_empty
        		FROM module_form_revision LIMIT 1";
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

    $sSQL = "SELECT module_form_revision.* 
                $sSQL_field
                $sSQL_add_field
             FROM module_form_revision, (SELECT @row :=0) AS r
             WHERE 1
             	$sSQL_Where
             	$sSQL_Where_params
	            " . (strlen($limit_data["key"]) && strlen($limit_data["value"]) 
	                    ? (strpos($limit_data["value"], ",") === false && !is_numeric($limit_data["value"])
                        	? " AND module_form_revision.`" . $limit_data["key"] . "` LIKE " . $db->toSql("%" . $limit_data["value"] . "%")
                        	: " AND module_form_revision.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
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
            ) . "module_form_revision.created ASC";        

    return array("schema" => $schema
                , "sql" => $sSQL
        );
}
