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
        , "js_custom_is_set"                    => false
        , "page_invalid"                        => null
        , "reset_content_by_user"               => null
        , "content_block"                       => null
        , "global_block"                        => 0
        , "user_path"                           => $globals->page["user_path"]
        , "settings_path"                       => $globals->settings_path
        );

        foreach($template["layers"] AS $layer_key => $layer_value) 
        {
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
                            $section_params["js_custom_is_set"] = false;
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
											/*
											$layout_value["settings"] = (array_key_exists($layout_value["type"] . "-" . $layout_value["ID"], $arrLayoutSettings["data"]) 
																			? $arrLayoutSettings["data"][$layout_value["type"] . "-" . $layout_value["ID"]] 
																			: $arrLayoutSettings["data"][$layout_value["type"] . "-0"] 
																		);*/
																		
																		
/*
											$wrap = array("prefix" => "", "postfix" => "");
											if(is_array($oPage->framework_css)) {
												$layout_value["grid_isset"] = true;					
												$layout_value["fluid_params"] = array();
                                                switch($layout_value["fluid"]) {
                                                    case -1:
                                                        $layout_value["fluid"] = ($oPage->framework_css["is_fluid"] ? "-fluid" : "");
                                                        $layout_value["grid_isset"] = false;
                                                        break;
                                                    case -2:        
                                                        $layout_value["fluid"] = ($oPage->framework_css["is_fluid"] ? "" : "-fluid");
                                                        $layout_value["grid_isset"] = false;
                                                        break; 
                                                    case 1:
                                                        $layout_value["fluid"] = ($oPage->framework_css["is_fluid"] ? "-fluid" : "");
                                                        $layout_value["grid_isset"] = null;
                                                        break;
                                                    case 2:
                                                        $layout_value["fluid"] = ($oPage->framework_css["is_fluid"] ? "-fluid" : "");
                                                        $layout_value["fluid_params"]["skip-prepost"] = true;
                                                        break;
                                                    default:
                                                        $layout_value["fluid"] = ($oPage->framework_css["is_fluid"] ? "-fluid" : "");
                                                }

								                switch($layout_value["wrap"]) {
								                    case -1:
								                        $layout_value["wrap"] = true;
								                        break;
								                    case 1:
								                        $layout_value["wrap"] = ($cm->oPage->framework_css["is_fluid"] ? "-fluid" : "");
								                        break;
								                    case 2:
								                        $layout_value["wrap"] = ($cm->oPage->framework_css["is_fluid"] ? "" : "-fluid");
								                        break;
								                    default:
								                        $layout_value["wrap"] = false;
								                }
								                
												//$layout_value["fluid"] = $layer_value["fluid"];
												
												//if($sections[$section_key]["count_block_visible"] > 1 && $oPage->framework_css["is_fluid"] && strlen($layer_fluid))
												//	$layout_value["fluid"] = "";

                                                if($layout_value["grid_isset"]) {
												    $layout_value["class"]["grid"] = cm_getClassByFrameworkCss(
												    	$layout_value["grid"]
												    	, "col" . $layout_value["fluid"]
												    	, $layout_value["fluid_params"]
												    );
                                                } elseif($layout_value["grid_isset"] === false) {
                                                    $layout_value["class"]["grid_alt"] = cm_getClassByFrameworkCss("", "row" . $layout_value["fluid"]);
                                                }

												if($layout_value["wrap"] !== false) {
													$wrap["prefix"] = '<div' . ($layout_value["wrap"] === true 
																					? ''
																					: ' class="' . cm_getClassByFrameworkCss("", "wrap" . $layout_value["wrap"]) . '"'
																			). '>';
													$wrap["postfix"] = '</div>';
												}
											}
*/											
											$buffer = system_block_process($layout_value, $section_params);
                                           	$section_params = $buffer["params"];
                                           
//											$sections[$section_key]["layouts"][$layout_key]["content"] = $wrap["prefix"] . $buffer["content"] . $wrap["postfix"];
											
											$layout_value["content"] = $buffer["content"];
											//$sections[$section_key]["layouts"][$layout_key]["content"] = $buffer["content"];
											
											//if($layout_value["wrap"] && $buffer["content"])
											//	$sections[$section_key]["layouts"][$layout_key]["content"] = '<div class="' . $layout_value["wrap"] . '">' . $sections[$section_key]["layouts"][$layout_key]["content"] . '</div>';
											
											if(array_key_exists("template", $buffer))
												$template = array_replace_recursive ($template, $buffer["template"]);
                                                                                        
                                        }

                                        if(($layout_value["visible"] == true || $layout_value["visible"] == null) && strlen($layout_value["content"])) {
                                            $section_params["count_block"]++;
                                            /*if(isset($buffer["data_blocks"]) && is_array($buffer["data_blocks"]) && count($buffer["data_blocks"])) {
                                                $cache_page["data_blocks"] = array_merge($cache_page["data_blocks"], $buffer["data_blocks"]);
                                            } */
                                            
                                            if($section_params["main_content"]
                                                && strlen($layout_value["content"]) 
                                                && (count($template["main_section"]) > 1 || $layout_value["use_in_content"] > 0)
                                            ) {
                                                $section_params["count_block_content"]++;
                                                $section_params["reset_content_by_user"] = false;
                                            }

                                            if(is_object($tpl_layer) && get_class($tpl_layer) == "ffTemplate") {
                                                $template["sections"][$section_key]["processed_block"]++;

                                                //parte per il templating custom
												if(!$tpl_layer->isset_var("block_" . $layout_value["smart_url"])) {
                                                    if(array_key_exists("Sez" . $section_key, $tpl_layer->DBlocks) !== false) {
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
												$tpl_layer->set_var("layout", process_html_page_error(null, false, $user_path));
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
                                            if(is_array($oPage->framework_css)) {
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
                                        if(is_array($oPage->framework_css)) {
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
                    if(is_array($oPage->framework_css)) {
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
		                    if(!is_array($oPage->framework_css)) {
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
            if(is_array($oPage->framework_css)) {
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
            		$tpl_layer->set_var("layout", process_html_page_error(404, false, $user_path));
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
            	setJsRequest("toolbaradmin");
            	setJsRequest("toolbar");
            	setJsRequest("cluetip");
               // setJsRequest($layout_settings_popup["ADMIN_INTERFACE_MENU_PLUGIN"]);
               // setJsRequest($layout_settings_popup["ADMIN_INTERFACE_PLUGIN"]);
            }
            
            $use_admin_ajax = false;
            if(USE_ADMIN_AJAX && is_array($globals->js["request"]) && count($globals->js["request"])) {
                if(AREA_SHOW_NAVBAR_ADMIN
                 //   || strlen($layout_settings_popup["ADMIN_TOOLBAR_MENU_PLUGIN"]) && array_key_exists($layout_settings_popup["ADMIN_TOOLBAR_MENU_PLUGIN"], $globals->js["request"])
                 //   || strlen($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]) && array_key_exists($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"], $globals->js["request"])
                ) {
                    $use_admin_ajax = true;
                }
            }

            if(AREA_SHOW_NAVBAR_ADMIN) {
                $oPage->tplAddJs("jquery.ui", "jquery.ui.js", FF_THEME_DIR ."/library/jquery.ui");
            }

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
				$sSQL = "SELECT static_pages.*
				            , static_pages_rel_languages.meta_title
				            , static_pages_rel_languages.meta_title_alt
				            , static_pages_rel_languages.meta_description
				            , static_pages_rel_languages.keywords
						FROM static_pages
					        INNER JOIN static_pages_rel_languages ON static_pages.ID = static_pages_rel_languages.ID_static_pages 
					        	AND static_pages_rel_languages.ID_languages = " . $db_gallery->toSql(LANGUAGE_INSET_ID, "Number") . "
						WHERE static_pages.parent = " . $db_gallery->toSql(ffCommon_dirname($settings_path), "Text") . "
					        AND static_pages.name = " . $db_gallery->toSql(basename($settings_path), "Text") . "
					        AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number");
				$db_gallery->query($sSQL);
				if($db_gallery->nextRecord()) {
					$seo_primary_block = "page";

					$globals->seo[$seo_primary_block]["title"] 												= $db_gallery->getField("meta_title", "Text", true);
				    $globals->seo[$seo_primary_block]["title_header"] 										= $db_gallery->getField("meta_title_alt", "Text", true);
				    if(!$globals->seo[$seo_primary_block]["title_header"])
				    	$globals->seo[$seo_primary_block]["title_header"] 									= $globals->seo[$seo_primary_block]["title"];

				    if(!$globals->seo[$seo_primary_block]["title"])
				    	$globals->seo[$seo_primary_block]["title"] 											= $globals->seo[$seo_primary_block]["title_header"];
				    
				    $globals->seo[$seo_primary_block]["meta"]["description"][] 								= $db_gallery->getField("meta_description", "Text", true);
				    $globals->seo[$seo_primary_block]["meta"]["keywords"][] 								= $db_gallery->getField("keywords", "Text", true);
					
					$globals->meta 																			= $globals->seo[$seo_primary_block]["meta"];
					
					$globals->seo[$seo_primary_block]["ID"] 												= $db_gallery->getField("ID", "Number", true);
				}
			}

			$globals->page_title = $globals->seo[$seo_primary_block]["title"];
			$globals->meta = $globals->seo[$seo_primary_block]["meta"];
			$ID_seo_node = $globals->seo[$seo_primary_block]["ID"];
			
			$globals->seo["current"] = $seo_primary_block;*/
                  
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
				if(AREA_SECTION_SHOW_MODIFY && is_array($template["navadmin"]) && count($template["navadmin"])) {
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
				} 
				

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
                $admin_menu["admin"]["option"] = $cms_options;
                $admin_menu["sys"]["path"] = $settings_path; 
                $admin_menu["sys"]["type"] = "admin_menu";
                $admin_menu["sys"]["ret_url"] = $ret_url;

                $serial_admin_menu = json_encode($admin_menu);
                $tpl_layer->set_var("admin", '<input class="ajaxcontent" type="hidden" value="'. FF_SITE_PATH . VG_SITE_FRAME . (strpos($ret_url, "?") ? substr($ret_url, 0, strpos($ret_url, "?")) : $ret_url) . "?sid=" . set_sid($serial_admin_menu) . '" />');
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

function system_block_process($layout, $params = array(), $layout_settings_popup = null) {
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

		$res["content"] = $block["tpl"]["header"] . $block["tpl"]["footer"];
//        $res["content"] = "<div id=\"" . $unic_id . "\" class=\"block ajaxcontent\" data-ready=\"" . $layout["ajax_on_ready"] . "\" data-event=\"" . $layout["ajax_on_event"] . "\" data-src=\"" . $serial_frame_url . "\"></div>";
        
        if(is_array($layout["settings"]) && count($layout["settings"])) {
            foreach($layout["settings"] AS $setting_key => $setting_value) {
                if(strpos($setting_key, "_PLUGIN") !== false) {
                    setJsRequest($layout["settings"][$setting_key]);
                }
            }
        }
        if(strlen($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"])) {
            switch ($layout["type"]) {
                case "STATIC_PAGE_BY_DB":
                    if(AREA_STATIC_SHOW_MODIFY || AREA_STATIC_SHOW_ADDNEW) {
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    }
                    break;
                case "STATIC_PAGE_BY_FILE":
                    if(AREA_STATIC_SHOW_MODIFY || AREA_STATIC_SHOW_ADDNEW) {
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    }
                    break;
                case "STATIC_PAGES_MENU":
                    if(AREA_STATIC_SHOW_MODIFY || AREA_STATIC_SHOW_ADDNEW) {
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
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
                    if(
                       AREA_VGALLERY_SHOW_MODIFY 
                        || AREA_VGALLERY_SHOW_ADDNEW
                    ) {
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    }
                    if(!$params["js_custom_is_set"]) {
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
                        $params["js_custom_is_set"] = true;
                    }
                    break;
                case "MODULE":
                    if(MODULE_SHOW_CONFIG) {
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    }

                    /*if(!$params["js_custom_module_is_set"]) {
                        $db->query("SELECT IF(EXISTS(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'display_view_mode' AND TABLE_NAME = 'module_" . $layout["db"]["value"] . "' AND TABLE_SCHEMA = '" . FF_DATABASE_NAME . "' ), 1, 0 ) AS val"); 
                        if($db->nextRecord()) {
                            if($db->getField("val", "Number", true)) {
                                $db->query("SELECT module_" . $layout["db"]["value"] . ".display_view_mode
                                                    FROM module_" . $layout["db"]["value"] . "
                                                    WHERE 1");
                                if($db->nextRecord()) {
                                    do {
                                        if(strlen($db->getField("display_view_mode")->getValue()))
                                            setJsRequest($db->getField("display_view_mode")->getValue());
                                    } while($db->nextRecord());
                                }
                                $params["js_custom_module_is_set"] = true;                                            
                            }
                        }
                    }*/
                    break;
                case "ECOMMERCE":
                    if(AREA_ECOMMERCE_SHOW_MODIFY)
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    break;
                case "LANGUAGES":
                    if(AREA_LANGUAGES_SHOW_MODIFY)
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    break;
                case "SEARCH":
                    //setJsRequest("ff.cms.search", "tools");
                    if(AREA_SEARCH_SHOW_MODIFY)
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    break;
                case "LOGIN":
                    if(AREA_LOGIN_SHOW_MODIFY)
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    break;
                case "COMMENT":
                    if(AREA_COMMENT_SHOW_MODIFY)
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    break;
                case "USER":
                    if(AREA_USERS_SHOW_MODIFY)
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    break;
                case "FORMS_FRAMEWORK":
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
                    break;
                default:
                    if(defined("AREA_" . $layout["type"] . "_SHOW_MODIFY") && constant("AREA_" . $layout["type"] . "_SHOW_MODIFY"))
                        setJsRequest($layout_settings_popup["ADMIN_POPUP_MENU_PLUGIN"]);
            }
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
                        if(is_dir(FF_DISK_PATH . "/conf/gallery/modules/" . $layout["db"]["value"] . "/extra"))
                        	$admin_menu["admin"]["module"]["extra"] = FF_SITE_PATH . VG_SITE_RESTRICTED . "/modules/" . $layout["db"]["value"] . "/extra/" . $layout["db"]["params"];
                        else
                        	$admin_menu["admin"]["module"]["extra"] = "";
                        
                        $admin_menu["sys"]["path"] = $params["user_path"];
                        $admin_menu["sys"]["type"] = "admin_toolbar";
                        $admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
                    }
                    
		            set_cache_data("M", $layout["db"]["value"] . "-" . $layout["db"]["params"]);
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
					$res["content"] = $block["tpl"]["header"] . $res["content"] .  $block["tpl"]["footer"];
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
	               // if($layout["settings"]["AREA_VGALLERY_SHOW_PREVIEW"]) {
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
	               // }

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
            case "COMMENT":
			    /**
			    * Admin Father Bar
			    */            
                if(AREA_COMMENT_SHOW_MODIFY) {
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
                        $admin_menu["admin"]["setting"] = ""; //$layout["type"];
                    }
                    if(MODULE_SHOW_CONFIG) {
                        $admin_menu["admin"]["module"]["value"] = $layout["db"]["value"];
                        $admin_menu["admin"]["module"]["params"] = $layout["db"]["params"];
                    }
                    $admin_menu["sys"]["path"] = $globals->user_path;
                    $admin_menu["sys"]["type"] = "admin_toolbar";
                    $admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
                }

                if(isset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
                    if(is_array($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
                        $cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"]);
                        $cm->oPage->tpl[0]->parse("SectWidgetsHeaders", true);
                    
                        $res["content"] = /*$cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["headers"] .*/ $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["html"] /*. $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]*/;

                        $cm->oPage->tpl[0]->set_var("WidgetsContent", $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["footers"]);
                        $cm->oPage->tpl[0]->parse("SectWidgetsFooters", true);
                    } else {
                        $res["content"] = $cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])];
                    }
                    unset($cm->oPage->components_buffer["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
                }
                if(!strlen($res["content"]) && isset($cm->oPage->contents["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])])) {
                    $res["content"] = $cm->oPage->contents["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]["data"];
                    unset($cm->oPage->contents["MD-" . $layout["location"] . "-" . "comment" . "-" . str_replace("/", "", $layout["db"]["value"] . "-" . $layout["db"]["params"])]);
                }

				/**
				* Process Block Header
				*/	
                $tpl = null;                
				if(check_function("set_template_var"))
					$block = get_template_header($globals->user_path, $admin_menu, $layout, $tpl);
				
				$res["content"] = $block["tpl"]["header"] . $res["content"] .  $block["tpl"]["footer"];
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
                    $admin_menu["sys"]["ret_url"] = $_SERVER["REQUEST_URI"];
                }

                if(check_function("process_forms_framework"))
                    $res = process_forms_framework($layout["db"]["value"], $layout["db"]["params"], $globals->user_path, $layout);

				/**
				* Process Block Header
				*/	                
				if(check_function("set_template_var"))
					$block = get_template_header($globals->user_path, $admin_menu, $layout);
				
				$res["content"] = $block["tpl"]["header"] . $res["content"] .  $block["tpl"]["footer"];
                break;
            default:
                ffErrorHandler::raise("lost static pages type: [" . $layout["type"] . "]", E_USER_WARNING, NULL, NULL);
        }
    }

	//$res["data_blocks"] = $layout["data_blocks"];
	$res["params"] = $params;

	return $res;
}