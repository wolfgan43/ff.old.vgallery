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
	$db->query("SELECT module_videobar.*
	                        FROM 
	                            module_videobar
	                        WHERE 
	                            module_videobar.name = " . $db->toSql($MD_chk["params"][0]));
	if($db->nextRecord()) {  
		$cm->oPage->tplAddJs("google.search"
            , array(
                "file" => "api?file=uds.js&v=1.0&source=uds-vbw"
                , "path" => "http://www.google.com/uds"
                , "exclude_compact" => true
        ));
		$cm->oPage->tplAddJs("google.search.videobar"
            , array(
                "file" => "gsvideobar.js?mode=new"
                , "path" => "http://www.google.com/uds/solutions/videobar"
                , "exclude_compact" => true
        ));
		$cm->oPage->tplAddJs("ff.cms.addons.videobar", array(
			"embed" => file_get_contents(__DIR__ . "/videobar.js")
		));

		$cm->oPage->tplAddCss("google.search.videobar"
            , array(
                "file" => "gsvideobar.css"
                , "path" => "http://www.google.com/uds/solutions/videobar"
        ));

	    $videobar_real_name = preg_replace('/[^a-zA-Z0-9]/', '', $db->getField("name")->getValue());
	    $videobar_quantity = $db->getField("quantity")->getValue();
	    $videobar_mode = $db->getField("mode")->getValue();
	    $videobar_std_list = $db->getField("std_list")->getValue();
	    $videobar_channel_list = $db->getField("channel_list")->getValue();
	    $videobar_search_list = $db->getField("search_list")->getValue();
		
		$tpl = ffTemplate::factory(get_template_cascading($user_path, "videobar.html", "/modules/videobar", __DIR__));
	    $tpl->load_file("videobar.html", "main");

	    $tpl->set_var("site_path", FF_SITE_PATH);
	    $tpl->set_var("theme_inset", THEME_INSET);
	    $tpl->set_var("domain_inset", DOMAIN_INSET);
	    $tpl->set_var("language_inset", LANGUAGE_INSET);

	    $tpl->set_var("real_name", $videobar_real_name);
	    if($videobar_quantity)
	    	$tpl->set_var("quantity", "true");
		else
			$tpl->set_var("quantity", "false");

	    if($videobar_mode)
	    	$tpl->set_var("mode", "false");
		else
			$tpl->set_var("mode", "true");


		if(strlen($videobar_std_list)) {
			$arrVideobar_std_list = explode(",", $videobar_std_list);
			if(is_array($arrVideobar_std_list) && count($arrVideobar_std_list)) {
				foreach($arrVideobar_std_list AS $arrVideobar_std_list_value) {
					if(strlen($strExecuteList))
						$strExecuteList .= ",";

					$strExecuteList = '"' . $arrVideobar_std_list_value . '"';
				}
			}
		}
			
		if(strlen($videobar_channel_list)) {
			$arrVideobar_channel_list = explode(",", $videobar_channel_list);
			if(is_array($arrVideobar_channel_list) && count($arrVideobar_channel_list)) {
				foreach($arrVideobar_channel_list AS $arrVideobar_channel_list_value) {
					if(strlen($strExecuteList))
						$strExecuteList .= ",";

					$strExecuteList = '"' . $arrVideobar_channel_list_value . '"';
				}
			}
		}

		if(strlen($videobar_search_list)) {
			$arrVideobar_search_list = explode(",", $videobar_search_list);
			if(is_array($arrVideobar_search_list) && count($arrVideobar_search_list)) {
				foreach($arrVideobar_search_list AS $arrVideobar_search_list_value) {
					if(strlen($strExecuteList))
						$strExecuteList .= ",";

					$strExecuteList = '"' . $arrVideobar_search_list_value . '"';
				}
			}
		}
		if(!strlen($strExecuteList))
			$strExecuteList = '""';			

		$tpl->set_var("execute_list", $strExecuteList);			
			
	    $cm->oPage->addContent($tpl->rpparse("main", false), null, "GoogleYouTubeVideoBar");
	}
