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
function system_layer_gallery($oPage = null) 
{
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");

    if(!$oPage) {
    	$oPage = $cm->oPage;
    	$return = true;
	}
    //$settings_path =& $oPage->user_vars["settings_path"];
    $ret_url = $_SERVER["REQUEST_URI"];
    //$content_full_size =& $oPage->user_vars["content_full_size"];
    $user_path = $globals->user_path;
        //ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());   

    $settings_path = $globals->settings_path;
    $selected_lang = $globals->selected_lang;

    //$userNID = get_session("UserNID");
    //$is_guest = (!$userNID || $userNID == MOD_SEC_GUEST_USER_ID);
	//$is_xhr = $cm->isXHR();
	
    /*
    if(!$ret_url)
        $ret_url = $_SERVER["REQUEST_URI"];
      */
    
    //$cache_page_contents = 0; // x cache_page
    //$globals->cache["layout_blocks"] = array(); // x cache_page
    //$globals->cache["section_blocks"] = array(); // x cache_page
    //$globals->cache["data_blocks"] = array(); // x cache_page
    //$globals->cache["ff_blocks"] = array(); // x cache_page
	
	
  /*  if(!$is_xhr) {
		if(check_function("system_set_media"))            
    		system_set_media($oPage, $settings_path, AREA_SHOW_NAVBAR_ADMIN);
        if(check_function("get_webservices"))
        	get_webservices(null, $oPage);
    } */
    
	if(ENABLE_STD_PERMISSION && check_function("get_file_permission")) {
	    $file_permission = get_file_permission($settings_path, "static_pages");
	    if (is_array($file_permission) && !check_mod($file_permission, 1, true)) {
	    	if(!defined("SKIP_MAIN_CONTENT")) define("SKIP_MAIN_CONTENT", true);
		}
	}   
	
    $oPage->output_buffer = "";
    
    if(!defined("SKIP_VG_LAYOUT")) {
        if($globals->fixed_pre["content"])
            $fixed_pre_content = implode("", $globals->fixed_pre["content"]);

        if(strlen($oPage->fixed_pre_content)) {
        	$fixed_pre_content = $fixed_pre_content . $oPage->fixed_pre_content;
            $oPage->fixed_pre_content = "";
        }
        
        if($globals->fixed_post["content"])
            $fixed_post_content = implode("", $globals->fixed_post["content"]);

        if(strlen($oPage->fixed_post_content)) {
        	$fixed_post_content = $oPage->fixed_post_content . $fixed_post_content;
            $oPage->fixed_post_content = "";
        }        
    } 
       
    $template = system_process_page(array(
        "user_path"                                 => $user_path
        , "settings_path"                           => $settings_path
		, "ff_contents"  							=> (defined("SKIP_MAIN_CONTENT")
    													? false
    													: $oPage->contents
    												)
    	, "fixed_pre_body"                          => ($globals->fixed_pre["body"]
                                                        ? implode("", $globals->fixed_pre["body"])
                                                        : ""
                                                    )
        , "fixed_pre_content"						=> $fixed_pre_content
    	, "fixed_post_content"						=> $fixed_post_content
    	, "fixed_post_body"						    => ($globals->fixed_post["body"]
                                                        ? implode("", $globals->fixed_post["body"])
                                                        : ""
                                                    )
    ));

    /*
    	, "search" 									=> $globals->search
    	, "navigation" 								=> $globals->navigation
    	, "ff_contents"								=> (defined("SKIP_MAIN_CONTENT") 
    													? false
    													: $oPage->contents
    												)*/    
    if(!count($template["stats"]["main_section"])) {
    	http_response_code(500);
		if(check_function("write_notification")) {
			write_notification("missing_main_section", "", "warning", null, $user_path);
		}
    } elseif($template["stats"]["notfound"] == $template["stats"]["main_content"]) {
        http_response_code(404);
        $globals->setSeo(array(
            "title" => ffTemplate::_get_word_by_code("not_found_title")
            , "description" => ffTemplate::_get_word_by_code("not_found_description")
        ), "detail");
	}

	$globals->cache["processed_block"] = $template["stats"]["processed_shard"];
	//if(!defined("DISABLE_CACHE") && check_function("system_set_cache_page")) {
	//	system_write_cache_page($user_path, $template["stats"]["processed_shard"]);
   // }


  //ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
    if(!$return) {
        if(!defined("SKIP_VG_LAYOUT")) {
			/*if(is_array($globals->seo) && count($globals->seo)) {
				if(array_key_exists("detail", $globals->seo)) {
					$seo_primary_block = "detail";
                } elseif(array_key_exists("detail-anagraph", $globals->seo)) {
                    $seo_primary_block = "detail-anagraph";                    
				} elseif(array_key_exists("page", $globals->seo)) {
					$seo_primary_block = "page";
                } elseif(array_key_exists("tag", $globals->seo)) {
                    $seo_primary_block = "tag";
				} elseif(array_key_exists("thumb", $globals->seo)) {
					$seo_primary_block = "thumb";
                } elseif(array_key_exists("thumb-anagraph", $globals->seo)) {
                    $seo_primary_block = "thumb-anagraph";
				} elseif(array_key_exists("media", $globals->seo)) {
					$seo_primary_block = "media";
				} 
			}       
			     
			if(!$seo_primary_block) {
			    $db = ffDB_Sql::factory();

				$sSQL = "SELECT static_pages.*
				            , static_pages_rel_languages.meta_title
				            , static_pages_rel_languages.meta_title_alt
				            , static_pages_rel_languages.meta_description
				            , static_pages_rel_languages.keywords
						FROM static_pages
					        INNER JOIN static_pages_rel_languages ON static_pages.ID = static_pages_rel_languages.ID_static_pages 
					        	AND static_pages_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
						WHERE static_pages.parent = " . $db->toSql(ffCommon_dirname($settings_path), "Text") . "
					        AND static_pages.name = " . $db->toSql(basename($settings_path), "Text") . "
					        AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number");
				$db->query($sSQL);
				if($db->nextRecord()) {
					$seo_primary_block = "page";

					$globals->seo[$seo_primary_block]["title"] 												= $db->getField("meta_title", "Text", true);
				    $globals->seo[$seo_primary_block]["title_header"] 										= $db->getField("meta_title_alt", "Text", true);
				    if(!$globals->seo[$seo_primary_block]["title_header"])
				    	$globals->seo[$seo_primary_block]["title_header"] 									= $globals->seo[$seo_primary_block]["title"];

				    if(!$globals->seo[$seo_primary_block]["title"])
				    	$globals->seo[$seo_primary_block]["title"] 											= $globals->seo[$seo_primary_block]["title_header"];
				    
				    $globals->seo[$seo_primary_block]["meta"]["description"][] 								= $db->getField("meta_description", "Text", true);
				    $globals->seo[$seo_primary_block]["meta"]["keywords"][] 								= $db->getField("keywords", "Text", true);
					
					$globals->meta 																			= $globals->seo[$seo_primary_block]["meta"];
					
					$globals->seo[$seo_primary_block]["ID"] 												= $db->getField("ID", "Number", true);
				}
			}

			$globals->page_title = $globals->seo[$seo_primary_block]["title"];
			$globals->meta = $globals->seo[$seo_primary_block]["meta"];
			$ID_seo_node = $globals->seo[$seo_primary_block]["ID"];
						
			$globals->seo["current"] = $seo_primary_block;*/
            if(check_function("system_set_media"))            
                system_set_media($oPage, $globals->settings_path, AREA_SHOW_NAVBAR_ADMIN, true);
                
            if(check_function("system_set_meta")) 
                system_set_meta($oPage);

		    $oPage->addEvent("on_tpl_parse", "write_user_vars" , ffEvent::PRIORITY_FINAL);

            if(AREA_SHOW_NAVBAR_ADMIN) {
            	$cms_options = array();
				if(AREA_SECTION_SHOW_MODIFY && is_array($template["navadmin"]) && count($template["navadmin"])) {
					$cms_options["editor"] = array();

					if(AREA_SEO_SHOW_MODIFY) { 
						$cms_options["editor"]["seo"] = true;
					}
					if(AREA_SITEMAP_SHOW_MODIFY && 0) { 
						$cms_options["editor"]["sitemap"] = array(
							"menu" => array("class" => "cms-editor-menu sitemap"
											, "icon" => cm_getClassByFrameworkCss("sitemap", "icon-tag", "2x")
											, "rel" => "add"
							)
						);
					}				
					if(AREA_LAYOUT_SHOW_MODIFY) { 
						$cms_options["editor"]["sitemap"] = array(
							"menu" => array("class" => "cms-editor-menu"
											, "icon" => cm_getClassByFrameworkCss("addnew", "icon-tag", "2x")
											, "rel" => "add"
							)
						);
					}
				} 

                $admin_menu["admin"]["unic_name"] = ($globals->page["template"]["path"] ?  "unic_custom_admin_menu" : "unic_admin_menu") . $settings_path;
                $admin_menu["admin"]["sections"] = $template["navadmin"];
                $admin_menu["admin"]["css"] = $oPage->page_css;
                $admin_menu["admin"]["js"] = $oPage->page_js;
                $admin_menu["admin"]["theme"] = $oPage->theme;
                $admin_menu["admin"]["international"] = ffTemplate::_get_word_by_code("", null, null, true);
                $admin_menu["admin"]["seo"] = array(
                    "src" => $globals->seo["current"] //$seo_primary_block
                    , "ID" => $globals->seo[$globals->seo["current"]]["ID"] //$ID_seo_node
                );
                $admin_menu["admin"]["option"] = $cms_options;
                $admin_menu["sys"]["path"] = $settings_path; 
                $admin_menu["sys"]["type"] = "admin_menu";
                //$admin_menu["sys"]["ret_url"] = $ret_url;

                if(check_function("set_template_var"))
                	$template["buffer"]["admin"] = '<input class="ajaxcontent" type="hidden" value="'.  get_admin_bar($admin_menu, VG_SITE_FRAME . (strpos($ret_url, "?") ? substr($ret_url, 0, strpos($ret_url, "?")) : $ret_url)) . '" />';

//                $serial_admin_menu = json_encode($admin_menu);
 //               $oPage->tpl_layer[0]->set_var("admin", '<input class="ajaxcontent" type="hidden" value="'. FF_SITE_PATH . VG_SITE_FRAME . (strpos($ret_url, "?") ? substr($ret_url, 0, strpos($ret_url, "?")) : $ret_url) . "?sid=" . set_sid($serial_admin_menu) . '" />');
            }
        }
        if($oPage->tpl_layer)
			$oPage->output_buffer = system_parse_page($template);
    } else {
        check_function("system_set_media");

    	return array("content" => preg_replace("/\n\s*/", "\n", system_parse_page($template))
    				, "media" => system_set_media_cascading(true)
    			);
    }
}  

