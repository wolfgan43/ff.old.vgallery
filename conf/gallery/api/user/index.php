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



switch (basename($cm->real_path_info, "." . vgCommon::PHP_EXT)) {
    case "login":
        $return = Auth::login(array("type" => "token"));
        break;
    case "logout":
        $return = Auth::logout(array("type" => "token"));
        break;
    case "registration":
        $return = Auth::registration();
        break;
    case "check":
        $return = Auth::check();
        break;
    case "refresh":
        $return = Auth::check(); //da impostare il refrsh con la request
        break;
    case "recover":
        $return = Auth::rec();
        break;
    case "activation":
        $return = Auth::activation();
        break;
    case "key":
        $return = Auth::key();
        break;
    case "share":
        $return = Auth::share();
        break;
    case "join":
        $return = Auth::join();
        break;
    case "certificate":
        $return = Auth::createCertificate();
        break;
    default:
        http_response_code("501");
}
if(!is_array($return)) {
    http_response_code("500");
    if(DEBUG_MODE === true) {
        $res["error"] = $return;
        $res["status"] = "500";
    }
} else {
    $res = $return;
    if ($res["status"])
        http_response_code("400");
}
echo ffCommon_jsonenc($res, true);
exit;