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

if (!AREA_PROPERTIES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

check_function("system_ffcomponent_set_title");

system_ffComponent_resolve_record(CM_TABLE_PREFIX . "showfiles_modes");
// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ExtrasImageModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = CM_TABLE_PREFIX . "showfiles_modes";
$oRecord->addEvent("on_done_action", "ExtrasImageModify_on_done_action");
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT " . CM_TABLE_PREFIX . "showfiles_modes.*
									, IF(" . CM_TABLE_PREFIX . "showfiles_modes.display_name = ''
										, REPLACE(" . CM_TABLE_PREFIX . "showfiles_modes.name, '-', ' ')
										, " . CM_TABLE_PREFIX . "showfiles_modes.display_name
									) AS display_name
								FROM " . CM_TABLE_PREFIX . "showfiles_modes 
								WHERE " . CM_TABLE_PREFIX . "showfiles_modes.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

/* Title Block */
	system_ffcomponent_set_title(
		null
		, array(
			"name" => "crop"
			, "type" => "content"
		)
		, false
		, false
		, $oRecord
	);	

if(check_function("get_file_properties"))
	$file_properties = get_image_default();

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dim_x";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_fix_x");
$oField->base_type = "Number";
$oField->default_value = new ffData($file_properties["dim_x"], "Text");
$oField->setWidthComponent(6);
$oField->required = true;
$oField->min_val = 1;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dim_y";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_fix_y");
$oField->base_type = "Number";
$oField->default_value = new ffData($file_properties["dim_y"], "Text");
$oField->setWidthComponent(6);
$oField->required = true;
$oField->min_val = 1;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_name");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_smart_url");
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oField->container_class = "hidden";
$oRecord->addContent($oField);

$oRecord->addTab("image");
$oRecord->setTabTitle("image", ffTemplate::_get_word_by_code("extras_image_modify_image"));

$oRecord->addContent(null, true, "image"); 
$oRecord->groups["image"] = array(
                                 "title" => ffTemplate::_get_word_by_code("extras_image_modify_image")
                                 , "cols" => 1
                                 , "tab" => "image"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "mode";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_mode");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("crop"), new ffData(ffTemplate::_get_word_by_code("crop"))),
                            array(new ffData("proportional"), new ffData(ffTemplate::_get_word_by_code("proportional"))),
                            array(new ffData("stretch"), new ffData(ffTemplate::_get_word_by_code("stretch")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("none");
$oField->default_value = new ffData($file_properties["mode"], "Text");
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");                                

$oField = ffField::factory($cm->oPage);
$oField->id = "when";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_when");
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array(new ffData("ever"), new ffData(ffTemplate::_get_word_by_code("always")))
								, array(new ffData("smaller"), new ffData(ffTemplate::_get_word_by_code("to_smaller")))
								, array(new ffData("bigger"), new ffData(ffTemplate::_get_word_by_code("to_bigger")))
							);
$oField->multi_select_one = false;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");

$oField = ffField::factory($cm->oPage);
$oField->id = "alignment";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modifyalign");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("top-left"), new ffData(ffTemplate::_get_word_by_code("top-left"))),
                            array(new ffData("top-middle"), new ffData(ffTemplate::_get_word_by_code("top-middle"))),
                            array(new ffData("top-right"), new ffData(ffTemplate::_get_word_by_code("top-right"))),
                            array(new ffData("middle-left"), new ffData(ffTemplate::_get_word_by_code("middle-left"))),
                            array(new ffData("center"), new ffData(ffTemplate::_get_word_by_code("center"))),
                            array(new ffData("middle-right"), new ffData(ffTemplate::_get_word_by_code("middle-right"))),
                            array(new ffData("bottom-left"), new ffData(ffTemplate::_get_word_by_code("bottom-left"))),
                            array(new ffData("bottom-middle"), new ffData(ffTemplate::_get_word_by_code("bottom-middle"))),
                            array(new ffData("bottom-right"), new ffData(ffTemplate::_get_word_by_code("bottom-right")))
                       );
$oField->default_value = new ffData($file_properties["alignment"], "Text");
$oField->required = true;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");

$oField = ffField::factory($cm->oPage);
$oField->id = "alpha";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_alpha");
$oField->base_type = "Number";
$oField->default_value = new ffData($file_properties["alpha"], "Number");
$oField->widget = "slider";
$oField->min_val = "0";
$oField->max_val = "127";
$oField->step = "1";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");

$oField = ffField::factory($cm->oPage);
$oField->id = "resize";
$oField->container_class = "mode-dep";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_resize");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData($file_properties["resize"], "Number");
$oRecord->addContent($oField, "image");

                            
$oField = ffField::factory($cm->oPage);
$oField->id = "bgcolor";
$oField->container_class = "mode-dep resize-dep";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_background");
$oField->default_value = new ffData($file_properties["bgcolor"], "Text");
$oField->required = true;
$oField->widget = "colorpicker";
$oRecord->addContent($oField, "image");

$oField = ffField::factory($cm->oPage);
$oField->id = "transparent";
$oField->container_class = "mode-dep resize-dep";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_transparent");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData($file_properties["transparent"], "Number");
//$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");


$oField = ffField::factory($cm->oPage);
$oField->id = "frame_size";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_frame_size");
$oField->base_type = "Number";
$oField->default_value = new ffData($file_properties["frame_size"], "Number");
//$oField->required = true;
$oField->widget = "spinner";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");

$oField = ffField::factory($cm->oPage);
$oField->id = "frame_color";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_frame_color");
$oField->default_value = new ffData($file_properties["frame_color"], "Text");
$oField->required = true;
$oField->widget = "colorpicker";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");


$oField = ffField::factory($cm->oPage);
$oField->id = "format";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_extension");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("jpg"), new ffData(ffTemplate::_get_word_by_code("jpg"))),
                            array(new ffData("png"), new ffData(ffTemplate::_get_word_by_code("png")))
                       );
