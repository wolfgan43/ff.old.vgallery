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
 * @subpackage connector
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */ 

	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["enable"]) && strlen($actual_srv["enable"])) 
    {
        if($actual_srv["base"])
            $globals->favicon["icon"] = explode("\n", $actual_srv["base"]);
        if($actual_srv["shortcut-icon"])
            $globals->favicon["shortcut icon"] = $actual_srv["shortcut-icon"];
        if($actual_srv["mask-icon"])
            $globals->favicon["mask-icon"] = $actual_srv["mask-icon"];
        if($actual_srv["apple-touch-icon"])
            $globals->favicon["apple-touch-icon"] = $actual_srv["apple-touch-icon"];

        if($actual_srv["icons"]) {
        	//$globals->favicon["base"] = $actual_srv["base"];
		}
    }
?>
