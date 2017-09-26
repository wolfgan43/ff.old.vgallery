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

if (!AREA_SETTINGS_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

$cm->oPage->form_method = "POST";

$area_sel = $_REQUEST["area"];
$area = $_REQUEST["areas"] ? $_REQUEST["areas"] : $area_sel;
$path = $_REQUEST["paths"] ? $_REQUEST["paths"] : $cm->real_path_info;
if(!$path)
    $path = "/";

$uid = $_REQUEST["uid"];
$gid = $_REQUEST["gid"];
    
$users = $_REQUEST["uid"] > 0 ? $_REQUEST["uid"] : $_REQUEST["users"]; 
$groups = $_REQUEST["gid"] > 0 ? $_REQUEST["gid"] : $_REQUEST["groups"];

if($_REQUEST["frmAction"] != "settings_update" && $_REQUEST["frmAction"] != "settings_delete") {
    unset($_REQUEST["settings_recordset_ori_values"]);
    unset($_REQUEST["settings_recordset_values"]);
    unset($_REQUEST["settings_recordset_key"]);
}


$tpl = ffTemplate::factory(get_template_cascading("/", "settings.html"));
$tpl->load_file("settings.html", "main");

$oField = ffField::factory($cm->oPage);
$oField->id = "areas";
$oField->label = ffTemplate::_get_word_by_code("settings_area");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT DISTINCT IF(info='', area, info) AS area_value, area AS area_name FROM settings ORDER BY area";
$oField->required = true;
$oField->properties["onchange"] = "document." . $cm->oPage->form_name . ".submit();";
if($area_sel) {
    $oField->value = new ffData($area_sel, "Text");
    $oField->multi_limit_select = true;
} else {
    $oField->value = new ffData($area, "Text");
    $oField->multi_limit_select = false;
}
$oField->parent_page = array(&$cm->oPage);
$tpl->set_var("areas", $oField->process());

if($user_path != ffCommon_dirname($user_path)) {
    $tmp_user_path = $user_path;
    do {
        $sSQL_path .= "
            UNION (
                SELECT
                    " . $db->toSql($tmp_user_path, "Text") . " AS path_value
                    , " . $db->toSql($tmp_user_path, "Text") . " AS path_name
            ) ";
        $tmp_user_path = ffCommon_dirname($tmp_user_path);
    } while($tmp_user_path != ffCommon_dirname($tmp_user_path));
}
$oField = ffField::factory($cm->oPage);
$oField->id = "paths";
$oField->label = ffTemplate::_get_word_by_code("settings_path");
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT  *
                        FROM 
                        (
                            (
                                SELECT
                                    '/' AS path_value
                                    , '/' AS path_name
                            )
                            $sSQL_path
                            
                        ) AS tbl_src
                        ORDER BY path_name
                        ";
$oField->required = true;
$oField->properties["onchange"] = "document." . $cm->oPage->form_name . ".submit();";
$oField->value = new ffData($path, "Text");
$oField->multi_select_one = false;
$oField->parent_page = array(&$cm->oPage);
$tpl->set_var("paths", $oField->process());

if(!($uid > 0 || $gid > 0)) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "users";
	$oField->label = ffTemplate::_get_word_by_code("settings_users");
	$oField->base_type = "Text";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT DISTINCT ID, IF(username='" . MOD_SEC_GUEST_USER_NAME . "', 'default', username) FROM " . CM_TABLE_PREFIX . "mod_security_users ORDER BY username";
	$oField->control_type = "input";
	$oField->widget = "checkgroup";
	$oField->grouping_separator = ",";
	$oField->properties["onchange"] = "document." . $cm->oPage->form_name . ".submit();";
	$oField->value = new ffData($users, "Text");
	$oField->parent_page = array(&$cm->oPage);

	$tpl->set_var("users", $oField->process());

	$oField = ffField::factory($cm->oPage);
	$oField->id = "groups";
	$oField->label = ffTemplate::_get_word_by_code("settings_groups");
	$oField->base_type = "Text";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT DISTINCT gid, IF(name='" . MOD_SEC_GUEST_GROUP_NAME . "', 'default', name) FROM " . CM_TABLE_PREFIX . "mod_security_groups ORDER BY name";
	$oField->control_type = "input";
	$oField->widget = "checkgroup";
	$oField->grouping_separator = ",";
	$oField->properties["onchange"] = "document." . $cm->oPage->form_name . ".submit();";
	$oField->value = new ffData($groups, "Text");
	$oField->parent_page = array(&$cm->oPage);
	
	$tpl->set_var("groups", $oField->process());
	
	$tpl->parse("SezUserGroup", false);
	$tpl->set_var("SezUserGroupSel", "");
} else {
	$tpl->set_var("SezUserGroup", "");
	if($gid > 0) {
		$sSQL = "SELECT name FROM " . CM_TABLE_PREFIX . "mod_security_groups WHERE gid = " . $db->toSql($gid, "Number");
		$db->query($sSQL);
		if($db->nextRecord()) {		
			$tpl->set_var("settings_user_group", "Group: " . $db->getField("name")->getValue());
			$tpl->parse("SezUserGroupSel", true);
		}
	}
	if($uid > 0) {
		$sSQL = "SELECT username FROM " . CM_TABLE_PREFIX . "mod_security_users WHERE ID = " . $db->toSql($uid, "Number");
		$db->query($sSQL);
		if($db->nextRecord()) {		
			$tpl->set_var("settings_user_group", "User: " . $db->getField("username")->getValue());
			$tpl->parse("SezUserGroupSel", true);
		}
	}
}

