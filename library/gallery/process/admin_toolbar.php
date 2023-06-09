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
function process_admin_toolbar($user_path = "/", $theme, $sections, $css = array(), $js = array(), $international = array(), $seo = array())
{
	$cm = cm::getInstance();
	if(Auth::isAdmin()) {
		$cm->oPage->tplAddJs("ff.cms.bar");
	} else {
		$cm->oPage->tplAddJs("ff.cms.block");
	}
	if(Auth::env("AREA_SECTION_SHOW_MODIFY")) {
		$cm->oPage->tplAddJs("ff.cms.editor");

		$option["editor"] = array();

		if(AREA_SEO_SHOW_MODIFY) {
			$cm->oPage->tplAddJs("ff.cms.seo");

			$option["editor"]["seo"] = true;
		}
		if(Auth::env("AREA_SITEMAP_SHOW_MODIFY") && 0) {
			$cm->oPage->tplAddJs("ff.cms.sitemap");

			$option["editor"]["sitemap"] = array(
				"menu" => array("class" => "cms-editor-menu sitemap"
				, "icon" => Cms::getInstance("frameworkcss")->get("sitemap", "icon-tag", "2x")
				, "rel" => "add"
				)
			);
		}
		if(Auth::env("AREA_LAYOUT_SHOW_MODIFY") && 0) {
			$cm->oPage->tplAddJs("ff.cms.layout");

			$option["editor"]["sitemap"] = array(
				"menu" => array("class" => "cms-editor-menu"
				, "icon" => Cms::getInstance("frameworkcss")->get("addnew", "icon-tag", "2x")
				, "rel" => "add"
				)
			);
		}
	}

    $count_tools = 0;

    $check_class = "checked";
    $check_icon = Cms::getInstance("frameworkcss")->get($check_class, "icon-tag");
    $uncheck_class = "unchecked";
    $uncheck_icon = Cms::getInstance("frameworkcss")->get($uncheck_class, "icon-tag");

	$cancel_dialog_url = "[CLOSEDIALOG]";

    $tpl = ffTemplate::factory(get_template_cascading($user_path, "admin.html", ""));
    $tpl->load_file("admin.html", "main");
    
    $tpl->set_var("site_path", FF_SITE_PATH);

    $tpl->set_var("info_icon", Cms::getInstance("frameworkcss")->get("info", "icon-tag", "2x"));
    $tpl->set_var("logout_icon", Cms::getInstance("frameworkcss")->get("power-off", "icon-tag", "2x"));
    $tpl->set_var("hide_icon", Cms::getInstance("frameworkcss")->get("bars", "icon-tag", "2x"));
    $tpl->set_var("refresh_icon", Cms::getInstance("frameworkcss")->get("refresh", "icon-tag"));

    if(Auth::env("AREA_ADMIN_SHOW_MODIFY")) {
        $tpl->set_var("path", VG_WS_ADMIN);
        $tpl->set_var("admin_icon", Cms::getInstance("frameworkcss")->get("vg-admin", "icon-tag"));        
        $tpl->parse("SezAdmin", false);
    }
    
    if(Auth::env("AREA_RESTRICTED_SHOW_MODIFY")) {
        $tpl->set_var("path", VG_WS_RESTRICTED);
        $tpl->set_var("restricted_icon", Cms::getInstance("frameworkcss")->get("vg-restricted", "icon-tag"));        
        $tpl->parse("SezRestricted", false);
    }
    
    if(Auth::env("AREA_ECOMMERCE_SHOW_MODIFY")) {
        $tpl->set_var("path", VG_WS_ECOMMERCE);
        $tpl->set_var("manage_icon", Cms::getInstance("frameworkcss")->get("vg-manage", "icon-tag"));        
        $tpl->parse("SezManage", false);
    }

    
    $tpl->set_var("tools_class", "");
    $tpl->set_var("tools_icon", Cms::getInstance("frameworkcss")->get("eraser", "icon-tag", "2x"));

    if(Auth::env("AREA_CHECKER_SHOW_MODIFY")) {
    	$count_tools++;
        $tpl->set_var("cache_class", "");
        $tpl->set_var("cache_icon", Cms::getInstance("frameworkcss")->get("eraser", "icon-tag"));

        $tpl->parse("SezAdminPanelToolsCache", false);
	}

	if($count_tools)
    	$tpl->parse("SezAdminPanelTools", false);

    //ffErrorHandler::raise("ad", E_USER_ERROR, null, get_defined_vars());
	if(Auth::env("AREA_THEME_SHOW_MODIFY")) {
        $tpl->set_var("tpl_class", "");
        $tpl->set_var("tpl_icon", Cms::getInstance("frameworkcss")->get("building-o", "icon-tag", "2x"));
        $tpl->set_var("css_class", "");
        $tpl->set_var("css_icon", Cms::getInstance("frameworkcss")->get("css", "icon-tag", "2x"));

        $tpl->set_var("more_class", Cms::getInstance("frameworkcss")->get("retracted", "icon"));
        $tpl->set_var("more_reverse_class", Cms::getInstance("frameworkcss")->get("exanded", "icon"));
        
        $tpl->set_var("css_addnew_class", Cms::getInstance("frameworkcss")->get("addnew", "icon"));
        $tpl->set_var("css_preview_class", Cms::getInstance("frameworkcss")->get("preview", "icon"));
        $tpl->set_var("css_edit_class", Cms::getInstance("frameworkcss")->get("editrow", "icon"));
        $tpl->set_var("css_delete_class", Cms::getInstance("frameworkcss")->get("deleterow", "icon"));
        $tpl->set_var("js_class", "");
        $tpl->set_var("js_icon", Cms::getInstance("frameworkcss")->get("js", "icon-tag", "2x"));
        $tpl->set_var("js_addnew_class", Cms::getInstance("frameworkcss")->get("addnew", "icon"));
        $tpl->set_var("js_preview_class", Cms::getInstance("frameworkcss")->get("preview", "icon"));
        $tpl->set_var("js_edit_class", Cms::getInstance("frameworkcss")->get("editrow", "icon"));
        $tpl->set_var("js_delete_class", Cms::getInstance("frameworkcss")->get("deleterow", "icon"));

		foreach($css AS $css_queue) {
		    foreach($css_queue AS $css_key => $css_value) {
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
    					$tpl->set_var("css_default_path", get_path_by_rule("utility") . "/resources/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/css") . "&extype=static");
    					$tpl->set_var("css_add_path", get_path_by_rule("utility") . "/resources/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/css") . "&extype=static");
    					$tpl->parse("SezAdminPanelCssAdd", false);
					} else {
    					$tpl->set_var("css_default_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files");
						$tpl->set_var("SezAdminPanelCssAdd", "");
					}

    				$tpl->set_var("css_view_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files");
    				$tpl->parse("SezAdminPanelCssView", false);

    				$tpl->set_var("SezAdminPanelCssModify", "");
    				$tpl->set_var("SezAdminPanelCssDelete", "");
				} else {
			        $tpl->set_var("css_check_icon", $check_icon);
			        $tpl->set_var("css_check_class", $check_class);

    				$tpl->set_var("SezAdminPanelCssAdd", "");
					$tpl->set_var("SezAdminPanelCssView", "");
					
					if(basename($tmp_file) == "main.css") {
						$tpl->set_var("css_default_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&deletable=0");
    					$tpl->set_var("css_edit_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static&deletable=0");
			            $tpl->parse("SezAdminPanelCssModify", false);
						$tpl->set_var("SezAdminPanelCssDelete", "");
					} else {
						$tpl->set_var("css_default_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static");
    					$tpl->set_var("css_edit_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static");
			            $tpl->parse("SezAdminPanelCssModify", false);
						$tpl->set_var("css_delete_path", urlencode(ffDialog(TRUE,
																	"yesno",
																	ffTemplate::_get_word_by_code("admin_erase_title"),
																	ffTemplate::_get_word_by_code("admin_erase_description"),
																	$cancel_dialog_url,
																	get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static" . "&" . "ThemeModify_frmAction=confirmdelete",
																	get_path_by_rule("utility") . "/resources/modify" . "/dialog")
														));
    					$tpl->parse("SezAdminPanelCssDelete", false);
					}
				}

    			$tpl->parse("SezAdminPanelCss", true);
			} 
		} reset($css);
		$tpl->set_var("css_add_path", get_path_by_rule("utility") . "/resources/add." . FF_PHP_EXT . "?source=&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/css") . "&extype=static");

		foreach($js AS $js_queue) {
		    foreach($js_queue AS $js_key => $js_value) {
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
						//elseif (strpos($js_value["path"], cm_getModulesExternalPath()) === 0)
						//	$tmp_path = $js_value["path"];
			            else
							$tmp_path = $js_value["path"] . "/";
					
					}
					
			        if ($js_value["file"] === null)
			            $tmp_file = $js_key . ".js";
					//elseif (strpos($tmp_path, cm_getModulesExternalPath()) === 0)
					//	$tmp_file = "";
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

				//if (strpos($tmp_path, cm_getModulesExternalPath()) === 0)
				//	$tmp_path = preg_replace("/^" . preg_quote(cm_getModulesExternalPath(), "/") . "(\/[^\/]+)/", CM_MODULES_PATH. "\$1/themes", $tmp_path);

			    if(!file_exists(FF_DISK_PATH . $tmp_path . $tmp_file))
			        $tmp_path = FF_SITE_PATH . "/themes/library/" . $js_key . "/"; 

			    if(strpos($tmp_path . $tmp_file, "/themes/" . $theme) !== false) {
			        $tpl->set_var("js_check_icon", $check_icon);
			        $tpl->set_var("js_check_class", $check_class);
    				
    				$tpl->set_var("SezAdminPanelJsAdd", "");
    				$tpl->set_var("SezAdminPanelJsView", "");
    				
    				$tpl->set_var("js_default_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static");
    				$tpl->set_var("js_edit_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static");
    				$tpl->parse("SezAdminPanelJsModify", false);
					$tpl->set_var("js_delete_path", urlencode(ffDialog(TRUE,
																"yesno",
																ffTemplate::_get_word_by_code("admin_erase_title"),
																ffTemplate::_get_word_by_code("admin_erase_description"),
																$cancel_dialog_url,
																get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&extype=static" . "&" . "ThemeModify_frmAction=confirmdelete",
																get_path_by_rule("utility") . "/resources/modify" . "/dialog")
													));
    				$tpl->parse("SezAdminPanelJsDelete", false);
				} elseif((strpos($tmp_path . $tmp_file, "/themes/" . THEME_INSET) !== false && basename($tmp_file) != "main.js") 
	                    || (strpos($tmp_path . $tmp_file, "/themes/" . "library") !== false && strpos(basename($tmp_file), "observe") !== false)
	                ) {
    				$tpl->set_var("js_check_icon", $uncheck_icon);
			        $tpl->set_var("js_check_class", $uncheck_class);

					$tpl->set_var("js_default_path", get_path_by_rule("utility") . "/resources/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/javascript") . "&extype=static");
					$tpl->set_var("js_add_path", get_path_by_rule("utility") . "/resources/add." . FF_PHP_EXT . "?source=" . urlencode($tmp_path . $tmp_file) . "&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/javascript") . "&extype=static");
    				$tpl->parse("SezAdminPanelJsAdd", false);

					$tpl->set_var("js_view_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files");
    				$tpl->parse("SezAdminPanelJsView", false);

    				$tpl->set_var("SezAdminPanelJsModify", "");
    				$tpl->set_var("SezAdminPanelJsDelete", "");
				} else {
    				$tpl->set_var("js_check_icon", $uncheck_icon);
			        $tpl->set_var("js_check_class", $uncheck_class);

    				$tpl->set_var("SezAdminPanelJsAdd", "");

					$tpl->set_var("js_default_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files");
					$tpl->set_var("js_view_path", get_path_by_rule("utility") . "/resources/modify." . FF_PHP_EXT . "?path=" . urlencode($tmp_path . $tmp_file) . "&writable=0&extype=files");
    				$tpl->parse("SezAdminPanelJsView", false);

    				$tpl->set_var("SezAdminPanelJsModify", "");
    				$tpl->set_var("SezAdminPanelJsDelete", "");
				}
				
    			$tpl->parse("SezAdminPanelJs", true);
			} 
		} reset($js);
		$tpl->set_var("js_add_path", get_path_by_rule("utility") . "/resources/add." . FF_PHP_EXT . "?source=&path=" . urlencode($user_path) . "&basepath=" . urlencode("/" . $theme . "/javascript") . "&extype=static");
		
		$tpl->parse("SezAdminPanelTheme", false);
	} else {
		$tpl->set_var("SezAdminPanelTheme", "");
	}    

	if(Auth::env("AREA_INTERNATIONAL_SHOW_MODIFY")) {
        $tpl->set_var("lang_class", "");
        $tpl->set_var("lang_icon", Cms::getInstance("frameworkcss")->get("language", "icon-tag", "2x"));
        $tpl->set_var("lang_check_icon", $check_icon);
        $tpl->set_var("lang_check_class", $check_class);
        $tpl->set_var("lang_uncheck_icon", $uncheck_icon);
        $tpl->set_var("lang_uncheck_class", $uncheck_class);

        $tpl->set_var("lang_addnew_class", Cms::getInstance("frameworkcss")->get("addnew", "icon"));
        $tpl->set_var("lang_edit_class", Cms::getInstance("frameworkcss")->get("editrow", "icon"));
        $tpl->set_var("lang_delete_class", Cms::getInstance("frameworkcss")->get("deleterow", "icon"));
        		
		if(is_array($international))
			$international = array_merge($international, ffTranslator::dump());
		else 
			$international = ffTranslator::dump();

		uksort($international, "strnatcasecmp");
	    foreach($international AS $international_key => $international_value) {
	    	$tpl->set_var("international_name", $international_key);
		    if($international_value) {
    			$tpl->set_var("international_edit_path", get_path_by_rule("utility") . "/international/modify." . FF_PHP_EXT . "?wc=" . urlencode($international_key));
		        $tpl->parse("SezAdminPanelInternationalModify", false);
				$tpl->set_var("international_delete_path", urlencode(ffDialog(TRUE,
															"yesno",
															ffTemplate::_get_word_by_code("admin_erase_title"),
															ffTemplate::_get_word_by_code("admin_erase_description"),
															$cancel_dialog_url,
															get_path_by_rule("utility") . "/international/modify." . FF_PHP_EXT . "?wc=" . urlencode($international_key) . "&" . "InternationalModify_frmAction=confirmdelete",
															get_path_by_rule("utility") . "/international/modify" . "/dialog")
												));
    			$tpl->parse("SezAdminPanelInternationalDelete", false);
    			
    			$tpl->parse("SezAdminPanelInternationalVisible", true);
			} else {
    			$tpl->set_var("international_edit_path", get_path_by_rule("utility") . "/international/modify." . FF_PHP_EXT . "?wc=" . urlencode($international_key));
		        $tpl->parse("SezAdminPanelInternationalModify", false);
		        $tpl->set_var("SezAdminPanelInternationalDelete", "");
		        
		        $tpl->parse("SezAdminPanelInternationalNoVisible", true);
			}
		} reset($international);
		$tpl->set_var("international_add_path", get_path_by_rule("utility") . "/international/modify." . FF_PHP_EXT);

		$tpl->parse("SezAdminPanelInternational", false);
	} else {
		$tpl->set_var("SezAdminPanelInternational", "");
	}    

	if(AREA_SEO_SHOW_MODIFY && is_array($seo) && count($seo) && $seo["ID"]) {
		$seo_url = get_path_by_rule("seo") . "/" . ltrim($seo["src"], "/") . "?key=" . $seo["ID"];

		$tpl->set_var("seo_url", $seo_url);
		$tpl->set_var("seo_class", "");
        $tpl->set_var("seo_icon", Cms::getInstance("frameworkcss")->get("seo", "icon-tag", "2x"));
        
		if($option["editor"]["seo"]) {
	        $tpl->parse("SezCmsSeo", false);
		}
		$tpl->parse("SezAdminPanelSeo", false);
	} else {
		$tpl->set_var("SezAdminPanelSeo", "");
	} 
	    
    if(Auth::env("AREA_SECTION_SHOW_MODIFY") && is_array($sections) && count($sections)) {
		$last_layer = "";
		$count_item = 0;

        $tpl->set_var("section_addnew_class", "");
        $tpl->set_var("section_addnew_icon", Cms::getInstance("frameworkcss")->get("lay-addnew", "icon-tag", "2x"));
        $tpl->set_var("section_add_path", get_path_by_rule("pages-structure") . "/modify." . FF_PHP_EXT);
        foreach($sections AS $sections_key => $sections_value) {
			$tpl->set_var("section_dialog_pre", ffCommon_specialchars('<h1 class="admin-title section">'));
			$tpl->set_var("section_dialog_post", ffCommon_specialchars('</h1>'));
                        
        	//$tpl->set_var("section_icon", ffCommon_specialchars('<i class="' . "type-section icon-section" . '"></i>'));
        	$tpl->set_var("layer_name", $sections_value["layer"]);  
        	$tpl->set_var("section_name", $sections_value["name"]);
			
            $tpl->set_var("section_id", $sections_value["ID"]);

            //if($sections_value["visible"]) {
                if(Auth::env("AREA_LAYOUT_SHOW_MODIFY")) {
                    if(is_array($sections_value["blocks"]) && count($sections_value["blocks"])) {
                        foreach($sections_value["blocks"] AS $layout_key => $layout_value) {
							if(isset($layout_value["type_group"]) && strlen($layout_value["type_group"])) { 
						        $tpl->set_var("dialog_pre", ffCommon_specialchars('<h1 class="admin-title ' . $layout_value["type_group"] . '">'));
						        $tpl->set_var("dialog_post", ffCommon_specialchars('</h1>'));
						        //$tpl->set_var("layout_type_group", "ico-" . $layout_value["type_group"]);
							}
					        if(isset($layout_value["type_class"]) && strlen($layout_value["type_class"])) { 
                                $tpl->set_var("layout_icon", Cms::getInstance("frameworkcss")->get("vg-" . $layout_value["type_class"], "icon-tag", $layout_value["type_group"]));
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
                            /*if($layout_value["type"] == "MODULE") {
                                //ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());         
                                $tpl->set_var("module_edit_path", get_path_by_rule("addons") . "/" . $layout_value["value"] . "/" . $layout_value["params"]);
								$tpl->set_var("module_delete_path", urlencode(ffDialog(TRUE,
																			"yesno",
																			ffTemplate::_get_word_by_code("admin_erase_title"),
																			ffTemplate::_get_word_by_code("admin_erase_description"),
																			$cancel_dialog_url,
																			get_path_by_rule("addons") . "/" . $layout_value["value"] . "/" . $layout_value["params"] . "?" . strtolower($layout_value["value"]) . "-config_frmAction=confirmdelete",
																			get_path_by_rule("addons") . "/" . $layout_value["value"] . "/" . $layout_value["params"] . "/dialog")
																));
                                $tpl->set_var("module_delete_path", get_path_by_rule("addons") . "/" . $layout_value["value"] . "/" . $layout_value["params"]);

                                $tpl->set_var("module_edit_class", Cms::getInstance("frameworkcss")->get("editrow", "icon"));
                                $tpl->set_var("module_delete_class", Cms::getInstance("frameworkcss")->get("deleterow", "icon"));

                                $tpl->parse("SezModule", false);
                            } else {
                                $tpl->set_var("SezModule", "");
                            }*/
                            
                            if($layout_value["visible"] !== NULL) {
                                $tpl->set_var("layout_edit_path", get_path_by_rule("blocks") . "/modify." . FF_PHP_EXT . "?keys[ID]=" . $layout_value["ID"] . "&location=" . $sections_value["ID"] . "&path=" . $user_path);
								$tpl->set_var("layout_delete_path", urlencode(ffDialog(TRUE,
																			"yesno",
																			ffTemplate::_get_word_by_code("admin_erase_title"),
																			ffTemplate::_get_word_by_code("admin_erase_description"),
																			$cancel_dialog_url,
																			get_path_by_rule("blocks") . "/modify." . FF_PHP_EXT . "?keys[ID]=" . $layout_value["ID"] . "&location=" . $sections_value["ID"] . "&path=" . $user_path . "&LayoutModify_frmAction=confirmdelete",
																			get_path_by_rule("blocks") . "/dialog")
																));
                                if(isset($layout_value["admin"]) && strlen($layout_value["admin"])) {
                                    $count_item++;
                                    $tpl->set_var("admin_menu", $layout_value["admin"]);
                                     $tpl->parse("SezLayoutAdmin", false);
                                } else {
                                    $tpl->set_var("SezLayoutAdmin", "");
                                }

                                $tpl->set_var("layout_edit_class", Cms::getInstance("frameworkcss")->get("editrow", "icon"));
                                $tpl->set_var("layout_delete_class", Cms::getInstance("frameworkcss")->get("deleterow", "icon"));
                                
                                //$tpl->set_var("layout_js_class", Cms::getInstance("frameworkcss")->get("js", "icon"));
                                //$tpl->set_var("layout_css_class", Cms::getInstance("frameworkcss")->get("css", "icon"));
                                $tpl->set_var("layout_js_icon", Cms::getInstance("frameworkcss")->get("js", "icon-tag", "2x"));
                                $tpl->set_var("layout_css_icon", Cms::getInstance("frameworkcss")->get("css", "icon-tag", "2x"));
                                
                                $tpl->parse("SezLayout", true);
                            } else {
                                $tpl->set_var("setting_class", Cms::getInstance("frameworkcss")->get("cog", "icon"));

                                if($layout_value["type"] == "FORMS_FRAMEWORK") {
                                    //DA CONCORDARE CON SAMUELE PRIMA O POI
                                    $tpl->set_var("ff_settings_path", get_path_by_rule("blocks") . "/type/modify." . FF_PHP_EXT . "/" . $layout_value["type"]);
                                    $tpl->set_var("SezNoLayoutSettings", "");
                                    $tpl->parse("SezNoLayoutFF", false);
                                    $tpl->set_var("SezNoLayoutVGS", "");
                                } elseif($layout_value["type"] == "VG_SERVICES") {
                                    $tpl->set_var("vgs_settings_path", FF_SITE_PATH . VG_UI_PATH . "/services/" . $layout_value["ID"] . "/config");
                                    $tpl->set_var("SezNoLayoutSettings", "");
                                    $tpl->set_var("SezNoLayoutFF", "");
                                    $tpl->parse("SezNoLayoutVGS", false);
                                } else {
                                    $tpl->set_var("layout_settings_path", get_path_by_rule("blocks") . "/type/modify." . FF_PHP_EXT . "/" . $layout_value["type"]);
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
                    
                    $tpl->set_var("layer_edit_path", get_path_by_rule("pages-structure") . "/layer/modify." . FF_PHP_EXT . "?section=" . $sections_value["ID"]);
                    $tpl->set_var("section_edit_path", get_path_by_rule("pages-structure") . "/modify." . FF_PHP_EXT . "?keys[ID]=" . $sections_value["ID"]);
					$tpl->set_var("section_delete_path", urlencode(ffDialog(TRUE,
																"yesno",
																ffTemplate::_get_word_by_code("admin_erase_title"),
																ffTemplate::_get_word_by_code("admin_erase_description"),
																$cancel_dialog_url,
																get_path_by_rule("pages-structure") . "/modify." . FF_PHP_EXT . "?keys[ID]=" . $sections_value["ID"] . "&SectionModify_frmAction=confirmdelete",
																get_path_by_rule("pages-structure") . "/dialog")
													));
                    $tpl->set_var("section_edit_class", Cms::getInstance("frameworkcss")->get("editrow", "icon"));
                    $tpl->set_var("section_delete_class", Cms::getInstance("frameworkcss")->get("deleterow", "icon"));

					$tpl->set_var("layout_dialog_pre", ffCommon_specialchars('<h1 class="admin-title layout">'));
					$tpl->set_var("layout_dialog_post", ffCommon_specialchars('</h1>'));
						
                    
					switch (ffCommon_url_rewrite($sections_value["name"])) {
						case "top":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", Cms::getInstance("frameworkcss")->get("lay-31", "icon-tag", "2x"));
							break;
						case "bottom":
						case "footer":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", Cms::getInstance("frameworkcss")->get("lay-1333", "icon-tag", "2x"));
							break;
						case "left":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", Cms::getInstance("frameworkcss")->get("lay-13", "icon-tag", "2x"));
							break;
						case "right":
							$tpl->set_var("layout_addnew_class", "");  
							$tpl->set_var("layout_addnew_icon", Cms::getInstance("frameworkcss")->get("lay-3133", "icon-tag", "2x"));
							break;
						case "content":
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", Cms::getInstance("frameworkcss")->get("lay-2233", "icon-tag", "2x"));
							break;
						default:
							$tpl->set_var("layout_addnew_class", "");
							$tpl->set_var("layout_addnew_icon", Cms::getInstance("frameworkcss")->get("lay", "icon-tag", "2x"));
							break;
					}
					
                    $tpl->set_var("layout_addnew_bottom_class", Cms::getInstance("frameworkcss")->get("addnew", "icon", array("2x")));
                    $tpl->set_var("layout_add_path", get_path_by_rule("blocks") . "/modify." . FF_PHP_EXT . "?location=" . $sections_value["ID"] . "&path=" . $user_path);

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
    
    $buffer = $tpl->rpparse("main", false);
    
    return $buffer;
}
