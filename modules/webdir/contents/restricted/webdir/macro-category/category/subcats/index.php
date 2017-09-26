<?php
$permission = check_webdir_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_WEBDIR_GROUP_ADMIN])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();

$sqlWhere = " WHERE ID_cat_2 = " . $db->toSql($_REQUEST["ID_cat_2"]) . " [AND] ";

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_2.slug 
		FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_2 
		WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID = " . $db->toSql($_REQUEST["ID_cat_2"], "Number");
$db->query($sSQL);
if($db->nextRecord()) {
	$category_slug = $db->getField("slug", "Text", true);
}

$macro_slug = $_REQUEST["macro_slug"];

$obj = ffGrid::factory($cm->oPage);
$obj->id = "Subcat";
$obj->title = ffTemplate::_get_word_by_code("webdir_subcat_title");
$obj->source_SQL = "SELECT
								" . CM_TABLE_PREFIX . "mod_webdir_cat_3.*
								, '' AS url
							FROM
								" . CM_TABLE_PREFIX . "mod_webdir_cat_3
							$sqlWhere [WHERE]
							[HAVING]
							[ORDER]
		";
$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
$obj->record_id = "SubcatModify";
$obj->resources[] = $obj->record_id;
$obj->order_default = "ID";
$obj->addit_insert_record_param = "ID_cat_2=" . $_REQUEST["ID_cat_2"] . "&";
$obj->addit_record_param = "ID_cat_2=" . $_REQUEST["ID_cat_2"] . "&";
$obj->addEvent("on_before_parse_row", "subcat_on_before_parse_row");
$obj->user_vars["macro_slug"] = $macro_slug;
$obj->user_vars["category_slug"] = $category_slug;
$obj->user_vars["webdir_frontend"] = $cm->router->named_rules["webdir_frontend"]->reverse;

$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->label = ffTemplate::_get_word_by_code("webdir_subcat_id");
$field->base_type = "Number";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->label = ffTemplate::_get_word_by_code("webdir_subcat_id");
$field->base_type = "Text";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "name";
$field->label = ffTemplate::_get_word_by_code("webdir_subcat_name");
$obj->addContent($field);

$obj->addContent($field);
$field = ffField::factory($cm->oPage);
$field->id = "url";
$field->label = ffTemplate::_get_word_by_code("webdir_subcat_url");
$field->control_type = "link";
$obj->addContent($field);

$bt = ffButton::factory($cm->oPage);
$bt->id = "back";
$bt->label = ffTemplate::_get_word_by_code("webdir_subcat_back");
$bt->action_type = "gotourl";
$bt->url = $_REQUEST["ret_url"];
$bt->aspect = "link";
$obj->addActionButton($bt);

$cm->oPage->addContent($obj);

function subcat_on_before_parse_row($oGrid) 
{
	$oGrid->grid_fields["url"]->setValue(($oGrid->user_vars["webdir_frontend"] == "/" ? "" : $oGrid->user_vars["webdir_frontend"]) . "/" . $oGrid->user_vars["macro_slug"] . "/" . $oGrid->user_vars["category_slug"] . "/" . $oGrid->db[0]->getField("slug", "Text", true));
}
