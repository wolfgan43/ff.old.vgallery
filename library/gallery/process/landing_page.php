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
function process_landing_page($user_path, $type, $primary_section = true) 
{
	$cm = cm::getInstance();

	switch($type)
	{
		case "tag":
			$arrLanding = explode("/", trim($user_path, "/"));
			if(count($arrLanding) <= 2) {
				if($cm->isXHR()) {
			        $res = process_landing_tag_content_by_type("/" . $arrLanding[0], $arrLanding[1]);
			        if($res === false && check_function("process_html_page_error"))
			            $res = '<div>' . process_html_page_error() . '</div>'; //TODO: da verificare. viene strippato il div contenitore quando viene caricato in ajax

				} elseif($primary_section) {
			        $res = process_landing_tag("/" . $arrLanding[0], $arrLanding[1]);
				}										
			}
			break;
		case "city":
		case "province":
		case "region":
		case "state":
			$res = process_landing_place($user_path, $type);
			break;
		default:
		
	}
	
	return $res;
}

function process_landing_tag($user_path, $group = null) 
{
    $cm = cm::getInstance();  
    $globals = ffGlobals::getInstance("gallery");

	$arrLandingPage = process_landing_tag_data($user_path);
        
	if($arrLandingPage["visible"]) {
        $arrLandingPageGroup = process_landing_tag_group();
        $res = parse_landing_page($user_path, $arrLandingPage, $arrLandingPageGroup, "tag", $group);
	}

    return $res["content"];
}

function process_landing_search($user_path, $group = null) 
{
    $cm = cm::getInstance();  
    $globals = ffGlobals::getInstance("gallery");

	$arrLandingPage = process_landing_search_data($user_path, $group);

    $arrLandingPageGroup = process_landing_tag_group(null, Cms::env("SEARCH_VGALLERY_LIMIT")); //process_landing_tag_group_default(Cms::env("SEARCH_VGALLERY_LIMIT"));
    //print_r($arrLandingPageGroup);
	if($cm->isXHR()) {
        $res = process_landing_tag_content_by_type($arrLandingPage, $group, "search", false);
    } else {
		$res = parse_landing_page($user_path, $arrLandingPage, $arrLandingPageGroup, "search", $group);
	}


    return $res["content"];
}

function process_landing_place($user_path, $type) {
	$cm = cm::getInstance();  
    $globals = ffGlobals::getInstance("gallery");
    check_function("preposition");
    check_function("get_locale");
    
	$group = "neg";

	$arrLandingPlace = process_landing_place_data($user_path, $type);

    $arrLandingPageGroup = process_landing_tag_group($group);
    $vg_params["search"]["place"]["city"] = $arrLandingPlace["keys"];
    $vg_params["settings_path"] = true;

    $landing_name = $arrLandingPlace["name"];
    if(LANGUAGE_INSET != LANGUAGE_DEFAULT) {
        check_function("get_webservices");
        $webservices    = get_webservices(); //todo:da sistemare il webservices e fare servizio di translation per il cms
        if($webservices["translate.google"] && $webservices["translate.google"]["enable"]) {
            $translator = ffTranslator::getInstance("google", $webservices["translate.google"]["code"]);
            $landing_name = $translator->translate($landing_name);
        }
    }

    $vg_params["title"] = (0 && $arrLandingPlace["h1"]
        ? $arrLandingPlace["h1"]
        : ffTemplate::_get_word_by_code("landing_place_" . $group) . " " . preposition("in", $landing_name)
    );
    $vg_params["description"] = $arrLandingPlace["pre_content"];
    //$vg_params["fixed_pre_content"] = "" //$arrLandingPlace["pre_content"];
    $vg_params["fixed_post_content"] = $arrLandingPlace["post_content"];

    //$vg_params["nocss"] = false;
    $res = process_landing_tag_content($arrLandingPageGroup, $vg_params);

   return $res;
}

function process_landing_tag_content_by_type($user_path, $group, $type = "tag", $return = "content") 
{        
	$cm = cm::getInstance();

    if(is_array($user_path))
    	$arrLandingPage = $user_path;
    else
		$arrLandingPage = process_landing_tag_data($user_path);

	if(is_array($arrLandingPage) && count($arrLandingPage)) {
		if($type == "tag") {
            $smart_url = "/" . $arrLandingPage["smart_url"];
            $params["prefix"] = "T";
            $params["search"]["relevance"] = $arrLandingPage["smart_url"]; 
        } elseif($type == "search") {                                   
            $smart_url = "";
            $params["prefix"] = "S";
			$params["search"]["term"] = $arrLandingPage["smart_url"];
            $params["search"]["markable"] = true;
        }

		$params["settings_type"] = ($type == "tag" 
                                    ? "overview"
                                    : $type);

        $arrLandingPageGroup = process_landing_tag_group();
	  	$res = parse_landing_page_overview($arrLandingPage, $arrLandingPageGroup, $params);
		if(!$group && $cm->isXHR()) {
			$prefix = '<div id="landing-overview">';
			$postfix = '</div>';
		}

		if(is_array($res["group"])) {
			if(count($res["group"]) == 1) {
				$group = $res["group"][0]; 
				//$res = process_landing_tag_content_by_type($arrLandingPage, $group, $type, false);
				$res["group"] = false;
				$res["current"] = false;
			} else {
				array_unshift($res["group"], "overview");
			}
		}

		if($group) {
			$arrLandingPageTags = process_landing_tags($arrLandingPage, $group);

			$params["settings_path"] = stripslash("/" . $type . "/" . $group . $smart_url);
	        $params["settings_type"] = $type;			

		    $res["content"] = parse_landing_page_content($arrLandingPageTags, $params);
			if(!isset($res["current"]))
				$res["current"] = $group;				
		}

		if($res["content"] && $prefix)
			$res["content"] = $prefix . $res["content"] . $postfix;
		
		if(!$res)
			$res = false;
	}

	if($return && $res[$return])
		return $res[$return];
	else
		return $res;
}

