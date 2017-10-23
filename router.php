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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

/**
 * Config Project
 */
define("FF_SKIP_COMPONENTS", true);
require_once("config.php");

require_once(__CMS_DIR__ . "/library/gallery/system/cache.php");

require_once("ff/main.php");

require_once(__CMS_DIR__ . "/library/gallery/system/gallery_redirect.php");

$path_info = $_SERVER["PATH_INFO"];
if($path_info == "/index")
    $path_info = "";

if($path_info) {
	system_gallery_redirect($path_info);
}
exit;
    
