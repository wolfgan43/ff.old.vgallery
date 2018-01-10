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
function process_vgallery_thumb($user_path, $type, $params = array(), &$layout) 
{
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;

	$class_reset = array();

    $arrKeyNode = array();
	$arrJsRequest = array();
	$block = array();
    $tpl = null;

    $layout["unic_id"] = $layout["prefix"] . $layout["ID"];
    if(is_array($layout["settings"])) {
	    $father_settings = $layout["settings"];
        $node_settings = $layout["settings"];
    } elseif(is_array($params["settings"])) {
        $father_settings = $params["settings"]["/"];
        $node_settings = $params["settings"]["/"];
    }
	check_function("vgallery_init");
//print_r($layout);
	$tpl_data = $params["tpl_data"];
    /**
    *  Define Params for Node Base
    */
    $vg_father_params = array(
	"ID_layout" => $layout["ID"]
        , "unic_id" => $layout["unic_id"]
    	, "vgallery_name" => $params["vgallery_name"]
    	, "user_path" => $user_path
        , "permalink" => $params["permalink"]
        , "parent" => $params["user_path"]
    	, "source_user_path" => $params["source_user_path"]
    	, "group" => $params["group"]
    	, "enable_user_sort" => (isset($_REQUEST[$layout["unic_id"] . "_sort"])
    		? true
    		: false
    	)
    	, "sort_default" => $_REQUEST[$layout["unic_id"] . "_sort"]
    	, "sort_method" => $_REQUEST[$layout["unic_id"] . "_sort_type"]
    	, "title" => $params["title"]
    	, "description" => $params["description"]
    	, "enable_title" => (isset($params["enable_title"])
    		? $params["enable_title"]
    		: ($father_settings["AREA_VGALLERY_LIST_SHOW_TITLE_ONLYHOME"] && $user_path != "/" . $layout["db"]["value"]
    			? false
    			: $father_settings["AREA_VGALLERY_LIST_SHOW_TITLE"]
    		)
    	)
    	, "enable_title_seo" => $father_settings["AREA_VGALLERY_LIST_SHOW_TITLE_BYDIR"]
    	, "enable_sub_title" => (isset($params["enable_sub_title"])
    		? $params["enable_sub_title"]
    		: $father_settings["AREA_VGALLERY_LIST_SHOW_TITLE_BYDIR"]
    	)
    	, "allow_insert" => $params["allow_insert"]
    	, "framework_css" => $params["framework_css"]
    	, "template_name" => $params["template_name"]
    	, "template_default_name" => $params["template_default_name"]
    	, "tpl_path" => ($params["tpl_path"] 
    						? $params["tpl_path"] 
    						: $layout["tpl_path"]
    					)
    	, "template_skip_hide" => $params["template_skip_hide"]
        , "navigation" => $params["navigation"]
        , "display_error" => !$params["skip_error"]
    );    

    switch($type) {
        case "anagraph":
            if($layout["unic_id"])
    			$layout["unic_id"] .= "T";

            $vg_father_params["name"] = "anagraph";
            $vg_father_params["settings_path"] = $params["source_user_path"];
            $vg_father_params["settings_type"] = "anagraph";
            $vg_father_params["settings_prefix"] = "A";
            //$vg_father_params["settings_layout"] = $layout["ID"];
            $vg_father_params["is_dir"] = true;	
            $vg_father_params["allow_insert"] = true;
            if(!is_array($vg_father_params["group"]) && strlen($user_path)) {
            	$vg_father_params["group"]["smart_url"] = basename($user_path);
            	$vg_father_params["settings_path"] = $user_path;
            } 
            break;
        case "learnmore":
            $vg_father_params["name"] = "learnmore";
    	    $vg_father_params["learnmore"] = $params["learnmore"];
            $vg_father_params["ID_layout"] = $params["ID_layout"];
    	    $layout["unic_id"] =  $params["learnmore"]["src_layout"]["prefix"] . $params["learnmore"]["src_layout"]["ID"] . "R" . $params["learnmore"]["ID_vgallery"]; 
    	    $layout["settings"] =  $father_settings;
    	    //$vg_father_params["unic_id_lower"] = strtolower($vg_father_params["unic_id"]);
		    //$vg_father_params["user_path"] = "/" . $learnmore["data_source"];

		    $vg_father_params["settings_path"] = $user_path;	//da gestire 
		    $vg_father_params["settings_type"] = $params["learnmore"]["src"]["table"];  
		    $vg_father_params["settings_prefix"] = "L";
		    $vg_father_params["settings_layout"] = $params["learnmore"]["src_layout"]["ID"];
		    //$vg_father_params["template_suffix"] = "_rel";
                           // print_r($vg_father_params);
            break;
        case "publishing":
            $vg_father_params["name"] = "publishing";
		    $vg_father_params["publishing"] = $params["publishing"];
		    $vg_father_params["settings_path"] = null;
		    $vg_father_params["settings_type"] = "publishing";
		    $vg_father_params["settings_layout"] = $layout["ID"];
		    $vg_father_params["settings_prefix"] = "P";	
		    $vg_father_params["is_dir"] = false;	
            break;
        case "wishlist":
            $vg_father_params["name"] = "wishlist";
		    $vg_father_params["wishlist"] = $params["wishlist"];
		    $vg_father_params["settings_path"] = VG_SITE_SEARCH . "/wishlist";
		    $vg_father_params["settings_type"] = basename(VG_SITE_SEARCH);
		    $vg_father_params["settings_layout"] = $layout["ID"];
		    $vg_father_params["settings_prefix"] = "W";
		    $vg_father_params["is_dir"] = true;
            break;
        default:
            if($layout["unic_id"])
    			$layout["unic_id"] .= "T";

            $vg_father_params["name"] = "vgallery";
		    $vg_father_params["settings_path"] = $user_path;
		    $vg_father_params["settings_type"] = "vgallery_nodes";
		    $vg_father_params["settings_prefix"] = "T";
    }

    $vg_father_params["unic_id"] = $layout["unic_id"];
    //$vg_father_params["unic_id_lower"] = strtolower($vg_father_params["unic_id"]);

    if($params["search"] !== null)
    {
    	$vg_father_params["allow_edit"] = false;
    	$vg_father_params["allow_insert"] = false;
    	$vg_father_params["allow_insert_dir"] = false;
    	$vg_father_params["search"] = $params["search"];
    	if(is_array($vg_father_params["search"]) && $params["settings_path"]) {
    		if($params["settings_path"] !== true) {
				$vg_father_params["settings_path"] = $params["settings_path"];

				$vg_father_params["settings_type"] = (isset($params["settings_type"]) 
					? $params["settings_type"] 
					: basename(VG_SITE_SEARCH)
				);
			}
			$vg_father_params["settings_layout"] = "";
			$vg_father_params["settings_prefix"] = "S";
		}
        $vg_father_params["template_skip_hide"] = true;
	} elseif(is_array($globals->filter) && count($globals->filter)) {
    	foreach($globals->filter AS $filter_key => $filter_value) {
    		switch($filter_key) {
    			case "first_letter":
    				if($filter_value == "0-9") {
    					$vg_father_params["search"]["filter"]["meta_title_alt"] = " REGEXP '^[0-9]' ";    			
    				} else {
						$vg_father_params["search"]["filter"]["meta_title_alt"] = $filter_value . "%";    			
					}
    				break;
                case "place":
                    $vg_father_params["search"]["place"] = $filter_value;    			
    			default:
    		}
    	}
    }
	//print_r($vg_father_params);
	
	if(is_array($params["limit"]) && count($params["limit"]))
		$vg_father_params["limit"] = $params["limit"];

	if(!$vg_father_params["settings_path"])
		$vg_father_params["settings_path"] = $layout["db"]["real_path"];

    /**
    *  Load Node Base Information
    */
    $vg_father = process_vgallery_father($vg_father_params, "thumb");

	//print_r($vg_father);
    if(!$vg_father || !$vg_father["available"] || !$vg_father["permission"]["visible"])
        return null; 
	//$layout["unic_id"] = $vg_father_params["unic_id"];    
	
    /**
    * Override Block Grid System
    */
    if(is_array($vg_father["template"]["container_class"])) {
        if(is_array($layout["class"])) {
            $layout["class"] = array_replace($layout["class"], $vg_father["template"]["container_class"]);
        } else {
            $layout["class"] = $vg_father["template"]["container_class"];
        }
    }
	
    if(is_array($vg_father["template"]["wrap"]) && !is_bool($vg_father["template"]["wrap"]["container"]) && strlen($vg_father["template"]["wrap"]["container"])) {
        $wrap_container["prefix"] = '<div class="vg-wrap ' . $vg_father["template"]["wrap"]["container"] . '">';
        $wrap_container["postfix"] = '</div>';             
    } else {
        $wrap_container["prefix"] = '<div class="vg-wrap">';
        $wrap_container["postfix"] = '</div>';             
    }
    
	/**
    * Admin Father Bar
    */
    $admin_menu = process_vgallery_admin_bar($vg_father, $layout);
	if(is_array($admin_menu))
		$enable_error = true;
	else
		$enable_error = false;
    /**
    *  Check Primary Visualization
    */
    //return array("content" => '<div class="block' . $block_layout_class . (is_array($layout["class"]) ? " " . implode(" ", $layout["class"]) : "") . $fixed_class . ($static_class ? " " . trim($static_class, "-") : "") . '" id="' . ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $unic_id)) . '"' . $block_properties . '>' . $tpl->rpparse("main", false) . '</div>');
   //print_r($layout);
	/**
	* Process Block Header
	*/	

    if(!count($vg_father) && check_function("set_template_var") && check_function("process_html_page_error")) {
		$block = get_template_header($user_path, $admin_menu, $layout);
		
        $buffer = ($enable_error 
                    ? $wrap_container["prefix"] .  process_html_page_error() . $wrap_container["postfix"]
                    : ""
                );
        if($params["navigation"] || $params["search"]) 
            http_response_code($cm->isXHR() ? 204 : 404); 

        $buffer_isset = true;
	}

	/**
	* Process Block Header
	*/	
    if($vg_father["hide_template"] && check_function("set_template_var") && check_function("process_html_page_error")) {
		$block = get_template_header($user_path, $admin_menu, $layout);

        $buffer = ($enable_error 
                    ? $wrap_container["prefix"] . process_html_page_error(ffTemplate::_get_word_by_code("vgallery_is_hidden_by_properties")) . $wrap_container["postfix"]
                    : ""
                );
        
        $buffer_isset = true;
	}	
//    print_r($vg_father);    
	if(!$buffer_isset) 
	{
	    /**
	    * Load Template
	    */  

	    if(isset($tpl_data["type"])) {
	        $tpl_data["custom"] = $vg_father["template"]["custom_name"] . "." . $tpl_data["type"];
	        $tpl_data["base"] = "vgallery" . $vg_father["template"]["suffix"] . "." . $tpl_data["type"];
	        $tpl_data["is_html"] = false;
	    } else {
	        $tpl_data["custom"] = $vg_father["template"]["custom_name"] . $vg_father["template"]["suffix"] . ".html";
	        $tpl_data["base"] = strtolower($vg_father["template"]["name"]) . $vg_father["template"]["suffix"] . ".html";
	        $tpl_data["is_html"] = true;
	    }

	    $tpl_data["result"] = get_template_cascading($vg_father["user_path"], $tpl_data, $vg_father["template"]["path"]);

	    $tpl_data["obj"] = ffTemplate::factory($tpl_data["result"]["path"]);
	    $tpl_data["obj"]->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   	
	    
	    if($vg_father["type"] == "learnmore" && $vg_father["navigation"] && $tpl_data["result"]["type"] == "custom" && !$tpl_data["obj"]->isset_var("pagination")) {
		    $vg_father["navigation"] = false;
		    $vg_father["enable_found_rows"] = false;
	    }
	    
		/**
		* Load Fields by Type
		*/

	/////////////////
	// DA scremare sia in process_vgallery_type che in process_vgallery_node_data 
	// in base ai campi che effettivamente vengono usati
	//e quindi vgallery_fields.enable_thumb vale per lo standard, il value per la pubblicazione
	// e relationship valgono i datalimit passati
	//FORSE FATTA LA PARTE SOTTA VEDI PRELOAD E VG_FIELD[KEYS]


	// Oltre a cio creare e ripristinare la cache delle relationship salvata dentro alle description dei singoli campi

	//Sistemare bene i 'media' eliminando il glob e basandosi sui nomi descritti nel campo description.
	//Fare solo 1 get file permission prima del foreach dei campi per i media


	//Gestire a castata le relationship con opportuno blocco di ridondanza per evitare effetto specchio col proprio riflesso



	/////////////////

	    //echo  $vg_father["limit"]["fields"] . "<br>";

		$vg_field = process_vgallery_type($vg_father, $tpl_data, $layout);
		/**
		* Load Record
		*/
		$vg = process_vgallery_node($vg_father, $father_settings, $vg_field);	
	    if (is_array($vg)) 
	    {
		    $count_files = 0;
            $count_field_per_row_empty = 0;

	        $switch_style_row = false;
	        $switch_style_col = false;
	        $col = 0;

	        $vg_field_sort 	= array();

	        if(count($vg["data"])) 
	        {			
			    if($vg_father["type"] != "learnmore") {
				    if(is_array($vg_field["relationship"]) && count($vg_field["relationship"]))
					    $vg_rel = process_vgallery_node_relationship($vg_father, $vg["key"], $vg_field["relationship"]);
			        /**
			        * Load Basic Data for Each Node
			        */
				    process_vgallery_node_data($vg_father, $vg_field, $vg, $vg_rel);
			    }

				//Load JS Plugin
			    if($vg_father["properties"]["plugin"]["name"] && $vg_father["properties"]["image"]["fields"]) {
			        $arrJsRequest[$vg_father["properties"]["plugin"]["name"]] = true;
			    }

			    /**
			    * Process Items
			    */
                    //$array_key = array_keys($vg["data"]);
                    //$last_key = end($array_key);
	            foreach($vg["data"] AS $vg_data_key => $vg_data_value) 
	            {
            		$count = array();
					
                    set_cache_data("V", $vg_data_value["ID"],  $vg_father["settings_prefix"] . $vg_father["ID_vgallery"]); 
		            //$globals->cache["data_blocks"]["V" . $vg_father["settings_prefix"] . "-" . $vg_data_value["ID"]] = $vg_data_value["ID"];

		            if($params["output"]) {
		                $arrKeyNode["vg-" . $vg_data_value["ID"]] = $vg_data_value["ID"];
		            }

	                //if(!$vg_data_value["permalink_parent"]) //non trovo una casistica in cui serva anzi in molti casi distrugge gli url
	                  //  $vg_data_value["permalink_parent"] = substr($vg_data_value["parent"], strlen($vg_father["vgallery_name"]) + 1);
	                if(!$vg_data_value["permalink_parent"])
	                    $vg_data_value["permalink_parent"] = $vg_data_value["parent"];                    
	                if(!$vg_data_value["smart_url"])
	                    $vg_data_value["smart_url"] = $vg_data_value["name"];
	                if(!$vg_data_value["title"])
	                    $vg_data_value["title"] = $vg_data_value["data"]["meta_title"];
	                if(!$vg_data_value["description"])
	                    $vg_data_value["description"] = $vg_data_value["data"]["meta_description"];
	                if(!$vg_data_value["header_title"])
	                    $vg_data_value["header_title"] = $vg_data_value["data"]["meta_title_alt"];
	                if(!$vg_data_value["keywords"])
	                    $vg_data_value["keywords"] = $vg_data_value["data"]["keywords"];
	                if(!$vg_data_value["tags"])
	                    $vg_data_value["tags"] = $vg_data_value["tags"]; // todo: DA gestire
					
					                    
                    if(is_array($params["settings"])) {
                        $settings_key = substr($vg_data_value["parent"], 0, strpos($vg_data_value["parent"], "/", 1));
                        if(isset($params["settings"][$settings_key]))
                            $node_settings = $params["settings"][$settings_key];
                        else
                            $node_settings = $father_settings;
                    }
                    
	                $params_fields = pre_process_vgallery_tpl_fields($tpl_data["obj"], $vg_father, $vg_field, $vg_data_value, $node_settings);
	                $params_fields["tpl"] = $tpl_data;
	                $params_fields["enable_sort"] = $node_settings["AREA_VGALLERY_SHOW_ORDER"];
	                $params_fields["enable_error"] = $enable_error;
	                $params_fields["reset"] = true;

				    /** 
				    * Process Field
				    */ 
				    
	                if(is_array($vg_field["fields"][$vg_data_value["ID_type"]]) && count($vg_field["fields"][$vg_data_value["ID_type"]]))  
	                {
	                    foreach($vg_field["fields"][$vg_data_value["ID_type"]] AS $parent_group_key => $parent_group_value) 
	                    {
							$parsed_field = process_vgallery_tpl_fields($tpl_data["obj"]
																		, $vg_father
																		, $vg_field["fields"][$vg_data_value["ID_type"]][$parent_group_key]
																		, $vg_data_value
																		, $layout
																		, $params_fields
																	);
              				
              				$vg_field_sort 	= $parsed_field["sort"];
	                        $count["field"] =  $count["field"] + $parsed_field["count"];
	                        $count["total_desc"] = $count["total_desc"] + $parsed_field["count_desc"];
	                        $count["total_img"] = $count["total_img"] + $parsed_field["count_img"];

	                        if($parsed_field["count_desc"]) 
	                        {
								if($count["field"] && is_array($parsed_field["js_request"]) && count($parsed_field["js_request"]))
									$arrJsRequest = array_replace($arrJsRequest, $parsed_field["js_request"]);

 								if(strlen($parent_group_key) || count($vg_field["fields"][$vg_data_value["ID_type"]]) > 1) {
 									$class_group = array();

									if(strlen($parent_group_key)) {
										$class_group[ffCommon_url_rewrite(preg_replace('/[^a-zA-Z]/', '', $parent_group_key))] = true;
			                        }                            

									if(count($class_group))
		                        		$tpl_data["obj"]->set_var("group_class", " " . implode(" ", array_filter(array_keys($class_group))));

			                        $tpl_data["obj"]->parse("SezVGalleryGroupContainerStart", false);
			                        $tpl_data["obj"]->parse("SezVGalleryGroupContainerEnd", false);
								}

		                        $tpl_data["obj"]->parse("SezVGalleryGroup", true);
		                        $tpl_data["obj"]->set_var("group_class", "");								
	                        }      
	                        
	                        $params_fields["reset"] = false;                    
	                    }
	                }

		            if(!$count["field"] && !$vg_father["is_custom_template"]) {
		                $count_field_per_row_empty++;
		                continue;
		            } else {
		                $count_files++;
		                $col++;	            
		            }
	                /**
	                * Set Col Class for each Item
	                */
	                
	                
	                $item_class = $vg_father["template"]["class"];
					$item_properties = $params_fields["htmltag"]["properties"];

	                if(is_array($params_fields["htmltag"]["class"]))
	                    $item_class = array_replace($item_class, $params_fields["htmltag"]["class"]);
					
	                if($vg_father["template"]["framework"]) 
	                {
	                    if($vg_father["properties"]["thumb_per_row"] > 1)
                		    $item_class["lvl"] = "col" . $col;
                		
                		if(is_array($vg_father["template"]["wrap"]) && $vg_father["template"]["wrap"]["row"] !== false) {
                			$item_class["oddeven"] = ($switch_style_col 
                										? "odd"
                										: "even"
                									);

                			$switch_style_col = !$switch_style_col;
						}
	                } else {
	                    if($vg_father["properties"]["thumb_per_row"] > 1)
                		    $item_class["lvl"] = "vgallery_col" . $col;
	                }

					$item_class["base"] = "vg-item";
					if(!$user_path) { //add class for vgallery aggregation
						$arrParent = explode("/", trim($vg_data_value["parent"], "/"));
						$item_class["vgallery"] = $arrParent[0];
					}

					//$tpl_data["obj"]->set_var("admin", "");
	                $popup = process_vgallery_admin_popup($vg_father, $vg_data_value, $layout);
					if(is_array($popup)) 
					{
						if(check_function("set_template_var"))
							$item_properties["admin"] = 'data-admin="' . get_admin_bar($popup, VG_SITE_FRAME . $vg_father["source_user_path"]) . '"';

	                    //$serial_popup = json_encode($popup);
	                    //$item_properties["admin"] = 'data-admin="' . FF_SITE_PATH . VG_SITE_FRAME . $vg_father["source_user_path"] . "?sid=" . set_sid($serial_popup, $popup["admin"]["unic_name"] . " P") . '"';

                    	$item_class["admin"] = "admin-bar";
	                }                  

					if($father_settings["AREA_VGALLERY_THUMB_EQUALIZER"])
						$item_properties["equalizer"] = cm_getClassByFrameworkCss("equalizer-col", "util");
						
					if($father_settings["AREA_VGALLERY_THUMB_SHOW_FILTER_AZ"])
						$item_properties["filter-az"] = "data-ffl=" . substr($vg_data_value["smart_url"], 0, 1);
					
					if($father_settings["AREA_VGALLERY_THUMB_FULLCLICK"])
						$item_properties["fullclick"] = 'data-fullclick="' . $vg_data_value["permalink"] . '"';
	                
	                //$item_class["slug"] = $vg_data_value["smart_url"]; //troppo lunga per poter essere usata come selettore
	                //print_r($vg_data_value);
					if($vg_data_value["class"])
			    		$item_class["custom"] = $vg_data_value["class"];
					if($vg_data_value["highlight"]["container"])
                		$item_class["grid"] = cm_getClassByFrameworkCss(explode(",", $vg_data_value["highlight"]["container"]), "col");
					
					if($vg_father["wishlist"] === null && !($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE) && USE_CART_PUBLIC_MONO && $vg_data_value["is_wishlisted"])
						$item_class["wishlist"] = "wishlisted";
	                
					if(($count_files + $count_field_per_row_empty) == count($vg["data"]))
						$item_class["end_row"] = "end";

	                /**
	                * Set Row Class
	                */

	                if(is_int($count_files / $vg_father["properties"]["thumb_per_row"])
	                    || ((($count_files + $count_field_per_row_empty) == count($vg["data"])) && !is_int($count_files / $vg_father["properties"]["thumb_per_row"]))
	                ) {
	                    $col = 0;
	                    $row_class = array();
	                     if($vg_father["template"]["framework"]) {
	                        if((is_array($vg_father["template"]["wrap"]) && $vg_father["template"]["wrap"]["row"] !== false) || $vg_father["is_custom_template"]) {
	                            if(!is_bool($vg_father["template"]["wrap"]["row"]) && strlen($vg_father["template"]["wrap"]["row"]))
	                                $row_class["grid"] = $vg_father["template"]["wrap"]["row"];

	                            $row_class["oddeven"] = ($switch_style_row 
	                                                    ? "odd"
	                                                    : "even"
	                                                );

	                            $str_row_class = implode(" ", array_filter($row_class));
	                        }
	                    } else {
	                        $str_row_class = ($switch_style_row
	                                            ? $vg_father["vgallery_name"] . "_positive"
	                                            : $vg_father["vgallery_name"] . "_negative"

	                                    );                   
	                    }

	                    $switch_style_row = !$switch_style_row;
	                }
	            


	                /**
	                * Parse Col Class and ID for each Items
	                */
	                if(!$tpl_data["is_html"]) {
	                    if(isset($tpl_data["tag" . $vg_father["template"]["suffix"]]["vgallery_row"]) && strlen($tpl_data["tag" . $vg_father["template"]["suffix"]]["vgallery_row"])) {
	                        $tpl_data["obj"]->set_var("tag" . $vg_father["template"]["suffix"] . "_vgallery_item", $tpl_data["tag" . $vg_father["template"]["suffix"]]["vgallery_row"]);
	                    } else {
	                        $tpl_data["obj"]->set_var("tag" . $vg_father["template"]["suffix"] . "_vgallery_item", "item");
	                    }
	                }

                    if($vg_father["is_custom_template"]) {
                        parse_vgallery_tpl_custom_vars(
                            $tpl_data["obj"]
                            , $params_fields
                            , $vg_father["unic_id"] . "-" . $vg_data_value["name"]
                            , $item_class
                            , $item_properties
                        );

                        $tpl_data["obj"]->parse("Item", true);
                    } else {
                        $tpl_data["obj"]->set_var("real_name", preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["unic_id"] . $vg_data_value["name"]));
                        if(is_array($item_class) && count($item_class))
                            $item_properties["class"] = 'class="' . implode(" ",array_filter($item_class)) . '"';

                        $tpl_data["obj"]->set_var("item_properties", " " . implode(" ", array_filter($item_properties)));
		                if($count["total_img"]) {
		                    if($count["total_desc"]) {
		                         if($vg_father["template"]["field"]["desc"]) {
		                            $tpl_data["obj"]->set_var("desc_class", " " . $vg_father["template"]["field"]["desc"]);
								 }
		                        $tpl_data["obj"]->parse("SezVGalleryDescriptionStart", false);
		                        $tpl_data["obj"]->parse("SezVGalleryDescriptionEnd", false);
		                    }
		                    
				            if($vg_father["template"]["field"]["img"])
		    			        $tpl_data["obj"]->set_var("img_class", " " . $vg_father["template"]["field"]["img"]);

				            $tpl_data["obj"]->parse("SezVGalleryImage" . $vg_father["template"]["field"]["location"], false);
				            $tpl_data["obj"]->set_var("SezVGalleryImage" . $vg_father["template"]["field"]["location"] . "Node", "");
		                }

	                    
	                    $tpl_data["obj"]->parse("SezVGallery", true);
                        $tpl_data["obj"]->set_var("SezVGalleryImageTop", ""); 
                        $tpl_data["obj"]->set_var("SezVGalleryImageBottom", ""); 
	                    $tpl_data["obj"]->set_var("SezVGalleryGroup", "");
	                    $tpl_data["obj"]->set_var("SezVGalleryDescriptionStart", "");
	                    $tpl_data["obj"]->set_var("SezVGalleryDescriptionEnd", "");
	                }   

	                /**
	                * Parse Row Class
	                */
	                
	                if(is_int($count_files / $vg_father["properties"]["thumb_per_row"])
	                    || ((($count_files + $count_field_per_row_empty) == count($vg["data"])) && !is_int($count_files / $vg_father["properties"]["thumb_per_row"]))
	                ) {
	                    if($vg_father["is_custom_template"]) {
	                        if($tpl_data["obj"]->isset_var("Row")) {
	                            if(is_array($row_class) && count($row_class)) {
	                                foreach($row_class AS $row_class_key => $row_class_value) {
	                                    if($tpl_data["obj"]->isset_var("class:" . $row_class_key))
	                                        $tpl_data["obj"]->set_var("class:" . $row_class_key, $row_class_value);
	                                }
	                            }

	                            $tpl_data["obj"]->set_var("class", $str_row_class);
	                            $tpl_data["obj"]->parse("Row", true);
	                            $tpl_data["obj"]->set_var("Item", "");
	                        }
	                    } elseif($tpl_data["is_html"]) { 
	                        if(count($vg["data"]) > $vg_father["properties"]["thumb_per_row"]) {
	                            $col = 0;

						        $row_class = array();
 						        if($vg_father["template"]["framework"]) {
		                            if(is_array($vg_father["template"]["wrap"]) && $vg_father["template"]["wrap"]["row"] !== false) {
		                                $tpl_data["obj"]->set_var("row_class", ($str_row_class ? " " . $str_row_class : ""));
		                                $tpl_data["obj"]->parse("SezVGalleryRowStart", false);
		                                $tpl_data["obj"]->parse("SezVGalleryRowEnd", false);
		                            } else {
		                                $tpl_data["obj"]->set_var("SezVGalleryRowStart", "");
		                                $tpl_data["obj"]->set_var("SezVGalleryRowEnd", "");
		                            }
						        } else {
                			        $tpl_data["obj"]->set_var("vg_switch_style", $str_row_class);                   
						        }
	                        }
	                        $tpl_data["obj"]->parse("SezVGalleryRow", true);
	                        $tpl_data["obj"]->set_var("SezVGallery", "");
	                    }
	                } 
	            }
	            

 				/**
	            * Set Hidden Params
	            */
	            if(is_array($vg_father["search"]) && array_key_exists("param", $vg_father["search"])) {
	                $vg_father["search"]["hidden"] = '<input name="search_param" type="hidden" value="' . $vg_father["search"]["param"] . '" />';
	            }            

	            /**
	            * Parse Title And Set Wishlist Title
	            */
	            $vg_father["title:wishlist"] = ($vg_father["wishlist"]
	                                ?   (strlen($vg_father["wishlist"]["name_fake"]) 
	                                        ? '<h2 class="wishlist-name">' . $vg_father["wishlist"]["name_fake"] . '</h2>' 
	                                        : ""
	                                    ) . ffTemplate::_get_word_by_code("wishlist_of") 
	                                    . '<span class="wishlist-owner">' 
	                                    . $vg_father["wishlist"]["owner_name"] 
	                                    . '</span>' 
	                                    . $vg_father["wishlist"]["date_info"]
	                                : ""
	                            );

            	$max_col["class"] = cm_getClassByFrameworkCss(array(12), "col");
            	$max_col["prefix"] = '<div class="' . $max_col["class"] . '">';
            	$max_col["postfix"] = '</div>'; 

				if($vg_father["is_custom_template"]) {
	                if($tpl_data["obj"]->isset_var("title") && $vg_father["title"])
	                    $tpl_data["obj"]->set_var("title", "<h1>" . $vg_father["title"] . "</h1>");

	                if($tpl_data["obj"]->isset_var("title:content"))
	                    $tpl_data["obj"]->set_var("title:content", $vg_father["title"]);

	                if($tpl_data["obj"]->isset_var("description") && $vg_father["description"])
	                    $tpl_data["obj"]->set_var("description", "<p>" . $vg_father["description"] . "</p>");

	                if($tpl_data["obj"]->isset_var("description:content"))
	                    $tpl_data["obj"]->set_var("description:content", $vg_father["description"]);	                    
	                    
	                if($tpl_data["obj"]->isset_var("title:wishlist"))
	                    $tpl_data["obj"]->set_var("title:wishlist", $vg_father["title:wishlist"]);

	                if($tpl_data["obj"]->isset_var("fixed_pre_content"))
		                $tpl_data["obj"]->set_var("fixed_pre_content", $max_col["prefix"] . $params["fixed_pre_content"] . $max_col["postfix"]);

	                if($tpl_data["obj"]->isset_var("fixed_post_content"))
		                $tpl_data["obj"]->set_var("fixed_post_content", $max_col["prefix"] . $params["fixed_post_content"] . $max_col["postfix"]);
	            } else {
            		//Set max col class if thumb_per_row > 1 and no Wrap
            		if($vg_father["properties"]["thumb_per_row"] > 1 && (!is_array($vg_father["template"]["wrap"]) || $vg_father["template"]["wrap"]["container"] === false)) { 
            			$tpl_data["obj"]->set_var("max_col_class", " " . $max_col["class"]);
	                    
	                    if($vg_father["navigation"] && is_object($vg_father["navigation"]["obj"]))
	                        $vg_father["navigation"]["obj"]->framework_css["component"]["class"] = $vg_father["navigation"]["obj"]->framework_css["component"]["class"] . ($max_col["class"] ? " " .cm_getClassByFrameworkCss("", "row") : "");
					}
	                if (/*$vg_father["enable_title"] &&*/ strlen($vg_father["title"])) {
				        if($tpl_data["is_html"]) {
	                        if(strlen($father_settings["AREA_VGALLERY_LIST_TITLE_HTMLTAG"]))
	                            $htmltag_title = $father_settings["AREA_VGALLERY_LIST_TITLE_HTMLTAG"];
	                        else
	                            $htmltag_title = "h" . ($vg_father["type"] == "publishing" 
		                                                     ? 3 
		                                                     : 2
	                                                     ); 
	                        if($htmltag_title)
	                    		$tpl_data["obj"]->set_var("name_title", $max_col["prefix"] . '<' . $htmltag_title . '>' . $vg_father["title"] . $vg_father["title:wishlist"] . '</' . $htmltag_title . '>' . $max_col["postfix"]);
		                    else
	                    		$tpl_data["obj"]->set_var("name_title", $max_col["prefix"] . $vg_father["title"] . $vg_father["title:wishlist"]) . $max_col["postfix"];
			            } else {
	                        if(isset($tpl_data["tag"]["vgallery"]) && strlen($tpl_data["tag"]["vgallery"])) {
	                            $tpl_data["obj"]->set_var("tag_vgallery", $tpl_data["tag"]["vgallery"]);
	                        } else {
	                            $tpl_data["obj"]->set_var("tag_vgallery", $vg_father["vgallery_name"]);
	                        }
	                        
	                        if(!strlen($vg_father["template"]["suffix"]) && strlen($tpl_data["tag"]["title"])) {
	                            if(isset($tpl_data["tag"]["title"]) && strlen($tpl_data["tag"]["title"])) {
	                                $tpl_data["obj"]->set_var("tag_title", $tpl_data["tag"]["title"]);
	                            } else {
	                                $tpl_data["obj"]->set_var("tag_title", "title");
	                            }
	                        }
		                    $tpl_data["obj"]->set_var("name_title", $vg_father["title"] . $vg_father["title:wishlist"]);
		                   // $tpl_data["obj"]->parse("SezVGalleryTitle", false);
			            }
	                }

	                if($vg_father["description"]) {
	                	if($tpl_data["is_html"]) {
		                    $tpl_data["obj"]->set_var("description_title", '<p>' . $vg_father["description"] . '</p>');
						} else {
							if(!strlen($vg_father["template"]["suffix"]) && strlen($tpl_data["tag"]["description"])) {
	                            if(isset($tpl_data["tag"]["title"]) && strlen($tpl_data["tag"]["description"])) {
	                                $tpl_data["obj"]->set_var("tag_description", $tpl_data["tag"]["description"]);
	                            } else {
	                                $tpl_data["obj"]->set_var("tag_description", "description");
	                            }
	                        }
							$tpl_data["obj"]->set_var("description_title", $vg_father["description"]);
						}
	                }
	                
	                $fixed_pre_content = $params["fixed_pre_content"] . $father_settings["AREA_VGALLERY_THUMB_FIXED_PRE_CONTENT"];
					if($fixed_pre_content)	                
	                	$tpl_data["obj"]->set_var("fixed_pre_content", $max_col["prefix"] . $fixed_pre_content . $max_col["postfix"]);
	                
	                $fixed_post_content = $father_settings["AREA_VGALLERY_THUMB_FIXED_POST_CONTENT"] . $params["fixed_post_content"];
					if($fixed_post_content)	                
	                	$tpl_data["obj"]->set_var("fixed_post_content", $max_col["prefix"] . $fixed_post_content . $max_col["postfix"]);
	            }            
	            
	            /**
	            * Parse Sorting
	            */
	            if($father_settings["AREA_VGALLERY_SHOW_ORDER"] && $vg_father["enable_sort"]) 
	            {
	                $vg_father["sort"]["hidden"] = '<input type="hidden" name="' . $vg_father["unic_id"] . '_sort" value="' . $vg_father["sort_default"] . '" />'
	                                            . '<input type="hidden" name="' . $vg_father["unic_id"] . '_sort_type" value="' . $vg_father["sort_method"] . '" />';

	                if($father_settings["AREA_VGALLERY_ORDER_SHOW_TITLE"]) { 
	                    $vg_father["sort"]["title"] = ffTemplate::_get_word_by_code("sort_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($vg_father["vgallery_name"])) . "_title");
	                }
	                
	                if($father_settings["AREA_VGALLERY_ORDER_LIST_BY_LAST_UPDATE"]) {
	                    $vg_father["sort"]["fields"][] = '<li'
	                                                        . ($vg_father["sort_default"] == "lastupdate"
	                                                            ? ' class="' . cm_getClassByFrameworkCss("current", "util") . '"'
	                                                            : ""
	                                                        )
	                                                        . '><a href="javascript:vgSort(\'' . $vg_father["unic_id"] . '\', \'lastupdate\');">' 
	                                                            . ffTemplate::_get_word_by_code("sort_by_" . "lastupdate") 
	                                                        . '</a>'
	                                                    . '</li>';
	                }
	                if(is_array($vg_field_sort["img"]) && count($vg_field_sort["img"])) {
	                    foreach ($vg_field_sort["img"] AS $vg_field_sort_key => $vg_field_sort_value) {
	                        if(!$father_settings["AREA_VGALLERY_SHOW_ORDER_NOLINK"])
	                            continue;

	                        $vg_father["sort"]["fields"][] = '<li'
	                                                            . ($father_settings["AREA_VGALLERY_SHOW_ORDER_NOLINK_HIDE_LABEL"]
	                                                                ? 'class="' . ffTemplate::_get_word_by_code("sort_by_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($vg_field_sort_value["name"]))) . '">'
	                                                                : '>' . ffTemplate::_get_word_by_code("sort_by_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($vg_field_sort_value["name"])))
	                                                            ) 
	                                                        . '</li>';
	                    }
	                }                

	                if(is_array($vg_field_sort["desc"]) && count($vg_field_sort["desc"])) {
	                    foreach ($vg_field_sort["desc"] AS $vg_field_sort_key => $vg_field_sort_value) {
	                        if($vg_field_sort_value["enable"]) {
	                            $vg_father["sort"]["fields"][] = '<li'
	                                                                . ($vg_father["sort_default"] == $vg_field_sort_value["name"]
	                                                                    ? ' class="' . cm_getClassByFrameworkCss("current", "util") . '"'
	                                                                    : ""
	                                                                )
	                                                                . '><a href="javascript:vgSort(\'' . $vg_father["unic_id"] . '\', \'' . $vg_field_sort_value["name"] . '\');">' 
	                                                                    . ffTemplate::_get_word_by_code("sort_by_" . ffTemplate::_get_word_by_code("sort_by_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($vg_field_sort_value["name"])))) 
	                                                                . '</a>'
	                                                            . '</li>';                                
	                        } elseif($father_settings["AREA_VGALLERY_SHOW_ORDER_NOLINK"]) {
	                            $vg_father["sort"]["fields"][] = '<li'
	                                                        . ($father_settings["AREA_VGALLERY_SHOW_ORDER_NOLINK_HIDE_LABEL"]
	                                                            ? 'class="' . ffTemplate::_get_word_by_code("sort_by_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($vg_field_sort_value["name"]))) . '">'
	                                                            : '>' . ffTemplate::_get_word_by_code("sort_by_" . preg_replace('/[^a-zA-Z0-9]/', '', strtolower($vg_field_sort_value["name"])))
	                                                        )
	                                                    . '</li>';                                
	                        }
	                    }
	                }
	                if(is_array($vg_father["sort"]["fields"])) {
	                    sort($vg_father["sort"]["fields"]);
	                    if($vg_father["is_custom_template"]) {
	                        if($tpl_data["obj"]->isset_var("sort")) {
	                            $tpl_data["obj"]->set_var("sort", ($vg_father["search"]["hidden"]
	                                                        ? $vg_father["search"]["hidden"]
	                                                        : ""
	                                                    ) 
	                                                    . $vg_father["sort"]["hidden"]
	                                                    . $vg_father["sort"]["title"]
	                                                    . implode("", $vg_father["sort"]["fields"])
	                            );
	                        }
	                        if($tpl_data["obj"]->isset_var("sort:title")) {
	                            $tpl_data["obj"]->set_var("sort", $vg_father["sort"]["title"]);
	                        }
	                        if($tpl_data["obj"]->isset_var("sort:fields")) {
	                            $tpl_data["obj"]->set_var("sort", ($vg_father["search"]["hidden"]
	                                                        ? $vg_father["search"]["hidden"]
	                                                        : ""
	                                                    ) 
	                                                    . $vg_father["sort"]["hidden"]
	                                                    . implode("", $vg_father["sort"]["fields"])
	                            );
	                        }
	                    } else {
	                        $tpl_data["obj"]->set_var("sort_title", $vg_father["sort"]["title"]);
	                        $tpl_data["obj"]->set_var("sort_fields", ($vg_father["search"]["hidden"]
	                                                        ? $vg_father["search"]["hidden"]
	                                                        : ""
	                                                    ) 
	                                                    . $vg_father["sort"]["hidden"]
	                                                    . implode("", $vg_father["sort"]["fields"])
	                            );
	                        
	                        $tpl_data["obj"]->parse("SezSort", false);
	                        
	                    }
	                }
	            }
	            
		        if ($count_files) 
		        {
	                if($vg_father["is_custom_template"]) {
	                    // Parse Archive Path
	                    $tpl_data["obj"]->set_var("archive:url", $vg_father["user_path"]);
	                    
	                    // Parse Wrap Class
	                    if($tpl_data["obj"]->isset_var("wrap:class") && is_array($vg_father["template"]["wrap"]) && !is_bool($vg_father["template"]["wrap"]["container"]))
	                        $tpl_data["obj"]->set_var("wrap:class", $vg_father["template"]["wrap"]["container"]);

						/**
						* Page Navigator
						*/
						if($vg_father["navigation"]) 
						{
		                    if($tpl_data["obj"]->isset_var("pagination") && ($vg_father["navigation"]["tot_page"] > 1 || $vg_father["navigation"]["infinite"])) {
		                        $tpl_data["obj"]->set_var("pagination", (!$vg_father["sort"] && $vg_father["search"]["hidden"]
		                                                        ? $vg_father["search"]["hidden"]
		                                                        : ""
		                                                    )
		                                                    . $vg_father["navigation"]["hidden"] 
		                                                    . $vg_father["navigation"]["obj"]->process(false)
		                                                );
		                    }
		                    if($tpl_data["obj"]->isset_var("alphanum") && $vg_father["navigation"]["alphanum"]) {
		                        $tpl_data["obj"]->set_var("alphanum", $vg_father["navigation"]["alphanum"]["obj"]->rpparse("main", false));
		                    }
						}
	                } else {
	                    /**
	                    * Parse Archive Path
	                    */
	                    $tpl_data["obj"]->set_var("show_file_archive", $vg_father["user_path"]);

	                    /**
	                    * Parse Wrap Class
	                    */
	                    if($vg_father["template"]["framework"]) {
	                        if(is_array($vg_father["template"]["wrap"]) && $vg_father["template"]["wrap"]["container"] !== false) {
	                            if(!is_bool($vg_father["template"]["wrap"]["container"]) && strlen($vg_father["template"]["wrap"]["container"]))
	                                $tpl_data["obj"]->set_var("wrap_class", " " . $vg_father["template"]["wrap"]["container"]);

	                            $tpl_data["obj"]->parse("SezVGalleryContainerStart", false);
	                            $tpl_data["obj"]->parse("SezVGalleryContainerEnd", false);
	                        }
	                    }
	                    
	                    /**
	                    * Page Navigator
	                    */
						if($vg_father["navigation"])
						{
							$nav["hidden"] = (!$vg_father["sort"] && $vg_father["search"]["hidden"]
	                                            ? $vg_father["search"]["hidden"]
	                                            : ""
	                                        );
							if($vg_father["navigation"]["location"] && ($vg_father["navigation"]["tot_page"] > 1 || $vg_father["navigation"]["infinite"]))
							{
								$nav["hidden"] .= $vg_father["navigation"]["hidden"];
								$nav["page"] = $vg_father["navigation"]["obj"]->process(false);
								if($vg_father["navigation"]["location"] == "top" || $vg_father["navigation"]["location"] == "both")
									$nav["top"] = $nav["page"];
								if($vg_father["navigation"]["location"] == "bottom" || $vg_father["navigation"]["location"] == "both")
									$nav["bottom"] = $nav["page"];
							}
							if($vg_father["navigation"]["alphanum"] && $vg_father["navigation"]["alphanum"]["location"])
							{
								$nav["alphanum"] = $vg_father["navigation"]["alphanum"]["obj"]->rpparse("main", false);
								if($vg_father["navigation"]["alphanum"]["location"] == "top" || $vg_father["navigation"]["alphanum"]["location"] == "both")
									$nav["top"] = $nav["alphanum"] . $nav["top"];
								if($vg_father["navigation"]["alphanum"]["location"] == "bottom" || $vg_father["navigation"]["alphanum"]["location"] == "both")
									$nav["bottom"] = $nav["bottom"] . $nav["alphanum"];
							}
						}
						if($nav["top"])
						{
							$tpl_data["obj"]->set_var("PageNavigator", $nav["hidden"] . $nav["top"]);
							$tpl_data["obj"]->parse("SezPageNavigatorTop", false);
							$nav["hidden"] = "";
						}
						if($nav["bottom"])
						{
						
							$tpl_data["obj"]->set_var("PageNavigator", $nav["hidden"] . $nav["bottom"]);
							$tpl_data["obj"]->parse("SezPageNavigatorBottom", false);
						}

	                    /**
	                    * Parse VGallery
	                    */
	                    $tpl_data["obj"]->parse("SezVGallerys", false); 
	                }
	            } else {
		            if($count_field_per_row_empty)
		                $strError = ffTemplate::_get_word_by_code("error_thumb_no_field_set");
		            else
		                $strError = ffTemplate::_get_word_by_code("error_thumb_nofilematch");
		        }            

			    if ($tpl_data["is_html"] && strlen($strError) && $enable_error) {
	                if($vg_father["is_custom_template"]) {
	                    $tpl_data["obj"]->set_var("error", $strError);
	                } else {
	                	$tpl_data["obj"]->set_var("error_class", cm_getClassByFrameworkCss("info", "callout", "error"));
			            $tpl_data["obj"]->set_var("strError", $strError);
			            $tpl_data["obj"]->parse("SezError", false);
	                }
			    }
		        
		        if($params["output"])
		            $tpl_data["obj"]->minify = "strong_strip"; 
		        
		        $buffer = $tpl_data["obj"]->rpparse("main", false);
	        }
	    }
	 
 	 	if (!$count_files  && check_function("set_template_var") && check_function("process_html_page_error")) {
            if($vg_father_params["display_error"] && $type != "learnmore" && !$vg_father["is_custom_template"]) {
                if(is_array($vg_father["template"]["wrap"]) && !is_bool($vg_father["template"]["wrap"]["container"]) && strlen($vg_father["template"]["wrap"]["container"])) {
                    $wrap_container["prefix"] = '<div class="vg-wrap ' . $vg_father["template"]["wrap"]["container"] . '">';
                    $wrap_container["postfix"] = '</div>';             
                } else {
                    $wrap_container["prefix"] = '<div class="vg-wrap">';
                    $wrap_container["postfix"] = '</div>';             
    //                $block["class"]["error"] = cm_getClassByFrameworkCss("info", "callout");
                }
                $block = get_template_header($user_path, $admin_menu, $layout, $tpl, $block);
                $buffer =  $wrap_container["prefix"] . process_html_page_error() . $wrap_container["postfix"]; 
                if($vg_father_params["navigation"] || $vg_father_params["search"]) 
                    http_response_code($cm->isXHR() ? 204 : 404); 
            } else {
                return;
            }
        } else {
			if($vg_father["seo"]["mode"] == "thumb")
        		$globals->navigation["tot_page"] = $vg_father["navigation"]["tot_page"];
//print_r($vg_father["search"]);
        	if($vg_father["search"]["encoded_params"]) {
        		$globals->user_path_params = "?" . $vg_father["search"]["encoded_params"];
			}

        	if($father_settings["AREA_VGALLERY_THUMB_SHOW_FILTER_AZ"] || $father_settings["AREA_VGALLERY_THUMB_FULLCLICK"])
        		$cm->oPage->tplAddJs("ff.cms.vgallery");

			//Set JS Plugin
		    setJsRequest($arrJsRequest);

	        /**
	        * Process Block Header
	        */            
	        $block["class"]["lvl"] = "lvl" . (substr_count($vg_father["settings_path"], "/"));
	        $block["class"]["vgallery"] = $vg_father["vgallery_class"];

			if($father_settings["AREA_VGALLERY_THUMB_EQUALIZER"])
				$block["properties"]["equalizer"] = cm_getClassByFrameworkCss("equalizer-row", "util");
	        
	        if($vg_father["is_custom_template"])
        		$block["class"]["template"] = ffCommon_url_rewrite($vg_father["template"]["custom_name"]);

	        if(check_function("set_template_var"))
	            $block = get_template_header($user_path, $admin_menu, $layout , $tpl, $block);
		}    
	}
	
	//SEO SETTINGS
    if (is_array($vg_father["seo"]) && count($vg_father["seo"]) && !isset($globals->seo[$vg_father["seo"]["mode"]])) {
    	$globals->seo[$vg_father["seo"]["mode"]] = $vg_father["seo"];
    }

    if($params["output"]) {
    	$res = array("nodes" => $arrKeyNode
    					, "pre" => $block["tpl"]["pre"]
    					, "post" => $block["tpl"]["post"]
    					, "content" => $buffer
                        , "default" => $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
                        , "params" => $vg_father["request_params"]
                        , "js_request" => array_keys($arrJsRequest)
                    );

        if($params["output"] === true)
        	return $res;
        else
        	return $res[$params["output"]]; 
    } else {
	    return array(
			"pre" 			=> $block["tpl"]["pre"]
			, "post" 		=> $block["tpl"]["post"]
			, "content" 	=> $buffer
			, "default" 	=> $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
		);
    }
}