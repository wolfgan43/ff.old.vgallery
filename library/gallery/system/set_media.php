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
function system_set_media($oPage, $setting_path, $admin = false, $include_media = false) 
{
    $globals = ffGlobals::getInstance("gallery");

	if($oPage->theme == FRONTEND_THEME) {
		if($admin) {
	        $oPage->widgetLoad("dialog");
	        $oPage->widgets["dialog"]->process(
	             "dialogManage"
	             , array(
	                "tpl_id" => null
	                //"name" => "myTitle"
	                , "url" => ""
	                , "title" => ""
	                , "callback" => ""
	                , "class" => ""
	                , "params" => array(
	                )
	                , "resizable" => true
	                , "position" => "center"
	                , "draggable" => true
	                , "doredirects" => true
	                , "responsive" => true
	                , "unic" => true
	                , "dialogClass" => cm_getClassByFrameworkCss("window-large", "dialog")
	            )
	            , $oPage
	        );
			$oPage->tplAddJs("ff.cms.bar");
		} else {
			$oPage->tplAddJs("ff.cms");
		}
		
		$oPage->tplAddcss("ff.cms.reset");
	}
	
	if($include_media)
		system_set_media_cascading();
}

function system_set_media_libs() 
{
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");
	$glob_libs = ffGlobals::getInstance("__ffTheme_libs__");

	if(is_array($globals->js["library"]) && count($globals->js["library"])) {
		foreach($globals->js["library"] AS $js_name => $js_enable) {
			if(!ffIsset($glob_libs->libs, "jquery/plugins/jquery.plugins." . $js_name)) {
				cm_loadlibs($glob_libs->libs, FF_THEME_DISK_PATH . "/library/plugins/jquery." . $js_name, "plugins/jquery.plugins." . $js_name, "jquery", false, true, true);
				if(is_array($glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name])) {
					$cm->oPage->libsExtend($glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name]);	
					$cm->oPage->tplAddJs("jquery.plugins." . $js_name);
				}
			}
		}
	}	

	if(is_array($globals->js["request"]) && count($globals->js["request"])) {
		foreach($globals->js["request"] AS $js_name => $js_enable) {
			if(!ffIsset($glob_libs->libs, "jquery/plugins/jquery.plugins." . $js_name)) {
				cm_loadlibs($glob_libs->libs, FF_THEME_DISK_PATH . "/library/plugins/jquery." . $js_name, "plugins/jquery.plugins." . $js_name, "jquery", false, true, true);
			}
			
			if(is_array($glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name])) {
				$glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name]["jquery"]["all"]["js_defs"]["plugins"]["js_defs"][$js_name]["js_defs"]["observe"]["file"] = "jquery." . $js_name . ".observe.js";
				if($globals->js["frontend"]["/" . "jquery." . $js_name . ".observe.js"]) {
					$glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name]["jquery"]["all"]["js_defs"]["plugins"]["js_defs"][$js_name]["js_defs"]["observe"]["path"] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/javascript";
				} else {
					$glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name]["jquery"]["all"]["js_defs"]["plugins"]["js_defs"][$js_name]["js_defs"]["observe"]["path"] = FF_THEME_DIR . "/" . THEME_INSET . "/javascript/plugins/jquery." . $js_name;
				}
			} else {
				cm_loadlibs($glob_libs->libs, FF_THEME_DISK_PATH . "/" . THEME_INSET . "/javascript/plugins/jquery." . $js_name, "plugins/jquery.plugins." . $js_name, "jquery", false, true);			
			}

			if(is_array($glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name])) {
				$cm->oPage->libsExtend($glob_libs->libs["jquery/plugins/jquery.plugins." . $js_name]);			
				$cm->oPage->tplAddJs("jquery.plugins." . $js_name . ".observe");
			} else {
				$plugin["file"] = "jquery." . $js_name . ".observe.js";
				if($globals->js["frontend"]["/" . "jquery." . $js_name . ".observe.js"]) {
					$plugin["path"] = FF_THEME_DIR . "/" . FRONTEND_THEME . "/javascript/jquery." . $js_name;
				} else {
					$plugin["path"] = FF_THEME_DIR . "/" . THEME_INSET . "/javascript/plugins/jquery." . $js_name;
				}
				$cm->oPage->tplAddJs("jquery." . $js_name, $plugin);
			}
		}
	}

	if(is_array($globals->js["embed"]) && count($globals->js["embed"])) {
		foreach($globals->js["embed"] AS $js_name => $js_embed) {
			$cm->oPage->tplAddJs($js_name, array(
				"embed" => $js_embed
			));
		}
	}	
	
	if(is_array($globals->css["embed"]) && count($globals->css["embed"])) {
		foreach($globals->css["embed"] AS $css_name => $css_embed) {
			$cm->oPage->tplAddCss($css_name, array(
				"embed" => $css_embed
			));
		}
	}		
}

