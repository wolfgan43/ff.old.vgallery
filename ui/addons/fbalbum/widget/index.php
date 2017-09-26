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
    $db = ffDB_Sql::factory();

	$oRecord = ffRecord::factory($cm->oPage);

    $db->query("SELECT module_fbalbum.*
                            FROM 
                                module_fbalbum
                            WHERE 
                                module_fbalbum.name = " . $db->toSql($MD_chk["params"][0]));
    if($db->nextRecord()) { 
    	$fb_lang = strtolower(substr(FF_LOCALE, 0, 2)) . "_" . strtoupper(substr(FF_LOCALE, 0, 2));

        $fbalbum_name = ffCommon_url_rewrite($db->getField("name", "Text", true));
        $fbalbum_facebookID = $db->getField("facebookID", "Text", true);
        $fbalbum_exclude_album = $db->getField("exclude_album", "Text", true);
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
        $fbalbum_exclude_image = $db->getField("exclude_image", "Text", true);
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
		$fbalbum_use_tooltip		= $db->getField("use_tooltip", "Number", true);
		$fbalbum_use_fancybox		= $db->getField("use_fancybox", "Number", true);
		$fbalbum_use_colorbox		= $db->getField("use_colorbox", "Number", true);
		$fbalbum_use_prettyphoto	= $db->getField("use_prettyphoto", "Number", true);
		

        $tpl = ffTemplate::factory(get_template_cascading($user_path, "albums.html", "/modules/fbalbum", ffCommon_dirname(__FILE__)));
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
    		$cm->oPage->tplAddJs("jquery.plugins.qtip2");

		    $tpl->set_var("use_tooltip", "true");
		    $tpl->parse("SezToolTip", false);
		} else {
			$tpl->set_var("use_tooltip", "false");
			$tpl->set_var("SezToolTip", "");
		}

    	$cm->oPage->tplAddJs("jquery.plugins.fbalbum");
    	
        $cm->oPage->addContent($tpl->rpparse("main", false), null, $MD_chk["id"]);
    }