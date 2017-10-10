<?php
function system_get_js_layout($oPage, $request, $setting_path, $return_buffer = false) {
    $db = ffDB_Sql::factory();

    /*if (!$return_buffer && file_exists($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/css/" . "main" . ".css")) {
        $oPage->tplAddCss("gallerydefault"
                        , "main" . ".css"
                        , FF_THEME_DIR . "/" . THEME_INSET . "/css"
                        , "stylesheet"
                        , "text/css"
                        , false
                        , false
                        , null
                        , false
                        , "top"
                    );
    }*/

    if(is_array($request)) {
        $js_request = $request;
        foreach($js_request AS $js_request_key => $js_request_value) {
            if(strlen($js_request_text))
                $js_request_text .= ", ";
            $js_request_text .= "'" . $js_request_key . "'";
        }
    } else {
        $js_request = array();
    }
    //$oPage->tplAddJs("main", "main.js", FF_SITE_PATH . "/javascript");
    $str_js_request = "";
    $str_js_request_end = "";

    $sSQL = "
        SELECT * FROM
        (
            (
                SELECT DISTINCT
                    js.*
                    , 1 AS `priority`
                FROM `js_dipendence`
                    INNER JOIN js ON js.ID = js_dipendence.`ID_js_libs`
                WHERE js.status = 1  
                    AND js.src_path <> ''
                    AND js.type = 'libs'
                    AND js_dipendence.`ID_js_plugin` IN 
                                        (
                                            SELECT DISTINCT 
                                                js.ID
                                            FROM js
                                            WHERE js.status =1
                                            AND js.src_path <> ''
                                            " . (strlen($js_request_text) 
                                                    ? " AND js.name IN ( " . $js_request_text . " ) "
                                                    : " AND 0 ") . "
                                        )
                 ORDER BY js.`order`
            )     
            UNION
            (
                SELECT 
                    js.*
                    , 2 AS `priority`
                FROM js
                WHERE js.status = 1
                    AND js.src_path <> ''
                    
                    " . (strlen($js_request_text) 
                            ? " AND js.name IN ( " . $js_request_text . " ) "
                            : "AND 0 ") . "
                ORDER BY js.`order` 
            )
        ) AS tbl_src
        ORDER BY tbl_src.priority, tbl_src.type, tbl_src.`order`";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            if($db->getField("base_path")->getValue()) {
                $base_path = $db->getField("base_path")->getValue();
            } else {
                $base_path = "";
            }
            
            $js_name = $db->getField("name")->getValue();
            
            $load_css = $db->getField("load_css")->getValue();
            $exclude_compact = $db->getField("exclude_compact")->getValue();
            $type = $db->getField("type")->getValue();
            if($type == "plugin")
            	$type = "plugins";

         /*   if($globals->js["request"][$js_name] === null)
                $async = $db->getField("async")->getValue();
            else
                $async = $globals->js["request"][$js_name];     */

            $async = false;

            if(strlen($load_css)) {
                $load_css = explode(";", $load_css);
                if(is_array($load_css) && count($load_css)) {
                    foreach($load_css AS $load_css_key => $load_css_value) {
                        $load_css_value = str_replace("[JQUERY_UI_THEME]", JQUERY_UI_THEME, $load_css_value);
                        if(substr(strtolower($load_css_value), 0, 7) == "http://"
                            || substr(strtolower($load_css_value), 0, 8) == "https://"
                            || substr($load_css_value, 0, 2) == "//"
                        ) {
                                if($return_buffer) {
                                    $str_js_request .= ' ff.injectCSS("' . ffGetFilename($load_css_value) . '", "' . $load_css_value .'", function() { ';
                                    $str_js_request_end .= ' }); ';
                                } else {
                                    $oPage->tplAddCss(ffGetFilename($load_css_value)
                                                        , basename($load_css_value)
                                                        , ffCommon_dirname($load_css_value)
                                                        , "stylesheet"
                                                        , "text/css"
                                                        , false
                                                        , false
                                                        , null
                                                        , false 
                                                        , "top"
                                                    );
                                }
                        } else {
                            if(file_exists($oPage->disk_path . $load_css_value)) {
                                $tmp_js_path = $load_css_value;
                            } else {
                                if(strlen($base_path) && file_exists($oPage->disk_path . $base_path . $load_css_value) && !file_exists($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $load_css_value)) {
                                    $tmp_js_path = $base_path . $load_css_value;
                                } else {
                                    $tmp_js_path = FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $load_css_value;
                                }
                            }
                            $real_name = "";
                            $real_path = "";
                            $tmp_user_path = $setting_path;
                            do {
                                if(strlen($tmp_user_path) && $tmp_user_path != "/")
                                    $real_name = str_replace("/", "_", trim($tmp_user_path, "/")) . "_" . basename($tmp_js_path);
                                else
                                    $real_name = basename($tmp_js_path);
                                    
                                if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/css/" . $real_name)) {
                                    $real_path = FF_THEME_DIR . "/" . $oPage->theme . "/css";
                                    break;
                                }
                            } while($tmp_user_path != ffCommon_dirname($tmp_user_path) && $tmp_user_path = ffCommon_dirname($tmp_user_path));
                             if(!strlen($real_path)) {
                                 $real_name = basename($tmp_js_path);
                                 $real_path = ffCommon_dirname($tmp_js_path);
                            }
                            if(file_exists($oPage->disk_path . $real_path . "/" . $real_name)) {
                                if($return_buffer) {
                                    $str_js_request .= ' ff.injectCSS("' . ffGetFilename($tmp_js_path) . '", "' . stripslash($real_path) . '/' . $real_name .'", function() { ';
                                    $str_js_request_end .= ' }); ';
                                } else {
                                    $oPage->tplAddCss(ffGetFilename($tmp_js_path)
                                                        , $real_name
                                                        , $real_path
                                                        , "stylesheet"
                                                        , "text/css"
                                                        , false
                                                        , false 
                                                        , null
                                                        , false 
                                                        , "top"
                                                    );
                                }
                            }
                        }
                    }
                }
            }

            $preload_js = $db->getField("preload_cnf")->getValue();
            if(strlen($preload_js)) {
                $preload_js = explode(";", $preload_js);
                if(is_array($preload_js) && count($preload_js)) {
                    foreach($preload_js AS $preload_js_key => $preload_js_value) { 
                        if(substr(strtolower($preload_js_value), 0, 7) == "http://"
                            || substr(strtolower($preload_js_value), 0, 8) == "https://"
                            || substr($preload_js_value, 0, 2) == "//"
                        ) {
                                if($return_buffer) {
                                    $str_js_request .= ' ff.pluginLoad("' . ffGetFilename($preload_js_value) . '", "' . $preload_js_value .'", function() { ';
                                    $str_js_request_end .= ' }); ';
                                } else {
                                    $oPage->tplAddJs(ffGetFilename($preload_js_value)
                                                                , basename($preload_js_value)
                                                                , ffCommon_dirname($preload_js_value)
                                                                , false
                                                                , $async
                                                                , null
                                                                , $exclude_compact
                                                    );
                                }
                        } else {
                            if(file_exists($oPage->disk_path . $preload_js_value)) {
                                $tmp_js_path = $preload_js_value;
                            } else {
                                if(strlen($base_path) && file_exists($oPage->disk_path . $base_path . $preload_js_value) && !file_exists($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $preload_js_value)) {
                                    $tmp_js_path = $base_path . $preload_js_value;
                                } else {
                                    $tmp_js_path = FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $preload_js_value;
                                }
                            }
                            $real_name = "";
                            $real_path = "";
                            $tmp_user_path = $setting_path;
                            do {
                                if(strlen($tmp_user_path) && $tmp_user_path != "/")
                                    $real_name = str_replace("/", "_", trim($tmp_user_path, "/")) . "_" . basename($tmp_js_path);
                                else
                                    $real_name = basename($tmp_js_path);

                                if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/javascript/" . $real_name)) {
                                    $real_path = FF_THEME_DIR . "/" . $oPage->theme . "/javascript";
                                    break;
                                }
                            } while($tmp_user_path != ffCommon_dirname($tmp_user_path) && $tmp_user_path = ffCommon_dirname($tmp_user_path));
                             if(!strlen($real_path)) {
                                 $real_name = basename($tmp_js_path);
                                 $real_path = ffCommon_dirname($tmp_js_path);
                            }
                            if(file_exists($oPage->disk_path . $real_path . "/" . $real_name)) {
                                if($return_buffer) {
                                    $str_js_request .= ' ff.pluginLoad("' . ffGetFilename($tmp_js_path) . '", "' . stripslash($real_path) . '/' . $real_name .'", function() { ';
                                    $str_js_request_end .= ' }); ';
                                } else {
                                    $oPage->tplAddJs(ffGetFilename($tmp_js_path)
                                                                , $real_name
                                                                , $real_path
                                                                , false
                                                                , $async
                                                                , null
                                                                , $exclude_compact
                                                    );
                                }
                            }
                        }                     
                    }
                }
            }
            $js_path = $db->getField("src_path")->getValue();
            if(substr(strtolower($js_path), 0, 7) == "http://"
                || substr(strtolower($js_path), 0, 8) == "https://"
                || substr($js_path, 0, 2) == "//"
            ) {
                    if($return_buffer) {
                        $str_js_request .= ' ff.pluginLoad("' . ffGetFilename($js_path) . '", "' . $js_path .'", function() { ';
                        $str_js_request_end .= ' }); ';
                    } else {
                        $oPage->tplAddJs(ffGetFilename($js_path)
                                                    , basename($js_path)
                                                    , ffCommon_dirname($js_path)
                                                    , false
                                                    , $async
                                                    , null
                                                    , $exclude_compact
                                        );
                    }
            } else {
                if(file_exists($oPage->disk_path . $js_path)) {
                    $tmp_js_path = $js_path;
                } else {
                    if(strlen($base_path) && file_exists($oPage->disk_path . $base_path . $js_path) && !file_exists($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $js_path)) {
                        $tmp_js_path = $base_path . $js_path;
                    } else {
                        $tmp_js_path = FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $js_path;
                    }
                }
                $real_name = "";
                $real_path = "";
                $tmp_user_path = $setting_path;
                do {
                    if(strlen($tmp_user_path) && $tmp_user_path != "/")
                        $real_name = str_replace("/", "_", trim($tmp_user_path, "/")) . "_" . basename($tmp_js_path);
                    else
                        $real_name = basename($tmp_js_path);

                    if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/javascript/" . $real_name)) {
                        $real_path = FF_THEME_DIR . "/" . $oPage->theme . "/javascript";
                        break;
                    }
                } while($tmp_user_path != ffCommon_dirname($tmp_user_path) && $tmp_user_path = ffCommon_dirname($tmp_user_path));
                if(!strlen($real_path)) {
                    $real_name = basename($tmp_js_path);
                    $real_path = ffCommon_dirname($tmp_js_path);
                }
                if(file_exists($oPage->disk_path . $real_path . "/" . $real_name)) {
                    if($return_buffer) {
                        $str_js_request .= ' ff.pluginLoad("' . ffGetFilename($tmp_js_path) . '", "' . stripslash($real_path) . '/' . $real_name .'", function() { ';
                        $str_js_request_end .= ' }); ';
                    } else {
                        $oPage->tplAddJs(ffGetFilename($tmp_js_path)
                                                    , $real_name
                                                    , $real_path
                                                    , false
                                                    , $async
                                                    , null
                                                    , false
                                        );
                    }
                }
            }

            $postload_js = $db->getField("postload_cnf")->getValue();
            if(strlen($postload_js)) {
                $postload_js = explode(";", $postload_js);
                if(is_array($postload_js) && count($postload_js)) {
                    foreach($postload_js AS $postload_js_key => $postload_js_value) {
                        if(substr(strtolower($postload_js_value), 0, 7) == "http://"
                            || substr(strtolower($postload_js_value), 0, 8) == "https://"
                            || substr($postload_js_value, 0, 2) == "//"
                        ) {
                                if($return_buffer) {
                                    $str_js_request .= ' ff.pluginLoad("' . ffGetFilename($postload_js_value) . '", "' . $postload_js_value .'", function() { ';
                                    $str_js_request_end .= ' }); ';
                                } else {
                                    $oPage->tplAddJs(ffGetFilename($postload_js_value)
                                                                , basename($postload_js_value)
                                                                , ffCommon_dirname($postload_js_value)
                                                                , false
                                                                , $async
                                                                , null
                                                                , $exclude_compact
                                                    );
                                }
                        } else {
                            if(file_exists($oPage->disk_path . $postload_js_value)) {
                                $tmp_js_path = $postload_js_value;
                            } else {
                                if(strlen($base_path) && file_exists($oPage->disk_path . $base_path . $postload_js_value) && !file_exists($oPage->disk_path . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $postload_js_value)) {
                                    $tmp_js_path = $base_path . $postload_js_value;
                                } else {
                                    $tmp_js_path = FF_THEME_DIR . "/" . THEME_INSET . "/javascript/" . $type . $postload_js_value;
                                }
                            }
                            $real_name = "";
                            $real_path = "";
                            $tmp_user_path = $setting_path;
                            do {
                                if(strlen($tmp_user_path) && $tmp_user_path != "/")
                                    $real_name = str_replace("/", "_", trim($tmp_user_path, "/")) . "_" . basename($tmp_js_path);
                                else
                                    $real_name = basename($tmp_js_path);

                                if(file_exists($oPage->disk_path . FF_THEME_DIR . "/" . $oPage->theme . "/javascript/" . $real_name)) {
                                    $real_path = FF_THEME_DIR . "/" . $oPage->theme . "/javascript";
                                    break;
                                }
                            } while($tmp_user_path != ffCommon_dirname($tmp_user_path) && $tmp_user_path = ffCommon_dirname($tmp_user_path));
                            if(!strlen($real_path)) {
                                $real_name = basename($tmp_js_path);
                                $real_path = ffCommon_dirname($tmp_js_path);
                            }
                            if(file_exists($oPage->disk_path . $real_path . "/" . $real_name)) {
                                if($return_buffer) {
                                    $str_js_request .= ' ff.pluginLoad("' . ffGetFilename($tmp_js_path) . '", "' . stripslash($real_path) . '/' . $real_name .'", function() { ';
                                    $str_js_request_end .= ' }); ';
                                } else {
                                    $oPage->tplAddJs(ffGetFilename($tmp_js_path)
                                                                , $real_name
                                                                , $real_path
                                                                , false
                                                                , $async
                                                                , null
                                                                , $exclude_compact
                                                    );
                                }
                            }
                        }                     
                    }
                }
            }            
        } while($db->nextRecord());
    }
    
    if($return_buffer) {
        $unic_js_key = preg_replace('/[^a-zA-Z0-9]/', '', $setting_path); 
        if(strlen($str_js_request)) {
        	//$str_js_request = '  <script type="text/javascript">' . $str_js_request . "if(ff.cms.skipInit) { ff.cms.skipInit = false; } else { ff.cms.widgetInit('#' + '" . $unic_js_key . "', false); } " . $str_js_request_end . '</script>';
            $str_js_request = '  <script type="text/javascript">' . $str_js_request  . $str_js_request_end . '</script>';
        }

        return array("key" => $unic_js_key, "data" => $str_js_request);
    } else {
        return $js_request;    
    }
}
?>
