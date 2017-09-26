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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

$globals = ffGlobals::getInstance("gallery");
check_function("system_sitemap");

switch($cm->processed_rule_attrs["id"]) {
    case "sitemap":
        $ext_default = "xml";
        $allow_index = true;
        $allowed_ext = array("xml");
        break;
    case "feed":
        $ext_default = "rss";
        $allow_index = false;
        $allowed_ext = array("mrss", "rss");
        break;
    case "manifest":
        /*
{
  "lang": "en",
  "dir": "ltr",
  "name": "Super Racer 3000",
  "description": "The ultimate futuristic racing game from the future!",
  "short_name": "Racer3K",
  "icons": [{
    "src": "icon/lowres.webp",
    "sizes": "64x64",
    "type": "image/webp"
  },{
    "src": "icon/lowres.png",
    "sizes": "64x64"
  }, {
    "src": "icon/hd_hi",
    "sizes": "128x128"
  }],
  "scope": "/racer/",
  "start_url": "/racer/start.html",
  "display": "fullscreen",
  "orientation": "landscape",
  "theme_color": "aliceblue",
  "background_color": "red",
  "screenshots": [{
    "src": "screenshots/in-game-1x.jpg",
    "sizes": "640x480",
    "type": "image/jpeg"
  },{
    "src": "screenshots/in-game-2x.jpg",
    "sizes": "1280x920",
    "type": "image/jpeg"
  }]
}        
*/
        if($cm->real_path_info == "/manifest.json" && is_array($globals->manifest) && count($globals->manifest)) {
            echo ffCommon_jsonenc($globals->manifest, true);
        } else {
            if($cm->isXHR())
                http_response_code(500);
            else
                http_response_code(404);
        }
        exit;
    default:
}

$arrPath = explode("/params", $cm->real_path_info);

$user_path = $arrPath[0];

$arrParams = explode(".", trim($arrPath[1], "-"));
if(count($arrParams) > 1) {
    $ext = $arrParams[1];
    $target = $arrParams[0];
} else {
    $ext = $arrParams[0];
}
if(!$ext)
    $ext = $ext_default;

if(array_search($ext, $allowed_ext) !== false) {
    $real_user_path = $user_path;
    if(strpos($real_user_path, $globals->strip_user_path) === 0) 
        $real_user_path = substr($real_user_path, strlen($globals->strip_user_path));

    if($allow_index && !$target && !$real_user_path) {
        $res = system_get_sitemap_index($ext, $globals->strip_user_path);
    } else {
        $domain_target = system_check_sitemap($target);
        if($domain_target) {
            ffRedirect("http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $domain_target . $_SERVER["REQUEST_URI"], 301);
        }
        $res = system_get_sitemap($target, $ext, $user_path);

        if($res["error"]) {
            if($cm->isXHR())
                http_response_code(500);
            else
                http_response_code(404);
        }
    }

    if($res["mime"])
        header("Content-type: " . $res["mime"]);
}

if($res["content"]) {
    echo ffCommon_utf8_for_xml($res["content"]);
} else {
    if($cm->isXHR())
        http_response_code(500);
    else
        http_response_code(404);
}
exit;