function process_landing_search_data($user_path, $group = null) 
{
	$db = ffDB_Sql::factory();
	$arrLandingPage = process_landing_tag_data($user_path);

	if(is_array($arrLandingPage) && count($arrLandingPage))	
		ffRedirect($user_path . (strlen($group) ? "/" . $group : ""), 301);

	$now = time();
	
	$arrLandingPage["query"] 									= $user_path;
	$arrLandingPage["ID"] 										= "";
	$arrLandingPage["code"] 									= "";
	$arrLandingPage["smart_url"] 								= ffCommon_url_rewrite(basename($user_path));
	$arrLandingPage["parent"] 									= ffCommon_dirname($user_path);
	$arrLandingPage["title"] 									= ucwords(str_replace("-", " ", $arrLandingPage["smart_url"]));
	$arrLandingPage["description"] 								= $arrLandingPage["title"];
	$arrLandingPage["keywords"] 								= str_replace("-", " ", $arrLandingPage["smart_url"]);
	$arrLandingPage["h1"] 										= ucwords(str_replace("-", " ", $arrLandingPage["smart_url"]));
	$arrLandingPage["ID_lang"] 									= LANGUAGE_INSET_ID;
	$arrLandingPage["h2"] 										= "";
	$arrLandingPage["pre_content"] 								= "";
	$arrLandingPage["post_content"] 							= "";
	$arrLandingPage["permalink"] 								= stripslash($arrLandingPage["parent"]) . "/" . $arrLandingPage["smart_url"];
	$arrLandingPage["framework_css"]["header"]["container"] 	= "";
	$arrLandingPage["framework_css"]["header"]["inner"] 		= "";
	$arrLandingPage["framework_css"]["body"]["menu"] 			= "";
	$arrLandingPage["framework_css"]["body"]["container"] 		= "";

	$sSQL = "SELECT search_tags_page_by_search.*
			FROM search_tags_page_by_search
			WHERE search_tags_page_by_search.smart_url = " . $db->toSql(ffCommon_url_rewrite(basename($user_path))) . "
				AND search_tags_page_by_search.parent = " . $db->toSql(ffCommon_dirname($user_path)) . "
				AND search_tags_page_by_search.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
    $db->query($sSQL);

    if($db->nextRecord()) {
		$arrLandingPage = array(
		    "ID"				    => $db->getField("ID", "Number", true)
		    , "code"			    => $db->getField("code", "Number", true)
            , "name"                => $db->getField("name", "Text", true)
    		, "smart_url" 		    => $db->getField("smart_url", "Text", true)
    		, "parent" 			    => $db->getField("parent", "Text", true)
    		, "title" 		        => $db->getField("meta_title", "Text", true)
    		, "description" 	    => $db->getField("meta_description", "Text", true)
    		, "keywords" 		    => $db->getField("keywords", "Text", true)
    		, "h1" 				    => $db->getField("h1", "Text", true)
    		, "h2" 				    => $db->getField("h2", "Text", true)
    		, "pre_content" 	    => $db->getField("pre_content", "Text", true)
    		, "post_content" 	    => $db->getField("post_content", "Text", true)
    		, "permalink"			=> $db->getField("permalink", "Text", true)
    		, "framework_css" => array(
    			"header" 	=> array("container"	=> $db->getField("class_header_container", "Text", true)
    								, "inner"	=> $db->getField("class_header_inner", "Text", true)
    						)	
    			, "body" 		=> array("menu"		=> $db->getField("class_body_menu", "Text", true)
    								, "container"	=> $db->getField("class_body_container", "Text", true)
    			)
    		)
		);
		//$sSQL = "UPDATE search_tags_page_by_search SET `hits` = `hits` + 1 WHERE `ID` = " . $db->toSql($arrLandingPage["ID"], "Number");
		//$db->execute($sSQL);
	} else {
		$sSQL = "INSERT INTO search_tags_page_by_search
				(	
					`ID`
                    , `name`
					, `smart_url`
					, `parent`
					, `meta_title`
					, `meta_description`
					, `keywords`
					, `h1`
					, `ID_lang`
					, `h2`
					, `pre_content`
					, `code`
					, `post_content`
					, `hits`
					, `created`
					, `last_update`
					, `permalink`
					, `class_header_container`
					, `class_header_inner`
					, `class_body_menu`
					, `class_body_container`
				)
				VALUES
				(
					null
                    , " . $db->toSql($arrLandingPage["name"]) . "
					, " . $db->toSql($arrLandingPage["smart_url"]) . "
					, " . $db->toSql($arrLandingPage["parent"]) . "
					, " . $db->toSql($arrLandingPage["title"]) . "
					, " . $db->toSql($arrLandingPage["description"]) . "
					, " . $db->toSql($arrLandingPage["keywords"]) . "
					, " . $db->toSql($arrLandingPage["h1"]) . "
					, " . $db->toSql($arrLandingPage["ID_lang"], "Number") . "
					, ''
					, ''
					, ''
					, ''
					, 0
					, " . $db->toSql($now, "Number") . "
					, " . $db->toSql($now, "Number") . "
					, " . $db->toSql($arrLandingPage["permalink"]) . "
					, " . $db->toSql($arrLandingPage["framework_css"]["header"]["container"]) . "
					, " . $db->toSql($arrLandingPage["framework_css"]["header"]["inner"]) . "
					, " . $db->toSql($arrLandingPage["framework_css"]["body"]["menu"]) . "
					, " . $db->toSql($arrLandingPage["framework_css"]["body"]["container"]) . "
				)";
		$db->execute($sSQL);
		$arrLandingPage["ID"] = $db->getInsertID(true);
	}
	return $arrLandingPage;
}

