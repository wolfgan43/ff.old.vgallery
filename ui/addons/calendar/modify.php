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

$record = system_ffComponent_resolve_record("module_calendar", array(
	"name" => "IF(module_calendar.display_name = ''
				    , REPLACE(module_calendar.name, '-', ' ')
				    , module_calendar.display_name
				)"
));

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "CalendarConfigModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->resources[] = "modules";
$oRecord->src_table = "module_calendar";
$oRecord->auto_populate_edit = true;
$oRecord->populate_edit_SQL = "SELECT module_calendar.*
									, IF(module_calendar.display_name = ''
										, REPLACE(module_calendar.name, '-', ' ')
										, module_calendar.display_name
									) AS display_name
								FROM module_calendar 
								WHERE module_calendar.ID =" . $db->toSql($_REQUEST["keys"]["ID"], "Number");
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
$oField->widget = "slug";
$oField->slug_title_field = "display_name";
$oField->container_class = "hidden";
$oRecord->addContent($oField, "general");

$oField = ffField::factory($cm->oPage);
$oField->id = "display_name";
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
