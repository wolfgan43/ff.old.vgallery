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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

$record = system_ffComponent_resolve_record("module_swf");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SwfConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_swf";
if(check_function("MD_swf_on_loaded_data"))
	$oRecord->addEvent("on_loaded_data", "MD_swf_on_loaded_data");
if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

/* Title Block */
system_ffcomponent_set_title(
    $record["name"]
    , true
    , false
    , false
    , $oRecord
); 
   
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_title");
$oField->required = TRUE;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "swf_url";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_swfurl");
$oField->base_type = "Text";
$oField->control_type = "file";
$oField->extended_type = "File";

$oField->file_storing_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/swf/" . "[name_VALUE]";
$oField->file_temp_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/swf";
$oField->file_max_size = MAX_UPLOAD;

$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/thumb" . "/[_FILENAME_]";
//$oField->file_temp_view_url = CM_SHOWFILES . "/[_FILENAME_]";
//$oField->file_temp_preview_url = CM_SHOWFILES . "/thumb" . "/[_FILENAME_]";
$oField->file_allowed_mime = array("swf");

$oField->file_base_path = FF_DISK_PATH . FF_THEME_DIR;
$oField->file_show_filename = true; 
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;

$oField->file_show_delete = true;
$oField->file_writable = false;
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}


 
//$oField->file_show_path = false;
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "limit";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_limit");
$oField->base_type = "Number";
$oField->required = TRUE;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "width";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_width");
$oField->base_type = "Number";
$oField->required = TRUE;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "height";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_height");
$oField->base_type = "Number";
$oField->required = TRUE;
$oRecord->addContent($oField);

$oRecord->addTab("params");
$oRecord->setTabTitle("params", ffTemplate::_get_word_by_code("swf_modify_params"));

$oRecord->addContent(null, true, "params"); 
$oRecord->groups["params"] = array(
                                 "title" => ffTemplate::_get_word_by_code("swf_modify_params")
                                 , "cols" => 1
                                 , "tab" => "params"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "play";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_play");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "loop";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_loop");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "menu";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_menu");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "quality";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_quality");
$oField->default_value = new ffData("hight", "Text");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "scale";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_scale");
$oField->default_value = new ffData("default", "Text");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "salign";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_salign");
$oField->default_value = new ffData("medium", "Text");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "wmode";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_wmode");
$oField->default_value = new ffData("transparent", "Text");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "bgcolor";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_bgcolor");
$oField->default_value = new ffData("FFFFFF", "Text");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "base";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_base");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "swliveconnect";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_swliveconnect");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "flashvars";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_flashvars");
$oField->widget = "listgroup";
$oField->grouping_separator = "&";
$oField->encode_entities = false;
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "devicefont";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_devicefont");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "allowscriptaccess";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_allowscriptaccess");
$oField->default_value = new ffData("always", "Text");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "seamlesstabbing";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_seamlesstabbing");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "allownetworking";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_allownetworking");
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "align";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_align");
$oField->default_value = new ffData("center", "Text");
$oRecord->addContent($oField, "params");

$oField = ffField::factory($cm->oPage);
$oField->id = "version";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_version");
$oField->default_value = new ffData("9.0.0", "Text");
$oRecord->addContent($oField, "params");

$cm->oPage->addContent($oRecord);

$oRecord->addTab("xml");
$oRecord->setTabTitle("xml", ffTemplate::_get_word_by_code("swf_modify_xml"));

$oRecord->addContent(null, true, "xml"); 
$oRecord->groups["xml"] = array(
                                 "title" => ffTemplate::_get_word_by_code("swf_modify_xml")
                                 , "cols" => 1
                                 , "tab" => "xml"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_xml";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_enable_xml");
$oField->base_type = "Number";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "xml");

$oField = ffField::factory($cm->oPage);
$oField->id = "xml_url";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_xmlurl");
$oField->required = TRUE;
$oField->data_type = "";
$oField->control_type = "label";
$oRecord->addContent($oField, "xml");

$oField = ffField::factory($cm->oPage);
$oField->id = "xml_varname";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_xmlvarname");
$oField->required = TRUE;
$oRecord->addContent($oField, "xml");

$oField = ffField::factory($cm->oPage);
$oField->id = "tblsrc";
$oField->data_source = "tbl_src";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_tbl_src");
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->multi_pairs = array (
                            array(new ffData("files"), new ffData(ffTemplate::_get_word_by_code("gallery"))),
                            array(new ffData("vgallery_nodes"), new ffData(ffTemplate::_get_word_by_code("vgallery"))),
                            array(new ffData("publishing"), new ffData(ffTemplate::_get_word_by_code("publishing")))
                       );      
$oField->required = true;
$oField->actex_child = "items";
$oRecord->addContent($oField, "xml");
                
