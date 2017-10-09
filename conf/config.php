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
 * @subpackage config
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

 if(defined("SHOWFILES_IS_RUNNING")) { 
	$ff = ffGlobals::getInstance("ff");
	if($ff->showfiles_events) {
        $ff->showfiles_events->addEvent("on_warning", "showfiles_on_missing_resource", ffEvent::PRIORITY_NORMAL);
		$ff->showfiles_events->addEvent("on_error", "showfiles_on_before_parsing_error", ffEvent::PRIORITY_NORMAL);
    }
    //cm::_addEvent("showfiles_on_error", "showfiles_on_before_parsing_error", ffEvent::PRIORITY_NORMAL);
    
	function showfiles_on_missing_resource($mode) {
		$res = null;
		if(!function_exists("write_notification")) {
			require_once(FF_DISK_PATH . "/library/" . THEME_INSET . "/common." . FF_PHP_EXT);

            if(check_function("write_notification"))
				write_notification("_error_notfound_path", "CM ShowFiles: " . $mode, "warning", "", $_SERVER["REQUEST_URI"]);
        }
		http_response_code(404);	//da migliorare usando cache_olp_path e quindi redirect 301
		if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/404.png")) {
			$res["base_path"] = FF_DISK_PATH . FF_THEME_DIR;
			$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/404.png";
		} elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET . "/images/error-pages/404.png")) {
			$res["base_path"] = FF_DISK_PATH . FF_THEME_DIR;
			$res["filepath"] = FF_DISK_PATH . FF_THEME_DIR . "/" . THEME_INSET  . "/images/error-pages/404.png";
		}
		
		if(stripos($mode, "x") !== false) {
	        $res["wizard"]["mode"] 					= explode("x", strtolower($mode));
	        $res["wizard"]["method"] 				= "proportional";
	        $res["wizard"]["resize"] 				= true;
	    } elseif(strpos($mode, "-") !== false) {
	        $res["wizard"]["mode"] 					= explode("-", $mode);
	        $res["wizard"]["method"] 				= "crop";
	        $res["wizard"]["resize"] 				= false;
	    }		
		return $res;
	}
    
    function showfiles_on_before_parsing_error($strError) {
        http_response_code(404);    //da migliorare usando cache_olp_path e quindi redirect 301
        exit;
    }
} elseif(!defined("GALLERY_INSTALLATION_PHASE") && !defined("SKIP_CMS")) {
	global $ff_global_setting;

	$ff_global_setting["ffRecord"]["widget_discl_enable"] = false;
	$ff_global_setting["ffGrid"]["widget_discl_enable"] = false;
	$ff_global_setting["ffGrid_html"]["reset_page_on_search"] = true;

/*
	$ff_global_setting["ffPageNavigator"]["framework_css"]["component"]["class"] = "pagenav";
	$ff_global_setting["ffPageNavigator"]["framework_css"]["pagination"]["class"] = "pagenav__pages";
	$ff_global_setting["ffPageNavigator"]["framework_css"]["pagination"]["col"] = null;
	$ff_global_setting["ffPageNavigator"]["framework_css"]["choice"]["class"] = "pagenav__choice";
	$ff_global_setting["ffPageNavigator"]["framework_css"]["choice"]["col"] = null;
	$ff_global_setting["ffPageNavigator"]["framework_css"]["totelem"]["class"] = "pagenav__totEl";
	$ff_global_setting["ffPageNavigator"]["framework_css"]["totelem"]["col"] = null;
	$ff_global_setting["ffPageNavigator"]["framework_css"]["perPage"]["class"] = "pagenav__perPage";
	$ff_global_setting["ffPageNavigator"]["framework_css"]["perPage"]["col"] = null;*/



	$ff_global_setting["ffPageNavigator"]["with_choice"] = true;
	$ff_global_setting["ffPageNavigator"]["with_totelem"] = true;
	$ff_global_setting["ffPageNavigator"]["PagePerFrame"] = 7;
	$ff_global_setting["ffPageNavigator"]["nav_selector_elements_all"] = true;

	$ff_global_setting["ffField"]["file_check_exist"] = true;
	$ff_global_setting["ffField"]["placeholder"] = true;
	$ff_global_setting["ffField"]["multi_select_one_label"] = ffTemplate::_get_word_by_code("multi_select_one_label");


	$ff_global_setting["ffField_html"]["encode_label"] = false;

	$ff_global_setting["ffGrid"]["symbol_valuta"] = "";    
	$ff_global_setting["ffGrid"]["switch_row_class"]["display"] = true;

	$ff_global_setting["ffGrid"]["open_adv_search"] = "never";
	$ff_global_setting["ffGrid"]["buttons_options"]["search"] = array(
	                                                                  "display"     => true
	                                                                  , "label"		=> false
	                                                        );
	$ff_global_setting["ffGrid"]["buttons_options"]["export"] = array(
	                                                                "display"        => true
	                                                        );
	$ff_global_setting["ffGrid_dialog"]["buttons_options"]["export"] = array(
	                                                                "display"		=> false
	                                                        );                                                            


	$ff_global_setting["ffDetails_horiz"]["switch_row_class"]["display"] = true;

	
	
	/*$ff_global_setting["ffWidget_actex"]["innerURL"] 					= null;
	$ff_global_setting["ffWidget_activecomboex"]["innerURL"] 			= null;
	$ff_global_setting["ffWidget_autocomplete"]["innerURL"] 			= null;
	$ff_global_setting["ffWidget_autocompletetoken"]["innerURL"] 		= null;*/
	//if(is_file(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/admin." . FF_PHP_EXT))
//		require_once(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/admin." . FF_PHP_EXT);

//	if(is_file(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/" . str_replace("/", "_", trim($cm->path_info . $cm->real_path_info, "/")) . "." . FF_PHP_EXT))
//		require_once(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/" . str_replace("/", "_", trim($cm->path_info . $cm->real_path_info, "/")) . "." . FF_PHP_EXT);
}
