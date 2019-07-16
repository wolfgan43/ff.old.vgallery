<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$cm->oPage->addContent(null, true, "rel"); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->ajax_addnew = true;
$oGrid->ajax_delete = true;
$oGrid->ajax_search = true;
//$oGrid->title = ffTemplate::_get_word_by_code("form_config");
$oGrid->id = "FormConfig";
$oGrid->source_SQL = "SELECT module_form.*
							, IF(module_form.display_name = ''
								, REPLACE(module_form.name, '-', ' ')
								, module_form.display_name
							) AS display_name
						FROM module_form 
					[WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "display_name";
//$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "FormConfigModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->addEvent("on_before_parse_row", "form_config_on_before_parse_row");

$oField = ffField::factory($cm->oPage);
$oField->id = "formcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("form_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("form_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->data_source = "ID_email";
$oField->label = ffTemplate::_get_word_by_code("form_email");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM email";
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
$oGrid->addContent($oField); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "clone"; 
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("clone_form");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "customizeform";
$oButton->class = "icon ico-customize";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/customize_form/[FormConfig_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("customize_form");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview_form/[FormConfig_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("preview_form");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "preview-email";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview_email/[FormConfig_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "preview.png";
$oButton->label = ffTemplate::_get_word_by_code("preview_mail");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "database";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . ffcommon_dirname($cm->oPage->page_path) . "/manage/detail/[FormConfig_name_VALUE]?ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
//$oButton->image = "detail.png";
$oButton->label = ffTemplate::_get_word_by_code("manage_detail");
$oButton->display_label = false;
$oGrid->addGridButton($oButton);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("form_config"))); 



$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
//$oGrid->title = ffTemplate::_get_word_by_code("form_group");
$oGrid->id = "FormConfigGroup";
$oGrid->source_SQL = "SELECT * FROM module_form_fields_group [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/modules/form/extra/group/modify";
$oGrid->record_id = "FormConfigGroupModify";
$oGrid->resources[] = $oGrid->record_id;

$oField = ffField::factory($cm->oPage);
$oField->id = "formgrp-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("form_group_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 
                        
$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("form_group"))); 


$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
//$oGrid->title = ffTemplate::_get_word_by_code("form_selection");
$oGrid->id = "FormConfigSelection";
$oGrid->source_SQL = "SELECT * FROM module_form_fields_selection [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "name";
$oGrid->use_search = FALSE;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/selection/modify";
$oGrid->record_id = "FormConfigSelectionModify";
$oGrid->resources[] = $oGrid->record_id;

//$oGrid->display_labels = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "formsel-ID";
$oField->base_type = "Number";
$oField->data_source = "ID";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->container_class = "name";
$oField->label = ffTemplate::_get_word_by_code("form_selection_name");
$oField->base_type = "Text";
$oGrid->addContent($oField); 
                        
$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("form_selection"))); 

function form_config_on_before_parse_row($component) {
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
        
	if($component->db[0]->getField("send_mail", "Text", true) && $component->db[0]->getField("ID_email", "Text", true)) {
		$component->grid_buttons["preview-email"]->visible = true;
	} else {
		$component->grid_buttons["preview-email"]->visible = false;
	}
}

?>