function parse_landing_page($user_path, $arrLandingPage, $arrLandingPageGroup, $type, $group = null) {
    $cm = cm::getInstance();  
    $globals = ffGlobals::getInstance("gallery");

	if(is_array($arrLandingPage) && count($arrLandingPage) && check_function("get_layout_settings"))
    {
        check_function("preposition");
        
        $framework_css = array(
        	"header" => array(
        		"container" => array(
        			"row-default" => ""
        		)
        		, "inner" => array(
        			"col" => array(
						"xs" => 12
						, "sm" => 12
						, "md" => 12
						, "lg" => 12
					)
        		)
        	) 
        	, "body" => array(
        		"container" => array(
        			"class" => "landing-container hidden"
        			, "row-default" => "" 
        		)
        		, "menu" => array(
        			"class" => "landing-menu"
        			, "row-default" => "" 
        		)
        	)
        );
		$framework_css = cache_get_settings("framework_css", "landing-page", $framework_css);

       // $framework_css = array_replace_recursive($framework_css, $arrLandingPage["framework_css"]);
        /***
        * Open Template
        */

        $tpl_data["custom"] = "landing-page" . str_replace("/", "_", $user_path). ".html";
        $tpl_data["base"] = "landing_page.html";
        $tpl_data["result"] = get_template_cascading($user_path, $tpl_data);

        $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
        //$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");
        $tpl->load_file($tpl_data["result"]["name"], "main");

        /***
        * Init Template
        */
        
        $cm->oPage->tplAddJs("ff.cms.landingpage", "ff.cms.landingpage.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools");
        $cm->oPage->tplAddJs("ff.ajax", "ajax.js", FF_THEME_DIR . "/library/ff");
        $cm->oPage->tplAddJs("ff.ffPageNavigator", "ffPageNavigator.js", FF_THEME_DIR . "/library/ff");

        //da potenziare
        $tpl->set_var("landing_type", $type);
        $tpl->set_var("landing_wrap", Cms::getInstance("frameworkcss")->getClass($framework_css["header"]["container"]));
        $tpl->set_var("landing_wrap_inner", Cms::getInstance("frameworkcss")->getClass($framework_css["header"]["inner"]));

        /**
        * Process Content Landing Page
        */
		if(is_array($arrLandingPageGroup["contents"]) && count($arrLandingPageGroup["contents"])) {
        	$tpl->set_var("landing_menu_class", Cms::getInstance("frameworkcss")->getClass($framework_css["body"]["menu"])); 
        	$tpl->set_var("landing_type_class", Cms::getInstance("frameworkcss")->get("navbar", "bar"));
            $tpl->set_var("landing_container_class", Cms::getInstance("frameworkcss")->getClass($framework_css["body"]["container"])); 
			/*if(!$group) {
				$group = $arrLandingPageGroup["starter"];
			}*/
			
			$arrLandingPageGroup["contents"] = array_merge(
				array("overview" => array(
						"name" => ""
						, "label" => ffTemplate::_get_word_by_code("landing_content_" . $type)
					)
				)
				, $arrLandingPageGroup["contents"]
			);

			$res = process_landing_tag_content_by_type($arrLandingPage, $group, $type, false);
			if($res["group"] !== false) {
	            foreach($arrLandingPageGroup["contents"] AS $group_key => $arrContent) {
            		if(is_array($res["group"]) && array_search($group_key, $res["group"]) === false)
            			continue;
            			
	                $tpl->set_var("landing_item_url", FF_SITE_PATH . ($type == "tag" ? "": "/" . $type) . stripslash($arrLandingPage["parent"]) . "/" . $arrLandingPage["smart_url"] . ($arrContent["name"] ? "/" . $arrContent["name"] : ""));
	                $tpl->set_var("landing_item_group", $arrContent["name"]);
	                $tpl->set_var("landing_item_name", $arrContent["label"]);
	                $tpl->set_var("landing_item_class", "");
	                if($group == $arrContent["name"]) {
                		$tpl->set_var("landing_item_class", ' class="' . Cms::getInstance("frameworkcss")->get("current", "util") . '"');
	                    $current_group = $arrContent;
	                }
	                $tpl->parse("SezTypeItem", true);
	            }
	            $tpl->parse("SezType", false);
			}
			if($res["current"]) {
				$current_group = $arrLandingPageGroup["contents"][$res["current"]];
				$group = $arrLandingPageGroup["contents"][$res["current"]]["name"];
			}
			if(!$res["content"]) { 
                if($group && check_function("process_html_page_error"))
				    $res["content"] = process_html_page_error();

                if($type != "search")	
    			    http_response_code(404);
			}
			
            $tpl->set_var("content", $res["content"]);
        }

        $layout = array(
            "ID" => null
            , "title" => "Landing Page"
            , "type" => "SEARCH"
            , "prefix" => ""
            , "class" => array(
                "base" => "landing-page"
                , "default" => $arrLandingPage["smart_url"]
            )
            , "settings" => Cms::getPackage("search") //get_layout_settings(null, "SEARCH")
        );

        $unic_id = "landing-page-" . $arrLandingPage["code"];

        /***
        * Set Seo Params
        */

		$globals->seo["current"] 							= "tag";
        $globals->seo["tag"]["ID"]                      	= $arrLandingPage["ID"];
         if($group) {
		 	$globals->seo["tag"]["title"]                   = str_replace(array("[GROUP]", "[TERM]"), array($current_group["label"], $arrLandingPage["title"]), Cms::env("SEARCH_META_TITLE_PROTOTYPE"));
			$globals->seo["tag"]["title_header"]            = str_replace(array("[GROUP]", "[TERM]"), array($current_group["label"], $arrLandingPage["h1"]), Cms::env("SEARCH_META_TITLE_HEADER_PROTOTYPE"));
			$globals->seo["tag"]["meta"]["description"][]   = str_replace(array("[GROUP]", "[TERM]"), array($current_group["label"], $arrLandingPage["description"]), Cms::env("SEARCH_META_DESCRIPTION_PROTOTYPE"));
			$globals->seo["tag"]["meta"]["keywords"][]      = str_replace(array("[GROUP]", "[TERM]"), array($current_group["label"], $arrLandingPage["keywords"]), Cms::env("SEARCH_KEYWORDS_PROTOTYPE"));
        } else {
		 	$globals->seo["tag"]["title"]                   = str_replace("[TERM]", $arrLandingPage["title"], Cms::env("SEARCH_OVERVIEW_META_TITLE_PROTOTYPE"));
			$globals->seo["tag"]["title_header"]            = str_replace("[TERM]", $arrLandingPage["title"], Cms::env("SEARCH_OVERVIEW_META_TITLE_HEADER_PROTOTYPE"));
			$globals->seo["tag"]["meta"]["description"][]   = str_replace("[TERM]", $arrLandingPage["title"], Cms::env("SEARCH_OVERVIEW_META_DESCRIPTION_PROTOTYPE"));
			$globals->seo["tag"]["meta"]["keywords"][]      = str_replace("[TERM]", $arrLandingPage["title"], Cms::env("SEARCH_OVERVIEW_KEYWORDS_PROTOTYPE"));
        }
        

        
        /**
        * Parsing Template
        */
        if($arrLandingPage["h1"]) {
        	$tpl->set_var("h1", $globals->seo["tag"]["title_header"]);
            $tpl->parse("SezH1", false);
        }
        if($arrLandingPage["h2"]) {
            $tpl->set_var("h2", $arrLandingPage["h2"]);
            $tpl->parse("SezH2", false);
        }
        if($arrLandingPage["pre_content"]) {
            $tpl->set_var("pre_content", $arrLandingPage["pre_content"]);
            $tpl->parse("SezPreContent", false);
        }
        if($arrLandingPage["post_content"]) {
            $tpl->set_var("post_content", $arrLandingPage["post_content"]);
            $tpl->parse("SezPostContent", false);
        }
                
        $buffer = $tpl->rpparse("main", false);
        
	    /**
	    * Admin Father Bar
	    */
	    if(AREA_SEARCH_SHOW_MODIFY) {
	        $admin_menu["admin"]["unic_name"] = $unic_id;
	        $admin_menu["admin"]["title"] = $layout["title"];
	        $admin_menu["admin"]["class"] = $layout["type_class"];
	        $admin_menu["admin"]["group"] = $layout["type_group"];
	        $admin_menu["admin"]["modify"] = "/restricted/tags/page/modify?keys[code]=" . $arrLandingPage["code"];
	        $admin_menu["admin"]["delete"] = "";
	        if(AREA_PROPERTIES_SHOW_MODIFY) {
	            $admin_menu["admin"]["extra"] = VG_SITE_RESTRICTED . "/tags/group";
	        }
	        if(AREA_ECOMMERCE_SHOW_MODIFY) {
	            $admin_menu["admin"]["ecommerce"] = "";
	        }
	        if(AREA_LAYOUT_SHOW_MODIFY) {
	           // $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
	           // $admin_menu["admin"]["layout"]["type"] = $layout["type"];
	        }
	        if(AREA_SETTINGS_SHOW_MODIFY) {
	            $admin_menu["admin"]["setting"] = ""; //$layout["type"]; 
	        }


	        $admin_menu["sys"]["path"] = $user_path;
	        $admin_menu["sys"]["type"] = "admin_toolbar";
	    }

	    /**
	    * Process Block Header
	    */            
	    if(check_function("set_template_var")) 
	        $block = get_template_header($user_path, $admin_menu, $layout, $tpl);

		$res["pre"] 		= $block["tpl"]["pre"];
		$res["content"] 	= $buffer;
		$res["post"] 		= $block["tpl"]["post"];
    } else {
    
    
    
    }

    return $res;
}

