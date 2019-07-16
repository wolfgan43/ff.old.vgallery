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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

 $db = ffDB_Sql::factory();
 
  check_function("system_ffcomponent_set_title");

if(system_ffcomponent_switch_by_path(__DIR__, false)) {
	$disable_dialog = true;

	$sSQL = "SELECT * FROM module_form WHERE module_form.name = " . $db->toSql(basename($cm->real_path_info));
	$db->query($sSQL);
	if($db->nextRecord()) {
	     $ID_form = $db->getField("ID", "Number", true);
	     $enable_revision = $db->getField("enable_revision", "Number", true);
	}
	
	if($ID_form > 0) {
		$sSQL = "SELECT 
		            module_form_fields.ID 
		            , module_form_fields.name 
		        FROM module_form_fields
		            INNER JOIN module_form ON module_form.ID = module_form_fields.ID_module
		        WHERE module_form.ID = " . $db->toSql($ID_form, "Number"). "
		            AND module_form_fields.enable_in_grid = '1' 
		        ORDER BY module_form_fields.`order`";
		$db->query($sSQL);
		if($db->nextRecord()) {
		    $arrFormField = array();
		    $sSQL_field = "";
		    do {
		        $arrFormField[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);

		        $sSQL_field .= ", ";

		        $sSQL_field .= " (SELECT 
		                            module_form_rel_nodes_fields.value
		                        FROM
		                            module_form_rel_nodes_fields
		                        WHERE
		                            module_form_rel_nodes_fields.ID_form_nodes = module_form_nodes.ID
		                            AND module_form_rel_nodes_fields.ID_form_fields = " . $db->tosql($db->getField("ID")) . "
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
		                        ) AS " . $db->tosql($db->getField("name"));
		    } while($db->nextRecord());
		}
	
		$oGrid = ffGrid::factory($cm->oPage);
		$oGrid->ajax_addnew = true;
	    $oGrid->ajax_edit = false;
		$oGrid->ajax_delete = true;
		$oGrid->ajax_search = true;
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
		                     WHERE module_form.ID = " . $db->toSql($ID_form, "Number") . " [AND] [WHERE] [ORDER]";
		$oGrid->order_default = "last_update";
		$oGrid->use_search = FALSE;
	    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "/contents/modify";
		$oGrid->record_id = "FormManageModify";
		$oGrid->resources[] = $oGrid->record_id;
		$oGrid->display_edit_bt = false;
		$oGrid->display_edit_url = MODULE_FORM_SHOW_MANAGE_MODIFY;
		$oGrid->display_delete_bt = MODULE_FORM_SHOW_MANAGE_MODIFY;
		$oGrid->display_new = false;
		$oGrid->addEvent("on_before_parse_row", "FormManageDetail_on_before_parse_row");
		
	    /**
	    * Title
	    */
	    system_ffcomponent_set_title(
	        ffTemplate::_get_word_by_code("form_contents_title")
	        , array(
	            "name" => "database"
	            , "type" => "content"
	        )
	        , false
	        , false
	        , $oGrid
	    );
				

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oGrid->addKeyField($oField);

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
		$oButton->display_label = false;
		$oGrid->addGridButton($oButton);
		                        
		$cm->oPage->addContent($oGrid);
	}
}



function FormManageDetail_on_before_parse_row($component) {
    if(isset($component->grid_buttons["revision"])) {
    	$component->grid_buttons["revision"]->label = ffTemplate::_get_word_by_code("form_revision") . " (" . $component->db[0]->getField("revision", "Number", true) . ")";
        if($component->db[0]->getField("revision", "Number", true)) {
            //$component->grid_buttons["revision"]->image = "revision.png";
            $component->grid_buttons["revision"]->class = "icon ico-revision";
            $component->grid_buttons["revision"]->action_type = "submit"; 
            $component->grid_buttons["revision"]->form_action_url = $component->grid_buttons["revision"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["revision"]->parent[0]->addit_record_param . "ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["revision"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["revision"]->jsaction = "ff.ajax.doRequest({fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["revision"]->action_type = "gotourl";
                //$component->grid_buttons["revision"]->url = $component->grid_buttons["revision"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["revision"]->parent[0]->addit_record_param . "setrevision=0&frmAction=setrevision&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }   
        }
    }

    if(isset($component->grid_buttons["visible"])) {
        if($component->db[0]->getField("visible", "Number", true)) {
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye", "icon");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit"; 
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=0&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }   
        } else {
            $component->grid_buttons["visible"]->class = Cms::getInstance("frameworkcss")->get("eye-slash", "icon", "transparent");
            $component->grid_buttons["visible"]->icon = null;
            $component->grid_buttons["visible"]->action_type = "submit";     
            $component->grid_buttons["visible"]->form_action_url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_CTX_ID"]) {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["visible"]->jsaction = "ff.ajax.doRequest({'action': 'setvisible', fields: [], 'url' : '[[frmAction_url]]'});";
                //$component->grid_buttons["visible"]->action_type = "gotourl";
                //$component->grid_buttons["visible"]->url = $component->grid_buttons["visible"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["visible"]->parent[0]->addit_record_param . "setvisible=1&frmAction=setvisible&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            }    
        }
    }
}
