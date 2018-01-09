<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_VGALLERY_TYPE_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$src_type = ($_REQUEST["src"]
				? $_REQUEST["src"]
				: "vgallery"
			);
switch($src_type) {
    case "anagraph":
        $src_table =  "anagraph";
        break;
    case "gallery":
        $src_table =  "files";
        break;
    case "vgallery":
        $src_table =  "vgallery_nodes";
        break;
    default:
       // $src_table = $src_type;
}

$limit = $_REQUEST["limit"];
$simple_interface = false;
if(!$limit) {
	$path = $_REQUEST["path"];

	$sSQL = "SELECT DISTINCT
	            " . $src_type . "_type.ID
	        FROM 
	            " . $src_type . "_type
	            INNER JOIN " . $src_table . " ON " . $src_table . ".ID_type = " . $src_type . "_type.ID
	        WHERE " . (OLD_VGALLERY 
                    ? $src_type . "_type.name <> 'System'"
                    : "1"
                ) . "
	            AND NOT(" . $src_type . "_type.public > 0)
	            AND (" . $src_table . ".parent LIKE '" . $db_gallery->toSql($path, "Text", false) . "%'
	            	OR " . $src_table . ".parent = ''
	            )";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		do {
			$limit_type[] = $db_gallery->getField("ID", "Number", true);
		} while($db_gallery->nextRecord());
	}
	
	if(is_array($limit_type)) {
		$limit = implode(",", $limit_type);
		if(count($limit_type) == 1) {
		//CHIEDERE A SAMU COME SI FA IL REDIRECT INTERNO AD UNA DIALOG
			//ffRedirect($cm->oPage->site_path . $cm->oPage->page_path . "/extra?keys[ID]=" . $limit_type[0] . "&XHR_DIALOG_ID=" . $_REQUEST["XHR_DIALOG_ID"]);
			//echo ffCommon_jsonenc(array("callback" => "dialogs.get('" . $_REQUEST["XHR_DIALOG_ID"] ."').params.current_url = '" . $cm->oPage->site_path . $cm->oPage->page_path . "/extra?keys[ID]=" . $limit_type[0] . "';"), true);
			//exit;
		}
	}
}

if(strlen($limit)) {
	$simple_interface = true;
	$sSQL_where = " AND " . $src_type . "_type.ID IN (" . $db_gallery->toSql($limit, "Text", false) . ")";
}

if(MASTER_CONTROL && !$simple_interface)
	$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "vgalleryType";
$oGrid->title = ffTemplate::_get_word_by_code("vgallery_type");
if($src_table) {
	$oGrid->source_SQL = "SELECT 
                            " . $src_type . "_type.*
                        FROM 
                            " . $src_type . "_type
                        WHERE " . (OLD_VGALLERY 
                                ? $src_type . "_type.name <> 'System'"
                                : "1"
                            ) . "
                        	AND NOT(" . $src_type . "_type.public > 0)
                        	$sSQL_where
                        [AND]
	                    [WHERE]
	                    [HAVING]
	                    [ORDER]";
} else {
	$oGrid->source_SQL = "	(
							SELECT 
	                            vgallery_type.*
	                        FROM 
	                            vgallery_type
	                        WHERE 
                        		" . (OLD_VGALLERY 
                                    ? "vgallery_type.name <> 'System'"
                                    : "1"
                                ) . "
                        		AND NOT(vgallery_type.public > 0)
	                        ) UNION (
								SELECT 
		                            anagraph_type.*
		                        FROM 
		                            anagraph_type
		                        WHERE " . (OLD_VGALLERY 
                                        ? "anagraph_type.name <> 'System'"
                                        : "1"
                                    ) . "
                        			AND NOT(anagraph_type.public > 0)
	                        )
	                        [AND]
	                        [WHERE]
	                        [HAVING]
	                        [ORDER] ";
}
$oGrid->order_default = "name";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/extra";
if($simple_interface && $src_type)
	$oGrid->addit_record_param = "src=" . $src_type . "&";

$oGrid->record_id = "VGalleryTypeModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = AREA_VGALLERY_TYPE_SHOW_MODIFY;
$oGrid->display_delete_bt = AREA_VGALLERY_TYPE_SHOW_MODIFY && !$simple_interface;
$oGrid->display_new = AREA_VGALLERY_SHOW_TYPE_MODIFY && !$simple_interface;
$oGrid->buttons_options["export"]["display"] = !$simple_interface;
$oGrid->use_search = !$simple_interface;
$oGrid->use_paging = !$simple_interface;
$oGrid->use_order = !$simple_interface;
$oGrid->display_labels = !$simple_interface;
$oGrid->addEvent("on_before_parse_row", "vgalleryType_on_before_parse_row");

// Chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);


// Visualizzazione
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_name");
$oField->encode_entities = false;
$oGrid->addContent($oField);

