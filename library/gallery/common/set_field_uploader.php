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
	function set_field_uploader($component) {
		$cm = cm::getInstance();

		static $dialog_loaded = false;

        switch($component->widget) {
            case "uploadifive":
            case "uploadify":
                $component->widget = "uploadifive";
                break;
            case "kcuploadifive":
            case "kcuploadify":
                $component->widget = "kcuploadifive";
                break;
            case "ckuploadifive":
            case "ckuploadify":
                $component->widget = "ckuploadifive";
                break;
            default:
        }
		
		$component->file_show_edit = true; 
        if(strpos($component->widget, "five") !== false) {
            if(!$dialog_loaded) {
            	$dialog_loaded = "showfilesManage";
		        $cm->oPage->widgetLoad("dialog");
				$cm->oPage->widgets["dialog"]->process(
					 $dialog_loaded
					, array(
				        "title"          => $component->label
						, "tpl_id"        => null
					)
					, $cm->oPage
				);        
			}
			$component->file_modify_path = FF_SITE_PATH . VG_SITE_RESTRICTED . "/resources/modify";
			$component->file_modify_dialog = $dialog_loaded;
			$component->uploadifive_sort_path = FF_SITE_PATH . VG_SITE_SERVICES . "/sort";

			$component->file_edit_type = "Aviary";
			if(check_function("get_webservices")) {
				$services_params = get_webservices("img.aviary");

			    if($services_params["enable"]) {
					$component->file_edit_params["Aviary"] = array(
						"key" => $services_params["key"]
						, "tools" => $services_params["tools"]
						, "theme" => $services_params["theme"]
						, "version" => $services_params["version"]
						, "post_url" => $services_params["post_url"]
					); 
			    } 
			}
		}

		return $component;
	}