function system_set_media_cascading($return = false) {
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");

    $res = "";
    $above_the_fold = "";
	$setting_path = $globals->settings_path;
	$browser = $cm->oPage->getBrowser();

	$base_path = FF_THEME_DIR . "/" . FRONTEND_THEME;
	$browser_name = strtolower($browser["name"]);
	$lang_name = strtolower(LANGUAGE_INSET) ;
	$pathname = str_replace("/", "_", trim($setting_path, "/"));
	$skip_cache = (defined("DISABLE_CACHE")
					? true
					: $globals->cache["enabled"] === false
						? true
						: false
				);
	
	$arrSettingsPath = array();
	if($setting_path != ffCommon_dirname($setting_path)) {
		while($setting_path != "/") {
			$arrSettingsPath[] = str_replace("/", "_", trim($setting_path, "/"));

			$setting_path = ffCommon_dirname($setting_path);
		};
		krsort($arrSettingsPath);
	}
	
	if(!$return) {
		/**
		* Load Media Base
		*/
		if($globals->settings_path == "/")
			array_unshift($arrSettingsPath, "home");
			
		array_unshift($arrSettingsPath, "root");
	}

	/**
	* Load Js by User Request
	*/
	system_set_media_libs();

	if(is_array($arrSettingsPath) && count($arrSettingsPath)) {
		foreach($arrSettingsPath AS $filename) {
			if($globals->html["pages"]["/" . $filename . ".html"])
				$globals->page["template"]["path"] = "/" . $filename . ".html";
		
			if(!$globals->media_exception["js"][$filename]) {
				if($globals->js["frontend"]["/events/" . $filename . ".js"]) {
					$js_embed = system_set_js_event($base_path . "/javascript/events", $filename . ".js");
					if($js_embed) {
						$js_key = "event_" . $filename;
						if($return) {
							$res .= $js_embed;
						} else {
							$cm->oPage->tplAddJs($js_key, array(
        						"embed" => $js_embed
					        ));					
						}
					}
				}

				// JS di Livello
				$js_key = $filename;
				if($globals->js["frontend"]["/" . $filename . ".js"]) {
					$cm->oPage->tplAddJs($js_key, array(
							"path" => $base_path . "/javascript"
							, "file" => $filename . ".js"
						));

					if($skip_cache)
            			$cm->oPage->override_js[$js_key] = $base_path . "/javascript/" . $filename . ".js" . "?" . filemtime(FF_DISK_PATH . $base_path . "/javascript/" . $filename . ".js");
				}

				// JS di Livello di lingua
				$js_key = $filename . "-" . $lang_name;
				if($globals->js["frontend"]["/" . $filename . "-" . $lang_name . ".js"]) {
					$cm->oPage->tplAddJs($js_key, array(
							"path" => $base_path . "/javascript"
							, "file" => $filename . "-" . $lang_name . ".js"
						));

					if($skip_cache)
            			$cm->oPage->override_js[$js_key] = $base_path . "/javascript/" . $filename . "-" . $lang_name . ".js" . "?" . filemtime(FF_DISK_PATH . $base_path . "/javascript/" . $filename . "-" . $lang_name . ".js");
				} 
				
				
				// JS di Livello specifico per Browser
				$js_key = $browser_name . "_" . $filename;
				if($globals->js["frontend"]["/" . $browser_name . "/" . $filename . ".js"]) {
					$cm->oPage->tplAddJs($js_key, array(
							"path" => $base_path . "/javascript/" . $browser_name
							, "file" => $filename . ".js"
						));

					if($skip_cache)
            			$cm->oPage->override_js[$js_key] = $base_path . "/javascript/" . $browser_name . "/" . $filename . ".js" . "?" . filemtime(FF_DISK_PATH . $base_path . "/javascript/" . $browser_name . "/" . $filename . ".js");
				}
				
				// JS di Livello di lingua specifico per Browser
				$js_key = $browser_name . "_" . $filename . "-" . $lang_name;
				if($globals->js["frontend"]["/" . $browser_name . "/" . $filename . "-" . $lang_name . ".js"]) {
					$cm->oPage->tplAddJs($js_key, array(
							"path" => $base_path . "/javascript/" . $browser_name
							, "file" => $filename . "-" . $lang_name . ".js"
						));

					if($skip_cache)
            			$cm->oPage->override_js[$js_key] = $base_path . "/javascript/" . $browser_name . "/" . $filename . "-" . $lang_name . ".js" . "?" . filemtime(FF_DISK_PATH . $base_path . "/javascript/" . $browser_name . "/" . $filename . "-" . $lang_name . ".js");
				} 
			}
			
			if(!$return && !$above_the_fold && $globals->css["frontend"]["/above-the-fold_" . $filename . ".css"])
				$above_the_fold = "above-the-fold_" . $filename . ".css";
			
			if(!$globals->media_exception["css"][$filename]) {
				// CSS di Livello
				$css_key = $filename;
				if($globals->css["frontend"]["/" . $filename . ".css"]) {
					if($return) {
						$res .= "ff.injectCSS('" . $css_key . "', '" . FF_SITE_PATH . $base_path . "/css/" . $filename . ".css'); ";
					} else {				
						$cm->oPage->tplAddCss($css_key, array(
								"path" => $base_path . "/css"
								, "file" => $filename . ".css"
							));

						if($skip_cache)
            				$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $filename . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $filename . ".css");
					}
				}
				// CSS di Livello di lingua
				$css_key = $filename . "-" . $lang_name;
				if($globals->css["frontend"]["/" . $filename . "-" . $lang_name . ".css"]) {
					$cm->oPage->tplAddCss($css_key, array(
							"path" => $base_path . "/css"
							, "file" => $filename . "-" . $lang_name . ".css"
						));

					if($skip_cache)
            			$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $filename . "-" . $lang_name . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $filename . "-" . $lang_name . ".css");
				} 

				// CSS di Livello specifico per Browser
				$css_key = $browser_name . "_" . $filename;
				if($globals->css["frontend"]["/" . $browser_name. "/" . $filename . ".css"]) {
					$cm->oPage->tplAddCss($css_key, array(
							"path" => $base_path . "/css/" . $browser_name
							, "file" => $filename . ".css"
						));

					if($skip_cache)
            			$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $browser_name . "/" . $filename . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $browser_name . "/". $filename . ".css");
				}
				// CSS di Livello di lingua specifico per Browser
				$css_key = $browser_name . "_" . $filename . "-" . $lang_name;
				if($globals->css["frontend"]["/" . $browser_name . "/" . $filename . "-" . $lang_name . ".css"]) {
					$cm->oPage->tplAddCss($css_key, array(
							"path" => $base_path . "/css/" . $browser_name
							, "file" => $filename . "-" . $lang_name . ".css"
						));

					if($skip_cache)
            			$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $browser_name . "/" . $filename . "-" . $lang_name . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $browser_name . "/". $filename . "-" . $lang_name . ".css");
				} 				
				
				// CSS di Livello per la stampa
				$css_key = $filename . "-print";
				if($globals->css["frontend"]["/" . $filename . "-print" . ".css"]) {
					$cm->oPage->tplAddCss($css_key, array(
							"path" => $base_path . "/css"
							, "file" => $filename . "-print" . ".css"
							, "css_media" => "print"
						));

					if($skip_cache)
            			$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $filename . "-print" . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $filename . "-print" . ".css");
				}
				// CSS di Livello di lingua per la stampa
				$css_key = $filename . "-print-" . $lang_name;
				if($globals->css["frontend"]["/" . $filename . "-print-" . $lang_name . ".css"]) {
					$cm->oPage->tplAddCss($css_key, array(
							"path" => $base_path . "/css"
							, "file" => $filename . "-print-" . $lang_name . ".css"
							, "css_media" => "print"
						));

					if($skip_cache)
            			$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $filename . "-print-" . $lang_name . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $filename . "-print-" . $lang_name . ".css");
				} 
				
				// CSS di Livello per la stampa specifico per Browser
				$css_key = $browser_name . "_" . $filename . "-print";
				if($globals->css["frontend"]["/" . $browser_name . "/" . $filename . "-print" . ".css"]) {
					$cm->oPage->tplAddCss($css_key, array(
							"path" => $base_path . "/css/" . $browser_name
							, "file" => $filename . "-print" . ".css"
							, "css_media" => "print"
						));

					if($skip_cache)
            			$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $browser_name . "/" . $filename . "-print" . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $browser_name . "/" . $filename . "-print" . ".css");
				}
				// CSS di Livello di lingua per la stampa specifico per Browser
				$css_key = $browser_name . "_" . $filename . "-print-" . $lang_name;
				if($globals->css["frontend"]["/" . $browser_name . "/" . $filename . "-print-" . $lang_name . ".css"]) {
					$cm->oPage->tplAddCss($css_key, array(
							"path" => $base_path . "/css/" . $browser_name
							, "file" => $filename . "-print-" . $lang_name . ".css"
							, "css_media" => "print"
						));

					if($skip_cache)
            			$cm->oPage->override_css[$css_key] = $base_path . "/css/" . $browser_name . "/" . $filename . "-print-" . $lang_name . ".css" . "?" . filemtime(FF_DISK_PATH . $base_path . "/css/" . $browser_name . "/" . $filename . "-print-" . $lang_name . ".css");
				}				
			}			
		}
	}
	
	if($return) {
		if (is_array($cm->oPage->page_css) && count($cm->oPage->page_css))
        {
        	foreach($cm->oPage->page_css AS $priority => $css) {
        		foreach($css AS $key => $value) {
        			///if($value["async"] != $cm->isXHR())
        			//	continue;

        			$css_path = "";
        			if($value["path"] === null) 
        				$value["path"] = $cm->oPage->getThemePath();
        			
        			$css_path = $value["path"];
        			
        			if($value["file"]) {
        				$css_path .= "/" . $value["file"];
        				
        				$res .= '<link href="' . $css_path .'" rel="stylesheet" type="text/css" />';
					}
        			if($value["embed"])
        				$res .= '<style type="' . $value["type"] . '">' . $value["embed"] . "</style>";
				}
        	}
		}

 		if (is_array($cm->oPage->page_js) && count($cm->oPage->page_js))
        {
        	$loaded_libs = array();
        	foreach($cm->oPage->page_js AS $priority => $js) {
        		foreach($js AS $key => $value) {
        			//if($value["async"] != $cm->isXHR())
        			//	continue;

        			$js_path = "";
        			if($value["path"] === null) 
        				$value["path"] = $cm->oPage->getThemePath();
        			
        			$js_path = $value["path"];
        			
        			if($value["file"]) {
        				$js_path .= "/" . $value["file"];

        				$res .= '<script src="' . $js_path . '"></script>';
					}        				
        			if($value["embed"])
        				$res .= '<script>' . $value["embed"] . '</script>';
        				
        			$loaded_libs[$key] = 'ff.libSet("js", "' . $key . '");';
				}
        	}
        	if(count($loaded_libs)) {
        		$res = '<script>' . implode(" ", $loaded_libs) . '</script>' . $res;
        					

        	}
		}	
	
	} else {
		if(!$above_the_fold && $globals->css["frontend"]["/above-the-fold.css"])
			$above_the_fold = "above-the-fold.css";

		if($above_the_fold) {
			$cm->oPage->above_the_fold = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/css/" . $above_the_fold;
		}
	
	}
	
	if($globals->page["template"]["path"])
	{
		$tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/pages" . ffCommon_dirname($globals->page["template"]["path"]));
		$tpl->load_file(basename($globals->page["template"]["path"]), "main");
	    
	    if(is_array($tpl->DVars) && count(is_array($tpl->DVars))) {
		    if(check_function("system_get_sections"))
		        $block_type = system_get_block_type();	

	    	foreach($tpl->DVars AS $tpl_var => $tpl_ignore)
	    	{
	    		$arrVar = explode(":", $tpl_var, 2);
	    		$ID_var = substr($arrVar[0], 1);

	    		switch(substr($arrVar[0], 0, 1)) {
	    			case "L":
	    				$globals->page["template"]["blocks"]["key"][$ID_var] = $ID_var;
	    				$globals->page["template"]["blocks"]["vars"][$arrVar[0]] = $ID_var;
	    				$globals->page["template"]["found"][$arrVar[0]] = "blocks";
	    				break;
	    			case "S":
	    				$globals->page["template"]["sections"]["key"][$ID_var] = $ID_var;
	    				$globals->page["template"]["sections"]["vars"][$arrVar[0]] = $ID_var;
	    				$globals->page["template"]["found"][$arrVar[0]] = "sections";
	    				break;
	    			case "R":
	    				$globals->page["template"]["layers"]["key"][$ID_var] = $ID_var;
	    				$globals->page["template"]["layers"]["vars"][$arrVar[0]] = $ID_var;
	    				$globals->page["template"]["found"][$arrVar[0]] = "layers";
	    				break;
	    			default:
	    				$arrVarBlock = explode("-", $tpl_var, 2);

	    				if($block_type[$arrVarBlock[0]]) {
	    					$globals->page["template"]["blocks"]["name"][$tpl_var] = "";
						} elseif($tpl_var == "contents") {
							$globals->page["template"]["container"]["content"] = true;
						} else {
							//$arrVar = explode(":", $tpl_var, 2);
	    					$globals->page["template"]["unknown"][$tpl_var] = "";
						}
	    		}
	    	}
		}
	}	
	return $res;
}


