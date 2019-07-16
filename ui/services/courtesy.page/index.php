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
 * @subpackage connector
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */ 
	// $globals : globals settings
    // $actual_srv = params defined by system
    if(Auth::isguest())
    {
        $now =  time();
        $timestamp = 0;
        $expire = null;
        $is_valid = true;

		if ($actual_srv["expire"] && $now > $actual_srv["expire"])
			$is_valid = false;

		 if($is_valid) {  	
		 	define("DISABLE_CACHE", true);	
		 	if($actual_srv["status_code"])
		 		http_response_code($actual_srv["status_code"]);

	        if($globals->user_path == "/") {
	            if(isset($actual_srv["embed"])  && strlen($actual_srv["embed"])) {
        			$embed_content = $actual_srv["embed"];

					if(is_array($oPage->page_js) && count($oPage->page_js)) {
						$js_preserve = array("jquery");
						$js_purged = array();

						foreach($js_preserve AS $key) {
							if(array_key_exists($key, $oPage->page_js)) {
								$js_purged[$key] = $oPage->page_js[$key];
							}
						}
						$oPage->page_js = $js_purged;
					}

					if(is_array($oPage->page_css) && count($oPage->page_css)) {
						$css_preserve = array(/*"gallerydefault"*/);
						$css_purged = array();

						foreach($css_preserve AS $key) {
							if(array_key_exists($key, $oPage->page_css)) {
								$css_purged[$key] = $oPage->page_css[$key];
							}
						}
						$oPage->page_css = $css_purged;
					}

					//$oPage->use_own_js = false;
					$oPage->widgets = array();

					$oPage->addContent($embed_content, null, basename(__DIR__));

                    if(is_file(FF_DISK_PATH . FF_THEME_DIR ."/" . FRONTEND_THEME . "/css/courtesy-page.css")) {
						$oPage->tplAddCss("courtesyPage", array(
        					"path" => FF_THEME_DIR ."/" . FRONTEND_THEME . "/css"
        					, "file" => "courtesy-page.css"
				        ));        
                    }
                    if(is_file(FF_DISK_PATH . FF_THEME_DIR ."/" . FRONTEND_THEME . "/javascript/courtesy-page.js")) {
						$oPage->tplAddJs("courtesyPage", array(
        					"path" => FF_THEME_DIR ."/" . FRONTEND_THEME . "/javascript"
        					, "file" => "courtesy-page.js"
				        ));                      
                    }
					define("SKIP_VG_CONTENT", true);
					define("SKIP_VG_LAYOUT", true);
				}
			} else { 
				if($actual_srv["force_login"]) {
					if($globals->user_path != FF_SITE_PATH . "/login" && basename($globals->user_path) != "login") {
						ffRedirect(FF_SITE_PATH . "/login");
					} else {
						use_cache(false);
					//essendoci bootstrap o foundation in teoria nn serve
/*						
                        if(is_file(FF_DISK_PATH . FF_THEME_DIR ."/" . FRONTEND_THEME . "/css/login.css")) {
						    $oPage->tplAddCss("login", "login.css", FF_THEME_DIR ."/" . FRONTEND_THEME . "/css", "stylesheet", "text/css", true, false, null, false, "bottom");
                        }
                        if(is_file(FF_DISK_PATH . FF_THEME_DIR ."/" . FRONTEND_THEME . "/javascript/courtesy-page-login.js")) {
                            $oPage->tplAddJs("courtesyPageLogin", "courtesy-page-login.js", FF_THEME_DIR ."/" . FRONTEND_THEME . "/javascript", true, false, null, false, "bottom");
                        }
*/
						define("SKIP_VG_CONTENT", true);
						define("SKIP_VG_LAYOUT", true);
					}
				}
			}			
		 }
    }