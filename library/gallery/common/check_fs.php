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
 
if(!function_exists("stripslash")) {
	function stripslash($temp) {
		if (substr($temp,-1) == "/")
			$temp = substr($temp,0,-1);
		return $temp;
	}
}
 
function check_fs($absolute_path, $relative_path, $purge_fs_db = true) {
    if(strpos($absolute_path, $relative_path) === false)
        return true;

    if($purge_fs_db)
        purge_fs_db($relative_path);   
    
    if (is_dir($absolute_path)) {
        if ($handle = @opendir($absolute_path)) {
            while (false !== ($file = readdir($handle))) { 
                if ($file != "." && $file != ".." && $file != ffMedia::STORING_BASE_NAME && $file !=  "_sys") {
                    if (is_dir($absolute_path . "/" . $file)) {
                        check_fs(stripslash($absolute_path) . "/" . $file, stripslash($relative_path) . "/" . $file);
                    } else {
                        if(ffCommon_url_rewrite(ffGetFilename(stripslash($absolute_path) . "/" . $file)) != ffGetFilename(stripslash($absolute_path) . "/" . $file) && !file_exists(stripslash($absolute_path) . "/" . ffCommon_url_rewrite(ffGetFilename(stripslash($absolute_path) . "/" . $file)) . (pathinfo(stripslash($absolute_path) . "/" . $file, PATHINFO_EXTENSION) ? "." . ffCommon_url_rewrite(pathinfo(stripslash($absolute_path) . "/" . $file, PATHINFO_EXTENSION)) : ""))) {
                            @rename(stripslash($absolute_path) . "/" . $file
                                    , stripslash($absolute_path)
                                        . "/" 
                                        . ffCommon_url_rewrite(ffGetFilename(stripslash($absolute_path) . "/" . $file)) 
                                        . (pathinfo(stripslash($absolute_path) . "/" . $file, PATHINFO_EXTENSION)
                                            ? "." . ffCommon_url_rewrite(pathinfo(stripslash($absolute_path) . "/" . $file, PATHINFO_EXTENSION))
                                            : ""
                                        )
                                    );
                            $file = ffCommon_url_rewrite(ffGetFilename(stripslash($absolute_path) . "/" . $file)) 
                                    . (pathinfo(stripslash($absolute_path) . "/" . $file, PATHINFO_EXTENSION)
                                        ? "." . ffCommon_url_rewrite(pathinfo(stripslash($absolute_path) . "/" . $file, PATHINFO_EXTENSION))
                                        : ""
                                    );
                        }
                        check_fs_db($relative_path, $file, false);
                    }
                }
            }
            if(ffCommon_url_rewrite(ffGetFilename($absolute_path)) != ffGetFilename($absolute_path) && !file_exists(ffCommon_dirname($absolute_path) . "/" . ffCommon_url_rewrite(ffGetFilename($absolute_path)) . (pathinfo($absolute_path, PATHINFO_EXTENSION) ? "." . ffCommon_url_rewrite(pathinfo($absolute_path, PATHINFO_EXTENSION)) : ""))) {
                rename($absolute_path
                        , ffCommon_dirname($absolute_path)
                            . "/" 
                            . ffCommon_url_rewrite(ffGetFilename($absolute_path)) 
                            . (pathinfo($absolute_path, PATHINFO_EXTENSION)
                                ? "." . ffCommon_url_rewrite(pathinfo($absolute_path, PATHINFO_EXTENSION))
                                : ""
                            )
                        );
                $relative_name = ffCommon_url_rewrite(ffGetFilename($absolute_path))
                        . (pathinfo($absolute_path, PATHINFO_EXTENSION)
                            ? "." . ffCommon_url_rewrite(pathinfo($absolute_path, PATHINFO_EXTENSION))
                            : ""
                        );

            } else {
                $relative_name = basename($relative_path);
            }
            check_fs_db(ffCommon_dirname($relative_path), $relative_name, true);
        }
    } else {
        if(ffCommon_url_rewrite(ffGetFilename($absolute_path)) != ffGetFilename($absolute_path) && !file_exists(ffCommon_dirname($absolute_path) . "/" . ffCommon_url_rewrite(ffGetFilename($absolute_path)) . (pathinfo($absolute_path, PATHINFO_EXTENSION) ? "." . ffCommon_url_rewrite(pathinfo($absolute_path, PATHINFO_EXTENSION)) : ""))) {
            rename($absolute_path
                    , ffCommon_dirname($absolute_path)
                        . "/" 
                        . ffCommon_url_rewrite(ffGetFilename($absolute_path)) 
                        . (pathinfo($absolute_path, PATHINFO_EXTENSION)
                            ? "." . ffCommon_url_rewrite(pathinfo($absolute_path, PATHINFO_EXTENSION))
                            : ""
                        )
                    );
            $relative_name = ffCommon_url_rewrite(ffGetFilename($absolute_path))
                    . (pathinfo($absolute_path, PATHINFO_EXTENSION)
                        ? "." . ffCommon_url_rewrite(pathinfo($absolute_path, PATHINFO_EXTENSION))
                        : ""
                    );
        } else {
            $relative_name = basename($relative_path);
        }
        check_fs_db(ffCommon_dirname($relative_path), $relative_name, false);
    }
    
    normalize_fs_db($relative_path);
}

function check_fs_closest_db($strFile) {
	if($strFile) 
	{
		$is_dir = is_dir(FF_DISK_UPDIR . $strFile);

		do {
			check_fs_db(ffCommon_dirname($strFile), basename($strFile), $is_dir);
			$strFile = ffCommon_dirname($strFile);
			if(!$is_dir)
				$is_dir = true;

		} while($strFile != "/");
	}
}

