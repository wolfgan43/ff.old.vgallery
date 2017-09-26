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
function resolve_relationship($ID_src, $field_src, $data_source, $data_limit, $ID_lang, $limit = null, $skip_update = false, $allow_relationship = true) {
    $db = ffDB_Sql::factory();
    
    $arrDescription = array();
    $nodes = array();
    
    if($limit === null) {
	    $sSQL = "SELECT vgallery_rel_nodes_fields.ID AS ID
	    			, vgallery_rel_nodes_fields.`description` AS `description`
	    			, vgallery_rel_nodes_fields.`description_text` AS `description_text`
	    			, vgallery_rel_nodes_fields.`limit` AS `limit`
	            FROM vgallery_rel_nodes_fields
	            WHERE vgallery_rel_nodes_fields.ID_nodes = " . $db->toSql($ID_src, "Number") . "
	            	AND vgallery_rel_nodes_fields.ID_fields = " . $db->toSql($field_src, "Number") . "
	            	AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql($ID_lang, "Number") . "
	            ORDER BY vgallery_rel_nodes_fields.params";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	    	$ID_field_description = $db->getField("ID", "Number", ture);
	    	$limit = $db->getField("limit", "Text", true);
		}
    }
    
    $sSQL = "SELECT vgallery.name 
            FROM vgallery
                INNER JOIN vgallery_nodes ON vgallery_nodes.ID_vgallery = vgallery.ID
            WHERE vgallery_nodes.ID = " . $db->toSql($ID_src, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $type_field = $db->getField("name", "Text")->getValue();
        
        $sSQL = "
                SELECT vgallery_nodes.*, vgallery.name AS vgallery_name
                FROM
                    vgallery_nodes
                    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                    INNER JOIN rel_nodes
                        ON 
                        (
                            (
                                rel_nodes.ID_node_src = vgallery_nodes.ID 
                                AND rel_nodes.contest_src = " . $db->toSql($data_source, "Text") . "
                                AND rel_nodes.contest_dst = " . $db->toSql($type_field, "Text") . " 
                                AND rel_nodes.ID_node_dst = " . $db->toSql($ID_src, "Number") . "
                            ) 
                        OR 
                            (
                                rel_nodes.ID_node_dst = vgallery_nodes.ID 
                                AND rel_nodes.contest_dst = " . $db->toSql($data_source, "Text") . "
                                AND rel_nodes.contest_src = " . $db->toSql($type_field, "Text") . " 
                                AND rel_nodes.ID_node_src = " . $db->toSql($ID_src, "Number") . "
                            )
                        )
                    WHERE vgallery_nodes.ID
                        NOT IN 
                        (
                            SELECT vgallery_nodes.ID
                                FROM vgallery_rel_nodes_fields
                                    INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                    INNER JOIN vgallery_nodes ON vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
                                    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                            WHERE
                                vgallery_fields.name = " .  $db->toSql("visible", "Text") . "
                                AND vgallery_type.name = " .  $db->toSql("System", "Text") . "
                                AND vgallery_rel_nodes_fields.ID_lang = " .  $db->toSql($ID_lang, "Number") . "
                                AND vgallery_rel_nodes_fields.description = " . $db->toSql("0", "Text") . "
                        )
                        " . (strlen($limit) 
                                ? " AND rel_nodes.ID IN (" . $db->toSql($limit, "Text", false) . ")" 
                                : "");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $db_desc = ffDB_Sql::factory();
            
            $ID_node = $db->getField("ID", "Number", true);
            do {
            	$key_node = md5($ID_node . "-" . $ID_lang . "-" . $data_limit);
            	if(!array_key_exists($key_node, $arrDescription)) {
            		$description = "";
	                $sSQL = "SELECT DISTINCT 
	                			vgallery_rel_nodes_fields.`description` AS `description`
                				, vgallery_rel_nodes_fields.`ID_fields` AS `ID_fields`
                				, vgallery_rel_nodes_fields.`limit` AS `limit`
	                            , vgallery_fields.*
	                            , vgallery_fields_data_type.name AS data_type
	                        FROM vgallery_rel_nodes_fields 
	                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
	                            INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = vgallery_fields.ID_data_type
	                        WHERE 
                        		" . (strlen($data_limit)
                        			? "`ID_fields` IN (" . $db_desc->toSql($data_limit, "Text", false) . ")"
                        			: 1
                        		) . "
	                            AND `ID_nodes` = " . $db_desc->toSql($ID_node, "Number") . "
	                            AND `ID_lang` = " . $db_desc->toSql($ID_lang, "Number") . "
	                        ORDER BY vgallery_fields.`order_thumb`, vgallery_fields.ID";
	                $db_desc->query($sSQL);
	                if($db_desc->nextRecord()) {
	                    do {
	                        
	                        if(strlen($description))
	                            $description .= " ";
	                            
	                        if($db_desc->getField("data_type", "Text", true) == "data") {
	                            $description .= $db_desc->getField("description", "Text", true);
	                            if(array_search($ID_node, $nodes) === false)
                            		$nodes[] = $ID_node;
	                        } elseif($allow_relationship && $db_desc->getField("data_type")->getValue() == "relationship") {
                        		$res = resolve_relationship($ID_node, $db_desc->getField("ID_fields", "Number", true), $db_desc->getField("data_source", "Text", true), $db_desc->getField("data_limit", "Text", true), $ID_lang, $db_desc->getField("limit", "Text", true), true, false);
	                            $description .= $res["description"];
	                            if(array_search($res["nodes"], $nodes) === false)
                            		$nodes[] = $res["nodes"];
	                        }
	                    } while($db_desc->nextRecord());
	                    
	                    if(array_search($description, $arrDescription) === false)
	                    	$arrDescription[$key_node] = $description;
	                }
				}
            } while($db->nextRecord());
        }
    }
    
    $description = implode(" ", $arrDescription);
    if(!$skip_update) {
//	    			, vgallery_rel_nodes_fields.`nodes` = " . $db->toSql(implode(",", $nodes)) . "
		$sSQL = "DELETE FROM vgallery_rel_nodes_fields 
				WHERE vgallery_rel_nodes_fields.ID_nodes = " . $db->toSql($ID_src, "Number") . "
	            	AND vgallery_rel_nodes_fields.ID_fields = " . $db->toSql($field_src, "Number") . "
	            	AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql($ID_lang, "Number") . "
	            	AND vgallery_rel_nodes_fields.params = ''
	            	AND vgallery_rel_nodes_fields.ID <> " . $db->toSql($ID_field_description, "Number");
	    $db->execute($sSQL);

    	if($ID_field_description > 0) {
			$sSQL = "UPDATE vgallery_rel_nodes_fields SET
						vgallery_rel_nodes_fields.`description` = " . $db->toSql("") . "
	    				, vgallery_rel_nodes_fields.`description_text` = " . $db->toSql($description) . "
	    				, vgallery_rel_nodes_fields.`last_update` = " . $db->toSql(time(), "Number") . "
	    				, vgallery_rel_nodes_fields.`nodes` = " . $db->toSql("") . "
		            WHERE vgallery_rel_nodes_fields.ID = " . $db->toSql($ID_field_description, "Number");
		    $db->execute($sSQL);
		} else {
			$sSQL = "INSERT INTO vgallery_rel_nodes_fields 
					(
						ID
						, `ID_nodes`
						, `ID_fields`
						, `ID_lang`
						, params
						, `description`
						, `description_text`
						, `last_update`
						, `nodes`
					)
					VALUES
					(
						null
						, " . $db->toSql($ID_src, "Number") . "
						, " . $db->toSql($field_src, "Number") . "
						, " . $db->toSql($ID_lang, "Number") . "
						, " . $db->toSql("") . "
						, " . $db->toSql("") . "
						, " . $db->toSql($description) . "
						, " . $db->toSql(time(), "Number") . "
						, " . $db->toSql("") . "
					)";
			$db->execute($sSQL);
		}		
		return $description;
    } else {

		return array("description" => $description
					, "nodes" => $ID_node
				);
    }
    
    
}
