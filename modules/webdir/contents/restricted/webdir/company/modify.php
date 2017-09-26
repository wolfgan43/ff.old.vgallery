<?php
use_cache(false);

$permission = check_webdir_permission();
if($permission !== true && !(is_array($permission) && count($permission) 
	&& ($permission[MOD_WEBDIR_GROUP_ADMIN]
		|| $permission[MOD_WEBDIR_GROUP_USER])
)) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();

$UserNID = get_session("UserNID");

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && $_REQUEST["keys"]["ID"] > 0) {
    $db = ffDB_Sql::factory();
    
    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_webdir_company 
            SET " . CM_TABLE_PREFIX . "mod_webdir_company.visible = " . $db->toSql($_REQUEST["setvisible"], "Number") . "
            WHERE " . CM_TABLE_PREFIX . "mod_webdir_company.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
    
    if($_REQUEST["XHR_DIALOG_ID"]) {
        die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("CompanyModify")), true));
    } else {
        die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("CompanyModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    }
}  


if($cm->path_info == MOD_WEBDIR_USER_PATH) {
	$simple_interface = true;
} else {
	$simple_interface = false;
}

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_company.*
		FROM " . CM_TABLE_PREFIX . "mod_webdir_company
		WHERE " . CM_TABLE_PREFIX . "mod_webdir_company.owner = " . $db->toSql($UserNID, "Number"); 
$db->query($sSQL);
if($db->nextRecord()) {
	$_REQUEST["keys"]["ID"] = $db->getField("ID", "Number", true);
}

$obj = ffRecord::factory($cm->oPage);
$obj->id = "CompanyModify";
$obj->resources[] = $obj->id;
$obj->title = ffTemplate::_get_word_by_code("webdir_company_modify_title");
$obj->src_table = CM_TABLE_PREFIX . "mod_webdir_company";
$obj->insert_additional_fields["created"] = new ffData(time());
$obj->insert_additional_fields["owner"] = new ffData($UserNID, "Number");
if($simple_interface) {
	$obj->insert_additional_fields["visible"] = new ffData("0", "Number");
} else {
	$obj->insert_additional_fields["visible"] = new ffData("1", "Number");
}
$obj->additional_fields["last_update"] = new ffData(time());
if(!strlen($_REQUEST["ret_url"])) {
	$obj->buttons_options["cancel"]["display"] = false;
}
if($simple_interface) {
	$obj->buttons_options["delete"]["display"] = false;
}

$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->base_type = "Number";
$field->label = "ID";
$obj->addKeyField($field);

$obj->addTab("generic");
$obj->setTabTitle("generic", ffTemplate::_get_word_by_code("webdir_company_modify_generic"));
$obj->addContent(null, true, "generic");
$obj->groups["generic"]["tab"] = "generic";

$field = ffField::factory($cm->oPage);
$field->id = "ID_cat_1";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_ID_cat_1");
$field->base_type = "Number";
$field->required = true;
$field->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_1.ID
						, " . CM_TABLE_PREFIX . "mod_webdir_cat_1.name
					FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_1
					WHERE 1
					ORDER BY " . CM_TABLE_PREFIX . "mod_webdir_cat_1.name";
$field->widget = "activecomboex";
$field->actex_update_from_db = true;
$field->actex_child = "ID_cat_2";
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "ID_cat_2";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_ID_cat_2");
$field->base_type = "Number";
$field->required = true;
$field->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID
						, " . CM_TABLE_PREFIX . "mod_webdir_cat_2.name
						, " . CM_TABLE_PREFIX . "mod_webdir_cat_2.ID_cat_1 AS ID_cat_1
					FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_2
					[WHERE]
					ORDER BY " . CM_TABLE_PREFIX . "mod_webdir_cat_2.name";
$field->widget = "activecomboex";
$field->actex_father = "ID_cat_1";
$field->actex_child = "ID_cat_3";
$field->actex_related_field = "ID_cat_1";
$field->actex_update_from_db = true;
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "ID_cat_3";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_ID_cat_3");
$field->base_type = "Number";
//$field->required = true;
$field->source_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_webdir_cat_3.ID
						, " . CM_TABLE_PREFIX . "mod_webdir_cat_3.name
						, " . CM_TABLE_PREFIX . "mod_webdir_cat_3.ID_cat_2 AS ID_cat_2
					FROM " . CM_TABLE_PREFIX . "mod_webdir_cat_3
					[WHERE]
					ORDER BY " . CM_TABLE_PREFIX . "mod_webdir_cat_3.name";
$field->widget = "activecomboex";
$field->actex_father = "ID_cat_2";
$field->actex_related_field = "ID_cat_2";
$field->actex_update_from_db = true;
$obj->addContent($field, "generic");



$field = ffField::factory($cm->oPage);
$field->id = "name";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_name");
$field->required = true;
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "slug";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_slug");
$field->widget = "slug";
$field->slug_title_field = "name";
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "title";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_title");
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "description";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_description");
$field->extended_type = "Text";
$obj->addContent($field, "generic");

$field = ffField::factory($cm->oPage);
$field->id = "keywords";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_keywords");
$field->extended_type = "Text";
$field->widget = "listgroup";
$field->grouping_separator = ",";
$obj->addContent($field, "generic");

// *********** ADDING TO PAGE ****************

$obj->addTab("homepage");
$obj->setTabTitle("homepage", ffTemplate::_get_word_by_code("webdir_company_modify_content"));
$obj->addContent(null, true, "homepage");
$obj->groups["homepage"]["tab"] = "homepage";


$field = ffField::factory($cm->oPage);
$field->id = "image";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_image");
$field->extended_type = "File";
$field->file_temp_path = FF_DISK_PATH . "/uploads/webdir/company";
$field->file_storing_path = FF_DISK_PATH . "/uploads/webdir/company/[ID_VALUE]";
$field->file_saved_view_url		= FF_SITE_PATH . "/cm/showfiles.php/webdir/company/[ID_VALUE]/[_FILENAME_]";
$field->file_saved_preview_url		= FF_SITE_PATH . "/cm/showfiles.php/webdir/company/[ID_VALUE]/thumb/[_FILENAME_]";
$field->file_temp_view_url			= FF_SITE_PATH . "/cm/showfiles.php/webdir/company/[_FILENAME_]";
$field->file_temp_preview_url		= FF_SITE_PATH . "/cm/showfiles.php/webdir/company/thumb/[_FILENAME_]";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "h1";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_h1");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "h2";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_h2");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "content1";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_content1");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$field = ffField::factory($cm->oPage);
$field->id = "content2";
$field->label = ffTemplate::_get_word_by_code("webdir_company_modify_content2");
$field->extended_type = "Text";
//$field->widget = "ckeditor";
$obj->addContent($field, "homepage");

$cm->oPage->addContent($obj);
