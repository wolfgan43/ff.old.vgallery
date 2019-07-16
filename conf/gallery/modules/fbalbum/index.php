<?php
//    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

	$oRecord = ffRecord::factory($oPage);

    $db_gallery->query("SELECT module_fbalbum.*
                            FROM 
                                module_fbalbum
                            WHERE 
                                module_fbalbum.name = " . $db_gallery->toSql(new ffData($oRecord->user_vars["MD_chk"]["params"][0])));
    if($db_gallery->nextRecord()) { 
    	$fb_lang = strtolower(substr(FF_LOCALE, 0, 2)) . "_" . strtoupper(substr(FF_LOCALE, 0, 2));

        $fbalbum_name = ffCommon_url_rewrite($db_gallery->getField("name", "Text", true));
        $fbalbum_facebookID = $db_gallery->getField("facebookID", "Text", true);
        $fbalbum_exclude_album = $db_gallery->getField("exclude_album", "Text", true);
        if(strlen($fbalbum_exclude_album)) {
			$arrExcludeAlbum = explode(",", $fbalbum_exclude_album);
			if(is_array($arrExcludeAlbum) && count($arrExcludeAlbum)) {
				foreach($arrExcludeAlbum AS $arrExcludeAlbum_value) {
					if(strlen($str_fbalbum_exclude_album))
						$str_fbalbum_exclude_album .= ",";
					
					$str_fbalbum_exclude_album .= "'" . $arrExcludeAlbum_value . "'";
				}
			}
        }
        $fbalbum_exclude_image = $db_gallery->getField("exclude_image", "Text", true);
        if(strlen($fbalbum_exclude_image)) {
			$arrExcludeImage = explode(",", $fbalbum_exclude_image);
			if(is_array($arrExcludeImage) && count($arrExcludeImage)) {
				foreach($arrExcludeImage AS $arrExcludeImage_value) {
					if(strlen($str_fbalbum_exclude_image))
						$str_fbalbum_exclude_image .= ",";
					
					$str_fbalbum_exclude_image .= "'" . $arrExcludeImage_value . "'";
				}
			}
        }
		$fbalbum_use_tooltip		= $db_gallery->getField("use_tooltip", "Number", true);
		$fbalbum_use_fancybox		= $db_gallery->getField("use_fancybox", "Number", true);
		$fbalbum_use_colorbox		= $db_gallery->getField("use_colorbox", "Number", true);
		$fbalbum_use_prettyphoto	= $db_gallery->getField("use_prettyphoto", "Number", true);
		

        $tpl = ffTemplate::factory(get_template_cascading($user_path, "albums.html", "/modules/fbalbum", __DIR__));
        $tpl->load_file("albums.html", "main");

        $tpl->set_var("site_path", FF_SITE_PATH);
        $tpl->set_var("theme_inset", THEME_INSET);
        $tpl->set_var("domain_inset", DOMAIN_INSET);
        $tpl->set_var("language_inset", LANGUAGE_INSET);

        $tpl->set_var("real_name", $fbalbum_name);
        $tpl->set_var("facebook_id", $fbalbum_facebookID);
        $tpl->set_var("exclude_album", $str_fbalbum_exclude_album);
 		$tpl->set_var("exclude_image", $str_fbalbum_exclude_image);
 		$tpl->set_var("fb_lang", $fb_lang);

		if($fbalbum_use_fancybox) {
            $tpl->set_var("use_fancybox", "true");
			$tpl->parse("SezFancyBox", false);
			$tpl->set_var("use_colorbox", "false");
			$tpl->set_var("use_prettyphoto", "false");
			$tpl->set_var("SezColorBox", "");
			$tpl->set_var("SezPrettyPhoto", "");
		} elseif($fbalbum_use_colorbox) {
			$tpl->set_var("use_colorbox", "true");
			$tpl->parse("SezColorBox", false);
			$tpl->set_var("use_fancybox", "false");
			$tpl->set_var("use_prettyphoto", "false");
			$tpl->set_var("SezFancyBox", "");
			$tpl->set_var("SezPrettyPhoto", "");
		} elseif($fbalbum_use_prettyphoto) {
			$tpl->set_var("use_prettyphoto", "true");
			$tpl->parse("SezPrettyPhoto", false);
			$tpl->set_var("use_fancybox", "false");
		    $tpl->set_var("use_colorbox", "false");
			$tpl->set_var("SezFancyBox", "");
			$tpl->set_var("SezColorBox", "");
		} else {
		    $tpl->set_var("use_fancybox", "false");
		    $tpl->set_var("use_colorbox", "false");
			$tpl->set_var("use_prettyphoto", "false");
			$tpl->set_var("SezFancyBox", "");
			$tpl->set_var("SezColorBox", "");
			$tpl->set_var("SezPrettyPhoto", "");
		}
		
		if($fbalbum_use_tooltip) {
            if(check_function("get_resource_cascading")) {
			    get_resource_cascading($oPage, $user_path, "jquery." . "qtip" . ".css", FF_THEME_DIR . "/library/plugins/jquery.qtip2");
            }
    		$oPage->tplAddJs("jQuery.fn.qtip", "jquery.qtip.js", FF_THEME_DIR . "/library/plugins/jquery.qtip2");

		    $tpl->set_var("use_tooltip", "true");
		    $tpl->parse("SezToolTip", false);
		} else {
			$tpl->set_var("use_tooltip", "false");
			$tpl->set_var("SezToolTip", "");
		}
		
        if(check_function("get_resource_cascading")) {
		    get_resource_cascading($oPage, $user_path, "jquery." . "fbalbum" . ".css", FF_THEME_DIR . "/jquery.fbalbum/css");
        }
    	$oPage->tplAddJs("jquery.isotope", "jquery.isotope.js", FF_THEME_DIR . "/library/plugins/jquery.isotope");
    	
		$oPage->tplAddJs("FB", "all.js", "http://connect.facebook.net/" . $fb_lang);
    	$oPage->tplAddJs("jquery.fbalbum", "jquery.fbalbum.js", FF_THEME_DIR . "/library/plugins/jquery.fbalbum");

        $oPage->addContent($tpl->rpparse("main", false), null, $oRecord->user_vars["MD_chk"]["id"]);
    }
?>