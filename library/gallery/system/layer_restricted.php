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
function system_layer_administration($oPage, $tpl_layer) 
{
    $cm = cm::getInstance();
    
    $real_path = str_replace($cm->real_path_info, "", $cm->path_info);
    
    $tpl_layer->set_var("helper", ffTemplate::_get_word_by_code(substr(str_replace("/", "_", $real_path), 1)));
    
}

function system_layer_notify($oPage, $tpl_layer) 
{
return;
	if($oPage->layer == ffGetFilename(VG_SITE_ADMIN) || $oPage->layer == ffGetFilename(VG_SITE_RESTRICTED) || $oPage->layer == ffGetFilename(VG_SITE_MANAGE)) {
        $filename = null;
        if(is_file(FF_DISK_PATH . "/themes/" . $oPage->theme . "/layouts/notify_" . $oPage->layer . ".html")) {
            $filename = FF_DISK_PATH . "/themes/" . $oPage->theme . "/layouts/notify_" . $oPage->layer . ".html";
        } elseif(is_file(FF_DISK_PATH . "/themes/" . THEME_INSET . "/layouts/notify_" . $oPage->layer . ".html")) {
            $filename = FF_DISK_PATH . "/themes/" . THEME_INSET . "/layouts/notify_" . $oPage->layer . ".html";
        }
        if($filename !== null) {
			//$oPage->tplAddCss("crystalnotifications", "jquery.crystalnotifications.css", FF_THEME_DIR . "/library/plugins/jquery.crystalnotifications");
			//$oPage->tplAddJs("jquery.crystalnotifications", "jquery.crystalnotifications.js", FF_THEME_DIR . "/library/plugins/jquery.crystalnotifications");
			$oPage->tplAddJs("layerProcess", "layerprocess.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/system");
            $db = ffDB_Sql::factory();
            $db_control = ffDB_Sql::factory();

            $count_notify = 0;    

            if(isset($_REQUEST["ret_url"]))
                $ret_url = $_REQUEST["ret_url"];
            else
                $ret_url = $_SERVER["REQUEST_URI"];

            $tpl = ffTemplate::factory(ffCommon_dirname($filename));
            $tpl->load_file("notify_" . $oPage->layer . ".html", "main");
            
            $tpl->set_var("site_path", FF_SITE_PATH);
            $tpl->set_var("theme_inset", THEME_INSET);
            $tpl->set_var("theme", $oPage->theme);
            $tpl->set_var("delete_icon", Cms::getInstance("frameworkcss")->get("cancel", "icon"));
            
            
            if($oPage->layer == ffGetFilename(VG_SITE_ADMIN))
                $sSQL_add = " OR area = '' ";
            
            $sSQL = "SELECT * 
                    FROM `notify_message` 
                    WHERE visible = '1' 
                        AND (area = " . $db->toSql($oPage->layer) . $sSQL_add . " ) 
                    ORDER BY area, last_update DESC";
            $db->query($sSQL);
            if($db->nextRecord()) {
                $arrNotify = array();

                do {
                    switch($db->getField("controls", "Text", true)) {
                        case "file":
                            if(
                                (
                                    substr(strtolower($db->getField("message", "Text", true)), 0, 7) != "http://" 
                                    && substr(strtolower($db->getField("message", "Text", true)), 0, 8) != "https://"
                                    && substr($db->getField("message", "Text", true), 0, 2) != "//"
                                ) && @file_exists(FF_DISK_PATH . $db->getField("message", "Text", true))
                            ) {
                                $sSQL = "UPDATE `notify_message` SET visible = '0' WHERE `notify_message`.ID = " . $db_control->toSql($db->getField("ID"));
                                $db_control->execute($sSQL);
                                $is_visible = false;
                            } else {
                                $is_visible = true;
                            }
                            break;
                        default:
                            $is_visible = true;
                    }

                    if(!$is_visible)
                        continue;
                    $notify_type = $db->getField("type", "Text", true);
                    $notify_title = $db->getField("title", "Text", true);
                    $notify_ID = $db->getField("ID", "Number", true);
                    $arrNotify[$notify_type][$notify_title]["item"][$notify_ID]["message"] = $db->getField("message", "Text", true);
                    $arrNotify[$notify_type][$notify_title]["item"][$notify_ID]["last_update"] = $db->getField("last_update", "Text", true);
                    $arrNotify[$notify_type][$notify_title]["item"][$notify_ID]["url"] = $db->getField("url", "Text", true);
                    $arrNotify[$notify_type][$notify_title]["item"][$notify_ID]["url_title"] = $db->getField("url_title", "Text", true);
                    $arrNotify[$notify_type][$notify_title]["item"][$notify_ID]["count"] = $db->getField("count", "Text", true);
                    $arrNotify[$notify_type][$notify_title]["item"][$notify_ID]["notify_url"] = FF_SITE_PATH . VG_SITE_ADMINNOTIFY . "/preview?keys[ID]=" . $db->getField("ID", "Number", true) . "&ret_url=" . urlencode($ret_url);
                    
                    $count_notify++;
                } while($db->nextRecord());
                
                if($count_notify) {
                    /*$css_deps         = array(
                          "jquery.ui.core"        => array(
                                  "file" => "jquery.ui.core.css"
                                , "path" => null
                                , "rel" => "jquery.ui"
                            ), 
                          "jquery.ui.theme"        => array(
                                  "file" => "jquery.ui.theme.css"
                                , "path" => null
                                , "rel" => "jquery.ui"
                            )
                    );

                    if(is_array($css_deps) && count($css_deps)) {
                        foreach($css_deps AS $css_key => $css_value) {
                            $rc = $oPage->widgetResolveCss($css_key, $css_value, $oPage);

                            $oPage->tplAddCss(preg_replace('/[^0-9a-zA-Z]+/', "", $css_key), $rc["file"], $rc["path"], "stylesheet", "text/css", false, false, null, false, "bottom");
                        }
                    }*/
                    
                    if(is_array($arrNotify) && count($arrNotify)) {
                        foreach($arrNotify AS $arrNotify_key => $arrNotify_value) {
                            $tpl->set_var("type", $arrNotify_key);
                            $tpl->set_var("class_type", "notify-" . $arrNotify_key);

                            if(is_array($arrNotify_value) && count($arrNotify_value)) {
                                foreach($arrNotify_value AS $notify_group_key => $notify_group_value) {
                                    $tpl->set_var("SezNotifyItem", "");
                                    if(substr($notify_group_key, "0", 1) == "_") {
                                        $tpl->set_var("title", ffTemplate::_get_word_by_code(ltrim($notify_group_key, "_")));
                                    } else {
                                        $tpl->set_var("title", $notify_group_key);
                                    }
                                    $tpl->set_var("real_title", $notify_group_key);
                                    if(is_array($notify_group_value["item"]) && count($notify_group_value["item"])) {
                                        foreach($notify_group_value["item"] AS $notify_item_key => $notify_item_value) {
                                            $tpl->set_var("count", $notify_item_value["count"]);
                                            
                                            $last_update = new ffdata($notify_item_value["last_update"], "Timestamp");
                                            $tpl->set_var("last_update", $last_update->getValue("DateTime", LANGUAGE_INSET));
                                            
                                            $message = strip_tags($notify_item_value["message"], "<br>");
                                            if(strlen($message) > 500)
                                                 $message = substr($message, 0, 500) . " ...";
                                                 
                                            $tpl->set_var("message", $message);
                                            if(strlen($notify_item_value["url"])) {
                                                $tpl->set_var("url", $notify_item_value["url"]);
                                                $tpl->set_var("url_title", $notify_item_value["url"]);
                                                $tpl->parse("SezUrl", false);
                                            } else {
                                                $tpl->set_var("SezUrl", "");
                                            }

                                            $tpl->set_var("notify_url", $notify_item_value["notify_url"]);
                                            $tpl->parse("SezNotifyItem", true);
                                        }
                                    }
                                    $tpl->parse("SezNotify", true);  
                                }
                            }
                        }
                    }
                    $tpl->set_var("notify_url", FF_SITE_PATH . VG_SITE_ADMINNOTIFY);
                    
                    $tpl->parse("SezNotifyList", false);
                }
            }
            $tpl_layer->set_var("notify", $tpl->rpparse("main", false));
        }
    }
}

