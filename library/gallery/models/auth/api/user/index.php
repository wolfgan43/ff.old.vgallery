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
        $res = Auth::login(array("type" => "token"));
        break;
    case "logout":
        $res = Auth::logout(array("type" => "token"));
        break;
    case "registration":
        $res = Auth::registration();
        break;
    case "check":
        $res = Auth::check();
        break;
    case "refresh":
        $res = Auth::check(); //da impostare il refrsh con la request
        break;
    case "recover":
        break;
    case "activation":
        break;
    default:
        http_response_code("501");
}

if($res["status"])
    http_response_code("400");

echo ffCommon_jsonenc($res, true);
exit;