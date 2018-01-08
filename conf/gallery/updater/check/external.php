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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
	require_once(__DIR__ . "/common.php");

	$params = updater_get_params($cm, true);
	if(!$params["invalid"]) {
        @set_time_limit(0);   
        if($params["user_path"] != "" && is_file(FF_DISK_PATH . $params["user_path"])) {
            readfile(FF_DISK_PATH . $params["user_path"]);
            exit;
        } else {
            $mode = $_REQUEST["mode"];  
            
            //require(ffCommon_dirname(__FILE__) . "/exclude_fs.php");
			$str_root_path = FF_DISK_PATH . $params["user_path"];
			
            $fs = updater_get_fs($str_root_path);
            if(is_array($fs)) {
	            ksort($fs);
	            reset($fs);
			} else {
				$fs = array();
			}

            if($mode == "compact") 
                    echo md5(json_encode($fs));
                else
                    echo json_encode($fs);

            exit;
        }
    } else {
        die($params["invalid"]);
    }
