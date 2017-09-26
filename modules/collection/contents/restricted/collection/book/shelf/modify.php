<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "bookShelfModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("mod_collection_book_location_modify_location");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_collection_book_shelf";
$oRecord->addEvent("on_done_action", "bookShelfModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_location";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_location");
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_collection_book_location ORDER BY name";
$oField->widget = "actex";
$oField->actex_update_from_db = true;
$oField->resources[] = "PlaceModify";
$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/place";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=PlaceModify_confirmdelete";
$oField->actex_child = "ID_library";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_library";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_library");
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_collection_book_library [WHERE] ORDER BY name";
$oField->widget = "actex";
$oField->actex_update_from_db = true;
$oField->resources[] = "LibraryModify";
$oField->actex_dialog_url = $cm->oPage->site_path . $cm->oPage->page_path . "/library";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=LibraryModify_confirmdelete";
$oField->actex_father = "ID_location";
$oField->actex_related_field = "ID_location";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_shelf");
$oField->required = true;
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);

function bookShelfModify_on_done_action($component, $action) 
{
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        switch ($action) 
        {
            case "insert":
            case "update":
                    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_book_shelf SET
                                                    " . CM_TABLE_PREFIX . "mod_collection_book_shelf.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . "
                                            WHERE " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
                    $db->execute($sSQL);
            break;
        }
    }
}