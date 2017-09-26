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
    
	$cufon_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . $oPage->theme . "/fonts/cufon/*");

	if(is_array($cufon_file) && count($cufon_file)) {
		$oPage->tplAddJs("cufon-yui", array(
        	"path" => "http://cufon.shoqolate.com/js"
        	, "file" => "cufon-yui.js?v=1.09i"
        ));        
	    foreach($cufon_file AS $real_file) {
	        if(is_file($real_file)) {
	            $relative_path = str_replace(FF_DISK_PATH, FF_SITE_PATH, $real_file);

		        $oPage->tplAddJs("cufon-yui." . ffGetFilename($relative_path), array(
        			"path" => ffCommon_dirname($relative_path)
        			, "file" => basename($relative_path)
		        )); 
	        }
	    }
	}
    