<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission) && $permission[global_settings("MOD_CROWDFUND_GROUP_ADMIN")])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
$UserNID = get_session("UserNID");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "BannerModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_banner_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_crowdfund_banner";
$oRecord->addEvent("on_done_action", "BannerModify_on_done_action");


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_banner_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_banner_limit");
$oField->base_type = "Number";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "col";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_banner_col");
$oField->base_type = "Number";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_showfiles_modes";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_banner_showfiles_modes");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "showfiles_modes.ID
							, " . CM_TABLE_PREFIX . "showfiles_modes.name
						FROM " . CM_TABLE_PREFIX . "showfiles_modes
						WHERE 1
						ORDER BY " . CM_TABLE_PREFIX . "showfiles_modes.name ";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/cm/showfiles-modes/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=MainRecord_confirmdelete";
$oField->resources[] = "cmSettingsModes"; 
$oRecord->addContent($oField);


$cm->oPage->addContent($oRecord);   

$oDetail = ffDetails::factory($cm->oPage);
$oDetail->id = "BannerDetail";
$oDetail->title = "";//ffTemplate::_get_word_by_code("drafts_modify_detail_title");
$oDetail->widget_discl_enable = false;
$oDetail->src_table = CM_TABLE_PREFIX . "mod_crowdfund_banner_rel_idea";
$oDetail->order_default = "ID";
$oDetail->fields_relationship = array ("ID_banner" => "ID");
$oDetail->display_new = true;
//$oDetail->display_delete = false;
$oDetail->addEvent("on_do_action", "BannerDetail_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_idea";
$oField->label = ffTemplate::_get_word_by_code("crowdfund_banner_detail_idea");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
							, (SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title AS name
                            	FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages
                            	WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
                            		AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
                            ) AS name
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.status > 0
						ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->required = true;
$oDetail->addContent($oField);


$oRecord->addContent($oDetail);
$cm->oPage->addContent($oDetail);
          
function BannerDetail_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(is_array($component->recordset) && count($component->recordset)) {
        $ID_node = $component->main_record[0]->key_fields["ID"]->value;
        
    }
}          

function BannerModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
        //UPDATE CACHE
        /*$sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `drafts`.last_update FROM drafts WHERE drafts.ID = " . $db->toSql($component->key_fields["ID"]->value) . ") 
                WHERE 
                    (
                        layout.value = " . $db->toSql($component->key_fields["ID"]->value) . "
                        AND layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("STATIC_PAGE_BY_DB") . ")
                    )
                    ";
        $db->execute($sSQL);*/
        //UPDATE CACHE 
    }

    
}

  
?>