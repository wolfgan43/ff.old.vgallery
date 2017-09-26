<?php
$permission = check_webdir_permission();
if($permission !== true && !(is_array($permission) && count($permission) && $permission[MOD_WEBDIR_GROUP_ADMIN])) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$obj = ffRecord::factory($cm->oPage);
$obj->id = "CategoryModify";
$obj->resources[] = $obj->id;
$obj->title = ffTemplate::_get_word_by_code("webdir_categoty_modify_title");
$obj->src_table = CM_TABLE_PREFIX . "mod_webdir_cat_2";
$obj->insert_additional_fields["ID_cat_1"] = new ffData($_REQUEST["ID_cat_1"]);
$obj->insert_additional_fields["created"] = new ffData(time());
$obj->additional_fields["last_update"] = new ffData(time());


$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->base_type = "Number";
$field->label = "ID";
$obj->addKeyField($field);

$obj->addTab("generic");
$obj->setTabTitle("generic", ffTemplate::_get_word_by_code("webdir_category_modify_generic"));
$obj->addContent(null, true, "generic");
$obj->groups["generic"]["tab"] = "generic";

$field = ffField::factory($cm->oPage);
$field->id = "name";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_name");
$field->required = true;
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "slug";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_slug");
$field->widget = "slug";
$field->slug_title_field = "name";
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "title";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_title");
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "description";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_description");
$field->extended_type = "Text";
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "keywords";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_keywords");
$field->extended_type = "Text";
$field->widget = "listgroup";
$field->grouping_separator = ",";
$obj->addContent($field, "generic");

// *********** ADDING TO PAGE ****************

$obj->addTab("homepage");
$obj->setTabTitle("homepage", ffTemplate::_get_word_by_code("webdir_category_modify_content"));
$obj->addContent(null, true, "homepage");
$obj->groups["homepage"]["tab"] = "homepage";

$field = ffField::factory($cm->oPage);
$field->id = "image";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_image");
$field->extended_type = "File";
$field->file_temp_path = FF_DISK_PATH . "/uploads/webdir/category";
$field->file_storing_path = FF_DISK_PATH . "/uploads/webdir/category/[ID_VALUE]";
$field->file_saved_view_url		= FF_SITE_PATH . "/cm/showfiles.php/webdir/category/[ID_VALUE]/[_FILENAME_]";
$field->file_saved_preview_url		= FF_SITE_PATH . "/cm/showfiles.php/webdir/category/[ID_VALUE]/thumb/[_FILENAME_]";
$field->file_temp_view_url			= FF_SITE_PATH . "/cm/showfiles.php/webdir/category/[_FILENAME_]";
$field->file_temp_preview_url		= FF_SITE_PATH . "/cm/showfiles.php/webdir/category/thumb/[_FILENAME_]";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "h1";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_h1");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "h2";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_h2");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "content1";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_content1");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "content2";
$field->label = ffTemplate::_get_word_by_code("webdir_category_modify_content2");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$cm->oPage->addContent($obj);