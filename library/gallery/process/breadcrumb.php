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
function process_breadcrumb($user_path, $settings_path, $root_path = "/", $layout) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    //$settings_path = $globals->settings_path;
	check_function("normalize_url");
    $strip_path = array(
    					VG_SITE_SEARCH => true
    					, VG_SITE_USER => true
    					, VG_SITE_NOTIFY => false
    					, USER_RESTRICTED_PATH => array("label" => ffTemplate::_get_word_by_code("profile_title"))
    					, VG_SITE_CART . "/confirm" => true
    				);
	if(is_array($globals->user["menu"]) && count($globals->user["menu"])) {
		$strip_path = array_merge($strip_path, $globals->user["menu"]);
	}

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];
    
    $show_home = $layout_settings["AREA_ORINAV_SHOW_NAV_HOME"];
    $show_nav = $layout_settings["AREA_ORINAV_SHOW_NAV"];
    
    if(check_function("get_grid_system_params"))
        $breadcrumb_params = get_grid_system_breadcrumb();

    $template_name = ($layout["template"]
    					? $layout["template"]
    					: "default"
    				) . ".html";
    
    //$tpl_data["custom"] = "breadcrumb.html";
    $tpl_data["custom"] = $layout["smart_url"] . ".html";
    $tpl_data["base"] = $template_name;
    $tpl_data["path"] = $layout["tpl_path"];
    
    $tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
    
    $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   

    /**
    * Admin Father Bar
    */
    if(AREA_ORINAV_SHOW_MODIFY) {
        $admin_menu["admin"]["unic_name"] = $unic_id;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if(AREA_PROPERTIES_SHOW_MODIFY) {
            $admin_menu["admin"]["extra"] = "";
        }
        if(AREA_ECOMMERCE_SHOW_MODIFY) {
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
        //$admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
    }

	/**
	* Process Block Header
	*/		    
	if($tpl_data["result"]["type"] != "custom") 
		$block["exclude"]["class"]["filename"] = true;
	else 
		$block["exclude"]["class"]["default"] = true;
	
    if(check_function("set_template_var")) { 
        $tpl = set_template_var($tpl);
		$block = get_template_header($user_path, $admin_menu, $layout, $tpl, $block);
    }

    
    
    $tmp_user_path = explode("/", substr($user_path, strlen(stripslash($root_path))));
    if($layout_settings["AREA_ORINAV_SHOW_NAV_ONLYHOME"]) {
        $user_path = $root_path . "/" . $tmp_user_path[1];
    }

    $hide_preview = false;
	if($layout_settings["AREA_ORINAV_HIDE_TITLE_HOME"] && ($user_path == $root_path . "/" . $tmp_user_path[1])) {
		$hide_preview = true;
	}
	
	if($layout_settings["AREA_ORINAV_SHOW_NAV_NAME"] || ($layout_settings["AREA_ORINAV_SHOW_PREVIEW_NAME"] && !$hide_preview)) {
		$alias_full_path = normalize_url($settings_path, HIDE_EXT, false, LANGUAGE_INSET, false, array("meta_title_alt", "meta_title"), true);
		$page_title_is_set = false;
		if($alias_full_path == "[META_TITLE]") {
			if(isset($globals->page_title) && strlen($globals->page_title)) {
				$page_title_is_set = true;
				$alias_full_path = $globals->page_title; 
			} else {
				$alias_full_path = ffTemplate::_get_word_by_code(basename($settings_path));
			}
		} 
	    
	    
	    $preview_name = ffGetFilename($user_path); 
	}
	
	if (!$hide_preview && $layout_settings["AREA_ORINAV_SHOW_PREVIEW_NAME"]) {
	    if (strlen($alias_full_path)) {
	        $tpl->set_var("name", ffCommon_specialchars($alias_full_path));
	    } else {
	        $tpl->set_var("name", ffCommon_specialchars($preview_name));
	    }
	    if($layout_settings["AREA_ORINAV_SHOW_PREVIEW_TOP"]) {
        	$tpl->parse("SezBreadCrumbTitleTop", false);
        	$tpl->set_var("SezBreadCrumbTitleBottom", "");
		} else {
			$tpl->set_var("SezBreadCrumbTitleTop", "");
			$tpl->parse("SezBreadCrumbTitleBottom", false);
		}
	} else {
			$tpl->set_var("SezBreadCrumbTitleTop", "");
        	$tpl->set_var("SezBreadCrumbTitleBottom", "");
	}

	 if ($layout_settings["AREA_ORINAV_SHOW_BACK"]) {
	 	if($layout_settings["AREA_ORINAV_SHOW_BACK_FORCE"] && strpos(ffCommon_dirname($settings_path), ($root_path == "/" ? $root_path : stripslash($root_path))) === 0) {
			$actual_back = ffCommon_dirname($user_path);
	 	} else {
			$actual_back = $_SERVER["HTTP_REFERER"];
	 	}
		if(strlen($actual_back)) {
			$tpl->set_var("ret_url", $actual_back);
		    if($layout_settings["AREA_ORINAV_SHOW_BACK_TOP"]) {
        		$tpl->parse("SezNavigationBackTop", false);
        		$tpl->set_var("SezNavigationBackBottom", "");
			} else {
				$tpl->set_var("SezNavigationBackTop", "");
				$tpl->parse("SezNavigationBackBottom", false);
			}
		} else {
			$tpl->set_var("SezNavigationBackTop", "");
		    $tpl->set_var("SezNavigationBackBottom", "");
		}
	} else {
		$tpl->set_var("SezNavigationBackTop", "");
	    $tpl->set_var("SezNavigationBackBottom", "");
	}    
    
    
    
    if(($settings_path != $root_path)) {
        if ($layout_settings["AREA_ORINAV_SHOW_NAV_TITLE"]) {
            $tpl->parse("SezNavigationTitle", false);    
        } else {
            $tpl->set_var("SezNavigationTitle", "");
        }
 
        if($show_nav) {
            $arrAlias = array_reverse(explode("/", normalize_url(ffCommon_dirname($settings_path), HIDE_EXT, false, LANGUAGE_INSET, false, "meta_title", false, "/")));
		    
            if(check_function("get_vgallery_group"))
		        $vgallery_group = get_vgallery_group(basename($settings_path), "name");
		    if(strlen($vgallery_group)) {
			    $real_path = ffcommon_dirname($settings_path);
		    } else {
			    $real_path = $settings_path;
		    }

            $smart_url = explode("/", normalize_url(ffCommon_dirname($real_path), HIDE_EXT, false, LANGUAGE_INSET, false, "smart_url", false, null, ffCommon_url_rewrite($vgallery_group)));

            $parzial_path = "";
            $count_elem = 0;
            $alternative_preview_name = "";
 
            foreach($smart_url as $key_user_path => $part_user_path) {
                if(strlen($part_user_path)) {
                    $parzial_path =  stripslash($parzial_path) . "/" . str_replace("_", "/", $part_user_path);
					$alternative_parzial_path = "";
                    if(array_key_exists($parzial_path, $strip_path)) {
						if(is_array($strip_path[$parzial_path])) {
							if(array_key_exists("break", $strip_path[$parzial_path])) {
								if(array_key_exists("label", $strip_path[$parzial_path]) && strlen($strip_path[$parzial_path]["label"])) {
									$forced_alternative_preview_name = $strip_path[$parzial_path]["label"];
								} else {
									$alternative_preview_name = $part_user_path;
								}
								break;
							} else {							
								if(array_key_exists("label", $strip_path[$parzial_path]) && strlen($strip_path[$parzial_path]["label"])) {
									$arrAlias[$count_elem] = $strip_path[$parzial_path]["label"];
								}
								if(array_key_exists("path", $strip_path[$parzial_path]) && strlen($strip_path[$parzial_path]["path"])) {
									$alternative_parzial_path = $strip_path[$parzial_path]["path"];
								}
							}
						} elseif($strip_path[$parzial_path] === true) {
							continue;	
                    	} elseif($strip_path[$parzial_path] === false) {
                    		$alternative_preview_name = $part_user_path;
                    		break;
						} elseif(strlen($strip_path[$parzial_path])) {
							$alternative_parzial_path = $strip_path[$parzial_path];
						} else {
							
						}
                	    
				    }
				    
                	if(substr_count($parzial_path, "/") - substr_count(stripslash($root_path), "/") > 0) {
	                    $alias = $arrAlias[$count_elem];

	                    $tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $parzial_path)));
	                    $tpl->set_var("part_actual_name", strlen($alias) 
	                                                        ? $alias
	                                                        : $part_user_path);
	                    $tpl->set_var("part_actual_path",  normalize_url_by_current_lang(strlen($alternative_parzial_path)
	                    									? $alternative_parzial_path
	                    									: $parzial_path
	                    								));
	                    
	                    if(!$layout_settings["AREA_ORINAV_SHOW_NAV_NAME"] && (ffCommon_dirname($settings_path) == $parzial_path || count($smart_url) == $key_user_path + 1)) {
	                        $tpl->set_var("part_actual_separator", "");
	                    } else {
	                        $tpl->set_var("part_actual_separator", ffTemplate::_get_word_by_code("breadcrumb_spacer"));
	                    }
                            $tpl->set_var("current", ""); 
	                    $tpl->parse("SezNavigationLinkPart", false);
	                    $tpl->set_var("SezNavigationNoLinkPart", "");
	                    $tpl->parse("SezNavigationActualPath", true);
					}                    
                    $count_elem++;  
                }   
            }
        }
        if ($show_home) {
            $tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . "home")));
            
            if($show_nav) {
                $tpl->set_var("home_name", ffTemplate::_get_word_by_code("breadcrumb_home"));
            } else {
                $tpl->set_var("home_name", normalize_url($root_path, HIDE_EXT, false, LANGUAGE_INSET, false, array("meta_title_alt", "meta_title"), true));
            }

            $tpl->set_var("home_path", normalize_url_by_current_lang($root_path));
            if($count_elem || $layout_settings["AREA_ORINAV_SHOW_NAV_NAME"]) {
                $tpl->set_var("home_separator", ffTemplate::_get_word_by_code("breadcrumb_spacer"));
                $tpl->set_var("current", ""); 
            } else {
                if(isset($breadcrumb_params["class"]["current"]))
                    $tpl->set_var("current", " " . $breadcrumb_params["class"]["current"]); 
                $tpl->set_var("home_separator", ""); 
            }
            $tpl->parse("SezNavigationHome", false);
        }                

        if ($layout_settings["AREA_ORINAV_SHOW_NAV_NAME"]) { 
            $tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id . $user_path)));
            $tpl->set_var("part_actual_name", ((strlen($alternative_preview_name) || strlen($forced_alternative_preview_name)) && !$page_title_is_set
									                ? (strlen($forced_alternative_preview_name)
									                	? $forced_alternative_preview_name
									                	: $alternative_preview_name . "_" . $preview_name
									                )
									                : (strlen($alias_full_path)
									                        ? $alias_full_path 
									                        : $preview_name
									                ) 
            									)
            								);
            if(isset($breadcrumb_params["class"]["current"]))
                $tpl->set_var("current", " " . $breadcrumb_params["class"]["current"]); 
            $tpl->set_var("part_actual_path", ""); 
            $tpl->set_var("part_actual_path", ""); 
            $tpl->set_var("part_actual_separator", "");
            $tpl->set_var("SezNavigationLinkPart", "");
            $tpl->parse("SezNavigationNoLinkPart", false);
            $tpl->parse("SezNavigationSepPart", false);
            $tpl->parse("SezNavigationActualPath", true);
        }
        $tpl->parse("SezNavigation", false);    
    } else {
        $tpl->set_var("SezNavigation", "");
    }

	$buffer = $tpl->rpparse("main", false);
    return array(
		"pre" 			=> $block["tpl"]["pre"]
		, "post" 		=> $block["tpl"]["post"]
		, "content" 	=> $buffer
		, "default" 	=> $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
	);
}
