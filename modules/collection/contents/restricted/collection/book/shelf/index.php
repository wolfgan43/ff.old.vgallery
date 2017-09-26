<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "shelf";
$oGrid->title = ffTemplate::_get_word_by_code("mod_collection_book_location_grid_title");
$oGrid->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID
                            , " . CM_TABLE_PREFIX . "mod_collection_book_shelf.name AS shelf_name
                            , " . CM_TABLE_PREFIX . "mod_collection_book_location.name AS location_name
                            , " . CM_TABLE_PREFIX . "mod_collection_book_library.name AS library_name
                        FROM " . CM_TABLE_PREFIX . "mod_collection_book_shelf
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_location ON  " . CM_TABLE_PREFIX . "mod_collection_book_location.ID = " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_location
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_library ON  " . CM_TABLE_PREFIX . "mod_collection_book_library.ID = " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_library
                    [AND] [WHERE] 
                    [HAVING]
                    [ORDER], library_name, shelf_name";
$oGrid->order_default = "location_name";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify"; 
$oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/?[KEYS]";
$oGrid->record_id = "bookShelfModify"; 
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "location_name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_location");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "library_name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_library");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "shelf_name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_shelf");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "cancel";
$oButton->action_type = "gotourl";
$oButton->url = "[RET_URL]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("settings_cancel");//Definita nell'evento
$oGrid->addActionButton($oButton);

$cm->oPage->addContent($oGrid);

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