<?php
$permission = check_webdir_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_WEBDIR_GROUP_ADMIN])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$obj = ffGrid::factory($cm->oPage);
$obj->id = "MacroCat";
$obj->title = ffTemplate::_get_word_by_code("webdir_macrocat_title");
$obj->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_webdir_cat_1.*
                            , (SELECT COUNT(*) FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_2 WHERE " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID_cat_1 = " . CM_TABLE_PREFIX . "mod_webdir_cat_1.ID) AS count_cat
                            , (SELECT COUNT(DISTINCT " . CM_TABLE_PREFIX . "mod_webdir_company.ID) FROM `" . CM_TABLE_PREFIX . "mod_webdir_company` WHERE " . CM_TABLE_PREFIX . "mod_webdir_company.ID_cat_1 = " . CM_TABLE_PREFIX . "mod_webdir_cat_1.ID) AS count_company
							, '' AS url
                    FROM
                            " . CM_TABLE_PREFIX . "mod_webdir_cat_1
                    $addWhere [WHERE]
                    [HAVING]
                    [ORDER]
		";
$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
$obj->record_id = "MacroCatModify";
$obj->resources[] = $obj->record_id;
$obj->addEvent("on_before_parse_row", "MacroCat_on_before_parse_row");
$obj->order_default = "ID";
$obj->force_no_field_params = true;
$obj->user_vars["webdir_frontend"] = $cm->router->named_rules["webdir_frontend"]->reverse;

$field = ffField::factory($cm->oPage);
$field->id = "ID";

$field->base_type = "Number";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->label = ffTemplate::_get_word_by_code("webdir_macrocat_id");
$field->encode_entities = false;
$obj->addContent($field, false);

$field = ffField::factory($cm->oPage);
$field->id = "name";
$field->label = ffTemplate::_get_word_by_code("webdir_macrocat_name");
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "url";
$field->label = ffTemplate::_get_word_by_code("webdir_macrocat_url");
$field->control_type = "link";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "count_company";
$field->label = ffTemplate::_get_word_by_code("webdir_macrocat_count_company");
$field->base_type = "Number";
$obj->addContent($field);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "category";
$oBt->action_type = "gotourl";
$oBt->url = FF_SITE_PATH . $cm->oPage->page_path . "/category?ID_cat_1=[ID_VALUE]&ret_url=[ENCODED_THIS_URL]";
$oBt->aspect = "link";
$obj->addGridButton($oBt);

$cm->oPage->addContent($obj);

function MacroCat_on_before_parse_row($oGrid)
{
	$oGrid->grid_buttons["category"]->label = ffTemplate::_get_word_by_code("webdir_macrocat_category") . " [" . $oGrid->db[0]->getField("count_cat")->getValue() . "]";
    $oGrid->grid_fields["url"]->setValue(($oGrid->user_vars["webdir_frontend"] == "/" ? "" : $oGrid->user_vars["webdir_frontend"]) . "/" . $oGrid->db[0]->getField("slug", "Text", true));
}
