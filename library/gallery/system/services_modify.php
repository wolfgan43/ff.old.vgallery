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
function system_services_modify($service_name, $type_field) {
	$cm = cm::getInstance();
	$db = ffDB_Sql::factory();
	
   	if(is_array($type_field) && count($type_field)) {
   		$i = 1;
   		foreach($type_field AS $type_field_key => $type_field_value) {
   			if(strlen($sSQL))
   				$sSQL .= " UNION";
   			
   			$sSQL .= " SELECT " . $i . " AS ID 
		                , " . $db->toSql($service_name) . " AS `group`
		                , " . $db->toSql($type_field_key) . " AS field
		                , (SELECT value FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = (SELECT ID FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE nome = " . $db->toSql(DOMAIN_NAME) . " LIMIT 1) AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db->toSql($service_name) . " AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db->toSql($type_field_key) . " LIMIT 1) AS value ";
		    $i++;
		}
    
	    $oGrid = ffGrid::factory($cm->oPage);
	    $oGrid->id = "ServicesModify";
	    $oGrid->resources[] = $oGrid->id;
	    $oGrid->title = ffTemplate::_get_word_by_code("services_title");
	    $oGrid->source_SQL = $sSQL . " [WHERE] [ORDER] ";
	    $oGrid->order_default = "ID";
	    $oGrid->use_search = false;
    	$oGrid->addEvent("on_before_parse_row", "services_on_before_parse_row");
    	$oGrid->addEvent("on_do_action", "services_on_do_action");
	    $oGrid->ret_url = $_REQUEST["ret_url"];
		$oGrid->user_vars["type_field"] = $type_field;
		$oGrid->fixed_pre_content = ffTemplate::_get_word_by_code(ffCommon_url_rewrite($service_name, "_") . "_info");
	    
	    $oGrid->display_new = false;
	    $oGrid->display_edit_bt = false;
	    $oGrid->display_edit_url = false;
	    $oGrid->display_delete_bt = false;

	    // Campi chiave
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "ID";
	    $oField->base_type = "Number";
	    $oGrid->addKeyField($oField);

	    // Campi visualizzati
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "group";
	    $oField->label = ffTemplate::_get_word_by_code("services_group");
	    $oField->control_type = "label";
	    $oGrid->addContent($oField);

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "field";
	    $oField->label = ffTemplate::_get_word_by_code("services_field");
	    $oField->control_type = "label";
	    $oGrid->addContent($oField);

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "value";
	    $oField->label = ffTemplate::_get_word_by_code("services_value");
	    $oField->control_type = "input";
	    $oField->required = true;
	    $oGrid->addContent($oField);

	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "ActionButtonUpdate";
	    $oButton->action_type = "submit";
	    $oButton->frmAction = "update";
        $oButton->aspect = "link";
	    $oButton->label = ffTemplate::_get_word_by_code("services_update");//Definita nell'evento
	    $oGrid->addActionButton($oButton);

	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "ActionButtonCancel";
	    $oButton->action_type = "gotourl";
	    $oButton->url = "[RET_URL]";
        $oButton->aspect = "link";
	    $oButton->label = ffTemplate::_get_word_by_code("services_cancel");//Definita nell'evento
	    $oGrid->addActionButton($oButton);

		$cm->oPage->addContent($oGrid);	
	}
	
	return null;
}