if(!$simple_interface) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "clone"; 
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_clone");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
}

if(MASTER_CONTROL && !$simple_interface) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "public";
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
	
	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code($src_type . "_type"))); 
} else {
	$cm->oPage->addContent($oGrid);
}



if(MASTER_CONTROL && !$simple_interface) {
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->full_ajax = true;
	$oGrid->id = "vgalleryTypePublic";
	$oGrid->title = ffTemplate::_get_word_by_code("vgallery_type");
	$oGrid->source_SQL = "SELECT 
	                            " . $src_type . "_type.*
	                        FROM 
	                            " . $src_type . "_type
	                        WHERE " . (OLD_VGALLERY 
                                    ? $src_type . ".name <> 'System'"
                                    : "1"
                                ) . "
                        		AND " . $src_type . "_type.public > 0
	                        [AND]
	                        [WHERE]
	                        [HAVING]
	                        [ORDER] ";

	$oGrid->order_default = "name";
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/extra";
	$oGrid->record_id = "VGalleryTypeModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->display_edit_bt = false;
	$oGrid->display_edit_url = AREA_VGALLERY_TYPE_SHOW_MODIFY;
	$oGrid->display_delete_bt = AREA_VGALLERY_TYPE_SHOW_MODIFY;
	$oGrid->display_new = AREA_VGALLERY_SHOW_TYPE_MODIFY;
	
	$oGrid->addEvent("on_before_parse_row", "vgalleryType_on_before_parse_row");

	// Chiave
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	// Visualizzazione
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("vgallery_type_name");
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
	
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "clone"; 
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("vgallery_type_clone");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

	if(MASTER_CONTROL) {
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "public";
		$oButton->action_type = "gotourl";
		$oButton->url = "";
		$oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("public");
		$oButton->display_label = false;
		$oGrid->addGridButton($oButton);
	}

	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code($src_type . "_type_public"))); 
}

function vgalleryType_on_before_parse_row($component) {
	if(isset($component->grid_fields["name"]) && check_function("get_update_by_service")) {
		$component->grid_fields["name"]->setValue(interface_set_public_field($component->grid_fields["name"], $component->db[0]));
	}

    if($component->db[0]->getField("is_clone", "Number", true) > 0) {
		$component->row_class = "clone";
    } else {
		$component->row_class = "";
    }	
	
	if(isset($component->grid_buttons["clone"])) {
	    $component->grid_buttons["clone"]->action_type = "submit"; 
	    $component->grid_buttons["clone"]->form_action_url = $component->grid_buttons["clone"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["clone"]->parent[0]->addit_record_param . "ret_url=" . urlencode($component->parent[0]->getRequestUri());
	    if($_REQUEST["XHR_DIALOG_ID"]) {
	        $component->grid_buttons["clone"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	    } else {
	        $component->grid_buttons["clone"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'clone', fields: [], 'url' : '[[frmAction_url]]'});";
	        //$component->grid_buttons["visible"]->action_type = "gotourl";
	        //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
		}   
		$component->grid_buttons["clone"]->visible = true;
	} 
    if(isset($component->grid_buttons["public"])) {
    	if(!$component->db[0]->getField("is_clone", "Number", true)) {
		    if($component->db[0]->getField("public", "Number", true)) {
		        //$component->grid_buttons["public"]->image = "visible.png";
                $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon");
                $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_no_public");
	            $component->grid_buttons["public"]->action_type = "submit"; 
	            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            if($_REQUEST["XHR_DIALOG_ID"]) {
	                $component->grid_buttons["public"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["public"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["public"]->action_type = "gotourl";
	                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=0&frmAction=setpublic&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            }   
		    } else {
	            //$component->grid_buttons["public"]->image = "notvisible.png";
                $component->grid_buttons["public"]->class = cm_getClassByFrameworkCss("globe", "icon", array("transparent"));
                $component->grid_buttons["public"]->label = ffTemplate::_get_word_by_code("set_public");
	            $component->grid_buttons["public"]->action_type = "submit";     
	            $component->grid_buttons["public"]->form_action_url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            if($_REQUEST["XHR_DIALOG_ID"]) {
	                $component->grid_buttons["public"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	            } else {
	                $component->grid_buttons["public"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setpublic', fields: [], 'url' : '[[frmAction_url]]'});";
	                //$component->grid_buttons["public"]->action_type = "gotourl";
	                //$component->grid_buttons["public"]->url = $component->grid_buttons["public"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["public"]->parent[0]->addit_record_param . "setpublic=1&frmAction=setpublic&ret_url=" . urlencode($component->parent[0]->getRequestUri());
	            }    
		    }
		    $component->grid_buttons["public"]->visible = true;
		} else {
			$component->grid_buttons["public"]->visible = false;
		}
	}
}

?>
