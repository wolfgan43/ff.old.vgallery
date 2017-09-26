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
    
	if (!MODULE_SHOW_CONFIG) {
	    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	}
    $db = ffDB_Sql::factory();
	check_function("system_ffcomponent_set_title");

	$record = system_ffComponent_resolve_record("module_videobar", array(
		"quantity" => null
		, "mode" => null
		, "std_list" => null
		, "channel_list" => null
		, "search_list" => null
	));    
	
	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->buttons_options["insert"]["display"] = false;
	$oRecord->buttons_options["update"]["display"] = false;
	$oRecord->buttons_options["delete"]["display"] = false;

	/* Title Block */
	system_ffcomponent_set_title(
	    null
	    , true
	    , false
	    , false
	    , $oRecord
	);  

	if($_REQUEST["keys"]["ID"]) {
		$cm->oPage->tplAddJs("google.search", array(
			"path" => "http://www.google.com/uds"
			, "name" => "api?file=uds.js&v=1.0&source=uds-vbw"
		));
		$cm->oPage->tplAddJs("google.search.videobar", array(
			"path" => "http://www.google.com/uds/solutions/videobar"
			, "name" => "gsvideobar.js?mode=new"
		));
		$cm->oPage->tplAddJs("ff.cms.addons.videobar", array(
			"embed" => file_get_contents(__DIR__ . "/widget/videobar.js")
		));
		$cm->oPage->tplAddCss("google.search.videobar", array(
			"path" => "http://www.google.com/uds/solutions/videobar"
			, "name" => "gsvideobar.css" 
		));
		
	    $videobar_real_name = preg_replace('/[^a-zA-Z0-9]/', '', $record["name"]);
	    $videobar_quantity = $record["quantity"];
	    $videobar_mode = $record["mode"];
	    $videobar_std_list = $record["std_list"];
	    $videobar_channel_list = $record["channel_list"];
	    $videobar_search_list = $record["search_list"]; 

	    $tpl = ffTemplate::factory(__DIR__ . "/widget");
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
			
	    $oRecord->fixed_pre_content = $tpl->rpparse("main", false);
	}
	
	$cm->oPage->addContent($oRecord);
