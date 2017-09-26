<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "LibraryModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("mod_collection_book_modify_library");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_collection_book_library";
$oRecord->addEvent("on_done_action", "bookLibraryModify_on_done_action");

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
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_library");
$oField->required = true;
$oRecord->addContent($oField);

$oGrid->addActionButton($oButton);

$cm->oPage->addContent($oRecord);

$cm->oPage->tplAddCSS("collection"
    , array(
        "file" => "collection.css"
        , "path" => "/modules/collection/themes/restricted/css"
));
$cm->oPage->tplAddCSS("book"
    , array(
        "file" => "book.css"
        , "path" => "/modules/collection/themes/restricted/css"
));

function bookLibraryModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        switch ($action) 
		{
				case "insert":
				case "update":
						$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_book_library SET
											" . CM_TABLE_PREFIX . "mod_collection_book_library.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . "
										WHERE " . CM_TABLE_PREFIX . "mod_collection_book_library.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
				break;
		}
    }
}