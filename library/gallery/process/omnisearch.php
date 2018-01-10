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
function process_omnisearch($user_path, &$layout) 
{
	$cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");
    $menu_categories = array();

    //$settings_path = $globals->settings_path;

    $unic_id = $layout["prefix"] . $layout["ID"];
    $layout_settings = $layout["settings"];

    $cm->oPage->tplAddJs("ff.cms.search");

    $template_name = ($layout["template"]
    					? $layout["template"]
    					: "default"
    				) . ".html";
    
    //$tpl_data["custom"] = "omnisearch.html";
	$tpl_data["custom"] = $layout["smart_url"] . ".html";		    
    $tpl_data["base"] = $template_name;
    $tpl_data["path"] = $layout["tpl_path"];
    
    $tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
    
    $tpl = ffTemplate::factory($tpl_data["result"]["path"]);
	$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");        

    $tpl->set_var("real_father", $unic_id);
    
    /**
    * Admin Father Bar
    */
    if(AREA_SEARCH_SHOW_MODIFY) {
        $admin_menu["admin"]["unic_name"] = $unic_id;
        $admin_menu["admin"]["title"] = $layout["title"];
        $admin_menu["admin"]["class"] = $layout["type_class"];
        $admin_menu["admin"]["group"] = $layout["type_group"];
        $admin_menu["admin"]["modify"] = "";
        $admin_menu["admin"]["delete"] = "";
        if(AREA_PROPERTIES_SHOW_MODIFY) {
            $admin_menu["admin"]["extra"] = "";
        }
        if(AREA_ECOMMERCE_SHOW_MODIFY) {
            $admin_menu["admin"]["ecommerce"] = "";
        }
        if(AREA_LAYOUT_SHOW_MODIFY) {
            $admin_menu["admin"]["layout"]["ID"] = $layout["ID"];
            $admin_menu["admin"]["layout"]["type"] = $layout["type"];
        }
        if(AREA_SETTINGS_SHOW_MODIFY) {
            $admin_menu["admin"]["setting"] = ""; //$layout["type"]; 
        }


        $admin_menu["sys"]["path"] = $user_path;
        $admin_menu["sys"]["type"] = "admin_toolbar";
       // $admin_menu["sys"]["ret_url"] = $ret_url;
    }

	/**
	* Process Block Header
	*/	
	if($tpl_data["result"]["type"] != "custom") 
		$block["exclude"]["class"]["filename"] = true;
	else 
		$block["exclude"]["class"]["default"] = true;

    if(check_function("set_template_var")) 
		$block = get_template_header($user_path, $admin_menu, $layout, $tpl, $block);

    if ($layout_settings["AREA_SEARCH_SHOW_TITLE"]) 
        $tpl->parse("SezTitle", false);

	$tmp_data_field["search"]["id"] = $unic_id . "-term";
	$tmp_data_field["search"]["action"] = "ff.cms.search.term(this, event);";  
	$tmp_data_field["search.button"]["action"] = "ff.cms.search.term(this, event);";  
	
	//DAL MENU TOGLIERE LE SEZIONI RIDONDANTI CURRENT
//ELIMINARE LE SETTINGS LAYOUT INUTILI E AGGIUNGERE LE NUOVE
	//da creare realmente un menu magari basato su un templating custom anche o un menu tags anche
	//aggiungere i gruppi
	/*$menu_categories["ciao"] = array(
		"name" => "Ciao"
		, "permalink" => "/ciao"
	);*/
        
	$oSearchField = ffField::factory($cm->oPage);
	$oSearchField->id = $tmp_data_field["search"]["id"];
	if($layout_settings["AREA_SEARCH_SHOW_LABEL"]) 
		$oSearchField->label = ffTemplate::_get_word_by_code("search_term_label");
	else
		$oSearchField->placeholder = ffTemplate::_get_word_by_code("search_term_placeholder");
	
	$oSearchField->class = "omnisearch-term";

	if($layout_settings["AREA_SEARCH_MENU_CATEGORIES"]) {
		$oSearchField->autocomplete_icon["ellipsis-v"] = array("class" => "actex-menu", "rel" => $unic_id . "-menu");
	} 
	
	if($layout_settings["AREA_SEARCH_MENU_COMBO"]) {
		$oSearchField->autocomplete_icon["caret-down"] = "actex-combo";
		$oSearchField->autocomplete_combo = true;
	}

	if($layout_settings["AREA_SEARCH_MENU_OFFCANVAS"] && check_function("get_grid_system_params")) {
		$grid_params = get_grid_system_params("menu-side-offcanvas");	
		$oSearchField->autocomplete_icon["bars"] = $grid_params["params"]["class_menu_toggle"];
	}
	if($layout_settings["AREA_SEARCH_MENU_BUTTON"]) {
		$oSearchField->autocomplete_icon["search"] = "actex-search";
	}
	if($layout_settings["AREA_SEARCH_AUTOCOMPLETE"]) {
		$oSearchField->widget = "autocomplete"; 
		$oSearchField->actex_service = FF_SITE_PATH . "/search";
		$oSearchField->autocomplete_minLength = 3;
		//$oSearchField->autocomplete_icon = "bars";
		//$oSearchField->autocomplete_compare = "Name";
		$oSearchField->autocomplete_readonly = false;
	}
	$oSearchField->use_own_location = true;
	$oSearchField->parent_page = array(&$cm->oPage);
	$oSearchField->properties["onkeydown"] = $tmp_data_field["search"]["action"];

	if($tpl_data["result"]["type"] == "custom")
    {
    	if($tpl->isset_var("search") || $tpl->isset_var("search:id")) {
    		$tmp_data_field["search"]["default"] = $oSearchField->process();  
		}
    		
		if(is_array($tmp_data_field) && count($tmp_data_field)) {
            foreach($tmp_data_field AS $tmp_data_field_key => $tmp_data_field_value) {
				foreach($tmp_data_field_value AS $tmp_data_field_prop_key => $tmp_data_field_prop_value) {
					$field_attr = strtolower($tmp_data_field_prop_key == "default"
	                    ? $tmp_data_field_key
	                    : $tmp_data_field_key . ":" . $tmp_data_field_prop_key
	                );
	                if($tpl->isset_var($field_attr)) {
	                    $tpl->set_var($field_attr, $tmp_data_field_prop_value);
	                } 					
				}
                                               
            }
        }        
    } else {
    	//$cm->oPage->addContent($oSearchField);
		$tpl->set_var("search_term", $oSearchField->process());
 
 		if($layout_settings["AREA_SEARCH_CATEGORIES"] && is_array($menu_categories) && count($menu_categories)) {
 			if($layout_settings["AREA_SEARCH_MENU_CATEGORIES"])
 				$tpl->set_var("class_categories", " hidden");
 			
 			foreach($menu_categories AS $cat_key => $cat_params) {
 				if($cat_params["class"]) 
 					$tpl->set_var("class_elem", $cat_params["class"]); // da mettere il current
 				else
 					$tpl->set_var("class_elem", "");

 				$tpl->set_var("search_menu_name", $cat_params["name"]);
				$tpl->set_var("search_menu_permalink", $cat_params["permalink"]);
 				$tpl->parse("SezCategoriesItem", true);
 			}

 			$tpl->parse("SezCategories", false);
 		}
 
 
    }

	$buffer = $tpl->rpparse("main", false);
    return array(
		"pre" 			=> $block["tpl"]["pre"]
		, "post" 		=> $block["tpl"]["post"]
		, "content" 	=> $buffer
		, "default" 	=> $block["tpl"]["pre"] . $buffer . $block["tpl"]["post"]
	);	
}
