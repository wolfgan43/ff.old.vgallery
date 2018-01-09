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
/*function system_set_language($selected_lang, $user_permission = null, $alt_selected_lang = null) {
	if($user_permission === null)
  		$user_permission = cache_get_session();

	$arrLang = $user_permission["lang"];
	$language_inset = $user_permission["lang"]["current"];

	if(is_array($arrLang) && count($arrLang)) {
		if(strlen($selected_lang) && array_key_exists(strtoupper($selected_lang), $arrLang)) {
			$real_selected_lang = $selected_lang;
			$real_selected_lang_ID = $arrLang[strtoupper($selected_lang)]["ID"];
		} elseif(is_array($language_inset) && array_key_exists("code", $language_inset) && strlen($language_inset["code"])) {
			$real_selected_lang = $language_inset["code"];
			$real_selected_lang_ID = $language_inset["ID"];
		} elseif(strlen($alt_selected_lang) && array_key_exists(strtoupper($alt_selected_lang), $arrLang)) {
			$real_selected_lang = $alt_selected_lang;
			$real_selected_lang_ID = $arrLang[strtoupper($alt_selected_lang)]["ID"];
		} else {
			$real_selected_lang = "";
			$real_selected_lang_ID = "";
		}
	} else {
		
	}
		
	if(!strlen($real_selected_lang)) {
		$real_selected_lang = get_session("language_default");
		$real_selected_lang_ID = get_session("ID_lang_default");
	}
	if(!strlen($real_selected_lang)) {
		$real_selected_lang = LANGUAGE_DEFAULT;
		$real_selected_lang_ID = $arrLang[LANGUAGE_DEFAULT]["ID"];
	}

	if(!defined("LANGUAGE_DEFAULT_TINY"))   
	    define("LANGUAGE_DEFAULT_TINY", strtolower(substr(LANGUAGE_DEFAULT, 0, 2)));

	define("LANGUAGE_INSET_TINY", strtolower(substr($real_selected_lang, 0, 2)));
	define("LANGUAGE_INSET", $real_selected_lang);
	define("LANGUAGE_INSET_ID", $real_selected_lang_ID);
	define("FF_LOCALE", $real_selected_lang);

	return $real_selected_lang;
}*/

