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
 $globals = ffGlobals::getInstance("gallery");
 
if($_REQUEST["out"] == "html") {
	$cm->oPage->use_own_form = true;
	$cm->oPage->template_file = "ffPage.html";
}
//ffErrorHandler::raise("sdf", E_USER_ERROR, get_defined_vars());

if($cm->oPage->isXHR() && $cm->oPage->getXHRComponent()) {
	$cm->oPage->process();
	exit;
}
/*
if(is_array($globals->params) && count($globals->params)) {
    foreach($globals->params AS $params_key => $params_value) {
        ${$params_key} = $params_value;
    }
}*/

$old_request_uri = $_SERVER["REQUEST_URI"];

$new_request_uri = $_SERVER["REQUEST_URI"];
$new_request_uri = str_replace(VG_SITE_FRAME, "", $new_request_uri);
if(strpos($new_request_uri, "?") !== false)
	$new_request_uri = substr($new_request_uri, 0, strpos($new_request_uri, "?"));

$is_framework = false;	

/*if(strlen($globals->frame_smart_url)) {
	$settings_path = substr($settings_path, 0, strpos($settings_path, "/" . $globals->frame_smart_url));
	$new_request_uri = substr($new_request_uri, 0, strpos($new_request_uri, "/" . $globals->frame_smart_url));
}*/

//$new_request_uri = str_replace("sid=" . $_REQUEST["sid"] . "&", "", $new_request_uri);
//$new_request_uri = str_replace("?sid=" . $_REQUEST["sid"], "", $new_request_uri);

if(array_key_exists("sid", $_REQUEST) && strlen($_REQUEST["sid"])) {
	$source_sid = str_replace("\\\"", "\"", $_REQUEST["sid"]);
	$sid = get_sid($source_sid, null, true);
	if(array_key_exists("value", $sid)) {
		$params = json_decode($sid["value"], true);

		$sys = $params["sys"];
		$admin = $params["admin"];
	}
}

$_SERVER["REQUEST_URI"] = $new_request_uri;

if($sys["ret_url"] == "")
	$sys["ret_url"] = "/";

if($_SERVER["REQUEST_URI"] == "")
	$_SERVER["REQUEST_URI"] = $sys["ret_url"];

