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
function system_set_cache_sid($sid, $data, $selected_lang) {
    if(use_cache() !== false) {
        if(strlen($sid) && strlen($data)) {
            $actual_user = strtolower(get_session("UserID"));
            $actual_user_path = CM_CACHE_PATH . "/sid/" . strtolower($selected_lang) . "/" . $actual_user . "/gzip";
            if(!is_dir($actual_user_path)) {
                $res = @mkdir($actual_user_path, 0777, true);
                if($res)
                    @chmod($actual_user_path, 0777);
            }
            
            //normal 
            $cache_sid_path = CM_CACHE_PATH . "/sid/" . strtolower($selected_lang) . "/" . $actual_user . "/" . $sid . ".html";
           /* if (!$handle = @fopen($cache_sid_path, "w")) {
                $strError .= "Unable write file: " . $cache_sid_path;
            } else {
                if (@fwrite($handle, preg_replace("/\{([\w|_|-]+)\}/i" , "{_\${1}}", $data)) === FALSE) {
                    $strError .= "Unable write file: " . $cache_sid_path;
                }

                @fclose($handle);
            }*/
            
            //compressed
            $cache_sid_gzip_path = CM_CACHE_PATH . "/sid/" . strtolower($selected_lang) . "/" . $actual_user . "/gzip/" . $sid . ".html.gz";
           /* if (!$handle = @fopen($cache_sid_gzip_path, "w")) {
                $strError .= "Unable write file: " . $cache_sid_gzip_path;
            } else {
                if (@fwrite($handle, cm_parse_gzip(preg_replace("/\{([\w|_|-]+)\}/i" , "{_\${1}}", $data))) === FALSE) {
                    $strError .= "Unable write file: " . $cache_sid_gzip_path;
                }

                @fclose($handle);
            }*/
            $now = time();
            $max_age = 60 * 60 * 24 * 7;
            
            $tmp_file_content = preg_replace("/\{([\w|_|-]+)\}/i" , "{_\${1}}", $data);
            
            cm_filecache_write(ffcommon_dirname($cache_sid_path), basename($cache_sid_path), $tmp_file_content, $now + $max_age);
            cm_filecache_write(ffcommon_dirname($cache_sid_gzip_path), basename($cache_sid_gzip_path), gzencode($tmp_file_content), $now + $max_age);

        }    
    }
}