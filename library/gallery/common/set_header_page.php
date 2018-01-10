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

        $site_name = (CM_LOCAL_APP_NAME
        				? CM_LOCAL_APP_NAME 
        				: $_SERVER["HTTP_HOST"]
        			);
		$domain_path = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . DOMAIN_INSET;
        //HTML Attr
		$globals->html["attr"] = array("lang" => strtolower(substr(LANGUAGE_INSET, 0, 2))
										/*, "xmlns:fb" => "http://www.facebook.com/2008/fbml"*/
										, "prefix" => "og: " . "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . "ogp.me/ns# fb: " . "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . "www.facebook.com/2008/fbml");

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

		if($globals->http_status)
			http_response_code($globals->http_status);
		else
			$globals->http_status = http_response_code();

		//Meta Application
        if(!$globals->meta["application-name"])
            $globals->meta["application-name"] = $globals->page_title;		
        if(!$globals->meta["apple-mobile-web-app-title"])
        	$globals->meta["apple-mobile-web-app-title"] = $globals->page_title;

		//Meta Viewport
		if(!$globals->meta["viewport"])
			$globals->meta["viewport"] = "width=device-width, height=device-height, initial-scale=1.0, user-scalable=0";

		//favicon
		if(!$globals->links["favicon"]) {
			if(file_exists(FF_DISK_PATH . "/favicon.ico")) {
				$globals->links["favicon"]  = "/favicon.ico";
			} elseif(file_exists(FF_DISK_PATH . "/favicon.png")) {
				$globals->links["favicon"]  = "/favicon.png";
			} elseif(file_exists(FF_DISK_PATH . "/favicon.gif")) {
				$globals->links["favicon"]  = "/favicon.gif";
			}
		}
		if($globals->links["favicon"]) {
			if(is_array($globals->links["favicon"]) && count($globals->links["favicon"])) {
				foreach($globals->links["favicon"] AS $favicon_key => $favicon_file) {
					$cm->oPage->tplAddCss("favicon-" . $favicon_key, basename($favicon_file), ffCommon_dirname($favicon_file), $favicon_key, ffMimeTypeByFilename($favicon_file));
				}
			} else {
				$cm->oPage->tplAddCss("favicon", basename($globals->links["favicon"]), ffCommon_dirname($globals->links["favicon"]), "icon", ffMimeTypeByFilename($globals->links["favicon"]));
			}
		}

        if($globals->http_status == 200) {
            //Meta Robots
            if(!$globals->meta["robots"])
            {
                $globals->meta["robots"] = "index, follow";
                if($globals->navigation["page"] > 0 /*&& $globals->navigation["tot_page"] >= $globals->navigation["page"]*/)
                    $globals->meta["robots"] = "noindex, follow";

            }

            if ($globals->navigation["tot_page"] && $globals->navigation["page"] > 1)
                $cm->oPage->tplAddCss("canonical.prev", ffUpdateQueryString("page", ($globals->navigation["page"] > 2 ? $globals->navigation["page"] - 1 : false), basename($cm->oPage->canonical)), ffCommon_dirname($cm->oPage->canonical), "prev", false);
            if ($globals->navigation["page"] && $globals->navigation["tot_page"] > $globals->navigation["page"])
                $cm->oPage->tplAddCss("canonical.next", ffUpdateQueryString("page", ($globals->navigation["page"] + 1), basename($cm->oPage->canonical)), ffCommon_dirname($cm->oPage->canonical), "next", false);


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
                    $cm->oPage->canonical = $domain_path . $globals->page["strip_path"] . $globals->user_path . $globals->user_path_params;
                }
            }
            if($cm->oPage->canonical)
				$globals->links["canonical"] = $cm->oPage->canonical;

            //RSS
            if (!$globals->links["atom"]) {
                if ($globals->seo["current"] == "detail") {
					$globals->links["atom"]["cat"] = $domain_path . ffCommon_dirname($globals->page["strip_path"] . $globals->page["user_path"]) . "/feed";
                }

                if ($globals->seo["current"] == "thumb" || $globals->seo["current"] == "detail") {
					$globals->links["atom"]["page"] = $domain_path . $globals->page["strip_path"] . $globals->page["user_path"] . "/feed";
                }
            }

            if($globals->links["atom"]) {
				if(is_array($globals->links["atom"]) && count($globals->links["atom"])) {
					foreach($globals->links["atom"] AS $atom_key => $atom_file) {
						$cm->oPage->tplAddCss("atom-" . $atom_key, basename($atom_file) . "/", ffCommon_dirname($atom_file), "alternate", "application/rss+xml");
					}
				} else {
					$cm->oPage->tplAddCss("atom", basename($globals->links["atom"]), ffCommon_dirname($globals->links["atom"]), "alternate", "application/rss+xml");
				}
			}

            //HrefLang
			if (!$globals->links["altlang"] && $globals->seo["altlang"]) {
				$globals->links["altlang"] = $globals->seo["altlang"];
			}
			if(is_array($globals->links["altlang"]) && count($globals->links["altlang"])) {
				foreach($globals->links["altlang"] AS $tiny_code => $path) {
					$cm->oPage->tplAddCss("altlang-" . $tiny_code
						, basename($path)
						, ffCommon_dirname($path)
						, "alternate"
						, null
						, false
						, false
						, array("hreflang" => $tiny_code)
					);
				}
			}

            //AMP Page
            if(!$globals->links["amp"] && 0) {
				$globals->links["amp"] = "http" . ($_SERVER["HTTPS"] ? "s": "") . "://" . DOMAIN_INSET . $globals->page["strip_path"] . $globals->page["user_path"] . "/amp";
            }
			if($globals->links["amp"]) {
				$cm->oPage->tplAddCss("amp"
					, basename($globals->links["amp"])
					, ffCommon_dirname($globals->links["amp"])
					, "amphtml"
					, null
				);
			}

            //Manifest
			if(!$globals->links["manifest"]) {
				$globals->links["manifest"] = "/manifest.json";
			}
            if($globals->links["manifest"]) {
                $cm->oPage->tplAddCss("manifest"
                    , basename($globals->links["manifest"])
                    , ffCommon_dirname($globals->links["manifest"])
                    , "manifest"
                    , null
                );
            }
            //<link rel="amphtml" href="https://www.example.com/url/to/amp/document.html">
        } else {
            $globals->meta["robots"] = "noindex, nofollow";
        }

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

		//Cover
		if(!$globals->cover) {
			$cover 													= $seo["cover"];
			if(!$cover) {
				if(check_function("get_thumb"))
					$seo["image"] 									= get_thumb(true);

				if(is_array($seo["image"]) && count($seo["image"])) {
					foreach($seo["image"] AS $img_src => $picture) {
						if(strpos($picture["src"], "data:") === 0)
							continue;

						if(!$picture["placehold"]) {
							$cover 									= $picture;
							$cover["src"] 							= $img_src;
							break;
						}
						if(!$cover)	{
							$cover 									= $picture;
							$cover["src"] 							= $img_src;
						}
					}
				}
				if(!$cover) {
					if (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-social.png")) {
						$cover["src"] 								= "/logo-social.png";
					} elseif (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-social.jpg")) {
						$cover["src"] 								= "/logo-social.jpg";
					} elseif (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo-social.gif")) {
						$cover["src"] 								= "/logo-social.gif";
					} elseif (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo.png")) {
						$cover["src"] 								= "/logo.png";
					} elseif (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo.jpg")) {
						$cover["src"] 								= "/logo.jpg";
					} elseif (is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/images/logo.gif")) {
						$cover["src"] 								= "/logo.gif";
					}
				}
			}
			$globals->cover = $cover;
		}

		if($globals->cover) {
			if(!$seo["image_thumb"]["facebook"])
				$seo["image_thumb"]["facebook"] = array(
					"width" 										=> "824"
					, "height" 										=> "464"
				);
			if(!$seo["image_thumb"]["twitter"])
				$seo["image_thumb"]["twitter"] = array(
					"width" 										=> "1024"
					, "height" 										=> "512"
				);

			if(strpos($globals->cover["src"], "://") === false)
				$globals->cover["url"] 								= cm_showfiles_get_abs_url($globals->cover["src"]);
			else
				$globals->cover["url"] 								= $globals->cover["src"];

			if(!$globals->cover["width"] && !$globals->cover["height"]) {
				$globals->cover["width"] 							= $seo["image_thumb"]["facebook"]["width"];
				$globals->cover["height"]							= $seo["image_thumb"]["facebook"]["height"];
			}

			if(!isset($globals->meta["og:image"]) || !isset($globals->meta["twitter:image"])) {
				$globals->meta["og:width"] 							= array("content" => $seo["image_thumb"]["facebook"]["width"] , "type" => "property");
				$globals->meta["og:height"] 						= array("content" => $seo["image_thumb"]["facebook"]["height"] , "type" => "property");

				$globals->meta["og:image"] 							= array("content" => str_replace(".", "-" . $seo["image_thumb"]["facebook"]["width"] . "x" . $seo["image_thumb"]["facebook"]["height"] . ".", $globals->cover["src"]), "type" => "property");
				$globals->meta["twitter:image"] 					= array("content" => str_replace(".", "-" . $seo["image_thumb"]["twitter"]["width"] . "x" . $seo["image_thumb"]["twitter"]["height"] . ".", $globals->cover["src"]), "type" => "name");

				if(strpos($globals->cover["src"], "://") === false) {
					$globals->meta["og:image"]["content"] 			= cm_showfiles_get_abs_url($globals->meta["og:image"]["content"]);
					$globals->meta["twitter:image"]["content"] 		= cm_showfiles_get_abs_url($globals->meta["twitter:image"]["content"]);
				}
			}
		}

        if(is_array($globals->meta["description"]))
			$meta_description_processed 							= implode(", ", $globals->meta["description"]);
		
		//Facebook BASE
	    if($globals->settings["MOD_SEC_SOCIAL_FACEBOOK_APPID"] && !isset($globals->meta["fb:app_id"]))
	        $globals->meta["fb:app_id"] 							= array("content" => $globals->settings["MOD_SEC_SOCIAL_FACEBOOK_APPID"], "type" => "property");

        //Open Graph BASE
        if(!isset($globals->meta["og:title"]))
            $globals->meta["og:title"] 								= array("content" => $globals->page_title, "type" => "property");
        if(!isset($globals->meta["og:description"]))
            $globals->meta["og:description"] 						= array("content" => $meta_description_processed, "type" => "property");
        if(!isset($globals->meta["og:site_name"]))
            $globals->meta["og:site_name"] 							= array("content" => $site_name, "type" => "property");
        if(!isset($globals->meta["og:type"]))
            $globals->meta["og:type"] 								= array("content" => "website", "type" => "property");
        if(!isset($globals->meta["og:url"]) && $cm->oPage->canonical)
            $globals->meta["og:url"] 								= array("content" => $cm->oPage->canonical, "type" => "property");


        //Twitter BASE
        if(!isset($globals->meta["twitter:title"]))
            $globals->meta["twitter:title"] 						= array("content" => $globals->page_title, "type" => "name");
        if(!isset($globals->meta["twitter:title"]))
            $globals->meta["twitter:description"] 					= array("content" => $meta_description_processed, "type" => "name");
        if(!isset($globals->meta["twitter:site"]))
            $globals->meta["twitter:site"] 							= array("content" => "@" . $site_name, "type" => "name");
        if(!isset($globals->meta["twitter:card"]))
            $globals->meta["twitter:card"] 							= array("content" => "summary", "type" => "name");

		//Override Meta
		if(is_array($override_meta) && count($override_meta)) {
			foreach($override_meta AS $override_meta_key => $override_meta_value) {
				if(isset($globals->meta["og:" . $override_meta_key]))
					$globals->meta["og:" . $override_meta_key]["content"] 		= $override_meta_value;
				elseif(isset($globals->meta["twitter:" . $override_meta_key]))
					$globals->meta["twitter:" . $override_meta_key]["content"] 	= $override_meta_value;
				elseif(isset($globals->meta["fb:" . $override_meta_key]))
					$globals->meta["fb:" . $override_meta_key]["content"] 		= $override_meta_value;
				else
					$globals->meta[$override_meta_key] 							= $override_meta_value;
			}
		}	  
        
		if(!$globals->seo[$globals->seo["current"]]) 
	    {
    		if(!is_bool($globals->page["seo"]) && $globals->page["user_path"] != $globals->page["seo"])
				$title 												= substr($globals->page["user_path"], strlen($globals->page["seo"]));
			else
    			$title 												= basename($globals->page["user_path"]);
    		
    		$title 													= ucwords(str_replace(array("-", "/"), " ", trim($title, "/")));
    		
    		$globals->seo["page"]["title"] 							= $globals->page_title;
    		$globals->seo["page"]["title_header"]					= ffTemplate::_get_word_by_code($title);
    		$globals->seo["page"]["meta"] 							= $globals->meta;
    		
    		$globals->seo["current"] 								= "page";
	    }

	    //Author
		if(!$globals->author && $seo["owner"]) {
			//todo: da implementare la classe anagraph
			check_function("get_user_data");

			$anagraph 												= user2anagraph($seo["owner"], "anagraph");
			$globals->author = array(
				"id" 												=> $anagraph["ID"]
				, "avatar"											=> $anagraph["avatar"]
				, "name" 											=> $anagraph["name"] . " " . $anagraph["surname"]
				, "src" 											=> ($anagraph["visible"] ? $anagraph["permalink"] : "")
				, "url" 											=> ($anagraph["visible"] ? $domain_path . $anagraph["permalink"] : "")
				, "tags" 											=> explode(",", $anagraph["tags"])
				, "uid" 											=> $anagraph["uid"]
			);

			//Meta Author
			if(!$globals->meta["author"])
				$globals->meta["author"] 							= $globals->author["name"];

			if(!isset($globals->meta["twitter:creator"]))
				$globals->meta["twitter:creator"] 					= array("content" => "@" . $globals->author["name"], "type" => "name");
		}



		//Title TAG
		if(strlen($globals->page_title)) {
			if(CM_LOCAL_APP_NAME && strpos($globals->page_title, CM_LOCAL_APP_NAME) === false)
			    $tmp_basic_title 									= ffTemplate::_get_word_by_code("separator_meta_title") . CM_LOCAL_APP_NAME;

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
			    
			$cm->oPage->title 										= $globals->page_title . $page_title_params . $tmp_basic_title;
		} elseif(!$cm->oPage->title) {
		    $cm->oPage->title 										= CM_LOCAL_APP_NAME;
		}
	}