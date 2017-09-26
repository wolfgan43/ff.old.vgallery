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
function process_forms_framework($value, $params, $user_path, $layout = null) {
	$cm = cm::getInstance();
	
	$location = $layout["location"];
	$unic_id = $layout["prefix"] . $layout["ID"];
	$layout_settings = $layout["settings"];

	if(file_exists(FF_DISK_PATH . $value . "/index." . FF_PHP_EXT)) {
		if(!is_array($params)) 
		{
			$arrParams = explode("|", $params);
			if(is_array($arrParams) && count($arrParams)) {
				foreach($arrParams AS $arrParams_value) {
					$arrParamsValue = explode(":", $arrParams_value);
					if(is_array($arrParamsValue) && count($arrParamsValue)) {
						$applet_params[$arrParamsValue[0]] = $arrParamsValue[1];
					}
				}
			}
		}
		require(FF_DISK_PATH . $value . "/index." . FF_PHP_EXT);
	}
	if($layout)
		return array("content" => $out_buffer);
	else
		return $out_buffer;
}
