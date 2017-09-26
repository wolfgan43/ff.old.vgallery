<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
if(strlen(basename($cm->real_path_info)) || (isset($_REQUEST["piattaforma"]) && strlen($_REQUEST["piattaforma"])))
{
    $smart_url_console = basename($cm->real_path_info);
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma
                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame_piattaforma.smart_url = " . $db->toSql($smart_url_console);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND FIND_IN_SET(" . $db->toSql($db->getField("ID", "Number", true)) . ", " . CM_TABLE_PREFIX . "mod_collection_videogame.piattaforma)";
        $path = $smart_url_console;
    }
}

if(isset($_REQUEST["publisher"]) && strlen($_REQUEST["publisher"]))
{
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher
                WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.smart_url = " . $db->toSql($_REQUEST["publisher"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND " . CM_TABLE_PREFIX . "mod_collection_videogame.publisher = " . $db->toSql($db->getField("ID", "Number", true));
    }
}

if(isset($_REQUEST["genre"]) && $_REQUEST["genre"])
{
    $sSQL_string .= " AND FIND_IN_SET(" . $db->toSql($_REQUEST["genre"]) . ", " . CM_TABLE_PREFIX . "mod_collection_videogame.genre)";
}

$filename = cm_cascadeFindTemplate("/contents/videogame/videogame-menu.html", "collection");
//$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/videogame/videogame-menu.html", $cm->oPage->theme);
$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("videogame-menu.html", "main");
$videogame_url = $cm->router->named_rules["collection_videogame"]->reverse;
$tpl->set_var("videogame_piattaforma_url", $videogame_url . "/piattaforma");
$tpl->set_var("videogame_genre_url", $videogame_url . "/genre");
$tpl->set_var("videogame_publisher_url", $videogame_url . "/publisher");
$tpl->set_var("videogame_ret_url", $videogame_url);


$cm->oPage->addContent($tpl->rpparse("main", false));

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "videogame";
$oGrid->title = ffTemplate::_get_word_by_code("mod_collection_videogame_grid_title");
$oGrid->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame.*
                        FROM " . CM_TABLE_PREFIX . "mod_collection_videogame
                        WHERE 1 " . $sSQL_string . "
                            [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";
$oGrid->order_default = "title";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/?[KEYS]";
$oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview/?[KEYS]"; 
$oGrid->record_id = "videogameModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "ModuleVideogame_on_before_parse_row");

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
$oField->display_label = false;
$oField->container_class = "cover";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_cover");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/collector/videogame/";
$oField->file_temp_path = DISK_UPDIR . "/collector/videogame/";
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
$oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_title");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "publisher";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_publisher");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.ID
                                    ,  " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher.name
                            FROM " . CM_TABLE_PREFIX . "mod_collection_videogame_publisher
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
$cm->oPage->tplAddCSS("videogame"
    , array(
        "file" => "videogame.css"
        , "path" => "/modules/collection/themes/restricted/css"
));

function ModuleVideogame_on_before_parse_row($component)
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