function system_init($cm) {
	$globals = ffGlobals::getInstance("gallery");

	$path_info                          = stripslash($_SERVER["PATH_INFO"]);
	if($path_info == "/index" || !$path_info)
	    $path_info                      = "/";

	$globals->user_path 	            = $path_info;
	$globals->page 			            = cache_get_page_properties($path_info, true);
	$globals->locale 		            = cache_get_locale($globals->page, DOMAIN_NAME); //pulisce il percorso dalla lingua
    $globals->selected_lang             = FF_LOCALE;

	define ("MOD_SEC_GUEST_USER_ID"     , "2");
	define ("MOD_SEC_GUEST_USER_NAME"   , "guest");
	define ("MOD_SEC_GUEST_GROUP_ID"    , "2");
	define ("MOD_SEC_GUEST_GROUP_NAME"  , "guests");

	define("CM_DONT_RUN_LAYOUT", true);

	$cm_layout_vars = array(
    	"main_theme" 					=> "responsive"
    	, "theme" 						=> ($globals->page["theme"]
    										? $globals->page["theme"]
    										: ($globals->page["restricted"]
												? ADMIN_THEME
												: FRONTEND_THEME
											) 
    									)	
    	, "layer" 						=> ($globals->page["layer"]
    										? $globals->page["layer"]
    										: null
    									)	
    	, "page" 						=> null
    	, "title" 						=> null
    	, "class_body" 					=> null
    	, "sect" 						=> array()
    	, "css" 						=> array()
    	, "js" 							=> array()
    	, "meta" 						=> array()
    	, "cdn" 						=> array()
    	, "ignore_defaults" 			=> false
    	, "ignore_defaults_main" 		=> true
    	, "exclude_ff_js" 				=> null
    	, "exclude_form" 				=> true
    	, "enable_gzip" 				=> true
    	, "compact_js" 					=> 2
    	, "compact_css"					=> 2
	);

	define("FF_THEME_FRAMEWORK_CSS", 	($globals->page["framework_css"] && !$globals->page["restricted"]
		? $globals->page["framework_css"]
		: "bootstrap-fluid"
	));
	define("FF_THEME_FONT_ICON",  		($globals->page["font_icon"]
		? $globals->page["font_icon"]
		: "fontawesome"
	));

	$globals->page["framework_css"] 	= FF_THEME_FRAMEWORK_CSS;
	$globals->page["font_icon"] 		= FF_THEME_FONT_ICON;

	if($globals->page["restricted"]) {
		$cm_layout_vars["exclude_form"] = false;
		$cm_layout_vars["compact_js"] = false;
		$cm_layout_vars["compact_css"] = false;
	}

	cm::getInstance()->layout_vars = $cm_layout_vars;
	
	if($cm_layout_vars["layer"] === null && $cm_layout_vars["theme"] == FRONTEND_THEME) {
		cm::getInstance()->layout_vars["layer"] = THEME_INSET;
	}
	if($cm_layout_vars["layer"] == THEME_INSET && $cm_layout_vars["theme"] != FRONTEND_THEME)
		cm::getInstance()->layout_vars["theme"] = FRONTEND_THEME;


	if(defined("SHOWFILES_IS_RUNNING")
        || $globals->page["group"] == "actex"
        || $globals->page["group"] == "service" // nn lo so
        || $globals->page["group"] == "resource"
        //|| strpos($globals->settings_path, "/services") === 0 
        //|| isset($_REQUEST["XHR_COMPONENT"])
    ) {
    	if($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"]) {
			require_once(FF_DISK_PATH . "/library/gallery/system/trace.php");
    		if(system_trace_isCrawler()) {
    			http_response_code("401");
    			echo '<html>
						<head>
							<title>no resource</title>
							<meta name="robots" content="noindex,nofollow" />
							<meta name="googlebot" content="noindex,nofollow" />
						</head>
					</html>';
    			exit;
    		}    
		}
//        define("FF_LOCALE", $globals->selected_lang);
//       define("LANGUAGE_INSET", $globals->selected_lang);
		//define("CM_DONT_RUN_LAYOUT", true);					//se tolto da sopra e necessario qui
		if(defined("SERVICE_TIME_LIMIT") && SERVICE_TIME_LIMIT > 0)
			set_time_limit(SERVICE_TIME_LIMIT);

		cm::getInstance()->layout_vars["layer"] = "empty";
		cm::getInstance()->layout_vars["page"] = "XHR";
		cm::getInstance()->layout_vars["exclude_ff_js"] = true;

		ffTemplate::addEvent("on_loaded_data", "ffTemplate_applets_on_loaded_file");
		cm::getInstance()->addEvent("on_before_include_applet", "cms_on_before_include_applet");
		cm::getInstance()->addEvent("on_before_process", function($cm) {
			if(!$cm->oPage->output_buffer) {
				$cm->oPage->process();
			} else {
				if (is_array($cm->oPage->output_buffer)) {
					$out_buffer = $cm->oPage->output_buffer;
				} elseif (strlen($cm->oPage->output_buffer)) {
					$out_buffer = array("html" => $cm->oPage->output_buffer);
				}
				echo ffCommon_jsonenc($out_buffer, true);
			}
			exit;
		});

        define("SKIP_CMS", true);
        return false;
    }      	

	if(!(is_array($globals->settings) && count($globals->settings))) {
	    $globals->settings = vg_get_settings();

	    $res = $cm->doEvent("vg_on_get_settings", array($globals->settings));
	    $rc = end($res);
	    if (is_array($rc))
	    {
	        $globals->settings = $rc;
	    }

	    if(is_array($globals->settings) && count($globals->settings)) {
	        $globals->settings["AREA_ANAGRAPH_CUSTOM_TYPE"] = ($globals->settings["AREA_ANAGRAPH_CUSTOM_TYPE"] == "null"
	                                                            ? null
	                                                            : $globals->settings["AREA_ANAGRAPH_CUSTOM_TYPE"]
	                                                        );
	        $globals->settings["AREA_ANAGRAPH_SHOW_USER_GROUP"] = ($globals->settings["AREA_ANAGRAPH_SHOW_USER_GROUP"] == "null"
	                                                                ? null
	                                                                : $globals->settings["AREA_ANAGRAPH_SHOW_USER_GROUP"]
	                                                            );
	        $globals->settings["ENABLE_STD_PERMISSION"] = ($globals->settings["ENABLE_ADV_PERMISSION"]
	                                                    ? true
	                                                    : (isset($globals->settings["ENABLE_STD_PERMISSION"])
	                                                        ? $globals->settings["ENABLE_STD_PERMISSION"]
	                                                        : false
	                                                    )
	                                                );
		    $globals->settings["ENABLE_MULTICART"] = (isset($globals->settings["ENABLE_MULTICART"])
		                                                    ? $globals->settings["ENABLE_MULTICART"]
		                                                    : false
		                                                );
		    $globals->settings["AREA_ECOMMERCE_CURRENCY_DEFAULT"] = (strlen($globals->settings["AREA_ECOMMERCE_CURRENCY_DEFAULT"])
		                                                                ? $globals->settings["AREA_ECOMMERCE_CURRENCY_DEFAULT"]
		                                                                : "&euro;"
		                                                            );
		                            
			$globals->ecommerce["company_data"]["reference"] = $globals->settings["SITE_OWNER_REFERENCE"];
			$globals->ecommerce["company_data"]["address"] = $globals->settings["SITE_OWNER_ADDRESS"];
			$globals->ecommerce["company_data"]["cap"] = $globals->settings["SITE_OWNER_CAP"];
			$globals->ecommerce["company_data"]["town"] = $globals->settings["SITE_OWNER_TOWN"];
			$globals->ecommerce["company_data"]["province"] = $globals->settings["SITE_OWNER_PROVINCE"];
			$globals->ecommerce["company_data"]["state"] = $globals->settings["SITE_OWNER_STATE"];
			$globals->ecommerce["company_data"]["piva"] = $globals->settings["SITE_OWNER_PIVA"];
			$globals->ecommerce["company_data"]["cf"] = $globals->settings["SITE_OWNER_CF"];
			$globals->ecommerce["company_data"]["tel"] = $globals->settings["SITE_OWNER_TEL"];
			$globals->ecommerce["company_data"]["email"] = $globals->settings["SITE_OWNER_EMAIL"];

		    $globals->ecommerce["preview"]["vatTime"] = array(
															"1380589201" => "22"
		                                                    , "1316214000" => "21" 
		                                                    , "0" => "20"
		                                                );
		    $globals->ecommerce["preview"]["use_shipping_price_in_detail"] = (isset($globals->settings["AREA_ECOMMERCE_PREVIEW_USE_SHIPPING_PRICE_IN_DETAIL"])
		                                ? $globals->settings["AREA_ECOMMERCE_PREVIEW_USE_SHIPPING_PRICE_IN_DETAIL"]
		                                : false
		                            );
		    $globals->ecommerce["preview"]["template"] = (isset($globals->settings["AREA_ECOMMERCE_PREVIEW_TEMPLATE"])
		                                ? $globals->settings["AREA_ECOMMERCE_PREVIEW_TEMPLATE"]
		                                : ""
		                            );
		    $globals->ecommerce["preview"]["pdf"]["font"] = (isset($globals->settings["AREA_ECOMMERCE_PREVIEW_PDF_FONT"])
		                                ? $globals->settings["AREA_ECOMMERCE_PREVIEW_PDF_FONT"]
		                                : "Arial"
		                            );
		    $globals->ecommerce["preview"]["pdf"]["margin-top"] = (isset($globals->settings["AREA_ECOMMERCE_PREVIEW_PDF_MARGIN_TOP"])
		                                ? $globals->settings["AREA_ECOMMERCE_PREVIEW_PDF_MARGIN_TOP"]
		                                : "40"
		                            );
		    $globals->ecommerce["preview"]["pdf"]["margin-bottom"] = (isset($globals->settings["AREA_ECOMMERCE_PREVIEW_PDF_MARGIN_BOTTOM"])
		                                ? $globals->settings["AREA_ECOMMERCE_PREVIEW_PDF_MARGIN_BOTTOM"]
		                                : "25"
		                            );
		    $globals->ecommerce["preview"]["show_anagraph_shipping_always"] = (isset($globals->settings["AREA_ECOMMERCE_SHOW_ANAGRAPH_SHIPPING_ALWAYS"])
		                                ? $globals->settings["AREA_ECOMMERCE_SHOW_ANAGRAPH_SHIPPING_ALWAYS"]
		                                : false
		                            );
		    $globals->ecommerce["preview"]["show_discount"] = (isset($globals->settings["AREA_ECOMMERCE_SHOW_DISCOUNT"])
		                                ? $globals->settings["AREA_ECOMMERCE_SHOW_DISCOUNT"]
		                                : false
		                            );
	    }
	}

	$logged = defined("IS_LOGGED"); 
    if($logged)
        $prefix = "LOGOUT";  
    else
        $prefix = "LOGIN";

    if($globals->page["restricted"])
        $area = "BACKOFFICE";
    else
    	$area = "FRONTEND";

    $globals->settings["JQUERY_UI_THEME"] = "base";
	if($globals->page["group"] == "login" || $globals->page["api"] == "login") {
		define("MOD_SEC_LOGIN_REGISTER_URL", $globals->settings["MOD_SEC_LOGIN_REGISTER_URL"]);
	     
	    if($globals->settings["MOD_SEC_OAUTH2_SERVER"]) {
	        define("MOD_SEC_OAUTH2_SERVER", true);

	        require FF_DISK_PATH . "/library/OAuth2/Autoloader.php";
	        OAuth2\Autoloader::register();
	    }	     

	    if($globals->settings["MOD_SEC_SOCIAL_FF"] && $globals->settings["MOD_SEC_SOCIAL_FF_CLIENT_ID"] && $globals->settings["MOD_SEC_SOCIAL_FF_CLIENT_SECRET"] && $globals->settings["MOD_SEC_SOCIAL_FF_OAUTH2_URL"]) {
	        define("MOD_SEC_SOCIAL_FF", true);
	        define("MOD_SEC_SOCIAL_FF_CLIENT_ID", $globals->settings["MOD_SEC_SOCIAL_FF_CLIENT_ID"]);
	        define("MOD_SEC_SOCIAL_FF_CLIENT_SECRET", $globals->settings["MOD_SEC_SOCIAL_FF_CLIENT_SECRET"]);
	        define("MOD_SEC_SOCIAL_FF_OAUTH2_URL", $globals->settings["MOD_SEC_SOCIAL_FF_OAUTH2_URL"]);
		}
			        
	    if($globals->settings["MOD_SEC_SOCIAL_GOOGLE"] && $globals->settings["MOD_SEC_SOCIAL_GOOGLE_APP_NAME"] && $globals->settings["MOD_SEC_SOCIAL_GOOGLE_CLIENT_ID"] && $globals->settings["MOD_SEC_SOCIAL_GOOGLE_CLIENT_SECRET"]) {
	        define("MOD_SEC_SOCIAL_GOOGLE", true);
	        define("MOD_SEC_SOCIAL_GOOGLE_APP_NAME", $globals->settings["MOD_SEC_SOCIAL_GOOGLE_APP_NAME"]);
	        define("MOD_SEC_SOCIAL_GOOGLE_CLIENT_ID", $globals->settings["MOD_SEC_SOCIAL_GOOGLE_CLIENT_ID"]);
	        define("MOD_SEC_SOCIAL_GOOGLE_CLIENT_SECRET", $globals->settings["MOD_SEC_SOCIAL_GOOGLE_CLIENT_SECRET"]);
	        define("MOD_SEC_SOCIAL_GOOGLE_CLIENT_REDIR_URI", "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . "/login/social/google/response");

	        define("LIB_GOOGLE", true);
	        set_include_path(get_include_path() . PATH_SEPARATOR . FF_DISK_PATH . "/library/google-api-php-client");
	        require "Google/Client.php";

	        if(!defined("MOD_SEC_OAUTH2_SERVER")) {
		        require FF_DISK_PATH . "/library/OAuth2/Autoloader.php";
		        OAuth2\Autoloader::register();
			}
	    }

	    if($globals->settings["MOD_SEC_SOCIAL_FACEBOOK"] && $globals->settings["MOD_SEC_SOCIAL_FACEBOOK_APPID"] && $globals->settings["MOD_SEC_SOCIAL_FACEBOOK_SECRET"]) {
	        define("MOD_SEC_SOCIAL_FACEBOOK", true);
	        define("MOD_SEC_SOCIAL_FACEBOOK_APPID", $globals->settings["MOD_SEC_SOCIAL_FACEBOOK_APPID"]);
	        define("MOD_SEC_SOCIAL_FACEBOOK_SECRET", $globals->settings["MOD_SEC_SOCIAL_FACEBOOK_SECRET"]);
	        define("MOD_SEC_SOCIAL_FACEBOOK_CLIENT_REDIR_URI", "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . "/login/social/facebook/response");
	        
	        define("LIB_FACEBOOK", true);
	        define('FACEBOOK_SDK_V4_SRC_DIR', FF_DISK_PATH . '/library/facebook-php-sdk/src/Facebook/');
	        require_once FF_DISK_PATH . '/library/facebook-php-sdk/autoload.' . FF_PHP_EXT;    
	    } else {
	        define("MOD_SEC_SOCIAL_FACEBOOK_APP", false);
	    }
	    
	    if($globals->settings["MOD_SEC_SOCIAL_JANRAIN"] && $globals->settings["MOD_SEC_SOCIAL_JANRAIN_APPID"] && $globals->settings["MOD_SEC_SOCIAL_JANRAIN_APPNAME"]) {
	        define("MOD_SEC_SOCIAL_JANRAIN", true);
	        define("MOD_SEC_SOCIAL_JANRAIN_APPID", $globals->settings["MOD_SEC_SOCIAL_JANRAIN_APPID"]);
	        define("MOD_SEC_SOCIAL_JANRAIN_APPNAME", $globals->settings["MOD_SEC_SOCIAL_JANRAIN_APPNAME"]);
	        define("MOD_SEC_SOCIAL_JANRAIN_CLIENT_REDIR_URI", "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . "/login/social/janrain/response");
	    } 
		
		if($cm_layout_vars["layer"] !== null)
			define("MOD_SEC_LOGIN_FORCE_LAYER", false);
		elseif(array_key_exists("MOD_SEC_FORCE_LAYER_" . $area, $globals->settings))
    		define("MOD_SEC_LOGIN_FORCE_LAYER", $globals->settings["MOD_SEC_FORCE_LAYER_" . $area]);

		if($globals->settings["MOD_SEC_" . $prefix . "_LOGO_" . $area]) {
		    $arrLogoBox = explode("-", $globals->settings["MOD_SEC_" . $prefix . "_LOGO_" . $area]);

		    define("MOD_SEC_LOGO", $arrLogoBox[0]);
		    if($arrLogoBox[1] == "Empty")
			    define("MOD_SEC_LOGO_PATH", false);
		} else {
			define("MOD_SEC_LOGO", false);
		}

		if(!defined("MOD_SEC_LOGO_PATH")) {
		    if($globals->settings["MOD_SEC_LOGO_PATH_" . $area] && is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/" . $globals->settings["MOD_SEC_LOGO_PATH_" . $area]))
		        define("MOD_SEC_LOGO_PATH", FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/" . $globals->settings["MOD_SEC_LOGO_PATH_" . $area]);
		    elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/logo-login.svg"))
		        define("MOD_SEC_LOGO_PATH", FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-login.svg");
		    elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/logo-login.png"))
		        define("MOD_SEC_LOGO_PATH", FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-login.png");
		    elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/logo-login.gif"))
		        define("MOD_SEC_LOGO_PATH", FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-login.gif");
		    elseif(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/logo.svg"))
		        define("MOD_SEC_LOGO_PATH", FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo.svg");
		    elseif(is_file(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/images/logo-login.png"))
		        define("MOD_SEC_LOGO_PATH", FF_THEME_DIR . "/" . THEME_INSET . "/images/logo-login.png");        
		    elseif(is_file(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/images/logo.svg"))
		        define("MOD_SEC_LOGO_PATH", FF_THEME_DIR . "/" . THEME_INSET . "/images/logo.svg");        
		}
	    
		//condivide la sessione per ai sottodomini
		cm::getInstance()->addEvent("mod_security_on_create_session", "cache_session_share_for_subdomains", ffEvent::PRIORITY_DEFAULT);	    
		cm::getInstance()->addEvent("mod_security_on_destroy_session", "cache_session_share_for_subdomains", ffEvent::PRIORITY_DEFAULT);		
		
	    if(check_function("check_user_request"))
	         cm::getInstance()->addEvent("mod_security_on_created_session", "check_user_request", ffEvent::PRIORITY_DEFAULT);

/*
	    //check required fields on form
	    if(check_function("check_user_request"))
	         cm::getInstance()->addEvent("mod_security_on_created_session", "check_user_form_request", ffEvent::PRIORITY_DEFAULT);
	    //check required fields on vgallery
	    if(check_function("check_user_request"))
	         cm::getInstance()->addEvent("mod_security_on_created_session", "check_user_vgallery_request", ffEvent::PRIORITY_DEFAULT);
	    //merge ecommerce cart (guest + user)
	    if(check_function("ecommerce_cart_merge"))
	         cm::getInstance()->addEvent("mod_security_on_created_session", "ecommerce_cart_merge", ffEvent::PRIORITY_DEFAULT);
*/	    
	    
	    if(/*!defined("DISABLE_CACHE") &&*/ check_function("system_set_cache_page")) {
	         cm::getInstance()->addEvent("mod_security_on_created_session", "system_write_cache_token_session", ffEvent::PRIORITY_DEFAULT);
	         cm::getInstance()->addEvent("mod_security_on_destroyed_session", "system_destroy_cache_token_session", ffEvent::PRIORITY_DEFAULT);
		}
	    
	}

    foreach($globals->settings AS $setting_key => $setting_value) {
    	if(strpos($setting_key, "MOD_SEC") === 0)
    		continue;

        if(!defined($setting_key))    define($setting_key, $setting_value);
    }

    if($globals->page["group"] == "shard"
    	|| $globals->page["group"] == "frame"
    ) {
    	define("CM_DONT_RUN_LAYOUT", true);
    }
    
	if(/*$globals->page["group"] == "service"
        ||*/ ($globals->page["group"] == "login" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
    ) {
//        define("FF_LOCALE", $globals->selected_lang);
//       define("LANGUAGE_INSET", $globals->selected_lang);
		//define("CM_DONT_RUN_LAYOUT", true);
        define("SKIP_CMS", true);
        return false;
    }

	cm::getInstance()->addEvent("on_before_cm", "system_init_on_before_cm", ffEvent::PRIORITY_HIGH);
}

function vg_get_settings_default()
{
	$default_settings = array();
	$file = FF_DISK_PATH . "/conf/modules/restricted/mod_restricted.xml";
	if(is_file($file)) {
		$xml = new SimpleXMLElement("file://" . $file, null, true);

		if (isset($xml->settings) && count($xml->settings->children()))
		{
			foreach ($xml->settings->children() as $key => $value)
			{
				foreach ($value->children() as $subkey => $subvalue)
				{
					$attrs = $subvalue->attributes();
					$default_settings[$subkey] = (string)$attrs["default"];
				}
			}
		}
	}

	return $default_settings;
}

function vg_get_settings($res = null, $DomainID = null, $db = null)
{
	if($res === null)
		$res = vg_get_settings_default();
    if ($db === null)
        $db = ffDb_Sql::factory();

    if (!is_object($db))
        ffErrorHandler::raise("invalid db object", E_USER_ERROR, null, get_defined_vars());

    $sSQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_restricted_settings WHERE 1 ";

    if ($DomainID === null)
    {
        if (is_callable("mod_security_get_domain") && MOD_SEC_MULTIDOMAIN)
        {
            $DomainID = mod_security_get_domain();
        }
    }    
    
    if ($DomainID !== null)
    {
        $sSQL .= " AND ID_domains = " . $db->toSql($DomainID);
    }

    $db->query($sSQL);
    if ($db->nextRecord())
    {
        do {
            $res[$db->getField("name", "Text", true)] = $db->getField("value", "Text", true);
        } while($db->nextRecord());
    }

    return $res;
}

function system_get_settings_path_by_user_path($page) {
	$globals = ffGlobals::getInstance("gallery");

	check_function("normalize_url");

	if($globals->page["primary"] && !$globals->page["restricted"]) {
		$db = ffDB_Sql::factory();

		require(FF_DISK_PATH . "/library/gallery/struct." . FF_PHP_EXT);
        /** @var include $def */
        $def_module = system_get_schema_module("fields", $def);
		if($page["user_path"] == "/")
		{ 
			$seo_priority = array(
				"page" 				=> array(
											"schema" 				=> "page"
											, "primary_table" 		=> $def["page"]["seo"]["primary_table"]
											, "table"				=> $def["page"]["seo"]["table"]
											, "tags"				=> "primary"
											, "where" => array(
											)
										)
									);
		} 
		else
		{
			$arrUserPath = explode("/", trim($page["user_path"], "/"));

			if(is_array($def_module) && count($def_module)) {
				foreach($def_module AS $module_key => $module_data)	 
				{
					if(strpos($page["user_path"], $module_key) === 0)		
					{
						$seo_priority[$module_key] = array(
							"schema" 				=> $module_key
							, "primary_table" 		=> $module_data["seo"]["primary_table"]
							, "table"				=> $module_data["seo"]["table"]
							, "tags"				=> "primary"
							, "where" => array(
							)
						);
					}
				}
				
				$def = $def + $def_module;
			}
			

			$seo_priority["detail"] 			= array(
													"schema" 																				=> "vgallery"
													, "primary_table" 																		=> $def["vgallery"]["seo"]["primary_table"]
													, "table"																				=> $def["vgallery"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "mode"																				=> "detail"
													, "compare" => array(
														"is_dir" 																			=> "0"
													)
													, "where" => array(
													)
													, "join" => array(
														"type" 																				=> "INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery"
													)
													, "select" 	=> array(
														"vgallery_name" 																	=> "vgallery.name 								AS `vgallery_name`"
														, "limit_level" 																	=> "vgallery.limit_level 						AS `limit_level`"
														, "limit_type" 																		=> "vgallery.limit_type 						AS `limit_type`"
														, "enable_ecommerce" 																=> "vgallery.name 								AS `vgallery_name`"
														, "use_pricelist_as_item_thumb" 													=> "vgallery.use_pricelist_as_item_thumb 		AS `use_pricelist_as_item_thumb`"
														, "use_pricelist_as_item_detail" 													=> "vgallery.use_pricelist_as_item_detail 		AS `use_pricelist_as_item_detail`"
														, "enable_multilang_visible" 														=> "vgallery.enable_multilang_visible 			AS `enable_multilang_visible`"
														, "enable_multi_cat" 																=> "vgallery.enable_multi_cat 					AS `enable_multi_cat`"
														, "drag_sort_node_enabled" 															=> "vgallery.drag_sort_node_enabled 			AS `drag_sort_node_enabled`"
														
														
														, "is_wishlisted" 																	=> (!AREA_SHOW_ECOMMERCE && USE_CART_PUBLIC_MONO 
																																				? "( SELECT ecommerce_order_detail.ID
																																			        FROM ecommerce_order_detail
			        																																	INNER JOIN ecommerce_order ON ecommerce_order.ID = ecommerce_order_detail.ID_order
																																			        WHERE ecommerce_order_detail.ID_items = vgallery_nodes.ID
			        																																	AND ecommerce_order_detail.tbl_src = 'vgallery_nodes'
			        																																	AND ecommerce_order.ID_user_cart = " . $db->toSql(get_session("UserNID"), "Number") . "
																																						AND ecommerce_order.cart_name = " . $db->toSql(ffCommon_url_rewrite(get_session("UserID"))) . " AND ecommerce_order.wishlist_archived = 0
																																						AND ecommerce_order.is_cart > 0 )" 
																																				: "''"
																																			) . "											AS `is_wishlisted`"
														, "available" 																		=> (AREA_SHOW_ECOMMERCE && AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK 
																																				? "IF(vgallery.enable_ecommerce > 0
                    																																	, IF(vgallery.use_pricelist_as_item_detail > 0
                    																																		, IFNULL( 
                    																																			, (SELECT ecommerce_pricelist.actual_qta
                    																																				FROM ecommerce_settings
																																										INNER JOIN ecommerce_pricelist ON ecommerce_settings.ID = ecommerce_pricelist.ID_ecommerce_settings
                    																																				WHERE ecommerce_settings.ID_items = vgallery_nodes.ID	
                    																																			)
                    																																			, 1
                    																																		)
                    																																		, IFNULL( 
                    																																			(SELECT ecommerce_settings.actual_qta
                    																																				FROM ecommerce_settings
                    																																				WHERE ecommerce_settings.ID_items = vgallery_nodes.ID	
                    																																			)
                    																																			, 1
                    																																		)
                    																																	)
                    																																	, 1
																																	                )" 
																																	            : "1"
																																	        ) . "											AS `available`"
													)
												);
			$seo_priority["detail-anagraph"] 	= array(
													"schema" 																				=> "anagraph"
													, "primary_table" 																		=> $def["anagraph"]["seo"]["primary_table"]
													, "table"																				=> $def["anagraph"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "mode"																				=> "detail"
													, "where" => array(
													)
													, "join" => array(
														"type" 																				=> "INNER JOIN anagraph_type ON anagraph_type.ID = anagraph.ID_type"
													)
													, "select" => array(
														"type" 																				=> "anagraph_type.name AS `type`"
													)
												);
			$seo_priority["thumb"] 				= array(
													"schema" 																				=> "vgallery"
													, "primary_table" 																		=> $def["vgallery"]["seo"]["primary_table"]
													, "table"																				=> $def["vgallery"]["seo"]["table"]
													, "tags"																				=> "secondary"
													, "mode"																				=> "thumb"
													, "compare" => array(
														"is_dir" 																			=> "1"
													)
													, "where" => array(
													)
													, "join" => array(
														"type" 																				=> "INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery"
													)
													, "select" => array(
														"vgallery_name" 																	=> "vgallery.name 								AS `vgallery_name`"
														, "limit_level" 																	=> "vgallery.limit_level 						AS `limit_level`"
														, "limit_type" 																		=> "vgallery.limit_type 						AS `limit_type`"
														, "enable_ecommerce" 																=> "vgallery.name 								AS `vgallery_name`"
														, "use_pricelist_as_item_thumb" 													=> "vgallery.use_pricelist_as_item_thumb 		AS `use_pricelist_as_item_thumb`"
														, "use_pricelist_as_item_detail" 													=> "vgallery.use_pricelist_as_item_detail 		AS `use_pricelist_as_item_detail`"
														, "enable_multilang_visible" 														=> "vgallery.enable_multilang_visible 			AS `enable_multilang_visible`"
														, "enable_multi_cat" 																=> "vgallery.enable_multi_cat 					AS `enable_multi_cat`"
														, "drag_sort_node_enabled" 															=> "vgallery.drag_sort_node_enabled 			AS `drag_sort_node_enabled`"
														, "is_wishlisted"																	=> "''											AS `is_wishlisted`" 
														, "available"																		=> "''											AS `available`" 
													)
												);
			$seo_priority["thumb-anagraph"] 	= array(
													"schema" 																				=> "anagraph"
													, "primary_table" 																		=> $def["anagraph"]["seo"]["primary_table"]
													, "table"																				=> $def["anagraph"]["seo"]["table"]
													, "tags"																				=> "secondary"
													, "mode"																				=> "thumb"
													, "where" => array(
													)
													, "join" => array(
														"type" 																				=> "INNER JOIN anagraph_type ON anagraph_type.ID = anagraph.ID_type"
													)
													, "select" => array(
														"type"																				=> "anagraph_type.name 							AS `type`"
													)
												);
			$seo_priority["page"] 				= array(
													"schema" 																				=> "page"
													, "primary_table" 																		=> $def["page"]["seo"]["primary_table"]
													, "table"																				=> $def["page"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "where" => array(
													)
												);
			$seo_priority["media"] 				= array(
													"schema" 																				=> "files"
													, "primary_table" 																		=> $def["files"]["seo"]["primary_table"]
													, "table"																				=> $def["files"]["seo"]["table"]
													, "tags"																				=> "secondary"
													, "mode"																				=> "thumb"
													, "where" => array(
													)
												);
			$seo_priority["tag"] 				= array(
													"schema" 																				=> "tag"
													, "primary_table" 																		=> $def["tag"]["seo"]["primary_table"]
													, "table"																				=> $def["tag"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "mode"																				=> "detail"
													, "where" => array(
														"primary" 																			=> ($def["tag"]["seo"]["primary_table"] != $def["tag"]["seo"]["table"]
																																				? "`" . $def["tag"]["seo"]["table"] . "`.`" . $def["tag"]["seo"]["permalink"] . "`"
																																				: "`" . $def["tag"]["seo"]["primary_table"] . "`.`" . $def["tag"]["seo"]["primary_permalink"] . "`"
																																			) . " = " . $db->toSql("/" . $arrUserPath[0])
													)
													, "join" => array(
														"tag" 																				=> "LEFT JOIN search_tags ON search_tags.ID_tag_page = search_tags_page.ID"
													)
													, "select" => array(
																																			"tag" => "search_tags.ID 							AS ID_primary_tag"
													)
												);												
			$seo_priority["city"] 				= array(
													"schema" 																				=> "city"
													, "primary_table" 																		=> $def["city"]["seo"]["primary_table"]
													, "table"																				=> $def["city"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "mode"																				=> "detail"
													, "where" => array(
														"primary" 																			=> ($def["city"]["seo"]["primary_table"] != $def["city"]["seo"]["table"]
																																				? "`" . $def["city"]["seo"]["table"] . "`.`" . $def["city"]["seo"]["permalink"] . "`"
																																				: "`" . $def["city"]["seo"]["primary_table"] . "`.`" . $def["city"]["seo"]["primary_permalink"] . "`"
																																			) . " = " . $db->toSql($page["db_path"])
													)
												);	
			$seo_priority["province"] 			= array(
													"schema" 																				=> "province"
													, "primary_table" 																		=> $def["province"]["seo"]["primary_table"]
													, "table"																				=> $def["province"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "mode"																				=> "detail"
													, "where" => array(
														"primary" 																			=> ($def["province"]["seo"]["primary_table"] != $def["province"]["seo"]["table"]
																																				? "`" . $def["province"]["seo"]["table"] . "`.`" . $def["province"]["seo"]["permalink"] . "`"
																																				: "`" . $def["province"]["seo"]["primary_table"] . "`.`" . $def["province"]["seo"]["primary_permalink"] . "`"
																																			) . " = " . $db->toSql($page["db_path"])
													)
												);	
			$seo_priority["region"] 			= array(
													"schema" 																				=> "region"
													, "primary_table" 																		=> $def["region"]["seo"]["primary_table"]
													, "table"																				=> $def["region"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "mode"																				=> "detail"
													, "where" => array(
														"primary" 																			=> ($def["region"]["seo"]["primary_table"] != $def["region"]["seo"]["table"]
																																				? "`" . $def["region"]["seo"]["table"] . "`.`" . $def["region"]["seo"]["permalink"] . "`"
																																				: "`" . $def["region"]["seo"]["primary_table"] . "`.`" . $def["region"]["seo"]["primary_permalink"] . "`"
																																			) . " = " . $db->toSql($page["db_path"])
													)
												);	
			$seo_priority["state"] 			= array(
													"schema" 																				=> "state"
													, "primary_table" 																		=> $def["state"]["seo"]["primary_table"]
													, "table"																				=> $def["state"]["seo"]["table"]
													, "tags"																				=> "primary"
													, "mode"																				=> "detail"
													, "where" => array(
														"primary" 																			=> ($def["state"]["seo"]["primary_table"] != $def["state"]["seo"]["table"]
																																				? "`" . $def["state"]["seo"]["table"] . "`.`" . $def["state"]["seo"]["permalink"] . "`"
																																				: "`" . $def["state"]["seo"]["primary_table"] . "`.`" . $def["state"]["seo"]["primary_permalink"] . "`"
																																			) . " = " . $db->toSql($page["db_path"])
													)
												);													
		}

		$sql_buffer = array();
		foreach($seo_priority AS $priority => $data)
		{	
			if(!array_key_exists($data["primary_table"], $sql_buffer)) 
			{		
				$sql_buffer[$data["primary_table"]] = array(); 	

				$table_target = $data["primary_table"];
				$permalink = $def[$data["schema"]]["seo"]["primary_permalink"];
				if(!$permalink)
					$permalink = $def[$data["schema"]]["seo"]["permalink"];

				$data["select"]["lang"] = LANGUAGE_DEFAULT_ID . " AS `ID_lang`";
				if(count($globals->locale) > 2 && $def[$data["schema"]]["field"]["lang"] &&  $data["table"] && $data["primary_table"] != $data["table"]) 
				{
					if($globals->selected_lang != LANGUAGE_DEFAULT) {
						$table_target = $data["table"];
						$permalink = $def[$data["schema"]]["seo"]["permalink"];				
					
						/**
						*  Lang Field
						*/
						$data["select"]["lang"] = "`" . $data["table"] . "`.`" . $def[$data["schema"]]["field"]["lang"] . "` AS `ID_lang`";
						if($def[$data["schema"]]["seo"]["permalink"]) {
							$data["select"]["permalink"] = (strpos($def[$data["schema"]]["seo"]["permalink"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["permalink"] . "` AS `" . $def[$data["schema"]]["field"]["permalink"] . "`"
								: $def[$data["schema"]]["seo"]["permalink"]
							);
						}
						if($def[$data["schema"]]["seo"]["keywords"]) {
							$data["select"]["keywords"] = (strpos($def[$data["schema"]]["seo"]["keywords"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["keywords"] . "` AS `" . $def[$data["schema"]]["field"]["keywords"] . "`"
								: $def[$data["schema"]]["seo"]["keywords"]
							);
						}
						if($def[$data["schema"]]["seo"]["description"]) {
							$data["select"]["description"] = (strpos($def[$data["schema"]]["seo"]["description"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["description"] . "` AS `" . $def[$data["schema"]]["field"]["description"] . "`"
								: $def[$data["schema"]]["seo"]["description"]
							);
						}
						if($def[$data["schema"]]["seo"]["title"]) {
							$data["select"]["title"] = (strpos($def[$data["schema"]]["seo"]["title"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["title"] . "` AS `" . $def[$data["schema"]]["field"]["title"] . "`"
								: $def[$data["schema"]]["seo"]["title"]
							);
						}
						if($def[$data["schema"]]["seo"]["header"]) {
							$data["select"]["header"] = (strpos($def[$data["schema"]]["seo"]["header"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["header"] . "` AS `" . $def[$data["schema"]]["field"]["header"] . "`"
								: $def[$data["schema"]]["seo"]["header"]
							);
						}
						if($def[$data["schema"]]["seo"]["permalink_parent"]) {
							$data["select"]["permalink_parent"] = (strpos($def[$data["schema"]]["seo"]["permalink_parent"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["permalink_parent"] . "` AS `" . $def[$data["schema"]]["field"]["permalink_parent"] . "`"
								: $def[$data["schema"]]["seo"]["permalink_parent"]
							);
						}
						if($def[$data["schema"]]["seo"]["smart_url"]) {
							$data["select"]["smart_url"] = (strpos($def[$data["schema"]]["seo"]["smart_url"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["smart_url"] . "` AS `" . $def[$data["schema"]]["field"]["smart_url"] . "`"
								: $def[$data["schema"]]["seo"]["smart_url"]
							);
						}
						if($def[$data["schema"]]["seo"]["visible"] && !ENABLE_STD_PERMISSION  && ENABLE_ADV_PERMISSION) {
							$data["select"]["visible"] = (strpos($def[$data["schema"]]["seo"]["visible"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["visible"] . "` AS `" . $def[$data["schema"]]["field"]["visible"] . "`"
								: $def[$data["schema"]]["seo"]["visible"]
							);
						}
						
						$data["select"]["alt_lang"] = LANGUAGE_DEFAULT_ID . " AS `alt_lang`";
						if($def[$data["schema"]]["field"]["permalink"]) {
							$data["select"]["alt_permalink"] = (strpos($def[$data["schema"]]["field"]["permalink"], " AS ") === false
								? "`" . $data["primary_table"] . "`.`" . $def[$data["schema"]]["field"]["permalink"] . "` AS `alt_permalink`"
								: $def[$data["schema"]]["field"]["permalink"]
							);
						}
					} else {
						$data["select"]["alt_lang"] = "`" . $data["table"] . "`.`" . $def[$data["schema"]]["field"]["lang"] . "` AS `alt_lang`";
						if($def[$data["schema"]]["seo"]["permalink"]) {
							$data["select"]["alt_permalink"] = (strpos($def[$data["schema"]]["seo"]["permalink"], " AS ") === false
								? "`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["permalink"] . "` AS `alt_permalink`"
								: $def[$data["schema"]]["seo"]["permalink"]
							);
						}						
					}
					$data["join"][] = "INNER JOIN `" . $data["table"] . "` ON 
											`" . $data["table"] . "`.`" . $def[$data["schema"]]["seo"]["rel_key"]  . "` = `" . $data["primary_table"] . "`.`" . ($def[$data["schema"]]["key"] ? $def[$data["schema"]]["key"] : "ID") . "`
											AND `" . $data["table"] . "`.`" . $def[$data["schema"]]["field"]["lang"]  . "` != " . LANGUAGE_DEFAULT_ID;
				}
				
				if(!$data["where"]["primary"]) {
					if(strpos($permalink, " AS ") === false)
						$permalink = "`" . $table_target . "`.`" . $permalink . "`";
					else
						$permalink = substr($permalink, 0, strpos($permalink, " AS "));

				
					$data["where"]["primary"] = $permalink . " = " . $db->toSql($page["db_path"]);
				}

				$sSQL = "SELECT 
							`" . $data["primary_table"] . "`.*
								" . (is_array($data["select"]) && count($data["select"])
									? ", " . implode(", ", $data["select"])
									: ""
								)
							. "	FROM `" . $data["primary_table"] . "`"
							. (is_array($data["join"]) && count($data["join"])
								? implode(" ", $data["join"])
								: ""
							)
				 			. " WHERE " . (is_array($data["where"]) && count($data["where"])
				 				? implode(" AND ", $data["where"])
				 				: ""
				 			);

				$db->query($sSQL);
				if($db->nextRecord()) {
					do { 
						$alt_tiny_code 																		= $globals->locale["lang"][$globals->locale["rev"]["key"][$db->record["alt_lang"]]]["tiny_code"];

						if($db->record["alt_permalink"] && $alt_tiny_code) {
							$altlang[$data["primary_table"]][$alt_tiny_code]								= normalize_url_by_current_lang(
																												$db->record["alt_permalink"]
																												, (LANGUAGE_DEFAULT_ID == $db->record["alt_lang"]  
																													? ""
																													: "/" . $alt_tiny_code
																												)
																												, true
																											); 
						}
						if($globals->locale["lang"][$globals->selected_lang]["ID"] == $db->record["ID_lang"]) {
							$full_path 																		= ($def[$data["schema"]]["field"]
																												? stripslash($db->record[$def[$data["schema"]]["field"]["parent"]]) . "/" . $db->record[$def[$data["schema"]]["field"]["name"]]
																												: stripslash($db->record[$def[$data["schema"]]["seo"]["primary_parent"]]) . "/" . $db->record[$def[$data["schema"]]["seo"]["smart_url"]]
																											);
							$type																			= $data["schema"];

							$globals->data_storage[$type][$full_path]										= $db->record;
						}
					} while($db->nextRecord());
					
					//if($db->numRows() == 1)
						$sql_buffer[$data["primary_table"]] 												= $db->record;
				}
			}

			if($sql_buffer[$data["primary_table"]]) {
				$invalid = false;
				if(is_array($data["compare"]) && count($data["compare"])) {
					foreach($data["compare"] AS $field_key => $field_value) {
						if($sql_buffer[$data["primary_table"]][$field_key] != $field_value) {
							$invalid = true;
							break;
						}
					}
				}

				if(!$invalid) {
					$settings_path 																	= $sql_buffer[$data["primary_table"]]["primary_permalink"];

					$globals->seo["current"] 														= $priority;
					if($altlang[$data["primary_table"]])
						$globals->seo["altlang"]													= $altlang[$data["primary_table"]];

					$globals->seo[$priority]["title"] 												= $sql_buffer[$data["primary_table"]][$def[$data["schema"]]["seo"]["title"]];
				    $globals->seo[$priority]["title_header"] 										= $sql_buffer[$data["primary_table"]][$def[$data["schema"]]["seo"]["header"]];
				    if(!$globals->seo[$priority]["title_header"])
				    	$globals->seo[$priority]["title_header"] 									= $globals->seo[$priority]["title"];

				    if(!$globals->seo[$priority]["title"])
				    	$globals->seo[$priority]["title"] 											= $globals->seo[$priority]["title_header"];
				    
				    if($sql_buffer[$data["primary_table"]][$def[$data["schema"]]["seo"]["description"]])
				    	$globals->seo[$priority]["meta"]["description"][] 							= strip_tags($sql_buffer[$data["primary_table"]][$def[$data["schema"]]["seo"]["description"]]);
				    
				    if($sql_buffer[$data["primary_table"]][$def[$data["schema"]]["seo"]["keywords"]])
				    	$globals->seo[$priority]["meta"]["keywords"][] 								= $sql_buffer[$data["primary_table"]][$def[$data["schema"]]["seo"]["keywords"]];
					
					if($sql_buffer[$data["primary_table"]][$def[$data["schema"]]["field"]["tags"]])
						$globals->seo[$priority]["meta"]["tags"][$data["tags"]] 					= $sql_buffer[$data["primary_table"]][$def[$data["schema"]]["field"]["tags"]];

					$globals->seo[$priority]["ID"] 													= $sql_buffer[$data["primary_table"]]["ID"];
					$globals->seo[$priority]["mode"]												= $data["mode"];

					$globals->meta 																	= $globals->seo[$priority]["meta"];
					
					break;	
				}	
			}		
		}

		if(!$settings_path && count($globals->locale["lang"]) > 2 && check_function("get_international_settings_path")) {
    		$res_settings  = get_international_settings_path($page["user_path"], $globals->selected_lang, false);
    		if($res_settings["url"])
    			$settings_path = $res_settings["url"];
		}
	}	

	return ($settings_path && $settings_path != $page["user_path"]
			? $settings_path
			: $page["user_path"]
		);
}

function system_get_schema_module($return = "schema", $def = null, $type = null) {
  	static $schema_module = null;

  	if(!$schema_module)
  	{
  		if(!$def)
  			require(FF_DISK_PATH . "/library/gallery/struct." . FF_PHP_EXT);

  		$schema_module  = array(
  			"schema" => array()
  			, "fields" => array()
  		);
		$ff_modules = glob(FF_DISK_PATH . "/modules/*");
		if(is_array($ff_modules) && count($ff_modules)) {
			foreach($ff_modules AS $real_module_dir) {
			    if(is_file($real_module_dir . "/conf/schema." . FF_PHP_EXT)) {
					require $real_module_dir . "/conf/schema." . FF_PHP_EXT;

                    /** @var include $schema */
                    $schema_module["schema"][basename($real_module_dir)] = $schema;
					foreach($schema AS $schema_key => $schema_value) {
						if(strpos($schema_key, "/") === 0) {
							if(is_array($schema_value))
								$schema_module["fields"][$schema_key] = array_replace_recursive($def["default"], $schema_value); 
							elseif($schema_value)
								$schema_module["fields"][$schema_key] = $def[$schema_value]; 
						}
					}
				}
			}		
		}  		
  	}

  	if($type)
  		return $schema_module[$return][$type];
  	else
  		return $schema_module[$return];
}

function system_init_on_before_cm($cm) {
    $globals = ffGlobals::getInstance("gallery");
    $globals->db_gallery = ffDB_Sql::factory();

    if(defined("SKIP_CMS"))
        return false;

    if(strpos($globals->page["user_path"], $cm->router->getRuleById("mod_sec_social")->reverse) !== false) {
        if(MOD_SEC_SOCIAL_FACEBOOK && check_function("set_user_permission_by_settings")) {
            $cm->modules["security"]["events"]->addEvent("facebook_done_user_create", "set_user_permission_by_settings", ffEvent::PRIORITY_DEFAULT);
        }
        if(MOD_SEC_SOCIAL_GOOGLE && check_function("set_user_permission_by_settings")) {
            $cm->modules["security"]["events"]->addEvent("google_done_user_create", "set_user_permission_by_settings", ffEvent::PRIORITY_DEFAULT);
        }
        if(MOD_SEC_SOCIAL_JANRAIN && check_function("set_user_permission_by_settings")) {
            $cm->modules["security"]["events"]->addEvent("janrain_done_user_create", "set_user_permission_by_settings", ffEvent::PRIORITY_DEFAULT);
        }
        
        return false;
    }

	//Gestione Pagine o risorse con gli header non validi 
	// tutti i path che vengono intercettati da apachee con errorDocument
    if($globals->page["group"] == "error" && check_function("system_gallery_error_document")) {
		system_gallery_error_document($globals->page["user_path"]);
    }
	
	

			


    if(check_function("check_chron_job_by_file"))
        check_chron_job_by_file();


/*
    if($globals->page["primary"]) {
	  
		if(!defined("DISABLE_CACHE") && !$globals->page["restricted"]) {
	    }

		if(!$globals->page["restricted"]
	        && count($globals->locale["lang"]) > 2 //1 is current other is lang
	    ) {
		    if(check_function("get_international_settings_path")) {
    			$res_settings  = get_international_settings_path($settings_path, $globals->selected_lang, false);
    			if($res_settings["url"])
    				$settings_path = $res_settings["url"];

			}
		}
	}


    $globals->settings_path = $settings_path;
*/
   // $globals->user_path = ffCommon_specialchars($settings_path);
	//$globals->selected_lang = constant("LANGUAGE_INSET");
    //inizializzazione di tutti i permessi in base all'utenza
    //recupero della lingua dalle impostazione dei gruppi

    
    if(check_function("system_init_permission"))
    	system_init_permission();

    //system_init_permission($settings_path, $selected_lang);

    /**
    * Process And Define Constant by Routing rules
    */
    if(is_array($cm->router->named_rules) && count($cm->router->named_rules)) {
        //print_r($cm->router->named_rules);
        foreach ($cm->router->named_rules as $rule_key => $rule_value) {
            if(strpos($rule_key, "user_") === 0 && strlen($rule_key) > 5) {
                $globals->custom_data["user"][(string) $rule_value->reverse] = substr($rule_key, 5);
            } else {
                // Skip Process Rule specific for lang
                $check_rule = preg_replace('/_[[A-z]{2}$/', '', $rule_key);
                if($check_rule != $rule_key && $cm->router->getRuleById($check_rule))
                    continue;
                
                $rule = ($cm->router->getRuleById($rule_key . "_" . LANGUAGE_INSET_TINY) 
                    ? $cm->router->getRuleById($rule_key . "_" . LANGUAGE_INSET_TINY)
                    : $rule_value
                );               

                if(!defined("VG_SITE_" . strtoupper($rule_key)))
                    define("VG_SITE_" . strtoupper($rule_key), $rule->reverse);
            }
        }
        reset($cm->router->named_rules);
    }

    /**
    * Process And Define Constant by Restricted Settings
    */

    foreach($cm->modules["restricted"]["settings"] AS $setting_group => $setting_value) {
        if(count($setting_value)) {
            foreach($setting_value AS $setting_key => $setting_default) {
                if($setting_key == strtoupper($setting_key) && !array_key_exists($setting_key, $globals->settings)) {
                    $globals->settings[$setting_key] = (string) $setting_default->default;

                    if(!defined($setting_key))    define($setting_key, $globals->settings[$setting_key]);
                }
            }
        }
    }

    
    //if(defined("CM_MULTIDOMAIN_ROUTING") && CM_MULTIDOMAIN_ROUTING)
    //	check_page_alias($settings_path, $_SERVER["HTTP_HOST"], false);

    if(!AREA_INTERNATIONAL_SHOW_MODIFY) {
        ffTemplate::$_MultiLang_Hide_code = true;
    } else {
    	ffTemplate::$_MultiLang_Insert_code_empty = true;
    }

    if(get_session("UserID") == SUPERADMIN_USERNAME) 
        ffErrorHandler::$hide = false;
    else
        ffErrorHandler::$hide = true;

    //cm::getInstance()->addEvent("on_before_routing", "system_cache_on_before_routing", ffEvent::PRIORITY_HIGH);
	if($globals->page["layer"] != "empty")
		cm::getInstance()->addEvent("on_before_routing", "system_init_on_before_routing", ffEvent::PRIORITY_HIGH);

	$res = null;
	if($globals->page["primary"]) {
		if(strpos($globals->page["user_path"], $cm->router->getRuleById("mod_sec_social")->reverse) !== false)
			define("SKIP_VG_CONTENT", true);

		if($globals->page["group"] != "console") {
			ffTemplate::addEvent("on_loaded_data", "ffTemplate_applets_on_loaded_file");
			cm::getInstance()->addEvent("on_before_include_applet", "cms_on_before_include_applet");
		}

        if(!$globals->page["restricted"]) {
		    if(strlen($globals->strip_user_path))
			    $res["path_info"] = $globals->strip_user_path;
		    if($globals->ID_domain > 0)
			    $res["ID_domain"] = $globals->ID_domain;

        } else {
          //  $cm->modules["security"]["events"]->addEvent("on_retrive_params", "mod_security_on_retrive_params", ffEvent::PRIORITY_DEFAULT);

            if($globals->page["restricted"] && strpos($_REQUEST["frmAction"], "_export") !== false) {
                 ffGrid::addEvent("on_factory", "ffGrid_export_on_factory_export" , ffEvent::PRIORITY_DEFAULT);
                 ffGrid::addEvent("on_before_process_interface", "ffGrid_on_before_process_interface_export" , ffEvent::PRIORITY_DEFAULT);
            }            
        }
        

	    //facebook init
		if($globals->page["primary"] 
			&& !$globals->page["restricted"]
	        && MOD_SEC_SOCIAL_FACEBOOK
			&& global_settings("MOD_SEC_SOCIAL_FACEBOOK_APP")
			&& check_function("system_lib_facebook")
			//&& get_session("UserID") == MOD_SEC_GUEST_USER_NAME
		) {        
			//$cm->addEvent("mod_security_on_create_session", "system_lib_facebook", ffEvent::PRIORITY_DEFAULT);
			$user_error = system_lib_facebook(LANGUAGE_INSET);
			if($user_error && check_function("write_notification")) {
				write_notification("_facebook_app", $user_error, "warning", "", $globals->user_path);
			}
		}

	}

	return $res;
}

function system_cache_on_tpl_parse($oPage, $tpl) 
{           
	$globals = ffGlobals::getInstance("gallery");

	$skip_cache = (defined("DISABLE_CACHE")
					? true
					: $globals->cache["enabled"] === false
						? true
						: false
				);

    if(!$skip_cache && $globals->page["primary"] && !$globals->page["restricted"]) {
		$oPage->minify = "minify";
    } else {
        $oPage->compact_css = false;
		$oPage->compact_js = false;	
    }


    //$oPage->minify = "strip";
    if(defined("DISABLE_CACHE"))
    	$oPage->compress = false;
}

function system_init_on_before_routing($cm)
{           
    $globals = ffGlobals::getInstance("gallery");


    if(is_dir(FF_DISK_PATH . "/conf" .  GALLERY_PATH . "/mc")) {
        define("MASTER_CONTROL", true);
    } else {
        define("MASTER_CONTROL", false);
    }

    if($globals->locale["default"] && check_function("get_locale"))
    	$globals->locale = get_locale();

    $globals->settings_path = system_get_settings_path_by_user_path($globals->page);

    switch($globals->page["group"]) {
    	case "console":


			if(check_function("system_layer_restricted"))
				call_user_func_array("system_layer_" . $globals->page["name"], array(&$cm));
    		break;
    	case "frame":
			$cm->oPage->theme = FRONTEND_THEME;
    	    //da mettere il process frame  che sta ala momento in /srv/frame
			rewrite_request($globals->page["strip_path"]); //imposta user_path e settings_path togliendo eventuali parametri
	        //$globals->settings_path = $settings_path;
	        ffGrid::addEvent ("on_factory_done", "ffGrid_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);
	        ffRecord::addEvent ("on_factory_done", "ffRecord_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);

	        if(array_key_exists("sid", $_REQUEST) && strlen($_REQUEST["sid"])) {
	            $source_sid = str_replace("\\\"", "\"", $_REQUEST["sid"]);
	            $sid = get_sid($source_sid, null, true);
	        }

	        if(is_array($sid)) {
			    //if(!defined("DISABLE_CACHE")) {
			        //check_cache_sid($sid["key"], LANGUAGE_INSET);
			   // }
				//if(array_key_exists("key", $sid))
		    	//	$globals->sid = $sid["key"];
			    
				if(array_key_exists("value", $sid)) {
			        $params = json_decode($sid["value"], true);
			        //$globals->params = $params;

			        if(check_function("process_init_modules")) {
			            if((is_array($params["sys"]) && array_key_exists("layouts", $params["sys"]) && is_array($params["sys"]["layouts"]) && count($params["sys"]["layouts"])) || (is_array($params["sys"]) && array_key_exists("layouts", $params["sys"]) && !is_array($params["sys"]["layouts"]) && strlen($params["sys"]["layouts"]))) {
			                process_init_modules($cm->oPage, true, $params["sys"]["layouts"]); 
			            } elseif(is_array($params["sys"]) && array_key_exists("module", $params["sys"]) && is_array($params["sys"]["module"]) && count($params["sys"]["module"])) {
			                process_init_modules($cm->oPage, null, "", $params["sys"]["module"]);
			            }
			        }
				}
			}
    		break;
    	case "shard":
			check_function("system_layer_shards");

			$shard = system_layer_shards($globals->user_path);
			if($shard)
				echo $shard["pre"] . $shard["content"] . $shard["post"];
			else
			{
				if($cm->isXHR()) {
					http_response_code(500);
				} else
					http_response_code(404);
			}
	        exit;
		case "service":
    	case "updater":
    	case "actex":
    		//non dovrebbe mai entrare qui
    		ffErrorHandler::raise("Catrina: Percorso Riservato. Verificare perche entra qui", E_USER_ERROR, null, get_defined_vars());
    		break;
		case "search":
			rewrite_request($globals->page["strip_path"]); //imposta user_path e settings_path togliendo eventuali parametri
			if(!$cm->oPage->isXHR() && check_function("system_layer_gallery")) {
                if(check_function("get_webservices")) 
                   $services_params = get_webservices(null, $cm->oPage);
                
				$cm->oPage->addEvent("on_tpl_layer_process", "system_layer_gallery" , ffEvent::PRIORITY_HIGH);
            }
            
            $cm->oPage->addEvent("on_tpl_parse", "system_cache_on_tpl_parse", ffEvent::PRIORITY_DEFAULT);
			break;
    	case "login":
    		if($globals->settings_path == "/login" && MOD_SEC_LOGIN_FORCE_LAYER == "empty")
    			break;
    	case "public":
    		//if(check_function("system_gallery_redirect")) TODO: da grossi problemi redirect in home e infiniti 
    			//system_gallery_redirect($globals->settings_path);

    	case "user":
    	default:
			rewrite_request($globals->page["strip_path"]); //imposta user_path e settings_path togliendo eventuali parametri
            $cm->oPage->page_path = $globals->user_path;

            if(!$cm->oPage->isXHR() && check_function("get_webservices")) 
                $services_params = get_webservices(null, $cm->oPage);

            //feed, sitemap , manifest
            if(strpos(basename($globals->settings_path), "sitemap") === false
                && strpos(basename($globals->settings_path), "feed") === false
                && strpos(basename($globals->settings_path), "manifest.") === false
                && check_function("system_layer_gallery")
            ) {
                //$globals->settings_path = $settings_path;
	        //if(check_function("system_layer_gallery")) {
	            if($cm->oPage->isXHR() 
	                && (
	                    strpos($cm->path_info, VG_SITE_USER) !== 0
	                    && strpos($cm->path_info, VG_SITE_CART) !== 0 
	                    && strpos($cm->path_info, VG_SITE_NOTIFY) !== 0
	                    && strpos($cm->path_info, "/user") !== 0
	                    && strpos($cm->path_info, "/services") !== 0
	                    && !isset($_REQUEST["XHR_DIALOG_ID"])
	                    && !isset($_REQUEST["XHR_COMPONENT"]) //Server per i form e la registrazione ad esempio
	                    //&& strpos($cm->path_info, VG_SITE_SERVICES) !== 0
	                )
	            ) {
            		//da cachare il contenuto generato via ajax e applicare tutte le compressioni del caso
	                $buffer = system_layer_gallery($cm->oPage, null, true);
	                if(strlen($buffer)) {
	                    if(check_function("system_get_js_layout")) {
	                        $arrJs = system_get_js_layout($cm->oPage, $globals->js["request"], $globals->settings_path, true); 
	                        //$strBuffer = '<div id="' . $arrJs["key"] . '">' . preg_replace("/\n\s*/", "\n", $buffer) . $arrJs["data"] . '</div>'; 
	                        $strBuffer = preg_replace("/\n\s*/", "\n", $buffer) . $arrJs["data"];
	                    }

				        if(check_function("system_set_cache_page"))
				            system_set_cache_page($strBuffer);

	                    //cache_send_header_content(false, false, false, false, strlen($strBuffer), false);

	                    echo $strBuffer;
	                    exit;
	                }
	            } else {
	                $cm->oPage->addEvent("on_tpl_layer_process", "system_layer_gallery" , ffEvent::PRIORITY_HIGH);
	            }
	        //}
				$cm->oPage->addEvent("on_tpl_parse", "system_cache_on_tpl_parse", ffEvent::PRIORITY_DEFAULT);
				
                ffGrid::addEvent ("on_factory_done", "ffGrid_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);
                ffRecord::addEvent ("on_factory_done", "ffRecord_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);

                if(check_function("process_init_modules"))
                    process_init_modules($cm->oPage, ($_SERVER['REQUEST_METHOD'] == "POST" ? null : $cm->oPage->isXHR()));    	
            }   	
    }
}

function rewrite_request($strip_path = null) {
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");
	
	$arrEncodedParams = array();
	
	$user_path = $globals->user_path;
    $settings_path = $globals->settings_path;
    
	if($strip_path) {
		if(strpos($user_path, $strip_path) === 0)
			$user_path = substr($user_path, strlen($strip_path));
	    if(!strlen($user_path))
	        $user_path = "/";
		
		if(strpos($settings_path, $strip_path) === 0)
			$settings_path = substr($settings_path, strlen($strip_path));
	    if(!strlen($settings_path))
	        $settings_path = "/";
	}

	//check domain alias and manipulate internal path
    if($globals->page["alias"] && $globals->page["alias"] != $globals->locale["prefix"] &&  ($globals->page["group"] == "public" || $globals->page["group"] == "login")) {
        $globals->strip_user_path = $globals->page["alias"];
        if(strpos($user_path, $globals->page["alias"] . "/") !== 0 && $user_path != $globals->page["alias"]) {
	       // $globals->page["user_path"] = stripslash($globals->strip_user_path . $globals->page["user_path"]); //nn so se va bene. il canonical e sbagliato se viene riabilitato
	        $user_path = stripslash($globals->strip_user_path . $user_path); //serve per i template di livello
	        $settings_path = stripslash($globals->strip_user_path . $settings_path);
	        $cm->path_info = $user_path; //$globals->page["user_path"]
		}
    }   

   // if(CM_MULTIDOMAIN_ROUTING)
   //     $settings_path = check_page_alias($settings_path, $_SERVER["HTTP_HOST"]);

     $request = cache_get_request($_GET);
     if($request["get"]) {
     	if($request["get"]["search"])
     		$globals->search = $request["get"]["search"];
     	if($request["get"]["navigation"])
     		$globals->navigation = $request["get"]["navigation"];
     	if($request["get"]["sort"])
     		$globals->sort = $request["get"]["sort"];
		if($request["get"]["filter"])
			$globals->filter = $request["get"]["filter"];
			
     	$arrEncodedParams = $request["get"]["query"];
     }
	/**
	* Global Params for Search and Navigation
	*/  
	 /* 
	if(is_array($_GET) && count($_GET)) {
		foreach($_GET AS $req_key => $req_value) {
			if(is_array($_GET[$req_key]))
				continue;

		    if(!is_array($req_value) && !strlen($req_value)) 
				continue;

			if(is_numeric($req_key) && !$req_value)
				continue;

	        switch($req_key) {
	            case "q": 
	                $globals->search["term"] = $req_value;
	                $globals->search["params"]["q"] = "q=" . urlencode($req_value);
	                
	                $arrEncodedParams["q"] = $globals->search["params"]["q"];
	                break;
	            case "page":
	            	if(is_numeric($req_value) && $req_value > 0) {
		                $globals->navigation["page"] = $req_value;  
		                if($req_value > 1)
		                    $arrEncodedParams["page"] = "page=" . urlencode($globals->navigation["page"]);
					}
	                break;
	            case "count":
	            	if(is_numeric($req_value) && $req_value > 0) {
		                $globals->navigation["rec_per_page"] = $req_value;
		                
		                $arrEncodedParams["count"] = "count=" . urlencode($globals->navigation["rec_per_page"]);
					}
	                break;
	            case "sort":
	                $globals->sort["name"] = $req_value;
	                
	                $arrEncodedParams["sort"] = "sort=" . urlencode($globals->sort["name"]);
	                break;
	            case "dir":
	                $globals->sort["dir"] = $req_value;
	                
	                $arrEncodedParams["dir"] = "dir=" . urlencode($globals->sort["dir"]);
	                break;
	            default:
	            if(!preg_match('/[^a-z\-0-9]/i', $req_key)) {
	                $globals->search["available_terms"][$req_key] = $req_value;
	                $arrEncodedParams[$req_key] = $req_key . "=" . urlencode($globals->search["available_terms"][$req_key]);
				}
	        }
		}
	}*/

	/**
	*  Pagination By url
	*/
    $user_path_shard = "";
	//da problemi con la cache. distrugge le pagine cachate
	if(0 && is_numeric(basename($user_path)) && basename($user_path) > 0) {
		if(basename($settings_path) == basename($user_path)) {
			$settings_path = ffCommon_dirname($settings_path);
		}

		$globals->navigation["page"] = basename($user_path);
		$user_path = ffCommon_dirname($user_path);
		
		$user_path_shard = "/" . $globals->navigation["page"] . $user_path_shard;
        
        if(basename($user_path) > 1)
		    $arrEncodedParams["page"] = "page=" . urlencode($globals->navigation["page"]);
	}
	
	/** 
	*  Filter By First Lecter By url  
	*/
	//da problemi con la cache. distrugge le pagine cachate
	if(0 && strlen(basename($user_path)) == 1 && ctype_alpha(basename($user_path))) {
		if(basename($settings_path) == basename($user_path)) {
			$settings_path = ffCommon_dirname($settings_path);
		}

		$globals->filter["first_letter"] = strtolower(basename($user_path));
		$user_path = ffCommon_dirname($user_path);
		 
		$user_path_shard = "/" . $globals->filter["first_letter"] . $user_path_shard;
		$arrEncodedParams["ffl"] = "ffl=" . urlencode($globals->filter["first_letter"]);
	}
	
	if(is_array($globals->search) && count($globals->search)) {
		$globals->search["markable"]	        = true;
		$globals->search["limit"]		        = false;
		$globals->search["settings_type"]	    = false;
	}

	if(is_array($arrEncodedParams) && count($arrEncodedParams)) {
		$globals->request = $arrEncodedParams;
		$globals->user_path_params = "?" . implode("&", $arrEncodedParams);
	}

	//$globals->settings_path_shard = $settings_path_shard;
	$globals->user_path_shard = $user_path_shard;

	$globals->settings_path = $settings_path;
	$globals->user_path = $user_path;
	
}

function ffTemplate_applets_on_loaded_file($tpl)
{
	$cm = cm::getInstance();

	$cm->preloadApplets($tpl);
	$cm->parseApplets($tpl);
}
function cms_on_before_include_applet($cm, $name, $params, $id) {
	$globals = ffGlobals::getInstance("gallery");
	$globals->applets["notifier"] = FF_DISK_PATH . "/library/gallery/classes/notifier/applet/index.php";

	return $globals->applets[$name];
}