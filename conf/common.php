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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if(!defined("GALLERY_INSTALLATION_PHASE") && !defined("SHOWFILES_IS_RUNNING")) {
    if(check_function("system_init"))
        cm::getInstance()->addEvent("on_after_init", "system_init", ffEvent::PRIORITY_HIGH);

    if(is_file(FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/common." . FF_PHP_EXT))
        require(FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/common." . FF_PHP_EXT);

    $globals = ffGlobals::getInstance("gallery");
    //$globals->is_primary_page           = false;
    //$globals->is_restricted_page        = false;
    $globals->user_path                 = null;
    $globals->request          			= null;
    $globals->user_path_params          = "";
    $globals->user_path_shard           = "";
    //$globals->frame_path                = null;
    //$globals->error_path                = null;
    //$globals->services_path             = null;
    //$globals->updater_path              = null;
    $globals->settings_path             = null;
    $globals->selected_lang             = null;
    //$globals->lang_alt                  = null;
    $globals->locale                  	= null;
    $globals->ecommerce                 = array();
    //$globals->permissions               = array();
    $globals->custom_data               = array();
    $globals->ID_domain                 = null;
    //$globals->frame_smart_url           = null;
    //$globals->sid                       = null;
    //$globals->params                    = array();
   	$globals->cache                     = array(
											"enabled"			=> null
											, "file" 			=> null
											, "layer_blocks" 	=> array()
											, "section_blocks" 	=> array()
											, "layout_blocks" 	=> array()
											, "data_blocks" 	=> array()
											, "ff_blocks" 		=> array()
											, "refresh" 		=> array(
												"nodes" 		=> array()
												, "tags" 		=> array()
												, "cats"		=> array()
												, "places" 		=> array()
		   										, "paths"		=> array()
											)
										);
    //$globals->cache_file                = null;
    $globals->strip_user_path           = null;
    $globals->media_exception           = array();
    $globals->js                        = array();
    $globals->css                       = array();
	$globals->microdata                 = array();
	$globals->author                 	= array();
    $globals->meta                      = array();
    $globals->html                      = array();
    $globals->template                  = array();
    $globals->manage                    = array();
    $globals->page               		= null;
    $globals->page_title                = null;
	$globals->cover               		= null;
	$globals->http_status              	= null;

	$globals->canonical                 = null;
    //$globals->favicon               	= null;
    //$globals->manifest               	= null;

	$globals->links						= null;
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
    $globals->tpl                  		= null;    //array associativo con tutte le info sulla struttura della pagina
    $globals->data_storage              = array();    //array associativo con tutte le info sulla struttura della pagina

	$globals->setMeta = function($name = "", $content = "", $type = "name") { //todo: eliminare l'intermedio e usare direttametne page_meta
		 $globals = ffGlobals::getInstance("gallery");

		 $globals->meta[$name] = array(
			 "content" 							=> $content
		 	, "type" 							=> $type
		 );
	};
	 //seo params divided in type
	$globals->setSeo = function($params = array(), $priority = "user") {
		 $globals = ffGlobals::getInstance("gallery");
		 if(is_array($params) && count($params)) {
			 if(is_array($globals->seo[$priority]))
				 $globals->seo[$priority] 		= array_replace($globals->seo[$priority], $params);
			 else
				 $globals->seo[$priority] 		= $params;
		 }
		 $globals->seo["current"] 				= $priority;
	};
	$globals->setMicrodata = function($params = array()) {
		 $globals = ffGlobals::getInstance("gallery");
		 if(is_array($params) && count($params)) {
			 if(is_array($globals->microdata))
				 $globals->microdata 			= array_replace($globals->microdata, $params);
			 else
				 $globals->microdata 			= $params;
		 }
	};
	$globals->setAuthor = function($name, $url = null, $avatar = null, $tags = null) {
		 $globals = ffGlobals::getInstance("gallery");

		 $globals->author = array(
		 	"name" 								=> $name
			 , "avatar" 						=> $avatar
			 , "url" 							=> $url
			 , "tags" 							=> $tags
		 );
	};

   /* if(1)//todo: da togliere. tolto come esperimento
    {
        global $ff_global_setting;

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
    }*/
}

