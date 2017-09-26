<?php
$permission = check_coudocs_permission();
if($permission !== true && !(is_array($permission) && $permission["owner"])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

/*$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));

if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffGrid.html")) {
	$oGrid->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
}*/
$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$oGrid = ffGrid::factory($cm->oPage);

$oGrid->full_ajax = true;
$oGrid->id = "category";
//$oGrid->title = ffTemplate::_get_word_by_code("category_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_cloudocs_category.*
                        FROM
                            " . CM_TABLE_PREFIX . "mod_cloudocs_category
                        WHERE 
                            " . CM_TABLE_PREFIX . "mod_cloudocs_category.ID_owner = " . $db->toSql($UserNID, "Number") . "
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "name";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "CategoryModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = true;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;


// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi di ricerca

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("category_name");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);


?>