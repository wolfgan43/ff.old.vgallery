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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function get_field_by_extension(&$obj_page_field, $params = array(), $ext = null) {
    $default_params = array(
        "ID" => null
        , "name" => ""
        , "label" => ""
        , "display_label" => true
        , "placeholder" => false
        , "class" => ""
        , "group" => null
        , "extended_type" => ""
        , "ff_extended_type" => ""
        , "source_SQL" => ""
        , "multi_pairs" => null
        , "writable" => true
        , "hide" => false
        , "enable_tip" => false
        , "encode_entities" => true
        , "require" => false
        , "check_control" => null
        , "fixed_pre_content" => ""
        , "fixed_post_content" => ""
        , "disable_select_one" => false
        , "select_one_label" => ""
        , "val_min" => 0
        , "val_max" => 0
        , "val_step" => 0
        , "file_multi" => false
        , "file_max_upload" => MAX_UPLOAD
        , "file_allowed_ext" => ""
        , "file_storing_path" => ""
        , "file_saved_view_url" => ""
        , "file_saved_preview_url" => ""
        , "file_temp_path" => ""
        , "file_modify_path" => ""
        , "properties" => ""
        , "js" => ""
        , "framework_css" => array()
    );
    
    $params = array_replace($default_params, $params);

    $params["is_image"] = ($params["extended_type"] == "Image" 
    							|| $params["extended_type"] == "Upload" 
    							|| $params["extended_type"] == "UploadImage" 
    								? true 
    								: false
            			);
    $params["is_selection"] = ($params["extended_type"] == "Selection" 
    							|| $params["extended_type"] == "SelectionWritable" 
    							|| $params["extended_type"] == "ComboImage" 
    							|| $params["extended_type"] == "Option" 
    							|| $params["extended_type"] == "Autocomplete" 
    							|| $params["extended_type"] == "Group" 
    							|| $params["extended_type"] == "Autocompletetoken" 
    							|| $params["extended_type"] == "AutocompleteMulti" 
    							|| $params["extended_type"] == "MonoRelation" 
    							|| $params["extended_type"] == "MultiRelation" 
    								? true 
    								: false
    						);

    if (!strlen($params["ff_extended_type"])) {
        if ($params["extended_type"] == "Date" || $params["extended_type"] == "DateCombo") {
            $params["ff_extended_type"] = "Date";
        } elseif ($params["extended_type"] == "Boolean" || $params["extended_type"] == "Number") {
            $params["ff_extended_type"] = "Number";
        } else {
            $params["ff_extended_type"] = "Text";
        }
    }


    $obj_page_field->id = $params["ID"];
    if (is_array($params["class"]) && count($params["class"]))
        $obj_page_field->container_class = implode(" ", $params["class"]);
    else
        $obj_page_field->container_class = ffCommon_url_rewrite($params["name"]) . $params["hide"] . $params["class"];

    $obj_page_field->setWidthComponent($params["framework_css"]["component"]);

    if (strlen($params["label"]))
        $obj_page_field->user_vars["label"] = $params["label"];
    elseif (strlen($params["name"]))
        $obj_page_field->user_vars["label"] = ffTemplate::_get_word_by_code($params["name"]);

    if ($params["display_label"]) {
        $obj_page_field->label = $obj_page_field->user_vars["label"];
        $obj_page_field->display_label = $params["display_label"];
        $obj_page_field->setWidthLabel($params["framework_css"]["label"]);
    } else
        $obj_page_field->display_label = false;

    if ($params["placeholder"]) {
        if (is_numeric($params["placeholder"]))
            $obj_page_field->placeholder = $obj_page_field->user_vars["label"];
        else
            $obj_page_field->placeholder = $params["placeholder"];
    } elseif (!$params["display_label"] && $obj_page_field->user_vars["label"]) {
        $obj_page_field->placeholder = true;
    } else {
        $obj_page_field->placeholder = false;
    }

    if ($params["is_selection"]) {
        if ($obj_page_field->placeholder === true && $obj_page_field->user_vars["label"])
            $params["select_one_label"] = $obj_page_field->user_vars["label"];
        elseif ($obj_page_field->placeholder)
            $params["select_one_label"] = $obj_page_field->placeholder;
    }

    $obj_page_field->multi_select_one = !$params["disable_select_one"];
    if (strlen($params["select_one_label"]))
        $obj_page_field->multi_select_one_label = $params["select_one_label"];
    else
        $obj_page_field->multi_select_one_label = ffTemplate::_get_word_by_code("multi_select_one_label");

    $obj_page_field->user_vars["ID_field"] = $params["ID_field"];
    $obj_page_field->user_vars["name"] = $params["name"];
    $obj_page_field->user_vars["group_field"] = $params["group"];
    $obj_page_field->user_vars["extended_type"] = $params["extended_type"];
    $obj_page_field->user_vars["is_image"] = $params["is_image"];
    $obj_page_field->user_vars["is_selection"] = $params["is_selection"];
    if (isset($params["user_vars"]) && is_array($params["user_vars"]))
        $obj_page_field->user_vars = array_replace_recursive($obj_page_field->user_vars, $params["user_vars"]);

    $obj_page_field->properties = $params["properties"];

    if ($params["require"]) {
        $obj_page_field->required = true;
    }

    if (is_array($params["check_control"])) {
        foreach ($params["check_control"] AS $checl_control) {
            if (strlen($checl_control))
                $obj_page_field->addValidator($checl_control);
        }
    } elseif (strlen($params["check_control"]))
        $obj_page_field->addValidator($params["check_control"]);

    if (strlen($params["fixed_pre_content"])) {
        $obj_page_field->fixed_pre_content .= $params["fixed_pre_content"];
    }
    if (strlen($params["fixed_post_content"])) {
        $obj_page_field->fixed_post_content .= $params["fixed_post_content"];
    }

    if ($params["enable_tip"]) {
        $obj_page_field->properties["title"] = ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $params["name"]) . "_tip");
    }
    if (array_key_exists("default_value", $params))
        $obj_page_field->default_value = new ffData($params["default_value"], $params["ff_extended_type"]);
	
	$obj_page_field->encode_entities = $params["encode_entities"];
    
    if ($params["is_selection"]) {
        if (!isset($params["enable_actex"]))
            $params["enable_actex"] = false;

        if (!$params["source_SQL"] && $params["ID_vgallery_field"] > 0)
            $params["enable_actex"] = true;
    }
	

    switch ($params["extended_type"]) {
        case "SelectionWritable":
        case "Selection":
        case "Option":
        case "Autocomplete":
        case "ComboImage":
        case "AutocompleteWritable":
        case "AutocompleteMulti":
        case "Autocompletetoken":
        case "MonoRelation":
        case "MultiRelation":
            $obj_page_field->base_type = "Text";
            
            if (strlen($params["source_SQL"])) {
                $obj_page_field->source_SQL = $params["source_SQL"];
            } elseif (is_array($params["multi_pairs"])) {
                $obj_page_field->multi_pairs = $params["multi_pairs"];
			} 

            if ($params["writable"]) {
                if ($params["extended_type"] == "Option") {
                    $obj_page_field->control_type = "radio";
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->widget = "";
                } elseif ($params["extended_type"] == "AutocompleteWritable") {
                    $obj_page_field->control_type = "";
                    // $obj_page_field->extended_type = "Selection";
                    $obj_page_field->actex_update_from_db = true;

                    //$obj_page_field->widget = "actex";
                    //$obj_page_field->actex_autocomp = true;
                    $obj_page_field->widget = "autocomplete";
                    $obj_page_field->autocomplete_combo = true;
                    $obj_page_field->autocomplete_multi = false;
                    $obj_page_field->autocomplete_readonly = false;
                    $obj_page_field->autocomplete_minLength = 0;
                    if ($params["compare"])
                        $obj_page_field->autocomplete_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocomplete_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocomplete_compare = "name";
                } elseif ($params["extended_type"] == "Autocomplete") {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
                    $obj_page_field->actex_multi = false;
                
/*
                    $obj_page_field->control_type = "";
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->actex_update_from_db = true;

                    //$obj_page_field->widget = "actex";
                    //$obj_page_field->actex_autocomp = true;
                    $obj_page_field->widget = "autocomplete";
                    $obj_page_field->autocomplete_combo = true;
                    $obj_page_field->autocomplete_minLength = 0;
                    if ($params["compare"])
                        $obj_page_field->autocomplete_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocomplete_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocomplete_compare = "name";
*/
               /* } elseif ($params["extended_type"] == "AutocompleteMulti") {
                    $obj_page_field->control_type = "";
                    // $obj_page_field->extended_type = "Selection";
                    $obj_page_field->actex_update_from_db = true;

                    //$obj_page_field->widget = "actex";
                    //$obj_page_field->actex_autocomp = true;
                    $obj_page_field->widget = "autocomplete";
                    $obj_page_field->autocomplete_combo = true;
                    $obj_page_field->autocomplete_multi = true;
                    $obj_page_field->autocomplete_readonly = false;
                    $obj_page_field->autocomplete_minLength = 0;
                    if ($params["compare"])
                        $obj_page_field->autocomplete_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocomplete_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocomplete_compare = "name";*/
                } elseif ($params["extended_type"] == "Autocompletetoken") {
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->widget = "autocompletetoken";
                    $obj_page_field->autocompletetoken_minLength = 0;
                    $obj_page_field->autocompletetoken_theme = "";
                    $obj_page_field->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
                    $obj_page_field->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
                    $obj_page_field->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
                    $obj_page_field->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
                    $obj_page_field->autocompletetoken_combo = true;
                    if ($params["compare"])
                        $obj_page_field->autocompletetoken_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocompletetoken_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocompletetoken_compare_having = "name";
				} elseif($params["extended_type"] == "MonoRelation") {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
				} elseif($params["extended_type"] == "SelectionWritable") {//come monorelation
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
				} elseif($params["extended_type"] == "MultiRelation") {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
                    $obj_page_field->actex_multi = true;
				} elseif ($params["extended_type"] == "AutocompleteMulti") { //come multirelation                   
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
                    $obj_page_field->actex_multi = true;
                } else {
                    if ($params["enable_actex"]) {
                        $obj_page_field->extended_type = "String";
                        $obj_page_field->control_type = "combo";
                        $obj_page_field->widget = "actex"; //nn funziona actex
                        $obj_page_field->actex_update_from_db = true;
                    } else {
                        $obj_page_field->control_type = "combo";
                        $obj_page_field->extended_type = "Selection";
                        
                       // $obj_page_field->pre_process(true);
                       $obj_page_field->user_vars["need_preload"] = true;
                    }
                }
            } else {
                $obj_page_field->extended_type = "String";
                $obj_page_field->control_type = "label";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = ",";


            $obj_page_field->encode_entities = false;
            break;
        case "Group":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Selection";
            $obj_page_field->control_type = "input";

            if (!$params["writable"])
                $obj_page_field->properties["disabled"] = "disabled";

            $obj_page_field->widget = "checkgroup";
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = ",";

            if (strlen($params["source_SQL"])) {
                $obj_page_field->source_SQL = $params["source_SQL"];
            } elseif (is_array($params["multi_pairs"])) {
                $obj_page_field->multi_pairs = $params["multi_pairs"];
            }
            $obj_page_field->encode_entities = false;
            break;
        case "Text":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
			$obj_page_field->encode_entities = false;

            $obj_page_field->widget = "";
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
        case "TextBB":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";

            if ($params["writable"]) {
                $obj_page_field->control_type = "textarea";
                if (file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/tiny_mce/tiny_mce.js")) {
                    $obj_page_field->widget = "tiny_mce";
                    $obj_page_field->encode_entities = false;
                } else {
                    $obj_page_field->widget = "";
                }
            } else {
                $obj_page_field->control_type = "label";
                $obj_page_field->widget = "";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
        case "TextCK":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";

            if ($params["writable"]) {
                $obj_page_field->control_type = "textarea";
                if (file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
                    $obj_page_field->widget = "ckeditor";
                    $obj_page_field->encode_entities = false;
                } else {
                    $obj_page_field->widget = "";
                }
                $obj_page_field->ckeditor_group_by_auth = true;
            } else {
                $obj_page_field->control_type = "label";
                $obj_page_field->widget = "";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
        case "Boolean":
            $obj_page_field->base_type = "Number";
            $obj_page_field->extended_type = "Boolean";
            $obj_page_field->control_type = "checkbox";

            if (!$params["writable"])
                $obj_page_field->properties["disabled"] = "disabled";

            $obj_page_field->widget = "";
            $obj_page_field->unchecked_value = new ffData("0", "Number");
            $obj_page_field->checked_value = new ffData("1", "Number");
            $obj_page_field->grouping_separator = "";
            break;
        case "Date":
            $obj_page_field->base_type = "Date";
            $obj_page_field->extended_type = "Date";

            if ($params["writable"]) {
                $obj_page_field->control_type = "input";
                $obj_page_field->widget = "datepicker";
            } else {
                $obj_page_field->control_type = "label";
                $obj_page_field->widget = "";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";

            break;
        case "DateCombo":
        case "DateComboAge":
        case "DateComboBooking":
        case "DateComboInv":
            $obj_page_field->base_type = "Date";
            $obj_page_field->extended_type = "Date";

            if ($params["writable"]) {
                $obj_page_field->control_type = "input";
                $obj_page_field->widget = "datechooser";
                switch ($params["extended_type"]) {
                    case "DateComboAge":
                        $obj_page_field->datechooser_type_date = "age";
                        break;
                    case "DateComboBooking":
                        $obj_page_field->datechooser_type_date = "booking";
                        break;
                    case "DateComboInv":
                        $obj_page_field->datechooser_type_date = "mixedInv";
                        break;
                    default:
                        $obj_page_field->datechooser_type_date = "mixed";
                }
            } else {
                $obj_page_field->control_type = "label";
                $obj_page_field->widget = "";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
        case "Image":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "File";

            $obj_page_field->file_storing_path = (strlen($params["file_storing_path"]) ? $params["file_storing_path"] : DISK_UPDIR . "[" . $params["ID"] . "_VALUE]"
                    );
            $obj_page_field->file_temp_path = (strlen($params["file_temp_path"]) ? $params["file_temp_path"] : DISK_UPDIR . "/tmp"
                    );

            $obj_page_field->file_multi = $params["file_multi"];
            $obj_page_field->file_max_size = $params["file_max_upload"];
            if (strlen($params["file_allowed_ext"]))
                $obj_page_field->file_allowed_mime = explode(",", $params["file_allowed_ext"]);
            else
                $obj_page_field->file_allowed_mime = array();

            $obj_page_field->file_show_filename = true;
            $obj_page_field->file_full_path = true;
            $obj_page_field->file_check_exist = true;
            $obj_page_field->file_normalize = true;

            $obj_page_field->file_show_preview = true;

            $obj_page_field->file_saved_view_url = $params["file_saved_view_url"];
            $obj_page_field->file_saved_preview_url = $params["file_saved_preview_url"];

            /*
              $obj_page_field->file_saved_view_url = (strlen($params["file_saved_view_url"])
              ? $params["file_saved_view_url"]
              : FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]"
              );
              $obj_page_field->file_saved_preview_url = (strlen($params["file_saved_preview_url"])
              ? $params["file_saved_preview_url"]
              : FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]"
              );
             */
            if ($params["writable"]) {
                $obj_page_field->control_type = "file";

                $obj_page_field->file_show_delete = true;
                $obj_page_field->file_writable = false;

                $obj_page_field->widget = "kcfinder";
                if (check_function("set_field_uploader")) {
                    $obj_page_field = set_field_uploader($obj_page_field);
                }
            } else {
                $obj_page_field->control_type = "picture_no_link";

                $obj_page_field->file_show_delete = false;
                $obj_page_field->file_writable = false;

                $obj_page_field->widget = "";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
        case "Upload":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "File";

            $obj_page_field->file_storing_path = (strlen($params["file_storing_path"]) 
                                                    ? $params["file_storing_path"] 
                                                    : DISK_UPDIR . "[" . $params["ID"] . "_VALUE]"
                                                );
            $obj_page_field->file_temp_path = (strlen($params["file_temp_path"]) 
                                                ? $params["file_temp_path"] 
                                                : DISK_UPDIR . "/tmp"
                                            );

            $obj_page_field->file_multi = $params["file_multi"];
            $obj_page_field->file_max_size = $params["file_max_upload"];
            if (strlen($params["file_allowed_ext"]))
                $obj_page_field->file_allowed_mime = explode(",", $params["file_allowed_ext"]);
            else
                $obj_page_field->file_allowed_mime = array();

            $obj_page_field->file_show_filename = true;
            $obj_page_field->file_full_path = true;
            $obj_page_field->file_check_exist = true;
            $obj_page_field->file_normalize = true;

            $obj_page_field->file_show_preview = true;
            $obj_page_field->file_saved_view_url = $params["file_saved_view_url"];
            $obj_page_field->file_saved_preview_url = $params["file_saved_preview_url"];
            $obj_page_field->file_modify_path = $params["file_modify_path"];

            /*
              $obj_page_field->file_saved_view_url = (strlen($params["file_saved_view_url"])
              ? $params["file_saved_view_url"]
              : FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]"
              );
              $obj_page_field->file_saved_preview_url = (strlen($params["file_saved_preview_url"])
              ? $params["file_saved_preview_url"]
              : FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]"
              );
             */

            if ($params["writable"]) {
                $obj_page_field->control_type = "file";

                $obj_page_field->file_show_delete = true;
                $obj_page_field->file_writable = false;

                $obj_page_field->widget = "uploadify";
                if (check_function("set_field_uploader")) {
                    $obj_page_field = set_field_uploader($obj_page_field);
                }
            } else {
                $obj_page_field->control_type = "picture_no_link";

                $obj_page_field->file_show_delete = false;
                $obj_page_field->file_writable = false;

                $obj_page_field->widget = "";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
        case "UploadImage":
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "File";

            $obj_page_field->file_storing_path = (strlen($params["file_storing_path"]) ? $params["file_storing_path"] : DISK_UPDIR . "[" . $params["ID"] . "_VALUE]"
                    );
            $obj_page_field->file_temp_path = (strlen($params["file_temp_path"]) ? $params["file_temp_path"] : DISK_UPDIR . "/tmp"
                    );

            $obj_page_field->file_multi = $params["file_multi"];
            $obj_page_field->file_max_size = $params["file_max_upload"];
            if (strlen($params["file_allowed_ext"]))
                $obj_page_field->file_allowed_mime = explode(",", $params["file_allowed_ext"]);
            else
                $obj_page_field->file_allowed_mime = array();

            $obj_page_field->file_show_filename = true;
            $obj_page_field->file_full_path = true;
            $obj_page_field->file_check_exist = true;
            $obj_page_field->file_normalize = true;

            $obj_page_field->file_show_preview = true;
            $obj_page_field->file_saved_view_url = $params["file_saved_view_url"];
            $obj_page_field->file_saved_preview_url = $params["file_saved_preview_url"];
            $obj_page_field->file_modify_path = $params["file_modify_path"];

            /*
              $obj_page_field->file_saved_view_url = (strlen($params["file_saved_view_url"])
              ? $params["file_saved_view_url"]
              : FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]"
              );
              $obj_page_field->file_saved_preview_url = (strlen($params["file_saved_preview_url"])
              ? $params["file_saved_preview_url"]
              : FF_SITE_PATH . constant("CM_SHOWFILES") . "/thumb/[_FILENAME_]"
              );
             */

            if ($params["writable"]) {
                $obj_page_field->control_type = "file";

                $obj_page_field->file_show_delete = true;
                $obj_page_field->file_writable = false;

                $obj_page_field->widget = "kcuploadify";
                if (check_function("set_field_uploader")) {
                    $obj_page_field = set_field_uploader($obj_page_field);
                }
            } else {
                $obj_page_field->control_type = "picture_no_link";

                $obj_page_field->file_show_delete = false;
                $obj_page_field->file_writable = false;

                $obj_page_field->widget = "";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
        case "GMap": // String
            ///$obj_page_field->properties["style"]["width"] = "400px";
            // $obj_page_field->properties["style"]["height"] = "200px";
            $obj_page_field->widget = "gmap";
            $obj_page_field->gmap_draggable = true;
            $obj_page_field->gmap_start_zoom = 10;
            $obj_page_field->gmap_force_search = true;
            if (check_function("set_field_gmap")) {
                $obj_page_field = set_field_gmap($obj_page_field);
            }
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "String";
            $obj_page_field->control_type = "";
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            $type_value = "Text";
            break;
        case "Number":
            $obj_page_field->base_type = "Number";
            $obj_page_field->extended_type = "";
            $obj_page_field->user_vars["min"] = $params["val_min"];
            $obj_page_field->user_vars["max"] = $params["val_max"];
            $obj_page_field->user_vars["step"] = $params["val_step"];
            $obj_page_field->widget = "";

            if ($params["writable"]) {
                if ($params["val_min"] > 0) {
                    $obj_page_field->min_val = $params["val_min"];
                    if(!$obj_page_field->default_value)
                    	$obj_page_field->default_value = new ffData($obj_page_field->min_val, "Number");
                }
                if ($params["val_max"] > 0)
                    $obj_page_field->max_val = $params["val_max"];

                if ($params["val_min"] > 0 && $params["val_max"] > 0) {
                    $obj_page_field->widget = "slider";

                    if ($params["val_step"] > 0) {
                        $obj_page_field->properties["readonly"] = "readonly";
                        $obj_page_field->step = $params["val_step"];
                    } else {
                        $obj_page_field->step = 1;
                    }
                }
                $obj_page_field->control_type = "input";
            } else
                $obj_page_field->control_type = "label";


            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
            break;
		case "default":
			break;
        default: // String
            $obj_page_field->base_type = "Text";
            $obj_page_field->extended_type = "Text";
			$obj_page_field->encode_entities = false;
            if ($params["writable"])
                $obj_page_field->control_type = "input";
            else
                $obj_page_field->control_type = "label";

            $obj_page_field->widget = "";
            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = "";
    }


    switch ($ext) {
        case "vgallery":
            $res = get_field_by_extension_vgallery($obj_page_field, $params);

            break;
        case "form":
            $res = get_field_by_extension_form($obj_page_field, $params);
            break;
        case "register":
            $res = get_field_by_extension_register($obj_page_field, $params);
            break;
        case "search":
            $res = get_field_by_extension_search($obj_page_field, $params);
            break;
        case "cart":
            $res = get_field_by_extension_cart($obj_page_field, $params);
            break;
        default:
            $res = array(
                "js" => $js
                , "obj" => $obj_page_field
            );
    }

    if ($params["is_selection"] && !strlen($res["obj"]->source_SQL) && $res["obj"]->multi_pairs === null) {
        $res["obj"]->extended_type = "Text";
    }

    if ($res["obj"]->user_vars["need_preload"] && $res["obj"]->extended_type == "Selection")  {
        $res["obj"]->pre_process(true); 
    }

    return $res["js"];
    //return array("obj" => $res["obj"], "js" => $res["js"]);
}

function get_field_by_extension_form($obj_page_field, $params = array()) {
    $db = ffDB_Sql::factory();

    static $selection = array();

    if(check_function("ecommerce_get_schema"))
		$schema_ecommerce = ecommerce_get_schema();	 

    $default_params = array(
        "custom" => array(
            "name" => ""
            , "class" => ""
        )
        , "send_mail" => false
        , "enable_in_mail" => true
        , "unic_value" => false
        , "preload_by_db" => null
        , "preload_by_domclass" => ""
        , "ID_selection" => 0
        , "ID_vgallery_field" => 0
        , "disable_free_input" => false
        , "show_price_in_label" => false
        , "form" => array(
            "ID" => 0
            , "name" => ""
            , "params" => ""
            , "enable_dynamic_cart" => false
            , "enable_dynamic_cart_advanced" => false
            , "enable_ecommerce" => false
        )
        , "type" => ""
        , "price_isset" => false
        , "qta" => 1
        , "price" => 0
        , "vat" => 0
        , "weight" => 0
        , "path" => "/mod/form" //modules va in conflitto con CM_MODULES_PATH
        , "prefix" => "form-"
    );

    $params = array_replace($default_params, $params);

    $source_SQL_key = "";
    if ($params["is_selection"]) {
        $string_params = "?";
        if (isset($params["father_name"])) {
            $string_params .= "&father_name=" . $params["father_name"];
        }
        if (isset($params["father"])) {
            $string_params .= "&father=" . $params["father"];
        }
        if (isset($params["child"])) {
            $string_params .= "&child=" . $params["father"];
        }
        if (isset($params["child_name"])) {
            $string_params .= "&child_name=" . $params["child_name"];
        }
        switch ($params["selectionSource"]) {
            case "anagraph":

                break;
            case "user":

                break;
            case "vgallery":
                if ($params["ID_vgallery_field"] > 0) {
                    $sSQL_selection_where = "";
                    $params["enable_actex"] = true;

                    if ($params["ID_vgallery_field"] > 0) {
                        if (strlen($sSQL_selection_where))
                            $sSQL_selection_where .= " OR ";
                        $sSQL_selection_where .= "vgallery_fields.ID = " . $db->toSql($params["ID_vgallery_field"], "Number");
                    }

                    $params["source_SQL"] = "(
                                            SELECT DISTINCT
                                                vgallery_rel_nodes_fields.description AS nameID
                                                , vgallery_rel_nodes_fields.description  AS name
                                                " . (AREA_SHOW_ECOMMERCE && strlen($params["type"]) ? " , '' AS qta
                                                        , '' AS price
                                                        , '' AS vat
                                                        , '' AS weight" : ""
                            ) . "
                                                , vgallery_fields.`order_thumb` AS `order`
                                           FROM vgallery_rel_nodes_fields
                                                INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                           WHERE 
                                                (   
                                                    $sSQL_selection_where
                                                )
                                                " . ($params["field"]["lang"] ? " AND " . $params["field"]["lang"] . "_rel_nodes_fields.`" . $params["field"]["lang"] . "` = " . $db->toSql(LANGUAGE_INSET_ID, "Text") : ""
                            ) . "
                                           [AND] [WHERE]
                                           [HAVING]
                                           [ORDER] [COLON] `order`, name
                                           [LIMIT]
                                       )";
                }
                break;
            case "city":
                $params["enable_actex"] = true;
                $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/city" . $string_params;
                break;
            case "province":
                $params["enable_actex"] = true;
                $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/province" . $string_params;
                break;
            case "region":
                $params["enable_actex"] = true;
                $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/region" . $string_params;
                break;
            case "state":
                $params["enable_actex"] = true;
                $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state" . $string_params;
                break;
            default:
                $params["source_SQL"] .= "(
                                           SELECT DISTINCT
                                                module_form_fields_selection_value.name AS nameID
                                                , module_form_fields_selection_value.name AS name
                                                , module_form_fields_selection_value.`order` AS `order`
                                           FROM module_form_fields_selection_value 
                                           WHERE " . ($params["ID_selection"]
                                           		? "module_form_fields_selection_value.ID_selection = " . $db->toSql($params["ID_selection"], "Number")
                                           		: "module_form_fields_selection_value.ID_form_fields = " . $db->toSql($params["ID"], "Number")
                                           ) . "
                                           [AND] [WHERE]
                                           [HAVING]
                                           [ORDER] [COLON] `order`, name
                                           [LIMIT]
                                       )";
                break;
        }

        if (strlen($params["source_SQL"]) && (AREA_SHOW_ECOMMERCE && $params["form"]["enable_ecommerce"] && strlen($params["type"]))) {
            $selection_value = array();
            $selection_user_vars = null;
            $source_SQL_key = md5($params["source_SQL"]);

            $db->query(str_replace(
            				array("[WHERE]"	, "[OR]"	, "[AND]"	, "[HAVING]"	, "[HAVING_OR]"	, "[HAVING_AND]"	, "[ORDER]"		, "[COLON]"	, "[LIMIT]")
            				, array(""		, ""		, ""		, ""			, ""			, ""				, " ORDER BY"	, ""		, "")
            				, $params["source_SQL"]
            			));
            if ($db->nextRecord()) {
                $old_qta = 0;
                do {
                    if (AREA_SHOW_ECOMMERCE && $params["form"]["enable_ecommerce"] && strlen($params["type"])) {
                        switch ($params["type"]) {
                            case "multiplier":
                                $nameID = $db->getField("qta", "Number", true);

                                $old_qta = $db->getField("qta", "Number", true);
                                $nameValue = $nameID;

                                if ($params["form"]["enable_dynamic_cart"]) {
                                    if (strlen($obj_page_field->properties["data-qta"]))
                                        $obj_page_field->properties["data-qta"] .= "|";

                                    $obj_page_field->properties["data-qta"] .= $db->getField("qta", "Number", true);

                                    if (strlen($obj_page_field->properties["data-price"]))
                                        $obj_page_field->properties["data-price"] .= "|";

                                    $obj_page_field->properties["data-price"] .= $db->getField("price", "Number", true);
                                }
                                break;
                            case "pricelist":
                            case "price":
                            default:
                                $nameID = $db->getField("nameID", "Text", true);
                                $nameValue = ffTemplate::_get_word_by_code($nameID);

                                if ($params["type"] == "price" && $params["show_price_in_label"]) {
                                    $nameValue .= " " . $db->getField("price", "Number")->getValue("Currency", LANGUAGE_INSET) . " " . $schema_ecommerce["symbol"];
                                }

                                if ($params["form"]["enable_dynamic_cart"]) {
                                    if (strlen($obj_page_field->properties["data-price"]))
                                        $obj_page_field->properties["data-price"] .= "|";

                                    $obj_page_field->properties["data-price"] .= $db->getField("price", "Number", true);

                                    if ($params["form"]["enable_dynamic_cart_advanced"]) {
                                        if (count($selection_value))
                                            $obj_page_field->properties["data-price-basic"] .= "|";

                                        $obj_page_field->properties["data-price-basic"] .= $db->getField("price_basic", "Number", true);

                                        if (count($selection_value))
                                            $obj_page_field->properties["data-qstep"] .= "|";

                                        $obj_page_field->properties["data-qstep"] .= $db->getField("qta", "Number", true);

                                        if (count($selection_value))
                                            $obj_page_field->properties["data-qfrom"] .= "|";

                                        $obj_page_field->properties["data-qfrom"] .= $db->getField("sum_qta_from", "Text", true);

                                        if (count($selection_value))
                                            $obj_page_field->properties["data-price-nostep"] .= "|";

                                        $obj_page_field->properties["data-price-nostep"] .= $db->getField("price_nostep", "Number", true);

                                        if (count($selection_value))
                                            $obj_page_field->properties["data-weight"] .= "|";

                                        $obj_page_field->properties["data-weight"] .= $db->getField("weight", "Number", true);
                                    }
                                }
                        }

                        $selection_value[] = array(new ffData($nameID), new ffData($nameValue));

                        $selection_user_vars["selection_price"][$nameID]["qta"] = ($db->getField("qta", "Number", true) > 0 ? $db->getField("qta", "Number", true) : 1);
                        $selection_user_vars["selection_price"][$nameID]["price"] = $db->getField("price", "Number", true);
                        $selection_user_vars["selection_price"][$nameID]["vat"] = $db->getField("vat", "Number", true);
                        $selection_user_vars["selection_price"][$nameID]["weight"] = $db->getField("weight", "Number", true);
                        if (strlen($db->getField("sum_qta_from", "Text", true)))
                            $selection_user_vars["selection_price"][$nameID]["sum_qta_from"] = explode(",", $db->getField("sum_qta_from", "Text", true));

                        $selection_user_vars["selection_price"][$nameID]["price_basic"] = $db->getField("price_basic", "Number", true);
                        $selection_user_vars["selection_price"][$nameID]["price_nostep"] = $db->getField("price_nostep", "Number", true);
                    } else
                        $selection_value[] = array(new ffData($db->getField("nameID")->getValue()), new ffData(ffTemplate::_get_word_by_code($db->getField("name")->getValue())));
                } while ($db->nextRecord());
            }

            $selection[$source_SQL_key]["multi_pairs"] = $selection_value;
            if ($selection_user_vars !== null)
                $selection[$source_SQL_key]["user_vars"] = $selection_user_vars;
        }
    }

    $sum_pfrom = array();
    if ($params["sum_pfrom"]) {
        $sum_pfrom = array_merge($sum_pfrom, explode(",", $params["sum_pfrom"]));
    }
    if (isset($params["father_name"]) && $params["enable_actex"]) {
        $obj_page_field->actex_father = $params["father_name"];
        if (isset($params["father"]))
            $obj_page_field->actex_related_field = "ID_" . $params["father"];
    }
    if (isset($params["child_name"]) && $params["enable_actex"]) {
        $obj_page_field->actex_child = $params["child_name"];
        if (isset($params["child"]))
            $obj_page_field->actex_related_field = "ID_" . $params["father"];
    }

    $obj_page_field->container_class = /* $params["prefix"] . */ $obj_page_field->container_class . (strlen($params["custom"]["class"]) ? " " . $params["custom"]["class"] : "");
    $obj_page_field->label = $obj_page_field->label . $params["label_ecommerce"];
    $obj_page_field->user_vars["type"] = (array_search($params["ID"], $sum_pfrom) !== false ? "pfrom" : $params["type"]);
    $obj_page_field->user_vars["send_mail"] = $params["send_mail"];
    $obj_page_field->user_vars["enable_in_mail"] = $params["enable_in_mail"];
    $obj_page_field->user_vars["unic_value"] = $params["unic_value"];

    if ($params["price_isset"]) {
        $obj_page_field->user_vars["qta"] = ($params["qta"] > 0 ? $params["qta"] : 1);
        $obj_page_field->user_vars["price"] = $params["price"];
        $obj_page_field->user_vars["vat"] = $params["vat"];
        $obj_page_field->user_vars["weight"] = $params["weight"];
    }

    if (strpos($params["ecommerce_class"], "dynamic-price") !== false) {
        if (array_search($params["ID"], $sum_pfrom) !== false) {
            $obj_page_field->class = "dynamic-pfrom";
        } else {
            $obj_page_field->class = $params["ecommerce_class"];
        }
    } elseif (strlen($params["sum_from"])) {
        $obj_page_field->class = "dynamic-value";
    } else {
        $obj_page_field->class = $params["ecommerce_class"];
    }

    switch ($params["extended_type"]) {
        case "SelectionWritable":
        case "Selection":
            if ($params["writable"]) {
                if ($params["enable_actex"]) {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex"; //nn funziona actex
                    $obj_page_field->actex_update_from_db = true;
                } else {
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->extended_type = "Selection";
                    //$obj_page_field->pre_process(true);
                    $obj_page_field->user_vars["need_preload"] = true;
                }
            }
        case "Option":
        case "Autocomplete":
        case "AutocompleteWritable":
        case "AutocompleteMulti":
        case "Autocompletetoken":
        case "MonoRelation":
        case "MultiRelation":
        case "Group":
            if (strlen($source_SQL_key) && array_key_exists($source_SQL_key, $selection)) {
                $obj_page_field->multi_pairs = $selection[$source_SQL_key]["multi_pairs"];
                if (array_key_exists("user_vars", $selection[$source_SQL_key]) && is_array($selection[$source_SQL_key]["user_vars"]))
                    $obj_page_field->user_vars = array_replace($obj_page_field->user_vars, $selection[$source_SQL_key]["user_vars"]);
            } elseif (strlen($params["source_SQL"])) {
                $obj_page_field->source_SQL = $params["source_SQL"];
            } elseif (is_array($params["multi_pairs"])) {
                $obj_page_field->multi_pairs = $params["multi_pairs"];
            }
            break;
        case "Text":
            if ($params["custom"]["name"]) {
                $params["js"] .= '
                    jQuery("#' . $params["form"]["ID"] . '_' . $params["ID"] . '").text(jQuery("#' . $params["form"]["ID"] . '_' . $params["ID"] . '_customdata").html());
                    jQuery("#' . $params["form"]["ID"] . '_data input.insert").attr("onclick", \' jQuery("#' . $params["form"]["ID"] . '_' . $params["ID"] . '").text(jQuery("#' . $params["form"]["ID"] . '_' . $params["ID"] . '_customdata").html()); \' + jQuery("#' . $params["form"]["ID"] . '_data input.insert").attr("onclick"));
                    jQuery("#' . $params["form"]["ID"] . '_' . $params["ID"] . '_customdata input").keyup(function() {
                        jQuery(this).attr("value", jQuery(this).val());
                    });
                    jQuery("#' . $params["form"]["ID"] . '_' . $params["ID"] . '_customdata textarea").keyup(function() {
                        jQuery(this).text(jQuery(this).text());
                    });
                ';
                $obj_page_field->properties["style"]["display"] = "none";
                $obj_page_field->encode_entities = false;

                if (isset($_REQUEST[$params["form"]["ID"] . '_' . $params["ID"]])) {
                    $real_custom_data = $_REQUEST[$params["form"]["ID"] . '_' . $params["ID"]];
                } else {
                    $real_custom_data = $params["custom"]["name"];
                }
                $obj_page_field->fixed_post_content = '<div id="' . $params["form"]["ID"] . '_' . $params["ID"] . '_customdata">' . $real_custom_data . '</div>';
            } else {
                if (!$params["writable"]) {
                    if (strlen($params["form"]["name"]))
                        $obj_page_field->default_value = new ffData(get_word_by_code("form_" . preg_replace('/[^a-zA-Z0-9]/', '', $params["form"]["name"]) . "_text_" . $params["form"]["params"][0]), "Text");
                    $obj_page_field->properties["readonly"] = "readonly";
                }
            }
            break;
        case "TextBB":
            break;
        case "TextCK":
            break;
        case "Boolean":
            break;
        case "Date":
            break;
        case "DateCombo":
            break;
        case "Image":
        case "Upload":
        case "UploadImage":
            $obj_page_field->file_storing_path = DISK_UPDIR . $params["path"] . $params["form"]["name"] . "/[form-ID_VALUE]";
            $obj_page_field->file_temp_path = DISK_UPDIR . $params["path"] . $params["form"]["name"];
            break;
        case "Number":
            if (AREA_SHOW_ECOMMERCE && $params["form"]["enable_ecommerce"] && strlen($params["type"] == "multiplier")) {
                $db->query($params["source_SQL"]);
                if ($db->nextRecord()) {
                    do {
                        $nameID = $db->getField("qta", "Number", true) > 0 ? $db->getField("qta", "Number", true) : 1;
                        $selection_price[$nameID]["price"] = $db->getField("price", "Number", true);
                        $selection_price[$nameID]["weight"] = $db->getField("weight", "Number", true);

                        if ($params["form"]["enable_dynamic_cart"]) {
                            if (strlen($obj_page_field->properties["data-qta"]))
                                $obj_page_field->properties["data-qta"] .= "|";

                            $obj_page_field->properties["data-qta"] .= $db->getField("qta", "Number", true);

                            if (strlen($obj_page_field->properties["data-price"]))
                                $obj_page_field->properties["data-price"] .= "|";

                            $obj_page_field->properties["data-price"] .= $db->getField("price", "Number", true);
                        }
                    } while ($db->nextRecord());
                }

                if ($selection_price !== null)
                    $obj_page_field->user_vars["selection_price"] = $selection_price;
            }
            break;
        default: // String
    }

    if(isset($_GET[ffCommon_url_rewrite($params["name"])]) && strlen($_GET[ffCommon_url_rewrite($params["name"])])) {
        if($params["ff_extended_type"] == "Text")
            $_GET[ffCommon_url_rewrite($params["name"])] = str_replace("-", " ", $_GET[ffCommon_url_rewrite($params["name"])]);

        $obj_page_field->default_value = new ffData($_GET[ffCommon_url_rewrite($params["name"])], $params["ff_extended_type"]);
    } else {
        switch($params["preload_by_db"]) {
            case "reference":
            case "avatar":
            case "name":
            case "surname":
            case "email":
            case "tel":
            case "billreference":
            case "billcf":
            case "billpiva":
            case "billaddress":
            case "billcap":
            case "billprovince":
            case "billtown":
            case "billstate":
            case "shippingreference":
            case "shippingaddress":
            case "shippingcap":
            case "shippingprovince":
            case "shippingtown":
            case "shippingstate":
                if(get_session("UserID") != MOD_SEC_GUEST_USER_NAME && check_function("get_user_data")) {
                    $obj_page_field->default_value = new ffData(get_user_data($params["preload_by_db"]), $params["ff_extended_type"]);
                }
            default:
        }
    }                        
    
    return array("obj" => $obj_page_field, "js" => $params["js"]);
}

function get_field_by_extension_vgallery($obj_page_field, $params = array()) {
    $db = ffDB_Sql::factory();

    if(check_function("ecommerce_get_schema"))
		$schema_ecommerce = ecommerce_get_schema();	 
    
    $default_params = array(
                "data_type"                 => "data"
                , "data_source"             => ""
                , "data_limit"              => ""
                , "vgallery_name"           => ""
                , "ID_vgallery_node"        => 0
                , "vgallery_node_name"      => ""
                , "vgallery_node_parent"    => ""
                , "vgallery_permalink"      => ""
            );

    $params = array_replace($default_params, $params);
    //print_r($params);

    if ($params["is_selection"]) {
        if (!$params["source_SQL"] && !$params["multi_pairs"]) {
            $params["enable_actex"] = true;

            switch ($params["data_type"]) {
                case "relationship":
                    $obj_page_field->autocomplete_compare = "";
                    $obj_page_field->autocomplete_compare_having = "display_name";
                    $obj_page_field->encode_entities = false;
                    switch ($params["data_source"]) {
                        case "anagraph":
                            $params["source_SQL"] = "
                                SELECT anagraph.ID
                                    , CONCAT(anagraph.name, ' ', anagraph.surname) AS display_name
                                    , IF(anagraph.avatar = ''
                                        , '" . cm_getClassByFrameworkCss("noimg", "icon-tag", "2x") . " ' 
                                        , CONCAT('<img src=\"" . CM_SHOWFILES . "/32x32', anagraph.avatar, '\" />')  
                                    ) AS image 
                                FROM anagraph
                                WHERE 1
                                [AND] [WHERE]  
                                [HAVING]                                                                      
                                [ORDER] [COLON] display_name
                                [LIMIT]";                        
                            break;
                        default:
                            $obj_page_field->actex_attr["url"] = "permalink";
	                        
	                        $params["source_SQL"] = "
	                            SELECT vgallery_nodes.ID 
									, IF(vgallery.enable_place
										, CONCAT(
											vgallery_nodes.meta_title_alt
											, IFNULL((
												SELECT CONCAT(' - ', " . FF_SUPPORT_PREFIX . "city.name)
												FROM " . FF_SUPPORT_PREFIX . "city
												WHERE " . FF_SUPPORT_PREFIX . "city.ID = vgallery_nodes.ID_place
											), '')
										)
										, vgallery_nodes.meta_title_alt
									) AS meta_title_alt
	                                , vgallery_nodes.permalink
	                            FROM vgallery_nodes 
	                                INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
								WHERE " . ($params["data_source"]
	                            	? "vgallery.name = " . $db->toSql($params["data_source"])
	                            	: "1"
	                            ) . "	                                
	                           [AND] [WHERE]
	                           [HAVING]
	                           [ORDER] [COLON] vgallery_nodes.meta_title_alt
	                           [LIMIT]";                     	
                    }
                    break;
                case "form":
                    $params["source_SQL"] = "SELECT module_form.name AS ID
                                                        , IF(module_form.display_name = ''
                                                            , REPLACE(module_form.name, '-', ' ')
                                                            , module_form.display_name
                                                        ) AS name
                                                    FROM module_form
                                                    [WHERE]
                                                    [HAVING]
                                                    [ORDER] [COLON] module_form.name
                                                    [LIMIT]";
                    if (AREA_MODULES_SHOW_MODIFY) {
                        $obj_page_field->actex_dialog_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/modules/form/extra?clonename=" . urlencode(ucwords(str_replace("-", " ", $params["vgallery_node_name"])));
                        $obj_page_field->actex_dialog_edit_params = array("name" => null);
                        $obj_page_field->actex_dialog_delete_url = $cm->oPage->site_path . VG_SITE_RESTRICTED . "/modules/form/extra?frmAction=FormExtraFieldModify_confirmdelete";
                        $obj_page_field->extended_type = "String";
                        $obj_page_field->widget = "actex";
						//$obj_page_field->widget = "activecomboex";
                        $obj_page_field->actex_update_from_db = true;
                        $obj_page_field->resources[] = "FormExtraFieldModify";
                    }

                    break;
                case "media":
                    if ($params["extended_type"] != "Image" || $params["extended_type"] != "Upload" || $params["extended_type"] != "UploadImage"
                    ) {
                        $params["extended_type"] = "Upload";
                    }
                    break;
                case "table.alt":
                default:
                    /* if(!strlen($params["select"]["data_source"]) || is_numeric($params["select"]["data_source"])) {
                      $params["select"]["table"] = $params["src"]["type"] . "_fields_selection_value";
                      $params["select"]["sWhere"] = " AND " . $params["src"]["type"] . "_fields_selection_value.ID_selection = " . $db->toSql($params["select"]["data_source"], "Number");
                      $params["select"]["field"] = "name";

                      } else {
                      $params["select"]["sWhere"] = "";
                      $params["select"]["table"] = $params["select"]["data_source"];
                      if(strlen($params["select"]["data_limit"]) && $params["select"]["data_limit"] != "null" && strpos($params["select"]["data_limit"], "name") === false) {
                      if(strpos($params["select"]["data_limit"], ",") === false) {
                      $params["select"]["field"] = "`" . $params["select"]["data_limit"] . "`";
                      } else {
                      $params["select"]["field"] = "CONCAT(`" . str_replace(",", "`, ' ',`", $params["select"]["data_limit"]) . "`)";
                      }
                      } else {
                      $params["select"]["field"] = "name";
                      }
                      } */
                    /* if(is_array($schema["db"]["selection_data_source"][$params["select"]["table"]]) && array_key_exists("query", $schema["db"]["selection_data_source"][$params["select"]["table"]])) {
                      $params["source_SQL"] = "SELECT ID, name
                      FROM (" . str_replace("[DISPLAY_VALUE]", $params["select"]["field"], $schema["db"]["selection_data_source"][$params["select"]["table"]]["query"]) . ") AS tbl_src
                      [WHERE] [HAVING] [ORDER] [LIMIT]";
                      } else { */
                    if(isset($params["select"]["having"])) {
                        $obj_page_field->autocomplete_compare = "";
                        $obj_page_field->autocomplete_compare_having = "display_name";
                    } 
                    if(array_key_exists("query", $params)) {
                        $params["source_SQL"] = $params["query"];
					} else {
		                if(strlen($params["select"]["data_limit"]) && strlen($params["select"]["data_source"])) {
		                    $url_params = "?data-limit=" . $params["select"]["data_limit"] . "&data-source=" . $params["select"]["data_source"];
		                }

		                switch ($params["select"]["data_source"]) {
		                    case "search_tags":
                    			$params["enable_actex"] = true;
		                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/tags" . $url_params;
		                        break; 
		                    case FF_SUPPORT_PREFIX . "city":
                    			$params["enable_actex"] = true;
		                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/city" . $url_params;
		                        break; 
		                    case FF_SUPPORT_PREFIX . "province":
                    			$params["enable_actex"] = true;
		                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/province" . $url_params;
		                        break; 
		                    case FF_SUPPORT_PREFIX . "region":
                    			$params["enable_actex"] = true;
		                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/region" . $url_params;
		                        break; 
		                    case FF_SUPPORT_PREFIX . "state":
                    			$params["enable_actex"] = true;
		                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state" . $url_params;
		                        break; 
		                    default:
		                    	$obj_page_field->autocomplete_readonly = !$params["select"]["writable"];
		                    	$obj_page_field->autocomplete_compare = $params["select"]["field"];

                        		$params["source_SQL"] = "SELECT DISTINCT " . ($params["select"]["ID"]
		                                                                ? $params["select"]["ID"]
		                                                                : "`" . $params["select"]["table"] . "`.ID"
		                                                            ) . " AS ID
		                                                            , " . $params["select"]["field"] . " AS display_name
		                                                FROM `" . $params["select"]["table"] . "` 
		                                                WHERE 1 " . $params["select"]["sWhere"] . "
		                                                [AND] [WHERE]
		                                                GROUP BY ID
		                                                [HAVING]
		                                                [ORDER] [COLON] display_name
		                                                [LIMIT]";
		                }            
					
					}
                    //}                                        
                    break;
            }

            if ($params["source_SQL"])
                $obj_page_field->source_SQL = $params["source_SQL"];
        }
    }

    $obj_page_field->user_vars["data_type"] = $params["data_type"];
    $obj_page_field->user_vars["data_source"] = $params["data_source"];
    $obj_page_field->user_vars["data_limit"] = $params["data_limit"];

    if (isset($obj_page_field->user_vars["code_lang"]))
        $obj_page_field->container_class = $obj_page_field->container_class . (strlen($obj_page_field->container_class) ? " " : "") . "mlang " . strtolower($obj_page_field->user_vars["code_lang"]);

    switch ($params["extended_type"]) {
        case "SelectionWritable":
        case "Selection":
            if ($params["enable_actex"]) {
                $obj_page_field->extended_type = "String";
                $obj_page_field->control_type = "combo";
                $obj_page_field->widget = "actex"; //nn funziona actex
                $obj_page_field->actex_update_from_db = true;
            } else {
                $obj_page_field->control_type = "combo";
                $obj_page_field->extended_type = "Selection";
               // $obj_page_field->pre_process(true);
               $obj_page_field->user_vars["need_preload"] = true;
            }
        case "Option":
        case "Autocomplete":
        case "AutocompleteWritable":
        case "AutocompleteMulti":
        case "Autocompletetoken":
        case "MonoRelation":
        case "MultiRelation":
        case "Group":
            break;
        case "Text":
            break;
        case "TextBB":
            break;
        case "TextCK":
            break;
        case "Boolean":
            break;
        case "Date":
            break;
        case "DateCombo":
            break;
        case "Image":
        case "Upload":
        case "UploadImage":
            break;
        case "Number":
            break;
        default:
            if ($params["data_type"] == "google.docs") {
                if (check_function("get_webservices")) {
                    $services_params = get_webservices("google.docs");

                    if ($services_params["enable"] && strlen($services_params["email"]) && strlen($services_params["password"])) {
                        $google_docs_auth = file_get_contents("https://www.google.com/accounts/ClientLogin?accountType=HOSTED_OR_GOOGLE&Email=" . $services_params["email"] . "&Passwd=" . $services_params["password"] . "&service=" . $params["data_source"] . "&source=" . APPID);
                        $strAuth = substr($google_docs_auth, strpos($google_docs_auth, "Auth") + 5);
                    }
                }

                switch ($params["data_source"]) {
                    case "wise":
                        $google_docs_service = "spreadsheets.google.com";
                        break;
                    default:
                }

                $strAuth = "";

                if (strlen($strAuth)) {
                    $google_docs_mode .= "/ccc";
                } else {
                    $google_docs_mode .= "/pub";
                }

                $google_docs_lang = strtolower(substr(LANGUAGE_INSET, 0, -1));

                $google_docs_token = $strAuth;

                $obj_page_field->display_label = false;

                //setJsRequest("gdocsEdit", "system");
                //if(check_function("system_set_js"))
                //system_set_js($cm->oPage, "/", false);
                $tpl = ffTemplate::factory(get_template_cascading("/", "google.docs.edit.html", "/vgallery"));
                $tpl->load_file("google.docs.edit.html", "main");
                $tpl->set_var("service", $google_docs_service);
                $tpl->set_var("mode", $google_docs_mode);
                $tpl->set_var("lang", $google_docs_lang);
                $tpl->set_var("token", $google_docs_token);

                if (check_function("get_vgallery_information_by_lang")) {
                    $tpl->set_var("title", get_vgallery_information_by_lang(null, $ID_vgallery_nodes, array("meta_title_alt" => "meta_title"), "System", $ID_vgallery));
                }
                //$tpl->rpparse("main", false);
                $obj_page_field->fixed_post_content = $tpl->rpparse("main", false);
            }
    }
    
    
    return array("obj" => $obj_page_field, "js" => $params["js"]);
}

function get_field_by_extension_register($obj_page_field, $params = array()) {
    $db = ffDB_Sql::factory();

    if(check_function("ecommerce_get_schema"))
		$schema_ecommerce = ecommerce_get_schema();	 
    
    $default_params = array(
        "custom" => array(
            "name" => ""
            , "class" => ""
        )
        , "send_mail" => false
        , "enable_in_mail" => true
        , "unic_value" => false
        , "preload_by_db" => null
        , "preload_by_domclass" => ""
        , "ID_selection" => 0
        , "disable_free_input" => false
        , "show_price_in_label" => false
        , "form" => null
        , "type" => ""
        , "price_isset" => false
        , "qta" => 1
        , "price" => 0
        , "vat" => 0
        , "weight" => 0
        , "path" => "/mod/register" //modules va in conflitto con CM_MODULES_PATH
        , "prefix" => "register-"
    );
    $params = array_replace($default_params, $params);
    
    if ($params["is_selection"]) {
        if (!$params["source_SQL"]) {
            $srv_data = array();
            //$string_params = "?type=selection";
            if (isset($params["father_name"])) {
                $srv_data[] = "father_name=" . $params["father_name"];
            }
            if (isset($params["father"])) {
                $srv_data[] = "father=" . $params["father"];
            }
            if (isset($params["child"])) {
                $srv_data[] = "child=" . $params["father"];
            }
            if (isset($params["child_name"])) {
                $srv_data[] = "child_name=" . $params["child_name"];
            }
            if(is_array($srv_data) && count($srv_data))
                $string_params = "?" . implode("&", $srv_data);

            if(strlen($params["selection_data_source"])) {
                
                if(!is_numeric($params["selection_data_source"])) {
                    switch ($params["selection_data_source"]) {
                        case "search_tags":
                            $params["enable_actex"] = true;
                            $obj_page_field->actex_service = FF_SITE_PATH . "/srv/tags";
                            break;
                        case FF_SUPPORT_PREFIX . "city":
                            $params["enable_actex"] = true;
                            $params["extended_type"] = "Selection";
                            $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/city" . $string_params;
                            break;
                        case FF_SUPPORT_PREFIX . "province":
                            $params["enable_actex"] = true;
                            $params["extended_type"] = "Selection";
                            $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/province" . $string_params;
                            break;
                        case FF_SUPPORT_PREFIX . "region":
                            $params["enable_actex"] = true;
                            $params["extended_type"] = "Selection";
                            $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/region" . $string_params;
                            break;
                        case FF_SUPPORT_PREFIX . "state":
                            $params["enable_actex"] = true;
                            $params["extended_type"] = "Selection";
                            $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state" . $string_params;
                            break;
                        default:
                            break;
                    }
                } else {
                /*
                if($params["extended_type"] == "ComboImage") {
                   $params["source_SQL"] .= "(
                            SELECT DISTINCT
                                 anagraph_fields_selection_value.name AS nameID
                                 , anagraph_fields_selection_value.name AS name
                            FROM anagraph_fields_selection_value 
                            WHERE anagraph_fields_selection_value.ID_selection = " . $db->toSql($params["selection_data_source"], "Number") . "
                            ORDER BY name 
                        )"; 
                    
                } else {
                    $params["source_SQL"] .= "(
                                SELECT DISTINCT
                                     anagraph_fields_selection_value.name AS nameID
                                     , anagraph_fields_selection_value.name AS name
                                     , anagraph_fields_selection_value.`order` AS `order`
                                FROM anagraph_fields_selection_value 
                                WHERE anagraph_fields_selection_value.ID_selection = " . $db->toSql($params["selection_data_source"], "Number") . "
                                [AND] [WHERE]
                                [HAVING]
                                ORDER BY `order`, name 
                                [LIMIT]
                            )";
                    }*/
                $params["source_SQL"] .= "(
                            SELECT DISTINCT
                                 anagraph_fields_selection_value.name AS nameID
                                 , anagraph_fields_selection_value.name AS name
                                 , anagraph_fields_selection_value.`order` AS `order`
                            FROM anagraph_fields_selection_value 
                            WHERE anagraph_fields_selection_value.ID_selection = " . $db->toSql($params["selection_data_source"], "Number") . "
                            [AND] [WHERE]
                               [HAVING]
                               [ORDER] [COLON] `order`, name
                               [LIMIT]
                        )";

                }
            } elseif(strlen($params["selectionSource"])) {
                switch ($params["selectionSource"]) {
                    case "city":
                        $params["enable_actex"] = true;
                        $params["extended_type"] = "Selection";
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/city" . $string_params;
                        break;
                    case "province":
                        $params["enable_actex"] = true;
                        $params["extended_type"] = "Selection";
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/province" . $string_params;
                        break;
                    case "region":
                        $params["enable_actex"] = true;
                        $params["extended_type"] = "Selection";
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/region" . $string_params;
                        break;
                    case "state":
                        $params["enable_actex"] = true;
                        $params["extended_type"] = "Selection";
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state" . $string_params;
                        break;
                    default:
                        $params["enable_actex"] = true;
                        $params["extended_type"] = "Selection";
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/selection/" . $params["selectionSource"]; //. "?type=selection";
                        break;
                }
            }  
                    
            if ($params["source_SQL"]) 
                $obj_page_field->source_SQL = $params["source_SQL"];
            
        }
    }

    if (isset($params["father_name"]) && $params["enable_actex"]) {
        $obj_page_field->actex_father = $params["father_name"];
        if (isset($params["father"]))
            $obj_page_field->actex_related_field = "ID_" . $params["father"];
    }
    if (isset($params["child_name"]) && $params["enable_actex"]) {
        $obj_page_field->actex_child = $params["child_name"];
        if (isset($params["child"]))
            $obj_page_field->actex_related_field = "ID_" . $params["father"];
    }

    $obj_page_field->container_class = /* $params["prefix"] . */ $obj_page_field->container_class . (strlen($params["custom"]["class"]) ? " " . $params["custom"]["class"] : "");
    $obj_page_field->label = $obj_page_field->label;
    $obj_page_field->user_vars["send_mail"] = $params["send_mail"];
    $obj_page_field->user_vars["enable_in_mail"] = $params["enable_in_mail"];
    $obj_page_field->user_vars["unic_value"] = $params["unic_value"];

    switch ($params["extended_type"]) {
        case "SelectionWritable":
        case "Selection":
            if ($params["enable_actex"]) {
                $obj_page_field->extended_type = "String";
                $obj_page_field->control_type = "combo";
                $obj_page_field->widget = "actex"; //nn funziona actex
                $obj_page_field->actex_update_from_db = true;
            } else {
                $obj_page_field->control_type = "combo";
                $obj_page_field->extended_type = "Selection";
                //$obj_page_field->pre_process(true);
                $obj_page_field->user_vars["need_preload"] = true;
            }        
        case "Option":
        case "Autocomplete":
        case "AutocompleteWritable":
        case "Autocompletetoken":
        case "Group":
            break;
        case "Text":
            break;
        case "TextBB":
            break;
        case "TextCK":
            break;
        case "Boolean":
            break;
        case "Date":
            break;
        case "DateCombo":
            break;
        case "Image":
        case "Upload": 
        case "UploadImage":
            $obj_page_field->file_storing_path = DISK_UPDIR . $params["path"] . $params["form"]["name"] . "/[form-ID_VALUE]";
            $obj_page_field->file_temp_path = DISK_UPDIR . $params["path"] . $params["form"]["name"];
            break;
        case "Number":
            break; 
        case "ComboImage": 
            $obj_page_field->widget = "imagepicker";
            $obj_page_field->control_type = "combo";
            $obj_page_field->extended_type = "Selection"; 
            break;
        default: // String
    }
    
    if(isset($_GET[ffCommon_url_rewrite($params["name"])]) && strlen($_GET[ffCommon_url_rewrite($params["name"])])) {
        if($params["ff_extended_type"] == "Text")
            $_GET[ffCommon_url_rewrite($params["name"])] = str_replace("-", " ", $_GET[ffCommon_url_rewrite($params["name"])]);
    
        $obj_page_field->default_value = new ffData($_GET[ffCommon_url_rewrite($params["name"])], $params["ff_extended_type"]);
    } else {
        switch($params["preload_by_db"]) {
            case "reference":
            case "avatar":
            case "name":
            case "surname":
            case "email":
            case "tel":
            case "billreference":
            case "billcf":
            case "billpiva":
            case "billaddress":
            case "billcap":
            case "billprovince":
            case "billtown":
            case "billstate":
            case "shippingreference":
            case "shippingaddress":
            case "shippingcap":
            case "shippingprovince":
            case "shippingtown":
            case "shippingstate":
                if(get_session("UserID") != MOD_SEC_GUEST_USER_NAME && check_function("get_user_data")) {
                    $obj_page_field->default_value = new ffData(get_user_data($params["preload_by_db"]), $params["ff_extended_type"]);
                }
            default:
        }
    }                        
    
    return array("obj" => $obj_page_field, "js" => $params["js"]);
}

function get_field_by_extension_search($obj_page_field, $params = array()) {
    $db = ffDB_Sql::factory();

    if(check_function("ecommerce_get_schema"))
		$schema_ecommerce = ecommerce_get_schema();	 
    
    $default_params = array(
        "custom" => array(
            "name" => ""
            , "class" => ""
        )
        , "send_mail" => false
        , "enable_in_mail" => true
        , "unic_value" => false
        , "preload_by_db" => null
        , "preload_by_domclass" => ""
        , "ID_fields" => 0
        , "ID_vgallery_field" => 0
        , "disable_free_input" => false
        , "show_price_in_label" => false
        , "form" => null
        , "type" => ""
        , "price_isset" => false
        , "qta" => 1
        , "price" => 0
        , "vat" => 0
        , "weight" => 0
        , "path" => "/mod/search" //modules va in conflitto con CM_MODULES_PATH
        , "prefix" => "search-"
    );

    $params = array_replace($default_params, $params);

    if ($params["is_selection"]) {
        $enable_actex = false;

        if ($params["area"] === "anagraph") {
            $sSQL = "SELECT selection_data_source
                        FROM anagraph_fields
                        WHERE ID = " . $db->toSql($params["ID_fields"], "Number");
            $db->query($sSQL);
            if ($db->nextRecord()) {
                $selection_data_source = $db->getField("selection_data_source", "Text", true);
                
                $url_params = "?voce=name";  
                if(strlen($params["data_limit"]) && strlen($params["data_source"])) {
                    $url_params .= "&data-limit=" . $params["data_limit"] . "&data-source=" . $params["data_source"];
                    
                }
                switch ($selection_data_source) {
                    case "search_tags":
                        $enable_actex = true;
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/tags" . $url_params;
                        break; 
                    case FF_SUPPORT_PREFIX . "city":
                        $enable_actex = true;
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/city" . $url_params;
                        break; 
                    case FF_SUPPORT_PREFIX . "province":
                        $enable_actex = true;
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/province" . $url_params;
                        break; 
                    case FF_SUPPORT_PREFIX . "region":
                        $enable_actex = true;
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/region" . $url_params;
                        break; 
                    case FF_SUPPORT_PREFIX . "state":
                        $enable_actex = true;
                        $obj_page_field->actex_service = FF_SITE_PATH . "/srv/place/state" . $url_params;
                        break; 
                    default:
                        $params["source_SQL"] = "SELECT DISTINCT 
                                                        anagraph_fields_selection_value.name AS nameID
                                                        , anagraph_fields_selection_value.name AS name
                                                        , anagraph_fields_selection_value.`order` AS `order`
                                                    FROM anagraph_fields_selection_value 
                                                    WHERE anagraph_fields_selection_value.ID_selection = " . $db->toSql($selection_data_source, "Number") . "
                                                    [AND] [WHERE]
                                                       [HAVING]
                                                       [ORDER] [COLON] `order`, name
                                                       [LIMIT]
                                                    ";
                        break;
                }
            }
		} else {
            $params["source_SQL"] = "SELECT DISTINCT 
                                            vgallery_fields_selection_value.name AS nameID
                                            , vgallery_fields_selection_value.name AS name
                                            , vgallery_fields_selection_value.`order` AS `order`
                                        FROM vgallery_fields_selection_value 
                                        	INNER JOIN vgallery_fields ON vgallery_fields.selection_data_source = vgallery_fields_selection_value.ID_selection
                                        WHERE vgallery_fields.ID = " . $db->toSql($params["ID_fields"], "Number") . "
                                        [AND] [WHERE]
                                           [HAVING]
                                           [ORDER] [COLON] `order`, name
                                           [LIMIT]
                                        ";        
        }
    }



    $obj_page_field->container_class = /* $params["prefix"] . */ $obj_page_field->container_class . (strlen($params["custom"]["class"]) ? " " . $params["custom"]["class"] : "");
    $obj_page_field->label = $obj_page_field->label . $params["label_ecommerce"];
    $obj_page_field->user_vars["send_mail"] = $params["send_mail"];
    $obj_page_field->user_vars["enable_in_mail"] = $params["enable_in_mail"];
    $obj_page_field->user_vars["unic_value"] = $params["unic_value"];





    switch ($params["extended_type"]) {
        case "SelectionWritable":
        case "Selection":
        case "Option":
        case "Autocomplete":
        case "ComboImage":
        case "AutocompleteWritable":
        case "AutocompleteMulti":
        case "Autocompletetoken":
        case "MonoRelation":
        case "MultiRelation":  
            $obj_page_field->base_type = "Text";

            if ($params["writable"]) {
                if ($params["extended_type"] == "Option") {
                    $obj_page_field->control_type = "radio";
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->widget = "";
                } elseif ($params["extended_type"] == "AutocompleteWritable") {
                    $obj_page_field->control_type = "";
                    // $obj_page_field->extended_type = "Selection";
                    $obj_page_field->actex_update_from_db = true;

                    //$obj_page_field->widget = "actex";
                    //$obj_page_field->actex_autocomp = true;
                    $obj_page_field->widget = "autocomplete";
                    $obj_page_field->autocomplete_combo = true;
                    $obj_page_field->autocomplete_multi = false;
                    $obj_page_field->autocomplete_readonly = false;
                    $obj_page_field->autocomplete_minLength = 0;
                    if ($params["compare"])
                        $obj_page_field->autocomplete_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocomplete_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocomplete_compare = "name";
                } elseif ($params["extended_type"] == "Autocomplete") {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
                    $obj_page_field->actex_multi = false;
                
/*                    $obj_page_field->control_type = "";
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->actex_update_from_db = true;

                    //$obj_page_field->widget = "actex";
                    //$obj_page_field->actex_autocomp = true;
                    $obj_page_field->widget = "autocomplete";
                    $obj_page_field->autocomplete_combo = true;
                    $obj_page_field->autocomplete_minLength = 0;
                    if ($params["compare"])
                        $obj_page_field->autocomplete_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocomplete_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocomplete_compare = "name";
*/
                /*} elseif ($params["extended_type"] == "AutocompleteMulti") {
                    $obj_page_field->control_type = "";
                    // $obj_page_field->extended_type = "Selection";
                    $obj_page_field->actex_update_from_db = true;

                    //$obj_page_field->widget = "actex";
                    //$obj_page_field->actex_autocomp = true;
                    $obj_page_field->widget = "autocomplete";
                    $obj_page_field->autocomplete_combo = true;
                    $obj_page_field->autocomplete_multi = true;
                    $obj_page_field->autocomplete_readonly = false;
                    $obj_page_field->autocomplete_minLength = 0;
                    if ($params["compare"])
                        $obj_page_field->autocomplete_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocomplete_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocomplete_compare = "name";*/
                } elseif ($params["extended_type"] == "Autocompletetoken") {
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->widget = "autocompletetoken";
                    $obj_page_field->autocompletetoken_minLength = 0;
                    $obj_page_field->autocompletetoken_theme = "";
                    $obj_page_field->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
                    $obj_page_field->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
                    $obj_page_field->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
                    $obj_page_field->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
                    $obj_page_field->autocompletetoken_combo = true;
                    if ($params["compare"])
                        $obj_page_field->autocompletetoken_compare = $params["compare"];
                    elseif ($params["compare_having"])
                        $obj_page_field->autocompletetoken_compare_having = $params["compare_having"];
                    else
                        $obj_page_field->autocompletetoken_compare_having = "name";
				} elseif($params["extended_type"] == "MonoRelation") {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
				} elseif($params["extended_type"] == "SelectionWritable") {//come monorelation
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
				} elseif($params["extended_type"] == "MultiRelation") {
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
                    $obj_page_field->actex_multi = true;
				} elseif ($params["extended_type"] == "AutocompleteMulti") { //come multirelation                   
                    $obj_page_field->extended_type = "String";
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->widget = "actex";
                    $obj_page_field->actex_update_from_db = true;
                    $obj_page_field->actex_autocomp = true;
                    $obj_page_field->actex_multi = true;
                } else {
                    if ($params["enable_actex"]) {
                        $obj_page_field->extended_type = "String";
                        $obj_page_field->control_type = "combo";
                        $obj_page_field->widget = "actex"; //nn funziona actex
                        $obj_page_field->actex_update_from_db = true;
                    } else {
                        $obj_page_field->control_type = "combo";
                        $obj_page_field->extended_type = "Selection";
                        //$obj_page_field->pre_process(true);
                        $obj_page_field->user_vars["need_preload"] = true;
                    }
                }
            } else {
                $obj_page_field->extended_type = "String";
                $obj_page_field->control_type = "label";
            }

            $obj_page_field->unchecked_value = new ffData("");
            $obj_page_field->checked_value = new ffData("");
            $obj_page_field->grouping_separator = ",";

            if (strlen($params["source_SQL"])) {
                $obj_page_field->source_SQL = $params["source_SQL"];
            } elseif (is_array($params["multi_pairs"])) {
                $obj_page_field->multi_pairs = $params["multi_pairs"];
            }
            $obj_page_field->encode_entities = false;
            break;
        case "Group":
            if (strlen($source_SQL_key) && array_key_exists($source_SQL_key, $selection)) {
                $obj_page_field->multi_pairs = $selection[$source_SQL_key]["multi_pairs"];
                if (array_key_exists("user_vars", $selection[$source_SQL_key]) && is_array($selection[$source_SQL_key]["user_vars"]))
                    $obj_page_field->user_vars = array_replace($obj_page_field->user_vars, $selection[$source_SQL_key]["user_vars"]);
            } else {
                $obj_page_field->source_SQL = $params["source_SQL"];
            }
            break;
        case "Date":
            break;
        case "DateCombo":
            break;
        case "Text":
        case "TextBB":
        case "TextCK":
        case "Boolean":
        case "Image":
        case "Upload":
        case "UploadImage":
        case "Number":
        default: // String
            break;
    }

    if(isset($_GET[ffCommon_url_rewrite($params["name"])]) && strlen($_GET[ffCommon_url_rewrite($params["name"])])) {
        if($params["ff_extended_type"] == "Text")
            $_GET[ffCommon_url_rewrite($params["name"])] = str_replace("-", " ", $_GET[ffCommon_url_rewrite($params["name"])]);
    
        $obj_page_field->default_value = new ffData($_GET[ffCommon_url_rewrite($params["name"])], $params["ff_extended_type"]);
    } else {
        switch($params["preload_by_db"]) {
            case "reference":
            case "avatar":
            case "name":
            case "surname":
            case "email":
            case "tel":
            case "billreference":
            case "billcf":
            case "billpiva":
            case "billaddress":
            case "billcap":
            case "billprovince":
            case "billtown":
            case "billstate":
            case "shippingreference":
            case "shippingaddress":
            case "shippingcap":
            case "shippingprovince":
            case "shippingtown":
            case "shippingstate":
                if(get_session("UserID") != MOD_SEC_GUEST_USER_NAME && check_function("get_user_data")) {
                    //$obj_page_field->default_value = new ffData(get_user_data($params["preload_by_db"]), $params["ff_extended_type"]);
                }
            default:
        }
    }                        

    return array("obj" => $obj_page_field, "js" => $params["js"]);
}


function get_field_by_extension_cart($obj_page_field, $params = array()) {
    $db = ffDB_Sql::factory();

    if(check_function("ecommerce_get_schema"))
		$schema_ecommerce = ecommerce_get_schema();	 

    $default_params = array(
    	"custom" => array(
            "name" => ""
            , "class" => ""
        )
        , "ID_selection" => 0
        , "show_price_in_label" => true
        , "price" => 0
        , "vat" => 0
        , "prefix" => "cart-"
    );
    $params = array_replace($default_params, $params);

    $obj_page_field->data_type = "";
	$obj_page_field->store_in_db = false;
    
	if($obj_page_field->default_value)
		$obj_page_field->value = $obj_page_field->default_value;
    
    if ($params["is_selection"]) {
        if (!$params["source_SQL"] && $params["ID_selection"]) {
            $params["source_SQL"] .= "(
                        SELECT ecommerce_order_addit_field_selection_value.ID
							, ecommerce_order_addit_field_selection_value.name AS name
						FROM ecommerce_order_addit_field_selection_value 
						WHERE ecommerce_order_addit_field_selection_value.ID_selection = " . $db->toSql($params["ID_selection"], "Number") . "
                        [AND] [WHERE]
                        [HAVING]
						[ORDER] [COLON] ecommerce_order_addit_field_selection_value.`order`, ecommerce_order_addit_field_selection_value.name
                        [LIMIT]
                    )";
            
            $obj_page_field->source_SQL = $params["source_SQL"];
        }
    }

    if (isset($params["father_name"]) && $params["enable_actex"]) {
        $obj_page_field->actex_father = $params["father_name"];
        if (isset($params["father"]))
            $obj_page_field->actex_related_field = "ID_" . $params["father"];
    }
    if (isset($params["child_name"]) && $params["enable_actex"]) {
        $obj_page_field->actex_child = $params["child_name"];
        if (isset($params["child"]))
            $obj_page_field->actex_related_field = "ID_" . $params["father"];
    }

    $obj_page_field->container_class = /* $params["prefix"] . */ $obj_page_field->container_class . (strlen($params["custom"]["class"]) ? " " . $params["custom"]["class"] : "");
    $obj_page_field->label = $obj_page_field->label;
    if($params["show_price_in_label"] && $params["price"])
    	$obj_page_field->label .= " " . $params["price"] . $schema_ecommerce["symbol"];

    switch ($params["extended_type"]) {
        case "SelectionWritable":
        case "Selection":
            if ($params["enable_actex"]) {
                $obj_page_field->extended_type = "String";
                $obj_page_field->control_type = "combo";
                $obj_page_field->widget = "actex"; //nn funziona actex
                $obj_page_field->actex_update_from_db = true;
            } else {
                $obj_page_field->control_type = "combo";
                $obj_page_field->extended_type = "Selection";
                //$obj_page_field->pre_process(true);
                $obj_page_field->user_vars["need_preload"] = true;
            }        
        case "Option":
        case "Autocomplete":
        case "AutocompleteWritable":
        case "Autocompletetoken":
        case "Group":
            break;
        case "Text":
            break;
        case "TextBB":
            break;
        case "TextCK":
            break;
        case "Boolean":
            break;
        case "Date":
            break;
        case "DateCombo":
            break;
        case "Image":
        case "Upload": 
        case "UploadImage":
            $obj_page_field->file_storing_path = DISK_UPDIR . $params["path"] . $params["form"]["name"] . "/[form-ID_VALUE]";
            $obj_page_field->file_temp_path = DISK_UPDIR . $params["path"] . $params["form"]["name"];
            break;
        case "Number":
            break; 
        case "ComboImage": 
            $obj_page_field->widget = "imagepicker";
            $obj_page_field->control_type = "combo";
            $obj_page_field->extended_type = "Selection"; 
            break;
        default: // String
    }
    
    if(isset($_GET[ffCommon_url_rewrite($params["name"])]) && strlen($_GET[ffCommon_url_rewrite($params["name"])])) {
        if($params["ff_extended_type"] == "Text")
            $_GET[ffCommon_url_rewrite($params["name"])] = str_replace("-", " ", $_GET[ffCommon_url_rewrite($params["name"])]);
    
        $obj_page_field->default_value = new ffData($_GET[ffCommon_url_rewrite($params["name"])], $params["ff_extended_type"]);
    } else {
        switch($params["preload_by_db"]) {
            case "reference":
            case "avatar":
            case "name":
            case "surname":
            case "email":
            case "tel":
            case "billreference":
            case "billcf":
            case "billpiva":
            case "billaddress":
            case "billcap":
            case "billprovince":
            case "billtown":
            case "billstate":
            case "shippingreference":
            case "shippingaddress":
            case "shippingcap":
            case "shippingprovince":
            case "shippingtown":
            case "shippingstate":
                if(get_session("UserID") != MOD_SEC_GUEST_USER_NAME && check_function("get_user_data")) {
                    $obj_page_field->default_value = new ffData(get_user_data($params["preload_by_db"]), $params["ff_extended_type"]);
                }
            default:
        }
    }                        
    
    return array("obj" => $obj_page_field, "js" => $params["js"]);
}