$oField = ffField::factory($cm->oPage);
$oField->id = "items";
$oField->data_source = "items";
$oField->label = ffTemplate::_get_word_by_code("swf_modify_items");
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->source_SQL = "
                    SELECT ID, path, type FROM 
                    (
                        SELECT ID, path, type FROM
                        (
                            (
                                SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path, 'files' AS type
                                FROM files
                                WHERE files.is_dir > 0
                            ) UNION (
                                SELECT ID, CONCAT(IF(parent = '/', '', parent), '/', name) AS path, 'vgallery_nodes' AS type
                                FROM vgallery_nodes
                                WHERE vgallery_nodes.is_dir > 0
                            ) UNION (
                                SELECT ID, name AS path, 'publishing' AS type
                                FROM publishing
                            ) UNION (
                                SELECT '" . VG_SITE_SEARCH . "/files' AS ID, '/gallery' AS name, 'search' AS type
                            )
                        ) AS sub_tbl_src
                        ORDER BY type, path
                    ) AS tbl_src
                    [WHERE]";
$oField->actex_father = "tblsrc";
$oField->actex_related_field = "type";
$oField->actex_update_from_db = true;
$oField->required = true;
$oRecord->addContent($oField, "xml");  

$oDetail_fields = ffDetails::factory($cm->oPage);
$oDetail_fields->id = "ModuleSwfModifyDFields";
$oDetail_fields->title = ffTemplate::_get_word_by_code("module_swf_modify_dfields_title");
$oDetail_fields->src_table = "module_swf_vgallery";
$oDetail_fields->order_default = "ID";
$oDetail_fields->fields_relationship = array ("ID_module_swf" => "swfcnf-ID");
$oDetail_fields->display_new = false;
$oDetail_fields->display_delete = false;
$oDetail_fields->auto_populate_insert = true;
$oDetail_fields->populate_insert_SQL = "
                SELECT 
                    vgallery_fields.ID AS ID_vgallery_fields
                    , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS typename
                    , '' AS alt_field_name
                    , '0' AS value
                    , '0' AS `order`
                    , vgallery_fields.ID_extended_type AS `ID_extended_type`
                    , '' AS `settings_type`
                    , '0' AS `enable_lastlevel`
                    , '0' AS `enable_thumb_label`
                    , '0' AS `enable_thumb_empty`
                    , '' AS `thumb_limit`
                    , vgallery_fields.ID_type AS ID_type
                    , '' AS display_view_mode
                    , '' AS enable_thumb_cascading
                    , '' AS enable_sort
                    , 0 AS meta_thumb_limit
                FROM vgallery_fields 
                    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                WHERE 1
                    AND vgallery_type.name <> " . $db->toSql("System", "Text") . "
                ORDER BY vgallery_fields.ID_type, vgallery_fields.`order_thumb`";
