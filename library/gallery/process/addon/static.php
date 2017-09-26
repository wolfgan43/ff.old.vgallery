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
function process_addon_static($user_path, $ID_node, $vgallery_name, $static_value, $layout) {
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

	$theme = $cm->oPage->theme;
	
    check_function("set_generic_tags");
    $static_value = set_generic_tags($static_value, $user_path);
    $static_name = ffGetFilename($static_value);
    $filename_lang = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . ffCommon_dirname($static_value) . "/" . LANGUAGE_INSET . "/" . basename($static_value);
    $filename_base = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $static_value;
    if(is_file($filename_lang)) {
        if($layout_settings["AREA_STATIC_TPL_ORIGINAL"]) {
            $buffer = file_get_contents($filename_lang);
            return $buffer;
        }

        $tpl = ffTemplate::factory(ffCommon_dirname($filename_lang));
        $tpl->load_file(basename($filename_lang), "main");
    } elseif(is_file($filename_base)) {
        if($layout_settings["AREA_STATIC_TPL_ORIGINAL"]) {
            $buffer = file_get_contents($filename_base);
            return $buffer;
        }

        $tpl = ffTemplate::factory(ffCommon_dirname($filename_base));
        $tpl->load_file(basename($filename_base), "main");
    } else {
        $tpl = ffTemplate::factory(get_template_cascading($user_path, "draft.html"));
        $tpl->load_file("draft.html", "main");
        $tpl->set_var("content", "");
        $strError = ffTemplate::_get_word_by_code("static_page_nopage_file");
    }
	
    $tpl->set_var("site_path", FF_SITE_PATH);
    $tpl->set_var("theme", $theme);
    $tpl->set_var("theme_inset", $theme);
    $tpl->set_var("domain_inset", DOMAIN_INSET);
    $tpl->set_var("language_inset", LANGUAGE_INSET);
    $tpl->set_var("real_father", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', "static" . ffCommon_url_rewrite($static_name))));

	if(strlen($globals->strip_user_path)) {
		if(strlen(basename($globals->strip_user_path))) {
			if(strpos($static_name, basename($globals->strip_user_path)) === 0) {
				$static_class = substr($static_name, strlen(basename($globals->strip_user_path)));
			} else {
				$static_class = $static_name;
			}
		} else {
			if(strpos($static_name, $globals->strip_user_path) === 0) {
				$static_class = substr($static_name, strlen($globals->strip_user_path));
			} else {
				$static_class = $static_name;
			}
		}
	} else {
		$static_class = $static_name;
	}
    
    if(strlen($static_class))
        $tpl->set_var("class_father", " " . trim($static_class, "-"));

    if(check_function("set_template_var")) {
        $tpl = set_template_var($tpl);
    }

    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);
    } else {
        $tpl->set_var("SezError", "");
    }

    return $tpl->rpparse("main", false);
}