function parse_landing_page_overview($arrLandingPage, $arrLandingPageGroup, $vg_params) {
	check_function("preposition");

	$res = null;
    if(is_array($arrLandingPageGroup["overview"]) && count($arrLandingPageGroup["overview"])) {
		$arrLandingPageTags = process_landing_tags($arrLandingPage);

//print_r($arrLandingPage);
		if($vg_params["settings_type"] == "overview")
			ksort($arrLandingPageGroup["overview"]);

		foreach($arrLandingPageGroup["overview"] AS $overview_data) {
			$vg_params["settings_path"] = stripslash("/" . $vg_params["settings_type"] . "/" . $overview_data["name"]);
			
            if($vg_params["settings_type"] == "overview") {
				$overview_data["data_source"] = $arrLandingPageGroup["contents"][$overview_data["name"]]["data_source"];
				$overview_data["ext"] = $arrLandingPageGroup["contents"][$overview_data["name"]]["ext"];
				$overview_data["applet"] = $arrLandingPageGroup["contents"][$overview_data["name"]]["applet"];
				$overview_data["module"] = $arrLandingPageGroup["contents"][$overview_data["name"]]["module"];
				
				$landing_data = $overview_data; 
				$tag = (array_key_exists($overview_data["name"], $arrLandingPageTags) 
					? $arrLandingPageTags[$overview_data["name"]] 
					: $arrLandingPageTags["default"]
				);

				$vg_params["search"]["filter"]["tags"] = $tag;	
            	$vg_params["prefix"] = "O";	
				$vg_params["template"] = "landing-" . $landing_data["name"] . "-overview";
                $vg_params["limit"]["elem"] = $landing_data["limit"];
                $vg_params["fixed_post_content"] = '<a class="lp-grp" href="javascript:void(0);" rel="' . $overview_data["name"] . '">' . ffTemplate::_get_word_by_code("show_all") . " " . preposition("i", $overview_data["label"]) . " " . preposition("di", $vg_params["search"]["filter"]["tags"]) . '</a>';
            } else {
                $landing_data = $arrLandingPageGroup["contents"][$overview_data["name"]];

                $vg_params["limit"]["elem"] = Cms::env("SEARCH_OVERVIEW_LIMIT");
                if(Cms::env("SEARCH_OVERVIEW_SUBTITLE"))
                	$vg_params["fixed_pre_content"] = '<a class="lp-grp" href="javascript:void(0);" rel="' . $overview_data["name"] . '">' . $overview_data["label"] . '</a>';
            }

			$overview = parse_landing_page_content($landing_data, $vg_params);
			if($overview) {
				$res["content"] .= $overview;
				$res["group"][] = $overview_data["name"];
			}
        }
    }

	return $res;
}

