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

function process_login($user_path, &$layout) {
    $cm = cm::getInstance();
    
    $framework_css = array(
        "block" => array(
            "container" => array(
                "class" => "login-box"
                , "col" => array(
                    "xs" => 12
                    , "sm" => 12
                    , "md" => 6
                    , "lg" => 6
                )
            )		
            , "fields" => array(
                "class" => null
                , "col" => array(
                    "xs" => 12
                    , "sm" => 12
                    , "md" => 12
                    , "lg" => 12
                )
            )
            , "lost_password" => array(
                "class" => "lostpassword"
                , "util" => "align-right"
            )
            , "register" => array(
                "class" => "register"
            )
        )
        , "actions" => array(
            "def" => array(
                "class" => "actions"
                , "col" => array(
                    "xs" => 12
                    , "sm" => 12
                    , "md" => 12
                    , "lg" => 12
                )
                , "util" => "right"
            )
            , "buttons" => array(
                "class" => "button login-button"
                , "button" => "primary"
            )
        )
    );
    
    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];

	$template_name = ($layout["template"] ? $layout["template"]. "_" : "");
    if (Auth::isGuest()) {
        //if (!$cm->isXHR() && ($user_path == "/login" || strpos($user_path , "/login/") !== false)) //non processa il block_login se siamo nella pagina di login
            //return array("content" => "");

        if (!$layout_settings["AREA_SHOW_LOGIN"])
            return array("content" => "");

        $base_template_name = "login";
       // $base_category_name  = "login";

	    if (strlen($layout_settings["AREA_LOGIN_REGISTER"]))
	        $check_register = true;
    } else {
        if (!$layout_settings["AREA_SHOW_LOGOUT"])
            return array("content" => "");

         $base_template_name = "logout";

        $user = Auth::get("user");
        $template_prefix = ffCommon_url_rewrite($user->acl_primary);
    }

    $tpl_data["id"] = $unic_id;
    $tpl_data["prefix"] = $template_prefix;
	$tpl_data["custom"] = $base_template_name . ".html";
    $tpl_data["base"] = $template_name . $base_template_name . ".html";
    $tpl_data["path"] = $layout["tpl_path"];
    
    $tpl_data["result"] = get_template_cascading($user_path, $tpl_data);

    $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
    //$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
    $tpl->load_file($tpl_data["result"]["name"], "main");





    

    /**
     * Admin Father Bar
     */
    if (Auth::env("AREA_LOGIN_SHOW_MODIFY")) {
        $admin_menu["admin"]["unic_name"] = $unic_id;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if (Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
            $admin_menu["admin"]["extra"] = "";
        }
        if (Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if (Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if (Auth::env("AREA_SETTINGS_SHOW_MODIFY")) {
            $admin_menu["admin"]["setting"] = ""; //$layout["type"]; 
        }

        $admin_menu["sys"]["path"] = $user_path;
        $admin_menu["sys"]["type"] = "admin_toolbar";
       // $admin_menu["sys"]["ret_url"] = $ret_url;
    }

    /**
     * Process Block Header
     */
    if (check_function("set_template_var"))
        $block = get_template_header($user_path, $admin_menu, $layout, $tpl);

    //LOGIN BOX
    if (Auth::isGuest()) {
        $tmp_data_field["username"]["id"] = $unic_id . "username";
        $tmp_data_field["username"]["content"] = ffTemplate::_get_word_by_code("login_username");
        $tmp_data_field["username"]["onkeydown"] = "return ff.submitProcessKey(event, '" . $unic_id . "btLogin')";
        $tmp_data_field["username"]["default"] = '<label for="' . $tmp_data_field["username"]["id"] . '">'
                . $tmp_data_field["username"]["content"]
                . '</label>'
                . '<input id="' . $tmp_data_field["username"]["id"]
                . '" type="text" onkeydown="' . $tmp_data_field["username"]["onkeydown"]
                . '" />';

        $tmp_data_field["password"]["id"] = $unic_id . "password";
        $tmp_data_field["password"]["content"] = ffTemplate::_get_word_by_code("login_password");
        $tmp_data_field["password"]["onkeydown"] = "return ff.submitProcessKey(event, '" . $unic_id . "btLogin')";
        $tmp_data_field["password"]["default"] = '<label for="' . $tmp_data_field["password"]["id"] . '">'
                . $tmp_data_field["password"]["content"]
                . '</label>'
                . '<input id="' . $tmp_data_field["password"]["id"]
                . '" type="password" onkeydown="' . $tmp_data_field["password"]["onkeydown"]
                . '" />';

        if (strlen($layout_settings["AREA_LOGIN_FORCE_RETURL"])) {
            if (
                    substr($layout_settings["AREA_LOGIN_FORCE_RETURL"], 0, 1) != "/"
            ) {
                $tmp_data_field["login"]["data-returl"] = $layout_settings["AREA_LOGIN_FORCE_RETURL"];
            } else {
                if (strpos($layout_settings["AREA_LOGIN_FORCE_RETURL"], "#") !== false) {
                    $part_alternative_hash = substr($layout_settings["AREA_LOGIN_FORCE_RETURL"], strpos($layout_settings["AREA_LOGIN_FORCE_RETURL"], "#"));
                    $alternative_path = substr($layout_settings["AREA_LOGIN_FORCE_RETURL"], 0, strpos($layout_settings["AREA_LOGIN_FORCE_RETURL"], "#"));
                }

                if (strpos($layout_settings["AREA_LOGIN_FORCE_RETURL"], "?") !== false) {
                    $part_alternative_path = substr($layout_settings["AREA_LOGIN_FORCE_RETURL"], 0, strpos($layout_settings["AREA_LOGIN_FORCE_RETURL"], "?"));
                    $part_alternative_url = substr($layout_settings["AREA_LOGIN_FORCE_RETURL"], strpos($layout_settings["AREA_LOGIN_FORCE_RETURL"], "?"));
                } else {
                    $part_alternative_path = $layout_settings["AREA_LOGIN_FORCE_RETURL"];
                }
                if (check_function("normalize_url") && check_function("get_international_settings_path")) {
                    $res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);

                    $tmp_data_field["login"]["data-returl"] = normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash;
                }
            }
        }

        $tmp_data_field["login"]["id"] = $unic_id . "btLogin";
        $tmp_data_field["login"]["href"] = "javascript:void(0);";
        $tmp_data_field["login"]["class"] = "login-ajax";
        $tmp_data_field["login"]["content"] = ffTemplate::_get_word_by_code("login_button");
        $tmp_data_field["login"]["default"] = '<a id="' . $tmp_data_field["login"]["id"]
                . '" href="' . $tmp_data_field["login"]["href"]
                . '" class="' . $tmp_data_field["login"]["class"]
                . '">'
                . $tmp_data_field["login"]["content"]
                . '</a>';

        if ($layout_settings["AREA_USER_SHOW_ECOMMERCE_CART_LOGIN"] && check_function("process_user_menu")) {
            $rel_layout = $layout;
            $rel_layout["settings"]["AREA_USER_SHOW_ECOMMERCE_CART"] = $layout_settings["AREA_USER_SHOW_ECOMMERCE_CART_LOGIN"];
            $rel_layout["settings"]["AREA_USER_TEMPLATE"] = "login";
            $rel_layout["prefix"] = $layout["prefix"] . "M";
            $res = process_user_menu(null, null, Cms::env("AREA_SHOW_ECOMMERCE"), $user_path, $rel_layout, true, false);

            $tmp_data_field["user-menu"] = $res["content"];
        }
        
        $tiny_lang_code = strtolower(substr(LANGUAGE_INSET, 0, 2));
        if (strlen($layout_settings["AREA_LOGIN_REGISTER"])) {
            $tmp_data_field["register"]["href"] = FF_SITE_PATH . ($cm->router->getRuleById("mod_auth_register_" . $tiny_lang_code)
                                                        ? $cm->router->getRuleById("mod_auth_register_" . $tiny_lang_code)->reverse
                                                        :  ($cm->router->getRuleById("mod_auth_register")
                                                            ? $cm->router->getRuleById("mod_auth_register")->reverse
                                                            : ""
                                                        ) 
                                                    );        
            $tmp_data_field["register"]["class"] = "register";
            $tmp_data_field["register"]["content"] = ffTemplate::_get_word_by_code("register_title");
            $tmp_data_field["register"]["default"] = '<a href="' . $tmp_data_field["register"]["href"] . '" class="' . $tmp_data_field["register"]["class"] . '">' . $tmp_data_field["register"]["content"] . '</a>';
        }
        $tmp_data_field["activation"]["href"] = FF_SITE_PATH . ($cm->router->getRuleById("mod_auth_activation_" . $tiny_lang_code)
                                                                    ? $cm->router->getRuleById("mod_auth_activation_" . $tiny_lang_code)->reverse
                                                                    :  $cm->router->getRuleById("mod_auth_activation")->reverse
                                                                );
        $tmp_data_field["activation"]["class"] = "activation";
        $tmp_data_field["activation"]["content"] = ffTemplate::_get_word_by_code("activation_title");
        $tmp_data_field["activation"]["default"] = '<a href="' . $tmp_data_field["activation"]["href"] . '" class="' . $tmp_data_field["activation"]["class"] . '">' . $tmp_data_field["activation"]["content"] . '</a>';

        $tmp_data_field["recover"]["href"] = FF_SITE_PATH . ($cm->router->getRuleById("mod_auth_recover_" . $tiny_lang_code)
                                                                ? $cm->router->getRuleById("mod_auth_recover_" . $tiny_lang_code)->reverse
                                                                :  $cm->router->getRuleById("mod_auth_recover")->reverse
                                                            );
        $tmp_data_field["recover"]["class"] = "recover";
        $tmp_data_field["recover"]["content"] = ffTemplate::_get_word_by_code("lost_password_title");
        $tmp_data_field["recover"]["default"] = '<a href="' . $tmp_data_field["recover"]["href"] . '" class="' . $tmp_data_field["recover"]["class"] . '">' . $tmp_data_field["recover"]["content"] . '</a>';
        
        if ($tpl_data["result"]["type"] == "custom" || $tpl_data["result"]["type"] == "category") {
            if (is_array($tmp_data_field) && count($tmp_data_field)) {
                foreach ($tmp_data_field AS $tmp_data_field_key => $tmp_data_field_value) {
                    foreach ($tmp_data_field_value AS $tmp_data_field_prop_key => $tmp_data_field_prop_value) {
                        $field_attr = strtolower($tmp_data_field_prop_key == "default" ? $tmp_data_field_key : $tmp_data_field_key . ":" . $tmp_data_field_prop_key
                        );
                        if ($tpl->isset_var($field_attr)) {
                            $tpl->set_var($field_attr, $tmp_data_field_prop_value);
                        }
                    }
                }
            }
        } else {
            $tpl->set_var("unic_id", $unic_id);
            if ($layout_settings["AREA_LOGIN_SHOW_TITLE"])
                $tpl->parse("SezLoginTitle", false);

            $tpl->set_var("force_ret_url", $tmp_data_field["login"]["data-returl"]);
            $tpl->set_var("user_menu", $tmp_data_field["user-menu"]);
        }

        $enable_social = false;

            if (strlen($layout_settings["AREA_LOGIN_REGISTER"])) {
                $tpl->set_var("link_register_class", Cms::getInstance("frameworkcss")->getClass($framework_css["block"]["register"]));
                $tpl->set_var("register_link", $tmp_data_field["register"]["href"]);
                $tpl->parse("SezRegister", false);
            }
            
            if ($layout_settings["AREA_LOGIN_SHOW_LOSTPASSWORD"]) {
                $tpl->set_var("lostpassword_class", Cms::getInstance("frameworkcss")->getClass($framework_css["block"]["lost_password"]));
                $tpl->set_var("user_path", $tmp_data_field["recover"]["href"]);
                $tpl->parse("SezLostPassword", false);
            }

            if (cm::env("MOD_AUTH_SOCIAL_FACEBOOK")) {
                $tpl->set_var("fb_appid", cm::env("MOD_AUTH_SOCIAL_FACEBOOK_CLIENT_ID"));

                $tpl->set_var("ajax_loader", Cms::getInstance("frameworkcss")->get("spinner", "icon-tag", "spin"));
                $tpl->set_var("logged_message", ffTemplate::_get_word_by_code("fb_wait_loading"));
                $tpl->parse("SectAjaxLoader", false);

                $tpl->parse("SezLoginExtendedFB", false);
                $tpl->set_var("SezLoginShortFB", "");

                $enable_social = true;
            }

            if (strlen($layout_settings["AREA_LOGIN_SHOW_LABEL"])) {
                $tpl->parse("SezShowLabelUsername", false);
                $tpl->parse("SezShowLabelPassword", false);
            } else {
                $tpl->set_var("SezShowLabelUsername", "");
                $tpl->set_var("SezShowLabelPassword", "");
            }
            
            $tpl->set_var("container_class", Cms::getInstance("frameworkcss")->getClass($framework_css["block"]["container"]));
            $tpl->set_var("fields_class", Cms::getInstance("frameworkcss")->getClass($framework_css["block"]["fields"]));
            
            
            
            
            $tpl->set_var("actions_class", Cms::getInstance("frameworkcss")->getClass($framework_css["actions"]["def"]));
            $tpl->set_var("buttons_class", Cms::getInstance("frameworkcss")->getClass($framework_css["actions"]["buttons"]));
            
        

        if ($enable_social) {
            $tpl->set_var("class_social", " social");
        }
    } else { //LOGOUT BOX
        $arrLogout = array();
        $arrLogout["avatar"] = Auth::getUserAvatar();
        $arrLogout["username"] = Auth::get("user")->username;

        $anagraph = Auth::get();

        $arrLogout["name"] = $anagraph["person"]["name"];
        $arrLogout["surname"] = $anagraph["person"]["surname"];
        if ($tpl_data["result"]["type"] == "custom" || $tpl_data["result"]["type"] == "category") {
            if (is_array($arrLogout) && count($arrLogout)) {
                foreach ($arrLogout AS $key => $value) {
                    if ($tpl->isset_var($key)) {
                        $tpl->set_var($key, $value); 
                    }
                }
            }
        }
        if ($layout_settings["AREA_LOGIN_SHOW_TITLE"])
            $tpl->parse("SezLogoutTitle", false);
        else
            $tpl->set_var("SezLogoutTitle", "");

        if ($layout_settings["AREA_LOGOUT_SHOW_AVATAR"]) {
            if(isset($arrLogout["avatar"])) {
				$arrSize = explode("x", cm::env("MOD_AUTH_USER_AVATAR"));
				
				if(!is_numeric($arrSize[0]) || !$arrSize[0] > 0)
					$arrSize[0] = 80;
				if(!is_numeric($arrSize[0]) || !$arrSize[1] > 0)
					$arrSize[1] = 80;

                $tpl->set_var("user_avatar", '<img src="' . $arrLogout["avatar"] . '" width="' . $arrSize[0] . '" height="' . $arrSize[1] . '" alt="' . $arrLogout["username"] . '" />');
                $tpl->parse("SezLogoutImage", false);
            }
        }    else {
            $tpl->set_var("SezLogoutImage", "");
        }

        if ($layout_settings["AREA_LOGOUT_SHOW_USERNAME"]) {
            $tpl->set_var("logout", $arrLogout["username"]);
            $tpl->parse("SezLogoutUsername", false);
        } else {
            $tpl->set_var("SezLogoutUsername", "");
        }

        if (check_function("process_user_menu")) {
            $rel_layout = $layout;
            $rel_layout["prefix"] = $layout["prefix"] . "M";
            $rel_layout["settings"]["AREA_USER_TEMPLATE"] = "login";
            $res = process_user_menu(null, null, Cms::env("AREA_SHOW_ECOMMERCE"), $user_path, $rel_layout, true, false);
            $tpl->set_var("user_menu", $res["content"]);
        }
    }

    $cm->doEvent("vg_on_processed_block_login", array(&$tpl, $anagraph));

    $buffer = $tpl->rpparse("main", false);
    return array(
		"pre" 			=> $block["tpl"]["pre"]
		, "post" 		=> $block["tpl"]["post"]
		, "content" 	=> $buffer
		, "default" 	=> $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
	);
}