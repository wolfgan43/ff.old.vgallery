<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;

$form_name = basename($cm->real_path_info);
if(strlen($form_name)) {
    $sSQL = "SELECT * FROM module_form";
    $db_gallery->query($sSQL);
    if($db_gallery->nextRecord()) {
        do {
            if(ffCommon_url_rewrite($db_gallery->getField("name")->getValue()) == $form_name) {
                 $ID_form = $db_gallery->getField("ID")->getValue();
                 $enable_revision = $db_gallery->getField("enable_revision", "Number", true);
                break;
            }
        } while($db_gallery->nextRecord());
    }
}

if($ID_form > 0) {
	$sSQL = "SELECT 
	            module_form_fields.ID 
	            , module_form_fields.name 
	        FROM module_form_fields
	            INNER JOIN module_form ON module_form.ID = module_form_fields.ID_module
	        WHERE module_form.ID = " . $db_gallery->toSql($ID_form, "Number"). "
	            AND module_form_fields.enable_in_grid = '1' 
	        ORDER BY module_form_fields.`order`";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		
		
	    $arrFormField = array();
	    $sSQL_field = "";
	    do {
	        $arrFormField[$db_gallery->getField("ID", "Number")->getValue()] = $db_gallery->getField("name", "Text")->getValue();

	        $sSQL_field .= ", ";

	        $sSQL_field .= " (SELECT 
	                            module_form_rel_nodes_fields.value
	                        FROM
	                            module_form_rel_nodes_fields
	                        WHERE
	                            module_form_rel_nodes_fields.ID_form_nodes = module_form_nodes.ID
	                            AND module_form_rel_nodes_fields.ID_form_fields = " . $db_gallery->tosql($db_gallery->getField("ID", "Number")) . "
	                            " . ($enable_revision
                            		? " AND module_form_rel_nodes_fields.ID_module_revision = IF(module_form_nodes.ID_actual_revision > 0
                            						, module_form_nodes.ID_actual_revision
                            						, (SELECT MAX(max_revision.ID_module_revision) 
                            							FROM module_form_rel_nodes_fields AS max_revision
                            							WHERE max_revision.ID_form_nodes = module_form_rel_nodes_fields.ID_form_nodes
                            						)
                            				)"
                            		: ""
	                            ) . "
	                        LIMIT 1
	                        ) AS " . $db_gallery->tosql($db_gallery->getField("name", "Text"));
	    } while($db_gallery->nextRecord());
	}


	$oGrid->id = "FormManageDetail";
	$oGrid->source_SQL = "SELECT module_form_nodes.ID AS ID
	                        , IF(module_form_nodes.hide > 0, 0, 1) AS visible
	                        , module_form_nodes.name AS last_update
	                        $sSQL_field
	                        " . ($enable_revision
								? ", (SELECT COUNT(DISTINCT module_form_revision.ID) AS count_revision 
										FROM module_form_revision
											INNER JOIN module_form_rel_nodes_fields ON module_form_rel_nodes_fields.ID_module_revision = module_form_revision.ID
										WHERE module_form_rel_nodes_fields.ID_form_nodes = module_form_nodes.ID
									) AS revision"
								: ""
							) . "
	                     FROM module_form_nodes
	                        INNER JOIN module_form ON module_form.ID = module_form_nodes.ID_module
	                     WHERE module_form.ID = " . $db_gallery->toSql($ID_form, "Number") . " [AND] [WHERE] [ORDER]";
	$oGrid->order_default = "last_update";
	$oGrid->use_search = FALSE;
	$oGrid->record_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/modify/" . $form_name;
	$oGrid->record_id = "FormManageModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->display_edit_bt = false;
	$oGrid->display_edit_url = MODULE_FORM_SHOW_MANAGE_MODIFY;
	$oGrid->display_delete_bt = MODULE_FORM_SHOW_MANAGE_MODIFY;
	$oGrid->display_new = false;
	$oGrid->title =  ffTemplate::_get_word_by_code("form_manage_detail_" . $form_name); 
	$oGrid->addEvent("on_before_parse_row", "FormManageDetail_on_before_parse_row");

	//$oGrid->display_labels = false;

	$oField = ffField::factory($cm->oPage);
	$oField->id = "formnode-ID";
	$oField->base_type = "Number";
	$oField->data_source = "ID";
	$oGrid->addKeyField($oField);
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "formnode-ID";
	$oField->base_type = "Number";
	$oField->data_source = "ID";
	$oField->label = ffTemplate::_get_word_by_code("form_ID");
	$oGrid->addContent($oField); */

	$oField = ffField::factory($cm->oPage);
	$oField->id = "last_update";
	$oField->base_type = "Timestamp";
	$oField->extended_type = "DateTime";
	$oField->app_type = "DateTime"; 
	$oField->order_dir = "DESC";
	$oField->label = ffTemplate::_get_word_by_code("form_last_update");
	$oGrid->addContent($oField); 

	if(is_array($arrFormField) && count($arrFormField)) {
	    foreach($arrFormField AS $field_key => $field_value) {
	        $field_name = $field_value;

	        $oField = ffField::factory($cm->oPage);
	        $oField->id = $field_name;
	        $oField->label = ffTemplate::_get_word_by_code("form_" . $field_name);
	        $oField->base_type = "Text";
	        $oGrid->addContent($oField); 
	    } 
	}

	if($enable_revision) {	
		$oField = ffField::factory($cm->oPage);
		$oField->id = "revision";
		$oField->base_type = "Number";
		$oField->label = ffTemplate::_get_word_by_code("form_revision");
		$oGrid->addContent($oField); 
	}

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "visible";
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("form_visible");
	$oButton->template_file = "ffButton_link_fixed.html";                           
	$oGrid->addGridButton($oButton);
	                        
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "back";
	$oButton->action_type = "gotourl";
	$oButton->url = $_REQUEST["ret_url"];
	$oButton->aspect = "link";
	//$oButton->image = "preview.png";
	$oButton->label = ffTemplate::_get_word_by_code("back");//Definita nell'evento
	$oGrid->addActionButton($oButton);

	$cm->oPage->addContent($oGrid);
}




function FormManageDetail_on_before_parse_row($component) {
    if(isset($component->grid_buttons["revision"])) {
    	$component->grid_buttons["revision"]->label = ffTemplate::_get_word_by_code("form_revision") . " (" . $component->db[0]->getField("revision", "Number", true) . ")";
        if($component->db[0]->getField("revision", "Number", true)) {
            //$component->grid_buttons["revision"]->image = "revision.png";
            $component->grid_buttons["revision"]->class = "icon ico-revision";
            $component->grid_buttons["revision"]->action_type = "submit"; 
            $component->grid_buttons["revision"]->form_action_url = $component->grid_buttons["revision"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["revision"]->parent[0]->addit_record_param . "ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["revision"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["revision"]->jsaction = "javascript:ff.ajax.doRequest({fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["revision"]->action_type = "gotourl";
                //$component->grid_buttons["revision"]->url = $component->grid_buttons["revision"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["revision"]->parent[0]->addit_record_param . "setrevision=0&frmAction=setrevision&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }   
        }
    }

    if(isset($component->grid_buttons["visible"])) {
        if($component->db[0]->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit"; 
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }   
        } else {
            $component->grid_buttons["visible"]->class = cm_getClassByFrameworkCss("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit";     
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }    
        }
    }
}
?>