$tpl->set_var("SezSettingsAddnew", "");
$tpl->parse("SezSettingsModify", false);

$cm->oPage->addContent($tpl->rpparse("main", false));

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "settings";
$oGrid->title = ffTemplate::_get_word_by_code("settings_title");
$oGrid->source_SQL = "SELECT
                            settings.*
                        FROM
                            settings
                        WHERE " . (strlen($area) 
                                    ? "settings.area = " . $db->toSql($area, "Text")
                                    : "1")
                                . "
                        [AND]
                        [WHERE]
                        [ORDER]";

$oGrid->order_default = "description";
//$oGrid->order_method = "none";
$oGrid->use_search = false;
if($uid > 0 || $gid > 0) {
	$oGrid->display_delete_bt = false;
	$oGrid->display_edit_bt = false;
	$oGrid->display_edit_url = false;
	$oGrid->display_new = false;
} else {
	$oGrid->display_delete_bt = true;
	$oGrid->display_edit_bt = false;
	$oGrid->display_edit_url = true;
	$oGrid->display_new = true;
	$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/detail";
	$oGrid->record_id = "SettingsModify";
	$oGrid->resources[] = $oGrid->record_id;

	$oGrid->addEvent("on_before_parse_row", "settings_on_before_parse_row"); 	

	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "status";
	$oButton->action_type = "none";
	$oButton->aspect = "link";
	$oButton->label = ffTemplate::_get_word_by_code("settings_status");
	$oButton->display_label = false;
	$oGrid->addGridButton($oButton);
}
$oGrid->display_navigator = false;

$oGrid->use_paging = false;
//$oGrid->display_labels = false;
//$oGrid->addEvent("on_before_parse_row", "settings_on_before_parse_row");
$oGrid->addEvent("on_do_action", "settings_on_do_action");

//$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
//$oGrid->record_id = "ExtrasModify";

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);
    
$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("settings_description");
$oField->base_type = "Text";
$oField->data_info["field"] = "description";
$oField->data_info["multilang"] = true;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("settings_info");
$oField->base_type = "Text";
$oGrid->addContent($oField);