function parse_landing_page_content($arrLandingPageTags, $vg_params = array()) {
	if(isset($arrLandingPageTags["items"])) {
		if($arrLandingPageTags["items"]["class"])
			$framework_css["items"]["class"] = $arrLandingPageTags["items"]["class"];

		//if($framework_css["items"]["fluid"] == 0 && is_array($arrLandingPageTags["items"]["grid"])) {
			$framework_css["items"]["fluid"] = $arrLandingPageTags["items"]["fluid"];
			$framework_css["items"]["grid"] = $arrLandingPageTags["items"]["grid"];
		//}	
	}
	if(isset($arrLandingPageTags["container"])) {
		if($arrLandingPageTags["container"]["class"])
				$framework_css["container"]["class"] = $arrLandingPageTags["container"]["class"];

			//if($framework_css["container"]["fluid"] == 0 && is_array($arrLandingPageTags["container"]["grid"])) {
				$framework_css["container"]["fluid"] = $arrLandingPageTags["container"]["fluid"];
				$framework_css["container"]["grid"] = $arrLandingPageTags["container"]["grid"];
			//}	
	} else {
		if($arrLandingPageTags["class"])
			$framework_css["container"]["class"] = $arrLandingPageTags["class"];

		//if($framework_css["container"]["fluid"] == 0 && is_array($arrLandingPageTags["grid"])) {
			$framework_css["container"]["fluid"] = $arrLandingPageTags["fluid"];
			$framework_css["container"]["grid"] = $arrLandingPageTags["grid"];
		//}
	}
	if($arrLandingPageTags["wrap"]) {
		$framework_css["wrap"] = $arrLandingPageTags["wrap"];
		$framework_css["extra"] = $arrLandingPageTags["extra"];
	}

	$vg_params["framework_css"] = $framework_css;
	if(!$vg_params["template"])
		$vg_params["template"] = "landing-" . $arrLandingPageTags["name"];
	
	if($vg_params["template"] && !is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/" . $vg_params["template"] . ".html"))
		$vg_params["template"] 		= null;
								
	if(!$vg_params["settings_path"])
		$vg_params["settings_path"] = true;

   /*    $vg_params["search"]["filter"]["tags"] = (array_key_exists($overview_data["name"], $arrLandingPageTags) 
        ? $arrLandingPageTags[$overview_data["name"]] 
        : $arrLandingPageTags["default"]
    );  */  
        
	if(!$vg_params["search"]["filter"]["tags"] && is_array($arrLandingPageTags["tags"]) && count($arrLandingPageTags["tags"]))
		$vg_params["search"]["filter"]["tags"] = $arrLandingPageTags["tags"];

	if($vg_params["search"])
		$res = process_landing_tag_content($arrLandingPageTags, $vg_params);

	return $res;
}
function process_landing_tag_content($arrLandingPageTags, $params = null) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    if(check_function("process_vgallery_thumb") && check_function("get_layout_settings")) {
        $layout = array(
            "ID" => ""
            , "settings" => array()
        );
        if(!is_array($arrLandingPageTags))
            $arrLandingPageTags = process_landing_tag_group($arrLandingPageTags);     

       //print_r($arrLandingPageTags);
       //print_r($params);
        //print_r($params);       
        //print_r($params["framework_css"] );
        if(!$params["search"])
            $params["search"] = $globals->search;
        if(!$params["navigation"])
            $params["navigation"] = $globals->navigation;
		$params["navigation"]["skip_alphanum"] = true;

        switch($arrLandingPageTags["ext"]) {
            case "applet":
            
                if(check_function("get_class_by_grid_system"))
                    $params["framework_css"] = get_class_by_grid_system_def($params["framework_css"], $params["framework_css"], true);

                if (check_function("process_forms_framework"))
                    $res = process_forms_framework($arrLandingPageTags["applet"], $params, $params["settings_path"]);

                break;
            case "module":
                if(check_function("get_class_by_grid_system"))
                    $params["framework_css"] = get_class_by_grid_system_def($params["framework_css"], $params["framework_css"], true);

                if(function_exists($arrLandingPageTags["module"])) 
                    $res = call_user_func_array($arrLandingPageTags["module"], array($params, $params["settings_path"]));
                break;
            default:
                $res = null;
                if(is_array($arrLandingPageTags["data_source"]) && count($arrLandingPageTags["data_source"])) {
                    foreach($arrLandingPageTags["data_source"] AS $src_type => $src_data) {
                        $arrLayout = get_layout_by_block($src_type, $src_data, "settings");
                        $arrLayoutSettings = null;
                        if(is_array($arrLayout)) {
                            if(array_key_exists("ID", $arrLayout)) {
                                $layout = $arrLayout;
                            } else {
                                $arrLayoutSettings = $arrLayout;
                            }
                            
                            $layout["prefix"] = "LP" . $params["prefix"];

                            $arrVgalleryLimit = $params["limit"];
                             
                            if(count($src_data) > 1) {
                                $vgallery_path = null;
                                $vgallery_name = null;
                                $arrVgalleryLimit["vgallery_name"] = array_keys($src_data);
                            } else {
                                $arrVgalleryPath = array_values($src_data);
                                $vgallery_name = basename($arrVgalleryPath[0]);
                                $vgallery_path = $src_data[$vgallery_name];
                                if(!$vgallery_path) {
                                    $vgallery_name = $src_type;
                                    $vgallery_path = $src_data[$src_type];
                                }
                            }

                            $buffer = process_vgallery_thumb(
                                $vgallery_path
                                , $src_type
                                , array(
                                    "vgallery_name" => $vgallery_name
                                    , "permalink" => ($params["permalink_parent"]
                                        ? $params["permalink_parent"]
                                        : ""
                                    ) . $arrLayout["base_path"]
                                    , "search" => $params["search"]
                                    , "settings_path" => ($params["settings_path"]
                                        ? $params["settings_path"]
                                        : "/" . $arrLandingPageTags["name"]
                                    )
                                    , "settings_type" => $params["settings_type"]
                                    , "framework_css" => $params["framework_css"]
                                    , "limit" => $arrVgalleryLimit
                                    , "settings" => $arrLayoutSettings
                                    /*, "group" => array(
                                        "ID" => 2
                                    )*/
                                    , "navigation" => $params["navigation"]
                                    , "fixed_pre_content" => $params["fixed_pre_content"]
                                    , "fixed_post_content" => $params["fixed_post_content"]
                                    , "template_name" => $params["template"]
                                    , "skip_error" => true
                                    , "template_skip_hide" => true
                                    , "output" => true
                                    , "enable_title" => false
                                    , "title" => $params["title"]
                                    , "description" => $params["description"]
                                )
                                , $layout
                            );
                            
                            if(is_array($buffer["nodes"])  && count($buffer["nodes"])) {
	                            if(!$params["nocss"]) {
                            		$css_name = str_replace("/", "_", trim($vgallery_path, "/"));
                            		if(is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/css/" . $css_name . ".css")) {
                            			$cm->oPage->tplAddCss($css_name, $css_name . ".css", FF_THEME_DIR . "/" . FRONTEND_THEME . "/css");
                            		}
								}

								$res .= $buffer["content"];
							}
                        }
                    }
                }
        }            
        
    }

    return $res;
}

