<?php
$permission = check_webdir_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_WEBDIR_GROUP_ADMIN])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();

$sqlWhere = " WHERE ID_cat_1 = " . $db->toSql($_REQUEST["ID_cat_1"]) . " [AND] ";

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_1.slug 
		FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_1 
		WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_1.ID = " . $db->toSql($_REQUEST["ID_cat_1"], "Number");
$db->query($sSQL);
if($db->nextRecord()) {
	$macro_slug = $db->getField("slug", "Text", true);
}

$obj = ffGrid::factory($cm->oPage);
$obj->id = "Category";
$obj->title = ffTemplate::_get_word_by_code("webdir_category_title");
$obj->source_SQL = "SELECT
								" . CM_TABLE_PREFIX . "mod_webdir_cat_2.*
								, (SELECT COUNT(*) FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_3 WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_3.ID_cat_2 = " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID) AS count_subcat
								, '' AS url
							FROM
								" . CM_TABLE_PREFIX . "mod_webdir_cat_2
							$sqlWhere [WHERE]
							[HAVING]
							[ORDER]
		";
$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
$obj->record_id = "CategoryModify";
$obj->resources[] = $obj->record_id;
$obj->order_default = "ID";
$obj->addit_insert_record_param = "ID_cat_1=" . $_REQUEST["ID_cat_1"] . "&";
$obj->addit_record_param = "ID_cat_1=" . $_REQUEST["ID_cat_1"] . "&";
$obj->addEvent("on_before_parse_row", "category_on_before_parse_row");
$obj->user_vars["macro_slug"] = $macro_slug;
$obj->user_vars["webdir_frontend"] = $cm->router->named_rules["webdir_frontend"]->reverse;

$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->label = ffTemplate::_get_word_by_code("webdir_category_id");
$field->base_type = "Number";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->label = ffTemplate::_get_word_by_code("webdir_category_id");
$field->base_type = "Text";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "name";
$field->label = ffTemplate::_get_word_by_code("webdir_category_name");

$obj->addContent($field);
$field = ffField::factory($cm->oPage);
$field->id = "url";
$field->label = ffTemplate::_get_word_by_code("webdir_category_url");
$field->control_type = "link";
$obj->addContent($field);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "subcat";
$oBt->action_type = "gotourl";
$oBt->url = FF_SITE_PATH . $cm->oPage->page_path . "/subcats?ID_cat_2=[ID_VALUE]&macro_slug=" . $macro_slug . "&ret_url=[ENCODED_THIS_URL]";
$oBt->aspect = "link";
$obj->addGridButton($oBt);


$bt = ffButton::factory($cm->oPage);
$bt->id = "back";
$bt->label = ffTemplate::_get_word_by_code("webdir_category_back");
$bt->action_type = "gotourl";
$bt->url = $_REQUEST["ret_url"];
$bt->aspect = "link";
$obj->addActionButton($bt);


$cm->oPage->addContent($obj);

function category_on_before_parse_row($oGrid)
{
	$oGrid->grid_buttons["subcat"]->label = ffTemplate::_get_word_by_code("webdir_category_subcat") . " [" . $oGrid->db[0]->getField("count_subcat")->getValue() . "]";
	$oGrid->grid_fields["url"]->setValue(($oGrid->user_vars["webdir_frontend"] == "/" ? "" : $oGrid->user_vars["webdir_frontend"]) . "/" . $oGrid->user_vars["macro_slug"] . "/" . $oGrid->db[0]->getField("slug", "Text", true));
}