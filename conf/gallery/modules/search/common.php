<?php
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

function MD_search_on_tpl_parse($component, $tpl) {
 
	$tpl->set_var("insert:onclick", $component->buttons_options["insert"]["url"]);
	$tpl->set_var("properties", $component->getProperties());


}
?>
