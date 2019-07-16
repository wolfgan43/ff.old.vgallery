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
function process_static_menu_child($menu_item, $settings_path, $user_path, $search_param = "", &$layout, $level = 2) 
{
    $globals = ffGlobals::getInstance("gallery");
    //$settings_path = $globals->settings_path;
	check_function("normalize_url");
	
    $db = ffDB_Sql::factory();

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
	
	
	
    if(check_function("get_grid_system_params"))
    	$menu_params = get_grid_system_menu($layout["template"], $layout_settings["AREA_STATIC_MENU_FOLLOW_FRAMEWORK_CSS"], true);
    
	//$tpl_data["custom"] = "menu_child.html";
    $tpl_data["id"] = $unic_id;
	$tpl_data["custom"] = $layout["smart_url"] . "_child.html";		
	$tpl_data["base"] = $menu_params["tpl_name"];
	$tpl_data["path"] = $layout["tpl_path"];

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
	
	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
    $tpl->load_file($tpl_data["result"]["name"], "main");

    $tpl->set_var("site_path", FF_SITE_PATH);
    $tpl->set_var("theme_inset", THEME_INSET);

    if(check_function("set_template_var")) { 
        $tpl = set_template_var($tpl); 
    }

    $buffer = "";
    $count = 0;
     
	if($level > 2)
		$tpl->set_var("dropdown", $menu_params["class"]["dropdown_sub"]);
	else
		$tpl->set_var("dropdown", $menu_params["class"]["dropdown"]);
		
	if(is_array($menu_item) && count($menu_item)) {
		$part_item = array();

		if($settings_path == "/") 
			$compare_path = $settings_path;
		else
			$compare_path = $settings_path . "/";
		
		foreach($menu_item AS $full_path => $item) {
			if(strpos($full_path, $settings_path . "/") !== false && substr_count($full_path, "/") == substr_count($compare_path, "/") && $full_path != "/") {
				$part_item[$full_path] = $item;
			}
		}
	}

	if(is_array($part_item) && count($part_item)) {
		$count_item = 1;
       	foreach($part_item AS $full_path => $item) {
       		$child = "";

       		$item_link_class = array();
           	$item_class = array();
           	$item_properties = array();

	        $tpl->set_var("class_elem", '');
			$tpl->set_var("SezItemDescription", "");	        
           	
            set_cache_data("S", $item["ID"]);
        	//$globals->cache["data_blocks"]["S" . "" . "-" . $item["ID"]] = $item["ID"];
        	
            $count++;

            //$tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $item["parent"] . $item["name"])));

            if($search_param && !$layout_settings["AREA_STATIC_NAME_SHOW_IMAGE"])
                $tpl->set_var("item", preg_replace("/(" . preg_quote($search_param) . ")/i" , "<strong class=\"theone\">\${1}</strong>", ffCommon_specialchars($item["title"])));
            else
                $tpl->set_var("item", ffCommon_specialchars($item["title"]));
			
			if($item["ajax"])
			{
				$tpl->set_var("submenu_properties", ' event="' . $item["ajax_on_event"] . '"');
			} else 
			{
				$tpl->set_var("submenu_properties", '');
			}

			$item_permalink = normalize_url_by_current_lang(stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]);			
            if($user_path == stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]) {
                $is_here = true; 

                if($layout_settings["AREA_STATIC_FORCE_ACTUAL_LINK"]) {
                    $tpl->set_var("show_file", $item_permalink);
                } else {
                    //$tpl->set_var("show_file",  "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $item["parent"] . $item["name"]));
                    $tpl->set_var("show_file",  "javascript:void(0);");
                }
            } else {
                $is_here = false;

                $tpl->set_var("SezTarget", "");
                if(strlen($item["alt_url"])) {
                	if(strpos($item["alt_url"], "/") === 0)
                		$tpl->set_var("show_file", normalize_url_by_current_lang($item["alt_url"]));
                	else
						$tpl->set_var("show_file", $item["alt_url"]);
					if(
						substr(strtolower($item["alt_url"]), 0, 7) == "http://"
						|| substr(strtolower($item["alt_url"]), 0, 8) == "https://"
                        || substr($item["alt_url"], 0, 2) == "//"
					) {
                    	$tpl->parse("SezTarget", false);
					}
				} else {
                   	$tpl->set_var("show_file", $item_permalink);
				}
            }

            if($layout_settings["AREA_STATIC_NAME_SHOW_IMAGE"]) {
                if($is_here) {
                	$item_link_class["current"] = $menu_params["class"]["current"];
					if($item["ajax"])
						$item_link_class["ajax"] = "ajaxcontent";

					$tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');
                    $tpl->parse("SezItemImgHere", false);
                    $tpl->set_var("SezItemImgNoHere", "");
                } else {
                    if($item["smart_url"] && strpos($user_path, stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]) !== FALSE ) {
                		$item_class["current"] = $menu_params["class"]["current"];
                    }
					if($item["ajax"])
						$item_link_class["ajax"] = "ajaxcontent";

                    if(is_array($item_link_class) && count($item_link_class))
                        $tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');

                    $tpl->set_var("SezItemImgHere", "");
                    $tpl->parse("SezItemImgNoHere", false);
                }
                $tpl->parse("SezItemImg", false);
                $tpl->set_var("SezItemNoImg", "");
            } else {
                if($is_here) {
                	$item_link_class["current"] = $menu_params["class"]["current"];

					if($item["ajax"])
						$item_link_class["ajax"] = "ajaxcontent";

					$tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');
                    $tpl->parse("SezItemNoImgHere", false);
                    $tpl->set_var("SezItemNoImgNoHere", "");
                } else {
                    if($item["smart_url"] && strpos($user_path, stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]) !== FALSE ) {
                        $item_class["current"] = $menu_params["class"]["current"];
                    }
					if($item["ajax"])
						$item_link_class["ajax"] = "ajaxcontent";

                    if(is_array($item_link_class) && count($item_link_class))
                        $tpl->set_var("class_elem", ' class="' . implode(" ", $item_link_class) . '"');

                    $tpl->set_var("SezItemNoImgHere", ""); 
                    $tpl->parse("SezItemNoImgNoHere", false);
                }
                $tpl->set_var("SezItemImg", "");
                $tpl->parse("SezItemNoImg", false);
            }

			if ($layout_settings["AREA_STATIC_SHOW_DESCRIPTION"] && (strlen(trim(strip_tags($item["description"]))) || strpos($item["description"], "<img") !== false)) {
                if($search_param)
                    $tpl->set_var("description", preg_replace("/(" . preg_quote($search_param) . ")/i" , "<strong class=\"theone\">\${1}</strong>", $item["description"]));
                else
                    $tpl->set_var("description", $item["description"]);
                $tpl->parse("SezItemDescription", false);
            }

        	if($item["owner"] == Auth::get("user")->id) {
				$is_owner = true;
        	} else {
				$is_owner = false;
        	}

	        if (
                Auth::env("AREA_STATIC_SHOW_MODIFY")
	            || Auth::env("AREA_STATIC_SHOW_ADDNEW")
	            || Auth::env("AREA_STATIC_SHOW_DELETE")
	            || Auth::env("AREA_PROPERTIES_SHOW_MODIFY")
	            || Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")
	            || Auth::env("AREA_SETTINGS_SHOW_MODIFY")
	            || $is_owner
	        ) {
                $popup["admin"]["unic_name"] = $unic_id . stripslash($item["parent"]) . "/" . $item["name"] . "-" . $is_owner;

				if($is_owner && !Auth::isAdmin())
        			$popup["admin"]["title"] = ffTemplate::_get_word_by_code("static_menu_owner") . ": " . $item["title"];
				else
	                $popup["admin"]["title"] = $layout["title"] . ": " . stripslash($item["parent"]) . "/" . $item["name"];

	            $popup["admin"]["class"] = $layout["type_class"];
	            $popup["admin"]["group"] = $layout["type_group"];
	            
				if($is_owner && !Auth::isAdmin()) {
                    $popup["admin"]["addnew"] = FF_SITE_PATH . VG_SITE_MENU . "/modify?parent=" . urlencode(stripslash($item["parent"]) . "/" . $item["name"]) . "&owner=" . $item["owner"];
                	$popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_MENU . "/modify" . stripslash($item["parent"]) . "/" . $item["name"] . "?owner=" . $item["owner"];
	                $popup["admin"]["delete"] = ffDialog(TRUE,
	                                                    "yesno",
	                                                    ffTemplate::_get_word_by_code("vgallery_erase_title"),
	                                                    ffTemplate::_get_word_by_code("vgallery_erase_description"),
	                                                    "--returl--",
	                                                    FF_SITE_PATH . VG_SITE_MENU . "/modify" . stripslash($item["parent"]) . "/" . $item["name"] . "?ret_url=" . "--encodereturl--" . "&frmAction=StaticModify_confirmdelete" . "&owner=" . $item["owner"], 
	                                                    FF_SITE_PATH . VG_SITE_MENU . "/dialog");
				} else {
	                if(Auth::env("AREA_STATIC_SHOW_ADDNEW")) {
	                    $popup["admin"]["addnew"] = get_path_by_rule("pages") . "/modify?parent=" . urlencode(stripslash($item["parent"]) . "/" . $item["name"]);
	                } else {
	                    $popup["admin"]["addnew"] = "";
	                }
	                if (Auth::env("AREA_STATIC_SHOW_MODIFY")) {
                		$popup["admin"]["modify"] = get_path_by_rule("pages") . "/modify" . stripslash($item["parent"]) . "/" . $item["name"];
					}
	                if(Auth::env("AREA_STATIC_SHOW_DELETE")) {
	                    $popup["admin"]["delete"] = ffDialog(TRUE,
	                                                    "yesno",
	                                                    ffTemplate::_get_word_by_code("vgallery_erase_title"),
	                                                    ffTemplate::_get_word_by_code("vgallery_erase_description"),
	                                                    "--returl--",
	                                                    get_path_by_rule("pages") . "/modify" . stripslash($item["parent"]) . "/" . $item["name"] . "?ret_url=" . "--encodereturl--" . "&frmAction=StaticModify_confirmdelete", 
	                                                    get_path_by_rule("pages") . "/dialog");
	                }
				}
                if(Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
                    $popup["admin"]["extra"] = "";
                }
                if(Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")) {
                    $popup["admin"]["ecommerce"] = "";
                }
                if(Auth::env("AREA_SETTINGS_SHOW_MODIFY")) {
                    $popup["admin"]["setting"] = "";
                }

                $popup["sys"]["path"] = $settings_path;
                $popup["sys"]["type"] = "admin_popup";

				if(check_function("set_template_var"))
					$item_properties["admin"] = 'data-admin="' . get_admin_bar($popup, VG_SITE_FRAME) . '"';

	            //$serial_popup = json_encode($popup);
	            //$item_properties["admin"] = 'data-admin="' . FF_SITE_PATH . VG_SITE_FRAME . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"';

	            //$item_class["admin"] = "admin-bar";
            }

			if(/*!$layout_settings["AREA_STATIC_SHOW_ONLYHOME"] &&*/ array_key_exists($full_path, $menu_item))
			{
				$level++;
				$child = process_static_menu_child($menu_item, $full_path, $user_path, $search_param, $layout, $level);
                $tpl->set_var("child", $child);
	            if(strlen($child))
            		$item_class["child"] = $menu_params["class"]["has_child"];
			}

           // $item_class["default"] = $item["smart_url"];
				
			if(count($item_class))
				$item_properties["class"] = 'class="' . implode(" ", array_filter($item_class)) . '"';

			$tpl->set_var("item_properties", implode(" ", array_filter($item_properties)));
			$tpl->parse("SezItem", true);
			
			$count_item++;
        }
        if ($count)
		{
			$buffer = $tpl->rpparse("main", false);
		}
    }
	
    return $buffer;
}
