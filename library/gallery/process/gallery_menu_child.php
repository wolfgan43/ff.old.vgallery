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
function process_gallery_menu_child($user_path, $source_user_path, $real_user_path, $layout, $absolute_path = DISK_UPDIR, $skip_control = false) 
{
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

	check_function("normalize_url");

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
   // $absolute_path = realpath($abs_path . $real_user_path);
    $buffer = "";

    if($absolute_path == DISK_UPDIR) {
    	$is_absolute = false;
    	$manage_path = VG_SITE_GALLERYMODIFY;
        $component_action = "GalleryModify";
        
        $admin_settings["AREA_SHOW_ADDNEW"] = AREA_GALLERY_SHOW_ADDNEW;
        $admin_settings["AREA_SHOW_MODIFY"] = AREA_GALLERY_SHOW_MODIFY;
        $admin_settings["AREA_SHOW_DELETE"] = AREA_GALLERY_SHOW_DELETE;
	} else {
		$is_absolute = true;
		$manage_path = VG_SITE_GALLERYMANAGE;
        $component_action = "ThemeModify";
        
        $admin_settings["AREA_SHOW_ADDNEW"] = AREA_THEME_SHOW_ADDNEW;
        $admin_settings["AREA_SHOW_MODIFY"] = AREA_THEME_SHOW_MODIFY;
        $admin_settings["AREA_SHOW_DELETE"] = AREA_THEME_SHOW_DELETE;
	}

    if(!$is_absolute && !$skip_control) {
	    if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
	    	$file_permission = get_file_permission($real_user_path, "files", true);
	    if (!check_mod($file_permission, 1, true, $admin_settings["AREA_SHOW_MODIFY"])) {  
	        return;
	    }
	}
	
    if(is_dir($absolute_path . $real_user_path)) {
	    if(check_function("get_grid_system_params"))
    		$menu_params = get_grid_system_menu($layout["template"], true, true);
	    
		$tpl_data["custom"] = "album_child.html";
		$tpl_data["base"] = $menu_params["tpl_name"];
		$tpl_data["path"] = $layout["tpl_path"];

		$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
		
		$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
		$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   	    
/*
    	$params_menu = "menu-child";
    	if($layout_settings["AREA_GALLERY_MENU_VERTICAL"])
		{
			$params_menu = "menu-side-child";
		} elseif($layout_settings["AREA_GALLERY_MENU_OFFCANVAS"])
		{
			$params_menu = "menu-side-offcanvas-child";
		}
		if(check_function("get_grid_system_params")) {
			$grid_params = get_grid_system_params($params_menu, $layout_settings["AREA_STATIC_MENU_FOLLOW_FRAMEWORK_CSS"]);
		}
		
	    $tpl_data["custom"] = "album_child.html";
	    $tpl_data["base"] = $grid_params["tpl_name"];

	    $tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
	    
	    $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	    $tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   
*/
       // $tpl = ffTemplate::factory(get_template_cascading($user_path, "menu_child.html", "", null, $layout["location"]));
        //$tpl->load_file("menu_child.html", "main");

        $tpl->set_var("site_path", FF_SITE_PATH);
        $tpl->set_var("theme_inset", THEME_INSET);

        $arr_real_file = glob(stripslash($absolute_path . $real_user_path) . "/*");
        if(is_array($arr_real_file) && count($arr_real_file)) {
            $rst_dir = array();
            $rst_file = array();
            $rst_res = array(); 
            
	        foreach ($arr_real_file as $real_file) { 
	            if ((is_dir($real_file) && basename($real_file) != CM_SHOWFILES_THUMB_PATH /*&& basename($real_file) != GALLERY_TPL_PATH*/) || (is_file($real_file) && strpos(basename($real_file), "pdf-conversion") === false) && strpos(basename($real_file), ".") !== 0) {
	                $file = str_replace($absolute_path, "", $real_file);
	                if(is_dir($real_file)) {
	                	if(!$is_absolute && !$skip_control) {
	                		if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
		                    	$file_permission = get_file_permission($file);
		                    if (check_mod($file_permission, 1, true, AREA_GALLERY_SHOW_MODIFY)) {
		                    	if(check_function("get_gallery_information_by_lang")) {
			                        $rst_dir[$file]["alias"] = get_gallery_information_by_lang($file);
			                        $rst_dir[$file]["description"] = get_gallery_information_by_lang($file, "description", $layout_settings["AREA_THUMB_ENABLE_CASCADING"]);
			                        $rst_dir[$file]["alt_url"] = get_gallery_information_by_lang($file, "alt_url", $layout_settings["AREA_THUMB_ENABLE_CASCADING"]);
								}
		                    }
		                    if ($admin_settings["AREA_SHOW_MODIFY"] && check_mod($file_permission, 2)) {
		                        $rst_dir[$file]["edit"] = true;
		                    } else {
		                        $rst_dir[$file]["edit"] = false;
		                    }
						} else {
							$rst_dir[$file]["edit"] = true;
						}
	                }
	                if(!$layout_settings["AREA_DIRECTORIES_SHOW_ONLYDIR"] && is_file($real_file)) {
						if(!$is_absolute && !$skip_control) {
							if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
		                    	$file_permission = get_file_permission($file);
		                    if (check_mod($file_permission, 1, true, AREA_GALLERY_SHOW_MODIFY)) {
		                    	if(check_function("get_gallery_information_by_lang")) {
			                        $rst_file[$file]["alias"] = get_gallery_information_by_lang($file);
			                        $rst_file[$file]["description"] = get_gallery_information_by_lang($file, "description", $layout_settings["AREA_THUMB_ENABLE_CASCADING"]);
			                        $rst_file[$file]["alt_url"] = get_gallery_information_by_lang($file, "alt_url", $layout_settings["AREA_THUMB_ENABLE_CASCADING"]);
								}
		                    }
		                    if ($admin_settings["AREA_SHOW_MODIFY"] && check_mod($file_permission, 2)) {
		                        $rst_file[$file]["edit"] = true;
		                    } else {
		                        $rst_file[$file]["edit"] = false;
		                    }
						} else {
							$rst_file[$file]["edit"] = true;
						}

	                }
	            }
	        }
            ksort($rst_dir);
            reset($rst_dir);
            ksort($rst_file);
            reset($rst_file);
            $rst_res = array_merge($rst_dir, $rst_file);
        }

        if(is_array($rst_res) && count($rst_res)) {
        	$count_item = 1;
            foreach ($rst_res as $file => $file_value) {
       			$child = "";
           		$item_class = array();
           		$item_properties = array();            
        		$show_file = "/" . basename($file);

                set_cache_data("G", md5($file), "T0", $file);
        		//$globals->cache["data_blocks"]["G" . "T0" . "-" . md5($file)] = $file;
        		
                //$tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $file)));

                if ($layout_settings["AREA_DIRECTORIES_SHOW_ALIAS"] && strlen($file_value["alias"])) {
                    $tpl->set_var("item", ffCommon_specialchars($file_value["alias"]));        
                } else {
                    $tpl->set_var("item", ffCommon_specialchars(basename($file)));
                }

				if($layout_settings["AREA_DIRECTORIES_SHOW_AJAX"] && is_dir($absolute_path . $file))  {                
	                $frame["sys"]["type"] = "GALLERY_MENU_CHILD";
	                $frame["sys"]["source_user_path"] = $source_user_path;
	                $frame["sys"]["real_user_path"] = $file;
	                $frame["sys"]["layout"] = $layout;
	                $frame["sys"]["settings_path"] = $settings_path;
	                $frame["sys"]["is_absolute"] = $is_absolute;
	                $frame["sys"]["skip_control"] = $skip_control;
	                $serial_frame = json_encode($frame);

	                $tpl->set_var("ajax_child", FF_SITE_PATH . VG_SITE_FRAME . $user_path . $show_file . "?sid=" . set_sid($serial_frame));
		            $tpl->parse("SezAjaxChild", false);
				} else {
					$tpl->set_var("SezAjaxChild", "");
				}

                if($settings_path == $user_path . $show_file) {
                    $is_here = true;

                    if($layout_settings["AREA_GALLERY_FORCE_ACTUAL_LINK"]) {
        				$tpl->set_var("show_file", normalize_url_by_current_lang($user_path . $show_file));
                    } else {
					    $tpl->set_var("show_file", "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $file));
                    }
                } else {
                    $is_here = false;
					
					if(!$is_absolute) {
		                $tpl->set_var("SezTarget", "");
		                if(strlen($file_value["alt_url"])) {
							if (
								substr($file_value["alt_url"], 0, 1) != "/"
							) {
								$tpl->set_var("show_file", $file_value["alt_url"]);
								if(
									substr(strtolower($file_value["alt_url"]), 0, 7) == "http://"
									|| substr(strtolower($file_value["alt_url"]), 0, 8) == "https://"
                                    || substr($file_value["alt_url"], 0, 2) == "//"
								) {
                    				$tpl->parse("SezTarget", false);
								} else {
									$tpl->set_var("SezTarget", "");	
								}
							} else {
								if(strpos($file_value["alt_url"], "#") !== false) {
									$part_alternative_hash = substr($file_value["alt_url"], strpos($file_value["alt_url"], "#"));
									$file_value["alt_url"] = substr($file_value["alt_url"], 0, strpos($file_value["alt_url"], "#"));
								}
								
								if(strpos($file_value["alt_url"], "?") !== false) {
									$part_alternative_path = substr($file_value["alt_url"], 0, strpos($file_value["alt_url"], "?"));
									$part_alternative_url = substr($file_value["alt_url"], strpos($file_value["alt_url"], "?"));
								} else {
									$part_alternative_path = $file_value["alt_url"];
									$part_alternative_url = "";
								}
								if(check_function("get_international_settings_path")) {
									$res = get_international_settings_path($part_alternative_path, LANGUAGE_INSET);
									$tpl->set_var("show_file", normalize_url($res["url"], HIDE_EXT, true, LANGUAGE_INSET) . $part_alternative_url . $part_alternative_hash);
								}
							}
						} else {                
					        if($source_user_path) {
        						$tpl->set_var("show_file", normalize_url_by_current_lang($user_path . $show_file));
							} else {
								$tpl->set_var("show_file", "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $file));
							}
						}
					} else {
						$tpl->set_var("show_file", "#" . preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $file));	
					}
                }

                if($layout_settings["AREA_DIRECTORIES_NAME_SHOW_IMAGE"]) {
                    if($is_here) {
                		$item_class["current"] = $menu_params["class"]["current"];

                        $tpl->parse("SezItemImgHere", false);
                        $tpl->set_var("SezItemImgNoHere", "");
                    } else {
	                    if(strpos($settings_path, $user_path . $show_file) !== FALSE) { 
                			$item_class["current"] = $menu_params["class"]["current"];
	                    }
                        $tpl->set_var("SezItemImgHere", "");
                        $tpl->parse("SezItemImgNoHere", false);
                    }
                    $tpl->parse("SezItemImg", false);
                    $tpl->set_var("SezItemNoImg", "");
                } else {
                    if($is_here) {
                		$item_class["current"] = $menu_params["class"]["current"];

                        $tpl->parse("SezItemNoImgHere", false);
                        $tpl->set_var("SezItemNoImgNoHere", "");
                    } else {
	                    if(strpos($settings_path, $user_path . $show_file) !== FALSE) { 
                			$item_class["current"] = $menu_params["class"]["current"];
	                    }
                        $tpl->set_var("SezItemNoImgHere", "");
                        $tpl->parse("SezItemNoImgNoHere", false);
                    }
                    $tpl->set_var("SezItemImg", "");
                    $tpl->parse("SezItemNoImg", false);
                }

                if ($layout_settings["AREA_DIRECTORIES_SHOW_DESCRIPTION"] && strlen(trim(strip_tags($file_value["description"])))) {
                    $tpl->set_var("description", $description);
                    $tpl->parse("SezItemDescription", false);
                } else {
                    $tpl->set_var("SezItemDescription", "");
                }
                
                if ($file_value["edit"]) {
                    $popup["admin"]["unic_name"] = $unic_id . $file;
                    $popup["admin"]["title"] = $layout["title"] . ": " . $file;
                    $popup["admin"]["class"] = $layout["type_class"];
                    $popup["admin"]["group"] = $layout["type_group"];
                    
                    $popup["admin"]["disable_huge"] = true;
                    
                    if($admin_settings["AREA_SHOW_ADDNEW"] && is_dir($absolute_path . $file)) {
                        $popup["admin"]["adddir"] = FF_SITE_PATH . $manage_path . "/add/dir?path=" . urlencode($file) . "&extype=files"; 
                        $popup["admin"]["addnew"] = FF_SITE_PATH . $manage_path . "/add/item?path=" . urlencode($file) . "&extype=files";
                    } else {
                        $popup["admin"]["adddir"] = "";
                        $popup["admin"]["addnew"] = "";
                    }

                    if($admin_settings["AREA_SHOW_MODIFY"]) {
                        $popup["admin"]["modify"] = FF_SITE_PATH . $manage_path . "/modify?path=" . urlencode($file) . "&extype=files";
                    }
                    if($admin_settings["AREA_SHOW_DELETE"]) {
                        $popup["admin"]["delete"] = ffDialog(TRUE,
                                                            "yesno",
                                                            ffTemplate::_get_word_by_code("vgallery_erase_title"),
                                                            ffTemplate::_get_word_by_code("vgallery_erase_description"),
                                                            "--returl--",
                                                            FF_SITE_PATH . $manage_path . "/modify?path=" . urlencode($file) . "&extype=files&ret_url=" . "--encodereturl--" . "&" . $component_action ."_frmAction=confirmdelete", 
                                                            FF_SITE_PATH . $manage_path . "/dialog");
                    }
                    if(!$is_absolute && AREA_PROPERTIES_SHOW_MODIFY) {
                        $popup["admin"]["extra"] = FF_SITE_PATH . $manage_path . "/properties?path=" . urlencode($file) . "&extype=files" . "&layout=" . $layout["ID"];
                    }
                    if(!$is_absolute && AREA_ECOMMERCE_SHOW_MODIFY && ENABLE_ECOMMERCE_FILES) {
                        $popup["admin"]["ecommerce"] = FF_SITE_PATH . $manage_path . "/ecommerce?path=" . urlencode($file) . "&extype=files";
                    }
		            if(AREA_GALLERY_SHOW_PERMISSION && ENABLE_STD_PERMISSION) {
		            	$popup["admin"]["setting"]["path"] = FF_SITE_PATH . $manage_path . "/permission?path=" . urlencode($file) . "&extype=files";
		            }

		            if(!$is_absolute)
            			$popup["sys"]["path"] = $settings_path;
		            else
            			$popup["sys"]["path"] = $user_path;

                    $popup["sys"]["type"] = "admin_popup";
                    $popup["sys"]["is_absolute"] = $is_absolute;

					if(strlen($block["admin"]["popup"])) {
		                $serial_popup = json_encode($popup);
		                
		                $item_properties["admin"] = 'data-admin="' . FF_SITE_PATH . VG_SITE_FRAME . $source_user_path . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"';
		                //$item_class["admin"] = "admin-bar";
					}  
                }

                if(!$layout_settings["AREA_DIRECTORIES_SHOW_ONLYHOME"] && !$layout_settings["AREA_DIRECTORIES_SHOW_AJAX"]) {
                    $child = process_gallery_menu_child($user_path, $source_user_path, $file, $layout, $absolute_path);
	                $tpl->set_var("child", $child);
		            if(strlen($child))
            			$item_class["child"] = $menu_params["class"]["has_child"];
				}

	            $item_class["default"] = "item" . $count_item;
					
				if(count($item_class))
					$item_properties["class"] = 'class="' . implode(" ", array_filter($item_class)) . '"';
					
				$tpl->set_var("item_properties", implode(" ", array_filter($item_properties)));
				$tpl->parse("SezItem", true);
				
				$count_item++;
            }
            $buffer = $tpl->rpparse("main", false); 
		}
    }
    
    return $buffer;
}
