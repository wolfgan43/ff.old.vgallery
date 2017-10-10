<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(AREA_SECTION_SHOW_MODIFY || AREA_LAYER_SHOW_MODIFY )) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(isset($_REQUEST["export"]) && check_function("export")) { 
	export_layout_structure();
} else {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "cmLayout";
	$oGrid->title = "";
	$oGrid->source_SQL = "SELECT * 
						FROM " . CM_TABLE_PREFIX . "layout 
						WHERE 1
							AND path NOT LIKE '/admin%'
							AND path NOT LIKE '/restricted%'
							AND path NOT LIKE '/manage%'
							AND path NOT LIKE '/frame%'
						 [AND] [WHERE] [ORDER]";
	$oGrid->order_default = "ID";
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/cm_modify";
	$oGrid->record_id = "cmLayoutModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->resources[] = "SectionModify";
	$oGrid->use_search = false;
	$oGrid->use_paging = false;
	$oGrid->full_ajax = true;
	$oGrid->display_delete_bt = false;
	$oGrid->display_edit_bt = true;
	$oGrid->buttons_options["export"]["display"] = false;

	// Campo chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Campi visualizzazione
	$oField = ffField::factory($cm->oPage);
	$oField->id = "path";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_path");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "title";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_title");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "theme";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_theme");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "page";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_page");
	$oGrid->addContent($oField);


	$oField = ffField::factory($cm->oPage);
	$oField->id = "exclude_ff_js";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_exclude_ff");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array(
		array(new ffData("0"), new ffData("Includi"))
		, array(new ffData("1"), new ffData("Escludi"))
	);
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "exclude_form";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_exclude_form");
	$oField->extended_type = "Selection";
	$oField->multi_pairs = array(
		array(new ffData("0"), new ffData("Includi"))
		, array(new ffData("1"), new ffData("Escludi"))
	);
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "layer";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_layer");
	$oGrid->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "class_body";
	$oField->label = ffTemplate::_get_word_by_code("cm_layout_body_class");
	$oGrid->addContent($oField);

	$cm->oPage->addContent($oGrid);


	$cm->oPage->addContent(null, true, "rel"); 


	if (AREA_SECTION_SHOW_MODIFY) {
	    $sSQL = "SELECT cm_layout.* 
	            FROM cm_layout 
	            WHERE cm_layout.path = " . $db_gallery->toSql("/");
	    $db_gallery->query($sSQL);
	    if($db_gallery->nextRecord()) {
	        $framework_css = cm_getFrameworkCss($db_gallery->getField("framework_css", "Text", true));
	        $template_framework = $framework_css["name"];
	    }

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
		$oGrid->addit_record_param = "framework=" . $template_framework . "&";
		$oGrid->addit_insert_record_param = "framework=" . $template_framework . "&";
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
	                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
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
	/*
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "interface_level";
	    $oField->label = ffTemplate::_get_word_by_code("section_interface_level");
	    $oField->display = false;
	    $oField->order_SQL = " layer_order, interface_level";
	    $oGrid->addContent($oField);*/

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID_layer";
	    $oField->label = ffTemplate::_get_word_by_code("section_layer");
	    $oField->base_type = "Number";
	    $oField->extended_type = "Selection";
	    $oField->source_SQL = "SELECT ID, name FROM layout_layer";
	    $oGrid->addContent($oField);
		
		if($template_framework) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "default_grid";
			$oField->container_class = "default";
			$oField->label = ffTemplate::_get_word_by_code("section_" . $template_framework . "_default_grid");
			$oField->base_type = "Number";
			$oField->min_val = "0";
			$oField->max_val = "12";
			$oField->step = "1";
			$oField->fixed_post_content = "/12";
			$oGrid->addContent($oField);
			
			if($template_framework == "bootstrap" || $template_framework == "foundation") {
				$oField = ffField::factory($cm->oPage);
				$oField->id = "grid_md";
				$oField->label = ffTemplate::_get_word_by_code("section_" . $template_framework . "_grid_md");
				$oField->base_type = "Number";
				$oField->min_val = "0";
				$oField->max_val = "12";
				$oField->step = "1";
				$oField->fixed_post_content = "/12";
				$oGrid->addContent($oField);

				$oField = ffField::factory($cm->oPage);
				$oField->id = "grid_sm";
				$oField->label = ffTemplate::_get_word_by_code("section_" . $template_framework . "_grid_sm");
				$oField->base_type = "Number";
				$oField->min_val = "0";
				$oField->max_val = "12";
				$oField->step = "1";
				$oField->fixed_post_content = "/12";
				$oGrid->addContent($oField);
			
				if($template_framework == "bootstrap")
				{
					$oField = ffField::factory($cm->oPage);
					$oField->id = "grid_xs";
					$oField->label = ffTemplate::_get_word_by_code("section_" . $template_framework . "_grid_xs");
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
		
 		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "import_struct"; 
	    $oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("import_structure");
		$oButton->action_type = "gotourl"; 
		$oButton->ajax = $oGrid->record_id;
		$oButton->url = $cm->oPage->site_path . "/admin/import/struct";
		$oGrid->addActionButtonHeader($oButton);
			
 		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "export_struct"; 
	    $oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("export_structure");
		$oButton->action_type = "gotourl";
		$oButton->ajax = true;
		$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "?export";
		$oGrid->addActionButtonHeader($oButton);		
					
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
	                , "service_path" => $cm->oPage->site_path . $cm->oPage->page_path . VG_SITE_SERVICES . "/sort"
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

		if(!$template_framework) {
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
		/*
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "order";
	    $oField->label = ffTemplate::_get_word_by_code("layer_order");
	    $oField->base_type = "Number";
	    $oField->display = false;
	    $oGrid_layer->addContent($oField);*/

	    $cm->oPage->addContent($oGrid_layer, "rel", null, array("title" => ffTemplate::_get_word_by_code("layer_title")));
	}
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
?>
