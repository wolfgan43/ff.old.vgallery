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
    $globals->user_path_shard           = ""; //non usata realmente ffl e page in path
    //$globals->frame_path                = null;
    //$globals->error_path                = null;
    //$globals->services_path             = null;
    //$globals->updater_path              = null;
    $globals->settings_path             = null;
    $globals->selected_lang             = null;
    //$globals->lang_alt                  = null;
    $globals->locale                  	= null;
    $globals->db_gallery                = null; //da togliere
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
    $globals->author                 	= null;
    $globals->meta                      = array();
    $globals->html                      = array();
    $globals->template                  = array();
    $globals->manage                    = array();
    //$globals->MD_chk                    = array();
    $globals->page               		= null;
    $globals->page_title                = null;
    $globals->cover               		= null;
    $globals->http_status              	= null;

    $globals->canonical                 = null;
    $globals->tags                 		= null;
    //$globals->favicon               	= null;
    //$globals->menifest                = null;

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
    $globals->setAuthor = function($id, $params = null) {
        $globals = ffGlobals::getInstance("gallery");

        if(!$params) {
            //todo: da implementare la classe anagraph
            check_function("get_user_data");
            $params 							= user2anagraph($id, "anagraph");

        }
        $globals->author = array(
            "id" 								=> (int) $id
        , "avatar"							=> $params["avatar"]
        , "name" 							=> ($params["role"] ? $params["role"] . " " : "") . $params["name"] . " " . $params["surname"]
        , "src" 							=> ($params["visible"] ? $params["permalink"] : "")
        , "url" 							=> ($params["visible"] ? "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . $params["permalink"] : "")
        , "tags" 							=> explode(",", $params["tags"])
        , "uid" 							=> (int) $params["uid"]
        );
    };
    $globals->setPage = function($user_vars) {
        $globals = ffGlobals::getInstance("gallery");

        $globals->user_vars = array_replace($globals->user_vars, $user_vars);
    };
}

