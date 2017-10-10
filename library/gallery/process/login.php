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
    
    setJsRequest("ff.cms.blockLogin", "tools");
    
    //$cm->oPage->tplAddJs("ff.cms.blockLogin", "ff.cms.blockLogin.js", FF_THEME_DIR .  "/gallery/javascript/tools");

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

    $UserID = get_session("UserID");
    $UserNID = get_session("UserNID");
    $UserEmail = get_session("UserEmail");

	$template_name = ($layout["template"] ? $layout["template"]. "_" : "");
    if ($UserID == MOD_SEC_GUEST_USER_NAME) {
        //if (!$cm->isXHR() && ($user_path == "/login" || strpos($user_path , "/login/") !== false)) //non processa il block_login se siamo nella pagina di login
         //   return array("content" => "");

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
         
        $user_permission = get_session("user_permission");
        $template_prefix = ffCommon_url_rewrite($user_permission["primary_gid_name"]);
    }
	
	$tpl_data["prefix"] = $template_prefix;
	$tpl_data["custom"] = $base_template_name . ".html";
    $tpl_data["base"] = $template_name . $base_template_name . ".html";
    $tpl_data["path"] = $layout["tpl_path"];
    
    $tpl_data["result"] = get_template_cascading($user_path, $tpl_data);

    $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
    $tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   


