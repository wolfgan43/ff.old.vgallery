<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_VGALLERY_TYPE_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && $_REQUEST["keys"]["ID"] > 0) {
    $db = ffDB_Sql::factory();
    
    $sSQL = "UPDATE vgallery_type_group
            SET vgallery_type_group.visible = " . $db_gallery->toSql($_REQUEST["setvisible"], "Number") . "
            WHERE vgallery_type_group.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
    $db_gallery->execute($sSQL);

    if($_REQUEST["XHR_DIALOG_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("vgalleryTypeGroupModify")), true));
    } else {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("vgalleryTypeGroupModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
    }
} 

if($_REQUEST["keys"]["ID"] > 0) {
	$title = ffTemplate::_get_word_by_code("vgallery_type_group_title_modify");
	$sSQL = "SELECT vgallery_type_group.* 
			FROM vgallery_type_group 
			WHERE vgallery_type_group.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$ID_type = $db_gallery->getField("ID_type", "Number", true);
		$type = $db_gallery->getField("type", "Text", true);
		if(check_function("get_vgallery_type_group")) {
			$arrGroup = get_vgallery_type_group($ID_type, "backoffice", true, $_REQUEST["keys"]["ID"]);
			$is_system = $arrGroup[ffCommon_url_rewrite($db_gallery->getField("name", "Text",true))]["is_system"];
		}
	}
} else {
	$title = ffTemplate::_get_word_by_code("vgallery_type_group_title_addnew");
	$ID_type = $_REQUEST["ID_type"];
	$type = $_REQUEST["type"];
	if(check_function("get_vgallery_type_group")) {
        get_vgallery_type_group($ID_type, "backoffice");
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "vgalleryTypeGroupModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "vgallery_type_group";
$oRecord->insert_additional_fields["type"] = new ffData($type);
$oRecord->insert_additional_fields["visible"] = new ffData("1", "Number");
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-menu">' . cm_getClassByFrameworkCss("vg-virtual-gallery", "icon-tag", array("2x", "content")) . $title . '</h1>';

if($is_system)
	$oRecord->buttons_options["delete"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_name");
if($is_system)
	$oField->control_type = "label";
else
	$oField->required = true;

$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cover";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_cover");
$oField->base_type = "Text";
$oField->control_type = "file";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/vgallery/group/[name_FATHER]";
$oField->file_temp_path = DISK_UPDIR . "/tmp/vgallery/group";
$oField->file_max_size = MAX_UPLOAD;
$oField->file_allowed_mime = array();                    
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_show_filename = true; 
$oField->file_show_delete = true;
$oField->file_writable = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]";
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
    $oField = set_field_uploader($oField);
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cover_mode";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_cover_mode");
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "showfiles_modes ORDER BY name";
if(AREA_PROPERTIES_SHOW_MODIFY) {
    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/image/modify";
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
    $oField->resources[] = "ExtrasImageModify";
}
$oRecord->addContent($oField);


if($ID_type > 0) {
	$oRecord->insert_additional_fields["ID_type"] = new ffData($ID_type, "Number");
	$oRecord->update_additional_fields["ID_type"] = new ffData($ID_type, "Number");
} else {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_type";
	$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_ID_type");
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT vgallery_type.ID
								, vgallery_type.name
							FROM vgallery_type
							WHERE vgallery_type.public = 0
								" . (OLD_VGALLERY 
                                    ? "AND vgallery_type.name <> 'System'"
                                    : ""
                                ) . "
							ORDER BY vgallery_type.name";
	$oField->required = true;
	$oRecord->addContent($oField);
}

switch ($_REQUEST["type"]) {
	case "backoffice":
		$path = "/restricted";
		break;
	case "thumb":
	case "detail":
	default:
		$path = "/";
		break;
}
$sSQL = "SELECT cm_layout.* 
        FROM cm_layout 
        WHERE cm_layout.path = " . $db_gallery->toSql($path);
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) {
    $framework_css = cm_getFrameworkCss($db_gallery->getField("framework_css", "Text", true));
    $template_framework = $framework_css["name"];
}

$group_class = "class";
$oRecord->addContent(null, true, $group_class); 
$oRecord->groups[$group_class] = array(
							"title" => ffTemplate::_get_word_by_code("layout_" . $group_class) . " " . ucwords($template_framework)
							, "cols" => 1
							, "class" => ""
						 );

$oField = ffField::factory($cm->oPage);
$oField->id = "class";
$oField->label = ffTemplate::_get_word_by_code("block_modify_class");
$oRecord->addContent($oField, $group_class);

if($template_framework) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "default_grid";
	$oField->label = ffTemplate::_get_word_by_code($template_framework . "_default_grid");
	$oField->base_type = "Number";
	$oField->widget = "slider";
	$oField->min_val = "0";
	$oField->max_val = "12";
	$oField->step = "1";
	$oField->default_value = new ffData(12, "Number");
	$oRecord->addContent($oField, $group_class);
	
	if($template_framework == "bootstrap" || $template_framework == "foundation") {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_md";
		$oField->label = ffTemplate::_get_word_by_code($template_framework . "_grid_md");
		$oField->base_type = "Number";
		$oField->widget = "slider";
		$oField->min_val = "0";
		$oField->max_val = "12";
		$oField->step = "1";
		$oField->default_value = new ffData(12, "Number");
		$oRecord->addContent($oField, $group_class);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_sm";
		$oField->label = ffTemplate::_get_word_by_code($template_framework . "_grid_sm");
		$oField->base_type = "Number";
		$oField->widget = "slider";
		$oField->min_val = "0";
		$oField->max_val = "12";
		$oField->step = "1";
		$oField->default_value = new ffData(12, "Number");
		$oRecord->addContent($oField, $group_class);

		if($template_framework == "bootstrap") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "grid_xs";
			$oField->label = ffTemplate::_get_word_by_code($template_framework . "_grid_xs");
			$oField->base_type = "Number";
			$oField->widget = "slider";
			$oField->min_val = "0";
			$oField->max_val = "12";
			$oField->step = "1";
			$oField->default_value = new ffData(12, "Number");
			$oRecord->addContent($oField, $group_class);
		}
	}
}

if($type == "backoffice") {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "optional";
	$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_optional");
	$oField->base_type = "Number";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oRecord->addContent($oField);
}
	
$cm->oPage->addContent($oRecord);
?>