function system_layer_quickpanel($oPage, $tpl_layer) 
{
    if($oPage->layer == ffGetFilename(VG_SITE_ADMIN) || $oPage->layer == ffGetFilename(VG_SITE_RESTRICTED) || $oPage->layer == ffGetFilename(VG_SITE_MANAGE)) {
        $filename = null;
        if(is_file(FF_DISK_PATH . "/themes/" . $oPage->theme . "/layouts/quickpanel_" . $oPage->layer . ".html")) {
            $filename = FF_DISK_PATH . "/themes/" . $oPage->theme . "/layouts/quickpanel_" . $oPage->layer . ".html";
        } elseif(is_file(FF_DISK_PATH . "/themes/" . THEME_INSET . "/layouts/quickpanel_" . $oPage->layer . ".html")) {
            $filename = FF_DISK_PATH . "/themes/" . THEME_INSET . "/layouts/quickpanel_" . $oPage->layer . ".html";
        }
        if($filename !== null) {
			$oPage->tplAddJs("layerProcess", "layerprocess.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/system");
            $tpl = ffTemplate::factory(ffCommon_dirname($filename));
            $tpl->load_file("quickpanel_" . $oPage->layer . ".html", "main");

            $tpl->set_var("site_path", FF_SITE_PATH);
            $tpl->set_var("theme_inset", THEME_INSET);
            $tpl->set_var("theme", $oPage->theme);
			$tpl->set_var("frontend_icon", Cms::getInstance("frameworkcss")->get("vg-fontend", "icon-tag"));
			$tpl->set_var("logout_icon", Cms::getInstance("frameworkcss")->get("power-off", "icon-tag", "2x"));
            
            if(Auth::env("AREA_ADMIN_SHOW_MODIFY")) {
            	$tpl->set_var("admin_icon", Cms::getInstance("frameworkcss")->get("vg-admin", "icon-tag"));
                $tpl->parse("SezAdmin", false);
            } else {
                $tpl->set_var("SezAdmin", "");
            }
            if(Auth::env("AREA_RESTRICTED_SHOW_MODIFY")) {
            	$tpl->set_var("restricted_icon", Cms::getInstance("frameworkcss")->get("vg-restricted", "icon-tag"));
                $tpl->parse("SezRestricted", false);
            } else {
                $tpl->set_var("SezRestricted", "");
            }
            if(Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")) {
            	$tpl->set_var("manage_icon", Cms::getInstance("frameworkcss")->get("vg-manage", "icon-tag"));
                $tpl->parse("SezManage", false);
            } else {
                $tpl->set_var("SezManage", "");
            }
            
            $tpl_layer->set_var("quickpanel", $tpl->rpparse("main", false));
        }
    }
}

function system_layer_languages($oPage, $tpl_layer) 
{
    if($oPage->layer == ffGetFilename(VG_SITE_ADMIN) || $oPage->layer == ffGetFilename(VG_SITE_RESTRICTED) || $oPage->layer == ffGetFilename(VG_SITE_MANAGE)) {
        $filename = null;
        if(is_file(FF_DISK_PATH . "/themes/" . $oPage->theme . "/layouts/languages_" . $oPage->layer . ".html")) {
            $filename = FF_DISK_PATH . "/themes/" . $oPage->theme . "/layouts/languages_" . $oPage->layer . ".html";
        } elseif(is_file(FF_DISK_PATH . "/themes/" . THEME_INSET . "/layouts/languages_" . $oPage->layer . ".html")) {
            $filename = FF_DISK_PATH . "/themes/" . THEME_INSET . "/layouts/languages_" . $oPage->layer . ".html";
        }
		$css = null;
		if(is_file(FF_DISK_PATH . "/themes/" . $oPage->theme . "/css/lang-flags16.css")) {
			$css = "f16";
			$oPage->tplAddCss("langFlag", "lang-flags16.css", FF_THEME_DIR . "/" . $oPage->theme . "/css");
		} elseif(is_file(FF_DISK_PATH . "/themes/" . THEME_INSET . "/css/lang-flags16.css")) {
			$css = "f16";
            $oPage->tplAddCss("langFlag", "lang-flags16.css", FF_THEME_DIR . "/" . THEME_INSET . "/css");
		} elseif(is_file(FF_DISK_PATH . "/themes/" . $oPage->theme . "/css/lang-flag32.css")) {
			$css = "f32";
            $oPage->tplAddCss("langFlag", "lang-flags32.css", FF_THEME_DIR . "/" . $oPage->theme . "/css");
		} elseif(is_file(FF_DISK_PATH . "/themes/" . THEME_INSET . "/css/lang-flags32.css")) {
			$css = "f32";
            $oPage->tplAddCss("langFlag", "lang-flags32.css", FF_THEME_DIR . "/" . THEME_INSET . "/css");
		}
		if($filename !== null && $css) {
			$oPage->tplAddJs("layerProcess", "layerprocess.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/system");
            $db = ffDB_Sql::factory(); 
			
            $tpl = ffTemplate::factory(ffCommon_dirname($filename));
            $tpl->load_file("languages_" . $oPage->layer . ".html", "main");

            $tpl->set_var("site_path", FF_SITE_PATH);
            $tpl->set_var("theme_inset", THEME_INSET);
            $tpl->set_var("theme", $oPage->theme);

            $sSQL = "SELECT " . FF_PREFIX . "languages.*
						FROM " . FF_PREFIX . "languages
						WHERE " . FF_PREFIX . "languages.status = '1'
						ORDER BY " . FF_PREFIX . "languages.description";
            $db->query($sSQL);
            if($db->nextRecord()) {
				$tpl->set_var("flag_dim", $css);
                do {
                    $code = $db->getField("code", "Text", true);
                    $tpl->set_var("code", $code);
                    $tpl->set_var("description", $db->getField("description", "Text", true));
					$tpl->set_var("flag_lang", "flag " . $db->getField("tiny_code", "Text", true));
                    if($code == LANGUAGE_INSET) {
						$tpl->set_var("flag_lang_active", "flag " . $db->getField("tiny_code", "Text", true));
                        $tpl->parse("SezActualLang", false);
                    } else {
                        $tpl->set_var("show_files", "?lang=" . $code);
                        $tpl->parse("SezLang", true);
                    }
						
                } while($db->nextRecord());
            }
            
            $tpl_layer->set_var("languages", $tpl->rpparse("main", false));
        }
    }
}


