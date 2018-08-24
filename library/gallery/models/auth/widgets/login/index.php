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
        "api"                   => array(
            "login"             => Auth::API_PATH . "/login"
            , "logout"          => Auth::API_PATH . "/logout"
            , "recover"         => Auth::API_PATH . "/recover"
            , "registration"    => Auth::API_PATH . "/registration"
            , "activation"      => Auth::API_PATH . "/activation"
        )
        , "title"               => array(
            "enable"            => false
            , "value"           => "login_title"
        )
        , "subtitle"               => array(
            "enable"            => false
            , "value"           => "login_subtitle"
        )
        , "domain"              => false
        , "stay_connect"        => true
        , "referer"             => $_SERVER["HTTP_REFERER"]
        , "redirect"            => "/"
        , "tpl_path"            => null
        , "registration"        => array(
            "enable"            => true
            , "path"            => Auth::API_PATH . "/registration.html"
        )
        , "recover"             => array(
            "username"          => array(
                "enable"        => false
                , "path"        => Auth::API_PATH . "/recover/username.html"
            )
            , "password"        => array(
                "enable"        => true
                , "path"        => Auth::API_PATH . "/recover/password.html"
            )
        )
        , "social"              => array(
            "facebook"          => array(
                "enable"        => Auth::env("FACEBOOK_APP_ID") && Auth::env("FACEBOOK_APP_SECRET")
                , "path"        => Auth::API_PATH . "/social/facebook"
                , "icon"        => cm_getClassByFrameworkCss("facebook", "icon")
                , "name"        => "Facebook"
                , "title"       => "mod_auth_social_facebook"
                , "app"         => array(
                    "id"        => Auth::env("FACEBOOK_APP_ID")
                    , "secret"  => Auth::env("FACEBOOK_APP_SECRET")
                    , "scope"   => Auth::env("FACEBOOK_APP_SECRET")
                )
            )
            , "gplus"           => array(
                "enable"        => Auth::env("GPLUS_APP_ID") && Auth::env("GPLUS_APP_SECRET")
                , "path"        => Auth::API_PATH . "/social/gplus"
                , "icon"        => cm_getClassByFrameworkCss("gplus", "icon")
                , "name"        => "GooglePlus"
                , "title"       => "mod_auth_social_gplus"
                , "app"         => array(
                    "id"      => Auth::env("GPLUS_APP_ID")
                    , "secret"  => Auth::env("GPLUS_APP_SECRET")
                    , "scope"   => Auth::env("GPLUS_APP_SCOPE")
                )
            )
            , "twitter"         => array(
                "enable"        => Auth::env("TWITTER_APP_ID") && Auth::env("TWITTER_APP_SECRET")
                , "path"        => Auth::API_PATH . "/social/twitter"
                , "icon"        => cm_getClassByFrameworkCss("twitter", "icon")
                , "name"        => "Twitter"
                , "title"       => "mod_auth_social_twitter"
                , "app"         => array(
                    "id"        => Auth::env("TWITTER_APP_ID")
                    , "secret"  => Auth::env("TWITTER_APP_SECRET")
                    , "scope"   => Auth::env("TWITTER_APP_SCOPE")
                )
            )
            , "linkedin"        => array(
                "enable"        => Auth::env("LINKEDIN_APP_ID") && Auth::env("LINKEDIN_APP_SECRET")
                , "path"        => Auth::API_PATH . "/social/linkedin"
                , "icon"        => cm_getClassByFrameworkCss("linkedin", "icon")
                , "name"        => "Linkedin"
                , "title"       => "mod_auth_social_linkedin"
                , "app"         => array(
                    "id"        => Auth::env("LINKEDIN_APP_ID")
                    , "secret"  => Auth::env("LINKEDIN_APP_SECRET")
                    , "scope"   => Auth::env("LINKEDIN_APP_SCOPE")
                )
            )
            , "dribble"         => array(
                "enable"        => Auth::env("DRIBBLE_APP_ID") && Auth::env("DRIBBLE_APP_SECRET")
                , "path"        => Auth::API_PATH . "/social/dribble"
                , "icon"        => cm_getClassByFrameworkCss("dribble", "icon")
                , "name"        => "Dribble"
                , "title"       => "mod_auth_social_dribble"
                , "app"         => array(
                    "id"        => Auth::env("DRIBBLE_APP_ID")
                    , "secret"  => Auth::env("DRIBBLE_APP_SECRET")
                    , "scope"   => Auth::env("DRIBBLE_APP_SCOPE")
                )
            )
        )
    );

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $response = Auth::login();

        if(isset($response["status"]) && $response["status"] === "0") {
            $response["welcome"] = Auth::widget("welcome");
        }
        if($_REQUEST["redirect"]) {
            $response["redirect"] = $_REQUEST["redirect"];
        }

        Api::send($response);
    } else {
        $config = array_replace_recursive($config_default, (array) $config);

        $path = Auth::_getDiskPath("tpl") . ($config["tpl_path"]
                ? $config["tpl_path"]
                : "/login"
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

        $token = Auth::password();
        $tpl->set_var("csrf_token", $token);

        $tpl->set_var("login_url", $config["api"]["login"]);

        if(isset($_REQUEST["ret_url"])) {
            $ret_url = $_REQUEST["ret_url"];
        } elseif(isset($_REQUEST["redirect"])) {
            $ret_url = $_REQUEST["redirect"];
        } elseif($config["redirect"]) {
            $ret_url = $config["redirect"];
        }
        $tpl->set_var("ret_url", $ret_url);

        if($config["title"]["enable"]) {
            $tpl->set_var("login_page_title", ffTemplate::_get_word_by_code($config["title"]["value"]));
            $tpl->parse("SezPageTitle", false);
        }
        if($config["subtitle"]["enable"]) {
            $tpl->set_var("login_page_subtitle", ffTemplate::_get_word_by_code($config["subtitle"]["value"]));
            $tpl->parse("SezPageSubtitle", false);
        }

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

        $show_recover = false;
        if($config["recover"]["password"]["enable"]) {
            $show_recover = true;
            $tpl->set_var("recover_password_path", $config["recover"]["password"]["path"]);
            $tpl->parse("SezRecoverPassword", false);
        }
        if($config["recover"]["username"]["enable"]) {
            $show_recover = true;
            $tpl->set_var("recover_username_path", $config["recover"]["username"]["path"]);
            $tpl->parse("SezRecoverUsername", false);
        }
        if($show_recover) {
            $tpl->parse("SezRecoverContainer", false);
        }

        $show_social = false;
        if(is_array($config["social"]) && count($config["social"])) {
            foreach($config["social"] AS $social_name => $social_setting) {
                if($social_setting["enable"]) {
                    $show_social = true;
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
        if($show_social) {
            $tpl->parse("SezSocialContainer", false);
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
