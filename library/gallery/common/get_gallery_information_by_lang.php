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
function get_gallery_information_by_lang($user_path, $type_return = "alias", $cascading = false, $check = false) {
	$db = ffDB_Sql::factory();
    
    if(is_array($type_return)) {
        foreach($type_return AS $type_return_key => $type_return_value) {
            $sSQL_field_cond = " IF(files_rel_languages." . $type_return_key . " = '', files_rel_languages." . $type_return_value . ", files_rel_languages." . $type_return_key . " ) ";
            break;
        }
    } else {
        $sSQL_field_cond = "files_rel_languages." . $type_return;
    }
    
    do {
        $sSQL = "SELECT 
                    $sSQL_field_cond AS return_value
                 FROM files 
                    INNER JOIN files_rel_languages ON files.ID = files_rel_languages.ID_files
                    INNER JOIN " . FF_PREFIX . "languages ON files_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID
                 WHERE 
                    files.parent =  " . $db->toSql(ffCommon_dirname($user_path), "Text") . "
                    AND files.name =  " . $db->toSql(basename($user_path), "Text") . " 
                    AND " . FF_PREFIX . "languages.code =  " . $db->toSql(LANGUAGE_INSET, "Text");
        $db->query($sSQL);
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
    } while ($cascading && !$value && $user_path != ffCommon_dirname($user_path) && $user_path = ffCommon_dirname($user_path));

	return $value;
}
