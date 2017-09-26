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

if (!AREA_PUBLISHING_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
     
$db = ffDB_Sql::factory();

$framework_css = $cm->oPage->framework_css;
$template_framework = $framework_css["name"];

$display_addnew = false;
if(!isset($_REQUEST["keys"]["ID"])) {
    if(isset($_REQUEST["field"])) {
        $copy_field = $_REQUEST["field"];
    } else {
        $display_addnew = true;
    }
}

$src_type = ($_REQUEST["src"]
    ? $_REQUEST["src"]
    : "vgallery"
);

switch($src_type) {
    case "anagraph":
        $src_table =  "anagraph";
        break;
    case "vgallery":
        $src_table =  "vgallery_nodes";
        break;
    default:
        $src_table = $src_type;
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "PublishingExtraFieldModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("publishing_fields_title"); 
$oRecord->src_table = "publishing_fields";
$oRecord->insert_additional_fields["ID_publishing"] = new ffData($_REQUEST["publishing"], "Number");
$oRecord->insert_additional_fields["ID_fields"] = new ffData($copy_field, "Number");
$oRecord->addEvent("on_do_action", "PublishingExtraFieldModify_on_do_action");
//$oRecord->addEvent("on_done_action", "PublishingExtraFieldModify_on_done_action");
$oRecord->buttons_options["print"]["display"] = false;

$oRecord->additional_fields["field_hash"] = new ffData("");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if($display_addnew) 
{
    $oField = ffField::factory($cm->oPage);
    $oField->id = "copy-from";
    $oField->label = ffTemplate::_get_word_by_code("publishing_fields_copy");
    $oField->base_type = "Number";
    $oField->source_SQL = "SELECT " . $src_type . "_fields.ID
                                    , IF(" . $src_type . "_fields.parent_thumb
                                        , CONCAT(" . $src_type . "_fields.name,' (', " . $src_type . "_fields.parent_thumb, ')')
                                        , " . $src_type . "_fields.name
                                    ) AS name
                                    , " . $src_type . "_type.name AS grp_name
                                FROM " . $src_type . "_fields 
                                    INNER JOIN " . $src_type . "_type ON " . $src_type . "_type.ID = " . $src_type . "_fields.ID_type
                                ORDER BY " . $src_type . "_fields.name";
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->actex_update_from_db = true;
    $oField->actex_group = "grp_name";
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);    
} 
else 
{
    if(isset($_REQUEST["keys"]["ID"])) {
        $sSQL = "SELECT publishing_fields.*
                FROM publishing_fields
                WHERE publishing_fields.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $copy_field = $db->getField("ID_fields", "Number", true);
        }
    }

    if(check_function("get_field_default"))
        $arrFieldData = get_field_default($src_type . "_fields", $copy_field, $field_default);

    $extended_type = $arrFieldData["extended_type_rev"][$arrFieldData["default"]["ID_extended_type"]];
    $is_resource = ($extended_type == "Upload"
        || $extended_type == "UploadImage"
        || $extended_type == "Image"
    );    
   
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_name");
    $oField->control_type = "label";
    $oField->default_value = new ffData($arrFieldData["default"]["name"]);
    $oField->data_type = "";
    $oField->store_in_db = false;
    $oField->setWidthComponent(array(6));
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "parent_thumb";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_parent");
    $oField->default_value = new ffData($arrFieldData["default"]["parent_thumb"]);
    $oField->setWidthComponent(array(6));
    $oRecord->addContent($oField);
	
	$img_setting_columns = array(4,4,4,4);
    if($is_resource) {
		if (strlen($framework_css_name)) {
			if (check_function("set_fields_grid_system")) {
				set_fields_grid_system($oRecord, array(
						"group" => null
						, "fluid" => false
						, "class" => false
						, "wrap" => false
						, "extra" => false
						, "image" => array(
							"prefix" => "settings_type_thumb"
							, "default_value" => array(
								$arrFieldData["default"]["settings_type_thumb"]
								, $arrFieldData["default"]["settings_type_thumb_md"]
								, $arrFieldData["default"]["settings_type_thumb_sm"]
								, $arrFieldData["default"]["settings_type_thumb_xs"]
							)
						)
					), $framework_css
				);
			}
			
			if($framework_css_name == "bootstrap" || $framework_css_name == "foundation") {
			    $img_setting_columns = array(6,6,6,6);
			}		
		}    
    } else {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "thumb_limit";
        $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_thumb_limit");
        $oField->base_type = "Number";
        $oField->default_value = new ffData($arrFieldData["default"]["thumb_limit"], "Number");
        $oField->setWidthComponent($img_setting_columns);
        $oRecord->addContent($oField);        
    }        
     
 	$oField = ffField::factory($cm->oPage);
    $oField->id = "enable_lastlevel";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_enable_lastlevel");
    $oField->base_type = "Number";
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    //$oField->actex_update_from_db = true;
    $oField->multi_pairs = array (
                                array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("no_link"))),
                                array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("to_detail_content")))
                           );
    if($is_resource)
        $oField->multi_pairs[] = array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("to_large_image")));

    $oField->actex_child = "display_view_mode_thumb";
    $oField->default_value = new ffData($arrFieldData["default"]["enable_lastlevel"], "Number");
    $oField->multi_select_one = false;
    $oField->setWidthComponent($img_setting_columns);
    $oRecord->addContent($oField); 

    $oField = ffField::factory($cm->oPage);
    $oField->id = "display_view_mode_thumb";
    $oField->container_class = "display_view_mode";
    $oField->label = ffTemplate::_get_word_by_code("admin_vgallery_type_display_view_mode");
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
	if(check_function("system_get_js_plugins"))
	    $oField->source_SQL = system_get_js_plugins("Number");

    $oField->actex_father = "enable_lastlevel";
    $oField->actex_related_field = "type";
    //$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/layout/extras/image/modify";
    //$oField->actex_dialog_edit_params = array("keys[ID]" => null);
    //$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
    //$oField->resources[] = "ExtrasImageModify";
    $oField->actex_update_from_db = true;
    $oField->actex_hide_empty = "all";
    $oField->default_value = new ffData($arrFieldData["default"]["display_view_mode_thumb"]);
    $oField->setWidthComponent($img_setting_columns);
    $oRecord->addContent($oField); 

    
    /**
    *  Field Container 
    */
	$oRecord->addContent(null, true, "FieldContainer"); 
	$oRecord->groups["FieldContainer"] = array(
	                                 "title" => ffTemplate::_get_word_by_code("publishing_modify_field_container")
	                              );
	if(check_function("set_fields_grid_system")) {
	    set_fields_grid_system($oRecord, array(
	            "group" => "FieldContainer"
	            , "fluid" => array(
	                "name" => "field_fluid_thumb"
	                , "prefix" => "field_grid_thumb"
	                , "one_field" => true
	                , "hide" => false
	                , "full_row" => true
	            )
	            , "class" => array(
                	"name" => "field_class_thumb"
	            )
	            , "wrap" => false
	        ), $framework_css
	    );
	}    
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_thumb_htmltag";
    $oField->container_class = "thumb_htmltag";
    $oField->base_type = "Number";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_thumb_htmltag");
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT 
                                vgallery_fields_htmltag.ID
                                , IF(vgallery_fields_htmltag.attr = ''
                                    , vgallery_fields_htmltag.tag
                                    , CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
                                ) AS name
                            FROM 
                                vgallery_fields_htmltag
                            [WHERE]
                            [HAVING]
                            ORDER BY vgallery_fields_htmltag.tag";
    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/htmltag/modify";
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryHtmlTagModify_confirmdelete";
    $oField->resources[] = "VGalleryHtmlTagModify";
    $oField->actex_update_from_db = true;
	$oField->multi_select_noone = true;
	$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("no");
	$oField->multi_select_noone_val = new ffData("-1", "Number");
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default_htmltag");
    $oField->default_value = new ffData($arrFieldData["default"]["ID_thumb_htmltag"], "Number");
    $oField->setWidthComponent(array(6));
    $oRecord->addContent($oField, "FieldContainer");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "custom_thumb_field";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_custom_thumb_field");
    $oField->extended_type = "Text";
    $oField->default_value = new ffData($arrFieldData["default"]["custom_thumb_field"]);
    $oField->setWidthComponent(array(6));
    $oRecord->addContent($oField, "FieldContainer"); 

    /**
    *  Field Settings 
    */
	$oRecord->addContent(null, true, "FieldSettings"); 
	$oRecord->groups["FieldSettings"] = array(
	                                 "title" => ffTemplate::_get_word_by_code("publishing_modify_field_settings")
	                              );
 	
	$oField = ffField::factory($cm->oPage);
    $oField->id = "enable_thumb_empty";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_enable_thumb_empty");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->default_value = new ffData($arrFieldData["default"]["enable_thumb_empty"], "Number");
    $oRecord->addContent($oField, "FieldSettings");
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_sort";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_enable_sort");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->default_value = new ffData($arrFieldData["default"]["enable_sort"], "Number");
    $oRecord->addContent($oField, "FieldSettings");

    $oField = ffField::factory($cm->oPage);
    $oField->id = "fixed_pre_content_thumb";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_fixed_pre_content");
    $oField->extended_type = "Text";
    $oField->default_value = new ffData($arrFieldData["default"]["fixed_pre_content_thumb"]);
    $oField->setWidthComponent(array(6));
    $oRecord->addContent($oField, "FieldSettings");    
        
    $oField = ffField::factory($cm->oPage);
    $oField->id = "fixed_post_content_thumb";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_fixed_post_content");
    $oField->extended_type = "Text";
    $oField->default_value = new ffData($arrFieldData["default"]["fixed_post_content_thumb"]);
    $oField->setWidthComponent(array(6));
    $oRecord->addContent($oField, "FieldSettings");     
	                                 
    /**
    *  Field Label 
    */
	$oRecord->addContent(null, true, "FieldLabel"); 
	$oRecord->groups["FieldLabel"] = array(
	                                 "title" => ffTemplate::_get_word_by_code("publishing_modify_field_label")
									 , "primary_field" => "enable_thumb_label"
	                              );
	                              
    $oField = ffField::factory($cm->oPage);
    $oField->id = "enable_thumb_label";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_enable_thumb_label");
    $oField->base_type = "Number";
    $oField->extended_type = "Boolean";
    $oField->control_type = "checkbox";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->default_value = new ffData($arrFieldData["default"]["enable_thumb_label"], "Number");
    //$oField->setWidthComponent(array(6));
    $oRecord->addContent($oField, "FieldLabel");
	                              
	if(check_function("set_fields_grid_system")) {
	    set_fields_grid_system($oRecord, array( 
	            "group" => "FieldLabel"
	            , "fluid" => array(
	                "name" => "label_fluid_thumb"
	                , "prefix" => "label_grid_thumb" 
	                , "one_field" => true
	                , "hide" => false
                    , "row" => false
	                , "full_row" => true
                    , "default_value" => new ffData("1", "Number")
	            )
	            , "class" => false
	            , "wrap" => false
	        ), $framework_css
	    );
	}   

            

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_label_thumb_htmltag";
    $oField->label = ffTemplate::_get_word_by_code("publishing_modify_fields_label_thumb_htmltag");
    $oField->base_type = "Number";
    $oField->widget = "actex";
	//$oField->widget = "activecomboex";
    $oField->source_SQL = "SELECT 
                                vgallery_fields_htmltag.ID
                                , IF(vgallery_fields_htmltag.attr = ''
                                    , vgallery_fields_htmltag.tag
                                    , CONCAT(vgallery_fields_htmltag.tag, ' (', vgallery_fields_htmltag.attr, ')')
                                ) AS name
                            FROM 
                                vgallery_fields_htmltag
                            [WHERE]
                            [HAVING]
                            ORDER BY vgallery_fields_htmltag.tag";
    $oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/content/vgallery" . "/htmltag/modify";
    $oField->actex_dialog_edit_params = array("keys[ID]" => null);
    $oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=VGalleryHtmlTagModify_confirmdelete";
    $oField->actex_update_from_db = true;
    $oField->resources[] = "VGalleryHtmlTagModify";  
	$oField->multi_select_noone = true;
	$oField->multi_select_noone_label = ffTemplate::_get_word_by_code("no");
	$oField->multi_select_noone_val = new ffData("-1", "Number");
	$oField->multi_select_one_label = ffTemplate::_get_word_by_code("default_htmltag");
    $oField->default_value = new ffData($arrFieldData["default"]["ID_label_thumb_htmltag"], "Number");
    //$oField->setWidthComponent(array(6));
    $oRecord->addContent($oField, "FieldLabel"); 
    
}

$cm->oPage->addContent($oRecord);



    
function PublishingExtraFieldModify_on_do_action($component, $action) { 
    $db = ffDB_Sql::factory();

    switch($action) {
        case "insert":
            $ret_url = $_REQUEST["ret_url"];
            if(isset($component->form_fields["copy-from"])) {
                ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "/modify?field=" . $component->form_fields["copy-from"]->getValue() . "&src=" . $_REQUEST["src"] . "&publishing=" . $_REQUEST["publishing"] . "&ret_url=" . urlencode($ret_url));
            }
        case "update":
        	if(isset($component->additional_fields["field_hash"])) {
				if(is_array($component->form_fields) && count($component->form_fields)) {
					foreach($component->form_fields AS $field_key => $field_value) {
						$str_to_hash .= $field_value->getValue();
					}
				}
				if(strlen($str_to_hash)) {
					$hash = substr(strtolower(preg_replace('/[0-9_\/]+/','',base64_encode(sha1($str_to_hash)))),0,8);

					$component->additional_fields["field_hash"] = new ffData($hash);
				}
        	}
        
            break;
        default:
    }
    return false;


}
