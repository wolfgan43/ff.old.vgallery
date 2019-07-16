<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(Auth::isGuest())
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
        			/*if(check_function("system_get_sections"))
                    	$template = system_get_sections();

                    $sections = $template["sections"];
					foreach($template["main_section"] AS $ID_main_section) {
					    if(is_array($sections[$ID_main_section]["layouts"]) && count($sections[$ID_main_section]["layouts"])) {
							$primary_main_section = $ID_main_section;
							break;
					    }
					}
					foreach($sections AS $sections_key => $sections_value) {
						if($sections_key !== $primary_main_section)
							unset($sections[$sections_key]);
					}  */
					
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

					$oPage->use_own_js = false;
					$oPage->widgets = array();

					$oPage->addContent($embed_content, null, basename(__DIR__));

                    if(is_file(FF_DISK_PATH . FF_THEME_DIR ."/" . FRONTEND_THEME . "/css/courtesy-page.css")) {
                        $oPage->tplAddCss("courtesyPage", "courtesy-page.css", FF_THEME_DIR ."/" . FRONTEND_THEME . "/css", "stylesheet", "text/css", true, false, null, false, "bottom");
                    }
                    if(is_file(FF_DISK_PATH . FF_THEME_DIR ."/" . FRONTEND_THEME . "/javascript/courtesy-page.js")) {
                        $oPage->tplAddJs("courtesyPage", "courtesy-page.js", FF_THEME_DIR ."/" . FRONTEND_THEME . "/javascript", true, false, null, false, "bottom");
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
						//$oPage->tplAddCss("gallerydefault", "main.css", FF_THEME_DIR ."/" . THEME_INSET . "/css");
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