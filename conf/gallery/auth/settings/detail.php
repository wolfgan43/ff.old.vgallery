<?php
if (!AREA_SETTINGS_SHOW_MODIFY) {
    FormsDialog(false, "OkOnly", ffTemplate::_get_word_by_code("dialog_title_accessdenied"), ffTemplate::_get_word_by_code("dialog_description_invalidpath"), "", $site_path . "/", THEME_INSET);
}

require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "SettingsModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("Settings_modify");
$oRecord->src_table = "settings";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oRecord->addTab("general");
$oRecord->setTabTitle("general", ffTemplate::_get_word_by_code("settings_general"));

$oRecord->addContent(null, true, "general"); 
$oRecord->groups["general"] = array(
                                         "title" => ffTemplate::_get_word_by_code("settings_general")
                                         , "cols" => 1
                                         , "tab" => "general"
                                      );

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("settings_description");
$oField->id = "description";
$oField->required = true;
$oRecord->addContent($oField, "general");


$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("settings_area");
$oField->id = "area";
$oField->source_SQL = "SELECT DISTINCT area, area FROM settings ORDER BY area";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("settings_type");
$oField->id = "type";
$oField->source_SQL = "SELECT DISTINCT type, type FROM settings ORDER BY type";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("settings_value_type");
$oField->id = "value_type";
$oField->source_SQL = "SELECT DISTINCT value_type, value_type FROM settings ORDER BY value_type";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("settings_criteria");
$oField->id = "criteria";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("settings_dependence");
$oField->id = "dependence";
$oField->source_SQL = "SELECT DISTINCT description, description FROM settings ORDER BY description";
$oField->widget = "listgroup";
$oField->grouping_separator = ";";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->label = ffTemplate::_get_word_by_code("settings_info");
$oField->id = "info";
$oField->control_type = "textarea";
$oRecord->addContent($oField, "general");

$cm->oPage->addContent($oRecord);

if(isset($_REQUEST["keys"]["ID"])) {
	$oRecord->addTab("settings");
	$oRecord->setTabTitle("settings", ffTemplate::_get_word_by_code("settings_title"));

	$oRecord->addContent(null, true, "settings"); 
	$oRecord->groups["settings"] = array(
	                                         "title" => ffTemplate::_get_word_by_code("settings_title")
	                                         , "cols" => 1
	                                         , "tab" => "settings"
	                                      );
	
	$oDetail = ffDetails::factory($cm->oPage);
	$oDetail->id = "SettingsRelDefault";
	$oDetail->title = ffTemplate::_get_word_by_code("settings_rel_default");
	$oDetail->src_table = "settings_rel_path_settings";
	$oDetail->order_default = "ID";
	$oDetail->auto_populate_edit = true;
	$oDetail->populate_edit_SQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_groups.name AS groupname
										, settings_rel_path_settings.value
										, settings_rel_path_settings.ID
										, settings_rel_path_settings.ID_settings
										, settings_rel_path.ID AS ID_rel_path
									FROM " . CM_TABLE_PREFIX . "mod_security_groups
										LEFT JOIN settings_rel_path ON settings_rel_path.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid AND settings_rel_path.path = '/'
										LEFT JOIN settings_rel_path_settings ON settings_rel_path.ID = settings_rel_path_settings.ID_rel_path AND settings_rel_path_settings.ID_settings = [ID_FATHER]
									ORDER BY " . CM_TABLE_PREFIX . "mod_security_groups.name";

	$oDetail->fields_relationship = array ("ID_settings" => "ID");
	$oDetail->display_new = false;
	$oDetail->display_delete = false;

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oDetail->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_rel_path";
	$oField->base_type = "Number";
	$oDetail->addHiddenField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "groupname";
	$oField->label = ffTemplate::_get_word_by_code("settings_rel_default_groupname");
	$oField->control_type = "label";
	$oField->store_in_db = false;
	$oDetail->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "value";
	$oField->label = ffTemplate::_get_word_by_code("settings_rel_default_value");

	$sSQL = "SELECT * FROM settings WHERE ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$criteria = explode(";", $db_gallery->getField("criteria")->getValue());
		$value_type = $db_gallery->getField("value_type")->getValue();

	    if(is_array($criteria) && count($criteria) && trim($criteria[0])) {
	        foreach ($criteria AS $criteria_value) {
	            $item_value[] = array(new ffData($criteria_value), new ffData($criteria_value));
	        }

	        $oField->base_type = "Text";
	        $oField->extended_type = "Selection";
	        $oField->control_type = "combo";
	        $oField->widget = "";
	        $oField->unchecked_value = new ffData("");
	        $oField->checked_value = new ffData("");
	        $oField->grouping_separator = "";
	        $oField->max_val = NULL;
	        $oField->min_val = NULL;
	        $oField->multi_pairs = $item_value; 
	        $oField->multi_select_one = false;
	        $oField->pre_process(true);                
	        $type_value = "Text";
	    } else {           
	        $oField->multi_pairs = NULL; 
	        switch($value_type) {
	            case "Boolean":
	                $oField->base_type = "Number";
	                $oField->extended_type = "Boolean";
	                $oField->control_type = "checkbox";
	                $oField->widget = "";
	                $oField->unchecked_value = new ffData("0", "Number");
	                $oField->checked_value = new ffData("1", "Number");
	                $oField->grouping_separator = "";
	                $oField->max_val = NULL;
	                $oField->min_val = NULL;
	                break;
	            case "String": 
	                $oField->base_type = "Text";
	                $oField->extended_type = "String";
	                $oField->control_type = "input";
	                $oField->widget = "";
	                $oField->unchecked_value = new ffData("");
	                $oField->checked_value = new ffData("");
	                $oField->grouping_separator = "";
	                $oField->max_val = NULL;
	                $oField->min_val = NULL;
	                break;
	            case "Integer": 
	                $oField->base_type = "Number";
	                $oField->extended_type = "String";
	                $oField->control_type = "input";
	                $oField->widget = "";
	                $oField->unchecked_value = new ffData("");
	                $oField->checked_value = new ffData("");
	                $oField->grouping_separator = "";
	                $oField->max_val = NULL;
	                $oField->min_val = NULL;
	                break;
	            case "%":
	                $oField->base_type = "Number";
	                $oField->extended_type = "String";
	                $oField->control_type = "input";
	                $oField->widget = "";
	                $oField->unchecked_value = new ffData("");
	                $oField->checked_value = new ffData("");
	                $oField->grouping_separator = "";
	                $oField->max_val = 100;
	                $oField->min_val = 0;
	                break;
	            case "Hex":
	                $oField->base_type = "Text";
	                $oField->extended_type = "String";
	                $oField->control_type = "input";
	                $oField->widget = "";
	                $oField->unchecked_value = new ffData("");
	                $oField->checked_value = new ffData("");
	                $oField->grouping_separator = "";
	                $oField->max_val = 6;
	                $oField->min_val = 6;
	                break;
	            default:
	        }        
	    }
	}
	$oDetail->addContent($oField);

	$oRecord->addContent($oDetail, "settings");
	$cm->oPage->addContent($oDetail);
}
?>