function check_fs_db($strPath, $strFile, $is_dir) {
    $db = ffDB_Sql::factory();
   
    $sSQL = "SELECT ID, is_dir FROM files WHERE parent = " . $db->toSql($strPath, "Text") . " AND name = " . $db->toSql($strFile, "Text");
    $db->query($sSQL);
    if ($db->nextRecord()) {
        $ID_files = $db->getField("ID");
        if(strpos($strFile, "pdf-conversion") === false /*&& $strFile != GALLERY_TPL_PATH && strpos($strPath, "/" . GALLERY_TPL_PATH) === false*/ && strpos($strFile, ".") !== 0 && ($strPath == "/" || strpos($strPath, "/.") === false)) {
            if($db->getField("is_dir")->getValue() != $is_dir) {
                $sSQL = "UPDATE files 
                        SET is_dir = " . $db->toSql($is_dir, "Text")  . "
                        WHERE
                            files.ID = " . $db->toSql($ID_files);
                $db->execute($sSQL);
            }
        } else {
            $sSQL = "DELETE FROM files_rel_languages WHERE files_rel_languages.ID_files = " . $db->toSql($ID_files);
            $db->execute($sSQL);

            $sSQL = "DELETE FROM files WHERE files.ID = " . $db->toSql($ID_files);
            $db->execute($sSQL);
        }
    } else {
        if(strpos($strFile, "pdf-conversion") === false /*&& $strFile != GALLERY_TPL_PATH && strpos($strPath, "/" . GALLERY_TPL_PATH) === false*/ && strpos($strFile, ".") !== 0 && ($strPath == "/" || strpos($strPath, "/.") === false)) {
            $sSQL = "INSERT INTO `files` 
                    (
                        `ID` 
                        , `name`
                        , `parent`
                        , `is_dir`
                        , `last_update`
                        , `owner`
                    ) VALUES (
                        NULL 
                        , " . $db->toSql($strFile, "Text") . "
                        , " . $db->toSql($strPath, "Text") . "
                        , " . $db->toSql($is_dir, "Text") . "
                        , " . $db->toSql(time(), "Text") . "
                        , " . $db->toSql(Auth::get("user")->id, "Number") . "
                    )";
            $db->query($sSQL);
            if($db->affectedRows()) {
                $ID_file = $db->getInsertID();
                
                $db->query("SELECT * FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'");
                if($db->nextRecord()) {
                    $db_visible = ffDB_Sql::factory();
                    do {
                        $sSQL = "INSERT INTO `files_rel_languages` 
                                (
                                    `ID` 
                                    , `ID_files`
                                    , `ID_languages`
                                    , `alias`
                                    , `keywords`
                                    , `description`
                                    , `visible`
                                    , `smart_url`
                                    , `meta_title`
                                    , `meta_title_alt`
                                ) VALUES (
                                    NULL 
                                    , " . $db_visible->toSql($ID_file) . "
                                    , " . $db_visible->toSql($db->getField("ID")) . "
                                    , " . $db_visible->toSql("", "Text") . "
                                    , " . $db_visible->toSql("", "Text") . "
                                    , " . $db_visible->toSql("", "Text") . "
                                    , " . $db_visible->toSql("1", "Text") . "
                                    , " . $db_visible->toSql("", "Text") . "
                                    , " . $db_visible->toSql("", "Text") . "
                                    , " . $db_visible->toSql("", "Text") . "
                                );";
                        $db_visible->execute($sSQL);
                    } while($db->nextRecord());            
                }
            }
        }
    }
}

function purge_fs_db($user_path) {
    $db = ffDB_Sql::factory();  

    if(!check_function("fs_operation"))
        return true;

    $sSQL = "SELECT ID
                , CONCAT(IF(files.parent = '/', '', files.parent), '/', files.name) AS full_path
                , files.parent AS parent
                , files.name AS name
                , is_dir
             FROM files 
             WHERE parent LIKE '" . $db->toSql($user_path, "Text", false) . "%'";
    $db->query($sSQL);
    if ($db->nextRecord()) {
        do {
            if(!(is_dir(FF_DISK_UPDIR . $db->getField("full_path")->getValue()) || is_file(FF_DISK_UPDIR . $db->getField("full_path")->getValue())) || (strpos($db->getField("full_path")->getValue(), "/" . "_sys") !== false)) {
                delete_file_from_db($db->getField("parent")->getValue(), $db->getField("name")->getValue());
            }
        } while($db->nextRecord());
    }
    
    $sSQL = "SELECT COUNT( * ) AS count_err, files.ID,  files.parent, files.name
            FROM files
            GROUP BY CONCAT( parent, name )
            HAVING count_err > 1";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            delete_file_from_db($db->getField("parent")->getValue(), $db->getField("name")->getValue(), $db->getField("ID")->getValue());
        } while($db->nextRecord());
    }
    
}


function normalize_fs_db($user_path)
{
    $db = ffDB_Sql::factory();  

    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '_', '-')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, ' ', '-')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '(', '-')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, ')', '-')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '�', 'a')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '�', 'e')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '�', 'i')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '�', 'o')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '�', 'u')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '--', '-')         
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '-.jpg', '.jpg')     
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '-.gif', '.gif')     
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
    $sSQL = "UPDATE `vgallery_rel_nodes_fields` SET 
                description = REPLACE(description, '-.png', '.png')     
            WHERE description LIKE '" . $db->toSql($user_path, "Text", false) . "%'
                AND ID_fields IN(SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.ID_extended_type IN (SELECT extended_type.ID FROM extended_type WHERE extended_type.`group` = 'upload'))";
    $db->execute($sSQL);
}
