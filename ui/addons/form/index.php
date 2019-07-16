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
if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");

if(system_ffcomponent_switch_by_path(__DIR__, array("modify", "field"))) {
	$disable_dialog = true;
	
	$cm->oPage->addContent(null, true, "rel"); 

    /**
	* form 
	*/
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->ajax_addnew = true;
    $oGrid->ajax_edit = false;
	$oGrid->ajax_delete = true;
	$oGrid->ajax_search = true;
	$oGrid->id = "FormConfig";
	$oGrid->source_SQL = "SELECT module_form.*
								, IF(module_form.display_name = ''
									, REPLACE(module_form.name, '-', ' ')
									, module_form.display_name
								) AS display_name
							FROM module_form 
						[WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "display_name";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]";
    $oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/add";
	$oGrid->record_id = "FormConfigModify";
	$oGrid->resources[] = $oGrid->record_id;
	$oGrid->addEvent("on_before_parse_row", "form_config_on_before_parse_row");

    /**
    * Title
    */
    system_ffcomponent_set_title(
        null
        , true
        , false
        , false
        , $oGrid
    );	
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("form_name");
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
	$oButton->ajax = true;
	$oButton->action_type = "gotourl";
	$oButton->frmAction = "clone";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]?[KEYS]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("vgallery_clone");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "preview";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]/preview";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("preview_form");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "preview-email";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . STAGE_ADMIN . "/utility/email/preview?keys[ID]=[ID_email_VALUE]&source=form-[name_VALUE]";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("preview_mail");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "database";
	$oButton->action_type = "gotourl";
	$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/[name_VALUE]/contents";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("manage_detail");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);

	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("form_config"))); 


	/**
	* form group
	*/
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->ajax_addnew = !$disable_dialog;
	$oGrid->ajax_edit = !$disable_dialog;
	$oGrid->ajax_delete = true;
	$oGrid->ajax_search = true; 
	$oGrid->id = "FormConfigGroup";
	$oGrid->source_SQL = "SELECT * FROM module_form_fields_group [WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "name";
	$oGrid->use_search = FALSE;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/[name_VALUE]";
	$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/group/add";
	$oGrid->record_id = "FormConfigGroupModify";
	$oGrid->resources[] = $oGrid->record_id;

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("form_group_name");
	$oGrid->addContent($oField); 
	                        
	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("form_group"))); 

	/**
	* form Selection
	*/
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->ajax_addnew = !$disable_dialog;
	$oGrid->ajax_edit = !$disable_dialog;
	$oGrid->ajax_delete = true;
	$oGrid->ajax_search = true; 
	$oGrid->id = "FormConfigSelection";
	$oGrid->source_SQL = "SELECT * FROM module_form_fields_selection [WHERE] [HAVING] [ORDER]";
	$oGrid->order_default = "name";
	$oGrid->use_search = FALSE;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/selection/[name_VALUE]";
	$oGrid->bt_insert_url = $cm->oPage->site_path . $cm->oPage->page_path . "/selection/add";
	$oGrid->record_id = "FormConfigSelectionModify";
	$oGrid->resources[] = $oGrid->record_id;

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("form_selection_name");
	$oGrid->addContent($oField); 
	                        
	$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("form_selection"))); 
}

function form_config_on_before_parse_row($component) {
	if($component->db[0]->getField("send_mail", "Text", true) && $component->db[0]->getField("ID_email", "Text", true)) {
		$component->grid_buttons["preview-email"]->display = true;
	} else {
		$component->grid_buttons["preview-email"]->display = false;
	}
}

