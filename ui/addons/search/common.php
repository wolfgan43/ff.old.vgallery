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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function MD_search_config_on_do_action($component, $action) {
    $theme = $component->parent[0]->theme;
    $search_path = "/search/" . ffCommon_url_rewrite($component->form_fields["name"]->getValue());
    
    switch ($action) {
        case "insert":
        	if(check_function("fs_operation")) {
        		$res = xcopy(FF_THEME_DIR . "/" . cm_getMainTheme() . "/ff/ffRecord/ffRecord.html"
        					, FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $search_path . "/search.tpl"
        				);
			}
    /*
            $res = true;
            
            if(!is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/search")) {
                $res = @mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/search");
                if($res) {
                    $res = @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/search", 0777);
                }
            }            

            if($res && !is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $search_path)) {
                $res = @mkdir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $search_path);
                if($res) {
                    $res = @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $search_path, 0777);
                }
            }

            if($res) {
                if($component->form_fields["tpl_search_path"]->getValue() == "") {
                    if(@copy(FF_DISK_PATH . FF_THEME_DIR . "/" . cm_getMainTheme() . "/ff/ffRecord/ffRecord.html", FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $search_path . "/search.tpl")) {
                        @chmod(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $search_path . "/search.tpl", 0777);
                        $component->form_fields["tpl_search_path"]->setValue("/modules" . $search_path . "/search.tpl", "Text");
                    }
                }
            }
    */
            break;
        case "update":
        
            break;
        case "confirmdelete":
        	if(check_function("fs_operation"))
            	xpurge_dir(FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH .  "/modules" . $search_path);
        
           // purge_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules" . $search_path, $search_path, false);
            break;
            
        default:            
    }
}

function MD_search_on_done_action($component, $action) {
	foreach($component->form_fields as $form_key => $form_value) {
		if(strlen($form_value->getValue())) {
			if(strlen($encoded_search_param))
				$encoded_search_param .= "&";

			$encoded_search_param .= "sap[" . urlencode($form_key) . "]=" . urlencode($form_value->getValue());
		}
	}

	if(strlen($encoded_search_param)) {
        if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
            $new_request_uri = $_SERVER["REQUEST_URI"];
            $new_request_uri = str_replace(VG_SITE_FRAME, "", $new_request_uri);
            $new_request_uri = str_replace("sid=" . $_REQUEST["sid"] . "&", "", $new_request_uri);
            $new_request_uri = str_replace("sid=" . $_REQUEST["sid"], "", $new_request_uri);
            
            $addparam = "&ret_url=" . urlencode($new_request_uri) . "&__nocache__"; 
        } else
            $addparam = "&ret_url=" . urlencode($_SERVER['REQUEST_URI']);
            
        $component->redirect(FF_SITE_PATH . VG_SITE_SEARCH . "/" . $component->user_vars["search_name"] . "?" . $encoded_search_param . $addparam);
        
	} else {
        $component->tplDisplayError(ffTemplate::_get_word_by_code("search_fields_empty"));
        return true;
	}
}


