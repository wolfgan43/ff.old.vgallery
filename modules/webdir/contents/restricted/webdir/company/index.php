<?php
	$permission = check_webdir_permission();
	if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_WEBDIR_GROUP_ADMIN])) {
	    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
	}

	$obj = ffGrid::factory($cm->oPage);
	$obj->id = "Company";
	$obj->title = ffTemplate::_get_word_by_code("webdir_company_title");
	$obj->source_SQL = "SELECT
	                            " . CM_TABLE_PREFIX . "mod_webdir_company.*
								, CONCAT('" . $cm->router->named_rules["webdir_frontend"]->reverse . ($cm->router->named_rules["webdir_frontend"]->reverse == "/" ? "" : "/") . "', " . CM_TABLE_PREFIX . "mod_webdir_cat_1.slug, '/', " . CM_TABLE_PREFIX . "mod_webdir_cat_2.slug, '/', " . CM_TABLE_PREFIX . "mod_webdir_cat_3.slug, '/', " . CM_TABLE_PREFIX . "mod_webdir_company.slug) AS url
	                    FROM
	                            " . CM_TABLE_PREFIX . "mod_webdir_company
	                            INNER JOIN " . CM_TABLE_PREFIX . "mod_webdir_cat_1 ON " . CM_TABLE_PREFIX . "mod_webdir_cat_1.ID = " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_1
	                            INNER JOIN " . CM_TABLE_PREFIX . "mod_webdir_cat_2 ON " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID = " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_2
	                            INNER JOIN " . CM_TABLE_PREFIX . "mod_webdir_cat_3 ON " . CM_TABLE_PREFIX . "mod_webdir_cat_3.ID = " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_3
	                    [WHERE]
	                    [HAVING]
	                    [ORDER]
			";
	$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
	$obj->record_id = "CompanyModify";
	$obj->resources[] = $obj->record_id;
	$obj->addEvent("on_before_parse_row", "Company_on_before_parse_row");
	$obj->order_default = "ID";
	$obj->force_no_field_params = true;

	$field = ffField::factory($cm->oPage);
	$field->id = "ID";
	$field->base_type = "Number";
	$obj->addKeyField($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "ID";
	$field->label = ffTemplate::_get_word_by_code("webdir_company_id");
	$field->encode_entities = false;
	$obj->addContent($field, false);

	$field = ffField::factory($cm->oPage);
	$field->id = "name";
	$field->label = ffTemplate::_get_word_by_code("webdir_company_name");
	$obj->addContent($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "url";
	$field->label = ffTemplate::_get_word_by_code("webdir_company_url");
	$field->control_type = "link";
	$obj->addContent($field);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "visible";
	$oButton->action_type = "gotourl";
	$oButton->url = "";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("webdir_company_visible");
	$oButton->template_file = "ffButton_link_fixed.html";                           
	$obj->addGridButton($oButton);
	
	$cm->oPage->addContent($obj);

	function Company_on_before_parse_row($component)
	{
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
