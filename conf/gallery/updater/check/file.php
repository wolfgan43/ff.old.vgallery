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

$params = updater_get_params($cm);
if(!$params["invalid"]) {
	@set_time_limit(0);

	if($params["user_path"] != "" && is_file(FF_DISK_PATH . $params["user_path"])) {
		ini_set('auto_detect_line_endings',true);

		if(function_exists("output_header"))
			output_header(FF_DISK_PATH . $params["user_path"], "inline", $params["user_path"], "text/plain", null, null, "text/");
		//header("Content-type: text/plain");
		//header("Content-Disposition: inline filename=" . rawurlencode(basename($params["user_path"])));
		//header("Content-Length: " . filesize(FF_DISK_PATH . $params["user_path"]));
		readfile(FF_DISK_PATH . $params["user_path"]);
		exit;
	} else {
		$fs_exclude				= array();
		$mode 					= $_REQUEST["mode"];
		require(__DIR__ . "/exclude_fs.php");

		if(strpos(ffCommon_dirname(ffCommon_dirname(__FILE__)), FF_DISK_PATH) === false) {
			die("File Config Path Corrupted: Wrong[" . FF_DISK_PATH . "] => ChangeIn[" . ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__))))) . "]");
		} else {
			if($params["user_path"] != "" && $params["user_path"] == str_replace(FF_DISK_PATH, "", ffCommon_dirname(ffCommon_dirname(__FILE__)))) {
				$str_root_path = ffCommon_dirname(ffCommon_dirname(__FILE__));
			} else {
				$str_root_path = FF_DISK_PATH . $params["user_path"];
				$fs_exclude[str_replace(FF_DISK_PATH, "", ffCommon_dirname(ffCommon_dirname(__FILE__)))] = true;
			}
		}

		if(is_array($params["exclude"]["fs"]) && count($params["exclude"]["fs"])) {
			$fs_exclude = array_merge($fs_exclude, $params["exclude"]["fs"]);
		}

		$fs = updater_get_fs($str_root_path, $fs_exclude);
		ksort($fs);
		reset($fs);

		if($mode == "compact")
			echo md5(json_encode($fs));
		else
			echo json_encode($fs);
		exit;
	}
} else {
	die($params["invalid"]);
}