function system_layer_restricted($cm) 
{
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();
    
    $cm->oPage->minify = false; //previene l'eliminazione degli invii a capo all'interno delle TEXTAREA

	if(check_function("set_header_page")) {
		set_header_page(null, false, false, false, false); 	
	}

    check_function("set_generic_tags");

   /* if (!Auth::env("AREA_RESTRICTED_SHOW_MODIFY")) {
        prompt_login(null, FF_SITE_PATH . ($globals->page["alias"] ? "" : VG_SITE_RESTRICTED) . "/login");
        //ffRedirect(FF_SITE_PATH . VG_SITE_RESTRICTED . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
    }*/
    
    if(check_function("system_ffrecord_process_print_button")) {
        ffRecord::addEvent("on_before_process_interface", "system_ffrecord_process_print_button" , ffEvent::PRIORITY_DEFAULT);
    }


    $menu_edit = array("modify" => "/modify");

    
    if(Auth::env("AREA_VGALLERY_SHOW_SEO")) {
        $menu_edit["seo"] = "/seo";
    }
    if(Auth::env("AREA_VGALLERY_SHOW_PERMISSION") && Cms::env("ENABLE_STD_PERMISSION")) {
        $menu_edit["permission"] = "/permission";
    }

    $menu_structure = array();
    if(Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
        $menu_structure["properties"] = "/properties";
    }
    
    
    
    $restricted_content = glob(FF_DISK_PATH . "/contents/restricted/*");
    if(is_array($restricted_content) && count($restricted_content)) {
        foreach($restricted_content AS $real_file) {
            if(is_dir($real_file)) {
                mod_restricted_add_menu_child(basename($real_file), VG_SITE_RESTRICTED . "/" . basename($real_file), ffTemplate::_get_word_by_code("mn_" . basename($real_file)));
                //if(strpos($cm->path_info, VG_SITE_RESTRICTED . "/" . basename($real_file)) !== false) {
                    $restricted_sub_content = glob(FF_DISK_PATH . "/contents/restricted/" . basename($real_file) . "/*");
                    if(is_array($restricted_sub_content) && count($restricted_sub_content)) {
                        foreach($restricted_sub_content AS $real_sub_file) {
                            if(is_dir($real_sub_file) && (file_exists($real_sub_file . "/index." . FF_PHP_EXT) || file_exists($real_sub_file . "/index." . "html"))) {
                                mod_restricted_add_menu_sub_element(basename($real_file), basename($real_sub_file), VG_SITE_RESTRICTED . "/" . basename($real_file) . "/" . basename($real_sub_file), ffTemplate::_get_word_by_code("mt_" . basename($real_sub_file)));
                            }
                        }
                    }                        
                //}
            }
        }
    }

    if((Auth::env("AREA_VGALLERY_SHOW_MODIFY") || Auth::env("AREA_VGALLERY_SHOW_ADDNEW") || Auth::env("AREA_VGALLERY_SHOW_DELETE") || Auth::env("AREA_VGALLERY_SHOW_SEO") || Auth::env("AREA_VGALLERY_SHOW_PERMISSION") || Auth::env("AREA_ECOMMERCE_SHOW_MODIFY"))) {
        $db_rescricted_detail = ffDB_Sql::factory();

        $db->query("SELECT vgallery.* 
                               FROM vgallery
                               WHERE
                                vgallery.status = 1");
        if($db->nextRecord()) {
            $vg_data = array();
            do {
                $vg_data[$db->getField("ID", "Number", true)]["full_path"] = "/" . $db->getField("name", "Text", true);
                $vg_data[$db->getField("ID", "Number", true)]["name"] = $db->getField("name", "Text", true);
            } while($db->nextRecord());    

            if(is_array($vg_data) && count($vg_data)) {
                if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
                    get_file_permission(null, "vgallery_nodes", array_keys($vg_data));

                if(!MOD_RES_FULLBAR && !array_key_exists("fullbar", $cm->modules["restricted"])) 
	                mod_restricted_add_menu_child("vgallery", VG_SITE_RESTRICTED . "/vgallery", ffTemplate::_get_word_by_code("vgallery_menu_title"));

                foreach($vg_data AS $vg_data_key => $vg_data_value) {
                    if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
                        $file_permission = get_file_permission($vg_data_value["full_path"], "vgallery_nodes");

                    if(!check_mod($file_permission, 1, false))
                        continue;


 					if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])) {
 						mod_restricted_add_menu_child($vg_data_value["name"], VG_SITE_RESTRICTED . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"]), ffTemplate::_get_word_by_code(ucwords(str_replace("-", " ", $vg_data_value["name"]))));

                        $db_rescricted_detail->query("SELECT vgallery_nodes.name AS name
                                                        , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
                                                        , ( SELECT count(*) FROM vgallery_nodes WHERE (vgallery_nodes.parent = full_path OR vgallery_nodes.parent LIKE CONCAT(full_path, '/%')) AND NOT(vgallery_nodes.is_dir > 0)) AS count_tot_elem
                                                      FROM vgallery_nodes
                                                      WHERE 
                                                        vgallery_nodes.ID_vgallery = " . $db_rescricted_detail->toSql($vg_data_key, "Number")  . "
                                                        AND (vgallery_nodes.is_dir > 0)
                                                        AND CONCAT(vgallery_nodes.parent, vgallery_nodes.name) <> " . $db_rescricted_detail->toSql("/" . $vg_data_value["name"])  . "
                                                        AND vgallery_nodes.parent = " . $db_rescricted_detail->toSql("/" . $vg_data_value["name"] /*. $actual_parent*/) . "
                                                      ORDER BY full_path
                                                        
                                                        
                                                        ");
                        if($db_rescricted_detail->nextRecord()) {
                            mod_restricted_add_menu_sub_element($vg_data_value["name"]
                                , $vg_data_value["name"]
                                , VG_SITE_RESTRICTED . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"])
                                , ffTemplate::_get_word_by_code("vgallery_" . $vg_data_value["name"] . "_all"));

                            do {
                                mod_restricted_add_menu_sub_element($vg_data_value["name"]
                                , $db_rescricted_detail->getField("name")->getValue()
                                , VG_SITE_RESTRICTED . "/vgallery" . strtolower($db_rescricted_detail->getField("full_path")->getValue())
                                , "(" . $db_rescricted_detail->getField("count_tot_elem")->getValue() . ") " .  ucwords(str_replace("-", " ", $db_rescricted_detail->getField("name")->getValue())));
                                
                            } while($db_rescricted_detail->nextRecord());
                        }
                    } else {
						mod_restricted_add_menu_sub_element("vgallery", "vgallery_" . $vg_data_value["name"], VG_SITE_RESTRICTED . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"]), ffTemplate::_get_word_by_code(ucwords(str_replace("-", " ", $vg_data_value["name"]))), "[QUERY_STRING]");
                    }
                }
            }
        }
    }
    if ((Auth::env("AREA_PUBLISHING_SHOW_ADDNEW") || Auth::env("AREA_PUBLISHING_SHOW_MODIFY") || Auth::env("AREA_PUBLISHING_SHOW_DELETE") || Auth::env("AREA_PUBLISHING_SHOW_DETAIL") || AREA_PUBLISHING_SHOW_PREVIEW)) {
        $db->query("SELECT 
                                    publishing.*
                                    , IF(ISNULL(layout.ID), 'not_visible', 'visible') AS visible
                                    , (SELECT 
                                            count(*) AS cont_elem
                                        FROM rel_nodes 
                                        WHERE 
                                        (
                                            ID_node_src = publishing.ID 
                                            AND contest_src = 'publishing'
                                        ) 
                                        OR 
                                        (
                                            ID_node_dst = publishing.ID
                                            AND contest_dst ='publishing'
                                        )
                                    ) AS count_elem
                               FROM publishing
                                LEFT JOIN layout ON layout.value REGEXP CONCAT('(.*)_', publishing.ID)
                                LEFT JOIN layout_type ON layout_type.ID = layout.ID_type AND layout_type.name = 'PUBLISHING'
                               WHERE
                                1");
        if($db->nextRecord()) {
            mod_restricted_add_menu_child("publishing", VG_SITE_RESTRICTED . "/publishing", ffTemplate::_get_word_by_code("publishing"));
            if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
                || strpos($cm->path_info, VG_SITE_RESTRICTED . "/publishing") === 0
            ) {
                if(isset($menu_edit[basename($cm->path_info)])) {  
                    mod_restricted_add_menu_sub_element("publishing", "modify", VG_SITE_RESTRICTED . "/publishing/modify", ffTemplate::_get_word_by_code("publishing_modify"), "[QUERY_STRING]");
                    mod_restricted_add_menu_sub_element("publishing", "properties", VG_SITE_RESTRICTED . "/publishing/properties", ffTemplate::_get_word_by_code("publishing_properties"), "[QUERY_STRING]");
                } else {
                    do {
                        mod_restricted_add_menu_sub_element("publishing", $db->getField("name")->getValue(), VG_SITE_RESTRICTED . "/publishing/detail/" . ffCommon_url_rewrite($db->getField("name")->getValue()), set_generic_tags(ffTemplate::_get_word_by_code($db->getField("visible")->getValue()) . " (" . $db->getField("count_elem")->getValue() . "/" . $db->getField("limit")->getValue() . ") " . $db->getField("name")->getValue()), "keys[ID]=" . $db->getField("ID")->getValue() . "&ret_url=" . urlencode(VG_SITE_RESTRICTED . "/publishing"));
                    } while($db->nextRecord());
                }
            }
        }
    }
    if ((Auth::env("AREA_DRAFT_SHOW_MODIFY") || Auth::env("AREA_DRAFT_SHOW_ADDNEW") || Auth::env("AREA_DRAFT_SHOW_DELETE"))) {
        $db->query("SELECT drafts.*
                                    , IF(ISNULL(layout.ID), 'not_visible', 'visible') AS visible
                               FROM drafts
                                    LEFT JOIN layout ON layout.value = drafts.ID
                                    LEFT JOIN layout_type ON layout_type.ID = layout.ID_type AND layout_type.name = 'STATIC_PAGE_BY_DB'
                               WHERE 1
                                " . ($globals->ID_domain > 0
                                    ? " AND drafts.ID_domain = " . $db->toSql($globals->ID_domain, "Number")
                                    : ""
                                )
                            );
        if($db->nextRecord()) {
            mod_restricted_add_menu_child("draft", VG_SITE_RESTRICTED . "/draft", ffTemplate::_get_word_by_code("draft"));
            if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
                || strpos($cm->path_info, VG_SITE_RESTRICTED . "/draft") === 0
            ) {
                do {
                    mod_restricted_add_menu_sub_element("draft", $db->getField("name")->getValue(), VG_SITE_RESTRICTED . "/draft/modify/" . ffCommon_url_rewrite($db->getField("name")->getValue()), set_generic_tags(ffTemplate::_get_word_by_code($db->getField("visible")->getValue()) . " " . $db->getField("name")->getValue()), "keys[ID]=" . $db->getField("ID")->getValue() . "&ret_url=" . urlencode(VG_SITE_RESTRICTED . "/draft"));
                } while($db->nextRecord());
            }
        }
    }
    if ((Auth::env("AREA_GALLERY_SHOW_MODIFY") || Auth::env("AREA_GALLERY_SHOW_ADDNEW") || Auth::env("AREA_GALLERY_SHOW_DELETE") || Auth::env("AREA_GALLERY_SHOW_PERMISSION") || Auth::env("AREA_ECOMMERCE_SHOW_MODIFY"))) {
        mod_restricted_add_menu_child("resources", VG_SITE_RESTRICTED . "/resources", ffTemplate::_get_word_by_code("resources"));

        if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
            || strpos($cm->path_info, VG_SITE_RESTRICTED . "/resources") === 0
        ) {
            if(strpos($cm->path_info, VG_SITE_RESTRICTED . "/resources") === 0 && (isset($menu_edit[basename($cm->path_info)]) || isset($menu_structure[basename($cm->path_info)]))) {
                foreach($menu_edit AS $edit_key => $edit_value) {
                    if(constant("AREA_GALLERY_SHOW_" . strtoupper($edit_key))) {
                        mod_restricted_add_menu_sub_element("resources", "resources_" . $edit_key, ffCommon_dirname($cm->path_info) . $edit_value, ffTemplate::_get_word_by_code("resources_" . $edit_key), "[QUERY_STRING]");
                    }
                }
                foreach($menu_structure AS $structure_key => $structure_value) {
                    if(constant("AREA_" . strtoupper($structure_key) . "_SHOW_MODIFY")) {
                        mod_restricted_add_menu_sub_element("resources", "resources_" . $structure_key, ffCommon_dirname($cm->path_info) . $structure_value, ffTemplate::_get_word_by_code("resources_" . $structure_key), "[QUERY_STRING]");
                    }
                }
            } elseif(basename(ffCommon_dirname($cm->path_info)) == "add") {
                mod_restricted_add_menu_sub_element("resources", "resources_dir", ffCommon_dirname($cm->path_info) . "/dir", ffTemplate::_get_word_by_code("resources_dir"), "[QUERY_STRING]");
                mod_restricted_add_menu_sub_element("resources", "resources_item", ffCommon_dirname($cm->path_info) . "/item", ffTemplate::_get_word_by_code("resources_item"), "[QUERY_STRING]");
            } else {
                
            }
        }
    }

    if((Auth::env("AREA_MODULES_SHOW_MODIFY") || Auth::env("MODULE_SHOW_CONFIG"))) {
        $db->query("SELECT module_form.*
                                    , IF(ISNULL(layout.ID), 'not_visible', 'visible') AS visible
                                    , (SELECT count(*) FROM module_form_nodes WHERE module_form_nodes.ID_module = module_form.ID) AS count_nodes 
                               FROM module_form
                                LEFT JOIN layout ON layout.value = 'form' AND layout.params = module_form.name
                                LEFT JOIN layout_type ON layout_type.ID = layout.ID_type AND layout_type.name = 'MODULE'
                               WHERE
                                1");
        if($db->nextRecord()) {
            if(!array_key_exists("form", $cm->modules["restricted"]["menu"])) {
                mod_restricted_add_menu_child("form", VG_SITE_RESTRICTED . "/modules/form", ffTemplate::_get_word_by_code("forms"));    
                $form_default = true;
            } else {
                $form_default = false;
            }

            if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
                || strpos($cm->path_info, $cm->modules["restricted"]["menu"]["form"]["path"]) === 0
            ) {
                do {
                    //"ret_url=" . urlencode($cm->modules["restricted"]["menu"]["form"]["path"])
                    mod_restricted_add_menu_sub_element("form", $db->getField("name")->getValue(), $cm->modules["restricted"]["menu"]["form"]["path"] . "/detail/" . ffCommon_url_rewrite($db->getField("name")->getValue()), set_generic_tags(($form_default ? ffTemplate::_get_word_by_code($db->getField("visible")->getValue()) : "") . " (" . $db->getField("count_nodes")->getValue() . ") " . ffTemplate::_get_word_by_code($db->getField("name")->getValue())), null);
                } while($db->nextRecord());
            }
        }
    }

    if(Auth::env("AREA_INTERNATIONAL_SHOW_MODIFY")) {
        mod_restricted_add_menu_child("wordcode", VG_SITE_RESTRICTED . "/wordcode", ffTemplate::_get_word_by_code("wordcode"));
    }
	 if(1) {
        mod_restricted_add_menu_child("tags", VG_SITE_RESTRICTED . "/tags", ffTemplate::_get_word_by_code("tags"));
    }
	 if(1) {
        mod_restricted_add_menu_child("place", VG_SITE_RESTRICTED . "/place", ffTemplate::_get_word_by_code("place"));
    }
    if(Auth::env("AREA_SITEMAP_SHOW_MODIFY")) {
        mod_restricted_add_menu_child("sitemap", VG_SITE_RESTRICTED . "/site-map", ffTemplate::_get_word_by_code("sitemap"));
    }
    
    //Users
    if(Auth::env("AREA_USERS_SHOW_MODIFY")) {
        mod_restricted_add_menu_child("users", VG_SITE_RESTRICTED . "/users", ffTemplate::_get_word_by_code("mn_users"));
        if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
            || strpos($cm->path_info, VG_SITE_RESTRICTED . "/users") === 0
        ) {
            $sSQL = "
                    SELECT 'noactive' AS ID, 
                        " . $db->toSql("anagraph_noactive") . " AS name
                        , (SELECT count(*) FROM anagraph WHERE anagraph.status = 0) AS count_nodes 
                    UNION
                    SELECT 'nopublic' AS ID, 
                        " . $db->toSql("anagraph_nopublic") . " AS name
                        , (SELECT count(*) FROM anagraph WHERE anagraph.visible = 0) AS count_nodes 
                    UNION
                    SELECT anagraph_categories.name AS ID
                        , anagraph_categories.name
                        , (SELECT count(*) FROM anagraph WHERE FIND_IN_SET(anagraph_categories.ID, anagraph.categories)) AS count_nodes 
                    FROM anagraph_categories
                    UNION
                    SELECT 'nocategory' AS ID, 
                        " . $db->toSql("anagraph_nocategory") . " AS name
                        , (SELECT count(*) FROM anagraph WHERE anagraph.categories = '') AS count_nodes 
                    ";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    mod_restricted_add_menu_sub_element("users", $db->getField("ID", "Text", true), VG_SITE_RESTRICTED . "/users/" . ffCommon_url_rewrite($db->getField("ID", "Text", true)), " (" . number_format($db->getField("count_nodes", "Number", true), "0", ",", ".") . ") " . ffTemplate::_get_word_by_code($db->getField("name", "Text", true)));
                } while($db->nextRecord());
            } 
        }
    }

    if(Auth::env("AREA_GROUPS_SHOW_MODIFY")) {
        mod_restricted_add_menu_child("groups", VG_SITE_RESTRICTED . "/groups", ffTemplate::_get_word_by_code("groups"));
    }
    
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_administration");
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_notify");
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_quickpanel");
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_languages");

    if(check_function("system_set_css"))
        system_set_css($cm->oPage, $globals->settings_path, true);
    if(check_function("system_set_js"))
        system_set_js($cm->oPage, $globals->settings_path, false);

}

