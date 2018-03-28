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
if($_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"]) {
    Jobs::getInstance()->getScripts();

	if($_REQUEST["kid"]) {
		Jobs::getInstance()->exec($_REQUEST["kid"]);
	} else {
		$jobs = Jobs::getInstance()->run();
		if(is_array($jobs) && count($jobs)) {
			foreach($jobs AS $job) {
				//Cache::log(Cms::getUrl("/api/job", array("kid" => $job["kid"]), "545454");
				Jobs::async("/api/job", array("kid" => $job["kid"]));
			}
		}
	}


} else {
	Cache::log(print_r($_SERVER, true), "job_server");
	http_response_code("401");
}
exit;