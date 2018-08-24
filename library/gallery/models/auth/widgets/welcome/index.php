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
$config_default = array(
    "avatar" => array(
        "mode" => "200-200"
        , "noavatar" => null
    )
);

$config = array_replace_recursive($config_default, (array) $config);

$path = Auth::_getDiskPath("tpl") . ($config["tpl_path"]
        ? $config["tpl_path"]
        : "/welcome"
    );
$html_name = "/index.html";
$css_name = "/style.css";
$script_name = "/script.js";

if(!is_file($file)) {
    $file = __DIR__ . $html_name;
}

$filename = (is_file($path . $html_name)
    ? $path . $html_name
    : __DIR__ . $html_name
);
$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file(basename($filename), "main");

$user = Auth::getUser();
if(strlen($user["name"])) {
    $nome = $user["name"];
} elseif(strlen($user["person"]["name"]) || strlen($user["person"]["surname"])) {
    $nome = $user["person"]["name"] . " " . $user["person"]["surname"];
} else {
    $nome = $user["user"]["username"];
}
$tpl->set_var("user_name", $nome);
$tpl->set_var("user_email", $user["email"]);
$tpl->set_var("user_avatar", Auth::getUserAvatar($config["avatar"], $user["avatar"]));

$html = $tpl->rpparse("main", false);

$css = file_get_contents(ffMedia::getFileOptimized(is_file($path . $css_name)
    ? $path . $css_name
    : __DIR__ . $css_name
));
$js = file_get_contents(ffMedia::getFileOptimized(is_file($path . $script_name)
    ? $path . $script_name
    : __DIR__ . $script_name
));

$output = array(
    "html"  => $html
    , "css" => $css
    , "js"  => $js
);

return $output;

