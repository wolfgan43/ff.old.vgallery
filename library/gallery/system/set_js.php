<?php
function system_set_js($oPage, $setting_path, $reset = true, $destination_path = null, $use_admin_ajax = false, $js_no_cascading = false, $use_ff_event = true) 
{
    $db = ffDB_Sql::factory();
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");

    if($use_admin_ajax) {
    	$ncol = 6;
    
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
            )
            , $oPage
        );
        /*$oPage->widgets["dialog"]->process(
             "dialogManageHuge"
             , array(
                "tpl_id" => null
                //"name" => "myTitle"
                , "url" => ""
                , "title" => ""
                , "callback" => ""
                , "class" => ""
                , "params" => array(
                )
                , "resizable" => false
                , "position" => "center"
                , "draggable" => true
                , "doredirects" => true
                , "width" => "'96%'"
            )
            , $oPage
        );*/
    }
    
    $oPage->tplAddJs("gallerymain", "main.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript", false, $cm->isXHR()); 
    
    if(check_function("system_get_js_layout"))
        $js_request = system_get_js_layout($oPage, $globals->js["request"], $setting_path, false);

    if(is_array($globals->js["system"]) && count($globals->js["system"])) {
        foreach($globals->js["system"] AS $js_name => $js_enable) {
            if($js_enable && is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/system/" . $js_name . ".js"))
                $oPage->tplAddJs($js_name, $js_name . ".js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/system", false, $cm->isXHR()); 
        }
    }
    if(is_array($globals->js["tools"]) && count($globals->js["tools"])) {
        foreach($globals->js["tools"] AS $js_name => $js_enable) {
            if($js_enable && is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/" . $js_name . ".js"))
                $oPage->tplAddJs($js_name, $js_name . ".js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools", false, $cm->isXHR()); 
        }
    }


    if(is_array($globals->js["embed"]) && count($globals->js["embed"])) {
        foreach($globals->js["embed"] AS $js_name => $js_embed) {
            $oPage->tplAddJs($js_name, array(
                "embed" => $js_embed
            ));
        }
    }
/*
    foreach(glob($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/system/*") AS $real_file) {
        $real_name = "";
        $real_path = "";
        $tmp_user_path = $setting_path;
        do {
            if(strlen($tmp_user_path) && $tmp_user_path != "/")
                $real_name = str_replace("/", "_", trim($tmp_user_path, "/")) . "_" . basename($real_file);
            else
                $real_name = basename($real_file);
            if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/javascript/" . $real_name)) {
                $real_path = FF_THEME_DIR . "/" . $oPage->theme . "/javascript";
                break;
            }
        } while($tmp_user_path != ffCommon_dirname($tmp_user_path) && $tmp_user_path = ffCommon_dirname($tmp_user_path));
        if(!strlen($real_path)) {
            $real_name = basename($real_file);
            $real_path = FF_THEME_DIR . "/" . THEME_INSET . "/javascript/system";
        }

        
        if(file_exists($oPage->disk_path . $real_path . "/" . $real_name)) {
            if(is_array($js_request) && array_key_exists(ffGetFilename($real_file), $js_request)) {
                $oPage->tplAddJs(ffGetFilename($real_file), basename($real_name), $real_path, false, $async);
            }
        }
    }  */
     /*
    $enable_js = get_session("jsTools");
    if(is_array($enable_js) && count($enable_js)) {
        foreach(glob($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/*") AS $real_file) {
            if(file_exists($real_file) && $enable_js[strtolower(ffGetFilename($real_file))]) {
                $oPage->tplAddJs(ffGetFilename($real_file), basename($real_file), FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . "tools", false, $async);
            }
        }
    } */


    //js di livello
    system_set_js_level($oPage, $setting_path, $js_no_cascading, $destination_path, $use_ff_event);
    
    if($reset) {
        $oPage->parse_css();
        $oPage->parse_js();
    }
}

function system_set_js_level($oPage, $setting_path, $js_no_cascading = false, $destination_path = null, $use_ff_event = true) {
    $globals = ffGlobals::getInstance("gallery");

    $js_cascading = array();
    $js_events_cascading = array();

    $skip_cache = (defined("DISABLE_CACHE")
        ? true
        : $globals->cache["enabled"] === false
            ? true
            : false
    );

    if($destination_path === null) {
        $destination_path = FF_THEME_DIR . "/" . $oPage->theme . "/javascript";
    }

    $js_name = "root";
    if (file_exists($oPage->disk_path . $destination_path . "/" . $js_name . ".js")) {
        $oPage->tplAddJs($js_name, $js_name . ".js", null, false, false, null, false, "bottom");

        if($skip_cache) {
            $oPage->override_js[$js_name] = $destination_path . "/" . $js_name . ".js" . "?" . filemtime($oPage->disk_path . $destination_path . "/" . $js_name . ".js");
        }
    }
    if($setting_path == "/") {
        $js_name = "home";
        if (file_exists($oPage->disk_path . $destination_path . "/" . $js_name . ".js")) {
            $oPage->tplAddJs($js_name, $js_name . ".js", null, false, false, null, false, "bottom");

            if($skip_cache) {
                $oPage->override_js[$js_name] = $destination_path . "/" . $js_name . ".js" . "?" . filemtime($oPage->disk_path . $destination_path . "/" . $js_name . ".js");
            }
        }
    }    
    if($use_ff_event) {
        if (strlen($js_name) && file_exists($oPage->disk_path . $destination_path . "/events/" . $js_name . ".js")) {
            system_set_js_level_events($oPage, $oPage->disk_path . $destination_path . "/events/" . $js_name . ".js");
        }
    }

    if($setting_path != ffCommon_dirname($setting_path)) {
        do {
            $js_name = str_replace("/", "_", trim($setting_path, "/"));

            if (file_exists($oPage->disk_path . $destination_path . "/" . $js_name . ".js")) {
                $js_cascading[] = $oPage->disk_path . $destination_path . "/" . $js_name . ".js";
            }

            if (strlen($js_name) && file_exists($oPage->disk_path . $destination_path . "/events/" . $js_name . ".js")) {
                $js_events_cascading[] = $oPage->disk_path . $destination_path . "/events/" . $js_name . ".js";
            }

            if ($js_no_cascading)
                break;
        } while($setting_path != ffCommon_dirname($setting_path) && $setting_path = ffCommon_dirname($setting_path));
        
        if(count($js_cascading)) {
            krsort($js_cascading);
            foreach($js_cascading AS $js_cascading_value) {
                $oPage->tplAddJs(ffGetFilename($js_cascading_value), basename($js_cascading_value), $destination_path, false, $oPage->isXHR(), null, false, "bottom");

                if($skip_cache) {
                    $oPage->override_js[ffGetFilename($js_cascading_value)] = $destination_path . "/" . ffGetFilename($js_cascading_value) . ".js" . "?" . filemtime($js_cascading_value);
                }
            }
        }

        if($use_ff_event && count($js_events_cascading)) {
            krsort($js_events_cascading);
            foreach($js_events_cascading AS $js_events_cascading_value) {
                system_set_js_level_events($oPage, $js_events_cascading_value);
            }
        }
    }    
}


function system_set_js_level_events($oPage, $js_path) {
    $js_key = ffGetFilename($js_path);
    $js_content = file_get_contents($js_path);
    if($js_content === false) {
        $oPage->tplAddJs(ffGetFilename($js_path), basename($js_path), ffCommon_dirname($js_path), false, $oPage->isXHR(), null, false, "bottom");
    } else {
        if(substr(ffGetFilename($js_path), strrpos(ffGetFilename($js_path), "_") + 1) == "modify") {
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
        $oPage->tplAddJs($js_key, null, null, false, $oPage->isXHR(), $js_content, false, "bottom");
    }
}
?>