$oField->default_value = new ffData($file_properties["format"], "Text");
$oField->required = true;
if(!CM_SHOWFILES_OPTIMIZE)
	$oField->setWidthComponent(6);
$oRecord->addContent($oField, "image");   

if(!CM_SHOWFILES_OPTIMIZE) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "format_jpg_quality";
	$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_jpg_quality");
	$oField->base_type = "Number";
	$oField->default_value = new ffData($file_properties["format_jpg_quality"], "Number");
	$oField->required = true;
	$oField->widget = "slider";
	$oField->min_val = "0";
	$oField->max_val = "100";
	$oField->step = "5";
	$oField->setWidthComponent(6);
	$oRecord->addContent($oField, "image");
}

$oRecord->addTab("watermarkimage");
$oRecord->setTabTitle("watermarkimage", ffTemplate::_get_word_by_code("extras_image_modify_watermarkimage"));

$oRecord->addContent(null, true, "watermarkimage"); 
$oRecord->groups["watermarkimage"] = array(
                                 "title" => ffTemplate::_get_word_by_code("extras_image_modify_watermarkimage")
                                 , "cols" => 1
                                 , "tab" => "watermarkimage"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "wmk_image";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_image_cover");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->control_type = "file";
$oField->file_storing_path = DISK_UPDIR . "/watermarks";
$oField->file_temp_path = DISK_UPDIR . "/watermarks";
$oField->file_max_size = MAX_UPLOAD;
$oField->file_show_filename = true; 
$oField->file_full_path = false;
$oField->file_check_exist = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/watermarks/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/watermarks/[_FILENAME_]";
$oField->file_temp_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/watermarks/[_FILENAME_]";
$oField->file_temp_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/watermarks/[_FILENAME_]";
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
$oField->file_allowed_mime = array(
                                        "png"
                                        , "gif"
                                        , "jpeg"
                                        , "jpg"
                                );
$oField->default_value = new ffData($file_properties["wmk_image"], "Text"); 
//$oField->setWidthComponent(4);
$oRecord->addContent($oField, "watermarkimage");

$oField = ffField::factory($cm->oPage);
$oField->id = "wmk_mode";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_wmk_mode");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("crop"), new ffData(ffTemplate::_get_word_by_code("crop"))),
                            array(new ffData("proportional"), new ffData(ffTemplate::_get_word_by_code("proportional"))),
                            array(new ffData("stretch"), new ffData(ffTemplate::_get_word_by_code("stretch")))
                       );
