<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
if(isset($_REQUEST["author"]) && strlen($_REQUEST["author"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_author.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_book_author
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book_author.smart_url = " . $db->toSql($_REQUEST["author"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND FIND_IN_SET(" . $db->toSql($db->getField("ID", "Number", true)) . ", " . CM_TABLE_PREFIX . "mod_collection_book.author)";
    }
}

if(isset($_REQUEST["editore"]) && strlen($_REQUEST["editore"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_editore.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_book_editore
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book_editore.smart_url = " . $db->toSql($_REQUEST["editore"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND FIND_IN_SET(" . $db->toSql($db->getField("ID", "Number", true)) . ", " . CM_TABLE_PREFIX . "mod_collection_book.edizione)";
    }
}

if(isset($_REQUEST["location"]) && strlen($_REQUEST["location"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_location.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_book_location
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book_location.smart_url = " . $db->toSql($_REQUEST["location"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $ID_location = $db->getField("ID", "Number", true);
        $sSQL_string .= " AND " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_location = " . $db->toSql($ID_location, "Number");
    }
}

if($ID_location > 0 && isset($_REQUEST["library"]) && strlen($_REQUEST["library"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_library.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_book_library
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book_library.smart_url = " . $db->toSql($_REQUEST["library"]) . "
                    AND  " . CM_TABLE_PREFIX . "mod_collection_book_library.ID_location = " . $db->toSql($ID_location, "Number");
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $ID_library = $db->getField("ID", "Number", true);
        $sSQL_string .= " AND " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_library = " . $db->toSql($ID_library, "Number");
    }
}

if($ID_location > 0 && $ID_library > 0 && isset($_REQUEST["shelf"]) && strlen($_REQUEST["shelf"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_shelf.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_book_shelf
                WHERE " . CM_TABLE_PREFIX . "mod_collection_book_shelf.smart_url = " . $db->toSql($_REQUEST["library"]) . "
                    AND  " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_location = " . $db->toSql($ID_location, "Number") . "
                    AND  " . CM_TABLE_PREFIX . "mod_collection_book_shelf.ID_library = " . $db->toSql($ID_library, "Number");
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_library = " . $db->toSql($db->getField("ID", "Number", true));
    }
}

$filename = cm_cascadeFindTemplate("/contents/book/book-menu.html", "collection");
//$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/book/book-menu.html", $cm->oPage->theme);
$tpl = ffTemplate::factory(ffCommon_dirname($filename));

$tpl->load_file("book-menu.html", "main");
$book_url = $cm->router->named_rules["collection_book"]->reverse;
$tpl->set_var("book_editore_url", $book_url . "/editore");
$tpl->set_var("book_author_url", $book_url . "/author");
$tpl->set_var("book_shelf_url", $book_url . "/shelf");
$tpl->set_var("book_ret_url", $book_url);

$cm->oPage->addContent($tpl->rpparse("main", false));

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "book";
$oGrid->title = ffTemplate::_get_word_by_code("mod_collection_book_grid_title");
$oGrid->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book.*
                            , GROUP_CONCAT( " . CM_TABLE_PREFIX . "mod_collection_book_author.name) AS author_name
                        FROM " . CM_TABLE_PREFIX . "mod_collection_book 
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_author ON FIND_IN_SET( " . CM_TABLE_PREFIX . "mod_collection_book_author.ID,  " . CM_TABLE_PREFIX . "mod_collection_book.author)
                            LEFT JOIN " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields ON " . CM_TABLE_PREFIX . "mod_collection_book_rel_fields.ID_book = " . CM_TABLE_PREFIX . "mod_collection_book.ID
                        WHERE 1 " . $sSQL_string . "
                    [AND] [WHERE] 
                    [HAVING]
					GROUP BY " . CM_TABLE_PREFIX . "mod_collection_book.ID
                    [ORDER]";
$oGrid->order_default = "title";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/?[KEYS]";
$oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview/?[KEYS]"; 
$oGrid->record_id = "bookModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "ModuleBook_on_before_parse_row");

$oField = ffField::factory($cm->oPage);
$oField->id = "author";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_author");
$oField->source_SQL = "SELECT ID, name 
						FROM " . CM_TABLE_PREFIX . "mod_collection_book_author
						WHERE 1
						ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->src_operation = "FIND_IN_SET([VALUE], [NAME])";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "edizione";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_edizione");
$oField->source_SQL = "SELECT ID, name 
						FROM " . CM_TABLE_PREFIX . "mod_collection_book_editore
						WHERE 1
						ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "favourite";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_is_favorite");
$oField->multi_pairs = array(array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no")))
                                , array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes")))
                        );
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cover";
$oField->container_class = "cover";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_cover");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/collector/book/";
$oField->file_temp_path = DISK_UPDIR . "/collector/book/";
//$oField->file_max_size = MAX_UPLOAD;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/70x70/[_FILENAME_]";//$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "[_FILENAME_]";
//$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb[_FILENAME_]";
$oField->control_type = "picture_no_link";
//$oField->uploadify_model = "horizzontal";
$oGrid->addContent($oField, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_title");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "author_name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_author");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "year";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_year");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "edizione";
$oField->extended_type = "Selection";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_book_edizione");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_book_editore.ID
                                    ,  " . CM_TABLE_PREFIX . "mod_collection_book_editore.name
                            FROM " . CM_TABLE_PREFIX . "mod_collection_book_editore
                            [AND] [WHERE]
                            [HAVING]
                            ORDER BY name"; 
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "favourite";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link"; 
$oButton->label = ffTemplate::_get_word_by_code("mod_collection_book_favourite");
$oButton->template_file = "ffButton_link_fixed.html";                           
$oGrid->addGridButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "edit";
$oButton->action_type = "gotourl";
$oButton->url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify?[KEYS]&ret_url=" . urlencode($cm->oPage->getRequestUri());
$oButton->aspect = "link";
$oButton->template_file = "ffButton_link_fixed.html"; 
$oButton->label = ffTemplate::_get_word_by_code("edit");
$oGrid->addGridButton($oButton); 

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

function ModuleBook_on_before_parse_row($component) 
{
    if(isset($component->grid_buttons["favourite"])) 
    {
        if($component->db[0]->getField("favourite", "Number", true) > 0) 
        {
            $component->grid_buttons["favourite"]->class = null;
            $component->grid_buttons["favourite"]->icon = cm_getClassByFrameworkCss("star", "icon-tag");
            $component->grid_buttons["favourite"]->action_type = "submit"; 
            $component->grid_buttons["favourite"]->form_action_url = $component->grid_buttons["favourite"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["favourite"]->parent[0]->addit_record_param . "setfavourite=0&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_DIALOG_ID"]) 
            {
                $component->grid_buttons["favourite"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setfavourite', fields: [], 'url' : '[[frmAction_url]]'});";
            } else 
            {
                $component->grid_buttons["favourite"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setfavourite', fields: [], 'url' : '[[frmAction_url]]'});";
            }   
        } else 
        {
            $component->grid_buttons["favourite"]->class = null;
            $component->grid_buttons["favourite"]->icon = cm_getClassByFrameworkCss("star-o", "icon-tag");
            $component->grid_buttons["favourite"]->action_type = "submit";     
            $component->grid_buttons["favourite"]->form_action_url = $component->grid_buttons["favourite"]->parent[0]->record_url . "?[KEYS]" . $component->grid_buttons["favourite"]->parent[0]->addit_record_param . "setfavourite=1&ret_url=" . urlencode($component->parent[0]->getRequestUri());
            if($_REQUEST["XHR_DIALOG_ID"]) {
                $component->grid_buttons["favourite"]->jsaction = "javascript:ff.ffPage.dialog.doRequest('[[XHR_DIALOG_ID]]', {'action': 'setstatusbyadmin', fields: [], 'url' : '[[frmAction_url]]'});";
            } else {
                $component->grid_buttons["favourite"]->jsaction = "javascript:ff.ajax.doRequest({'action': 'setstatusbyadmin', fields: [], 'url' : '[[frmAction_url]]'});";
            }    
        }
        $component->grid_buttons["favourite"]->display = true;
    }
}
        
