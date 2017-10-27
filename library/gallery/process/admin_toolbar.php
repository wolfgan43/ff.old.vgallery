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
function process_admin_toolbar($ret_url = null, $user_path = "/", $theme, $sections, $css = array(), $js = array(), $international = array(), $seo = array(), $option = array()) 
{
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

    if(strlen($globals->strip_user_path) && strpos($ret_url, $globals->strip_user_path) === 0) {
        $ret_url = substr($ret_url, strlen($globals->strip_user_path));
	}

    $buffer = "";
    $count_application = 0;
    $count_ecommerce = 0;
    $count_tools = 0;

    $check_class = "checked";
    $check_icon = cm_getClassByFrameworkCss($check_class, "icon-tag");
    $uncheck_class = "unchecked";
    $uncheck_icon = cm_getClassByFrameworkCss($uncheck_class, "icon-tag");

    if(check_function("get_layout_settings"))
    	$layout_settings = get_layout_settings(NULL, "ADMIN");

    if($ret_url === NULL)
        $ret_url = $_SERVER["REQUEST_URI"];

    if(USE_ADMIN_AJAX) {
    	$suffix = "_ajax";
    	$cancel_dialog_url = "[CLOSEDIALOG]";
	} else {
		$suffix = "";
		$cancel_dialog_url = ($ret_url ? $ret_url : "/");
	}
    
    $tpl = ffTemplate::factory(get_template_cascading($user_path, "admin.html", ""));
    $tpl->load_file("admin" . $suffix . ".html", "main");
    
    $tpl->set_var("site_path", FF_SITE_PATH);
    $tpl->set_var("ret_url", urlencode($ret_url));

    $tpl->set_var("info_icon", cm_getClassByFrameworkCss("info", "icon-tag", "2x"));
    $tpl->set_var("logout_icon", cm_getClassByFrameworkCss("power-off", "icon-tag", "2x"));
    $tpl->set_var("hide_icon", cm_getClassByFrameworkCss("bars", "icon-tag", "2x"));
    $tpl->set_var("refresh_icon", cm_getClassByFrameworkCss("refresh", "icon-tag"));
    
    if($layout_settings["AREA_NAVADMIN_SHOW_TITLE"])
        $tpl->parse("SezTitle", false);
    else
        $tpl->set_var("SezTitle", "");

    if(AREA_ADMIN_SHOW_MODIFY) {
        $tpl->set_var("path", VG_SITE_ADMIN);
        $tpl->set_var("admin_icon", cm_getClassByFrameworkCss("vg-admin", "icon-tag"));        
        $tpl->parse("SezAdmin", false);
    }
    
    if(AREA_RESTRICTED_SHOW_MODIFY) {
        $tpl->set_var("path", VG_SITE_RESTRICTED);
        $tpl->set_var("restricted_icon", cm_getClassByFrameworkCss("vg-restricted", "icon-tag"));        
        $tpl->parse("SezRestricted", false);
    }
    
    if(AREA_ECOMMERCE_SHOW_MODIFY) {
        $tpl->set_var("path", VG_SITE_MANAGE);
        $tpl->set_var("manage_icon", cm_getClassByFrameworkCss("vg-manage", "icon-tag"));        
        $tpl->parse("SezManage", false);
    }

    
    if($layout_settings["AREA_NAVADMIN_SHOW_TOOLS"] || 1) {
        $tpl->set_var("tools_class", "");
        $tpl->set_var("tools_icon", cm_getClassByFrameworkCss("eraser", "icon-tag", "2x"));

        if(AREA_CHECKER_SHOW_MODIFY) {
            $tpl->set_var("cache_class", "");
            $tpl->set_var("cache_icon", cm_getClassByFrameworkCss("eraser", "icon-tag"));

        	$tpl->parse("SezAdminPanelToolsCache", false);
		} else {
			$tpl->set_var("SezAdminPanelToolsCache", "");
		}
        $tpl->parse("SezAdminPanelTools", false);
    } else {
        $tpl->set_var("SezAdminPanelTools", "");
    }
    //ffErrorHandler::raise("ad", E_USER_ERROR, null, get_defined_vars());
	if(AREA_THEME_SHOW_MODIFY) {
        $tpl->set_var("tpl_class", "");
        $tpl->set_var("tpl_icon", cm_getClassByFrameworkCss("building-o", "icon-tag", "2x"));
        $tpl->set_var("css_class", "");
        $tpl->set_var("css_icon", cm_getClassByFrameworkCss("css", "icon-tag", "2x"));

        $tpl->set_var("more_class", cm_getClassByFrameworkCss("retracted", "icon"));
        $tpl->set_var("more_reverse_class", cm_getClassByFrameworkCss("exanded", "icon"));
        
        $tpl->set_var("css_addnew_class", cm_getClassByFrameworkCss("addnew", "icon"));
        $tpl->set_var("css_preview_class", cm_getClassByFrameworkCss("preview", "icon"));
        $tpl->set_var("css_edit_class", cm_getClassByFrameworkCss("editrow", "icon"));
        $tpl->set_var("css_delete_class", cm_getClassByFrameworkCss("deleterow", "icon"));
        $tpl->set_var("js_class", "");
        $tpl->set_var("js_icon", cm_getClassByFrameworkCss("js", "icon-tag", "2x"));
        $tpl->set_var("js_addnew_class", cm_getClassByFrameworkCss("addnew", "icon"));
        $tpl->set_var("js_preview_class", cm_getClassByFrameworkCss("preview", "icon"));
        $tpl->set_var("js_edit_class", cm_getClassByFrameworkCss("editrow", "icon"));
        $tpl->set_var("js_delete_class", cm_getClassByFrameworkCss("deleterow", "icon"));

		$tpl->set_var("layout_manage_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/section/cm_modify." . FF_PHP_EXT . "?path=" . urlencode($user_path) . "&ret_url=" . urlencode($ret_url));
		
	    foreach($css AS $css_key => $css_value) {
			$tmp_path = "";
			$tmp_file = "";

			if($css_value["type"] != "text/css")
				continue;

    		$tpl->set_var("css_name", $css_key);

	        //$res = $oPage->doEvent("on_css_parse", array($oPage, $css_key, $css_value["path"], $css_value["file"]));
	        //$rc = end($res);
			$rc = null;
	        if ($rc === null)
	        {
				if ($css_value["path"] === null)
					$tmp_path = "/themes/" . $theme . "/css";
				elseif (strlen($css_value["path"]))
					$tmp_path = $css_value["path"];

				if ($css_value["file"] === null)
					$tmp_file = $css_key . ".css";
				else
				{
					$tmp_file = $css_value["file"];
				}
			}
			else
			{
				$tmp_path = $rc["path"];
				$tmp_file = $rc["file"];
			}

			$tmp_path = rtrim($tmp_path, "/") . "/";
			$tmp_file = ltrim($tmp_file, "/");

		    if(strpos($tmp_path . $tmp_file, "/themes/" . $theme) === false) {
		        $tpl->set_var("css_check_icon", $uncheck_icon);
		        $tpl->set_var("css_check_class", $uncheck_class);

    			if(strpos($tmp_path . $tmp_file, "/themes/" . THEME_INSET . "/css") === false) {
    				$tpl->set_var("css_default_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/css") . "&extype=static&ret_url=" . urlencode($ret_url));
    				$tpl->set_var("css_add_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/css") . "&extype=static&ret_url=" . urlencode($ret_url));
    				$tpl->parse("SezAdminPanelCssAdd", false);
				} else {
    				$tpl->set_var("css_default_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files&ret_url=" . urlencode($ret_url));
					$tpl->set_var("SezAdminPanelCssAdd", "");
				}

    			$tpl->set_var("css_view_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files&ret_url=" . urlencode($ret_url));
    			$tpl->parse("SezAdminPanelCssView", false);

    			$tpl->set_var("SezAdminPanelCssModify", "");
    			$tpl->set_var("SezAdminPanelCssDelete", "");
			} else {
		        $tpl->set_var("css_check_icon", $check_icon);
		        $tpl->set_var("css_check_class", $check_class);

    			$tpl->set_var("SezAdminPanelCssAdd", "");
				$tpl->set_var("SezAdminPanelCssView", "");
				
				if(basename($tmp_file) == "main.css") {
					$tpl->set_var("css_default_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&deletable=0&ret_url=" . urlencode($ret_url));
    				$tpl->set_var("css_edit_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&deletable=0&ret_url=" . urlencode($ret_url));
		            $tpl->parse("SezAdminPanelCssModify", false);
					$tpl->set_var("SezAdminPanelCssDelete", "");
				} else {
					$tpl->set_var("css_default_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&ret_url=" . urlencode($ret_url));
    				$tpl->set_var("css_edit_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&ret_url=" . urlencode($ret_url));
		            $tpl->parse("SezAdminPanelCssModify", false);
		            if(USE_ADMIN_AJAX) {
			            $tpl->set_var("css_delete_path", urlencode(ffDialog(TRUE,
			                                                        "yesno",
			                                                        ffTemplate::_get_word_by_code("admin_erase_title"),
			                                                        ffTemplate::_get_word_by_code("admin_erase_description"),
			                                                        $cancel_dialog_url,
			                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=stativ&ret_url=" . urlencode($ret_url) . "&" . "ThemeModify_frmAction=confirmdelete",
			                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify" . "/dialog")
			                                            ));
					} else {
			            $tpl->set_var("css_delete_path", ffDialog(TRUE,
			                                                        "yesno",
			                                                        ffTemplate::_get_word_by_code("admin_erase_title"),
			                                                        ffTemplate::_get_word_by_code("admin_erase_description"),
			                                                        $cancel_dialog_url,
			                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=stativ&ret_url=" . urlencode($ret_url) . "&" . "ThemeModify_frmAction=confirmdelete",
			                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify" . "/dialog")
			                                            );
					}
    				$tpl->parse("SezAdminPanelCssDelete", false);
				}
			}

    		$tpl->parse("SezAdminPanelCss", true);
		} reset($css);
		$tpl->set_var("css_add_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/add." . FF_PHP_EXT . "?source=&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/css") . "&extype=static&ret_url=" . urlencode($ret_url));

	    foreach($js AS $js_key => $js_value) {
			$tmp_path = "";
			$tmp_file = "";

	        if($js_value["embed"])
	            continue;
	            
    		$tpl->set_var("js_name", $js_key);

	        //$res = $oPage->doEvent("on_js_parse", array($oPage, $js_key, $js_value["path"], $js_value["file"]));
	        //$rc = end($res);
	        $rc = null;
	        if ($rc === null)
	        {
		        if ($js_value["path"] === null)
		            $tmp_path = "/themes/" . $theme . "/javascript/";
				elseif (strlen($js_value["path"])) {
					if (
						substr(strtolower($js_value["path"]), 0, 7) == "http://"
						|| substr(strtolower($js_value["path"]), 0, 8) == "https://"
					)
		                continue;
	                    //$tmp_path = $js_value["path"] . "/";
					elseif (strpos($js_value["path"], cm_getModulesExternalPath()) === 0)
						$tmp_path = $js_value["path"];
		            else
						$tmp_path = $js_value["path"] . "/";
				
				}
				
		        if ($js_value["file"] === null)
		            $tmp_file = $js_key . ".js";
				elseif (strpos($tmp_path, cm_getModulesExternalPath()) === 0)
					$tmp_file = "";
		        else
		        {
		            $tmp_file = $js_value["file"];
		        }
	        }
	        else
	        {
	            $tmp_path = $rc["path"];
	            $tmp_file = $rc["file"];
	        }

			if (strpos($tmp_path, cm_getModulesExternalPath()) === 0)
				$tmp_path = preg_replace("/^" . preg_quote(cm_getModulesExternalPath(), "/") . "(\/[^\/]+)/", CM_MODULES_PATH. "\$1/themes", $tmp_path);
		    
		    if(!file_exists(FF_DISK_PATH . $tmp_path . $tmp_file))
		        $tmp_path = FF_SITE_PATH . "/themes/library/" . $js_key . "/"; 

		    if(strpos($tmp_path . $tmp_file, "/themes/" . $theme) !== false) {
		        $tpl->set_var("js_check_icon", $check_icon);
		        $tpl->set_var("js_check_class", $check_class);
    			
    			$tpl->set_var("SezAdminPanelJsAdd", "");
    			$tpl->set_var("SezAdminPanelJsView", "");
    			
    			$tpl->set_var("js_default_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&ret_url=" . urlencode($ret_url));
    			$tpl->set_var("js_edit_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&ret_url=" . urlencode($ret_url));
    			$tpl->parse("SezAdminPanelJsModify", false);
		        if(USE_ADMIN_AJAX) {
		            $tpl->set_var("js_delete_path", urlencode(ffDialog(TRUE,
		                                                        "yesno",
		                                                        ffTemplate::_get_word_by_code("admin_erase_title"),
		                                                        ffTemplate::_get_word_by_code("admin_erase_description"),
		                                                        $cancel_dialog_url,
		                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&ret_url=" . urlencode($ret_url) . "&" . "ThemeModify_frmAction=confirmdelete",
		                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify" . "/dialog")
		                                            ));
				} else {
		            $tpl->set_var("js_delete_path", ffDialog(TRUE,
		                                                        "yesno",
		                                                        ffTemplate::_get_word_by_code("admin_erase_title"),
		                                                        ffTemplate::_get_word_by_code("admin_erase_description"),
		                                                        $cancel_dialog_url,
		                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&ret_url=" . urlencode($ret_url) . "&" . "ThemeModify_frmAction=confirmdelete",
		                                                        FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify" . "/dialog")
		                                            );
				}
    			$tpl->parse("SezAdminPanelJsDelete", false);
			} elseif((strpos($tmp_path . $tmp_file, "/themes/" . THEME_INSET) !== false && basename($tmp_file) != "main.js") 
                    || (strpos($tmp_path . $tmp_file, "/themes/" . "library") !== false && strpos(basename($tmp_file), "observe") !== false)
                ) {
    			$tpl->set_var("js_check_icon", $uncheck_icon);
		        $tpl->set_var("js_check_class", $uncheck_class);

				$tpl->set_var("js_default_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/javascript") . "&extype=static&ret_url=" . urlencode($ret_url));
				$tpl->set_var("js_add_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/javascript") . "&extype=static&ret_url=" . urlencode($ret_url));
    			$tpl->parse("SezAdminPanelJsAdd", false);

				$tpl->set_var("js_view_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files&ret_url=" . urlencode($ret_url));
    			$tpl->parse("SezAdminPanelJsView", false);

    			$tpl->set_var("SezAdminPanelJsModify", "");
    			$tpl->set_var("SezAdminPanelJsDelete", "");
			} else {
    			$tpl->set_var("js_check_icon", $uncheck_icon);
		        $tpl->set_var("js_check_class", $uncheck_class);

    			$tpl->set_var("SezAdminPanelJsAdd", "");

				$tpl->set_var("js_default_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files&ret_url=" . urlencode($ret_url));
				$tpl->set_var("js_view_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files&ret_url=" . urlencode($ret_url));
    			$tpl->parse("SezAdminPanelJsView", false);

    			$tpl->set_var("SezAdminPanelJsModify", "");
    			$tpl->set_var("SezAdminPanelJsDelete", "");
			}
			
    		$tpl->parse("SezAdminPanelJs", true);
		} reset($js);
		$tpl->set_var("js_add_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/add." . FF_PHP_EXT . "?source=&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/javascript") . "&extype=static&ret_url=" . urlencode($ret_url));
		
		$tpl->parse("SezAdminPanelTheme", false);
	} else {
		$tpl->set_var("SezAdminPanelTheme", "");
	}    

	if(AREA_INTERNATIONAL_SHOW_MODIFY) {
        $tpl->set_var("lang_class", "");
        $tpl->set_var("lang_icon", cm_getClassByFrameworkCss("language", "icon-tag", "2x"));
        $tpl->set_var("lang_check_icon", $check_icon);
        $tpl->set_var("lang_check_class", $check_class);
        $tpl->set_var("lang_uncheck_icon", $uncheck_icon);
        $tpl->set_var("lang_uncheck_class", $uncheck_class);

        $tpl->set_var("lang_addnew_class", cm_getClassByFrameworkCss("addnew", "icon"));
        $tpl->set_var("lang_edit_class", cm_getClassByFrameworkCss("editrow", "icon"));
        $tpl->set_var("lang_delete_class", cm_getClassByFrameworkCss("deleterow", "icon"));
        		
		if(is_array($international))
			$international = array_merge($international, ffTemplate::_get_word_by_code("", null, null, true));
		else 
			$international = ffTemplate::_get_word_by_code("", null, null, true);

		uksort($international, "strnatcasecmp");
	    foreach($international AS $international_key => $international_value) {
	    	$tpl->set_var("international_name", $international_key);
		    if($international_value) {
    			$tpl->set_var("international_edit_path", FF_SITE_PATH . VG_SITE_INTERNATIONAL . "/modify." . FF_PHP_EXT . "?wc=" . urlencode($international_key) . "&ret_url=" . urlencode($ret_url));
		        $tpl->parse("SezAdminPanelInternationalModify", false);
		        if(USE_ADMIN_AJAX) {
			        $tpl->set_var("international_delete_path", urlencode(ffDialog(TRUE,
			                                                    "yesno",
			                                                    ffTemplate::_get_word_by_code("admin_erase_title"),
			                                                    ffTemplate::_get_word_by_code("admin_erase_description"),
			                                                    $cancel_dialog_url,
			                                                    FF_SITE_PATH . VG_SITE_INTERNATIONAL . "/modify." . FF_PHP_EXT . "?wc=" . urlencode($international_key) . "&ret_url=" . urlencode($ret_url) . "&" . "InternationalModify_frmAction=confirmdelete",
			                                                    FF_SITE_PATH . VG_SITE_INTERNATIONAL . "/modify" . "/dialog")
			                                        ));
				} else {
			        $tpl->set_var("international_delete_path", ffDialog(TRUE,
			                                                    "yesno",
			                                                    ffTemplate::_get_word_by_code("admin_erase_title"),
			                                                    ffTemplate::_get_word_by_code("admin_erase_description"),
			                                                    $cancel_dialog_url,
			                                                    FF_SITE_PATH . VG_SITE_INTERNATIONAL . "/modify." . FF_PHP_EXT . "?wc=" . urlencode($international_key) . "&ret_url=" . urlencode($ret_url) . "&" . "InternationalModify_frmAction=confirmdelete",
			                                                    FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/themes/modify" . "/dialog")
			                                        );
				}
    			$tpl->parse("SezAdminPanelInternationalDelete", false);
    			
    			$tpl->parse("SezAdminPanelInternationalVisible", true);
			} else {
    			$tpl->set_var("international_edit_path", FF_SITE_PATH . VG_SITE_INTERNATIONAL . "/modify." . FF_PHP_EXT . "?wc=" . urlencode($international_key) . "&ret_url=" . urlencode($ret_url));
		        $tpl->parse("SezAdminPanelInternationalModify", false);
		        $tpl->set_var("SezAdminPanelInternationalDelete", "");
		        
		        $tpl->parse("SezAdminPanelInternationalNoVisible", true);
			}
		} reset($international);
		$tpl->set_var("international_add_path", FF_SITE_PATH . VG_SITE_INTERNATIONAL . "/modify." . FF_PHP_EXT . "?ret_url=" . urlencode($ret_url));

		$tpl->parse("SezAdminPanelInternational", false);
	} else {
		$tpl->set_var("SezAdminPanelInternational", "");
	}    

	if(AREA_SEO_SHOW_MODIFY && is_array($seo) && count($seo) && $seo["ID"]) { 
		$seo_url = FF_SITE_PATH . VG_SITE_RESTRICTED . "/seo/" . ltrim($seo["src"], "/") . "/modify." . FF_PHP_EXT . "?key=" . $seo["ID"] . "&ret_url=" . urlencode($ret_url);

		$tpl->set_var("seo_url", $seo_url);
		$tpl->set_var("seo_class", "");
        $tpl->set_var("seo_icon", cm_getClassByFrameworkCss("seo", "icon-tag", "2x"));
        
		if($option["editor"]["seo"]) {
	        $tpl->parse("SezCmsSeo", false);
		}
		$tpl->parse("SezAdminPanelSeo", false);
	} else {
		$tpl->set_var("SezAdminPanelSeo", "");
	} 
	    
    if(AREA_SECTION_SHOW_MODIFY && is_array($sections) && count($sections)) {
        $tpl->set_var("section_addnew_class", "");
        $tpl->set_var("section_addnew_icon", cm_getClassByFrameworkCss("lay-addnew", "icon-tag", "2x"));
        $tpl->set_var("section_add_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/section/modify." . FF_PHP_EXT . "?ret_url=" . urlencode($ret_url));
        foreach($sections AS $sections_key => $sections_value) {
			$tpl->set_var("section_dialog_pre", ffCommon_specialchars('<h1 class="admin-title section">'));
			$tpl->set_var("section_dialog_post", ffCommon_specialchars('</h1>'));
                        
        	//$tpl->set_var("section_icon", ffCommon_specialchars('<i class="' . "type-section icon-section" . '"></i>'));
        	$tpl->set_var("layer_name", $sections_value["layer"]);  
        	$tpl->set_var("section_name", $sections_value["name"]);
			
            $tpl->set_var("section_id", $sections_value["ID"]);

            //if($sections_value["visible"]) {
                if(AREA_LAYOUT_SHOW_MODIFY) {
                    if(is_array($sections_value["layouts"]) && count($sections_value["layouts"])) {
                        //print_r($sections_value["layouts"]);
						
                        foreach($sections_value["layouts"] AS $layout_key => $layout_value) { 
							if(isset($layout_value["type_group"]) && strlen($layout_value["type_group"])) { 
						        $tpl->set_var("dialog_pre", ffCommon_specialchars('<h1 class="admin-title ' . $layout_value["type_group"] . '">'));
						        $tpl->set_var("dialog_post", ffCommon_specialchars('</h1>'));
						        //$tpl->set_var("layout_type_group", "ico-" . $layout_value["type_group"]);
							}
					        if(isset($layout_value["type_class"]) && strlen($layout_value["type_class"])) { 
                                $tpl->set_var("layout_icon", cm_getClassByFrameworkCss("vg-" . $layout_value["type_class"], "icon-tag", $layout_value["type_group"]));
							}

							if(isset($layout_value["title"]) && strlen($layout_value["title"])) {
        						$tpl->set_var("layout_name", $layout_value["title"]);
							}
	                        $tpl->set_var("layout_visible", ($layout_value["visible"] ? "" : "hidden notvisible"));
							$tpl->set_var("layout_id", $layout_value["ID"]);
							 
							$ID_layout = "";
							if($layout_value["multi_id"]) {
								$arrMultiID = explode(",", $layout_value["multi_id"]);
								if(is_array($arrMultiID) && count($arrMultiID)) {
									foreach($arrMultiID AS $arrMultiID_value) {
										if(strlen($arrMultiID_value)) {
											if(strlen($ID_layout))
												$ID_layout .= " ";
												
											$ID_layout .= '<a href="javascript:void(0);">' . $layout_key . trim($arrMultiID_value) . '</a>';
										}
									}
								}
							}
							
							if($layout_value["visible"])
								$tpl->parse("SezBlockPosDim", false);
							else
								$tpl->set_var("SezBlockPosDim", "");
							
							$arrBlockClass = array();
							$str_block_class = "";
							
							if(is_array($layout_value["class"]))
								$arrBlockClass = explode(" ", implode(" ",$layout_value["class"]));
		
							array_unshift($arrBlockClass, "block", $layout_value["type_class"]);
							
							foreach($arrBlockClass AS $arrBlockClass_key => $arrBlockClass_value)
							{
								$str_block_class .= '<span class="' . ($arrBlockClass_key%2 ? "even" : "odd") . '">.' . $arrBlockClass_value . '</span>';
							}
							
                            $tpl->set_var("block_layout_id", (strlen($ID_layout) ? $ID_layout : '<a href="javascript:void(0);">' . $layout_key . '</a>'));
                            $tpl->set_var("block_layout_class", $str_block_class);
                            $tpl->set_var("block_layout_name", '<a href="javascript:void(0);">' . "block_" . $layout_value["smart_url"] . '</a>');
                            
							$tpl->set_var("block_layout_type", $layout_value["type_class"]);
							if($layout_value["ajax"])
							{
								$tpl->set_var("block_layout_ajax", '<a href="javascript:void(0);">' . ffTemplate::_get_word_by_code("yes") . '</a>');
								$tpl->set_var("block_layout_ajax_on_document_ready", $layout_value["ajax_on_ready"]);
								$tpl->set_var("block_layout_ajax_on_load_event", $layout_value["ajax_on_event"]);
							} else
							{
								$tpl->set_var("block_layout_ajax", '<a href="javascript:void(0);">' . ffTemplate::_get_word_by_code("no") . '</a>');
								$tpl->set_var("block_layout_ajax_on_document_ready", ffTemplate::_get_word_by_code("na"));
								$tpl->set_var("block_layout_ajax_on_load_event", ffTemplate::_get_word_by_code("na"));
							}
                            if($layout_value["type"] == "MODULE") {
                                //ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());         
                                $tpl->set_var("module_edit_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $layout_value["value"] . "/config/modify." . FF_PHP_EXT . "/" . $layout_value["params"] . "?ret_url=" . urlencode($ret_url));
		        				if(USE_ADMIN_AJAX) {
	                                $tpl->set_var("module_delete_path", urlencode(ffDialog(TRUE,
	                                                                            "yesno",
	                                                                            ffTemplate::_get_word_by_code("admin_erase_title"),
	                                                                            ffTemplate::_get_word_by_code("admin_erase_description"),
	                                                                            $cancel_dialog_url,
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $layout_value["value"] . "/config/modify." . FF_PHP_EXT . "/" . $layout_value["params"] . "?ret_url=" . urlencode($ret_url) . "&" . strtolower($layout_value["value"]) . "-config_frmAction=confirmdelete",
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $layout_value["value"] . "/config" . "/dialog")
	                                                                ));
								} else {
	                                $tpl->set_var("module_delete_path", ffDialog(TRUE,
	                                                                            "yesno",
	                                                                            ffTemplate::_get_word_by_code("admin_erase_title"),
	                                                                            ffTemplate::_get_word_by_code("admin_erase_description"),
	                                                                            $cancel_dialog_url,
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $layout_value["value"] . "/config/modify." . FF_PHP_EXT . "/" . $layout_value["params"] . "?ret_url=" . urlencode($ret_url) . "&" . strtolower($layout_value["value"]) . "-config_frmAction=confirmdelete",
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $layout_value["value"] . "/config" . "/dialog")
	                                                                );
								}
                                $tpl->set_var("module_delete_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $layout_value["value"] . "/config/modify." . FF_PHP_EXT . "/" . $layout_value["params"]);

                                $tpl->set_var("module_edit_class", cm_getClassByFrameworkCss("editrow", "icon"));
                                $tpl->set_var("module_delete_class", cm_getClassByFrameworkCss("deleterow", "icon"));

                                $tpl->parse("SezModule", false);
                            } else {
                                $tpl->set_var("SezModule", "");
                            }
                            
                            if($layout_value["visible"] !== NULL) {
                                $tpl->set_var("layout_edit_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/modify." . FF_PHP_EXT . "?keys[ID]=" . $layout_value["ID"] . "&location=" . $sections_value["ID"] . "&path=" . $user_path . "&ret_url=" . urlencode($ret_url));
								if(USE_ADMIN_AJAX) {
	                                $tpl->set_var("layout_delete_path", urlencode(ffDialog(TRUE,
	                                                                            "yesno",
	                                                                            ffTemplate::_get_word_by_code("admin_erase_title"),
	                                                                            ffTemplate::_get_word_by_code("admin_erase_description"),
	                                                                            $cancel_dialog_url,
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/modify." . FF_PHP_EXT . "?keys[ID]=" . $layout_value["ID"] . "&location=" . $sections_value["ID"] . "&path=" . $user_path . "&ret_url=" . urlencode($ret_url) . "&LayoutModify_frmAction=confirmdelete",
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block" . "/dialog")
	                                                                ));
								} else {
	                                $tpl->set_var("layout_delete_path", ffDialog(TRUE,
	                                                                            "yesno",
	                                                                            ffTemplate::_get_word_by_code("admin_erase_title"),
	                                                                            ffTemplate::_get_word_by_code("admin_erase_description"),
	                                                                            $cancel_dialog_url,
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/modify." . FF_PHP_EXT . "?keys[ID]=" . $layout_value["ID"] . "&location=" . $sections_value["ID"] . "&path=" . $user_path . "&ret_url=" . urlencode($ret_url) . "&LayoutModify_frmAction=confirmdelete",
	                                                                            FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block" . "/dialog")
	                                                                );
								}
                                if(isset($layout_value["admin"]) && strlen($layout_value["admin"])) {
                                    $count_item++;
                                    $tpl->set_var("admin_menu", $layout_value["admin"]);
                                     $tpl->parse("SezLayoutAdmin", false);
                                } else {
                                    $tpl->set_var("SezLayoutAdmin", "");
                                }

                                $tpl->set_var("layout_edit_class", cm_getClassByFrameworkCss("editrow", "icon"));
                                $tpl->set_var("layout_delete_class", cm_getClassByFrameworkCss("deleterow", "icon"));
                                
                                //$tpl->set_var("layout_js_class", cm_getClassByFrameworkCss("js", "icon"));
                                //$tpl->set_var("layout_css_class", cm_getClassByFrameworkCss("css", "icon"));
                                $tpl->set_var("layout_js_icon", cm_getClassByFrameworkCss("js", "icon-tag", "2x"));
                                $tpl->set_var("layout_css_icon", cm_getClassByFrameworkCss("css", "icon-tag", "2x"));
                                
                                $tpl->parse("SezLayout", true);
                            } else {
                                $tpl->set_var("setting_class", cm_getClassByFrameworkCss("cog", "icon"));

                                if($layout_value["type"] == "FORMS_FRAMEWORK") {
                                    //DA CONCORDARE CON SAMUELE PRIMA O POI
                                    $tpl->set_var("ff_settings_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/settings/modify." . FF_PHP_EXT . "/" . $layout_value["type"] . "?ret_url=" . urlencode($ret_url));
                                    $tpl->set_var("SezNoLayoutSettings", "");
                                    $tpl->parse("SezNoLayoutFF", false);
                                    $tpl->set_var("SezNoLayoutVGS", "");
                                } elseif($layout_value["type"] == "VG_SERVICES") {
                                    $tpl->set_var("vgs_settings_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/configuration/services/" . $layout_value["ID"] . "/config" . "?ret_url=" . urlencode($ret_url));
                                    $tpl->set_var("SezNoLayoutSettings", "");
                                    $tpl->set_var("SezNoLayoutFF", "");
                                    $tpl->parse("SezNoLayoutVGS", false);
                                } else {
                                    $tpl->set_var("layout_settings_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/settings/modify." . FF_PHP_EXT . "/" . $layout_value["type"] . "?ret_url=" . urlencode($ret_url));
                                    $tpl->parse("SezNoLayoutSettings", false);
                                    $tpl->set_var("SezNoLayoutFF", "");
                                    $tpl->set_var("SezNoLayoutVGS", "");
                                }
                                
                                if(isset($layout_value["admin"]) && strlen($layout_value["admin"])) {
                                    $count_item++;
                                    $tpl->set_var("admin_menu", $layout_value["admin"]);
                                    $tpl->parse("SezNoLayoutAdmin", false);
                                } else {
                                    $tpl->set_var("SezNoLayoutAdmin", "");
                                }
                                
                                $tpl->parse("SezNoLayout", true);
                            }
                        }

                    }
                    
                    $tpl->set_var("layer_edit_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/section/layer/modify." . FF_PHP_EXT . "?section=" . $sections_value["ID"] . "&ret_url=" . urlencode($ret_url));
                    $tpl->set_var("section_edit_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/section/modify." . FF_PHP_EXT . "?keys[ID]=" . $sections_value["ID"] . "&ret_url=" . urlencode($ret_url));
					if(USE_ADMIN_AJAX) {
	                    $tpl->set_var("section_delete_path", urlencode(ffDialog(TRUE,
	                                                                "yesno",
	                                                                ffTemplate::_get_word_by_code("admin_erase_title"),
	                                                                ffTemplate::_get_word_by_code("admin_erase_description"),
	                                                                $cancel_dialog_url,
	                                                                FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/section/modify." . FF_PHP_EXT . "?keys[ID]=" . $sections_value["ID"] . "&ret_url=" . urlencode($ret_url) . "&SectionModify_frmAction=confirmdelete",
	                                                                FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/section" . "/dialog")
	                                                    ));
					} else {
	                    $tpl->set_var("section_delete_path", ffDialog(TRUE,
	                                                                "yesno",
	                                                                ffTemplate::_get_word_by_code("admin_erase_title"),
	                                                                ffTemplate::_get_word_by_code("admin_erase_description"),
	                                                                $cancel_dialog_url,
	                                                                FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/section/modify." . FF_PHP_EXT . "?keys[ID]=" . $sections_value["ID"] . "&ret_url=" . urlencode($ret_url) . "&SectionModify_frmAction=confirmdelete",
	                                                                FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/manage/section" . "/dialog")
	                                                    );
					}

                    $tpl->set_var("section_edit_class", cm_getClassByFrameworkCss("editrow", "icon"));
                    $tpl->set_var("section_delete_class", cm_getClassByFrameworkCss("deleterow", "icon"));

					$tpl->set_var("layout_dialog_pre", ffCommon_specialchars('<h1 class="admin-title layout">'));
					$tpl->set_var("layout_dialog_post", ffCommon_specialchars('</h1>'));
						
                    
					switch (ffCommon_url_rewrite($sections_value["name"])) {
						case "top":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", cm_getClassByFrameworkCss("lay-31", "icon-tag", "2x"));
							break;
						case "bottom":
						case "footer":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", cm_getClassByFrameworkCss("lay-1333", "icon-tag", "2x"));
							break;
						case "left":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", cm_getClassByFrameworkCss("lay-13", "icon-tag", "2x"));
							break;
						case "right":
							$tpl->set_var("layout_addnew_class", "");  
							$tpl->set_var("layout_addnew_icon", cm_getClassByFrameworkCss("lay-3133", "icon-tag", "2x"));
							break;
						case "content":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", cm_getClassByFrameworkCss("lay-2233", "icon-tag", "2x"));
							break;
						default:
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", cm_getClassByFrameworkCss("lay", "icon-tag", "2x"));
							break;
					}
					
                    $tpl->set_var("layout_addnew_bottom_class", cm_getClassByFrameworkCss("addnew", "icon", array("2x")));
                    $tpl->set_var("layout_add_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/modify." . FF_PHP_EXT . "?location=" . $sections_value["ID"] . "&path=" . $user_path . "&ret_url=" . urlencode($ret_url));

                    $tpl->parse("SezLayouts", false);
                    $tpl->set_var("SezLayout", "");
                    $tpl->set_var("SezNoLayout", "");
                } else {
                    $tpl->set_var("SezLayouts", "");  
                }
				
                if($sections_value["layer"] != $last_layer) {
                	$last_layer = $sections_value["layer"];
                	$tpl->parse("SezSectionSep", false);
				} else {
					$tpl->set_var("SezSectionSep", "");  
				}

                $tpl->parse("SezSection", true);
            //}
        }
        $tpl->parse("SezSections", false);
    }

    if(array_key_exists("editor", $option)) {
	    foreach($option["editor"] AS $editor_key => $editor_value) {
    		if(is_array($editor_value) && array_key_exists("menu", $editor_value)) {
    			$tpl->set_var("cms_editor_menu_icon", $editor_value["menu"]["icon"]);
    			$tpl->set_var("cms_editor_menu_class", $editor_value["menu"]["class"]);
    			$tpl->set_var("cms_editor_menu_rel", $editor_value["menu"]["rel"]);  

    			if(isset($editor_value["menu"]["url"]))
    				$tpl->set_var("cms_editor_menu_url", $editor_value["menu"]["url"]);  
    			else
    				$tpl->set_var("cms_editor_menu_url", "javascript:void(0);");  

    			$tpl->parse("SezCmsEditorMenu", true);
    		}
	    }

        $tpl->parse("SezCmsEditor", false);
    }
    
	if(strlen($layout_settings["ADMIN_INTERFACE_MENU_PLUGIN"])) {
	    $tpl->set_var("class_plugin", preg_replace('/[^a-zA-Z0-9\-]/', '', $layout_settings["ADMIN_INTERFACE_MENU_PLUGIN"]));
	    $tpl->set_var("class_name", preg_replace('/[^a-zA-Z0-9\-]/', '', $layout_settings["ADMIN_INTERFACE_PLUGIN"]));
	    //setJsRequest($layout_settings["ADMIN_INTERFACE_MENU_PLUGIN"]);
	    //setJsRequest($layout_settings["ADMIN_INTERFACE_PLUGIN"]);
	} else {
	    $tpl->set_var("class_plugin", "admin_menu");
	    $tpl->set_var("class_name", "admin_menu");
	}    

    $buffer = $tpl->rpparse("main", false);
    
    return $buffer;
}