function services_on_before_parse_row($component) {
    $db = ffDB_Sql::factory();
    //ffErrorHandler::raise("asd", E_USER_ERROR, NULL, get_defined_vars());   
    $field = $component->grid_fields["field"]->getValue();
    $type_field = $component->user_vars["type_field"];

    $select_one = true;
    $select_one_label = ffTemplate::_get_word_by_code("multi_select_one_label");
    $multi_pairs = null;
    //$component->grid_fields["field"]->setValue(ffTemplate::_get_word_by_code($field));
    if(is_array($type_field[$field])) {
        $extended_type = $type_field[$field]["extended_type"];
        if(isset($type_field[$field]["select_one"]))
            $select_one = $type_field[$field]["select_one"];
        if(isset($type_field[$field]["select_one_label"]))
            $select_one_label = $type_field[$field]["select_one_label"];
        if(isset($type_field[$field]["multi_pairs"]))
            $multi_pairs = $type_field[$field]["multi_pairs"];

        if(isset($type_field[$field]["required"]))
            $component->grid_fields["value"]->required = $type_field[$field]["required"];
        if(isset($type_field[$field]["placeholder"]))
            $component->grid_fields["value"]->placeholder = $type_field[$field]["placeholder"];
        if(isset($type_field[$field]["label"]))
            $component->grid_fields["value"]->label = $type_field[$field]["label"];
    } else {
        $extended_type = $type_field[$field];

        $component->grid_fields["value"]->required = false;
        $component->grid_fields["value"]->placeholder = true;
        $component->grid_fields["value"]->label = ffTemplate::_get_word_by_code("services_value");
    }
	
    switch($extended_type) {
        case "Date": 
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "DateTime";
            $component->grid_fields["value"]->control_type = "input";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "datepicker"; //datepicker
            $component->grid_fields["value"]->datepicker_force_datetime = true;

            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;  
        case "Timestamp": 
            $component->grid_fields["value"]->base_type = "Timestamp";
            $component->grid_fields["value"]->extended_type = "DateTime";
            $component->grid_fields["value"]->control_type = "input";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "datepicker"; //datepicker
            $component->grid_fields["value"]->datepicker_force_datetime = true;

            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;  
        case "Boolean":
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "Boolean";
            $component->grid_fields["value"]->control_type = "checkbox";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "";
            $component->grid_fields["value"]->unchecked_value = new ffData("0", "Text");
            $component->grid_fields["value"]->checked_value = new ffData("1", "Text");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;
        case "String": 
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "String";
            $component->grid_fields["value"]->control_type = "input";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "";
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            
                   // ffErrorHandler::raise("asd", E_USER_ERROR, NULL, get_defined_vars());

            break;
        case "Integer": 
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "String";
            $component->grid_fields["value"]->control_type = "input";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "";
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;
        case "%":
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "String";
            $component->grid_fields["value"]->control_type = "input";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "";
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = 100;
            $component->grid_fields["value"]->min_val = 0;
            break;
        case "Hex":
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "String";
            $component->grid_fields["value"]->control_type = "input";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "";
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = 6;
            $component->grid_fields["value"]->min_val = 6;
            break;
        case "Text":
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "Text";
            $component->grid_fields["value"]->control_type = "textarea";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
			if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
                $component->grid_fields["value"]->widget = "ckeditor";
            } else {
                $component->grid_fields["value"]->widget = "";
            }
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
			$component->grid_fields["value"]->ckeditor_group_by_auth = true;
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;
        case "TextSimple":
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "Text";
            $component->grid_fields["value"]->control_type = "textarea";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "";
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;
        case "Selection":
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "Selection";
            $component->grid_fields["value"]->control_type = "combo";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "";
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;
        case "Browse":
            $component->grid_fields["value"]->base_type = "Text";
            $component->grid_fields["value"]->extended_type = "File";
            $component->grid_fields["value"]->control_type = "file";
            $component->grid_fields["value"]->multi_pairs = $multi_pairs;
            $component->grid_fields["value"]->multi_select_one = $select_one;
            $component->grid_fields["value"]->multi_select_one_label = $select_one_label;
            $component->grid_fields["value"]->widget = "uploadifive"; 
			if(check_function("set_field_uploader")) { 
				$component->grid_fields["value"] = set_field_uploader($component->grid_fields["value"]);
			}            
            $component->grid_fields["value"]->unchecked_value = new ffData("");
            $component->grid_fields["value"]->checked_value = new ffData("");
            $component->grid_fields["value"]->grouping_separator = "";
            $component->grid_fields["value"]->max_val = NULL;
            $component->grid_fields["value"]->min_val = NULL;
            break;            
        default:         
    }        

	$component->grid_fields["value"]->pre_process(true); 
