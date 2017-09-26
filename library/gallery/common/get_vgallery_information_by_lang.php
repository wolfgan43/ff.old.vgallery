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
function get_vgallery_information_by_lang($name = NULL, $parent, $field_name = NULL, $field_type = NULL, $vgallery = NULL, $type_return = "description", $check = false, $lang = true) {

    $db = ffDB_Sql::factory();

	if(is_numeric($parent) && $parent > 0) {
   		$vgallery_nodes_Sql = " vgallery_nodes.ID = " .  $db->toSql($parent, "Number");
   	} else {
    	$vgallery_nodes_Sql = " vgallery_nodes.parent = " .  $db->toSql($parent, "Text");
   	}
    if($name === NULL) 
        $name = "";
    else 
        $name = " AND vgallery_nodes.name =  " . $db->toSql($name, "Text");

    if($lang === true)
        $lang = LANGUAGE_INSET;    
    

    if($field_type === NULL) {
        $field_type_sql = " vgallery_fields.ID_type ";
    } else {
        if(is_numeric($field_type)) {
            $field_type_sql = $db->toSql($field_type, "Number");
        } else {
            $field_type_sql = " ( SELECT ID FROM vgallery_type WHERE vgallery_type.name = " . $db->toSql($field_type, "Text") . " ) ";
        }
    }

    if($vgallery === NULL) {
        $vgallery_Sql = "";
    } else {
        if(is_numeric($vgallery)) {
            $vgallery_Sql = " AND vgallery.ID = " . $db->toSql($vgallery, "Number");
        } else {
            $vgallery_Sql = " AND vgallery.name = " .  $db->toSql($vgallery, "Text");
        }
    }        


    if(is_array($field_name)) {
        foreach($field_name AS $field_name_key => $field_name_value) {
            $sSQL = "SELECT 
                        " . ($lang === false
                                ? "vgallery_nodes." . $field_name_key . " AS return_value"
                                : "vgallery_rel_nodes_fields." . $type_return . " AS return_value"
                            ) . "
                     FROM vgallery_rel_nodes_fields 
                             " . ($lang !== false 
                                     ? "INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID =  vgallery_rel_nodes_fields.ID_lang"
                                     : ""
                                 ) . "
                             " . ($field_name !== NULL && $lang !== false
                                    ? "INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields" 
                                    : ""
                                 ) . "
                             INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
                             " . ($vgallery_Sql
                                     ? "INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery"
                                     : ""
                                 ) . "
                    WHERE "
                         . $vgallery_nodes_Sql . 
                        ($field_name !== NULL && $lang !== false
                            ? " AND vgallery_fields.name = " . $db->toSql($field_name_key, "Text") . "
                                AND vgallery_fields.ID_type = " . $field_type_sql
                            : "" 
                        ) . "
                        " . ($lang !== false 
                                ? " AND " . FF_PREFIX . "languages.code =  " . $db->toSql($lang, "Text") 
                                : "" 
                            ) . 
                        $name . 
                        $vgallery_Sql
                        . ($lang === false
                                ? " AND vgallery_nodes." . $field_name_key . " <> '' "
                                : " AND vgallery_rel_nodes_fields." . $type_return . " <> '' "
                            );
            $db->query($sSQL);
            if(!$db->numRows()) {
                $sSQL = "SELECT 
                            " . ($lang === false
                                    ? "vgallery_nodes." . $field_name_value . " AS return_value"
                                    : "vgallery_rel_nodes_fields." . $type_return . " AS return_value"
                                ) . "
                         FROM vgallery_rel_nodes_fields 
                                 " . ($lang !== false 
                                         ? "INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID =  vgallery_rel_nodes_fields.ID_lang"
                                         : ""
                                     ) . "
                                 " . ($field_name !== NULL && $lang !== false
                                        ? "INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields" 
                                        : ""
                                     ) . "
                                 INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
                                 " . ($vgallery_Sql
                                         ? "INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery"
                                         : ""
                                     ) . "
                        WHERE "
                             . $vgallery_nodes_Sql . 
                            ($field_name !== NULL && $lang !== false
                                ? " AND vgallery_fields.name = " . $db->toSql($field_name_value, "Text") . "
                                    AND vgallery_fields.ID_type = " . $field_type_sql
                                : "" 
                            ) . "
                            " . ($lang !== false 
                                    ? " AND " . FF_PREFIX . "languages.code =  " . $db->toSql($lang, "Text") 
                                    : "" 
                                ) . 
                            $name . 
                            $vgallery_Sql
                            . ($lang === false
                                ? " AND vgallery_nodes." . $field_name_key . " <> '' "
                                : " AND vgallery_rel_nodes_fields." . $type_return . " <> '' "
                            );
                $db->query($sSQL);
                if($db->numRows()) {
                	break;
				}
            }
        }
    } else {
        $sSQL = "SELECT 
                    " . ($lang === false
                            ? "vgallery_nodes." . $type_return . " AS return_value"
                            : "vgallery_rel_nodes_fields." . $type_return . " AS return_value"
                        ) . "
                 FROM vgallery_rel_nodes_fields 
                         " . ($lang !== false 
                                 ? "INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID =  vgallery_rel_nodes_fields.ID_lang"
                                 : ""
                             ) . "
                         " . ($field_name !== NULL && $lang !== false
                                ? "INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields" 
                                : ""
                             ) . "
                         INNER JOIN vgallery_nodes ON vgallery_nodes.ID = vgallery_rel_nodes_fields.ID_nodes
                         " . ($vgallery_Sql
                                 ? "INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery"
                                 : ""
                             ) . "
                WHERE "
                     . $vgallery_nodes_Sql . 
                    ($field_name !== NULL && $lang !== false
                        ? " AND vgallery_fields.name = " . $db->toSql($field_name, "Text") . "
                            AND vgallery_fields.ID_type = " . $field_type_sql
                        : "" 
                    ) . "
                    " . ($lang !== false 
                            ? " AND " . FF_PREFIX . "languages.code =  " . $db->toSql($lang, "Text") 
                            : "" 
                        ) . 
                    $name . 
                    $vgallery_Sql;
        $db->query($sSQL);
        
    }
        
    if ($db->nextRecord()) {
        if ($check) {
            if($db->getField("return_value")->getValue()) {
                $value = true;
            } else { 
                $value = false;
            }
        } else {
            $value = $db->getField("return_value")->getValue();
        }
    } else {
        if ($check) {
            $value = NULL;
        } else {
            $value = "";
        }
    }

    return $value;
}
