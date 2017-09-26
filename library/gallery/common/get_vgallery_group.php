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
function get_vgallery_group($name, $field = "ID", $limit_vgallery_path = null) {
    $db = ffDB_Sql::factory();

    $db->query("SELECT vgallery_groups.*, vgallery_groups_menu.limit_type AS limit_type 
                    " . ($limit_vgallery_path === null
                        ? ""
                        : ", COUNT(vgallery_nodes.ID) AS limit_vgallery "
                    ) . "
                FROM vgallery_groups
                    INNER JOIN vgallery_groups_menu ON vgallery_groups_menu.ID = vgallery_groups.ID_menu
                    " . ($limit_vgallery_path === null
                        ? ""
                        : " INNER JOIN vgallery_groups_fields ON vgallery_groups_fields.ID_group = vgallery_groups.ID
                        	INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_groups_fields.ID_fields
                            INNER JOIN vgallery_nodes ON vgallery_nodes.ID_type = vgallery_fields.ID_type 
                            	AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) = " . $db->toSql($limit_vgallery_path, "Text")
                    ) . "
                WHERE 1
                " . ($limit_vgallery_path === null
                    ? ""
                    : " GROUP BY vgallery_groups.ID "
                )
    );
    if ($db->nextRecord())
    { 
        do {
            if(ffCommon_url_rewrite($db->getField("name", "Text", true)) == $name) {
                if($limit_vgallery_path === null) {
                    return $db->getField($field, "Text", true);
                } else {
                    return array("field" => $db->getField($field, "Text", true)
                                , "name" => $name
                                , "count" => $db->getField("limit_vgallery", "Number", true)
                                , "enable_child" => $db->getField("enable_item_child", "Number", true)
                                , "limit_type" => $db->getField("limit_type", "Text", true)
                            );
                }
            }
        } while($db->nextRecord());
    }
    return null;
}