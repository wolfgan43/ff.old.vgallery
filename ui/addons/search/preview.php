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

if (!MODULE_SHOW_CONFIG) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();
$db_selection = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);


$db->query("SELECT module_search.*
                        FROM 
                            module_search
                        WHERE 
                            module_search.name = " . $db->toSql(new ffData(basename($cm->real_path_info)))) . "
                            ";
if($db->nextRecord()) {
    $ID_search = $db->getField("ID")->getValue();
    $search_name = $db->getField("name")->getValue();
    $tpl_search_path = $db->getField("tpl_search_path")->getValue();
}

$db->query("SELECT 
                            vgallery_fields.*
                            , vgallery_type.name AS type
                            , extended_type.name AS extended_type
                            , module_search_fields_group.name AS `group_field`
                        FROM 
                            module_search_vgallery
                            INNER JOIN vgallery_fields ON vgallery_fields.ID = module_search_vgallery.ID_vgallery_fields
                            INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                            INNER JOIN extended_type ON extended_type.ID = module_search_vgallery.ID_extended_type
                            LEFT JOIN module_search_fields_group ON module_search_fields_group.ID = module_search_vgallery.ID_module_search_group
                        WHERE 
                            module_search_vgallery.ID_module = " . $db->toSql(new ffData($ID_search, "Number")) . "
                        ORDER BY module_search_vgallery.`order`, vgallery_fields.name
                        ");
if($db->nextRecord()) {
   /* if($tpl_search_path && file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_search_path)) {
        $oRecord->template_dir = ffCommon_dirname(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . $tpl_search_path);
        $oRecord->template_file = basename($tpl_search_path);
    }*/

    $oRecord->id = "SearchConfigPreview";
    $oRecord->class = "SearchConfigPreview";
    $oRecord->src_table = ""; 

    if(check_function("MD_search_on_done_action"))
    	$oRecord->addEvent("on_done_action", "MD_search_on_done_action");

    $oRecord->skip_action = true;
    $oRecord->buttons_options["cancel"]["display"] = true;
    $oRecord->buttons_options["insert"]["display"] = false;
    $oRecord->buttons_options["print"]["display"] = false;
    
    // nuove variabili
    //$oRecord->search_name = $search_name;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "search-ID";
    $oField->base_type = "Number";
    $oField->default_value = new ffData($ID_search, "Number");
    $oRecord->addKeyField($oField);


    do {
        $field_name = $db->getField("name")->getValue();
        $field_type = $db->getField("type")->getValue();
        $group_field = $db->getField("group_field")->getValue() 
                            ? $db->getField("group_field")->getValue() 
                            : null;
        
        if (strlen($group_field) && !isset($oRecord->groups[$group_field])) { 
        	$oRecord->addContent(null, true, $group_field);
            $oRecord->groups[$group_field] = array(
                                                     "title" => ffTemplate::_get_word_by_code("search_" . $group_field)
                                                     , "cols" => 1
                                                  );
        }

        $obj_page_field = ffField::factory($cm->oPage);
        $obj_page_field->id = $field_type . $field_name;
        $obj_page_field->container_class = "search_" . preg_replace('/[^a-zA-Z0-9]/', '', $field_name);
        $obj_page_field->label = ffTemplate::_get_word_by_code($field_name);
        $obj_page_field->user_vars["group_field"] = $group_field;

        $selection_value = array();        
        
        switch($db->getField("extended_type")->getValue())
        {
            case "Selection":
            case "Option":
                $obj_page_field->base_type = "Text";

                if($db->getField("extended_type")->getValue() == "Option") {
                    $obj_page_field->control_type = "radio";
                    $obj_page_field->extended_type = "Selection";
                    $obj_page_field->widget = "";
				} else {
                    $obj_page_field->control_type = "combo";
                    $obj_page_field->extended_type = "Selection";
				}
                
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $db_selection->query("SELECT DISTINCT
                                        vgallery_rel_nodes_fields.description AS nameID
                                        , vgallery_rel_nodes_fields.description AS name
                                       FROM vgallery_rel_nodes_fields
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                            INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                       WHERE 
                                            vgallery_fields.ID = " . $db->toSql($db->getField("ID")) . " 
                                            AND " . FF_PREFIX . "languages.code = ". $db->toSql(LANGUAGE_INSET, "Text") . "
                                       ORDER BY vgallery_rel_nodes_fields.description
                                       ");
                if($db_selection->nextRecord()) {
                    do {
                        $selection_value[] = array(new ffData($db_selection->getField("nameID")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("name")->getValue())));
                    } while($db_selection->nextRecord());
                }
				
				$obj_page_field->multi_pairs = $selection_value;
				$obj_page_field->encode_entities = false;
                
                $type_value = "Text";
                break;
            case "Group":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Selection";
                $obj_page_field->control_type = "input";

                $obj_page_field->widget = "checkgroup";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = ",";  //#*#*#";
                $db_selection->query("
                                        SELECT DISTINCT
                                            vgallery_rel_nodes_fields.description
                                        FROM vgallery_rel_nodes_fields
                                            INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
                                            INNER JOIN " . FF_PREFIX . "languages ON " . FF_PREFIX . "languages.ID = vgallery_rel_nodes_fields.ID_lang
                                        WHERE 
                                            vgallery_fields.ID = " . $db->toSql($db->getField("ID")) . " 
                                                AND " . FF_PREFIX . "languages.code = ". $db->toSql(LANGUAGE_INSET, "Text") . "
                                        ORDER BY vgallery_rel_nodes_fields.description
                                    ");
                if($db_selection->nextRecord()) {
                    do {
                        $selection_value[] = array(new ffData($db_selection->getField("description")->getValue()), new ffData(ffTemplate::_get_word_by_code($db_selection->getField("description")->getValue())));
                    } while($db_selection->nextRecord());
                }

                $obj_page_field->multi_pairs = $selection_value;
                $obj_page_field->encode_entities = false;
                
                $type_value = "Text";
                break;
            case "Text":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";
                $obj_page_field->control_type = "textarea";
                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "TextBB":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";
                $obj_page_field->control_type = "textarea";
                if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/tiny_mce/tiny_mce.js")) {
                    $obj_page_field->widget = "tiny_mce";
                } else {
                    $obj_page_field->widget = "";
                }
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "TextCK":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";
                $obj_page_field->control_type = "textarea";
                if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
                    $obj_page_field->widget = "ckeditor";
                } else {
                    $obj_page_field->widget = "";
                }
                $obj_page_field->ckeditor_group_by_auth = true;
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;
                
            case "Boolean":
                $obj_page_field->base_type = "Number";
                $obj_page_field->extended_type = "Boolean";
                $obj_page_field->control_type = "checkbox";
                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData("0", "Number");
                $obj_page_field->checked_value = new ffData("1", "Number");
                $obj_page_field->grouping_separator = "";
                $type_value = "Number";
                break;

            case "Date":
                $obj_page_field->base_type = "Date";
                $obj_page_field->extended_type = "Date";
                $obj_page_field->control_type = "input";
                $obj_page_field->widget = "datepicker";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Date";
                break;

            case "DateCombo":
                $obj_page_field->base_type = "Date";
                $obj_page_field->extended_type = "Date";
                $obj_page_field->control_type = "input";
                $obj_page_field->widget = "datechooser";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Date";
                break;

            case "Image":
            case "Upload":
            case "UploadImage":
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";
                $obj_page_field->control_type = "input";
                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
                break;

            case "Number":
                $obj_page_field->base_type = "Number";
                $obj_page_field->extended_type = "";
                $obj_page_field->control_type = "input";
                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData(""); 
                $obj_page_field->checked_value = new ffData(""); 
                $obj_page_field->grouping_separator = "";
                $type_value = "Number";
                break;

                
            default: // String
                $obj_page_field->base_type = "Text";
                $obj_page_field->extended_type = "Text";
                $obj_page_field->control_type = "input";
                $obj_page_field->widget = "";
                $obj_page_field->unchecked_value = new ffData("");
                $obj_page_field->checked_value = new ffData("");
                $obj_page_field->grouping_separator = "";
                $type_value = "Text";
        }

       /* if(isset($_GET[$field_name]) && strlen($_GET[$field_name])) {
            $obj_page_field->default_value = new ffData($_REQUEST[$field_name], $type_value);
            
        }
       */
        $oRecord->addContent($obj_page_field, $group_field);
    } while($db->nextRecord());

    
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "search";
    $oButton->label = ffTemplate::_get_word_by_code("searchadv_search");
    $oButton->action_type = "submit";
    $oButton->frmAction = "adv_param";
    $oButton->form_action_url = FF_SITE_PATH . VG_SITE_SEARCH . "/" . $search_name . "?search_inset=&ret_url=" . urlencode($cm->oPage->getRequestUri());
    $oButton->aspect = "link";
    //$oButton->label = "Anteprima";//Definita nell'evento
    $oRecord->addActionButton($oButton);    
    //print_r($oRecord->groups);
    $cm->oPage->addContent($oRecord);
}
/*
function array_newsearch($needle, $haystack, $flags = NULL) 
{ 
    if (is_object($needle) && strtolower(get_class($needle)) == "ffData") 
        $tmp = $needle->getValue(); 
    else 
        $tmp = $needle; 
    if(is_array($haystack)) {
        foreach($haystack as $key => $value) 
        { 
            if($value[0]->getValue() == $tmp && $flags === NULL)
                return $key; 
            elseif($value[1]->getValue() == $tmp && $flags === 1)
                return $key; 
            elseif(($value[0]->getValue() == $tmp || $value[1]->getValue() == $tmp) && $flags === 2)
                return $key; 
        } 
    }
    return FALSE; 
}      */