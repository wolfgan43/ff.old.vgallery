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
function set_template_var($tpl, $vars = null, $prefix = "") {
    if($vars === null) {
        $globals = ffGlobals::getInstance("gallery");
        
        if(isset($globals->template)) {
            if(is_array($globals->template["vars"]) && count($globals->template["vars"])) {
                $vars = $globals->template["vars"];
            }
        } else {
            $vars = array();
        }
    }

    if(is_array($vars) && count($vars)) {
        foreach($vars AS $vars_key => $vars_value) {
            $tpl->set_var($vars_key, $vars_value);
            if(is_array($vars_value)) {
                if(count($vars_value)) {
                    $tpl = set_template_var($tpl, $vars_value, (strlen($prefix) ? $prefix . "_" : "") . $vars_key);
                }
            } else {
                $tpl->set_var($vars_key, $vars_value);

                if (strlen($vars_value)) {
                    $tpl->parse("Sect_" . (strpos($prefix, "_") === false ? "" : substr($prefix, 0, strpos($prefix, "_") + 1 )) . $vars_key, false);
                } else {
                    $tpl->set_var("Sect_" . (strpos($prefix, "_") === false ? "" : substr($prefix, 0, strpos($prefix, "_") + 1 )) . $vars_key, "");
                }
            }
        }
    }
    
    return $tpl;
}

function get_template_header($user_path, $admin_menu = null, $layout = null, &$tpl = null, $block = array()) {
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");

	if($layout !== null) {
		if(array_key_exists("unic_id", $layout))
			$unic_id = $layout["unic_id"];
		else
			$unic_id = $layout["prefix"] . $layout["ID"] . $layout["postfix"];

		$real_father = preg_replace('/[^a-zA-Z0-9]/', '', $unic_id);
	}

	if($tpl !== null) {
		$tpl->set_var("site_path", FF_SITE_PATH);
		$tpl->set_var("theme", $cm->oPage->getTheme());
		$tpl->set_var("theme_inset", THEME_INSET);
	    $tpl->set_var("domain_inset", DOMAIN_INSET);
	    $tpl->set_var("language_inset", LANGUAGE_INSET);	
		//$tpl->set_var("ret_url", $ret_url);
		//$tpl->set_var("encode_ret_url", urlencode($ret_url));

		if($layout !== null) {
		    $tpl->set_var("unic_id", $unic_id);
		    $tpl->set_var("unic_id_lower", strtolower($unic_id));
		    if(!isset($block["exclude"]["class"]["filename"]))
				$filename = ffCommon_url_rewrite(ffGetFilename($tpl->sTemplate));
		}
	}

	if($layout !== null) {
		//$block["class"] = array();
		//$block["properties"] = array();
		
		if(is_array($admin_menu) && count($admin_menu)) {
			$block["properties"]["admin"] = 'data-admin="'. get_admin_bar($admin_menu, VG_SITE_FRAME . $user_path) . '"';

		    //$serial_admin_menu = json_encode($admin_menu);
		    //$block["properties"]["admin"] = 'data-admin="'. FF_SITE_PATH . VG_SITE_FRAME . $user_path . "?sid=" . set_sid($serial_admin_menu, $admin_menu["admin"]["unic_name"]) . '"';
		}
		$block["class"]["block"] = "block";
		if($filename)
			$block["class"]["tpl"] = $filename;

		if(!isset($block["exclude"]["class"]["default"]) && is_array($layout["class"]))
			$block["class"] = array_replace($layout["class"], $block["class"]);   

		if((isset($layout["ajax"]) && $layout["ajax"]) 
			&& !$cm->isXHR()
		) {
			$ajax_base_path = cache_get_page_by_id("block");
			switch($layout["type"]) {
				case "ECOMMERCE":
					$serial_frame_url = FF_SITE_PATH . VG_SITE_FRAME . stripslash($globals->settings_path) . "?sid=" . $globals->ecommerce["sid"]; 
					
					$serial_frame_url = FF_SITE_PATH . $globals->locale["prefix"] . $ajax_base_path . "/" . $layout["smart_url"];  					
					break;
				case "VIRTUAL_GALLERY":
					if($layout["ajax_on_ready"] != "preload")
						$real_father .= "T";

					switch($layout["db"]["value"]) {
						case "anagraph":
							$ajax_base_path = cache_get_page_by_id("anagraph");
							break;
						case "gallery":
							$ajax_base_path = cache_get_page_by_id("album");
							break;
						default:
							//$ajax_base_path = cache_get_page_by_id("block");
					}
					//$serial_frame_url = FF_SITE_PATH . $ajax_base_path . "/" . $layout["value"] . $layout["params"]; //quello sopra e sotto si equivalgono
					
					//$serial_frame_url = FF_SITE_PATH . $globals->user_path;  
					//break;
				case "PUBLISHING":
					/* 
					$serial_frame_url = FF_SITE_PATH . cache_get_page_by_id("publish") . "/" . $layout["da trovare qualcosa per avere il nome reale dell'elemento"];
					
					break;*/
				default:
				   // $frame["sys"]["layouts"] = preg_replace('/[^a-zA-Z0-9]/', '', $unic_id);
				   // $frame["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
				   // $serial_frame = json_encode($frame);

					//$serial_frame_url = FF_SITE_PATH . VG_SITE_FRAME . stripslash($globals->settings_path) . "/" . set_sid($serial_frame, null, false, $layout["smart_url"]);  

					$serial_frame_url = FF_SITE_PATH . $globals->locale["prefix"] . $ajax_base_path . "/" . $layout["smart_url"];  
			}		    
			
 			$block["properties"]["ajax"] = 'data-ready="' . $layout["ajax_on_ready"] . '" data-event="' . $layout["ajax_on_event"] . '" data-src="' . $serial_frame_url . '"';
 			//if($layout["ajax_on_ready"] != "preload" && $layout["ajax_on_ready"] != "inview")
               // $block["class"]["ajax"] = "ajaxcontent";
		}
        $str_id = "";
		if(is_array($block["class"]))
			$str_class =  ' class="' . implode(" " , array_unique(explode(" ", implode(" " , array_filter($block["class"]))))) . '"';
		if(is_array($block["properties"]))
			$str_properties =  " " . implode(" ", array_filter($block["properties"]));
		if(strlen($real_father))
			$str_id = 'id="' . $real_father . '"' . $str_id;
		//$block["class"]["custom"] = $custom_class;

		$block["tpl"]["pre"] = '<div ' . $str_id . $str_class . $str_properties . '>';
		$block["tpl"]["post"] = '</div>';

		if($layout["wrap"]) {
			$block["tpl"]["pre"] .= '<div class="' . $layout["wrap"] . '">';
			$block["tpl"]["post"] .= '</div>';
		}		
	}

	return $block;    
}

function get_admin_bar($arrAdmin = null, $url = null) {
	$cm = cm::getInstance();
	/*static $js_isset = false;
	if(!$js_isset) {
		$cm->oPage->tplAddJs("ff.cms.bar.block");
		//todo: da togliere e metter nella request asincrona

		$js_isset = true;
	}*/
	$sid = set_sid(json_encode($arrAdmin), $arrAdmin["admin"]["unic_name"]);
	if(is_array($arrAdmin) && count($arrAdmin)) {
		if($url)
			return  FF_SITE_PATH . $url . "?sid=" . $sid;
		else
			return $sid;
	}
}
