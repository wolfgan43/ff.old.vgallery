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
function refresh_cache($namespace, $node, $action, $path = null, $tags = null, $cats = null) {
    $db = ffDB_Sql::factory();

    static $processed = null;

    $arrUpdate = array();
    $arrDelete = array();
    $arrPath = array();

    //  return;
    //schema
    /*
    [element_type][element_subtype][ID_category]-[ID_node]-[ID_node_group]
        [element_type]:
            V = Virtual Gallery
            G = Gallery
            S = Static Menu
            D = Draft Database
            T = Draft Html
            M = Module

        [element_subtype]:
            V = View or Detail
            T = Thumb or Report
            S = Search
            P = Publish

        [ID_category] : 0 ~ n
        [ID_node] : 1 ~ n
        [ID_node_group] : (optional) 1 ~ n

    */
    $operator["LIKE"] = " LIKE '%[VALUE]%'";
    $operator["IN"] = false;

    $op_current = "IN";

    $namespace = strtoupper($namespace);
    if(!$namespace)
        $namespace = "M";

    switch($namespace) {
        case "V":
        case "G":
        case "S":
        case "D":
        case "T":
        case "M":
            break;
        default:
            $prefix = $namespace . "-";
            $namespace = "M";
    }

    if($namespace == "T")
        $op_current = "LIKE";




    $last_update = time() + 100;

    //Node
    if(is_array($node)) {
        foreach($node AS $node_key) {
            if(!$processed["node"][$node_key]) {
                $processed["node"][$node_key] = true;

                if($op_current == "LIKE")
                    $arrNode[] =  $db->toSql($node_key, "Number");
                else
                    $arrNode[] = " FIND_IN_SET('" . $node_key . "', `cache_page`.`data_" . strtolower($namespace) . "_block`) ";
            }
        }
    } elseif(strlen($node)) {
        if(!$processed["node"][$node]) {
            $processed["node"][$node] = true;

            if($op_current == "LIKE")
                $arrNode[] =  $db->toSql($node, "Number");
            else
                $arrNode[] = " FIND_IN_SET('" . $node . "', `cache_page`.`data_" . strtolower($namespace) . "_block`) ";
        }
    }

    if($arrNode) {
        if($operator[$op_current]) {
            $nodes = "`cache_page`.`data_" . strtolower($namespace) . "_block` " . str_replace("[VALUE]", implode(",", $arrNode), $operator[$op_current]);
        } else {
            $nodes = implode("OR", $arrNode);
        }
    }

    //Path
    if(is_array($path)) {
        foreach($path AS $user_path) {
            $tmp_path = $user_path;
            do {
                if(!$processed["path"][$tmp_path]) {
                    $processed["path"][$tmp_path] = true;

                    $arrPath[$tmp_path] = $db->toSql($tmp_path);
                }
                $tmp_path = ffCommon_dirname($tmp_path);
            } while($tmp_path && $tmp_path != "/");
        }
    } elseif(strlen($path)) {
        $tmp_path = $path;
        do {
            if(!$processed["path"][$tmp_path]) {
                $processed["path"][$tmp_path] = true;

                $arrPath[$tmp_path] = $db->toSql($tmp_path);
            }
            $tmp_path = ffCommon_dirname($tmp_path);
        } while($tmp_path && $tmp_path != "/");
    }

    switch($action) {
        case "insert":
            if($arrPath)
                $query["where"][] = "`cache_page`.`user_path` IN(" . implode(",", $arrPath) . ")";
            break;
        case "update":
            if($nodes)
                $query["where"][] = $nodes;

            break;
        case "confirmdelete":
            if($arrPath)
                $query["where"][] = "`cache_page`.`user_path` IN(" . implode(",", $arrPath) . ")";

            if($nodes)
                $query["where"][] = $nodes;
            break;
        default:

    }

    if($query["where"]) {
        $sSQL = "UPDATE `cache_page` SET 
		            `cache_page`.`last_update` = " . $db->toSql($last_update, "Number") . " 
		        WHERE (" . implode(" OR ", $query["where"]) . ")";
        $db->execute($sSQL);
        update_cache_file($last_update, array_keys($arrPath));

    }
    return null;
}

