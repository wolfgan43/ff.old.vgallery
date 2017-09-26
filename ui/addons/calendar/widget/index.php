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

    $db->query("SELECT module_calendar.*
                            FROM 
                                module_calendar
                            WHERE 
                                module_calendar.name = " . $db->toSql($MD_chk["params"][0]));
    if($db->nextRecord()) {  
        $calendars_name = $db->getField("name")->getValue();

        $calendar_width = $db->getField("width")->getValue();
        $calendar_height = $db->getField("height")->getValue();

        $calendar_private_key = $db->getField("private_key")->getValue();
        
        $calendar_title = $db->getField("title")->getValue();
        $calendar_show_title = $db->getField("show_title")->getValue();
        $calendar_show_navigation = $db->getField("show_navigation")->getValue();
        $calendar_show_date = $db->getField("show_date")->getValue();
        $calendar_show_print = $db->getField("show_print")->getValue();
        $calendar_show_tab = $db->getField("show_tab")->getValue();
        $calendar_show_list_calendar = $db->getField("show_list_calendar")->getValue();
        $calendar_show_timezone = $db->getField("show_timezone")->getValue();
        $calendar_show_border = $db->getField("show_border")->getValue();
        
        $calendar_start_day = $db->getField("start_day")->getValue();
        $calendar_start_mode = $db->getField("start_mode")->getValue();
        $calendar_bgcolor = $db->getField("bgcolor")->getValue();
        $calendar_color = $db->getField("color")->getValue();
        $calendar_calendars = $db->getField("calendars")->getValue();
        $calendar_timezone = $db->getField("timezone")->getValue();
       
        
        $sSQL = "SELECT " . FF_PREFIX . "languages.tiny_code FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.code = " . $db->toSql(LANGUAGE_INSET);
        $db->query($sSQL);
        if($db->nextRecord())
        	$tiny_lang_code = $db->getField("tiny_code", "Text", true);
        
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
      
        $cm->oPage->addContent($tpl->rpparse("main", false), null, "GoogleCalendar");
           
    }
