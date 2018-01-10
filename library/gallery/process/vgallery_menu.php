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
function process_vgallery_menu($user_path, $vgallery_name, $source_user_path = NULL, &$layout) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

	check_function("normalize_url");

    $vgallery_name = ffCommon_url_rewrite($vgallery_name);
    
    $db = ffDB_Sql::factory();
    $db_desc = ffDB_Sql::factory();
    
    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];

    setJsRequest($layout_settings["AREA_VGALLERY_PLUGIN"]);

    if($source_user_path === NULL)
        $source_user_path = "";
   
    $show_home = $layout_settings["AREA_VGALLERY_SHOW_HOME"];

    $sSQL = "SELECT vgallery.* 
            FROM vgallery 
            WHERE vgallery.name = " . $db->toSql($vgallery_name);
    $db->query($sSQL);
    if ($db->nextRecord()) {
        $limit_level = $db->getField("limit_level")->getValue(); 
        $enable_ecommerce = $db->getField("enable_ecommerce", "Number", true);
        $enable_multilang_visible = $db->getField("enable_multilang_visible", "Number", true);
        $use_pricelist_as_item_thumb = $db->getField("use_pricelist_as_item_thumb", "Number", true);
    }

    if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
    	$father_permission = get_file_permission("/" . $vgallery_name . stripslash($user_path), "vgallery_nodes");
    if (!check_mod($father_permission, 1, ($enable_multilang_visible ? true : LANGUAGE_DEFAULT))) {  
        return array("content" => "");
    }
    
    if((count(explode("/", $user_path)))  <= $limit_level) {
        $allow_insert_dir = true;
    } else {
        $allow_insert_dir = false;
    } 
           
	$params_menu = "menu";
	if($layout_settings["AREA_VGALLERY_MENU_FOLLOW_FRAMEWORK_CSS"])
	{
		$follow_framework = true;
		if($layout_settings["AREA_VGALLERY_MENU_VERTICAL"])
		{
			$params_menu = "menu-side";
		} elseif($layout_settings["AREA_VGALLERY_MENU_OFFCANVAS"])
		{
			$params_menu = "menu-side-offcanvas";
                        
			$template["container"]["class"] = "marketing off-canvas-wrap";
			$template["container"]["wrap"] = true;
			$template["container"]["wrap_class"] = "inner-wrap";
			$template["container"]["properties"] = "data-offcanvas";
                        
		}
	}    
	if(check_function("get_grid_system_params"))
		$menu_params = get_grid_system_menu($params_menu, $follow_framework);


	//$tpl_data["custom"] = $vgallery_name . "_menu.html";
	$tpl_data["custom"] = $layout["smart_url"] . ".html";		
	$tpl_data["base"] = $menu_params["tpl_name"];
	$tpl_data["path"] = $layout["tpl_path"];

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);

	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");

