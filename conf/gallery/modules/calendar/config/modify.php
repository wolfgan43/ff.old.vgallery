<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("MODULE_SHOW_CONFIG")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$oRecord = ffRecord::factory($cm->oPage);

if(!isset($_REQUEST["keys"]["calendarcnf-ID"])) {
    $db_gallery->query("SELECT module_calendar.*
                            FROM 
                                module_calendar
                            WHERE 
                                module_calendar.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info)))
                        );
    if($db_gallery->nextRecord()) {
        $_REQUEST["keys"]["calendarcnf-ID"] = $db_gallery->getField("ID", "Number")->getValue();
    } else {
		if($_REQUEST["keys"]["ID"] > 0) {
	    	$db_gallery->execute("DELETE
		                            FROM 
		                                modules
		                            WHERE 
		                                modules.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number")
		                        );
		    if($_REQUEST["XHR_DIALOG_ID"]) {
			    die(ffCommon_jsonenc(array("resources" => array("modules"), "close" => true, "refresh" => true), true));
		    } else {
			    ffRedirect($_REQUEST["ret_url"]);
		    } 
        }
	}
}

if(isset($_REQUEST["keys"]["calendarcnf-ID"]))
{
	$module_calendar_title = ffTemplate::_get_word_by_code("modify_module_calendar");
	$sSQL = "SELECT module_calendar.name
				FROM module_calendar
				WHERE module_calendar.ID = " . $_REQUEST["keys"]["calendarcnf-ID"];
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord())
	{
		$module_calendar_title .= ": " . $db_gallery->getField("name", "Text", true);
	}
} else
{
	$module_calendar_title = ffTemplate::_get_word_by_code("addnew_module_calendar");
}

$oRecord->id = "CalendarConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
//$oRecord->title = ffTemplate::_get_word_by_code("calendar_modify");
$oRecord->src_table = "module_calendar";
$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-module">' . Cms::getInstance("frameworkcss")->get("vg-modules", "icon-tag", array("2x", "module", "calendar")) . $module_calendar_title . '</h1>';

if(check_function("MD_general_on_done_action"))
	$oRecord->addEvent("on_done_action", "MD_general_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "calendarcnf-ID";
$oField->base_type = "Number";
$oField->data_source = "ID"; 
$oRecord->addKeyField($oField);

$oRecord->addTab("general");
$oRecord->setTabTitle("general", ffTemplate::_get_word_by_code("module_calendar_general"));

$oRecord->addContent(null, true, "general"); 
$oRecord->groups["general"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_calendar_general")
                                 , "cols" => 1
                                 , "tab" => "general"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("calendar_name");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "private_key";
$oField->label = ffTemplate::_get_word_by_code("calendar_private_key");
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "width";
$oField->label = ffTemplate::_get_word_by_code("calendar_width");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "height";
$oField->label = ffTemplate::_get_word_by_code("calendar_height");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "title";
$oField->label = ffTemplate::_get_word_by_code("calendar_title");
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "start_mode";
$oField->label = ffTemplate::_get_word_by_code("calendar_start_mode");
$oField->extended_type = "Selection";
$oField->base_type = "Text";
$oField->multi_pairs =  array (
                            array(new ffData("WEEK"), new ffData(ffTemplate::_get_word_by_code("week"))),
                            array(new ffData("MONTH"), new ffData(ffTemplate::_get_word_by_code("month"))),
                            array(new ffData("AGENDA"), new ffData(ffTemplate::_get_word_by_code("agenda"))),
                       );
$oField->default_value = new ffData("MONTH");
$oField->required = true; 
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "start_day";
$oField->label = ffTemplate::_get_word_by_code("calendar_start_day");
$oField->extended_type = "Selection";
$oField->base_type = "Number";
$oField->multi_pairs =  array (
                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("Sunday"))),
                            array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("Monday"))),
                            array(new ffData("3", "Number"), new ffData(ffTemplate::_get_word_by_code("Tuesday"))),
                            array(new ffData("4", "Number"), new ffData(ffTemplate::_get_word_by_code("Wednesday"))),
                            array(new ffData("5", "Number"), new ffData(ffTemplate::_get_word_by_code("Thursday"))),
                            array(new ffData("6", "Number"), new ffData(ffTemplate::_get_word_by_code("Friday"))),
                            array(new ffData("7", "Number"), new ffData(ffTemplate::_get_word_by_code("Saturday"))),
                       );
$oField->default_value = new ffData("1", "Number"); 
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "bgcolor";
$oField->label = ffTemplate::_get_word_by_code("calendar_bgcolor");
$oField->widget = "";
$oField->default_value = new ffData("FFFFFF");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "color";
$oField->label = ffTemplate::_get_word_by_code("calendar_color");
$oField->widget = "";
$oField->default_value = new ffData("A32929");
$oField->required = true;
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "calendars";
$oField->label = ffTemplate::_get_word_by_code("calendar_calendars");
$oField->widget = "listgroup";
$oField->grouping_separator = ",";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "timezone";
$oField->label = ffTemplate::_get_word_by_code("calendar_timezone");
$oField->default_value = new ffData("Europe/Rome");
$oField->required = true;
$oRecord->addContent($oField, "general");


$oRecord->addTab("display");
$oRecord->setTabTitle("display", ffTemplate::_get_word_by_code("module_calendar_display"));

$oRecord->addContent(null, true, "display"); 
$oRecord->groups["display"] = array(
                                 "title" => ffTemplate::_get_word_by_code("module_calendar_display")
                                 , "cols" => 1
                                 , "tab" => "display"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "show_title";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_title");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_navigation";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_navigation");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_date";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_date");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_print";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_print");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_tab";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_tab");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_list_calendar";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_list_calendar");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_timezone";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_timezone");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$oField = ffField::factory($cm->oPage);
$oField->id = "show_border";
$oField->label = ffTemplate::_get_word_by_code("calendar_show_border");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "display");

$cm->oPage->addContent($oRecord);
?>
