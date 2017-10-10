<?php
 if(!defined("GALLERY_INSTALLATION_PHASE") && !defined("SHOWFILES_IS_RUNNING")) {
 	 ffDB_Sql::addEvent("on_factory_done", "ffDB_Sql_on_factory_done");	 

     cm::getInstance()->addEvent("mod_security_on_check_session", "request_info", ffEvent::PRIORITY_DEFAULT);

	/* if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/routing_table.xml")) {
		 if(check_function("system_gallery_redirect"))
		    cm::getInstance()->addEvent("on_before_init", "system_gallery_redirect", ffEvent::PRIORITY_HIGH);
	 }*/
	    
     if(check_function("system_init"))     
        cm::getInstance()->addEvent("on_after_init", "system_init", ffEvent::PRIORITY_HIGH);

    if(is_file(FF_DISK_PATH . "/themes/site/common.php"))
        require(FF_DISK_PATH . "/themes/site/common.php");
	 
  	$globals = ffGlobals::getInstance("gallery");
  	//$globals->is_primary_page           = false;
  	//$globals->is_restricted_page        = false;
	$globals->user_path                 = null;
	$globals->user_path_params          = "";
	$globals->request          			= null;
	$globals->user_path_shard           = "";
    //$globals->frame_path                = null;
    //$globals->error_path                = null;
    //$globals->services_path             = null;
    //$globals->updater_path              = null;
	$globals->settings_path             = null;
	$globals->selected_lang             = null;
	//$globals->lang_alt                  = null;
	$globals->locale                  	= null;
	$globals->db_gallery                = null;
	$globals->ecommerce                 = array();
	$globals->permissions               = array();
	$globals->custom_data               = array();
	$globals->ID_domain                 = null;
	$globals->frame_smart_url           = null;
	$globals->sid                       = null;
	$globals->params                    = array();
	$globals->cache                     = array(
											"enabled" => null
											, "file" => null
											, "section_blocks" => array()
											, "layout_blocks" => array()
											, "data_blocks" => array()
											, "ff_blocks" => array()
										);
	//$globals->cache_file                = null;
	$globals->strip_user_path           = null;
	$globals->media_exception           = array();
	$globals->js                        = array();
	$globals->css                       = array();
	$globals->meta                      = array();
	$globals->html                      = array();  
	$globals->template                  = array();
	$globals->manage                    = array();
	$globals->MD_chk                    = array();
	$globals->page               		= null;
	$globals->page_title                = null;
	$globals->canonical                 = null;
	$globals->favicon               	= null;
    $globals->menifest                  = null;  
	$globals->user						= array(
											"menu" => null
											, "pages" => null
										);
	$globals->settings                  = array();
	$globals->fixed_pre                 = array(
                                            "body" => null
                                            , "content" => null
                                        );
	$globals->fixed_post                = array(
                                            "body" => null
                                            , "content" => null
                                        );
	$globals->seo                       = array();
	$globals->search                    = null;
    $globals->navigation                = null;
    $globals->sort                      = null;
    $globals->filter                    = null;
	$globals->services                  = null;    //facebook api mailchimp api ecc
	$globals->data_storage				= array();

    $globals->setSeo = function($params = array(), $priority = "user") {
         $globals = ffGlobals::getInstance("gallery");
         if(is_array($params) && count($params)) {
             if(is_array($globals->seo[$priority]))
                 $globals->seo[$priority] = array_replace($globals->seo[$priority], $params);
             else
                 $globals->seo[$priority] = $params;
         }
         $globals->seo["current"] = $priority;
    };
}