function system_layer_admin($cm) 
{
    $globals = ffGlobals::getInstance("gallery");

	$cm->oPage->minify = false; //previene l'eliminazione degli invii a capo all'interno delle TEXTAREA
	if(check_function("set_header_page")) {
		set_header_page(null, false, false, false, false); 	
	}

    /*if (!Auth::env("AREA_ADMIN_SHOW_MODIFY")) {
        prompt_login(null, FF_SITE_PATH . ($globals->page["alias"] ? "" : VG_SITE_ADMIN) . "/login");
        //ffRedirect(FF_SITE_PATH . VG_SITE_ADMIN . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
    }*/
    
    if(MASTER_CONTROL) {
        $cm->modules["restricted"]["menu"]["config"]["elements"]["domain"]["hide"] = false;
    } else {
        $cm->modules["restricted"]["menu"]["config"]["elements"]["domain"]["hide"] = true;
    }

    $admin_content = glob(FF_DISK_PATH . "/contents/admin/*");
    if(is_array($admin_content) && count($admin_content)) {
        foreach($admin_content AS $real_file) {
            if(is_dir($real_file)) {
                mod_restricted_add_menu_child(basename($real_file), VG_SITE_ADMIN . "/" . basename($real_file), ffTemplate::_get_word_by_code("mn_" . basename($real_file)));
                //if(strpos($cm->path_info, VG_SITE_ADMIN . "/" . basename($real_file)) !== false) {
                    $admin_sub_content = glob(FF_DISK_PATH . "/contents/admin/" . basename($real_file) . "/*");
                    if(is_array($admin_sub_content) && count($admin_sub_content)) {
                        foreach($admin_sub_content AS $real_sub_file) {
                            if(is_dir($real_sub_file) && (file_exists($real_sub_file . "/index." . FF_PHP_EXT) || file_exists($real_sub_file . "/index." . "html"))) {
                                mod_restricted_add_menu_sub_element(basename($real_file), basename($real_sub_file), VG_SITE_ADMIN . "/" . basename($real_file) . "/" . basename($real_sub_file), ffTemplate::_get_word_by_code("mt_" . basename($real_sub_file)));
                            }
                        }
                    }                        
                //}
            }
        }
    }

    $module_file = glob(FF_DISK_PATH . "/conf/gallery/modules/*");
    if(is_array($module_file) && count($module_file)) {
        foreach($module_file AS $real_file) {
            if(is_dir($real_file)) {
                $relative_file = basename($real_file);
                
                mod_restricted_add_menu_sub_element("content", $relative_file, VG_SITE_ADMIN . "/content/modules/" . $relative_file . "/config", ffTemplate::_get_word_by_code("mn_" . $relative_file));
                
            }
        }
    }
    
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_administration");
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_notify");
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_quickpanel");
    $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_languages");
              
    if(check_function("system_set_css"))
        system_set_css($cm->oPage, $globals->settings_path, true);
    if(check_function("system_set_js"))
        system_set_js($cm->oPage, $globals->settings_path, false);
}