$oField->multi_select_one_label = ffTemplate::_get_word_by_code("none");
$oField->default_value = new ffData($file_properties["wmk_mode"], "Text");
$oField->setWidthComponent(4);
$oRecord->addContent($oField, "watermarkimage");

$oField = ffField::factory($cm->oPage);
$oField->id = "wmk_alignment";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_image_align"); 
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
                            array(new ffData("top-left"), new ffData(ffTemplate::_get_word_by_code("top-left"))),
                            array(new ffData("top-middle"), new ffData(ffTemplate::_get_word_by_code("top-middle"))),
                            array(new ffData("top-right"), new ffData(ffTemplate::_get_word_by_code("top-right"))),
                            array(new ffData("middle-left"), new ffData(ffTemplate::_get_word_by_code("middle-left"))),
                            array(new ffData("center"), new ffData(ffTemplate::_get_word_by_code("center"))),
                            array(new ffData("middle-right"), new ffData(ffTemplate::_get_word_by_code("middle-right"))),
                            array(new ffData("bottom-left"), new ffData(ffTemplate::_get_word_by_code("bottom-left"))),
                            array(new ffData("bottom-middle"), new ffData(ffTemplate::_get_word_by_code("bottom-middle"))),
                            array(new ffData("bottom-right"), new ffData(ffTemplate::_get_word_by_code("bottom-right")))
						);
$oField->default_value = new ffData($file_properties["wmk_alignment"], "Text"); 
$oField->setWidthComponent(4);
$oRecord->addContent($oField, "watermarkimage");

$oField = ffField::factory($cm->oPage);
$oField->id = "wmk_alpha";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_wmk_alpha");
$oField->base_type = "Number";
$oField->default_value = new ffData($file_properties["wmk_alpha"], "Number");
$oField->widget = "slider";
$oField->min_val = "0";
$oField->max_val = "127";
$oField->step = "1";
$oField->setWidthComponent(4);
$oRecord->addContent($oField, "watermarkimage");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_image_dir";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_image_dir");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData($file_properties["enable_thumb_image_dir"], "Number");
//$oField->setWidthComponent(6);
$oRecord->addContent($oField, "watermarkimage");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_image_file";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_image_file");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData($file_properties["enable_thumb_image_file"], "Number");
//$oField->setWidthComponent(6);
$oRecord->addContent($oField, "watermarkimage");

$oRecord->addTab("watermarktext");
$oRecord->setTabTitle("watermarktext", ffTemplate::_get_word_by_code("extras_image_modify_watermarktext"));

$oRecord->addContent(null, true, "watermarktext"); 
$oRecord->groups["watermarktext"] = array(
                                 "title" => ffTemplate::_get_word_by_code("extras_image_modify_watermarktext")
                                 , "cols" => 1
                                 , "tab" => "watermarktext"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "word_size";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_word_size");
$oField->base_type = "Number";
$oField->default_value = new ffData($file_properties["word_size"], "Number");
$oField->widget = "spinner";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "watermarktext");

$oField = ffField::factory($cm->oPage);
$oField->id = "word_color";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_word_color");
$oField->default_value = new ffData($file_properties["word_color"], "Text");
$oField->required = true;
$oField->setWidthComponent(6);
$oField->widget = "colorpicker";
$oRecord->addContent($oField, "watermarktext");

/*
$oField = ffField::factory($cm->oPage);
$oField->id = "word_color";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_word_color");
$oField->default_value = new ffData($file_properties["word_color"], "Text");
$oField->required = true;
$oRecord->addContent($oField, "watermark");  */

$oField = ffField::factory($cm->oPage);
$oField->id = "word_type";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_word_type");
$oField->default_value = new ffData($file_properties["word_type"], "Text");
$oField->required = true;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "watermarktext");

