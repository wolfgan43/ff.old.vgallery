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
function set_generic_tags($description, $description_bypath = "") 
{
    $cm = cm::getInstance();
    $theme = $cm->oPage->theme;
    
    $haystack = $description;
    
    $haystack = str_replace("[SITE_PATH]", FF_SITE_PATH, $haystack);
        $haystack = str_replace("[LANGUAGE_INSET]", LANGUAGE_INSET, $haystack);
    $haystack = str_replace("[THEME_INSET]", $theme, $haystack);
    $haystack = str_replace("[DOMAIN_INSET]", DOMAIN_INSET, $haystack);
    $haystack = str_replace("[ENCODED_THIS_URL]", urlencode($_SERVER["REQUEST_URI"]), $haystack);
    if(strlen($description_bypath)) 
    	$haystack = str_replace("[DESCRIPTION_BY_PATH]", ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9\_]/', '', str_replace("/", "_", substr($description_bypath, 1)))), $haystack);

    return $haystack;
}