$oDetail_fields->auto_populate_edit = true;
$oDetail_fields->populate_edit_SQL = "
                SELECT 
                    (
                        SELECT module_swf_vgallery.ID
                        FROM module_swf_vgallery
                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                    ) 
                    AS ID
                    , [swfcnf-ID_FATHER] AS ID_module_swf
                    , vgallery_fields.ID AS ID_vgallery_fields
                    , CONCAT(vgallery_type.name, ' - ', vgallery_fields.name) AS typename
                    , 
                                    (
                                        SELECT alt_field_name
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS alt_field_name
                    , 
                                    (
                                        SELECT value
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS value
                    , 
                                    (
                                        SELECT `order`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `order`
                    , vgallery_fields.ID_extended_type AS `ID_extended_type`
                    , 
                                    (
                                        SELECT `settings_type`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `settings_type`
                    , 
                                    (
                                        SELECT `enable_lastlevel`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `enable_lastlevel`
                    , 
                                    (
                                        SELECT `enable_thumb_label`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `enable_thumb_label`
                    , 
                                    (
                                        SELECT `enable_thumb_empty`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `enable_thumb_empty`
                    , 
                                    (
                                        SELECT `thumb_limit`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `thumb_limit`
                    , 
                                    (
                                        SELECT `display_view_mode`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `display_view_mode`
                    , 
                                    (
                                        SELECT `enable_thumb_cascading`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `enable_thumb_cascading`
                    , 
                                    (
                                        SELECT `enable_sort`
                                        FROM module_swf_vgallery
                                        WHERE module_swf_vgallery.ID_vgallery_fields = vgallery_fields.ID
                                        AND module_swf_vgallery.ID_module_swf =[swfcnf-ID_FATHER]
                                    ) 
                    AS `enable_sort`
                    , vgallery_fields.ID_type AS ID_type
                FROM vgallery_fields
                    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                WHERE 1
                    AND vgallery_type.name <> " . $db->toSql("System", "Text") . "
                ORDER BY vgallery_fields.ID_type, `order`";
$oDetail_fields->tab = true;
$oDetail_fields->tab_label = "typename";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail_fields->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "typename";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_typename");
$oField->store_in_db = false;
$oDetail_fields->addHiddenField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_vgallery_fields";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_fields");
$oField->base_type = "Number";
$oField->control_type = "label";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM vgallery_fields";
$oField->required = true;
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "alt_field_name";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_alt_field_name");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_type";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_idtype");
$oField->base_type = "Number";
$oField->control_type = "label";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM vgallery_type";
$oField->store_in_db = false;
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_value");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oField->required = true;
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_extended_type";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_extended_type");
if(check_function("set_field_extended_type"))
	$oField = set_field_extended_type($oField);

$oField->actex_child = "settings_type";
$oField->store_in_db = false;
$oField->multi_limit_select = true;
$oDetail_fields->addContent($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "settings_type";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_settings_type");
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->source_SQL = "SELECT ID, name, type FROM 
                    (
                        ( 
                            SELECT 
                                ID
                                , name
                                , 8 AS type 
                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
                            ORDER BY name
                        )
                        UNION
                        ( 
                            SELECT 
                                ID
                                , name
                                , 15 AS type 
                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
                            ORDER BY name
                        )
                        UNION
                        ( 
                            SELECT 
                                ID
                                , name
                                , 16 AS type 
                            FROM " . CM_TABLE_PREFIX . "showfiles_modes
                            ORDER BY name
                        )
                    ) AS tbl_src
                    [WHERE]
                    ORDER BY tbl_src.name";
$oField->actex_father = "ID_extended_type";
$oField->actex_related_field = "type";
$oField->actex_dialog_url = get_path_by_rule("utility") . "/image/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
$oField->resources[] = "ExtrasImageModify";
$oField->actex_update_from_db = true;
$oDetail_fields->addContent($oField);   

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_lastlevel";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_enable_lastlevel");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_label";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_enable_thumb_label");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_empty";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_enable_thumb_empty");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "thumb_limit";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_thumb_limit");
$oField->base_type = "Number";
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "order";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_order");
$oField->base_type = "Number";
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_cascading";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_enable_thumb_cascading");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "display_view_mode";
$oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_display_view_mode");
$oDetail_fields->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_sort";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_enable_sort");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->unchecked_value = new ffData("0", "Number");
$oField->checked_value = new ffData("1", "Number");
$oDetail_fields->addContent($oField);
/*
$oField = ffField::factory($cm->oPage);
$oField->id = "meta_thumb_limit";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dfields_meta_thumb_limit");
$oField->base_type = "Number";
$oDetail_fields->addContent($oField);
*/
$oRecord->addContent($oDetail_fields, "xml");
$cm->oPage->addContent($oDetail_fields);

$oDetail_criteria = ffDetails::factory($cm->oPage);
$oDetail_criteria->id = "ModuleSwfModifyDCriteria";
$oDetail_criteria->title = ffTemplate::_get_word_by_code("module_swf_modify_dcriteria_title");
$oDetail_criteria->src_table = "module_swf_criteria";
$oDetail_criteria->order_default = "ID";
$oDetail_criteria->fields_relationship = array ("ID_module_swf" => "swfcnf-ID");
$oDetail_criteria->display_new = true;
$oDetail_criteria->display_delete = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oDetail_criteria->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "src_fields";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dcriteria_fields");
$oDetail_criteria->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "operator";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dcriteria_operator");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("="), new ffData(ffTemplate::_get_word_by_code("="))),
                            array(new ffData("<"), new ffData(ffTemplate::_get_word_by_code("<"))),
                            array(new ffData(">"), new ffData(ffTemplate::_get_word_by_code(">"))),
                            array(new ffData("<="), new ffData(ffTemplate::_get_word_by_code("<="))),
                            array(new ffData(">="), new ffData(ffTemplate::_get_word_by_code(">="))),
                            array(new ffData("<>"), new ffData(ffTemplate::_get_word_by_code("<>"))),
                            array(new ffData("LIKE"), new ffData(ffTemplate::_get_word_by_code("LIKE")))
                       );
$oField->required = true;
$oDetail_criteria->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "value";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_dcriteria_value");
$oDetail_criteria->addContent($oField);

$oRecord->addContent($oDetail_criteria, "xml");
$cm->oPage->addContent($oDetail_criteria);


$oRecord->addTab("tplsettings");
$oRecord->setTabTitle("tplsettings", ffTemplate::_get_word_by_code("module_swf_modify_tpl_settings_title"));

$oRecord->addContent(null, true, "tplsettings"); 
$oRecord->groups["tplsettings"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_swf_modify_tpl_settings_title")
                                 , "cols" => 1
                                 , "tab" => "tplsettings"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_title_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_title_tag");
$oRecord->addContent($oField, "tplsettings");
                              
$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_main_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_main_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_parent_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_parent_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_row_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_row_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_row_image_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_row_image_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_row_field_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_row_field_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_sub_parent_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_sub_parent_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_sub_row_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_sub_row_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_sub_row_image_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_sub_row_image_tag");
$oRecord->addContent($oField, "tplsettings");

$oField = ffField::factory($cm->oPage);
$oField->id = "tpl_sub_row_field_tag";
$oField->label = ffTemplate::_get_word_by_code("module_swf_modify_tplsettings_tpl_sub_row_field_tag");
$oRecord->addContent($oField, "tplsettings");