$oField = ffField::factory($cm->oPage);
$oField->id = "word_align";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_word_align");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData("top-left"), new ffData(ffTemplate::_get_word_by_code("top-left"))),
                            array(new ffData("top-middle"), new ffData(ffTemplate::_get_word_by_code("top-middle"))),
                            array(new ffData("top-right"), new ffData(ffTemplate::_get_word_by_code("top-right"))),
                            array(new ffData("middle-left"), new ffData(ffTemplate::_get_word_by_code("middle-left"))),
                            array(new ffData("center"), new ffData(ffTemplate::_get_word_by_code("center"))),
                            array(new ffData("middle-right"), new ffData(ffTemplate::_get_word_by_code("middle-right"))),
                            array(new ffData("bottom-left"), new ffData(ffTemplate::_get_word_by_code("bottom-left"))),
                            array(new ffData("bottom-middle"), new ffData(ffTemplate::_get_word_by_code("bottom-middle"))),
                            array(new ffData("bottom-right"), new ffData(ffTemplate::_get_word_by_code("bottom-right")))
                       );
$oField->default_value = new ffData($file_properties["word_align"], "Text");
$oField->required = true;
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "watermarktext");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_word_dir";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_word_dir");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData($file_properties["enable_thumb_word_dir"], "Number");
//$oField->setWidthComponent(6);
$oRecord->addContent($oField, "watermarktext");

$oField = ffField::factory($cm->oPage);
$oField->id = "enable_thumb_word_file";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_word_file");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData($file_properties["enable_thumb_word_file"], "Number");
//$oField->setWidthComponent(6);
$oRecord->addContent($oField, "watermarktext");

$oRecord->addTab("advanced");
$oRecord->setTabTitle("advanced", ffTemplate::_get_word_by_code("extras_image_modify_advanced"));

$oRecord->addContent(null, true, "advanced"); 
$oRecord->groups["advanced"] = array(
                                 "title" => ffTemplate::_get_word_by_code("extras_image_modify_advanced")
                                 , "cols" => 1
                                 , "tab" => "advanced"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "max_upload";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_max_upload");
$oField->base_type = "Number";
$oField->default_value = new ffData("0", "Number");
$oField->widget = "spinner";
$oRecord->addContent($oField, "advanced");

$item_value[] = array(new ffData(FF_THEME_DIR . "/" . CM_DEFAULT_THEME . "/images/spacer.gif"), new ffData("spacer"));

$icon_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . CM_DEFAULT_THEME . "/images/icons/" . THEME_ICO . "/thumb/*");
if(is_array($icon_file) && count($icon_file)) {
    foreach($icon_file AS $real_file) {
        if(is_file($real_file)) {
            $relative_path = str_replace(FF_DISK_PATH, "", $real_file);
            
            $item_value[] = array(new ffData($relative_path), new ffData(ffGetFilename($relative_path)));
        }
    }
}

$oField = ffField::factory($cm->oPage);
$oField->id = "force_icon";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_force_icon");
$oField->extended_type = "Selection";
$oField->multi_pairs = $item_value;
$oField->default_value = new ffData("", "Text");
$oRecord->addContent($oField, "advanced");

$oField = ffField::factory($cm->oPage);
$oField->id = "allowed_ext";
$oField->label = ffTemplate::_get_word_by_code("extras_image_modify_allowed_ext");
$oField->default_value = new ffData("", "Text");
$oRecord->addContent($oField, "advanced");


$cm->oPage->addContent($oRecord);

$cm->oPage->tplAddJs("ff.cms.admin.image-modify");


function ExtrasImageModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
	    if(check_function("system_get_sections"))
			$block_type = system_get_block_type();	    
    
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT " . CM_TABLE_PREFIX . "showfiles_modes.last_update FROM " . CM_TABLE_PREFIX . "showfiles_modes WHERE " . CM_TABLE_PREFIX . "showfiles_modes.ID = " . $db->toSql($component->key_fields["ID"]->value) . ") 
                WHERE layout.ID_type IN (" . $db->toSql($block_type["virtual-gallery"]["ID"], "Number") . ", " . $db->toSql($block_type["gallery"]["ID"], "Number") . ", " . $db->toSql($block_type["publishing"]["ID"], "Number") . ")
                    ";
        $db->execute($sSQL);
		
		if (FF_ENABLE_MEM_SHOWFILES_CACHING) {
			ffCache::getInstance(CM_CACHE_ADAPTER)->clear("__showfiles_modes__");
		}
		
		if(CM_SHOWFILES_THUMB_IN_CACHE) {
			$relative_path = "/cache/" . CM_SHOWFILES_THUMB_PATH;
			
			if(check_function("fs_operation"))
        		xpurge_dir($relative_path);
		}
    }
}