function write_user_vars() {
	$globals = ffGlobals::getInstance("gallery");
	$cm = cm::getInstance();
	$seo = $globals->seo[$globals->seo["current"]];

	$cm->oPage->output_buffer["html"] = str_replace(
		array(
			"[[PAGE_TITLE]]"
			, "[[PAGE_HEADER]]"
			, "[[PAGE_DESCRIPTION]]"
			, "[[PAGE_KEYWORDS]]"
			, "[[PAGE_SMART_URL]]"
			, "[[PAGE_PERMALINK]]"
			, "[[PAGE_PERMALINK_PARENT]]"
		)
		, array(
			$seo["title"]
			, ($seo["title_header"] ? $seo["title_header"] : $seo["title"])
			, (is_array($seo["meta"]["description"]) ? implode(" ", $seo["meta"]	["description"]) : $seo["title"])
			, (is_array($seo["keywords"]) ? implode(" ", $seo["keywords"]) : "")
			, basename($globals->user_path)
			, $globals->user_path
			, ffCommon_dirname($globals->user_path)
		)
		, $cm->oPage->output_buffer["html"]
	);

}

function system_process_page_blocks($blocks, $params, &$template) 
{
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");
	if(is_array($blocks) && count($blocks))
	{
		$is_xhr = $cm->isXHR();
		foreach($blocks AS $block_key => $ID_block) 
		{
			if(!$template["blocks"][$ID_block])
				continue;
					
			if($template["blocks"][$ID_block]["processed"]) 
			{
				if($block_key != $template["blocks"][$ID_block]["processed"]) {
					$template["buffer"]["blocks"][$block_key] = $template["buffer"]["blocks"][$template["blocks"][$ID_block]["processed"]];
				}
			} 
			else 
			{
 				if($params["main_content"] && $template["blocks"][$ID_block]["use_in_content"] == 0) {
		            switch ($template["blocks"][$ID_block]["type"]) {
		                case "STATIC_PAGE_BY_DB":
		                case "GALLERY":
		                case "VIRTUAL_GALLERY":
		                case "MODULE":
		                    $template["blocks"][$ID_block]["use_in_content"] = 1;
		                    break;
		                case "FORMS_FRAMEWORK":
		                case "PUBLISHING":
		                case "STATIC_PAGE_BY_FILE":
		                case "VGALLERY_MENU":
		                case "LOGIN":
		                case "STATIC_PAGES_MENU":
		                case "VGALLERY_GROUP":
		                case "GALLERY_MENU":
		                case "LANGUAGES":
		                case "SEARCH":
		                case "ORINAV": 
		                case "ECOMMERCE":
		                case "USER":
		                default:
		                    $template["blocks"][$ID_block]["use_in_content"] = -1;
		                    break;
		            }
				}
		        if($is_xhr && $template["blocks"][$ID_block]["use_in_content"] < 0)
		            continue;

		        if($globals->page["template"] || $template["blocks"][$ID_block]["visible"]) 
		        {
		            $globals->cache["layout_blocks"][$template["blocks"][$ID_block]["ID"]]["last_update"] = $template["blocks"][$ID_block]["last_update"]; //array X cache_page
		            $globals->cache["layout_blocks"][$template["blocks"][$ID_block]["ID"]]["frequency"] = $template["blocks"][$ID_block]["frequency"]; //array X cache_page

		            if($params["no_content_block"]) {
						/**
						* Process Block Header
						*/	 
						$block = get_template_header(null, false, $template["blocks"][$ID_block]);  

						$buffer["content"] = $block["tpl"]["header"] . "{" . $template["blocks"][$ID_block]["smart_url"] . "}" . $block["tpl"]["footer"];
		            } else {
						$buffer = system_block_process($template["blocks"][$ID_block], $params); 
						$params = $buffer["params"];

						if(array_key_exists("template", $buffer))
							$template = array_replace_recursive ($template, $buffer["template"]);                                            
		            }
					if(strlen($buffer["content"])) {
						if($template["blocks"][$ID_block]["ajax"]) {
							$params["count_block"]++;

							if($params["main_content"]
								&& strlen($buffer["content"])
								&& (count($template["stats"]["main_section"]) > 1 || $template["blocks"][$ID_block]["use_in_content"] > 0)
							) {
								$params["count_block_content"]++;
								$params["reset_content_by_user"] = false;
							}

							if(!defined("SKIP_VG_LAYOUT"))
							{
								$template["buffer"]["blocks"][$block_key]["pre"] 														= $buffer["pre"];
								$template["buffer"]["blocks"][$block_key]["post"] 														= $buffer["post"];
							}

							$template["buffer"]["blocks"][$block_key]["ID"]																= $template["blocks"][$ID_block]["ID"];
							$template["buffer"]["blocks"][$block_key]["type"]															= $template["blocks"][$ID_block]["type_class"];
							$template["buffer"]["blocks"][$block_key]["group"]															= $template["blocks"][$ID_block]["type_group"];
							$template["buffer"]["blocks"][$block_key]["plugins"]														= $template["blocks"][$ID_block]["plugins"];
							$template["buffer"]["blocks"][$block_key]["class"]															= $template["blocks"][$ID_block]["class"];
							if(!$params["no_content"]) {
								$template["buffer"]["blocks"][$block_key]["content"]													= $template["blocks"][$ID_block]["fixed_pre_content"] . $buffer["content"] . $template["blocks"][$ID_block]["fixed_post_content"];
								//Append Blocks into Section
								if($params["section_key"])
									$template["buffer"]["sections"][$params["section_key"]]["content"] 									.= $template["buffer"]["blocks"][$block_key]["pre"]
																																			. $template["buffer"]["blocks"][$block_key]["content"]
																																			. $template["buffer"]["blocks"][$block_key]["post"];
							}

							//Section block count++
							if($params["ID_section"])
								$template["sections"][$params["ID_section"]]["processed_block"]++;

							//Page Shard count++
							$template["stats"]["processed_shard"]++;
						}
					} else {
						cache_writeLog("Block: " . $template["blocks"][$ID_block]["smart_url"] . " (ID: " . $ID_block . ") URL: " . $globals->user_path, "error_block_notfound");
					}

				}

				//store key in buffer and prevend rerender blocks
				$template["blocks"][$ID_block]["processed"] 																		= $block_key;
			}
	    }
	}
	return $params;
}

