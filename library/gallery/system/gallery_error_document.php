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
function system_gallery_error_document($settings_path) {
    $is_valid_resource = null;

    if(ffGetFilename($settings_path) == "notfound") {
        $error_type = "404";
        if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/404.png")) {
            $error_path = FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/404.png";
        } else {
            $error_path = FF_THEME_DIR . "/" . THEME_INSET  . "/images/error-pages/404.png";
        }
    } elseif(ffGetFilename($settings_path) == "forbidden") {
        $error_type = "403";
        if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/403.png")) {
            $error_path = FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/403.png";
        } else {
            $error_path = FF_THEME_DIR . "/" . THEME_INSET . "/images/error-pages/403.png";
        }
    } else {
		$error_type = "500";
    }

    require_once(FF_DISK_PATH . "/conf/gallery/updater/check/exclude_fs." . FF_PHP_EXT);
    
    $skip_fs = false;
	/** @var include $fs_exclude */
	if(is_array($fs_exclude) && count($fs_exclude)) {
        foreach($fs_exclude AS $fs_exclude_key => $fs_exclude_value) {
            if(basename($fs_exclude_key) != "uploads" && strpos($_SERVER["REQUEST_URI"], $fs_exclude_key) === 0) {
                $skip_fs = true;
                break;
            }
        }
    }

    http_response_code($error_type);
    
    if($skip_fs) {
        output_header(FF_DISK_PATH . $error_path);
        readfile(FF_DISK_PATH . $error_path);
    } else {
        if(strpos($_SERVER["REQUEST_URI"], SITE_UPDIR) === 0 || strpos($_SERVER["REQUEST_URI"], FF_THEME_DIR) === 0) {
            $request_uri = parse_url($_SERVER["REQUEST_URI"]);
            if(!@file_exists(FF_DISK_PATH . rtrim($request_uri["path"], "/")) && strlen($error_path)) {
                $real_path = $_SERVER["REQUEST_URI"];
                
                $real_ext = ffGetFilename($real_path, false);
                $real_path = ffCommon_dirname($real_path) . "/" . ffGetFilename($real_path);
                
                $arrRealPath = explode("/", $real_path);
                if(is_array($arrRealPath) && count($arrRealPath)) {
					$new_real_path = "";
                    foreach($arrRealPath AS $arrRealPath_value) {
                        if(strlen($arrRealPath_value)) {
                            $new_real_path .= "/" . ffCommon_url_rewrite($arrRealPath_value);
                        }
                    }
                    if(strlen($real_ext))
                        $new_real_path = $new_real_path . "." . $real_ext;
                }

                if(strlen($new_real_path) && file_exists(FF_DISK_PATH . $new_real_path)) {
                    output_header(FF_DISK_PATH . $new_real_path);
                    readfile(FF_DISK_PATH . $new_real_path);
                    $is_valid_resource = true;    
                } else {
                    output_header(FF_DISK_PATH . $error_path);
                    $is_valid_resource = false;
                }
            }
        }
		
        if($is_valid_resource === true) {
            if(check_function("write_notification"))
                write_notification("_error_unformatted_path", "Change " . $_SERVER["REQUEST_URI"] . "<br />&nbsp;With&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $new_real_path, "information");
        } else {
            if(check_function("write_notification"))
                write_notification("_error_notfound_path", $_SERVER["REQUEST_URI"], "warning", "", "", true, -1, null, "file");

            if($is_valid_resource === false) {
                readfile(FF_DISK_PATH . $error_path);
            }
        }
    }
    
	exit;
}
