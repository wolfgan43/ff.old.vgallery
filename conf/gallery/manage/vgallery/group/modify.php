<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_VGALLERY_GROUP_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "VGalleryGroupModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("vgallery_group_modify");
$oRecord->src_table = "vgallery_groups";
$oRecord->addEvent("on_done_action", "VGalleryGroupModify_on_done_action");
$oRecord->display_required_note = FALSE;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_menu";
$oField->container_class = "settings_type";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_settings_type");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID, name FROM vgallery_groups_menu [WHERE]";
$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/menu/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryGroupMenuModify_confirmdelete";
$oField->actex_update_from_db = true;
$oField->resources[] = "VGalleryGroupMenuModify";
$oField->required = true;
$oRecord->addContent($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_name");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_item_child";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_enable_item_child");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "meta_title_alt";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_meta_title_alt");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_description");
$oField->extended_type = "Text";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "default";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_default");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "sort";                                                     
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_sort");
$oField->base_type = "Number";
$oRecord->addContent($oField);

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);

$oDetail_fields = ffDetails::factory($cm->oPage);
$oDetail_fields->id = "VGalleryGroupModifyDFields";
$oDetail_fields->title = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_title");
$oDetail_fields->src_table = "vgallery_groups_fields";
$oDetail_fields->order_default = "ID";
$oDetail_fields->fields_relationship = array ("ID_group" => "ID");
$oDetail_fields->tab = true;
//$oDetail_fields->tab_label = "field_name";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail_fields->addKeyField($oField);

     /*
$oField = ffField::factory($cm->oPage);
$oField->id = "field_name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_field_name");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS ID
                            , vgallery_fields.name AS name
                        FROM vgallery_fields
                            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                        [WHERE] 
                        ORDER BY vgallery_type.name, vgallery_fields.name";
$oField->actex_update_from_db = true;
$oField->actex_father = "ID_type";
$oField->actex_related_field = "ID_type";
$oField->store_in_db = false;
$oField->display = false;
$oDetail_fields->addContent($oField); */

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_fields";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_fields");
$oField->base_type = "Number";
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID
                            , name 
                            , vgallery_fields.ID_type
                        FROM vgallery_fields
                        [WHERE] 
                        ORDER BY name";
$oField->actex_update_from_db = true;
$oField->actex_father = "ID_type";
$oField->actex_child = "ID_extended_type";
$oField->actex_related_field = "ID_type";
$oField->required = true;
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "settings_type_detail";
$oField->base_type = "Number";
$oField->container_class = "settings_type";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_settings_type");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID, name, type FROM 
                    (
                        ( 
                            SELECT 
                                ID
                                , name
                                , 8 AS type 
                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
                            ORDER BY name
                        )
                        UNION
                        ( 
                            SELECT 
                                ID
                                , name
                                , 15 AS type 
                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
                            ORDER BY name
                        )
						UNION
                        ( 
                            SELECT 
                                ID
                                , name
                                , 16 AS type 
                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
                            ORDER BY name
                        )
                    ) AS tbl_src
                    [WHERE]
                    ORDER BY tbl_src.name";
$oField->actex_father = "ID_extended_type";
$oField->actex_related_field = "type";
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/extras/image/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
$oField->resources[] = "ExtrasImageModify";
$oField->actex_update_from_db = true;
$oDetail_fields->addContent($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "parent_detail";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_parent");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order_detail";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_order");
$oField->base_type = "Number";
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_detail_label";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_enable_detail_label");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_detail_empty";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_enable_detail_empty");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "enable_detail_cascading";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_dfields_enable_detail_cascading");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "display_view_mode_detail";
$oField->container_class = "display_view_mode";
$oField->label = ffTemplate::_get_word_by_code("vgallery_group_modify_type_display_view_mode");
$oDetail_fields->addContent($oField); 

$oRecord->addContent($oDetail_fields);
$cm->oPage->addContent($oDetail_fields);

function VGalleryGroupModify_on_done_action($component, $action) {
	if($action == "confirmdelete" || (strlen($component->form_fields["name"]->value_ori->getValue()) && $component->form_fields["name"]->value_ori->getValue() != $component->form_fields["name"]->value->getValue())) {
		if(check_function("refresh_cache")) {
            //tutte le vgallery in teoria
        	//refresh_cache_get_blocks_by_layout("");
		}
	}
}
?>
