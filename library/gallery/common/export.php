<?php
check_function("write");

function export() {

}

function export_layout_structure($name = "default", $svg = null, $description = null) {
	$cm = cm::getInstance();
	
	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "ExportLayoutStructure";
	$oRecord->resources[] = $oRecord->id;
	$oRecord->title = ffTemplate::_get_word_by_code("export_layout_structure_title");
	$oRecord->src_table = "";
	$oRecord->skip_action = true;
	$oRecord->addEvent("on_done_action", "export_layout_structure_execute");

	// Campo chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oRecord->addKeyField($oField);

	// Campi visualizzazione
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("export_layout_structure_name");
	$oField->required = true;
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "description";
	$oField->label = ffTemplate::_get_word_by_code("export_layout_structure_description");
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "svg";
	$oField->label = ffTemplate::_get_word_by_code("export_layout_structure_svg");
	$oField->required = true;
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "css";
	$oField->label = "CSS";
	$oField->extended_type = "Text";
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "js";
	$oField->label = "Javascript";
	$oField->extended_type = "Text";
	$oRecord->addContent($oField);	
	

	$cm->oPage->addContent($oRecord);
}

function export_layout_structure_execute($component, $action)
{
	$db = ffDB_Sql::factory();

	$struct = array(
		"name" => $component->form_fields["name"]->getValue()
		, "svg" => $component->form_fields["svg"]->getValue()
		, "description" => $component->form_fields["description"]->getValue()
		, "css" => $component->form_fields["css"]->getValue()
		, "js" => $component->form_fields["js"]->getValue()
		, "layers" => array(
			"items" => array()
			, "rules" => array()
		)
		, "locations" => array(
			"items" => array()
			, "rules" => array()
		)
	);
	
	$sSQL = "SELECT layout_layer.* FROM layout_layer WHERE 1 ORDER BY `order`, ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["layers"]["items"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}
	
	$sSQL = "SELECT layout_layer_path.* FROM layout_layer_path WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = "rule" . $db->getField("ID", "Number", true);
			$struct["layers"]["rules"][$smart_url] = $db->record;
		} while($db->nextRecord()); 
	}

	$sSQL = "SELECT layout_location.* FROM layout_location WHERE 1 ORDER BY `interface_level`, ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["locations"]["items"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}
	
	$sSQL = "SELECT layout_location_path.* FROM layout_location_path WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = "rule" . $db->getField("ID", "Number", true);
			$struct["locations"]["rules"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}	

	$smart_url = ffCommon_url_rewrite($struct["name"]);
	write_array2xml($struct, FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/xml/struct/" . $smart_url . ".xml");
}


function export_vgallery_structure($name = "default", $svg = null, $description = null) {
	$db = ffDB_Sql::factory();
	
	$struct = array(
		"name" => $name
		, "svg" => $svg
		, "description" => $description
		, "layers" => array(
			"items" => array()
			, "rules" => array()
		)
		, "locations" => array(
			"items" => array()
			, "rules" => array()
		)
	);

	$sSQL = "SELECT vgallery_fields_htmltag.* FROM vgallery_fields_htmltag WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["htmltag"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}
	
	$sSQL = "SELECT vgallery_fields_selection_value.* FROM vgallery_fields_selection_value WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["selection"]["values"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}

	$sSQL = "SELECT vgallery_fields_selection.* FROM vgallery_fields_selection WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["selection"]["items"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}

	$sSQL = "SELECT vgallery_groups_menu.* FROM vgallery_groups_menu WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["groups"]["menu"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}	
	
	$sSQL = "SELECT vgallery_groups.* FROM vgallery_groups WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["groups"]["items"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}

	$sSQL = "SELECT vgallery_groups_fields.* FROM vgallery_groups_fields WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = "rule" . ffCommon_url_rewrite($db->getField("ID", "Number", true));
			$struct["groups"]["rules"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}		

	
	$sSQL = "SELECT vgallery_type_group.* FROM vgallery_type_group WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["types"]["groups"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}		
	
	$sSQL = "SELECT vgallery_type.* FROM vgallery_type WHERE 1 ORDER BY `name`, ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["types"]["items"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}

	$sSQL = "SELECT vgallery_fields.* FROM vgallery_fields WHERE 1 ORDER BY `name`, ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["fields"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}
	 
	$smart_url = ffCommon_url_rewrite($struct["name"]);
	write_array2xml($struct, FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/xml/collections/struct/" . $smart_url . ".xml");
}

function export_vgallery($name = "default", $svg = null, $description = null) {
	$db = ffDB_Sql::factory();
	
	$struct = array(
		"name" => $name
		, "svg" => $svg
		, "description" => $description
		, "layers" => array(
			"items" => array()
			, "rules" => array()
		)
		, "locations" => array(
			"items" => array()
			, "rules" => array()
		)
	);

	$sSQL = "SELECT vgallery.* FROM vgallery WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["items"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}

	$sSQL = "SELECT vgallery_nodes.* FROM vgallery_nodes WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["nodes"]["items"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}

	$sSQL = "SELECT vgallery_rel.* FROM vgallery_rel WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["rel"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}
	
	$sSQL = "SELECT vgallery_nodes_rel_languages.* FROM vgallery_nodes_rel_languages WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["nodes"]["lang"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}	

	$sSQL = "SELECT vgallery_rel_nodes_fields.* FROM vgallery_rel_nodes_fields WHERE 1 ORDER BY ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
			$struct["nodes"]["data"][$smart_url] = $db->record;
		} while($db->nextRecord());
	}	
	
	$smart_url = ffCommon_url_rewrite($struct["name"]);
	write_array2xml($struct, FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/xml/collections/" . $smart_url . ".xml");
}
  
