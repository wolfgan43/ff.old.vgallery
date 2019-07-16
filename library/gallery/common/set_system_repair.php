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
function set_system_repair() {
    $arrFtpMkDir = NULL;
    
    if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME)) {
        /*$res = @mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME);
        if($res) {
            @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME, 0755);
        } else { */
            $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME;
            $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "<br>";
        //}
    } else {
        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME, 0755);
    }

    if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH)) {
        /*$res = @mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH);
        if($res) {
            @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, 0755);
        } else {*/
            $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH;
            $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "<br>";
        //}
    } else {
        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, 0755);
    }

    if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules")) {
        /*if(@mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules")) {
            @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules", 0755);
        } else {*/
            $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules";
            $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . "<br>";
        //}
    } else {
        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules", 0777);
    }
    
    $tmp_module_dir = glob(FF_DISK_PATH . "/conf/gallery" . "/modules/*");
    if(is_array($tmp_module_dir) && count($tmp_module_dir)) {
        foreach($tmp_module_dir AS $tmp_module_dir_key => $tmp_module_dir_value) {

            if(is_dir($tmp_module_dir_value) && !@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)))) {
                /*if(@mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)))) {
                    @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)), 0755);
                } else {*/
                    $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value));
                    $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)) . "<br>";
                //}
            } else {
                @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/" . strtolower(basename($tmp_module_dir_value)), 0777);
            }
        }    
    }
    if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email")) {
        /*if(@mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email")) {
            @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email", 0755);
        } else {*/
            $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email";
            $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email" . "<br>";
        //}
    } else {
        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email", 0777);
    }
    if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import")) {
        /*if(@mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import")) {
            @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import", 0755);
        } else { */
            $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import";
            $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import" . "<br>";
        //}
    } else {
        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/import", 0777);
    }
    if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template")) {
        /*if(@mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template")) {
            @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template", 0755);
        } else {  */
            $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template";
            $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template" . "<br>";
        //}
    } else {
        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template", 0777);
    }

    if(!@is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images")) {
        $arrFtpMkDir[] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/images";
        $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " " . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images" . "<br>";
    } else {
        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images", 0755);
    }    
    
    $strError .= set_fs(FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "chmod", array(FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH => true), $arrFtpMkDir, "0777");

    if(!@is_dir(FF_DISK_UPDIR . "/users")) {
        /*if(@mkdir(FF_DISK_UPDIR . "/users")) {
            @chmod(FF_DISK_UPDIR . "/users", 0777);
        } else {*/
            $arrFtpMkDirUpload[] = "/" . basename(FF_DISK_UPDIR) . "/users";
            $strError .= ffTemplate::_get_word_by_code("dir_creation_failed") . " /" . basename(FF_DISK_UPDIR) . "/users" . "<br>";
        //}
    } else {
        @chmod(FF_DISK_UPDIR . "/users", 0777);
    }

    $strError .= set_fs("/" . basename(FF_DISK_UPDIR) . "/users", "chmod", null, $arrFtpMkDirUpload, "0777");
    
    if(!$strError) {
        if(is_dir(FF_DISK_UPDIR . "/_sys")) {
            $strError = set_fs("/" . basename(FF_DISK_UPDIR) . "/_sys", "chmod", NULL, NULL, "0777");
            if(check_function("fs_operation")) {
                full_copy(FF_DISK_UPDIR . "/_sys", FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, true);
                if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/users")) {
                     full_copy(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/users", FF_DISK_UPDIR . "/users", true);
                }

                if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/email"))
                    full_copy(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/email", FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/email", true);

                if(is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/template"))
                    full_copy(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/template", FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template", true);
            }
        }
        //$strError = set_fs(FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "chmod", NULL, $arrFtpMkDir, "0755");
    } 

    return $strError;
}