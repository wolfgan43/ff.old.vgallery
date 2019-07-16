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
define("SITEMAP_LIMIT", 20000);
define("FEED_LIMIT", 50);


function system_get_sitemap_index($ext = "xml", $strip_user_path = null, $module = true) {
	$base_path = "/cache/sitemap";

	$date_type = "Y-m-d H:i";	
	switch($ext) {
		case "html":
			break;
		case "rss":
			$date_type = "D, d M Y H:i:s O";
			break;
		case "json":
			$template = false;
			$mime = ffMedia::getMimeTypeByExtension($ext);
			break;
		case "xml":
			$date_type = DATE_ATOM;
			$template = "sitemapindex.xml";
			$mime = ffMedia::getMimeTypeByExtension($ext);
			break;
		default:	
		
	}	

	$file = $base_path . $strip_user_path . "/index." . $ext;

	if(0 && is_file(FF_DISK_PATH . $file) && filemtime(FF_DISK_PATH . $file) > filectime(FF_DISK_PATH . $file)) {
		$buffer = file_get_contents(FF_DISK_PATH . $file);
	} else {
		$db = ffDB_Sql::factory();

		if(check_function("get_locale"))
			$locale = get_locale(null, true);

		$sSQL = "SELECT static_pages.ID
					, static_pages.last_update
				FROM static_pages
				WHERE static_pages.visible > 0
					" . ($strip_user_path
						? " AND static_pages.parent LIKE '" . $db->toSql($strip_user_path, "Text", false) . "%'"
						: ""
					) . "
				ORDER BY static_pages.last_update DESC";
		$db->query($sSQL);
		$count_base = $db->numRows();
		if($count_base) {
			$sitemap["base"] = array(
						"url" => "/sitemap-" . "base." . $ext
						, "lastmod" =>  date($date_type, (is_file(FF_DISK_PATH . $base_path . "/" . "base." . $ext) 
							? filemtime(FF_DISK_PATH . $base_path . "/" . "base." . $ext) 
							: $db->getField("last_update", "Number", true)
						))
						, "mono_lang" => true
						, "count" => $db->numRows()
					);
		}
					
		$sSQL = "SELECT layout.value
					, layout.last_update
					, " . (is_array($locale["lang"]) && count($locale["lang"] > 1)
						? " IF(layout.value = 'anagraph'
							, '0'
							, (SELECT vgallery.enable_multilang_visible
								FROM vgallery
								WHERE vgallery.name = layout.value)
						 )"
						: "0"  
					) . " AS enable_multilang_visible
					, IF(layout.value = 'anagraph'
						, (SELECT COUNT(anagraph.ID) 
							FROM anagraph
							WHERE anagraph.visible > 0
								AND IF(layout.params
									, FIND_IN_SET(layout.params, anagraph.categories)	
									, 1
								)
						)
						, (SELECT COUNT(vgallery_nodes.ID) 
							FROM vgallery_nodes
								INNER JOIN vgallery ON vgallery_nodes.ID_vgallery = vgallery.ID 
							WHERE vgallery.name = layout.value
								AND vgallery_nodes.parent LIKE CONCAT(layout.params, '%')
								AND vgallery_nodes.visible > 0
						)
					) AS count_block
				FROM layout
					INNER JOIN layout_type ON layout_type.ID = layout.ID_type
					" . ($strip_user_path
						? " INNER JOIN layout_path ON layout_path.ID_layout = layout.ID AND layout_path.path LIKE '" . $db->toSql($strip_user_path, "Text", false) . "%'"
						: ""
					) . "					
				WHERE layout_type.name = " . $db->toSql("VIRTUAL_GALLERY") . "
				ORDER BY `value`, `params`";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$block_name = $db->getField("value", "Text", true);
				$block_count = $db->getField("count_block", "Number", true);
				if($block_count) {
					$sitemap[$block_name] = array(
						"url" => "/sitemap-" . $block_name . "." . $ext
						, "lastmod" => date($date_type, (is_file(FF_DISK_PATH . $base_path . "" . "/" . $block_name . "." . $ext) 
											? filemtime(FF_DISK_PATH . $base_path . "" . "/" . $block_name . "." . $ext) 
						 					: ($db->getField("last_update", "Number", true) > 0
												? $db->getField("last_update", "Number", true)
												: time()
											)
						 				))
						, "mono_lang" => $db->getField("enable_multilang_visible", "Number", true)
						, "count" => $block_count
					);
					
					$block_page = floor($block_count / SITEMAP_LIMIT);
					if($block_page) {
						for($i = 2; $i <= $block_page+1; $i++) {
							$sitemap[$block_name . "-" . $i] = $sitemap[$block_name];
							$sitemap[$block_name . "-" . $i]["url"] = "/sitemap-" . $block_name . "-" . $i . "." . $ext;
						
						}
					}
				}
			} while($db->nextRecord());
		}

		if($module) {
			if($module === true) {
				$module = array();
				$ff_modules = glob(FF_DISK_PATH . "/modules/*");
				if(is_array($ff_modules) && count($ff_modules)) {
				    foreach($ff_modules AS $real_module_dir) {
			    		$block_name = basename($real_module_dir);
			    		
						$module[$block_name] = null;
					}		
				}
			} elseif(is_string($module) && strlen($module)) {
				$module = array($module => null);
			}

			if(is_array($module) && count($module)) {
				check_function("get_schema_fields_by_type");
				foreach($module AS $module_name => $module_lastmod) {
			    	if(is_file(FF_DISK_PATH . "/modules/" . $module_name . "/conf/schema." . FF_PHP_EXT)) {
						require FF_DISK_PATH . "/modules/" . $module_name . "/conf/schema." . FF_PHP_EXT;

						if(is_array($schema) && count($schema)) {
							foreach($schema AS $block_name => $block_params) {
								//if(is_array($block_params)) {
									$block_params = get_schema_fields_by_type($block_params, true);
								//} elseif(strlen($block_params)) {
								//	$block_params = get_schema_fields_by_type($block_params, true);	
								//}
								if(!$block_params["seo"]["primary_table"])
									continue;

								if($strip_user_path && strpos($block_name, $strip_user_path) === false)
									continue;
								
								if(!$strip_user_path && substr_count($block_name, "/") > 1)
									continue;

								$block_target = basename($block_name);								
								
								$sSQL = "SELECT `" . $block_params["seo"]["primary_table"] . "`.ID
											, `" . $block_params["seo"]["primary_table"] . "`.`" . $block_params["field"]["last_update"] . "` AS last_update
										FROM `" . $block_params["seo"]["primary_table"] . "`"
											. ($block_params["field"]["ID_category"] && $block_params["type"] 
			        							? " INNER JOIN `" . $block_params["type"] . "` ON `" . $block_params["type"] . "`.ID = " . "`" . $block_params["seo"]["primary_table"] . "`.`" . $block_params["field"]["ID_category"] . "`
			        	 								AND  `" . $block_params["type"] . "`.`name` = " . $db->toSql($block_target)
			        							: ""
										    )										
										. " WHERE `" . $block_params["seo"]["primary_table"] . "`.`" . $block_params["field"]["permalink"] . "` <> '' "
			        						. ($block_params["field"]["visible"] 
			        							? " AND `" . $block_params["seo"]["primary_table"] . "`.`" . $block_params["field"]["visible"] . "` = '1'"
			        							: ""
			        						)
										. " ORDER BY `" . $block_params["seo"]["primary_table"] . "`.`" . $block_params["field"]["last_update"] . "` DESC";
								$db->query($sSQL);
								if($db->nextRecord()) {
									$block_count = $db->numRows();
									if($block_count) {
										$sitemap[$block_target] = array(
											"url" => "/sitemap-" . $block_target . "." . $ext
											, "lastmod" =>  date(DATE_ATOM, (is_file(FF_DISK_PATH . $base_path . "" . "/" . $block_target . "." . $ext) 
														? filemtime(FF_DISK_PATH . $base_path . "" . "/" . $block_target . "." . $ext) 
														: ($module_lastmod 
															? $module_lastmod
															: ($db->getField("last_update", "Number", true) > 0
																? $db->getField("last_update", "Number", true)
																: time()
															)
														)
													))
											, "count" => $block_count
										);
										
										$block_page = floor($block_count / SITEMAP_LIMIT);
										if($block_page) {
											for($i = 2; $i <= $block_page + 1; $i++) {
												$sitemap[$block_target . "-" . $i] = $sitemap[$block_target];
												$sitemap[$block_target . "-" . $i]["url"] = "/sitemap-" . $block_target . "-" . $i . "." . $ext;
											
											}
										}									
									}
								}
							}
						}
					}
				}
			}
		}

		if(is_array($locale["lang"]) && count($locale["lang"]) > 1) {
			foreach($locale["lang"] AS $code => $lang) {
				$sitemap_index[$code] = $sitemap;
				if($lang["prefix"]) {
					foreach($sitemap_index[$code] AS $block_name => $page) {
						if($page["mono_lang"])
							unset($sitemap_index[$code][$block_name]);
						else
							$sitemap_index[$code][$block_name]["url"] = $lang["prefix"] . $page["url"];
					}
				}
			}
		} else {
			$sitemap_index[LANGUAGE_DEFAULT] = $sitemap;
		}

		if($template) {
			$tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/contents");
			$tpl->load_file("siteindex." . $ext, "main");
			$tpl->set_var("http_protocol", "http" . ($_SERVER["HTTPS"] ? "s" : "")); 
			$tpl->set_var("domain", DOMAIN_INSET);
			if(is_array($sitemap_index) && count($sitemap_index)) {
				foreach($sitemap_index AS $lang_code => $sitemap) {
					foreach($sitemap AS $page) {
						$tpl->set_var("url"			, htmlentities($page["url"]));
						$tpl->set_var("last_update"	, $page["lastmod"]);
						$tpl->set_var("frequency"	, $page["frequency"]);
						$tpl->set_var("priority"	, $page["priority"]);
						$tpl->parse("SezPage", true);
					}
				}
			}	

			$buffer = $tpl->rpparse("main", false);
		} elseif($template === false) {
			$buffer = json_encode($sitemap_index);
		}

		if(count($sitemap_index)) {
		    $expires = time() + (60 * 60 * 24 * 1);

			cm_filecache_write(FF_DISK_PATH . ffCommon_dirname($file), basename($file), $buffer, $expires);
		} else {
			$error = true;
		}
	}

	return array(
		"content" => $buffer
		, "mime" => $mime
		, "error" => $error
	);
}