//ffErrorHandler::raise("SD", E_USER_WARNING, null, get_defined_vars());
    return null;  
}

function services_on_do_action($component, $action) {
    $db_update = ffDB_Sql::factory();

    switch($action) {
        case "update":
            if(is_array($component->recordset_values) && count($component->recordset_values)) {
				$sSQL = " SELECT ID FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE nome = " . $db_update->toSql(DOMAIN_NAME);
				$db_update->query($sSQL);
				if($db_update->nextRecord()) {
					$ID_domain = $db_update->getField("ID", "Number")->getValue();
				} else {
	                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains 
	                        (
	                            `ID` ,
	                            `nome` ,
	                            `owner` ,
	                            `company_name` ,
	                            `type` ,
	                            `creation_date` ,
	                            `expiration_date` ,
	                            `time_zone` ,
	                            `status` ,
	                            `billing_status` ,
	                            `ip_address`
	                        ) 
	                        VALUES 
	                        (
	                            NULL , 
	                            " . $db_update->toSql(new ffData(DOMAIN_NAME, "Text")) . ", 
	                            0, 
	                            '', 
	                            '0', 
	                            CURDATE(), 
	                            CURDATE(), 
	                            0, 
	                            0, 
	                            0, 
	                            " . $db_update->toSql(new ffData($_SERVER["REMOTE_ADDR"], "Text")) . " 
	                        )";
	                $db_update->execute($sSQL);
	                $ID_domain =$db_update->getInsertID(true);
				}

                foreach ($component->recordset_values AS $record_key => $record_value) {
                    switch($component->user_vars["type_field"][$record_value["field"]]) {
                        case "Timestamp":
                            $tmp_value = new ffData($record_value["value"], "DateTime", FF_LOCALE);

                            $real_value = $tmp_value->getValue("Timestamp");
                            break;
                        default:
                            $real_value = $record_value["value"];
                    }
                    
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains_fields.*
                    		FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields
                            WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_update->toSql($ID_domain, "Number") . "  
                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db_update->toSql($record_value["group"]) . "
                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db_update->toSql($record_value["field"]);
                    $db_update->query($sSQL);
					if($db_update->nextRecord()) {
						if($db_update->numRows() > 1) {
		                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields 
		                            WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_update->toSql($ID_domain, "Number") . "  
		                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db_update->toSql($record_value["group"]) . "
		                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db_update->toSql($record_value["field"]) . "
		                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID <> " . $db_update->toSql($db_update->getField("ID", "Number"));
		                    $db_update->execute($sSQL);
						}
					
	                    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains_fields SET 
	                                " . CM_TABLE_PREFIX . "mod_security_domains_fields.value = " . $db_update->toSql($real_value) . "
	                            WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_update->toSql($ID_domain, "Number") . "  
	                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db_update->toSql($record_value["group"]) . "
	                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db_update->toSql($record_value["field"]);
	                    $db_update->execute($sSQL);
					} else {
                        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains_fields 
                                    (
                                        ID
                                        , ID_domains
                                        , `group`
                                        , field
                                        , value
                                    )
                                    VALUES
                                    ( 
                                        ''
                                        , " . $db_update->toSql($ID_domain, "Number") . "  
                                        , " . $db_update->toSql($record_value["group"]) . "  
                                        , " . $db_update->toSql($record_value["field"]) . "  
                                        , " . $db_update->toSql($real_value) . "  
                                    )";
                        $db_update->execute($sSQL);
                    }
                }
                
                $sSQL = "UPDATE 
                            `layout` 
                        SET 
                            `layout`.`last_update` = " . $db_update->toSql(time()) . "
                        WHERE 1
                            ";
                $db_update->execute($sSQL);
            }

            unset_session("webservices");

            ffRedirect($component->ret_url);
            break;
        default:

    }
}
