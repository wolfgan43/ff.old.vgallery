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
function system_adv_permission($settings_path) {
    $globals = ffGlobals::getInstance("gallery");

    if(Auth::isLogged()) {
        if(basename($settings_path) != "login") {
            $db = ffDB_Sql::factory();

            $src_path = $settings_path;
            $sWhere = "";
            do {
                $src_folder_name = basename($src_path);
                $src_folder_path = ffCommon_dirname($src_path);
                if (strlen($sWhere))
                    $sWhere .= " OR ";
                $sWhere .= " (static_pages.parent = " . $db->toSql($src_folder_path, "Text") . " AND static_pages.name = " . $db->toSql($src_folder_name, "Text") . " )";
            } while($src_folder_name != "" && $src_path = ffCommon_dirname($src_path));

            $sSQL = "SELECT IF(static_pages_rel_groups.mod & 1 = 0, 0, 1) AS result
                    FROM static_pages 
                        INNER JOIN static_pages_rel_groups ON static_pages.ID = static_pages_rel_groups.ID_static_pages
                    WHERE 
                        static_pages_rel_groups.gid IN (" . Auth::get("user")->acl . ")
                        " . ($globals->ID_domain > 0
                            ? " AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number")
                            : ""
                        ) . "
                        AND (" . $sWhere . ")
                        AND (
                                (static_pages.parent =  " . $db->toSql(ffCommon_dirname($settings_path), "Text") . " AND static_pages.name =  " . $db->toSql(basename($settings_path), "Text") . ") 
                            OR
                                (
                                    (static_pages.parent <> " . $db->toSql(ffCommon_dirname($settings_path), "Text") . " OR static_pages.name <>  " . $db->toSql(basename($settings_path), "Text") . ") 
                                AND
                                    (static_pages_rel_groups.mod & 4 = 0)
                                )
                            )
                    
                    ORDER  BY 
                        LENGTH(CONCAT(static_pages.parent, static_pages.name)) DESC
                        , static_pages_rel_groups.mod DESC

                    LIMIT 1 ";
            $db->query($sSQL);
            if($db->nextRecord()) {
                if($db->getField("result", "Number", true) > 0) {
                    
                } else {
                    ffRedirect(stripslash($settings_path) . "/login?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
                }
            }
        }
    } else {
        ffRedirect(FF_SITE_PATH . "/login?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
    }
}
