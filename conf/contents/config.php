<?php
	global $ff_global_setting;
	
	if(!defined("GALLERY_INSTALLATION_PHASE") && !defined("SKIP_CMS")) {
		$ff_global_setting["ffPage_html"]["form_workaround"] = true;
		$ff_global_setting["ffPage_html"]["jquery_ui_force_theme"] = "base";
		
		$ff_global_setting["ffField_html"]["encode_label"] = false;
		//$ff_global_setting["ffField"]["multi_select_one_label"] = ffTemplate::_get_word_by_code("multi_select_one_label");

		//if(!is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/widgets/activecomboex"))
			$ff_global_setting["ffWidget_activecomboex"]["theme"] = CM_DEFAULT_THEME;

		//if(!is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/widgets/autocomplete"))
			$ff_global_setting["ffWidget_autocomplete"]["theme"] = CM_DEFAULT_THEME;

		//if(!is_dir(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/jquery.ui"))
			$ff_global_setting["ffWidget_datepicker"]["theme"] = CM_DEFAULT_THEME;
			
			
		$ff_global_setting["ffWidget_actex"]["innerURL"] 				= null; //FF_SITE_PATH . "/actexparse";
		$ff_global_setting["ffWidget_activecomboex"]["innerURL"] 		= null; //FF_SITE_PATH . "/parsedata";
		$ff_global_setting["ffWidget_autocomplete"]["innerURL"] 		= null; //FF_SITE_PATH . "/aparsedata";
		$ff_global_setting["ffWidget_autocompletex"]["innerURL"] 		= null; //FF_SITE_PATH . "/aparsedatax";
		$ff_global_setting["ffWidget_autocompletetoken"]["innerURL"] 	= null; //FF_SITE_PATH . "/atparsedata";  
	}
?>