<?php
	global $ff_global_setting;

	$ff_global_setting["ffPage_html"]["form_workaround"] = true;
	$ff_global_setting["ffPage_html"]["jquery_ui_force_theme"] = "base";
	
	$ff_global_setting["ffRecord"]["widget_discl_enable"] = false;
  	$ff_global_setting["ffGrid"]["widget_discl_enable"] = false;
  	$ff_global_setting["ffGrid_html"]["reset_page_on_search"] = true;
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
    $ff_global_setting["ffGrid"]["switch_row_class"]["first"] = "odd";
    $ff_global_setting["ffGrid"]["switch_row_class"]["second"] = "even";
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

	if(is_file(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/admin." . FF_PHP_EXT))
		require_once(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/admin." . FF_PHP_EXT);

	if(is_file(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/" . str_replace("/", "_", trim($cm->path_info . $cm->real_path_info, "/")) . "." . FF_PHP_EXT))
		require_once(FF_THEME_DISK_PATH . "/" . $cm->oPage->getTheme() . "/conf/" . str_replace("/", "_", trim($cm->path_info . $cm->real_path_info, "/")) . "." . FF_PHP_EXT);
	  
?>