if(is_array($sys["module"]) && count($sys["module"])) 
{
	$is_framework = true;
	
	$res = array();
	$res["content"] = "";
	
	foreach ($sys["module"] AS $module_key => $module_value) {
		$tmp_buffer = "";
		$unic_id = $module_value["layout"]["prefix"] . $module_value["layout"]["ID"];

	    $_SERVER["REQUEST_URI"] = $old_request_uri;
	    $cm->oPage->process(false);
	    $_SERVER["REQUEST_URI"] = $new_request_uri;
		if(isset($cm->oPage->components_buffer[$unic_id])) {
		    if(is_array($cm->oPage->components_buffer[$unic_id])) {
		        $tmp_buffer = $cm->oPage->components_buffer[$unic_id]["headers"] . $cm->oPage->components_buffer[$unic_id]["html"] . $cm->oPage->components_buffer[$unic_id]["footers"];
		    } else {
		        $res["content"] = $cm->oPage->components_buffer[$unic_id];
		    }
		    //unset($cm->oPage->components_buffer[$unic_id]);
		}
		if(!strlen($tmp_buffer) && isset($cm->oPage->contents[$unic_id])) {
		    $tmp_buffer = $cm->oPage->contents[$unic_id]["data"];
		    //unset($cm->oPage->contents[$unic_id]);
		}
		if(strlen($tmp_buffer)) 
		{
			/**
			* Admin Father Bar
			*/                    
            if(AREA_MODULES_SHOW_MODIFY) {
                $admin_menu["admin"]["unic_name"] = $module_value["layout"]["prefix"] . $module_value["layout"]["ID"];
                $admin_menu["admin"]["title"] = $layout_value["title"] . ": " . $user_path;
                $admin_menu["admin"]["class"] = $layout_value["type_class"];
                $admin_menu["admin"]["group"] = $layout_value["type_group"];
                $admin_menu["admin"]["adddir"] = "";
                $admin_menu["admin"]["addnew"] = "";
                $admin_menu["admin"]["modify"] = "";
                $admin_menu["admin"]["delete"] = "";
                $admin_menu["admin"]["extra"] = "";

                $admin_menu["admin"]["ecommerce"] = "";
                $admin_menu["admin"]["layout"] = "";
                $admin_menu["admin"]["setting"] = ""; //$layout_value["type"];
                if(MODULE_SHOW_CONFIG) {
                    $admin_menu["admin"]["module"]["value"] = strtolower($module_key);
                    $admin_menu["admin"]["module"]["params"] = $module_value["layout"]["ID"];

                } 
	            if(is_file(FF_DISK_PATH . VG_ADDONS_PATH . "/" . strtolower($module_key) . "/fields." . FF_PHP_EXT))
                    $admin_menu["admin"]["module"]["extra"] = get_path_by_rule("addons") . "/". strtolower($module_key) . "/" .$module_value["layout"]["ID"] . "/fields";
	            else
                    $admin_menu["admin"]["module"]["extra"] = "";

                $admin_menu["sys"]["path"] = $settings_path;
                $admin_menu["sys"]["location"] = $layout_value["location"];
                $admin_menu["sys"]["type"] = "admin_toolbar";
                $admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
            }	

			/**
			* Process Block Header
			*/	
			if(check_function("set_template_var"))
				$block = get_template_header($settings_path, $admin_menu, $layout_value);
			
			$tmp_buffer = $block["tpl"]["pre"] . $tmp_buffer .  $block["tpl"]["post"];
		}
		
		$res["content"] .= $tmp_buffer;
	}
	if(strlen($res["content"]))
		$frame_buffer = $cm->oPage->output_buffer["headers"] . $res["content"] . $cm->oPage->output_buffer["footers"];
    
} 
elseif(isset($sys["type"])) 
{
    if(isset($sys["settings_path"])) {
    	$settings_path = urldecode($sys["settings_path"]);
	} else {
    	$settings_path = urldecode($sys["path"]);
	}
    if($settings_path == "")
        $settings_path = "/";

    $globals->settings_path = $settings_path;
    
    $location = urldecode($sys["location"]);
	//$ret_url = $sys["ret_url"]; il redirect non e realistico. Meglio farlo gestire dal javascript
	$ret_url = "";
	
    switch(strtoupper($sys["type"])) {
        case "ADMIN_MENU":
        	if(check_function("process_admin_toolbar"))
                $frame_buffer = process_admin_toolbar($settings_path, $admin["theme"], $admin["sections"], $admin["css"], $admin["js"], $admin["international"], $admin["seo"]);
            break;
        case "ADMIN_POPUP":
            if(check_function("process_admin_menu"))
                $frame_buffer = process_admin_menu(${str_replace("_popup", "", $sys["type"])}, "popup", $settings_path, $location, $ret_url);
            break;
        case "ADMIN_TOOLBAR":
            if(check_function("process_admin_menu"))
                $frame_buffer = process_admin_menu(${str_replace("_toolbar", "", $sys["type"])}, "toolbar", $settings_path, $location, $ret_url);
            break;
		case "GALLERY_MENU_CHILD":       //da eliminare
			if(check_function("process_gallery_menu_child"))
				$frame_buffer = process_gallery_menu_child($user_path, $sys["source_user_path"], $sys["real_user_path"], $sys["layout"], ($sys["is_absolute"] ? FF_DISK_PATH : DISK_UPDIR), $sys["skip_control"]);
			break;
		case "VGALLERY_MENU_CHILD":
			if(check_function("process_vgallery_menu_child"))
				$frame_buffer = process_vgallery_menu_child(null, $sys["real_user_path"], $sys["source_user_path"], $sys["layout"]);
			break;
        default:
    }    
}  
elseif(strlen($settings_path)) 
{ 
	$db = ffDB_Sql::factory();

	$layout = null;
	$request_type = null;
	
	$cm->oPage->page_js = array();
	$cm->oPage->page_css = array();

	/*if(substr_count($settings_path, "/") > 1) {
	    $arrSettingsPath = explode("/", trim($settings_path, "/")); 
		$request_type = $arrSettingsPath[0];
		unset($arrSettingsPath[0]);

		$settings_path = "/" . implode("/", $arrSettingsPath); 
	}*/

    switch($globals->page["name"]) {
        case "anagraph": 
        	$check_vgallery_dir = get_vgallery_is_dir(basename($settings_path), "/anagraph" . ffCommon_dirname($settings_path));    			

	        /*if(1 || $layout["db"]["params"] > 0) {
	            $arrAvailablePath = explode("/", $settings_path);
	            if(count($arrAvailablePath) > 2) {
	                $check_vgallery_dir = false; 
	            } else {
	                $check_vgallery_dir = true;
	            }
			} else {
				if($settings_path)
					$check_vgallery_dir = false;
				else
					$check_vgallery_dir = true;
			}*/
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
            if(check_function("process_vgallery_thumb")) {
            	$arrMap = explode("_", basename($settings_path));
                if(strlen($arrMap[0]))
                {
                    $sSQL = "SELECT module_maps.description_limit
                                    , module_maps.contest
                                FROM module_maps
                                WHERE name = " . $db->toSql($arrMap[0], "Text");
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
			if(0) {
    			//da fare gestione schema per i moduli
    		} else {
			    $arrSettingsPath = explode("/", trim($settings_path, "/")); 

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
			}        
        	break;
		default:          
			//$globals->cache["data_blocks"] = array(); // x cache_page
			
			if(!$request_type && basename($settings_path)) {
				if(check_function("query_layout"))
					$sSQL = query_layout_by_smart_url(basename($settings_path));
					
				$db->query($sSQL);
				if($db->nextRecord() && check_function("system_layer_gallery")) {
					//do {
						$ID_layout 														= $db->getField("ID", "Number", true);
						$type 															= $db->getField("type", "Text", true);
						$layout["ID"] 													= $ID_layout;
						$layout["ID_type"] 												= $db->getField("ID_type", "Number", true);
						$layout["ID_section"] 											= null;
						$layout["prefix"] 												= "L";
						$layout["smart_url"] 											= $db->getField("smart_url", "Text", true);
						$layout["title"] 												= $db->getField("name", "Text", true);
						$layout["type_class"] 											= $db->getField("type_class", "Text", true);
						$layout["type_group"] 											= $db->getField("type_group", "Text", true);
						$layout["multi_id"] 											= $db->getField("multi_id", "Text", true);
						$layout["type"] 												= $type;
						$layout["location"] 											= $db->getField("location", "Text", true);
						$layout["template"] 											= $db->getField("template", "Text", true);
						$layout["template_detail"] 										= $db->getField("template_detail", "Text", true);
						$layout["tpl_path"] 											= $db->getField("tpl_path", "Text", true);
						$layout["value"] 												= $db->getField("value", "Text", true);
						$layout["params"] 												= $db->getField("params", "Text", true);
						$layout["last_update"] 											= $db->getField("last_update", "Text", true);
						$layout["frequency"] 											= $db->getField("frequency", "Text", true);
						$layout["use_in_content"] 										= $db->getField("use_in_content", "Number", true);
						$layout["ajax"] 												= $db->getField("use_ajax", "Number", true);
						$layout["ajax_on_ready"] 										= $db->getField("ajax_on_ready", "Text", true);
						$layout["ajax_on_event"] 										= $db->getField("ajax_on_event", "Text", true);
						$layout["content"] 												= "";
						$layout["settings"] 											= null;
						$layout["plugins"] 												= $db->getField("js_lib", "Text", true);
						$layout["js"] 													= $db->getField("js", "Text", true);
						$layout["css"] 													= $db->getField("css", "Text", true);
						$layout["visible"] 												= $db->getField("visible", "Text", true);
						if($layout["visible"]) {
							if(check_function("get_layout_settings"))
								$layout["settings"] = get_layout_settings($ID_layout, $type);
							$layout["ajax"] = false;
							$layout["db"]["value"] = $layout["value"];
							$layout["db"]["params"] = $layout["params"];
							$layout["db"]["real_path"] = $db->getField("real_path", "Text", true);
						}
						//$main_section_params["js_custom_is_set"] = true;
						
						$main_section_params["search"] = $globals->search;
						$main_section_params["navigation"] = $globals->navigation;

						$main_section_params["user_path"] = $layout["db"]["real_path"];
						$main_section_params["settings_path"] = $layout["db"]["real_path"];						
						
						$buffer = system_block_process($layout, $main_section_params);

						$main_section_params = $buffer["params"];
						$main_section_params["count_block"]++;
						
						$frame_buffer = $buffer["default"];
					//} while($db->nextRecord());
				}
			}
    }
    
	// da eliminare prob o sistemare in alto
	if(is_array($layouts) && count($layouts)) {
		//$globals->cache["data_blocks"] = array(); // x cache_page

		if(check_function("system_layer_gallery")) {
			$main_section_params["count_block"] = 0;
			//$main_section_params["js_custom_is_set"] = true;
			//$main_section_params["js_custom_module_is_set"] = false;
			
			$main_section_params["search"] = $globals->search;
			$main_section_params["navigation"] = $globals->navigation;
			
			//$_SERVER["REQUEST_URI"] = "/frame" . $old_request_uri;
			$cm->oPage->page_js = array();
			$cm->oPage->page_css = array();
			//ffErrorHandler::raise("ASD", E_USER_WARNING, $cm->oPage, get_defined_vars());
			//$cm->oPage->process(false);
			//$_SERVER["REQUEST_URI"] = $new_request_uri;

			foreach($layouts AS $layout_key => $layout_value) {
				$res = array();
				$res["content"] = "";

				$buffer = system_block_process($layout_value, $main_section_params);

				$main_section_params = $buffer["params"];
				$main_section_params["count_block"]++;
				/*if(isset($buffer["data_blocks"]) && is_array($buffer["data_blocks"]) && count($buffer["data_blocks"])) {
					$cache_page["data_blocks"] = array_merge($cache_page["data_blocks"], $buffer["data_blocks"]);
				} */
				
				$layout_value["content"] = $buffer["default"];
				$frame_buffer .= $layout_value["content"];
			}
			reset($layouts);
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
}

// da sistemare. Quando il componente viene recuperato senza struttura html fare in modo che vada o nel secondo caso senza form che lo generi
if(strlen($globals->frame_smart_url)) {
	if($_REQUEST["out"] == "html") {
		if($is_framework) {
			$cm->oPage->use_own_js = true;

			$js_struct = '
				ff.load("ff.ajax", function() {
					if(ff.fn.frame === undefined) {
						ff.fn.frame = function (params, data) {
							if(params.component !== undefined 
								&& data.html !== undefined 
								&& jQuery(data.html).is("#" + params.component)
							) {
								ff.cms.widgetInit("#" + params.component, false);
							}
						};
							
						ff.pluginAddInit("ff.ajax", function () {
							ff.ajax.addEvent({
								"event_name" : "onUpdatedContent"
								, "func_name" : ff.fn.frame
							});
						});			
					}			
				});';
			$cm->oPage->tplAddJs("ff.cms.frame", array(
				"embed" => $js_struct
			));				
		} else {
			$cm->oPage->page_css = array();
			$cm->oPage->page_js = array();
			$cm->oPage->addContent(preg_replace("/\n\s*/", "\n", $frame_buffer));
		}
		//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
		$frame_buffer =  $cm->oPage->process(false);	
	} else {
		$frame_buffer =  $js_struct . preg_replace("/\n\s*/", "\n", $frame_buffer);	
	}
} else {
	$frame_buffer = preg_replace("/\n\s*/", "\n", $frame_buffer);	
}

//if(strlen($globals->frame_smart_url) && check_function("system_set_cache_sid")) {
//	system_set_cache_sid($globals->sid, $frame_buffer, LANGUAGE_INSET);
//}

if(!defined("DISABLE_CACHE") && $frame_buffer && check_function("system_set_cache_page")) {
	//system_write_cache_page($globals->user_path, $main_section_params["count_block"]);
	system_set_cache_page($frame_buffer, $main_section_params["count_block"]);
} elseif($_REQUEST["XHR_data"]) {
	header("Content-type: text/javascript");

	$expires = 60 * 60 * 24 * 7; 
	header("Content-Length: " . strlen($frame_buffer));	
	
	die(ffCommon_jsonenc(array("injectid" => $_REQUEST["XHR_data"], "refresh" => true, "headers" => "", "html" => $frame_buffer, "footers" => "", "rows" => "1"), true));
} else {
	cache_send_header_content(false, false, false, false, strlen($frame_buffer), false);
	
	if(!$frame_buffer) 
		http_response_code(($cm->oPage->isXHR() ? 500 : 404));

	echo $frame_buffer;
}

exit;