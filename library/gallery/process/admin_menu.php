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
function process_admin_menu($admin_menu, $template_name = "menu", $user_path = "/", $location = "content", $ret_url = NULL) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

    if(check_function("get_layout_settings"))
    	$layout_settings = get_layout_settings(NULL, "ADMIN");

    $count_element = 0;
    /*
    $admin_menu["unic_name"];
    $admin_menu["title"];
    $admin_menu["layout"]["ID"] = "";
    $admin_menu["layout"]["type"] = "";
    $admin_menu["module"]["value"] = "";
    $admin_menu["module"]["params"] = "";
    $admin_menu["adddir"] = "";
    $admin_menu["addnew"] = "";
    $admin_menu["modify"] = "";
    $admin_menu["delete"] = "";
    $admin_menu["exstras"] = "";
    $admin_menu["ecommerce"] = "";
    $admin_menu["setting"] = ""; 
    */

	$cancel_dialog_url = "[CLOSEDIALOG]";
	$admin_menu["delete"] = str_replace("--returl--", urlencode("[CLOSEDIALOG]"), $admin_menu["delete"]);
	$admin_menu["delete"] = str_replace("--encodereturl--", urlencode($ret_url), $admin_menu["delete"]);

    $file_name = "admin_" . $template_name . ".html";
    if($ret_url === NULL)
        $ret_url = $_SERVER["REQUEST_URI"];
      
    if(isset($admin_menu["unic_name"]) && strlen($admin_menu["unic_name"])) {
        $tpl = ffTemplate::factory(get_template_cascading($user_path, $file_name));
        $tpl->load_file($file_name, "main");
		
        $tpl->set_var("site_path", FF_SITE_PATH);
        $tpl->set_var("ret_url", urlencode($ret_url));

        $class_name = "admin-link";
        //$class_primary = " vg-primary";
        $class_primary = "";
        if(strlen($layout_settings["ADMIN_INTERFACE_PLUGIN"])) {
			$class_name = ffCommon_url_rewrite($layout_settings["ADMIN_INTERFACE_PLUGIN"]);
        
        	setJsRequest($layout_settings["ADMIN_INTERFACE_PLUGIN"]);
		}

        if($template_name == "popup") {
            $icon_size = null;
            $allow_delete = true;
        } else {
            $icon_size = "lg";          
            $icon_layout_size = "lg";
            $allow_delete = false;
            $tpl->set_var("block_edit_class", cm_getClassByFrameworkCss(null, "icon", "stack")); 
            $tpl->set_var("block_edit_icon", cm_getClassByFrameworkCss("th-large", "icon-tag", $icon_layout_size)); 
            $tpl->set_var("block_delete_class", cm_getClassByFrameworkCss(null, "icon", "stack"));
            $tpl->set_var("block_delete_icon", cm_getClassByFrameworkCss("deleterow", "icon-tag", $icon_layout_size));
            $tpl->set_var("block_delete_class", cm_getClassByFrameworkCss("deleterow", "icon", $icon_layout_size));
            
            $tpl->set_var("toggle_class", cm_getClassByFrameworkCss("ellipsis-v", "icon", $icon_layout_size)); 
        }
		
        if(isset($admin_menu["group"]) && strlen($admin_menu["group"])) { 
			$tpl->set_var("block_type_class", $admin_menu["group"]);
	        $tpl->set_var("dialog_pre", ffCommon_specialchars('<h1 class="admin-title ' . $admin_menu["group"] . '">'));
	        $tpl->set_var("dialog_post", ffCommon_specialchars('</h1>'));
		}

		if(isset($admin_menu["class"]) && strlen($admin_menu["class"])) { 
            $tpl->set_var("block_class", cm_getClassByFrameworkCss("vg-" . $admin_menu["class"], "icon", array($admin_menu["group"], "2x")));
            $tpl->set_var("block_icon", cm_getClassByFrameworkCss("vg-" . $admin_menu["class"], "icon-tag", array($admin_menu["group"], "2x")));
		}

		if(isset($admin_menu["title"]) && strlen($admin_menu["title"])) {
        	$tpl->set_var("item_name", $admin_menu["title"]);
            $tpl->parse("SezMenuAdminTitle", false);
		}

        if(file_exists(FF_DISK_PATH . $cm->oPage->getThemePath(false) . "/images/icons/sep." . THEME_ICO_EXTENSION)) {
			$tpl->set_var("icon_sep_path", FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $cm->oPage->theme . "/images/icons/sep." . THEME_ICO_EXTENSION);
		} else {
			$tpl->set_var("icon_sep_path", FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . THEME_INSET . "/images/icons/" . THEME_ICO . "/sep." . THEME_ICO_EXTENSION);
		}
        

        
        
        $tpl->set_var("modify_class", cm_getClassByFrameworkCss("editrow", "icon", $icon_size)); 
        $tpl->set_var("modify_icon", cm_getClassByFrameworkCss("editrow", "icon-tag", $icon_size)); 
        $tpl->set_var("delete_class", cm_getClassByFrameworkCss("deleterow", "icon", $icon_size));
        $tpl->set_var("delete_icon", cm_getClassByFrameworkCss("deleterow", "icon-tag", $icon_size));

        if(isset($admin_menu["layout"]) && is_array($admin_menu["layout"])) {
            $count_element++;
            if($admin_menu["layout"]["ID"] > 0) {
	            $tpl->set_var("item_modify_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/modify");
				$tpl->set_var("item_delete_path", urlencode(ffDialog(TRUE,
													"yesno",
													ffTemplate::_get_word_by_code("vgallery_erase_title"),
													ffTemplate::_get_word_by_code("vgallery_erase_description"),
													$cancel_dialog_url,
													FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/modify?keys[ID]=" . urlencode($admin_menu["layout"]["ID"]) . "&location=" . urlencode($location) . "&path=" . urlencode($user_path) . "&LayoutModify_frmAction=confirmdelete",
													FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block" . "/dialog")
									));
	            $tpl->set_var("item_id", urlencode($admin_menu["layout"]["ID"]));
	            $tpl->set_var("item_location", urlencode($location));
	            $tpl->set_var("item_path", urlencode($user_path));

	            $tpl->parse("SezLayoutDelete", false);
			} else {
				$tpl->set_var("item_modify_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/layout/block/settings/modify/" . strtolower($admin_menu["layout"]["type"]));

			   	$tpl->set_var("SezLayoutDelete", "");
			}    
			$tpl->parse("SezLayout", false);
        } else {
               $tpl->set_var("SezLayoutModify", "");
               $tpl->set_var("SezLayoutDefaultModify", "");
               $tpl->set_var("SezLayoutDelete", "");
               
               $tpl->set_var("SezLayout", "");
        }

        if(isset($admin_menu["module"]) && is_array($admin_menu["module"]) && count($admin_menu["module"])) {
			if(array_key_exists("value", $admin_menu["module"]) && array_key_exists("params", $admin_menu["module"]))
			{
				$count_element++;
				
		        $tpl->set_var("item_modify_path", FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $admin_menu["module"]["value"] . "/config/modify/" . $admin_menu["module"]["params"]);
				$tpl->set_var("class_name", $class_name . $class_primary);
		        $tpl->parse("SezModuleModify", false);

                if($allow_delete) {
					$tpl->set_var("item_delete_path", urlencode(ffDialog(TRUE,
														"yesno",
														ffTemplate::_get_word_by_code("vgallery_erase_title"),
														ffTemplate::_get_word_by_code("vgallery_erase_description"),
														$cancel_dialog_url,
														FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $admin_menu["module"]["value"] . "/config/modify/" . $admin_menu["module"]["params"] . "?form-config_frmAction=confirmdelete",
														FF_SITE_PATH . VG_SITE_ADMINGALLERY . "/modules/" . $admin_menu["module"]["value"] . "/config/modify/dialog")
										));
				    $tpl->set_var("class_name", $class_name);
                    $tpl->parse("SezModuleDelete", false);
                } else {
                    $tpl->set_var("SezModuleDelete", "");
                }
			}
			if(array_key_exists("extra", $admin_menu["module"]) && strlen($admin_menu["module"]["extra"]))
			{
				$count_element++;
                $tpl->set_var("property_icon", cm_getClassByFrameworkCss("table", "icon-tag", $icon_size));
                $tpl->set_var("property_class", cm_getClassByFrameworkCss("table", "icon", $icon_size));

				$tpl->set_var("item_path", $admin_menu["module"]["extra"]);
				$tpl->set_var("class_name", $class_name);
				$tpl->parse("SezModuleExtra", false);
			}			
        } else {
        	$tpl->set_var("SezModule", "");
            $tpl->set_var("SezModuleExtra", "");
        }

        if(isset($admin_menu["adddir"]) && strlen($admin_menu["adddir"])) {
            $count_element++;

            $tpl->set_var("item_path", $admin_menu["adddir"]);
            
            $tpl->set_var("adddir_class", cm_getClassByFrameworkCss(null, "icon", "stack"));
            $tpl->set_var("adddir_icon", implode("", cm_getClassByFrameworkCss(array("plus", "folder"), "icon-tag", "stack")));

			$tpl->set_var("class_name", $class_name);
            $tpl->parse("SezMenuAdminAddDir", false);
        } else {
               $tpl->set_var("SezMenuAdminAddDir", "");
        }

        if(isset($admin_menu["addnew"]) && strlen($admin_menu["addnew"])) {
            $count_element++;

            $tpl->set_var("item_path", $admin_menu["addnew"]);

            $tpl->set_var("addnew_class", cm_getClassByFrameworkCss(null, "icon", "stack"));
            $tpl->set_var("addnew_icon", implode("", cm_getClassByFrameworkCss(array("plus", "file"), "icon-tag", "stack")));

            $tpl->set_var("class_name", $class_name . $class_primary);
            $class_primary = "";
            $tpl->parse("SezMenuAdminAddNew", false);
        } else {
               $tpl->set_var("SezMenuAdminAddNew", "");
        }
        
        if(isset($admin_menu["modify"]) && strlen($admin_menu["modify"])) {
            $count_element++;

            $tpl->set_var("item_path", $admin_menu["modify"]);
            $tpl->set_var("class_name", $class_name . $class_primary);
            $class_primary = "";
            $tpl->parse("SezMenuAdminModify", false);
        } else {
               $tpl->set_var("SezMenuAdminModify", "");
        }

        if($allow_delete && isset($admin_menu["delete"]) && strlen($admin_menu["delete"])) {
            $count_element++;
			$tpl->set_var("item_path", urlencode($admin_menu["delete"]));
			$tpl->set_var("class_name", $class_name);
            $tpl->parse("SezMenuAdminDelete", false);
        } else {
               $tpl->set_var("SezMenuAdminDelete", "");
        }

		if(AREA_PROPERTIES_SHOW_MODIFY && isset($admin_menu["fields"]) && strlen($admin_menu["fields"])) {
            $count_element++;
            
            if(strpos($admin_menu["fields"], "?") === false) {
            	$admin_menu["fields"] = $admin_menu["fields"];
			} else {
				$admin_menu["fields"] = $admin_menu["fields"];
			}
            $tpl->set_var("item_path", $admin_menu["fields"]);
            
            $tpl->set_var("fields_icon", cm_getClassByFrameworkCss("table", "icon-tag", $icon_size));
            $tpl->set_var("fields_class", cm_getClassByFrameworkCss("table", "icon", $icon_size));
			$tpl->set_var("class_name", $class_name);
            $tpl->parse("SezMenuAdminFields", false);
        } else {
               $tpl->set_var("SezMenuAdminFields", "");
        }
        
        if(AREA_PROPERTIES_SHOW_MODIFY && isset($admin_menu["extra"]) && strlen($admin_menu["extra"])) {
            $count_element++;
            
            $tpl->set_var("item_path", $admin_menu["extra"]);
            
            $tpl->set_var("property_icon", cm_getClassByFrameworkCss("object-group", "icon-tag", $icon_size));
            $tpl->set_var("property_class", cm_getClassByFrameworkCss("object-group", "icon", $icon_size));
			$tpl->set_var("class_name", $class_name);
            $tpl->parse("SezMenuAdminExtra", false);
        } else {
               $tpl->set_var("SezMenuAdminExtra", "");
        }

        if(AREA_SHOW_ECOMMERCE && isset($admin_menu["ecommerce"]) && strlen($admin_menu["ecommerce"])) {
            $count_element++;

            $tpl->set_var("item_path", $admin_menu["ecommerce"]);

			$tpl->set_var("ecommerce_icon", cm_getClassByFrameworkCss("shopping-cart", "icon-tag", $icon_size));
            $tpl->set_var("ecommerce_class", cm_getClassByFrameworkCss("shopping-cart", "icon", $icon_size));
			$tpl->set_var("class_name", $class_name);
            $tpl->parse("SezMenuAdminEcommerce", false);
        } else {
               $tpl->set_var("SezMenuAdminEcommerce", "");
        }

        if(AREA_SETTINGS_SHOW_MODIFY && isset($admin_menu["setting"])) {
        	$skip_setting = false;
			if(is_array($admin_menu["setting"]) && count($admin_menu["setting"])) {
	            $tpl->set_var("item_path", $admin_menu["setting"]["path"]);
			} elseif(strlen($admin_menu["setting"])) {
	            $tpl->set_var("item_path", FF_SITE_PATH . VG_SITE_PERMISSION . "/modify" . $settings_path . "?area=" . urlencode($admin_menu["setting"]));
        	} else {
				$skip_setting = true;
        	}
        	if(!$skip_setting) {
	            $count_element++;
	            
	            $tpl->set_var("setting_icon", cm_getClassByFrameworkCss("lock", "icon-tag", $icon_size));
                $tpl->set_var("setting_class", cm_getClassByFrameworkCss("lock", "icon", $icon_size));
				$tpl->set_var("class_name", $class_name);
	            $tpl->parse("SezMenuAdminSetting", false);
			}
        } else {
               $tpl->set_var("SezMenuAdminSetting", "");
        }
        
        if($count_element) {
            return $tpl->rpparse("main", false);
        } 
    }
             
    return "";
}