function process_landing_place_data($user_path, $type) {
	$globals = ffGlobals::getInstance("gallery");

	$tbl_support = FF_SUPPORT_PREFIX . $type;
	if(!$globals->data_storage[$type][$user_path]) {
		$db = ffDB_Sql::factory();

		$sSQL = "SELECT " . $tbl_support . ".*
				FROM ". $tbl_support . "
				WHERE ". $tbl_support . ".permalink = " . $db->toSql($user_path);
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				$globals->data_storage[$type][$db->record["permalink"]]["places"][] = $db->record;
				$globals->data_storage[$type][$db->record["permalink"]]["keys"][] = $db->record["ID"];
			} while($db->nextRecord());
		}
	}
	$father = $globals->data_storage[$type][$user_path];
	if($father) {
		$res                                                        = $father["places"][0];
		$res["keys"]["ID" .($type == "city" ? "" : "_" . $type)] 	= $father["keys"];
	}
	
	return $res;
}

function process_landing_tag_data($user_path) {
	$globals = ffGlobals::getInstance("gallery");

	if(!$globals->data_storage["tag"][$user_path]) {
		$db = ffDB_Sql::factory();

		$sSQL = "SELECT search_tags_page.*
					, search_tags.ID AS ID_primary_tag
				FROM search_tags_page 
					LEFT JOIN search_tags ON search_tags.ID_tag_page = search_tags_page.ID
				WHERE search_tags_page.smart_url = " . $db->toSql(basename($user_path)) . "
					AND search_tags_page.parent = " . $db->toSql(ffCommon_dirname($user_path)) . "
					AND search_tags_page.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
		$db->query($sSQL);
		if($db->nextRecord()) {
			$globals->data_storage["tag"][stripslash($db->record["parent"]) . "/" . $db->record["smart_url"]] = $db->record;
		}
	}

	$father = $globals->data_storage["tag"][$user_path];
	if($father) {
		$res = array(
		    "ID"				=> $father["ID"]
			, "ID_primary_tag"	=> $father["ID_primary_tag"]
		    , "code"			=> $father["code"]
    		, "smart_url" 		=> $father["smart_url"]
    		, "parent" 			=> $father["parent"]
    		, "title" 			=> $father["meta_title"]
    		, "description" 	=> $father["meta_description"]
    		, "keywords" 		=> $father["keywords"]
    		, "h1" 				=> $father["h1"]
    		, "h2" 				=> $father["h2"]
    		, "pre_content" 	=> $father["pre_content"]
    		, "post_content" 	=> $father["post_content"]
    		, "visible"			=> $father["visible"]
    		, "permalink"		=> $father["permalink"]
    		, "class" => array(
    			"header_container"	=> $father["class_header_container"]
    			, "header_inner"	=> $father["class_header_inner"]
    			, "body_menu"		=> $father["class_body_menu"]
    			, "body_container"	=> $father["class_body_container"]
    		)
		);
	}

	return $res;
}

