<?php
//    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

	$oRecord = ffRecord::factory($oPage);

    $db_gallery->query("SELECT module_calendar.*
                            FROM 
                                module_calendar
                            WHERE 
                                module_calendar.name = " . $db_gallery->toSql(new ffData($oRecord->user_vars["MD_chk"]["params"][0])));
    if($db_gallery->nextRecord()) {  
        $calendars_name = $db_gallery->getField("name")->getValue();

        $calendar_width = $db_gallery->getField("width")->getValue();
        $calendar_height = $db_gallery->getField("height")->getValue();

        $calendar_private_key = $db_gallery->getField("private_key")->getValue();
        
        $calendar_title = $db_gallery->getField("title")->getValue();
        $calendar_show_title = $db_gallery->getField("show_title")->getValue();
        $calendar_show_navigation = $db_gallery->getField("show_navigation")->getValue();
        $calendar_show_date = $db_gallery->getField("show_date")->getValue();
        $calendar_show_print = $db_gallery->getField("show_print")->getValue();
        $calendar_show_tab = $db_gallery->getField("show_tab")->getValue();
        $calendar_show_list_calendar = $db_gallery->getField("show_list_calendar")->getValue();
        $calendar_show_timezone = $db_gallery->getField("show_timezone")->getValue();
        $calendar_show_border = $db_gallery->getField("show_border")->getValue();
        
        $calendar_start_day = $db_gallery->getField("start_day")->getValue();
        $calendar_start_mode = $db_gallery->getField("start_mode")->getValue();
        $calendar_bgcolor = $db_gallery->getField("bgcolor")->getValue();
        $calendar_color = $db_gallery->getField("color")->getValue();
        $calendar_calendars = $db_gallery->getField("calendars")->getValue();
        $calendar_timezone = $db_gallery->getField("timezone")->getValue();
       
        
        $sSQL = "SELECT " . FF_PREFIX . "languages.tiny_code FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.code = " . $db_gallery->toSql(LANGUAGE_INSET);
        $db_gallery->query($sSQL);
        if($db_gallery->nextRecord())
        	$tiny_lang_code = $db_gallery->getField("tiny_code", "Text", true);
        
        $tpl = ffTemplate::factory(get_template_cascading($user_path, "calendars.html", "/modules/calendar", ffCommon_dirname(__FILE__)));
        $tpl->load_file("calendars.html", "main");

        $tpl->set_var("site_path", FF_SITE_PATH);
        $tpl->set_var("theme_inset", THEME_INSET);
        $tpl->set_var("domain_inset", DOMAIN_INSET);
        $tpl->set_var("language_inset", LANGUAGE_INSET);

        $tpl->set_var("real_name", $calendars_name);
        
        $tpl->set_var("width", $calendar_width);
        $tpl->set_var("height", $calendar_height);

        if(strlen($calendar_private_key)) {
        	$tpl->set_var("pvttk", urlencode($calendar_private_key));
        	$tpl->parse("SezPrivateKey", false);
		} else {
			$tpl->set_var("SezPrivateKey", "");			
		}
        
        $tpl->set_var("start_day", urlencode($calendar_start_day));
        $tpl->set_var("lang", urlencode($tiny_lang_code));
        $tpl->set_var("bgcolor", urlencode($calendar_bgcolor));
        
        if(strlen($calendar_calendars)) {
        	$tpl->set_var("calendar_data", urlencode($calendar_calendars));
        	$tpl->parse("SezCalendars", false);
		} else {
			$tpl->set_var("SezCalendars", "");			
		}
        $tpl->set_var("color", urlencode($calendar_color));
        $tpl->set_var("timezone", urlencode($calendar_timezone));
        
        if($calendar_show_border) {
        	$tpl->parse("SezBorder", false);
        	$tpl->set_var("SezNoBorder", "");
		} else {
        	$tpl->set_var("SezBorder", "");
        	$tpl->parse("SezNoBorder", false);
		}

        if(strlen($calendar_title)) {
        	$tpl->set_var("title", urlencode($calendar_title));
        	$tpl->parse("SezTitle", false);
		} else {
			$tpl->set_var("SezTitle", "");
		}

        if(!$calendar_show_title) {
        	$tpl->parse("SezShowTitle", false);
		} else {
			$tpl->set_var("SezShowTitle", "");
		}
      
        if(!$calendar_show_navigation) {
        	$tpl->parse("SezShowNav", false);
		} else {
			$tpl->set_var("SezShowNav", "");
		}
      
        if(!$calendar_show_date) {
        	$tpl->parse("SezShowDate", false);
		} else {
			$tpl->set_var("SezShowDate", "");
		}
      
        if(!$calendar_show_print) {
        	$tpl->parse("SezShowPrint", false);
		} else {
			$tpl->set_var("SezShowPrint", "");
		}
      
        if(!$calendar_show_tab) {
        	$tpl->parse("SezShowTabs", false);
		} else {
			$tpl->set_var("SezShowTabs", "");
		}
      
        if(!$calendar_show_list_calendar) {
        	$tpl->parse("SezShowCalendars", false);
		} else {
			$tpl->set_var("SezShowCalendars", "");
		}
      
        if(!$calendar_show_timezone) {
        	$tpl->parse("SezShowTz", false);
		} else {
			$tpl->set_var("SezShowTz", "");
		}    
      
        $oPage->addContent($tpl->rpparse("main", false), null, "GoogleCalendar");
           
    }
?>