function system_process_page_sections($sections = null, $params, &$template)
{
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");
	if(!$sections)
		$sections                                       = $template["sections"];

	if(!$params["user_path"])
        $params["user_path"]                            = $globals->user_path;

    if(is_array($sections) && count($sections)) 
    {
    	$is_xhr = $cm->isXHR();
        foreach($sections AS $key => $value) 
        {
        	if(is_array($value))
        	{
        		$ID_section = $key;
        		$section_key = $value["name"];
        	}
        	else 
        	{
        		$ID_section = $value;
        		$section_key = $key;
        	}

        	if(!$template["sections"][$ID_section])
        		continue;

			if($template["sections"][$ID_section]["processed"]) 
			{
				if($section_key != $template["sections"][$ID_section]["processed"])
					$template["buffer"]["sections"][$section_key] = $template["buffer"]["sections"][$template["sections"][$ID_section]["processed"]];
			} 
			else 
			{
	            $params["main_content"] = $template["sections"][$ID_section]["is_main"];
	            
	            if($is_xhr && !$params["main_content"])
	                continue;

	            $globals->cache["section_blocks"][] = $template["sections"][$ID_section]["ID"]; //array X cache_page

	            $params["count_block"] = 0;
	            $params["count_block_content"] = 0;
	            $params["page_invalid"] = null;
	            $params["reset_content_by_user"] = null;

	            $params["section_key"] = $section_key;
	            $params["ID_section"] = $ID_section;
	            
	            if($template["sections"][$ID_section]["blocks"])
					$params = system_process_page_blocks($template["sections"][$ID_section]["blocks"], $params, $template);

					
				if($params["main_content"] && !$params["skip_landing_page"]) 
				{
					/**
					*  Load promary content if exists and content empty
					*/
					if(!$params["content_block"] && check_function("process_landing_page")) 
					{
						$buffer = ($params["no_content_block"]
							? process_landing_page($params["user_path"], $globals->seo["current"], $template["stats"]["primary_section"] == $ID_section)
							: "{block-default}"
						);

						if($buffer) 
						{
							$params["count_block"]++;
							$params["count_block_content"]++;

							//Section block count++	
	                        $template["sections"][$ID_section]["processed_block"]++;

							//Page Shard count++
							$template["stats"]["processed_shard"]++;

							if(!$params["no_content"]) {
								//Append Landing Page into Section
								$template["buffer"]["sections"][$section_key]["content"] .= $buffer;
							}
						}
					}

					/**
					* Set params for manage http status
					*/
					$template["stats"]["main_content"]++;
					if(!$params["count_ff"]) {
						$params["content_block"] = $params["count_block"] + $params["count_ff"];
					} elseif($params["page_invalid"] === true) {
                        $params["content_block"] = 0;
                    } else {
	                    if($params["reset_content_by_user"] !== false 
	                        && !$params["count_block_content"]
	                    )
	                        $params["content_block"] = 0;
						else
                            $params["content_block"] = $params["count_block"];
					}

					/**
					* manage by params 404 and content 404 
					*/
					if(!$params["content_block"] && check_function("process_html_page_error")) { 
						if($is_xhr) {
							/*http_response_code(204);  //nn funzionano i servizi custom TODO: da verificare vedi serivzi di giorgio
							exit;*/
						} else {
							$template["stats"]["notfound"]++;
							if(!$params["no_content_block"]) {
								//Append Landing Page into Section
								$template["buffer"]["sections"][$section_key]["content"] .= process_html_page_error();
							}
							//Section block count++	
		                    $template["sections"][$ID_section]["processed_block"]++;

							//Page Shard count++
							$template["stats"]["processed_shard"]++;							
						}
					}									
                }					
				
	            if($template["sections"][$ID_section]["processed_block"] || $template["sections"][$ID_section]["show_empty"]) 
	            {
	                if(!defined("SKIP_VG_LAYOUT")) 
	                {
	                    if($template["sections"][$ID_section]["wrap"] !== false) {
							$template["buffer"]["sections"][$section_key]["wrap"]["class"] 													= $template["sections"][$ID_section]["wrap"];
	                        $template["buffer"]["sections"][$section_key]["wrap"]["pre"]													= '<div class="' . $template["sections"][$ID_section]["wrap"] . '">'; //. $template["buffer"]["sections"][$section_key]["default"] . '</div>';
	                        $template["buffer"]["sections"][$section_key]["wrap"]["post"] 													= '</div>';
						}
						if(!$template["sections"][$ID_section]["hide"]) {
							$template["buffer"]["sections"][$section_key]["pre"] 															= '<div class="' 
																																				. implode(" ", array_filter($template["sections"][$ID_section]["class"])) . '"'
																																				. (!$params["framework_css"] && $template["sections"][$ID_section]["width"] > 0
																																					? ' style="' . $template["sections"][$ID_section]["width"] . $template["sections"][$ID_section]["sign"] . ';"'
																																					: ''
																																				) . '>';
							$template["buffer"]["sections"][$section_key]["post"] 															= '</div>'; 
						}
					}
					$template["buffer"]["sections"][$section_key]["class"] 																	= $template["sections"][$ID_section]["class"];
					$template["buffer"]["sections"][$section_key]["width"] 																	= $template["sections"][$ID_section]["width"];
					$template["buffer"]["sections"][$section_key]["sign"] 																	= $template["sections"][$ID_section]["sign"];
					//Append Sections into Layer 
					if(!$params["no_content"] && $params["layer_key"]) {
						$template["buffer"]["layers"][$params["layer_key"]]["content"] 														.= $template["buffer"]["sections"][$section_key]["pre"]
																																				. $template["buffer"]["sections"][$section_key]["wrap"]["pre"]
																																				. $template["buffer"]["sections"][$section_key]["content"]
																																				. $template["buffer"]["sections"][$section_key]["wrap"]["post"]
																																				. $template["buffer"]["sections"][$section_key]["post"];
					}

	                //Layers block count++	
	                if($params["ID_layer"])
	                    $template["layers"][$params["ID_layer"]]["processed_block"] 														= $template["layers"][$params["ID_layer"]]["processed_block"] + $template["sections"][$ID_section]["processed_block"];

					//Page Shard count++
					$template["stats"]["processed_shard"]++;
				}

				//store key in buffer and prevend rerender sections
				$template["sections"][$ID_section]["processed"] 																			= $section_key;
            }     
        }
    }
}

function system_process_page_layers($layers = null, $params, &$template)
{
	if(!$layers)
		$layers = $template["layers"];

	if(is_array($layers) && count($layers)) {
		foreach($layers AS $key => $value) 
        {
         	if(is_array($value))
        	{
        		$ID_layer = $key;
        		$layer_key = $value["name"];
        	}
        	else 
        	{
        		$ID_layer = $value;
        		$layer_key = $key;
        	}

        	if(!$template["layers"][$ID_layer])
        		continue;

        	$params["layer_key"] = $layer_key;
        	$params["ID_layer"] = $ID_layer;
			
			if($template["layers"][$ID_layer]["sections"])
				system_process_page_sections($template["layers"][$ID_layer]["sections"], $params, $template);

            //Layers
			if(!defined("SKIP_VG_LAYOUT")) 
            {
                if($template["layers"][$ID_layer]["wrap"] !== false) {
                    $template["buffer"]["layers"][$layer_key]["wrap"]["class"]																	= $template["layers"][$ID_layer]["wrap"];
                    $template["buffer"]["layers"][$layer_key]["wrap"]["pre"]																	= '<div class="' . $template["layers"][$ID_layer]["wrap"] . '">';
                    $template["buffer"]["layers"][$layer_key]["wrap"]["post"]																	= '</div>';
				}

				if(!$template["layers"][$ID_layer]["hide"] && ($template["layers"][$ID_layer]["processed_block"] || $template["layers"][$ID_layer]["show_empty"])) {
					$template["buffer"]["layers"][$layer_key]["pre"] 																			= '<div class="' 
																																					. implode(" ", array_filter($template["layers"][$ID_layer]["class"])) . '"'
																																					. (!$params["framework_css"] && $template["layers"][$ID_layer]["width"] > 0
																																						? ' style="' . $template["layers"][$ID_layer]["width"] . $template["layers"][$ID_layer]["sign"] . ';"'
																																						: ''
																																					) . '>';
					$template["buffer"]["layers"][$layer_key]["post"] 																			= '</div>'; 
				}
			}            

			$template["buffer"]["layers"][$layer_key]["class"]																					= $template["layers"][$ID_layer]["class"];
			$template["buffer"]["layers"][$layer_key]["width"]																					= $template["layers"][$ID_layer]["width"];
			$template["buffer"]["layers"][$layer_key]["sign"]																					= $template["layers"][$ID_layer]["sign"];
			//Append Layers into Container 
			if(!$params["no_content"] && $template["buffer"]["container"] !== false) {
				$template["buffer"]["container"]["content"] 																					.= $template["buffer"]["layers"][$layer_key]["pre"]
																																					. $template["buffer"]["layers"][$layer_key]["wrap"]["pre"]
																																					. $template["buffer"]["layers"][$layer_key]["content"]
																																					. $template["buffer"]["layers"][$layer_key]["wrap"]["post"]
																																					. $template["buffer"]["layers"][$layer_key]["post"];
			}
			//Page Shard count++
			$template["stats"]["processed_shard"]++;
        }
	}
}


