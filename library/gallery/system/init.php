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

function system_init($cm) {
    $globals = ffGlobals::getInstance("gallery");

    $path_info                          = stripslash($_SERVER["PATH_INFO"]);
    if($path_info == "/index" || !$path_info)
        $path_info                      = "/";

    $globals->user_path 	            = $path_info;
    $globals->page 			            = cache_get_page_properties($path_info, true);
    $globals->locale 		            = cache_get_locale($globals->page, DOMAIN_NAME); //pulisce il percorso dalla lingua
    $globals->selected_lang             = FF_LOCALE;

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

    define("FF_THEME_FRAMEWORK_CSS", ($globals->page["restricted"] && defined("FRAMEWORK_CSS_RESTRICTED")
        ? FRAMEWORK_CSS_RESTRICTED
        : ($globals->page["framework_css"]
            ? $globals->page["framework_css"]
            : (defined("FRAMEWORK_CSS")
                ? FRAMEWORK_CSS
                : "bootstrap-fluid"
            )
        )
    ));

    define("FF_THEME_FONT_ICON", ($globals->page["restricted"] && defined("FONT_ICON_RESTRICTED")
        ? FONT_ICON_RESTRICTED
        : ($globals->page["font_icon"]
            ? $globals->page["font_icon"]
            : (defined("FONT_ICON")
                ? FONT_ICON
                : "fontawesome"
            )
        )
    ));

	$globals->page["framework_css"] 	= FF_THEME_FRAMEWORK_CSS;
	$globals->page["font_icon"] 		= FF_THEME_FONT_ICON;

	if($globals->page["restricted"]) {
		$cm_layout_vars["exclude_form"] = false;
		$cm_layout_vars["compact_js"] 	= false;
		$cm_layout_vars["compact_css"] 	= false;
	}

	cm::getInstance()->layout_vars = $cm_layout_vars;

	if($cm_layout_vars["layer"] === null && $cm_layout_vars["theme"] == FRONTEND_THEME) {
		cm::getInstance()->layout_vars["layer"] = "empty";
	}
	if($cm_layout_vars["layer"] == THEME_INSET && $cm_layout_vars["theme"] != FRONTEND_THEME)
		cm::getInstance()->layout_vars["theme"] = FRONTEND_THEME;

	if($globals->page["router"]) {
		cm::getInstance()->router->addRule(
			$globals->page["router"]["source"]
			, array(
				"url" => $globals->page["router"]["destination"]
			)
			, cmRouter::PRIORITY_NORMAL
			, true
			, false
			, 0
			, array()
			, ($globals->page["router"]["reverse"]
				? $globals->page["router"]["reverse"]
				: "/" . $globals->page["name"]
			)
		);
	}

	if(defined("SHOWFILES_IS_RUNNING")
		|| $globals->page["group"] == "actex"
		|| $globals->page["group"] == "service" // nn lo so
		|| $globals->page["group"] == "resource"
		//|| strpos($globals->settings_path, "/services") === 0
		//|| isset($_REQUEST["XHR_COMPONENT"])
	) {
		if($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"]) {
			if(Stats::isCrawler()) {
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
		if(defined("SERVICE_TIME_LIMIT") && SERVICE_TIME_LIMIT > 0)
			set_time_limit(SERVICE_TIME_LIMIT);

		cm::getInstance()->layout_vars["layer"] = "empty";
		cm::getInstance()->layout_vars["page"] = "XHR";
		cm::getInstance()->layout_vars["exclude_ff_js"] = true;

		//ffTemplate::addEvent("on_loaded_data", "ffTemplate_applets_on_loaded_file");
		//cm::getInstance()->addEvent("on_before_include_applet", "cms_on_before_include_applet");
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

    if($globals->page["group"] == "login" || $globals->page["api"] == "login") {
        Auth::env("CALLBACK_LOGIN", "check_user_request");
    }


	if(/*$globals->page["group"] == "service"
        ||*/ ($globals->page["group"] == "login" && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
    ) {
//        define("FF_LOCALE", $globals->selected_lang);
//       define("LANGUAGE_INSET", $globals->selected_lang);
        define("SKIP_CMS", true);
        return false;
    }

	cm::getInstance()->addEvent("on_before_cm", "system_init_on_before_cm", ffEvent::PRIORITY_HIGH);
}

function normalize_url_by_current_lang($user_path, $prefix = true, $abs_url = false) {
	$globals = ffGlobals::getInstance("gallery");
	$schema = Cms::getSchema();

	if($prefix === true) {
		$prefix = $globals->locale["prefix"];
	}
	$res = FF_SITE_PATH . $prefix . $user_path;
	if ($res != "/" && substr($res,-1) == "/")
		$res = substr($res,0,-1);

    $arrSettings_path = explode("/", trim($res, "/"));
	if(is_array($schema["alias"]) && count($schema["alias"])) {
		$alias_flip = array_flip($schema["alias"]);
		if($alias_flip["/" . $arrSettings_path[0]]) {
			$res = substr($res, strlen("/" . $arrSettings_path[0]));
			$prefix_url = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $alias_flip["/" . $arrSettings_path[0]];
		}
	}

	if($abs_url && !$prefix_url) {
		$domain = (defined("DOMAIN_DEFAULT")
			? DOMAIN_DEFAULT
			: (substr_count(DOMAIN_NAME, ".") > 1
				? DOMAIN_NAME
				: "www." . DOMAIN_NAME
			)
		);

		//substr($domain_name, 0, strpos($domain_name, "."));
		$prefix_url = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . $domain;
	}

	return $prefix_url . $res;
}

function system_get_settings_path_by_user_path($page) {
	$globals = ffGlobals::getInstance("gallery");

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


														, "is_wishlisted" 																	=> (!Cms::env("AREA_SHOW_ECOMMERCE") && Cms::env("USE_CART_PUBLIC_MONO")
																																				? "( SELECT ecommerce_order_detail.ID
																																			        FROM ecommerce_order_detail
			        																																	INNER JOIN ecommerce_order ON ecommerce_order.ID = ecommerce_order_detail.ID_order
																																			        WHERE ecommerce_order_detail.ID_items = vgallery_nodes.ID
			        																																	AND ecommerce_order_detail.tbl_src = 'vgallery_nodes'
			        																																	AND ecommerce_order.ID_user_cart = " . $db->toSql(Auth::get("user")->id, "Number") . "
																																						AND ecommerce_order.cart_name = " . $db->toSql(ffCommon_url_rewrite(Auth::get("user")->username)) . " AND ecommerce_order.wishlist_archived = 0
																																						AND ecommerce_order.is_cart > 0 )"
																																				: "''"
																																			) . "											AS `is_wishlisted`"
														, "available" 																		=> (Cms::env("AREA_SHOW_ECOMMERCE") && Cms::env("AREA_ECOMMERCE_LIMIT_FRONTEND_BY_STOCK")
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
						if($def[$data["schema"]]["seo"]["visible"] && !Cms::env("ENABLE_STD_PERMISSION") && Cms::env("ENABLE_ADV_PERMISSION")) {
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

function system_init_on_before_cm($cm)
{
    $globals = ffGlobals::getInstance("gallery");

    if(defined("SKIP_CMS"))
        return false;

    if(strpos($globals->page["user_path"], $cm->router->getRuleById("mod_auth_social")->reverse) !== false) {
		define("SKIP_VG_CONTENT", true);

         return false;
    }

	//Gestione Pagine o risorse con gli header non validi
	// tutti i path che vengono intercettati da apachee con errorDocument
    if($globals->page["group"] == "error" && check_function("system_gallery_error_document")) {
		system_gallery_error_document($globals->page["user_path"]);
    }


    //if(check_function("system_init_permission"))
    //	system_init_permission();


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

    if(!Auth::env("AREA_INTERNATIONAL_SHOW_MODIFY")) {
        define("FF_TRANSLATOR_HIDE_CODE", true);
    }

    if(Auth::isAdmin())
        ffErrorHandler::$hide = false;
    else
        ffErrorHandler::$hide = true;

    //cm::getInstance()->addEvent("on_before_routing", "system_cache_on_before_routing", ffEvent::PRIORITY_HIGH);
	if($globals->page["layer"] != "empty")
		cm::getInstance()->addEvent("on_before_routing", "system_init_on_before_routing", ffEvent::PRIORITY_HIGH);

	$res = null;
	if($globals->page["primary"]) {
		if($globals->page["group"] != "console") {
			//ffTemplate::addEvent("on_loaded_data", "ffTemplate_applets_on_loaded_file");
			//cm::getInstance()->addEvent("on_before_include_applet", "cms_on_before_include_applet");
		}

		if($globals->page["group"] == "facebook") {
            //facebook init
            if($globals->page["primary"]
                && !$globals->page["restricted"]
                && Cms::env("MOD_AUTH_SOCIAL_FACEBOOK_APP")
                && check_function("system_lib_facebook")
            ) {
                $user_error = system_lib_facebook(LANGUAGE_INSET);
                if($user_error) {
                    Logs::write($user_error, "facebook");
                }
            }
        }

	     cm::getInstance()->addEvent("on_before_page_process", function(cm $cm) {
			$glob_libs = ffGlobals::getInstance("__ffTheme_libs__");

			cm_loadlibs($glob_libs->libs, FF_DISK_PATH . "/library/gallery", "cms", "", false, false);
			if(Cms::env("AREA_SHOW_ECOMMERCE"))
				cm_loadlibs($glob_libs->libs, FF_DISK_PATH . "/library/gallery/ecommerce", "cms", "cms/ecommerce", false, false);

			 cm_loadlibs_save($glob_libs->libs, $_REQUEST["__NOCACHE__"]); //todo: non va bene questa variabile. non si aggiorna mai il file
	     }, ffEvent::PRIORITY_HIGH);

        if(!$globals->page["restricted"]) {
		    if(strlen($globals->strip_user_path))
			    $res["path_info"] = $globals->strip_user_path;
		    if($globals->ID_domain > 0)
			    $res["ID_domain"] = $globals->ID_domain;

        } else {
        	Auth::addEvent("on_check_session", function() { //da rifare prob con i cookie. con la cache questo non funziona piu
			    $globals = ffGlobals::getInstance("gallery");

				$request = get_session("request_info");
				if(!strlen($request))
					$request = get_session("request_vgallery");

				if($request)
				{
				    if(strpos($globals->page["user_path"], USER_RESTRICTED_PATH) === false
				        &&
				        strpos($globals->page["user_path"], VG_SITE_MOD_SEC_LOGIN) === false
				        &&
				        strpos($globals->page["user_path"], VG_SITE_UPDATER) === false
				        &&
				        strpos($globals->page["user_path"], VG_SITE_ERROR) === false
				        &&
				        strpos($globals->page["user_path"], VG_SITE_VGALLERY) === false
				        &&
				        strpos($globals->page["user_path"], $request) === false
				    )
				    {
				        ffRedirect(FF_SITE_PATH . $request . "?ret_url=" . urlencode($globals->user_path));
				    }
				}
			});

            if(strpos($_REQUEST["frmAction"], "_export") !== false) {
                 ffGrid::addEvent("on_factory", function($page, $disk_path, $theme) {
						$base_path = $disk_path . "/themes/responsive";
						$class_name = "ffGrid_xls";

						$base_path .= "/ff/ffGrid/" . $class_name . "." . FF_PHP_EXT;

						return array("class_name" => $class_name
									, "base_path" => $base_path
						);
					}, ffEvent::PRIORITY_DEFAULT);

                 ffGrid::addEvent("on_before_process_interface", function($component) {
					$arrAction = explode("_", $_REQUEST["frmAction"]);
					if($arrAction[0] != $component->id) {
						return false;
					}
				}, ffEvent::PRIORITY_DEFAULT);
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

    if(!$skip_cache && $globals->page["cache"] && !$globals->page["restricted"]) {
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

    switch($globals->page["group"]) {
    	case "console":
    		$cm->router->addRule(
    			"^/" . $globals->page["name"]. "(.*)"
    			, array(
    				"module" => "security"
    				, "url"=> '/auth$0'
    			)
    			, cmRouter::PRIORITY_HIGH
    			, true
    			, true
    		);
    		if(!$cm->isXHR()) {
                $cm->router->addRule(
                    "^/" . $globals->page["name"] . "(.*)"
                    , array(
                        "module" => "restricted"
                    , "url" => '/layout$0'
                    )
                    , cmRouter::PRIORITY_HIGH
                    , true
                    , true
                );
            }
    		if($globals->page["name"] == "ecommerce") {

			} elseif(strpos($globals->page["user_path"], "/" . $globals->page["name"] . "/addons") === 0) {
    			$cm->router->addRule("^/" . $globals->page["name"]. "/addons(.*)", array("url"=> VG_UI_PATH . '/addons$1'), cmRouter::PRIORITY_LOW, true);
    		} else {
    			$cm->router->addRule("^/" . $globals->page["name"]. "(.*)", array("url"=> VG_UI_PATH . '/restricted/blocks$1'), cmRouter::PRIORITY_LOW, true);
    			if(is_dir(FF_DISK_PATH . "/" . VG_UI_PATH . "/restricted/" . $globals->page["name"])) {
    				$cm->router->addRule("^/" . $globals->page["name"] . "(.*)", array("url"=> VG_UI_PATH . "/restricted/" . $globals->page["name"] . '$1'), cmRouter::PRIORITY_NORMAL, true);
    			}
			}

    		$cm->router->getRuleById("mod_auth_login")->reverse = "/" . $globals->page["name"] . "/login"; //da mettere la rule qui

			if(is_array($_ENV["VG_RULE"]) && count($_ENV["VG_RULE"])) {
				foreach($_ENV["VG_RULE"] AS $key => $value) {
					$base_rule = VG_SYS_PATH;
					$real_key = strtolower($key);

					if(strpos($value, "/") === 0) {
						$src = $value;
    					$dest = "/" . basename($value);
					} else {
						$src = "/" . $globals->page["name"] . "/" . $value;
    					$dest = "/" . $value;
					}
					//echo $src . "  <===> " . $dest . "<br>";
    				$cm->router->addRule("^" . $src . "(.*)", array("url"=> $base_rule . $dest . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "mod_sec_" . $real_key), $src);
				}
			}
    		if(is_array($_ENV["VG_RULE_UI"]) && count($_ENV["VG_RULE_UI"])) {
				foreach($_ENV["VG_RULE_UI"] AS $key => $value) {
					$base_rule = VG_UI_PATH . "/restricted";
    				$real_key = strtolower($key);

					if(strpos($value, "/") === 0) {
						$src = $value;
    					$dest = "/" . basename($value);
					} else {
						$src = "/" . $globals->page["name"] . "/" . $value;
    					$dest = "/" . $value;
					}
					//echo $src . "  <===> " . $dest . "<br>";
    				$cm->router->addRule("^" . $src . "(.*)", array("url"=> $base_rule . $dest . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "mod_sec_" . $real_key), $src);
				}
			}
			/*
    		$constant = get_defined_constants(true);
    		$rules = array_filter($constant["user"], function($k) {
				return strpos($k, "VG_RULE_") === 0;
    		}, ARRAY_FILTER_USE_KEY);

    		if(is_array($rules) && count($rules)) {
    			foreach($rules AS $key => $value) {
    				if(strpos($key, "VG_RULE_UI_") === 0) {
    					$base_rule = VG_UI_PATH . "/restricted";
    					$real_key = strtolower(str_replace("VG_RULE_UI_", "", $key));
					} else {
						$base_rule = VG_SYS_PATH;
						$real_key = strtolower(str_replace("VG_RULE_", "", $key));
					}
					if(strpos($value, "/") === 0) {
						$src = $value;
    					$dest = "/" . basename($value);
					} else {
						$src = "/" . $globals->page["name"] . "/" . $value;
    					$dest = "/" . $value;
					}
					//echo $src . "  <===> " . $dest . "<br>";
    				$cm->router->addRule("^" . $src . "(.*)", array("url"=> $base_rule . $dest . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "mod_sec_" . $real_key), $src);
    			}
    		}*/
    		//die();
    		//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());

    		if($cm->isXHR()) {
				if(strpos($globals->user_path, "/admin") === 0 && substr_count($globals->user_path, "/") == 3) {
					$admin_type = basename(ffCommon_dirname($globals->user_path));

					switch($admin_type) {
						case "block":
						case "item":
						case "bar":
							check_function("process_admin");
							$buffer = process_admin($admin_type, basename($globals->user_path));
							if($buffer)
								if($buffer === true) { //da vedere bene
									http_response_code(204);
								} else {
									echo $buffer;
								}
							else
								http_response_code(500);

							exit;
						default:
					}
				}
    		} else {
				if(check_function("system_layer_restricted"))
					call_user_func_array("system_layer_restricted", array(&$cm, $globals->page["name"]));
			}
    		break;
    	case "frame":
			$cm->oPage->theme = FRONTEND_THEME;
    	    //da mettere il process frame  che sta ala momento in /srv/frame
			rewrite_request($globals->page["strip_path"]); //imposta user_path e settings_path togliendo eventuali parametri

	        //$globals->settings_path = $settings_path;
	       // ffGrid::addEvent ("on_factory_done", "ffGrid_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);
	       // ffRecord::addEvent ("on_factory_done", "ffRecord_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);

	        if(array_key_exists("sid", $_REQUEST) && strlen($_REQUEST["sid"])) {
	            $source_sid = str_replace("\\\"", "\"", $_REQUEST["sid"]);
	            $sid = get_sid($source_sid, null, true);
	        }

	        if(is_array($sid)) {
			    //if(!defined("DISABLE_CACHE")) {
			        //check_cache_sid($sid["key"], LANGUAGE_INSET);
			   // }
				if(array_key_exists("key", $sid))
		    		$globals->sid = $sid["key"];

				if(array_key_exists("value", $sid)) {
			        $params = json_decode($sid["value"], true);
			        $globals->params = $params;

			        if(check_function("process_init_modules")) {
			            if((is_array($params["sys"]) && array_key_exists("layouts", $params["sys"]) && is_array($params["sys"]["layouts"]) && count($params["sys"]["layouts"])) || (is_array($params["sys"]) && array_key_exists("layouts", $params["sys"]) && !is_array($params["sys"]["layouts"]) && strlen($params["sys"]["layouts"]))) {
			                process_init_modules($cm->oPage, true, $params["sys"]["layouts"]);
			            } elseif(is_array($params["sys"]) && array_key_exists("module", $params["sys"]) && is_array($params["sys"]["module"]) && count($params["sys"]["module"])) {
			                process_init_modules($cm->oPage, null, "", $params["sys"]["module"]);
			            }
			        }
				}
			}

			//$globals->tpl = system_pre_process_page($params);
//da rivedere tutto

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
			$cm->router->addRule("^" . $globals->user_path . '(.*)', array("url"=> "/conf/gallery/search" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "search"), "/search");

			rewrite_request($globals->page["strip_path"]); //imposta user_path e settings_path togliendo eventuali parametri
			if(!$cm->oPage->isXHR() && check_function("system_layer_gallery")) {
                if(check_function("get_webservices"))
                   $services_params = get_webservices(null, $cm->oPage);

				$cm->oPage->addEvent("on_after_process_components", "system_layer_gallery" /*, ffEvent::PRIORITY_HIGH*/);
            }

			$cm->oPage->addEvent("on_tpl_parse", "system_cache_on_tpl_parse", ffEvent::PRIORITY_DEFAULT);
            $cm->oPage->addEvent("on_tpl_parsed", "system_set_cache_page" , ffEvent::PRIORITY_FINAL);

            break;
    	case "login":
            //$cm->router->addRule("^" . $globals->page["user_path"]. "(.*)", array("module" => "security", "url"=> '/login$1'), cmRouter::PRIORITY_DEFAULT, true, false, 0, null, $globals->page["user_path"]);
    	case "user":
    		if(strpos($globals->page["user_path"], "/user/account") === 0) {
    			$cm->router->addRule("^" . $globals->user_path . '(.*)', array("url"=> VG_UI_PATH . "/restricted/auth/profile/account" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "account"), "/user/account");
    		} elseif(strpos($globals->page["user_path"], "/user/vgallery") === 0) {
    			$cm->router->addRule("^" . $globals->user_path . '(.*)', array("url"=> VG_UI_PATH . "/restricted/blocks/contents" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "vgallery"), "/user/vgallery");
    			$cm->router->addRule("^" . $globals->user_path . '(/[^/]+)*/modify$', array("url"=> VG_UI_PATH . "/restricted/blocks/contents/modify" . '$1'), cmRouter::PRIORITY_NORMAL, true);
    		} elseif(strpos($globals->page["user_path"], "/user/resources") === 0) {
    			$cm->router->addRule("^" . $globals->user_path . "(.*)", array("url"=> VG_UI_PATH . "/restricted/albums/resources" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "gallery"), "/user/resources");
    		} elseif(strpos($globals->page["user_path"], "/user/draft") === 0) {
    			$cm->router->addRule("^" . $globals->user_path . "(.*)", array("url"=> VG_UI_PATH . "/restricted/wysiwyg" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "draft"), "/user/draft");
    		} elseif(strpos($globals->page["user_path"], "/user/menu") === 0) {
    			$cm->router->addRule("^" . $globals->user_path . "(.*)", array("url"=> VG_UI_PATH . "/restricted/pages" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "menu"), "/user/menu");
    		} elseif(strpos($globals->page["user_path"], "/user/ecommerce") === 0) {
    			$cm->router->addRule("^" . $globals->user_path . "(.*)", array("url"=> "/conf/gallery/ecommerce/manage" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "ecommerce"), "/user/ecommerce");
    			$cm->router->addRule("^" . $globals->user_path . '/preview(.*)', array("url"=> "/conf/gallery/ecommerce/documents/bill/preview" . '$1'), cmRouter::PRIORITY_NORMAL, true);
    			$cm->router->addRule("^" . $globals->user_path . '/form(.*)', array("url"=> VG_UI_PATH . "/addons/form/contents" . '$1'), cmRouter::PRIORITY_NORMAL, true);
    		} elseif(strpos($globals->page["user_path"], "/user/wishlist") === 0) {
				$cm->router->addRule("^" . $globals->user_path . "(.*)", array("url"=> "/conf/gallery/ecommerce/cart/wishlist" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "wishlist"), "/user/wishlist");
    		} elseif(strpos($globals->page["user_path"], "/user/cart") === 0) {
				$cm->router->addRule("^" . $globals->user_path . "(.*)", array("url"=> "/conf/gallery/ecommerce/cart" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "cart"), "/user/cart");
    		} elseif(strpos($globals->page["user_path"], "/user/mpay") === 0) {
    			$cm->router->addRule("^" . $globals->user_path . "(.*)", array("url"=> "/conf/gallery/ecommerce/mpay" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "mpay"), "/user/mpay");
			} else {
				$cm->router->addRule("^" . $globals->user_path . '(.*)', array("url"=> VG_UI_PATH . "/restricted/auth/profile" . '$1'), cmRouter::PRIORITY_NORMAL, true, false, 0, array("id" => "profile"), "/user");
			}
    	case "public":
    		//if(check_function("system_gallery_redirect")) TODO: da grossi problemi redirect in home e infiniti
    			//system_gallery_redirect($globals->settings_path);

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
                && check_function("system_set_cache_page")
            ) {
                system_load_resources();

                if($_REQUEST["XHR_COMPONENT"]) {
                    $globals->tpl = system_pre_process_page();
                } else {
                    if(Cms::env("ENABLE_ADV_PERMISSION") && check_function("system_adv_permission")) {
                        system_adv_permission($globals->settings_path);
                    }

                    if($cm->oPage->isXHR()
                        && (
                            strpos($cm->path_info, VG_SITE_USER) !== 0
                            && strpos($cm->path_info, VG_SITE_CART) !== 0
                            && strpos($cm->path_info, VG_SITE_NOTIFY) !== 0
                            && strpos($cm->path_info, "/user") !== 0
                            && strpos($cm->path_info, "/services") !== 0
                            && !isset($_REQUEST["XHR_CTX_ID"])
                            && !isset($_REQUEST["XHR_COMPONENT"]) //Server per i form e la registrazione ad esempio
                        )
                    ) {
                        $globals->tpl = system_pre_process_page(array(
                            "limit_section" => true
                        ));
                        $cm->oPage->process(false);

                        //da cachare il contenuto generato via ajax e applicare tutte le compressioni del caso
                        $buffer = system_layer_gallery($cm->oPage);
                        if($buffer["content"]) {
	                        $frame_buffer = $buffer["content"] . $buffer["media"];
                            system_set_cache_page($frame_buffer);

                            echo $frame_buffer;
                            exit;
                        }
                    } else {
                        $globals->tpl = system_pre_process_page();
                        $cm->oPage->addEvent("on_after_process_components", "system_layer_gallery" /*, ffEvent::PRIORITY_HIGH*/);
                    }
                }
            }

			$cm->oPage->addEvent("on_tpl_parse", "system_cache_on_tpl_parse", ffEvent::PRIORITY_DEFAULT);
            $cm->oPage->addEvent("on_tpl_parsed", "system_set_cache_page" , ffEvent::PRIORITY_FINAL);

        //  ffGrid::addEvent ("on_factory_done", "ffGrid_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);
	       // ffRecord::addEvent ("on_factory_done", "ffRecord_gallery_on_factory_done" , ffEvent::PRIORITY_HIGH);



    }


}

/**
* Load resources
*/
function system_load_resources() {
	$globals = ffGlobals::getInstance("gallery");
	$base_path = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME;

	$directory = new RecursiveDirectoryIterator(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME);
	$flattened = new RecursiveIteratorIterator($directory);


	// Make sure the path does not contain "/.Trash*" folders and ends eith a .php or .html file
	$files = new RegexIterator($flattened, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:js|css|html)$#Di');

	foreach($files as $file) {
		if(strpos($file, $base_path . "/css") === 0) {
			$globals->css["frontend"][substr($file, strlen($base_path . "/css"))] = true;
		} elseif(strpos($file, $base_path . "/javascript") === 0) {
			$globals->js["frontend"][substr($file, strlen($base_path . "/javascript"))] = true;
		} elseif(strpos($file, $base_path . "/contents") === 0) {
			$globals->html["frontend"][substr($file, strlen($base_path . "/contents"))] = true;
		} elseif(strpos($file, $base_path . "/pages") === 0) {
			$globals->html["pages"][substr($file, strlen($base_path . "/pages"))] = true;
		}
	}
}


function rewrite_request($strip_path = null) {
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");
    if($globals->locale["default"] && check_function("get_locale"))
    	$globals->locale = get_locale();

	if(!$globals->settings_path)
    	$globals->settings_path = system_get_settings_path_by_user_path($globals->page);

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


    $request = Cms::requestCapture();
    if($request["search"]) {
        $globals->search = $request["search"];
    }
    if($request["navigation"]) {
        $globals->navigation = $request["navigation"];
    }
    if($request["dir"]) {
        $globals->sort = array("dir" => $request["dir"]);
    }
    if($request["filter"]) {
        $globals->filter = $request["filter"];
    }
    if($request["valid"]) {
        $globals->request = $request["valid"];
    }

    if(is_array($globals->search) && count($globals->search)) {
        $globals->search["markable"]	        = true;
        $globals->search["limit"]		        = false;
        $globals->search["settings_type"]	    = false;
    }

	$globals->settings_path = $settings_path;
	$globals->user_path = $user_path;

}
/*
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
}*/