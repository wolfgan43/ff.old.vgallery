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
function check_system($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;

    check_function("get_literal_size");
    
    if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME)) {
        if(@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH)) {
            $tmp_module_dir = glob(FF_DISK_PATH . "/conf/gallery" . "/modules/*");
            if(is_array($tmp_module_dir) && count($tmp_module_dir)) {
                foreach($tmp_module_dir AS $tmp_module_dir_key => $tmp_module_dir_value) {
                    if(is_dir($tmp_module_dir_value) && !@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)))) {
                        $check["status"] .= ffTemplate::_get_word_by_code("directory_not_exist") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)) . "<br>";
                    } else {
                        if($show_info && is_dir($tmp_module_dir_value)) {
                            $fs = get_check_fs(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)));

                            $arrDir = array("d" => 1);
                            $tot_size = array_sum(array_diff($fs, $arrDir));

                            $check["info"] .= "/modules/" . basename($tmp_module_dir_value) . " => " . ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size) . "<br>";
                        }
                    }
                }
            }
            if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email")) {
                $check["status"] .= ffTemplate::_get_word_by_code("directory_not_exist") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email" . "<br>";
            } elseif($show_info) {
                $fs = get_check_fs(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email");
                $arrDir = array("d" => 1);
                $tot_size = array_sum(array_diff($fs, $arrDir));

                $check["info"] .= "/email" . " => " . ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size) . "<br>";
            }
            if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import")) {
                $check["status"] .= ffTemplate::_get_word_by_code("directory_not_exist") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . "<br>";
            } elseif($show_info) {
                $fs = get_check_fs(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import");
                $arrDir = array("d" => 1);
                $tot_size = array_sum(array_diff($fs, $arrDir));

                $check["info"] .= "/import" . " => " . ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size) . "<br>";
            }
            if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template")) {
                $check["status"] .= ffTemplate::_get_word_by_code("directory_not_exist") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template" . "<br>";
            } elseif($show_info) {
                $fs = get_check_fs(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template");
                $arrDir = array("d" => 1);
                $tot_size = array_sum(array_diff($fs, $arrDir));

                $check["info"] .= "/template" . " => " . ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size) . "<br>";
            }
            if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/" . CM_SHOWFILES_THUMB_PATH)) {
                $check["status"] .= ffTemplate::_get_word_by_code("directory_not_exist") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/" . CM_SHOWFILES_THUMB_PATH . "<br>";
            } elseif($show_info) {
                $fs = get_check_fs(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/" . CM_SHOWFILES_THUMB_PATH);
                $arrDir = array("d" => 1);
                $tot_size = array_sum(array_diff($fs, $arrDir));

                $check["info"] .= "/images/" . CM_SHOWFILES_THUMB_PATH . " => " . ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size) . "<br>";
            }
            if(!@is_dir(DISK_UPDIR . "/users")) {
                $check["status"] .= ffTemplate::_get_word_by_code("directory_not_exist") . " /" . basename(DISK_UPDIR) . "/users" . "<br>";
            } elseif($show_info) {
                $fs = get_check_fs(DISK_UPDIR . "/users");
                $arrDir = array("d" => 1);
                $tot_size = array_sum(array_diff($fs, $arrDir));

                $check["info"] .= "/" . basename(DISK_UPDIR) . "/users" . " => " . ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size) . "<br>";
            }                                                                                             

            $fs = get_check_fs(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH);

            if($show_info) {
                $arrDir = array("d" => 1);
                $tot_dir = count(array_intersect($fs, $arrDir));
                $tot_size = array_sum(array_diff($fs, $arrDir));

                $check["info"] .= "<br>";
                $check["info"] .= ffTemplate::_get_word_by_code("count_dir") . $tot_dir . "<br>";
                $check["info"] .= ffTemplate::_get_word_by_code("tot_file_size") . get_literal_size($tot_size);
            }

            //$files = array_search("0", $fs);
            //if($files !== false) {
            //     $check["status"] .= ffTemplate::_get_word_by_code("permission_corrupted"); 
            //}
        } else {
            $check["status"] = ffTemplate::_get_word_by_code("directory_not_exist") . " /" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH;
        }
    } else {
        $check["status"] = ffTemplate::_get_word_by_code("directory_not_exist") . " /" . FRONTEND_THEME;
    }
    
    return $check;    
}