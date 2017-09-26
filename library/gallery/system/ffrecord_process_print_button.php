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
function system_ffrecord_process_print_button($component) {
    $cm = cm::getInstance();

    if(array_key_exists("print", $component->buttons_options) && $component->buttons_options["print"]["display"]) {
        $real_path_info = $cm->path_info . $cm->real_path_info;
        //ffErrorHandler::raise("ASD2", E_USER_ERROR, get_defined_vars());
        $cm->oPage->tplAddJs("jquery.plugins.printelement");     

        $oButton_print = ffButton::factory($cm->oPage);
        $oButton_print->id = "print";
        $oButton_print->aspect = "link";
        //$oButton->action_type = "submit";
        $print_title = str_replace("/", " - ", ltrim($real_path_info, "/"));
        do {
            $css_name = str_replace("/", "_", trim($real_path_info, "/"));

            if(file_exists(FF_DISK_PATH . "/themes/" . $cm->oPage->getTheme() . "/css/print_" . $css_name . ".css")) {
                $cm->oPage->tplAddCss(
                    "print_" . $css_name
                    , array(
                        "file"          => "print_" . $css_name . ".css"
                        , "path"        => "/themes/" . $cm->oPage->getTheme() . "/css"
                        , "css_media"   => "print"
                        , "priority"    => "bottom"
                ));

                $print_css = ", overrideElementCSS: ['http://" . DOMAIN_INSET . FF_SITE_PATH . "/themes/" . $cm->oPage->getTheme() . "/css/print_" . $css_name . ".css']";
                break;
            }
        } while($real_path_info != ffCommon_dirname($real_path_info) && $real_path_info = ffCommon_dirname($real_path_info));
        

        $oButton_print->jsaction = "ff.load('jquery.plugins.printelement', function() { jQuery('#" . $component->id . "_data').printElement({ pageTitle : '" . $print_title . "'" . $print_css . " }); });";
        $oButton_print->label = ffTemplate::_get_word_by_code("ffRecord_print");
        $component->addActionButton($oButton_print, 0);
    }
}