function system_pre_process_page($params = null/*, $process_page = false*/) 
{
    $globals 					= ffGlobals::getInstance("gallery");
	$params["framework_css"]	= cm_getFrameworkCss();
	if(!$params["settings_path"])
		$params["settings_path"] = $globals->settings_path;
		
	
	//check_function("set_template_var");
	
	/** 
	* Process Structure
	*/
	if(check_function("system_get_sections")) {
        $template = system_get_sections($params["limit_section"], $params["settings_path"], true);

		/**
		* Init Modules
		*/
		if(is_array($template["blocks_by_type"]["MODULE"]) && count($template["blocks_by_type"]["MODULE"]) && check_function("process_init_modules")) {
			foreach($template["blocks_by_type"]["MODULE"] AS $ID_block => $smart_url) {
				get_module($template["blocks"][$ID_block]["location"]
					, $template["blocks"][$ID_block]["value"]
					, $template["blocks"][$ID_block]["params"]
					, array(
					    "own_location" => (strlen($globals->frame_smart_url) && $_REQUEST["out"] == "html"
					        ? false
					        : true
					    )
					    , "ajax" => $template["blocks"][$ID_block]["ajax"]
					)
				);
			}
		}        
        

	}

	
	//if($process_page)
	//	$template = system_process_page($params, $template);
	
	
	return $template;
}


function system_process_page($params, $template = null) {
    $cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");

    if(!$params["user_path"])
        $params["user_path"]	    = $globals->user_path;

	if(!$params["settings_path"])
		$params["settings_path"]	= $globals->settings_path;

	$params["search"] 				= $globals->search;
	$params["navigation"] 			= $globals->navigation;
	$params["framework_css"] 		= $globals->page["framework_css"];

	if(!$template)
		$template 					= ($globals->tpl
										? $globals->tpl
										: system_pre_process_page($params)
									);

	/**
	* Process FF Contents
	*/
	if($template["stats"]["primary_section"] && is_array($params["ff_contents"]) && count($params["ff_contents"])) 
	{
	    //ffErrorHandler::raise("ad", E_USER_ERROR, null, get_defined_vars());
	    $params["count_ff"] = 0;

		if(AREA_SHOW_NAVBAR_ADMIN) {
		    $navadmin_section = ($template["navadmin"][$template["stats"]["primary_section"]]
		        ? $template["stats"]["primary_section"]
		        : "unknown"
		    );

		    if(!is_array($template["navadmin"][$navadmin_section]["blocks"]))
		        $template["navadmin"][$navadmin_section]["blocks"] = array();
		}	        

		foreach($params["ff_contents"] AS $contents_key => $contents_value) {
		    if(strpos($contents_key, "/contents") === 0) {
		        if(strpos(substr($contents_key, strlen("/contents")), $params["settings_path"]) !== 0) {
		            continue;
		        }
		    }
		    
		    if(strpos($contents_key, "MD-") === 0)
		        continue;

		    switch($contents_key) {
		        case "SEARCH":
		            $content_unic_id = "S" . "0";
		            $params["count_ff"]++;

		            $content_layout[$content_unic_id]["prefix"] = "s";
		            $content_layout[$content_unic_id]["ID"] = $params["count_ff"];
		            $content_layout[$content_unic_id]["smart_url"] = ffCommon_url_rewrite($contents_key);
		            $content_layout[$content_unic_id]["title"] = $contents_key;
		            $content_layout[$content_unic_id]["type"] = "SEARCH";
		            $content_layout[$content_unic_id]["location"] = "Content";
		            //$content_layout[$content_unic_id]["width"] = $sections[$primary_main_section]["width"];
		            $content_layout[$content_unic_id]["visible"] = NULL;
		            if(check_function("get_layout_settings"))
		                $content_layout[$content_unic_id]["settings"] = get_layout_settings(NULL, "SEARCH");
		            $content_layout_sort = $content_layout[$content_unic_id]["settings"]["AREA_SEARCH_DEFAULT_SORT"];
		            break;
		        default:
		        
		            $content_unic_id = "FF" . $contents_key;
		            $params["count_ff"]++;
		            
		            $content_layout[$content_unic_id]["prefix"] = "FF";
		            $content_layout[$content_unic_id]["ID"] = $params["count_ff"];
		            $content_layout[$content_unic_id]["smart_url"] = ffCommon_url_rewrite("ff " . $contents_key);

		            if(is_object($contents_value["data"]) && get_class($contents_value["data"]) == "ffTemplate")
                         $content_layout[$content_unic_id]["title"] = ucwords(ffCommon_url_rewrite(ffGetFilename($contents_value["data"]->sTemplate), " "));                        
		            else
                        $content_layout[$content_unic_id]["title"] = $contents_key . " (" . "FORMS FRAMEWORK" . ")";

		            $content_layout[$content_unic_id]["type"] = "FORMS_FRAMEWORK";
		            $content_layout[$content_unic_id]["location"] = "Content";
		            //$content_layout[$content_unic_id]["width"] = $sections[$primary_main_section]["width"];
		            $content_layout[$content_unic_id]["visible"] = NULL;
		            if(check_function("get_layout_settings"))
		                $content_layout[$content_unic_id]["settings"] = get_layout_settings(NULL, "FORMS_FRAMEWORK");
		            $content_layout_sort = $content_layout[$content_unic_id]["settings"]["AREA_FF_DEFAULT_SORT"];
		    }
		    $tmp_content_layout = $content_layout[$content_unic_id];
		    $tmp_content_layout["settings"] = md5(serialize($tmp_content_layout["settings"]));
		    
		    if(AREA_SHOW_NAVBAR_ADMIN) {
				$template["navadmin"][$navadmin_section]["blocks"] = 
					array_slice($template["navadmin"][$navadmin_section]["blocks"], 0, $content_layout_sort, true)
					+ array($content_unic_id => $tmp_content_layout)
					+ array_slice($template["navadmin"][$navadmin_section]["blocks"], $content_layout_sort, NULL, true);
		    }
		        
		    switch($contents_value["data"]) {
		        case is_array($contents_value["data"]):
		            $content_layout[$content_unic_id]["content"] = print_r($contents_value["data"], true);
		            $globals->cache["ff_blocks"][] = md5($content_layout[$content_unic_id]["content"]); //array X cache_page
		            break;
		        case is_object($contents_value["data"]):
		            if(get_class($contents_value["data"]) == "ffTemplate") {
		                 $content_layout[$content_unic_id]["content"] = $contents_value["data"]->rpparse("main", false);
		                 $globals->cache["ff_blocks"][] = md5($content_layout[$content_unic_id]["content"]); 
		            } elseif(isset($contents_value["data"]->id) && isset($cm->oPage->components_buffer[$contents_value["data"]->id])) {
		                if(is_array($cm->oPage->components_buffer[$contents_value["data"]->id])) {
		                    $content_layout[$content_unic_id]["content"] = /*$cm->oPage->components_buffer[$contents_value["data"]->id]["headers"] .*/ $cm->oPage->components_buffer[$contents_value["data"]->id]["html"] /*. $cm->oPage->components_buffer[$contents_value["data"]->id]["footers"]*/;
		                } else {
		                    $content_layout[$content_unic_id]["content"] = $cm->oPage->components_buffer[$contents_value["data"]->id];
		                }
		                $globals->cache["ff_blocks"][] = $contents_value["data"]->id; //array X cache_page
		            }
		            break;
		        
		        default:
		            $content_layout[$content_unic_id]["content"] = $contents_value["data"];
		            $globals->cache["ff_blocks"][] = md5($contents_value["data"]); //array X cache_page
		    }
		    
			$template["sections"][$template["stats"]["primary_section"]]["blocks"] = 
				array_slice($template["sections"][$template["stats"]["primary_section"]]["blocks"], 0, $content_layout_sort, true)
				+ array($content_unic_id => $content_layout[$content_unic_id])
				+ array_slice($template["sections"][$template["stats"]["primary_section"]]["blocks"], $content_layout_sort, NULL, true);
		}
	}

	$template["fixed_pre_content"] 	= $params["fixed_pre_body"] . $params["fixed_pre_content"];
	$template["fixed_post_content"] = $params["fixed_post_content"] . $params["fixed_post_body"];
	
	if($globals->page["template"]) {
		$template["buffer"]["container"]["content"] = false;
		$params["skip_landing_page"] = true;

		system_process_page_blocks($globals->page["template"]["blocks"]["vars"], $params, $template);
		system_process_page_sections($globals->page["template"]["sections"]["vars"], $params, $template);
		system_process_page_layers($globals->page["template"]["layers"]["vars"], $params, $template);
	} else {
		system_process_page_layers($template["layers"], $params, $template);

	    //Off Canvas
		if(isset($template["offcanvas"])) {
			$template["buffer"]["layers"]["offcanvas"]["content"] 																							= $template["offcanvas"];
			if(!$params["no_content"])
				$template["buffer"]["container"]["content"] 																								.= $template["offcanvas"];
		}

		//not found
		if(!$params["no_content"] && !$template["buffer"]["container"]["content"]) {
			if(AREA_SHOW_NAVBAR_ADMIN && check_function("system_wizard")) {
				$template["buffer"]["container"]["content"] = system_wizard("struct");
				//da inserire il wizard
			} else {
     			if(check_function("process_html_page_error")) {
            		$template["buffer"]["container"]["content"] = process_html_page_error(404);
				}
	        }		
		}

		//Container
		if($template["container"]["wrap"]) {
			$template["buffer"]["container"]["wrap"]["class"] 																								= $template["container"]["wrap_class"];
			$template["buffer"]["container"]["wrap"]["pre"] 																								= '<div class="' . ($template["container"]["wrap_class"]
	        																																					? $template["container"]["wrap_class"]
	        																																					: "wrap-" . $template["container"]["class"]
																																							) . '">';
			$template["buffer"]["container"]["wrap"]["post"] 																								= '</div>';
			
		}
		
		$template["buffer"]["container"]["class"] 																											= $template["container"]["class"];
		$template["buffer"]["container"]["properties"] 																										= $template["container"]["properties"];
		$template["buffer"]["container"]["width"] 																											= $template["container"]["width"];
		$template["buffer"]["container"]["pre"] 																											= '<div class="' 
																																								. ($template["container"]["class"] 
																																									? $template["container"]["class"] 
																																									: "Content"
																																								) . '"'
																																								. (!$params["framework_css"] && $template["container"]["width"] > 0
																																									? ' style="' . $template["container"]["width"] . $template["container"]["sign"] . ';"'
																																									: ''
																																								) . ' ' . $template["container"]["properties"] . '>';
		$template["buffer"]["container"]["post"] 																											= '</div>'; 		
		
		if(!$params["no_content"]) {
			$template["buffer"]["container"]["default"] 																									= $template["fixed_pre_content"] 
																																								. $template["buffer"]["container"]["pre"]
																																								. $template["buffer"]["container"]["wrap"]["pre"]
																																									. $template["buffer"]["container"]["content"]
																																									. $template["buffer"]["container"]["wrap"]["post"]
																																									. $template["buffer"]["container"]["post"]
																																									. $template["fixed_post_content"];
	    }        
	
	}

	switch($params["output"]) {
		case "html":
			return system_parse_page($template);
		case "array":
		default:
			return $template;
	}
}


