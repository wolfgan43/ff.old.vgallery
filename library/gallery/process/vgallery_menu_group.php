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
function process_vgallery_menu_group($user_path, $group_menu, $search_param = "", &$layout) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

    $db = ffDB_Sql::factory();

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];

    setJsRequest($layout_settings["AREA_STATIC_PLUGIN"]);


    if(!strlen($user_path))
        $user_path = "/";
        
    $page = basename($user_path);
    $parent = ffCommon_dirname($user_path);

    $show_home = $layout_settings["AREA_VGALLERY_GROUP_SHOW_HOME"];
    
    if(check_function("get_grid_system_params"))
    	$menu_params = get_grid_system_menu($layout["template"]);
    
	//$tpl_data["custom"] = "menu_group.html";
    $tpl_data["id"] = $unic_id;
	$tpl_data["custom"] = $layout["smart_url"] . ".html";		
	$tpl_data["base"] = $menu_params["tpl_name"];
	$tpl_data["path"] = $layout["tpl_path"];

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
	
	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	//$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
    $tpl->load_file($tpl_data["result"]["name"], "main");
	
	/**
	* Admin Father Bar
	*/
    if(Auth::env("AREA_VGALLERY_GROUP_SHOW_MODIFY")) {
        $admin_menu["admin"]["unic_name"] = $unic_id;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        $admin_menu["admin"]["addnew"] = "";
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

	/**
	* Process Block Header
	*/		    
    if(check_function("set_template_var"))
		$block = get_template_header($user_path, $admin_menu, $layout, $tpl);

    if ($layout_settings["AREA_VGALLERY_GROUP_SHOW_TITLE"]) {
        $tpl->set_var("title" , ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id)));
        $tpl->parse("SezTitle", false);
    } else
        $tpl->set_var("SezTitle", "");

    $sSQL = "SELECT 
                  " . $db->toSql($user_path) . " AS parent
                , vgallery_groups.name AS name
                , vgallery_groups.description AS description
                , vgallery_groups_menu.name AS menu_name
             FROM vgallery_groups
                INNER JOIN vgallery_groups_menu ON vgallery_groups_menu.ID = vgallery_groups.ID_menu
             WHERE
                vgallery_groups.ID_menu = " . $db->toSql($group_menu, "Number") . "
             ORDER BY `sort`, name";
    $db->query($sSQL);
    if ($db->nextRecord()) {
        if ($show_home) {
            $tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . "home")));
            $tpl->set_var("SezHomeEdit", "");        

            if(check_function("normalize_url")) {
            	$tpl->set_var("show_file", normalize_url($user_path . ($search_param ? "?search_param=" . urlencode($search_param) : ""), HIDE_EXT, true, LANGUAGE_INSET));
			}
            $tpl->set_var("home", ffTemplate::_get_word_by_code($db->getField("menu_name")->getValue() . "_home"));
            if($settings_path == $user_path) {
                $tpl->set_var("class_status", "home current");
            } else {
                $tpl->set_var("class_status", "home");
            }

            if($layout_settings["AREA_VGALLERY_GROUP_HOME_SHOW_IMAGE"]) {
                $tpl->parse("SezHomeImg", false);
                $tpl->set_var("SezHomeNoImg", "");
            } else {
                $tpl->set_var("SezHomeImg", "");
                $tpl->parse("SezHomeNoImg", false);
            }

            if($layout_settings["AREA_VGALLERY_GROUP_HOME_SHOW_PARENT"]) {
                $tpl->parse("SezHomeHeaderParent", false);
                $tpl->set_var("SezHomeHeaderNoParent", "");
                $tpl->parse("SezHomeHeader", false);
                $tpl->parse("SezHomeFooter", false);
            } else {
                $tpl->set_var("SezHomeHeaderParent", "");
                $tpl->parse("SezHomeHeaderNoParent", false);
                $tpl->parse("SezHomeHeader", false);
                $tpl->set_var("SezHomeFooter", "");
            }
        } else {
            $tpl->set_var("SezHomeHeader", "");
            $tpl->set_var("SezHomeFooter", "");
        }

        do {
            $tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $db->getField("parent")->getValue() . $db->getField("name")->getValue())));

            $tpl->set_var("item", ffCommon_specialchars($db->getField("name")->getValue()));
            if(ffCommon_url_rewrite(basename($settings_path)) == ffCommon_url_rewrite($db->getField("name")->getValue())) {      
                $is_here = true;
                             
                //$tpl->set_var("show_file",  "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $db->getField("parent")->getValue() . ffCommon_url_rewrite($db->getField("name")->getValue())));
            } else {
                $is_here = false;
            }
			if(check_function("normalize_url")) {
				$tpl->set_var("show_file", normalize_url(stripslash($db->getField("parent")->getValue()) . "/" . ffCommon_url_rewrite($db->getField("name")->getValue()) . ($search_param ? "?search_param=" . urlencode($search_param) : ""), HIDE_EXT, true, LANGUAGE_INSET));
			}
            if($layout_settings["AREA_VGALLERY_GROUP_NAME_SHOW_IMAGE"]) {
                if($is_here) {
                    $class_elem = "current";
                    $tpl->set_var("class_status", $class_elem);
					$tpl->set_var("class_elem", $class_elem);
					$tpl->parse("SezClassElem", false);
                    $tpl->parse("SezItemImgHere", false);
                    $tpl->set_var("SezItemImgNoHere", "");
                } else {
                    if(0) {
                        $tpl->set_var("class_status", "current");
                    } else {
                        $tpl->set_var("class_status", "item");
                    }
					$tpl->set_var("SezClassElem", "");
					$tpl->set_var("SezItemImgHere", "");
                    $tpl->parse("SezItemImgNoHere", false);
                }
                $tpl->parse("SezItemImg", false);
                $tpl->set_var("SezItemNoImg", "");
            } else {
                if($is_here) {
                    $class_elem = "current";
                    $tpl->set_var("class_status", $class_elem);
					$tpl->set_var("class_elem", $class_elem);
                    $tpl->parse("SezItemNoImgHere", false);
                    $tpl->set_var("SezItemNoImgNoHere", "");
                } else {
                    if(0) {
                        $tpl->set_var("class_status", "current");
                    } else {
                        $tpl->set_var("class_status", "item");
                    }
					$tpl->set_var("SezClassElem", "");
					$tpl->set_var("SezItemNoImgHere", "");
                    $tpl->parse("SezItemNoImgNoHere", false);
                }
                $tpl->set_var("SezItemImg", "");
                $tpl->parse("SezItemNoImg", false);
            }

            if ($layout_settings["AREA_VGALLERY_GROUP_SHOW_DESCRIPTION"] && (strlen(trim(strip_tags($db->getField("description")->getValue()))) || strpos($db->getField("description")->getValue(), "<img") !== false)) {
                $tpl->set_var("description", $db->getField("description")->getValue());

                $tpl->parse("SezItemDescription", false);
            } else {
                $tpl->set_var("SezItemDescription", "");
            }

            $tpl->set_var("SezItemEdit", "");

            $tpl->parse("SezItem", true);
            $tpl->set_var("SezError", "");
        } while ($db->nextRecord());
        
        if(strlen($layout_settings["AREA_VGALLERY_GROUP_PLUGIN"])) 
            $tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $layout_settings["AREA_VGALLERY_GROUP_PLUGIN"]));
        else
            $tpl->set_var("class_plugin", "vgroupmenu");
        
        $tpl->parse("SezMenu", false);
    } else {
        $tpl->set_var("SezMenu", "");
    }
    
    if(strlen($strError)) {
        $tpl->set_var("strError", $strError);
        $tpl->parse("SezError", false);        
    } else {
        $tpl->set_var("SezError", "");
    }

    
	$res["pre"] 								= $block["tpl"]["pre"];
	$res["post"] 								= $block["tpl"]["post"];
    if(is_array($menu_params["template"]) && count($menu_params["template"])) {
    	$res["template"] 						= $menu_params["template"];
    	$res["template"]["offcanvas"] 			= $tpl->rpparse("main", false);

		$res["content"] 						= $res["template"]["content"];
		$res["default"] 						= $res["template"]["content"];
    } else { 
    	$res["content"] 						= $tpl->rpparse("main", false);
		$res["default"] 						= $res["pre"] . $res["content"] . $res["post"];
    }
    
    return $res;
}