function refresh_cache_get_blocks_by_layout($value) {
    $db = ffDB_Sql::factory();

    if(is_array($value) && count($value)) {
        foreach($value AS $subvalue) {
            $sSQL_where[] = "layout.value = " . $db->toSql($subvalue);
        }
    } elseif($value) {
        $sSQL_where[] = "layout.value = " . $db->toSql($value);

    }

    if(is_array($sSQL_where) && count($sSQL_where)) {
        $sSQL = "SELECT layout.ID
				FROM layout
				WHERE " . implode(" OR ", $sSQL_where);
        $db->query($sSQL);
        if($db->nextRecord()) {
            do {
                refresh_cache_block($db->getField("ID", "Number", true));
            } while($db->nextRecord());
        }
    }
}

function refresh_cache_block($ID_block) {
    $db = ffDB_Sql::factory();

    $last_update = time() + 100;

    $sSQL = "UPDATE cache_page SET
                `cache_page`.`last_update` = " . $db->toSql($last_update, "Number") . " 
            WHERE FIND_IN_SET(" . $db->toSql($ID_block, "Number") . ", `cache_page`.layout_blocks) ";
    $db->execute($sSQL);

    update_cache_file($last_update);
}

function update_cache_file($last_update, $path = null) {
    $db = ffDB_Sql::factory();

    if(is_array($path) && count($path)) {
        foreach(array_filter($path) AS $user_path) {
            $query["order"][] = "IF(cache_page.user_path = " . $db->toSql($user_path) . "
				, 0
				, 1
			)";
        }

        $query["where"] = " OR `cache_page`.`user_path` IN ('" . implode("','", $path) . "')";
    }
    $query["order"][] = "LENGTH(cache_page.user_path)";

    $sSQL = "SELECT CONCAT(cache_page.disk_path, '/', cache_page.filename) AS cache_file
                , cache_page.user_path AS cache_user_path
                , cache_page.ext AS cache_ext
                , cache_page.lang AS lang
            FROM cache_page
            WHERE `cache_page`.`last_update` = " . $db->toSql($last_update, "Number") . "
                " . ($query["where"]
            ? $query["where"]
            : ""
        ) . "
            ORDER BY " . implode(" , ", $query["order"]) . "
            ";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $schema = cache_get_settings();

        $cache = array();
        //$count_page = $db->numRows();
        $max_page = 100;
        $page = 0;
        $base_path = FF_DISK_PATH . "/cache";
        $error_document_path = $schema["page"]["/error"]["cache_path"];

        do {
            $cache_user_path 	= $db->getField("cache_user_path", "Text", true);
            $cache_file 	= $db->getField("cache_file", "Text", true);
            $cache_ext 		= $db->getField("cache_ext", "Text", true);
            //$lang 			= $db->getField("lang", "Text", true);

            if(strpos($cache_file, FF_DISK_PATH) === 0) {
                if(!@touch($cache_file . "." . $cache_ext, filectime($cache_file . "." . $cache_ext) - 10))
                    @unlink($cache_file . "." . $cache_ext);
                if(!@touch($cache_file . ".gz", filectime($cache_file . ".gz") - 10))
                    @unlink($cache_file . ".gz");

                $arrUserPath = explode("/", $cache_user_path);
                $cache_error_path = substr($cache_file, 0, strpos($cache_file, $cache_user_path));
                $arrErrorDocument[$cache_error_path . $error_document_path . "/" . $arrUserPath[1] . ".php"][substr(ffCommon_dirname($cache_file), strlen($base_path))] = true;

                $cache[$cache_file . "." . $cache_ext] = "updated";
            } else {
                $cache[$cache_file . "." . $cache_ext] = "wrong fs";
            }

            $page++;
        } while($db->nextRecord() && $page <= $max_page);
    }

    if(is_array($arrErrorDocument) && count($arrErrorDocument)) {
        check_function("Filemanager");
        $fs = new Filemanager("php");
        foreach($arrErrorDocument AS $path => $keys) {
            $fs->delete(array_keys($keys), $path, Filemanager::SEARCH_IN_VALUE);
        }
    }


    cache_writeLog($last_update . " COUNT: " . count($cache) . " FROM: " . $_SERVER["REQUEST_URI"] . " REFERER: " . $_SERVER["HTTP_REFERER"] . " " . print_r($cache, true), "log_refresh" . (count($cache) > 1000 ? "_huge" : (count($cache) > 100 ? "_big" : "")));


}