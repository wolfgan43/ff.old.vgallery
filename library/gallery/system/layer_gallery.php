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
function system_layer_gallery($oPage, $tpl_layer, $limit_section = null) 
{
	//ffErrorHandler::raise("ciao stoocks :) ", E_USER_ERROR, null, get_defined_vars());
    $cm = cm::getInstance();
    //$settings_path =& $oPage->user_vars["settings_path"];
    $ret_url = $_SERVER["REQUEST_URI"];
    //$content_full_size =& $oPage->user_vars["content_full_size"];
    $globals = ffGlobals::getInstance("gallery");
    $user_path = $globals->user_path;
	$framework_css = cm_getFrameworkCss();

    $settings_path = $globals->settings_path;
    $selected_lang = $globals->selected_lang;

    $db_gallery = ffDB_Sql::factory();

    $userNID = get_session("UserNID");
    $is_guest = (!$userNID || $userNID == MOD_SEC_GUEST_USER_ID); 

    /*
    if(!$ret_url)
        $ret_url = $_SERVER["REQUEST_URI"];
      */
    
    $template = Array();
    $frame_buffer = "";
    //$cache_page_contents = 0; // x cache_page
   // $globals->cache["layout_blocks"] = array(); // x cache_page
  //  $globals->cache["section_blocks"] = array(); // x cache_page
   // $globals->cache["ff_blocks"] = array(); // x cache_page
	
    if(check_function("system_get_sections")) {
        $template = system_get_sections($limit_section);
       // $template = $structure["template"];
        //$sections = $template["sections"];
        //$layers = $template["layers"];
 	    //$navadmin_sections = $template["sections"];
	}
    /*
    if(!$oPage->isXHR() && check_function("get_webservices")) {
        $services_params = get_webservices(null, $oPage);
    } */
    
    if(ENABLE_STD_PERMISSION && check_function("get_file_permission")) {
	    $file_permission = get_file_permission($settings_path, "static_pages");
	    if (is_array($file_permission) && !check_mod($file_permission, 1, true)) {
	    	if(!defined("SKIP_MAIN_CONTENT")) define("SKIP_MAIN_CONTENT", true);
		}
	}    

    if(!defined("SKIP_VG_CONTENT")) {
    	/*****
    	* PROCESS BLOCK
    	*/
    	$template = system_get_blocks($template); 
    }

    if(!$template["primary_section"])
        $template["primary_section"] = $template["main_section"][0];      

    if(!defined("SKIP_VG_LAYOUT")) {
        //Before Body
        if($globals->fixed_pre["body"]) {
            $ff_unic_id = "PREBODY";
            $globals->cache["ff_blocks"][] = $ff_unic_id;

            if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
                $tpl_layer->set_var("fixed_pre_body", implode("", $globals->fixed_pre["body"]));
            } else {
                $frame_buffer .= implode("", $globals->fixed_pre["body"]);
            }
        }
        
        //Before Main Content
        if($globals->fixed_pre["content"]) {
            $oPage->fixed_pre_content = implode("", $globals->fixed_pre["content"]) . $oPage->fixed_pre_content;
        }

        if(strlen($oPage->fixed_pre_content)) {
            $ff_unic_id = "FFPRE";
            $globals->cache["ff_blocks"][] = $ff_unic_id;

            if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate")
                $tpl_layer->set_var("fixed_pre_content", $oPage->fixed_pre_content);
            else 
                $frame_buffer .= $oPage->fixed_pre_content;
           /* if(!defined("SKIP_MAIN_CONTENT") && $template["primary_section"]) {
                

                $ff["pre_content"] = array($ff_unic_id => array("content" => $oPage->fixed_pre_content));
                //array_unshift($sections[$template["main_section"][0]]["layouts"], array("content" => $oPage->fixed_pre_content));
                $template["sections"][$template["primary_section"]]["layouts"] =  $ff["pre_content"] + $template["sections"][$template["primary_section"]]["layouts"];
                
            } */
        }

        //After Main Content
        if($globals->fixed_post["content"]) {
            $oPage->fixed_post_content = $oPage->fixed_post_content . implode("", $globals->fixed_post["content"]);
        }

        if(strlen($oPage->fixed_post_content)) {
            $ff_unic_id = "FFPOST";
            $globals->cache["ff_blocks"][] = $ff_unic_id;

            if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate")
                $tpl_layer->set_var("fixed_post_content", $oPage->fixed_post_content);
            else 
                $frame_buffer .= $oPage->fixed_post_content;
                
/*            
            if(!defined("SKIP_MAIN_CONTENT") && $template["primary_section"]) {
                
                $ff["post_content"] = array($ff_unic_id => array("content" => $oPage->fixed_post_content));
                //$sections[$primary_main_section]["layouts"][] = array("content" => $oPage->fixed_post_content);
                $template["sections"][$template["primary_section"]]["layouts"] = $template["sections"][$template["primary_section"]]["layouts"] + $ff["post_content"];
                
            }       */
        }

        //After Body
        if($globals->fixed_post["body"]) {
            $ff_unic_id = "POSTBODY";
            $globals->cache["ff_blocks"][] = $ff_unic_id;

            if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
                $tpl_layer->set_var("fixed_post_body", implode("", $globals->fixed_post["body"]));
            } else {
                $frame_buffer .= implode("", $globals->fixed_post["body"]);
            }
        }
    }    

    $oPage->output_buffer = "";
    /*$primary_main_section = "";
	foreach($template["main_section"] AS $ID_main_section) {
        if(is_array($template["sections"][$ID_main_section]["layouts"]) && count($template["sections"][$ID_main_section]["layouts"])) {
			$primary_main_section = $ID_main_section;
			break;
        }
    }
    if(!$primary_main_section && is_array($template["main_section"]) && count($template["main_section"]))	     //TODO: da mettere nella funzione get_sections
    	$primary_main_section = $template["main_section"][0];
       */
    if(!defined("SKIP_MAIN_CONTENT") && $template["primary_section"]) {
        //ffErrorHandler::raise("ad", E_USER_ERROR, null, get_defined_vars());
        $count_ff = 0;
        $count_vg = 0;
        if(is_array($oPage->contents) && count($oPage->contents)) {
        //is_array($sections[$template["main_section"]]["layouts"])
        	if($template["primary_section"]) {
		        //per evitare di non visualizzare i cotenuti di default se una persona nasconde la sezione content da area riservata
		        //Da Gestire meglio
		        //if(!$sections[$primary_main_section]["visible"])
		        //    $sections[$primary_main_section]["visible"] = true;  

	            foreach($oPage->contents AS $contents_key => $contents_value) {
	                if(strpos($contents_key, "/contents") === 0) {
	                    if(strpos(substr($contents_key, strlen("/contents")), $settings_path) !== 0) {
	                        continue;
	                    }
	                }
	                
	                if(strpos($contents_key, "MD-") === 0)
	                    continue;

	                switch($contents_key) {
	                    case "SEARCH":
	                        $content_unic_id = "S" . "0";

	                        $content_layout[$content_unic_id]["prefix"] = "s";
	                        $content_layout[$content_unic_id]["ID"] = $count_vg;
	                        $content_layout[$content_unic_id]["smart_url"] = ffCommon_url_rewrite($contents_key);
	                        $content_layout[$content_unic_id]["title"] = $contents_key;
	                        $content_layout[$content_unic_id]["type"] = "SEARCH";
	                        $content_layout[$content_unic_id]["location"] = "Content";
	                        //$content_layout[$content_unic_id]["width"] = $sections[$primary_main_section]["width"];
	                        $content_layout[$content_unic_id]["visible"] = NULL;
	                        if(check_function("get_layout_settings"))
	                            $content_layout[$content_unic_id]["settings"] = get_layout_settings(NULL, "SEARCH");
	                        $content_layout_sort = $content_layout[$content_unic_id]["settings"]["AREA_SEARCH_DEFAULT_SORT"];
	                        
	                        $count_vg++;
	                        break;
	                    default:
	                    
	                        $content_unic_id = "FF" . $contents_key;
	                        $count_ff++;
	                        $content_layout[$content_unic_id]["prefix"] = "FF";
	                        $content_layout[$content_unic_id]["ID"] = $count_ff;
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
	                        
	                        $content_layout_sort = $content_layout_sort + $count_ff;
	                }
	                $tmp_content_layout = $content_layout[$content_unic_id];
	                $tmp_content_layout["settings"] = md5(serialize($tmp_content_layout["settings"]));

	                if(AREA_SHOW_NAVBAR_ADMIN /*&& is_array($navadmin_sections) && array_key_exists($section_name, $navadmin_sections)*/) {
	                    //array_splice($template["navadmin"][$template["primary_section"]]["layouts"], $content_layout_sort, 0, array($content_unic_id => $tmp_content_layout));
	                    $template["navadmin"][$template["primary_section"]]["layouts"] = 
							array_slice($template["navadmin"][$template["primary_section"]]["layouts"], 0, $content_layout_sort, true)
				            + array($content_unic_id => $tmp_content_layout)
				            + array_slice($template["navadmin"][$template["primary_section"]]["layouts"], $content_layout_sort, NULL, true);
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
	                        } elseif(isset($contents_value["data"]->id) && isset($oPage->components_buffer[$contents_value["data"]->id])) {
	                            if(is_array($oPage->components_buffer[$contents_value["data"]->id])) {
	                                $content_layout[$content_unic_id]["content"] = /*$oPage->components_buffer[$contents_value["data"]->id]["headers"] .*/ $oPage->components_buffer[$contents_value["data"]->id]["html"] /*. $oPage->components_buffer[$contents_value["data"]->id]["footers"]*/;
	                            } else {
	                                $content_layout[$content_unic_id]["content"] = $oPage->components_buffer[$contents_value["data"]->id];
	                            }
	                            $globals->cache["ff_blocks"][] = $contents_value["data"]->id; //array X cache_page
	                        }
	                        break;
	                    
	                    default:
	                        $content_layout[$content_unic_id]["content"] = $contents_value["data"];
	                        $globals->cache["ff_blocks"][] = md5($contents_value["data"]); //array X cache_page
	                }
	                
	                //array_splice($template["sections"][$template["primary_section"]]["layouts"], $content_layout_sort, 0, array($content_unic_id => $content_layout[$content_unic_id]));
	                $template["sections"][$template["primary_section"]]["layouts"] = 
						array_slice($template["sections"][$template["primary_section"]]["layouts"], 0, $content_layout_sort, true)
			            + array($content_unic_id => $content_layout[$content_unic_id])
			            + array_slice($template["sections"][$template["primary_section"]]["layouts"], $content_layout_sort, NULL, true);
	            }
	        }
		}
    }

    //if(check_function("get_layout_settings"))
       // $layout_settings_popup = get_layout_settings(NULL, "ADMIN"); 

  
    if(is_array($template["layers"]) && count($template["layers"])) {
        $count_main_content                     = 0;
        $count_notfound                         = 0;
        $tmp_arrSection                         = array();

        $section_params = array(
            "main_content"                      => null
        , "count_block"                         => 0
        , "count_block_content"                 => 0
        //, "js_custom_is_set"                    => false
        , "page_invalid"                        => null
        , "reset_content_by_user"               => null
        , "content_block"                       => null
        , "global_block"                        => 0
        , "user_path"                           => $globals->page["user_path"]
        , "settings_path"                       => $globals->settings_path
        );

        foreach($template["layers"] AS $layer_key => $layer_value) 
        {
			$globals->cache["layer_blocks"][] = $template["layers"][$layer_key]["ID"]; //array X cache_page

            if(is_array($layer_value["sections"]) && count($layer_value["sections"])) 
            {
                foreach($layer_value["sections"] AS $section_key) 
                {
                    if(array_key_exists($section_key, $template["sections"])) {
                        $section_params["main_content"] = $template["sections"][$section_key]["is_main"];
                        
                        if($oPage->isXHR() && !$section_params["main_content"])
                        	continue;

//                        if($sections[$section_key]["visible"]) 
 //                       {
                            $globals->cache["section_blocks"][] = $template["sections"][$section_key]["ID"]; //array X cache_page

                            $section_params["count_block"] = 0;
                            $section_params["count_block_content"] = 0;
                            //$section_params["js_custom_is_set"] = false;
                            //$section_params["js_custom_module_is_set"] = false;
                            $section_params["page_invalid"] = null;
                            $section_params["reset_content_by_user"] = null;

                            $section_params["search"] = $globals->search;
                            $section_params["navigation"] = $globals->navigation;
							
                            if(is_array($template["sections"][$section_key]) && count($template["sections"][$section_key])) 
                            {
                                if(is_array($template["sections"][$section_key]["layouts"]) && count($template["sections"][$section_key]["layouts"])) 
                                {
									foreach($template["sections"][$section_key]["layouts"] AS $layout_key => $layout_value) {
										if(!$layout_value)
											$layout_value = $template["blocks"][$layout_key];
									
 										if($section_params["main_content"] && $layout_value["use_in_content"] == 0) {
                                            switch ($layout_value["type"]) {
                                                case "STATIC_PAGE_BY_DB":
                                                case "GALLERY":
                                                case "VIRTUAL_GALLERY":
                                                case "MODULE":
                                                    $layout_value["use_in_content"] = 1;
                                                    break;
                                                case "FORMS_FRAMEWORK":
                                                case "PUBLISHING":
                                                case "STATIC_PAGE_BY_FILE":
                                                case "VGALLERY_MENU":
                                                case "LOGIN":
                                                case "COMMENT":
                                                case "STATIC_PAGES_MENU":
                                                case "VGALLERY_GROUP":
                                                case "GALLERY_MENU":
                                                case "LANGUAGES":
                                                case "SEARCH":
                                                case "ORINAV": 
                                                case "ECOMMERCE":
                                                case "USER":
                                                default:
                                                	$layout_value["use_in_content"] = -1;
                                                    break;
                                            }
										}
                                    	if($oPage->isXHR() && $layout_value["use_in_content"] < 0)
                                    		continue;

                                        if(!strlen($layout_value["content"]) && $layout_value["visible"]) {
                                            $globals->cache["layout_blocks"][$layout_value["ID"]]["last_update"] = $layout_value["last_update"]; //array X cache_page
                                            $globals->cache["layout_blocks"][$layout_value["ID"]]["frequency"] = $layout_value["frequency"]; //array X cache_page

											$buffer = system_block_process($layout_value, $section_params);
                                           	$section_params = $buffer["params"];

//											$sections[$section_key]["layouts"][$layout_key]["content"] = $wrap["prefix"] . $buffer["content"] . $wrap["postfix"];
											
											$layout_value["content"] = $buffer["pre"] . $buffer["content"] . $buffer["post"];
											//$sections[$section_key]["layouts"][$layout_key]["content"] = $buffer["content"];
											
											//if($layout_value["wrap"] && $buffer["content"])
											//	$sections[$section_key]["layouts"][$layout_key]["content"] = '<div class="' . $layout_value["wrap"] . '">' . $sections[$section_key]["layouts"][$layout_key]["content"] . '</div>';
											
											if(array_key_exists("template", $buffer))
												$template = array_replace_recursive ($template, $buffer["template"]);
                                                                                        
                                        }
										if(strlen($layout_value["content"])) {
											if ($buffer["content"] && ($layout_value["visible"] == true || $layout_value["visible"] == null)) {
												$section_params["count_block"]++;
												/*if(isset($buffer["data_blocks"]) && is_array($buffer["data_blocks"]) && count($buffer["data_blocks"])) {
													$cache_page["data_blocks"] = array_merge($cache_page["data_blocks"], $buffer["data_blocks"]);
												} */

												if ($section_params["main_content"]
													&& strlen($layout_value["content"])
													&& (count($template["main_section"]) > 1 || $layout_value["use_in_content"] > 0)
												) {
													$section_params["count_block_content"]++;
													$section_params["reset_content_by_user"] = false;
												}

												if (is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
													$template["sections"][$section_key]["processed_block"]++;

													//parte per il templating custom
													if (!$tpl_layer->isset_var("block_" . $layout_value["smart_url"])) {
														if (array_key_exists("Sez" . $section_key, $tpl_layer->DBlocks) !== false) {
															$tmp_arrSection[$section_key] = $tmp_arrSection[$section_key] . $layout_value["content"];
														} else {
															$tpl_layer->set_var("layout", $layout_value["fixed_pre_content"] . $layout_value["content"] . $layout_value["fixed_post_content"]);
															$tpl_layer->parse("SezSectionLayout", true);
														}
													} else {
														$tpl_layer->set_var("block_" . $layout_value["smart_url"], $layout_value["content"]);
													}
												} else {
													$frame_buffer .= $layout_value["content"];
												}
											}
										} else {
											cache_writeLog("Block: " . $layout_value["smart_url"] . " (ID: " . $layout_value["ID"] . ") URL: " . $user_path, "error_block_notfound");
										}
                                    }
                                }

								if($section_params["main_content"]) 
								{
									/**
									*  Load promary content if exists and content empty
									*/
									if(!$section_params["content_block"] && check_function("process_landing_page")) 
									{
										$buffer = process_landing_page($user_path, $globals->seo["current"], $template["primary_section"] == $section_key);
										if($buffer) 
										{
											$section_params["count_block"]++;
										    $section_params["count_block_content"]++;
	                                        $template["sections"][$section_key]["processed_block"]++;
										
											if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") 
		                                    {
	                                    		$tpl_layer->set_var("layout", $buffer);
                                        		$tpl_layer->parse("SezSectionLayout", true); 
											} 
											else 
											{
                                        		$frame_buffer .= $buffer;
											}
										}
									}

									/**
									* Set params for manage http status
									*/
									$count_main_content++;
									if($count_ff || $count_vg) {
										$section_params["content_block"] = $section_params["count_block"] + $count_ff + $count_vg;
									} elseif($section_params["page_invalid"] === true) {
                                        $section_params["content_block"] = 0;
                                    } else {
	                                    if($section_params["reset_content_by_user"] !== false
	                                    	&& !$section_params["count_block_content"]
	                                    )
	                                        $section_params["content_block"] = 0;
										else
                                            $section_params["content_block"] = $section_params["count_block"];
									}

									/**
									* manage by params 404 and content 404 
									*/
									if(!$section_params["content_block"] && check_function("process_html_page_error")) { 
										if($oPage->isXHR()) {
											/*http_response_code(204);  //nn funzionano i servizi custom TODO: da verificare vedi serivzi di giorgio
											exit;*/
										} else {
											$count_notfound++;
											if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
												$tpl_layer->set_var("layout", process_html_page_error());
                                                $tpl_layer->parse("SezSectionLayout", true);											
		                                        $template["sections"][$section_key]["processed_block"]++;
											}
										}
									}									
                                }

                                if($template["sections"][$section_key]["processed_block"] || $template["sections"][$section_key]["show_empty"]) 
                                {
                                //	print_r($sections[$section_key]);
                                    if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") 
                                    {
                                        if(defined("SKIP_VG_LAYOUT")) {
                                            if(is_array($framework_css)) {
                                                //da valutare in base alla casistica in teoria va bene cosi
                                                $tpl_layer->set_var("SezSectionWidth", "");
                                            } else {
                                                $tpl_layer->set_var("section_width", "100%");
                                                $tpl_layer->parse("SezSectionWidth", false);
                                            }
                                        } else {
                                            if($template["sections"][$section_key]["width"] > 0) {
                                                $tpl_layer->set_var("section_width", $template["sections"][$section_key]["width"] . $template["sections"][$section_key]["sign"]);
                                                $tpl_layer->parse("SezSectionWidth", false);
                                            } else {
                                                $tpl_layer->set_var("SezSectionWidth", "");
                                            }

                                            $section_params["global_block"] += $section_params["count_block"];
                                            if($section_params["main_content"]) {
                                                if(!$section_params["content_block"]) {
                                                    if($section_params["reset_content_by_user"] === true)
	                                                    $tpl_layer->set_var("SezSectionLayout", "");
                                                }
                                            }

                                            //parte per il templating custom
                                            if(array_key_exists("Sez" . $section_key, $tpl_layer->DBlocks) !== false
                                                && is_array($tmp_arrSection) && count($tmp_arrSection)
                                                && array_key_exists($section_key, $tmp_arrSection)
                                            ) {
                                                $tpl_layer->set_var("content", $tmp_arrSection[$section_key]);
                                                $tpl_layer->parse("Sez" . $section_key, false);
                                            } 
                                        }
                                        if(is_array($framework_css)) {
                                            if($template["sections"][$section_key]["wrap"] !== false) {
                                                $tpl_layer->set_var("wrap_section_class", $template["sections"][$section_key]["wrap"]);
                                                $tpl_layer->parse("SezSectionWrapStart", false);
                                                $tpl_layer->parse("SezSectionWrapEnd", false);
                                            } else {
                                                $tpl_layer->set_var("SezSectionWrapStart", "");
                                                $tpl_layer->set_var("SezSectionWrapEnd", "");
                                            }                                     
                                            
                                            /*if($layer_value["count"] > 1 && $layers[$layer_key]["wrap"] === false) {
                                                if($sections[$section_key]["class"]["grid_alt"]) {
                                                    $layers[$layer_key]["class"]["grid"] = $sections[$section_key]["class"]["grid_alt"];
                                                    unset($sections[$section_key]["class"]["grid_alt"]);
                                                } 
                                            }*/
                                            
                                             //Secondo me e tutto da togliere. Questo limita la liberta del grafico
/*
                                            if($layer_value["count"] == 1) 
                                            {            
                                                if(!$sections[$section_key]["grid_isset"] && ($layers[$layer_key]["wrap"] === false || ($sections[$section_key]["wrap"] !== false && $sections[$section_key]["fluid"])))
                                                    unset($sections[$section_key]["class"]["grid"]);
                                            }
                                            if($layers[$layer_key]["wrap"] !== false || $sections[$section_key]["wrap"] !== false) {   
                                                 unset($sections[$section_key]["class"]["grid_alt"]);
                                            }
                                            if($layers[$layer_key]["wrap"] !== false) {   
                                                unset($layers[$layer_key]["class"]["grid"]);
                                            }
*/
                                        } else {
											if($template["sections"][$section_key]["width"] > 0) {
                                                $tpl_layer->set_var("section_width", $template["sections"][$section_key]["width"] . $template["sections"][$section_key]["sign"]);
                                                $tpl_layer->parse("SezSectionWidth", false);
                                            } else {
                                                $tpl_layer->set_var("SezSectionWidth", "");
                                            }                                        
                                        }
                                        
                                        $tpl_layer->set_var("section_class", implode(" ", array_filter($template["sections"][$section_key]["class"])));
//echo implode(" ", array_filter($sections[$section_key]["class"])) . "asd<br>\n";
                                        $template["layers"][$layer_key]["processed_block"] = $template["layers"][$layer_key]["processed_block"] + $template["sections"][$section_key]["processed_block"];

                                        if($template["sections"][$section_key]["hide"]) {
											$tpl_layer->set_var("SezSectionStart", "");
											$tpl_layer->set_var("SezSectionEnd", "");
                                        } else {
											$tpl_layer->parse("SezSectionStart", false);
											$tpl_layer->parse("SezSectionEnd", false);
                                        }

                                        $tpl_layer->parse("SezSection", true);
                                        $tpl_layer->set_var("SezSectionLayout", "");
                                    }
                                }                                
                            }
                       // }
                    }
                }
            }
   // print_r($sections);          
            if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
                if(defined("SKIP_VG_LAYOUT")) {
                    $tpl_layer->set_var("SezLayerStart", "");
                    $tpl_layer->set_var("SezLayerEnd", "");
                    if(is_array($framework_css)) {
                        //da valutare in base alla casistica in teoria va bene cosi
                        $tpl_layer->set_var("SezLayerWidth", "");
                    } else {
                        $tpl_layer->set_var("layer_width", "100%");
                        $tpl_layer->parse("SezLayerWidth", false);
                    }
                    $tpl_layer->parse("SezLayer", true);
                } else {
                    if($template["layers"][$layer_key]["processed_block"] || $template["layers"][$layer_key]["show_empty"]) {
	                    if(array_key_exists("wrap", $template["layers"][$layer_key]) && $template["layers"][$layer_key]["wrap"] !== false) {
                            $tpl_layer->set_var("wrap_layer_class", $template["layers"][$layer_key]["wrap"]);
	                        $tpl_layer->parse("SezLayerWrapStart", false);
	                        $tpl_layer->parse("SezLayerWrapEnd", false);
	                    } else {
	                        $tpl_layer->set_var("SezLayerWrapStart", "");
	                        $tpl_layer->set_var("SezLayerWrapEnd", "");
	                    }

	                    $tpl_layer->set_var("SezLayerStart", "");
	                    $tpl_layer->set_var("SezLayerEnd", "");
                    	if(array_key_exists("class", $template["layers"][$layer_key])) {
	                        $tpl_layer->set_var("layer_class", implode(" ", array_filter($template["layers"][$layer_key]["class"])));
		                    if(!is_array($framework_css)) {
		                        if($template["layers"][$layer_key]["width"] > 0) {
		                            $tpl_layer->set_var("layer_width", $template["layers"][$layer_key]["width"] . $template["layers"][$layer_key]["sign"]);
		                            $tpl_layer->parse("SezLayerWidth", false);
		                        } else {
		                            $tpl_layer->set_var("SezLayerWidth", "");
		                        }
		                    }

	                        if(!$template["layers"][$layer_key]["hide"]) {
	                            $tpl_layer->parse("SezLayerStart", false);
	                            $tpl_layer->parse("SezLayerEnd", false);
	                        }
						}
                        $tpl_layer->parse("SezLayer", true); 
                    } else {
                        $tpl_layer->set_var("SezLayerStart", "");
                        $tpl_layer->set_var("SezLayerEnd", "");
                    }              
                }
                
                $tpl_layer->set_var("SezSection", ""); 
                
                
            }
        }
        
		if(isset($template["offcanvas"])) {
//                    $oPage->tplAddJs("foundation.offcanvas", "foundation.offcanvas.min.js", "https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.2/js/foundation/foundation.offcanvas.min.js");
            $tpl_layer->set_var("SezSection", $template["offcanvas"]);
            $tpl_layer->set_var("SezLayerStart", "");
            $tpl_layer->set_var("SezLayerEnd", "");
            $tpl_layer->parse("SezLayer", true); 
        }

        if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
            $tpl_layer->set_var("container_class", $template["container"]["class"]);
            if(is_array($framework_css)) {
                if($template["container"]["wrap"]) {
                    if($template["container"]["wrap_class"]) {
                        $tpl_layer->set_var("wrap_container_class", $template["container"]["wrap_class"]);
                    } else {
                        $tpl_layer->set_var("wrap_container_class", "wrap-" . $template["container"]["class"]);
                    }
                    $tpl_layer->parse("SezContainerWrapStart", false);
                    $tpl_layer->parse("SezContainerWrapEnd", false);
                } else {
                    $tpl_layer->set_var("SezContainerWrapStart", "");
                    $tpl_layer->set_var("SezContainerWrapEnd", "");
                }
            } else {
                if($template["container"]["width"] > 0) {
                    $tpl_layer->set_var("container_width", $template["container"]["width"] . $template["container"]["sign"]);
                    $tpl_layer->parse("SezContainerWidth", false);
                } else {
                    $tpl_layer->set_var("SezContainerWidth", "");
                }
            }
            if(isset($template["container"]["properties"])) {
                $tpl_layer->set_var("container_properties", $template["container"]["properties"]);
                $tpl_layer->parse("SezContainerProperties", false);
            } else {
                $tpl_layer->set_var("SezContainerProperties", "");
            }
            
            /*if(!defined("DISABLE_CACHE") && http_response_code() == 200 ) {
                if(check_function("system_set_cache_page")) {
                    //$oPage->addEvent("on_tpl_parsed", "system_set_cache_page_old" , ffEvent::PRIORITY_FINAL);
                    //system_write_cache_page_old($user_path, $user_path, $cache_page["data_blocks"], $cache_page["layout_blocks"], implode(",", $cache_page["section_blocks"]), implode(",", $cache_page["ff_blocks"]),  ($section_params["content_block"] === null ? $section_params["global_block"] : $section_params["content_block"]));

                   // $oPage->addEvent("on_tpl_parsed", "system_set_cache_page" , ffEvent::PRIORITY_FINAL);
                    system_write_cache_page($user_path, ($section_params["content_block"] === null ? $section_params["global_block"] : $section_params["content_block"]));
                }
            }*/
        }        
    } else {
        if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
            if(AREA_SHOW_NAVBAR_ADMIN && check_function("system_wizard")) {
            	$tpl_layer->set_var("layout", system_wizard("struct"));
				//da inserire il wizard
			} else {
     			if(check_function("process_html_page_error")) {
            		$tpl_layer->set_var("layout", process_html_page_error(404));
				}
	        }

	        $tpl_layer->set_var("container_class", cm_getClassByFrameworkCss("", "row"));
			$tpl_layer->parse("SezSectionLayout", false);
			$tpl_layer->parse("SezSection", false);
		    $tpl_layer->parse("SezLayer", false);
        }
    }

    if(!count($template["main_section"])) {
    	http_response_code(500);
		if(check_function("write_notification")) {
			write_notification("missing_main_section", "", "warning", null, $user_path);
		}
    } elseif($count_notfound == $count_main_content) {
        http_response_code(404);
        $globals->setSeo(array(
            "title" => ffTemplate::_get_word_by_code("not_found_title")
            , "description" => ffTemplate::_get_word_by_code("not_found_description")
        ), "detail");
	}

	if(!defined("DISABLE_CACHE") && check_function("system_set_cache_page")) {
		system_write_cache_page($user_path, ($section_params["content_block"] === null ? $section_params["global_block"] : $section_params["content_block"]));
    }

  //ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
  
    if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
        if(!defined("SKIP_VG_LAYOUT")) {
            if(AREA_SHOW_NAVBAR_ADMIN) {
            	//setJsRequest("toolbaradmin");
            	setJsRequest("toolbar");
            	setJsRequest("cluetip");
               // setJsRequest($layout_settings_popup["ADMIN_INTERFACE_MENU_PLUGIN"]);
               // setJsRequest($layout_settings_popup["ADMIN_INTERFACE_PLUGIN"]);
            }
            
            $use_admin_ajax = false;
            if(is_array($globals->js["request"]) && count($globals->js["request"])) {
                if(AREA_SHOW_NAVBAR_ADMIN
                 //   || strlen($layout_settings_popup["ADMIN_TOOLBAR_MENU_PLUGIN"]) && array_key_exists($layout_settings_popup["ADMIN_TOOLBAR_MENU_PLUGIN"], $globals->js["request"])
                 //   || strlen($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]) && array_key_exists($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"], $globals->js["request"])
                ) {
                    $use_admin_ajax = true;
                }
            }

            if(AREA_SHOW_NAVBAR_ADMIN) {
 //               $oPage->tplAddJs("jquery.ui", "jquery.ui.js", FF_THEME_DIR ."/library/jquery.ui");
//				$oPage->tplAddCss("jquery.toolbaradmin", "jquery.toolbar.admin.css", FF_THEME_DIR ."/" . THEME_INSET . "/javascript/plugins/jquery.toolbaradmin/css");
//				$oPage->tplAddJs("jquery.toolbaradmin", "jquery.toolbaradmin.observe.js", FF_THEME_DIR ."/" . THEME_INSET . "/javascript/plugins/jquery.toolbaradmin");
            }

            if(check_function("system_set_js"))
                system_set_js($oPage, $settings_path, false, null, $use_admin_ajax, constant("JS_NO_CASCADING"), true);    
            if(check_function("system_set_css"))
                system_set_css($oPage, $settings_path, constant("CSS_NO_CASCADING"), false);
            if(check_function("system_set_meta")) 
                system_set_meta($oPage);

		    $oPage->addEvent("on_tpl_parse", "write_user_vars" , ffEvent::PRIORITY_FINAL);
			//da usare phantomJS 
			//$oPage->tplAddJs("ff.cms.above-the-fold", "ff.cms.above-the-fold.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"); 
			
            if(AREA_SHOW_NAVBAR_ADMIN) {
            	$cms_options = array();
				/*if(AREA_SECTION_SHOW_MODIFY && is_array($template["navadmin"]) && count($template["navadmin"])) {
					$oPage->tplAddCss("cms-editor", "cms-editor.css", FF_THEME_DIR . "/" . THEME_INSET . "/css");
					$oPage->tplAddJs("ff.cms.editor", "ff.cms.editor.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"); 
					//$oPage->tplAddJs("jquery.fn.ColorPicker", "jquery.colorpicker.js", FF_THEME_DIR . "/library/plugins/jquery.colorpicker"); 
					//$oPage->tplAddJs("jquery.fn.niceScroll", "jquery.nicescroll.js", FF_THEME_DIR . "/library/plugins/jquery.nicescroll"); 
					//$oPage->tplAddJs("jquery.fn.hoverIntent", "jquery.hoverintent.js", FF_THEME_DIR . "/library/plugins/jquery.hoverintent"); 
					
					$cms_options["editor"] = array();

					if(AREA_SEO_SHOW_MODIFY) { 
						$oPage->tplAddCss("cms-seo", "cms-seo.css", FF_THEME_DIR . "/" . THEME_INSET . "/css");
					    $oPage->tplAddJs("ff.cms.seo", "ff.cms.seo.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"); 
					    if(is_file(FF_THEME_DISK_PATH . "/" . THEME_INSET . "/javascript/tools/stopwords/ff.cms.seo.stopwords." . strtolower(LANGUAGE_INSET) . ".js"))
					    	$cm->oPage->tplAddJs("ff.cms.seo.stopWords", "ff.cms.seo.stopwords." . strtolower(LANGUAGE_INSET) . ".js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/stopwords");

						$cms_options["editor"]["seo"] = true;
					}
					if(AREA_SITEMAP_SHOW_MODIFY && 0) { 
					    $oPage->tplAddJs("ff.cms.sitemap", "ff.cms.sitemap.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"); 

						$cms_options["editor"]["sitemap"] = array(
							"menu" => array("class" => "cms-editor-menu sitemap"
											, "icon" => cm_getClassByFrameworkCss("sitemap", "icon-tag", "2x")
											, "rel" => "add"
							)
						);
					}				
					if(AREA_LAYOUT_SHOW_MODIFY) { 
					    $oPage->tplAddJs("ff.cms.layout", "ff.cms.layout.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools");

						$cms_options["editor"]["sitemap"] = array(
							"menu" => array("class" => "cms-editor-menu"
											, "icon" => cm_getClassByFrameworkCss("addnew", "icon-tag", "2x")
											, "rel" => "add"
							)
						);
					}
				} */
				

                $admin_menu["admin"]["unic_name"] = "unic_admin_menu" . $settings_path;
                $admin_menu["admin"]["sections"] = $template["navadmin"];
                $admin_menu["admin"]["css"] = $oPage->page_css;
                $admin_menu["admin"]["js"] = $oPage->page_js;
                $admin_menu["admin"]["theme"] = $oPage->theme;
                $admin_menu["admin"]["international"] = ffTemplate::_get_word_by_code("", null, null, true);
                 $admin_menu["admin"]["seo"] = array(
                    "src" => $globals->seo["current"] //$seo_primary_block
                    , "ID" => $globals->seo[$globals->seo["current"]]["ID"] //$ID_seo_node
                );
                //$admin_menu["admin"]["option"] = $cms_options;
                $admin_menu["sys"]["path"] = $settings_path; 
               // $admin_menu["sys"]["type"] = "admin_menu";
               // $admin_menu["sys"]["ret_url"] = $ret_url;

                $serial_admin_menu = json_encode($admin_menu);

				$oPage->properties_body["data-admin"] = set_sid($serial_admin_menu);
                //$tpl_layer->set_var("admin", '<input class="ajaxcontent" type="hidden" value="'. FF_SITE_PATH . VG_SITE_FRAME . (strpos($ret_url, "?") ? substr($ret_url, 0, strpos($ret_url, "?")) : $ret_url) . "?sid=" . set_sid($serial_admin_menu) . '" />');
            } else
                $tpl_layer->set_var("admin", "");
        }
        
        if(check_function("system_set_cache_page"))
            $oPage->addEvent("on_tpl_parsed", "system_set_cache_page" , ffEvent::PRIORITY_FINAL);
    } else {
        return $frame_buffer;
    }
