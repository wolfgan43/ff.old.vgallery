<?php
function system_set_css($oPage, $setting_path, $css_no_cascading = false, $include_ff = false) 
{
    $globals = ffGlobals::getInstance("gallery");

    if ($oPage->theme == FRONTEND_THEME && is_file($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/css/" . "main" . ".css")) {
        $oPage->tplAddCss("gallerydefault"
                        , "main" . ".css"
                        , FF_THEME_DIR . "/" . THEME_INSET . "/css"
                        , "stylesheet"
                        , "text/css"
                        , false
                        , false
                        , null
                        , false
                        , "first"
                    );
    }

    if(is_array($oPage->font_icon)) {
        if(is_file($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "font-icon" . ".css"))
        {
            $oPage->tplAddCss("font-icon"
                                , "font-icon" . ".css"
                                , null
                                , "stylesheet"
                                , "text/css"
                                , false
                                , $oPage->isXHR()
                                , null
                                , false
                                , "bottom"
                            );
        } else if(is_file($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/css/" . "font-icon" . ".css"))
        {
            $oPage->tplAddCss("font-icon"
                                , "font-icon" . ".css"
                                , FF_THEME_DIR . "/" . THEME_INSET . "/css"
                                , "stylesheet"
                                , "text/css"
                                , false
                                , $oPage->isXHR()
                                , null
                                , false
                                , "bottom"
                            );  
        }
	}
	    
    if (is_array($oPage->framework_css)) {
    	if(is_file($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/css/" . "ff-" . $oPage->framework_css["name"] . ".css")) {
	        $oPage->tplAddCss("ff-" . $oPage->framework_css["name"]
                            , "ff-" . $oPage->framework_css["name"] . ".css"
                            , FF_THEME_DIR . "/" . THEME_INSET . "/css"
                            , "stylesheet"
                            , "text/css"
                            , false
                            , $oPage->isXHR()
                            , null
                            , false
                            , "bottom"
                        );
		}
    }      
    
    if($include_ff) {
        if (is_file($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "ff" . ".css")) {
            $oPage->tplAddCss("ff"
                                , "ff" . ".css"
                                , FF_THEME_DIR . "/" . $oPage->theme . "/css"
                                , "stylesheet"
                                , "text/css"
                                , false
                                , $oPage->isXHR()
                                , null
                                , false
                                , "bottom"
                            );
        } elseif (is_file($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/css/" . "ff" . ".css")) {
            $oPage->tplAddCss("ff"
                                , "ff" . ".css"
                                , FF_THEME_DIR . "/" . THEME_INSET . "/css"
                                , "stylesheet"
                                , "text/css"
                                , false
                                , $oPage->isXHR()
                                , null
                                , false
                                , "bottom"
                            );
        }  
        
   	if (is_file($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "ff-skin" . ".css")) {
            $oPage->tplAddCss("ff-skin"
                                , "ff-skin" . ".css"
                                , null
                                , "stylesheet"
                                , "text/css"
                                , false
                                , $oPage->isXHR()
                                , null
                                , false
                                , "bottom"
                            );
        }
    }
    
    if(is_array($globals->css["frontend"]) && count($globals->css["frontend"])) {
    	foreach($globals->css["frontend"] AS $css_key => $css_value) {
		    if(!isset($globals->media_exception["css"][$css_key]) || $globals->media_exception["css"][$css_key]) { 
		        $oPage->tplAddCss($css_key
		                        , (isset($css_value["file"]) ? $css_value["file"] : null)
		                        , (isset($css_value["path"]) ? $css_value["path"] : null)
		                        , (isset($css_value["rel"]) ? $css_value["rel"] : "stylesheet")
		                        , (isset($css_value["type"]) ? $css_value["type"] : "text/css")
		                        , (isset($css_value["overwrite"]) ? $css_value["overwrite"] : false)
		                        , (isset($css_value["async"]) ? $css_value["async"] : false)
		                        , (isset($css_value["media"]) ? $css_value["media"] : null)
		                        , (isset($css_value["exclude_compact"]) ? $css_value["exclude_compact"] : false)
		                        , (isset($css_value["priority"]) ? $css_value["priority"] : "top")
		                    );
			}
    	}
	}

    if(is_array($globals->css["embed"]) && count($globals->css["embed"])) {
        foreach($globals->css["embed"] AS $css_name => $css_embed) {
            $oPage->tplAddCss($css_name, array(
                "embed" => $css_embed
            ));
        }
    }

    //css di livello
    system_set_css_level($oPage, $setting_path, $css_no_cascading);
    
   // $oPage->parse_css();  
}

function system_set_css_level($oPage, $setting_path, $css_no_cascading = false) {
	$globals = ffGlobals::getInstance("gallery");

    $above_the_fold = "";
    $arrCss = array();
	$skip_cache = (defined("DISABLE_CACHE")
					? true
					: $globals->cache["enabled"] === false
						? true
						: false
				);

    $css_meta = array("screen" => false
                        , "tty" => false
                        , "tv" => false
                        , "projection" => true
                        , "handheld" => false
                        , "print" => true
                        , "braille" => false
                        , "aural" => false
                        , "all" => false
                    );
	$user_permission = get_session("user_permission");
    if(isset($user_permission["primary_gid_name"]) && strlen($user_permission["primary_gid_name"]))
    	$group_name = $user_permission["primary_gid_name"];
	else 
        $group_name = MOD_SEC_GUEST_GROUP_NAME;

    if(($css_no_cascading && $setting_path == "/")
        || (!$css_no_cascading)
    ) {
        $css_name = "root";
        if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css")) {
            //css root di lingua va gia in bottom
            $arrCss[] = array($css_name . "-" . strtolower(LANGUAGE_INSET)
                            , $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css" 
                            , null
                            , "stylesheet"
                            , "text/css"
                            , false
                            , false
                            , null
                            , false
                            , "bottom"
                        );
        }
        if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")) {
            //css root standard
            $arrCss[] = array($css_name
                            , $css_name . ".css"
                            , null
                            , "stylesheet"
                            , "text/css"
                            , false
                            , $oPage->isXHR()
                            , null
                            , false
                            , "bottom"
                        );
        }
        
        foreach($css_meta AS $css_meta_key => $css_meta_value) {
            if($css_meta_value) {
                //css root con meta di lingua va gia in bottom
                if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET) . ".css")) {
                    $arrCss[] = array($css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET)
                                    , $css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET) . ".css"
                                    , null
                                    , "stylesheet"
                                    , "text/css"
                                    , false
                                    , false
                                    , $css_meta_key
                                    , false
                                    , "bottom"
                                );
                }
                //css root con meta
                if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . ".css")) {
                    $arrCss[] = array($css_name . "-" . $css_meta_key
                                    , $css_name . "-" . $css_meta_key . ".css"
                                    , null
                                    , "stylesheet"
                                    , "text/css"
                                    , false
                                    , false
                                    , $css_meta_key
                                    , false
                                    , "bottom"
                                );
                }
            }
        } reset($css_meta);

        $css_name = "group-" . $group_name;
 		if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")) {
            //css group standard
            $arrCss[] = array($css_name
                            , $css_name . ".css"
                            , null
                            , "stylesheet"
                            , "text/css"
                            , false
                            , $oPage->isXHR()
                            , null
                            , false
                            , "bottom"
                        );
        }
    }

    if($setting_path == "/") {
        $css_name = "home";

        if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css")) {
            //css home di lingua va gia in bottom
			$arrCss[] = array($css_name . "-" . strtolower(LANGUAGE_INSET)
                            , $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css"
                            , null
                            , "stylesheet"
                            , "text/css"
                            , false
                            , false
                            , null
                            , false
                            , "bottom"
                        );
        } 
        if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")) {
            //css home standard
            $arrCss[] = array($css_name
                            , $css_name . ".css"
                            , null
                            , "stylesheet"
                            , "text/css"
                            , false
                            , false
                            , null
                            , false
                            , "bottom"
                        );
        }

        
        foreach($css_meta AS $css_meta_key => $css_meta_value) {
            if($css_meta_value) {
                //css home con meta di lingua va gia in bottom
                if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET) . ".css")) {
                    $arrCss[] = array($css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET)
                                    , $css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET) . ".css"
                                    , null
                                    , "stylesheet"
                                    , "text/css"
                                    , false
                                    , false
                                    , $css_meta_key
                                    , false
                                    , "bottom"
                                );
                }
                
                //css home con meta
                if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . ".css")) {
                    $arrCss[] = array($css_name . "-" . $css_meta_key
                                    , $css_name . "-" . $css_meta_key . ".css"
                                    , null
                                    , "stylesheet"
                                    , "text/css"
                                    , false
                                    , false
                                    , $css_meta_key
                                    , false
                                    , "bottom"
                                );
                }
            }
        } reset($css_meta);

        $css_name = "group-" . $group_name . "_home";
 		if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")) {
            //css group home 
            $arrCss[] = array($css_name
                            , $css_name . ".css"
                            , null
                            , "stylesheet"
                            , "text/css"
                            , false
                            , $oPage->isXHR()
                            , null
                            , false
                            , "bottom"
                        );
        }     
        
        if(!strlen($above_the_fold) && file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "above-the-fold_" . $css_name . ".css")) {
            $above_the_fold = "above-the-fold_" . $css_name;
        }
    } else {
    	$arrCssCascading = array();
        do {
            $css_name = str_replace("/", "_", trim($setting_path, "/"));

            if (file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css")) {
                //css di lingua va gia in bottom
                $arrCssCascading[] = array(ffGetFilename($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css")
                                                , $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css"
                                                , null
                                                , "stylesheet"
                                                , "text/css"
                                                , false
                                                , $oPage->isXHR()
                                                , null
                                                , false
                                                , "bottom"
                                            );
            }
            if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")) {
                //css standard
                $arrCssCascading[] = array(ffGetFilename($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")
                                                , $css_name . ".css"
                                                , null
                                                , "stylesheet"
                                                , "text/css"
                                                , false
                                                , $oPage->isXHR()
                                                , null
                                                , false
                                                , "bottom"
                                            );
            }
                        
            //css per i figli
            $father_setting_path = ffCommon_dirname($setting_path);
            $css_name = str_replace("/", "_", ltrim($father_setting_path, "/") . "/");
            
            if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css")) {
                //css per i figli di lingua va gia in bottom
                $arrCssCascading[] = array(ffGetFilename($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css")
                                                , $css_name . "-" . strtolower(LANGUAGE_INSET) . ".css"
                                                , null
                                                , "stylesheet"
                                                , "text/css"
                                                , false
                                                , $oPage->isXHR()
                                                , null
                                                , false
                                                , "bottom"
                                            );
            }
            if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")) {
                //css per i figli standard
                $arrCssCascading[] = array(ffGetFilename($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . ".css")
                                                , $css_name . ".css"
                                                , null
                                                , "stylesheet"
                                                , "text/css"
                                                , false
                                                , $oPage->isXHR()
                                                , null
                                                , false
                                                , "bottom"
                                            );
            }
            
            foreach($css_meta AS $css_meta_key => $css_meta_value) {
                if($css_meta_value) {
                    if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET) . ".css")) {
                        //css con specifico meta di lingua va gia in bottom
                        $arrCssCascading[] = array(ffGetFilename($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET) . ".css")
                                                , $css_name . "-" . $css_meta_key . "-" . strtolower(LANGUAGE_INSET) . ".css"
                                                , null
                                                , "stylesheet"
                                                , "text/css"
                                                , false
                                                , $oPage->isXHR()
                                                , $css_meta_key
                                                , false
                                                , "bottom"
                                            );
                    }
                    if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . ".css")) {
                        //css con specifico meta standard
                        $arrCssCascading[] = array(ffGetFilename($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $css_name . "-" . $css_meta_key . ".css")
                                                , $css_name . "-" . $css_meta_key . ".css"
                                                , null
                                                , "stylesheet"
                                                , "text/css"
                                                , false
                                                , $oPage->isXHR()
                                                , $css_meta_key
                                                , false
                                                , "bottom"
                                            );
                    }
                }
            } reset($css_meta);

            if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "group-" . $group_name . "_" . $css_name . ".css")) {
                //css standard
                $arrCssCascading[] = array(ffGetFilename($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "group-" . $group_name . "_" . $css_name . ".css")
                                                , "group-" . $group_name . "_" . $css_name . ".css"
                                                , null
                                                , "stylesheet"
                                                , "text/css"
                                                , false
                                                , $oPage->isXHR()
                                                , null
                                                , false
                                                , "bottom"
                                            );
            }
            
            //css Above The fold
            if(!strlen($above_the_fold) && file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "above-the-fold_" . $css_name . ".css")) {
                $above_the_fold = "above-the-fold_" . $css_name;
            }

            if ($css_no_cascading)
                break;
        } while($setting_path != ffCommon_dirname($setting_path) && $setting_path = ffCommon_dirname($setting_path));

        krsort($arrCssCascading);
		$arrCss = array_merge($arrCss, $arrCssCascading);
    }  

    if(count($arrCss)) {
        foreach($arrCss AS $css) {
            //$css_cascading_value[1] .= "?" . time();
            call_user_func_array(array($oPage, "tplAddCss"), $css);

            if($skip_cache) {
            	$css_path = ($css[2] 
            					? $css[2] 
            					: "/themes/" . $oPage->theme . "/css/"
            				) . $css[1];
            	
            	$oPage->override_css[$css[0]] = $css_path . "?" . filemtime(FF_DISK_PATH . $css_path);
            	
			}
            //$oPage->tplAddCss(ffGetFilename($css_cascading_value), basename($css_cascading_value));
        } 
    }   
     
    //css Above The fold
    if(!strlen($above_the_fold) && file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . "above-the-fold" . ".css")) {
        //css standard
        $above_the_fold = "above-the-fold.css";
    }
    
    if(strlen($above_the_fold)) {
    	$oPage->tplAddCss("above-the-fold"
	                        , null
	                        , null
	                        , "stylesheet"
	                        , "text/css"
	                        , false
	                        , false
	                        , null
	                        , true
	                        , "bottom"
	                        , file_get_contents($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $above_the_fold)
    	);
    }
}