if($groups) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_groups.gid AS ID_name
                , IF(name='" . MOD_SEC_GUEST_GROUP_NAME . "', 'default', name) AS name
                , settings_rel_path.ID AS ID_rel_path
            FROM " . CM_TABLE_PREFIX . "mod_security_groups
                LEFT JOIN settings_rel_path ON settings_rel_path.path =" . $db->toSql($path, "Text") . "
                    AND settings_rel_path.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
            WHERE " . CM_TABLE_PREFIX . "mod_security_groups.gid IN  (" . $db->toSql($groups, "Text", false) . ")";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $settings_rel_path["groups" . $db->getField("name")->getValue()]["ID_name"] = $db->getField("ID_name")->getValue();
            $settings_rel_path["groups" . $db->getField("name")->getValue()]["name"] = $db->getField("name")->getValue();
            $settings_rel_path["groups" . $db->getField("name")->getValue()]["type"] = "Group";
            $settings_rel_path["groups" . $db->getField("name")->getValue()]["ID_rel_path"] = $db->getField("ID_rel_path")->getValue();
            $settings_rel_path["groups" . $db->getField("name")->getValue()]["path"] = $path;
        } while($db->nextRecord());
    }
}
if($users) {
    $sSQL = "SELECT 
                " . CM_TABLE_PREFIX . "mod_security_users.ID AS ID_name
                , IF(username='" . MOD_SEC_GUEST_USER_NAME . "', 'default', username) AS name
                , settings_rel_path.ID AS ID_rel_path
            FROM " . CM_TABLE_PREFIX . "mod_security_users
                LEFT JOIN settings_rel_path ON settings_rel_path.path =" . $db->toSql($path, "Text") . "
                    AND settings_rel_path.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
            WHERE " . CM_TABLE_PREFIX . "mod_security_users.ID IN  (" . $db->toSql($users, "Text", false) . ")";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $settings_rel_path["users" . $db->getField("name")->getValue()]["ID_name"] = $db->getField("ID_name")->getValue();
            $settings_rel_path["users" . $db->getField("name")->getValue()]["name"] = $db->getField("name")->getValue();
            $settings_rel_path["users" . $db->getField("name")->getValue()]["type"] = "User";
            $settings_rel_path["users" . $db->getField("name")->getValue()]["ID_rel_path"] = $db->getField("ID_rel_path")->getValue();
            $settings_rel_path["users" . $db->getField("name")->getValue()]["path"] = $path;
        } while($db->nextRecord());
    }
}
if(is_array($settings_rel_path) && count($settings_rel_path)) {
    foreach($settings_rel_path AS $settings_rel_path_key => $settings_rel_path_value) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = $settings_rel_path_key;
        $oField->label = $settings_rel_path_value["type"] . ": " . $settings_rel_path_value["name"];
        $oField->data_type = "callback";
        $oField->data_source = "get_setting_value";
        $oField->control_type = "input";
        $oGrid->addContent($oField, false);  
    }
    reset($settings_rel_path);
}

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "update";
$oButton->action_type = "submit";
$oButton->frmAction = "update";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("settings_update");//Definita nell'evento
$oGrid->addActionButton($oButton);

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "delete";
$oButton->action_type = "submit";
$oButton->frmAction = "delete";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("settings_delete");//Definita nell'evento
$oGrid->addActionButton($oButton); 

$oButton = ffButton::factory($cm->oPage);
$oButton->id = "cancel";
$oButton->action_type = "gotourl";
$oButton->url = "[RET_URL]";
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("settings_cancel");//Definita nell'evento
$oGrid->addActionButton($oButton);

$oGrid->user_vars["settings_rel_path"] = $settings_rel_path;
$oGrid->user_vars["area"] = $area;

$cm->oPage->addContent($oGrid);

function settings_on_before_parse_row($component) {
	$db = ffDB_Sql::factory();
	
	$sSQL = "SELECT * 
				FROM settings_rel_path_settings 
					INNER JOIN settings ON settings.ID = settings_rel_path_settings.ID_settings
				WHERE settings.ID = " . $db->toSql($component->key_fields["ID"]->value);
	$db->query($sSQL);
	if($db->nextRecord()) {
		$component->grid_buttons["status"]->class = "fa-thumbs-o-up";
	} else {
		$component->grid_buttons["status"]->class = "fa-thumbs-down";
	}
}


