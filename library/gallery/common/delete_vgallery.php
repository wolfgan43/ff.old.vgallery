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
function delete_vgallery($parent, $name, $vgallery_name) {
        $db = ffDB_Sql::factory();

        $db->query("SELECT * 
                    FROM vgallery_nodes 
                        INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                    WHERE 
                        parent = " . $db->toSql(stripslash($parent) . "/" . $name, "Text") . "
                        AND vgallery.name = " . $db->toSql($vgallery_name, "Text"));
        if ($db->nextRecord())
            { 
                do
                    {
                        delete_vgallery($db->getField("parent")->getValue(), $db->getField("name")->getValue(), $vgallery_name);
                    }
                while ($db->nextRecord());
            }

        //Elimina le relazioni tra i vari nodi            
        $sSQL = "DELETE 
                    FROM `rel_nodes` 
                    WHERE 
                        (
                            `rel_nodes`.`ID_node_src` IN ( SELECT vgallery_nodes.ID 
                                             FROM vgallery_nodes 
                                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                             WHERE vgallery_nodes.parent = " .  $db->toSql($parent, "Text") . "
                                                AND vgallery_nodes.name = " . $db->toSql($name, "Text") . "
                                                AND vgallery.name = " . $db->toSql($vgallery_name, "Text") . "
                                             ) 
                            AND `rel_nodes`.`contest_src` = " . $db->toSql($vgallery_name, "Text") . "
                        ) 
                    OR
                        (
                            `rel_nodes`.`ID_node_dst` IN ( SELECT vgallery_nodes.ID 
                                             FROM vgallery_nodes 
                                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                             WHERE vgallery_nodes.parent = " .  $db->toSql($parent, "Text") . "
                                                AND vgallery_nodes.name = " . $db->toSql($name, "Text") . "
                                                AND vgallery.name = " . $db->toSql($vgallery_name, "Text") . "
                                             ) 
                            AND `rel_nodes`.`contest_dst` = " . $db->toSql($vgallery_name, "Text") . "
                        ) 
                ";    
        $db->query($sSQL);

        // Elimina i campi internazionalizzati
        $sSQL = "DELETE 
                    FROM `vgallery_rel_nodes_fields`
                    WHERE `vgallery_rel_nodes_fields`.`ID_nodes` IN ( SELECT vgallery_nodes.ID 
                                         FROM vgallery_nodes 
                                            INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                         WHERE vgallery_nodes.parent = " .  $db->toSql($parent, "Text") . "
                                            AND vgallery_nodes.name = " . $db->toSql($name, "Text") . "
                                            AND vgallery.name = " . $db->toSql($vgallery_name, "Text") . ")";
        $db->query($sSQL);

        // Elimina i nodi
        $sSQL = "DELETE 
                    FROM vgallery_nodes 
                    WHERE vgallery_nodes.parent = " .  $db->toSql($parent, "Text") . " 
                    AND vgallery_nodes.name = " . $db->toSql($name, "Text") . "
                    AND vgallery_nodes.ID_vgallery IN ( SELECT ID FROM vgallery WHERE vgallery.name = " . $db->toSql($vgallery_name, "Text") . ")";
        $db->query($sSQL);
        $full_path = stripslash($parent) . "/" . $name;
        if($full_path && ffCommon_dirname($full_path) != $full_path && check_function("fs_operation")) {
            purge_dir(DISK_UPDIR . $full_path, $full_path, true);
        }
}