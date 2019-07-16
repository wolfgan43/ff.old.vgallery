<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
if (!Auth::env("AREA_VGALLERY_TYPE_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");
system_ffcomponent_resolve_by_path();

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && $_REQUEST["keys"]["ID"] > 0) {
    $sSQL = "UPDATE vgallery_type_group
            SET vgallery_type_group.visible = " . $db->toSql($_REQUEST["setvisible"], "Number") . "
            WHERE vgallery_type_group.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);

    //if($_REQUEST["XHR_CTX_ID"]) {
        die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("vgalleryTypeGroupModify")), true));
   // } else {
    //    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("vgalleryTypeGroupModify")), true));
        //ffRedirect($_REQUEST["ret_url"]);
   // }
} 

if($_REQUEST["keys"]["ID"] > 0) {
	$title = ffTemplate::_get_word_by_code("vgallery_type_group_title_modify");
	$sSQL = "SELECT vgallery_type_group.* 
			FROM vgallery_type_group 
			WHERE vgallery_type_group.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$ID_type = $db->getField("ID_type", "Number", true);
		$type = $db->getField("type", "Text", true);
		if(check_function("get_vgallery_type_group")) {
			$arrGroup = get_vgallery_type_group($ID_type, "backoffice", true, $_REQUEST["keys"]["ID"]);
			$is_system = $arrGroup[ffCommon_url_rewrite($db->getField("name", "Text",true))]["is_system"];
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

/* Title Block */
system_ffcomponent_set_title(
	$title
	, true
	, false
	, false
	, $oRecord
);	 

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
$oField->file_storing_path = FF_DISK_UPDIR . "/vgallery/group/[name_FATHER]";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/vgallery/group";
$oField->file_max_size = MAX_UPLOAD;
$oField->file_allowed_mime = array();                    
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_show_filename = true; 
$oField->file_show_delete = true;
$oField->file_writable = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/thumb/[_FILENAME_]";
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
    $oField = set_field_uploader($oField);
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "cover_mode";
$oField->label = ffTemplate::_get_word_by_code("vgallery_type_group_cover_mode");
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "showfiles_modes ORDER BY name";
if(Auth::env("AREA_PROPERTIES_SHOW_MODIFY")) {
    $oField->actex_dialog_url = get_path_by_rule("utility") . "/image/modify";
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

$framework_css = Cms::getInstance("frameworkcss")->getFramework();
$framework_css_name = $framework_css["name"];

$group_class = "class";
$oRecord->addContent(null, true, $group_class); 
$oRecord->groups[$group_class] = array(
							"title" => ffTemplate::_get_word_by_code("layout_" . $group_class) . " " . ucwords($framework_css_name)
							, "cols" => 1
							, "class" => ""
						 );

$oField = ffField::factory($cm->oPage);
$oField->id = "class";
$oField->label = ffTemplate::_get_word_by_code("block_modify_class");
$oRecord->addContent($oField, $group_class);

if($framework_css_name) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "default_grid";
	$oField->label = ffTemplate::_get_word_by_code($framework_css_name . "_default_grid");
	$oField->base_type = "Number";
	$oField->widget = "slider";
	$oField->min_val = "0";
	$oField->max_val = "12";
	$oField->step = "1";
	$oField->default_value = new ffData(12, "Number");
	$oRecord->addContent($oField, $group_class);
	
	if($framework_css_name == "bootstrap" || $framework_css_name == "foundation") {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_md";
		$oField->label = ffTemplate::_get_word_by_code($framework_css_name . "_grid_md");
		$oField->base_type = "Number";
		$oField->widget = "slider";
		$oField->min_val = "0";
		$oField->max_val = "12";
		$oField->step = "1";
		$oField->default_value = new ffData(12, "Number");
		$oRecord->addContent($oField, $group_class);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "grid_sm";
		$oField->label = ffTemplate::_get_word_by_code($framework_css_name . "_grid_sm");
		$oField->base_type = "Number";
		$oField->widget = "slider";
		$oField->min_val = "0";
		$oField->max_val = "12";
		$oField->step = "1";
		$oField->default_value = new ffData(12, "Number");
		$oRecord->addContent($oField, $group_class);

		if($framework_css_name == "bootstrap") {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "grid_xs";
			$oField->label = ffTemplate::_get_word_by_code($framework_css_name . "_grid_xs");
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