/*    
   $js_min = js_minifier($oPage);
   die($js_min);
*/
    //ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());   

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

function system_block_process($layout, $params = array()) {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery"); 

    $res = array(
    	"pre" 						=> ""
    	, "content" 				=> ""
		, "post" 					=> ""
	);

    $params["xhr"] 					= false; //non siamo dentro alla chiamata xhr percui e false //$cm->isXHR();
    $params["user_path_shard"] 		= $globals->user_path_shard;
	//$params["unic_id"] 				= $layout["prefix"] . $layout["ID"];
	
    //if($layout["type"] == "ECOMMERCE" || (isset($layout["use_ajax"]) && $layout["use_ajax"])) {
    if((isset($layout["ajax"]) && $layout["ajax"]) && $layout["ajax_on_ready"] != "preload" && !$cm->isXHR()) {
		/**
		* Process Block Header
		*/	
        $tpl = null;

		if(check_function("set_template_var"))
			$block = get_template_header($params["user_path"], null, $layout, $tpl);

		$res["pre"] 		= $block["tpl"]["pre"];
		$res["content"] 	= "";
		$res["post"] 		= $block["tpl"]["post"];

        if(is_array($layout["settings"]) && count($layout["settings"])) {
            foreach($layout["settings"] AS $setting_key => $setting_value) {
                if(strpos($setting_key, "_PLUGIN") !== false) {
                    setJsRequest($layout["settings"][$setting_key]);
                }
            }
        }
    }
    else 
    {
    	$callback = (function_exists("system_block_" . $layout["type"])
			? "system_block_" . $layout["type"]
			: "system_block_WIDGET"
		);

		$buffer = call_user_func_array(
			$callback
			, array(
				array($layout)
				, $params
			)
		);

		$res = $buffer[0];

		if($layout["type"] == "VIRTUAL_GALLERY") {
			if ($params["page_invalid"] !== false && is_bool($res["page_invalid"])) {
				$params["page_invalid"] = $res["page_invalid"];
			}
		}
		/*
        switch ($layout["type"]) {
			case "STATIC_PAGE_BY_DB":
			case "STATIC_PAGE_BY_FILE":
				$res = system_block_simple($layout, $params, true);
				break;
            case "GALLERY":
				$res = system_block_gallery($layout, $params, true);
                break;
            case "MODULE":
            	$res = system_block_module($layout, $params, true);
                break;
            case "VIRTUAL_GALLERY": 
            	if(check_function("vgallery_init")) {
            		$res = vgallery_init($params, $layout, $globals->data_storage);
            	}

				if($params["page_invalid"] !== false && is_bool($res["page_invalid"])) {
	                $params["page_invalid"] = $res["page_invalid"];
	            }
 
            	break;
            case "PUBLISHING":
				$res = system_block_publishing($layout, $params, true);
                break;
            case "VGALLERY_MENU":
				$res = system_block_menu_vgallery($layout, $params, true);
                break;
            case "GALLERY_MENU":
				$res = system_block_menu_album($layout, $params, true);
                break;
            case "STATIC_PAGES_MENU":
            	$res = system_block_menu($layout, $params, true);
				break;
            case "VGALLERY_GROUP":
				$res = system_block_menu_group($layout, $params, true);
                break;
            case "ECOMMERCE":
            case "LANGUAGES":
            case "SEARCH":
            case "LOGIN":
            case "ORINAV":
				$res = system_block_widget($layout, $params, true);
                break;
            case "COMMENT":
				$res = system_block_comment($layout, $params, true);
                break;
            case "USER":
				$res = system_block_menu_user($layout, $params, true);
                break;
            case "FORMS_FRAMEWORK":
				$res = system_block_applet($layout, $params, true);
                break;
            default:
                ffErrorHandler::raise("lost static pages type: [" . $layout["type"] . "]", E_USER_WARNING, NULL, NULL);
        }*/
    }

	//$res["data_blocks"] = $layout["data_blocks"];
	$res["params"] = $params;

	return $res;
}

