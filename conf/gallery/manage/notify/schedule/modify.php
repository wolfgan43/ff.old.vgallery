<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!Auth::env("AREA_SCHEDULE_SHOW_MODIFY")) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$uid = Auth::get("user")->id;

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
                            array(new ffData(basename(VG_SITE_ADMIN)), new ffData(basename(VG_SITE_ADMIN))),
                            array(new ffData(basename(VG_SITE_RESTRICTED)), new ffData(basename(VG_SITE_RESTRICTED))), 
                            array(new ffData(basename(VG_SITE_MANAGE)), new ffData(basename(VG_SITE_MANAGE)))
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
?>