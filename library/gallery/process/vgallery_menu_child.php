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
function process_vgallery_menu_child($menu_item = null, $user_path, $source_user_path = null, $layout, $level = 2) 
{
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

    check_function("normalize_url");

    $db = ffDB_Sql::factory();
    $db_desc = ffDB_Sql::factory();

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
    
	$vgallery_name = substr($user_path, 1, strpos($user_path, "/", 1) -1);
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
    	$father_permission = get_file_permission($user_path, "vgallery_nodes", true);
    if (!check_mod($father_permission, 1, ($enable_multilang_visible ? true : LANGUAGE_DEFAULT))) {  
        return;
    }

    if(check_function("get_grid_system_params"))
    	$menu_params = get_grid_system_menu($layout["template"], $layout_settings["AREA_STATIC_MENU_FOLLOW_FRAMEWORK_CSS"], true);

	$tpl_data["custom"] = $vgallery_name . "_menu_child.html";
	$tpl_data["base"] = $menu_params["tpl_name"];
	$tpl_data["path"] = $layout["tpl_path"];

	$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
	
	$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");

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

    $first_access = false;
    
	if($menu_item === null) {
        $first_access = true;
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
                                , vgallery_nodes.visible                            AS `visible`
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
								, " . (!ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION
									? " vgallery_nodes_rel_languages.visible "
									: " vgallery_nodes.visible "
								) . "												AS `visible`
                            "
                        )
                    ) . "
                    , (" . ($layout_settings["AREA_VGALLERY_SHOW_COVER"]   //da togliere extras e far popolare la cover con l'id del field
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
					: "(SELECT COUNT(ID) 
						FROM vgallery_nodes AS vgallery_child 
						WHERE vgallery_child.visible > 0 
							AND vgallery_child.is_dir = 0 
							AND vgallery_child.parent LIKE CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name, '%'))"
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
                    	, vgallery_nodes.ID ";
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
	                                case "meta_description":
		                                $menu_item[$fullpath]["description"] = $tmpMeta[1];
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
	                $menu_item[$fullpath]["meta_title"]             = $db->getField("meta_title", "Text", true);  
	                $menu_item[$fullpath]["meta_title_alt"]         = $db->getField("meta_title_alt", "Text", true);  
	                $menu_item[$fullpath]["meta_description"]       = $db->getField("meta_description", "Text", true);  
	                $menu_item[$fullpath]["keywords"]               = $db->getField("keywords", "Text", true);  
                
                    $menu_item[$fullpath]["smart_url"]              = $db->getField("smart_url", "Text", true);  
                    $menu_item[$fullpath]["title"]                  = ($menu_item[$fullpath]["meta_title_alt"]
                    													? $menu_item[$fullpath]["meta_title_alt"]
                    													: $menu_item[$fullpath]["meta_title"]
                    												);  
					$menu_item[$fullpath]["description"] 			= $menu_item[$fullpath]["meta_description"];
	                $menu_item[$fullpath]["permalink_parent"]       = $db->getField("permalink_parent", "Text", true);  
	                $menu_item[$fullpath]["alt_url"]                = $db->getField("alt_url", "Text", true);  
	            }
				
				$menu_item[$fullpath]["ID"] 						= $db->getField("ID", "Number", true);
				$menu_item[$fullpath]["parent"] 					= $db->getField("parent", "Text", true);
				$menu_item[$fullpath]["name"] 						= $db->getField("name", "Text", true);
				$menu_item[$fullpath]["cover"] 						= $db->getField("cover", "Text", true);
				$menu_item[$fullpath]["description"] 				= $db->getField("meta_description", "Text", true);
				$menu_item[$fullpath]["owner"]						= $db->getField("owner", "Number", true);
		        $menu_item[$fullpath]["visible"] 					= $db->getField("visible", "Number", true);
		        $menu_item[$fullpath]["count"] 						= $db->getField("count_child", "Number", true);

				if($layout_settings["AREA_VGALLERY_SHOW_AJAX"])
					$menu_item[$fullpath]["ajax"] = true;
				else
					$menu_item[$fullpath]["ajax"] = $db->getField("use_ajax", "Number", true);

				$menu_item[$fullpath]["ajax_on_event"] 				= $db->getField("ajax_on_event", "Text", true);
				$menu_item[$fullpath]["type"] 						= "vgallery";
				
				if(!$menu_item[$fullpath]["permalink_parent"])
					$menu_item[$fullpath]["permalink_parent"] 		= $menu_item[$fullpath]["parent"];
				if(!$menu_item[$fullpath]["smart_url"])
					$menu_item[$fullpath]["smart_url"] 				= $menu_item[$fullpath]["name"];

		        $menu_key[] 										= $menu_item[$fullpath]["ID"];
			} while($db->nextRecord());
		}
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

			if(ffCommon_dirname($full_path) != $home_path && !$menu_item[$full_path]["is_dir"]) {
				$tmp_path = ffCommon_dirname($full_path);
				do {
					if(!isset($menu_item[$tmp_path]))
						break;

					$menu_item[$tmp_path]["count"]++;

					$tmp_path = ffCommon_dirname($tmp_path);
				} while($tmp_path != "/");
			}

		}

		foreach($menu_item AS $full_path => $item) {
			if(substr_count($full_path, "/") == substr_count(stripslash($user_path) . "/", "/")) {
				$part_item[$full_path] = $item;
			}
		}		
	}

    if(is_array($part_item) && count($part_item)) {
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

//            if(!$layout_settings["AREA_VGALLERY_SHOW_FULLTREE"] && (!isset($item["count"]) || $item["count"] <= 0)) {
//			    	continue;
//			}

            if(!$layout_settings["AREA_VGALLERY_SHOW_FULLTREE_ITEM"] && !$item["is_dir"]) {
				continue;
			}

            set_cache_data("V", $item["ID"], $item["ID_vgallery"]);
			//$globals->cache["data_blocks"]["V" . $item["ID_vgallery"] . "-" . $item["ID"]] = $item["ID"];
            
            $count++;
            
            //$tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $item["parent"] . $item["name"])));

			$image_cover = "";
			if($layout_settings["AREA_VGALLERY_SHOW_COVER"]) {
				if(is_file(DISK_UPDIR . $item["cover"])) {
					$image_cover = '<img src="' .  FF_SITE_PATH . constant("CM_SHOWFILES") . (strlen($layout_settings["AREA_VGALLERY_SHOW_COVER_MODE"]) ? "/" . $layout_settings["AREA_VGALLERY_SHOW_COVER_MODE"] : "") . $item["cover"] . '" />';
				}
			}            
            
            if($first_access) {
				if(strlen($image_cover)) {
					$tpl->set_var("item", $image_cover . "<span class=\"title\">" . ffCommon_specialchars($item["title"]) . ($layout_settings["AREA_VGALLERY_SHOW_COUNT"] ? " (" . (int) $item["count"] . ")" : "") . "</span>");
				} else {
					$tpl->set_var("item", $image_cover . ffCommon_specialchars($item["title"]) . ($layout_settings["AREA_VGALLERY_SHOW_COUNT"] ? " (" . (int) $item["count"] . ")" : ""));
				}
            } else {
				if(strlen($image_cover)) {
					$tpl->set_var("item", $image_cover . "<span class=\"title\">" . ffCommon_specialchars($item["title"]) . ($layout_settings["AREA_VGALLERY_SHOW_COUNT_SUB"] ? " (" . (int) $item["count"] . ")" : "") . "</span>");
				} else {
					$tpl->set_var("item", $image_cover . ffCommon_specialchars($item["title"]) . ($layout_settings["AREA_VGALLERY_SHOW_COUNT_SUB"] ? " (" . (int) $item["count"] . ")" : ""));
				}
            }

			if(0 && $layout_settings["AREA_VGALLERY_SHOW_AJAX"] && $item["is_dir"])  {
	            $frame["sys"]["type"] = "VGALLERY_MENU_CHILD";
	            $frame["sys"]["source_user_path"] = $source_user_path;
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

/*           
                $tpl->set_var("SezTarget", "");
                if(strlen($item["alt_url"])) {
					if (
						substr($item["alt_url"], 0, 1) != "/"
					) {
						$tpl->set_var("show_file", $item["alt_url"]);
						if(
							substr(strtolower($item["alt_url"]), 0, 7) == "http://"
							|| substr(strtolower($item["alt_url"]), 0, 8) == "https://"
                            || substr($item["alt_url"], 0, 2) == "//"
						) {
                    		$tpl->parse("SezTarget", false);
						} else {
							$tpl->set_var("SezTarget", "");	
						}
					} else {
						if(strpos($item["alt_url"], "#") !== false) {
							$part_alternative_hash = substr($item["alt_url"], strpos($item["alt_url"], "#"));
							$alternative_path = substr($item["alt_url"], 0, strpos($item["alt_url"], "#"));
						}
												
						if(strpos($item["alt_url"], "?") !== false) {
							$part_alternative_path = substr($item["alt_url"], 0, strpos($item["alt_url"], "?"));
							$part_alternative_url = substr($item["alt_url"], strpos($item["alt_url"], "?"));
						} else {
							$part_alternative_path = $item["alt_url"];
							$part_alternative_url = "";
						}
						if(check_function("get_international_settings_path")) {
							$res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
							$tpl->set_var("show_file", normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
						}
					}
*/
				} else {                
					$tpl->set_var("show_file", $item_permalink);
				}
            }
            
			if($layout_settings["AREA_VGALLERY_FORCE_EMPTY_LINK"]) {
				$skip_child = false;
			} else {
	            if($item["count"] + ($layout_settings["AREA_VGALLERY_SHOW_EMPTY_DIR"] ? $item["count_dir"] : 0) > 0) {
	                $skip_child = false;
	            } else {
	                $skip_child = true;
	            }
			}
            if($layout_settings["AREA_VGALLERY_NAME_SHOW_IMAGE"]) {
                if($skip_child) {
                    $tpl->set_var("SezItemImgHere", "");
                    $tpl->set_var("SezItemImgNoHere", "");
                    $tpl->parse("SezItemImgNoLink", false);
                } else {
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
                }
                $tpl->parse("SezItemImg", false);
                $tpl->set_var("SezItemNoImg", "");
            } else {
                if($skip_child) {
                    $tpl->set_var("SezItemNoImgHere", "");
                    $tpl->set_var("SezItemNoImgNoHere", "");
                    $tpl->parse("SezItemNoImgNoLink", false);
                } else {
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

            if(AREA_VGALLERY_DIR_SHOW_ADDNEW || AREA_VGALLERY_DIR_SHOW_MODIFY || AREA_VGALLERY_DIR_SHOW_DELETE || AREA_PROPERTIES_SHOW_MODIFY || AREA_ECOMMERCE_SHOW_MODIFY || AREA_SETTINGS_SHOW_MODIFY) {
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
                }
                if(AREA_PROPERTIES_SHOW_MODIFY) {
                    $popup["admin"]["extra"] = FF_SITE_PATH . VG_SITE_VGALLERYMODIFY . "/" . ffCommon_url_rewrite($vgallery_name) . "/properties?keys[ID]=" . $item["ID"] . "&type=" . ($item["is_dir"] ? "dir" : "node") . "&vname=" . $vgallery_name . "&path=" . urlencode($item["parent"]) . "&extype=vgallery_nodes" . "&layout=" . $layout["ID"]; 
                }
                if(AREA_ECOMMERCE_SHOW_MODIFY && $enable_ecommerce) {
                    $popup["admin"]["ecommerce"] = FF_SITE_PATH . VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vgallery_name) . "/ecommerce/all?keys[ID]=" . $item["ID"] . "&type=" . ($item["is_dir"] ? "dir" : "node") . "&vname=" . $vgallery_name . "&path=" . urlencode($item["parent"]) . "&extype=vgallery_nodes";
                }
                if(AREA_SETTINGS_SHOW_MODIFY) {
                    $popup["admin"]["setting"] = "";
                }

                $popup["sys"]["path"] = $user_path;
                $popup["sys"]["type"] = "admin_popup";

	            $serial_popup = json_encode($popup);
	            
	            $item_properties["admin"] = 'data-admin="' . FF_SITE_PATH . VG_SITE_FRAME . $source_user_path . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"';
	            //$item_class["admin"] = "admin-bar";
            }
			
			//in teoria nn si chiama mai questo punto 
            if(!$skip_child) {
            	$level++;
                $child = process_vgallery_menu_child($menu_item, stripslash($item["parent"]) . "/" . $item["name"], $source_user_path, $layout, $level);
                $tpl->set_var("child", $child);
	            if(strlen($child))
            		$item_class["child"] = $menu_params["class"]["has_child"];                
			}
			
            //$item_class["default"] = $item["smart_url"];
				
			if(count($item_class))
				$item_properties["class"] = 'class="' . implode(" ", array_filter($item_class)) . '"';
				
			$tpl->set_var("item_properties", implode(" ", array_filter($item_properties)));
			$tpl->parse("SezItem", true);
			
			$count_item++;
        }
        if ($count)
            $buffer = $tpl->rpparse("main", false);
    }

    return $buffer;
}