function system_layer_manage($cm) 
{
    if(!Cms::env("AREA_SHOW_ECOMMERCE")) {
        ffRedirect(FF_SITE_PATH, 301);
    }

    $globals = ffGlobals::getInstance("gallery");
	$db = ffDB_Sql::factory();

	$cm->oPage->minify = false; //previene l'eliminazione degli invii a capo all'interno delle TEXTAREA

	if(check_function("set_header_page")) {
		set_header_page(null, false, false, false, false); 	
	}

	check_function("set_generic_tags");

	/*if (!Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")) {
	    prompt_login(null, FF_SITE_PATH . ($globals->page["alias"] ? "" : VG_SITE_MANAGE) . "/login");
	    //ffRedirect(FF_SITE_PATH . VG_SITE_MANAGE . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
	}*/

	if($cm->path_info == VG_SITE_MANAGE) 
	    ffRedirect(FF_SITE_PATH . VG_SITE_MANAGE . "/operations/" . date("Y", time()));


	$menu_structure = array(
	                            "settings" => "/settings"
	                            , "basic" => "/basic"
	                            , "pricelist_bytime" => "/time"
	                            , "pricelist_byqta" => "/qta"
	                            , "specialsupport" => "/specialsupport"
	                            , "addstock" => "/addstock"
	                        );
	//Anagraph
	if ((Auth::env("AREA_ANAGRAPH_SHOW_MODIFY") || Auth::env("AREA_ANAGRAPH_SHOW_ADDNEW") || Auth::env("AREA_ANAGRAPH_SHOW_DELETE"))) {
	    mod_restricted_add_menu_child("anagraph", VG_SITE_MANAGE . "/anagraph", ffTemplate::_get_word_by_code("anagraph"), "", "", VG_SITE_MANAGE . "/anagraph/all");
	    $db->query("
	                        SELECT 'all' AS ID, 
	                            " . $db->toSql("anagraph_all") . " AS name
	                            , (SELECT count(*) FROM anagraph WHERE 1) AS count_nodes 
	                        UNION
	                        SELECT anagraph_categories.name AS ID
	                            , anagraph_categories.name
	                            , (SELECT count(*) FROM anagraph WHERE FIND_IN_SET(anagraph_categories.ID, anagraph.categories)) AS count_nodes 
	                        FROM anagraph_categories
	                        UNION
	                        SELECT 'users' AS ID, 
	                            " . $db->toSql("anagraph_users") . " AS name
	                            , (SELECT count(*) FROM anagraph WHERE anagraph.uid > 0) AS count_nodes 
	                        UNION
	                        SELECT 'nocategory' AS ID, 
	                            " . $db->toSql("anagraph_nocategory") . " AS name
	                            , (SELECT count(*) FROM anagraph WHERE anagraph.categories = '') AS count_nodes 
	                        ");
	    if($db->nextRecord()) {
	        if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
	            || strpos($cm->path_info, VG_SITE_MANAGE . "/anagraph") === 0
	        ) {
	            if(strpos($cm->path_info, VG_SITE_MANAGE . "/anagraph") === 0 && strpos($cm->path_info, "modify") !== false) {
	                $hide_anagraph_categories = true;
	            } else {
	                $hide_anagraph_categories = false;
	            }

	            do {
	                mod_restricted_add_menu_sub_element("anagraph", $db->getField("ID")->getValue(), VG_SITE_MANAGE . "/anagraph/" . ffCommon_url_rewrite($db->getField("ID")->getValue()), " (" . $db->getField("count_nodes")->getValue() . ") " . ffTemplate::_get_word_by_code($db->getField("name")->getValue()), null, "" , null, $hide_anagraph_categories);
	            } while($db->nextRecord());
	        }
	    }
	}
	//Products 
	if((Auth::env("AREA_VGALLERY_SHOW_MODIFY") || Auth::env("AREA_VGALLERY_SHOW_ADDNEW") || Auth::env("AREA_VGALLERY_SHOW_DELETE") || Auth::env("AREA_VGALLERY_SHOW_SEO") || Auth::env("AREA_VGALLERY_SHOW_PERMISSION") || Auth::env("AREA_ECOMMERCE_SHOW_MODIFY"))) {
	    $db_manage_detail = ffDB_Sql::factory();
	    $db->query("SELECT vgallery.* 
	                           FROM vgallery
	                           WHERE
	                            vgallery.status = 1
	                            AND vgallery.enable_ecommerce = '1'
	                            ");
	    if($db->nextRecord()) {
	        $vg_data = array();
	        do {
	            $vg_data[$db->getField("ID", "Number", true)]["full_path"] = "/" . $db->getField("name", "Text", true);
	            $vg_data[$db->getField("ID", "Number", true)]["name"] = $db->getField("name", "Text", true);
	        } while($db->nextRecord());    
	        
	        if(is_array($vg_data) && count($vg_data)) {
	            if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
	                get_file_permission(null, "vgallery_nodes", array_keys($vg_data));

				$sSQL_cond = "";
	            foreach($vg_data AS $vg_data_key => $vg_data_value) {
	                if(Cms::env("ENABLE_STD_PERMISSION") && check_function("get_file_permission"))
	                    $file_permission = get_file_permission($vg_data_value["full_path"], "vgallery_nodes");
	                if(!check_mod($file_permission, 1, false))
	                    continue;

	                mod_restricted_add_menu_child($vg_data_value["name"], VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"]), ffTemplate::_get_word_by_code("vgallery_" . $vg_data_value["name"]));

	                if(MOD_RES_FULLBAR || array_key_exists("fullbar", $cm->modules["restricted"])
	                    || strpos($cm->path_info, VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"])) === 0
	                ) {
	                    if(basename($cm->path_info) == "ecommerce") {
	                        foreach($menu_structure AS $structure_key => $structure_value) {
	                            if(constant("AREA_ECOMMERCE_" . strtoupper($structure_key) . "_SHOW_MODIFY")) {
	                                ffRedirect(VG_SITE_MANAGE . "/vgallery/" . ffCommon_url_rewrite($vg_data_value["name"]) . "/ecommerce" . $structure_value . "?". $cm->query_string);
	                            }
	                        }
	                    } elseif(isset($menu_structure[basename($cm->path_info)])) {
	                        $db_manage_detail->query("SELECT * FROM vgallery_nodes WHERE ID = " . $db_manage_detail->toSql($_REQUEST["keys"]["ID"], "Number"));
	                        if($db_manage_detail->nextRecord()) {
	                            if(check_function("ecommerce_get_file_properties_ecommerce"))
	                                $ecommerce_properties = ecommerce_get_file_properties_ecommerce(stripslash($db_manage_detail->getField("parent")->getValue()) . "/" . $db_manage_detail->getField("name")->getValue(), "vgallery_nodes");
	                        }
	                        if($ecommerce_properties["enable_ecommerce"]) {
	                            foreach($menu_structure AS $structure_key => $structure_value) {
	                                if(constant("AREA_ECOMMERCE_" . strtoupper($structure_key) . "_SHOW_MODIFY")) {
	                                    
	                                    if(strpos($structure_key, "_by") !== false && strlen($ecommerce_properties["type"]) && strpos($structure_key, $ecommerce_properties["type"]) === false)
	                                        continue;

	                                    mod_restricted_add_menu_sub_element($vg_data_value["name"], "ecommerce_" . $structure_key, ffCommon_dirname($cm->path_info) . $structure_value, ffTemplate::_get_word_by_code("ecommerce_" . $structure_key), "[QUERY_STRING]");
	                                }
	                            }
	                        } elseif(Auth::env("AREA_ECOMMERCE_SETTINGS_SHOW_MODIFY")) {
	                            mod_restricted_add_menu_sub_element($vg_data_value["name"], "ecommerce_settings", ffCommon_dirname($cm->path_info) . "/settings", ffTemplate::_get_word_by_code("ecommerce_settings"), "[QUERY_STRING]");
	                        } else {
	                            ffRedirect($_REQUEST["ret_url"]);
	                        }
	                    } else {
	                        /*$actual_parent = str_replace(VG_SITE_MANAGE . "/vgallery/" . $vg_data_value["name"], "", $cm->path_info);
	                        if($actual_parent != ffcommon_dirname($actual_parent)) {
	                            mod_restricted_add_menu_sub_element($vg_data_value["name"]
	                            , "backto"
	                            , VG_SITE_MANAGE . "/vgallery" . ffcommon_dirname("/" . $vg_data_value["name"] . $actual_parent)
	                            , ffTemplate::_get_word_by_code("back_to") . " " . str_replace("-", " ", basename(ffcommon_dirname("/" . $vg_data_value["name"] . $actual_parent)))
	                            , "[QUERY_STRING]");
	                        }*/

	                        if(Cms::env("AREA_ECOMMERCE_SHOW_LOCATION") && Cms::env("ECOMMERCE_CHARGE_METHOD")) {
	                            $location  = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["location"] : null);
	                        }
	                        
	                        $show_available  = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_available"] : true);
	                        $show_unavailable  = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_unavailable"] : false);
	                        $show_stock = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_stock"] : true);
	                        $show_error = (strlen($_REQUEST["frmAction"]) ? $_REQUEST["show_error"] : true);

	                        if($show_available && $show_unavailable && $show_stock && $show_error) {
	                            $sSQL_cond_count = "";
	                        } else {
	                            if($show_available) {
	                                $sSQL_cond .= " OR (qta > 0)";
	                            }
	                            if($show_unavailable) {
	                                $sSQL_cond .= " OR (qta = 0)";
	                            }
	                            if($show_stock) {
	                                $sSQL_cond .= " OR (NOT(useunic) AND usestock)";
	                            }
	                            if($show_error) {
	                                $sSQL_cond .= " OR (qta < 0 OR ISNULL(qta))";
	                            }

	                            $sSQL_cond_count = " 0 " . $sSQL_cond;
	                        }                            
	                        
	                        $db_manage_detail->query("SELECT vgallery_nodes.name AS name
	                                                        , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
	                                                        , ( SELECT count(*) FROM
	                                                            ( SELECT DISTINCT vgallery_nodes.ID
	                                                                , ecommerce_settings.useunic AS useunic
	                                                                , ecommerce_settings.usestock AS usestock 
	                                                                , IF(ISNULL(ecommerce_settings.ID)
	                                                                    , null
	                                                                    , ecommerce_settings.actual_qta
	                                                                ) AS qta
	                                                                , vgallery_nodes.parent AS parent
	                                                            FROM vgallery_nodes
	                                                                  LEFT JOIN ecommerce_settings ON ecommerce_settings.ID_items = vgallery_nodes.ID"
	                                                                . (Cms::env("AREA_ECOMMERCE_SHOW_LOCATION") && Cms::env("ECOMMERCE_CHARGE_METHOD")
	                                                                    ? ($location > 0 
	                                                                        ? " INNER JOIN ecommerce_settings_location ON ecommerce_settings_location.ID_items = vgallery_nodes.ID AND ecommerce_settings_location.ID_location = " . $db_manage_detail->toSql($location, "Number") . "
	                                                                            INNER JOIN ecommerce_location ON ecommerce_location.ID = ecommerce_settings_location.ID_location"
	                                                                        : " LEFT JOIN ecommerce_settings_location ON ecommerce_settings_location.ID_items = vgallery_nodes.ID
	                                                                            LEFT JOIN ecommerce_location ON ecommerce_location.ID = ecommerce_settings_location.ID_location"
	                                                                    )
	                                                                    : ""
	                                                                ) . "
	                                                            WHERE 
	                                                                IF(ISNULL(ecommerce_settings.ID)
	                                                                    , IF(
	                                                                            (
	                                                                                SELECT IF(ISNULL(ecommerce_settings.ID), 0, ecommerce_settings.cascading) 
	                                                                                FROM vgallery_nodes AS parent_nodes
	                                                                                    INNER JOIN ecommerce_settings ON ecommerce_settings.ID_items = parent_nodes.ID
	                                                                                WHERE vgallery_nodes.parent LIKE CONCAT(IF(parent_nodes.parent = '/', '', parent_nodes.parent), '/', parent_nodes.name, '%')
	                                                                                ORDER BY LENGTH(CONCAT(IF(parent_nodes.parent = '/', '', parent_nodes.parent))) DESC
	                                                                                LIMIT 1
	                                                                            ) > 0
	                                                                        , 1
	                                                                        , 0
	                                                                    )
	                                                                    , 1
	                                                                )
	                                                                AND IF(NOT(vgallery_nodes.is_dir > 0) OR (NOT(ISNULL(ecommerce_settings.ID)) AND (ecommerce_settings.basic_price > 0 OR NOT(ecommerce_settings.cascading) > 0))
	                                                                    , 1
	                                                                    , 0
	                                                                )
	                                                                " . (Cms::env("AREA_ECOMMERCE_SHOW_LOCATION") && Cms::env("ECOMMERCE_CHARGE_METHOD") && $location === "0"
	                                                                    ? " AND ISNULL(ecommerce_settings_location.ID) "
	                                                                    : ""
	                                                                ) . " 
	                                                                " . (strlen($sSQL_cond_count)
	                                                                    ? " HAVING " . $sSQL_cond_count
	                                                                    : ""
	                                                                ) . "
	                                                            ) AS tbl_src
	                                                            WHERE (tbl_src.parent = full_path OR tbl_src.parent LIKE CONCAT(full_path, '/%'))
	                                                        ) AS count_tot_elem
	                                                      FROM vgallery_nodes
	                                                      WHERE 
	                                                        vgallery_nodes.ID_vgallery = " . $db_manage_detail->toSql($vg_data_key, "Number")  . "
	                                                        AND (vgallery_nodes.is_dir > 0)
	                                                        AND CONCAT(vgallery_nodes.parent, vgallery_nodes.name) <> " . $db_manage_detail->toSql("/" . $vg_data_value["name"])  . "
	                                                        AND vgallery_nodes.parent = " . $db_manage_detail->toSql("/" . $vg_data_value["name"] /*. $actual_parent*/) . "
	                                                      ORDER BY full_path
	                                                        ");
	                        if($db_manage_detail->nextRecord()) {
	                            do {
	                                mod_restricted_add_menu_sub_element($vg_data_value["name"]
	                                , $db_manage_detail->getField("name")->getValue()
	                                , VG_SITE_MANAGE . "/vgallery" . $db_manage_detail->getField("full_path")->getValue()
	                                , "(" . $db_manage_detail->getField("count_tot_elem")->getValue() . ") " .  str_replace("-", " ", $db_manage_detail->getField("name")->getValue())
	                                , "[QUERY_STRING]");
	                                
	                            } while($db_manage_detail->nextRecord());
	                        }
	                    }
	                }
	            } 
	        }
	    }
	}
	//Discount
	if(Cms::env("AREA_ECOMMERCE_USE_COUPON") || Cms::env("AREA_ECOMMERCE_USE_PROMOTION")) {
	    mod_restricted_add_menu_child("discount", VG_SITE_MANAGE . "/discount", ffTemplate::_get_word_by_code("discount"));
	        
	    if(Cms::env("AREA_ECOMMERCE_USE_COUPON") && AREA_COUPON_SHOW_MODIFY) {
	        mod_restricted_add_menu_sub_element("discount", "coupon", VG_SITE_MANAGE . "/discount/coupon", ffTemplate::_get_word_by_code("discount_coupon"));
	    }
	}
	//Operation
	$operation_menu[] = "all";
	$db->query("SELECT DISTINCT YEAR(ecommerce_order.date) AS archive 
	                   FROM ecommerce_order
	                   ORDER BY ecommerce_order.date DESC");
	if($db->nextRecord()) {
	    mod_restricted_add_menu_child("operations", VG_SITE_MANAGE . "/operations", ffTemplate::_get_word_by_code("operations"), "", "", VG_SITE_MANAGE . "/operations/" . $db->getField("archive", "Text", true));
	    do {
	        $operation_menu[] = $db->getField("archive")->getValue();
	    } while($db->nextRecord());
	} else {
	    mod_restricted_add_menu_child("operations", VG_SITE_MANAGE . "/operations", ffTemplate::_get_word_by_code("operations"), "", "", VG_SITE_MANAGE . "/operations/" . "all");
	}     

	foreach($operation_menu AS $operation_menu_value) {
	    mod_restricted_add_menu_sub_element("operations"
	    , $operation_menu_value
	    , VG_SITE_MANAGE . "/operations/" . ffCommon_url_rewrite($operation_menu_value)
	    , $operation_menu_value
	    , $_SERVER["QUERY_STRING"]);
	}
	//Documents
	if(Auth::env("AREA_ECOMMERCE_SHOW_DOCUMENT")) {
	    mod_restricted_add_menu_child("documents", VG_SITE_MANAGE . "/documents", ffTemplate::_get_word_by_code("documents"));

	    if(Auth::env("AREA_BILL_SHOW_MODIFY")) {
	        if(Auth::env("AREA_ECOMMERCE_SHOW_ACTIVITY"))
	            mod_restricted_add_menu_sub_element("documents", "bill_sent", VG_SITE_MANAGE . "/documents/bill/sent", ffTemplate::_get_word_by_code("bill_sent"));
	        if(Auth::env("AREA_ECOMMERCE_SHOW_PASSIVITY"))
	            mod_restricted_add_menu_sub_element("documents", "bill_received", VG_SITE_MANAGE . "/documents/bill/received", ffTemplate::_get_word_by_code("bill_received"));
	    }
	    if(Auth::env("AREA_PAYMENTS_SHOW_MODIFY")) {
	        if(Auth::env("AREA_ECOMMERCE_SHOW_PASSIVITY"))
	            mod_restricted_add_menu_sub_element("documents", "payments_sent", VG_SITE_MANAGE . "/documents/payments/sent", ffTemplate::_get_word_by_code("payments_sent"));
	        if(Auth::env("AREA_ECOMMERCE_SHOW_ACTIVITY"))
	        mod_restricted_add_menu_sub_element("documents", "payments_received", VG_SITE_MANAGE . "/documents/payments/received", ffTemplate::_get_word_by_code("payments_received"));
	    }
	    if(1) {
	        if(Auth::env("AREA_ECOMMERCE_SHOW_ACTIVITY"))
	            mod_restricted_add_menu_sub_element("documents", "contracts_sent", VG_SITE_MANAGE . "/documents/contracts/sent", ffTemplate::_get_word_by_code("contracts_sent"));
	        if(Auth::env("AREA_ECOMMERCE_SHOW_PASSIVITY"))
	            mod_restricted_add_menu_sub_element("documents", "contracts_received", VG_SITE_MANAGE . "/documents/contracts/received", ffTemplate::_get_word_by_code("contracts_received"));
	    }
	}
	//Reports
	if(Cms::env("AREA_ECOMMERCE_SHOW_REPORT") && Auth::env("AREA_REPORT_SHOW_MODIFY")) {
	    mod_restricted_add_menu_child("reports", VG_SITE_MANAGE . "/reports", ffTemplate::_get_word_by_code("reports"));
	    
	    if(is_dir(FF_DISK_PATH . "/conf" . GALLERY_PATH_ECOMMERCE . "/reports")) {
	        $reports = glob(FF_DISK_PATH . "/conf" . GALLERY_PATH_ECOMMERCE . "/reports/*", GLOB_ONLYDIR);
	        if(is_array($reports) && count($reports)) {
	            foreach($reports AS $reports_dir) {
	                $report_dirname = ffGetFilename($reports_dir);
	                mod_restricted_add_menu_sub_element("reports", $report_dirname, VG_SITE_MANAGE . "/reports/" . $report_dirname, ffTemplate::_get_word_by_code("reports_" . strtolower($report_dirname)));
	            }
	        }
	    }
	}
	//Shipping
	if(Cms::env("AREA_ECOMMERCE_USE_SHIPPING") && Auth::env("AREA_ECOMMERCE_SHIPPINGPRICE_SHOW_MODIFY")) {
	    mod_restricted_add_menu_child("shipping", VG_SITE_MANAGE . "/shipping", ffTemplate::_get_word_by_code("shipping"));
	}

	//mod_restricted_add_menu_child("back", FF_SITE_PATH . "/", ffTemplate::_get_word_by_code("backtosite"));
	//ffErrorHandler::raise("mod_security: User Not Found!!!", E_USER_ERROR, null, get_defined_vars());             
	$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_administration");
	$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_notify");
	$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_quickpanel");
	$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_languages");

	if(check_function("system_set_css"))
	    system_set_css($cm->oPage, $globals->settings_path, true);
	if(check_function("system_set_js"))
	    system_set_js($cm->oPage, $globals->settings_path, false);

}