//    $tpl->set_var("skin", (strlen($layout_settings["AREA_VGALLERY_SHOW_THEME"]) ? " " . $layout_settings["AREA_VGALLERY_SHOW_THEME"] : ""));

	/**
	* Admin Father Bar
	*/
    if(check_mod($father_permission, 2) && (AREA_VGALLERY_DIR_SHOW_ADDNEW || AREA_PROPERTIES_SHOW_MODIFY || AREA_ECOMMERCE_SHOW_MODIFY || AREA_LAYOUT_SHOW_MODIFY || AREA_SETTINGS_SHOW_MODIFY)) {
        $admin_menu["admin"]["unic_name"] = $unic_id . $user_path;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        if(AREA_VGALLERY_DIR_SHOW_ADDNEW && $allow_insert_dir) {
            $admin_menu["admin"]["adddir"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/modify?type=dir" . "&vname=" . $vgallery_name . "&path=" . urlencode("/" . $vgallery_name . stripslash($user_path)); 
        } else {
            $admin_menu["admin"]["adddir"];
        }

        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if(AREA_PROPERTIES_SHOW_MODIFY) {
            $admin_menu["admin"]["extra"] = "";
        }
        if(AREA_ECOMMERCE_SHOW_MODIFY && $enable_ecommerce) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if(AREA_LAYOUT_SHOW_MODIFY) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if(AREA_SETTINGS_SHOW_MODIFY) {
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

    if ($layout_settings["AREA_VGALLERY_SHOW_TITLE"]) {
        $tpl->set_var("title" , ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id)));
        $tpl->parse("SezTitle", false);
    }
   
    $sSQL = "SELECT DISTINCT 
        		vgallery_nodes.ID AS ID
        		, vgallery_nodes.ID_type AS ID_type
        		, vgallery_nodes.parent AS parent
        		, vgallery_nodes.name AS name
        		, vgallery_nodes.is_dir AS is_dir
        		, vgallery_nodes.visible AS visible
        		, vgallery_nodes.use_ajax AS use_ajax
        		, vgallery_nodes.ajax_on_event AS ajax_on_event
                , vgallery.ID AS ID_vgallery
                , vgallery_type.name AS type
                " . (OLD_VGALLERY
                    ? "
                        , (SELECT GROUP_CONCAT(CONCAT(vgallery_fields.name, ':::', vgallery_rel_nodes_fields.description) ORDER BY vgallery_fields.name SEPARATOR '|@|')
                                FROM  vgallery_rel_nodes_fields
                                    INNER JOIN  vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
                                    AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                    AND  vgallery_fields.name IN('alt_url', 'meta_title', 'smart_url', 'permalink_parent')
                                ORDER BY vgallery_fields.name
                        )                                                       AS `meta` 
                    "
                    : (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
                        ? "
                            , vgallery_nodes.permalink                          AS permalink
                            , vgallery_nodes.keywords                           AS keywords
                            , vgallery_nodes.meta_description                   AS meta_description
                            , vgallery_nodes.meta_title                         AS meta_title
                            , vgallery_nodes.meta_title_alt                     AS meta_title_alt
                            , vgallery_nodes.`parent`                           AS permalink_parent
                            , vgallery_nodes.name                               AS smart_url
                            , vgallery_nodes.alt_url                            AS alt_url
                        "
                        : "
                            , vgallery_nodes_rel_languages.permalink            AS permalink
                            , vgallery_nodes_rel_languages.keywords             AS keywords
                            , vgallery_nodes_rel_languages.meta_description     AS meta_description
                            , vgallery_nodes_rel_languages.meta_title           AS meta_title
                            , vgallery_nodes_rel_languages.meta_title_alt       AS meta_title_alt
                            , vgallery_nodes_rel_languages.permalink_parent     AS permalink_parent
                            , vgallery_nodes_rel_languages.smart_url            AS smart_url
                            , vgallery_nodes_rel_languages.alt_url              AS alt_url
                        "
                    )
                ) . "
                , (" . ($layout_settings["AREA_VGALLERY_SHOW_COVER"]  //da togliere extras e far popolare la cover con l'id del field
                    	? "(
							SELECT GROUP_CONCAT(DISTINCT vgallery_rel_nodes_fields.description ORDER BY vgallery_fields.`order_backoffice` SEPARATOR ' ') AS name
							FROM vgallery_rel_nodes_fields
		                        INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
							WHERE 
								vgallery_rel_nodes_fields.ID_fields IN ( SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.settings_type = 'extras' )
							    AND vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
								AND vgallery_rel_nodes_fields.ID_lang = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "                    		
                    	)"
                    	: "''"
                    ) . "
                ) AS cover
                , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
			    , " . ($layout_settings["AREA_VGALLERY_NAME_SHOW_DESCRIPTION"] 
		        	? " (
					        SELECT GROUP_CONCAT(DISTINCT vgallery_rel_nodes_fields.description ORDER BY vgallery_fields.enable_in_menu, vgallery_fields.`order_backoffice` SEPARATOR ' ') AS name
					        FROM vgallery_rel_nodes_fields
					            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
					        WHERE 
					            vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID 
					            AND vgallery_rel_nodes_fields.ID_fields IN (SELECT vgallery_fields.ID FROM vgallery_fields WHERE vgallery_fields.enable_in_menu > 0 )
					            AND vgallery_rel_nodes_fields.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
					    ) "
					: " ''"
				) . " AS description
				, " . ($layout_settings["AREA_VGALLERY_FORCE_EMPTY_LINK"]
					? "''"
					: "(SELECT COUNT(vgallery_child.ID) 
						FROM vgallery_nodes AS vgallery_child 
						WHERE vgallery_child.visible > 0 
							AND vgallery_child.is_dir = 0 
							AND (vgallery_child.parent LIKE CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name, '%'))
								OR vgallery_child.ID IN(vgallery_nodes.cats)
						)"
				) . " AS count_child
	        FROM vgallery_nodes
                " . (OLD_VGALLERY
                    ? "LEFT JOIN vgallery_rel_nodes_fields
		                ON (
		                    vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
		                    AND vgallery_rel_nodes_fields.ID_fields = ( SELECT sort_default FROM vgallery_type WHERE vgallery_type.ID = vgallery_nodes.ID_type)
		                    AND vgallery_rel_nodes_fields.ID_lang = " . $db->tosql(LANGUAGE_INSET_ID, "Number") . "
		                ) "
                    : (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
                        ? ""
                        : " INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
                                AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
                    )
                ) . "                
	            INNER JOIN vgallery_type ON vgallery_nodes.ID_type = vgallery_type.ID
	            INNER JOIN vgallery ON vgallery_nodes.ID_vgallery = vgallery.ID
	        WHERE 
	            (vgallery_nodes.parent = " . $db->toSql("/" . $vgallery_name . stripslash($user_path)) . " OR vgallery_nodes.parent LIKE '" . $db->toSql("/" . $vgallery_name . stripslash($user_path), "Text", false) . "/%')
	            AND vgallery.name = " . $db->toSql($vgallery_name, "Text") . "
                AND vgallery_nodes.name <> ''
                AND vgallery_nodes.is_dir > 0
				" . (ENABLE_STD_PERMISSION 
				    ? ""
				    : (LANGUAGE_INSET_ID != LANGUAGE_DEFAULT_ID && ENABLE_ADV_PERMISSION && !OLD_VGALLERY
				        ? " AND vgallery_nodes_rel_languages.visible > 0 " 
				        : " AND vgallery_nodes.visible > 0 "
				    )
				) . " 
                ORDER BY (LENGTH(full_path) - LENGTH(REPLACE(full_path, '/', '')))
                    , vgallery_nodes.`order`
                    , vgallery_nodes.name ";
    $db->query($sSQL);
	if ($db->nextRecord()) {
		$menu_item = array();
		do {
			$fullpath = $db->getField("full_path", "Text", true);

            if(OLD_VGALLERY) {
			    $meta = $db->getField("meta", "Text", true);			
			    if(strlen($meta)) {
 				    $arrMeta = explode("|@|", $meta);
				    if(is_array($arrMeta) && count($arrMeta)) {
					    foreach($arrMeta AS $arrMeta_value) {
						    $tmpMeta = explode(":::", $arrMeta_value);
						    
						    switch($tmpMeta[0]) {
							    case "smart_url":
								    $menu_item[$fullpath]["smart_url"] = $tmpMeta[1];
								    break;
							    case "meta_title":
								    $menu_item[$fullpath]["title"] = $tmpMeta[1];
								    break;
							    case "permalink_parent":
								    $menu_item[$fullpath]["permalink_parent"] = $tmpMeta[1];
								    break;
							    case "alt_url":
								    $menu_item[$fullpath]["alt_url"] = $tmpMeta[1];
								    break;
						    }
					    }
				    }
			    }			
            } else {
                $menu_item[$fullpath]["smart_url"]              = $db->getField("smart_url", "Text", true);  
                $menu_item[$fullpath]["title"]                  = $db->getField("meta_title", "Text", true);  
                $menu_item[$fullpath]["permalink_parent"]       = $db->getField("permalink_parent", "Text", true);  
                $menu_item[$fullpath]["alt_url"]                = $db->getField("alt_url", "Text", true);  
            }
			$menu_item[$fullpath]["ID"] = $db->getField("ID", "Number", true);
			$menu_item[$fullpath]["ID_vgallery"] = $db->getField("ID_vgallery", "Number", true);
			$menu_item[$fullpath]["ID_type"] = $db->getField("ID_type", "Number", true);
			$menu_item[$fullpath]["parent"] = $db->getField("parent", "Text", true);
			$menu_item[$fullpath]["name"] = $db->getField("name", "Text", true);
			$menu_item[$fullpath]["type"] = $db->getField("type", "Text", true);
			$menu_item[$fullpath]["is_dir"] = $db->getField("is_dir", "Number", true);
			$menu_item[$fullpath]["cover"] = $db->getField("cover", "Text", true);
			$menu_item[$fullpath]["description"] = $db->getField("description", "Text", true);
            $menu_item[$fullpath]["visible"] = $db->getField("visible", "Number", true);
            $menu_item[$fullpath]["count"] = $db->getField("count_child", "Number", true);
			if($layout_settings["AREA_VGALLERY_SHOW_AJAX"])
				$menu_item[$fullpath]["ajax"] = true;
			else
				$menu_item[$fullpath]["ajax"] = $db->getField("use_ajax", "Number", true);

			$menu_item[$fullpath]["ajax_on_event"] = $db->getField("ajax_on_event", "Text", true);
			
			if(!$menu_item[$fullpath]["permalink_parent"])
				$menu_item[$fullpath]["permalink_parent"] = $menu_item[$fullpath]["parent"];
			if(!$menu_item[$fullpath]["smart_url"])
				$menu_item[$fullpath]["smart_url"] = $menu_item[$fullpath]["name"];
			
			$menu_key[] = $menu_item[$fullpath]["ID"];
		} while($db->nextRecord());
	}

	if(is_array($menu_item) && count($menu_item)) {
		$home_path = "/" . $vgallery_name . stripslash($user_path);
		$home_id = $menu_item[$home_path]["ID"];
		
		$part_item = array();
		
		if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
			get_file_permission(null, "vgallery_nodes", $menu_key);
		
		foreach($menu_item AS $full_path => $item) {
			if(ENABLE_STD_PERMISSION) {
                if(check_function("get_file_permission"))
				    $file_permission = get_file_permission($full_path, "vgallery_nodes");
	            if (!check_mod($file_permission, 1, ($enable_multilang_visible ? true : LANGUAGE_DEFAULT), AREA_VGALLERY_SHOW_MODIFY)) {  
	                unset($menu_item[$full_path]);

	                continue;
                }
            }
			
            if(!$layout_settings["AREA_VGALLERY_FORCE_EMPTY_LINK"] && ffCommon_dirname($full_path) != $home_path) {
				$tmp_path = ffCommon_dirname($full_path);
				do {
					if(!isset($menu_item[$tmp_path]))
						break;
				
					if($menu_item[$full_path]["is_dir"]) {
                        $menu_item[$tmp_path]["count_dir"]++;
                    } else {
                        $menu_item[$tmp_path]["count"]++;
                    }

					$tmp_path = ffCommon_dirname($tmp_path);
				} while($tmp_path != "/");
			}

		}

		foreach($menu_item AS $full_path => $item) {
			if(substr_count($full_path, "/") == substr_count($home_path . "/", "/")) {
				$part_item[$full_path] = $item;
			}
		}		
	}

	/**
	* Process Block Header
	*/	
    if(check_function("set_template_var")) {
    	$block["class"]["nav"] = $menu_params["class"]["nav"];
		if($layout_settings["AREA_STATIC_MENU_STICK"]) {
			$block["class"]["sticky"] = $menu_params["class"]["sticky"];
		}

		if($follow_framework && $layout_settings["AREA_STATIC_MENU_OFFCANVAS"]) {
			$block["class"]["offcanvas"] = $menu_params["class"]["side"];
		}
    
        $tpl = set_template_var($tpl); 
        if(!$layout_settings["AREA_STATIC_MENU_OFFCANVAS"])
            $block = get_template_header($settings_path, $admin_menu, $layout, $tpl, $block);
    }    	
	
    if(is_array($part_item) && count($part_item)) {
    	$item_class = array();
    	$item_properties = array();    
        if($show_home) {
            //$tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . "home")));

            if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
				$file_permission = get_file_permission($home_path, "vgallery_nodes");
            if(check_mod($file_permission, 2) && (AREA_VGALLERY_DIR_SHOW_ADDNEW || AREA_VGALLERY_DIR_SHOW_MODIFY || AREA_VGALLERY_DIR_SHOW_DELETE || AREA_PROPERTIES_SHOW_MODIFY || AREA_ECOMMERCE_SHOW_MODIFY || AREA_SETTINGS_SHOW_MODIFY)) {
                $popup["admin"]["unic_name"] = $unic_id . $home_path . " home";
                $popup["admin"]["title"] = $layout["title"] . ": " . $home_path;
				$popup["admin"]["class"] = $layout["type_class"];
				$popup["admin"]["group"] = $layout["type_group"];
				
                if((count(explode("/", $user_path))  <= $limit_level)) {
                    $allow_insert_dir = true;
                } else {
                    $allow_insert_dir = false;
                }        
                
                if(AREA_VGALLERY_DIR_SHOW_ADDNEW && $allow_insert_dir) {
                    $popup["admin"]["adddir"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/modify?type=dir" . "&vname=" . $vgallery_name . "&path=" . urlencode("/" . $vgallery_name . stripslash($user_path)) . "&extype=vgallery_nodes"; 
                } else {
                    $popup["admin"]["adddir"] = "";
                }

                if(strtolower($user_path) != "/" . strtolower($vgallery_name)) {
                    if(AREA_VGALLERY_DIR_SHOW_MODIFY) {
                        $popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/modify?keys[ID]=" . $home_id . "&type=dir" . "&vname=" . $vgallery_name . "&path=" . urlencode("/" . $vgallery_name . stripslash(ffCommon_dirname($user_path))) . "&extype=vgallery_nodes"; 
                    }
                    if(AREA_VGALLERY_DIR_SHOW_DELETE) {
                        $popup["admin"]["delete"] = ffDialog(TRUE,
                                                        "yesno",
                                                        ffTemplate::_get_word_by_code("vgallery_erase_title"),
                                                        ffTemplate::_get_word_by_code("vgallery_erase_description"),
                                                        "--returl--",
                                                        FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/modify?keys[ID]=" . $home_id . "&type=dir" . "&vname=" . $vgallery_name . "&path=" . urlencode("/" . $vgallery_name . stripslash(ffCommon_dirname($user_path))) . "&extype=vgallery_nodes&ret_url=" . "--encodereturl--" . "&VGalleryNodesModify_frmAction=confirmdelete",
                                                        FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/dialog");
                    }
                } else {
                    $popup["admin"]["modify"] = "";
                    $popup["admin"]["delete"] = "";
                }
                if(AREA_PROPERTIES_SHOW_MODIFY) {
                    $popup["admin"]["extra"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/properties?keys[ID]=" . $home_id . "&type=dir" . "&vname=" . $vgallery_name . "&path=" . urlencode("/" . $vgallery_name . stripslash(ffCommon_dirname($user_path))) . "&extype=vgallery_nodes" . "&layout=" . $layout["ID"];
                }
                if(AREA_ECOMMERCE_SHOW_MODIFY && $enable_ecommerce) {
                    $popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_WS_ECOMMERCE . "/vgallery/" . ffCommon_url_rewrite($vgallery_name) . "/ecommerce/all?keys[ID]=" . $home_id . "&type=dir" . "&vname=" . $vgallery_name . "&path=" . urlencode("/" . $vgallery_name . stripslash(ffCommon_dirname($user_path))) . "&extype=vgallery_nodes";
                }
                if(AREA_SETTINGS_SHOW_MODIFY) {
                    $popup["admin"]["setting"] = "";
                }
                
                $popup["sys"]["path"] = $user_path;
                $popup["sys"]["type"] = "admin_popup";

				if(check_function("set_template_var"))
					$item_properties["admin"] = 'data-admin="' . get_admin_bar($popup, VG_SITE_FRAME . $vg_father["source_user_path"]) . '"';
                
//	            $serial_popup = json_encode($popup);
//	            $item_properties["admin"] = 'data-admin="' . FF_SITE_PATH . VG_SITE_FRAME . $vg_father["source_user_path"] . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"';

	            $item_class["admin"] = "admin-bar";
            }

            $tpl->set_var("home", ffCommon_specialchars("home"));
            if($settings_path == $home_path) { 
                $item_class["current"] = $menu_params["class"]["current"];
			}

	        if($source_user_path) {
        		$tpl->set_var("show_file", normalize_url_by_current_lang($item["permalink_parent"]));
			} else {
				$tpl->set_var("show_file", "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . "home"));
			}
            
            if($layout_settings["AREA_VGALLERY_HOME_SHOW_IMAGE"]) {
                $tpl->parse("SezHomeImg", false);
                $tpl->set_var("SezHomeNoImg", "");
            } else {
                $tpl->set_var("SezHomeImg", "");
                $tpl->parse("SezHomeNoImg", false);
            }

            $item_class["default"] = "home";

			if(count($item_class))
				$item_properties["class"] = 'class="' . implode(" ", array_filter($item_class)) . '"';
				
			$tpl->set_var("item_properties", implode(" ", array_filter($item_properties)));
			
            if($layout_settings["AREA_VGALLERY_HOME_SHOW_PARENT"]) {
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

		$count_item = 1;
       	foreach($part_item AS $full_path => $item) {
			$child = "";
			$item_class = array();
			$item_properties = array();
			
       	   	if(!(isset($item["name"]) && isset($item["parent"]) && isset($item["ID_type"])))
       	   		continue;
            
            if($layout_settings["AREA_VGALLERY_SHOW_EMPTY"]) {
                if(!$layout_settings["AREA_VGALLERY_SHOW_EMPTY_DIR"] && $item["count"] <= 0) {
                    if($item["count_dir"] <= 0) {
                        continue;
                    }
                }
            } else {
                if($layout_settings["AREA_VGALLERY_SHOW_EMPTY_DIR"]) {
                    if(!$item["is_dir"] && $item["count"] + $item["count_dir"] <= 0) {
                        continue;
                    }
                } else {
                    if($item["count"] <= 0) {
                        continue;
                    }
                }
            }            
/*            if(!$layout_settings["AREA_VGALLERY_SHOW_EMPTY"]) {
                if($item["count"] + ($layout_settings["AREA_VGALLERY_SHOW_EMPTY_DIR"] ? $item["count_dir"] : 0) <= 0) {
                    continue;
                }
            } else {
                if(!$layout_settings["AREA_VGALLERY_SHOW_EMPTY_DIR"] && $item["count_dir"] <= 0) {
                    continue;
                }
            }      */

//            if(!$layout_settings["AREA_VGALLERY_SHOW_FULLTREE"] && (!isset($item["count"]) || $item["count"] <= 0)) { 
//                    continue;

            
            if(!$layout_settings["AREA_VGALLERY_SHOW_FULLTREE_ITEM"] && !$item["is_dir"]) {
				continue;
			}
			
            set_cache_data("V", $item["ID"], $item["ID_vgallery"]);
            //$globals->cache["data_blocks"]["V" . $item["ID_vgallery"] . "-" . $item["ID"]] = $item["ID"];
            
            //$tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $item["parent"] . $item["name"])));
            
            $image_cover = "";
			if($layout_settings["AREA_VGALLERY_SHOW_COVER"]) {
				if(is_file(DISK_UPDIR . $item["cover"])) {
					$image_cover = '<img src="' .  FF_SITE_PATH . constant("CM_SHOWFILES") . (strlen($layout_settings["AREA_VGALLERY_SHOW_COVER_MODE"]) ? "/" . $layout_settings["AREA_VGALLERY_SHOW_COVER_MODE"] : "") . $item["cover"] . '" />';
				}
			}            
			
			if(strlen($image_cover)) {
				$tpl->set_var("item", $image_cover . "<span class=\"title\">" . ffCommon_specialchars($item["title"]) . ($layout_settings["AREA_VGALLERY_SHOW_COUNT"] ? " (" . (int) $item["count"] . ")" : "") . "</span>");
			} else {
				$tpl->set_var("item", $image_cover . ffCommon_specialchars($item["title"]) . ($layout_settings["AREA_VGALLERY_SHOW_COUNT"] ? " (" . (int) $item["count"] . ")" : ""));            
			}
			
			if(0 && $layout_settings["AREA_VGALLERY_SHOW_AJAX"] && $item["is_dir"])  {
	            $frame["sys"]["type"] = "VGALLERY_MENU_CHILD";
	            $frame["sys"]["source_user_path"] = $source_user_path . stripslash($user_path);
	            $frame["sys"]["real_user_path"] = stripslash($item["parent"]) . "/" . $item["name"];
	            $frame["sys"]["vgallery"] = $vgallery_name;
	            $frame["sys"]["layout"] = $layout;
	            $frame["sys"]["settings_path"] = $settings_path;
	            $serial_frame = json_encode($frame);

	            $tpl->set_var("ajax_child", FF_SITE_PATH . VG_SITE_FRAME . stripslash($item["parent"]) . "/" . $item["name"] . "?sid=" . set_sid($serial_frame));
	            $tpl->parse("SezAjaxChild", false);
			} else {
				$tpl->set_var("SezAjaxChild", "");
			}
			
			if($item["ajax"]) 
			{
				$tpl->set_var("menu_properties", ' event="' . $item["ajax_on_event"] . '"');
			} else 
			{
				$tpl->set_var("menu_properties", '');
			}

			$item_permalink = normalize_url_by_current_lang(stripslash($item["permalink_parent"]) . "/" . $item["smart_url"]);
            if($settings_path == stripslash($item["parent"]) . "/" . $item["name"]) {
                $is_here = true;
                
                if($layout_settings["AREA_VGALLERY_FORCE_ACTUAL_LINK"]) {
                    $tpl->set_var("show_file", $item_permalink);
                } else {
				    //$tpl->set_var("show_file", "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $item["parent"] . $item["name"]));
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

            if($layout_settings["AREA_VGALLERY_FORCE_EMPTY_LINK"]) {
            	$enable_link = true;
			} else {
				$enable_link = $item["count"] + ($layout_settings["AREA_VGALLERY_SHOW_EMPTY_DIR"] ? $item["count_dir"] : 0);
			}         
            if($layout_settings["AREA_VGALLERY_NAME_SHOW_IMAGE"]) {
                if($enable_link) {
                    if($is_here) {
                		$item_class["current"] = $menu_params["class"]["current"];
						if($item["ajax"])
							$item_class["ajax"] = "ajaxcontent";

						$tpl->set_var("class_elem", ' class="' . implode(" ", $item_class) . '"');
                        $tpl->parse("SezItemImgHere", false);
                        $tpl->set_var("SezItemImgNoHere", "");
                    } else {
                        if(strpos($settings_path, stripslash($item["parent"]) . "/" . $item["name"]) !== FALSE) {
	                        $item_class["current"] = $menu_params["class"]["current"];
                        }
						if($item["ajax"]) 
						{
							$item_class["ajax"] = "ajaxcontent";
							$tpl->set_var("class_elem", ' class="' . $item_class["ajax"] . '"');
						}
                        $tpl->set_var("SezItemImgHere", "");
                        $tpl->parse("SezItemImgNoHere", false);
                    }
                    $tpl->set_var("SezItemImgNoLink", "");
                } else {
                    $tpl->set_var("SezItemImgHere", "");
                    $tpl->set_var("SezItemImgNoHere", "");
                    $tpl->parse("SezItemImgNoLink", false);
                }
                
                $tpl->parse("SezItemImg", false);
                $tpl->set_var("SezItemNoImg", "");
            } else {
                if($enable_link) {
                    if($is_here) {
                		$item_class["current"] = $menu_params["class"]["current"];
	                   
						if($item["ajax"])
							$item_class["ajax"] = "ajaxcontent";

						$tpl->set_var("class_elem", ' class="' . implode(" ", $item_class) . '"');
                        $tpl->parse("SezItemNoImgHere", false);
                        $tpl->set_var("SezItemNoImgNoHere", "");
                    } else {
                        if(strpos($settings_path, stripslash($item["parent"]) . "/" . $item["name"]) !== FALSE) {
                			$item_class["current"] = $menu_params["class"]["current"];
	                    }
	                    
						if($item["ajax"])
						{
							$item_class["ajax"] = "ajaxcontent";
							$tpl->set_var("class_elem", ' class="' . $item_class["ajax"] . '"');
						}
                        $tpl->set_var("SezItemNoImgHere", "");
                        $tpl->parse("SezItemNoImgNoHere", false); 
                    }
                    $tpl->set_var("SezItemNoImgNoLink", "");
                } else {
                    $tpl->set_var("SezItemNoImgHere", "");
                    $tpl->set_var("SezItemNoImgNoHere", "");
                    $tpl->parse("SezItemNoImgNoLink", false);
                }

                $tpl->set_var("SezItemImg", "");
                $tpl->parse("SezItemNoImg", false);
            }

            if($layout_settings["AREA_VGALLERY_NAME_SHOW_DESCRIPTION"]) {
                if ((strlen(trim(strip_tags($item["description"]))) || strpos($item["description"], "<img") !== false)) {
                    $tpl->set_var("description", $item["description"]);
                    $tpl->parse("SezItemDescription", false);
                } else {
                    $tpl->set_var("SezItemDescription", "");
                }
            }
            
            if(check_mod($file_permission, 2) && (AREA_VGALLERY_DIR_SHOW_ADDNEW || AREA_VGALLERY_DIR_SHOW_MODIFY || AREA_VGALLERY_DIR_SHOW_DELETE || AREA_PROPERTIES_SHOW_MODIFY || AREA_ECOMMERCE_SHOW_MODIFY || AREA_SETTINGS_SHOW_MODIFY)) {
                $popup["admin"]["unic_name"] = $unic_id . stripslash($item["parent"]) . "/" . $item["name"];
                $popup["admin"]["title"] = $layout["title"] . ": " . stripslash($item["parent"]) . "/" . $item["name"];
				$popup["admin"]["class"] = $layout["type_class"];
				$popup["admin"]["group"] = $layout["type_group"];
				
                if($item["is_dir"] && (count(explode("/", stripslash($item["parent"]) . "/" . $item["name"]))  <= $limit_level)) {
                    $allow_insert_dir = true;
                } else {
                    $allow_insert_dir = false;
                }        
                
                if(AREA_VGALLERY_DIR_SHOW_ADDNEW && $allow_insert_dir) {
                    $popup["admin"]["adddir"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/modify?type=" . ($item["is_dir"] ? "dir" : "node") . "&vname=" . $vgallery_name . "&path=" . urlencode(stripslash($item["parent"]) . "/" . $item["name"]) . "&extype=vgallery_nodes"; 
                } else {
                    $popup["admin"]["adddir"] = "";
                }

                if(strtolower(stripslash($item["parent"]) . "/" . $item["name"]) != "/" . strtolower($vgallery_name)) {
                    if(AREA_VGALLERY_DIR_SHOW_MODIFY) {
                        $popup["admin"]["modify"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/modify?keys[ID]=" . $item["ID"] . "&type=" . ($item["is_dir"] ? "dir" : "node") . "&vname=" . $vgallery_name . "&path=" . urlencode($item["parent"]) . "&extype=vgallery_nodes"; 
                    }
                    if(AREA_VGALLERY_DIR_SHOW_DELETE) {
                        $popup["admin"]["delete"] = ffDialog(TRUE,
                                                        "yesno",
                                                        ffTemplate::_get_word_by_code("vgallery_erase_title"),
                                                        ffTemplate::_get_word_by_code("vgallery_erase_description"),
                                                        "--returl--",
                                                        FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/modify?keys[ID]=" . $item["ID"] . "&type=" . ($item["is_dir"] ? "dir" : "node") . "&vname=" . $vgallery_name . "&path=" . urlencode($item["parent"]) . "&extype=vgallery_nodes&ret_url=" . "--encodereturl--" . "&VGalleryNodesModify_frmAction=confirmdelete",
                                                        FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/dialog");
                    }
                } else {
                    $popup["admin"]["modify"] = "";
                    $popup["admin"]["delete"] = "";
                }
                if(AREA_PROPERTIES_SHOW_MODIFY) {
                    $popup["admin"]["extra"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/properties?keys[ID]=" . $item["ID"] . "&type=" . ($item["is_dir"] ? "dir" : "node") . "&vname=" . $vgallery_name . "&path=" . urlencode($item["parent"]) . "&extype=vgallery_nodes&ret_url=" . "--encodereturl--" . "&layout=" . $layout["ID"]; 
                }
                if(AREA_ECOMMERCE_SHOW_MODIFY && $enable_ecommerce) {
                    $popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_WS_ECOMMERCE . "/vgallery/" . ffCommon_url_rewrite($vgallery_name) . "/ecommerce/all?keys[ID]=" . $item["ID"] . "&type=" . ($item["is_dir"] ? "dir" : "node") . "&vname=" . $vgallery_name . "&path=" . urlencode($item["parent"]) . "&extype=vgallery_nodes&ret_url=" . "--encodereturl--"; 
                }
                if(AREA_SETTINGS_SHOW_MODIFY) {
                    $popup["admin"]["setting"] = "";
                }

                $popup["sys"]["path"] = $user_path;
                $popup["sys"]["type"] = "admin_popup";

				if(check_function("set_template_var"))
					$item_properties["admin"] = 'data-admin="' . get_admin_bar($popup, VG_SITE_FRAME . $vg_father["source_user_path"]) . '"';
					                
//	            $serial_popup = json_encode($popup);
//	            $item_properties["admin"] = 'data-admin="' . FF_SITE_PATH . VG_SITE_FRAME . $vg_father["source_user_path"] . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"';

	            $item_class["admin"] = "admin-bar";
            } 

            if(1 || $enable_link) {
            	if(check_function("process_vgallery_menu_array_child"))
                	$child = process_vgallery_menu_array_child($menu_item, stripslash($item["parent"]) . "/" . $item["name"], $source_user_path, $vgallery_name, $layout, 2, $enable_ecommerce, $limit_level);

                if(strlen($child) && $menu_params["icon"]) {
                    $tpl->set_var("caret_class", $menu_params["icon"]);
                    $tpl->parse("SezCaret", false);
                }
			}

            $tpl->set_var("child", $child);
            if(strlen($child))
            	$item_class["child"] = $menu_params["class"]["has_child"];

            //$item_class["default"] = $item["smart_url"];

			if(count($item_class))
				$item_properties["class"] = 'class="' . implode(" ", array_filter($item_class)) . '"';
				
			$tpl->set_var("item_properties", implode(" ", array_filter($item_properties)));
            $tpl->parse("SezItem", true);
            $tpl->set_var("SezError", ""); 

            $count_item++;
        }    
        if(strlen($layout_settings["AREA_VGALLERY_PLUGIN"]))
        	$tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $layout_settings["AREA_VGALLERY_PLUGIN"])); 
        else
        	$tpl->set_var("class_plugin", "vgmenu"); 
        	
        $tpl->parse("SezMenu", false);
    } else {
        $tpl->set_var("SezMenu", "");
        $strError = ffTemplate::_get_word_by_code("vgallery_no_item");
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
    
    /*
    if($layout_settings["AREA_VGALLERY_MENU_OFFCANVAS"])
    {
        $template["offcanvas"] = $block["tpl"]["pre"] . $tpl->rpparse("main", false) . $block["tpl"]["post"];
        $res["content"] = '<nav class="tab-bar hide-for-large-up"> 
                                <a class="left-off-canvas-toggle menu-icon" aria-expanded="false">
                                    <span></span>
                                </a>
                            </nav>';
    } else {
		$res["content"] = $block["tpl"]["pre"] . $tpl->rpparse("main", false) . $block["tpl"]["post"];
    }
	if(is_array($template) && count($template))
		$res["template"] = $template;
    */
	return $res;
}