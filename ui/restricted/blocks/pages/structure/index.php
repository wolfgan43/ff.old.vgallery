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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

 if (!(AREA_SECTION_SHOW_MODIFY || AREA_LAYER_SHOW_MODIFY )) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

$cm->oPage->title = ffTemplate::_get_word_by_code("structure");
$cm->oPage->addContent(null, true, "rel"); 

if (AREA_SECTION_SHOW_MODIFY) {
    $framework_css = cm_getFrameworkCss();
    $framework_css_name = $framework_css["name"];
    
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->id = "Section";
    //$oGrid->title = ffTemplate::_get_word_by_code("section_title");

    $oGrid->source_SQL = "SELECT layout_location.* 
                            , layout_layer.`order` AS layer_order
                            , layout_location_path.default_grid AS `default_grid`
                            , layout_location_path.grid_md AS `grid_md`
                            , layout_location_path.grid_sm AS `grid_sm`
                            , layout_location_path.grid_xs AS `grid_xs`
                            , layout_location_path.class AS `class`
                        FROM layout_location
                            LEFT JOIN layout_layer ON layout_layer.ID = layout_location.ID_layer
                            LEFT JOIN layout_location_path ON layout_location_path.ID_layout_location = layout_location.ID AND layout_location_path.path = '%'
                        WHERE 1
                        [AND] [WHERE] 
                        GROUP BY layout_location.ID
                        [ORDER]";	
    $oGrid->order_default = "ID";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
	$oGrid->addit_record_param = "framework=" . $framework_css_name . "&";
	$oGrid->addit_insert_record_param = "framework=" . $framework_css_name . "&";
    $oGrid->record_id = "SectionModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->resources[] = "cmLayoutModify";
    $oGrid->use_search = false;
    $oGrid->use_order = false;
    $oGrid->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid
            , array(
                "resource_id" => "layout_location"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
            , "ID"
        )
    );
    $oGrid->buttons_options["export"]["display"] = false;
    $oGrid->addEvent("on_before_parse_row", "Section_on_before_parse_row");
    // Campi chiave

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oField->order_SQL = " layer_order, interface_level";
    $oGrid->addKeyField($oField);

    // Campi di ricerca

    // Campi visualizzati
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("section_name");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_layer";
    $oField->label = ffTemplate::_get_word_by_code("section_layer");
    $oField->base_type = "Number";
    $oField->extended_type = "Selection";
    $oField->source_SQL = "SELECT ID, name FROM layout_layer";
    $oGrid->addContent($oField);
	
	if($framework_css_name) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "default_grid";
		$oField->container_class = "default";
		$oField->label = ffTemplate::_get_word_by_code("section_" . $framework_css_name . "_default_grid");
		$oField->base_type = "Number";
		$oField->min_val = "0";
		$oField->max_val = "12";
		$oField->step = "1";
		$oField->fixed_post_content = "/12";
		$oGrid->addContent($oField);
		
		if($framework_css_name == "bootstrap" || $framework_css_name == "foundation") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "grid_md";
			$oField->label = ffTemplate::_get_word_by_code("section_" . $framework_css_name . "_grid_md");
			$oField->base_type = "Number";
			$oField->min_val = "0";
			$oField->max_val = "12";
			$oField->step = "1";
			$oField->fixed_post_content = "/12";
			$oGrid->addContent($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "grid_sm";
			$oField->label = ffTemplate::_get_word_by_code("section_" . $framework_css_name . "_grid_sm");
			$oField->base_type = "Number";
			$oField->min_val = "0";
			$oField->max_val = "12";
			$oField->step = "1";
			$oField->fixed_post_content = "/12";
			$oGrid->addContent($oField);
		
			if($framework_css_name == "bootstrap")
			{
				$oField = ffField::factory($cm->oPage);
				$oField->id = "grid_xs";
				$oField->label = ffTemplate::_get_word_by_code("section_" . $framework_css_name . "_grid_xs");
				$oField->base_type = "Number";
				$oField->min_val = "0";
				$oField->max_val = "12";
				$oField->step = "1";
				$oField->fixed_post_content = "/12";
				$oGrid->addContent($oField);
			}
		}

		$oField = ffField::factory($cm->oPage);
		$oField->id = "class";
		$oField->container_class = "default class";
		$oField->label = ffTemplate::_get_word_by_code("section_default_class");
		$oGrid->addContent($oField);			
	} else {
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "width";
	    $oField->label = ffTemplate::_get_word_by_code("section_width");
	    $oGrid->addContent($oField);
	}
	
    $cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("section_title"))); 
}

if(AREA_LAYER_SHOW_MODIFY) {
    $oGrid_layer = ffGrid::factory($cm->oPage);
    $oGrid_layer->full_ajax = true;
    $oGrid_layer->id = "Layer";
    $oGrid_layer->title = ffTemplate::_get_word_by_code("layer_title");
	$oGrid_layer->source_SQL = "SELECT layout_layer.* 
								FROM layout_layer 
								WHERE 1
								[AND] [WHERE] [HAVING] [ORDER]";
    $oGrid_layer->order_default = "ID";
    $oGrid_layer->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/layer/modify";
    $oGrid_layer->record_id = "LayerModify";
    $oGrid_layer->resources[] = $oGrid_layer->record_id;
    $oGrid_layer->use_search = false;
    $oGrid_layer->use_order = false;
    $oGrid_layer->widget_deps[] = array(
        "name" => "dragsort"
        , "options" => array(
              &$oGrid_layer
            , array(
                "resource_id" => "layout_layer"
                , "service_path" => get_path_by_rule("services", "restricted") . "/sort"
            )
            , "ID"
        )
    );
    $oGrid_layer->buttons_options["export"]["display"] = false;
    
    // Campi chiave
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oField->order_SQL = " `order`";
    $oGrid_layer->addKeyField($oField);

    // Campi di ricerca
     
    // Campi visualizzati
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("section_name");
    $oGrid_layer->addContent($oField);

	if(!$framework_css_name) {
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "width";
	    $oField->label = ffTemplate::_get_word_by_code("layer_width");
	    $oGrid_layer->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "show_empty";
		$oField->label = ffTemplate::_get_word_by_code("layer_show_empty");
		$oField->base_type = "Number";
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array (
		                            array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no"))),
		                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes"))),
		                       );   
		$oField->multi_select_one = false;
		$oGrid_layer->addContent($oField);
	}

    $cm->oPage->addContent($oGrid_layer, "rel", null, array("title" => ffTemplate::_get_word_by_code("layer_title")));
}

function Section_on_before_parse_row($component) {
	if($component->db[0]->record["default_grid"] +
		$component->db[0]->record["grid_md"] +
		$component->db[0]->record["grid_sm"] +
		$component->db[0]->record["grid_xs"] == 0
	)
	{
		if(isset($component->grid_fields["default_grid"]))
			$component->grid_fields["default_grid"]->setValue(12);
		if(isset($component->grid_fields["grid_md"]))
			$component->grid_fields["grid_md"]->setValue(12);
		if(isset($component->grid_fields["grid_sm"]))
			$component->grid_fields["grid_sm"]->setValue(12);
		if(isset($component->grid_fields["grid_xs"]))
			$component->grid_fields["grid_xs"]->setValue(12);
	}
    if(strtolower($component->grid_fields["name"]->getValue()) == "content") {
        $component->visible_delete_bt = false; 
    } else {
        $component->visible_delete_bt = true; 
    }    
}
