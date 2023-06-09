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
function process_language($selected_lang, $user_path, &$layout) 
{
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;
    
    $db = ffDB_Sql::factory();
    
    check_function("get_locale");

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];

    $template_name = ($layout["template"]
    					? $layout["template"]
    					: "default"
    				) . ".html";

    $tpl_data["id"] = $unic_id;
    //$tpl_data["custom"] = "language.html";
    $tpl_data["custom"] = $layout["smart_url"] . ".html";		
    $tpl_data["base"] = $template_name;
    $tpl_data["path"] = $layout["tpl_path"];
    
    $tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
    
    $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
    $tpl->load_file($tpl_data["result"]["name"], "main");
    
    /**
    * Admin Father Bar
    */
    if(Auth::env("AREA_LANGUAGES_SHOW_MODIFY")) {
        $admin_menu["admin"]["unic_name"] = $unic_id;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if(Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
            $admin_menu["admin"]["extra"] = "";
        }
        if(Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if(Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if(Auth::env("AREA_SETTINGS_SHOW_MODIFY")) {
            $admin_menu["admin"]["setting"] = ""; //$layout["type"]; 
        }

        $admin_menu["sys"]["path"] = $user_path;
        $admin_menu["sys"]["type"] = "admin_toolbar";
    }

	if($layout_settings["AREA_LANGUAGE_ACTUAL_SHOW_IMAGE"] || $layout_settings["AREA_LANGUAGE_LIST_SHOW_IMAGE"]) {
		if($layout_settings["AREA_LANGUAGE_USE_ICON_16X16"]) {
			$flag_dim = "16";
			/*if(is_file(FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/css/lang-flags16.css")) {
				$layout["class"]["flag"] = "f16";
				$cm->oPage->tplAddCss("langFlag", "lang-flags16.css", FF_THEME_DIR . "/" . FRONTEND_THEME . "/css");
			} elseif(is_file(FF_DISK_PATH . "/themes/" . cm_getMainTheme() . "/css/lang-flags16.css")) {
				$layout["class"]["flag"] = "f16";
				$cm->oPage->tplAddCss("langFlag", "lang-flags16.css", "/modules/restricted" . FF_THEME_DIR . "/" . cm_getMainTheme() . "/css");
			}*/
		} 
		else
		{
			$flag_dim = "32";

/*			if(is_file(FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/css/lang-flag32.css")) {
				$layout["class"]["flag"] = "f32";
				$cm->oPage->tplAddCss("langFlag", "lang-flags32.css", FF_THEME_DIR . "/" . FRONTEND_THEME . "/css");
			} elseif(is_file(FF_DISK_PATH . "/themes/" . cm_getMainTheme() . "/css/lang-flags32.css")) {
				$layout["class"]["flag"] = "f32";
				$cm->oPage->tplAddCss("langFlag", "lang-flags32.css", FF_THEME_DIR . "/" . cm_getMainTheme() . "/css");
			}*/
		}
		
		$layout["class"]["flag"] = "f" . $flag_dim;
        $filename = cm_cascadeFindTemplate("/css/lang-flags" . $flag_dim . ".css", "security");
		//$filename = cm_moduleCascadeFindTemplateByPath("restricted", "/css/lang-flags" . $flag_dim . ".css", $cm->oPage->theme);
		$ret = cm_moduleGetCascadeAttrs($filename);
		$cm->oPage->tplAddCSS("lang-flags" . $flag_dim, array(
			"file" => "lang-flags" . $flag_dim . ".css"
			, "path" => $ret["path"]
		));	
	}
    
	/**
	* Process Block Header
	*/		    
    if(check_function("set_template_var"))
		$block = get_template_header($user_path, $admin_menu, $layout, $tpl);

	if ($layout_settings["AREA_LANGUAGE_SHOW_TITLE"]) 
		$tpl->parse("SezTitle", false);
   
   	$arrLang = get_locale("lang", true);
   	if(is_array($arrLang) && count($arrLang)) 
   	{
   		check_function("normalize_url");
        $request = Cms::requestCapture();
  		if(is_array($request["valid"]) && count($request["valid"]))
			$query_string = "?" . implode("&", $request["valid"]);
   		
   		foreach($arrLang AS $lang_code => $lang) {
   			$menu_item_properties = "";
        	if($lang_code == LANGUAGE_INSET) {
        		$menu_target = "ACTUAL";
        		$menu_item_properties = ' class="' . Cms::getInstance("frameworkcss")->get("current", "util") . '"';
        		$tpl->set_var("item_url", "javascript:void(0);"); 
        		$tpl->set_var("item_lang", 'rel="nofollow"');
			} else {
				$menu_target = "LIST";
				$tpl->set_var("item_url", normalize_url($globals->settings_path, HIDE_EXT, true, $lang_code) . $query_string); 
				$tpl->set_var("item_lang", ' lang="' . $lang["tiny_code"] . '" rel="nofollow"');
			}
			$tpl->set_var("menu_item_properties", $menu_item_properties);

			$item_class = $lang["tiny_code"];
        	if($layout_settings["AREA_LANGUAGE_" . $menu_target . "_SHOW_IMAGE"]) {
        		$tpl->set_var("item_class",  "flag " . $item_class);
        		$tpl->set_var("item_description", "");
			} else {
				$tpl->set_var("item_class",  $item_class);
				$tpl->set_var("item_description", ucfirst($layout_settings["AREA_LANGUAGE_" . $menu_target . "_SHOW_TINYCODE"]
					? $item_class
					: ffTemplate::_get_word_by_code($lang["description"])
				));
			}
			
	        $tpl->parse("SezLang", true);
   		}
		
		$menu_class["default"] = Cms::getInstance("frameworkcss")->get("topbar", "bar"); 
		$menu_class["plugin"] = $layout_settings["AREA_LANGUAGE_PLUGIN"];
		
		$tpl->set_var("menu_class", implode(" " , $menu_class));
		  
		$tpl->parse("SezLanguages", false);
    } else {
        $tpl->set_var("strError", ffTemplate::_get_word_by_code("languages_no_item"));
        $tpl->parse("SezError", false);
    }   	
   	/*
    $sSQL = "SELECT " . FF_PREFIX . "languages.code
				, " . FF_PREFIX . "languages.tiny_code
				, " . FF_PREFIX . "languages.description
        	FROM " . FF_PREFIX . "languages 
        	WHERE " . FF_PREFIX . "languages.status > 0
        	ORDER BY " . ($layout_settings["AREA_LANGUAGE_SHOW_ACTUAL"]
        			? "IF(code = " . $db->toSql(LANGUAGE_INSET) . "
        				, 0
        				, 1
        			), "
        			: ""
        		) . "
        		IF(code = " . $db->toSql(LANGUAGE_DEFAULT) . "
        			, 0
        			, 1
        		)
        		, description";
    $db->query($sSQL);
    if ($db->nextRecord()) {
		if($db->numRows() > 1) {
		    check_function("normalize_url");
			$url_source = $globals->settings_path;
		    $request = Cms::requestCapture();
            if(is_array($request["valid"]) && count($request["valid"]))
                $query_string = "?" . implode("&", $request["valid"]);
		}

        do {
        	$menu_item_properties = "";
        	$lang_code = $db->getField("code", "Text", true);
        	if($lang_code == LANGUAGE_INSET) {
        		$menu_target = "ACTUAL";
        		$menu_item_properties = ' class="' . Cms::getInstance("frameworkcss")->get("current", "util") . '"';
        		$tpl->set_var("item_url", "javascript:void(0);"); 
			} else {
				$menu_target = "LIST";
				$tpl->set_var("item_url", normalize_url($url_source, HIDE_EXT, true, $lang_code) . $query_string); 
			}
			$tpl->set_var("menu_item_properties", $menu_item_properties);

			$item_class = $db->getField("tiny_code", "Text", true);
        	if($layout_settings["AREA_LANGUAGE_" . $menu_target . "_SHOW_IMAGE"]) {
        		$tpl->set_var("item_class",  "flag " . $item_class);
        		$tpl->set_var("item_description", "");
			} else {
				$tpl->set_var("item_class",  $item_class);
				$tpl->set_var("item_description", ucfirst($layout_settings["AREA_LANGUAGE_" . $menu_target . "_SHOW_TINYCODE"]
					? $item_class
					: $db->getField("description", "Text", true)
				));
			}
			
	        $tpl->parse("SezLang", true);
		} while($db->nextRecord());

		$menu_class["default"] = Cms::getInstance("frameworkcss")->get("navbar", "bar");
		$menu_class["plugin"] = $layout_settings["AREA_LANGUAGE_PLUGIN"];
		
		$tpl->set_var("menu_class", implode(" " , $menu_class));
		
		$tpl->parse("SezLanguages", false);
    } else {
        $tpl->set_var("strError", ffTemplate::_get_word_by_code("languages_no_item"));
        $tpl->parse("SezError", false);
    }
  */ 
    
	$buffer = $tpl->rpparse("main", false);
    return array(
		"pre" 			=> $block["tpl"]["pre"]
		, "post" 		=> $block["tpl"]["post"]
		, "content" 	=> $buffer
		, "default" 	=> $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
	);	
}