/*
 	$check_vgallery_path = false;
    $vgallery_user_path = get_session("vgallery_user_path");
    if (is_array($vgallery_user_path) && strlen($user_path))
        $check_vgallery_path = array_search($user_path, $vgallery_user_path);
        
        
    if (
        strpos($user_path, USER_RESTRICTED_PATH) === false && strpos($user_path, VG_SITE_ECOMMERCE) === false && strpos($user_path, VG_SITE_CART) === false && $check_register && strpos($user_path, VG_SITE_MOD_SEC_ACTIVATION) === false && strpos($user_path, VG_SITE_MOD_SEC_RECOVER) === false && strpos($user_path, VG_SITE_VGALLERY) === false && $check_vgallery_path === false
    ) {
        $user_ret_url = $ret_url;
    } else {
        $user_ret_url = urldecode($ret_url);
        do {
            $arr_ret_url = @parse_url($user_ret_url);
            if ($arr_ret_url === false) {
                $user_ret_url = FF_SITE_PATH . "/";
            } else {
                parse_str($arr_ret_url["query"], $arg_ret_url);
                $user_ret_url = $arg_ret_url["ret_url"];
                if (!strlen($user_ret_url))
                    $user_ret_url = FF_SITE_PATH . "/";
            }
            $check_vgallery_path = false;
            if (is_array($vgallery_user_path) && strlen($user_ret_url))
                $check_vgallery_path = array_search($user_ret_url, $vgallery_user_path);

            if (strlen($layout_settings["AREA_LOGIN_REGISTER"])) {
                if (strpos($user_path, $layout_settings["AREA_LOGIN_REGISTER"]) === false) {
                    $check_register = false;
                } else {
                    $check_register = true;
                }
            } else {
                $check_register = true;
            }
        } while (
        !(strpos($user_ret_url, USER_RESTRICTED_PATH) === false && strpos($user_ret_url, VG_SITE_ECOMMERCE) === false && strpos($user_ret_url, VG_SITE_CART) === false && $check_register && strpos($user_ret_url, VG_SITE_MOD_SEC_ACTIVATION) === false && strpos($user_ret_url, VG_SITE_MOD_SEC_RECOVER) === false && strpos($user_ret_url, VG_SITE_VGALLERY) === false && $check_vgallery_path === false
        ) && $user_ret_url != "/"
        );
    }*/

    /**
     * Load Template
     */
     
     
    

    /**
     * Admin Father Bar
     */
    if (AREA_LOGIN_SHOW_MODIFY) {
        $admin_menu["admin"]["unic_name"] = $unic_id;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if (AREA_PROPERTIES_SHOW_MODIFY) {
            $admin_menu["admin"]["extra"] = "";
        }
        if (AREA_ECOMMERCE_SHOW_MODIFY) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if (AREA_LAYOUT_SHOW_MODIFY) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if (AREA_SETTINGS_SHOW_MODIFY) {
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
    if ($UserID == MOD_SEC_GUEST_USER_NAME) {
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
            $res = process_user_menu(null, null, AREA_SHOW_ECOMMERCE, $user_path, $rel_layout, true, false);

            $tmp_data_field["user-menu"] = $res["content"];
        }
        
        $tiny_lang_code = strtolower(substr(LANGUAGE_INSET, 0, 2));
        if (strlen($layout_settings["AREA_LOGIN_REGISTER"])) {
            $tmp_data_field["register"]["href"] = FF_SITE_PATH . ($cm->router->getRuleById("mod_sec_register_" . $tiny_lang_code) 
                                                        ? $cm->router->getRuleById("mod_sec_register_" . $tiny_lang_code)->reverse
                                                        :  ($cm->router->getRuleById("mod_sec_register")
                                                            ? $cm->router->getRuleById("mod_sec_register")->reverse
                                                            : (MOD_SEC_LOGIN_REGISTER_URL
                                                                ? MOD_SEC_LOGIN_REGISTER_URL
                                                                : ""
                                                            )
                                                        ) 
                                                    );        
            $tmp_data_field["register"]["class"] = "register";
            $tmp_data_field["register"]["content"] = ffTemplate::_get_word_by_code("register_title");
            $tmp_data_field["register"]["default"] = '<a href="' . $tmp_data_field["register"]["href"] . '" class="' . $tmp_data_field["register"]["class"] . '">' . $tmp_data_field["register"]["content"] . '</a>';
        }
        $tmp_data_field["activation"]["href"] = FF_SITE_PATH . ($cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code) 
                                                                    ? $cm->router->getRuleById("mod_sec_activation_" . $tiny_lang_code)->reverse
                                                                    :  $cm->router->getRuleById("mod_sec_activation")->reverse
                                                                );
        $tmp_data_field["activation"]["class"] = "activation";
        $tmp_data_field["activation"]["content"] = ffTemplate::_get_word_by_code("activation_title");
        $tmp_data_field["activation"]["default"] = '<a href="' . $tmp_data_field["activation"]["href"] . '" class="' . $tmp_data_field["activation"]["class"] . '">' . $tmp_data_field["activation"]["content"] . '</a>';

        $tmp_data_field["recover"]["href"] = FF_SITE_PATH . ($cm->router->getRuleById("mod_sec_recover_" . $tiny_lang_code) 
                                                                ? $cm->router->getRuleById("mod_sec_recover_" . $tiny_lang_code)->reverse
                                                                :  $cm->router->getRuleById("mod_sec_recover")->reverse
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
                $tpl->set_var("link_register_class", cm_getClassByDef($framework_css["block"]["register"]));
                $tpl->set_var("register_link", $tmp_data_field["register"]["href"]);
                $tpl->parse("SezRegister", false);
            }
            
            if ($layout_settings["AREA_LOGIN_SHOW_LOSTPASSWORD"]) {
                $tpl->set_var("lostpassword_class", cm_getClassByDef($framework_css["block"]["lost_password"]));
                $tpl->set_var("user_path", $tmp_data_field["recover"]["href"]);
                $tpl->parse("SezLostPassword", false);
            }

            if (MOD_SEC_SOCIAL_FACEBOOK) {
                $tpl->set_var("fb_appid", MOD_SEC_SOCIAL_FACEBOOK_APPID);

                if (file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . CM_DEFAULT_THEME . "/images/plugins/ajax-loader.gif")) {
                    $tpl->set_var("img_ajax_loader", FF_SITE_PATH . FF_THEME_DIR . "/" . CM_DEFAULT_THEME . "/images/plugins/ajax-loader.gif");
                    $tpl->set_var("logged_message", ffTemplate::_get_word_by_code("fb_wait_loading"));
                    $tpl->parse("SectAjaxLoader", false);
                }

                $tpl->parse("SezLoginExtendedFB", false);
                $tpl->set_var("SezLoginShortFB", "");

                $enable_social = true;
            }
            if (MOD_SEC_SOCIAL_JANRAIN) {
                $tpl->set_var("janrain_appname", ffCommon_url_rewrite(MOD_SEC_SOCIAL_JANRAIN_APPNAME));

                $tpl->parse("SezLoginExtendedJanrain", false);
                $tpl->set_var("SezLoginShortJanrain", "");

                $enable_social = true;
            }
            
            if (strlen($layout_settings["AREA_LOGIN_SHOW_LABEL"])) {
                $tpl->parse("SezShowLabelUsername", false);
                $tpl->parse("SezShowLabelPassword", false);
            } else {
                $tpl->set_var("SezShowLabelUsername", "");
                $tpl->set_var("SezShowLabelPassword", "");
            }
            
            $tpl->set_var("container_class", cm_getClassByDef($framework_css["block"]["container"]));
            $tpl->set_var("fields_class", cm_getClassByDef($framework_css["block"]["fields"]));
            
            
            
            
            $tpl->set_var("actions_class", cm_getClassByDef($framework_css["actions"]["def"]));
            $tpl->set_var("buttons_class", cm_getClassByDef($framework_css["actions"]["buttons"]));
            
        

        if ($enable_social) {
            $tpl->set_var("class_social", " social");
        }
    } else { //LOGOUT BOX
        $user_permission = get_session("user_permission");
        
        $arrLogout = array();
        if (check_function("get_user_avatar")) {
        	$path_url = cm_showfiles_get_abs_url("/avatar");
            $arrLogout["avatar"] = get_user_avatar($user_permission["avatar"], true, $UserEmail, $path_url, false); 
        }
        $arrLogout["username"] = $UserID;
        $user_permission = get_session("user_permission");
        $arrLogout["name"] = $user_permission["name"];
        $arrLogout["surname"] = $user_permission["surname"];
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
				$arrSize = explode("x", MOD_SEC_USER_AVATAR_MODE);
				
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

        //if(check_function("get_layout_settings"))
        //$layout["settings"] = get_layout_settings($layout["ID"], "ADMIN");

        if (check_function("process_user_menu")) {
            $rel_layout = $layout;
            $rel_layout["prefix"] = $layout["prefix"] . "M";
            $rel_layout["settings"]["AREA_USER_TEMPLATE"] = "login";
            $res = process_user_menu(null, null, AREA_SHOW_ECOMMERCE, $user_path, $rel_layout, true, false);
            $tpl->set_var("user_menu", $res["content"]);
        }
    }

    $cm->doEvent("vg_on_processed_block_login", array(&$tpl, $user_permission));

    return array("content" => $block["tpl"]["header"] . $tpl->rpparse("main", false) . $block["tpl"]["footer"]);
}