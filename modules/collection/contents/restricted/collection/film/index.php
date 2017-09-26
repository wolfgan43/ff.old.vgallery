<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();
if(isset($_REQUEST["actor"]) && strlen($_REQUEST["actor"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_actor.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_film_actor
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film_actor.smart_url = " . $db->toSql($_REQUEST["actor"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND FIND_IN_SET(" . $db->toSql($db->getField("ID", "Number", true)) . ", " . CM_TABLE_PREFIX . "mod_collection_film.actor)";
    }
}

if(isset($_REQUEST["director"]) && strlen($_REQUEST["director"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_director.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_film_director
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film_director.smart_url = " . $db->toSql($_REQUEST["director"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND " . CM_TABLE_PREFIX . "mod_collection_film.director = " . $db->toSql($db->getField("ID", "Number", true));
    }
}

if(isset($_REQUEST["quality"]) && strlen($_REQUEST["quality"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_quality.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_film_quality
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film_quality.smart_url = " . $db->toSql($_REQUEST["quality"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND " . CM_TABLE_PREFIX . "mod_collection_film.ID IN (SELECT " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID_film
                                                                                    FROM " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields
                                                                                    WHERE " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID_quality = " . $db->toSql($db->getField("ID", "Number", true)) . ")";
    }
}

if(isset($_REQUEST["support"]) && strlen($_REQUEST["support"]) > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film_support.*
                FROM " . CM_TABLE_PREFIX . "mod_collection_film_support
                WHERE " . CM_TABLE_PREFIX . "mod_collection_film_support.smart_url = " . $db->toSql($_REQUEST["support"]);
    $db->query($sSQL);
    if($db->nextRecord())
    {
        $sSQL_string .= " AND " . CM_TABLE_PREFIX . "mod_collection_film.ID IN (SELECT " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID_film
                                                                                    FROM " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields
                                                                                    WHERE " . CM_TABLE_PREFIX . "mod_collection_film_rel_fields.ID_support = " . $db->toSql($db->getField("ID", "Number", true)) . ")";
    }
}

if(isset($_REQUEST["genre"]) && $_REQUEST["genre"])
{
    $sSQL_string .= " AND FIND_IN_SET(" . $db->toSql($_REQUEST["genre"]) . ", " . CM_TABLE_PREFIX . "mod_collection_film.genre)";
}

$filename = cm_cascadeFindTemplate("/contents/film/film-menu.html", "collection");
//$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/film/film-menu.html", $cm->oPage->theme);
$tpl = ffTemplate::factory(ffCommon_dirname($filename));

$tpl->load_file("film-menu.html", "main");
$film_url = $cm->router->named_rules["collection_film"]->reverse;

$tpl->set_var("film_actor_url", $film_url . "/actor");
$tpl->set_var("film_director_url", $film_url . "/director");
$tpl->set_var("film_genre_url", $film_url . "/genre");
$tpl->set_var("film_quality_url", $film_url . "/quality");
$tpl->set_var("film_support_url", $film_url . "/support");
$tpl->set_var("film_ret_url", $film_url);
$cm->oPage->addContent($tpl->rpparse("main", false));

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "film";
$oGrid->title = ffTemplate::_get_word_by_code("mod_collection_film_grid_title");
$oGrid->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_film.*
                        FROM " . CM_TABLE_PREFIX . "mod_collection_film
						WHERE 1 " . $sSQL_string . "
                    [AND] [WHERE] 
                    [HAVING]
                    [ORDER]";
$oGrid->order_default = "title";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/?[KEYS]";
$oGrid->bt_edit_url = $cm->oPage->site_path . $cm->oPage->page_path . "/preview/?[KEYS]"; 
$oGrid->record_id = "filmModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->addEvent("on_before_parse_row", "ModuleFilm_on_before_parse_row");

$oField = ffField::factory($cm->oPage);
$oField->id = "actor";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_actor");
$oField->source_SQL = "SELECT ID, name 
						FROM " . CM_TABLE_PREFIX . "mod_collection_film_actor
						WHERE 1
						ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->src_operation = "FIND_IN_SET([VALUE], [NAME])";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "director";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_director");
$oField->source_SQL = "SELECT ID, name 
						FROM " . CM_TABLE_PREFIX . "mod_collection_film_director
						WHERE 1
						ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "genre";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_genre");
$oField->source_SQL = "SELECT ID, name 
						FROM " . CM_TABLE_PREFIX . "mod_collection_film_genre
						WHERE 1
						ORDER BY name";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->src_operation = "FIND_IN_SET([VALUE], [NAME])";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_quality";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_quality");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID, name 
                            FROM " . CM_TABLE_PREFIX . "mod_collection_film_quality
                            WHERE 1
                            ORDER BY name";
$oField->actex_update_from_db = true;
$oField->src_operation = "ID IN (SELECT " . CM_TABLE_PREFIX . "mod_collection_film_quality.ID_film FROM " . CM_TABLE_PREFIX . "mod_collection_film_quality WHERE FIND_IN_SET([VALUE],[NAME]))";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_support";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_support");
$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID, name 
                            FROM " . CM_TABLE_PREFIX . "mod_collection_film_support
                            WHERE 1
                            ORDER BY name";
$oField->actex_update_from_db = true;
$oField->src_operation = "ID IN (SELECT " . CM_TABLE_PREFIX . "mod_collection_film_support.ID_film FROM " . CM_TABLE_PREFIX . "mod_collection_film_support WHERE FIND_IN_SET([VALUE],[NAME]))";
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
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_cover");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/collector/film/";
$oField->file_temp_path = DISK_UPDIR . "/collector/film/";
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
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_title");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "year";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_year");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "vote";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_imdb_vote");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "personal_vote";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_film_personal_vote");
$oGrid->addContent($oField);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "favourite";
$oButton->action_type = "gotourl";
$oButton->url = "";
$oButton->aspect = "link"; 
$oButton->label = ffTemplate::_get_word_by_code("mod_collection_film_favourite");
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

function ModuleFilm_on_before_parse_row($component)
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
        

        