function system_parse_page($template) {
    $globals = ffGlobals::getInstance("gallery");
	if($globals->page["template"])
	{
		$tpl = ffTemplate::factory(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/pages" . ffCommon_dirname($globals->page["template"]["path"]));
	    $tpl->load_file(basename($globals->page["template"]["path"]), "main");
		if(is_array($tpl->DVars) && count($tpl->DVars)) {

			foreach($tpl->DVars AS $tpl_var => $tpl_ignore) 
			{
				if($tpl_var == "contents")
				{
					$tpl->set_var("contents", $template["buffer"]["container"]["default"]);
				} 
				else
				{
					$arrVarParam = null;
					if(strpos($tpl_var, ":") !== false) {
						$arrVarName = explode(":", $tpl_var);
						
						$var_name = $arrVarName[0];
						$var_param = $arrVarName[1];
						if(strpos($var_param, ".") !== false) {
							$arrVarParam = explode(".", $var_param);
							$var_param = $arrVarParam[0];
							unset($arrVarParam[0]);
						}
					} else {
						$var_name = $tpl_var;
						$var_param = "default";
					}
			
					if($var_param == "default")
					{
						$var_response = $template["buffer"][$globals->page["template"]["found"][$var_name]][$var_name]["pre"]
										. $template["buffer"][$globals->page["template"]["found"][$var_name]][$var_name]["wrap"]["pre"]
										. $template["buffer"][$globals->page["template"]["found"][$var_name]][$var_name]["content"]
										. $template["buffer"][$globals->page["template"]["found"][$var_name]][$var_name]["wrap"]["post"]
										. $template["buffer"][$globals->page["template"]["found"][$var_name]][$var_name]["post"];
					} else 
						$var_response = $template["buffer"][$globals->page["template"]["found"][$var_name]][$var_name][$var_param];

					if(is_array($var_response)) {
						if(is_array($arrVarParam) && count($arrVarParam)) {
							$tmp_var_response = array();
							foreach($arrVarParam AS $param_value) {
								$tmp_var_response[] = $var_response[$param_value];
							}
							$var_response = implode(" ", array_filter($tmp_var_response));
						} else {
							$var_response = implode(" ", $var_response);
						}
					}

					$tpl->set_var($tpl_var, $var_response);
				}
			
			}
		}
	     //  ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());   
		
		$tpl->set_var("fixed_pre_content", $template["fixed_pre_content"]);
		$tpl->set_var("fixed_post_content", $template["fixed_post_content"]);
		
		$buffer = $tpl->rpparse("main", false);
	} else {
		$buffer = $template["buffer"]["container"]["default"];
	}
	$buffer = $template["buffer"]["admin"] . $buffer;

	return $buffer;
}



function system_block_process($layout, $params = array()) {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();
    $res = array("content" => "");

    $unic_id = $layout["prefix"] . $layout["ID"];

    $params["xhr"] = $cm->isXHR();
    $params["user_path_shard"] = $globals->user_path_shard;

    //if($layout["type"] == "ECOMMERCE" || (isset($layout["use_ajax"]) && $layout["use_ajax"])) {
    if((isset($layout["ajax"]) && $layout["ajax"]) && $layout["ajax_on_ready"] != "preload" && !$cm->isXHR()) {
		/**
		* Process Block Header
		*/	
        $tpl = null;

		if(check_function("set_template_var"))
			$block = get_template_header($params["user_path"], null, $layout, $tpl);

		$res["pre"] 		= $block["tpl"]["header"];
		$res["post"] 		= $block["tpl"]["footer"];
		$res["content"] 	= "";
		$res["default"] 	= $res["pre"] . $res["content"] . $res["post"];

		//        $res["content"] = "<div id=\"" . $unic_id . "\" class=\"block ajaxcontent\" data-ready=\"" . $layout["ajax_on_ready"] . "\" data-event=\"" . $layout["ajax_on_event"] . "\" data-src=\"" . $serial_frame_url . "\"></div>";
        
        if(is_array($layout["settings"]) && count($layout["settings"])) {
            foreach($layout["settings"] AS $setting_key => $setting_value) {
                if(strpos($setting_key, "_PLUGIN") !== false) {
                    setJsRequest($layout["settings"][$setting_key]);
                }
            }
        }

        switch ($layout["type"]) {
            case "STATIC_PAGE_BY_DB":
                if(AREA_STATIC_SHOW_MODIFY || AREA_STATIC_SHOW_ADDNEW) {
                	get_admin_bar();
                }
                break;
            case "STATIC_PAGE_BY_FILE":
                if(AREA_STATIC_SHOW_MODIFY || AREA_STATIC_SHOW_ADDNEW) {
                    get_admin_bar();
                }
                break;
            case "STATIC_PAGES_MENU":
                if(AREA_STATIC_SHOW_MODIFY || AREA_STATIC_SHOW_ADDNEW) {
                    get_admin_bar();
                }
                break;
            case "VIRTUAL_GALLERY":
                if(check_function("set_field_gmap")) { 
                    set_field_gmap();
                }
            case "GALLERY":
            case "PUBLISHING":
            case "VGALLERY_MENU":
            case "VGALLERY_GROUP":
            case "GALLERY_MENU":
                if(AREA_VGALLERY_SHOW_MODIFY || AREA_VGALLERY_SHOW_ADDNEW) {
                    get_admin_bar();
                }
                /*
                $db = ffDB_Sql::factory();
                    $db->query("SELECT thumb_display_view_mode
                                            , preview_display_view_mode 
                                        FROM settings_thumb");
                    if($db->nextRecord()) {
                        do {
                            if(strlen($db->getField("thumb_display_view_mode")->getValue()))
                                setJsRequest($db->getField("thumb_display_view_mode")->getValue());
                            if(strlen($db->getField("preview_display_view_mode")->getValue()))
                                setJsRequest($db->getField("preview_display_view_mode")->getValue());
                        } while($db->nextRecord());
                    }
                    
                    $db->query("SELECT display_view_mode_thumb
                        			, display_view_mode_detail
                        		FROM vgallery_fields");
                    if($db->nextRecord()) {
                        do {
                            if(strlen($db->getField("display_view_mode_thumb", "Text", true)))
                                setJsRequest($db->getField("display_view_mode_thumb", "Text", true));
                            if(strlen($db->getField("display_view_mode_detail", "Text", true)))
                                setJsRequest($db->getField("display_view_mode_detail", "Text", true));
                        } while($db->nextRecord());
                    }
                }*/
                break;
            case "MODULE":
                if(MODULE_SHOW_CONFIG) {
                    get_admin_bar();
                }
                break;
            case "ECOMMERCE":
                if(AREA_ECOMMERCE_SHOW_MODIFY)
                    get_admin_bar();
                break;
            case "LANGUAGES":
                if(AREA_LANGUAGES_SHOW_MODIFY)
                    get_admin_bar();
                break;
            case "SEARCH":
                if(AREA_SEARCH_SHOW_MODIFY)
                    get_admin_bar();
                break;
            case "LOGIN":
                if(AREA_LOGIN_SHOW_MODIFY)
                    get_admin_bar();
                break;
            case "USER":
                if(AREA_USERS_SHOW_MODIFY)
                    get_admin_bar();
                break;
            case "FORMS_FRAMEWORK":
                    get_admin_bar();
                break;
            default:
                if(defined("AREA_" . $layout["type"] . "_SHOW_MODIFY") && constant("AREA_" . $layout["type"] . "_SHOW_MODIFY"))
                    get_admin_bar();
        }
    }
    else 
    {
        switch ($layout["type"]) {
            case "STATIC_PAGE_BY_DB":
                if(check_function("process_static_page"))
                    $res = process_static_page($layout["type"], $layout["db"]["value"], $params["user_path"], $layout);

                break;
            case "STATIC_PAGE_BY_FILE":
                if(check_function("process_static_page"))
                    $res = process_static_page($layout["type"], $layout["db"]["value"], $params["user_path"], $layout);

                break;
            case "GALLERY":
                if(strlen($layout["db"]["real_path"]) && $layout["db"]["real_path"] != "/") {
                    if(strlen($layout["db"]["params"]) && strpos($layout["db"]["real_path"], $layout["db"]["params"]) === 0) {
                        $available_path = substr($layout["db"]["real_path"], strlen($layout["db"]["params"]));
                    } else {
                        if(strpos($params["settings_path"], stripslash($layout["db"]["real_path"])) === 0) {
                            $available_path = substr($params["settings_path"], strlen(stripslash($layout["db"]["real_path"])));
                        } else {
                            $available_path = $params["settings_path"];
                        }
                    }
                } else {
                    $available_path = $params["settings_path"];
                }
                
                $real_path = realpath(DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
                if((!$params["main_content"] || $layout["use_in_content"] == "-1") && $real_path === false && strlen($available_path) && $available_path != "/") {
                    do {
                        $available_path = ffCommon_dirname($available_path);
                        $real_path = realpath(DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
                        if($real_path !== false)
                            break;
                    } while($available_path != "/");
                }

				/*if(!$params["main_content"] || (strpos($params["settings_path"], stripslash($layout["db"]["value"]) . $available_path) === 0 && is_dir(DISK_UPDIR . $params["settings_path"]))
				) {
					$valid_gallery_path = true;
				} else {
					$valid_gallery_path = true;
				}*/

				if($real_path) {
	                if(!is_dir($real_path)) {  
	                    $available_path = $layout["db"]["value"];
	                    $real_path = realpath(DISK_UPDIR . stripslash($layout["db"]["value"]));
	                } else {  
	                    if(strpos($available_path, $layout["db"]["value"]) === 0) {
	                        $available_path = stripslash(substr($available_path, strlen($layout["db"]["value"])));
	                    } else {
	                        $available_path = stripslash($layout["db"]["value"]) . stripslash($available_path);
	                    }
	                }

	                if($available_path == "")
	                    $available_path = "/";

	                if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
	                    $file_permission = get_file_permission($available_path, "files", null, true);
	                    
	                //File permessi Cartella (controllo se l'utente ha diritti di lettura)
	                if (check_mod($file_permission, 1, true, AREA_GALLERY_SHOW_MODIFY)) {
	                    if(is_dir($real_path)) {
	                        $rst_file = array();
	                        $rst_dir = array();
	                        $arr_real_path = glob($real_path . "/*");
	                        if(is_array($arr_real_path) && count($arr_real_path)) {
	                            foreach ($arr_real_path AS $real_file) { 
	                                $file = str_replace(DISK_UPDIR, "", $real_file);
	                                $description = "";
	                                if ((is_dir($real_file) && basename($real_file) != CM_SHOWFILES_THUMB_PATH /*&& basename($real_file) != GALLERY_TPL_PATH*/) || (is_file($real_file) && strpos(basename($real_file), "pdf-conversion") === false) && strpos(basename($real_file), ".") !== 0) {
	                                    if(ENABLE_STD_PERMISSION && check_function("get_file_permission"))
	                                        $file_permission = get_file_permission($file);
	                                    if (check_mod($file_permission, 1, true, AREA_GALLERY_SHOW_MODIFY)) {
	                                        $rst_dir[$file]["permission"] = $file_permission;
	                                    }
	                                }
	                            }
	                        }
	                        $rst_item = array_merge($rst_dir, $rst_file);
	                        if(check_function("process_gallery_thumb"))
	                            $res = process_gallery_thumb($rst_item, $available_path, NULL, $params["user_path"], NULL, $layout);
	                    } elseif(is_file($real_path)) {
	                        if(check_function("process_gallery_view"))
	                            $res = process_gallery_view($available_path, NULL, $params["user_path"], $layout);
	                    }
	                    if($params["page_invalid"] === null) {
	                        $params["page_invalid"] = false;
	                    }
	                } else {
	                    $res["content"] = ffTemplate::_get_word_by_code("error_access_denied");
	                }
				}
                break;    
            case "MODULE":
                if(isset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
                    if(is_array($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
                        $cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"]);
                        $cm->oPage->tpl[0]->parse("SectWidgetsHeaders", true);

                        $res["content"] = /*$cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"] .*/ $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["html"] /*. $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]*/;

                        $cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]);
                        $cm->oPage->tpl[0]->parse("SectWidgetsFooters", true);
                    } else {
                        $res["content"] = $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])];
                    }
                    
                    unset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
                }
                if(!strlen($res["content"]) && isset($cm->oPage->contents["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
                    $res["content"] = $cm->oPage->contents["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["data"];
                    unset($cm->oPage->contents["MD-" . $layout["location"] . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
                }
                
                if(is_object($res["content"])) {
                    if(isset($cm->oPage->components_buffer[$res["content"]->id])) {
                        $res["content"] = /*$cm->oPage->components_buffer[$res["content"]->id]["headers"] .*/ $cm->oPage->components_buffer[$res["content"]->id]["html"] /*. $cm->oPage->components_buffer[$res["content"]->id]["footers"]*/;
                    } else {
                        $res["content"] = "";
                    }
                }
                if(strlen($res["content"])) { 
                    setJsRequest($layout["settings"]["AREA_MODULE_PLUGIN"]);
				    /**
				    * Admin Father Bar
				    */                    
                    if(AREA_MODULES_SHOW_MODIFY) {
                        $admin_menu["admin"]["unic_name"] = $unic_id;
                        $admin_menu["admin"]["title"] = $layout["title"] . ": " . $params["user_path"];
                        $admin_menu["admin"]["class"] = $layout["type_class"];
                        $admin_menu["admin"]["group"] = $layout["type_group"];
                        $admin_menu["admin"]["adddir"] = "";
                        $admin_menu["admin"]["addnew"] = "";
                        $admin_menu["admin"]["modify"] = "";
                        $admin_menu["admin"]["delete"] = "";
                        $admin_menu["admin"]["extra"] = "";

                        $admin_menu["admin"]["ecommerce"] = "";
                        if(AREA_LAYOUT_SHOW_MODIFY) {
                            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
                            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
                        }
                        $admin_menu["admin"]["setting"] = ""; //$layout["type"];
                        if(MODULE_SHOW_CONFIG) {
                            $admin_menu["admin"]["module"]["value"] = $layout["db"]["value"];
                            $admin_menu["admin"]["module"]["params"] = $layout["db"]["params"];
                        }

			            if(is_file(FF_DISK_PATH . VG_ADDONS_PATH . "/" . $layout["db"]["value"] . "/fields." . FF_PHP_EXT))
		                    $admin_menu["admin"]["module"]["extra"] = get_path_by_rule("addons") . "/". $layout["db"]["value"] . "/" . $layout["db"]["params"] . "/fields";
			            else
		                    $admin_menu["admin"]["module"]["extra"] = "";
                        
                        $admin_menu["sys"]["path"] = $params["user_path"];
                        $admin_menu["sys"]["type"] = "admin_toolbar";
                        //$admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
                    }
                    
                    set_cache_data("M", md5($layout["db"]["value"] . "-" . $layout["db"]["params"]));
                    //$globals->cache["data_blocks"]["M" . "" . "-" . md5($layout["db"]["value"] . "-" . $layout["db"]["params"])] = $layout["db"]["value"] . "-" . $layout["db"]["params"];

					/**
					* Process Block Header
					*/	
                    $tpl = null;
					if(check_function("set_template_var")) {
						$block["class"]["type"] = $layout["db"]["value"];
						$block["class"]["default"] = $layout["db"]["params"];

						$block = get_template_header($params["user_path"], $admin_menu, $layout, $tpl, $block);
					}

					$res["pre"] 		= $block["tpl"]["header"];
					$res["post"] 		= $block["tpl"]["footer"];
					
					$res["default"] 	= $res["pre"] . $res["content"] .  $res["post"];
                }  
                break;
            case "VIRTUAL_GALLERY":
            	if(check_function("vgallery_init")) {
            		$res = vgallery_init($params, $layout, $globals->data_storage);
            	}

				if($params["page_invalid"] !== false && is_bool($res["page_invalid"])) {
	                $params["page_invalid"] = $res["page_invalid"];
	            }
            	break;                
            case "VIRTUAL_GALLERY2":
				$arrVGalleryParam = array();
				$vgallery_group = null;
                $vgallery_name = $layout["db"]["value"];
                //echo $layout["db"]["real_path"] . " hhh " . $params["user_path"]; 
				$source_user_path = $layout["db"]["real_path"];
				$available_path = $params["settings_path"];

				if(strlen($params["settings_path"]) > strlen($layout["db"]["real_path"])) {
					if(!$layout["db"]["real_path"] || $layout["db"]["real_path"] == "/") { 
						$source_user_path = null;
					} else {
						$index = strpos($params["settings_path"], $layout["db"]["real_path"]);
						$available_path = substr($params["settings_path"], $index + strlen($layout["db"]["real_path"]));
						$source_user_path = substr($params["settings_path"], 0, $index + strlen($layout["db"]["real_path"]));
						
						$source_user_path_count_slash = substr_count($source_user_path, "/");
						$arrUserPath = explode("/", $params["user_path"]);
						$arrFinalPath = array_slice($arrUserPath, 0, $source_user_path_count_slash + 1);

						$source_user_path = implode("/", $arrFinalPath);
					}
				} else {
					$available_path = "";
					$source_user_path = $params["user_path"];
				} 
				
/*
				if(strlen($layout["db"]["real_path"]) && $source_user_path != "/") {
	                if($layout["db"]["real_path"] == $available_path) {
	                    $available_path = "";
	                } else {
	                    $available_path = str_replace($source_user_path . "/", "/", $available_path);
	                }
	            }*/

                $buffer_vg_view = "";
                $buffer_vg_thumb["content"] = "";
                $buffer_vg_parent["content"] = "";

                switch($vgallery_name) {
                	case "anagraph":
                		$vgallery_type = "anagraph";
	                    //only 1 subdir
	                    $virtual_path = stripslash($available_path);
	                    
	                    if($layout["db"]["params"] > 0) {
							$arrVgalleryGroup = array(
								"ID" => $layout["db"]["params"]
							);
	                    
	                    	$arrAvailablePath = explode("/", $virtual_path);
	                    	if(count($arrAvailablePath) > 1) {
	                    		$check_vgallery_dir = false;
	                    	} else {
	                    		$check_vgallery_dir = true;
	                    	}
						} else {
							if($virtual_path)
								$check_vgallery_dir = false;
							else
								$check_vgallery_dir = true;
						}
                		break;
                	case "files":
                		break; 
                	default: 
 						$vgallery_type = "vgallery";
	                    $virtual_path = "/" . $vgallery_name . stripslash($layout["db"]["params"]) . stripslash($available_path);
	                    if(!$virtual_path)
	                        $virtual_path = "/" . $vgallery_name;

	                    $check_vgallery_dir = get_vgallery_is_dir(basename($virtual_path), ffCommon_dirname($virtual_path));                	
                }
//echo $virtual_path . "<br><br>\n";
				if($check_vgallery_dir) {
					//parent vgallery
					if($layout["settings"]["AREA_VGALLERY_SHOW_THUMB_PARENT"] && $virtual_path != $source_user_path) {
	                    if(check_function("process_vgallery_view"))
	                        $buffer_vg_view = process_vgallery_view(
	                        		$virtual_path . $globals->user_path_shard
	                        		, $vgallery_name
	                        		, array(
	                        			"source_user_path" => $source_user_path
	                        			, "search" => $params["search"]
                                        , "navigation" => $params["navigation"]
	                        		)
	                        		, $layout
	                        	);
					}

	                //vgallery_thumb
	                if(check_function("process_vgallery_thumb"))
	                    $buffer_vg_thumb = process_vgallery_thumb(
	                    		$virtual_path
	                    		, $vgallery_type
	                    		, array(
	                    			"source_user_path" => $source_user_path
	                    			, "user_path" => $params["user_path"]
	                    			, "group" => $arrVgalleryGroup
	                    			, "search" => $params["search"]
                                    , "navigation" => $params["navigation"]
                                    , "vgallery_name" => $vgallery_name
                                    , "template_skip_hide" => $cm->isXHR()
	                    		)                    
	                    		, $layout
	                    	);
                    if($layout["settings"]["AREA_VGALLERY_SHOW_THUMB_TOP"]) {
                        $res["content"] = $buffer_vg_thumb["content"] . (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "");
                    } else {
                        $res["content"] = (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "") . $buffer_vg_thumb["content"];
                    }
                    
                    $res["pre"] = $buffer_vg_thumb["pre"];
                    $res["post"] = $buffer_vg_thumb["post"];
				} else {
					/**
					*  Check Adv Group Params  
					*/
 					if(ffCommon_dirname($virtual_path) != "/") {
		                if(check_function("get_vgallery_group"))
		                    $arrVgalleryGroup = get_vgallery_group(basename($virtual_path), "ID", ffcommon_dirname($virtual_path));

		                if(is_array($arrVgalleryGroup)) {
		                    $virtual_path = ffCommon_dirname($virtual_path);
		                    $vgallery_group_name = $arrVgalleryGroup["name"];
		                    $enable_child = $arrVgalleryGroup["enable_child"];
		                    if($arrVgalleryGroup["count"]) {
		                        $vgallery_group["ID"] = $arrVgalleryGroup["field"];
		                        $vgallery_group["name"] = $vgallery_group_name;
		                    } 
		                }
		            }	

					//vgallery view
	                if($layout["settings"]["AREA_VGALLERY_SHOW_PREVIEW"]) {
	                    if(check_function("process_vgallery_view")) {
	                        $buffer_vg_view = process_vgallery_view(
	                        		$virtual_path . $globals->user_path_shard
	                        		, $vgallery_name
	                        		, array(
	                        			"source_user_path" => $source_user_path
	                        			, "group" => $vgallery_group
	                        			, "search" => $params["search"]
                                        , "navigation" => $params["navigation"]
	                        		)
	                        		, $layout
	                        	);
						}
	                }

					//parent vgallery
					if($layout["settings"]["AREA_VGALLERY_SHOW_PREVIEW_PARENT"] && ffCommon_dirname($virtual_path) != "/") {
	                    if(check_function("process_vgallery_thumb")) {
	                        $buffer_vg_parent = process_vgallery_thumb(
	                        		ffCommon_dirname($virtual_path)
	                        		, $vgallery_name
	                        		, array(
	                        			"source_user_path" => ffCommon_dirname($source_user_path)
	                        			, "user_path" => $params["user_path"]
	                        			, "group" => $arrVgalleryGroup
	                        			, "search" => $params["search"]
                                        , "navigation" => $params["navigation"]
                                        , "vgallery_name" => $vgallery_name
                                        , "template_skip_hide" => $cm->isXHR()
	                        		)
	                        		, $layout
	                        	);
						}
					}
					
                    if($layout["settings"]["AREA_VGALLERY_SHOW_PREVIEW_TOP"]) {
                        $res["content"] = (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "") . $buffer_vg_parent["content"];
                    } else {
                        $res["content"] = $buffer_vg_parent["content"] . (is_array($buffer_vg_view) ? $buffer_vg_view["content"] : "");
                    }    

                    $res["pre"] = $buffer_vg_view["pre"];
                    $res["post"] = $buffer_vg_view["post"];
				}

				if($params["page_invalid"] !== false && $buffer_vg_view === false) {
                    $params["page_invalid"] = true;
                } else {
                    if(strlen($res["content"])) {
                        $params["page_invalid"] = false;
                    }
                }

                break;
            case "PUBLISHING": 
                $publish = explode("_", $layout["db"]["value"]);
                if(is_array($publish) && count($publish) == 2) {
                    $publishing = array();
                    $publishing["ID"] = $publish[1];
                    $publishing["src"]= $publish[0];

                    $source_user_path = $layout["db"]["params"] 
                                            ? $layout["db"]["params"]
                                            : (strlen($layout["db"]["real_path"]) &&  $layout["db"]["real_path"] != "/"
                                                ? $layout["db"]["real_path"]
                                                : NULL
                                            ); 
                    if($publish[0] == "gallery") {
                        if(check_function("process_gallery_thumb"))
                            $res = process_gallery_thumb(NULL, NULL, NULL, $source_user_path, $publishing, $layout);
                    } elseif($publish[0] == "vgallery" || $publish[0] == "anagraph") {
                        if(check_function("process_vgallery_thumb"))
                            $res = process_vgallery_thumb(
                            		NULL
                            		, "publishing"
                            		, array(
                            			"source_user_path" => $source_user_path
                            			, "user_path" => $params["user_path"]
                            			, "allow_insert" => false
                            			, "publishing" => $publishing
                            		)
                            		, $layout
                            	);
                    }                                               
                }
                break;
            case "VGALLERY_MENU":
                $part_virtual_path = explode("/", $layout["db"]["value"]);
                $vgallery_name = $part_virtual_path[1];
                unset($part_virtual_path[0]);
                unset($part_virtual_path[1]);

                $virtual_path = "/" . implode("/", $part_virtual_path);
                
                $source_user_path = $layout["db"]["params"] 
                                        ? $layout["db"]["params"] 
                                        : (strlen($layout["settings"]["AREA_VGALLERY_START_PATH"])
                                            ? $layout["settings"]["AREA_VGALLERY_START_PATH"]
                                            : $layout["db"]["real_path"]
                                        );

                if($virtual_path != ffCommon_dirname($virtual_path)) {
                    $source_user_path = str_replace($virtual_path, "", $source_user_path);
                }
                                                                                
                if(check_function("process_vgallery_menu"))
                    $res = process_vgallery_menu($virtual_path, $vgallery_name, $source_user_path, $layout); 

                break;
            case "GALLERY_MENU":
                if($layout["settings"]["AREA_DIRECTORIES_SHOW_ONLYHOME"]) {
                    $available_path = $layout["db"]["value"];
                    $source_user_path = $layout["db"]["params"]
                                            ? $layout["db"]["params"]
                                            : NULL; 
                } else {
                    if($layout["db"]["real_path"] != "/") {
                        if(strpos($globals->settings_path, $layout["db"]["real_path"]) === 0) {
                            $available_path = substr($globals->settings_path, strlen($layout["db"]["real_path"]));
                        } else {
                            $available_path = $globals->settings_path;
                        }
                    } else {
                        $available_path = $globals->settings_path;
                    }
                    
                    $real_path = realpath(DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
                    if($real_path === false && $available_path != "/") {
                        do {
                            $available_path = ffCommon_dirname($available_path);
                            $real_path = realpath(DISK_UPDIR . stripslash($layout["db"]["value"]) . $available_path);
                            if($real_path !== false)
                                break;
                        } while($available_path != "/");
                    }

                    $source_user_path = $layout["db"]["params"]
                                            ? $layout["db"]["params"] . stripslash($available_path)
                                            : NULL; 

                    if(!is_dir($real_path)) {  
                        $available_path = $layout["db"]["value"];
                    } else {  
                        if(strpos($available_path, $layout["db"]["value"]) === 0) {
                            $available_path = stripslash(substr($available_path, strlen($layout["db"]["value"])));
                        } else {
                            $available_path = stripslash($layout["db"]["value"]) . stripslash($available_path);
                        }
                    }

                    if($available_path == "")
                        $available_path = "/";
                }

                if(check_function("process_gallery_menu"))
                    $res = process_gallery_menu($available_path, $source_user_path, $layout);
                break;
            case "STATIC_PAGES_MENU":
            	if($layout["db"]["value"] == "/home")
            		$layout["db"]["value"] = "/";

                if($layout["settings"]["AREA_STATIC_FOLLOW_PATH"] && (strlen($layout["db"]["value"]) && strpos($params["settings_path"], $layout["db"]["value"]) === 0)) {
                    if($layout["settings"]["AREA_STATIC_SHOW_ONLYHOME"]) {
                        if(strlen(stripslash($layout["db"]["value"])) && strpos($params["settings_path"], $layout["db"]["value"]) === 0) {
                            $tmp_ArrPath = explode("/", substr($params["settings_path"], strlen($layout["db"]["value"])));
                            $virtual_path = $layout["db"]["value"] . "/" . $tmp_ArrPath[1];
                        } else {
                            $tmp_ArrPath = explode("/", $params["settings_path"]);
                            $virtual_path = "/" . $tmp_ArrPath[1];
                        }
                    } else {
                        $virtual_path = $params["settings_path"];
                    }
                } else {
					$virtual_path = $layout["db"]["value"]; 
                }

                if(check_function("process_static_menu"))
                    $res = process_static_menu($virtual_path, $params["user_path"], null, $layout);
				break;
            case "VGALLERY_GROUP":
                if($layout["db"]["real_path"] != "/") {
                    if($layout["db"]["real_path"] == $globals->settings_path) {
                        $available_path = "";
                    } else {
                        $available_path = str_replace($layout["db"]["real_path"] . "/", "/", $globals->settings_path);
                    }
                } else
                    $available_path = $globals->settings_path;
                    
                 
                $virtual_path = stripslash($available_path);

                $virtual_path = stripslash($layout["db"]["params"]) . stripslash($available_path);
                if(strlen($virtual_path)) {
                    if(check_function("get_vgallery_group"))
                        $vgallery_group = get_vgallery_group(basename($globals->user_path));
                    if($vgallery_group) {
                        $globals->user_path = ffCommon_dirname($globals->user_path);
                        $virtual_path = ffCommon_dirname($virtual_path);
                    } 

                    $sSQL = "SELECT vgallery_groups.ID
                                , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
                            FROM vgallery_nodes 
                                INNER JOIN vgallery_fields ON vgallery_fields.ID_type = vgallery_nodes.ID_type
                                INNER JOIN vgallery_groups_fields ON vgallery_groups_fields.ID_fields = vgallery_fields.ID
                                INNER JOIN vgallery_groups ON vgallery_groups.ID = vgallery_groups_fields.ID_group
                            WHERE 
                                vgallery_groups.ID_menu = " . $db->toSql($layout["db"]["value"]) . "
                                AND " . $db->toSql($virtual_path) . " LIKE CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name, '%')
                            ORDER BY LENGTH(CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) DESC
                            LIMIT 1";
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $real_path = str_replace($db->getField("full_path", "Text", true), "", $virtual_path);
                        $real_user_path = $globals->user_path;
                        if(strlen($real_path)) {
                            do {
                                $real_user_path = ffcommon_dirname($globals->user_path);
                                $real_path = ffcommon_dirname($real_path);
                            } while($real_path != "/");
                            
                        }
                        
                        if(check_function("process_vgallery_menu_group"))
                            $res = process_vgallery_menu_group($real_user_path, $layout["db"]["value"], null, $layout);
                    }
                }
                break;
            case "ECOMMERCE":
                if(check_function("ecommerce_cart_widget"))
                    $res = ecommerce_cart_widget($globals->user_path, $layout);
                break;
            case "LANGUAGES":
                if(check_function("process_language"))
                    $res = process_language(LANGUAGE_INSET, $globals->user_path, $layout);
                break;
            case "SEARCH":
                if(check_function("process_omnisearch"))
                    $res = process_omnisearch($globals->user_path, $layout);
                break;
            case "LOGIN":
                if(check_function("process_login"))
                    $res = process_login($globals->user_path, $layout);
                break;
            case "ORINAV":
                if(check_function("process_breadcrumb"))
                    $res = process_breadcrumb($globals->user_path, $globals->settings_path, $layout["db"]["real_path"], $layout);
                break;
            case "USER":
                if(check_function("process_user_menu"))
                    $res = process_user_menu(null, null, AREA_SHOW_ECOMMERCE, $globals->user_path, $layout);
                break;
            case "FORMS_FRAMEWORK":
			    /**
			    * Admin Father Bar
			    */            
                if(AREA_FORMS_FRAMEWORK_SHOW_MODIFY) {
                    $admin_menu["admin"]["unic_name"] = $unic_id;
                    $admin_menu["admin"]["title"] = $layout["title"] . ": " . $globals->user_path;
                    $admin_menu["admin"]["class"] = $layout["type_class"];
                    $admin_menu["admin"]["group"] = $layout["type_group"];
                    $admin_menu["admin"]["adddir"] = "";
                    $admin_menu["admin"]["addnew"] = "";
                    $admin_menu["admin"]["modify"] = "";
                    $admin_menu["admin"]["delete"] = "";
                    $admin_menu["admin"]["extra"] = "";
                    $admin_menu["admin"]["ecommerce"] = "";
                    if(AREA_LAYOUT_SHOW_MODIFY) {
                        $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
                        $admin_menu["admin"]["layout"]["type"] = $layout["type"];
                    }
                    if(AREA_SETTINGS_SHOW_MODIFY) {
                        $admin_menu["admin"]["setting"] = "";
                    }

                    $admin_menu["sys"]["path"] = $globals->user_path;
                    $admin_menu["sys"]["type"] = "admin_toolbar";
                    //$admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
                }

                if(check_function("process_forms_framework"))
                    $res = process_forms_framework($layout["db"]["value"], $layout["db"]["params"], $globals->user_path, $layout);

				/**
				* Process Block Header
				*/	                
				if(check_function("set_template_var"))
					$block = get_template_header($globals->user_path, $admin_menu, $layout);
				
				$res["pre"] 		= $block["tpl"]["header"];
				$res["post"] 		= $block["tpl"]["footer"];
				
				$res["default"] 	= $res["pre"] . $res["content"] .  $res["post"];
                break;
            default:
                ffErrorHandler::raise("lost static pages type: [" . $layout["type"] . "]", E_USER_WARNING, NULL, NULL);
        }
/*
        setJsRequest($layout["plugins"], "library");

        if($layout["js"])
			$globals->js["embed"][$layout["smart_url"]] = $layout["js"];

        if($layout["css"])
			$globals->css["embed"][$layout["smart_url"]] = $layout["css"];*/

    }

	//$res["data_blocks"] = $layout["data_blocks"];
	$res["params"] = $params;
	
	return $res;
}