function settings_on_do_action($component, $action) {
    $settings_rel_path = $component->user_vars["settings_rel_path"];
    $area = $component->user_vars["area"];

    $db_update = ffDB_Sql::factory();
    
    switch($action) {
        case "update":
            if(is_array($settings_rel_path) && count($settings_rel_path)) {
                foreach($settings_rel_path AS $settings_rel_path_key => $settings_rel_path_value) {
                    if(!is_numeric($settings_rel_path_value["ID_rel_path"]) || $settings_rel_path_value["ID_rel_path"] <= 0) {
                        if($settings_rel_path_value["type"] == "Group") {
                            $uid = 0;
                            $gid = $settings_rel_path_value["ID_name"];
                        } elseif($settings_rel_path_value["type"] == "User") {
                            $uid = $settings_rel_path_value["ID_name"];
                            $gid = 0;
                        } 

                        $sSQL = "INSERT INTO settings_rel_path 
                        			(
										`ID` 	
										, `path`
										, `uid`
										, `gid`
										, `mod`
									)
                        			VALUES 
                                    (
	                                    null
	                                    , " . $db_update->toSql($settings_rel_path_value["path"], "Text") . "
	                                    , " . $db_update->toSql($uid, "Number") . "
	                                    , " . $db_update->toSql($gid, "Number") . "
	                                    , ''
                                    )";
                        $db_update->execute($sSQL);
                        $settings_rel_path[$settings_rel_path_key]["ID_rel_path"] = $db_update->getInsertID(true);
                    } 
                    
                }
                reset($settings_rel_path);
            }
           // ffErrorHandler::raise("asd", E_USER_ERROR, NULL, get_defined_vars());
            if(is_array($component->recordset_keys) && count($component->recordset_keys)) {
                foreach ($component->recordset_keys as $record_key => $record_value) {
					$arrSettingRelPathSettings = array();
					$sSQL = "SELECT settings_rel_path_settings.*
                               FROM settings_rel_path_settings
							   WHERE settings_rel_path_settings.ID_settings = " . $db_update->toSql($record_value["ID"], "Number");
					$db_update->query($sSQL);
					if($db_update->nextRecord())
					{
						do {
							$arrSettingRelPathSettings[$db_update->getField("ID_rel_path", "Number", true)] = 0;
						}while($db_update->nextRecord());
					}
						
                    foreach ($component->recordset_values[$record_key] as $field_key => $field_value) {
                        $sSQL = "SELECT value_type FROM settings WHERE ID = " . $db_update->toSql($record_value["ID"], "Number");
                        $db_update->query($sSQL);
                        if($db_update->nextRecord()) {
                             switch($db_update->getField("value_type")->getValue()) {
                                 case "Boolean":
                                 case "%": 
                                    $type_value = "Number";
                                    break;
								 case "Integer":
                                 case "String": 
                                 case "Hex":
                                 default:
                                 $type_value = "Text";
                             }
                        } else {
                            $type_value = "Text";
                        }

                        if($field_value === NULL) {
                            if($type_value == "Number") {
                                $value_set = "0";
                            } else {
                                $value_set = "";
                            }
                        } else {
                            if($type_value == "Number") {
                                if(!is_int($field_value) && strlen($field_value)) {
                                    $value_set = "1";
                                } elseif(!strlen($field_value)) {
                                    $value_set = "0";
                                } else {
                                    $value_set = $field_value;
                                }
                            } else {
                                $value_set = $field_value;
                            }
                        }
						if(is_array($arrSettingRelPathSettings) && array_key_exists($settings_rel_path[$field_key]["ID_rel_path"], $arrSettingRelPathSettings))
						{
							$sSQL = "UPDATE settings_rel_path_settings SET 
										settings_rel_path_settings.value = " . $db_update->toSql($value_set, $type_value) . "
									WHERE settings_rel_path_settings.ID_rel_path = " . $db_update->toSql($settings_rel_path[$field_key]["ID_rel_path"], "Number") . "
										AND settings_rel_path_settings.ID_settings = " . $db_update->toSql($record_value["ID"], "Number");
							$db_update->execute($sSQL);
						} else
						{
                            $sSQL = "INSERT INTO settings_rel_path_settings 
                            			(
                            				`ID` 	
                            				, `ID_rel_path`
                            				, `ID_settings` 
                            				, `value`
                            			)
                            			VALUES
                                        ( 
                                            null
                                            , " . $db_update->toSql($settings_rel_path[$field_key]["ID_rel_path"], "Number") . "
                                            , " . $db_update->toSql($record_value["ID"], "Number") . "
                                            , " . $db_update->toSql($value_set, $component->grid_fields[$field_key]->base_type) . "
                                        )";
                            $db_update->execute($sSQL);
                        }
                    }
                }
            }
            break;
        case "delete":
            if(is_array($component->recordset_keys) && count($component->recordset_keys)) {
                foreach ($component->recordset_keys as $record_key => $record_value) {
                    foreach ($component->recordset_values[$record_key] as $field_key => $field_value) {
                        $sSQL = "DELETE FROM settings_rel_path_settings 
                                WHERE settings_rel_path_settings.ID_rel_path = " . $db_update->toSql($settings_rel_path[$field_key]["ID_rel_path"], "Number") . "
                                    AND settings_rel_path_settings.ID_settings IN ( SELECT settings.ID FROM settings WHERE settings.area = " . $db_update->toSql($area, "Text") . " )";
                        $db_update->execute($sSQL);
                    }
                }
            }
            break;
        default:

    }
    
    ffRedirect($component->parent[0]->ret_url);
}
function get_setting_value($component, $key) {
    $db = ffDB_Sql::factory();
    
    $component = $component[$key]->parent[0];
    $settings_rel_path = $component->user_vars["settings_rel_path"];
    
    $value_set = NULL;
    $criteria = explode(";", $component->db[0]->record["criteria"]);
    if(is_array($criteria) && count($criteria) && trim($criteria[0])) {
        foreach ($criteria AS $criteria_value) {
            $item_value[] = array(new ffData($criteria_value), new ffData($criteria_value));
        }

        $component->grid_fields[$key]->base_type = "Text";
        $component->grid_fields[$key]->extended_type = "Selection";
        $component->grid_fields[$key]->control_type = "combo";
        $component->grid_fields[$key]->widget = "";
        $component->grid_fields[$key]->unchecked_value = new ffData("");
        $component->grid_fields[$key]->checked_value = new ffData("");
        $component->grid_fields[$key]->grouping_separator = "";
        $component->grid_fields[$key]->max_val = NULL;
        $component->grid_fields[$key]->min_val = NULL;
        $component->grid_fields[$key]->multi_pairs = $item_value; 
        $component->grid_fields[$key]->multi_select_one = false;
        $component->grid_fields[$key]->pre_process(true);                
        $type_value = "Text";
    } else {           
        $component->grid_fields[$key]->multi_pairs = NULL; 
        switch($component->db[0]->record["value_type"]) {
            case "Boolean":
                $component->grid_fields[$key]->base_type = "Number";
                $component->grid_fields[$key]->extended_type = "Boolean";
                $component->grid_fields[$key]->control_type = "checkbox";
                $component->grid_fields[$key]->widget = "";
                $component->grid_fields[$key]->unchecked_value = new ffData("0", "Number");
                $component->grid_fields[$key]->checked_value = new ffData("1", "Number");
                $component->grid_fields[$key]->grouping_separator = "";
                $component->grid_fields[$key]->max_val = NULL;
                $component->grid_fields[$key]->min_val = NULL;
                $type_value = "Number";
                break;
            case "String": 
                $component->grid_fields[$key]->base_type = "Text";
                $component->grid_fields[$key]->extended_type = "String";
                $component->grid_fields[$key]->control_type = "input";
                $component->grid_fields[$key]->widget = "";
                $component->grid_fields[$key]->unchecked_value = new ffData("");
                $component->grid_fields[$key]->checked_value = new ffData("");
                $component->grid_fields[$key]->grouping_separator = "";
                $component->grid_fields[$key]->max_val = NULL;
                $component->grid_fields[$key]->min_val = NULL;
                $type_value = "Text";
                break;
            case "Integer": 
                $component->grid_fields[$key]->base_type = "Number";
                $component->grid_fields[$key]->extended_type = "String";
                $component->grid_fields[$key]->control_type = "input";
                $component->grid_fields[$key]->widget = "";
                $component->grid_fields[$key]->unchecked_value = new ffData("");
                $component->grid_fields[$key]->checked_value = new ffData("");
                $component->grid_fields[$key]->grouping_separator = "";
                $component->grid_fields[$key]->max_val = NULL;
                $component->grid_fields[$key]->min_val = NULL;
                $type_value = "Number";
                break;
            case "%":
                $component->grid_fields[$key]->base_type = "Number";
                $component->grid_fields[$key]->extended_type = "String";
                $component->grid_fields[$key]->control_type = "input";
                $component->grid_fields[$key]->widget = "";
                $component->grid_fields[$key]->unchecked_value = new ffData("");
                $component->grid_fields[$key]->checked_value = new ffData("");
                $component->grid_fields[$key]->grouping_separator = "";
                $component->grid_fields[$key]->max_val = 100;
                $component->grid_fields[$key]->min_val = 0;
                $type_value = "Number";
                break;
            case "Hex":
                $component->grid_fields[$key]->base_type = "Text";
                $component->grid_fields[$key]->extended_type = "String";
                $component->grid_fields[$key]->control_type = "input";
                $component->grid_fields[$key]->widget = "";
                $component->grid_fields[$key]->unchecked_value = new ffData("");
                $component->grid_fields[$key]->checked_value = new ffData("");
                $component->grid_fields[$key]->grouping_separator = "";
                $component->grid_fields[$key]->max_val = 6;
                $component->grid_fields[$key]->min_val = 6;
                $type_value = "Text";
                break;
            default:
                $type_value = "Text";

        }        
    }
    
    if(is_numeric($settings_rel_path[$key]["ID_rel_path"]) && $settings_rel_path[$key]["ID_rel_path"] > 0) {
        $sSQL = "SELECT settings_rel_path_settings.ID AS ID
                    , settings_rel_path_settings.value AS value
                FROM settings_rel_path_settings
                WHERE settings_rel_path_settings.ID_rel_path = " . $db->toSql($settings_rel_path[$key]["ID_rel_path"], "Number") . "
                    AND settings_rel_path_settings.ID_settings = " . $db->toSql($component->db[0]->record["ID"], "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $value_set = $db->getField("value")->getValue();
            //ffErrorHandler::raise("asd", E_USER_ERROR, Null, get_defined_vars());
        } 
    }            

    if($type_value == "Number" && !$value_set)
        $value_set = "0";
    
    $component->grid_fields[$key]->value = new ffData($value_set, $type_value);    
        //ffErrorHandler::raise("asd", E_USER_ERROR, NULL, get_defined_vars());
    return new ffData($value_set, $type_value);   
}
