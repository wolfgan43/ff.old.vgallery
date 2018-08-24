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

    );
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $response = Auth::login();

        Api::send($response);
    } else {
        $config = array_replace_recursive($config_default, (array) $config);

        $path = Auth::_getDiskPath("tpl") . ($config["tpl_path"]
                ? $config["tpl_path"]
                : "/registration"
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
        $config["recover"]["username"]["enable"] = true;
        $config["recover"]["password"]["enable"] = true;
        $config["registration"]["enable"] = true;
        $config["social"]["gplus"]["enable"] = true;
        $config["social"]["twitter"]["enable"] = true;
        $config["social"]["linkedin"]["enable"] = true;
        $config["social"]["dribble"]["enable"] = true;
        $config["social"]["ff"]["enable"] = true;
        $config["recover"]["password"]["path"] = "/registrazione/recupero-password";

        $token = Auth::password();
        $tpl->set_var("csrf_token", $token);

        $tpl->set_var("login_url", $config["api"]["login"]);

        $tpl->set_var("login_url", $config["api"]["login"]);
        if($config["domain"]) {
            $tpl->parse("SezDomain", false);
        } else {
            $tpl->set_var("domain_name", $_SERVER["HTTP_HOST"]);
            $tpl->parse("SezDomainHidden", false);
        }
        if($config["stay_connect"]) {
            $tpl->parse("SezStayConnect", false);
        }

        if($config["registration"]["enable"]) {
            $tpl->set_var("register_path", $config["registration"]["path"]);
            $tpl->parse("SezRegistration", false);
        }
        if($config["recover"]["password"]["enable"]) {
            $tpl->set_var("recover_password_path", $config["recover"]["password"]["path"]);
            $tpl->parse("SezRecoverPassword", false);
        }
        if($config["recover"]["username"]["enable"]) {
            $tpl->set_var("recover_username_path", $config["recover"]["username"]["path"]);
            $tpl->parse("SezRecoverUsername", false);
        }


        if(is_array($config["social"]) && count($config["social"])) {
            foreach($config["social"] AS $social_name => $social_setting) {
                if($social_setting["enable"]) {
                    $tpl->set_var("social_class", $social_name);
                    $tpl->set_var("social_dialog_name", $social_setting["title"]);
                    $tpl->set_var("social_path", $social_setting["path"]);
                    $tpl->set_var("social_icon", $social_setting["icon"]);
                    $tpl->set_var("social_name", $social_setting["name"]);
                    $tpl->set_var("social_text_button", ffTemplate::_get_word_by_code(str_replace("-", "_", $social_name) . "_social_login"));

                    $tpl->parse("SezSocialLogin", true);
                }
            }
        }

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

        if(Auth::_isXHR()) {
            Api::send($output);
        }

        return $output;
    }
