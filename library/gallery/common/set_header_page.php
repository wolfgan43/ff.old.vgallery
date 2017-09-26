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
	function set_header_page($page_title = null, $meta_description = null, $meta_keywords = null, $override_meta = null, $canonical = null) {
		$globals = ffGlobals::getInstance("gallery");
		$cm = cm::getInstance();

/***
* TODO
* implementare le stopword
* 
* implementare autocompilazione meta keywords (max 5)
* 
* implementare social tag google+ e twitter
* 
* implementare i microdata 
* 
* http://code.lancepollard.com/complete-list-of-html-meta-tags/
* 
* <link href="/myid123/jsonld.js" rel="alternate" type="application/ld+json" />
*
*/
        if(!$globals->seo["current"] && is_array($globals->seo) && count($globals->seo)) {
            if(isset($globals->seo["user"]))
                $globals->seo["current"] = "user";
            elseif(isset($globals->seo["detail"]))
                $globals->seo["current"] = "detail";
            elseif(isset($globals->seo["detail-anagraph"]))
                $globals->seo["current"] = "detail-anagraph";
            elseif(isset($globals->seo["thumb"]))
                $globals->seo["current"] = "thumb";
            elseif(isset($globals->seo["thumb-anagraph"]))
                $globals->seo["current"] = "thumb-anagraph";
            elseif(isset($globals->seo["page"]))
                $globals->seo["current"] = "page";
            elseif(isset($globals->seo["media"]))
                $globals->seo["current"] = "media";
            elseif(isset($globals->seo["tag"]))
                $globals->seo["current"] = "tag";
            elseif(isset($globals->seo["city"]))
                $globals->seo["current"] = "city";
            elseif(isset($globals->seo["province"]))
                $globals->seo["current"] = "province";
            elseif(isset($globals->seo["region"]))
                $globals->seo["current"] = "region";
            elseif(isset($globals->seo["state"]))
                $globals->seo["current"] = "state";
        }

        $seo = $globals->seo[$globals->seo["current"]];

        if($globals->seo["current"] == "user") {
            if (isset($globals->seo["detail"]))
                $seo = array_replace_recursive($globals->seo["detail"], $seo);
            elseif (isset($globals->seo["detail-anagraph"]))
                $seo = array_replace_recursive($globals->seo["detail-anagraph"], $seo);

            if($seo["description"])
                $globals->meta["description"][] = $seo["description"];
        }

		if(is_array($seo["meta"]) && count($seo["meta"]))
			if(is_array($globals->meta) && !count($globals->meta))
				$globals->meta = array_replace($globals->meta, $seo["meta"]);
			else
				$globals->meta = $seo["meta"];

		$owner = null; //da implementare
		$domain_abs_path = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . FF_SITE_PATH;
/*
		$domain_path = "http" . ($_SERVER["HTTPS"] ? "s": "");
		if(strpos(CM_SHOWFILES, "://") !== false) {
            $domain_path = "";
        } else {
            $domain_path .= "://" . DOMAIN_INSET . FF_SITE_PATH;
        }
*/
        $site_name = (CM_LOCAL_APP_NAME 
        				? CM_LOCAL_APP_NAME 
        				: $_SERVER["HTTP_HOST"]
        			);
        					
        //HTML Attr
		$globals->html["attr"] = array(
			"lang" => strtolower(substr(LANGUAGE_INSET, 0, 2))
			/*, "xmlns:fb" => "http://www.facebook.com/2008/fbml"*/
			, "prefix" => "og: " . "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . "ogp.me/ns# fb: " . "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . "www.facebook.com/2008/fbml"
		);

		//favicon
		if($globals->favicon) {
			if(is_array($globals->favicon) && count($globals->favicon)) {
				foreach($globals->favicon AS $favicon_rel => $favicon) {
					if(is_array($favicon)) {
						foreach($favicon AS $favicon_file) {
							$cm->oPage->tplAddTag("link", array(
								"href" => $favicon_file
								, "type" => ffMimeTypeByFilename($favicon_file)
								, "rel" => $favicon_rel
							)); 
						}
					} else {
						$cm->oPage->tplAddTag("link", array(
							"href" => $favicon
							, "type" => ffMimeTypeByFilename($favicon)
							, "rel" => $favicon_rel
						)); 
					}
				}
			} else {
				$cm->oPage->tplAddTag("favicon", array(
					"href" => $globals->favicon
					, "type" => ffMimeTypeByFilename($globals->favicon)
				)); 
			}
		} elseif(file_exists(FF_DISK_PATH . "/favicon.ico")) {
				$cm->oPage->tplAddTag("favicon", array(
					"href" => "/favicon.ico"
					, "type" => "image/ico"
				)); 
	    } elseif(file_exists(FF_DISK_PATH . "/favicon.png")) {
				$cm->oPage->tplAddTag("favicon", array(
					"href" => "/favicon.png"
					, "type" => "image/png"
				)); 
	    } elseif(file_exists(FF_DISK_PATH . "/favicon.gif")) {
			$cm->oPage->tplAddTag("favicon", array(
				"href" => "/favicon.gif"
				, "type" => "image/x-icon"
			));
		}

		//Page Title
		if(strlen($page_title))	{	
			$globals->page_title = preg_replace('/(\r|\n|\")/', "", $page_title) . ffTemplate::_get_word_by_code("separator_meta_title") . CM_LOCAL_APP_NAME;
		} elseif($page_title !== false && !strlen($globals->page_title)) {
			if($seo["title"]) {
				$globals->page_title = $seo["title"];
			} elseif($globals->page["user_path"] == "/") {
				$globals->page_title = CM_LOCAL_APP_NAME;
			} else {
		 		$arrUser_path = explode("/", substr($globals->page["user_path"], 1));
			    krsort($arrUser_path);

			    $globals->page_title = ucwords(implode(ffTemplate::_get_word_by_code("separator_meta_title"), $arrUser_path));
			}
		}  		
		
		//Meta Application
        if(!$globals->meta["application-name"])
            $globals->meta["application-name"] = $globals->page_title;		
        if(!$globals->meta["apple-mobile-web-app-title"])
        	$globals->meta["apple-mobile-web-app-title"] = $globals->page_title;

        //Meta Author
        if(!$globals->meta["author"] && $owner)
            $globals->meta["author"] = "owner";		
		
        //Meta Viewport
        if(!$globals->meta["viewport"])
            $globals->meta["viewport"] = "width=device-width, height=device-height, initial-scale=1.0, user-scalable=0";

        if(http_response_code() == 200) {
            //Meta Robots
            if (!$globals->meta["robots"]) {
                $globals->meta["robots"] = "index, follow";
                if ($globals->navigation["page"] > 0 /*&& $globals->navigation["tot_page"] >= $globals->navigation["page"]*/)
                    $globals->meta["robots"] = "noindex, follow";
            }

            if ($globals->navigation["tot_page"] && $globals->navigation["page"] > 1) {
                $cm->oPage->tplAddTag("prev", array(
                    "href" => ffUpdateQueryString("page", ($globals->navigation["page"] > 2 ? $globals->navigation["page"] - 1 : false), $cm->oPage->canonical)
                ));
            }
            if ($globals->navigation["page"] && $globals->navigation["tot_page"] > $globals->navigation["page"]) {
                $cm->oPage->tplAddTag("next", array(
                    "href" => ffUpdateQueryString("page", ($globals->navigation["page"] + 1), $cm->oPage->canonical)
                ));
            }

            //canonical Url
            if (strpos($globals->meta["robots"], "noindex") !== false) {
                $cm->oPage->canonical = null;
            } else {
                if (strlen($canonical)) {
                    if (strpos($canonical, "://") !== false) {
                        $cm->oPage->canonical = $canonical;
                    } else {
                        $cm->oPage->canonical = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $canonical;
                    }

                    $globals->canonical = $canonical;
                } elseif ($globals->canonical) {
                    $cm->oPage->canonical = $globals->canonical;
                } elseif ($canonical !== false && !$cm->oPage->canonical) {
                    $cm->oPage->canonical = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . $globals->page["strip_path"] . $globals->user_path . $globals->user_path_params;
                }
            }

            //RSS
            if (!$globals->css["atom"]) {
                if ($globals->seo["current"] == "detail") {
                    $cm->oPage->tplAddTag("alternate", array(
                        "href" => "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . ffCommon_dirname($globals->page["strip_path"] . $globals->page["user_path"]) . "/feed/"
                    , "type" => "application/rss+xml"
                    ));
                }
                if ($globals->seo["current"] == "thumb" || $globals->seo["current"] == "detail") {
                    $cm->oPage->tplAddTag("alternate", array(
                        "href" => "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . $globals->page["strip_path"] . $globals->page["user_path"] . "/feed/"
                    , "type" => "application/rss+xml"
                    ));
                }
            }

            /*
                    if(!$globals->css["rss"]) {
                        $cm->oPage->tplAddTag("alternate", array(
                            "href" => "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . $globals->page["strip_path"] . $globals->page["user_path"] . "/feed.mrss"
                            , "type" => "application/rss+xml"
                        ));
                    }
            */

            //HrefLang
            if (is_array($globals->seo["altlang"]) && count($globals->seo["altlang"])) {
                foreach ($globals->seo["altlang"] AS $tiny_code => $path) {
                    $cm->oPage->tplAddTag("alternate", array(
                        "href" => $path
                    , "hreflang" => $tiny_code
                    ));
                }
            }

            //AMP Page
            if (!$globals->css["amp"] && 0) {
                $cm->oPage->tplAddTag("link", array(
                    "href" => "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET . $globals->page["strip_path"] . $globals->page["user_path"] . "/amp"
                , "rel" => "amphtml"
                ));
            }

            //Manifest
            if ($globals->manifest) {
                $cm->oPage->tplAddTag("link", array(
                    "href" => "/manifest.json"
                , "rel" => "manifest"
                ));
            }
        } else {
            $globals->meta["robots"] = "noindex, nofollow";
        }
		//<link rel="amphtml" href="https://www.example.com/url/to/amp/document.html">

		
		//Meta Description
		if(is_array($meta_description)) {
			if(is_array($globals->meta["description"])) {
				$globals->meta["description"] = array_merge($globals->meta["description"], $meta_description);
			} else {
				$globals->meta["description"] = $meta_description;
			}
		} elseif(strlen($meta_description)) {
			$globals->meta["description"][] = preg_replace('/(\r|\n|\")/', " ", strip_tags($meta_description));
			//$cm->oPage->tplAddMeta("description", preg_replace('/(\r|\n|\")/', " ", strip_tags($meta_description)));
		} elseif($meta_description !== false && !$globals->meta["description"]) {
			$globals->meta["description"][] = $globals->page_title;
		}
		
		//Meta Keywords
		if($meta_keywords !== null) {
			if(is_array($meta_keywords)) {
				if(count($meta_keywords)) {
					foreach($meta_keywords AS $keyword_key => $keyword_value) {
						if(strpos($keyword_value, ",") === false) { //#*#*#";
							$globals->meta["keywords"][] = preg_replace('/(\r|\n|\")/', " ", $keyword_value);
						} else {
							$arrTmpKeyword = explode(",", $keyword_value);  //#*#*#";
							foreach($arrTmpKeyword AS $arrTmpKeyword_value) {
								if(strlen($arrTmpKeyword_value)) {
									$globals->meta["keywords"][] = preg_replace('/(\r|\n|\")/', " ", $arrTmpKeyword_value);
								}
							}
						}
					}
				}
			} elseif(strlen($meta_keywords)) {
				if(strpos($meta_keywords, ",") === false) {  //#*#*#";
					$globals->meta["keywords"][] = preg_replace('/(\r|\n|\")/', " ", $meta_keywords);
				} else {
					$arrTmpKeyword = explode(",", $meta_keywords);  //#*#*#";
					foreach($arrTmpKeyword AS $arrTmpKeyword_value) {
						if(strlen($arrTmpKeyword_value)) {
							$globals->meta["keywords"][] = preg_replace('/(\r|\n|\")/', " ", $arrTmpKeyword_value);
						}
					}
				}
			} 
		}

        if(!isset($globals->meta["og:image"]) || !isset($globals->meta["twitter:image"]))
        {
            if(!$seo["image_thumb"]["facebook"])
                $seo["image_thumb"]["facebook"] = "470x470";
            if(!$seo["image_thumb"]["twitter"])
                $seo["image_thumb"]["twitter"] = "1024x512";

            //Image
            $cover = $seo["cover"];
            if(!$cover) {
                if(check_function("get_thumb"))
                    $seo["image"] = get_thumb(true);
                //print_r($seo["image"]);
                //die();
                if(is_array($seo["image"]) && count($seo["image"])) {
                    foreach($seo["image"] AS $img_src => $picture) {
                        if(!$picture["placehold"]) {
                            $cover 				= $picture;
                            $cover["path"] 		= $img_src;
                            break;
                        }
                        if(!$cover)	{
                            $cover 				= $picture;
                            $cover["path"] 		= $img_src;
                        }
                    }
                }
            }

            if($cover) {
                if(!$cover["width"] && !$cover["height"]) {
                    $globals->meta["og:image"] =  array("content" => cm_showfiles_get_abs_url("/" . $seo["image_thumb"]["facebook"] . $cover["path"]), "type" => "property");
                    $globals->meta["twitter:image"] =  array("content" => cm_showfiles_get_abs_url("/" . $seo["image_thumb"]["twitter"] . $cover["path"]), "type" => "name");
                } else {

                    if(strpos($cover["src"], "://") !== false)
                        $picture_path = $cover["src"];
                    else
                        $picture_path = $domain_abs_path . $cover["src"];

                    $globals->meta["og:image"] =  array("content" => $picture_path, "type" => "property");
                    $globals->meta["twitter:image"] =  array("content" => $picture_path, "type" => "name");
                }
            }

            if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-social.png")) {
                $logo_social = "/" . FRONTEND_THEME . "/images/logo-social.png";
            } elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-social.jpg")) {
                $logo_social = "/" . FRONTEND_THEME . "/images/logo-social.jpg";
            } elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-social.gif")) {
                $logo_social = "/" . FRONTEND_THEME . "/images/logo-social.gif";
            } elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo.png")) {
                $logo_social = "/" . FRONTEND_THEME . "/images/logo.png";
            } elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo.jpg")) {
                $logo_social = "/" . FRONTEND_THEME . "/images/logo.jpg";
            } elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo.gif")) {
                $logo_social = "/" . FRONTEND_THEME . "/images/logo.gif";
            }

            if($logo_social) {
                $globals->meta["og:image"] =  array("content" => cm_showfiles_get_abs_url("/" . $seo["image_thumb"]["facebook"] . $logo_social), "type" => "property");
                $globals->meta["twitter:image"] =  array("content" => cm_showfiles_get_abs_url("/" . $seo["image_thumb"]["twitter"] . $logo_social), "type" => "name");
            }
        }

        if(is_array($globals->meta["description"]))
			$meta_description_processed = implode(", ", $globals->meta["description"]);
		
		//Facebook BASE
	    if($globals->settings["MOD_SEC_SOCIAL_FACEBOOK_APPID"] && !isset($globals->meta["fb:app_id"]))
	        $globals->meta["fb:app_id"] = array("content" => $globals->settings["MOD_SEC_SOCIAL_FACEBOOK_APPID"], "type" => "property"); 

		//Open Graph BASE
        if(!isset($globals->meta["og:title"]))
            $globals->meta["og:title"] 						= array("content" => $globals->page_title, "type" => "property");
        if(!isset($globals->meta["og:description"]))
            $globals->meta["og:description"] 				= array("content" => $meta_description_processed, "type" => "property");
        if(!isset($globals->meta["og:site_name"]))
            $globals->meta["og:site_name"] 					= array("content" => $site_name, "type" => "property");
        if(!isset($globals->meta["og:type"]))
            $globals->meta["og:type"] 					= array("content" => "website", "type" => "property");
        if(!isset($globals->meta["og:url"]) && $cm->oPage->canonical)
            $globals->meta["og:url"] 					= array("content" => $cm->oPage->canonical, "type" => "property");


        //Twitter BASE
        if(!isset($globals->meta["twitter:title"]))
            $globals->meta["twitter:title"] 				= array("content" => $globals->page_title, "type" => "name");
        if(!isset($globals->meta["twitter:title"]))
            $globals->meta["twitter:description"] 			= array("content" => $meta_description_processed, "type" => "name");
        if(!isset($globals->meta["twitter:site"]))
            $globals->meta["twitter:site"] 				= array("content" => "@" . $site_name, "type" => "name");
        if(!isset($globals->meta["twitter:card"]))
            $globals->meta["twitter:card"] 				= array("content" => "summary", "type" => "name");
        if(!isset($globals->meta["twitter:creator"]) && $owner)
            $globals->meta["twitter:creator"] 			= array("content" => "@" . $owner, "type" => "name");
		
		if(defined("DISABLE_CACHE") && isset($_REQUEST["__nocache__"]) && !isset($_REQUEST["__debug__"]) && !isset($_REQUEST["__query__"]) && strlen($globals->meta["og:url"]["content"])) {
			$res = @file_get_contents("https://developers.facebook.com/tools/debug/og/object?q=" . urlencode($globals->meta["og:url"]["content"]));
        }


		//Override Meta
		if(is_array($override_meta) && count($override_meta)) {
			foreach($override_meta AS $override_meta_key => $override_meta_value) {
				if(isset($globals->meta["og:" . $override_meta_key]))
					$globals->meta["og:" . $override_meta_key]["content"] = $override_meta_value;
				elseif(isset($globals->meta["twitter:" . $override_meta_key]))
					$globals->meta["twitter:" . $override_meta_key]["content"] = $override_meta_value;
				elseif(isset($globals->meta["fb:" . $override_meta_key]))
					$globals->meta["fb:" . $override_meta_key]["content"] = $override_meta_value;
				else
					$globals->meta[$override_meta_key] = $override_meta_value;
			}
		}	  
        
		if(!$globals->seo[$globals->seo["current"]]) 
	    {
    		if(!is_bool($globals->page["seo"]) && $globals->page["user_path"] != $globals->page["seo"])
				$title = substr($globals->page["user_path"], strlen($globals->page["seo"]));
			else
    			$title = basename($globals->page["user_path"]);
    		
    		$title = ucwords(str_replace(array("-", "/"), " ", trim($title, "/")));
    		
    		$globals->seo["page"]["title"] = $globals->page_title;
    		$globals->seo["page"]["title_header"] = ffTemplate::_get_word_by_code($title);
    		$globals->seo["page"]["meta"] = $globals->meta;
    		
    		$globals->seo["current"] = "page";
	    }

		//Title TAG
		if(strlen($globals->page_title)) {
			if(CM_LOCAL_APP_NAME && strpos($globals->page_title, CM_LOCAL_APP_NAME) === false)
			    $tmp_basic_title = ffTemplate::_get_word_by_code("separator_meta_title") . CM_LOCAL_APP_NAME;

			if(is_array($globals->request) && count($globals->request)) {
                $page_title_params = "";
				foreach($globals->request AS $req) {
					$arrReq = explode("=", $req);
					if($page_title_params)
						$page_title_params .= ", ";
					else 
						$page_title_params = " - ";

					$page_title_params .= ffTemplate::_get_word_by_code($arrReq[0]) . " " . ucwords($arrReq[1]);
				}
			}
			    
			$cm->oPage->title = $globals->page_title . $page_title_params . $tmp_basic_title;
		} elseif(!$cm->oPage->title) {
		    $cm->oPage->title = CM_LOCAL_APP_NAME;
		}
	}