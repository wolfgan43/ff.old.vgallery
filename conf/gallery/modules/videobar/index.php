<?php
	$oRecord = ffRecord::factory($oPage);

	$db_gallery->query("SELECT module_videobar.*
	                        FROM 
	                            module_videobar
	                        WHERE 
	                            module_videobar.name = " . $db_gallery->toSql(new ffData($oRecord->user_vars["MD_chk"]["params"][0])));
	if($db_gallery->nextRecord()) {  
		$oPage->tplAddJs("google.search", "api?file=uds.js&v=1.0&source=uds-vbw", "http://www.google.com/uds", false, false, null, true);	
		$oPage->tplAddJs("google.search.videobar", "gsvideobar.js?mode=new", "http://www.google.com/uds/solutions/videobar", false, false, null, true);	
		$oPage->tplAddJs("youtube.videobar", "videobar.js", VG_SITE_MODULE_RESOURCE . "/videobar", false, false, null, true);	

		$oPage->tplAddCss("google.search.videobar"
						, "gsvideobar.css"
						, "http://www.google.com/uds/solutions/videobar"						
						, "stylesheet"
						, "text/css"
						, false
						, false
						, null
						, true
						, "bottom");

		
	    $videobar_real_name = preg_replace('/[^a-zA-Z0-9]/', '', $db_gallery->getField("name")->getValue());
	    $videobar_quantity = $db_gallery->getField("quantity")->getValue();
	    $videobar_mode = $db_gallery->getField("mode")->getValue();
	    $videobar_std_list = $db_gallery->getField("std_list")->getValue();
	    $videobar_channel_list = $db_gallery->getField("channel_list")->getValue();
	    $videobar_search_list = $db_gallery->getField("search_list")->getValue();
		
		$tpl = ffTemplate::factory(get_template_cascading($user_path, "videobar.html", "/modules/videobar", ffCommon_dirname(__FILE__)));
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
			
	    $oPage->addContent($tpl->rpparse("main", false), null, "GoogleYouTubeVideoBar");
	}
?>