function process_landing_tags($arrLandingPage, $group = null) {
	$db = ffDB_Sql::factory();
	$arrLoadedTags = array();
	$arrLandingPageTags = array();
	
	if($group)
		$arrLandingPageTags = process_landing_tag_group($group);

	$sSQL = "SELECT GROUP_CONCAT(search_tags_page_rel_group.ID_tag SEPARATOR ',') AS tags
				, GROUP_CONCAT(search_tags_rel.ID_dest SEPARATOR ',') AS tags_alias
				, search_tags_group.smart_url AS group_name
			FROM search_tags_page_rel_group
				INNER JOIN search_tags_group ON search_tags_group.ID = search_tags_page_rel_group.ID_tag_group
				LEFT JOIN search_tags_rel ON search_tags_rel.ID_src = search_tags_page_rel_group.ID_tag
			WHERE search_tags_page_rel_group.ID_tag_page = " . $db->toSql($arrLandingPage["ID"], "Number") . 
				($group && is_array($arrLandingPageTags)
					? " AND search_tags_page_rel_group.ID_tag_group = " . $db->toSql($arrLandingPageTags["ID"], "Number")
					: ""
				) . "
			GROUP BY search_tags_page_rel_group.ID_tag_page, search_tags_group.ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$tags_sep = "";
			$group_name = $db->getField("group_name", "Text", true);
			$tags = $db->getField("tags", "Text", true);
			$tags_alias = $db->getField("tags_alias", "Text", true);
			if($tags || $tags_alias) {
				if($tags && $tags_alias)
					$tags_sep = ",";

				$arrLoadedTags[$group_name] = explode(",", $tags . $tags_sep . $tags_alias);
			}
			
			if($arrLandingPage["ID_primary_tag"])
				$arrLoadedTags[$group_name][] = $arrLandingPage["ID_primary_tag"];
		} while($db->nextRecord());
	}
	
	if($group) {
		$arrLandingPageTags["tags"] = $arrLoadedTags[$group];
		if($arrLandingPage["ID_primary_tag"])
			$arrLandingPageTags["tags"][] = $arrLandingPage["ID_primary_tag"];
	} else {
		$arrLandingPageTags = $arrLoadedTags;
		if($arrLandingPage["ID_primary_tag"])
			$arrLandingPageTags["default"][] = $arrLandingPage["ID_primary_tag"];
	}
	
	return $arrLandingPageTags;
}

function process_landing_tag_group_default($limit) {
    static $landing_page_group = null;

    if(!is_array($landing_page_group)) {
		$db = ffDB_Sql::factory();
		if($limit == "null")
			$limit = "";
		
		if(!$limit || $limit != "anagraph") {
			$arrSQL[] = "( SELECT layout.*
 							, layout_path.path AS path
 							, '' AS real_value
 							, CONCAT('/', layout.value, layout.params) AS real_params
						FROM layout 
							INNER JOIN layout_type ON layout_type.ID = layout.ID_type
							INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
						WHERE layout_type.name = " . $db->toSql("VIRTUAL_GALLERY") . "
							AND layout.value != 'anagraph'
							" . ($limit
								? " AND FIND_IN_SET(layout.value, " . $db->toSql($limit) . ")"
								: ""
							) . " 
						ORDER BY real_params
					)";
		}
		if(!$limit || strpos($limit, "anagraph") !== false) {
			$arrSQL[] = "( SELECT layout.*
 							, layout_path.path AS path
 							, anagraph_categories.name AS real_value
 							, CONCAT('/', anagraph_categories.smart_url) AS real_params
						FROM layout 
							INNER JOIN layout_type ON layout_type.ID = layout.ID_type
							INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
							LEFT JOIN anagraph_categories ON anagraph_categories.ID = layout.params
						WHERE layout_type.name = " . $db->toSql("VIRTUAL_GALLERY") . "
							AND layout.value = 'anagraph'
						ORDER BY real_params
					)";
		}
		$sSQL = implode(" UNION ", $arrSQL);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$landing_page_group["starter"] = ($db->getField("real_value", "Text", true)
			    								? $db->getField("real_value", "Text", true)
			    								: ffCommon_url_rewrite($db->getField("value", "Text", true))
			    							);
			do {
				$source_path = $db->getField("path", "Text", true);
				$vgallery_name = $db->getField("value", "Text", true);
	            switch($vgallery_name) {
	                case "anagraph":
	                    $vgallery_type = "anagraph";
	                    break;
	                case "files":
	                    $vgallery_type = "files";
	                    break;
	                default:
	                    $vgallery_type = "vgallery";
	                
	            }
	            
				$unic_id = $db->getField("real_value", "Text", true);
				if(!$unic_id)
					$unic_id = $vgallery_name;
				
				$unic_id = ffCommon_url_rewrite($unic_id);
				$vgallery_path = stripslash($db->getField("real_params", "Text", true));

				$landing_page_group["contents"][$unic_id]["label"] 												= ffTemplate::_get_word_by_code($unic_id);
				$landing_page_group["contents"][$unic_id]["name"] 												= $unic_id;
				$landing_page_group["contents"][$unic_id]["data_source"][$vgallery_type][$vgallery_name] 		= $vgallery_path;
				$landing_page_group["source"][$vgallery_path]													= $unic_id;

				$landing_page_group["overview"][$unic_id]["ID"]													= $landing_page_group["contents"][$unic_id]["ID"];
				$landing_page_group["overview"][$unic_id]["label"]												= $landing_page_group["contents"][$unic_id]["label"];
				$landing_page_group["overview"][$unic_id]["name"]												= $landing_page_group["contents"][$unic_id]["name"];
			} while($db->nextRecord());
		}
	}
	
	return $landing_page_group;
}