function system_get_sitemap($target, $ext = "xml", $user_path = null, $lang = null, $user = null) {
	$cm = cm::getInstance();

	$base_path = "/cache/sitemap";

	$date_type 				= "Y-m-d H:i";	
	$default_limit			= SITEMAP_LIMIT;
	switch($ext) {
		case "html":
			$template 		= "sitemap.html";
			$mime 			= ffMedia::getMimeTypeByExtension($ext);
			break;
		case "rss":
		case "mrss":
			$date_type 		= "D, d M Y H:i:s O";
			$template 		= "siterss2.xml";
			//$mime 			= ffMedia::getMimeTypeByExtension($ext);
            $mime 			= ffMedia::getMimeTypeByExtension("xml");
            $default_limit	= FEED_LIMIT;
            
            $skip_dir 		= true;
			break;
		case "json":
			$template 		= false;
			$mime 			= ffMedia::getMimeTypeByExtension($ext);
			break;
		case "xml":
			$date_type 		= DATE_ATOM;
			$template 		= "sitemap.xml";
			$mime 			= ffMedia::getMimeTypeByExtension($ext);
			break;
		default:	
			return false;

	}
	
	$file = $base_path . $user_path . "/" . ($target ? $target : "index") . "." . $ext;

	if(0 && is_file(FF_DISK_PATH . $file) && filemtime(FF_DISK_PATH . $file) > filectime(FF_DISK_PATH . $file)) {
		$buffer = file_get_contents(FF_DISK_PATH . $file);
	} else {
		$db = ffDB_Sql::factory();
		check_function("get_schema_fields_by_type");

		$count  = (isset($_REQUEST["count"])
                    ? $_REQUEST["count"]
                    : $default_limit
                );

		$offset = ($_REQUEST["page"] > 1
                    ? ($_REQUEST["page"]-1) * $count
                    : 0
                );

		$arrTarget = explode("-", $target);
		$page = array_pop($arrTarget);
		if(is_numeric($page) && $page > 1) {
			$target = implode("-", $arrTarget);
			$offset = ($page - 1) * $count;
		}
		
	    $arrFrequency = array("always" 		=> 10
	                            , "hourly" 	=> 9
	                            , "daily" 	=> 8
	                            , "weekly" 	=> 7
	                            , "monthly" => 6
	                            , "yearly" 	=> 5
	                            , "never" 	=> 4
	                        );

	                        
		if(check_function("get_locale"))
	    	$locale = get_locale();

	    if($user_path) {
	    	$arrPath = explode("/", trim($user_path, "/"));
            
            $link = "/" . $arrPath[0];
            if(!$lang && $locale["rev"]["lang"][$arrPath[0]]) {
                $lang 				= $locale["rev"]["lang"][$arrPath[0]];
                $ID_lang 			= $locale["lang"][$lang]["ID"];

	            unset($arrPath[0]);
                $link = "/" . $arrPath[1];
			}
            
			$user_path = "/" . implode("/", $arrPath);
		}
		
		if(!$lang) {
			$lang 					= LANGUAGE_DEFAULT;
			$ID_lang 				= LANGUAGE_DEFAULT_ID;
		}	                        

	   // $link 						=  $user_path;
	    if($target == "base") {
			$schema 				= get_schema_fields_by_type("page");
		} elseif($target == "anagraph") {
	    	$schema 				= get_schema_fields_by_type("anagraph");
		} elseif($target) {
			$link 					= "/" . $target;
			$user_path 				= "";
		}

		if(!$schema)
			$schema 				= get_schema_fields_by_type(/*$user_path .*/ $link, "vgallery");
        
        $q      = (isset($_REQUEST["q"])
                    ? $_REQUEST["q"]
                    : null
                );

        $dir    = (isset($_REQUEST["dir"]) && $_REQUEST["dir"] == "asc"
                    ? $_REQUEST["dir"]
                    : "desc"
                ); 
        $sort   = (isset($_REQUEST["sort"]) && $schema["field"][$_REQUEST["sort"]]
                    ? $schema["field"][$_REQUEST["sort"]]
                    : ($schema["field"]["published_at"]
                    	? (strpos($schema["field"]["published_at"], " AS") === false
                    		? $schema["field"]["published_at"]
                    		: substr($schema["field"]["published_at"], 0, strpos($schema["field"]["published_at"], " AS"))
                    	) . " " . $dir . ", "
                    	: ""
                    ) .
                    ($schema["field"]["last_update"]
                    	? (strpos($schema["field"]["last_update"], " AS") === false
                    		? $schema["field"]["last_update"]
                    		: substr($schema["field"]["last_update"], 0, strpos($schema["field"]["last_update"], " AS"))
                    	)
                    	: "ID"
                    )
                );

        if($lang != LANGUAGE_DEFAULT && (!is_array($schema["settings"]) || $schema["settings"]["enable_multilang"] !== false))
            $prefix 			= $locale["lang"][$lang]["prefix"];

		$channel = array(
			"self" => trim($_SERVER["REQUEST_URI"], "&")
			, "locale" => $locale["lang"]["current"]["tiny_code"] . ($locale["lang"]["current"]["country"] ? "-" . $locale["lang"]["current"]["country"] : "")
			, "webmaster" => A_FROM_EMAIL . " (" . A_FROM_NAME . ")"
			, "target" => $prefix . ($user_path ? $user_path : $link)
			, "type" => array(
				"rss" => 'xmlns:atom="http://www.w3.org/2005/Atom"'
				, "mrss" => 'xmlns:media="http://search.yahoo.com/mrss/"'
			)
			, "category" => null /* array(
										"url" => "[url di approdo]"
										, "name" => "[nome della categoria]"
									)*/
			, "image" => null /* array(
										"url" => "[src dell'immagine]"
										, "title" => "[alt dell'immagine]"
										, "link" => "[url di approdo]"
										, "description" => "[description dell'immagine]"
										, "width" => "[width dell'immagine]"
										, "height" => "[height dell'immagine]"
									)*/
			, "copyright" => null /* "[copyright applicabile]" */
			, "doc" => null /* "[url documentazione]" */
		);        
        $res = $cm->doEvent("vg_sitemap_on_before_process_channel", array($user_path, $channel));
        $rc = end($res);
		if ($rc)
		{
			$channel = $rc;
		}          
        
        if($user_path) {
			$arrLimitPath[] = (strpos($schema["seo"]["permalink"], " AS") === false
		    					? "`" . ($lang != LANGUAGE_DEFAULT
									? $schema["seo"]["table"]
									: $schema["seo"]["primary_table"]
								)
								. "`.`" . $schema["seo"]["permalink"] . "`"
		    					: substr($schema["seo"]["permalink"], 0, strpos($schema["seo"]["permalink"], " AS"))
		    				) . " LIKE " . $db->toSql($user_path . "/%");
			//$arrLimitPath[] = "`" . $schema["seo"]["permalink"] . "` = " . $db->toSql($user_path);
            if(!$channel["title"] || !$channel["description"]) {
				$sSQL = "SELECT `" . ($lang != LANGUAGE_DEFAULT
									? $schema["seo"]["table"]
									: $schema["seo"]["primary_table"]
								)
								. "`.`" . $schema["seo"]["title"] . "` 		AS title
                            , `" . ($lang != LANGUAGE_DEFAULT
									? $schema["seo"]["table"]
									: $schema["seo"]["primary_table"]
								)
								. "`.`" . $schema["seo"]["description"] . "` AS description
                            , " . (strpos($schema["field"]["last_update"], " AS") === false
                                ? "`" . $schema["seo"]["primary_table"] . "`.`" . $schema["field"]["last_update"] . "` AS last_update"
                                : $schema["field"]["last_update"]
                            ) . "
                            , " . ($schema["field"]["published_at"]
                                    ? (strpos($schema["field"]["published_at"], " AS") === false
                                        ? "`" . $schema["seo"]["primary_table"] . "`.`" . $schema["field"]["published_at"] . "` AS published_at"
                                        : $schema["field"]["published_at"]
                                    )
                                    : "'' AS published_at"
                            ) . "						
                        FROM " . ($lang == LANGUAGE_DEFAULT
                                ? "`" . $schema["seo"]["primary_table"] . "`"
                                : "`" . $schema["seo"]["table"] . "`"
                            )
							. ($lang != LANGUAGE_DEFAULT && $schema["seo"]["table"] != $schema["seo"]["primary_table"]
								? " INNER JOIN `" . $schema["seo"]["primary_table"] . "` ON `" . $schema["seo"]["primary_table"] . "`.ID = `" . $schema["seo"]["table"] . "`.`" . $schema["seo"]["rel_key"] . "`"
								: ""
							) . " 
                        WHERE " . (strpos($schema["seo"]["permalink"], " AS") === false
									? "`" . ($lang != LANGUAGE_DEFAULT
										? $schema["seo"]["table"]
										: $schema["seo"]["primary_table"]
									) . "`.`" . $schema["field"]["permalink"] . "`"
									: substr($schema["seo"]["permalink"], 0, strpos($schema["seo"]["permalink"], " AS"))
                                ) . " = " . $db->toSql($user_path);
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $channel["publishedat"]         = $db->getField("published_at", "Number", true);
                    $channel["lastmod"]             = $db->getField("last_update", "Number", true);
                    $channel["title"]               = $db->getField("title", "Text", true);	
                    $channel["description"]         = $db->getField("description", "Text", true);	
                }
            }
		}

        $alias = cache_get_settings("alias");
		//strippa il path di base per la cache
		if(is_array($alias) && count($alias)) {
			foreach($alias AS $alias_domain => $alias_path) {
				if($alias_domain == $_SERVER["HTTP_HOST"]) {
					$strip_user_path = $alias_path;
					continue;
				}

				$arrExcludePath[] = (strpos($schema["field"]["permalink"], " AS") === false
		    							? "`" . ($lang != LANGUAGE_DEFAULT
												? $schema["seo"]["table"]
												: $schema["seo"]["primary_table"]
											)
											. "`.`" . $schema["field"]["permalink"] . "`"
		    							: substr($schema["field"]["permalink"], 0, strpos($schema["field"]["permalink"], " AS"))
		    						) . " NOT LIKE " . $db->toSql($alias_path . "/%");
				$arrExcludePath[] = (strpos($schema["field"]["permalink"], " AS") === false
		    							? "`" . ($lang != LANGUAGE_DEFAULT
												? $schema["seo"]["table"]
												: $schema["seo"]["primary_table"]
											)
											. "`.`" . $schema["field"]["permalink"] . "`"
		    							: substr($schema["field"]["permalink"], 0, strpos($schema["field"]["permalink"], " AS"))
		    						) . " <> " . $db->toSql($alias_path);
			}
		}

       // if($schema) {
		$field_request 			= false;
		$field_frequency 		= false;

			    	
	    	
		$sitemap["name"] 				= trim($link, "/");
		$sitemap["channel"] 			= $channel;
		$sitemap["table"] 				= ($schema["seo"]["primary_table"] 
											? $schema["seo"]["primary_table"] 
											: "cache_page"
										);

		$sSQL = "SELECT DISTINCT 
		    		" . $schema["seo"]["primary_table"] . ".ID AS ID
		    		, " . (strpos($schema["seo"]["permalink"], " AS") === false
		    			? "`" . ($lang != LANGUAGE_DEFAULT
							? $schema["seo"]["table"]
							: $schema["seo"]["primary_table"]
						) . "`.`" . $schema["seo"]["permalink"] . "` AS permalink"
		    			: $schema["seo"]["permalink"]
		    		) . "
		    		, " . (strpos($schema["field"]["last_update"], " AS") === false
		    			? "`" . $schema["seo"]["primary_table"] . "`.`" . $schema["field"]["last_update"] . "` AS last_update"
		    			: $schema["field"]["last_update"]
		    		) . "
		    		, " . ($schema["field"]["published_at"]
		    				? (strpos($schema["field"]["published_at"], " AS") === false
		    					? "`" . $schema["seo"]["primary_table"] . "`.`" . $schema["field"]["published_at"] . "` AS published_at"
		    					: $schema["field"]["published_at"]
		    				)
		    				: "'' AS published_at"
		    		) . "
					, " . (strpos($schema["seo"]["title"], " AS") === false
		    			? "`" . ($lang != LANGUAGE_DEFAULT
							? $schema["seo"]["table"]
							: $schema["seo"]["primary_table"]
						) . "`.`" . $schema["seo"]["title"] . "` AS title"
		    			: $schema["seo"]["title"]
		    		) . "		    				
					, " . (strpos($schema["seo"]["description"], " AS") === false
		    			? "`" . ($lang != LANGUAGE_DEFAULT
							? $schema["seo"]["table"]
							: $schema["seo"]["primary_table"]
						) . "`.`" . $schema["seo"]["description"] . "` AS description"
		    			: $schema["seo"]["description"]
		    		) . "		    				
				FROM " . ($lang != LANGUAGE_DEFAULT
			        ?  "`" . $schema["seo"]["table"] . "`"
			        :  "`" . $schema["seo"]["primary_table"] . "`"
				)
				. ($lang != LANGUAGE_DEFAULT && $schema["seo"]["table"] != $schema["seo"]["primary_table"]
			        ? " INNER JOIN `" . $schema["seo"]["primary_table"] . "` ON `" . $schema["seo"]["primary_table"] . "`.ID = `" . $schema["seo"]["table"] . "`.`" . $schema["seo"]["rel_key"] . "`"
			        : ""
			    )
				. ($schema["field"]["ID_category"]
			        ? " INNER JOIN `" . $schema["type"] . "` ON `" . $schema["type"] . "`.ID = " . "`" . $schema["seo"]["primary_table"] . "`.`" . $schema["field"]["ID_category"] . "`"
			        	. ($target
			        		? "AND  `" . $schema["type"] . "`.`name` = " . $db->toSql($target)
			        		: ""
			        	)
			        : ""
				)
				. "
				WHERE " . (strpos($schema["field"]["permalink"], " AS") === false
		    				? "`" . ($lang != LANGUAGE_DEFAULT
									? $schema["seo"]["table"]
									: $schema["seo"]["primary_table"]
								)
								. "`.`" . $schema["field"]["permalink"] . "`"
		    				: substr($schema["field"]["permalink"], 0, strpos($schema["field"]["permalink"], " AS"))
		    			) . " <> '' "
                    . ($schema["field"]["robots"]
			        	? " AND `" . ($lang != LANGUAGE_DEFAULT
								? $schema["seo"]["table"]
								: $schema["seo"]["primary_table"]
							) . "`.`" . $schema["field"]["robots"] . "` NOT LIKE '%noindex%'"
			        	: ""
			        )
			        . ($schema["field"]["visible"]
			        	? " AND `" . ($lang != LANGUAGE_DEFAULT
								? $schema["seo"]["table"]
								: $schema["seo"]["primary_table"]
							) . "`.`" . $schema["field"]["visible"] . "` = '1'"
			        	: ""
			        )
			        . ($skip_dir && $schema["field"]["is_dir"]
			        	? "AND `" . $schema["seo"]["primary_table"] . "`.`" . $schema["field"]["is_dir"] . "` = '0'"
			        	: ""
					)
			        . ($lang != LANGUAGE_DEFAULT
			        	? " AND `" . $schema["seo"]["rel_lang"] . "` = " . $db->toSql($ID_lang, "Number")
			        	: ""
			        )
			        . ($arrLimitPath
			        	? " AND (" . implode(" OR ", $arrLimitPath) . ")"
			        	: ""
			        )
			        . ($arrExcludePath
			        	? " AND (" . implode(" AND ", $arrExcludePath) . ")"
			        	: ""
			        )
                    . ($q
                        ? " AND " . (strpos($schema["seo"]["title"], " AS") === false
                            ? "`" . ($lang != LANGUAGE_DEFAULT
                                ? $schema["seo"]["table"]
                                : $schema["seo"]["primary_table"]
                            ) . "`.`" . $schema["seo"]["title"] . "`"
                            : $schema["seo"]["title"]
                        ) . " LIKE " . $db->toSql("%" . str_replace(" ", "%", $q) . "%")
                        . " AND " . (strpos($schema["seo"]["description"], " AS") === false
                            ? "`" . ($lang != LANGUAGE_DEFAULT
                                ? $schema["seo"]["table"]
                                : $schema["seo"]["primary_table"]
                            ) . "`.`" . $schema["seo"]["description"] . "`"
                            : $schema["seo"]["description"]
                        ) . " LIKE " . $db->toSql("%" . str_replace(" ", "%", $q) . "%")
                        : ""
                    ) . "
				ORDER BY 
					" . ($sort
                        ? $sort . " " . $dir . ", "
                        : ""
                    )
                    . ($lang != LANGUAGE_DEFAULT
						? 	"`" . $schema["seo"]["table"] . "`.`ID`"
						: 	"`" . $schema["seo"]["primary_table"] . "`.`ID`"
					) .  "
				LIMIT " . $offset . ", " . $count;
		$db->query($sSQL);
        if($db->nextRecord()) {
			do {
				$permalink = $db->getField("permalink", "Text", true);
				if($strip_user_path) {
					if($permalink == $strip_user_path)
						$permalink = "";
					elseif(strpos($permalink, $strip_user_path) === 0) {
						$permalink = substr($permalink, strlen($strip_user_path));
					}				
				}

				if(strpos($permalink, "/") !== 0)	
					continue;

				if($permalink == "/")
					$permalink = "";

				if($field_request)
					$request = $db->getField("request", "Text", true);
				
				if($request)				
					$permalink .= "?" . $request;
				
				$last_update = ($db->getField("published_at", "Number", true)
					? ($db->getField("published_at", "Number", true) /*+ 3600*2*/)
					: $db->getField("last_update", "Number", true)
				);
				if(!$last_update)
					$last_update = time();

				if($field_frequency)
					$frequancy = $db->getField("frequency", "Text", true);

				$page = array(
					"ID"					=> $db->getField("ID", "Number", true)
					, "prefix"				=> $prefix
					, "permalink"			=> $permalink
					, "url" 				=> $prefix . $permalink
					, "lastmod" 			=>  date($date_type, $last_update)
					, "frequency" 			=> $frequancy
					, "title"				=> strip_tags(str_ireplace(array("<br />","<br>","<br/>"), "\r\n", $db->getField("title", "Text", true)))
					, "description"			=> str_ireplace(array("<br />","<br>","<br/>"), "\r\n", $db->getField("description", "Text", true))
					, "priority" 			=> round((100 * (pow(0.8, substr_count($permalink, "/")))) / 100, 2)
					, "cover"				=> null
					, "author"				=> null
					, "source"				=> null
				);		
				
    			$res = $cm->doEvent("vg_sitemap_on_load_data", array(&$page, &$channel));
		       // $res = end($res);		
				$sitemap["pages"][$prefix . $permalink] = $page;
				$sitemap["keys"][$page["ID"]] = $prefix . $permalink;
			} while($db->nextRecord());
		}

    	$res = $cm->doEvent("vg_sitemap_on_before_process", array(&$sitemap));
		//$res = end($res);		

        if($template 
            && (is_array($sitemap["pages"]) && count($sitemap["pages"])
                    || ($channel["title"] && $channel["description"])
                )
        ) {
            if(!$channel["title"])
                $channel["title"] = $cm->oPage->title;

            if(!$channel["title"])
                $channel["title"] = ucwords(str_replace("-", " ", basename($user_path)));

            if(!$channel["description"])
                $channel["description"] = $channel["title"];   

			$tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/contents");
			$tpl->load_file($template, "main");
			$tpl->set_var("http_protocol"					, "http" . ($_SERVER["HTTPS"] ? "s" : "")); 
			$tpl->set_var("domain"							, DOMAIN_INSET);		

			if(is_array($sitemap["pages"]) && count($sitemap["pages"])) {
				foreach($sitemap["pages"] AS $page) {
					$tpl->set_var("url"						, htmlentities($page["url"]));
					$tpl->set_var("last_update"				, $page["lastmod"]);
					$tpl->set_var("priority"				, $page["priority"]);
					if($page["frequency"]) {
						$tpl->set_var("frequency"			, $page["frequency"]);
						$tpl->parse("SezFrequency", false);
					}
                    if($page["title"]) {
                        $tpl->set_var("title"				, html_entity_decode($page["title"]));
                        $tpl->parse("SezTitle"              , false);
                    }
                    if($page["description"]) {
                        $tpl->set_var("description"			, ffCommon_specialchars($page["description"]));
                        $tpl->parse("SezDescription"        , false);
                    }

                    if($page["cover"]) {
						if($ext == "rss") {
							$tpl->set_var("cover_tag"		, '<img src="' . $page["cover"] . '" />');
						} elseif($ext == "mrss") {
						
							$tpl->set_var("cover_mime"		, ffMedia::getMimeTypeByFilename($page["cover"], "image/jpeg"));
							$tpl->set_var("cover_url"		, $page["cover"]);
							$tpl->parse("SezCover", false);
						}
					}

					if($page["author"]) {
						$tpl->set_var("author_email"		, $page["author"]["email"]);
						$tpl->parse("SezAuthor", false);
					}
					if($page["source"]) {
						$tpl->set_var("source_name"			, $page["source"]["name"]);
						$tpl->set_var("source_url"			, $page["source"]["url"]);
						$tpl->parse("SezSource", false);
					}

					$tpl->parse("SezPage", true);
					$tpl->set_var("SezFrequency", "");
					$tpl->set_var("SezTitle", "");
					$tpl->set_var("SezCover", "");
					$tpl->set_var("SezDescription", "");
					$tpl->set_var("SezAuthor", "");
					$tpl->set_var("SezSource", "");
				}
			}			
			
			$tpl->set_var("self"							, ffCommon_specialchars($sitemap["channel"]["self"]));
			$tpl->set_var("locale"							, $sitemap["channel"]["locale"]);		
			if($sitemap["channel"]["lastmod"] >= time()) {
                $tpl->set_var("last_update"					, date($date_type, $sitemap["channel"]["lastmod"]));
                $tpl->parse("SezLastUpdate"                 , false);
            }
            if($sitemap["channel"]["publishedat"] >= time()) {
                $tpl->set_var("published_at"                , date($date_type, $sitemap["channel"]["publishedat"]));
                $tpl->parse("SezPublishedAt"                , false);
            }
			$tpl->set_var("webmaster"						, $sitemap["channel"]["webmaster"]);
			$tpl->set_var("target"							, $sitemap["channel"]["target"]);
			
			$tpl->set_var("channel_title"					, ffCommon_specialchars($sitemap["channel"]["title"]));
            $tpl->set_var("channel_description"				, $sitemap["channel"]["description"]);

			$tpl->set_var("rss_type"						, $sitemap["channel"]["type"][$ext]);
			if($ext == "rss") {
				$tpl->parse("SezAtom", false);
			}

			if($sitemap["channel"]["category"]) {
				$tpl->set_var("category_url", $sitemap["channel"]["category"]["url"]);
				$tpl->set_var("category_name", $sitemap["channel"]["category"]["name"]);
				$tpl->parse("SezCategory", false);
			}
			
			if(is_array($sitemap["channel"]["image"]) && count($sitemap["channel"]["image"])) {
				foreach($sitemap["channel"]["image"] AS $image_key => $image_value) {
					$tpl->set_var("image_properties", '<' . $image_key . '>' . $image_value . '</' . $image_key . '>');
					$tpl->parse("SezImageProperties", true);
				
				}
				$tpl->parse("SezImage", false);
			}
			
			if($sitemap["channel"]["copyright"]) {
				$tpl->set_var("copyright", $sitemap["channel"]["copyright"]);
				$tpl->parse("SezCopyright", false);
			}
			
			if($sitemap["channel"]["doc"]) {
				$tpl->set_var("doc_url", $sitemap["channel"]["doc"]);
				$tpl->parse("SezDoc", false);
			}

			$buffer = $tpl->rpparse("main", false);
		} elseif($template === false) {
			$buffer = json_encode($sitemap);
		}
		
		if(count($sitemap)) {
		    $expires = time() + (60 * 60 * 24 * 1);

			cm_filecache_write(FF_DISK_PATH . ffCommon_dirname($file), basename($file), $buffer, $expires);
		} else {
			$error = true;
		}		
	}
	
	return array(
		"content" => $buffer
		, "mime" => $mime
		, "error" => $error
	);
}

function system_check_sitemap($target) {
	$alias = cache_get_settings("alias");
	//strippa il path di base per la cache
	if(is_array($alias) && count($alias)) {
		foreach($alias AS $alias_domain => $alias_path) {
			if($alias_domain != $_SERVER["HTTP_HOST"]) {
				$arrExcludePath[$alias_path . "/" . $target] = $alias_domain;
			}
		}
	}
	
	if(is_array($arrExcludePath) && count($arrExcludePath)) {
		$ff_modules = glob(FF_DISK_PATH . "/modules/*");
		if(is_array($ff_modules) && count($ff_modules)) {
			foreach($ff_modules AS $real_module_dir) {
				$module_name = basename($real_module_dir);

				if(is_file(FF_DISK_PATH . "/modules/" . $module_name . "/conf/schema." . FF_PHP_EXT)) {
					require FF_DISK_PATH . "/modules/" . $module_name . "/conf/schema." . FF_PHP_EXT;
					
					foreach($arrExcludePath AS $exclude_path => $domain_target) {
						if(isset($schema[$exclude_path])) {
							return $domain_target;
						}
					}
				}
			}		
		}
	}
}