function system_set_js_event($js_path, $js_name) {
	$cm = cm::getInstance();

    $js_key = ffGetFilename($js_path);
    $js_content = file_get_contents($js_path . "/" . $js_name);
/*    if($js_content === false) {
		$cm->oPage->tplAddJs($js_key, array(
			"path" => $js_path
			, "file" => $js_name
		));
    } else {
*/
	if($js_content) {
        if(substr($js_key, strrpos($js_key, "_") + 1) == "modify") {
            $type_event = "ffRecord";
        } else {
            $type_event = "ffGrid";
        }

        $js_content = '
            if(!jQuery.isFunction("ff.fn.' . $js_key . '")) {
                ff.fn.' . $js_key . ' = function (params, data) {
                    if(params === undefined && data === undefined) {
                        ' . $js_content . '
                    } else {
                        if(params.component !== undefined && ff.struct.get(params.component).type == "' . $type_event . '") {
                            ' . $js_content . '
                        }
                    }
                };

                ff.pluginAddInit("ff.ajax", function () {
                    ff.ajax.addEvent({
                        "event_name" : "onUpdatedContent"
                        , "func_name" : ff.fn.' . $js_key . '
                    });

                });
                
                jQuery(document).ready(function() {
                    ff.fn.' . $js_key . '();
                });
            } 
        ';
    }
    
    return $js_content;
}
