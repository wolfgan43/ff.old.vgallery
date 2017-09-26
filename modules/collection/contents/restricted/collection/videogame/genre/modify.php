<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage); 
$oRecord->id = "videogameGenreModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("mod_collection_videogame_genre_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_collection_videogame_genre";
$oRecord->addEvent("on_done_action", "videogameGenreModify_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("mod_collection_videogame_genre_name");
$oRecord->addContent($oField);

$cm->oPage->addContent($oRecord);
  


function videogameGenreModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if (strlen($action)) 
    {
        switch ($action) 
		{
				case "insert":
				case "update":
						$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_collection_videogame_genre SET
												" . CM_TABLE_PREFIX . "mod_collection_videogame_genre.smart_url = " . $db->toSql(ffCommon_url_rewrite($component->form_fields["name"]->getValue())) . "
												WHERE " . CM_TABLE_PREFIX . "mod_collection_videogame_genre.ID = " . $db->toSql($component->key_fields["ID"]->value, "Number");
						$db->execute($sSQL);
				break;
		}
    }
}