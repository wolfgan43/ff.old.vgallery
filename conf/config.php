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
if(defined("SHOWFILES_IS_RUNNING"))
{
    $ff = ffGlobals::getInstance("ff");

    if ($ff->showfiles_events) {
        $ff->showfiles_events->addEvent("on_warning", "showfiles_on_missing_resource", ffEvent::PRIORITY_NORMAL);
        $ff->showfiles_events->addEvent("on_error", "showfiles_on_before_parsing_error", ffEvent::PRIORITY_NORMAL);
    }
    //cm::_addEvent("showfiles_on_error", "showfiles_on_before_parsing_error", ffEvent::PRIORITY_NORMAL);

	function showfiles_on_missing_resource($user_path, $referer, $mode)
	{
		$res = null;

		spl_autoload_register(function ($class) {
			switch ($class) {
				case "Cache":
					require (__CMS_DIR__ . "/library/gallery/classes/" . strtolower($class) . "/" . $class . ".php");
					break;
				case "vgCommon":
					require(__CMS_DIR__ . "/library/gallery/classes/" . $class . ".php");
					break;
			}
		});

		Cache::log("Mode: " . $mode . " URL: " . $_SERVER["HTTP_HOST"] . $user_path . " REFERER: " . $referer, "resource_missing");

		if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/conf/common.php")) {
			require_once(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/conf/common.php");
			if(function_exists("cms_showfiles_on_warning")) {
				$res = cms_showfiles_on_warning($user_path, $referer, $mode);
			}
		}

		if(!$res) {
			//da migliorare usando cache_olp_path e quindi redirect 301
			$mime = ffMimeTypeByFilename($_SERVER["REQUEST_URI"]);
			switch ($mime) {
				case "image/svg+xml":
				case "image/jpeg":
				case "image/png":
				case "image/gif":
					$res["base_path"] = FF_DISK_PATH . FF_THEME_DIR;
					if (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/noimg.svg")) {
						$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/noimg.svg";
					} else {
						$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . CM_DEFAULT_THEME . "/images/noimg.svg";
					}

					if (stripos($mode, "x") !== false) {
						$res["wizard"]["mode"] = explode("x", strtolower($mode));
						$res["wizard"]["method"] = "proportional";
						$res["wizard"]["resize"] = true;
					} elseif (strpos($mode, "-") !== false) {
						$res["wizard"]["mode"] = explode("-", $mode);
						$res["wizard"]["method"] = "crop";
						$res["wizard"]["resize"] = false;
					}
					break;
				default:

			}
		}

		if(!$res)
			http_response_code(404);

		return $res;
	}

	function showfiles_on_before_parsing_error($strError)
    {
        http_response_code(404);    //da migliorare usando cache_olp_path e quindi redirect 301
        exit;
    }
}