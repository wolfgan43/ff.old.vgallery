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
function process_vgallery_view($user_path, $vgallery_name, $params = null, &$layout) 
{
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;
    $tpl = null;
    $block = array();
    $arrJsRequest = array();
    
    $layout["unic_id"] = $layout["prefix"] . $layout["ID"];
    if($layout["unic_id"])
        $layout["unic_id"] .= "V";

    $layout_settings = $layout["settings"];
    check_function("vgallery_init");

     /**
    *  Define Params for Node Base
    */

    $vg_father_params = array(
         "ID_layout" => $layout["ID"]
        , "unic_id" => $layout["unic_id"]
        , "unic_id_lower" => strtolower($layout["unic_id"])
        , "vgallery_name" => $vgallery_name
        , "user_path" => $user_path
        , "source_user_path" => $params["source_user_path"]
        , "group" => $params["group"]
        , "enable_user_sort" => false
        , "sort_default" => null
        , "sort_method" => null
        , "enable_title" => $layout_settings["AREA_VGALLERY_PREVIEW_SHOW_TITLE"]
        , "enable_title_seo" => true
        , "allow_insert" => false
        , "framework_css" => $params["framework_css"]
        , "template_name" => $params["template_name"]
        , "tpl_path" => $layout["tpl_path"]
        , "template_skip_hide" => $params["template_skip_hide"]
        , "ref" => $params["ref"]
        , "ID_cart_detail" => ($params["ID_cart_detail"] === null && isset($_REQUEST["detail"]) && $_REQUEST["detail"] > 0
            ? $_REQUEST["detail"]
            : $params["ID_cart_detail"]
        )
        , "display_error" => !$params["skip_error"]
    );    
    
    if($vgallery_name == "anagraph")
    {
        $vg_father_params["name"] = "anagraph";
        $vg_father_params["settings_path"] = $params["source_user_path"]; //forse era /
        $vg_father_params["settings_type"] = "anagraph";
        $vg_father_params["settings_prefix"] = "A";
        $vg_father_params["is_dir"] = false;    
        $vg_father_params["allow_insert"] = false; 

    }
    else 
    {
        $vg_father_params["name"] = "vgallery";
        $vg_father_params["settings_path"] = ffCommon_dirname($user_path);
        $vg_father_params["settings_type"] = "vgallery_nodes";
        $vg_father_params["settings_prefix"] = "V";
    } 

    if($params["search"])
    {
        $vg_father_params["search"] = $params["search"];
        $vg_father_params["template_skip_hide"] = true;
    } 
        
    if(is_array($params["limit"]) && count($params["limit"]))
        $vg_father_params["limit"] = $params["limit"];        

	if(!$vg_father_params["settings_path"])
		$vg_father_params["settings_path"] = $layout["db"]["real_path"];
        
    $vg_father = process_vgallery_father($vg_father_params, "detail");

    if($vg_father_params["ref"] !== null || $vg_father_params["ID_cart_detail"] !== null) {
        use_cache(false);
    }
    //$vgallery_name = ffCommon_url_rewrite($vgallery_name);
    
    if(!$vg_father || !$vg_father["available"] || !$vg_father["permission"]["visible"])
        return false;

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

    unset($layout["class"]["type"]); //elimina la classe virtual gallery
    
    /**
    * Admin Father Bar
    */
    $admin_menu = process_vgallery_admin_bar($vg_father, $layout);
    if(is_array($admin_menu))
        $enable_error = true;
    else
        $enable_error = false;

    /**
    * Process Block Header
    */            
    if($vg_father["hide_template"] && check_function("set_template_var") && check_function("process_html_page_error")) {
        $block = get_template_header($vg_father["user_path"], $admin_menu, $layout);
        
        $buffer = ($enable_error 
                    ? process_html_page_error(ffTemplate::_get_word_by_code("vgallery_is_hidden_by_properties"))
                    : ""
                );
        
        $buffer_isset = true;
    }

    if(!$buffer_isset) 
    {
        /**
        * Load Template
        */   
        $tpl_data = array( 
            "custom" => $vg_father["template"]["custom_name"] . $vg_father["template"]["suffix"] . ".html"
            , "base" => strtolower($vg_father["template"]["name"]) . $vg_father["template"]["suffix"] . ".html"
            , "is_html" => true
        );
        $tpl_data["result"] = get_template_cascading($vg_father["user_path"], $tpl_data, $vg_father["template"]["path"]);

        $tpl_data["obj"] = ffTemplate::factory($tpl_data["result"]["path"]);
        $tpl_data["obj"]->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   	

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
        $vg_field = process_vgallery_type($vg_father, $tpl_data, $layout);

         /**
        * Load Record
        */
        $vg = process_vgallery_node($vg_father, $layout_settings, $vg_field);
        if (is_array($vg)) 
        {
            $count_files = 0;
            
            $switch_style = false;
            $col = 0;

            if(count($vg["data"])) 
            {
                if(is_array($vg_field["relationship"]) && count($vg_field["relationship"]))
                    $vg_rel = process_vgallery_node_relationship($vg_father, $vg["key"], $vg_field["relationship"]);
                    //print_r($vg_rel);
                    
                /**
                * Load Basic Data for Each Node
                */
                process_vgallery_node_data($vg_father, $vg_field, $vg, $vg_rel);

                //Load JS Plugin
               // $arrJsRequest["vgallery"] = true;
			    if($vg_father["properties"]["plugin"]["name"] && $vg_father["properties"]["image"]["fields"]) {
			        $arrJsRequest[$vg_father["properties"]["plugin"]["name"]] = true;
			    }

                /**
                * Process Block Header
                */            
                if(is_array($params["group"]) && strlen($params["group"]["name"])) {
                    $block["class"]["group"] = ffCommon_url_rewrite($params["group"]["name"]);
                }
                
                if($vg_father_params["ref"] === null && $vg_father_params["ID_cart_detail"] === null && !($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE) && USE_CART_PUBLIC_MONO && $vg_father["is_wishlisted"]) {
                    $block["class"]["wishlist"] = "wishlisted";
                }
                
                if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
                   $block["class"]["ajax"] = "ajax";
                } else {
                   $block["class"]["ajax"] = "noajax";
                }
				
				$block["class"]["vgallery"] = $vg_father["vgallery_class"];
                
                if($vg_father["is_custom_template"])
                    $block["class"]["template"] = ffCommon_url_rewrite($vg_father["template"]["custom_name"]);

                if($layout_settings["AREA_VGALLERY_PREVIEW_SHOW_TITLE"]) {
                    if(strlen($layout_settings["AREA_VGALLERY_PREVIEW_TITLE_HTMLTAG"]))
                        $htmltag_title = $layout_settings["AREA_VGALLERY_PREVIEW_TITLE_HTMLTAG"];
                    else
                        $htmltag_title = "h1";

                    $tpl_data["obj"]->set_var("name_title", '<' . $htmltag_title . '>' . $vg_father["title"] . '</' . $htmltag_title . '>');
                   // $tpl_data["obj"]->parse("SezVGalleryTitle", false);
                //} else {
                   // $tpl_data["obj"]->set_var("SezVGalleryTitle", "");
                }
                
                /**
                * Process Rows
                */
                $count = array();
                foreach($vg["data"] AS $vg_data_key => $vg_data_value) 
                {    
                    //old
                    //$globals->cache["data_blocks"]["VV" . $vg_father["ID_vgallery"] . "-" . $vg_father["ID_node"] . "-" . $params["group"]["ID"]] = $vg_father["ID_node"];
                    set_cache_data("V", $vg_data_value["ID"] . ($vg_father["group"]["ID"] ? "-" . $vg_father["group"]["ID"] : ""),  $vg_father["settings_prefix"] . $vg_father["ID_vgallery"]);
                    //$globals->cache["data_blocks"]["V" . $vg_father["settings_prefix"] . "-" . $vg_data_value["ID"] . ($vg_father["group"]["ID"] ? "-" . $vg_father["group"]["ID"] : "")] = $vg_data_value["ID"];

                    $params_fields = pre_process_vgallery_tpl_fields($tpl_data["obj"], $vg_father, $vg_field, $vg_data_value, $layout_settings);
                    $params_fields["tpl"] = $tpl_data;
                    $params_fields["enable_sort"] = false;
                    $params_fields["enable_error"] = $enable_error;
                    

                    /**
                    * Process Field
                    */                
                    if(is_array($vg_field["fields"][$vg_data_value["ID_type"]]) && count($vg_field["fields"][$vg_data_value["ID_type"]])) 
                    {
                        foreach($vg_field["fields"][$vg_data_value["ID_type"]] AS $parent_group_key => $parent_group_value) {
                            
                            if($layout_settings["AREA_VGALLERY_SHOW_GROUP"]) {
                                $tpl_data["obj"]->set_var("class_name", preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["vgallery_type"] . $parent_group_key) . "_field");
                                $tpl_data["obj"]->set_var("show_file", "#" . preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["unic_id"] . "G" . $parent_group_key));
                                $tpl_data["obj"]->set_var("name",  preg_replace('/[^a-zA-Z0-9]/', '', ffTemplate::_get_word_by_code($vg_father["vgallery_type"] . $parent_group_key)));
                                $tpl_data["obj"]->parse("SezField", true);
                            }
                            
                            $parsed_field = process_vgallery_tpl_fields($tpl_data["obj"]
                                                                        , $vg_father
                                                                        , $vg_field["fields"][$vg_data_value["ID_type"]][$parent_group_key]
                                                                        , $vg_data_value
                                                                        , $layout
                                                                        , $params_fields
                                                                    );
                            $count["field"] = $count["field"] + $parsed_field["count"];
                            $count["total_desc"] = $count["total_desc"] + $parsed_field["count_desc"];
                            $count["total_img"] = $count["total_img"] + $parsed_field["count_img"];

                            if($parsed_field["count_desc"]) 
                            {
                                if($count["field"] && is_array($parsed_field["js_request"]) && count($parsed_field["js_request"]))
                                    $arrJsRequest = array_replace($arrJsRequest, $parsed_field["js_request"]);

                                if(count($vg_field["fields"][$vg_data_value["ID_type"]]) > 1) {
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
                        }
                    }
                    

                    if(!$count["field"]) {
                        $count_field_per_row_empty++;
                        continue;
                    } else {
                        $count_files++;
                        $col++;                
                    }
                    /*
                    if ($vg_father["enable_date"]) 
                    {
                        $file_time = new ffData($vg_data_value["last_update"], "Timestamp", FF_SYSTEM_LOCALE);
                        $file_time = $file_time->getValue("Date", LANGUAGE_INSET);
                        $tpl_data["obj"]->set_var("last_update", $file_time ? ffCommon_specialchars($file_time) : ffTemplate::_get_word_by_code("date_format_unknow"));

                        $count_field++;
                        
                        $tpl_data["obj"]->parse("SezVGalleryDate", false);
                    } else {
                        $tpl_data["obj"]->set_var("SezVGalleryDate", "");
                    }  */

                    /**
                    * Class and ID for each Items
                    */
                    if(is_array($params_fields["htmltag"]["class"]))
                        $block["class"] = array_replace($block["class"], $params_fields["htmltag"]["class"]);

                    $block["properties"] = $params_fields["htmltag"]["properties"];
                    
    /*                $tpl_data["obj"]->set_var("real_name", preg_replace('/[^a-zA-Z0-9]/', '', $vg_father["unic_id"] . $vg_data_value["name"]));

                    $item_class = $params_fields["htmltag"]["class"];
                    $item_properties = $params_fields["htmltag"]["properties"];

                    if($vg_father["template"]["framework"]) 
                    {
                        $item_class = array_replace($item_class, $vg_father["template"]["class"]);
                        $item_class["lvl"] = "vgc" . $col;
                        
                        if($vg_father["template"]["wrap"]["row"] !== false) {
                            $item_class["switch"] = ($switch_style 
                                                        ? "odd"
                                                        : "even"
                                                    );

                            $switch_style = !$switch_style;
                        }
                    } else {
                        $item_class["lvl"] = "vgallery_col" . $col;
                    }
                    
                    $item_class["type"] = preg_replace('/[^a-zA-Z0-9]/', '', $vg_data_value["type"]);
                    
                    if($vg_data_value["highlight"])
                        $item_class["highlight"] = "highlight";
                    
                    if($vg_father["wishlist"] === null && !($vg_father["enable_ecommerce"] && AREA_SHOW_ECOMMERCE) && USE_CART_PUBLIC_MONO && $vg_data_value["is_wishlisted"])
                        $item_class["wishlist"] = "wishlisted";
                    
                    if($count_files == count($vg["data"]))
                        $item_class["row_end"] = "end";*/

                        
                        
                    //$str_item_class = implode(" ", array_filter($item_class));
                   // $tpl_data["obj"]->set_var("col_class", $str_item_class);

                    if($tpl_data["is_html"] && $tpl_data["result"]["type"] != "custom") {
                        if($count["total_img"]) {
                            if($count["total_desc"]) {
                                 if($vg_father["template"]["field"]["desc"])
                                    $tpl_data["obj"]->set_var("desc_class", " " . $vg_father["template"]["field"]["desc"]);
                                    
                                $tpl_data["obj"]->parse("SezVGalleryDescriptionStart", false);
                                $tpl_data["obj"]->parse("SezVGalleryDescriptionEnd", false);
                            }
                            
                            if($vg_father["template"]["field"]["img"])
                                $tpl_data["obj"]->set_var("img_class", " " . $vg_father["template"]["field"]["img"]);

                            $tpl_data["obj"]->parse("SezVGalleryImage" . $vg_father["template"]["field"]["location"], false);
                            $tpl_data["obj"]->set_var("SezVGalleryImage" . $vg_father["template"]["field"]["location"] . "Node", "");
                        }
                    }                
                }

                if(check_function("set_template_var"))
                    $block = get_template_header($vg_father["user_path"], $admin_menu, $layout, $tpl, $block);

                if ($count_files) 
                {
                    if($vg_father["is_custom_template"]) {
						parse_vgallery_tpl_custom_vars($tpl_data["obj"], $params_fields);
                    } else {            
                        $tpl_data["obj"]->parse("SezPreview", false);
                    }
                } 
                else 
                {
                    if($count_field_per_row_empty)
                        $strError = ffTemplate::_get_word_by_code("error_detail_no_field_set");
                    else
                        $strError = ffTemplate::_get_word_by_code("error_detail_nofilematch");

                }

                if (strlen($strError) && $enable_error) 
                {
                    if($vg_father["is_custom_template"]) {
                        $tpl_data["obj"]->set_var("error", $strError);
                    } else {
                    	$tpl_data["obj"]->set_var("error_class", cm_getClassByFrameworkCss("danger", "callout", "error"));
                        $tpl_data["obj"]->set_var("strError", $strError);
                        $tpl_data["obj"]->parse("SezError", false);
                    }
                } 
                else 
                {
                    if(is_array($vg_father["warning"]) && count($vg_father["warning"])) {
                        if($vg_father["is_custom_template"]) {
                            $tpl_data["obj"]->set_var("error", implode("<br />", array_filter($vg_father["warning"])));
                        } else {
                        	$tpl_data["obj"]->set_var("error_class", cm_getClassByFrameworkCss("warning", "callout", "warning"));
                            $tpl_data["obj"]->set_var("strError", implode("<br />", array_filter($vg_father["warning"])));
                            $tpl_data["obj"]->parse("SezError", false);
                        }
                    }

                    if($layout_settings["AREA_VGALLERY_PREVIEW_SHOW_HORIZNAV"]) 
                    {
                        $db = ffDB_Sql::factory();
                        if(OLD_VGALLERY) {
                            $sSQL_find_next_prev_select = " , 
                                        (
                                            SELECT IF(vgallery_rel_nodes_fields.description_text = ''
                                                    , vgallery_rel_nodes_fields.description
                                                    , vgallery_rel_nodes_fields.description_text
                                                ) AS description
                                            FROM vgallery_rel_nodes_fields
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                            WHERE vgallery_rel_nodes_fields.ID_nodes = tbl_src_internal.ID
                                                AND vgallery_fields.name = 'smart_url'
                                                AND vgallery_fields.ID_type = (SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.name = 'system')
                                                AND vgallery_rel_nodes_fields.uid = IF(vgallery_rel_nodes_fields.`nodes` = ''
                                                    , 0
                                                    , " . $db->toSql((get_session("UserID") == MOD_SEC_GUEST_USER_NAME ? "0" : get_session("UserNID")), "Number") . "
                                                )
                                                AND vgallery_rel_nodes_fields.ID_lang =  " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                                            LIMIT 1
                                        ) AS smart_url ";
                        } else {
							if(LANGUAGE_DEFAULT == LANGUAGE_INSET) {
	                            $sSQL_find_next_prev_select = " , 
	                                    (
	                                        SELECT vgallery_nodes.permalink
	                                        FROM vgallery_nodes
	                                         WHERE vgallery_nodes.ID = tbl_src_internal.ID
	                                    ) AS permalink";                
							} else {
	                            $sSQL_find_next_prev_select = " , 
	                                    (
	                                        SELECT vgallery_nodes_rel_languages.permalink
	                                        FROM vgallery_nodes_rel_languages
	                                         WHERE vgallery_nodes_rel_languages.ID_nodes = tbl_src_internal.ID
	                                            AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
	                                    ) AS permalink";                
							}                            
                        }                    
                        $sSQL_internal = "
                                        SELECT @row := @row +1 AS row, tbl_src_internal.ID
                                        [find_next_prev_select]
                                        FROM (
                                            SELECT vgallery_nodes.ID
                                            , (" . ($enable_user_sort
                                                ? "SELECT IF(vgallery_rel_nodes_fields.description_text = ''
                                                            , vgallery_rel_nodes_fields.description
                                                            , vgallery_rel_nodes_fields.description_text
                                                        ) AS description
                                                    FROM vgallery_rel_nodes_fields
                                                        INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                                    WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
                                                        AND vgallery_fields.name = " .  $db->toSql($sort, "Text") . "
                                                        AND vgallery_fields.ID_type = vgallery_nodes.ID_type
                                                        AND vgallery_rel_nodes_fields.uid = IF(vgallery_rel_nodes_fields.`nodes` = ''
                                                            , 0
                                                            , " . $db->toSql((get_session("UserID") == MOD_SEC_GUEST_USER_NAME ? "0" : get_session("UserNID")), "Number") . "
                                                        )
                                                        AND vgallery_rel_nodes_fields.ID_lang = IF(vgallery_fields.disable_multilang > 0, " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . ", " . $db->toSql(LANGUAGE_INSET_ID, "Number") . ")
                                                    LIMIT 1
                                                " 
                                                : "SELECT IF(vgallery_rel_nodes_fields.description_text = ''
                                                            , vgallery_rel_nodes_fields.description
                                                            , vgallery_rel_nodes_fields.description_text
                                                        ) AS description
                                                    FROM vgallery_rel_nodes_fields
                                                        INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields 
                                                    WHERE vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
                                                        AND vgallery_fields.ID = " .  $db->toSql($vg_father["sort_default"], "Number") . "
                                                        AND vgallery_fields.ID_type = vgallery_nodes.ID_type
                                                        AND vgallery_rel_nodes_fields.uid = IF(vgallery_rel_nodes_fields.`nodes` = ''
                                                            , 0
                                                            , " . $db->toSql((get_session("UserID") == MOD_SEC_GUEST_USER_NAME ? "0" : get_session("UserNID")), "Number") . "
                                                        )
                                                        AND vgallery_rel_nodes_fields.ID_lang = IF(vgallery_fields.disable_multilang > 0, " . $db->toSql(LANGUAGE_DEFAULT_ID, "Number") . ", " . $db->toSql(LANGUAGE_INSET_ID, "Number") . ")
                                                    LIMIT 1
                                                " 
                                            ) . ") AS actual_sort
                                            FROM vgallery_nodes
                                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
                                            WHERE 1
                                                AND vgallery_nodes.visible > 0
                                                AND vgallery_nodes.is_dir = 0
                                                AND vgallery_nodes.parent = " . $db->toSql(ffCommon_dirname($vg_father["user_path"])) . "
                                                AND vgallery.name = " . $db->toSql($vg_father["vgallery_name"]) . "
                                            ORDER BY " .
                                                ($layout_settings["AREA_VGALLERY_LIST_SHOW_GROUP"]
                                                    ? " vgallery_nodes.parent, "
                                                    : ""
                                                ) .
                                                ($enable_user_sort
                                                    ? "actual_sort " . $vg_father["sort_method"]
                                                    : "vgallery_nodes.`order`, " . ($layout_settings["AREA_VGALLERY_ORDER_LIST_BY_TITLE"] 
                                                                                        ? "actual_sort " . $vg_father["sort_method"]
                                                                                        : ($layout_settings["AREA_VGALLERY_ORDER_LIST_BY_LAST_UPDATE"]
                                                                                            ? "vgallery_nodes.last_update " . $vg_father["sort_method"]
                                                                                            : "vgallery_nodes.ID " . $vg_father["sort_method"]
                                                                                        )
                                                                                    )
                                                ) . "
                                        ) AS tbl_src_internal
                                            , (SELECT @row :=0) AS r
                                        ";
                        
                        $sSQL = "SELECT tbl_src.*
                                FROM (" . 
                                    str_replace("[find_next_prev_select]", "", $sSQL_internal)
                                . ") AS tbl_src
                                WHERE tbl_src.`ID` = " . $db->toSql($vg_father["ID_node"], "Number");
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $vgallery_position = $db->getField("row", "Number", true);
                            $vgallery_position_back = $vgallery_position - 2;
                            if($vgallery_position_back < 0)
                                $vgallery_position_back = 0;
                                
                            $vgallery_prev_url = "";
                            $vgallery_next_url = "";
                            
                            $sSQL = "SELECT tbl_src.*
                                FROM (" . 
                                    str_replace("[find_next_prev_select]", $sSQL_find_next_prev_select, $sSQL_internal)
                                . ") AS tbl_src
                                WHERE 1
                                LIMIT " . $vgallery_position_back . ", 3"; 
                            $db->query($sSQL);
                            if($db->nextRecord()) {
                                do{
                                    if(!strlen($vgallery_prev_url) && $db->getField("row", "Number", true) < $vgallery_position ) {
                                        $vgallery_prev_url = $db->getField("permalink", "Text", true);
                                    }
                                    if(!strlen($vgallery_next_url) && $db->getField("row", "Number", true) > $vgallery_position ) {
                                        $vgallery_next_url = $db->getField("permalink", "Text", true);
                                    }
                                } while($db->nextRecord());
                            }

                            if(strlen($vgallery_prev_url)) {
                                $tpl_data["obj"]->set_var("vgallery_detail_url_prev", $vgallery_prev_url);
                                $tpl_data["obj"]->set_var("vgallery_detail_prev", ffTemplate::_get_word_by_code("vgallery_" . $vg_father["vgallery_name"] . "_prev"));
                                $tpl_data["obj"]->parse("SezHorizNavPrev", false);
                            }

                            if(strlen($vgallery_next_url)) {            
                                $tpl_data["obj"]->set_var("vgallery_detail_url_next", $vgallery_next_url); 
                                $tpl_data["obj"]->set_var("vgallery_detail_next", ffTemplate::_get_word_by_code("vgallery_" . $vg_father["vgallery_name"] . "_next")); 
                                $tpl_data["obj"]->parse("SezHorizNavNext", false);               
                            }
                            $tpl_data["obj"]->parse("SezHorizNav", false);
                        }
                    }
                }
            }
        }
        if($count_files) {
        	if($vg_father["search"]["encoded_params"]) {
        		$globals->user_path_params = "?" . $vg_father["search"]["encoded_params"];
			}

            $buffer = $tpl_data["obj"]->rpparse("main", false);
		} else {
            return null;
		}
    }

    //setJsRequest("ff.cms.vgallery", "tools");
    
    //Set JS Plugin
    setJsRequest($arrJsRequest);    
        
	//SEO SETTINGS
    if (is_array($vg_father["seo"]) && count($vg_father["seo"]) && !isset($globals->seo[$vg_father["seo"]["mode"]])) {
    	$globals->seo[$vg_father["seo"]["mode"]] = $vg_father["seo"];
    }

    if($params["output"]) {
        $res = array(
        	"content" => $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
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
			, "content" 	=> $buffer
			, "post" 		=> $block["tpl"]["post"]
		);
	}
}