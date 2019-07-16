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

  function system_layer_shard($settings_path) {
  	$cm = cm::getInstance();
  	$db = ffDB_Sql::factory();

  	$globals = ffGlobals::getInstance("gallery");

	$layout = null;

	$cm->oPage->page_js = array();
	$cm->oPage->page_css = array();

	//$globals->cache["data_blocks"] = array(); // x cache_page

	/*if(substr_count($settings_path, "/") > 1) {
	    $arrSettingsPath = explode("/", trim($settings_path, "/")); 
		$request_type = $arrSettingsPath[0];
		unset($arrSettingsPath[0]);

		$settings_path = "/" . implode("/", $arrSettingsPath); 
	}*/

    switch($globals->page["name"]) {
        case "anagraph": 
        	$check_vgallery_dir = get_vgallery_is_dir(basename($settings_path), "/anagraph" . ffCommon_dirname($settings_path));    			

        	$layout = (check_function("get_layout_settings")
                    	? get_layout_by_block("anagraph", $settings_path)
                    	: null
	                );
        	if($check_vgallery_dir) {
	            if(check_function("process_vgallery_thumb")) {
	                $frame_buffer = process_vgallery_thumb(
	                    $settings_path
	                    , "anagraph"
	                    , array(
	                        "vgallery_name" => "anagraph"
	                        , "output" => "content"
	                        , "search" => $globals->search
	                        , "navigation" => $globals->navigation
	                        , "template_skip_hide" => true
	                    )
	                    , $layout
	                );            
	            }
        	} else {
				if(check_function("process_vgallery_view")) {
		            $frame_buffer = process_vgallery_view(
		                $settings_path
		                , "anagraph"
		                , array(	
		                    "vgallery_name" => "anagraph"
		                    , "output" => "content"
	                        , "search" => $globals->search
	                        , "navigation" => $globals->navigation
	                        , "template_skip_hide" => true
		                )
		                , $layout
		            );	
				}        	
        	}

            break;
        case "gallery":
        	$layout = (check_function("get_layout_settings")
                    	? get_layout_by_block("files", $settings_path)
                    	: null
                    );
            if(check_function("process_vgallery_thumb")) {
                $frame_buffer = process_vgallery_thumb(
                    $settings_path
                    , "files"
                    , array(
                        "vgallery_name" => "files"
                        , "output" => "content"
                        , "search" => $globals->search
                        , "navigation" => $globals->navigation
                        , "template_skip_hide" => true
                    )
                    , $layout
                );            
            }
            break;
    	case "publish":
    		$layout = (check_function("get_layout_settings")
                    	? get_layout_by_block("publishing", basename($settings_path))
                    	: null
                    );
            $publish = explode("_", $layout["db"]["value"]);
            if(is_array($publish) && count($publish) == 2) {
                $publishing = array();
                $publishing["ID"] = $publish[1];
                $publishing["src"]= $publish[0];              
    			if(check_function("process_vgallery_thumb")) {
					$frame_buffer = process_vgallery_thumb(
		                null
		                , "publishing"
		                , array(
		                    "publishing" => $publishing
		                    , "allow_insert" => false
		                    , "output" => "content"
		                    , "template_skip_hide" => true
		                )
		                , $layout
		            );    		
				}
			}
    		break;    		
        case "marker":
			$frame_buffer = "";
            if(check_function("process_vgallery_thumb")) {
            	$arrMap = explode("_", basename($settings_path));
                if(strlen($arrMap[0]))
                {
                    $sSQL = "SELECT module_maps.description_limit
                                    , module_maps.contest
                                FROM module_maps
                                WHERE name = " . $db->toSql($arrMap[0]);
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $data_limit = $db->getField("description_limit", "Text", true);
                        
                        
                        /**
                        * all
                        * selected vgallery
                        * anagraph
                        */
                        $contest = $db->getField("contest", "Text", true);
                        
                    }

                    $sSQL = "SELECT module_maps_marker.ID_node
                                    , vgallery.name AS vgallery_name
                                FROM module_maps_marker
                                    INNER JOIN vgallery_nodes ON vgallery_nodes.ID = module_maps_marker.ID_node
                                    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                WHERE module_maps_marker.smart_url = " . $db->toSql($arrMap[1]);
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        do {
                            $vgallery_name = $db->getField("vgallery_name", "Text", true);
                            $markers[$vgallery_name]["nodes"][] = $db->getField("ID_node", "Number", true);
                            if($data_limit) {
                                $markers[$vgallery_name]["fields"] = explode(",", $data_limit);
                            } 
                        } while($db->nextRecord());

                        if(is_array($markers) && count($markers) && check_function("get_layout_settings")) {
                            foreach($markers AS $vgallery_name => $marker) {
                            	$layout = get_layout_by_block("vgallery", "/" . $vgallery_name);
                                $frame_buffer .= process_vgallery_thumb(
                                                    "/" . $vgallery_name
                                                    , "vgallery"
                                                    , array(
                                                        "limit" => $marker
                                                        , "output" => "content"
                                                        , "vgallery_name" => $vgallery_name
                                                        , "search" => $globals->search
                                                        , "navigation" => $globals->navigation
                                                        , "template_skip_hide" => true
                                                    )
								                    , $layout
                                                );
                            }
                        }
                    }
                }
            }
            break;
        case "menu":
            $layout = array(
                "ID" => 0
                , "prefix" => "menu"
            );
            if(check_function("process_vgallery_menu_child"))
                $frame_buffer = process_vgallery_menu_child(null, $settings_path, null, $layout);
            break;
        case "album":
            $layout = array(
                "ID" => 0
                , "prefix" => "menu"
            );            
        
            if(check_function("process_gallery_menu_child"))
                $frame_buffer = process_gallery_menu_child($settings_path, null, null, $layout);
            break;
        case "tag":
            if(check_function("process_landing_page")) {
                if(ffCommon_dirname($settings_path) == "/") {
                    $landing_path = $settings_path;
                    $landing_group = null;
                } else {
                    $landing_path = ffCommon_dirname($settings_path);
                    $landing_group = basename($settings_path);
                }

                $frame_buffer = process_landing_tag_content_by_type($landing_path, $landing_group);

                //$frame_buffer = $landingpage["content"];
            }        
        	break;
        case "block":
		default:     
			$arrSettingsPath = explode("/", trim($settings_path, "/")); 

			if(0) {
    			//da fare gestione schema per i moduli
    		} elseif(count($arrSettingsPath) > 1) {

				$arrVgalleryBlock = array(
    				"vgallery_name" => $arrSettingsPath[1]
    				, "settings_path" => "/" . implode("/", $arrSettingsPath)
    			); 

    			$layout = (check_function("get_layout_settings")
				            ? get_layout_by_block("vgallery", $arrVgalleryBlock["settings_path"])
				            : null
						);
	            $check_vgallery_dir = get_vgallery_is_dir(basename($arrVgalleryBlock["settings_path"]), ffCommon_dirname($arrVgalleryBlock["settings_path"]));    			
				if($check_vgallery_dir) {
					if(check_function("process_vgallery_thumb")) {
						$frame_buffer = process_vgallery_thumb(
			                $arrVgalleryBlock["settings_path"]
			                , "vgallery"
			                , array(
			                    "vgallery_name" => $arrVgalleryBlock["vgallery_name"]
			                    , "output" => "content"
			                    , "search" => $globals->search
			                    , "navigation" => $globals->navigation
			                    , "template_skip_hide" => true
			                    , "settings_thumb" => true
			                )
			                , $layout
			            );    		
					}
				} else {
					if(check_function("process_vgallery_view")) {
		                $frame_buffer = process_vgallery_view(
		                    $arrVgalleryBlock["settings_path"]
		                    , "vgallery"
		                    , array(	
		                        "vgallery_name" => $arrVgalleryBlock["vgallery_name"]
		                        , "output" => "content"
	                        	, "search" => $globals->search
	                            , "navigation" => $globals->navigation
	                            , "template_skip_hide" => true
		                    )
		                    , $layout
		                );	
					}
				}
			} else {
				check_function("system_get_sections");
				check_function("system_layer_gallery");

				$template = system_get_blocks(null, array(
					"blocks" => array(
						"name" => array(basename($settings_path) => true)
					)
				));

				$layouts = array_values($template["blocks"]);
				$layout = $layouts[0];

				//$main_section_params["js_custom_is_set"] = true;
				$main_section_params["search"] = $globals->search;
				$main_section_params["navigation"] = $globals->navigation;
				$main_section_params["user_path"] = $layout["db"]["real_path"];
				$main_section_params["settings_path"] = $layout["db"]["real_path"];
				$buffer = system_block_process($layout, $main_section_params);

				$main_section_params = $buffer["params"];
				$main_section_params["count_block"]++;



				$frame_buffer = $buffer["pre"] . $buffer["content"] . $buffer["post"];



			}
    }  

	if($frame_buffer) {
		$res_media_buffer = "";
		if (is_array($cm->oPage->page_css) && count($cm->oPage->page_css))
        {
        	foreach($cm->oPage->page_css AS $key => $value) {
        		if($value["async"] != $cm->isXHR())
        			continue;

        		$css_path = "";
        		if($value["path"] === null) 
        			$value["path"] = $cm->oPage->getThemePath();
        		
        		$css_path = $value["path"];
        		
        		if($value["file"])
        			$css_path .= "/" . $value["file"];
        			
        		if($css_path)
        			$res_media_buffer .= 'ff.injectCSS("' . $key . '", "' . $css_path . '");';
        		if($value["embed"])
        			$frame_buffer .= '<style type="' . $value["type"] . '">' . $value["embed"] . "</style>";
        	}
		}
 		if (is_array($cm->oPage->page_js) && count($cm->oPage->page_js))
        {
        	foreach($cm->oPage->page_js AS $key => $value) {
        		if($value["async"] != $cm->isXHR())
        			continue;

        		$js_path = "";
        		if($value["path"] === null) 
        			$value["path"] = $cm->oPage->getThemePath();
        		
        		$js_path = $value["path"];
        		
        		if($value["file"])
        			$js_path .= "/" . $value["file"];

        		if($js_path)
        			$res_media_buffer .= 'ff.pluginLoad("' . $key . '", "' . $js_path . '");';
        			
        		if($value["embed"])
        			$res_media_buffer .= $value["embed"];
        	}
		}
		if($res_media_buffer)
			$frame_buffer .= '<script type="text/javascript">' . $res_media_buffer . '</script>';    
	}
	if(!defined("DISABLE_CACHE") && $frame_buffer && check_function("system_set_cache_page")) {
		system_write_cache_page($globals->page["user_path"], $main_section_params["count_block"]);
		system_set_cache_page($frame_buffer);  
	}
  	
  	
  	return $frame_buffer;
  }
