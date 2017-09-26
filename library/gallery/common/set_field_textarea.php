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
function set_field_textarea($component = null) {
    $cm = cm::getInstance();
    $textarea_choose = global_settings("TEXTAREA_DECISION");

    $rev = array(
                "codemirror" => "editarea", 
                "editarea" => "codemirror"
                );
    
    if($component !== null)
    {
        $component->widget = "";
        if(strlen($textarea_choose) &&  is_dir(FF_DISK_PATH . "/themes/library/". $textarea_choose) && is_dir(FF_DISK_PATH . "/themes/responsive/ff/ffField/widgets/" . $textarea_choose)) {
            $component->widget = $textarea_choose;
        } elseif (is_dir(FF_DISK_PATH . "/themes/library/" . $rev[$textarea_choose]) && is_dir(FF_DISK_PATH . "/themes/responsive/ff/ffField/widgets/" . $rev[$textarea_choose])) {
            $component->widget = $rev[$textarea_choose];

        }
    } else {
        $component = true; 
    }  
    return $component;
}