function process_landing_tag_group($group = null, $limit = null) {
    static $landing_page_group = null;
	$db = ffDB_Sql::factory();
    
    if(!is_array($landing_page_group)) {
        $landing_page_group = array();
		if($limit && $limit != "null")
			$arrLimit = explode(",", $limit);
			
	    $sSQL = "SELECT search_tags_group.*
				    , search_tags_group_rel.data_source AS data_source
				    , search_tags_group_rel.data_limit AS data_limit
			    FROM search_tags_group
				    INNER JOIN search_tags_group_rel ON search_tags_group_rel.ID_group = search_tags_group.ID
			    WHERE 1 "
                    . ($group === null
                        ? " AND search_tags_group.status > 0 "
                        : "" //" AND search_tags_group.smart_url = " . $db->toSql($group)
                    ) . "
			    ORDER BY search_tags_group.`order`";
	    $db->query($sSQL);
	    if($db->nextRecord() ) {
		    $landing_page_group["starter"] = $db->getField("smart_url", "Text", true);
		    do {
                $vgallery_path = $db->getField("data_limit", "Text", true);
                $vgallery_type = $db->getField("data_source", "Text", true);
                $external = false;
                switch($vgallery_type) {
                    case "anagraph":
                        $vgallery_name = "anagraph";
                        break;
                    case "files":
                        $vgallery_name = "files";
                        break;
                    case "module":
                        $vgallery_name = "module";
                        $external = $vgallery_path;
                        break;
                    case "applet":
                        $vgallery_name = "applet";
                        $external = $vgallery_path;
                        break;
                    default:
                        $arrVgalleryPath = explode("/", trim($vgallery_path, "/"));
                        $vgallery_name = $arrVgalleryPath[0];
                }
                
				if($arrLimit && array_search($vgallery_name, $arrLimit) === false)
					continue;

			    $unic_id = $db->getField("smart_url", "Text", true);
			    $overview_order = $db->getField("overview_order", "Number", true);
			    
			    $overview_id = str_repeat("0", 3 - strlen($overview_order)) . $overview_order . "-" . $unic_id;
			    $overview_limit = $db->getField("overview_limit", "Number", true);
			    
			    $landing_page_group["contents"][$unic_id]["ID"]                             					= $db->getField("ID", "Number", true);
                $landing_page_group["contents"][$unic_id]["label"] 												= ffTemplate::_get_word_by_code($db->getField("name", "Text", true));						
			    $landing_page_group["contents"][$unic_id]["name"]												= $unic_id;
			    $landing_page_group["contents"][$unic_id]["class"]												= $db->getField("class", "Text", true);
			    $landing_page_group["contents"][$unic_id]["fluid"]												= $db->getField("fluid", "Number", true);
			    $landing_page_group["contents"][$unic_id]["grid"]												= (strlen($db->getField("grid", "Text", true)) ? explode(",", $db->getField("grid", "Text", true)) : "");
			    $landing_page_group["contents"][$unic_id]["wrap"]												= (strlen($db->getField("wrap", "Text", true)) ? explode(",", $db->getField("wrap", "Text", true)) : "");
			    $landing_page_group["contents"][$unic_id]["extra"]              								= (strlen($db->getField("extra", "Text", true)) ? explode(",", $db->getField("extra", "Text", true)) : "");
			    $landing_page_group["contents"][$unic_id]["sort"]												= $db->getField("sort", "Text", true);
			    $landing_page_group["contents"][$unic_id]["sort_method"]										= $db->getField("sort_method", "Text", true);
			    $landing_page_group["contents"][$unic_id]["menu_tag"]											= $db->getField("menu_tag", "Number", true);
			    $landing_page_group["contents"][$unic_id]["menu_search"]										= $db->getField("menu_search", "Number", true);
			    

				if($external) {
					$landing_page_group["contents"][$unic_id]["ext"] 											= $vgallery_name;
					$landing_page_group["contents"][$unic_id][$vgallery_name] 									= $external;
				} else {
					$landing_page_group["contents"][$unic_id]["data_source"][$vgallery_type][$vgallery_name]  	= $vgallery_path;
			    	$landing_page_group["source"][$vgallery_path]	                            				= $unic_id;
				}

				if($overview_limit > 0) 
				{
				    $landing_page_group["overview"][$overview_id]["ID"]												= $landing_page_group["contents"][$unic_id]["ID"];
				    $landing_page_group["overview"][$overview_id]["label"]											= $landing_page_group["contents"][$unic_id]["label"];
				    $landing_page_group["overview"][$overview_id]["name"]											= $landing_page_group["contents"][$unic_id]["name"];
				    $landing_page_group["overview"][$overview_id]["container"]["class"]								= $db->getField("overview_container_class", "Text", true);
				    $landing_page_group["overview"][$overview_id]["container"]["fluid"]								= $db->getField("overview_container_fluid", "Number", true);
				    $landing_page_group["overview"][$overview_id]["container"]["grid"]								= (strlen($db->getField("overview_container_grid", "Text", true)) ? explode(",", $db->getField("overview_container_grid", "Text", true)) : "");
				    $landing_page_group["overview"][$overview_id]["limit"]											= $overview_limit;
				    $landing_page_group["overview"][$overview_id]["sort"]											= $db->getField("overview_sort", "Text", true);
				    $landing_page_group["overview"][$overview_id]["sort_method"]									= $db->getField("overview_sort_method", "Text", true);
				    $landing_page_group["overview"][$overview_id]["items"]["class"]									= $db->getField("overview_item_class", "Text", true);
				    $landing_page_group["overview"][$overview_id]["items"]["fluid"]									= $db->getField("overview_item_fluid", "Number", true);
				    $landing_page_group["overview"][$overview_id]["items"]["grid"]									= (strlen($db->getField("overview_item_grid", "Text", true)) ? explode(",", $db->getField("overview_item_grid", "Text", true)) : "");
				    $landing_page_group["overview"][$overview_id]["wrap"]											= (strlen($db->getField("overview_wrap", "Text", true)) ? explode(",", $db->getField("overview_wrap", "Text", true)) : "");
				    $landing_page_group["overview"][$overview_id]["extra"]              							= (strlen($db->getField("overview_extra", "Text", true)) ? explode(",", $db->getField("overview_extra", "Text", true)) : "");
				}

			    //$landing_page_group["source"][$source_path]					= $unic_id;
		    } while($db->nextRecord());
	    } else {
	    	$landing_page_group = process_landing_tag_group_default($limit);
	    }
   }

    if($group)
        return $landing_page_group["contents"][$group];
    else
	    return $landing_page_group;
}