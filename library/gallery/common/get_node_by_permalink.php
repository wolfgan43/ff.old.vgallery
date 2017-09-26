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
function get_node_by_id($ID_node, $field = "smart_url", $type = "vgallery", $ID_lang = LANGUAGE_INSET_ID) {
    $db = ffDB_Sql::factory();
    
    switch ($type) {
        case "anagraph":
            $src["type"] = "anagraph";
            $src["table"] = "anagraph";
            $src["fields"]["smart_url"] = "smart_url";
            $src["lang"] = false;
            break;
        case "gallery":
            $src["type"] = "files";
            $src["table"] = "files";
            $src["fields"]["smart_url"] = "name";
            $src["lang"] = true;
            break;
        case "vgallery":
            $src["type"] = "vgallery";
            $src["table"] = "vgallery_nodes";
            $src["fields"]["smart_url"] = "name";
            $src["lang"] = true;
            break;
        default:
            $src["type"] = $type;
            $src["table"] = $type;
            $src["fields"]["smart_url"] = "smart_url";
            $src["lang"] = true;
    }
    
    if($ID_lang == LANGUAGE_DEFAULT_ID || !$src["lang"]) {
        $sSQL = "SELECT " . $src["table"] . ".*
                    FROM " . $src["table"] . "
                    WHERE " . $src["table"] . ".`ID` = " . $db->toSql($ID_node, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            if(is_array($field)) {
                foreach($field AS $field_value) {
                	if(isset($src["fields"][$field_value]))
                		$res[$field_value] = $db->getField($src["fields"][$field_value], "Text", true);
                	else 
                		$res[$field_value] = $db->getField($field_value, "Text", true);
                }
            } else {
            	if(isset($src["fields"][$field]))
					$res = $db->getField($src["fields"][$field], "Text", true);                	
            	else
                	$res = $db->getField($field, "Text", true);    
            }
        }
    } else {
        if(OLD_VGALLERY) {
            $sSQL = "SELECT " . $src["type"] . "_rel_nodes_fields.description AS field_value
                        , " . $src["type"] . "_fields.name AS field_name
                    FROM " . $src["type"] . "_rel_nodes_fields 
                        INNER JOIN " . $src["type"] . "_fields ON " . $src["type"] . "_fields.ID = " . $src["type"] . "_rel_nodes_fields.ID_fields
                    WHERE " . $src["table"] . "_rel_languages.ID_lang = " . $db->toSql($ID_lang, "Number") . "
                        AND " . $src["type"] . "_rel_nodes_fields.ID_fields = 
                        (
                            SELECT " . $src["type"] . "_fields.ID 
                            FROM " . $src["type"] . "_fields 
                                INNER JOIN " . $src["type"] . "_type ON " . $src["type"] . "_type.ID = " . $src["type"] . "_fields.ID_type 
                                WHERE vgallery_type.name = 'System'
                        )
                        AND " . $src["type"] . "_rel_nodes_fields.ID_nodes = " . $db->toSql($ID_node, "Number");
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    if(is_array($field)) {
                        if(array_key_exists($db->getField("field_name", "Text", true), $field)) {
                         $res[$db->getField("field_name", "Text", true)] = $db->getField("field_value", "Text", true);
                        }
                    } elseif($db->getField("field_name", "Text", true) == $field) {
                        $res = $db->getField("field_value", "Text", true);    
                    }
                } while($db->nextRecord());
            }
        } else {
            $sSQL = "SELECT " . $src["table"] . "_rel_languages.*
                        FROM " . $src["table"] . "_rel_languages
                        WHERE " . $src["table"] . "_rel_languages.`ID` = " . $db->toSql($ID_node, "Number") . "
                            AND " . $src["table"] . "_rel_languages.ID_lang = " . $db->toSql($ID_lang, "Number");
            $db->query($sSQL);
            if($db->nextRecord()) {
				if(is_array($field)) {
	                foreach($field AS $field_value) {
                		if(isset($src["fields_lang"][$field_value]))
                			$res[$field_value] = $db->getField($src["fields_lang"][$field_value], "Text", true);
                		else 
                			$res[$field_value] = $db->getField($field_value, "Text", true);
	                }
	            } else {
            		if(isset($src["fields_lang"][$field]))
						$res = $db->getField($src["fields_lang"][$field], "Text", true);                	
            		else
                		$res = $db->getField($field, "Text", true);    
	            }            
            }
        }
    }
    
    return $res;
}
function get_node_by_permalink($user_path, $field = "ID", $type = "vgallery", $ID_lang = LANGUAGE_INSET_ID) {
    $db = ffDB_Sql::factory();
    
    switch ($type) {
        case "anagraph":
            $src["type"] = "anagraph";
            $src["table"] = "anagraph";
            $src["lang"] = false;
            break;
        case "gallery":
            $src["type"] = "files";
            $src["table"] = "files";
            $src["lang"] = true;
            break;
        case "vgallery":
            $src["type"] = "vgallery";
            $src["table"] = "vgallery_nodes";
            $src["lang"] = true;
            break;
        default:
            $src["type"] = $type;
            $src["table"] = $type;
            $src["lang"] = true;
    }
    
    if($ID_lang == LANGUAGE_DEFAULT_ID || !$src["lang"]) {
        $sSQL = "SELECT " . $src["table"] . ".*
                    FROM " . $src["table"] . "
                    WHERE " . $src["table"] . ".`permalink` = " . $db->toSql($user_path, "Text");
        $db->query($sSQL);
        if($db->nextRecord()) {
            if(is_array($field)) {
                foreach($field AS $field_value) {
                    if($db->record[$field_value])
                        $res[$field_value] = $db->record[$field_value];
                }
            } else {
                $res = $db->getField($field, "Text", true);    
            }
        }
    } else {
        $sSQL = "SELECT " . $src["type"] . "_rel_languages.*
                    FROM " . $src["table"] . "_rel_languages
                    WHERE " . $src["table"] . "_rel_languages.`permalink` = " . $db->toSql($user_path, "Text") . "
                        AND " . $src["table"] . "_rel_languages.ID_lang = " . $db->toSql($ID_lang, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            if(is_array($field)) {
                foreach($field AS $field_value) {
                    if($db->record[$field_value])
                        $res[$field_value] = $db->record[$field_value];
                }
            } else {
                $res = $db->getField($field, "Text", true);    
            }
        }
    }
    
    return $res;
}