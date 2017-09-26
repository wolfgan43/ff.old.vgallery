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
function update_vgallery_models($action, $ID_vgallery, $ID_node, $vgallery_name, $actual_path, $item_name, $gallery_models = null, $owner = null) {
    $db = ffDB_Sql::factory();
    $db_rel_nodes = ffDB_Sql::factory();
    $db_rel_update = ffDB_Sql::factory();

	$arrGallery["default"] = "";
	
	if(0 && $gallery_models !== null) {
		if(is_array($gallery_models) && count($gallery_models)) {
			foreach($gallery_models AS $gallery_models_key => $gallery_models_value) {
				$arrGallery[$gallery_models_key] = $gallery_models_value;
			}
		}
	}
    
    if(is_array($arrGallery) && count($arrGallery)) {
    	foreach($arrGallery AS $arrGallery_key => $arrGallery_path) {
		    $sSQL = "SELECT * FROM vgallery_rel WHERE vgallery_rel.ID_vgallery = " . $db->toSql($ID_vgallery, "Number");
		    $db->query($sSQL);
		    if($db->nextRecord()) {
		        do {
		            $ID_node_src = 0;
		            $contest_src = "";
		            $cascading_src = $db->getField("cascading", "Text")->getValue();
		            
		            switch($db->getField("contest_src", "Text")->getValue()) {
		                case "files":
		                    $sSQL = "SELECT 
		                                * 
		                            FROM files
		                            WHERE files.ID = " . $db_rel_nodes->toSql($db->getField("ID_node_src"));
		                    $db_rel_nodes->query($sSQL);
		                    if($db_rel_nodes->nextRecord()) {
		                        $tmp_nodes_src_path = stripslash($actual_path) . "/" . $item_name . $arrGallery_path;
		                        $parzial_node_src_path = stripslash($db_rel_nodes->getField("parent")->getValue()) . "/" . $db_rel_nodes->getField("name")->getValue();

		                        $contest_src = "files";
		                        if($action == "update") {
		                            $sSQL = " 
		                                    SELECT files.* 
		                                    FROM files
		                                    WHERE files.ID IN (
		                                        SELECT 
		                                            IF(`ID_node_src` =  " . $db->toSql($ID_node, "Number") . " AND `contest_src` = " . $db->toSql($vgallery_name, "Text") . "
		                                                , ID_node_dst
		                                                , ID_node_src
		                                            ) AS ID
		                                        FROM rel_nodes
		                                        WHERE 
		                                            (
		                                                `ID_node_src` =  " . $db_rel_nodes->toSql($ID_node, "Number") . "
		                                                AND `contest_src` = " . $db_rel_nodes->toSql($vgallery_name, "Text") . "
		                                                AND `contest_dst` = " . $db_rel_nodes->toSql($contest_src, "Text") . "
		                                            )
		                                            OR
		                                            (
		                                                `ID_node_dst` = " . $db_rel_nodes->toSql($ID_node, "Number") . " 
		                                                AND `contest_dst` = " . $db_rel_nodes->toSql($vgallery_name, "Text") . "
		                                                AND `contest_src` = " . $db_rel_nodes->toSql($contest_src, "Text") . "
		                                            )
		                                        )
		                                    AND files.parent LIKE '" . $db_rel_nodes->toSql($parzial_node_src_path, "Text", false) . "%'
		                            ";
		                            $db_rel_nodes->query($sSQL);
		                            if($db_rel_nodes->nextRecord()) {
		                                $old_file_full_path = stripslash($db_rel_nodes->getField("parent", "Text", true)) . "/" . $db_rel_nodes->getField("name", "Text", true);
		                                $ID_actual_rel = $db_rel_nodes->getField("ID", "Number", true);
		                                
		                                if($old_file_full_path != stripslash($parzial_node_src_path) . $tmp_nodes_src_path) {
											if(strpos(stripslash($parzial_node_src_path) . $tmp_nodes_src_path, $old_file_full_path) !== 0 && check_function("fs_operation")) {
			                            		full_copy(DISK_UPDIR . $old_file_full_path, DISK_UPDIR . stripslash($parzial_node_src_path) . $tmp_nodes_src_path, true);
                                                if($old_file_full_path && ffCommon_dirname($old_file_full_path) != $old_file_full_path && check_function("fs_operation"))
			                                        purge_dir(DISK_UPDIR . $old_file_full_path, $old_file_full_path, false);
											}
		                                    $sSQL = "UPDATE files 
		                                            SET 
		                                                name = " . $db_rel_update->toSql(basename(stripslash($parzial_node_src_path) . $tmp_nodes_src_path)) . "
		                                                , parent = " . $db_rel_update->toSql(ffCommon_dirname(stripslash($parzial_node_src_path) . $tmp_nodes_src_path)) . "
		                                            WHERE files.ID = " . $db_rel_update->toSql($ID_actual_rel);
		                                    $db_rel_update->execute($sSQL);
		                                    $sSQL = "UPDATE files 
		                                            SET 
		                                                files.parent = " . $db_rel_update->toSql(stripslash($parzial_node_src_path) . $tmp_nodes_src_path) . "
		                                            WHERE files.parent = " . $db_rel_update->toSql($old_file_full_path);
		                                    $db_rel_update->execute($sSQL);

		                                    $sSQL = "UPDATE vgallery_rel_nodes_fields 
		                                            SET 
		                                                description = REPLACE(description, " . $db_rel_update->toSql($old_file_full_path) . ", " . $db_rel_update->toSql(stripslash($parzial_node_src_path) . $tmp_nodes_src_path) . ")
		                                            WHERE description LIKE '" . $db_rel_update->toSql($old_file_full_path, "Text", false) . "%'";
		                                    $db_rel_update->execute($sSQL);
		                                }
		                            }
		                        }
		                        foreach(explode("/", $tmp_nodes_src_path) AS $part_node_src_path) {
		                            if(strlen($part_node_src_path)) {
		                                $parzial_node_src_path = stripslash($parzial_node_src_path) . "/" . $part_node_src_path;
		                                
		                                if(!is_dir(DISK_UPDIR . $parzial_node_src_path)) {
		                                    if(@mkdir(DISK_UPDIR . $parzial_node_src_path))
		                                        @chmod(DISK_UPDIR . $parzial_node_src_path, 0777);
		                                }

		                                $sSQL = "SELECT * 
		                                        FROM files
		                                        WHERE 
		                                            parent = " . $db_rel_update->toSql(ffCommon_dirname($parzial_node_src_path)) . "
		                                            AND name = " . $db_rel_update->toSql(basename($parzial_node_src_path), "Text");
		                                $db_rel_update->query($sSQL);
		                                if($db_rel_update->nextRecord()) {
		                                    $ID_node_src = $db_rel_update->getField("ID", "Number")->getValue();
		                                } else {
		                                    $sSQL = "INSERT INTO files
		                                                (
		                                                    name
		                                                    , parent
		                                                    , last_update
		                                                    , is_dir
		                                                    , owner
		                                                ) VALUES (" . 
		                                                    $db_rel_update->toSql(basename($parzial_node_src_path), "Text") . ", " . 
		                                                    $db_rel_update->toSql(ffCommon_dirname($parzial_node_src_path)) . ", " . 
		                                                    $db_rel_update->toSql(time(), "Number") . ", " . 
		                                                    $db_rel_update->toSql("1", "Text") . ", " . 
		                                                    $db_rel_update->toSql($owner, "Number") . "
		                                                )"; 
		                                    $db_rel_update->execute($sSQL);
		                                    $ID_node_src = $db_rel_update->getInsertID(true);
		                                }
		                            }
		                        }
		                    } 
		                    break;
		                case "vgallery_nodes":
		                    $sSQL = "SELECT 
		                                vgallery_nodes.* 
		                                , vgallery.name AS vgallery_name
		                            FROM vgallery_nodes
		                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
		                            WHERE vgallery_nodes.ID = " . $db_rel_nodes->toSql($db->getField("ID_node_src"));
		                    $db_rel_nodes->query($sSQL);
		                    if($db_rel_nodes->nextRecord()) {
		                        $tmp_nodes_src_path = stripslash(str_replace("/" . $vgallery_name, "", $actual_path)) . "/" . $item_name . $arrGallery_path;
		                        $parzial_node_src_path = stripslash($db_rel_nodes->getField("parent")->getValue()) . "/" . $db_rel_nodes->getField("name")->getValue();
		                        $ID_vgallery = $db_rel_nodes->getField("ID_vgallery", "Number", true);

		                        $contest_src =  $db_rel_nodes->getField("vgallery_name")->getValue();
		                        if($action == "update") {
		                            $sSQL = " 
		                                    SELECT vgallery_nodes.* 
		                                    FROM vgallery_nodes
		                                    WHERE vgallery_nodes.ID IN (
		                                        SELECT 
		                                            IF(`ID_node_src` =  " . $db->toSql($ID_node, "Number") . " AND `contest_src` = " . $db->toSql($vgallery_name, "Text") . "
		                                                , ID_node_dst
		                                                , ID_node_src
		                                            ) AS ID
		                                        FROM rel_nodes
		                                        WHERE 
		                                            (
		                                                `ID_node_src` =  " . $db_rel_nodes->toSql($ID_node, "Number") . "
		                                                AND `contest_src` = " . $db_rel_nodes->toSql($vgallery_name, "Text") . "
		                                                AND `contest_dst` = " . $db_rel_nodes->toSql($contest_src, "Text") . "
		                                            )
		                                            OR
		                                            (
		                                                `ID_node_dst` = " . $db_rel_nodes->toSql($ID_node, "Number") . " 
		                                                AND `contest_dst` = " . $db_rel_nodes->toSql($vgallery_name, "Text") . "
		                                                AND `contest_src` = " . $db_rel_nodes->toSql($contest_src, "Text") . "
		                                            )
		                                        )
		                                    AND vgallery_nodes.parent LIKE '" . $db_rel_nodes->toSql($parzial_node_src_path, "Text", false) . "%'
		                            ";
		                            $db_rel_nodes->query($sSQL);
		                            if($db_rel_nodes->nextRecord()) {
		                                $old_file_full_path = stripslash($db_rel_nodes->getField("parent", "Text", true)) . "/" . $db_rel_nodes->getField("name", "Text", true);
		                                $ID_actual_rel = $db_rel_nodes->getField("ID", "Number", true);
		                                
		                                if($old_file_full_path != stripslash($parzial_node_src_path) . $tmp_nodes_src_path) {
		                                    $sSQL = "UPDATE vgallery_nodes 
		                                            SET 
		                                                name = " . $db_rel_update->toSql(basename(stripslash($parzial_node_src_path) . $tmp_nodes_src_path)) . "
		                                                , parent = " . $db_rel_update->toSql(ffCommon_dirname(stripslash($parzial_node_src_path) . $tmp_nodes_src_path)) . "
		                                            WHERE vgallery_nodes.ID = " . $db_rel_update->toSql($ID_actual_rel);
		                                    $db_rel_update->execute($sSQL);
		                                    $sSQL = "UPDATE vgallery_nodes 
		                                            SET vgallery_nodes.parent = REPLACE(vgallery_nodes.parent, " . $db->toSql($old_file_full_path)  . ", " . $db->toSql(stripslash($parzial_node_src_path) . $tmp_nodes_src_path) . ")
		                                            WHERE
		                                            (vgallery_nodes.parent = " . $db->toSql($old_file_full_path)  . " 
		                                                OR vgallery_nodes.parent LIKE '" . $db->toSql($old_file_full_path, "Text", false)  . "/%'
		                                            )";
		                                    $db_rel_update->execute($sSQL);
		                                    if($db_rel_update->affectedRows())
		                                        $rel_vgallery_mode = "update";
		                                    else
		                                        $rel_vgallery_mode = "insert";
		                                }
		                                
		                            }
		                        }
		                        foreach(explode("/", $tmp_nodes_src_path) AS $part_node_src_path) {
		                            if(strlen($part_node_src_path)) {
		                                $parzial_node_src_path = stripslash($parzial_node_src_path) . "/" . $part_node_src_path;

		                                $sSQL = "SELECT * 
		                                        FROM vgallery_nodes
		                                        WHERE 
		                                            ID_vgallery = " . $db_rel_update->toSql($ID_vgallery) . "
		                                            AND parent = " . $db_rel_update->toSql(ffCommon_dirname($parzial_node_src_path)) . "
		                                            AND name = " . $db_rel_update->toSql(basename($parzial_node_src_path));
		                                $db_rel_update->query($sSQL);
		                                if($db_rel_update->nextRecord()) {
		                                    $ID_node_src = $db_rel_update->getField("ID", "Number")->getValue();
		                                } else {
		                                    $sSQL = "INSERT INTO  
		                                                `vgallery_nodes` 
		                                            ( 
		                                                `ID` 
		                                                , `ID_vgallery` 
		                                                , `name` 
		                                                , `parent` 
		                                                , `ID_type`
		                                                , `is_dir`
		                                                , `owner` 
		                                                , `last_update`
		                                            )
		                                            VALUES
		                                            (
		                                                ''
		                                                , " . $db_rel_update->toSql($ID_vgallery) . "
		                                                , " . $db_rel_update->toSql(basename($parzial_node_src_path)) . "
		                                                , " . $db_rel_update->toSql(ffCommon_dirname($parzial_node_src_path)) . "
		                                                , ( SELECT ID FROM vgallery_type WHERE vgallery_type.name = " . $db_rel_update->toSql("Directory", "Text") . " )
		                                                , '1'
		                                                , " . $db_rel_update->toSql($owner, "Number") . "
		                                                , " . $db_rel_update->toSql(time(), "Number") . "
		                                            )";
		                                    $db_rel_update->execute($sSQL);
		                                    $ID_node_src = $db_rel_update->getInsertID(true);
		                                }
		                            }
		                        }
		                    }
		                    break;
		                default:
		            }
		            if(strlen($contest_src) && $ID_node_src > 0) {
		                $sSQL = "SELECT * 
		                        FROM rel_nodes
		                        WHERE 
		                            `ID_node_src` =  " . $db_rel_update->toSql($ID_node, "Number") . "
		                            AND `contest_src` = " . $db_rel_update->toSql($vgallery_name, "Text") . "
		                            AND `ID_node_dst` = " . $db_rel_update->toSql($ID_node_src, "Number") . " 
		                            AND `contest_dst` = " . $db_rel_update->toSql($contest_src, "Text") . "
		                            ";
		                $db_rel_update->query($sSQL);
		                if(!$db_rel_update->numRows()) {
		                    $sSQL = "INSERT INTO 
		                                rel_nodes
		                                (
		                                ID, 
		                                `ID_node_src`, 
		                                `contest_src`, 
		                                `ID_node_dst`, 
		                                `contest_dst`,
		                                `cascading`
		                                )
		                                VALUES
		                                (
		                                '', 
		                                    " . $db_rel_update->toSql($ID_node, "Number") . ", 
		                                    " . $db_rel_update->toSql($vgallery_name, "Text") . ",
		                                    " . $db_rel_update->toSql($ID_node_src, "Number") . ", 
		                                    " . $db_rel_update->toSql($contest_src, "Text") . ",
		                                    " . $db_rel_update->toSql($cascading_src, "Text") . "
		                                )
		                    ";
		                    $db_rel_update->execute($sSQL);
		                }
		            }
		        } while($db->nextRecord());
		    }
		}
	}
}
