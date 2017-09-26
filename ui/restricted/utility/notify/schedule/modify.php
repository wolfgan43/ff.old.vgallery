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

if (!AREA_SCHEDULE_SHOW_MODIFY) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$user_permission = get_session("user_permission");
$uid = $user_permission["ID"];

// -------------------------
//          RECORD
// -------------------------
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ScheduleModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("schedule_modify_title");
$oRecord->src_table = "notify_schedule";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("schedule_modify_name");
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->label = ffTemplate::_get_word_by_code("schedule_modify_area");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
                            array(new ffData(basename(VG_WS_ADMIN)), new ffData(basename(VG_WS_ADMIN))),
                            array(new ffData(basename(VG_WS_RESTRICTED)), new ffData(basename(VG_WS_RESTRICTED))), 
                            array(new ffData(basename(VG_WS_ECOMMERCE)), new ffData(basename(VG_WS_ECOMMERCE)))
                       );      
$oRecord->addContent($oField);



$oField = ffField::factory($cm->oPage);
$oField->id = "job";
$oField->label = ffTemplate::_get_word_by_code("schedule_modify_job");
$oField->extended_type = "Selection";
	foreach(glob(FF_DISK_PATH . "/conf" . GALLERY_PATH_JOB . "/*", GLOB_ONLYDIR) as $real_file) { 
	    if (is_dir($real_file)) {
	        $oField->multi_pairs[] = array(new ffData(basename($real_file)), new ffData(ffTemplate::_get_word_by_code(basename($real_file))));
	        
	    }
	}
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "period";
$oField->label = ffTemplate::_get_word_by_code("schedule_modify_period");
$oField->required = true;
$oField->base_type = "Number";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "hour";
$oField->label = ffTemplate::_get_word_by_code("schedule_modify_hour");
$oField->required = true;
$oField->base_type = "Time";
$oField->app_type = "Time";
$oField->widget = "timepicker";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("schedule_modify_status");
$oField->required = true;
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("1", "Number");
$oRecord->addContent($oField);


$oRecord->additional_fields = array("owner" => new ffData($uid, "Number")
                                    , "last_update" =>  new ffData(time(), "Number")
                                    );

$cm->oPage->addContent($oRecord);